<?
class CPL_Admin_Themes_Angle_Controller extends CP_Admin_Themes_Angle_Controller
{


	/**
     *
     */
	function getPatientQueueNo() {
        return $this->view->getPatientQueueNo();
    }


	/**
     *
     */
	function getUpdateQueueNoNext() {
        return $this->view->getUpdateQueueNoNext();
    }
    /**
     *
     */
    function getSearchMedicines() {
        return $this->view->getSearchMedicines();
    }
    /**
     *
     */
    function getSearchMedicinePortal() {
        return $this->view->getSearchMedicinePortal();
    }

}