<?
class CPL_Admin_Plugins_Common_Login_Model extends CP_Admin_Plugins_Common_Login_Model
{
    /**
     *
     */
    function getLoginSubmit() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');

        $email     = $fn->getPostParam('email'    , '', true);
        $pass_word = $fn->getPostParam('pass_word', '', true);
        $saveLogin = $fn->getPostParam('saveLogin', '', true);
        $loginBySmartCard = $fn->getPostParam('loginBySmartCard', '', true);

        //-------------------------------------------------------------------------------------//
        $valArr   = $this->getLoginSubmitValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError){
            header('Content-type: application/json');
            return $xmlText;
        }

        if ($loginBySmartCard){
            $smartCardId = $fn->getPostParam('smartCardId', '', true);
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE smart_card_id = '{$smartCardId}'
              AND published = 1
            ";
        } else {
            $SQL = "
            SELECT *
            FROM {$cpCfg['cp.modAccessStaffTable']}
            WHERE email = '{$email}'
              AND published = 1
            ";
        }
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            $userGroupId = $row['user_group_id'];

            //amended by syed for double login issue, not sure about the below condition
            //if($cpCfg['cp.hasMultiUsergroupPerStaff'] && $userGroupId != $cpCfg['cp.superAdminUGId']){
            if($cpCfg['cp.hasMultiUsergroupPerStaff']){
                $returnText = $this->view->getChooseUsergroupForm($row);
                return $validate->getSuccessMessageXML('', $returnText);
            } else {
                if($cpCfg['cp.captureAutoLogin']){

                    if ($row['developer'] != 1) {
                        /*$SQLStaff     = "
                        SELECT * FROM staff
                        WHERE developer = 0
                          AND published = 1
                          AND staff_id  = {$row['staff_id']}
                          ";
                        $resultStaff  = $db->sql_query($SQLStaff);
                        while ($rowStaff = $db->sql_fetchrow($resultStaff)){

                            /* Checking Previous attendance */
                            /*$SQLAtt = "
                            SELECT * FROM attendance
                            WHERE staff_id = {$row['staff_id']}
                            ";
                            $resultAtt  = $db->sql_query($SQLAtt);
                            $numRowsAtt = $db->sql_numrows($resultAtt);

                            if ($numRowsAtt > 0) {
                                $this->getMarkPreviousAttendanceRecords($rowStaff);
                            }
                        }*/

                        /*$today = date('Y-m-d');
                        $SQLStaffAtt = "
                        SELECT *
                        FROM attendance
                        WHERE record_date = '{$today}'
                          AND staff_id = {$row['staff_id']}
                        ";
                        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
                        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

                        if ($numRowsStaffAtt == 0) {

                            $SQLEmployee = "
                            SELECT employee_id
                            FROM employee
                            WHERE staff_id = {$row['staff_id']}
                            ";
                            $resultEmployee = $db->sql_query($SQLEmployee);
                            $rowEmployee    = $db->sql_fetchrow($resultEmployee);

                            $fa = array();

                            if ($cpCfg['cp.hasMultiUniqueSites']) {
                                $fa['site_id']      = $row['site_id'];
                            }

                            date_default_timezone_set("Asia/Calcutta");
                            $fa['time_in']          = date('H:i:s');
                            $fa['creation_date']    = date('Y-m-d H:i:s');
                            $fa['staff_id']         = $row['staff_id'];
                            $fa['employee_id']      = $rowEmployee['employee_id'];
                            $fa['record_date']      = $today;
                            $fa['created_by']       = $row['first_name'] . ' ' . $row['last_name'];

                            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
                            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
                            $hist_id                = $db->sql_nextid();
                        }*/

                        /*$SQLAtt2 = "
                        SELECT record_date 
                        FROM attendance
                        WHERE staff_id  = {$row['staff_id']}
                        AND record_date < '{$today}'
                        ";
                        $resultAtt2 = $db->sql_query($SQLAtt2);
                        $rowAtt2    = $db->sql_fetchrow($resultAtt2);

                        if($rowAtt2['record_date'] != ""){
                            $SQLUpdateTimeOut = "
                            UPDATE attendance SET leave_time = '18:00:00'
                            WHERE staff_id  = {$row['staff_id']}
                            AND record_date = '{$rowAtt2['record_date']}'
                            AND (leave_time = '' OR leave_time IS NULL) 
                            ";
                            $resultUpdateTimeOut  = $db->sql_query($SQLUpdateTimeOut);
                        }*/
                    }
                }

                $retUrl = $this->setSessionValuesAfterLogin($row, $saveLogin);
                /** if there is a hook for homepage in the theme level then use that **/
                $theme = getCPThemeObj($cpCfg['cp.theme']);
                if (method_exists($theme->fns, 'setSessionValuesAfterLogin')){
                    $theme->fns->setSessionValuesAfterLogin($row);
                }
                return $validate->getSuccessMessageXML($retUrl);
            }
        }
    }

    /**
     *
     */
    function getLogout() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if($cpCfg['cp.captureAutoLogin']){
            $today = date('Y-m-d');
            
            /*$SQLStaffAtt = "
            SELECT *
            FROM attendance
            WHERE record_date = '{$today}'
              AND staff_id = '{$_SESSION['staff_id']}'
            ";
            $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
            $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
            
            if ($numRowsStaffAtt > 0) {
                $fa = array();

                $staffRec = $fn->getRecordRowById('staff', 'staff_id', $_SESSION['staff_id']);
                date_default_timezone_set("Asia/Calcutta");
                $fa['leave_time']           = date('H:i:s');
                $fa['modification_date']    = date('Y-m-d H:i:s');
                $fa['modified_by']          = $_SESSION['userFullName'];

                $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$_SESSION['staff_id']}'";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, $cpCfg['cp.attendanceTable'], $whereCondition);
                $db->sql_query($SQL);
            }*/
        }

        session_destroy();
        $fn->resetCookie("adminUserNameC");
        $fn->resetCookie("adminPasswordC");
        $fn->sessionRegenerate();
        
        // added the below 2 lines by ahmad due to bug with resetCookie function
        setcookie('adminUserNameC', '',  time()-1209600);
        setcookie('adminPasswordC', '',  time()-1209600);

        $cpUtil->redirect('index.php');
    }    

    /**
     *
     */
    function getMarkPreviousAttendanceRecords($row) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        /* Finding last attendance record */
        $sqlPrevAtt = "
        SELECT MAX(record_date) AS last_attendance_date FROM attendance
        WHERE staff_id = {$row['staff_id']}
        ";
        $resultPrevAtt  = $db->sql_query($sqlPrevAtt);        
        $rowPrevAtt     = $db->sql_fetchrow($resultPrevAtt);
        
        $date = new DateTime($rowPrevAtt['last_attendance_date']);
        $date->modify('+1 day');
        $begin = $date->format('Y-m-d');
        $end   = date("Y-m-d",mktime (0,0,0,date("m"),date("d"), date("Y")));
        
        $begin = new DateTime($begin);
        $end   = new DateTime($end);

        $interval = array();
        //Create array with all dates within date span
    	while($begin < $end) {
    		$interval[] = $begin->format('Y-m-d');
    		$begin->modify('+1 day');
    	}
	
	    #$interval = new DateInterval('P1D');
        #$daterange = new DatePeriod($begin, $interval ,$end);
        
        foreach($interval as $date){

            #$record_date = $date->format("Y-m-d");
            $timestamp   = strtotime($date);
            $record_day  = date("D", $timestamp);
            
            // Inserting attendance record excluding Sunday
            //if ($record_day != 'Sun') {    
                $fa = array();
                $fa['staff_id']         = $row['staff_id'];
                $fa['record_date']      = $date;
                $fa['on_leave']         = 1;
                $fa['creation_date']    = date('Y-m-d H:i:s');
                $fa['created_by']       = $row['first_name'] . ' ' . $row['last_name'];
    
                $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
                $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
                $hist_id                = $db->sql_nextid();
            //}
        }
    }

    /**
     *
     */
    function getStaffTimeOutUpdate() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');

        $today = date("Y-m-d");
            
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE record_date = '{$today}'
          AND staff_id    = '{$staff_id}'
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
        
        if ($numRowsStaffAtt > 0) {
            $fa = array();
            
            $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);
            date_default_timezone_set("Asia/Calcutta");
            $fa['leave_time_day_shift'] = date('H:i:s');
            $fa['modification_date']    = date('Y-m-d H:i:s');
            $fa['modified_by']          = $staffRec['first_name'] . ' ' . $staffRec['last_name'];

            $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$staff_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);
        }

    }

    /**
     *
     */
    function getStaffTimeInUpdate() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');
                    
        $SQLStaff     = "
        SELECT * FROM staff
        WHERE developer = 0
          AND published = 1
          AND staff_id  = {$staff_id}
          ";
        $resultStaff  = $db->sql_query($SQLStaff);
        while ($rowStaff = $db->sql_fetchrow($resultStaff)){

            /* Checking Previous attendance */
            $SQLAtt = "
            SELECT * FROM attendance
            WHERE staff_id = {$staff_id}
            ";
            $resultAtt  = $db->sql_query($SQLAtt);
            $numRowsAtt = $db->sql_numrows($resultAtt);

            if ($numRowsAtt > 0) {
                //$this->getMarkPreviousAttendanceRecords($rowStaff);
            }
        }

        $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);

        $today = date('Y-m-d');
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE record_date = '{$today}'
          AND staff_id = {$staff_id}
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

        if ($numRowsStaffAtt == 0) {

            $SQLEmployee = "
            SELECT employee_id
            FROM employee
            WHERE staff_id = {$staff_id}
            AND status = 'Active'
            ";
            $resultEmployee = $db->sql_query($SQLEmployee);
            $rowEmployee    = $db->sql_fetchrow($resultEmployee);

            $fa = array();

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id']      = $staffRec['site_id'];
            }

            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_day_shift'] = date('H:i:s');
            $fa['creation_date']     = date('Y-m-d H:i:s');
            $fa['staff_id']          = $staff_id;
            $fa['employee_id']       = $rowEmployee['employee_id'];
            $fa['record_date']       = $today;
            $fa['created_by']        = $staffRec['first_name'] . ' ' . $staffRec['last_name'];
            $fa['shift']             = 'Day';

            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
            $hist_id                = $db->sql_nextid();
        }
    }

    /**
     *
     */
    function getStaffTimeOutUpdateNight() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');

        $today = date("Y-m-d");
        $yesterday     = date("Y-m-d", strtotime("yesterday"));
            
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE staff_id    = '{$staff_id}'
          AND time_in_night_shift != ''
        ORDER BY attendance_id DESC
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
        
        if ($numRowsStaffAtt > 0) {
            $fa = array();
            
            $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);
            date_default_timezone_set("Asia/Calcutta");
            $fa['leave_time_night_shift'] = date('H:i:s');
            $fa['modification_date']      = date('Y-m-d H:i:s');
            $fa['modified_by']            = $staffRec['first_name'] . ' ' . $staffRec['last_name'];

            $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$staff_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);
        }

    }

    /**
     *
     */
    function getStaffTimeInUpdateNight() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');
                    
        $SQLStaff     = "
        SELECT * FROM staff
        WHERE developer = 0
          AND published = 1
          AND staff_id  = {$staff_id}
          ";
        $resultStaff  = $db->sql_query($SQLStaff);
        while ($rowStaff = $db->sql_fetchrow($resultStaff)){

            /* Checking Previous attendance */
            $SQLAtt = "
            SELECT * FROM attendance
            WHERE staff_id = {$staff_id}
            ";
            $resultAtt  = $db->sql_query($SQLAtt);
            $numRowsAtt = $db->sql_numrows($resultAtt);

            if ($numRowsAtt > 0) {
                //$this->getMarkPreviousAttendanceRecords($rowStaff);
            }
        }

        $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);

        $today = date('Y-m-d');
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE record_date = '{$today}'
          AND staff_id = {$staff_id}
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

        if ($numRowsStaffAtt == 0) {

            $SQLEmployee = "
            SELECT employee_id
            FROM employee
            WHERE staff_id = {$staff_id}
            AND status = 'Active'
            ";
            $resultEmployee = $db->sql_query($SQLEmployee);
            $rowEmployee    = $db->sql_fetchrow($resultEmployee);

            $fa = array();

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id']      = $staffRec['site_id'];
            }

            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_night_shift'] = date('H:i:s');
            $fa['creation_date']       = date('Y-m-d H:i:s');
            $fa['staff_id']            = $staff_id;
            $fa['employee_id']         = $rowEmployee['employee_id'];
            $fa['record_date']         = $today;
            $fa['created_by']          = $staffRec['first_name'] . ' ' . $staffRec['last_name'];
            $fa['shift']               = 'Night';

            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
            $hist_id                = $db->sql_nextid();
        } else {
            $fa = array();
            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_night_shift'] = date('H:i:s');
            $fa['shift']               = 'Both';

            $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$staff_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);            
        }
    }

    /**
     *
     */
    function getStaffTimeInUpdateDay() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');
                    
        $SQLStaff     = "
        SELECT * FROM staff
        WHERE developer = 0
          AND published = 1
          AND staff_id  = {$staff_id}
          ";
        $resultStaff  = $db->sql_query($SQLStaff);
        while ($rowStaff = $db->sql_fetchrow($resultStaff)){

            /* Checking Previous attendance */
            $SQLAtt = "
            SELECT * FROM attendance
            WHERE staff_id = {$staff_id}
            ";
            $resultAtt  = $db->sql_query($SQLAtt);
            $numRowsAtt = $db->sql_numrows($resultAtt);

            if ($numRowsAtt > 0) {
                //$this->getMarkPreviousAttendanceRecords($rowStaff);
            }
        }

        $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);

        $today = date('Y-m-d');
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE record_date = '{$today}'
          AND staff_id = {$staff_id}
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

        if ($numRowsStaffAtt == 0) {

            $SQLEmployee = "
            SELECT employee_id
            FROM employee
            WHERE staff_id = {$staff_id}
            AND status = 'Active'
            ";
            $resultEmployee = $db->sql_query($SQLEmployee);
            $rowEmployee    = $db->sql_fetchrow($resultEmployee);

            $fa = array();

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id']      = $staffRec['site_id'];
            }

            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_double_shift_morning']   = date('H:i:s');
            $fa['creation_date']    = date('Y-m-d H:i:s');
            $fa['staff_id']         = $staff_id;
            $fa['employee_id']      = $rowEmployee['employee_id'];
            $fa['record_date']      = $today;
            $fa['created_by']       = $staffRec['first_name'] . ' ' . $staffRec['last_name'];
            $fa['shift']            = 'Double Shift Morning';

            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
            $hist_id                = $db->sql_nextid();
        } else {
            $fa = array();
            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_double_shift_morning'] = date('H:i:s');
            $fa['shift']         = 'Double Shift';

            $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$staff_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);            
        }
    }

    /**
     *
     */
    function getStaffTimeOutUpdateDay() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');

        $today     = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime("yesterday"));
            
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE staff_id = '{$staff_id}'
          AND time_in_double_shift_morning != ''
        ORDER BY attendance_id DESC
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
        
        if ($numRowsStaffAtt > 0) {
            $fa = array();
            
            $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);
            date_default_timezone_set("Asia/Calcutta");
            $fa['leave_time_double_shift_morning'] = date('H:i:s');
            $fa['modification_date'] = date('Y-m-d H:i:s');
            $fa['modified_by']       = $staffRec['first_name'] . ' ' . $staffRec['last_name'];

            $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$staff_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getStaffTimeInUpdateEvening() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');
                    
        $SQLStaff     = "
        SELECT * FROM staff
        WHERE developer = 0
          AND published = 1
          AND staff_id  = {$staff_id}
          ";
        $resultStaff  = $db->sql_query($SQLStaff);
        while ($rowStaff = $db->sql_fetchrow($resultStaff)){

            /* Checking Previous attendance */
            $SQLAtt = "
            SELECT * FROM attendance
            WHERE staff_id = {$staff_id}
            ";
            $resultAtt  = $db->sql_query($SQLAtt);
            $numRowsAtt = $db->sql_numrows($resultAtt);

            if ($numRowsAtt > 0) {
                //$this->getMarkPreviousAttendanceRecords($rowStaff);
            }
        }

        $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);

        $today = date('Y-m-d');
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE record_date = '{$today}'
          AND staff_id = {$staff_id}
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

        if ($numRowsStaffAtt == 0) {

            $SQLEmployee = "
            SELECT employee_id
            FROM employee
            WHERE staff_id = {$staff_id}
            AND status = 'Active'
            ";
            $resultEmployee = $db->sql_query($SQLEmployee);
            $rowEmployee    = $db->sql_fetchrow($resultEmployee);

            $fa = array();

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id']      = $staffRec['site_id'];
            }

            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_double_shift_evening']   = date('H:i:s');
            $fa['creation_date']    = date('Y-m-d H:i:s');
            $fa['staff_id']         = $staff_id;
            $fa['employee_id']      = $rowEmployee['employee_id'];
            $fa['record_date']      = $today;
            $fa['created_by']       = $staffRec['first_name'] . ' ' . $staffRec['last_name'];
            $fa['shift']            = 'Double Shift Evening';

            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, $cpCfg['cp.attendanceTable']);
            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
            $hist_id                = $cpCfg['cp.attendanceTable'] . '_id';
            $hist_id                = $db->sql_nextid();
        } else {
            $fa = array();
            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_double_shift_evening'] = date('H:i:s');
            $fa['shift']         = 'Double Shift';

            $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$staff_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);            
        }
    }

    /**
     *
     */
    function getStaffTimeOutUpdateEvening() {
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $staff_id = $fn->getReqParam('staff_id');

        $today     = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime("yesterday"));
            
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE staff_id = '{$staff_id}'
          AND time_in_double_shift_evening != ''
        ORDER BY attendance_id DESC
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
        
        if ($numRowsStaffAtt > 0) {
            $fa = array();
            
            $staffRec = $fn->getRecordRowById('staff', 'staff_id', $staff_id);
            date_default_timezone_set("Asia/Calcutta");
            $fa['leave_time_double_shift_evening'] = date('H:i:s');
            $fa['modification_date'] = date('Y-m-d H:i:s');
            $fa['modified_by']       = $staffRec['first_name'] . ' ' . $staffRec['last_name'];

            $whereCondition = "WHERE record_date = '{$today}' AND staff_id = '{$staff_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);
        }
    }
}