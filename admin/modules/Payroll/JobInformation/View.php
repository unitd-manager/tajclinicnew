<?
class CPL_Admin_Modules_Payroll_JobInformation_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            if ($row['employee_work_type'] == 'Part time') {
                $amount = $row['day_rate'];
            } else {
                $amount = $row['salary'];
            }

            $emp_code = '';
            if($row['emp_code'] != ''){
                $emp_code = 'EMP-'.$row['emp_code'];
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $emp_code)}
            {$listObj->getGoToDetailText($count, $row['first_name'])}
            {$listObj->getListDataCell($row['department'])}
            {$listObj->getListDataCell($row['spass_no'])}
            {$listObj->getListDataCell($row['fin_no'])}
            {$listObj->getListDataCell($row['nric_no'])}
            {$listObj->getListDataCell($row['emp_type'])}
            {$listObj->getListDataCell($row['basic_pay'])}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++ ;
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

        $text = "
        {$message}
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('EMP Code', 'a.emp_code')}
        {$listObj->getListHeaderCell('Full Name', 'e.first_name')}
        {$listObj->getListHeaderCell('Department', 'j.department')}
        {$listObj->getListHeaderCell('S Pass No', 'e.spass_no')}
        {$listObj->getListHeaderCell('FIN No', 'e.fin_no')}
        {$listObj->getListHeaderCell('NRIC No', 'e.nric_no')}
        {$listObj->getListHeaderCell('Employment Type', 'j.emp_type')}
        {$listObj->getListHeaderCell('Basic Pay', 'j.basic_pay')}
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
        {$formObj->getTBRow('Employee Name', 'employee_name')}
        <input type='hidden' name='employee_id' value=''>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

        $sqldepartment  = $fn->getValueListSQL('department');
        $sqldesignation = $fn->getValueListSQL('designation');

        $expNoEdit  = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        /*
        $designation  = $fn->getReqParam('designation');
        $designation = array(
              "Programmer"
             ,"Developer"
        );
        */

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );
        $emp_type  = $fn->getReqParam('emp_type');

        $emp_typeArray = array(
              "Full Time"
             ,"Part Time"
             ,"Contract"
        );

        $payment_type  = $fn->getReqParam('payment_type');

        $paymenttypeArray = array(
              "Monthly"
             ,"Weekly"
             ,"Daily"
             ,"Hourly"
        );

        $working_days  = $fn->getReqParam('working_days');

        $workingdaysArray = array(
              "5"
             ,"5.5"
             ,"6"
        );

        $mode_of_payment  = $fn->getReqParam('mode_of_payment');

        $paymentArray = array(
              "cheque"
             ,"cash"
             ,"giro payment transfer"
        );

        $bank_name  = $fn->getReqParam('bank_name');

        $BankArray = array(
              "Australia & NewZealand Banking Group Ltd"
             ,"BNP paribas"
             ,"Bangkok Bank Public Company Ltd(SIN)"
             ,"Bank Of America(SIN)"
             ,"Bank Of China(SIN)"
             ,"Bank Of India(SIN)"
             ,"Bank Of Singapore Ltd(SIN)"
             ,"Bank Of Tokyo-Mitsubishi(SIN)"
             ,"CIMB Bank Berhad(SIN)"
             ,"Chung Khiaw Bank(SIN)"
             ,"CityBank(SIN)"
             ,"Credit Agricole Corporate & Investment Bank(SIN)"
             ,"DBS / POSB(SIN)"
             ,"Deutsche Bank AG(SIN)"
             ,"Far Eastern Bank(SIN)"
             ,"HL Bank(SIN)"
             ,"HSBC(SIN)"
             ,"ICIC Bank Ltd"
             ,"Indian Bank"
             ,"Indian Overseas Bank"
             ,"Industrial & Commercial Bank(SIN)"
             ,"Industrial & Commercial Bank Of China"
             ,"J.P.Morgan Chase Bank / Chase Manhattan Bank"
             ,"May Bank / Malayan Banking Berhad(SIN)"
             ,"Mizuho Corporate Bank Ltd"
             ,"National Australia Bank Ltd"
             ,"OCBC(SIN)"
             ,"P.T.Bank Negara Indonesia(Persero) Tbk(SIN)"
             ,"RHB Bank Berhad(SIN)"
             ,"southern Bank Berhad(SIN)"
             ,"Standard Chartered Bank(SIN)"
             ,"State Bank Of India"
             ,"Sumitomo Mitsui Banking Corporation"
             ,"The Bank Of East Asia(SIN)"
             ,"The Royal Bank Of Scotland N.V / ABN AMRO(SIN)"
             ,"UBS AG"
             ,"UCO Bank(SIN)"
             ,"United Overseas Bank Ltd(SIN)"
        );

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <div>Step 1 (Job Information)</div>
                    </div>
                    <div class='float_left'>
                        <div>Employee Name: {$row['employee_name']}</div>
                    </div>
                    <div class='float_left'>
                        <div>Nric No: {$row['nric_no']}</div>
                    </div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Designation', 'designation', $sqldesignation, $row['designation'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Department', 'department', $sqldepartment, $row['department'], $expVl)}</td>
                                <td>{$formObj->getYesNoRRow('Under Probation Period', 'probationary', $row['probationary'])}</td>
                                <td>{$formObj->getDDRowByArr('Employment Type', 'emp_type', $emp_typeArray, $row['emp_type'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDateRow('Joined Date (YYYY-MM-DD)', 'join_date', $row['join_date'])}</td>
                                <td>{$formObj->getDateRow('Actual Joined Date (YYYY-MM-DD)', 'act_join_date', $row['act_join_date'])}</td>
                                <td>{$formObj->getDateRow('Termination Date (YYYY-MM-DD)', 'termination_date', $row['termination_date'])}</td>
                                <td>{$formObj->getTARow('Reason for Termination', 'termination_reason', $row['termination_reason'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Status', 'status', $status, $row['status'])}</td>
                            </tr>

                            <tr>
                                <th colspan='4'>Salary Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Payment Frequency', 'payment_type', $paymenttypeArray, $row['payment_type'])}</td>
                                <td>{$formObj->getDDRowByArr('Working Calendar(No of Days/Week)', 'working_days', $workingdaysArray, $row['working_days'])}</td>
                                <td>{$formObj->getTBRow('Basic Pay', 'basic_pay', $row['basic_pay'])}</td>
                                <td>{$formObj->getTBRow('Overtime Pay (Normal)', 'overtime_pay_rate', $row['overtime_pay_rate'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Overtime Pay (Sunday)', 'overtime_pay_sunday', $row['overtime_pay_sunday'])}</td>
                                <td>{$formObj->getTBRow('Overtime Pay (Holiday)', 'overtime_pay_holiday', $row['overtime_pay_holiday'])}</td>
                                <td>{$formObj->getTBRow('Allowance (Attendance)', 'allowance1', $row['allowance1'])}</td>
                                <td>{$formObj->getTBRow('Allowance (Hardship)', 'allowance2', $row['allowance2'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Allowance (Travel)', 'allowance3', $row['allowance3'])}</td>
                                <td>{$formObj->getTBRow('Deduction 1', 'deduction1', $row['deduction1'])}</td>
                                <td>{$formObj->getTBRow('Deduction 2', 'deduction2', $row['deduction2'])}</td>
                                <td>{$formObj->getTBRow('Deduction 3', 'deduction3', $row['deduction3'])}</td>
                            </tr>
            
                            <tr>
                                <th colspan='4'>EPF Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getYesNoRRow('EPF Applicable', 'cpf_applicable', $row['cpf_applicable'])}</td>
                                <td>{$formObj->getTBRow('EPF No', 'cpf_account_no', $row['cpf_account_no'])}</td>
                                <td>{$formObj->getTBRow('Income Tax No', 'income_tax_id', $row['income_tax_id'])}</td>
                                <td>{$formObj->getTBRow('Income Tax Amount', 'income_tax_amount', $row['income_tax_amount'])}</td>
                            </tr>
                            <tr>
                                <th colspan='4'>Bank Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Mode Of Payment', 'mode_of_payment', $paymentArray, $row['mode_of_payment'])}</td>
                                <td>{$formObj->getTBRow('Account No', 'account_no', $row['account_no'])}</td>
                                <td>{$formObj->getDDRowByArr('Bank Name', 'bank_name', $BankArray, $row['bank_name'])}</td>
                                <td>{$formObj->getTBRow('Bank Code', 'bank_code', $row['bank_code'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Branch Code', 'branch_code', $row['branch_code'])}</td>
                            </tr>
                            <tr>
                                <th colspan='4'>Other Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Sosco', 'sosco', $row['sosco'])}</td>
                                <td>{$formObj->getTBRow('Accommodation', 'accommodation', $row['accommodation'])}</td>
                                <td>{$formObj->getTBRow('Other Deduction', 'other_deduction', $row['other_deduction'])}</td>
                                <td>{$formObj->getTBRow('Employer EPF', 'employer_epf', $row['employer_epf'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Employer Sosco', 'employer_socso', $row['employer_socso'])}</td>
                                <td>{$formObj->getTBRow('Income Tax File No', 'income_tax_file_no', $row['income_tax_file_no'])}</td>
                                <td>{$formObj->getTBRow('EPF Membership No', 'epf_membership_no', $row['epf_membership_no'])}</td>
                                <td>{$formObj->getTBRow('Sosco Membership No', 'sosco_membership_no', $row['sosco_membership_no'])}</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSearch(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $textPublished  = "";

        $sqlCompany = $fn->getDDSql('enggCrm_company');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Email', 'email' )}
        ";

        $fielset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany)}
        {$formObj->getTBRow('Position', 'position')}
        ";

        $fielset3 = "
        {$formObj->getYesNoDropDownRow('Subscribed', 'subscribe')}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Contact Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $rows = "";

        /*
        if( $cpCfg['m.project.jobInformation.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("payroll_jobInformation", "event_eventLink", "Events Linked", $row);
        }
        */

        $record_id = $fn->getIssetParam($row, 'employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_jobInformation', 'attachment', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'payroll_jobInformation'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $special_search  = $fn->getReqParam('special_search');
        $company_id      = $fn->getReqParam('company_id');
        $category        = $fn->getReqParam('category');
        $employee_id     = $fn->getReqParam('employee_id');
        $employee_status = $fn->getReqParam('employee_status');
 
        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

        //==================================================================//
        $companyText  = "";
        $categoryText = "";
        $interestText = "";

        $sqlCompany     = $fn->getDDSql('enggCrm_company');
        $SQLCategory    = $fn->getValueListSQL('contactCategory');

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.status = 'Current'
        ORDER BY employee_name
        ";

        /*$companyText = "
        <td>
            <select name='company_id' >
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        ";*/

        //==================================================================//
        $spArray = array(
              "Flagged"
             ,"Not-Flagged"
        );

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        if($employee_status == ''){
            $employee_status = 'Current';
        }

        $text = "
        <td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        {$categoryText}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
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
}