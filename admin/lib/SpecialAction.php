<?
class CPL_Admin_Lib_SpecialAction extends CP_Admin_Lib_SpecialAction {

    //==================================================================//
    function getPublishEnableRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $module       = $fn->getPostParam('room');
        $currentValue = $fn->getPostParam('currentValue');
        $uploadTo     = $fn->getPostParam('uploadTo', 'live');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        /* if newValue = 0 it means the record has to be un-published
         if newValue = 1 it means the record has to be published
        */


        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE `{$tableName}`
        SET enable_gst = {$newValue}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result = $db->sql_query($updateSQL);

        $text = $listObj->getListEnablePublishedImageIcon($module, $record_id, $newValue);

        return $text;
    }

    function getDeleteRecordByID($module = '', $record_id = '') {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $linksArray = Zend_Registry::get('linksArray');

        if ($module == ''){ // if called from url
            if(!$fn->isValidCSRFToken()){ //check for valid CSRF token
                $fn->setCSRFToken();
                $arr = array(
                         'message' => 'Invalid Access!'
                        ,'refresh' => true
                    );
                return json_encode($arr);
            }

            $record_id = $fn->getPostParam('record_id');
            $module    = $fn->getPostParam('room');
        }

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }
        $fn->setCSRFToken();
        $fnMod = includeCPClass('ModuleFns', $module);
        $funcName = "beforeDeleteHandler";
        if (method_exists($fnMod, $funcName)) {
            $returnedVal = $fnMod->$funcName($record_id);
            if (is_array($returnedVal)){
                if ($returnedVal['status'] == 'error'){
                    return json_encode($returnedVal);
                }
            }
        }

        $tableName          = $modulesArr[$module]['tableName'];
        $keyFieldName       = $modulesArr[$module]['keyField'];
        $relatedTablesArray = $modulesArr[$module]['relatedTables'];

        foreach ($relatedTablesArray as $key => $value) {
            if ($value == 'media') {
                $media = getCPPluginObj('common_media');
                $media->deleteMediaRecord($module, $record_id);
            } else {
                $SQL = "DELETE FROM `{$value}` WHERE {$keyFieldName}= {$record_id}";
                $result = $db->sql_query($SQL);
            }
        }

        //-----------------------------------------------------//
        $SQL = "DELETE FROM `{$tableName}` WHERE {$keyFieldName}= {$record_id}";
        $result = $db->sql_query($SQL);

        if ($module == 'hms_product'){
            $SQL1 = "DELETE FROM `inventory` WHERE product_id= {$record_id}";
            $result1 = $db->sql_query($SQL1);            
        }

        $funcName   = "afterDeleteHandler";
        if (method_exists($fnMod, $funcName)) {
            $fnMod->$funcName($record_id);
        }

        $arr = array('status' => 'success');
        return json_encode($arr);
    }
}