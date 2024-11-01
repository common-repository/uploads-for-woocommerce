<?php

namespace WooUploads;

defined( 'ABSPATH' ) or exit;

class App {

	const ACTION_UPLOAD = 'woouploads_upload';
	const ACTION_DELETE = 'woouploads_delete';
	const ACTION_CANCEL = 'woouploads_cancel';
	const ACTION_UPDATE = 'woouploads_update';

	const DEFAULT_FILE_EXT_SUPPORT = 'jpg jpeg png';

	static private $_inst;

	private $validators = [];

	static public function init(){
		if( null === self::$_inst ){
			self::$_inst = new self;
		}
		return self::$_inst;
	}

	private function __construct(){
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
		add_action( 'woocommerce_init', [ $this, 'register_validators' ] );
	}

	public function on_plugins_loaded(){
		new \WooUploads\i18n\TextDomain;
		new \WooUploads\Assets;
		new \WooUploads\WooCommerce\Products\DataTabs;
		new \WooUploads\WooCommerce\Products\Form;
		new \WooUploads\WooCommerce\Products\Ajax;
		new \WooUploads\WooCommerce\Cart\Add;
		new \WooUploads\WooCommerce\Cart\Item;
		new \WooUploads\WooCommerce\Order\Checkout;
		new \WooUploads\WooCommerce\Order\Delete;
		new \WooUploads\WooCommerce\Order\MoveUploads;
		new \WooUploads\WooCommerce\Order\View;
		new \WooUploads\WooCommerce\Order\Zip;
		new \WooUploads\WooCommerce\Settings\Page;
	}

	public function register_validators(){
		$this->validators = apply_filters( 'woo_uploads_validators', [
			'jpg'            => new \WooUploads\Validators\JPG,
			'jpeg'           => new \WooUploads\Validators\JPEG,
			'png'            => new \WooUploads\Validators\PNG,
			'file_extension' => new \WooUploads\Validators\FileExtension,
		] );
	}

	public function get_validators(){
		return $this->validators;
	}

}
