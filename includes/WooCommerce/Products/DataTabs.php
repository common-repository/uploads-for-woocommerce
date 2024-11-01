<?php

namespace WooUploads\WooCommerce\Products;

defined( 'ABSPATH' ) or exit;

class DataTabs {

	public function __construct(){
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'upload_tab' ] );
		add_action( 'woocommerce_product_data_panels', [ $this, 'tab_data' ] );
		add_action( 'woocommerce_update_product', [ $this, 'update' ] );
	}

	/**
	 * Add new tab to products data
	**/
	public function upload_tab( $tabs ){
		$tabs['woo_uploads'] = array(
			'label'    => __( 'Upload', 'uploads-for-woocommerce' ),
			'target'   => 'woo_uploads_data_tab',
			'priority' => 10,
			'class'    => [],
		);
		return $tabs;
	}

	/**
	 * Content of woo_uploads_tab
	**/
	public function tab_data(){
		global $post;
		$woo_uploads_on = (bool) get_post_meta( $post->ID, 'woo_uploads_on', true );
		echo '<div id="woo_uploads_data_tab" class="panel woocommerce_options_panel">';
			echo '<p class="form-field">
				<label for="woo_uploads_on">', esc_html( 'Enable file upload', 'uploads-for-woocommerce' ), '</label>
				<input type="checkbox" id="woo_uploads_on" name="woo_uploads_on" value="1" ', checked( $woo_uploads_on, true, false ), '>
			</p>';
			do_action( 'woo_uploads_product_datatab_form', $post->ID );
		echo '</div>';
	}

	public function update( $product_id ){
		// $product = wc_get_product( $product_id );
		$woo_uploads_on = ! empty( $_POST['woo_uploads_on'] )
			? (bool) $_POST['woo_uploads_on']
			: false;
		update_post_meta( $product_id, 'woo_uploads_on', $woo_uploads_on );
		do_action( 'woo_uploads_product_datatab_update', $product_id );
	}

}
