<?php

namespace WooUploads\Traits;

defined( 'ABSPATH' ) or exit;

trait Visibility {

	use \WooUploads\Traits\ProductId;

	private $is_visible = false;

	/**
	 * Only available at "wp" hook
	**/
	public function is_visible_on_product(){
		$this->is_visible = function_exists( 'is_product' )
			&& is_product()
			&& $this->upload_enabled( $this->get_product_id() );
		$this->is_visible = apply_filters( 'woo_uploads_is_visible', $this->is_visible );
		return $this->is_visible;
	}

	public function upload_enabled( $product_id ){
		return apply_filters(
			'woo_uploads_is_enabled',
			(bool) get_post_meta( $product_id, 'woo_uploads_on', true ),
			$product_id
		);
	}

}
