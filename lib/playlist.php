<?php
/**
 * Redaxo Video Addon.
 * @author Tobias Krais
 * @author <a href="http://www.design-to-use.de">www.design-to-use.de</a>
 */

/**
 * Video playlist class.
 */
class Playlist
{
    /** @var int Playlist ID */
    public int $playlist_id = 0;

    /** @var string Name */
    public string $name = '';

    /** @var Video[] Videos */
    public $videos = [];

    /**
     * Constructor.
     * @param int $playlist_id playlist ID
     */
    public function __construct($playlist_id)
    {
        $query = 'SELECT * FROM '. \rex::getTablePrefix() .'d2u_videos_playlists '
                .'WHERE playlist_id = '. $playlist_id;
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        if ($num_rows > 0) {
            $this->playlist_id = $playlist_id;
            $this->name = stripslashes((string) $result->getValue('name'));
            $video_ids = preg_grep('/^\s*$/s', explode('|', (string) $result->getValue('video_ids')), PREG_GREP_INVERT);
            if (is_array($video_ids)) {
                foreach ($video_ids as $video_id) {
                    $this->videos[$video_id] = new Video($video_id, rex_clang::getCurrentId());
                }
            }
        }
    }

    /**
     * Deletes the object in all languages.
     */
    public function delete(): void
    {
        $query = 'DELETE FROM '. \rex::getTablePrefix() .'d2u_videos_playlists '
            .'WHERE playlist_id = '. $this->playlist_id;
        $result = rex_sql::factory();
        $result->setQuery($query);
    }

    /**
     * Get all playlists.
     * @param int $clang_id redaxo clang id
     * @return Playlist[] array with Playlist objects
     */
    public static function getAll($clang_id)
    {
        $query = 'SELECT playlist_id FROM '. \rex::getTablePrefix() .'d2u_videos_playlists';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $playlist = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $playlist[$result->getValue('playlist_id')] = new self((int) $result->getValue('playlist_id'));
            $result->next();
        }
        return $playlist;
    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if successful
     */
    public function save()
    {
        $query = \rex::getTablePrefix() .'d2u_videos_playlists SET '
                ."name = '". addslashes($this->name) ."', "
                ."video_ids = '". implode('|', array_keys($this->videos)) ."' ";

        if (0 === $this->playlist_id) {
            $query = 'INSERT INTO '. $query;
        } else {
            $query = 'UPDATE '. $query .' WHERE playlist_id = '. $this->playlist_id;
        }

        $result = rex_sql::factory();
        $result->setQuery($query);
        if (0 === $this->playlist_id) {
            $this->playlist_id = (int) $result->getLastId();
        }

        return !$result->hasError();
    }
}
