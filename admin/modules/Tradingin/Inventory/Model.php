<?
class CPL_Admin_Modules_Tradingin_Inventory_Model extends CP_Admin_Modules_Tradingin_Inventory_Model
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $appendSqlStk   = "";
        $leftJoinMedicineSite = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $siteId = $fn->getSessionParam('cp_site_id');
            $appendSqlStk = "AND ibs.site_id = '{$siteId}'";
            $leftJoinMedicineSite = "LEFT JOIN (medicine_site ms) ON (i.product_id = ms.product_id)";
        }

        $SQL = "
        SELECT i.*
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.item_code
              ,p.unit
              ,p.product_code
              ,i.actual_stock AS stock
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        {$leftJoinMedicineSite}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getSQL1() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $appendSqlOrd   = "";
        $appendSqlPur   = "";
        $appendSqlInv   = "";
        $appendSqlStk   = "";
        $appendSqlToStk = "";
        $appendSqlToUFP = "";
        $leftJoinMedicineSite = "";
        $appendSqlInT   = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $siteId = $fn->getSessionParam('cp_site_id');
            $SQLsitedetail="
            SELECT site_id
                   ,title
            FROM site
            WHERE site_id = {$siteId}
            ";
            $resultsitedetail = $db->sql_query($SQLsitedetail);
            $rowsitedetail = $db->sql_fetchrow($resultsitedetail);

            $appendSqlOrd = "AND o.site_id = {$siteId}";
            $appendSqlPur = "AND po.site_id = {$siteId}";
            $appendSqlInv = "AND inv.site_id = {$siteId}";
            $appendSqlStk = "AND st.to_location = '{$siteId}'";
            $leftJoinMedicineSite = "LEFT JOIN (medicine_site ms) ON (i.product_id = ms.product_id)";
        } else {
            $appendSqlStk   = "AND st.from_location = 1";
            $appendSqlToStk = "AND st.to_location   = 1";
            $appendSqlToUFP = "AND st.to_location   = 4";
        }

        $SQL = "
        SELECT i.*
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.item_code
              ,p.unit
              ,p.product_code
              ,
                if(
                    (SELECT SUM(CASE 
                                WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN pp.qty * pp.pack_size
                                ELSE pp.qty END) AS purchased_qty
                     FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     AND po.status != 'Cancelled'
                     {$appendSqlPur})

                    ,(SELECT SUM(CASE 
                                WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN pp.qty * pp.pack_size
                                ELSE pp.qty END) AS purchased_qty
                     FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     AND po.status != 'Cancelled'
                     {$appendSqlPur})
                    ,''
                )
                -
                if(
                    (SELECT SUM(damage_qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     {$appendSqlPur})

                    ,(SELECT SUM(damage_qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     {$appendSqlPur})
                    ,''
                )
                -
                if(
                    (SELECT SUM(inItm.qty) FROM invoice_item inItm
                    LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                    WHERE inItm.record_id = p.product_id
                    AND inItm.not_add_in_stock != 1
                    AND inv.status = 'Paid'
                    AND inv.invoice_type = 'POS'
                    {$appendSqlInv})

                    ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
                    LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                    WHERE inItm.record_id = p.product_id
                    AND inItm.not_add_in_stock != 1
                    AND inv.status = 'Paid'
                    AND inv.invoice_type = 'POS'
                    {$appendSqlInv})
                    ,''
                )
                -
                if(
                    (SELECT SUM(CASE 
                                WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN sth.qty * sth.pack_size
                                ELSE sth.qty END)
                    FROM stock_transfer_history sth 
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlStk})

                    ,(SELECT SUM(CASE 
                                WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN sth.qty * sth.pack_size
                                ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlStk})
                    ,''
                )
                + 
                if(
                    (SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToStk})

                    ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToStk})
                    ,''
                )
                -
                if (
                    (SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToUFP})

                    ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToUFP})
                    ,''
                )
                +
                if(
                    (SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                    {$appendSqlInv})

                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                    {$appendSqlInv})
                    ,''
                
                )
                +
                if(
                    (SELECT SUM(adjust_stock)
                      FROM inventory_adjust_stock
                      WHERE product_id = p.product_id
                      {$appendSqlInT})

                    ,(SELECT SUM(adjust_stock)
                      FROM inventory_adjust_stock
                      WHERE product_id = p.product_id
                      {$appendSqlInT})
                      ,''
                ) AS stock
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        {$leftJoinMedicineSite}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'i';
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $inventory_id        = $fn->getReqParam('inventory_id');
        $supplier_id         = $fn->getReqParam('supplier_id');
        $category_id         = $fn->getReqParam('category_id');
        $minimum_order_level = $fn->getReqParam('minimum_order_level');
        $expiry_date         = $fn->getReqParam('expiry_date');

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $searchVar->sqlSearchVar[] = "ms.site_id = {$cpSiteIdSession}";
        }

        if ($inventory_id != "") {
            $searchVar->sqlSearchVar[] = "i.inventory_id = '{$inventory_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.product_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'i.inventory_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title LIKE '{$tv['keyword']}%'
                    || i.inventory_code LIKE '%{$tv['keyword']}%'
                )";
            } 

            /*else if ($supplier_id != '' ) {
            } else {
                $searchVar->sqlSearchVar[] = "i.actual_stock > 0";
            }*/

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "i.flag = 1";
            }

            if ($minimum_order_level != '') {
                $searchVar->sqlSearchVar[] = "i.minimum_order_level > 0";
                $searchVar->sqlHavingVar[] = "stock <= i.minimum_order_level";
            }

            if ($expiry_date == 'Expiry Date < 100') {
                $searchVar->sqlSearchVar[] = "(i.product_id IN (SELECT ibs.product_id
                                                            FROM inventory_batchwise_stock ibs
                                                            LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id)
                                                            WHERE ibs.current_stock > 0
                                                            AND DATEDIFF(po.expiry_date, Now()) < 100))";
            } else if($expiry_date == 'Expiry Date < 60'){
                $searchVar->sqlSearchVar[] = "(i.product_id IN (SELECT ibs.product_id
                                                            FROM inventory_batchwise_stock ibs
                                                            LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id)
                                                            WHERE ibs.current_stock > 0
                                                            AND DATEDIFF(po.expiry_date, Now()) < 60))";
            } else if($expiry_date == 'Expiry Date < 30'){
                $searchVar->sqlSearchVar[] = "(i.product_id IN (SELECT ibs.product_id
                                                            FROM inventory_batchwise_stock ibs
                                                            LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id)
                                                            WHERE ibs.current_stock > 0
                                                            AND DATEDIFF(po.expiry_date, Now()) < 30))";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(i.flag != 1 OR i.flag IS null)";
            }

            $searchVar->sqlSearchVar[] = "p.published = 1";

            $searchVar->sortOrder = "p.title ASC, stock DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('company_name', 'Please enter the company name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product_id   = $fn->getPostParam('product_id');
        $inventory_id = $fn->getPostParam('inventory_id');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        
        /*$SQLInventory = "
        SELECT changed_stock
        FROM inventory
        WHERE product_id = '{$product_id}'
        AND inventory_id = '{$inventory_id}'
        ";
        $resultInventory  = $db->sql_query($SQLInventory);
        $numRowsInventory = $db->sql_numrows($resultInventory);
        $rowInventory     = $db->sql_fetchrow($resultInventory);

        if($fa['changed_stock'] != $rowInventory['changed_stock']) {
            $fa2 = array();
            $fa2['product_id']   = $product_id;
            $fa2['inventory_id'] = $inventory_id;
            $fa2['adjust_stock'] = $fa['changed_stock'];
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'adjust_stock_log');
            $SQLLog = $dbUtil->getInsertSQLStringFromArray($fa2, 'adjust_stock_log');
            $resultLog = $db->sql_query($SQLLog);
        }*/

        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'changed_stock');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'minimum_order_level');

        return $fa;
    }

    /**
     *
     */
    function getExportDataOld($dataArray){
        $db      = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fa = array(
             'item_code'    => $phpExcel->getFldObj('ITEM CODE')
            ,'product_name' => $phpExcel->getFldObj('MEDICINE NAME')
            ,'stock'        => $phpExcel->getFldObj('STOCK')
            ,'price'        => $phpExcel->getFldObj('UNIT PRICE')
            ,'amount'       => $phpExcel->getFldObj('AMOUNT')
        );

        //$dataArray = $dbUtil->getResultsetAsArray($result);

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getExportData($dataArray){
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

        $file_name = "Ledger_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PRODUCT ID');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'ITEM CODE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'MEDICINE NAME');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STOCK');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'COST PRICE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'AMOUNT');
        //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CHANGED STOCK');
        //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PRODUCT ID');

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

        $acc_head_id = $fn->getReqParam('acc_head_id');

        $appendSqlStk   = "";
        $leftJoinMedicineSite = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $siteId = $fn->getSessionParam('cp_site_id');
            $appendSqlStk = "AND ibs.site_id = '{$siteId}'";
            $leftJoinMedicineSite = "LEFT JOIN (medicine_site ms) ON (i.product_id = ms.product_id)";
        }

        $minimum_order_level = $fn->getReqParam('minimum_order_level');
        $appendSqlMOL = "";
        if ($minimum_order_level != '') {
            $appendSqlMOL = "AND i.minimum_order_level > 0 HAVING stock <= i.minimum_order_level";
        }

        $expiry_date         = $fn->getReqParam('expiry_date');
        $appendSqlExpiry = "";
        if ($expiry_date == 'Expiry Date < 100') {
            $appendSqlExpiry = "AND (i.product_id IN (SELECT ibs.product_id
                                                        FROM inventory_batchwise_stock ibs
                                                        LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id)
                                                        WHERE ibs.current_stock > 0
                                                        AND DATEDIFF(po.expiry_date, Now()) < 100))";
        } elseif($expiry_date == 'Expiry Date < 30'){
            $appendSqlExpiry = "AND (i.product_id IN (SELECT ibs.product_id
                                                        FROM inventory_batchwise_stock ibs
                                                        LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id)
                                                        WHERE ibs.current_stock > 0
                                                        AND DATEDIFF(po.expiry_date, Now()) < 30))";
        }

        $sql = "
        SELECT i.*
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.item_code
              ,p.unit
              ,p.product_code
              ,i.actual_stock AS stock
              ,p.price
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        {$leftJoinMedicineSite}
        WHERE (p.title LIKE '{$tv['keyword']}%' OR i.inventory_code LIKE '%{$tv['keyword']}%')
        AND p.published = 1
        {$appendSqlMOL}
        {$appendSqlExpiry}
        ORDER BY p.title ASC, stock DESC
        ";
        $result = $db->sql_query($sql);
        $total_amount = 0;
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            $stock = str_replace('.00', '', number_format($row['stock'], 2, '.', ''));

            $SQLPP = "
            SELECT pack_size
                  ,cost_price
            FROM po_product
            WHERE product_id = {$row['product_id']}
            ORDER BY po_product_id DESC
            ";
            $resultPP = $db->sql_query($SQLPP);
            $rowPP = $db->sql_fetchrow($resultPP);
            if(is_numeric($rowPP['pack_size'])){
                $price = $rowPP['cost_price'] / $rowPP['pack_size'];
                $price = number_format($price, 2, '.', '');
            } else {
                $price = $rowPP['cost_price'];
            }

            $amount = $price * $stock;
            $total_amount += $amount;

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PROD-'.$row['item_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, strtoupper($row['product_name']));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $stock);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($price, 2));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($amount, 2));
            //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['changed_stock']);
            //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['productId']);
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'TOTAL');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($total_amount, 2));

        $actSheet->getStyle("C{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getExportData1($dataArray){
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

        $file_name = "Ledger_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'ITEM CODE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'MEDICINE NAME');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'STOCK');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'UNIT PRICE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'AMOUNT');
        //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CHANGED STOCK');
        //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PRODUCT ID');

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

        $acc_head_id = $fn->getReqParam('acc_head_id');

        $appendSqlOrd   = "";
        $appendSqlPur   = "";
        $appendSqlInv   = "";
        $appendSqlStk   = "";
        $appendSqlToStk = "";
        $appendSqlToUFP = "";
        $leftJoinMedicineSite = "";
        $appendSqlInT   = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $siteId = $fn->getSessionParam('cp_site_id');
            $SQLsitedetail="
            SELECT site_id
                   ,title
            FROM site
            WHERE site_id = {$siteId}
            ";
            $resultsitedetail = $db->sql_query($SQLsitedetail);
            $rowsitedetail = $db->sql_fetchrow($resultsitedetail);

            $appendSqlOrd = "AND o.site_id = {$siteId}";
            $appendSqlPur = "AND po.site_id = {$siteId}";
            $appendSqlInv = "AND inv.site_id = {$siteId}";
            $appendSqlStk = "AND st.to_location = '{$siteId}'";
            $leftJoinMedicineSite = "LEFT JOIN (medicine_site ms) ON (i.product_id = ms.product_id)";
        } else {
            $appendSqlStk   = "AND st.from_location = 1";
            $appendSqlToStk = "AND st.to_location   = 1";
            $appendSqlToUFP = "AND st.to_location   = 4";
        }

        $minimum_order_level = $fn->getReqParam('minimum_order_level');
        $appendSqlMOL = "";
        if ($minimum_order_level != '') {
            $appendSqlMOL = "AND i.minimum_order_level > 0 HAVING stock <= i.minimum_order_level";
        }

        $sql = "
        SELECT i.*
              ,p.product_id AS productId
              ,p.title AS product_name
              ,p.item_code
              ,p.unit
              ,p.product_code
              ,
                if(
                    (SELECT SUM(CASE 
                                WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN pp.qty * pp.pack_size
                                ELSE pp.qty END) AS purchased_qty
                     FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     AND po.status != 'Cancelled'
                     {$appendSqlPur})

                    ,(SELECT SUM(CASE 
                                WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN pp.qty * pp.pack_size
                                ELSE pp.qty END) AS purchased_qty
                     FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     AND po.status != 'Cancelled'
                     {$appendSqlPur})
                    ,''
                )
                -
                if(
                    (SELECT SUM(damage_qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     {$appendSqlPur})

                    ,(SELECT SUM(damage_qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = p.product_id
                     {$appendSqlPur})
                    ,''
                )
                -
                if(
                    (SELECT SUM(inItm.qty) FROM invoice_item inItm
                    LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                    WHERE inItm.record_id = p.product_id
                    AND inItm.not_add_in_stock != 1
                    AND inv.status = 'Paid'
                    AND inv.invoice_type = 'POS'
                    {$appendSqlInv})

                    ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
                    LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                    WHERE inItm.record_id = p.product_id
                    AND inItm.not_add_in_stock != 1
                    AND inv.status = 'Paid'
                    AND inv.invoice_type = 'POS'
                    {$appendSqlInv})
                    ,''
                )
                -
                if(
                    (SELECT SUM(CASE 
                                WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN sth.qty * sth.pack_size
                                ELSE sth.qty END)
                    FROM stock_transfer_history sth 
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlStk})

                    ,(SELECT SUM(CASE 
                                WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                                THEN sth.qty * sth.pack_size
                                ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlStk})
                    ,''
                )
                + 
                if(
                    (SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToStk})

                    ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToStk})
                    ,''
                )
                -
                if (
                    (SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToUFP})

                    ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    AND st.status = 'Delivered'
                    {$appendSqlToUFP})
                    ,''
                )
                +
                if(
                    (SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                    {$appendSqlInv})

                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = p.product_id
                    {$appendSqlInv})
                    ,''
                
                )
                +
                if(
                    (SELECT SUM(adjust_stock)
                      FROM inventory_adjust_stock
                      WHERE product_id = p.product_id
                      {$appendSqlInT})

                    ,(SELECT SUM(adjust_stock)
                      FROM inventory_adjust_stock
                      WHERE product_id = p.product_id
                      {$appendSqlInT})
                      ,''
                ) AS stock
                ,p.price
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        {$leftJoinMedicineSite}
        WHERE (p.title LIKE '{$tv['keyword']}%' OR i.inventory_code LIKE '%{$tv['keyword']}%')
        {$appendSqlMOL}
        ORDER BY p.title ASC, stock DESC
        ";
        $result = $db->sql_query($sql);
        $total_amount = 0;
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
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

            $amount = $price * $row['stock'];
            $total_amount += $amount;

            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['stock']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($price, 2));
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($amount, 2));
            //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['changed_stock']);
            //$actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['productId']);
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($total_amount, 2));

        $actSheet->getStyle("C{$rowc}:E{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getImportDataOLD(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        /*
        STOCK - qty_in_stock - qty
        PRODUCT - title
        Item Code - item_code
        Category - category_id
        FC from China - fc_price
        Purchase Cost from BLOSSOMS - price
        Product Weight - product_weight
        VAT% - vat_percentage
        Price per KG - weight_per_kg
        Product Display Price - selling_price
        Add Shipping Cost - logistics
        Comission Calculation Price (Less VAT & Logistics) - agent_price
        TC Comsn % ( 5% - 20%) - commission
        */

        $fa = array(
              'title' => $phpExcel->getImportFldObj('PRODUCT')
             //,'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
             ,'item_code' => $phpExcel->getImportFldObj('ITEM CODE')
             ,'inventory_code' => $phpExcel->getImportFldObj('Inventory Code')
             ,'inventory_id' => $phpExcel->getImportFldObj('Inventory Id')
             ,'code' => $phpExcel->getImportFldObj('Code')
             ,'color' => $phpExcel->getImportFldObj('Color')
             ,'size'  => $phpExcel->getImportFldObj('Size')
             ,'model'  => $phpExcel->getImportFldObj('Model')
             ,'stock' => $phpExcel->getImportFldObj('Stock')
             ,'product_id' => $phpExcel->getImportFldObj('Product Id')
        );

        $fa['title']['refOnly'] = true;
        $fa['item_code']['refOnly'] = true;
        $fa['inventory_code']['refOnly'] = true;
        //$fa['inventory_id']['refOnly'] = true;
        $fa['color']['refOnly'] = true;
        $fa['size']['refOnly'] = true;
        $fa['model']['refOnly'] = true;
        $fa['stock']['refOnly'] = true;
        $fa['product_id']['refOnly'] = true;
        $fa['code']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingin_inventory'
            ,'matchFieldArr'       => array('inventory_id')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallbackForStock'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        $fa = array(
             'inventory_batchwise_stock_id' => $phpExcel->getImportFldObj('Stock Id')
            ,'title'                        => $phpExcel->getImportFldObj('Medicine Name')
            ,'batch_no'                     => $phpExcel->getImportFldObj('Batch No')
            ,'selling_price'                => $phpExcel->getImportFldObj('Selling Price')
            ,'current_stock'                => $phpExcel->getImportFldObj('Current Stock')
            ,'adjust_stock'                 => $phpExcel->getImportFldObj('Adjust Stock')
            ,'code'                         => $phpExcel->getImportFldObj('Import')
        );

        $fa['inventory_batchwise_stock_id']['refOnly'] = true;
        $fa['title']['refOnly'] = true;
        $fa['batch_no']['refOnly'] = true;
        $fa['selling_price']['refOnly'] = true;
        $fa['current_stock']['refOnly'] = true;
        $fa['adjust_stock']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingin_inventory'
            ,'matchFieldArr'       => array('code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallbackForStock'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallbackForStock($inventory_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $inventory_batchwise_stock_id = $fa['inventory_batchwise_stock_id'];
        $title                        = $fa['title'];
        $batch_no                     = $fa['batch_no'];
        $selling_price                = $fa['selling_price'];
        $current_stock                = $fa['current_stock'];
        $adjust_stock                 = $fa['adjust_stock'];

        $SQLStock ="
        SELECT current_stock
               ,adjust_stock
               ,po_product_id
               ,product_id
               ,batch_no
        FROM inventory_batchwise_stock
        WHERE inventory_batchwise_stock_id = {$inventory_batchwise_stock_id}
        ";
        $resultStock = $db->sql_query($SQLStock);
        $rowStock    = $db->sql_fetchrow($resultStock);

        $fa = array();
        $fa['adjust_stock']  = $rowStock['adjust_stock'] + $adjust_stock;
        $fa['current_stock'] = $rowStock['current_stock'] + $adjust_stock;
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'inventory_batchwise_stock');
        $whereCondition = "WHERE inventory_batchwise_stock_id = {$inventory_batchwise_stock_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory_batchwise_stock', $whereCondition);
        $result = $db->sql_query($SQL);

        $SQLInventory = "
        SELECT inventory_id
        FROM inventory
        WHERE product_id = {$rowStock['product_id']}
        ";
        $resultInventory = $db->sql_query($SQLInventory);
        $rowInventory    = $db->sql_fetchrow($resultInventory);

        $fa2 = array();
        $fa2['product_id']    = $rowStock['product_id'];
        $fa2['batch_no']      = $rowStock['batch_no'];
        $fa2['inventory_id']  = $rowInventory['inventory_id'];
        $fa2['po_product_id'] = $rowStock['po_product_id'];
        $fa2['adjust_stock']  = $adjust_stock;
        $fa2['inventory_batchwise_stock_id'] = $inventory_batchwise_stock_id;
        $fa2    = $fn->addCreationDetailsToFieldsArray($fa2, 'adjust_stock_log');
        $SQLLog = $dbUtil->getInsertSQLStringFromArray($fa2, 'adjust_stock_log');
        $resultLog = $db->sql_query($SQLLog);
    }

    /**
     *
     */
    function getImportData1(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        $fa = array(
              'title' => $phpExcel->getImportFldObj('PRODUCT NAME')
             ,'item_code' => $phpExcel->getImportFldObj('ITEM CODE')
             ,'changed_stock' => $phpExcel->getImportFldObj('STOCK')
             ,'product_id' => $phpExcel->getImportFldObj('PRODUCT ID')
        );

        $fa['title']['refOnly'] = true;
        $fa['item_code']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingin_inventory'
            ,'matchFieldArr'       => array('product_id')
            ,'fldsArr'             => $fa
            //,'callbackAfterInsert' => 'importDataRowCallbackForStock'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallbackForStock1($inventory_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $stock = $fa['stock'];
        $item_code = $fa['item_code'];
        $title = $fa['title'];
        $inventory_code = $fa['inventory_code'];
        $code = $fa['code'];
        $color = $fa['color'];
        $size = $fa['size'];
        $model = $fa['model'];
        $product_id = $fa['product_id'];
        $inventory_id = $fa['inventory_id'];

        //$productRec  = $fn->getRecordRowByID('product', 'product_id', $product_id);
        $fa2 = array();
        $fa2['purchase_order_date']  = date('Y-m-d');
        $fa2['po_code'] = $this->getUpdatePOCode();

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'purchase_order');
        $result = $db->sql_query($SQL);
        $purchase_order_id  = $db->sql_nextid();

        $fa3 = array();
        $fa3['product_id'] = $product_id;
        //$fa3['inventory_id'] = $inventory_id;
        $fa3['purchase_order_id']  = $purchase_order_id;
        $fa3['qty']  = $stock;
        //$fa3['qty_requested']  = $stock;
        $fa3['color_size_code']  = $code;
        $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
        $result = $db->sql_query($SQL);

        if($color != ''){
            $productColorChk = $fn->getRecordByCondition('product_color', "color = '{$color}' AND product_id = '{$product_id}'");
            if($productColorChk){
                //$productColorRec = $fn->getRecordByCondition('product_color', "code = '{$code}'");
                $qty = $productColorChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_color SET qty = {$qty}
                WHERE product_color_id = {$productColorChk['product_color_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_color_id = $productColorChk['product_color_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['color'] = $color;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color');
                $result = $db->sql_query($SQL);
                $product_color_id = $db->sql_nextid();
            }
        }

        if($size != ''){
            $productSizeChk = $fn->getRecordByCondition('product_size', "size = '{$size}' AND product_id = '{$product_id}'");
            if($productSizeChk){
                //$productSizeRec = $fn->getRecordByCondition('product_size', "code = '{$code}'");
                $qty = $productSizeChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_size SET qty = {$qty}
                WHERE product_size_id = {$productSizeChk['product_size_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_size_id = $productSizeChk['product_size_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['size'] = $size;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_size');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_size');
                $result = $db->sql_query($SQL);
                $product_size_id = $db->sql_nextid();
            }

        }

        if($model != ''){
            $productModelChk = $fn->getRecordByCondition('product_model', "model = '{$model}' AND product_id = '{$product_id}'");
            if($productModelChk){
                $qty = $productModelChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_model SET qty = {$qty}
                WHERE product_model_id = {$productModelChk['product_model_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_model_id = $productModelChk['product_model_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['model'] = $model;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_model');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_model');
                $result = $db->sql_query($SQL);
                $product_model_id = $db->sql_nextid();
            }

        }

        if($color != '' && $size != ''){
            $productColorSizeChk = $fn->getRecordByCondition('product_color_by_size', "product_color_id = '{$product_color_id}' AND product_size_id = '{$product_size_id}' AND product_id = '{$product_id}'");
            if($productColorSizeChk){
                //$productColorSizeRec = $fn->getRecordByCondition('product_color_by_size', "code = '{$code}'");
                $qty = $productColorSizeChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_color_by_size SET qty = {$qty}
                WHERE product_color_by_size_id = {$productColorSizeChk['product_color_by_size_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['product_color_id'] = $product_color_id;
                $fa['product_size_id']  = $product_size_id;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color_by_size');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color_by_size');
                $result = $db->sql_query($SQL);
            }
        }
            $invRec = $fn->getRecordByCondition('inventory', "product_id = {$product_id}");
            $fa4 = array();
            $fa4['product_id'] = $product_id;
            $fa4['actual_stock'] = $invRec['actual_stock'] + $stock;
            //$fa4 = $fn->addCreationDetailsToFieldsArray($fa4, 'inventory');

            $whereCondition = "WHERE product_id = {$product_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa4, 'inventory', $whereCondition);
            $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdatePOCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $poCode = $fn->getSettingsValueByKey("poCode");

        $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'poCode'";
        $result = $db->sql_query($SQL);

        return $POCode;
    }


    /**
      *
      */
    function getBatchWiseAdjustStockUpdate() {
        $cpCfg  = Zend_Registry::get('cpCfg');
        $fn     = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db     = Zend_Registry::get('db');

        $SQLProductInventory = "
        SELECT p.product_id
              ,i.changed_stock
              ,i.inventory_id
        FROM product p
        LEFT JOIN inventory i ON (i.product_id = p.product_id)
        WHERE p.published = 1
        ";
        $resultProductInventory = $db->sql_query($SQLProductInventory);
        $count  = 1;
        $count2 = 1;
        while ($rowProductInventory = $db->sql_fetchrow($resultProductInventory)) {
            if($rowProductInventory['changed_stock'] > 0) {
                $SQLPO = "
                SELECT pop.product_id
                      ,pop.po_product_id
                      ,pop.batch_no
                      ,SUM(CASE 
                        WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                        THEN pop.qty * pop.pack_size
                        ELSE pop.qty END) AS qty
                FROM po_product pop
                LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
                WHERE po.status != 'Cancelled'
                AND pop.product_id = {$rowProductInventory['product_id']}
                GROUP BY pop.batch_no, pop.product_id
                ORDER BY pop.po_product_id DESC
                ";
                $resultPO  = $db->sql_query($SQLPO);
                $numRowsPO = $db->sql_numrows($resultPO);

                $AdjustStock = $rowProductInventory['changed_stock'];
                $count = 1;
                while($rowPO = $db->sql_fetchrow($resultPO)) {
                    if($AdjustStock != 0) {
                        if($AdjustStock > $rowPO['qty'] && $numRowsPO > $count) {
                            $fa = array();
                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $rowPO['qty'];
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = $AdjustStock - $rowPO['qty'];
                        } else if($AdjustStock > $rowPO['qty'] && $numRowsPO <= $count) {
                            $fa = array();
                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $AdjustStock;
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = 0;

                        } else if ($AdjustStock < $rowPO['qty']) {
                            $fa = array();
                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $AdjustStock;
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = 0;
                        } else {
                            $fa = array();
                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $AdjustStock;
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = 0;
                        }
                    }

                    $count++;
                }
            } else {
                $SQLPO = "
                SELECT pop.product_id
                      ,pop.po_product_id
                      ,pop.batch_no
                      ,SUM(CASE 
                        WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                        THEN pop.qty * pop.pack_size
                        ELSE pop.qty END) AS qty
                FROM po_product pop
                LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
                WHERE po.status != 'Cancelled'
                AND pop.product_id = {$rowProductInventory['product_id']}
                GROUP BY pop.batch_no, pop.product_id
                ORDER BY pop.po_product_id DESC
                ";
                $resultPO  = $db->sql_query($SQLPO);
                $numRowsPO = $db->sql_numrows($resultPO);

                $AdjustStock = $rowProductInventory['changed_stock'];
                $count2 = 1;
                while($rowPO = $db->sql_fetchrow($resultPO)) {
                    if($AdjustStock != 0) {
                        if(abs($AdjustStock) > $rowPO['qty'] && $numRowsPO > $count) {
                            $fa = array();
                            $qty = $rowPO['qty'];
                            if($qty > 0) {
                                $qty = '-'.$qty;
                            }

                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $qty;
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = $AdjustStock + $rowPO['qty'];
                        } else if(abs($AdjustStock) > $rowPO['qty'] && $numRowsPO <= $count) {
                            $fa = array();
                            $qty = $AdjustStock;
                            if($qty > 0) {
                                $qty = '-'.$qty;
                            }

                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $qty;
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = 0;

                        } else if (abs($AdjustStock) < $rowPO['qty']) {
                            $fa = array();
                            $qty = $AdjustStock;
                            if($qty > 0) {
                                $qty = '-'.$qty;
                            }

                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $qty;
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = 0;
                        } else {
                            $fa = array();
                            $qty = $AdjustStock;
                            if($qty > 0) {
                                $qty = '-'.$qty;
                            }

                            $fa['product_id']    = $rowProductInventory['product_id'];
                            $fa['batch_no']      = $rowPO['batch_no'];
                            $fa['inventory_id']  = $rowProductInventory['inventory_id'];
                            $fa['adjust_stock']  = $qty;
                            $fa['po_product_id'] = $rowPO['po_product_id'];
                            $fa  = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);

                            $AdjustStock = 0;
                        }
                    }

                    $count2++;
                }
            }
        }
    }

    /**
     *
     */
    function getCreateUpdateChangedStockRecord() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $batch_no      = $fn->getReqParam('batch_no');
        $product_id    = $fn->getReqParam('product_id');
        $inventory_id  = $fn->getReqParam('inventory_id');
        $adjust_stock  = $fn->getReqParam('adjust_stock');
        $po_product_id = $fn->getReqParam('po_product_id');

        $SQLAdjustStock = "
        SELECT adjust_stock
              ,current_stock
              ,inventory_batchwise_stock_id
        FROM inventory_batchwise_stock
        WHERE product_id = '{$product_id}'
        AND po_product_id = '{$po_product_id}'
        ";
        $resultAdjustStock  = $db->sql_query($SQLAdjustStock);
        $numRowsAdjustStock = $db->sql_numrows($resultAdjustStock);
        $rowAdjustStock = $db->sql_fetchrow($resultAdjustStock);

        if($adjust_stock < 0) {
            if($rowAdjustStock['adjust_stock'] < 0) {
                $AdjustStock = abs($adjust_stock - $rowAdjustStock['adjust_stock']);
                if($rowAdjustStock['adjust_stock'] > $adjust_stock) {
                    $AdjustStock = -$AdjustStock;
                } else {
                    $AdjustStock = $AdjustStock;
                }
            } else {
                if($rowAdjustStock['adjust_stock'] > 0) {
                    $AdjustStock = $adjust_stock - $rowAdjustStock['adjust_stock'];
                } else {
                    $AdjustStock = $adjust_stock + $rowAdjustStock['adjust_stock'];
                }
            }
        } else {
            $AdjustStock = $adjust_stock - $rowAdjustStock['adjust_stock'];
        }

        $stock = $rowAdjustStock['current_stock'] + $AdjustStock;

        $fa = array();
        $fa['adjust_stock']  = $adjust_stock;
        $fa['current_stock'] = $stock;
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'inventory_batchwise_stock');
        $whereCondition = "WHERE inventory_batchwise_stock_id = {$rowAdjustStock['inventory_batchwise_stock_id']}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory_batchwise_stock', $whereCondition);
        $result = $db->sql_query($SQL);

        $SQLUpdateProduct = "
        UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0)+{$AdjustStock}
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);
        
        $SQLUpdateInventory = "
        UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0)+{$AdjustStock}
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);

        $fa2 = array();
        $fa2['product_id']    = $product_id;
        $fa2['batch_no']      = $batch_no;
        $fa2['inventory_id']  = $inventory_id;
        $fa2['po_product_id'] = $po_product_id;
        $fa2['adjust_stock']  = $adjust_stock;
        $fa2['inventory_batchwise_stock_id'] = $rowAdjustStock['inventory_batchwise_stock_id'];
        $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'adjust_stock_log');
        $SQLLog = $dbUtil->getInsertSQLStringFromArray($fa2, 'adjust_stock_log');
        $resultLog = $db->sql_query($SQLLog);

        $invRec  = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);

        return $invRec['actual_stock'];
    }

    /**
     *
     */
    function getBatchWiseAdjustStockUpdateForMinusStock() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        
        $site_id    = $fn->getSessionParam('cp_site_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql   = '';
        $sqlAppendSt = '';
        $stockTransferSQLForMultiSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql   = "AND po.site_id = {$site_id}";
            $sqlAppendSt = "AND st.to_location = {$site_id}";            
        }

        //$product_id = 550;
        //pp.product_id = {$product_id} AND 

        $SQLProduct = "
        SELECT product_id
        FROM product
        WHERE published = 1
        ";

        $resultProduct = $db->sql_query($SQLProduct);
        $count = 1;
        while ($rowProduct = $db->sql_fetchrow($resultProduct)){

            $SQLPO ="
            SELECT p.title
                  ,p.unit
                  ,p.item_code
                  ,po.po_code AS po_code
                  ,po.purchase_order_id AS purchase_order_id
                  ,pp.cost_price
                  ,pp.pack_size
                  ,pp.selling_price
                  ,pp.qty_requested AS qty
                  ,pp.qty
                  ,pp.gst
                  ,pp.batch_no AS batch_no
                  ,pp.expiry_date
                  ,p.hsn AS hsn_code
                  ,p.product_id AS product_id
                  ,p.title AS main_product_title
                  ,p.item_code AS main_product_code
                  ,'PURCHASE ORDER' AS stock_from
                  ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
                    LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                    WHERE inItm.record_id = pp.product_id
                    AND inItm.not_add_in_stock != 1
                    AND inv.status = 'Paid'
                    AND inv.invoice_type = 'POS'
                    AND inItm.batch_no = pp.batch_no) AS Sales

                 ,(SELECT SUM(CASE 
                           WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                           THEN pop.qty * pop.pack_size
                           ELSE pop.qty END) AS Purchase 
                    FROM po_product pop
                    LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
                    WHERE po.status != 'Cancelled'
                    AND pop.batch_no = pp.batch_no
                    AND pop.product_id = pp.product_id) AS Purchase
            FROM po_product pp
            LEFT JOIN product p ON (p.product_id = pp.product_id)
            LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pp.purchase_order_id)
            WHERE pp.product_id = '{$rowProduct['product_id']}'
            AND po.status != 'Cancelled'
            {$appendSql}
            GROUP BY pp.batch_no
            ORDER BY 
            CASE
                WHEN (Purchase < Sales 
                      ) THEN 1
                ELSE pp.po_product_id
            END
            ";
            $resultPo = $db->sql_query($SQLPO);
            $numRows = $db->sql_numrows($resultPo);

            $rows = "";
            $batchwiseStock = 0;
            while ($rowPo = $db->sql_fetchrow($resultPo)){
                $stockArray   = $fn->getStockForProductBatchWise($rowPo['product_id'], '', $rowPo['batch_no']);
                $stock        = $stockArray['OverallStock'];
                $SoldQty      = $stockArray['SoldQty'];
                $PurchasedQty = $stockArray['PurchasedQty'];

                if($PurchasedQty < $SoldQty) {
                    $batchwiseStock = $PurchasedQty - $SoldQty;

                    $SQLAdjustStock = "
                    SELECT adjust_stock
                          ,inventory_adjust_stock_id
                    FROM inventory_adjust_stock
                    WHERE product_id = '{$rowPo['product_id']}'
                    AND batch_no = '{$rowPo['batch_no']}'
                    ";
                    $resultAdjustStock  = $db->sql_query($SQLAdjustStock);
                    $numRowsAdjustStock = $db->sql_numrows($resultAdjustStock);
                    $rowAdjustStock = $db->sql_fetchrow($resultAdjustStock);

                    /*$SQLProductInventory = "
                    SELECT p.product_id
                          ,i.inventory_id
                    FROM product p
                    LEFT JOIN inventory i ON (i.product_id = p.product_id)
                    WHERE p.product_id = {$rowPo['product_id']}
                    ";
                    $resultProductInventory = $db->sql_query($SQLProductInventory);
                    $rowProductInventory = $db->sql_fetchrow($resultProductInventory);

                    $fa = array();
                    $fa['product_id']   = $rowPo['product_id'];
                    $fa['batch_no']     = $rowPo['batch_no'];
                    $fa['inventory_id'] = $rowProductInventory['inventory_id'];*/

                    if($numRowsAdjustStock == 0) {
                        /*$fa['adjust_stock'] = $batchwiseStock;
                        
                        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                        $result = $db->sql_query($SQL);*/
                    } else {
                        /*if ($rowAdjustStock['adjust_stock'] < 0) {
                            $batchwiseStock = $batchwiseStock + $rowAdjustStock['adjust_stock'];
                        } else {
                            $batchwiseStock = $batchwiseStock - $rowAdjustStock['adjust_stock'];
                        }

                        $fa['adjust_stock'] = $batchwiseStock;

                        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                        $whereCondition = "WHERE inventory_adjust_stock_id = {$rowAdjustStock['inventory_adjust_stock_id']}";
                        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory_adjust_stock', $whereCondition);
                        $result = $db->sql_query($SQL);*/
                        print($count." - ".$rowAdjustStock['adjust_stock']." - ".$rowPo['product_id']." - ".$rowPo['title']."<br/>");
                        $count++;
                    }

                }
            }
        }
    }

    /**
     *
     */
    function getBatchWiseAdjustStockUpdateForStock() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        
        $site_id    = $fn->getSessionParam('cp_site_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql   = '';
        $sqlAppendSt = '';
        $stockTransferSQLForMultiSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql   = "AND po.site_id = {$site_id}";
            $sqlAppendSt = "AND st.to_location = {$site_id}";            
        }

        //$product_id = 550;
        //pp.product_id = {$product_id} AND 

        $SQLProduct = "
        SELECT product_id
        FROM product
        WHERE published = 1
        ";

        $resultProduct = $db->sql_query($SQLProduct);
        while ($rowProduct = $db->sql_fetchrow($resultProduct)){

            $SQLPO ="
            SELECT p.title
                  ,p.unit
                  ,p.item_code
                  ,po.po_code AS po_code
                  ,po.purchase_order_id AS purchase_order_id
                  ,pp.cost_price
                  ,pp.pack_size
                  ,pp.selling_price
                  ,pp.qty_requested AS qty
                  ,pp.qty
                  ,pp.gst
                  ,pp.batch_no AS batch_no
                  ,pp.expiry_date
                  ,p.hsn AS hsn_code
                  ,p.product_id AS product_id
                  ,p.title AS main_product_title
                  ,p.item_code AS main_product_code
                  ,'PURCHASE ORDER' AS stock_from
                  ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
                    LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                    WHERE inItm.record_id = pp.product_id
                    AND inItm.not_add_in_stock != 1
                    AND inv.status = 'Paid'
                    AND inv.invoice_type = 'POS'
                    AND inItm.batch_no = pp.batch_no) AS Sales

                 ,(SELECT SUM(CASE 
                           WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                           THEN pop.qty * pop.pack_size
                           ELSE pop.qty END) AS Purchase 
                    FROM po_product pop
                    LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
                    WHERE po.status != 'Cancelled'
                    AND pop.batch_no = pp.batch_no
                    AND pop.product_id = pp.product_id) AS Purchase
            FROM po_product pp
            LEFT JOIN product p ON (p.product_id = pp.product_id)
            LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pp.purchase_order_id)
            WHERE pp.product_id = '{$rowProduct['product_id']}'
            AND po.status != 'Cancelled'
            {$appendSql}
            GROUP BY pp.batch_no
            ORDER BY 
            CASE
                WHEN (Purchase < Sales 
                      ) THEN 1
                ELSE pp.po_product_id
            END
            ";
            $resultPo = $db->sql_query($SQLPO);
            $numRows = $db->sql_numrows($resultPo);

            $rows = "";
            $count = 1;
            $batchwiseStock = 0;
            while ($rowPo = $db->sql_fetchrow($resultPo)){
                $stockArray   = $fn->getStockForProductBatchWise($rowPo['product_id'], '', $rowPo['batch_no']);
                $stock        = $stockArray['OverallStock'];
                $SoldQty      = $stockArray['SoldQty'];
                $PurchasedQty = $stockArray['PurchasedQty'];

                if($PurchasedQty < $SoldQty) {
                    $batchwiseStock = $PurchasedQty - $SoldQty;
                    print("Purchase Less:".$batchwiseStock."<br/>");
                } else if($PurchasedQty >= $SoldQty) {
                    if($batchwiseStock > 0) {
                        $batchwiseStock = $stock;
                        print("Purchased Greater or Equal:".$batchwiseStock."<br/>");
                    } else {
                        $SQLAdjustStock = "
                        SELECT adjust_stock
                              ,inventory_adjust_stock_id
                        FROM inventory_adjust_stock
                        WHERE product_id = '{$rowPo['product_id']}'
                        AND batch_no = '{$rowPo['batch_no']}'
                        ";
                        $resultAdjustStock  = $db->sql_query($SQLAdjustStock);
                        $numRowsAdjustStock = $db->sql_numrows($resultAdjustStock);
                        $rowAdjustStock = $db->sql_fetchrow($resultAdjustStock);

                        $SQLProductInventory = "
                        SELECT p.product_id
                              ,i.inventory_id
                        FROM product p
                        LEFT JOIN inventory i ON (i.product_id = p.product_id)
                        WHERE p.product_id = {$rowPo['product_id']}
                        ";
                        $resultProductInventory = $db->sql_query($SQLProductInventory);
                        $rowProductInventory = $db->sql_fetchrow($resultProductInventory);

                        $fa = array();
                        $fa['product_id']   = $rowPo['product_id'];
                        $fa['batch_no']     = $rowPo['batch_no'];
                        $fa['inventory_id'] = $rowProductInventory['inventory_id'];

                        if($numRowsAdjustStock == 0) {
                            $fa['adjust_stock'] = $batchwiseStock;
                            
                            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_adjust_stock');
                            $result = $db->sql_query($SQL);
                        } else {
                            if ($rowAdjustStock['adjust_stock'] < 0) {
                                $batchwiseStock = $batchwiseStock + $rowAdjustStock['adjust_stock'];
                            } else {
                                $batchwiseStock = $batchwiseStock - $rowAdjustStock['adjust_stock'];
                            }

                            $fa['adjust_stock'] = $batchwiseStock;

                            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'inventory_adjust_stock');
                            $whereCondition = "WHERE inventory_adjust_stock_id = {$rowAdjustStock['inventory_adjust_stock_id']}";
                            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory_adjust_stock', $whereCondition);
                            $result = $db->sql_query($SQL);
                        }

                        $batchwiseStock = $batchwiseStock + $stock;
                        print("Purchased Greater or Excess:".$batchwiseStock."<br/>");
                    }
                }

            }
        }
    }

    /**
     *
     */
    function getBatchProductUpdateCurrentStock() {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        //AND product_id = 44
        
        $SQLProduct = "
        SELECT product_id
        FROM product
        WHERE published = 1
        ";

        $resultProduct = $db->sql_query($SQLProduct);
        $batchwiseStock = 0;
        $stockOverall   = 0;
        $batchwiseStockOverall = 0;
        while ($rowProduct = $db->sql_fetchrow($resultProduct)){
            $rows = "";
            $count = 1;
            $batchwiseStock = 0;
            $stockOverall   = 0;
            $batchwiseStockOverall = 0;
            $po_product_id     = "";
            $purchase_order_id = "";
            $batch_no          = "";

            $stockOverallArray = $fn->getStockForProduct($rowProduct['product_id'], '');
            $stockOverall      = $stockOverallArray['OverallStock'];
            $batchCount        = $this->getBatchProductCountCheck($rowProduct['product_id']);

            if($stockOverall > 0 && $batchCount >= 1) {
                $appendSql   = '';
                $sqlAppendSt = '';
                $stockTransferSQLForMultiSite = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSql   = "AND po.site_id = {$site_id}";
                    $sqlAppendSt = "AND st.to_location = {$site_id}";

                    $stockTransferSQLForMultiSite = "
                    UNION
                    SELECT  p.title
                           ,p.unit
                           ,p.item_code
                           ,st.stock_transfer_id AS po_code
                           ,st.stock_transfer_id AS purchase_order_id
                           ,pp.cost_price
                           ,pp.pack_size
                           ,pp.selling_price
                           ,pp.qty_requested AS qty
                           ,pp.gst
                           ,pp.batch_no AS batch_no
                           ,pp.expiry_date
                           ,p.hsn AS hsn_code
                           ,p.product_id AS product_id
                           ,p.title AS main_product_title
                           ,p.item_code AS main_product_code
                           ,'STOCK TRANSFER' AS stock_from
                    FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    LEFT JOIN po_product pp ON (pp.po_product_id = sth.po_product_id)
                    LEFT JOIN product p ON (p.product_id = pp.product_id)
                    WHERE sth.product_id = {$rowProduct['product_id']}
                      {$sqlAppendSt}
                    AND st.status = 'Delivered'        
                    GROUP BY batch_no
                    ORDER BY batch_no
                    ";
                }

                $SQLPO ="
                SELECT p.title
                      ,p.unit
                      ,p.item_code
                      ,po.po_code AS po_code
                      ,po.purchase_order_id AS purchase_order_id
                      ,pp.cost_price
                      ,pp.pack_size
                      ,pp.selling_price
                      ,pp.qty_requested AS qty
                      ,pp.qty
                      ,pp.gst
                      ,pp.batch_no AS batch_no
                      ,pp.expiry_date
                      ,pp.po_product_id
                      ,p.hsn AS hsn_code
                      ,p.product_id AS product_id
                      ,p.title AS main_product_title
                      ,p.item_code AS main_product_code
                      ,'PURCHASE ORDER' AS stock_from
                      ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
                        LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                        WHERE inItm.record_id = pp.product_id
                        AND inItm.not_add_in_stock != 1
                        AND inv.status = 'Paid'
                        AND inv.invoice_type = 'POS'
                        AND inItm.batch_no = pp.batch_no) AS Sales

                     ,(SELECT SUM(CASE 
                               WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                               THEN pop.qty * pop.pack_size
                               ELSE pop.qty END) AS Purchase 
                        FROM po_product pop
                        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
                        WHERE po.status != 'Cancelled'
                        AND pop.batch_no = pp.batch_no
                        AND pop.product_id = pp.product_id) AS Purchase
                FROM po_product pp
                LEFT JOIN product p ON (p.product_id = pp.product_id)
                LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pp.purchase_order_id)
                WHERE pp.product_id = {$rowProduct['product_id']}
                AND po.status != 'Cancelled'
                {$appendSql}
                GROUP BY pp.batch_no
                ORDER BY 
                CASE
                    WHEN (Purchase < Sales 
                          ) THEN 1
                    ELSE pp.po_product_id
                END
                {$stockTransferSQLForMultiSite}
                ";
                $resultPo = $db->sql_query($SQLPO);
                $numRows = $db->sql_numrows($resultPo);
                while ($rowPo = $db->sql_fetchrow($resultPo)){
                    $selling_price = $rowPo['selling_price'];
                    if($selling_price == ""){
                        $selling_price = 0;
                    }

                    $stockArray   = $fn->getStockForProductBatchWise($rowPo['product_id'], '', $rowPo['batch_no']);
                    $stock        = $stockArray['OverallStock'];
                    $SoldQty      = $stockArray['SoldQty'];
                    $PurchasedQty = $stockArray['PurchasedQty'];
                    $AdjustStock  = $stockArray['AdjustStock'];

                    if($PurchasedQty < $SoldQty) {
                        $batchwiseStock = $PurchasedQty - $SoldQty;
                    } else if($PurchasedQty >= $SoldQty) {
                        if($batchwiseStock > 0) {
                            $batchwiseStock = $stock;
                        } else {
                            $batchwiseStock = $batchwiseStock + $stock;
                        }
                    }

                    //print($rowPo['batch_no']." - ".$rowPo['Purchase']." - ".$rowPo['Sales']." - ".$batchwiseStock." - ".$stock."<br/>");                
                    if($batchwiseStock > 0 || $count == $numRows) {
                        if($batchwiseStock > 0) {
                            $fa = array();
                            $fa['product_id']         = $rowPo['product_id'];
                            $fa['po_product_id']      = $rowPo['po_product_id'];
                            $fa['purchase_order_id']  = $rowPo['purchase_order_id'];
                            $fa['batch_no']           = $rowPo['batch_no'];
                            $fa['current_stock']      = $batchwiseStock;

                            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_batchwise_stock');
                            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_batchwise_stock');
                            $result = $db->sql_query($SQL);
                            print("Id: ".$rowPo['product_id']." / Name: ".$rowPo['title']." / Batch Wise Stock: ".$batchwiseStock."<br/>");
                            $batchwiseStockOverall += $batchwiseStock;
                        }
                    }

                    $count++;
                    $po_product_id     = $rowPo['po_product_id'];
                    $purchase_order_id = $rowPo['purchase_order_id'];
                    $batch_no          = $rowPo['batch_no'];
                }
            }

            //print($batchwiseStockOverall."<br/>");
            //print($stockOverall."<br/>");
            
            if($batchwiseStockOverall <= 0) {
                $SQLStock ="
                SELECT current_stock
                FROM inventory_batchwise_stock
                WHERE product_id = {$rowProduct['product_id']}
                ";
                $resultStock  = $db->sql_query($SQLStock);
                $numRowsStock = $db->sql_numrows($resultStock);

                if ($stockOverall > 0 && $numRowsStock == 0) {
                    $SQLPOCheck  = "
                    SELECT  pp.po_product_id
                           ,pp.purchase_order_id
                           ,pp.batch_no
                    FROM po_product pp
                    LEFT JOIN purchase_order po ON (po.purchase_order_id = pp.purchase_order_id)
                    WHERE pp.product_id = {$rowProduct['product_id']}
                    AND po.status != 'Cancelled'
                    ORDER BY pp.po_product_id DESC
                    ";
                    $resultPOCheck = $db->sql_query($SQLPOCheck);
                    $rowPOCheck    = $db->sql_fetchrow($resultPOCheck);

                    $fa = array();
                    $fa['product_id']         = $rowProduct['product_id'];
                    $fa['po_product_id']      = $rowPOCheck['po_product_id'];
                    $fa['purchase_order_id']  = $rowPOCheck['purchase_order_id'];
                    $fa['batch_no']           = $rowPOCheck['batch_no'];
                    $fa['current_stock']      = $stockOverall;

                    $fa = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_batchwise_stock');
                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_batchwise_stock');
                    $result = $db->sql_query($SQL);
                    print("OverallStock Stock: ".$stockOverall."<br/>");
                }
            } 

            if ($batchwiseStockOverall != $stockOverall) {
                if ($stockOverall > 0) {
                    $SQLStock ="
                    DELETE FROM inventory_batchwise_stock
                    WHERE product_id = {$rowProduct['product_id']}
                    ";
                    $resultStock  = $db->sql_query($SQLStock);

                    $SQLPOCheck  = "
                    SELECT  pp.po_product_id
                           ,pp.purchase_order_id
                           ,pp.batch_no
                    FROM po_product pp
                    LEFT JOIN purchase_order po ON (po.purchase_order_id = pp.purchase_order_id)
                    WHERE pp.product_id = {$rowProduct['product_id']}
                    AND po.status != 'Cancelled'
                    ORDER BY pp.po_product_id DESC
                    ";
                    $resultPOCheck = $db->sql_query($SQLPOCheck);
                    $rowPOCheck    = $db->sql_fetchrow($resultPOCheck);

                    $fa = array();
                    $fa['product_id']         = $rowProduct['product_id'];
                    $fa['po_product_id']      = $rowPOCheck['po_product_id'];
                    $fa['purchase_order_id']  = $rowPOCheck['purchase_order_id'];
                    $fa['batch_no']           = $rowPOCheck['batch_no'];
                    $fa['current_stock']      = $stockOverall;

                    $fa = $fn->addCreationDetailsToFieldsArray($fa, 'inventory_batchwise_stock');
                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'inventory_batchwise_stock');
                    $result = $db->sql_query($SQL);
                    print("OverallStock Stock Not Matched: ".$stockOverall."<br/>");
                }
            }
        }
    }   

    /**
     *
     */
    function getBatchProductCountCheck($product_id){
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $site_id    = $fn->getSessionParam('cp_site_id');

        $thForSiteId  = "";
        $tdForSiteId  = "";
        $thForSiteId  = "";
        $rows         = "";
        $sqlAppend    = "";
        $sqlAppendSt  = "";
        $appendSqlStk2 = "";
        $stockTransferSQLForMultiSite = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $sqlAppend = "AND po.site_id = {$site_id}";
            $sqlAppendSt = "AND st.to_location = {$site_id}";

            $stockTransferSQLForMultiSite = "
            UNION
            SELECT  sth.batch_no AS batch_no
                   ,sth.product_id
                   ,pp.expiry_date
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            LEFT JOIN po_product pp ON (pp.po_product_id = sth.po_product_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
              {$sqlAppendSt}
            GROUP BY batch_no
            ";
            
            $SQLsitedetail="
            SELECT site_id
                   ,title
            FROM site
            WHERE site_id = {$site_id}
            ";
            $resultsitedetail = $db->sql_query($SQLsitedetail);
            $rowsitedetail = $db->sql_fetchrow($resultsitedetail);
        }

        $SQLBatchNo = "
        SELECT  pp.batch_no AS batch_no
               ,pp.product_id
               ,pp.expiry_date
        FROM po_product pp
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pp.purchase_order_id)
        LEFT JOIN product p ON (p.product_id = pp.product_id)
        WHERE pp.product_id = {$product_id}
          {$sqlAppend}
        AND po.status != 'Cancelled'
        GROUP BY pp.batch_no
        {$stockTransferSQLForMultiSite}
        ";
        $resultBatchNo  = $db->sql_query($SQLBatchNo);
        $numRowsBatchNo = $db->sql_numrows($resultBatchNo);

        $count = 0;
        while ($rowBatchNo    = $db->sql_fetchrow($resultBatchNo)) {
            $stockArray = $fn->getStockForProductBatchWise($rowBatchNo['product_id'], '', $rowBatchNo['batch_no']);
            $stock      = $stockArray['OverallStock'];

            if($stock > 0) {
                $count++;
            }
        }

        return $count;
    } 

    /**
     *
     */
    function getUpdatePurchaseQtyInBatchwiseStock() {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQLBatchWiseStock = "
        SELECT po_product_id
              ,product_id
              ,current_stock
              ,sales_return_qty
              ,sales_qty
              ,purchase_order_id
              ,inventory_batchwise_stock_id
        FROM inventory_batchwise_stock
        ";
        $resultBatchWiseStock  = $db->sql_query($SQLBatchWiseStock);
        $numRowsBatchWiseStock = $db->sql_numrows($resultBatchWiseStock);
        
        while($BatchWiseStock = $db->sql_fetchrow($resultBatchWiseStock)) {
            $SQLPO = "
            SELECT pop.product_id
                  ,pop.po_product_id
                  ,pop.batch_no
                  ,(CASE 
                    WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                    THEN pop.qty * pop.pack_size
                    ELSE pop.qty END) AS qty
            FROM po_product pop
            WHERE pop.po_product_id = {$BatchWiseStock['po_product_id']}
            AND pop.product_id = {$BatchWiseStock['product_id']}
            AND pop.purchase_order_id = {$BatchWiseStock['purchase_order_id']}
            ";
            $resultPO  = $db->sql_query($SQLPO);
            $numRowsPO = $db->sql_numrows($resultPO);
            $rowPO     = $db->sql_fetchrow($resultPO);

            $fa = array();
            $fa['purchased_qty'] = $rowPO['qty'];
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'inventory_batchwise_stock');
            $whereCondition = "WHERE inventory_batchwise_stock_id = {$BatchWiseStock['inventory_batchwise_stock_id']}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory_batchwise_stock', $whereCondition);
            $result = $db->sql_query($SQL);
        }

    }

    /**
     *
     */
    function getUpdateStockInInventoryAndProductLog() {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $site_id    = $fn->getSessionParam('cp_site_id');
        $product_id = $fn->getReqParam('product_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlProduct = "";
        if($product_id != "") {
            $appendSqlProduct = " WHERE product_id = '{$product_id}'";
        }

        print$SQLBatchWiseStock = "
        SELECT product_id
              ,SUM(current_stock) AS current_stock
        FROM inventory_batchwise_stock
        {$appendSqlProduct}
        GROUP BY product_id
        ";
        $resultBatchWiseStock  = $db->sql_query($SQLBatchWiseStock);
        $numRowsBatchWiseStock = $db->sql_numrows($resultBatchWiseStock);
        print('<br/>');

        while($BatchWiseStock = $db->sql_fetchrow($resultBatchWiseStock)) {
            print$SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = '{$BatchWiseStock['current_stock']}'
            WHERE product_id = '{$BatchWiseStock['product_id']}'
            ";
            //$resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);
            print('<br/>');

            print$SQLUpdateInventory = "
            UPDATE inventory SET actual_stock = '{$BatchWiseStock['current_stock']}'
            WHERE product_id = '{$BatchWiseStock['product_id']}'
            ";
            //$resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
            print('<br/>');
        }

    }

    /**
     *
     */
    function getUpdateStockInInventoryAndProduct() {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $site_id    = $fn->getSessionParam('cp_site_id');
        $product_id = $fn->getReqParam('product_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlProduct = "";
        if($product_id != "") {
            $appendSqlProduct = " WHERE product_id = '{$product_id}'";
        }

        print$SQLBatchWiseStock = "
        SELECT product_id
              ,SUM(current_stock) AS current_stock
        FROM inventory_batchwise_stock
        {$appendSqlProduct}
        GROUP BY product_id
        ";
        $resultBatchWiseStock  = $db->sql_query($SQLBatchWiseStock);
        $numRowsBatchWiseStock = $db->sql_numrows($resultBatchWiseStock);
        print('<br/>');

        while($BatchWiseStock = $db->sql_fetchrow($resultBatchWiseStock)) {
            print$SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = '{$BatchWiseStock['current_stock']}'
            WHERE product_id = '{$BatchWiseStock['product_id']}'
            ";
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);
            print('<br/>');

            print$SQLUpdateInventory = "
            UPDATE inventory SET actual_stock = '{$BatchWiseStock['current_stock']}'
            WHERE product_id = '{$BatchWiseStock['product_id']}'
            ";
            $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
            print('<br/>');
        }

    }

}   
