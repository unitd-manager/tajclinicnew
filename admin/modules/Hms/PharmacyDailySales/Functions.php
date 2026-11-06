<?
class CPL_Admin_Modules_Hms_PharmacyDailySales_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_pharmacyDailySales');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'tableName'     => 'pharma_daily_sales'
           ,'keyField'      => 'pharma_daily_sales_id'
           ,'title'         => 'Pharmacy Daily Sales'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_pharmacyDailySales', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    
}
