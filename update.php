<?php
// Update modules
if(class_exists(D2UModuleManager)) {
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