<?php

if (\rex::isBackend() && is_object(\rex::getUser())) {
    rex_perm::register('d2u_videos[]', rex_i18n::msg('d2u_videos_rights'));
    rex_perm::register('d2u_videos[edit_data]', rex_i18n::msg('d2u_videos_rights_edit_data'), rex_perm::OPTIONS);
    rex_perm::register('d2u_videos[edit_lang]', rex_i18n::msg('d2u_videos_rights_edit_lang'), rex_perm::OPTIONS);
    rex_perm::register('d2u_videos[settings]', rex_i18n::msg('d2u_videos_rights_settings'), rex_perm::OPTIONS);
}

if (\rex::isBackend()) {
    rex_extension::register('CLANG_DELETED', rex_d2u_videos_clang_deleted(...));
    rex_extension::register('D2U_VIDEO_IN_USE', rex_d2u_videos_configured_video_is_in_use(...));
    rex_extension::register('D2U_HELPER_TRANSLATION_LIST', rex_d2u_videos_translation_list(...));
    rex_extension::register('MEDIA_IS_IN_USE', rex_d2u_videos_media_is_in_use(...));
} else {
    rex_extension::register('YREWRITE_SITEMAP', rex_d2u_videos_sitemap(...));
}

/**
 * Checks if video is used in configured database tables.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<string> Warning message as array
 */
function rex_d2u_videos_configured_video_is_in_use(rex_extension_point $ep): array
{
    $warning = $ep->getSubject();
    $params = $ep->getParams();
    $video_id = (int) $params['video_id'];

    $tables_config = (string) rex_config::get('d2u_videos', 'additional_video_usage_tables', '');
    if ('' === trim($tables_config)) {
        return $warning;
    }

    $tables = json_decode($tables_config, true);
    if (!is_array($tables)) {
        return $warning;
    }

    foreach ($tables as $table_config) {
        if (!is_array($table_config)) {
            continue;
        }

        $table = rex_d2u_videos_get_safe_identifier((string) ($table_config['table'] ?? ''));
        $field = rex_d2u_videos_get_safe_identifier((string) ($table_config['field'] ?? ''));
        if ('' === $table || '' === $field) {
            continue;
        }

        $id_field = rex_d2u_videos_get_safe_identifier((string) ($table_config['id_field'] ?? ''));
        $name_field = rex_d2u_videos_get_safe_identifier((string) ($table_config['name_field'] ?? ''));
        $db_table = str_starts_with($table, rex::getTablePrefix()) ? $table : rex::getTable($table);
        $is_article_slice_table = in_array($db_table, [rex::getTable('article_slice'), rex::getTablePrefix() . 'article_slice'], true);
        $select_fields = [$field];
        if ('' !== $id_field) {
            $select_fields[] = $id_field;
        }
        if ('' !== $name_field) {
            $select_fields[] = $name_field;
        }
        foreach (['id', 'article_id', 'clang_id', 'ctype_id'] as $slice_field) {
            if ($is_article_slice_table && !in_array($slice_field, $select_fields, true)) {
                $select_fields[] = $slice_field;
            }
        }

        $additional_where = rex_d2u_videos_get_additional_where((string) ($table_config['where'] ?? ''), count($warning));
        if (null === $additional_where) {
            continue;
        }

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT '. implode(', ', array_map(static fn ($select_field) => '`'. $select_field .'`', $select_fields)) .' FROM `'. $db_table .'` '
            .'WHERE (`'. $field .'` = :video_id OR FIND_IN_SET(:video_id, `'. $field .'`) OR `'. $field .'` LIKE :pipe_video_id)'
            . $additional_where['sql'], [
                ':video_id' => (string) $video_id,
                ':pipe_video_id' => '%|'. $video_id .'|%',
                ...$additional_where['params'],
            ]);

        for ($i = 0; $i < $sql->getRows(); ++$i) {
            $label = (string) ($table_config['label'] ?? $table);
            $name = '' !== $name_field ? (string) $sql->getValue($name_field) : '';
            $message = rex_d2u_videos_get_usage_link($is_article_slice_table, $sql, $label, $name);
            if (!in_array($message, $warning, true)) {
                $warning[] = $message;
            }
            $sql->next();
        }
    }

    return $warning;
}

/**
 * Returns safe SQL identifier.
 */
function rex_d2u_videos_get_safe_identifier(string $identifier): string
{
    return 1 === preg_match('/^[a-zA-Z0-9_]+$/', $identifier) ? $identifier : '';
}

/**
 * Returns additional safe WHERE fragment from config.
 * @return array{sql:string,params:array<string,int|string>}|null Null if invalid
 */
function rex_d2u_videos_get_additional_where(string $where, int $index): ?array
{
    $where = trim($where);
    if ('' === $where) {
        return ['sql' => '', 'params' => []];
    }

    if (!preg_match('/^([a-zA-Z0-9_]+)\s*(=|!=|>=|<=|>|<)\s*(.+)$/', $where, $matches)) {
        return null;
    }

    $field = rex_d2u_videos_get_safe_identifier($matches[1]);
    if ('' === $field) {
        return null;
    }

    $operator = $matches[2];
    $raw_value = trim($matches[3]);
    $param_name = ':config_where_'. $index;

    if (preg_match('/^-?\d+$/', $raw_value)) {
        return [
            'sql' => ' AND `'. $field .'` '. $operator .' '. $param_name,
            'params' => [$param_name => (int) $raw_value],
        ];
    }

    if (preg_match("/^(\"([^\"]*)\"|'([^']*)')$/", $raw_value, $value_matches)) {
        $value = '' !== ($value_matches[2] ?? '') ? $value_matches[2] : ($value_matches[3] ?? '');
        return [
            'sql' => ' AND `'. $field .'` '. $operator .' '. $param_name,
            'params' => [$param_name => $value],
        ];
    }

    return null;
}

/**
 * Returns usage link for configured database table row.
 */
function rex_d2u_videos_get_usage_link(bool $is_article_slice_table, rex_sql $sql, string $label, string $name): string
{
    if ($is_article_slice_table && (int) $sql->getValue('article_id') > 0) {
        $article_id = (int) $sql->getValue('article_id');
        $clang_id = (int) $sql->getValue('clang_id');
        $slice_id = (int) $sql->getValue('id');
        $ctype_id = (int) $sql->getValue('ctype_id');
        $article = rex_article::get($article_id, $clang_id);
        $article_name = $article instanceof rex_article ? $article->getName() : 'Artikel '. $article_id;

        return '<a href="?page=content/edit&amp;article_id='. $article_id .'&amp;slice_id='. $slice_id .'&amp;clang='. $clang_id .'&amp;ctype='. $ctype_id .'&amp;function=edit">'. rex_escape($article_name) .'</a> (Slice '. $slice_id .')';
    }

    return rex_escape($label) . ('' !== $name ? ': '. rex_escape($name) : '');
}

/**
 * Deletes language specific configurations and objects.
 * @param rex_extension_point<string> $ep Redaxo extension point
 * @return mixed Warning message as array
 */
function rex_d2u_videos_clang_deleted(rex_extension_point $ep)
{
    $warning = $ep->getSubject();
    $params = $ep->getParams();
    $clang_id = (int) $params['id'];

    // Delete
    $videos = TobiasKrais\D2UVideos\Video::getAll($clang_id);
    foreach ($videos as $video) {
        $video->delete(false);
    }

    return $warning;
}

/**
 * Checks if media is used by this addon.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<string> Warning message as array
 */
function rex_d2u_videos_media_is_in_use(rex_extension_point $ep)
{
    $warning = $ep->getSubject();
    $params = $ep->getParams();
    $filename = (string) $params['filename'];

    // News
    $sql_videos = rex_sql::factory();
    $sql_videos->setQuery('SELECT lang.video_id, name FROM `' . rex::getTablePrefix() . 'd2u_videos_videos_lang` AS lang '
        .'LEFT JOIN `' . rex::getTablePrefix() . 'd2u_videos_videos` AS videos ON lang.video_id = videos.video_id '
        .'WHERE lang.picture = :filename OR videos.picture = :filename OR lang.redaxo_file = :filename OR videos.redaxo_file = :filename', [':filename' => $filename]);

    // Prepare warnings
    // News
    for ($i = 0; $i < $sql_videos->getRows(); ++$i) {
        $message = '<a href="javascript:openPage(\'index.php?page=d2u_videos/videos&func=edit&entry_id='.
            $sql_videos->getValue('video_id') .'\')">'. rex_i18n::msg('d2u_videos') .': '. $sql_videos->getValue('name') .'</a>';
        if (!in_array($message, $warning, true)) {
            $warning[] = $message;
        }
        $sql_videos->next();
    }

    // Settings
    $addon = rex_addon::get('d2u_videos');
    if ($addon->hasConfig('player_js') && $addon->getConfig('player_js') === $filename) {
        $message = '<a href="javascript:openPage(\'index.php?page=d2u_videos/settings\')">'.
             rex_i18n::msg('d2u_videos') .' '. rex_i18n::msg('d2u_helper_settings') . '</a>';
        if (!in_array($message, $warning, true)) {
            $warning[] = $message;
        }
    }
    return $warning;
}

/**
 * Adds videos to sitemap.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<string> updated sitemap entries
 */
function rex_d2u_videos_sitemap(rex_extension_point $ep)
{
    $sitemap_entries = $ep->getSubject();

    $modules = \TobiasKrais\D2UVideos\Module::getModules();
    foreach ($modules as $module) {
        $module->initRedaxoContext(rex_addon::get('d2u_videos'), 'modules/');
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM '. rex::getTablePrefix() .'article_slice WHERE module_id = '. $module->getRedaxoId());
        for ($i = 0; $i < $sql->getRows(); ++$i) {
            $type = (string) $sql->getValue('value1');
            $article_id = (int) $sql->getValue('article_id');
            $clang_id = (int) $sql->getValue('clang_id');
            if ('playlist' === $type) {
                $playlist_id = (int) $sql->getValue('value2');
                $playlist = new TobiasKrais\D2UVideos\Playlist($playlist_id);
                $video_entry = '';
                foreach ($playlist->videos as $playlist_video) {
                    // set correct clang id
                    $video = new \TobiasKrais\D2UVideos\Video($playlist_video->video_id, $clang_id, true);
                    $video_entry .= $video->getSitemapEntry();
                }
                // insert into sitemap
                foreach ($sitemap_entries as $sitemap_key => $sitemap_entry) {
                    if (str_contains($sitemap_entry, rex_getUrl($article_id, $clang_id) .'</loc>')) {
                        $sitemap_entries[$sitemap_key] = str_replace('</url>', $video_entry .'</url>', $sitemap_entry);
                    }
                }
            } elseif ('video' === $type) {
                $video_id = (int) $sql->getValue('value3');
                $video = new \TobiasKrais\D2UVideos\Video($video_id, $clang_id, true);
                // insert into sitemap
                foreach ($sitemap_entries as $sitemap_key => $sitemap_entry) {
                    if (str_contains($sitemap_entry, rex_getUrl($article_id, $clang_id) .'</loc>')) {
                        $sitemap_entries[$sitemap_key] = str_replace('</url>', $video->getSitemapEntry() .'</url>', $sitemap_entry);
                    }
                }
            }
            $sql->next();
        }

    }

    return $sitemap_entries;
}

/**
 * Addon translation list.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<array<string, array<int, array<string, string>>|string>|string> Addon translation list
 */
function rex_d2u_videos_translation_list(rex_extension_point $ep) {
    $params = $ep->getParams();
    $source_clang_id = (int) $params['source_clang_id'];
    $target_clang_id = (int) $params['target_clang_id'];
    $filter_type = (string) $params['filter_type'];

    $list = $ep->getSubject();
    $list_entry = [
        'addon_name' => rex_i18n::msg('d2u_videos'),
        'pages' => []
    ];

    $videos = \TobiasKrais\D2UVideos\Video::getTranslationHelperObjects($target_clang_id, $filter_type);
    if (count($videos) > 0) {
        $html = '<ul>';
        foreach ($videos as $video) {
            if ('' === $video->name) {
                $video = new \TobiasKrais\D2UVideos\Video($video->video_id, $source_clang_id);
            }
            $html .= '<li><a href="'. rex_url::backendPage('d2u_videos/videos', ['entry_id' => $video->video_id, 'func' => 'edit']) .'">'. $video->name .'</a></li>';
        }
        $html .= '</ul>';
        
        $list_entry['pages'][] = [
            'title' => rex_i18n::msg('d2u_videos'),
            'icon' => 'fa-video-camera',
            'html' => $html
        ];
    }

    $list[] = $list_entry;

    return $list;
}