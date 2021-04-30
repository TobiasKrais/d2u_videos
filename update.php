<?php
// Update modules
if(class_exists('D2UModuleManager')) {
	$modules = [];
	$modules[] = new D2UModule("30-1",
		"D2U Videomanager - Video / Playlist",
		1);
	$d2u_module_manager = new D2UModuleManager($modules, "", "d2u_videos");
	$d2u_module_manager->autoupdate();
}

// remove default lang setting
if (!$this->hasConfig('default_lang')) {
	$this->removeConfig('default_lang');
}
if (!$this->hasConfig('preferred_video_type')) {
	$this->removeConfig('preferred_video_type');
}

$sql = rex_sql::factory();
// Update database to 1.0.7
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."d2u_videos_videos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."d2u_videos_videos_lang` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."d2u_videos_playlists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

// Remove old columns
\rex_sql_table::get(
    \rex::getTable('d2u_videos_videos_lang'))
    ->removeColumn('updatedate')
    ->removeColumn('updateuser')
    ->ensure();

// Add columns
\rex_sql_table::get(
    \rex::getTable('d2u_videos_videos'))
    ->ensureColumn(new \rex_sql_column('video_type', 'VARCHAR(10)', TRUE))
    ->alter();
\rex_sql_table::get(
    \rex::getTable('d2u_videos_videos_lang'))
    ->ensureColumn(new \rex_sql_column('video_type', 'VARCHAR(10)', TRUE))
    ->alter();

if (rex_version::compare($this->getVersion(), '1.1.0', '<')) {
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos SET `video_type` = 'redaxo' WHERE `redaxo_file` <> '' AND `video_type` IS NULL;");
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos SET `video_type` = 'youtube' WHERE `youtube_video_id` <> '' AND `video_type` IS NULL;");
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos_lang SET `video_type` = 'redaxo' WHERE `redaxo_file` <> '' AND `video_type` IS NULL;");
	$sql->setQuery("UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos_lang SET `video_type` = 'youtube' WHERE `youtube_video_id` <> '' AND `video_type` IS NULL;");
}