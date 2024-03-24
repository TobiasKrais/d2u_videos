<?php

$slice = $this->getCurrentSlice(); /** @phpstan-ignore-line */

$cols = (int) $slice->getValue(20);
if (0 === $cols) {
    $cols = 8;
}
$offset_lg_cols = (int) $slice->getValue(17);
$offset_lg = '';
if ($offset_lg_cols > 0) { /** @phpstan-ignore-line */
    $offset_lg = ' mr-lg-auto ml-lg-auto ';
}

$type = (string) $slice->getValue(1);

$playlist_id = (int) $slice->getValue(2);
$playlist = new TobiasKrais\D2UVideos\Playlist($playlist_id);

$video_id = (int) $slice->getValue(3);
$video = new TobiasKrais\D2UVideos\Video($video_id, rex_clang::getCurrentId(), true);

if (\rex::isBackend()) {
    if ('playlist' === $type) {
        echo '<p>Gewählte Playlist: '. $playlist->name .'</p>';
    } elseif ('video' === $type) {
        echo '<p>Gewähltes Video: '. $video->name .'</p>';
    }

    if ('plyr' === (string) rex_config::get('d2u_videos', 'player', 'ultimate') && !rex_addon::get('plyr')->isAvailable()) {
        echo '<p style="color:red">Das Plyr Addon muss installiert und aktiviert sein.</p>';
    }
} else {
    // frontend
    if ('plyr' === (string) rex_config::get('d2u_videos', 'player', 'ultimate') && rex_addon::get('plyr')->isAvailable()) {
        if (!function_exists('loadJsPlyr')) {
            function loadJsPlyr(): void
            {
                echo '<script src="'. rex_url::base('assets/addons/plyr/vendor/plyr/dist/plyr.min.js') .'"></script>';
            }
        }
        loadJsPlyr();
    }

    echo '<div class="col-12 col-lg-'. $cols . $offset_lg .'">';
    if ('playlist' === $type) {
        if (rex_config::get('d2u_videos', 'player', 'ultimate') === 'plyr' && rex_addon::get('plyr')->isAvailable()) {
            $media_filenames = [];
            $ld_json = '';
            foreach ($playlist->videos as $playlist_video) {
                $media_filenames[] = '' !== $playlist_video->redaxo_file_lang ? $playlist_video->redaxo_file_lang : $playlist_video->redaxo_file;
                $ld_json .= $playlist_video->getLDJSONScript();
            }
            echo rex_plyr::outputMediaPlaylist($media_filenames, 'play-large,play,progress,current-time,duration,restart,volume,mute,pip,fullscreen');
            echo '<script src="'. rex_url::base('assets/addons/plyr/plyr_playlist.js') .'"></script>';
            echo $ld_json;
        } else {
            $videomanager = new TobiasKrais\D2UVideos\Videomanager();
            $videomanager->printPlaylist($playlist);
        }
    } elseif ('video' === $type) {
        if ('plyr' === (string) rex_config::get('d2u_videos', 'player', 'ultimate') && rex_addon::get('plyr')->isAvailable()) {
            $video_filename = '' !== $video->redaxo_file_lang ? $video->redaxo_file_lang : $video->redaxo_file;
            echo rex_plyr::outputMedia($video_filename, 'play-large,play,progress,current-time,duration,restart,volume,mute,pip,fullscreen', rex_url::media($video->picture));
            echo '<script src="'. rex_url::base('assets/addons/plyr/plyr_init.js') .'"></script>';
        } else {
            $videomanager = new TobiasKrais\D2UVideos\Videomanager();
            $videomanager->printVideo($video);
        }
        echo $video->getLDJSONScript();
    }

    echo '</div>';
}
