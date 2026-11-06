<?
class CPL_Admin_Widgets_Hms_MOLReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $appendSqlOrd   = "";
        $appendSqlPur   = "";
        $appendSqlInv   = "";
        $appendSqlStk   = "";
        $appendSqlToStk = "";
        $appendSqlToUFP = "";
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
        } else {
            $appendSqlStk   = "AND st.from_location = 1";
            $appendSqlToStk = "AND st.to_location   = 1";
            $appendSqlToUFP = "AND st.to_location   = 4";
        }

        $SQL = "
        SELECT DISTINCT p.product_id
              ,p.title AS product_name
              ,p.offer_medicine
              ,s.company_name
              ,i.minimum_order_level
              ,i.inventory_id
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
        FROM product p
        LEFT JOIN (inventory i) ON (i.product_id = p.product_id)
        LEFT JOIN (po_product pop) ON (pop.product_id = p.product_id)
        LEFT JOIN (`supplier` s) ON (s.supplier_id = pop.supplier_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 's';

        $supplier_id    = $fn->getReqParam('supplier_id');

        if ($supplier_id != '') {
            $searchVar->sqlSearchVar[] = "s.supplier_id = '{$supplier_id}'";
        }

        $searchVar->sqlSearchVar[] = "i.minimum_order_level > 0";
        $searchVar->sqlHavingVar[] = "stock <= i.minimum_order_level";

        $searchVar->sortOrder = "p.title DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_mOLReport');

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

        $file_name = "MOLReport__" . date("d-m-Y") . ".xls";

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
        $supplier_id    = $fn->getReqParam('supplier_id');

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Medicine Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Offer Medicine');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Supplier');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Stock');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'MOL');

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


        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        $supplierIdAppendSql = '';
        $stockAppendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND s.site_id = {$cpSiteIdSession}";
        }
        if ($supplier_id != '') {
            $supplierIdAppendSql = "AND s.supplier_id = '{$supplier_id}'";
        }

        $stockAppendSql = "HAVING stock <= i.minimum_order_level";

        $appendSqlOrd = "";
        $appendSqlPur = "";
        $appendSqlInv = "";
        $appendSqlStk = "";
        $appendSqlInT = "";

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
            $appendSqlStk = "AND st.to_location = '{$rowsitedetail['title']}'";
        }

        $SQL = "
        SELECT DISTINCT p.product_id
              ,p.title AS product_name
              ,p.offer_medicine
              ,s.company_name
              ,i.minimum_order_level
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
                    (SELECT SUM(sth.qty) FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    {$appendSqlStk})

                    ,(SELECT SUM(sth.qty) FROM stock_transfer_history sth
                    LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
                    WHERE sth.product_id = p.product_id
                    {$appendSqlStk})
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
        FROM product p
        LEFT JOIN (inventory i) ON (i.product_id = p.product_id)
        LEFT JOIN (po_product pop) ON (pop.product_id = p.product_id)
        LEFT JOIN (`supplier` s) ON (s.supplier_id = pop.supplier_id)
        WHERE i.minimum_order_level > 0
        {$appendSqlSite}
        {$supplierIdAppendSql}
        {$stockAppendSql}
        ORDER BY p.title DESC
        ";
  
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['offer_medicine']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['stock']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['minimum_order_level']);             
        }

        $colc = 0;
        $rowc++;

        $actSheet->getStyle("A{$rowc}:G{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}