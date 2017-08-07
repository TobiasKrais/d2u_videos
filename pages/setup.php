<?php
/*
 * Modules
 */
$d2u_module_manager = new D2UModuleManager(D2UVideosModules::getD2UVideosModules(), "modules/", "d2u_videos");

// D2UModuleManager actions
$d2u_module_id = rex_request('d2u_module_id', 'string');
$paired_module = rex_request('pair_'. $d2u_module_id, 'int');
$function = rex_request('function', 'string');
if($d2u_module_id != "") {
	$d2u_module_manager->doActions($d2u_module_id, $function, $paired_module);
}

// D2UModuleManager show list
$d2u_module_manager->showManagerList();

/*
 * Templates
 */
?>
<h2>Beispielseiten</h2>
<ul>
	<li>Videos Addon: <a href="https://www.kaltenbach.com/de/medien/videos/" target="_blank">
		https://www.kaltenbach.com/de/medien/videos/</a>.</li>
</ul>
<h2>Support</h2>
<p>Fehlermeldungen bitte im <a href="https://github.com/TobiasKrais/d2u_videos" target="_blank">GitHub Repository</a> melden.</p>
<h2>Changelog</h2>
<p>1.0.0:</p>
<ul>
	<li>Initiale Version.</li>
</ul>