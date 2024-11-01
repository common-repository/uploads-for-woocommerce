<?php

namespace WooUploads\Abstracts;

defined( 'ABSPATH' ) or exit;

use \WooUploads\App;

abstract class Validator {

	protected $allowed_ext = [];
	protected $thumb;

	public function __construct(){
		add_filter( 'woo_uploads_js_validators', [ $this, 'js_validator' ] );
	}

	abstract public function validate( array $file ) : bool;

	abstract public function js_validator( array $validators ) : array;

	protected function get_allowed_extensions() : array {
		if( ! $this->allowed_ext ){
			$this->allowed_ext = get_option( 'woo_uploads_allowed_ext', APP::DEFAULT_FILE_EXT_SUPPORT );
			$this->allowed_ext = explode( ' ', $this->allowed_ext );
			$this->allowed_ext = array_filter( $this->allowed_ext );
			$this->allowed_ext = array_values( $this->allowed_ext );
		}
		return $this->allowed_ext;
	}

	public function get_file_extension( string $filename ) : string {
		$name = explode( '.', $filename );
		return strtolower( array_pop( $name ) );
	}

	// will implement this as needed on each validator, so we can check if method is callable
	// if so use validator to generate thumbnails, else default thumbnail will be shown
	// $file = uploaded file
	// $filename = newly generated filename
	// $path = $path were to save the file, this returns the thumbnail path.
	// abstract public function generate_thumbnail( array $file, string $filename, string $path ) : ?string;

}
