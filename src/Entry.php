<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

class Entry extends SimpleObject {

	use HasThumbnailsTrait;

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

	public function __construct( string $platform, iterable $properties ) {
		parent::__construct( $properties );

		$this->platform = $platform;

		$this->sortThumbnails();
	}

	public function getDate() {

		return new \DateTime( $this->timestamp );

	}

}
