<?php

/**
 * @author Jose Ignacio Alvarez Ruiz (http://www.nacho-alvarez.es)
 */

$body="<?php
  define('HTTP_SERVER', 'http://localhost');
  define('HTTPS_SERVER', 'http://localhost');
  define('ENABLE_SSL', false);
  define('HTTP_COOKIE_DOMAIN', '/proyecto/');
  define('HTTPS_COOKIE_DOMAIN', '/proyecto/');
  define('HTTP_COOKIE_PATH', '/proyecto/".$folder."/');
  define('HTTPS_COOKIE_PATH', '/proyecto/".$folder."/');
  define('DIR_WS_HTTP_CATALOG', '/proyecto/".$folder."/');
  define('DIR_WS_HTTPS_CATALOG', '/proyecto/".$folder."/');
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_MANUALS', 'manuals/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');

  define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
  define('DIR_FS_CATALOG', '/opt/lampp/htdocs/proyecto/".$folder."/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');

  define('DB_SERVER', 'localhost');
  define('DB_SERVER_USERNAME', 'root');
  define('DB_SERVER_PASSWORD', '');
  define('DB_DATABASE', 'catalog');
  define('USE_PCONNECT', 'false');
  define('STORE_SESSIONS', 'mysql'); 
?>"

?>
