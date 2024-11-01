<?php

namespace WooUploads\Validators;

defined( 'ABSPATH' ) or exit;

class JPEG extends JPG {

	public function js_validator( array $validators ) : array {
		$validators['jpeg']  = 'return "image/jpeg" == file.type';
		return $validators;
	}

}
