<?php

namespace WooUploads\WooCommerce\Order;

defined( 'ABSPATH' ) or exit;

class Checkout {

	public function __construct(){
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'add_order_items' ], 10, 4 );
	}

	public function add_order_items( $item, $cart_item_key, $values, $order ){
		if( isset( $values['_uploads'] ) && $values['_uploads'] ){
			$item->add_meta_data( '_uploads', $values['_uploads'], true );
		}
		if( isset( $values['_upload_dir'] ) && $values['_upload_dir'] ){
			$item->add_meta_data( '_upload_dir', $values['_upload_dir'], true );
		}
	}

}

