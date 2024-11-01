<?php

namespace WooUploads\Validators;

defined( 'ABSPATH' ) or exit;

use \WooUploads\Abstracts\Validator;

class FileExtension extends Validator {

	public function validate( array $file ) : bool {
		if( $file['error'] || empty( $file['name'] ) ) return false;
		return in_array( $this->get_file_extension( $file['name'] ), $this->get_allowed_extensions() );
	}

	public function js_validator( array $validators ) : array {
		$validators['file_extension'] = '
			let allowed_ext = ' . json_encode( $this->get_allowed_extensions() ) . ';
			let ext = file.name.split(".").pop().toLowerCase();
			return allowed_ext.indexOf( ext ) !== -1;
		';
		return $validators;
	}

}
