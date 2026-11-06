<?
class CPL_Admin_Modules_Hms_InternalLocation_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL   = "
        SELECT il.*
        FROM `internal_location` il
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 'il';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "il.internal_location_id = {$tv['record_id']}";
        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    il.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the site name');

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
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the site name');

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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn    = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    /**
     *
     */
    function getInternalLocationSQL() {

        $SQL = "
        SELECT il.internal_location_id
              ,il.title
        FROM internal_location il
       	WHERE il.published = 1
        ";

        return $SQL;
    }
}
