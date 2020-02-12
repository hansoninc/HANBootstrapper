<?php
/**
 * Plugin Name: HanBootStrapper
 * Plugin URI: https://github.com/hansoninc/HANBootstrapWordPress
 * Description: This plugin works in conjunction with the HanBootStrapper JS. This plugin installs the hbs.js and allows developers to hook in Javascript controllers based on pages, sections and actions.
 * Version: 4.0.1
 * Author: Mike Louviere / HansonInc
 * Author URI: http://hansoninc.com
 * License: GPL2
 */
/*  Copyright 2014 Mike Louviere / HansonInc  (email : mike.louviere@hansoninc.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}

if ( !defined( 'HBS_URL' ) ) 
{
	define( 'HBS_URL', plugin_dir_url( __FILE__ ) );
}

//Include dependencies and activate admin_toolbar shortcut
require_once ( 'helpers/helpers.php' );
include ( 'includes/admin_toolbar.php' );

class HanBsSettings {
	
	/**
	* Holds the values to be used in the fields callbacks
	*/
	private $options;

	/**
	* Start up
	*/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'han_bs_pluginpage' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_init', array( $this, 'enque_plugin_assets' ) );
	}	

	/**
	 * Add options page
	 */
	public function han_bs_pluginpage() {

		global $current_user;
        wp_get_current_user();
		$logged_in = $current_user->user_login;

		$hanbs_option_group = get_option('hanbs_option_name');
		$logged_in_user_option = "data-user-{$logged_in}";
		$user_access = $hanbs_option_group[$logged_in_user_option];

		//Plugin restricts access to settings page to users not specifically provided access within settings page itself
		//Until plugin has been provided explicit access to users, all Admins are provided rights
		if ( $hanbs_option_group['hanbs-has-access'] ) 
		{

			//Check logged in state, access level
			if ($logged_in == $user_access) {
				add_menu_page(
					'Settings Admin',
					'HanBootStrapper for WordPress',
					'manage_options',
					'hanbs-namespace-admin',
					array( $this, 'create_admin_page' ),
					HBS_URL.'images/icons/hbs-hanson-logo-white.png',
					99
				);
			}
		} 
		else 
		{
			//Otherwise just provide access to everyone since it's the first time
			add_menu_page(
				'Settings Admin',
				'HanBootStrapper for WordPress',
				'manage_options',
				'hanbs-namespace-admin',
				array( $this, 'create_admin_page' ),
				HBS_URL.'images/icons/hbs-hanson-logo-white.png',
				99
			);
		}
	}

	/**
	* Enque Plugin Assets
	*/
	public function enque_plugin_assets() {	
		if ( is_admin() ) 
		{
			function is_edit_page($new_edit = null)
			{
			    global $pagenow;

				//Assure we're in the Admin area
			    if (!is_admin()) return false;

			    if ( $new_edit == "edit" )
			    {
			        return in_array( $pagenow, array( 'post.php' ) );
			    }
			    elseif ( $new_edit == "new" ) //check for new post page 
			    {
			        return in_array( $pagenow, array( 'post-new.php' ) );
			    }
			    else {
			        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
			    }
			}

			//Enque dependencies when plugin settings page OR post/page
            if ( is_edit_page() || is_edit_page('new') || is_plugin_page() ) 
            {
            	if ( $_GET["page"] == "hanbs-namespace-admin" && !$_GET["post"] ) 
            	{
            		wp_enqueue_style( 'hbs-css', HBS_URL . 'css/hbs.css' );
            		wp_enqueue_script( 'jquery-hbs', HBS_URL  . '/js/jquery.1.11.0.js', null, null, true );
					wp_enqueue_script( 'hbs-validation', HBS_URL  . '/js/hbs_validation.js', 'jquery-hbs', '9880649384aea9f1ee166331c0a30daa', true );
            	} 
            	else 
            	{
					wp_enqueue_style( 'hbs-css', HBS_URL . 'css/hbs.css' );
				}
			}
		}
	}

	/**
	 * Build HanBootStrapper for WordPress Options & Settings Page
	 */
	public function create_admin_page() 
	{
		// Set options class property to current options
		$this->options = get_option( 'hanbs_option_name' );

		//Build UI HTML for Plugin Options
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<form method="post" action="options.php">
			<?php
				// Output hidden fields required for setting updates
				settings_fields( 'hanbs_options_group' );
				do_settings_sections( 'hanbs-namespace-admin' );
				do_settings_sections( 'hanbs-cpts-admin' );
				?>
				<p class="nomargin"><strong>The code below must be added to the activated theme's body tag.</strong></p>
				<small class="footnote">Note: This is not necessary when using hantheme.</small>
				<textarea class="hbs-funk" readonly>data-section="&lt;?php get_data_section(); ?&gt;" data-page="&lt;?php get_data_page(); ?&gt;"</textarea>
				<div class="clearfix">
					<a href="https://github.com/hansoninc/HANBootstrapWordPress" target="_blank">Issues? View the GitHub!</a> | <a href="http://jira.hansoninc.com/browse/HANINT/" target="_blank">Submit a Bug</a> | <a href="http://intranet.hansoninc.local/display/engtools/HAN+Bootstrap+Plugin" target="_blank">Get Help</a>
				</div>

				<?php
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register Plugin Settings
	 */
	public function page_init() 
	{
		register_setting(
			'hanbs_options_group', // Option group
			'hanbs_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'general', // ID
			'General Settings', // Title
			array( $this, 'print_section_info' ), // Callback
			'hanbs-namespace-admin' // Page
		);

		add_settings_field(
			'hanbs_namespace', // ID
			'Project Namespace:', // Title
			array( $this, 'hanbs_namespace_callback' ), // Callback
			'hanbs-namespace-admin', // Page
			'general' // Section
		);

		add_settings_field(
			'hanbs_assetpath', // ID
			'Javascript Path:', // Title
			array( $this, 'hanbs_assetpath_callback' ), // Callback
			'hanbs-namespace-admin', // Page
			'general' // Section
		);

		add_settings_field(
			'hanbs_debug', // ID
			'PHP Debugging:', // Title
			array( $this, 'hanbs_debug_callback' ),  // Callback
			'hanbs-namespace-admin',  // Page
			'general' // Settings
		);

		add_settings_section(
			'cpts', // ID
			'Custom Post Type Settings', // Title
			array( $this, 'hanbs_cpts_instruction' ), // Callback
			'hanbs-namespace-admin' // Page
		);

		$cpts_args = array(
			'public' => true
		);

		$cpts_output = 'objects';
		$post_types = get_post_types( $cpts_args, $cpts_output );

		foreach( $post_types as $post_type ) {
			$name = $post_type->name;
			$label = $post_type->label;

			$args = array (
	            'name' 	=> $post_type->name,
				'label' => $post_type->label
			);

			$callback = array ( $this, 'hanbs_cpts_callback' );

			//If regular WP Page or custom post, post edit screen add options fields to UI
			if ($name != 'page' && $name != 'attachment') 
			{
				add_settings_field(
					$name,
					$label,
					$callback,
					'hanbs-namespace-admin',
					'cpts',
					$args
				);
			}
		}

		//Add settings to control plugin level user access rights
		add_settings_section(
			'users', // ID
			'User Access', // Title
			array( $this, 'hanbs_users_instruction' ), // Callback
			'hanbs-namespace-admin' // Page
		);

		$admin_users = get_users('role=administrator');
		
		//Loop through each admin user and add settings to each user table
		foreach ( $admin_users as $admin ) 
		{
			$args = array (
				'user_id' => $admin->ID,
	            'user_login' 	=> $admin->user_login,
	            'display_name' => $admin->display_name,
	            'user_email' => $admin->user_email,
				'user_nicename' => $admin->user_nicename,
				'user_role' => $admin->roles[0]
			);

			$callback = array ( $this, 'hanbs_users_callback' );
			add_settings_field(
				$admin->ID,
				$admin->display_name,
				$callback,
				'hanbs-namespace-admin',
				'users',
				$args
			);
		}
	}

	/**
	* Sanitize each setting field as needed
	*
	* @param array $input Contains all settings fields as array keys
	*/
	public function sanitize( $input ) 
	{
		$new_input = array();

		if ( isset( $input['hanbs_namespace'] ) ) 
		{
			$new_input['hanbs_namespace'] = sanitize_text_field( $input['hanbs_namespace'] );
		}

		if ( isset( $input['hanbs_assetpath'] ) ) 
		{
			$new_input['hanbs_assetpath'] = sanitize_text_field( $input['hanbs_assetpath'] );
		}

		if ( isset( $input['hanbs_debug'] ) ) 
		{
			$new_input['hanbs_debug'] = sanitize_text_field( $input['hanbs_debug'] );
		}

		if ( isset( $input['hanbs-has-access'] ) ) 
		{
			$new_input['hanbs-has-access'] = sanitize_text_field( $input['hanbs-has-access'] );
		}

		//Sanitize CPT fields
		$cpts_args = array(
			'public' => true
		);

		$cpts_output = 'objects'; // names or objects
		$post_types = get_post_types( $cpts_args, $cpts_output );

		foreach( $post_types as $post_type ) 
		{
			//Page/Post level data-section, data-page fields
			$name = $post_type->name;
			if( isset( $input['data-section-'.$name.''] ) ) 
			{
				$new_input['data-section-'.$name.''] = sanitize_text_field( $input['data-section-'.$name.''] );
			}

			if( isset( $input['data-page-'.$name.''] ) ) 
			{
				$new_input['data-page-'.$name.''] = sanitize_text_field( $input['data-page-'.$name.''] );
			}

		}

		$users = get_users();
		foreach ( $users as $user ) 
		{
			//Data Section Fields
			$login = $user->user_login;
			$data_user_login = "data-user-{$login}";

			if ( isset( $input[$data_user_login] ) ) 
			{
				$new_input[$data_user_login] = sanitize_text_field( $input[$data_user_login] );
			}
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() 
	{
		print '<p class="intro">Namespace should match the activated themes js controller directory name.</p>'; ?>
			<img class="plugin-hanson-logo" src="<?php echo HBS_URL. 'images/icons/hbs-hanson-logo-lg.png'; ?> ">
		<?php
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function hanbs_namespace_callback() 
	{
		printf(
			'<input type="text" id="hanbs_namespace" name="hanbs_option_name[hanbs_namespace]" value="%s" maxlength="3"/>',
			isset( $this->options['hanbs_namespace'] ) ? esc_attr( $this->options['hanbs_namespace']) : 'HAN'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function hanbs_assetpath_callback() 
	{
		printf(
			'<input type="text" id="hanbs_assetpath" name="hanbs_option_name[hanbs_assetpath]" value="%s" maxlength="100"/> <small>This path is theme root relative.</small>',
			isset( $this->options['hanbs_assetpath'] ) ? esc_attr( $this->options['hanbs_assetpath']) : '/assets/js/'
		);
	}

	public function hanbs_debug_callback() 
	{
		$hanbs_option_group = get_option('hanbs_option_name');
		$debug_val = $hanbs_option_group['hanbs_debug'];
		?>
			<input type="radio" id="hanbs_debug_yes" name="hanbs_option_name[hanbs_debug]" value="On" <?php if ($debug_val == 'On') echo 'checked'; ?>><label for="hanbs_debug_yes" class="hbs-label on">On</label><input type="radio" id="hanbs_debug_off" name="hanbs_option_name[hanbs_debug]" value="Off" <?php if ($debug_val == 'Off' || $debug_val != 'On') echo 'checked'; ?>><label for="hanbs_debug_off" class="hbs-label off">Off</label>
		<?php
	}

	/**
	 * Custom Post Type Instruction
	 */
	public function hanbs_cpts_instruction() 
	{	
		print '<p>Set the default data-section and data-page attributes for custom post types. You can override these per-post if needed on the edit post screen.</p>';	
	}

	/**
	 * Custom Post Types Field Settings
	 */
	public function hanbs_cpts_callback(array $args) 
	{	
		$name = $args['name'];

		//Plugin Page Settings - Data Section
		$data_section_name = "data-section-{$name}";
		printf(
			"<div class='cpt-field-wrap'><span class='field-label-prefix'>Data Section: </span><input type='text' id='{$data_section_name}' name='hanbs_option_name[{$data_section_name}]' value='%s'/></div>",
			isset( $this->options[$data_section_name] ) ? esc_attr( $this->options[$data_section_name]) : ''
		);

		//Plugin Page Settings - Data Page
		$data_page_name = "data-page-{$name}";
		printf(
			"<div class='cpt-field-wrap'><span class='field-label-prefix'>Data Page: </span><input type='text' id='{$data_page_name}' name='hanbs_option_name[{$data_page_name}]' value='%s'/></div>",
			isset( $this->options[$data_page_name] ) ? esc_attr( $this->options[$data_page_name]) : ''
		);	

		//When settings page is saved via POST:
		//Update all individual posts data-section and data-page values to use plugin/setting level options (can be overwritten at individual page/post level)
		if (isset($_GET['settings-updated']) && $_GET['settings-updated']) 
		{
  			
			$args = array( 'post_type' => $name, 'posts_per_page' => -1, );
			$loop = new WP_Query( $args );

			$hanbs_option_group = get_option('hanbs_option_name');

			$data_section_name = "data-section-{$name}";
			$hanbs_datasection = $hanbs_option_group[$data_section_name];

			$data_page_name = "data-page-{$name}";
			$hanbs_datapage = $hanbs_option_group[$data_page_name];

			while ( $loop->have_posts() ) : $loop->the_post();
				$post_id = get_the_ID();

				if ( $post_id ) {
				    // Insert/Update Post Meta ([data-section, data-page])
				    update_post_meta($post_id, '_hanbs_datasection', $hanbs_datasection);
				    update_post_meta($post_id, '_hanbs_datapage', $hanbs_datapage);
				}
			endwhile;

			//Display green-check indicator for each field group updated
			echo '<div class="saved-flag wp-menu-image dashicons-before dashicons-yes"></div>';
		}	
	}

	/**
	 * User Access Instruction
	 */
	public function hanbs_users_instruction() 
	{	
		print '<p>Select which users are permitted access to HanBootStrapper settings and features.';
		print '<div class="alert danger hide" id="user-access-notice">You must provide access to at least one user.</div>';
		print '<input type="hidden" name="hanbs_option_name[hanbs-has-access]" value="true">';
	}

	/**
	 * User Access Settings
	 */
	public function hanbs_users_callback(array $args) 
	{

		$login = $args['user_login'];
		$role = $args['user_role'];
		$hanbs_option_group = get_option('hanbs_option_name');

		$data_user_login = "data-user-{$login}";
		$user_access = $hanbs_option_group[$data_user_login];
		
		global $current_user;
		wp_get_current_user(); 

        $logged_in = $current_user->user_login;
        $logged_in_status = "";

        //Used for UI purposes (showing tooltips regarding invidual user options)
		if ($logged_in == $login) {
			$logged_in_status = "logged-in";
		} else {
			$logged_in_status = "not-logged-in";
		}

		?>
		
		<?php if ( $logged_in == $login ) : ?>
			<label for="data-user-<?php echo $login; ?>">
				<input data-role="<?php echo $role; ?>" data-loggedin="true" class="user-access-option <?php echo $logged_in_status; ?>" type="checkbox" id="data-user-<?php echo $login; ?>" name="hanbs_option_name[data-user-<?php echo $login; ?>]" value="<?php echo $login; ?>" checked readonly />
				<span class="checkbox-proxy"></span>
			</label>
			<p class="logged-in-helper">You cannot remove access for the currently logged in user.</p>
		<?php else: ?>
			<label for="data-user-<?php echo $login; ?>">
				<input data-role="<?php echo $role; ?>" class="user-access-option <?php echo $logged_in_status; ?>" type="checkbox" id="data-user-<?php echo $login; ?>" name="hanbs_option_name[data-user-<?php echo $login; ?>]" value="<?php echo $login; ?>" <?php if ($user_access == $login) { echo 'checked'; } ?> />  
				<span class="checkbox-proxy"></span>
			</label>
		<?php endif; ?>
		
		<?php
	}
}

if ( is_admin() ) 
{	
	require_once ( 'includes/pages_posts_options.php' );

	/**
	 * Instantiate core HBS plugin
	 */
	$hanbs_settings_page = new HanBsSettings();
} 
else
{
	require ( 'includes/pages_posts_frontend.php' );
}