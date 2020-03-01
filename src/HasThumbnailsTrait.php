<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

trait HasThumbnailsTrait {

	/** @var EntryImage[] */
	public $thumbnails = [];

	public function getThumbnail() {

		// The first thumbnail is the largest
		return \reset( $this->thumbnails );

	}

	protected function sortThumbnails() {

		// Order thumbnails largest to smallest
		\usort( $this->thumbnails, [ $this, 'compareThumbnails' ] );

	}

	protected function compareThumbnails( $a, $b ) {

		return ( $b->width * $b->height ) <=> ( $a->width * $a->height );

	}

}
