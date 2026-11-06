<?
class CPL_Admin_Modules_Hms_LabTest_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $count      = 0;
        $rows       = '';
        $searchDone = $fn->getReqParam('searchDone');
        $page = $tv['page'];
        $totalFees  = 0;
        foreach ($dataArray as $row){
            $email     = $row['email'];
            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

            $visit_code = '';
            if($row['visit_code'] != ''){
                $visit_code = 'LT-'.$row['visit_code'];
            }
        

            $age = '';

            if($row['age_year'] != ''){
                $age = $row['age_year'].' Yrs';
            } elseif($row['age_month'] != ''){
                $age = $row['age_month'].' Months';
            } elseif($row['age_day'] != ''){
                $age = $row['age_day'].' Days';
            }
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($visit_code)}
            {$listObj->getListDataCell($check_up_date)}
            {$listObj->getListDataCell($row['patient_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($age)}
            {$listObj->getListDataCell($row['address_area'])}
            {$listObj->getListRowEnd($row['patient_information_id'])}
            ";

            $count++ ;
        }

        //$newPatientLink = "index.php?_topRm=main&module=hms_labTest&_action=new";
        $search_List    = "index.php?_topRm=main&module=hms_labTest";
        $class = '';
        $displayNone = '';
        $cpSearch = '';
        if($searchDone != 1 && $page < 2){
            $class='defaultListDisplay';
        }else {
            $displayNone = 'displayNone';
            $cpSearch="
            <script>
                $('.cpSearch').css('display', 'block');
            </script>
            ";
        }
        
        $text = "
        <div class='searchListDisplay {$displayNone}'>{$this->getSearchList()}</div>
        <div class='{$class}'>
            <div class='floatbox goToSearchPatientVisit'>
                <div class='float_left'>
                    <a href='{$search_List}' class='btn btn-info'>Go To Search</a>
                </div>
            </div>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Visit Code', 'pv.visit_code')}
            {$listObj->getListHeaderCell('Visit Date', 'pv.check_up_date')}
            {$listObj->getListHeaderCell('Patient Name', 'patient_name')}
            {$listObj->getListHeaderCell('Status', 'p.status')}
            {$listObj->getListHeaderCell('Gender', 'p.gender')}
            {$listObj->getListHeaderCell('Age', 'p.age')}
            {$listObj->getListHeaderCell('Town/City', 'p.address_area')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        {$cpSearch}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset1 = "
        {$formObj->getDateRow('Check Up Date (YYYY-MM-DD)', 'check_up_date')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getSearchList(){
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        //$newPatientLink     = "index.php?_topRm=main&module=hms_labTest&_action=new";
        $lab_test_List = "index.php?_topRm=main&module=hms_labTest";
        $expHideFirstOpt    = array('hideFirstOption' => 1);
        $searchlistArr      = array('Search by Name'
                                   ,'Search by NRIC');
        $row    = '';
        $expGender   = array('sqlType' => 'OneField');
        $sqlGender   = $fn->getValueListSQL('gender');

        $formActionAddpatient = "index.php?module=hms_labTest&_spAction=addPatientRecord&showHTML=0";
        $searchResultRows = $this->getPatientVisitSearchResult();
        $searchResultAppointmentRows = $this->getPatientVisitAppointmentSearchResult();

        /*<td>{$formObj->getTBRow('Weight (in kgs)', 'weight', '')}</td>
        <td>{$formObj->getTBRow('Temperature-°F', 'temperature', '')}</td>*/

        /*
        <div class='float_right mb10'>
            <a href='{$formActionAddpatient}' class='button' id='addPatientRecord'>Quick Add Patient</a>
        </div>
        */
        $expGroupHeading = array('useKey' => false);
        $yesNoArr = array(1 => 'Male', 0 => 'Female');
        //{$formObj->getRRow('Gender', 'gender','', $yesNoArr, $expGroupHeading)}
        
                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        
           $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND site_id = {$cpSiteIdSession}";
        }

          $sqlEmployeeReferral = "
        SELECT employee_id
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";


        $text = "
        <div class='floatbox'>
            <div class='float_left displayVisitRecords'>
                <a href='#' class='btn btn-info'>Display Visit Records</a>
            </div>
        </div>
        <div class='searchPanelInPatientVisitLabel'>
            <div class='linkPortalWrapper'>
                <div expanded='1' class='header'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <label class='headerLabel'>
                                Please key in the words to search the patient records
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <div class='linkPortalDataWrapper'>
                        <table class='thinlist'>
                            <tbody>
                                <tr>
                                    <td colspan='2'>
                                        {$formObj->getTBRow('Patient Name', 'patient_name', '')}
                                        <input type='hidden' name='patient_information_id' value=''/>
                                    </td>
                                    <td>{$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, 'Male', $expGender)}
                                    </td>
                                    <td>
                                    <div class='ageBox'>{$formObj->getTBRow('Age (Years)', 'age_year', '')}</div>
                                    <div class='ageBox'>{$formObj->getTBRow('(Months)', 'age_month', '')}</div>
                                    <div class='ageBox'>{$formObj->getTBRow('(Days)', 'age_day', '')}</div>
                                    </td>
                                    <td>{$formObj->getTBRow('Father Name', 'father_name', '')}</td>
                                </tr>
                                <tr>
                                </tr>
                                <tr>
                                    <td>{$formObj->getTBRow('Husband Name', 'spuse_name', '')}</td>
                                    <td>{$formObj->getTBRow('Mobile', 'phone', '')}</td>
                                    <td>{$formObj->getTBRow('Town/City', 'address_area', '')}</td>
                                    <td>{$formObj->getDDRowBySQL('Referral Doctor', 'referral_doctor_id', $sqlEmployeeReferral, $row['referral_doctor_id'])}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class='floatbox'>
                                            <div class='float_left createPatientButtonPatientVisit'>
                                                <a class='btn btn-info createPatientVisitSearchButton'>Create Visit</a>
                                            </div>
                                            <div class='float_left clearSearchValues'>
                                                <a class='btn btn-danger clearSearchValuesButton'>Clear</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class='searchTableInPatientVisit searchTableInPatientVisithide'>
            {$searchResultRows}
        </div>
        <div class='searchTableInPatientVisitAppointment'>
            {$searchResultAppointmentRows}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPatientVisitSearchResult(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $inputBoxVaue    = $fn->getReqParam('inputBoxVaue');
        $lock            = $fn->getReqParam('lock');
        $currentDate     = date("Y-m-d");
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $text = '';
        $resultRow = '';

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND p.site_id = {$cpSiteIdSession}";
        }

        if($inputBoxVaue != ''){
            $SQL = "
            SELECT p.nric
                  ,p.patient_information_id
                  ,p.mobile
                  ,p.father_name
                  ,p.spuse_name
                  ,p.address_area
                  ,p.phone
                  ,p.email
                  ,p.dob
                  ,p.name AS patient_name
            FROM patient_information p
            WHERE (p.name LIKE '%{$inputBoxVaue}%'
               OR p.nric LIKE '%{$inputBoxVaue}%'
               OR CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) LIKE '%{$inputBoxVaue}%')
               OR CONCAT_WS('', p.first_name, p.middle_name) LIKE '%{$inputBoxVaue}%'
               OR CONCAT_WS('', p.middle_name, p.last_name) LIKE '%{$inputBoxVaue}%'
            ";

            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            while($rec    = $db->sql_fetchrow($result)){
                $appendSqlPV = '';
                $appendSqlAp = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
                    $appendSqlAp = "AND a.site_id  = {$cpSiteIdSession}";
                }

                $dob = $fn->getCPDate($rec['dob'], 'd-m-Y');

                $SQLPatientVisit = "
                SELECT pv.lab_test_id
                      ,pv.status
                FROM lab_test pv
                WHERE patient_information_id = {$rec['patient_information_id']}
                AND pv.check_up_date = '{$currentDate}'
                {$appendSqlPV}
                ";
                $resultPatientVisit  = $db->sql_query($SQLPatientVisit);
                $numRowsPatientVisit = $db->sql_numrows($resultPatientVisit);
                $rowPatientVisit     = $db->sql_fetchrow($resultPatientVisit);

                $SQLAppointment = "
                SELECT a.appointment_id
                      ,a.dr_Linked
                      ,a.check_up_time
                FROM appointment a
                WHERE patient_information_id = {$rec['patient_information_id']}
                AND a.check_up_date = '{$currentDate}'
                {$appendSqlAp}
                ";
                $resultAppointment   = $db->sql_query($SQLAppointment);
                $numRowsAppointment  = $db->sql_numrows($resultAppointment);
                $rowAppointment      = $db->sql_fetchrow($resultAppointment);

                $createVisit = "
                <div class='button btn btn-default visitCreateButton'>
                    <a class='createVisit' patient_information_id={$rec['patient_information_id']} dr_required='{$rowAppointment['dr_Linked']}' appointment_id='{$rowAppointment['appointment_id']}'>
                        Create Visit
                    </a>
                <div>
                ";

                if($numRowsPatientVisit > 0){
                    $patientVisitLink = "index.php?_topRm=main&module=hms_labTest&_action=edit&lab_test_id={$rowPatientVisit['lab_test_id']}";
                    $createVisit = "<a class = 'button btn btn-default viewVisitRecord' href='{$patientVisitLink}'>
                                        View Record
                                    </a>
                    ";
                }

                $age = '';
                if($rec['dob'] != ''){
                    $dob_for_age = $dateUtil->formatDate($rec['dob'], 'DD-MM-YYYY');
                    $modObj = getCPModuleObj('hms_patientInformation');
                    $age = $modObj->view->getFindage($dob_for_age, date('d-m-Y'));
                }

                $text .= "
                <tr>
                    <td>{$rec['patient_name']}</td>
                    <td class='txtCenter'>{$createVisit}</td>
                    <td>{$rec['father_name']}</td>
                    <td>{$rec['spuse_name']}</td>
                    <td>{$rec['address_area']}</td>
                    <td>{$rec['phone']}</td>
                </tr>
                ";
            }

            if($numRows > 0){
                $resultRow = "
                <div class='searchResultLabel'>
                    <label class=''>Please find the Search Results below : {$numRows} Record(s)</label>
                </div>
                <table class='thinlist'>
                    <thead>
                        <th>Patient Name</th>
                        <th class='txtCenter'>Visit</th>
                        <th>Father Name</th>
                        <th>Husband Name</th>
                        <th>Town/City</th>
                        <th>Mobile</th>
                    </thead>
                    <tbody>
                        {$text}
                    </tbody>
                </table>
                ";
            }else{
                $resultRow = "
                <div class='searchResultLabel'>
                    <label class=''>No Results found for '{$inputBoxVaue}'.</label>
                </div>
                ";
            }
        }

        return $resultRow;
    }

    /**
     *
     */
    function getPatientVisitAppointmentSearchResult(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $inputBoxVaue    = $fn->getReqParam('inputBoxVaue');
        $lock            = $fn->getReqParam('lock');
        $currentDate     = date("Y-m-d");
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $text = '';
        $resultRow = '';
        $age = '';

        $appendSql = '';
        $appendSqlCount = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            //$appendSql = "AND p.site_id = {$cpSiteIdSession}";
            $appendSqlCount = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $appendSqlPV = '';
        $appendSqlAp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
            $appendSqlAp = "AND a.site_id  = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT pv.check_up_date
              ,pv.check_up_time
              ,p.patient_information_id
              ,p.nric
              ,p.mobile
              ,p.email
              ,p.dob
              ,p.father_name
              ,p.spuse_name
              ,p.address_area
              ,p.phone
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.name AS patient_name
              ,pv.visit_code
              ,pv.status
              ,pv.lab_test_id
        FROM lab_test pv
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date = '{$currentDate}'
        AND pv.status != 'Cancelled'
        {$appendSqlPV}
        {$appendSql}
        ORDER BY pv.status DESC, pv.lab_test_id DESC
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $SQLCount = "
        SELECT count(pv.lab_test_id) as  case_count
        FROM lab_test pv
        WHERE pv.check_up_date = '{$currentDate}'
        AND status != 'Cancelled'
        {$appendSqlCount}
        ";

        $resultCount = $db->sql_query($SQLCount);
        $evRow       = $db->sql_fetchrow($resultCount);
        //$recCount = $fn->getRecordCount('lab_test', "check_up_date = '{$currentDate}' AND status != 'Cancelled'");
        while($rec    = $db->sql_fetchrow($result)){
            $patientVisitLink = "index.php?_topRm=main&module=hms_labTest&_action=edit&lab_test_id={$rec['lab_test_id']}";
            $createVisit = "<a class = 'button btn btn-default viewVisitRecord' href='{$patientVisitLink}'>
                                View Record
                            </a>
            ";

            $printToken   = "index.php?_topRm=main&module=hms_labTest&_spAction=printTokenForVisit&patient_information_id={$rec['patient_information_id']}&lab_test_id={$rec['lab_test_id']}&showHTML=0";

            $patient_Link = "index.php?_topRm=main&module=hms_patientInformation&_action=edit&patient_information_id={$rec['patient_information_id']}";
            $patient_name = "<a href='{$patient_Link}' target='_blank'><u>{$rec['patient_name']}</u></a>";

            $check_up_time = $rec['check_up_time'];

            $visit_code = '';
            if($rec['visit_code'] != ''){
                $visit_code = 'LT-'.$rec['visit_code'];
            }

            $bgColorBalance = '';
            if($rec['status'] == 'Closed'){
                $bgColorBalance = "bgcolor='#BCFDFD'";
            } elseif($rec['status'] == 'Cancelled'){
                $bgColorBalance = "bgcolor='#DF6C68'";
            }    

            if($rec['age_year'] != ''){
                $age = $rec['age_year'].' Yrs';
            } elseif($rec['age_month'] != ''){
                $age = $rec['age_month'].' Months';
            } elseif($rec['age_day'] != ''){
                $age = $rec['age_day'].' Days';
            }

            $patSumCount = $fn->getRecordCount('lab_test', "patient_information_id = '{$rec['patient_information_id']}' AND status != 'Cancelled'");
            $pastVisit = '';

            if($patSumCount > 1){
                $viewOverallSummaryLink = "index.php?_topRm=main&module=hms_labTest&_spAction=overallSummary&lab_test_id={$rec['lab_test_id']}&patient_information_id={$rec['patient_information_id']}&showHTML=0";

                $pastVisit="<a href='{$viewOverallSummaryLink}' class='viewOverallSummary'><u>Past Summary</u></a>";
            }
            $check_up_time = date('h:i:s a', strtotime($rec['check_up_time']));

            $text .= " 
            <tr>
                <td {$bgColorBalance}>{$visit_code}</td>
                <td {$bgColorBalance}>{$patient_name}</td>
                <td {$bgColorBalance} class='txtCenter'>{$createVisit}</td>
                <td class='txtCenter'>{$pastVisit}</td>
                <td class='txtCenter'>{$check_up_time}</td>
                <td>{$rec['gender']}</td>
                <td class='txtCenter'>{$age}</td>
                <td>
                    <a href='{$printToken}' target='_blank'>
                        <u>Print Token</u>
                    </a>
                </td>
                <td>{$rec['status']}</td>
                <td>{$rec['address_area']}</td>
                <td>{$rec['phone']}</td>
            </tr>
            ";
        }

        if($numRows > 0){
            $resultRow = "
            <div class='searchResultLabel'>
                <label class=''>Please find below the number of patients visited today : {$evRow['case_count']} Patient(s)</label>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>Visit Code</th>
                    <th>Patient Name</th>
                    <th class='txtCenter'>Visit</th>
                    <th class='txtCenter'>Past Summary</th>
                    <th class='txtCenter'>Check Up Time</th>
                    <th>Gender</th>
                    <th class='txtCenter'>Age</th>
                    <th>Print</th>
                    <th>Status</th>
                    <th>Town/City</th>
                    <th>Mobile</th>
                </thead>
                <tbody>
                    {$text}
                </tbody>
            </table>
            ";
        }else{
            $resultRow = "";
        }

        return $resultRow;
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

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        //$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['address_country']);

        $expVl           = array('sqlType' => 'OneField');
        $expBillType     = array('sqlType' => 'OneField', 'hideFirstOption' => 1);
        $sqlCategory     = $fn->getValueListSQL('patientVisitCategory');
        $sqlTitle        = $fn->getValueListSQL('patientVisitTitle');
        $sqlBillType     = $fn->getValueListSQL('billType');
        $expNoEdit       = array('isEditable' => 0);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $followUp = array(
                       '1 week'  => 'One week later'
                      ,'2 weeks'  => 'Two weeks later'
                      ,'3 weeks'  => 'Three weeks later'
                      ,'4 weeks'  => 'Four weeks later'
                      ,'5 weeks'  => 'Five weeks later'
                      ,'6 weeks'  => 'Six weeks later'
                      ,'2 months'  => 'Two months later'
                      ,'3 months'  => 'Three months later'
                      ,'6 months'  => 'Six months later'
                      );
        $expArr = array('useKey' => 1);

        $medicalTestLbl = "
        <li class='first'>
            <a href='#tabs-6' class='investigations'>Investigations</a>
        </li>
        ";

        $medicalTestTab = "
        <div id='tabs-6'>
            <div id='medicalDisplay'>{$this->getMedicalPortalDisplay($row['lab_test_id'])}</div>
        </div>
        ";

        $search_List = "index.php?_topRm=main&module=hms_labTest&_action=searchlist";

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQLOrder ="
        SELECT order_id
        FROM `order`
        WHERE lab_test_id = {$row['lab_test_id']}
        AND order_type = 'LT'
        {$appendSql}
        ";
        $resultOrder  = $db->sql_query($SQLOrder);
        $numRowsOrder = $db->sql_numrows($resultOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        $cancelVisit = '';
        $gotoOrder = '';
        $generateOrder = '';
        $invoicePortalDisplay = '';
        $actionButtons = '';
        $receiptPortalDisplay = '';
        if($numRowsOrder > 0){
            $OrderLink = "index.php?_topRm=finance&module=hms_order&_action=edit&order_id={$rowOrder['order_id']}";

            $SQLInvoice = "
            SELECT i.*
            FROM invoice i
            WHERE i.order_id = {$rowOrder['order_id']}
            AND i.status != 'Cancelled'
            ";
            $resultInvoice = $db->sql_query($SQLInvoice);
            $numRowsInvoice = $db->sql_numrows($resultInvoice);

            if($numRowsInvoice == 0){
                if($row['status'] != 'Cancelled'){
                    $generateOrder = "<a href='#' id='createOrderRecord' lab_test_id='{$row['lab_test_id']}' class='btn btn-info'>Generate Bill</a>";
                    $cancelVisit = "<a lab_test_id='{$row['lab_test_id']}' class='btn btn-danger cancelVisitRecord'>Cancel Visit</a>";
                }

            }else{
                $billSummaryOrder = "index.php?module=hms_labTest&_spAction=summaryInOrder&order_id={$rowOrder['order_id']}&showHTML=0";
                $generateOrder = "<div class='billSummaryOrder float_left'><a class='btn btn-primary' href='{$billSummaryOrder}' id='billSummaryOrder' order_id='{$rowOrder['order_id']}'>Bill Summary</a></div>";

            }

            $modObj = getCPModuleObj('hms_order');
            $rowOrder = $fn->getRecordRowByID('order', 'lab_test_id', $row['lab_test_id']);
            $invoicePortalDisplay =  $modObj->view->getInvoicePortalDisplay($rowOrder['order_id']);

            $formActionReceipt = "index.php?module=hms_order&_spAction=generateReceiptForm&order_id={$rowOrder['order_id']}&patient_information_id={$row['patient_information_id']}&lab_test_id={$row['lab_test_id']}&showHTML=0";

            $actionButtons ="
            <div class='btn btn-info mb5'>
                <a href='{$formActionReceipt}' id='generateReceipt'>CREATE RECEIPT</a>
            </div>
            ";
            $receiptPortalDisplay =  $modObj->view->getReceiptPortalDisplay($rowOrder['order_id']);
        }
        else{
            if($row['status'] != 'Cancelled'){
                $generateOrder = "<a href='#' id='createOrderRecord' lab_test_id='{$row['lab_test_id']}' class='btn btn-info'>Generate Bill</a>";
            }

            if($row['status'] != 'Cancelled'){
                $cancelVisit = "<a lab_test_id='{$row['lab_test_id']}' class='btn btn-danger cancelVisitRecord'>Cancel Visit</a>";
            }
        }
        
        $printPrescription = '';
        $labReport = '';
        $labReqReport = '';
        if($row['status'] != 'Cancelled'){
            $urllabReport = "index.php?module=hms_labTest&_spAction=printLabReport&lab_test_id={$row['lab_test_id']}&showHTML=0";
            $labReport = "<a href='{$urllabReport}' id='labReport' lab_test_id='{$row['lab_test_id']}' class='btn btn-info' target='_blank'>Lab Report</a>";

            $urllabReqReport = "index.php?module=hms_labTest&_spAction=printLabRequestForm&lab_test_id={$row['lab_test_id']}&showHTML=0";
            $labReqReport = "<a href='{$urllabReqReport}' id='labReport' lab_test_id='{$row['lab_test_id']}' class='btn btn-info' target='_blank'>Lab Request Form</a>";
        }

        $gotoSearch = "
        <div class='floatbox editTopButtonActionDiv'>
            <div class='float_left'>
                {$generateOrder}
                {$labReqReport}
                {$labReport}
                {$gotoOrder}
                {$cancelVisit}
            </div>
            <div class='float_right createdModifiedEditTop'><b>Created By :</b> {$row['created_by']} on {$row['creation_date']}&nbsp;&nbsp;&nbsp;&nbsp;<b>Modified By:</b> {$row['modified_by']} {$row['modification_date']}</div>
        </div>";

        $appendSqlPv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPv = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $visit_code = '';
        if($row['visit_code'] != ''){
            $visit_code = 'LT-'.$row['visit_code'];
        }

        $age = '';
        if($row['dob'] != ''){
            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
        }


        $patient_Link = "index.php?_topRm=main&module=hms_patientInformation&_action=edit&patient_information_id={$row['patient_information_id']}";
        $patient_name = "<a href='{$patient_Link}' target='_blank'><u>{$row['patient_name']}</u></a>";

        $status  = $fn->getReqParam('status');
        $recordTypeArray = array(
            "By Appointment"
           ,"Walk In"
        );

        $statusArray = array(
            "status"
           ,"New"
           ,"Visited Dr"
           ,"Closed"
           ,"On Hold"
           ,"Cancelled"
        );

        $expComp  = array('detailValue' => $row['company_name']);

        $patSumCount = $fn->getRecordCount('lab_test', "patient_information_id = '{$row['patient_information_id']}' AND status != 'Cancelled'");
        $pastVisit = '';

        if($patSumCount > 1){
            $viewOverallSummaryLink = "index.php?_topRm=main&module=hms_labTest&_spAction=overallSummary&lab_test_id={$row['lab_test_id']}&patient_information_id={$row['patient_information_id']}&showHTML=0";
            $pastVisit ="
            <a href='{$viewOverallSummaryLink}' class='viewOverallSummary ml20'><u>Past Summary</u></a>";
        }

        /*$age    = explode(".", $row['age']);
        $year = $age['0'];
        $month = $age['1'];*/
        $sqlGender      = $fn->getValueListSQL('gender');
        $sqlFees      = $fn->getValueListSQL('fees');
        $check_up_date_pv = $fn->getCPDate($row['check_up_date'],"d-m-Y");
$sqlEmployeeReferral = "
        SELECT employee_id
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE status = 'Active'
        ORDER BY employee_name
        ";

        $text = "
        {$gotoSearch}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Patient Visit Details</div>
                    <div class='toggle'></div>
                    <div class='float_right ml20'>Visit Code: {$visit_code}</div>
                    <div class='float_right mr20'>Check Up Date&Time: {$check_up_date_pv} {$row['check_up_time']}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='15%'>{$formObj->getTBRow('Name', 'name', $row['patient_name'])}</td>
                                <td width='25%'>
                                <div class='ageBox'>{$formObj->getTBRow('Age (Yrs)' , 'age_year', $row['age_year'])}</div>
                                <div class='ageBox'>{$formObj->getTBRow('(Months)' , 'age_month', $row['age_month'])}</div>
                                <div class='ageBox'>{$formObj->getTBRow('(Days)' , 'age_day', $row['age_day'])}</div>
                                </td>
                                <td width='15%'>{$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVl)}
                                </td>
                                <td width='15%'>{$formObj->getTBRow('Mobile', 'patient_phone', $row['patient_phone'])}</td>
                                <td width='20%'>{$formObj->getTBRow('Father Name', 'father_name', $row['father_name'])}</td>
                            </tr>
                            <tr>
                                <td width='15%'>{$formObj->getTBRow('Husband Name', 'spuse_name', $row['spuse_name'])}</td>
                                <td width='25%'>{$formObj->getTBRow('Town/City', 'address_area', $row['address_area'])}</td>
                                <td width='15%'>{$formObj->getDDRowByArr('Status', 'status', $statusArray, $row['status'], $expNoEdit)}</td>
                                <td class='notesTitle' width='15%' colspan='1'>{$formObj->getDateRow('Check up Date(YEAR-MONTH-DATE)', 'check_up_date', $row['check_up_date'])}</td>
                                <td class='notesTitle' width='15%' colspan='1'>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                               
                                <input type='hidden' name='lab_test_id' value='{$row['lab_test_id']}' />
                            </tr>
                            <tr>
                             <td width='25%'>{$formObj->getDDRowBySQL('Referral Doctor', 'referral_doctor_id', $sqlEmployeeReferral, $row['referral_doctor_id'])}<?td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>
                        {$pastVisit}
                    </div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <div id='tabs' class='mb20'>
                        <ul>
                            {$medicalTestLbl}
                        </ul>
                        {$medicalTestTab}
                        <div class='tab-footer'>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id='patientVisitInvoicePortal'>
            {$invoicePortalDisplay}
        </div>

        {$actionButtons}
        <div id='patientVisitReceiptPortal'>{$receiptPortalDisplay}</div>
        <input type='hidden' id='fld_order_id' name='order_id' value='{$rowOrder['order_id']}' />
        ";

        /*<div id='patientVisitSummaryPortal'>
            {$this->getPatientVisitSummaryPortal($row['patient_information_id'])}
        </div>*/
        return $text;
    }
    /**
     *
     */
    function getOverallSummary(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $lab_test_id = $fn->getReqParam('lab_test_id');

        $appendSqlPV = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM lab_test pv
        WHERE pv.patient_information_id = '{$patient_information_id}'
        AND pv.status != 'Cancelled'
        {$appendSqlPV}
        ORDER BY check_up_date DESC
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $employee_id_count = '';
        $treatment = '';
        $employeeTitle = '';
        $PvText = '';
        $balance_Amount = '0.00';
        $overall_balance_Amount = '0.00';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLEV = "
            SELECT Distinct ev.employee_id
                  ,(CASE 
                    WHEN e.salutation = 'Dr' 
                    THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                    ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                    END) AS employee_name
                  ,ev.lab_test_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.lab_test_id = '{$rowPv['lab_test_id']}'
            GROUP BY employee_name
            ";
            $resultEv = $db->sql_query($SQLEV);
            $drAttended = '';
            while ($rowEv = $db->sql_fetchrow($resultEv)) {
                $drAttended .=$rowEv['employee_name'] . ', ';
            }
            $dr_attended = rtrim($drAttended,', ');
            $dr_attended =  substr($dr_attended , 0, 9);

            $SQL1 = "
            SELECT mt.title
            FROM medical_test_lab mt
            WHERE mt.lab_test_id = '{$rowPv['lab_test_id']}'
            ";
            $result1 = $db->sql_query($SQL1);
            $labTest = '';
            while ($rowTv = $db->sql_fetchrow($result1)) {
                $labTest .=$rowTv['title'] . ', ';
            }
            $labTest = rtrim($labTest,', ');

            $SQL2 = "
            SELECT mt.*
            FROM medicines_visit mt
            WHERE mt.lab_test_id = '{$rowPv['lab_test_id']}'
            ";
            $result2 = $db->sql_query($SQL2);
            $medTest = '';
            $instructionDisplay = '';
            while ($row2 = $db->sql_fetchrow($result2)) {
                if($row2['qty'] == 0 || $row2['qty'] == ''){
                    $qty = "{$row2['qty']}";
                } else {
                    $qty = "({$row2['qty']})";                    
                }

                if($row2['instruction'] != ''){
                    $instruction = explode(", ", $row2['instruction']);
                    $instructionLen = count($instruction);

                    $morning = 0;
                    $noon = 0;
                    $night = 0;

                    if($row2['dosage'] == ''){
                        $row2['dosage'] = 1;
                    }

                    for($i=0;$i<$instructionLen;$i++){
                        //print $instruction[$i];
                        if($instruction[$i] == 'Morning'){
                            $morning = $row2['dosage'];
                        }
                        if($instruction[$i] == 'Noon'){
                            $noon = $row2['dosage'];
                        }
                        if($instruction[$i] == 'Night'){
                            $night = $row2['dosage'];
                        }
                    }

                    if($row2['instruction'] == 'STAT' || $row2['instruction'] == 'SOS'){
                        $instructionDisplay = $row2['instruction'];
                    } else {
                        $instructionDisplay = $morning.' - '.$noon.' - '.$night;
                    }
                }

                $medTest .="<div>{$row2['title']}{$qty} {$instructionDisplay}</div>";
            }
            //$medTest = rtrim($medTest,', ');

            $check_up_date = $fn->getCPDate($rowPv['check_up_date'],"d-m-Y");

            $orderRec = $fn->getRecordByCondition('order', "lab_test_id = '{$rowPv['lab_test_id']}'");
            $total_invoice_amount = '0.00';
            $invoiced_Paid_Amount = '0.00';

            if($orderRec['order_id'] != ''){
                $appendSqlOrd = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSqlOrd = "AND o.site_id = {$cpSiteIdSession}";
                }

                $subSqlForPercentSum = "
                SELECT o.*
                      ,(SELECT SUM(invHist.amount) AS prev_sum
                        FROM invoice_receipt_history invHist
                        LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                        LEFT JOIN `invoice` i ON (i.order_id = {$orderRec['order_id']})
                        WHERE invHist.related_invoice_id =  i.invoice_id
                        AND r.receipt_status != 'Cancelled'
                        AND i.status != 'Cancelled'
                        ) as Amount_Paid
                     ,(SELECT SUM(inv.invoice_amount)
                        FROM invoice inv
                        WHERE inv.order_id = o.order_id 
                        AND inv.status != 'Cancelled'
                        ) as total_invoice_amount
                FROM `order`o
                WHERE o.order_id = {$orderRec['order_id']}
                {$appendSqlOrd}
                ";
                $resultSubSql = $db->sql_query($subSqlForPercentSum);
                $rowSql       = $db->sql_fetchrow($resultSubSql);

                if($rowSql['total_invoice_amount'] != ''){
                    $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
                    $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                    $balance_Amount = number_format($balance_Amount, 2);
                    $overall_balance_Amount += $balance_Amount;
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }else{
                    $total_invoice_amount = $rowSql['total_invoice_amount'];
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                    $overall_balance_Amount += $balance_Amount;
                    $balance_Amount = number_format($balance_Amount, 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }
            }

            $overall_balance_Amount = number_format($overall_balance_Amount, 2);

            $visit_code_Link = "index.php?_topRm=main&module=hms_labTest&_action=edit&lab_test_id={$rowPv['lab_test_id']}";
            $visit_codePVt = "<a href='{$visit_code_Link}' target='_blank'>LT- {$rowPv['visit_code']}</a>";

            $viewSummaryTreatmentLink = "index.php?_topRm=main&module=hms_labTest&_spAction=viewSummaryTreatment&lab_test_id={$rowPv['lab_test_id']}&showHTML=0";
            $viewSummaryTreatment = "<a href='{$viewSummaryTreatmentLink}' class='viewSummaryForTreatmentRecord'><u>View Summary</u></a>";

            //$applyMedicineLink = "index.php?_topRm=main&module=hms_labTest&_spAction=applyMedicine&lab_test_id={$rowPv['lab_test_id']}&lab_test_id_main={$lab_test_id}&showHTML=0";
            $applyMedicine = '';
            if($medTest != '') {
                $applyMedicine = "<a href='#' class='btn btn-info applyForMedicineRecord' lab_test_id={$rowPv['lab_test_id']} lab_test_id_main={$lab_test_id}>Apply</a>";
            }

            $PvText .= "
            <tr>
                <td width='10%'>{$check_up_date}</td>
                <td width='10%'>{$dr_attended}</td>
                <td width='15%'>{$rowPv['complain']}</td>
                <td width='37%'>{$applyMedicine}{$medTest}</td>
                <td width='13%'>{$labTest}</td>
                <td width='5%'>{$total_invoice_amount}</td>
                <td width='10%'>{$rowPv['notes']}</td>
            </tr>
            ";
        }

        $text = "
        <div class=''>
            <div>
                <div class='patientVisitSummaryPortal'>
                    <table class='thinlist mb20 overallSummaryPortal'>
                        <thead>
                            <tr>
                                <th class=''>Date</td>
                                <th class=''>Attended By</td>
                                <th class=''>Disease List</td>
                                <th class=''>Medicine List</td>
                                <th class=''>Lab Test</td>
                                <th class=''>Fees</td>
                                <th class=''>Notes</td>
                            </tr>
                        </thead>
                        <tbody>
                            {$PvText}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <input type='hidden' id='fld_patient_information_id' value='{$patient_information_id}'>
        ";

        return $text;
    }

    /**
     */
    function getMedicalPortalDisplay($lab_test_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');

        if($lab_test_id == ''){
            $lab_test_id = $fn->getReqParam('lab_test_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_labTest&_spAction=MedicalTestRecordSubmit&showHTML=0";
        $rows  = "";
        $catRows = '';
        $catLinks = '';

        /*$SQLMedicalTest = "
        (SELECT m.medical_test_id
              ,m.title
              ,m.fees
        FROM medical_test m
        LEFT JOIN (medical_test_lab mv) ON (mv.medical_test_id = m.medical_test_id)
        WHERE lab_test_id = {$lab_test_id}
        )
        UNION
        (SELECT m.medical_test_id
              ,m.title
              ,m.fees
        FROM medical_test m
        )
        ";*/

        $SQLCat = "
        SELECT value
              ,valuelist_id
        FROM valuelist
        WHERE key_text = 'investigationCategory'
        ORDER BY sort_order
        ";
        $resultCat   = $db->sql_query($SQLCat);
        $count = 0;
        while ($rowCat = $db->sql_fetchrow($resultCat)) {
            $SQLMedicalTest = "
            SELECT m.medical_test_id
                  ,m.title
                  ,m.fees
                  ,m.lab_supplier_fees
                  ,m.container
            FROM medical_test m
            WHERE category = '{$rowCat['value']}'
            ORDER BY m.title
            ";

            $result   = $db->sql_query($SQLMedicalTest);
            $inputRow  = "";
            $title ="{$rowCat['value']}";
            $countrow=1;
            while ($row = $db->sql_fetchrow($result)) {
                $medTestVisitRec = $fn->getRecordByCondition('medical_test_lab', "lab_test_id = '{$lab_test_id}' AND title='{$row['title']}'");

                if($medTestVisitRec['medical_test_lab_id'] != ''){
                    $checked = "checked='checked'";
                    $class ="";
                    $bgColor = "bgColorHighlight";
                } else {
                    $checked = '';
                    $class ="displayNone";
                    $bgColor = '';
                }

                if($medTestVisitRec['supplier_id'] > 0){
                    $checked1 = "checked='checked'";
                    $class1 ="";
                } else {
                    $checked1 = '';
                    $class1 ="displayNone";
                }

                if($medTestVisitRec['notes'] != ''){
                    $notes = 'View Result';
                }else {
                    $notes = 'Add Result';
                }

                if($medTestVisitRec['fees'] != ''){
                    $fees = $medTestVisitRec['fees'];
                }else {
                    $fees = $row['fees'];
                }

                if($medTestVisitRec['creation_date'] != ''){
                    $creationDate = $fn->getCPDate($medTestVisitRec['creation_date'],"Y-m-d");
                }else {
                    $creationDate = date("Y-m-d");
                }

                        /*<div class='hideTreatmentDetails_{$row['title']}_{$count} hideLabDetails {$class} labVisitNotes'>
                            <input type='text' value='{$fees}' id='fld_fees' class='text mt10 mb10' name='fees[]'>
                            <div><a href='#' class='addNoteLab'>{$notes}</a></div>
                            <div class='hideNotesLab'>
                                <div class='type-text ym-fbox-text row_notes'>
                                    <textarea id='fld_notes' name='notes[]'>{$medTestVisitRec['notes']}</textarea>
                                </div>
                            </div>
                        </div>*/
                $divSeparation = '';
                if($countrow == 5){
                    $divSeparation = "</div><div class='col-md-4 col-sm-4'>";
                    $countrow = 0;
                }

                $SQLMTP = "
                SELECT m.medical_test_parameter_id
                      ,m.title
                      ,m.normal_value
                      ,m.medical_test_id
                FROM medical_test_parameter m
                WHERE m.medical_test_id = '{$row['medical_test_id']}'
                ORDER BY m.title
                ";

                $resultMTP   = $db->sql_query($SQLMTP);
                $numRows = $db->sql_numrows($resultMTP);
                $resultShow = '';
                $classResult = '';
                $view = '';
                if($numRows > 0){
                    $classResult = "hideme";

                    $viewMedPara = "index.php?_topRm=main&module=hms_labTest&_spAction=viewMedicalParameters&lab_test_id={$lab_test_id}&medical_test_id={$row['medical_test_id']}&showHTML=0";

                    $view = "<a href='{$viewMedPara}' class='viewMedPara float_right'><u>Add Result</u></a>";
                }
                $investigation_date_id = 'investigation_date_'.$row['medical_test_id'];
                $hospitalFees = $fees - $row['lab_supplier_fees'];
                $supplierRec = $fn->getRecordByCondition('supplier', "category = 'Lab'");

                $inputRow .= "
                <div class=''>
                    <div class='type-check ym-fbox-check labTestBox {$bgColor}'>
                        <input type='checkbox' id='title_{$count}' {$checked} value='{$row['title']}_{$count}' name='title[]' class='labTitle' lab_test_id = '{$lab_test_id}' medical_test_id='{$row['medical_test_id']}'>
                        <label for='title_{$count}'>{$row['title']}</label>
                        <div class='hideTreatmentDetails_{$row['title']}_{$count} hideLabDetails {$class} labVisitNotes'>
                            {$view}
                              <div>
                        <input type='checkbox' id='print_label_{$count}' value='1' name='print_label[]' class='printLabel' lab_test_id='{$lab_test_id}' medical_test_id='{$row['medical_test_id']}' ".($medTestVisitRec['print_label'] == 1 ? "checked='checked'" : "")." onclick=\"this.value = this.checked ? '1' : '0'\">
                        <label for='print_label_{$count}'>Print</label>
                    </div>
                            <div>
                                <label>Fees</label>
                                <input type='text' value='{$fees}' id='fld_fees' class='labFees text mt10 mb10' name='fees[]' lab_test_id = '{$lab_test_id}' medical_test_id='{$row['medical_test_id']}'>
                            </div>
                            <div>
                                {$formObj->getDateRow('Date (Year-Month-Date)', $investigation_date_id, $creationDate)}
                                <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id' />
                                <input type='hidden' value='{$lab_test_id}' name='lab_test_id' />
                            </div>
                            <div class='{$classResult}'>
                                <label>Result</label>
                                <div class='type-text ym-fbox-text row_notes'>
                                    <textarea id='fld_notes' class='labNotes' name='notes[]' lab_test_id = '{$lab_test_id}' medical_test_id='{$row['medical_test_id']}'>{$medTestVisitRec['notes']}</textarea>
                                </div>
                            </div>
                            <div>
                                <input type='checkbox' id='' {$checked1} value='{$row['lab_supplier_fees']}' name='other_labs[]' class='otherLabs' lab_test_id = '{$lab_test_id}' medical_test_id='{$row['medical_test_id']}' supplier_id = {$supplierRec['supplier_id']} container = '{$row['container']}'>
                                <label for='{$row['medical_test_id']}'>Other Labs</label>
                            </div>
                            <div class='hideLabSupplierFees {$class1}'>
                                <div>
                                    <label>Lab Supplier Fees : {$row['lab_supplier_fees']}<label>
                                </div>
                                <div>
                                    <label>Hospital Fees : {$hospitalFees}<label>
                                </div>
                                <div>
                                    <label>Container : {$row['container']}<label>
                                </div>
                            </div>
                        </div>
                        <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id[]' />
                    </div>
                </div>
                {$divSeparation}
                ";
                $count ++;
                $countrow ++;
            }
            $catRows .= "
            <div class='panel panel-default'>
                <div class='panel-heading' id='{$title}'>
                    <strong>{$title}</strong>
                    <a href='#saveBtn' class='ml20'><u>Go to Top</u></a>
                    <a class='medTestMainSubmit btn btn-info ml20' lab_test_id = '{$lab_test_id}'>Save</a>
                </div>
                <div class='panel-body'><div class='floatbox col-md-4 col-sm-4'>{$inputRow}</div></div>
            </div>
            ";
            //$catLinks .= "<a href='#{$title}' class='mr20'><u>{$title}</u></a>";
            $catLinks .= "<li class='ui-state-default'><a href='#{$title}' valuelist_id='{$rowCat['valuelist_id']}' class='investigationCategoryLink mr20'><u>{$title}</u></a></li>";
        }

        $patientVisitRec = $fn->getRecordByCondition('lab_test', "lab_test_id = '{$lab_test_id}'");
        $text = "
        <form>
        </form>
        <div id='' class=''>
            <form id='portalForm_medicalTestDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button' id='saveBtn'>
                </div>
                <ul id='sortableInvesticationCategories'>  
                    {$catLinks}
                </ul>
                <br/><br/><br/>
                <div class='floatbox'>{$catRows}</div>
                <input type='hidden' name='lab_test_id' value='{$lab_test_id}' />
            </form>
            <script type='text/javascript'>
                jQuery('document').ready(function () {
                    cpm.hms.labTest.sortInvestigationGroups();
                });
            </script>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getViewMedicalParameters() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $medical_test_id = $fn->getReqParam('medical_test_id');
        $lab_test_id = $fn->getReqParam('lab_test_id');

        $SQLMTP = "
        SELECT m.medical_test_parameter_id
              ,m.title
              ,m.normal_value
              ,m.medical_test_id
        FROM medical_test_parameter m
        WHERE m.medical_test_id = '{$medical_test_id}'
        ORDER BY m.medical_test_parameter_id
        ";

        $resultMTP   = $db->sql_query($SQLMTP);
        $numRows = $db->sql_numrows($resultMTP);
        $rowTitle = '';
        while ($rowMTP = $db->sql_fetchrow($resultMTP)) {
            $medVisitParaRec = $fn->getRecordByCondition('medical_visit_parameter', "medical_test_parameter_id = '{$rowMTP['medical_test_parameter_id']}' AND medical_test_id='{$rowMTP['medical_test_id']}' AND lab_test_id = {$lab_test_id}");

            $rowTitle .= "
            <div class='type-text ym-fbox-text row_notes medParaList'>
                <label>{$rowMTP['title']}</label> 
                <textarea id='fld_para_notes' class='med_para_notes' medical_test_id ='{$rowMTP['medical_test_id']}' medical_test_parameter_id={$rowMTP['medical_test_parameter_id']} lab_test_id = {$lab_test_id} name='para_notes[]'>{$medVisitParaRec['notes']}</textarea>
            </div>
            <input type='hidden' value='{$rowMTP['medical_test_parameter_id']}' name='medical_test_parameter_id[]' />
            <input type='hidden' value='{$rowMTP['medical_test_id']}' name='medical_test_id_para[]' />
            ";
        }
        $medTestVisitRecCon = $fn->getRecordByCondition('medical_test_lab', "lab_test_id = '{$lab_test_id}' AND medical_test_id='{$medical_test_id}'");
        if($medTestVisitRecCon['medical_test_lab_id'] != ''){
            $text = "
            {$rowTitle}
            <div><a class='medParaSubmit btn btn-info'>Save</a></div>
            ";
        } else{
            $text = "
            Please click save.
            ";            
        }


        return $text;
    }

    /**
     *
     */
    function getAddNoteLab() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $lab_visit_id = $fn->getReqParam('lab_visit_id');

        $formAction = "index.php?module=hms_labTest&_spAction=addNoteLabSubmit&showHTML=0";
        $labVisitRec = $fn->getRecordRowByID('lab_visit', 'lab_visit_id', $lab_visit_id);

        $text = "
        <form id='portalForm' class='yform columnar addNoteForm' method='post' action='{$formAction}'>
            {$formObj->getTARow('Notes', 'notes', $labVisitRec['notes'])}
            <input type='hidden' name='lab_visit_id' value='{$lab_visit_id}' />
        </form>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');


        $record_id = $fn->getIssetParam($row, 'lab_test_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_labTest', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $statusArray = array(
            "New"
           ,"Visited"
           ,"Closed"
           ,"On Hold"
           ,"Cancelled"
        );

        $yesterdayArray = array(
             "All Days"
            ,"Yesterday"
        );

        $billType           = $fn->getReqParam('bill_type');
        $sqlBillType        = $fn->getValueListSQL('billType');
        $status             = $fn->getReqParam('status');
        $yesterday          = $fn->getReqParam('yesterday');
        $check_up_date1     = $fn->getReqParam('check_up_date_1');
        $check_up_date2     = $fn->getReqParam('check_up_date_2');
        $sqlCategory        = $fn->getValueListSQL('employeeCategory');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if($yesterday == ""){
            $yesterday = "Yesterday";
        }

        $text = "
        <td>
            <select name='yesterday'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($yesterdayArray, $yesterday)}
           </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($statusArray, $status)}
           </select>
        </td>
        <td>
            {$formObj->getDateRangeRow('Visit Date:', 'check_up_date', $check_up_date1, $check_up_date2)}
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryInOrder() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $order_id = $fn->getReqParam('order_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND o.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT o.*
              ,(SELECT SUM(round((oi.unit_price * oi.qty),2))
               FROM order_item oi
               WHERE oi.order_id = o.order_id
               ) AS order_amount
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                WHERE i.order_id = o.order_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM receipt r
                WHERE o.order_id = r.order_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
              ,(SELECT  SUM(oi.unit_price)
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type = 'Doctor/Nurse'
                )AS consultation_fees
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS Total_Amount
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                LEFT JOIN `invoice` i ON (i.order_id = {$order_id})
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) as Amount_Paid
        FROM `order`o
        WHERE o.order_id = {$order_id}
        {$appendSql}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $orderAmt   = number_format(round($row['order_amount']), 2);
        $invoiceAmt = number_format($row['invoice_amount'] ,2);
        $receiptAmt = number_format($row['receipt_amount'] ,2);

        $total_invoice_amount = 0;
        if($row['invoice_amount'] != ''){
            $total_invoice_amount = $row['invoice_amount'] - $row['discount'];
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
        }else{
            $total_invoice_amount = $row['invoice_amount'];
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
        }

        $total_invoice_amount = number_format($total_invoice_amount, 2);

        $outstandingInvoiceAmt = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);
        $overallBalanceAmt     = number_format($row['order_amount'] - $row['receipt_amount'], 2);

        $order_items_Details = '';

        $Lab = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        $Sub_Total = 0;
        if($numRowsOrderItem > 0){
            $count = 1;
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$row['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                    $rowOrderItem['record_type'] = 'Consultation Fees';
                }


                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount'] = $rowOrderItem['QTY_AMOUNT'];
                }

                $Lab .= "<tr>
                            <td><b>{$rowOrderItem['record_type']}</b>
                            <ol>
                        ";


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        if($rowOrderItem['record_type'] != 'Consultation Fees'){
                            $Lab .= "<li>{$rowList['item_title']}</li>";
                        }
                    }
                }

                $Lab .="</ol></td>
                                <td class='txtRight'>{$rowOrderItem['Amount']}</td>
                            </tr>";

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }
        }

        $order_items_Details .="{$Lab}";
        $total_amount = number_format($Sub_Total - $row['discount'], 2);
        $Sub_Total    = number_format($Sub_Total, 2);
        $discount     = number_format($row['discount'], 2);

        $rows = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <table class='thinlist'>
                        <tr>
                            <th>Total Amount: {$total_invoice_amount}</th>
                            <th>Amount Paid: {$invoiced_Paid_Amount}</th>
                            <th>Amount Due: {$balance_Amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tr>
                            <th>Description</th>
                            <th class='txtRight'>Amount</th>
                        </tr>
                        {$order_items_Details}
                        <tr>
                            <th>Sub Total</th>
                            <th class='txtRight'>{$Sub_Total}</th>
                        </tr>
                        <tr>
                            <th>Discount</th>
                            <th class='txtRight'>{$discount}</th>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <th class='txtRight'>{$total_amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        ";

        $text = "
        {$rows}
        ";

        return $text;

    }

    /**
     *
    **/
    function getPrintTokenForVisit(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $lab_test_id       = $fn->getReqParam('lab_test_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');

        $SQL = "
        SELECT pv.check_up_date
              ,pv.check_up_time
              ,p.patient_information_id
              ,p.nric
              ,p.mobile
              ,p.age
              ,p.gender
              ,p.email
              ,p.dob
              ,p.father_name
              ,p.spuse_name
              ,p.address_area
              ,p.phone
              ,p.name AS patient_name
              ,pv.visit_code
              ,pv.lab_test_id
        FROM lab_test pv
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.lab_test_id = '{$lab_test_id}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $visit   = $db->sql_fetchrow($result);
        //============================================================================= //

        $today = date("d-m-Y");

        $gender = '';
        if($visit['gender'] == 'Female'){
            $gender = 'F';
        }else if($visit['gender'] == 'Male'){
            $gender = 'M';            
        }

        if($visit['gender'] != "" && $visit['age'] != ""){
            $genderAge = "({$gender}/{$visit['age']})";
        }

        elseif ($visit['gender'] != "" && $visit['age'] == "") {
            $genderAge = "({$gender})";
        }

        elseif ($visit['gender'] == "" && $visit['age'] != "") {
            $genderAge = "({$visit['age']})";
        }

        else{
            $genderAge = "";
        }

        //print(strlen($visit['patient_name']));
        $height = 150;
        $patientNameLength = strlen($visit['patient_name']);
        if($patientNameLength > 6){
            $height = $height + 20;
        }

        $tbl1 = '
        <table border="0" style="border:1px Solid #000000" cellpadding="4">
            <tr>
                <td width="38%">Visit Code</td>
                <td width="6%" >:</td>
                <td width="56%">LT-'.$visit['visit_code'].'</td>
            </tr>
            <tr>
                <td width="38%">Name</td>
                <td width="6%" >:</td>
                <td width="56%">'.$visit['patient_name'].' '.$genderAge.'</td>
            </tr>
            <tr>
                <td width="38%">Town/City</td>
                <td width="6%" >:</td>
                <td width="56%">'.$visit['address_area'].'</td>
            </tr>
            <tr>
                <td width="38%">Father Name</td>
                <td width="6%" >:</td>
                <td width="56%">'.$visit['father_name'].'</td>
            </tr>
            <tr>
                <td width="38%">Husband Name</td>
                <td width="6%" >:</td>
                <td width="56%">'.$visit['spuse_name'].'</td>
            </tr>
        </table>
        ';

        $pdf = new MYPDF_Local('L', 'px', array('302.250', $height), true, 'UTF-8', false);

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
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER, 10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();
        $pdf->SetFont('Courier', 'B', 11);
        $pdf->ln(-8);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $download_title = $visit['visit_code'] . '-Token.pdf';
        $pdf->IncludeJS("print();");
        $pdf->Output($download_title, 'I');

    }

    /**
     *
     */
    function getPrintLabReport() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Prescription');
        $pdf->SetTitle('Print Prescription');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(10, PDF_MARGIN_TOP, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage('P', 'A5');

        $lab_test_id = $fn->getReqParam('lab_test_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name

            FROM lab_test pv
            LEFT JOIN medicines_visit m ON (m.lab_test_id = pv.lab_test_id)
            LEFT JOIN employee e ON (e.employee_id = pv.referral_doctor_id)
            LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
            WHERE pv.lab_test_id = '{$lab_test_id}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B', 10);

        $today = date("d-m-Y");
        $gender = '';
        if($company['gender'] == 'Female'){
            $gender = 'F';
        }else if($company['gender'] == 'Male'){
            $gender = 'M';            
        }

        $currentDate   = date("d-m-Y");

        $visit_code = '';
        if($company['visit_code'] != ''){
            $visit_code = 'LT-'.$company['visit_code'];
        }

        $age = '';

        if($company['age_year'] != ''){
            $age = $company['age_year'].' Yrs';
        } elseif($company['age_month'] != ''){
            $age = $company['age_month'].' Months';
        } elseif($company['age_day'] != ''){
            $age = $company['age_day'].' Days';
        }

        $doctorName = '';
        /*if($company['first_name'] == 'DR.SHEIK ABDUL KHADER'){
            $doctorName = $company['first_name'];
        }*/
        
        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td width="70%">Pt Name:'.$company['patient_name'].'('.$gender .'/'.$age.')</td>
                <td width="30%" align="left">Date:'.$currentDate.'</td>
            </tr>
            <tr>
                <td width="70%" align="">Ref By: '.$company['employee_name'].'</td>
                <td width="30%" align="left">Code:'.$visit_code.'</td>
            </tr>
        </table>
        <table border="0" width="120%">
            <tr>
                <td align="right">Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().'</td>
            </tr>
        </table>
        ';

        $SQL1 = "
        SELECT mt.*
              ,m.normal_value
              ,m.fees AS amount
              ,m.units
        FROM medical_test_lab mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.lab_test_id = '{$lab_test_id}'
          AND mt.print_label = 1
        ";
        $result1 = $db->sql_query($SQL1);
        $numRows1 = $db->sql_numrows($result1);
        $marginTop = '';
        if($numRows1 == 1){
            $marginTop = "<br/><br/>";
        }
        $tbl4 = '
        <table cellpadding="4" border="0">
            <thead>
                <tr>
                    <th colspan="4" style="text-decoration:underline;">Reports:</th>
                </tr>
                <tr bgcolor="#D3D3D3" >
                    <th style="border-left:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="35%">Test Name</th>
                    <th style="border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="25%">Value</th>
                    <th style="border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="19%">Units</th>
                    <th style="border-right:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="21%">Ref.Range</th>
                </tr>
            </thead>
        ';
        while ($row1 = $db->sql_fetchrow($result1)) {

            $SQLMTP = "
            SELECT m.medical_test_parameter_id
                  ,m.title
                  ,m.normal_value
                  ,m.medical_test_id
                  ,m.units
            FROM medical_test_parameter m
            WHERE m.medical_test_id = '{$row1['medical_test_id']}'
            ORDER BY m.medical_test_parameter_id
            ";

            $resultMTP   = $db->sql_query($SQLMTP);
            $numRowsMTP = $db->sql_numrows($resultMTP);

            if($numRowsMTP > 0){
                $tbl4 = $tbl4.'
                <tr>
                    <td width="100%" align="left" style="font-weight:bold;font-size:12pt;">'.$row1['title'].'</td>
                </tr>
                ';
            }else{
                $tbl4 = $tbl4.'
                <tr>'.$marginTop.'
                    <td width="35%" align="left" style="line-height:30px;font-size:12pt;">'.$row1['title'].'</td>
                    <td width="25%" align="left" style="line-height:30px;">'.$row1['notes'].'</td>
                    <td width="13%" align="left" style="line-height:30px;">'.$row1['units'].'</td>
                    <td width="27%" align="left" style="line-height:30px;">'.nl2br($row1['normal_value']).'</td>
                </tr>
                ';
            }

            while ($rowMTP = $db->sql_fetchrow($resultMTP)) {
                $medVisitParaRec = $fn->getRecordByCondition('medical_visit_parameter', "medical_test_parameter_id = '{$rowMTP['medical_test_parameter_id']}' AND medical_test_id='{$rowMTP['medical_test_id']}' AND lab_test_id = {$lab_test_id}");
                $tbl4 = $tbl4.'<tr>
                            <td width="35%" style="font-weight:bold;" align="left">'.strtoupper($rowMTP['title']).'</td>
                            <td width="25%" style="font-weight:bold;" align="left">'.$medVisitParaRec['notes'].'</td>
                            <td width="19%" align="left" style="font-size:9pt;">'.$rowMTP['units'].'</td>
                            <td width="21%" align="left" style="font-size:9pt;">'.nl2br($rowMTP['normal_value']).'</td>
                        </tr>
                        ';
            }
        }

        $tbl4 = $tbl4.'</table>';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td align="right">Lab Technician</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, false, false, false, false, '');
        //$pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tblHead, true, false, false, false, '');
        //$pdf->writeHTML($tbl3, false, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->ln(10);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $download_title = 'Lab_Report.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintLabRequestForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Prescription');
        $pdf->SetTitle('Print Prescription');

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

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage('P', 'A5');

        $lab_test_id = $fn->getReqParam('lab_test_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
         FROM lab_test pv
         LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
         WHERE pv.lab_test_id = '{$lab_test_id}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B', 10);

        $today = date("d-m-Y");
        $gender = '';
        if($company['gender'] == 'Female'){
            $gender = 'F';
        }else if($company['gender'] == 'Male'){
            $gender = 'M';            
        }

        $currentDate   = date("d-m-Y");

        $visit_code = '';
        if($company['visit_code'] != ''){
            $visit_code = 'LT-'.$company['visit_code'];
        }

        $age = '';

        if($company['age_year'] != ''){
            $age = $company['age_year'].' Yrs';
        } elseif($company['age_month'] != ''){
            $age = $company['age_month'].' Months';
        } elseif($company['age_day'] != ''){
            $age = $company['age_day'].' Days';
        }

        $doctorName = '';
        /*if($company['first_name'] == 'DR.SHEIK ABDUL KHADER'){
            $doctorName = $company['first_name'];
        }*/
        
        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td width="70%">Pt Name:'.$company['patient_name'].'('.$gender .'/'.$age.')</td>
                <td width="30%" align="left">Date:'.$currentDate.'</td>
            </tr>
            <tr>
                <td width="70%" align="">Ref By:</td>
                <td width="30%" align="left">Code:'.$visit_code.'</td>
            </tr>
        </table>
        <table border="0" width="120%">
            <tr>
                <td align="right">Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().'</td>
            </tr>
        </table>
        ';

        $SQL1 = "
        SELECT mt.*
              ,m.normal_value
              ,mt.fees AS amount
        FROM medical_test_lab mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.lab_test_id = '{$lab_test_id}'
        ";
        $result1 = $db->sql_query($SQL1);
        $total_amount = 0;
        $tbl4 = '
        <table cellpadding="4" border="0" width="100%">
            <thead>
                <tr>
                    <th colspan="2" style="text-decoration:underline;">Lab Test Required:</th>
                </tr>
                <tr bgcolor="#D3D3D3">
                    <th style="border-left:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="70%">Test Name</th>
                    <th align="right" style="border-right:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="30%">Fees (Rs)</th>
                </tr>
            </thead>
        ';
        while ($row1 = $db->sql_fetchrow($result1)) {

            $tbl4 = $tbl4.'<tr>
                                <td align="left" width="70%">'.$row1['title'].'</td>
                                <td align="right"  width="30%">'.$row1['amount'].'</td>
                            </tr>
                            ';

            /*$SQLMTP = "
            SELECT m.medical_test_parameter_id
                  ,m.title
                  ,m.normal_value
                  ,m.medical_test_id
            FROM medical_test_parameter m
            WHERE m.medical_test_id = '{$row1['medical_test_id']}'
            ORDER BY m.title
            ";

            $resultMTP   = $db->sql_query($SQLMTP);
            $numRowsMTP = $db->sql_numrows($resultMTP);
            while ($rowMTP = $db->sql_fetchrow($resultMTP)) {
                $tbl4 = $tbl4.'
                        <tr>
                            <td align="left" style="font-weight:normal;" width="70%">'.strtoupper($rowMTP['title']).'</td>
                            <td align="right"  width="30%"></td>
                        </tr>
                        ';
            }*/

            $total_amount += $row1['amount'];
        }

        $tbl4 = $tbl4.'<tr>
                            <td align="right" width="75%">Total Amount : </td>
                            <td align="right"  width="25%" style="border-top:1px solid #000000; border-bottom:1px solid #000000;">'.$total_amount.'</td>
                        </tr>
                    </table>';

        $pdf->writeHTML($tbl1, false, false, false, false, '');
        //$pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tblHead, true, false, false, false, '');
        //$pdf->writeHTML($tbl3, false, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $download_title = 'Lab_Report.pdf';
        $pdf->Output($download_title, 'I');
    }
}