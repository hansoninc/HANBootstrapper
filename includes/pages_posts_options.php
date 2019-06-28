<?php 

/**
 * Adds a box to the main column on the Post and Page edit screens.
 */

if ( !defined( 'HBS_URL' ) ) 
{
	define( 'HBS_URL', plugin_dir_url( __FILE__ ) );
}

function hanbs_add_custom_box() 
{
	global $current_user;
    wp_get_current_user(); 
	$logged_in = $current_user->user_login;

	$hanbs_option_group = get_option('hanbs_option_name');
	$user_access = $hanbs_option_group['data-user-'.$logged_in.''];

	if ( $logged_in == $user_access ) 
	{
		//Get all pages, posts and custom post types for Meta Box
		$pagesandposts = get_post_types();
		//Remove attachment, acf etc
		$pagesandposts_sanitized = array_diff($pagesandposts, array("attachment", "acf", "revision", "nav_menu_item"));

		foreach ( $pagesandposts_sanitized as $pagesorpost ) 
		{
			add_meta_box(
				'hanbs_sectionid',
				__( 'HanBootStrapper Settings', 'hanbs_textdomain' ),
				'hanbs_inner_custom_box',
				$pagesorpost
			);
		}
	} 
	else if ( $logged_in == !$user_access ) 
	{
		//Get all pages, posts and custom post types for Meta Box
		$pagesandposts = get_post_types();
		//Remove attachment, acf etc
		$pagesandposts_sanitized = array_diff($pagesandposts, array("attachment", "acf", "revision", "nav_menu_item"));

		foreach ( $pagesandposts_sanitized as $pagesorpost ) 
		{
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
 * Prints the box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
function hanbs_inner_custom_box( $post ) 
{

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'hanbs_inner_custom_box', 'hanbs_inner_custom_box_nonce' );

	/*
	* Use get_post_meta() to retrieve an existing value
	* from the database and use the value for the form.
	*/
	$datasection = get_post_meta( $post->ID, '_hanbs_datasection', true );
	$datapage = get_post_meta( $post->ID, '_hanbs_datapage', true );

	if ($datasection) 
	{
		$datasection_placeholder = $datasection;
	} 
	else 
	{
		$datasection_placeholder = "[data-section]";
	}

	global $current_screen;
	$post_type = get_post_type( $post );

	if ($post_type == 'page') 
	{
		echo '<div class="clearfix post-box"><h4>Data Section should match the name of the JavaScript controller you wish to enque.<br> Example: '.get_assetpath_from_option().get_namespace_from_option().'/controllers/'.$datasection_placeholder.'.js. Where '.$datasection_placeholder.' is the controller name.</h4></div>';
		echo '<div class="fields-wrap"><div class="clearfix field-box"><p class="field-label"><label for="hanbs_new_field">';
		   _e( "Data Section:", 'hanbs_textdomain' );
		echo '</label></p>';
		echo '<input type="text" id="hanbs_datasection" name="hanbs_datasection" value="' . esc_attr( $datasection ) . '" class="post-field"/></div>';

		$datapage = get_post_meta( $post->ID, '_hanbs_datapage', true );

		echo '<div class="clearfix" style="width: 50%; display: inline-block;"><p class="field-label"><label for="hanbs_new_field">';
		   _e( "Data Page:", 'hanbs_textdomain' );
		echo '</label></p>';
		echo '<input type="text" id="hanbs_datapage" name="hanbs_datapage" value="' . esc_attr( $datapage ) . '" class="post-field"/></div></div>';
	} 
	else 
	{

		//If page is a post/custom post, check to see data-sections && || data-page option(s) is set, if true add it to the field
		//Allows all new posts to get published with the field rather than blank
		$hanbs_option_group = get_option('hanbs_option_name');

		$data_section_post_type = "data-section-{$post_type}";
		$hanbs_datasection = $hanbs_option_group[$data_section_post_type];

		$data_page_post_type = "data-page-{$post_type}";
		$hanbs_datapage = $hanbs_option_group[$data_page_post_type];

		if ($hanbs_datasection != '') 
		{
			$datasection = $hanbs_datasection;
			$datasection_placeholder = $hanbs_datasection;
		}

		if ($hanbs_datapage != '') 
		{
			$datapage = $hanbs_datapage;
		} 
		else
		{
			$datapage = get_post_meta( $post->ID, '_hanbs_datapage', true );
		}

		echo '<div class="clearfix post-box"><h4>Data Section should match the name of the JavaScript controller you wish to enque.<br> Example: '.get_assetpath_from_option().get_namespace_from_option().'/'.'controllers/'.$datasection_placeholder.'.js. Where '.$datasection_placeholder.' is the controller name.</h4></div>';
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
function hanbs_save_postdata( $post_id ) 
{

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

?>