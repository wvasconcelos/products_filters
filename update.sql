SELECT @configuration_group_id := `configuration_group_id` 
FROM `configuration` 
WHERE `configuration_key` = 'FILTER_BY_SPECIALS';

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

('Display Filters by Default', 
'FILTER_DISPLAY_DEFAULT',
'Yes',
'If a user opens a category that has filters enabled, display filters.',
@configuration_group_id, 
6, 
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