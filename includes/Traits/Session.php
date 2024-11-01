<?php

namespace WooUploads\Traits;

defined( 'ABSPATH' ) or exit;

trait Session {

	public function session_init(){
		if( $this->is_woocommerce() ){
			if( WC()->session ){
				WC()->session->set_customer_session_cookie( true );
			}
		}
	}

	public function is_woocommerce(){
		return function_exists( 'WC' );
	}

	public function is_woocommerce_or_exit(){
		if( ! $this->is_woocommerce() ){
			exit( __( 'WooCommerce not active.', 'uploads-for-woocommerce' ) );
		}
		return true;
	}

}
