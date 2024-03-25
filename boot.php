<?php

if (\rex::isBackend() && is_object(\rex::getUser())) {
    rex_perm::register('d2u_videos[]', rex_i18n::msg('d2u_videos_rights'));
    rex_perm::register('d2u_videos[edit_data]', rex_i18n::msg('d2u_videos_rights_edit_data'), rex_perm::OPTIONS);
    rex_perm::register('d2u_videos[edit_lang]', rex_i18n::msg('d2u_videos_rights_edit_lang'), rex_perm::OPTIONS);
    rex_perm::register('d2u_videos[settings]', rex_i18n::msg('d2u_videos_rights_settings'), rex_perm::OPTIONS);
}

if (\rex::isBackend()) {
    rex_extension::register('CLANG_DELETED', rex_d2u_videos_clang_deleted(...));
    rex_extension::register('D2U_HELPER_TRANSLATION_LIST', rex_d2u_videos_translation_list(...));
    rex_extension::register('MEDIA_IS_IN_USE', rex_d2u_videos_media_is_in_use(...));
} else {
    rex_extension::register('YREWRITE_SITEMAP', rex_d2u_videos_sitemap(...));
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
    $filename = addslashes((string) $params['filename']);

    // News
    $sql_videos = rex_sql::factory();
    $sql_videos->setQuery('SELECT lang.video_id, name FROM `' . rex::getTablePrefix() . 'd2u_videos_videos_lang` AS lang '
        .'LEFT JOIN `' . rex::getTablePrefix() . 'd2u_videos_videos` AS videos ON lang.video_id = videos.video_id '
        .'WHERE picture = "'. $filename .'" OR lang.redaxo_file = "'. $filename .'" OR videos.redaxo_file = "'. $filename .'"');

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
    $source_clang_id = $params['source_clang_id'];
    $target_clang_id = $params['target_clang_id'];
    $filter_type = $params['filter_type'];

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