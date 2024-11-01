(function( $ ) {
	'use strict';

	jQuery(document).on('click', "#CedWad_save_license", function(){
		$( document ).find( '.CedWad_loader' ).show();
		jQuery(".licennse_notification").hide();
		var license_key = $('#CedWad_license_key').val();
		$.ajax({
			url:CedWad_action_handler.ajax_url,
			type:'post',
			data : {
				action :'CedWadGetlicense',
				_nonce : 'CedWadGetlicense',
				license_key : license_key
			},
			success:function(response)
			{	
				if(response == 'invalid_license_key')
				{	
					alert('invalid license key');
				}
				else
				{	
					window.location = window.location.href;
				}
			}
		});
	});
})( jQuery );