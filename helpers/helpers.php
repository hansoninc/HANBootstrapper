<?php

	/*
	* Returns plugin level option value for Namespace e.g. "HAN"
	*/
	function get_namespace_from_option() 
	{
		$hanbs_option_group = get_option('hanbs_option_name');

		if ($hanbs_option_group['hanbs_namespace']) 
		{
			$hanbs_namespace = strtoupper($hanbs_option_group['hanbs_namespace']);
		}
		else 
		{
			$hanbs_namespace = "HAN";
		}
		 return $hanbs_namespace;
	}

	/*
	* Returns plugin level option assset path used for enqueing dependencies
	*/
	function get_assetpath_from_option() 
	{
		$hanbs_option_group = get_option('hanbs_option_name');

		if ($hanbs_option_group['hanbs_assetpath']) 
		{
			$hanbs_assetpath = $hanbs_option_group['hanbs_assetpath'];
		} 
		else 
		{
			$hanbs_assetpath = "/assets/js/";
		}
		 return $hanbs_assetpath;
	}

	/*
	* Returns boolean used to determine if debugging is enabled inside plugin options
	*/
	function is_debugging() 
	{
		$hanbs_option_group = get_option('hanbs_option_name');

		if ($hanbs_option_group['hanbs_debug'] == "On") 
		{
			$hanbs_debug = true;
		} 
		else 
		{
			$hanbs_debug = false;
		}

		return $hanbs_debug;
	}

	/*
	* Generate data-section attribute based on associated post/page data
	*/
	function get_data_section() 
	{
		$data_section = get_post_meta( get_the_ID(), '_hanbs_datasection', true );

		// check if the meta field has a value
		if ( ! empty( $data_section ) ) 
		{

			$hanbs_option_group = get_option('hanbs_option_name');

			if ( $hanbs_option_group['hanbs_namespace'] ) 
			{
				$hanbs_namespace = strtoupper($hanbs_option_group['hanbs_namespace']);
			} 
			else 
			{
				$hanbs_namespace = "HAN";
			}

			echo $hanbs_namespace.'.controllers.'.$data_section;
		} 
		else 
		{
			add_action('wp_footer', 'set_nodata_section_notice');
		}
	}

	/*
	* Generate data-page attribute based on associated post/page data
	*/
	function get_data_page() 
	{
		$data_page = get_post_meta( get_the_ID(), '_hanbs_datapage', true );
		// check if the meta field has a value

		if ( ! empty( $data_page ) ) 
		{
			echo $data_page;
		}
	}

	/*
	* Display debugging messaging if enabled
	*/
	function set_nodata_section_notice() 
	{
		if ( is_debugging() ) 
		{
			echo '<script>console.log("HBS NOTICE: Missing data-section declaration in page/posts editor.");</script>';
		}
	}
?>