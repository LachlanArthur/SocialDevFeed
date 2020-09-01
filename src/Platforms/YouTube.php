<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;
use LachlanArthur\SocialDevFeed\EntryImage;
use LachlanArthur\SocialDevFeed\Meta;

class YouTube extends AbstractPlatformBase {

	public static function getName() { return 'youtube'; }
	public static function getTitle() { return 'YouTube'; }
	public static function getIdLabel() { return 'Playlist ID'; }

	/** @var string */
	protected static $apiKey;

	protected static $defaultLimit = 25;

	public $baseUrl = 'https://www.googleapis.com';

	/** @var string */
	public $playlistId;

	/** @var integer */
	public $limit;

	public function __construct( $playlistId, $args = [] ) {

		$args = array_merge( [
			'limit' => self::$defaultLimit,
		], $args );

		$this->playlistId = $playlistId;
		$this->limit = $args[ 'limit' ];

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
		return self::getName() . '-' . \md5( $this->playlistId );
	}

	protected function requestPlaylistItems( $playlistId, $limit ) {

		$json = $this->request( 'get', "{$this->baseUrl}/youtube/v3/playlistItems", [
			'part' => 'snippet',
			'playlistId' => $playlistId,
			'maxResults' => $limit,
			'key' => self::$apiKey,
		] );

		$response = \json_decode( $json );

		return $response->items;

	}

	protected function requestPlaylist( $playlistId ) {

		$json = $this->request( 'get', "{$this->baseUrl}/youtube/v3/playlists", [
			'part' => 'snippet',
			'id' => $playlistId,
			'key' => self::$apiKey,
		] );

		$response = \json_decode( $json );

		return reset( $response->items );

	}

	protected function requestChannel( $channelId ) {

		$json = $this->request( 'get', "{$this->baseUrl}/youtube/v3/channels", [
			'part' => 'snippet',
			'id' => $channelId,
			'key' => self::$apiKey,
		] );

		$response = \json_decode( $json );

		return reset( $response->items );

	}

	public function getEntries() {

		try {
			return \array_map( [ $this, 'createEntryFromPlaylistItem' ], $this->requestPlaylistItems( $this->playlistId, $this->limit ) );
		} catch ( \Throwable $e ) {
			return null;
		}

	}

	/**
	 * @param object $item
	 * @return Entry
	 */
	public static function createEntryFromPlaylistItem( $item ) {

		$snippet = $item->snippet;

		return new Entry( self::getName(), [
			'url' => 'https://www.youtube.com/watch?v=' . $snippet->resourceId->videoId,
			'title' => $snippet->title,
			'description' => $snippet->description,
			'datetime' => new \DateTime( $snippet->publishedAt, new \DateTimeZone( 'UTC' ) ),
			'thumbnails' => self::processThumbnails( $snippet->thumbnails ),
		] );

	}

	public function getMeta() {

		try {
			$playlist = $this->requestPlaylist( $this->playlistId );
			$playlistSnippet = $playlist->snippet;

			$authorChannel = $this->requestChannel( $playlistSnippet->channelId );
			$authorChannelSnippet = $authorChannel->snippet;

			return new Meta( self::getName(), [
				'title' => $playlistSnippet->title,
				'author' => $authorChannelSnippet->title,
				'url' => "https://www.youtube.com/playlist?list={$this->playlistId}",
				'thumbnails' => self::processThumbnails( $authorChannelSnippet->thumbnails ),
			] );
		} catch ( \Throwable $e ) {
			return null;
		}

	}

	/**
	 * @param object $youtubeThumbnails
	 * @return EntryImage[]
	 */
	public static function processThumbnails( $youtubeThumbnails ) {
		$thumbnails = [];

		foreach ( \array_values( (array) $youtubeThumbnails ) as $thumbnail ) {
			$thumbnails[] = (object) [
				'url' => $thumbnail->url,
				'width' => $thumbnail->width,
				'height' => $thumbnail->height,
			];
		}

		return $thumbnails;
	}

}
