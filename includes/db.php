<?php
//define('DB_SERVER','127.0.0.1');

// define('DB_SERVER','localhost');
// define('DB_USER','root');
// define('DB_PASS','');
// define('DB_NAME','db_webgallery');

defined('DB_SERVER') or define('DB_SERVER', 'd13xat1hwxt21t45.cbetxkdyhwsb.us-east-1.rds.amazonaws.com');
defined('DB_USER')   or define('DB_USER', 'v9gfxtishfkmeq4s');
defined('DB_PASS')   or define('DB_PASS', 'x0ucnmiu2xsy902h');
defined('DB_NAME')   or define('DB_NAME', 'fw2j6o8llwuaa6sl');

// defined('DB_SERVER') or define('DB_SERVER', '');
// defined('DB_USER')   or define('DB_USER', '');
// defined('DB_PASS')   or define('DB_PASS', '');
// defined('DB_NAME')   or define('DB_NAME', '');

$connection = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
 mysqli_set_charset($connection,"utf8mb4");
// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

