<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

class SimpleObject {

	public function __construct( iterable $properties ) {

		foreach ( $properties as $property => $value ) {
			$this->{$property} = $value;
		}

	}

}
