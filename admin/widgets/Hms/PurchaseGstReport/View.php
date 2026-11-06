<?
class CPL_Admin_Widgets_Hms_PurchaseGstReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>S.NO.</th>
                    <th>GSTIN</th>
                    <th>COMPANY NAME</th>
                    <th>PURCHASE BILL NO.</th>
                    <th>PURCHASE DATE</th>
                    <th>RATE OF TAX</th>
                    <th>TOTAL TAXABLE VALUE</th>
                    <th>CGST</th>
                    <th>SGST</th>
                    <th>IGST</th>
                    <th>R.OFF</th>
                    <th>LORRY FRIEGHT</th>
                    <th>TOTAL INVOICE VALUE</th>
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

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $count = 1;
        $amount = 0;
        $total = 0;
        $total_po_amount_without_gst = 0;
        $total_half_gst = 0;

        foreach($this->model->dataArray as $row){
            $po_date = $fn->getCPDate($row['po_date'],"d-m-Y");
            $po_amount = $row['po_amount'] + $row['gst_amount'];
            $half_gst = $row['gst_amount'] / 2;

            $total_po_amount_without_gst += $row['po_amount'];
            $total_half_gst += $half_gst;
            $total += $po_amount;

            $half_gst    = number_format($half_gst, 2);
            $po_amount    = number_format($po_amount, 2);
            $po_amount_without_gst    = number_format($row['po_amount'], 2);
            $gst = round($row['gst']);

            $rows .= "
            <tr>
                <td>{$count}</td>
                <td>{$row['gst_no']}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['supplier_inv_code']}</td>
                <td>{$po_date}</td>
                <td>{$gst}%</td>
                <td align='right'>{$po_amount_without_gst}</td>
                <td align='right'>{$half_gst}</td>
                <td align='right'>{$half_gst}</td>
                <td></td>
                <td></td>
                <td></td>
                <td align='right'>{$po_amount}</td>
            </tr>
            ";
            $count++;

        }
        $total_po_amount_without_gst = number_format($total_po_amount_without_gst, 2);
        $total_half_gst = number_format($total_half_gst, 2);
        $total = number_format($total, 2);

        $totalRow = " 
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='6'>Total</td>
            <td class='txtRight lastRowBgColor'>{$total_po_amount_without_gst}</td>
            <td class='txtRight lastRowBgColor'>{$total_half_gst}</td>
            <td class='txtRight lastRowBgColor'>{$total_half_gst}</td>
            <td class='txtRight lastRowBgColor'></td>
            <td class='txtRight lastRowBgColor'></td>
            <td class='txtRight lastRowBgColor'></td>
            <td class='txtRight lastRowBgColor'>{$total}</td>
        </tr>
        ";

        $text = "
        {$rows}
        {$totalRow}
        ";

        return $text;
    }
}