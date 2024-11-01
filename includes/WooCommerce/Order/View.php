<?php

namespace WooUploads\WooCommerce\Order;

defined( 'ABSPATH' ) or exit;

class View {

	public function __construct(){
		add_action( 'woocommerce_after_order_itemmeta', [ $this, 'display_uploads' ], 10, 3 );
		add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hide_metadata' ] );
	}

	public function display_uploads( $item_id, $item, $product ){
		if( ! is_admin() || ! $item->is_type( 'line_item' ) ) return;

		$order_id = $item->get_order_id();
		$uploads = $item->get_meta( '_uploads', true );

		if( $uploads && is_array( $uploads ) ){
			$upload_dir = wp_upload_dir();
			$baseUrl = sprintf( '%s/woouploads', $upload_dir['baseurl'] );
			$orderUrl = sprintf( '%s/%s/', $baseUrl, $order_id );
			$orderUrlThumb = sprintf( '%s/%s/.thumb/', $baseUrl, $order_id );
			$images = '<div class="woo-preview">';
				$images .= '<div class="row">';
					foreach( $uploads as $file_id => $upload ){
						$images .= sprintf(
							'<div class="col-sm-3">
								<img class="woouploads-preview" src="%s" alt="">
								<div class="woouploads-details">
									<label>%s</label> %s
									<p>%s</p>
								</div>
							</div>',
							esc_url( $upload['thumbnail']['url'] ),
							__( 'Quantity:', 'uploads-for-woocommerce' ),
							esc_html( $upload['quantity'] ),
							esc_html( $upload['filename'] )
						);
					}
				$images .= '</div>';
			$images .= '</div>';
			echo $images;
		}
	}

	public function hide_metadata( $metadata ){
		$metadata[] = '_upload_dir';
		$metadata[] = '_uploads';
		return $metadata;
	}

}

