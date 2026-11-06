<?
class CPL_Admin_Modules_Hms_Complain_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_complain');
        $modObj['tableName'] = 'complain';
        $modObj['keyField']  = 'complain_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('apply','save')
           ,'relatedTables' => array('media')
           ,'title'         => 'Complain List'
           ,'hasFlagInList' => 0
        ));
    }
    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_complain', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }


}
