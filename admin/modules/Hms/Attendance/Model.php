<?
class CPL_Admin_Modules_Hms_Attendance_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT a.*
              ,TIME_FORMAT(a.time_in_day_shift, '%H:%i') time_in_day_shift_formatted
              ,TIME_FORMAT(a.leave_time_day_shift, '%H:%i') leave_time_day_shift_formatted
              ,TIME_FORMAT(a.time_in_night_shift, '%H:%i') time_in_night_shift_formatted
              ,TIME_FORMAT(a.leave_time_night_shift, '%H:%i') leave_time_night_shift_formatted
              ,TIME_FORMAT(a.time_in_double_shift_morning, '%H:%i') time_in_double_shift_morning_formatted
              ,TIME_FORMAT(a.leave_time_double_shift_morning, '%H:%i') leave_time_double_shift_morning_formatted
              ,TIME_FORMAT(a.time_in_double_shift_evening, '%H:%i') time_in_double_shift_evening_formatted
              ,TIME_FORMAT(a.leave_time_double_shift_evening, '%H:%i') leave_time_double_shift_evening_formatted
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM attendance a
        LEFT JOIN (employee e) ON (e.employee_id = a.employee_id) 
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'a';

        $attendance_id  	= $fn->getReqParam('attendance_id');
        $employee_id       	= $fn->getReqParam('employee_id');
        $special_search 	= $fn->getReqParam('special_search');

        $userGroupID    	= $fn->getSessionParam('userGroupID');
        $staffIDS       	= $fn->getSessionParam('staff_id');
        $attendanceDate1    = $fn->getReqParam('attendanceDate1');
        $attendanceDate2    = $fn->getReqParam('attendanceDate2');
        $employee_id        = $fn->getReqParam('employee_id');

        if ($attendance_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.attendance_id  = '{$attendance_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "a.attendance_id  = '{$tv['record_id']}'";
        } else {
            if ($employee_id != '' ) {
                $searchVar->sqlSearchVar[] = "a.employee_id  = '{$employee_id}'";
            }

            if ($tv['special_search'] == "Absent") {
                $searchVar->sqlSearchVar[] = "a.on_leave = 1";
            }

            if ($tv['special_search'] == "Present") {
                $searchVar->sqlSearchVar[] = "(a.on_leave = 0 OR a.on_leave IS NULL)";
            }

            if ($attendanceDate1 != "" && $attendanceDate1 != "From"
            && $attendanceDate2 != "" && $attendanceDate2 != "To" ) {
                $searchVar->sqlSearchVar[] = "(a.record_date BETWEEN '{$attendanceDate1}' AND '{$attendanceDate2}')";
            }

            if ($attendanceDate1 != "" && $attendanceDate1 != "From" && $attendanceDate2 == "To") {
                $searchVar->sqlSearchVar[] = "(a.record_date >= '{$attendanceDate1}')";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    s.first_name LIKE '%{$tv['keyword']}%'
                 OR s.last_name  LIKE '%{$tv['keyword']}%'
                 OR a.notes  LIKE '%{$tv['keyword']}%'
                )";
            }

            //if ($userGroupID != 1 && $userGroupID != 7){
            if ($userGroupID != $cpCfg['cp.superAdminUGId'] && $userGroupID != 7){
                $searchVar->sqlSearchVar[] = "a.staff_id= '{$staffIDS}'";
            }

            $searchVar->sortOrder = "a.record_date DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('staff_id', 'Please select the staff');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $hour = $fn->getReqParam('leave_time_dd_hour');
        $min  = $fn->getReqParam('leave_time_dd_minute');

        $validate->resetErrorArray();

        //$validate->validateData('staff_id', 'Please select the staff');
        $validate->validateData('record_date', 'Please enter the date');
        $validate->validateData('shift', 'Please choose the shift');
        
        /*if ($hour == '' || $min == ''){
            $validate->errorArray['leave_time']['name'] = "leave_time";
            $validate->errorArray['leave_time']['msg']  = 'Please enter the time';
        }*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $attendance_id = $fn->getPostParam('attendance_id');
        if($fa['on_leave'] == '' || $fa['on_leave'] == '0') {            
            $SQLAttendance = "
            SELECT time_in_day_shift
                   ,leave_time_day_shift
                   ,time_in_night_shift
                   ,leave_time_night_shift
                   ,time_in_double_shift_morning
                   ,leave_time_double_shift_morning
                   ,time_in_double_shift_evening
                   ,leave_time_double_shift_evening
                   ,staff_id
                   ,employee_id
            FROM attendance
            WHERE attendance_id = {$attendance_id}
            ";
            $resultAttendance = $db->sql_query($SQLAttendance);
            $rowAttendance    = $db->sql_fetchrow($resultAttendance);
            
            //if($rowAttendance['time_in'] == "" && $rowAttendance['leave_time'] == "" && $rowAttendance['time_in_shift2'] == "" && $rowAttendance['leave_time_shift2'] == "" && $rowAttendance['time_in_shift1'] == "" && $rowAttendance['leave_time_shift1'] == "") {
                $SQLEmployee = "
                SELECT employee_type
                       ,time_in
                       ,time_out
                       ,time_in_night
                       ,time_out_night
                       ,time_in_morning
                       ,time_out_morning
                       ,time_in_evening
                       ,time_out_evening
                       ,staff_id
                FROM employee
                WHERE employee_id = {$rowAttendance['employee_id']}
                ";
                $resultEmployee = $db->sql_query($SQLEmployee);
                $rowEmployee    = $db->sql_fetchrow($resultEmployee);

                $faAtten = array();
                if($fa['shift'] == 'DSM'){
                    $faAtten['time_in_double_shift_morning']    = $rowEmployee['time_in_morning'];
                    $faAtten['leave_time_double_shift_morning'] = $rowEmployee['time_out_morning'];

                } elseif ($fa['shift'] == 'DSE') {
                    $faAtten['time_in_double_shift_evening']    = $rowEmployee['time_in_evening'];
                    $faAtten['leave_time_double_shift_evening'] = $rowEmployee['time_out_evening'];

                } elseif ($fa['shift'] == 'Day') {
                    $faAtten['time_in_day_shift']      = $rowEmployee['time_in'];
                    $faAtten['leave_time_day_shift']   = $rowEmployee['time_out'];

                } elseif ($fa['shift'] == 'Night') {
                    $faAtten['time_in_night_shift']    = $rowEmployee['time_in_night'];
                    $faAtten['leave_time_night_shift'] = $rowEmployee['time_out_night'];
                }

                $whereCondition = "WHERE attendance_id = {$attendance_id}";
                $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($faAtten, 'attendance', $whereCondition);
                $db->sql_query($SQLUpdate);
            //}
        } else {
            $faAtten = array();
            $faAtten['time_in_day_shift']      = '';
            $faAtten['leave_time_day_shift']   = '';
            $faAtten['time_in_night_shift']    = '';
            $faAtten['leave_time_night_shift'] = '';

            $whereCondition = "WHERE attendance_id = {$attendance_id}";
            $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($faAtten, 'attendance', $whereCondition);
            $db->sql_query($SQLUpdate);
        }

        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'record_date');
        $fa = $fn->addToFieldsArray($fa, 'on_leave');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'time_in');
        $fa = $fn->addToFieldsArray($fa, 'leave_time');
        $fa = $fn->addToFieldsArray($fa, 'type_of_leave');

        $fa = $fn->addToFieldsArray($fa, 'time_in_morning');
        $fa = $fn->addToFieldsArray($fa, 'leave_time_morning');
        $fa = $fn->addToFieldsArray($fa, 'time_in_evening');
        $fa = $fn->addToFieldsArray($fa, 'leave_time_evening');
        $fa = $fn->addToFieldsArray($fa, 'task');
        $fa = $fn->addToFieldsArray($fa, 'shift');

        /*$fa['time_in']    = $fn->getFullHourValueFromDD('time_in');
        $fa['leave_time'] = $fn->getFullHourValueFromDD('leave_time');*/
        
        return $fa;
    }

    function getAddNewValuelistFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        
        $valuelist_value = $fn->getPostParam('valuelist_value');
        $valuelist_name  = $fn->getReqParam('valuelist_name');

        /*if (!$this->getAddNewValuelistFormValidate($valuelist_name, $valuelist_value)){
            return $validate->getErrorMessageXML();
        }*/
        
        $fa = array();
        $fa['key_text']      = $valuelist_name;
        $fa['value']         = $valuelist_value;
        $fa['creation_date'] = date("Y-m-d H:i:s");
        
        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'valuelist');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();
            
        return $validate->getSuccessMessageXML('', $valuelist_value);
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        
            $fa = array(
                  'staff_name'    => $phpExcel->getFldObj('Staff Name')
                 ,'record_date'   => $phpExcel->getFldObj('Date')
                 ,'on_leave'   	  => $phpExcel->getFldObj('On Leave')
                 ,'time_in'   	  => $phpExcel->getFldObj('Time In')
                 ,'leave_time'    => $phpExcel->getFldObj('Time out')
            );

        $file_name = "Attendance_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

}
