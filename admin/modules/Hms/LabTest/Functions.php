<?
class CPL_Admin_Modules_Hms_LabTest_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_labTest');
        $modObj['tableName'] = 'lab_test';
        $modObj['keyField']  = 'lab_test_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           //,'actBtnsSearchlist' => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'title'         => 'Lab Test(Self)'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_labTest', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('hms_labTest', 'attachmentBloodTest', 'attachmentBloodTest');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
