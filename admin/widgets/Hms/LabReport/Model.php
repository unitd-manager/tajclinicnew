<?
class CPL_Admin_Widgets_Hms_LabReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        /*$SQL = "
        SELECT mtv.creation_date
              ,SUM(mt.fees) As amount
              ,DATE_FORMAT(mtv.creation_date, '%Y-%m-%d') AS date
              ,mt.blood_related
              ,(SELECT COUNT(mtv.medical_test_id)
                FROM medical_test_visit m
                WHERE mt.blood_related = 1
                AND m.medical_test_visit_id = mtv.medical_test_visit_id
                AND mt.medical_test_id = mtv.medical_test_id) AS labtestcount
              ,(SELECT COUNT(mtv.medical_test_id)
                FROM medical_test_visit m
                WHERE mtv.title = 'X Ray'
                AND m.medical_test_visit_id = mtv.medical_test_visit_id
                AND mt.medical_test_id = mtv.medical_test_id) AS xraycount
              ,(SELECT COUNT(mtv.medical_test_id)
                FROM medical_test_visit m
                WHERE mtv.title = 'ECG'
                AND m.medical_test_visit_id = mtv.medical_test_visit_id
                AND mt.medical_test_id = mtv.medical_test_id) AS ECGcount
              ,(SELECT SUM(mt.fees)
                FROM medical_test_visit m
                WHERE mt.blood_related = 1
                AND m.medical_test_visit_id = mtv.medical_test_visit_id
                AND mt.medical_test_id = mtv.medical_test_id) AS labtestfees
              ,(SELECT SUM(mt.fees)
                FROM medical_test_visit m
                WHERE mtv.title = 'X Ray'
                AND m.medical_test_visit_id = mtv.medical_test_visit_id
                AND mt.medical_test_id = mtv.medical_test_id) AS XRayfees
              ,(SELECT SUM(mt.fees)
                FROM medical_test_visit m
                WHERE mtv.title = 'ECG'
                AND m.medical_test_visit_id = mtv.medical_test_visit_id
                AND mt.medical_test_id = mtv.medical_test_id) AS ECGfees
        FROM medical_test_visit mtv
        LEFT JOIN (medical_test mt) ON (mt.medical_test_id = mtv.medical_test_id)
        ";

        $SQL = "
        SELECT mtv.creation_date
              ,DATE_FORMAT(mtv.creation_date, '%Y-%m-%d') AS date
        FROM medical_test_visit mtv
        LEFT JOIN (medical_test mt) ON (mt.medical_test_id = mtv.medical_test_id)
        ";*/

        $SQL = "
        SELECT * FROM (
          SELECT site_id, DATE_FORMAT(mtv.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_visit mtv
          UNION  
          SELECT site_id, DATE_FORMAT(mtl.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_lab mtl
          UNION  
          SELECT site_id, DATE_FORMAT(mtip.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_in_patient mtip
        ) A
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
        $searchVar->mainTableAlias = 'A';

        $medical_test_id = $fn->getReqParam('medical_test_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        if($tv['module'] == 'common_dashboard'){
           $last7days  = date('Y-m-d', strtotime('today - 7 days'));
            $start_date = $last7days;
            $end_date   = $current_date;
        }

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "creation_date >= '{$start_date}' AND creation_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "creation_date >= '{$start_date}' AND creation_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "creation_date >= '{$start_date}' AND creation_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "creation_date >= '{$start_date}' AND creation_date <= '{$end_date}'";
        }

        if ($start_date == '') {
            if ($monthVal != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(creation_date, '%m') = '{$monthVal}'" ;
            }
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(creation_date, '%Y') = '{$yearVal}'" ;
        }

        $searchVar->sortOrder = "creation_date DESC";
        $searchVar->groupBy   = "DATE_FORMAT(creation_date, '%Y-%m-%d')";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_labReportSummary');

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

        $file_name = "labReport__" . date("d-m-Y") . ".xls";

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
        $monthValAppendSql = '';
        $yearValAppendSql = '';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Lab Test (Patient Visit)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Lab Test (Self)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Lab Test (In Patient)');
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

        if ($start_date == '') {
            if ($monthVal != '') {
                $monthValAppendSql = "AND DATE_FORMAT(creation_date, '%m') = '{$monthVal}'" ;
            }
        }
        
        if ($yearVal != '') {
            $yearValAppendSql = "DATE_FORMAT(creation_date, '%Y') = '{$yearVal}'" ;
        }

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND creation_date >= '{$start_date}' AND creation_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND creation_date >= '{$start_date}' AND creation_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND creation_date >= '{$start_date}' AND creation_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND creation_date >= '{$start_date}' AND creation_date <= '{$end_date}'";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }


        $SQL = "
        SELECT * FROM (
          SELECT site_id, DATE_FORMAT(mtv.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_visit mtv
          UNION  
          SELECT site_id, DATE_FORMAT(mtl.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_lab mtl
          UNION  
          SELECT site_id, DATE_FORMAT(mtip.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_in_patient mtip
        ) A
        WHERE 
        {$yearValAppendSql}
        {$monthValAppendSql}
        {$startDateAppendSql}
        {$appendSqlSite}
        GROUP BY DATE_FORMAT(creation_date, '%Y-%m-%d')
        ORDER BY creation_date DESC
        ";

        $result = $db->sql_query($SQL);
        $totalOverAll = 0;
        $totalOverAllCount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $day  = $fn->getCPDate($row['creation_date'], 'D');
            $date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND m.site_id = {$cpSiteIdSession}";
            }

            $SQLPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,SUM(m.fees) AS fees
                   ,SUM(m.lab_supplier_fees) AS lab_supplier_fees
                   ,pv.status
            FROM medical_test_visit m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = m.patient_visit_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND pv.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultPatLabTest = $db->sql_query($SQLPatLabTest);
            $patlabtesttotal = "";
            $totalPattestcount = 0;
            $totalPattestamount = 0;
            while ($rowPatLabTest    = $db->sql_fetchrow($resultPatLabTest)) {
                if($rowPatLabTest['fees'] == ""){
                    $rowPatLabTest['fees'] = 0;
                }
                if($rowPatLabTest['lab_supplier_fees'] == ""){
                    $rowPatLabTest['lab_supplier_fees'] = 0;
                }
                $fees = $rowPatLabTest['fees'] - $rowPatLabTest['lab_supplier_fees'];

                $patlabtesttotal .= $rowPatLabTest['title'].'('.$rowPatLabTest['count'].' - '.$fees.'), ';

                $totalPattestcount  += $rowPatLabTest['count'];
                $totalPattestamount += $fees;

            }
            $patlabtesttotal = rtrim($patlabtesttotal, ", ");

            $SQLLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,SUM(m.fees) AS fees
                   ,SUM(m.lab_supplier_fees) AS lab_supplier_fees
                   ,lt.status
            FROM medical_test_lab m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (lab_test lt) ON (lt.lab_test_id = m.lab_test_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND lt.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultLabTest = $db->sql_query($SQLLabTest);
            $labtesttotal = "";
            $totaltestcount = 0;
            $totaltestamount = 0;
            while ($rowLabTest    = $db->sql_fetchrow($resultLabTest)) {
                if($rowLabTest['fees'] == ""){
                    $rowLabTest['fees'] = 0;
                }
                if($rowLabTest['lab_supplier_fees'] == ""){
                    $rowLabTest['lab_supplier_fees'] = 0;
                }

                $fees = $rowLabTest['fees'] - $rowLabTest['lab_supplier_fees'];

                $labtesttotal .= $rowLabTest['title'].'('.$rowLabTest['count'].' - '.$fees.'), ';

                $totaltestcount  += $rowLabTest['count'];
                $totaltestamount += $fees;

            }

            $labtesttotal = rtrim($labtesttotal, ", ");

            $SQLInPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,SUM(m.fees) AS fees
                   ,SUM(m.lab_supplier_fees) AS lab_supplier_fees
            FROM medical_test_in_patient m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (in_patient ip) ON (ip.in_patient_id = m.in_patient_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND ip.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultInPatLabTest = $db->sql_query($SQLInPatLabTest);
            $InPatLabTestTotal = "";
            $totalIpTestCount  = 0;
            $totalIpTestAmount = 0;
            while ($rowInPatLabTest    = $db->sql_fetchrow($resultInPatLabTest)) {
                if($rowInPatLabTest['fees'] == ""){
                    $rowInPatLabTest['fees'] = 0;
                }
                if($rowInPatLabTest['lab_supplier_fees'] == ""){
                    $rowInPatLabTest['lab_supplier_fees'] = 0;
                }

                $fees = $rowInPatLabTest['fees'] - $rowInPatLabTest['lab_supplier_fees'];

                $InPatLabTestTotal .= $rowInPatLabTest['title'].'('.$rowInPatLabTest['count'].' - '.$fees.'), ';

                $totalIpTestCount  += $rowInPatLabTest['count'];
                $totalIpTestAmount += $fees;

            }

            $InPatLabTestTotal = rtrim($InPatLabTestTotal, ", ");

            $totalAlltestcount  = $totaltestcount + $totalPattestcount + $totalIpTestCount;
            $totalAlltestamount = $totaltestamount + $totalPattestamount + $totalIpTestAmount;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $day);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $patlabtesttotal);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $labtesttotal);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $InPatLabTestTotal);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalAlltestcount.'('.$totalAlltestamount.')');

            $totalOverAll      += $totalAlltestamount; 
            $totalOverAllCount += $totalAlltestcount; 
        }

        $totalOverAll = number_format(round($totalOverAll), 2);

        $colc=0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Grand Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllCount.'('.$totalOverAll.')');

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getSqlForCount() {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $totalOverAll           = 0;
        $totalOverAllCount      = 0;
        $totaltestcount         = 0;
        $totalPattestcount      = 0;
        $totalIpTestCount       = 0;
        $totaltestamount        = 0;
        $totalPattestamount     = 0;
        $totalIpTestAmount      = 0;
        $totalPatTestXrayCount  = 0;
        $totalPatTestXrayAmount = 0;
        $totalPatTestEcgCount   = 0;
        $totalPatTestEcgAmount  = 0;
        $totalTestXrayCount     = 0;
        $totalTestXrayAmount    = 0;
        $totalTestEcgCount      = 0;
        $totalTestEcgAmount     = 0;
        $totalIpTestXrayCount   = 0;
        $totalIpTestXrayAmount  = 0;
        $totalIpTestEcgCount    = 0;
        $totalIpTestEcgAmount   = 0;
        $totalAlltestcount      = 0;
        $totalAlltestamount     = 0;
        $totalLabCount          = 0; 
        $totalLabAmount         = 0; 
        $totalEcgCount          = 0; 
        $totalEcgAmount         = 0; 
        $totalXrayCount         = 0; 
        $totalXrayAmount        = 0; 
        $appendSql = '';

        foreach($this->dataArray as $row){
            $day  = $fn->getCPDate($row['creation_date'], 'D');
            $date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND m.site_id = {$cpSiteIdSession}";
            }

            $SQLPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,SUM(m.fees) AS fees
            FROM medical_test_visit m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = m.patient_visit_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND pv.status != 'Cancelled'
              AND mt.category != 'Vaccination'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultPatLabTest = $db->sql_query($SQLPatLabTest);

            $patlabtesttotal        = "";
            $totalPattestcount      = 0;
            $totalPattestamount     = 0;
            $totalPatTestXrayCount  = 0;
            $totalPatTestXrayAmount = 0;
            $totalPatTestEcgCount   = 0;
            $totalPatTestEcgAmount  = 0;

            while ($rowPatLabTest = $db->sql_fetchrow($resultPatLabTest)) {
                if($rowPatLabTest['fees'] == ""){
                    $rowPatLabTest['fees'] = 0;
                }

                $patlabtesttotal .= $rowPatLabTest['title'].'('.$rowPatLabTest['count'].' - '.$rowPatLabTest['fees'].'), ';

                if($rowPatLabTest['category'] != "radiology" && $rowPatLabTest['category'] != "ECG"){
                    $totalPattestcount  += $rowPatLabTest['count'];
                    $totalPattestamount += $rowPatLabTest['fees'];
                }

                if($rowPatLabTest['category'] == "radiology"){
                    $totalPatTestXrayCount  += $rowPatLabTest['count'];
                    $totalPatTestXrayAmount += $rowPatLabTest['fees'];
                }

                if($rowPatLabTest['category'] == "ECG"){
                    $totalPatTestEcgCount  += $rowPatLabTest['count'];
                    $totalPatTestEcgAmount += $rowPatLabTest['fees'];
                }

            }
            
            $patlabtesttotal = rtrim($patlabtesttotal, ", ");

            $SQLLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,SUM(m.fees) AS fees
            FROM medical_test_lab m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (lab_test lt) ON (lt.lab_test_id = m.lab_test_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND lt.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultLabTest = $db->sql_query($SQLLabTest);
            $labtesttotal        = "";
            $totaltestcount      = 0;
            $totaltestamount     = 0;
            $totalTestXrayCount  = 0;
            $totalTestXrayAmount = 0;
            $totalTestEcgCount   = 0;
            $totalTestEcgAmount  = 0;

            while ($rowLabTest    = $db->sql_fetchrow($resultLabTest)) {
                if($rowLabTest['fees'] == ""){
                    $rowLabTest['fees'] = 0;
                }

                $labtesttotal .= $rowLabTest['title'].'('.$rowLabTest['count'].' - '.$rowLabTest['fees'].'), ';

                if($rowLabTest['category'] != "radiology" && $rowLabTest['category'] != "ECG"){
                    $totaltestcount  += $rowLabTest['count'];
                    $totaltestamount += $rowLabTest['fees'];
                }

                if($rowLabTest['category'] == "radiology"){
                    $totalTestXrayCount  += $rowLabTest['count'];
                    $totalTestXrayAmount += $rowLabTest['fees'];
                }

                if($rowLabTest['category'] == "ECG"){
                    $totalTestEcgCount  += $rowLabTest['count'];
                    $totalTestEcgAmount += $rowLabTest['fees'];
                }

            }
            $labtesttotal = rtrim($labtesttotal, ", ");

            $SQLInPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,SUM(m.fees) AS fees
            FROM medical_test_in_patient m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (in_patient ip) ON (ip.in_patient_id = m.in_patient_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND ip.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultInPatLabTest = $db->sql_query($SQLInPatLabTest);
            $InPatLabTestTotal     = "";
            $totalIpTestCount      = 0;
            $totalIpTestAmount     = 0;
            $totalIpTestXrayCount  = 0;
            $totalIpTestXrayAmount = 0;
            $totalIpTestEcgCount   = 0;
            $totalIpTestEcgAmount  = 0;

            while ($rowInPatLabTest    = $db->sql_fetchrow($resultInPatLabTest)) {
                if($rowInPatLabTest['fees'] == ""){
                    $rowInPatLabTest['fees'] = 0;
                }

                $InPatLabTestTotal .= $rowInPatLabTest['title'].'('.$rowInPatLabTest['count'].' - '.$rowInPatLabTest['fees'].'), ';

                if($rowInPatLabTest['category'] != "radiology" && $rowInPatLabTest['category'] != "ECG"){
                    $totalIpTestCount  += $rowInPatLabTest['count'];
                    $totalIpTestAmount += $rowInPatLabTest['fees'];
                }

                if($rowInPatLabTest['category'] == "radiology"){
                    $totalIpTestXrayCount  += $rowInPatLabTest['count'];
                    $totalIpTestXrayAmount += $rowInPatLabTest['fees'];
                }

                if($rowInPatLabTest['category'] == "ECG"){
                    $totalIpTestEcgCount  += $rowInPatLabTest['count'];
                    $totalIpTestEcgAmount += $rowInPatLabTest['fees'];
                }

            }

            $InPatLabTestTotal = rtrim($InPatLabTestTotal, ", ");

            $totalAlltestcount  = $totaltestcount + $totalPattestcount + $totalIpTestCount + $totalPatTestXrayCount + $totalPatTestEcgCount + $totalTestXrayCount + $totalTestEcgCount + $totalIpTestXrayCount + $totalIpTestEcgCount;
            $totalAlltestamount = $totaltestamount + $totalPattestamount + $totalIpTestAmount + $totalPatTestXrayAmount + $totalPatTestEcgAmount + $totalTestXrayAmount + $totalTestEcgAmount + $totalIpTestXrayAmount + $totalIpTestEcgAmount;


            $totalOverAll      += $totalAlltestamount; 
            $totalOverAllCount += $totalAlltestcount; 

            $totalLabCount   += $totaltestcount + $totalPattestcount + $totalIpTestCount;
            $totalXrayCount  += $totalPatTestXrayCount + $totalTestXrayCount + $totalIpTestXrayCount;
            $totalEcgCount   += $totalPatTestEcgCount + $totalTestEcgCount + $totalIpTestEcgCount;
            $totalLabAmount  += $totaltestamount + $totalPattestamount + $totalIpTestAmount;
            $totalXrayAmount += $totalPatTestXrayAmount + $totalTestXrayAmount + $totalIpTestXrayAmount;
            $totalEcgAmount  += $totalPatTestEcgAmount + $totalTestEcgAmount + $totalIpTestEcgAmount;
        }

        $text = "
        <tr>
            <th width='26%' style='border-top:1px solid #FFFFFF;line-height:25px;'>LAB TEST: {$totalLabCount} / {$totalLabAmount}RS</th>
            <th width='26%' style='border-top:1px solid #FFFFFF;line-height:25px;'>X-RAY: {$totalXrayCount} / {$totalXrayAmount}RS</th>
            <th width='26%' style='border-top:1px solid #FFFFFF;line-height:25px;'>ECG: {$totalEcgCount} / {$totalEcgAmount}RS</th>
        </tr>
        ";

        return $text;
    }

}