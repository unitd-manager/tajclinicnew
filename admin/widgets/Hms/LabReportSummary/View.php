<?
class CPL_Admin_Widgets_Hms_LabReportSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <h2>Lab Report Summary</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Month</th>
						<th>Medical Test</th>
						<th>Number of Test</th>
                        <th>Amount</th>
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
        $site_id        = $fn->getReqParam('site_id');
        $rows = '';
        $totalOverAllCase = 0;
        $totalOverAll = 0;

        foreach($this->model->dataArray as $row){

            $month = $fn->getCPDate($row['creation_date'], 'M');

		    $rows .= "
			<tr>
				<td>{$month}</td>
				<td>{$row['title']}</td>
				<td>{$row['medicaltestcount']}</td>
                <td class='txtRight'>{$row['amount']}</td>
			</tr>
			";
            $totalOverAllCase += $row['medicaltestcount'];
            $totalOverAll += $row['amount'];
        }
        $totalOverAll = number_format(round($totalOverAll), 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='2'>Total</td>
            <td class='lastRowBgColor'>{$totalOverAllCase}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
        </tr>
        ";

        return $text;
    }

}