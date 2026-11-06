<?
class CPL_Admin_Widgets_Hms_BalanceSheetReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <h2>Balance Sheet Visit Report</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist' width='100%'>
				<thead>
					<tr>
						<th>Income</th>
						<th>Expense</th>
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

        $total_amount_visit       = 0;
        $totaltestamount          = 0;
        $totaltestamount1         = 0;
        $totaltestamount2         = 0;
        $totalOverAllLabtest      = 0;
        $totalOverAllinPatient    = 0;
        $totalAllIPAdminCharges   = 0;
        $totalAllIPTheatreCharges = 0;

        $monthValAppendSql  = '';
        $yearValAppendSql   = '';
        $startDateAppendSql = '';

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $current_date = date('Y-m-d');

        if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date   = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal != '' && $yearVal != ''){
            $monthValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
            $yearValAppendSql  = "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQLSub = "
        SELECT COUNT(ev.patient_visit_id) AS patient_count
              ,SUM(ev.consultation_fees) AS fees_count
        FROM employee_visit ev
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
        WHERE e.first_name != ''
          AND pv.status != 'Cancelled'
          {$startDateAppendSql}
          {$monthValAppendSql}
          {$yearValAppendSql}
          {$appendSql}
        ";
        $resultSub = $db->sql_query($SQLSub);
        while ($rowSub = $db->sql_fetchrow($resultSub)) {
            $sum_amount = $rowSub['fees_count'];
        }
        $total_amount_visit += $sum_amount;

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND m.site_id = {$cpSiteIdSession}";
        }

        $monthValAppendSql  = '';
        $yearValAppendSql   = '';
        $startDateAppendSql = '';
        /*
        if ($start_date == '') {
            if ($monthVal != '') {
                $monthValAppendSql = "AND DATE_FORMAT(m.creation_date, '%m') = '{$monthVal}'" ;
            }
        }
        
        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(m.creation_date, '%Y') = '{$yearVal}'" ;
        } 

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        }*/

        if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        } else if ($monthVal != '' && $yearVal != ''){
            $monthValAppendSql = "AND DATE_FORMAT(m.creation_date, '%m') = '{$monthVal}'" ;
            $yearValAppendSql  = "AND DATE_FORMAT(m.creation_date, '%Y') = '{$yearVal}'" ;
        }

        $startDateAppendSql = '';
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        /*if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        } */

        if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal != '' && $yearVal != ''){
            $monthValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
            $yearValAppendSql  = "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND inp.site_id = {$cpSiteIdSession}";
        }

        $SQLIP = "
        SELECT pv.check_up_date 
              ,inp.amount 
              ,inp.nursing_fees 
              ,inp.other_fees 
              ,inp.in_patient_id
              ,inp.surgeon_fees
              ,inp.theatre_charges
              ,inp.anesthetic_fees
              ,inp.theater_assistant_fees
        FROM in_patient inp
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id= inp.patient_visit_id)
        WHERE inp.in_patient_id > 0
        {$appendSqlSite}
        {$startDateAppendSql}
        {$monthValAppendSql}
        {$yearValAppendSql}
         ";
        $resultIP = $db->sql_query($SQLIP);
        while ($rowIP    = $db->sql_fetchrow($resultIP)) {
            $SQLEmpVisit = "
            SELECT SUM(consultation_fees) AS consultation_fees
                   ,employee_in_patient_id
            FROM employee_in_patient
            WHERE in_patient_id = {$rowIP['in_patient_id']}
            ORDER BY employee_in_patient_id ASC
            ";
            $resultEmpVisit = $db->sql_query($SQLEmpVisit);
            $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

            $totalIPAdminCharges   = $rowEmpVisit['consultation_fees'] + $rowIP['amount'] + $rowIP['nursing_fees'] + $rowIP['other_fees'];
            $totalIPTheatreCharges = $rowIP['theatre_charges'];
            $totalOverAllIP        = $totalIPAdminCharges + $totalIPTheatreCharges;

            $totalOverAllinPatient    += $totalOverAllIP;
            $totalAllIPAdminCharges   += $totalIPAdminCharges;
            $totalAllIPTheatreCharges += $totalIPTheatreCharges;
        }

        //Expense related codes// 
        $sqlgroup = "
        SELECT expense_group_id 
              ,title
        FROM expense_group
        WHERE title != 'PHARMACY'
          AND title != 'LAB'
        ";
        $resultgroup = $db->sql_query($sqlgroup);
        $expense_group = '';
        $amount = 0;
        $expense_amount ='';
        $overAllExpense1 = 0;
        while ($rowgroup    = $db->sql_fetchrow($resultgroup)) {
            $appendSqlSite = '';
            $source = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
                if($cpSiteIdSession == 1){
                    $source = 'Hab Hosp Income';
                }
                if($cpSiteIdSession == 2){
                    $source = 'Cres Clinic Income';
                }
                if($cpSiteIdSession == 3){
                    $source = 'EV Clinic Income';
                }
            }

            $startDateAppendSql = '';
            $monthValAppendSql = '';
            $yearValAppendSql = '';
            /*if ($start_date != '' && $end_date == '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else {
                if($monthVal != ''){
                    $month = $monthVal;
                }

                if($yearVal != ''){
                    $year = $yearVal;
                }

                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            }

            if ($monthVal != '') {
                $startDateAppendSql .= "AND DATE_FORMAT(e.date, '%m') = '{$monthVal}'" ;
            }

            if ($yearVal != '') {
                $startDateAppendSql .= "AND DATE_FORMAT(e.date, '%Y') = '{$yearVal}'" ;
            } */

            if ($start_date != '' && $end_date != '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$current_date}'";
            } else if ($monthVal == '' && $yearVal == ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else if ($monthVal != '' && $yearVal != ''){
                $monthValAppendSql = "AND DATE_FORMAT(e.date, '%m') = '{$monthVal}'" ;
                $yearValAppendSql  = "AND DATE_FORMAT(e.date, '%Y') = '{$yearVal}'" ;
            }

            $sqlexp = "
            SELECT SUM(e.amount) AS amount
                  ,e.group
                  ,e.source
                  ,es.title AS sub_title
            FROM expense e
            LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
            WHERE e.group = {$rowgroup['expense_group_id']}
            AND e.source = '{$source}'
            {$appendSqlSite}
            {$startDateAppendSql}
            {$monthValAppendSql}
            {$yearValAppendSql}
            GROUP BY e.group
            ";
            $resultexp = $db->sql_query($sqlexp);
            $amount = 0;
            while ($rowexp = $db->sql_fetchrow($resultexp)) {
                $amount += $rowexp['amount'];
            }
            $amountFormat = number_format($amount, 2);

            $sqlexp1 = "
            SELECT SUM(e.amount) AS amount
                  ,e.group
                  ,e.source
                  ,es.title AS sub_title
            FROM expense e
            LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
            WHERE e.group = {$rowgroup['expense_group_id']}
            AND e.source = '{$source}'
            {$appendSqlSite}
            {$startDateAppendSql}
            {$monthValAppendSql}
            {$yearValAppendSql}
            GROUP BY es.expense_sub_group_id
            ORDER BY es.title ASC
            ";
            $resultexp1 = $db->sql_query($sqlexp1);
            $subtitle = '';
            while ($rowexp1 = $db->sql_fetchrow($resultexp1)) {
                $class = '';
                /*if(substr($rowexp1['source'], 0,2) != 'hab'){
                    $class='bgColorHighlight';
                }*/
                /*$subtitle .= "
                <tr class='{$class}'>
                <td>{$date}</td>
                <td>{$rowexp1['sub_title']}</td>
                <td align='right'>{$rowexp1['amount']}</td>
                <td>{$rowexp1['description']}</td>
                <td>{$rowexp1['source']}</td>
                </tr>";
                */

                $sqlexpOverall = "
                SELECT SUM(e.amount) AS amount
                      ,e.group
                      ,e.source
                      ,es.title AS sub_title
                FROM expense e
                LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
                WHERE e.group = {$rowexp1['group']}
                {$appendSqlSite}
                {$startDateAppendSql}
                {$monthValAppendSql}
                {$yearValAppendSql}
                AND es.title = '{$rowexp1['sub_title']}'
                ";
                $resultexpOverall = $db->sql_query($sqlexpOverall);
                $amountOverall = 0;
                $rowexpOverall = $db->sql_fetchrow($resultexpOverall);
                $amountOverall = $rowexpOverall['amount'];
                $amountOverallFormat = number_format($amountOverall, 2);

                $sqlexpSubDetail = "
                SELECT e.amount
                      ,e.group
                      ,es.title AS sub_title
                      ,e.description
                      ,e.source
                      ,e.date
                FROM expense e
                LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
                WHERE e.group = {$rowexp1['group']}
                AND e.source = '{$rowexp1['source']}'
                {$appendSqlSite}
                {$startDateAppendSql}
                {$monthValAppendSql}
                {$yearValAppendSql}
                AND es.title = '{$rowexp1['sub_title']}'
                ";
                $resultexpSubDetail = $db->sql_query($sqlexpSubDetail);
                $subHeadtitle = '';
                while ($rowexpSubDetail = $db->sql_fetchrow($resultexpSubDetail)) {
                    $date = $fn->getCPDate($rowexpSubDetail['date'],"d-m-Y");

                    $subHeadtitle .= "
                    <tr class='{$class}'>
                        <td>{$date}</td>
                        <td>{$rowexpSubDetail['sub_title']}</td>
                        <td>{$rowexpSubDetail['description']}</td>
                        <td>{$rowexpSubDetail['source']}</td>
                        <td align='right'>{$rowexpSubDetail['amount']}</td>
                    </tr>";
                }

                $subtitle .= "
                <tr class='{$class}'>
                    <td class='expenseDetailsSubHead'>
                        <div class='expenseSubHeadDetails'>+ {$rowexp1['sub_title']}</div>
                        <div class='subTitlesWithoutGroup'><table>{$subHeadtitle}</table></div>
                    </td>
                    <td align='right'>{$rowexp1['amount']}</td>
                    <td>[Overall Amount: {$amountOverallFormat}]</td>
                </tr>";
            }

            $expense_group .= "
            <table width=100%>
                <tr>
                    <td width = 85% class='expenseDetailsHead'>
                    <div class='expenseDetails'>+ {$rowgroup['title']}</div>
                    <div class='subTitles'><table>{$subtitle}</table></div>
                    </td>
                    <td width = 15% align='right'>
                    {$amountFormat}
                    </td>
                </tr>
            </table>
            "; 

            $overAllExpense1 += $amount;
        }
 
        /* EXPENSE RECORDS FROM HOSPITAL SOURCE */
        $source = '';
        if($cpSiteIdSession == 1){
            $source = 'Hab Hosp Income';
        } else if($cpSiteIdSession == 2){
            $source = 'Cres Clinic Income';
        } else if($cpSiteIdSession == 3){
            $source = 'EV Clinic Income';
        }
        $overAllExpense2 = 0;
        if($source != ''){
            $appendSqlSite = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
            }

            $startDateAppendSql = '';
            $monthValAppendSql = '';
            $yearValAppendSql = '';

            if ($start_date != '' && $end_date != '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$current_date}'";
            } else if ($monthVal == '' && $yearVal == ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else if ($monthVal != '' && $yearVal != ''){
                $monthValAppendSql = "AND DATE_FORMAT(e.date, '%m') = '{$monthVal}'" ;
                $yearValAppendSql  = "AND DATE_FORMAT(e.date, '%Y') = '{$yearVal}'" ;
            }

            $sqlexpHead = "
            SELECT e.source
                  ,eg.title AS group_title
                  ,eg.expense_group_id
            FROM expense e
            LEFT JOIN expense_group eg ON (eg.expense_group_id = e.group)
            WHERE e.source = '{$source}'
              AND (eg.title = 'LAB'
              OR eg.title = 'PHARMACY')
            {$appendSqlSite}
            {$startDateAppendSql}
            {$monthValAppendSql}
            {$yearValAppendSql}
            GROUP BY e.group
            ";
            $resultexpHead = $db->sql_query($sqlexpHead);
            while ($rowexpHead = $db->sql_fetchrow($resultexpHead)) {
                $sqlexp = "
                SELECT SUM(e.amount) AS amount
                      ,e.group
                      ,es.title AS sub_title
                FROM expense e
                LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
                WHERE e.group = {$rowexpHead['expense_group_id']}
                  AND e.source = '{$source}'
                {$appendSqlSite}
                {$startDateAppendSql}
                {$monthValAppendSql}
                {$yearValAppendSql}
                GROUP BY e.group
                ";
                $resultexp = $db->sql_query($sqlexp);
                $amount = 0;
                while ($rowexp = $db->sql_fetchrow($resultexp)) {
                    $amount += $rowexp['amount'];
                }
                $amountFormat = number_format($amount, 2);

                $sqlexp1 = "
                SELECT SUM(e.amount) AS amount
                      ,e.group
                      ,es.title AS sub_title
                      ,e.source
                FROM expense e
                LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
                WHERE e.group = {$rowexpHead['expense_group_id']}
                  AND e.source = '{$source}'
                {$appendSqlSite}
                {$startDateAppendSql}
                {$monthValAppendSql}
                {$yearValAppendSql}
                GROUP BY es.expense_sub_group_id
                ORDER BY es.title ASC
                ";
                $resultexp1 = $db->sql_query($sqlexp1);
                $subtitle = '';
                while ($rowexp1 = $db->sql_fetchrow($resultexp1)) {

                    $sqlexpOverall = "
                    SELECT SUM(e.amount) AS amount
                          ,e.group
                          ,e.source
                          ,es.title AS sub_title
                    FROM expense e
                    LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
                    WHERE e.group = {$rowexp1['group']}
                    {$appendSqlSite}
                    {$startDateAppendSql}
                    {$monthValAppendSql}
                    {$yearValAppendSql}
                    AND es.title = '{$rowexp1['sub_title']}'
                    ";
                    $resultexpOverall = $db->sql_query($sqlexpOverall);
                    $amountOverall = 0;
                    $rowexpOverall = $db->sql_fetchrow($resultexpOverall);
                    $amountOverall = $rowexpOverall['amount'];
                    $amountOverallFormat = number_format($amountOverall, 2);

                    $sqlexpSubDetail = "
                    SELECT e.amount
                          ,e.group
                          ,es.title AS sub_title
                          ,e.description
                          ,e.source
                          ,e.date
                    FROM expense e
                    LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
                    WHERE e.group = {$rowexp1['group']}
                    AND e.source = '{$rowexp1['source']}'
                    {$appendSqlSite}
                    {$startDateAppendSql}
                    {$monthValAppendSql}
                    {$yearValAppendSql}
                    AND es.title = '{$rowexp1['sub_title']}'
                    ";
                    $resultexpSubDetail = $db->sql_query($sqlexpSubDetail);
                    $subHeadtitle = '';
                    while ($rowexpSubDetail = $db->sql_fetchrow($resultexpSubDetail)) {
                        $date = $fn->getCPDate($rowexpSubDetail['date'],"d-m-Y");

                        $subHeadtitle .= "
                        <tr>
                            <td>{$date}</td>
                            <td>{$rowexpSubDetail['sub_title']}</td>
                            <td>{$rowexpSubDetail['description']}</td>
                            <td>{$rowexpSubDetail['source']}</td>
                            <td align='right'>{$rowexpSubDetail['amount']}</td>
                        </tr>";
                    }

                    $subtitle .= "
                    <tr>
                        <td class='expenseDetailsSubHead'>
                            <div class='expenseSubHeadDetails'>+ {$rowexp1['sub_title']}</div>
                            <div class='subTitlesWithoutGroup'><table>{$subHeadtitle}</table></div>
                        </td>
                        <td align='right'>{$rowexp1['amount']}</td>
                        <td>[Overall Amount: {$amountOverallFormat}]</td>
                    </tr>";
                }
                $expense_group .= "
                <table width=100%>
                    <tr>
                        <td width = 85% class='expenseDetailsHead'>
                        <div class='expenseDetails'>+ {$rowexpHead['group_title']}</div>
                        <div class='subTitles'><table>{$subtitle}</table></div>
                        </td>
                        <td width = 15% align='right'>
                        {$amountFormat}
                        </td>
                    </tr>
                </table>
                "; 
                $overAllExpense2 += $amount;
            }
        }

        $overAllExpense = $overAllExpense1 + $overAllExpense2;

        $admissionChargesTr = '';
        $theatreChargesTr   = '';

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            if($cpSiteIdSession == 1){
                $admissionChargesTr = "
                    <tr>
                        <td width = '70%'>Admission Charges</td>
                        <td width = '30%' align='right'>{$totalAllIPAdminCharges}</td>
                    </tr>
                ";

                $theatreChargesTr = "
                    <tr>
                        <td width = '70%'>Theater Charges</td>
                        <td width = '30%' align='right'>{$totalAllIPTheatreCharges}</td>
                    </tr>
                ";
            }
            else{
                $totalOverAllinPatient = 0;
            }
        }

        $overAllIncome         = $total_amount_visit + $totalOverAllinPatient ;
        $overAllProfit         = $overAllIncome - $overAllExpense;
        $overAllIncome         = number_format($overAllIncome, 2);
        $overAllExpense        = number_format($overAllExpense, 2);
        $overAllProfit         = number_format($overAllProfit, 2);
        $totalOverAllinPatient = number_format($totalOverAllinPatient, 2);
        $total_amount_visit    = number_format($total_amount_visit, 2);

        $totalAllIPAdminCharges   = number_format($totalAllIPAdminCharges, 2);
        $totalAllIPTheatreCharges = number_format($totalAllIPTheatreCharges, 2);

        $text = "
        <tr>
            <td class='incomeReport' width='30%'>
                <table width=100%>
                    <tr>
                        <td width = '70%'><span>Patient Visit</span></td>
                        <td width = '30%' align='right'>{$total_amount_visit}</td>
                    </tr>
                    {$admissionChargesTr}
                    {$theatreChargesTr}
                </table>
            </td>
            <td class='incomeReport' width='70%'>
                {$expense_group}
            </td>
        </tr>
        <tr>
            <td class='totalValue'>
                <div class='float_left'>Total</div> <div class='float_right'>{$overAllIncome}</div>
            </td>
            <td class='totalValue' align='right'>
                <div class='float_left'>Total</div> <div class='float_right'>{$overAllExpense}</div>
            </td>
        </tr>
        <tr>
            <td class='totalValue lastRowBgColor'>
                <div class='float_left '>Balance</div> <div class='float_right'>{$overAllProfit}</div>
            </td>
            <td class='' align='right'>
            </td>
        </tr>
        ";

        return $text;
    }

}