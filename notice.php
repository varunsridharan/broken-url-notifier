<?php
/*  Copyright 2014  Varun Sridharan  (email : varunsridharan23@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Plugin Name: Broken Url Notifier
Plugin URI: http://varunsridharan.in/
Description: Sends email to site admin when there is  broken url or image in your website
Version: 0.1
Author: Varun Sridharan
Author URI: http://varunsridharan.in/
License: GPL2
*/

defined('ABSPATH') or die("No script kiddies please!");
$bun_plug_url = plugins_url().'/'.basename(__DIR__).'/';
$bun_plug_path = plugin_dir_path( __FILE__ );


if ( is_admin() ){
	add_action('admin_menu', 'Broken_url_notifier_menu');
	function Broken_url_notifier_menu() {
		global $bun_plug_url;
		add_options_page('Broken Url Notifier', 'broken Url Notifier', 'administrator','broken_url_notifier', 'broken_url_notifier');
		add_option("broken_url_notifier_img", $bun_plug_url.'img1.jpg', '', 'yes');
		add_option("broken_url_notifier_email", '', '', 'yes');
		add_option("broken_url_notifier_email_subject", '', '', 'yes');
	}

}



function broken_url_notifier(){
	global $bun_plug_url;
	wp_enqueue_media();

	if(isset($_POST['broken_link_checker_update'])) {
		update_option('broken_url_notifier_img',$_POST['ad_image']);
		update_option('broken_url_notifier_email',$_POST['notic_email']);
		update_option('broken_url_notifier_email_subject',$_POST['notic_email_subject']);

	}
	?>


<div class="wrap">
	<?php
	if(isset($_POST['broken_link_checker_update'])){
        echo '<div id="message" class="updated">
		<p>Settings Updated.</p>
		</div>';
    }
     

    ?>
	<h2>Broken Url Notifier</h2>

	<style type="text/css">
img {
	margin-right: 10px;
	vertical-align: middle;
	cursor: pointer;
}

img.addBorder {
	border: 3px solid #595959;
}
</style>


	<form enctype="multipart/form-data" action="" id="mainform"
		method="post">
		<?php wp_nonce_field('update-options');  ?>


		<table id="general" class="tab active form-table">
			<tbody>

				<tr valign="top">
					<th class="titledesc" scope="row">Error Image</th>

					<td class="forminp forminp-select"><img
						src="&lt;?php echo $bun_plug_url; ?&gt;img1.jpg" width="50px"
						alt="" /> <img src="&lt;?php echo $bun_plug_url; ?&gt;img2.jpg"
						width="50px" alt="" /> <input id="upload_image_button"
						class="button" type="button" value="Upload or Choose Image"
						style="vertical-align: middle;" /> <input id="upload_image"
						type="hidden" size="36" name="ad_image" value="http://" /></td>
				</tr>
				<tr valign="top">
					<th class="titledesc" scope="row">Notification Email Subject</th>

					<td class="forminp forminp-select"><input
						id="notic_email_subject" type="text" size="36"
						name="notic_email_subject"
						value="&lt;?php echo @get_option('broken_url_notifier_email_subject'); ?&gt;" />
					</td>
				</tr>
				<tr valign="top">
					<th class="titledesc" scope="row">Notification Email</th>

					<td class="forminp forminp-select"><input id="notic_email"
						type="text" size="36" name="notic_email"
						value="&lt;?php echo @get_option('broken_url_notifier_email'); ?&gt;" />
					</td>
				</tr>
			</tbody>
		</table>



		<input type="submit" class="button button-primary"
			value="Update Settings" name="broken_link_checker_update" />
	</form>
</div>













<script type="text/javascript">
jQuery(document).ready(function($){
    
    
    var existing = "<?php echo get_option('broken_url_notifier_img'); ?>";
    $('img').each(function(){
        
        if(existing == $(this).attr('src')){$(this).addClass('addBorder');}
    
    }).click(function(){
        $('img').removeClass('addBorder');
        $(this).addClass('addBorder');
        $('#upload_image').val($(this).attr('src'));
    })
    
var custom_uploader;
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
        $('#upload_image').val(attachment.url);
        $('img').removeClass('addBorder');
    });
    custom_uploader.open();
});
});
</script>

<?php



}





function checkImage() {

	if(isset($_POST['imageError'])){
		$content = "Your website (".site_url().") is signaling a broken image! <br/><br/>
		<strong>Broken Image Url:</strong>  ".stripslashes($_POST['image'])." <br/>
		<strong>Referenced on Page:</strong> <a href='".stripslashes($_POST['page'])."'>
		".stripslashes($_POST['page']).
		"</a>";
		
		if(get_option('broken_url_notifier_email')) {
			$bun_email = get_option('admin_email');

        } else {
            $bun_email = get_option('broken_url_notifier_email');
        }
         
        if(get_option('broken_url_notifier_email_subject')) {
            $bun_email_subject = '404 Image Found';
        } else {
            $bun_email_subject = get_option('broken_url_notifier_email_subject');

        }
         
        if(! wp_mail($bun_email, $bun_email_subject, $content )) {
			echo 'false';
		}
	}

	if(is_404()) {
		$bun_content = "Your website (".site_url().") is signaling a broken link! <br/><br/>
		<strong>Referenced on Page:</strong> <a href='http://".$_SERVER['SERVER_ADDR']."'>
		".$_SERVER['REQUEST_URI']."</a> <br/>
		<strong>Refered Page :</strong>  ".$_SERVER["HTTP_REFERER"]." <br/> ";

		if(! wp_mail($bun_email, '404 Page Found', $content )) {
			echo 'false';
		}
	}

}
add_action('wp_head','checkImage',1);
?>

<?php

function add_this_script_footer(){ ?>

<script type="text/javascript">jQuery(document).ready(function(){
	jQuery('img').error(function() {
        var oldImg = jQuery(this).attr('src');
        jQuery(this).attr( "src", "<?php echo  get_option('broken_url_notifier_img') ; ?>");	
			jQuery.post(window.location.href, { 
				imageError:'yes',
				image: oldImg, 
				page: window.location.href 
			}, function() { 
			});
	});
});</script>

<?php } 

add_action('wp_footer', 'add_this_script_footer',200); ?>