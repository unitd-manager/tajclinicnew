<?
class CPL_Admin_Modules_Hms_Prescription_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	function getPrescribeMedicine() {
        return $this->view->getPrescribeMedicine();
    }

    function getAddPrescribeMedicineDetail() {
        return $this->view->getAddPrescribeMedicineDetail();
    }
    
    function getAddPrescribeMedicine() {
        return $this->view->getAddPrescribeMedicine();
    }

    function getPrescribeMedicineFormSubmit() {
        return $this->model->getPrescribeMedicineFormSubmit();
    }

    function getPrescribeMedicineValidate() {
        return $this->model->getPrescribeMedicineValidate();
    }

    function getEditPrescribeMedicine() {
        return $this->view->getEditPrescribeMedicine();
    }

	function getEditPrescribeMedicineFormSubmit() {
        return $this->model->getEditPrescribeMedicineFormSubmit();
    }

    function getDeletePrescribeMedicine() {
        return $this->model->getDeletePrescribeMedicine();
    }

    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

}