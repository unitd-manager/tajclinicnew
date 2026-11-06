<?
class CPL_Admin_Widgets_Hms_MfgCompanyReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $grandTotal  = 0;
        $month  = $fn->getReqParam('month');
        $year   = $fn->getReqParam('year');
        $medicine_company_id   = $fn->getReqParam('medicine_company_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND mc.site_id = {$cpSiteIdSession}";
        }

        $SQLPD = "
        SELECT mc.*
              ,p.title AS product
              ,SUM(pop.cost_price * pop.qty) AS total_value
        FROM `medicine_company` mc
        LEFT JOIN (po_product pop) ON (pop.medicine_company_id = mc.medicine_company_id)
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        WHERE DATE_FORMAT(pop.creation_date, '%m') = '{$month}'
        AND DATE_FORMAT(pop.creation_date, '%Y') = '{$year}'
        AND mc.medicine_company_id = '{$medicine_company_id}'
        AND pop.cost_price != 0.00
        {$appendSqlSite}
        GROUP BY pop.product_id
        ";
        $resultPD   = $db->sql_query($SQLPD);
        while ($rowPD = $db->sql_fetchrow($resultPD)) {
            
            $grandTotal += $rowPD['total_value'];
        }
        $grandTotal = number_format($grandTotal, 2);

        $text = "
        <h2>Mfg Company Report</h2>
        <div class='float_right grandTotalProductDisplay'>
            Grand Total: {$grandTotal}
        </div>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Medicine Name</th>
                        <th>Price</th>
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
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $rows = '';

        foreach($this->model->dataArray as $row){

            $total_value = number_format($row['total_value'], 2);

		    $rows .= "
			<tr>
				<td>{$row['product']}</td>
                <td class='txtRight'>{$total_value}</td>
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}