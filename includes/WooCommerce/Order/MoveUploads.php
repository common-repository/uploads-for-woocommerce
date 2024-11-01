<?php

namespace WooUploads\WooCommerce\Order;

defined( 'ABSPATH' ) or exit;

class MoveUploads {

	use \WooUploads\Traits\FileSystem;

	public function __construct(){
		add_action( 'woocommerce_order_status_changed', [ $this, 'order_status_changed' ], 10, 3 );
	}

	public function order_status_changed( $order_id, $old_status, $new_status ){
		if( 'processing' == $new_status ){
			$order = wc_get_order( $order_id );
			if( $order->get_meta( '_moved_uploads' ) ) return;
			// move them
			$upload_dir = wp_upload_dir();
			$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
			$baseUrl = sprintf( '%s/woouploads', $upload_dir['baseurl'] );
			$tmpUploadDirs = [];
			// loop over cart items
			foreach( $order->get_items() as $item_id => $item_data ){
				$uploads = wc_get_order_item_meta( $item_id, '_uploads', true );
				if( $uploads && is_array( $uploads ) ){
					$item_upload_dir = wc_get_order_item_meta( $item_id, '_upload_dir', true );
					// create order upload dir
					$tmpDir = sprintf( '%s/.tmp/%s/', $baseDir, $item_upload_dir );
					$orderDir = sprintf( '%s/%s/', $baseDir, $order->get_id() );
					$orderDirThumb = sprintf( '%s/%s/.thumb/', $baseDir, $order->get_id() );
					$orderUrlThumb = sprintf( '%s/%s/.thumb/', $baseUrl, $order->get_id() );
					$tmpUploadDirs[] = $tmpDir; // could change when user logges in before placing order
					if( $this->mkdir( $baseDir, $orderDirThumb ) ){
						foreach( $uploads as $file_id => &$upload ){
							$this->move( $tmpDir . $file_id, $orderDir . $file_id );
							$this->move( $upload['thumbnail']['location'], $orderDirThumb . $upload['thumbnail']['filename'] );
							$upload['thumbnail']['location'] = $orderDirThumb . $upload['thumbnail']['filename'];
							if( null !== $upload['thumbnail']['filename'] ){
								$upload['thumbnail']['url'] = $orderUrlThumb . $upload['thumbnail']['filename'];
							}
						}
						wc_update_order_item_meta( $item_id, '_uploads', $uploads );
						wc_delete_order_item_meta( $item_id, '_upload_dir' );
					}
				}
			}
			$tmpUploadDirs = array_values( array_unique( $tmpUploadDirs ) );
			foreach( $tmpUploadDirs as $tmpUploadDir ){
				// delete tmp folder if empty
				$this->delete_if_empty( $tmpUploadDir );
			}
			$order->add_meta_data( '_moved_uploads', 'true', true );
			$order->save();
		}
	}

}

