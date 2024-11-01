<?php

namespace WooUploads\WooCommerce\Order;

defined( 'ABSPATH' ) or exit;

class Zip {

	use \WooUploads\Traits\FileSystem;

	public function __construct(){
		add_action( 'admin_init', [ $this, 'generate_zip' ] );
		add_action( 'add_meta_boxes', [ $this, 'metaboxes' ] );
	}

	public function metaboxes(){
		add_meta_box( 'woouploads-zip-archive', __( 'Upload actions', 'uploads-for-woocommerce' ), [ $this, 'uploads_box' ], 'shop_order', 'side', 'high' );
	}

	public function uploads_box( $post ){
		$hasUploads = false;
		$order = wc_get_order( $post->ID );
		foreach( $order->get_items() as $key => $item_data ){
			if( isset( $item_data['_uploads'] ) && $item_data['_uploads'] ){
				$hasUploads = true;
				break;
			}
		}
		if( ! class_exists( 'ZipArchive' ) ){
			echo '<p>', __( 'ZipArchive module not installed. Generating archives is unavailable.', 'uploads-for-woocommerce' ), '</p>';
		} elseif( ! $order->get_meta( '_moved_uploads' ) ){
			echo '<p>', __( 'You need to start processing the order to be able to generate the archive.', 'uploads-for-woocommerce' ), '</p>';
		} elseif( $hasUploads ){
			$zipFile = $order->get_meta( '_zip_archive_filename', true );
			echo '<p>
				<a href="javascript:void(0);" data-href="', esc_url( get_edit_post_link( $order->get_id() ) ), '&amp;zip=', wp_create_nonce( $order->get_id() . '_generate_zip' ),'" class="button button-primary button-block woouploads-create-zip">',
					esc_html__( 'Generate ZIP archive', 'uploads-for-woocommerce' ),
				'</a>
			</p>';
			$upload_dir = wp_upload_dir();
			$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
			$baseUrl = sprintf( '%s/woouploads', $upload_dir['baseurl'] );
			$orderDir = sprintf( '%s/%s/', $baseDir, $order->get_id() );
			$orderUrl = sprintf( '%s/%s/', $baseUrl, $order->get_id() );
			if( $zipFile && file_exists( $orderDir . $zipFile ) ){
				printf(
					/* translators: %s formated url, link of archive file; <strong> HTML equivalent bold */
					__( 'We found a previously generated archive.
						You can regenerate it, by clicking on the <strong>Generate ZIP archive</strong>
						button or you can redownload it by %s.', 'uploads-for-woocommerce' ),
					/* translators: the formated archive url text */
					sprintf( '<a href="%2$s" download>%1$s</a>', __( 'clicking this link', 'uploads-for-woocommerce' ), esc_url( $orderUrl . $zipFile ) )
				);
			}
			do_action( 'woo_uploads_zip_metabox', $order );
		} else {
			/* translators: &#39; HTML quote entity */
			esc_html_e( 'This order doesn&#39;t have any uploads.', 'uploads-for-woocommerce' );
		}
	}

	public function generate_zip(){
		if( isset( $_GET['post'], $_GET['action'], $_GET['zip'] ) && 'edit' == $_GET['action'] && $_GET['zip'] ){
			if( wp_verify_nonce( $_GET['zip'], $_GET['post'] . '_generate_zip' ) ){

				set_time_limit(0);

				// phpcs:disable
				@ini_set( 'zlib.output_compression', 0 );
				@ini_set( 'implicit_flush', 1 );
				// phpcs:enable

				while( ob_get_level() > 0 ) ob_get_clean();

				ob_start( 'ob_gzhandler' );

				ob_implicit_flush( 1 );

				$order_id = absint( $_GET['post'] );
				$zip = new \ZipArchive;
				$order = wc_get_order( $order_id );

				$upload_dir = wp_upload_dir();
				$baseDir = sprintf( '%s/woouploads', $upload_dir['basedir'] );
				$baseUrl = sprintf( '%s/woouploads', $upload_dir['baseurl'] );
				$orderDir = sprintf( '%s/%s/', $baseDir, $order->get_id() );
				$orderUrl = sprintf( '%s/%s/', $baseUrl, $order->get_id() );
				// remove previously generated zip archive
				if( $prev = $order->get_meta( '_zip_archive_filename', true ) ){
					$this->delete( $orderDir . $prev );
				}
				$zipFile = apply_filters( 'woo_uploads_zip_archive_filename', sha1( microtime() . rand( 10000, 99999 ) . $order->get_id() ) . '.zip', $order );
				if( is_readable( $orderDir ) && $zip->open( $orderDir . $zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ){
					$order->update_meta_data( '_zip_archive_filename', $zipFile );
					$order->save();
					/* translators: %d formated digit, order id; <strong> HTML equivalent bold */
					$this->output( __( 'Initializing archive packing on order <strong>#%d</strong>.', 'uploads-for-woocommerce' ), $order->get_id() );
					foreach( $order->get_items() as $id => $item_data ){
						if( isset( $item_data['_uploads'] ) && $item_data['_uploads'] ){
							$dir_name = sprintf( '%s-%s', sanitize_title( $item_data->get_name() ), $id );
							if( $zip->addEmptyDir( $dir_name ) ){
								/* translators: %s formated string, archive directory name; <strong> HTML equivalent bold */
								$this->output( __( 'Successfully created directory <strong>%s</strong> in archive.', 'uploads-for-woocommerce' ), $dir_name );
								foreach( $item_data['_uploads'] as $file_id => $upload ){
									$filename    = explode( '.', $upload['filename'] );
									$file_id_key = explode( '.', $file_id );
									$ext         = array_pop( $filename );
									$ext2        = array_pop( $file_id_key );
									$filename    = join( '-', $filename );
									$file_id_key = substr( join( '-', $file_id_key ), 0, 8 );
									$zip->addFile(
										$orderDir . $file_id,
										$dir_name . '/' .
										apply_filters(
											'woo_uploads_zip_item_filename',
											sprintf(
												'%s-%s-[qty-%d].%s',
												$filename, $file_id_key, $upload['quantity'], $ext
											),
											$filename, $file_id_key, $upload['quantity'], $ext, $item_data, $order
										)
									);
									/* translators: %s formated string, name of the archived file; <strong> HTML equivalent bold */
									$this->output( __( 'Added <strong>%s</strong> to the archive.', 'uploads-for-woocommerce' ), esc_html( $upload['filename'] ) );
								}
								/* translators: %s formated string, order item name; <strong> HTML equivalent bold */
								$this->output( __( 'Finished packing item <strong>%s</strong> into the archive.', 'uploads-for-woocommerce' ), esc_html( $item_data->get_name() ) );
							} else {
								/* translators: %s formated string, archive directory name; <strong> HTML equivalent bold */
								$this->output( __( 'Failed to create <strong>%s</strong> subdirectory in archive.', 'uploads-for-woocommerce' ), esc_html( $dir_name ) );
							}
						}
					}
					/* translators: %d formated digit, order id; <strong> HTML equivalent bold */
					$this->output( __( 'Finished packing order <strong>#%d</strong>.', 'uploads-for-woocommerce' ), $order->get_id() );
					$this->output( '<a href="%s" download>%s</a>', esc_url( $orderUrl . $zipFile ), __( 'Click here to download your archive.', 'uploads-for-woocommerce' ) );
					$zip->close();
				} else {
					$this->output( __( 'Failed to create archive.', 'uploads-for-woocommerce' ) );
				}
			} else {
				$this->output( __( 'Request failed, invalid nonce. Please refresh the page.', 'uploads-for-woocommerce' ) );
			}
			exit;
		}
	}

	public function output( ...$params ){
		echo '<p>';
		call_user_func_array( 'printf', $params );
		echo '</p>';
	}

}

