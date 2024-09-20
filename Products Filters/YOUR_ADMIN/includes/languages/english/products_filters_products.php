<?php
/**
 * Products Filters plugin for Zen Cart. Value to products page - language definition.
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
define('LIMIT_MAX_ROWS', '25');

#PAGE
define('HEADING_TITLE', 'Products Filters (3 of 3) - Filter Values to Products Manager');
define('HEADING_SUBTITLE', 'Selected Product: ');
define('BREADCRUMB_DIVIDER', ' > ');

#MAIN TABLE HEADERS
define('TBL_HEAD_PRODUCTS', 'Products');
define('TBL_HEAD_ID', 'Part#');
define('TBL_HEAD_QUANTITY', 'Qtty');
define('TBL_HEAD_STATUS', 'Status');
define('TBL_HEAD_NUM_VALUES', 'Filters');
define('TBL_HEAD_ACTIONS', 'Actions');

#RIGHT PANE TABLE
define('TBL_DETAILS_HEAD', 'Product Details');
define('TBL_DETAILS_CATEGORY', 'Category: ');
define('TBL_DETAILS_FILTER', 'Filter: ');
define('TBL_DETAILS_VALUE', 'Value: ');
define('TBL_DETAILS_PRODUCT', 'Product: ');

#BUTTONS
define('BTN_OPEN', 'Open');
define('BTN_DELETE', 'Delete');
define('BTN_ADD', 'Add Product');
define('BTN_EDIT', 'Edit');
define('BTN_BACK', 'Back to Values');

#FEEDBACK
define('FEEDBACK_NO_PRODUCTS', 'There are no product available to list.');
define('FEEDBACK_CONFIRM_DELETE', 'Are you sure you want to disconnect that product from this value?');

define('HEAD_DELETE_CONFIRM', 'Confirm Delete');
define('HEAD_EDIT', 'Rename Value');
define('HEAD_ADD', 'Add Value');

#NAVIGATION
define('PAGINATION_LABEL', 'Displaying <b>%s</b> to <b>%s</b> (of <b>%s</b> product associations)');
