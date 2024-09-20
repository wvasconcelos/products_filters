<?php
/**
 * Products Filters plugin for Zen Cart. Filter to values administration.
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
if( $filter->fID == 0 ){
	zen_redirect( zen_href_link(FILENAME_ADDON_FILTERS, '') );
}

#DATABASE MANIPULATION PRIOR TO LOADING
if ($filter->action != "") {
	switch ( $filter->action ) {
		case 'add_confirmed':
			if ($filter->fID > 0 and $filter->value_name != '') { #ADD FILTER
				$sql_data_array = array(
					'filter_id' => zen_db_prepare_input($filter->fID),
					'filter_value' => $filter->value_name,
					'sort_order' => zen_db_prepare_input( $filter->sort_order ),
					'created_by' => $_SESSION['admin_id'],
					'created_on' => date("Y-m-d H:i:s"),
					'modified_by' => $_SESSION['admin_id'],
					'last_modified' => date("Y-m-d H:i:s"));
				zen_db_perform(TABLE_ADDON_FILTERS_VALUES, $sql_data_array); //ADD FILTER
				$filter->vID = $db->Insert_ID();
				
				$parameters = 'fID=' . $filter->fID . '&vID=' . $filter->vID;
				if( isset($_POST['page']) and (int)$_POST['page'] > 1 ){
					$parameters .= "&page=" . (int)$_POST['page'];
				}
				zen_redirect( zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters) );
			}
			break;
		case 'edit_confirmed':
			if ( (int)$filter->fID > 0 and (int)$filter->vID > 0 and $filter->value_name != "") { #EDIT VALUE
				$sql_data_array = array(
					'filter_value' => $filter->value_name,
					'sort_order' => zen_db_prepare_input( $filter->sort_order ),
					'modified_by' => $_SESSION['admin_id'],
					'last_modified' => date("Y-m-d H:i:s"));
				zen_db_perform(TABLE_ADDON_FILTERS_VALUES, $sql_data_array, "update", "filter_value_id = '" . $filter->vID . "'");
				$parameters = 'fID=' . $filter->fID . '&vID=' . $filter->vID;
				if(isset($_POST['page']) and (int)$_POST['page'] > 1 ){
					$parameters .= "&page=" . (int)$_POST['page'];
				}
				zen_redirect( zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters) );
			}
			break;
		case 'delete_confirmed':
			if ($filter->fID > 0) { #DELETE FILTER
				$sql = "DELETE 
						FROM `" . TABLE_ADDON_FILTERS_VALUES . "`
						WHERE `filter_value_id` = :valueId:";
				$sql = $db->bindVars($sql, ':valueId:', $filter->vID, 'integer');
				$db->Execute($sql);
				$parameters = 'fID=' . $filter->fID;
				if(isset($_POST['page']) and (int)$_POST['page'] > 1 ){
					$parameters .= "&page=" . (int)$_POST['page'];
				}
				zen_redirect( zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters) );
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
										<td class="dataTableHeadingContent"><?php echo TBL_HEAD_VALUES; ?></td>
										<td class="dataTableHeadingContent" align="center"><?php echo TBL_HEAD_PRODUCTS; ?></td>
										<td class="dataTableHeadingContent" align="center"><?php echo TBL_HEAD_SORT; ?></td>
										<td class="dataTableHeadingContent" align="right"><?php echo TBL_HEAD_ACTIONS; ?></td>
									</tr>
<?php
	#LOAD RECORDS
	$sql =  "SELECT `filter_value_id`, `filter_value`, `sort_order`
			 FROM `" . TABLE_ADDON_FILTERS_VALUES . "` 
			 WHERE `filter_id` = :filterId:
			 ORDER BY `sort_order` ASC";
	$sql = $db->bindVars($sql, ':filterId:', $filter->fID, 'integer');
	$rec = $db->Execute($sql);
	$rec_count = 0;
	$total_records = 0;
	if (!$rec->EOF) {
		$total_records = $rec->RecordCount();
		$pages_split = new splitPageResults( $_GET['page'], LIMIT_MAX_ROWS, $sql, $total_records );
		echo $pages_split->display_count($total_records, LIMIT_MAX_ROWS, (int)$_GET['page'], PAGINATION_LABEL);
		$rec = $db->Execute($sql);
		while (!$rec->EOF) {
			$rec_count++;
			if ($filter->vID == 0) { #DEFAULT: FIRST IN THE LIST
				$filter->vID = $rec->fields['filter_value_id'];
			}
			$filterValuesCount = $filter->GetProductsCount( $rec->fields['filter_value_id'] );
			
			$parameters = 'fID=' . $filter->fID . '&vID=' . $rec->fields['filter_value_id'];
			if ((int)$_GET['page'] > 1){
				$parameters .= "&page=" . (int)$_GET['page'];
			}
			
			if ( $rec->fields['filter_value_id'] == $filter->vID ) {
				$tr = ' id="defaultSelected" class="dataTableRowSelected"';
			} else {
				$tr = ' class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters) . '\';"';
			}
?>
									<tr<?php echo $tr; ?>>
										<td class="dataTableContent"><?php echo $rec_count; ?></td>
										<td class="dataTableContent"><?php echo $rec->fields['filter_value']; ?></td>
										<td class="dataTableContent" align="center"><?php echo $filter->GetProductsCount( $rec->fields['filter_value_id'] ); ?></td>
										<td class="dataTableContent" align="center"><?php echo $rec->fields['sort_order']; ?></td>
										<td class="dataTableContent" align="right">
<?php
			#DELETE BUTTON
			if ( $filterValuesCount <= 0 ) {
				echo $filter->tab(11) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'action=delete&' . $parameters) . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . "</a>";
			}

			#EDIT BUTTON
			echo $filter->tab(11) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'action=edit&' . $parameters) . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . "</a>";
			
			#OPEN
			echo $filter->tab(11) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_PRODUCTS, $parameters) . '">' . zen_image(DIR_WS_IMAGES . 'icons/folder.gif', BTN_OPEN) . '</a>';
			
			#SELECTOR
			if ( $rec->fields['filter_value_id'] == $filter->vID ) {
				echo $filter->tab(11) . zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_EDIT);
			} else {
				echo $filter->tab(11) . '<a href="' . zen_href_link(FILENAME_ADDON_FILTERS_VALUES, $parameters) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
										<td colspan=2><?php echo $pages_split->display_count($total_records, LIMIT_MAX_ROWS, (int)$_GET['page'], PAGINATION_LABEL); ?></td>
										<td align=left><?php echo $pages_split->display_links($total_records, LIMIT_MAX_ROWS, $highest_page_number, $_GET['page'], $parameters); ?></td>
										<td align=right colspan=2>
											<a href="<?php echo zen_href_link(FILENAME_ADDON_FILTERS, 'fID=' . $filter->fID); ?>">
												<button type="button" class="btn btn-xs btn-default" aria-label="Left Align">
													<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> <?php echo BTN_BACK; ?>
												</button>
											</a>
											<a href="<?php echo zen_href_link(FILENAME_ADDON_FILTERS_VALUES, 'fID=' . $filter->fID . '&action=add'); ?>">
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
					switch ($filter->action) {
						case 'add':
							echo $filter->show_add_value( $filter->fID, $filter->vID );
							break;
						case 'edit':
							echo $filter->show_edit_value( $filter->fID, $filter->vID );
							break;
						case 'delete':
							echo $filter->show_delete_value( $filter->fID, $filter->vID );
							break;
						default:
							echo $filter->show_view_value( $filter->fID, $filter->vID );
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
