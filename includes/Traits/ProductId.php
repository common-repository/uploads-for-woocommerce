<?php

namespace WooUploads\Traits;

defined( 'ABSPATH' ) or exit;

trait ProductId {

	private $product_id = 0;

	/**
	 * Only available at "wp" hook
	**/
	public function get_product_id(){
		if( ! $this->product_id ){
			$this->set_product_id( get_queried_object_id() );
		}
		return $this->product_id;
	}

	public function set_product_id( int $product_id ){
		$this->product_id = $product_id;
	}

}
