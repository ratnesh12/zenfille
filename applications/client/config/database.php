<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'default';
$active_record = TRUE;
 
$db['default']['hostname'] = 'localhost';
if ($_SERVER["HTTP_HOST"] == "localhost") {
    $db['default']['username'] = 'root';
    $db['default']['password'] = '';
    $db['default']['database'] = 'zenfile_dev';
}
elseif($_SERVER["HTTP_HOST"] == "zenfile.local")
{
    $db['default']['username'] = 'root';
    $db['default']['password'] = 'aw34res';
    $db['default']['database'] = 'zenfile.com';
}
elseif($_SERVER["HTTP_HOST"] == "dev.zenfile.com" || $_SERVER["HTTP_HOST"] == "parkipfiling.com" || $_SERVER["HTTP_HOST"] == "www.parkipfiling.com")
{

    $db['default']['username'] = 'zenfile_dev';
    $db['default']['password'] = 'zenfile_dev!hell@#';
    $db['default']['database'] = 'zenfile_dev';
} elseif ($_SERVER["HTTP_HOST"] == "parkip.com") {
    $db['default']['hostname'] = 'zenfile.com';
    $db['default']['username'] = 'zenfile_dev';
    $db['default']['password'] = 'zenfile_dev!hell@#';
    $db['default']['database'] = 'zenfile_dev';
} else {
    $db['default']['username'] = 'zenfile_app';
    $db['default']['password'] = 'Qlsi$Nb7!8m?';
    $db['default']['database'] = 'zenfile_app';
}


$db['default']['dbdriver'] = 'mysql';
$db['default']['dbprefix'] = 'zen_';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;


$db['antongorodezkiy_local']['hostname'] = 'localhost';
$db['antongorodezkiy_local']['username'] = 'root';
$db['antongorodezkiy_local']['password'] = 'root';
$db['antongorodezkiy_local']['database'] = 'zenfile_app';
$db['antongorodezkiy_local']['dbdriver'] = 'mysql';
$db['antongorodezkiy_local']['dbprefix'] = 'zen_';
$db['antongorodezkiy_local']['pconnect'] = TRUE;
$db['antongorodezkiy_local']['db_debug'] = TRUE;
$db['antongorodezkiy_local']['cache_on'] = FALSE;
$db['antongorodezkiy_local']['cachedir'] = '';
$db['antongorodezkiy_local']['char_set'] = 'utf8';
$db['antongorodezkiy_local']['dbcollat'] = 'utf8_general_ci';
$db['antongorodezkiy_local']['swap_pre'] = '';
$db['antongorodezkiy_local']['autoinit'] = TRUE;
$db['antongorodezkiy_local']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */