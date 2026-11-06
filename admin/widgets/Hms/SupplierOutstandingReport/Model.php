<?
class CPL_Admin_Widgets_Hms_SupplierOutstandingReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        
        $SQL = "
        SELECT po.*
              ,su.company_name
        FROM purchase_order po
        LEFT JOIN (`supplier` su) ON (su.supplier_id = po.company_id_supplier)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'po';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $start_date    = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $supplier_id    = $fn->getReqParam('supplier_id');

        /*if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
        }*/

        
        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(po.invoice_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(po.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if ($supplier_id != '') {
            $searchVar->sqlSearchVar[] = "su.supplier_id = '{$supplier_id}'";
        }

        //$searchVar->sqlSearchVar[] = "(po.payment_status = 'Due' OR po.payment_status IS NULL OR po.payment_status = 'Partially Paid')";
        $searchVar->sqlSearchVar[] = "po.company_id_supplier > 0";
        $searchVar->sqlSearchVar[] = "po.status != 'Cancelled'";
        //$searchVar->groupBy   = "po.company_id_supplier";
        $searchVar->sortOrder = "su.company_name, po.invoice_date DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_supplierOutstandingReport');

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

        $file_name = "SupplierOutstandingReport__" . date("d-m-Y") . ".xls";

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
        $supplierIdAppendSql = '';
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $current_date = date('Y-m-d');
        $supplier_id    = $fn->getReqParam('supplier_id');
        $totalAmount = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Supplier Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Due');
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

        /*if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "WHERE po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "WHERE po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "WHERE po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
        }*/

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(po.invoice_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(po.invoice_date, '%Y') = '{$yearVal}'" ;
        }

        if ($supplier_id != '') {
            $supplierIdAppendSql = "AND su.supplier_id = '{$supplier_id}'";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND po.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT po.*
              ,su.company_name
        FROM purchase_order po
        LEFT JOIN (`supplier` su) ON (su.supplier_id = po.company_id_supplier)
        WHERE (po.payment_status = 'Due' OR po.payment_status IS NULL OR po.payment_status = 'Partially Paid')
        AND po.company_id_supplier > 0
        {$appendSqlSite}
        {$startDateAppendSql}
        {$supplierIdAppendSql}
        ORDER BY su.company_name, po.invoice_date DESC
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $totalAmount = 0;
            $colc = 0;
            $rowc++;
            $amount = number_format($row['amount'], 2, '.', '');

            $SQLPaid = "
            SELECT SUM(round(
            (pop.qty * pop.cost_price),2)) AS total_cost
            FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM supplier_receipt_history srh
            LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
            WHERE srh.purchase_order_id = {$row['purchase_order_id']}
              AND sr.receipt_status    != 'Cancelled'
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

            $totalAmount = $rowPaid['total_cost'];
            $Paid_Amount = $rowPartialPayment['Po_partial_payment'];
            $balance_Amount = $totalAmount - $Paid_Amount;

            $totalAmount = number_format($totalAmount, 2);
            $Paid_Amount    = number_format($Paid_Amount, 2);
            $balance_Amount = number_format($balance_Amount, 2);

            $invoice_date  = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
            
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['supplier_inv_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalAmount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $Paid_Amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $balance_Amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['payment_status']);             

       }

        $colc = 0;
        $rowc++;

        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}