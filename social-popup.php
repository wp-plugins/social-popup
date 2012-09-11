<?php
/**
 Plugin Name: Social PopUP - Google+, Facebook and Twitter popup
 Plugin URI: http://www.masquewordpress.com/plugins/social-popup/
 Version: 1.1
 Description: This plugin will display a popup or splash screen when a new user visit your site showing a Google+, twitter and facebook follow links. This will increase you followers ratio in a 40%. Popup will be close depending on your settings. Check readme.txt for full details.
 Author: Damian Logghe
 Author URI: http://www.masquewordpress.com
 */

class socialPopup 
{
	function __construct() {
		
        
        add_action( 'admin_init', array(&$this,'register_options' ));
		
		add_action( 'admin_menu',array(&$this,'register_menu' ) );
		
		add_action( 'init',array(&$this,'load_scripts' ) );	
		
		add_action( 'wp_head',array(&$this,'exec_plugin' ) );	
	}
	
	
	
	
		
	 	//function that register options 
 	 function register_options()
	{
		register_setting( 'spu_options', 'spu_option' );
		
		
	}
	
		//function that register the menu link in the settings menu	and editor section inside the option page
	 function register_menu()
	{
		add_options_page( 'Social PopUP', 'Social PopUP', 'manage_options', 'social-pop-up',array(&$this, 'options_page') );
		
		add_settings_section('bsbm_forms', 'Settings', array(&$this, 'style_box_form'), 'spu_style_form');
	}
	
	//Function that load the scripts
	function load_scripts()
	{
		if(!is_admin())
		{
			
			wp_enqueue_script('spu-fb', 'http://connect.facebook.net/en_US/all.js#xfbml=1', array('jquery'),FALSE,FALSE);
			wp_enqueue_script('spu-tw', 'http://platform.twitter.com/widgets.js', array('jquery'),FALSE,FALSE);
			wp_enqueue_script('spu-go', 'https://apis.google.com/js/plusone.js', array('jquery'),FALSE,FALSE);
			wp_enqueue_script('spu', plugins_url( 'spu.js' , __FILE__ ),array('jquery'));
			wp_enqueue_style('spu-css', plugins_url( 'spu.css' , __FILE__ ));
		}
	}
	
	 //function that display the options page
	 function options_page()
	{
		?>
		<div class="metabox-holder">
	    
	    <?php screen_icon(); echo "<h2>". __( 'Social PopUP' ) ."</h2>"; ?>
	 
	   
	    <style type="text/css">
	    	.postbox input.field,.postbox textarea  { width:500px;}
	    </style>
	   	<form method="post" action="options.php" style="width:70%;" >
	 
	    <?php settings_fields( 'spu_options' );?>
	   
	    
	    <div class="postbox"><?php do_settings_sections( 'spu_style_form' ); ?></div>
	    </form>
	    </div>
	<?
	
	}
	
	
	
	
	//function that display the textarea editor form
	function style_box_form()
	{
		$defaults = array( 'enable' => 'false', 'title' => 'Please support the site','message' => 'By clicking any of these buttons you help our site to get better', 'facebook' => 'https://www.facebook.com/pages/Timersys/146687622031640', 'twitter'=>'chifliiiii','close' => 'true','close-advanced' => 'true', 'bg_opacity' => '0.65' , 'days-no-click' => '10' );
		$options = get_option('spu_option',$defaults);
		
		
		?>

			<div class="inside"><div class="intro"><p>Please add settings for the Social PopUP.</p></div> 
				
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row">Enable / Disable Social PopUP</th>
					<td><fieldset>
						<select name="spu_option[enable]" >
			                <option value="true" <?PHP if($options['enable'] == 'true'){echo 'selected="selected"';} ?>>Enabled</option>
			                <option value="false" <?PHP if($options['enable'] == 'false'){echo 'selected="selected"';} ?>>Disabled</option>
						</select>
					</fieldset>
					</td>
				</tr>
 
		    
		    	<tr valign="top">
			        <th scope="row">Popup Title</th>
			        <td><fieldset>
						<input class="field" name="spu_option[title]" type="text"  value="<?php echo $options['title']; ?>" />
			                        
						<div class="description">Title / titlebar text of your popup.</div>
			        </fieldset>
			        </td>
		    	</tr>
		   
		    	<tr valign="top">
			        <th scope="row">Popup Message</th>
			        <td><fieldset>
			        	<textarea name="spu_option[message]" cols="" rows="5" ><?php echo $options['message']; ?></textarea>
			        
			        	<div class="description">The message you want to show inside your popup.</div>
			        </fieldset>
			        </td>
		    	</tr>
		    
		    	<tr valign="top">
		        	<th scope="row">Google '+1' URL</th>
		        	<td><fieldset>
		        		<input class="field" name="spu_option[google]" type="text" value="<?php echo $options['google']; ?>" />
		        
		        		<div class="description">The Google url you want to +1 (include 'http://'). Leave empty for current visitor page</div>
		        	</fieldset>
		        	</td>
		    	</tr>
		   
		    	<tr valign="top">
		     	   <th scope="row">Facebook URL</th>
		     	   <td><fieldset>
		     	   		<input class="field" name="spu_option[facebook]" type="text"  value="<?php echo $options['facebook']; ?>" />
		        
		     	   		<div class="description">You facebook page (include 'http://').</div>
		     	   </fieldset>
		     	   </td>
		    	</tr>
		    	
		    	<tr valign="top">
		        	<th scope="row">Twitter Username</th>
		        	<td><fieldset>
		        		<input class="field" name="spu_option[twitter]" type="text"  value="<?php echo $options['twitter']; ?>" />
		        
		        		<div class="description">The Twitter usename to use with the follow, without "@" sign</div>
		        	</fieldset>
		        	</td>
		    	</tr>
		    
		    	<tr valign="top">
		    		<th scope="row">Show Close Button</th>
		    		<td><fieldset>
						<select name="spu_option[close]">
			                <option value="true" <?PHP if($options['close'] == 'true'){echo 'selected="selected"';} ?> >Yes</option>
			                <option value="false" <?PHP if($options['close'] == 'false'){echo 'selected="selected"';} ?> >No</option>
			            </select>
			            <div class="description">Enable / Disable the close button.</div>
		    		</fieldset>
		    		</td>
		    	</tr>
		        
		    	<tr valign="top">
		        	<th scope="row">Close Advanced keys</th>
		        	<td><fieldset>
						<select name="spu_option[close-advanced]" >
			                <option value="true" <?PHP if($options['close-advanced'] == 'true'){echo 'selected="selected"';} ?>>Enabled</option>
			                <option value="false" <?PHP if($options['close-advanced'] == 'false'){echo 'selected="selected"';} ?>>Disabled</option>
						</select>
		        
						<div class="description">If enabled, users can close the popup by pressing the escape key or clicking outside of the popup.</div>
		        	</fieldset>
		        	</td>
		    	</tr>
		    	
		    	<tr valign="top">
		        	<th scope="row">How many days until popup shows again?</th>
		        	<td><fieldset>
						<input class="field" name="spu_option[days-no-click]" type="text"  value="<?php echo $options['days-no-click']; ?>" />
		        
						<div class="description">This only applies when the user DONT click any of the social icons and close the popup</div>
		        	</fieldset>
		        	</td>
		    	</tr>
		    	
		    	<tr valign="top">
		        	<th scope="row">Opacity</th>
		        	<td><fieldset>
						<input class="field" name="spu_option[bg_opacity]" type="text"  value="<?php echo $options['bg_opacity']; ?>" />
		        
						<div class="description">Change background opacity. Default is 0.65</div>
		        	</fieldset>
		        	</td>
		    	</tr>
		    		
					
			<tr valign="top">
		        	<th scope="row"><h2>Support this plugin</h2></th>
			</tr>
			    	
			<tr valign="top">
		        	<th scope="row"><p>Please support this plugin with any of these options :D.</p></th>
			</tr>
		
			<tr valign="top">
		        	<th scope="row"><strong>Click here to add the powered by link</strong></th>
		        	<td><fieldset>
		        		<input type="checkbox" name="spu_option[credits]"  value="true" <?php echo $options['credits'] && $options['credits'] == 'true' ? 'checked="checked"':'';?>>
		        	</fieldset>
		        	</td>
			</tr>
			
			<tr valign="top">
		        	<th scope="row"><strong>Or even better invite me a cofee</strong></th>
		        	<td><fieldset>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="3ZMTRLTEXQ9UW">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
		        	</fieldset>
		        	</td>
			</tr>

				</tbody>
			</table> 		
			
			
		
		<?php
		if (get_bloginfo('version') >= '3.1') { submit_button('Save Changes','secondary'); } else { echo '<input type="submit" name="submit" id="submit" class="button-secondary" value="Save Changes"  />'; }	
		echo '</div><div style="clear:both;"></div>';
	}
	
	
	function exec_plugin()
	{
	
		
		$defaults = array( 'enable' => 'false', 'title' => 'Please support the site','message' => 'By clicking any of these buttons you help our site to get better', 'facebook' => 'https://www.facebook.com/pages/Timersys/146687622031640', 'twitter'=>'chifliiiii','close' => 'true','close-advanced' => 'true', 'bg_opacity' => '0.65' );
		
		// Get all of the options required for the popup
		$options = get_option('spu_option',$defaults);
				
		// Only continue if the pop-up option is enabled...
		if($options['enable'] == 'true')
		{ ?>
				
					
				<script type="text/javascript">
				
						
					jQuery(document).ready(function() {		
									
						jQuery().delay('1500').socialPopUP({
							// Configure display of popup
							title: "<?php echo $options['title']; ?>",
							message: "<?php echo $options['message']; ?>",
							closeable: <?php echo $options['close']; ?>,
							advancedClose: <?php echo $options['close-advanced']; ?>,
							opacity: "<?php echo $options['bg_opacity']; ?>",
							fb_url: "<?php echo $options['facebook']; ?>",
							go_url: "<?php echo $options['google']; ?>",
							twitter_user: "<?php echo $options['twitter']; ?>",
							days_no_click: "<?php echo $options['days-no-click']; ?>",
							credits: <?php echo $options['credits'] == 'true' ? 'true' : 'false'; ?>
							
						});
						
					});
					
				</script>
	
		<?PHP
			
		} // End if enabled
		
} // End main function
	

	
		
	
	
	
	
} //end of class


$social_pop_up = new socialPopup();


	
	
	