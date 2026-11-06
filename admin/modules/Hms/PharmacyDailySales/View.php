<?
class CPL_Admin_Modules_Hms_PharmacyDailySales_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $current_month = $fn->getReqParam('current_month');
        $current_year  = $fn->getReqParam('current_year');
        $count         = 0;
        $rows          = '';
        $grandTotal    = 0;
        foreach ($dataArray as $row){
            $SQLBillNo = "
            SELECT MIN(CONVERT(bill_number, UNSIGNED INTEGER)) AS start_bill_no
                  ,MAX(CONVERT(bill_number, UNSIGNED INTEGER)) AS end_bill_no
            FROM `order`
            WHERE order_status = 'Paid'
            AND order_type = 'POS'
            AND order_date = '{$row['date']}'
            ";
            $resultBillNo = $db->sql_query($SQLBillNo);
            $recBillNo    = $db->sql_fetchrow($resultBillNo);
            $BillNos      =  $recBillNo['start_bill_no'] . ' - ' . $recBillNo['end_bill_no'];

            $salesArray      = $this->model->getTotalPharmacySalesAmount($row['date'], '', '');
            $salesReturn     = number_format($salesArray['salesReturn'], 2);
            $totalAmount     = number_format($salesArray['totalAmount'], 2);
            $totalCollection = number_format($salesArray['totalCollection'], 2);

            $date = $fn->getCPDate($row['date'],"d-m-Y");

            $salesGstArray      = $this->model->getTotalPharmacySalesGstAmount($row['date'], '', '');
            $gstTotalAmount     = number_format($salesGstArray['totalAmount'], 2);

            $gstAmount = '';
            if ($fn->isDeveloper() || $_SESSION['userGroupName'] == 'Administrator'){
                $gstAmount = "{$listObj->getListDataCell($gstTotalAmount, 'right')}";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $date)}
            {$listObj->getListDataCell($BillNos)}
            {$listObj->getListDataCell($totalCollection, 'right')}
            {$listObj->getListDataCell($salesReturn, 'right')}
            {$listObj->getListDataCell(number_format($row['excess_amount'], 2), 'right')}
            {$listObj->getListDataCell($totalAmount, 'right')}
            {$gstAmount}
            {$listObj->getListRowEnd($row['pharma_daily_sales_id'])}
            ";

            $count++;
        }

        $salesOverallArray  = $this->model->getTotalPharmacySalesAmount('', $current_month, $current_year);
        $totalOverallAmount = $salesOverallArray['totalAmount'];
        $grandTotal         = number_format($totalOverallAmount, 2);

        $salesGstOverallArray  = $this->model->getTotalPharmacySalesGstAmount('', $current_month, $current_year);
        $totalOverallGstAmount = $salesGstOverallArray['totalAmount'];
        $grandGstTotal         = number_format($totalOverallGstAmount, 2);

        $acTotal = '';
        $grantGstTot = '';
        if ($fn->isDeveloper() || $_SESSION['userGroupName'] == 'Administrator'){
            $acTotal = "{$listObj->getListHeaderCell('AC Total', '', 'txtRight')}";
            $grantGstTot = "
            <th colspan='2' class='totalFeesInList'></th>
            <th class='totalFeesInList'>Grand AC Total: {$grandGstTotal}</th>
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Date', 'p.date')}
        {$listObj->getListHeaderCell('Bill Nos', '')}
        {$listObj->getListHeaderCell('Sales Amount', 'p.sales_amount', 'txtRight')}
        {$listObj->getListHeaderCell('Sales Return', 'p.sales_return', 'txtRight')}
        {$listObj->getListHeaderCell('Excess Amount', 'p.excess_amount', 'txtRight')}
        {$listObj->getListHeaderCell('Total', '', 'txtRight')}
        {$acTotal}
        {$listObj->getListHeaderEnd()}
        <tr bgcolor='#CFDBE2'>
            <th colspan='6' class='totalFeesInList'></th>
            <th class='totalFeesInList'>Grand Total: {$grandTotal}</th>
            {$grantGstTot}
        </tr>
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

        $fielset1 = "
        {$formObj->getDateRow('Date', 'date')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];
        $expVl     = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        /*    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            WHERE ini.record_id = {$product_id}
            {$appendSqlInv}
            AND srh.status = 'Approved'
            ) as sales_return_qty

            */

        $SQLCollection = "
        SELECT SUM(invoice_amount) AS total_amount
        FROM `invoice`
        WHERE 
        status != 'Cancelled'
        AND invoice_type = 'POS'
        AND invoice_date = '{$row['date']}'
        ";
        $resultCollection = $db->sql_query($SQLCollection);
        $recCollection    = $db->sql_fetchrow($resultCollection);

        $totalCollection = $recCollection['total_amount'];

        $SQLBillNo = "
        SELECT MIN(CONVERT(bill_number, UNSIGNED INTEGER)) AS start_bill_no
              ,MAX(CONVERT(bill_number, UNSIGNED INTEGER)) AS end_bill_no
        FROM `order`
        WHERE order_status = 'Paid'
        AND order_type = 'POS'
        AND order_date = '{$row['date']}'
        ";
        $resultBillNo = $db->sql_query($SQLBillNo);
        $recBillNo    = $db->sql_fetchrow($resultBillNo);

        $BillNos =  $recBillNo['start_bill_no'] . ' - ' . $recBillNo['end_bill_no'];
        $salesReturn      = '';

        $SQLSalesReturn = "
        SELECT 
            SUM(srh.qty_return * srh.price) as sales_return_amount 
        FROM sales_return_history srh
        LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
        LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
        LEFT JOIN (`order` o) ON (o.order_id = srh.order_id)
        WHERE o.order_status = 'Paid'
        AND o.order_type = 'POS'
        AND srh.date = '{$row['date']}'
        ";
        $resultSalesReturn = $db->sql_query($SQLSalesReturn);
        $recSalesReturn    = $db->sql_fetchrow($resultSalesReturn);

        $salesReturn =  $recSalesReturn['sales_return_amount'] ;

        $totalAmount = $totalCollection + $row['excess_amount'] - $salesReturn;
        $totalAmount = number_format($totalAmount, 2);

        $totalAmountTd = "
        <div class='type-text ym-fbox-text row_totalAmount non-editable'>
            <label for='fld_totalAmount'>Total</label>
            <div class='txt' id='fld_totalAmount'>
                <span class='value pharmacyTotalAmount'>{$totalAmount}</span>
            </div>
        </div>";

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Pharmacy Daily Sales Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='15%'>{$formObj->getDateRow('Date', 'date', $row['date'])}</td>
                                <td width='15%'>{$formObj->getTBRow('Bill Nos', 'bill_nos', $BillNos, $expNoEdit)}</td>
                                <td width='15%'>{$formObj->getTBRow('Sales Amount', 'sales_amount', $totalCollection, $expNoEdit)}</td>
                                <td width='15%'>{$formObj->getTBRow('Excess Amount', 'excess_amount', $row['excess_amount'])}</td>
                                <td width='15%'>{$formObj->getTBRow('Sales Return', 'sales_return', $salesReturn, $expNoEdit)}</td>
                                <td width='25%'>{$totalAmountTd}</td>
                            </tr>
                            <tr>
                                <td width='20%' colspan='2'>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                            </tr>  
                            <tr>
                                <td colspan='4' class='creModdate'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

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
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');


        $record_id = $fn->getIssetParam($row, 'pharma_daily_sales_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_pharmacyDailySales', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv     = Zend_Registry::get('tv');
        $fn     = Zend_Registry::get('fn');

        $current_month = $fn->getReqParam('current_month');
        $current_year  = $fn->getReqParam('current_year');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

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
        SELECT DISTINCT DATE_FORMAT(p.date, '%Y')
        FROM pharma_daily_sales p
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
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
}