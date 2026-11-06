<?
class CPL_Admin_Modules_Hms_Vaccination_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db      = Zend_Registry::get('db');

        $count   = 0;
        $rows    = '';
        $group_name = "";
        foreach ($dataArray as $row){

            $SQLGroupName = "
            SELECT group_name
            FROM medical_test_group
            WHERE medical_test_id = {$row['medical_test_id']}
            ";
            $resultGroupName = $db->sql_query($SQLGroupName);
            $group_name = "";
            while ($rowGroupName = $db->sql_fetchrow($resultGroupName)){
                $group_name .= $rowGroupName['group_name'].', ';
            }
            $group_name = rtrim($group_name, ', ');

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['medical_test_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($group_name)}
            {$listObj->getListDataCell($row['fees'])}
            {$listObj->getListDataCell($row['normal_value'])}
            {$listObj->getListSortOrderField($row, 'medical_test_id')}
            {$listObj->getListRowEnd($row['medical_test_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'c.medical_test_code')}
        {$listObj->getListHeaderCell('Title', 'mt.title')}
        {$listObj->getListHeaderCell('Group Name', '')}
        {$listObj->getListHeaderCell('MRP', 'mt.fees' )}
        {$listObj->getListHeaderCell('Brand', 'mt.normal_value' )}
        {$listObj->getListSortOrderImage('mt')}
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
        {$formObj->getTBRow('Title', 'title')}
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

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $SQLInvestigationCategory   = $fn->getValueListSQL('investigationCategory');        
        $SQLInvestigationGroupName   = $fn->getValueListSQL('investigationGroup', 'value');        
        $SQLRoute      = $fn->getValueListSQL('route');

        //$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');
        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=hms_vaccination&_spAction=addNewValuelistForm&valuelist_name=investigationGroup&medical_test_id={$row['medical_test_id']}&showHTML=0";
        $expGroup = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='investigationGroup'>Add</a>");

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'> Medical Test Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Code', 'medical_test_code', $row['medical_test_code'])}</td>
                                <td>{$formObj->getTBRow('Title', 'title', $row['title'])}</td>
                                <td>{$formObj->getTBRow('MRP', 'fees', $row['fees'])}</td>
                                <td>{$formObj->getTBRow('Brand', 'normal_value', $row['normal_value'])}</td>
                                <td>{$formObj->getTBRow('Units', 'units', $row['units'])}</td>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Dosage', 'dosage', $row['dosage'])}</td>
                                <td>{$formObj->getDDRowBySQL('Route', 'route', $SQLRoute, $row['route'], $expVl)}</td>
                                <td colspan='2' class='notesTitle'>{$formObj->getTARow('Description ', 'description', $row['description'])}</td>
                            </tr>        
                            <tr>
                                <td colspan='5' class='creModdate'>{$formObj->getCreationModificationText($row)}</td>
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

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
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
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');


        $record_id = $fn->getIssetParam($row, 'medical_test_id');

        $text = "
        <div id='medicalTestGroupLinkPortal'>
            {$this->getMedicalTestGroupLink($record_id)}
        </div>
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_vaccination', 'attachment', $row)}
        ";
        /*
        <div id='medicalTestLinkPortal'>
            {$this->getMedicalTestParameter($record_id)}
        </div>
        */
        return $text;
    }

    /**
     *
     */
    function getMedicalTestParameter($medical_test_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($medical_test_id == ''){
            $medical_test_id = $fn->getReqParam('medical_test_id');
        }

        $medicalParameters = $this->getMedicalTestParameterDetail($medical_test_id);

        $recCount = $fn->getRecordCount('medical_test_parameter', "medical_test_id = '{$medical_test_id}'");

        $header ="
        <thead>
            <tr>
            <th>Title</th>
            <th>Normal Value</th>
            <th>Units</th>
            <th>Created BY</th>
            <th>Updated By</th>
            <th class='portalActBtns'></th>
            <th class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $formActionMedicalParameters = "index.php?module=hms_vaccination&_spAction=medicalParameters&medical_test_id={$medical_test_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddMedicalParameters' href='{$formActionMedicalParameters}' medical_test_id='{$medical_test_id}'>
                        Add
                    </a>
                </div>
                ";

        $text = "
        <div class='linkPortalWrapper hms_medical_parametersLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Medical Test Parameters</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddMedicalParametersPortal'>
                            {$medicalParameters}
                        </tbody>
                    </table>
                    <input type='hidden' name='medical_test_id' value='{$medical_test_id}' />
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
    function getMedicalTestParameterDetail($medical_test_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($medical_test_id == ''){
            $medical_test_id = $fn->getReqParam('medical_test_id');
        }

        $medical_test_parameter_id = $fn->getReqParam('medical_test_parameter_id');

        $rows  = "";

        $SQL="
        SELECT m.*
        FROM medical_test_parameter m
        WHERE m.medical_test_id = '{$medical_test_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditMedicalParameters   = "index.php?module=hms_vaccination&_spAction=editMedicalParameters&medical_test_parameter_id={$row['medical_test_parameter_id']}&medical_test_id={$medical_test_id}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteMedicalParameters' href='#'  medical_test_parameter_id='{$row['medical_test_parameter_id']}' medical_test_id='{$row['medical_test_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditMedicalParameters' href='{$formActionEditMedicalParameters}' medical_test_parameter_id='{$row['medical_test_parameter_id']}'  medical_test_id='{$row['medical_test_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";

            $rows .= "
                <tr>
                    <td>{$row['title']}</td>
                    <td>{$row['normal_value']}</td>
                    <td>{$row['units']}</td>
                    <td>{$row['created_by']}</td>
                    <td>{$row['modified_by']}</td>                    
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
    function getMedicalParameters() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $medical_test_id  = $fn->getReqParam('medical_test_id');

        $formAction = "index.php?_topRm=main&module=hms_vaccination&_spAction=medicalParametersFormSubmit&showHTML=0";

        $text = "
        <form id='medicalParametersPortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title')}
            {$formObj->getTBRow('Normal Value', 'normal_value')}
            {$formObj->getTBRow('Units', 'units')}
            <input type='hidden' name='medical_test_id' value='{$medical_test_id}' />
        </form>
        ";
        return $text;
    }    
     /**
     *
     */
    function getEditMedicalParameters() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $medical_test_id  = $fn->getReqParam('medical_test_id');
        $medical_test_parameter_id  = $fn->getReqParam('medical_test_parameter_id');

        if($medical_test_parameter_id == ''){
        $medical_test_parameter_id  = $fn->getReqParam('medical_test_parameter_id');
        }

        $rows  = "";

        $formAction = "index.php?module=hms_vaccination&_spAction=editMedicalParametersFormSubmit&showHTML=0&medical_test_parameter_id={$medical_test_parameter_id}";

        $SQLMedPara="
        SELECT m.*
        FROM medical_test_parameter m
        WHERE m.medical_test_parameter_id = '{$medical_test_parameter_id}'
        ";
        $resultMedPara   = $db->sql_query($SQLMedPara);
        $rowMedPara = $db->sql_fetchrow($resultMedPara);

        $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Title', 'title', $rowMedPara['title'])}
            {$formObj->getTBRow('Normal Value', 'normal_value', $rowMedPara['normal_value'])}
            {$formObj->getTBRow('Units', 'units', $rowMedPara['units'])}
            <input type='hidden' name='medical_test_parameter_id' value='{$medical_test_parameter_id}' />
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

        $category   = $fn->getReqParam('category');
        $group_name   = $fn->getReqParam('group_name');

        $sqlCategory = $fn->getValueListSQL('investigationCategory');
        $sqlgroup_name = $fn->getValueListSQL('investigationGroup', 'sort_order');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='group_name' >
                <option value=''>Group Name</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlgroup_name, $group_name)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $medical_test_id    = $fn->getReqParam('medical_test_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=hms_vaccination&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='medical_test_id' value='{$medical_test_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedicalTestGroupLink($medical_test_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($medical_test_id == ''){
            $medical_test_id = $fn->getReqParam('medical_test_id');
        }

        $MedicalTestGroup = $this->getMedicalTestGroupLinkDetails($medical_test_id);

        $recCount = $fn->getRecordCount('medical_test_group', "medical_test_id = '{$medical_test_id}'");

        $header ="
        <thead>
            <tr>
                <th>Title</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $formActionMedicalTestGroup = "index.php?module=hms_vaccination&_spAction=AddMedicalTestGroup&medical_test_id={$medical_test_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddMedicalTestGroup' href='{$formActionMedicalTestGroup}' medical_test_id={$medical_test_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper hms_vaccination_medicalTestGroupLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Age Group Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='MedicalTestGrouplist'>
                        {$header}
                        <tbody id='AddMedicalTestGroupPortal'>
                            {$MedicalTestGroup}
                        </tbody>
                    </table>
                    <input type='hidden' name='medical_test_id' value='{$medical_test_id}' />
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
    function getMedicalTestGroupLinkDetails($medical_test_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($medical_test_id == ''){
            $medical_test_id = $fn->getReqParam('medical_test_id');
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $rows  = "";

        $SQL="
        SELECT *
        FROM medical_test_group
        WHERE medical_test_id = '{$medical_test_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditMedicalTestGroup   = "index.php?module=hms_vaccination&_spAction=EditMedicalTestGroup&medical_test_group_id={$row['medical_test_group_id']}&medical_test_id={$medical_test_id}&showHTML=0";

            $deleteIcon = "
            <div class='float_right'>
                <a class='deleteMedicalTestGroup' href='#'  medical_test_group_id='{$row['medical_test_group_id']}' medical_test_id='{$row['medical_test_id']}'>
                    <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                </a>
            </div>
            ";

            $editIcon = "
            <div class='float_right'>
                <a class='EditMedicalTestGroup' href='{$formActionEditMedicalTestGroup}' medical_test_group_id='{$row['medical_test_group_id']}'  medical_test_id='{$row['medical_test_id']}'>
                    <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                </a>
            </div>
            ";


            $rows .= "
                <tr>
                    <td>{$row['group_name']}</td>                    
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
    function getAddMedicalTestGroup() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');
        //$SQLInvestigationGroupName = $fn->getValueListSQL('investigationGroup', 'value');  
        $SQLInvestigationGroupName = $fn->getValueListSQL('investigationGroup', 'sort_order');

        $medical_test_id  = $fn->getReqParam('medical_test_id');

        $formAction = "index.php?_topRm=main&module=hms_vaccination&_spAction=medicalTestGroupSubmit&showHTML=0";

        $text = "
        <form id='medicalTestGroupPortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Group Name', 'group_name', $SQLInvestigationGroupName, '', $expVl)}
            <input type='hidden' name='medical_test_id' value='{$medical_test_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getEditMedicalTestGroup() {
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $medical_test_id        = $fn->getReqParam('medical_test_id');
        $medical_test_group_id  = $fn->getReqParam('medical_test_group_id');
        $expVl = array('sqlType' => 'OneField');
        //$SQLInvestigationGroupName = $fn->getValueListSQL('investigationGroup', 'value'); 
        $SQLInvestigationGroupName = $fn->getValueListSQL('investigationGroup', 'sort_order');

        if($medical_test_group_id == ''){
            $medical_test_group_id  = $fn->getReqParam('medical_test_group_id');
        }

        $rows  = "";

        $formAction = "index.php?module=hms_vaccination&_spAction=EditMedicalTestGroupFormSubmit&showHTML=0&medical_test_group_id={$medical_test_group_id}";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQLMedicalTestGroup = "
        SELECT group_name
        FROM  medical_test_group
        WHERE medical_test_group_id = '{$medical_test_group_id}'
        ";
        $resultMedicalTestGroup   = $db->sql_query($SQLMedicalTestGroup);
        $rowMedicalTestGroup = $db->sql_fetchrow($resultMedicalTestGroup);

        $rows .= "
        <form id='medicalTestGroupEditPortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Group Name', 'group_name', $SQLInvestigationGroupName, $rowMedicalTestGroup['group_name'], $expVl)}
            <input type='hidden' name='medical_test_group_id' value='{$medical_test_group_id}' />
        </form>
        ";        

        $text = "{$rows}";

        return $text;
    }
}