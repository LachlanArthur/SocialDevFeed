<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

trait HasThumbnailsTrait {

	/** @var EntryImage[] */
	public $thumbnails = [];

	/**
	 * @return boolean
	 */
	public function hasThumbnail() {

		return ! empty( $this->thumbnails );

	}

	/**
	 * @return EntryImage
	 */
	public function getThumbnail() {

		// The first thumbnail is the largest
		return \reset( $this->thumbnails );

	}

	protected function sortThumbnails() {

		if ( ! is_array( $this->thumbnails ) ) $this->thumbnails = [];

		if ( empty( $this->thumbnails ) ) return;

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
