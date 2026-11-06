<?
class CPL_Admin_Modules_Hms_InternalLocation_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
    	
        $modObj = $modules->getModuleObj('hms_internalLocation');
        $modObj['tableName'] = 'internal_location';
        $modObj['keyField']  = 'internal_location_id';
        $modules->registerModule($modObj, array(
            'title' => 'Internal Location'
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('apply','save')
        ));
    }


    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

    }
    
    /**
     *
     */
    function setLinksArrayForSiteLink($linksArrObj, $module){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');    	
    }
}