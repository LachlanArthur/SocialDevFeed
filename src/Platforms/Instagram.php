<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;

class Instagram extends AbstractPlatformBase {

	public static $name = 'instagram';

	/** @var string */
	public $username;

	/** @var \GuzzleHttp\ClientInterface */
	public $http;

	public function __construct( $username, \GuzzleHttp\ClientInterface $http = null ) {
		$this->username = $username;
		$this->http = $http ?? new \GuzzleHttp\Client( [
			'base_uri' => 'https://www.instagram.com',
			'timeout' => 5,
			'headers' => [
				'User-Agent' => 'LachlanArthur/SocialDevFeed/1.0',
			],
		] );
	}

	public function getCacheKey() {
		return self::$name . '-' . $this->username;
	}

	public function get() {

		try {
			$response = $this->http->request( 'get', '/beeple_crap/?__a=1' );
			$json = \json_decode( (string) $response->getBody() );
			$edges = $json->graphql->user->edge_owner_to_timeline_media->edges;
		} catch ( \Exception $e ) {
			return null;
		}

		return \array_map( [ $this, 'createEntryFromEdge' ], $edges );

	}

	protected function createEntryFromEdge( $edge ) {

		$node = $edge->node;

		return new Entry( self::$name, [
			'url' => "https://www.instagram.com/p/{$node->shortcode}/",
			'title' => $node->edge_media_to_caption->edges[0]->node->text,
			'description' => null,
			'timestamp' => \date( DATE_ATOM, $node->taken_at_timestamp ) ?: null,
			'image' => $node->display_url,
			'imageWidth' => $node->dimensions->width,
			'imageHeight' => $node->dimensions->height,
			'thumbnails' => \array_map( function( $thumbnail ) {
				return (object) [
					'url' => $thumbnail->src,
					'width' => $thumbnail->config_width,
					'height' => $thumbnail->config_height,
				];
			}, $node->thumbnail_resources ),
		] );

	}

}
