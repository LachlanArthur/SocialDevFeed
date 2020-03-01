<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

class Entry extends SimpleObject {

	use HasThumbnailsTrait;

	/** @var string */
	public $platform;

	/** @var \DateTime */
	public $datetime;

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

}
