<?
class CPL_Admin_Modules_Common_Dashboard_Controller extends CP_Admin_Modules_Common_Dashboard_Controller
{
	function getPatientDoctorWise(){
        return $this->view->getPatientDoctorWise();
    }

    function getPatientVisitSiteWise(){
        return $this->view->getPatientVisitSiteWise();
    }

    function getLabReportSummary(){
        return $this->view->getLabReportSummary();
    }

    function getAttendanceReportSummary(){
        return $this->view->getAttendanceReportSummary();
    }

    function getPatientVisitDisplay(){
        return $this->view->getPatientVisitDisplay();
    }
}