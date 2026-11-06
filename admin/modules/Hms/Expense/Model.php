<?
class CPL_Admin_Modules_Hms_Expense_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');

        $SQL ="
        SELECT e.*
        FROM expense e
        ";

        return $SQL;

    }

    /**
     *
     */

    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'e';

        $expense_id     = $fn->getReqParam('expense_id');
        $date1          = $fn->getReqParam('date_1');
        $date2          = $fn->getReqParam('date_2');
        $group          = $fn->getReqParam('group');
        $sub_group      = $fn->getReqParam('sub_group');
        $current_month  = $fn->getReqParam('current_month');
        $site_id        = $fn->getReqParam('site_id');
        $source         = $fn->getReqParam('source');
        $status         = $fn->getReqParam('status');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.expense_id = {$tv['record_id']}";
        }

        if ($expense_id != "") {
            $searchVar->sqlSearchVar[] = "e.expense_id = '{$expense_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.expense_id = '{$tv['record_id']}'";
        } else {

            if ($date1 != "" && $date2 != "") {
                $searchVar->sqlSearchVar[] = "(e.date BETWEEN '{$date1}' AND '{$date2}')";
            }

            if ($current_month != "") {
                $search_form = date('Y-'.$current_month.'-01');
                $search_to   = date("Y-m-t", strtotime($search_form));
                $searchVar->sqlSearchVar[] = "(e.date BETWEEN '{$search_form}' AND '{$search_to}')";
            }else {
                $search_form = date('Y-m-01');
                $search_to   = date('Y-m-t');
                $searchVar->sqlSearchVar[] = "(e.date BETWEEN '{$search_form}' AND '{$search_to}')";
            }

            if ($group != "") {
                $searchVar->sqlSearchVar[] = "e.group = '{$group}'";
            }

            if ($sub_group != "") {
                $searchVar->sqlSearchVar[] = "e.sub_group = '{$sub_group}'";
            }

            if ($source != "") {
                $searchVar->sqlSearchVar[] = "e.source = '{$source}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "e.status = '{$status}'";
            }

            if ($site_id != "") {
                $searchVar->sqlSearchVar[] = "e.site_id = '{$site_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    e.description LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "e.date DESC";
        }

    }

    /**
    *
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('date', 'Please enter date');
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $current_date = date('Y-m-d');

        $fa = $this->getFields();

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('group', 'Please select group');
        $validate->validateData('sub_group', 'Please select sub group');
        $validate->validateData('source', 'Please enter the source');

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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'group');
        $fa = $fn->addToFieldsArray($fa, 'sub_group');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
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
        $expense_id     = $fn->getReqParam('expense_id');

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
        $valuelist_name = "Group";

        $fa['group'] = $valuelist_value;

        $whereCondition = "WHERE expense_id = {$expense_id}";
        $sqlUpdate      = $dbUtil->getUpdateSQLStringFromArray($fa, "expense", $whereCondition);
        $resultUpdate   = $db->sql_query($sqlUpdate);

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
    function getSubgroupByGroupJSON(){
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $rows = "";

        $expense_group_id = $fn->getReqParam('expense_group_id');
        $group            = $fn->getReqParam('group');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $json  = array();
        
        if ($expense_group_id == ""){
            return json_encode($json);
        }

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT expense_sub_group_id
              ,title
        FROM expense_sub_group 
        WHERE expense_group_id = '{$expense_group_id}'
        {$appendSql}
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['expense_sub_group_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

}