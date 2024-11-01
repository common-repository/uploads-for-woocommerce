<?php

namespace WooUploads;

defined( 'ABSPATH' ) or exit;

use \WooUploads\i18n\AjaxStrings;
use \WooUploads\App;

class Assets {

	use \WooUploads\Traits\Visibility; //this trait autoloads the ProductId trait

	public function __construct(){
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
		add_filter( 'body_class', [ $this, 'add_body_class' ], 10, 2 );
	}

	public function admin_assets( $page ){
		if( 'post.php' == $page ){
			wp_enqueue_style( 'woouploads-admin-css', WOO_UPLOADS_URL . 'assets/css/uploads-for-woocommerce-admin.css', [], WOO_UPLOADS_VERSION );
			wp_enqueue_script( 'woouploads-admin-js', WOO_UPLOADS_URL . 'assets/js/uploads-for-woocommerce-admin.js', [ 'jquery' ], WOO_UPLOADS_VERSION, true );
		}
	}

	public function frontend_assets(){
		if( $this->is_visible_on_product() || ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ){
			wp_enqueue_style( 'woouploads-css', WOO_UPLOADS_URL . 'assets/css/uploads-for-woocommerce.css', WOO_UPLOADS_VERSION );
			wp_enqueue_script( 'woouploads-js', WOO_UPLOADS_URL . 'assets/js/uploads-for-woocommerce.js', [ 'jquery' ], WOO_UPLOADS_VERSION, true );
			wp_localize_script( 'woouploads-js', 'WooUploads', [
				'admin'      => [
					'ajax_url'     => admin_url( 'admin-ajax.php' ),
					'max_filesize' => wp_max_upload_size(),
					'product_id'   => $this->get_product_id(),
					'loader'       => sprintf( '<img class="woo-quick-loader" src="%s/assets/images/ajax-loader.gif" alt="">', esc_url( WOO_UPLOADS_URL ) ),
					'nonce'        => [
						'upload' => wp_create_nonce( APP::ACTION_UPLOAD ),
						'delete' => wp_create_nonce( APP::ACTION_DELETE ),
						'cancel' => wp_create_nonce( APP::ACTION_CANCEL ),
						'update' => wp_create_nonce( APP::ACTION_UPDATE ),
					],
					'actions'      => [
						'upload' => APP::ACTION_UPLOAD,
						'delete' => APP::ACTION_DELETE,
						'cancel' => APP::ACTION_CANCEL,
						'update' => APP::ACTION_UPDATE,
					],
				],
				'i18n'       => ( new AjaxStrings )->get_localized_strings(),
				'validators' => apply_filters( 'woo_uploads_js_validators', [] ),
				'custom'     => apply_filters( 'woo_uploads_custom_ajax_object', [] ),
			] );
		}
	}

	public function add_body_class( $classes, $class ){
		$classes[] = 'woouploads';
		$multiply_by_quantity = get_option( 'woo_uploads_upload_as_quantity', 'no' );
		if( 'yes' == $multiply_by_quantity ){
			$classes[] = 'woouploads-multiply-quantity';
		} else {
			$classes[] = 'woouploads-default-quantity';
		}
		if( $this->is_visible_on_product() ){
			$classes[] = 'woouploads-enabled';
		} else {
			$classes[] = 'woouploads-disabled';
		}
		return $classes;
	}

}
