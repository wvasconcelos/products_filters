<?php
/**
 * Products Filters plugin for Zen Cart. AJAX add filter to product definition.
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
		#CREATE THE DATA ARRAY
		$sql_data_array = array('product_id'		=> $filter->pID,
								'filter_value_id'	=> $filter->vID,
								'created_by'		=> $_SESSION['admin_id'],
								'created_on'		=> date("Y-m-d H:i:s") );
		#CHECK IF VALUE DOES NOT EXIST
		$sql = "SELECT *
				FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "`
				WHERE 	filter_value_id = :valueId:
					AND product_id = :productId:";
		$sql = $db->bindVars($sql, ':valueId:', $filter->vID, 'integer');
		$sql = $db->bindVars($sql, ':productId:', $filter->pID, 'integer');
		$rec = $db->Execute($sql);
		if($rec->EOF){ #RECORD DOES NOT EXIST
			zen_db_perform(TABLE_ADDON_FILTERS_PRODUCTS, $sql_data_array); #INSERT NEW RECORD
			#RETRIEVE / RETURN FILTER NAME AND VALUE
			$sql = "SELECT filter_name, filter_value
					FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS ptf
						JOIN `" . TABLE_ADDON_FILTERS_VALUES . "` AS v
							ON ptf.filter_value_id = v.filter_value_id
						JOIN `" . TABLE_ADDON_FILTERS . "` AS n
							ON v.filter_id = n.filter_id
					WHERE ptf.filter_value_id = :valueId:
					AND ptf.product_id = :productId:";
			$sql = $db->bindVars($sql, ':valueId:', $filter->vID, 'integer');
			$sql = $db->bindVars($sql, ':productId:', $filter->pID, 'integer');
			$rec = $db->Execute($sql);
			if(!$rec->EOF){
				echo $rec->fields['filter_name'] . ": " . $rec->fields['filter_value']; #RETURN PRODUCT NAME
			}
		} #END IF RECORD DOES NOT EXIST
	}
?>