// JavaScript Document
var checkboxdata = new Array();
jQuery(document).ready(function(){
		
		var data = jQuery(".category_settings").html();
			jQuery('.export_category_settings').colorbox({html:data});
			
		
		jQuery(".export_btn").click(function(){
												 
				$confirm = confirm('Do you want to do an export?');
				if($confirm)
				{
				   jQuery(".cat_export_btn").attr("disabled","disabled");
				   jQuery(".all_export_btn").attr("disabled","disabled");
				   jQuery(".button").attr("disabled","disabled");
				   
				   if( jQuery(this).hasClass( 'cat_export_btn' ) )
				   		jQuery(".cat_product_exporting_loader").css("display","block");
					else if( jQuery(this).hasClass( 'all_export_btn' ) )
				   		jQuery(".all_product_exporting_loader").css("display","block")
				   
				   
				}
				else
					return false;
		});
		
		
		jQuery(".trash").click(function(){
				$confirm = confirm('Do you want to delete this export permanently?');
				if($confirm)
					return true
				else
					return false;
	   });
		
		
		jQuery(".export_category_settings").click(function(){
			window.checkboxdata.splice(0,window.checkboxdata.length);														
			jQuery("input[name='checked_category[]']:checkbox:checked").each( function () {
    			window.checkboxdata.push(jQuery(this).val());
   			})									  
		});
		
});

function save_product(){
			products = new Array();
			products = unique( window.checkboxdata );
			//alert(products);
			jQuery.ajax({
				type:'post',
				url: admin_url,
				data: { action: 'save_product', products:products},
				beforeSend: function(){ jQuery(".save_loader").css('visibility','visible');},
				complete: function(){ jQuery(".save_loader").css('visibility','hidden');
				},
				success:function(data){
					//alert(data);
				},
				error:function(){
						alert('fail');
					}
			});
			return false;
}

function save_category(){
			category = new Array();
			category = unique( window.checkboxdata );		
			//alert(category)
			jQuery.ajax({
				type:'post',
				url: admin_url,
				data: { action: 'save_category', categories:category},
				beforeSend: function(){ jQuery(".save_loader").css('visibility','visible');},
				complete: function(){ jQuery(".save_loader").css('visibility','hidden');},
				error:function(){ alert('fail');}
			});
			return false;
}

function unique(list) {
  var result = [];
  jQuery.each(list, function(i, e) {
    if (jQuery.inArray(e, result) == -1) result.push(e);
  });
  return result;
}

function changechk(clickdiv){
	if( jQuery('.checkimg', clickdiv).attr('src') == pluginpath+'img/checked.png')
	{
		jQuery('.checkimg', clickdiv).attr('src',pluginpath+'img/unchecked.png' );
		pos = jQuery.inArray( jQuery('.checkeddone ', clickdiv).val(), window.checkboxdata );
		jQuery('.checkbox', clickdiv).removeClass('checkeddone');
		if( pos >=0 )  window.checkboxdata.splice(pos,1);
	}
	else
	{
		jQuery('.checkimg', clickdiv).attr('src',pluginpath+'img/checked.png' );
		jQuery('.checkbox', clickdiv).addClass('checkeddone');
		window.checkboxdata.push( jQuery('.checkeddone ', clickdiv).val() );
	}
}