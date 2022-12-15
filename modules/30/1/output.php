<?php
$slice = $this->getCurrentSlice(); /** @phpstan-ignore-line */

$cols = (int) $slice->getValue(20);
if($cols === 0) {
	$cols = 8;
}
$offset_lg_cols = (int) $slice->getValue(17);
$offset_lg = "";
if($offset_lg_cols > 0) {
	$offset_lg = " mr-lg-auto ml-lg-auto ";
}

$type = (string) $slice->getValue(1);

$playlist_id = (int) $slice->getValue(2);
$playlist = new Playlist($playlist_id);

$video_id = (int) $slice->getValue(3);
$video = new Video($video_id, rex_clang::getCurrentId(), TRUE);

if(\rex::isBackend()) {
	if($type === "playlist") {
		print '<p>Gewählte Playlist: '. $playlist->name .'</p>';
	}
	else if($type === "video") {
		print '<p>Gewähltes Video: '. $video->name .'</p>';
	}

	if(strval(rex_config::get('d2u_videos', 'player', 'ultimate')) === 'plyr' && !rex_addon::get('plyr')->isAvailable()) {
		print '<p style="color:red">Das Plyr Addon muss installiert und aktiviert sein.</p>';
	}
}
else {
	// frontend
	if(strval(rex_config::get('d2u_videos', 'player', 'ultimate')) === 'plyr' && rex_addon::get('plyr')->isAvailable()) {
		if(!function_exists('loadJsPlyr')) {
			function loadJsPlyr():void {
				print '<script src="'. rex_url::base('assets/addons/plyr/vendor/plyr/dist/plyr.min.js') .'"></script>';
			}
		}
		loadJsPlyr();
	}

	print '<div class="col-12 col-lg-'. $cols . $offset_lg .'">';
	if($type === "playlist") {
		if(rex_config::get('d2u_videos', 'player', 'ultimate') === strval('plyr') && rex_addon::get('plyr')->isAvailable()) {
			$media_filenames = [];
			$ld_json = '';
			foreach($playlist->videos as $playlist_video) {
				$media_filenames[] = $playlist_video->redaxo_file_lang !== '' ? $playlist_video->redaxo_file_lang : $playlist_video->redaxo_file;
				$ld_json .= $playlist_video->getLDJSONScript();
			}
			print rex_plyr::outputMediaPlaylist($media_filenames, 'play-large,play,progress,current-time,duration,restart,volume,mute,pip,fullscreen');
			print '<script src="'. rex_url::base('assets/addons/plyr/plyr_playlist.js') .'"></script>';			
			print $ld_json;
		}
		else {
			$videomanager = new Videomanager();
			$videomanager->printPlaylist($playlist);
		}
	}
	else if($type === "video") {
		if(strval(rex_config::get('d2u_videos', 'player', 'ultimate')) === 'plyr' && rex_addon::get('plyr')->isAvailable()) {
			$video_filename = $video->redaxo_file_lang !== '' ? $video->redaxo_file_lang : $video->redaxo_file;
			print rex_plyr::outputMedia($video_filename, 'play-large,play,progress,current-time,duration,restart,volume,mute,pip,fullscreen', rex_url::media($video->picture));	
			print '<script src="'. rex_url::base('assets/addons/plyr/plyr_init.js') .'"></script>';
		}
		else {
			$videomanager = new Videomanager();
			$videomanager->printVideo($video);
		}
		print $video->getLDJSONScript();
	}
	
	print '</div>';
}