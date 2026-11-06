<?
class CPL_Admin_Modules_Payroll_Leave_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT l.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,j.designation
        FROM `leave` l
        LEFT JOIN (employee e) ON (l.employee_id = e.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = l.employee_id)
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
        $searchVar->mainTableAlias = 'l';

        $leave_id    = $fn->getReqParam('leave_id');
        $employee_id = $fn->getReqParam('employee_id');
        $status      = $fn->getReqParam('status');

        if ($leave_id != "") {
            $searchVar->sqlSearchVar[] = "l.leave_id = '{$leave_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.leave_id = '{$tv['record_id']}'";
        } else {

            if ($employee_id != "") {
                $searchVar->sqlSearchVar[] = "l.employee_id = '{$employee_id}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "l.status = '{$status}'";
            }

            //$fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.salary_id');
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'l.leave_id');

          /*  if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    l.reason  LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "l.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(l.flag != 1 OR l.flag IS null)";
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
        $validate->validateData('employee_id', 'Please select employee');

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
        $fa['status'] = 'Applied';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $employee_id = $fn->getReqParam('emp_id');
        $from_date   = $fn->getPostParam('from_date');
        $to_date     = $fn->getPostParam('to_date');
        if($employee_id != ''){
            $SQL="
            SELECT * FROM `leave` 
            WHERE employee_id = {$employee_id}
            AND (from_date BETWEEN {$from_date} AND {$from_date})
            AND (to_date BETWEEN {$to_date} AND {$to_date});
            ";

            $result   = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if($numRows > 0){
                $validate->errorArray['from_date']['name'] = "from_date";
                $validate->errorArray['from_date']['msg']  = "Please note that leave is applied for mentioned date range";
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
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'designation');
        $fa = $fn->addToFieldsArray($fa, 'from_date');
        $fa = $fn->addToFieldsArray($fa, 'to_date');
        $fa = $fn->addToFieldsArray($fa, 'leave_type');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'reason');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'leave_id');
        $fa = $fn->addToFieldsArray($fa, 'no_of_days');

        return $fa;
    }

    /**
     *
     */



}
