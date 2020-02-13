<?php
/*
 * Modules
 */
$d2u_module_manager = new D2UModuleManager(D2UVideosModules::getModules(), "modules/", "d2u_videos");

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
<h2>FAQ</h2>
<ul>
	<li><b>Videos werden in Safari nicht angezeigt, anderen Browsern aber schon.</b><br>
		In der .htaccess Datei muss folgender Wert eingetragen werden:<br>
		<pre>Header set Accept-Ranges bytes</pre></li>
</ul>
<h2>Fremdcode</h2>
<p>Für den Videoplayer braucht es den Ultimate Video Player, der hier erworben
	werden kann: <a href="http://codecanyon.net/item/ultimate-video-player/7694071">
	http://codecanyon.net/item/ultimate-video-player/7694071</a>. Aus dem gekauften
	Archiv muss die start/java/fwduvplayer.js in den Medienpool hochgeladen werden und
	in den Einstellungen dieses Addons gespeichert werden.</p>
<h2>Support</h2>
<p>Fehlermeldungen bitte im <a href="https://github.com/TobiasKrais/d2u_videos" target="_blank">GitHub Repository</a> melden.</p>
<h2>Changelog</h2>
<p>1.0.9-DEV:</p>
<ul>
	<li>Bugfix speichern von einfachen Anführungszeichen in der Video Beschreibung.</li>
</ul>
<p>1.0.8:</p>
<ul>
	<li>Anpassungen an aktuelles Upstream Release: aufgrund eines Fehlers im Upstream Release funktionieren bisher genutzte relative URLs für Dateien aus dem Medienpool nicht mehr. Deshalb werden jetzt auch für Videos aus dem Medienpool absolute URLs ausgegeben.</li>
	<li>Backend: Einstellungen und Setup Tabs rechts eingeordnet um sie vom Inhalt besser zu unterscheiden.</li>
	<li>ycom/media_auth Rechte werden geprüft und Video im Fall dass keine Benutzerrechte bestehen nicht ausgegeben.</li>
	<li>Nicht benötigte Felder "updatedate" und "updateuser" entfernt.</li>
</ul>
<p>1.0.7:</p>
<ul>
	<li>Listen im Backend werden jetzt nicht mehr in Seiten unterteilt.</li>
	<li>Konvertierung der Datenbanktabellen zu utf8mb4.</li>
	<li>Bugfix: Safari meldete "Undefined Property".</li>
	<li>Bugfix: bei Videosplayer ohne Playlist wurde unterer Teil des Videos vom Player abgeschnitten.</li>
	<li>Bugfix: Videos ohne Vorschaubild konnten nicht abgespielt werden.</li>
	<li>Bugfix: Videos ohne Video-URL in einer Sprache konnten in Videoliste gelangen und verursachten Fehler.</li>
</ul>
<p>1.0.6:</p>
<ul>
	<li>Sprachdetails werden ausgeblendet, wenn Speicherung der Sprache nicht vorgesehen ist.</li>
	<li>Bugfix: YouTube Videos wurden mit aktuellen Player nicht mehr dargestellt, wenn nur die YouTube Video ID angegeben war.</li>
	<li>Bugfix: Sprachspezifische YouTube Video ID wurde nicht korrekt gespeichert.</li>
	<li>Bugfix: Prioritäten wurden beim Löschen nicht reorganisiert.</li>
</ul>
<p>1.0.5:</p>
<ul>
	<li>Bilder für die aktuelle Upstream Version hinzugefügt.</li>
</ul>
<p>1.0.4:</p>
<ul>
	<li>Zeigt playlist nur wenn mehr als ein Video vorhanden ist.</li>
	<li>Bugfix: Speichern von Namen mit einfachem Anführungszeichen führte zu Fehler.</li>
	<li>Bugfix: Änderungen in der Playlist wurde nicht richtig gespeichert.</li>
</ul>
<p>1.0.3:</p>
<ul>
	<li>Zeigt playlist nur wenn mehr als ein Video vorhanden ist.</li>
	<li>Bugfix: Speichern von Namen mit einfachem Anführungszeichen führte zu Fehler.</li>
	<li>Bugfix: Änderungen in der Playlist wurde nicht richtig gespeichert.</li>
</ul>
<p>1.0.2:</p>
<ul>
	<li>Bugfix: Preview Image Type war zu klein.</li>
	<li>Update für Ultimate Video Player 4.6.</li>
	<li>Englische Übersetzung des Backends hinzugefügt.</li>
	<li>ycom/auth_media Rechte werden geprüft und Video im Fall nicht ausgegeben.</li>
</ul>
<p>1.0.1:</p>
<ul>
	<li>D2U Helper Übersetzungshilfe integriert.</li>
	<li>Editierrechte für Übersetzer eingeschränkt.</li>
</ul>
<p>1.0.0:</p>
<ul>
	<li>Initiale Version.</li>
</ul>