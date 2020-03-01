<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;
use LachlanArthur\SocialDevFeed\EntryImage;
use LachlanArthur\SocialDevFeed\Meta;

class YouTube extends AbstractPlatformBase {

	public static function getName() { return 'youtube'; }
	public static function getTitle() { return 'YouTube'; }

	/** @var string */
	protected static $apiKey;

	protected static $defaultLimit = 25;

	/** @var string */
	public $playlistId;

	/** @var \Google_Client */
	public $google;

	/** @var \Google_Service_YouTube */
	public $youtube;

	/** @var integer */
	public $limit;

	public function __construct( $playlistId, $limit = null ) {

		$this->playlistId = $playlistId;
		$this->limit = $limit ?? self::$defaultLimit;

		$this->google = new \Google_Client();
		$this->google->setDeveloperKey( self::$apiKey );
		$this->google->setScopes( [
			'https://www.googleapis.com/auth/youtube.readonly',
		] );

		$this->youtube = new \Google_Service_YouTube( $this->google );

	}

	public static function setApiKey( $apiKey ) {
		self::$apiKey = $apiKey;
	}

	public static function hasApiKey() {
		return ! empty( self::$apiKey );
	}

	public static function setDefaultLimit( $limit ) {
		self::$defaultLimit = $limit;
	}

	public function getCacheKey() {
		return self::getName() . '-' . $this->playlistId;
	}

	public function getEntries() {

		try {
			$items = $this->youtube->playlistItems->listPlaylistItems( 'snippet', [
				'playlistId' => $this->playlistId,
				'maxResults' => $this->limit,
			] );
		} catch ( \Exception $e ) {
			// DO SOMETHING
			return null;
		}

		return \array_map( [ $this, 'createEntryFromPlaylistItem' ], \iterator_to_array( $items ) );

	}

	/**
	 * @param \Google_Service_YouTube_PlaylistItem $item
	 * @return Entry
	 */
	public static function createEntryFromPlaylistItem( $item ) {

		$snippet = $item->getSnippet();

		return new Entry( self::getName(), [
			'url' => 'https://www.youtube.com/watch?v=' . $snippet->getResourceId()->getVideoId(),
			'title' => $snippet->getTitle(),
			'description' => $snippet->getDescription(),
			'datetime' => new \DateTime( $snippet->getPublishedAt(), new \DateTimeZone( 'UTC' ) ),
			'thumbnails' => self::processThumbnails( $snippet->getThumbnails() ),
		] );

	}

	public function getMeta() {

		try {
			$playlistsResponse = $this->youtube->playlists->listPlaylists( 'snippet', [
				'id' => $this->playlistId,
			] );

			$playlists = $playlistsResponse->getItems();

			/** @var \Google_Service_YouTube_Playlist $playlist */
			$playlist = reset( $playlists );

			$playlistSnippet = $playlist->getSnippet();

			$authorChannelResponse = $this->youtube->channels->listChannels( 'snippet', [
				'id' => $playlistSnippet->getChannelId(),
			] );

			$authorChannels = $authorChannelResponse->getItems();

			/** @var \Google_Service_YouTube_Channel $authorChannel */
			$authorChannel = reset( $authorChannels );

			$authorChannelSnippet = $authorChannel->getSnippet();

			return new Meta( self::getName(), [
				'title' => $playlistSnippet->getTitle(),
				'author' => $authorChannelSnippet->getTitle(),
				'url' => "https://www.youtube.com/playlist?list={$this->playlistId}",
				'thumbnails' => self::processThumbnails( $authorChannelSnippet->getThumbnails() ),
			] );
		} catch ( \Exception $e ) {
			return null;
		}

	}

	/**
	 * @param \Google_Service_YouTube_ThumbnailDetails $youtubeThumbnails
	 * @return EntryImage[]
	 */
	public static function processThumbnails( $youtubeThumbnails ) {
		$thumbnails = [];

		foreach ( \array_values( (array) $youtubeThumbnails->toSimpleObject() ) as $thumbnail ) {
			$thumbnails[] = (object) [
				'url' => $thumbnail->url,
				'width' => $thumbnail->width,
				'height' => $thumbnail->height,
			];
		}

		return $thumbnails;
	}

}
