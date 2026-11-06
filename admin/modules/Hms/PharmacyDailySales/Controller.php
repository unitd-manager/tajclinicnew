<?
class CPL_Admin_Modules_Hms_PharmacyDailySales_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getCreateCurrentDayPharmacyRecord() {
        return $this->model->getCreateCurrentDayPharmacyRecord();
    }
}