<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

class Entry {

	/** @var string */
	public $platform;

	/** @var string */
	public $timestamp = '0000-00-00T00:00:00+00:00';

	/** @var string */
	public $url;

	/** @var string */
	public $title;

	/** @var string */
	public $description;

	/** @var string */
	public $image;

	/** @var integer */
	public $imageWidth;

	/** @var integer */
	public $imageHeight;

	/** @var EntryImage[] */
	public $thumbnails = [];

	public function __construct( string $platform, iterable $properties ) {

		$this->platform = $platform;

		foreach ( $properties as $property => $value ) {
			$this->{$property} = $value;
		}

		$this->sortThumbnails();
	}

	/**
	 * @return EntryImage
	 */
	public function getImage() {

		if ( ! empty( $this->image ) ) {

			return (object) [
				'url' => $this->image,
				'width' => $this->imageWidth,
				'height' => $this->imageHeight,
			];

		} else {

			return $this->getThumbnail();

		}

	}

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

	public function getDate() {

		return new \DateTime( $this->timestamp );

	}

}
