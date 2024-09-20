<?php
/**
 * Products Filters plugin for Zen Cart. Filter admin page - language definition.
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
define('HEADING_TITLE', 'Products Filters (1 of 3) - Filter Management');
define('HEADING_SUBTITLE', 'Selected Filter: ');
define('BREADCRUMB_DIVIDER', ' > ');

#MAIN TABLE HEADERS
define('TBL_HEAD_FILTERS', 'Filters');
define('TBL_HEAD_VALUES', 'Values');
define('TBL_HEAD_STATUS', 'Status');
define('TBL_HEAD_SORT', 'Sort');
define('TBL_HEAD_ACTIONS', 'Actions');

#RIGHT PANE TABLE
define('TBL_DETAILS_HEAD', 'Filter Details');
define('TBL_FILTER_NAME', 'Name: ');
define('TBL_FILTER_STATUS', 'Status: ');
define('TBL_FILTER_SORT_ORDER', 'Sort: ');

#BUTTONS
define('BTN_OPEN', 'Open');
define('BTN_DELETE', 'Delete');
define('BTN_ADD', 'Add Filter');
define('BTN_EDIT', 'Edit');
define('BTN_STATUS', 'Status');

#LABELS
define('LBL_ACTIVE','Active');
define('LBL_INACTIVE','Inactive');

#FEEDBACK
define('FEEDBACK_NO_FILTER_VALUES', 'Values for this filter are not available yet. Click the open button to add one.');
define('FEEDBACK_NO_FILTERS', 'There are no filters available to display yet.');
define('FEEDBACK_CONFIRM_DELETE', 'Are you sure you want to delete this filter?');

define('HEAD_DELETE_CONFIRM', 'Confirm Delete');
define('HEAD_EDIT', 'Rename Filter');
define('HEAD_ADD', 'Add Filter');

#NAVIGATION
define('PAGINATION_LABEL', 'Displaying <b>%s</b> to <b>%s</b> (of <b>%s</b> filters)');
