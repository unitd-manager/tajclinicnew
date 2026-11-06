<?
class CPL_Admin_Modules_Hms_PatientVisit_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_patientVisit');
        $modObj['tableName'] = 'patient_visit';
        $modObj['keyField']  = 'patient_visit_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           //,'actBtnsSearchlist' => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'title'         => 'Patient Visit'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_patientVisit', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('hms_patientVisit', 'attachmentBloodTest', 'attachmentBloodTest');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('hms_patientVisit', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('hms_patientVisit', 'dneEarpicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('hms_patientVisit', 'dneNosepicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
