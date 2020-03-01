<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;

interface PlatformInterface {

	/**
	 * Get the 'slug'-version of the platform name.
	 *
	 * Used in cache keys and classes.
	 * Should be lowercase a-z with dashes.
	 *
	 * @return string
	 */
	public static function getName();

	/**
	 * Get the title of the platform.
	 *
	 * @return string
	 */
	public static function getTitle();

	/**
	 * @return string
	 */
	public function getCacheKey();

	/**
	 * @return Entry[]
	 */
	public function getEntries();

	/**
	 * @return Meta[]
	 */
	public function getMeta();

}
