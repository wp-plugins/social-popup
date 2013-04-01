<?php
/**
 Plugin Name: Social PopUP - Google+, Facebook and Twitter popup
 Plugin URI: http://www.masquewordpress.com/plugins/social-popup/
 Version: 1.4.1
 Description: This plugin will display a popup or splash screen when a new user visit your site showing a Google+, twitter and facebook follow links. This will increase you followers ratio in a 40%. Popup will be close depending on your settings. Check readme.txt for full details.
 Author: Damian Logghe
 Author URI: http://www.masquewordpress.com
 */

class socialPopup 
{

	var $_options;
	var $_credits;
	var $_defaults;
	
	function __construct() {
		
        
        add_action( 'admin_init', array(&$this,'register_options' ));
		
		add_action( 'admin_menu',array(&$this,'register_menu' ) );
		
		add_action( 'init',array(&$this,'load_scripts' ) );	
		
		add_action( 'wp_head',array(&$this,'exec_plugin' ) );	

		add_action( 'wp_footer',array(&$this,'print_pop' ) );	
		
		add_action('wp_ajax_spu_reset', array(&$this,'spu_reset' ));
		add_action('wp_ajax_nopriv_spu_reset', array(&$this,'spu_reset' ));
		
		$this->_defaults = array( 'enable' => 'true',  'facebook' => 'https://www.facebook.com/pages/Timersys/146687622031640', 'twitter'=>'chifliiiii','google' => '','close' => 'true','close-advanced' => 'true', 'bg_opacity' => '0.65' , 'days-no-click' => '99', 'where' => array('everywhere'=>'true' ), 'template' => '<div id="spu-title">Please support the site</div>
<div id="spu-msg-cont">
     <div id="spu-msg">
     By clicking any of these buttons you help our site to get better </br>
     <h3>Twitter {twitter} </h3>
    <h3> Facebook {facebook} </h3>
     <h3>Google+ {google} </h3>
     </div>
    <div class="step-clear"></div>
</div>', 'css' =>'.spu-button {
	margin-left:15px;
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
		$options = get_option('spu_option',$this->_defaults);
		
		$this->_options = $options;
		
		$defaults = array('credits' => 'false');
		$credits = get_option('spu_credit_option',$defaults);
		
		$this->_credits = $credits;
	}
	
	
	
	
		
	 	//function that register options 
 	 function register_options()
	{
		register_setting( 'spu_options', 'spu_option' );

		register_setting( 'spu_credit_options', 'spu_credit_option' );
		
		
	}
	
		//function that register the menu link in the settings menu	and editor section inside the option page
	 function register_menu()
	{
		add_options_page( 'Social PopUP', 'Social PopUP', 'manage_options', 'social-pop-up',array(&$this, 'options_page') );
		
		add_settings_section('bsbm_forms', 'Settings', array(&$this, 'style_box_form'), 'spu_style_form');
		
		add_settings_section('spu_support', 'Support the plugin', array(&$this, 'spu_support_form'), 'spu-support');
	}
	
	//Function that load the scripts
	function load_scripts()
	{
		if(!is_admin())
		{
			
			wp_enqueue_script('spu-fb', 'http://connect.facebook.net/en_US/all.js#xfbml=1', array('jquery'),FALSE,FALSE);
			wp_enqueue_script('spu-tw', 'http://platform.twitter.com/widgets.js', array('jquery'),FALSE,FALSE);
			wp_enqueue_script('spu-go', 'https://apis.google.com/js/plusone.js', array('jquery'),FALSE,FALSE);
			wp_enqueue_script('spu', plugins_url( 'spu.js' , __FILE__ ),array('jquery'),'1.1');
			wp_enqueue_style('spu-css', plugins_url( 'spu.css' , __FILE__ ));
		}
	}
	
	 //function that display the options page
	 function options_page()
	{
		?>
		<div class="metabox-holder">
	    
	    <?php screen_icon(); echo "<h2>". __( 'Social PopUP' ) ."</h2>"; ?>
	 
		    <div class="postbox" style="float:left;width:700px;margin-right:30px;">
		    
		    	<?php do_settings_sections( 'spu_style_form' ); ?>
		    
		    </div>
	    
		
	    
		    <div class="postbox" style="float:right;width:300px;margin-right:30px;">
		    	
		    	<?php do_settings_sections( 'spu-support' ); ?>
		   	
		   	</div>
	   	</div>
	<?
	
	}
	
	
	
	
	//function that display the textarea editor form
	function style_box_form()
	{
		
		
		$options = $this->_options;
		$defaults = $this->_defaults;
		?>
		<style type="text/css">
	    	.postbox input.field,.postbox textarea  { width:500px;}div.inside ul li {list-style: square;margin-left: 20px;}
	    </style>
	    <script type="text/javascript">
	    	jQuery(document).ready(function($){

	    		$('.reset_html').click(function(e){
	    				e.preventDefault(); 
	    				$.post('<?php echo site_url('wp-admin/admin-ajax.php');?>',
	    					   { action: 'spu_reset', what:'html'}, 
	    					   function(response){
			    					$('#html_area').val(response);
			    			   }
	    						) 	
	    				
	    		});					
	    		$('.reset_css').click(function(e){ 
	    		e.preventDefault();
	    				$.post('<?php echo site_url('wp-admin/admin-ajax.php');?>',
	    					   { action: 'spu_reset', what:'css'}, 
	    					   function(response){
			    					$('#css_area').val(response);
			    			   }
	    						) 	
	    				return false;
	    		});					
	    	/*	$('.reset_css').click(function(){ $('#css_area').text('<?php echo $defaults['css'];?>'); return false; });*/
	    	});
	    </script>
	   	<form method="post" action="options.php" >
	 
	    <?php settings_fields( 'spu_options' );?>
			<div class="inside"><div class="intro"><p>Please add settings for the Social PopUP.</p></div> 
				
			<table class="form-table">
				<tbody>
				
				<tr valign="top">
		        	<th scope="row" colspan="2"><h2>Main Settings</h2></th>
		        	</td>
		    	</tr>	
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
		        	<th scope="row" colspan="2"><h2>Styling & Messages</h2></th>
		        	</td>
		    	</tr>	
		    	
		    	<tr valign="top">
			        <th scope="row">Template</th>
			        <td><fieldset>
						<textarea  class="textarea" name="spu_option[template]" cols="" rows="9" id="html_area" ><?php echo isset($options['template']) && $options['template'] != '' ? $options['template'] : $defaults['template'] ; if( isset($_REQUEST['reset_html'])) echo $defaults['template'];?></textarea>
			                        
						<div class="description">Edit the default template. Add or remove buttons with {twitter}, {facebook}, {google} and edit or add your custom HTML. <button class="reset_html" value="reset_html">RESET HTML CODE</button></div>
			        </fieldset>
			        </td>
		    	</tr>
		    	
		    			   
		    	<tr valign="top">
			        <th scope="row">Css Rules</th>
			        <td><fieldset>
			        	<textarea name="spu_option[css]" cols="" rows="9" id="css_area" ><?php echo isset($options['css']) && $options['css'] != '' ? $options['css'] : $defaults['css']; ?></textarea>
			        
			        	<div class="description">This are some rules for the default template. Feel free to create yours.<button class="reset_css">RESET CSS CODE</button></div>
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
		        	<th scope="row" colspan="2"><h2>Avanced</h2>Some advanced options</th>
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
						<input class="field" name="spu_option[days-no-click]" type="text"  value="<?php echo isset($options['days-no-click']) && $options['days-no-click'] != '' ? $options['days-no-click']: $defaults['days-no-click']; ?>" />
		        
						<div class="description">When a user closes the popup he won't see it again until all these days pass</div>
		        	</fieldset>
		        	</td>
		    	</tr>
		    	
		    	
		    		
		    
		    	<tr valign="top">
		        	<th scope="row" colspan="2"><h2>Display rules</h2>Be careful, some rules overrides others</th>
		        	
		    	</tr>	
		    	<tr valign="top">
		    		<th scope="row">Show in:</th>
		    		<td><fieldset>
						<input type="checkbox" value="true" name="spu_option[where][home]" <?php echo isset($options['where']['home']) && $options['where']['home'] == 'true' ? 'checked="checked"':'';?>/> Home <br/>
						<input type="checkbox" value="true" name="spu_option[where][pages]" <?php echo isset($options['where']['pages']) && $options['where']['pages'] == 'true' ? 'checked="checked"':'';?>/> Pages <br/>
						<input type="checkbox" value="true" name="spu_option[where][posts]" <?php echo isset($options['where']['posts']) && $options['where']['posts'] == 'true' ? 'checked="checked"':'';?>/> Posts <br/>
						<input type="checkbox" value="true" name="spu_option[where][everywhere]" <?php echo isset($options['where']['everywhere']) && $options['where']['everywhere'] == 'true'  ? 'checked="checked"':'';
							if( !isset($options['where']) ) echo 'checked="checked"';
						?>/> Everywhere<br/>
						
			            <div class="description">Where to show popup.</div>
		    		</fieldset>
		    		</td>
		    	</tr>
		    	<tr valign="top">
		    		<th scope="row">Show to:</th>
		    		<td><fieldset>
						<input type="checkbox" value="logged" name="spu_option[show_to][]" <?php echo !isset($options['show_to']) || in_array('logged', $options['show_to']) == 'true' ? 'checked="checked"':'';?>/> Logged in users <br/>
						<input type="checkbox" value="nologged" name="spu_option[show_to][]" <?php echo !isset($options['show_to']) || in_array('nologged', $options['show_to']) == 'true' ? 'checked="checked"':'';?>/> Non Logged Users <br/>
				      
		    		</fieldset>
		    		</td>
		    	</tr>
		    	<tr valign="top">
		    		<th scope="row">User Roles:</th>
		    		<td><fieldset>
		    			<?php 
		    			$roles =  get_editable_roles();
		    			
		    			foreach ($roles as $rol) :
		    			?>
		    			<input type="checkbox" value="<?php echo $rol['name'];?>" name="spu_option[roles][]" <?php echo !isset($options['roles']) || in_array($rol['name'],$options['roles'])? 'checked="checked"':'';?>/> <?php echo $rol['name'];?> <br/>

		    			<?php
		    			endforeach;
		    			?>
		    			<div class="description">Choose which user roles will see the popup.( Logged in users must be checked )</div>
		    		</fieldset>
		    		</td>
		    	</tr>
		    	<tr valign="top">
		    		<th scope="row">Show only IF:</th>
		    		<td><fieldset>
						<input type="checkbox" value="never_commented" name="spu_option[show_if][]" <?php echo isset($options['show_if']) && in_array('never_commented', $options['show_if']) == 'true' ? 'checked="checked"':'';?>/> The user has never left a comment <br/>
						<input type="checkbox" value="search_engine" name="spu_option[show_if][]" <?php echo isset($options['show_if']) && in_array('search_engine', $options['show_if']) == 'true' ? 'checked="checked"':'';?>/> The user arrived via a search engine. <br/>
						<input type="checkbox" value="internal" name="spu_option[show_if][]" <?php echo isset($options['show_if']) && in_array('internal', $options['show_if']) == 'true' ? 'checked="checked"':'';?>/> The user did not arrive on this page via another page on your site. <br/>
						The user arrived via the following referrer : <input type="text" value="<?php echo isset($options['show_if']['referrer']) ? $options['show_if']['referrer']:'';?>" name="spu_option[show_if][referrer]" /><br/>
				        The user is on a certain URL (enter one URL per line) 
						<textarea name="spu_option[show_if][onurl]"><?php echo isset($options['show_if']['onurl']) ? $options['show_if']['onurl']:'';?></textarea><br/>
				        
				        The user is NOT on a certain URL (enter one URL per line) 
						<textarea name="spu_option[show_if][notonurl]"><?php echo isset($options['show_if']['notonurl']) ? $options['show_if']['notonurl']:'';?></textarea><br/>
				      
		    		</fieldset>
		    		</td>
		    	</tr>		    	
				<tr valign="top">
		        	<th scope="row" colspan="2"><h2>Debugging</h2></th>
		        	</td>
		    	</tr>	
		    	
		    	<tr valign="top">
		        	<th scope="row">Delete Cookies</th>
		        	<td><fieldset>
						<button class="button" onclick="return clearCookie('spushow');">Delete Cookies</button>
						<script type="text/javascript">
							function clearCookie(name, domain, path){
							    var domain = domain || document.domain;
							    var path = path || "/";
							    document.cookie = name + "=; expires=" + +new Date + ";  path=/";
							    alert('Cookies deleted!');
							    return false;
							};
							</script>
							<div class="description">If you already closed the popup and don't want to wait for <?php echo $options['days-no-click']; ?> days, click this button to see the popup again.</div>
		        		</fieldset>
		        	</td>
		    	</tr>
		    	
				</tbody>
			</table> 		
			
			
		
		<?php
		if (get_bloginfo('version') >= '3.1') { submit_button('Save Changes','secondary'); } else { echo '<input type="submit" name="submit" id="submit" class="button-secondary" value="Save Changes"  />'; }	?>
		</div><div style="clear:both;"></div>
		</form>
		<?php
	}
	
	
	function exec_plugin()
	{
	
		$print_script = false;
		$options = $this->_options;
		
		// Only continue if the pop-up option is enabled...
		if($options['enable'] == 'true')
		{ 
			
			//if show everywhere i print script
			if( isset($options['where']['everywhere']) && $options['where']['everywhere'] == 'true' )
			{
			
				
				$print_script = true;
			}
			else
			{
				if( isset($options['where']['posts']) && $options['where']['posts'] == 'true' )
				{
				
					if ( is_single() || is_home() )
					{
						$print_script = true;
					}
				}
				if( isset($options['where']['pages']) && $options['where']['pages'] == 'true' )
				{
				
					if ( is_page() )
					{
						$print_script = true;
					}
				}
				if( isset($options['where']['home']) && $options['where']['home'] == 'true' )
				{
				
					if ( is_front_page() )
					{
						$print_script = true;
					}
				}
				
			}
			
			if (isset($options['show_to']) && in_array('logged', $options['show_to']))
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
			
			if (isset($options['show_to']) && in_array('nologged', $options['show_to']) && !in_array('logged', $options['show_to']))
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
			elseif (isset($options['show_to']) && in_array('nologged', $options['show_to']) && in_array('logged', $options['show_to']))
			{
				$print_script = true;
			}
			
			if( isset($options['roles']) && in_array('logged', $options['show_to']) && is_user_logged_in() )
			{
				foreach( $options['roles'] as $rol )
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
			if( isset($options['show_if']) && in_array('never_commented', $options['show_if']) )
			{ 
				if ( !isset($_COOKIE['comment_author_'.COOKIEHASH]) &&  $print_script == true ) {
					$print_script = true;
				} else {
					$print_script = false;
				}
			}	

			if( isset($options['show_if']) && in_array('search_engine', $options['show_if']) &&  $print_script == true )
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
			if( isset($options['show_if']) && in_array('internal', $options['show_if']) &&  $print_script == true )
			{ 
				$internal = str_replace(array('http://','https://'),'',site_url());
				if($this->referrer_matches(addcslashes($internal,"/"))) {
					$print_script = false;
				}
				
			}
			if( isset($options['show_if']['referrer']) && $options['show_if']['referrer'] != '' &&  $print_script == true )
			{ 
				
				if(!$this->referrer_matches(addcslashes($options['show_if']['referrer'],"/"))) {
					$print_script = false;
				}
				
			}
			if( isset($options['show_if']['onurl']) && $options['show_if']['onurl'] != '' &&  $print_script == true )
			{
				$array_urls =  explode("\n", $options['show_if']['onurl']);
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
			
			if( isset($options['show_if']['notonurl']) && $options['show_if']['notonurl'] != '' &&  $print_script == true )
			{
				$array_urls =  explode("\n", $options['show_if']['notonurl']);
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
				
			
	
			
	} // End if enabled
	
	if( $print_script ) $this->print_script();
		
} // End main function
	
//function to print script

function print_script()
{
	$options = $this->_options;
	$credit = $this->_credits;
?>				
				<style type="text/css">
				<?php echo $options['css'];?>
				</style>			
				<script type="text/javascript">
					jQuery(document).ready(function() {		
									
						jQuery().delay('1500').socialPopUP({
							// Configure display of popup
							advancedClose: <?php echo $options['close-advanced']; ?>,
							opacity: "<?php echo $options['bg_opacity']; ?>",
							days_no_click: "<?php echo $options['days-no-click']; ?>"
						});
						
					});
					
				</script>
				
<?php
}

//function that prints support part
function spu_support_form()
{
		$credits = $this->_credits;
		?>

		<div class="inside"><div class="intro"><p><strong>If you enjoyed, please support this plugin:</strong></p></div>
		
		<ul>
			<li>
				<a href="http://wordpress.org/extend/plugins/social-popup/">Rate the plugin 5★ on WordPress.org</a>
			</li>
			<li>Or even better invite me a coffee :
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="3ZMTRLTEXQ9UW">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>

			</li>
		</ul>
		
		</div><div style="clear:both;"></div>
		<?
}		

//function that prints pop
function print_pop()
{		
	$options = $this->_options;
	$credits = $this->_credits;
	
	
	$socials = array(
		"google" => '<div class="spu-button spu-google"><div class="g-plusone" data-callback="googleCB" data-action="share" data-annotation="bubble" data-height="24" data-href="' . $options['google'] . '"></div></div>',
  		"twitter" => '<div class="spu-button spu-twitter"><a href="https://twitter.com/' . $options['twitter'] . '" class="twitter-follow-button" data-show-count="false" data-size="large">Follow Me</a></div>',
  		"facebook" => '<div class="spu-button spu-facebook"><div id="fb-root"></div><fb:like href="' . $options['facebook'] . '" send="false"  show_faces="false" data-layout="button_count"></fb:like></div>'
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
	
			echo isset($credits['credits']) && $credits['credits'] == 'on' ? '<div id="spu-bottom"><span style="font-size:10px;float: right;margin-top: -6px;">By <a href="http://www.masquewordpress.com">MasqueWordpress.com</a></span></div>':'';
	
	echo '</div>';
}
//function to check internal or external referer
function referrer_matches($check) {

	$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';

	if(preg_match( '/' . $check . '/i', $referer )) {
		return true;
	} else {
		return false;
	}

}
//function that returns current site url
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
//function to create and sanitize array for onurl
function sanitise_array($arrayin) {

	foreach( (array) $arrayin as $key => $value) {
		$arrayin[$key] = htmlentities(stripslashes($value) ,ENT_QUOTES, 'UTF-8');
	}

	return $arrayin;
}

//function that reset HTML AND CSS
function spu_reset() {

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
} //end of class


$social_pop_up = new socialPopup();
