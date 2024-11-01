<?php

namespace WooUploads\WooCommerce\Order;

defined( 'ABSPATH' ) or exit;

class Delete {

	use \WooUploads\Traits\FileSystem;

	public function __construct(){
		add_action( 'woocommerce_delete_order_items', [ $this, 'delete_order_uploads' ] );
	}

	public function delete_order_uploads( $order_id ){
		$order = wc_get_order( $order_id );
		$upload_dir = wp_upload_dir();
		$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
		$orderDir = sprintf( '%s/%s/', $baseDir, $order->get_id() );
		$this->delete( $orderDir, true );
	}

}
