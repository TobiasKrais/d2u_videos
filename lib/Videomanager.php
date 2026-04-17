<?php

namespace TobiasKrais\D2UVideos;

use rex_addon;
use rex_clang;
use rex_escape;
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
    private static bool $plyrAssetsLoaded = false;
    private static bool $vidstackAssetsLoaded = false;
    private static bool $vidstackPlaylistAssetsLoaded = false;

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
            if ($this->useVidstack()) {
                $this->printVidstackSingle($video);
                return;
            }
            if ($this->usePlyr() && $this->printPlyrSingle($video)) {
                return;
            }
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
            if ($this->useVidstack()) {
                $this->printVidstackPlaylist($proved_videos);
                return;
            }
            if ($this->usePlyr() && $this->printPlyrPlaylist($proved_videos)) {
                return;
            }
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
            if ($this->useVidstack()) {
                $this->printVidstackPlaylist($playlist->videos);
                return;
            }
            if ($this->usePlyr() && $this->printPlyrPlaylist($playlist->videos)) {
                return;
            }
            $useYoutube = $this->useYoutube($playlist->videos);
            $this->printVideoplayer($playlist->videos, count($playlist->videos) > 1 ? 'yes' : 'no', $useYoutube);
        }
    }

    /**
     * Returns selected player from addon config.
     */
    public function getConfiguredPlayer(): string
    {
        $player = (string) rex_addon::get('d2u_videos')->getConfig('player', 'ultimate');
        if ('vidstack' === $player && rex_addon::get('vidstack')->isAvailable()) {
            return 'vidstack';
        }

        if ('plyr' === $player && rex_addon::get('plyr')->isAvailable()) {
            return 'plyr';
        }

        // Prefer Vidstack automatically whenever it is installed.
        if (rex_addon::get('vidstack')->isAvailable()) {
            return 'vidstack';
        }

        if (rex_addon::get('plyr')->isAvailable()) {
            return 'plyr';
        }

        return 'ultimate';
    }

    /**
     * Checks whether configured external addon is available.
     */
    public function isConfiguredPlayerAddonAvailable(): bool
    {
        return match ($this->getConfiguredPlayer()) {
            'plyr' => rex_addon::get('plyr')->isAvailable(),
            'vidstack' => rex_addon::get('vidstack')->isAvailable(),
            default => true,
        };
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
            return;
        }
    ?>
		<script>
		if (typeof FWDUVPUtils === 'undefined' || typeof FWDUVPlayer === 'undefined') {
			console.warn('D2U Videos: FWDUVPlayer assets not available, skipping Ultimate Video Player initialization.');
		} else {
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
        }
	</script>
	<?php
    }

    /**
     * Prints plyr assets once.
     */
    private function printPlyrAssets(): void
    {
        if (self::$plyrAssetsLoaded) {
            return;
        }

        echo '<script src="'. rex_url::base('assets/addons/plyr/vendor/plyr/dist/plyr.min.js') .'"></script>';
        self::$plyrAssetsLoaded = true;
    }

    /**
     * Prints single video with plyr.
     */
    private function printPlyrSingle(Video $video): bool
    {
        $videoFilename = $this->getRedaxoFilename($video);
        if ('' === $videoFilename) {
            return false;
        }

        $plyrClass = '\\rex_plyr';
        $this->printPlyrAssets();
        echo $plyrClass::outputMedia($videoFilename, 'play-large,play,progress,current-time,duration,restart,volume,mute,pip,fullscreen', '' !== $video->getPreviewPictureFilename() ? rex_url::media($video->getPreviewPictureFilename()) : '');
        echo '<script src="'. rex_url::base('assets/addons/plyr/plyr_init.js') .'"></script>';
        echo $video->getLDJSONScript();

        return true;
    }

    /**
     * Prints playlist with plyr.
     * @param Video[] $videos
     */
    private function printPlyrPlaylist(array $videos): bool
    {
        $mediaFilenames = [];
        $ldJson = '';
        foreach ($videos as $video) {
            $videoFilename = $this->getRedaxoFilename($video);
            if ('' === $videoFilename) {
                continue;
            }
            $mediaFilenames[] = $videoFilename;
            $ldJson .= $video->getLDJSONScript();
        }

        if (0 === count($mediaFilenames)) {
            return false;
        }

        $plyrClass = '\\rex_plyr';
        $this->printPlyrAssets();
        echo $plyrClass::outputMediaPlaylist($mediaFilenames, 'play-large,play,progress,current-time,duration,restart,volume,mute,pip,fullscreen');
        echo '<script src="'. rex_url::base('assets/addons/plyr/plyr_playlist.js') .'"></script>';
        echo $ldJson;

        return true;
    }

    /**
     * Prints Vidstack assets once.
     */
    private function printVidstackAssets(): void
    {
        if (self::$vidstackAssetsLoaded) {
            return;
        }

        echo '<link rel="stylesheet" href="'. rex_url::addonAssets('vidstack', 'vidstack.css') .'">';
        echo '<link rel="stylesheet" href="'. rex_url::addonAssets('vidstack', 'vidstack_helper.css') .'">';
        echo '<script src="'. rex_url::addonAssets('vidstack', 'vidstack.js') .'"></script>';
        echo '<script src="'. rex_url::addonAssets('vidstack', 'vidstack_helper.js') .'"></script>';
        self::$vidstackAssetsLoaded = true;
    }

    /**
     * Prints Vidstack playlist assets once.
     */
    private function printVidstackPlaylistAssets(): void
    {
        if (self::$vidstackPlaylistAssetsLoaded) {
            return;
        }

        echo '<style>
            .d2u-videos-vidstack-playlist {
                display: grid;
                gap: 1rem;
            }
            .d2u-videos-vidstack-stage {
                display: grid;
                gap: 0.5rem;
            }
            .d2u-videos-vidstack-stage-title {
                font-size: 1.25rem;
                font-weight: 600;
                line-height: 1.3;
            }
            .d2u-videos-vidstack-stage-teaser {
                color: #666;
                margin: 0;
            }
            .d2u-videos-vidstack-items {
                display: grid;
                gap: 0.75rem;
                margin: 0;
                padding: 0;
                list-style: none;
            }
            .d2u-videos-vidstack-button {
                width: 100%;
                display: grid;
                grid-template-columns: 140px 1fr;
                gap: 0.75rem;
                align-items: center;
                border: 1px solid #d9d9d9;
                border-radius: 0.75rem;
                background: #fff;
                padding: 0.75rem;
                text-align: left;
            }
            .d2u-videos-vidstack-button.is-active {
                border-color: #1f6feb;
                box-shadow: 0 0 0 1px #1f6feb;
            }
            .d2u-videos-vidstack-thumb {
                aspect-ratio: 16 / 9;
                width: 100%;
                object-fit: cover;
                border-radius: 0.5rem;
                background: #111;
            }
            .d2u-videos-vidstack-thumb.is-empty {
                display: block;
            }
            .d2u-videos-vidstack-meta strong {
                display: block;
                margin-bottom: 0.25rem;
            }
            .d2u-videos-vidstack-meta span {
                display: block;
                color: #666;
            }
            .d2u-videos-vidstack-player .audio-container,
            .d2u-videos-vidstack-player .video-container,
            .d2u-videos-vidstack-player media-player {
                width: 100%;
                display: block;
            }
            @media (max-width: 767px) {
                .d2u-videos-vidstack-button {
                    grid-template-columns: 1fr;
                }
            }
        </style>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll(".d2u-videos-vidstack-playlist").forEach(function (playlist) {
                    if (playlist.dataset.vsPlaylistBound === "1") {
                        return;
                    }
                    playlist.dataset.vsPlaylistBound = "1";

                    var stageTitle = playlist.querySelector("[data-stage-title]");
                    var stageTeaser = playlist.querySelector("[data-stage-teaser]");
                    var stagePlayer = playlist.querySelector("[data-stage-player]");
                    var buttons = playlist.querySelectorAll("[data-playlist-target]");

                    buttons.forEach(function (button) {
                        button.addEventListener("click", function () {
                            var targetId = button.getAttribute("data-playlist-target");
                            var template = playlist.querySelector("template[data-playlist-template=\"" + targetId + "\"]");
                            if (!template || !stagePlayer) {
                                return;
                            }

                            stagePlayer.innerHTML = template.innerHTML;
                            if (stageTitle) {
                                stageTitle.textContent = button.getAttribute("data-title") || "";
                            }
                            if (stageTeaser) {
                                stageTeaser.textContent = button.getAttribute("data-teaser") || "";
                            }

                            buttons.forEach(function (candidate) {
                                candidate.classList.toggle("is-active", candidate === button);
                            });

                            document.dispatchEvent(new Event("vsrun"));
                        });
                    });
                });
            });
        </script>';

        self::$vidstackPlaylistAssetsLoaded = true;
    }

    /**
     * Prints single video with Vidstack.
     */
    private function printVidstackSingle(Video $video): void
    {
        $this->printVidstackAssets();
        echo '<div class="d2u-videos-vidstack-player">'. $this->getVidstackPlayerHtml($video) .'</div>';
        echo $video->getLDJSONScript();
    }

    /**
     * Prints playlist with Vidstack.
     * @param Video[] $videos
     */
    private function printVidstackPlaylist(array $videos): void
    {
        $playlistItems = [];
        $ldJson = '';
        foreach ($videos as $index => $video) {
            if (0 === $video->video_id || '' === $video->getVideoURL()) {
                continue;
            }

            $playlistItems[] = [
                'id' => 'd2u-videos-vidstack-item-'. $video->video_id .'-'. $index .'-'. random_int(1, 1000),
                'title' => $video->name,
                'teaser' => $video->teaser,
                'thumb' => $this->getThumbUrl($video),
                'player' => $this->getVidstackPlayerHtml($video),
            ];
            $ldJson .= $video->getLDJSONScript();
        }

        if (0 === count($playlistItems)) {
            return;
        }

        $firstItem = $playlistItems[0];
        $this->printVidstackAssets();
        $this->printVidstackPlaylistAssets();

        echo '<div class="d2u-videos-vidstack-playlist">';
        echo '<div class="d2u-videos-vidstack-stage">';
        echo '<div class="d2u-videos-vidstack-stage-title" data-stage-title>'. rex_escape($firstItem['title']) .'</div>';
        if ('' !== $firstItem['teaser']) {
            echo '<p class="d2u-videos-vidstack-stage-teaser" data-stage-teaser>'. rex_escape($firstItem['teaser']) .'</p>';
        } else {
            echo '<p class="d2u-videos-vidstack-stage-teaser" data-stage-teaser></p>';
        }
        echo '<div class="d2u-videos-vidstack-player" data-stage-player>'. $firstItem['player'] .'</div>';
        echo '</div>';
        echo '<ul class="d2u-videos-vidstack-items">';

        foreach ($playlistItems as $index => $item) {
            echo '<li>';
            echo '<button type="button" class="d2u-videos-vidstack-button'. (0 === $index ? ' is-active' : '') .'" data-playlist-target="'. rex_escape($item['id']) .'" data-title="'. rex_escape($item['title']) .'" data-teaser="'. rex_escape($item['teaser']) .'">';
            if ('' !== $item['thumb']) {
                echo '<img class="d2u-videos-vidstack-thumb" src="'. rex_escape($item['thumb']) .'" alt="'. rex_escape($item['title']) .'">';
            } else {
                echo '<span class="d2u-videos-vidstack-thumb is-empty"></span>';
            }
            echo '<span class="d2u-videos-vidstack-meta"><strong>'. rex_escape($item['title']) .'</strong><span>'. rex_escape($item['teaser']) .'</span></span>';
            echo '</button>';
            echo '<template data-playlist-template="'. rex_escape($item['id']) .'">'. $item['player'] .'</template>';
            echo '</li>';
        }

        echo '</ul>';
        echo '</div>';
        echo $ldJson;
    }

    /**
     * Creates the Vidstack player markup for a video object.
     */
    private function getVidstackPlayerHtml(Video $video): string
    {
        $vidstackClass = '\\FriendsOfRedaxo\\VidStack\\Video';
        $vidstackVideo = new $vidstackClass($video->getVideoURL(), $video->name, $this->getVidstackLang());
        $videoInfo = $vidstackClass::getVideoInfo($video->getVideoURL());
        $isAudio = $vidstackClass::isAudio($video->getVideoURL());

        if (!$isAudio && '' !== $video->getPreviewPictureFilename()) {
            $vidstackVideo->setPoster(rex_url::media($video->getPreviewPictureFilename()), $video->name);
        }

        if ($videoInfo['platform'] === 'youtube' || $videoInfo['platform'] === 'vimeo') {
            $vidstackVideo->setAttributes([
                'crossorigin' => '',
                'playsinline' => true,
                'controls' => false,
            ]);
        } elseif ($isAudio) {
            $vidstackVideo->setAttributes([
                'controls' => true,
                'preload' => 'metadata',
            ]);
        } else {
            $vidstackVideo->setAttributes([
                'crossorigin' => '',
                'playsinline' => true,
                'controls' => true,
                'preload' => 'metadata',
            ]);
        }

        return $vidstackVideo->generateFull();
    }

    /**
     * Returns the current Vidstack language code.
     */
    private function getVidstackLang(): string
    {
        $clang = rex_clang::getCurrent();
        if (null === $clang) {
            return 'de';
        }

        $code = strtolower((string) $clang->getCode());
        return match (substr($code, 0, 2)) {
            'en' => 'en',
            'es' => 'es',
            'fr' => 'fr',
            default => 'de',
        };
    }

    /**
     * Returns Redaxo media filename of a video if available.
     */
    private function getRedaxoFilename(Video $video): string
    {
        if ('' !== $video->redaxo_file_lang) {
            return $video->redaxo_file_lang;
        }
        if ('' !== $video->redaxo_file) {
            return $video->redaxo_file;
        }

        return '';
    }

    /**
     * Returns thumbnail URL for playlist items.
     */
    private function getThumbUrl(Video $video): string
    {
        if ('' === $video->getPreviewPictureFilename()) {
            return '';
        }

        return 'index.php?rex_media_type='. $this->video_thumb_type .'&rex_media_file='. $video->getPreviewPictureFilename();
    }

    /**
     * Checks if configured player is Plyr.
     */
    private function usePlyr(): bool
    {
        return 'plyr' === $this->getConfiguredPlayer() && rex_addon::get('plyr')->isAvailable();
    }

    /**
     * Checks if configured player is Vidstack.
     */
    private function useVidstack(): bool
    {
        return 'vidstack' === $this->getConfiguredPlayer() && rex_addon::get('vidstack')->isAvailable();
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
