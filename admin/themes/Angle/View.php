<?
class CPL_Admin_Themes_Angle_View extends CP_Admin_Themes_Angle_View
{
    var $jssKeys = array('blend-2.2', 'jscrollpane-2.0', 'noty-2.0.3');

    /**
     *
     */
    function getHeaderPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module_name = $tv['module'];
        $module_title = $modulesArr[$module_name]['title'];

        $mainNav = includeCPClass('Lib', 'Room', 'Room');
        Zend_Registry::set('mainNav', $mainNav);

        $SERVER = $_SERVER['HTTP_HOST'];

        $logoText = '';

        if (isLoggedInAdmin()) {
            $logoText = "<h1 class='siteTitle'>{$cpCfg['cp.siteTitle']}</h1>";

            if ($cpCfg['cp.hasAdminOnly'] == false) {
                $logoText = "<a href='/' target='_blank'>{$logoText}</a>";
            }

            if($module_title == 'Home'){
                $logoText = $logoText;
            }else{
                if($tv['action'] != 'list') {
                    $logoText = "<h1 class='siteTitle'>".$modulesArr[$module_name]['title'] ." ". "Details</h1>";
                }else{
                    $logoText = "<h1 class='siteTitle'>{$module_title}</h1>";
                }

            }

            $logoText = "
            <div class='logo float_left'>
                {$logoText}
            </div>
            ";

        } else {
            //$logoText = "<img src='images/HMS logo.png' width='50' height='50'/>";
        }

        $rightText = '';
        $cpMultiYearText = '';
        $cpAdminInterfaceLangText = '';
        $cpMultiUniqueSiteText = '';

        //multi country widget
        if (isLoggedInAdmin() && $cpCfg['cp.multiCountry']) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiCountry.ignoreModules'])) {
                $wMultiCountry = getCPWidgetObj('common_multiCountry');

                $cpMultiYearText = "
                <div class='float_left'>
                    <div class='multi-country'>{$wMultiCountry->getWidget()}</div>
                </div>
                ";
            }
        }

        //admin interface langs
        if (isLoggedInAdmin() && $cpCfg['cp.hasAdminInterfaceLangs']) {
            $wAdminTranslation = getCPWidgetObj('common_adminTranslation');

            $cpAdminInterfaceLangText = "
            <div class='float_left'>
                <div class='admin-langs'>{$wAdminTranslation->getWidget()}</div>
            </div>
            ";
        }

        if (isLoggedInAdmin() && $cpCfg['cp.hasMultiYears']) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiYear.ignoreModules'])) {
                $wMultiYear = getCPWidgetObj('common_multiYear');

                $cpMultiYearText = "
                <div class='float_left'>
                    <div class='multi-year'>{$wMultiYear->getWidget()}</div>
                </div>
                ";
            }
        }

        $userGroupType = $fn->getSessionParam('userGroupType');

        //&& $userGroupType == 'Super Administrator'

        if (isLoggedInAdmin() && $cpCfg['cp.hasMultiUniqueSites'] && $_SESSION['isDeveloper'] == 1) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
                $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite');

                $cpMultiUniqueSiteText = "
                <div class='float_right'>
                    <div class='multi-unique-site'>{$wMultiUniqueSite->getWidget()}</div>
                </div>
                ";
            }
        }

        $logged_IN_text_Logout = '';
        $homeMenuDisplay   = '';
        $leftMenuShowHide  = '';
        $helpAndGetStarted = '';
        $siteName          = '';
        $TimeoutButton     = '';
        $TimeinButton      = '';
        $TimeinButton2     = '';
        $TimeoutButton2    = '';
        $TimeoutButton3    = '';
        $TimeinButton3     = '';
        $TimeoutButton4    = '';
        $TimeinButton4     = '';
        $dayShiftButtons   = '';
        $searchmedicines   = '';
        if (isLoggedInAdmin()) {

            $leftMenuShowHide = "<a class='leftnavShowHide leftnavShowHideicon'></a>";

            $homeMenuDisplay = "<div>
                                    {$this->getHomeMenuDisplay()}
                                </div>";

            /*$helpAndGetStarted = "
            <div class='float_right helpAndGetStarted'>
                <div class='getStarted float_left'>
                    <a class='getStartedContentTask button btn btn-default' href='#'>Get Started</a>
                </div>

            </div>
            ";*/

            $viewsearchMedicinesLink = "index.php?_theme=angle&_spAction=searchMedicines&showHTML=0";
            $searchmedicines = "
            <div class='float_right'>
                <div class='float_left'>
                    <a href='{$viewsearchMedicinesLink}' class='btn btn-primary viewsearchMedicines' id='viewsearchMedicines'>Search Medicines</a>
                </div>

            </div>
            ";


            $SQLEmployee = "
            SELECT employee_type
            FROM employee
            WHERE staff_id = {$_SESSION['staff_id']}
            ";
            $resultEmployee = $db->sql_query($SQLEmployee);
            $rowEmployee    = $db->sql_fetchrow($resultEmployee);

            $today = date("Y-m-d");

            $SQLAttend = "
            SELECT *
            FROM attendance
            WHERE record_date = '{$today}'
            AND staff_id = {$_SESSION['staff_id']}";
            $resultAttend = $db->sql_query($SQLAttend);
            $rowAttend = $db->sql_fetchrow($resultAttend);

            if($rowAttend['time_in_day_shift'] == ""){
                $TimeinButton = "
                <div class='float_left TimeinButtonHeader'>
                    <input class='btn btn-success TimeinHeaderButton' staff_id={$_SESSION['staff_id']} value='TIME IN DAY SHIFT'/>
                </div>
                ";

                $TimeoutButtonHide = "displayNone";
            } else {
                $TimeinButton = "
                <div class='float_left TimeinButtonHeader'>
                    <input class='btn btn-success TimeinHeaderButton' staff_id={$_SESSION['staff_id']} value='TIME IN DAY SHIFT' disabled/>
                </div>
                ";

                $TimeoutButtonHide = "";               
            }

            $TimeoutButton = "
            <div class='float_right TimeoutButtonHeader {$TimeoutButtonHide}'>
                <a class='btn btn-danger TimeoutHeaderButton' href='#' staff_id={$_SESSION['staff_id']}>Time Out</a>
            </div>
            ";

            if($rowAttend['time_in_night_shift'] == ""){
                $TimeinButton2 = "
                <div class='float_left TimeinButton2Header'>
                    <input class='btn btn-primary TimeinHeaderButton2' staff_id={$_SESSION['staff_id']} value='TIME IN NIGHT SHIFT' />
                </div>
                ";

                $TimeoutButton2Hide = "displayNone";
            }else{
                $TimeinButton2 = "
                <div class='float_left TimeinButton2Header'>
                    <input class='btn btn-primary TimeinHeaderButton2' staff_id={$_SESSION['staff_id']} value='TIME IN NIGHT SHIFT' disabled/>
                </div>
                ";

                $TimeoutButton2Hide = ""; 
            }
$TimeinButton2 = '';
            $TimeoutButton2 = "
            <div class='float_right TimeoutButton2Header {$TimeoutButton2Hide}'>
                <a class='btn btn-primary TimeoutHeaderButton2' href='#' staff_id={$_SESSION['staff_id']}>Time Out</a>
            </div>
            ";


            $TimeoutButton3 = '';
            $TimeinButton3  = '';

            $TimeoutButton4 = '';
            $TimeinButton4  = '';

            $dayShiftButtons = "
            <div class='dayTimePanel floatbox'>
                {$TimeinButton}
                {$TimeoutButton}
            </div>
            ";
 $dayShiftButtons = '';
            if($rowEmployee['employee_type'] == "Double Shift"){
                $TimeinButton   = '';
                $TimeoutButton  = ''; 
                $dayShiftButtons = '';

                if($rowAttend['time_in_double_shift_morning'] == ""){
                    $TimeinButton3 = "
                    <div class='float_left TimeinButton3Header'>
                        <input class='btn btn-success TimeinHeaderButtonDay2' type='button' staff_id={$_SESSION['staff_id']} value='TI DS MORNING' />
                    </div>
                    ";

                    $TimeoutButton3Hide = "displayNone";
                }else{
                    $TimeinButton3 = "
                    <div class='float_left TimeinButton3Header'>
                        <input class='btn btn-success TimeinHeaderButtonDay2' type='button' staff_id={$_SESSION['staff_id']} value='TI DS MORNING' disabled/>
                    </div>
                    ";

                    $TimeoutButton3Hide = "";
                }

                $TimeoutButton3 = "
                <div class='float_right TimeoutButton3Header {$TimeoutButton3Hide}'>
                    <a class='btn btn-danger TimeoutHeaderButtonDay2' type='button' href='#' staff_id={$_SESSION['staff_id']}>Time Out</a>
                </div>
                ";

                if($rowAttend['time_in_double_shift_evening'] == ""){
                    $TimeinButton4 = "
                    <div class='float_left TimeinButton4Header'>
                        <input class='btn btn-primary TimeinHeaderButtonDSEvening' type='button' staff_id={$_SESSION['staff_id']} value='TI DS EVENING' />
                    </div>
                    ";

                    $TimeoutButton4Hide = "displayNone";
                }else{
                    $TimeinButton4 = "
                    <div class='float_left TimeinButton4Header'>
                        <input class='btn btn-primary TimeinHeaderButtonDSEvening' type='button' staff_id={$_SESSION['staff_id']} value='TI DS EVENING' disabled/>
                    </div>
                    ";

                    $TimeoutButton4Hide = "";
                }

                $TimeoutButton4 = "
                <div class='float_right TimeoutButton4Header {$TimeoutButton4Hide}'>
                    <a class='btn btn-primary TimeoutHeaderButtonDSEvening' type='button' href='#' staff_id={$_SESSION['staff_id']}>Time Out</a>
                </div>
                ";
            }

            /*<div class='helpContent float_right'>
                    <a class='helpContentTask button btn btn-default' module_name='{$module_title}' href='#'>Help</a>
                </div>*/

            if(!changePasswordOnLogin()){
                $logged_IN_text_Logout = "
                    <div class='float_right logoutWrap'>
                        <span class='username mr5'>{$ln->gd('cp.lbl.welcome', 'Welcome')} {$_SESSION['userFullName']}</span>
                        <div class='txtRight mr10 ul float_right'>
                            <a href='index.php?plugin=common_login&_spAction=logout' class='logout'>
                                {$ln->gd('cp.lbl.logout', 'Logout')}
                            </a>
                        </div>
                    </div>
                ";
            }

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

                $SQLSite = "
                SELECT title
                FROM site 
                WHERE site_id = {$cpSiteIdSession}
                ";
                $resultSite = $db->sql_query($SQLSite);
                $rowSite    = $db->sql_fetchrow($resultSite);

                $siteName = "
                <div class='float_right SiteNameOnHeader'>
                    - {$rowSite['title']}
                </div>
                ";
            }
        }

        $leftText = "
        <div class='topMostPanel'>
            <b>UTS : {$cpCfg['cp.companyName']}</b>
            {$logged_IN_text_Logout}
        </div>
        <div id='header-left'>
            <div class='floatbox'>
                {$searchmedicines}
                <div class='dayTimePanel floatbox timeInMargin'>
                    {$TimeinButton2}
                    {$TimeoutButton2}
                </div>

                <div class='dayTimePanel floatbox timeInMargin'>
                    {$TimeoutButton4}
                    {$TimeinButton4}
                </div>
                
                <div class='dayTimePanel floatbox timeInMargin'>
                    {$TimeoutButton3}
                    {$TimeinButton3}
                </div>

                {$dayShiftButtons}
                {$cpMultiUniqueSiteText}
                {$cpMultiYearText}
                {$cpAdminInterfaceLangText}
                {$leftMenuShowHide}
                {$homeMenuDisplay}
                <div class='float_left'>
                    {$logoText}{$siteName}
                </div>
            </div>
        </div>
        ";

        if (isLoggedInAdmin()) {
            $topRooms = '';
            if(!changePasswordOnLogin()){
                $topRooms = "
                <div class='float_right'>
                    <div class='hlist noBg'>
                        {$mainNav->getTopRooms()}
                    </div>
                </div>
                ";
            }
            $rightText = "
            <div id='topnav'>
            {$topRooms}
            </div>
            ";
        }

        $actions = '';
        if ($cpCfg['cp.showActionPanelInHeader']) {
            $action = Zend_Registry::get('action');

            if ($tv['action'] != 'new') {
                $actions = "
                <div class='hlist actionBtns noBg'>
                    {$action->getActionButtons()}
                </div>
                ";
            }
        }

        $getStartedPopupOnloadSession = $fn->getSessionParam('getStartedPopupOnloadSession');
        $getStartedPopuphidden = "<input type='hidden' id='getStartedPopupOnloadSession' value={$getStartedPopupOnloadSession}>";

        $getLocationPopupOnloadhidden = '';
        /*$locationSetPopup = '';
        if (isLoggedInAdmin()) {
            $getLocationPopupOnloadSession = $fn->getSessionParam('getLocationPopupOnloadSession');
            $getLocationPopupOnloadhidden  = "<input type='hidden' id='getLocationPopupOnloadSession' value={$getLocationPopupOnloadSession}>";

            $cp_site_id = $fn->getSessionParam('cp_site_id');
        
            $SQL = "
            SELECT site_id
                 , title
            FROM site
            ";
            $arr = $dbUtil->getArrayFromSQLForVL($SQL);

            $locationSetPopup = "
            <div id='locationChoosemodal' class='modal fade' data-backdrop='static'>
              <div class='modal-dialog'>
                <div class='modal-content'>
                  <div class='modal-header'>
                    <h4 class='modal-title'>Please Choose the Location below (Location is the clinic where you work)</h4>
                  </div>
                  <div class='modal-body'>
                    <div class='onLoadLocationSelectDropdown'>
                        <label>{$cpCfg['cp.chooseSiteLbl']}</label>
                        <select name='chooseLocationByUserDropdown'>
                            {$cpUtil->getDropDownFromArr($arr, $cp_site_id)}
                        </select>
                    </div>
                  </div>
                  <div class='modal-footer'>
                    <button type='button' class='btn btn-primary chooseLocationByUserSubmit' data-dismiss='modal'>Set Location</button>
                  </div>
                </div>
              </div>
            </div>
            ";
        }*/

        //{$locationSetPopup}


        $text = "
        {$getStartedPopuphidden}
        {$leftText}
        {$actions}
        {$getLocationPopupOnloadhidden}
        ";

        return $text;
    }

    /**
     *
     */
    function getMainThemeOutput() {
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $viewHelper = Zend_Registry::get('viewHelper');

        $headerPanel    = $this->getHeaderPanel();
        $navPanel       = $this->getNavPanel();
        $leftPanel      = $this->getLeftPanel();
        $moduleName     = $tv['module'];
        $moduleTitle = $modulesArr[$moduleName]['title'];
        if($tv['action'] != 'list') {
            $moduleTitle = $modulesArr[$moduleName]['title'] .' '. 'Details';
        }

        $rightPanel = '';
        if ($tv['action'] == 'list') {
            $rightPanel = $this->getListRightPanel();
        }
        $bodyPanel      = $this->getBodyPanel();
        $navPanel2      = $this->getNavPanel2(); //note: this line has to be below the $bodyPanel
        $extendedPanel  = $this->getExtendedPanel();
        $footerPanel    = $this->getFooterPanel();
        $pageCSSClass   = $viewHelper->getPageCSSClass();
        $pagerPanel = '';
        if($cpCfg['cp.showPagerPanelInFooter']){
            $pagerPanel = "
            <div class='float_left pagelinksBottom'>
                {$this->getPagerPanel()}
            </div>
            ";
        }
        $leftCol = '';
        if($tv['module'] != 'hms_home'){
            $leftCol = "
            <aside id='col1' class='displayNone'>
                <div id='col1_content' class='clearfix'>
                    {$leftPanel}
                </div>
            </aside>
            ";
        }

        $patient_queue_display = '';
        if($tv['module'] == 'hms_patientVisit'){
            $patient_queue_display = "
            <div class = 'float_right'>{$this->getPatientQueueNo()}</div>
            ";
        }

        $paymentReminder = '';
        $paymentReminderPopup = '';
        $paymentReminderMessage = isset($cpCfg['paymentReminderMessage'])
            ? trim((string) $cpCfg['paymentReminderMessage'])
            : '';
        if (isset($cpCfg['paymentReminder']) && (int) $cpCfg['paymentReminder'] === 1
            && $paymentReminderMessage !== '') {
            $paymentReminderMessage = htmlspecialchars($paymentReminderMessage, ENT_QUOTES, 'UTF-8');
            $paymentReminder ="
            <div><marquee><strong>{$paymentReminderMessage} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {$paymentReminderMessage} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {$paymentReminderMessage} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </strong></marquee></div>
            ";
            $paymentReminderPopup = "
            <div id='paymentReminderModal' class='modal fade' tabindex='-1' role='dialog' aria-labelledby='paymentReminderModalTitle' aria-hidden='true'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='paymentReminderModalTitle'>Payment Reminder</h4>
                        </div>
                        <div class='modal-body'>
                            <div class='paymentReminderTextScroll'><marquee><strong>{$paymentReminderMessage}</strong></marquee></div>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                        </div>
                    </div>
                </div>
            </div>
            ";
        }

        $mainInner = "
        <div class='mainInner'>
            {$paymentReminder}
            {$paymentReminderPopup}
            {$leftCol}
            <aside id='col2'>
                <div id='col2_content' class='clearfix'>
                    {$rightPanel}
                </div>
            </aside>

            <div id='col3' class='fullleftlist'>
                <div id='col3_content' class='clearfix contentScroller'>
                <div class='moduleTitle'>
                    <div class = 'float_left moduleName'>{$moduleTitle}</div>
                    {$patient_queue_display}
                </div>
                <nav id='nav2'>
                    <div id='goTop'></div>
                    <div class=''>
                        <div class='page'>
                            {$navPanel2}
                        </div>
                    </div>
                </nav>
                    {$bodyPanel}
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <div id='main' class='{$pageCSSClass} clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
            </div>
            <div id='extended'>
                <div class='page_margins'>
                    <div class='page'>
                        {$extendedPanel}
                    </div>
                </div>
            </div>
            <a href='#goTop' class='scrollToTop'>
                <i class='fa fa-arrow-up'></i>
            </a>
            <footer id='footer'>
                <div class='page_margins'>
                    <div class='page'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            ";

        } else {

            if ($navPanel != ''){
                $navPanel = "
                <nav id='nav'>
                    {$navPanel}
                </nav>
                ";
            }

            if ($navPanel2 != ''){
                $navPanel2 = "
                <nav id='nav2'>
                    {$navPanel2}
                </nav>
                ";
            }

            $text = "
            <div class='page_margins'>
                <div class='page'>
                    <header id='header'>
                        {$headerPanel}
                    </header>
                    {$navPanel}
                    {$navPanel2}

                    <div id='main' class='{$pageCSSClass} clearfix'>
                        {$mainInner}
                    </div>
                    <a href='#' class='scrollToTop'>
                        <i class='fa fa-arrow-up'></i>
                    </a>
                    {$pagerPanel}
                    <footer id='footer'>
                        {$footerPanel}
                    </footer>
                </div>
            </div>
            ";
        }

        $logged_in = $fn->getReqParam('logged_in');
        $random_id = $fn->getReqParam('random_id');
        if ($logged_in == 1 && $random_id != "" && $cpCfg['cp.autoLoginToIntranet'] == 1){
            $autoLoginUrl = $cpCfg['intranetUrl'] . "index.php?_spAction=autoLoginUserByRandomID&showHTML=0&random_id={$random_id}";
            $text .= "<iframe id='utilFrame' name='utilFrame' class='utilFrame' src='{$autoLoginUrl}'></iframe>";
        }

        return $text;
    }

    /**
     *
     */
    function getPatientQueueNo() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $currentDate  = date("Y-m-d");

        $SQL ="
        SELECT MIN(pq.queue_no) AS queue_no
              ,pv.employee_id
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS Doctor_Name
              ,pv.patient_visit_id
        FROM `patient_queue` pq
        LEFT JOIN patient_visit pv ON (pv.patient_information_id = pq.patient_information_id)
        LEFT JOIN employee_visit ev ON (ev.patient_visit_id = pv.patient_visit_id)
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date = '{$currentDate}'
        AND pv.status NOT IN ('Visited', 'Cancelled', 'Closed')
        GROUP BY pv.employee_id
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $text = '';
        $queueDisplay = "<div class='queueNumberDisplay'>
                            {$text}
                         </div>
                         ";

        if($numRows > 0){
            while ($row = $db->sql_fetchrow($result)) {
                $doctorCode = substr($row['Doctor_Name'], 0, 2);
                $SQLQueue ="
                SELECT pq.patient_visit_id
                FROM patient_queue pq
                LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
                WHERE pv.employee_id = {$row['employee_id']}
                AND pv.check_up_date = '{$currentDate}'
                AND pv.status NOT IN ('Visited', 'Cancelled')
                AND pv.patient_visit_id != {$row['patient_visit_id']}
                ";
                $resultQueue  = $db->sql_query($SQLQueue);
                $numRowsQueue = $db->sql_numrows($resultQueue);

                $nextQueueNoLink = '';
                if($numRowsQueue >= 1){
                    $nextQueueNoLink = "<a class='nextQueueNo' queue_no={$row['queue_no']} employee_id={$row['employee_id']} >Next</a>";
                }

                $patientVisitLink = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";
                $createVisit = "<a class = 'viewVisitRecord' href='{$patientVisitLink}'>
                                    {$row['Patient_Name']}
                                </a>
                ";

                $text .= "
                <div class = 'queueNoTable divtoBlink'>
                    <div class='float_left'>{$doctorCode}: QUE{$row['queue_no']}</div>
                    <div class='float_right'>Waiting: {$numRowsQueue}</div><br/>
                    <div class='float_left'>Patient Name: <u>{$createVisit}</u></div>
                    <div class='float_right'>{$nextQueueNoLink}</div>
                </div>
                ";
            }

            $queueDisplay = "
                <div class='queueNumberDisplay'>
                    {$text}
                </div>
            ";
        }

        return $queueDisplay;
    }

    /**
     *
     */
    function getUpdateQueueNoNext(){
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $queue_no    = $fn->getReqParam('queue_no');
        $employee_id = $fn->getReqParam('employee_id');
        $currentDate  = date("Y-m-d");

        $SQLQueue = "
        SELECT pq.patient_visit_id
        FROM patient_queue pq
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
        WHERE pq.queue_no = {$queue_no}
        AND pv.employee_id = {$employee_id}
        AND pv.check_up_date = '{$currentDate}'
        ";
        $resultQueue  = $db->sql_query($SQLQueue);
        $rowQueue     = $db->sql_fetchrow($resultQueue);

        $updatePatientVisitSQL = "
        UPDATE patient_visit SET status = 'Visited'
        WHERE patient_visit_id = {$rowQueue['patient_visit_id']}
        ";
        $resultPatientVisitSQL = $db->sql_query($updatePatientVisitSQL);

        $fa = array();
        $whereCondition = "WHERE patient_visit_id = {$rowQueue['patient_visit_id']}";
        $updatePatientVisitSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($updatePatientVisitSQL);
    }

    /**
     *
     */
    function getLoginThemeOutput() {
        $login = getCPPluginObj('common_login');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $headerPanel = $this->getHeaderPanel();
        $footerPanel = $this->getFooterPanel();

        $loginTitle ="
        <div class='floatbox'>
            <div class='float_left loginTitle'><h1>Welcome to Taj Poly Clinical Lab Management System</h1></div>
        </div>
        ";

        $paymentReminder = '';
        $paymentReminderMessage = isset($cpCfg['paymentReminderMessage'])
            ? trim((string) $cpCfg['paymentReminderMessage'])
            : '';
        if (isset($cpCfg['paymentReminder']) && (int) $cpCfg['paymentReminder'] === 1
            && $paymentReminderMessage !== '') {
            $paymentReminderMessage = htmlspecialchars($paymentReminderMessage, ENT_QUOTES, 'UTF-8');
            $paymentReminder ="
            <div><marquee><strong>{$paymentReminderMessage} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {$paymentReminderMessage} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            {$paymentReminderMessage} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </strong></marquee></div>
            ";
        }

        $mainInner = "
        <div class='mainInner'>
            {$paymentReminder}
            <div id='col3'>
                <div id='col3_content' class='clearfix'>
                    {$loginTitle}
                    {$login->getLoginForm()}
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <div class='tplLogin'>
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <div id='main' class='clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
            </div>
            <footer id='footer'>
                <div class='page_margins'>
                    <div class='page'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            </div>
            ";

        } else {
            $text = "
            <div class='page_margins tplLogin'>
                <div class='page'>
                    <header id='header'>
                        {$headerPanel}
                    </header>
                    <div id='main' class='clearfix floatbox'>
                        {$mainInner}
                    </div>
                    <footer id='footer'>
                        {$footerPanel}
                    </footer>
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getNavPanel2(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $searchHTML = Zend_Registry::get('searchHTML');
        $action = Zend_Registry::get('action');
        $pager = Zend_Registry::get('pager');

        $langBtns = '';
        $searchText = '';
        if ($tv['action'] == 'list') {
            $searchText = $searchHTML->getSearchHTML($tv['module']);
        }

        if (($tv['action'] == 'edit' || $tv['action'] == 'detail')
            && $modulesArr[$tv['module']]['hasMultiLang'] == 1
            && $cpCfg['cp.multiLang'] == 1
                ){
            $wLang = getCPWidgetObj('common_language');
            $langBtns = $wLang->getWidget();
        }

        $actions = '';
        if ($tv['action'] != 'new') {
            $actions = $action->getActionButtons();

            if($tv['action'] == 'edit' || $tv['action'] == 'detail'){
                $actions .=" 
                <div class='float_right backToList'>
                    {$pager->getBackButton()}
                </div>";
            }
        }

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$searchText}
                {$langBtns}
            </div>
            <div class='float_right'>
                <div class='hlist actionBtns'>
                    {$actions}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module = $modulesArr[$tv['module']];
        $scrollContent = $module['scrollContent'];

        $actionName = ucfirst($tv['action']);
        $actionTemp = "get{$actionName}";  //eg: getList
        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);

            $error = includeCPClass('Lib', 'Errors', 'Errors');
            $exp = array(
                'replaceArr' => array(
                    'clsName' => $clsName
                    , 'funcName' => $actionTemp
                )
            );
            print $error->getError('themeMethodNotFound', $exp);
            exit();
        }

        $text = $clsInst->$actionTemp();
        if ($scrollContent) {
            $text = "
            <div class='listTblWrapper'>
                {$text}
            </div>
            ";
        }

        return $text;
    }
    /**
     *
     */
    function getSearchMedicines() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $product = "
        <input type='text' value='' id='poProduct' class='text productTitle' name='product_title_search'>
        ";
        
        $text = "
        <table class='thinlist'>
            <tr>
                <td class='productSize'>{$product}</td>
            </tr>
        </table>
        ";


        $text .="
        <div id='productLink'>{$this->getSearchMedicinePortal()}</div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSearchMedicinePortal() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');



        $product_id  = $fn->getReqParam('product_id');
        $rows = '';
        $product_name = '';

        if ($product_id != '') {

            $SQLProduct = "
            SELECT p.*
            FROM product p
            WHERE p.product_id = {$product_id}
            ";
            $resultProduct = $db->sql_query($SQLProduct);
            $rowProduct    = $db->sql_fetchrow($resultProduct);
            $product_name = $rowProduct['title'];

            $SQLCom = "
            SELECT pop.qty
                  ,pop.free_items   
                  ,pop.cost_price
                  ,pop.selling_price
                  ,pop.product_id
                  ,pop.medicine_company_id
                  ,pop.purchase_order_id
                  ,p.title
                  ,c.company_name
                  ,s.company_name AS supplier_name
            FROM po_product pop
            LEFT JOIN `product` p ON (p.product_id = pop.product_id)
            LEFT JOIN `company` c ON (c.company_id = pop.medicine_company_id)
            LEFT JOIN `supplier` s ON (s.supplier_id = pop.supplier_id)
            WHERE p.product_id = {$product_id}
            ORDER BY p.title DESC
            ";
            $resultCom = $db->sql_query($SQLCom);
            $rows = '';
            $count = 1;
            while($rowCom = $db->sql_fetchrow($resultCom)){

                $rows .= "
                <tr>
                    <td>{$rowCom['supplier_name']}</td>
                    <td>{$rowCom['company_name']}</td>
                    <td>{$rowCom['qty']}</td>
                    <td>{$rowCom['free_items']}</td>
                    <td>{$rowCom['cost_price']}</td>
                    <td>{$rowCom['selling_price']}</td>
                </tr>
                ";
            }
        }

        $text = "
        <div>
            Search result for : {$product_name}
        </div>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Mfr Comp</th>
                    <th>Qty</th>
                    <th>Free</th>
                    <th>Rate</th>
                    <th>MRP</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }

}