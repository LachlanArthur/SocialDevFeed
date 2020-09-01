<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

use Psr\SimpleCache\CacheInterface;
use LachlanArthur\SocialDevFeed\Platforms\PlatformInterface;

class Feed {

	/**
	 * @var PlatformInterface[]
	 */
	public $platforms = [];

	/** @var CacheInterface */
	public $entriesCache;

	/** @var CacheInterface */
	public $metaCache;

	/** @var resource */
	protected static $curlHandle = null;

	public function __construct( CacheInterface $entriesCache, CacheInterface $metaCache ) {
		$this->entriesCache = $entriesCache;
		$this->metaCache    = $metaCache;
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

			$platformEntries = $this->getCachedEntries( $platform );

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
	public function getCachedEntries( $platform ) {

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

			$meta = $this->getCachedMeta( $platform );

			if ( ! empty( $meta ) ) {
				$metaList[] = $meta;
			}

		}

		return $metaList;

	}

	/**
	 * @param PlatformInterface $platform
	 * @return Meta
	 */
	public function getCachedMeta( $platform ) {

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

	public static function getCurlHandle() {

		if ( self::$curlHandle === null ) {
			self::$curlHandle = \curl_init();
		}

		return self::$curlHandle;

	}

	/**
	 * @param string $url
	 * @param string|array $params
	 * @return string
	 */
	public static function mergeUrlParams( $url, $params ) {

		$urlBase = $url;
		$existingParams = [];
		$mergeParams = [];

		if ( strpos( $url, '?' ) !== false ) {
			[ $urlBase, $urlParamString ] = explode( '?', $url, 2 );
			if ( ! empty( $urlParamString ) ) {
				\parse_str( $urlParamString, $existingParams );
			}
		}

		if ( is_string( $params ) ) {
			\parse_str( $params, $mergeParams );
		} else if ( is_array( $params ) ) {
			$mergeParams = $params;
		}

		$newQuery = \http_build_query( \array_merge( $existingParams, $mergeParams ) );

		if ( ! empty( $newQuery ) ) {
			$newQuery = '?' . $newQuery;
		}

		return $urlBase . $newQuery;

	}

	public static function request( $method, $url, $body = null, $curlOptions = [] ) {

		$method = \strtoupper( $method );

		if ( $method === 'GET' && ! empty( $body ) ) {
			$url = self::mergeUrlParams( $url, $body );
			$body = null;
		}

		$ch = self::getCurlHandle();

		$options = [
			CURLOPT_USERAGENT => 'LachlanArthur/SocialDevFeed/1.0',
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_URL => $url,
			CURLOPT_TIMEOUT => 5,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_REFERER => $_SERVER[ 'HTTP_HOST' ],
		];

		switch ( $method ) {

			case 'GET':
				// Do nothing
				break;

			case 'HEAD':
				$options[ CURLOPT_NOBODY ] = true;
				break;

			default:
				$options[ CURLOPT_POSTFIELDS ] = $body;
				break;

		}

		$options = array_replace( $options, $curlOptions );

		foreach ( $options as $option_key => $option_value ) {
			\curl_setopt( $ch, $option_key, $option_value );
		}

		return \curl_exec( $ch );

	}

}
