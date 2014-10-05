 <div class="wrap"> 
	<h2>Broken Url Notifier</h2>
  
	<form enctype="multipart/form-data"  method="post">
	<?php  wp_nonce_field('update-options');  ?>
        
        
    <table id="bun_settings" class="tab active form-table">
        <tbody> 

            <tr valign="top">
                <th class="titledesc" scope="row">Error Image</th>

                <td class="forminp forminp-select">
                	<?php 
                	$imageArray = array();
                	$imageArray[] = bun_url.'img/img1.jpg';
                	$imageArray[] = bun_url.'img/img2.jpg';
                	$image = '';
                	$selectedImage = '';
                	if($this->settings('error_image',true)) {
	                	if(! in_array($this->settings('error_image',true),$imageArray)){
	                		$imageArray[] = $this->settings('error_image',true);
	                	}
	                	$selectedImage = $this->settings('error_image',true);
                	}
                	
                	foreach($imageArray as $img){
                		$image .= '<img data-src="'.$img.'" src="'.$img.'"  class="error_img';
                		if($this->settings('error_image',true) == $img) { $image .=  ' addBorder '; };
						$image .=  '" width="50px" />';
                	}
                	echo $image;
                	?>

                	<input id="upload_image_button" class="button" type="button" value="Upload or Choose Image"/>
                    <input id="upload_image" type="hidden" size="36" name="bun_settings[error_image]" value="<?php echo $selectedImage; ?>" />
                </td>
            </tr>

        </tbody>
        
    </table>
    <hr/>
    <h2>Broken Notification</h2>
    
    <table id="bun_settings" class="tab active form-table">
        <tbody>  
            <tr valign="top">
                <th class="titledesc" scope="row">Broken Image</th>
                <td class="forminp forminp-select">
				<label>
					<input name="bun_settings[broken_image_status_notify]" type="checkbox"
					<?php if($this->settings('broken_image_status_notify',true)) {echo 'checked'; }  ?>
					/> 
				</label>
                </td>
            </tr>
            <tr valign="top">
                <th class="titledesc" scope="row">Broken Page (404)</th>
                <td class="forminp forminp-select">
				<label>
					<input name="bun_settings[broken_page_status_notify]" type="checkbox"
					<?php if($this->settings('broken_page_status_notify',true)) {echo 'checked'; }  ?>
					/> 
				</label>
                </td>
            </tr>
                               
   
        </tbody>
        
    </table>
    
      <hr/>
    <h2>Loging</h2>
    
    <table id="bun_settings" class="tab active form-table">
        <tbody>  
            <tr valign="top">
                <th class="titledesc" scope="row">Log Broken Image</th>
                <td class="forminp forminp-select">
				<label>
					<input name="bun_settings[log_broken_image]" type="checkbox"
					<?php if($this->settings('log_broken_image',true)) {echo 'checked'; }  ?>
					/> 
				</label>
                </td>
            </tr> 
            <tr valign="top">
                <th class="titledesc" scope="row">Log Broken Url</th>
                <td class="forminp forminp-select">
				<label>
					<input name="bun_settings[log_broken_url]" type="checkbox"
					<?php if($this->settings('log_broken_url',true)) {echo 'checked'; }  ?>
					/> 
				</label>
                </td>
            </tr>           

        </tbody>
        
    </table> 
       
    
     <hr/>
    <h2>Email Settings</h2>
    
    <table id="bun_settings" class="tab active form-table">
        <tbody>  
            <tr valign="top">
                <th class="titledesc" scope="row">Email Id</th>

                <td class="forminp forminp-select">
                    <input id="notic_email" type="text" size="36" name="bun_settings[email_id]" value="<?php echo $this->settings('email_id'); ?>" />
                </td>
            </tr>   
        </tbody>
        
    </table>   

        
        
    <input type="hidden" name="action" value="bun_save_settings"/>             
    <input type="submit"  class="button button-primary" value="Update Settings" name="broken_link_checker_update" />
    </form>
</div> 