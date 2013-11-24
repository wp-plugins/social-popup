jQuery(document).ready(function($){

	$('.reset_html').click(function(e){
			e.preventDefault(); 
			$.post(ajaxurl,
				   { action: 'spu_reset', what:'html'}, 
				   function(response){
    					editor_template.setValue(response);
    			   }
					) 	
			
	});					
	$('.reset_css').click(function(e){ 
	e.preventDefault();
			$.post(ajaxurl,
				   { action: 'spu_reset', what:'css'}, 
				   function(response){
    					editor_css.setValue(response);
    			   }
					) 	
					
			return false;
	});					

/*	$('.reset_css').click(function(){ $('#css_area').text('<?php echo $defaults['css'];?>'); return false; });*/
});
function clearCookie(name, domain, path){
    var domain = domain || document.domain;
    var path = path || "/";
    document.cookie = name + "=; expires=" + +new Date + ";  path=/";
    alert('Cookies deleted!');
    return false;
};