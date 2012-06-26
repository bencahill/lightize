<?php

spl_autoload_register();

$db = new ezSQL_pdo('sqlite:'.Config::cacheDir.'/image.db');
$db->query("PRAGMA foreign_keys = ON");

?>
