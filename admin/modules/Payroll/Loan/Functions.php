<?
class CPL_Admin_Modules_Payroll_Loan_Functions {

    /**
     *
     */

    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_loan');
        $modObj['tableName'] = 'loan';
        $modObj['keyField']  = 'loan_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('payroll_loan', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}