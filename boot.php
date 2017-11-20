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

/**
 * Deletes language specific configurations and objects
 * @param rex_extension_point $ep Redaxo extension point
 * @return string[] Warning message as array
 */
function rex_d2u_videos_clang_deleted(rex_extension_point $ep) {
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$clang_id = $params['id'];

	// Delete
	$videos = Video::getAll($clang_id);
	foreach ($videos as $video) {
		$video->delete(FALSE);
	}

	return $warning;
}

/**
 * Checks if media is used by this addon
 * @param rex_extension_point $ep Redaxo extension point
 * @return string[] Warning message as array
 */
function rex_d2u_videos_media_is_in_use(rex_extension_point $ep) {
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$filename = addslashes($params['filename']);

	// Settings
	$addon = rex_addon::get("d2u_videos");
	if($addon->hasConfig("player_js") && $addon->getConfig("player_js") == $filename) {
		$message = '<a href="javascript:openPage(\'index.php?page=d2u_videos/settings\')">'.
			 rex_i18n::msg('d2u_videos') ." ". rex_i18n::msg('d2u_helper_settings') . '</a>';
		if(!in_array($message, $warning)) {
			$warning[] = $message;
		}
	}

	return $warning;
}