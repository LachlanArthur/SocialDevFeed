<?php declare( strict_types = 1 );

namespace LachlanArthur\SocialDevFeed;

class FileCache implements CacheInterface {

	public $cachePath;

	/**
	 * How long entries are valid for in seconds.
	 *
	 * @var integer
	 */
	public $validity;

	/**
	 * @param [type] $cachePath Where the cache files are stored. Defaults to
	 * @param [type] $validity How long entries are valid for in seconds.
	 */
	public function __construct( $cachePath = null, $validity = 60 * 60 * 24 ) {
		$this->cachePath = \rtrim( $cachePath ?? \sys_get_temp_dir(), '/' ) . '/';
		$this->validity = $validity;
	}

	public function get( $key, callable $setter = null ) {
		$path = $this->getKeyPath( $key );

		if ( $this->fileIsValid( $path ) ) {

			try {
				$data = \unserialize( \file_get_contents( $path ) );
				if ( $data === false ) throw new \Exception();
				return $data;
			} catch ( \Exception $e ) {
				$this->clear( $key );
				return null;
			}

		} elseif ( \is_callable( $setter ) ) {

			$data = \call_user_func( $setter );
			$this->set( $key, $data );
			return $data;

		} else {
			return null;
		}
	}

	public function set( $key, $entries ) {
		if ( empty( $entries ) ) {
			$this->clear( $key );
		} else {
			\file_put_contents( $this->getKeyPath( $key ), \serialize( $entries ) );
		}
	}

	public function clear( $key ) {
		$path = $this->getKeyPath( $key );
		if ( \is_file( $path ) ) {
			\unlink( $path );
		}
	}

	protected function getKeyPath( $key ) {
		return "{$this->cachePath}{$key}";
	}

	protected function fileIsValid( $path ) {
		if ( ! \is_file( $path ) ) return false;

		$stat = \stat( $path );
		if ( $stat[ 'mtime' ] + $this->validity < time() ) {
			echo "Cache file {$path} has expired\n";
			return false;
		}

		return true;
	}

}
