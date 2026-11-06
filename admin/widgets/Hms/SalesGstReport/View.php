<?
class CPL_Admin_Widgets_Hms_SalesGstReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /*
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');

        if($yearVal != "") {
            $year = $yearVal;
        } else {
            $year = $year;
        }

        if($monthVal != "") {
            $month = $monthVal;
        } else {
            $month = $month;
        }

        $month = date("F", strtotime($year.'-'.$month.'-1'));
        $summaryRec = $this->model->getSqlForCount();
        $total_gst_amount     = number_format($summaryRec['total_gst_amount'], 2);
        $total_order_amount   = number_format($summaryRec['total_order_amount'], 2);
        $overall_order_amount = number_format($summaryRec['overall_order_amount'], 2);

        $text = "
        <h2>GST Report</h2>
        <table class='thinlist summaryTable mb20'>
            <thead>
                <th colspan='6'>Summary</th>
            </thead>
            <tr>
                <td><b>Year : {$year}</b></td>
                <td><b>Month : {$month}</b></td>
                <td><b>Total GST Amount: {$total_gst_amount}</b></td>
                <td><b>Total Amount: {$total_order_amount}</b></td>
                <td><b>Overall Amount: {$overall_order_amount}</b></td>
            </tr>
        </table>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Branch GSTIN</th>
                        <th>Sales Bill No</th>
                        <th>Sales Bill Date</th>
                        <th class='txtRight'>Sales Invoice Value</th>
                        <th class='txtCenter'>GST %</th>
                        <th class='txtRight'>Taxable Value</th>
                        <th class='txtRight'>IGST Amount</th>
                        <th class='txtRight'>CGST Amount</th>
                        <th class='txtRight'>SGST/UTGST Amount</th>
                        <th class='txtRight'>CESS Amount</th>
                        <th class='txtRight'>Total Tax + Cess</th>
                    </tr>
                </thead>
                {$this->getRowsHTML()}
            </table>
        </div>
        ";
        return $text;
    }

    /*
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $total_order_amount   = 0;
        $total_gst_amount     = 0;
        $overall_order_amount = 0;
        $order_id = '';
        $orderAmountRounded    = 0;
        $orderGSTAmount        = 0;
        $orderAmountWithoutGST = 0;
        foreach ($this->model->dataArray as $row) {
            $totalamount  = $row['gst_amount'] + ($row['order_amount']);
            $totalamount  = number_format($totalamount,2);
            $order_amount = number_format(($row['order_amount']) - $row['gst_amount'], 2);
            $gstAmount    = number_format($row['gst_amount'], 2);

            $billNoLink = "index.php?_topRm=pharmacy&module=hms_order&_action=edit&order_id={$row['order_id']}";
            if($row['invoice_date'] >= '2020-04-01'){
                $billNo = $row['gst_bill_no'];                
            } else {
                $billNo = $row['bill_number'];
            }

            if($row['invoice_code'] == ""){
                $invoice_code = 'INV - '.$row['invoice_id']; 
            }
            else{
                $invoice_code = $row['invoice_code'];
            }

            $invoice_date = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
            $totalAmount  = number_format(($row['order_amount']), 2);
            $gst_Sum_Half = $row['gst_amount'] / 2;
            $gst_Sum_Half = number_format($gst_Sum_Half, 2);

		    $rows .= "
			<tr>
                <td>{$cpCfg['cp.PharmacyGstNumberSalesItem']}</td>
                <td>
                    <a href='{$billNoLink}' target='_blank'>
                        <u>{$billNo}</u>
                    </a>
                </td>
                <td>{$invoice_date}</td>
                <td align='right'>{$totalAmount}</td>
                <td align='center'>{$row['gst']}</td>
                <td align='right'>{$order_amount}</td>
                <td align='right'>0.00</td>
                <td align='right'>{$gst_Sum_Half}</td>
                <td align='right'>{$gst_Sum_Half}</td>
                <td align='right'>0.00</td>
                <td align='right'>{$gstAmount}</td>
			</tr>
			";

            if($row['order_id'] != $order_id) {
                $SQLOrderAmount = "
                SELECT SUM(ROUND((oi.unit_price * oi.qty), 2)) AS order_amount
                      ,SUM((ROUND((oi.unit_price * oi.qty), 2) * oi.gst)/100) AS gst_amount
                FROM `order_item` oi
                WHERE oi.order_id = {$row['order_id']}
                ";
                $resultOrderAmount = $db->sql_query($SQLOrderAmount);
                $rowOrderAmount    = $db->sql_fetchrow($resultOrderAmount); 

                $orderAmountRounded    += round($rowOrderAmount['order_amount']);
                $orderGSTAmount        += $rowOrderAmount['gst_amount'];
                $orderAmountWithoutGST += round($rowOrderAmount['order_amount']) - $rowOrderAmount['gst_amount'];
            }

            $order_id = $row['order_id'];
        }

        $total_order_amount   = number_format($orderAmountWithoutGST, 2);
        $total_gst_amount     = number_format($orderGSTAmount, 2);
        $overall_order_amount = number_format($orderAmountRounded, 2);

        $text = "
        {$rows}
        <tr>
            <td class='lastRowBgColor' colspan='3'>Total</td>
            <td class='txtRight lastRowBgColor'>{$overall_order_amount}</td>
            <td class='lastRowBgColor'></td>
            <td class='txtRight lastRowBgColor'>{$total_order_amount}</td>
            <td class='lastRowBgColor' colspan='4'></td>
            <td class='txtRight lastRowBgColor'>{$total_gst_amount}</td>
        </tr>
        ";

        return $text;
    }

}