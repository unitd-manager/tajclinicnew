<?
class CPL_Admin_Widgets_Hms_DrPaymentReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%M') AS month
              ,ev.employee_id
              ,e.first_name
              ,e.fees_commission
              ,e.fees_commission_type
              ,COUNT(ev.patient_visit_id) AS patient_count
              ,SUM(ev.consultation_fees) AS fees_count
              ,SUM(ev.fees_commission) AS fees_commission_count
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
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
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $employee_id    = $fn->getReqParam('employee_id');

        if ($employee_id != '') {
            $searchVar->sqlSearchVar[] = "ev.employee_id = {$employee_id}" ;
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }


        $searchVar->sqlSearchVar[] = "e.first_name != ''";
        $searchVar->sqlSearchVar[] = "e.visits_patients = 1";
        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
        $searchVar->groupBy        = "DATE_FORMAT(pv.check_up_date, '%M'), e.first_name";
        $searchVar->sortOrder      = "DATE_FORMAT(pv.check_up_date, '%M') desc";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_drPaymentReport');

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

        $file_name = "DrPaymentReport__" . date("d-m-Y") . ".xls";

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
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $employee_id    = $fn->getReqParam('employee_id');
        $totalOverAllCase = 0;
        $totalOverAll = 0;
        $totalOverAllCommission = 0;
        $totalOverAllLabCase = 0;
        $totalOverAllLab = 0;
        $totalOverAllLabCommission = 0;
        if($monthVal == ""){
            $monthVal = $month;
        }

        if($yearVal == ""){
            $yearVal = $year;
        }

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Dr Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Cases');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Commission');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Lab Test');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Commission');

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

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $appendSql = "";
        if ($monthVal != '') {
            $appendSql .= "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $appendSql .= "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%M') AS month
              ,ev.employee_id
              ,e.first_name
              ,e.fees_commission
              ,e.fees_commission_type
              ,COUNT(ev.patient_visit_id) AS patient_count
              ,SUM(ev.consultation_fees) AS fees_count
              ,SUM(ev.fees_commission) AS fees_commission_count
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
        WHERE e.first_name != ''
        AND e.visits_patients = 1
        AND pv.status != 'Cancelled'
        {$employeeIdAppendSql}
        {$monthValAppendSql}
        {$yearValAppendSql}
        {$appendSqlSite}
        GROUP BY DATE_FORMAT(pv.check_up_date, '%M'), e.first_name
        ORDER BY DATE_FORMAT(pv.check_up_date, '%M') DESC
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            if($row['fees_commission_type'] == 'Value'){
                $row['fees_commission_type'] = 'Rs';
            }

            $fees_count = number_format($row['fees_count']);
            $fees_commission_count = number_format($row['fees_commission_count']);
            $fees_commission = number_format($row['fees_commission'], 0, '.', '');

            $SQLPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,e.lab_commission_type
                   ,e.lab_commission
                   ,SUM(m.fees) AS fees
                   ,(CASE 
                    WHEN e.lab_commission_type = '%'
                    THEN SUM(m.fees * e.lab_commission / 100)
                    ELSE SUM(e.lab_commission) END ) AS labCommission
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            LEFT JOIN (medical_test_visit m) ON (m.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            WHERE pv.status != 'Cancelled'
            {$appendSql}
            AND ev.employee_id = {$row['employee_id']}
            GROUP BY DATE_FORMAT(pv.check_up_date, '%m'), ev.employee_id
            ";
            $resultPatLabTest = $db->sql_query($SQLPatLabTest);
            $rowPatLabTest    = $db->sql_fetchrow($resultPatLabTest);

            $lab_fees_amount           = number_format($rowPatLabTest['fees']);
            $lab_fees_commission_count = number_format($rowPatLabTest['labCommission']);
            if($rowPatLabTest['lab_commission_type'] == 'Value'){
                $rowPatLabTest['lab_commission_type'] = 'Rs';
            }

            $lab_fees_commission = number_format($rowPatLabTest['lab_commission'], 0, '.', '');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['month']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['patient_count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fees_count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $fees_commission_count.' ('.$fees_commission.$row['fees_commission_type'].')');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowPatLabTest['count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $lab_fees_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $lab_fees_commission_count.' ('.$lab_fees_commission.$rowPatLabTest['lab_commission_type'].')');

            $totalOverAllCase          += $row['patient_count'];
            $totalOverAll              += $row['fees_count'];
            $totalOverAllCommission    += $row['fees_commission_count'];
            $totalOverAllLabCase       += $rowPatLabTest['count'];
            $totalOverAllLab           += $rowPatLabTest['fees'];
            $totalOverAllLabCommission += $rowPatLabTest['labCommission'];
        }

        $totalOverAll              = number_format(round($totalOverAll), 2);
        $totalOverAllCommission    = number_format(round($totalOverAllCommission), 2);
        $totalOverAllLab           = number_format(round($totalOverAllLab), 2);
        $totalOverAllLabCommission = number_format(round($totalOverAllLabCommission), 2);

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllCase);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAll);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllCommission);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllLabCase);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllLab);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllLabCommission);

        $actSheet->getStyle("A{$rowc}:H{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


}