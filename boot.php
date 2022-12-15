<?php
if(\rex::isBackend() && is_object(\rex::getUser())) {
	rex_perm::register('d2u_videos[]', rex_i18n::msg('d2u_videos_rights'));
	rex_perm::register('d2u_videos[edit_data]', rex_i18n::msg('d2u_videos_rights_edit_data'), rex_perm::OPTIONS);
	rex_perm::register('d2u_videos[edit_lang]', rex_i18n::msg('d2u_videos_rights_edit_lang'), rex_perm::OPTIONS);
	rex_perm::register('d2u_videos[settings]', rex_i18n::msg('d2u_videos_rights_settings'), rex_perm::OPTIONS);
}

if(\rex::isBackend()) {
	rex_extension::register('CLANG_DELETED', 'rex_d2u_videos_clang_deleted');
	rex_extension::register('MEDIA_IS_IN_USE', 'rex_d2u_videos_media_is_in_use');
}
else {
	rex_extension::register('YREWRITE_SITEMAP', 'rex_d2u_videos_sitemap');
}

/**
 * Deletes language specific configurations and objects
 * @param rex_extension_point<string> $ep Redaxo extension point
 * @return mixed Warning message as array
 */
function rex_d2u_videos_clang_deleted(rex_extension_point $ep) {
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$clang_id = $params['id'];

	// Delete
	$videos = Video::getAll($clang_id);
	foreach ($videos as $video) {
		$video->delete(false);
	}

	return $warning;
}

/**
 * Checks if media is used by this addon
 * @param rex_extension_point<string> $ep Redaxo extension point
 * @return array<mixed>|string Warning message as array
 */
function rex_d2u_videos_media_is_in_use(rex_extension_point $ep) {
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$filename = addslashes($params['filename']);

	// News
	$sql_videos = rex_sql::factory();
	$sql_videos->setQuery('SELECT lang.video_id, name FROM `' . rex::getTablePrefix() . 'd2u_videos_videos_lang` AS lang '
		.'LEFT JOIN `' . rex::getTablePrefix() . 'd2u_videos_videos` AS videos ON lang.video_id = videos.video_id '
		.'WHERE picture = "'. $filename .'" OR lang.redaxo_file = "'. $filename .'" OR videos.redaxo_file = "'. $filename .'"');  

	// Prepare warnings
	// News
	for($i = 0; $i < $sql_videos->getRows(); $i++) {
		$message = '<a href="javascript:openPage(\'index.php?page=d2u_videos/videos&func=edit&entry_id='.
			$sql_videos->getValue('video_id') .'\')">'. rex_i18n::msg('d2u_videos') .': '. $sql_videos->getValue('name') .'</a>';
		if(!in_array($message, $warning, true)) {
			$warning[] = $message;
		}
		$sql_videos->next();
    }

	// Settings
	$addon = rex_addon::get("d2u_videos");
	if($addon->hasConfig("player_js") && $addon->getConfig("player_js") === $filename) {
		$message = '<a href="javascript:openPage(\'index.php?page=d2u_videos/settings\')">'.
			 rex_i18n::msg('d2u_videos') ." ". rex_i18n::msg('d2u_helper_settings') . '</a>';
		if(is_array($warning) && !in_array($message, $warning, true)) {
			$warning[] = $message;
		}
	}
	return $warning;
}

/**
 * Adds videos to sitemap
 * @param rex_extension_point<string> $ep Redaxo extension point
 * @return array<string> updated sitemap entries
 */
function rex_d2u_videos_sitemap(rex_extension_point $ep) {
	/** @var string[] $sitemap_entries */
	$sitemap_entries = $ep->getSubject();
	
	$modules = D2UVideosModules::getModules();
	foreach($modules as $module) {
		$module->initRedaxoContext(rex_addon::get('d2u_videos'), 'modules/');
		$sql = rex_sql::factory();
		$sql->setQuery('SELECT * FROM '. rex::getTablePrefix() .'article_slice WHERE module_id = '. $module->getRedaxoId());
		for($i = 0; $i < $sql->getRows(); $i++) {
			$type = (string) $sql->getValue("value1");
			$article_id = (int) $sql->getValue("article_id");
			$clang_id = (int) $sql->getValue("clang_id");
			if($type === 'playlist') {
				$playlist_id = (int) $sql->getValue("value2");
				$playlist = new Playlist($playlist_id);
				$video_entry = '';
				foreach($playlist->videos as $playlist_video) {
					// set correct clang id
					$video = new Video($playlist_video->video_id, $clang_id, true);
					$video_entry .= $video->getSitemapEntry();
				}
				// insert into sitemap
				foreach($sitemap_entries as $sitemap_key => $sitemap_entry) {
					if(str_contains($sitemap_entry, rex_getUrl($article_id, $clang_id) .'</loc>')) {
						$sitemap_entries[$sitemap_key] = str_replace('</url>', $video_entry .'</url>', $sitemap_entry);
					}
				}
			}
			else if($type === 'video') {
				$video_id = (int) $sql->getValue("value3");
				$video = new Video($video_id, $clang_id, true);
				// insert into sitemap
				foreach($sitemap_entries as $sitemap_key => $sitemap_entry) {
					if(str_contains($sitemap_entry, rex_getUrl($article_id, $clang_id) .'</loc>')) {
						$sitemap_entries[$sitemap_key] = str_replace('</url>', $video->getSitemapEntry() .'</url>', $sitemap_entry);
					}
				}
			}
			$sql->next();
		}
		
	}

	return $sitemap_entries;
}