<?php
/*
Plugin Name: Social PopUP - Google+, Facebook and Twitter popup
Plugin URI: http://www.timersys.com/plugins-wordpress/social-popup/
Version: 1.6.4.1
Description: This plugin will display a popup or splash screen when a new user visit your site showing a Google+, twitter and facebook follow links. This will increase you followers ratio in a 40%. Popup will be close depending on your settings. Check readme.txt for full details.
Author: Damian Logghe
Author URI: http://www.timersys.com
License: MIT License
Text Domain: spu
Domain Path: languages
*/

/*

**********
* License
****************************************************************************
*	Copyright (C) 2011-2013 Damian Logghe and contributors
*
*	Permission is hereby granted, free of charge, to any person obtaining
*	a copy of this software and associated documentation files (the
*	"Software"), to deal in the Software without restriction, including
*	without limitation the rights to use, copy, modify, merge, publish,
*	distribute, sublicense, and/or sell copies of the Software, and to
*	permit persons to whom the Software is furnished to do so, subject to
*	the following conditions:
*
*	The above copyright notice and this permission notice shall be
*	included in all copies or substantial portions of the Software.
*
*	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
*	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
*	MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
*	NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
*	LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
*	OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
*	WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
****************************************************************************/


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( function_exists ('load_plugin_textdomain') ){
	load_plugin_textdomain ( 'spu', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}
		
		
require(dirname (__FILE__).'/WP_Plugin_Base.class.php');
  
class Social_Popup extends WP_Plugin_Base_spu
{

	
	
	var $_credits;
	var $_defaults;
	
		protected $sections;
		
	
	function __construct() {
		
		$this->WPB_PREFIX		=	'spu';
		$this->WPB_SLUG			=	'social-popup'; // Need to match plugin folder name
		$this->WPB_PLUGIN_NAME	=	'Social PopUP';
		$this->WPB_VERSION		=	'1.6.4.1';
		$this->PLUGIN_FILE		=   plugin_basename(__FILE__);
		$this->options_name		=   'spu_settings';
		
		$this->sections['spu_general']      		= __( 'Main Settings', $this->WPB_PREFIX );
		$this->sections['spu_styling']   			= __( 'Styling', $this->WPB_PREFIX );
		$this->sections['spu_display_rules']        = __( 'Display Rules', $this->WPB_PREFIX );
		$this->sections['spu_debugging']       		= __( 'Debugging', $this->WPB_PREFIX );
		
		
		//activation hook
		register_activation_hook( __FILE__, array(&$this,'activate' ));        
		
		//deactivation hook
		register_deactivation_hook( __FILE__, array(&$this,'deactivate' ));   
		

		
		//admin menu
		add_action( 'admin_menu',array(&$this,'register_menu' ) );

		
		//load js and css 
		add_action( 'init',array(&$this,'load_scripts' ),50 );	

		
	
		$this->upgradePlugin();
			
		$this->setDefaults();
		
		$this->loadOptions();
		
		//print popup and code to run popup		
		add_action( 'wp_head',array(&$this,'exec_plugin' ) );
		add_action( 'wp_footer',array(&$this,'print_pop' ) );	
		
		//AJAX
		add_action('wp_ajax_spu_reset', array(&$this,'reset_styling' ));
		
		
		parent::__construct();
		
	
		
	}	
		
	/**
	* Check technical requirements before activating the plugin. 
	* Wordpress 3.0 or newer required
	*/
	function activate()
	{
		parent::activate();
		

		do_action( $this->WPB_PREFIX.'_activate' );
		
		
	}	

	/**
	* Run when plugin is deactivated
	* Wordpress 3.0 or newer required
	*/
	function deactivate()
	{
		
	#	global $wpdb;
	#	$wpdb->query("DROP TABLE  `".$wpdb->base_prefix."wsm_monitor_index`");
		
		do_action( $this->WPB_PREFIX.'_deactivate' );
	}
	


	/**
	* function that register the menu link in the settings menu	and editor section inside the option page
	*/
	 function register_menu()
	{
		$menu = add_menu_page( 'Social PopUP', 'Social PopUP', 'manage_options',  $this->WPB_SLUG,array(&$this, 'display_page') );
		
	}


	
	
	/**
	* Load scripts and styles
	*/
	function load_scripts()
	{
		if(!is_admin())
		{
			
			wp_enqueue_script('spu-fb', 'http://connect.facebook.net/en_US/all.js#xfbml=1', array('jquery'),$this->WPB_VERSION,FALSE);
			wp_enqueue_script('spu-tw', 'http://platform.twitter.com/widgets.js', array('jquery'),$this->WPB_VERSION,FALSE);
			wp_enqueue_script('spu-go', 'https://apis.google.com/js/plusone.js', array('jquery'),$this->WPB_VERSION,FALSE);
			wp_enqueue_script('spu', plugins_url( 'spu.js' , __FILE__ ),array('jquery'),$this->WPB_VERSION);
			wp_enqueue_style('spu-css', plugins_url( 'spu.css' , __FILE__ ),'all',$this->WPB_VERSION);
	
		}
		else
		{
			wp_enqueue_script('spu-admin', plugins_url( 'admin/assets/js/spu.js' , __FILE__ ),array('jquery'),$this->WPB_VERSION);
			wp_enqueue_script('codemirror');
		}
		
	}
	
	function exec_plugin()
	{
	
		$print_script = false;
		$options = $this->_options;
		
		
		// Only continue if the pop-up option is enabled...
		if($options['enable'] == 'true')
		{ 
			

			//if show everywhere i print script
			if( isset($options['where']['everywhere']) && $options['where']['everywhere'] == '1' )
			{
			
				
				$print_script = true;
			}
			else
			{
				if( isset($options['where']['posts']) && $options['where']['posts'] == '1' )
				{
				
					if ( is_single() || is_home() )
					{
						$print_script = true;
					}
				}
				if( isset($options['where']['pages']) && $options['where']['pages'] == '1' )
				{
				
					if ( is_page() )
					{
						$print_script = true;
					}
				}
				if( isset($options['where']['home']) && $options['where']['home'] == '1' )
				{
				
					if ( is_front_page() )
					{
						$print_script = true;
					}
				}
				
			}
			
			if (isset($options['show_to']) && key_exists('nologged', $options['show_to']) && key_exists('logged', $options['show_to'])  )
			{
				//$print_script = true; if its true it will remain true
			}
			elseif (isset($options['show_to']) && key_exists('logged', $options['show_to']))
			{
				if( is_user_logged_in() && $print_script == true )
				{
					$print_script = true;
				}
				else
				{
					$print_script = false;
				}
			
			}
			elseif (isset($options['show_to']) && key_exists('nologged', $options['show_to']))
			{
				if( !is_user_logged_in() && $print_script == true )
				{
					$print_script = true;
				}
				else
				{
					$print_script = false;
				}			
				
			}
			if( isset($options['roles']) && key_exists('logged', $options['show_to']) && is_user_logged_in() )
			{
				foreach( $options['roles'] as $rol => $v)
				{
					
					if( current_user_can(strtolower($rol)) &&  $print_script == true )
					{
						$print_script = true;
						break;
					}
					else
					{
						$print_script = false;
					}	
					
				}
				
			}

			if( isset($options['show_if']) && is_array($options['show_if']) && key_exists('never_commented', $options['show_if']) )
			{ 
				if ( !isset($_COOKIE['comment_author_'.COOKIEHASH]) &&  $print_script == true ) {
					$print_script = true;
				} else {
					$print_script = false;
				}
			}	

			if( isset($options['show_if']) && is_array($options['show_if']) && key_exists('search_engine', $options['show_if']) &&  $print_script == true )
			{ 
				$ref = isset($_SERVER['HTTP_REFERRER']) ? $_SERVER['HTTP_REFERRER'] : '';

				$SE = array('/search?', '.google.', 'web.info.com', 'search.', 'del.icio.us/search', 'soso.com', '/search/', '.yahoo.', '.bing.' );
	
				foreach ($SE as $url) {
					if (strpos($ref,$url)!==false){
						$print_script = true;
						break;
					}
					else
					{
						$print_script = false;
					} 
				}
			}
			if( isset($options['show_if']) && is_array($options['show_if']) && key_exists('internal', $options['show_if']) &&  $print_script == true )
			{ 
				$internal = str_replace(array('http://','https://'),'',site_url());
				if($this->referrer_matches(addcslashes($internal,"/"))) {
					$print_script = false;
				}
				
			}
			if( isset($options['referrer']) && $options['referrer'] != '' &&  $print_script == true )
			{ 
				
				if(!$this->referrer_matches(addcslashes($options['referrer'],"/"))) {
					$print_script = false;
				}
				
			}
			if( isset($options['on_url']) && $options['on_url'] != '' &&  $print_script == true )
			{
				$array_urls =  explode("\n", $options['on_url']);
				$urllist = array_map( 'trim', $this->sanitise_array($array_urls) );

				if(!empty($urllist)) {
					if(in_array($this->myURL(), $urllist)) {
						// we are on the list
						$print_script = true;
						
					} else {
						$print_script = false;
				    }
				} else {
					$print_script = true;
				}
			}
			
			if( isset($options['not_on_url']) && $options['not_on_url'] != '' &&  $print_script == true )
			{
				$array_urls =  explode("\n", $options['not_on_url']);
				$urllist = array_map( 'trim', $this->sanitise_array($array_urls) );

				if(!empty($urllist)) {
					if(in_array($this->myURL(), $urllist)) {
						// we are on the list
						$print_script = false;
							
					} else {
						$print_script = true;
				    }
				} else {
					$print_script = true;
				}
			}
				
			if( isset( $options['mobiles']) && $options['mobiles'] == 'false' &&  $print_script == true )
			{
				require_once( dirname (__FILE__).'/Mobile_Detect.php');
				$detect = new Mobile_Detect;
				if ( $detect->isMobile() || $detect->isTablet()){
					$print_script = false;
				}
			}
	

		} // End if enabled
	
		if( $print_script ) $this->print_script();
		
	} // End exec plugin

	/**
	* Print the script
	*/
	function print_script()
	{
		$options = $this->_options;
		$credit = $this->_credits;
	?>				
					<style type="text/css">
					<?php echo $options['css'];?>
					</style>			
					<script type="text/javascript">
						jQuery(document).ready(function($){
								
						setTimeout( 
						function(){				
							socialPopUP({
								// Configure display of popup
								advancedClose: <?php echo $options['close-advanced']; ?>,
								opacity: "<?php echo $options['bg_opacity']; ?>",
								s_to_close: "<?php echo $options['seconds-to-close'] ; ?>",
								days_no_click: "<?php echo $options['days-no-click']; ?>",
								segundos : "<?php _e('seconds',$this->WPB_PREFIX);?>",
								esperar : "<?php _e('Wait',$this->WPB_PREFIX);?>",
								thanks_msg : "<?php echo $options['thanks_msg'] ; ?>",
								thanks_sec : "<?php echo $options['thanks_sec'] ; ?>",
							})
						}
							,<?php echo (int)$options['seconds-to-show'] * 1000 ;?>
								);
						});	
						
						
					</script>
					
	<?php
	}

	/**
	* Print popup html markup in footer
	*/
	function print_pop()
	{		
		$options = $this->_options;
		//used for old version
		$credits = $this->_credits;
		
		
		$socials = array(
			"google" => '<div class="spu-button spu-google"><div class="g-plusone" data-callback="googleCB" data-annotation="bubble" data-size="medium" data-href="' . $options['google'] . '"></div></div>',
	  		"twitter" => '<div class="spu-button spu-twitter"><a href="https://twitter.com/' . $options['twitter'] . '" class="twitter-follow-button" data-show-count="false" >Follow Me</a></div>',
	  		"facebook" => '<div class="spu-button spu-facebook"><div id="fb-root"></div><div class="fb-like" data-href="' . $options['facebook'] . '" data-send="false" data-width="450" data-show-faces="true"data-layout="button_count"></div></div>'
	  	);
	  	$template = $options['template'];
	
		echo '<div id="spu-bg"></div>
				<div id="spu-main">';
				echo $options['close'] == 'true' ? '<a href="#" onClick="spuFlush('. $options['days-no-click'] .');" id="spu-close">Close</a>' : '';
			 
				
				foreach ($socials as $key => $value)
				{
					$template = str_replace("{" . $key . "}", $value, $template);
				}
				echo $template;
				echo '<span id="spu-timer"></span>';
		echo ((isset($options['credits']) && $options['credits'] == 'true') || isset($credits['credits']) && $credits['credits'] == 'on' ) ? '<div id="spu-bottom"><span style="font-size:10px;float: right;margin-top: -6px;">Social PopUP by <a href="http://www.timersys.com">Timersys</a></span></div>':'';
		
		echo '</div>';
	}
	/**
	* Load options to use later
	*/	
	function loadOptions()
	{

		$this->_options = get_option($this->WPB_PREFIX.'_settings',$this->_defaults);

		$this->_styling = get_option($this->WPB_PREFIX.'_styling',$this->_defaults);
		$this->_display_rules = get_option($this->WPB_PREFIX.'_display_rules',$this->_defaults);
	}
	
		
	/**
	* loads plugins defaults
	*/
	function setDefaults()
	{
		$this->_defaults = array( 'version' => $this->WPB_VERSION ,'enable' => 'true',  'facebook' => 'https://www.facebook.com/pages/Timersys/146687622031640', 'twitter'=>'chifliiiii','google' => '','close' => 'true','close-advanced' => 'true', 'bg_opacity' => '0.65' , 'days-no-click' => '99', 'where' => array('everywhere'=>'true' ),'seconds-to-show' => '1', 'thanks_msg' => 'Thanks for supporting the site', 'thanks_sec' => '3', 'template' => '<div id="spu-title">Please support the site</div>
	<div id="spu-msg-cont">
	     <div id="spu-msg">
	     By clicking any of these buttons you help our site to get better </br>
	     {twitter} {facebook} {google}
	     </div>
	    <div class="step-clear"></div>
	</div>', 'css' =>'.spu-button {
		margin-left:15px;
		margin-left: 15px;
		display: inline-table;
		margin-top: 12px;
		vertical-align: middle;
	}
	#spu-msg-cont {
		border-bottom:1px solid#ccc;
		border-top:1px solid#ccc;
		background-image:linear-gradient(bottom,#D8E7FC 0%,#EBF2FC 65%);
		background-image:-o-linear-gradient(bottom,#D8E7FC 0%,#EBF2FC 65%);
		background-image:-moz-linear-gradient(bottom,#D8E7FC 0%,#EBF2FC 65%);
		background-image:-webkit-linear-gradient(bottom,#D8E7FC 0%,#EBF2FC 65%);
		background-image:-ms-linear-gradient(bottom,#D8E7FC 0%,#EBF2FC 65%);
		background-image:-webkit-gradient(linear,left bottom,left top,color-stop(0,#D8E7FC),color-stop(0.85,#EBF2FC));
		padding:16px;
	}
	#spu-msg {
		margin:0 0 22px;
	}
	.step-clear {
		clear:both!important;
	}
	#spu-title {
		font-family:"Lucida Sans Unicode","Lucida Grande",sans-serif!important;
		font-size:12px;
		padding:12px 0 9px 10px;
		font-size:16px;
	}' );
		
	}
	
	/**
	* RESET styling function
	*/
	function reset_styling() {

		if($_REQUEST['what'] == 'html')
		{
			echo $this->_defaults['template'];
		}
		if($_REQUEST['what'] == 'css')
		{
			echo $this->_defaults['css'];
		}
		die();
	}	

	/**
	* function to check internal or external referer
	*/
	function referrer_matches($check) {
	
		$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
	
		if(preg_match( '/' . $check . '/i', $referer )) {
			return true;
		} else {
			return false;
		}
	
	}
	/**
	* function that returns current site url
	*/
	function myURL() {
	
	 	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			$url .= "https://";
		} else {
			$url = 'http://';
		}
	
		if ($_SERVER["SERVER_PORT"] != "80") {
	  		$url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	 	} else {
	  		$url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	 	}
	
	 	return trailingslashit($url);
	}
	/**
	* function to create and sanitize array for onurl
	*/
	function sanitise_array($arrayin) {
	
		foreach( (array) $arrayin as $key => $value) {
			$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
		}
	
		return $arrayin;
	}

	/**
	* Upgrade previous plugin version database options
	*/
	function upgradePlugin(){
		
		$options = get_option($this->WPB_PREFIX.'_option');

		$new_options = $options;
		
		if( !$options ) return;
		
		$new_options['where'] = '';
		if( is_array( $options['where']))
		{
			foreach( $options['where'] as $k => $v )
			{
				 $new_options['where'][$k]= '1';
			}
		}
		
		$new_options['roles'] = '';
		if( is_array( $options['roles']))
		{
			foreach( $options['roles'] as $k => $v )
			{
				 $new_options['roles'][$v]= '1';
			}
		}
		
		$new_options['show_to'] = '';
		if( is_array( $options['show_to']))
		{
			foreach( $options['show_to'] as $k => $v )
			{
				 $new_options['show_to'][$v]= '1';
			}
		}
		
		$new_options['show_if'] = '';
		if( is_array( $options['show_if']))
		{
			foreach( $options['show_if'] as $k => $v )
			{
				 if($v!='' ) $new_options['show_if'][$v]= '1';
			}
		}
		
		if(isset($options['show_if']['referrer']) && $options['show_if']['referrer'] != '')
		{
			$new_options['referrer'] = $options['show_if']['referrer'];
		}

		if(isset($options['show_if']['onurl']) && $options['show_if']['onurl'] != '')
		{
			$new_options['on_url'] = $options['show_if']['onurl'];
		}

		if(isset($options['show_if']['notonurl']) && $options['show_if']['notonurl'] != '')
		{
			$new_options['not_on_url'] = $options['show_if']['notonurl'];
		}
		
		$new_options['seconds-to-show'] = '1';
		
		delete_option($this->WPB_PREFIX.'_option');
		update_option($this->WPB_PREFIX.'_settings', $new_options);
	}

} //end of class
	
	

$wsi = new Social_Popup();