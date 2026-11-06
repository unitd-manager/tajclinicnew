<?
class CPL_Admin_Widgets_Hms_SalesGstReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT  o.order_id
               ,DATE_FORMAT(o.order_date, '%d-%m-%Y') AS order_date
               ,SUM(ROUND((oi.unit_price * oi.qty), 2)) AS order_amount
               ,oi.gst AS gst
               ,SUM((ROUND((oi.unit_price * oi.qty), 2) * oi.gst)/100) AS gst_amount
               ,o.cust_company_name AS companyName
               ,o.cust_gst_no
               ,o.bill_number
               ,o.gst_bill_no
               ,i.invoice_id
               ,i.invoice_code
               ,i.invoice_date
        FROM `order` o
        LEFT JOIN `order_item` oi ON (oi.order_id = o.order_id)
        LEFT JOIN `invoice` i ON (i.order_id = o.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $yearVal    = $fn->getReqParam('year');
        $monthVal   = $fn->getReqParam('month');

        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');

        if($yearVal != "") {
            $year = $yearVal;
        } else {
            $year = $year;
        }

        if($monthVal != "") {
            $month = $monthVal;
        } else {
            $month = $month;
        }
        
        $dateMonth = $year. '-' .$month;
        $searchVar->sqlSearchVar[] = "DATE_FORMAT(o.order_date, '%Y-%m') = '{$dateMonth}'";
        //$searchVar->sqlSearchVar[] = "o.order_date = '2019-02-05'";
        $searchVar->sqlSearchVar[] = "o.order_status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "i.invoice_id != ''";
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "o.order_type = 'POS'";

          
        $searchVar->sqlSearchVar[] = "(CASE WHEN o.order_date >= '2020-04-01' THEN o.enable_gst = 1 ELSE TRUE END)";

        $searchVar->groupBy   = 'oi.order_id, oi.gst';
        $searchVar->sortOrder = "o.order_id DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_salesGstReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');

        $total_gst_amount      = 0;
        $total_order_amount    = 0;
        $overall_order_amount  = 0;
        $order_id              = '';
        $orderAmountRounded    = 0;
        $orderGSTAmount        = 0;
        $orderAmountWithoutGST = 0;
        foreach($this->dataArray as $row){

            if($row['order_id'] != $order_id) {
                $SQLOrderAmount = "
                SELECT SUM(ROUND((oi.unit_price * oi.qty), 2)) AS order_amount
                      ,SUM((ROUND((oi.unit_price * oi.qty), 2) * oi.gst)/100) AS gst_amount
                FROM `order_item` oi
                WHERE oi.order_id = {$row['order_id']}
                ";
                $resultOrderAmount = $db->sql_query($SQLOrderAmount);
                $rowOrderAmount    = $db->sql_fetchrow($resultOrderAmount); 

                $orderAmountRounded    += round($rowOrderAmount['order_amount']);
                $orderGSTAmount        += $rowOrderAmount['gst_amount'];
                $orderAmountWithoutGST += round($rowOrderAmount['order_amount']) - $rowOrderAmount['gst_amount'];
            }

            $order_id = $row['order_id'];
        }

        $row = array('total_gst_amount' => $orderGSTAmount, 'total_order_amount' => $orderAmountWithoutGST, 'overall_order_amount' => $orderAmountRounded);

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

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $yearVal      = $fn->getReqParam('year');
        $monthVal     = $fn->getReqParam('month');
        $order_status = $fn->getReqParam('order_status');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "SalesGstReport__" . date("d-m-Y") . ".xls";

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
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch GSTIN');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sales Bill No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sales Bill Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sales Invoice Value');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GST %');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Taxable Value');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'IGST Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CGST Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'SGST/UTGST Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CESS Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Tax + Cess');
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

        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        
        if($yearVal != "") {
            $year = $yearVal;
        } else {
            $year = $year;
        }

        if($monthVal != "") {
            $month = $monthVal;
        } else {
            $month = $month;
        }
        
        $dateMonth = $year. '-' .$month;
        $appendSql = "AND DATE_FORMAT(o.order_date, '%Y-%m') = '{$dateMonth}'";

        $SQL = "
        SELECT  o.order_id
               ,DATE_FORMAT(o.order_date, '%d-%m-%Y') AS order_date
               ,SUM(oi.unit_price * oi.qty) AS order_amount
               ,oi.gst AS gst
               ,SUM(((oi.unit_price * oi.qty) * oi.gst)/100) AS gst_amount
               ,o.cust_company_name AS companyName
               ,o.cust_gst_no
               ,o.bill_number
               ,o.gst_bill_no
               ,i.invoice_id
               ,i.invoice_code
               ,i.invoice_date
        FROM `order` o
        LEFT JOIN `order_item` oi ON (oi.order_id = o.order_id)
        LEFT JOIN `invoice` i ON (i.order_id = o.order_id)
        WHERE o.order_status != 'Cancelled'
        AND o.order_type = 'POS'
        {$appendSql}
        AND i.invoice_id != ''
        AND i.status != 'Cancelled'
        AND (CASE WHEN o.order_date >= '2020-04-01' THEN o.enable_gst = 1 ELSE TRUE END)
        GROUP BY oi.order_id, oi.gst
        ORDER BY o.order_id DESC
        ";
        $result = $db->sql_query($SQL);

        $total_order_amount   = 0;
        $total_gst_amount     = 0;
        $overall_order_amount = 0;
        $order_id = '';
        $orderAmountRounded    = 0;
        $orderGSTAmount        = 0;
        $orderAmountWithoutGST = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $totalamount  = $row['gst_amount'] + round($row['order_amount']);
            $totalamount  = $totalamount;
            $order_amount = round($row['order_amount']) - $row['gst_amount'];
            $gstAmount    = $row['gst_amount'];
            
            if($row['invoice_date'] < "2020-04-01"){
                $billNo = $row['bill_number'];
            } else {
                $billNo = $row['gst_bill_no'];                
            }

            if($row['invoice_code'] == ""){
                $invoice_code = 'INV - '.$row['invoice_id']; 
            }
            else{
                $invoice_code = $row['invoice_code'];
            }

            $invoice_date = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
            $totalAmount  = round($row['order_amount']);
            $gst_Sum_Half = $row['gst_amount'] / 2;
            $gst_Sum_Half = $gst_Sum_Half;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['cp.PharmacyGstNumberSalesItem']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpCfg['cp.pharmacyname']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $billNo);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_date);
            $actSheet->getStyle('E'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalAmount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gst']);
            $actSheet->getStyle('G'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $order_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '0.00');
            $actSheet->getStyle('I'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gst_Sum_Half);
            $actSheet->getStyle('J'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gst_Sum_Half);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '0.00');
            $actSheet->getStyle('L'.$rowc)->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gstAmount);

            if($row['order_id'] != $order_id) {
                $SQLOrderAmount = "
                SELECT SUM(ROUND((oi.unit_price * oi.qty), 2)) AS order_amount
                      ,SUM((ROUND((oi.unit_price * oi.qty), 2) * oi.gst)/100) AS gst_amount
                FROM `order_item` oi
                WHERE oi.order_id = {$row['order_id']}
                ";
                $resultOrderAmount = $db->sql_query($SQLOrderAmount);
                $rowOrderAmount    = $db->sql_fetchrow($resultOrderAmount); 

                $orderAmountRounded    += round($rowOrderAmount['order_amount']);
                $orderGSTAmount        += $rowOrderAmount['gst_amount'];
                $orderAmountWithoutGST += round($rowOrderAmount['order_amount']) - $rowOrderAmount['gst_amount'];
            }

            $order_id = $row['order_id'];
        }

        $total_order_amount   = $orderAmountWithoutGST;
        $total_gst_amount     = $orderGSTAmount;
        $overall_order_amount = $orderAmountRounded;

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "Total");
        $actSheet->getStyle('E'.$rowc)->getNumberFormat()->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_order_amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->getStyle('G'.$rowc)->getNumberFormat()->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_order_amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, "");
        $actSheet->getStyle('L'.$rowc)->getNumberFormat()->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        );
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_gst_amount);
        $actSheet->getStyle("A{$rowc}:L{$rowc}")->applyFromArray($headStyle);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}