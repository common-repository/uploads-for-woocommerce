<?php

namespace WooUploads\i18n;

defined( 'ABSPATH' ) or exit;

class TextDomain {

	public function __construct(){
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'admin_init', [ $this, 'load_textdomain' ] );
	}

	public function load_textdomain(){
		load_plugin_textdomain( 'uploads-for-woocommerce', false, '/uploads-for-woocommerce/languages' );
	}

}
