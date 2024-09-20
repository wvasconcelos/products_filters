<?php
/**
 * Products Filters plugin for Zen Cart. Filter value to products administration.
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
require('includes/application_top.php');

$filter = new ProductsFiltersClass();
$filter->update(); #INITIALIZE

#FILTER ID REQUIRED
if( $filter->vID == 0 ){
	zen_redirect( zen_href_link(FILENAME_ADDON_FILTERS, '') );
}

#DATABASE MANIPULATION PRIOR TO LOADING
if ( $filter->action != '' ) {
	switch ( $filter->action ) {
		case 'add_confirmed':
			if ( $filter->pID > 0 and $filter->vID > 0 ) { #ADD CONNECTION
				#CHECK IF NOT DUPLICATE
				$sql = "SELECT `product_id`
						FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "`
						WHERE `product_id` = :productId:
							AND `filter_value_id` = :valueId:";
				$sql = $db->bindVars($sql, ':productId:', $filter->pID, 'integer');
				$sql = $db->bindVars($sql, ':valueId:', $filter->vID, 'integer');
				$rec = $db->Execute($sql);
				if( $rec->EOF ){
					$sql_data_array = array(
						'product_id' => $filter->pID,
						'filter_value_id' => $filter->vID,
						'created_by' => $_SESSION['admin_id'],
						'created_on' => date("Y-m-d H:i:s"));
					zen_db_perform(TABLE_ADDON_FILTERS_PRODUCTS, $sql_data_array); //INSERT (NO PARAMETERS)
					$filter->pID = $db->Insert_ID();
				}
			}
			$parameters = 'fID=' . $filter->fID . '&vID=' . $filter->vID;
			if( $filter->pID > 0 ){
				$parameters .= '&pID=' . $filter->pID;
			}
			if( isset($_POST['page']) and (int)$_POST['page'] > 1 ){
				$parameters .= "&page=" . (int)$_POST['page'];
			}
			zen_redirect( zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters) );
			break;
		case 'delete_confirmed':
			if ( $filter->pID > 0 and $filter->vID > 0 ) { #DELETE CONNECTION
				$sql = "DELETE FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "`
						WHERE `product_id` = :productId:
							AND `filter_value_id` = :valueId:";
				$sql = $db->bindVars($sql, ':productId:', $filter->pID, 'integer');
				$sql = $db->bindVars($sql, ':valueId:', $filter->vID, 'integer');
				
				$db->Execute($sql);
				$parameters = 'fID=' . $filter->fID . '&vID=' . $filter->vID;
				if(isset($_POST['page']) and (int)$_POST['page'] > 1 ){
					$parameters .= "&page=" . (int)$_POST['page'];
				}
				zen_redirect( zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters) );
			}
			break;
		default:
			break;
	}
}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
		<title><?php echo HEADING_TITLE; ?></title>
		<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
		<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
		<script language="javascript" src="includes/menu.js"></script>
		<script language="javascript" src="includes/general.js"></script>

		<link rel="stylesheet" type="text/css" href="includes/css/jquery-ui.min.css">
		<script language="javascript" src="includes/javascript/jquery-1.12.4.min.js"></script>
		<script language="javascript" src="includes/javascript/jquery-ui.min.js"></script>
		
		<!--<link rel="stylesheet" type="text/css" href="includes/css/bootstrap.min.css">-->
		<script language="javascript" src="includes/javascript/bootstrap.min.js"></script>
		
		<link rel="stylesheet" type="text/css" href="includes/css/ProductsFilters.css">
		
		<script type="text/javascript">
			<!--
			function init()
			{
				cssjsmenu('navbar');
				if (document.getElementById)
				{
					var kill = document.getElementById('hoverJS');
					kill.disabled = true;
				}
			}
			// -->
		</script>
	</head>
	<body onLoad="init()">
		<!-- header //-->
		<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
		<!-- header_eof //-->

		<!-- body //-->
		<table border="0" width="100%" cellspacing="2" cellpadding="2">
			<tr>
				<td colspan=2>
					<table border="0" width="100%" cellspacing="0" cellpadding="0">
						<tr>
							<td class="pageHeading">
								<?php echo HEADING_TITLE; ?>
							</td>
						</tr><tr>
							<td id="headingSubtitle">
								<?php echo HEADING_SUBTITLE; ?>
								<?php echo $filter->GetBreadcrumb(); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="75%" valign="top">
					<table border="0" width="100%" cellspacing="0" cellpadding="0" id="main-table">
						<tr>
							<td valign="top">
								<table class="table">
									<tr class="dataTableHeadingRow">
										<td class="dataTableHeadingContent" style="width:40px;">#</td>
										<td class="dataTableHeadingContent"><?php echo TBL_HEAD_PRODUCTS; ?></td>
										<td class="dataTableHeadingContent"><?php echo TBL_HEAD_ID; ?></td>
										<td class="dataTableHeadingContent" align="center"><?php echo TBL_HEAD_QUANTITY; ?></td>
										<td class="dataTableHeadingContent" align="center"><?php echo TBL_HEAD_STATUS; ?></td>
										<td class="dataTableHeadingContent" align="center"><?php echo TBL_HEAD_NUM_VALUES; ?></td>
										<td class="dataTableHeadingContent" align="right"><?php echo TBL_HEAD_ACTIONS; ?></td>
									</tr>
<?php
	#LOAD RECORDS
	$sql =  "SELECT pd.`products_name`, p.`products_id`, p.`products_quantity`, p.`products_model`, p.`products_status`
			 FROM `" . TABLE_ADDON_FILTERS_PRODUCTS . "` AS ptf
				JOIN `" . TABLE_PRODUCTS . "` AS p
					ON p.`products_id` = ptf.`product_id`
				JOIN `" . TABLE_PRODUCTS_DESCRIPTION . "` AS pd
					ON pd.`products_id` = p.`products_id`
			 WHERE ptf.`filter_value_id` = :valueId:
				AND p.`products_id` IS NOT NULL
			 ORDER BY `products_name` ASC";
	$sql = $db->bindVars($sql, ':valueId:', $filter->vID, 'integer');
	$rec = $db->Execute($sql);
	$rec_count = 0;
	$total_records = 0;
	$icon_on = zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
	$icon_off = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
	if (!$rec->EOF) {
		$total_records = $rec->RecordCount();
		$pages_split = new splitPageResults( $_GET['page'], LIMIT_MAX_ROWS, $sql, $total_records );
		echo $pages_split->display_count($total_records, LIMIT_MAX_ROWS, (int)$_GET['page'], PAGINATION_LABEL);
		$rec = $db->Execute($sql);
		while (!$rec->EOF) {
			$rec_count++;
			if ($filter->pID == 0) { #DEFAULT: FIRST IN THE LIST
				$filter->pID = $rec->fields['products_id'];
			}
			#PARAMETERS
			$parameters = 'fID=' . $filter->fID . '&vID=' . $filter->vID . '&pID=' . $rec->fields['products_id'];
			if ((int)$_GET['page'] > 1){
				$parameters .= "&page=" . (int)$_GET['page'];
			}
			#FIGURE OUT SELECTED ROW
			if ( $rec->fields['products_id'] == $filter->pID ) {
				$tr = ' id="defaultSelected" class="dataTableRowSelected"';
			} else {
				$tr = ' class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters) . '\';"';
			}
?>
									<tr<?php echo $tr; ?>>
										<td class="dataTableContent"><?php echo $rec_count; ?></td>
										<td class="dataTableContent"><?php echo $rec->fields['products_name']; ?></td>
										<td class="dataTableContent"><?php echo $rec->fields['products_model']; ?></td>
										<td class="dataTableContent" align="center"><?php echo $rec->fields['products_quantity']; ?></td>
										<td class="dataTableContent" align="center"><?php echo ( $rec->fields['products_status'] == 1 ? $icon_on : $icon_off ); ?></td>
										<td class="dataTableContent" align="center"><?php echo $filter->GetProductsToValuesCount( $rec->fields['products_id'] ); ?></td>
										<td class="dataTableContent" align="right">
<?php
			#DELETE BUTTON
			echo $filter->tab(11) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'action=delete&' . $parameters) . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . "</a>";

			echo ' &nbsp;&nbsp; ';
			#SELECTOR
			if ( $rec->fields['products_id'] == $filter->pID ) {
				echo $filter->tab(11) . zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_EDIT);
			} else {
				echo $filter->tab(11) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
			}
?>
										</td>
									</tr>
<?php
			$rec->MoveNext();
		}
	}else{ #NO RECORDS AVAILABLE: DISPLAY ZERO
		$pages_split = new splitPageResults( $_GET['page'], LIMIT_MAX_ROWS, $sql, $total_records );
	}
	
	$highest_page_number = ceil( $total_records / LIMIT_MAX_ROWS );
?>
									<tr>
										<td colspan="2"><?php echo $pages_split->display_count($total_records, LIMIT_MAX_ROWS, (int)$_GET['page'], PAGINATION_LABEL); ?></td>
										<td align="left" colspan="3"><?php echo $pages_split->display_links($total_records, LIMIT_MAX_ROWS, $highest_page_number, $_GET['page'], $parameters); ?></td>
										<td align="right" colspan="2">
											<a href="<?php echo zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'fID=' . $filter->fID . '&vID=' . $filter->vID); ?>">
												<button type="button" class="btn btn-xs btn-default" aria-label="Left Align">
													<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> <?php echo BTN_BACK; ?>
												</button>
											</a>
											<a href="<?php echo zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, 'fID=' . $filter->fID . '&vID=' . $filter->vID . '&action=add'); ?>">
												<button type="button" class="btn btn-xs btn-success" aria-label="Left Align">
													<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo BTN_ADD; ?>
												</button>
											</a>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td width="25%" valign="top">
					<table border="0" width="100%" cellspacing="0" cellpadding="2">
						<tr class="infoBoxHeading">
							<td class="infoBoxHeading"><?php echo TBL_DETAILS_HEAD; ?></td>
						</tr>
					</table>
					<table border="0" width="100%" cellspacing="0" cellpadding="2">
						<tr>
							<td class="infoBoxContent">
								<div id="preview_content">
<!-- START RIGHT SIDE PANE -->
					<?php
					switch ( $filter->action ) {
						case 'add':
							echo $filter->show_add_product( $filter->fID, $filter->vID );
							break;
						case 'delete':
							echo $filter->show_delete_product( $filter->fID, $filter->vID, $filter->pID );
							break;
						default:
							echo $filter->show_view_product( $filter->fID, $filter->vID, $filter->pID );
							break;
					} #END SWITCH ACTION
					?>
								</div>
								<br />
							</td>
						</tr>
					</table>
					<!-- END RIGHT SIDE PANE -->
				</td>
			</tr>
		</table>
		<!-- body_eof //-->
		<!-- footer //-->
		<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
		<!-- footer_eof //-->
		<br>
	</body>
</html>
<?php
	require(DIR_WS_INCLUDES . 'application_bottom.php');
