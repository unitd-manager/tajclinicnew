<?
class CPL_Admin_Modules_Tradingsg_Pos_Controller extends CP_Admin_Modules_Tradingsg_Pos_Controller
{
    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getSearchCustomerDetails() {
        return $this->model->getSearchCustomerDetails();
    }

    function getDisplayCustomerDetails() {
        return $this->model->getDisplayCustomerDetails();
    }

    function getUpdateOrderLineItems() {
        return $this->model->getUpdateOrderLineItems();
    }

    function getOrderItems(){
        return $this->view->getOrderItems();
    }

    function getUpdateQtyOrderItem(){
        return $this->model->getUpdateQtyOrderItem();
    }

    function getCreateNewOrder(){
        return $this->model->getCreateNewOrder();
    }

    function getSaleByName(){
        return $this->view->getSaleByName();
    }

    function getSaleByNameSubmit(){
        return $this->model->getSaleByNameSubmit();
    }

    function getCancelOrder(){
        return $this->model->getCancelOrder();
    }

    function getGenerateBill(){
        return $this->view->getGenerateBill();
    }

    function getPrintBill(){
        return $this->view->getPrintBill();
    }

    function getPrintBillForPrinter(){
        return $this->view->getPrintBillForPrinter();
    }

    function getDeleteItem(){
        return $this->model->getDeleteItem();
    }

    function getCloseOrder(){
        return $this->model->getCloseOrder();
    }

    function getUpdateDiscountOrder(){
        return $this->model->getUpdateDiscountOrder();
    }

    function getUpdateBalance(){
        return $this->model->getUpdateBalance();
    }

    function getProductPrice(){
        return $this->view->getProductPrice();
    }

    function getProductPriceDisplay(){
        return $this->view->getProductPriceDisplay();
    }

    function getUpdatediscountType(){
        return $this->model->getUpdatediscountType();
    }

    function getUpdateDiscountPercentOrderItem(){
        return $this->model->getUpdateDiscountPercentOrderItem();
    }

    function getUpdatePiecesOrderItem(){
        return $this->model->getUpdatePiecesOrderItem();
    }

    function getCheckPendingOrderDetails(){
        return $this->view->getCheckPendingOrderDetails();
    }

    function getOrderStatusToPending(){
        return $this->view->getOrderStatusToPending();
    }

    function getInsertOldOrder(){
        return $this->view->getInsertOldOrder();
    }

    function getApplyDiscount(){
        return $this->view->getApplyDiscount();
    }

    function getApplyDiscountSubmit(){
        return $this->model->getApplyDiscountSubmit();
    }

    function getPrintbillcondition(){
        return $this->view->getPrintbillcondition();
    }

    function getPrintbillconditionForPrinter(){
        return $this->view->getPrintbillconditionForPrinter();
    }

    function getPrintBillPdf(){
        return $this->view->getPrintBillPdf();
    }

    function getAddClient(){
        return $this->view->getAddClient();
    }

    function getAddClientSubmit(){
        return $this->model->getAddClientSubmit();
    }

    function getRemoveClient(){
        return $this->model->getRemoveClient();
    }

    function getPrintInvoiceRecord(){
        return $this->view->getPrintInvoiceRecord();
    }

    function getCreditCard(){
        return $this->view->getCreditCard();
    }

    function getCreditCardSubmit(){
        return $this->model->getCreditCardSubmit();
    }

    function getModeOfPaymentUpdate(){
        return $this->model->getModeOfPaymentUpdate();
    }

    function getGenerateKeyString(){
        return $this->view->getGenerateKeyString();
    }

    function getCancelOrderCurrent(){
        return $this->model->getCancelOrderCurrent();
    }

    function getCancelOrderNotes(){
        return $this->view->getCancelOrderNotes();
    }

    function getCancelOrderNotesSubmit(){
        return $this->model->getCancelOrderNotesSubmit();
    }

    function getGSTStatusUpdate(){
        return $this->model->getGSTStatusUpdate();
    }

    function getLoyaltyUpdate(){
        return $this->model->getLoyaltyUpdate();
    }
    
    function getApplyShippingCharges(){
        return $this->view->getApplyShippingCharges();
    }

    function getApplyShippingChargesSubmit(){
        return $this->model->getApplyShippingChargesSubmit();
    }

    function getRemoveShippingChargeOrder(){
        return $this->model->getRemoveShippingChargeOrder();
    }

    function getUpdateOrderDate(){
        return $this->model->getUpdateOrderDate();
    }

    function getUpdateWeightOrderItem(){
        return $this->model->getUpdateWeightOrderItem();
    }

    function getUpdateRefNoOrderItem(){
        return $this->model->getUpdateRefNoOrderItem();
    }

    function getAddDefaultDiscountType(){
        return $this->view->getAddDefaultDiscountType();
    }

    function getAddDefaultDiscountTypeSubmit(){
        return $this->model->getAddDefaultDiscountTypeSubmit();
    }

    function getProfitAmount(){
        return $this->view->getProfitAmount();
    }

    function getUpdateUnitPriceOrderItem(){
        return $this->model->getUpdateUnitPriceOrderItem();
    }

    function getBatchProductSelect(){
        return $this->view->getBatchProductSelect();
    }

    function getAddBatchProductForPos(){
        return $this->model->getAddBatchProductForPos();
    }

    function getSearchVisitDetails(){
        return $this->model->getSearchVisitDetails();
    }

    function getUpdateOrderLineItemsVisit(){
        return $this->model->getUpdateOrderLineItemsVisit();
    }

    function getDeleteAllOrderItem(){
        return $this->model->getDeleteAllOrderItem();
    }

    function getBatchProductCountCheck(){
        return $this->model->getBatchProductCountCheck();
    }

    function getUpdateNotAddInStock(){
        return $this->model->getUpdateNotAddInStock();
    }

    function getUpdateProductPrice(){
        return $this->model->getUpdateProductPrice();
    }

    function getUpdatePatientNameOnOrder(){
        return $this->model->getUpdatePatientNameOnOrder();
    }

    function getUpdateMobileInOrder(){
        return $this->model->getUpdateMobileInOrder();
    }

    function getUpdateCategoryInOrder(){
        return $this->model->getUpdateCategoryInOrder();
    }

    function getUpdatePOSBillNumberOnOrder(){
        return $this->model->getUpdatePOSBillNumberOnOrder();
    }

    function getUpdateDrNameOnOrder(){
        return $this->model->getUpdateDrNameOnOrder();
    }

    function getCloseOrderSession(){
        return $this->model->getCloseOrderSession();
    }

    function getUpdateDiscountPercentage(){
        return $this->model->getUpdateDiscountPercentage();
    }

    function getStockCheckForProductOrder(){
        return $this->model->getStockCheckForProductOrder();
    }
}