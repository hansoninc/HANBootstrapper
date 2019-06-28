<?php

if ( !defined( 'HBS_URL' ) ) {
	define( 'HBS_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Add HBS to Admin Toolbar
 */

function hanbs_customize_toolbar() 
{
	global $wp_admin_bar;

	$args = array(
		'id'     => 'HBS',
		'title' => __('<img src="'. HBS_URL . '/images/icons/hbs-hanson-logo-white.png" style="width: 14px; vertical-align:middle;margin-right:5px" alt="Han Bootstrapper" title="Han Bootstrapper" />HanBootStrapper' ),
		'href'   => '/wp-admin/admin.php?page=hanbs-namespace-admin'
	);

	global $current_user;
    wp_get_current_user(); 
	$logged_in = $current_user->user_login;

	$hanbs_option_group = get_option('hanbs_option_name');
	$data_user_loggedin = "data-user-{$logged_in}";

	$user_access = $hanbs_option_group[$data_user_loggedin];

	if ( $logged_in == $user_access ) 
	{
		$wp_admin_bar->add_menu( $args );
	}
}

add_action( 'wp_before_admin_bar_render', 'hanbs_customize_toolbar', 999 );	

?>