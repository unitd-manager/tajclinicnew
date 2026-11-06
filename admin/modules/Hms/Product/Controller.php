<?
class CPL_Admin_Modules_Hms_Product_Controller extends CP_Admin_Modules_Hms_Product_Controller
{
    function getGenerateBulkVouchers() {
        return $this->model->getGenerateBulkVouchers();
    }

    function getGenerateVoucherFormSubmit() {
        return $this->model->getGenerateVoucherFormSubmit();
    }
    
    function getPrintVoucher() {
        return $this->model->getPrintVoucher();
    }

    function getCategoryJsonByProductGroupId() {
        return $this->model->getCategoryJsonByProductGroupId();
    }

    function getQuickAdd(){
        return $this->view->getQuickAdd();
    }

    function getQuickAddSubmit(){
        return $this->model->getQuickAddSubmit();
    }

    function getAddProductRecord(){
        return $this->view->getAddProductRecord();
    }

    function getAddProductPrice(){
        return $this->view->getAddProductPrice();
    }

    function getAddProductPriceSubmit(){
        return $this->model->getAddProductPriceSubmit();
    }

    function getProductPriceDetailList(){
        return $this->view->getProductPriceDetailList();
    }

    function getProductPriceDetail(){
        return $this->view->getProductPriceDetail();
    }

    function getEditProductPrice(){
        return $this->view->getEditProductPrice();
    }
    
    function getEditProductPriceSubmit(){
        return $this->model->getEditProductPriceSubmit();
    }
    
    function getProductPriceHistory(){
        return $this->view->getProductPriceHistory();
    }

    function getDosageAgeWise() {
        return $this->view->getDosageAgeWise();
    }

    function getDosageAgeWiseValidate() {
        return $this->model->getDosageAgeWiseValidate();
    }

    function getDosageAgeWiseFormSubmit() {
        return $this->model->getDosageAgeWiseFormSubmit();
    }

    function getAddDosageAgeWise() {
        return $this->view->getAddDosageAgeWise();
    }

    function getEditDosageAgeWise() {
        return $this->view->getEditDosageAgeWise();
    }

    function getEditDosageAgeWiseFormSubmit() {
        return $this->model->getEditDosageAgeWiseFormSubmit();
    }

    function getDeleteDosageAgeWise() {
        return $this->model->getDeleteDosageAgeWise();
    }

    function getDosageWeightWise() {
        return $this->view->getDosageWeightWise();
    }

    function getDosageWeightWiseValidate() {
        return $this->model->getDosageWeightWiseValidate();
    }

    function getDosageWeightWiseFormSubmit() {
        return $this->model->getDosageWeightWiseFormSubmit();
    }

    function getAddDosageWeightWise() {
        return $this->view->getAddDosageWeightWise();
    }

    function getEditDosageWeightWise() {
        return $this->view->getEditDosageWeightWise();
    }

    function getEditDosageWeightWiseFormSubmit() {
        return $this->model->getEditDosageWeightWiseFormSubmit();
    }

    function getDeleteDosageWeightWise() {
        return $this->model->getDeleteDosageWeightWise();
    }

    function getProductDetailsUpdateLink() {
        return $this->model->getProductDetailsUpdateLink();
    }

    function getAddBranchMedicine() {
        return $this->view->getAddBranchMedicine();
    }

    function getBranchMedicineValidate() {
        return $this->model->getBranchMedicineValidate();
    }

    function getAddBranchMedicineSubmit() {
        return $this->model->getAddBranchMedicineSubmit();
    }

    function getBranchMedicine() {
        return $this->view->getBranchMedicine();
    }

    function getEditBranchMedicine() {
        return $this->view->getEditBranchMedicine();
    }

    function getEditBranchMedicineSubmit() {
        return $this->model->getEditBranchMedicineSubmit();
    }

    function getDeleteBranchMedicine() {
        return $this->model->getDeleteBranchMedicine();
    }
    function getLinkMedicineToSites() {
        return $this->view->getLinkMedicineToSites();
    }
    
}