<?php defined( 'ABSPATH' ) or exit; ?>

<div class="wu-container-fluid">
	<div class="wu-row">
		<div class="wu-col-12">
			<div id="upload-response"></div>
		</div> <!-- end .col -->
	</div> <!-- end .row -->
</div> <!-- end .container -->

<div class="wu-container-fluid upload-post-hide">
	<div class="wu-row">
		<div class="wu-col-12 text-center">

			<div class="upload-box">

				<div class="upload-input">
					<input type="file" id="woo-file-input" class="inputfile" multiple>
					<label for="woo-file-input">
						<i class="icon-upload"></i>
						<div class="divider"></div>
						<p class="description">
							<?php /* translators: <br> HTML break */ ?>
							<?php _e( 'Select one or more files from your<br>computer, or drag and drop them here', 'uploads-for-woocommerce' ); ?>
						</p>
					</label>
				</div>

			</div>

		</div> <!-- end .col -->
	</div> <!-- end .row -->
</div> <!-- end .container -->

<div id="upload-processing">
	<?php
	$i          = 0;
	$upload_dir = wp_upload_dir();
	foreach( WC()->cart->get_cart() as $key => $cart_item ){
		if( ! empty( $cart_item['_uploads'] ) && $this->product_id === $cart_item['product_id'] ){
			foreach( $cart_item['_uploads'] as $file_id => $upload ){
				?>
				<div id="upload-file-<?php echo $i; ?>" class="wu-container-fluid upload-file" data-i="<?php echo $i; ?>">
					<div class="wu-row">
						<div class="wu-col-4">
							<div class="thumb text-center">
								<img src="<?php echo esc_url( $upload['thumbnail']['url'] ); ?>" alt="">
							</div>
						</div>
						<div class="wu-col-8">
							<h4>
								<span class="upload-title">
									<?php echo esc_html( $upload['filename'] ); ?>
								</span>
								<span class="upload-delete" data-id="<?php echo esc_attr( $file_id ); ?>" data-i="<?php echo $i; ?>">
									<span class="wu-remove">&times;</span>
								</span>
							</h4>
							<div class="wu-container-fluid wu-quantity">
								<input type="hidden" name="id[<?php echo esc_attr( $file_id ); ?>]" value="<?php echo esc_html( $file_id ); ?>">
								<label>
									<?php esc_html_e( 'Quantity:', 'uploads-for-woocommerce' ); ?>
								</label>
								<input type="number" name="woo-upload-quantity[<?php echo esc_attr( $file_id ); ?>]" step="1" min="1" class="woo-upload-quantity" value="<?php echo esc_attr( $upload['quantity'] ); ?>" data-id="<?php echo esc_attr( $file_id ); ?>" data-i="<?php echo $i; ?>">
							</div>
						</div>
					</div>
				</div>

				<?php
				$i++;
			}
		}
	}
	?>
</div>
