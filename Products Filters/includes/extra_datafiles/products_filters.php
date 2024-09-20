<?php
/**
 * Products Filters plugin for Zen Cart. Catalog database tables and page file name definition.
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
	#TABLES
	define('TABLE_ADDON_FILTERS',			DB_PREFIX . 'addon_filters');
	define('TABLE_ADDON_FILTERS_VALUES', 	DB_PREFIX . 'addon_filters_values');
	define('TABLE_ADDON_FILTERS_PRODUCTS',	DB_PREFIX . 'addon_filters_products');
	define('TABLE_ADDON_FILTERS_CATEGORIES',	DB_PREFIX . 'addon_filters_categories');
	
	#TEMPORARY TABLE NAMES
	define('FTMP_TABLE_ALL', 'tmp_main');
	define('FTMP_TABLE_FILTERED', 'tmp_filtered');
	
	#FILE NAMES
	define('FILENAME_ADDON_PRODUCTS_FILTERS', 'products_filters');
?>