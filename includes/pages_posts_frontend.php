<?php 

/*
 * Enque HanBootStrapper Core JS Dependency
*/

if ( !defined( 'HBS_URL' ) ) 
{
	define( 'HBS_URL', plugin_dir_url( __FILE__ ) );
}

function hbs_enqueue_script() 
{
	if ( !is_admin() ) 
	{
		wp_enqueue_script( 'hbs', HBS_URL . 'js/hbs.js', 'jquery' );
	}
}
add_action('wp_enqueue_scripts', 'hbs_enqueue_script');

/*
* Eqneue section script on theme front-end based on data-section
*/
function enque_section_script() 
{
	$data_section = get_post_meta( get_the_ID(), '_hanbs_datasection', true );

	if ( !empty( $data_section ) ) 
	{
		$section_js = get_template_directory_uri().get_assetpath_from_option().get_namespace_from_option()."/controllers/$data_section.js";

		$themedirectory = end((explode('/', get_template_directory())));
		$section_path = get_template_directory().get_assetpath_from_option().get_namespace_from_option()."/controllers/$data_section.js";

		if ( is_debugging() && !file_exists($section_path) ) 
		{
			if ( is_debugging() ) 
			{
				echo "<script>console.log('HBS NOTICE: Cannot bootstrap section controller, file missing: ".get_assetpath_from_option().get_namespace_from_option()."/controllers/$data_section.js');</script>";
			}
		} 
		else 
		{
			wp_register_script($data_section, $section_js, array(), null, false);
			wp_enqueue_script($data_section);
		}
	} 
	else 
	{
		if ( is_debugging() && file_exists($section_path) ) 
		{
			echo "<script>console.log('HBS NOTICE: Unable to get DataSection from page/post settings. Double check to make sure the template query has been restored to the original/main query. This error is usually thrown as a result of missing wp_reset_query() or wp_reset_postdata().')</script>";
		}
	}
}
add_action('wp_footer', 'enque_section_script');

?>