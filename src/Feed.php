<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

class Feed {

	/**
	 * @var Platforms\PlatformInterface[]
	 */
	public $platforms = [];

	/** @var CacheInterface */
	public $cache;

	function __construct( CacheInterface $cache = null ) {
		$this->cache = $cache ?? new FileCache( \sys_get_temp_dir() . '/social-dev-feed' );
	}

	public function add( Platforms\PlatformInterface $platform ) : void {
		$this->platforms[] = $platform;
	}

	public function setLimit( $limit ) {
		$this->limit = $limit;
	}

	/**
	 * @return Entry[]
	 */
	public function get( $limit = 20 ) {

		$aggregateItems = [];

		foreach ( $this->platforms as $platform ) {

			$platformItems = $this->cache->get( $platform->getCacheKey(), [ $platform, 'get' ] );

			$aggregateItems = \array_merge( $aggregateItems, $platformItems );

		}

		// Sort newest to oldest
		\usort( $aggregateItems, [ $this, 'compareEntryTimestamps' ] );

		$aggregateItems = \array_slice( $aggregateItems, 0, $limit );

		return $aggregateItems;

	}

	protected function getCached() {
		return \array_map( function( Platforms\PlatformInterface $platform ) {

			return $this->cache->get( $platform->getCacheKey() ) ?? $platform;

		}, $this->platforms );
	}

	protected function compareEntryTimestamps( Entry $a, Entry $b ) {
		return $b->timestamp <=> $a->timestamp;
	}

}
