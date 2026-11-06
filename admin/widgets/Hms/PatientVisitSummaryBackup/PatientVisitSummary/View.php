<?
class CPL_Admin_Widgets_Hms_PatientVisitSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($tv['module'] == 'common_dashboard'){
            $heading = "Patient Visit Summary Last 7 Days";
        }else {
            $heading = "Patient Visit Summary";
        }
        
        $text = "
        <h2>{$heading}</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
						<th>Day</th>
                        <th>Doctor</th>
                        <th>Duty Doctor</th>
                        <th>Staff</th>
                        <th>Consultant</th>
                        <th>Total</th>
                        <th>Total - Consultant</th>
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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $rows = '';
        $totalAppFixed = 0;
        $totalPatVisited = 0;
        $totalPatNotVisited = 0;
        $totalWalkIn = 0;
        $totalOverAll = 0;
        $totalOverAllCase =0;
        $totalOverAllConsult = 0;
        $totalOverAllCaseConsult =0;

        foreach($this->model->dataArray as $row){
            $appendSql = '';

            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");
            $sum_amount = '';
            $case_count = '';
            $doctor     = '';
            $duty_doctor = '';
            $consultant = '';
            $staff      = '';
            $total_amount = '';
            $total_case_count = '';
            $total_amount_consult = '';
            $total_case_count_consult = '';

            $sqlCategory = $fn->getValueListSQL('employeeCategory');
            $resultCat   = $db->sql_query($sqlCategory);

            $numrows     = $db->sql_numrows($resultCat);

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND pv.site_id = {$cpSiteIdSession}";
            }

            while ($rowCat = $db->sql_fetchrow($resultCat)) {
                $SQLSub = "
                SELECT COUNT(ev.patient_visit_id) AS patient_count
                      ,SUM(ev.consultation_fees) AS fees_count
                FROM employee_visit ev
                LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                  AND e.first_name != ''
                  AND pv.status != 'Cancelled'
                  AND e.category = '{$rowCat['value']}'
                  {$appendSql}
                ";
                $resultSub = $db->sql_query($SQLSub);
                $sum_amount = '';
                $case_count = '';

                while ($rowSub = $db->sql_fetchrow($resultSub)) {
                    $sum_amount = $sum_amount + $rowSub['fees_count'];
                    $case_count = $case_count + $rowSub['patient_count'];
                }
                if($rowCat['value'] == 'Doctor'){
                    // To check the pat visited in morning
                    $morningVisits = '';
                    $evengVisits = '';

                    $sqlOnBehalfMorn = "
                    SELECT COUNT(pv.on_behalf) AS on_behalf_count_morn
                          ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Doctor'
                      AND pv.on_behalf > 0
                      AND pv.check_up_time > '7:00'
                      AND pv.check_up_time < '14:55'
                      {$appendSql}
                    ";
                    $resultOnBehalfMorn = $db->sql_query($sqlOnBehalfMorn);
                    $rowOnBehalfMorn = $db->sql_fetchrow($resultOnBehalfMorn);

                    // To check the at visited in evening
                    $sqlOnBehalfEven = "
                    SELECT COUNT(pv.on_behalf) AS on_behalf_count_even
                          ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Doctor'
                      AND pv.on_behalf > 0
                      AND pv.check_up_time > '15:00'
                      AND pv.check_up_time < '23:50'
                      {$appendSql}
                    ";
                    $resultOnBehalfEven = $db->sql_query($sqlOnBehalfEven);
                    $rowOnBehalfEven = $db->sql_fetchrow($resultOnBehalfEven);

                    if($rowOnBehalfMorn['on_behalf_count_morn'] > 0 && $rowOnBehalfEven['on_behalf_count_even'] > 0){                        
                        $morningVisits =  ' [ MORNG :'.$rowOnBehalfMorn['on_behalf_count_morn'] .' cs - ' . $rowOnBehalfMorn['on_behalf_fees_count']  .' rs]';
                        $evengVisits   =  ' - [ EVENG :'.$rowOnBehalfEven['on_behalf_count_even'] .' cs - ' . $rowOnBehalfEven['on_behalf_fees_count']  .' rs]';
                    } else if($rowOnBehalfMorn['on_behalf_count_morn'] > 0){
                        $morningVisits =  ' : [ MORNG :'.$rowOnBehalfMorn['on_behalf_count_morn'] .' cs - ' . $rowOnBehalfMorn['on_behalf_fees_count']  .' rs]';
                        $evengVisits   =  ' - [ EVENG : 0]';
                    } else if($rowOnBehalfEven['on_behalf_count_even'] > 0){
                        $morningVisits   =  ' [ MORNG : 0] ';
                        $evengVisits   =  ' : [ EVENG :'.$rowOnBehalfEven['on_behalf_count_even'] .' cs - ' . $rowOnBehalfEven['on_behalf_fees_count']  .' rs]';
                    }
                    $doctor =  $case_count .' cs - ' . $sum_amount  .' rs' . $morningVisits.$evengVisits;
                }

                if($rowCat['value'] == 'Duty Doctor'){
                    // To check the pat visited in morning by duty dr
                    $morningVisitsDutyDr = '';
                    $evengVisitsDutyDr = '';

                    $sqlOnBehalfMornDutyDr = "
                    SELECT COUNT(pv.on_behalf) AS on_behalf_count_morn
                          ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Duty Doctor'
                      AND pv.on_behalf > 0
                      AND pv.check_up_time > '7:00'
                      AND pv.check_up_time < '14:55'
                      {$appendSql}
                    ";
                    $resultOnBehalfMornDutyDr = $db->sql_query($sqlOnBehalfMornDutyDr);
                    $rowOnBehalfMornDutyDr    = $db->sql_fetchrow($resultOnBehalfMornDutyDr);

                    // To check the pat visited in evening by duty dr
                    $sqlOnBehalfEvenDutyDr = "
                    SELECT COUNT(pv.on_behalf) AS on_behalf_count_even
                          ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Duty Doctor'
                      AND pv.on_behalf > 0
                      AND pv.check_up_time > '15:00'
                      AND pv.check_up_time < '23:50'
                      {$appendSql}
                    ";
                    $resultOnBehalfEvenDutyDr = $db->sql_query($sqlOnBehalfEvenDutyDr);
                    $rowOnBehalfEvenDutyDr    = $db->sql_fetchrow($resultOnBehalfEvenDutyDr);

                    if($rowOnBehalfMornDutyDr['on_behalf_count_morn'] > 0 && $rowOnBehalfEvenDutyDr['on_behalf_count_even'] > 0){                        
                        $morningVisitsDutyDr =  ' [ MORNG :'.$rowOnBehalfMornDutyDr['on_behalf_count_morn'] .' cs - ' . $rowOnBehalfMornDutyDr['on_behalf_fees_count']  .' rs]';
                        $evengVisitsDutyDr   =  ' - [ EVENG :'.$rowOnBehalfEvenDutyDr['on_behalf_count_even'] .' cs - ' . $rowOnBehalfEvenDutyDr['on_behalf_fees_count']  .' rs]';
                    } else if($rowOnBehalfMornDutyDr['on_behalf_count_morn'] > 0){
                        $morningVisitsDutyDr =  ' : [ MORNG :'.$rowOnBehalfMornDutyDr['on_behalf_count_morn'] .' cs - ' . $rowOnBehalfMornDutyDr['on_behalf_fees_count']  .' rs]';
                        $evengVisitsDutyDr   =  ' - [ EVENG : 0]';
                    } else if($rowOnBehalfEvenDutyDr['on_behalf_count_even'] > 0){
                        $morningVisitsDutyDr   =  ' [ MORNG : 0] ';
                        $evengVisitsDutyDr   =  ' - [ EVENG :'.$rowOnBehalfEvenDutyDr['on_behalf_count_even'] .' cs - ' . $rowOnBehalfEvenDutyDr['on_behalf_fees_count']  .' rs]';
                    }
                    $duty_doctor =  $case_count .' cs - ' . $sum_amount  .' rs' . $morningVisitsDutyDr.$evengVisitsDutyDr;
                }
                /*
                if($rowCat['value'] == 'Duty Doctor'){
                    $sqlOnBehalf = "
                    SELECT COUNT(pv.on_behalf) AS on_behalf_count
                          ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Duty Doctor'
                      AND pv.on_behalf > 0
                      {$appendSql}
                    ";
                    $resultOnBehalf = $db->sql_query($sqlOnBehalf);
                    $rowOnBehalf = $db->sql_fetchrow($resultOnBehalf);

                    if($rowOnBehalf['on_behalf_count'] > 0){                        
                        $duty_doctor =  $case_count .' cs - ' . $sum_amount  .' rs ['.$rowOnBehalf['on_behalf_count'] .' cs - ' . $rowOnBehalf['on_behalf_fees_count']  .' rs]';
                    } else {
                        $duty_doctor =  $case_count .' cs - ' . $sum_amount.' rs';
                    }
                }
                */
                if($rowCat['value'] == 'Staff'){
                    $staff =  $case_count .' cs - ' . $sum_amount.' rs';
                }
                if($rowCat['value'] == 'Consultant'){
                    $consultant =  $case_count .' cs - ' . $sum_amount.' rs';
                    $total_amount_consult += $sum_amount;
                    $total_case_count_consult   += $case_count;
                }
                $total_amount += $sum_amount;
                $total_case_count   += $case_count;

            }
            $total = $total_case_count .' cs - ' . $total_amount.' rs';
            $total_consultant = ($total_case_count - $total_case_count_consult) .' cs - ' . ($total_amount - $total_amount_consult).' rs';
		    $rows .= "
			<tr>
				<td>{$check_up_date}</td>
                <td>{$row['day']}</td>
                <td>{$doctor}</td>
                <td>{$duty_doctor}</td>
                <td>{$staff}</td>
                <td>{$consultant}</td>
                <td class='txtRight'>{$total}</td>
                <td class='txtRight'>{$total_consultant}</td>
            </tr>
            ";
            $totalOverAllCase += $total_case_count;
            $totalOverAll += $total_amount;
            $totalOverAllCaseConsult += $total_case_count - $total_case_count_consult;
            $totalOverAllConsult += $total_amount - $total_amount_consult;
        }
        //$totalOverAll = number_format(round($totalOverAll), 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='6'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllCase} - {$totalOverAll}</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllCaseConsult} - {$totalOverAllConsult}</td>
        </tr>
        ";

        return $text;
    }

}