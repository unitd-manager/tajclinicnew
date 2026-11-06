<?
class CPL_Admin_Widgets_Hms_VaccinationReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT vv.*
              ,pi.name
              ,pi.phone
              ,pi.father_name
              ,pi.spuse_name
              ,pi.address_area
        FROM vaccination_visit vv
        LEFT JOIN (medical_test mt) ON (mt.medical_test_id = vv.medical_test_id)
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = vv.patient_visit_id)
        LEFT JOIN (patient_information pi) ON (pi.patient_information_id = vv.patient_information_id)
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
        $searchVar->mainTableAlias = 'vv';
        $month          = date('m');
        $year           = date('Y');
        $start_date    = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');


        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "vv.due_date >= '{$start_date}' AND vv.due_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "vv.due_date >= '{$start_date}' AND vv.due_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "vv.due_date >= '{$start_date}' AND vv.due_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "vv.due_date >= '{$start_date}' AND vv.due_date <= '{$end_date}'";
        }

        
        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(vv.due_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(vv.due_date, '%Y') = '{$yearVal}'" ;
        }
        $searchVar->sqlSearchVar[] = "vv.outside != 1" ;

        $searchVar->sortOrder      = "vv.vaccination_visit_id DESC";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_vaccinationReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     **
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

        $file_name = "InPatientReport__" . date("d-m-Y") . ".xls";

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
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $employeeIdAppendSql = '';
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $current_date = date('Y-m-d');
        $employee_id  = $fn->getReqParam('employee_id');
        $totalOverAllCase = 0;
        $totalOverAll     = 0;
        $totalOverAllCommission = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Patient Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date Admitted');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Time Admitted');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date Discharge');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');

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
            $startDateAppendSql = "WHERE pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "WHERE pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "WHERE pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "WHERE pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT pv.check_up_date 
              ,CONCAT_WS('/', pi.name, pi.gender, pi.age_year) AS name
              ,inp.date_admitted    
              ,inp.time_admitted    
              ,inp.date_discharge
              ,inp.amount 
              ,inp.status
        FROM in_patient inp
        LEFT JOIN (patient_visit pv) ON (pv.patient_information_id= inp.patient_information_id)
        LEFT JOIN (patient_information pi) ON (pi.patient_information_id = inp.patient_information_id) 
        {$startDateAppendSql}
        GROUP BY DATE_FORMAT(pv.check_up_date, '%M'), name
        ORDER BY DATE_FORMAT(pv.check_up_date, '%M') DESC
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            
            $check_up_date  = $fn->getCPDate($row['check_up_date'], 'd-m-Y');
            $date_admitted  = $fn->getCPDate($row['date_admitted'], 'd-m-Y');
            $date_discharge = $fn->getCPDate($row['date_discharge'], 'd-m-Y');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $check_up_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date_admitted);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['time_admitted']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date_discharge);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
             

       }

        $colc = 0;
        $rowc++;

        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


}