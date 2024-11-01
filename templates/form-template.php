<?php defined( 'ABSPATH' ) or exit; ?>
<script type="text/plain" id="woo-upload-template">
	<div id="upload-file-{{i}}" class="wu-container-fluid upload-file in-progress is-preview-loading" data-i="{{i}}">
		<div class="wu-row">
			<div class="wu-col-4">
				<div class="thumb text-center">
					<img src="<?php echo esc_url( WOO_UPLOADS_URL . '/assets/images/ajax-loader.gif' ); ?>" alt="">
				</div>
			</div>
			<div class="wu-col-8">
				<h4>
					<span class="upload-title" title="{{filename}}">
						{{filename}}
					</span>
					<span class="upload-delete tmp-upload" data-id="{{ID}}" data-i="{{i}}">
						<span class="wu-remove">&times;</span>
					</span>
				</h4>
				<div class="wu-container-fluid upload-post-pending">
					<div class="wu-row">
						<div class="wu-col-12">
							<span class="upload-status"><?php esc_html_e( 'Uploading', 'uploads-for-woocommerce' ); ?></span>
							<div class="progress">
								<div class="progress-bar" role="progressbar"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="wu-container-fluid wu-quantity upload-post-processed">
					<input type="hidden" name="id[{{ID}}]" value="{{ID}}">
					<label>
						<?php esc_html_e( 'Quantity:', 'uploads-for-woocommerce' ); ?>
					</label>
					<input type="number" name="woo-upload-quantity[{{ID}}]" step="1" min="1" class="woo-upload-quantity tmp-upload" value="1" data-id="{{ID}}" data-i="{{i}}">
				</div>
			</div>
		</div>
	</div>
</script>
