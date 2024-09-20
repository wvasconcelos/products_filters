--DELETE ANY PREVIOUS INSTALATION
SET @cdid = 0;
SELECT (@cdid := configuration_group_id) FROM configuration_group WHERE configuration_group_title LIKE 'Products Filters';;

DELETE FROM configuration_group WHERE configuration_group_id = @cdid;
DELETE FROM configuration WHERE configuration_group_id = @cdid;
DELETE FROM admin_pages WHERE page_key LIKE 'configProductsFilters';
DELETE FROM admin_pages WHERE page_key LIKE 'addonFilters%';

--CREATE A CONFIGURATION GROUP ID
INSERT INTO configuration_group (configuration_group_id, configuration_group_title, configuration_group_description, sort_order, visible) VALUES (NULL, 'Products Filters', 'Configure Products Filters preferences.', '1', '1');
SET @configuration_group_id=last_insert_id();
UPDATE configuration_group SET sort_order = @configuration_group_id WHERE configuration_group_id = @configuration_group_id;

--Find the sort order value of the last page
SET @sort_order = 0;
SELECT (@sort_order := (sort_order + 1)) FROM admin_pages WHERE menu_key = 'catalog' ORDER BY sort_order DESC LIMIT 1;

--Register the new plugin in the catalog section of Admin
INSERT IGNORE INTO admin_pages (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES ('addonFilters','BOX_ADDON_FILTERS','FILENAME_ADDON_FILTERS','','catalog','Y',@sort_order);
INSERT IGNORE INTO admin_pages (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES ('addonFiltersValues','BOX_ADDON_FILTERS_VALUES','FILENAME_ADDON_FILTERS_VALUES','','catalog','N',@sort_order);
INSERT IGNORE INTO admin_pages (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES ('addonFiltersProducts','BOX_ADDON_FILTERS_PRODUCTS','FILENAME_ADDON_FILTERS_PRODUCTS','','catalog','N',@sort_order);
INSERT IGNORE INTO admin_pages (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES ('addonFiltersAdd','BOX_ADDON_FILTERS_ADD','FILENAME_ADDON_FILTERS_ADD','','catalog','N',@sort_order);
INSERT IGNORE INTO admin_pages (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES ('addonFiltersDelete','BOX_ADDON_FILTERS_DELETE','FILENAME_ADDON_FILTERS_DELETE','','catalog','N',@sort_order);
--REGISTER NEW CONFIGURATION PAGE
INSERT IGNORE INTO admin_pages 
(page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order)
VALUES 
('configProductsFilters','BOX_CONFIGURATION_PRODUCTS_FILTERS','FILENAME_CONFIGURATION',CONCAT('gID=',@configuration_group_id),'configuration','Y',@configuration_group_id);

--CREATE REQUIRED TABLES
CREATE TABLE IF NOT EXISTS addon_filters (
	`filter_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`filter_name` varchar(128) NOT NULL,
	`active` tinyint(1) NOT NULL DEFAULT '1',
	`sort_order` int(11) NOT NULL DEFAULT '0',
	`created_by` int(11) NOT NULL,
	`created_on` timestamp NOT NULL,
	`modified_by` int(11) NOT NULL,
	`last_modified` timestamp NOT NULL,
PRIMARY KEY (`filter_id`)
);

CREATE TABLE IF NOT EXISTS addon_filters_values (
	`filter_value_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`filter_id` int(11) NOT NULL,
	`filter_value` varchar(128) NOT NULL,
	`sort_order` int(11) NOT NULL DEFAULT '0',
	`created_by` int(11) NOT NULL,
	`created_on` timestamp NOT NULL,
	`modified_by` int(11) NOT NULL,
	`last_modified` timestamp NOT NULL,
	PRIMARY KEY (`filter_value_id`)
);

CREATE TABLE IF NOT EXISTS addon_filters_products (
	`product_id` int(11) NOT NULL,
	`filter_value_id` int(11) NOT NULL,
	`sort_order` int(11) NOT NULL DEFAULT '0',
	`created_by` int(11) NOT NULL,
	`created_on` timestamp NOT NULL,
	PRIMARY KEY (`product_id`,`filter_value_id`)
);

CREATE TABLE IF NOT EXISTS addon_filters_categories (
 `categories_id` int(11) NOT NULL,
 `date_added` timestamp NOT NULL,
 `last_modified` datetime NOT NULL,
 `modified_by` int(11) NOT NULL DEFAULT '0',
 `filters_status` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`categories_id`)
);

--CREATE RECORDS INTO THE CONFIGURATION TABLE
INSERT IGNORE INTO configuration 
(configuration_title, 
configuration_key, 
configuration_value, 
configuration_description, 
configuration_group_id, 
sort_order, 
date_added, 
set_function)
VALUES

('Filter by Manufacturers', 
'FILTER_BY_MANUFACTURERS',
'Yes', 
'Enable filter products based on manufacturers name?',
@configuration_group_id, 
0, 
CURRENT_TIMESTAMP, 
'zen_cfg_select_option(array(\'Yes\', \'No\'),'),

('Filter by Price Ranges', 
'FILTER_BY_PRICES',
'Yes', 
'Enable filter products using price ranges?', 
@configuration_group_id, 
1, 
CURRENT_TIMESTAMP,
'zen_cfg_select_option(array(\'Yes\', \'No\'),'),

('Filter by Ratings', 
'FILTER_BY_RATINGS',
'Yes', 
'Enable filter products based on Customer Ratings?', 
@configuration_group_id, 
2, 
CURRENT_TIMESTAMP,
'zen_cfg_select_option(array(\'Yes\', \'No\'),'),

('Filter Specials', 
'FILTER_BY_SPECIALS',
'Yes', 
'Enable filter products that are on-sale (specials)?', 
@configuration_group_id, 
3, 
CURRENT_TIMESTAMP,
'zen_cfg_select_option(array(\'Yes\', \'No\'),'),

('Number of Break Points', 
'FILTER_BREAK_POINTS_NUMBER',
'5', 
'If filter by price ranges is enabled, how many price range break points do you want?',
@configuration_group_id, 
4, 
CURRENT_TIMESTAMP, 
'zen_cfg_select_option(array(\'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\'),'),

('Display Filters by Default', 
'FILTER_DISPLAY_DEFAULT',
'Yes',
'If a user opens a category that has filters enabled, display filters.',
@configuration_group_id, 
5,
CURRENT_TIMESTAMP, 
'zen_cfg_select_option(array(\'Yes\', \'No\'),'),

('Display Items Counter on Filters', 
'FILTER_SHOW_ITEMS_COUNTER',
'Yes',
'Display the number of items associated with each filter?',
@configuration_group_id, 
7, 
CURRENT_TIMESTAMP, 
'zen_cfg_select_option(array(\'Yes\', \'No\'),'),

('Display Items Counter on Options', 
'FILTER_OPTION_SHOW_ITEMS_COUNTER',
'Yes',
'Display the number of items associated with each option?',
@configuration_group_id, 
8, 
CURRENT_TIMESTAMP, 
'zen_cfg_select_option(array(\'Yes\', \'No\'),');
