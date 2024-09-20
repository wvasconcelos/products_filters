<?php
/**
 * Products Filters for Zen Cart.
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
                               'loadFile'=>'products_filters.php',
                               'classPath'=>DIR_WS_CLASSES);
  $autoLoadConfig[101][] = array('autoType'=>'classInstantiate',
                               'className'=>'ProductsFiltersClass',
                               'objectName'=>'filter');
