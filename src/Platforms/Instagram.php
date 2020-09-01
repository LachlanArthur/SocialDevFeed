<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;
use LachlanArthur\SocialDevFeed\Meta;

class Instagram extends AbstractPlatformBase {

	public static function getName() { return 'instagram'; }
	public static function getTitle() { return 'Instagram'; }
	public static function getIdLabel() { return 'Access Token'; }

	protected static $defaultLimit = 25;

	/** @var string */
	protected $token;

	public function __construct( $token, $args = [] ) {

		$args = array_merge( [
			'limit' => self::$defaultLimit,
		], $args );

		$this->token = $token;
		$this->limit = $args[ 'limit' ];

	}

	public static function setDefaultLimit( $limit ) {
		self::$defaultLimit = $limit;
	}

	public function getCacheKey() {
		return self::getName() . '-' . \md5( $this->token );
	}

	protected function requestMyMedia() {
		try {

			$json = $this->request( 'get', 'https://graph.instagram.com/me/media', [
				'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp',
				'limit' => $this->limit,
				'access_token' => $this->token,
			] );

			return \json_decode( $json );

		} catch ( \Throwable $e ) {

			return (object) [
				'data' => [],
				'paging' => [],
			];

		}
	}

	protected function requestMyDetails() {
		try {

			$json = $this->request( 'get', 'https://graph.instagram.com/me', [
				'fields' => 'id,username',
				'access_token' => $this->token,
			] );

			return \json_decode( $json );

		} catch ( \Throwable $e ) {

			return (object) [];

		}
	}

	public function getEntries() {

		$response = $this->requestMyMedia();

		return \array_filter( \array_map( [ $this, 'createEntryFromMedia' ], $response->data ) );

	}

	protected function createEntryFromMedia( $media ) {

		// Item is unavailable for copyright reasons.
		if ( empty( $media->permalink ) ) return false;

		$entryData = [
			'url' => $media->permalink,
			'title' => $media->caption,
			'description' => null,
			'datetime' => new \DateTime( $media->timestamp, new \DateTimeZone( 'UTC' ) ),
			'thumbnails' => [],
		];

		switch ( $media->media_type ) {

			case 'IMAGE':
			case 'CAROUSEL_ALBUM':
				$entryData[ 'thumbnails' ][] = (object) [
					'url' => $media->media_url,
					'width' => null,
					'height' => null,
				];
				break;

			case 'VIDEO':
				$entryData[ 'thumbnails' ][] = (object) [
					'url' => $media->thumbnail_url,
					'width' => null,
					'height' => null,
				];
				break;

		}

		return new Entry( self::getName(), $entryData );

	}

	public function getMeta() {

		$response = $this->requestMyDetails();

		return new Meta( self::getName(), [
			'title' => $response->username,
			'author' => $response->username,
			'url' => "https://www.instagram.com/{$response->username}/",
			'thumbnails' => [],
		] );

	}

}
