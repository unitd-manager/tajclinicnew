<?
class CPL_Admin_Widgets_Hms_MOLReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>MOL Report</h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Offer Medicine</th>
                        <th>Supplier</th>
                        <th>Stock</th>
                        <th>MOL</th>
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
        $cpCfg   = Zend_Registry::get('cpCfg');
        $productName = '';

        $rows   = '';
        $count  = 1;
        foreach($this->model->dataArray as $row){
            $productName = "<a href='index.php?_topRm=pharmacy&module=tradingin_inventory&_action=edit&record_id={$row['inventory_id']}' target='_blank'><u>{$row['product_name']}</u></a>";
            
            $rows .= "
            <tr>
                <td>{$row['product_name']}</td>
                <td>{$row['offer_medicine']}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['stock']}</td>
                <td>{$row['minimum_order_level']}</td>
            </tr>
            ";

            $count++;
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}