<?
class CPL_Admin_Modules_Tradingsg_StockTransfer_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_stockTransfer');
        $modules->registerModule($modObj, array(
            'tableName' => 'stock_transfer'
            ,'hasFlagInList' => 0
           ,'keyField'         => 'stock_transfer_id'
           ,'actBtnsList'   => array('new')
           ,'actBtnsEdit'   => array('save','apply')
           ,'title'     => 'Stock Transfer'
        ));
    }
}