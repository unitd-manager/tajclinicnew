<?
class CPL_Admin_Widgets_Hms_StockReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT i.*
        	  ,p.title AS product_title
              ,p.item_code
              ,p.price
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        LEFT JOIN (product_company pc) ON (pc.product_id = p.product_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $location_id = $fn->getReqParam('location_id');
        $company_id  = $fn->getReqParam('company_id');

        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "i.site_id = {$location_id}";
        }

        if ($company_id != '') {
            $searchVar->sqlSearchVar[] = "pc.company_id = {$company_id}";
        }

        $searchVar->sqlSearchVar[] = "p.title != ''";

        $searchVar->sortOrder       = "p.title ASC LIMIT 0,2000";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_stockReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getExportToExcel12($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');


        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'contactDate'         => $phpExcel->getFldObj('Date')
             ,'company_name'        => $phpExcel->getFldObj('Client')
             ,'comments'            => $phpExcel->getFldObj('Meeting Notes')
             ,'staff_name'  		=> $phpExcel->getFldObj('Staff')
        );

        $file_name = "LeadByStaff_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
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

        $staff_id  = $fn->getReqParam('staff_id');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');

        $rows = '';
        $appendSql = '';


        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "StockReport__" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Item Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Stock');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cost Price/Qty');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Cost');
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


        $SQL = "
        SELECT i.*
              ,p.title AS product_title
              ,p.item_code
              ,p.price
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        ORDER BY p.title ASC
        ";

        $result = $db->sql_query($SQL);

        $sum_purchase_cp_per_qty = '';

        $linkToStock = '' ;
        $grand_total = 0;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $SQLPP = "
            SELECT pack_size
            FROM po_product
            WHERE product_id = {$row['product_id']}
            ORDER BY po_product_id DESC
            ";
            $resultPP = $db->sql_query($SQLPP);
            $rowPP = $db->sql_fetchrow($resultPP);
            if(is_numeric($rowPP['pack_size'])){
                $price = $row['price'] / $rowPP['pack_size'];
            } else {
                $price = $row['price'];
            }

            $stockArray = $fn->getStockForProduct($row['product_id']);
            $stock      = $stockArray['OverallStock'];

            $sum_purchase_cp_per_qty = $stock * $price;
            $grand_total += $sum_purchase_cp_per_qty;
            if($sum_purchase_cp_per_qty){
                $sum_purchase_cp_per_qty = round($sum_purchase_cp_per_qty, 2);
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stock);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($price,2));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $sum_purchase_cp_per_qty);
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($grand_total,2));
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}