<?php
/**
 * Products Filters plugin for Zen Cart. Observer class definition, filters object instantiation.
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
	class products_filters_observer extends base {
		function __construct() {
			global $zco_notifier; //ADD THIS LINE ONLY AS NEEDED
			$this->attach($this, array('NOTIFY_HTML_HEAD_START'));
		}
		function update(&$callingClass, $notifier, $paramsArray) {
			global $sniffer, $filters;
			
			if( $sniffer->table_exists( TABLE_ADDON_FILTERS ) ){
				include( DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'products_filters.php' );
				require(DIR_WS_CLASSES . 'products_filters.php');
				$filters = new ProductsFilters();
			}
		}
	}
?>
