<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

trait HasThumbnailsTrait {

	/** @var EntryImage[] */
	public $thumbnails = [];

	/**
	 * @return EntryImage
	 */
	public function getThumbnail() {

		// The first thumbnail is the largest
		return \reset( $this->thumbnails );

	}

	protected function sortThumbnails() {

		// Order thumbnails largest to smallest
		\usort( $this->thumbnails, [ $this, 'compareThumbnails' ] );

	}

	/**
	 * @param EntryImage $a
	 * @param EntryImage $b
	 * @return integer
	 */
	protected function compareThumbnails( $a, $b ) {

		return ( $b->width * $b->height ) <=> ( $a->width * $a->height );

	}

}
