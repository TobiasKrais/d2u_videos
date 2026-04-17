<?php

$slice = $this->getCurrentSlice(); /** @phpstan-ignore-line */

$cols = (int) $slice->getValue(20);
if (0 === $cols) {
    $cols = 8;
}
$offset_lg_cols = (int) $slice->getValue(17);
$offset_lg = '';
if ($offset_lg_cols > 0) { /** @phpstan-ignore-line */
    $offset_lg = ' me-lg-auto ms-lg-auto ';
}

$type = (string) $slice->getValue(1);

$playlist_id = (int) $slice->getValue(2);
$playlist = new TobiasKrais\D2UVideos\Playlist($playlist_id);

$video_id = (int) $slice->getValue(3);
$video = new \TobiasKrais\D2UVideos\Video($video_id, rex_clang::getCurrentId(), true);
$videomanager = new \TobiasKrais\D2UVideos\Videomanager();
$selectedPlayer = $videomanager->getConfiguredPlayer();

if (\rex::isBackend()) {
    if ('playlist' === $type) {
        echo '<p>Gewählte Playlist: '. $playlist->name .'</p>';
    } elseif ('video' === $type) {
        echo '<p>Gewähltes Video: '. $video->name .'</p>';
    }

    if ('plyr' === $selectedPlayer && !rex_addon::get('plyr')->isAvailable()) {
        echo '<p style="color:red">'. rex_i18n::msg('d2u_videos_settings_plyr_missing') .'</p>';
    } elseif ('vidstack' === $selectedPlayer && !rex_addon::get('vidstack')->isAvailable()) {
        echo '<p style="color:red">'. rex_i18n::msg('d2u_videos_settings_vidstack_missing') .'</p>';
    }
} else {
    echo '<div class="col-12 col-lg-'. $cols . $offset_lg .'">';
    if ('playlist' === $type) {
        $videomanager->printPlaylist($playlist);
    } elseif ('video' === $type) {
        $videomanager->printVideo($video);
    }

    echo '</div>';
}
