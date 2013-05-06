<?php
/*
| -------------------------------------------------------------------
| SMARTY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to Smarty template engine.
| For details see Smarty documentation.
|
*/

$config["smarty"]["template_dir"] = APPPATH . 'views/';

$config["smarty"]["compile_dir"]  = APPPATH  . 'templates_c/';

$config["smarty"]["config_dir"]   = APPPATH  . 'config/';

$config["smarty"]["cache_dir"]    = BASEPATH . 'cache/';

// default templates extension
$config["smarty"]["default_ext"] = 'tpl';

?>