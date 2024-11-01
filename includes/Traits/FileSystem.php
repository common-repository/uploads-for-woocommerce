<?php

namespace WooUploads\Traits;

defined( 'ABSPATH' ) or exit;

trait FileSystem {

	static private $_fs;

	public function mkdir( $base, $path ){
		if( ( $mkdir = wp_mkdir_p( $path ) ) ){
			return $mkdir & $this->put_index_recursive( $base, $path );
		}
		return false;
	}

	public function put_index_recursive( $base, $path ){
		$error      = 1; // bit mask
		$recursions = str_replace( $base, '', $path );
		$recursions = explode( '/', trim( $recursions, '/' ) );
		$build      = $base;
		$error     &= $this->put_index( $build ); // put index in base
		foreach( $recursions as $recursion ){
			$build .= '/' . $recursion;
			$error &= $this->put_index( $build );
		}
		return $error;
	}

	public function put_index( $path ){
		$path = trailingslashit( $path );
		if( ! file_exists( $path . 'index.html' ) ){
			return ( false !== $this->getFS()->put_contents( $path . 'index.html', '' ) )
				? true
				: false;
		}
		return true;
	}

	public function delete_if_empty( $path ){
		$hasFiles = false;
		$dir = new \RecursiveDirectoryIterator( $path );
		$it = new \RecursiveIteratorIterator( $dir );
		$it->rewind();
		while( $it->valid() ){
			if( ! $it->isDot() && false === stripos( $it->getSubPathName(), 'index.html' ) ){
				$hasFiles = true;
				break;
			}
			$it->next();
		}
		if( ! $hasFiles ){
			return $this->delete( $path, true );
		}
		return false;
	}

	public function delete( $path, $recursive = false ){
		return $this->getFS()->delete( $path, $recursive );
	}

	public function move( $src, $dest, $override = false ){
		return $this->getFS()->move( $src, $dest, $override );
	}

	private function getFS(){
		if( is_null( self::$_fs ) ){
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			self::$_fs = new \WP_Filesystem_Direct( [] );
		}
		return self::$_fs;
	}

}
