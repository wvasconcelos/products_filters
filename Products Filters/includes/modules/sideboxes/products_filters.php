<?php
/**
 * Products Filters plugin for Zen Cart. Sidebox module definition.
 * Description: Allows customers to retrieve a list of products from the
 * catalog by filtering items listed anywhere within a given category.
 * 
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
 * @Version: 2.0
 * @Release Date: Friday, January 26 2018 PST
 * @Tested on Zen Cart v1.5.5 $
 */
	$replace_cat_with_filters = false;
	$is_filter_browsing = false;
	if( isset($filters) ){
		$replace_cat_with_filters 	= $filters->replace_cat_with_filters;
		$is_filter_browsing			= $filters->is_filter_browsing;
	}
	
	# START: SIDE BOX CALL
	if ( $is_filter_browsing ){
		#LOAD SIDEBOX TEMPLATE
		require($template->get_template_dir('tpl_products_filters.php',DIR_WS_TEMPLATE,
		$current_page_base,'sideboxes'). '/tpl_products_filters.php');
		$title 			=  BOX_HEADING_CATALOG_PRODUCTS_FILTER;
		$left_corner	= false;
		$right_corner	= false;
		$right_arrow	= false;
		require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE,
		$current_page_base,'common') . '/' . $column_box_default);
		
		if( $replace_cat_with_filters ){ #HIDE OTHER BOXES
			$column_left_display->EOF = 1;
		}
	}else{
		echo '<script src="' . DIR_WS_TEMPLATE . 'jscript/products_filters.js"></script>
		<link rel="stylesheet" href="' . DIR_WS_TEMPLATE . 'css/products_filters.css">';
		echo $filters->filter_switch_html;
	}
	# END: SIDE BOX CALL
