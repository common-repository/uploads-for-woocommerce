jQuery(function($){

	$('.woouploads-create-zip').on('click',function(e){
		e.preventDefault();
		if( ! $('.woouploads-modal').length ){
			$('body').append( `
				<div class="woouploads-modal">
					<div class="woouploads-modal-body"></div>
					<div class="woouploads-modal-close dashicons dashicons-no"></div>
				</div>
			` );
		}
		$('body').addClass('woo-modal');
		var xhr = new XMLHttpRequest();
		xhr.open( 'GET', $(this).data('href') + '&t=' + ( new Date() ).getTime() );
		xhr.seenBytes = 0;
		xhr.onreadystatechange = function(){
			if( 3 == xhr.readyState || 4 == xhr.readyState ){
				var newData = xhr.response.substr( xhr.seenBytes );
				$('.woouploads-modal-body').append( newData ).scrollTop( $('.woouploads-modal-body').prop('scrollHeight') );
				xhr.seenBytes = xhr.responseText.length;
			}
		};
		xhr.addEventListener("error",function(e){
			$('.woouploads-modal-body').append( e ).scrollTop( $('.woouploads-modal-body').prop('scrollHeight') );
		});
		xhr.send();
	});

	$('body').on('click','.woouploads-modal-close',function(e){
		$('body').removeClass('woo-modal');
		$('.woouploads-modal-body').html('');
		window.location.reload();
	});

	$('.woouploads-delete').on('click',function(e){
		if( ! confirm( $(this).data('confirm') ) ){
			e.preventDefault();
		}
	});

});
