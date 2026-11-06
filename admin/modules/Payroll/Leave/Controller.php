<?
class CPL_Admin_Modules_Payroll_Leave_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getLeave() {
        return $this->view->getLeave();
    }

    function getLeaveFormSubmit() {
        return $this->model->getLeaveFormSubmit();
    }

    function getLeaveValidate() {
        return $this->model->getLeaveValidate();
    }

    function getDeleteLeave() {
        return $this->model->getDeleteLeave();
    }

}