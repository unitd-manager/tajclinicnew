<?
class CPL_Admin_Modules_Tradingin_Inventory_View extends CP_Admin_Modules_Tradingin_Inventory_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $site_id =  '';

        $count   = 0;
        $rows    = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $site_id = $cpSiteIdSession;
        }

        foreach ($dataArray as $row){
            $weight     = "";
            $weightColumnValue = "";
            if($cpCfg['showWeightInPos'] == 1){
                $weightColumnValue = $listObj->getListDataCell($weight, 'center');
            }

            $item_code  = "<a href='index.php?_topRm=pharmacy&module=hms_product&_action=edit&product_id={$row['product_id']}'>PROD-{$row['item_code']}</a>";

            $productCodeTd = $listObj->getListDataCell($item_code, 'center');
            $stock = str_replace('.00', '', $row['actual_stock']);
                
            $adjustStockLink = "";
            if($_SESSION['userGroupName'] == "Super Administrator" 
                || $_SESSION['userGroupName'] == "Administrator"
               || $_SESSION['userGroupName'] == "PHARMACY ADMIN" ) {
                $adjustStockLink = "<a class='poLinked' product_id='{$row['product_id']}' site_id=''><u>Adjust Stock</u></a>";
            }

            $stock = "<span class='stockUpdateList_{$row['inventory_id']}'>{$stock}</span> {$adjustStockLink}";

            $hightlightDueTasks = $listObj->getListRowHeader($row, $count);

            $SQLIBS = "
            SELECT ibs.product_id
                  ,ibs.po_product_id
                  ,ibs.current_stock
                  ,DATEDIFF(po.expiry_date, Now()) AS days
            FROM inventory_batchwise_stock ibs
            LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id)
            WHERE ibs.product_id = {$row['product_id']}
              AND ibs.current_stock > 0
              AND DATEDIFF(po.expiry_date, Now()) < 100
            ";

            $resultIBS = $db->sql_query($SQLIBS);
            $rowIBS = $db->sql_fetchrow($resultIBS);
            $numRowsIBS = $db->sql_numrows($resultIBS);
            if($numRowsIBS > 0){
                 $hightlightDueTasks = $listObj->getListRowHeader($row, $count, 'invExpColor');
            }

            $SQLIBS1 = "
            SELECT ibs.product_id
                  ,ibs.po_product_id
                  ,ibs.current_stock
                  ,DATEDIFF(po.expiry_date, Now()) AS days
            FROM inventory_batchwise_stock ibs
            LEFT JOIN po_product po ON (po.po_product_id = ibs.po_product_id)
            WHERE ibs.product_id = {$row['product_id']}
              AND ibs.current_stock > 0
              AND DATEDIFF(po.expiry_date, Now()) < 30
            ";

            $resultIBS1 = $db->sql_query($SQLIBS1);
            $rowIBS1 = $db->sql_fetchrow($resultIBS1);
            $numRowsIBS1 = $db->sql_numrows($resultIBS1);
            if($numRowsIBS1 > 0){
                 $hightlightDueTasks = $listObj->getListRowHeader($row, $count, 'invExpColorRed');
            }

            $rows .= "
            {$hightlightDueTasks}
            {$listObj->getListDataCell('STK-'.$row['inventory_code'])}
            {$productCodeTd}
            {$listObj->getGoToDetailText($count, $row['product_name'])}
            {$listObj->getListDataCell($stock)}
            {$listObj->getListDataCell($row['minimum_order_level'])}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++ ;
        }

        $weightColumn = "";
        if($cpCfg['showWeightInPos'] == 1){
            $weightColumn = $listObj->getListHeaderCell('weight', '', 'txtCenter');
        }

        $productCodeTh = $listObj->getListHeaderCell('Item Code', 'item_code' , 'txtCenter');

        $text = "
        <a class='btn btn-primary updatedCurrentStockToAllProduct mb10'>
            <span class='glyphicon glyphicon-repeat mr5'></span>
            Update Stock
        </a>
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Inventory Code', 'inventory_code')}
        {$productCodeTh}
        {$listObj->getListHeaderCell('Name', 'product_name')}
        {$listObj->getListHeaderCell('Stock', 'i.actual_stock' )}
        {$listObj->getListHeaderCell('MOL', 'i.minimum_order_level' )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $db      = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];
        $expNoEdit = array('isEditable' => 0);
        $site_id   = $fn->getSessionParam('cp_site_id');

        /*Begin Stock Details For The Location */
        /*$SQLsitedetail="
        SELECT internal_location_id
               ,title
        FROM internal_location
        WHERE internal_location_id NOT IN (1, 4)
        ";
        $resultsitedetail = $db->sql_query($SQLsitedetail);

        $PurchasedWeight = '';
        $SoldWeight      = '';
        $StockWeight     = '';
        $StockRows       = '';

        while($rowsitedetail = $db->sql_fetchrow($resultsitedetail)) {
            $stockArray = $fn->getStockForProductLocationWise($row['product_id'], $rowsitedetail['internal_location_id']);
            $stock = $stockArray['OverallStock'];
            $StockTransferIn  = $stockArray['StockTransferIn'];
            $StockTransferOut = $stockArray['StockTransferOut'];
            $UsedPatientQty   = $stockArray['UsedPatientQty'];

            $StockRows .= "
            <tr>
                <td>{$rowsitedetail['title']}</td>
                <td>
                    {$StockTransferIn}
                    <a class='stockTransferredIn ml20' product_id='{$row['product_id']}' internal_location_id='{$rowsitedetail['internal_location_id']}'>
                        View
                    </a>
                </td>
                <td>
                    {$StockTransferOut}
                    <a class='stockTransferredOut ml20' product_id='{$row['product_id']}' internal_location_id='{$rowsitedetail['internal_location_id']}'>
                        View
                    </a>
                </td>
                <td>
                    {$UsedPatientQty}
                    <a class='stockUsedForPatient ml20' product_id='{$row['product_id']}' internal_location_id='{$rowsitedetail['internal_location_id']}'>
                        View
                    </a>
                </td>
                <td>
                    {$stock}
                </td>
            </tr>
            ";
        }

        $stockRowHeading = "
        <table class='thinlist stockDetailsTable'>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Total Stock Transferred(In)</th>
                    <th>Total Stock Transferred(Out)</th>
                    <th>Total Used For Patient Qty</th>
                    <th>Total Available Qty</th>
                </tr>
            </thead>
            <tbody>
                {$StockRows}
            </tbody>
        </table>
        ";*/

        $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

        $item_code  = "<a href='index.php?_topRm=pharmacy&module=hms_product&_action=edit&product_id={$row['product_id']}'>PROD-{$row['item_code']}</a>";

        if ($_SESSION['userGroupName'] == "Super Administrator" 
            || $_SESSION['userGroupName'] == "Administrator"
            || $_SESSION['userGroupName'] == "PHARMACY ADMIN" ) {
            $stockArray          = $fn->getStockForProduct($row['product_id']);
            $stock               = $stockArray['OverallStock'];
            $PurchasedQty        = $stockArray['PurchasedQty'];
            $SoldQty             = $stockArray['SoldQty'];
            $SalesReturnQty      = $stockArray['SalesReturnQty'];
            $StockTransferOutQty = $stockArray['StockTransferOutQty'];
            $StockTransferInQty  = $stockArray['StockTransferInQty'];
            $DamagedQty          = $stockArray['DamagedQty'];

            //$PurchasedQty = "{$PurchasedQty} <a class='poLinked' product_id='{$row['product_id']}' site_id=''><u>View</u></a>";
            $SoldQty = "{$SoldQty} <a class='billsLinked' product_id='{$row['product_id']}' site_id=''><u>View</u></a>";
            
            $SQLAdjustStock = "
            SELECT adjust_stock
                  ,adjust_stock_log_id
            FROM adjust_stock_log
            WHERE product_id = '{$row['product_id']}'
            AND inventory_id = '{$row['inventory_id']}'
            ";
            $resultAdjustStock = $db->sql_query($SQLAdjustStock);
            $numRowsAdjustStock = $db->sql_numrows($resultAdjustStock);
            $rowAdjustStock = $db->sql_fetchrow($resultAdjustStock);

            $AdjustStockSaveView = "
            <a inventory_id='{$row['inventory_id']}' history_record='{$numRowsAdjustStock}' product_id='{$row['product_id']}' class='float_left viewAllUpdatedAdjustStockHistory'>
                <u>View History</u>
            </a>";

            $expEditCond = array('isEditable' => 0
                                ,'notesRight' => $AdjustStockSaveView);
            if($_SESSION['userGroupName'] == "Administrator" || $_SESSION['userGroupName'] == "Super Administrator") {
                $expEditCond = array('notesRight' => $AdjustStockSaveView);
            }

            $stockVal = str_replace('.00', '', $row['actual_stock']);
            $adjustStockLink = "";
            if($_SESSION['userGroupName'] == "Super Administrator" || $_SESSION['userGroupName'] == "Administrator") {
                $adjustStockLink = "<a class='poLinked' product_id='{$row['product_id']}' site_id=''><u>Adjust Stock</u></a>";
            }

            $stockVal = "<span class='stockUpdate'>{$stockVal}</span> {$adjustStockLink}";
            $stockFields = "
            <tr>
                <td width='25%'>{$formObj->getTBRow('Purchased Qty(+)', 'PurchasedQty', $PurchasedQty, $expNoEdit)}</td>
                <td width='25%'>{$formObj->getTBRow('Sold Qty(-)', 'SoldQty', $SoldQty, $expNoEdit)}</td>
                <td width='25%'>{$formObj->getTBRow('Sales Return Qty(+)', 'SalesReturnQty', $SalesReturnQty, $expNoEdit)}</td>
                <td width='25%'>{$formObj->getTBRow('Stock', 'stock', $stockVal, $expNoEdit)}</td>
            </tr>
            ";
        } else {
            $stockVal = str_replace('.00', '', number_format($row['actual_stock'], 2, '.', ','));
            $stockFields = "
            <tr>
                <td width='25%'>{$formObj->getTBRow('Stock', 'stock', $stockVal, $expNoEdit)}</td>
            </tr>
            ";
        }

        /*
        <td width='25%' class='AdjustStockTdRow'>
            {$formObj->getTBRow('Adjust Stock(+/-)', 'changed_stock', $row['changed_stock'], $expEditCond)}
        </td>
        */

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Product Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='25%'>{$formObj->getTBRow('Name', 'product_name', $row['product_name'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('Item Code', 'item_code', $item_code, $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('MOL', 'minimum_order_level', $row['minimum_order_level'])}</td>
                                <td width='25%'>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                            </tr>
                            {$stockFields}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <input type='hidden' name='product_id' value='{$row['product_id']}' />
        <input type='hidden' name='inventory_id' value='{$row['inventory_id']}' />
        ";

        /*<td width='25%'>{$formObj->getTBRow('Total Stock Transferred(In)(+)', 'StockTransferInQty', $StockTransferInQty, $expNoEdit)}</td>
        <td width='25%'>{$formObj->getTBRow('Total Stock Transferred(Out)(-)', 'StockTransferOutQty', $StockTransferOutQty, $expNoEdit)}</td>
        */
        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        /*$text = "
        {$this->getBatchWiseStockDisplay($row)}
        {$this->getPurchaseOrderDisplay($row)}
        {$this->getOrderDisplay($row)}
        ";*/
        $text = "";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $supplier_id         = $fn->getReqParam('supplier_id');
        $category_id         = $fn->getReqParam('category_id');
        $minimum_order_level = $fn->getReqParam('minimum_order_level');
        $expiry_date         = $fn->getReqParam('expiry_date');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $spArray1 = array(
            "MOL Products"
        );

        $spArray2 = array(
            ""
           ,"Expiry Date < 100"
           ,"Expiry Date < 60"
           ,"Expiry Date < 30"
        );

        $sqlSupplier = "
        SELECT c.company_id
              ,c.company_name
        FROM company c
        WHERE c.category = 'Supplier'
        ORDER BY c.company_name
        ";

        $sqlCategory = "
        SELECT c.category_id
              ,c.title
        FROM category c
        ";

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        <td>
            <select name='minimum_order_level'>
                <option value=''>Minimum Order Level</option>
                {$cpUtil->getDropDown1($spArray1, $minimum_order_level)}
           </select>
        </td>
        <td>
            <select name='expiry_date'>
                <option value=''>Expiry</option
                {$cpUtil->getDropDown1($spArray2, $expiry_date)}
           </select>
        </td>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getCreateInventoryRecords() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        /*$SQL = "
        SELECT p.product_id
        FROM product p
        WHERE p.product_id NOT IN(
            SELECT invent.product_id
            FROM inventory invent
        )
        ORDER BY p.product_id
       ";*/

       $SQL = "
        SELECT p.product_id
        FROM product p
        LEFT JOIN inventory inv ON inv.product_id = p.product_id
        WHERE inv.product_id IS NULL
       ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $SQLInvCode = "
            SELECT MAX(CONVERT(inventory_code, UNSIGNED INTEGER)) + 1 AS inv_code
            FROM `inventory`
            ";
            $resultInvCode = $db->sql_query($SQLInvCode);
            $rowInvCode    = $db->sql_fetchrow($resultInvCode);

            if($rowInvCode['inv_code'] != ""){
                $inv_code = $rowInvCode['inv_code'];
            }
            else{
                $inv_code = "1001";
            }

            $fa = array();

            $fa['product_id']     = $row['product_id'];
            $fa['creation_date']  = date('Y-m-d H:i:s');
            $fa['inventory_code'] = $inv_code;

            $inventory_id = $fn->addRecord($fa, 'inventory');
        }
    }

    /**
     *
     */
    function getUpdateInventoryCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Inventory order Code */
        $inventoryCode = $fn->getSettingsValueByKey("inventoryCode");

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'inventoryCode'";
        $result = $db->sql_query($SQL);

        return $inventoryCode;
    }

    /**
     */
    function getOrderDisplay(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');
        $site_id = $fn->getReqParam('site_id');

        $formAction = '';

        $text = "
        <div class='linkPortalWrapper panel tradingin_inventory_orderLink'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <div expanded='1'>
                        <div class='floatbox'>
                            <div class='float_left RightPanelHeading'>Bills Linked</div>
                            <div class='txtRight'>
                              <div class='toggle'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='linkPortalDataWrapper'>
                        <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                            <div id='invoicePortalOuter'>
                                {$this->getOrderDisplayDetail($product_id, $site_id)}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getOrderDisplayDetail($product_id, $site_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $sqlSite = "";
        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $sqlSite = "AND o.site_id = {$site_id}";
        }

        $SQL = "
        SELECT DISTINCT o.order_id
              ,oi.order_item_id
              ,oi.item_title
              ,oi.unit_price
              ,oi.qty
              ,oi.qty * oi.unit_price
              ,o.order_date
              ,o.bill_number
              ,o.record_type
              ,com.company_name
        FROM `order_item` oi
        LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
        LEFT JOIN (`invoice` inv) ON (o.order_id = inv.order_id)
        LEFT JOIN company com ON com.company_id = o.company_id
        WHERE oi.record_id = {$product_id}
        {$sqlSite}
        AND (o.order_status = 'Paid' || o.order_status = 'Due' || o.order_status = 'Partial Payment')
        AND (inv.status = 'Paid' || inv.status = 'Due' || inv.status = 'Partial Payment')
        ORDER BY o.order_date desc
        ";

        $result   = $db->sql_query($SQL);
        $client = '';
        while ($rowOI = $db->sql_fetchrow($result)) {
            if($rowOI['record_type'] == 'POS'){
                $client = 'POS';
            }
            else{
                $client = $rowOI['company_name'];
            }

            /*$SQLINV = "
            SELECT DISTINCT i.invoice_id
                  ,it.unit_price
                  ,it.qty
                  ,i.invoice_date
                  ,i.invoice_code
            FROM `invoice_item` it
            LEFT JOIN (`invoice` i) ON (i.invoice_id = it.invoice_id)
            WHERE it.record_id = {$product_id}
            AND i.order_id = {$rowOI['order_id']}
            AND (i.status = 'Paid' || i.status = 'Due' || i.status = 'Partial Payment')
            ";

            $resultINV   = $db->sql_query($SQLINV);
            $rowsInv = '';
            while ($rowINV = $db->sql_fetchrow($resultINV)) {
                $urlPrint = "index.php?_topRm=pos&module=tradingsg_pos&_spAction=printInvoiceRecord&orderNo={$rowOI['order_id']}&showHTML=0";

                if($rowINV['invoice_code'] == ""){
                    $invoice_code = 'INV - '.$rowINV['invoice_id']; 
                }
                else{
                    $invoice_code = $rowINV['invoice_code'];
                }
                
                $rowsInv .="
                <tr>
                    <td>
                        <a href='{$urlPrint}' target='_blank'>
                            <u>{$invoice_code}</u>
                        </a>
                    </td>
                    <td>{$fn->getCPDate($rowINV['invoice_date'], 'd-m-Y')}</td>
                    <td>{$rowINV['qty']}</td>
                    <td class='txtRight'>{$rowINV['unit_price']}</td>
                </tr>
                ";
            }*/

            if($rowOI['order_id'] < 10){
                $orderNo = '0000' . $rowOI['order_id'];
            }
            else if($rowOI['order_id'] <= 99){
                $orderNo = '000' . $rowOI['order_id'];
            }
            else if($rowOI['order_id'] <= 999){
                $orderNo = '00' . $rowOI['order_id'];
            }
            else if($rowOI['order_id'] <= 9999){
                $orderNo = '0' . $rowOI['order_id'];
            }
            else{
                $orderNo = $rowOI['order_id'];
            }

            $OrderLink = $orderNo;
            if($cpSiteIdSession == $site_id) {
                $OrderEditLink = "index.php?_topRm=pharmacy&module=hms_order&_action=edit&order_id={$rowOI['order_id']}";
                $OrderLink = "  <a href='{$OrderEditLink}' target='_blank'>
                                    <u>{$rowOI['bill_number']}</u>
                                </a>";
            }

            $rows .= "
            <tr class='orderRightPanelTr'>
                <td>
                    {$OrderLink}
                </td>
                <td>{$fn->getCPDate($rowOI['order_date'], 'd-m-Y')}</td>
                <td class='txtRight'>{$rowOI['unit_price']}</td>
                <td>{$rowOI['qty']}</td>
                <td>{$client}</td>
            </tr>
            ";

            /*<tr>
                <td></td>
                <td colspan='4'>
                    <table class='thinlist'>
                        <tr>
                            <th>Invoice Code</th>
                            <th>Date</th>
                            <th>PCS</th>
                            <th class='txtRight'>Amount</th>
                        </tr>
                        {$rowsInv}
                    </table>
                </td>
            </tr>*/
        }

        //style='background-color:#0F9191; color:#ffffff'

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Order Id</th>
            <th>Date</th>
            <th class='txtRight'>Amount</th>
            <th>PCS</th>
            <th>Client</th>
        </tr>
        ";

        $text = "
        <table class='thinlist' width='100%'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     */
    function getPurchaseOrderDisplay(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');
        $site_id = $fn->getReqParam('site_id');

        $formAction = '';

        $text = "
        <div class='linkPortalWrapper panel tradingin_inventory_purchaseOrderLink'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <div expanded='1'>
                        <div class='floatbox'>
                            <div class='float_left RightPanelHeading'>Purchase Orders Linked</div>
                            <div class='txtRight'>
                              <div class='toggle'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='linkPortalDataWrapper'>
                        <form id='poPrint' class='' method='post' action='{$formAction}'>
                            <div id='invoicePortalOuter'>
                                {$this->getPurchaseOrderDisplayDetail($product_id, $site_id)}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getPurchaseOrderDisplayDetail($product_id, $site_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        $tdForSiteId = "";
        $thForSiteId = "";
        $leftjnAppend = "";
        $sqlSite = "";

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $sqlAppend = ",st.title as site_title";
            $leftjnAppend = "
            LEFT JOIN site st ON st.site_id = po.site_id";
            $sqlSite = "AND po.site_id = {$site_id}";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        (SELECT pop.cost_price
              ,pop.qty
              ,com.company_name AS supplier_name
              ,po.po_code
              ,po.purchase_order_date
              ,po.purchase_order_id
              ,po.creation_date
              ,p.title AS product_name
              ,pop.pack_size
              ,pop.batch_no
              ,pop.selling_price
              ,pop.product_id
              ,pop.po_product_id
              ,(CASE 
               WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
               THEN pop.qty * pop.pack_size
               ELSE pop.qty END) AS purchased_qty
              {$sqlAppend}
              ,ibs.sales_qty AS SoldQty
        FROM inventory_batchwise_stock ibs
        LEFT JOIN po_product pop ON (pop.po_product_id = ibs.po_product_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN supplier com ON (pop.supplier_id = com.supplier_id)
        LEFT JOIN product p ON (p.product_id = ibs.product_id)
        {$leftjnAppend}
        WHERE ibs.product_id = {$product_id}
        {$sqlSite}
        AND ibs.current_stock > 0
        ORDER BY ibs.po_product_id)
        UNION
        (SELECT pop.cost_price
              ,pop.qty
              ,com.company_name AS supplier_name
              ,po.po_code
              ,po.purchase_order_date
              ,po.purchase_order_id
              ,po.creation_date
              ,p.title AS product_name
              ,pop.pack_size
              ,pop.batch_no
              ,pop.selling_price
              ,pop.product_id
              ,pop.po_product_id
              ,(CASE 
               WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
               THEN pop.qty * pop.pack_size
               ELSE pop.qty END) AS purchased_qty
              {$sqlAppend}
              ,ibs.sales_qty AS SoldQty
        FROM inventory_batchwise_stock ibs
        LEFT JOIN po_product pop ON (pop.po_product_id = ibs.po_product_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN supplier com ON (pop.supplier_id = com.supplier_id)
        LEFT JOIN product p ON (p.product_id = ibs.product_id)
        {$leftjnAppend}
        WHERE ibs.product_id = {$product_id}
        {$sqlSite}
        AND ibs.current_stock <= 0
        ORDER BY
        (CASE
            WHEN (ibs.adjust_stock IS NOT NULL OR 
                  ibs.adjust_stock != ''
            ) THEN 1
            ELSE ibs.inventory_batchwise_stock_id
        END)
        LIMIT 10)
        ";
        $result   = $db->sql_query($SQL);
        $product_name         = "";
        $batchwiseStock       = 0;
        $batchwiseAdjustStock = 0;
        $minusStock           = 0;
        $plusStock            = 0;
        while ($rowPo = $db->sql_fetchrow($result)) {

            if($rowPo['purchase_order_date'] == '' || $rowPo['purchase_order_date'] == 0){
                $purchase_order_date = $fn->getCPDate($rowPo['creation_date'], 'd-m-Y');
            }
            else{
                $purchase_order_date = $fn->getCPDate($rowPo['purchase_order_date'], 'd-m-Y');
            }

            $SQLSite = "
            SELECT title
            FROM site
            WHERE site_id = '{$site_id}'
            ";
            $resultSite = $db->sql_query($SQLSite);
            $rowSite    = $db->sql_fetchrow($resultSite);
            $siteArr    = explode(" ", $rowSite['title']);
            $sitePrefix = "";
            foreach($siteArr as $site) {
               $sitePrefix .= substr($site,0,1);
            }

            $po_code = 'PO - '.$rowPo['po_code'];
            if($cpSiteIdSession == $site_id) {
                $po_code = "<a href='index.php?_topRm=pharmacy&module=tradingsg_purchaseOrder&_action=edit&record_id={$rowPo['purchase_order_id']}' target='_blank'><u>PO - {$rowPo['po_code']}</u></a>";
            }

            $appendSqlSiteStock = '';
            if($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSiteStock = "site_id = {$cpSiteIdSession}";
            }

            $SQLStock = "
            SELECT current_stock
                  ,adjust_stock
                  ,inventory_batchwise_stock_id
            FROM inventory_batchwise_stock
            WHERE po_product_id = {$rowPo['po_product_id']}
            {$appendSqlSiteStock}
            ";
            $resultStock = $db->sql_query($SQLStock);
            $rowStock    = $db->sql_fetchrow($resultStock);
            $stock       = $rowStock['current_stock'];
            $stockExcludeAdjust = $rowStock['current_stock'] - $rowStock['adjust_stock'];

            $SQLInventory = "
            SELECT inventory_id
            FROM inventory
            WHERE product_id = '{$rowPo['product_id']}'
            ";
            $resultInventory = $db->sql_query($SQLInventory);
            $rowInventory    = $db->sql_fetchrow($resultInventory);

            $disableAdjustStock = "";
            $adjustStockSaveBtn = "
            <a class='btn btn-success float_left AdjustStockInventoryEditSaveBtn'>
                Save
            </a>
            ";

            $trClassForMinust = "";
            if($stock <= 0) {
                $adjustStockSaveBtn = "";
                $trClassForMinust   = "class='minusStockRowsInAdjustStock'";
                $disableAdjustStock = "disabled='disabled'";
            }

            /*if ($_SESSION['userGroupName'] == "Super Administrator") {*/
                $adjust_stock = "
                <a inventory_batchwise_stock_id='{$rowStock['inventory_batchwise_stock_id']}' stock='{$stockExcludeAdjust}' class='float_left viewAllUpdatedAdjustStockHistory'>
                    <u>View</u>
                </a>
                <input name='adjust_stock' value='{$rowStock['adjust_stock']}' class='txt float_left AdjustStockInventoryEdit' stock='{$stockExcludeAdjust}' batch_no='{$rowPo['batch_no']}' product_id='{$rowPo['product_id']}' po_product_id='{$rowPo['po_product_id']}' inventory_id='{$rowInventory['inventory_id']}' {$disableAdjustStock}/>
                {$adjustStockSaveBtn}
                ";
            /*} else {
                $adjust_stock = "
                <input name='adjust_stock' value='{$rowStock['adjust_stock']}' class='txt float_left AdjustStockInventoryEdit' batch_no='{$rowPo['batch_no']}' product_id='{$rowPo['product_id']}' inventory_id='{$rowInventory['inventory_id']}' disabled/>
                ";
            }*/

            if($stock > 0) {
                $plusStock  += $stock;
            } else {
                $minusStock += $stock;
            }

            if ($_SESSION['isDeveloper'] == 1) {
                $soldQtyColumnVal = "<td>{$rowPo['SoldQty']}</td>";
            } else {
                $soldQtyColumnVal = "";
            }

            $rows .= "
            <tr {$trClassForMinust}>
                <td>{$adjust_stock}</td>
                <td>{$po_code}</td>
                <td>{$purchase_order_date}</td>
                <td>{$rowPo['batch_no']}</td>
                <td>{$rowPo['cost_price']}</td>
                <td>{$rowPo['selling_price']}</td>
                <td>{$rowPo['pack_size']}</td>
                <td>{$rowPo['qty']}</td>
                <td>{$rowPo['purchased_qty']}</td>
                {$soldQtyColumnVal}
                <td class='overallStockForLocationWise txtRight'>{$stock}</td>
            </tr>
            ";

            $product_name = $rowPo['product_name'];
        }

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $thForSiteId = "<th>Location</th>";
        }

        $OverallStockTotal = $plusStock + $minusStock;

        if ($_SESSION['isDeveloper'] == 1) {
            $colspanProductName   = "4";
            $soldQtyColumnHeading = "<th>Sold Qty</th>";
        } else {
            $colspanProductName   = "3";
            $soldQtyColumnHeading = "";
        }

        $header ="
        <tr>
            <th colspan='{$colspanProductName}'>{$product_name}</th>
            <th class='txtRight' colspan='2'>Total Minus Stock : </th>
            <th class='txtRight'>{$minusStock}</th>
            <th class='txtRight' colspan='2'>Overall Stock : </th>
            <th class='txtRight'>{$OverallStockTotal}</th>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Adjust Stock</th>
            <th>PO Code</th>
            <th>Date</th>
            <th>Batch No</th>
            <th>Cost Price</th>
            <th>Selling Price</th>
            <th>Pack Size</th>
            <th>Qty</th>
            <th>Purchased Qty</th>
            {$soldQtyColumnHeading}
            <th>Stock Qty</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     */
    function getPurchaseOrderDisplayDetail1($product_id, $site_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        $tdForSiteId = "";
        $thForSiteId = "";
        $leftjnAppend = "";
        $sqlSite = "";

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $sqlAppend = ",st.title as site_title";
            $leftjnAppend = "
            LEFT JOIN site st ON st.site_id = po.site_id";
            $sqlSite = "AND po.site_id = {$site_id}";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        SELECT pop.cost_price
              ,pop.qty
              ,com.company_name AS supplier_name
              ,po.po_code
              ,po.purchase_order_date
              ,po.purchase_order_id
              ,po.creation_date
              ,p.title AS product_name
              ,pop.pack_size
              ,pop.batch_no
              ,pop.product_id
              ,pop.po_product_id
              ,SUM(CASE 
               WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
               THEN pop.qty * pop.pack_size
               ELSE pop.qty END) AS purchased_qty
              ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
                LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
                WHERE inItm.record_id = pop.product_id
                AND inItm.not_add_in_stock != 1
                AND inv.status = 'Paid'
                AND inv.invoice_type = 'POS'
                AND inItm.batch_no = pop.batch_no) AS Sales

             ,(SELECT SUM(CASE 
                       WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                       THEN pop.qty * pop.pack_size
                       ELSE pop.qty END) AS Purchase 
                FROM po_product pop
                LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
                WHERE po.status != 'Cancelled'
                AND pop.batch_no = pop.batch_no
                AND pop.product_id = pop.product_id) AS Purchase
              {$sqlAppend}
        FROM po_product pop
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN product p ON (p.product_id = pop.product_id)
        LEFT JOIN supplier com ON (pop.supplier_id = com.supplier_id)
        {$leftjnAppend}
        WHERE pop.product_id = {$product_id}
        AND po.status != 'Cancelled'
        GROUP BY pop.batch_no
        ORDER BY 
        CASE
            WHEN (Purchase < Sales 
                  ) THEN 1
            ELSE pop.po_product_id
        END
        {$sqlSite}
        ";
        $result   = $db->sql_query($SQL);
        $product_name         = "";
        $batchwiseStock       = 0;
        $batchwiseAdjustStock = 0;
        while ($rowPo = $db->sql_fetchrow($result)) {
            /*if($cpCfg['cp.hasMultiUniqueSites']  == true){
                $tdForSiteId = "<td>{$rowPo['site_title']}</td>";
            }*/

            if($rowPo['purchase_order_date'] == '' || $rowPo['purchase_order_date'] == 0){
                $purchase_order_date = $fn->getCPDate($rowPo['creation_date'], 'd-m-Y');
            }
            else{
                $purchase_order_date = $fn->getCPDate($rowPo['purchase_order_date'], 'd-m-Y');
            }

            $SQLSite = "
            SELECT title
            FROM site
            WHERE site_id = '{$site_id}'
            ";
            $resultSite = $db->sql_query($SQLSite);
            $rowSite    = $db->sql_fetchrow($resultSite);
            $siteArr    = explode(" ", $rowSite['title']);
            $sitePrefix = "";
            foreach($siteArr as $site) {
               $sitePrefix .= substr($site,0,1);
            }

            $po_code = 'PO - '.$rowPo['po_code'];
            if($cpSiteIdSession == $site_id) {
                $po_code = "<a href='index.php?_topRm=pharmacy&module=tradingsg_purchaseOrder&_action=edit&record_id={$rowPo['purchase_order_id']}' target='_blank'><u>PO - {$rowPo['po_code']}</u></a>";
            }

            $stockArray   = $fn->getStockForProductBatchWise($rowPo['product_id'], '', $rowPo['batch_no']);
            $stock        = $stockArray['OverallStock'];
            $SoldQty      = $stockArray['SoldQty'];
            $PurchasedQty = $stockArray['PurchasedQty'];

            $SQLAdjustStock = "
            SELECT adjust_stock
                  ,inventory_adjust_stock_id
            FROM inventory_adjust_stock
            WHERE product_id = '{$rowPo['product_id']}'
            AND batch_no = '{$rowPo['batch_no']}'
            ";
            $resultAdjustStock = $db->sql_query($SQLAdjustStock);
            $rowAdjustStock    = $db->sql_fetchrow($resultAdjustStock);

            $stockWithoutAdjustArray = $fn->getStockForProductBatchWiseWithoutAdjustStock($rowPo['product_id'], '', $rowPo['batch_no']);
            $stockWithoutAdjust      = $stockWithoutAdjustArray['OverallStock'];
            $SoldQtyAdjust           = $stockWithoutAdjustArray['SoldQty'];
            $PurchasedQtyAdjust      = $stockWithoutAdjustArray['PurchasedQty'];
            
            if($PurchasedQtyAdjust < $SoldQtyAdjust) {
                $batchwiseAdjustStock = $PurchasedQtyAdjust - $SoldQtyAdjust;
            } else if($PurchasedQtyAdjust >= $SoldQtyAdjust) {
                if($batchwiseAdjustStock > 0) {
                    $batchwiseAdjustStock = $stockWithoutAdjust;
                } else {
                    $batchwiseAdjustStock = $batchwiseAdjustStock + $stockWithoutAdjust;
                }
            }

            $stockExcludeAdjust = $stockWithoutAdjust;

            if($PurchasedQty < $SoldQty) {
                $batchwiseStock = $PurchasedQty - $SoldQty;
            } else if($PurchasedQty >= $SoldQty) {
                if($batchwiseStock > 0) {
                    $batchwiseStock = $stock;
                } else {
                    $batchwiseStock = $batchwiseStock + $stock;
                }
            }

            //$batchwiseStock = $stock;
            //$stockExcludeAdjust = $stockWithoutAdjust;

            $SQLInventory = "
            SELECT inventory_id
            FROM inventory
            WHERE product_id = '{$rowPo['product_id']}'
            ";
            $resultInventory = $db->sql_query($SQLInventory);
            $rowInventory    = $db->sql_fetchrow($resultInventory);

            if ($_SESSION['userGroupName'] == "Super Administrator") {
                $adjust_stock = "
                <a inventory_adjust_stock_id='{$rowAdjustStock['inventory_adjust_stock_id']}' stock='{$stockExcludeAdjust}' class='float_left viewAllUpdatedAdjustStockHistory'>
                    <u>View</u>
                </a>
                <input name='adjust_stock' value='{$rowAdjustStock['adjust_stock']}' class='txt float_left AdjustStockInventoryEdit' stock='{$stockExcludeAdjust}' batch_no='{$rowPo['batch_no']}' product_id='{$rowPo['product_id']}' inventory_id='{$rowInventory['inventory_id']}' />
                <a class='btn btn-success float_left AdjustStockInventoryEditSaveBtn'>
                    Save
                </a>
                ";
            } else {
                $adjust_stock = "
                <input name='adjust_stock' value='{$rowAdjustStock['adjust_stock']}' class='txt float_left AdjustStockInventoryEdit' batch_no='{$rowPo['batch_no']}' product_id='{$rowPo['product_id']}' inventory_id='{$rowInventory['inventory_id']}' disabled/>
                ";
            }

            if($rowPo['purchased_qty'] <= 0) {
                $adjust_stock = '';
            }

            //if($rowPo['purchased_qty'] > 0) {
                $rows .= "
                <tr>
                    <td>{$adjust_stock}</td>
                    <td>{$po_code}</td>
                    <td>{$purchase_order_date}</td>
                    <td>{$rowPo['batch_no']}</td>
                    <td>{$rowPo['cost_price']}</td>
                    <td>{$rowPo['pack_size']}</td>
                    <td>{$rowPo['qty']}</td>
                    <td>{$rowPo['purchased_qty']}</td>
                    <td class='overallStockForLocationWise'>{$batchwiseStock}</td>
                </tr>
                ";
            //}

            $product_name = $rowPo['product_name'];
        }

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $thForSiteId = "<th>Location</th>";
        }

        $header ="
        <tr>
            <th colspan='9'>{$product_name}</th>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Adjust Stock</th>
            <th>PO Code</th>
            <th>Date</th>
            <th>Batch No</th>
            <th>Amount</th>
            <th>Pack Size</th>
            <th>Qty</th>
            <th>Purchased Qty</th>
            <th>Stock Qty</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     */
    function getBatchWiseStockDisplay(){
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');
        $internal_location_id = $fn->getReqParam('internal_location_id');

        $thForSiteId   = "";
        $sqlAppend     = "";
        $tdForSiteId   = "";
        $thForSiteId   = "";
        $leftjnAppend  = "";
        $leftjnAppend2 = "";
        $rows          = "";

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $sqlAppend = ",s.title as site_title";
            $leftjnAppend = "
            LEFT JOIN site s ON s.site_id = po.site_id";
            $leftjnAppend2 = "
            LEFT JOIN site s ON s.site_id = st.to_location";
        }

        $appendSql   = '';
        $sqlAppendSt = '';
        $stockTransferSQLForMultiSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql   = "AND po.site_id = {$site_id}";
            $sqlAppendSt = "AND st.to_location = {$site_id}";

            $stockTransferSQLForMultiSite = "
            UNION
            SELECT  sth.batch_no AS batch_no
                   ,sth.product_id
                   ,pp.expiry_date
                   {$sqlAppend}
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            LEFT JOIN po_product pp ON (pp.po_product_id = sth.po_product_id)
            {$leftjnAppend2}
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
              {$sqlAppendSt}
            GROUP BY batch_no
            ";
        }

        $SQLsitedetail="
        SELECT internal_location_id
               ,title
        FROM internal_location
        WHERE internal_location_id = {$internal_location_id}
        ";
        $resultsitedetail = $db->sql_query($SQLsitedetail);
        $rowsitedetail = $db->sql_fetchrow($resultsitedetail);

        $SQLBatchNo = "
        SELECT  pp.batch_no AS batch_no
               ,pp.product_id
               ,pp.expiry_date
               {$sqlAppend}
        FROM po_product pp
        LEFT JOIN product p ON (p.product_id = pp.product_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = pp.purchase_order_id)
        {$leftjnAppend}
        WHERE pp.product_id = {$product_id}
        GROUP BY pp.batch_no
        {$stockTransferSQLForMultiSite}
        ";
        $resultBatchNo = $db->sql_query($SQLBatchNo);
        while ($rowBatchNo = $db->sql_fetchrow($resultBatchNo)) {
            $stockArray     = $fn->getStockForProductBatchWise($rowBatchNo['product_id'], $internal_location_id, $rowBatchNo['batch_no']);
            $OverallStock   = $stockArray['OverallStock'];
            $PurchasedQty   = $stockArray['PurchasedQty'];
            $SoldQty        = $stockArray['SoldQty'];
            $SalesReturnQty = $stockArray['SalesReturnQty'];
            $DamagedQty     = $stockArray['DamagedQty'];
            //$expiry_date    = $fn->getCPDate($rowBatchNo['expiry_date'], 'd-m-Y');
            $expiry_date    = $rowBatchNo['expiry_date'];

            $rows .= "
            <tr>
                <td>{$rowBatchNo['batch_no']}</td>
                <td>{$expiry_date}</td>
                <td>{$OverallStock}</td>
            </tr>
            ";

        }

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $thForSiteId = "<th>Location</th>";
        }

        $header = "
        <tr style='background-color:#EAEAE8;'>
        <th>Batch No</th>
        <th>Expiry Date</th>
        <th>Total Available Qty</th>
        </tr>
        ";

        $table = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        $formAction = '';

        $text = "
        <div class='linkPortalWrapper panel tradingin_inventory_batchWiseStockLink'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <div expanded='1'>
                        <div class='floatbox'>
                            <div class='float_left RightPanelHeading'>Batch Wise Stock</div>
                            <div class='txtRight'>
                              <div class='toggle'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='linkPortalDataWrapper'>
                        <form id='batchwisestock' class='' method='post' action='{$formAction}'>
                            <div id='batchStockPortalOuter'>
                                {$table}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getStockTransferDisplay(){
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $product_id           = $fn->getReqParam('product_id');
        $internal_location_id = $fn->getReqParam('internal_location_id');
        $type                 = $fn->getReqParam('type');

        $formAction = '';

        $text = "
        <div class='linkPortalWrapper panel tradingin_inventory_stockTransferLink'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <div expanded='1'>
                        <div class='floatbox'>
                            <div class='float_left RightPanelHeading'>Stock Transfer Linked</div>
                            <div class='txtRight'>
                              <div class='toggle'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='linkPortalDataWrapper'>
                        <form id='stockTransfer' class='' method='post' action='{$formAction}'>
                            <div id='invoicePortalOuter'>
                                {$this->getStockTransferDisplayDetail($product_id, $internal_location_id, $type)}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getStockTransferDisplayDetail($product_id, $internal_location_id, $type){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql = "AND (st.to_location = '{$internal_location_id}' OR st.from_location = '{$internal_location_id}')";
        if($type == "stockIn") {
            $appendSql = "AND st.to_location = '{$internal_location_id}'";
        }
        else if($type == "stockOut") {
            $appendSql = "AND st.from_location = '{$internal_location_id}'";
        }

        else if($type == "usedPatient") {
            $appendSql = "AND st.from_location = '{$internal_location_id}'
                          AND st.to_location = '4'";
        }

        $SQL = "
        SELECT  st.from_location
               ,st.to_location
               ,st.date
               ,(CASE 
                WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                THEN sth.qty * sth.pack_size
                ELSE sth.qty END) AS qty
        FROM stock_transfer_history sth
        LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
        WHERE sth.product_id = {$product_id}
        {$appendSql}
        AND st.stock_deducted = 1
        ";
        $result   = $db->sql_query($SQL);

        while ($rowST = $db->sql_fetchrow($result)) {

            $Sqlfrom = "
            SELECT title
            FROM internal_location
            WHERE internal_location_id = {$rowST['from_location']}
            ";
            $resultfrom = $db->sql_query($Sqlfrom);
            $from = $db->sql_fetchrow($resultfrom);

            $Sqlto = "
            SELECT title
            FROM internal_location
            WHERE internal_location_id = {$rowST['to_location']}
            ";
            $resultto = $db->sql_query($Sqlto);
            $to = $db->sql_fetchrow($resultto);

            $rows .= "
            <tr class='stockTransferRightPanelTr'>
                <td>{$fn->getCPDate($rowST['date'], 'd-m-Y')}</td>
                <td>{$from['title']}</td>
                <td>{$to['title']}</td>
                <td>{$rowST['qty']}</td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Date</th>
            <th>From Location</th>
            <th>To Location</th>
            <th>Qty</th>
        </tr>
        ";

        $text = "
        <table class='thinlist' width='100%'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     */
    function getUpdatedAdjustStockHistory(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $inventory_batchwise_stock_id = $fn->getReqParam('inventory_batchwise_stock_id');
        
        $rows = "";

        $SQL = "
        SELECT *
        FROM adjust_stock_log
        WHERE inventory_batchwise_stock_id = {$inventory_batchwise_stock_id}
        ORDER BY adjust_stock_log_id DESC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['adjust_stock']}</td>
                <td><i>{$row['created_by']} - {$row['creation_date']}</i></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Adjust Stock</th>
            <th>Created/Updated By</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }
}