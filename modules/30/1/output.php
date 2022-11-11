<?php
$slice = $this->getCurrentSlice(); /** @phpstan-ignore-line */

$cols = (int) $slice->getValue(20);
if($cols == 0) {
	$cols = 8;
}
$offset_lg_cols = (int) $slice->getValue(17);
$offset_lg = "";
if($offset_lg_cols > 0) {
	$offset_lg = " mr-lg-auto ml-lg-auto ";
}

$type = (string) $slice->getValue(1);

print '<div class="col-12 col-lg-'. $cols . $offset_lg .'">';
if($type == "playlist") {
	$playlist_id = (int) "REX_VALUE[2]";
	$playlist = new Playlist($playlist_id);

	if(\rex::isBackend()) {
		print '<p>Gewählte Plalist: '. $playlist->name .'</p>';
	}
	else {
		$videomanager = new Videomanager();
		$videomanager->printPlaylist($playlist);
	}
}
else if($type == "video") {
	$video_id = (int) "REX_VALUE[3]";
	$video = new Video($video_id, rex_clang::getCurrentId(), TRUE);
	if(\rex::isBackend()) {
		print '<p>Gewähltes Video: '. $video->name .'</p>';
	}
	else {
		$videomanager = new Videomanager();
		$videomanager->printVideo($video);
		print $video->getLDJSONScript();
	}
}
print '</div>';