<?
class CPL_Admin_Modules_Hms_PatientInformation_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT p.*
              ,p.name AS patient_name
              ,c.company_name
              ,c.phone AS c_phone
              ,c.address_flat AS c_address_flat
              ,c.address_street AS c_address_street
              ,c.address_town AS c_address_town
              ,c.address_state AS c_address_state
              ,c.address_country AS c_address_country
              ,b.patient_information_source_id
              ,s.title AS site_title
        FROM patient_information p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN patient_relationinfo b ON (b.patient_information_source_id = p.patient_information_id)
        LEFT JOIN site s ON (p.site_id = s.site_id)
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
        $searchVar->mainTableAlias = 'p';

        $status                 = $fn->getReqParam('status');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $billType               = $fn->getReqParam('bill_type');
        $site_id                = $fn->getReqParam('site_id');

        if ($patient_information_id != "") {
            $searchVar->sqlSearchVar[] = "p.patient_information_id = '{$patient_information_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.patient_information_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.patient_information_id');

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }
            if ($billType != "") {
                $searchVar->sqlSearchVar[] = "p.bill_type = '{$billType}'";
            }

            if ($site_id != "") {
                $searchVar->sqlSearchVar[] = "p.site_id = '{$site_id}'";
            }

            if ($tv['keyword'] != "") {
                $nric = str_replace('-', '', $tv['keyword']);
                $searchVar->sqlSearchVar[] = "(
                       p.name         LIKE '%{$tv['keyword']}%'
                    OR p.father_name  LIKE '%{$tv['keyword']}%'
                    OR p.address_area LIKE '%{$tv['keyword']}%'
                    OR p.spuse_name   LIKE '%{$tv['keyword']}%'
                    OR p.phone        LIKE '%{$tv['keyword']}%'
                    OR p.mobile       LIKE '%{$tv['keyword']}%'
                    OR p.uhid         LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('name', 'Please enter the name');

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
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpCfg    = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $first_name      = $fn->getPostParam('first_name');
        $middle_name     = $fn->getPostParam('middle_name');
        $last_name       = $fn->getPostParam('last_name');
        $pass_type       = $fn->getPostParam('pass_type');
        $nric            = $fn->getPostParam('nric');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

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

        $fa = $this->getFields();
        $fa['first_name']   = strtoupper($first_name);
        $fa['middle_name']  = strtoupper($middle_name);
        $fa['last_name']    = strtoupper($last_name);

        $fa['patient_code'] = $patient_code;
        $fa['uhid']         = $uhid;
        $fa['first_admit']  = date('Y-m-d');

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id']  = $cpSiteIdSession;
        }

        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $pass_type              = $fn->getPostParam('pass_type');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $nric                   = $fn->getPostParam('nric', '', true);
        $phone                  = $fn->getPostParam('phone');
        $mobile                 = $fn->getPostParam('mobile');
        $email                  = $fn->getPostParam('email');

        $patientInfoRec = $fn->getRecordRowById('patient_information', 'patient_information_id', $patient_information_id);

        $validate->resetErrorArray();
        $validate->validateData('name', 'Please enter the name');

        if ($phone != '') {
            $validate->validateData("phone",  'Please enter only number eg: 85414745', "number");
        }

        if ($mobile != '') {
            $validate->validateData("mobile",  'Please enter only number eg: 85414745', "number");
        }

        if ($email != '') {
            $validate->validateData("email",  'Please enter the correct email address with @ and .', 'email');
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
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $first_name             = $fn->getPostParam('first_name');
        $middle_name            = $fn->getPostParam('middle_name');
        $last_name              = $fn->getPostParam('last_name');
        $pass_type              = $fn->getPostParam('pass_type');
        $nric                   = $fn->getPostParam('nric');
        $phone                  = $fn->getPostParam('phone');
        $patient_information_id = $fn->getReqParam('patient_information_id');

        $patientInfoRec = $fn->getRecordRowById('patient_information', 'patient_information_id', $patient_information_id);

        $fa = $this->getFields();
        $fa['first_name']   = strtoupper($first_name);
        $fa['middle_name']  = strtoupper($middle_name);
        $fa['last_name']    = strtoupper($last_name);
        $fa['phone']        = $phone;

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
        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'nric');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'middle_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');

        $fa = $fn->addToFieldsArray($fa, 'registration_no');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'dob');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');

        $fa = $fn->addToFieldsArray($fa, 'first_admit');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'father_name');
        $fa = $fn->addToFieldsArray($fa, 'mother_name');
        $fa = $fn->addToFieldsArray($fa, 'spuse_name');
        $fa = $fn->addToFieldsArray($fa, 'primary_contact');
        $fa = $fn->addToFieldsArray($fa, 'alergies');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'bill_type');
        $fa = $fn->addToFieldsArray($fa, 'worker_id');
        $fa = $fn->addToFieldsArray($fa, 'father_nric');
        $fa = $fn->addToFieldsArray($fa, 'mother_nric');
        $fa = $fn->addToFieldsArray($fa, 'relationship');
        $fa = $fn->addToFieldsArray($fa, 'serial_no_of_book');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'pass_type');
        $fa = $fn->addToFieldsArray($fa, 'remarks');
        $fa = $fn->addToFieldsArray($fa, 'age_year');
        $fa = $fn->addToFieldsArray($fa, 'age_month');
        $fa = $fn->addToFieldsArray($fa, 'age_day');
        $fa = $fn->addToFieldsArray($fa, 'previous_medical_history');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'name'      => $phpExcel->getFldObj('Name')
             ,'uhid'    => $phpExcel->getFldObj('UHID')
             ,'age_year'          => $phpExcel->getFldObj('Age')
             ,'gender'          => $phpExcel->getFldObj('Gender')
             ,'father_name'        => $phpExcel->getFldObj('Father Name')
             ,'spuse_name'             => $phpExcel->getFldObj('Husband Name')
             ,'phone'        => $phpExcel->getFldObj('Phone')
             ,'mobile'          => $phpExcel->getFldObj('Phone 2')
             ,'address_street'         => $phpExcel->getFldObj('Address')
             ,'address_area'    => $phpExcel->getFldObj('Town / City')
             ,'first_admit'           => $phpExcel->getFldObj('First Visit On')
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
              'nric'                 => $phpExcel->getImportFldObj('MRID')
             ,'registration_no'      => $phpExcel->getImportFldObj('IC / PP')
             ,'gender'               => $phpExcel->getImportFldObj('Gender')
             ,'dob'                  => $phpExcel->getImportFldObj('DOB')
             ,'race'                 => $phpExcel->getImportFldObj('Race')
             ,'phone'                => $phpExcel->getImportFldObj('Contact Number Phone')
             ,'mobile'               => $phpExcel->getImportFldObj('Contact Number Mobile')
             ,'email'                => $phpExcel->getImportFldObj('Email')
             ,'address_country'      => $phpExcel->getImportFldObj('Country')
        );


        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'hms_patientInformation'
            ,'matchFieldArr'       => array()
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
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
    function getUpdateCompanyDetails() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $company_id     = $fn->getReqParam('company_id');
        $arr = array('phone' => '', 'address_flat' => '', 'address_street' => '', 'address_town' => '', 'address_state' => '' , 'address_country' => '');

        if($company_id != ''){
            $SQL    = "
            SELECT c.*
                  ,gc.name AS c_address_country
            FROM company c
            LEFT JOIN geo_country gc ON (gc.country_code = c.address_country)
            WHERE company_id = {$company_id}
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            $arr['phone']         = $row['phone'];
            $arr['address_flat']  = $row['address_flat'];
            $arr['address_street']= $row['address_street'];
            $arr['address_town']  = $row['address_town'];
            $arr['address_state']  = $row['address_state'];
            $arr['address_country']  = $row['c_address_country'];
        }

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getHmsPatientInformationHmsPatientInformationLinkSQL($id) {

        return "
        SELECT a.patient_information_id
              ,a.name AS patient_name
              ,a.nric
        FROM `patient_information` a
        LEFT JOIN (patient_relationinfo b) ON (b.patient_information_id = a.patient_information_id)
        WHERE b.patient_information_source_id = {$id}
        UNION
        SELECT a.patient_information_id
              ,a.name AS patient_name
              ,a.nric
        FROM `patient_information` a
        LEFT JOIN (patient_relationinfo b) ON (b.patient_information_source_id = a.patient_information_id)
        WHERE b.patient_information_id = {$id}
        ";
    }
}
