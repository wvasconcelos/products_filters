<?php
/**
 * Products Filters plugin for Zen Cart. Catalog filters class definition.
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
	class ProductsFilters{
		/* START: INSTANCE VARIABLES */
		#SANITIZED GET (URL) PARAMETERS
		public $main_page	= '';	#PAGE IDENTIFICATION (STRING)
		public $cPath	= array();	#LIST OF CATEGORY IDs (STRING)
		public $fv		= array();	#SELECTED FILTER, VALUE IDs (ARRAY 2D)
		public $view		= '';	#VIEW: ROWS (LIST) OR COLUMNS (GRID)
		public $sort_by		= '';	#SORT BY AND SORT ORDER CODEs
		public $manufacturers = array(); #ARRAY: SEL MANUF. IDs (INT)
		public $price_indexes = array(); #ARRAY: SEL PRICE LIST INDEX (INT)
		public $ratings	= array();	#ARRAY: SEL RATINGS INDEX (INT)
		public $specials = false;	#SHOW ONLY SPECIALS? (Y/N)
		
		#CATEGORY / FILTER INFO VARIABLES
		public $is_filter_browsing	= false;	#FILTERS ALLOWED (Y/N)
		public $category_has_filters= false;	#FILTER AVAILABILITY (Y/N)
		private $load_temp_tables	= false;	#PERMISSION TO LOAD
		public $filters_loaded		= false; 	#TEMP TABLES LOADED
		public $replace_cat_with_filters = false; #REPLACE CATEGORIES WITH FILTER BROWSING?
		private $valid_filter_page	= false;	#ONE OF THE 4 VALID PAGES
		public $browsing_mode		= '';		#STORES EXPLICIT REQUEST
		public $count_filters_selected = 0;		#SEL FILTERS COUNT (INT)
		public $filter_switch_html	= '';		#FILTER/CAT BTN (HTML)
		public $pricing_break_points= array();	#PRICING BREAK POINTS (ARRAY[FLOAT])

/*******
COMMENTS:
IS_FILTER_BROWSING: if true, load temp tables. True when:
- Page is:
 (1) category listing (index),
 (2) product listing (index),
 (3) product description (product_info),
 (4) product filter (products_filters);
- cPath is available;
- Category_has_filters is true (see below);
- Handles explicit requests for filter/category and/or default setting.
Called from:
- Methods within this class;
- includes/modules/sideboxes/products_filters.php

CATEGORY_HAS_FILTERS: true when current category:
- Has filters enabled and 
- Contains 1+ item(s) with filter.
Called from:
- Methods within this class;


LOAD_TEMP_TABLES: Enables create temp tables. Alias for:
- is_filter_browsing.
Called from:
- __constructor method only.

FILTERS_LOADED: Checks if both temp tables were created. True if:
- Both temp and filters_temp tables were successfully created.


REPLACE_CAT_WITH_FILTERS: true when:
- Page is:
 (1) category listing,
 (2) product product,
 (3) product description,
 (4) product filter;
- In filter mode OR mode empty AND default == filters;
- category_has_filters is true (see above).

BROWSING_MODE: stores explicit browsing mode requests.

COUNT_FILTERS_SELECTED: Returns the number of filters selected, independent of how many values were selected on each filter.

FILTER_SWITCH_HTML: Generates the filter/category switch button to display in the page.
*******/
		/* END: INSTANCE VARIABLES */
		
		function __construct() {
			/* THIS COSTRUCTOR: 
			 * 1. SANITIZES & PREPS ALL USER GENERATED INPUT VALUES
			 * 2. PROVIDES FILTER STATUS INFORMATION
			 * 3. CONTROLS FILTER DATA IN THE SESSION
			 * 4. LOADS AVAILABLE AND FILTERED PRODUCTS TEMP TABLES
			 */
			
			$this->LoadInstanceVariables(); #LOAD SANITIZED USER INPUT
			
			if( $this->is_filter_browsing ){ #START: IS FILTER BROWSING?
				if( $this->main_page == 'product_info' ){ #PRODUCT PAGE
					if( isset($_SESSION['products_filters']) ){
						#FV (2D ARRAY: FILTER ID, VALUE ID)
						if( isset($_SESSION['products_filters']['fv']) ){
							$this->fv = $_SESSION['products_filters']['fv'];
						}
						#VIEW (GRID / LIST)
						if( isset($_SESSION['products_filters']['view']) ){
							$this->view = $_SESSION['products_filters']['view'];
						}
						#SORT (STRING)
						if( isset($_SESSION['products_filters']['sort']) ){
							$this->sort_by = $_SESSION['products_filters']['sort'];
						}
						#MANUFACTURERS (ARRAY)
						if( isset($_SESSION['products_filters']['m']) ){
							$this->manufacturers = $_SESSION['products_filters']['m'];
						}
						#PRICE INDEXES (ARRAY)
						if( isset($_SESSION['products_filters']['p']) ){
							$this->price_indexes = $_SESSION['products_filters']['p'];
						}
						#RATINGS (ARRAY)
						if( isset($_SESSION['products_filters']['r']) ){
							$this->ratings = $_SESSION['products_filters']['r'];
						}
						#SPECIALS (Y/N)
						if( isset($_SESSION['products_filters']['s']) ){
							$this->specials = $_SESSION['products_filters']['s'];
						}
						#CPATH (ARRAY)
						if( isset($_SESSION['products_filters']['cPath']) ){
							$this->cPath = $_SESSION['products_filters']['cPath'];
						}
					} #END: IF SESSION PRODUCTS FILTERS EXISTS
					if( isset($_SESSION['browsing_mode']) ){
						$this->browsing_mode = $_SESSION['browsing_mode'];
					}
				}else{ #IS FILTER BROWSING BUT IS NOT PRODUCT PAGE
					$this->LoadSession(); #LOAD SESSION WITH NEW INPUT
				} #END: IF PRODUCT PAGE
			}else{ #NOT FILTER BROWSING
				/*
				if( !$this->valid_filter_page ){ #NOT VALID FILTER PAGE
					if( isset($_SESSION['browsing_mode']) ){
						unset($_SESSION['browsing_mode']);
					}
				}
				*/
				//do nothing
			} #END: IS FILTER BROWSING
			
			#START: LOADING INFORMATION VARIABLES
			#NUMBER OF FILTERS SELECTED
			$this->count_filters_selected = $this->GetCountFiltersSelected();

			#FILTER CATEGORY SWITCH (HTML OUTPUT)
			$this->filter_switch_html = $this->GetFilterSwitchHTML();
			
			if( $this->is_filter_browsing ){
				#CHECK IF REPLACING CATEGORY WITH FILTER BROWSING
				$this->replace_cat_with_filters = $this->GetReplaceCatWithFilters();
				
				if( $this->main_page != 'product_info' ){ #NOT PROD DESC PG
					$this->load_temp_tables = true; #ENABLE TEMP TABLES
				}
			}
			
			#CREATE TEMP TABLE 
			if( $this->load_temp_tables ){
				#CREATE, LOAD TEMP TABLE W/ CANDIDATE FILTES, VALUES, PRODUCTS
				$confirm_tmp_table_created = $this->CreateLoadTempTable();
			}
			
			#CREATE AND LOAD FILTERED TABLE
			/***** LAST IN CONSTRUCTOR *****/
			if( $confirm_tmp_table_created ){
				#PRICING: ONLY LOAD IF TMP TABLE EXISTS
				if( FILTER_BY_PRICES == 'Yes' ){
					$this->pricing_break_points = $this->GetPricingBreakPoints();
				}
				
				#FILTERED TABLE: ONLY LOAD IF TMP TABLE EXISTS
				$this->filters_loaded = $this->CreateLoadFilteredTable();
			}
		} #END: function __construct()
		
		/***********************************
		 **        PRIVATE METHODS        **
		 ***********************************/
		private function LoadInstanceVariables(){
			/* LOAD INSTANCE VARIABLES MAKING SURE THEY
			 * ARE SANITIZED AND ARE IN THE PROPER TYPE
			 ******************************************/
			#MAIN PAGE (STRING)
			if( isset($_GET['main_page']) and $_GET['main_page'] != '' ){
				$this->main_page = $this->SanitizeString( $_GET['main_page'] );
			}
			#CPATH (ARRAY)
			if( isset($_GET['cPath']) and $_GET['cPath'] != ''){
				$this->cPath = $this->StringToArray( $_GET['cPath'] );
			}
			#FV (2D ARRAY: FILTER ID, VALUE ID)
			if( isset($_GET['fv']) and $_GET['fv'] != '' ){ #FILTERS SELECTED
				$this->fv = $this->GetFiltersValuesArray( $_GET['fv'] );
			}
			#VIEW (GRID / LIST)
			if( isset($_GET['view']) and $_GET['view'] != '' ){
				$this->view = $this->SanitizeString( $_GET['view'] );
			}
			#SORT (STRING)
			if( isset($_GET['sort']) and $_GET['sort'] != '' ){
				$this->sort_by = $this->SanitizeString( $_GET['sort'] );
			}
			#MANUFACTURERS (ARRAY)
			if( isset($_GET['m']) and $_GET['m'] != '' ){
				$this->manufacturers = $this->StringToArray( $_GET['m'] );
			}
			#PRICE INDEXES (ARRAY)
			if( isset($_GET['p']) and $_GET['p'] != '' ){
				$this->price_indexes = $this->StringToArray( $_GET['p'] );
			}
			#RATINGS (ARRAY)
			if( isset($_GET['r']) and $_GET['r'] != '' ){
				$this->ratings = $this->StringToArray( $_GET['r'] );
			}
			#SPECIALS (Y/N)
			if( isset($_GET['s']) and $_GET['s'] == 1 ){
				$this->specials = true;
			}
			#VALID FILTER PAGES
			$valid_pages = array(
				'index',			/* PRODUCTS/CATEGORIES LISTING */
				'products_filters',	/* PRODUCTS FILTERS */
				'product_info'		/* PRODUCT DESCRIPTION */
				);
			if( in_array($this->main_page, $valid_pages) ){
				$this->valid_filter_page = true;
			}
			#LOAD EXPLICIT REQUESTS FOR BROWSING MODE
			if( isset($_GET['mode']) and $_GET['mode'] != '' ){
				$this->browsing_mode = zen_db_prepare_input($_GET['mode']);
				$_SESSION['browsing_mode'] = $this->browsing_mode;
			}else if( isset($_SESSION['browsing_mode']) ){
				$this->browsing_mode = $_SESSION['browsing_mode'];
			}else{ #MODE IS STILL EMPTY
				if( FILTER_DISPLAY_DEFAULT == 'Yes' ){
					$this->browsing_mode = 'filter';
				}
			}
			
			#CONTROL SESSION
			if( $this->browsing_mode == 'category'){
				if( isset( $_SESSION['products_filters'] ) ){
					unset( $_SESSION['products_filters'] );
				}
			}
			
			#DETAILED CONFIRMATION ON BROWSING MODE
			$this->is_filter_browsing = $this->GetIsFilterBrowsing();
			
			#CATEGORY HAS FILTERS (YES/NO)
			$cID = $this->cPath[ count($this->cPath) - 1 ]; #GET LEAF CATEGORY ID
			if( $cID > 0 ){
				$this->category_has_filters = $this->GetCategoryHasFiltersAnswer( $cID );
			}
		} #END: function LoadInstanceVariables
		
		private function GetIsFilterBrowsing(){
			/* DECIDE IF USER SHOULD BE BROWSING BY
			 * FILTERS OR CATEGORIES (RETURN TRUE/FALSE)
			 **************************************/
			$is_filter_browsing = true;
			
			/* VALID PAGES: PRODUCTS FILTERS, DESCRIPTION,
			 * LISTING, AND CATEGORY LISTING PAGES. */
			if( !$this->valid_filter_page ){ #NOT VALID PAGE
				$is_filter_browsing = false;
			}else{ #PAGE IS STILL A CANDIDATE
				#PAGE MUST HAVE a CPATH (NO HOMEPAGE)
				if( count($this->cPath) == 0 ){
					$is_filter_browsing = false; #CPATH REQUIRED
				}else{
					#CATEGORY MUST HAVE FILTERS EXPLICITLY ENABLED
					$cID = $this->cPath[ count($this->cPath) - 1 ];
					if( !$this->GetCategoryHasFiltersAnswer( $cID ) ){
						#CURRENT CATEGORY HAS NO FILTERS
						$is_filter_browsing = false;
					}
				}
			}
			
			if( $is_filter_browsing ){
				#HANDLE EXPLICIT REQUESTS: FILTER/CATEGORY BROWSING
				if( $this->browsing_mode == 'filter' ){
					$is_filter_browsing = true;
				}else{
					$is_filter_browsing = false;
				}
			}
			
			return $is_filter_browsing;
		} #END: function GetIsFilterBrowsing
		
		private function LoadSession(){
			$_SESSION['products_filters'] = array(
				'fv'	=>$this->fv,
				'view'	=>$this->view,
				'sort'	=>$this->sort_by,
				'm'		=>$this->manufacturers,
				'p'		=>$this->price_indexes,
				'r'		=>$this->ratings,
				's'		=>$this->specials,
				'cPath'	=>$this->cPath
			);
		} #END: function LoadSession
		
		private function CreateLoadTempTable(){
			/* CREATE A TEMPORARY TABLE HOSTING
			 * PRODUCT ID, FILTER ID AND VALUE ID
			 * OF ALL PRODUCTS IN THE CURRENT CPATH.
			 **************************************/
			global $db;
			
			$temp_table_created = false;
			
			if( count($this->cPath) > 0 ){
				$sql = "CREATE TEMPORARY TABLE `" . FTMP_TABLE_ALL . "` ( 
							`products_id` INT NOT NULL ,
							`filters_id` INT NOT NULL ,
							`values_id` INT NOT NULL , 
							PRIMARY KEY (`products_id`, `filters_id`, `values_id`)
						) ENGINE=MyISAM";
				$temp_table_created = $db->Execute($sql);
				
				if( $temp_table_created ){ #LOAD TEMP TABLE
					#GENERATE DATA TO LOAD MAIN TABLE
					$sql = "INSERT INTO `" . FTMP_TABLE_ALL . "`
								SELECT DISTINCT fp.`product_id`, fv.`filter_id`, fv.`filter_value_id`
								FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS fp
									JOIN `" . TABLE_ADDON_FILTERS_VALUES."` AS fv
										ON fp.`filter_value_id` = fv.`filter_value_id`
									JOIN `" . TABLE_ADDON_FILTERS . "` AS f
										on fv.`filter_id` = f.`filter_id`
									JOIN `" . TABLE_PRODUCTS_TO_CATEGORIES . "` AS ptc
										ON ptc.`products_id` = fp.`product_id`
									JOIN `" . TABLE_PRODUCTS . "` AS p
										ON ptc.`products_id` = p.`products_id`
								WHERE p.`products_status` = 1
									AND f.`active` = 1";
					#GET CATEGORY ID
					$current_category_id = $this->cPath[count($this->cPath)-1];
					if( zen_has_category_subcategories( $current_category_id ) ){
						$sub_cats = zen_get_categories( '', $current_category_id );
						$c = array();
						foreach( $sub_cats as $cID ){
							$c[] = $cID['id'];
						}
						if( count( $c ) > 0 ){
							$cat_list = implode( "','", $c );
							$sql .= " AND ptc.`categories_id` IN ('" . $cat_list . "')";
						}
					}else{
						$sql .= " AND ptc.`categories_id` = '" . $current_category_id . "'";
					}
					$sql .= " ORDER BY fv.`filter_id`, fv.`filter_value_id`, fp.`product_id` ASC";
					
					$db->Execute($sql);
				} #END: IF TEMP TABLE CREATED, POPULATE TABLE
			} #END: IF CPATH
			
			return $temp_table_created;
		} #END: function CreateLoadTempTable
		
		private function CreateLoadFilteredTable(){
			/* CREATE A FILTERED COPY OF THE TEMP TABLE
			 * REMOVING ALL ITEMS THAT DO NOT MATCH
			 * FILTERS SELECTED.
			 */
			global $db;
			
			$filters_table_created = false;
			
			#CREATE FILTERED TABLE
			$sql = "CREATE TEMPORARY TABLE `" . FTMP_TABLE_FILTERED . "` ( 
					`products_id` INT NOT NULL ,
					`filters_id` INT NOT NULL ,
					`values_id` INT NOT NULL , 
					PRIMARY KEY (`products_id`, `filters_id`, `values_id`)
				) ENGINE=MyISAM";
			$filters_table_created = $db->Execute($sql);
			if( $filters_table_created ){
				#POPULATE FILTERED TABLE: CLONE TEMP TABLE
				$sql = "INSERT INTO `" . FTMP_TABLE_FILTERED . "`
							SELECT * FROM `" . FTMP_TABLE_ALL . "`";
				$db->Execute($sql);
				
				#GROUP VALUES BY FILTER
				$filter_values = array();
				if( count($this->fv) > 0 ){
					foreach( $this->fv as $fv){
						$filter_values[$fv['filter_id']][] = $fv['value_id'];
					}
				}
				
				#REMOVE PRODUCTS THAT DO NOT MATCH SELECTED FILTERS
				if( count($filter_values) > 0 ){
					foreach($filter_values as $values){
						if( count($values) > 1 ){
						$sql = "DELETE FROM `" . FTMP_TABLE_FILTERED . "`
								WHERE `products_id` NOT IN (
									SELECT `products_id`
									FROM `" . FTMP_TABLE_ALL . "`
									WHERE `values_id` IN ('" . implode("','", $values) . "')
								)";
						}else{
							$sql = "DELETE FROM `" . FTMP_TABLE_FILTERED . "`
									WHERE `products_id` NOT IN (
										SELECT `products_id`
										FROM `" . FTMP_TABLE_ALL . "`
										WHERE `values_id` = '" . $values[0] . "'
									)";
						}
						$db->Execute($sql);
					}
				}
				
				#MANUFACTURERS
				if( FILTER_BY_MANUFACTURERS == 'Yes' ){
					if( count($this->manufacturers) > 0 ){
						if( count($this->manufacturers) > 1 ){
							$where = "WHERE p.`manufacturers_id` IN ('" . implode("','", $this->manufacturers) . "')";
						}else{ #ONLY ONE MANUFACTURER
							$where = "WHERE p.`manufacturers_id` = '" . $this->manufacturers[0] . "'";
						}
						$sql = "DELETE FROM `" . FTMP_TABLE_FILTERED . "`
									WHERE `products_id` NOT IN (
										SELECT t.`products_id`
										FROM `" . FTMP_TABLE_ALL . "` t
											JOIN `" . TABLE_PRODUCTS . "` p 
												ON t.`products_id` = p.`products_id`
										" . $where . "
									)";
						$db->Execute($sql);
					}
				}
				
				#PRICE RANGE
				if( FILTER_BY_PRICES == 'Yes' ){
					if( count($this->price_indexes) > 0 ){
						$f_sql = '';
						$round_count = 0;
						$f_sql .= " SELECT t.`products_id`
								FROM `" . FTMP_TABLE_ALL . "` t
									JOIN `" . TABLE_PRODUCTS . "` p 
										ON t.`products_id` = p.`products_id`
								WHERE ";
						$where = '';
						
						foreach($this->price_indexes as $pi){
							$round_count++;
							if( $pi == 1 ){
								$where .= " OR (`products_price` < '" . $this->pricing_break_points[0] . "')";
							}else if( $pi > 1 and $pi < count($this->pricing_break_points) ){
								$where .= " OR ( `products_price` >= '" . $this->pricing_break_points[$pi - 2] . "'
												AND `products_price` < '" . $this->pricing_break_points[$pi - 1] . "')";
							}else if( $pi == count($this->pricing_break_points) ){
								$where .= " OR (`products_price` >= '" . $this->pricing_break_points[ $pi - 2 ] . "')";
							}
						} #END FOR EACH
						$where = trim( $where, ' OR ' );
						#EXECUTE
						if( $where != '' ){
							$sql = "DELETE FROM `" . FTMP_TABLE_FILTERED . "`
									WHERE `products_id` NOT IN (" . $f_sql . $where . ")";
							$db->Execute($sql);
						}
					}
				}
				
				#CUSTOMER RATINGS
				if( FILTER_BY_RATINGS == 'Yes' ){
					#IDENTIFY PRODUCTS BY AVERAGE RATINGS
					if( count($this->ratings) > 0 ){
						$pids = array();
						$sql = "SELECT p.`products_id` AS 'pid', AVG(r.`reviews_rating`) AS 'avg'
								FROM `" . TABLE_PRODUCTS . "` AS p
									LEFT JOIN `" . TABLE_REVIEWS . "` AS r
										ON p.`products_id` = r.`products_id`
								WHERE p.`products_status` = 1
									AND r.`reviews_rating` IS NOT NULL
									AND r.`status` = 1
									AND p.`products_id` IN ( 
										SELECT DISTINCT `products_id`
										FROM `" . FTMP_TABLE_FILTERED . "`
									)
								GROUP BY p.`products_id`";
						$rec = $db->Execute( $sql );
						while( !$rec->EOF ){
							$avg = $rec->fields['avg'];
							if( (ceil($avg) - $avg) <= 0.5 ){
								$avg = ceil($avg);
							}else{
								$avg = floor($avg);
							}
							if( in_array($avg, $this->ratings) ){
								$pids[] = $rec->fields['pid'];
							}
							$rec->MoveNext();
						}
						
						if( count($pids) > 0 ){
							$sql = "DELETE 
									FROM `" . FTMP_TABLE_FILTERED . "`
									WHERE `products_id` NOT IN 
									('" . implode("','", $pids) . "')";
							$db->Execute($sql);
						}
					}
				} #END RATINGS FILTER
				
				#SPECIALS / ON-SALE
				if( FILTER_BY_SPECIALS == 'Yes' ){
					if( $this->specials ){
						$f_sql = "SELECT DISTINCT t.`products_id`
									FROM `" . FTMP_TABLE_ALL . "` AS t
										LEFT JOIN `" . TABLE_SPECIALS . "` AS s
											ON t.`products_id` = s.`products_id`
									WHERE s.`status` = 1
										AND (s.`expires_date` = '0001-01-01' OR s.`expires_date` > DATE(NOW()))
										AND s.`specials_date_available` < DATE(NOW())";
						$sql = "DELETE FROM `" . FTMP_TABLE_FILTERED . "`
								WHERE `products_id` NOT IN (" . $f_sql . ")";
						$db->Execute($sql);
					}
				} #END SPECIALS FILTER
			} #END: IF TABLE CREATED
			
			return $filters_table_created;
			
		} #END: function CreateLoadFilteredTable
		
		private function StringToArray( $input_string ){
			/* RECEIVES A STRING CONTAINING IDs SEPARATED BY UNDERSCORE
			 * CHARACTERS, SANITIZES THAT STRING AND CONVERT ITS CONTENT
			 * INTO AN ARRAY CONTAINING VALID, NON-DUPLICATE IDs (ONLY).
			 */
			$output_array = array();
			$input_string = $this->SanitizeString( $input_string );
			$input_string = preg_replace('/[^0-9\_]/', '', $input_string); #ONLY NUMBERS, UNDERSCOREs
			if( strpos($input_string, "_") ){ #MULTIPLE IDS
				$tmp = explode( "_", $input_string );
				foreach( $tmp as $id ){
					if( (int)$id > 0 and !in_array((int)$id, $output_array) ){
						$output_array[] = (int)$id;
					}
				}
			}else if( (int)$input_string > 0 ){ #SINGLE ID
				$output_array[] = (int)$input_string;
			}
			
			return $output_array;
		} #END: function StringToArray
		
		private function SanitizeString( $input ){
			#ENCODING OF SPECIAL CHARACTERS
			$char = array("'", '"');
			$encd = array("&#39;", "&quot;");
			$input = str_ireplace($char, $encd, $input);
			#REMOVAL OF ANYTHING OTHER THAN APPROVED CHARACTERS
			$normal_characters = "a-zA-Z0-9\s!@#$%&*()_+-={}|:;<>?,.\/\[\]";
			$input = preg_replace("/[^$normal_characters]/", '', $input );
			$input = zen_db_prepare_input( $input );
			
			return $input;
		} #END: function SanitizeString
		
		private function GetFiltersValuesArray( $fv ){
			/* RECEIVES A STRING CONTAINING FILTER VALUE IDS SEPARATED BY
			 * UNDERSCORES AND RETURNS AN ARRAY OF 2 ITEM ARRAYS
			 * COMPOSED OF FILTER ID AND VALUE ID.
			 */
			global $db;
			
			$fv = $this->StringToArray( $fv ); #CONVERT STRING TO ARRAY
			$filters_array = array();
			if( count($fv) > 0 ){
				#ASSOCIATE OPTIONS TO VALUES
				$sql = "SELECT `filter_value_id`, `filter_id`
						FROM `" . TABLE_ADDON_FILTERS_VALUES . "`
						WHERE `filter_value_id` IN ('" . implode( "','", $fv ) . "')
						ORDER BY `filter_id` ASC";
				$rec = $db->Execute($sql);
				while( !$rec->EOF ){
					$filters_array[] = array(
						'filter_id'	=> $rec->fields['filter_id'],
						'value_id'	=> $rec->fields['filter_value_id']
					);
					$rec->MoveNext();
				}
			}
			
			return $filters_array;
		} #END: function GetFiltersValuesArray
		
		private function GetCountFiltersSelected(){
			/* COUNTS THE NUMBER OF FILTERS SELECTED
			 */
			$selected_filters_count = 0;
			#COUNT CUSTOM FILTERS
			if( count($this->fv) > 0 ){
				$filter_ids = array();
				foreach( $this->fv as $fv ){
					if( !in_array($fv['filter_id'], $filter_ids) ){
						$filter_ids[] = $fv['filter_id'];
					} 
				}
				$selected_filters_count = count( $filter_ids );
			}
			#COUNT NATIVE DATA FILTERS
			$selected_filters_count += count( $this->GetSelectedNativeFilterIDs() );
			
			return $selected_filters_count;
		} #END: function GetCountFiltersSelected
		
		private function GetCategoryHasFiltersAnswer( $cID ){
			/* BASED ON A PROVIDED CPATH, RECURSIVELY CHECK
			 * IF ANY PRODUCT WITHIN CATEGORY HAS FILTERS.
			 */
			global $db;
			
			$category_has_filters = false;
			
			$cID = $this->SanitizeString( $cID );
			#CHECK IF CATEGORY IS ENABLED
			$sql = "SELECT `filters_status`
					FROM `" . TABLE_ADDON_FILTERS_CATEGORIES . "`
					WHERE `categories_id` = :categoryId:";
			$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
			$rec = $db->Execute($sql);
			if( !$rec->EOF ){ #CATEGORY LISTED
				if( $rec->fields['filters_status'] == '1' ){ #CATEGORY ENABLED
					$sql = "SELECT f.`filter_id`
							FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS fp
								JOIN `" . TABLE_ADDON_FILTERS_VALUES . "` AS fv
									ON fp.`filter_value_id` = fv.`filter_value_id`
								JOIN `" . TABLE_ADDON_FILTERS . "` AS f
									ON f.`filter_id` = fv.`filter_id`
								LEFT JOIN `" . TABLE_PRODUCTS_TO_CATEGORIES . "` AS ptc
									ON fp.`product_id` = ptc.`products_id`
								JOIN `" . TABLE_PRODUCTS . "` AS p
									ON ptc.`products_id` = p.`products_id`
							WHERE p.`products_status` = 1
								AND f.`active` = 1";
					
					if( zen_has_category_subcategories( $cID ) ){
						$sub_cats = zen_get_categories( '', $cID );
						$c = array();
						foreach( $sub_cats as $catID ){ #GET ALL CATEGORY IDs
							$c[] = zen_db_prepare_input((int)$catID['id']);
						}
						if( count( $c ) > 0 ){
							$cat_list = implode( "','", $c );
							$sql .= " AND ptc.`categories_id` IN ('" . $cat_list . "')";
						}
					}else{ #NO SUB-CATEGORIES
						$sql .= " AND ptc.`categories_id` = :categoryId:";
						$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
					} #END: IF SUB-CATEGORIES
					$sql .= " LIMIT 1";
					$rec = $db->Execute($sql);
					if( !$rec->EOF and (int)$rec->fields['filter_id'] > 0 ){
						$category_has_filters = true;
					}
				} #END: IF CATEGORY ENABLED
			} #END: IF CATEGORY IS LISTED
			
			if( !$category_has_filters ){
				if( isset($_SESSION['products_filters']) ){
					unset($_SESSION['products_filters']);
				}
			}
			
			return $category_has_filters;
		} #END: function GetCategoryHasFiltersAnswer
		
		private function GetFilterSwitchHTML(){
			$cPath	= '';
			$output	= '';
			
			#LOAD CPATH
			if( count($this->cPath) > 0 ){
				$cPath = 'cPath=' . implode("_", $this->cPath);
			}
			
			#ATTEMPT TO LOAD OUTPUT
			if( $this->main_page != 'product_info' ){ #NOT PRODUCT DESC PG
				if( $this->category_has_filters ){ #FILTERS AVAILABLE
					if( $this->is_filter_browsing ){ #USING FILTERS
						$output = '<div class="leftBoxContainer" id="productsfilters"><a href="' . zen_href_link(FILENAME_DEFAULT, $cPath . '&mode=category') . '" id="fc-switch"><span><img src="' . DIR_WS_TEMPLATE . 'images/icon-category.png" /> ' . MENU_TITLE_SHOP_BY_CATEGORIES . '</span></a></div>' . "\n";
					}else{ #NOT USING FILTERS
						$output = '<div class="leftBoxContainer" id="productsfilters"><a href="' . zen_href_link(FILENAME_DEFAULT, $cPath . '&mode=filter') . '" id="fc-switch"><span><img src="' . DIR_WS_TEMPLATE . 'images/icon-filter.png" /> ' . MENU_TITLE_SHOP_BY_FILTERS . '</span></a></div>' . "\n";
					} #END: IF USING FILTERS
				} #END: IF FILTERS AVAILABLE
			} #END: IF NOT IN PRODUCT DESCRIPTION PAGE
			
			return $output;
		} #END: function GetFilterSwitchHTML
		
		private function GetPricingBreakPoints(){
			global $db;
			
			$pricing_break_points = array();
			
			if( $this->load_temp_tables ){
				/* Note: Temp Filters table not loaded yet... */
				#START: GET AVAILABLE PRICE RANGE (MIN/MAX)
				$sql = "SELECT DISTINCT p.`products_id`, p.`products_price`, a.`options_values_price`, a.`price_prefix`, s.`specials_new_products_price`, s.`expires_date`, s.`status`, s.`specials_date_available`
						FROM `" . FTMP_TABLE_ALL . "` AS t
							LEFT JOIN `" . TABLE_PRODUCTS . "` AS p
								ON t.`products_id` = p.`products_id`
							LEFT JOIN `" . TABLE_PRODUCTS_ATTRIBUTES . "` AS a
								ON p.`products_id` = a.`products_id`
							LEFT JOIN `" . TABLE_SPECIALS . "` AS s
								ON p.`products_id` = s.`products_id`
						WHERE p.`products_status` = 1";
				$rec = $db->Execute($sql);
				$min_price = 0;
				$max_price = 0;
				while( !$rec->EOF ){
					#CHECK IF PRODUCT IS ON SALE
					if( $rec->fields['status'] == 1 and ( $rec->fields['expires_date'] == '0001-01-01' or date("Ymd", strtotime($rec->fields['expires_date'])) > date("Ymd")) and ($rec->fields['specials_date_available'] == '0001-01-01' or date("Ymd", strtotime($rec->fields['specials_date_available'])) < date("Ymd") )){
						$price = $rec->fields['specials_new_products_price'];
					}else{
						$price = $rec->fields['products_price'];
					}
					#CHECK IF PRODUCT HAS ATTRIBUTE, CONSIDER OPTIONS' PRICES
					if( $rec->fields['options_values_price'] != null ){
						if( $rec->fields['price_prefix'] == '-' ){
							$price -= $rec->fields['options_values_price'];
						}else{
							$price += $rec->fields['options_values_price'];
						}
					}
					
					if( $min_price == 0 or $min_price > $price ){
						$min_price = $price;
					}
					if( $max_price == 0 or $max_price < $price ){
						$max_price = $price;
					}
					$rec->MoveNext();
				}
				#GET USER DEFINED NUMBER OF PRICING BREAK POINTS
				$number_of_price_break_points = strip_tags(zen_get_configuration_key_value(FILTER_BREAK_POINTS_NUMBER));
				#DEFINE AN APPROPRIATE PRICE GAP
				$price_gap = 0;
				if( (int)$number_of_price_break_points > 1 ){ #AT LEAST TWO
					$price_gap = ($max_price - $min_price)/(int)$number_of_price_break_points;
				}
				if( $price_gap < 5 ){
					return $pricing_break_points; #EMPTY (NO NEED TO USE A PRICE RANGE FILTER)
				}else if( $price_gap < 7.5 ){
					$price_gap = 5;
				}else if( $price_gap < 15 ){
					$price_gap = 10;
				}else if( $price_gap < 35 ){
					$price_gap = 20;
				}else if( $price_gap < 75 ){
					$price_gap = 50;
				}else if( $price_gap <= 100 ){
					$price_gap = 100;
				}else{
					$price_gap = floor($price_gap);
				}
				#MIN PRICE ROUNDED
				$min_price = floor($min_price);
				$min_price = floor($min_price - ($min_price%$price_gap));
				if( $min_price < $price_gap ){
					$min_price = $price_gap;
				}
				#MAX PRICE ROUNDED
				$max_price = ceil($max_price);
				#END: GET AVAILABLE PRICE RANGE (MIN/MAX)
				
				#GENERATE BREAK POINTS AS MULTIPLES OF PRICE GAP
				$price = $min_price;
				for( $i = 0; $i < (int)$number_of_price_break_points; $i++ ){
					if( ceil( $price ) < $max_price ){
						$pricing_break_points[] = ceil( $price );
					}
					$price += $price_gap;
				}
				if( count($pricing_break_points) < $number_of_price_break_points ){
					$price += $price_gap;
					$pricing_break_points[] = ceil( $price );
				}
			} #END: IF FILTERS LOADED
			
			return $pricing_break_points;
		} #END: function GetPricingBreakPoints
		
		private function GetReplaceCatWithFilters(){
			/* DECIDE WHEN TO REPLACE CATEGORY
			 * BROWSING WITH FILTER BROWSING.
			 *********************************/
			$replace_cat_with_filters	= false;
			
			$is_homepage				= false;
			$is_category_page			= false;
			$is_product_page			= false;
			$is_products_filters_page	= false;
			
			#IDENTIFY CURRENT PAGE
			if( $this->main_page == 'index' ){
				if( $this->cPath == '' ){
					$is_homepage = true;
				}else{
					$is_category_page = true;
				}
			}else if( $this->main_page == 'product_info' ){
				$is_product_page = true;
			}else if( $this->main_page == 'products_filters' ){
				$is_products_filters_page = true;
			}
			
			#DECIDE
			#1. HOMEPAGE: ALWAYS SHOW CATEGORIES
			if( $is_homepage ){
				//do nothing: keep replace as false
			}
			
			#2. CATEGORY / PRODUCTS LISTING PAGES
			if( $is_category_page ){
				if( $this->is_filter_browsing ){
					$replace_cat_with_filters = true;
				}
			}
			
			#3. PRODUCT DESCRIPTION PAGE
			if( $is_product_page ){
				if( $this->is_filter_browsing ){
					$replace_cat_with_filters = true;
				}
			}
			
			#4. PRODUCTS FILTERS PAGE: ALWAYS REPLACE
			if( $is_products_filters_page ){
				$replace_cat_with_filters = true;
			}
			
			return $replace_cat_with_filters;
		}
		
		/**********************************
		 **        PUBLIC METHODS        **
		 **********************************/
		public function GetSelectedValues(){
			/* RETURNS AN ARRAY WITH ALL SELECTED
			 * FILTER VALUE IDS FROM SESSION.
			 */
			$selected_array = array();
			if( count( $this->fv ) > 0 ){
				foreach( $this->fv as $fv ){
					$selected_array[] = $fv['value_id'];
				}
			}
			
			return $selected_array;
		} #END: function GetSelectedValues
		
		public function GetSelectedNativeFilterIDs(){
			/* RETURNS AN ARRAY WITH THE NAMES OF ALL
			 * SELECTED NATIVE DATA FILTER PARAMETERS.
			 */
			$native_filter_ids = array();
			if( isset($_GET['m']) and $this->SanitizeString( $_GET['m'] ) != '' ){
				$native_filter_ids[] = 'm';
			}
			if( isset($_GET['p']) and $this->SanitizeString( $_GET['p'] ) != '' ){
				$native_filter_ids[] = 'p';
			}
			if( isset($_GET['r']) and $this->SanitizeString( $_GET['r'] ) != '' ){
				$native_filter_ids[] = 'r';
			}
			if( isset($_GET['s']) and $this->SanitizeString( $_GET['s'] ) == 1 ){
				$native_filter_ids[] = 's';
			}
			
			return $native_filter_ids;
		} #END: function GetSelectedNativeFilterIDs
		
		public function IsValueSelected( $vID ){
			/* GETS A SINGLE FILTER VALUE ID
			 * AND CHECK IF IT IS SELECTED. 
			 * RETURN TRUE OR FALSE.        
			 *******************************/
			$isSelected = false;
			if( isset($_GET['fv']) ){
				$fv_ar = $this->StringToArray( $_GET['fv'] );
				if( count($fv_ar) > 0 ){
					foreach( $fv_ar as $fv ){
						if( $fv == $vID ){
							$isSelected = true;
							break;
						}
					}
				}
			}
			
			return $isSelected;
		} #END: function IsValueSelected
		
		public function FilterInUse( $filter_id ){
			/* GETS A SINGLE FILTER ID AND CHECKS
			 * IF ANY OF ITS VALUES IS SELECTED. 
			 * RETURN TRUE OR FALSE.             
			 ************************************/
			global $db;
			
			$filter_in_use = false;
			if( isset($_GET['fv']) ){
				$fv_ar = $this->StringToArray( $_GET['fv'] );
				if( count($fv_ar) > 0 ){
					$sql = "SELECT DISTINCT `filter_id`
							FROM `" . TABLE_ADDON_FILTERS_VALUES . "`
							WHERE `filter_value_id` IN ('" . implode("','", $fv_ar) . "')";
					$rec = $db->Execute($sql);
					while( !$rec->EOF ){
						if( $filter_id == $rec->fields['filter_id'] ){
							$filter_in_use = true;
						}
						$rec->MoveNext();
					}
				}
			}
			
			return $filter_in_use;
		} #END: function FilterInUse
		
		public function SelectTempTableName( $filter_id ){
			/* BASED ON A GIVEN FILTER ID, DECIDE
			 * BETWEEN TEMP AND FILTERED TABLES.
			 * NOTE: THE PROVIDED FILTER_ID CAN BE:
			 * NUMERIC => RELATED TO THE FILTERS TABLE (CUSTOM FILTER).
			 * CHARACTER => RELATED TO ZEN CART DATA (NATIVE FILTER).
			 *************************************/
			
			$custom_selected_ids = array();
			if( count($this->fv) > 0 ){ #CUSTOM FILTER SELECTED
				#CASE 1: ANOTHER CUSTOM FILTER IS SELECTED
				if( !is_numeric($filter_id) ){ #NATIVE FILTER
					return FTMP_TABLE_FILTERED; #FILTERED
				}else if ( !$this->FilterInUse( (int)$filter_id ) ){ #UNSELECTED CUSTOM FILTER
					return FTMP_TABLE_FILTERED; #FILTERED
				}
				
				#CASE 2: MULTIPLE CUSTOM FILTERS SELECTED
				foreach($this->fv as $fv){
					if( !in_array($fv['filter_id'], $custom_selected_ids) ){
						$custom_selected_ids[] = $fv['filter_id'];
					}
				}
				$filter_count = count( $custom_selected_ids );
				if( $filter_count > 1 ){
					return FTMP_TABLE_FILTERED; #FILTERED
				}
			}
			
			#CASE 3: MULTIPLE COMBINATIONS OF FILTERS SELECTED
			$native_selected_ids = $this->GetSelectedNativeFilterIDs();
			$filter_count += count( $native_selected_ids );
			if( $filter_count > 1 ){
				return FTMP_TABLE_FILTERED; #FILTERED
			}
			
			#CASE 4: ANOTHER FILTER SELECTED
			if( $filter_count == 1 ){
				if( !in_array($filter_id, $custom_selected_ids) ){
					if( !in_array($filter_id, $native_selected_ids) ){
						return FTMP_TABLE_FILTERED; #FILTERED
					}
				}
			}
			
			#RETURN DEFAULT
			return FTMP_TABLE_ALL; #DEFAULT TABLE
		} #END: function SelectTempTableName
		
		public function GetProductsPriceRangeCount( $index ){
			/* COUNTER METHOD FOR NATIVE
			 * DATA: PRICE RANGE
			 ************************/
			global $db;
			
			$count = 0;
			$from_price = 0;
			
			if( $this->filters_loaded ){
				$selected_temp_table = $this->SelectTempTableName("p");
				
				if( $index == 0 ){
					$to_price = $this->pricing_break_points[ $index ];
					#COUNT PRODUCTS AVAILABLE IN PRICE RANGE
					$sql = "SELECT COUNT(*) AS count 
							FROM `" . TABLE_PRODUCTS . "`
							WHERE `products_status` = 1
								AND `products_price` <= '" . $to_price . "'
								AND `products_id` IN (SELECT `products_id` FROM `" . $selected_temp_table . "`)";
				}else if( $index == count($this->pricing_break_points) - 1 ){
					$from_price = $this->pricing_break_points[ $index - 1 ];
					#COUNT PRODUCTS AVAILABLE IN PRICE RANGE
					$sql = "SELECT COUNT(*) AS count 
							FROM `" . TABLE_PRODUCTS . "`
							WHERE `products_status` = 1
								AND `products_price` > '" . $from_price . "'
								AND `products_id` IN (SELECT `products_id` FROM `" . $selected_temp_table . "`)";
				}else if( $index > 0 and $index < count($this->pricing_break_points) - 1 ){
					$from_price = $this->pricing_break_points[ $index - 1 ];
					$to_price = $this->pricing_break_points[ $index ];
					$sql = "SELECT COUNT(*) AS count 
							FROM `" . TABLE_PRODUCTS . "`
							WHERE `products_status` = 1
								AND `products_price` > :fromPrice:
								AND `products_price` <= :toPrice:
								AND `products_id` IN (SELECT `products_id` FROM `" . $selected_temp_table . "`)";
					$sql = $db->bindVars($sql, ':fromPrice:', $from_price, 'integer');
					$sql = $db->bindVars($sql, ':toPrice:', $to_price, 'integer');
				}
				
				$rec = $db->Execute($sql);
				if( !$rec->EOF ){
					$count = (int)$rec->fields['count'];
				}
			} #END: IF FILTERS LOADED
			
			return $count;
		} #END: function GetProductsPriceRangeCount
		
		public function GetProductsSpecialsCount(){
			/* COUNTER METHOD FOR NATIVE  
			 * DATA: ITEMS ON SALE/SPECIAL
			 *****************************/
			global $db;
			
			$count = 0;
			
			if( $this->filters_loaded ){
				$selected_temp_table = $this->SelectTempTableName("s");
				$sql = "SELECT COUNT(*) AS 'count'
						FROM `" . TABLE_PRODUCTS . "` AS p
							LEFT JOIN `" . TABLE_SPECIALS . "` AS s
								ON p.`products_id` = s.`products_id`
						WHERE p.`products_status` = 1
							AND s.`specials_id` IS NOT NULL
							AND s.`status` = 1
							AND (s.`expires_date` = '0001-01-01' OR s.`expires_date` > DATE(NOW()))
							AND s.`specials_date_available` <= DATE(NOW())
							AND p.`products_id` IN ( SELECT DISTINCT `products_id` FROM `" . $selected_temp_table . "` )";
				$rec = $db->Execute($sql);
				if( !$rec->EOF ){
					$count = (int)$rec->fields['count'];
				}
			} #END: IF FILTERS LOADED
			return $count;
		} #END: function GetProductsSpecialsCount
		
		public function GetProductsRatingsCount(){
			/* COUNTER METHOD FOR NATIVE  
			 * DATA: ITEM RATINGS
			 *****************************/
			global $db;
			
			#INITIALIZE RETURN ARRAY
			$ratings_count = array(); #DECLARE
			
			if( $this->filters_loaded ){
				for( $i = 1; $i <= 5; $i++ ){ #INITIALIZE
					$ratings_count[$i] = 0;
				}
				#LOAD ARRAY WITH PRODUCTS RATINGS
				$selected_temp_table = $this->SelectTempTableName("r");
				$sql = "SELECT AVG(r.`reviews_rating`) AS 'avg'
						FROM `" . TABLE_PRODUCTS . "` AS p
							LEFT JOIN `" . TABLE_REVIEWS . "` AS r
								ON p.`products_id` = r.`products_id`
						WHERE p.`products_status` = 1
							AND r.`reviews_rating` IS NOT NULL
							AND r.`status` = 1
							AND p.`products_id` IN ( 
								SELECT DISTINCT `products_id`
								FROM `".$selected_temp_table."`
							)
						GROUP BY p.`products_id`";
				$rec = $db->Execute($sql);
				while( !$rec->EOF ){
					$average = ceil( $rec->fields['avg'] );
					$ratings_count[ $average ]++;
					$rec->MoveNext();
				}
			} #END: IF FILTERS LOADED
			
			return $ratings_count;
		} #END: function GetProductsRatingsCount
		
		public function GetProductsManufacturersCount(){
			/* COUNTER METHOD FOR NATIVE DATA: MANUFACTURERS.
			 * RETURN ARRAY INDEXED BY MANUFACTURER ID,
			 * CONTAINING MANUFACTURER NAME, NUMBER OF ITEMS.
			 ************************************************/
			global $db;
			
			$manufacturers = array();
			
			if( $this->filters_loaded ){
				$selected_temp_table = $this->SelectTempTableName("m");
				$sql = "SELECT m.`manufacturers_id`, m.`manufacturers_name`, COUNT(*) AS 'count'
						FROM `" . TABLE_PRODUCTS . "` AS p
							LEFT JOIN `" . TABLE_MANUFACTURERS . "` AS m
								ON m.`manufacturers_id` = p.`manufacturers_id`
						WHERE p.`products_status` = 1
							AND m.`manufacturers_name` IS NOT NULL
							AND p.`products_id` IN ( SELECT DISTINCT `products_id` FROM `" . $selected_temp_table . "` )
						GROUP BY m.`manufacturers_id`, m.manufacturers_name
						ORDER BY m.`manufacturers_name` ASC";
				$rec = $db->Execute($sql);
				if( !$rec->EOF ){
					while( !$rec->EOF ){
						$manufacturers[ $rec->fields['manufacturers_id'] ] = array(
							'name' => $rec->fields['manufacturers_name'],
							'count' => $rec->fields['count']
						);
						$rec->MoveNext();
					}
				}
			} #END: IF FILTERS LOADED
			
			return $manufacturers;
		} #END: function GetProductsManufacturersCount
		
		public function GetFilterNameFromId( $fID ){
			/* RETURNS FILTER NAME FROM
			 * PROVIDED FILTER ID.
			 **************************/
			global $db;
			
			$filter_name = '';
			$sql = "SELECT `filter_name`
					FROM `" . TABLE_ADDON_FILTERS . "`
					WHERE `filter_id` = :filterId:";
			$sql = $db->bindVars($sql, ':filterId:', (int)$fID, 'integer');
			$rec = $db->Execute($sql);
			if( !$rec->EOF ){
				$filter_name = $rec->fields['filter_name'];
			}
			
			return $filter_name;
		} #END: function GetFilterNameFromId
		
		public function GetAvailableFiltersInfo(){
			/* RETURNS AN ARRAY CONTAINING ALL
			 * AVAILABLE FILTER NAMES AND IDS.
			 *********************************/
			global $db;
			
			$filters_info = array();
			if( $this->filters_loaded ){
				$sql = "SELECT DISTINCT t.`filters_id`, f.`filter_name`
						FROM `" . FTMP_TABLE_ALL . "` AS t
							LEFT JOIN `" . TABLE_ADDON_FILTERS . "` AS f
								ON t.`filters_id` = f.`filter_id`
							ORDER BY f.`sort_order`, f.`filter_name`";
				$rec = $db->Execute( $sql );
				while( !$rec->EOF ){
					$filters_info[ $rec->fields['filters_id'] ] = $rec->fields['filter_name'];
					$rec->MoveNext();
				}
			} #END: IF FILTERS LOADED
			
			return $filters_info;
		} #END: function GetAvailableFiltersInfo
		
		public function GetFilterValuesInfo( $fID ){
			/* RETURN AN ARRAY CONTAINING FILTER VALUES
			 * NAMES AND IDS FROM A GIVEN FILTER ID.   
			 ******************************************/
			global $db;
			
			$values_info = array();
			
			if( $this->filters_loaded ){
				#DECIDE WHEN TO USE FILTERED vs. FULL TEMP TABLEs
				$selected_temp_table = $this->SelectTempTableName( $fID );
				$sql = "SELECT DISTINCT v.`filter_value_id`, v.`filter_value`
						FROM `" . FTMP_TABLE_ALL . "` AS t
							LEFT JOIN `" . TABLE_ADDON_FILTERS_VALUES . "` AS v
								ON t.`filters_id` = v.`filter_id`
						WHERE t.`filters_id` = :filterId:
						ORDER BY v.`sort_order`, v.`filter_value` ASC";
				$sql = $db->bindVars($sql, ':filterId:', (int)$fID, 'integer');
				$rec = $db->Execute($sql);
				while( !$rec->EOF ){
					#COUNT PRODUCTS
					$sql = "SELECT COUNT(*) AS count
							FROM `" . $selected_temp_table . "`
							WHERE `values_id` = :valueId:";
					$sql = $db->bindVars($sql, ':valueId:', (int)$rec->fields['filter_value_id'], 'integer');
					$rec_count = $db->Execute($sql);
					if( !$rec_count->EOF ){
						if( $rec_count->fields['count'] > 0 ){
							$values_info[] = array(
								'id'	=>$rec->fields['filter_value_id'],
								'name'	=>$rec->fields['filter_value'],
								'count'	=>$rec_count->fields['count']
							);
						}
					}
					
					$rec->MoveNext();
				}
			} #END: IF FILTERS LOADED
			
			return $values_info;
		} #END: function GetFilterValuesInfo
	} #END: class ProductsFilters
	