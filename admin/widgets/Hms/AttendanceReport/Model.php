<?
class CPL_Admin_Widgets_Hms_AttendanceReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    
    function getSQL(){
        
        $SQL = "
        SELECT a.*
              ,e.first_name
              ,e.status
              ,e.employee_type
              ,e.time_in
              ,e.time_out
              ,e.time_in_night
              ,e.time_out_night
              ,e.time_in_morning
              ,e.time_out_morning
              ,e.time_in_evening
              ,e.time_out_evening
        FROM `attendance` a
        LEFT JOIN (`employee` e) ON (e.employee_id = a.employee_id) 
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
        $searchVar->mainTableAlias = 'a';

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $employee_id  = $fn->getReqParam('employee_id');
        $attendance_id  = $fn->getReqParam('attendance_id');
        $current_date = date('Y-m-d');
        $last30days   = date('Y-m-d', strtotime('today - 7 days'));
        $on_leave    = $fn->getReqParam('on_leave');

        if($tv['module'] == 'common_dashboard'){
            $last7days = date('Y-m-d', strtotime('today - 7 days'));
            $start_date = $last7days;
            $end_date   = $current_date;
        }
        
        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {

            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(a.record_date, '%m') = '{$monthVal}'";
        }

        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(a.record_date, '%Y') = '{$yearVal}'";
        }

        if ($employee_id != '') {
            $searchVar->sqlSearchVar[] = "a.employee_id = '{$employee_id}'";
        }

        if ($on_leave == "Absent") {
            $searchVar->sqlSearchVar[] = "a.on_leave = 1";
        }

        if ($on_leave == "Present") {
            $searchVar->sqlSearchVar[] = "(a.on_leave = 0 OR a.on_leave IS NULL)";
        }


        $searchVar->sqlSearchVar[] = "e.status = 'Active'";
        $searchVar->groupBy   = "a.employee_id";
        $searchVar->sortOrder = 'a.attendance_id DESC';

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
        $dates = '';
        $current_date  = date('Y-m-d');
        $current_year  = date('Y');
        $current_month = date('m');
        $start_date = $current_year . '-' . $current_month . '-' . '01';
        $end_date = $current_year . '-' . $current_month . '-' . '31';

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name');
        for($i=$start_date; $i <= $end_date; $i++){
            $datesAtt  = date('d', strtotime($i));
            //$dates .= "{$datesAtt}";
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $datesAtt);
        }


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
        GROUP BY a.employee_id
        ORDER BY a.attendance_id DESC
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $dates = '';
            $current_date  = date('Y-m-d');
            $current_year  = date('Y');
            $current_month = date('m');
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            $end_date = $current_year . '-' . $current_month . '-' . '31';

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);

            for($i=$start_date; $i <= $end_date; $i++){
                $on_leave = '';
                $datesAtt  = date('Y-m-d', strtotime($i));
                $SQL = "
                SELECT a.*
                FROM `attendance` a
                WHERE a.record_date = '{$datesAtt}'
                  AND a.employee_id = {$row['employee_id']}
                ";
                $result1 = $db->sql_query($SQL);
                $row1 = $db->sql_fetchrow($result1);
                $numRows = $db->sql_numrows($result1);

                //if($numRows > 0){
                if($row1['record_date'] != ''){
                    if($row1['on_leave'] == 1){
                        $on_leave = 'A';
                    } else {
                        $on_leave = 'P';                    
                    }
                }
                //}
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $on_leave);
            }
            
        }

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}