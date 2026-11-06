<?
class CPL_Admin_Widgets_Hms_PharmacyDailySales_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $month       = $fn->getReqParam('month');

        $heading        = '';
        $monthSearch    = '';

        $month  = $fn->getReqParam('month');
        if ($month == '') {
            $month = date('m');
        }

        $arr = array (
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

        $heading = "
        <div class='float_left m10 heading'>
            Pharmacy Daily Sales
        </div>
        ";
        if($tv['module'] == 'tradingsg_dashboard'){
            $monthSearch = "
            <div class='float_right  mt5 mb5'>
                <td class='fieldValue'>
                    <select name='month'>
                        <option>Select Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
            </div>
            ";
        }

        $acTotal = '';
        if ($fn->isDeveloper() || $_SESSION['userGroupName'] == 'Administrator'){
            $acTotal = "<th class='txtRight'>AC Total Amount</th>";
        }

        $text = "
        <h2 class='floatbox'>
        {$heading} 
        {$monthSearch}
        </h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Bill Nos</th>
                        <th class='txtRight'>Sales Amount</th>
                        <th class='txtRight'>Excess Amount</th>
                        <th class='txtRight'>Sales Return</th>
                        <th class='txtRight'>Total Amount</th>
                        {$acTotal}
                    </tr>
                </thead>
                <tbody>
                    {$this->getRowsHTML()}
                </tbody>
            </table>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv    = Zend_Registry::get('tv');
        
        $rows   = '';
        $text   = "";
        $count  = 1;
        $month  = $fn->getReqParam('month');
        $change = $fn->getReqParam('change');
        $totalOverAll = 0;
        $totalOverAllGst = 0;

        if ($month == '') {
            $month = date('m');
        } else {
            $month = $month;
        }
            
        $SQLPharma = "
            SELECT p.*
            FROM pharma_daily_sales p
            WHERE DATE_FORMAT(p.date, '%m') = '{$month}'
            ORDER BY p.date desc
        ";
        $resultPharma = $db->sql_query($SQLPharma);
        $rows = '';
        $count = 1;
        while ($rowPharma = $db->sql_fetchrow($resultPharma)) {
            $SQLBillNo = "
            SELECT MIN(CONVERT(bill_number, UNSIGNED INTEGER)) AS start_bill_no
                  ,MAX(CONVERT(bill_number, UNSIGNED INTEGER)) AS end_bill_no
            FROM `order`
            WHERE order_status = 'Paid'
            AND order_type = 'POS'
            AND order_date = '{$rowPharma['date']}'
            ";
            $resultBillNo = $db->sql_query($SQLBillNo);
            $recBillNo    = $db->sql_fetchrow($resultBillNo);

            $BillNos =  $recBillNo['start_bill_no'] . ' - ' . $recBillNo['end_bill_no'];
            
            $SQLCollection = "
            SELECT SUM(invoice_amount) AS total_amount
            FROM `invoice`
            WHERE (status = 'Paid')
            AND invoice_type = 'POS'
            AND invoice_date = '{$rowPharma['date']}'
            ";
            /*$SQLCollection = "
            SELECT SUM(oi.unit_price * oi.qty) AS total_amount
            FROM `order_item` oi
            LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
            WHERE o.order_status != 'Cancelled'
            AND o.order_type = 'POS'
            AND o.order_date = '{$rowPharma['date']}'
            ";*/
            $resultCollection = $db->sql_query($SQLCollection);
            $recCollection    = $db->sql_fetchrow($resultCollection);

            $totalCollection = $recCollection['total_amount'];

            $SQLCollectionGst = "
            SELECT SUM(i.invoice_amount) AS total_amount
            FROM `invoice` i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            WHERE (i.status = 'Paid')
            AND i.invoice_type = 'POS'
            AND i.invoice_date = '{$rowPharma['date']}'
            AND o.enable_gst = 1
            ";
            $resultCollectionGst = $db->sql_query($SQLCollectionGst);
            $recCollectionGst    = $db->sql_fetchrow($resultCollectionGst);

            $totalGstCollection = $recCollectionGst['total_amount'];
            
            $date = $fn->getCPDate($rowPharma['date'],"d-m-Y");

            $SQLSalesReturn = "
            SELECT 
                SUM(srh.qty_return * srh.price) as sales_return_amount 
            FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            LEFT JOIN (`order` o) ON (o.order_id = srh.order_id)
            WHERE o.order_status != 'Cancelled'
            AND o.order_type = 'POS'
            AND srh.date = '{$rowPharma['date']}'
            ";
            $resultSalesReturn = $db->sql_query($SQLSalesReturn);
            $recSalesReturn    = $db->sql_fetchrow($resultSalesReturn);

            $salesReturn =  $recSalesReturn['sales_return_amount'] ;

            $SQLSalesReturnGst = "
            SELECT 
                SUM(srh.qty_return * srh.price) as sales_return_amount 
            FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            LEFT JOIN (`order` o) ON (o.order_id = srh.order_id)
            WHERE o.order_status != 'Cancelled'
            AND o.order_type = 'POS'
            AND o.enable_gst = 1
            AND srh.date = '{$rowPharma['date']}'
            ";
            $resultSalesReturnGst = $db->sql_query($SQLSalesReturnGst);
            $recSalesReturnGst    = $db->sql_fetchrow($resultSalesReturnGst);

            $salesReturnGst =  $recSalesReturnGst['sales_return_amount'] ;

            $totalAmount = $totalCollection + $rowPharma['excess_amount'] - $salesReturn;
            $gstTotalAmount = $totalGstCollection - $salesReturnGst;

            $gstTotal = '';
            if ($fn->isDeveloper() || $_SESSION['userGroupName'] == 'Administrator'){
                $gstTotal = "<td class='txtRight'>{$gstTotalAmount}</td>";
            }

            $rows .= "
            <tr>
                <td>{$date}</td>
                <td>{$BillNos}</td>
                <td class='txtRight'>{$totalCollection}</td>
                <td class='txtRight'>{$rowPharma['excess_amount']}</td>
                <td class='txtRight'>{$salesReturn}</td>
                <td class='txtRight'>{$totalAmount}</td>
                {$gstTotal}
            </tr>
            ";
            $count++;

            $totalOverAll += $totalAmount;
            $totalOverAllGst += $gstTotalAmount;
            $totalAmount = number_format(round($totalAmount));
        }

        $totalOverAll = number_format(round($totalOverAll));
        $totalOverAllGst = number_format(round($totalOverAllGst));

        $overallGstTot = '';
        if ($fn->isDeveloper() || $_SESSION['userGroupName'] == 'Administrator'){
            $overallGstTot = "<td class='txtRight lastRowBgColor'>{$totalOverAllGst}</td>";
        }
        $text = "
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='5'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
            {$overallGstTot}
        </tr>
        {$rows}
        ";

        if($month != "" && $change == 1) {
            print $text;
        }
        else {
            return $text;
        }
    }
}