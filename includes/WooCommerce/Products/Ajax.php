<?php

namespace WooUploads\WooCommerce\Products;

defined( 'ABSPATH' ) or exit;

use \WooUploads\App;

class Ajax {

	use \WooUploads\Traits\FileSystem;
	use \WooUploads\Traits\Session;
	use \WooUploads\Traits\Visibility; //this trait autoloads the ProductId trait

	public function __construct(){
		add_action( 'woocommerce_init', [ $this, 'session_init' ] );
		add_action( 'wp_ajax_' . APP::ACTION_UPLOAD, [ $this, 'upload' ] );
		add_action( 'wp_ajax_nopriv_' . APP::ACTION_UPLOAD, [ $this, 'upload' ] );
		add_action( 'wp_ajax_' . APP::ACTION_DELETE, [ $this, 'delete' ] );
		add_action( 'wp_ajax_nopriv_' . APP::ACTION_DELETE, [ $this, 'delete' ] );
		add_action( 'wp_ajax_' . APP::ACTION_CANCEL, [ $this, 'cancel' ] );
		add_action( 'wp_ajax_nopriv_' . APP::ACTION_CANCEL, [ $this, 'cancel' ] );
		add_action( 'wp_ajax_' . APP::ACTION_UPDATE, [ $this, 'update' ] );
		add_action( 'wp_ajax_nopriv_' . APP::ACTION_UPDATE, [ $this, 'update' ] );
	}

	public function upload(){
		$this->is_woocommerce_or_exit();
		if( isset( $_FILES['woo-upload-file'], $_FILES['woo-upload-file']['name'], $_POST['woo_nonce'], $_POST['product_id'] ) ){
			// verify nonce
			if( ! wp_verify_nonce( $_POST['woo_nonce'], APP::ACTION_UPLOAD ) ){
				wp_send_json_error( __( 'Please refresh the page.', 'uploads-for-woocommerce' ) );
			}
			// check if upload is enabled for this product
			if( ! $this->upload_enabled( $_POST['product_id'] ) ){
				/* translators: &#39; HTML quote entity */
				wp_send_json_error( __( 'Upload isn&#39;t enabled for this product.', 'uploads-for-woocommerce' ) );
			}
			// create directories
			$upload_dir = wp_upload_dir();
			$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
			$baseUrl = sprintf( '%s/woouploads', $upload_dir['baseurl'] );
			// create tmp session upload dir
			$tmpDir = sprintf( '%s/.tmp/%s/', $baseDir, WC()->session->get_customer_id() );
			$tmpUrl = sprintf( '%s/.tmp/%s/', $baseUrl, WC()->session->get_customer_id() );
			$tmpDirThumb = sprintf( '%s/.tmp/%s/.thumb/', $baseDir, WC()->session->get_customer_id() );
			$tmpUrlThumb = sprintf( '%s/.tmp/%s/.thumb/', $baseUrl, WC()->session->get_customer_id() );
			// create directories recursively
			if( $this->mkdir( $baseDir, $tmpDirThumb ) ){
				//get all validators
				$validators = APP::init()->get_validators();
				// validate file extension, is allowed file extension?
				if( $validators['file_extension']->validate( $_FILES['woo-upload-file'] ) ){
					$ext       = $validators['file_extension']->get_file_extension( $_FILES['woo-upload-file']['name'] );
					$filename  = sha1( microtime( true ) . $_FILES['woo-upload-file']['name'] . openssl_random_pseudo_bytes( 8 ) ) . '.' . $ext;
					$thumbnail = null;
					// if validator defined, we validate the file through it
					if( isset( $validators[ $ext ] ) ){
						if( $validators[ $ext ]->validate( $_FILES['woo-upload-file'] ) ){
							if( is_callable( [ $validators[ $ext ], 'generate_thumbnail' ] ) ){
								$thumbnail = $validators[ $ext ]->generate_thumbnail( $_FILES['woo-upload-file'], $filename, $tmpDirThumb );
							}
						} else {
							wp_send_json_error( __( 'Failed to validate the uploaded file.', 'uploads-for-woocommerce' ) );
						}
					}
					// else move on
					// save the original file
					if( move_uploaded_file( $_FILES['woo-upload-file']['tmp_name'], trailingslashit( $tmpDir ) . $filename ) ){
						$__thumbnail = $thumbnail
								? trailingslashit( $tmpUrlThumb ) . $thumbnail
								: apply_filters( 'woo_uploads_thumbnail_' . $ext, WOO_UPLOADS_URL . 'assets/images/default_thumbnail.png' );
						$product_id = absint( $_POST['product_id'] );
						$WC_uploads = WC()->session->get( $product_id . '_tmp_uploads', [] );
						$WC_uploads[ $filename ] = [
							'filename'  => sanitize_file_name( $_FILES['woo-upload-file']['name'] ),
							'thumbnail' => [
								'url'      => $__thumbnail,
								'location' => str_replace( '\\', '/', $tmpDirThumb ) . $thumbnail, // replace backslash with forward slash for windows compatibility
								'filename' => $thumbnail,
							],
							'quantity'  => 1,
						];
						WC()->session->set( $product_id . '_tmp_uploads', $WC_uploads );
						wp_send_json_success( [
							'file_id' => sanitize_file_name( $filename ),
							'thumb'   => $__thumbnail,
						] );
					} else {
						@unlink( $tmpDirThumb . $thumbnail );
						wp_send_json_error( __( 'Unable to save file to temporary directory.', 'uploads-for-woocommerce' ) );
					}
				} else {
					wp_send_json_error( __( 'Invalid file type uploaded.', 'uploads-for-woocommerce' ) );
				}
			} else {
				wp_send_json_error( __( 'Unable to create temporary upload directory.', 'uploads-for-woocommerce' ) );
			}
		}
		/* translators: Something went wrong, please try again. User shouldn't even see this error, happens only if someone is tinkering with the ajax calls */
		wp_send_json_error( __( 'Sum ting went wong, wi tu lo', 'uploads-for-woocommerce' ) );
		wp_die();
	}

	public function delete(){
		if( isset( $_POST['file_id'], $_POST['woo_nonce'], $_POST['product_id'] ) ){
			// verify nonce
			if( ! wp_verify_nonce( $_POST['woo_nonce'], APP::ACTION_DELETE ) ){
				wp_send_json_error( __( 'Please refresh the page.', 'uploads-for-woocommerce' ) );
			}
			// setup some variables
			$file_id = sanitize_text_field( $_POST['file_id'] );
			$product_id = absint( $_POST['product_id'] );
			$upload_dir = wp_upload_dir();
			$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
			$tmpDir = sprintf( '%s/.tmp/%s/', $baseDir, WC()->session->get_customer_id() );
			// delete from tmp
			if( isset( $_POST['tmp_upload'] ) && 'true' == $_POST['tmp_upload'] ){
				$WC_uploads = WC()->session->get( $product_id . '_tmp_uploads', [] );
				if( isset( $WC_uploads[ $file_id ] ) ){
					if( file_exists( $tmpDir . $file_id ) ){
						@unlink( $tmpDir . $file_id );
					}
					if( file_exists( $WC_uploads[ $file_id ]['thumbnail']['location'] ) ){
						@unlink( $WC_uploads[ $file_id ]['thumbnail']['location'] );
					}
					unset( $WC_uploads[ $file_id ] );
					WC()->session->set( $product_id . '_tmp_uploads', $WC_uploads );
				}
			// delete from cart item
			} else {
				foreach( WC()->cart->cart_contents as $key => $cart_item ){
					if( $cart_item['product_id'] == $product_id && isset( $cart_item['_uploads'][ $file_id ] ) && $cart_item['_uploads'] ){
						$qty = $cart_item['quantity'] - $cart_item['_uploads'][ $file_id ]['quantity'];
						$multiply_by_quantity = get_option( 'woo_uploads_upload_as_quantity', 'no' );
						if( file_exists( $tmpDir . $file_id ) ){
							@unlink( $tmpDir . $file_id );
						}
						if( file_exists( $cart_item['_uploads'][ $file_id ]['thumbnail']['location'] ) ){
							@unlink( $cart_item['_uploads'][ $file_id ]['thumbnail']['location'] );
						}
						unset( $cart_item['_uploads'][ $file_id ] );
						WC()->cart->cart_contents[ $key ] = $cart_item;
						if( 'yes' == $multiply_by_quantity ){
							WC()->cart->set_quantity( $key, $qty, 0 );
						} elseif( ! count( $cart_item['_uploads'] ) ){
							WC()->cart->set_quantity( $key, 0, 0 );
						}
						WC()->cart->set_session();
						break;
					}
				}
			}
			/* translators: %s formated string of filename */
			wp_send_json_success( sprintf( __( 'Successfully deleted %s', 'uploads-for-woocommerce' ), esc_html( $file_id ) ) );
		}
		/* translators: Something went wrong, please try again. User shouldn't even see this error, happens only if someone is tinkering with the ajax calls */
		wp_send_json_error( __( 'Sum ting went wong, wi tu lo', 'uploads-for-woocommerce' ) );
		wp_die();
	}

	public function cancel(){
		if( isset( $_POST['woo_nonce'], $_POST['product_id'] ) ){
			// verify nonce
			if( ! wp_verify_nonce( $_POST['woo_nonce'], APP::ACTION_CANCEL ) ){
				wp_send_json_error( __( 'Please refresh the page.', 'uploads-for-woocommerce' ) );
			}
			// setup some variables
			$product_id = absint( $_POST['product_id'] );
			$upload_dir = wp_upload_dir();
			$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
			$tmpDir = sprintf( '%s/.tmp/%s/', $baseDir, WC()->session->get_customer_id() );
			// delete file from storage and session
			$WC_uploads = WC()->session->get( $product_id . '_tmp_uploads', [] );
			foreach( $WC_uploads as $file_id => $upload ){
				if( file_exists( $tmpDir . $file_id ) ){
					@unlink( $tmpDir . $file_id );
				}
				if( file_exists( $upload['thumbnail']['location'] ) ){
					@unlink( $upload['thumbnail']['location'] );
				}
			}
			WC()->session->set( $product_id . '_tmp_uploads', [] );
			wp_send_json_success( __( 'Successfully deleted all uploads.', 'uploads-for-woocommerce' ) );
		}
		/* translators: Something went wrong, please try again. User shouldn't even see this error, happens only if someone is tinkering with the ajax calls */
		wp_send_json_error( __( 'Sum ting went wong, wi tu lo', 'uploads-for-woocommerce' ) );
		wp_die();
	}

	public function update(){
		if( isset( $_POST['woo_nonce'], $_POST['product_id'], $_POST['file_id'], $_POST['woo_quantity'] ) ){
			// verify nonce
			if( ! wp_verify_nonce( $_POST['woo_nonce'], APP::ACTION_UPDATE ) ){
				wp_send_json_error( __( 'Please refresh the page.', 'uploads-for-woocommerce' ) );
			}
			// setup some variables
			$file_id = sanitize_text_field( $_POST['file_id'] );
			$product_id = absint( $_POST['product_id'] );
			// update quantity in tmp session
			if( isset( $_POST['tmp_upload'] ) && 'true' == $_POST['tmp_upload'] ){
				$WC_uploads = WC()->session->get( $product_id . '_tmp_uploads', [] );
				if( isset( $WC_uploads[ $file_id ]['quantity'] ) ){
					$WC_uploads[ $file_id ]['quantity'] = absint( $_POST['woo_quantity'] ) ?? 1;
					WC()->session->set( $product_id . '_tmp_uploads', $WC_uploads );
				}
			} else {
				// update quantity in cart
				foreach( WC()->cart->cart_contents as $key => $cart_item ){
					if( $product_id == $cart_item['product_id'] && isset( $cart_item['_uploads'][ $file_id ] ) ){
						$cart_item['_uploads'][ $file_id ]['quantity'] = absint( $_POST['woo_quantity'] ) ?? 1;
						$qty = 0;
						foreach( $cart_item['_uploads'] as $file_id => $upload ){
							$qty += $upload['quantity'];
						}
						WC()->cart->cart_contents[ $key ] = $cart_item;
						$multiply_by_quantity = get_option( 'woo_uploads_upload_as_quantity', 'no' );
						if( 'yes' == $multiply_by_quantity ){
							WC()->cart->set_quantity( $key, $qty, 0 );
						}
						WC()->cart->set_session();
						break;
					}
				}
			}
			/* translators: %s formated string of filename */
			wp_send_json_success( sprintf( __( 'Successfully updated %s', 'uploads-for-woocommerce' ), esc_html( $file_id ) ) );
		}
		/* translators: Something went wrong, please try again. User shouldn't even see this error, happens only if someone is tinkering with the ajax calls */
		wp_send_json_error( __( 'Sum ting went wong, wi tu lo', 'uploads-for-woocommerce' ) );
		wp_die();
	}

}
