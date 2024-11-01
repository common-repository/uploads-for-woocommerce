<?php

namespace WooUploads\Validators;

defined( 'ABSPATH' ) or exit;

use \WooUploads\Abstracts\Validator;

class JPG extends Validator {

	public function validate( array $file ) : bool {
		list( $width, $height ) = getimagesize( $file['tmp_name'] );
		$mime = mime_content_type( $file['tmp_name'] );
		return 'image/jpeg' == $mime && $width > 1 && $height > 1;
	}

	public function js_validator( array $validators ) : array {
		$validators['jpg']  = 'return "image/jpeg" == file.type';
		return $validators;
	}

	public function generate_thumbnail( array $file, string $filename, string $path ) : ?string {
		list( $width, $height ) = getimagesize( $file['tmp_name'] );
		$ratio = $width / $height;
		// new dimensions for thumbnails
		if( $width > $height ){
			$newWidth = get_option( 'thumbnail_size_w' );
			$newHeight = get_option( 'thumbnail_size_w' ) / $ratio;
			$posX = 0;
			$posY = ( get_option( 'thumbnail_size_h' ) - $newHeight ) / 2;
		} else {
			$newWidth = get_option( 'thumbnail_size_w' ) * $ratio;
			$newHeight = get_option( 'thumbnail_size_h' );
			$posX = ( get_option( 'thumbnail_size_w' ) - $newWidth ) / 2;
			$posY = 0;
		}
		$resource = imagecreatefromjpeg( $file['tmp_name'] );
		$new_image = imagecreatetruecolor( get_option( 'thumbnail_size_w' ), get_option( 'thumbnail_size_h' ) );
		$color = imagecolorallocatealpha( $new_image, 255, 255, 255, 127 );
		imagefill( $new_image, 0, 0, $color );
		imagecopyresampled( $new_image, $resource, $posX, $posY, 0, 0, $newWidth, $newHeight, $width, $height );
		// save thumb
		imagejpeg( $new_image, trailingslashit( $path ) . $filename );
		imagedestroy( $resource );
		imagedestroy( $new_image );
		return $filename;
	}

}
