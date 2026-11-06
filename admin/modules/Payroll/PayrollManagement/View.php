<?
class CPL_Admin_Modules_Payroll_PayrollManagement_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            //$email     = $row['email'];
            //$website   = $row['website'];

            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');

            /*if($age < 55){
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE to_age <= '55'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            } elseif ($age > 55 && $age <= 60) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '56' AND to_age = '60'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            } elseif ($age > 60 && $age <= 65) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '61' AND to_age = '65'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            } elseif ($age > 65) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age >= '66'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }*/

            $OT  = $row['ot_hours'] * $row['overtime_pay_rate'];
            $gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'];

            $cpf = 0;
            $cpfE = 0;
            if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $sprCondition = '';
                if($row['spr_year'] != '' && $row['citizen'] == 'PR'){
                    $sprCondition = "AND spr_year = {$row['spr_year']}";
                } else {
                    $sprCondition = "AND spr_year = 3";
                }

                $SQLPercentageCPF = "
                SELECT by_employer
                      ,by_employee
                      ,cap_amount_employer
                      ,cap_amount_employee
                FROM cpf_calculator
                WHERE {$age} BETWEEN from_age AND to_age
                  AND {$gross_pay} BETWEEN from_salary AND to_salary
                  AND year = {$year}
                  {$sprCondition}
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

                $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                $cpf = round($cpf, 2);

                if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                    $rowPercentageCPF['cap_amount_employer'] != 0){
                    $cpf = $rowPercentageCPF['cap_amount_employer'];
                }

                $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                $cpfE = round($cpfE, 2);

                if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                    $rowPercentageCPF['cap_amount_employee'] != 0){
                    $cpfE = $rowPercentageCPF['cap_amount_employee'];
                }
            }

            $total_deduction = round($cpfE, 2) + $row['mbf'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'];
            $total_deduction_display = number_format($total_deduction, 2);
            $net_total = $gross_pay - $total_deduction;
            $net_total = number_format($net_total, 2);
            $urlPrintLinkPdf  = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=payslipprintPdf&payroll_management_id={$row['payroll_management_id']}&employee_id={$row['employee_id']}&showHTML=0";

            $employee_name = "<a href='index.php?_topRm={$tv['topRm']}&module=payroll_employee&record_id={$row['employee_id']}&_action=edit'>{$row['employee_name']}</a>";
            
            $payroll_month = $dateUtil->getShortMonthName($row['payroll_month']);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($employee_name)}
            {$listObj->getListDataCell("<a href='{$urlPrintLinkPdf}' target='_blank'>Pay slip print</a>")}
            {$listObj->getListDataCell($payroll_month, 'center')}
            {$listObj->getListDataCell($row['payroll_year'], 'center')}
            {$listObj->getListDataCell($row['basic_pay'], 'right')}
            {$listObj->getListDataCell($OT)}
            {$listObj->getListDataCell(number_format($cpf, 2), 'right')}
            {$listObj->getListDataCell(number_format($cpfE, 2), 'right')}
            {$listObj->getListDataCell($row['allowance1'], 'right')}
            {$listObj->getListDataCell($total_deduction_display, 'right')}
            {$listObj->getListDataCell($net_total, 'right')}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['payroll_management_id'])}
            ";

            $count++ ;
        }

        $printPdfLink = "<a href='#' class='button printPayslipForAllLink'>
                            Print All Payslip
                        </a>";

        //$search_List    = "index.php?_topRm=main&module=payroll_jobInformation";
        $current_Month = date('m') - 1;
        if ($current_Month <= 9 && $current_Month > 0) {
            $current_Month = 0 . $current_Month;
        } else if ($current_Month == 0) {
            $current_Month = 12;
        } else {
            $current_Month = $current_Month;
        }

        if ($current_Month == 12) {
            $current_Year  = date('Y') - 1;
        } else {
            $current_Year  = date('Y');
        }

        /* Find the employees for whom Job information is not created */
        $sqlEmp = "SELECT e.first_name 
                   FROM employee e
                   WHERE e.status = 'Current'
                   AND e.employee_id NOT IN 
                   (SELECT ji.employee_id
                    FROM job_information ji
                    WHERE e.employee_id = ji.employee_id
                      AND ji.status = 'Current')
                   ";
        $resultEmp = $db->sql_query($sqlEmp);
        $numRowsEmp = $db->sql_numrows($resultEmp);
        $rowsEmp = '';
        $count = 1;
        while($rowEmp = $db->sql_fetchrow($resultEmp)) {
            if ($count == $numRowsEmp) {
                $rowsEmp .= $rowEmp['first_name'];
            } else {
                $rowsEmp .= $rowEmp['first_name'] . ', ';
            }
            $count++;
        }

        $message = '';
        if ($numRowsEmp) {
            $message = "
            <div class='txtCenter'>Please create Job information records for the below employees to make them appear in payroll.<br/>
            {$rowsEmp}
            </div>
            ";
        }

        $recCount = $fn->getRecordCount('payroll_management', "payroll_month = '{$current_Month}' AND payroll_year = '{$current_Year}'");
        $text = "
        {$message}
            <div class='floatbox'>
                <div class='float_left'>
                    <a current_Month = '{$current_Month}' record_count='{$recCount}' current_Year = '{$current_Year}'  class='button GenerateRecords'>Generate Records</a>
                </div>
                <div class='float_left'>
                    {$printPdfLink}
                </div>
            </div>

        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Employee Name', 's.employee_name')}
        {$listObj->getListHeaderCell('Pay slip print', '$urlPrintLinkPdf')}
        {$listObj->getListHeaderCell('Month', 'pm.payroll_month')}
        {$listObj->getListHeaderCell('Year', 'pm.payroll_year')}
        {$listObj->getListHeaderCell('Basic Pay', 'j.basic_pay')}
        {$listObj->getListHeaderCell('OT', '$OT')}
        {$listObj->getListHeaderCell('CPF(Employer)', '$cpf')}
        {$listObj->getListHeaderCell('CPF(Employee)', '$cpfE')}
        {$listObj->getListHeaderCell('Allowance', 'j.allowance1')}
        {$listObj->getListHeaderCell('Deductions', 'pm.cpf_deduction')}
        {$listObj->getListHeaderCell('Net Pay', 'pm.net_total')}
        {$listObj->getListHeaderCell('Status', 'pm.status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Employee ID', 'employee_id')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }
    /**
     *
     */
    function getNew1(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";


        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $expStf = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');

        $status = $fn->getReqParam('status');

        $StatusArray = array(
              "Paid"
             ,"Approved"
             ,"Generated"
             ,"Hold"
             ,"Cancelled"
        );

        $lastMonthRecord = $fn->getRecordCount('payroll_management', "payroll_month > '{$row['payroll_month']}' AND payroll_year = '{$row['payroll_year']}' AND employee_id = {$row['employee_id']}");

        if($lastMonthRecord > 0){
            $exptd = array('isEditable' => 0);
        }else{
            $exptd = array('isEditable' => 1);
        }

            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');

            /*if($age <= 50){
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = 'below' AND to_age = '50'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

            }elseif ($age >= 51 && $age <= 55) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '51' AND to_age = '55'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }elseif ($age >= 56 && $age <= 60) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '56' AND to_age = '60'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }elseif ($age >= 60 && $age <= 65) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '61' AND to_age = '65'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }elseif ($age > 65) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '61' AND to_age = 'Above'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }*/
            $overtime_pay_normal = $row['overtimepaynormal'] * $row['ot_hours'];
            $overtime_pay_normal = number_format($overtime_pay_normal, 2);
            $overtime_pay_sunday = $row['overtimepaysunday'] * $row['ot_hours_sunday'];
            $overtime_pay_sunday = number_format($overtime_pay_sunday, 2);
            $overtime_pay_holiday = $row['overtimepayholiday'] * $row['ot_hours_holiday'];
            $overtime_pay_holiday = number_format($overtime_pay_holiday, 2);
            $ot_amount = $overtime_pay_normal + $overtime_pay_sunday + $overtime_pay_holiday;
            //$OT = $row['ot_hours'] * $row['overtime_pay_rate'];
            //$gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'];
            $gross_pay = $row['basic_pay'] + $ot_amount + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['performance_entitlement'] + $row['referral_incentive'] + $row['incentive'];

        $cpf = 0;
        if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
            $sprCondition = '';
            if($row['spr_year'] != '' && $row['citizen'] == 'PR'){
                $sprCondition = "AND spr_year = {$row['spr_year']}";
            } else {
                $sprCondition = "AND spr_year = '3'";
            }

            $SQLPercentageCPF = "
            SELECT by_employer
                  ,cap_amount_employer
            FROM cpf_calculator
            WHERE {$age} BETWEEN from_age AND to_age
              AND {$gross_pay} BETWEEN from_salary AND to_salary
              AND year = {$year}
              {$sprCondition}
            ";
            $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
            $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

            $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
            $cpf = round($cpf, 2);

            if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                $rowPercentageCPF['cap_amount_employer'] != 0){
                $cpf = $rowPercentageCPF['cap_amount_employer'];
            }
        }
            //$total_deduction = $cpf + $row['mbf'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'];
            //$total_deduction = $cpf + $row['mbf'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'] + $row['deduction_epf'];
            $total_deduction = $row['deduction_epf'] + $row['deduction_sosco'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['other_deduction'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'];
            $net_total = $gross_pay - $total_deduction;


        if($row['status'] == 'Paid'){
            $paidDateObjClass = "";
        }else{
            $paidDateObjClass = "displayNone passType";
        }

        $payment_method  = $fn->getReqParam('payment_method');

        $paymentArray = array(
              "cheque"
             ,"cash"
             ,"giro payment transfer"
        );

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='20%'>{$formObj->getTBRow('Employee Name', '', $row['employee_name'], $expNoEdit)}</td>
                                <td width='15%'>{$formObj->getDateRow('Generated Date', 'generated_date', $row['generated_date'])}</td>
                                <td width='15%'>{$formObj->getTBRow('Basic Pay', '', $row['basic_pay'], $expNoEdit)}</td>
                                <td width='15%'>{$formObj->getDDRowByArr('Status', 'status', $StatusArray, $row['status'], $exptd)}</td>
                                <td width='15%'>{$formObj->getDDRowByArr('Payment Method <br/>(Transfer / Cheque / Cash)', 'payment_method', $paymentArray, $row['payment_method'], $exptd)}</td>
                                <td width='20%' class='{$paidDateObjClass}'><div >{$formObj->getDateRow('Paid Date', 'paid_date', $row['paid_date'])}</div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class='linkPortalDataWrapper payDetails'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th colspan='2'>EARNINGS</th>
                                <th colspan='2'>DEDUCTIONS</th>
                            </tr>
                            <tr>
                                <td>Basic Pay</td>
                                <td class='basicPayRate'>{$row['basic_pay']}</td>
                                <td>EPF</td>
                                <td>{$formObj->getTBRow('', 'deduction_epf', $row['deduction_epf'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay Rate/ Hour (Normal)</td>
                                <td>{$formObj->getTBRow('', 'overtimepaynormal', $row['overtimepaynormal'], $expNoEdit)}</td>
                                <td>Sosco</td>
                                <td>{$formObj->getTBRow('', 'deduction_sosco', $row['deduction_sosco'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>OT Hours (Normal)</td>
                                <td>{$formObj->getTBRow('', 'ot_hours', $row['ot_hours'], $exptd)}</td>
                                <td>Income Tax</td>
                                <td>{$formObj->getTBRow('', 'income_tax_amount', $row['income_tax_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay (Normal)</td>
                                <td>{$formObj->getTBRow('', 'overtime_pay_normal', $overtime_pay_normal, $exptd)}</td>
                                <td>Advance Payment</td>
                                <td>{$formObj->getTBRow('', 'loan_amount', $row['loan_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay Rate/ Hour (Sunday)</td>
                                <td>{$formObj->getTBRow('', 'overtimepaysunday', $row['overtimepaysunday'], $expNoEdit)}</td>
                                <td>Accommodation</td>
                                <td>{$formObj->getTBRow('', 'accommodation', $row['accommodation'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>OT Hours (sunday)</td>
                                <td>{$formObj->getTBRow('', 'ot_hours_sunday', $row['ot_hours_sunday'], $exptd)}</td>
                                <td>Absence</td>
                                <td>{$formObj->getTBRow('', 'absence', $row['absence'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay (sunday)</td>
                                <td>{$formObj->getTBRow('', 'overtime_pay_sunday', $overtime_pay_sunday, $exptd)}</td>
                                <td>Other Deduction</td>
                                <td>{$formObj->getTBRow('', 'other_deduction', $row['other_deduction'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay Rate/ Hour (Holiday)</td>
                                <td>{$formObj->getTBRow('', 'overtimepayholiday', $row['overtimepayholiday'], $expNoEdit)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>OT Hours (Holiday)</td>
                                <td>{$formObj->getTBRow('', 'ot_hours_holiday', $row['ot_hours_holiday'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Overtime Pay (Holiday)</td>
                                <td>{$formObj->getTBRow('', 'overtime_pay_holiday', $overtime_pay_holiday, $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Overtime Amount</td>
                                <td>{$formObj->getTBRow('', 'ot_amount', $ot_amount, $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Commission</td>
                                <td>{$formObj->getTBRow('', 'commission', $row['commission'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Allowance (Attendance)</td>
                                <td>{$formObj->getTBRow('', 'allowance1', $row['allowance1'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Allowance (Hardship)</td>
                                <td>{$formObj->getTBRow('', 'allowance2', $row['allowance2'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Allowance (Travel)</td>
                                <td>{$formObj->getTBRow('', 'allowance3', $row['allowance3'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Performance Entitlement</td>
                                <td>{$formObj->getTBRow('', 'performance_entitlement', $row['performance_entitlement'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Referral Incentive</td>
                                <td>{$formObj->getTBRow('', 'referral_incentive', $row['referral_incentive'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Incentive</td>
                                <td>{$formObj->getTBRow('', 'incentive', $row['incentive'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Gross Pay</th>
                                <td class='grossPay'>{$gross_pay}</td>
                                <th>Total Deductions</th>
                                <td class='totalDeduction'>{$total_deduction}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <th colspan='2'>EPF Employer Contributions</th>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>EPF</td>
                                <td>{$formObj->getTBRow('', 'employer_epf', $row['employer_epf'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Sosco</td>
                                <td>{$formObj->getTBRow('', 'employer_socso', $row['employer_socso'], $exptd)}</td>
                            </tr>
                            <tr>
                                <th colspan='2'>NET PAY</th>
                                <th colspan='2' class='netTotalPayrollMgmt' align='right'>{$net_total}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {$formObj->getTARow('Notes', 'notes', $row['notes'], $exptd)}
            </div>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit1($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $expStf = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');

        $status = $fn->getReqParam('status');

        $StatusArray = array(
              "Paid"
             ,"Approved"
             ,"Generated"
             ,"Hold"
             ,"Cancelled"
        );

        $lastMonthRecord = $fn->getRecordCount('payroll_management', "payroll_month > '{$row['payroll_month']}' AND payroll_year = '{$row['payroll_year']}' AND employee_id = {$row['employee_id']}");

        if($lastMonthRecord > 0){
            $exptd = array('isEditable' => 0);
        }else{
            $exptd = array('isEditable' => 1);
        }

            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');

            /*if($age <= 50){
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = 'below' AND to_age = '50'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

            }elseif ($age >= 51 && $age <= 55) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '51' AND to_age = '55'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }elseif ($age >= 56 && $age <= 60) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '56' AND to_age = '60'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }elseif ($age >= 60 && $age <= 65) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '61' AND to_age = '65'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }elseif ($age > 65) {
                $SQLPercentageCPF = "
                SELECT by_employer
                FROM cpf_calculator
                WHERE from_age = '61' AND to_age = 'Above'
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);
            }*/
            $OT = $row['ot_hours'] * $row['overtime_pay_rate'];
            //$gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'];
            $gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['performance_entitlement'] + $row['referral_incentive'] + $row['incentive'] + $row['overtime_pay_normal'] + $row['overtime_pay_sunday'] + $row['overtime_pay_holiday'];

        $cpf = 0;
        if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
            $sprCondition = '';
            if($row['spr_year'] != '' && $row['citizen'] == 'PR'){
                $sprCondition = "AND spr_year = {$row['spr_year']}";
            } else {
                $sprCondition = "AND spr_year = '3'";
            }

            $SQLPercentageCPF = "
            SELECT by_employer
                  ,cap_amount_employer
            FROM cpf_calculator
            WHERE {$age} BETWEEN from_age AND to_age
              AND {$gross_pay} BETWEEN from_salary AND to_salary
              AND year = {$year}
              {$sprCondition}
            ";
            $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
            $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

            $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
            $cpf = round($cpf, 2);

            if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                $rowPercentageCPF['cap_amount_employer'] != 0){
                $cpf = $rowPercentageCPF['cap_amount_employer'];
            }
        }
            //$total_deduction = $cpf + $row['mbf'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'];
            //$total_deduction = $cpf + $row['mbf'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'] + $row['deduction_epf'];
            $total_deduction = $row['deduction_epf'] + $row['deduction_sosco'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['other_deduction'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'];
            $net_total = $gross_pay - $total_deduction;


        if($row['status'] == 'Paid'){
            $paidDateObjClass = "";
        }else{
            $paidDateObjClass = "displayNone passType";
        }

        $payment_method  = $fn->getReqParam('payment_method');

        $paymentArray = array(
              "cheque"
             ,"cash"
             ,"giro payment transfer"
        );


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='20%'>{$formObj->getTBRow('Employee Name', '', $row['employee_name'], $expNoEdit)}</td>
                                <td width='15%'>{$formObj->getDateRow('Generated Date', 'generated_date', $row['generated_date'])}</td>
                                <td width='15%'>{$formObj->getTBRow('Basic Pay', '', $row['basic_pay'], $expNoEdit)}</td>
                                <td width='15%'>{$formObj->getDDRowByArr('Status', 'status', $StatusArray, $row['status'], $exptd)}</td>
                                <td width='15%'>{$formObj->getDDRowByArr('Payment Method <br/>(Transfer / Cheque / Cash)', 'payment_method', $paymentArray, $row['payment_method'], $exptd)}</td>
                                <td width='20%' class='{$paidDateObjClass}'><div >{$formObj->getDateRow('Paid Date', 'paid_date', $row['paid_date'])}</div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class='linkPortalDataWrapper payDetails'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th colspan='2'>EARNINGS</th>
                                <th colspan='2'>DEDUCTIONS</th>
                            </tr>
                            <tr>
                                <td>Basic Pay</td>
                                <td class='basicPayRate'>{$row['basic_pay']}</td>
                                <td>EPF</td>
                                <td>{$formObj->getTBRow('', 'deduction_epf', $row['deduction_epf'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay Rate/ Hour</td>
                                <td class='otPayRate'>{$row['overtime_pay_rate']}</td>
                                <td>Sosco</td>
                                <td>{$formObj->getTBRow('', 'sosco', $row['sosco'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>OT Hours</td>
                                <td>{$formObj->getTBRow('', 'ot_hours', $row['ot_hours'], $exptd)}</td>
                                <td>Income Tax</td>
                                <td>{$formObj->getTBRow('', 'income_tax_amount', $row['income_tax_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Amount</td>
                                <td class='ot_amount'>{$formObj->getTBRow('', 'ot_amount', $row['ot_amount'], $exptd)}</td>
                                <td>Advance Payment</td>
                                <td>{$formObj->getTBRow('', 'loan_amount', $row['loan_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Commission</td>
                                <td>{$formObj->getTBRow('', 'commission', $row['commission'], $exptd)}</td>
                                <td>Accommodation</td>
                                <td>{$formObj->getTBRow('', 'accommodation', $row['accommodation'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowance (Attendance)</td>
                                <td>{$formObj->getTBRow('', 'allowance1', $row['allowance1'], $exptd)}</td>
                                <td>Absence</td>
                                <td>{$formObj->getTBRow('', 'absence', $row['absence'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowance (Hardship)</td>
                                <td>{$formObj->getTBRow('', 'allowance2', $row['allowance2'], $exptd)}</td>
                                <td>Other Deduction</td>
                                <td>{$formObj->getTBRow('', 'other_deduction', $row['other_deduction'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowance (Travel)</td>
                                <td>{$formObj->getTBRow('', 'allowance3', $row['allowance3'], $exptd)}</td>
                                <td>Deduction 1</td>
                                <td>{$formObj->getTBRow('', 'deduction1', $row['deduction1'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Performance Entitlement</td>
                                <td>{$formObj->getTBRow('', 'performance_entitlement', $row['performance_entitlement'], $exptd)}</td>
                                <td>Deduction 2</td>
                                <td>{$formObj->getTBRow('', 'deduction2', $row['deduction2'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Referral Incentive</td>
                                <td>{$formObj->getTBRow('', 'referral_incentive', $row['referral_incentive'], $exptd)}</td>
                                <td>Deduction 3</td>
                                <td>{$formObj->getTBRow('', 'deduction3', $row['deduction3'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Incentive</td>
                                <td>{$formObj->getTBRow('', 'incentive', $row['incentive'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Overtime Pay (Normal)</td>
                                <td>{$formObj->getTBRow('', 'overtime_pay_normal', $row['overtime_pay_normal'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Overtime Pay (Sunday)</td>
                                <td>{$formObj->getTBRow('', 'overtime_pay_sunday', $row['overtime_pay_sunday'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Overtime Pay (Holiday)</td>
                                <td>{$formObj->getTBRow('', 'overtime_pay_holiday', $row['overtime_pay_holiday'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Gross Pay</th>
                                <td class='grossPay'>{$gross_pay}</td>
                                <th>Total Deductions</th>
                                <td class='totalDeduction'>{$total_deduction}</td>
                            </tr>
                            <tr>
                                <th colspan='2'>NET PAY</th>
                                <th colspan='2' class='netTotalPayrollMgmt' align='right'>{$net_total}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {$formObj->getTARow('Notes', 'notes', $row['notes'], $exptd)}
            </div>
        </div>
        ";

        return $text;
    }


    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'payroll_management_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_payrollManagement', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv     = Zend_Registry::get('tv');
        $fn     = Zend_Registry::get('fn');

        $employee_id = $fn->getReqParam('employee_id');
        $status      = $fn->getReqParam('status');
        $year        = $fn->getReqParam('year');
        $month       = $fn->getReqParam('month');
        $employee_status    = $fn->getReqParam('employee_status');

        if($employee_status == ''){
            $employee_status = 'Current';
        }

        if ($month == '') {
            $month = date('m') - 1;
            if ($month <= 9 && $month > 0) {
                $month = 0 . $month;
            } else if ($month == 0) {
                $month = 12;
            } else {
                $month = $month;
            }
        }

        if ($year == '') {
            if ($month == 12) {
                $year  = $year - 1;
            } else {
                $year = date('Y');
            }
        }

        /*
        if($month == ''){
            $month = date('m')-1;
            $month = 0 . $month;
        }

        if($year == ''){
            if ($month == 00)
            $year = date('Y');
        }
        */

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE status = '{$employee_status}'
        ORDER BY employee_name
        ";

        $StatusArray = array(
              "Paid"
             ,"Approved"
             ,"Generated"
             ,"Hold"
             ,"Cancelled"
        );

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        $arr = array (
                ' '  => 'Select Month'
               ,'01' => 'January'
               ,'02' => 'February'
               ,'03' => 'March'
               ,'04' => 'April'
               ,'05' => 'May'
               ,'06' => 'June'
               ,'07' => 'July'
               ,'08' => 'August'
               ,'09' => 'September'
               ,'10' => 'October'
               ,'11' => 'November'
               ,'12' => 'December'
               );

        $sqlYear = "SELECT DISTINCT payroll_year FROM payroll_management";

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $text = "
        <td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($StatusArray, $tv['status'])}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        <td>
            <select name='year' class='yearFilter'>
                <option value=''>Select Year</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
            </select>
        </td>
        <td>
            <select name='month' class='ml10 mr10'>
                {$cpUtil->getDropDownFromArr($arr, $month)}
            </select>
        </td>
        <td>
            <select name='employee_status'>
                <option value=''>Employee Status</option>
                {$cpUtil->getDropDown1($status, $employee_status)}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintPayslipForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=printPayslipFormSubmit&showHTML=0";

        $exp = array(
            'hideFirstOption' => true
           ,'sqlType' => 'OneField'
        );

        $expmonth = array(
            'hideFirstOption' => true,
            'useKey' => true
        );

        $monthArray = array(
                         '01' => 'January'
                        ,'02' => 'February'
                        ,'03' => 'March'
                        ,'04' => 'April'
                        ,'05' => 'May'
                        ,'06' => 'June'
                        ,'07' => 'July'
                        ,'08' => 'August'
                        ,'09' => 'September'
                        ,'10' => 'October'
                        ,'11' => 'November'
                        ,'12' => 'December'
                      );

        $sqlYear = "SELECT DISTINCT payroll_year FROM payroll_management";
        
        $currentMonth = date('m') - 1;
        if ($currentMonth <= 9 && $currentMonth > 0) {
            $currentMonth = 0 . $currentMonth;
        } else if ($currentMonth == 0) {
            $currentMonth = 12;
        } else {
            $currentMonth = $currentMonth;
        }

        if ($currentMonth == 12) {
            $currentYear  = date('Y') - 1;
        } else {
            $currentYear  = date('Y');
        }

        $text = "
        <form id='portalFormPrintPayslip' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDropDownRowBySQL('Year', 'payroll_year', $sqlYear, $currentYear, $exp)}
                {$formObj->getDropDownRowByArray('Month', 'payroll_Month', $monthArray, $currentMonth, $expmonth)}
            </table>
        </form>
        ";

        return $text;
    }

    
    /**
     *
     */
    function getPrintPaySlipForAllPdf1() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $payroll_year  = $fn->getReqParam('payroll_year');
        $payroll_month = $fn->getReqParam('payroll_month');

        $SQL = "
        SELECT pm.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.position AS designation
              ,e.salary
              ,e.fin_no
              ,e.nric_no
              ,e.date_of_birth  AS dob
              ,e.employee_id
              ,e.citizen
              ,e.spr_year
              ,cpf.by_employer
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        WHERE pm.payroll_month = '{$payroll_month}'
        AND pm.payroll_year = '{$payroll_year}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);

        $count = 1;

        While ($Row = $db->sql_fetchrow($result)){
            //============================================================================= //

            $pdf->SetFont('Courier','B',10);

            $today = date("d-m-Y");
            //$payroll_month = $fn->getCPDate($Row['payroll_month'], 'M');
            if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
                $finNo ='
                <td width="21%" style="font-weight:normal;" align="right">Nric No :</td>
                <td width="48%"> '.$Row['nric_no'].'</td>
                ';
            }else {
                $finNo ='
                <td width="21%" style="font-weight:normal;" align="right">Fin No :</td>
                <td width="48%"> '.$Row['fin_no'].'</td>
                ';
            }

            $prefix_month = $dateUtil->getShortMonthName($Row['payroll_month']);

            $tbl1 = '<table border="0" width="100%" style="font-size:14px;">
                    <tr>
                        <td align="center" style="font-weight:bold; font-size:20px;">SALARY SLIP - '.$prefix_month.' '.$Row['payroll_year'].'<br/></td>
                    </tr>';

            $generated_date = $fn->getCPDate($Row['generated_date'], 'd-m-Y');
            $tbl1 = $tbl1.'
                    <tr>
                        <td width="22%" style="font-weight:normal;">Employee Name :</td>
                        <td width="78%">'.$Row['employee_name'].'</td>
                    </tr>
                    <tr>
                        '.$finNo.'
                        <td width="15%" style="font-weight:normal;" align="right">Date:</td>
                        <td width="16%" align="right"> '.$generated_date.'</td>
                    </tr>';

            $tbl1 = $tbl1.'</table>';

            if($count == 1){
                //$pdf->ln(-6);
            }else{
                $pdf->AddPage();
            }
            $pdf->ln(-5);
            $pdf->writeHTML($tbl1, true, false, false, false, '');

            $dob = $fn->getCPDate($Row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');

            $OT  = $Row['ot_hours'] * $Row['overtime_pay_rate'];
            $gross_pay = $Row['basic_pay'] + $Row['ot_amount'] + $Row['commission'] + $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'];

            $cpf = 0;
            $cpfE = 0;
            if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
                $sprCondition = '';
                if($Row['spr_year'] != '' && $Row['citizen'] == 'PR'){
                    $sprCondition = "AND spr_year = {$Row['spr_year']}";
                } else {
                    $sprCondition = "AND spr_year = 3";
                }

                $SQLPercentageCPF = "
                SELECT by_employer
                      ,by_employee
                      ,cap_amount_employer
                      ,cap_amount_employee
                FROM cpf_calculator
                WHERE {$age} BETWEEN from_age AND to_age
                  AND {$gross_pay} BETWEEN from_salary AND to_salary
                  AND year = {$year}
                  {$sprCondition}
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

                $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                $cpf = round($cpf, 2);

                if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                    $rowPercentageCPF['cap_amount_employer'] != 0){
                    $cpf = $rowPercentageCPF['cap_amount_employer'];
                }

                $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                $cpfE = round($cpfE, 2);

                if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                    $rowPercentageCPF['cap_amount_employee'] != 0){
                    $cpfE = $rowPercentageCPF['cap_amount_employee'];
                }
            }

            $total_deduction = $Row['deduction_epf'] + $Row['deduction_sosco'] + $Row['mbf'] + $Row['loan_amount'] + $Row['income_tax_amount'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
            $net_total = $gross_pay - $total_deduction;
            $net_total = number_format($net_total, 2);

            $tbl2 = '
            <table border="1" width="100%" style="border: 2px solid red; font-size:14px;">
                <tr style="background-color: #b6e5f9;">
                    <th colspan="2" height="25px">EARNINGS</th>
                    <th colspan="2" height="25px">DEDUCTIONS</th>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%" height="20px">Basic Pay</td>
                    <td  width="15%" height="20px" align="right">'.$Row['basic_pay'].'</td>
                    <td width="35%" height="20px">CPF-Employee</td>
                    <td width="15%" height="20px" align="right">'.number_format($cpfE, 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Overtime Pay Rate/ Hour</td>
                     <td width="15%" align="right">'.$Row['overtime_pay_rate'].'</td>
                    <td width="35%">MBF</td>
                     <td width="15%" align="right">'.number_format($Row['mbf'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">OT Hours</td>
                    <td width="15%" align="right">'.$Row['ot_hours'].'</td>
                    <td width="35%">Advance / Loan</td>
                    <td width="15%" align="right">'.number_format($Row['loan_amount'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Overtime Amount</td>
                    <td width="15%" align="right">'.$OT.'</td>
                    <td width="35%">Income Tax</td>
                    <td width="15%" align="right">'.number_format($Row['income_tax_amount'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Commission</td>
                    <td width="15%" align="right">'.$Row['commission'].'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowances 1</td>
                    <td width="15%" align="right">'.$Row['allowance1'].'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowances 2</td>
                    <td width="15%" align="right">'.$Row['allowance2'].'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowances 3</td>
                    <td width="15%" align="right">'.$Row['allowance3'].'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%" align="right"></td>
                    <td width="35%">Deduction 1</td>
                    <td width="15%" align="right">'.number_format($Row['deduction1'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%" align="right"></td>
                    <td width="35%">Deduction 2</td>
                    <td width="15%" align="right">'.number_format($Row['deduction2'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%" align="right"></td>
                    <td width="35%">Deduction 3</td>
                    <td width="15%" align="right">'.number_format($Row['deduction3'], 2).'</td>
                </tr> 
                <tr>
                    <th width="35%" height="25px" style="background-color: #b6e5f9;">(A)Gross Pay</th>
                    <td width="15%" height="25px" align="right">'.number_format($gross_pay,2).'</td>
                    <th width="35%" height="25px" style="background-color: #b6e5f9;">(B)Total Deductions</th>
                    <td width="15%" height="25px" align="right">'.number_format($total_deduction,2).'</td>
                </tr>
                <tr style="background-color: #b6e5f9;">
                    <th colspan="3"  height="25px" align="right">NET PAY (A-B)</th>
                    <td align="right">'.$net_total.'</td>
                </tr>
            </table>
            ';

            $pdf->writeHTML($tbl2, true, false, false, false, '');
            $count ++;
        }
        $pdf->Output();
    }

    /**
     *
     */
    function getPrintPaySlipForAllPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $payroll_year  = $fn->getReqParam('payroll_year');
        $payroll_month = $fn->getReqParam('payroll_month');

        $SQL = "
        SELECT pm.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.position AS designation
              ,e.salary
              ,e.fin_no
              ,e.nric_no
              ,e.passport
              ,e.date_of_birth  AS dob
              ,e.employee_id
              ,e.citizen
              ,e.spr_year
              ,cpf.by_employer
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        WHERE pm.payroll_month = '{$payroll_month}'
        AND pm.payroll_year = '{$payroll_year}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);

        $count = 1;

        While ($Row = $db->sql_fetchrow($result)){
            //============================================================================= //

            $pdf->SetFont('Courier','B',10);

            $today = date("d-m-Y");
            //$payroll_month = $fn->getCPDate($Row['payroll_month'], 'M');
            if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
                $nricpassno ='
                <td width="21%" style="font-weight:normal;" align="right">Nric No  :</td>
                <td width="48%"> '.$Row['nric_no'].'</td>
                ';
            }else {
                $nricpassno ='
                <td width="21%" style="font-weight:normal;" align="right">Passport No  :</td>
                <td width="38%"> '.$Row['passport'].'</td>
                ';
            }

            $prefix_month = $dateUtil->getShortMonthName($Row['payroll_month']);

            $tbl1 = '<table border="0" width="100%" style="font-size:14px;">
                    <tr>
                        <td align="center" style="font-weight:bold; font-size:20px;">SALARY SLIP - '.$prefix_month.' '.$Row['payroll_year'].'<br/></td>
                    </tr>';

            $generated_date = $fn->getCPDate($Row['generated_date'], 'd-m-Y');
            $tbl1 = '
            <table border="0" width="100%" style="font-size:14px;">
                <tr>
                    <td align="center" height="25px" style="font-weight:bold; font-size:20px;"><u>SALARY SLIP - '.$prefix_month.' '.$Row['payroll_year'].'</u></td>
                </tr>
                <tr>
                    <td width="21%" style="font-weight:normal;">Employee Name :</td>
                    <td width="48%">'.$Row['employee_name'].'</td>
                    <td width="15%" style="font-weight:normal;" align="right">Date:</td>
                    <td width="16%" align="right"> '.$generated_date.'</td>
                </tr>
                <tr>
                    '.$nricpassno.'
                    <td width="25%" style="font-weight:normal;" align="right">Payment Method:</td>
                    <td width="16%" align="right"> '.$Row['payment_method'].'</td>
                </tr>
            </table>
            ';

            if($count == 1){
                //$pdf->ln(-6);
            }else{
                $pdf->AddPage();
            }
            $pdf->ln(-5);
            $pdf->writeHTML($tbl1, true, false, false, false, '');

            $dob = $fn->getCPDate($Row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');

            $overtime_pay_normal = $Row['overtimepaynormal'] * $Row['ot_hours'];
            $overtime_pay_sunday = $Row['overtimepaysunday'] * $Row['ot_hours_sunday'];
            $overtime_pay_holiday = $Row['overtimepayholiday'] * $Row['ot_hours_holiday'];
            $ot_amount = $overtime_pay_normal + $overtime_pay_sunday + $overtime_pay_holiday;
            $gross_pay = $Row['basic_pay'] + $ot_amount + $Row['commission'] + $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'] + $Row['performance_entitlement'] + $Row['referral_incentive'] + $Row['incentive'];

            $cpf = 0;
            $cpfE = 0;
            if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
                $sprCondition = '';
                if($Row['spr_year'] != '' && $Row['citizen'] == 'PR'){
                    $sprCondition = "AND spr_year = {$Row['spr_year']}";
                } else {
                    $sprCondition = "AND spr_year = 3";
                }

                $SQLPercentageCPF = "
                SELECT by_employer
                      ,by_employee
                      ,cap_amount_employer
                      ,cap_amount_employee
                FROM cpf_calculator
                WHERE {$age} BETWEEN from_age AND to_age
                  AND {$gross_pay} BETWEEN from_salary AND to_salary
                  AND year = {$year}
                  {$sprCondition}
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

                $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                $cpf = round($cpf, 2);

                if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                    $rowPercentageCPF['cap_amount_employer'] != 0){
                    $cpf = $rowPercentageCPF['cap_amount_employer'];
                }

                $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                $cpfE = round($cpfE, 2);

                if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                    $rowPercentageCPF['cap_amount_employee'] != 0){
                    $cpfE = $rowPercentageCPF['cap_amount_employee'];
                }
            }

            $total_deduction = $Row['deduction_epf'] + $Row['deduction_sosco'] + $Row['loan_amount'] + $Row['income_tax_amount'] + $Row['other_deduction'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
            $net_total = $gross_pay - $total_deduction;
            $net_total_in_words = $fn->getConvertNumber($net_total .'.00');
            $net_total = number_format($net_total, 2);

            $tbl2 = '
            <table border="1" width="100%" style="border: 2px solid red; font-size:14px;">
                <tr style="background-color: #b6e5f9;">
                    <th colspan="2">EARNINGS</th>
                    <th colspan="2">DEDUCTIONS</th>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%" height="20px">Basic Pay</td>
                    <td  width="15%" height="20px" align="right">'.$Row['basic_pay'].'</td>
                    <td width="35%" height="20px">EPF</td>
                    <td width="15%" height="20px" align="right">'.number_format($Row['deduction_epf'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Overtime Pay (Normal)</td>
                    <td width="15%" align="right">'.number_format($overtime_pay_normal, 2).'</td>
                    <td width="35%">Sosco</td>
                    <td width="15%" align="right">'.$Row['deduction_sosco'].'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Overtime Pay (Sunday)</td>
                    <td width="15%" align="right">'.number_format($overtime_pay_sunday, 2).'</td>
                    <td width="35%">Income Tax</td>
                    <td width="15%" align="right">'.number_format($Row['income_tax_amount'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Overtime Pay (Holiday)</td>
                    <td width="15%" align="right">'.number_format($overtime_pay_holiday, 2).'</td>
                    <td width="35%">Advance Payment</td>
                    <td width="15%" align="right">'.number_format($Row['loan_amount'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Commission</td>
                    <td width="15%" align="right">'.$Row['commission'].'</td>
                    <td width="35%">Accommodation</td>
                    <td width="15%" align="right">'.$Row['accommodation'].'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowance (Attendance)</td>
                    <td width="15%" align="right">'.$Row['allowance1'].'</td>
                    <td width="35%">Absence</td>
                    <td width="15%" align="right">'.$Row['absence'].'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowance (Hardship)</td>
                    <td width="15%" align="right">'.$Row['allowance2'].'</td>
                    <td width="35%">Other Deduction</td>
                    <td width="15%" align="right">'.number_format($Row['other_deduction'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowance (Travel)</td>
                    <td width="15%" align="right">'.$Row['allowance3'].'</td>
                    <td width="35%"></td>
                    <td width="15%"></td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Performance Entitlement</td>
                    <td width="15%" align="right">'.number_format($Row['performance_entitlement'], 2).'</td>
                    <td width="35%"></td>
                    <td width="15%"></td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Referral Incentive</td>
                    <td width="15%" align="right">'.number_format($Row['referral_incentive'], 2).'</td>
                    <td width="35%"></td>
                    <td width="15%"></td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Incentive</td>
                    <td width="15%" align="right">'.number_format($Row['incentive'], 2).'</td>
                    <td width="35%"></td>
                    <td width="15%"></td>
                </tr>
                <tr>
                    <th width="35%" style="background-color: #b6e5f9;">(A)Gross Pay</th>
                    <td width="15%" align="right">'.number_format($gross_pay,2).'</td>
                    <th width="35%" style="background-color: #b6e5f9;">(B)Total Deductions</th>
                    <td width="15%" align="right">'.number_format($total_deduction,2).'</td>
                </tr>
                <tr>
                    <td width="35%"></td>
                    <td width="15%"></td>
                    <th colspan="2" style="background-color: #b6e5f9;">EPF Employer Contributions</th>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%"></td>
                    <td width="35%" height="20px">EPF</td>
                    <td width="15%" height="20px" align="right">'.number_format($Row['employer_epf'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%"></td>
                    <td width="35%">Sosco</td>
                    <td width="15%" align="right">'.number_format($Row['employer_socso'], 2).'</td>
                </tr>

                <tr style="background-color: #b6e5f9;font-size:15px;">
                    <th align="right">NET PAY (A-B)</th>
                    <td colspan="3">'.$net_total.'<span style="font-size:11px;"><b>('.strtoupper($net_total_in_words).')</b></span></td>
                </tr>
            </table>
            ';

            $pdf->writeHTML($tbl2, true, false, false, false, '');
            $count ++;
        }
        $pdf->Output();
    }
}