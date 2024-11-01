//$(document).ready(function(){
jQuery(document).ready(function($) {

	$(".ua_ppf").live("click", function(){
		var i_ppf_id = $(this).attr("i_ppf_id");
		//var s_frm_ppf_params = $('#frm_update_ppf_' + i_ppf_id).serialize();
		var s_frm_ppf_params = [{
								'i_ppf_id':i_ppf_id,
								's_ppf_name':encodeURI($('#txt_ppf_name_' + i_ppf_id).val()),
								's_ppf_imgurl':encodeURI($('#txt_ppf_imgurl_' + i_ppf_id).val()),
								's_spec_ppf_nonce':$('#txt_spec_ppf_nonce_' + i_ppf_id).val()
							}];
		var o_data = {
				action : 'a_spec_ppf_update',
				s_frm_ppf_params : s_frm_ppf_params
			};
		
		jQuery.post(o_ajax.ajaxurl, o_data, function(s_response){
			if(s_response != '')
			{
				if(s_response != 0)
				{
					jQuery('#tr_update_ppf_' + i_ppf_id).hide('fast');
					jQuery('#tr_update_ppf_' + i_ppf_id).html(s_response);
					jQuery('#tr_update_ppf_' + i_ppf_id).show('fast');
				}
			}
			else
			{
				// no update happened
			}
		});
		return false;
	});

	$(".da_ppf").live("click", function(){
		var i_ppf_id = $(this).attr("i_ppf_id");
		var s_frm_ppf_params = [{
								'i_ppf_id':i_ppf_id,
								's_spec_ppf_nonce':$('#txt_spec_ppf_nonce_' + i_ppf_id).val()
							}];
		var o_data = {
				action : 'a_spec_ppf_delete',
				s_frm_ppf_params : s_frm_ppf_params
			};
		
		jQuery.post(o_ajax.ajaxurl, o_data, function(s_response){

			if(s_response != '')
			{
				if(s_response == 1)
				{
					jQuery('#tr_update_ppf_' + i_ppf_id).hide("fast", function () {
						jQuery('#tr_update_ppf_' + i_ppf_id).remove();
					});
				}
			}
			else
			{
				// no delete happened
			}
		});
		
		return false;
	});
});