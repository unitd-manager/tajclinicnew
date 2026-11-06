<?
class CPL_Admin_Modules_Payroll_Loan_Model extends CP_Common_Lib_ModuleModelAbstract
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
        FROM `loan` l
        LEFT JOIN (employee e) ON (e.employee_id = l.employee_id)
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

        $status       = $fn->getReqParam('status');
        $loan_id   = $fn->getReqParam('loan_id');

        if ($loan_id != "") {
            $searchVar->sqlSearchVar[] = "l.loan_id = '{$loan_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.loan_id = '{$tv['record_id']}'";
        } else {
            //$fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.salary_id');

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "l.status = '{$status}'";
            }

           /* if ($category != "") {
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
        $validate->validateData('employee_id', 'Please enter the employee');

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
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'due_date');
        $fa = $fn->addToFieldsArray($fa, 'no_of_months');
        $fa = $fn->addToFieldsArray($fa, 'loan_closing_date');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');

        return $fa;
    }

    /**
     *
     */


}
