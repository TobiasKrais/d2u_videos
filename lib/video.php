<?php
/**
 * Redaxo Videomanager Addon
 * @author Tobias Krais
 * @author <a href="http://www.design-to-use.de">www.design-to-use.de</a>
 */

/**
 * class representing database video object
 */
class Video implements \D2U_Helper\ITranslationHelper {
	/**
	 * @var int Video ID.
	 */
	var int $video_id = 0;
	
	/**
	 * @var String name
	 */
	var string $name = "";
	
	/**
	 * @var String Teaser
	 */
	var string $teaser = "";
	
	/**
	 * @var String Youtube Video ID for all langs
	 */
	var string $youtube_video_id = "";
	
	/**
	 * @var String Youtube Video ID
	 */
	var string $youtube_video_id_lang = "";
	
	/**
	 * @var String Preview picture filename
	 */
	var string $picture = "";
	
	/**
	 * @var int priority
	 */
	var int $priority = 0;
	
	/**
	 * @var string MP4 filename for all languages
	 */
	var string $redaxo_file = "";
	
	/**
	 * @var string MP4 filename
	 */
	var string $redaxo_file_lang = "";
	
	/**
	 * @var int Redaxo language ID
	 */
	var int $clang_id = 0;
	
	/**
	 * @var string "yes" if translation needs update
	 */
	var string $translation_needs_update = "delete";

	/**
	 * @var string Video type, either 'redaxo' for videos in redaxo media pool
	 * or 'youtube' for YouTube video id
	 */
	var string $video_type = "";

	/**
	 * @var string Video type, either 'redaxo' for videos in redaxo media pool
	 * or 'youtube' for YouTube video id
	 */
	var string $video_type_lang = "";

	/**
	 * @var string Video URL
	 */
	private string $video_url = "";
	
	/**
	 * @var string LD+JSON code for video search engines
	 */
	private string $ld_json = "";
	
	/**
	 * @var string XML code for sitemap
	 */
	private string $sitemap_entry = "";
	
	/**
	 * Constructor
	 * @param int $video_id Video ID.
	 * @param int $clang_id Redaxo language ID
	 * @param boolean $fallback Fallback to default lang if no localization is available
	 */
	public function __construct($video_id, $clang_id, $fallback = TRUE) {
		$this->clang_id = $clang_id;
		
		$query = "SELECT * FROM ". \rex::getTablePrefix() ."d2u_videos_videos AS videos "
				."LEFT JOIN ". \rex::getTablePrefix() ."d2u_videos_videos_lang AS lang "
					."ON lang.video_id = videos.video_id "
				."WHERE videos.video_id = ". $video_id ." "
					."AND clang_id = ". $this->clang_id;
		$result = rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			$this->video_id = $video_id;
			$this->name = stripslashes((string) $result->getValue("name"));
			$this->teaser = (string) $result->getValue("teaser");
			$this->video_type = (string) $result->getValue("videos.video_type");
			$this->video_type_lang = (string) $result->getValue("lang.video_type");
			$this->youtube_video_id = (string) $result->getValue("videos.youtube_video_id");
			$this->youtube_video_id_lang = (string) $result->getValue("lang.youtube_video_id");
			$this->redaxo_file = (string) $result->getValue("videos.redaxo_file");
			$this->redaxo_file_lang = (string) $result->getValue("lang.redaxo_file");
			$this->picture = (string) $result->getValue("picture");
			$this->priority = (int) $result->getValue("priority");
			$this->translation_needs_update = (string) $result->getValue("translation_needs_update");
		}
		else if($fallback) {
			// fallback to default lang
			$query_fallback = "SELECT * FROM ". \rex::getTablePrefix() ."d2u_videos_videos AS videos "
					."LEFT JOIN ". \rex::getTablePrefix() ."d2u_videos_videos_lang AS lang "
						."ON lang.video_id = videos.video_id "
					."WHERE videos.video_id = ". $video_id ." "
						."AND clang_id = ". rex_config::get("d2u_helper", "default_lang", rex_clang::getStartId());
			$result_fallback = rex_sql::factory();
			$result_fallback->setQuery($query_fallback);

			if ($result_fallback->getRows() > 0) {
				$this->video_id = $video_id;
				$this->name = stripslashes((string) $result_fallback->getValue("name"));
				$this->teaser = stripslashes((string) $result_fallback->getValue("teaser"));
				$this->picture = (string) $result_fallback->getValue("picture");
				$this->priority = (int) $result_fallback->getValue("priority");
				if($this->redaxo_file === "" && $this->redaxo_file_lang === "" && $this->youtube_video_id === "" && $this->youtube_video_id_lang === "") {
					$this->video_type = (string) $result_fallback->getValue("videos.video_type");
					$this->video_type_lang = (string) $result_fallback->getValue("lang.video_type");
					$this->redaxo_file = (string) $result_fallback->getValue("videos.redaxo_file");
					$this->redaxo_file_lang = (string) $result_fallback->getValue("lang.redaxo_file");
					$this->youtube_video_id = (string) $result_fallback->getValue("videos.youtube_video_id");
					$this->youtube_video_id_lang = (string) $result_fallback->getValue("lang.youtube_video_id");
				}
			}
		}
	}
	
	/**
	 * Deletes the object in all languages.
	 * @param boolean $delete_all If TRUE, all translations and main object are deleted. If 
	 * FALSE, only this translation will be deleted.
	 */
	public function delete($delete_all = true):void {
		$query_lang = "DELETE FROM ". \rex::getTablePrefix() ."d2u_videos_videos_lang "
			."WHERE video_id = ". $this->video_id
			. ($delete_all ? '' : ' AND clang_id = '. $this->clang_id) ;
		$result_lang = rex_sql::factory();
		$result_lang->setQuery($query_lang);
		
		// If no more lang objects are available, delete
		$query_main = "SELECT * FROM ". \rex::getTablePrefix() ."d2u_videos_videos_lang "
			."WHERE video_id = ". $this->video_id;
		$result_main = rex_sql::factory();
		$result_main->setQuery($query_main);
		if($result_main->getRows() === 0) {
			$query = "DELETE FROM ". \rex::getTablePrefix() ."d2u_videos_videos "
				."WHERE video_id = ". $this->video_id;
			$result = rex_sql::factory();
			$result->setQuery($query);

			// reset priorities
			$this->setPriority(TRUE);			
		}
	}

	/**
	 * Get all videos.
	 * @param int $clang_id Redaxo clang id.
	 * @return Video[] Array with Video objects.
	 */
	public static function getAll($clang_id) {
		$query = "SELECT video_id FROM ". \rex::getTablePrefix() ."d2u_videos_videos "
			."ORDER BY priority";
		$result = rex_sql::factory();
		$result->setQuery($query);
		
		$videos = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$videos[(int) $result->getValue("video_id")] = new Video((int) $result->getValue("video_id"), $clang_id);
			$result->next();
		}
		return $videos;
	}

	/**
	 * Get LD+JSON code including surrounding script tag. In case not all required
	 * data is available, an empty string is returned
	 * @return string String containing LD+JSON Code
	 */
	public function getLDJSONScript() {
		if($this->ld_json !== "") {
			return $this->ld_json;
		}
		$rex_video = rex_media::get($this->redaxo_file_lang !== "" ? $this->redaxo_file_lang : $this->redaxo_file);
		if($rex_video instanceof rex_media && $this->picture !== "") {
			$server = rtrim((rex_addon::get('yrewrite')->isAvailable() ? rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), "/");
			
			$this->ld_json .= '<script type="application/ld+json">'. PHP_EOL;
			$this->ld_json .= '{'. PHP_EOL;
			$this->ld_json .= '"@context": "https://schema.org",'. PHP_EOL;
			$this->ld_json .= '"@type": "VideoObject",'. PHP_EOL;
			$this->ld_json .= '"name": "'. $this->name .'",'. PHP_EOL;
			$this->ld_json .= '"description": "'. ($this->teaser !== "" ? $this->teaser : $this->name) .'",'. PHP_EOL;
			$this->ld_json .= '"thumbnailUrl": [ "'. $server . rex_url::media($this->picture) .'" ],'. PHP_EOL;
			$this->ld_json .= '"uploadDate": "'. date('c', $rex_video->getUpdateDate()) .'",'. PHP_EOL;
			$this->ld_json .= '"contentUrl": "'. $server . $rex_video->getUrl() .'"'. PHP_EOL;
			$this->ld_json .= '}'. PHP_EOL;
			$this->ld_json .= '</script>'. PHP_EOL;
		}
		return $this->ld_json;
	}
	
	/**
	 * Get all playlists, the video is in.
	 * @return Playlist[] Array with playlists objects.
	 */
	public function getPlaylists() {
		$query = "SELECT playlist_id FROM ". \rex::getTablePrefix() ."d2u_videos_playlists "
			."WHERE video_ids = '". $this->video_id ."' OR video_ids LIKE '%,". $this->video_id ."%' OR video_ids LIKE '%". $this->video_id .",%'";
		$result = rex_sql::factory();
		$result->setQuery($query);
		
		$playlists = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$playlists[(int) $result->getValue("playlist_id")] = new Playlist((int) $result->getValue("playlist_id"));
			$result->next();
		}
		return $playlists;
	}
	
	/**
	 * Get sitemap entry in XML format (<video:video>...</video:video>)
	 * @return string String containing XML sitemap Code
	 */
	public function getSitemapEntry() {
		if($this->sitemap_entry !== '') {
			return $this->sitemap_entry;
		}
		$rex_video = rex_media::get($this->redaxo_file_lang !== '' ? $this->redaxo_file_lang : $this->redaxo_file);
		if($rex_video instanceof rex_media && $this->picture !== '' && $this->name !== '') {
			$server = rtrim((rex_addon::get('yrewrite')->isAvailable() ? rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), '/');
			
			$this->sitemap_entry .= '	<video:video>'. PHP_EOL;
			$this->sitemap_entry .= '		<video:thumbnail_loc>'. $server . rex_url::media($this->picture) .'</video:thumbnail_loc>'. PHP_EOL;
			$this->sitemap_entry .= '		<video:title>'. $this->name .'</video:title>'. PHP_EOL;
			$this->sitemap_entry .= '		<video:description>'. ($this->teaser !== "" ? $this->teaser : $this->name) .'</video:description>'. PHP_EOL;
			$this->sitemap_entry .= '		<video:content_loc>'. $server . $rex_video->getUrl() .'</video:content_loc>'. PHP_EOL;
			$this->sitemap_entry .= '		<video:publication_date>'. date('c', $rex_video->getUpdateDate()) .'</video:publication_date>'. PHP_EOL;
			$this->sitemap_entry .= '	</video:video>'. PHP_EOL;
		}
		return $this->sitemap_entry;
	}
	
	/**
	 * Get objects concerning translation updates
	 * @param int $clang_id Redaxo language ID
	 * @param string $type 'update' or 'missing'
	 * @return Video[] Array with Video objects.
	 */
	public static function getTranslationHelperObjects($clang_id, $type) {
		$query = 'SELECT video_id FROM '. \rex::getTablePrefix() .'d2u_videos_videos_lang '
				."WHERE clang_id = ". $clang_id ." AND translation_needs_update = 'yes' "
				.'ORDER BY name';
		if($type === 'missing') {
			$query = 'SELECT main.video_id FROM '. \rex::getTablePrefix() .'d2u_videos_videos AS main '
					.'LEFT JOIN '. \rex::getTablePrefix() .'d2u_videos_videos_lang AS target_lang '
						.'ON main.video_id = target_lang.video_id AND target_lang.clang_id = '. $clang_id .' '
					.'LEFT JOIN '. \rex::getTablePrefix() .'d2u_videos_videos_lang AS default_lang '
						.'ON main.video_id = default_lang.video_id AND default_lang.clang_id = '. \rex_config::get('d2u_helper', 'default_lang') .' '
					."WHERE target_lang.video_id IS NULL "
					.'ORDER BY default_lang.name';
			$clang_id = intval(\rex_config::get('d2u_helper', 'default_lang'));
		}
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$objects = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$objects[] = new Video(intval($result->getValue("video_id")), $clang_id);
			$result->next();
		}
		
		return $objects;
    }

	/**
	 * Get video URL
	 * @return string video URL
	 */
	public function getVideoURL() {
		if($this->video_url === "") {
			$media_domain = trim(\rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : \rex::getServer(), "/");
			if($this->video_type_lang === "youtube" && $this->youtube_video_id_lang !== "") {
				$this->video_url = (strlen($this->youtube_video_id_lang) < 15 ? "https://www.youtube.com/watch?v=" : "") . $this->youtube_video_id_lang;
			}
			else if($this->video_type_lang === "redaxo" && $this->redaxo_file_lang !== "") {
				$this->video_url = $media_domain . rex_url::media($this->redaxo_file_lang);
			}
			else if($this->video_type === "youtube" && $this->youtube_video_id !== "") {
				$this->video_url = (strlen($this->youtube_video_id) < 15 ? "https://www.youtube.com/watch?v=" : "") . $this->youtube_video_id;
			}
			else if($this->redaxo_file !== "") {
				$this->video_url = $media_domain . rex_url::media($this->redaxo_file);
			}
		}
		return $this->video_url;
	}
	
	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if successful
	 */
	public function save() {
		$error = false;

		// Save the not language specific part
		$pre_save_video = new Video($this->video_id, $this->clang_id);

		// save priority, but only if new or changed
		if($this->priority !== $pre_save_video->priority || $this->video_id === 0) {
			$this->setPriority();
		}
	
		if($this->video_id === 0 || $pre_save_video !== $this) {
			$query = \rex::getTablePrefix() ."d2u_videos_videos SET "
					."picture = '". $this->picture ."', "
					."priority = ". $this->priority .", "
					."video_type = '". $this->video_type ."', "
					."youtube_video_id = '". $this->youtube_video_id ."', "
					."redaxo_file = '". $this->redaxo_file ."' ";

			if($this->video_id === 0) {
				$query = "INSERT INTO ". $query;
			}
			else {
				$query = "UPDATE ". $query ." WHERE video_id = ". $this->video_id;
			}

			$result = rex_sql::factory();
			$result->setQuery($query);
			if($this->video_id === 0) {
				$this->video_id = (int) $result->getLastId();
				$error = $result->hasError();
			}
		}
		
		if(!$error) {
			// Save the language specific part
			$pre_save_video = new Video($this->video_id, $this->clang_id);
			if($pre_save_video !== $this) {
				$query = "REPLACE INTO ". \rex::getTablePrefix() ."d2u_videos_videos_lang SET "
						."video_id = '". $this->video_id ."', "
						."clang_id = '". $this->clang_id ."', "
						."name = '". addslashes($this->name) ."', "
						."teaser = '". addslashes($this->teaser) ."', "
						."video_type = '". $this->video_type_lang ."', "
						."youtube_video_id = '". $this->youtube_video_id_lang ."', "
						."redaxo_file = '". $this->redaxo_file_lang ."', "
						."translation_needs_update = '". $this->translation_needs_update ."' ";

				$result = rex_sql::factory();
				$result->setQuery($query);
				$error = $result->hasError();
			}
		}
		
		return $error;
	}
	
	/**
	 * Reassigns priorities in database.
	 * @param boolean $delete Reorder priority after deletion
	 */
	private function setPriority($delete = FALSE):void {
		// Pull prios from database
		$query = "SELECT video_id, priority FROM ". \rex::getTablePrefix() ."d2u_videos_videos "
			."WHERE video_id <> ". $this->video_id ." ORDER BY priority";
		$result = rex_sql::factory();
		$result->setQuery($query);
		
		// When priority is too small, set at beginning
		if($this->priority <= 0) {
			$this->priority = 1;
		}
		
		// When prio is too high or was deleted, simply add at end 
		if($this->priority > $result->getRows() || $delete) {
			$this->priority = (int) $result->getRows() + 1;
		}

		$videos = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$videos[intval($result->getValue("priority"))] = intval($result->getValue("video_id"));
			$result->next();
		}
		array_splice($videos, ($this->priority - 1), 0, array($this->video_id));

		// Save all prios
		foreach($videos as $prio => $video_id) {
			$query = "UPDATE ". \rex::getTablePrefix() ."d2u_videos_videos "
					."SET priority = ". ($prio + 1) ." " // +1 because array_splice recounts at zero
					."WHERE video_id = ". $video_id;
			$result = rex_sql::factory();
			$result->setQuery($query);
		}
	}
}
