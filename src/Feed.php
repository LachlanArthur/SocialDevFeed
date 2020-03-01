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
	public $entriesCache;

	/** @var CacheInterface Defaults to a filesystem cache with a 30 day lifetime */
	public $metaCache;

	public function __construct( CacheInterface $entriesCache = null, CacheInterface $metaCache = null ) {
		$this->entriesCache = $entriesCache ?? new Psr16Cache( new FilesystemAdapter( 'lasdfg-entries', 60 * 60 * 24,      \sys_get_temp_dir() ) );
		$this->metaCache    = $metaCache    ?? new Psr16Cache( new FilesystemAdapter( 'lasdfg-meta',    60 * 60 * 24 * 30, \sys_get_temp_dir() ) );
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
		$entries = self::getCacheValueOtherwise( $this->entriesCache, $platform->getCacheKey(), [ $platform, 'getEntries' ] );

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

		return self::getCacheValueOtherwise( $this->metaCache, $platform->getCacheKey(), [ $platform, 'getMeta' ] );

	}

	protected static function getCacheValueOtherwise( CacheInterface $cache, $cacheKey, callable $otherwise ) {

		if ( $cache->has( $cacheKey ) ) {

			$value = $cache->get( $cacheKey, null );

		} else {

			$value = $otherwise();
			$cache->set( $cacheKey, $value );

		}

		return $value;

	}

	protected function compareEntryDateTime( Entry $a, Entry $b ) {
		return $b->datetime <=> $a->datetime;
	}

}
