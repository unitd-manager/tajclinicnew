<?
class CPL_Admin_Modules_Hms_Employee_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('jscolor-1.4.4', 'starrating-3.14');
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

            $today = date("Y-m-d");

            $SQLAttend = "
            SELECT *
            FROM attendance
            WHERE record_date = '{$today}'
            AND employee_id = {$row['employee_id']}";
            $resultAttend = $db->sql_query($SQLAttend);
            $rowAttend = $db->sql_fetchrow($resultAttend);

            $timeindisplayHide = "";
            if($rowAttend['time_in_day_shift'] != ""){
                $timeindisplayHide = "displayNone";
            }

            $timeoutdisplayHide = "";
            if($rowAttend['leave_time_day_shift'] != ""){
                $timeoutdisplayHide = "displayNone";
            }

           /* {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell("<a class='btn btn-success {$timeindisplayHide} TimeinButton' href='#' employee_id={$row['employee_id']}>Time In</a>")}
            {$listObj->getListDataCell("<a class='btn btn-success {$timeoutdisplayHide} TimeoutButton' href='#' employee_id={$row['employee_id']}>Time Out</a>")}
            {$listObj->getListHeaderCell('Staff Linked', 'staff_name')}
            {$listObj->getListHeaderCell('Time In', '')}
            {$listObj->getListHeaderCell('Time Out', '')}*/

            $company = "<a href='index.php?_topRm=project&module=hms_company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>";
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['salutation'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['specialist'])}
            {$listObj->getGoToDetailText($count, $row['first_name'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Salutation', 'a.salutation')}
        {$listObj->getListHeaderCell('Category', 'a.category')}
        {$listObj->getListHeaderCell('Specialist', 'a.specialist')}
        {$listObj->getListHeaderCell('Name', 'first_name')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Mobile', 'a.mobile')}
        
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }
    /**
     *
     */
    function getList1($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $company = "<a href='index.php?_topRm=project&module=hms_company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>";
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['salutation'])}
            {$listObj->getListDataCell($row['position'])}
            {$listObj->getGoToDetailText($count, $row['first_name'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Salutation', 'a.salutation')}
        {$listObj->getListHeaderCell('Position', 'a.position')}
        {$listObj->getListHeaderCell('Name', 'first_name')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Mobile', 'a.mobile')}
        {$listObj->getListHeaderCell('Category', 'a.category')}
        {$listObj->getListHeaderCell('Staff Linked', 'staff_name')}
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
        $expEdit = array('isEditable' => 0);

        $fielset = "
        {$formObj->getTBRow('Name *', 'first_name')}
        <!--{$formObj->getTBRow('Middle Name', 'middle_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}-->
        ";

        $text = "
        {$formObj->getTBRow('', "error_box1", '', $expEdit)}
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
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

        $chineseName    = '';
        $chinesePos     = '';
        $chineseDept    = '';
        $compAddressDD  = '';
        $companyAddress = '';
        $staffDetail    = '';
        $personalAdd    = '';
        $compLink       = '';
        $WorkingTimingDetails = '';

        $sqlCategory         = $fn->getValueListSQL('employeeCategory');
        $sqlTitle            = $fn->getValueListSQL('contactTitle');
        $sqlEmployeeWorkType = $fn->getValueListSQL('employeeWorkType');
        $sqlEmployeeType     = $fn->getValueListSQL('employeeType');
        $sqlPosition         = $fn->getValueListSQL('positionType','value');
        $sqlComp             = $fn->getDDSql('hms_company');
        $sqlSpecialist       = $fn->getValueListSQL('employeeSpecialist');
        $expEdit  = array('isEditable' => 0);

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        if ($tv['action'] == 'edit'){
            if($cpCfg['m.hms.hasMultipleCompanyAddress'] == 1){
                $sqlCombo = "
                SELECT company_address_id
                      ,CONCAT_WS(', ', address_flat, address_street, address_town, address_country) AS address
                FROM  company_address a
                WHERE company_id = '{$row['company_id']}'
                {$appendSql}
                ORDER BY company_address_id
                ";
                $compAddressDD = "
                {$formObj->getDDRowBySQL('Company Address', 'company_address_id', $sqlCombo, $row['company_address_id'])}
                ";
            }
        }

        $status = array(
              "Active"
             ,"Archive"
        );

        $discountTypeArr = array(
              "%"
             ,"Value"
        );

        $expVl = array('sqlType' => 'OneField');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['country_name']);

        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=hms_employee&_spAction=addNewValuelistForm&valuelist_name=positionType&employee_id={$row['employee_id']}&showHTML=0";
        $expPosition     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='positionType'>Add</a>");
        
        $formAddCategory = "index.php?_topRm={$tv['topRm']}&module=hms_employee&_spAction=addNewValuelistForm&valuelist_name=employeeCategory&employee_id={$row['employee_id']}&showHTML=0";
        $expCategory     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddCategory}' class='mr20 addNewValue' valuelist_name='employeeCategory'>Add</a>");

        /*
        $fielset1 = "
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTBRow('Name', 'employee_name', $row['employee_name'])}
        {$chineseName}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Passport *', 'passport', $row['passport'])}
        {$formObj->getTBRow('Nric No *', 'nric_no', $row['nric_no'])}
        {$formObj->getDDRowBySQL('Full Time & Part Time *', 'employee_work_type', $sqlEmployeeWorkType, $row['employee_work_type'], $expVl)}
        <div class='addHourlyRate'>{$formObj->getTBRow('Add Hourly Rate', 'add_hourly_rate', $row['add_hourly_rate'])}</div>
        <div class='salaryForFullTime'>{$formObj->getTBRow('Salary', 'salary', $row['salary'])}</div>
        {$formObj->getDDRowBySQL('Position', 'position', $sqlPosition, $row['position'], $expPosition)}
        <div class='type-text ym-fbox-text'>
            <label>Event Color</label>
            <input name='color' class='color {hash:true}' type='text' value='{$row['color']}'>
        </div>
        ";
        */

        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('hms_employee', 'hms_companyLink', 'fld_company_id')}'>Choose</a>";
        }

        $appendSqlStaff = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlStaff = "WHERE site_id = {$cpSiteIdSession}";
        }

        $expHideFirst = array('hideFirstOption' => 1);
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);
        $sqlStaff = "
        SELECT staff_id
              ,CONCAT_WS(' ', first_name, last_name ) AS staff_name
        FROM staff
        {$appendSqlStaff}
        ORDER BY staff_name
        ";

        $normalTimings = "";
        $doubleShiftTimings = "displayNone";
        if($row['employee_type'] == 'Double Shift') {
            $normalTimings = "displayNone";
            $doubleShiftTimings = "";
        }

        $WorkingTimingDetails .= "
        <tr class='{$normalTimings} normalTimings'>
            <th colspan='5'>Office Time Details</th>
        </tr>
        <tr class='{$normalTimings} normalTimings'>
            <th colspan='2'>Day Shift</th>
            <th colspan='3'>Night Shift</th>
        </tr>
        <tr class='{$normalTimings} normalTimings'>
            <td>{$formObj->getTimeRow('Time in (HH:MM)', 'time_in', $row['time_in'])}</td>
            <td>{$formObj->getTimeRow('Time out (HH:MM)', 'time_out', $row['time_out'])}</td>
            <td>{$formObj->getTimeRow('Time in (HH:MM)', 'time_in_night', $row['time_in_night'])}</td>
            <td>{$formObj->getTimeRow('Time out (HH:MM)', 'time_out_night', $row['time_out_night'])}</td>
        </tr>
        ";

        //if($row['employee_type'] == 'Double Shift') {
        $WorkingTimingDetails .= "
        <tr class='{$doubleShiftTimings} doubleShiftTimings'>
            <th colspan='5'>Office Time Details</th>
        </tr>
        <tr class='{$doubleShiftTimings} doubleShiftTimings'>
            <th colspan='2'>DS Morning</th>
            <th colspan='3'>DS Evening</th>
        </tr>
        <tr class='{$doubleShiftTimings} doubleShiftTimings'>
            <td>{$formObj->getTimeRow('Time in (HH:MM)', 'time_in_morning', $row['time_in_morning'])}</td>
            <td>{$formObj->getTimeRow('Time out (HH:MM)', 'time_out_morning', $row['time_out_morning'])}</td>
            <td>{$formObj->getTimeRow('Time in (HH:MM)', 'time_in_evening', $row['time_in_evening'])}</td>
            <td>{$formObj->getTimeRow('Time out (HH:MM)', 'time_out_evening', $row['time_out_evening'])}</td>
            <td></td>
        </tr>
        <tr class='{$doubleShiftTimings} doubleShiftTimings'>
            <th colspan='2'>Night Shift</th>
            <th colspan='3'></th>
        </tr>
        <tr class='{$doubleShiftTimings} doubleShiftTimings'>
            <td>{$formObj->getTimeRow('Time in (HH:MM)', 'time_in_night1', $row['time_in_night'])}</td>
            <td>{$formObj->getTimeRow('Time out (HH:MM)', 'time_out_night1', $row['time_out_night'])}</td>
            <td colspan='3'></td>
        </tr>
        ";
        //}

        $text = "
        {$formObj->getTBRow('', "error_box1", '', $expEdit)}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Employee Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Name *', 'first_name', $row['first_name'])}</td>
                                <td>{$formObj->getYesNoRRow('Visits Patients', 'visits_patients', $row['visits_patients'])}</td>
                                <td>{$formObj->getYesNoRRow('Attendance Required', 'add_in_payroll', $row['add_in_payroll'])}</td>
                                <td class='highlightedTdForNote'>{$formObj->getDDRowByArr('Status', 'status', $status, $row['status'], $expHideFirst)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Choose Category *', 'category', $sqlCategory, $row['category'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Choose Specialist', 'specialist', $sqlSpecialist, $row['specialist'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Staff', 'staff_id', $sqlStaff, $row['staff_id'])}</td>
                                <td>{$formObj->getTBRow('Consulting Fees', 'consultation_fees', $row['consultation_fees'])}</td>
                                <td>{$formObj->getDateRow('Date Of Joining', 'joining_date', $row['joining_date'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Full Time & Part Time', 'employee_work_type', $sqlEmployeeWorkType, $row['employee_work_type'], $expVl)}</td>
                                <td>{$formObj->getDDRowByArr('Fees Commission Type', 'fees_commission_type', $discountTypeArr, $row['fees_commission_type'])}</td>
                                <td>{$formObj->getTBRow('Fees Commission', 'fees_commission', $row['fees_commission'])}</td>
                                <td>{$formObj->getDDRowByArr('Lab Commission Type', 'lab_commission_type', $discountTypeArr, $row['lab_commission_type'])}</td>
                                <td>{$formObj->getTBRow('Lab Commission', 'lab_commission', $row['lab_commission'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Shift Type', 'employee_type', $sqlEmployeeType, $row['employee_type'], $expVl)}</td>
                            </tr>
                            {$WorkingTimingDetails}
                            <tr>
                                <th colspan='5'>More Details</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Flat / Building', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}</td>
                                <td>{$formObj->getTBRow('Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}</td>
                            </tr>
                            <tr>
                                <td><div class='salaryForFullTime'>{$formObj->getTBRow('Salary', 'salary', $row['salary'])}</div></td>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'])}</td>
                            </tr>
                            <tr>
                                <td class= 'creationModificationText' colspan = '5'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            ";
                            /*<tr>
                            <tr>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td><div class='type-text ym-fbox-text'>
                                        <label>Event Color</label>
                                        <input name='color' class='color {hash:true}' type='text' value='{$row['color']}'>
                                    </div>
                                </td>
                            </tr>
                                <th colspan='5'>Login Details</th>
                            </tr>
                            <tr>
                                <td colspan='3'>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td colspan='2'>{$formObj->getTBRow('Pass Word', 'pass_word', $row['pass_word'])}</td>
                            </tr>*/

        return $text;
    }

    /**
     *
     */
    function getSearch(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $textPublished  = "";

        $sqlCompany = $fn->getDDSql('hms_company');
        $sqlInterest = $fn->getDDSql('common_interest');

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
        {$formObj->getTBRow('Category', 'category')}
        ";

        $fielset3 = "
        {$formObj->getYesNoDropDownRow('Subscribed', 'subscribe')}
        {$formObj->getDDRowBySQL('Interst Group', 'interest_id', $sqlInterest)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Employee Details', $fielset1)}
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

        if( $cpCfg['m.hms.employee.showInterest'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("hms_employee", "common_interestLink", "Interests Linked", $row);
        }

        /*if( $cpCfg['m.hms.employee.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("hms_employee", "event_eventLink", "Events Linked", $row);
        }*/

        $record_id = $fn->getIssetParam($row, 'employee_id');

        $text = "
        <div id='employeePerformanceLinkPortal'>
            {$this->getEmployeePerformance($row['employee_id'])}
        </div>
        {$media->getRightPanelMediaDisplay("Picture", "hms_employee", "picture", $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'hms_employee'
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

        $interest_id    = $fn->getReqParam('interest_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $company_id     = $fn->getReqParam('company_id');
        //$position       = $fn->getReqParam('position');
        $status         = $fn->getReqParam('status');
        $category       = $fn->getReqParam('category');

        if ($tv['searchDone'] == 0){
            $status = 'Active';
        }

        //==================================================================//
        $companyText  = "";
        $categoryText = "";
        $interestText = "";

        $sqlCompany     = $fn->getDDSql('hms_company');
        $SQLStatus      = $fn->getValueListSQL('companyStatus');
        $sqlCategory    = $fn->getValueListSQL('employeeCategory');
        $sqlInterest    = $fn->getDDSql('common_interest');
        $sqlPosition    = $fn->getValueListSQL('positionType','value');

        $companyText = "
        <td>
            <select name='company_id' >
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        ";

        /*$categoryText = "
        <td>
            <select name='position'>
                <option value=''>Position</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPosition, $position)}
            </select>
        </td>
        ";*/

        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $statusArray = array(
              "Active"
             ,"Archive"
        );

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($statusArray, $tv['status'])}
            </select>
        </td>
        {$interestText}
        <td>
            <select name='category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory, $category)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $employee_id    = $fn->getReqParam('employee_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=hms_employee&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='employee_id' value='{$employee_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getEmployeeTimeInUpdate() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $employee_id = $fn->getReqParam('employee_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $today = date("Y-m-d");
        $employeeRec = $fn->getRecordRowById('employee', 'employee_id', $employee_id);

        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE record_date = '{$today}'
          AND employee_id = {$employeeRec['employee_id']}
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);

        if ($numRowsStaffAtt == 0) {

            $fa = array();
            date_default_timezone_set("Asia/Calcutta");
            $fa['time_in_day_shift'] = date('H:i:s');
            $fa['creation_date']     = date('Y-m-d H:i:s');
            $fa['employee_id']       = $employeeRec['employee_id'];
            $fa['record_date']       = $today;
            $fa['created_by']        = $employeeRec['first_name'] . ' ' . $employeeRec['last_name'];
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id'] = $cpSiteIdSession;
            }

            $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, 'attendance');
            $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);

        }

    }
    /**
     *
     */
    function getEmployeeTimeOutUpdate() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $employee_id = $fn->getReqParam('employee_id');

        $today = date("Y-m-d");

            
        $SQLStaffAtt = "
        SELECT *
        FROM attendance
        WHERE record_date = '{$today}'
          AND employee_id = '{$employee_id}'
        ";
        $resultStaffAtt  = $db->sql_query($SQLStaffAtt);
        $numRowsStaffAtt = $db->sql_numrows($resultStaffAtt);
        
        if ($numRowsStaffAtt > 0) {
            $fa = array();
            
            $employeeRec = $fn->getRecordRowById('employee', 'employee_id', $employee_id);
            date_default_timezone_set("Asia/Calcutta");
            $fa['leave_time_day_shift'] = date('H:i:s');
            $fa['modification_date']    = date('Y-m-d H:i:s');
            $fa['modified_by']          = $employeeRec['first_name'] . ' ' . $employeeRec['last_name'];

            $whereCondition = "WHERE record_date = '{$today}' AND employee_id = '{$employee_id}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'attendance', $whereCondition);
            $db->sql_query($SQL);
        }

    }

    /**
     *
     */
    function getEmployeePerformance($employee_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($employee_id == ''){
            $employee_id = $fn->getReqParam('employee_id');
        }

        $EmployeePerformance = $this->getEmployeePerformanceDetails($employee_id);

        $recCount = $fn->getRecordCount('emp_performance', "employee_id = '{$employee_id}'");

        $SQLEP ="
        SELECT ep.type
              ,AVG(ep.rating) AS rating
              ,ep.emp_performance_id
        FROM emp_performance ep
        WHERE employee_id = '{$employee_id}'
        GROUP BY ep.type
        ORDER BY ep.type ASC
        ";
        $resultEP   = $db->sql_query($SQLEP);
        $numRowsEP = $db->sql_numrows($resultEP);

        $optionArr = array(
             1 => 'Terrible'
            ,2 => 'Poor'
            ,3 => 'Average'
            ,4 => 'Very Good'
            ,5 => 'Excellent'
        );
        
        $expRating['hoverTipDefault'] = '&nbsp;';
        $expRating['optionArr']       = $optionArr;
        $ratingAvgRow = '';
        while ($rowEP = $db->sql_fetchrow($resultEP)) {
            $rowEP['rating'] = round($rowEP['rating']);

            $ratingField  = "rating{$rowEP['emp_performance_id']}";
            $ratingAvgRow .= "<div class='float_left'>{$rowEP['type']}(AVG)&nbsp;:</div><div class='float_left'>{$formObj->getStarRatingRow('', $ratingField, $rowEP['rating'], true, $expRating)}</div>";
        }

        $header ="
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Rating</th>
                <th>Notes</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $formActionEmployeePerformance = "index.php?module=hms_employee&_spAction=AddEmployeePerformance&employee_id={$employee_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddEmployeePerformance' href='{$formActionEmployeePerformance}' employee_id={$employee_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper hms_employee_employeePerformanceLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Employee Performance</div>
                    <div class='float_right'>
                        <span class='count' id='AddEmployeePerformancePortalCount'>({$fn->getRecordCount('emp_performance', "employee_id = '{$employee_id}'")})</span>
                        <div class='toggle'></div>
                    </div>
                    <div class='float_right'>{$ratingAvgRow}</div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='employeePerformancelist'>
                        {$header}
                        <tbody id='AddEmployeePerformancePortal'>
                            {$EmployeePerformance}
                        </tbody>
                    </table>
                    <input type='hidden' name='employee_id' value='{$employee_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;

    }

    /**
     *
     */
    function getEmployeePerformanceDetails($employee_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($employee_id == ''){
            $employee_id = $fn->getReqParam('employee_id');
        }

        $rows  = "";

        $SQL="
        SELECT ep.date
              ,ep.type
              ,ep.rating
              ,ep.notes
              ,ep.employee_id
              ,ep.created_by
              ,ep.modified_by
              ,ep.creation_date
              ,ep.modification_date
              ,ep.emp_performance_id
        FROM emp_performance ep
        WHERE employee_id = '{$employee_id}'
        ORDER BY ep.type, ep.date ASC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $optionArr = array(
             1 => 'Terrible'
            ,2 => 'Poor'
            ,3 => 'Average'
            ,4 => 'Very Good'
            ,5 => 'Excellent'
        );
        
        $expRating['hoverTipDefault'] = '&nbsp;';
        $expRating['optionArr']       = $optionArr;

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {
            $creation = $fn->getCPDate($row['date'], 'd-m-Y');

            $ratingField = "rating{$row['emp_performance_id']}";

            $formActionEditEmpPerform = "index.php?_topRm=utils&module=hms_employee&_spAction=EditEmployeePerformance&emp_performance_id={$row['emp_performance_id']}&showHTML=0";
            $EditEmployeePerformance  = "
            <a class='EditEmployeePerformance' href='{$formActionEditEmpPerform}' emp_performance_id='{$row['emp_performance_id']}' employee_id='{$row['employee_id']}'>
                <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
             </a>";

            $DeleteEmployeePerformance = "
            <a class='deleteEmployeePerformance' href='#'  emp_performance_id='{$row['emp_performance_id']}' employee_id='{$row['employee_id']}'>
                <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
            </a>
            ";

            $rows .= "
            <tr>
                <td>{$creation}</td>
                <td>{$row['type']}</td>
                <td>{$formObj->getStarRatingRow('', $ratingField, $row['rating'], true, $expRating)}</td>
                <td>{$row['notes']}</td>
                <td>{$EditEmployeePerformance}</td>
                <td>{$DeleteEmployeePerformance}</td>
            </tr>
            ";
            $count++;
        }

        $text = "{$rows}";

        return $text;
    }

    /**
     *
     */
    function getAddEmployeePerformance() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $employee_id = $fn->getReqParam('employee_id');

        $formAction = "index.php?_topRm=utils&module=hms_employee&_spAction=AddEmployeePerformanceSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
        $expVl      = array('sqlType' => 'OneField');

        $optionArr = array(
             1 => 'Terrible'
            ,2 => 'Poor'
            ,3 => 'Average'
            ,4 => 'Very Good'
            ,5 => 'Excellent'
        );
        
        $expRating['hoverTipDefault'] = 'Click to Rate';
        $expRating['optionArr']       = $optionArr;

        $sqlType     = $fn->getValueListSQL('employeePerformanceType', 'value');
        $currentDate = date("Y-m-d");
        
        $text = "
        <form id='AddEmployeePerformanceForm' class='AddEmployeePerformanceForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Type', 'type', $sqlType, '', $expVl)}
            {$formObj->getDateRow('Date', 'date', $currentDate)}
            {$formObj->getStarRatingRow('Rating', 'rating', '', false, $expRating)}
            {$formObj->getTARow('Notes', 'notes')}
            <input type='hidden' name='employee_id' value='{$employee_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditEmployeePerformance() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $emp_performance_id = $fn->getReqParam('emp_performance_id');

        $formAction = "index.php?_topRm=utils&module=hms_employee&_spAction=EditEmployeePerformanceSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
        $expVl      = array('sqlType' => 'OneField');

        $SQL ="
        SELECT ep.date
              ,ep.type
              ,ep.rating
              ,ep.notes
              ,ep.employee_id
              ,ep.created_by
              ,ep.modified_by
              ,ep.creation_date
              ,ep.modification_date
              ,ep.emp_performance_id
        FROM emp_performance ep
        WHERE emp_performance_id = '{$emp_performance_id}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $row     = $db->sql_fetchrow($result);

        $optionArr = array(
             1 => 'Terrible'
            ,2 => 'Poor'
            ,3 => 'Average'
            ,4 => 'Very Good'
            ,5 => 'Excellent'
        );
        
        $expRating['hoverTipDefault'] = 'Click to Rate';
        $expRating['optionArr']       = $optionArr;

        $sqlType     = $fn->getValueListSQL('employeePerformanceType', 'value');
        $currentDate = date("Y-m-d");
        
        $text = "
        <form id='EditEmployeePerformanceForm' class='EditEmployeePerformanceForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Type', 'type', $sqlType, $row['type'], $expVl)}
            {$formObj->getDateRow('Date', 'date', $row['date'])}
            {$formObj->getStarRatingRow('Rating', 'rating', $row['rating'], false, $expRating)}
            {$formObj->getTARow('Notes', 'notes', $row['notes'])}
            <input type='hidden' name='employee_id' value='{$row['employee_id']}' />
            <input type='hidden' name='emp_performance_id' value='{$row['emp_performance_id']}' />
        </form>
        ";

        return $text;
    }

}