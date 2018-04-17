<?php
/**
 * Redaxo Videomanager Addon
 * @author Tobias Krais
 * @author <a href="http://www.design-to-use.de">www.design-to-use.de</a>
 */

/**
 * Manages video outpu
 */
class Videomanager {
	/**
	 * @var int maximale Höhe des Playerfensters
	 */
	var $max_height = 440;
	
	/**
	 * @var int maximale Breite des Playerfensters
	 */
	var $max_width = 1180;

	/**
	 * @var String Playlist position ("left" or "right")
	 */
	var $playlist_position = "right";
	
	/**
	 * @var int Playlist width
	 */
	var $playlist_width = 400;

	/**
	 * @var String Image Manager Type video thumbs
	 */
	var $video_thumb_type = "d2u_videos_thumb";
	
	/**
	 * @var String Image Manager Type video preview
	 */
	var $video_preview_type = "d2u_videos_preview";

	/**
	 * Constructor.
	 */
	public function __construct() {
		$d2u_videos = rex_addon::get('d2u_videos');
		$this->max_height = $d2u_videos->getConfig('max_height');
		$this->max_width = $d2u_videos->getConfig('max_width');
	}
	
	/**
	 * Prints single video
	 * @param Video $video Video Objekt
	 */
	function printVideo($video) {
		if($video->video_id > 0) {
			$useYoutube = $this->useYoutube([$video]);
			$this->printVideoplayer([$video], "no", $useYoutube);
		}
	}

	/**
	 * Prints multiple videos
	 * @param Video[] $videos Video Objekte
	 */
	function printVideos($videos) {
		if(count($videos) > 0) {
			$useYoutube = $this->useYoutube($videos);
			$this->printVideoplayer($videos, (count($videos) > 1 ? "yes" : "no"), $useYoutube);
		}
	}

	/**
	 * Prints playlist
	 * @param Playlist $playlist Playlist Objekt
	 */
	function printPlaylist($playlist) {
		if(count($playlist->videos) > 0) {
			$useYoutube = $this->useYoutube($playlist->videos);
			$this->printVideoplayer($playlist->videos, (count($playlist->videos) > 1 ? "yes" : "no"), $useYoutube);
		}
	}

	/**
	 * Prints necessary JS part
	 * @param int $playerID Unique player instance ID. Necessary if multiple
	 * players should be displayed on one page.
	 * @param String $showPlaylist "yes" oder "no". Last option if only one video should be played
	 * @param String $useYoutube "yes" if youtube video is in playlist, "no" if not. Default is "no".
	 */
	private function printJS($playerID, $showPlaylist, $useYoutube = "no") {
		$d2u_videos = rex_addon::get('d2u_videos');
		if($d2u_videos->hasConfig('player_js') && $d2u_videos->getConfig('player_js') != '') {
			print '<script src="'. rex_url::media($d2u_videos->getConfig('player_js')) .'"></script>';
		}
		else {
			print '<p>D2U Videos settings incomplete. Please upload FWDUVPlayer.js to media pool and complete settings.</p>';
		}

	?>
		<script type="text/javascript">
		FWDUVPUtils.onReady(function(){
			FWDUVPlayer.useYoutube = "<?php print $useYoutube; ?>";
			new FWDUVPlayer({		
				//main settings
				instanceName:"player<?php print $playerID; ?>",
				parentId:"videoplayer<?php print $playerID; ?>",
				playlistsId:"playlists<?php print $playerID; ?>",
				mainFolderPath:"<?php print $d2u_videos->getAssetsUrl(); ?>",
				skinPath:"minimal_skin_dark",
				displayType:"responsive",
				useDeepLinking:"yes",
				addKeyboardSupport:"yes",
				autoScale:"yes",
				showButtonsToolTip:"yes",
				autoPlay:"no",
				loop:"no",
				shuffle:"no",
				maxWidth:<?php print $this->max_width; ?>,
				maxHeight:<?php print $this->max_height; ?>,
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
				showPlaylistButtonAndPlaylist:"<?php print $showPlaylist; ?>",
				playlistPosition:"<?php print $this->playlist_position; ?>",
				showPlaylistByDefault:"<?php print $showPlaylist; ?>",
				showPlaylistName:"no",
				showSearchInput:"no",
				showLoopButton:"no",
				showShuffleButton:"no",
				showNextAndPrevButtons:"<?php print $showPlaylist; ?>",
				forceDisableDownloadButtonForFolder:"yes",
				addMouseWheelSupport:"yes",
				folderVideoLabel:"VIDEO ",
				playlistRightWidth:<?php print $this->playlist_width; ?>,
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
	 * Gibt die <div> für den Player aus
	 * @param int $playerID Unique ID der Playerinstanz. Nötig um auf einer Seite
	 * mehrere Instanzen des Player betreiben zu können.
	 */
	private function printPlayerDiv($playerID) {
		print '<div id="videoplayer'. $playerID .'" style="position:relative; left:0px; top:0px;"></div>';
	}
	
	/**
	 * Gibt die Videos im Array aus.
	 * @param Video[] $videos Auszugebende Videos.
	 * @param String $showPlaylist "yes" = Playlist wird angezeigt, "no" = Playlist wird nicht angezeigt
	 * @param String $useYoutube "yes" wenn Youtube eingebunden werden soll, "no" wenn nicht. Default: "no"
	 */
	private function printVideoplayer($videos, $showPlaylist = "yes", $useYoutube = "no") {
		// Für data-source der Playlist und zur Unterscheidung bei mehreren Playlists auf einer Seite
		$playerID = rand(1, 1000);

		$this->printJS($playerID, $showPlaylist, $useYoutube);
		$this->printPlayerDiv($playerID);

		$playlist_start = '<ul id="playlists'. $playerID .'" style="display:none;">';
		$playlist_inhalt = '<ul id="'. $playerID .'" style="display:none;">';

		$videocounter = 0;
		foreach ($videos as $video) {
			// Videoobjekt initialisieren
			if($video->video_id == 0) {
				continue;
			}
			
			// ycom/auth_media permissions
			$rex_video = FALSE;
			if(($this->youtube_video_id != "" && (rex_config::get('d2u_videos', 'preferred_video_type') == 'youtube') || ($this->redaxo_file == "" && $this->redaxo_file_lang == ""))) {
				$rex_video = FALSE;
			}
			else if($this->redaxo_file_lang != "") {
				$rex_video = rex_media::get($video->redaxo_file_lang);
			}
			else if($this->redaxo_file != "") {
				$rex_video = rex_url::media($this->redaxo_file);
			}
			if($rex_video instanceof rex_media && rex_plugin::get('ycom', 'auth_media')->isAvailable() && !rex_ycom_auth_media::checkPerm($rex_video)) {
				continue;
			}

			// Standard URLs für Bilder
			$bild_filename_thumb = 'index.php?rex_media_type='. $this->video_thumb_type .'&rex_media_file='. $video->picture;
			$bild_filename_preview = 'index.php?rex_media_type='. $this->video_preview_type .'&rex_media_file='. $video->picture;

			if($videocounter == 0) {
				$playlist_start .= '<li data-source="'. $playerID .'" data-playlist-name="'. $video->teaser .'" data-thumbnail-path="'. $bild_filename_preview .'">';
			}
			$playlist_inhalt .=  '<li data-thumb-source="'. $bild_filename_thumb
				.'" data-video-source="'. $video->getVideoURL() .'" data-poster-source="'.
				$bild_filename_preview .'" data-downloadable="yes">';

			// Rest der Ausgabe
			if($videocounter == 0) {
				$playlist_start .= '<p class="minimalDarkCategoriesTitle"><span class="minimalDarkBold">'. $video->teaser .'</span></p>';
				$playlist_start .= '</li>';
			}

			$playlist_inhalt .=  '<div data-video-short-description="">';
			$playlist_inhalt .=  '<div>';
			$playlist_inhalt .=  '<p class="minimalDarkThumbnailTitle">'. $video->name .'</p>';
			$playlist_inhalt .=  '<p class="minimalDarkThumbnailDesc">'. $video->teaser .'</p>';
			$playlist_inhalt .=  '</div>';
			$playlist_inhalt .=  '</div>';
			$playlist_inhalt .=  '</li>';
			$videocounter++;
		}
		$playlist_start .= '</ul>';
		$playlist_inhalt .= '</ul>';

		print $playlist_start . $playlist_inhalt;
	}
	
	/**
	 * Gibt an, ob sich in dem Videoarray ein Video von YouTube befindet.
	 * @param Video[] $videos Array mit Video Objekten
	 * @return String "no", wenn kein Youtube Video im Array ist, "yes" wenn
	 * mindestens ein Youtube Video im Array ist.
	 */
	private function useYoutube($videos) {
		foreach($videos as $video) {
			if($video->youtube_video_id != "") {
				return "yes";
			}
		}
		return "no";
	}	
}