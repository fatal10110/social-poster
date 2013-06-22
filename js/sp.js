var SP_core = {
    active: false,
	
    popup_wrap: {
        loader: jQuery('<img />').attr({'src': sp_url + 'img/load.gif', 'id': 'sp-load_ajax-image'}),
        wrapper: jQuery('<div />').attr('id', 'sp_popup_wrapper')
    },
	
	loadButton: {},
    
	alert: function (text, callback) {
		var button = {};
		button[sp_lang.ok] = function() { jQuery( this ).dialog( "close" ); if(typeof callback == 'function') callback(); };
		button[sp_lang.cancel] = function() { jQuery( this ).dialog( "close" ); };
					
		SP_core.popup_form_obj.dialog({
			title: sp_lang.alert,
			open: function() {
				SP_core.popup_open();
				SP_core.popup_form_obj.html("<img class='alert' src='" + sp_url + "img/alert.png' />" + text);
			},
			close: function() { SP_core.popup_close(); },
			buttons: button,
			modal: true
		});		
	}, 
	
	error: function (text) {
		var button = {};
		button[sp_lang.ok] = function() { jQuery( this ).dialog( "close" ); };
					
		SP_core.popup_form_obj.dialog({
			title: sp_lang.error,
			open: function() {
				SP_core.popup_open();
				SP_core.popup_form_obj.html("<img class='error' src='" + sp_url + "img/error.png' />" + text);
			},
			close: SP_core.popup_close,
			buttons: button,
			modal: true
		});		
	},

    popup_close: function(obj) {
            if(!SP_core.active) return false;
			
            SP_core.active = false;
			SP_core.popup_form_obj.empty().remove();
			SP_core.popup_form_obj = SP_core.popup_wrap.wrapper.append(SP_core.popup_wrap.loader);
    },
    
    popup_open: function() {
          if(SP_core.active) return false;
		  
          SP_core.active = true;
    },
	
    init: function() {
			
			SP_core.popup_form_obj = SP_core.popup_wrap.wrapper.append(SP_core.popup_wrap.loader);
			SP_core.loadButton[sp_lang.cancel] = function() { jQuery(this).dialog("close"); } 
            //Adding account - Selecting social network
            jQuery("#sp_socials").change(SP_core.display_fields_for_social);
            
            //On editing account
            jQuery(document).on('click','#sp_add_butt , .sp_edit', SP_core.edit_account);
            
            //On deleting account
            jQuery(document).on('click','.sp_del , #sp_del_butt', SP_core.delete_from_table);
            
            //On editing post
            jQuery(document).on('click','.sp_edit_post', SP_core.edit_post);
			
			//Changing post thumbnail
			jQuery(document).on('click','.sp_im_butt', SP_core.change_image);
            
            
    },
    
    edit_account: function() {
 			
        if(SP_core.active) return;
			
		var lnk = jQuery(this);
        var title;
        var popup_buttons = {};
        var data = { }
        var action;
		
		popup_buttons[sp_lang.cancel] = function() { jQuery(this).dialog("close"); }
			
		if(lnk.hasClass('sp_acc')) { //Social User Form
			data.action = 'sp_acc_form';
			action = 'sp_acc';
		} else if(lnk.hasClass('sp_mail_grp')) { //Mail Group Form
			data.action = 'sp_mail_grp_form';
			action = 'sp_mail_grp';
		} else if(lnk.hasClass('sp_mail_svc')) { //Mail Service Form
			data.action = 'sp_mail_svc_form';
			action = 'sp_mail_svc';
		}
			
		if(lnk.hasClass('sp_edit'))
		{ //Edit Form
			title = lnk.attr('title');
			data.aid = lnk.data('id');
			popup_buttons[sp_lang.edit] = function(params) { SP_core._form_send(action,params, data.aid);}
		} else { //New Form
			title = lnk.text();
			popup_buttons[sp_lang.add] = function(params) { SP_core._form_send(action, params);}
		}
            			
        SP_core.popup_form_obj.dialog({
			title: title,
			buttons: SP_core.loadButton,
			width: 500,
			minHeight: 500 ,
			height: 'auto',
			modal: true,
			open: function() {
                    SP_core.popup_open();
				
					jQuery.post(ajaxurl,data,function(response) { 
									SP_core.popup_form_obj.html(response);
									SP_core.popup_form_obj.dialog({ buttons: popup_buttons });
					});  
			},
			close: SP_core.popup_close
		});
		
		return false;
    },
    
    edit_post: function() {
        if(SP_core.active) return false;
		
		var edited_acc = jQuery(this);
		var acc_id = edited_acc.data('acc');
        var title = edited_acc.attr('title');
        var name = '';
        var pid = jQuery('#post_ID').val();
		
		jQuery(this).parents('.selectit').find('input[type="checkbox"]').attr('checked','checked');
                
        if(edited_acc.hasClass('sp_soc')) name = 'sp_soc';
        else name = 'sp_mail';
		
		var reg = /^.+\[\d+\]\[(.+)\]$/;
		var def = '0'; //Default values
		var post_data = jQuery('#post > input[name^="' + name + '[' + acc_id + ']"]');
		
		if(!post_data.length)
		{
			post_data = jQuery('#post .sp-post-popup-wrapper').find('input[name^="sp\["], textarea[name^="sp\["]');
			
			def = '1';
			reg = /^.+\[(.+)\]$/;
		}
		
		var data = {
                    "id": acc_id,
					"pid": pid,
					"action": 'sp_get_form',
                    "type": name,
					"def": def
	               };
				   
		var popup_buttons = {};
		popup_buttons[sp_lang.cancel] = function() { jQuery(this).dialog("close"); }
		
		if(!post_data.length) return false;
			
		data['form_data']  = {};
			
        post_data.each(function() {
					var obj = jQuery(this);
					var key = obj.attr('name').replace(reg,'$1');
					data['form_data'][key] = obj.val();
		});
			
		if(def == '0')
		{
			popup_buttons[sp_lang.save] = function() { SP_core.set_edition(name, acc_id); jQuery(this).dialog("close"); }
			popup_buttons[sp_lang.del] = function() { SP_core.remove_edition(name, acc_id); jQuery(this).dialog("close"); }	
		} else //New post edition 
			popup_buttons[sp_lang.save] = function() { SP_core.set_edition(name, acc_id); jQuery(this).dialog("close"); }
        
            
        SP_core.popup_form_obj.dialog({
            title: title,
            height: 'auto',
            width: 'auto',
            buttons: SP_core.loadButton,
            modal: true,
            open: function() {
                SP_core.popup_open();
                    
                jQuery.post(ajaxurl , data , function(response){ //Get the from from the server
                        SP_core.popup_form_obj.html(response);
                        SP_core.popup_form_obj.dialog({ position: { my: "center", at: "center", of: window }, buttons: popup_buttons });
                });  
            },
				
            close: SP_core.popup_close
		});
			
		return false;
    },
    
    display_fields_for_social: function() {
			
            var soc = jQuery(this).children('option:selected');
			
			if(soc.hasClass('sp_have_page')) jQuery('tr.sp_pages_box:hidden').css('display','table-row');
            
            else if(soc.hasClass('sp_have_boards')) {
            
                jQuery('#sp_pages_field:hidden').css('display','table-row');
                jQuery('#sp_post_on:visible').css('display','none');
            
            } else jQuery('tr.sp_pages_box:visible').hide();         
    },
	
	change_image: function() {
			var wrapper = jQuery(this).parents('.sp-post-popup-wrapper');	
			var images = wrapper.find('.sp-im-wrapper li');
			
			if(images.length <= 1) return false;

			var imageInput = wrapper.find('input[name="sp[image]"]');
			var current = wrapper.find('.sp-im-wrapper li:visible');
			var toShow;
			
			if(jQuery(this).hasClass('sp_im_next')) toShow = (current.index() == images.length - 1) ? images.first() : current.next('li:hidden');
			 else toShow = (current.index() == 0) ? images.last() : current.prev('li:hidden');
			
			var imageURL = toShow.css('background-image').replace(/^url\((['"]?)(.*)\1\)$/,"$2");
			
			current.hide(); 
			toShow.show();
			
			imageInput.val(imageURL);
		return false;
	},

    set_edition: function(name, acc_id)
    {//Set edited post data to socials 
        var form_array = jQuery('#frm').serializeArray();
        var post = jQuery('#post');
		
		for (input in form_array) {
            var key = form_array[input].name.replace(/^.+\[(.+)\]$/,'$1');
            var new_value = form_array[input].value;
            var exist = post.find('input[name="' + name + '[' + acc_id + '][' + key + ']"]');
            
            if(exist.length > 0) exist.val(new_value); 
            else jQuery('<input />').attr('type','hidden').attr('name',name + '[' + acc_id + '][' + key + ']').val(new_value).appendTo(post);
        }
    },
    	
    remove_edition: function (name,acc_id)
    {//Remove edited form
        jQuery('#post > input[name^="' + name + '[' + acc_id + ']"]').remove();
    },

    delete_from_table: function() 
    {		
        var lnk = jQuery(this);
        var id = jQuery(this).data('id');
        var name, action, title;
			
        if(lnk.hasClass('sp_acc')) { //On social account
            name = 'sp_accs';
            action = 'sp_acc_del';
        } else if(lnk.hasClass('sp_mail_grp')) { //On mail froups 
            name = 'sp_grps';
            action = 'sp_grp_del';
        } else if(lnk.hasClass('sp_mail_svc')) { //On mail service
            name = 'sp_srvc';
            action = 'sp_svc_del';
        } else if(lnk.hasClass('sp_logs')) { //On logs
            name = 'sp_logs';
            action = 'sp_log_del';
        }
			
        if(name != 'sp_logs') SP_core.alert(sp_lang.sure, function() { SP_core._del_list(action,id,name) });
        else SP_core._del_list(action,id,name);	
        
        return false;
	},
	
	_setInProcess: function(obj) { //Set teble row in process
		var tr = obj.parents('tr');
		
		var colspan = obj.parents('tr').find('th, td').length;
		tr.empty();
	
		jQuery('<td >').attr('colspan',colspan).css('text-align','center').html('<img src="' + sp_url + '/img/load.gif" />').appendTo(tr);

	},
	
	_del_list: function(action,id,name) 
	{ //Delete from WP table
		
		if(!id)
		{
			id = new Array();

			jQuery('input[name="' + name +'[]"]:checked').each(function() {
				SP_core._setInProcess(jQuery(this));
				id.push(jQuery(this).val());
			});
		} else SP_core._setInProcess(jQuery('.check-column input[value="' + id + '"]'));
		
		
		jQuery.post(
			ajaxurl,
			{
				'action': action,
				'id': id
			},	
			function(response){
				if(response) SP_core.error(response);			
				else SP_core._update_table();
			}
		); 	
	},
	
	_form_send: function(action, data, id)
	{//Save form data using AJAX
		jQuery('#sp_res_load').remove();
		
		var butt = jQuery(data.target).parent();
		var loader = jQuery('<div />').attr('id','sp_res_load').html('<img src="' + sp_url + '/img/load.gif" />');
		var send = jQuery("#sp_form").serialize() + '&action=' + action;
		
		butt.before(loader);

		if(typeof(id) !== 'undefined')
			send += '&aid=' + id;
			
		jQuery.post(
			ajaxurl,
			send,
			function(result)
			{
				result = result.split(':',2);

				if(result[0] === 'OK')
				{
					jQuery('#sp_error').hide();
					loader.css('color','green').text(result[1]);
					SP_core._update_table();
					
					
					setTimeout(function(){ 
						SP_core.popup_form_obj.fadeOut(600, function() {
							SP_core.popup_form_obj.dialog("close"); 
						});
					},600);
					
				} else {
					jQuery('#sp_error').show().text(result[1]);
					loader.remove();
				}
			}
		);
	},	
	
	_update_table: function ()
	{//Update WP Table
		jQuery.post(
			ajaxurl,
			{
				'action': 'sp_field_list',
				'url': window.location.href,
				'list_args': list_args,  
				'_ajax_fetch_list_nonce': jQuery('#_ajax_fetch_list_nonce').val()
			},
			function(response){
				var loc = window.location.href.replace(/&paged=\d+/im,'');
						
				jQuery('#the-list').html(response.rows);
				jQuery('.displaying-num').html(response.total_items_i18n);
				jQuery('span.displaying-num').html(response.total_items_i18n);
				jQuery('.tablenav.top').replaceWith(response.nav_top);
				jQuery('.tablenav.bottom').replaceWith(response.nav_bottom);
			},"json"
		);     
	}
}
	
function sp_soc_checked(data)
{
	jQuery('#sp_soc_selected').val(data.selectedData.value);
		
	switch(sp_soc_arr[data.selectedData.value])
	{
		case 1:
			jQuery('li.sp_pages_box:hidden').css('display','block');
			jQuery('#sp_post_on:hidden').css('display','block');
		break;
			
		case 2:
				jQuery('#sp_pages_field:hidden').css('display','block');
				jQuery('#sp_post_on:visible').css('display','none');		
		break;
			
		case 0:
			jQuery('li.sp_pages_box:visible').css('display','none');
			jQuery('#sp_post_on:visible').css('display','none');		
	} 
}		
	
jQuery(document).ready(function($) {
    $('#sp_del_fil_butt').click(function() {//Del filter
			location.href = $(this).parent().attr('href');
            
            return false;
    });
	
	SP_core.init();
});