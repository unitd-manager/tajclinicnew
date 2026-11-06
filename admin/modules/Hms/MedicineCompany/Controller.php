<?
class CPL_Admin_Modules_Hms_MedicineCompany_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getProductDisplay() {
        return $this->view->getProductDisplay();
    }

    function getLinkProduct() {
        return $this->view->getLinkProduct();
    }

    function getLinkProductSubmit() {
        return $this->model->getLinkProductSubmit();
    }

    function getDeleteLinkedProduct() {
        return $this->model->getDeleteLinkedProduct();
    }

    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getUpdateOfferMedicine(){
        return $this->model->getUpdateOfferMedicine();
    }
}