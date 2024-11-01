<?php

namespace WooUploads\WooCommerce\Settings;

defined( 'ABSPATH' ) or exit;

use WooUploads\WooCommerce\Settings\Section;

class Page {

	public function __construct(){
		add_filter( 'woocommerce_get_settings_pages', [ $this, 'add_page' ] );
	}

	public function add_page( $settings ){
		$settings['woo_uploads'] = new Section;
		return $settings;
	}

}
