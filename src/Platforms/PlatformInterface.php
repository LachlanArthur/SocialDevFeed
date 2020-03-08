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
	 * Get the label of the ID for this platform.
	 *
	 * @return string
	 */
	public static function getIdLabel();

	/**
	 * Unique key for caching this instance's output
	 *
	 * Must follow [PSR-16](https://www.php-fig.org/psr/psr-16/) cache key rules.
	 *
	 * Cannot contain the following characters: `{}()/\@:`
	 *
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
