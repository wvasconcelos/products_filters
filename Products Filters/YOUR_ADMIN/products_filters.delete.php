<?php
/**
 * Products Filters plugin for Zen Cart. AJAX delete filter from product definition.
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
	require_once("includes/application_top.php");
	if( !isset($filter) ){
		$filter = new ProductsFiltersClass();
	}
	#INITIALIZE
	$filter->update();
	
	if( $filter->pID > 0 and $filter->vID > 0 ){
		$sql = "DELETE FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` 
				WHERE product_id = '" . $filter->pID . "' 
					AND filter_value_id = :valueId:";
		$sql = $db->bindVars($sql, ':valueId:', $filter->vID, 'integer');
		$db->Execute($sql);
	}
?>