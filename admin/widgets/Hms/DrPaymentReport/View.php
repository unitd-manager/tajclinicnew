<?
class CPL_Admin_Widgets_Hms_DrPaymentReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <h2>Dr Payment Report</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Dr Name</th>
						<th>Month</th>
                        <th class='txtRight'>Total Cases</th>
                        <th class='txtRight'>Total Amount</th>
                        <th class='txtRight'>Commission</th>
                        <th class='txtRight'>Lab Test</th>
                        <th class='txtRight'>Lab Total Amount</th>
                        <th class='txtRight'>Commission</th>
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
        $fees_count = '';
        $totalOverAllCase = 0;
        $totalOverAll = 0;
        $totalOverAllCommission = 0;
        $totalOverAllLabCase = 0;
        $totalOverAllLab = 0;
        $totalOverAllLabCommission = 0;
        $month    = date('m');
        $year     = date('Y');
        $monthVal = $fn->getReqParam('month');
        $yearVal  = $fn->getReqParam('year');

        if($monthVal == ""){
            $monthVal = $month;
        }

        if($yearVal == ""){
            $yearVal = $year;
        }

        $appendSql = "";
        if ($monthVal != '') {
            $appendSql .= "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $appendSql .= "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        foreach($this->model->dataArray as $row){

            $SQLSub = "
            SELECT e.first_name
              ,COUNT(ev.patient_visit_id) AS patient_count
              ,SUM(ev.consultation_fees) AS fees_count
            FROM employee_visit ev
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
            WHERE  e.first_name != ''
              AND e.category = 'Doctor'
              AND pv.status != 'Cancelled'
            ";
            $resultSub = $db->sql_query($SQLSub);
            $rowSub = $db->sql_fetchrow($resultSub);
            $fees_count = number_format($row['fees_count']);
            $fees_commission_count = number_format($row['fees_commission_count']);

            if($row['fees_commission_type'] == 'Value'){
                $row['fees_commission_type'] = 'Rs';
            }

            $fees_commission = number_format($row['fees_commission'], 0, '.', '');

            $SQLPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,e.lab_commission_type
                   ,e.lab_commission
                   ,SUM(m.fees) AS fees
                   ,(CASE 
                    WHEN e.lab_commission_type = '%'
                    THEN SUM(m.fees * e.lab_commission / 100)
                    ELSE SUM(e.lab_commission) END ) AS labCommission
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
            LEFT JOIN (medical_test_visit m) ON (m.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            WHERE pv.status != 'Cancelled'
            {$appendSql}
            AND ev.employee_id = {$row['employee_id']}
            GROUP BY DATE_FORMAT(pv.check_up_date, '%m'), ev.employee_id
            ";
            $resultPatLabTest = $db->sql_query($SQLPatLabTest);
            $rowPatLabTest    = $db->sql_fetchrow($resultPatLabTest);

            $lab_fees_amount           = number_format($rowPatLabTest['fees']);
            $lab_fees_commission_count = number_format($rowPatLabTest['labCommission']);
            if($rowPatLabTest['lab_commission_type'] == 'Value'){
                $rowPatLabTest['lab_commission_type'] = 'Rs';
            }

            $lab_fees_commission = number_format($rowPatLabTest['lab_commission'], 0, '.', '');

		    $rows .= "
			<tr>
				<td>{$row['first_name']}</td>
                <td>{$row['month']}</td>
                <td class='txtRight'>{$row['patient_count']}</td>
                <td class='txtRight'>{$fees_count}</td>
                <td class='txtRight'>{$fees_commission_count} ({$fees_commission}{$row['fees_commission_type']})</td>
                <td class='txtRight'>{$rowPatLabTest['count']}</td>
                <td class='txtRight'>{$lab_fees_amount}</td>
                <td class='txtRight'>{$lab_fees_commission_count} ({$lab_fees_commission}{$rowPatLabTest['lab_commission_type']})</td>
            </tr>
            ";

            $totalOverAllCase          += $row['patient_count'];
            $totalOverAll              += $row['fees_count'];
            $totalOverAllCommission    += $row['fees_commission_count'];
            $totalOverAllLabCase       += $rowPatLabTest['count'];
            $totalOverAllLab           += $rowPatLabTest['fees'];
            $totalOverAllLabCommission += $rowPatLabTest['labCommission'];
        }

        $totalOverAll              = number_format(round($totalOverAll), 2);
        $totalOverAllCommission    = number_format(round($totalOverAllCommission), 2);
        $totalOverAllLab           = number_format(round($totalOverAllLab), 2);
        $totalOverAllLabCommission = number_format(round($totalOverAllLabCommission), 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='2'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllCase}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllCommission}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllLabCase}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllLab}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllLabCommission}</td>
        </tr>
        ";

        return $text;
    }

}