<?
class CPL_Admin_Modules_Hms_Product_View extends CP_Admin_Modules_Hms_Product_View
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){

            $item_code = '';
            if($row['item_code'] != ''){
                $item_code = 'PROD-'.$row['item_code'];
            }
            if($row['not_add_in_stock'] == 1){
                $not_add_in_stock = 'Yes';
            } else{
                $not_add_in_stock = '';                
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($item_code, 'center')}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($not_add_in_stock)}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['dosage'])}
            {$listObj->getListDataCell($row['route'])}
            {$listObj->getListDataCell($row['medicine_qty'])}
            {$listObj->getListDataCell($row['days'])}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListDataCell($row['modified_by'] . ' ' . $row['modification_date'])}
            {$listObj->getListRowEnd($row['product_id'])}
            ";
            $rowCounter++;
        }


        $sortOrder = $listObj->getListSortOrderImage('p');

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Item Code', 'item_code' , 'headerCenter')}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('Not Add in Stock', 'p.not_add_in_stock')}
        {$listObj->getListHeaderCell('Price', 'p.price')}
        {$listObj->getListHeaderCell('Category', 'C.category_id')}
        {$listObj->getListHeaderCell('Dosage', 'p.dosage')}
        {$listObj->getListHeaderCell('Route', 'p.route')}
        {$listObj->getListHeaderCell('Medicine Qty', 'p.medicine_qty')}
        {$listObj->getListHeaderCell('Days', 'p.days')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Updated By', 'p.modified_by')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";
        //{$this->getUpdateProductItemCodeNumber()}

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit($row){
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $formObj->mode = $tv['action'];
        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $SQLRoute      = $fn->getValueListSQL('route');
        $sqlInstruction = $fn->getValueListSQL('instruction');
        $SQLdosage      = $fn->getValueListSQL('dosage');

        $expNoEdit = array('isEditable' => 0);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $sqlCategory = '';
        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $validatedProduct = "
        <td>{$formObj->getTBRow('Product Name *', 'title', $ln->gfv($row, 'title', '0'))}</td>
        <td>{$formObj->getTBRow('Price', 'price', $row['price'], $expNoEdit)}</td>
        <td>{$formObj->getTBRow('Dosage', 'dosage', $row['dosage'])}</td>
        <td>{$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}</td>
        ";

        $item_code = '';
        if($row['item_code'] != ''){
            $item_code = 'PROD-'.$row['item_code'];
        }
        $text = "
        <div class='floatbox'>
            <div class='float_right createdModifiedEditTop'><b>Created By :</b> {$row['created_by']} on {$row['creation_date']}&nbsp;&nbsp;&nbsp;&nbsp;<b>Modified By:</b> {$row['modified_by']} {$row['modification_date']}</div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Product Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Item Code', 'item_code', $item_code, $expNoEdit)}</td>
                                {$validatedProduct}
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Route', 'route', $SQLRoute, $row['route'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Medicine Qty', 'medicine_qty', $row['medicine_qty'])}</td>
                                <td>{$formObj->getTBRow('Days', 'days', $row['days'])}</td>
                                <td>{$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, $row['instruction'], $expVl)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}</td>
                                <td>{$formObj->getYesNoRRow('Not add in stock', 'not_add_in_stock', $row['not_add_in_stock'])}</td>
                                <td>{$formObj->getYesNoRRow('Add syringe(POS)', 'add_syringe_in_pos', $row['add_syringe_in_pos'])}</td>
                                <td>{$formObj->getYesNoRRow('Published', 'published', $row['published'])}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
    //==================================================================//
    function getEdit1($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $formObj->mode = $tv['action'];
        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $SQLRoute      = $fn->getValueListSQL('route');
        $sqlInstruction = $fn->getValueListSQL('instruction');
        $SQLdosage      = $fn->getValueListSQL('dosage');

        $expNoEdit = array('isEditable' => 0);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $sqlCategory = '';
        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $validatedProduct = "
        {$formObj->getTBRow('Product Name *', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL('Dosage', 'dosage', $SQLdosage, $row['dosage'], $expVl)}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        ";

        $item_code = '';
        if($row['item_code'] != ''){
            $item_code = $row['item_code'];
        }
            
        $fielset1 = "
        {$formObj->getTBRow('Item Code', 'item_code', $item_code, $expNoEdit)}
        {$validatedProduct}
        {$formObj->getDDRowBySQL('Route', 'route', $SQLRoute, $row['route'], $expVl)}
        {$formObj->getTBRow('Medicine Qty', 'medicine_qty', $row['medicine_qty'])}
        {$formObj->getTBRow('Days', 'days', $row['days'])}
        {$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, $row['instruction'], $expVl)}
        {$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Product Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";
        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'product_id');

        $text ="
        <div id='DosageAgeWiseLinkPortal'>{$this->getAddDosageAgeWise($record_id)}</div>
        <div id='DosageWeightWiseLinkPortal'>{$this->getAddDosageWeightWise($record_id)}</div>
        <div id='BranchMedicineLinkPortal'>{$this->getAddBranchMedicine($record_id)}</div>
        {$media->getRightPanelMediaDisplay('Picture', 'hms_product', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Related Picture', 'hms_product', 'relatedPicture', $row)}
        ";
        return $text;
    }
    /**
     *
     */
    function getAddDosageAgeWise($product_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $DosageAgeWise = $this->getAddDosageAgeWiseDetail($product_id);

        $recCount = $fn->getRecordCount('dosage_agewise', "product_id = '{$product_id}'");

        $header ="
        <thead>
            <tr>
            <th width='10%'>Dosage</th>
            <th width='10%'>Instruction</th>
            <th width='20%'>Age From</th>
            <th width='20%'>Age To</th>
            <th width='15%'>Created BY</th>
            <th width='15%'>Updated By</th>
            <th width='10%' class='portalActBtns'></th>
            <th width='10%' class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $formActionDosageAgeWise = "index.php?module=hms_product&_spAction=DosageAgeWise&product_id={$product_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddDosageAgeWise' href='{$formActionDosageAgeWise}' product_id='{$product_id}'>
                        Add
                    </a>
                </div>
                ";

        $text = "
        <div class='linkPortalWrapper hms_dosage_agewiseLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Dosage Age Wise</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddDosageAgeWisePortal'>
                            {$DosageAgeWise}
                        </tbody>
                    </table>
                    <input type='hidden' name='product_id' value='{$product_id}' />
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
    function getAddDosageAgeWiseDetail($product_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $dosage_agewise_id = $fn->getReqParam('dosage_agewise_id');

        $rows  = "";

        $SQL="
        SELECT da.*
        FROM dosage_agewise da
        WHERE product_id = '{$product_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditDosageAgeWise   = "index.php?module=hms_product&_spAction=EditDosageAgeWise&dosage_agewise_id={$row['dosage_agewise_id']}&product_id={$product_id}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteDosageAgeWise' href='#'  dosage_agewise_id='{$row['dosage_agewise_id']}' product_id='{$row['product_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditDosageAgeWise' href='{$formActionEditDosageAgeWise}' dosage_agewise_id='{$row['dosage_agewise_id']}'  product_id='{$row['product_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";

            $ageFromYear = "";
            if($row['age_from_year'] != ''){
                $ageFromYear  = $row['age_from_year'].' Yrs ';
            } 

            $ageFromMonth = "";
            if($row['age_from_month'] != ''){
                $ageFromMonth = $row['age_from_month'].' Months ';
            } 

            $ageFromDays = "";
            if($row['age_from_day'] != ''){
                $ageFromDays  = $row['age_from_day'].' Days ';
            }

            $ageToYear = "";
            if($row['age_to_year'] != ''){
                $ageToYear    = $row['age_to_year'].' Yrs ';
            } 

            $ageToMonth = "";
            if($row['age_to_month'] != ''){
                $ageToMonth   = $row['age_to_month'].' Months ';
            }

            $ageToDays = "";
            if($row['age_to_day'] != ''){
                $ageToDays    = $row['age_to_day'].' Days ';
            }

            $rows .= "
                <tr>
                    <td>{$row['dosage']}</td>
                    <td>{$row['instruction']}</td>
                    <td>{$ageFromYear}{$ageFromMonth}{$ageFromDays}</td>
                    <td>{$ageToYear}{$ageToMonth}{$ageToDays}</td>
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

        $text = "{$rows}";

        return $text;
    }
    /**
     *
     */
    function getDosageAgeWise() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $product_id  = $fn->getReqParam('product_id');
        $SQLdosage      = $fn->getValueListSQL('dosage');
        $sqlInstruction = $fn->getValueListSQL('instruction');

        $formAction = "index.php?_topRm=main&module=hms_product&_spAction=DosageAgeWiseFormSubmit&showHTML=0";

        $text = "
        <form id='dosageAgeWisePortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Dosage' , 'dosage')}
            {$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, '', $expVl)}
            <table><tr>Age From
            <td class='from_age_box'>{$formObj->getTBRow('(Years)' , 'age_from_year')}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Months)' , 'age_from_month')}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Days)' , 'age_from_day')}</td>
            </tr></table>
            <table><tr>Age To
            <td class='from_age_box'>{$formObj->getTBRow('(Years)' , 'age_to_year')}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Months)' , 'age_to_month')}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Days)' , 'age_to_day')}</td>
            </tr></table>
            <input type='hidden' name='product_id' value='{$product_id}' />
        </form>
        ";
        return $text;
    }    
     /**
     *
     */
    function getEditDosageAgeWise() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');
        $SQLdosage   = $fn->getValueListSQL('dosage');
        $product_id  = $fn->getReqParam('product_id');
        $dosage_agewise_id  = $fn->getReqParam('dosage_agewise_id');
        $sqlInstruction = $fn->getValueListSQL('instruction');

        if($dosage_agewise_id == ''){
        $dosage_agewise_id  = $fn->getReqParam('dosage_agewise_id');
        }

        $rows  = "";

        $formAction = "index.php?module=hms_product&_spAction=EditDosageAgeWiseFormSubmit&showHTML=0&dosage_agewise_id={$dosage_agewise_id}";

        $SQLAgewise="
        SELECT da.*
        FROM dosage_agewise da
        WHERE dosage_agewise_id = '{$dosage_agewise_id}'
        ";
        $resultAgewise   = $db->sql_query($SQLAgewise);
        $rowAgewise = $db->sql_fetchrow($resultAgewise);

        $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Dosage' , 'dosage', $rowAgewise['dosage'])}
            {$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, $rowAgewise['instruction'], $expVl)}
            <table><tr>Age From
            <td class='from_age_box'>{$formObj->getTBRow('(Years)' , 'age_from_year', $rowAgewise['age_from_year'])}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Months)' , 'age_from_month', $rowAgewise['age_from_month'])}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Days)' , 'age_from_day', $rowAgewise['age_from_day'])}</td>
            </tr></table>
            <table><tr>Age To
            <td class='from_age_box'>{$formObj->getTBRow('(Years)' , 'age_to_year', $rowAgewise['age_to_year'])}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Months)' , 'age_to_month', $rowAgewise['age_to_month'])}</td>
            <td class='from_age_box'>{$formObj->getTBRow('(Days)' , 'age_to_day', $rowAgewise['age_to_day'])}</td>
            </tr></table>
            <input type='hidden' name='dosage_agewise_id' value='{$dosage_agewise_id}' />
        </form>
        ";        

        $text="{$rows}";

        return $text;
    }

    /**
     *
     */
    function getAddDosageWeightWise($product_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $DosageWeightWise = $this->getAddDosageWeightWiseDetail($product_id);

        $recCount = $fn->getRecordCount('dosage_wtwise', "product_id = '{$product_id}'");

        $header ="
        <thead>
            <tr>
            <th>Dosage</th>
            <th>Instruction</th>
            <th>Wt From</th>
            <th>Wt To</th>
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

        $formActionDosageWeightWise = "index.php?module=hms_product&_spAction=DosageWeightWise&product_id={$product_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddDosageWeightWise' href='{$formActionDosageWeightWise}' product_id='{$product_id}'>
                        Add
                    </a>
                </div>
                ";

        $text = "
        <div class='linkPortalWrapper hms_dosage_WeightwiseLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Dosage Weight Wise</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddDosageWeightWisePortal'>
                            {$DosageWeightWise}
                        </tbody>
                    </table>
                    <input type='hidden' name='product_id' value='{$product_id}' />
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
    function getAddDosageWeightWiseDetail($product_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $dosage_wtwise_id = $fn->getReqParam('dosage_wtwise_id');

        $rows  = "";

        $SQL="
        SELECT dw.*
        FROM dosage_wtwise dw
        WHERE product_id = '{$product_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditDosageWeightWise   = "index.php?module=hms_product&_spAction=EditDosageWeightWise&dosage_wtwise_id={$row['dosage_wtwise_id']}&product_id={$product_id}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteDosageWeightWise' href='#'  dosage_wtwise_id='{$row['dosage_wtwise_id']}' product_id='{$row['product_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditDosageWeightWise' href='{$formActionEditDosageWeightWise}' dosage_wtwise_id='{$row['dosage_wtwise_id']}'  product_id='{$row['product_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";

            $rows .= "
                <tr>
                    <td>{$row['dosage']}</td>
                    <td>{$row['instruction']}</td>
                    <td>{$row['wt_from']}</td>
                    <td>{$row['wt_to']}</td>
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
    function getDosageWeightWise() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $product_id  = $fn->getReqParam('product_id');
        $SQLdosage      = $fn->getValueListSQL('dosage');
        $sqlInstruction = $fn->getValueListSQL('instruction');

        $formAction = "index.php?_topRm=main&module=hms_product&_spAction=DosageWeightWiseFormSubmit&showHTML=0";

        $text = "
        <form id='dosageWeightWisePortalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Dosage', 'dosage')}
            {$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, '', $expVl)}
            {$formObj->getTBRow('Wt From', 'wt_from')}
            {$formObj->getTBRow('Wt To', 'wt_to')}
            <input type='hidden' name='product_id' value='{$product_id}' />
        </form>
        ";
        return $text;
    }    
     /**
     *
     */
    function getEditDosageWeightWise() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');
        $SQLdosage   = $fn->getValueListSQL('dosage');
        $product_id  = $fn->getReqParam('product_id');
        $dosage_wtwise_id  = $fn->getReqParam('dosage_wtwise_id');
        $sqlInstruction = $fn->getValueListSQL('instruction');

        if($dosage_wtwise_id == ''){
        $dosage_wtwise_id  = $fn->getReqParam('dosage_wtwise_id');
        }

        $rows  = "";

        $formAction = "index.php?module=hms_product&_spAction=EditDosageWeightWiseFormSubmit&showHTML=0&dosage_wtwise_id={$dosage_wtwise_id}";

        $SQLWeightwise="
        SELECT da.*
        FROM dosage_wtwise da
        WHERE dosage_wtwise_id = '{$dosage_wtwise_id}'
        ";
        $resultWeightwise   = $db->sql_query($SQLWeightwise);
        $rowWeightwise = $db->sql_fetchrow($resultWeightwise);

        $rows .= "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Dosage', 'dosage', $rowWeightwise['dosage'])}
            {$formObj->getDDRowBySQL('Instruction', 'instruction', $sqlInstruction, $rowWeightwise['instruction'], $expVl)}
            {$formObj->getTBRow('Wt From', 'wt_from', $rowWeightwise['wt_from'])}
            {$formObj->getTBRow('Wt To', 'wt_to', $rowWeightwise['wt_to'])}
            <input type='hidden' name='dosage_wtwise_id' value='{$dosage_wtwise_id}' />
        </form>
        ";        

        $text="{$rows}";

        return $text;
    }
    /**
     *
     */
    function getProductPriceDetail($product_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $Product = $this->getProductPriceDetailList($product_id);

        $recCount = $fn->getRecordCount('product_price', "product_id = '{$product_id}'");

        $header ="
        <thead>
            <tr>
                <th>Site</th>
                <th>Price</th>
                <th>Edit</th>
                <th>Created / Modified</th>
            </tr>
        </thead>
        ";

        $formActionProductPrice = "index.php?module=hms_product&_spAction=AddProductPrice&product_id={$product_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddProductPrice' href='{$formActionProductPrice}' product_id={$product_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper hms_product_productPriceLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Product Price Linked</div>
                    <div class='txtRight'>
                        <span class='count' id='AddProductPricePortalCount'>({$fn->getRecordCount('product_price', "product_id = '{$product_id}'")})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='productPricelist'>
                        {$header}
                        <tbody id='AddProductPricePortal'>
                            {$Product}
                        </tbody>
                    </table>
                    <input type='hidden' name='product_id' value='{$product_id}' />
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
    function getProductPriceDetailList($product_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $rows  = "";

        $SQL="
        SELECT pp.price
              ,pp.created_by
              ,pp.creation_date
              ,pp.modified_by
              ,pp.modification_date
              ,pp.product_price_id
              ,pp.product_id
              ,s.title AS site_name
        FROM product_price pp
        LEFT JOIN (site s) ON (s.site_id = pp.site_id)
        WHERE product_id = '{$product_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {

            $creation = $row['created_by'].' '.$row['creation_date'];
            if($row['modification_date']){
                $creation = $row['modified_by'].' '.$row['modification_date'];
            }

            $formEditProductPrice  = "index.php?_topRm=inventory&module=hms_product&_spAction=editProductPrice&product_price_id={$row['product_price_id']}&showHTML=0";
            $editPriceRecordLink   = "<a class='EditProductPrice' href='{$formEditProductPrice}' product_price_id='{$row['product_price_id']}' product_id='{$row['product_id']}'><u>Edit</u></a>";
            
            $rows .= "
                <tr>
                    <td>{$row['site_name']}</td>
                    <td>{$row['price']}</td>
                    <td>{$editPriceRecordLink}</td>   
                    <td>{$creation}</td>  
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noProductPrice' colspan='4'><font>No Records Linked</font></td>
                </tr>
            ";

        }

        $text="{$rows}";

        return $text;
    }


    /**
     *
     */
    function getAddProductPrice() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $product_id = $fn->getReqParam('product_id');

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        ORDER BY title
        ";

        $formAction = "index.php?_topRm=inventory&module=hms_product&_spAction=AddProductPriceSubmit&showHTML=0";

        $text = "
        <form id='AddProductPriceForm' class='AddProductPriceForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Site', 'site_id', $sqlSite)}
            {$formObj->getTBRow('Price', 'price', '')}
            <input type='hidden' name='product_id' value='{$product_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditProductPrice() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $product_price_id = $fn->getReqParam('product_price_id');

        $sqlProduct = "
        SELECT price
              ,site_id
        FROM product_price
        WHERE product_price_id = {$product_price_id}
        ";
        $resultproduct = $db->sql_query($sqlProduct);
        $rowproduct = $db->sql_fetchrow($resultproduct);

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        ORDER BY title
        ";

        $expNoEdit = array('disabled' => 1);
        $formAction = "index.php?_topRm=inventory&module=hms_product&_spAction=EditProductPriceSubmit&showHTML=0";

        $text = "
        <form id='EditProductPriceForm' class='AddProductPriceForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Site', 'site_id', $sqlSite, $rowproduct['site_id'], $expNoEdit)}
            {$formObj->getTBRow('Price', 'price', $rowproduct['price'])}
            <input type='hidden' name='product_price_id' value='{$product_price_id}' />
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getProductPriceHistory($product_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $Product = $this->getProductPriceHistoryList($product_id);

        $header ="
        <thead>
            <tr>
                <th>Site</th>
                <th>Date</th>
                <th>Price</th>
                <th>Created / Modified</th>
            </tr>
        </thead>
        ";

        $text = "
        <div class='linkPortalWrapper hms_product_productPriceLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Product Price History</div>
                    <div class='txtRight'>
                        <span class='count' id='AddProductPricePortalCount'>
                            ({$fn->getRecordCount('product_price_history', "product_id = '{$product_id}'")})
                        </span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='productPricelist'>
                        {$header}
                        <tbody id='AddProductPricePortal'>
                            {$Product}
                        </tbody>
                    </table>
                    <input type='hidden' name='product_id' value='{$product_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }

    /**
     *
     */
    function getProductPriceHistoryList($product_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $rows  = "";

        $SQL="
        SELECT pp.price
              ,pp.created_by
              ,pp.creation_date
              ,pp.modified_by
              ,pp.modification_date
              ,pp.product_price_id
              ,pp.product_id
              ,s.title AS site_name
        FROM product_price_history pp
        LEFT JOIN (site s) ON (s.site_id = pp.site_id)
        WHERE product_id = '{$product_id}'
        ORDER BY creation_date DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {

            $creation = $row['created_by'].' '.$row['creation_date'];
            if($row['modification_date']){
                $creation = $row['modified_by'].' '.$row['modification_date'];
            }

            $creation_date = $fn->getCPDate($row['creation_date'], 'Y-m-d');
            
            $rows .= "
                <tr>
                    <td>{$row['site_name']}</td>
                    <td>{$creation_date}</td>
                    <td>{$row['price']}</td>
                    <td>{$creation}</td>  
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noProductPrice' colspan='4'><font>No Records Linked</font></td>
                </tr>
            ";

        }

        $text="{$rows}";

        return $text;
    }

    //==================================================================//
    /**
     *
     * @return <type>
     */
    function getProductCountryLinkSQLxxx($id) {
        $SQL = "
        SELECT c.product_country_id
        	   ,sb.description
        FROM bank sb
        WHERE sb.company_id = {$id}
        ";

        return $SQL;
    }


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $supplier_id     	 = $fn->getReqParam('supplier_id');
        $special_search      = $fn->getReqParam('special_search');
        $category_id         = $fn->getReqParam('category_id');
        $general_quotation   = $fn->getReqParam('general_quotation');
        $published           = $fn->getReqParam('published');
        $subCatOptions  = '';

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');
        $catOptions  = '';

        $sqlSupplier = "
        SELECT c.medical_supplier_id
        	  ,c.title
        FROM medical_supplier c
        ORDER BY c.title
        ";


        if ($tv['category_id'] != "") {
            $sqlCombo = "
            SELECT a.sub_category_id
                  ,a.title
            FROM sub_category a
            WHERE a.category_id = {$tv['category_id']}
            ORDER BY a.title
            ";
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $tv['sub_category_id']);
        }

        /*$olArray = array(
            "Published"
           ,"Not-Published"
           ,"View All"
        ); */

        /*<td>
            <select name='supplier_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>*/

        if($tv['special_search'] == ''){
            $sp_search = $tv['special_search'];
        }
        $spArray = array(
            "Not add in stock"
        );

        if($published == "") {
            $published = 1;
        }

        $publishedArray = array(1 => 'Published', 0 => 'Un Published');

        $text = "
        <td>
            <select name='published'>
                {$cpUtil->getDropDownFromArr($publishedArray, $published)}
            </select>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";


        return $text;
    }

    /**
     *
     */
    function getUpdateProductItemCodeSQL() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT product_id
              ,item_code
        FROM product
        ";
        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {

            /*if ($row['item_code'] != '' || $row['item_code'] != 0){
                $item_code_arr = explode('-', $row['item_code']);
                $item_code_no = $item_code_arr[1];
                $item_code = $fn->getSettingsValueByKey('productCodePrefix') . ($item_code_no + $count);
            } else {*/
                $item_code = $fn->getSettingsValueByKey('productCodePrefix') . ($fn->getSettingsValueByKey('nextProductItemCode') + $count);
            //}

            $sqlUpdate = "
            UPDATE product set item_code = '{$item_code}'
            WHERE product_id = {$row['product_id']}
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);

            $count++;
        }
    }

    /**
     *
     */
    function getUpdateProductItemCodeNumber(){
        $db = Zend_Registry::get('db');
        set_time_limit(50000);

        $SQL = "
        SELECT product_id
        FROM product
        ORDER BY product_id
        ";
        $result = $db->sql_query($SQL);
        $count = 10001;

        while ($row = $db->sql_fetchrow($result)) {
            $SQLUpdate    = "
            UPDATE product
            set item_code = {$count}
            WHERE product_id = {$row['product_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);

            $count++;
        }

        $SQL = "
        SELECT product_id, item_code
        FROM product
        ORDER BY product_id
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $SQLUpdate    = "
            UPDATE order_item
            set item_code = {$row['item_code']}
            WHERE record_id = {$row['product_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);

            $SQLUpdateII    = "
            UPDATE invoice_item
            set item_code = {$row['item_code']}
            WHERE record_id = {$row['product_id']}
            ";
            $resultUpdateII = $db->sql_query($SQLUpdateII);
        }
    }

    /**
     *
     */
    function getAddBranchMedicine($product_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $BranchMedicine = $this->getAddBranchMedicineDetail($product_id);

        $recCount = $fn->getRecordCount('medicine_site', "product_id = '{$product_id}'");

        $header ="
        <thead>
            <tr>
                <th>Site</th>
                <th>Rack</th>
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

        $formActionBranchMedicine = "index.php?module=hms_product&_spAction=BranchMedicine&product_id={$product_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddBranchMedicine' href='{$formActionBranchMedicine}' product_id='{$product_id}'>
                        Add
                    </a>
                </div>
                ";

        $text = "
        <div class='linkPortalWrapper hms_medicine_siteLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Branch Medicine</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddBranchMedicinePortal'>
                            {$BranchMedicine}
                        </tbody>
                    </table>
                    <input type='hidden' name='product_id' value='{$product_id}' />
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
    function getAddBranchMedicineDetail($product_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $medicine_site_id = $fn->getReqParam('medicine_site_id');

        $rows  = "";

        $SQL="
        SELECT ms.*
              ,s.title AS site_name
        FROM medicine_site ms
        LEFT JOIN (site s) ON (s.site_id = ms.site_id)
        WHERE product_id = '{$product_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $formActionEditBranchMedicine   = "index.php?module=hms_product&_spAction=EditBranchMedicine&medicine_site_id={$row['medicine_site_id']}&product_id={$product_id}&showHTML=0";

            $deleteIcon ="
                <div class='float_right'>
                    <a class='deleteBranchMedicine' href='#'  medicine_site_id='{$row['medicine_site_id']}' product_id='{$row['product_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";

            $editIcon ="
                <div class='float_right'>
                    <a class='EditBranchMedicine' href='{$formActionEditBranchMedicine}' medicine_site_id='{$row['medicine_site_id']}'  product_id='{$row['product_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                    </a>
                </div>
                ";

            
            $rows .= "
                <tr>
                    <td>{$row['site_name']}</td>
                    <td>{$row['rake']}</td>
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

        $text = "{$rows}";

        return $text;
    }
    /**
     *
     */
    function getBranchMedicine() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $product_id  = $fn->getReqParam('product_id');

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        ORDER BY title
        ";

        $formAction = "index.php?_topRm=inventory&module=hms_product&_spAction=AddBranchMedicineSubmit&showHTML=0";

        $text = "
        <form id='AddBranchMedicineForm' class='AddBranchMedicineForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Site', 'site_id', $sqlSite)}
            {$formObj->getTBRow('Rack', 'rake', '')}
            <input type='hidden' name='product_id' value='{$product_id}' />
        </form>
        ";

        return $text;
    }    
     /**
     *
     */
    function getEditBranchMedicine() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');
        $medicine_site_id = $fn->getReqParam('medicine_site_id');

        $sqlProduct = "
        SELECT site_id
               ,rake
        FROM medicine_site
        WHERE medicine_site_id = {$medicine_site_id}
        ";
        $resultproduct = $db->sql_query($sqlProduct);
        $rowproduct = $db->sql_fetchrow($resultproduct);

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        ORDER BY title
        ";

        $expNoEdit = array('disabled' => 1);
        $formAction = "index.php?_topRm=inventory&module=hms_product&_spAction=EditBranchMedicineSubmit&showHTML=0";

        $text = "
        <form id='EditBranchMedicineForm' class='EditBranchMedicineForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Site', 'site_id', $sqlSite, $rowproduct['site_id'], '')}
            {$formObj->getTBRow('Rack', 'rake', $rowproduct['rake'])}
            <input type='hidden' name='medicine_site_id' value='{$medicine_site_id}' />
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getLinkMedicineToSites() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        //admin/index.php?_spAction=linkMedicineToSites&showHTML=0&module=hms_product

        $sqlSite = "
        SELECT site_id
        FROM site
        ORDER BY site_id
        ";
        $resultSite   = $db->sql_query($sqlSite);
        
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $SQL="
            SELECT product_id
            FROM product
            ";
            $result   = $db->sql_query($SQL);
            
            while ($row = $db->sql_fetchrow($result)) {
                $fa = array();
                $fa['product_id']       = $row['product_id'];
                $fa['site_id']          = $rowSite['site_id'];
                $fa['creation_date']    = date("Y-m-d H:i:s");
                $fa['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'medicine_site');
                //$result1 = $db->sql_query($insert);
            }
        }
    }    
}