<?
class CPL_Admin_Modules_Tradingin_Inventory_Controller extends CP_Admin_Modules_Tradingin_Inventory_Controller
{
    function getPurchaseOrderDisplay() {
        return $this->view->getPurchaseOrderDisplay();
    }

    function getOrderDisplay() {
        return $this->view->getOrderDisplay();
    }

    function getBatchWiseStockDisplay() {
        return $this->view->getBatchWiseStockDisplay();
    }

    function getStockTransferDisplay() {
        return $this->view->getStockTransferDisplay();
    }

    function getCreateInventoryRecords() {
        return $this->view->getCreateInventoryRecords();
    }

    function getUpdatedAdjustStockHistory(){
        return $this->view->getUpdatedAdjustStockHistory();
    }

    function getBatchWiseAdjustStockUpdate(){
        return $this->model->getBatchWiseAdjustStockUpdate();
    }

    function getCreateUpdateChangedStockRecord(){
        return $this->model->getCreateUpdateChangedStockRecord();
    }

    function getBatchWiseAdjustStockUpdateForMinusStock(){
        return $this->model->getBatchWiseAdjustStockUpdateForMinusStock();
    }

    function getBatchWiseAdjustStockUpdateForStock(){
        return $this->model->getBatchWiseAdjustStockUpdateForStock();
    }

    function getBatchProductUpdateCurrentStock(){
        return $this->model->getBatchProductUpdateCurrentStock();
    }

    function getUpdatePurchaseQtyInBatchwiseStock(){
        return $this->model->getUpdatePurchaseQtyInBatchwiseStock();
    }

    function getUpdateStockInInventoryAndProduct(){
        return $this->model->getUpdateStockInInventoryAndProduct();
    }

    function getUpdateStockInInventoryAndProductLog(){
        return $this->model->getUpdateStockInInventoryAndProductLog();
    }
}