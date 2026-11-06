<?
class CPL_Admin_Modules_Hms_MedicalTest_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

    function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getMedicalParameters() {
        return $this->view->getMedicalParameters();
    }

    function getMedicalParametersFormSubmit() {
        return $this->model->getMedicalParametersFormSubmit();
    }

    function getEditMedicalParameters() {
        return $this->view->getEditMedicalParameters();
    }

    function getEditMedicalParametersFormSubmit() {
        return $this->model->getEditMedicalParametersFormSubmit();
    }

    function getDeleteMedicalParameters() {
        return $this->model->getDeleteMedicalParameters();
    }

    function getMedicalTestParameter() {
        return $this->view->getMedicalTestParameter();
    }
}