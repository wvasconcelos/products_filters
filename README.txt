======================
** PRODUCTS FILTERS **
======================
Customers find it difficult to navigate through on-line stores with a large selection of items using hierarchical categories. To solve that problem, large stores, such as Amazon and WalMart, rely on product filters. This plug-in attempts to replicate the product filtering capabilities from larger on-line retailers.

==================
** NEW ON V.2.0 **
==================
1. Simplification of the constructor method
2. Bug fixes
3. Option to display filters by default when it available in the category
4. Ability to control the display of item counters at the filter and filter option levels.

==================
** INSTALLATION **
==================
1. Download the zip file and extract the files into a local folder;
2. Rename the YOUR_ADMIN folder based on your admin folder name;
3. If you are not using the RESPONSIVE_CLASSIC template, rename the includes/templates/responsive_classic folder based on your template's name;
4. Upload all plug-in files to your web server. You should have no rewrites of core files here.
5. Login to admin and go to Tools > Install SQL Patches. Upload the provided install.sql file. 
6. Modify the following core files:

**************************
* CORE FILE MODIFICATION *
**************************
* File Name and Location (1/2)
******************************
./YOUR_ADMIN/includes/modules/product/collect_info.php
Add:
<?php
	#PRODUCT FILTERS
	$filter->update();
	echo $filter->AddFilterAjaxHTML();
?>

Before (last):
        </table></td>

* File Name and Location (2/2)
******************************
./YOUR_ADMIN/includes/modules/category_product_listing.php

Add:
<?php
	#PRODUCTS FILTERS
	echo $filter->GetCatStatusBtn( $categories->fields['categories_id'] );
?>

Before:
<?php
      if ($categories->fields['categories_status'] == '1') {

Note: to accommodate the new button, on the line immediately before the newly added code do the following:

Replace the width on:
<td class="dataTableContent" width="50" align="left">

With:
width="68"

----
===================
** CONFIGURATION **
===================
1. Plug-in Configuration
Admin: Configuration > Products Filters 

2. Create Filters: 
Admin: Catalog > Products Filters

3. Add the Product Filter side-box:
Admin: Tools > Layout Boxes Controller > sideboxes/products_filters.php
 > Left/Right Column Status: [on]
 > Single Column Status: [on]
 > Single Column Sort Order: -1
 
4. Enabling filters on specific categories
Admin: Catalog > Categories/Products > [F]
Click the [F] button turning it from red to green to enable.

5. Adding filters to products:
Admin: Catalog > Categories/Products
Select a product to edit, go to the Products Filters section and select all filters you want to associate with that product. 

Note: It is not necessary to update the product after you add filters (changes are saved dynamically on the fly through AJAX calls).

==========================
** FINAL CONSIDERATIONS **
==========================
This plug-in was tested on Zen Cart 1.5.5.

THIS PLUGIN IS PROVIDED "AS IS", WITH NO WARRANTY OF ANY KIND, EXPRESS OR IMPLIED. IN NO EVENT SHALL THE DEVELOPER BE LIABLE FOR ANY DAMAGES OR OTHER LIABILITY ARISING FROM, OUT OF OR IN CONNECTION WITH THE GIFT CARD PLUGIN. BY INSTALLING THIS PLUGIN IN A PRODUCTION SITE, YOUR ASSUME FULL RESPONSIBILITY ON ANY UNINTENDED SIDE EFFECT THIS PLUGIN MAY HAVE ON YOUR WEBSITE.

BEFORE YOU INSTALL THIS EXTENSION, IT IS A GOOD IDEA TO CREATE A FRESH BACKUP COPY OF YOUR WEBSITE (SCRIPT FILES AND DATABASE).