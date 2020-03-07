<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;
use LachlanArthur\SocialDevFeed\Meta;

class WordPressRest extends AbstractPlatformBase {

	public static function getName() { return 'wordpress-rest'; }
	public static function getTitle() { return 'WordPress REST API'; }
	public static function getIdLabel() { return 'REST Endpoint'; }

	/** @var string */
	public $URL;

	public function __construct( $URL ) {

		$this->URL = $URL;

	}

	public function getCacheKey() {
		return self::getName() . '-' . $this->URL;
	}

	public function getEntries() {

		try {
			$json = $this->request( 'get', $this->URL, [
				'_fields' => 'link,title,excerpt,date_gmt,_links.wp:featuredmedia',
				'_embed' => '1',
			] );
			$posts = \json_decode( $json );
			return \array_map( [ $this, 'createEntryFromPost' ], $posts );
		} catch ( \Throwable $e ) {
			return null;
		}

	}

	protected function createEntryFromPost( $post ) {

		$thumbnails = null;
		$media = $post->_embedded->{'wp:featuredmedia'}[0] ?? null;
		if ( ! empty( $media ) ) {
			$thumbnails = $this->getThumbnailsFromMedia( $media );
		}

		return new Entry( self::getName(), [
			'url' => $post->link ?? null,
			'title' => $post->title->rendered ?? null,
			'description' => $post->excerpt->rendered ?? null,
			'datetime' => new \DateTime( $post->date_gmt, new \DateTimeZone( 'UTC' ) ),
			'thumbnails' => $thumbnails,
		] );

	}

	protected function getThumbnailsFromMedia( $media ) {

		$thumbnails = [
			(object) [
				'url' => $media->source_url ?? null,
				'width' => $media->width ?? null,
				'height' => $media->height ?? null,
			],
		];

		foreach ( $media->media_details->sizes as $size ) {
			$thumbnails[] = (object) [
				'url' => $size->source_url ?? null,
				'width' => $size->width ?? null,
				'height' => $size->height ?? null,
			];
		}

		return $thumbnails;

	}

	public function getMeta() {

		try {

			$baseURL = strstr( $this->URL, '/wp-json/', true );

			if ( empty( $baseURL ) ) return null;

			$json = $this->request( 'get', $baseURL . '/wp-json/', [
				'_fields' => 'name,home',
			] );

			$meta = \json_decode( $json );

			return new Meta( self::getName(), [
				'title' => $meta->name,
				'author' => $meta->name,
				'url' => $meta->home,
			] );
		} catch ( \Throwable $e ) {
			return null;
		}

	}

}
