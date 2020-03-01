<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;
use LachlanArthur\SocialDevFeed\Meta;

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

	protected function getJson() {
		$response = $this->http->request( 'get', "/{$this->username}/?__a=1" );
		return \json_decode( (string) $response->getBody() );
	}

	public function getEntries() {

		try {
			$json = $this->getJson();
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
			'datetime' => new \DateTime( \date( \DATE_ATOM, $node->taken_at_timestamp ), new \DateTimeZone( 'UTC' ) ),
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

	public function getMeta() {

		try {
			$json = $this->getJson();

			$user = $json->graphql->user;

			return new Meta( self::$name, [
				'title' => $user->full_name,
				'author' => $user->full_name,
				'url' => "https://www.instagram.com/{$user->username}/",
				'thumbnails' => [ 'url' => $user->profile_pic_url_hd ],
			] );
		} catch ( \Exception $e ) {
			return null;
		}

	}

}
