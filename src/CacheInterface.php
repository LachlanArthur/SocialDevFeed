<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

interface CacheInterface {

	/**
	 * @param string $key
	 * @param callable $setter
	 * @return Entry[]|null
	 */
	public function get( $key, callable $setter = null );

	/**
	 * @param string $key
	 * @param Entry[] $entries
	 * @return void
	 */
	public function set( $key, $entries );

	/**
	 * @param string $key
	 */
	public function clear( $key );

}
