In first place... sorry for the traduction ;) I'm spanish, so... can a native english speaker improve this traduction? Thanks very much!

--------------------------------------------------------------------------------------------------------------
AUTOMATIC STORES GENERATION WITH OSCOMMERCE AND MULTI-STORES ADD-ON
USING A LAMPP ENVIRONMENT (Apache, MySQL and PHP under LINUX)
AUTHOR: Jose Ignacio Alvarez Ruiz
WEB: http://www.nacho-alvarez.es
--------------------------------------------------------------------------------------------------------------
Description:

The magnific Multi-Stores add-on (http://addons.oscommerce.com/info/1730) allow to modify the OsCommerce structure in order to maintenance different stores. For now, and it is all that I know, the only way for duplicate a store was copying manually the folder that contains the Oscommerce installation to a well-know path.

With the modifications I suggest, when stores are going to be created, the name and password of the manager can be specified and the following will be ocurr:

* An automatic copy of your master store will be made.
* A manager for the new store will be created, without visit the 'Administrators' menu.
* If you make changes in the master store, these changes will be showed in the rest of the stores, without need of aditional changes.
* When you delete a store, the data of the manager, the data of the store and the data of database relative to this store will be deleted.

To-Do:

Some new text is writed in english in order to don't modify various files of the OsCommerce structure. It is necessary to define constants for each language and text.
-------------------------------------------------------------------------------------------------


---------------------------------------
INSTALLATION REQUIREMENTS
---------------------------------------

* LAMPP environment (Apache, MySQL and PHP under LINUX) with PERMISSIONS OF SYSTEM'S ADMINISTRATOR. This requirement is INDISPENSABLE for the good working of this add-on.

* The stores path must to be like http://localhost/project/catalog/, in order to the successive stores will be stored in /project/store2, /project/store3, etc... 

* The /opt/lampp/htdocs/project path (or equivalent) must to be writing permissions (777) in order to allow the automatic copy for the new stores.

-------------------------------------
INSTALLATION INSTRUCTIONS
-------------------------------------

* BACKUP your application data and your database.

* Install OsCommerce in normal way, in example from http://localhost/project/catalog

* Install the Multi-Stores add-on. Update database following the instructions contained in the compressed file.

* Copy 'catalog' folder and name to this copy 'minimal'. This 'minimal' folder will be used like 'master store'. This store can't be showed in the database rows like a shop installed in the system and, nevertheless, will be linked by the rest of the stores, so a change in the PHP code of the 'minimal' store cause this change be effective in the other stores installed. This is in this way because the stores files are soft links to the 'minimal' store or 'master store'.

* In this point, we have two ways in order to install this add-on. We can see in first place the fastest way and then the manual way.

+++++++++
FASTEST WAY
+++++++++

* DO YOU REALLY BACKUP YOUR DATA?

* Copy stores.php, body-database-tables.php and body-configure.php files from the 'admin' folder contained in the compressed file into the 'admin' folder from your OsCommerce installation.

* ATENTTION: IMPORTANT STEP. Change body-configure.php for this file contains the same configuration as catalog/includes/configure.php, that is to say, the correct configuration of your store.

* In the original version of the MultiStore add-on, deactivate a store when you click in the red button that appears on its name doesn't work for me. To correct this fact, copy the index.php file from the 'minimal' folder contained in the zipped file of this add-on into your 'minimal' folder.

++++++++
MANUAL WAY
++++++++

* DO YOU REALLY BACKUP YOUR DATA?

* Copy the body-database-tables.php and body-configure.php files from the 'admin' folder contained in the zipped file into your 'admin' folder of your OsCommerce installation.

* ATTENTION: VERY IMPORTANT STEP! Modify your body-configure.php in order to this file have the same configuration as catalog/includes/configure.php, that is to say, the correct configuration of your store. If you are not sure about this point, look up an example in the same 'body-configure.php' file.

/////////////////////////////////////////////////////////////////////////////////////////////////////////

Now, you should modify your STORES.PHP files following these instructions:

***************************************
Look for this at around line 13:
***************************************

require('includes/application_top.php'); 

***************************************
write below:
***************************************

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


***************************************
Look for this at around line 117, replace this:
***************************************

  $stores_config_table = tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']);

***************************************
  with this:
***************************************

	//$stores_config_table = tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']);
	$stores_config_table = 'configuration_'.$stores_name;
	// get the store url
	$stores_url = tep_db_prepare_input($HTTP_POST_VARS['stores_url']);

	// get the data of the store manager
	$administrators_username = tep_db_prepare_input($HTTP_POST_VARS['administrators_username']);
	$administrators_password = tep_db_prepare_input($HTTP_POST_VARS['administrators_password']);


***************************************
Look for this at around line 129:
***************************************

$entry_stores_config_table_unchanged = false;

***************************************
write below:
***************************************

$entry_stores_url = false;
$entry_stores_manager_error = false;


***************************************
Look for this at around line 133:
***************************************

        if (!tep_not_null($stores_name)) {
          $error = true;
          $entry_stores_name_error = true;
        }

***************************************
write below:
***************************************

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

***************************************
Look for this at around line 166, replace this:
***************************************

        if (($error == false) && ($entry_stores_config_table_exists_error == false)) {
          $sql_data_array = array('stores_name' => tep_db_prepare_input($HTTP_POST_VARS['stores_name']),
                                  'stores_url'  => tep_db_prepare_input($HTTP_POST_VARS['stores_url']),
                                  'stores_config_table'  => tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']),
                                  'stores_status' => tep_db_prepare_input($HTTP_POST_VARS['stores_status']));

***************************************
with this:
***************************************

        if (($error == false) && ($entry_stores_config_table_exists_error == false)) {
          $sql_data_array = array('stores_name' => tep_db_prepare_input($HTTP_POST_VARS['stores_name']),
                                  'stores_url'  => tep_db_prepare_input($HTTP_POST_VARS['stores_url']),
                                  'stores_config_table'  => $stores_config_table,
                                  'stores_status' => tep_db_prepare_input($HTTP_POST_VARS['stores_status']));


***************************************
Look for this at around line 176:
***************************************

$stores_id = tep_db_insert_id();

***************************************
write below:
***************************************

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


***************************************
Look for this at around line 240, replace this:
***************************************

            if (isset($HTTP_POST_VARS['insert_table']) && ($HTTP_POST_VARS['insert_table'] == 'on')) {
              tep_db_query("CREATE TABLE " . tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']) . " (configuration_id int NOT NULL auto_increment, configuration_title varchar(64) NOT NULL, configuration_key varchar(64) NOT NULL, configuration_value varchar(255) NOT NULL, configuration_description varchar(255) NOT NULL, configuration_group_id int NOT NULL, sort_order int(5) NULL, last_modified datetime NULL, date_added datetime NOT NULL, use_function varchar(255) NULL, set_function varchar(255) NULL, PRIMARY KEY (configuration_id))");

***************************************
with this:
***************************************
           if (isset($HTTP_POST_VARS['insert_table']) && ($HTTP_POST_VARS['insert_table'] == 'on')) {
              tep_db_query("CREATE TABLE " . $stores_config_table . " (configuration_id int NOT NULL auto_increment, configuration_title varchar(64) NOT NULL, configuration_key varchar(64) NOT NULL, configuration_value varchar(255) NOT NULL, configuration_description varchar(255) NOT NULL, configuration_group_id int NOT NULL, sort_order int(5) NULL, last_modified datetime NULL, date_added datetime NOT NULL, use_function varchar(255) NULL, set_function varchar(255) NULL, PRIMARY KEY (configuration_id))");


***************************************
Look for this at around line 402, replace this:
***************************************

//rmh M-S_multi-stores begin
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website URL', 'HTTP_CATALOG_SERVER', '', 'The URL for your stores catalog (eg. http://www.domain.com)', 16, 1, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website SSL URL', 'HTTPS_CATALOG_SERVER', '', 'The SSL URL for your stores catalog (eg. https://www.domain.com)', 16, 2, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Enable SSL Store Catalog', 'ENABLE_SSL_CATALOG', 'false', 'Enable SSL links for Store Catalog', 16, 3, now(), now(), NULL, 'tep_cfg_select_option(array(\'true\', \'false\'),')");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website Path', 'DIR_WS_CATALOG', '', 'Directory Website Path for Store Catalog (absolute path required -- eg. /catalog/)', 16, 4, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Path', 'DIR_FS_CATALOG', '', 'Directory Filesystem Path for Store Catalog (absolute path required -- eg. /home/user/public_html/catalog/)', 16, 5, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website Images Path', 'DIR_WS_CATALOG_IMAGES', '', 'Store Catalog Website Images Path (with trailing slash -- eg. http://www.domain.com/catalog/images/)', 16, 6, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Website Languages Path', 'DIR_WS_CATALOG_LANGUAGES', '', 'Store Catalog Website Languages Path (with trailing slash -- eg. http://www.domain.com/catalog/includes/languages/)', 16, 7, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Languages Path', 'DIR_FS_CATALOG_LANGUAGES', '', 'Store Catalog Filesystem Languages Path (with trailing slash -- eg. /home/user/public_html/catalog/includes/languages/)', 16, 8, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Images Path', 'DIR_FS_CATALOG_IMAGES', '', 'Store Catalog Filesystem Images Path (with trailing slash -- eg. /home/user/public_html/catalog/images/)', 16, 9, now(), now(), NULL, NULL)");
              tep_db_query("INSERT INTO " .$stores_config_table . " VALUES ('', 'Store Catalog Filesystem Modules Path', 'DIR_FS_CATALOG_MODULES', '', 'Store Catalog Filesystem Modules Path (with trailing slash -- eg. /home/user/public_html/catalog/includes/modules/)', 16, 10, now(), now(), NULL, NULL)");
//rmh M-S_multi-stores end

***************************************
with this:
***************************************

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


***************************************
Look for this at around line 426:
***************************************

            tep_db_perform(TABLE_STORES, $sql_data_array, 'update', "stores_id = '" . (int)$stores_id . "'");

***************************************
write below:
***************************************

	    // let update the manager username
	    tep_db_query('update administrators set administrators_username = "'.$administrators_username.'" where administrators_selected_stores_id = '.$stores_id);

	    // let update the manager password
	    if ($administrators_password != '*****')
	    	tep_db_query('update administrators set administrators_password = "'.md5($administrators_password).'" where administrators_selected_stores_id = '.$stores_id);


***************************************
Look for this at around line 443:
***************************************

          if ($entry_stores_config_table_exists_error == true) $messageStack->add_session(ERROR_STORES_CONFIG_TABLE_EXISTS, 'error');
          
***************************************
write below:
***************************************

	  // added 
	  if ($entry_stores_manager_error == true) $messageStack->add_session('Store manager data cannot be empty', 'error');	
	  if ($entry_stores_url == true) $messageStack->add_session('Store URL cannot be empty', 'error');	 	


***************************************
Look for this at around line 450:
***************************************

      case 'deleteconfirm':
        $stores_id = tep_db_prepare_input($HTTP_GET_VARS['storeID']);

***************************************
write below:
***************************************

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


***************************************
Look for this at around line 597, replace this:
***************************************

      $contents[] = array('text' => '<br>' . TEXT_STORES_NAME . '<br>' . tep_draw_input_field('stores_name'));
      $contents[] = array('text' => '<br>' . TEXT_STORES_IMAGE . '<br>' . tep_draw_file_field('stores_image'));
      $contents[] = array('text' => '<br>' . TEXT_STORES_URL . '<br>' . tep_draw_input_field('stores_url', $storeInfo->stores_url));
      $contents[] = array('text' => '<br>' . TEXT_STORES_CONFIG_TABLE . '<br>' . tep_draw_input_field('stores_config_table', $storeInfo->stores_config_table));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('insert_table', '', true) . ' ' . TEXT_INSERT_TABLE);

***************************************
with this:
***************************************

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


***************************************
Look for this at around line 617, replace the whole 'case':
***************************************

    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_STORE . '</b>');

      $contents = array('form' => tep_draw_form('stores', FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id . '&action=save', 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('stores_status', $storeInfo->stores_status));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_STORES_NAME . '<br>' . tep_draw_input_field('stores_name', $storeInfo->stores_name));
      $contents[] = array('text' => '<br>' . TEXT_STORES_IMAGE . '<br>' . tep_draw_file_field('stores_image') . '<br>' . $storeInfo->stores_image);
      $contents[] = array('text' => '<br>' . TEXT_STORES_URL . '<br>' . tep_draw_input_field('stores_url', $storeInfo->stores_url));
      $contents[] = array('text' => '<br>' . TEXT_STORES_CONFIG_TABLE . '<br>' . tep_draw_input_field('stores_config_table', $storeInfo->stores_config_table));
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_STORES, 'page=' . $HTTP_GET_VARS['page'] . '&storeID=' . $storeInfo->stores_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

***************************************
with this:
***************************************

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////

Finally, modify the 'index.php' file from the 'minimal' folder in the following way:

***************************************
Look for this at around line 13:
***************************************

  require('includes/application_top.php');

***************************************
write below:
***************************************

  // check if the store has been disabled
  $store_status_query = tep_db_query("select stores_status from " . TABLE_STORES . " where stores_id = ".STORES_ID);
  $store_status = tep_db_fetch_array($store_status_query);

  if ($store_status['stores_status'] == 0)
  {
	echo 'Store disabled by the system administrator';
	exit();
  }


That's all! Enjoy!
