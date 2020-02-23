<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

interface PlatformInterface {

	/**
	 * @return string
	 */
	public function getCacheKey();

	/**
	 * @return Entry[]
	 */
	public function get();

}
