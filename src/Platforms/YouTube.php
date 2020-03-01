<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;

class YouTube extends AbstractPlatformBase {

	public static $name = 'youtube';

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

	public static function setDefaultLimit( $limit ) {
		self::$defaultLimit = $limit;
	}

	public function getCacheKey() {
		return self::$name . '-' . $this->playlistId;
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

		return new Entry( self::$name, [
			'url' => 'https://www.youtube.com/watch?v=' . $snippet->getResourceId()->getVideoId(),
			'title' => $snippet->getTitle(),
			'description' => $snippet->getDescription(),
			'timestamp' => \date( DATE_ATOM, \strtotime( $snippet->getPublishedAt() ) ) ?: null,
			'thumbnails' => \array_map( function( $thumbnail ) {
				return (object) [
					'url' => $thumbnail->url,
					'width' => $thumbnail->width,
					'height' => $thumbnail->height,
				];
			}, \array_values( (array) $snippet->getThumbnails()->toSimpleObject() ) ),
		] );

	}

}
