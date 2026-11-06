<?
class CPL_Admin_Modules_Tradingsg_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $current_month = $fn->getReqParam('current_month');
        $current_year  = $fn->getReqParam('current_year');

        $site_id = $fn->getSessionParam('cp_site_id');

        $text = '';
        $rows = '';
        $count = 0;
        $totalCost = 0;
        foreach ($dataArray as $row){

            /*$SQLTotal = "
            SELECT SUM(pop.qty * pop.cost_price) AS total_cost
                  ,SUM(((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) AS Discount_Total
                  ,MAX(pop.gst) AS Gst_Percent
            FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultTotal     = $db->sql_query($SQLTotal);
            $rowTotal        = $db->sql_fetchrow($resultTotal);
            $totalCost       = $rowTotal['total_cost'] - $rowTotal['Discount_Total'];
            $discountOverall = ($totalCost * $row['overall_discount']) / 100;
            $totalCost       = $totalCost - $discountOverall;
            $gstCost         = ($totalCost * $rowTotal['Gst_Percent']) / 100;
            $totalCost       = $totalCost + $gstCost;
            $totalCost       = number_format(round($totalCost), 2);*/


            $po_code = "";
            if($row['po_code'] != "") {
                $po_code = 'PO - '.$row['po_code'];
            }

            $overall_discount = $row['overall_discount'];

            if($overall_discount == ''){
                $overall_discount = 0;
            }

            $SQLTotal = "
            SELECT SUM(pop.qty * pop.cost_price) AS total_cost
                  ,SUM((((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
                  ,SUM((((pop.qty * pop.cost_price) - (((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
            FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal    = $db->sql_fetchrow($resultTotal);
            $totalCost   = $rowTotal['total_cost'] - $rowTotal['Discount_Total'] + $rowTotal['GST_Total'];
            $totalCost   = number_format(round($totalCost), 2);

            if($row['creation_date'] != ''){
                $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y h:i a');
                $created_update_by = $row['created_by'].' <i>'.$creation_date.'</i>';
            }

            if($row['modification_date'] != ''){
                $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y h:i a');
                $created_update_by = $row['modified_by'].' <i>'.$modification_date.'</i>';
            }

            if($row['creation_date'] != '' && $row['modification_date'] != ''){
                $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y h:i a');
                $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y h:i a');
                $created_update_by = $row['modified_by'].' <i>'.$modification_date.'</i>';
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $po_code)}
            {$listObj->getListDataCell($row['supplier_name_substr'])}
            {$listObj->getListDataCell($totalCost, 'Right')}
            {$listObj->getListDataCell($row['supplier_inv_code'])}
            {$listObj->getListDateCell($row['invoice_date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['purchase_order_date'])}
            {$listObj->getListDataCell($created_update_by)}
            {$listObj->getListRowEnd($row['purchase_order_id'])}
            ";
            
            $count++;
        }

        $rows = $listObj->getDisplayListRows($rows);

        $formActionProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItemList&showHTML=0";

        $addExistingProduct = "
        <div class='float_left'>
            <a class='btn btn-warning' id='AddProductList' href='{$formActionProduct}'>Add Existing Product</a>
        </div>";

        $formActionNewProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddNewProductList&showHTML=0";

        $addNewProduct = "
        <div class='float_left'>
            <a class='btn btn-primary' id='AddNewProductList' href='{$formActionNewProduct}'>Add 
            Medicines</a>
        </div>";

        $salesOverallArray  = $this->model->getTotalPharmacySalesAmount('', $current_month, $current_year);
        $totalOverallAmount = $salesOverallArray['totalAmount'];
        $grandTotal         = number_format($totalOverallAmount, 2);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('PO Code', 'po.po_code')}
        {$listObj->getListHeaderCell('Supplier Name', 'su.company_name')}
        {$listObj->getListHeaderCell('PO Value', 'amount', 'txtRight')}
        {$listObj->getListHeaderCell('Invoice No', 'po.supplier_inv_code')}
        {$listObj->getListHeaderCell('Invoice Date', 'po.invoice_date')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Purchase Date', 'po.purchase_order_date')}
        {$listObj->getListHeaderCell('Updated By', '')}
        {$listObj->getListHeaderEnd()}
        <tr bgcolor='#CFDBE2'>
            <th colspan='4' class='totalFeesInList'></th>
            <th class='totalFeesInList txtRight' colspan='2'>Grand Total: {$grandTotal}</th>
        </tr>
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        //$newSupplierUrl = "index.php?module=tradingsg_purchaseOrder&_spAction=newSupplier&showHTML=0";
        $newSupplierUrl = 'index.php?_spAction=newSupplier&lnkRoom=tradingsg_supplier&showHTML=0';


        //$newSupplierUrl = "<a id='addSupplier' href='{$newSupplierUrl}'>New</a>";

        $newSupplierUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Contact' 
            w=600 h=560 href='' link='{$newSupplierUrl}' callback='cpm.tradingsg.purchaseOrder.afterNewSupplier'>Add Supplier</a>";

        $expSupplier = array('hideFirstOption' => 1);
        $sqlSupplier = "
        SELECT supplier_id
              ,company_name
        FROM supplier 
        WHERE status = 'Active'
        ORDER BY company_name 
         ";

        $fieldset = "
        {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, '', $expSupplier)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset)}
        ";
        return $text;
    }

    //==================================================================//
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expNoEdit = array('isEditable' => 0);

        $expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('trading_contact');

        $expCompany = array('sqlType' => 'OneField');

        $expVl = array('sqlType' => 'OneField');
        $sqlPriority = $fn->getValuelistSql('quotePriority');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $statusArr = $cpCfg['m.trading.purchaseOrder.statusArr'];
        if($row['status'] == 'confirmed'){       //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $expStaff   = array('detailValue' => $row['staff_name']);

        $actionButtons = '';
        //$Patient_visit_link = "index.php?_topRm=main&module=tradingsg_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";

        $urlPOtoExcel = "index.php?module=tradingsg_purchaseOrder&_spAction=printPOtoExcel&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left btn btn-primary mb5'>
            <a href='{$urlPOtoExcel}' id='printExcel'>Export to Excel</a>
        </div>
        ";

        $urlPOtoPDF = "index.php?module=tradingsg_purchaseOrder&_spAction=printPOtoPDF&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $urlPOwiwthpricetoPDF = "index.php?module=tradingsg_purchaseOrder&_spAction=printPOwithpricetoPDF&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left btn btn-info mb5'>
            <a href='{$urlPOtoPDF}' target='blank' id='printPDF'>Purchase Order PDF</a>
        </div>
        <div class='float_left btn btn-info mb5'>
            <a href='{$urlPOwiwthpricetoPDF}' target='blank' id='printPDF'>Purchase Order with price PDF</a>
        </div>
        <div class='float_left btn btn-warning mb5 duplicatePO'>
            <a href='#' class='duplicatePO' purchase_order_id='{$row['purchase_order_id']}'>Re-Order</a>
        </div>
        ";

        $print ="
        <div class='floatbox actionBtnsDetail'>
            <div class='purchaseOrderRightpanelButtons floatbox'>
                {$actionButtons}
            </div>
        </div>
        ";

        //$sqlSupplier = $fn->getDDSql('tradingsg_supplier');
        $expSupplier = array('hideFirstOption' => 1);
        $sqlSupplier = "
        SELECT supplier_id
              ,company_name
        FROM supplier 
        WHERE status = 'Active'
        ORDER BY company_name 
         ";

        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

        $paymentstatusArr = array(
              "Due"
             ,"Paid"
             ,"Partially Paid"
             ,"Cancelled"
        );

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Purchase Order Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('PO Code', 'po_code', $row['po_code'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Title', 'title', $row['title'])}</td>
                                <td>{$formObj->getDDRowByArr('Status', 'status', $statusArr, $row['status'])}</td>
                                <td>{$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, $row['company_id_supplier'], $expSupplier)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDateRow('Purchase Date', 'purchase_order_date', $row['purchase_order_date'])}</td>
                                <td>{$formObj->getTBRow('Invoice No', 'supplier_inv_code', $row['supplier_inv_code'])}</td>
                                <td>{$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}</td>
                                <td>{$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}</td>
                                <td>{$formObj->getTARow('Notes to Supplier', 'notes', $row['notes'])}</td>
                                <td>{$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'])}</td>
                                <td>{$formObj->getDDRowByArr('Payment Status', 'payment_status', $paymentstatusArr, $row['payment_status'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'])}</td>
                                <input type='hidden' name='purchase_order_id' value='{$row['purchase_order_id']}' />
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        $text = '';
        $record_id = $fn->getIssetParam($row, 'purchase_order_id');
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');

        $sqlpurchaseorder = "
        SELECT po.*
        FROM purchase_order po
        WHERE po.purchase_order_id = {$row['purchase_order_id']}
        ";

        $resultpurchaseorder = $db->sql_query($sqlpurchaseorder);
        $rowpurchaseorder = $db->sql_fetchrow($resultpurchaseorder);

        if ($rowpurchaseorder['purchase_order_id'] != '') {
            $text .="
            <div class='linkPortalWrapper'>
                <div expanded='1' class='header'>
                    <div class='floatbox'>
                        <div class='toggle'></div>
                    </div>
                </div>
                <div>
                    <div class='linkPortalDataWrapper'>
                        <div id='tabs' class='mb20'>
                            <ul>
                                <li class='first'>
                                    <a href='#tabs-1' class='linkedMedicines'>Linked Medicines</a>
                                </li>
                                <li class='first'>
                                    <a href='#tabs-2' class='addMedicines'>Add Medicines</a>
                                </li>
                            </ul>
                            <div id='tabs-1'>
                                <div id='productLinkPortal'>
                                    {$this->getAddProduct($row['purchase_order_id'], $row['company_id_supplier'])}
                                </div>
                            </div>
                            <div id='tabs-2'>
                                <div id='addProductsPortal'>
                                    {$this->getAddNewProduct($row['purchase_order_id'], $row['company_id_supplier'], $row['status'])}
                                </div>
                            </div>
                            <div class='tab-footer'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ";
        }

        $text .="
        {$comment->getView(array(
             'roomName' => 'tradingsg_purchaseOrder'
            ,'recordId' => $record_id
        ))}
        {$media->getRightPanelMediaDisplay('Picture', 'tradingsg_purchaseOrder', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_purchaseOrder', 'attachment', $row)}
        ";

        return $text;
    }

    //==================================================================//
    /**
     *
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $status        = $fn->getReqParam('status');
        $supplier_id   = $fn->getReqParam('supplier_id');
        $current_month = $fn->getReqParam('current_month');
        $current_year  = $fn->getReqParam('current_year');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $sqlSupplier = "
        SELECT supplier_id
              ,company_name
        FROM supplier 
        WHERE status = 'Active'
        ORDER BY company_name 
         ";

         $currentMonthArray = array (
            '01' => 'January'
           ,'02' => 'February'
           ,'03' => 'March'
           ,'04' => 'April'
           ,'05' => 'May'
           ,'06' => 'June'
           ,'07' => 'July'
           ,'08' => 'August'
           ,'09' => 'September'
           ,'10' => 'October'
           ,'11' => 'November'
           ,'12' => 'December'
        );

        if($current_month == ""){
            $current_month = date('m');
        }
        if($current_year == ""){
            $current_year = date('Y');
        }

        $sqlYear = "
        SELECT DISTINCT DATE_FORMAT(p.purchase_order_date, '%Y')
        FROM purchase_order p 
        WHERE p.purchase_order_date != ''
        ";

        $text = "
        <td>
            <select name='current_year' class='leadStaffYearFilter'>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $current_year)}
            </select>
        </td>
        <td>
            <select name='current_month'>
                {$cpUtil->getDropDownFromArr($currentMonthArray, $current_month)}
           </select>
        </td>
        <td>
            <select name='supplier_id'>
                <option value=''>Supplier</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.purchaseOrder.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select class='w125' name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddProduct($purchase_order_id='' ,$supplier_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($purchase_order_id == ''){
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        if($supplier_id == ''){
            $supplier_id = $fn->getReqParam('supplier_id');
        }

        $Product  = $this->getAddProductDetail($purchase_order_id);
        $recCount = $fn->getRecordCount('po_product', "purchase_order_id = '{$purchase_order_id}'");
        $rowPo    = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);

        $header ="
        <thead>
            <th class='click-all-top'>
                <a href='#' class='check-all'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                </a>
                <a href='#' class='uncheck-all'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                </a>
            </th>
            <th>S.No</th>
            <th>Medicine Code</th>
            <th>Medicine Title</th>
            <th class='txtRight'>Rate</th>
            <th class='txtRight'>MRP</th>
            <th>GST%(Cost Price)</th>
            <th>Batch No</th>
            <th>Expiry Date</th>
            <th>Pack Size</th>
            <th>Qty</th>
            <th>Damaged Qty</th>
            <th>Added to Stock</th>
            <th>Qty Balance</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Edit</th>
            <th>History</th>
            <th>Delete</th>
        </thead>
        ";

        $formActionProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItem&purchase_order_id={$purchase_order_id}&supplier_id={$supplier_id}&showHTML=0";

        $add = "<div class='float_left'>
                    <a class='btn btn-info' id='AddProduct' href='{$formActionProduct}' supplier_id='{$supplier_id}' purchase_order_id='{$purchase_order_id}'>Add Existing Product</a>
                </div>";

        $formActionNewProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddNewProduct&purchase_order_id={$purchase_order_id}&supplier_id={$supplier_id}&showHTML=0";

        $addNewProduct = "
        <div class='float_left'>
            <a class='btn btn-primary' id='AddNewProduct' href='{$formActionNewProduct}' supplier_id='{$supplier_id}' purchase_order_id='{$purchase_order_id}'>Add Medicines</a>
        </div>";

        $allQtyDelivered = "<a class='btn btn-danger qtyAllDelivered' purchase_order_id='{$purchase_order_id}'>Add all Qty to Stock</a>";

        $overall_discount = $rowPo['overall_discount'];
        if($overall_discount == ''){
            $overall_discount = 0;
        }

        $SQLTotal = "
        SELECT SUM(pop.qty_requested * pop.cost_price) AS total_cost
              ,SUM((((pop.qty_requested * pop.cost_price) * pop.discount_percentage) / 100)
               + (((pop.qty_requested * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
              ,SUM((((pop.qty_requested * pop.cost_price) - 
                (((pop.qty_requested * pop.cost_price) * pop.discount_percentage) / 100)
                 - (((pop.qty_requested * pop.cost_price) * 
                    {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
        FROM po_product pop WHERE pop.purchase_order_id = {$rowPo['purchase_order_id']}
        ";
        $resultTotal   = $db->sql_query($SQLTotal);
        $rowTotal      = $db->sql_fetchrow($resultTotal);
        $totalCost     = $rowTotal['total_cost'] - $rowTotal['Discount_Total'] + $rowTotal['GST_Total'];
        $Grand_Total   = number_format(round($totalCost), 2);
        $discountTotal = number_format(round($rowTotal['Discount_Total']), 2);

        $SQLTotalPack = "
        SELECT SUM(CASE 
                    WHEN pop.pack_size REGEXP '^[+-]?[0-9]+$'
                    THEN ((pop.qty_requested * pop.pack_size) * (pop.cost_price / pop.pack_size))
                    ELSE (pop.qty_requested * pop.cost_price) END ) AS total_cost
              ,SUM((((pop.qty_requested * pop.cost_price) * pop.discount_percentage) / 100)
               + (((pop.qty_requested * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
              ,SUM((((pop.qty_requested * pop.cost_price) - 
                (((pop.qty_requested * pop.cost_price) * pop.discount_percentage) / 100)
                 - (((pop.qty_requested * pop.cost_price) * 
                    {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
        FROM po_product pop WHERE pop.purchase_order_id = {$rowPo['purchase_order_id']}
        ";
        $resultTotalPack      = $db->sql_query($SQLTotalPack);
        $rowTotalPack         = $db->sql_fetchrow($resultTotalPack);
        $totalCostPack        = $rowTotalPack['total_cost'] - $rowTotalPack['Discount_Total'] + $rowTotalPack['GST_Total'];
        $Grand_Total_PackSize = number_format(round($totalCostPack), 2);
        //$Grand_Total_PackSize = "";
        /*$SQLGrandTotal = "
        SELECT  SUM(qty_requested * cost_price) AS Grand_Total
               ,SUM(qty * cost_price) AS Grand_Total_Delivered
        FROM po_product
        WHERE purchase_order_id = '{$purchase_order_id}'
        ";
        $resultGrandTotal = $db->sql_query($SQLGrandTotal);
        $rowGrandTotal    = $db->sql_fetchrow($resultGrandTotal);

        $Grand_Total = number_format($rowGrandTotal['Grand_Total'], 2);
        $Grand_Total_Delivered = number_format($rowGrandTotal['Grand_Total_Delivered'], 2);*/

        $viewMedicinesLink = "index.php?_topRm=main&module=tradingsg_purchaseOrder&_spAction=ViewMedicines&purchase_order_id={$purchase_order_id}&supplier_id={$supplier_id}&showHTML=0";
        $ListofMedicines =  "<a href='{$viewMedicinesLink}' class='btn btn-primary viewMedicines'>List of Medicines</a>";
        $ListofMedicines = "";

        $text = "
        <div class='header'>
            <div class='floatbox'>
                <div class='float_left'>
                    {$allQtyDelivered}
                    {$ListofMedicines}
                </div>
                <div class='float_right grandTotalPurchasePo'>
                    Grand Total: {$Grand_Total}
                </div>
                <div class='float_right grandTotalPurchasePo'>
                    Pack Size Grand Total: {$Grand_Total_PackSize}
                </div>
                <div class='float_right disountTotalValuePurchaseOrderRightPanel'>
                    Discount: {$discountTotal}
                </div>
                <div class='float_right btn btn-info applyCustomerDiscount'>Apply</div>
                <div class='float_right grandTotalPurchasePoDiscount'>
                    {$formObj->getTBRow('Overall Discount %', 'overall_discount', $rowPo['overall_discount'])}
                </div>
            </div>
        </div>
        <form class='purchaseOrderPoProduct'>
            <table class='renewallist room-poProduct-table'>
                {$header}
                <tbody id='AddProductPortal'>
                    {$Product}
                </tbody>
            </table>
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getAddProductDetail($purchase_order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if($purchase_order_id == ''){
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        $po_product_id = $fn->getReqParam('po_product_id');

        $rows  = "";

        $SQL="
        SELECT pop.*
              ,pop.cost_price AS price
              ,pop.selling_price
              ,p.title AS product
              ,p.item_code
              ,p.product_code
        FROM po_product pop
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        LEFT JOIN (company com) ON (pop.supplier_id = com.company_id)
        WHERE purchase_order_id = '{$purchase_order_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $qty_balance = '';
        $totalAmount = 0;
        $actualTotalAmount = 0;

        while ($row = $db->sql_fetchrow($result)) {

        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');


            $creation                = $row['created_by'].' '.$creation_date;
            $modification            = $row['modified_by'].' '.$modification_date;
            $formActionEditPOProduct = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=editPoProductRecordForm&po_product_id={$row['po_product_id']}&showHTML=0";
            $editPORecordLink        = "<a class='EditPoProduct' href='{$formActionEditPOProduct}' po_product_id='{$row['po_product_id']}' purchase_order_id='{$row['purchase_order_id']}'>
                                            <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                                        </a>";
            $viewHistoryUrl = "index.php?_topRm=inventory&module=tradingsg_purchaseOrder&_spAction=previousOrderForClient&po_product_id={$row['po_product_id']}&showHTML=0";
            $viewRecordHistoryLink = "
            <a href='{$viewHistoryUrl}' po_product_id='{$row['po_product_id']}' class='productViewHistory'><u>View History</u></a>";

            $deletePORecordLink      = "<div class='float_right'>
                                            <a class='deletePoProduct' href='#'  po_product_id='{$row['po_product_id']}' purchase_order_id='{$row['purchase_order_id']}'>
                                                <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                                            </a>
                                        </div>
                                        ";

            $qty_balance = $row['qty_requested'] - $row['qty'];

            $appendSqlSiteStock = '';
            if($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSiteStock = "site_id = {$cpSiteIdSession}";
            }
            
            $SQLStock ="
            SELECT current_stock
            FROM inventory_batchwise_stock
            WHERE po_product_id = {$row['po_product_id']}
            {$appendSqlSiteStock}
            ";
            $resultStock = $db->sql_query($SQLStock);
            $rowStock    = $db->sql_fetchrow($resultStock);
            $stock       = $rowStock['current_stock'];
            
            $stockDetail = '';
            $inputRow = "<input class='poProductId' type='checkbox' name='poProductId[]' value='{$row['po_product_id']}'>";

            $totalAmount = $row['qty_requested'] * $row['price'];
            $actualTotalAmount = $row['qty'] * $row['price'];

            $totalAmountFormatted       = number_format($totalAmount, 2);
            $actualTotalAmountFormatted = number_format($actualTotalAmount, 2);
            $unitPrice                  = number_format($row['price'], 2);

            $gstVal = $row['price'] * $row['gst'] / 100;
            $gstVal = number_format($gstVal, 2);

            $productCodeTd = "<td>{$row['item_code']}</td>";

            /*$expiry_date = '';
            
            if($row['expiry_date'] != ""){
                $expiry_date = $fn->getCPDate($row['expiry_date'], 'd-m-Y');
            }*/

            $expiry_date = $row['expiry_date'];

            /*if($row['status'] == "Closed") {
                $editPORecordLink   = "";
                $deletePORecordLink = "";
            }*/

            $rows .= "
                <tr poRowProduct[] = {$row['po_product_id']}>
                    <td>{$inputRow}</td>
                    <td>{$count}</td>
                    {$productCodeTd}
                    <td>
                        <a class='creationModificationPo' po_product_id='{$row['po_product_id']}'>
                            <u>{$row['product']}</u>
                        </a>
                    </td>
                    <td align='Right'>{$row['cost_price']}</td>
                    <td align='Right'>{$row['selling_price']}</td>
                    <td>{$gstVal}({$row['gst']}%)</td>
                    <td>{$row['batch_no']}</td>
                    <td>{$expiry_date}</td>
                    <td>{$row['pack_size']}</td>
                    <td>{$row['qty_requested']}</td>
                    <td>{$row['damage_qty']}</td>
                    <td>{$row['qty']}</td>
                    <td>{$qty_balance}</td>
                    <td>{$stock}</td>
                    <td>{$row['status']}</td>
                    <td align='Right'>{$totalAmountFormatted}</td>
                    <td>{$editPORecordLink}</td>
                    <td>{$viewRecordHistoryLink}</td>
                    <td>{$deletePORecordLink}</td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal' colspan='11'><font>No Records Linked</font></td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }

    /**
     *
     */
    function getEditPoProductRecordForm() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $po_product_id = $fn->getReqParam('po_product_id');
        $po_productRec = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
        $productRec = $fn->getRecordRowByID('product', 'product_id', $po_productRec['product_id']);
        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=editPoProductRecordSubmit&showHTML=0";

        if($po_productRec['status'] == ''){
            $po_productRec['status'] = 'In progress';        
        }

        $expNoEdit = '';

        if($po_productRec['qty'] > 0){
            $expNoEdit = array('isEditable' => 0);
        }

        $sqlMedicineCompany = "
        SELECT medicine_company_id
               ,medicine_company_name
        FROM medicine_company
        WHERE published = 1
        ORDER BY medicine_company_name
        ";

        $fieldset1 = "
        {$formObj->getTBRow('Pack Size', 'pack_size', $po_productRec['pack_size'])}
        {$formObj->getTBRow('Batch No', 'batch_no', $po_productRec['batch_no'])}
        {$formObj->getTBRow('Expiry Date', 'expiry_date', $po_productRec['expiry_date'])}
        {$formObj->getTBRow('Quantity', 'qty', $po_productRec['qty_requested'])}
        {$formObj->getTBRow('Free', 'free_items', $po_productRec['free_items'])}
        {$formObj->getTBRow('Damaged Qty', 'damage_qty', $po_productRec['damage_qty'])}
        {$formObj->getTBRow('Add to Stock', 'qty_delivered', $po_productRec['qty'])}
        {$formObj->getTBRow('Rate', 'cost_price', $po_productRec['cost_price'])}
        {$formObj->getTBRow('MRP', 'selling_price', $po_productRec['selling_price'])}
        {$formObj->getTBRow('Discount %', 'discount_percentage', $po_productRec['discount_percentage'])}
        ";

        $fieldset2 = "
        {$formObj->getTBRow('Hsn', 'hsn', $po_productRec['hsn'])}
        {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.purchaseOrder.statusArr'], $po_productRec['status'])}
        ";
        
        if($po_productRec['status'] == "Closed") {
            $fieldset1 = "";
            $fieldset2 = "
            <input type='hidden' name='status' value='{$po_productRec['status']}' />
            ";
        }

        $text = "
        <form id='EditPoProductForm' class='EditPoProductForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            {$formObj->getTBRow('Medicine Name', 'product_title', $productRec['title'], $expNoEdit)}
            <input type='hidden' name='product_id_edit_hidden' class='product_id_hidden' value='{$po_productRec['product_id']}'>
            {$formObj->getDDRowBySQL('Mfr Company', 'medicine_company', $sqlMedicineCompany, $po_productRec['medicine_company_id'])}
            {$fieldset1}
            {$formObj->getTBRow('GST %', 'gst', $po_productRec['gst'])}
            {$fieldset2}
            <input type='hidden' name='po_product_id' value='{$po_product_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getAddMultipleLineItem() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $supplier_id       = $fn->getReqParam('supplier_id');
        $supplierRec       = $fn->getRecordRowByID('supplier', 'supplier_id', $supplier_id);

        /*$sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";

        $product    = "
        <select name='product_id[]' class='poProduct'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlproduct)}
        </select>
        ";*/

        /*$sqlSupplier = "
        SELECT company_id
             , company_name AS supplier_name
        FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";

        $sqlSupplier = $fn->getDDSql('tradingsg_company');

        $Supplier    = "
        <select name='company_id_supplier[]' class='poProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";*/

        $status = $fn->getReqParam('status');

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price           = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]' disabled>";
        $costPrice       = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]' disabled>";
        $gst             = "<input type='text' value='' id='gst' class='text poGst' name='gst[]' disabled>";
        $qty             = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code  = "<div class='inventoryCode'></div>";
        $tag_no          = "<div class='tagNo'></div>";

        $productCodeTh = "";
        $productCodeTd = "";
        if ($cpCfg['cp.serialKeyActive'] == "SULB-DHEO-0R6K-59CL" || $cpCfg['cp.serialKeyActive'] == "YODX0-9DT58-VCZ5W-A8XXB") {
            $product_code  = "<div class='productCode'></div>";
            $productCodeTd = "<td>{$product_code}</td>";
            $productCodeTh = "<th>Product Code</th>";
        }else{
            $item_code     = "<div class='itemCode'></div>";
            $productCodeTd = "<td>{$item_code}</td>";
            $productCodeTh = "<th>Item Code</th>";
        }

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            {$productCodeTd}
            <td>{$tag_no}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            {$productCodeTd}
            <td>{$tag_no}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            {$productCodeTd}
            <td>{$tag_no}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            {$productCodeTd}
            <td>{$tag_no}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            {$productCodeTd}
            <td>{$tag_no}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRow button mb10'>Add Item</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr>{$newRow} **Please search products below, related to {$supplierRec['company_name']}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Product</th>
            <th>Inventory code</th>
            {$productCodeTh}
            <th>Tag No</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Cost Price (without GST)</th>
            <th class='txtCenter'>Selling Price (without GST)</th>
            <th class='txtCenter'>GST %</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItemSubmit&showHTML=0";

        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            <input type='hidden' name='supplier_id' value='{$supplier_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleLineItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        /*$sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";

        $product    = "
        <select name='product_id[]' class='poProduct'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlproduct)}
        </select>
        ";*/

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' value=''>
        ";
        $price          = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]' disabled>";
        $qty            = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $clear          = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code = "<div class='inventoryCode'></div>";
        $gst            = "<input type='text' value='' id='gst' class='text poGst' name='gst[]' disabled>";
        $costPrice      = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]' disabled>";
        $tag_no         = "<div class='tagNo'></div>";

        $productCodeTd = "";
        if ($cpCfg['cp.serialKeyActive'] == "SULB-DHEO-0R6K-59CL" || $cpCfg['cp.serialKeyActive'] == "YODX0-9DT58-VCZ5W-A8XXB") {
            $product_code  = "<div class='productCode'></div>";
            $productCodeTd = "<td>{$product_code}</td>";
        }else{
            $item_code     = "<div class='itemCode'></div>";
            $productCodeTd = "<td>{$item_code}</td>";
        }

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            {$productCodeTd}
            <td>{$tag_no}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddMultipleLineItemList() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title_list[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]' disabled>";
        $costPrice = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]' disabled>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]' disabled>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code = "<div class='inventoryCode'></div>";
        $item_code = "<div class='itemCode'></div>";
        $supplier = "<div class='supplier'></div>
        <input type='hidden' name='supplier_id[]' class='supplier_id_hidden' value=''>";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRowList button mb10'>Add Item</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Product</th>
            <th>Inventory code</th>
            <th>Item code</th>
            <th>Supplier</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Cost Price (without GST)</th>
            <th class='txtCenter'>Selling Price (without GST)</th>
            <th class='txtCenter'>GST %</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItemListSubmit&showHTML=0";

        $text = "
        <form id='addMultipleLineItemListForm' class='addMultipleLineItemListForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='list' id=''>
            <tr>
                <td>{$newRow}</td>
                <td>Add Qty for all Products: <input type='text' value='' class='text allQty' name='qty_all'>
                <a class='btn btn-info applyQtyAll' href='#'>Apply</a></td>
                <!--<td>Search: <input type='text' value='' class='text findWordTitle' name='find'>
                <a class='btn btn-success findWord' href='#'>Find</a></td>-->

                <td><a class='btn btn-primary loadMOL' href='#'>Load products <= Mol</a></td>
                <td><a href='#' class='btn btn-danger clearAllItem'>Clear All</a></td>
            </table>
            </tr>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleLineItemList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title_list[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]' disabled>";
        $costPrice = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]' disabled>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]' disabled>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code = "<div class='inventoryCode'></div>";
        $item_code = "<div class='itemCode'></div>";
        $supplier = "<div class='supplier'></div>
        <input type='hidden' name='supplier_id[]' class='supplier_id_hidden' value=''>";


        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddNewProduct($purchase_order_id = '', $supplier_id = '', $status = '') {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');

        if($purchase_order_id == '') {
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        if($supplier_id == '') {
            $supplier_id = $fn->getReqParam('supplier_id');
        }
        
        if($status == '') {
            $status = $fn->getReqParam('status');
        }

        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl   = array('sqlType' => 'OneField');
        $rows    = '';

        $sqlMedicineCompany = "
        SELECT medicine_company_id
               ,medicine_company_name
        FROM medicine_company
        WHERE published = 1
        ORDER BY medicine_company_name
        ";

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice  = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price            = "<input type='text' value='0' id='price' class='text lineItemDescription' name='price[]'>";
        $cost_price       = "<input type='text' value='0' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst              = "<input type='text' value='0' id='gst' class='text poGst' name='gst[]'>";
        $qty              = "<input type='text' value='0' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered    = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear            = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $hsn              = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit             = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $pack_size        = "<input type='text' value='' id='packSize' class='text packSize' name='pack_size[]'>";
        $tag_no           = "<input type='text' value='' id='tag_no' class='text tagNo' name='tag_no[]'>";
        $medicine_company = "{$formObj->getDDRowBySQL('', 'medicine_company[]', $sqlMedicineCompany, '')}";
        $batch_no         = "<input type='text' value='' id='batch_no' class='text batchNo' name='batch_no[]'>";
        $free_items       = "<input type='text' value='0' id='free_items' class='text freeItems' name='free_items[]'>";
        $discount_percent = "<input type='text' value='0' id='discount_percentage' class='text discountPercentage' name='discount_percentage[]'>";
        $expiry_date      = "<input type='text' value='' id='expiry_date' class='text expiry_date' name='expiry_date[]'>";

        for($i=0;$i<10;$i++){
            //$expiry_date_id = 'expiry_date_'.$i;
            //$expiry_date    = "{$formObj->getDateRow('', $expiry_date_id, '')}";

            $rows .= "
            <tr>
                <td class='productSize'>{$product}</td>
                <td class='priceSize'>{$medicine_company}</td>
                <td class='packSize'>{$pack_size}</td>
                <td class='batchNo'>{$batch_no}</td>
                <td class='priceSize expiryDate'>{$expiry_date}</td>
                <td class='qtySize'>{$qty}</td>
                <td class='qtySize'>{$free_items}</td>
                <td class='priceSize'>{$cost_price}</td>
                <td class='priceSize'>{$price}</td>
                <td class='qtySize'>{$discount_percent}</td>
                <td class='poGst'>{$gst}</td>
                <td class='hsn'>{$hsn}</td>
                <td>{$clear}</td>
            </tr>
            ";
        }
        
        $formActionNewProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddNewProductMaster&supplier_id={$supplier_id}&showHTML=0";
        
        $newRow = "
        <a href='{$formActionNewProduct}' class='addNewProductProductPopup btn btn-success mb10'>Add New Medicine</a>
        ";

        $expEdit = array('isEditable' => 0);
        $addMoreRow = "
        <a class='addSinglePoRowNew btn btn-info'>Add More Items</a>
        ";

        $header ="
        <tr>
            <div class='floatbox'>
                <div class='float_left'>
                    {$newRow}
                </div>
                <div class='float_left'>
                    {$addMoreRow}
                </div>
                <div class='type-button float_right'>
                    <input class='btn btn-primary' type='submit' value='Submit' name='addNewproductForm' />
                </div>
            </div>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Medicine Name</th>
            <th class='txtCenter'>Mfr Company</th>
            <th class='txtCenter'>Pack Size</th>
            <th class='txtCenter'>Batch No</th>
            <th class='txtCenter'>Expiry Date (MM/YYYY)</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Free</th>
            <th class='txtCenter'>Rate (without GST)</th>
            <th class='txtCenter'>Mrp (without GST)</th>
            <th class='txtCenter'>Discount %</th>
            <th class='txtCenter'>GST %</th>
            <th class='txtCenter'>HSN Code</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddNewProductSubmit&showHTML=0";
        $returnUrl = $cpUrl->getRequestURI();

        $text = "
        <form id='addNewproductForm' class='addNewproductForm cpJqForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', 'error_box1', '', $expEdit)}
            <div class='table-responsive'>
                <table class='thinlist' id='po_productTable'>
                    {$header}
                    {$rows}
                </table>
            </div>
            <div class='floatbox'>
                <div class='type-button float_right mt20'>
                    <input class='btn btn-primary' type='submit' value='Submit' name='addNewproductForm' />
                </div>
            </div>
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            <input type='hidden' name='supplier_id' value='{$supplier_id}' />
            <input type='hidden' name='returnUrl' value='{$returnUrl}'>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleLineItemNew() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $rows = '';

        $sqlMedicineCompany = "
        SELECT medicine_company_id
               ,medicine_company_name
        FROM medicine_company
        WHERE published = 1
        ";

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice  = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price            = "<input type='text' value='0' id='price' class='text lineItemDescription' name='price[]'>";
        $cost_price       = "<input type='text' value='0' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst              = "<input type='text' value='0' id='gst' class='text poGst' name='gst[]'>";
        $qty              = "<input type='text' value='0' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered    = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear            = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $hsn              = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit             = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $pack_size        = "<input type='text' value='' id='packSize' class='text packSize' name='pack_size[]'>";
        $tag_no           = "<input type='text' value='' id='tag_no' class='text tagNo' name='tag_no[]'>";
        $medicine_company = "{$formObj->getDDRowBySQL('', 'medicine_company[]', $sqlMedicineCompany, '')}";
        $batch_no         = "<input type='text' value='' id='batch_no' class='text batchNo' name='batch_no[]'>";
        $free_items       = "<input type='text' value='0' id='free_items' class='text freeItems' name='free_items[]'>";
        $discount_percent = "<input type='text' value='0' id='discount_percentage' class='text discountPercentage' name='discount_percentage[]'>";
        $expiry_date      = "<input type='text' value='' id='expiry_date' class='text expiry_date' name='expiry_date[]'>";

        for($i=0;$i<5;$i++){
            //$expiry_date_id = 'expiry_date_'.$i;
            //$expiry_date    = "{$formObj->getDateRow('', $expiry_date_id, '')}";

            $rows .= "
            <tr>
                <td class='productSize'>{$product}</td>
                <td class='priceSize'>{$medicine_company}</td>
                <td class='packSize'>{$pack_size}</td>
                <td class='batchNo'>{$batch_no}</td>
                <td class='priceSize expiryDate'>{$expiry_date}</td>
                <td class='qtySize'>{$qty}</td>
                <td class='qtySize'>{$free_items}</td>
                <td class='priceSize'>{$cost_price}</td>
                <td class='priceSize'>{$price}</td>
                <td class='qtySize'>{$discount_percent}</td>
                <td class='poGst'>{$gst}</td>
                <td class='hsn'>{$hsn}</td>
                <td>{$clear}</td>
            </tr>
            ";
        }

        return $rows;
    }

    /**
     *
     */
    function getAddNewProductList() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');

        $productTypeArr = array(
             "Purchasing and Selling"
            ,"Purchasing Product"
            ,"Selling Product"
        );

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product[]'>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $cost_price = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]'>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $product_weight = "<input type='text' value='' id='productWeight' class='text productWeight' name='product_weight[]'>";
        $hsn = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $category = "{$formObj->getDDRowBySQL('', 'category[]', $sqlCategory, '')}";
        $sqlSupplier = $fn->getDDSql('tradingsg_supplier');
        $supplier = "{$formObj->getDDRowBySQL('', 'supplier_id[]', $sqlSupplier, '')}";
        $type = "{$formObj->getDDRowByArr('', 'type[]', $productTypeArr, 'Purchasing and Selling')}";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$product_weight}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$product_weight}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$product_weight}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$product_weight}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$product_weight}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRowNewList button mb10'>Add Item</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Product</th>
            <th class='txtCenter'>Category</th>
            <th class='txtCenter'>Supplier</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Cost Price (without GST)</th>
            <th class='txtCenter'>Selling Price (without GST)</th>
            <th class='txtCenter'>GST %</th>
            <th class='txtCenter'>HSN Code</th>
            <th class='txtCenter'>Unit</th>
            <th class='txtCenter'>Weight</th>
            <th class='txtCenter'>Type</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddNewProductListSubmit&showHTML=0";

        $text = "
        <form id='addNewproductListForm' class='addNewproductListForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleLineItemNewList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');

        $productTypeArr = array(
             "Purchasing and Selling"
            ,"Purchasing Product"
            ,"Selling Product"
        );

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product[]'>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $cost_price = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]'>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $product_weight = "<input type='text' value='' id='productWeight' class='text productWeight' name='product_weight[]'>";
        $hsn = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $category = "{$formObj->getDDRowBySQL('', 'category[]', $sqlCategory)}";
        $sqlSupplier = $fn->getDDSql('tradingsg_supplier');
        $supplier = "{$formObj->getDDRowBySQL('', 'supplier_id[]', $sqlSupplier, '')}";
        $type = "{$formObj->getDDRowByArr('', 'type[]', $productTypeArr, 'Purchasing and Selling')}";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$product_weight}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        ";

        return $rows;
    }
    /**
     *
     */

    function getProduct() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        //echo "Testing";
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');

        $formAction = "index.php?_topRm=order&module=tradingsg_purchaseOrder&_spAction=ProductFormSubmit&showHTML=0";

        $sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";


        $sqlCategory = '';

        $text = "
        <form id='addMultipleLineItemForm' class='yform columnar addLineItem' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Product', 'product_id', $sqlproduct,'')}
            {$formObj->getTBRow('Quantity', 'qty')}
            {$formObj->getTBRow('Qty Delivered', 'qty_delivered')}
            {$formObj->getTBRow('Cost Price', 'price')}
            {$formObj->getTBRow('Status', 'status')}
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getPreviousOrderForClient() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $po_product_id    = $fn->getReqParam('po_product_id');
        $poProductRec     = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
        $purchaseOrderRec = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $poProductRec['purchase_order_id']);

        $rows = '';
        $errorText = '';

        $sqlClient = "
        SELECT po.po_code
              ,po.purchase_order_id
              ,pop.price
              ,DATE_FORMAT(po.purchase_order_date, '%d-%m-%Y') AS purchase_order_date
              ,pop.qty
              ,s.company_name AS title
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN (supplier s) ON (s.supplier_id = po.company_id_supplier)
        WHERE po.company_id_supplier = {$purchaseOrderRec['company_id_supplier']}
          AND pop.product_id = {$poProductRec['product_id']}
          AND pop.purchase_order_id != {$poProductRec['purchase_order_id']}
        ORDER BY pop.po_product_id DESC
        LIMIT 0, 10
        ";

        $result     = $db->sql_query($sqlClient);
        $numRows    = $db->sql_numrows($result);

        if ($numRows == 0) {
            $clientRows =  "
            <div class='header mt10' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left' >Purchase History from This Suppliers</div>
                </div>
            </div>
            <table class='thinlist'>
                <td>Sorry, no previous Purchase History records for this Suppliers</td>
            </table>";
        }
        else{
            while ($row = $db->sql_fetchrow($result)) {
                $purchase_order = "<a target='_blank' href='index.php?_topRm=inventory&module=tradingsg_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
                $rows .= "
                <tr>
                    <td>{$purchase_order}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['purchase_order_date']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['qty']}</td>
                </tr>
                ";
                $companyName_client = $row['title'];
            }

            $clientRows = "
            <div class='header mt10' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left'>Purchase History from {$companyName_client}</div>
                </div>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>PO Code</th>
                    <th>Supplier Name</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Qty</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            ";
        }


        $sqlOtherClient = "
        SELECT po.po_code
              ,po.purchase_order_id
              ,pop.price
              ,DATE_FORMAT(po.purchase_order_date, '%d-%m-%Y') AS purchase_order_date
              ,pop.qty
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pop.purchase_order_id)
        WHERE po.company_id_supplier != {$purchaseOrderRec['company_id_supplier']}
          AND pop.product_id = {$poProductRec['product_id']}
          AND pop.purchase_order_id != {$poProductRec['purchase_order_id']}
        ORDER BY pop.po_product_id DESC
        LIMIT 0, 10
        ";

        $resultOtherClient     = $db->sql_query($sqlOtherClient);
        $numRowsOtherClient    = $db->sql_numrows($resultOtherClient);

        if ($numRowsOtherClient == 0) {
            $otherClientRows =  "
            <div class='header mt20' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left'>Purchase History from Other Suppliers</div>
                </div>
            </div>
            <table class='thinlist'>
                <td>Sorry, no previous Purchase History records for Other Suppliers</td>
            </table>";
        }
        else{
            $otherRows ='';
            while ($row = $db->sql_fetchrow($resultOtherClient)) {
                $purchase_order = "<a target='_blank' href='index.php?_topRm=inventory&module=tradingsg_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
                $otherRows .= "
                <tr>
                    <td>{$purchase_order}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['purchase_order_date']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['qty']}</td>
                </tr>
                ";
            }

            $otherClientRows = "
            <div class='header mt20' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left'>Purchase History from Other Suppliers</div>
                </div>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>PO Code</th>
                    <th>Supplier Name</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Qty</th>
                </thead>

                <tbody>
                    {$otherRows}
                </tbody>
            </table>
            ";
        }

        $text ="
        {$clientRows}
        {$otherClientRows}
        ";

        return $text;
    }

    /**
     *
     */
    function getLastQuotedPrice(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $product_id        = $fn->getReqParam('product_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $json  = array();

        if($product_id == ""){
            return json_encode($json);
        }

        $SQL    = "
        SELECT  po.product_id
               ,po.price
        FROM  po_product po
        WHERE po.product_id = {$product_id}
        AND po.purchase_order_id < {$purchase_order_id}
        ORDER BY po.product_id DESC
        LIMIT 0,1
        ";

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $json[] = $row['price'];

        return json_encode($json);
    }

    /**
     *
     */
    function getAddNewProductMaster() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $supplier_id = $fn->getReqParam('supplier_id');

        $formAction = "index.php?_topRm=inventory&module=tradingsg_purchaseOrder&_spAction=AddNewProductMasterSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);

        $text = "
        <form id='NewProductPortalForm' class='NewProductPortalForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Medicine Name', 'title', '')}
            {$formObj->getTBRow('Combination', 'generic_name', '')}
            <input type='hidden' name='supplier_id' value='{$supplier_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getViewMedicines() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $purchase_order_id  = $fn->getReqParam('purchase_order_id');

            $SQLCom = "
            SELECT pop.qty
                  ,pop.free_items   
                  ,pop.cost_price
                  ,pop.selling_price
                  ,pop.product_id
                  ,pop.medicine_company_id
                  ,pop.purchase_order_id
                  ,p.title
                  ,c.company_name
            FROM po_product pop
            LEFT JOIN `product` p ON (p.product_id = pop.product_id)
            LEFT JOIN `company` c ON (c.company_id = pop.medicine_company_id)
            WHERE pop.purchase_order_id = '{$purchase_order_id}'
            ORDER BY p.title DESC
            ";
            $resultCom = $db->sql_query($SQLCom);
            $rows = '';
            $count = 1;
            while($rowCom = $db->sql_fetchrow($resultCom)){

                $rows .= "
                <tr>
                    <td>{$rowCom['title']}</td>
                    <td>{$rowCom['company_name']}</td>
                    <td>{$rowCom['qty']}</td>
                    <td>{$rowCom['free_items']}</td>
                    <td>{$rowCom['cost_price']}</td>
                    <td>{$rowCom['selling_price']}</td>
                </tr>
                ";
            }

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Mfr Comp</th>
                    <th>Qty</th>
                    <th>Free</th>
                    <th>Rate</th>
                    <th>MRP</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }

}