<?
class CPL_Admin_Modules_Hms_Attendance_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('hms_attendance');
        $modules->registerModule($modObj, array(
            'hasFlagInList'=> 0
           ,'moduleGroup'   => 'hms'
           ,'actBtnsEdit'   => array('save', 'apply', 'cancel')
           ,'actBtnsList'   => array('export')
        ));
    }

    /**
     *
     */
    function setLocalArrayValues(){
        $tv = Zend_Registry::get('tv');

        array_push($tv['protSiteSpActionExceptions'], 'sendAttendanceReportToPM');
        array_push($tv['protSiteSpActionExceptions'], 'createAttendanceForAbsent');
    }
}