<?
class CPL_Admin_Modules_Tradingsg_Dashboard_Functions
{
    function setModuleArray($modules){
        $tv = Zend_Registry::get('tv');
        $modObj = $modules->getModuleObj('tradingsg_dashboard');

        $modules->registerModule($modObj, array(
            'hasDb'       => false
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array()
           ,'actBtnsEdit' => array()
           ,'hasOnlyListView' => true
           ,'title'         => 'Dashboard'
        ));
    }
}
