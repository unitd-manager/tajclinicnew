<?
class CPL_Admin_Modules_Payroll_JobInformation_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getContactByCompanyJSON(){
        return $this->model->getContactByCompanyJSON();
    }

    function getSearchEmployeeDetails(){
    	return $this->model->getSearchEmployeeDetails();
    }

}