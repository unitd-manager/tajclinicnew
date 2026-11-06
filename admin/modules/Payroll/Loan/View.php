<?
class CPL_Admin_Modules_Payroll_Loan_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            $date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');
            $amount = number_format($row['amount'], 2);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['employee_name'])}
            {$listObj->getListDataCell($amount)}
            {$listObj->getListDataCell($date)}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['loan_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Employee Name', 's.employee_name')}
        {$listObj->getListHeaderCell('Amount', 'l.amount')}
        {$listObj->getListHeaderCell('Date', 'l.date')}
        {$listObj->getListHeaderCell('Status', 'l.status')}
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
              "Applied"
             ,"Waiting for Approval"
             ,"Denied"
             ,"Approved"
             ,"Hold"
        );

        $type = $fn->getReqParam('type');

        $loantypeArray = array(
              "Personal Loan"
             ,"Home Loan"
             ,"Car Loan"
             ,"Other"
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
                                <td>{$formObj->getDateRow('Date (YYYY-MM-DD)', 'date', $row['date'])}</td>
                                <td>{$formObj->getTBRow('Amount', 'amount', $row['amount'])}</td>
                                <td>{$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName, $row['employee_name'], $expNoEdit)}</td>
                                <td>{$formObj->getDDRowByArr('Type of Loan', 'type', $loantypeArray, $row['type'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Status', 'status', $StatusArray, $row['status'])}</td>
                                <td>{$formObj->getDateRow('Due date (YYYY-MM-DD)', 'due_date', $row['due_date'])}</td>
                                <td>{$formObj->getTBRow('No Of Months', 'no_of_months', $row['no_of_months'])}</td>
                                <td>{$formObj->getDateRow('Actual Loan closing date (YYYY-MM-DD)', 'loan_closing_date', $row['loan_closing_date'])}</td>
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
    */
        
    function getEditold($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

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
              "Applied"
             ,"Waiting for Approval"
             ,"Denied"
             ,"Approved"
             ,"Hold"
        );

        $type = $fn->getReqParam('type');

        $loantypeArray = array(
              "Personal Loan"
             ,"Home Loan"
             ,"Car Loan"
             ,"Other"
        );

        $fieldset1 = "
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName, $row['employee_name'], $expNoEdit)}
        {$formObj->getDDRowByArr('Type of Loan', 'type', $loantypeArray, $row['type'])}
        {$formObj->getDDRowByArr('Status', 'status', $StatusArray, $row['status'])}
        {$formObj->getDateRow('Due date', 'due_date', $row['due_date'])}
        {$formObj->getTARow('No Of Months', 'no_of_months', $row['no_of_months'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
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
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'loan_id');
        $loan_id  = $fn->getReqParam('loan_id');
        $employee_id = $fn->getReqParam('employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_loan', 'attachment', $row)}
        ";

        $sqlLoan = "
        SELECT l.*
        FROM `loan` l
        WHERE l.loan_id = {$row['loan_id']}
        AND employee_id = {$row['employee_id']}
        ";

        $resultLoan = $db->sql_query($sqlLoan);
        $rowLoan = $db->sql_fetchrow($resultLoan);

        $printText ="";
        if ($rowLoan['loan_id'] != '') {
            $printText .="
            <div id='renewalLinkPortal'>{$this->getAddLoan($row['loan_id'], $row['employee_id'])}</div>
            ";
        }
        $text=$text.$printText;

        return $text;
    }

    /**
     *
     */
    function getAddLoan($loan_id='',$employee_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($loan_id == ''){
            $loan_id = $fn->getReqParam('loan_id');
        }


        $Loan = $this->getAddLoanDetail($loan_id, $employee_id);

        $recCount = $fn->getRecordCount('loan', "employee_id = '{$employee_id}' AND loan_id < {$loan_id}");

        $header ="
        <thead>
            <tr>
            <th>Type of Loan</th>
            <th>From Date</th>
            <th>To Date</th>
            <th>No of Months</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $text = "
        <div class='linkPortalWrapper payroll_loan__payroll_leaveLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Loan Employee Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody>
                            {$Loan}
                        </tbody>
                    </table>
                    <input type='hidden' name='loan_id' value='{$loan_id}' />
                    <input type='hidden' name='employee_id' value='{$employee_id}' />
                </form> 
            </div>
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddLoanDetail($loan_id = '',$employee_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($loan_id == ''){
            $loan_id = $fn->getReqParam('loan_id');
        }

        if($employee_id == ''){
            $employee_id = $fn->getReqParam('employee_id');
        }

        $rows  = "";

        $SQL="
        SELECT * FROM `loan` 
        WHERE employee_id = {$employee_id}
        AND loan_id < {$loan_id};
        ";

        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
                <tr>
                    <td>{$row['type']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['due_date']}</td>
                    <td>{$row['no_of_months']}</td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $employee_id = $fn->getReqParam('employee_id');
        $status   = $fn->getReqParam('status');

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
        //$sqlStatus = $fn->getValueListSQL('companyStatus');
        $status = $fn->getReqParam('status');

        $statusArray = array(
              "Applied"
             ,"Waiting for Approval"
             ,"Denied"
             ,"Approved"
             ,"Hold"
        );

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($statusArray, $tv['special_search'])}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
}