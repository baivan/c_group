<?php

/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
$host = gethostname();
$endpointUrl ="";
if ($host == 'Jamess-MacBook-Air.local') {
   defined('LOG_PATH') || define('LOG_PATH', '/Applications/MAMP/htdocs/logs/envirofit/'); 
}
elseif ($host == 'jamess-air') {
  defined('LOG_PATH') || define('LOG_PATH', '/Applications/MAMP/htdocs/logs/envirofit/'); 
}
elseif($host == 'Jamess-Air'){
  defined('LOG_PATH') || define('LOG_PATH', '/Applications/MAMP/htdocs/logs/envirofit/'); 
}
else{
  defined('LOG_PATH') || define('LOG_PATH', '/usr/local/www/logs/c_group');  
}



defined('INFO_FILE') || define('INFO_FILE', 'ui_info.log');
defined('ERROR_FILE') || define('ERROR_FILE', 'ui_error.log');

//defined('INFO_FILE') || define('INFO_FILE', 'info.log');
//defined('ERROR_FILE') || define('ERROR_FILE', 'error.log');

return new \Phalcon\Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => 'localhost',
        'username' => 'covenant',
        'password' => 'covenant',
        'dbname' => 'covenant',
        'charset' => 'utf8',
    ],
    'application' => [
        'appDir' => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir' => APP_PATH . '/models/',
        'migrationsDir' => APP_PATH . '/migrations/',
        'viewsDir' => APP_PATH . '/views/',
        'pluginsDir' => APP_PATH . '/plugins/',
        'libraryDir' => APP_PATH . '/library/',
        'cacheDir' => BASE_PATH . '/cache/',
        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpspace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        //CREATE USER 'covenant'@'localhost' IDENTIFIED BY 'covenant';
        //GRANT ALL PRIVILEGES ON `covenant`.* TO 'covenant'@'localhost';
        'baseUri' => preg_replace('/public([\/\\\\])index.php$/', '', $_SERVER["PHP_SELF"]),
    ],
    'log' => LOG_PATH . '/',
    'endpoints' => [
      // 'core' => 'http://api.southwell.io/envirofit_api_alpha',
        //'core' => 'http://localhost:8888/envirofit_api_alpha',
        'core'=>$endpointUrl,
        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsb2NhbGhvc3Q'
        . 'iLCJpYXQiOjE0ODM5NTYwNDYsImFwcCI6ImphdmEzNjAiLCJvd25lciI6ImFub255bW91'
        . 'cyIsImFjdGlvbiI6Im9wZW5SZXF1ZXN0In0.eLHZjnFduufVspUz7E2QfTzKFfPqNWYBoENJbmIeZtA'
    ]
        ]);
