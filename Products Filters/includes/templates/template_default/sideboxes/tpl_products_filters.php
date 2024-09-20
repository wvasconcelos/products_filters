<?php
/**
 * Products Filters plugin for Zen Cart. Sidebox template definition.
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
	$content = '';
	
	$filter_category_switch = '';
	if( isset($filters) ){
		$filter_category_switch = $filters->filter_switch_html;
	}
	
	if( isset($filters) ){
		if( $filters->replace_cat_with_filters ){
			if( $filters->main_page != 'product_info' ){
				#NOT IN PRODUCT DESCRIPTION PAGE: SHOW FILTERS
				##### LOAD FILTERS #####
				$filters_info = $filters->GetAvailableFiltersInfo();
				
				foreach( $filters_info as $fID => $filter_name ){
					$filter_count = 0;
					##### LOAD VALUES #####
					$values_output = $filters->GetFilterValuesInfo( $fID );
					
					#LOAD FILTER COUNT 
					foreach( $values_output as $v ){
						$filter_count += $v['count'];
					}
					
					#IF FILTER HAS ANY VALUE WITH PRODUCTS, SHOW FILTER
					if( $filter_count > 0 ){
						$content .= '<div class="filter-name collapsed" id="collapse-' . $fID . '" onClick="CollapseToggle(\'' . $fID . '\');">
							' . $filter_name;
						if( FILTER_SHOW_ITEMS_COUNTER == 'Yes' ){
							$content .= ' <span>' . $filter_count . '</span>' . "\n";
						}
						$content .= '
							</div>
							<ul class="filter-value-list" id="filter-' . $fID . '">' . "\n";
						foreach( $values_output as $v ){
							$content .= '<li class="filter-value" onClick="switch_check(' . $v['id'] . ');">';
							
							$cbxSel = false;
							if( $filters->IsValueSelected($v['id'])){
								$cbxSel = true;
							}
							
							$content .= zen_draw_checkbox_field('filter-values[]', $v['id'], ($cbxSel?true:false), 'onChange="UpdateFilters();" class="cbxFilters" id="cbxFilter_' . $v['id'] . '"');
							
							$lblOption = $v['name'];
							if( FILTER_OPTION_SHOW_ITEMS_COUNTER == 'Yes' ){
								$lblOption .= ' <span>' . $v['count'] . '</span>';
							}
							
							$content .= zen_draw_label( $lblOption, 'filter-values', 'class="lblFilters'.($cbxSel?' selectFilter':'') . '" id="lblFilter_' . $v['id'] . '"');
							
							$content .= '</li>' . "\n";
						}
						$content .= '</ul>' . "\n";
					}
				} #END: WHILE FILTERS
				
				#ADD CPATH REFERENCE FOR JSCRIPT
				if( count($filters->cPath) > 0 ){
					$content .= zen_draw_hidden_field('cPath', implode("_", $filters->cPath));
				}
				
				#DECIDE WHAT TO SHOW ON LEFT MENU
				if( $content != '' ){ #FILTERS AVAILABLE: SHOW
					#***** START: NATIVE FILTERS *****#
					#START: MANUFACTURERS (#1)
					if( FILTER_BY_MANUFACTURERS == 'Yes' ){
						$manufacturers_count = $filters->GetProductsManufacturersCount();
						if( count($manufacturers_count) > 0 ){
							foreach( $manufacturers_count as $mID => $mInfo ){
								$filter_count += (int)$mInfo['count'];
								$manufacturer_options .= '<li class="filter-value" onClick="switch_other(\'m\',' . $mID . ');">';
								
								$cbxSel = false;
								if( in_array($mID, $filters->manufacturers ) ){
									$cbxSel = true;
								}
								
								$manufacturer_options .= zen_draw_checkbox_field('manufacturers[]', $mID, ($cbxSel ? true : false), 'onChange="UpdateOther(\'m\');" class="cbxFilters" id="cbx_manufacturers_' . $mID . '"');
								
								$lblOption = $mInfo['name'];
								if( FILTER_OPTION_SHOW_ITEMS_COUNTER == 'Yes' ){
									$lblOption .= ' <span>' . $mInfo['count'] . '</span>';
								}
								
								$manufacturer_options .= zen_draw_label( $lblOption, 'manufacturers', 'class="lblFilters'.($cbxSel?' selectFilter':'') . '" id="lbl_manufacturers_' . $mID . '"');
								
								$manufacturer_options .= '</li>' . "\n";
							}
							if( $filter_count > 0 ){
								$content .= '<div class="filter-name collapsed" id="collapse-manufacturers" onClick="CollapseToggle(\'manufacturers\');">
								' . FILTER_TITLE_MANUFACTURERS;
								
								if( FILTER_SHOW_ITEMS_COUNTER == 'Yes' ){
									$content .= ' <span>' . $filter_count . '</span>' . "\n";
								}
								
								$content .= '
								</div>
								<ul class="filter-value-list" id="filter-manufacturers">' . "\n";
								$content .= $manufacturer_options . "\n";
								$content .= '</ul>' . "\n";
							}
						}
					} #END: MANUFACTURERS (#1)
					
					#START: PRICE RANGE (#2)
					if( FILTER_BY_PRICES == 'Yes' ){
						$price_options = ''; #HTML OUTPUT
						if( count($filters->pricing_break_points) > 1 ){
							#COUNT PRODUCTS AVAILABLE IN PRICE RANGE
							$cbxSel = false;
							$count = $filters->GetProductsPriceRangeCount(0);
							$total_count = $count;
							$top_index = count($filters->pricing_break_points) - 1;
							if( in_array(1, $filters->price_indexes) ){
								$cbxSel = true;
							}
							#PRICE < X
							$price_options .= '<li ' . ($count > 0 ? 'class="filter-value' . ($cbxSel?' selectFilter':'') . '" onClick="switch_other(\'p\',\'1\');"' : 'class="filter-value-disabled"') . '>';
							
							$price_options .= zen_draw_checkbox_field('pricing[]', '1', $cbxSel, ( $count > 0 ?'onChange="UpdateOther(\'p\');" id="cbx_pricing_1"' : 'disabled="disabled"' ) . ' class="cbxFilters"');
							
							#LOWER PRICE OPTION LABEL
							$lblOption = FILTER_PRICE_LESS_THAN . $filters->pricing_break_points[0];
							#DISPLAY ITEM COUNT ON LABEL?
							if( FILTER_OPTION_SHOW_ITEMS_COUNTER == 'Yes' ){
								$lblOption .= ' <span>' . $count . '</span>';
							}
							$price_options .= zen_draw_label($lblOption, 'pricing', 'class="lblFilters" id=" lbl_pricing_1"');
							
							$price_options .= '</li>' . "\n";
							
							#PRICE BETWEEN X AN Y
							for( $i = 1; $i < $top_index; $i++ ){
								$count = $filters->GetProductsPriceRangeCount($i);
								$total_count += $count;
								$p = $i + 1;
								$cbxSel = false;
								if( in_array($p, $filters->price_indexes) ){
									$cbxSel = true;
								}
								
								#PRICE OPTION LABEL
								$lblOption = FILTER_PRICE_FROM . $filters->pricing_break_points[$i-1] . FILTER_PRICE_TO . $filters->pricing_break_points[$i];
								#DISPLAY ITEM COUNT ON LABEL?
								if( FILTER_OPTION_SHOW_ITEMS_COUNTER == 'Yes' ){
									$lblOption .= ' <span>' . $count . '</span>';
								}
								$cbx_label = zen_draw_label( $lblOption, 'pricing', 'class="lblFilters" id="lbl_pricing_' . $p . '"');
								
								#HTML OUTPUT
								$price_options .= '<li ' . ($count > 0 ? 'class="filter-value' . ($cbxSel?' selectFilter':'') . '" onClick="switch_other(\'p\',\'' . $p . '\');"' : 'class="filter-value-disabled"') . '>' . 
								zen_draw_checkbox_field('pricing[]', $p, $cbxSel, ( $count > 0 ?'onChange="UpdateOther(\'p\');" id="cbx_pricing_' . $p . '"' : 'disabled="disabled"' ) . ' class="cbxFilters"') . 
								$cbx_label . 
								'</li>' . "\n";
							}
							#PRICE > Y
							$p = $top_index + 1;
							$cbxSel = false;
							if( in_array($p, $filters->price_indexes) ){
								$cbxSel = true;
							}
							$count = $filters->GetProductsPriceRangeCount($top_index);
							$total_count += $count;
							
							#LOWER PRICE OPTION LABEL
							$lblOption = FILTER_PRICE_GREATER_THAN . $filters->pricing_break_points[$top_index - 1];
							#DISPLAY ITEM COUNT ON LABEL?
							if( FILTER_OPTION_SHOW_ITEMS_COUNTER == 'Yes' ){
								$lblOption .= ' <span>' . $count . '</span>';
							}
							$cbx_label = zen_draw_label($lblOption, 'pricing', 'class="lblFilters" id="lbl_pricing_' . $p . '"');
							
							$price_options .= '<li ' . ($count > 0 ? 'class="filter-value' . ($cbxSel?' selectFilter':'') . '" onClick="switch_other(\'p\',\'' . $p . '\');"' : 'class="filter-value-disabled"') . '>' . 
							zen_draw_checkbox_field('pricing[]', $p, $cbxSel, ( $count > 0 ?'onChange="UpdateOther(\'p\');" id="cbx_pricing_' . $p . '"' : 'disabled="disabled"' ) . ' class="cbxFilters"') . 
							$cbx_label . 
							'</li>' . "\n";
							#BUILD / DISPLAY BOX
							if( $price_options != '' and $total_count > 0 ){
								$content .= '<div class="filter-name collapsed" id="collapse-pricing" onClick="CollapseToggle(\'pricing\');">
								';
								
								#PRICE FILTER LABEL
								$content .= FILTER_TITLE_PRICE_RANGE;
								#DISPLAY ITEM COUNT ON LABEL?
								if( FILTER_SHOW_ITEMS_COUNTER == 'Yes' ){
									$content .= ' <span>' . $total_count . '</span>';
								}
								
								$content .= '
								</div>
								<ul class="filter-value-list" id="filter-pricing">' . "\n";
								$content .= $price_options . "\n";
								$content .= '</ul>' . "\n";
							}
						}
						
					} #END: PRICE RANGE (#2)
					
					#START: PRODUCT RATINGS (#3)
					if( FILTER_BY_RATINGS == 'Yes' ){
						$ratings_count = $filters->GetProductsRatingsCount();
						
						#LOAD OUTPUT
						$filter_count = 0;
						for( $i = 1; $i <= 5; $i++ ){
							#CHECKBOX IS SELECTED?
							$cbxSel = false;
							if( in_array($i, $filters->ratings) ){
								$cbxSel = true;
							}
							#CHECKBOX IS DISABLED?
							$active = true;
							if($ratings_count[$i]==0){
								$active = false;
							}
							$filter_count += $ratings_count[$i];
							#LOAD OPTION OUTPUT
							$ratings_options .= '<li' . ($active?' class="filter-value" onClick="switch_other(\'r\',' . $i . ');"':' class="filter-value-disabled"') . '>';
							
							$ratings_options .= zen_draw_checkbox_field('ratings[]', $i, $cbxSel, ($active?'':' disabled="disabled"') . 'onChange="UpdateOther(\'r\');" class="cbxFilters" id="cbx_ratings_' . $i . '"');
							
							#RATINGS OPTION LABEL
							$lblOption = constant('RATING_'.$i);
							#DISPLAY ITEM COUNT ON LABEL?
							if( FILTER_OPTION_SHOW_ITEMS_COUNTER == 'Yes' ){
								$lblOption .= ' <span>' . $ratings_count[$i] . '</span>';
							}
							$ratings_options .= zen_draw_label($lblOption, 'ratings', 'class="lblFilters'.($cbxSel?' selectFilter':'') . '" id="lbl_ratings_' . $i . '"');
							
							$ratings_options .= '</li>' . "\n";
						}
						
						#SHOW ONLY IF SELECTION HAS RATINGS
						if( $filter_count > 0 ){
							$content .= '<div class="filter-name collapsed" id="collapse-ratings" onClick="CollapseToggle(\'ratings\');">
							';
							
							#RATINGS FILTER LABEL
							$content .= FILTER_TITLE_PRODUCT_RATINGS;
							#DISPLAY ITEM COUNT ON LABEL?
							if( FILTER_SHOW_ITEMS_COUNTER == 'Yes' ){
								$content .= ' <span>' . $filter_count . '</span>' . "\n";
							}
							
							$content .= '
							</div>
							<ul class="filter-value-list" id="filter-ratings">' . "\n";
							$content .= $ratings_options . "\n";
							$content .= '</ul>' . "\n";
						}
					} #END: PRODUCT RATINGS (#3)
					
					#START: SPECIALS / ON SALE (#4)
					if( FILTER_BY_SPECIALS == 'Yes' ){
						$count = $filters->GetProductsSpecialsCount();
						if( $count > 0 ){
							#CHECKBOX IS SELECTED?
							$cbxSel = false;
							if( $filters->specials ){
								$cbxSel = true;
							}
							
							#LOAD OPTION OUTPUT
							$content .= '<div class="filter-name collapsed" id="collapse-specials" onClick="CollapseToggle(\'specials\');">';
							
							#SPECIALS FILTER LABEL
							$content .= FILTER_TITLE_SPECIALS;
							#DISPLAY ITEM COUNT ON LABEL?
							if( FILTER_SHOW_ITEMS_COUNTER == 'Yes' ){
								$content .= ' <span>' . $count . '</span>' . "\n";
							}
							
							$content .= '
							</div>
							<ul class="filter-value-list" id="filter-specials">
								<li class="filter-value" onClick="switch_other(\'s\',1);">';
							
							$content .= zen_draw_checkbox_field('specials[]', 1, $cbxSel, 'onChange="UpdateOther(\'s\');" class="cbxFilters" id="cbx_specials_1"');
							
							#SPECIALS OPTION LABEL
							$lblOption = FILTER_TITLE_SPECIALS;
							#DISPLAY ITEM COUNT ON LABEL?
							if( FILTER_OPTION_SHOW_ITEMS_COUNTER == 'Yes' ){
								$lblOption .= ' <span>' . $count . '</span>';
							}
							$content .= zen_draw_label($lblOption, 'specials', 'class="lblFilters'.($cbxSel?' selectFilter':'') . '" id="lbl_specials_1"');
							
							$content .= '</li>
							</ul>' . "\n";
						} #IF ANY PRODUCT ON SALE IS LISTED
					} #END: SPECIALS / ON SALE (#4)
					#***** END: NATIVE FILTERS *****#
				} #END: IF FILTERS ARE AVAILABLE
				
				#***** START: RESET FILTER ICONS *****#
				$selected_list = '';
				if( $filters->main_page == 'products_filters' and $content != '' ){
					#RESET SELECTED NATIVE DATA FILTERS
					$native_array = $filters->GetSelectedNativeFilterIDs();
					if( count($native_array) > 0 ){
						foreach( $native_array as $nf ){
							$selected_list .= '<div onClick="window.location = RemoveURLParameter(\'' . $nf . '\');">';
							switch($nf){
								case 'm':
									$selected_list .= FILTER_TITLE_MANUFACTURERS;
									break;
								case 'p':
									$selected_list .= FILTER_TITLE_PRICE_RANGE;
									break;
								case 'r':
									$selected_list .= FILTER_TITLE_PRODUCT_RATINGS;
									break;
								case 's':
									$selected_list .= FILTER_TITLE_SPECIALS;
									break;
								default:
									break;
							}
							$selected_list .= "<span>&#10006;</span></div>\n";
						}
					}
					#RESET SELECTED CUSTOM FILTERS
					$url_parameters = '';
					if( count($filters->cPath) > 0 ){ #cPath
						$url_parameters .= '&cPath=' . implode("_", $filters->cPath);
					}
					if( $filters->view != '' ){ #view
						$url_parameters .= '&view=' . $filters->view;
					}
					if( $filters->sort_by != '' ){ #sort
						$url_parameters .= '&sort=' . $filters->sort_by;
					}
					
					if( count($filters->manufacturers) > 0 ){ #m
						$url_parameters .= '&m=' . implode("_", $filters->manufacturers);
					}
					if( count($filters->price_indexes) > 0 ){ #p
						$url_parameters .= '&p=' . implode("_", $filters->price_indexes);
					}
					if( count($filters->ratings) > 0 ){ #r
						$url_parameters .= '&r=' . implode("_", $filters->ratings);
					}
					if( $filters->specials === true ){ #s
						$url_parameters .= '&s=1';
					}
					if( count($filters->fv) > 0 ){
						$filter_ids = array();
						$filter_value_ids = array();
						
						foreach($filters->fv as $fv){
							$filter_value_ids[ $fv['filter_id'] ][] = $fv['value_id'];
							if( !in_array($fv['filter_id'], $filter_ids) ){
								$filter_ids[] = $fv['filter_id'];
							}
						}
						
						foreach($filter_ids as $fID){
							#GET FILTER NAME
							$filter_name = $filters->GetFilterNameFromId( $fID );
							$new_fv = '';
							if( $filter_name != '' ){
								#CREATE ALTERNATIVE VALUES LISTS
								foreach($filter_ids as $f){
									if( $fID != $f ){
										foreach( $filter_value_ids[$f] as $v ){
											$new_fv .= $v . '_';
										}
									}
								}
								$new_fv = trim($new_fv, "_");
								
								$selected_list .= '<div onClick="window.location = \'' . zen_href_link(FILENAME_ADDON_PRODUCTS_FILTERS, 'fv=' . $new_fv . $url_parameters) . '\';">
									' . $filter_name . '
									<span>&#10006;</span>
								</div>' . "\n";
							}
						}
					}
					
					#LOAD CONTENT VARIABLE WITH RESET ICONS
				}
				#***** END: RESET FILTER ICONS *****#
			}else{ #IN PRODUCT DESCRIPTION PAGE: SHOW BACK BUTTON
				$parameters = '';
				if( count($filters->cPath) > 0 ){
					$parameters = 'cPath=' . implode("_", $filters->cPath);
				}
				
				#VIEW
				if( $filters->view != '' ){
					$parameters .= '&view=' . $filters->view;
				}
				#SORT
				if( $filters->sort_by != '' ){
					$parameters .= '&sort=' . $filters->sort_by;
				}
				#CUSTOM FILTERS
				if( count($filters->fv) > 0 ){
					$fvIDs = '';
					foreach($filters->fv as $fv){
						if( $fvIDs != '' ){
							$fvIDs .= '_';
						}
						$fvIDs .= $fv['value_id'];
					}
					$parameters .= '&fv=' . $fvIDs;
				}
				
				#SPECIAL FILTERS
				if( count($filters->manufacturers) > 0 ){ #MANUFACTURERS
					$parameters .= '&m=' . implode("_", $filters->manufacturers);
				}
				if( count($filters->price_indexes) > 0 ){ #PRICE RANGE
					$parameters .= '&p=' . implode("_", $filters->price_indexes);
				}
				if( count($filters->ratings) > 0 ){ #CUSTOMER RATINGS
					$parameters .= '&r=' . implode("_", $filters->ratings);
				}
				if( $filters->specials ){ #SPECIALS
					$parameters .= '&s=1';
				}
				
				$content = '<a href="' . zen_href_link(FILENAME_ADDON_PRODUCTS_FILTERS, $parameters) . '" id="back_to_filter">' . BTN_BACK_TO_RESULTS . '</a>';
			} #END: IF NOT IN PRODUCT DESCRIPTION PAGE
		} #END: IF REPLACE CAT WITH FILTERS
		
		if( $content != '' ){
			if( $selected_list != '' ){
				$selected_list = '<div id="selected_filters">
					<h4>' . FILTER_TITLE_SELECTED . '</h4>
					' . $selected_list . '
				</div>' . "\n";
			}
			
			$content = '<div id="filters-master">
							' . $filter_category_switch . '
							<p id="filterTitle" class="collapsed" onclick="CollapseFilters();">' . TITLE_FILTERS . '</p>'
							. $selected_list . '
							<div id="filters">
								' . $content . '
							</div>
						</div>' . "\n";
		} #END: IF CONTENT VAR IS NOT EMPTY
	} #END: IF FILTERS OBJECT EXISTS
	
	#PREPEND TAGS TO LOAD CSS AND JS FILES
	$content = '
		<script src="' . DIR_WS_TEMPLATE . 'jscript/products_filters.js"></script>
		<link rel="stylesheet" href="' . DIR_WS_TEMPLATE . 'css/products_filters.css">
		' . $content;
