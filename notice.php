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
    Version: 0.3
    Author: Varun Sridharan
    Author URI: http://varunsridharan.in/
    License: GPL2
*/

defined('ABSPATH') or die("No script kiddies please!");
define('bun_url',plugins_url('',__FILE__).'/');
define('bun_path',plugin_dir_path( __FILE__ ));

class Broken_Url_Notifier{
	public $bun_v;
	public $settings_array;
	public $broken_reports;
	public $notice_email;
	public $notice_email_subj;
	public $notice_email_content;
	
	/**
	 * @since 0.1
	 * @access public
	 */
	public function __construct() {
		$this->bun_v = '0.3';
		register_activation_hook( __FILE__, array($this ,'_activate') );
		$this->get_exData();
		if(empty($this->broken_reports)){
			$this->broken_reports = array();
		} 
		add_action('wp_footer', array($this,'add_footer_script'),200);
		$this->request_actions();
		if(is_admin()){
			add_action('admin_menu', array($this,'add_menu')); 
		}		
        
		if($this->settings('email_id',true)) {
			$this->notice_email = $this->settings('email_id',true);		
		} else {
			$this->notice_email = get_option('admin_email');
		}
		 
		if($this->settings('email_subj',true)) {
			$this->notice_email_subj = $this->settings('email_subj',true);
		} else {
			$this->notice_email_subj = 'Image Not Found';
		}         
	}
 
	/**
	 * Plugin Activation
	 * @since 0.3
	 * @access public
	 * @return None
	 */
	public function _activate(){
		# Delete Old Existing Database field
		if(get_option('broken_url_notifier_img')){ delete_option('broken_url_notifier_img'); }
		if(get_option('broken_url_notifier_email')){ delete_option('broken_url_notifier_email'); }
		if(get_option('broken_url_notifier_email_subject')){ delete_option('broken_url_notifier_email_subject'); }
		
		# Create New Field
		add_option("bun_settings", '', '', 'yes'); 
		add_option("bun_reports", '', '', 'yes');
	}	
	
	/**
	 * Adds A Seperate Menu
	 * @since 0.3
	 * @access public
	 * @return Adds Menu Array In WP-ADMIN Menu Array
	 */
	public function add_menu(){
		$page1 = add_menu_page('Broken Url Notifier', 'Broken Url Notifier', 'administrator','broken-url-notifier',array($this,'broken_url_notifier_page'), '');
		add_submenu_page( 'broken-url-notifier', 'Settings', 'Settings', 'administrator', 'broken-url-notifier', array($this,'broken_url_notifier_page') );
		$page2 = add_submenu_page( 'broken-url-notifier', 'View Report', 'View Report', 'administrator', 'broken-url-notifier-reports', array($this,'broken_url_notifier_reports_page') );
		
		# Register Style & Script
		$this->register_script_style();
		add_action( 'admin_print_styles-' . $page1, array($this,'enqueue_script_style') );
		add_action( 'admin_print_styles-' . $page2, array($this,'enqueue_script_style') );
	}
	
	
	/**
	 * Register All Needed Scripts & Styles
	 * @since 0.1
	 * @access public
	 */
	public function register_script_style(){
		wp_register_script( 'bun_table', bun_url.'js/jquery.dataTables.min.js', array( 'jquery' ), $this->bun_v, false );
		wp_register_script( 'bun_script', bun_url.'js/script.js', array( 'jquery' ), $this->bun_v, false );
		wp_register_style( 'bun_style', bun_url.'css/style.css', false,$this->bun_v, 'all' );
		wp_register_style( 'bun_table', bun_url.'css/jquery.dataTables.min.css', false,$this->bun_v, 'all' );
	}
	
	/**
	 * Enqueue All Needed Scripts
	 * @since 0.1
	 * @access public
	 */
	public function enqueue_script_style() {
		wp_enqueue_script( 'bun_script' );
		wp_enqueue_script( 'bun_table' );
		wp_enqueue_style( 'bun_style' );
		wp_enqueue_style( 'bun_table' );
	}
	
	/**
	 * Gets Saved Plugin Data
	 */
	private function get_exData(){
		$db_value = get_option('bun_settings');
		$this->settings_array = json_decode($db_value,true);
		$db_value = get_option('bun_reports');
		$this->broken_reports = json_decode($db_value,true);
	}
	
	/**
	 * Retrives Plugin Settings Using key
	 * @param string $key
	 */
	private function settings($key, $return = false){
		$value = '';
		if(isset($this->settings_array[$key]) && !empty($this->settings_array[$key])){
			$value = $this->settings_array[$key];
		} else {
			$value = false;
		}
		
		if($return)
			return $value;
		else 
			echo $value;		
	}
	
	/**
	 * Gets all action Requested for this plugin
	 * @since 0.3
	 * @access public
	 */
	public function request_actions(){ 
        add_action('wp_head',array($this,'log_404'),1); 
        
		if(isset($_REQUEST['action'])){
			if($_REQUEST['action'] == 'bun_save_settings'){
				$this->save_settings();
			} else if($_REQUEST['action'] == 'bun_log_img'){ 
				$this->log_image();
				if($this->settings('broken_image_status_notify',true)) {
					add_action('wp_loaded',array($this,'send_email'),1); 
				}
			} else if($_REQUEST['action'] == 'delete_log'){ 
				$this->DeleteLog();
			}
		}
	}

	/**
	 * Saves Plugin Data
	 * @since 0.1
	 * @access private
	 */	
	private function save_settings() {
		$data = array_filter($_REQUEST['bun_settings']);
		update_option('bun_settings', json_encode($data));
        $this->get_exData();
	}

	private function log_image(){
		
		$this->notice_email_content = "Your website (".site_url().") is signaling a broken image! <br/><br/>
					<strong>Broken Image Url:</strong>  ".stripslashes($_REQUEST['image'])." <br/>
					<strong>Referenced on Page:</strong> <a href='".stripslashes($_REQUEST['page'])."'>
					".stripslashes($_REQUEST['page'])."</a>";

		if($this->settings('log_broken_image',true)){
			 $arrayKey = MD5($_REQUEST['image']);
			 $tmp_arr = array();

			 if(isset($this->broken_reports[$arrayKey])){
			 	$this->broken_reports[$arrayKey]['hits'] = $this->broken_reports[$arrayKey]['hits'] + 1;
			 } else {
			 	$tmp_arr['type'] = 'image';
			 	$tmp_arr['url'] = $_REQUEST['image'];
			 	$tmp_arr['page'] = $_REQUEST['page'];
			 	$tmp_arr['hits'] = 1;
			 	$this->broken_reports[$arrayKey] = $tmp_arr;
			 }
			 
			 $data = array_filter($this->broken_reports);
			 update_option('bun_reports', json_encode($data));
		}
		
	}
	
	public function send_email(){
 		if(! wp_mail($this->notice_email ,$this->notice_email_subj, $this->notice_email_content )) {
			echo 'false';
		} else {
			echo 'sent';
		}
		exit();
	}
    

	public function DeleteLog(){
		$arrayKey = $_REQUEST['key'];
		if(isset($this->broken_reports[$arrayKey])){
			unset($this->broken_reports[$arrayKey]);
			$data = array_filter($this->broken_reports);
			update_option('bun_reports', json_encode($data));
			echo 'done';
		} else {
			echo 'failed';
		}
		exit();
	}
	
    public function log_404(){
        if(is_404()){
            if($this->settings('broken_page_status_notify',true)){
                $bun_content = "Your website (".site_url().") is signaling a broken link! <br/><br/> 
                            <strong>Referenced on Page:</strong> <a href='http://".$_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI']."'> 
                                                                    ".$_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI']."</a> <br/> 
                            <strong>Refered Page :</strong>  ".@$_SERVER["HTTP_REFERER"]." <br/> "; 

                wp_mail($this->notice_email, '404 Page Found', $bun_content );                  
            }
            
            if($this->settings('log_broken_url',true)){
                $arrayKey = MD5($_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI']);
                $tmp_arr = array();
                $tmp_arr['type'] = 'page';
                $tmp_arr['url'] =  "http://".$_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI'];
                $tmp_arr['page'] = @$_SERVER["HTTP_REFERER"];
                $tmp_arr['hits'] = 1;

                if(isset($this->broken_reports[$arrayKey])){
                    $this->broken_reports[$arrayKey]['hits'] = $this->broken_reports[$arrayKey]['hits'] + 1;
                } else { 
                    $this->broken_reports[$arrayKey] = $tmp_arr;
                }

                $data = array_filter($this->broken_reports);
                update_option('bun_reports', json_encode($data));                
            }
        }
    }
	
	/**
	 * Gets all action Requested for this plugin
	 * @since 0.3
	 * @access public
	 */	
	public function broken_url_notifier_page(){
		wp_enqueue_media();
		require(bun_path.'inc/bun_page.php');
	}
    
	public function broken_url_notifier_reports_page(){  
		require(bun_path.'inc/bun_report_page.php');
	}	
	
	
	public function add_footer_script(){ 
		echo "<script>
				jQuery(document).ready(function(){
					jQuery('img').error(function() {
        				var oldImg = jQuery(this).attr('src');
       					jQuery(this).attr('src', '".$this->settings('error_image',true)."');
						jQuery.post(window.location.href, {
       						action : 'bun_log_img',
							imageError:'yes',
							image: oldImg,
							page: window.location.href
						}, function() {
						});
					});
				});
       			</script>";	 	
	}
	

}

$Broken_Url_Notifier = new Broken_Url_Notifier;

?>