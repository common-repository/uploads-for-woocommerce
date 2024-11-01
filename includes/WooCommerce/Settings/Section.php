<?php

namespace WooUploads\WooCommerce\Settings;

defined( 'ABSPATH' ) or exit;

use \WooUploads\App;

class Section extends \WC_Settings_Page {

	public function __construct(){
		$this->id = 'woo_uploads';
		$this->label = __( 'Uploads', 'uploads-for-woocommerce' );
		parent::__construct();
	}

	public function get_sections(){
		return apply_filters( 'woocommerce_get_sections_' . $this->id, [
			'general' => __( 'General', 'uploads-for-woocommerce' ),
		] );
	}

	public function get_settings(){
		global $current_section;
		return apply_filters( 'woocommerce_get_settings_' . $this->id, [
			[
				'title' => __( 'File settings', 'uploads-for-woocommerce' ),
				'type'  => 'title',
				'id'    => 'woo_uploads_file_settings',
			],
			[
				'title'   => __( 'Allowed file extensions', 'uploads-for-woocommerce' ),
				'desc'    => __( 'Separate all file extensions with a space and without the dot. Ex: jpg png doc pdf', 'uploads-for-woocommerce' ),
				'type'    => 'text',
				'id'      => 'woo_uploads_allowed_ext',
				'default' => APP::DEFAULT_FILE_EXT_SUPPORT,
			],
			[
				'title'   => __( 'Uploads as quantity', 'uploads-for-woocommerce' ),
				'desc'    => __( 'Multiply each uploaded file by product price', 'uploads-for-woocommerce' ),
				'type'    => 'checkbox',
				'id'      => 'woo_uploads_upload_as_quantity',
				'default' => 'yes',
			],
			[
				'type' => 'sectionend',
				'id'   => 'store_address',
			],
		], $current_section );
	}

}
