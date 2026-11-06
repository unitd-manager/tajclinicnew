<?
class CPL_Admin_Modules_Hms_PatientVisit_View extends CP_Common_Lib_ModuleViewAbstract
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
        $totalFees     = 0;
        $totalLabFees  = 0;
        foreach ($dataArray as $row){
            $email     = $row['email'];
            $check_up_date = $fn->getCPDate($row['check_up_date'],"d-m-Y");

            $visit_code = '';
            if($row['visit_code'] != ''){
                $visit_code = 'VST-'.$row['visit_code'];
            }
        
            $employeeVisitRec = $fn->getRecordRowByID('employee_visit', 'patient_visit_id', $row['patient_visit_id']);

            $totalFees += $employeeVisitRec['consultation_fees'];

            $age = '';
            $gender = '';

            if($row['age_year'] != ''){
                $age = $row['age_year'].' Yrs';
            } elseif($row['age_month'] != ''){
                $age = $row['age_month'].' Months';
            } elseif($row['age_day'] != ''){
                $age = $row['age_day'].' Days';
            }

            if($row['gender'] == 'Male'){
                $gender = 'M';
            } else if($row['gender'] == 'Female'){
                $gender = 'F';
            }
            $age = $gender . '(' . $age .')';
            $employee_name = substr($row['employee_name'],0,9);

            $labFeesSQL = "
            SELECT SUM(fees) as lab_fees 
            FROM `medical_test_visit` 
            WHERE patient_visit_id =  {$row['patient_visit_id']}
            ";
            $resultlabFees = $db->sql_query($labFeesSQL);
            $labFeesRow    = $db->sql_fetchrow($resultlabFees);
            $totalLabFees += $labFeesRow['lab_fees'];

            /*if($row['employee_id'] != "") {
                $referDrSQL = "
                SELECT  patient_information_id 
                FROM `patient_visit` 
                WHERE employee_id = {$row['employee_id']}
                ";
                $resultreferDr = $db->sql_query($referDrSQL);
                $referDrRow    = $db->sql_fetchrow($resultreferDr);
            }*/

            $visitCodeRow = "
            <td style='background-color: white;'>
            <div id=''>{$visit_code}</div>
            </td>
            ";

            /*if($row['referral_doctor_id'] != "") {
                $SQLRefDrCheck = "
                SELECT patient_visit_id
                FROM patient_visit
                WHERE referral_doctor_id = {$row['referral_doctor_id']}
                AND patient_information_id = {$row['patient_information_id']}
                AND patient_visit_id > {$row['patient_visit_id']}
                ";
                $resultRefDrCheck  = $db->sql_query($SQLRefDrCheck);
                $numRowsRefDrCheck = $db->sql_numrows($resultRefDrCheck);
                $rowRefDrCheck     = $db->sql_fetchrow($resultRefDrCheck); 

                if($numRowsRefDrCheck > 0) {
                    $visitCodeRow = "
                    <td class='blinkingBackground'>
                    <div id=''>{$visit_code}</div>
                    </td>
                    "; 
                }
            }*/
            if(($row['referral_doctor_id'] != "" && $row['visited_by_referral_doctor'] != 1) || ($row['review_date'] != "" && $row['visited_by_referral_doctor'] != 1)) {
                $visitCodeRow = "
                <td class='blinkingBackground'>
                <div id=''>{$visit_code}</div>
                </td>
                "; 
            }
            $review_date = $fn->getCPDate($row['review_date'],"d-m-Y");

            if($row['review_date'] != ""){
                $patient_name = $row['patient_name'].' ('.$review_date.')';
            } else{
                $patient_name = $row['patient_name'];   
            }
            
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['uhid'])}
            {$visitCodeRow}
            {$listObj->getListDataCell($check_up_date)}
            {$listObj->getListDataCell($patient_name)}
            {$listObj->getListDataCell($employee_name)}
            {$listObj->getListDataCell($employeeVisitRec['consultation_fees'], 'right')}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($age)}
            {$listObj->getListDataCell($labFeesRow['lab_fees'], 'right')}
            {$listObj->getListDataCell($row['address_area'])}
            {$listObj->getListDataCell($row['patient_phone'])}
            {$listObj->getListRowEnd($row['patient_information_id'])}
            ";

            $count++ ;
        }

        //$newPatientLink = "index.php?_topRm=main&module=hms_patientVisit&_action=new";
        $search_List    = "index.php?_topRm=main&module=hms_patientVisit";
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

        $totalFees = number_format($totalFees, 2);
        $totalLabFees = number_format($totalLabFees, 2);

        $text = "
        <div class='searchListDisplay {$displayNone}'>{$this->getSearchList()}</div>
        <div class='{$class}'>
            <div class='floatbox goToSearchPatientVisit'>
                <div class='float_left'>
                    <a href='{$search_List}' class='btn btn-info'>Go To Search</a>
                </div>
            </div>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('UHID', 'p.uhid')}
            {$listObj->getListHeaderCell('Visit Code', 'pv.visit_code')}
            {$listObj->getListHeaderCell('Visit Date', 'pv.check_up_date')}
            {$listObj->getListHeaderCell('Patient Name', 'patient_name')}
            {$listObj->getListHeaderCell('Attended By', 'employee_name')}
            {$listObj->getListHeaderCell('Fees', 'fees', 'txtRight')}
            {$listObj->getListHeaderCell('Status', 'p.status')}
            {$listObj->getListHeaderCell('Gender', 'p.gender')}
            {$listObj->getListHeaderCell('Lab Fees', 'p.age')}
            {$listObj->getListHeaderCell('Town/City', 'p.address_area')}
            {$listObj->getListHeaderCell('Phone', 'p.patient_phone')}
            {$listObj->getListHeaderEnd()}
            <tr bgcolor='#23B7E5'>
                <th colspan='7' class='totalFeesInList'>Total</th>
                <th class='totalFeesInList'>{$totalFees}</th>
            </tr>
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

        //$newPatientLink     = "index.php?_topRm=main&module=hms_patientVisit&_action=new";
        $patient_visit_List = "index.php?_topRm=main&module=hms_patientVisit";
        $expHideFirstOpt    = array('hideFirstOption' => 1);
        $searchlistArr      = array('Search by Name'
                                   ,'Search by NRIC');
        $row    = '';
        $expGender   = array('sqlType' => 'OneField');
        $sqlGender   = $fn->getValueListSQL('gender');

        $formActionAddpatient = "index.php?module=hms_patientVisit&_spAction=addPatientRecord&showHTML=0";
        $searchResultRows = $this->getPatientVisitSearchResult();
        $searchResultAppointmentRows = $this->getPatientVisitAppointmentSearchResult();

        /*
        <div class='float_right mb10'>
            <a href='{$formActionAddpatient}' class='button' id='addPatientRecord'>Quick Add Patient</a>
        </div>
        */
        $expGroupHeading = array('useKey' => false);
        $yesNoArr = array(1 => 'Male', 0 => 'Female');
        //{$formObj->getRRow('Gender', 'gender','', $yesNoArr, $expGroupHeading)}

        $vaccinationScheduleLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=vaccinationSchedule&showHTML=0";
        /*<div class='float_left'>
            <a href='{$vaccinationScheduleLink}' class='btn btn-warning vaccinationSchedule'>Vaccination Schedule</a>
        </div>*/

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
                                    <td colspan='3'>
                                        <div class='patientDetailsBoxUhid'>
                                            {$formObj->getTBRow('UHID', 'uhid', '')}
                                        </div>
                                        <div class='patientDetailsBoxName'>
                                            {$formObj->getTBRow('Patient Name', 'patient_name', '')}
                                        </div>
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
                                    <td>{$formObj->getTBRow('Husband Name', 'spuse_name', '')}</td>
                                    <td>{$formObj->getTBRow('HT', 'height', '')}</td>
                                    <td>{$formObj->getTBRow('Wt (in kgs)', 'weight', '')}</td>
                                    <td>{$formObj->getTBRow('BMI', 'bmi', '')}</td>
                                    <td colspan='3'>
                                        <div class='BPOthersBox'>{$formObj->getTBRow('BP', 'blood_pressure', '')}</div>
                                        <div class='BPOthersBox'>{$formObj->getTBRow('PR', 'pulse_rate', '')}</div>
                                        <div class='BPOthersBox'>{$formObj->getTBRow('RR', 'respiratory_rate', '')}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{$formObj->getTBRow('SpO2', 'SpO2', '')}</td>
                                    <td>{$formObj->getTBRow('Mobile', 'phone', '')}</td>
                                    <td colspan='2'>{$formObj->getTBRow('Address', 'address_street', '')}</td>
                                    <td>{$formObj->getTBRow('Town/City', 'address_area', '')}</td>
                                    <td>{$formObj->getTBRow('Temperature-°F', 'temperature', '')}</td>
                                </tr>
                                <tr>
                                    <td colspan='2'>
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
                  ,p.uhid
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
               {$appendSql}
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
                SELECT pv.patient_visit_id
                      ,pv.status
                FROM patient_visit pv
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
                    $patientVisitLink = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPatientVisit['patient_visit_id']}";
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
                    <td>{$rec['uhid']}</td>
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
                        <th>UHID</th>
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

        /*$recEmp = $fn->getRecordByCondition("employee", "staff_id = {$_SESSION['staff_id']}");
        $empCondition = '';
        if($recEmp['employee_id'] != ''){
            $empCondition = "AND ev.employee_id = {$recEmp['employee_id']}";
        }*/

        $empCondition = '';
        if($_SESSION['userGroupName'] != 'RECEPTIONIST' && $_SESSION['userGroupName'] != 'Nurse' && $_SESSION['userGroupName'] != 'Super Administrator' && $_SESSION['userGroupName'] != 'Administrator'){
            $recEmp       = $fn->getRecordByCondition("employee", "staff_id = {$_SESSION['staff_id']} AND status = 'Active'");
            $empCondition = "AND ev.employee_id = {$recEmp['employee_id']}";
        }

        $time = date('H:i');
        $timeCondition = '';
        /*if ($_SESSION['userGroupName'] == 'Administrator'){
            if($time < '15:30'){
                $timeCondition = "AND pv.check_up_time < '15:30:00'";
            }else{
                $timeCondition = "AND pv.check_up_time > '15:30:00'";
            }
        }*/

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
              ,p.uhid
              ,pv.visit_code
              ,pv.status
              ,pv.patient_visit_id
              ,ev.consultation_fees
        FROM patient_visit pv
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN employee_visit ev ON (ev.patient_visit_id = pv.patient_visit_id)
        WHERE pv.check_up_date = '{$currentDate}'
        AND pv.status != 'Cancelled'
        {$timeCondition}
        {$empCondition}
        {$appendSqlPV}
        {$appendSql}
        ORDER BY pv.status DESC, pv.patient_visit_id DESC
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $SQLCount = "
        SELECT count(pv.patient_visit_id) as  case_count
                ,SUM(ev.consultation_fees) as fees_total
        FROM patient_visit pv
        LEFT JOIN employee_visit ev ON (ev.patient_visit_id = pv.patient_visit_id)
        WHERE pv.check_up_date = '{$currentDate}'
        AND status != 'Cancelled'
        {$timeCondition}
        {$empCondition}
        {$appendSqlCount}
        ";

        $resultCount = $db->sql_query($SQLCount);
        $evRow       = $db->sql_fetchrow($resultCount);
        //$recCount = $fn->getRecordCount('patient_visit', "check_up_date = '{$currentDate}' AND status != 'Cancelled'");
        while($rec    = $db->sql_fetchrow($result)){
            $patientVisitLink = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rec['patient_visit_id']}";
            $createVisit = "<a class = 'button btn btn-default viewVisitRecord' href='{$patientVisitLink}'>
                                View Record
                            </a>
            ";

            $printToken   = "index.php?_topRm=main&module=hms_patientVisit&_spAction=printTokenForVisit&patient_information_id={$rec['patient_information_id']}&patient_visit_id={$rec['patient_visit_id']}&showHTML=0";

            $patient_Link = "index.php?_topRm=main&module=hms_patientInformation&_action=edit&patient_information_id={$rec['patient_information_id']}";
            $patient_name = "<a href='{$patient_Link}' target='_blank'><u>{$rec['patient_name']}</u></a>";

            $check_up_time = $rec['check_up_time'];

            $visit_code = '';
            if($rec['visit_code'] != ''){
                $visit_code = 'VST-'.$rec['visit_code'];
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

            $patSumCount = $fn->getRecordCount('patient_visit', "patient_information_id = '{$rec['patient_information_id']}' AND status != 'Cancelled'");
            $pastVisit = '';

            if($patSumCount > 1){
                $viewOverallSummaryLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=overallSummary&patient_visit_id={$rec['patient_visit_id']}&patient_information_id={$rec['patient_information_id']}&showHTML=0";

                $pastVisit="<a href='{$viewOverallSummaryLink}' class='viewOverallSummary'><u>Past Summary</u></a>";
            }
            $check_up_time = date('h:i:s a', strtotime($rec['check_up_time']));

            $printOpCard   = "index.php?_topRm=main&module=hms_patientVisit&_spAction=printOpCard&patient_information_id={$rec['patient_information_id']}&patient_visit_id={$rec['patient_visit_id']}&showHTML=0";

            $text .= " 
            <tr>
                <td><a href='{$printOpCard}' target='_blank'>{$rec['uhid']}</a></td>
                <td {$bgColorBalance}>{$visit_code}</td>
                <td {$bgColorBalance}>{$patient_name}</td>
                <td {$bgColorBalance}>{$rec['consultation_fees']}</td>
                <td {$bgColorBalance} class='txtCenter'>{$createVisit}</td>
                <td class='txtCenter'>{$pastVisit}</td>
                <td class='txtCenter'>{$check_up_time}</td>
                <td>{$rec['gender']}</td>
                <td class='txtCenter'>{$age}</td>
                <td>{$rec['status']}</td>
                <td>{$rec['address_area']}</td>
                <td>{$rec['phone']}</td>
                <td>
                    <a href='{$printToken}' target='_blank'>
                        <u>Print Token</u>
                    </a>
                </td>
            </tr>
            ";
        }

        if($numRows > 0){
            $resultRow = "
            <div class='searchResultLabel'>
                <label class=''>Please find below the number of patients visited today : {$evRow['case_count']} Patient(s) - Total Amount: {$evRow['fees_total']} Rs</label>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>UHID</th>
                    <th>Visit Code</th>
                    <th>Patient Name</th>
                    <th>Fees</th>
                    <th class='txtCenter'>Visit</th>
                    <th class='txtCenter'>Past Summary</th>
                    <th class='txtCenter'>Check Up Time</th>
                    <th>Gender</th>
                    <th class='txtCenter'>Age</th>
                    <th>Status</th>
                    <th>Town/City</th>
                    <th>Mobile</th>
                    <th>Token</th>
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

        $addDrLbl = "
        <li class='first'>
            <a href='#tabs-2'>Add Dr / Nurse</a>
        </li>
        ";

        $formActionAddDr = "index.php?module=hms_patientVisit&_spAction=addDoctorRecord&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
        $addDrTab = "
        <div id='tabs-2'>
            <div class='btn btn-info mb10'><a href='{$formActionAddDr}' id='addDoctorRecord' patient_visit_id={$row['patient_visit_id']}>Add Record</a></div>
            <div id='doctorDisplay'>{$this->getDoctorPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $medHisLbl = "
        <li class='first'>
            <a href='#tabs-1' class='chiefComplains'>Chief Complains</a>
        </li>
        ";

        $medHisTab = "
        <div id='tabs-1'>
            <div class='floatbox'>
                <div class='float_left complainTabClass'>
                    <div id='chiefComplains'>{$this->getChiefComplainsDisplay($row['patient_visit_id'])}</div>
                </div>
                <div class='float_left complainTabClass'>
                    <div id='summaryPortalDisplay'>{$this->getSummaryPortalDisplay($row['patient_visit_id'])}</div>
                </div>
                <div class='float_left'>
                    <div id='procedurePortal'>{$this->getProcedurePortalDisplay($row['patient_visit_id'])}</div>
                </div>
            </div>
        </div>
        ";

        $vitalSignsLbl = "
        <li class='first'>
            <a href='#tabs-9'>Vital Signs</a>
        </li>
        ";

        $vitalSignsTab = "
        <div id='tabs-9'>
            <div id='vitalSigns'>{$this->getVitalSignsDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $sysExamLbl = "
        <li class='first'>
            <a href='#tabs-10'>Systemic Examination</a>
        </li>
        ";

        $sysExamTab = "
        <div id='tabs-10'>
            <div id='sysExam'></div>
        </div>
        ";

        $medicineLbl = "
        <li class='first'>
            <a href='#tabs-5' class='medicines'>Medicines</a>
        </li>
        ";

        $viewPrescribeMedicineLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=prescribeMedicine&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
        $viewPrescribeMedicine = "<a href='{$viewPrescribeMedicineLink}' class='viewPrescribeMedicineRecord btn btn-info float_left'>Prescribe Medicine</a>";

        $medicineTab = "
        <div id='tabs-5'>
            <!--<div>
                <a href='#' class='btn btn-info float_left mb10' id='addMedicines' patient_visit_id={$row['patient_visit_id']}>
                    Add Record
                </a>
            </div>-->
            <div id='medicinesDisplay'>{$this->getMedicinesPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $medicalTestLbl = "
        <li class='first'>
            <a href='#tabs-6' class='investigations'>Investigations</a>
        </li>
        ";

        $medicalTestTab = "
        <div id='tabs-6'>
            <div id='medicalDisplay'>{$this->getMedicalPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $vaccinationLbl = "
        <li class='first'>
            <a href='#tabs-9' class='Vaccination'>Vaccination</a>
        </li>
        ";

        $vaccinationTab = "
        <div id='tabs-9'>
            <div id='vaccinationDisplay'>{$this->getVaccinationPortalDisplay($row['patient_visit_id'])}
            </div>
        </div>
        ";

        $summaryLbl = "
        <li class='first'>
            <a href='#tabs-8'>Provisional Diagnosis</a>
        </li>
        ";

        $summaryTab = "
        <div id='tabs-8'>
            <div id='summaryPortalDisplay'>{$this->getSummaryPortalDisplay($row['patient_visit_id'])}</div>
        </div>
        ";

        $dneReportLbl = "
        <li class='first'>
            <a href='#tabs-11' class='DneReport'>Dne Report (Throat)</a>
        </li>
        ";

        $dneReportTab = "
        <div id='tabs-11'>
            <div id='dneReportDisplay'>{$this->getDneReportPortalDisplay($row['patient_visit_id'])}
            </div>
        </div>
        ";

        $dneReportEarLbl = "
        <li class='first'>
            <a href='#tabs-12' class='DneReport'>Dne Report (Ear)</a>
        </li>
        ";

        $dneReportEarTab = "
        <div id='tabs-12'>
            <div id='dneReportDisplay'>{$this->getDneReportEarPortalDisplay($row['patient_visit_id'])}
            </div>
        </div>
        ";

        $dneReportNoseLbl = "
        <li class='first'>
            <a href='#tabs-13' class='DneReport'>Dne Report (Nose)</a>
        </li>
        ";

        $dneReportNoseTab = "
        <div id='tabs-13'>
            <div id='dneReportDisplay'>{$this->getDneReportNosePortalDisplay($row['patient_visit_id'])}
            </div>
        </div>
        ";

        $formPerioChart = "index.php?module=hms_patientVisit&_spAction=perioChartForm&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

        $search_List = "index.php?_topRm=main&module=hms_patientVisit&_action=searchlist";

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQLOrder ="
        SELECT order_id
        FROM `order`
        WHERE patient_visit_id = {$row['patient_visit_id']}
        AND order_type = 'OP'
        {$appendSql}
        ";
        $resultOrder  = $db->sql_query($SQLOrder);
        $numRowsOrder = $db->sql_numrows($resultOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        $SQLEmpVisit = "
        SELECT consultation_fees
               ,employee_visit_id
        FROM employee_visit
        WHERE patient_visit_id = {$row['patient_visit_id']}
        ORDER BY employee_visit_id ASC
        ";
        $resultEmpVisit = $db->sql_query($SQLEmpVisit);
        $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

        $sqlFees  = $fn->getValueListSQL('fees');
        
        $cancelVisit = '';
        $gotoOrder = '';
        $generateOrder = '';
        $invoicePortalDisplay = '';
        $actionButtons = '';
        $receiptPortalDisplay = '';
        $feesUpdateByLink = '';

        $feesRow = "{$formObj->getTBRow('Fees', 'consultation_fees', $rowEmpVisit['consultation_fees'])}";
        $resultFees = $db->sql_query($sqlFees);
        /*while($rowFees    = $db->sql_fetchrow($resultFees)){
            $feesUpdateByLink .= "<a class='feesUpdateForVisitRecord p5'>{$rowFees['value']}</a>";
        }*/

        $feesUpdateByLink = "<div class='ageBox feesBox'>{$feesUpdateByLink}</div>";
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
                    $generateOrder = "<a href='#' id='createOrderRecord' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-info'>Generate Bill</a>";
                    $cancelVisit = "<a patient_visit_id='{$row['patient_visit_id']}' class='btn btn-danger cancelVisitRecord'>Cancel Visit</a>";
                }
            }
            else{
                $billSummaryOrder = "index.php?module=hms_patientVisit&_spAction=summaryInOrder&order_id={$rowOrder['order_id']}&showHTML=0";
                $generateOrder = "<div class='billSummaryOrder float_left'><a class='btn btn-primary' href='{$billSummaryOrder}' id='billSummaryOrder' order_id='{$rowOrder['order_id']}'>Bill Summary</a></div>";

                $feesRow = "{$formObj->getTBRow('Fees', 'consultation_fees', $rowEmpVisit['consultation_fees'], $expNoEdit)}
                <input type='hidden' name='consultation_fees' value='{$rowEmpVisit['consultation_fees']}' />";
                
                $feesUpdateByLink = "";
            }

            $modObj = getCPModuleObj('hms_order');
            $invoicePortalDisplay =  $modObj->view->getInvoicePortalDisplay($rowOrder['order_id']);

            $formActionReceipt = "index.php?module=hms_order&_spAction=generateReceiptForm&order_id={$rowOrder['order_id']}&patient_information_id={$row['patient_information_id']}&patient_visit_id={$row['patient_visit_id']}&showHTML=0";

            $actionButtons ="
            <div class='btn btn-info mb5'>
                <a href='{$formActionReceipt}' id='generateReceipt'>CREATE RECEIPT</a>
            </div>
            ";
            $receiptPortalDisplay =  $modObj->view->getReceiptPortalDisplay($rowOrder['order_id']);
        }
        else{
            if($row['status'] != 'Cancelled'){
                $generateOrder = "<a href='#' id='createOrderRecord' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-info'>Generate Bill</a>";
            }

            if($row['status'] != 'Cancelled'){
                $cancelVisit = "<a patient_visit_id='{$row['patient_visit_id']}' class='btn btn-danger cancelVisitRecord'>Cancel Visit</a>";
            }
            
            $feesRow = $formObj->getTBRow('Fees', 'consultation_fees', $rowEmpVisit['consultation_fees']);
        }
        
        $printPrescription = '';
        $labReport = '';
        $labReqReport = '';
        $createAdmission = '';
        $dneReport = '';
        if($row['status'] != 'Cancelled'){
            $urlPrescription = "index.php?module=hms_patientVisit&_spAction=printPrescription&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
            $printPrescription = "<a href='{$urlPrescription}' id='printPrescription' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-info' target='_blank'>Print Prescription</a>";

            $urllabReport = "index.php?module=hms_patientVisit&_spAction=printLabReport&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
            $labReport = "<a href='{$urllabReport}' id='labReport' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-info' target='_blank'>Lab Report</a>";

            $urlDneReport = "index.php?module=hms_patientVisit&_spAction=printDneReport&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
            $dneReport = "<a href='{$urlDneReport}' id='dneReport' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-info' target='_blank'>DNE Report</a>";

            $urllabReqReport = "index.php?module=hms_patientVisit&_spAction=printLabRequestForm&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
            $labReqReport = "<a href='{$urllabReqReport}' id='labReport' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-info' target='_blank'>Lab Request Form</a>";

            $SQLInPat = "
            SELECT i.*
            FROM in_patient i
            WHERE i.patient_visit_id = {$row['patient_visit_id']}
            AND i.status != 'Cancelled'
            ";
            $resultInPat = $db->sql_query($SQLInPat);
            $numRowsInPat = $db->sql_numrows($resultInPat);
            $createAdmission = '';

            if($numRowsInPat == 0){
                $urlCreateAdmission = "index.php?module=hms_patientVisit&_spAction=createAdmission&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
                $createAdmission = "<a href='#' id='createAdmission' patient_visit_id='{$row['patient_visit_id']}' class='btn btn-primary'>Create Admission</a>";
            }
        }

        $gotoSearch = "
        <div class='floatbox editTopButtonActionDiv'>
            <div class='float_left'>
                {$generateOrder}
                {$printPrescription}
                {$labReqReport}
                {$labReport}
                {$gotoOrder}
                {$createAdmission}
                {$cancelVisit}
            </div>
            <div class='float_right createdModifiedEditTop'><b>Created By :</b> {$row['created_by']} on {$row['creation_date']}&nbsp;&nbsp;&nbsp;&nbsp;<b>Modified By:</b> {$row['modified_by']} {$row['modification_date']}</div>
        </div>";

        $SQLTreatment ="
        SELECT tv.*, t.title
        FROM `treatment_visit` tv
        LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
        WHERE tv.patient_visit_id = {$row['patient_visit_id']}
          AND tv.follow_up_date IS NOT NULL
        ORDER BY tv.follow_up_date
        ";
        $resultTreatment  = $db->sql_query($SQLTreatment);
        $treatmentTitle = '';
        while ($rowTv = $db->sql_fetchrow($resultTreatment)) {
            $follow_up_date = $fn->getCPDate($rowTv['follow_up_date'],"d-m-Y");
            $treatmentTitle .= $rowTv['title'] .' - '. $follow_up_date . '<br>';
        }

        $appendSqlPv = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPv = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$row['patient_information_id']}'
        {$appendSqlPv}
        ORDER BY check_up_date DESC
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $employee_id_count = '';
        $treatment = '';
        $employeeTitle = '';
        $PvText = '';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLEV = "
            SELECT Distinct ev.employee_id
                  ,(CASE 
                    WHEN e.salutation = 'Dr' 
                    THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                    ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                    END) AS employee_name
                  ,ev.patient_visit_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY employee_name
            ";
            $resultEv = $db->sql_query($SQLEV);
            $drAttended = '';
            while ($rowEv = $db->sql_fetchrow($resultEv)) {
                $drAttended .=$rowEv['employee_name'] . ', ';
            }
            $dr_attended = rtrim($drAttended,', ');

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY tv.treatment_id
            ";
            $resultTv = $db->sql_query($SQLTV);
            $pvTreatment = '';
            while ($rowTv = $db->sql_fetchrow($resultTv)) {
                $pvTreatment .=$rowTv['title'] . ', ';
            }
            $pv_treatment = rtrim($pvTreatment,', ');
            $check_up_date = $fn->getCPDate($rowPv['check_up_date'],"d-m-Y");

            $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$rowPv['patient_visit_id']}'");
            $total_invoice_amount = '0.00';
            $invoiced_Paid_Amount = '0.00';
            $balance_Amount = '0.00';

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
                        WHERE invHist.invoice_id =  i.invoice_id
                        AND r.receipt_status != 'Cancelled'
                        AND i.status != 'Cancelled'
                        ) as Amount_Paid
                     ,(SELECT SUM(inv.invoice_amount)
                        FROM invoice inv
                        WHERE inv.order_id = o.order_id AND
                        inv.status != 'Cancelled'
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
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }else{
                    $total_invoice_amount = $rowSql['total_invoice_amount'];
                    $invoiced_Paid_Amount = number_format($rowSql['Amount_Paid'], 2);
                    $balance_Amount = $total_invoice_amount - $rowSql['Amount_Paid'];
                    $balance_Amount = number_format($balance_Amount, 2);
                    $total_invoice_amount = number_format($total_invoice_amount, 2);
                }
            }

            $visit_code_Link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPv['patient_visit_id']}";
            $visit_codePVt = "<a href='{$visit_code_Link}' target='_blank'>VST- {$rowPv['visit_code']}</a>";

            $bgColorBalance = '';
            if($balance_Amount > 0){
                $bgColorBalance = "bgcolor='#BCFDFD'";
            }

            $PvText .= "
            <tr {$bgColorBalance}>
                <td>{$visit_codePVt}</td>
                <td>{$check_up_date}</td>
                <td>{$dr_attended}</td>
                <td>{$pv_treatment}</td>
                <td>{$total_invoice_amount}</td>
                <td>{$invoiced_Paid_Amount}</td>
                <td>{$balance_Amount}</td>
            </tr>
            ";
        }

        $visit_code = '';
        if($row['visit_code'] != ''){
            $visit_code = 'VST-'.$row['visit_code'];
        }

        $age = '';
        if($row['dob'] != ''){
            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
        }


        $appointmentRec = $fn->getRecordByCondition('appointment', "source_patient_visit_id = '{$row['patient_visit_id']}'");
        $createAppointmentLabel = 'Create Appointment';

        $appointmentDr_Linked = $appointmentRec['dr_Linked'];
        if($appointmentDr_Linked == ''){
            $SQLEmpVisit = "
            SELECT employee_id
            FROM employee_visit
            WHERE patient_visit_id = {$row['patient_visit_id']}
            ORDER BY employee_visit_id
            ";
            $resultEmpVisit = $db->sql_query($SQLEmpVisit);
            $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);
            $appointmentDr_Linked = $rowEmpVisit['employee_id'];
        }

        if(is_array($appointmentRec)){
            $createAppointmentLabel = 'Appointment Created';
        }

        $patient_Link = "index.php?_topRm=main&module=hms_patientInformation&_action=edit&patient_information_id={$row['patient_information_id']}";
        $patient_name = "<a href='{$patient_Link}' target='_blank'><u>{$row['patient_name']}</u></a>";

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND site_id = {$cpSiteIdSession}";
        }

        $sqlEmployee = "
        SELECT employee_id
              ,(CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE visits_patients = 1
        AND category = 'Doctor' OR category = 'Duty Doctor' OR category = 'Nurse'
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

        $sqlEmployeeReferral = "
        SELECT employee_id
              ,(CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE category = 'Doctor'
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

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

        $feesArray = array(
            "100"
           ,"150"
        );

        $expComp  = array('detailValue' => $row['company_name']);

        $categoryDDRLabel    = 'Client Name';
        $showHideForBillType = '';
        $showHideForAppointmentType = 'displayNone';
        if($row['bill_type'] == 'Company'){
            $categoryForDDR = 'Client';
            $showHideForBillType = 'displayNone';
            $showHideForAppointmentType = '';
        }elseif ($row['bill_type'] == 'Panel') {
            $categoryForDDR = $row['bill_type'];
            $categoryDDRLabel = 'Panel Name';
            $showHideForBillType = 'displayNone';
            $showHideForAppointmentType = '';
        }else{
            $categoryForDDR = $row['bill_type'];
        }

        $appendSqlComp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlComp = "AND site_id = {$cpSiteIdSession}";
        }

        $sqlCompany = "
        SELECT company_id
               ,company_name
        FROM company
        WHERE category = '{$categoryForDDR}'
        {$appendSqlComp}
        ORDER BY company_name
        ";

        $SQLEmpVisit = "
        SELECT consultation_fees
               ,employee_visit_id
        FROM employee_visit
        WHERE patient_visit_id = {$row['patient_visit_id']}
        ORDER BY employee_visit_id ASC
        ";
        $resultEmpVisit = $db->sql_query($SQLEmpVisit);
        $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

        $patSumCount = $fn->getRecordCount('patient_visit', "patient_information_id = '{$row['patient_information_id']}' AND status != 'Cancelled'");
        $pastVisit = '';

        if($patSumCount > 1){
            $viewOverallSummaryLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=overallSummary&patient_visit_id={$row['patient_visit_id']}&patient_information_id={$row['patient_information_id']}&showHTML=0";
            $pastVisit ="
            <a href='{$viewOverallSummaryLink}' class='viewOverallSummary ml20'><u>Past Summary</u></a>";
        }

        /*$age    = explode(".", $row['age']);
        $year = $age['0'];
        $month = $age['1'];*/
        $sqlGender      = $fn->getValueListSQL('gender');
        $check_up_date_pv = $fn->getCPDate($row['check_up_date'],"d-m-Y");

        $actual_weight = 0;
        $weightText = '';
        //class='blinking'
        if($row['age_year'] != ''){
            if($row['age_year'] >= 5 && $row['age_year'] <= 14){
                $age_actual =  intval($row['age_year']) + (intval($row['age_month'])/10);
                $actual_weight = 4 * $age_actual;
                $weightText = "<span>Weight Should Be : $actual_weight kg<b><span>";
            }
            else if($row['age_year'] >= 1 && $row['age_year'] < 5){
                $age_actual   =  intval($row['age_year']) + (intval($row['age_month'])/10);
                $actual_weight = 2 * ($age_actual + 5);
                $weightText = "<span>Weight Should Be : $actual_weight kg<b><span>";
            }
        }
        else if($row['age_month'] != '' && $row['age_year'] == ''){
            $actual_weight = ($row['age_month'] + 9) / 2;
            $weightText = "<span>Weight Should Be : $actual_weight kg<b><span>";
        }

        $formActionUpdatetown = "index.php?module=hms_patientVisit&_spAction=updateTown&patient_information_id={$row['patient_information_id']}&showHTML=0";

        $Updatetown = "<div class='townupdate'>
                    <a id='Updatetown' href='{$formActionUpdatetown}' patient_information_id='{$row['patient_information_id']}'>
                        Update Town
                    </a>
                </div>
                ";

        $printOpCard   = "index.php?_topRm=main&module=hms_patientVisit&_spAction=printOpCard&patient_information_id={$row['patient_information_id']}&patient_visit_id={$row['patient_visit_id']}&showHTML=0";
        $uhid = "<a href='{$printOpCard}' target='_blank'>{$row['uhid']}</a>";        

        $text = "
        {$gotoSearch}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Patient Visit Details</div>
                    <div class='float_left ml20'>{$row['employee_name']}</div>
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
                                <td width='5%'>
                                    <div class='mt15'>
                                        {$formObj->getTBRow('UHID', 'uhid', $uhid, $expNoEdit)}
                                    </div>
                                </td>
                                <td width='20%'>
                                    {$formObj->getTBRow('Name', 'name', $row['patient_name'])}
                                </td>
                                <td width='25%'>
                                    <div class='ageBox'>{$formObj->getTBRow('Age (Yrs)' , 'age_year', $row['age_year'])}</div>
                                    <div class='ageBox'>{$formObj->getTBRow('(Months)' , 'age_month', $row['age_month'])}</div>
                                    <div class='ageBox'>{$formObj->getTBRow('(Days)' , 'age_day', $row['age_day'])}</div>
                                </td>
                                <td width='10%'>
                                    {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVl)}
                                </td>
                                <td width='15%'>
                                    {$formObj->getTBRow('Mobile', 'patient_phone', $row['patient_phone'])}
                                </td>
                                <td width='15%'>
                                    {$formObj->getTBRow('Father Name', 'father_name', $row['father_name'])}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='15%'>{$formObj->getTBRow('Husband Name', 'spuse_name', $row['spuse_name'])}</td>
                                <td width='25%'>
                                <div class='townBox'>{$formObj->getTBRow('Town/City', 'address_area', $row['address_area'])}</div>
                                <div class='townBox'>{$Updatetown}</div>
                                </td>
                                <td width='15%'>{$formObj->getYesNoRRow('Dr Required', 'dr_required', $row['dr_required'], $expNoEdit)}</td>
                                <td width='10%'>{$formObj->getDDRowByArr('Status', 'status', $statusArray, $row['status'], $expNoEdit)}</td>
                                <td width='10%'>{$formObj->getDDRowBySQL('Choose Dr/Nurse', 'employee_id', $sqlEmployee, $row['employee_id'])}</td>
                            </tr>
                            <tr>
                                <td colspan='3'>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('HT', 'height', $row['height'])}</div>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('Wt (in kgs)', 'weight', $row['weight'])}</div>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('BMI', 'bmi', $row['bmi'])}</div>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('BP', 'blood_pressure', $row['blood_pressure'])}</div>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('PR', 'pulse_rate', $row['pulse_rate'])}</div>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('RR', 'respiratory_rate', $row['respiratory_rate'])}</div>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('SpO2', 'SpO2', $row['SpO2'])}</div>
                                <div class='ageBox tempBox'>{$formObj->getTBRow('Temp-°F', 'temperature', $row['temperature'])}</div>
                                <div class='ageBox tempBox w300'>{$feesRow}</div>
                                {$feesUpdateByLink}
                                <!-- <div class='ageBox tempBox '>{$formObj->getDDRowBySQL('Fees', 'consultation_fees', $sqlFees, $rowEmpVisit['consultation_fees'], $expVl)}</div> -->
                                </td>
                                <td class='notesTitle' width='15%' colspan='1'>{$formObj->getDateRow('Check up Date(YEAR-MONTH-DATE)', 'check_up_date', $row['check_up_date'])}</td>
                                <td width='10%'>{$formObj->getTimeRow('Check up Time', 'check_up_time', $row['check_up_time'])}</td>
                            </tr>
                            <tr>
                                <td width=''>{$formObj->getYesNoRRow('Registration Fees', 'register_fees', $row['register_fees'])}</td>
                                <td width='10%'>{$formObj->getDDRowBySQL('Refer to Dr', 'referral_doctor_id', $sqlEmployee, $row['referral_doctor_id'])}</td>
                                <td width=''>{$formObj->getYesNoRRow('Visited By Referral Dr', 'visited_by_referral_doctor', $row['visited_by_referral_doctor'])}</td>
                                <td width='15%' >{$formObj->getDateRow('Review Date(YEAR-MONTH-DATE)', 'review_date', $row['review_date'])}</td>
                                <td class='notesTitle' width='15%'>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                            </tr>
                            <tr>
                                <td class='notesTitle' width='15%'>{$formObj->getTARow('Previous Medical History', 'previous_medical_history', $row['previous_medical_history'])}</td>
                                <td class='notesTitle' width='15%'>{$formObj->getTARow('Allergy', 'allergy', $row['allergy'])}</td>
                                <td class='notesTitle' colspan='2'>{$formObj->getTARow('DNE Report', 'dne_report_notes', $row['dne_report_notes'])}</td>
                                <td width='15%'><b>{$weightText}<b></td>
                                <input type='hidden' name='employee_visit_id' value='{$rowEmpVisit['employee_visit_id']}' />
                                <input type='hidden' name='patient_visit_id' value='{$row['patient_visit_id']}' />
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            {$this->getOverallTabsSummary($row['patient_visit_id'])}
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
                            {$medHisLbl}
                            {$medicineLbl}
                            {$medicalTestLbl}
                            {$dneReportLbl}
                            {$dneReportEarLbl}
                            {$dneReportNoseLbl}
                        </ul>
                        {$medHisTab}
                        {$medicineTab}
                        {$medicalTestTab}
                        {$dneReportTab}
                        {$dneReportEarTab}
                        {$dneReportNoseTab}
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
    function getUpdateTown() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expNoEdit = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');
        $patient_information_id = $fn->getReqParam('patient_information_id');


        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=updateTownSubmit&showHTML=0";
        
        $SQLTown="
        SELECT p.address_area
        FROM patient_information p
        WHERE patient_information_id = '{$patient_information_id}'
        ";
        $resultTown   = $db->sql_query($SQLTown);
        $rowTown = $db->sql_fetchrow($resultTown);
        
        $text = "
        <form id='updateTownPortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Existing Name', 'address_area', $rowTown['address_area'], $expNoEdit)}
            {$formObj->getTBRow('Update Town', 'address_area_update')}
            <input type='hidden' name='patient_information_id' value='{$patient_information_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getPrescribeMedicine() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $prescribe_medicine_id  = $fn->getReqParam('prescribe_medicine_id');

        $SQLMV ="
        SELECT mv.*
        FROM `medicines_visit` mv
        WHERE mv.title = '{$rowPM['medicine_name']}'
        ";
        $resultMV  = $db->sql_query($SQLMV);
        $numRowsMedvisit = $db->sql_numrows($resultMV);
        
        $rows = '';
        $count = 1;
        while($rowMV = $db->sql_fetchrow($resultMV)){
            $add = "index.php?_topRm=main&module=hms_patientVisit&_spAction=prescribeMedicineFormSubmit&prescription_id={$rowPM['prescription_id']}&patient_visit_id={$rowPM['patient_visit_id']}&showHTML=0";

            $SQLPM = "
            SELECT pm.*
            FROM prescribe_medicine pm
            ";
            $resultPM  = $db->sql_query($SQLPM);
            $rowPM     = $db->sql_fetchrow($resultPM);

            if($numRowsMedvisit > 0){
                $addremove = "<a href='#' class='removemedicine' prescribe_medicine_id={$rowPM['prescribe_medicine_id']} medicines_visit_id={$rowMV['medicines_visit_id']}><u>Remove</u></a>";
            } else {
                $addremove = "<a href='#' class='addmedicine' prescribe_medicine_id={$rowPM['prescribe_medicine_id']}><u>Add</u></a>";                    
            }

            $rows .= "
            <tr>
                <td>{$rowPM['medicine_name']}</td>
                <td>{$rowPM['dosage']}</td>
                <td>{$rowPM['instruction']}</td>
                <td>{$rowPM['days']}</td>
                <td>{$addremove}</td>
            </tr>
            ";
        }

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Dosage</th>
                    <th>Instruction</th>
                    <th>No of Days</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
            <input type='hidden' id='patient_visit_id' value='{$patient_visit_id}'>

        </table>
        ";

        return $text;
    } 
    /**
     *
     */
    function getDiseaseList() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $prescription_id  = $fn->getReqParam('prescription_id');

        $SQLPM="
        SELECT  p.prescription_id
               ,p.disease_name
        FROM prescription p
        ORDER BY p.disease_name ASC
        ";
        $resultPM   = $db->sql_query($SQLPM);
        
        $rows = '';
        $count = 0;
        while($rowPM = $db->sql_fetchrow($resultPM)){

            $SQLPatientVisit = "
            SELECT pv.diagnosis, pv.patient_information_id
            FROM patient_visit pv
            WHERE pv.patient_visit_id = '{$patient_visit_id}'
            ";
            $resultPv = $db->sql_query($SQLPatientVisit);
            $rowPv    = $db->sql_fetchrow($resultPv);

            if($rowPv['diagnosis'] != ""){
                $diagnosis    = explode(", ", $rowPv['diagnosis']);
                sort($diagnosis);
                $diagnosisLen = count($diagnosis);

                $checkboxCheck = "";
                for($i=0; $i<$diagnosisLen; $i++){
                    if($rowPM['disease_name'] == $diagnosis[$i]){
                        $checkboxCheck = "Checked = 'Checked'";
                    }
                }

                $checkbox = "
                <input type='checkbox' {$checkboxCheck} class='addmedicinevisit' name='disease_name' prescription_id='{$rowPM['prescription_id']}' patient_visit_id='{$patient_visit_id}' disease_name='{$rowPM['disease_name']}' patient_information_id='{$rowPv['patient_information_id']}'>";
            }
            else{
                $checkbox = "
                <input type='checkbox' class='addmedicinevisit' name='disease_name' prescription_id='{$rowPM['prescription_id']}' patient_visit_id='{$patient_visit_id}' disease_name='{$rowPM['disease_name']}' patient_information_id='{$rowPv['patient_information_id']}'>";                

            }

                        
            $rows .= "
            <tr>
                <td class='txtCenter'>{$checkbox}</td>
                <td>{$rowPM['disease_name']}</td>
            </tr>
            ";
            $count++;
        }

        $text = "
        <form id='portalFormDiseaseList' class='yform columnar' method='post'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th width='20%' class='txtCenter'>Check</th>
                        <th width='80%'>Diagnosis Name</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
                <input type='hidden' id='patient_visit_id' value='{$patient_visit_id}'>

            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getComplainList() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

        $SQLPM="
        SELECT  p.complain_id
               ,p.title
        FROM complain p
        ORDER BY p.title ASC
        ";
        $resultPM   = $db->sql_query($SQLPM);
        
        $rows = '';
        $count = 0;
        while($rowPM = $db->sql_fetchrow($resultPM)){

            $SQLPatientVisit = "
            SELECT pv.complain, pv.patient_information_id
            FROM patient_visit pv
            WHERE pv.patient_visit_id = '{$patient_visit_id}'
            ";
            $resultPv = $db->sql_query($SQLPatientVisit);
            $rowPv    = $db->sql_fetchrow($resultPv);

            if($rowPv['complain'] != ""){
                $complain    = explode(", ", $rowPv['complain']);
                sort($complain);
                $complainLen = count($complain);

                $checkboxCheck = "";
                for($i=0; $i<$complainLen; $i++){
                    if($rowPM['title'] == $complain[$i]){
                        $checkboxCheck = "Checked = 'Checked'";
                    }
                }

                $checkbox = "
                <input type='checkbox' {$checkboxCheck} class='addmedicinevisit' name='complain_title' complain_id='{$rowPM['complain_id']}' patient_visit_id='{$patient_visit_id}' title='{$rowPM['title']}' patient_information_id='{$rowPv['patient_information_id']}'>";
            }
            else{
                $checkbox = "
                <input type='checkbox' class='addmedicinevisit' name='complain_title' complain_id='{$rowPM['complain_id']}' patient_visit_id='{$patient_visit_id}' title='{$rowPM['title']}' patient_information_id='{$rowPv['patient_information_id']}'>";                

            }

                        
            $rows .= "
            <tr>
                <td class='txtCenter'>{$checkbox}</td>
                <td>{$rowPM['title']}</td>
            </tr>
            ";
            $count++;
        }

        $text = "
        <form id='portalFormDiseaseList' class='yform columnar' method='post'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th width='20%' class='txtCenter'>Check</th>
                        <th width='80%'>Complain Title</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
                <input type='hidden' id='patient_visit_id' value='{$patient_visit_id}'>

            </table>
        </form>
        ";

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
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $appendSqlPV = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            //$appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$patient_information_id}'
        AND pv.status != 'Cancelled'
        ORDER BY check_up_date DESC
        LIMIT 0,10
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $employee_id_count = '';
        $treatment = '';
        $employeeTitle = '';
        $PvText = '';
        $balance_Amount = '0.00';
        $overall_balance_Amount = '0.00';
        $sitePrefix = '';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $rowPv['site_id']);
                $sitePrefix = substr($siteRec['title'],0,3); 
            }
            $SQLEV = "
            SELECT Distinct ev.employee_id
                  ,(CASE 
                    WHEN e.salutation = 'Dr' 
                    THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                    ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                    END) AS employee_name
                  ,ev.patient_visit_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.patient_visit_id = '{$rowPv['patient_visit_id']}'
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
            ,mt.notes
            FROM medical_test_visit mt
            WHERE mt.patient_visit_id = '{$rowPv['patient_visit_id']}'
            ";
            $result1 = $db->sql_query($SQL1);
            $labTest = '';
            while ($rowTv = $db->sql_fetchrow($result1)) {
                $labTest .= $rowTv['title'] . '-' . $rowTv['notes'] .  ', ';
            }
            $labTest = rtrim($labTest,', ');
            $labTest = 'BP - '.$rowPv['blood_pressure'].'<br>'.$labTest;

            $SQL2 = "
            SELECT mt.*
            FROM medicines_visit mt
            WHERE mt.patient_visit_id = '{$rowPv['patient_visit_id']}'
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
            $check_up_date = $check_up_date . ' '. $rowPv['check_up_time'];
            $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$rowPv['patient_visit_id']}' AND order_type = 'OP'");
            $total_invoice_amount = '0.00';
            $invoiced_Paid_Amount = '0.00';

            if($orderRec['order_id'] != ''){
                $subSqlForPercentSum = "
                SELECT SUM(ini.unit_price) AS total_invoice_amount
                       ,inv.discount 
                FROM `invoice`inv
                LEFT JOIN invoice_item ini ON (ini.invoice_id = inv.invoice_id)
                WHERE inv.order_id = {$orderRec['order_id']}
                AND inv.status != 'Cancelled'
                AND ini.record_type = 'Doctor/Nurse'
                ";
                $resultSubSql = $db->sql_query($subSqlForPercentSum);
                $rowSql       = $db->sql_fetchrow($resultSubSql);

                $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
                $total_invoice_amount = number_format($total_invoice_amount, 2);
            }

            $visit_code_Link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPv['patient_visit_id']}";
            $visit_codePVt = "<a href='{$visit_code_Link}' target='_blank'>VST- {$rowPv['visit_code']}</a>";

            $viewSummaryTreatmentLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=viewSummaryTreatment&patient_visit_id={$rowPv['patient_visit_id']}&showHTML=0";
            $viewSummaryTreatment = "<a href='{$viewSummaryTreatmentLink}' class='viewSummaryForTreatmentRecord'><u>View Summary</u></a>";

            //$applyMedicineLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=applyMedicine&patient_visit_id={$rowPv['patient_visit_id']}&patient_visit_id_main={$patient_visit_id}&showHTML=0";
            $applyMedicine = '';
            if($medTest != '') {
                $applyMedicine = "<a href='#' class='btn btn-info applyForMedicineRecord' patient_visit_id={$rowPv['patient_visit_id']} patient_visit_id_main={$patient_visit_id}>Apply</a>";
            }

            $PvText .= "
            <tr>
                <td width='10%'>{$check_up_date}-({$sitePrefix})</td>
                <td width='10%'>{$visit_codePVt}</td>
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
                                <th class=''>Code</td>
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
     *
     */
    function getVaccinationSchedule(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        SELECT value AS group_name
        FROM valuelist
        WHERE key_text = 'investigationGroup'
          AND value != 'Vaccination'
        ORDER BY sort_order
        ";
        $result   = $db->sql_query($SQL);
        $PvText = '';
        while ($row = $db->sql_fetchrow($result)) {
            $SQL = "
            SELECT m.medical_test_id
                  ,m.title
                  ,m.fees
            FROM medical_test m
            LEFT JOIN medical_test_group mtg ON (mtg.medical_test_id = m.medical_test_id)
            WHERE m.category = 'Vaccination'
              AND mtg.group_name = '{$row['group_name']}'
            ORDER BY mtg.group_name
            ";
            $result1   = $db->sql_query($SQL);
            $vaccination = '';
            $fees = '';
            while ($row1 = $db->sql_fetchrow($result1)) {
                $inputRow = "<input class='vaccinationId' type='checkbox' name='vaccinationId[]' value='{$row1['fees']}'>";
                $vaccination .="{$inputRow} {$row1['title']} <br/>";
                $fees .="{$row1['fees']} <br/>";
            }

            $PvText .= "
            <tr>
                <td>{$row['group_name']}</td>
                <td>{$vaccination}</td>
                <td class='fees txtRight'>{$fees}</td>
            </tr>
            ";
        }
        $expNoEdit = array('isEditable' => 0);

        $text = "
        <div class=''>
            <div>
                <div class='patientVisitVaccinationSchedule'>
                    <div class='fees_amount totalFreeze'>{$formObj->getTBRow('Total : ', 'fees_amount', '', $expNoEdit)}</div>                    
                    <table class='thinlist mb20 overallSummaryPortal'>
                        <thead>
                            <tr>
                                <th class=''>Age Group</td>
                                <th class=''>Vaccination</td>
                                <th class=''>MRP</td>
                            </tr>
                        </thead>
                        <tbody>
                            {$PvText}
                        </tbody>
                    </table>
                    <div class='fees_amount'>{$formObj->getTBRow('Total : ', 'fees_amount', '', $expNoEdit)}</div>                    
                </div>
            </div>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getPatientVisitSummaryPortal($patient_information_id=''){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $site_id = $fn->getSessionParam('cp_site_id');

        if($patient_information_id == ''){
            $patient_information_id = $fn->getReqParam('patient_information_id');
        }

        $patientVisitSummary_type_val = $fn->getReqParam('patientVisitSummary_type');

        if($patientVisitSummary_type_val == ''){
            $patientVisitSummary_type_val = 'Due';
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND pv.site_id = {$site_id}";
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$patient_information_id}'
        AND pv.status != 'Cancelled'
        {$appendSql}
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
                  ,ev.patient_visit_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY employee_name
            ";
            $resultEv = $db->sql_query($SQLEV);
            $drAttended = '';
            while ($rowEv = $db->sql_fetchrow($resultEv)) {
                $drAttended .=$rowEv['employee_name'] . ', ';
            }
            $dr_attended = rtrim($drAttended,', ');

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY tv.treatment_id
            ";
            $resultTv = $db->sql_query($SQLTV);
            $pvTreatment = '';
            while ($rowTv = $db->sql_fetchrow($resultTv)) {
                $pvTreatment .=$rowTv['title'] . ', ';
            }
            $pv_treatment = rtrim($pvTreatment,', ');
            $check_up_date = $fn->getCPDate($rowPv['check_up_date'],"d-m-Y");

            $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$rowPv['patient_visit_id']}'");
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

            $visit_code_Link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPv['patient_visit_id']}";
            $visit_codePVt = "<a href='{$visit_code_Link}' target='_blank'>VST- {$rowPv['visit_code']}</a>";

            $viewSummaryTreatmentLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=viewSummaryTreatment&patient_visit_id={$rowPv['patient_visit_id']}&showHTML=0";
            $viewSummaryTreatment = "<a href='{$viewSummaryTreatmentLink}' class='viewSummaryForTreatmentRecord'><u>View Summary</u></a>";

            $bgColorBalance = '';
            if($balance_Amount > 0){
                $bgColorBalance = "bgcolor='#BCFDFD'";
            }

            if($patientVisitSummary_type_val == 'Due'){
                if($balance_Amount > 0){
                    $PvText .= "
                    <tr {$bgColorBalance}>
                        <td>{$visit_codePVt}</td>
                        <td>{$check_up_date}</td>
                        <td>{$dr_attended}</td>
                        <td>{$viewSummaryTreatment}</td>
                        <td>{$total_invoice_amount}</td>
                        <td>{$invoiced_Paid_Amount}</td>
                        <td>{$balance_Amount}</td>
                    </tr>
                    ";
                }
            }else{
                $PvText .= "
                <tr {$bgColorBalance}>
                    <td>{$visit_codePVt}</td>
                    <td>{$check_up_date}</td>
                    <td>{$dr_attended}</td>
                    <td>{$viewSummaryTreatment}</td>
                    <td>{$total_invoice_amount}</td>
                    <td>{$invoiced_Paid_Amount}</td>
                    <td>{$balance_Amount}</td>
                </tr>
                ";
            }
        }


        //<div class='float_left patientVisitSummary_Filter'>{$formObj->getDDRowByArr('Display payment due records', 'patientVisitSummary_type', $patientVisitSummary_type, $patientVisitSummary_type_val)}</div>
        $linkDisplayText = "Display payment due records";
        if($patientVisitSummary_type_val == 'Due'){
            $linkDisplayText = "Show All Records";
        }

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Patient Visit History</div>
                    <div class='float_left InvoiceToggleHeading'>- Overall Due : {$overall_balance_Amount}</div>
                    <div class='float_left patientVisitSummary_Filter'><a href='#' class='patientVisitSummary_type'>{$linkDisplayText}</a></div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper patientVisitSummaryPortal'>
                    <table class='thinlist mb20 visitSummary'>
                        <thead>
                            <tr>
                                <th class='label'>Visit Code</td>
                                <th class='label'>Date</td>
                                <th class='label'>Dr Attended</td>
                                <th class='label'>Treatment</td>
                                <th class='label'>Total Amount</td>
                                <th class='label'>Paid</td>
                                <th class='label'>Balance</td>
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
     *
     */
    function getviewSummaryTreatment() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
                  ,tv.notes AS treatment_notes
                  ,pv.notes
                  ,pv.complain
                  ,pv.treatment_summary                  
                  ,mv.title AS medicines_name
                  ,CONCAT_WS(', ', mv.dosage, mv.days) AS medicines_desc
                  ,mv.instruction
                  ,mv.days
                  ,mv.qty
                  ,mv.dosage
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = tv.patient_visit_id)
            LEFT JOIN (medicines_visit mv) ON (mv.patient_visit_id = pv.patient_visit_id)
            WHERE tv.patient_visit_id = '{$patient_visit_id}'
            ";
            $resultTv = $db->sql_query($SQLTV);
            $rows = '';
            $count = 1;
            while($rowTv = $db->sql_fetchrow($resultTv)){
                $rows .= "
                <tr height='30px'>
                    <td><b>Main Notes</b></td>
                    <td>{$rowTv['notes']}</td>
                </tr>
                <tr height='30px'>
                    <td><b>Treatment</b></td>
                    <td></td>
                </tr>
                <tr height='30px'>
                    <td>{$rowTv['title']}</td>
                    <td>{$rowTv['treatment_notes']}</td>
                </tr>
                <tr height='30px'>
                    <td><b>Medicines</b></td>
                    <td></td>
                </tr>
                <tr height='30px'>
                    <td>{$rowTv['medicines_name']}</td>
                    <td>{$rowTv['medicines_desc']} days</td>
                </tr>
                <tr height='30px'>
                    <td><b>Summary</b></td>
                    <td></td>
                </tr>
                <tr height='30px'>
                    <td>Complain</td>
                    <td>{$rowTv['complain']}</td>
                </tr>
                <tr height='30px'>
                    <td>Treatment Summary</td>
                    <td>{$rowTv['treatment_summary']}</td>
                </tr>
                ";
            }

        $text = "
        <table class='thinlist'>
            <thead>
                <tr height='30px'>
                    <th>Title</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }
    /**
     *
     */
    function getOverallTabsSummary($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $text= '';

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }
        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$patientVisitRec['patient_information_id']}'
        {$appendSql}
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $PvText = '';
        $MhText = '';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLMH = "
            SELECT mh.medical_history_information_id
                  ,mh.title
                  ,mh.patient_visit_id
            FROM medical_history_information mh
            WHERE mh.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY mh.medical_history_information_id
            ";
            $resultMh = $db->sql_query($SQLMH);
            $pvMedHis = '';
            while ($rowMh = $db->sql_fetchrow($resultMh)) {
                $pvMedHis .=$rowMh['title'] . '<br> ';
            }
            $MhText .= "
            <div class=''>{$pvMedHis}</div>
            ";

            $SQLTV = "
            SELECT tv.treatment_id
                  ,t.title
                  ,tv.patient_visit_id
            FROM treatment_visit tv
            LEFT JOIN (treatment t) ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY tv.treatment_id
            ";
            $resultTv = $db->sql_query($SQLTV);
            $pvTreatment = '';
            while ($rowTv = $db->sql_fetchrow($resultTv)) {
                $pvTreatment .=$rowTv['title'] . '<br> ';
            }
            $PvText .= "
            <div class=''>{$pvTreatment}</div>
            ";
        }
        /*$text="
        {$pvMedHis}
        {$PvText}
        ";*/

        return $text;
    }

    /**
     *
     */
    function getDoctorPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT ev.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM employee_visit ev
        LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ORDER BY ev.employee_visit_id
        ";
        $result   = $db->sql_query($SQL);

        while ($rowEV = $db->sql_fetchrow($result)) {

            if($rowEV['employee_id'] == ""){
                $rowEV['employee_name'] = "Nurse";
                $rowEV['category']      = "Nurse";
            }

            $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editDoctorRecord&showHTML=0&patient_visit_id={$rowEV['patient_visit_id']}&employee_visit_id={$rowEV['employee_visit_id']}";
            $editRow = "<td><a href='{$editURL}' id='editDoctorRecord' patient_visit_id={$rowEV['patient_visit_id']}><u>Edit</u></a></td>";
            $rows .= "
            <tr>
                <td>{$rowEV['category']}</td>
                <td>{$rowEV['employee_name']}</td>
                <td>{$rowEV['consultation_fees']}</td>
                <td>{$rowEV['consultation_room']}</td>
                <td>{$rowEV['notes']}</td>
                {$editRow}
                <td><a href='#' class='deleteDoctorRecord' employee_visit_id='{$rowEV['employee_visit_id']}' patient_visit_id={$rowEV['patient_visit_id']}><u>Delete</u></a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Dr/Nurse</th>
        <th>Name</th>
        <th>Consulting Fees</th>
        <th>Room</th>
        <th>Notes</th>
        <th>Edit</th>
        <th>Delete</th>
        </tr>
        ";

        $text = "
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedicinesPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $cpUtil = Zend_Registry::get('cpUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";
        $stock = '';

        $sqlInstruction = $fn->getValueListSQL('instruction');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT mv.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM medicines_visit mv
        LEFT JOIN (employee e) ON (e.employee_id = mv.employee_id)
        WHERE mv.patient_visit_id = {$patient_visit_id}
        ORDER BY mv.medicines_visit_id
        ";
        $result   = $db->sql_query($SQL);

        $SQLRoute      = $fn->getValueListSQL('route');
        $SQLdosage      = $fn->getValueListSQL('dosage');

        $foodArray = array('Before', 'After');

        while ($rowMV = $db->sql_fetchrow($result)) {
            $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editMedicineRecord&showHTML=0&patient_visit_id={$rowMV['patient_visit_id']}&medicines_visit_id={$rowMV['medicines_visit_id']}";
            $editRow = "<td><a href='{$editURL}' id='editMedicineRecord' patient_visit_id={$rowMV['patient_visit_id']}>Edit</a></td>";
                /*<td class='title'>
                    <input type='text' value='{$rowMV['title']}' name='title' disabled>
                </td>*/
            $medicine_Link = "index.php?_topRm=utils&module=hms_product&_action=edit&product_id={$rowMV['product_id']}";            
            $rows .= "
            <tr recid='{$rowMV['medicines_visit_id']}' product_id='{$rowMV['product_id']}' class='portal-row2 row-hms_patientVisit__hms_product'>
                <td class='employee_id'>
                    <select name='employee_id'>
                        <option value=''>Please Select</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlDoctor, $rowMV['employee_id'])}
                    </select>
                </td>
                <td class='title'>
                    <a href='{$medicine_Link}' target='_blank'><u>{$rowMV['title']}</u></a>
                </td>
                <td class='route'>
                    <select name='route' >
                        <option value=''>Select</option>
                            {$dbUtil->getDropDownFromSQLCols1($db, $SQLRoute, $rowMV['route'])}
                    </select>
                </td>
                <td class='dosage'>
                    <!--<select name='dosage'>
                        <option value=''>Select</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $SQLdosage, $rowMV['dosage'])}
                    </select>-->
                    <input type='text' value='{$rowMV['dosage']}' name='dosage'>
                </td>
                <td class='qty'>
                    <input type='text' value='{$rowMV['qty']}' name='qty'>
                </td>
                <td class='instruction'>
                    <select name='instruction'>
                        <option value=''>Please Select</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlInstruction, $rowMV['instruction'])}
                    </select>
                </td>
                <td class='days'>
                    <input type='text' value='{$rowMV['days']}' name='days'>
                </td>
                <td class='AfBfFood'>
                    <select name='af_bf_food'>
                        <option value=''>Select</option>
                        {$cpUtil->getDropDown1($foodArray, $rowMV['af_bf_food'])}
                    </select>
                </td>
                <td><a href='#' class='deleteMedicineRecord' medicines_visit_id='{$rowMV['medicines_visit_id']}' patient_visit_id={$rowMV['patient_visit_id']}><u>Delete</u></a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th width='8%'>Dr</th>
            <th width='28%'>Medicine Name</th>
            <th width='7%'>Route</th>
            <th width='10%'>Dosage</th>
            <th width='5%'>Qty</th>
            <th width='18%'>Instruction</th>
            <th width='5%'>Days</th>
            <th width='11%'>Food</th>
            <th width='8%'>Delete</th>
        </tr>
        ";

        $text = "
        <form></form>
        <div class='addExistingMedicine float_left mb10'>
            <input type='text' value='' id='fld_product_title' class='text' name='product_title' placeholder='Add Existing Medicine' patient_visit_id={$patient_visit_id}>
        </div>
        <!--<div class='addNewMedicine float_left'>
            <input type='text' value='' id='fld_product_title' class='text' name='product_title_new' placeholder='Add New Medicine'>
        </div> 
        <div class='float_left'>
            <input class='btn btn-info newProductTitle' type='button' value='Create' name='portalForm' />
        </div>
        -->
        <div class='float_left'>
            <input class='btn btn-warning medicineSave' type='button' value='Save' name='portalForm' />
        </div>
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getMedicalPortalDisplayBackup($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=MedicalTestRecordSubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $SQLMedicalTest = "
        (SELECT m.medical_test_id
              ,m.title
              ,m.fees
        FROM medical_test m
        LEFT JOIN (medical_test_visit mv) ON (mv.medical_test_id = m.medical_test_id)
        WHERE patient_visit_id = {$patient_visit_id}
        )
        UNION
        (SELECT m.medical_test_id
              ,m.title
              ,m.fees
        FROM medical_test m
        )
        ";

        $result   = $db->sql_query($SQLMedicalTest);
        $count = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $medTestVisitRec = $fn->getRecordByCondition('medical_test_visit', "patient_visit_id = '{$patient_visit_id}' AND title='{$row['title']}'");

            if($medTestVisitRec['medical_test_visit_id'] != ''){
                $checked = "checked='checked'";
                $class ="";
            } else {
                $checked = '';
                $class ="displayNone";
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

                    /*<div class='hideTreatmentDetails_{$row['title']}_{$count} hideLabDetails {$class} labVisitNotes'>
                        <input type='text' value='{$fees}' id='fld_fees' class='text mt10 mb10' name='fees[]'>
                        <div><a href='#' class='addNoteLab'>{$notes}</a></div>
                        <div class='hideNotesLab'>
                            <div class='type-text ym-fbox-text row_notes'>
                                <textarea id='fld_notes' name='notes[]'>{$medTestVisitRec['notes']}</textarea>
                            </div>
                        </div>
                    </div>*/
            
            $inputRow .= "
            <div class='c20l'>
                <div class='type-check ym-fbox-check labTestBox'>
                    <input type='checkbox' id='title_{$count}' {$checked} value='{$row['title']}_{$count}' name='title[]' class='labTitle'>
                    <label for='title_{$count}'>{$row['title']}</label>
                    <div>
                        <label>Fees</label>
                        <input type='text' value='{$fees}' id='fld_fees' class='text mt10 mb10' name='fees[]'>
                    </div>
                    <label>Result</label>
                    <div class='type-text ym-fbox-text row_notes'>
                        <textarea id='fld_notes' name='notes[]'>{$medTestVisitRec['notes']}</textarea>
                    </div>
                    <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id[]' />
                </div>
            </div>
            ";
            $count ++;
        }

        $patientVisitRec = $fn->getRecordByCondition('patient_visit', "patient_visit_id = '{$patient_visit_id}'");
        $text = "
        <div id='' class=''>
        <form></form>
            <form id='portalForm_medicalTestDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='floatbox'>{$inputRow}</div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getVaccinationPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }
        $patientVisitRec = $fn->getRecordByCondition('patient_visit', "patient_visit_id = '{$patient_visit_id}'");
        $patient_information_id   = $patientVisitRec['patient_information_id'];

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=vaccinationRecordSubmit&showHTML=0";
        $rows  = "";
        $catRows = '';
        $catLinks = '';
        $count = 0;

        /*$SQLMedicalTest = "
        SELECT m.medical_test_id
              ,m.title
              ,m.fees
              ,mtg.group_name
        FROM medical_test m
        LEFT JOIN medical_test_group mtg ON (mtg.medical_test_id = m.medical_test_id)
        WHERE m.category = 'Vaccination'
        GROUP BY mtg.group_name
        ORDER BY m.medical_test_id, mtg.group_name
        ";*/
        $SQLMedicalTest = "
        SELECT value AS group_name
        FROM valuelist
        WHERE key_text = 'investigationGroup'
          AND value != 'Vaccination'
        ORDER BY sort_order
        ";
        $result   = $db->sql_query($SQLMedicalTest);

        $inputRow  = "";
        $title ='';
        $groupName = '';
        while ($row = $db->sql_fetchrow($result)) {
            $SQL = "
            SELECT m.medical_test_id
                  ,m.title
                  ,m.fees
                  ,mtg.group_name
            FROM medical_test m
            LEFT JOIN medical_test_group mtg ON (mtg.medical_test_id = m.medical_test_id)
            WHERE m.category = 'Vaccination'
              AND mtg.group_name = '{$row['group_name']}'
            ORDER BY mtg.group_name
            ";
            $result1   = $db->sql_query($SQL);
            $class = '';
            while ($row1 = $db->sql_fetchrow($result1)) {
                $medTestVisitRec = $fn->getRecordByCondition('vaccination_visit', "patient_information_id = '{$patient_information_id}' AND title='{$row1['title']}' AND group_name='{$row['group_name']}'");
                if($medTestVisitRec['vaccination_visit_id'] != ''){
                    $class = 'highlightGroupName';
                }
            }

            if($groupName != $row['group_name']){
                $title .= "<a href='#{$row['group_name']}' class='mr20 vaccinationName {$class}' patient_visit_id = '{$patient_visit_id}'>{$row['group_name']}</a>";
            }

            $count ++;
            $groupName = $row['group_name'];
        }
        $catRows = "
        <div class='panel panel-default'>
            <div class='panel-body'><div class='floatbox col-md-8 col-sm-8'><div class='vaccinationPortalDetail'></div></div></div>
        </div>
        ";
        $catLinks = "{$title}";

        $patientVisitRec = $fn->getRecordByCondition('patient_visit', "patient_visit_id = '{$patient_visit_id}'");

        $summaryLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=summaryVaccination&showHTML=0&patient_visit_id={$patient_visit_id}";
        $summary = "<a href='{$summaryLink}' class='summaryForVaccination'><u>Summary</u></a>";

        $text = "
        <div id='' class=''>
            <form id='portalForm_vaccinationDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <a class='vaccinationMainSubmit btn btn-info ml20'>Save</a>
                {$summary}
                <div>{$catLinks}</div>
                <div class='floatbox'>{$catRows}</div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getVaccinationPortalDetailDisplay($group_name='', $patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');

        if($group_name == ''){
            $group_name = $fn->getReqParam('group_name');
        }
        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $patientVisitRec = $fn->getRecordByCondition('patient_visit', "patient_visit_id = '{$patient_visit_id}'");
        $patient_information_id   = $patientVisitRec['patient_information_id'];

        $rows  = "";
        $catRows = '';
        $catLinks = '';
        $count = 0;

        $SQLMedicalTest = "
        SELECT m.medical_test_id
              ,m.title
              ,m.fees
              ,mtg.group_name
        FROM medical_test m
        LEFT JOIN medical_test_group mtg ON (mtg.medical_test_id = m.medical_test_id)
        WHERE m.category = 'Vaccination'
        AND mtg.group_name = '{$group_name}'
        ORDER BY m.medical_test_id, mtg.group_name
        ";
        $result   = $db->sql_query($SQLMedicalTest);

        $inputRow  = "";
        $title ='';
        $groupName = '';
        while ($row = $db->sql_fetchrow($result)) {
            $disabled ='';
            $medTestVisitRec = $fn->getRecordByCondition('vaccination_visit', "patient_information_id = '{$patient_information_id}' AND title='{$row['title']}' AND medical_test_id='{$row['medical_test_id']}' AND group_name='{$row['group_name']}'");

            if($medTestVisitRec['vaccination_visit_id'] != '' && $medTestVisitRec['outside'] == 0){
                $checked = "checked='checked'";
                $class ="";
                $bgColor = "bgColorHighlight";
                if($medTestVisitRec['patient_visit_id'] != $patient_visit_id){
                    $disabled = 'disabled';
                }
            } else {
                $checked = '';
                $class ="displayNone";
                $bgColor = '';
            }

            if($medTestVisitRec['fees'] != ''){
                $fees = $medTestVisitRec['fees'];
                if($medTestVisitRec['patient_visit_id'] != $patient_visit_id){
                    $disabled = 'disabled';
                }
            }else {
                $fees = $row['fees'];
            }

            $vaccination_date = '';

            if($medTestVisitRec['vaccination_date'] != ''){
                $vaccination_date = $fn->getCPDate($medTestVisitRec['vaccination_date'],"d-m-Y");
                if($medTestVisitRec['patient_visit_id'] != $patient_visit_id){
                    $disabled = 'disabled';
                }
            }

            $medical_test_id = 'due_date_'.$row['medical_test_id'];
            $vaccination_date_id = 'vaccination_date_'.$row['medical_test_id'];
            $outside = 'outside_'.$row['medical_test_id'];
            $expGroupHeading = array('useKey' => false);
            $yesNoArr = array(1 => 'Yes', 0 => 'No');

            
            $inputRow .= "
                <tr>
                    <td>
                        <div class='type-check ym-fbox-check'>
                        <input type='checkbox' id='title_{$count}' {$checked} value='{$row['title']}_{$count}' name='title[]' class='labTitle vaccinationTitle' {$disabled} patient_information_id = '{$patient_information_id}' patient_visit_id = '{$patient_visit_id}' medical_test_id='{$row['medical_test_id']}' group_name='{$row['group_name']}'>
                        <label for='title_{$count}'>{$row['title']}</label>
                        </div>
                    </td>
                    <td>
                        <div class='fld_fees'>
                            <input type='text' value='{$fees}' id='fld_fees' class='text vaccinationFees' name='fees[]' {$disabled} patient_information_id = '{$patient_information_id}' patient_visit_id = '{$patient_visit_id}' medical_test_id='{$row['medical_test_id']}' group_name='{$row['group_name']}'>
                        </div>
                    </td>
                    <td>
                        <div class='givenDateVaccination'>
                            {$formObj->getDateRow('Given Date', $vaccination_date_id, $medTestVisitRec['vaccination_date'])}
                        </div>
                        <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id' />
                        <input type='hidden' value='{$patient_visit_id}' name='patient_visit_id' />
                    </td>
                    <td>
                        <div class='dueDateVaccination'>
                            {$formObj->getDateRow('Due Date', $medical_test_id, $medTestVisitRec['due_date'])}
                        </div>
                        <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id' />
                        <input type='hidden' value='{$patient_visit_id}' name='patient_visit_id' />
                    </td>
                    <td>
                        {$formObj->getRRow('Outside', $outside, $medTestVisitRec['outside'], $yesNoArr, array('useKey'=>true, 'rowCls'=>'outside'))}
                        <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id' />
                        <input type='hidden' value='{$patient_visit_id}' name='patient_visit_id' />
                        <input type='hidden' value='{$patient_information_id}' name='patient_information_id' />
                        <input type='hidden' value='{$row['group_name']}' name='group_name' />
                    </td>

                    <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id[]' />
                    <input type='hidden' value='{$row['group_name']}' name='group_name[]' />
                </tr>
            ";
            $count ++;
        }

        $text = "
        <h3><strong>{$group_name}</strong></h3>
        <table class='list'>
            <tr>
                <th>Vaccination</th>
                <th>MRP</th>
                <th>Given date</th>
                <th>Due date</th>
                <th>Outside</th>
            </tr>
            {$inputRow}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryVaccination(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $patientVisitRec = $fn->getRecordByCondition('patient_visit', "patient_visit_id = '{$patient_visit_id}'");
        $patient_information_id   = $patientVisitRec['patient_information_id'];

        $appendSqlPV = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $PvText = '';

        $SQLPatientVisit = "
        SELECT pv.*
        FROM vaccination_visit pv
        WHERE pv.patient_information_id = '{$patient_information_id}'
        ORDER BY vaccination_visit_id
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $outside = '';
            if($rowPv['outside'] == 1){
                $outside = '(Outside)';
            }

            $PvText .= "
            <tr>
                <td>{$rowPv['title']} $outside</td>
                <td>{$rowPv['fees']}</td>
                <td>{$rowPv['vaccination_date']}</td>
                <td>{$rowPv['due_date']}</td>
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
                                <th class=''>Vaccination</td>
                                <th class=''>MRP</td>
                                <th class=''>Given date</td>
                                <th class=''>Due date</td>
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
    function getMedicalPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=MedicalTestRecordSubmit&showHTML=0";
        $rows  = "";
        $catRows = '';
        $catLinks = '';

        /*$SQLMedicalTest = "
        (SELECT m.medical_test_id
              ,m.title
              ,m.fees
        FROM medical_test m
        LEFT JOIN (medical_test_visit mv) ON (mv.medical_test_id = m.medical_test_id)
        WHERE patient_visit_id = {$patient_visit_id}
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
          AND value != 'Vaccination'
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
                $medTestVisitRec = $fn->getRecordByCondition('medical_test_visit', "patient_visit_id = '{$patient_visit_id}' AND title='{$row['title']}'");

                if($medTestVisitRec['medical_test_visit_id'] != ''){
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
                if($countrow == 4){
                    $divSeparation = "</div><div class='col-md-6 col-sm-6 noPadding'>";
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

                    $viewMedPara = "index.php?_topRm=main&module=hms_patientVisit&_spAction=viewMedicalParameters&patient_visit_id={$patient_visit_id}&medical_test_id={$row['medical_test_id']}&showHTML=0";

                    $view = "<a href='{$viewMedPara}' class='viewMedPara float_right'><u>Add Result</u></a>";
                }
                $investigation_date_id = 'investigation_date_'.$row['medical_test_id'];

                $hospitalFees = $fees - $row['lab_supplier_fees'];
                $supplierRec = $fn->getRecordByCondition('supplier', "category = 'Lab'");
                
                $inputRow .= "
                <div class=''>
                    <div class='type-check ym-fbox-check labTestBox {$bgColor}'>
                        <input type='checkbox' id='title_{$count}' {$checked} value='{$row['title']}_{$count}' name='title[]' class='labTitle' patient_visit_id = '{$patient_visit_id}' medical_test_id='{$row['medical_test_id']}'>
                        <label for='title_{$count}'>{$row['title']}</label>
                        <div class='hideTreatmentDetails_{$row['title']}_{$count} hideLabDetails {$class} labVisitNotes'>
                            {$view}
                             <div>
                        <input type='checkbox' id='print_label_{$count}' value='1' name='print_label[]' class='printLabel' patient_visit_id='{$patient_visit_id}' medical_test_id='{$row['medical_test_id']}' ".($medTestVisitRec['print_label'] == 1 ? "checked='checked'" : "")." onclick=\"this.value = this.checked ? '1' : '0'\">
                        <label for='print_label_{$count}'>Print</label>
                    </div>
                            <div>
                                <label>Fees</label>
                                <input type='text' value='{$fees}' id='fld_fees' class='labFees text mt10 mb10' name='fees[]' patient_visit_id = '{$patient_visit_id}' medical_test_id='{$row['medical_test_id']}'>
                            </div>
                            <div>
                                {$formObj->getDateRow('Date (Year-Month-Date)', $investigation_date_id, $creationDate)}
                                <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id' />
                                <input type='hidden' value='{$patient_visit_id}' name='patient_visit_id' />
                            </div>
                            <div class='{$classResult}'>
                                <label>Result</label>
                                <div class='type-text ym-fbox-text row_notes'>
                                    <textarea id='fld_notes' class='labNotes' name='notes[]' patient_visit_id = '{$patient_visit_id}' medical_test_id='{$row['medical_test_id']}'>{$medTestVisitRec['notes']}</textarea>
                                </div>
                            </div>
                            <div>
                                <input type='checkbox' id='' {$checked1} value='{$row['lab_supplier_fees']}' name='other_labs[]' class='otherLabs' patient_visit_id = '{$patient_visit_id}' medical_test_id='{$row['medical_test_id']}' supplier_id = {$supplierRec['supplier_id']} container = '{$row['container']}'>
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
            <div class='panel panel-default InvestigationsPanelWithInvestigations col-md-6 noPadding'>
                <div class='panel-heading' id='{$title}'>
                    <strong>{$title}</strong>
                    <a href='#saveBtn' class='ml20'><u>Go to Top</u></a>
                    <a class='medTestMainSubmit btn btn-info ml20' patient_visit_id = '{$patient_visit_id}'>Save</a>
                </div>
                <div class='panel-body'><div class='floatbox col-md-6 col-sm-6 noPadding'>{$inputRow}</div></div>
            </div>
            ";
            //$catLinks .= "<a href='#{$title}' class='mr20'><u>{$title}</u></a>";
            $catLinks .= "<li class='ui-state-default'><a href='#{$title}' valuelist_id='{$rowCat['valuelist_id']}' class='investigationCategoryLink mr20'><u>{$title}</u></a></li>";
        }

        $patientVisitRec = $fn->getRecordByCondition('patient_visit', "patient_visit_id = '{$patient_visit_id}'");
        $text = "
        <div id='' class=''>
        <form></form>
            <form id='portalForm_medicalTestDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button' id='saveBtn'></div>
                <ul id='sortableInvesticationCategories'>  
                    {$catLinks}
                </ul>
                <br/><br/><br/>
                <div class='floatbox col-md-12 noPadding'>
                    {$catRows}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
            <script type='text/javascript'>
                jQuery('document').ready(function () {
                    cpm.hms.patientVisit.sortInvestigationGroups();
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
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

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
            $medVisitParaRec = $fn->getRecordByCondition('medical_visit_parameter', "medical_test_parameter_id = '{$rowMTP['medical_test_parameter_id']}' AND medical_test_id='{$rowMTP['medical_test_id']}' AND patient_visit_id = {$patient_visit_id}");

            $rowTitle .= "
            <div class='type-text ym-fbox-text row_notes medParaList'>
                <label>{$rowMTP['title']}</label> 
                <textarea id='fld_para_notes' class='med_para_notes' medical_test_id ='{$rowMTP['medical_test_id']}' medical_test_parameter_id={$rowMTP['medical_test_parameter_id']} patient_visit_id = {$patient_visit_id} name='para_notes[]'>{$medVisitParaRec['notes']}</textarea>
            </div>
            <input type='hidden' value='{$rowMTP['medical_test_parameter_id']}' name='medical_test_parameter_id[]' />
            <input type='hidden' value='{$rowMTP['medical_test_id']}' name='medical_test_id_para[]' />
            ";
        }
        $medTestVisitRecCon = $fn->getRecordByCondition('medical_test_visit', "patient_visit_id = '{$patient_visit_id}' AND medical_test_id='{$medical_test_id}'");
        if($medTestVisitRecCon['medical_test_visit_id'] != ''){
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

        $formAction = "index.php?module=hms_patientVisit&_spAction=addNoteLabSubmit&showHTML=0";
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
    function getLabsDisplay($patient_visit_id='') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT  l.supplier_category
               ,ls.title
               ,l.labs_id
               ,l.patient_visit_id
               ,l.labs_code
               ,l.order_id
               ,l.amount
        FROM labs l
        LEFT JOIN (labs_supplier ls) ON (ls.labs_supplier_id = l.supplier_id)
        WHERE l.patient_visit_id = {$patient_visit_id}
        ORDER BY l.labs_id
        ";
        $result   = $db->sql_query($SQL);
        $serialNo = 1;
        while ($rowL = $db->sql_fetchrow($result)) {
            $supplier_category_link = '';
            $supplier_category = '';

            $receiptRec = $fn->getRecordByCondition('payments_receipt', "order_id != '' AND labs_id = '{$rowL['labs_id']}' AND receipt_status != 'Cancelled'");
            if($receiptRec){
                $supplier_category = "<a href='#' id='supplier_categoryFormLink'><u>View Form</u></a>";
                $editRow = "<a href='#' id='supplier_categoryFormLink'><u>Edit</u></a>";
                $viewSummaryLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=viewSummaryLabs&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&order_id={$rowL['order_id']}&labs_id={$rowL['labs_id']}";
                $viewSummary = "<a href='{$viewSummaryLink}' class='viewSummaryForLabsRecord'><u>View Summary</u></a>";
                $deleteLink   = "<a href='#' id='supplier_DeleteLink'><u>Delete</u></a>";
            }else{
                $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabsRecord&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                $editRow = "<a href='{$editURL}' id='editLabsRecord' patient_visit_id={$rowL['patient_visit_id']}><u>Edit</u></a>";

                if($rowL['supplier_category'] == 'Acrylic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=acrylicDentureForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='acrylicFormDenture' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";
                }else if($rowL['supplier_category'] == 'Ceramic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addCeramicForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='addCeramicForm' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";

                }else if($rowL['supplier_category'] == 'Orthodontic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addOrthodonticForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='addOrthodontic' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";

                }

                $viewSummary = "<a href='#' id='generatenoReceipt'><u>View Summary</u></a>";
                $deleteLink   = "<a href='#' class='deleteLabsRecord' labs_id='{$rowL['labs_id']}' patient_visit_id={$rowL['patient_visit_id']}><u>Delete</u></a>";
            }

            $labsCodeLink = "index.php?_topRm=inventory&module=hms_labs&_action=edit&labs_id={$rowL['labs_id']}";
            $LabsCode = "<a href='{$labsCodeLink}'><u>LB - {$rowL['labs_code']}</u></a>";

            if($rowL['amount'] == ''){
                $rowL['amount'] = 0;
            }

            $labsAmount = number_format($rowL['amount'], 2);
            $rows .= "
            <tr>
                <td>{$serialNo}</td>
                <td>{$LabsCode}</td>
                <td>{$rowL['title']}</td>
                <td>{$rowL['supplier_category']} - {$supplier_category}</td>
                <td>{$labsAmount}</td>
                <td>{$editRow}</td>
                <td>{$deleteLink}</td>
                <td>{$viewSummary}</td>
            </tr>
            ";
            $serialNo++;
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>S.No</th>
        <th>Labs Code</th>
        <th>Supplier Name</th>
        <th>Category</th>
        <th>Amount</th>
        <th>Edit</th>
        <th>Delete</th>
        <th>View Summary</th>
        </tr>
        ";

        $text = "
        <div id='' class='labsDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='labsPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedicalHistoryDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=medicalHistorySubmit&showHTML=0";
        $rows  = "";
        $inputRow  = "";

        $MedHisArray = array(
                            'Cardiac Comp'
                           ,'Hypertension'
                           ,'Blood Disorders'
                           ,'Diabetes'
                           ,'Jaundice'
                           ,'Pregnant'
                           );

        $SQL = "
        SELECT m.title
        FROM medical_history_information m
        WHERE m.patient_visit_id = {$patient_visit_id}
        AND m.status = 'Current'
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);
        $numRowsMV = $db->sql_numrows($result);

        $SQL1 = "
        SELECT m.title, m.others
        FROM medical_history_information m
        WHERE m.patient_visit_id = {$patient_visit_id}
        AND m.status = 'Current'
        ";
        $result1   = $db->sql_query($SQL1);
        $row = $db->sql_fetchrow($result1);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);

        $SQLChk = "
        SELECT patient_visit_id
        FROM patient_visit
        WHERE patient_visit_id < {$patient_visit_id}
        AND patient_information_id = {$patientVisitRec['patient_information_id']}
        ORDER BY patient_visit_id DESC
        ";
        $resultChk   = $db->sql_query($SQLChk);
        $rowChk = $db->sql_fetchrow($resultChk);
        $numRowsChk = $db->sql_numrows($resultChk);

        if($numRowsChk > 0 && $numRowsMV == 0){
            $SQL = "
            SELECT  m.title
                   ,m.status
            FROM medical_history_information m
            WHERE m.patient_visit_id = {$rowChk['patient_visit_id']}
            AND m.status = 'Current'
            ";
            $result    = $db->sql_query($SQL);
            $resultIns = $db->sql_query($SQL);

            while ($rowIns = $db->sql_fetchrow($resultIns)) {
                $fa = array();
                $fa['title']            = $rowIns['title'];
                $fa['status']           = $rowIns['status'];
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date']    = date("Y-m-d H:i:s");
                $fa['created_by']       = $fn->getSessionParam('userName');

                $SQLMedHis    = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_history_information');
                $resultMedHis = $db->sql_query($SQLMedHis);
            }

            $dataArray = $dbUtil->getResultsetAsArrayForForm($result);
        }

        $text = "
        <form></form>
        <div id='' class=''>
            <form id='portalForm_medHisDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='floatbox'>
                    <div class='medHisDisplay'>{$formObj->getCheckBoxArrRowByArr('', 'title', $MedHisArray, $dataArray)}</div>
                    <div class='float_left'>
                        {$formObj->getTARow('Others', 'others', $patientVisitRec['other_medical_history'])}
                    </div>
                    <div class='float_left'>
                        {$formObj->getTARow('Allergies', 'allergies', $patientInfoRec['alergies'])}
                    </div>
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getChiefComplainsDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction          = "index.php?_topRm=main&module=hms_patientVisit&_spAction=chiefComplainsSubmit&showHTML=0";
        $rows                = "";
        $inputRow            = "";
        $viewComplainListLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=complainList&patient_visit_id={$patient_visit_id}&showHTML=0";
        $viewComplainList     = "<a href='{$viewComplainListLink}' class='viewComplainListRecord btn btn-info float_left'>Complain List</a>";
        $patientVisitRec     = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_complainDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    <div class='addComplain float_left mb10'>
                        <input type='text' value='' id='fld_complain_title' class='text' name='complain_title' placeholder='Add Complain' patient_visit_id={$patient_visit_id}>
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-info complainSave' type='button' value='Add' name='portalForm' />
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-warning' type='submit' value='Save' name='portalForm' />
                    </div>
                </div>

                <!--<div class='type-button floatbox'>
                    <input class='button float_left' type='submit' value='Save' name='portalForm' />
                    {$viewComplainList}
                </div>-->
                <div class=''>
                    {$formObj->getTARow('Complain', 'complain', $patientVisitRec['complain'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getProcedurePortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction          = "index.php?_topRm=main&module=hms_patientVisit&_spAction=procedurePortalSubmit&showHTML=0";
        $rows                = "";
        $inputRow            = "";
        $patientVisitRec     = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_procedureDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    <div class='addProcedure float_left mb10'>
                        <input type='text' value='' id='fld_procedure_title' class='text' name='procedure_title' placeholder='Add Procedure' patient_visit_id={$patient_visit_id}>
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-info procedureSave' type='button' value='Add' name='portalForm' />
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-warning' type='submit' value='Save' name='portalForm' />
                    </div>
                </div>

                <div class=''>
                    {$formObj->getTARow('Procedure', 'visit_procedure', $patientVisitRec['visit_procedure'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getVitalSignsDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction          = "index.php?_topRm=main&module=hms_patientVisit&_spAction=vitalSignsSubmit&showHTML=0";
        $rows                = "";
        $inputRow            = "";
        $patientVisitRec     = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_vitalSignsDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button floatbox'>
                    <input class='button float_left' type='submit' value='Save' name='portalForm' />
                </div>
                <div class='c33l'>
                    {$formObj->getTBRow('Temperature-°F', 'temperature', $patientVisitRec['temperature'])}
                    {$formObj->getTBRow('PR', 'pulse_rate', $patientVisitRec['pulse_rate'])}
                    {$formObj->getTBRow('RR', 'respiratory_rate', $patientVisitRec['respiratory_rate'])}
                    {$formObj->getTBRow('BP', 'blood_pressure', $patientVisitRec['blood_pressure'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction          = "index.php?_topRm=main&module=hms_patientVisit&_spAction=summaryPortalSubmit&showHTML=0";
        $rows                = "";
        $inputRow            = "";
        $viewDiseaseListLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=diseaseList&patient_visit_id={$patient_visit_id}&showHTML=0";
        $viewDiseaseList     = "<a href='{$viewDiseaseListLink}' class='viewDiseaseListRecord btn btn-info float_left'>Diagnosis List</a>";
        $patientVisitRec     = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_summaryDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    <div class='addDiagnosis float_left mb10'>
                        <input type='text' value='' id='fld_diagnosis_title' class='text' name='diagnosis_title' placeholder='Add Diagnosis' patient_visit_id={$patient_visit_id}>
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-info diagnosisSave' type='button' value='Add' name='portalForm' />
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-warning' type='submit' value='Save' name='portalForm' />
                    </div>
                </div>
                <!--<div class='type-button floatbox'>
                    <input class='button float_left' type='submit' value='Save' name='portalForm' />
                    {$viewDiseaseList}
                </div>-->
                <div class=''>
                    {$formObj->getTARow('Diagnosis', 'diagnosis', $patientVisitRec['diagnosis'])}
                </div>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDneReportPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction      = "index.php?_topRm=main&module=hms_patientVisit&_spAction=dneReportPortalSubmit&showHTML=0";
        $patientVisitRec   = $fn->getRecordByCondition('dne_report', "patient_visit_id = '{$patient_visit_id}' AND report_type='Throat'");

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.first_name
        ";

        $urlDneReport = "index.php?module=hms_patientVisit&_spAction=printDneReport&patient_visit_id={$patient_visit_id}&showHTML=0";
        $dneReport = "<a href='{$urlDneReport}' id='dneReport' patient_visit_id='{$patient_visit_id}' class='btn btn-info' target='_blank'>Print Report</a>";

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_dneReportDisplay' class='yform columnar dneReportForm' method='post' action='{$formAction}'>
                <div class='mb10'>
                    <input class='btn btn-success' type='submit' value='SAVE' name='dneReportForm' />
                    {$dneReport}
                </div>
                <table>
                    <tr>
                        <td width='25%'>{$formObj->getTBRow('Department Title', 'department_title', $patientVisitRec['department_title'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Report Heading', 'report_heading', $patientVisitRec['report_heading'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Ref By', 'ref_by', $patientVisitRec['ref_by'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Study', 'study', $patientVisitRec['study'])}</td>
                    </tr>
                    <tr>
                        <td width='25%'>{$formObj->getDDRowBySQL('Examined By', 'examined_by', $sqlDoctor, $patientVisitRec['employee_id'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Posterior 1/3 of tongue', 'posterior_tongue', $patientVisitRec['posterior_tongue'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Vallecullae', 'vallecullae', $patientVisitRec['vallecullae'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Epiglottis', 'epiglottis', $patientVisitRec['epiglottis'])}</td>
                    </tr>
                    <tr>
                        <td width='25%'>{$formObj->getTBRow('Aryepiglottic Fold', 'aryepiglottic_fold', $patientVisitRec['aryepiglottic_fold'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Ventricular Bands', 'ventricular_bands', $patientVisitRec['ventricular_bands'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Pyriform Fossas', 'pyriform_fossas', $patientVisitRec['pyriform_fossas'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Oesophagus', 'oesophagus', $patientVisitRec['oesophagus'])}</td>
                    </tr>
                    <tr>
                        <td width='25%'>{$formObj->getTBRow('Advised', 'advised', $patientVisitRec['advised'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Specialist', 'specialist', $patientVisitRec['specialist'])}</td>
                        <td colspan='3'>
                            <div class='col-md-6'>{$formObj->getTARow('Vocal Cords', 'vocal_cords', $patientVisitRec['vocal_cords'])}</div>
                            <div class='col-md-6'>{$formObj->getTARow('Impression', 'impression', $patientVisitRec['impression'])}</div>
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
                <input type='hidden' name='report_type' value='Throat' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDneReportEarPortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction      = "index.php?_topRm=main&module=hms_patientVisit&_spAction=dneReportPortalSubmit&showHTML=0";
        $patientVisitRec   = $fn->getRecordByCondition('dne_report', "patient_visit_id = '{$patient_visit_id}' AND report_type='Ear'");

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.first_name
        ";

        $urlDneReport = "index.php?module=hms_patientVisit&_spAction=printDneEarReport&patient_visit_id={$patient_visit_id}&showHTML=0";
        $dneReport = "<a href='{$urlDneReport}' id='dneReport' patient_visit_id='{$patient_visit_id}' class='btn btn-info' target='_blank'>Print Report</a>";

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_dneReportEarDisplay' class='yform columnar dneReportForm' method='post' action='{$formAction}'>
                <div class='mb10'>
                    <input class='btn btn-success' type='submit' value='SAVE' name='dneReportForm' />
                    {$dneReport}
                </div>
                <table>
                    <tr>
                        <td width='25%'>{$formObj->getTBRow('Department Title', 'department_title', $patientVisitRec['department_title'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Report Heading', 'report_heading', $patientVisitRec['report_heading'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Ref By', 'ref_by', $patientVisitRec['ref_by'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Study', 'study', $patientVisitRec['study'])}</td>
                    </tr>
                    <tr>
                        <td width='25%'>{$formObj->getDDRowBySQL('Examined By', 'examined_by', $sqlDoctor, $patientVisitRec['employee_id'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Advised', 'advised', $patientVisitRec['advised'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Specialist', 'specialist', $patientVisitRec['specialist'])}</td>
                        <td>
                            {$formObj->getTARow('Impression', 'impression', $patientVisitRec['impression'])}
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td width='50%'><b>RIGHT</b>
                            <table>
                                <tr>
                                    <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('EAC', 'eac_right', $patientVisitRec['eac_right'])}</div></td>
                                </tr>
                                <tr>
                                    <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Tympanic Membrane', 'tympanic_membrane_right', $patientVisitRec['tympanic_membrane_right'])}</div></td>
                                </tr>
                                <tr>
                                    <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Middle Ear', 'middle_ear_right', $patientVisitRec['middle_ear_right'])}</div></td>
                                </tr>
                            </table>
                        </td>
                        <td width='50%'><b>LEFT</b>
                            <table>
                                <tr>
                                    <td><div class='col-md-6'>{$formObj->getTBRow('EAC', 'eac_left', $patientVisitRec['eac_left'])}</div></td>
                                </tr>
                                <tr>
                                    <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Tympanic Membrane', 'tympanic_membrane_left', $patientVisitRec['tympanic_membrane_left'])}</div></td>
                                </tr>
                                <tr>
                                    <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Middle Ear', 'middle_ear_left', $patientVisitRec['middle_ear_left'])}</div></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
                <input type='hidden' name='report_type' value='Ear' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDneReportNosePortalDisplay($patient_visit_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        $formAction      = "index.php?_topRm=main&module=hms_patientVisit&_spAction=dneReportPortalSubmit&showHTML=0";
        $patientVisitRec   = $fn->getRecordByCondition('dne_report', "patient_visit_id = '{$patient_visit_id}' AND report_type='Nose'");

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.first_name
        ";

        $urlDneReport = "index.php?module=hms_patientVisit&_spAction=printDneNoseReport&patient_visit_id={$patient_visit_id}&showHTML=0";
        $dneReport = "<a href='{$urlDneReport}' id='dneReport' patient_visit_id='{$patient_visit_id}' class='btn btn-info' target='_blank'>Print Report</a>";

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_dneReportNoseDisplay' class='yform columnar dneReportForm' method='post' action='{$formAction}'>
                <div class='mb10'>
                    <input class='btn btn-success' type='submit' value='SAVE' name='dneReportForm' />
                    {$dneReport}
                </div>
                <table>
                    <tr>
                        <td width='25%'>{$formObj->getTBRow('Department Title', 'department_title', $patientVisitRec['department_title'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Report Heading', 'report_heading', $patientVisitRec['report_heading'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Ref By', 'ref_by', $patientVisitRec['ref_by'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Study', 'study', $patientVisitRec['study'])}</td>
                    </tr>
                    <tr>
                        <td width='25%'>{$formObj->getDDRowBySQL('Examined By', 'examined_by', $sqlDoctor, $patientVisitRec['employee_id'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Advised', 'advised', $patientVisitRec['advised'])}</td>
                        <td width='25%'>{$formObj->getTBRow('Specialist', 'specialist', $patientVisitRec['specialist'])}</td>
                        <td>
                            {$formObj->getTARow('Impression', 'impression', $patientVisitRec['impression'])}
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td width='25%'><b>RIGHT</b></td>
                        <td width='25%'><b>LEFT</b></td>
                    </tr>
                    <tr>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Nasal Septum', 'nasal_septum', $patientVisitRec['nasal_septum'])}</div></td>
                        <td width='25%'><div class='col-md-6'></div></td>
                    </tr>
                    <tr>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Nasal Cavity', 'nasal_cavity_right', $patientVisitRec['nasal_cavity_right'])}</div></td>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Nasal Cavity', 'nasal_cavity_left', $patientVisitRec['nasal_cavity_left'])}</div></td>
                    </tr>
                    <tr>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Inferior Turbinate', 'inferior_turbinate_right', $patientVisitRec['inferior_turbinate_right'])}</div></td>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Inferior Turbinate', 'inferior_turbinate_left', $patientVisitRec['inferior_turbinate_left'])}</div></td>
                    </tr>
                    <tr>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Middle Turbinate', 'middle_turbinate_right', $patientVisitRec['middle_turbinate_right'])}</div></td>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Middle Turbinate', 'middle_turbinate_left', $patientVisitRec['middle_turbinate_left'])}</div></td>
                    </tr>
                    <tr>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Paranasal Sinuses', 'paranasal_sinuses', $patientVisitRec['paranasal_sinuses'])}</div></td>
                        <td width='25%'><div class='col-md-6'></div></td>
                    </tr>
                    <tr>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('OMU', 'omu_right', $patientVisitRec['omu_right'])}</div></td>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('OMU', 'omu_left', $patientVisitRec['omu_left'])}</div></td>
                    </tr>
                    <tr>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Choanae', 'choanae_right', $patientVisitRec['choanae_right'])}</div></td>
                        <td width='25%'><div class='col-md-6'>{$formObj->getTBRow('Choanae', 'choanae_left', $patientVisitRec['choanae_left'])}</div></td>
                    </tr>
                </table>
                <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
                <input type='hidden' name='report_type' value='Nose' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddDoctorRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addDoctorRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.first_name
        ";

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor)}
            {$formObj->getTBRow('Consulting Fees', 'consultation_fees')}
            {$formObj->getTBRow('Room', 'consultation_room')}
            {$formObj->getTARow('Notes', 'notes')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddTreatmentRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addTreatmentRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('treatmentCategory', 'value ASC');

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Code', 'treatment_code')}
            {$formObj->getTBRow('Title', 'treatment_title')}
            {$formObj->getDDRowBySQL('Category', 'category', $sqlCategory, '', $expVl)}
            {$formObj->getTBRow('Fees', 'fees')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddDiagnosisRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addDiagnosisRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Code', 'diagnosis_code')}
            {$formObj->getTBRow('Title', 'diagnosis_title')}
            {$formObj->getTBRow('Fees', 'fees')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getLabsSupplierJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $supplier_category   = $fn->getReqParam('supplier_category');

        $json  = array();

        if ($supplier_category == ""){
            $json[] = array("value" => "", "caption" => "Please Select");
            return json_encode($json);
        }

        $SQL = "
        SELECT labs_supplier_id
              ,title
        FROM labs_supplier
        WHERE category = '{$supplier_category}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['labs_supplier_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }

    /**
     *
     */
     function getAddLabsRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $expVl = array('sqlType' => 'OneField');
        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addLabsRecordSubmit&showHTML=0";
        $patient_visit_id       = $fn->getReqParam('patient_visit_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $sqlCategory = $fn->getValueListSQL('labSupplierCategory');

        /*$labsModule = $fn->getReqParam('labsModule');
        $categoryValue = '';
        $sqlSupplier  = '';
        if($labsModule == 1){
            $supplier = $fn->getReqParam('supplier');
            $categoryValue = $supplier;
            $expVl = array('disabled' =>  true,
                            'sqlType' => 'OneField');

            $sqlSupplier = "
            SELECT labs_supplier_id
                  ,title
            FROM labs_supplier
            WHERE category = '{$supplier}'
            ORDER BY title
            ";
        }*/

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Category', 'supplier_category', $sqlCategory, '', $expVl)}
            {$formObj->getDDRowBySQL('Supplier', 'supplier_id', '', '')}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            <input type='hidden' name='patient_information_id' value='{$patient_information_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditLabsRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $labs_id          = $fn->getReqParam('labs_id');
        $rows = '';

        $SQL = "
        SELECT  l.supplier_category
               ,l.labs_id
               ,l.patient_visit_id
               ,l.supplier_id
        FROM labs l
        WHERE l.patient_visit_id = {$patient_visit_id}
        AND labs_id = {$labs_id}
        ";
        $result   = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $sqlSupplier = "
        SELECT labs_supplier_id
              ,title
        FROM labs_supplier
        WHERE category = '{$row['supplier_category']}'
        ORDER BY title
        ";

        $expVl  = array('sqlType' => 'OneField',
                        'disabled' => true);
        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabsRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $sqlCategory = $fn->getValueListSQL('labSupplierCategory');

        $text = "
        <form id='EditLabsRecordportalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Category', 'supplier_category', $sqlCategory, $row['supplier_category'], $expVl)}
            {$formObj->getDDRowBySQL('Supplier', 'supplier_id', $sqlSupplier, $row['supplier_id'])}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
            <input type='hidden' name='labs_id' value='{$labs_id}' />
        </form>
        ";

        return $text;
    }


    /**
     *
     */
     function getEditDoctorRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editDoctorRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $employee_visit_id = $fn->getReqParam('employee_visit_id');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT ev.*
        FROM employee_visit ev
        WHERE ev.employee_visit_id = {$employee_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $rowEV = $db->sql_fetchrow($result);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor, $rowEV['employee_id'])}
            {$formObj->getTBRow('Consulting Fees', 'consultation_fees', $rowEV['consultation_fees'])}
            {$formObj->getTBRow('Room', 'consultation_room', $rowEV['consultation_room'])}
            {$formObj->getTARow('Notes', 'notes', $rowEV['notes'])}
            <input type='hidden' name='employee_visit_id' value='{$employee_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddLabRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addLabRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_id
        ";

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Test Name', 'title')}
            {$formObj->getTARow('Notes', 'notes')}
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor)}
            <input type='hidden' name='patient_visit_id' value='{$patient_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditLabRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $lab_visit_id = $fn->getReqParam('lab_visit_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT *
        FROM lab_visit
        WHERE lab_visit_id = {$lab_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $rowLV = $db->sql_fetchrow($result);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Medicine', 'title', $rowLV['title'])}
            {$formObj->getTARow('Notes', 'notes', $rowLV['notes'])}
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor, $rowLV['employee_id'])}
            <input type='hidden' name='lab_visit_id' value='{$lab_visit_id}' />
        </form>
        ";

        return $text;
    }

    /**
     */
     function getAddPatientRecord() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expVl = array('sqlType' => 'OneField');
        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addPatientRecordSubmit&showHTML=0";
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $sqlBillType    = $fn->getValueListSQL('billType');
        $sqlGender      = $fn->getValueListSQL('gender');

        $row = '';
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $billType   = $fn->getReqParam('bill_type');

        $companyDetailsHide = '';
        if($billType == '' || $billType == 'Individual'){
            $companyDetailsHide = 'companyDetailsHide';
            $sqlCompany = '';
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlComp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlComp = "AND site_id = {$cpSiteIdSession}";
        }

        $sqlCompany = "
        SELECT company_id
               ,company_name
        FROM company
        WHERE category = '{$billType}'
        {$appendSqlComp}
        ORDER BY company_name
        ";


        $fieldsetHideItems = "
        <table>
            <tr>
                <th colspan='4'>Address Details</th>
            </tr>

            <tr>
                <td width='25%'>{$formObj->getTBRow('Address Street', 'address_street')}</td>
                <td width='25%'>{$formObj->getTBRow('Address Area', 'address_area')}</td>
                <td width='25%'>{$formObj->getTBRow('Address City', 'address_city')}</td>
                <td width='25%'>{$formObj->getTBRow('Address Code', 'address_code')}</td>
            </tr>
            <tr>
                <td width='25%'>{$formObj->getDDRowBySQL('Address Country', 'address_country', $sqlCountry)}</td>
            </tr>
        </table>
        ";

        $fieldsetHide = "
        <div class = 'linkPortalWrapper'>
            <div expanded='1' class='header'>
               <a id='displayText' href='#'>Show More Fields (+)</a>
            </div>
            <div id='toggleText' style='display: none'>
                    {$formObj->getFieldSetWrapped('', $fieldsetHideItems)}
            </div>
        </div>
        ";

        $expArr      = array('hideFirstOption' => 1);
        $expPassType = array('rowCls' => 'showme');
        $expPassport = array('rowCls' => 'hideme');
        $expGender   = array('sqlType' => 'OneField', 'rowCls' => 'hideme');
        $expBillType = array('sqlType' => 'OneField', 'hideFirstOption' => 1);

        $nricRow = $formObj->getTBRow('NRIC *', 'nric', '', $expPassType);
        $passportRow = $formObj->getTBRow('Passport No *', 'registration_no', '', $expPassport);
        $genderRow = $formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, '', $expGender);
        $dobRow = $formObj->getDateRow('DOB (YYYY-MM-DD)', 'dob', '', array('yearStart' => 1950, 'yearEnd' => date('Y'), 'rowCls' => 'hideme'));

        $text = "
        <form id='portalForm' class='yform columnar qucikaddPatientForm' method='post' action='{$formAction}'>
            <table class='thinlist'>
                <tr>
                    <td>{$formObj->getTBRow('First Name*', 'first_name')}</td>
                    <td>{$formObj->getTBRow('Middle Name', 'middle_name')}</td>
                    <td>{$formObj->getTBRow('Last Name', 'last_name')}</td>
                    <td>{$formObj->getDDRowByArr('Pass Type', 'pass_type', $cpCfg['m.hms.patientInformation.passTypeArr'], 'NRIC', $expArr)}</td>
                </tr>
                <tr>
                    <td>{$nricRow}</td>
                    <td>{$passportRow}</td>
                    <td>{$dobRow}</td>
                    <td>{$genderRow}</td>
                </tr>
                <tr>
                    <td>{$formObj->getTBRow('Phone', 'phone')}</td>
                    <td>{$formObj->getTBRow('Email', 'email')}</td>
                    <td>{$formObj->getDateRow('First Visit On (YYYY-MM-DD)', 'first_admit', '')}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>{$formObj->getDDRowBySQL('Bill Type*', 'bill_type', $sqlBillType, 'Individual', $expBillType)}</td>
                    <td class='companyDetailsTr {$companyDetailsHide}'>{$formObj->getDDRowBySQL('', 'company_id', $sqlCompany,'')}</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
            {$fieldsetHide}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getCompanyNameJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        $company_category   = $fn->getReqParam('company_category');

        $json  = array();

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlComp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlComp = "AND site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT company_id
              ,company_name
        FROM company
        WHERE category = '{$company_category}'
        {$appendSqlComp}
        ORDER BY company_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['company_id'], "caption" => $row['company_name']);
        }

        return json_encode($json);
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


        $record_id = $fn->getIssetParam($row, 'patient_visit_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Dne Report Throat', 'hms_patientVisit', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Dne Report Ear', 'hms_patientVisit', 'dneEarpicture', $row)}
        {$media->getRightPanelMediaDisplay('Dne Report Nose', 'hms_patientVisit', 'dneNosepicture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_patientVisit', 'attachment', $row)}
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
            ,"Review Records"
        );

        $billType           = $fn->getReqParam('bill_type');
        $sqlBillType        = $fn->getValueListSQL('billType');
        $status             = $fn->getReqParam('status');
        $yesterday          = $fn->getReqParam('yesterday');
        $check_up_date1     = $fn->getReqParam('check_up_date_1');
        $check_up_date2     = $fn->getReqParam('check_up_date_2');
        $employee_id        = $fn->getReqParam('employee_id');
        $referral_doctor_id = $fn->getReqParam('referral_doctor_id');
        $employee_category  = $fn->getReqParam('employee_category');
        $investigation  = $fn->getReqParam('investigation');
        $sqlCategory        = $fn->getValueListSQL('employeeCategory');
        $sqlInvestigation        = $fn->getValueListSQL('investigationCategory');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND site_id = {$cpSiteIdSession}";
        }

        $sqlEmployee = "
        SELECT employee_id
              ,(CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

        $sqlEmployeeReferral = "
        SELECT employee_id
              ,(CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE category = 'Doctor'
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

        if($yesterday == ""){
            $yesterday = "Yesterday";
        }

        $text = "
        <td>
            <select name='yesterday'>
                {$cpUtil->getDropDown1($yesterdayArray, $yesterday)}
           </select>
        </td>
        <td>
            <select name='referral_doctor_id'>
                <option value=''>ReferTo Dr</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeReferral, $referral_doctor_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($statusArray, $status)}
           </select>
        </td>
        <td>
            <select name='investigation'>
                <option value=''>Investigations</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlInvestigation, $investigation)}
            </select>
        </td>
        <td>
            <select name='employee_id'>
                <option value=''>Employee</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployee, $employee_id)}
            </select>
        </td>
        <td>
            {$formObj->getDateRangeRow('Visit Date:', 'check_up_date', $check_up_date1, $check_up_date2)}
        </td>
        ";
        /*<td>
            <select name='employee_category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory, $employee_category)}
            </select>
        </td>*/

        return $text;
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getSelectDoctorDetails(){
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $appointment_id         = $fn->getReqParam('appointment_id');
        $age_year               = $fn->getReqParam('age_year');
        $age_month              = $fn->getReqParam('age_month');
        $age_day                = $fn->getReqParam('age_day');
        $patient_name           = $fn->getReqParam('patient_name');
        $father_name            = $fn->getReqParam('father_name');
        $husband_name           = $fn->getReqParam('husband_name');
        $address_area           = $fn->getReqParam('address_area');
        $address_street         = $fn->getReqParam('address_street');
        $phone                  = $fn->getReqParam('phone');
        $gender                 = $fn->getReqParam('gender');
        $weight                 = $fn->getReqParam('weight');
        $temperature            = $fn->getReqParam('temperature');
        $height            = $fn->getReqParam('height');
        $bmi            = $fn->getReqParam('bmi');
        $blood_pressure            = $fn->getReqParam('blood_pressure');
        $pulse_rate            = $fn->getReqParam('pulse_rate');
        $respiratory_rate            = $fn->getReqParam('respiratory_rate');
        $SpO2            = $fn->getReqParam('SpO2');

        $expNoEdit = array('isEditable' => 0);

        $formAction = "index.php?_topRm=main&module=hms_patientVisit&_spAction=createVisitRecordSubmit&showHTML=0";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND site_id = {$cpSiteIdSession}";
        }

        $employee_id = "";
        if($_SESSION['staff_id'] != ""){
            $SQLemp = "
            SELECT employee_id
            FROM employee
            WHERE staff_id = {$_SESSION['staff_id']}
            ";
            $resultemp = $db->sql_query($SQLemp);
            $rowemp    = $db->sql_fetchrow($resultemp);

            $employee_id = $rowemp['employee_id'];
        }

        $sqlEmployee = "
        SELECT employee_id
              ,(CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE visits_patients = 1
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

        $text = "
        <form id='portalFormPatientVisitCreate' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDDRowBySQL('Choose Dr/Nurse', 'employee_id', $sqlEmployee, $employee_id)}
                {$formObj->getYesNoRRow('Dr Required', 'dr_required', '1', $expNoEdit)}
                <input type='hidden' name='patient_information_id' value='{$patient_information_id}'>
                <input type='hidden' name='appointment_id' value='{$appointment_id}'>
                <input type='hidden' name='patient_name' value='{$patient_name}'>
                <input type='hidden' name='father_name' value='{$father_name}'>
                <input type='hidden' name='husband_name' value='{$husband_name}'>
                <input type='hidden' name='address_area' value='{$address_area}'>
                <input type='hidden' name='address_street' value='{$address_street}'>
                <input type='hidden' name='age_year' value='{$age_year}'>
                <input type='hidden' name='age_month' value='{$age_month}'>
                <input type='hidden' name='age_day' value='{$age_day}'>
                <input type='hidden' name='phone' value='{$phone}'>
                <input type='hidden' name='gender' value='{$gender}'>
                <input type='hidden' name='weight' value='{$weight}'>
                <input type='hidden' name='temperature' value='{$temperature}'>
                <input type='hidden' name='height' value='{$height}'>
                <input type='hidden' name='bmi' value='{$bmi}'>
                <input type='hidden' name='blood_pressure' value='{$blood_pressure}'>
                <input type='hidden' name='pulse_rate' value='{$pulse_rate}'>
                <input type='hidden' name='respiratory_rate' value='{$respiratory_rate}'>
                <input type='hidden' name='SpO2' value='{$SpO2}'>
            </table>
        </form>
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
     */
    function getViewSummaryLabs() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $order_id = $fn->getReqParam('order_id');
        $labs_id  = $fn->getReqParam('labs_id');

        $SQLReceipt = "
        SELECT date
              ,amount
        FROM payments_receipt
        WHERE order_id = {$order_id}
        AND  labs_id = {$labs_id}
        AND receipt_status != 'Cancelled'
        ";
        $resultReceipt = $db->sql_query($SQLReceipt);
        $numRowsReceipt = $db->sql_numrows($resultReceipt);
        $rows = '';
        if($numRowsReceipt > 0){
            $count = 1;
            while($rowReceipt  = $db->sql_fetchrow($resultReceipt)){
                $amount = number_format($rowReceipt['amount'], 2);
                $rows .= "
                <tr>
                    <td>{$rowReceipt['date']}</td>
                    <td class='txtRight'>{$amount}</td>
                </tr>
                ";
            }
        }

        $SQL = "
        SELECT l.*
              ,(SELECT SUM(i.payments_amount) FROM payments i
                WHERE i.labs_id = l.labs_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM payments_receipt r
                WHERE r.labs_id = l.labs_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM payments_receipt_history invHist
                LEFT JOIN payments_receipt r ON (r.payments_receipt_id = invHist.payments_receipt_id)
                LEFT JOIN `payments` i ON (i.labs_id = {$labs_id})
                WHERE invHist.payments_id =  i.payments_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) as Amount_Paid
        FROM `labs` l
        WHERE l.labs_id = {$labs_id}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $total_invoice_amount = 0;
        if($row['invoice_amount'] != ''){
            $total_invoice_amount = $row['invoice_amount'];
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
        $overallBalanceAmt    = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class='txtRight'>Amount</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        <br/>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th colspan = '2'>Summary</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Amount payable</td>
                    <td class='txtRight'>{$total_invoice_amount}</td>
                </tr>
                <tr>
                    <td>Amount paid</td>
                    <td class='txtRight'>{$invoiced_Paid_Amount}</td>
                </tr>
                <tr>
                    <td>Outstanding Amount</td>
                    <td class='txtRight'>{$overallBalanceAmt}</td>
                </tr>
            </tbody>
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintPrescription() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

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
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 13);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.uhid
              ,e.first_name
              ,m.title AS medicine
              ,m.instruction
              ,m.dosage
              ,m.route
              ,m.qty
              ,m.days
              ,m.af_bf_food
              ,e.first_name
            FROM patient_visit pv
            LEFT JOIN medicines_visit m ON (m.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
            LEFT JOIN (employee_visit ev) ON (ev.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE pv.patient_visit_id = '{$patient_visit_id}'
            ORDER BY 
            CASE
                WHEN (m.title LIKE '%inj%' 
                      ) THEN 1
                WHEN (m.title LIKE '%TAB%' 
                      ) THEN 2
                WHEN (m.title LIKE '%CAP%' 
                      ) THEN 3
                WHEN (m.title LIKE '%SYP%' 
                      ) THEN 4
                ELSE m.medicines_visit_id
            END, m.medicines_visit_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        //$font = $pdf->addTTFfont("lib/gothic.ttf");
        //$pdf->SetFont($font,'B',10);
        $pdf->SetFont('','B', 9);
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
            $visit_code = 'VST-'.$company['visit_code'];
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
        /*if($company['first_name'] == 'Dr Vinaignan'){
            $doctorName = $company['first_name'];
        }*/
        
        $tbl1 = '
        <table border="0" cellpadding="4" width="100%" style="border-bottom:2px solid #000000;">
            <tr>
                <td width="75%">UHID : '.$company['uhid'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Patient Name : '.$company['patient_name'].'&nbsp;&nbsp;('.$gender.') &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Age : '.$age .'</td>
                <td width="25%" align="left">Date &nbsp;: '.$currentDate.'</td>
            </tr>
            <tr>
                <td width="75%" align="">Consultant &nbsp;&nbsp;&nbsp;&nbsp;: '.$company['first_name'].'</td>
                <td width="25%" align="left">Code : '.$visit_code.'</td>
            </tr>
        </table>
        ';
        
        $fbsVal   = $fn->getRecordByCondition('medical_test_visit', "patient_visit_id = '{$patient_visit_id}' AND title='FBS'");
        $rbsVal   = $fn->getRecordByCondition('medical_test_visit', "patient_visit_id = '{$patient_visit_id}' AND title='RBS'");
        $ppVal    = $fn->getRecordByCondition('medical_test_visit', "patient_visit_id = '{$patient_visit_id}' AND title='POST PRANDIAL'");
        $hb1AcVal = $fn->getRecordByCondition('medical_test_visit', "patient_visit_id = '{$patient_visit_id}' AND title='HbA1C'");

        $blood_pressure = '';
        if($company['blood_pressure'] != ''){
            $blood_pressure = $company['blood_pressure'].' mm/hg';
        }

        $fbsVal_notes = '';
        if($fbsVal['notes'] != ''){
            $fbsVal_notes = $fbsVal['notes'].' mg/dl';
        }

        $rbsVal_notes = '';
        if($rbsVal['notes'] != ''){
            $rbsVal_notes = $rbsVal['notes'].' mg/dl';
        }

        $ppVal_notes = '';
        if($ppVal['notes'] != ''){
            $ppVal_notes = $ppVal['notes'].' mg/dl';
        }

        $hb1AcVal_notes = '';
        if($hb1AcVal['notes'] != ''){
            $hb1AcVal_notes = $hb1AcVal['notes'].' mg/dl';
        }
        
        $tbl2 = '
        <table border="0" width="100%" style="padding-top:10px;">
            <tr>
                <td width="18%" align="left">T- '.$company['temperature'] .'</td>
                <td width="35%" align="left">BP- '.$blood_pressure.'</td>
                <td width="22%" align="left">PR- '.$company['pulse_rate'].'</td>
                <td width="25%" align="right">WT- '.$company['weight'].'Kgs</td>
            </tr>
        </table>
        <table border="0" width="100%">
            <tr>
                <td width="33%" align="left">FBS- '.$fbsVal_notes.'</td>
                <td width="33%" align="center">RBS- '.$rbsVal_notes.'</td>
                <td width="33%" align="right">PP- '.$ppVal_notes.'</td>
            </tr>
        </table>
        ';

        $tbl3 = '';

        $tbl3 ='<table border="0" width="100%" cellpadding="4">';

        $sub_total = 0;
        $count = 1;
        $total_qty = 0;
        $discount = 0;
        $discountValueTotal = 0;
        while ($row = $db->sql_fetchrow($result)) {
            if($row['medicine'] != '' && $row['instruction'] != ''){
                $instruction = explode(", ", $row['instruction']);
                $instructionLen = count($instruction);

                $morning = 0;
                $noon = 0;
                $night = 0;
                $dosage = '';

                for($i=0;$i<$instructionLen;$i++){
                    if($instruction[$i] == 'Morning'){
                        if((strpos($row['dosage'], 'ML') !== false ) || (strpos($row['dosage'], 'ml') !== false)){
                            $morning = $row['dosage'];
                            $dosage = '';
                        }
                        else{
                            $morning = 1;
                            if($row['dosage']){
                                $dosage = '  ('.$row['dosage']. ')';
                            }
                        }
                    }
                    if($instruction[$i] == 'Noon'){
                        if((strpos($row['dosage'], 'ML') !== false ) || (strpos($row['dosage'], 'ml') !== false)){
                            $noon = $row['dosage'];
                            $dosage = '';
                        }
                        else{
                            $noon = 1;
                            if($row['dosage']){
                                $dosage = '  ('.$row['dosage']. ')';
                            }
                        }
                    }
                    if($instruction[$i] == 'Night'){
                        if((strpos($row['dosage'], 'ML') !== false ) || (strpos($row['dosage'], 'ml') !== false)){
                            $night = $row['dosage'];
                            $dosage = '';
                        }
                        else{
                            $night = 1;
                            if($row['dosage']){
                                $dosage = '  ('.$row['dosage']. ')';
                            }
                        }
                    }
                }

                if($row['qty'] == 0 || $row['qty'] == ''){
                    $qty = "{$row['qty']}";
                }else {
                    $qty = "({$row['qty']})";                    
                }

                $route = $row['route'];
                if($route == ''){
                    $route = "{$route}";
                }else {
                    $route = "({$route})";                    
                }

                $days = $row['days'];
                if($days == ''){
                    $days = "{$days}";
                }else {
                    $days = "({$days})";                    
                }

                if($row['instruction'] == 'STAT' || $row['instruction'] == 'SOS'){
                    $tbl3 = $tbl3.'<tr>
                                        <td align="left" width="35%">'.$row['medicine'].'</td>
                                        <!--<td align="left" width="10%">'.$qty.'</td>-->
                                        <td align="left" width="8%">'.$route.'</td>
                                        <td align="center" width="47%">'.$row['instruction'].'</td>
                                    </tr>
                                    ';
                } else {

                    if($count == 1){
                        $tbl3 = $tbl3.'<tr>
                                          <th width="4%"></th>
                                          <th width="31%"><font style="font-size:15px;">R<sub>x</sub></font></th>
                                          <th width="8%"></th>
                                          <th align="center" width="11%">Morning</th>
                                          <th align="center" width="3%"></th>
                                          <th align="center" width="13%">Afternoon</th>
                                          <th align="center" width="3%"></th>
                                          <th align="center" width="9%">Night</th>
                                          <th align="center" width="8%">Days</th>
                                          <th align="center" width="10%">Food</th>
                                       </tr>  
                        ';
                    }

                    $tbl3 = $tbl3.'<tr>
                                        <th width="4%" align="center">•</th>
                                        <td align="left" width="31%">'.$row['medicine'].''.$dosage.'</td>
                                        <td align="center" width="8%">'.$route.'</td>
                                        <td align="center" width="11%">'.$morning.'</td>
                                        <td align="center" width="3%">-</td>
                                        <td align="center" width="13%">'.$noon.'</td>
                                        <td align="center" width="3%">-</td>
                                        <td align="center" width="9%">'.$night.'</td>
                                        <td align="center" width="8%">'.$days.'</td>
                                        <td align="center" width="10%">'.$row['af_bf_food'].'</td>
                                    </tr>
                                    ';
                }
                if($count == 1){
                    //$tbl3 = $tbl3.'</table>';
                }
            }
            $count++;
        }
        $tbl3 = $tbl3.'
                    <tr>
                        <td width="100%" style="border-bottom: 2px solid #000000;">
                        </td>
                    </tr>
                </table>';

        $SQL1 = "
        SELECT mt.*
              ,m.normal_value
              ,mt.fees AS amount
        FROM medical_test_visit mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.patient_visit_id = '{$patient_visit_id}'
        ";
        $result1 = $db->sql_query($SQL1);
        $numRows1 = $db->sql_numrows($result1);
        $total_amount = 0;
        $labTitle = '';
        $tbl4 = '';
        $testResult = '';
        if($numRows1 > 0){
        $tbl4 = '
        <table cellpadding="4" border="0" width="100%">
            <thead>
            <tr><th style="text-decoration:underline;">Lab Test Required:</th></tr>
            </thead>
        ';
        while ($row1 = $db->sql_fetchrow($result1)) {
            $testResult = '';
            /*$tbl4 = $tbl4.'<tr>
                                <td align="left" width="100%">'.$row1['title'].'</td>
                            </tr>
                            ';*/
            if($row1['notes'] != ''){
                $testResult = ' - '.$row1['notes'];
            }
            $labTitle .= $row1['title'] .$testResult. ', ';

            $total_amount += $row1['amount'];
        }
        $labTitle = rtrim($labTitle, ", ");

        $tbl4 = $tbl4.'<tr>
                            <td align="left" width="100%">'.$labTitle.'</td>
                        </tr>
                        </table>
                        ';
        }

        $tbl5 = '
        <table cellpadding="4" border="0">
            <tr>
                <td width="14%" style="line-height:25px;border-bottom:2px solid #000000;">Complaints</td>
                <td width="3%" style="line-height:25px;border-bottom:2px solid #000000;">:</td>
                <td width="83%" style="line-height:25px;border-bottom:2px solid #000000;">'.$company['complain'].'</td>
            </tr>
            <tr>
                <td width="14%" style="line-height:25px;border-bottom:2px solid #000000;">Diagnosis</td>
                <td width="3%" style="line-height:25px;border-bottom:2px solid #000000;">:</td>
                <td width="83%" style="line-height:25px;border-bottom:2px solid #000000;">'.$company['diagnosis'].'</td>
            </tr>
        </table>
        ';

        $tbl6 = '
        <table cellpadding="4" border="0">
            <tr>
                <td width="11%">Wt (in kgs)</td>
                <td width="3%">:</td>
                <td width="22%">'.$company['weight'].'</td>
                <td width="9%">Temp-°F</td>
                <td width="3%">:</td>
                <td width="22%">'.$company['temperature'].'</td>
                <td width="5%">BP</td>
                <td width="3%">:</td>
                <td width="22%">'.$company['blood_pressure'].'</td>
            </tr>
        </table>
        ';

        $tbl7 = '
        <table cellpadding="4" border="0" width="100%">
            <thead>
                <tr>
                    <th style="text-decoration:underline;">Advice:</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align="left" width="100%">'.$company['patient_visit_advice'].'</td>
                </tr>
                <tr>
                    <td align="left" width="100%"><br/><br/>Referred To Doctor : '.$company['referred_to_doctor'].'</td>
                </tr>
            </tbody>
        </table>
        ';

        /*
        <td width="25%" align="center" style="border-left:1px solid #000000;">
            <img src="images/Ear.jpg" alt="ear-img"><br/>
            <img src="images/Nose.jpg" alt="nose-img" height="160"><br/>
            <img src="images/Throught.jpg" alt="throught-img" height="200"><br/>
            <img src="images/Vocal.jpg" alt="vocal-img" height="150">
        </td>*/

        $tbl8 = '
        '.$tbl1.'
        <table>
            <tr>
                <td width="20%" style="border-left:2px solid #000000;border-right:2px solid #000000;">
                    <table cellpadding="2" border="0" width="100%">
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">HT : '.$company['height'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">Wt (in kgs) : '.$company['weight'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">BMI : '.$company['bmi'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">BP : '.$company['blood_pressure'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">PR : '.$company['pulse_rate'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">RR : '.$company['respiratory_rate'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">SpO2 : '.$company['SpO2'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">Temp-°F : '.$company['temperature'].'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">FBS : '.$fbsVal_notes.'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">PP : '.$ppVal_notes.'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">RBS : '.$rbsVal_notes.'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%">HBA1C : '.$hb1AcVal_notes.'</td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                        <tr>
                            <td width="100%"></td>
                        </tr>
                    </table>
                </td>
                <td width="80%" style="border-right:2px solid #000000;"><br/>'.$tbl5.'<br/><br/><br/>'.$tbl3.'<br/><br/>'.$tbl4.'<br/><br/></td>
            </tr>
            <tr>
                <td colspan="2" width="100%" style="border-top:2px solid #000000;font-size:13px;">
                </td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl8, true, false, false, false, '');
        $download_title = 'Prescription.pdf';
        $pdf->Output($download_title, 'I');
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

        $patient_visit_id       = $fn->getReqParam('patient_visit_id');
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
              ,pv.patient_visit_id
        FROM patient_visit pv
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
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
        $height = 172;
        $patientNameLength = strlen($visit['patient_name']);
        if($patientNameLength > 6){
            $height = $height + 20;
        }

        $husband_name = "";
        if($visit['spuse_name'] != "") {
            $husband_name = "| HN: {$visit['spuse_name']}";
        }

        $tbl1 = '
        <table border="0" cellpadding="1" style="border:1px solid #000000;">
            <tr>
                <td width="100%" align="left">'.ltrim($visit['patient_name'], " ").''.$genderAge.'| VST-'.$visit['visit_code'].'</td>

            </tr>
            <tr>
                <td width="100%">Town/City: '.$visit['address_area'].'</td>
            </tr>
            <tr>
                <td width="100%">Fr: '.$visit['father_name'].' '.$husband_name.'</td>
            </tr>
            <tr>
                <td width="100%" style="line-height:25px;"></td>
            </tr>
        </table>
        ';

        $pdf = new MYPDF_Local('P', 'px', array('302.250', $height), true, 'UTF-8', false);
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
        $pdf->SetMargins(6, 4, 6);
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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
        //$pdf = new MYPDF_Local('P', 'px', array('302.250', '242'), true, 'UTF-8', false);
        //$pdf->AddPage('P', array('100', '100'));
        $pdf->AddPage();
        $pdf->SetFont('Courier', 'B', 10);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $download_title = $visit['visit_code'] . '-Token.pdf';
        $pdf->IncludeJS("print();");
        $pdf->Output($download_title, 'I');

    }

    /**
     *
    **/
    function getPrintOpCard(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $patient_visit_id       = $fn->getReqParam('patient_visit_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');

        $SQL = "
        SELECT pv.check_up_date
              ,pv.check_up_time
              ,p.patient_information_id
              ,p.nric
              ,p.mobile
              ,p.age_year
              ,p.gender
              ,p.email
              ,p.dob
              ,p.father_name
              ,p.spuse_name
              ,p.address_area
              ,p.phone
              ,p.uhid
              ,p.name AS patient_name
              ,pv.visit_code
              ,pv.patient_visit_id
        FROM patient_visit pv
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
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

        if($visit['gender'] != "" && $visit['age_year'] != ""){
            $genderAge = "({$gender}/{$visit['age_year']})";
        }

        elseif ($visit['gender'] != "" && $visit['age_year'] == "") {
            $genderAge = "({$gender})";
        }

        elseif ($visit['gender'] == "" && $visit['age_year'] != "") {
            $genderAge = "({$visit['age_year']})";
        }

        else{
            $genderAge = "";
        }

        //print(strlen($visit['patient_name']));
        $height = 172;
        $patientNameLength = strlen($visit['patient_name']);
        if($patientNameLength > 6){
            $height = $height + 20;
        }

        $husband_name = "";
        if($visit['spuse_name'] != "") {
            $husband_name = "| HN: {$visit['spuse_name']}";
        }
        $images  = '<img src="images/logo.png"  width="70px" height="75px"/>';

        $tbl1 = '
        <table border="0" cellpadding="1" style="border:1px solid #000000;">
            <tr>
                <td width="17%" style="border-bottom:1px solid #000000;">'.$images.'</td>
                <td width="83%" align="center" style="border-bottom:1px solid #000000;">Santhya Speciality Hospital, Madurai - 16</td>
            </tr>
            <tr>
                <td width="100%" align="left">'.ltrim($visit['patient_name'], " ").''.$genderAge.'</td>

            </tr>
            <tr>
                <td width="100%">ID: '.$visit['uhid'].' / M: '.$visit['phone'].'</td>
            </tr>
            <tr>
                <td width="100%">Fr: '.$visit['father_name'].' '.$husband_name.'</td>
            </tr>
            <tr>
                <td width="100%">Town/City: '.$visit['address_area'].'</td>
            </tr>
        </table>
        ';

        $pdf = new MYPDF_Local('P', 'px', array('302.250', $height), true, 'UTF-8', false);
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
        $pdf->SetMargins(12, 4, 6);
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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
        //$pdf = new MYPDF_Local('P', 'px', array('302.250', '242'), true, 'UTF-8', false);
        //$pdf->AddPage('P', array('100', '100'));
        $pdf->AddPage();
        $pdf->SetFont('Courier', 'B', 10);
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
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Lab Report');
        $pdf->SetTitle('Lab Report');

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
        $pdf->AddPage();

        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.uhid
              ,e.first_name
              ,m.title AS medicine
              ,m.instruction
              ,m.dosage
              ,m.route
              ,m.qty
            FROM patient_visit pv
            LEFT JOIN medicines_visit m ON (m.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
            LEFT JOIN (employee_visit ev) ON (ev.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE pv.patient_visit_id = '{$patient_visit_id}'
            ORDER BY m.medicines_visit_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('','B', 9);

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
            $visit_code = 'VST-'.$company['visit_code'];
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
        if($company['first_name'] == 'DR.SHEIK ABDUL KHADER'){
            $doctorName = $company['first_name'];
        }
        
        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td width="80%">UHID : '.$company['uhid'].'  &nbsp;&nbsp;&nbsp; Pt Name : '.$company['patient_name'].'('.$gender .'/'.$age.')</td>
                <td width="20%" align="left">Date &nbsp;: '.$currentDate.'</td>
            </tr>
            <tr>
                <td width="80%" align="">Doctor : '.$cpCfg['cp.doctorNamePDF'].'</td>
                <td width="20%" align="left">Code : '.$visit_code.'</td>
            </tr>
        </table>
        <table border="0" width="109%">
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
        FROM medical_test_visit mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.patient_visit_id = '{$patient_visit_id}'
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
            <tr><th colspan="3" style="text-decoration:underline;">Reports:</th></tr>
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
                    <td width="100%" align="left" style="line-height:30px;font-size:11px;">'.$row1['title'].'</td>
                </tr>
                ';
            }else{
                $tbl4 = $tbl4.'
                <tr>'.$marginTop.'
                    <td width="35%" align="left" style="line-height:30px;font-size:11px;">'.$row1['title'].'</td>
                    <td width="25%" align="left" style="line-height:30px;" >'.$row1['notes'].'</td>
                    <td width="13%" align="left" style="line-height:30px;">'.$row1['units'].'</td>
                    <td width="27%" align="left" style="line-height:30px;">'.$row1['normal_value'].'</td>
                </tr>
                ';
            }

            while ($rowMTP = $db->sql_fetchrow($resultMTP)) {
                $medVisitParaRec = $fn->getRecordByCondition('medical_visit_parameter', "medical_test_parameter_id = '{$rowMTP['medical_test_parameter_id']}' AND medical_test_id='{$rowMTP['medical_test_id']}' AND patient_visit_id = {$patient_visit_id}");
                $tbl4 = $tbl4.'<tr>
                            <td width="35%" align="left">'.strtoupper($rowMTP['title']).'</td>
                            <td width="25%" align="left">'.$medVisitParaRec['notes'].'</td>
                            <td width="19%" align="left" style="font-size:9px;">'.$rowMTP['units'].'</td>
                            <td width="21%" align="left" style="font-size:9px;">'.$rowMTP['normal_value'].'</td>
                        </tr>
                        ';
            }
        }

        $tbl4 = $tbl4.'</table>';

        $pdf->writeHTML($tbl1, false, false, false, false, '');
        //$pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tblHead, true, false, false, false, '');
        //$pdf->writeHTML($tbl3, false, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
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
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Lab Request Form');
        $pdf->SetTitle('Lab Request Form');

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
        $pdf->AddPage();

        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.uhid
              ,e.first_name
              ,m.title AS medicine
              ,m.instruction
              ,m.dosage
              ,m.route
              ,m.qty
            FROM patient_visit pv
            LEFT JOIN medicines_visit m ON (m.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
            LEFT JOIN (employee_visit ev) ON (ev.patient_visit_id = pv.patient_visit_id)
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE pv.patient_visit_id = '{$patient_visit_id}'
            ORDER BY m.medicines_visit_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('','B', 9);

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
            $visit_code = 'VST-'.$company['visit_code'];
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
        if($company['first_name'] == 'DR.SHEIK ABDUL KHADER'){
            $doctorName = $company['first_name'];
        }
        
        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td width="80%">UHID : '.$company['uhid'].'  &nbsp;&nbsp;&nbsp; Pt Name : '.$company['patient_name'].'('.$gender .'/'.$age.')</td>
                <td width="20%" align="left">Date &nbsp;: '.$currentDate.'</td>
            </tr>
            <tr>
                <td width="80%" align="">Doctor : '.$cpCfg['cp.doctorNamePDF'].'</td>
                <td width="20%" align="left">Code : '.$visit_code.'</td>
            </tr>
        </table>
        <table border="0" width="109%">
            <tr>
                <td align="right">Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().'</td>
            </tr>
        </table>
        ';

        $SQL1 = "
        SELECT mt.*
              ,m.normal_value
              ,mt.fees AS amount
        FROM medical_test_visit mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.patient_visit_id = '{$patient_visit_id}'
        ";
        $result1 = $db->sql_query($SQL1);
        $total_amount = 0;
        $tbl4 = '
        <table cellpadding="4" border="0" width="100%">
            <thead>
            <tr><th colspan="2" style="text-decoration:underline;">Lab Test Required:</th></tr>
            <tr bgcolor="#D3D3D3">
                <th style="border-left:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="70%">Test Name</th>
                <th align="right" style="border-right:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="30%">Fees (Rs)</th>
            </tr>
            </thead>
        ';
        while ($row1 = $db->sql_fetchrow($result1)) {

            $tbl4 = $tbl4.'<tr>
                                <td align="left" width="70%">'.$row1['title'].'</td>
                                <td align="right" width="30%">'.$row1['amount'].'</td>
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
                            <td align="left" style="font-size:9px;" width="70%">'.strtoupper($rowMTP['title']).'</td>
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

    function getPatVisitRecWithInvCancelled() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        //print 'function working' . '<br/>';
        //http://habibiahms.cubosale.in/admin/index.php?_topRm=main&module=hms_patientVisit&_spAction=patVisitRecWithInvCancelled&showHTML=0
        $SQL = "
        SELECT p.*
        FROM patient_visit p
        WHERE p.status = 'Closed'
          AND p.order_id != ''
          AND p.check_up_date BETWEEN '2017-12-01' AND '2017-12-22' 
          AND p.site_id =1
        ";
        $result   = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $SQLPaid = "
            SELECT i.*
            FROM invoice i
            WHERE i.status = 'Paid'
              AND i.order_id = {$row['order_id']}
            ";
            $resultPaid   = $db->sql_query($SQLPaid);

            $numRowsPaid = $db->sql_numrows($resultPaid);
            if($numRowsPaid < 1){
                $SQL2 = "
                SELECT i.*
                FROM invoice i
                WHERE i.status = 'Cancelled'
                  AND i.order_id = {$row['order_id']}
                ";
                $result2   = $db->sql_query($SQL2);                
                $row2 = $db->sql_fetchrow($result2);
                if($row2['order_id'] != ''){
                    $SQL3 = "
                    SELECT p.*
                    FROM patient_visit p
                    WHERE p.order_id = {$row2['order_id']}
                    ";
                    $result3   = $db->sql_query($SQL3);
                    $row3 = $db->sql_fetchrow($result3);

                    $SQLUpdate ="
                    UPDATE patient_visit SET status = 'Visited' WHERE patient_visit_id = {$row3['patient_visit_id']}
                    ";
                    //$resultUpdate   = $db->sql_query($SQLUpdate);

                    print $count.' - Status - '.$row3['status'].'- Code -'.$row3['visit_code'].'<br/>';
                    $count++;
                }
            }
        }
    }
}