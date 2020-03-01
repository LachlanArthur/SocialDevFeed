<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

use LachlanArthur\SocialDevFeed\Entry;

interface PlatformInterface {

	/**
	 * @return string
	 */
	public function getCacheKey();

	/**
	 * @return Entry[]
	 */
	public function getEntries();

}
