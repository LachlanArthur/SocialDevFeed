<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;
use LachlanArthur\SocialDevFeed\Meta;

class Instagram extends AbstractPlatformBase {

	public static function getName() { return 'instagram'; }
	public static function getTitle() { return 'Instagram'; }
	public static function getIdLabel() { return 'Username'; }

	/** @var string */
	public $username;

	public function __construct( $username ) {
		$this->username = $username;
	}

	public function getCacheKey() {
		return self::getName() . '-' . $this->username;
	}

	protected function getJson() {
		$json = $this->request( 'get', "https://www.instagram.com/{$this->username}/?__a=1" );
		return \json_decode( $json );
	}

	public function getEntries() {

		try {
			$json = $this->getJson();
			$edges = $json->graphql->user->edge_owner_to_timeline_media->edges;
			return \array_map( [ $this, 'createEntryFromEdge' ], $edges );
		} catch ( \Throwable $e ) {
			return null;
		}

	}

	protected function createEntryFromEdge( $edge ) {

		$node = $edge->node;

		$thumbnails = [
			(object) [
				'url' => $node->display_url,
				'width' => $node->dimensions->width,
				'height' => $node->dimensions->height,
			],
		];

		foreach ( $node->thumbnail_resources as $thumbnail_resource ) {
			$thumbnails[] = (object) [
				'url' => $thumbnail_resource->src,
				'width' => $thumbnail_resource->config_width,
				'height' => $thumbnail_resource->config_height,
			];
		}

		return new Entry( self::getName(), [
			'url' => "https://www.instagram.com/p/{$node->shortcode}/",
			'title' => $node->edge_media_to_caption->edges[0]->node->text ?? null,
			'description' => null,
			'datetime' => new \DateTime( \date( \DATE_ATOM, $node->taken_at_timestamp ), new \DateTimeZone( 'UTC' ) ),
			'thumbnails' => $thumbnails,
		] );

	}

	public function getMeta() {

		try {
			$json = $this->getJson();

			$user = $json->graphql->user;

			return new Meta( self::getName(), [
				'title' => $user->full_name,
				'author' => $user->full_name,
				'url' => "https://www.instagram.com/{$user->username}/",
				'thumbnails' => [ (object) [ 'url' => $user->profile_pic_url_hd ] ],
			] );
		} catch ( \Throwable $e ) {
			return null;
		}

	}

}
