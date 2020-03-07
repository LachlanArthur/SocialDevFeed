<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed\Platforms;

abstract class AbstractPlatformBase implements PlatformInterface {

	public function request( $method, $url, $body = null, $curlOptions = [] ) {

		return \LachlanArthur\SocialDevFeed\Feed::request( $method, $url, $body, $curlOptions );

	}

}
