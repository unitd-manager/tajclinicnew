<?
class CPL_Admin_Modules_Hms_InternalLocation_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListPublishedImage($row['published'], $row['internal_location_id'])}
            {$listObj->getListDataCell($row['internal_location_id'], '', '', '25%')}
            {$listObj->getListRowEnd($row['internal_location_id'])}
            ";

            $rowCounter++;
        }


        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 's.title')}
        {$listObj->getListHeaderCell('Published', 's.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Location Id', 's.internal_location_id')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Location Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $exp = array('useKey' => 1);

        $fieldset1 = "
        {$formObj->getTBRow('Location Name', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'] )}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Location Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = "";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}