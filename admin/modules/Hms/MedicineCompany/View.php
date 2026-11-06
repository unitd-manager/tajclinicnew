<?
class CPL_Admin_Modules_Hms_MedicineCompany_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email     = $row['email'];
            $website   = $row['website'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['medicine_company_name'])}
            {$listObj->getListDataCell("<a href='{$website}'>{$website}</a>")}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListPublishedImage($row['published'], $row['medicine_company_id'])}
            {$listObj->getListRowEnd($row['medicine_company_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'c.medicine_company_name')}
        {$listObj->getListHeaderCell('Website', 'c.website')}
        {$listObj->getListHeaderCell('Telephone', 'c.phone' )}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
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
        {$formObj->getTBRow('Client Name', 'medicine_company_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEditOld($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Name', 'medicine_company_name', $row['medicine_company_name'])}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}
        ";

        $fieldset2 = "
        {$formObj->getTBRow('Address1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address2', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        ";

        $fieldset3 = "
        {$formObj->getTBRow('Address1', 'billing_address_flat', $row['billing_address_flat'])}
        {$formObj->getTBRow('Address2', 'billing_address_street', $row['billing_address_street'])}
        {$formObj->getTBRow('District/ Town', 'billing_address_town', $row['billing_address_town'])}
        {$formObj->getTBRow('State/ Zip', 'billing_address_state', $row['billing_address_state'])}
        {$formObj->getDDRowBySQL('Country', 'billing_address_country', $sqlCountry, $row['billing_address_country'], $expCountry)}
        ";


        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Client Delivery Address', $fieldset2)}
        {$formObj->getFieldSetWrapped('Client Billing Address', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
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
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='0' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Client Details &nbsp;&nbsp;&nbsp;&nbsp; {$row['medicine_company_name']}</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'medicine_company_name', $row['medicine_company_name'])}</td>
                                <td>{$formObj->getTBRow('Website', 'website', $row['website'])}</td>
                                <td>{$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getYesNoRRow('Published', 'published', $row['published'])}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Client Delivery Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>

                            <tr>
                                <th colspan='5'>Client Billing Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'billing_address_flat', $row['billing_address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'billing_address_street', $row['billing_address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'billing_address_town', $row['billing_address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'billing_address_state', $row['billing_address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'billing_address_country', $sqlCountry, $row['billing_address_country'], $expCountry)}</td>
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
        {$formObj->getTBRow('Company Name', 'medicine_company_name')}
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

        $record_id = $fn->getIssetParam($row, 'medicine_company_id');

        $text = "
        <div id='productDisplayPortal'>{$this->getProductDisplay($row['medicine_company_id'])}</div>
        {$displayLinkData->getLinkPortalMain('hms_medicineCompany', 'hms_contactLink', 'Contacts Linked', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_medicineCompany', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getProductDisplayOLD($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $grandTotal  = 0;
        

        $SQLPD = "
        SELECT pop.cost_price
              ,p.title AS product
              ,SUM(pop.cost_price * pop.qty) AS total_value
        FROM `po_product` pop
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        WHERE pop.cost_price != 0.00
        AND pop.medicine_company_id = {$row['medicine_company_id']}
        GROUP BY pop.product_id
        ";

        $resultPD   = $db->sql_query($SQLPD);
        $recCount = $db->sql_numrows($resultPD);
        $total_value = 0;
        while ($rowPD = $db->sql_fetchrow($resultPD)) {
            $total_value = number_format($rowPD['total_value'], 2);

            $rows .= "
            <tr>
                <td>{$rowPD['product']}</td>
                <td>{$total_value}</td>
            </tr>
            ";
            $grandTotal += $rowPD['total_value'];
        }

        $header ="
        <tr>
          <th>product Name</th>
          <th>Cost Price</th>
        </tr>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $grandTotal = number_format($grandTotal, 2);


        $text = "
        <div class='linkPortalWrapper hms_medicineCompany_productDisplayLink'>
          <div class='panel panel-primary'>
            <div class='panel-heading'>
              <div expanded='1'>
                  <div class='floatbox'>
                      <div class='float_left RightPanelHeading'>Product Display</div>
                      <div class='txtRight'>
                          <span class='count' id='ProductDisplayPortalCount'>({$recCount})</span>
                          <div class='toggle'></div>
                      </div>
                  </div>
              </div>
            </div>
            <div class='panel-body'>
                <div class='linkPortalDataWrapper'>
                    <div class='header'>
                        <div class='floatbox'>
                            <div class='float_right grandTotalProductDisplay'>
                                Grand Total: {$grandTotal}
                            </div>
                        </div>
                    </div>
                    <form>
                        <table class='ProductDisplayList'>
                            <thead>
                               {$header}
                            </thead>
                            <tbody id='ProductDisplayPortal'>
                                {$rows}
                            </tbody>
                        </table>
                </form>
            </div>
            </div>
          </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getProductDisplay($medicine_company_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($medicine_company_id == ''){
            $medicine_company_id = $fn->getReqParam('medicine_company_id');
        }

        $productDetail = $this->getProductDisplayDetail($medicine_company_id);

        $recCount = $fn->getRecordCount('product', "medicine_company_id = '{$medicine_company_id}'");

        $header ="
        <thead>
            <tr>
                <th>Medicines</th>
                <th>Offer Medicine</th>
                <th class='portalActBtns'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }

        $formActionLinkProduct = "index.php?module=hms_medicineCompany&_spAction=linkProduct&medicine_company_id={$medicine_company_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddProduct' href='{$formActionLinkProduct}' medicine_company_id='{$medicine_company_id}'>
                        Add
                    </a>
                </div>
                ";

        $text = "
        <div class='linkPortalWrapper hms_medicine_siteLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Medicine Linked</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                    </div>
                </div>
            </div>
            <div class='addExistingMedicine float_left mb10 mt10 ml10'>
                <input type='text' value='' id='fld_product_title' class='text' name='product_title' placeholder='Add Medicine' medicine_company_id={$medicine_company_id}>
            </div>
            <div class='float_left productExist mt10'>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddProductDetailPortal'>
                            {$productDetail}
                        </tbody>
                    </table>
                    <input type='hidden' name='medicine_company_id' value='{$medicine_company_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }

    /**
     *
     */
    function getProductDisplayDetail($medicine_company_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($medicine_company_id == ''){
            $medicine_company_id = $fn->getReqParam('medicine_company_id');
        }

        $rows  = "";

        $SQL = "
        SELECT p.title AS product, p.product_id, p.offer_medicine
        FROM `product` p
        WHERE p.medicine_company_id = {$medicine_company_id}
        ";
        $result   = $db->sql_query($SQL);
        $recCount = $db->sql_numrows($result);
        
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $deleteIcon ="
                <div class=''>
                    <a class='deleteProduct' href='#'  medicine_company_id='{$medicine_company_id}' product_id='{$row['product_id']}'>
                        <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    </a>
                </div>
                ";
            
            $rows .= "
                <tr>
                    <td width='30%'>{$row['product']}</td>
                    <td width='20%'>
                        <input type='text' value='{$row['offer_medicine']}' id='fld_offer_medicine' class='float_left text w100' name='offer_medicine' product_id='{$row['product_id']}'>
                        <input class='float_left btn btn-warning medicineSave' type='button' value='Save' name='portalForm' />
                    </td>
                    <td>
                        {$deleteIcon}
                    </td>
                </tr>
            ";
            $count++;
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
    /**
     *
     */
    function getLinkProduct() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expVl = array('sqlType' => 'OneField');

        $product = "
        <input type='text' value='' id='poProduct' class='text productTitle' name='product_search'>
        ";
        
        $text = "
        <table class='thinlist'>
            <tr>
                <td class='productSize'>{$product}</td>
            </tr>
        </table>
        ";

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
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
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
}