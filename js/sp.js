//Active dfialog
var  active = false;

	function sp_get_arr()
	{//Post fom array
		var a = jQuery('#frm').serializeArray();
							
		var arr = {};

		for (i in a) {
			arr[a[i].name] = a[i].value;
		}
		
		return arr;
	}
	
	function sp_set_field(name)
	{//Set edited form
		var arr = sp_get_arr();
		var inp = '';
		
		jQuery.each(arr, function(key, value) {
		  		  
			if(key != 'pre')
			{
				inp = jQuery('<input type="hidden" name="' + name + '[' + arr['pre'] + '][' + key + ']"/>');
				inp.appendTo('#post').val(value);
			}
		});
	}
	
	function sp_update_field(name)
	{//Update edited form
		var arr = sp_get_arr()
		
		jQuery.each(arr, function(key, value) {
			if(key != 'pre')
				jQuery('#post > input[name="' + name + '[' + arr['pre'] + '][' + key + ']"]').val(value);
		});
	}
	
	function sp_remove_field(name)
	{//Remove edited form
		var id = jQuery('input[name=pre]').val();
		
		jQuery('#post > input[name^="' + name + '[' + id + ']"]').remove();
	}
	
	jQuery(document).ready(function() {
		jQuery('#sp_del_fil_butt').click(function(event)
		{//Del filter
			event.preventDefault();
			location.href = jQuery(this).parent().attr('href');
		});
		
		jQuery(document).on('click','a.sp_edit_post',function(event)
		{//Editing post
				event.preventDefault();
				
				if(active) return;
				
				var id = jQuery(this).attr('id') ,pid = jQuery('#post_ID').val(), name = '', title = jQuery(this).attr('title');
                
                if(jQuery(this).hasClass('sp_soc'))
                    name = 'sp_soc';
                else
                    name = 'sp_mail';
					
				var a = jQuery('#post > input[name^="' + name + '[' + id + ']"]');
				var data = {
					"id": id,
					"pid": pid,
					"action": 'sp_get_form',
                    "type": name					
				};
				
				function sp_close_dialog(dialog)
				{
					dialog.dialog( "close" );
					
					jQuery('#sp_load_form').remove();
					
					active = 0;
				}
				
				var butt = {
					close: function() { sp_close_dialog(jQuery( this )); }
				};
				
				if(a.length > 0)
				{//Edit
					data['json'] = a.serializeArray();
					butt['save'] = function() { sp_update_field(name); sp_close_dialog(jQuery( this )); }
					butt['delete'] = function() { sp_remove_field(name); sp_close_dialog(jQuery( this )); }	
				} else //New
					butt['save'] = function() { sp_set_field(name); sp_close_dialog(jQuery( this )); }

				jQuery( "<div id='sp_load_form'><img src='" + sp_url + "img/load.gif' /></div>" ).dialog({
				title: title,
				height: 'auto',
				width: 'auto',
				buttons: butt,
				//modal: true,
				open: function() {
						active = 1;
						
						jQuery.post(
							ajaxurl,
							data,							
							function(response){ //updating the domain field
								jQuery('#sp_load_form').html(response);
								jQuery('#sp_load_form').dialog( "option", "position", { my: "center", at: "center", of: window });

							}
						);  
					},
				close: function() {
					active = 0;
					jQuery('#sp_load_form').remove();
				}
			});	
		});	
        
        jQuery(document).on('click','a.sp_im_butt',function(event)
        {
            event.preventDefault();
			
			function sp_set_image(obj)
			{//Set edited post image
				var back = '';
				
				if(obj.hasClass('sp_im'))
				{
					back = obj.css('background-image');
					back = back.match(/src=(.+?)&w=/);
					back = decodeURIComponent(back[1]);
				}
				
				jQuery('#frm input[name="image"]').val(back);
			}
            
			var im = jQuery('#sp_im li:visible');
			var next = im.next('li:hidden');
			var prev = im.prev('li:hidden');
			
			if(next.length > 0 && jQuery(this).hasClass('sp_im_next'))
			{
				im.hide();
				next.show();
				sp_set_image(next);
			} else if(prev.length > 0 && jQuery(this).hasClass('sp_im_prev'))
			{
				im.hide();
				prev.show();
				sp_set_image(prev);
			}
        });
		
		jQuery("#sp_socials").change( function(test) {
			var soc = jQuery(this).children('option:selected');
			
			if(soc.hasClass('sp_have_page'))
				jQuery('tr.sp_pages_box:hidden').css('display','table-row');
            else if(soc.hasClass('sp_have_boards')) {
                jQuery('#sp_pages_field:hidden').css('display','table-row');
                jQuery('#sp_post_on:visible').css('display','none');
            } else
				jQuery('tr.sp_pages_box:visible').hide();       
		});

		jQuery(document).on('click','#sp_add_butt , .sp_edit',function(event) 
		{
			event.preventDefault();
			
			if(active) return;
			
			active = 1;
			
			var lnk = jQuery(this),title, butt = {}, data = { }, action;
			
			if(lnk.hasClass('sp_acc')) {
				data.action = 'sp_acc_form';
				action = 'sp_acc';
			} else if(lnk.hasClass('sp_mail_grp')) {
				data.action = 'sp_mail_grp_form';
				action = 'sp_mail_grp';
			} else if(lnk.hasClass('sp_mail_svc')) {
				data.action = 'sp_mail_svc_form';
				action = 'sp_mail_svc';
			}
			
			if(lnk.hasClass('sp_edit'))
			{
				title = lnk.attr('title');
				data.aid = lnk.attr('id').replace('sp_','');
				butt[sp_lang.edit] = function(params) { sp_form_send(action,params, data.aid);}
			} else {
				title = lnk.text();
				butt[sp_lang.add] = function(params) { sp_form_send(action,params);}
			}
				jQuery( "<div id='sp_form_box'><img src='" + sp_url + "img/load.gif' /></div>" ).dialog({
					title: title,
					buttons: butt,
					width: 500,
					//minHeight: 500 ,
					height: 'auto',
					modal: true,
					open: function() {
							jQuery.post(
								ajaxurl,
								data,
								
								function(response) { //updating the domain field
									jQuery('#sp_form_box').html(response);
									jQuery('#sp_form_box').dialog( "option", "position", { my: "center", at: "center", of: window });
								}
							);  
						},
					close: function() {
						active = 0;
						jQuery('#sp_form_box').remove();
					}
				});
		});
		
		
		jQuery(document).on('click','.sp_del , #sp_del_butt',function(event) {
			event.preventDefault();
			
			var lnk = jQuery(this), id = sp_get_id(jQuery(this)), name, action, title;
			
			if(lnk.hasClass('sp_acc')) {
				name = 'sp_accs';
				action = 'sp_acc_del';
			} else if(lnk.hasClass('sp_mail_grp')) {
				name = 'sp_grps';
				action = 'sp_grp_del';
			} else if(lnk.hasClass('sp_mail_svc')) {
				name = 'sp_srvc';
				action = 'sp_svc_del';
			} else if(lnk.hasClass('sp_logs')) {
				name = 'sp_logs';
				action = 'sp_log_del';
			}
			
			if(name != 'sp_logs')
			{
				if(active) return;
				
				active = 1;
				
				var title = sp_lang.alert, butt = {}, data = {};
				
				butt[sp_lang.del]  = function() { 
					jQuery( this ).dialog( "close" ); 
					active = 0; 

					sp_del_list(action,id,name);
				}
				
				butt[sp_lang.cancel]  = function() { jQuery( this ).dialog( "close" ); active = 0;}

					jQuery( "<div><img style='float: left;' src='" + sp_url + "img/alert.png' />" + sp_lang.sure + "</div>" ).dialog({
						title: title,
						buttons: butt,
						modal: true,
						
						close: function() {
							active = 0;
						}
					});
			} else 
				sp_del_list(action,id,name);
		});			
	});

function sp_del_list(action,id,name) 
{

	if(!id)
	{
		id = new Array();

		jQuery('input[name="' + name +'[]"]:checked').each(function() {
			id.push(jQuery(this).val());
		});
	}
	
    jQuery.post(
        ajaxurl,
        {
            'action': action,
            'id': id
        },
                    
        function(response){
            if(response === '')
			{
				sp_update_table();
			} else {
				var butt = {};
				butt[sp_lang.ok] = function() { jQuery( this ).dialog( "close" );};
			
				jQuery( "<div><img style='margin-right: 5px; float: left;' src='" + sp_url + "img/error.png' />" + response + "</div>" ).dialog({
					title: sp_lang.error,
					buttons: butt,
					modal: true
				});				
			}
        }
    ); 	
}

function sp_form_send(action, data, id)
{
	jQuery('#sp_res_load').remove();
	
	var butt = jQuery(data.target).parent();
	butt.before('<div id="sp_res_load" style="float:left; padding-right: 10px; padding-top: 10px;"><img height="24" width="24"	src="' + sp_url + '/img/load.gif" /></div>');

	var send = jQuery("#sp_form").serialize() + '&action=' + action;
	
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
				jQuery('#sp_res_load').css('color','green').text(result[1]);
				sp_update_table();
				jQuery('#sp_form_box').delay(600).queue(function(){ jQuery(this).dialog("close").dequeue(); });
				
			}
			else
			{
				jQuery('#sp_error').show().text(result[1]);
				jQuery('#sp_res_load').remove();
			}
		}
	);
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

function sp_update_table()
{
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

function sp_get_id(obj)
{
	var id = obj.attr('id'), re = /sp_\d+/;

	if(re.test(id)) return parseInt(id.replace('sp_',''));

	return false;
}
