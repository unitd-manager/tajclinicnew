<?
class CPL_Admin_Modules_Hms_Attendance_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSendAttendanceReportToPM(){
        return $this->view->getSendAttendanceReportToPM();
    }    

    function getCreateAttendanceForAbsent(){
        return $this->view->getCreateAttendanceForAbsent();
    }    

    function getDeleteDuplicateAttendanceForAbsent(){
        return $this->view->getDeleteDuplicateAttendanceForAbsent();
    }

    function getDoubleShiftTimingUpdate(){
    	return $this->view->getDoubleShiftTimingUpdate();
    }
}