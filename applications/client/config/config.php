<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

date_default_timezone_set("America/New_York");
// TEST_MODE is an option to send emails to a special list like client@zenfile.com, pm@zenfile.com, fa@zenfile.com 11

if (isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] == "zenfile.com")
{
    define('TEST_MODE', FALSE);
    if(! defined('TEST_CLIENT_EMAIL')){
        define('TEST_CLIENT_EMAIL', 'client@zenfile.com');
    }
    if(! defined('TEST_FA_EMAIL')){
        define('TEST_FA_EMAIL', 'fa@zenfile.com');
    }
    if(! defined('TEST_PM_EMAIL')){
        define('TEST_PM_EMAIL', 'pm@zenfile.com');
    }
    if(! defined('TEST_BDV_EMAIL')){
        define('TEST_BDV_EMAIL', 'bdv@zenfile.com');
    }
    if(! defined('TEST_FIRM_EMAIL')){
        define('TEST_FIRM_EMAIL', 'firm@zenfile.com');
    }
    if(! defined('TEST_SUPERVISOR_EMAIL')){
        define('TEST_SUPERVISOR_EMAIL', 'superviser@zenfile.com');
    }
}else{
    define('TEST_MODE', TRUE);

    if(! defined('TEST_CLIENT_EMAIL')){
        define('TEST_CLIENT_EMAIL', 'client@dev.zenfile.com');
    }
    if(! defined('TEST_FA_EMAIL')){
        define('TEST_FA_EMAIL', 'fa@dev.zenfile.com');
    }
    if(! defined('TEST_PM_EMAIL')){
        define('TEST_PM_EMAIL', 'pm@dev.zenfile.com');
    }
    if(! defined('TEST_BDV_EMAIL')){
        define('TEST_BDV_EMAIL', 'bdv@dev.zenfile.com');
    }
    if(! defined('TEST_FIRM_EMAIL')){
        define('TEST_FIRM_EMAIL', 'firm@dev.zenfile.com');
    }
    if(! defined('TEST_SUPERVISOR_EMAIL')){
        define('TEST_SUPERVISOR_EMAIL', 'superviser@dev.zenfile.com');
    }
}

// Increase for case number
$config['default_case_number_increase'] = 13;
$config['default_time_interval_between_cases'] = 10;

if ($_SERVER['HTTP_HOST'] == 'parkipfiling.com' || $_SERVER['HTTP_HOST'] == 'www.parkipfiling.com' || $_SERVER['HTTP_HOST'] == 'zen') {
    $config['title_of_the_site'] = 'ParkIP';
} else {
    $config['title_of_the_site'] = 'ZenFile';
}
/*
|--------------------------------------------------------------------------
| Base Site URL444
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| If this is not set then CodeIgniter will guess the protocol, domain and
| path to your installation.
|
*/
//$config['base_url']	= 'https://zenfile.com/client/';
if ($_SERVER['HTTP_HOST'] == 'parkip.com') {
    $config['base_url']     = 'http://localhost/new_project/client/';
} else {
    $config['base_url']     = 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].str_replace('//','/',dirname($_SERVER['SCRIPT_NAME']).'/');
}
$config['base_url_pm']  = 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/pm/';

//$config['cpanel_ip_address'] = '108.167.175.233';
$config['cpanel_ip_address'] = '127.0.0.1';
$config['cpanel_api_hash'] = '78af47c56d4c042b74639d18feb535ba5aafb0f29c9e6a3c29a24da47b6992dc2a6696e318533a40219876c9f50d3c045331464a7cea62f56050081ec027efa5806bd33695205234b59ee7d63addf8751a355710f6c3ed1f1c3cc4849034d939f1f9b6159248630572ce7ad23769fbc1146b0f7eb632b1bad3437fc9197c6d74010c1cfb163d4d644b0128a7f874aaefe37eba660c7832e3a2000302f618bda723a42240df0f41bbe8147ff897db7d64ee38090c08d27c4f727c69f56f2b39477d76af73ca25997f44d56ad585db059215422962bd30f77b7083cfc4c31c7e9d4c5cafa3bdf7082f40f147c9734e1a818d5525345fcdc817a223bb1d965945491bfbffa47c62792b73504a1cb97f887a80b700cd24ba602f9a6ed0dcee5f1b26273eebf75af1263df0ecc686e3b476f804ccbe536a285144cc0f6f3d5dc595a65d1dd019fd3780f8e140b46e4ba4b8a3e8597aaf67a0da9cee6582723027116eb62ede5ca1418e70bf667f66c50b4a3e833a416f8d37b7cc6c74656467c45ffde2f3373a28c3d3b5c1e0d9f36941bc7b25ad04e4060a8eebb031931b546f065b3946308c3b706a496891a15a6d0ba9a85c3725788663b84f4e09a3db6b32ff8c5d4c2bb954718be545f2ffb788c98644';
$config['cpanel_username'] = 'zenfile';
$config['cpanel_password'] = 'Ptp3e31z#';
$config['cpanel_skin'] = 'x3';
$config['cpanel_domain'] = 'zenfile.com';
$config['cpanel_tracker_domain'] = 'zenfile.com';
$config['cpanel_email_quota'] = '0';

$config['zenfile_bcc_email'] = 'brian.daniel@zenfile.com';
$config['zenfile_pm_email'] = 'levitan.co@gmail.com';
$config['zenfile_default_email_password'] = 'fKp9*5U9';
$config['default_email_box'] = '@zenfile.com';

$config['client_date_format'] = 'M d, Y';
/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
|
| Typically this will be your index.php file, unless you've renamed it to
| something else. If you are using mod_rewrite to remove the page set this
| variable so that it is blank.
|
*/
$config['index_page'] = '';




/*
|--------------------------------------------------------------------------
| URI PROTOCOL
|--------------------------------------------------------------------------
|
| This item determines which server global should be used to retrieve the
| URI string.  The default setting of 'AUTO' works for most servers.
| If your links do not seem to work, try one of the other delicious flavors:
|
| 'AUTO'			Default - auto detects
| 'PATH_INFO'		Uses the PATH_INFO
| 'QUERY_STRING'	Uses the QUERY_STRING
| 'REQUEST_URI'		Uses the REQUEST_URI
| 'ORIG_PATH_INFO'	Uses the ORIG_PATH_INFO
|
*/
$config['uri_protocol']	= 'AUTO';

/*
|--------------------------------------------------------------------------
| URL suffix
|--------------------------------------------------------------------------
|
| This option allows you to add a suffix to all URLs generated by CodeIgniter.
| For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/urls.html
*/

$config['url_suffix'] = '';

/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
|
| This determines which set of language files should be used. Make sure
| there is an available translation if you intend to use something other
| than english.
|
*/
$config['language']	= 'english';

/*
|--------------------------------------------------------------------------
| Default Character Set
|--------------------------------------------------------------------------
|
| This determines which character set is used by default in various methods
| that require a character set to be provided.
|
*/
$config['charset'] = 'UTF-8';

/*
|--------------------------------------------------------------------------
| Enable/Disable System Hooks
|--------------------------------------------------------------------------
|
| If you would like to use the 'hooks' feature you must enable it by
| setting this variable to TRUE (boolean).  See the user guide for details.
|
*/
$config['enable_hooks'] = true;


/*
|--------------------------------------------------------------------------
| Class Extension Prefix
|--------------------------------------------------------------------------
|
| This item allows you to set the filename/classname prefix when extending
| native libraries.  For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/core_classes.html
| http://codeigniter.com/user_guide/general/creating_libraries.html
|
*/
$config['subclass_prefix'] = 'MY_';


/*
|--------------------------------------------------------------------------
| Allowed URL Characters
|--------------------------------------------------------------------------
|
| This lets you specify with a regular expression which characters are permitted
| within your URLs.  When someone tries to submit a URL with disallowed
| characters they will get a warning message.
|
| As a security measure you are STRONGLY encouraged to restrict URLs to
| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
|
| Leave blank to allow all characters -- but only if you are insane.
|
| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
|
*/
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-?&=';


/*
|--------------------------------------------------------------------------
| Enable Query Strings
|--------------------------------------------------------------------------
|
| By default CodeIgniter uses search-engine friendly segment based URLs:
| example.com/who/what/where/
|
| By default CodeIgniter enables access to the $_GET array.  If for some
| reason you would like to disable it, set 'allow_get_array' to FALSE.
|
| You can optionally enable standard query string based URLs:
| example.com?who=me&what=something&where=here
|
| Options are: TRUE or FALSE (boolean)
|
| The other items let you set the query string 'words' that will
| invoke your controllers and its functions:
| example.com/index.php?c=controller&m=function
|
| Please note that some of the helpers won't work as expected when
| this feature is enabled, since CodeIgniter is designed primarily to
| use segment based URLs.
|
*/
$config['allow_get_array']		= TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger']	= 'c';
$config['function_trigger']		= 'm';
$config['directory_trigger']	= 'd'; // experimental not currently in use

/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
|
| If you have enabled error logging, you can set an error threshold to
| determine what gets logged. Threshold options are:
| You can enable error logging by setting a threshold over zero. The
| threshold determines what gets logged. Threshold options are:
|
|	0 = Disables logging, Error logging TURNED OFF
|	1 = Error Messages (including PHP errors)
|	2 = Debug Messages
|	3 = Informational Messages
|	4 = All Messages
|
| For a live site you'll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config['log_threshold'] = 1;

/*
|--------------------------------------------------------------------------
| Error Logging Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/logs/ folder. Use a full server path with trailing slash.
|
*/
$config['log_path'] = '';

/*
|--------------------------------------------------------------------------
| Date Format for Logs
|--------------------------------------------------------------------------
|
| Each item that is logged has an associated date. You can use PHP date
| codes to set your own date formatting
|
*/
$config['log_date_format'] = 'Y-m-d H:i:s';

/*
|--------------------------------------------------------------------------
| Cache Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| system/cache/ folder.  Use a full server path with trailing slash.
|
*/
$config['cache_path'] = '';

/*
|--------------------------------------------------------------------------
| Encryption Key
|--------------------------------------------------------------------------
|
| If you use the Encryption class or the Session class you
| MUST set an encryption key.  See the user guide for info.
|
*/
$config['encryption_key'] = 'zenapp3c5l3i';

/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
|
| 'sess_cookie_name'		= the name you want for the cookie
| 'sess_expiration'			= the number of SECONDS you want the session to last.
|   by default sessions last 7200 seconds (two hours).  Set to zero for no expiration.
| 'sess_expire_on_close'	= Whether to cause the session to expire automatically
|   when the browser window is closed
| 'sess_encrypt_cookie'		= Whether to encrypt the cookie
| 'sess_use_database'		= Whether to save the session data to a database
| 'sess_table_name'			= The name of the session database table
| 'sess_match_ip'			= Whether to match the user's IP address when reading the session data
| 'sess_match_useragent'	= Whether to match the User Agent when reading the session data
| 'sess_time_to_update'		= how many seconds between CI refreshing Session Information
|
*/
$config['sess_cookie_name']		= 'zen_session';
$config['sess_expiration']		= 7200;
$config['sess_expire_on_close']	= TRUE;
$config['sess_encrypt_cookie']	= TRUE;
$config['sess_use_database']	= TRUE;
$config['sess_table_name']		= 'zen_sessions';
$config['sess_match_ip']		= FALSE;
$config['sess_match_useragent']	= FALSE;
$config['sess_time_to_update']	= 7200;

/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
|
| 'cookie_prefix' = Set a prefix if you need to avoid collisions
| 'cookie_domain' = Set to .your-domain.com for site-wide cookies
| 'cookie_path'   =  Typically will be a forward slash
| 'cookie_secure' =  Cookies will only be set if a secure HTTPS connection exists.
|
*/
$config['cookie_prefix']	= "/";
$config['cookie_domain']	= "";
$config['cookie_path']		= "/";
$config['cookie_secure']	= FALSE;

/*
|--------------------------------------------------------------------------
| Global XSS Filtering
|--------------------------------------------------------------------------
|
| Determines whether the XSS filter is always active when GET, POST or
| COOKIE data is encountered
|
*/
$config['global_xss_filtering'] = FALSE;

/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
|
| 'csrf_token_name' = The token name
| 'csrf_cookie_name' = The cookie name
| 'csrf_expire' = The number in seconds the token should expire.
*/
$config['csrf_protection'] = FALSE;
$config['csrf_token_name'] = 'csrf_test_name';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;

/*
|--------------------------------------------------------------------------
| Output Compression
|--------------------------------------------------------------------------
|
| Enables Gzip output compression for faster page loads.  When enabled,
| the output class will test whether your server supports Gzip.
| Even if it does, however, not all browsers support compression
| so enable only if you are reasonably sure your visitors can handle it.
|
| VERY IMPORTANT:  If you are getting a blank page when compression is enabled it
| means you are prematurely outputting something to your browser. It could
| even be a line of whitespace at the end of one of your scripts.  For
| compression to work, nothing can be sent before the output buffer is called
| by the output class.  Do not 'echo' any values with compression enabled.
|
*/
$config['compress_output'] = FALSE;

/*
|--------------------------------------------------------------------------
| Master Time Reference
|--------------------------------------------------------------------------
|
| Options are 'local' or 'gmt'.  This pref tells the system whether to use
| your server's local time as the master 'now' reference, or convert it to
| GMT.  See the 'date helper' page of the user guide for information
| regarding date handling.
|
*/
$config['time_reference'] = 'local';


/*
|--------------------------------------------------------------------------
| Rewrite PHP Short Tags
|--------------------------------------------------------------------------
|
| If your PHP installation does not have short tag support enabled CI
| can rewrite the tags on-the-fly, enabling you to utilize that syntax
| in your view files.  Options are TRUE or FALSE (boolean)
|
*/
$config['rewrite_short_tags'] = FALSE;


/*
|--------------------------------------------------------------------------
| Reverse Proxy IPs
|--------------------------------------------------------------------------
|
| If your server is behind a reverse proxy, you must whitelist the proxy IP
| addresses from which CodeIgniter should trust the HTTP_X_FORWARDED_FOR
| header in order to properly identify the visitor's IP address.
| Comma-delimited, e.g. '10.0.1.200,10.0.1.201'
|
*/
$config['proxy_ips'] = '';
$config['http_path_upload'] = '/srv/www/htdocs/zenfile/';
if ($_SERVER['HTTP_HOST'] == 'zen' || $_SERVER['HTTP_HOST'] == 'lastzenfile') {
    $config['path_upload'] = '../';
} else {
    $config['path_upload'] = '/home/zenfile/public_html/';
}

if (isset($_SERVER["HTTP_HOST"]) && ($_SERVER["HTTP_HOST"] == "dev.zenfile.com" || $_SERVER["HTTP_HOST"] == "parkipfiling.com" || $_SERVER["HTTP_HOST"] == "www.parkipfiling.com") )
{
    $config['http_path_upload'] = '/srv/www/htdocs/devzen/';
    $config['path_upload'] = '/home/devzen/public_html/';

   // $config['cpanel_ip_address'] = '108.167.175.178';
    $config['cpanel_ip_address'] = '127.0.0.1';
    $config['cpanel_api_hash'] = '78af47c56d4c042b74639d18feb535ba5aafb0f29c9e6a3c29a24da47b6992dc2a6696e318533a40219876c9f50d3c045331464a7cea62f56050081ec027efa5806bd33695205234b59ee7d63addf8751a355710f6c3ed1f1c3cc4849034d939f1f9b6159248630572ce7ad23769fbc1146b0f7eb632b1bad3437fc9197c6d74010c1cfb163d4d644b0128a7f874aaefe37eba660c7832e3a2000302f618bda723a42240df0f41bbe8147ff897db7d64ee38090c08d27c4f727c69f56f2b39477d76af73ca25997f44d56ad585db059215422962bd30f77b7083cfc4c31c7e9d4c5cafa3bdf7082f40f147c9734e1a818d5525345fcdc817a223bb1d965945491bfbffa47c62792b73504a1cb97f887a80b700cd24ba602f9a6ed0dcee5f1b26273eebf75af1263df0ecc686e3b476f804ccbe536a285144cc0f6f3d5dc595a65d1dd019fd3780f8e140b46e4ba4b8a3e8597aaf67a0da9cee6582723027116eb62ede5ca1418e70bf667f66c50b4a3e833a416f8d37b7cc6c74656467c45ffde2f3373a28c3d3b5c1e0d9f36941bc7b25ad04e4060a8eebb031931b546f065b3946308c3b706a496891a15a6d0ba9a85c3725788663b84f4e09a3db6b32ff8c5d4c2bb954718be545f2ffb788c98644';
    $config['cpanel_username'] = 'devzen';
    $config['cpanel_password'] = 'Ptp3e31z#';
    $config['cpanel_skin'] = 'x3';
    $config['cpanel_domain'] = 'dev.zenfile.com';
    $config['cpanel_tracker_domain'] = 'dev.zenfile.com';
    $config['cpanel_email_quota'] = '0';
    //$config['zenfile_bcc_email'] = 'brian.daniel@zenfile.com';
    $config['zenfile_bcc_email'] = 'mr.dyan@gmail.com';
    //$config['zenfile_bcc_email'] = 'eduard.stavrest@gmail.com';
    $config['default_email_box'] = '@dev.zenfile.com';
}

/* End of file config.php */
/* Location: ./application/config/config.php */
