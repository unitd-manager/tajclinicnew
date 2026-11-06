<?
class CPL_Admin_Widgets_Hms_StockReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');


        $text = "
        <h2>Stock Report</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
                    <th>Inventory Code</th>
                    <th>Product Name</th>
                    <th>Total Stock</th>
                    <th class='txtRight'>Cost Price/Qty</th>
                    <th class='txtRight'>Total Cost</th>
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
        $fn 	= Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg 	= Zend_Registry::get('cpCfg');

        $rows = '';
		$siteTitle = '' ;
        $count = 1 ;
        $linkToStock = '' ;
        $sum_purchase_cp_per_qty = '';

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        foreach($this->model->dataArray as $row){

            $SQLPP = "
            SELECT pack_size
            FROM po_product
            WHERE product_id = {$row['product_id']}
            ORDER BY po_product_id DESC
            ";
            $resultPP = $db->sql_query($SQLPP);
            $rowPP = $db->sql_fetchrow($resultPP);
            if(is_numeric($rowPP['pack_size'])){
                $price = $row['price'] / $rowPP['pack_size'];
            } else {
                $price = $row['price'];
            }

            $stockArray = $fn->getStockForProduct($row['product_id']);
            $stock      = $stockArray['OverallStock'];

            $sum_purchase_cp_per_qty = $stock * $price;

            if($sum_purchase_cp_per_qty){
                $sum_purchase_cp_per_qty = round($sum_purchase_cp_per_qty, 2);
            }
            $price = round($price, 2);

		    $rows .= "
            <tr>
                <td>STK-{$row['inventory_code']}</td>
                <td>{$row['product_title']}</td>
                <td>{$stock}</td>
                <td class='txtRight'>{$price}</td>
                <td class='txtRight'>{$sum_purchase_cp_per_qty}</td>
            </tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}