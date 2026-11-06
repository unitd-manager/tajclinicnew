<?
$config['cp.TimeZone'] = 'Asia/Calcutta';
date_default_timezone_set($config['cp.TimeZone']);

define('CP_HOST', $_SERVER['HTTP_HOST']);
$docRoot = $_SERVER['DOCUMENT_ROOT'];

//================================================================//
if (CP_HOST == "tajclinic.unitdtechnologies.com" || CP_HOST == "tajclinic.unitdtechnologies.com") {
    define('CP_ENV', 'production');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');

} else if (CP_HOST == "demohms.cubosale.com") {
    define('CP_ENV', 'testing');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');

} else if (CP_HOST == "demohms.localhost") {
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $rootFolder = substr($docRoot, 0, stripos($docRoot, '/demohms/'));

    define('CP_ENV', 'local');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');
}
else if (CP_HOST == "tajclinic.localhost") {
    define('CP_ENV', 'local');
    define('CP_CORE_PATH', $docRoot . '/cmspilotv30/');

}

define('CP_PATH', CP_CORE_PATH . 'CP/');
define('CP_PATH2', CP_CORE_PATH . 'CP2/');
//================================================================//
require_once(CP_PATH . 'common/lib/inc_path.php');

/*** Local Server **/
$config['local'] = array(
     'db' => array(
          'host'     => 'localhost'
         ,'username' => 'root'
         ,'password' => ''
         ,'dbname'   => 'tajclinic'
     )
    ,'display_errors' => true
);

/*** Development Server **/
$config['development'] = $config['local'];
$config['development']['db']['username'] = 'demohms';
$config['development']['db']['password'] = 's1a2n3t4h5y6a';
$config['development']['display_errors'] = false;


/*** Testing Server **/
$config['testing'] = $config['development'];
$config['testing']['db']['dbname']   = 'demohms';
$config['testing']['db']['username'] = 'demohms';
$config['testing']['db']['password'] = 'm3U6T6GC4SK79K';
$config['testing']['display_errors'] = false;

/*** Production Server **/
$config['production'] = $config['testing'];
$config['production']['db']['dbname']   = 'tajclinic';
$config['production']['db']['username'] = 'tajclinic';
$config['production']['db']['password'] = 'b6974602e9393';
$config['production']['display_errors'] = false;

//================================================================//
require_once(CP_PATH . 'common/lib/Registry.php');
$cfgCommon = require_once(CP_PATH . 'common/lib/config.php');
$cfgCommon2 = require_once(CP_PATH2 . 'common/lib/config.php');
$cfgMast = require_once($cfgCommon['cp.masterPath'] . 'lib/config.php');
$cfgMast2   = require_once(CP_LIB_PATH2 . 'config.php');
$cfgLoc  = require_once($cfgCommon['cp.localPath'] . 'lib/config.php');

$cpCfg = array_merge($config, $cfgCommon, $cfgCommon2, $cfgMast, $cfgMast2, $cfgLoc);
Zend_Registry::set('cpCfg',$cpCfg);
//================================================================//
