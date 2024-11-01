<?php

namespace WooUploads\WooCommerce\Cart;

defined( 'ABSPATH' ) or exit;

class Add {

	use \WooUploads\Traits\Visibility; //this trait autoloads the ProductId trait

	public function __construct(){
		add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate' ], 10, 3 );
		add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_to_cart_item_data' ], 10, 4 );
		add_filter( 'woocommerce_loop_add_to_cart_link', [ $this, 'loop_add_to_cart_button' ], 10, 3 );
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'calculate_price' ], 20, 1 );
	}

	public function validate( $passed, $product_id, $quantity ){
		if( $this->upload_enabled( $product_id ) ){
			$tmp = $WC_uploads = WC()->session->get( $product_id . '_tmp_uploads', [] );
			if( ! $tmp ){
				wc_add_notice( __( 'Please upload a file before adding the product to your cart.', 'uploads-for-woocommerce' ), 'error' );
				$passed = false;
			}
		}
		return $passed;
	}

	public function add_to_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ){
		if( $this->upload_enabled( $product_id ) ){
			$WC_tmp = WC()->session->get( $product_id . '_tmp_uploads', [] );
			if( isset( $cart_item_data['_uploads'] ) ){
				$cart_item_data['_uploads'] = array_merge( $cart_item_data['_uploads'], $WC_tmp );
			} else {
				$cart_item_data['_uploads'] = $WC_tmp;
			}
			$cart_item_data['_upload_dir'] = WC()->session->get_customer_id();
			WC()->session->set( $product_id . '_tmp_uploads', [] );
		}
		return $cart_item_data;
	}

	public function loop_add_to_cart_button( $link, $product, $args ){
		if( $this->upload_enabled( $product->get_id() ) ){
			$link = sprintf(
				'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
				esc_url( $product->get_permalink() ),
				esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
				esc_attr( isset( $args['class'] ) ? str_replace( 'ajax_add_to_cart', '', $args['class'] ) : 'button' ),
				isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
				esc_html__( 'View product', 'uploads-for-woocommerce' )
			);
		}
		return $link;
	}

	public function calculate_price( $cart ){
		$multiply_by_quantity = get_option( 'woo_uploads_upload_as_quantity', 'no' );
		if( 'yes' == $multiply_by_quantity ){
			foreach( $cart->get_cart() as $key => $item ){
				if( isset( $item['_uploads'] ) && $item['_uploads'] ){
					$qty = 0;
					foreach( $item['_uploads'] as $upload ){
						$qty += $upload['quantity'];
					}
					if( $qty > 0 ){
						$cart->set_quantity( $key, $qty, 0 );
					}
				}
			}
		}
	}

}
