<?
class CPL_Admin_Widgets_Hms_PurchaseGstReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $appendSQL = '';

        $SQL = "
        SELECT pop.*
              ,po.po_code
              ,po.purchase_order_date AS po_date
              ,po.supplier_inv_code
              ,s.company_name
              ,s.gst_no
              ,SUM((pop.cost_price * pop.qty) - ((pop.cost_price * pop.discount_percentage) /100 * pop.qty)) AS po_amount
              ,SUM(((pop.cost_price * pop.qty) - ((pop.cost_price * pop.discount_percentage) /100 * pop.qty)) * pop.gst / 100) AS gst_amount
        FROM po_product pop
        LEFT JOIN (`purchase_order` po) ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN supplier s ON s.supplier_id = po.company_id_supplier
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $supplier_id     = $fn->getReqParam('supplier_id');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(po.purchase_order_date , '%Y-%m-%d')  >= '{$start_date}' AND DATE_FORMAT(po.purchase_order_date , '%Y-%m-%d')  <= '{$end_date}'";
        }

        if ($supplier_id != "") {
            $searchVar->sqlSearchVar[] = "po.company_id_supplier = '{$supplier_id}'";
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(po.purchase_order_date , '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(po.purchase_order_date , '%Y') = '{$yearVal}'" ;
        }

        $searchVar->sqlSearchVar[] = "po.status != 'Cancelled'";
        $searchVar->groupBy = "pop.purchase_order_id, pop.gst";
        $searchVar->sortOrder = 'po.purchase_order_id';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_purchaseOrderReport');

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

        $file_name = "ExpenseReport_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.NO.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GSTIN');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'COMPANY NAME');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PURCHASE BILL NO.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PURCHASE DATE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'RATE OF TAX');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL TAXABLE VALUE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CGST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'SGST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'IGST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'R.OFF');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'LORRY FRIEGHT');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL INVOICE VALUE');

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

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $supplier_id     = $fn->getReqParam('supplier_id');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        $appendFollowUpDateSQL = '';
        if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $appendFollowUpDateSQL = "AND DATE_FORMAT(po.purchase_order_date , '%Y-%m-%d')  >= '{$start_date}' AND DATE_FORMAT(po.purchase_order_date , '%Y-%m-%d')  <= '{$end_date}'";
        }

        $monthSql = '';
        if ($monthVal != '') {
            $monthSql = "AND DATE_FORMAT(po.purchase_order_date , '%m') = '{$monthVal}'" ;
        }

        $yearSql = '';
        if ($yearVal != '') {
            $yearSql = "AND DATE_FORMAT(po.purchase_order_date , '%Y') = '{$yearVal}'" ;
        }

        $comSql = '';
        if ($supplier_id != "") {
            $comSql = "AND po.company_id_supplier = '{$supplier_id}'";
        }

        $count =1;

        $SQL = "
        SELECT pop.*
              ,po.po_code
              ,po.purchase_order_date AS po_date
              ,po.supplier_inv_code
              ,s.company_name
              ,s.gst_no
              ,SUM((pop.cost_price * pop.qty) - ((pop.cost_price * pop.discount_percentage) /100 * pop.qty)) AS po_amount
              ,SUM(((pop.cost_price * pop.qty) - ((pop.cost_price * pop.discount_percentage) /100 * pop.qty)) * pop.gst / 100) AS gst_amount
        FROM po_product pop
        LEFT JOIN (`purchase_order` po) ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN supplier s ON s.supplier_id = po.company_id_supplier
        WHERE po.status != 'Cancelled'
        {$appendFollowUpDateSQL}
        {$monthSql}
        {$yearSql}
        {$comSql}
        GROUP BY pop.purchase_order_id, pop.gst
        ORDER BY po.purchase_order_id
        ";

        $result = $db->sql_query($SQL);
        $total = 0;
        $total_po_amount_without_gst = 0;
        $total_half_gst = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {

            $colc = 0;
            $rowc++;

            $po_date = $fn->getCPDate($row['po_date'],"d-m-Y");
            $po_amount = $row['po_amount'] + $row['gst_amount'];
            $half_gst = $row['gst_amount'] / 2;

            $total_po_amount_without_gst += $row['po_amount'];
            $total_half_gst += $half_gst;
            $total += $po_amount;

            //$half_gst    = round($half_gst, 2);
            //$po_amount    = round($po_amount, 2);
            $po_amount_without_gst    = round($row['po_amount'], 2);
            $gst = round($row['gst']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gst_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['supplier_inv_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $po_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gst);
            $actSheet->getStyle('G'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['po_amount']);
            $actSheet->getStyle('H'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $half_gst);
            $actSheet->getStyle('I'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $half_gst);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->getStyle('M'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $po_amount);

            $count++;

        }
        $colc = 0;

        $rowc++;
        $total_po_amount_without_gst = round($total_po_amount_without_gst, 2);
        $total_half_gst = round($total_half_gst, 2);
        //$total = round($total, 2);

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Total");
        $actSheet->getStyle('G'.$rowc)->getNumberFormat()->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_po_amount_without_gst);
        $actSheet->getStyle('H'.$rowc)->getNumberFormat()->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_half_gst);
        $actSheet->getStyle('I'.$rowc)->getNumberFormat()->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_half_gst);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->getStyle('M'.$rowc)->getNumberFormat()->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}