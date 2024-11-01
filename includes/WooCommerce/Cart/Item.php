<?php

namespace WooUploads\WooCommerce\Cart;

defined( 'ABSPATH' ) or exit;

class Item {

	use \WooUploads\Traits\Visibility; //this trait autoloads the ProductId trait

	public function __construct(){
		add_filter( 'woocommerce_cart_item_quantity', [ $this, 'quantity_selector' ], 10, 3 );
		add_action( 'woocommerce_remove_cart_item', [ $this, 'cart_item_delete_uploads' ], 10, 2 );
		add_filter( 'woocommerce_cart_item_name', [ $this, 'cart_item_name' ], 10, 3 );
		add_filter( 'woocommerce_cart_item_class', [ $this, 'cart_item_class' ], 10, 3 );
	}

	public function quantity_selector( $product_quantity, $cart_item_key, $cart_item ){
		$multiply_by_quantity = get_option( 'woo_uploads_upload_as_quantity', 'no' );
		if( 'yes' == $multiply_by_quantity && isset( $cart_item['_uploads'] ) && $cart_item['_uploads'] ){
			/* translators: &#39; HTML quote entity */
			$product_quantity = $cart_item['quantity'] . '<br><sub>' . __( 'If you want to change the quantity, visit this product&#39;s page.', 'uploads-for-woocommerce' ) . '</sub>';
		}
		return $product_quantity;
	}

	public function cart_item_delete_uploads( $cart_item_key, $cart ){
		$item = $cart->cart_contents[ $cart_item_key ];
		if( isset( $item['_uploads'] ) && $item['_uploads'] ){
			$upload_dir = wp_upload_dir();
			$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
			$tmpDir = sprintf( '%s/.tmp/%s/', $baseDir, $item['_upload_dir'] );
			foreach( $item['_uploads'] as $file_id => $upload ){
				if( file_exists( $tmpDir . $file_id ) ){
					@unlink( $tmpDir . $file_id );
				}
				if( file_exists( $upload['thumbnail']['location'] ) ){
					@unlink( $upload['thumbnail']['location'] );
				}
			}
			unset( $item['_uploads'] );
			$cart->cart_contents[ $cart_item_key ] = $item;
		}
	}

	public function cart_item_name( $item_name, $cart_item, $cart_item_key ){
		$images = '';
		if( isset( $cart_item['_uploads'] ) && $cart_item['_uploads'] ){
			$images .= '<br><br><div class="wu-conteiner-fluid woo-preview">';
				$images .= '<div class="wu-row">';
				foreach( $cart_item['_uploads'] as $file_id => $upload ){
					$images .= sprintf(
						'<div class="wu-col-3">
							<img class="woouploads-preview" src="%s" alt="">
							<div class="woouploads-details">
								<span>%s</span> %s
							</div>
						</div>',
						esc_url( $upload['thumbnail']['url'] ),
						__( 'Quantity:', 'uploads-for-woocommerce' ),
						esc_html( $upload['quantity'] )
					);
				}
				$images .= '</div>';
			$images .= '</div>';
			$images .= '<br>';
		}
		return $item_name . $images;
	}

	public function cart_item_class( $class_name, $cart_item, $cart_item_key ){
		if( isset( $cart_item['_uploads'] ) && $cart_item['_uploads'] ){
			$class_name .= ' woouploads-cart-item-has-upload';
		}
		return $class_name;
	}

}
