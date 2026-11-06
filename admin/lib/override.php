<?
$cpCfg = Zend_Registry::get('cpCfg');
$fn    = Zend_Registry::get('fn');
$modulesArr = Zend_Registry::get('modulesArr');
$tv = Zend_Registry::get('tv');

$dashboard = getCPModuleObj('common_dashboard')->model;
$dashboard2 = getCPModuleObj('tradingsg_dashboard')->model;

$themePath = CP_THEMES_PATH_LOCAL_ALIAS . $cpCfg['cp.theme'] . '/';
//$modulesArr['hms_company']['title'] = 'MFR COMPANY';

$arr = array();

if ($tv['module'] == 'tradingsg_dashboard'){
    $arr[] = $dashboard2->getDasboardObj('hms_pharmacyDailySales', array('cssClass' => 'c100l'));
    $arr[] = $dashboard2->getDasboardObj('hms_supplierOutstanding', array('cssClass' => 'c100l'));
} else {
    $arr[] = $dashboard->getDasboardObj('hms_patientVisitSummary');
	$arr[] = $dashboard->getDasboardObj('hms_attendanceReportDashboard');
	$arr[] = $dashboard->getDasboardObj('hms_labReport');
	$arr[] = $dashboard->getDasboardObj('hms_supplierOutstanding');
	$arr[] = $dashboard->getDasboardObj('hms_patientVisitLocationwiseChart');
}

/*$arr[] = $dashboard->getDasboardObj('hms_patientVisitSummary');
//$arr[] = $dashboard->getDasboardObj('hms_dailyCollectionReport');
$arr[] = $dashboard->getDasboardObj('hms_attendanceReportDashboard');
$arr[] = $dashboard->getDasboardObj('hms_labReport');
$arr[] = $dashboard->getDasboardObj('hms_supplierOutstanding');
$arr[] = $dashboard->getDasboardObj('hms_patientVisitLocationwiseChart');
//$arr[] = $dashboard->getDasboardObj('hms_diseaseSummaryChart');
//$arr[] = $dashboard->getDasboardObj('hms_revenueByMonthChart');
//$arr[] = $dashboard->getDasboardObj('hms_revenueByDayChart');
//$arr[] = $dashboard->getDasboardObj('hms_patientVisitChart');
//$arr[] = $dashboard->getDasboardObj('hms_labChartSummary');*/



$cpCfg['cp.dashboardArr'] = $arr;

$cssFilesArr = array();
$cssFilesArr[] = 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,200,200italic,300,300italic,400italic,600,600italic,700italic,700,900,900italic';
$cssFilesArr[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css';
$cssFilesArr[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css';
$jsFilesArr = array();
$jsFilesArr[] = $themePath.'js/bootstrap-modal.js';
$jssKeys = array('fontAwesome-4.3.0');

$cpCfg['cp.dashboardArr'] = $arr;

$tv = Zend_Registry::get('tv');
array_push($tv['protSiteSpActionExceptions'], 'createAttendanceForAbsent');
array_push($tv['protSiteSpActionExceptions'], 'createCurrentDayPharmacyRecord');
array_push($tv['protSiteSpActionExceptions'], 'generateGstBillNo');

CP_Common_Lib_Registry::arrayMerge('tv', $tv);
CP_Common_Lib_Registry::arrayMerge('jsFilesArr', $jsFilesArr);
CP_Common_Lib_Registry::arrayMerge('jssKeys', $jssKeys);
CP_Common_Lib_Registry::arrayMerge('cssFilesArr', $cssFilesArr);
CP_Common_Lib_Registry::arrayMerge('cpCfg', $cpCfg);
CP_Common_Lib_Registry::arrayMerge('modulesArr', $modulesArr);
