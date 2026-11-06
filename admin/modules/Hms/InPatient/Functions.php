<?
class CPL_Admin_Modules_Hms_InPatient_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_inPatient');
        $modObj['tableName'] = 'in_patient';
        $modObj['keyField']  = 'in_patient_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('apply','save')
           ,'relatedTables' => array('media')
           ,'title'         => 'In Patient'
        ));
    }
    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_inPatient', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }


}
