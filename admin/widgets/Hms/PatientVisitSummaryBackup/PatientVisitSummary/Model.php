<?
class CPL_Admin_Widgets_Hms_PatientVisitSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,ev.employee_id
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'pv';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $employee_id    = $fn->getReqParam('employee_id');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');

        if($tv['module'] == 'common_dashboard'){
            /*$start_date = date('Y-m-d', mktime (0,0,0,date("m"), date("d"), date("Y")));
            $end_date = $current_date;*/
            //$start_date = $year . '-' . $month . '-' . '01';
            //$end_date = $year . '-' . $month . '-' . '31';
            $last7days  = date('Y-m-d', strtotime('today - 7 days'));
            $start_date = $last7days;
            $end_date   = $current_date;
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        if ($employee_id != '') {
            $searchVar->sqlSearchVar[] = "ev.employee_id = {$employee_id}" ;
        }
        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "pv.site_id = {$site_id}" ;
            }
        }

        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
        $searchVar->groupBy        = "pv.check_up_date";
        $searchVar->sortOrder      = "pv.check_up_date desc";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_patientVisitSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
    /**
     */
    function getExportToExcel(){
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn       = Zend_Registry::get('fn');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "PatientVisit__" . date("d-m-Y") . ".xls";

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
        $appendSql = '';
        $startDateAppendSql = '';
        $employeeIdAppendSql = '';
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $employee_id    = $fn->getReqParam('employee_id');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $totalOverAll = 0;
        $totalOverAllCase =0;
        $totalOverAllConsult = 0;
        $totalOverAllCaseConsult =0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Doctor');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Duty Doctor');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Consultant');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total - Consultant');

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

        if ($employee_id != '') {
            $employeeIdAppendSql = "AND ev.employee_id = {$employee_id}" ;
        }
        if ($monthVal != '') {
            $monthValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,ev.employee_id
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        WHERE pv.status != 'Cancelled'
        {$startDateAppendSql}
        {$employeeIdAppendSql}
        {$monthValAppendSql}
        {$yearValAppendSql}
        {$appendSqlSite}
        GROUP BY pv.check_up_date
        ORDER BY pv.check_up_date DESC
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");
            $sum_amount = '';
            $case_count = '';
            $doctor     = '';
            $duty_doctor = '';
            $consultant = '';
            $staff      = '';
            $total_amount = '';
            $total_case_count = '';
            $total_amount_consult = '';
            $total_case_count_consult = '';

            $sqlCategory = $fn->getValueListSQL('employeeCategory');
            $resultCat   = $db->sql_query($sqlCategory);
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND pv.site_id = {$cpSiteIdSession}";
            }

            while ($rowCat = $db->sql_fetchrow($resultCat)) {
                $SQLSub = "
                SELECT ev.*
                      ,COUNT(ev.patient_visit_id) AS patient_count
                      ,SUM(ev.consultation_fees) AS fees_count
                FROM employee_visit ev
                LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                WHERE DATE_FORMAT(ev.creation_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                  AND e.first_name != ''
                  AND pv.status != 'Cancelled'
                  AND e.category = '{$rowCat['value']}'
                  {$appendSql}
                ";
                $resultSub = $db->sql_query($SQLSub);
                $sum_amount = '';
                $case_count = '';

                while ($rowSub = $db->sql_fetchrow($resultSub)) {
                    $sum_amount = $sum_amount + $rowSub['fees_count'];
                    $case_count = $case_count + $rowSub['patient_count'];
                }
                if($rowCat['value'] == 'Doctor'){
                    $doctor =  $case_count .' cs - ' . $sum_amount  .' rs';
                }
                if($rowCat['value'] == 'Duty Doctor'){
                    $duty_doctor =  $case_count .' cs - ' . $sum_amount.' rs';
                }
                if($rowCat['value'] == 'Staff'){
                    $staff =  $case_count .' cs - ' . $sum_amount.' rs';
                }
                if($rowCat['value'] == 'Consultant'){
                    $consultant =  $case_count .' cs - ' . $sum_amount.' rs';
                    $total_amount_consult += $sum_amount;
                    $total_case_count_consult   += $case_count;
                }
                $total_amount += $sum_amount;
                $total_case_count   += $case_count;
            }
            $total = $total_case_count .' cs - ' . $total_amount.' rs';
            $total_consultant = ($total_case_count - $total_case_count_consult) .' cs - ' . ($total_amount - $total_amount_consult).' rs';

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $check_up_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['day']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $doctor);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $duty_doctor);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $consultant);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $staff);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_consultant);

            $totalOverAllCase += $total_case_count;
            $totalOverAll += $total_amount;
            $totalOverAllCaseConsult += $total_case_count - $total_case_count_consult;
            $totalOverAllConsult += $total_amount - $total_amount_consult;
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllCase.' - '.$totalOverAll);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllCaseConsult.' - '.$totalOverAllConsult);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    /**
     */
    function getExportToExcel1(){
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn       = Zend_Registry::get('fn');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "PatientVisit__" . date("d-m-Y") . ".xls";

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
        $appendSql = '';
        $startDateAppendSql = '';
        $employeeIdAppendSql = '';
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $employee_id    = $fn->getReqParam('employee_id');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Dr In Charge');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');

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

        if ($employee_id != '') {
            $employeeIdAppendSql = "AND ev.employee_id = {$employee_id}" ;
        }
        if ($monthVal != '') {
            $monthValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%W') AS day
              ,count(a.appointment_id) AS appointment_fixed
              ,ev.employee_id
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        LEFT JOIN (appointment a) ON (a.appointment_id = pv.appointment_id)
        WHERE pv.status != 'Cancelled'
        {$startDateAppendSql}
        {$employeeIdAppendSql}
        {$monthValAppendSql}
        {$yearValAppendSql}
        GROUP BY pv.check_up_date
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $SQL1 = "
            SELECT (CASE 
                    WHEN e.salutation = 'Dr' 
                    THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                    ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                    END) AS employee_name
            FROM employee e
            LEFT JOIN (employee_visit ev) ON (ev.employee_id = e.employee_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE pv.check_up_date = '{$row['check_up_date']}'
            AND pv.status != 'Cancelled'
            GROUP BY e.first_name
            ";
            $result1 = $db->sql_query($SQL1);

            $employee_name = '';

            while ($rowEM = $db->sql_fetchrow($result1)) {
                $employee_name .= $rowEM['employee_name'].', ';
            }
            $employee_name = rtrim($employee_name, ', ');
            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

            $SqlAppointment ="
            SELECT count(ev.patient_visit_id) AS patients_visited
            FROM employee_visit ev
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE pv.record_type = 'By Appointment'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
            ";
            $resultAppointment = $db->sql_query($SqlAppointment);
            $rowAp = $db->sql_fetchrow($resultAppointment);

            $SqlWalkIn ="
            SELECT count(ev.patient_visit_id) AS patients_walkin
            FROM employee_visit ev
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE pv.record_type = 'Walk In'
              AND pv.check_up_date = '{$row['check_up_date']}'
              AND pv.status != 'Cancelled'
            ";
            $resultWalkIn = $db->sql_query($SqlWalkIn);
            $rowWI = $db->sql_fetchrow($resultWalkIn);

            $total = $rowAp['patients_visited'] + $rowWI['patients_walkin'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $check_up_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['day']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $employee_name);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}