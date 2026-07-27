<?php
/*
 * Modules
 */
$d2u_module_manager = new \TobiasKrais\D2UHelper\ModuleManager(\TobiasKrais\D2UVideos\Module::getModules(), 'modules/', 'd2u_videos');

// \TobiasKrais\D2UHelper\ModuleManager actions
$d2u_module_id = rex_request('d2u_module_id', 'string');
$paired_module = rex_request('pair_'. $d2u_module_id, 'int');
$function = rex_request('function', 'string');
if ('' !== $d2u_module_id) {
    if (!\TobiasKrais\D2UHelper\BackendHelper::getPageCsrfToken()->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $d2u_module_manager->doActions($d2u_module_id, $function, $paired_module);
    }
}

// \TobiasKrais\D2UHelper\ModuleManager show list
$d2u_module_manager->showManagerList();

?>
<h2>Beispielseiten</h2>
<ul>
	<li>Videos Addon: <a href="https://www.kaltenbach.com/de/medien/videos/" target="_blank">
		https://www.kaltenbach.com/de/medien/videos/</a>.</li>
</ul>
<h2>FAQ</h2>
<ul>
	<li><b>Videos werden in Safari nicht angezeigt, anderen Browsern aber schon.</b><br>
		In der .htaccess Datei muss folgender Wert eingetragen werden:<br>
		<pre>Header set Accept-Ranges bytes</pre></li>
</ul>
<h2>Fremdcode</h2>
<p>Für den Videoplayer wird primär das <strong>Vidstack Addon</strong> empfohlen. Aus Gründen der
	Abwärtskompatibilität werden zusätzlich das Plyr Addon sowie der Ultimate Video Player unterstützt;
	beide sind jedoch als veraltet markiert und werden im nächsten Major Release entfernt. Der Ultimate
	Video Player kann hier erworben werden: <a href="http://codecanyon.net/item/ultimate-video-player/7694071">
	http://codecanyon.net/item/ultimate-video-player/7694071</a>. Aus dem gekauften
	Archiv muss die start/java/fwduvplayer.js in den Medienpool hochgeladen werden und
	in den Einstellungen dieses Addons gespeichert werden.</p>
<h2>Support</h2>
<p>Fehlermeldungen bitte im <a href="https://github.com/TobiasKrais/d2u_videos" target="_blank">GitHub Repository</a> melden.</p>