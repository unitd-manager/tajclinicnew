<?
class CPL_Admin_Modules_Payroll_Training_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getTrainingEmplyoee() {
        return $this->view->getTrainingEmplyoee();
    }

    function getTrainingEmplyoeeFormSubmit() {
        return $this->model->getTrainingEmplyoeeFormSubmit();
    }

    function getTrainingEmplyoeeValidate() {
        return $this->model->getTrainingEmplyoeeValidate();
    }

    function getDeleteTrainingEmplyoee() {
        return $this->model->getDeleteTrainingEmplyoee();
    }
}