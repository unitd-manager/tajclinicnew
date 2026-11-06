<?
class CPL_Admin_Modules_Hms_Vaccination_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_vaccination');
        $modObj['tableName'] = 'medical_test';
        $modObj['keyField']  = 'medical_test_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Vaccination'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_vaccination', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    
}
