<?
class CPL_Admin_Modules_Hms_MedicineCompany_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_medicineCompany');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'tableName'     => 'medicine_company'
           ,'keyField'      => 'medicine_company_id'
           ,'titleField'    => 'medicine_company_name'
           ,'title'         => 'MFR Company'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_medicineCompany', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('hms_medicineCompany', 'hms_contactLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'contact'
            ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
            ,'showLinkPanelInNew'    => 0
            ,'showLinkPanelInEdit'   => 1
            ,'linkingType'           => 'portal'
            ,'hasPortalEdit'         => 1
            ,'hasPortalDelete'       => 1
            ,'portalDialogWidth'     => 700
            ,'portalDialogHeight'    => 500
            ,'anchorFieldsArr'       => array(
                 'first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id')
                ,'last_name' => $inst->getLinkAnchorObj('last_name', 'contact_id'))
            ,'fieldlabel' => array(
                 'Name'
                ,'Email'
                ,'Phone (Direct)'
                ,'Mobile'
                ,'Position'
                ,'Dept.'
            )
        ));

    }
}
