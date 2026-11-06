<?
class CPL_Admin_Modules_Tradingsg_Supplier_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getCreateLoginForm() {
        return $this->view->getCreateLoginForm();
    }

    function getCreateLoginFormSubmit() {
        return $this->model->getCreateLoginFormSubmit();
    }

    function getGeneratePurchaseOrderForm() {
        return $this->view->getGeneratePurchaseOrderForm();
    }

    function getGeneratePurchaseOrderFormSubmit() {
        return $this->model->getGeneratePurchaseOrderFormSubmit();
    }

    function getGeneratePurchaseOrderFormValidate() {
        return $this->model->getGeneratePurchaseOrderFormValidate();
    }

    function getPopulatePOAmount() {
        return $this->model->getPopulatePOAmount();
    }

    function getNewSupplier() {
        return $this->view->getNewSupplier();
    }
    function getAddSupplier() {
        return $this->model->getAddSupplier();
    }
    function getSupplierList(){
        return $this->model->getSupplierList();
    }

    function getAddPurchaseOrderDetail() {
        return $this->view->getAddPurchaseOrderDetail();
    }

    function getSupplierPaymentDetail() {
        return $this->view->getSupplierPaymentDetail();
    }

    function getPopulatePOTotalAmount() {
        return $this->model->getPopulatePOTotalAmount();
    }
    
    function getMedTestVisitPortalDetail() {
        return $this->view->getMedTestVisitPortalDetail();
    }

    function getReceiptHistoryForSupplier() {
        return $this->view->getReceiptHistoryForSupplier();
    }
}