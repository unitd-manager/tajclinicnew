<?
class CPL_Admin_Modules_Hms_Complain_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT c.*
        FROM complain c
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
        $searchVar->mainTableAlias = 'c';

        $complain_id   = $fn->getReqParam('complain_id');

        if ($complain_id != "") {
            $searchVar->sqlSearchVar[] = "c.complain_id = '{$complain_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.complain_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.complain_id');


            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title  LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            /*if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }*/

            $searchVar->sortOrder = "c.title";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the complain title');

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
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'complain_group');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');

        return $fa;
    }
}
