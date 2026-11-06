<?
class CPL_Admin_Modules_Hms_PatientVisit_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT DISTINCT pv.patient_visit_id
              ,pv.visit_code
              ,pv.check_up_date
              ,pv.status
              ,pv.patient_information_id
              ,pv.flag
              ,pv.created_by
              ,pv.creation_date
              ,pv.modified_by
              ,pv.modification_date
              ,pv.company_name
              ,pv.bill_type
              ,pv.check_up_time
              ,pv.dr_required
              ,pv.employee_id
              ,pv.weight
              ,pv.temperature
              ,pv.pulse_rate
              ,pv.respiratory_rate
              ,pv.blood_pressure
              ,pv.crt
              ,pv.on_behalf
              ,pv.referral_doctor_id
              ,pv.notes
              ,pv.review_date
              ,pv.visited_by_referral_doctor
              ,pv.dne_report_notes
              ,pv.register_fees
              ,p.uhid
              ,p.name AS patient_name
              ,p.first_name
              ,p.middle_name
              ,p.last_name
              ,p.name
              ,p.nric
              ,p.email
              ,p.mobile
              ,p.dob
              ,p.patient_code
              ,p.father_name
              ,p.spuse_name
              ,p.address_area
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.phone AS patient_phone
              ,p.previous_medical_history
              ,pv.allergy
              ,pv.height
              ,pv.bmi
              ,pv.SpO2
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM patient_visit pv
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN (employee e) ON (e.employee_id = pv.employee_id)
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
        $searchVar->mainTableAlias = 'pv';

        $status             = $fn->getReqParam('status');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $employee_category  = $fn->getReqParam('employee_category');
        $check_up_date1     = $fn->getReqParam('check_up_date_1');
        $check_up_date2     = $fn->getReqParam('check_up_date_2');
        $employee_id        = $fn->getReqParam('employee_id');
        $referral_doctor_id = $fn->getReqParam('referral_doctor_id');
        $currentDate        = date("Y-m-d");
        $previousDate       = date("Y-m-d", strtotime("yesterday"));
        $yesterday          = $fn->getReqParam('yesterday');
        //$company_name = $fn->getReqParam('company_name');
        $investigation  = $fn->getReqParam('investigation');

        if ($patient_visit_id != "") {
            $searchVar->sqlSearchVar[] = "pv.patient_visit_id = '{$patient_visit_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pv.patient_visit_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'pv.patient_visit_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "pv.status = '{$status}'";
            } else{
                $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
            }

            if ($employee_id != "") {
                $searchVar->sqlSearchVar[] = "pv.employee_id = '{$employee_id}'";
            }

            if ($referral_doctor_id != "") {
                $searchVar->sqlSearchVar[] = "pv.referral_doctor_id = '{$referral_doctor_id}'";
            }

            if ($check_up_date1 != "" && $check_up_date2 != "") {
                $searchVar->sqlSearchVar[] = "(pv.check_up_date BETWEEN '{$check_up_date1}' AND '{$check_up_date2}')";
            }

            /*if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.name      LIKE '{$tv['keyword']}%'
                    OR p.email        LIKE '%{$tv['keyword']}%'
                    OR p.mobile       LIKE '{$tv['keyword']}%'
                    OR p.address_area LIKE '{$tv['keyword']}%'
                    OR pv.visit_code  LIKE '%{$tv['keyword']}%'
                )";
            }

             //date(“D d-M-Y”, strtotime(“yesterday”));

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            if ($yesterday == "Yesterday") {
                $searchVar->sqlSearchVar[] = "(pv.check_up_date BETWEEN '{$previousDate}' AND '{$previousDate}')";
            }
            if ($yesterday == "Review Records") {
                $searchVar->sqlSearchVar[] = "pv.review_date != ''";
            }

            if ($employee_category != ""){
                $searchVar->sqlSearchVar[] = "pv.employee_id IN (SELECT ev.employee_id FROM `employee_visit` ev
                                                                 LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                                                                 WHERE e.category = '{$employee_category}'
                                                                 GROUP BY ev.employee_id)";
            }

            if ($investigation != ""){
                $searchVar->sqlSearchVar[] = "pv.patient_visit_id IN (SELECT mv.patient_visit_id FROM `medical_test_visit` mv
                                                                 LEFT JOIN medical_test mt ON (mt.medical_test_id = mv.medical_test_id)
                                                                 WHERE mt.category = '{$investigation}'
                                                                 GROUP BY mv.patient_visit_id)";
            }

            $searchVar->sortOrder = "pv.check_up_date DESC, pv.patient_visit_id DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('check_up_date', 'Please select the patient check up date');

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

        //$visit_code = $fn->getSettingsValueByKey("nextPatientvisitCode");
        $fa = $this->getFields();
        //$fa['visit_code'] = $visit_code;

        $id = $fn->addRecord($fa);
        //To update patient visit code
        //$SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatientvisitCode'";
        //$resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);

    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();
        //$validate->validateData('bill_type', 'Please select the Bill Type');
        /*$appointment_check_up_date = $fn->getPostParam('appointment_check_up_date');
        $patient_information_id    = $fn->getReqParam('patient_information_id');
        $appointment_check_up_time = $fn->getPostParam('appointment_check_up_time');

        $validate->resetErrorArray();
        $appendSqlAp = '';
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlAp = "AND site_id = {$cpSiteIdSession}";
        }

        if($appointment_check_up_date != ''){
            $SQL = "
            SELECT patient_information_id
            FROM appointment
            WHERE patient_information_id = {$patient_information_id}
            AND check_up_date = '{$appointment_check_up_date}'
            AND check_up_time = '{$appointment_check_up_time}'
            {$appendSqlAp}
            ";
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if($numRows > 0){
                $validate->errorArray['appointment_check_up_time']['name'] = "appointment_check_up_time";
                $validate->errorArray['appointment_check_up_time']['msg']  = "Appointment already Created for this time";
            }
        }*/

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
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        /*if($fa['follow_up_date'] != '' ){
            $followUpRec = $fn->getRecordByCondition('follow_up_patient', "patient_visit_id = '{$patient_visit_id}' AND record_type = 'Follow Up'");

            $fa1 = array();
           // $fa1['description']      = $fa['follow_up_notes'];
            $fa1['follow_up_date']   = $fa['follow_up_date'];
            $fa1['follow_up_time']   = date("H:i:s");

            if($followUpRec['follow_up_patient_id'] == ''){
                $fa1['record_type']      = 'Follow Up';
                $fa1['patient_visit_id'] = $patient_visit_id;
                $fa1['patient_information_id'] = $patientVisitRec['patient_information_id'];
                $fa1['employee_id']      = $employeeVisitRec['employee_id'];

                if ($cpCfg['cp.hasMultiUniqueSites']) {
                  $fa1['site_id']        = $cpSiteIdSession;
                }

                $fa1['creation_date']    = date("Y-m-d H:i:s");
                $fa1['created_by']       = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa1, 'follow_up_patient');
                $result = $db->sql_query($SQL);
            } else {
                $fa1['modification_date']    = date("Y-m-d H:i:s");
                $fa1['modified_by']       = $fn->getSessionParam('userName');

                $whereCondition = "
                WHERE follow_up_patient_id = {$followUpRec['follow_up_patient_id']}
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'follow_up_patient', $whereCondition);
                $db->sql_query($SQL);
            }
        }*/

        $consultation_fees = $fn->getPostParam('consultation_fees');
        if($consultation_fees == ''){
            $consultation_fees = $fn->getReqParam('consultation_fees');
        }
        
        $employee_visit_id = $fn->getReqParam('employee_visit_id');
        
        $faEV['consultation_fees'] = $consultation_fees;
        $faEV['employee_id']       = $fa['employee_id'];
        
        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        AND employee_visit_id = {$employee_visit_id}
        ";
        
        $SQLEV = $dbUtil->getUpdateSQLStringFromArray($faEV, 'employee_visit', $whereCondition);
        $db->sql_query($SQLEV);

        $SQLEmpVisit = "
        SELECT ev.consultation_fees
               ,ev.employee_visit_id
               ,e.fees_commission_type
               ,e.fees_commission
        FROM employee_visit ev
        LEFT JOIN `employee` e ON (e.employee_id = ev.employee_id)
        WHERE ev.employee_visit_id = {$employee_visit_id}
        ORDER BY ev.employee_visit_id ASC
        ";
        $resultEmpVisit = $db->sql_query($SQLEmpVisit);
        $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

        $fees_commission = 0;
        if($consultation_fees > 0){
            if($rowEmpVisit['fees_commission_type'] == "%"){
                $fees_commission = ($rowEmpVisit['consultation_fees'] * $rowEmpVisit['fees_commission']) / 100;
            }else{
                $fees_commission = $rowEmpVisit['fees_commission'];
            }
        }

        if($fees_commission == ''){
          $fees_commission = 0;
        }

        $SQLEmployeeVisit ="
        UPDATE employee_visit set fees_commission = {$fees_commission} WHERE employee_visit_id = {$employee_visit_id}
        ";
        $resultEmployeeVisit = $db->sql_query($SQLEmployeeVisit);

        /*if($fa['longtime_follow_up_date'] != '' ){
            $followUpRec = $fn->getRecordByCondition('follow_up_patient', "patient_visit_id = '{$patient_visit_id}' AND record_type = 'Longtime Follow Up'");

            $fa1 = array();
            $fa1['description']      = $fa['longtime_follow_up_notes'];
            $fa1['follow_up_date']   = $fa['longtime_follow_up_date'];
            $fa1['follow_up_time']   = date("H:i:s");

            if($followUpRec['follow_up_patient_id'] == ''){
                $fa1['record_type']      = 'Longtime Follow Up';
                $fa1['patient_visit_id'] = $patient_visit_id;
                $fa1['patient_information_id'] = $patientVisitRec['patient_information_id'];
                $fa1['employee_id'] = $employeeVisitRec['employee_id'];

                if ($cpCfg['cp.hasMultiUniqueSites']) {
                  $fa1['site_id']        = $cpSiteIdSession;
                }

                $fa1['creation_date']    = date("Y-m-d H:i:s");
                $fa1['created_by']       = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa1, 'follow_up_patient');
                $result = $db->sql_query($SQL);
            } else {
                $fa1['modification_date'] = date("Y-m-d H:i:s");
                $fa1['modified_by']       = $fn->getSessionParam('userName');

                $whereCondition = "
                WHERE follow_up_patient_id = {$followUpRec['follow_up_patient_id']}
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa1, 'follow_up_patient', $whereCondition);
                $db->sql_query($SQL);
            }
        }*/

        $appointment_check_up_date = $fn->getPostParam('appointment_check_up_date');
        if($appointment_check_up_date != '' ){
            $patient_information_id    = $fn->getReqParam('patient_information_id');
            $appointment_check_up_time = $fn->getPostParam('appointment_check_up_time');
            $appointment_description   = $fn->getPostParam('appointment_description');
            $appointment_employee_id   = $fn->getPostParam('appointment_employee_id');

            /*$SQLEmpVisit = "
            SELECT employee_id
            FROM employee_visit
            WHERE patient_visit_id = {$patient_visit_id}
            ORDER BY employee_visit_id
            ";
            $resultEmpVisit = $db->sql_query($SQLEmpVisit);
            $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);*/

            $fa3 = array();

            $fa3['patient_information_id']  = $patient_information_id;
            $fa3['dr_Linked']               = $appointment_employee_id;
            $fa3['check_up_date']           = $appointment_check_up_date;
            $fa3['check_up_time']           = $appointment_check_up_time;
            $fa3['source_patient_visit_id'] = $patient_visit_id;
            $fa3['status']         = 'New';

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa3['site_id'] = $cpSiteIdSession;
            }

            $fa3['description']            = $appointment_description;

            $appointmentRec = $fn->getRecordByCondition('appointment', "source_patient_visit_id = '{$patient_visit_id}'");

            if(is_array($appointmentRec)){
                $fa3['modification_date']   = date("Y-m-d H:i:s");
                $fa3['modified_by']         = $fn->getSessionParam('userName');

                $whereCondition = "WHERE appointment_id = {$appointmentRec['appointment_id']}";
                $sqlAppointmentUpdate    = $dbUtil->getUpdateSQLStringFromArray($fa3, "appointment", $whereCondition);
                $resultAppointmentUpdate = $db->sql_query($sqlAppointmentUpdate);
            } else {
                $fa3['creation_date'] = date("Y-m-d H:i:s");
                $fa3['created_by']    = $fn->getSessionParam('userName');

                $insertAppointmentSQL = $dbUtil->getInsertSQLStringFromArray($fa3, "appointment");
                $resultAppointmentSQL = $db->sql_query($insertAppointmentSQL);
            }

        }

        $SQLOrderCheck = "
        SELECT order_id
        FROM `order`
        WHERE patient_visit_id = {$patientVisitRec['patient_visit_id']}
        ";
        $resultOrderCheck  = $db->sql_query($SQLOrderCheck);
        $numRowsOrderCheck = $db->sql_numrows($resultOrderCheck);
        $rowOrderCheck     = $db->sql_fetchrow($resultOrderCheck);

        if($patientVisitRec['company_id'] != ''){
            $sqlCompany = "
            SELECT company_id
                  ,company_name
                  ,address_flat
                  ,address_street
                  ,address_town
                  ,address_state
                  ,address_country
                  ,phone
            FROM company
            WHERE category = '{$patientVisitRec['bill_type']}'
            AND company_id = {$patientVisitRec['company_id']}
            ORDER BY company_name
            ";
            $resultCompany = $db->sql_query($sqlCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);

            $faComp = array();
            $faComp['company_name']     = $rowCompany['company_name'];
            $faComp['address_flat']     = $rowCompany['address_flat'];
            $faComp['address_street']   = $rowCompany['address_street'];
            $faComp['address_town']     = $rowCompany['address_town'];
            $faComp['address_state']    = $rowCompany['address_state'];
            $faComp['address_country']  = $rowCompany['address_country'];
            $faComp['phone']            = $rowCompany['phone'];

            $whereCondition = "
            WHERE patient_visit_id = {$patientVisitRec['patient_visit_id']}
            ";
            $updateComName = $dbUtil->getUpdateSQLStringFromArray($faComp, 'patient_visit', $whereCondition);
            $resultComName = $db->sql_query($updateComName);

            if($numRowsOrderCheck > 0){
                $faOrder = array();
                $faOrder['company_id']                    = $patientVisitRec['company_id'];
                $faOrder['company_name']                  = $patientVisitRec['company_name'];
                $faOrder['cust_address1']                 = $patientVisitRec['address_flat'];
                $faOrder['cust_address2']                 = $patientVisitRec['address_street'];
                $faOrder['cust_address_city']             = $patientVisitRec['address_town'];
                $faOrder['cust_address_state']            = $patientVisitRec['address_state'];
                $faOrder['cust_address_country_code']     = $patientVisitRec['address_country'];
                $faOrder['cust_phone']                    = $patientVisitRec['phone'];
                $faOrder['bill_type']                     = $patientVisitRec['bill_type'];

                $whereCondition = "
                WHERE order_id = {$rowOrderCheck['order_id']}
                ";
                $updateOrder = $dbUtil->getUpdateSQLStringFromArray($faOrder, 'order', $whereCondition);
                $resultOrder = $db->sql_query($updateOrder);
            }

        }else{
            $updateComName = "
            UPDATE patient_visit SET company_name = '' , address_flat = '', address_street = '', address_town = '', address_state = '', address_country = '', phone = ''
            WHERE patient_visit_id = {$patientVisitRec['patient_visit_id']}
            ";
            $resultComName = $db->sql_query($updateComName);

            if($numRowsOrderCheck > 0){
                $updateOrder = "
                UPDATE `order` SET company_id = '' , company_name = '', cust_address1 = '', cust_address2 = '', cust_address_city = '', cust_address_state = '', cust_address_country_code = '', cust_phone = ''
                WHERE order_id = {$rowOrderCheck['order_id']}
                ";
                $resultOrder = $db->sql_query($updateOrder);
            }
        }

        /*TO UPDATE PATIENT INFO RECORD*/

        $fa4 = array();
        $fa4['name']  = $fn->getPostParam('name');
        $fa4['phone']  = $fn->getPostParam('patient_phone');
        $fa4['gender']  = $fn->getPostParam('gender');
        $fa4['father_name']  = $fn->getPostParam('father_name');
        $fa4['spuse_name']  = $fn->getPostParam('spuse_name');
        $fa4['address_area']  = $fn->getPostParam('address_area');
        $fa4['age_year']  = $fn->getPostParam('age_year');
        $fa4['age_month']  = $fn->getPostParam('age_month');
        $fa4['age_day']  = $fn->getPostParam('age_day');
        $fa4['previous_medical_history']  = $fn->getPostParam('previous_medical_history');
        $fa4['height']  = $fn->getPostParam('height');
        $fa4['weight']  = $fn->getPostParam('weight');
        $fa4['bmi']  = $fn->getPostParam('bmi');

        $whereCondition = "
        WHERE patient_information_id = {$patientVisitRec['patient_information_id']}
        ";
        $updatePI = $dbUtil->getUpdateSQLStringFromArray($fa4, 'patient_information', $whereCondition);
        $resultPI = $db->sql_query($updatePI);

        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }
    
    
       function getMedicalTestRecordPrintUpdate(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $print_label = $fn->getReqParam('print_label');
        $medical_test_id = $fn->getReqParam('medical_test_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
     

        $SQL = "
        UPDATE medical_test_visit SET print_label = '{$print_label}'
        WHERE patient_visit_id = {$patient_visit_id} 
          AND medical_test_id = {$medical_test_id}
        ";
        $result = $db->sql_query($SQL);

       
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
        $fa = $fn->addToFieldsArray($fa, 'check_up_date');
        $fa = $fn->addToFieldsArray($fa, 'check_up_time');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'visit_summary');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'longtime_follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_notes');
        $fa = $fn->addToFieldsArray($fa, 'longtime_follow_up_notes');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_value');
        $fa = $fn->addToFieldsArray($fa, 'longtime_follow_up_value');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'middle_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'nric');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'dr_required');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'no_of_days');
        $fa = $fn->addToFieldsArray($fa, 'bill_type');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'weight');
        $fa = $fn->addToFieldsArray($fa, 'temperature');
        $fa = $fn->addToFieldsArray($fa, 'pulse_rate');
        $fa = $fn->addToFieldsArray($fa, 'respiratory_rate');
        $fa = $fn->addToFieldsArray($fa, 'blood_pressure');
        $fa = $fn->addToFieldsArray($fa, 'crt');
        $fa = $fn->addToFieldsArray($fa, 'on_behalf');
        $fa = $fn->addToFieldsArray($fa, 'referral_doctor_id');
        $fa = $fn->addToFieldsArray($fa, 'visited_by_referral_doctor');        
        $fa = $fn->addToFieldsArray($fa, 'review_date');        
        $fa = $fn->addToFieldsArray($fa, 'allergy');        
        $fa = $fn->addToFieldsArray($fa, 'height');        
        $fa = $fn->addToFieldsArray($fa, 'bmi');        
        $fa = $fn->addToFieldsArray($fa, 'SpO2');
        $fa = $fn->addToFieldsArray($fa, 'dne_report_notes');   
        $fa = $fn->addToFieldsArray($fa, 'register_fees');   

        return $fa;
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
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'hms_company'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }
    /**
     *
     */
    function getHmsCompanyHmsContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
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
    function getHmsCompanyHmsDiscountLinkSQL($id) {

        return "
        SELECT d.discount_id
              ,pg.title
              ,c.title AS category_title
              ,d.margin
              ,d.discount_percent
        FROM discount d
        LEFT JOIN (product_group pg) ON (d.product_group_id = pg.product_group_id)
        LEFT JOIN (category c) ON (d.category_id = c.category_id)
        WHERE d.company_id = {$id}
        ORDER BY pg.sort_order
        ";
    }

    /**
     *
     */
    function getHmsCompanyHmsCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.company_id
              ,a.company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }

    /**
     *
     */
    function getAddLabsRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $validate->validateData('supplier_category', 'Please select category');
        $validate->validateData('supplier_id', 'Please select supplier');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddLabsRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddLabsRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $cpSiteIdSession    = $fn->getSessionParam('cp_site_id');
        $labs_code          = $fn->getSettingsValueByKey("nextLabsCode");

        $supplierName = "
        SELECT title
        FROM labs_supplier
        WHERE labs_supplier_id = {$supplier_id}
        ";
        $resultSupplier = $db->sql_query($supplierName);
        $rowSupplier    = $db->sql_fetchrow($resultSupplier);

        $fa = array();
        $fa['labs_code']         = $labs_code;
        $fa['labs_date']         = date('Y-m-d');
        $fa['title']             = $rowSupplier['title'];
        $fa['supplier_id']       = $supplier_id;
        $fa['status']            = 'new';
        $fa['supplier_category'] = $supplier_category;
        $fa['patient_visit_id']  = $patient_visit_id;
        $fa['patient_information_id']  = $patient_information_id;
        $fa['site_id']           = $cpSiteIdSession;
        $fa['creation_date']     = date("Y-m-d H:i:s");
        $fa['created_by']        = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'labs');
        $result = $db->sql_query($SQL);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        //To update patient code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextLabsCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }


    /**
     *
     */
    function getEditLabsRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $validate->validateData('supplier_category', 'Please select category');
        $validate->validateData('supplier_id', 'Please select supplier');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditLabsRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditLabsRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $supplier_category  = $fn->getPostParam('supplier_category');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $labs_id            = $fn->getReqParam('labs_id');

        $fa = array();
        $fa['supplier_id']       = $supplier_id;
        $fa['supplier_category'] = $supplier_category;
        $fa['modification_date'] = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "WHERE labs_id = {$labs_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'labs', $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddDoctorRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddDoctorRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $consultation_fees  = $fn->getPostParam('consultation_fees');
        $consultation_room  = $fn->getPostParam('consultation_room');
        $notes              = $fn->getPostParam('notes');
        $employee_id        = $fn->getReqParam('employee_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['notes']             = $notes;
        $fa['employee_id']       = $employee_id;
        $fa['patient_visit_id']  = $patient_visit_id;
        $fa['consultation_room'] = $consultation_room;
        $fa['consultation_fees'] = $consultation_fees;
        $fa['creation_date']     = date("Y-m-d H:i:s");
        $fa['created_by']        = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'employee_visit');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddDoctorRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $employee_id = $fn->getPostParam('employee_id');
        $patient_visit_id   = $fn->getPostParam('patient_visit_id');

        $recCount = $fn->getRecordCount('employee_visit', "employee_id = '{$employee_id}' AND patient_visit_id = '{$patient_visit_id}'");
        $validate->validateData('employee_id', 'Please select Doctor/Nurse');

        if($recCount > 0){
            $validate->errorArray['employee_id']['name'] = "employee_id";
            $validate->errorArray['employee_id']['msg']  = "Doctor/Nurse already added";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditDoctorRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditDoctorRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $consultation_room  = $fn->getPostParam('consultation_room');
        $consultation_fees  = $fn->getPostParam('consultation_fees');
        $notes              = $fn->getPostParam('notes');
        $employee_id        = $fn->getReqParam('employee_id');
        $employee_visit_id   = $fn->getReqParam('employee_visit_id');

        $fa = array();
        $fa['notes']             = $notes;
        $fa['employee_id']       = $employee_id;
        $fa['consultation_room'] = $consultation_room;
        $fa['consultation_fees'] = $consultation_fees;
        $fa['modification_date']    = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "
        WHERE employee_visit_id = {$employee_visit_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'employee_visit', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditDoctorRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getAddPatientRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddPatientRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $first_name       = $fn->getPostParam('first_name');
        $middle_name      = $fn->getPostParam('middle_name');
        $last_name        = $fn->getPostParam('last_name');
        $phone            = $fn->getPostParam('phone');
        $email            = $fn->getPostParam('email');
        $dob              = $fn->getPostParam('dob');
        $gender           = $fn->getPostParam('gender');
        $registration_no  = $fn->getPostParam('registration_no');
        $first_admit      = $fn->getPostParam('first_admit');
        $bill_type        = $fn->getPostParam('bill_type');
        $company_id       = $fn->getPostParam('company_id');
        $address_street   = $fn->getPostParam('address_street');
        $address_area     = $fn->getPostParam('address_area');
        $address_city     = $fn->getPostParam('address_city');
        $address_code     = $fn->getPostParam('address_code');
        $address_country  = $fn->getPostParam('address_country');
        $pass_type        = $fn->getPostParam('pass_type');
        $nric             = $fn->getPostParam('nric');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "WHERE site_id = {$cpSiteIdSession}";
        }

        $SQLPatientCode = "
        SELECT MAX(CONVERT(patient_code, UNSIGNED INTEGER))+ 1 AS patient_code
        FROM patient_information
        {$appendSql}
        ";
        $resultPatientCode = $db->sql_query($SQLPatientCode);
        $rowPatientCode    = $db->sql_fetchrow($resultPatientCode);

        if($rowPatientCode['patient_code'] != ""){
            $patient_code = $rowPatientCode['patient_code'];
        }
        else{
            $patient_code = "1000";
        }

        $fa = array();
        $fa['patient_code']    = $patient_code;
        $fa['first_name']      = strtoupper($first_name);
        $fa['middle_name']     = strtoupper($middle_name);
        $fa['last_name']       = strtoupper($last_name);
        $fa['email']           = $email;
        $fa['registration_no'] = $registration_no;
        $fa['first_admit']     = $first_admit;
        $fa['bill_type']       = $bill_type;
        $fa['company_id']      = $company_id;
        $fa['address_street']  = $address_street;
        $fa['address_area']    = $address_area;
        $fa['address_city']    = $address_city;
        $fa['address_code']    = $address_code;
        $fa['address_country'] = $address_country;
        $fa['creation_date']   = date("Y-m-d H:i:s");
        $fa['created_by']      = $fn->getSessionParam('userName');

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id']  = $cpSiteIdSession;
        }

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_information');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddPatientRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $pass_type  = $fn->getPostParam('pass_type');
        $nric       = $fn->getPostParam('nric', '', true);
        $phone      = $fn->getPostParam('phone');
        $email      = $fn->getPostParam('email');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please Enter First Name');
        $validate->validateData('nric', 'Please Enter NRIC');

        if ($phone != '') {
            $validate->validateData("phone",  'Please enter only number eg: 85414745', "number");
        }

        if ($email != '') {
            $validate->validateData("email",  'Please enter the correct email address with @ and .', 'email');
        }

        if ($pass_type == 'NRIC') {
            $validate->validateData("nric",  'Please enter 12 digit NRIC without -', "number", "", 12, 12);

            $nric1 = substr($nric, 0, 6);
            $nric2 = substr($nric, 6, 2);
            $nric3 = substr($nric, 8, 4);

            $nric_final = $nric1 . '-' . $nric2 . '-' . $nric3;
            if ($nric != ''){
                $rec = $fn->getRecordByCondition('patient_information', "nric = '{$nric_final}'");
                $expNRIC = array('displayText' => 'click here', 'target' => '_blank');
                $NRIClink = $fn->getRecordDetailLink('hms_patientInformation', 'record_id', $rec['patient_information_id'], $expNRIC);

                if (is_array($rec)){
                    $validate->errorArray['nric']['name'] = "nric";
                    $validate->errorArray['nric']['msg']  = "NRIC already exist in system, please '{$NRIClink}'to check the detail";
                }
            }
        } else if ($pass_type == 'Passport') {
            $validate->validateData('registration_no', 'Please enter Passport No');
            $validate->validateData('dob', 'Please enter DOB');
        } else if ($pass_type == 'DOB') {
            $validate->validateData('dob', 'Please enter DOB');
        }

        /*
        $nric = $fn->getPostParam('nric', '', true);
        $nric = str_replace('-', '', $nric);

        if ($nric != ''){
            $rec = $fn->getRecordByCondition('patient_information', "REPLACE(nric, '-', '') = '{$nric}'");
            $expNRIC = array('displayText' => 'click here', 'target' => '_blank');
            $NRIClink = $fn->getRecordDetailLink('hms_patientInformation', 'record_id', $rec['patient_information_id'], $expNRIC);

            if (is_array($rec)){
                $validate->errorArray['nric']['name'] = "nric";
                $validate->errorArray['nric']['msg']  = "NRIC already exist in system, please '{$NRIClink}'to check the detail";

            }
        }
        */

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddLabRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddLabRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $title            = $fn->getPostParam('title');
        $notes            = $fn->getPostParam('notes');
        $employee_id      = $fn->getReqParam('employee_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['title']            = $title;
        $fa['employee_id']      = $employee_id;
        $fa['patient_visit_id'] = $patient_visit_id;
        $fa['notes']            = $notes;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'lab_visit');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddLabRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getEditLabRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditLabRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $title       = $fn->getPostParam('title');
        $notes = $fn->getPostParam('notes');
        $employee_id = $fn->getReqParam('employee_id');
        $lab_visit_id = $fn->getReqParam('lab_visit_id');

        $fa = array();
        $fa['title']            = $title;
        $fa['employee_id']      = $employee_id;
        $fa['notes']      = $notes;
        $fa['modification_date']    = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "
        WHERE lab_visit_id = {$lab_visit_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'lab_visit', $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditLabRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getVaccinationRecordSubmitold() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getVaccinationValidate()){
            return $validate->getErrorMessageXML();
        }

        $titles             = $fn->getPostParam('title', array());
        $fees_arr           = $fn->getPostParam('fees', array());
        $medicaltest_id_arr = $fn->getPostParam('medical_test_id', array());
        $group_name_arr     = $fn->getPostParam('group_name', array());
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        //$vaccination_date_arr           = $fn->getPostParam('vaccination_date', array());
        //$notes_arr          = $fn->getPostParam('notes', array());

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $check_up_date   = $patientVisitRec['check_up_date'];
        $patient_information_id   = $patientVisitRec['patient_information_id'];

        $labRec = $fn->getRecordByCondition('vaccination_visit', "patient_visit_id = '{$patient_visit_id}'");
        if($labRec['vaccination_visit_id'] != ''){
            $SQLDelete = "DELETE FROM vaccination_visit WHERE patient_visit_id = {$patient_visit_id}";
            $db->sql_query($SQLDelete);
        }

        $count = count($titles);
        for ($i= 0; $i < $count; $i++) {

            $title          = $titles[$i];
            $title_explode  = explode('_', $title);
            $fees           = $fees_arr[$title_explode[1]];
            $medicaltest_id = $medicaltest_id_arr[$title_explode[1]];
            $group_name     = $group_name_arr[$title_explode[1]];
            //$notes          = $notes_arr[$title_explode[1]];
            //$vaccination_date  = $vaccination_date_arr[$title_explode[1]];
            $dueDate = $fn->getPostParam('due_date_'.$medicaltest_id);

            if ($title) {
                $fa = array();
                $fa['title']                  = $title_explode[0];
                $fa['fees']                   = $fees;
                $fa['medical_test_id']        = $medicaltest_id;
                $fa['patient_visit_id']       = $patient_visit_id;
                $fa['patient_information_id'] = $patient_information_id;
                $fa['vaccination_date']       = date("Y-m-d");
                $fa['creation_date']          = date("Y-m-d");
                $fa['created_by']             = $fn->getSessionParam('userName');
                $fa['due_date']               = $dueDate;
                $fa['group_name']             = $group_name;

                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $fa['site_id'] = $cpSiteIdSession;
                }

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'vaccination_visit');
                $result = $db->sql_query($SQL);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getVaccinationRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $title   = $fn->getReqParam('title');
        $medical_test_id   = $fn->getReqParam('medical_test_id');
        $patient_information_id   = $fn->getReqParam('patient_information_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $group_name   = $fn->getReqParam('group_name');
        $medicalTestRec = $fn->getRecordRowByID('medical_test', 'medical_test_id', $medical_test_id);

        $fa = array();
        $fa['title']                  = $medicalTestRec['title'];
        $fa['fees']                   = $medicalTestRec['fees'];
        $fa['medical_test_id']        = $medical_test_id;
        $fa['patient_visit_id']       = $patient_visit_id;
        $fa['patient_information_id'] = $patient_information_id;
        $fa['vaccination_date']       = date("Y-m-d");
        $fa['creation_date']          = date("Y-m-d");
        $fa['created_by']             = $fn->getSessionParam('userName');
        $fa['group_name']             = $group_name;
        $fa['outside']             = 0;

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'vaccination_visit');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getVaccinationValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getVaccinationOutsideRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $medical_test_id   = $fn->getReqParam('medical_test_id');
        $patient_information_id   = $fn->getReqParam('patient_information_id');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $group_name   = $fn->getReqParam('group_name');
        $medicalTestRec = $fn->getRecordRowByID('medical_test', 'medical_test_id', $medical_test_id);

        $fa = array();
        $fa['title']                  = $medicalTestRec['title'];
        $fa['medical_test_id']        = $medical_test_id;
        $fa['patient_visit_id']       = $patient_visit_id;
        $fa['patient_information_id'] = $patient_information_id;
        $fa['creation_date']          = date("Y-m-d");
        $fa['created_by']             = $fn->getSessionParam('userName');
        $fa['outside']                = 1;
        $fa['group_name']             = $group_name;

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'vaccination_visit');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getVaccinationRecordDelete(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $medical_test_id   = $fn->getReqParam('medical_test_id');
        $group_name   = $fn->getReqParam('group_name');

        $SQL = "
        DELETE FROM vaccination_visit 
        WHERE patient_visit_id = {$patient_visit_id} 
          AND medical_test_id = {$medical_test_id}
          AND group_name = '{$group_name}'
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateVaccinationFeesAndDate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $medical_test_id        = $fn->getReqParam('medical_test_id');
        $fees                   = $fn->getReqParam('fees');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $patient_visit_id       = $fn->getReqParam('patient_visit_id');
        $group_name             = $fn->getReqParam('group_name');
        $due_date               = $fn->getReqParam('due_date');
        $vaccination_date       = $fn->getReqParam('vaccination_date');

        $fa1 = array();
        if($fees != ''){
            $fa1['fees']  = $fees;
        }

        if($due_date != ''){
            $fa1['due_date'] = $due_date;
        }

        if($vaccination_date != ''){
            $fa1['vaccination_date'] = $vaccination_date;
        }

        $whereCondition = "
        WHERE patient_visit_id = '{$patient_visit_id}' AND medical_test_id = '{$medical_test_id}'
        ";
        $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, 'vaccination_visit', $whereCondition);
        $db->sql_query($SQLUpdate);
    }

    /**
     *
     */
    function getMedicalTestRecordSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $medical_test_id   = $fn->getReqParam('medical_test_id');
        $medicalTestRec = $fn->getRecordRowByID('medical_test', 'medical_test_id', $medical_test_id);
        $date = date('Y-m-d');

        $fa = array();
        $fa['title']            = $medicalTestRec['title'];
        $fa['fees']             = $medicalTestRec['fees'];
        $fa['status']           = 'Current';
        $fa['medical_test_id']  = $medical_test_id;
        $fa['patient_visit_id'] = $patient_visit_id;
        $fa['creation_date']    = $date;
        $fa['created_by']       = $fn->getSessionParam('userName');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_test_visit');
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getMedicalTestValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getMedicalTestRecordDelete(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $medical_test_id   = $fn->getReqParam('medical_test_id');

        $SQL = "
        DELETE FROM medical_test_visit 
        WHERE patient_visit_id = {$patient_visit_id} 
          AND medical_test_id = {$medical_test_id}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getMedicalTestRecordUpdate(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $lab_supplier_fees = $fn->getReqParam('lab_supplier_fees');
        $medical_test_id = $fn->getReqParam('medical_test_id');
        $supplier_id = $fn->getReqParam('supplier_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $container = $fn->getReqParam('container');

        $SQL = "
        UPDATE medical_test_visit SET lab_supplier_fees = '{$lab_supplier_fees}', supplier_id = '{$supplier_id}', container = '{$container}'
        WHERE patient_visit_id = {$patient_visit_id} 
          AND medical_test_id = {$medical_test_id}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateMedTestFeesAndNotes() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $medical_test_id   = $fn->getReqParam('medical_test_id');
        $fees   = $fn->getReqParam('fees');
        $notes   = $fn->getReqParam('notes');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');
        $investigation_date   = $fn->getReqParam('investigation_date');

        $fa1 = array();
        if($fees != ''){
            $fa1['fees']  = $fees;
        }
        if($notes != ''){
            $fa1['notes'] = $notes;
        }
        if($investigation_date != ''){
            $fa1['creation_date'] = $investigation_date;
        }

        $whereCondition = "
        WHERE patient_visit_id = '{$patient_visit_id}' AND medical_test_id = '{$medical_test_id}'
        ";
        $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, 'medical_test_visit', $whereCondition);
        $db->sql_query($SQLUpdate);
    }

    /**
     *
     */
    function getUpdateMedicalVisitParameter() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $medical_test_parameter_id   = $fn->getReqParam('medical_test_parameter_id');
        $medical_test_id   = $fn->getReqParam('medical_test_id');
        $notes   = $fn->getReqParam('notes');
        $patient_visit_id   = $fn->getReqParam('patient_visit_id');

        $fa1 = array();
        $fa1['medical_test_id']  = $medical_test_id;
        $fa1['medical_test_parameter_id']  = $medical_test_parameter_id;
        $fa1['creation_date']    = date("Y-m-d");
        $fa1['created_by']       = $fn->getSessionParam('userName');
        $fa1['notes']            = $notes;
        $fa1['patient_visit_id'] = $patient_visit_id;

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa1['site_id'] = $cpSiteIdSession;
        }

        $MVPRec = $fn->getRecordByCondition('medical_visit_parameter', "patient_visit_id = '{$patient_visit_id}' AND medical_test_id = '{$medical_test_id}' AND medical_test_parameter_id = '{$medical_test_parameter_id}'");
        if($MVPRec['medical_visit_parameter_id'] != ''){
            $whereCondition = "
            WHERE patient_visit_id = '{$patient_visit_id}' AND medical_test_id = '{$medical_test_id}' AND medical_test_parameter_id = '{$medical_test_parameter_id}'
            ";
            $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, 'medical_visit_parameter', $whereCondition);
            $db->sql_query($SQLUpdate);
        } else{
            $SQLPara    = $dbUtil->getInsertSQLStringFromArray($fa1, 'medical_visit_parameter');
            $resultPara = $db->sql_query($SQLPara);
        }
    }

    /**
     *
     */
    function getMedicalHistorySubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getMedicalHistoryValidate()){
            return $validate->getErrorMessageXML();
        }

        $title_arr       = $fn->getPostParam('title', array());
        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $others = $fn->getPostParam('others');
        $allergies = $fn->getPostParam('allergies');

        $SQL = "
        SELECT m.title
        FROM medical_history_information m
        WHERE m.patient_visit_id = {$patient_visit_id}
        ";
        $result   = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArrayForForm($result);

        $oddTitle = array_diff($dataArray, $title_arr);

        foreach($oddTitle as $valueTitle){
            $SQLDelete="DELETE FROM medical_history_information WHERE title='{$valueTitle}' AND patient_visit_id = {$patient_visit_id}";
            $resultDelete = $db->sql_query($SQLDelete);

            $date = date('Y-m-d');
            $SQLUpdate ="
            UPDATE medical_his_information_history
            SET end_date='{$date}'
            WHERE patient_visit_id = {$patient_visit_id}
              AND title='{$valueTitle}'
              AND end_date IS NULL
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        foreach($title_arr as $value){
            $medHisRec = $fn->getRecordByCondition('medical_history_information', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}'");
            if($medHisRec['medical_history_information_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['status'] = 'Current';
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by'] = $fn->getSessionParam('userName');

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_history_information');
                $result = $db->sql_query($SQL);
                $medical_history_information_id = $db->sql_nextid();
            }

            $medInfoHisRec = $fn->getRecordByCondition('medical_his_information_history', "patient_visit_id = '{$patient_visit_id}' AND title = '{$value}' AND end_date IS NULL");
            if($medInfoHisRec['medical_his_information_history_id'] == ''){
                $fa = array();
                $fa['title'] = $value;
                $fa['patient_visit_id'] = $patient_visit_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['start_date'] = date("Y-m-d");
                $fa['medical_history_information_id'] = $medical_history_information_id;

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_his_information_history');
                $result = $db->sql_query($SQL);
            }
        }

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $fa1 = array();
        $fa1['alergies']        = $allergies;
        $whereCondition = "
        WHERE patient_information_id = {$patientVisitRec['patient_information_id']}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa1, 'patient_information', $whereCondition);
        $db->sql_query($SQLInvoice);

        $fa2 = array();
        $fa2['other_medical_history'] = $others;
        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getMedicalHistoryValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getAddComplain() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $title  = $fn->getReqParam('title');
        $complain_id  = $fn->getReqParam('complain_id');
        $patient_information_id  = $fn->getReqParam('patient_information_id');

        if($complain_id == ''){
            $fa = array();
            $fa['title']      = $title;
            $complain_id = $fn->addRecord($fa, 'complain');            
        }

        $SQLComplain = "
        SELECT complain
        FROM patient_visit
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultComplain  = $db->sql_query($SQLComplain);
        $rowComplain     = $db->sql_fetchrow($resultComplain);

        $checkComplain = $rowComplain['complain'];

        if($checkComplain != ''){
            $checkComplain = $checkComplain.', '.$title;
        }
        else{
            $checkComplain = $title;
        }

        $SQLUPDATE = "
        UPDATE patient_visit
        SET complain = '{$checkComplain}'
        WHERE patient_visit_id = {$patient_visit_id}
        ";

        $result  = $db->sql_query($SQLUPDATE);

        $fa = array();
        $fa['patient_visit_id']       = $patient_visit_id;
        $fa['patient_information_id'] = $patient_information_id;
        $fa['complain_id']            = $complain_id;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'complain_visit_history');
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getAddProcedure() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $visit_procedure  = $fn->getReqParam('visit_procedure');

        $SQLprocedure = "
        SELECT visit_procedure
        FROM patient_visit
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultProcedure  = $db->sql_query($SQLprocedure);
        $rowProcedure     = $db->sql_fetchrow($resultProcedure);

        $checkProcedure = $rowProcedure['visit_procedure'];

        if($checkProcedure != ''){
            $checkProcedure = $checkProcedure.', '.$visit_procedure;
        }
        else{
            $checkProcedure = $visit_procedure;
        }

        $SQLUPDATE = "
        UPDATE patient_visit
        SET visit_procedure = '{$checkProcedure}'
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $result  = $db->sql_query($SQLUPDATE);

    }

    /**
     *
     */
    function getRemoveComplain() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $title  = $fn->getReqParam('title');
        $complain_id  = $fn->getReqParam('complain_id');
        $patient_information_id  = $fn->getReqParam('patient_information_id');

        $SQLComplain = "
        SELECT complain
        FROM patient_visit
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultComplain  = $db->sql_query($SQLComplain);
        $rowComplain     = $db->sql_fetchrow($resultComplain);

        $checkComplain = $rowComplain['complain'];
        $checkComplain = str_replace($title.', ', '', $checkComplain);
        $checkComplain = str_replace($title, '', $checkComplain);
        $checkComplain = str_replace(', , ', ', ', $checkComplain);
        $checkComplain = rtrim($checkComplain, ", ");

        $SQLUPDATE = "
        UPDATE patient_visit
        SET complain = '{$checkComplain}'
        WHERE patient_visit_id = {$patient_visit_id}
        ";

        $result  = $db->sql_query($SQLUPDATE);

        $SQLDelete ="
               DELETE FROM complain_visit_history
               WHERE patient_visit_id = {$patient_visit_id}
                 AND complain_id = {$complain_id}
                 AND patient_information_id = {$patient_information_id}
               ";
        $resultDelete = $db->sql_query($SQLDelete);
    }

    /**
     *
     */
    function getAddDiagnosis() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $disease_name  = $fn->getReqParam('disease_name');
        $prescription_id  = $fn->getReqParam('prescription_id');
        $patient_information_id  = $fn->getReqParam('patient_information_id');

        if($prescription_id == ''){
            $fa = array();
            $fa['disease_name']      = $disease_name;
            $prescription_id = $fn->addRecord($fa, 'prescription');            
        }

        $SQLComplain = "
        SELECT diagnosis
        FROM patient_visit
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultComplain  = $db->sql_query($SQLComplain);
        $rowComplain     = $db->sql_fetchrow($resultComplain);

        $checkComplain = $rowComplain['diagnosis'];

        if($checkComplain != ''){
            $checkComplain = $checkComplain.', '.$disease_name;
        }
        else{
            $checkComplain = $disease_name;
        }

        $SQLUPDATE = "
        UPDATE patient_visit
        SET diagnosis = '{$checkComplain}'
        WHERE patient_visit_id = {$patient_visit_id}
        ";

        $result  = $db->sql_query($SQLUPDATE);

        $fa = array();
        $fa['patient_visit_id']       = $patient_visit_id;
        $fa['patient_information_id'] = $patient_information_id;
        $fa['prescription_id'] = $prescription_id;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'complain_visit_history');
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getRemoveDiagnosis() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $disease_name  = $fn->getReqParam('disease_name');
        $prescription_id  = $fn->getReqParam('prescription_id');
        $patient_information_id  = $fn->getReqParam('patient_information_id');

        $SQLComplain = "
        SELECT diagnosis
        FROM patient_visit
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultComplain  = $db->sql_query($SQLComplain);
        $rowComplain     = $db->sql_fetchrow($resultComplain);

        $checkComplain = $rowComplain['diagnosis'];
        $checkComplain = str_replace($disease_name.', ', '', $checkComplain);
        $checkComplain = str_replace($disease_name, '', $checkComplain);
        $checkComplain = str_replace(', , ', ', ', $checkComplain);
        $checkComplain = rtrim($checkComplain, ", ");

        $SQLUPDATE = "
        UPDATE patient_visit
        SET diagnosis = '{$checkComplain}'
        WHERE patient_visit_id = {$patient_visit_id}
        ";

        $result  = $db->sql_query($SQLUPDATE);

        $SQLDelete ="
               DELETE FROM complain_visit_history
               WHERE patient_visit_id = {$patient_visit_id}
                 AND prescription_id = {$prescription_id}
                 AND patient_information_id = {$patient_information_id}
               ";
        $resultDelete = $db->sql_query($SQLDelete);
    }

    /**
     *
     */
    function getSummaryPortalSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getSummaryPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $diagnosis = $fn->getPostParam('diagnosis');

        $fa = array();
        $fa['diagnosis']          = $diagnosis;

        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);


        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSummaryPortalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getChiefComplainsSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getChiefComplainsValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $complain = $fn->getPostParam('complain');

        $fa = array();
        $fa['complain']          = $complain;

        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getChiefComplainsValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getProcedurePortalSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getProcedurePortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $visit_procedure = $fn->getPostParam('visit_procedure');

        $fa = array();
        $fa['visit_procedure']          = $visit_procedure;

        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getProcedurePortalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getVitalSignsSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getVitalSignsValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $temperature = $fn->getPostParam('temperature');
        $pulse_rate = $fn->getPostParam('pulse_rate');
        $respiratory_rate = $fn->getPostParam('respiratory_rate');
        $blood_pressure = $fn->getPostParam('blood_pressure');
        $crt = $fn->getPostParam('crt');

        $fa = array();
        $fa['temperature']      = $temperature;
        $fa['pulse_rate']       = $pulse_rate;
        $fa['respiratory_rate'] = $respiratory_rate;
        $fa['blood_pressure']   = $blood_pressure;
        $fa['crt']              = $crt;

        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($SQLInvoice);

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getVitalSignsValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getLabsSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getLabsValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id = $fn->getPostParam('patient_visit_id');
        $supplier_id = $fn->getPostParam('supplier_id');

        $fa = array();
        $fa['supplier_id'] = $supplier_id;

        $labsRec = $fn->getRecordRowByID('labs', 'patient_visit_id', $patient_visit_id);
        if($labsRec['labs_id'] != ''){
            $fa['modification_date']  = date("Y-m-d H:i:s");
            $fa['modified_by']        = $fn->getSessionParam('userName');

            $whereCondition = "
            WHERE labs_id = {$labsRec['labs_id']}
            ";
            $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'labs', $whereCondition);
            $db->sql_query($SQLUpdate);
        } else {
            $fa['labs_date']        = date("Y-m-d");
            $fa['patient_visit_id'] = $patient_visit_id;
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['created_by']       = $fn->getSessionParam('userName');

            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'labs');
            $result = $db->sql_query($SQL);
        }


        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getLabsValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getPrescribeMedicineFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $prescribe_medicine_id = $fn->getReqParam('prescribe_medicine_id');

        $SQLPM="
        SELECT pm.*
        FROM prescribe_medicine pm
        WHERE prescribe_medicine_id = {$prescribe_medicine_id} 
        ";
        $resultPM   = $db->sql_query($SQLPM);
        $rowPM = $db->sql_fetchrow($resultPM);

        $fa1 = array();

        $fa1['title']                  = $rowPM['medicine_name'];
        $fa1['dosage']                 = $rowPM['dosage'];
        $fa1['instruction']            = $rowPM['instruction'];
        $fa1['days']                   = $rowPM['days'];
        $fa1['product_id']             = $rowPM['product_id'];
        $fa1['creation_date']          = date("Y-m-d H:i:s");
        $fa1['created_by']             = $fn->getSessionParam('userName');

        $insertPrescriptionSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'medicines_visit');
        $resultPrescriptionSQL = $db->sql_query($insertPrescriptionSQL);

    }
    /**
     *
     */
    function getDeleteMedicineVisit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $medicines_visit_id = $fn->getReqParam('medicines_visit_id');

        $SQL ="
               DELETE FROM medicines_visit
               WHERE medicines_visit_id = {$medicines_visit_id}
               ";
        $result = $db->sql_query($SQL);
    }
     /**
     *
     */
    function getPrescribeMedicineValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter Medicine Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    function getCreateVisitRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $validate->resetErrorArray();
        $dr_required = $fn->getPostParam('dr_required');

        if($dr_required == 1){
            $validate->validateData('employee_id', 'Please select Dr/Nurse');
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     *
     */
    function getCreateVisitRecordSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getCreateVisitRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_information_id  = $fn->getPostParam('patient_information_id');
        $employee_id             = $fn->getPostParam('employee_id');
        $appointment_id          = $fn->getPostParam('appointment_id');
        $dr_required             = $fn->getPostParam('dr_required');
        $patient_name            = $fn->getPostParam('patient_name');
        $age_year                = $fn->getPostParam('age_year');
        $age_month               = $fn->getPostParam('age_month');
        $age_day                 = $fn->getPostParam('age_day');
        $father_name             = $fn->getPostParam('father_name');
        $husband_name            = $fn->getPostParam('husband_name');
        $address_area            = $fn->getPostParam('address_area');
        $address_street          = $fn->getPostParam('address_street');
        $phone                   = $fn->getPostParam('phone');
        $gender                  = $fn->getPostParam('gender');
        $weight                  = $fn->getPostParam('weight');
        $temperature             = $fn->getPostParam('temperature');
        $height                  = $fn->getPostParam('height');
        $bmi                     = $fn->getPostParam('bmi');
        $blood_pressure          = $fn->getPostParam('blood_pressure');
        $pulse_rate              = $fn->getPostParam('pulse_rate');
        $respiratory_rate        = $fn->getPostParam('respiratory_rate');
        $SpO2                    = $fn->getPostParam('SpO2');
        $cpSiteIdSession         = $fn->getSessionParam('cp_site_id');

        if($patient_information_id == ""){
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "WHERE site_id = {$cpSiteIdSession}";
            }

            $SQLPatientCode = "
            SELECT MAX(CONVERT(patient_code, UNSIGNED INTEGER)) + 1 AS patient_code
            FROM patient_information
            {$appendSql}
            ";
            $resultPatientCode = $db->sql_query($SQLPatientCode);
            $rowPatientCode    = $db->sql_fetchrow($resultPatientCode);

            if($rowPatientCode['patient_code'] != ""){
                $patient_code = $rowPatientCode['patient_code'];
            }
            else{
                $patient_code = "1000";
            }

            $SQLPatientUhid = "
            SELECT MAX(CONVERT(uhid, UNSIGNED INTEGER)) + 1 AS uhid
            FROM patient_information
            {$appendSql}
            ";
            $resultPatientUhid = $db->sql_query($SQLPatientUhid);
            $rowPatientUhid    = $db->sql_fetchrow($resultPatientUhid);

            if($rowPatientUhid['uhid'] != ""){
                $uhid = $rowPatientUhid['uhid'];
            }
            else{
                $uhid = "100000";
            }

            $faPatInfo = array();
            $faPatInfo['patient_code']   = $patient_code;
            $faPatInfo['uhid']           = $uhid;
            $faPatInfo['name']           = strtoupper($patient_name);
            $faPatInfo['father_name']    = strtoupper($father_name);
            $faPatInfo['spuse_name']     = strtoupper($husband_name);
            $faPatInfo['address_area']   = strtoupper($address_area);
            $faPatInfo['address_street'] = strtoupper($address_street);
            $faPatInfo['phone']          = $phone;
            $faPatInfo['age_year']       = $age_year;
            $faPatInfo['age_month']      = $age_month;
            $faPatInfo['age_day']        = $age_day;
            $faPatInfo['gender']         = $gender;
            $faPatInfo['height']         = $height;
            $faPatInfo['weight']         = $weight;
            $faPatInfo['bmi']            = $bmi;

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $faPatInfo['site_id']    = $cpSiteIdSession;
            }
            
            $faPatInfo['created_by']     = $fn->getSessionParam('userName');
            $faPatInfo['creation_date']  = date("Y-m-d H:i:s");
            $patient_information_id = $fn->addRecord($faPatInfo, 'patient_information');

        }

        else {
            $faPatInfo = array();
            $faPatInfo['name']              = $patient_name;
            $faPatInfo['father_name']       = $father_name;
            $faPatInfo['spuse_name']        = $husband_name;
            $faPatInfo['address_area']      = $address_area;
            $faPatInfo['address_street']    = $address_street;
            $faPatInfo['phone']             = $phone;
            $faPatInfo['age_year']          = $age_year;
            $faPatInfo['age_month']         = $age_month;
            $faPatInfo['age_day']           = $age_day;
            $faPatInfo['gender']            = $gender;
            $faPatInfo['height']         = $height;
            $faPatInfo['weight']         = $weight;
            $faPatInfo['bmi']            = $bmi;
            $faPatInfo['modified_by']       = $fn->getSessionParam('userName');
            $faPatInfo['modification_date'] = date("Y-m-d H:i:s");

            $whereCondition   = "WHERE patient_information_id = {$patient_information_id}";
            $updatePatInfoSQL = $dbUtil->getUpdateSQLStringFromArray($faPatInfo, 'patient_information', $whereCondition);
            $resultPatInfoSQL = $db->sql_query($updatePatInfoSQL);
        }

        $currentDate     = date("Y-m-d");
        $patientInfoRec  = $fn->getRecordRowByID('patient_information', 'patient_information_id',  $patient_information_id);
        
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "WHERE site_id = {$cpSiteIdSession}";
        }

        $SQLVisitCode = "
        SELECT MAX(CONVERT(visit_code, UNSIGNED INTEGER)) + 1 AS visit_code
        FROM patient_visit
        {$appendSql}
        ";
        $resultVisitCode = $db->sql_query($SQLVisitCode);
        $rowVisitCode    = $db->sql_fetchrow($resultVisitCode);

        if($rowVisitCode['visit_code'] != ""){
            $visit_code = $rowVisitCode['visit_code'];
        }
        else{
            $visit_code = "1000";
        }

        $fa = array();

        if($patientInfoRec['bill_type'] == ''){
            $patientInfoRec['bill_type'] = 'Individual';
        }

        $fa['patient_information_id'] = $patient_information_id;
        $fa['bill_type']              = 'Individual';
        $fa['status']                 = 'Visited';
        $fa['record_type']            = 'Walk In';
        $fa['employee_id']            = $employee_id;
        $fa['check_up_date']          = $currentDate;
        $fa['check_up_time']          = date("H:i:s");
        $fa['visit_code']             = $visit_code;
        $fa['dr_required']            = $dr_required;
        $fa['weight']                 = $weight;
        $fa['temperature']            = $temperature;
        $fa['height']            = $height;
        $fa['bmi']            = $bmi;
        $fa['blood_pressure']            = $blood_pressure;
        $fa['pulse_rate']            = $pulse_rate;
        $fa['respiratory_rate']            = $respiratory_rate;
        $fa['SpO2']            = $SpO2;

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id']   = $cpSiteIdSession;
        }

        if($appointment_id != ''){
          $fa['appointment_id']     = $appointment_id;
        }

        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_visit');
        $resultVisitSQL   = $db->sql_query($insertVisitSQL);
        $patient_visit_id = $db->sql_nextid();

        if($employee_id != ""){
            $rowEmployee = $fn->getRecordRowByID('employee', 'employee_id', $employee_id);

            if($rowEmployee['consultation_fees'] == ""){
                $rowEmployee['consultation_fees'] = 0;
            }

            $fees_commission = 0;
            if($rowEmployee['consultation_fees'] > 0){
                if($rowEmployee['fees_commission_type'] == "%"){
                    $fees_commission = ($rowEmployee['consultation_fees'] * $rowEmployee['fees_commission']) / 100;
                }else{
                    $fees_commission = $rowEmployee['fees_commission'];
                }
            }

            $fa2 = array();

            $fa2['consultation_fees'] = $rowEmployee['consultation_fees'];
            $fa2['employee_id']       = $rowEmployee['employee_id'];
            $fa2['patient_visit_id']  = $patient_visit_id;
            $fa2['creation_date']     = date("Y-m-d H:i:s");
            $fa2['created_by']        = $fn->getSessionParam('userName');

            $insertEmployeeVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'employee_visit');
            $resultEmployeeVisitSQL   = $db->sql_query($insertEmployeeVisitSQL);
        }else{

            $fa2 = array();
            
            $fa2['consultation_fees'] = "0.00";
            $fa2['patient_visit_id']  = $patient_visit_id;
            $fa2['creation_date']     = date("Y-m-d H:i:s");
            $fa2['created_by']        = $fn->getSessionParam('userName');

            $insertEmployeeVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'employee_visit');
            $resultEmployeeVisitSQL   = $db->sql_query($insertEmployeeVisitSQL);
        }

        return $validate->getSuccessMessageXML($patient_visit_id);
    }

    /**
     *
     */
    function getCreateVisitRecordDirectValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     *
     */
    function getCreateVisitRecordDirect(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getCreateVisitRecordDirectValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_information_id  = $fn->getReqParam('patient_information_id');
        $employee_id             = $fn->getReqParam('dr_required');
        $appointment_id          = $fn->getReqParam('appointment_id');
        $cpSiteIdSession         = $fn->getSessionParam('cp_site_id');

        $currentDate  = date("Y-m-d");

        $patientInfoRec  = $fn->getRecordRowByID('patient_information', 'patient_information_id',  $patient_information_id);
        $fa = array();

        if($patientInfoRec['bill_type'] == ''){
            $patientInfoRec['bill_type'] = 'Individual';
        }
        
        if($patientInfoRec['bill_type'] == 'Company' || $patientInfoRec['bill_type'] == 'Panel'){
            $sqlCompany = "
            SELECT company_id
                  ,company_name
                  ,address_flat
                  ,address_street
                  ,address_town
                  ,address_state
                  ,address_country
                  ,phone
            FROM company
            WHERE category = '{$patientInfoRec['bill_type']}'
            AND company_id = {$patientInfoRec['company_id']}
            ORDER BY company_name
            ";
            $resultCompany = $db->sql_query($sqlCompany);
            $rowCompany    = $db->sql_fetchrow($resultCompany);

            $fa['company_name']           = $rowCompany['company_name'];
            $fa['address_flat']           = $rowCompany['address_flat'];
            $fa['address_street']         = $rowCompany['address_street'];
            $fa['address_town']           = $rowCompany['address_town'];
            $fa['address_state']          = $rowCompany['address_state'];
            $fa['address_country']        = $rowCompany['address_country'];
            $fa['phone']                  = $rowCompany['phone'];
            $fa['company_id']             = $rowCompany['company_id'];
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "WHERE site_id = {$cpSiteIdSession}";
        }

        $SQLVisitCode = "
        SELECT MAX(CONVERT(visit_code, UNSIGNED INTEGER)) + 1 AS visit_code
        FROM patient_visit
        {$appendSql}
        ";
        $resultVisitCode = $db->sql_query($SQLVisitCode);
        $rowVisitCode    = $db->sql_fetchrow($resultVisitCode);

        if($rowVisitCode['visit_code'] != ""){
            $visit_code = $rowVisitCode['visit_code'];
        }
        else{
            $visit_code = "1000";
        }

        $fa['patient_information_id'] = $patient_information_id;
        $fa['bill_type']              = 'Individual';
        $fa['status']                 = 'New';
        $fa['record_type']            = 'Walk In';
        $fa['employee_id']            = $employee_id;
        $fa['check_up_date']          = $currentDate;
        $fa['check_up_time']          = date("H:i:s");

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id']            = $cpSiteIdSession;
        }

        if($appointment_id != ''){
          $fa['appointment_id']       =  $appointment_id;
        }

        $fa['visit_code']             = $visit_code;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'patient_visit');
        $resultVisitSQL   = $db->sql_query($insertVisitSQL);
        $patient_visit_id = $db->sql_nextid();

        $rowEmployee = $fn->getRecordRowByID('employee', 'employee_id', $employee_id);

        $fa2['consultation_fees'] = $rowEmployee['consultation_fees'];
        $fa2['employee_id']       = $rowEmployee['employee_id'];
        $fa2['patient_visit_id']  = $patient_visit_id;
        $fa2['creation_date']     = date("Y-m-d H:i:s");
        $fa2['created_by']        = $fn->getSessionParam('userName');

        $insertEmployeeVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'employee_visit');
        $resultEmployeeVisitSQL   = $db->sql_query($insertEmployeeVisitSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDeleteDoctorRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $employee_visit_id = $fn->getReqParam('employee_visit_id');

      $SQL = "
      DELETE FROM employee_visit
      WHERE employee_visit_id = {$employee_visit_id}
      ";
      $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getDeleteLabsRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $labs_id = $fn->getReqParam('labs_id');

      $SQLLabs = "
      DELETE FROM labs
      WHERE labs_id = {$labs_id}
      ";
      $resultLabs = $db->sql_query($SQLLabs);

      $SQLLabsHistory = "
      DELETE FROM labs_history
      WHERE labs_id = {$labs_id}
      ";
      $resultLabsHistory = $db->sql_query($SQLLabsHistory);

      $SQLVisitPerio = "
      DELETE FROM visit_perio_chart
      WHERE labs_id = {$labs_id}
      ";
      $resultVisitPerio = $db->sql_query($SQLVisitPerio);
    }

    /**
     *
     */
    function getDeleteMedicineRecord(){
      $fn = Zend_Registry::get('fn');
      $db = Zend_Registry::get('db');
      $dbUtil = Zend_Registry::get('dbUtil');
      $cpCfg = Zend_Registry::get('cpCfg');

      $medicines_visit_id = $fn->getReqParam('medicines_visit_id');

      $SQL = "
      DELETE FROM medicines_visit
      WHERE medicines_visit_id = {$medicines_visit_id}
      ";
      $result = $db->sql_query($SQL);
    }


    /**
     *
     */
    function getAddMedicine() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $patient_visit_id= $fn->getReqParam('patient_visit_id');

        $SQLEV = "
        SELECT ev.employee_id
        FROM employee_visit ev
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ORDER BY ev.employee_visit_id ASC
        ";
        $resultEV = $db->sql_query($SQLEV);
        $rowEV    = $db->sql_fetchrow($resultEV);

        $fa = array();
        $fa['qty']              = 0;
        $fa['employee_id']      = $rowEV['employee_id'];
        $fa['patient_visit_id'] = $patient_visit_id;
        $id = $fn->addRecord($fa, 'medicines_visit');
    }

    /**
     *
     */
    function getApplyMedicine() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $patient_visit_id= $fn->getReqParam('patient_visit_id');
        $patient_visit_id_main= $fn->getReqParam('patient_visit_id_main');

        $SQLEV = "
        SELECT mt.*
        FROM medicines_visit mt
        WHERE mt.patient_visit_id = '{$patient_visit_id}'
        ";
        $resultEV = $db->sql_query($SQLEV);
        while ($rowEV = $db->sql_fetchrow($resultEV)) {
            $fa = array();
            $fa['qty']              = $rowEV['qty'];
            $fa['title']            = $rowEV['title'];
            $fa['dosage']           = $rowEV['dosage'];
            $fa['route']            = $rowEV['route'];
            $fa['days']             = $rowEV['days'];
            $fa['instruction']      = $rowEV['instruction'];
            $fa['employee_id']      = $rowEV['employee_id'];
            $fa['product_id']       = $rowEV['product_id'];
            $fa['patient_visit_id'] = $patient_visit_id_main;
            $id = $fn->addRecord($fa, 'medicines_visit');
        }
    }

    /**s
     *
     */
    function getSearchProductTitle() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $site_id = $fn->getSessionParam('cp_site_id');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title) AS label
        FROM product p
        WHERE (p.title LIKE '{$productTitle}%')
          AND p.published = 1
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
     function getAddProductAndLineItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $title = $fn->getReqParam('title');
        $patient_visit_id= $fn->getReqParam('patient_visit_id');

        $fa = array();
        $fa['title']      = $title;
        $fa['published']  = 1;
        $fa['product_type']  = 'Purchasing and Selling';
        $fa['item_code']     = $this->getUpdateProductCode();
        $fa['created_by']    = $fn->getSessionParam('userName');
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $product_id = $fn->addRecord($fa, 'product');

        $SQLEV = "
        SELECT ev.employee_id
        FROM employee_visit ev
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ORDER BY ev.employee_visit_id ASC
        ";
        $resultEV = $db->sql_query($SQLEV);
        $rowEV    = $db->sql_fetchrow($resultEV);

        $fa = array();
        $fa['qty']              = 0;
        $fa['employee_id']      = $rowEV['employee_id'];
        $fa['patient_visit_id'] = $patient_visit_id;
        $id = $fn->addRecord($fa, 'medicines_visit');

        $arr = array();
        $arr['msg'] = '';

        $SQL    = "
        SELECT   p.title
        FROM product p
        WHERE p.product_id = '{$product_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if($product_id != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set product_id = '{$product_id}'
            ,title = '{$row['title']}'
            WHERE medicines_visit_id = {$id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductItemCode");

        if($nextProductItemCode < 10){
            $ProCode = '000' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 99){
            $ProCode = '00' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 999){
            $ProCode = '0' . $nextProductItemCode;
        }
        else{
            $ProCode = $nextProductItemCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
    }

    /**
     *
     */
     function getCreateProductLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_id = $fn->getReqParam('product_id');
        $patient_visit_id= $fn->getReqParam('patient_visit_id');

        $SQLEV = "
        SELECT ev.employee_id
        FROM employee_visit ev
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ORDER BY ev.employee_visit_id ASC
        ";
        $resultEV = $db->sql_query($SQLEV);
        $rowEV    = $db->sql_fetchrow($resultEV);

        $fa = array();
        $fa['qty']              = 0;
        $fa['employee_id']      = $rowEV['employee_id'];
        $fa['patient_visit_id'] = $patient_visit_id;
        $id = $fn->addRecord($fa, 'medicines_visit');

        $arr = array();
        $arr['msg'] = '';

        $patVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patVisitRec['patient_information_id']);
        if($patVisitRec['weight'] == ''){
            $patVisitRec['weight'] = 0;
        }

        $SQL    = "
        SELECT   p.title
                ,p.dosage
                ,p.route
                ,p.medicine_qty
                ,p.instruction
                ,p.days
        FROM product p
        WHERE p.product_id = '{$product_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $SQLDW    = "
        SELECT dw.*
        FROM dosage_wtwise dw 
        WHERE dw.product_id = '{$product_id}'       
        ";
        $resultDW = $db->sql_query($SQLDW);
        $dosage_weight = '';
        $instruction_weight = '';
        while ($rowDW = $db->sql_fetchrow($resultDW)) {
            if($rowDW['wt_from'] == $patVisitRec['weight']){
                $dosage_weight = $rowDW['dosage'];
                $instruction_weight = $rowDW['instruction'];
            }elseif($rowDW['wt_from'] <= $patVisitRec['weight'] && $rowDW['wt_to'] >= $patVisitRec['weight']){
                $dosage_weight = $rowDW['dosage'];
                $instruction_weight = $rowDW['instruction'];
            }
        }


        $SQLDA    = "
        SELECT da.*
        FROM dosage_agewise da 
        WHERE da.product_id = '{$product_id}'       
        ";
        $resultDA = $db->sql_query($SQLDA);
        $dosage_age = '';
        $instruction_age = '';
        if($patInfoRec['age_year'] == ''){
            $patInfoRec['age_year'] = 0;
        }
        if($patInfoRec['age_month'] == ''){
            $patInfoRec['age_month'] = 0;
        }
        if($patInfoRec['age_day'] == ''){
            $patInfoRec['age_day'] = 0;
        }
        $age_year = $patInfoRec['age_year'] * 12 * 30;
        $age_month = $patInfoRec['age_month'] * 30;
        $age_day = $patInfoRec['age_day'];
        $age = $age_year + $age_month + $age_day;

        while ($rowDA = $db->sql_fetchrow($resultDA)) {
            if($rowDA['age_from_year'] == ''){
                $rowDA['age_from_year'] = 0;
            }
            if($rowDA['age_from_month'] == ''){
                $rowDA['age_from_month'] = 0;
            }
            if($rowDA['age_from_day'] == ''){
                $rowDA['age_from_day'] = 0;
            }

            $age_from_year = $rowDA['age_from_year'] * 12 * 30;
            $age_from_month = $rowDA['age_from_month'] * 30;
            $age_from_day = $rowDA['age_from_day'];
            $ageDAFrom = $age_from_year + $age_from_month + $age_from_day;

            $age_to_year = $rowDA['age_to_year'] * 12 * 30;
            $age_to_month = $rowDA['age_to_month'] * 30;
            $age_to_day = $rowDA['age_to_day'];
            $ageDATo = $age_to_year + $age_to_month + $age_to_day;

            if($ageDAFrom <= $age && $ageDATo >= $age){
                $dosage_age = $rowDA['dosage'];
                $instruction_age = $rowDA['instruction'];
            }

            /*if(($rowDA['age_from_year'] <= $patInfoRec['age_year']) && ($rowDA['age_to_year'] >= $patInfoRec['age_year']) && $rowDA['age_from_year'] != '' && $rowDA['age_from_month'] == ''){
                $dosage_age = $rowDA['dosage'];
                $instruction_age = $rowDA['instruction'];
            } else if(($rowDA['age_from_year'] <= $patInfoRec['age_year']) && ($rowDA['age_to_year'] >= $patInfoRec['age_year']) && $rowDA['age_from_year'] != '' && $rowDA['age_from_month'] != ''){
                $dosage_age = $rowDA['dosage'];
                $instruction_age = $rowDA['instruction'];
            } else if(($rowDA['age_from_month'] <= $patInfoRec['age_month']) && ($rowDA['age_to_month'] >= $patInfoRec['age_month']) && $rowDA['age_from_month'] != '' && $patInfoRec['age_year'] == '' && $rowDA['age_from_day'] <= $patInfoRec['age_day'] && $rowDA['age_from_year'] == ''){
                $dosage_age = $rowDA['dosage'];
                $instruction_age = $rowDA['instruction'];
            } else if(($rowDA['age_from_day'] <= $patInfoRec['age_day']) && ($rowDA['age_to_day'] >= $patInfoRec['age_day']) && $rowDA['age_from_day'] != ''  && $patInfoRec['age_month'] == ''){
                $dosage_age = $rowDA['dosage'];
                $instruction_age = $rowDA['instruction'];
            }*/
        }

        if($instruction_weight != ''){
            $instruction = $instruction_weight;
        } else if($instruction_age != '') {
            $instruction = $instruction_age;
        } else {
            $instruction = $row['instruction'];
        }

        if($dosage_weight != ''){
            $dosage = $dosage_weight;
        } else if($dosage_age != '') {
            $dosage = $dosage_age;
        } else {
            $dosage = $row['dosage'];
        }

        if($product_id != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set product_id = '{$product_id}'
            ,title = '{$row['title']}'
            ,dosage = '{$dosage}'
            ,route = '{$row['route']}'
            ,qty = '{$row['medicine_qty']}'
            ,instruction = '{$instruction}'
            ,days = '{$row['days']}'
            ,af_bf_food = 'After'
            WHERE medicines_visit_id = {$id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
     function getUpdateProductLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_id = $fn->getReqParam('product_id');
        $rec_id = $fn->getReqParam('rec_id');
        $id = $tv['srcRoomId'];
        $instruction = $fn->getReqParam('instruction');
        $days = $fn->getReqParam('days');
        $dosage = $fn->getReqParam('dosage');
        $qty = $fn->getReqParam('qty');
        $employee_id = $fn->getReqParam('employee_id');
        $selling_price = $fn->getReqParam('selling_price');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $route = $fn->getReqParam('route');
        $afBfFood = $fn->getReqParam('afBfFood');

        $arr = array();
        $arr['msg'] = '';

        $SQL    = "
        SELECT   p.title
                ,p.dosage
        FROM product p
        WHERE p.product_id = '{$product_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if($product_id != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set product_id = '{$product_id}'
            ,title = '{$row['title']}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($instruction != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set instruction = '{$instruction}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($route != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set route = '{$route}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($days != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set days = '{$days}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($dosage != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set dosage = '{$dosage}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($qty != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set qty = '{$qty}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($employee_id != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set employee_id = '{$employee_id}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($afBfFood != ''){
            $SQLUpdate    = "
            UPDATE medicines_visit
            set af_bf_food = '{$afBfFood}'
            WHERE medicines_visit_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getAddNoteLabSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $lab_visit_id  = $fn->getPostParam('lab_visit_id');
        $notes         = $fn->getPostParam('notes');

        if (!$this->getAddNoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['notes']     = $notes;

        $SQLUpdate    = "
        UPDATE lab_visit
        set notes = '{$notes}'
        WHERE lab_visit_id = {$lab_visit_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNoteFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     */
    function getAddNoteMedicalTestSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $medical_test_visit  = $fn->getPostParam('medical_test_visit');
        $notes         = $fn->getPostParam('notes');

        if (!$this->getAddNoteMedicalTestFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['notes']     = $notes;

        $SQLUpdate    = "
        UPDATE medical_test_visit
        set notes = '{$notes}'
        WHERE medical_test_visit = {$medical_test_visit}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNoteFormMedicalTestValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getCreateOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

        $fa = array();

        $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);
        $patientInfoRec  = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);
        $patientRow      = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientInfoRec['patient_information_id']);

        $fa['company_id']                    = $patientVisitRec['company_id'];
        /*$fa['company_name']                  = $patientVisitRec['company_name'];
        $fa['cust_address1']                 = $patientVisitRec['address_flat'];
        $fa['cust_address2']                 = $patientVisitRec['address_street'];
        $fa['cust_address_city']             = $patientVisitRec['address_town'];
        $fa['cust_address_state']            = $patientVisitRec['address_state'];
        $fa['cust_address_country_code']     = $patientVisitRec['address_country'];
        $fa['cust_phone']                    = $patientVisitRec['phone'];*/
        $fa['father_name']                   = $patientRow['father_name'];
        $fa['mother_name']                   = $patientRow['mother_name'];
        $fa['spuse_name']                    = $patientRow['spuse_name'];
        $fa['cust_first_name']               = $patientRow['name'];
        $fa['cust_address1']                 = $patientRow['address_street'];
        $fa['cust_address2']                 = $patientRow['address_area'];
        $fa['cust_address_city']             = $patientRow['address_city'];
        $fa['cust_address_country_code']     = $patientRow['address_country'];
        $fa['cust_address_po_code']          = $patientRow['address_code'];
        $fa['cust_phone']                    = $patientRow['phone'];
        $fa['shipping_first_name']           = $patientRow['name'];
        $fa['shipping_address1']             = $patientRow['address_street'];
        $fa['shipping_address_area']         = $patientRow['address_area'];
        $fa['shipping_address_city']         = $patientRow['address_city'];
        $fa['shipping_address_country_code'] = $patientRow['address_country'];
        $fa['shipping_address_po_code']      = $patientRow['address_code'];
        $fa['shipping_phone']                = $patientRow['phone'];
        $fa['primary_contact']               = $patientRow['primary_contact'];
        $fa['relationship']                  = $patientRow['relationship'];
        $fa['patient_visit_id']              = $patient_visit_id;
        $fa['check_up_date']                 = $patientVisitRec['check_up_date'];
        $fa['no_of_days']                    = $patientVisitRec['no_of_days'];
        $fa['order_type']                    = 'OP';

        $SQLRelation = "
        SELECT b.patient_information_id
              ,CONCAT_WS(' ', b.first_name, b.middle_name, b.last_name) AS patient_name
              ,b.nric
        FROM `patient_relationinfo` a
        LEFT JOIN (patient_information b) ON (b.patient_information_id = a.patient_information_source_id)
        WHERE a.patient_information_id = {$patientInfoRec['patient_information_id']}
        ";
        $resultRelation = $db->sql_query($SQLRelation);
        $relation = '';

        while ($rowRelation = $db->sql_fetchrow($resultRelation)) {
            $relation .= $rowRelation['patient_name'].', ';
        }

        $relation = rtrim($relation, ', ');

        $fa['relationship']                  = $relation;
        $fa['patient_information_id']        = $patientInfoRec['patient_information_id'];
        $fa['first_name']                    = $patientInfoRec['name'];
        $fa['nric']                          = $patientInfoRec['nric'];
        $fa['bill_type']                     = $patientInfoRec['bill_type'];
        $fa['serial_no_of_book']             = $patientInfoRec['serial_no_of_book'];
        $fa['department']                    = $patientInfoRec['department'];
        $fa['worker_id']                     = $patientInfoRec['worker_id'];

        $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$patient_visit_id}'");

        if(is_array($orderRec)){

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id'] = $cpSiteIdSession;
            }

            $fa['modification_date']  = date("Y-m-d H:i:s");
            $fa['modified_by']        = $_SESSION['userName'];

            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "order", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "WHERE site_id = {$cpSiteIdSession}";
            }

            $SQLOrderCode = "
            SELECT MAX(CONVERT(order_code, UNSIGNED INTEGER)) + 1 AS order_code
            FROM `order`
            {$appendSql}
            ";
            $resultOrderCode = $db->sql_query($SQLOrderCode);
            $rowOrderCode    = $db->sql_fetchrow($resultOrderCode);

            if($rowOrderCode['order_code'] != ""){
                $order_code = $rowOrderCode['order_code'];
            }
            else{
                $order_code = "1000";
            }

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id']      = $cpSiteIdSession;
            }

            $fa['order_code']       = $order_code;
            $fa['order_date']       = date('Y-m-d');
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['created_by']       = $_SESSION['userName'];
            $fa['order_status']     = 'New';

            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();
        }

        $SQLDoctor = "
        SELECT (CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
               ,ev.consultation_fees
               ,ev.notes
               ,ev.employee_visit_id
        FROM employee_visit ev
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ";
        $resultDoctor  = $db->sql_query($SQLDoctor);
        $numRowsDoctor = $db->sql_numrows($resultDoctor);

        if($numRowsDoctor > 0){

          while ($rowDoctor = $db->sql_fetchrow($resultDoctor)) {

            $fa4['record_id']       = $rowDoctor['employee_visit_id'];
            $fa4['order_id']        = $order_id;
            $fa4['record_type']     = 'Doctor/Nurse';
            $fa4['unit_price']      = $rowDoctor['consultation_fees'];
            $fa4['description']     = $rowDoctor['notes'];
            $fa4['item_title']      = $rowDoctor['employee_name'];
            $fa4['not_add_in_stock']      = 0;

            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowDoctor['employee_visit_id']}'
                                                                    AND order_id = {$order_id}
                                                                    AND record_type = 'Doctor/Nurse'");
            if(is_array($orderItemRec)){
                $fa4['modification_date']   = date("Y-m-d H:i:s");

                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa4, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $fa4['creation_date']   = date("Y-m-d H:i:s");

                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa4, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
          }

        }

        if($patientVisitRec['register_fees'] == 1){
            $fa2 = array();
            $fa2['record_id']       = $patient_visit_id;
            $fa2['order_id']        = $order_id;
            $fa2['record_type']     = 'Registration Fees';
            $fa2['unit_price']      = 50;
            $fa2['item_title']      = 'Registration Fees';

            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$patient_visit_id}'
                                                                    AND order_id = {$order_id}
                                                                    AND record_type = 'Registration Fees'");
            if(is_array($orderItemRec)){
                $fa2['modification_date']   = date("Y-m-d H:i:s");

                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa2, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $fa2['creation_date']   = date("Y-m-d H:i:s");

                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa2, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
        }

        $SQLMedicalTestDelete = "
        DELETE FROM order_item
        WHERE order_id = {$order_id}
          AND (record_type = 'Medical Test'
          OR record_type = 'Vaccination')
        ";
        $db->sql_query($SQLMedicalTestDelete);

        $SQLMedicalTest = "
        SELECT  mtv.title
               ,mtv.fees
               ,mtv.medical_test_visit_id
               ,mt.category
        FROM medical_test_visit mtv
        LEFT JOIN medical_test mt ON (mt.medical_test_id = mtv.medical_test_id)
        WHERE mtv.patient_visit_id = {$patient_visit_id}
        ";

        $resultMedicalTest  = $db->sql_query($SQLMedicalTest);
        $numRowsMedicalTest = $db->sql_numrows($resultMedicalTest);

        if($numRowsMedicalTest > 0){

          while ($rowMedicalTest = $db->sql_fetchrow($resultMedicalTest)) {

            if($rowMedicalTest['fees'] == ""){
                $rowMedicalTest['fees'] = 0;
            }

            $fa1['record_id']       = $rowMedicalTest['medical_test_visit_id'];
            $fa1['order_id']        = $order_id;
            $fa1['unit_price']      = $rowMedicalTest['fees'];
            $fa1['item_title']      = $rowMedicalTest['title'];
            if($rowMedicalTest['category'] == 'Vaccination'){                
                $fa1['record_type']     = 'Vaccination';
            } else {
                $fa1['record_type']     = 'Medical Test';
            }

            $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowMedicalTest['medical_test_visit_id']}'
                                                                    AND order_id = {$order_id}
                                                                    AND record_type = 'Medical Test'");
            if(is_array($orderItemRec)){
                $fa1['modification_date']   = date("Y-m-d H:i:s");

                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $fa1['creation_date']   = date("Y-m-d H:i:s");

                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa1, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }
          }

        }

        //Commented by Ansari on 06.03.2018 - The status and labs are not used.
        /*$SQLUpdate = "
        UPDATE patient_visit set status = 'Order Raised' WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQLUpdateLabs = "
        UPDATE labs set order_id = '{$order_id}' WHERE patient_visit_id = {$patient_visit_id}
        ";
        $resultUpdateLabs = $db->sql_query($SQLUpdateLabs);*/

        return $order_id;
    }

    /**
     *
     */
    function getUpdateConsultingFees() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_id     = $fn->getReqParam('employee_id');
        $arr = array();

        if($employee_id != ''){
            $SQL    = "
            SELECT consultation_fees
            FROM employee
            WHERE employee_id = {$employee_id}
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            return $row['consultation_fees'];
        }
    }

    /**
     *
     */
    function getConvertFollowUpDate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $follow_up_date     = $fn->getReqParam('follow_up_date');

        $Date = date("Y-m-d");
        $convertedDate = date("Y-m-d", strtotime($Date. " + ". $follow_up_date));

        return $convertedDate;
    }

    /**
     *
     */
    function getCancelPatientVisitRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $patient_visit_id     = $fn->getReqParam('patient_visit_id');

        $SQLPatientVisit ="
        UPDATE patient_visit SET status = 'Cancelled'
        WHERE patient_visit_id = '{$patient_visit_id}'
        ";
        $resultPatientVisit = $db->sql_query($SQLPatientVisit);

        $SQLOrder = "
        SELECT order_id
        FROM `order`
        WHERE patient_visit_id = '{$patient_visit_id}'
        ";
        $resultOrder  = $db->sql_query($SQLOrder);
        $rowOrder     = $db->sql_fetchrow($resultOrder);
        $numRowsOrder = $db->sql_numrows($resultOrder);

        if($numRowsOrder > 0){
            $SQLUpdateOrder = "
            UPDATE `order` SET order_status = 'Cancelled'
            WHERE order_id = '{$rowOrder['order_id']}'
            ";
            $resultUpdateOrder = $db->sql_query($SQLUpdateOrder);

            $SQLInvoice = "
            UPDATE invoice SET status = 'Cancelled'
            WHERE order_id = '{$rowOrder['order_id']}'
            ";
            $resultInvoice = $db->sql_query($SQLInvoice);

            $SQLReceipt = "
            UPDATE receipt SET receipt_status = 'Cancelled'
            WHERE order_id = '{$rowOrder['order_id']}'
            ";
            $resultReceipt = $db->sql_query($SQLReceipt);
        }

    }

    /**
     *
     */
    function getSearchComplainDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $patientDetail = $extractor[0];

        $SQL = "
        SELECT  title AS value
               ,title AS label
               ,complain_id AS id
               ,title AS complain_Name
        FROM complain
        WHERE (complain_id LIKE '{$patientDetail}%'
        OR title LIKE '{$patientDetail}%')
        GROUP BY title
        ORDER BY title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchProcedureDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $patientDetail = $extractor[0];

        $SQL = "
        SELECT  visit_procedure AS value
               ,visit_procedure AS label
               ,patient_visit_id AS id
               ,visit_procedure AS visit_procedure
        FROM patient_visit
        WHERE (visit_procedure LIKE '{$patientDetail}%')
        ORDER BY visit_procedure
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchDiagnosisDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $patientDetail = $extractor[0];

        $SQL = "
        SELECT  disease_name AS value
               ,disease_name AS label
               ,prescription_id AS id
               ,disease_name AS disease_name
        FROM prescription
        WHERE (prescription_id LIKE '{$patientDetail}%'
        OR disease_name LIKE '{$patientDetail}%')
        ORDER BY disease_name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchPatientDetails() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $patientDetail = $extractor[0];

        $SQL = "
        SELECT  name AS value
               ,CONCAT_WS(' :: ', uhid, name, father_name, spuse_name, address_area, phone) AS label
               ,patient_information_id AS id
               ,name AS Patient_Name
               ,father_name
               ,spuse_name AS husband_name
               ,address_area AS city_town
               ,address_street
               ,age_year
               ,age_month
               ,age_day
               ,phone
               ,gender
               ,uhid
               ,height
               ,weight
               ,bmi
        FROM patient_information
        WHERE (patient_information_id LIKE '{$patientDetail}%'
        OR name LIKE '{$patientDetail}%') 
       ORDER BY Patient_Name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchPatientDetailsWithPhone() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $patientDetail = $extractor[0];

        $SQL = "
        SELECT  phone AS value
               ,CONCAT_WS(' :: ', uhid, name, father_name, spuse_name, address_area, phone) AS label
               ,patient_information_id AS id
               ,name AS patient_name
               ,father_name
               ,spuse_name AS husband_name
               ,address_area AS city_town
               ,age_year
               ,age_month
               ,age_day
               ,phone
               ,gender
               ,uhid
        FROM patient_information
        WHERE (patient_information_id LIKE '{$patientDetail}%'
        OR phone LIKE '{$patientDetail}%')
        ORDER BY patient_name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchPatientDetailsWithAddress() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $patientDetail = $extractor[0];
        $patientDetail = str_replace('-', '', $patientDetail);

        $SQL = "
        SELECT DISTINCT address_area
               ,address_area AS value
               ,address_area AS label
               ,patient_information_id AS id
               ,name AS patient_name
               ,father_name
               ,spuse_name AS husband_name
               ,address_area AS city_town
               ,age
               ,phone
               ,gender
               ,uhid
        FROM patient_information
        WHERE (patient_information_id LIKE '{$patientDetail}%'
        OR REPLACE(address_area,' ', '') LIKE '{$patientDetail}%')
        GROUP BY REPLACE(address_area,' ', '')
        ORDER BY value
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchPatientDetailsWithUhid() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $patientDetail = $extractor[0];

        $SQL = "
        SELECT  uhid AS value
               ,CONCAT_WS(' :: ', uhid, name, father_name, spuse_name, address_area, phone) AS label
               ,patient_information_id AS id
               ,name AS patient_name
               ,father_name
               ,spuse_name AS husband_name
               ,address_area AS city_town
               ,address_street
               ,age_year
               ,age_month
               ,age_day
               ,phone
               ,gender
        FROM patient_information
        WHERE (uhid LIKE '{$patientDetail}%') 
        ORDER BY patient_name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    function getAddPrescribeMedicineFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $prescription_id  = $fn->getReqParam('prescription_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $SQLEV = "
        SELECT ev.employee_id
        FROM employee_visit ev
        WHERE ev.patient_visit_id = {$patient_visit_id}
        ORDER BY ev.employee_visit_id ASC
        ";
        $resultEV = $db->sql_query($SQLEV);
        $rowEV    = $db->sql_fetchrow($resultEV);

        $SQLPM = "
        SELECT pm.*
        FROM prescribe_medicine pm
        WHERE prescription_id = {$prescription_id}
        ";
        $resultPM   = $db->sql_query($SQLPM);

        while ($rowPM = $db->sql_fetchrow($resultPM)) {

            $fa1 = array();

            $fa1['title']            = $rowPM['medicine_name'];
            $fa1['employee_id']      = $rowEV['employee_id'];
            $fa1['dosage']           = $rowPM['dosage'];
            $fa1['instruction']      = $rowPM['instruction'];
            $fa1['patient_visit_id'] = $patient_visit_id;
            $fa1['product_id']       = $rowPM['product_id'];
            $fa1['days']             = $rowPM['days'];
            $fa1['creation_date']    = date("Y-m-d H:i:s");
            $fa1['created_by']       = $fn->getSessionParam('userName');

            $insertPrescriptionSQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'medicines_visit');
            $resultPrescriptionSQL = $db->sql_query($insertPrescriptionSQL);

        }
    }

    function getRemovePrescribeMedicine() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $prescription_id  = $fn->getReqParam('prescription_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');

        $SQLPM = "
        SELECT pm.*
        FROM prescribe_medicine pm
        WHERE prescription_id = {$prescription_id}
        ";
        $resultPM   = $db->sql_query($SQLPM);

        while ($rowPM = $db->sql_fetchrow($resultPM)) {
            $DeleteMedicine = "
            DELETE FROM medicines_visit
            WHERE title = '{$rowPM['medicine_name']}'
            AND patient_visit_id = {$patient_visit_id}
            ";
            $resultMedicine = $db->sql_query($DeleteMedicine);
        }
    }

    function getCreateAdmission() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $patientVisitRec  = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $patient_visit_id);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "WHERE site_id = {$cpSiteIdSession}";
        }

        $SQLCode = "
        SELECT MAX(CONVERT(code, UNSIGNED INTEGER)) + 1 AS code
        FROM in_patient
        {$appendSql}
        ";
        $resultCode = $db->sql_query($SQLCode);
        $rowCode    = $db->sql_fetchrow($resultCode);

        if($rowCode['code'] != ""){
            $code = $rowCode['code'];
        }
        else{
            $code = "1000";
        }
        
        $fa = array();

        $fa['code']                   = $code;
        $fa['date_admitted']          = date("Y-m-d");
        $fa['time_admitted']          = date("H:i:s");
        $fa['patient_visit_id']       = $patient_visit_id;
        $fa['patient_information_id'] = $patientVisitRec['patient_information_id'];
        $fa['weight']                 = $patientVisitRec['weight'];
        $fa['height']                 = $patientVisitRec['height'];
        $fa['blood_pressure']         = $patientVisitRec['blood_pressure'];
        $fa['pulse_rate']             = $patientVisitRec['pulse_rate'];
        $fa['respiratory_rate']       = $patientVisitRec['respiratory_rate'];
        $fa['bmi']                    = $patientVisitRec['bmi'];
        $fa['SpO2']                   = $patientVisitRec['SpO2'];
        $fa['temperature']            = $patientVisitRec['temperature'];
        $fa['employee_id']            = $patientVisitRec['employee_id'];
        $fa['status']                 = 'Admitted';
        $fa['nursing_fees']           = 0;
        $fa['site_id']                = $cpSiteIdSession;
        $fa['creation_date']          = date("Y-m-d H:i:s");
        $fa['created_by']             = $fn->getSessionParam('userName');

        $insertSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'in_patient');
        $resultSQL = $db->sql_query($insertSQL);
        $in_patient_id = $db->sql_nextid();

        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }

        $rowEmployee = $fn->getRecordRowByID('employee', 'employee_id', $patientVisitRec['employee_id']);

        $fa2 = array();
        $fa2['consultation_fees'] = $rowEmployee['consultation_fees'];
        $fa2['employee_id']       = $rowEmployee['employee_id'];
        $fa2['in_patient_id']     = $in_patient_id;
        $fa2['creation_date']     = date("Y-m-d H:i:s");
        $fa2['created_by']        = $fn->getSessionParam('userName');

        //$insertEmployeeVisitSQL   = $dbUtil->getInsertSQLStringFromArray($fa2, 'employee_in_patient');
        //$resultEmployeeVisitSQL   = $db->sql_query($insertEmployeeVisitSQL);

        return $in_patient_id;  
    }

    function getUpdateConsultingFeesLink() {
        $fn     = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db     = Zend_Registry::get('db');
        
        $employee_visit_id = $fn->getReqParam('employee_visit_id');
        $consultation_fees = $fn->getReqParam('consultation_fees');
        $patient_visit_id  = $fn->getReqParam('patient_visit_id');

        $SQLEmpVisit = "
        SELECT ev.consultation_fees
               ,ev.employee_visit_id
               ,e.fees_commission_type
               ,e.fees_commission
        FROM employee_visit ev
        LEFT JOIN `employee` e ON (e.employee_id = ev.employee_id)
        WHERE ev.employee_visit_id = {$employee_visit_id}
        ORDER BY ev.employee_visit_id ASC
        ";
        $resultEmpVisit = $db->sql_query($SQLEmpVisit);
        $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

        $fees_commission = 0;
        if($consultation_fees > 0){
            if($rowEmpVisit['fees_commission_type'] == "%"){
                $fees_commission = ($rowEmpVisit['consultation_fees'] * $rowEmpVisit['fees_commission']) / 100;
            }else{
                $fees_commission = $rowEmpVisit['fees_commission'];
            }
        }
        
        $faEV['consultation_fees'] = $consultation_fees;
        $faEV['fees_commission']    = $fees_commission;

        $whereCondition = "
        WHERE patient_visit_id = {$patient_visit_id}
        AND employee_visit_id = {$employee_visit_id}
        ";
        
        $SQLEV = $dbUtil->getUpdateSQLStringFromArray($faEV, 'employee_visit', $whereCondition);
        $db->sql_query($SQLEV);
    }

    function getUpdateEmployeeIdOnAttendance() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');

        //http://habibiahms.localhost/admin/index.php?module=hms_patientVisit&_spAction=UpdateEmployeeIdOnAttendance&showHTML=0';

        $SQLAttendance = "
        SELECT staff_id 
              ,attendance_id
        FROM attendance
        WHERE employee_id IS NULL
        ";
        $resultAttendance = $db->sql_query($SQLAttendance);
        while($rowAttendance    = $db->sql_fetchrow($resultAttendance)){
            $SQLEmployee = "
            SELECT employee_id 
            FROM employee
            WHERE staff_id = '{$rowAttendance['staff_id']}'
            ";
            $resultEmployee = $db->sql_query($SQLEmployee);
            $rowEmployee    = $db->sql_fetchrow($resultEmployee);

            $SQLUpdateAttendance = "
            UPDATE attendance SET employee_id = '{$rowEmployee['employee_id']}'
            WHERE attendance_id = '{$rowAttendance['attendance_id']}'
            ";
            $resultUpdateAttendance = $db->sql_query($SQLUpdateAttendance);
        }
    }

    function getInvestigationsCategorySortOrderUpdate() {
        $fn       = Zend_Registry::get('fn');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $db       = Zend_Registry::get('db');

        $valuelist_id = $fn->getReqParam('valuelistId', array());
        
        $count = 1;
        foreach ($valuelist_id as $valuelist){
            $SQLUpdateValuelist = "
            UPDATE valuelist SET sort_order = '{$count}'
            WHERE valuelist_id = '{$valuelist}'
            ";
            $resultUpdateValuelist = $db->sql_query($SQLUpdateValuelist);

            $count++;
        }
    }

    /**
     *
     */
    function getUpdateTownSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $patient_information_id = $fn->getReqParam('patient_information_id');        
        $address_area_update  = $fn->getPostParam('address_area_update');

        if (!$this->getUpdateTownFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['address_area']  = $address_area_update;

        $SQLTown="
        SELECT p.address_area
        FROM patient_information p
        WHERE patient_information_id = '{$patient_information_id}'
        ";
        $resultTown   = $db->sql_query($SQLTown);
        $rowTown = $db->sql_fetchrow($resultTown);

        $SQLUpdateTown = "
        UPDATE patient_information SET address_area = '{$address_area_update}'
        WHERE address_area = '{$rowTown['address_area']}'
        ";
        $resultUpdateTown = $db->sql_query($SQLUpdateTown);

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getUpdateTownFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('address_area_update', 'Please enter Town');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintDneReport() {
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
        $pdf->SetSubject('DNE Report');
        $pdf->SetTitle('DNE Report');

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
              ,e.first_name
              ,e.category
              ,dr.creation_date AS dneDate
              ,dr.department_title
              ,dr.report_heading
              ,dr.ref_by
              ,dr.study
              ,dr.posterior_tongue
              ,dr.vallecullae
              ,dr.epiglottis
              ,dr.aryepiglottic_fold
              ,dr.ventricular_bands
              ,dr.pyriform_fossas
              ,dr.oesophagus
              ,dr.advised
              ,dr.vocal_cords
              ,dr.impression
              ,dr.report_type
              ,dr.specialist
        FROM patient_visit pv
        LEFT JOIN (dne_report dr) ON (dr.patient_visit_id = pv.patient_visit_id)
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN (employee e) ON (e.employee_id = dr.employee_id)
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
          AND dr.report_type = 'Throat'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $category = '';
        if($company['category'] != ""){
          $category = '('.$company['category'].')';
        }

        $pdf->SetFont('', '', 9);

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

        $dneDate = $fn->getCPDate($company['dneDate'], 'd-M-Y');

        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td colspan="2" align="center" style="font-size:16px;font-weight:bold;">'.$company['department_title'].'<br/></td>
            </tr>
            <tr>
                <td width="50%">Visit Code : '.$visit_code.'</td>
                <td width="50%">Ref By : '.$company['ref_by'].'</td>
            </tr>
            <tr>
                <td width="50%">Patient Name : '.$company['patient_name'].'</td>
                <td width="50%">Study : '.$company['study'].'</td>
            </tr>
            <tr>
                <td width="50%">Age : '.$age.'</td>
                <td width="50%">Examined By : '.$company['first_name'].'</td>
            </tr>
            <tr>
              <td width="50%">Sex : '.$gender.'</td>
              <td width="50%">Date : '.$currentDate.'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;">
            <tr>
                <td width="30%" style="font-weight:bold;">Posterior 1/3 of tongue</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['posterior_tongue'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Vallecullae</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['vallecullae'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Epiglottis</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['epiglottis'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Aryepiglottic Fold</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['aryepiglottic_fold'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Ventricular Bands</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['ventricular_bands'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Vocal Cords</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['vocal_cords'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Pyriform Fossas</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['pyriform_fossas'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Oesophagus</td>
                <td width="4%">:</td>
                <td width="66%">'.$company['oesophagus'].'</td>
            </tr>
        </table>
        <br/><br/>
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;color:#FF0000;">
            <tr>
                <td width="20%" style="font-weight:bold;">IMPRESSION</td>
                <td width="4%">:</td>
                <td width="76%">'.$company['impression'].'</td>
            </tr>
        </table>
        <br/><br/>
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;color:#3b7811;">
            <tr>
                <td width="12%">Advised</td>
                <td width="4%">:</td>
                <td width="84%">'.$company['advised'].'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <table border="0" width="100%" cellpadding="4" style="line-height:30px;">
            <tr>
                <td width="100%" align="center" style="font-size:16px;text-decoration:underline;">'.$company['report_heading'].'</td>
            </tr>
        </table>
        ';

        $SQLPV = "
        SELECT pv.dne_report_notes
        FROM patient_visit pv
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
        ";
        $resultPv  = $db->sql_query($SQLPV);
        $numRowsPv = $db->sql_numrows($resultPv);
        $rowPv     = $db->sql_fetchrow($resultPv);

        $tbl4 = '<table cellpadding="4" border="0">'; 

        $SQLMedia = "
        SELECT file_name
        FROM media
        WHERE record_id = '{$patient_visit_id}'
        AND room_name   = 'hms_patientVisit'
        AND record_type = 'picture'
        ORDER BY sort_order ASC
        ";
        $resultMedia  = $db->sql_query($SQLMedia);
        $numRowsMedia = $db->sql_numrows($resultMedia);

        if($numRowsMedia > 0) {
          $count = 1;
          while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
            $tbl4 = $tbl4.'<tr>';
            $tbl4 = $tbl4.'<td><img src="'.$imageAttached.'" width="250" height="180"></td>';
            $tbl4 = $tbl4.'</tr>';
            $count++;
          }
        }

        $tbl4 = $tbl4.'</table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px;line-height:20px;color:#3b7811;font-weight:bold;">
            <tr>
                <td width="100%" align="center">'.$company['first_name'].'</td>
            </tr>
            <tr>
                <td width="100%" align="center">('.$company['specialist'].')</td>
            </tr>
        </table>
        ';

        $tblMain = '
        '.$tbl1.'
        '.$tbl3.'
        <table>
            <tr>
              <td width="70%">'.$tbl2.'</td>
              <td width="30%">'.$tbl4.'</td>
            </tr>
        </table>
        <br/><br/>
        '.$tbl5.'
        ';

        $pdf->writeHTML($tblMain, false, false, false, false, '');
        $download_title = 'DNE_Report.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintDneEarReport() {
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
        $pdf->SetSubject('DNE Report');
        $pdf->SetTitle('DNE Report');

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
              ,e.first_name
              ,e.category
              ,dr.creation_date AS dneDate
              ,dr.department_title
              ,dr.report_heading
              ,dr.ref_by
              ,dr.study
              ,dr.advised
              ,dr.impression
              ,dr.report_type
              ,dr.specialist
              ,dr.eac_right
              ,dr.tympanic_membrane_right
              ,dr.middle_ear_right
              ,dr.eac_left
              ,dr.tympanic_membrane_left
              ,dr.middle_ear_left
        FROM patient_visit pv
        LEFT JOIN (dne_report dr) ON (dr.patient_visit_id = pv.patient_visit_id)
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN (employee e) ON (e.employee_id = dr.employee_id)
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
          AND dr.report_type = 'Ear'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $category = '';
        if($company['category'] != ""){
          $category = '('.$company['category'].')';
        }

        $pdf->SetFont('', '', 9);

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

        $dneDate = $fn->getCPDate($company['dneDate'], 'd-M-Y');

        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td colspan="2" align="center" style="font-size:16px;font-weight:bold;">'.$company['department_title'].'<br/></td>
            </tr>
            <tr>
                <td width="50%">Visit Code : '.$visit_code.'</td>
                <td width="50%">Ref By : '.$company['ref_by'].'</td>
            </tr>
            <tr>
                <td width="50%">Patient Name : '.$company['patient_name'].'</td>
                <td width="50%">Study : '.$company['study'].'</td>
            </tr>
            <tr>
                <td width="50%">Age : '.$age.'</td>
                <td width="50%">Examined By : '.$company['first_name'].'</td>
            </tr>
            <tr>
              <td width="50%">Sex : '.$gender.'</td>
              <td width="50%">Date : '.$currentDate.'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;">
            <tr>
                <td width="30%" style="font-weight:bold;"></td>
                <td width="4%"></td>
                <td width="33%" style="font-weight:bold;">RIGHT</td>
                <td width="33%" style="font-weight:bold;">LEFT</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">EAC</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['eac_right'].'</td>
                <td width="33%">'.$company['eac_left'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Tympanic Membrane</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['tympanic_membrane_right'].'</td>
                <td width="33%">'.$company['tympanic_membrane_left'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Middle Ear</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['middle_ear_right'].'</td>
                <td width="33%">'.$company['middle_ear_left'].'</td>
            </tr>
        </table>
        <br/><br/>
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;color:#FF0000;">
            <tr>
                <td width="20%" style="font-weight:bold;">IMPRESSION</td>
                <td width="4%">:</td>
                <td width="76%">'.$company['impression'].'</td>
            </tr>
        </table>
        <br/><br/>
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;color:#3b7811;">
            <tr>
                <td width="12%">Advised</td>
                <td width="4%">:</td>
                <td width="84%">'.$company['advised'].'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <table border="0" width="100%" cellpadding="4" style="line-height:30px;">
            <tr>
                <td width="100%" align="center" style="font-size:16px;text-decoration:underline;">'.$company['report_heading'].'</td>
            </tr>
        </table>
        ';

        $SQLPV = "
        SELECT pv.dne_report_notes
        FROM patient_visit pv
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
        ";
        $resultPv  = $db->sql_query($SQLPV);
        $numRowsPv = $db->sql_numrows($resultPv);
        $rowPv     = $db->sql_fetchrow($resultPv);

        $tbl4 = '<table cellpadding="4" border="0">'; 

        $SQLMedia = "
        SELECT file_name
        FROM media
        WHERE record_id = '{$patient_visit_id}'
        AND room_name   = 'hms_patientVisit'
        AND record_type = 'dneEarpicture'
        ORDER BY sort_order ASC
        ";
        $resultMedia  = $db->sql_query($SQLMedia);
        $numRowsMedia = $db->sql_numrows($resultMedia);

        if($numRowsMedia > 0) {
          $count = 1;
          while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
            $tbl4 = $tbl4.'<tr>';
            $tbl4 = $tbl4.'<td><img src="'.$imageAttached.'" width="250" height="180"></td>';
            $tbl4 = $tbl4.'</tr>';
            $count++;
          }
        }

        $tbl4 = $tbl4.'</table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px;line-height:20px;color:#3b7811;font-weight:bold;">
            <tr>
                <td width="100%" align="center">'.$company['first_name'].'</td>
            </tr>
            <tr>
                <td width="100%" align="center">('.$company['specialist'].')</td>
            </tr>
        </table>
        ';

        $tblMain = '
        '.$tbl1.'
        '.$tbl3.'
        <table>
            <tr>
              <td width="70%">'.$tbl2.'</td>
              <td width="30%">'.$tbl4.'</td>
            </tr>
        </table>
        <br/><br/>
        '.$tbl5.'
        ';

        $pdf->writeHTML($tblMain, false, false, false, false, '');
        $download_title = 'DNE_Report.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintDneNoseReport() {
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
        $pdf->SetSubject('DNE Report');
        $pdf->SetTitle('DNE Report');

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
              ,e.first_name
              ,e.category
              ,dr.creation_date AS dneDate
              ,dr.department_title
              ,dr.report_heading
              ,dr.ref_by
              ,dr.study
              ,dr.advised
              ,dr.impression
              ,dr.report_type
              ,dr.specialist
              ,dr.nasal_septum
              ,dr.nasal_cavity_right
              ,dr.nasal_cavity_left
              ,dr.inferior_turbinate_right
              ,dr.inferior_turbinate_left
              ,dr.middle_turbinate_right
              ,dr.middle_turbinate_left
              ,dr.paranasal_sinuses
              ,dr.omu_right
              ,dr.omu_left
              ,dr.choanae_right
              ,dr.choanae_left
        FROM patient_visit pv
        LEFT JOIN (dne_report dr) ON (dr.patient_visit_id = pv.patient_visit_id)
        LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
        LEFT JOIN (employee e) ON (e.employee_id = dr.employee_id)
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
          AND dr.report_type = 'Nose'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $category = '';
        if($company['category'] != ""){
          $category = '('.$company['category'].')';
        }

        $pdf->SetFont('', '', 9);

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

        $dneDate = $fn->getCPDate($company['dneDate'], 'd-M-Y');

        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td colspan="2" align="center" style="font-size:16px;font-weight:bold;">'.$company['department_title'].'<br/></td>
            </tr>
            <tr>
                <td width="50%">Visit Code : '.$visit_code.'</td>
                <td width="50%">Ref By : '.$company['ref_by'].'</td>
            </tr>
            <tr>
                <td width="50%">Patient Name : '.$company['patient_name'].'</td>
                <td width="50%">Study : '.$company['study'].'</td>
            </tr>
            <tr>
                <td width="50%">Age : '.$age.'</td>
                <td width="50%">Examined By : '.$company['first_name'].'</td>
            </tr>
            <tr>
              <td width="50%">Sex : '.$gender.'</td>
              <td width="50%">Date : '.$currentDate.'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;">
            <tr>
                <td width="30%" style="font-weight:bold;"></td>
                <td width="4%"></td>
                <td width="33%" style="font-weight:bold;">RIGHT</td>
                <td width="33%" style="font-weight:bold;">LEFT</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Nasal Septum</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['nasal_septum'].'</td>
                <td width="33%">-</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Nasal Cavity</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['nasal_cavity_right'].'</td>
                <td width="33%">'.$company['nasal_cavity_left'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Inferior Turbinate</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['inferior_turbinate_right'].'</td>
                <td width="33%">'.$company['inferior_turbinate_left'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Middle Turbinate</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['middle_turbinate_right'].'</td>
                <td width="33%">'.$company['middle_turbinate_left'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Paranasal Sinuses</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['paranasal_sinuses'].'</td>
                <td width="33%">-</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">OMU</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['omu_right'].'</td>
                <td width="33%">'.$company['omu_left'].'</td>
            </tr>
            <tr>
                <td width="30%" style="font-weight:bold;">Choanae</td>
                <td width="4%">:</td>
                <td width="33%">'.$company['choanae_right'].'</td>
                <td width="33%">'.$company['choanae_left'].'</td>
            </tr>
        </table>
        <br/><br/>
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;color:#FF0000;">
            <tr>
                <td width="20%" style="font-weight:bold;">IMPRESSION</td>
                <td width="4%">:</td>
                <td width="76%">'.$company['impression'].'</td>
            </tr>
        </table>
        <br/><br/>
        <table border="0" width="100%" cellpadding="4" style="line-height:25px;color:#3b7811;">
            <tr>
                <td width="12%">Advised</td>
                <td width="4%">:</td>
                <td width="84%">'.$company['advised'].'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <table border="0" width="100%" cellpadding="4" style="line-height:30px;">
            <tr>
                <td width="100%" align="center" style="font-size:16px;text-decoration:underline;">'.$company['report_heading'].'</td>
            </tr>
        </table>
        ';

        $SQLPV = "
        SELECT pv.dne_report_notes
        FROM patient_visit pv
        WHERE pv.patient_visit_id = '{$patient_visit_id}'
        ";
        $resultPv  = $db->sql_query($SQLPV);
        $numRowsPv = $db->sql_numrows($resultPv);
        $rowPv     = $db->sql_fetchrow($resultPv);

        $tbl4 = '<table cellpadding="4" border="0">'; 

        $SQLMedia = "
        SELECT file_name
        FROM media
        WHERE record_id = '{$patient_visit_id}'
        AND room_name   = 'hms_patientVisit'
        AND record_type = 'dneNosepicture'
        ORDER BY sort_order ASC
        ";
        $resultMedia  = $db->sql_query($SQLMedia);
        $numRowsMedia = $db->sql_numrows($resultMedia);

        if($numRowsMedia > 0) {
          $count = 1;
          while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
            $tbl4 = $tbl4.'<tr>';
            $tbl4 = $tbl4.'<td><img src="'.$imageAttached.'" width="250" height="180"></td>';
            $tbl4 = $tbl4.'</tr>';
            $count++;
          }
        }

        $tbl4 = $tbl4.'</table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:13px;line-height:20px;color:#3b7811;font-weight:bold;">
            <tr>
                <td width="100%" align="center">'.$company['first_name'].'</td>
            </tr>
            <tr>
                <td width="100%" align="center">('.$company['specialist'].')</td>
            </tr>
        </table>
        ';

        $tblMain = '
        '.$tbl1.'
        '.$tbl3.'
        <table>
            <tr>
              <td width="70%">'.$tbl2.'</td>
              <td width="30%">'.$tbl4.'</td>
            </tr>
        </table>
        <br/><br/>
        '.$tbl5.'
        ';

        $pdf->writeHTML($tblMain, false, false, false, false, '');
        $download_title = 'DNE_Report.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getDneReportPortalSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getDneReportPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $patient_visit_id   = $fn->getPostParam('patient_visit_id');
        $department_title   = $fn->getPostParam('department_title');
        $report_heading     = $fn->getPostParam('report_heading');
        $ref_by             = $fn->getPostParam('ref_by');
        $study              = $fn->getPostParam('study');
        $employee_id        = $fn->getPostParam('examined_by');
        $posterior_tongue   = $fn->getPostParam('posterior_tongue');
        $vallecullae        = $fn->getPostParam('vallecullae');
        $epiglottis         = $fn->getPostParam('epiglottis');
        $aryepiglottic_fold = $fn->getPostParam('aryepiglottic_fold');
        $ventricular_bands  = $fn->getPostParam('ventricular_bands');
        $pyriform_fossas    = $fn->getPostParam('pyriform_fossas');
        $oesophagus         = $fn->getPostParam('oesophagus');
        $advised            = $fn->getPostParam('advised');
        $vocal_cords        = $fn->getPostParam('vocal_cords');
        $impression         = $fn->getPostParam('impression');
        $report_type        = $fn->getPostParam('report_type');
        $specialist         = $fn->getPostParam('specialist');
        $eac_right         = $fn->getPostParam('eac_right');
        $eac_left         = $fn->getPostParam('eac_left');
        $tympanic_membrane_right = $fn->getPostParam('tympanic_membrane_right');
        $tympanic_membrane_left  = $fn->getPostParam('tympanic_membrane_left');
        $middle_ear_right        = $fn->getPostParam('middle_ear_right');
        $middle_ear_left         = $fn->getPostParam('middle_ear_left');

        $nasal_septum         = $fn->getPostParam('nasal_septum');
        $nasal_cavity_right         = $fn->getPostParam('nasal_cavity_right');
        $nasal_cavity_left         = $fn->getPostParam('nasal_cavity_left');
        $inferior_turbinate_right         = $fn->getPostParam('inferior_turbinate_right');
        $inferior_turbinate_left         = $fn->getPostParam('inferior_turbinate_left');
        $middle_turbinate_right         = $fn->getPostParam('middle_turbinate_right');
        $middle_turbinate_left         = $fn->getPostParam('middle_turbinate_left');
        $paranasal_sinuses         = $fn->getPostParam('paranasal_sinuses');
        $omu_right         = $fn->getPostParam('omu_right');
        $omu_left         = $fn->getPostParam('omu_left');
        $choanae_right         = $fn->getPostParam('choanae_right');
        $choanae_left         = $fn->getPostParam('choanae_left');

        $SQLDneReport = "
        SELECT dne_report_id
        FROM dne_report
        WHERE patient_visit_id = {$patient_visit_id}
        AND report_type = '{$report_type}'
        ";
        $resultDneReport  = $db->sql_query($SQLDneReport);
        $numRowsDneReport = $db->sql_numrows($resultDneReport);

        if($numRowsDneReport > 0){
          $fa = array();
          $fa['department_title']   = $department_title;
          $fa['report_heading']     = $report_heading;
          $fa['ref_by']             = $ref_by;
          $fa['study']              = $study;
          $fa['employee_id']        = $employee_id;
          $fa['posterior_tongue']   = $posterior_tongue;
          $fa['vallecullae']        = $vallecullae;
          $fa['epiglottis']         = $epiglottis;
          $fa['aryepiglottic_fold'] = $aryepiglottic_fold;
          $fa['ventricular_bands']  = $ventricular_bands;
          $fa['pyriform_fossas']    = $pyriform_fossas;
          $fa['oesophagus']         = $oesophagus;
          $fa['advised']            = $advised;
          $fa['specialist']         = $specialist;
          $fa['vocal_cords']        = $vocal_cords;
          $fa['impression']         = $impression;
          $fa['report_type']        = $report_type;
          $fa['modification_date']  = date("Y-m-d H:i:s");
          $fa['modified_by']        = $fn->getSessionParam('userName');
          $fa['eac_right']         = $eac_right;
          $fa['eac_left']         = $eac_left;
          $fa['tympanic_membrane_right'] = $tympanic_membrane_right;
          $fa['tympanic_membrane_left']  = $tympanic_membrane_left;
          $fa['middle_ear_right']        = $middle_ear_right;
          $fa['middle_ear_left']         = $middle_ear_left;

          $fa['nasal_septum']         = $nasal_septum;
          $fa['nasal_cavity_right']         = $nasal_cavity_right;
          $fa['nasal_cavity_left']         = $nasal_cavity_left;
          $fa['inferior_turbinate_right']         = $inferior_turbinate_right;
          $fa['inferior_turbinate_left']         = $inferior_turbinate_left;
          $fa['middle_turbinate_right']         = $middle_turbinate_right;
          $fa['middle_turbinate_left']         = $middle_turbinate_left;
          $fa['paranasal_sinuses']         = $paranasal_sinuses;
          $fa['omu_right']         = $omu_right;
          $fa['omu_left']         = $omu_left;
          $fa['choanae_right']         = $choanae_right;
          $fa['choanae_left']         = $choanae_left;

          $whereCondition = "
          WHERE patient_visit_id = {$patient_visit_id} AND report_type = '{$report_type}'
          ";
          $SQLDne = $dbUtil->getUpdateSQLStringFromArray($fa, 'dne_report', $whereCondition);
          $db->sql_query($SQLDne);
        } else {
          $fa = array();
          $fa['department_title']   = $department_title;
          $fa['report_heading']     = $report_heading;
          $fa['ref_by']             = $ref_by;
          $fa['study']              = $study;
          $fa['employee_id']        = $employee_id;
          $fa['patient_visit_id']   = $patient_visit_id;
          $fa['posterior_tongue']   = $posterior_tongue;
          $fa['vallecullae']        = $vallecullae;
          $fa['epiglottis']         = $epiglottis;
          $fa['aryepiglottic_fold'] = $aryepiglottic_fold;
          $fa['ventricular_bands']  = $ventricular_bands;
          $fa['pyriform_fossas']    = $pyriform_fossas;
          $fa['oesophagus']         = $oesophagus;
          $fa['advised']            = $advised;
          $fa['specialist']         = $specialist;
          $fa['vocal_cords']        = $vocal_cords;
          $fa['impression']         = $impression;
          $fa['report_type']        = $report_type;
          $fa['creation_date']      = date("Y-m-d H:i:s");
          $fa['created_by']         = $fn->getSessionParam('userName');
          $fa['tympanic_membrane_right'] = $tympanic_membrane_right;
          $fa['tympanic_membrane_left']  = $tympanic_membrane_left;
          $fa['middle_ear_right']        = $middle_ear_right;
          $fa['middle_ear_left']         = $middle_ear_left;
          $fa['nasal_septum']         = $nasal_septum;
          $fa['nasal_cavity_right']         = $nasal_cavity_right;
          $fa['nasal_cavity_left']         = $nasal_cavity_left;
          $fa['inferior_turbinate_right']         = $inferior_turbinate_right;
          $fa['inferior_turbinate_left']         = $inferior_turbinate_left;
          $fa['middle_turbinate_right']         = $middle_turbinate_right;
          $fa['middle_turbinate_left']         = $middle_turbinate_left;
          $fa['paranasal_sinuses']         = $paranasal_sinuses;
          $fa['omu_right']         = $omu_right;
          $fa['omu_left']         = $omu_left;
          $fa['choanae_right']         = $choanae_right;
          $fa['choanae_left']         = $choanae_left;

          $SQLDne = $dbUtil->getInsertSQLStringFromArray($fa, 'dne_report');
          $db->sql_query($SQLDne);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDneReportPortalValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

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
    function getCalculateBMI() {
        $fn       = Zend_Registry::get('fn');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $db       = Zend_Registry::get('db');

        $height = $fn->getReqParam('height');
        $weight = $fn->getReqParam('weight');

        $bmi1 = ($height * $height) / 10000;

        $bmi = number_format($weight / $bmi1 , 2);

        return $bmi;      
    }
}
