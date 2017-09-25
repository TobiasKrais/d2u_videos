<?php
	$type = "REX_VALUE[1]";
	
	print '<div class="col-12">';
	if($type == "playlist") {
		$playlist_id = "REX_VALUE[2]";
		$playlist = new Playlist($playlist_id);
		
		if(rex::isBackend()) {
			print '<p>Gewählte Plalist: '. $playlist->name .'</p>';
		}
		else {
			$videomanager = new Videomanager();
			$videomanager->printPlaylist($playlist);
		}
	}
	else if($type == "video") {
		$video = new Video($video_id, rex_clang::getCurrentId());
		if(rex::isBackend()) {
			print '<p>Gewähltes Video: '. $video->name .'</p>';
		}
		else {
			$videomanager = new Videomanager();
			$videomanager->printVideo($video);
		}
	}
	print '</div>';
?>