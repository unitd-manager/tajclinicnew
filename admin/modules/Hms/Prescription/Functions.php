<?
class CPL_Admin_Modules_Hms_Prescription_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_prescription');
        $modObj['tableName'] = 'prescription';
        $modObj['keyField']  = 'prescription_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('apply','save')
           ,'relatedTables' => array('media')
           ,'title'         => 'Diagnosis List'
        ));
    }
    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_prescription', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }


}
