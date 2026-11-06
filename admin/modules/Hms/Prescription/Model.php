<?
class CPL_Admin_Modules_Hms_Prescription_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT p.*
        FROM prescription p
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

        $prescription_id   = $fn->getReqParam('prescription_id');

        if ($prescription_id != "") {
            $searchVar->sqlSearchVar[] = "p.prescription_id = '{$prescription_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.prescription_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.prescription_id');


            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.disease_name  LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            /*if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }*/

            $searchVar->sortOrder = "p.disease_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('disease_name', 'Please enter name');

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
        $fa = $fn->addToFieldsArray($fa, 'disease_name');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');

        return $fa;
    }
    /**
     *
     */
    function getPrescribeMedicineFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getPrescribeMedicineValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id      = $fn->getReqParam('product_id');
        $prescription_id = $fn->getPostParam('prescription_id');
        $medicine_name   = $fn->getPostParam('medicine_name');
        $dosage          = $fn->getPostParam('dosage');
        $instruction     = $fn->getPostParam('instruction');
        $days            = $fn->getPostParam('days');

        if($product_id == ""){
            $faPro = array();

            $faPro['title']         = $medicine_name;
            $faPro['dosage']        = $dosage;
            $faPro['creation_date'] = date("Y-m-d H:i:s");
            $faPro['created_by']    = $fn->getSessionParam('userName');

            $product_id = $fn->addRecord($faPro, 'product');
        }

        $fa = array();

        $fa['medicine_name']   = $medicine_name;
        $fa['dosage']          = $dosage;
        $fa['instruction']     = $instruction;
        $fa['days']            = $days;
        $fa['prescription_id'] = $prescription_id;
        $fa['product_id']      = $product_id;
        $fa['creation_date']   = date("Y-m-d H:i:s");
        $fa['created_by']      = $fn->getSessionParam('userName');

        $insertPrescriptionSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'prescribe_medicine');
        $resultPrescriptionSQL = $db->sql_query($insertPrescriptionSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPrescribeMedicineFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getPrescribeMedicineValidate()){
            return $validate->getErrorMessageXML();
        }

        $prescription_id       = $fn->getPostParam('prescription_id');
        $medicine_name         = $fn->getPostParam('medicine_name');
        $dosage                = $fn->getPostParam('dosage');
        $instruction           = $fn->getPostParam('instruction');
        $days                  = $fn->getPostParam('days');
        $prescribe_medicine_id = $fn->getPostParam('prescribe_medicine_id');
        $product_id            = $fn->getReqParam('product_id');

        if($product_id == ""){
            $faPro = array();

            $faPro['title']         = $medicine_name;
            $faPro['dosage']        = $dosage;
            $faPro['creation_date'] = date("Y-m-d H:i:s");
            $faPro['created_by']    = $fn->getSessionParam('userName');

            $product_id = $fn->addRecord($faPro, 'product');
        }


        $fa1 = array();

        $fa1['medicine_name']          = $medicine_name;
        $fa1['dosage']                 = $dosage;
        $fa1['instruction']            = $instruction;
        $fa1['days']                   = $days;
        $fa1['prescribe_medicine_id']  = $prescribe_medicine_id;
        $fa1['product_id']             = $product_id;
        $fa1['modification_date']      = date("Y-m-d H:i:s");
        $fa1['modified_by']            = $fn->getSessionParam('userName');

        $whereConditionPrescription = "WHERE prescribe_medicine_id = {$prescribe_medicine_id}" ;
        $sqlUpdatePrescription      = $dbUtil->getUpdateSQLStringFromArray($fa1, "prescribe_medicine", $whereConditionPrescription);
        $resultUpdatePrescription   = $db->sql_query($sqlUpdatePrescription);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPrescribeMedicineValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('medicine_name', 'Please enter Medicine Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getDeletePrescribeMedicine(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $prescription_id = $fn->getReqParam('prescription_id');
        $prescribe_medicine_id = $fn->getReqParam('prescribe_medicine_id');

        $SQL ="
               DELETE FROM prescribe_medicine
               WHERE prescribe_medicine_id = {$prescribe_medicine_id}
               ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title) AS label
        FROM product p
        WHERE (p.title LIKE '%{$productTitle}%')
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }


  }
