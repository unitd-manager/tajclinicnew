<?
class CPL_Admin_Modules_Hms_Expense_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

    function getSubgroupByGroupJSON(){
        return $this->model->getSubgroupByGroupJSON();
    }
}