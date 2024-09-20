<?php
/**
 * Products Filters plugin for Zen Cart. Admin filters class definition.
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
class ProductsFiltersClass extends base{

	#INSTANCE VARIABLES
	var $fID		= 0;
	var $vID		= 0;
	var $pID		= 0;
	var $sort_order	= 0;
	var $action		= '';
	var $filter_name= '';
	var $value_name	= '';
	var $is_active	= false;
	
	function __construct() {
		$this->attach($this, array('NOTIFY_BEGIN_ADMIN_PRODUCTS', 'NOTIFY_BEGIN_ADMIN_CATEGORIES'));
	}

	function update(){
		#INITIALIZE CATEGORY PRODUCT LISTING
		if( $_REQUEST['action'] == 'filter_status' ){
			$this->SetCategoryStatus( $_GET['cID'] );
		}

		#SELECTED FILTER ID
		if( isset($_GET['fID']) and $_GET['fID'] != '' ){
			$this->fID = (int)$this->SanitizeString( $_GET['fID'] );
		}else if( isset($_POST['fID']) and $_POST['fID'] != '' ){
			$this->fID = (int)$this->SanitizeString( $_POST['fID'] );
		}
		
		#SELECTED FILTER VALUE (OPTION) ID
		if( isset($_GET['vID']) and $_GET['vID'] != '' ){
			$this->vID = (int)$this->SanitizeString( $_GET['vID'] );
		}else if( isset($_POST['vID']) and $_POST['vID'] != '' ){
			$this->vID = (int)$this->SanitizeString( $_POST['vID'] );
		}
		
		#SELECTED PRODUCT ID
		if( isset($_GET['pID']) and $_GET['pID'] != '' ){
			$this->pID = (int)$this->SanitizeString( $_GET['pID'] );
		}else if( isset($_POST['pID']) and $_POST['pID'] != '' ){
			$this->pID = (int)$this->SanitizeString( $_POST['pID'] );
		}else if( isset($_POST['selPID']) and $_POST['selPID'] != '' ){
			$this->pID = (int)$this->SanitizeString( $_POST['selPID'] );
		}

		#SELECTED SORT ORDER
		if( isset($_GET['txtSortOrder']) and $_GET['txtSortOrder'] != '' ){
			$this->sort_order = $this->SanitizeString( $_GET['txtSortOrder'] );
		}else if( isset($_POST['txtSortOrder']) and $_POST['txtSortOrder'] != '' ){
			$this->sort_order = $this->SanitizeString( $_POST['txtSortOrder'] );
		}

		#SELECTED ACTION
		if( isset($_GET['action']) and $_GET['action'] != '' ){
			$this->action = $this->SanitizeString( $_GET['action'] );
		}else if( isset($_POST['action']) and $_POST['action'] != '' ){
			$this->action = $this->SanitizeString( $_POST['action'] );
		}

		#FILTER NAME
		if( isset($_GET['txtFilter']) and $_GET['txtFilter'] != '' ){
			$this->filter_name = $this->SanitizeString( $_GET['txtFilter'] );
		}else if( isset($_POST['txtFilter']) and $_POST['txtFilter'] != '' ){
			$this->filter_name = $this->SanitizeString( $_POST['txtFilter'] );
		}

		#SELECTED SORT ORDER
		if( isset($_GET['txtValue']) and $_GET['txtValue'] != '' ){
			$this->value_name = $this->SanitizeString( $_GET['txtValue'] );
		}else if( isset($_POST['txtValue']) and $_POST['txtValue'] != '' ){
			$this->value_name = $this->SanitizeString( $_POST['txtValue'] );
		}

		#IS FILTER ACTIVE
		if( isset($_GET['rdActive']) and $_GET['rdActive'] == 1 ){
			$this->is_active = true;
		}else if( isset($_POST['rdActive']) and $_POST['rdActive'] == 1 ){
			$this->is_active = true;
		}
	}

	public function AddFilterAjaxHTML(){
		global $db;

		$output = '';
		
		if( $this->pID > 0 ){
			$filters = $this->GetFilters();
			$output = '<link rel="stylesheet" href="includes/css/jquery-ui.min.css">
			<script src="includes/javascript/jquery-ui.min.js"></script>
			<script src="includes/javascript/products_filters.js"></script>
			<link rel="stylesheet" href="includes/css/products_filters.css">
			<tr>
				<td colspan="2">'. zen_draw_separator('pixel_black.gif', '100%', '3') . '</td>
			</tr>
			<tr>
				<td class="main" valign="top">
					';

			#GENERATE TREE
			$fTree = '<ul>' . "\n";
			foreach($filters as $f){
				$fTree .= '<li><div class="filter-name">' . $f['name'] . '</div>';
				if( count($f['leafs']) > 0 ){
					$fTree .= '<ul>';
					foreach($f['leafs'] as $v){
						$fTree .= '<li><div class="value-lnk" onClick="AddFilter(' . $this->pID . ',' . $v['id'] . ');">' . $v['name'] . '</div></li>';
					}
					$fTree .= '</ul>';
				}
				$fTree .= '</li>' . "\n";
			}
			$fTree .= '</ul>' . "\n";

			#LOAD TREE
			$output .= '<p><b>' . BOX_ADDON_FILTERS . '</b></p>
					<ul id="menu">
						<li>
							<div style="font-size:16px; font-weight:bold;">Start</div>
							' . $fTree . '
						</li>
					</ul>
				</td>
				<td class="main" valign="top">
					<div id="filters">';

			#LOAD DISPLAY OF SELECTED FILTERS
			$sql = "SELECT v.filter_value_id, v.filter_value, n.filter_name 
					FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS ptf
						JOIN `" . TABLE_ADDON_FILTERS_VALUES . "` AS v 
							ON ptf.filter_value_id = v.filter_value_id
						JOIN `" . TABLE_ADDON_FILTERS . "` AS n
							ON n.filter_id = v.filter_id
					WHERE ptf.product_id = :productsId:
					ORDER BY v.`sort_order`, v.`filter_value` ASC";
			$sql = $db->bindVars($sql, ':productsId:', $this->pID, 'integer');
			$rec = $db->Execute($sql);
			while(!$rec->EOF){
				$output .= '<span id="btn_' . $rec->fields['filter_value_id'] . '" class="btn btn-primary">' . $rec->fields['filter_name'] . ": " . $rec->fields['filter_value'] . ' <span onClick="RemoveFilter(' . $this->pID . ',' . $rec->fields['filter_value_id'] . ');" class="badge">x</span></span>' . "\n";
				$rec->MoveNext();
			}

			#END
			$output .= '
					</div>
				</td>
			</tr>' . "\n";
		}

		return $output;
	}

	private function GetFilters(){
		global $db;
		$output = array();

		#LOAD FILTER NAMES
		$sql = "SELECT n.`filter_id`, n.`filter_name`
				FROM `" . TABLE_ADDON_FILTERS . "` AS n";
		$rec = $db->Execute($sql);

		while( !$rec->EOF ){
			$output[$rec->fields['filter_id']] = array(
				'name'	=> $rec->fields['filter_name'],
				'type'	=> 'filter',
				'id'	=> $rec->fields['filter_id'],
				'leafs'	=> '');

			#LOAD FILTERS VALUES
			$values = array();
			$sql = "SELECT v.filter_value_id, v.filter_value 
					FROM `" . TABLE_ADDON_FILTERS . "` AS n
						LEFT JOIN `" . TABLE_ADDON_FILTERS_VALUES . "` AS v
							ON n.filter_id = v.filter_id
					WHERE n.filter_id = '" . $rec->fields['filter_id'] . "'";
			if( $this->pID > 0 ){
				$sql .= " AND v.filter_value_id NOT IN (
							SELECT filter_value_id
							FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "`
							WHERE product_id = :productsId:
						  )";
				$sql = $db->bindVars($sql, ':productsId:', $this->pID, 'integer');
			}
			$sql .= ' ORDER BY v.`sort_order`, v.`filter_value` ASC';
			$rec_values = $db->Execute($sql);
			while( !$rec_values->EOF ){
				if( $rec_values->fields['filter_value'] != "" ){
					$values[$rec_values->fields['filter_value_id']] = array(
						'name'	=> $rec_values->fields['filter_value'],
						'id'	=> $rec_values->fields['filter_value_id'],
						'type'	=> 'value'
					);
				}
				$rec_values->MoveNext();
			}
			if( count($values) > 0 ){
				$output[$rec->fields['filter_id']]['leafs'] = $values;
			}else{ //FILTER HAS NO VALUES: REMOVE FILTER
				unset( $output[$rec->fields['filter_id']] );
			}
			$rec->MoveNext();
		}

		return $output;
	}

	/* START: FILTER MANAGEMENT METHODS */
	function GetFilterNameFromID( $fID ){
		global $db;
		$filterName = '';
		$sql = "SELECT `filter_name` 
				FROM `" . TABLE_ADDON_FILTERS . "` 
				WHERE `filter_id` = :filtersId:";
		$sql = $db->bindVars($sql, ':filtersId:', $fID, 'integer');
		$rec = $db->Execute($sql);
		if ( !$rec->EOF ) {
			$filterName = $rec->fields['filter_name'];
		}
		return $filterName;
	}

	function GetValueNameFromID( $vID ){
		global $db;
		$valueName = '';
		$sql = "SELECT `filter_value` 
				FROM `" . TABLE_ADDON_FILTERS_VALUES . "`
				WHERE `filter_value_id` = :valuesId:";
		$sql = $db->bindVars($sql, ':valuesId:', $vID, 'integer');
		$rec = $db->Execute($sql);
		if ( !$rec->EOF ) {
			$valueName = $rec->fields['filter_value'];
		}
		return $valueName;
	}

	function GetProductsCount( $valueID ) {
		global $db;
		$sql = "SELECT COUNT(*) AS numProducts 
				FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` 
				WHERE `filter_value_id` = :valuesId:";
		$sql = $db->bindVars($sql, ':valuesId:', $valueID, 'integer');
		$rec = $db->Execute($sql);
		if (!$rec->EOF) {
			return $rec->fields['numProducts'];
		}
		return 0;
	}

	function GetValueCount( $filterID ) {
		global $db;
		$sql = "SELECT COUNT(*) AS numValues 
				FROM `" . TABLE_ADDON_FILTERS_VALUES . "` 
				WHERE `filter_id` = :filterId:";
		$sql = $db->bindVars($sql, ':filterId:', $filterID, 'integer');
		$rec = $db->Execute($sql);
		if (!$rec->EOF) {
			return $rec->fields['numValues'];
		}
		return 0;
	}

	function GetProductsToValuesCount( $productID ) {
		global $db;
		$sql = "SELECT COUNT(*) AS numValues 
				FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` 
				WHERE `product_id` = :productId:";
		$sql = $db->bindVars($sql, ':productId:', $productID, 'integer');
		$rec = $db->Execute($sql);
		if (!$rec->EOF) {
			return $rec->fields['numValues'];
		}
		return 0;
	}

	function tab($n) {
		$tabs = '';
		for ($i == 0; $i < $n; $i++) {
			$tabs .= "\t";
		}
		return $tabs;
	}

	function GetBreadcrumb(){
		global $db;

		$output = '';
		$calling_page = trim($_SERVER['SCRIPT_NAME'], DIR_WS_ADMIN);
		$calling_page = substr($calling_page, 0, stripos($calling_page, 'php') - 1);

		switch( $calling_page ){
			case FILENAME_ADDON_FILTERS_PRODUCTS:
				if( $this->pID == 0  and $this->vID > 0 ){
					#TRY TO GENERATE A DEFAULT PID
					$sql = "SELECT fp.`product_id` 
							FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS fp
								JOIN `" . TABLE_PRODUCTS_DESCRIPTION . "` AS pd
									ON fp.`product_id` = pd.`products_id`
							WHERE `filter_value_id` = :valueId:
							ORDER BY fp.`sort_order`, pd.`products_name` ASC
							LIMIT 1";
					$sql = $db->bindVars($sql, ':valueId:', $this->vID, 'integer');
					$rec = $db->Execute($sql);
					if( !$rec->EOF ){
						$this->pID = $rec->fields['product_id'];
					}
				}
				$output = '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, 'fID=' . $this->fID) . '">' . $this->GetFilterNameFromID( $this->fID ) . '</a>';
				$output .= BREADCRUMB_DIVIDER;
				$output .= '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'fID=' . $this->fID . '&vID=' . $this->vID) . '">' . $this->GetValueNameFromID( $this->vID ) . '</a>';
				if( $this->pID > 0 ){
					$output .= BREADCRUMB_DIVIDER;
					$output .= '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'fID=' . $this->fID . '&vID=' . $this->vID . '&pID=' . $this->pID) . '">' . zen_get_products_name( $this->pID, $_SESSION['languages_id'] ) . '</a>';
				}
				break;
			case FILENAME_ADDON_FILTERS_VALUES:
				if( $this->vID == 0  and $this->fID > 0 ){
					#TRY TO GENERATE A DEFAULT VID
					$sql = "SELECT `filter_value_id` 
							FROM `" . TABLE_ADDON_FILTERS_VALUES . "`
							WHERE `filter_id` = :filterId:
							ORDER BY `sort_order`, `filter_value` ASC
							LIMIT 1";
					$sql = $db->bindVars($sql, ':filterId:', $this->fID, 'integer');
					$rec = $db->Execute($sql);
					if( !$rec->EOF ){
						$this->vID = $rec->fields['filter_value_id'];
					}
				}
				$output = '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, 'fID=' . $this->fID) . '">' . $this->GetFilterNameFromID( $this->fID ) . '</a>';
				if( $this->vID > 0 ){
					$output .= BREADCRUMB_DIVIDER;
					$output .= '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'fID=' . $this->fID . '&vID=' . $this->vID) . '">' . $this->GetValueNameFromID( $this->vID ) . '</a>';
				}
				break;
			case FILENAME_ADDON_FILTERS:
				$output = '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, 'fID=' . $this->fID) . '">' . $this->GetFilterNameFromID( $this->fID ) . '</a>';
				break;
			default;
				break;
		}

		return $output;
	}

	function GetFilterCount( $categoryID ) {
		global $db;
		$sql = "SELECT COUNT(*) AS numFilters
				FROM `" . TABLE_ADDON_FILTERS . "`
				WHERE `categories_id` = :categoryId:";
		$sql = $db->bindVars($sql, ':categoryId:', $categoryID, 'integer');
		$rec = $db->Execute($sql);
		if (!$rec->EOF) {
			return $rec->fields['numFilters'];
		}
		return 0;
	}

	function get_products_dropdown( $product_id = 0 ){
		global $db;

		#LOCAL VARIABLES
		$output = '';
		$products_array = array();

		#BUILD AN ARRAY LISTING ALL PRODUCTS WITH NO ATTRIBUTE
		$sql = "SELECT p.`products_id`, pd.`products_name`
				FROM `" . TABLE_PRODUCTS_DESCRIPTION . "` AS pd
					JOIN `" . TABLE_PRODUCTS . "` AS p
						ON p.`products_id` = pd.`products_id`
				WHERE p.`products_status` = '1'";
		if( $product_id > 0 ){
			$sql .= " AND p.`products_id` = :productId:";
			$sql = $db->bindVars($sql, ':productId:', $product_id, 'integer');
		}
		$sql .= " ORDER BY pd.`products_name` ASC";
		$rec = $db->Execute($sql);
		if( $rec->RecordCount() > 1 ){
			while(!$rec->EOF){
				$products_array[] = array(
					'id' => $rec->fields['products_id'],
					'text' => $rec->fields['products_name']
				);
				$rec->MoveNext();
			}

			$output = zen_draw_pull_down_menu('selPID', $products_array) . '<br /><br />' . "\n";
		}else{ #ADD PRODUCT LINK
			$output .= zen_get_products_name( $product_id ) . '<br /><br />' . "\n";
			$output .= zen_draw_hidden_field( 'product_id', $product_id );
		}

		return $output;
	}
	/* RIGHT-SIDE PANE DISPLAYS */
	/*** RIGHT PANE DISPLAY: FILTERS ***/
	function show_view_filter( $fID ){
		global $db;

		$output = '';
		if( $this->fID > 0 ){
			#LOAD SET PARAMETERS
			$parameters = '';
			foreach($_GET as $k=>$v){
				if( !($k == 'page' and $v == 1) ){
					if( $k != 'action' ){
						$parameters .= '&' . $k . '=' . $v;
					}
				}
			}
			$parameters = trim($parameters,"&");

			#LOAD FILTER NAME
			$sql = "SELECT `filter_name`
					FROM `" . TABLE_ADDON_FILTERS . "`
					WHERE `filter_id` = :filterId:";
			$sql = $db->bindVars($sql, ':filterId:', $this->fID, 'integer');
			$rec = $db->Execute($sql);
			if (!$rec->EOF) {
				$output .= $this->tab(4) . '<h4>' . $rec->fields['filter_name'] . '</h4>' . "\n";
			}
			#LOAD FILTER VALUES
			$vID = 0;
			$sql = "SELECT `filter_value_id`, `filter_value`
					FROM `" . TABLE_ADDON_FILTERS_VALUES . "`
					WHERE `filter_id` = :filterId:
					ORDER BY `sort_order`, `filter_value` ASC";
			$sql = $db->bindVars($sql, ':filterId:', $this->fID, 'integer');
			$rec = $db->Execute($sql);
			if (!$rec->EOF) {
				$output .= $this->tab(4) . '<ul>' . "\n";
				while ( !$rec->EOF ) {
					if( $vID == 0 ){
						$vID = $rec->fields['filter_value_id'];
					}
					$output .= $this->tab(5) . '<li>' . $rec->fields['filter_value'] . '</li>' . "\n";
					$rec->MoveNext();
				}
				$output .= $this->tab(4) . '</ul>' . "\n";
			}else{
				$output .= $this->tab(4) . '<p>' . FEEDBACK_NO_FILTER_VALUES . '</p>' . "\n";
			}
			#RIGHT PANES' BUTTONS
			#OPEN
			$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'fID=' . $this->fID . '&vID=' . $vID) . '">
						<button type="button" class="btn btn-xs btn-default" aria-label="Left Align">
							<span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span> ' . BTN_OPEN . '
						</button>
					</a>' . "\n";
			#EDIT
			$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, 'action=edit&' . $parameters) . '">
						<button type="button" class="btn btn-xs btn-success" aria-label="Left Align">
							<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ' . BTN_EDIT . '
						</button>
					</a>' . "\n";

			#DELETE
			if ($rec->RecordCount() <= 0) {
				$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, 'action=delete&' . $parameters) . '">
						<button type="button" class="btn btn-xs btn-danger" aria-label="Left Align">
							<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ' . BTN_DELETE . '
						</button>
					</a>' . "\n";
			}
			$output .= $this->tab(4) . '</div>' . "\n";
		}else{ #FILTER ID IS ZERO
			$output .= '<p>' . FEEDBACK_NO_FILTERS . '</p>' . "\n";
		}
		return $output;
	}

	function show_add_filter(){
		global $db;
		$output = '';

		$output = $this->tab(7) . zen_draw_form('frmAdd', FILENAME_ADDON_FILTERS, '', 'post');
		$output .= $this->tab(8) . '<h4>'. HEAD_ADD .'</h4>
								<table class="table">
									<tbody>
										<tr>
											<th>' . TBL_FILTER_NAME .'</th>
											<td>' . zen_draw_input_field('txtFilter', $this->GetFilterNameFromID( $this->fID ), zen_set_field_length(TABLE_ADDON_FILTERS, 'filter_name') ) . '</td>
										</tr>
										<tr>
											<th>' . TBL_FILTER_STATUS .'</th>
											<td>' .
											zen_draw_radio_field('rdActive', 1, true) .
											zen_draw_label(LBL_ACTIVE, 'rdActive') . '<br />' .
											zen_draw_radio_field('rdActive', 0, false) .
											zen_draw_label(LBL_INACTIVE, 'rdActive') .
											'</td>
										</tr>
										<tr>
											<th>' . TBL_FILTER_SORT_ORDER .'</th>
											<td>' . zen_draw_input_field('txtSortOrder', 0, 'size="3" maxlength="4" style="text-align:center;"') . '</td>
										</tr>
									</tbody>
								</table>' . "\n";
		#SAVE BUTTON
		$output .= $this->tab(8) . zen_draw_hidden_field('action', 'add_confirmed') . "\n";
		$output .= $this->tab(8) . zen_image_submit('button_add.gif', BTN_ADD) . "\n";
		$output .= $this->tab(7) . '</form>' . "\n";

		#CANCEL BUTTON
		$parameters = "";
		if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
			$parameters = "page=" . (int)$_GET['page'];
		}
		$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, $parameters) . '">' .
			zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
			"</a>\n";

		return $output;
	}

	function show_edit_filter( $fID ){
		global $db;

		$output = '';
		if ( (int)$fID > 0 ) {
			$name = '';
			$active = false;
			$sort = 0;
			$sql = "SELECT `filter_name`, `active`, `sort_order`
					FROM `" . TABLE_ADDON_FILTERS . "`
					WHERE `filter_id` = :filterId:";
			$sql = $db->bindVars($sql, ':filterId:', $fID, 'integer');
			$rec = $db->Execute($sql);
			if( !$rec->EOF ){
				$name = $rec->fields['filter_name'];
				$active = $rec->fields['active'];
				$sort = $rec->fields['sort_order'];
			}
			$output = $this->tab(7) . zen_draw_form('frmSave', FILENAME_ADDON_FILTERS, '', 'post');
			$output .= $this->tab(8) . '<h4>'. HEAD_EDIT .'</h4>
									<table class="table">
										<tbody>
											<tr>
												<th>' . TBL_FILTER_NAME .'</th>
												<td>' . zen_draw_input_field('txtFilter', $name, zen_set_field_length(TABLE_ADDON_FILTERS, 'filter_name') ) . '</td>
											</tr><tr>
												<th>' . TBL_FILTER_STATUS .'</th>
												<td>' .
												zen_draw_radio_field( 'rdActive', 1, ( $active==1 ? true : false ) ) .
												zen_draw_label(LBL_ACTIVE, 'rdActive') . '<br />' .
												zen_draw_radio_field( 'rdActive', 0, ( $active==0 ? true : false ) ) .
												zen_draw_label(LBL_INACTIVE, 'rdActive') .
												'</td>
											</tr>
											<tr>
											<th>' . TBL_FILTER_SORT_ORDER .'</th>
											<td>' . zen_draw_input_field('txtSortOrder', $sort, 'size="3" maxlength="4" style="text-align:center;"') . '</td>
										</tr>
										</tbody>
									</table>' . "\n";
			#SAVE BUTTON
			$output .= $this->tab(8) . zen_draw_hidden_field('action','edit_confirmed') . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('fID', $fID) . "\n";
			$output .= $this->tab(8) . zen_image_submit('button_save.gif', IMAGE_SAVE) . "\n";
			$output .= $this->tab(7) . '</form>' . "\n";
			#CANCEL BUTTON
			$parameters = "cID=" . $cID . '&fID=' . $fID;
			if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
				$parameters .= "&page=" . (int)$_GET['page'];
			}
			$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, $parameters) . '">' .
				zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
				"</a>\n";
		} #END IF cID IS SET

		return $output;
	}

	function show_delete_filter( $fID ){
		$output = '';
		if ( $fID > 0 ) {
			#GENERIC PARAMETERS
			$parameters = "";
			if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
				$parameters = "page=" . (int)$_GET['page'];
			}

			$output = $this->tab(7) . zen_draw_form('frmDelete', FILENAME_ADDON_FILTERS, '', 'post');
			$output .= $this->tab(8) . '<h4>'. HEAD_DELETE_CONFIRM .'</h4>' . "\n";
			$output .= $this->tab(8) . '<p>'. FEEDBACK_CONFIRM_DELETE . '</p>' . "\n";
			$output .= $this->tab(8) . '<p><b>'. $this->GetFilterNameFromID($fID) . '</b></p>' . "\n";

			#BUTTONS
			$output .= $this->tab(8) . zen_draw_hidden_field('action','delete_confirmed') . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('fID', $fID) . "\n";
			$output .= $this->tab(8) . zen_image_submit('button_delete.gif', IMAGE_DELETE) . "\n";
			$output .= $this->tab(7) . '</form>' . "\n";
			$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS, $parameters . '&fID=' . $fID) . '">' .
				zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
				"</a>\n";
		} #END IF cID IS SET

		return $output;
	}

	/*** RIGHT PANE DISPLAY: FILTER VALUES ***/
	function show_view_value( $fID, $vID ){
		global $db;

		$output = '';
		if( $vID > 0 ){
			$output .= $this->tab(4) . '<h4>' . $this->GetValueNameFromID( $vID ) . '</h4>' . "\n";

			#LOAD SET PARAMETERS
			$parameters = '';
			$vid_added = false;
			foreach( $_GET as $k => $v ){
				if( $k != 'action' ){
					if( $k == 'page' and $v > 1 ){
						$parameters .= '&' . $k . '=' . $v;
					}else if( $k != 'page' ){
						$parameters .= '&' . $k . '=' . $v;
						if( $k == 'vID' ){
							$vid_added = true;
						}
					}
				}
			}

			if( $vID > 0 and !$vid_added ){
				$parameters .= '&vID=' . $vID;
			}

			$parameters = trim( $parameters, "&" );

			#LOAD FILTER VALUES
			$sql = "SELECT ptf.`product_id`, pd.`products_name`
					FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS ptf
						JOIN `" . TABLE_PRODUCTS_DESCRIPTION . "` AS pd 
							ON ptf.`product_id` = pd.`products_id`
					WHERE `filter_value_id` = :valueId:
					ORDER BY ptf.`sort_order`, pd.`products_name` ASC";
			$sql = $db->bindVars($sql, ':valueId:', $vID, 'integer');
			$rec = $db->Execute($sql);
			$pID = 0;
			if (!$rec->EOF) {
				$output .= $this->tab(4) . '<ul>' . "\n";
				while ( !$rec->EOF ) {
					if( $pID == 0 ){
						$pID = $rec->fields['product_id'];
					}
					$output .= $this->tab(5) . '<li>' . $rec->fields['products_name'] . '</li>' . "\n";
					$rec->MoveNext();
				}
				$output .= $this->tab(4) . '</ul>' . "\n";
			}else{
				$output .= $this->tab(4) . '<p>' . FEEDBACK_NO_PRODUCTS . '</p>' . "\n";
			}
			if( $pID > 0 ){
				$parameters .= '&pID=' . $pID;
			}

			#RIGHT PANES' BUTTONS
			#OPEN
			$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters) . '">
						<button type="button" class="btn btn-xs btn-default" aria-label="Left Align">
							<span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span> ' . BTN_OPEN . '
						</button>
					</a>' . "\n";
			#EDIT
			$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'action=edit&' . $parameters) . '">
						<button type="button" class="btn btn-xs btn-success" aria-label="Left Align">
							<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ' . BTN_EDIT . '
						</button>
					</a>' . "\n";

			#DELETE
			if ($rec->RecordCount() <= 0) {
				$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'action=delete&' . $parameters) . '">
						<button type="button" class="btn btn-xs btn-danger" aria-label="Left Align">
							<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ' . BTN_DELETE . '
						</button>
					</a>' . "\n";
			}
			$output .= $this->tab(4) . '</div>' . "\n";
		}else{ #FILTER ID IS ZERO
			$output .= '<p>' . FEEDBACK_NO_VALUES . '</p>' . "\n";
		}
		return $output;
	}

	function show_add_value($fID, $vID){
		global $db;
		$output = '';

		$output = $this->tab(7) . zen_draw_form('frmAdd', FILENAME_ADDON_FILTERS_VALUES, '', 'post');
		$output .= $this->tab(8) . '<h4>'. HEAD_ADD .'</h4>
								<table class="table">
									<tbody>
										<tr>
											<th>' . TBL_DETAILS_FILTER .'</th>
											<td>' . $this->GetFilterNameFromID( $fID ) . '</td>
										</tr>
										<tr>
											<th>' . TBL_DETAILS_VALUE_NAME .'</th>
											<td>' . zen_draw_input_field('txtValue', $this->GetValueNameFromID($vID), zen_set_field_length(TABLE_ADDON_FILTERS_VALUES, 'filter_value') ) . '</td>
										</tr>
										<tr>
											<th>' . TBL_DETAILS_SORT_ORDER .'</th>
											<td>' . zen_draw_input_field('txtSortOrder', 0, 'size="3" maxlength="4" style="text-align:center;"' ) . '</td>
										</tr>
									</tbody>
								</table>' . "\n";
		#SAVE BUTTON
		$output .= $this->tab(8) . zen_draw_hidden_field('fID', $fID) . "\n";
		$output .= $this->tab(8) . zen_draw_hidden_field('action', 'add_confirmed') . "\n";
		$output .= $this->tab(8) . zen_image_submit('button_add.gif', BTN_ADD) . "\n";
		$output .= $this->tab(7) . '</form>' . "\n";

		#CANCEL BUTTON
		$parameters = "fID=" . $fID . "&vID=" . $vID;
		if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
			$parameters .= "&page=" . (int)$_GET['page'];
		}
		$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters) . '">' .
			zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
			"</a>\n";

		return $output;
	}

	function show_edit_value( $fID, $vID ){
		global $db;

		$name = '';
		$sort = 0;
		$sql = "SELECT `filter_value`, `sort_order`
				FROM `" . TABLE_ADDON_FILTERS_VALUES . "`
				WHERE `filter_value_id` = :valueId:";
		$sql = $db->bindVars($sql, ':valueId:', $vID, 'integer');
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			$name = $rec->fields['filter_value'];
			$sort = $rec->fields['sort_order'];
		}

		$output = '';
		if ( (int)$fID > 0 and (int)$vID > 0 ) {
			$output = $this->tab(7) . zen_draw_form('frmSave', FILENAME_ADDON_FILTERS_VALUES, '', 'post');

			$output .= $this->tab(8) . '<h4>'. HEAD_EDIT .'</h4>
									<table class="table">
										<tbody>
											<tr>
												<th>' . TBL_DETAILS_FILTER .'</th>
												<td>' . $this->GetFilterNameFromID( $fID ) . '</td>
											</tr>
											<tr>
												<th>' . TBL_DETAILS_VALUE_NAME .'</th>
												<td>' . zen_draw_input_field('txtValue', $name, zen_set_field_length(TABLE_ADDON_FILTERS_VALUES, 'filter_value') ) . '</td>
											</tr>
											<tr>
											<th>' . TBL_DETAILS_SORT_ORDER .'</th>
											<td>' . zen_draw_input_field('txtSortOrder', $sort, 'size="3" maxlength="4" style="text-align:center;"' ) . '</td>
										</tr>
										</tbody>
									</table>' . "\n";

			#SAVE BUTTON
			$output .= $this->tab(8) . zen_draw_hidden_field('action','edit_confirmed') . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('fID', $fID) . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('vID', $vID) . "\n";
			$output .= $this->tab(8) . zen_image_submit('button_save.gif', IMAGE_SAVE) . "\n";
			$output .= $this->tab(7) . '</form>' . "\n";
			#CANCEL BUTTON
			$parameters = "cID=" . $cID . '&fID=' . $fID . '&vID=' . $vID;
			if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
				$parameters .= "&page=" . (int)$_GET['page'];
			}
			$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters) . '">' .
				zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
				"</a>\n";
		} #END IF cID IS SET

		return $output;
	}

	function show_delete_value( $fID, $vID ){
		$output = '';
		if ( $fID > 0 and $vID > 0 ) {
			#GENERIC PARAMETERS
			$parameters = 'fID=' . $fID;
			if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
				$parameters .= "&page=" . (int)$_GET['page'];
			}

			$output = $this->tab(7) . zen_draw_form('frmDelete', FILENAME_ADDON_FILTERS_VALUES, '', 'post');
			$output .= $this->tab(8) . '<h4>'. HEAD_DELETE_CONFIRM .'</h4>' . "\n";
			$output .= $this->tab(8) . '<p>'. FEEDBACK_CONFIRM_DELETE . '</p>' . "\n";
			$output .= $this->tab(8) . '<p><b>'. $this->GetValueNameFromID($vID) . '</b></p>' . "\n";

			#BUTTONS
			$output .= $this->tab(8) . zen_draw_hidden_field('action','delete_confirmed') . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('fID', $fID) . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('vID', $vID) . "\n";
			$output .= $this->tab(8) . zen_image_submit('button_delete.gif', IMAGE_DELETE) . "\n";
			$output .= $this->tab(7) . '</form>' . "\n";
			$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters . '&vID=' . $vID) . '">' .
				zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
				"</a>\n";
		} #END IF cID IS SET

		return $output;
	}

	/*** RIGHT PANE DISPLAY: PRODUCTS TO VALUES ***/
	function show_add_product($fID, $vID){
		global $db;
		$output = '';

		$output = $this->tab(7) . zen_draw_form('frmAdd', FILENAME_ADDON_FILTERS_PRODUCTS, '', 'post');
		$output .= $this->tab(8) . '<h4>'. HEAD_ADD .'</h4>
								<table class="table">
									<tbody>
										<tr>
											<th>' . TBL_DETAILS_FILTER .'</th>
											<td>' . $this->GetFilterNameFromID( $fID ) . '</td>
										</tr>
										<tr>
											<th>' . TBL_DETAILS_VALUE .'</th>
											<td>' . $this->GetValueNameFromID( $vID ) . '</td>
										</tr>
										<tr>
											<th>' . TBL_DETAILS_PRODUCT .'</th>
											<td>' . $this->get_products_dropdown( $pID ) . '</td>
										</tr>
									</tbody>
								</table>
								<hr />' . "\n";
		#SAVE BUTTON
		$output .= $this->tab(8) . zen_draw_hidden_field('fID', $fID) . "\n";
		$output .= $this->tab(8) . zen_draw_hidden_field('vID', $vID) . "\n";
		$output .= $this->tab(8) . zen_draw_hidden_field('action', 'add_confirmed') . "\n";
		$output .= $this->tab(8) . zen_image_submit('button_add.gif', BTN_ADD) . "\n";
		$output .= $this->tab(7) . '</form>' . "\n";

		#CANCEL BUTTON
		$parameters = "fID=" . $fID . '&vID=' . $vID;
		if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
			$parameters .= "&page=" . (int)$_GET['page'];
		}
		$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters) . '">' .
			zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
			"</a>\n";

		return $output;
	}

	function show_delete_product( $fID, $vID, $pID ){
		$output = '';
		if ( $vID > 0 and $pID > 0 ) {
			#GENERIC PARAMETERS
			$parameters = "fID=" . $fID . '&vID=' . $vID;
			if( isset($_GET['page']) and (int)$_GET['page'] > 1 ){
				$parameters .= "&page=" . (int)$_GET['page'];
			}

			$output = $this->tab(7) . zen_draw_form('frmDelete', FILENAME_ADDON_FILTERS_PRODUCTS, '', 'post');
			$output .= $this->tab(8) . '<h4>'. HEAD_DELETE_CONFIRM .'</h4>' . "\n";
			$output .= $this->tab(8) . '<p>'. FEEDBACK_CONFIRM_DELETE . '</p>' . "\n";
			$output .= $this->tab(8) . '<p><b>'. $this->GetValueNameFromID($vID) . '</b></p>' . "\n";
			$output .= $this->tab(8) . '<p><b>'. zen_get_products_name( $pID ) . '</b></p>' . "\n";

			#BUTTONS
			$output .= $this->tab(8) . zen_draw_hidden_field('action','delete_confirmed') . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('fID', $fID) . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('vID', $vID) . "\n";
			$output .= $this->tab(8) . zen_draw_hidden_field('pID', $pID) . "\n";
			$output .= $this->tab(8) . zen_image_submit('button_delete.gif', IMAGE_DELETE) . "\n";
			$output .= $this->tab(7) . '</form>' . "\n";
			$output .= $this->tab(8) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters . '&pID=' . $pID) . '">' .
				zen_image_button('button_cancel.gif', IMAGE_CANCEL) .
				"</a>\n";
		}

		return $output;
	}

	function show_view_product( $fID, $vID, $pID ){
		global $db;

		$output = '';
		if( $vID > 0 ){
			$output .= $this->tab(4) . '<h4>' . zen_get_products_name( $pID ) . '</h4>' . "\n";

			#LOAD FILTER ASSOCIATIONS
			$sql = "SELECT f.`filter_name`, f.`filter_id`, v.`filter_value`, v.`filter_value_id`, p.`master_categories_id`
					FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS ptf
						JOIN `" . TABLE_ADDON_FILTERS_VALUES . "` AS v
							ON ptf.`filter_value_id` = v.`filter_value_id`
						JOIN `" . TABLE_ADDON_FILTERS . "` AS f
							ON v.`filter_id` = f.`filter_id`
						JOIN `" . TABLE_PRODUCTS . "` AS p
							ON ptf.`product_id` = p.`products_id`
					WHERE `product_id` = :productId:";
			$sql = $db->bindVars($sql, ':productId:', $pID, 'integer');
			$rec = $db->Execute($sql);
			if (!$rec->EOF) {
				$filter_name = '';
				$filter_value = '';
				$category = $rec->fields['categories_name'];
				$sem = 0;

				$output .= $this->tab(4) . '<p><a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'fID=' . $rec->fields['filter_id']) . '"><b>' . $category . '</b></a></p>' . "\n";
				$output .= $this->tab(4) . '<ul>' . "\n";
				while ( !$rec->EOF ) {
					#CHECK CATEGORY NAME
					if( $rec->fields['categories_name'] != $category ){
						$category = $rec->fields['categories_name'];
						$output .= $this->tab(7) . '</li>' . "\n";
						$output .= $this->tab(6) . '</ul>' . "\n";
						$output .= $this->tab(5) . '</li>' . "\n";
						$output .= $this->tab(4) . '</ul>' . "\n";
						$output .= $this->tab(4) . '<p><a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'fID=' . $rec->fields['filter_id']) . '"><b>' . $category . '</b></a></p>' . "\n";
						$output .= $this->tab(4) . '<ul>' . "\n";
					}

					#FILTER
					if ($filter_name != $rec->fields['filter_name']) {
						$filter_name = $rec->fields['filter_name'];
						if ($sem == 1) {
							$output .= $this->tab(5) . '</ul>' . "\n";
							$output .= $this->tab(6) . '</li>' . "\n";
							$sem = 0;
						}
						$output .= $this->tab(6) . '<li><a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'fID=' . $rec->fields['filter_id'] . '&vID=' . $rec->fields['filter_value_id']) . '">' . $filter_name . "</a>\n";
					}
					#VALUE
					if ($filter_value != $rec->fields['filter_value']) {
						if ($sem == 0) {
							$output .= $this->tab(7) . '<ul>' . "\n";
							$sem = 1;
						}
						$filter_value = $rec->fields['filter_value'];
						if( $rec->fields['filter_value_id'] == $vID ){
							$output .= $this->tab(8) . '<li><b>' . $filter_value . '</b></li>' . "\n";
						}else{
							$output .= $this->tab(8) . '<li><a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'fID=' . $rec->fields['filter_id'] . '&vID=' . $rec->fields['filter_value_id'] . '&pID=' . $pID) . '">' . $filter_value . '</a></li>' . "\n";
						}
					} else {
						$output .= $this->tab(7) . '</li>' . "\n";
					}
					$rec->MoveNext();
				}
				$output .= $this->tab(4) . '</ul>' . "\n";
				$output .= $this->tab(4) . '</li>' . "\n";
				$output .= $this->tab(4) . '</ul>' . "\n";

				#RIGHT PANES' BUTTONS
				#EDIT
				$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_PRODUCT, 'page=1&product_type=1&cPath=' . zen_get_generated_category_path_ids( $rec->fields['master_categories_id']) . '&pID=' . $pID . '&action=new_product') . '" target="_blank">
							<button type="button" class="btn btn-xs btn-default" aria-label="Left Align">
								<span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span> ' . BTN_OPEN . '
							</button>
						</a>' . "\n";
				#DELETE
				if ($rec->RecordCount() <= 0) {
					$output .= $this->tab(4) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'action=delete&' . $parameters) . '">
							<button type="button" class="btn btn-xs btn-danger" aria-label="Left Align">
								<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ' . BTN_DELETE . '
							</button>
						</a>' . "\n";
				}
				$output .= $this->tab(4) . '</div>' . "\n";
			}else{
				$output .= $this->tab(4) . '<p>' . FEEDBACK_NO_PRODUCTS . '</p>' . "\n";
			}
		}else{ #FILTER ID IS ZERO
			$output .= '<p>' . FEEDBACK_NO_VALUES . '</p>' . "\n";
		}
		return $output;
	}

	public function CategoryStatus( $cID ){
		/* BASED ON A CATEGORY ID, CHECK IF THAT CATEGORY IS
		 * ENABLED TO DISPLAY PRODUCT FILTERS.
		 */
		global $db;

		$category_status = false;
		$cID = $this->SanitizeString( $cID );
		$sql = "SELECT `filters_status`
				FROM `" . TABLE_ADDON_FILTERS_CATEGORIES . "`
				WHERE `categories_id` = :categoryId:";
		$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
		$rec = $db->Execute( $sql );
		if( !$rec->EOF ){
			if( $rec->fields['filters_status'] == 1 ){
				$category_status = true;
			}
		}

		return $category_status;
	}

	public function GetCatStatusBtn( $cID ){
		/* BASED ON A CATEGORY ID, OUTPUTS THE RED/GREEN BUTTON
		 * THAT SWITCHES THE FILTER STATUS ON A GIVEN CATEGORY.
		 */
		$btn = '';

		$parameters = 'action=filter_status&cID=' . $cID;
		if( isset($_GET['page']) and !empty($_GET['page']) ){
			$parameters .= '&page=' . $_GET['page'];
		}
		if( isset($_GET['search']) && !empty($_GET['search']) ){
			$parameters .= '&search=' . $_GET['search'];
		}
		if( isset($_GET['cPath']) and !empty($_GET['cPath'])){
			$parameters .= '&cPath=' . $_GET['cPath'];
		}
		if( $this->CategoryStatus( $cID ) ){
			$btn = '<a href="' . zen_href_link(FILENAME_CATEGORIES, $parameters) . '" style="float:right">' . zen_image(DIR_WS_IMAGES . 'icon_filters_on.png', IMAGE_ICON_STATUS_ON) . '</a>';
		}else{
			$btn = '<a href="' . zen_href_link(FILENAME_CATEGORIES, $parameters) . '" style="float:right">' . zen_image(DIR_WS_IMAGES . 'icon_filters_off.png', IMAGE_ICON_STATUS_OFF) . '</a>';
		}

		return $btn;
	}

	public function SetCategoryStatus( $cID ){
		/* BASED ON A CATEGORY ID, SWITCH ITS FILTER STATUS
		 * BETWEEN ENABLED AND DISABLED. IF ENABLING, ENABLE
		 * ALL SUB CATEGORIES AS WELL.
		 */
		global $db;

		$execute_enable_category = true;
		$cID = $this->SanitizeString( $cID );
		$sql = "SELECT `filters_status`
				FROM `" . TABLE_ADDON_FILTERS_CATEGORIES . "`
				WHERE `categories_id` = :categoryId:";
		$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
		$rec = $db->Execute( $sql );
		if( !$rec->EOF ){ #CATEGORY ID AVAILABLE
			if( $rec->fields['filters_status'] == 1 ){ #ENABLED: DISABLE CAT
				$sql = "UPDATE `" . TABLE_ADDON_FILTERS_CATEGORIES . "`
						SET `filters_status` = 0
						WHERE `categories_id` = :categoryId:";
				$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
				$db->Execute($sql);
				$execute_enable_category = false;
			}
		}
		if( $execute_enable_category ){ #ALSO ENABLE SUB CATEGORIES
			$category_ids = $this->GetAllSubCatIDs( $cID );
			foreach( $category_ids as $catID ){
				$this->EnableCategory( $catID );
			}
		}
	}

	private function EnableCategory( $cID ){
		/* ENABLE CATEGORY. IF CATEGORY NOT IN TABLE,
		 * ADD; OTHERWISE, UPDATE STATUS TO ENABLED.
		 */
		global $db;

		$cID = $this->SanitizeString( $cID );
		$sql = "SELECT `filters_status`
				FROM `" . TABLE_ADDON_FILTERS_CATEGORIES . "`
				WHERE `categories_id` = :categoryId:";
		$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
		$rec = $db->Execute( $sql );
		if( !$rec->EOF ){ #CATEGORY ID AVAILABLE
			$sql = "UPDATE `" . TABLE_ADDON_FILTERS_CATEGORIES . "`
					SET `filters_status` = 1
					WHERE `categories_id` = :categoryId:";
			$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
		}else{
			$sql = "INSERT INTO `" . TABLE_ADDON_FILTERS_CATEGORIES . "`
					(`categories_id`,
					 `date_added`,
					 `last_modified`,
					 `modified_by`,
					 `filters_status`)
					VALUES
					(:categoryId:,
					 '" . date("Y-m-d H:i:s") . "',
					 '" . date("Y-m-d H:i:s") . "',
					 :adminId:,
					 '1')";
			$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
			$sql = $db->bindVars($sql, ':adminId:', (int)$_SESSION['admin_id'], 'integer');
		}

		$db->Execute($sql);
	}

	private function GetAllSubCatIDs( $cID ){
		/* BASED ON A CATEGORY ID, RETURN AN ARRAY CONTAINING
		 * ALL ACTIVE SUB CATEGORIES UNDER THAT CATEGORY.
		 */
		global $db;

		$cat_ar = array();
		$cID = $this->SanitizeString( $cID );
		$cat_ar[] = $cID;
		$sql = "SELECT `categories_id`
				FROM `" . TABLE_CATEGORIES . "`
				WHERE `parent_id` = :categoryId:
				AND `categories_status` = 1";
		$sql = $db->bindVars($sql, ':categoryId:', $cID, 'integer');
		$rec = $db->Execute($sql);
		while( !$rec->EOF ){
			$cat_ar = array_merge($cat_ar, $this->GetAllSubCatIDs( $rec->fields['categories_id'] ));
			$rec->MoveNext();
		}
		return $cat_ar;
	}

	/*********************
	 ** PRIVATE METHODS **
	 *********************/
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
	}
} #END: ProductsFiltersClass
