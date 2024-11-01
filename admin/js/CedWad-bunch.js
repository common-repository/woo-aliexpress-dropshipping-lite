(function( $ ) {
	'use strict';

	$(document).on('click', '.CedWad_bunch', function(){
		$( this ).next( '.CedWad_loader_filter_table' ).show();
		$(this).hide();
		var filterId = $(this).attr('data-filterId');
		$.ajax({
			url : CedWad_action_handler.ajax_url,
			type : 'post',
			data : {
				action : 'CedWad_createBunch',
				_nonce : 'CedWad_createBunch',
				filetrId : filterId
			},
			success : function( response ) 
			{
				$('#CedWad_bunch_'+filterId).show();
				$( '.CedWad_loader_filter_table' ).hide();
				$( '<span class="filter_success_msg_content">Bunch Created Successfully!</span>' ).insertAfter( $( '#CedWad_bunch_'+filterId ) );
				setTimeout( function(){ $('.filter_success_msg_content').hide(); }, 3000 );
				
			},
			fail : function( response )
			{
				$('#CedWad_bunch_'+filterId).show();
				$( '.CedWad_loader_filter_table' ).hide();
				$( '<span class="filter_error_msg_content">Unable to Process Request!</span>' ).insertAfter( $( '#CedWad_bunch_'+filterId ) );
				setTimeout( function(){ $('.filter_error_msg_content').hide(); }, 3000 );
			}
		});
	});

	$( document ).on( 'click', '.CedWad_add_to_bunch_button', function(){
		$(document).find( '.CedWad_loader' ).show();
		var bunchName = $( '#CedWadBunchName' ).val();
		var filterId = $( '#CedWad_filterId' ).val();
		var bunchId = $( '#CedWad_bunchId' ).val();
		var productId = $( this ).attr( 'data-productId' );
		var productData = $( '#CedWad_product_data_'+productId ).val();
		$.ajax({
			url  : CedWad_action_handler.ajax_url,
			type : 'post',
			data : {
				action       : 'CedWad_addProductToBunch',
				_nonce		 : 'CedWad_addProductToBunch',
				bunchName    : bunchName,
				productId    : productId,
				productData  : productData,
				filterId     : filterId,
				bunchId      : bunchId
			},
			success : function( response )
			{
				console.log("Whoa! Data is pushed :)");
				$( '#CedWad_bunchId' ).val(response);
				$( '#CedWad_add_to_bunch_'+productId ).val( 'Remove from Bunch' );
				$('#CedWad_add_to_bunch_'+productId).addClass( 'CedWad_remove_from_bunch_button' );
				$('#CedWad_add_to_bunch_'+productId).parents('div').addClass( 'CedWad_remove_from_bunch_button_parent_wrapper' );
				$('#CedWad_add_to_bunch_'+productId).parents('div').removeClass( 'CedWad_add_to_bunch_button_parent_wrapper' );
				$('#CedWad_add_to_bunch_'+productId).removeClass( 'CedWad_add_to_bunch_button' );
				$('#CedWad_product_wrapper_border_'+productId).addClass('CedWad_already_added_product_wrapper_border');
				$( document ).find( '.CedWad_loader' ).hide();
			},
			fail : function( response )
			{
				$( document ).find( '.CedWad_loader' ).hide();
			}
		});
	} );

	$( document ).on( 'click', '.CedWad_remove_from_bunch_button', function(){
		$(document).find( '.CedWad_loader' ).show();
		var filterId = $( '#CedWad_filterId' ).val();
		var bunchId = $( '#CedWad_bunchId' ).val();
		var productId = $( this ).attr( 'data-productId' );

		$.ajax({
			url  : CedWad_action_handler.ajax_url,
			type : 'post',
			data : {
				action       : 'CedWad_removeProductFromBunch',
				_nonce		 : 'CedWad_removeProductFromBunch',
				productId    : productId,
				filterId     : filterId,
				bunchId      : bunchId
			},
			success : function( response )
			{
				console.log("Whoa! Data is pushed :)");
				$('#CedWad_add_to_bunch_'+productId).attr( 'value', 'Add to Bunch' );
				$('#CedWad_add_to_bunch_'+productId).addClass( 'CedWad_add_to_bunch_button' );
				$('#CedWad_add_to_bunch_'+productId).parents('div').addClass( 'CedWad_add_to_bunch_button_parent_wrapper' );
				$('#CedWad_add_to_bunch_'+productId).parents('div').removeClass( 'CedWad_remove_from_bunch_button_parent_wrapper' );
				$('#CedWad_add_to_bunch_'+productId).removeClass( 'CedWad_remove_from_bunch_button' );
				$('#CedWad_product_wrapper_border_'+productId).removeClass('CedWad_already_added_product_wrapper_border');
				$( document ).find( '.CedWad_loader' ).hide();
			},
			fail : function( response )
			{
				$( document ).find( '.CedWad_loader' ).hide();
			}
		});

	} );
})( jQuery );