<?
class CPL_Admin_Widgets_Hms_DailyCollectionReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT i.*
              ,(
                SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                ) as receipt_amount
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,pv.visit_code
              ,o.patient_visit_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (`patient_visit` pv) ON (pv.patient_visit_id = o.patient_visit_id)
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
        $searchVar->mainTableAlias = 'i';

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');
        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');
        $employee_id    = $fn->getReqParam('employee_id');

        if($tv['module'] == 'common_dashboard'){
            /*$start_date = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-30, date("Y")));
            $end_date = $current_date;*/
            $last30days = date('Y-m-d', strtotime('today - 7 days'));
            $start_date = $last30days;
            $end_date   = $current_date;
        }

        if ($start_date != '' && $end_date == '') {
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "i.site_id = {$site_id}" ;
            }
        }

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "o.order_status != 'Cancelled'";

        $searchVar->sortOrder = 'i.invoice_date DESC';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_dailyCollectionReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');

        $serial_no   = 0;
        $grand_total = 0;
        $order_amount = 0;

        foreach($this->dataArray as $row){
            $serial_no += 1;
            $order_amount = $row['receipt_amount'] - $row['sales_return_amount'];
            $grand_total += $order_amount;
        }

        $row = array(
                    'grand_total' => $grand_total
                    );

        return $row;
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

        $file_name = "DailyCollectionReport_" . date("d-m-Y") . ".xls";

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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $record_type    = $fn->getReqParam('record_type');
        $location_id    = $fn->getReqParam('location_id');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $employee_id    = $fn->getReqParam('employee_id');
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $startDateAppendSql = '';

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');

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

        if ($monthVal != '') {
            $monthValAppendSql = "AND DATE_FORMAT(i.invoice_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(i.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == '') {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND i.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT i.*
              ,(
                SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                ) as receipt_amount
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE i.status != 'Cancelled'
          AND o.order_status != 'Cancelled'
        {$startDateAppendSql}
        {$monthValAppendSql}
        {$yearValAppendSql}
        {$appendSqlSite}
        ORDER BY i.invoice_date DESC
        ";

        $result = $db->sql_query($SQL);

        $grand_total = 0 ;
        $grand_totalfrm = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;

            $amount = number_format($row['invoice_amount'] - $row['discount'], 2);
            $grand_total += $amount;

            $grand_totalfrm = number_format(round($grand_total) ,2);

            $creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");

            $rowc++;
            $colc=0 ;


            $amount = number_format(round($amount), 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $creationDate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);

        }
            $colc=0 ;
            $rowc++;

            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Grand Total');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $grand_totalfrm);

            $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}