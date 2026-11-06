<?
class CPL_Admin_Modules_Tradingsg_StockTransfer_View extends CP_Common_Lib_ModuleViewAbstract
{
   function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $text = '';
        $rows = '';
        $readonly = '';
        $OrderItems = '';

        $rowCounter = 0;

        $SQLdeleteHistory ="
        DELETE FROM stock_transfer_history
        WHERE stock_transfer_id NOT IN (SELECT stock_transfer_id FROM stock_transfer)
        ";
        $resultdelhis = $db->sql_query($SQLdeleteHistory);
        $deletehistory = $db->sql_fetchrow($resultdelhis);
        
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $Sqlfrom ="
            select title as from_location
            FROM internal_location  
            WHERE internal_location_id = '{$row['from_location']}'
            ";

            $resultfrom = $db->sql_query($Sqlfrom);
            $from = $db->sql_fetchrow($resultfrom);

            $stock_transfer_date = $fn->getCPDate($row['date'],"d-m-Y");

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $stock_transfer_date)}
            {$listObj->getListDataCell($from['from_location'])}
            {$listObj->getListDataCell($row['location_name'])}
            {$listObj->getListDataCell($row['status'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Date', 'date')}
        {$listObj->getListHeaderCell('From Location', 'location_name')}
        {$listObj->getListHeaderCell('To Location', 'from_location')}
        {$listObj->getListHeaderCell('Status', 'status')}
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $expNoEdit  = array('isEditable' => 0);
        
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $siteRec         = $fn->getRecordRowByID('site', 'site_id', $cpSiteIdSession);

        $newLocationUrl = 'index.php?_spAction=newLocation&lnkRoom=tradingsg_stockTransfer&showHTML=0';
        $newLocationUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Location' 
            w=600 h=560 href='' link='{$newLocationUrl}' callback='cpm.tradingsg.stockTransfer.afterNewLocation'>Add Location</a>";

        $sqlstocktransfrom = "
        SELECT internal_location_id
              ,title 
        FROM internal_location 
        WHERE internal_location_id != 4 
        ";

        $sqlstocktransto = "
        SELECT internal_location_id
              ,title 
        FROM internal_location 
        ";

        $expVl = array('sqlType' => 'OneField');
        
        $fieldset = "
        {$formObj->getDDRowBySQL('From Location', 'from_location', $sqlstocktransfrom, '')}
        {$formObj->getDDRowBySQL('To Location', 'to_location', $sqlstocktransto, '')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Select Location', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');
        $text = '';

        $record_id = $fn->getIssetParam($row, 'stock_transfer_id');

        $text .="
        {$comment->getView(array(
             'roomName' => 'tradingsg_stockTransfer'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $current_date = date('Y-m-d');
        $text = '';

        $text = "
        <div id='editDisplayLoad'>{$this->getEditDisplay($row['stock_transfer_id'], $row['from_location'])}</div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditDisplay($stock_transfer_id='', $site_id=''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $current_date = date('Y-m-d');
        $text = '';
        $rows = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if($stock_transfer_id == ''){
            $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        }

        /*if($site_id == ''){
            $site_id = $fn->getReqParam('site_id');
        }*/

        $SQLStockTransfer = "
        SELECT st.*
        FROM stock_transfer st
        WHERE st.stock_transfer_id = {$stock_transfer_id}
        ORDER BY st.date DESC
        ";
        $resultStockTransfer = $db->sql_query($SQLStockTransfer);
        $row = $db->sql_fetchrow($resultStockTransfer);

        $stock_transfer_status_arr = array('Request', 'Delivered', 'On Hold', 'Cancelled');
        $stock_transfer_id         = $row['stock_transfer_id'];
        $stock_transfer_date       = $fn->getCPDate($row['date'],"d-m-Y");

        $OrderItems = $this->getOrderItems($stock_transfer_id);

        $reuqestFormPdf   = "index.php?module=tradingsg_stockTransfer&_spAction=printExportAsPdf&id={$row['stock_transfer_id']}&printType=request&showHTML=0";
        $deliveryOrderPdf = "index.php?module=tradingsg_stockTransfer&_spAction=printExportAsPdf&id={$row['stock_transfer_id']}&printType=delivery&showHTML=0";

        $editableFalse = '';
        $buttonChange  = '';

        $appendSqlstocktrans = "";
        if($row['from_location'] == 1) {
            $appendSqlstocktrans = "WHERE internal_location_id != 4";
        }

        $sqlstocktrans = "
        SELECT internal_location_id
              ,title 
        FROM internal_location 
        {$appendSqlstocktrans}
        ";
        
        $expVl = array('sqlType' => 'OneField');

        if($row['lock_record'] == 1){
            $editableFalse = "disabled = '1'";

            $buttonChange .= "
            <a class='btn btn-info rollbackChanges' stock_transfer_id= '{$row['stock_transfer_id']}'>
                <span class='fa-refresh'></span>
                 Rollback Transaction
            </a>";

            $buttonChange .= "
            <a class='btn btn-danger deductFromStock' stock_transfer_id= '{$row['stock_transfer_id']}'>
                <span class='fa-check'></span>
                Deduct From Stock
            </a>";
        }else{
            $buttonChange = "
            <a class='btn btn-success completeTransaction' stock_transfer_id= '{$row['stock_transfer_id']}'>
                <span class='fa-lock'></span>
                Complete Transaction
            </a>";
        }

        if($row['status'] == 'Cancelled' || $row['status'] == 'Delivered'){
            $editableFalse = "disabled = '1'";
        }

        if($row['status'] == 'Cancelled'){
            $buttonChange = "<div class='CancelledButton btn-danger'>Cancelled</div>";
        }

        $expNoEdit    = '';
        //$fromLocation = $formObj->getDDRowBySQL('From Location', 'from_location', $sqlstocktrans, $row['from_location']);
        $toLocation   = $formObj->getDDRowBySQL('To Location', 'to_location', $sqlstocktrans, $row['to_location']);
        
        $Sqlfrom = "
        SELECT title
        FROM internal_location
        WHERE internal_location_id = {$row['from_location']}
        ";
        $resultfrom = $db->sql_query($Sqlfrom);
        $from = $db->sql_fetchrow($resultfrom);

        $fromLocation = "<div class='type-text ym-fbox-text row_from_location'>
                                <label for='fld_from_location'>From Location</label>
                                <div class='txt'>{$from['title']}</div>
                            </div>";

        if($row['stock_deducted'] == 1){
            $Sqlfrom = "
            SELECT title
            FROM internal_location
            WHERE internal_location_id = {$row['from_location']}
            ";
            $resultfrom = $db->sql_query($Sqlfrom);
            $from = $db->sql_fetchrow($resultfrom);

            $Sqlto = "
            SELECT title
            FROM internal_location
            WHERE internal_location_id = {$row['to_location']}
            ";
            $resultto = $db->sql_query($Sqlto);
            $to = $db->sql_fetchrow($resultto);

            $expNoEdit    = array('isEditable' => 0);

            $fromLocation = "<div class='type-text ym-fbox-text row_from_location'>
                                <label for='fld_from_location'>From Location</label>
                                <div class='txt'>{$from['title']}</div>
                            </div>";

            $toLocation   = "<div class='type-text ym-fbox-text row_to_location'>
                                <label for='fld_to_location'>To Location</label>
                                <div class='txt'>{$to['title']}</div>
                            </div>";

            $buttonChange = "<div class='DeliveredProducts btn-success'>Stock transferred successfully</div>";
        }

        $text = "
        <!--<div class='float_left btn btn-info mb10'>
             <a href='{$reuqestFormPdf}' target = 'blank' id='exportasPdfStockTransfer'><span class='fa-file-pdf-o'></span>Request Form</a>
        </div>
        <div class='float_left btn btn-info mb10'>
             <a href='{$deliveryOrderPdf}' target = 'blank' id='exportasPdfStockTransfer'><span class='fa-print'></span>Delivery Order</a>
        </div>-->
        <table class='list thinlist topTable'>
            <tr>
                <th>
                    {$formObj->getTBRow('Title', 'title', $row['title'], $expNoEdit)}
                </th>
                <th>
                    {$fromLocation}
                </th>
                <th>
                    {$toLocation}
                </th>
                <th>
                    {$formObj->getDateRow('Date', 'date', $row['date'], $expNoEdit)}
                </th>
                <th>
                    {$formObj->getDDRowByArr('Status', 'status', $stock_transfer_status_arr, $row['status'], $expNoEdit)}
                </th>
            </tr>
            <tr>
                <th>
                    {$formObj->getTBRow('Notes', 'notes', $row['notes'], $expNoEdit)}
                </th>
                <th colspan = '2'>
                    <div class='locationTitle'><label>Created By : </label>{$row['created_by']} {$row['creation_date']}
                    </div>
                </th>
                <th colspan = '2'>
                    <div class='locationTitle'><label>Modified By : </label>{$row['modified_by']} {$row['modification_date']}
                    </div>
                </th>
            </tr>
        </table>

        <div class='addProduct'>
            Search by Product : <input type='text' value='' id='fld_product_title' class='text' name='product_title' stock_transfer_id={$row['stock_transfer_id']} from_location='{$row['from_location']}' {$editableFalse}>
        </div>

        <input type='hidden' name='site_id' value={$cpSiteIdSession}>

        <div class = 'float_box'>
            <div class = 'float_left actionButtons'>
                {$buttonChange}
            </div>
        </div>

        <table class='list thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product Name</th>
                    <th>Batch No</th>
                    <th>Stock in From Location</th>
                    <th>Request Qty</th>
                    <th>Qty Delivered</th>
                    <th>Created By</th>
                    <th>Modified By</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id='orderItems'>
                {$OrderItems}
            </tbody>
        </table>
        <input type='hidden' name='from_location' value='{$row['from_location']}'>
        ";

        return $text;
    }

    /**
     *totalqty
     */
    function getOrderItems($stock_transfer_id = ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $text = '';
        $rows = '';
        $totalquantity = '';

        if ($stock_transfer_id == ''){
            $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        }

        $SqlStockTransferCount = "
        SELECT stock_transfer_history_id
        FROM stock_transfer_history
        WHERE stock_transfer_id = '{$stock_transfer_id}'
        ";
        $resultStockTransferCount  = $db->sql_query($SqlStockTransferCount);
        $numRowsStockTransferCount = $db->sql_numrows($resultStockTransferCount);

        $StockHistorySql = "
        SELECT p.title
              ,sh.qty
              ,sh.qty_requested
              ,sh.stock_transfer_history_id
              ,sh.created_by
              ,sh.product_id
              ,sh.modified_by
              ,sh.creation_date
              ,sh.modification_date
              ,sh.batch_no
              ,sh.pack_size
              ,sh.po_product_id
              ,st.stock_transfer_id 
              ,st.from_location
              ,st.to_location
              ,st.status
              ,st.lock_record
        FROM `stock_transfer_history` sh
        LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sh.stock_transfer_id)
        LEFT JOIN  `product` p ON (p.product_id = sh.product_id)
        WHERE p.published = '1' 
        AND sh.stock_transfer_id = {$stock_transfer_id}
        ORDER BY sh.stock_transfer_history_id
        ";
        $resultHistorySql= $db->sql_query($StockHistorySql);
        $rowCounter = 1;
        $batchwiseStock = 0;
        while ($rowz = $db->sql_fetchrow($resultHistorySql)) {
            if($rowz['from_location'] == "1") {
                $stockField = "current_stock";
            } else {
                $sqlLocation = "
                SELECT internal_location_id
                      ,title 
                FROM internal_location 
                WHERE internal_location_id = {$rowz['from_location']}
                ";
                $resultLocation = $db->sql_query($sqlLocation);
                $rowLocation    = $db->sql_fetchrow($resultLocation);
                $toLocation     = strtolower($rowLocation['title']);
                $toLocation     = str_replace(' ', '_', $toLocation);
                $stockField     = "{$toLocation}";
            }

            $SQLStock ="
            SELECT {$stockField}
            FROM inventory_batchwise_stock
            WHERE po_product_id = {$rowz['po_product_id']}
            ";
            $resultStock = $db->sql_query($SQLStock);
            $rowStock    = $db->sql_fetchrow($resultStock);
            $stock       = $rowStock[$stockField];
            $totalqty    = $stock + $rowz['qty'];
        
            $editableFalse = '';
            $expNoEdit     = '';
            $deleteLink = "<a href='#' class='deleteItem btn btn-danger' stock_transfer_history_id='{$rowz['stock_transfer_history_id']}' stock_transfer_id= '{$rowz['stock_transfer_id']}'>Delete</a>";
            
            if($rowz['status'] == 'Cancelled'){
                $editableFalse  = "disabled = '1'";
                $deleteLink     = "";
                $expNoEdit      = array('isEditable' => 0);
            }

            if($rowz['status'] == 'Delivered'){
                $deleteLink     = "";
                $editableFalse  = "disabled = '1'";
            }

            $editableFalseRequest = '';
            if($rowz['lock_record'] == 1){
                $editableFalseRequest  = "disabled = '1'";
            }

            $editableFalseDelivered = "disabled = '1'";
            if($cpSiteIdSession == 1){
                $editableFalseDelivered = "disabled = '1'";
            }

            if($rowz['lock_record'] == 1 && $rowz['status'] == 'Delivered'){
                $editableFalse  = "disabled = '1'";
                $totalqty       = $stock;
            }

            if($rowz['pack_size'] != '' && is_numeric($rowz['pack_size']) > 0) {
                $totalqty = $totalqty / $rowz['pack_size'];
                $totalqty = (int) $totalqty;
            }

            $rows .= "
            <tr>
                <td>
                    {$rowCounter}
                    <input type='hidden' class='stockTransfer_product_count' name='stockTransfer_product_count' value='{$numRowsStockTransferCount}'/>
                </td>
                <td class='w25p'>{$rowz['title']}</td>
                <td>{$rowz['batch_no']}</td>
                <td>{$totalqty}</td>
                <td class='w100'>
                    <input type='text' value='{$rowz['qty_requested']}' id='fld_Request_qty' class='text w100' name='request_qty' stock_transfer_history_id='{$rowz['stock_transfer_history_id']}' stock_transfer_id= '{$rowz['stock_transfer_id']}' stock='{$totalqty}' {$editableFalse} {$editableFalseRequest}>
                </td>
                <td class='w100'>
                    <input type='text' value='{$rowz['qty']}' id='fld_qty' class='text w100' name='qty' stock_transfer_history_id='{$rowz['stock_transfer_history_id']}' stock_transfer_id= '{$rowz['stock_transfer_id']}' stock='{$totalqty}' {$editableFalse} {$editableFalseDelivered}>
                </td>
                <td>{$rowz['created_by']}  {$rowz['creation_date']}</td>
                <td>{$rowz['modified_by']}  {$rowz['modification_date']} </td>
                <td>{$deleteLink}</td>
            </tr>
            ";
            $rowCounter++ ;        
        }

        $text = "
        {$rows}
        ";
        return $text;
        
    }

    /**
     *
     */
    function getNewLocation(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addLocation&lnkRoom=tradingsg_stockTransfer&showHTML=0";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Value', 'value')}
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $to_location   = $fn->getReqParam('to_location');
        $from_location = $fn->getReqParam('from_location');
        $date1         = $fn->getReqParam('date_1');
        $date2         = $fn->getReqParam('date_2');

        $sqlstocktransfrom = "
        SELECT internal_location_id
              ,title 
        FROM internal_location 
        WHERE internal_location_id != 4 
        ";

        $appendSql = "";
        if($from_location == 1) {
            $appendSql = "WHERE internal_location_id != 4";
        }

        $sqlstocktransto = "
        SELECT internal_location_id
              ,title 
        FROM internal_location 
        {$appendSql}
        ";

        $text = "
        <td>
            {$formObj->getDateRangeRow('Date:', 'date', $date1, $date2)}
        </td>
        <td>
            <select name='from_location'>
                <option value=''>From Location</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlstocktransfrom, $from_location)}
            </select>
        </td>    
        <td>
            <select name='to_location'>
                <option value=''>To Location</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlstocktransto, $to_location)}
            </select>
        </td>    
        ";


        return $text;
    }

    /**
     *
     */
    function getBatchProductSelectStock() {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $product_id        = $fn->getReqParam('product_id');
        $stockMain         = $fn->getReqParam('stock');
        $from_location     = $fn->getReqParam('from_location');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $site_id           = $fn->getSessionParam('cp_site_id');

        $appendSql   = '';
        $sqlAppendSt = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND po.site_id = {$site_id}";
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
              ,pp.expiry_date
              ,p.hsn AS hsn_code
              ,p.product_id AS product_id
              ,p.title AS main_product_title
              ,p.item_code AS main_product_code
              ,ibs.batch_no AS batch_no
              ,ibs.po_product_id
              ,ibs.{$stockField}
              ,(CASE 
                WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                THEN FLOOR(ibs.{$stockField} / pp.pack_size)
                ELSE ibs.{$stockField} END) AS stock
        FROM inventory_batchwise_stock ibs
        LEFT JOIN (po_product pp) ON (pp.po_product_id = ibs.po_product_id)
        LEFT JOIN (product p) ON (p.product_id = ibs.product_id)
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = ibs.purchase_order_id)
        WHERE ibs.product_id = {$product_id}
        AND po.status != 'Cancelled'
        {$appendSql}
        HAVING stock > 0
        ORDER BY ibs.po_product_id
        ";
        $resultPo = $db->sql_query($SQLPO);
        $numRows = $db->sql_numrows($resultPo);

        $rows = "";
        $count = 1;
        $batchwiseStock = 0;
        while ($rowPo = $db->sql_fetchrow($resultPo)){
            $selling_price = $rowPo['selling_price'];
            if($selling_price == ""){
                $selling_price = 0;
            }

            $appendSqlSiteStock = '';
            if($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSiteStock = "AND ibs.site_id = {$site_id}";
            }

            $SQLStock ="
            SELECT (CASE 
                    WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                    THEN FLOOR(ibs.{$stockField} / pp.pack_size)
                    ELSE ibs.{$stockField} END) AS stock
            FROM inventory_batchwise_stock ibs
            LEFT JOIN (po_product pp) ON (pp.po_product_id = ibs.po_product_id)
            WHERE ibs.po_product_id = {$rowPo['po_product_id']}
            {$appendSqlSiteStock}
            ";
            $resultStock = $db->sql_query($SQLStock);
            $rowStock    = $db->sql_fetchrow($resultStock);
            $stock       = $rowStock['stock'];
            
            $selling_price  = number_format($selling_price, 2);
            $expiry_date    = $rowPo['expiry_date'];
            $productNameRow = " <a class='batchProductAdd' stock_transfer_id='{$stock_transfer_id}' batch_no='{$rowPo['batch_no']}' po_product_id='{$rowPo['po_product_id']}' product_id='{$rowPo['product_id']}'>
                                    {$rowPo['title']}
                                </a>";
                                                    
            $poTd = "";
            $po_code = 'PO - '.$rowPo['po_code'];
            $po_code = "<a href='index.php?_topRm=pharmacy&module=tradingsg_purchaseOrder&_action=edit&record_id={$rowPo['purchase_order_id']}' target='_blank'><u>PO - {$rowPo['po_code']}</u></a>";

            $poTd = "<td>{$po_code}</td>";

            $rows .= "
            <tr>
                <td>{$productNameRow}</td>
                <td>{$rowPo['batch_no']}</td>
                <td class='txtCenter'><b>{$rowPo['stock']}</b></td>
                <td class='txtRight'>{$selling_price}</td>
                {$poTd}
            </tr>
            ";

            $count++;
            $mainProdTitle = $rowPo['main_product_title'];
            $mainProdCode  = $rowPo['main_product_code'];
        }

        $poTh = "<th>PO Code</th>";

        $header = "
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Batch No</th>
                <th class='txtCenter'>Stock</th>
                <th class='txtRight'>Selling Price</th>
                {$poTh}
            </tr>
        </thead>
        ";

        $text = "
        <div class='linkPortalWrapper tradingsg_pos_tradingsg_batchProductLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>{$mainProdCode} - {$mainProdTitle} - Stock Overall : {$stockMain}</div>
                    <div class='txtRight'>
                        <span class='count'>({$numRows})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form id='batchProductViewPo' class='batchProductViewPo' method='post'>
                    <table class='thinlist' id='batchProductTable'>
                        {$header}
                        <tbody>
                            {$rows}
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getBatchProductSelectStock1() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        
        $product_id = $fn->getReqParam('product_id');
        $site_id    = $fn->getReqParam('site_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        $SQLStockTransfer = "
        SELECT st.*
        FROM stock_transfer st
        WHERE st.stock_transfer_id = {$stock_transfer_id}
        ";
        $resultStockTransfer = $db->sql_query($SQLStockTransfer);
        $rowStockTransfer    = $db->sql_fetchrow($resultStockTransfer);

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
                   ,pp.po_product_id
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
            WHERE sth.product_id = {$product_id}
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
              ,pp.po_product_id
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
              ,'PURCHASE ORDER' AS stock_from
        FROM po_product pp
        LEFT JOIN product p ON (p.product_id = pp.product_id)
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pp.purchase_order_id)
        WHERE pp.product_id = {$product_id}
        AND po.status != 'Cancelled'
        {$appendSql}
        GROUP BY pp.batch_no
        {$stockTransferSQLForMultiSite}
        ";
        $resultPo = $db->sql_query($SQLPO);
        $numRows = $db->sql_numrows($resultPo);
        $rows = "";

        $SQLsitedetail="
        SELECT site_id
               ,title
        FROM site
        WHERE site_id = {$rowStockTransfer['from_location']}
        ";
        $resultsitedetail = $db->sql_query($SQLsitedetail);
        $rowsitedetail = $db->sql_fetchrow($resultsitedetail);

        $batchwiseStock = 0;
        while ($rowPo = $db->sql_fetchrow($resultPo)){
            $selling_price = $rowPo['selling_price'];
            if($selling_price == ""){
                $selling_price = 0;
            }

            $selling_price  = number_format($selling_price, 2);
            $productNameRow = "<input class='batchProductId' type='checkbox' name='batchProductId' product_id='{$rowPo['product_id']}' value='{$rowPo['batch_no']}'>";
            //$expiry_date    = $fn->getCPDate($rowPo['expiry_date'], 'd-m-Y');
            $expiry_date    = $rowPo['expiry_date'];
            $productNameRow = " <a class='batchProductAdd' stock_transfer_id='{$stock_transfer_id}' batch_no='{$rowPo['batch_no']}' product_id='{$rowPo['product_id']}' po_product_id='{$rowPo['po_product_id']}'>
                                    {$rowPo['title']}
                                </a>";

            $stockArray   = $fn->getStockForProductBatchWise($rowPo['product_id'], '', $rowPo['batch_no']);
            $stock        = $stockArray['OverallStock'];
            $SoldQty      = $stockArray['SoldQty'];
            $PurchasedQty = $stockArray['PurchasedQty'];

            if($PurchasedQty < $SoldQty) {
                $batchwiseStock = $PurchasedQty - $SoldQty;
            } else if($PurchasedQty >= $SoldQty) {
                if($batchwiseStock > 0) {
                    $batchwiseStock = $stock;
                } else {
                    $batchwiseStock = $batchwiseStock + $stock;
                }
            }

            if($batchwiseStock > 0) {
                $rows .= "
                <tr>
                    <td>{$productNameRow}</td>
                    <td>{$rowPo['batch_no']}</td>
                    <td class='txtRight'>{$rowPo['unit']}</td>
                    <td class='txtCenter'>{$batchwiseStock}</td>
                    <td class='txtRight'>{$selling_price}</td>
                    <td class='txtCenter'>{$rowPo['gst']}</td>
                    <td>{$expiry_date}</td>
                    <td>{$rowPo['hsn_code']}</td>
                </tr>
                ";
            }

            $mainProdTitle = $rowPo['main_product_title'];
            $mainProdCode  = $rowPo['main_product_code'];
            $stock_from    = $rowPo['stock_from'];
        }

        $header = "
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Batch No</th>
                <th class='txtRight'>UOM</th>
                <th class='txtCenter'>Qty</th>
                <th class='txtRight'>Selling Price</th>
                <th class='txtCenter'>GST(%)</th>
                <th>Expiry Date</th>
                <th>HSN Code</th>
            </tr>
        </thead>
        ";

        $text = "
        <div class='linkPortalWrapper tradingsg_pos_tradingsg_batchProductLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>{$mainProdCode} - {$mainProdTitle}</div>
                    <div class='txtRight'>
                        <span class='count'>({$numRows})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form id='batchProductViewPo' class='batchProductViewPo' method='post'>
                    <table class='thinlist' id='batchProductTable'>
                        {$header}
                        <tbody>
                            {$rows}
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        ";

        return $text;
    }
}