<?php

namespace WooUploads;

defined( 'ABSPATH' ) or exit;

spl_autoload_register( __NAMESPACE__ . '\AutoLoader::load' );

class AutoLoader {

	static public function load( $class ){
		if( 0 === strpos( $class, __NAMESPACE__ ) ){
			$path = preg_replace( '#^' . __NAMESPACE__ . '\\\\#', WOO_UPLOADS_DIR . 'includes/', $class );
			$path = preg_replace( '#\\\\#', '/', $path );
			if( is_readable( $path . '.php' ) ){
				include_once $path . '.php';
			}
		}
	}

}

