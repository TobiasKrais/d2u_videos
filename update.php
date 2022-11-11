<?php
rex_addon::get('d2u_videos')->includeFile(__DIR__.'/install.php');

// remove default lang setting
if (rex_config::has('d2u_videos', 'default_lang')) {
	rex_config::remove('d2u_videos', 'default_lang');
}
if (rex_config::has('d2u_videos', 'preferred_video_type')) {
	rex_config::remove('d2u_videos', 'preferred_video_type');
}

// Remove old columns
\rex_sql_table::get(
    \rex::getTable('d2u_videos_videos_lang'))
    ->removeColumn('updatedate')
    ->removeColumn('updateuser')
    ->ensure();

if (rex_version::compare(rex_addon::get('d2u_videos')->getVersion(), '1.1.0', '<')) {
	$sql = rex_sql::factory();
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos SET `video_type` = 'redaxo' WHERE `redaxo_file` <> '' AND `video_type` IS NULL;");
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos SET `video_type` = 'youtube' WHERE `youtube_video_id` <> '' AND `video_type` IS NULL;");
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos_lang SET `video_type` = 'redaxo' WHERE `redaxo_file` <> '' AND `video_type` IS NULL;");
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos_lang SET `video_type` = 'youtube' WHERE `youtube_video_id` <> '' AND `video_type` IS NULL;");
}