<h2>Changelog</h2>
<p>1.3.2:</p>
<ul>
	<li>Dokumentation/Einstellungen: Das Vidstack Addon wird jetzt durchgängig als primärer, empfohlener Videoplayer ausgewiesen. In der Player-Auswahl (Einstellungen) steht Vidstack an erster Stelle und ist mit "(empfohlen)" gekennzeichnet, Plyr und der Ultimate Video Player sind mit "(veraltet)" markiert. README und Setup-Hilfeseite wurden entsprechend angepasst (neue Sprachschlüssel <code>d2u_videos_settings_recommended</code> und <code>d2u_videos_settings_deprecated</code> in de/en/nl).</li>
	<li>Wartung: Die Backend-Seiten Einstellungen und Setup verzichten jetzt auf separate Inhaltsdateien mit <code>require</code>; der Inhalt steht direkt in <code>pages/settings.settings.php</code> und <code>pages/settings.setup.php</code>. Damit kann ein versehentliches Löschen einer Zieldatei die Seiten nicht mehr unbenutzbar machen.</li>
	<li>Bugfix: Nach erfolgreichem Löschen eines Videos wird wieder eine grüne Bestätigungsmeldung mit dem Text "Video wurde gelöscht." angezeigt.</li>
	<li>Security/Bugfix: Die <code>save()</code>-Methode in <code>lib/Video.php</code> verwendet jetzt gebundene Parameter statt SQL-String-Konkatenation (Sprach- und Nicht-Sprach-Felder wie <code>picture</code>, <code>video_type</code>, <code>youtube_video_id</code>, <code>redaxo_file</code>, <code>translation_needs_update</code>); <code>getPlaylists()</code> castet die Video-ID in der LIKE-Abfrage nach <code>int</code>. Verhindert SQL-Injection und <code>rex_sql_exception</code> bei Werten mit Anfuehrungszeichen.</li>
</ul>
<p>1.3.1:</p>
<ul>
	<li>Backend: Abbrechen-Buttons in Video- und Playlistformularen fuehren jetzt wieder zur Liste.</li>
	<li>Neuer Extension Point <code>D2U_VIDEO_IN_USE</code> hinzugefügt. Vor dem Löschen eines Videos werden jetzt Playlist-Verwendungen und externe Verwendungen über Addon-Hooks geprüft.</li>
	<li>Einstellungen erweitert: Zusätzliche Video-Verwendungsprüfungen können als JSON für eigene Datenbanktabellen/Felder konfiguriert werden. Für Slices wird dabei ein Direktlink zur betroffenen REDAXO-Slice ausgegeben.</li>
</ul>
<p>1.3.0:</p>
<ul>
	<li>Backend: CSRF-Schutz fuer Modul-Installation, -Update und -Deinstallation auf der Setup-Seite ergaenzt.</li>
	<li>Neues Modul 30-2 "D2U Videomanager - Video / Playlist (BS5)" hinzugefügt.</li>
	<li>Modul 30-1 als "(BS4, deprecated)" markiert. Die BS4-Variante wird im nächsten Major Release entfernt.</li>
	<li>Benötigt d2u_helper &gt;= 2.1.0.</li>
	<li>Bugfix: Prioritäten werden bei Videos nach dem Speichern wieder stabil neu durchnummeriert, auch wenn in der Datenbank bereits doppelte Werte vorhanden sind.</li>
	<li>Unterstützung des Vidstack Addons als weitere Player-Option hinzugefügt.</li>
	<li>Modul "30-1 D2U Videomanager - Video / Playlist" schaltet jetzt zwischen Ultimate Video Player, Plyr und Vidstack um.</li>
	<li>Vidstack Ausgabe unterstützt einzelne Videos und Playlists inklusive YouTube-Kompatibilität.</li>
	<li>Ultimate Video Player und Plyr sind als veraltet markiert und werden im nächsten Major Release nicht mehr unterstützt.</li>
	<li>Backend-Listen sortierbar gemacht und Standardsortierungen von SQL-Queries auf <code>rex_list</code>-<code>defaultSort</code> umgestellt.</li>
	<li>Die Priorität von Videos kann in der Backend-Liste jetzt direkt per Hoch-/Runter-Buttons geändert werden.</li>
	<li>Security: Die <code>media-is-in-use</code>-Extension-Points in <code>boot.php</code> verwenden jetzt gebundene Parameter statt SQL-String-Konkatenation mit <code>addslashes()</code>.</li>
	<li>Security: Die <code>save()</code>-Methoden in <code>lib/Video.php</code> und <code>lib/Playlist.php</code> verwenden jetzt gebundene Parameter statt SQL-String-Konkatenation mit <code>addslashes()</code>.</li>
	<li>Security: Modul-Ausgaben (<code>modules/30/1/output.php</code>, <code>modules/30/2/output.php</code>) härten Backend-Eingaben gegen XSS via <code>rex_escape()</code> für Playlist- und Video-Namen in HTML- und Attributausgaben.</li>
</ul>
<p>1.2.1:</p>
<ul>
	<li>Sprachspezifisches Bild hinzugefügt.</li>
	<li>Modul "30-1 D2U Videomanager - Video / Playlist" Anpassungen prachspezifisches Bild.</li>
	<li>Bugfix: Videotyp wurde in sprachbezogenen Videos nicht gespeichert.</li>
</ul>
<p>1.2.0:</p>
<ul>
	<li>Vorbereitung auf R6: Folgende Klassen wurden umbenannt. Die alten Klassennamen funktionieren weiterhin, sind aber als veraltet markiert.
		<ul>
			<li><code>Playlist</code> wird zu <code>TobiasKrais\D2UVideos\Playlist</code>.</li>
			<li><code>Video</code> wird zu <code>TobiasKrais\D2UVideos\Video</code>.</li>
			<li><code>Videomanager</code> wird zu <code>TobiasKrais\D2UVideos\Videomanager</code>.</li>
		</ul>
		Folgende interne Klasse wurden wurden ebenfalls umbenannt:
		<ul>
			<li><code>D2UVideosModules</code> wird zu <code>TobiasKrais\D2UVideos\Module</code>.</li>
		</ul>
	</li>
	<li>PHP-CS-Fixer Code Verbesserungen.</li>
	<li>Bugfix für verbesserte JSON Ausgabe.</li>
	<li>Bugfix beim Speichern von Videos und Playlists.</li>
</ul>
<p>1.1.0:</p>
<ul>
	<li>Methode für die Ausgabe von Videoinformationen in sitemap.xml hinzugefügt.
		Für eine korrekte optische Darstellung der sitemap.xml braucht es noch <a href="https://github.com/yakamara/redaxo_yrewrite/pull/521" target="_blank">diesen PR für YRewrite <=2.9.1</a>.</li>
	<li>Unterstützung des Plyr Addons als alternativen Video Player.</li>
	<li>Anpassungen an Publish Github Release to Redaxo.</li>
	<li>Bugfix: Beim Löschen von Medien die vom Addon verlinkt werden wurde der Name der verlinkenden Quelle in der Warnmeldung nicht immer korrekt angegeben.</li>
	<li>Auswahlfeld Videotyp für jeden Datensatz.</li>
	<li>Anpassung auf aktuelle Upstream Version: breite des Videos wurde bei einzelnen Video nicht mehr korrekt gesetzt.</li>
	<li>Modul "30-1 D2U Videomanager - Video / Playlist" gibt jetzt auch LD+JSON Code aus.</li>
	<li>Videoklasse verfügt nun über eine Methode die den LD+JSON Code für die Videosuchmaschine ausgibt.</li>
	<li>install.php und update.php auf Redaxo Style umgestellt und vereinheitlicht.</li>
	<li>rexstan Codelevel 9.</li>
</ul>
<p>1.0.9:</p>
<ul>
	<li>Benötigt Redaxo >= 5.10, da die neue Klasse rex_version verwendet.</li>
	<li>Bugfix: Speichern von einfachen Anführungszeichen in der Video Beschreibung.</li>
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