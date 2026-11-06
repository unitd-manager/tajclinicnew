<?
class CPL_Admin_Widgets_Hms_ProductSalesReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $time_in      = $fn->getReqParam('time_in');
        $time_out     = $fn->getReqParam('time_out');
        $current_date = date('Y-m-d');
        $month        = date('m');
        $year         = date('Y');

        $jsonArray =  $this->getRowsHTML();

        if ($start_date != '') {
            $start_date = $start_date;
        }

        if ($monthVal == '' && $yearVal == ''){
            $start_date = $start_date;
            $end_date = $start_date;
        }

        $timeSummary = '';
        $dateRange = '';
        if($start_date != '' && $end_date != '') {
            $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
            $end_date_formatted = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');
            
            if($start_date == $end_date) {
                $dateRange = "
                <th>Date : {$start_date_formatted}</th>
                ";
            }else {
                $dateRange = "
                <th>Start Date : {$start_date_formatted}</th>
                <th>&nbsp;&nbsp;&nbsp;End Date : {$end_date_formatted}</th>
                ";
            }            
        } else {
            $dateObj   = DateTime::createFromFormat('!m', $monthVal);
            $monthName = $dateObj->format('F');
            $dateRange = "
            <th>{$monthName} {$yearVal}</th>
            ";   
        }

        $text = "
        <div class='float_left mr20 '><h1>Product Sales Report</h1></div>
        <div class='float_left ml20'><h1> Total : <b>{$jsonArray['overalltotal']}</b>&nbsp;&nbsp;&nbsp; for&nbsp;&nbsp;&nbsp; {$dateRange}</h1>
        </div>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Medicine Name</th>
                        <th>Sales Qty</th>
                        <th class='txtRight'>Amount</th>
                        <th class='txtRight'>Sales Return</th>
                        <th class='txtRight'>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$jsonArray['text']}
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $month        = date('m');
        $year         = date('Y');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $invoice_date = $fn->getReqParam('invoice_date');
        $time_in      = $fn->getReqParam('time_in');
        $time_out     = $fn->getReqParam('time_out');
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $current_date = date('Y-m-d');

        $monthValAppendSql = '';
        $yearValAppendSql = '';
        if ($monthVal != '') {
            $monthValAppendSql .= "AND DATE_FORMAT(inv.invoice_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $yearValAppendSql .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
        }

        $timeAppendSql = "";
        if ($time_in != '' && $time_out != '') {
            $timeAppendSql = "AND DATE_FORMAT(inv.creation_date, '%H:%i:%s') BETWEEN '{$time_in}' AND '{$time_out}'";
        }

        $appendSqlDate = "";
        if ($start_date != '') {
            $appendSqlDate .= "AND inv.invoice_date = '{$start_date}'";
        } 

        if ($start_date == '') {
            if ($monthVal != '') {
                $appendSqlDate .= "AND DATE_FORMAT(inv.invoice_date, '%m') = '{$monthVal}'";
            }

            if ($yearVal != '') {
                $appendSqlDate .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
            }

            if ($monthVal == '' && $yearVal == ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $appendSqlDate .= "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
            }
        }

        $timeAppendReturnSql = "";
        if ($time_in != '' && $time_out != '') {
            $timeAppendReturnSql = "AND DATE_FORMAT(srh.creation_date, '%H:%i:%s') BETWEEN '{$time_in}' AND '{$time_out}'";
        }

        $appendSqlReturnDate = "";
        if ($start_date != '') {
            $appendSqlReturnDate .= "AND srh.date = '{$start_date}'";
        } 

        if ($start_date == '') {
            if ($monthVal != '') {
                $appendSqlReturnDate .= "AND DATE_FORMAT(srh.date, '%m') = '{$monthVal}'";
            }

            if ($yearVal != '') {
                $appendSqlReturnDate .= "AND DATE_FORMAT(srh.date, '%Y') = '{$yearVal}'";
            }

            if ($monthVal == '' && $yearVal == ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $appendSqlReturnDate .= "AND srh.date >= '{$start_date}' AND srh.date <= '{$end_date}'";
            }
        }

        $totalOverAll = 0;

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $site_id = $cpSiteIdSession;
        }

        $appendSqlSiteStock = '';
        if($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSiteStock = "AND ibs.site_id = {$site_id}";
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND inv.site_id = {$site_id}";
        }

        $rows   = '';
        $count  = 1;
        $stock = '';
        foreach($this->model->dataArray as $row){

            if($row['record_id'] != '' && $row['product_name'] != ''){
                $SQLStock ="
                SELECT SUM(current_stock) AS current_stock
                FROM inventory_batchwise_stock ibs
                WHERE ibs.product_id = {$row['record_id']}
                {$appendSqlSiteStock}
                ";
                $resultStock = $db->sql_query($SQLStock);
                $rowStock    = $db->sql_fetchrow($resultStock);
                $stock       = $rowStock['current_stock'];

                $SQLSalesReturn = "
                SELECT SUM(srh.qty_return * srh.price) as sales_return_amount 
                FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = srh.order_id)
                WHERE o.order_status != 'Cancelled'
                AND inv.invoice_id != 'Cancelled'
                AND o.order_type = 'POS'
                AND ini.record_id = {$row['record_id']}
                {$monthValAppendSql}
                {$yearValAppendSql}
                {$timeAppendSql}
                {$appendSqlDate}
                {$appendSql}
                ";

                $resultSalesReturn    = $db->sql_query($SQLSalesReturn);
                $recSalesReturn       = $db->sql_fetchrow($resultSalesReturn);
                $salesReturn          = $recSalesReturn['sales_return_amount'];
                $totalAmount          = $row['Amount'] - $salesReturn;
                $salesReturn          = number_format($salesReturn, 2);
                $totalAmountFormatted = number_format($totalAmount, 2);
                $Amount               = number_format($row['Amount'], 2);

                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$row['product_name']}</td>
                    <td>{$row['qty']}</td>
                    <td class='txtRight'>{$Amount}</td>
                    <td class='txtRight'>{$salesReturn}</td>
                    <td class='txtRight'>{$totalAmountFormatted}</td>
                </tr>
                ";
                $totalOverAll += $totalAmount; 
                $count++;
            }
        }
        $totalOverAll = number_format(round($totalOverAll), 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='5'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
        </tr>
        ";

        return array('text' => $text, 'overalltotal' => $totalOverAll);
    }
}