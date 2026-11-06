<?
class CPL_Admin_Widgets_Hms_AttendanceReportDashboard_Model extends CP_Common_Lib_WidgetModelAbstract
{
    
    function getSQL(){
        
        $SQL = "
        SELECT e.*
        FROM `employee` e
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'e';

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $employee_id  = $fn->getReqParam('employee_id');
        $current_date = date('Y-m-d');
        $last30days   = date('Y-m-d', strtotime('today - 7 days'));
        $on_leave    = $fn->getReqParam('on_leave');

        $searchVar->sqlSearchVar[] = "e.category = 'Nurse'";
        $searchVar->sqlSearchVar[] = "e.staff_id != ''";
        $searchVar->sqlSearchVar[] = "e.status = 'Active'";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_attendanceReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "AttendanceReport_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $employee_id  = $fn->getReqParam('employee_id');
        $current_date = date('Y-m-d');

        $monthValAppendSql  = '';
        $yearValAppendSql   = '';
        $startDateAppendSql = '';

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Present / Absent');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day TI/TO');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Night TI/TO');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TI/TO');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Hrs Worked');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(a.record_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(a.record_date, '%Y') = '{$yearVal}'" ;
        }

        if ($employee_id != '') {
            $startDateAppendSql .= "AND a.employee_id = '{$employee_id}'";
        }
  
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND a.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT a.*
              ,TIME_FORMAT(a.time_in, '%H:%i') time_in_formatted
              ,TIME_FORMAT(a.leave_time, '%H:%i') leave_time_formatted
              ,TIME_FORMAT(a.time_in_shift2, '%H:%i') time_in_shift2_formatted
              ,TIME_FORMAT(a.leave_time_shift2, '%H:%i') leave_time_shift2_formatted
              ,TIME_FORMAT(a.time_in_shift1, '%H:%i') time_in_shift1_formatted
              ,TIME_FORMAT(a.leave_time_shift1, '%H:%i') leave_time_shift1_formatted
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM `attendance` a
        LEFT JOIN (`employee` e) ON (e.employee_id = a.employee_id)
        WHERE {$startDateAppendSql}
        {$monthValAppendSql}
        {$yearValAppendSql}
        {$appendSqlSite}
        ORDER BY a.attendance_id DESC
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $creationDate = $fn->getCPDate($row['record_date'],"d-m-Y");
            $record_sign_in        = $row['time_in'];
            $record_sign_out       = $row['leave_time'];
            $record_created        = $row['record_date'];
            $time1                 = date("H:i", strtotime($record_sign_in) );
            $time2                 = date("H:i", strtotime($record_sign_out) );
            $record_created        = date("l", strtotime($record_created) );
            $day                   = $record_created;
            list($hours, $minutes) = explode(':', $time1);
            $startTimestamp        = mktime($hours, $minutes);
            list($hours, $minutes) = explode(':', $time2);
            $endTimestamp          = mktime($hours, $minutes);
            $seconds               = $endTimestamp - $startTimestamp;
            $minutes               = ($seconds / 60) % 60;
            $hours                 = floor($seconds / (60 * 60));
            
            if($row['leave_time'] != '00:00:00' && $row['leave_time'] != ''){
                $total_time = sprintf("%02d", $hours). ":" .sprintf("%02d", $minutes);
            } else {
                $total_time = '';
            }

            $on_leave = ($row['on_leave'] == 1) ? "Absent" : "Present";

            $timeInOutShiftDay   = $row['time_in_formatted'].' / '.$row['leave_time_formatted'];
            $timeInOutShiftDay   = rtrim($timeInOutShiftDay, ' / ');
            $timeInOutShiftNight = $row['time_in_shift2_formatted'].' / '.$row['leave_time_shift2_formatted'];
            $timeInOutShiftNight = rtrim($timeInOutShiftNight, ' / ');
            $timeInOutShift      = $row['time_in_shift1_formatted'].' / '.$row['leave_time_shift1_formatted'];
            $timeInOutShift      = rtrim($timeInOutShift, ' / ');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $creationDate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $on_leave);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $timeInOutShiftDay);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $timeInOutShiftNight);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $timeInOutShift);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_time);
        }

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}