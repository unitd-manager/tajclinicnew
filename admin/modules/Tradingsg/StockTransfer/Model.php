<?
class CPL_Admin_Modules_Tradingsg_StockTransfer_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() { 
        $fn = Zend_Registry::get('fn');   
        
        $SQL ="
        SELECT st.*
        ,s.title AS location_name     
        FROM stock_transfer st
        LEFT JOIN internal_location s ON (s.internal_location_id = st.to_location)
        ";

        return $SQL;

    }

    /**
     *
     */

    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $date1   = $fn->getReqParam('date_1');
        $date2   = $fn->getReqParam('date_2');
        $to_location         = $fn->getReqParam('to_location');
        $from_location         = $fn->getReqParam('from_location');

        $searchVar->mainTableAlias = 'st';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "st.stock_transfer_id = {$tv['record_id']}";
        } else{
            if ($date1 != "" && $date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(st.date BETWEEN '{$date1}' AND '{$date2}')";
            }
            if ($from_location != "" ) {
                $searchVar->sqlSearchVar[] = "(st.from_location = '{$from_location}')";
            }
            if ($to_location != "" ) {
                $searchVar->sqlSearchVar[] = "(st.to_location = '{$to_location}')";
            }
            $searchVar->sqlSearchVar[] = "(st.status != 'Cancelled')";
            $searchVar->sortOrder = "st.stock_transfer_id DESC";
        }

    }

    /**
     *
     */
    function getUpdateOrderLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $arr['msg'] = '';

        $product_id        = $fn->getReqParam('product_id');
        $from_location     = $fn->getReqParam('from_location');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        $SQLCheck  = "
        SELECT product_id
        FROM stock_transfer_history
        WHERE product_id = {$product_id}
        AND stock_transfer_id = {$stock_transfer_id}
        ";

        $resultCheck = $db->sql_query($SQLCheck);
        $numRows     = $db->sql_numrows($resultCheck);
        
        if($numRows >= 1){
            $arr['msg'] = "Please note the product is already added";
            return $cpUtil->getJsonFromArray($arr);
            exit();
        }

        if($from_location == "1") {
            $stockField = "current_stock";
        } else {
            $sqlLocation = "
            SELECT internal_location_id
                  ,title 
            FROM internal_location 
            WHERE internal_location_id = {$from_location}
            ";
            $resultLocation = $db->sql_query($sqlLocation);
            $rowLocation    = $db->sql_fetchrow($resultLocation);
            $toLocation     = strtolower($rowLocation['title']);
            $toLocation     = str_replace(' ', '_', $toLocation);
            $stockField     = "{$toLocation}";
        }
        
        $SQLPO = "
        SELECT pop.pack_size
              ,pop.expiry_date
              ,ibs.po_product_id
              ,ibs.{$stockField}
              ,(CASE 
                WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                THEN FLOOR(ibs.{$stockField} / pop.pack_size)
                ELSE ibs.{$stockField} END) AS stock
              ,ibs.batch_no
        FROM inventory_batchwise_stock ibs
        LEFT JOIN (po_product pop) ON (pop.po_product_id = ibs.po_product_id)
        WHERE ibs.product_id = '{$product_id}'
        HAVING stock > 0
        ";

        $resultPO = $db->sql_query($SQLPO);
        $rowPO    = $db->sql_fetchrow($resultPO);

        $fa = array();
        $fa['stock_transfer_id'] = $stock_transfer_id;
        $fa['product_id']        = $product_id;
        $fa['batch_no']          = $rowPO['batch_no'];
        $fa['po_product_id']     = $rowPO['po_product_id'];
        $fa['pack_size']         = $rowPO['pack_size'];
        $fa['created_by']        = $fn->getSessionParam('userName');
        $fa['creation_date']     = date("Y-m-d H:i:s");
        
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_transfer_history');
        $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateOrderLineItems1() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $arr['msg'] = '';

        $product_id = $fn->getReqParam('product_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        $SQLCheck  = "
        SELECT product_id
        FROM stock_transfer_history
        WHERE product_id = {$product_id}
        AND stock_transfer_id = {$stock_transfer_id}
        ";

        $resultCheck = $db->sql_query($SQLCheck);
        $numRows     = $db->sql_numrows($resultCheck);
        
        if($numRows >= 1){
            $arr['msg'] = "Please note the product is already added";
            return $cpUtil->getJsonFromArray($arr);
            exit();
        }

        $sqlPoProduct = "
        SELECT pack_size
              ,batch_no
        FROM po_product
        WHERE product_id = {$product_id}
        ORDER BY po_product_id DESC
        ";
        $resultPoProduct = $db->sql_query($sqlPoProduct);
        $rowPoProduct    = $db->sql_fetchrow($resultPoProduct);

        $fa = array();
        $fa['stock_transfer_id'] = $stock_transfer_id;
        $fa['product_id']        = $product_id;
        //$fa['qty']               = 0;
        $fa['batch_no']          = $rowPoProduct['batch_no'];
        $fa['pack_size']         = $rowPoProduct['pack_size'];
        $fa['created_by']        = $fn->getSessionParam('userName');
        $fa['creation_date']     = date("Y-m-d H:i:s");
        
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_transfer_history');
        $db->sql_query($SQL);
    }
     /**
     *
     */
     function getUpdateStockTransferMod(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");

        $SQLtransmod    = "
        UPDATE stock_transfer
        set modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

     }

    /**
     *
     */
    function getUpdateQtyOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $stock_transfer_history_qty = $fn->getReqParam('stock_transfer_history_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $qty         = $fn->getReqParam('qty');
        $request_qty = $fn->getReqParam('request_qty');

        $OrderItems = $this->getUpdateStockTransferMod();

        $SQL    = "
        UPDATE stock_transfer_history
        set qty = {$qty} ,modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE stock_transfer_history_id = {$stock_transfer_history_qty}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateRequestQtyOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $stock_transfer_history_qty = $fn->getReqParam('stock_transfer_history_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $request_qty = $fn->getReqParam('request_qty');

        $OrderItems = $this->getUpdateStockTransferMod();

        $SQL    = "
        UPDATE stock_transfer_history
        set qty_requested = {$request_qty} ,modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE stock_transfer_history_id = {$stock_transfer_history_qty}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateCompleteTransactionProduct() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        $SQL    = "
        UPDATE stock_transfer_history
        SET lock_record = 1
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result = $db->sql_query($SQL);

        $SQLtransmod    = "
        UPDATE stock_transfer
        SET status = 'On Hold' 
           ,lock_record = 1 
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

    }

    /**
     *
     */
    function getRollbackCompleteTransactionProduct() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        $SQL    = "
        UPDATE stock_transfer_history
        SET lock_record = 0
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result = $db->sql_query($SQL);

        $SQLtransmod    = "
        UPDATE stock_transfer
        SET status = 'Request' 
           ,lock_record = 0 
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

    }

    /**
     *
     */
     function getUpdateDeductStockProduct(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $cpSiteIdSession   = $fn->getSessionParam('cp_site_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $modified_by       = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");

        $SQLtransmod = "
        UPDATE stock_transfer
        SET status = 'Delivered' 
           ,stock_deducted = 1 
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

        $SQL = "
        UPDATE stock_transfer_history
        SET stock_deducted = 1
           ,qty = qty_requested 
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result = $db->sql_query($SQL);

        $SQLStockTrans = "
        SELECT sth.product_id
              ,sth.qty
              ,st.from_location
              ,st.to_location
              ,po.pack_size
              ,sth.po_product_id
        FROM stock_transfer_history sth
        LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
        LEFT JOIN po_product po ON (po.po_product_id = sth.po_product_id)
        WHERE sth.stock_transfer_id = {$stock_transfer_id}
        ";
        $resultStockTrans  = $db->sql_query($SQLStockTrans);
        while($StockTrans = $db->sql_fetchrow($resultStockTrans)){
            if($StockTrans['from_location'] == 1) {
                $SQLStock ="
                SELECT current_stock
                      ,stock_transfer_internal_in
                      ,stock_transfer_internal_out
                FROM inventory_batchwise_stock
                WHERE po_product_id = {$StockTrans['po_product_id']}
                ";
                $resultStock = $db->sql_query($SQLStock);
                $rowStock    = $db->sql_fetchrow($resultStock);

                if($StockTrans['pack_size'] != '' && is_numeric($StockTrans['pack_size']) > 0){
                    $qty = $StockTrans['qty'] * $StockTrans['pack_size'];
                } else {
                    $qty = $StockTrans['qty'];
                }

                $totalqtyOut = $rowStock['stock_transfer_internal_out'] + $qty;

                $sqlToLocation = "
                SELECT internal_location_id
                      ,title 
                FROM internal_location 
                WHERE internal_location_id = {$StockTrans['to_location']}
                ";
                $resultToLocation = $db->sql_query($sqlToLocation);
                $rowToLocation    = $db->sql_fetchrow($resultToLocation);
                $toLocation       = strtolower($rowToLocation['title']);
                $toLocation       = str_replace(' ', '_', $toLocation);
                
                $SQLStockTo = "
                SELECT {$toLocation} AS Stock_To
                FROM inventory
                WHERE product_id = {$StockTrans['product_id']}
                ";
                $resultStockTo = $db->sql_query($SQLStockTo);
                $rowStockTo    = $db->sql_fetchrow($resultStockTo);

                $SQLBatchStockTo = "
                SELECT {$toLocation} AS Stock_To
                FROM inventory_batchwise_stock
                WHERE product_id = {$StockTrans['product_id']}
                AND po_product_id = {$StockTrans['po_product_id']}
                ";
                $resultBatchStockTo = $db->sql_query($SQLBatchStockTo);
                $rowBatchStockTo    = $db->sql_fetchrow($resultBatchStockTo);

                $totalqtyto = $rowStockTo['Stock_To'] + $qty;
                $totalbatchqtyto = $rowBatchStockTo['Stock_To'] + $qty;

                $SQLUpdateInventory1 = "
                UPDATE inventory SET {$toLocation} = {$totalqtyto}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateInventory1 = $db->sql_query($SQLUpdateInventory1);

                $SQLUpdateProduct = "
                UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0) - {$qty}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                $SQLUpdateInventory = "
                UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0) - {$qty}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);

                $SQLUpdateStock = "
                UPDATE inventory_batchwise_stock SET current_stock = IFNULL(current_stock, 0) - {$qty}
                      ,stock_transfer_internal_out = {$totalqtyOut}
                      ,{$toLocation} = {$totalbatchqtyto}
                WHERE product_id  = '{$StockTrans['product_id']}'
                AND po_product_id = '{$StockTrans['po_product_id']}'
                ";
                $resultUpdateStock  = $db->sql_query($SQLUpdateStock);
            } 

            if($StockTrans['to_location'] == 1) {
                $SQLStock ="
                SELECT current_stock
                      ,stock_transfer_internal_in
                      ,stock_transfer_internal_out
                FROM inventory_batchwise_stock
                WHERE po_product_id = {$StockTrans['po_product_id']}
                ";
                $resultStock = $db->sql_query($SQLStock);
                $rowStock    = $db->sql_fetchrow($resultStock);

                if($StockTrans['pack_size'] != '' && is_numeric($StockTrans['pack_size']) > 0){
                    $qty = $StockTrans['qty'] * $StockTrans['pack_size'];
                } else {
                    $qty = $StockTrans['qty'];
                }

                $totalqtyOut = $rowStock['stock_transfer_internal_in'] + $qty;

                $sqlLocation = "
                SELECT internal_location_id
                      ,title 
                FROM internal_location 
                WHERE internal_location_id = {$StockTrans['from_location']}
                ";
                $resultLocation = $db->sql_query($sqlLocation);
                $rowLocation    = $db->sql_fetchrow($resultLocation);
                $fromLocation   = strtolower($rowLocation['title']);
                $fromLocation   = str_replace(' ', '_', $fromLocation);
                
                $SQLStockFrom = "
                SELECT {$fromLocation} AS Stock_From
                FROM inventory
                WHERE product_id = {$StockTrans['product_id']}
                ";
                $resultStockFrom = $db->sql_query($SQLStockFrom);
                $rowStockFrom    = $db->sql_fetchrow($resultStockFrom);

                $SQLBatchStock = "
                SELECT {$fromLocation} AS Stock_From
                FROM inventory_batchwise_stock
                WHERE product_id  = {$StockTrans['product_id']}
                AND po_product_id = {$StockTrans['po_product_id']}
                ";
                $resultBatchStock = $db->sql_query($SQLBatchStock);
                $rowBatchStock    = $db->sql_fetchrow($resultBatchStock);
                
                $totalqty      = $rowStockFrom['Stock_From'] - $qty;
                $totalbatchqty = $rowBatchStock['Stock_From'] - $qty;
                
                $SQLUpdateInventory1 = "
                UPDATE inventory SET {$fromLocation} = {$totalqty}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateInventory1 = $db->sql_query($SQLUpdateInventory1);

                $SQLUpdateProduct = "
                UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0) + {$qty}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                $SQLUpdateInventory = "
                UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0) + {$qty}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);

                $SQLUpdateStock = "
                UPDATE inventory_batchwise_stock SET current_stock = IFNULL(current_stock, 0) + {$qty}
                      ,stock_transfer_internal_in = {$totalqtyOut}
                      ,{$fromLocation} = {$totalbatchqty}
                WHERE product_id  = '{$StockTrans['product_id']}'
                AND po_product_id = '{$StockTrans['po_product_id']}'
                ";
                $resultUpdateStock  = $db->sql_query($SQLUpdateStock);
            } 

            if($StockTrans['from_location'] != 1 && $StockTrans['to_location'] != 1) {
                if($StockTrans['pack_size'] != '' && is_numeric($StockTrans['pack_size']) > 0){
                    $qty = $StockTrans['qty'] * $StockTrans['pack_size'];
                } else {
                    $qty = $StockTrans['qty'];
                }

                $sqlLocation = "
                SELECT internal_location_id
                      ,title 
                FROM internal_location 
                WHERE internal_location_id = {$StockTrans['from_location']}
                ";
                $resultLocation = $db->sql_query($sqlLocation);
                $rowLocation    = $db->sql_fetchrow($resultLocation);
                $fromLocation   = strtolower($rowLocation['title']);
                $fromLocation   = str_replace(' ', '_', $fromLocation);

                $sqlToLocation = "
                SELECT internal_location_id
                      ,title 
                FROM internal_location 
                WHERE internal_location_id = {$StockTrans['to_location']}
                ";
                $resultToLocation = $db->sql_query($sqlToLocation);
                $rowToLocation    = $db->sql_fetchrow($resultToLocation);
                $toLocation       = strtolower($rowToLocation['title']);
                $toLocation       = str_replace(' ', '_', $toLocation);

                $SQLBatchStock = "
                SELECT {$fromLocation} AS Stock_From
                FROM inventory_batchwise_stock
                WHERE product_id  = {$StockTrans['product_id']}
                AND po_product_id = {$StockTrans['po_product_id']}
                ";
                $resultBatchStock = $db->sql_query($SQLBatchStock);
                $rowBatchStock    = $db->sql_fetchrow($resultBatchStock);
                
                $SQLBatchStockTo = "
                SELECT {$toLocation} AS Stock_To
                FROM inventory_batchwise_stock
                WHERE product_id  = {$StockTrans['product_id']}
                AND po_product_id = {$StockTrans['po_product_id']}
                ";
                $resultBatchStockTo = $db->sql_query($SQLBatchStockTo);
                $rowBatchStockTo    = $db->sql_fetchrow($resultBatchStockTo);

                $totalbatchqty   = $rowBatchStock['Stock_From'] - $qty;
                $totalbatchqtyto = $rowBatchStockTo['Stock_To'] + $qty;

                $SQLStockFrom = "
                SELECT {$fromLocation} AS Stock_From
                FROM inventory
                WHERE product_id = {$StockTrans['product_id']}
                ";
                $resultStockFrom = $db->sql_query($SQLStockFrom);
                $rowStockFrom    = $db->sql_fetchrow($resultStockFrom);

                $SQLStockTo = "
                SELECT {$toLocation} AS Stock_To
                FROM inventory
                WHERE product_id = {$StockTrans['product_id']}
                ";
                $resultStockTo = $db->sql_query($SQLStockTo);
                $rowStockTo    = $db->sql_fetchrow($resultStockTo);

                $totalqty   = $rowStockFrom['Stock_From'] - $qty;
                $totalqtyto = $rowStockTo['Stock_To'] + $qty;

                $SQLUpdateInventory = "
                UPDATE inventory SET {$fromLocation} = {$totalqty}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateInventory = $db->sql_query($SQLUpdateInventory);

                $SQLUpdateInventory1 = "
                UPDATE inventory SET {$toLocation} = {$totalqtyto}
                WHERE product_id = '{$StockTrans['product_id']}'
                ";
                $resultUpdateInventory1 = $db->sql_query($SQLUpdateInventory1);

                $SQLUpdateStock = "
                UPDATE inventory_batchwise_stock SET {$fromLocation} = {$totalbatchqty}
                       ,{$toLocation} = {$totalbatchqtyto}
                WHERE product_id  = '{$StockTrans['product_id']}'
                AND po_product_id = '{$StockTrans['po_product_id']}'
                ";
                $resultUpdateStock  = $db->sql_query($SQLUpdateStock);
            }

            $to_location   = $StockTrans['to_location'];
            $from_location = $StockTrans['from_location'];
        }

        $SQLUpdateSth = "
        UPDATE stock_transfer_history
        SET  to_location   = {$to_location}
            ,from_location = {$from_location}
            ,modified_by   = '{$modified_by}'
            ,modification_date  = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $resultUpdateSth = $db->sql_query($SQLUpdateSth);

     }

    /**
     *
     */
     function getUpdateStatusStockTransfer(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $modified_by       = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $product_status    = $fn->getReqParam('product_status');

        $SQLtransmod    = "
        UPDATE stock_transfer
        SET status = '{$product_status}' 
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

     }

    /**
     *
     */
    function getSearchProductTitle() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $from_location = $fn->getReqParam('from_location');

        $extractor = explode(" **** ", $title);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $productTitle = $extractor[0];

        $siteId = $fn->getSessionParam('cp_site_id');
        $appendSqlMSSite = '';
        $leftJoinMSite   = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlMSSite = "AND ms.site_id = {$siteId}";
            $leftJoinMSite   = "LEFT JOIN medicine_site ms ON (ms.product_id = p.product_id)";
        }

        $leftJoinMedicineSite = "";
        $appendSqlStk   = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $appendSqlStk = "AND ibs.site_id = '{$siteId}'";
            $leftJoinMedicineSite = "LEFT JOIN (medicine_site ms) ON (i.product_id = ms.product_id)";
        }

        if($from_location == "1") {
            $stockField = "current_stock";
        } else {
            $sqlLocation = "
            SELECT internal_location_id
                  ,title 
            FROM internal_location 
            WHERE internal_location_id = {$from_location}
            ";
            $resultLocation = $db->sql_query($sqlLocation);
            $rowLocation    = $db->sql_fetchrow($resultLocation);
            $toLocation     = strtolower($rowLocation['title']);
            $toLocation     = str_replace(' ', '_', $toLocation);
            $stockField     = "{$toLocation}";
        }

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' ', p.title, CONCAT('(',
              (SELECT SUM(CASE 
                      WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                      THEN FLOOR(ibs.{$stockField} / pp.pack_size)
                      ELSE ibs.{$stockField} END) AS stock
               FROM inventory_batchwise_stock ibs
               LEFT JOIN po_product pp ON (pp.po_product_id = ibs.po_product_id)
               WHERE ibs.product_id = p.product_id
               AND ibs.{$stockField} > 0
               {$appendSqlStk})
              , ')')) AS label
              ,(SELECT SUM(CASE 
                       WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                       THEN FLOOR(ibs.{$stockField} / pp.pack_size)
                       ELSE ibs.{$stockField} END) AS stock
                FROM inventory_batchwise_stock ibs
                LEFT JOIN po_product pp ON (pp.po_product_id = ibs.po_product_id)
                WHERE ibs.product_id = p.product_id
                AND ibs.{$stockField} > 0
                {$appendSqlStk}) AS stock
        FROM inventory i
        LEFT JOIN product p ON (p.product_id = i.product_id)
        {$leftJoinMSite}
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%')
        {$appendSqlMSSite}
        AND p.published = 1
        HAVING stock > 0
        ORDER BY p.title
        ";

        /*} else {
            $sqlLocation = "
            SELECT internal_location_id
                  ,title 
            FROM internal_location 
            WHERE internal_location_id = {$from_location}
            ";
            $resultLocation = $db->sql_query($sqlLocation);
            $rowLocation    = $db->sql_fetchrow($resultLocation);
            $toLocation     = strtolower($rowLocation['title']);
            $toLocation     = str_replace(' ', '_', $toLocation);

            $SQL = "
            SELECT p.title AS value
                  ,p.title AS label
                  ,p.product_id AS id
                  ,CONCAT_WS(' ', p.title, CONCAT('(', {$toLocation}, ')')) AS label
                  ,{$toLocation} AS stock
            FROM inventory i
            LEFT JOIN product p ON (p.product_id = i.product_id)
            {$leftJoinMSite}
            WHERE (p.title LIKE '%{$productTitle}%'
            OR p.item_code LIKE '%{$productTitle}%')
            {$appendSqlMSSite}
            AND p.published = 1
            HAVING stock > 0
            ORDER BY p.title
            ";
        }*/

        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        
        return $arr;
    }

    /**
    *
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('to_location', 'Please Select to location');

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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $current_date = date('Y-m-d');
        $stock_transfer_code = $fn->getSettingsValueByKey("nextStockTransferCode");

        $fa = $this->getFields();
        $fa['date']    = $current_date;
        $fa['status']  = 'Request';
        //$fa['stock_transfer_code'] = $stock_transfer_code;
        $to_location   = $fn->getRecordRowByID('internal_location', 'internal_location_id', $fa['to_location']);
        $fa['title']   = 'Transfer to '.$to_location['title'];
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $id = $fn->addRecord($fa);
        //To update expense code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextStockTransferCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('status', 'Please select status');

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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $fa = array();
        //$fa = $fn->addToFieldsArray($fa, 'stock_transfer_id');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'to_location');
        $fa = $fn->addToFieldsArray($fa, 'from_location');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        return $fa;
    }
    /**
     *
     */
    function getDeleteItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $stock_transfer_historyid    = $fn->getReqParam('stock_transfer_history_id');

        $deleteSQL    = "
        DELETE FROM stock_transfer_history
        WHERE stock_transfer_history_id = '{$stock_transfer_historyid}'
        ";
        $result = $db->sql_query($deleteSQL);

        return $deleteSQL;
    }

    /**
     *
     */
    function getPrintExportAsPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('HMS');
        $pdf->SetSubject('Internal Transfer');
        $pdf->SetTitle('Internal Transfer');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->SetFont('Courier','',10);
        $pdf->AddPage();

        $stockTransfer_id = $fn->getReqParam('id');
        $printType        = $fn->getReqParam('printType');

        $SQL = "
        SELECT st.date
               ,st.from_location
               ,st.to_location
               ,sth.product_id
               ,sth.qty
               ,sth.qty_requested
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sth ON (sth.stock_transfer_id = st.stock_transfer_id)
        WHERE st.stock_transfer_id = '{$stockTransfer_id}'
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $row2    = $db->sql_fetchrow($result);

        $from_location = $fn->getRecordRowByID('internal_location', 'internal_location_id', $row2['from_location']);
        $to_location   = $fn->getRecordRowByID('internal_location', 'internal_location_id', $row2['to_location']);
        $tblHeadingText = '';

        if($printType == 'request'){
            $tblHeadingText = 'REQUEST FORM';
        }
        else{
            $tblHeadingText = 'DELIVERY ORDER';
        }

        $tblHeading = '
        <table border="0" width="100%" cellpadding="5">
            <tr>
                <td border="0" align="center" height="30"><font style="font-size:20px; font-weight:bold">'.$tblHeadingText.'</font>
                </td>
            </tr>
        </table>
        ';

        $stock_transfer_date = $fn->getCPDate($row2['date'],"d-m-Y");

        $tblFromTo = '
        <table border = "1" width = "100%" cellpadding="5">
            <tr bgcolor="#FDCA9C" style="font-weight:bold;">
                <th>Date</th>
                <th>Transfer From</th>
                <th>Transfer To</th>
            </tr>
            <tr>
                <td>'.$stock_transfer_date.'</td>
                <td>'.$from_location['title'].'</td>
                <td>'.$to_location['title'].'</td>
            </tr>
        </table>
        ';
        
        if($printType == 'request'){
            $tblFromTo = '';
        }

        $tblproducts = '<table border = "1" width = "100%" cellpadding="5">';

        if($printType == 'request'){
            $tblproducts = $tblproducts.'
                <thead>
                    <tr bgcolor="#FDCA9C" style="font-weight:bold;">
                        <th width = "10%">SNo</th>
                        <th width = "15%">code</th>
                        <th width = "60%">Item Name</th>
                        <th width = "15%" align = "center">Request Qty</th>
                    </tr>
                </thead>
            ';
        }

        if($printType == 'delivery'){
            $tblproducts = $tblproducts.'
                <thead>
                    <tr bgcolor="#FDCA9C" style="font-weight:bold;">
                        <th width = "10%">SNo</th>
                        <th width = "15%">Code</th>
                        <th width = "45%">Item Name</th>
                        <th width = "15%" align = "center">Request Qty</th>
                        <th width = "15%" align = "center">Qty Delivered</th>
                    </tr>
                </thead>
            ';
        }

        $serialNo = 1;

        while($row = $db->sql_fetchrow($result2)){

            $product_id = $row['product_id'];

            if($product_id != ''){
                $sqlProduct ="
                SELECT product_code
                       ,title
                FROM product
                WHERE product_id = {$row['product_id']}
                ";
                $resultProduct = $db->sql_query($sqlProduct);
                $rowProduct    = $db->sql_fetchrow($resultProduct);

                if($printType == 'request'){
                    $tblproducts = $tblproducts.'
                    <tbody>
                        <tr nobr="true">
                            <td width = "10%">'.$serialNo.'</td>
                            <td width = "15%">PROD - '.$rowProduct['product_code'].'</td>
                            <td width = "60%">'.$rowProduct['title'].'</td>
                            <td width = "15%" align = "center">'.$row['qty_requested'].'</td>
                        </tr>
                    </tbody>';
                }

                if($printType == 'delivery'){
                    $tblproducts = $tblproducts.'
                    <tbody>
                        <tr nobr="true">
                            <td width = "10%">'.$serialNo.'</td>
                            <td width = "15%">PROD - '.$rowProduct['product_code'].'</td>
                            <td width = "45%">'.$rowProduct['title'].'</td>
                            <td width = "15%" align = "center">'.$row['qty_requested'].'</td>
                            <td width = "15%" align = "center">'.$row['qty'].'</td>
                        </tr>
                    </tbody>';
                }

                $serialNo++;
            }else{

                $tblproducts = $tblproducts.'
                    <tbody>
                        <tr>
                            <td width = "100%"> No items has been transfered </td>
                        </tr>
                    </tbody>';
            }


        }

        $tblproducts = $tblproducts.'</table>';

        if($printType == 'Reuqest Form'){
            $tblSignature = '
            <table width = "100%" border = "0" cellpadding = "5">
                <tr style="font-weight:bold;">
                    <th width = "40%">From Location: '.$from_location['title'].'</th>
                    <th width = "20%"></th>
                </tr>
                <tr>
                    <td width = "14%"><br/><br/>Signature:</td>
                    <td width = "26%"><br/><br/><hr></td>
                </tr>
            </table>
            ';
        }else{
            $tblSignature = '
            <table width = "100%" border = "0" cellpadding = "5">
                <tr style="font-weight:bold;">
                    <th width = "40%">From Location: '.$from_location['title'].'</th>
                    <th width = "20%"></th>
                    <th width = "40%" align = "left">To Location: '.$to_location['title'].'</th>
                </tr>
                <tr>
                    <td width = "14%"><br/><br/>Signature:</td>
                    <td width = "26%"><br/><br/><hr></td>
                    <td width = "20%"></td>
                    <td width = "14%"><br/><br/>Signature:</td>
                    <td width = "26%"><br/><br/><hr></td>
                </tr>
            </table>
            ';
        }

        $pdf->writeHTML($tblHeading, true, false, false, false, '');
        $pdf->writeHTML($tblFromTo, true, false, false, false, '');
        $pdf->writeHTML($tblproducts, true, false, false, false, '');
        $pdf->writeHTML($tblSignature, true, false, false, false, '');
        $pdf->Output('Internal Transfer.pdf', 'I');

    }

    /**
     *
     */
    function getAddLocation() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddLocationValidate()){
            return $validate->getErrorMessageXML();
        }

        $value  = $fn->getPostParam('value');

        $fa = array();
        $fa['value']   = $value;
        $fa['key_text']   = 'stockLocation';

        $valuelist_id = $fn->addRecord($fa, 'valuelist');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddLocationValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

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
    function getAddBatchProductForStockTransfer() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $tv     = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $batch_no          = $fn->getReqParam('batch_no');
        $product_id        = $fn->getReqParam('product_id');
        $po_product_id     = $fn->getReqParam('po_product_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        $SQLCheck  = "
        SELECT product_id
        FROM stock_transfer_history
        WHERE product_id = {$product_id}
        AND po_product_id = {$po_product_id}
        AND stock_transfer_id = {$stock_transfer_id}
        ";

        $resultCheck = $db->sql_query($SQLCheck);
        $numRows     = $db->sql_numrows($resultCheck);
        
        if($numRows >= 1){
            $arr['msg'] = "Please note the product is already added";
            return $cpUtil->getJsonFromArray($arr);
            exit();
        }
        
        $SQLPO = "
        SELECT pop.pack_size
              ,pop.expiry_date
              ,ibs.po_product_id
              ,ibs.batch_no
        FROM inventory_batchwise_stock ibs
        LEFT JOIN (po_product pop) ON (pop.po_product_id = ibs.po_product_id)
        WHERE ibs.product_id  = '{$product_id}'
        AND ibs.po_product_id = '{$po_product_id}'
        ";
        $resultPO = $db->sql_query($SQLPO);
        $rowPO    = $db->sql_fetchrow($resultPO);

        $fa = array();
        $fa['stock_transfer_id'] = $stock_transfer_id;
        $fa['product_id']        = $product_id;
        $fa['po_product_id']     = $po_product_id;
        $fa['batch_no']          = $batch_no;
        $fa['pack_size']         = $rowPO['pack_size'];
        $fa['created_by']        = $fn->getSessionParam('userName');
        $fa['creation_date']     = date("Y-m-d H:i:s");

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_transfer_history');
        $db->sql_query($SQL);
    }

    /**
     *
     */
    function getBatchProductCountCheck(){
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $product_id    = $fn->getReqParam('product_id');
        $from_location = $fn->getReqParam('from_location');
        $site_id       = $fn->getSessionParam('cp_site_id');

        $thForSiteId  = "";
        $tdForSiteId  = "";
        $thForSiteId  = "";
        $rows         = "";
        $sqlAppend    = "";
        $sqlAppendSt  = "";
        $stockTransferSQLForMultiSite = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $sqlAppendSt = "AND ibs.site_id = {$site_id}";
        }

        if($from_location == "1") {
            $stockField = "current_stock";
        } else {
            $sqlLocation = "
            SELECT internal_location_id
                  ,title 
            FROM internal_location 
            WHERE internal_location_id = {$from_location}
            ";
            $resultLocation = $db->sql_query($sqlLocation);
            $rowLocation    = $db->sql_fetchrow($resultLocation);
            $toLocation     = strtolower($rowLocation['title']);
            $toLocation     = str_replace(' ', '_', $toLocation);
            $stockField     = "{$toLocation}";
        }

        $SQLBatchNo = "
        SELECT  ibs.batch_no AS batch_no
               ,ibs.product_id
               ,ibs.{$stockField}
               ,po.pack_size
               ,po.expiry_date
        FROM inventory_batchwise_stock ibs
        LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id) 
        WHERE ibs.product_id = {$product_id}
        AND ibs.{$stockField} > 0
        ";
        $resultBatchNo  = $db->sql_query($SQLBatchNo);
        $numRowsBatchNo = $db->sql_numrows($resultBatchNo);

        $count = 0;
        while ($rowBatchNo = $db->sql_fetchrow($resultBatchNo)) {
            if($rowBatchNo['pack_size'] != '' && is_numeric($rowBatchNo['pack_size']) > 0){
                $stock = $rowBatchNo[$stockField] / $rowBatchNo['pack_size'];
            } else {
                $stock = $rowBatchNo[$stockField];
            }

            $stock = (int) $stock;

            if($stock > 0) {
                $count++;
            }
        }

        print $count;
    }

    /**
     *
     */
    function getBatchProductCountCheck1(){
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');
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

        print $count;
    }

    /**
     *
     */
    function getToLocationJson() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $from_location = $fn->getReqParam('from_location');

        $json  = array();
        if ($from_location == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $appendSql = "";
        if($from_location == 1) {
            $appendSql = "WHERE internal_location_id != 4";
        }

        $SQL = "
        SELECT internal_location_id
              ,title 
        FROM internal_location 
        {$appendSql}
        ";
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['internal_location_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }
}