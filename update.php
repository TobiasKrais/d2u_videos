<?php
// Update modules
if(class_exists('D2UModuleManager')) {
	$modules = [];
	$modules[] = new D2UModule("30-1",
		"D2U Videomanager - Video / Playlist",
		1);
	$d2u_module_manager = new D2UModuleManager($modules, "", "d2u_references");
	$d2u_module_manager->autoupdate();
}

// remove default lang setting
if (!$this->hasConfig()) {
	$this->removeConfig('default_lang');
}

$sql = rex_sql::factory();
// Update database to 1.0.7
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."d2u_videos_videos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."d2u_videos_videos_lang` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$sql->setQuery("ALTER TABLE `". rex::getTablePrefix() ."d2u_videos_playlists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

if (rex_string::versionCompare($this->getVersion(), '1.0.8', '<')) {
	$sql->setQuery("ALTER TABLE ". \rex::getTablePrefix() ."d2u_videos_videos_lang DROP updatedate;");
	$sql->setQuery("ALTER TABLE ". \rex::getTablePrefix() ."d2u_videos_videos_lang DROP updateuser;");
}