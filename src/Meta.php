<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

class Meta extends SimpleObject {

	use HasThumbnailsTrait;

	/** @var string */
	public $platform;

	/** @var string */
	public $title;

	/** @var string */
	public $author;

	/** @var string */
	public $url;

	public function __construct( string $platform, iterable $properties ) {
		parent::__construct( $properties );

		$this->platform = $platform;

		$this->sortThumbnails();
	}

}
