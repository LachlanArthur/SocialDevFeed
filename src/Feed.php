<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

use LachlanArthur\SocialDevFeed\Platforms\PlatformInterface;

class Feed {

	/**
	 * @var PlatformInterface[]
	 */
	public $platforms = [];

	/** @var CacheInterface Defaults to a filesystem cache with a 24 hour lifetime */
	public $cache;

	public function __construct( CacheInterface $cache = null ) {
		$this->cache = $cache ?? new Psr16Cache( new FilesystemAdapter( 'lasdfg', 60 * 60 * 24, \sys_get_temp_dir() ) );
	}

	public function add( PlatformInterface $platform ) : void {
		$this->platforms[] = $platform;
	}

	public function setLimit( $limit ) {
		$this->limit = $limit;
	}

	/**
	 * @return Entry[]
	 */
	public function getEntries() {

		$allEntries = [];

		foreach ( $this->platforms as $platform ) {

			$platformEntries = $this->getPlatformEntries( $platform );

			$allEntries = \array_merge( $allEntries, $platformEntries );

		}

		// Sort newest to oldest
		\usort( $allEntries, [ $this, 'compareEntryDateTime' ] );

		return $allEntries;

	}

	/**
	 * @param PlatformInterface $platform
	 * @return Entry[]
	 */
	public function getPlatformEntries( $platform ) {

		/** @var Entry[] $entries */
		$entries = $this->getCacheValueOtherwise( 'entries-' . $platform->getCacheKey(), [ $platform, 'getEntries' ] );

		if ( ! \is_array( $entries ) ) {
			$entries = [];
		}

		return $entries;

	}

	/**
	 * @return Meta[]
	 */
	public function getMeta() {

		$metaList = [];

		foreach ( $this->platforms as $platform ) {

			$metaList[] = $this->getPlatformMeta( $platform );

		}

		return $metaList;

	}

	/**
	 * @param PlatformInterface $platform
	 * @return Meta
	 */
	public function getPlatformMeta( $platform ) {

		return $this->getCacheValueOtherwise( 'meta-' . $platform->getCacheKey(), [ $platform, 'getMeta' ] );

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

	protected function compareEntryDateTime( Entry $a, Entry $b ) {
		return $b->datetime <=> $a->datetime;
	}

}
