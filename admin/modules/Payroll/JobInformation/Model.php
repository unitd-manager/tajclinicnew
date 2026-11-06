<?
class CPL_Admin_Modules_Payroll_JobInformation_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT j.*
        ,e.emp_code
        ,(CASE 
          WHEN e.salutation = 'Dr' 
          THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
          ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
          END) AS employee_name
        ,e.first_name
        ,e.last_name
        ,e.phone
        ,e.email
        ,e.salary
        ,e.nric_no
        ,e.position
        ,e.date_of_expiry
        ,e.spass_no
        ,e.fin_no
        ,e.employee_work_type
        FROM `job_information` j
        LEFT JOIN (employee e) ON (e.employee_id = j.employee_id)
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

        $employee_id     = $fn->getReqParam('employee_id');
        $employee_status = $fn->getReqParam('employee_status');

        if ($employee_id != "") {
            $searchVar->sqlSearchVar[] = "j.employee_id = '{$employee_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "j.employee_id = '{$tv['record_id']}'";
        } else {
            //$fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.salary_id');

          /*  if ($status != "") {
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

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "j.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(j.flag != 1 OR j.flag IS null)";
            }

            $searchVar->sqlSearchVar[] = "j.status = 'Current'";

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
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('employee_name', 'Please Select the employee');
        $validate->validateData('employee_id', 'Please Select the employee');
        $employee_id = $fn->getPostParam('employee_id');

        if($employee_id != ''){
            $employeeExist = $fn->getRecordCount('job_information', "employee_id = {$employee_id}");
            if($employeeExist > 0){
                $validate->errorArray['employee_name']['name'] = "employee_name";
                $validate->errorArray['employee_name']['msg']  = "Employee already exists.";
            }
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = 'Current';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        //$validate->validateData('first_name', 'Please enter the name');

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
        $cpCfg = Zend_Registry::get('cpCfg');

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

        $fa = $fn->addToFieldsArray($fa, 'join_date');
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'designation');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'probationary');
        $fa = $fn->addToFieldsArray($fa, 'emp_type');
        $fa = $fn->addToFieldsArray($fa, 'join_date');
        $fa = $fn->addToFieldsArray($fa, 'act_join_date');
        $fa = $fn->addToFieldsArray($fa, 'termination_date');
        $fa = $fn->addToFieldsArray($fa, 'termination_reason');
        $fa = $fn->addToFieldsArray($fa, 'payment_type');
        $fa = $fn->addToFieldsArray($fa, 'working_days');
        $fa = $fn->addToFieldsArray($fa, 'basic_pay');
        $fa = $fn->addToFieldsArray($fa, 'overtime');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_rate');
        $fa = $fn->addToFieldsArray($fa, 'cpf_applicable');
        $fa = $fn->addToFieldsArray($fa, 'cpf_account_no');
        $fa = $fn->addToFieldsArray($fa, 'income_tax_id');
        $fa = $fn->addToFieldsArray($fa, 'pay_cdac');
        $fa = $fn->addToFieldsArray($fa, 'pay_sinda');
        $fa = $fn->addToFieldsArray($fa, 'pay_mbmf');
        $fa = $fn->addToFieldsArray($fa, 'pay_eucf');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_payment');
        $fa = $fn->addToFieldsArray($fa, 'account_no');
        $fa = $fn->addToFieldsArray($fa, 'bank_name');
        $fa = $fn->addToFieldsArray($fa, 'bank_code');
        $fa = $fn->addToFieldsArray($fa, 'branch_code');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'income_tax_amount');
        $fa = $fn->addToFieldsArray($fa, 'allowance1');
        $fa = $fn->addToFieldsArray($fa, 'allowance2');
        $fa = $fn->addToFieldsArray($fa, 'allowance3');
        $fa = $fn->addToFieldsArray($fa, 'deduction1');
        $fa = $fn->addToFieldsArray($fa, 'deduction2');
        $fa = $fn->addToFieldsArray($fa, 'deduction3');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_sunday');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_holiday');
        $fa = $fn->addToFieldsArray($fa, 'sosco');
        $fa = $fn->addToFieldsArray($fa, 'accommodation');
        $fa = $fn->addToFieldsArray($fa, 'other_deduction');
        $fa = $fn->addToFieldsArray($fa, 'employer_epf');
        $fa = $fn->addToFieldsArray($fa, 'employer_socso');
        $fa = $fn->addToFieldsArray($fa, 'income_tax_file_no');
        $fa = $fn->addToFieldsArray($fa, 'epf_membership_no');
        $fa = $fn->addToFieldsArray($fa, 'sosco_membership_no');


        return $fa;
    }

    /**
     *
     */
    function getExportData1($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');


        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Contact_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Contact Id');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Salutation');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'First Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Last Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Position');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fax');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Mobile');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Subscribed');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Website');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Phone');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Fax');

        if($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Country');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Home Country');

        } else {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Flat');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Street');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Town');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'State');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Country');
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Category');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }
        //============================================================================= //
        foreach ($dataArray as $row){
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['contact_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['salutation']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['last_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['position']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone_direct']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['fax']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['subscribe']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_website']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_phone']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_fax']);

            if($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['comp_mul_address_country']);

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['address_country']);
            } else {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_flat']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_street']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_town']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_state']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_address_country']);
            }
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['c_category']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getContactByCompanyJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $company_id   = $fn->getReqParam('company_id');

        $json  = array();

        if ($company_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name) AS contact_name
        FROM contact
        WHERE company_id = '{$company_id}'
        ORDER BY contact_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['contact_id'], "caption" => $row['contact_name']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getMultipleAddress(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $company_id   = $fn->getReqParam('company_id');
        $json  = array();

        if($company_id == ""){
            return json_encode($json);
        }


        $SQL    = "
        SELECT   company_address_id
                 , CONCAT_WS(', ', address_flat, address_street, address_town, address_country) AS address
        FROM     company_address a
        WHERE    company_id = {$company_id}
        ORDER BY company_address_id
        ";

        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['company_address_id'], "caption" => $row['address']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getCompanyAddress(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $company_id   = $fn->getReqParam('company_id');

        $SQL = "
        SELECT *
        FROM company
        WHERE company_id = {$company_id}";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $json = array("address_street" => $row['address_street'], "address_flat" => $row['address_flat'],
                "address_town" => $row['address_town'], "address_state" => $row['address_state'],
                "address_country" => $row['address_country']
        );

        return json_encode($json);
    }

    /**
     *
     */
    function getEmailValidation(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $email   = $fn->getReqParam('email');
        $contact_id   = $fn->getReqParam('contact_id');
        $email  = trim($email);
        $append = "";

        if($contact_id != ""){
            $append = "AND contact_id != {$contact_id}";
        }

        $SQL = "
        SELECT email
        FROM   contact
        WHERE  email = '{$email}'
               AND email != ''
               {$append}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $check = ($numRows >= 1) ? 1 : 0;

        return $check;

    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'contact_id'          => $phpExcel->getFldObj('Contact ID')
             ,'salutation'          => $phpExcel->getFldObj('Salutation')
             ,'first_name'          => $phpExcel->getFldObj('First Name')
             ,'last_name'           => $phpExcel->getFldObj('Last Name')
             ,'email'               => $phpExcel->getFldObj('Email')
             ,'position'            => $phpExcel->getFldObj('Position')
             ,'phone_direct'        => $phpExcel->getFldObj('Phone')
             ,'fax'                 => $phpExcel->getFldObj('Fax')
             ,'mobile'              => $phpExcel->getFldObj('Mobile')
             ,'subscribe'           => $phpExcel->getFldObj('Subscribed')
             ,'c_company_name'      => $phpExcel->getFldObj('Company Name')
             ,'c_website'           => $phpExcel->getFldObj('Company Website')
             ,'c_phone'             => $phpExcel->getFldObj('Company Phone')
             ,'c_fax'               => $phpExcel->getFldObj('Company Fax')

             ,'c_address_flat'      => $phpExcel->getFldObj('Flat')
             ,'c_address_street'    => $phpExcel->getFldObj('Street')
             ,'c_address_town'      => $phpExcel->getFldObj('Town')
             ,'c_address_state'     => $phpExcel->getFldObj('State')
             ,'c_address_country'   => $phpExcel->getFldObj('Country')

             ,'c_category'           => $phpExcel->getFldObj('Category')
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
    function getSearchEmployeeDetails(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $employeeDetail = $extractor[0];

        $SQL = "
        SELECT  CONCAT_WS(' ', first_name, last_name) AS value
               ,CONCAT_WS(' ', first_name, last_name) AS label
               ,employee_id AS id
              ,(CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE (employee_id LIKE '%{$employeeDetail}%'
        OR first_name LIKE '%{$employeeDetail}%'
        OR last_name LIKE '%{$employeeDetail}%'
        OR nric_no LIKE '%{$employeeDetail}%')
        AND status = 'Current'
        AND add_in_payroll = 1
        ORDER BY Employee_Name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }
}
