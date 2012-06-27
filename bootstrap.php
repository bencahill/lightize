<?php

spl_autoload_register();

define( "L_IMAGE_DIR", dirname( dirname( __FILE__ ) ) );
define( "L_CACHE_DIR", dirname( __FILE__ )."/cache" );

global $db;
$db = new ezSQL_pdo('sqlite:'.L_CACHE_DIR.'/image.db','foo','bar');
$db->query("PRAGMA foreign_keys = ON");

?>
