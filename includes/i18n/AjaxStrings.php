<?php

namespace WooUploads\i18n;

defined( 'ABSPATH' ) or exit;

class AjaxStrings {

	public function get_localized_strings(){
		return apply_filters( 'woo_uploads_ajax_strings', [
			'are_you_sure_cancel_upload' => __( 'Are you sure you want to cancel the upload?', 'uploads-for-woocommerce' ),
			'please_upload_files'        => __( 'Please upload a file before adding the product to your cart.', 'uploads-for-woocommerce' ),
			'preparing_uploads'          => __( 'Please wait, while we prepare your files for upload.', 'uploads-for-woocommerce' ),
			/* translators: {{filename}} is a variable for JavaScript to be replaced with the uploaded filename */
			'filesize_to_big'            => __( 'The filesize of <strong>{{filename}}</strong> is too large.', 'uploads-for-woocommerce' ),
			/* translators: {{filename}} is a variable for JavaScript to be replaced with the uploaded filename; &#39; HTML quote entity */
			'invalid_file_type'          => __( 'The uploaded file <strong>{{filename}}</strong> isn&#39;t supported.', 'uploads-for-woocommerce' ),
			'processing'                 => __( 'Processing', 'uploads-for-woocommerce' ),
			'error'                      => __( 'Error', 'uploads-for-woocommerce' ),
			'please_refresh_page'        => __( 'Please refresh the page.', 'uploads-for-woocommerce' ),
			'confirm_img_delete'         => __( 'Are you sure you want to delete this file?', 'uploads-for-woocommerce' ),
			'please_wait_until_updated'  => __( 'Please wait until quantity is updated.', 'uploads-for-woocommerce' ),
			'post_processing_failed'     => __( 'Post-processing of the file failed likely because the server is busy or does not have enough resources.', 'uploads-for-woocommerce' ),
		] );
	}

}
