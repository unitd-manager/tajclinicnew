<?
class CPL_Admin_Modules_Hms_Order_Controller extends CP_Admin_Modules_Hms_Order_Controller
{
    /**
     *
     */
    function getUploadToDHL() {
        return $this->model->getUploadToDHL();
    }

    function getGenerateDHLLabel() {
        return $this->model->getGenerateDHLLabel();
    }

    function getAttachDHLLabelToOrder() {
        return $this->model->getAttachDHLLabelToOrder();
    }

    function getPrintDeliveryOrder() {
        return $this->view->getPrintDeliveryOrder();
    }

    function getPrintDeliveryOrderByProductGroup() {
        return $this->view->getPrintDeliveryOrderByProductGroup();
    }

    function getPrintOrderSummary() {
        return $this->view->getPrintOrderSummary();
    }

    function getPrintCaptainCopy() {
        return $this->view->getPrintCaptainCopy();
    }

    function getPrintCaptainCopy1() {
        return $this->view->getPrintCaptainCopy1();
    }

    function getGenerateSalesReturnForm() {
        return $this->view->getGenerateSalesReturnForm();
    }

    function getGenerateSalesReturnFormSubmit() {
        return $this->model->getGenerateSalesReturnFormSubmit();
    }

    function getPrintSalesReturn() {
        return $this->view->getPrintSalesReturn();
    }

    function getGenerateInvoiceForm() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->view->getGenerateInvoiceForm();
    }

    function getGenerateInvoiceFormSubmit() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->model->getGenerateInvoiceFormSubmit();
    }

    function getGenerateInvoiceFormInpatient() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->view->getGenerateInvoiceFormInpatient();
    }

    function getGenerateInvoiceFormInpatientSubmit() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->model->getGenerateInvoiceFormInpatientSubmit();
    }

    function getEditInvoiceFormSubmit() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->model->getEditInvoiceFormSubmit();
    }

    function getGenerateReceiptForm() {
        $modObj = getCPModuleObj('hms_receipt');
        return $modObj->view->getGenerateReceiptForm();
    }

    function getGenerateReceiptFormSubmit() {
        $modObj = getCPModuleObj('hms_receipt');
        return $modObj->model->getGenerateReceiptFormSubmit();
    }

    function getPopulateReceiptAmount() {
        return $this->model->getPopulateReceiptAmount();
    }

    function getPopulateInvoiceAmount() {
        return $this->model->getPopulateInvoiceAmount();
    }

    function getCancelInvoice() {
        return $this->model->getCancelInvoice();
    }

    function getCancelReceipt() {
        return $this->model->getCancelReceipt();
    }

    function getPrintInvoiceRecord() {
        return $this->view->getPrintInvoiceRecord();
    }

    function getPrintDeliveryInvoiceRecord() {
        return $this->view->getPrintDeliveryInvoiceRecord();
    }

    function getPrintInvoiceRecordForPurchaseOrder() {
        return $this->view->getPrintInvoiceRecordForPurchaseOrder();
    }

    function getPrintReceipt() {
        return $this->view->getPrintReceipt();
    }

    function getTransporterInvoiceRecord() {
        return $this->view->getTransporterInvoiceRecord();
    }

    function getExtraInvoiceRecord() {
        return $this->view->getExtraInvoiceRecord();
    }

    function getEditInvoiceForm() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->view->getEditInvoiceForm();
    }

    function getPrintBill() {
        $modObj = getCPModuleObj('tradingsg_pos');
        return $modObj->view->getEditInvoiceForm();
    }

	function getSummaryInOrder () {
        return $this->view->getSummaryInOrder();
	}

    function getInvoiceCodeVatUpdate() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->model->getInvoiceCodeVatUpdate();
    }

    function getGenerateFullInvoice() {
        $modObj = getCPModuleObj('hms_invoice');
        return $modObj->model->getGenerateFullInvoice();
    }

    function getInvoicePortalDisplay() {
        return $this->view->getInvoicePortalDisplay();
    }
    
    function getReceiptPortalDisplay() {
        return $this->view->getReceiptPortalDisplay();
    }

    function getGenerateInvoiceCode() {
        return $this->view->getGenerateInvoiceCode();
    }
    function getGenerateReceiptCode() {
        return $this->view->getGenerateReceiptCode();
    }
    function getUpdateInvoiceCodeNumber() {
        return $this->view->getUpdateInvoiceCodeNumber();
    }
    function getUpdateReceiptCodeNumber() {
        return $this->view->getUpdateReceiptCodeNumber();
    }

    function getPrescriptionRegister() {
        return $this->view->getPrescriptionRegister();
    }

    function getSelectSession() {
        return $this->view->getSelectSession();
    }

    function getCancelBill() {
        return $this->model->getCancelBill();
    }

    function getGenerateGstBillNo() {
        return $this->model->getGenerateGstBillNo();
    }
}