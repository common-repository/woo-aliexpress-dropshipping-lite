(function( $ ) {
	'use strict';

	$(document).on('click', '#CedWad_appKey', function(){
		$(this).removeClass('CedWad_input_error_class');
		$(".CedWad_errormsg").hide();
	});
	$(document).on('click', '#CedWad_transactionId', function(){
		$(this).removeClass('CedWad_input_error_class');
		$(".CedWad_errormsg_tracking").hide();
	});
	$(document).on('click', '#CedWad_PushDetails', function(){
		$( document ).find( '.CedWad_loader' ).show();
		var appKey = $("#CedWad_appKey").val();
		if(appKey == null || appKey == "" || typeof(appKey) == "undefined"){
			$("#CedWad_appKey").addClass('CedWad_input_error_class');
			$(".CedWad_errormsg").css('display','table');
			$( document ).find( '.CedWad_loader' ).hide();
			return false;
		}
		var transcationID = $("#CedWad_transactionId").val();
		if(transcationID == null || transcationID == "" || typeof(transcationID) == "undefined"){
			$("#CedWad_transactionId").addClass('CedWad_input_error_class');
			$(".CedWad_errormsg_tracking").css('display','table');
			$( document ).find( '.CedWad_loader' ).hide();
			return false;
		}
		var pushedDetails = {'appKey':appKey, 'transactionId' : transcationID};
		$.ajax({
			url : CedWad_action_handler.ajax_url,
			type : 'post',
			data : {
				action : 'CedWad_Pushingdeatils',
				_nonce : 'CedWad_Pushingdeatils',
				details : pushedDetails
			},
			success : function( response ) 
			{
				response = jQuery.parseJSON(response);
				if( response.status = "success" )
				{
					$(".CedWad_successmsg").css('display','table');
					setTimeout( function(){ $('.CedWad_successmsg').hide(); }, 3000 );
				}
				else
				{
					$('.error_msg_content').html('Unable to Process Data.');
					$(".CedWad_errormsg").css('display','table');
					setTimeout( function(){ $('.CedWad_errormsg').hide(); }, 3000 );
				}
				$( document ).find( '.CedWad_loader' ).hide();
				
			},
			error : function( response )
			{
				$('.error_msg_content').html('Unable to Process Data.');
				$(".CedWad_errormsg").css('display','table');
				setTimeout( function(){ $('.CedWad_errormsg').hide(); }, 3000 );
				$( document ).find( '.CedWad_loader' ).hide();
			}
		});
	})
	$(document).on('click', '.CedWad_push_filter_button', function(){
		$( this ).hide();
		$( document ).find( '.CedWad_loader_save_filter' ).show();
		var filterId = $(this).attr('data-filterId');
		var minPrice = $("input[name='CedWad_cost_range_min[]']").map(function(){return $(this).val();}).get();
		var maxPrice = $("input[name='CedWad_cost_range_max[]']").map(function(){return $(this).val();}).get();
		var markupPrice = $("input[name='CedWad_markup_amount[]']").map(function(){return $(this).val();}).get();
		var sign = $("select[name='CedWad_markup_sign[]']").map(function(){return $(this).val();}).get();
		var keyword = $("#CedWadAliKeyword").val();
		var cat = $("#CedWadAliCat").val();
		var catname = $("#CedWadAliCat option:selected").text();
		var filtername = $('#CedWadFilterName').val();
		var pushedDetails = {'todo':filterId, 'rules':{'name':filtername,'minPrice' : minPrice, 'maxPrice' : maxPrice, 'markupPrice' : markupPrice, 'signs' : sign, 'keyword' : keyword, 'cat' : cat,'catname' : catname}};

		$.ajax({
			url : CedWad_action_handler.ajax_url,
			type : 'post',
			data : {
				action : 'CedWad_PushingRules',
				_nonce : 'CedWad_PushingRules',
				details : pushedDetails
			},
			success : function( response ) 
			{
				response = jQuery.parseJSON(response);
				if( response.status = "success" )
				{
					$(".CedWad_filter_success_msg").css('display','block');
					setTimeout( function(){ $('.CedWad_filter_success_msg').hide(); }, 3000 );
				}
				else
				{
					$('.filter_error_msg_content').html('Unable to Process Data.');
					$(".CedWad_filter_error_msg").css('display','table');
					setTimeout( function(){ $('.CedWad_filter_error_msg').hide(); }, 3000 );
				}
				$( '.CedWad_push_filter_button' ).show();
				$( document ).find( '.CedWad_loader_save_filter' ).hide();
				
			},
			error : function( response )
			{
				$('.filter_error_msg_content').html('Unable to Process Data.');
				$(".CedWad_filter_error_msg").css('display','block');
				setTimeout( function(){ $('.CedWad_filter_error_msg').hide(); }, 3000 );
				$( '.CedWad_push_filter_button' ).show();
				$( document ).find( '.CedWad_loader_save_filter' ).hide();
			}
		});
	})

	jQuery( document ).on( 'click', '.CedWad_markupAdd', function(){
		var repeatable = jQuery(this).parents('tr').clone();
		jQuery( repeatable ).insertAfter( jQuery( this ).parents('tr') );
		// jQuery(this).parent( 'td' ).remove();
		jQuery( repeatable ).find( 'input[type=text]' ).val("");
	} );
	jQuery( document ).on( 'click', '.CedWad_markupDelete', function(){
  		var count = 0;
  		$('.CedWad-advanced-prices tbody tr').each(function(key, value){
  			console.log( count );
  			count = count + 1;
  		} );
  		if( count > 1 )
			jQuery(this).parents('tr').remove();
		else
			jQuery( '.CedWad-advanced-prices' ).find( 'input' ).val("");
        // jQuery('.CedWad-advanced-prices').find('tbody').find('tr:last').html( button_html );

	} );
	$(document).on('click','#email_registration',function(){
		var email=$('#CedWad_email').val();
		 var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        if(expr.test(email) != true)
        {
        	alert("please provide valid email");
        	return ;
        }

		$.ajax({
			url:CedWad_action_handler.ajax_url,
			type:'post',
			data : {
				action :'CedWadGetEmail',
				_nonce :'CedWadGetEmail',
				email : email
			},
			success:function(response)
			{	
				window.location = window.location.href;
			}
		});
	});
$(document).on('click','#CedWad_regenerate_license',function(){
$.ajax({
			url:CedWad_action_handler.ajax_url,
			type:'post',
			data : {
				action :'CedWad_regenerate_license',
				_nonce :'CedWad_regenerate_license'
			},
			success:function(response)
			{	
				window.location = window.location.href;
			}
		});
});

})( jQuery );
