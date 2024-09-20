<?php
/**
 * Products Filters plugin for Zen Cart. Filters page template definition.
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
	$content = '' . "\n";
	
	$column_list = array('PRODUCT_LIST_NAME', 'PRODUCT_LIST_MODEL', 'PRODUCT_LIST_PRICE', 'PRODUCT_LIST_IMAGE');
	
	$listing_sql = "SELECT DISTINCT p.products_id, p.products_model,
			p.products_type, p.master_categories_id, p.manufacturers_id, p.products_price, 
			p.products_tax_class_id, p.products_image, pd.products_description, pd.products_name, 
			IF(s.status = 1, s.specials_new_products_price, NULL) as specials_new_products_price, 
			IF(s.status =1, s.specials_new_products_price, p.products_price) as final_price, 
			p.products_sort_order, p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status
			 FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd 
				JOIN " . TABLE_PRODUCTS . " p 
					ON p.products_id = pd.products_id 
				LEFT JOIN " . TABLE_MANUFACTURERS . " m 
					ON p.manufacturers_id = m.manufacturers_id 
				LEFT JOIN " . TABLE_SPECIALS . " s
					ON p.products_id = s.products_id";
	
	if( $filters->count_filters_selected > 0 and count($filters->cPath) > 0 ){ #IF FILTER(S) SELECTED
		$listing_sql .= "
				JOIN `" . FTMP_TABLE_FILTERED . "` AS f
					ON f.`products_id` = p.`products_id`";
	}else if( count($filters->cPath) > 0 ){ #NO FILTER SELECTED
		$listing_sql .= "
				JOIN `" . FTMP_TABLE_ALL . "` AS t
					ON t.`products_id` = p.`products_id`";
	}
	
	$listing_sql .= "
			WHERE p.products_status = 1
				AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
	
	/* START: SORT ORDER */
	$order_sql = '';
	if( $filters->sort_by != ''){
		$sort_col = substr($filters->sort_by, 0 , 1);
		$sort_order = substr($filters->sort_by, -1);
		switch ( $column_list[ $sort_col - 1 ] ){
			case 'PRODUCT_LIST_MODEL':
			$order_sql .= " ORDER BY p.products_model " . ($sort_order == 'd' ? "DESC" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_NAME':
			$order_sql .= " ORDER BY pd.products_name " . ($sort_order == 'd' ? "DESC" : "");
			break;
		case 'PRODUCT_LIST_MANUFACTURER':
			$order_sql .= " ORDER BY m.manufacturers_name " . ($sort_order == 'd' ? "DESC" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_QUANTITY':
			$order_sql .= " ORDER BY p.products_quantity " . ($sort_order == 'd' ? "DESC" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_IMAGE':
			$order_sql .= " ORDER BY pd.products_name";
			break;
		case 'PRODUCT_LIST_WEIGHT':
			$order_sql .= " ORDER BY p.products_weight " . ($sort_order == 'd' ? "DESC" : "") . ", pd.products_name";
			break;
		case 'PRODUCT_LIST_PRICE':
			$order_sql .= " ORDER BY p.products_price_sorter " . ($sort_order == 'd' ? "DESC" : "") . ", pd.products_name";
			break;
		}
	}
	if($order_sql==''){
		$order_sql = ' ORDER BY p.`products_ordered` DESC';
	}
	$listing_sql .= $order_sql;
	/* END: SORT ORDER */
?>
	<script type="text/javascript">
		$(document).ready(
			function(){
				$("input[name='main_page'").val("products_filters");
<?php
	#LOAD FV URL PARAMETERS
	if( count($filters->fv) > 0 ){
		$fvIDs = '';
		foreach( $filters->fv as $fv ){
			if( $fvIDs != '' ){
				$fvIDs .= '_';
			}
			$fvIDs .= $fv['value_id'];
		}
?>
				$("#filter_results").append('<?php echo zen_draw_hidden_field('fv', $fvIDs); ?>');
<?php
	}
	if( count( $filters->manufacturers ) > 0 ){
?>
				$("#filter_results").append('<?php echo zen_draw_hidden_field('m', implode("_",$filters->manufacturers)); ?>');
<?php
	}
	if( count( $filters->price_indexes ) > 0 ){
?>
				$("#filter_results").append('<?php echo zen_draw_hidden_field('p', implode("_",$filters->price_indexes)); ?>');
<?php
	}
	if( count( $filters->ratings ) > 0 ){
?>
				$("#filter_results").append('<?php echo zen_draw_hidden_field('r', implode("_",$filters->ratings)); ?>');
<?php
	}
	if( $filters->specials === true ){
?>
				$("#filter_results").append('<?php echo zen_draw_hidden_field('s', '1'); ?>');
<?php
	}
?>
			}
		);
	</script>
	
	<script type="text/javascript" src="<?php echo $template->get_template_dir('products_filters.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'); ?>/products_filters.js"></script>
	
	<h1 id="productListHeading"><?php echo NAVBAR_TITLE; ?></h1>
<?php
	require($template->get_template_dir('tpl_modules_product_listing.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_product_listing.php');
?>
<br clear="all" />
