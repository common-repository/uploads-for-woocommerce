<?php

namespace WooUploads\WooCommerce\Products;

defined( 'ABSPATH' ) or exit;

class Form {

	use \WooUploads\Traits\Visibility; //this trait autoloads the ProductId trait
	use \WooUploads\Traits\Session;

	public function __construct(){
		add_action( 'woocommerce_init', [ $this, 'session_init' ] );
		add_action( 'wp', [ $this, 'register_additional_hooks' ] );
	}

	public function register_additional_hooks(){
		if( $this->is_visible_on_product() ){
			add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'render' ] );
		}
	}

	public function render(){
		global $product;
		include WOO_UPLOADS_DIR . 'templates/form.php';
		include WOO_UPLOADS_DIR . 'templates/form-template.php';
	}

}
