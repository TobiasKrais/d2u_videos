<?php

namespace TobiasKrais\D2UVideos;

use rex_addon;
use rex_media;
use rex_media_manager;
use rex_plugin;
use rex_url;
use rex_ycom_media_auth;

/**
 * Redaxo Videomanager Addon.
 * @author Tobias Krais
 * @author <a href="http://www.design-to-use.de">www.design-to-use.de</a>
 */

/**
 * @api
 * Manages video output
 */
class Videomanager
{
    /** @var int Max. player window height */
    public int $max_height = 440;

    /** @var int Max. player window width including playlist width */
    public int $max_width = 1180;

    /** @var string Playlist position ("left" or "right") */
    public string $playlist_position = 'right';

    /** @var int Playlist width */
    public int $playlist_width = 400;

    /** @var string Image Manager Type video thumbs */
    public string $video_thumb_type = 'd2u_videos_thumb';

    /** @var string Image Manager Type video preview */
    public string $video_preview_type = 'd2u_videos_preview';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $d2u_videos = rex_addon::get('d2u_videos');
        $this->max_height = (int) $d2u_videos->getConfig('max_height');
        $this->max_width = (int) $d2u_videos->getConfig('max_width');
    }

    /**
     * Prints single video.
     * @param Video $video Video Objekt
     */
    public function printVideo($video): void
    {
        if ($video->video_id > 0 && '' !== $video->getVideoURL()) {
            $useYoutube = $this->useYoutube([$video]);
            $this->printVideoplayer([$video], 'no', $useYoutube);
        }
    }

    /**
     * Prints multiple videos.
     * @param Video[] $videos Video Objekte
     * @api
     */
    public function printVideos($videos): void
    {
        $proved_videos = [];
        foreach ($videos as $video) {
            if ($video->video_id > 0 && '' !== $video->getVideoURL()) {
                $proved_videos[] = $video;
            }
        }
        if (count($proved_videos) > 0) {
            $useYoutube = $this->useYoutube($proved_videos);
            $this->printVideoplayer($proved_videos, count($proved_videos) > 1 ? 'yes' : 'no', $useYoutube);
        }
    }

    /**
     * Prints playlist.
     * @param Playlist $playlist Playlist Objekt
     */
    public function printPlaylist($playlist): void
    {
        if (count($playlist->videos) > 0) {
            $useYoutube = $this->useYoutube($playlist->videos);
            $this->printVideoplayer($playlist->videos, count($playlist->videos) > 1 ? 'yes' : 'no', $useYoutube);
        }
    }

    /**
     * Prints necessary JS part.
     * @param int $playerID Unique player instance ID. Necessary if multiple
     * players should be displayed on one page.
     * @param string $showPlaylist "yes" oder "no". Last option if only one video should be played
     * @param string $useYoutube "yes" if youtube video is in playlist, "no" if not. Default is "no".
     */
    private function printJS($playerID, $showPlaylist, $useYoutube = 'no'): void
    {
        $d2u_videos = rex_addon::get('d2u_videos');
        if ($d2u_videos->hasConfig('player_js') && '' !== $d2u_videos->getConfig('player_js')) {
            echo '<script src="'. rex_url::media((string) $d2u_videos->getConfig('player_js')) .'"></script>';
        } else {
            echo '<p>D2U Videos settings incomplete. Please upload FWDUVPlayer.js to media pool and complete settings.</p>';
        }
    ?>
		<script>
		FWDUVPUtils.onReady(function(){
			FWDUVPlayer.useYoutube = "<?= $useYoutube ?>";
			new FWDUVPlayer({
				//main settings
				instanceName:"player<?= $playerID ?>",
				parentId:"videoplayer<?= $playerID ?>",
				playlistsId:"playlists<?= $playerID ?>",
				mainFolderPath:"<?= $d2u_videos->getAssetsUrl() ?>",
				skinPath:"minimal_skin_dark",
				displayType:"responsive",
				useDeepLinking:"yes",
				addKeyboardSupport:"yes",
				autoScale:"yes",
				showButtonsToolTip:"yes",
				autoPlay:"no",
				loop:"no",
				shuffle:"no",
				maxWidth:<?= $this->max_width ?>,
				maxHeight:<?= $this->max_height ?>,
				buttonsToolTipHideDelay:1.5,
				volume:.8,
				backgroundColor:"#000",
				videoBackgroundColor:"#000",
				posterBackgroundColor:"#000",
				buttonsToolTipFontColor:"#5a5a5a",
				//logo settings
				showLogo:"no",
				//playlists/categories settings
				showPlaylistsButtonAndPlaylists:"no",
				showPlaylistsByDefault:"no",
				thumbnailSelectedType:"opacity",
				startAtPlaylist:0,
				buttonsMargins:0,
				thumbnailMaxWidth:350,
				thumbnailMaxHeight:350,
				horizontalSpaceBetweenThumbnails:40,
				verticalSpaceBetweenThumbnails:40,
				//playlist settings
				showPlaylistButtonAndPlaylist:"<?= $showPlaylist ?>",
				playlistPosition:"<?= $this->playlist_position ?>",
				showPlaylistByDefault:"<?= $showPlaylist ?>",
				showPlaylistName:"no",
				showSearchInput:"no",
				showLoopButton:"no",
				showShuffleButton:"no",
				showNextAndPrevButtons:"<?= $showPlaylist ?>",
				forceDisableDownloadButtonForFolder:"yes",
				addMouseWheelSupport:"yes",
				folderVideoLabel:"VIDEO ",
				playlistRightWidth:<?= $this->playlist_width ?>,
				playlistBottomHeight:599,
				startAtVideo:0,
				maxPlaylistItems:50,
				thumbnailWidth:100,
				thumbnailHeight:61,
				spaceBetweenControllerAndPlaylist:2,
				spaceBetweenThumbnails:2,
				scrollbarOffestWidth:8,
				scollbarSpeedSensitivity:.5,
				playlistBackgroundColor:"#000",
				playlistNameColor:"#FFFFFF",
				thumbnailNormalBackgroundColor:"#1b1b1b",
				thumbnailHoverBackgroundColor:"#313131",
				thumbnailDisabledBackgroundColor:"#272727",
				searchInputBackgroundColor:"#000000",
				searchInputColor:"#999999",
				youtubeAndFolderVideoTitleColor:"#FFFFFF",
				youtubeOwnerColor:"#888888",
				youtubeDescriptionColor:"#888888",
				//controller settings
				showControllerWhenVideoIsStopped:"yes",
				showNextAndPrevButtonsInController:"no",
				showVolumeButton:"yes",
				showTime:"yes",
				showYoutubeQualityButton:"yes",
				showInfoButton:"no",
				showDownloadButton:"no",
				showFacebookButton:"no",
				showEmbedButton:"no",
				showFullScreenButton:"yes",
				repeatBackground:"yes",
				controllerHeight:37,
				controllerHideDelay:3,
				startSpaceBetweenButtons:7,
				spaceBetweenButtons:8,
				scrubbersOffsetWidth:2,
				mainScrubberOffestTop:14,
				timeOffsetLeftWidth:5,
				timeOffsetRightWidth:3,
				timeOffsetTop:0,
				volumeScrubberHeight:80,
				volumeScrubberOfsetHeight:12,
				timeColor:"#888888",
				youtubeQualityButtonNormalColor:"#888888",
				youtubeQualityButtonSelectedColor:"#FFFFFF",
				//embed window and info window
				embedAndInfoWindowCloseButtonMargins:0,
				borderColor:"#333333",
				mainLabelsColor:"#FFFFFF",
				secondaryLabelsColor:"#a1a1a1",
				shareAndEmbedTextColor:"#5a5a5a",
				inputBackgroundColor:"#000000",
				inputColor:"#FFFFFF"
			});
		});
	</script>
	<?php
    }

    /**
     * Gibt die <div> für den Player aus.
     * @param int $playerID Unique ID der Playerinstanz. Nötig um auf einer Seite
     * mehrere Instanzen des Player betreiben zu können.
     */
    private function printPlayerDiv($playerID): void
    {
        echo '<div id="videoplayer'. $playerID .'" style="position:relative; left:0px; top:0px;"></div>'. PHP_EOL;
    }

    /**
     * Gibt die Videos im Array aus.
     * @param Video[] $videos auszugebende Videos
     * @param string $showPlaylist "yes" = Playlist wird angezeigt, "no" = Playlist wird nicht angezeigt
     * @param string $useYoutube "yes" wenn Youtube eingebunden werden soll, "no" wenn nicht. Default: "no"
     */
    private function printVideoplayer($videos, $showPlaylist = 'yes', $useYoutube = 'no'): void
    {
        // Für data-source der Playlist und zur Unterscheidung bei mehreren Playlists auf einer Seite
        $playerID = random_int(1, 1000);

        $this->printJS($playerID, $showPlaylist, $useYoutube);
        $this->printPlayerDiv($playerID);

        $playlist_start = '<ul id="playlists'. $playerID .'" style="display:none;">'. PHP_EOL;
        $playlist_inhalt = '<ul id="'. $playerID .'" style="display:none;">'. PHP_EOL;

        $videocounter = 0;
        foreach ($videos as $video) {
            // Videoobjekt initialisieren
            if (0 === $video->video_id || '' === $video->getVideoURL()) {
                continue;
            }

            // ycom/auth_media permissions
            $rex_video = false;
            if (('youtube' === $video->video_type_lang && '' !== $video->youtube_video_id_lang) || ('youtube' === $video->video_type && '' !== $video->youtube_video_id)) {
                $rex_video = false;
            } elseif ('' !== $video->redaxo_file_lang) {
                $rex_video = rex_media::get($video->redaxo_file_lang);
            } elseif ('' !== $video->redaxo_file) {
                $rex_video = rex_media::get($video->redaxo_file);
            }

            // Check media permissions
            if ($rex_video instanceof rex_media && rex_plugin::get('ycom', 'media_auth')->isAvailable() && !rex_ycom_media_auth::checkPerm(rex_media_manager::create('', $rex_video->getFileName()))) {
                continue;
            }

            // Standard URLs für Bilder
            $fallback_background = rex_url::addonAssets('d2u_videos', 'minimal_skin_dark/thumbnail-background.png');

            $picture_thumb = '' !== $video->getPreviewPictureFilename() ? 'index.php?rex_media_type='. $this->video_thumb_type .'&rex_media_file='. $video->getPreviewPictureFilename() : $fallback_background;
            $picture_preview = '' !== $video->getPreviewPictureFilename() ? 'index.php?rex_media_type='. $this->video_preview_type .'&rex_media_file='. $video->getPreviewPictureFilename() : $fallback_background;

            if (0 === $videocounter) {
                $playlist_start .= '<li data-source="'. $playerID .'" data-playlist-name="'. $video->teaser .'" data-thumbnail-path="'. $picture_preview .'">';
            }
            $playlist_inhalt .= '<li data-thumb-source="'. $picture_thumb .'" data-video-source="'. $video->getVideoURL() .'" data-poster-source="'. $picture_preview .'" data-downloadable="yes">';

            // Rest der Ausgabe
            if (0 === $videocounter) {
                $playlist_start .= '<p class="minimalDarkCategoriesTitle"><span class="minimalDarkBold">'. $video->teaser .'</span></p>';
                $playlist_start .= '</li>'. PHP_EOL;
            }

            $playlist_inhalt .= '<div data-video-short-description="">';
            $playlist_inhalt .= '<div>';
            $playlist_inhalt .= '<p class="minimalDarkThumbnailTitle">'. $video->name .'</p>';
            $playlist_inhalt .= '<p class="minimalDarkThumbnailDesc">'. $video->teaser .'</p>';
            $playlist_inhalt .= '</div>';
            $playlist_inhalt .= '</div>';
            $playlist_inhalt .= $video->getLDJSONScript();
            $playlist_inhalt .= '</li>'. PHP_EOL;
            ++$videocounter;
        }
        $playlist_start .= '</ul>'. PHP_EOL;
        $playlist_inhalt .= '</ul>'. PHP_EOL;

        echo $playlist_start . $playlist_inhalt;
    }

    /**
     * Gibt an, ob sich in dem Videoarray ein Video von YouTube befindet.
     * @param Video[] $videos Array mit Video Objekten
     * @return string "no", wenn kein Youtube Video im Array ist, "yes" wenn
     * mindestens ein Youtube Video im Array ist
     */
    private function useYoutube($videos)
    {
        foreach ($videos as $video) {
            if ('' !== $video->youtube_video_id_lang || '' !== $video->youtube_video_id) {
                return 'yes';
            }
        }
        return 'no';
    }
}
