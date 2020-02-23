<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;

class YouTube extends AbstractPlatformBase {

	public static $name = 'youtube';

	/** @var string */
	public $playlistId;

	/** @var string */
	public $apiKey;

	/** @var \Google_Client */
	public $google;

	/** @var \Google_Service_YouTube */
	public $youtube;

	/** @var integer */
	public $limit;

	public function __construct( $playlistId, $apiKey, $limit = 25 ) {

		$this->playlistId = $playlistId;
		$this->apiKey = $apiKey;
		$this->limit = $limit;

		$this->google = new \Google_Client();
		$this->google->setDeveloperKey( $apiKey );
		$this->google->setScopes( [
			'https://www.googleapis.com/auth/youtube.readonly',
		] );

		$this->youtube = new \Google_Service_YouTube( $this->google );

	}

	public function getCacheKey() {
		return self::$name . '-' . $this->playlistId;
	}

	/**
	 * @return
	 */
	public function get() {

		$items = $this->youtube->playlistItems->listPlaylistItems( 'snippet', [
			'playlistId' => $this->playlistId,
			'maxResults' => $this->limit,
		] );

		return \array_map( [ $this, 'createEntryFromPlaylistItem' ], \iterator_to_array( $items ) );

	}

	/**
	 * @param \Google_Service_YouTube_PlaylistItem $item
	 * @return Entry
	 */
	public static function createEntryFromPlaylistItem( $item ) : Entry {

		return self::createEntryFromVideo( $item->getSnippet()->getResourceId()->getVideoId(), $item );

	}

	/**
	 * @param string $video_id
	 * @param \Google_Service_YouTube_Video|\Google_Service_YouTube_PlaylistItem $snippet
	 * @return Entry
	 */
	public static function createEntryFromVideo( $video_id, $item ) : Entry {

		$snippet = $item->getSnippet();

		return new Entry( self::$name, [
			'url' => "https://www.youtube.com/watch?v={$video_id}",
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
