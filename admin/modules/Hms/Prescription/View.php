<?
class CPL_Admin_Modules_Hms_Prescription_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['disease_name'])}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListRowEnd($row['prescription_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Diagnosis', 'p.disease_name')}
        {$listObj->getListHeaderCell('Description', 'p.description' )}
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

        $fielset1 = "
        {$formObj->getTBRow('Diagnosis', 'disease_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Diagnosis Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Diagnosis', 'disease_name', $row['disease_name'])}</td>
                                <td>{$formObj->getTBRow('Description', 'description', $row['description'])}</td>
                            </tr>
                            <tr>
                                <td colspan='2' class='creModdate'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

         $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Diagnosis', 'disease_name')}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $rightpanel = "";

        $record_id = $fn->getIssetParam($row, 'prescription_id');

        $rightpanel .="
        <div id='PrescribeMedicineLinkPortal'>{$this->getAddPrescribeMedicine($record_id)}</div>
        ";
        
        $rightpanel .= "
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_prescription', 'attachment', $row)}
        ";

        return $rightpanel;
    }
    /**
     *
     */
    function getAddPrescribeMedicine($prescription_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($prescription_id == ''){
            $prescription_id = $fn->getReqParam('prescription_id');
        }

        $PrescribeMedicine = $this->getAddPrescribeMedicineDetail($prescription_id);

        $recCount = $fn->getRecordCount('prescribe_medicine', "prescription_id = '{$prescription_id}'");

        $header ="
        <thead>
            <tr>
            <th>Medicine Name</th>
            <th>Dosage</th>
            <th>Instruction</th>
            <th>No of Days</th>
            <th class='portalActBtns'></th>
            <th class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $formActionPrescribeMedicine = "index.php?module=hms_prescription&_spAction=PrescribeMedicine&prescription_id={$prescription_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddPrescribeMedicine' href='{$formActionPrescribeMedicine}' prescription_id='{$prescription_id}'>
                        Add
                    </a>
                </div>
                ";

        $text = "
        <div class='linkPortalWrapper hms_prescribe_medicineLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Prescribe Medicine</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddPrescribeMedicinePortal'>
                            {$PrescribeMedicine}
                        </tbody>
                    </table>
                    <input type='hidden' name='prescription_id' value='{$prescription_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddPrescribeMedicineDetail($prescription_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($prescription_id == ''){
            $prescription_id = $fn->getReqParam('prescription_id');
        }

        $prescribe_medicine_id = $fn->getReqParam('prescribe_medicine_id');

        $rows  = "";

        $SQL="
        SELECT pm.*
        FROM prescribe_medicine pm
        WHERE prescription_id = '{$prescription_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditPrescribeMedicine   = "index.php?module=hms_prescription&_spAction=EditPrescribeMedicine&prescribe_medicine_id={$row['prescribe_medicine_id']}&prescription_id={$prescription_id}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deletePrescribeMedicine' href='#'  prescribe_medicine_id='{$row['prescribe_medicine_id']}' prescription_id='{$row['prescription_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditPrescribeMedicine' href='{$formActionEditPrescribeMedicine}' prescribe_medicine_id='{$row['prescribe_medicine_id']}'  prescription_id='{$row['prescription_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";

            $rows .= "
                <tr>
                    <td>{$row['medicine_name']}</td>
                    <td>{$row['dosage']}</td>
                    <td>{$row['instruction']}</td>
                    <td>{$row['days']}</td>
                    
                    <td>
                        {$editIcon}
                    </td>
                    <td>
                        {$deleteIcon}
                    </td>
                </tr>
            ";
            $count++;
        }

        $text="{$rows}";

        return $text;
    }
    /**
     *
     */
    function getPrescribeMedicine() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $prescription_id  = $fn->getReqParam('prescription_id');
        $sqlInstruction   = $fn->getValueListSQL('instruction');

        $formAction = "index.php?_topRm=main&module=hms_prescription&_spAction=PrescribeMedicineFormSubmit&showHTML=0";

        $text = "
        <form id='perscriptionMedicinePortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Medicine Name', 'medicine_name')}
            {$formObj->getTBRow('Dosage', 'dosage')}
            {$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, '', $expVl)}
            {$formObj->getTBRow('No of Days', 'days')}
            <input type='hidden' name='prescription_id' value='{$prescription_id}' />
            <input type='hidden' name='product_id' value=''>
        </form>
        ";
        return $text;
    }

     /**
     *
     */
    function getEditPrescribeMedicine() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');
        $sqlInstruction   = $fn->getValueListSQL('instruction');
        $prescription_id  = $fn->getReqParam('prescription_id');
        $prescribe_medicine_id  = $fn->getReqParam('prescribe_medicine_id');

        if($prescribe_medicine_id == ''){
        $prescribe_medicine_id  = $fn->getReqParam('prescribe_medicine_id');
        }

        $rows  = "";

        $formAction = "index.php?module=hms_prescription&_spAction=EditPrescribeMedicineFormSubmit&showHTML=0&prescribe_medicine_id={$prescribe_medicine_id}";

        $SQLMedicine="
        SELECT pm.*
        FROM prescribe_medicine pm
        WHERE prescribe_medicine_id = '{$prescribe_medicine_id}'
        ";
        $resultMedicine   = $db->sql_query($SQLMedicine);
        $rowMedicine = $db->sql_fetchrow($resultMedicine);

        $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Medicine Name', 'medicine_name', $rowMedicine['medicine_name'])}
            {$formObj->getTBRow('Dosage', 'dosage', $rowMedicine['dosage'])}
            {$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, $rowMedicine['instruction'], $expVl)}
            {$formObj->getTBRow('No of Days', 'days', $rowMedicine['days'])}
            <input type='hidden' name='prescribe_medicine_id' value='{$prescribe_medicine_id}' />
            <input type='hidden' name='product_id' value='{$rowMedicine['product_id']}' />
        </form>
        ";        

        $text="{$rows}";

        return $text;
    }
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $status   = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
}