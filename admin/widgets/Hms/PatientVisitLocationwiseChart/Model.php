<?
class CPL_Admin_Widgets_Hms_PatientVisitLocationwiseChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT pv.check_up_date
              ,pi.address_area
              ,COUNT(pv.patient_visit_id) AS no_of_visit
              ,DATE_FORMAT(pv.check_up_date, '%M') AS Month
        FROM patient_visit pv
        LEFT JOIN (`patient_information` pi) ON (pi.patient_information_id = pv.patient_information_id)
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

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');
        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');
        $employee_id    = $fn->getReqParam('employee_id');

        /*$last30days = date('Y-m-d', strtotime('today - 30 days'));
        $start_date = $last30days;
        $end_date   = $current_date;*/

        $start_date = date('Y-m-01');
        $end_date   = date('Y-m-t');

        $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";

        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";

        $searchVar->groupBy   = "pi.address_area";
        $searchVar->sortOrder = "COUNT(pv.patient_visit_id) DESC LIMIT 0,10";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_patientVisitLocatiowiseChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     *
    function getSqlForCount() {
        $db = Zend_Registry::get('db');

       $No_of_visit  = 0;
       // $grand_total = 0;
      //  $order_amount = 0;

        foreach($this->dataArray as $row){
            $No_of_visit += 1;
           // $order_amount = $row['receipt_amount'] - $row['sales_return_amount'];
          //  $grand_total += $order_amount;
        }

        $row = array(
                    'No_of_visit' => $No_of_visit
                    );

        return $row;
    }

    /**
     */
 /**
     *
     


    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "PatientVisitByMonth" . date("d-m-Y") . ".xls";

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
        $actSheet = &$objPHPExcel->getActiveSheet();
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $actSheet->mergeCells("A{$rowc}:D{$rowc}");
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PATIENT VISIT LOCATION WISE');

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.NO');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'LOCATION');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'MONTH');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NO.OF VISIT
              //  /******************** FORMAT HEADER *******************/
/*
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A2:{$lastCol}2")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');

        $startDateAppendSql = '';

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql .= "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } 

        else if ($start_date == '' && $end_date != ''){
            $start_date         = $year . '-' . $month . '-' . '01';
            $startDateAppendSql .= "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } 

        else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql .= "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } 

        else if ($monthVal == '' && $yearVal == ''){
            $start_date         = $year . '-' . $month . '-' . '01';
            $end_date           = $year . '-' . $month . '-' . '31';
            $startDateAppendSql .= "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $site_id = $fn->getSessionParam('cp_site_id');
            if($site_id != ''){
                $startDateAppendSql .= "AND pv.site_id = {$site_id}" ;
            }
        }
       
        $SQL = "
        SELECT pv.check_up_date
              ,pi.address_area
              ,COUNT(pv.patient_visit_id) AS no_of_visit
              ,DATE_FORMAT(pv.check_up_date, '%M') AS Month
        FROM patient_visit pv
        LEFT JOIN (`patient_information` pi) ON (pi.patient_information_id = pv.patient_information_id)
        WHERE pv.status != 'Cancelled'
        {$startDateAppendSql}
        GROUP BY pi.address_area, DATE_FORMAT(pv.check_up_date, '%Y-%m')
        ORDER BY COUNT(pv.patient_visit_id) DESC
        ";

        $result = $db->sql_query($SQL);
        $total = 0;
        $count = 1;
        $numberOfVisit = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if($row['address_area'] == ''){
                $row['address_area'] = 'No location mentioned';
            }
            
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, strtoupper($row['address_area']));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, strtoupper($row['Month']));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_visit']);

            $numberOfVisit += $row['no_of_visit'];

            $count++;
        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL VISIT');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, number_format($numberOfVisit, 2));

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }    
*/
}