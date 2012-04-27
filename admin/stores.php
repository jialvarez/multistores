<?php
/*
  $Id: stores.php,v 1.0 2004/08/23 22:50:52 rmh Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
// Remake for allow to send hidden checks
  function tep_draw_hidden_selection_field($name, $type, $value = '', $checked = false, $compare = '') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $type = 'checkbox';
    // in style visibility:hidden is the difference
    $selection = '<input style="visibility:hidden" type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name]) && (($HTTP_GET_VARS[$name] == 'on') || (stripslashes($HTTP_GET_VARS[$name]) == $value))) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name]) && (($HTTP_POST_VARS[$name] == 'on') || (stripslashes($HTTP_POST_VARS[$name]) == $value))) || (tep_not_null($compare) && ($value == $compare)) ) {
      $selection .= ' CHECKED';
    }

    $selection .= '>';

    return $selection;
  }


function rmdird($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }
 
    // Simple delete for a file
    if (is_file($dirname)) {
        return unlink($dirname);
    }
 
    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
 
        // Recurse
        rmdird("$dirname/$entry");
    }
 
    // Clean up
    $dir->close();
    return rmdir($dirname);
}

  function makeRecursLink($orig, $dest)
  {
    if (is_dir($orig)) {
	if (substr($orig, -1) != '/') {
	$orig .= '/';
	}

	$handle = opendir($orig);

	while (false !== ($file = readdir($handle))) 
	{
		if ($file != '.' && $file != '..') 
		{
			$path = $orig.$file;
	
			if (is_file($path)) 
			{
				if (!file_exists($dest.'/'.$file))
				{
					@symlink($path, $dest.'/'.$file);
				}
			} 
			else if (is_dir($path)) 
			{
				@mkdir($dest.'/'.$file, 0755);                   
				makeRecursLink($path, $dest.'/'.$file);
			}
		}
	}
    }

    closedir($handle);
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          if (isset($HTTP_GET_VARS['storeID'])) {
            tep_set_store_status($HTTP_GET_VARS['storeID'], $HTTP_GET_VARS['flag']);
          }
        }
        tep_redirect(tep_href_link(FILENAME_STORES, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'storeID=' . $HTTP_GET_VARS['storeID']));
        break;
      case 'insert':
      case 'save':
        if (isset($HTTP_GET_VARS['storeID'])) $stores_id = tep_db_prepare_input($HTTP_GET_VARS['storeID']);
        $stores_name = tep_db_prepare_input($HTTP_POST_VARS['stores_name']);
        //$stores_config_table = tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']);
	$stores_config_table = 'configuration_'.$stores_name;
	// get the store url
	$stores_url = tep_db_prepare_input($HTTP_POST_VARS['stores_url']);

	// get the data of the store manager
	$administrators_username = tep_db_prepare_input($HTTP_POST_VARS['administrators_username']);
	$administrators_password = tep_db_prepare_input($HTTP_POST_VARS['administrators_password']);
        
	$error = false;
        $entry_stores_name_error = false;
        $entry_stores_config_table_error = false;
        $entry_stores_config_table_exists_error = false;
        $entry_stores_config_table_unchanged = false;
	$entry_stores_url = false;
	$entry_stores_manager_error = false;

        if (!tep_not_null($stores_name)) {
          $error = true;
          $entry_stores_name_error = true;
        }

	// check for the correct data of the store manager
	if (!tep_not_null($administrators_username) || !tep_not_null($administrators_password))
	{
		$error = true;
		$entry_stores_manager_error = true;
	}

	// check for a valid URL
	if (!tep_not_null($stores_url))
	{
		$error = true;
		$entry_stores_url = true;
	}

        $check_config_table_query = tep_db_query("select stores_config_table from " . TABLE_STORES . " where stores_id = '" . (int)$stores_id . "'");
        $check_config_table = tep_db_fetch_array($check_config_table_query);
        if (!tep_not_null($stores_config_table)) {
          $error = true;
          $entry_stores_config_table_error = true;
        } else if (tep_table_exists($stores_config_table) == true) {
            if ($check_config_table['stores_config_table'] != $stores_config_table) {
              $error = true;
              $entry_stores_config_table_exists_error = true;
            } else {
              $entry_stores_config_table_unchanged = true;
            }
        }

        if (($error == false) && ($entry_stores_config_table_exists_error == false)) {
          $sql_data_array = array('stores_name' => tep_db_prepare_input($HTTP_POST_VARS['stores_name']),
                                  'stores_url'  => tep_db_prepare_input($HTTP_POST_VARS['stores_url']),
                                  'stores_config_table'  => $stores_config_table,
                                  'stores_status' => tep_db_prepare_input($HTTP_POST_VARS['stores_status']));

          if ($action == 'insert') {
            $sql_data_array['date_added'] = 'now()';

            tep_db_perform(TABLE_STORES, $sql_data_array);
            $stores_id = tep_db_insert_id();

	// get url for a new store
	$url = tep_db_prepare_input($HTTP_POST_VARS['stores_url']);

	// get store folder for a new store => in example, obtain 'minimal' from /opt/lampp/htdocs/minimal
        $new_store_folder = explode("/", $url);
        $folder = $new_store_folder[sizeof($new_store_folder)-1];

	// get DIR_FS_CATALOG like /opt/lampp/htdocs/proyecto/catalog
	$root_path = explode("/", DIR_FS_CATALOG);

	// remove catalog in order to obtain DIR FS ROOT
	for ($i=0;$i<count($root_path)-2;$i++)
		$my_root_path .= ($root_path[$i] == '' ? '/' : $root_path[$i].'/');

	// get path to be copied in from_path	
	$to_path = $my_root_path . $folder;
	$from_path = $my_root_path . "minimal";
	
	// for the connection cannot be rejected by the server
	ini_set('max_execution_time', 0);

	// create parent directory and includes directory
	mkdir($to_path);
	mkdir($to_path . '/includes');

	// copy the two different files in each store
	copy($from_path . '/includes/configure.php', $to_path . '/includes/configure.php');
	copy($from_path . '/includes/database_tables.php', $to_path . '/includes/database_tables.php');
		
	// make soft links ;)
	makeRecursLink($from_path, $to_path);
	
	// recover the normal execution time
	ini_set('max_execution_time', 30);
		
	// modify configure.php file according to the store configuration
	require ('body-configure.php');
	$fp=fopen($to_path."/includes/configure.php",'w');        // Open the file for overwrite
	fwrite($fp,$body);            // Write the file
	fclose($fp);                    // Close the file
	// modify file permissions
	chmod($to_path."/includes/configure.php", 0444);

	// modify database_tables.php file
	require ('body-database-tables.php');
	$fp=fopen($to_path."/includes/database_tables.php",'w');        // Open the file for overwrite
	fwrite($fp,$body);            // Write the file
	fclose($fp);                    // Close the file
	
	// get localhost from http://localhost/project/catalog/
	$url = tep_db_prepare_input($HTTP_POST_VARS['stores_url']);
	$web1 = explode("//", $url); // remove http://, get the right part
	$web2 = explode("/", $web1[1]); // from localhost/multistore/catalog, 
	$web = $web2[0]."/".$web2[1]; // get the first part, that is, localhost

	$languages_array = tep_db_query("select distinct languages_id from " . TABLE_LANGUAGES_TO_STORES);
	while($languages_ids = tep_db_fetch_array($languages_array))
	{
		$language_ident = $languages_ids['languages_id'];
		tep_db_query("INSERT INTO " . TABLE_LANGUAGES_TO_STORES . " VALUES (". $language_ident .", ".$stores_id.")");
	}

            if (isset($HTTP_POST_VARS['insert_table']) && ($HTTP_POST_VARS['insert_table'] == 'on')) {
              tep_db_query("CREATE TABLE " . $stores_config_table . " (configuration_id int NOT NULL auto_increment, configuration_title varchar(64) NOT NULL, configuration_key varchar(64) NOT NULL, configuration_value varchar(255) NOT NULL, configuration_description varchar(255) NOT NULL, configuration_group_id int NOT NULL, sort_order int(5) NULL, last_modified datetime NULL, date_added datetime NOT NULL, use_function varchar(255) NULL, set_function varchar(255) NULL, PRIMARY KEY (configuration_id))");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store Name', 'STORE_NAME', '" . tep_db_input($stores_name) . "', 'The name of my store', '1', '1', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Store Owner', 'STORE_OWNER', 'Harald Ponce de Leon', 'The name of my store owner', '1', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('E-Mail Address', 'STORE_OWNER_EMAIL_ADDRESS', 'root@localhost', 'The e-mail address of my store owner', '1', '3', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('E-Mail From', 'EMAIL_FROM', 'osCommerce <root@localhost>', 'The e-mail address used in (sent) e-mails', '1', '4', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Country', 'STORE_COUNTRY', '223', 'The country my store is located in <br><br><b>Note: Please remember to update the store zone.</b>', '1', '6', 'tep_get_country_name', 'tep_cfg_pull_down_country_list(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Zone', 'STORE_ZONE', '18', 'The zone my store is located in', '1', '7', 'tep_cfg_get_zone_name', 'tep_cfg_pull_down_zone_list(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expected Sort Order', 'EXPECTED_PRODUCTS_SORT', 'desc', 'This is the sort order used in the expected products box.', '1', '8', 'tep_cfg_select_option(array(\'asc\', \'desc\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expected Sort Field', 'EXPECTED_PRODUCTS_FIELD', 'date_expected', 'The column to sort by in the expected products box.', '1', '9', 'tep_cfg_select_option(array(\'products_name\', \'date_expected\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Switch To Default Language Currency', 'USE_DEFAULT_LANGUAGE_CURRENCY', 'false', 'Automatically switch to the language\'s currency when it is changed', '1', '10', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Extra Order Emails To', 'SEND_EXTRA_ORDER_EMAILS_TO', '', 'Send extra order emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '1', '11', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Search-Engine Safe URLs (still in development)', 'SEARCH_ENGINE_FRIENDLY_URLS', 'false', 'Use search-engine safe urls for all site links', '1', '12', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Cart After Adding Product', 'DISPLAY_CART', 'true', 'Display the shopping cart after adding a product (or return back to their origin)', '1', '14', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Guest To Tell A Friend', 'ALLOW_GUEST_TO_TELL_A_FRIEND', 'false', 'Allow guests to tell a friend about a product', '1', '15', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Default Search Operator', 'ADVANCED_SEARCH_DEFAULT_OPERATOR', 'and', 'Default search operators', '1', '17', 'tep_cfg_select_option(array(\'and\', \'or\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Address and Phone', 'STORE_NAME_ADDRESS', 'Store Name\nAddress\nCountry\nPhone', 'This is the Store Name, Address and Phone used on printable documents and displayed online', '1', '18', 'tep_cfg_textarea(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Category Counts', 'SHOW_COUNTS', 'true', 'Count recursively how many products are in each category', '1', '19', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Tax Decimal Places', 'TAX_DECIMAL_PLACES', '0', 'Pad the tax value this amount of decimal places', '1', '20', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Prices with Tax', 'DISPLAY_PRICE_WITH_TAX', 'false', 'Display prices with tax included (true) or add the tax at the end (false)', '1', '21', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('First Name', 'ENTRY_FIRST_NAME_MIN_LENGTH', '2', 'Minimum length of first name', '2', '1', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Last Name', 'ENTRY_LAST_NAME_MIN_LENGTH', '2', 'Minimum length of last name', '2', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Date of Birth', 'ENTRY_DOB_MIN_LENGTH', '10', 'Minimum length of date of birth', '2', '3', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('E-Mail Address', 'ENTRY_EMAIL_ADDRESS_MIN_LENGTH', '6', 'Minimum length of e-mail address', '2', '4', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Street Address', 'ENTRY_STREET_ADDRESS_MIN_LENGTH', '5', 'Minimum length of street address', '2', '5', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Company', 'ENTRY_COMPANY_MIN_LENGTH', '2', 'Minimum length of company name', '2', '6', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Post Code', 'ENTRY_POSTCODE_MIN_LENGTH', '4', 'Minimum length of post code', '2', '7', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('City', 'ENTRY_CITY_MIN_LENGTH', '3', 'Minimum length of city', '2', '8', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('State', 'ENTRY_STATE_MIN_LENGTH', '2', 'Minimum length of state', '2', '9', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Telephone Number', 'ENTRY_TELEPHONE_MIN_LENGTH', '3', 'Minimum length of telephone number', '2', '10', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Password', 'ENTRY_PASSWORD_MIN_LENGTH', '5', 'Minimum length of password', '2', '11', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Credit Card Owner Name', 'CC_OWNER_MIN_LENGTH', '3', 'Minimum length of credit card owner name', '2', '12', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Credit Card Number', 'CC_NUMBER_MIN_LENGTH', '10', 'Minimum length of credit card number', '2', '13', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Review Text', 'REVIEW_TEXT_MIN_LENGTH', '50', 'Minimum length of review text', '2', '14', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Best Sellers', 'MIN_DISPLAY_BESTSELLERS', '1', 'Minimum number of best sellers to display', '2', '15', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Also Purchased', 'MIN_DISPLAY_ALSO_PURCHASED', '1', 'Minimum number of products to display in the \'This Customer Also Purchased\' box', '2', '16', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Products', 'MIN_DISPLAY_NEWPRODUCTS', '1', 'Minimum number of products to display in the \'New Products\' box', '2', '18', now())"); //rmh M-S_fixes

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Address Book Entries', 'MAX_ADDRESS_BOOK_ENTRIES', '5', 'Maximum address book entries a customer is allowed to have', '3', '1', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Search Results', 'MAX_DISPLAY_SEARCH_RESULTS', '20', 'Amount of products to list', '3', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Page Links', 'MAX_DISPLAY_PAGE_LINKS', '5', 'Number of \'number\' links use for page-sets', '3', '3', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Special Products', 'MAX_DISPLAY_SPECIAL_PRODUCTS', '9', 'Maximum number of products on special to display', '3', '4', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Products Module', 'MAX_DISPLAY_NEW_PRODUCTS', '9', 'Maximum number of new products to display in a category', '3', '5', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products Expected', 'MAX_DISPLAY_UPCOMING_PRODUCTS', '10', 'Maximum number of products expected to display', '3', '6', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Manufacturers List', 'MAX_DISPLAY_MANUFACTURERS_IN_A_LIST', '0', 'Used in manufacturers box; when the number of manufacturers exceeds this number, a drop-down list will be displayed instead of the default list', '3', '7', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Manufacturers Select Size', 'MAX_MANUFACTURERS_LIST', '1', 'Used in manufacturers box; when this value is \'1\' the classic drop-down list will be used for the manufacturers box. Otherwise, a list-box with the specified number of rows will be displayed.', '3', '7', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Length of Manufacturers Name', 'MAX_DISPLAY_MANUFACTURER_NAME_LEN', '15', 'Used in manufacturers box; maximum length of manufacturers name to display', '3', '8', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Reviews', 'MAX_DISPLAY_NEW_REVIEWS', '6', 'Maximum number of new reviews to display', '3', '9', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Selection of Random Reviews', 'MAX_RANDOM_SELECT_REVIEWS', '10', 'How many records to select from to choose one random product review', '3', '10', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Selection of Random New Products', 'MAX_RANDOM_SELECT_NEW', '10', 'How many records to select from to choose one random new product to display', '3', '11', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Selection of Products on Special', 'MAX_RANDOM_SELECT_SPECIALS', '10', 'How many records to select from to choose one random product special to display', '3', '12', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Categories To List Per Row', 'MAX_DISPLAY_CATEGORIES_PER_ROW', '3', 'How many categories to list per row', '3', '13', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('New Products Listing', 'MAX_DISPLAY_PRODUCTS_NEW', '10', 'Maximum number of new products to display in new products page', '3', '14', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Best Sellers', 'MAX_DISPLAY_BESTSELLERS', '10', 'Maximum number of best sellers to display', '3', '15', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Also Purchased', 'MAX_DISPLAY_ALSO_PURCHASED', '6', 'Maximum number of products to display in the \'This Customer Also Purchased\' box', '3', '16', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Customer Order History Box', 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', '6', 'Maximum number of products to display in the customer order history box', '3', '17', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Order History', 'MAX_DISPLAY_ORDER_HISTORY', '10', 'Maximum number of orders to display in the order history page', '3', '18', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Small Image Width', 'SMALL_IMAGE_WIDTH', '100', 'The pixel width of small images', '4', '1', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Small Image Height', 'SMALL_IMAGE_HEIGHT', '80', 'The pixel height of small images', '4', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Heading Image Width', 'HEADING_IMAGE_WIDTH', '57', 'The pixel width of heading images', '4', '3', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Heading Image Height', 'HEADING_IMAGE_HEIGHT', '40', 'The pixel height of heading images', '4', '4', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Subcategory Image Width', 'SUBCATEGORY_IMAGE_WIDTH', '100', 'The pixel width of subcategory images', '4', '5', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Subcategory Image Height', 'SUBCATEGORY_IMAGE_HEIGHT', '57', 'The pixel height of subcategory images', '4', '6', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Calculate Image Size', 'CONFIG_CALCULATE_IMAGE_SIZE', 'true', 'Calculate the size of images?', '4', '7', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Image Required', 'IMAGE_REQUIRED', 'true', 'Enable to display broken images. Good for development.', '4', '8', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Gender', 'ACCOUNT_GENDER', 'true', 'Display gender in the customers account', '5', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Date of Birth', 'ACCOUNT_DOB', 'true', 'Display date of birth in the customers account', '5', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Company', 'ACCOUNT_COMPANY', 'true', 'Display company in the customers account', '5', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Suburb', 'ACCOUNT_SUBURB', 'true', 'Display suburb in the customers account', '5', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('State', 'ACCOUNT_STATE', 'true', 'Display state in the customers account', '5', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Default Customer Group', 'DEFAULT_CUSTOMER_GROUP', '1', 'The Customer Group a new member is assigned to', '5', '7', 'tep_cfg_get_customer_group', 'tep_cfg_pull_down_cg_list(', now())"); //rmh M-S_pricing
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Visitor Pricing Group', 'VISITOR_PRICING_GROUP', '0', 'The Customer Group pricing for guests (Hide Prices = Must login to see pricing & None = Default osCommerce)', '5', '8', 'tep_cfg_get_customer_group', 'tep_cfg_pull_down_vg_list(', now())"); //rmh M-S_pricing

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Stores ID', 'STORES_ID', '" . $stores_id . "', 'The id of my store', '6', '0', now())"); //rmh M-S_multi-stores
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_PAYMENT_INSTALLED', 'cc.php;cod.php', 'List of payment module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: cc.php;cod.php;paypal.php)', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_ORDER_TOTAL_INSTALLED', 'ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php', 'List of order_total module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php)', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Installed Modules', 'MODULE_SHIPPING_INSTALLED', 'flat.php', 'List of shipping module filenames separated by a semi-colon. This is automatically updated. No need to edit. (Example: ups.php;flat.php;item.php)', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Cash On Delivery Module', 'MODULE_PAYMENT_COD_STATUS', 'True', 'Do you want to accept Cash On Delevery payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_COD_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_COD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Credit Card Module', 'MODULE_PAYMENT_CC_STATUS', 'True', 'Do you want to accept credit card payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Split Credit Card E-Mail Address', 'MODULE_PAYMENT_CC_EMAIL', '', 'If an e-mail address is entered, the middle digits of the credit card number will be sent to the e-mail address (the outside digits are stored in the database with the middle digits censored)', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Flat Shipping', 'MODULE_SHIPPING_FLAT_STATUS', 'True', 'Do you want to offer flat rate shipping?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Shipping Cost', 'MODULE_SHIPPING_FLAT_COST', '5.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_SHIPPING_FLAT_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Shipping Zone', 'MODULE_SHIPPING_FLAT_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_SHIPPING_FLAT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Currency', 'DEFAULT_CURRENCY', 'USD', 'Default Currency', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Language', 'DEFAULT_LANGUAGE', 'en', 'Default Language', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Default Order Status For New Orders', 'DEFAULT_ORDERS_STATUS_ID', '1', 'When a new order is created, this order status will be assigned to it.', '6', '0', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'Do you want to display the order shipping cost?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '2', 'Sort order of display.', '6', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', '6', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) VALUES ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', '6', '4', 'currencies->format', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', '6', '5', 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Sub-Total', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 'Do you want to display the order sub-total cost?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '1', 'Sort order of display.', '6', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Tax', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 'Do you want to display the order tax value?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '3', 'Sort order of display.', '6', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Total', 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 'Do you want to display the total order value?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '4', 'Sort order of display.', '6', '2', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Country of Origin', 'SHIPPING_ORIGIN_COUNTRY', '223', 'Select the country of origin to be used in shipping quotes.', '7', '1', 'tep_get_country_name', 'tep_cfg_pull_down_country_list(', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Postal Code', 'SHIPPING_ORIGIN_ZIP', 'NONE', 'Enter the Postal Code (ZIP) of the Store to be used in shipping quotes.', '7', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Enter the Maximum Package Weight you will ship', 'SHIPPING_MAX_WEIGHT', '50', 'Carriers have a max weight limit for a single package. This is a common one for all.', '7', '3', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Package Tare weight.', 'SHIPPING_BOX_WEIGHT', '3', 'What is the weight of typical packaging of small to medium packages?', '7', '4', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Larger packages - percentage increase.', 'SHIPPING_BOX_PADDING', '10', 'For 10% enter 10', '7', '5', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Image', 'PRODUCT_LIST_IMAGE', '1', 'Do you want to display the Product Image?', '8', '1', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Manufaturer Name','PRODUCT_LIST_MANUFACTURER', '0', 'Do you want to display the Product Manufacturer Name?', '8', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Model', 'PRODUCT_LIST_MODEL', '0', 'Do you want to display the Product Model?', '8', '3', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Name', 'PRODUCT_LIST_NAME', '2', 'Do you want to display the Product Name?', '8', '4', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Price', 'PRODUCT_LIST_PRICE', '3', 'Do you want to display the Product Price', '8', '5', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Quantity', 'PRODUCT_LIST_QUANTITY', '0', 'Do you want to display the Product Quantity?', '8', '6', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Weight', 'PRODUCT_LIST_WEIGHT', '0', 'Do you want to display the Product Weight?', '8', '7', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Buy Now column', 'PRODUCT_LIST_BUY_NOW', '4', 'Do you want to display the Buy Now column?', '8', '8', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Category/Manufacturer Filter (0=disable; 1=enable)', 'PRODUCT_LIST_FILTER', '1', 'Do you want to display the Category/Manufacturer Filter?', '8', '9', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Location of Prev/Next Navigation Bar (1-top, 2-bottom, 3-both)', 'PREV_NEXT_BAR_LOCATION', '2', 'Sets the location of the Prev/Next Navigation Bar (1-top, 2-bottom, 3-both)', '8', '10', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check stock level', 'STOCK_CHECK', 'true', 'Check to see if sufficent stock is available', '9', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Subtract stock', 'STOCK_LIMITED', 'true', 'Subtract product in stock by product orders', '9', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Checkout', 'STOCK_ALLOW_CHECKOUT', 'true', 'Allow customer to checkout even if there is insufficient stock', '9', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Mark product out of stock', 'STOCK_MARK_PRODUCT_OUT_OF_STOCK', '***', 'Display something on screen so customer can see which product has insufficient stock', '9', '4', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Stock Re-order level', 'STOCK_REORDER_LEVEL', '5', 'Define when stock needs to be re-ordered', '9', '5', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Page Parse Time', 'STORE_PAGE_PARSE_TIME', 'false', 'Store the time it takes to parse a page', '10', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Log Destination', 'STORE_PAGE_PARSE_TIME_LOG', '/var/log/www/tep/page_parse_time.log', 'Directory and filename of the page parse time log', '10', '2', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Log Date Format', 'STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S', 'The date format', '10', '3', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display The Page Parse Time', 'DISPLAY_PAGE_PARSE_TIME', 'true', 'Display the page parse time (store page parse time must be enabled)', '10', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Store Database Queries', 'STORE_DB_TRANSACTIONS', 'false', 'Store the database queries in the page parse time log (PHP4 only)', '10', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Cache', 'USE_CACHE', 'false', 'Use caching features', '11', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Cache Directory', 'DIR_FS_CACHE', '/tmp/', 'The directory where the cached files are saved', '11', '2', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Transport Method', 'EMAIL_TRANSPORT', 'sendmail', 'Defines if this server uses a local connection to sendmail or uses an SMTP connection via TCP/IP. Servers running on Windows and MacOS should change this setting to SMTP.', '12', '1', 'tep_cfg_select_option(array(\'sendmail\', \'smtp\'),', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Linefeeds', 'EMAIL_LINEFEED', 'LF', 'Defines the character sequence used to separate mail headers.', '12', '2', 'tep_cfg_select_option(array(\'LF\', \'CRLF\'),', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use MIME HTML When Sending Emails', 'EMAIL_USE_HTML', 'false', 'Send e-mails in HTML format', '12', '3', 'tep_cfg_select_option(array(\'true\', \'false\'),', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Verify E-Mail Addresses Through DNS', 'ENTRY_EMAIL_ADDRESS_CHECK', 'false', 'Verify e-mail address through a DNS server', '12', '4', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send E-Mails', 'SEND_EMAILS', 'true', 'Send out e-mails', '12', '5', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable download', 'DOWNLOAD_ENABLED', 'false', 'Enable the products download functions.', '13', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Download by redirect', 'DOWNLOAD_BY_REDIRECT', 'false', 'Use browser redirection for download. Disable on non-Unix systems.', '13', '2', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Expiry delay (days)' ,'DOWNLOAD_MAX_DAYS', '7', 'Set number of days before the download link expires. 0 means no limit.', '13', '3', '', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Maximum number of downloads' ,'DOWNLOAD_MAX_COUNT', '5', 'Set the maximum number of downloads. 0 means no download authorized.', '13', '4', '', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable GZip Compression', 'GZIP_COMPRESSION', 'false', 'Enable HTTP GZip compression.', '14', '1', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Compression Level', 'GZIP_LEVEL', '5', 'Use this compression level 0-9 (0 = minimum, 9 = maximum).', '14', '2', now())");

              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Session Directory', 'SESSION_WRITE_DIRECTORY', '/tmp', 'If sessions are file based, store them in this directory.', '15', '1', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Force Cookie Use', 'SESSION_FORCE_COOKIE_USE', 'False', 'Force the use of sessions when cookies are only enabled.', '15', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check SSL Session ID', 'SESSION_CHECK_SSL_SESSION_ID', 'False', 'Validate the SSL_SESSION_ID on every secure HTTPS page request.', '15', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check User Agent', 'SESSION_CHECK_USER_AGENT', 'False', 'Validate the clients browser user agent on every page request.', '15', '4', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Check IP Address', 'SESSION_CHECK_IP_ADDRESS', 'False', 'Validate the clients IP address on every page request.', '15', '5', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Prevent Spider Sessions', 'SESSION_BLOCK_SPIDERS', 'False', 'Prevent known spiders from starting a session.', '15', '6', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
              tep_db_query("INSERT INTO " .$stores_config_table . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Recreate Session', 'SESSION_RECREATE', 'False', 'Recreate the session to generate a new session ID when the customer logs on or creates an account (PHP >=4.1 needed).', '15', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
//rmh M-S_multi-stores begin
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website URL', 'HTTP_CATALOG_SERVER', 'http://".$web."', 'The URL for your stores catalog (eg. http://www.domain.com)', 16, 1, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website SSL URL', 'HTTPS_CATALOG_SERVER', '', 'The SSL URL for your stores catalog (eg. https://www.domain.com)', 16, 2, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Enable SSL Store Catalog', 'ENABLE_SSL_CATALOG', 'false', 'Enable SSL links for Store Catalog', 16, 3, now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");

              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website Path', 'DIR_WS_CATALOG', 'http://".$web."/".$folder."/"."', 'Directory Website Path for Store Catalog (absolute path required -- eg. /catalog/)', 16, 4, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Path', 'DIR_FS_CATALOG',  '".$to_path."', 'Directory Filesystem Path for Store Catalog (absolute path required -- eg. /home/user/public_html/catalog/)', 16, 5, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website Images Path', 'DIR_WS_CATALOG_IMAGES', '".$url."/images/"."', 'Store Catalog Website Images Path (with trailing slash -- eg. http://www.domain.com/catalog/images/)', 16, 6, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website Languages Path', 'DIR_WS_CATALOG_LANGUAGES', '".$url."/includes/languages/"."', 'Store Catalog Website Languages Path (with trailing slash -- eg. http://www.domain.com/catalog/includes/languages/)', 16, 7, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Languages Path', 'DIR_FS_CATALOG_LANGUAGES', '".$to_path."/includes/languages"."', 'Store Catalog Filesystem Languages Path (with trailing slash -- eg. /home/user/public_html/catalog/includes/languages/)', 16, 8, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Images Path', 'DIR_FS_CATALOG_IMAGES', '".$to_path."/images"."', 'Store Catalog Filesystem Images Path (with trailing slash -- eg. /home/user/public_html/catalog/images/)', 16, 9, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Modules Path', 'DIR_FS_CATALOG_MODULES', '".$to_path."/includes/modules"."', 'Store Catalog Filesystem Modules Path (with trailing slash -- eg. /home/user/public_html/catalog/includes/modules/)', 16, 10, now(), now(), NULL, NULL)");
//rmh M-S_multi-stores end

	      // store the data relative to the manager
	     tep_db_query('insert into administrators (administrators_username, administrators_password, administrators_allowed_pages, administrators_allowed_stores, administrators_selected_stores_id) values ("'.$administrators_username.'", "'.md5($administrators_password).'", "configuration.php|catalog.php|customers.php|orders.php|reports.php|tools.php", '.$stores_id.', '.$stores_id.')');
            }
          } elseif ($action == 'save') {
            if (($entry_stores_config_table_unchanged == false) && (tep_table_exists($stores_config_table) == false)) {
              tep_db_query("RENAME TABLE " . $check_config_table['stores_config_table'] . " TO " . tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']));
              $messageStack->add_session(TEXT_UPDATE_WARNING_CONFIG, 'warning');
            }

            $sql_data_array['last_modified'] = 'now()';

            tep_db_perform(TABLE_STORES, $sql_data_array, 'update', "stores_id = '" . (int)$stores_id . "'");
	    
	    // let update the manager username
	    tep_db_query('update administrators set administrators_username = "'.$administrators_username.'" where administrators_selected_stores_id = '.$stores_id);

	    // let update the manager password
	    if ($administrators_password != '*****')
	    	tep_db_query('update administrators set administrators_password = "'.md5($administrators_password).'" where administrators_selected_stores_id = '.$stores_id);
          }

          if ($stores_image = new upload('stores_image', DIR_FS_CATALOG_IMAGES)) {
            tep_db_query("update " . TABLE_STORES . " set stores_image = '" . $stores_image->filename . "' where stores_id = '" . (int)$stores_id . "'");
          }
          tep_redirect(tep_href_link(FILENAME_STORES, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'storeID=' . $stores_id));

        } else if ($error == true) {
          if (($entry_stores_name_error == true) OR ($entry_stores_config_table_error == true)) $messageStack->add_session(ERROR_STORES_NAME_CONFIG, 'error');
          if ($entry_stores_config_table_exists_error == true) $messageStack->add_session(ERROR_STORES_CONFIG_TABLE_EXISTS, 'error');
	  // added 
	  if ($entry_stores_manager_error == true) $messageStack->add_session('Store manager data cannot be empty', 'error');	
	  if ($entry_stores_url == true) $messageStack->add_session('Store URL cannot be empty', 'error');	 	
          tep_redirect(tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page']));
        }
        break;
      case 'deleteconfirm':
        $stores_id = tep_db_prepare_input($HTTP_GET_VARS['storeID']);

	// delete store's folder
	//DIR_FS_CATALOG
	$store_query = tep_db_query("select stores_image, stores_config_table from " . TABLE_STORES . " where stores_id = '" . (int)$stores_id . "'");
	$store = tep_db_fetch_array($store_query);
	
	$config_table = $store['stores_config_table'];
	$delete_store_query = tep_db_query('select configuration_value from ' . $config_table . ' where configuration_key = \'DIR_FS_CATALOG\'');

	$delete_store = tep_db_fetch_array($delete_store_query);
	
	// delete folder (DANGER)
	rmdird($delete_store['configuration_value']);

	// delete store manager
	tep_db_query('DELETE FROM ' .TABLE_ADMINISTRATORS. ' WHERE administrators_selected_stores_id = '.$stores_id);

        if ($stores_id == '1') {
          $messageStack->add(ERROR_DEFAULT_STORE, 'error');
          break;
        }
        $store_query = tep_db_query("select stores_image, stores_config_table from " . TABLE_STORES . " where stores_id = '" . (int)$stores_id . "'");
        $store = tep_db_fetch_array($store_query);

        if (isset($HTTP_POST_VARS['delete_image']) && ($HTTP_POST_VARS['delete_image'] == 'on')) {
          $image_location = DIR_FS_CATALOG_IMAGES . $store['stores_image']; //rmh M-S_fixes

          if (file_exists($image_location)) @unlink($image_location);
        }
        if (isset($HTTP_POST_VARS['delete_table']) && ($HTTP_POST_VARS['delete_table'] == 'on')) {
          tep_db_query("DROP TABLE IF EXISTS " . $store['stores_config_table']);
        }

        tep_db_query("delete from " . TABLE_STORES . " where stores_id = '" . (int)$stores_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_TO_STORES . " where stores_id = '" . (int)$stores_id . "'");
        tep_db_query("delete from " . TABLE_CATEGORIES_TO_STORES . " where stores_id = '" . (int)$stores_id . "'");
        tep_db_query("update " . TABLE_SPECIALS . " set stores_id = '0' where stores_id = '" . (int)$stores_id . "'");
        tep_db_query("delete from " . TABLE_MANUFACTURERS_TO_STORES . " where stores_id = '" . (int)$stores_id . "'");

        tep_redirect(tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STORES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $stores_query_raw = "select stores_id, stores_name, stores_image, stores_url, stores_config_table, stores_status, date_added, last_modified from " . TABLE_STORES . " order by stores_id";
  $stores_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $stores_query_raw, $stores_query_numrows);
  $stores_query = tep_db_query($stores_query_raw);
  while ($stores = tep_db_fetch_array($stores_query)) {
    if ((!isset($HTTP_GET_VARS['storeID']) || (isset($HTTP_GET_VARS['storeID']) && ($HTTP_GET_VARS['storeID'] == $stores['stores_id']))) && !isset($storeInfo) && (substr($action, 0, 3) != 'new')) {
      $store_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS_TO_STORES . " where stores_id = '" . (int)$stores['stores_id'] . "'");
      $store_products = tep_db_fetch_array($store_products_query);

      $storeInfo_array = array_merge($stores, $store_products);
      $storeInfo = new objectInfo($storeInfo_array);
    }

    if (isset($storeInfo) && is_object($storeInfo) && ($stores['stores_id'] == $storeInfo->stores_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $stores['stores_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $stores['stores_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $stores['stores_name']; ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($stores['stores_status'] == '1') {
        echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_STORES, 'action=setflag&flag=0&storeID=' . $stores['stores_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link(FILENAME_STORES, 'action=setflag&flag=1&storeID=' . $stores['stores_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($storeInfo) && is_object($storeInfo) && ($stores['stores_id'] == $storeInfo->stores_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $stores['stores_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $stores_split->display_count($stores_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_STORES); ?></td>
                    <td class="smallText" align="right"><?php echo $stores_split->display_links($stores_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_STORE . '</b>');

      $contents = array('form' => tep_draw_form('stores', FILENAME_STORES, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_STORES_NAME . '<br>' . tep_draw_input_field('stores_name', '', 'size="24"', true));
      $contents[] = array('text' => '<br>' . TEXT_STORES_IMAGE . '<br>' . tep_draw_file_field('stores_image'));
      $contents[] = array('text' => '<br>' . TEXT_STORES_URL . '<br>' . tep_draw_input_field('stores_url', $storeInfo->stores_url,'size="24"', true));
      
      // because we consider a store's configuration table like 'configuration_'.store_name, 
      // we don't need this input text
      //$contents[] = array('text' => '<br>' . TEXT_STORES_CONFIG_TABLE . '<br>' . tep_draw_input_field('stores_config_table', $storeInfo->stores_config_table));
      
      // draw the same checkbox but like a hidden field with the same effect
      $contents[] = array('text' => '<br><u>Now, type data for the store manager</u>' . tep_draw_hidden_selection_field('insert_table', '', true, true));

      // now, we can request the name of the stores manager
      $contents[] = array('text' => 'Manager name');
      $contents[] = array('text' => tep_draw_input_field('administrators_username', '', 'maxlength="32" size="24"', true));

      // and a password
      $contents[] = array('text' => 'Password');
      $contents[] = array('text' => tep_draw_input_field('administrators_password', '', 'maxlength="32" size="24"', true));

      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_draw_hidden_field('stores_status', '1') . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $HTTP_GET_VARS['storeID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      // get store manager info
      $store_manager_query = tep_db_query('SELECT administrators_username FROM '.TABLE_ADMINISTRATORS.' WHERE administrators_selected_stores_id = ' . $storeInfo->stores_id);
      $store_manager_array = tep_db_fetch_array($store_manager_query);

      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_STORE . '</b>');

      $contents = array('form' => tep_draw_form('stores', FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id . '&action=save', 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('stores_status', $storeInfo->stores_status));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_STORES_NAME . '<br>' . tep_draw_input_field('stores_name', $storeInfo->stores_name, 'size="24"', 'size="24"'));
      $contents[] = array('text' => '<br>' . TEXT_STORES_IMAGE . '<br>' . tep_draw_file_field('stores_image') . '<br>' . $storeInfo->stores_image);
      $contents[] = array('text' => '<br>' . TEXT_STORES_URL . '<br>' . tep_draw_input_field('stores_url', $storeInfo->stores_url,'size="24" readonly="readonly" title="'.$storeInfo->stores_url.'"'));

      // because we consider a store's configuration table like 'configuration_'.store_name, 
      // we don't need this input text
      //$contents[] = array('text' => '<br>' . TEXT_STORES_CONFIG_TABLE . '<br>' . tep_draw_input_field('stores_config_table', $storeInfo->stores_config_table));

      // draw the same checkbox but like a hidden field with the same effect
      $contents[] = array('text' => '<br><u>Here is the data for the store manager</u>');

      // now, we can request the name of the stores manager
      $contents[] = array('text' => 'Manager name');
      $contents[] = array('text' => tep_draw_input_field('administrators_username', $store_manager_array['administrators_username'], 'size="24" maxlength="32"', true));

      // and a password
      $contents[] = array('text' => 'Password');
      $contents[] = array('text' => tep_draw_input_field('administrators_password', '*****', 'size="24" maxlength="32"', true));

      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_STORE . '</b>');

      $contents = array('form' => tep_draw_form('stores', FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $storeInfo->stores_name . '</b>');
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_table', '', true) . ' ' . TEXT_DELETE_TABLE);

      if ($storeInfo->products_count > 0) {
        $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $storeInfo->products_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($storeInfo) && is_object($storeInfo)) {
        $heading[] = array('text' => '<b>' . $storeInfo->stores_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($storeInfo->date_added));
        if (tep_not_null($storeInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($storeInfo->last_modified));
        $contents[] = array('text' => '<br>' . tep_info_image($storeInfo->stores_image, $storeInfo->stores_name));
        $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $storeInfo->products_count);
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
