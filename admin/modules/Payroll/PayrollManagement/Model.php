<?
class CPL_Admin_Modules_Payroll_PayrollManagement_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT pm.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.position AS designation
              ,e.salary
              ,e.date_of_birth AS dob
              ,e.spr_year
              ,e.citizen
              ,cpf.by_employer
              ,j.overtime_pay_rate AS overtimepaynormal
              ,j.overtime_pay_sunday AS overtimepaysunday
              ,j.overtime_pay_holiday AS overtimepayholiday
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = pm.employee_id)

        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $payroll_management_id = $fn->getReqParam('payroll_management_id');
        $status                = $fn->getReqParam('status');
        $employee_id           = $fn->getReqParam('employee_id');
        $year                  = $fn->getReqParam('year');
        $month                 = $fn->getReqParam('month');
        $employee_status       = $fn->getReqParam('employee_status');

        if ($payroll_management_id != "") {
            $searchVar->sqlSearchVar[] = "pm.payroll_management_id = '{$payroll_management_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pm.payroll_management_id = '{$tv['record_id']}'";
        } else {
           /* $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.payroll_management_id');

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }*/

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

            $searchVar->sqlSearchVar[] = "pm.payroll_year = '{$year}'";
            $searchVar->sqlSearchVar[] = "pm.payroll_month = '{$month}'";

            if ($employee_id != "") {
                $searchVar->sqlSearchVar[] = "pm.employee_id = '{$employee_id}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "pm.status = '{$status}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "pm.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(pm.flag != 1 OR pm.flag IS null)";
            }

            if ($employee_status != "") {
                $searchVar->sqlSearchVar[] = "e.status = '{$employee_status}'";
            }else{
                $searchVar->sqlSearchVar[] = "e.status = 'Current'";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please enter the employee name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave(){
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $db       = Zend_Registry::get('db');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'ot_hours');
        $fa = $fn->addToFieldsArray($fa, 'additional_wages');
        $fa = $fn->addToFieldsArray($fa, 'cpf_deduction');
        $fa = $fn->addToFieldsArray($fa, 'statutary_deduction');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'net_total');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'payroll_year');
        $fa = $fn->addToFieldsArray($fa, 'payroll_month');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'loan_amount');
        $fa = $fn->addToFieldsArray($fa, 'loan_description');
        $fa = $fn->addToFieldsArray($fa, 'commission');
        $fa = $fn->addToFieldsArray($fa, 'mbf');
        $fa = $fn->addToFieldsArray($fa, 'ot_amount');
        $fa = $fn->addToFieldsArray($fa, 'basic_pay');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_rate');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'cpf_account_no');
        $fa = $fn->addToFieldsArray($fa, 'pay_cdac');
        $fa = $fn->addToFieldsArray($fa, 'pay_sinda');
        $fa = $fn->addToFieldsArray($fa, 'pay_mbmf');
        $fa = $fn->addToFieldsArray($fa, 'pay_eucf');
        $fa = $fn->addToFieldsArray($fa, 'allowance1');
        $fa = $fn->addToFieldsArray($fa, 'allowance2');
        $fa = $fn->addToFieldsArray($fa, 'allowance3');
        $fa = $fn->addToFieldsArray($fa, 'deduction1');
        $fa = $fn->addToFieldsArray($fa, 'deduction2');
        $fa = $fn->addToFieldsArray($fa, 'deduction3');
        $fa = $fn->addToFieldsArray($fa, 'income_tax_amount');
        $fa = $fn->addToFieldsArray($fa, 'generated_date');
        $fa = $fn->addToFieldsArray($fa, 'paid_date');
        $fa = $fn->addToFieldsArray($fa, 'sosco');
        $fa = $fn->addToFieldsArray($fa, 'accommodation');
        $fa = $fn->addToFieldsArray($fa, 'other_deduction');
        $fa = $fn->addToFieldsArray($fa, 'performance_entitlement');
        $fa = $fn->addToFieldsArray($fa, 'referral_incentive');
        $fa = $fn->addToFieldsArray($fa, 'incentive');
        $fa = $fn->addToFieldsArray($fa, 'deduction_epf');
        $fa = $fn->addToFieldsArray($fa, 'deduction_sosco');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_normal');
        $fa = $fn->addToFieldsArray($fa, 'absence');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_sunday');
        $fa = $fn->addToFieldsArray($fa, 'payment_method');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_holiday');
        $fa = $fn->addToFieldsArray($fa, 'ot_hours_sunday');
        $fa = $fn->addToFieldsArray($fa, 'ot_hours_holiday');
        $fa = $fn->addToFieldsArray($fa, 'employer_epf');
        $fa = $fn->addToFieldsArray($fa, 'employer_socso');

        return $fa;
    }


    /**
     *
     */

    function getpayslipprintPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot2.php');

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

        $employee_id = $fn->getReqParam('employee_id');
        $payroll_management_id = $fn->getReqParam('payroll_management_id');

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
              ,j.overtime_pay_rate AS overtimepaynormal
              ,j.overtime_pay_sunday AS overtimepaysunday
              ,j.overtime_pay_holiday AS overtimepayholiday
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = pm.employee_id)
        WHERE pm.employee_id = '{$employee_id}'
        AND pm.payroll_management_id ='{$payroll_management_id}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row = $db->sql_fetchrow($result2);
        $company = $db->sql_fetchrow($result2);
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

        $generated_date = $fn->getCPDate($Row['generated_date'], 'd-m-Y');
        $prefix_month = $dateUtil->getLongMonthName($Row['payroll_month']);

        for($i=1;$i<3;$i++){

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

        $pdf->writeHTML($tbl1, true, false, false, false, '');


        $dob = $fn->getCPDate($Row['dob'], 'Y');
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
        $overtime_pay_normal = $Row['overtimepaynormal'] * $Row['ot_hours'];
        $overtime_pay_sunday = $Row['overtimepaysunday'] * $Row['ot_hours_sunday'];
        $overtime_pay_holiday = $Row['overtimepayholiday'] * $Row['ot_hours_holiday'];
        $ot_amount = $overtime_pay_normal + $overtime_pay_sunday + $overtime_pay_holiday;
        //$OT  = $Row['ot_hours'] * $Row['overtime_pay_rate'];
        //$gross_pay = $Row['basic_pay'] + $Row['ot_amount'] + $Row['commission'] + $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'];
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

        //$total_deduction = round($cpfE, 2) + $Row['mbf'] + $Row['loan_amount'] + $Row['income_tax_amount'] + $Row['pay_cdac'] + $Row['pay_sinda'] + $Row['pay_mbmf'] + $Row['pay_eucf'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
        $total_deduction = $Row['deduction_epf'] + $Row['deduction_sosco'] + $Row['loan_amount'] + $Row['income_tax_amount'] + $Row['other_deduction'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
        $net_total = $gross_pay - $total_deduction;
        $net_total_in_words = $fn->getConvertNumber($net_total .'.00');
        $net_total = number_format($net_total, 2);
        //$amount_in_words = $cpUtil->convertNumberToWords($net_total);


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
        $pdf->ln(-2);
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $tbl3 ='
        <table border="0" width="100%" style="font-size:14px;">
            <tr>
                <td height="35px" width="50%">Approved By Signature:</td>
                <td width="50%">Received By Signature:</td>
            </tr>
        </table>
            ';
        $pdf->writeHTML($tbl3, true, false, false, false, '');

    }

        //$pdf->ln(-12);
        //$pdf->writeHTML($tbl1, true, false, false, false, '');
        //$pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->Output();
    }
    /**
     *
     */

    function getpayslipprintPdf1() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot2.php');

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

        $employee_id = $fn->getReqParam('employee_id');
        $payroll_management_id = $fn->getReqParam('payroll_management_id');

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
              ,j.overtime_pay_rate AS overtimepaynormal
              ,j.overtime_pay_sunday AS overtimepaysunday
              ,j.overtime_pay_holiday AS overtimepayholiday
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = pm.employee_id)
        WHERE pm.employee_id = '{$employee_id}'
        AND pm.payroll_management_id ='{$payroll_management_id}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row = $db->sql_fetchrow($result2);
        $company = $db->sql_fetchrow($result2);
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

        $generated_date = $fn->getCPDate($Row['generated_date'], 'd-m-Y');
        $prefix_month = $dateUtil->getLongMonthName($Row['payroll_month']);

        $tbl1 = '
        <table border="0" width="100%" style="font-size:14px;">
            <tr>
                <td align="center" style="font-weight:bold; font-size:20px;">SALARY SLIP - '.$prefix_month.' '.$Row['payroll_year'].'<br/></td>
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

        $dob = $fn->getCPDate($Row['dob'], 'Y');
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
        $overtime_pay_normal = $Row['overtimepaynormal'] * $Row['ot_hours'];
        $overtime_pay_sunday = $Row['overtimepaysunday'] * $Row['ot_hours_sunday'];
        $overtime_pay_holiday = $Row['overtimepayholiday'] * $Row['ot_hours_holiday'];
        $ot_amount = $overtime_pay_normal + $overtime_pay_sunday + $overtime_pay_holiday;
        //$OT  = $Row['ot_hours'] * $Row['overtime_pay_rate'];
        //$gross_pay = $Row['basic_pay'] + $Row['ot_amount'] + $Row['commission'] + $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'];
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

        //$total_deduction = round($cpfE, 2) + $Row['mbf'] + $Row['loan_amount'] + $Row['income_tax_amount'] + $Row['pay_cdac'] + $Row['pay_sinda'] + $Row['pay_mbmf'] + $Row['pay_eucf'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
        $total_deduction = $Row['deduction_epf'] + $Row['loan_amount'] + $Row['income_tax_amount'] + $Row['other_deduction'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
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
                <td width="35%" height="20px">EPF</td>
                <td width="15%" height="20px" align="right">'.number_format($Row['deduction_epf'], 2).'</td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Overtime Pay Rate/ Hour(Normal)</td>
                 <td width="15%" align="right">'.$Row['overtimepaynormal'].'</td>
                <td width="35%">Sosco</td>
                <td width="15%" align="right">'.$Row['sosco'].'</td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">OT Hours(Normal)</td>
                <td width="15%" align="right">'.$Row['ot_hours'].'</td>
                <td width="35%">Income Tax</td>
                <td width="15%" align="right">'.number_format($Row['income_tax_amount'], 2).'</td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Overtime Pay (Normal)</td>
                <td width="15%" align="right">'.number_format($overtime_pay_normal, 2).'</td>
                <td width="35%">Advance Payment</td>
                <td width="15%" align="right">'.number_format($Row['loan_amount'], 2).'</td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Overtime Pay Rate/ Hour (Sunday)</td>
                <td width="15%" align="right">'.number_format($Row['overtimepaysunday'], 2).'</td>
                <td width="35%">Accommodation</td>
                <td width="15%" align="right">'.$Row['accommodation'].'</td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">OT Hours(Sunday)</td>
                <td width="15%" align="right">'.$Row['ot_hours_sunday'].'</td>
                <td width="35%">Absence</td>
                <td width="15%" align="right">'.$Row['absence'].'</td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Overtime Pay (Sunday)</td>
                <td width="15%" align="right">'.number_format($overtime_pay_sunday, 2).'</td>
                <td width="35%">Other Deduction</td>
                <td width="15%" align="right">'.number_format($Row['other_deduction'], 2).'</td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Overtime Pay Rate/ Hour (Holiday)</td>
                <td width="15%" align="right">'.number_format($Row['overtimepayholiday'], 2).'</td>
                <td width="35%"></td>
                <td width="15%"></td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">OT Hours(Holiday)</td>
                <td width="15%" align="right">'.$Row['ot_hours_holiday'].'</td>
                <td width="35%"></td>
                <td width="15%" align="right"></td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Overtime Pay (Holiday)</td>
                <td width="15%" align="right">'.number_format($overtime_pay_holiday, 2).'</td>
                <td width="35%"></td>
                <td width="15%"></td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Overtime Amount</td>
                <td width="15%" align="right">'.$ot_amount.'</td>
                <td width="35%"></td>
                <td width="15%"></td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Commission</td>
                <td width="15%" align="right">'.$Row['commission'].'</td>
                <td width="35%"></td>
                <td width="15%"></td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Allowance (Attendance)</td>
                <td width="15%" align="right">'.$Row['allowance1'].'</td>
                <td width="35%"></td>
                <td width="15%"></td>
            </tr>
            <tr style="font-weight:normal;">
                <td width="35%">Allowance (Hardship)</td>
                <td width="15%" align="right">'.$Row['allowance2'].'</td>
                <td width="35%"></td>
                <td width="15%"></td>
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

        $pdf->ln(-5);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->Output();
    }
    /**
     *
     */
     function getUpdateRecords() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $current_Month = $fn->getReqParam('current_Month');
        $current_Year  = $fn->getReqParam('current_Year');

        $sqlemployee = "
        SELECT j.*
        FROM job_information j
        WHERE j.status = 'Current'
        ";
        $resultemployee = $db->sql_query($sqlemployee);

        while ($rowemployee = $db->sql_fetchrow($resultemployee)) {

            $payMgmtRec = $fn->getRecordByCondition('payroll_management', "employee_id = '{$rowemployee['employee_id']}' AND payroll_month={$current_Month} AND payroll_year={$current_Year}");

            if($payMgmtRec['payroll_management_id'] == '') {
                $fa = array();
                $fa['employee_id']       = $rowemployee['employee_id'];
                $fa['payroll_month']     = $current_Month;
                $fa['payroll_year']      = $current_Year;
                $fa['creation_date']     = date("Y-m-d H:i:s");
                $fa['created_by']        = $fn->getSessionParam('userName');
                $fa['status']            = "Generated";
                $fa['generated_date']    = date("Y-m-d");
                $fa['basic_pay']         = $rowemployee['basic_pay'];
                $fa['overtime_pay_rate'] = $rowemployee['overtime_pay_rate'];
                $fa['department']        = $rowemployee['department'];
                $fa['sosco']             = $rowemployee['sosco'];
                $fa['accommodation']     = $rowemployee['accommodation'];
                $fa['other_deduction']   = $rowemployee['other_deduction'];
                $fa['cpf_account_no']    = $rowemployee['cpf_account_no'];
                $fa['allowance1']        = $rowemployee['allowance1'];
                $fa['allowance2']        = $rowemployee['allowance2'];
                $fa['allowance3']        = $rowemployee['allowance3'];
                $fa['deduction1']        = $rowemployee['deduction1'];
                $fa['deduction2']        = $rowemployee['deduction2'];
                $fa['deduction3']        = $rowemployee['deduction3'];

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'payroll_management');
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
     function getUpdateOverTimeAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hours = $fn->getReqParam('hours');
        $payRate = $fn->getReqParam('payRate');

        $ot_amount = $hours * $payRate;

        return $ot_amount;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getAccountCompanyPayrollContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.last_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }

    /**
     *
     */
    function getPrintPayslipFormValidate(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('payroll_year', 'Please Select Year');
        $validate->validateData('payroll_Month', 'Please Select Month');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintPayslipFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getPrintPayslipFormValidate()){
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }
}
