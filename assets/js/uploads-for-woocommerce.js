jQuery(function($){

	var isUploading = false;
	var hasUploads = 0;
	var submited = false;
	var updatingQuantity = 0;
	var timeout;
	var validators = WooUploads.validators;

	for( extension in WooUploads.validators ){
		validators[ extension ] = new Function( 'file', validators[ extension ] );
	}

	//~ window.onbeforeunload = function(e){
		//~ if( isUploading || hasUploads ){
			//~ return WooUploads.i18n.are_you_sure_cancel_upload;
		//~ }
	//~ };

	window.onunload = function(e){
		if( ! submited && ( isUploading || hasUploads ) ){
			$.ajax({
				url: WooUploads.admin.ajax_url,
				type: 'post',
				async: false,
				data: {
					action : WooUploads.admin.actions.cancel,
					woo_nonce : WooUploads.admin.nonce.cancel,
					product_id : WooUploads.admin.product_id,
				},
			});
    	}
	};

	var isAdvancedUpload = function(){
		var div = document.createElement('div');
		return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
	}();
	var _template = function( str ){
		this.vars = {};
		this.original = str || '';
		this.template = str || '';
		return this;
	};
	_template.prototype = {
		replace : function( obj ){
			var obj = $.extend( {}, obj );
			this.vars = $.extend( this.vars, obj );
			for( var key in obj ){
				this.replaceString( '{{' + key + '}}', obj[ key ] );
			}
		},
		replaceString : function( key, value ){
			this.template = this.template.replace(
				new RegExp( key, 'g' ),
				value
			);
		},
		toString : function(){
			return this.template;
		},
		toHTML : function(){
			var $html = $( this.template );
			$html.data('_tpl',this);
			return $html;
		}
	};
	var template = function( str ){
		return new _template( str );
	};

	var $form = $('.cart'),
		$input = $('#woo-file-input'),
		files = false;

	if( isAdvancedUpload ){

		var canceledRequests = [],
			uploadCounter = $('.upload-file').length || 0;

		$('.single_add_to_cart_button').hide();

		$form
			.on('submit', function(e){
				if( ! hasUploads ){
					e.preventDefault();
					alert( WooUploads.i18n.please_upload_files );
				} else if( updatingQuantity > 0 ){
					e.preventDefault();
					alert( WooUploads.i18n.please_wait_until_updated );
				} else {
					submited = true;
				}
			})
			.on('click','.single_add_to_cart_button', function(e){
				if( ! hasUploads && ! $(this).hasClass('disabled') ){
					e.preventDefault();
					alert( WooUploads.i18n.please_upload_files );
				}
			});

		// form actions
		$form
			.on('change','#woo-file-input',function(e){
				files = e.target.files;
				$(this).trigger('woo_uploads.init');
			})
			.on('woo_uploads.init','#woo-file-input',function(e){
				if( $form.hasClass('is-uploading') ) return false;
				$form.addClass('is-uploading');
				$('.single_add_to_cart_button').hide();
				$('#upload-response').text('');
				if( files ){
					var _process_files = [];
					$.each( files, function( i, file ){
						var ext = file.name.split(".").pop().toLowerCase();
						var validator = validators[ ext ] || false;
						if( validators.file_extension( file ) ){
							var err = 0;
							if( 'function' === typeof validator ){
								if( ! validator( file ) ){
									$('#upload-response').append(
										'<p class="alert alert-danger">' + WooUploads.i18n.invalid_file_type.replace( '{{filename}}', file.name ) + '</p>'
									);
									err++;
								}
							}
							if( file.size > WooUploads.admin.max_filesize ){
								$('#upload-response').append(
									'<p class="alert alert-danger">' + WooUploads.i18n.filesize_to_big.replace( '{{filename}}', file.name ) + '</p>'
								);
								err++;
							}
							if( ! err ){
								_process_files.push( file );
							}
						} else {
							$('#upload-response').append(
								'<p class="alert alert-danger">' + WooUploads.i18n.invalid_file_type.replace( '{{filename}}', file.name ) + '</p>'
							);
						}
					});
					if( _process_files.length > 0 ){
						isUploading = true;
						$('.upload-post-hide').hide();
						$.each( _process_files, function( i, _file ){
							var _tpl = template( $('#woo-upload-template').html() );
							_tpl.replace({
								'i' : uploadCounter + i,
								'filename' : _file.name
							});
							$('#upload-processing').append( _tpl.toHTML() );
						});
						var requestCount = 0;
						var makeRequest = function(){
							var currentRequest = requestCount;
							// show upload
							if( currentRequest >= _process_files.length ){
								isUploading = false;
								$input.val('');
								$form.removeClass('is-uploading');
								$('.upload-post-hide').show();
								$('.upload-post-show').hide();
								$('.single_add_to_cart_button').show();
								return;
							}
							if( _process_files.length === 0 ){
								canceledRequests = [];
								$('.upload-post-show').fadeIn();
								return;
							}
							if( -1 !== canceledRequests.indexOf( currentRequest ) ){
								requestCount++;
								uploadCounter++;
								makeRequest();
								return;
							}
							var currentFile = _process_files[ currentRequest ];
							var ajaxData = new FormData;
							ajaxData.append( 'action', WooUploads.admin.actions.upload );
							ajaxData.append( 'woo_nonce', WooUploads.admin.nonce.upload );
							ajaxData.append( 'product_id', WooUploads.admin.product_id );
							ajaxData.append( 'woo-upload-file', currentFile );
							$.ajax({
								url : WooUploads.admin.ajax_url,
								type : 'post',
								data : ajaxData,
								dataType : 'json',
								cache : false,
								contentType : false,
								processData : false,
								async : true,
								xhr: function(){
									var xhr = new window.XMLHttpRequest();
									xhr.upload.addEventListener( "progress", function( e ){
										if( e.lengthComputable ){
											var percentComplete = e.loaded / e.total;
											var $upload = $('#upload-file-' + uploadCounter );
											percentComplete = parseInt( percentComplete * 100 );
											$upload.find('.progress-bar').width( percentComplete + '%' );
											if( percentComplete == 100 ){
												$upload.find('.upload-status').text( WooUploads.i18n.processing );
											}
										}
									}, false);
									$('#upload-file-' + uploadCounter + ' .upload-delete' ).data('xhr',xhr);
									return xhr;
								},
								complete : function( response ){
									requestCount++;
									uploadCounter++;
									makeRequest();
								},
								success : function( response ){
									var $upload = $('#upload-file-' + uploadCounter );
									$upload.removeClass('in-progress');
									if( response && response.success ){
										var _tpl = $('#upload-file-' + uploadCounter).data('_tpl');
										_tpl.replace({
											'ID' : response.data.file_id,
										});
										var $markup = _tpl.toHTML();
										var img = new Image();
										img.onload = (function(){
											$('#upload-file-' + this)
												.removeClass('is-preview-loading')
												.find('.thumb img')
												.attr('src',response.data.thumb);
										}).bind( uploadCounter );
										img.src = response.data.thumb;
										$markup.removeClass('in-progress');
										$markup.find('.upload-post-pending').hide();
										$markup.find('.upload-post-processed').removeClass('upload-post-processed');
										$upload.replaceWith( $markup );
										hasUploads++;
									} else if( ! response.success && response.data ){
										$upload.find('.upload-status').text( WooUploads.i18n.error + ': ' + response.data );
										$upload.find('.progress-bar').addClass('progress-bar-danger');
										$upload.find('.upload-post-processed').remove();
									} else if( ! response ){
										alert( WooUploads.i18n.please_refresh_page );
									}
								},
								error : function( response ){
									var $upload = $('#upload-file-' + uploadCounter );
									$upload.find('.upload-status').text( WooUploads.i18n.error + ': ' + WooUploads.i18n.post_processing_failed );
									$upload.find('.progress-bar').addClass('progress-bar-danger');
								}
							});
						};
						makeRequest();
					} else {
						$input.val('');
						$form.removeClass('is-uploading');
						isUploading = false;
					}
				} else {
					$input.val('');
					$form.removeClass('is-uploading');
					isUploading = false;
				}
				return false;
			})
			.on('drag dragstart dragend dragover dragenter dragleave drop', function(e){
				e.preventDefault();
				e.stopPropagation();
			})
			.on('dragover dragenter', function(){
				$form.addClass('is-dragover');
			})
			.on('dragleave dragend drop', function(){
				$form.removeClass('is-dragover');
			})
			.on('drop', function(e){
				files = e.originalEvent.dataTransfer.files;
				$input.trigger('woo_uploads.init');
			});

		// upload delete
		$(document)
			.on('click','.upload-delete',function(e){
				e.preventDefault();
				if( $(this).data('delete-initiated') ) return;
				$(this).data('delete-initiated',true);
				if( confirm( WooUploads.i18n.confirm_img_delete ) ){
					var xhr = $(this).data('xhr' );
					if( xhr && typeof xhr.abort == 'function' ){
						xhr.abort();
					}
					canceledRequests.push( parseInt( $(this).data('i') ) );
					var $upload = $(this).closest('.upload-file');
					var id = $(this).data('id');
					if( ! $upload.hasClass('in-progress') ){
						var preData = {};
						preData.action = WooUploads.admin.actions.delete;
						preData.woo_nonce = WooUploads.admin.nonce.delete;
						preData.product_id = WooUploads.admin.product_id;
						preData.file_id = id;
						preData.tmp_upload = $(this).hasClass( 'tmp-upload' );
						$.post( WooUploads.admin.ajax_url, preData );
					}
					$upload.fadeOut(function(){
						$(this).remove();
						if( $('.upload-file').length == 0 ){
							$('.upload-post-hide').show();
							$('.upload-post-show').hide();
							$input.val('');
							$form.removeClass('is-uploading');
							$('#upload-response').text('');
							isUploading = false;
							canceledRequests = [];
						}
					});
				} else {
					$(this).data('delete-initiated',false);
				}
			})
			.on('keyup change','.woo-upload-quantity',function(e){
				var $self = $(this);
				if( parseInt( $self.val() ) < 1 ){
					$self.val( 1 );
				}
				if( ! $self.prev().find('.woo-quick-loader').length ){
					$self.prev().append( WooUploads.admin.loader );
				}
				clearTimeout( timeout );
				timeout = setTimeout( function(){
					$self.addClass('is-updating');
					updatingQuantity += 1;
					var preData = {};
					preData.action = WooUploads.admin.actions.update;
					preData.woo_nonce = WooUploads.admin.nonce.update;
					preData.product_id = WooUploads.admin.product_id;
					preData.file_id = $self.data('id');
					preData.woo_quantity = $self.val();
					preData.tmp_upload = $self.hasClass( 'tmp-upload' );
					$.post( WooUploads.admin.ajax_url, preData, function(d){
						$self.prev().find('.woo-quick-loader').remove();
						$self.removeClass('is-updating');
						$self.blur();
						updatingQuantity -= 1;
					} );
				}, 300 );
			});

	}

});
