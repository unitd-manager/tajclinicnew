<?
class CPL_Admin_Widgets_Hms_PatientVisitSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $headerDrMorn  =  '';
        $headerCons = '';
        $headerTotCons = '';


        if($tv['module'] == 'common_dashboard'){
            $heading = "Patient Visit Summary Last 7 Days";
        }else {
            $heading = "Patient Visit Summary";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($cpSiteIdSession == 1){
                $headerDrMorn = "<th>Doctor Morning</th>";
                $headerTotCons = "<th class='txtRight'>Total - Consultant</th>";
                $headerCons ="<th>Consultant</th>";
            }
            else if($cpSiteIdSession == 2){
                $headerDrMorn = "";
                $headerTotCons = "";
                $headerCons ="";
            }
            else if($cpSiteIdSession == 3){
                $headerDrMorn = "<th>Doctor Morning</th>";
                $headerTotCons = "";
                $headerCons ="";
            }
        }
        else{
                $headerDrMorn = "<th>Doctor Morning</th>";
                $headerTotCons = "<th class='txtRight'>Total - Consultant</th>";
                $headerCons ="<th>Consultant</th>";
        }
        
        $text = "
        <h2>{$heading}</h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        {$headerDrMorn}
                        <th>Doctor Evening</th>
                        <th>Doctor Total</th>
                        <th>Duty Doctor</th>
                        <th>Staff</th>
                        {$headerCons}
                        <th>Lab(Dr/Duty Dr (Total))</th>
                        <th class='txtRight'>Total</th>
                        {$headerTotCons}
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
        $totalOverAllDrLabAmt =0;
        $totalOverAllDutyDrLabAmt =0;
        $colspan = 8;

        foreach($this->model->dataArray as $row){
            $appendSql = '';

            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");
            $sum_amount = '';
            $case_count = '';
            $doctor     = '';
            $duty_doctor = '';
            $doctor_lab_amount = '';
            $duty_doctor_lab_amount = '';
            $consultant = '';
            $staff      = '';
            $total_amount = '';
            $total_case_count = '';
            $total_amount_consult = '';
            $total_case_count_consult = '';
            $lab_amount = '';

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

                $SQLSub1 = "
                SELECT SUM(m.fees) AS lab_fees
                FROM employee_visit ev
                LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                LEFT JOIN (medical_test_visit m) ON (m.patient_visit_id = ev.patient_visit_id)
                LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
                WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                  AND e.first_name != ''
                  AND pv.status != 'Cancelled'
                  AND e.category = '{$rowCat['value']}'
                  AND mt.category != 'Vaccination'
                  {$appendSql}
                ";
                $resultSub1 = $db->sql_query($SQLSub1);
                $lab_amount = '';

                while ($rowSub = $db->sql_fetchrow($resultSub)) {
                    $sum_amount = $sum_amount + $rowSub['fees_count'];
                    $case_count = $case_count + $rowSub['patient_count'];
                }
                while ($rowSub1 = $db->sql_fetchrow($resultSub1)) {
                    $lab_amount = $lab_amount + $rowSub1['lab_fees'];
                }
                if($rowCat['value'] == 'Doctor'){
                    // To check the pat visited in morning
                    $morningVisits = '0 cs - 0 rs';
                    $evengVisits = '0 cs - 0 rs';

                    $sqlOnBehalfMorn = "
                    SELECT  COUNT(ev.patient_visit_id) AS patient_count
                           ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Doctor'
                      AND pv.check_up_time > '06:00'
                      AND pv.check_up_time < '14:55'
                      {$appendSql}
                    ";
                    $resultOnBehalfMorn = $db->sql_query($sqlOnBehalfMorn);
                    $rowOnBehalfMorn = $db->sql_fetchrow($resultOnBehalfMorn);

                    // To check the at visited in evening
                    $sqlOnBehalfEven = "
                    SELECT  COUNT(ev.patient_visit_id) AS patient_count
                           ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Doctor'
                      AND pv.check_up_time > '15:00'
                      AND pv.check_up_time < '23:50'
                      {$appendSql}
                    ";
                    $resultOnBehalfEven = $db->sql_query($sqlOnBehalfEven);
                    $rowOnBehalfEven = $db->sql_fetchrow($resultOnBehalfEven);

                    if($rowOnBehalfMorn['patient_count'] > 0 && $rowOnBehalfEven['patient_count'] > 0){                        
                        $morningVisits =  $rowOnBehalfMorn['patient_count'] .' cs - ' . $rowOnBehalfMorn['on_behalf_fees_count']  .' rs';
                        $evengVisits   =  $rowOnBehalfEven['patient_count'] .' cs - ' . $rowOnBehalfEven['on_behalf_fees_count']  .' rs';
                    } else if($rowOnBehalfMorn['patient_count'] > 0){
                        $morningVisits =  $rowOnBehalfMorn['patient_count'] .' cs - ' . $rowOnBehalfMorn['on_behalf_fees_count']  .' rs';
                        $evengVisits   =  '0 cs - 0 rs';
                    } else if($rowOnBehalfEven['patient_count'] > 0){
                        $morningVisits   =  '0 cs - 0 rs';
                        $evengVisits   =  $rowOnBehalfEven['patient_count'] .' cs - ' . $rowOnBehalfEven['on_behalf_fees_count']  .' rs';
                    }

                    $doctor =  $case_count .' cs - ' . $sum_amount  .' rs';
                    $doctor_lab_amount =  $lab_amount.' rs';
                }

                if($rowCat['value'] == 'Duty Doctor'){
                    // To check the pat visited in morning by duty dr
                    $morningVisitsDutyDr = '';
                    $evengVisitsDutyDr = '';

                    /*$sqlOnBehalfMornDutyDr = "
                    SELECT COUNT(ev.patient_visit_id) AS patient_count
                          ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Duty Doctor'
                      AND pv.check_up_time > '07:00'
                      AND pv.check_up_time < '14:55'
                      {$appendSql}
                    ";
                    $resultOnBehalfMornDutyDr = $db->sql_query($sqlOnBehalfMornDutyDr);
                    $rowOnBehalfMornDutyDr    = $db->sql_fetchrow($resultOnBehalfMornDutyDr);

                    // To check the pat visited in evening by duty dr
                    $sqlOnBehalfEvenDutyDr = "
                    SELECT COUNT(ev.patient_visit_id) AS patient_count
                          ,SUM(ev.consultation_fees) AS on_behalf_fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND e.category = 'Duty Doctor'
                      AND pv.check_up_time > '15:00'
                      AND pv.check_up_time < '23:50'
                      {$appendSql}
                    ";
                    $resultOnBehalfEvenDutyDr = $db->sql_query($sqlOnBehalfEvenDutyDr);
                    $rowOnBehalfEvenDutyDr    = $db->sql_fetchrow($resultOnBehalfEvenDutyDr);

                    if($rowOnBehalfMornDutyDr['patient_count'] > 0 && $rowOnBehalfEvenDutyDr['patient_count'] > 0){                        
                        $morningVisitsDutyDr =  ' [ MORNG :'.$rowOnBehalfMornDutyDr['patient_count'] .' cs - ' . $rowOnBehalfMornDutyDr['on_behalf_fees_count']  .' rs]';
                        $evengVisitsDutyDr   =  ' - [ EVENG :'.$rowOnBehalfEvenDutyDr['patient_count'] .' cs - ' . $rowOnBehalfEvenDutyDr['on_behalf_fees_count']  .' rs]';
                    } else if($rowOnBehalfMornDutyDr['patient_count'] > 0){
                        $morningVisitsDutyDr =  ' : [ MORNG :'.$rowOnBehalfMornDutyDr['patient_count'] .' cs - ' . $rowOnBehalfMornDutyDr['on_behalf_fees_count']  .' rs]';
                        $evengVisitsDutyDr   =  ' - [ EVENG : 0]';
                    } else if($rowOnBehalfEvenDutyDr['patient_count'] > 0){
                        $morningVisitsDutyDr   =  ' [ MORNG : 0] ';
                        $evengVisitsDutyDr   =  ' - [ EVENG :'.$rowOnBehalfEvenDutyDr['patient_count'] .' cs - ' . $rowOnBehalfEvenDutyDr['on_behalf_fees_count']  .' rs]';
                    }*/

                    $duty_doctor =  $case_count .' cs - ' . $sum_amount  .' rs' . $morningVisitsDutyDr.$evengVisitsDutyDr;
                    $duty_doctor_lab_amount =  $lab_amount.' rs';
                }
                
                if($rowCat['value'] == 'Staff' || $rowCat['value'] == 'Student'){
                    $sqlStaff = "
                    SELECT  COUNT(ev.patient_visit_id) AS patient_count
                           ,SUM(ev.consultation_fees) AS fees_count
                    FROM employee_visit ev
                    LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                    LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                    WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$row['check_up_date']}'
                      AND e.first_name != ''
                      AND pv.status != 'Cancelled'
                      AND (e.category = 'Staff' OR  e.category = 'Student')
                      {$appendSql}
                    ";
                    $resultStaff = $db->sql_query($sqlStaff);
                    $rowStaff = $db->sql_fetchrow($resultStaff);

                    $staff    =  $rowStaff['patient_count'] .' cs - ' . $rowStaff['fees_count'].' rs';
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
            $totalLabAmount = $doctor_lab_amount + $duty_doctor_lab_amount;

            $tdCons = "";
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                if($cpSiteIdSession == 1){
                    $tdDrMorn = "<td>{$morningVisits}</td>";
                    $tdCons = "<td>{$consultant}</td>";
                    $tdTotCons = "<td class='txtRight'>{$total_consultant}</td>";
                }
                else if($cpSiteIdSession == 2){
                    $tdDrMorn = "";
                    $tdCons = "";
                    $tdTotCons = "";
                }
                else if($cpSiteIdSession == 3){
                    $tdDrMorn = "<td>{$morningVisits}</td>";
                    $tdCons = "";
                    $tdTotCons = "";
                }
            }
            else{
                $tdDrMorn = "<td>{$morningVisits}</td>";
                $tdTotCons = "<td class='txtRight'>{$total_consultant}</td>";
            }

            $rows .= "
            <tr>
                <td>{$check_up_date}</td>
                <td>{$row['day']}</td>
                {$tdDrMorn}
                <td>{$evengVisits}</td>
                <td>{$doctor}</td>
                <td>{$duty_doctor}</td>
                <td>{$staff}</td>
                {$tdCons}
                <td>{$doctor_lab_amount}/{$duty_doctor_lab_amount} ({$totalLabAmount})</td>
                <td class='txtRight'>{$total}</td>
                {$tdTotCons}
            </tr>
            ";
            $totalOverAllCase += $total_case_count;
            $totalOverAll += $total_amount;
            $totalOverAllCaseConsult += $total_case_count - $total_case_count_consult;
            $totalOverAllConsult += $total_amount - $total_amount_consult;
            $totalOverAllDrLabAmt += $doctor_lab_amount;
            $totalOverAllDutyDrLabAmt += $duty_doctor_lab_amount;
        }
        //$totalOverAll = number_format(round($totalOverAll), 2);
        $tot= '';
        $totalOverAllLabAmount = $totalOverAllDrLabAmt + $totalOverAllDutyDrLabAmt;
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($cpSiteIdSession == 1){
                $colspan = 8;
                $tot = "<td class='txtRight lastRowBgColor'>{$totalOverAllCaseConsult} - {$totalOverAllConsult}</td>";
            }
            else if($cpSiteIdSession == 2){
                $colspan = 6;
                $tol = '';
            }
            else if($cpSiteIdSession == 3){
                $colspan = 7;
                $tol = '';
            }
        }
        else{
            $colspan = 8;
            $tot = "<td class='txtRight lastRowBgColor'>{$totalOverAllCase} - {$totalOverAll}</td>";
        }

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan={$colspan}>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllDrLabAmt}/{$totalOverAllDutyDrLabAmt} ($totalOverAllLabAmount)</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllCase} - {$totalOverAll}</td>
            {$tot}
        </tr>
        ";

        return $text;
    }

}