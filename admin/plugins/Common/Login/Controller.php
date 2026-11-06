<?
class CPL_Admin_Plugins_Common_Login_Controller extends CP_Admin_Plugins_Common_Login_Controller
{
    /**
     *
     */
    function getLoginForm(){
        return $this->view->getLoginForm();
    }

    /**
     *
     */
    function getLoginSubmit(){
        return $this->model->getLoginSubmit();
    }

    /**
     *
     */
    function getChooseUsergroupSubmit(){
        return $this->model->getChooseUsergroupSubmit();
    }

    /**
     *
     */
    function loginWithCookie(){
        return $this->model->loginWithCookie();
    }

    /**
     *
     */
    function getAutoLoginRandomID(){
        return $this->model->getAutoLoginRandomID();
    }

    /**
     *
     */
    function getAutoLoginUserByRandomID(){
        return $this->model->getAutoLoginUserByRandomID();
    }
    
    /**
     *
     */
    function getLogout() {
        return $this->model->getLogout();
    }

    /**
     *
     */
    function getStaffTimeOutUpdate() {
        return $this->model->getStaffTimeOutUpdate();
    }

    /**
     *
     */
    function getStaffTimeInUpdate() {
        return $this->model->getStaffTimeInUpdate();
    }

    /**
     *
     */
    function getStaffTimeOutUpdateNight() {
        return $this->model->getStaffTimeOutUpdateNight();
    }

    /**
     *
     */
    function getStaffTimeInUpdateNight() {
        return $this->model->getStaffTimeInUpdateNight();
    }

    /**
     *
     */
    function getStaffTimeInUpdateDay() {
        return $this->model->getStaffTimeInUpdateDay();
    }

    /**
     *
     */
    function getStaffTimeOutUpdateDay() {
        return $this->model->getStaffTimeOutUpdateDay();
    }

    /**
     *
     */
    function getStaffTimeInUpdateEvening() {
        return $this->model->getStaffTimeInUpdateEvening();
    }

    /**
     *
     */
    function getStaffTimeOutUpdateEvening() {
        return $this->model->getStaffTimeOutUpdateEvening();
    }

}