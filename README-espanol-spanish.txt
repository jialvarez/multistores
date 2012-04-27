--------------------------------------------------------------------------------------------------
GENERACIÓN AUTOMÁTICA DE TIENDAS CON OSCOMMERCE Y MULTI-STORES
UTILIZANDO UN ENTORNO LAMPP (Apache, MySQL y PHP bajo LINUX)
AUTOR: José Ignacio Álvarez Ruiz
WEB: http://www.nacho-alvarez.es
--------------------------------------------------------------------------------------------------
Descripción:

El magnífico add-on Multi-Stores (http://addons.oscommerce.com/info/1730) permite modificar la estructura de OsCommerce de forma que se puedan mantener varias tiendas. Hasta ahora, por lo que yo conozco, la única forma de replicar las tiendas era copiando manualmente la carpeta con la instalación de OsCommerce a una ubicación bien conocida.

Con las modificaciones que propongo, al crear tiendas, se puede especificar el nombre y el password del gestor y ocurrirá lo siguiente:

* Se realizará una copia automática de su tienda maestra.
* Se insertará el gestor de forma automática, sin tener que pasar por el menú 'Administradores'.
* Si usted realiza cambios en la tienda maestra, éstos se reflejan en el resto de tiendas del sistema sin necesidad de cambios adicionales.
* Cuando usted elimina una tienda, se eliminan tanto los datos del gestor que la mantiene como la carpeta que contiene los datos de la tienda, así como los datos contenidos en la base de datos relativos a dicha tienda.

Por hacer:

Algunos textos nuevos están escritos en inglés para no modificar varios ficheros de la estructura de OsCommerce. Es necesario definir constantes para cada idioma y cada texto.
-------------------------------------------------------------------------------------------------


---------------------------------------------
REQUISITOS PARA LA INSTALACIÓN
---------------------------------------------

* Entorno LAMPP (Apache, MySQL y PHP bajo LINUX). Este requisito es INDISPENSABLE para el funcionamiento de este add-on.

* La ruta debe ser del tipo http://localhost/project/catalog/, de forma que las sucesivas tiendas irán en /project/store2, /project/store3, etc... 

* El directorio /opt/lampp/htdocs/project (o equivalente) debe tener permisos de escritura (777) para poder realizarse la copia automática de las nuevas tiendas.

---------------------------------------------
INSTRUCCIONES DE INSTALACIÓN
---------------------------------------------

* REALIZAR UNA COPIA de los datos de la aplicación y de la base de datos.

* Instalar OsCommerce de forma normal, por ejemplo desde http://localhost/project/catalog

* Instalar el add-on Multi-stores. Actualizar la base de datos según las instrucciones del add-on.

* Para corregir el error de imagen que trae el add-on MultiStore por defecto en el panel de administración, modificamos la extensión del archivo oscommerce.png por oscommerce.gif, y copiamos oscommerce.gif a catalog/images para que aparezca bien en la tienda.

* Duplicar la carpeta catalog y a la copia llamarla 'minimal'. Esta carpeta minimal será la que se utilizará como "tienda maestra". Dicha tienda no aparecerá en los registros de la base de datos como tienda instalada en el sistema y, sin embargo, enlazará al resto de tiendas, de forma que una modificación en el código PHP de la tienda 'minimal' provocará que dicho cambio sea efectivo en todas las tiendas instaladas en el sistema. Esto es así porque los archivos de las tiendas son puramente enlaces o 'soft links' a la tienda 'minimal' o tienda maestra.

* En este punto, tenemos dos formas de instalar este add-on. Veremos primero la forma rápida y después la forma manual.

+++++++++
FORMA RÁPIDA
+++++++++

* ¿REALMENTE HIZO YA LA COPIA DE SEGURIDAD DE SUS DATOS?

* Copiar stores.php, body-database-tables.php y body-configure.php de la carpeta admin proporcionada en el archivo comprimido dentro de la carpeta admin de su instalación de OsCommerce. 

* ATENCIÓN: PASO IMPORTANTE. Modificar body-configure.php de forma que tenga la misma configuración que catalog/includes/configure.php, es decir, la configuración correcta de su tienda. Si tiene dudas, consulte el ejemplo en el mismo fichero 'body-configure.php'.

* En la versión original del add-on MultiStore, a mi no me funcionaba el hecho de desactivar una tienda pulsando en el botón rojo que aparece sobre ella en el menú Tiendas. Para corregir esto, copie el archivo index.php de la carpeta minimal proporcionada en el archivo comprimido del add-on dentro de su carpeta 'minimal'.

++++++++++
FORMA MANUAL
++++++++++

* ¿REALMENTE HIZO YA LA COPIA DE SEGURIDAD DE SUS DATOS?

* Copiar body-database-tables.php y body-configure.php de la carpeta admin proporcionada en el archivo comprimido dentro de la carpeta admin de su instalación de OsCommerce. 

* ATENCIÓN: PASO IMPORTANTE. Modificar body-configure.php de forma que tenga la misma configuración que catalog/includes/configure.php, es decir, la configuración correcta de su tienda. Si tiene dudas, consulte el ejemplo en el mismo fichero 'body-configure.php'.

/////////////////////////////////////////////////////////////////////////////////////////////////////////

A continuación, debe modificar su fichero STORES.PHP siguiendo las instrucciones que se indican a continuación:

***************************************
Sobre la línea 13:
***************************************

require('includes/application_top.php'); 

***************************************
escribir debajo:
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
Sobre la línea 117, sustituir esto:
***************************************

  $stores_config_table = tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']);

***************************************
  por esto:
***************************************

	//$stores_config_table = tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']);
	$stores_config_table = 'configuration_'.$stores_name;
	// get the store url
	$stores_url = tep_db_prepare_input($HTTP_POST_VARS['stores_url']);

	// get the data of the store manager
	$administrators_username = tep_db_prepare_input($HTTP_POST_VARS['administrators_username']);
	$administrators_password = tep_db_prepare_input($HTTP_POST_VARS['administrators_password']);


***************************************
Sobre la línea 129:
***************************************

$entry_stores_config_table_unchanged = false;

***************************************
escribir debajo:
***************************************

$entry_stores_url = false;
$entry_stores_manager_error = false;


***************************************
Sobre la línea 133:
***************************************

        if (!tep_not_null($stores_name)) {
          $error = true;
          $entry_stores_name_error = true;
        }

***************************************
escribir debajo:
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
Sobre la línea 166, sustituir esto:
***************************************

        if (($error == false) && ($entry_stores_config_table_exists_error == false)) {
          $sql_data_array = array('stores_name' => tep_db_prepare_input($HTTP_POST_VARS['stores_name']),
                                  'stores_url'  => tep_db_prepare_input($HTTP_POST_VARS['stores_url']),
                                  'stores_config_table'  => tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']),
                                  'stores_status' => tep_db_prepare_input($HTTP_POST_VARS['stores_status']));

***************************************
por esto:
***************************************

        if (($error == false) && ($entry_stores_config_table_exists_error == false)) {
          $sql_data_array = array('stores_name' => tep_db_prepare_input($HTTP_POST_VARS['stores_name']),
                                  'stores_url'  => tep_db_prepare_input($HTTP_POST_VARS['stores_url']),
                                  'stores_config_table'  => $stores_config_table,
                                  'stores_status' => tep_db_prepare_input($HTTP_POST_VARS['stores_status']));


***************************************
Sobre la línea 176:
***************************************

$stores_id = tep_db_insert_id();

***************************************
escribir debajo:
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
Sobre la línea 240, sustituir esto:
***************************************

            if (isset($HTTP_POST_VARS['insert_table']) && ($HTTP_POST_VARS['insert_table'] == 'on')) {
              tep_db_query("CREATE TABLE " . tep_db_prepare_input($HTTP_POST_VARS['stores_config_table']) . " (configuration_id int NOT NULL auto_increment, configuration_title varchar(64) NOT NULL, configuration_key varchar(64) NOT NULL, configuration_value varchar(255) NOT NULL, configuration_description varchar(255) NOT NULL, configuration_group_id int NOT NULL, sort_order int(5) NULL, last_modified datetime NULL, date_added datetime NOT NULL, use_function varchar(255) NULL, set_function varchar(255) NULL, PRIMARY KEY (configuration_id))");

***************************************
por esto:
***************************************

           if (isset($HTTP_POST_VARS['insert_table']) && ($HTTP_POST_VARS['insert_table'] == 'on')) {
              tep_db_query("CREATE TABLE " . $stores_config_table . " (configuration_id int NOT NULL auto_increment, configuration_title varchar(64) NOT NULL, configuration_key varchar(64) NOT NULL, configuration_value varchar(255) NOT NULL, configuration_description varchar(255) NOT NULL, configuration_group_id int NOT NULL, sort_order int(5) NULL, last_modified datetime NULL, date_added datetime NOT NULL, use_function varchar(255) NULL, set_function varchar(255) NULL, PRIMARY KEY (configuration_id))");


***************************************
Sobre la línea 402, sustituir esto:
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
por esto:
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
Sobre la línea 426:
***************************************

            tep_db_perform(TABLE_STORES, $sql_data_array, 'update', "stores_id = '" . (int)$stores_id . "'");

***************************************
escribir debajo:
***************************************

	    // let update the manager username
	    tep_db_query('update administrators set administrators_username = "'.$administrators_username.'" where administrators_selected_stores_id = '.$stores_id);

	    // let update the manager password
	    if ($administrators_password != '*****')
	    	tep_db_query('update administrators set administrators_password = "'.md5($administrators_password).'" where administrators_selected_stores_id = '.$stores_id);


***************************************
Sobre la línea 443:
***************************************

          if ($entry_stores_config_table_exists_error == true) $messageStack->add_session(ERROR_STORES_CONFIG_TABLE_EXISTS, 'error');
          
***************************************
escribir debajo:
***************************************

	  // added 
	  if ($entry_stores_manager_error == true) $messageStack->add_session('Store manager data cannot be empty', 'error');	
	  if ($entry_stores_url == true) $messageStack->add_session('Store URL cannot be empty', 'error');	 	


***************************************
Sobre la línea 450:
***************************************

      case 'deleteconfirm':
        $stores_id = tep_db_prepare_input($HTTP_GET_VARS['storeID']);

***************************************
escribir debajo:
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
Sobre la línea 597, sustituir esto:
***************************************

      $contents[] = array('text' => '<br>' . TEXT_STORES_NAME . '<br>' . tep_draw_input_field('stores_name'));
      $contents[] = array('text' => '<br>' . TEXT_STORES_IMAGE . '<br>' . tep_draw_file_field('stores_image'));
      $contents[] = array('text' => '<br>' . TEXT_STORES_URL . '<br>' . tep_draw_input_field('stores_url', $storeInfo->stores_url));
      $contents[] = array('text' => '<br>' . TEXT_STORES_CONFIG_TABLE . '<br>' . tep_draw_input_field('stores_config_table', $storeInfo->stores_config_table));
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('insert_table', '', true) . ' ' . TEXT_INSERT_TABLE);

***************************************
por esto:
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
Sobre la línea 617, sustituir el caso entero:
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
por esto:
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

Por último, modifique el fichero 'index.php' de la carpeta 'minimal' de la siguiente forma:

***************************************
Sobre la línea 13:
***************************************

  require('includes/application_top.php');

***************************************
escribir debajo:
***************************************

  // check if the store has been disabled
  $store_status_query = tep_db_query("select stores_status from " . TABLE_STORES . " where stores_id = ".STORES_ID);
  $store_status = tep_db_fetch_array($store_status_query);

  if ($store_status['stores_status'] == 0)
  {
	echo 'Store disabled by the system administrator';
	exit();
  }


¡Eso es todo! Espero que puedan disfrutarlo.

