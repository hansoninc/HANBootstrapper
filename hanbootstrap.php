<?php
/**
 * Plugin Name: HanBootStrapper for WordPress
 * Plugin URI: http://hansoninc.com
 * Description: This plugin works in conjunction with the internal HanBootStrapper JS. This plugin installs the hbs.js and allows developers to hook in controllers based on pages, sections and actions.
 * Version: 1.12.23.16
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !defined( 'HBS_URL' ) ) {
	define( 'HBS_URL', plugin_dir_url( __FILE__ ) );
}

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

		//print_r($current_user);

		$hanbs_option_group = get_option('hanbs_option_name');
		$user_access = $hanbs_option_group['data-user-'.$logged_in.''];

		

		/*echo $logged_in;
		echo $user_access;*/

		//If plugin has had specific users provided access, check to see which users and authorise if needed
		if ( $hanbs_option_group['hanbs-has-access'] ) {
			//Check to 
			if ($logged_in == $user_access) {
				add_menu_page(
					'Settings Admin',
					'HanBootStrapper',
					'manage_options',
					'hanbs-namespace-admin',
					array( $this, 'create_admin_page' ),
					HBS_URL.'images/icons/hbs-hanson-logo-white.png',
					99
				);
			}
		} else {
			//Otherwise just provide access to everyone since it's the first time
			add_menu_page(
				'Settings Admin',
				'HanBootStrapper',
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
		if ( is_admin() ) {
			function is_edit_page($new_edit = null){
			    global $pagenow;
			    //make sure we are on the backend
			    if (!is_admin()) return false;


			    if ($new_edit == "edit")
			        return in_array( $pagenow, array( 'post.php',  ) );
			    elseif($new_edit == "new") //check for new post page
			        return in_array( $pagenow, array( 'post-new.php' ) );
			    else //check for either new or edit
			        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
			}

			//if ( is_plugin_page() || is_edit_page() ) {  is_plugin_page is depr. with no replacement
            if ( is_edit_page() || is_plugin_page() ) {
				wp_enqueue_style( 'hbs-css', HBS_URL . 'css/hbs.css' );
				if ($_GET["page"] == "hanbs-namespace-admin") {
					wp_enqueue_script( 'jquery-hbs', HBS_URL  . '/js/jquery.1.11.0.js', null, null, true );
					wp_enqueue_script( 'hbs-validation', HBS_URL  . '/js/hbs_validation.js', 'jquery-hbs', '9880649384aea9f1ee166331c0a30daa', true );
				}
			}
            
            
		}
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'hanbs_option_name' );
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<form method="post" action="options.php">
		<?php
			// This prints out all hidden setting fields
			settings_fields( 'hanbs_options_group' );
			do_settings_sections( 'hanbs-namespace-admin' );
			do_settings_sections( 'hanbs-cpts-admin' );
			?>
			<p class="nomargin"><strong>The code below must be added to the activated theme's body tag.</strong></p>
			<small class="footnote">Note: This is not necessary when using hantheme.</small>
			<textarea class="hbs-funk" readonly>data-section="&lt;?php get_data_section(); ?&gt;"
data-page="&lt;?php get_data_page(); ?&gt;"</textarea>
			<div class="clearfix">
				<a href="http://jira.hansoninc.com/browse/HANINT/" target="_blank">Issues? Submit a Ticket</a> | <a href="http://intranet.hansoninc.local/display/engtools/HAN+Bootstrap+Plugin" target="_blank">Get Help</a>
			</div>

			<?php
			submit_button();
		?>
		</form>
	</div>
	<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
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

		$cpts_output = 'objects'; // names or objects

		$post_types = get_post_types( $cpts_args, $cpts_output );

		foreach( $post_types as $post_type ) {
			$name = $post_type->name;
			$label = $post_type->label;
			$args = array (
	            'name' 	=> $post_type->name,
				'label' => $post_type->label
			);
			$callback = array ( $this, 'hanbs_cpts_callback' );

			if ($name != 'page' && $name != 'attachment') {
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

		add_settings_section(
			'users', // ID
			'User Access', // Title
			array( $this, 'hanbs_users_instruction' ), // Callback
			'hanbs-namespace-admin' // Page
		);

		$users =  get_users('role=administrator');
		/*echo '<pre>';
		print_r($admins);
		echo '</pre>';*/
		// Array of stdClass objects.
		foreach ( $users as $user ) {
			$args = array (
				'user_id' => $user->ID,
	            'user_login' 	=> $user->user_login,
	            'display_name' => $user->display_name,
	            'user_email' => $user->user_email,
				'user_nicename' => $user->user_nicename,
				'user_role' => $user->roles[0]
			);

			$callback = array ( $this, 'hanbs_users_callback' );
			add_settings_field(
				$user->ID,
				//$user->display_name.' <span class="light">('.$user->user_login.')</span>',
				$user->display_name,
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
	public function sanitize( $input ) {
		$new_input = array();

		if( isset( $input['hanbs_namespace'] ) ) {
			$new_input['hanbs_namespace'] = sanitize_text_field( $input['hanbs_namespace'] );
		}

		if( isset( $input['hanbs_debug'] ) ) {
			$new_input['hanbs_debug'] = sanitize_text_field( $input['hanbs_debug'] );
		}

		if( isset( $input['hanbs-has-access'] ) ) {
			$new_input['hanbs-has-access'] = sanitize_text_field( $input['hanbs-has-access'] );
		}

		//Sanitize CPT fields
		$cpts_args = array(
			'public' => true
		);

		$cpts_output = 'objects'; // names or objects
		$post_types = get_post_types( $cpts_args, $cpts_output );
		foreach( $post_types as $post_type ) {
			//Data Section Fields
			$name = $post_type->name;
			if( isset( $input['data-section-'.$name.''] ) ) {
				$new_input['data-section-'.$name.''] = sanitize_text_field( $input['data-section-'.$name.''] );
			}

			if( isset( $input['data-page-'.$name.''] ) ) {
				$new_input['data-page-'.$name.''] = sanitize_text_field( $input['data-page-'.$name.''] );
			}

		}

		$users = get_users();
		// Array of stdClass objects.
		foreach ( $users as $user ) {
			//Data Section Fields
			$login = $user->user_login;

			if( isset( $input['data-user-'.$login.''] ) ) {
				$new_input['data-user-'.$login.''] = sanitize_text_field( $input['data-user-'.$login.''] );
			}
		}

		return $new_input;

	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print '<p class="intro">Namespace should match the activated themes js controller directory name.</p>'; ?>
		<img class="plugin-hanson-logo" src="<?php echo HBS_URL. 'images/icons/hbs-hanson-logo-lg.png'; ?> ">
		<?php
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function hanbs_namespace_callback() {
		printf(
			'<input type="text" id="hanbs_namespace" name="hanbs_option_name[hanbs_namespace]" value="%s" maxlength="3"/>',
			isset( $this->options['hanbs_namespace'] ) ? esc_attr( $this->options['hanbs_namespace']) : 'HAN'
		);
	}

	public function hanbs_debug_callback() {
		$hanbs_option_group = get_option('hanbs_option_name');
		$debug_val = $hanbs_option_group['hanbs_debug'];
		?>
			<input type="radio" id="hanbs_debug_yes" name="hanbs_option_name[hanbs_debug]" value="On" <?php if ($debug_val == 'On') echo 'checked'; ?>><label for="hanbs_debug_yes" class="hbs-label on">On</label><input type="radio" id="hanbs_debug_off" name="hanbs_option_name[hanbs_debug]" value="Off" <?php if ($debug_val == 'Off') echo 'checked'; ?>><label for="hanbs_debug_off" class="hbs-label off">Off</label>
		<?php
	}

	/**
	 * Custom Post Type Instruction
	 */
	public function hanbs_cpts_instruction() {	
		print '<p>Set the default data-section and data-page attributes for custom post types. You can override these per-post if needed on the edit post screen.</p>';	
	}

	/**
	 * Custom Post Types Settings
	 */
	public function hanbs_cpts_callback(array $args) {	
		$name = $args['name'];
		//echo $name;

		printf(
			'<div class="cpt-field-wrap"><span class="field-label-prefix">Data Section: </span><input type="text" id="data-section-'.$name.'" name="hanbs_option_name[data-section-'.$name.']" value="%s"/></div>',
			isset( $this->options['data-section-'.$name.''] ) ? esc_attr( $this->options['data-section-'.$name.'']) : ''
		);

		printf(
			'<div class="cpt-field-wrap"><span class="field-label-prefix">Data Page: </span><input type="text" id="data-page-'.$name.'" name="hanbs_option_name[data-page-'.$name.']" value="%s"/></div>',
			isset( $this->options['data-page-'.$name.''] ) ? esc_attr( $this->options['data-page-'.$name.'']) : ''
		);	


		//Update all Posts with new data attributes
		if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
  			// Update All Posts DataAction and DataPage

			$args = array( 'post_type' => $name,'posts_per_page' => -1, );
			$loop = new WP_Query( $args );

			$hanbs_option_group = get_option('hanbs_option_name');
			$hanbs_datasection = $hanbs_option_group['data-section-'.$name.''];
			$hanbs_datapage = $hanbs_option_group['data-page-'.$name.''];

			while ( $loop->have_posts() ) : $loop->the_post();
				$post_id = get_the_ID();

				if ($post_id) {
				    // insert post meta
				    update_post_meta($post_id, '_hanbs_datasection', $hanbs_datasection);
				    update_post_meta($post_id, '_hanbs_datapage', $hanbs_datapage);
				}

			endwhile;

			//echo '<span class="saved-flag">Saved</span>';
			echo '<div class="saved-flag wp-menu-image dashicons-before dashicons-yes"></div>';
		}	
	}

	/**
	 * User Access Instruction
	 */
	public function hanbs_users_instruction() {	
		print '<p>Select which users are permitted access to HanBootStrapper settings and features.';
		print '<div class="alert danger hide" id="user-access-notice">You must provide access to at least one user.</div>';
		print '<input type="hidden" name="hanbs_option_name[hanbs-has-access]" value="true">';
	}

	/**
	 * User Access Settings
	 */
	public function hanbs_users_callback(array $args) {
		$login = $args['user_login'];
		$role = $args['user_role'];
		$hanbs_option_group = get_option('hanbs_option_name');
		$user_access = $hanbs_option_group['data-user-'.$login.''];
		
		global $current_user;
		wp_get_current_user(); 
        $logged_in = $current_user->user_login;

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
			<p class="logged-in-helper">You cannot remove acces for this user while logged in.</p>

		<?php else: ?>
			<label for="data-user-<?php echo $login; ?>">
				<input data-role="<?php echo $role; ?>" class="user-access-option <?php echo $logged_in_status; ?>" type="checkbox" id="data-user-<?php echo $login; ?>" name="hanbs_option_name[data-user-<?php echo $login; ?>]" value="<?php echo $login; ?>" <?php if ($user_access == $login) { echo 'checked'; } ?> />  
				<span class="checkbox-proxy"></span>
			</label>
		<?php endif; ?>
		
		<?php
	}
}

if ( is_admin() ) {
	$hanbs_settings_page = new HanBsSettings();
}

/*
 * Enque hbs.js
*/

function hbs_enqueue_script() {
	if ( ! is_admin() ) {
		wp_enqueue_script( 'hbs', HBS_URL . 'js/hbs.js', 'jquery' );
	}
}

add_action('wp_enqueue_scripts', 'hbs_enqueue_script');

/**
 * Adds a box to the main column on the Post and Page edit screens.
 */

function hanbs_add_custom_box() {
	global $current_user;
    wp_get_current_user(); 
	$logged_in = $current_user->user_login;

	$hanbs_option_group = get_option('hanbs_option_name');
	$user_access = $hanbs_option_group['data-user-'.$logged_in.''];

	if ($logged_in == $user_access) {
		//Get all pages, posts and custom post types for Meta Box
		$pagesandposts = get_post_types();
		//Remove attachment, acf etc
		$pagesandposts_sanitized = array_diff($pagesandposts, array("attachment", "acf", "revision", "nav_menu_item"));

		foreach ( $pagesandposts_sanitized as $pagesorpost ) {

			add_meta_box(
				'hanbs_sectionid',
				__( 'HanBootStrapper Settings', 'hanbs_textdomain' ),
				'hanbs_inner_custom_box',
				$pagesorpost
			);
		}
	} else if ( $logged_in == !$user_access ) {
		//Get all pages, posts and custom post types for Meta Box
		$pagesandposts = get_post_types();
		//Remove attachment, acf etc
		$pagesandposts_sanitized = array_diff($pagesandposts, array("attachment", "acf", "revision", "nav_menu_item"));

		foreach ( $pagesandposts_sanitized as $pagesorpost ) {

			add_meta_box(
				'hanbs_sectionid_hidden',
				__( 'HanBootStrapper Settings', 'hanbs_textdomain' ),
				'hanbs_inner_custom_box',
				$pagesorpost
			);
		}
	}
}
add_action( 'add_meta_boxes', 'hanbs_add_custom_box' );

/**
 * Add HBS to Admin Toolbar
 *
 */
function hanbs_customize_toolbar(){
	global $wp_admin_bar;

	$args = array(
		'id'     => 'HBS',
		'title' => __('<img src="'. HBS_URL . '/images/icons/hbs-hanson-logo-white.png" style="vertical-align:middle;margin-right:5px" alt="Han Bootstrapper" title="Han Bootstrapper" />HanBootStrapper' ),
		'href'   => '/wp-admin/admin.php?page=hanbs-namespace-admin'
	);

	global $current_user;
    wp_get_current_user(); 
	$logged_in = $current_user->user_login;

	$hanbs_option_group = get_option('hanbs_option_name');
	$user_access = $hanbs_option_group['data-user-'.$logged_in.''];

	if ($logged_in == $user_access) {

		$wp_admin_bar->add_menu( $args );

	}
}

add_action( 'wp_before_admin_bar_render', 'hanbs_customize_toolbar', 999 );	


function get_namespace_from_option() {
	$hanbs_option_group = get_option('hanbs_option_name');

	if ($hanbs_option_group['hanbs_namespace']) {
		$hanbs_namespace = strtoupper($hanbs_option_group['hanbs_namespace']);
	} else {
		$hanbs_namespace = "HAN";
	}
	 return $hanbs_namespace;
}

function is_debugging() {
	$hanbs_option_group = get_option('hanbs_option_name');

	if ($hanbs_option_group['hanbs_debug'] == "On") {
		$hanbs_debug = true;
	} else {
		$hanbs_debug = false;
	}
	 return $hanbs_debug;
}

/**
 * Prints the box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
function hanbs_inner_custom_box( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'hanbs_inner_custom_box', 'hanbs_inner_custom_box_nonce' );

	/*
	* Use get_post_meta() to retrieve an existing value
	* from the database and use the value for the form.
	*/
	$datasection = get_post_meta( $post->ID, '_hanbs_datasection', true );
	$datapage = get_post_meta( $post->ID, '_hanbs_datapage', true );

	if ($datasection) {
		$datasection_placeholder = $datasection;
	} else {
		$datasection_placeholder = "[data-section]";
	}

	global $current_screen;

	$post_type = get_post_type( $post );



	if ($post_type == 'page') {
		echo '<div class="clearfix post-box"><h4>Data Section should match the name of the JavaScript controller you wish to enque.<br> Example: /assets/js/'.get_namespace_from_option().'/'.'controllers/'.$datasection_placeholder.'.js. Where '.$datasection_placeholder.' is the controller name.</h4></div>';
		echo '<div class="fields-wrap"><div class="clearfix field-box"><p class="field-label"><label for="hanbs_new_field">';
		   _e( "Data Section:", 'hanbs_textdomain' );
		echo '</label></p>';
		echo '<input type="text" id="hanbs_datasection" name="hanbs_datasection" value="' . esc_attr( $datasection ) . '" class="post-field"/></div>';

		$datapage = get_post_meta( $post->ID, '_hanbs_datapage', true );

		echo '<div class="clearfix" style="width: 50%; display: inline-block;"><p class="field-label"><label for="hanbs_new_field">';
		   _e( "Data Page:", 'hanbs_textdomain' );
		echo '</label></p>';
		echo '<input type="text" id="hanbs_datapage" name="hanbs_datapage" value="' . esc_attr( $datapage ) . '" class="post-field"/></div></div>';
	} else {

		//If page is a post/custom post, check to see if HBS option is set, if it is add it to the field
		//Allows all new posts to get published with the field rather than blank
		$hanbs_option_group = get_option('hanbs_option_name');
		$hanbs_datasection = $hanbs_option_group['data-section-'.$post_type.''];
		$hanbs_datapage = $hanbs_option_group['data-page-'.$post_type.''];

		if ($hanbs_datasection != '') {
			$datasection = $hanbs_datasection;
			$datasection_placeholder = $hanbs_datasection;
		}

		if ($hanbs_datapage != '') {
			$datapage = $hanbs_datapage;
		} else {
			$datapage = get_post_meta( $post->ID, '_hanbs_datapage', true );
		}

		echo '<div class="clearfix post-box"><h4>Data Section should match the name of the JavaScript controller you wish to enque.<br> Example: /assets/js/'.get_namespace_from_option().'/'.'controllers/'.$datasection_placeholder.'.js. Where '.$datasection_placeholder.' is the controller name.</h4></div>';
		echo '<div class="fields-wrap"><div class="clearfix field-box"><p class="field-label"><label for="hanbs_new_field">';
		   _e( "Data Section:", 'hanbs_textdomain' );
		echo '</label></p>';
		echo '<input type="text" id="hanbs_datasection" name="hanbs_datasection" value="' . esc_attr( $datasection ) . '" class="post-field"/></div>';

		echo '<div class="clearfix" style="width: 50%; display: inline-block;"><p class="field-label"><label for="hanbs_new_field">';
		   _e( "Data Page:", 'hanbs_textdomain' );
		echo '</label></p>';
		echo '<input type="text" id="hanbs_datapage" name="hanbs_datapage" value="' . esc_attr( $datapage ) . '" class="post-field"/></div></div>';
	}

	
	
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function hanbs_save_postdata( $post_id ) {

	/*
	* We need to verify this came from the our screen and with proper authorization,
	* because save_post can be triggered at other times.
	*/

	// Check if our nonce is set.
	if ( ! isset( $_POST['hanbs_inner_custom_box_nonce'] ) )
	return $post_id;

	$nonce = $_POST['hanbs_inner_custom_box_nonce'];

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $nonce, 'hanbs_inner_custom_box' ) )
		return $post_id;

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	// Check the user's permissions.
	if ( 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	}

	/* OK, its safe for us to save the data now. */

	// Sanitize user input.
	$sani_datasection = sanitize_text_field( $_POST['hanbs_datasection'] );
	$sani_datapage = sanitize_text_field( $_POST['hanbs_datapage'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, '_hanbs_datasection', $sani_datasection );
	update_post_meta( $post_id, '_hanbs_datapage', $sani_datapage );
}
add_action( 'save_post', 'hanbs_save_postdata' );

/*
* Generate data-section attribute based on associated post/page data
*/

function set_nodata_section_notice() {
	if (is_debugging()) {
		echo '<script>console.log("HBS NOTICE: Missing data-section declaration in page/posts editor.");</script>';
	}
}

function get_data_section() {
	$data_section = get_post_meta( get_the_ID(), '_hanbs_datasection', true );
	// check if the meta field has a value
	if( ! empty( $data_section ) ) {

		$hanbs_option_group = get_option('hanbs_option_name');

		if ($hanbs_option_group['hanbs_namespace']) {
			$hanbs_namespace = strtoupper($hanbs_option_group['hanbs_namespace']);
		} else {
			$hanbs_namespace = "HAN";
		}

		echo $hanbs_namespace.'.controllers.'.$data_section;

	} else {
		add_action('wp_footer', 'set_nodata_section_notice');
	}
}

/*
* Generate data-page attribute based on associated post/page data
*/
function get_data_page() {
	$data_page = get_post_meta( get_the_ID(), '_hanbs_datapage', true );
	// check if the meta field has a value
	if( ! empty( $data_page ) ) {
		echo $data_page;
	}
}

/*
* Eqneue section script based on data-section
*/
function enque_section_script() {

	$data_section = get_post_meta( get_the_ID(), '_hanbs_datasection', true );
	if ( !empty( $data_section ) ) {
		$section_js = get_template_directory_uri()."/assets/js/".get_namespace_from_option()."/controllers/$data_section.js";

		$themedirectory = end((explode('/', get_template_directory())));
		$section_path = get_template_directory()."/assets/js/".get_namespace_from_option()."/controllers/$data_section.js";

		if ( is_debugging() && !file_exists($section_path) ) {
			if ( is_debugging() ) {
				echo "<script>console.log('HBS NOTICE: Cannot bootstrap section controller, file missing: /assets/js/".get_namespace_from_option()."/controllers/$data_section.js');</script>";
			}
		} else {
			wp_register_script($data_section, $section_js, array(), null, false);
			wp_enqueue_script($data_section);
		}
	} else {
		if ( is_debugging() && file_exists($section_path) ) {
			echo "<script>console.log('HBS NOTICE: Unable to get DataSection from page/post settings. Double check to make sure the template query has been restored to the original/main query. This error is usually thrown as a result of missing wp_reset_query() or wp_reset_postdata().')</script>";
		}
	}
}

add_action('wp_footer', 'enque_section_script');

?>