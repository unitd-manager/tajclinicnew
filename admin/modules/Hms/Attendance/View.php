<?
class CPL_Admin_Modules_Hms_Attendance_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";
        $task       = '';
        $timeInOutShiftDay = '';
        $timeInOutShiftNight = '';
        foreach ($dataArray as $row){
            $time_in = substr($row['time_in_day_shift'], 0, 5);
            $leave_time = substr($row['leave_time_day_shift'], 0, 5);

            if ($cpCfg['m.hms.attendance.hasMultipleSessions'] == 1) {

                $timeInOutData = "
                {$listObj->getListDataCell($row['time_in_morning'])}
                {$listObj->getListDataCell($row['leave_time_morning'])}
                {$listObj->getListDataCell($row['time_in_evening'])}
                {$listObj->getListDataCell($row['leave_time_evening'])}
                ";

                /*$timeIn = '';
                if ($row['time_in_morning'] == '' || $row['time_in_morning'] == '00:00:00') {
                    $timeIn = $listObj->getListDataCell($row['time_in_evening']);
                } else {
                    $timeIn = $listObj->getListDataCell($row['time_in_morning']);
                }

                $timeOut = '';
                if ($row['leave_time_evening'] == '' || $row['leave_time_evening'] == '00:00:00') {
                    $timeOut = $listObj->getListDataCell($row['leave_time_morning']);
                } else {
                    $timeOut = $listObj->getListDataCell($row['leave_time_evening']);
                }

                $timeInOutData = "
                {$timeIn}
                {$timeOut}
                ";*/

            } else {
                $timeInOutShiftDay = $row['time_in_day_shift_formatted'].' / '.$row['leave_time_day_shift_formatted'];
                $timeInOutShiftDay = rtrim($timeInOutShiftDay, ' / ');
                $timeInOutDataDayShift = "
                {$listObj->getListDataCell($timeInOutShiftDay)}
                ";

                $timeInOutShiftNight = $row['time_in_night_shift_formatted'].' / '.$row['leave_time_night_shift_formatted'];
                $timeInOutShiftNight = rtrim($timeInOutShiftNight, ' / ');
                $timeInOutDataNightShift = "
                {$listObj->getListDataCell($timeInOutShiftNight)}
                ";

                $timeInOutShiftDSMorning = $row['time_in_double_shift_morning_formatted'].' / '.$row['leave_time_double_shift_morning_formatted'];
                $timeInOutShiftDSMorning = rtrim($timeInOutShiftDSMorning, ' / ');
                $timeInOutDataShiftDSMorning = "
                {$listObj->getListDataCell($timeInOutShiftDSMorning)}
                ";

                $timeInOutShiftDSEvening = $row['time_in_double_shift_evening_formatted'].' / '.$row['leave_time_double_shift_evening_formatted'];
                $timeInOutShiftDSEvening = rtrim($timeInOutShiftDSEvening, ' / ');
                $timeInOutDataShiftDSEvening = "
                {$listObj->getListDataCell($timeInOutShiftDSEvening)}
                ";

                $sqlEmployee = "
                SELECT employee_type
                FROM employee
                WHERE employee_id = {$row['employee_id']}
                ";
                $resultEmployee = $db->sql_query($sqlEmployee);
                $rowEmployee    = $db->sql_fetchrow($resultEmployee);
            }
            
            $createdModifiedBy = $row['created_by'].' / '.$row['modified_by'];
            $createdModifiedBy = rtrim($createdModifiedBy, ' / ');

            $record_date = $fn->getCPDate($row['record_date'],"d-m-Y");

            $record_sign_in        = $row['time_in_day_shift'];
            $record_sign_out       = $row['leave_time_day_shift'];
            $record_created        = $row['record_date'];
            $time1                 = date("H:i", strtotime($record_sign_in));
            $time2                 = date("H:i", strtotime($record_sign_out));
            $record_created        = date("l", strtotime($record_created));
            $day                   = $record_created;
            list($hours, $minutes) = explode(':', $time1);
            $startTimestamp        = mktime($hours, $minutes);
            list($hours, $minutes) = explode(':', $time2);
            $endTimestamp          = mktime($hours, $minutes);
            $seconds               = $endTimestamp - $startTimestamp;
            $minutes               = ($seconds / 60) % 60;
            $hours                 = floor($seconds / (60 * 60));
            
            if($row['time_in_day_shift'] != '00:00:00' && $row['time_in_day_shift'] != '' && $row['leave_time_day_shift'] != '00:00:00' && $row['leave_time_day_shift'] != ''){
                $total_time = sprintf("%02d", $hours). ":" .sprintf("%02d", $minutes);
            } else {
                $total_time = '';
            }

            $record_sign_in_day2  = $row['time_in_double_shift_morning'];
            $record_sign_out_day2 = $row['leave_time_double_shift_morning'];
            $record_created_day2  = $row['record_date'];
            $time1_day2           = date("H:i", strtotime($record_sign_in_day2));
            $time2_day2           = date("H:i", strtotime($record_sign_out_day2));
            $record_created_day2  = date("l", strtotime($record_created_day2));
            $day_day2             = $record_created_day2;
            list($hours_day2, $minutes_day2) = explode(':', $time1_day2);
            $startTimestamp_day2 = mktime($hours_day2, $minutes_day2);
            list($hours_day2, $minutes_day2) = explode(':', $time2_day2);
            $endTimestamp_day2 = mktime($hours_day2, $minutes_day2);
            $seconds_day2      = $endTimestamp_day2 - $startTimestamp_day2;
            $minutes_day2      = ($seconds_day2 / 60) % 60;
            $hours_day2        = floor($seconds_day2 / (60 * 60));
            
            if($row['time_in_double_shift_morning'] != '00:00:00' && $row['time_in_double_shift_morning'] != '' && $row['leave_time_double_shift_morning'] != '00:00:00' && $row['leave_time_double_shift_morning'] != ''){
                $total_time_day2 = sprintf("%02d", $hours_day2). ":" .sprintf("%02d", $minutes_day2);
            } else {
                $total_time_day2 = '';
            }

            $record_sign_in_day3  = $row['time_in_double_shift_evening'];
            $record_sign_out_day3 = $row['leave_time_double_shift_evening'];
            $record_created_day3  = $row['record_date'];
            $time1_day3           = date("H:i", strtotime($record_sign_in_day3));
            $time2_day3           = date("H:i", strtotime($record_sign_out_day3));
            $record_created_day3  = date("l", strtotime($record_created_day3));
            $day_day3             = $record_created_day3;
            list($hours_day3, $minutes_day3) = explode(':', $time1_day3);
            $startTimestamp_day3 = mktime($hours_day3, $minutes_day3);
            list($hours_day3, $minutes_day3) = explode(':', $time2_day3);
            $endTimestamp_day3 = mktime($hours_day3, $minutes_day3);
            $seconds_day3      = $endTimestamp_day3 - $startTimestamp_day3;
            $minutes_day3      = ($seconds_day3 / 60) % 60;
            $hours_day3        = floor($seconds_day3 / (60 * 60));
            
            if($row['time_in_double_shift_evening'] != '00:00:00' && $row['time_in_double_shift_evening'] != '' && $row['leave_time_double_shift_evening'] != '00:00:00' && $row['leave_time_double_shift_evening'] != ''){
                $total_time_day3 = sprintf("%02d", $hours_day3). ":" .sprintf("%02d", $minutes_day3);
            } else {
                $total_time_day3 = '';
            }

            $record_sign_in_night  = $row['time_in_night_shift'];
            $record_sign_out_night = $row['leave_time_night_shift'];
            $record_created_night  = $row['record_date'];
            $time1_night           = date("H:i", strtotime($record_sign_in_night));
            $time2_night           = date("H:i", strtotime($record_sign_out_night));
            $record_created_night  = date("l", strtotime($record_created_night));
            $day_night             = $record_created_night;
            list($hours_night, $minutes_night) = explode(':', $time1_night);
            $startTimestamp_night  = mktime($hours_night, $minutes_night);
            list($hours_night, $minutes_night) = explode(':', $time2_night);
            $endTimestamp_night    = mktime($hours_night, $minutes_night);
            $seconds_night         = $endTimestamp_night - $startTimestamp_night;
            $minutes_night         = ($seconds_night / 60) % 60;
            $hours_night           = floor($seconds_night / (60 * 60));
            
            if($row['time_in_night_shift'] != '00:00:00' && $row['time_in_night_shift'] != '' && $row['leave_time_night_shift'] != '00:00:00' && $row['leave_time_night_shift'] != ''){
                $total_time_night = sprintf("%02d", $hours_night). ":" .sprintf("%02d", $minutes_night);
            } else {
                $total_time_night = '';
            }

            $total_time = $this->sum_the_time($total_time, $total_time_day2, $total_time_day3, $total_time_night);

            $on_leave = ($row['on_leave'] == 1) ? "Absent" : "Present";

            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['employee_name'])}
            {$listObj->getListDateCell($row['record_date'])}
            {$listObj->getListDataCell($on_leave)}
            {$timeInOutDataDayShift}
            {$timeInOutDataShiftDSMorning}
            {$timeInOutDataShiftDSEvening}
            {$timeInOutDataNightShift}
            {$listObj->getListDataCell($total_time)}
            {$listObj->getListDataCell($createdModifiedBy)}
            {$listObj->getListDataCell($row['attendance_id'], 'center')}
    		{$listObj->getListRowEnd($row['attendance_id'])}
			";

        	$rowCounter++;
		}

        if ($cpCfg['m.hms.attendance.hasMultipleSessions'] == 1) {
            $timeInOutHeader = "
            {$listObj->getListHeaderCell('Time In (Morning)', 'a.time_in_morning')}
            {$listObj->getListHeaderCell('Time Out (Morning)', 'a.leave_time_morning')}
            {$listObj->getListHeaderCell('Time In (Evening)', 'a.time_in_evening')}
            {$listObj->getListHeaderCell('Time Out (Evening)', 'a.leave_time_evening')}
            ";

            /*$timeInOutHeader = "
            {$listObj->getListHeaderCell('Time In', 'a.time_in_morning')}
            {$listObj->getListHeaderCell('Time Out', 'a.leave_time_evening')}
            ";*/
        } else {
            $timeInOutHeaderDayShift = "
            {$listObj->getListHeaderCell('Day TI/TO')}
            ";

            $timeInOutHeaderNightShift = "
            {$listObj->getListHeaderCell('Night TI/TO')}
            ";

            $timeInOutHeaderDShiftMorning = "
            {$listObj->getListHeaderCell('DSM TI/TO')}
            ";

            $timeInOutHeaderDShiftEventing = "
            {$listObj->getListHeaderCell('DSE TI/TO')}
            ";
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($cpCfg['m.project.staffFieldLabel'], 'b.staff_id')}
        {$listObj->getListHeaderCell('Date', 'a.record_date')}
        {$listObj->getListHeaderCell('Present / Absent', 'a.on_leave')}
        {$timeInOutHeaderDayShift}
        {$timeInOutHeaderDShiftMorning}
        {$timeInOutHeaderDShiftEventing}
        {$timeInOutHeaderNightShift}
        {$listObj->getListHeaderCell('Total Hrs Worked')}
        {$listObj->getListHeaderCell('Create By / Modified By')}
        {$listObj->getListHeaderCell('ID', 'a.attendance_id', 'headerCenter')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
		";
        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff s
        ORDER BY staff_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL($cpCfg['m.project.staffFieldLabel'], 'staff_id', $sqlStaff, $_SESSION['staff_id'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $leave = '';
        
        $sqlStaff = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlEmp = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlStaff = "
        SELECT distinct a.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM `attendance` a
        LEFT JOIN (`employee` e) ON (e.employee_id = a.employee_id) 
        WHERE e.status = 'Active' 
        AND e.add_in_payroll = 1
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

        $expStf = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);

        if ($_SESSION['userGroupName'] == "Super Administrator" || 
            $_SESSION['userGroupName'] == "Administrator") {
            $leave = $formObj->getYesNoRRow('Absent Today', 'on_leave', $row['on_leave'], $expNoEdit);
        }

        $time = "
        {$formObj->getTimeRow('Time in (HH:MM)', 'time_in_day_shift', $row['time_in_day_shift'], $expNoEdit)}
        {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time_day_shift', $row['leave_time_day_shift'], $expNoEdit)}
        ";

        $timeDSM = "
        {$formObj->getTimeRow('Time in (HH:MM)', 'time_in_double_shift_morning', $row['time_in_double_shift_morning'], $expNoEdit)}
        {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time_double_shift_morning', $row['leave_time_double_shift_morning'], $expNoEdit)}
        ";

        $timeDSE = "
        {$formObj->getTimeRow('Time in (HH:MM)', 'time_in_double_shift_evening', $row['time_in_double_shift_evening'], $expNoEdit)}
        {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time_double_shift_evening', $row['leave_time_double_shift_evening'], $expNoEdit)}
        ";

        $timeNight = "
        {$formObj->getTimeRow('Time in (HH:MM)', 'time_in_night_shift', $row['time_in_night_shift'], $expNoEdit)}
        {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time_night_shift', $row['leave_time_night_shift'], $expNoEdit)}
        ";

        $sqlEmployee = "
        SELECT employee_type
        FROM employee
        WHERE employee_id = {$row['employee_id']}
        ";
        $resultEmployee = $db->sql_query($sqlEmployee);
        $rowEmployee    = $db->sql_fetchrow($resultEmployee);

        $inOut = "
        {$formObj->getFieldSetWrapped('Day Timing', $time)}
        {$formObj->getFieldSetWrapped('Night Timing', $timeNight)}
        ";
        $shift = array(
              "Day"
             ,"Night"
        );

        if($rowEmployee['employee_type'] == "Double Shift") {
            $inOut = "
            {$formObj->getFieldSetWrapped('Double Shift Morning Timing', $timeDSM)}
            {$formObj->getFieldSetWrapped('Double Shift Evening Timing', $timeDSE)}
            {$formObj->getFieldSetWrapped('Night Timing', $timeNight)}
            ";

            $shift = array(
                  "DSM"
                 ,"DSE"
                 ,"Night"
            );
        }

        $sqlTypeOfLeave = $fn->getValueListSQL('typeOfLeave');
        $expVl     = array('sqlType' => 'OneField');

        $fielset1  = "
        {$formObj->getDDRowBySQL('Employee',  'employee_id', $sqlStaff, $row['employee_id'], $expStf)}
        {$formObj->getDateRow('Date', 'record_date', $row['record_date'])}
		";


        $text = "
        {$formObj->getFieldSetWrapped('Details', $fielset1)}
        {$leave}
        {$formObj->getDDRowByArr('Shift', 'shift', $shift, $row['shift'])}
        {$inOut}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $userGroupID 		= $fn->getSessionParam('userGroupID');
        $special_search     = $fn->getReqParam('special_search');
        $special_search     = $fn->getReqParam('special_search');
        $attendanceDate1    = $fn->getReqParam('attendanceDate1');
        $attendanceDate2    = $fn->getReqParam('attendanceDate2');
        $yearEnd = date('Y') + 10;
        $SQL = '';
        $employeeText = '';
        $employee_id        = $fn->getReqParam('employee_id');

        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlEmp = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        if ($userGroupID == 1 || $userGroupID == 2){
            $SQLEmp = "
            SELECT distinct a.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
            FROM `attendance` a
            LEFT JOIN (`employee` e) ON (e.employee_id = a.employee_id) 
            WHERE e.status = 'Active' 
            AND e.add_in_payroll = 1
            {$appendSqlEmp}
            ORDER BY employee_name
            ";

            $employeeText ="
            <td>
                <select name='employee_id' >
                    <option value=''>Employee</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLEmp, $employee_id)}
                </select>
            </td>
            ";

        }

        $olArray = array(
            "Present"
            ,"Absent"
        );

		if ($attendanceDate1 == ''){
			$attendanceDate1 = 'From';
		}

		if ($attendanceDate2 == ''){
			$attendanceDate2 = 'To';
		}
        
        $text = "
        {$employeeText}
        <td>
            <select name='special_search'>
                <option value=''>Present / Absent</option>
                {$cpUtil->getDropDown1($olArray, $tv['special_search'])}
            </select>
        </td>
        <td class='dateRange'>
        	<b>Attendance Date:</b>
            <input type='text' allowEdit='1' name='attendanceDate1' class='fld_date'
                   id='fld_quoteDate1' value='{$attendanceDate1 }' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='attendanceDate2' class='fld_date'
                   id='fld_quoteDate2' value='{$attendanceDate2}' yearEnd='{$yearEnd}' />
        </td>
        ";


        return $text;
    }

    /**
     *
     */
   function getSendAttendanceReportToPM() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $appendSQL = '';
        $yesterday = date("Y-m-d", strtotime("yesterday"));
        //$yesterday = "2011-08-14";
        $rowCounter = 0;

        $SQL = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,s.name AS display_name
              ,s.email
        FROM staff s
        WHERE s.staff_id NOT IN
              (SELECT a.staff_id
                FROM attendance a
                WHERE a.staff_id = s.staff_id
                AND a.record_date = '{$yesterday}'
              )
        AND s.position IN ('Programmer SG', 'Programmer HK', 'Programmer', 'Trainee')
        AND s.published = 1
        ORDER BY staff_name
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $staff_name1 = '';
        $rows = '';
        $comma = '';

        $data = array();
        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {

            $staff_name   = $row['display_name'];

            if ($staff_name != $staff_name1){
                $data[$staff_name] = array();
                $staff_name1 = $staff_name;
            }

            $data[$staff_name][] = $row;
        }

        $staff = "";
        $link = "";

        foreach ($data as $staff_name => $rows){

        if($rowCounter > 0){
            $comma = ",";
        }
            $staff_name_disp = "";
            foreach ($rows as $key => $row ){
                $staff_name_disp   = $row['display_name'];
                $staff_id   = $row['staff_id'];
            }

            $staff .= "{$comma} {$staff_name_disp}";
            $link .= "
            <p>
                <p><a href='http://studiouss.usoftsolutions.com/admin/index.php?_topRm=admin&module=hms_attendance&_spAction=list&searchDone=1&staff_id={$staff_id}'>{$staff_name_disp}</a></p>
            </p>
            ";

            if($numRows > 0) {
               $fa['creation_date'] = date("Y-m-d H:i:s");
               $fa['staff_id']      = $row['staff_id'];
               $fa['record_date']   = $yesterday;
               $fa['on_leave']      = 1;
               $fa['leave_time']    = '00:00:00';
               $fa['time_in']       = '00:00:00';
               $fa['type_of_leave'] = 'Personal Leave';
               $SQL                 = $dbUtil->getInsertSQLStringFromArray($fa, "attendance");
               $result              = $db->sql_query($SQL);
            }
            $rowCounter++;

        }

        $s = '';
        if ($rowCounter > 1){
            $s = 's';
        }

        $text = "
        <table border='0'>
            <tbody>
            <p>Dear Syed / Moin</p>
            <p>The below mentioned staff{$s} seems to be not present yesterday / Not marked the attendance. </p>
            <p>They have marked as not present. </p>
            <p>Please update the attendance if any changes</p>
            {$link}
            <p>Thanks for your help.</p>
            <p>Regards<br>
            Admin</p>
            </tbody>
        </table>
        ";

        $message     = $text;

        $subject     = "USS Attendance" ." - " . $staff . " - " . $yesterday;
        $fromName    = "Admin";
        $fromEmail   = "usstech@usoftsolutions.com";

        $toName      = "Syed, Moin";
        $toEmail     = "shafeeq@usoftsolutions.com";
        $toEmail1    = "";

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'toEmail1'  => $toEmail1
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        if ($rowCounter > 0){
            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
            $emailMsg->sendEmail();
        }

        /*if ($rowCounter > 0){
            $smtp  = includeCPClass('libLocal', 'smtp', 'CPSMTP');
            $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
            $error = $smtp->sendEmail($toName, $toEmail1, $fromName, $fromEmail, $subject, $message);
        } */

        return $text;
    }

    /**
     *
     */
   function getCreateAttendanceForAbsent() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //admin/index.php?_spAction=createAttendanceForAbsent&showHTML=0&module=hms_attendance

        $appendSQL = '';
        $yesterday = date("Y-m-d", strtotime("yesterday"));
        //$yesterday = "2011-08-14";
        $rowCounter = 0;

        $SQL = "
        SELECT e.*
        FROM `employee` e
        WHERE e.staff_id != ''
          AND e.status = 'Active'
          AND e.add_in_payroll = 1
          AND e.employee_id NOT IN (SELECT employee_id from attendance where record_date = CURDATE())
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rows = '';

        $data = array();
        while ($row = $db->sql_fetchrow($result)) {
            $today = date("Y-m-d");
            $fa = array();
            $fa['staff_id']         = $row['staff_id'];
            $fa['employee_id']      = $row['employee_id'];
            $fa['record_date']      = $today;
            $fa['on_leave']         = 1;
            $fa['site_id']         = $row['site_id'];
            $fa['creation_date']    = date('Y-m-d H:i:s');
            $fa['created_by']       = 'Admin';

            $SQLInsert      = $dbUtil->getInsertSQLStringFromArray($fa, 'attendance');
            $resultInsert   = $db->sql_query($SQLInsert);
        }

        return 'success';
    }

    function sum_the_time($time1, $time2, $time3, $time4) {
        $times = array($time1, $time2, $time3, $time4);
        $seconds = 0;

        foreach ($times as $time) {

            if($time != ""){
                list($hour,$minute) = explode(':', $time);
                $seconds += $hour*3600;
                $seconds += $minute*60;
            }
        }

        $hours    = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);

        if($minutes <= 9) {
            $minutes = "0".$minutes;
        }

        if($hours <= 9) {
            $hours = "0".$hours;
        }

        $total_hrs = $hours.':'.$minutes;

        if($hours == "00" && $minutes == "00"){
            $total_hrs = "";
        }

        return "{$total_hrs}";
    }

    /**
     *
     */
   function getDeleteDuplicateAttendanceForAbsent() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //admin/index.php?_spAction=deleteDuplicateAttendanceForAbsent&showHTML=0&module=hms_attendance

        $appendSQL = '';
        $rowCounter = 0;

        /*for($i=1; $i < 6; $i++){
            $datesAtt  = '2018-08-'.$i;

            $SQL = "
            SELECT employee_id, record_date 
            FROM attendance 
            WHERE record_date = '{$datesAtt}' 
            GROUP BY employee_id 
            HAVING COUNT(employee_id) > 1 
            ORDER BY `attendance`.`record_date` DESC
            ";
            $result  = $db->sql_query($SQL);

            $numRows = $db->sql_numrows($result);
            $rows = '';

            $data = array();
            while ($row = $db->sql_fetchrow($result)) {
                $sqlDelete ="
                DELETE FROM attendance WHERE employee_id = {$row['employee_id']} AND on_leave = 1 AND record_date = '{$row['record_date']}'
                ";
                $resultDelete  = $db->sql_query($sqlDelete);
            }
        }*/

        return 'success';
    }

    /**
     *
     */
    function getDoubleShiftTimingUpdate() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //http://habibiahms.localhost/admin/index.php?module=hms_attendance&_spAction=doubleShiftTimingUpdate&showHTML=0
    
        $SQLAttendance = "
        SELECT a.attendance_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,a.time_in_day_shift
              ,a.leave_time_day_shift
        FROM attendance a
        LEFT JOIN employee e ON (e.employee_id = a.employee_id)
        WHERE e.employee_type = 'Double Shift'
        AND (a.on_leave IS NULL OR a.on_leave = 0)
        AND a.time_in_day_shift != ''
        AND a.leave_time_day_shift != ''
        ORDER BY a.attendance_id DESC
        ";
        $resultAttendance = $db->sql_query($SQLAttendance);
        while ($rowAttendance    = $db->sql_fetchrow($resultAttendance)) {
            $SQLUpdateAttendance = "
            UPDATE `attendance` SET time_in_double_shift_morning = '{$rowAttendance['time_in_day_shift']}'
                                ,leave_time_double_shift_morning = '{$rowAttendance['leave_time_day_shift']}'
            WHERE attendance_id = {$rowAttendance['attendance_id']} 
            ";
            $resultUpdateAttendance = $db->sql_query($SQLUpdateAttendance);

            $SQLUpdateAttendanceDay = "
            UPDATE `attendance` SET time_in_day_shift = NULL
                                ,leave_time_day_shift = NULL
            WHERE attendance_id = {$rowAttendance['attendance_id']} 
            ";
            $resultUpdateAttendanceDay = $db->sql_query($SQLUpdateAttendanceDay);

            print($rowAttendance['employee_name'].' --- '.$rowAttendance['attendance_id'].'<br/>');
        }
    }
}