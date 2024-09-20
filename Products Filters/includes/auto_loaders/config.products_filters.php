<?php
/**
 * Products Filters plugin for Zen Cart. Auto loader definition - calls observer class.
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
$autoLoadConfig[100][] = array('autoType'=>'class',
                              'loadFile'=>'observers/class.products_filters.php');
$autoLoadConfig[101][] = array('autoType'=>'classInstantiate',
                              'className'=>'products_filters_observer',
                              'objectName'=>'update');
?>