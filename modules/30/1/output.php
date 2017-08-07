<?php
	$type = "REX_VALUE[1]";
	
	print '<div class="col-12">';
	if($type == "playlist") {
		$playlist_id = "REX_VALUE[2]";
		$playlist = new Playlist($playlist_id);
		
		$videomanager = new Videomanager();
		$videomanager->printPlaylist($playlist);
	}
	else if($type == "video") {
		$video = new Video($video_id, rex_clang::getCurrentId());
		$videomanager = new Videomanager();
		$videomanager->printVideo($video);
	}
	print '</div>';
?>