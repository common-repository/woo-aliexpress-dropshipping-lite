

(function( $ ) {
	'use strict';
	  $('.carousel .item').each(function(){
  var next = $(this).next();
  if (!next.length) {
    next = $(this).siblings(':first');
  }
  next.children(':first-child').clone().appendTo($(this));
  
  for (var i=0;i<4;i++) {
    next=next.next();
    if (!next.length) {
      next = $(this).siblings(':first');
    }
    
    next.children(':first-child').clone().appendTo($(this));
  }
});

	$(document).on('click', '.CedWad_blast', function(){
		$( this ).next( '.CedWad_loader_blast_table' ).show();
		$(this).hide();
		var bunchId = $(this).attr('data-bunchId');
		var blast = $(this).attr('data-blast');
		var product_id = $(this).attr( 'data-productId' );
		var action ="";
		if( blast == 'product' )
		{
			var action = "CedWad_blastProduct";
			var nonce = "CedWad_blastProduct";
		}
		else if( blast == 'bunch' )
		{
			var action = 'CedWad_blastBunch';
			var nonce = 'CedWad_blastBunch';
		}
		$.ajax({
			url : CedWad_action_handler.ajax_url,
			type : 'post',
			data : {
				action : action,
				_nonce : nonce,
				bunchId : bunchId,
				product_id : product_id
			},
			success : function( response ) 
			{

				$( '.CedWad_loader_blast_table' ).hide();
				if( $('#CedWad_blast_'+bunchId).length > 0 ){
					$('#CedWad_blast_'+bunchId).show();
					$( '<span class="bunch_success_msg_content">Product Created Successfully!</span>' ).insertAfter( $( '#CedWad_blast_'+bunchId ) );
				}
				else{
					$('#CedWad_blast_'+product_id).show();
					$( '<span class="bunch_success_msg_content">Product Created Successfully!</span>' ).insertAfter( $( '#CedWad_blast_'+product_id ) );
				}
				setTimeout( function(){ $('.bunch_success_msg_content').hide(); }, 3000 );
				
			},
			error : function( response )
			{
				$( '.CedWad_loader_blast_table' ).hide();
				if( $('#CedWad_blast_'+bunchId).length ){
					$('#CedWad_blast_'+bunchId).show();
					$( '<span class="bunch_error_msg_content">Unable to Process Request!</span>' ).insertAfter( $( '#CedWad_bunch_'+bunchId ) );
				}
				else{
					$('#CedWad_blast_'+product_id).show();
					$( '<span class="bunch_error_msg_content">Unable to Process Request!</span>' ).insertAfter( $( '#CedWad_bunch_'+product_id ) );
				}
				setTimeout( function(){ $('.bunch_error_msg_content').hide(); }, 3000 );
			}
		});
	});

	$( document ).on( 'click', '.CedWad_featured_image_icon', function(){
		if( $( this ).attr( 'data-selectedimage' ) == 'no' )
		{
			$( document ).find('.CedWad_featured_image_icon').each( function(){
				$(this).find('polygon').attr( 'fill', '#ffffff' );
				$( this ).attr( 'data-selectedimage', 'no' );
			} );
			var url = $(this).attr( 'data-mainImage' );
			$( '#CedWad_selected_as_main_image' ).val( url );
			$( this ).attr( 'data-selectedimage', 'yes' );
			$( this ).find( 'polygon' ).attr( 'fill', '#000000' );
		}
		else
		{
			var url = $(this).attr( 'data-mainImage' );
			$( '#CedWad_selected_as_main_image' ).val( "" );
			$( this ).attr( 'data-selectedimage', 'no' );	
			$( this ).find( 'polygon' ).attr( 'fill', '#ffffff' );
		}
	} );

	$( document ).on( 'change', '.CedWad_settings_to_use', function(){
		var selected_value = $( this ).val();
		if( selected_value == 'local' )
		{
			$( '.CedWad_edit_product_hidden_row' ).show();
		}
		else
		{
			$( '.CedWad_edit_product_hidden_row' ).hide();
		}
	} );

})( jQuery );