<?
class CPL_Admin_Widgets_Hms_SupplierOutstanding_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $due_period    = $fn->getReqParam('due_period');
        $dueArray = array(
              "> 60 Days"
             ,"30 to 60 Days"
        );

        $dueSearch = "
        <div class='float_right  mt5 mb5'>
            <td class='fieldValue'>
                <select name='due_period'>
                    <option value=''>Select Due</option>
                    {$cpUtil->getDropDown1($dueArray, $due_period)}
                </select>   
            </td>
        </div>
        ";

        /*$text = "
        <h2>
        Supplier Outstanding
        {$dueSearch}
        </h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Supplier Name</th>
                        <th class='txtRight'>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$this->getRowsHTML()}
                </tbody>
            </table>
        </div>
        ";*/

        $title = "Supplier Outstanding";

        $text = "
        <div id='duePeriod' class='inner'>
            <h2>
            {$title}
            {$dueSearch}
            </h2>
            <div class = 'tableOuter scroll-pane'>
                {$this->getSupplierOutstandingByDue()}
            </div>
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
        
        $rows   = '';
        $text   = '';
        $count  = 1;
        $amount = 0;
        $month  = date('m');
        $year   = date('Y');
        $start_date = $year . '-' . $month . '-' . '01';
        $end_date   = $year . '-' . $month . '-' . '31';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql    = '';
        $totalOverAll = 0;

        $due_period    = $fn->getReqParam('due_period');

        $whereSQL = '';
        if ($due_period == '> 60 Days') {
            $whereSQL = "AND DATEDIFF(NOW() ,p.invoice_date ) > 60";
        } else if($due_period == '30 to 60 Days'){
            $whereSQL = "AND (DATEDIFF(NOW() ,p.invoice_date ) > 30 AND DATEDIFF(NOW() ,p.invoice_date ) < 60)";
        } else {
            $whereSQL = "AND p.invoice_date  >= '{$start_date}' AND p.invoice_date  <= '{$end_date}'"; 
        }

        foreach($this->model->dataArray as $row){
            $totalAmount = 0;
            $overall_discount = $row['overall_discount'];

            if($overall_discount == ''){
                $overall_discount = 0;
            }
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND p.site_id = {$cpSiteIdSession}";
            }
            $SQLPaid = "
            SELECT SUM(pop.qty * pop.cost_price) AS po_amount
                  ,SUM((((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
                  ,SUM((((pop.qty * pop.cost_price) - (((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
            FROM purchase_order p
            LEFT JOIN po_product pop ON (pop.purchase_order_id = p.purchase_order_id)
            WHERE p.company_id_supplier IN ({$row['company_id_supplier']})
            {$whereSQL}
            AND p.status  != 'Cancelled'
            {$appendSql}
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM supplier_receipt_history srh
            LEFT JOIN (purchase_order p) ON (srh.purchase_order_id = p.purchase_order_id)
            LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
            WHERE p.company_id_supplier IN ({$row['company_id_supplier']})
              AND sr.receipt_status != 'Cancelled'
              {$whereSQL}
            {$appendSql}
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
            $amount   = $rowPaid['po_amount'] - $rowPaid['Discount_Total'] + $rowPaid['GST_Total'];

            if ($rowPartialPayment['Po_partial_payment'] == 0){
                $totalAmount += $amount;
            } else {
                $totalAmount += $amount - $rowPartialPayment['Po_partial_payment'];
            }

            $totalOverAll += $totalAmount;
            $totalAmount = number_format(round($totalAmount));

            if($totalAmount > 0){
                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$row['company_name']}</td>
                    <td class='txtRight'>{$totalAmount}</td>
                </tr>
                ";

                $count++;
            }
        }

        $totalOverAll = number_format(round($totalOverAll));
        
        $text = "
        <tr>
            <td class='txtRight lastRowBgColor' colspan='2'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
        </tr>
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getSupplierOutstandingByDue() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $rows   = '';
        $text   = '';
        $count  = 1;
        $amount = 0;
        $month  = date('m');
        $year   = date('Y');
        $start_date = $year . '-' . $month . '-' . '01';
        $end_date   = $year . '-' . $month . '-' . '31';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql    = '';
        $totalOverAll = 0;

        $due_period    = $fn->getReqParam('due_period');

        $whereSQL = '';
        if ($due_period == '> 60 Days') {
            $whereSQL = "AND DATEDIFF(CURDATE() ,p.invoice_date ) > 60";
        } else if($due_period == '30 to 60 Days'){
            $whereSQL = "AND (DATEDIFF(CURDATE() ,p.invoice_date ) > 30 AND DATEDIFF(CURDATE() ,p.invoice_date ) < 60)";
        } else {
            $whereSQL = "AND p.invoice_date  >= '{$start_date}' AND p.invoice_date  <= '{$end_date}'"; 
        }
        $SQL = "
        SELECT p.*
              ,su.company_name
              ,su.supplier_id
        FROM purchase_order p
        LEFT JOIN (`supplier` su) ON (su.supplier_id = p.company_id_supplier)
        WHERE p.company_id_supplier > 0
          AND p.status != 'Cancelled'
        GROUP BY p.company_id_supplier
        ORDER BY su.company_name
        "; 
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $totalAmount = 0;
            $overall_discount = $row['overall_discount'];

            if($overall_discount == ''){
                $overall_discount = 0;
            }
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND p.site_id = {$cpSiteIdSession}";
            }
            $SQLPaid = "
            SELECT SUM(pop.qty * pop.cost_price) AS po_amount
                  ,SUM((((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty * pop.cost_price) * IFNULL(p.overall_discount, 0)) / 100)) AS Discount_Total
                  ,SUM((((pop.qty * pop.cost_price) - (((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty * pop.cost_price) * IFNULL(p.overall_discount, 0)) / 100)) * pop.gst) / 100) AS GST_Total
            FROM po_product pop
            LEFT JOIN purchase_order p ON (p.purchase_order_id = pop.purchase_order_id)
            WHERE p.company_id_supplier IN ({$row['company_id_supplier']})
            {$whereSQL}
            AND p.status  != 'Cancelled'
            AND p.invoice_date IS NOT NULL
            {$appendSql}
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM supplier_receipt_history srh
            LEFT JOIN (purchase_order p) ON (srh.purchase_order_id = p.purchase_order_id)
            LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
            WHERE p.company_id_supplier IN ({$row['company_id_supplier']})
              AND sr.receipt_status != 'Cancelled'
              AND p.invoice_date IS NOT NULL
              {$whereSQL}
            {$appendSql}
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
            $amount   = $rowPaid['po_amount'] - $rowPaid['Discount_Total'] + $rowPaid['GST_Total'];

            if ($rowPartialPayment['Po_partial_payment'] == 0){
                $totalAmount += $amount;
            } else {
                $totalAmount += $amount - $rowPartialPayment['Po_partial_payment'];
            }

            $totalOverAll += $totalAmount;
            $totalAmount = number_format(round($totalAmount));
            $supplierLink = "index.php?_topRm=pharmacy&module=tradingsg_supplier&_action=edit&supplier_id={$row['supplier_id']}";

            if($totalAmount > 0){
                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td><a href='{$supplierLink}' target='_blank'><u>{$row['company_name']}</u></a></td>
                    <td class='txtRight'>{$totalAmount}</td>
                </tr>
                ";

                $count++;
            }
        }

        $totalOverAll = number_format(round($totalOverAll));
        
        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Supplier Name</th>
                    <th class='txtRight'>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='txtRight lastRowBgColor' colspan='2'>Total</td>
                    <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
                </tr>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }
}