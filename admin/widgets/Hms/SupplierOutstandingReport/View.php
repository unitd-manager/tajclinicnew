<?
class CPL_Admin_Widgets_Hms_SupplierOutstandingReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Supplier Outstanding Report</h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>PO Code</th>
                        <th>Invoice Date</th>
                        <th>Invoice Code</th>
                        <th>Supplier Name</th>
                        <th class='txtRight'>Amount</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $rows        = '';
        $count       = 1;
        $totalAmount = 0;
        $totalOverAll = 0;
        $totalOverAllPaid = 0;
        $totalOverAllbalance = 0;
        foreach($this->model->dataArray as $row){
            $overall_discount = $row['overall_discount'];

            if($overall_discount == ''){
                $overall_discount = 0;
            }
            /*
            $SQLPaid = "
            SELECT SUM(round((pop.qty * pop.cost_price),2)) AS total_cost
            FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            */
            $SQLPaid = "
            SELECT SUM(pop.qty * pop.cost_price) AS total_cost
                  ,SUM((((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
                  ,SUM((((pop.qty * pop.cost_price) - (((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
            FROM po_product pop 
            WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM supplier_receipt_history srh
            LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
            WHERE srh.purchase_order_id = {$row['purchase_order_id']}
              AND sr.receipt_status    != 'Cancelled'
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

            //$totalAmount = $rowPaid['total_cost'];
            $totalAmount   = $rowPaid['total_cost'] - $rowPaid['Discount_Total'] + $rowPaid['GST_Total'];
           // $totalAmount   = number_format(round($totalAmount), 2);
            $Paid_Amount = $rowPartialPayment['Po_partial_payment'];
            $balance_Amount = $totalAmount - $Paid_Amount;

            $totalOverAll += $totalAmount;
            $totalOverAllPaid += $Paid_Amount;
            $totalOverAllbalance += $balance_Amount;

            $totalAmount    = number_format(round($totalAmount));
            $Paid_Amount    = number_format(round($Paid_Amount));
            $balance_Amount = number_format(round($balance_Amount));

            $invoice_date  = $fn->getCPDate($row['invoice_date'], 'd-m-Y');

            if($totalAmount > 0){
                $rows .= "
                <tr>
                    <td>{$row['po_code']}</td>
                    <td>{$invoice_date}</td>
                    <td>{$row['supplier_inv_code']}</td>
                    <td>{$row['company_name']}</td>
                    <td class='txtRight'>{$totalAmount}</td>
                    <td class='txtRight'>{$Paid_Amount}</td>
                    <td class='txtRight'>{$balance_Amount}</td>
                    <td>{$row['payment_status']}</td>
                </tr>
                ";

                $count++;
            }
        }
        $totalOverAll        = number_format(round($totalOverAll));
        $totalOverAllPaid    = number_format(round($totalOverAllPaid));
        $totalOverAllbalance = number_format(round($totalOverAllbalance));

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='4'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllPaid}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllbalance}</td>
            <td class='txtRight lastRowBgColor'></td>
        </tr>
        ";

        return $text;
    }
}