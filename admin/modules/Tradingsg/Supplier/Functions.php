<?
class CPL_Admin_Modules_Tradingsg_Supplier_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_supplier');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new','import')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'titleField'    => 'company_name'
           ,'title'         => 'Supplier'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('tradingsg_supplier', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
