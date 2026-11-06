<?
class CPL_Admin_Modules_Hms_Vaccination_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT mt.*
        FROM medical_test mt
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
        $searchVar->mainTableAlias = 'mt';

        $category   = $fn->getReqParam('category');
        $medical_test_id   = $fn->getReqParam('medical_test_id');
       // $company_name = $fn->getReqParam('company_name');
        $group_name   = $fn->getReqParam('group_name');

        if ($medical_test_id != "") {
            $searchVar->sqlSearchVar[] = "mt.medical_test_id = '{$medical_test_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "mt.medical_test_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'mt.medical_test_id');

            $searchVar->sqlSearchVar[] = "mt.category = 'Vaccination'";
            /*if ($group_name != "") {
                $searchVar->sqlSearchVar[] = "mt.group_name = '{$group_name}'";
            }*/

            if ($group_name != ""){
                $searchVar->sqlSearchVar[] = "mt.medical_test_id IN (
                                                SELECT mtg.medical_test_id FROM `medical_test_group` mtg
                                                WHERE mtg.group_name = '{$group_name}'
                                              )";
            }

           /* if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    mt.title  LIKE '{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "mt.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(mt.flag != 1 OR mt.flag IS null)";
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
        $validate->validateData('title', 'Please enter the title');

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
        $fa['category']  = 'Vaccination';
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
        $fa = $fn->addToFieldsArray($fa, 'medical_test_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'fees');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'normal_value');
        $fa = $fn->addToFieldsArray($fa, 'blood_related');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'units');
        $fa = $fn->addToFieldsArray($fa, 'dosage');
        $fa = $fn->addToFieldsArray($fa, 'route');

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
    function getValueByValuelistJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $valuelist_name = $fn->getReqParam('valuelist_name');

        $json  = array();

        if ($valuelist_name == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT v.value
              ,v.value
        FROM valuelist v
        WHERE v.key_text = '{$valuelist_name}'
        ORDER BY v.value
        ";
        $result = $db->sql_query($SQL);
        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['value'], "caption" => $row['value']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getAddNewValuelistFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $valuelist_value = $fn->getPostParam('valuelist_value');
        $valuelist_name  = $fn->getReqParam('valuelist_name');
        $medical_test_id     = $fn->getReqParam('medical_test_id');

        if (!$this->getAddNewValuelistFormValidate($valuelist_name, $valuelist_value)){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['key_text']      = $valuelist_name;
        $fa['value']         = $valuelist_value;
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'valuelist');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();

        $fa = array();
        $valuelist_name = "investigationGroup" ;

        $fa['group_name'] = $valuelist_value;

        $whereCondition = "WHERE medical_test_id = {$medical_test_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "medical_test", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML('', $valuelist_value);
    }

    /**
     *
     */
    function getAddNewValuelistFormValidate($valuelist_name, $valuelist_value) {
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('valuelist_value', 'Please enter value');

        if ($valuelist_value) {
            $sql = "
            SELECT value FROM valuelist
            WHERE key_text = '{$valuelist_name}'
              AND value = '{$valuelist_value}'
            ";
            $result  = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);
            if ($numRows > 0) {
                $validate->errorArray['valuelist_value']['name'] = "valuelist_value";
                $validate->errorArray['valuelist_value']['msg']  = "Entered value already exists";
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
    function getMedicalParametersFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getMedicalParametersValidate()){
            return $validate->getErrorMessageXML();
        }

        $medical_test_id = $fn->getReqParam('medical_test_id');
        $title           = $fn->getPostParam('title');
        $normal_value    = $fn->getPostParam('normal_value');
        $units           = $fn->getPostParam('units');

        $fa = array();
        $fa['title']           = $title;
        $fa['normal_value']    = $normal_value;
        $fa['units']           = $units;
        $fa['medical_test_id'] = $medical_test_id;
        $fa['creation_date']   = date("Y-m-d H:i:s");
        $fa['created_by']      = $fn->getSessionParam('userName');

        $insertWeightWiseSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_test_parameter');
        $resultWeightWiseSQL = $db->sql_query($insertWeightWiseSQL);

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getEditMedicalParametersFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getMedicalParametersValidate()){
            return $validate->getErrorMessageXML();
        }

        $title        = $fn->getPostParam('title');
        $normal_value = $fn->getPostParam('normal_value');
        $units        = $fn->getPostParam('units');
        $medical_test_parameter_id = $fn->getPostParam('medical_test_parameter_id');


        $fa1 = array();

        $fa1['title']             = $title;
        $fa1['normal_value']      = $normal_value;
        $fa1['units']             = $units;
        $fa1['modification_date'] = date("Y-m-d H:i:s");
        $fa1['modified_by']       = $fn->getSessionParam('userName');

        $whereConditionWeightWise = "WHERE medical_test_parameter_id = {$medical_test_parameter_id}" ;
        $sqlUpdateWeightWise      = $dbUtil->getUpdateSQLStringFromArray($fa1, "medical_test_parameter", $whereConditionWeightWise);
        $resultUpdateWeightWise   = $db->sql_query($sqlUpdateWeightWise);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getMedicalParametersValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }    
    /**
     *
     */
    function getDeleteMedicalParameters(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $medical_test_id = $fn->getReqParam('medical_test_id');
        $medical_test_parameter_id = $fn->getReqParam('medical_test_parameter_id');

        $SQL ="
               DELETE FROM medical_test_parameter
               WHERE medical_test_parameter_id = {$medical_test_parameter_id}
               ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getMedicalTestGroupValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('group_name', 'Please Select Group');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getMedicalTestGroupSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getMedicalTestGroupValidate()){
            return $validate->getErrorMessageXML();
        }

        $medical_test_id = $fn->getPostParam('medical_test_id');
        $group_name      = $fn->getPostParam('group_name');

        $fa = array();

        $fa['group_name']       = $group_name;
        $fa['medical_test_id']  = $medical_test_id;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $insertMedicalTestGroupSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'medical_test_group');
        $resultMedicalTestGroupSQL = $db->sql_query($insertMedicalTestGroupSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditMedicalTestGroupFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $cpCfg    = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getMedicalTestGroupValidate()){
            return $validate->getErrorMessageXML();
        }

        $medical_test_id       = $fn->getPostParam('medical_test_id');
        $group_name            = $fn->getPostParam('group_name');
        $medical_test_group_id = $fn->getPostParam('medical_test_group_id');

        $fa1 = array();

        $fa1['group_name']        = $group_name;
        $fa1['modification_date'] = date("Y-m-d H:i:s");
        $fa1['modified_by']       = $fn->getSessionParam('userName');
        
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa1['site_id'] = $cpSiteIdSession;
        }

        $whereCondition           = "WHERE medical_test_group_id = {$medical_test_group_id}" ;
        $sqlUpdateMedicalGroup    = $dbUtil->getUpdateSQLStringFromArray($fa1, "medical_test_group", $whereCondition);
        $resultUpdateMedicalGroup = $db->sql_query($sqlUpdateMedicalGroup);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDeleteMedicalTestGroup(){
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $medical_test_id       = $fn->getReqParam('medical_test_id');
        $medical_test_group_id = $fn->getReqParam('medical_test_group_id');

        $SQL ="
        DELETE FROM medical_test_group
        WHERE medical_test_group_id = {$medical_test_group_id}
        ";
        $result = $db->sql_query($SQL);
    }
}
