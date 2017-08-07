<?php
/**
 * Redaxo Video Addon
 * @author Tobias Krais
 * @author <a href="http://www.design-to-use.de">www.design-to-use.de</a>
 */

/**
 * Video playlist class
 */
class Playlist {
	/**
	 * @var int Playlist ID
	 */
	var $playlist_id = 0;
	
	/**
	 * @var String Name
	 */
	var $name = "";
	
	/**
	 * @var Video[] Videos
	 */
	var $videos = [];
	
	/**
	 * Constructor
	 * @param int $playlist_id Playlist ID.
	 */
	 public function __construct($playlist_id) {
		$query = "SELECT * FROM ". rex::getTablePrefix() ."d2u_videos_playlists "
				."WHERE playlist_id = ". $playlist_id;
		$result = rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		if ($num_rows > 0) {
			$this->playlist_id = $playlist_id;
			$this->name = $result->getValue("name");
			$video_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("video_ids")), PREG_GREP_INVERT);
			foreach ($video_ids as $video_id) {
				$this->videos[$video_id] = new Video($video_id, rex_clang::getCurrentId());
			}
		}
	}
	
	/**
	 * Deletes the object in all languages.
	 */
	public function delete() {
		$query = "DELETE FROM ". rex::getTablePrefix() ."d2u_videos_playlists "
			."WHERE playlist_id = ". $this->playlist_id;
		$result = rex_sql::factory();
		$result->setQuery($query);
	}

	/**
	 * Get all playlists.
	 * @param int $clang_id Redaxo clang id.
	 * @return Playlist[] Array with Playlist objects.
	 */
	public static function getAll($clang_id) {
		$query = "SELECT playlist_id FROM ". rex::getTablePrefix() ."d2u_videos_playlist";
		$result = rex_sql::factory();
		$result->setQuery($query);
		
		$playlist = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$playlist[$result->getValue("playlist_id")] = new Playlist($result->getValue("playlist_id"), $clang_id);
			$result->next();
		}
		return $playlist;
	}
	
	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if successful
	 */
	public function save() {
		$error = 0;

		$query = rex::getTablePrefix() ."d2u_videos_playlists SET "
				."name = '". $this->name ."', "
				."video_ids = '". implode('|', array_keys($this->videos)) ."' ";

		if($this->playlist_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE playlist_id = ". $this->playlist_id;
		}

		$result = rex_sql::factory();
		$result->setQuery($query);
		if($this->playlist_id == 0) {
			$this->playlist_id = $result->getLastId();
			$error = $result->hasError();
		}
		
		return $error;
	}
}