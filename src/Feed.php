<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class Feed {

	/**
	 * @var Platforms\PlatformInterface[]
	 */
	public $platforms = [];

	/** @var CacheInterface */
	public $cache;

	function __construct( CacheInterface $cache = null ) {
		$this->cache = $cache ?? new Psr16Cache( new FilesystemAdapter( 'lasdfg', 60 * 60 * 24, \sys_get_temp_dir() . '/social-dev-feed' ) );
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

			$platformItems = $this->getCacheValueOtherwise( $platform->getCacheKey(), [ $platform, 'getEntries' ] );

			if ( ! \is_array( $platformItems ) ) {
				$platformItems = [];
			}

			$aggregateItems = \array_merge( $aggregateItems, $platformItems );

		}

		// Sort newest to oldest
		\usort( $aggregateItems, [ $this, 'compareEntryTimestamps' ] );

		$aggregateItems = \array_slice( $aggregateItems, 0, $limit );

		return $aggregateItems;

	}

	protected function getCacheValueOtherwise( $cacheKey, callable $otherwise ) {

		if ( $this->cache->has( $cacheKey ) ) {

			$value = $this->cache->get( $cacheKey, null );

		} else {

			$value = $otherwise();
			$this->cache->set( $cacheKey, $value );

		}

		return $value;

	}

	protected function compareEntryTimestamps( Entry $a, Entry $b ) {
		return $b->timestamp <=> $a->timestamp;
	}

}
