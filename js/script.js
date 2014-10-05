jQuery(document).ready(function($){
    var existing = "";
	var custom_uploader;
	
	var switches = document.querySelectorAll('input[type="checkbox"]');
	for (var i=0, sw; sw = switches[i++]; ) {
		var div = document.createElement('div');
		div.className = 'switch';
		sw.parentNode.insertBefore(div, sw.nextSibling);
	}	
	
    $('img').each(function(){
        if(existing == $(this).attr('src')){
			$(this).addClass('addBorder');
		}
    });
	
	$('table#bun_settings tr td').on('click','img.error_img',function(){
        $('img').removeClass('addBorder');
        $(this).addClass('addBorder');
        $('#upload_image').val($(this).attr('data-src'));		
	})
    
	
	$('#upload_image_button').click(function(e) {
		e.preventDefault();
		if (custom_uploader) {  custom_uploader.open(); return; }
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {  text: 'Choose Image'  },
			multiple: false
		});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			var thubmnail = attachment['sizes']['thumbnail'].url;
			
			$('#upload_image').val(attachment.url);
			$('img').removeClass('addBorder');
			$('#upload_image_button').before('<img data-src="'+attachment.url+'" src="'+thubmnail+'" class="error_img addBorder" width="50px" /> ');
		});
		custom_uploader.open();
	});
});