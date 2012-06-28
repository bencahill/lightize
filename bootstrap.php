<?php

spl_autoload_register();

define( "L_IMAGE_DIR", dirname( dirname( __FILE__ ) ) );
define( "L_CACHE_DIR", dirname( __FILE__ )."/cache" );

global $db;
$db = new PDO('sqlite:'.L_CACHE_DIR.'/image.db');
$db->exec("PRAGMA foreign_keys = ON");
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); 

?>
