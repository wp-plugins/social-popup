<?php

/**********
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
****************************************************************************
 * @author      Damian Logghe <info@timersys.com>
 * @license     MIT License https://github.com/serbanghita/Mobile-Detect/blob/master/LICENSE.txt
 * @link        GitHub Repository: https://github.com/timersys/wp-plugin-base
 * @version     1.0
 */

/*
* I also took quite lot of code from http://alisothegeek.com/2011/04/wordpress-settings-api-tutorial-follow-up/
*
*/


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists('WP_Plugin_Base') ) {
  
class WP_Plugin_Base {

	protected $WPB_PREFIX		=	'wpb';
	protected $WPB_SLUG			=	'wp-plugin-base'; // Need to match plugin folder name
	protected $WPB_PLUGIN_NAME	=	'WP Plugin Base';
	protected $WPB_VERSION		=	'1.0';
	protected $WPB_ABS_PATH;	
	protected $WPB_REL_PATH;
	protected $WPB_PLUGIN_URL;
	protected $PLUGIN_FILE;
	protected $current_page;
	protected $options_name;
	
	protected $sections;
	protected $checkboxes;
	protected $settings;
	
	var $_options;
	var $_credits;
	var $_defaults;
	
	function __construct() {
	
		$this->WPB_ABS_PATH 	= WP_PLUGIN_DIR . '/'. $this->WPB_SLUG;
		$this->WPB_REL_PATH		=	dirname( plugin_basename( __FILE__ ) );
		$this->WPB_PLUGIN_URL	=	WP_PLUGIN_URL . '/'. $this->WPB_SLUG;
	
		//activation hook
		register_activation_hook( __FILE__, array(&$this,'activate' ));
		
		//Load all fields and defaults
		$this->get_settings();        
		
				
		//register database options and prepare fields with settings API
        add_action( 'admin_init', array( &$this, 'register_settings' ) );
		
		if ( ! get_option( $this->options_name ) )
			$this->initialize_settings();
					
		//load js and css 
		add_action( 'init',array(&$this,'load_base_scripts' ) );	
		
		//adding settings links on plugins page
		add_filter( 'plugin_action_links', array(&$this,'add_settings_link'), 10, 2 );
		
		//translations
		
		if ( function_exists ('load_plugin_textdomain') ){
			load_plugin_textdomain ( $this->WPB_PREFIX, false, $this->WPB_REL_PATH . '/languages/' );
		}
		
		
		//Ajax hooks here	
	
		
	}	
		
	/**
	* Check technical requirements before activating the plugin. 
	* Wordpress 3.0 or newer required
	*/
	function activate()
	{
		if ( ! function_exists ('register_post_status') ){
			deactivate_plugins (basename (dirname (__FILE__)) . '/' . basename (__FILE__));
			wp_die( __( "This plugin requires WordPress 3.0 or newer. Please update your WordPress installation to activate this plugin.", $this->WPB_PREFIX ) );
		}
		
	}	

		/**
	 * Settings and defaults
	 * 
	 * @since 1.0
	 */
	public function get_settings() {
		
		require_once(dirname (__FILE__).'/admin/fields.php');
		
	}

	/**
	* Register settings
	*
	* @since 1.0
	*/
	public function register_settings() {
		
		
	
		register_setting( $this->options_name, $this->options_name, array ( &$this, 'validate_settings' ) );
				
		foreach ( $this->sections as $slug => $title ) {
			add_settings_section( $slug, $title, array( &$this, 'display_section' ), $this->options_name );
		}
		
		$this->get_settings();
		
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );
		}
		
	}
	
	/**
	 * Initialize settings to their default values
	 * 
	 * @since 1.0
	 */
	public function initialize_settings() {
		
		$default_settings = array();
		foreach ( $this->settings as $id => $setting ) {
			if ( $setting['type'] != 'heading' )
				$default_settings[$id] = $setting['std'];
		}
		
		update_option( $this->options_name, $default_settings );
		
	}

	/**
	 * Create settings field
	 *
	 * @since 1.0
	 */
	public function create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      => 'default_field',
			'title'   => __( 'Default Field', $this->WPB_PREFIX ),
			'desc'    => __( 'This is a default description.' , $this->WPB_PREFIX),
			'std'     => '',
			'type'    => 'text',
			'section' => 'general',
			'choices' => array(),
			'onclick' => '',
			'class'   => ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'desc'      => $desc,
			'std'       => $std,
			'choices'   => $choices,
			'label_for' => $id,
			'onclick'	=> $onclick,
			'class'     => $class,
			'title'		=> $title
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
		add_settings_field( $id, $title, array( $this, 'display_setting' ), $this->options_name, $section, $field_args );
	}
	
	/**
	* Add a settings link to the Plugins page
	*
	* http://www.whypad.com/posts/wordpress-add-settings-link-to-plugins-page/785/
	*/
	function add_settings_link( $links, $file )
	{
		
		
		if ( $file == $this->PLUGIN_FILE ){
			$settings_link = '<a href="options-general.php?page='.$this->WPB_SLUG.'">' . __( "Settings" ) . '</a>';
	
			array_unshift( $links, $settings_link );
		}
	
		return $links;
	}

	/**
	 * HTML output callback for fields
	 *
	 * @since 1.0
	 */
	public function display_setting( $args = array() ) {
		
		extract( $args );
		
		$options = get_option( $this->options_name );
		
		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;
		
		$field_class = '';
		if ( $class != '' )
			$field_class = ' ' . $class;
		
		switch ( $type ) {
			
			case 'heading':
				echo '</td></tr><tr valign="top"><td colspan="2"><h4>' . $desc . '</h4>';
				break;
			
			case 'checkbox':
			
				if (!empty($choices) )
				{
					
					if( !is_array($options[$id]) && is_array($std))
					{ 
						$options[$id] = array();
						foreach( $std as $default)
						{
							$options[$id][$default] = 1;
						}	
						
					}
					foreach( $choices as $val => $label)
					{
						echo '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="'.$this->options_name.'[' . $id . '][' . $val . ']" value="1" ' . @checked( $options[$id][$val], 1, false ) . '  /> <label for="' . $id . '">' . $label . '</label><br>';
					}
				}
				else	
				{
					echo '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="'.$this->options_name.'[' . $id . ']" value="1" ' . checked( $options[$id], 1, false ) . ' /> <label for="' . $id . '">' . $desc . '</label>';
				}
				
				break;
			
			case 'select':
				echo '<select class="select' . $field_class . '" name="'.$this->options_name.'[' . $id . ']">';
				
				foreach ( $choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $options[$id], $value, false ) . '>' . $label . '</option>';
				
				echo '</select>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $choices as $value => $label ) {
					echo '<input class="radio' . $field_class . '" type="radio" name="'.$this->options_name.'[' . $id . ']" id="' . $id . $i . '" value="' . esc_attr( $value ) . '" ' . checked( $options[$id], $value, false ) . '> <label for="' . $id . $i . '">' . $label . '</label>';
					if ( $i < count( $options ) - 1 )
						echo '<br />';
					$i++;
				}
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'textarea':
				echo '<textarea class="' . $field_class . '" id="' . $id . '" name="'.$this->options_name.'[' . $id . ']" placeholder="' . $std . '" rows="5" cols="30">' . wp_htmledit_pre( $options[$id] ) . '</textarea>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'password':
				echo '<input class="regular-text' . $field_class . '" type="password" id="' . $id . '" name="'.$this->options_name.'[' . $id . ']" value="' . esc_attr( $options[$id] ) . '" />';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'button':
		 		echo '<button class="button-primary' . $field_class . '" id="' . $id . '" name="'.$this->options_name.'[' . $id . ']" value="' . esc_attr( $options[$id] ) . '" onclick="' . $onclick . '">' . $title . '</button>';
		 		
		 		if ( $desc != '' )
		 			echo '<br /><span class="description">' . $desc . '</span>';
		 		
		 		break;
		 					
			case 'text':
			default:
		 		echo '<input class="regular-text' . $field_class . '" type="text" id="' . $id . '" name="'.$this->options_name.'[' . $id . ']" placeholder="' . $std . '" value="' . esc_attr( $options[$id] ) . '" />';
		 		
		 		if ( $desc != '' )
		 			echo '<br /><span class="description">' . $desc . '</span>';
		 		
		 		break;


		 	
		}
		
	}
	
		/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public function display_page() {
		
		require_once( dirname(__FILE__).'/admin/header.php');		
	
		echo '<form action="options.php" method="post" id="form">';
	
		settings_fields( $this->options_name );
		do_settings_sections($this->options_name );
		
		?>
		<p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php _e( 'Save Changes', $this->WPB_PREFIX );?>" /></p>
		
		<?php
		require_once( dirname(__FILE__).'/admin/sidebar.php');
		?>
		
		
		
	</form>

	<script type="text/javascript">
	
		
		
		jQuery(document).ready(function($) {
			var sections = [];
			
			$('#right-sidebar').stickyMojo({footerID: '#wpfooter', contentID: '#left-content'});
		<?php	
			foreach ( $this->sections as $section_slug => $section )
				echo "sections['$section'] = '$section_slug';";
		?>	
			var wrapped = $(".wrap h3").not('.nowrap').wrap("<div class=\"ui-tabs-panel\">");
			wrapped.each(function() {
				$(this).parent().append($(this).parent().nextUntil("div.ui-tabs-panel"));
			});
			$(".ui-tabs-panel").each(function(index) {
				$(this).attr("id", sections[$(this).children("h3").text()]);
				if (index > 0)
					$(this).addClass("ui-tabs-hide");
			});
			
			$('p.submit').appendTo('#form');
			
			$("input[type=text], textarea").each(function() {
				if ($(this).val() == $(this).attr("placeholder") || $(this).val() == "")
					$(this).css("color", "#999");
			});
			
			$("input[type=text], textarea").focus(function() {
				if ($(this).val() == $(this).attr("placeholder") || $(this).val() == "") {
					$(this).val("");
					$(this).css("color", "#000");
				}
			}).blur(function() {
				if ($(this).val() == "" || $(this).val() == $(this).attr("placeholder")) {
					$(this).val($(this).attr("placeholder"));
					$(this).css("color", "#999");
				}
			});
			
			$("#ui-tabs a").eq(0).addClass("nav-tab-active");
			$("#ui-tabs a").click(function(){
			
				$("#ui-tabs a").removeClass("nav-tab-active");
				$(this).addClass("nav-tab-active");
				$('.ui-tabs-panel').hide();
				$($(this).attr('href')).fadeIn();
				return false;
			});
			
			$(".wrap h3, .wrap table").show();
			
			// This will make the "warning" checkbox class really stand out when checked.
			// I use it here for the Reset checkbox.
			$(".warning").change(function() {
				if ($(this).is(":checked"))
					$(this).parent().css("background", "#c00").css("color", "#fff").css("fontWeight", "bold");
				else
					$(this).parent().css("background", "none").css("color", "inherit").css("fontWeight", "normal");
			});
			$('.updated').delay(3000).fadeOut();


			// Browser compatibility
			if ($.browser.mozilla) 
			         $("form").attr("autocomplete", "off");
		});
		function sticky_relocate() {
			    var window_top = jQuery(window).scrollTop();
			    var div_top = jQuery('#sticky-anchor').offset().top;
			    if (window_top > div_top) {
			        jQuery('#sticky').addClass('stick').css('top',div_top);
			    } else {
			        jQuery('#sticky').removeClass('stick');
			    }
		}
	</script> 

	<?php
		
	}


	/**
	*	function that register plugin options 
	*/
 	 function register_options()
	{
		register_setting( $this->WPB_PREFIX.'_options', $this->WPB_PREFIX.'_settings' );
		
	}

	/**
	 * Description for section
	 *
	 * 
	 */
	public function display_section() {
		// common html text
	}

	/**
	* Load scripts and styles
	*/
	function load_base_scripts()
	{
			
		
			if( is_admin() && isset($_GET['page']) && $_GET['page'] == $this->WPB_SLUG )
			{
				wp_enqueue_style('wsi-admin-css', plugins_url( 'admin/assets/base/style.css', __FILE__ ) , __FILE__,'','all',$this->WPB_VERSION );
				wp_enqueue_script('sticky', plugins_url( 'admin/assets/base/sticky.js', __FILE__ ) ,array('jquery'),$this->WPB_VERSION );
			}	
		
	}
	
	 /**
	 * Render Options Page
	 */
	 function options_page()
	{
		
		?>
		<form method="post" action="options.php" >
		<?php
		
		    settings_fields( $this->WPB_PREFIX.'_options' );
			
			//wich tab page we are now
			$this->current_page = isset( $_REQUEST['wpb_page'] ) ?  $_REQUEST['wpb_page'] : 'index';
			
			//Headers and tabs
			require_once( dirname (__FILE__).'/admin/header.php');
			
			//Tabs content page
			require_once( dirname (__FILE__).'/admin/tabs/'.$this->current_page.'.php');	
		
		?>
		</form>
		<?php
		
		//Sidebar credits
		require_once( dirname (__FILE__).'/admin/sidebar.php');	
		
		
	}
	
	/**
	* Validate settings
	*
	* @since 1.0
	*/
	public function validate_settings( $input ) {
		
		if ( ! isset( $input['reset_plugin'] ) ) {
			$options = get_option( $this->options_name );
			
			foreach ( $this->checkboxes as $id ) {
				if ( isset( $options[$id] ) && ! isset( $input[$id] ) )
					unset( $options[$id] );
			}
			
			return $input;
		}
		return false;
		
	}
	function is_multi($a) {
    $rv = array_filter($a,'is_array');
    if(count($rv)>0) return true;
    return false;
}
	
	
}

} //check if exisct