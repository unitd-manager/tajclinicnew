<?
class CPL_Admin_Modules_Tradingsg_Supplier_View extends CP_Common_Lib_ModuleViewAbstract
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

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['company_name'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['gst_no'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['supplier_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Main Phone Number', 's.phone' )}
        {$listObj->getListHeaderCell('Category', 's.category' )}
        {$listObj->getListHeaderCell('GST No', 's.gst_no' )}
        {$listObj->getListHeaderCell('Status', 's.status' )}
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
        {$formObj->getTBRow('Supplier Name', 'company_name')}
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

        $sqlStatus   = $fn->getValueListSQL('supplierStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        $createLogin = '';
        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');
        //<td>{$formObj->getTBRow('Discount Percent', 'discount_percent', $row['discount_percent'])}</td>

        if($row['address_country'] == ''){
            $row['address_country'] =  'IN';
        }
        
        $categoryArr = array(
            "Medicine"
            ,"Lab"
        );


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Supplier Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                    {$createLogin}
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'company_name', $row['company_name'])}</td>
                                <td>{$formObj->getTBRow('Main Phone Number', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Alternate Phone Number', 'contact_phone', $row['contact_phone'])}</td>
                                <td>{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}</td>
                                <td>{$formObj->getDDRowByArr('Category', 'category', $categoryArr, $row['category'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getTBRow('Gst No', 'gst_no', $row['gst_no'])}</td>
                                <td>{$formObj->getTBRow('Tin No', 'tin_no', $row['tin_no'])}</td>
                                <td>{$formObj->getTBRow('Dl No', 'cst_no', $row['cst_no'])}</td>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('State', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>

                            <!--<tr>
                                <th colspan='6'>More Details</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getDDRowBySQL('Supplier Type', 'supplier_type', $sqlSupplier, $row['supplier_type'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Industry', 'industry', $sqlIndustry, $row['industry'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Company Size', 'company_size', $sqlSize, $row['company_size'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Company Source', 'source', $sqlSource, $row['source'], $expVl)}</td>
                            </tr>-->
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
    function getEdit1($row){
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

        $createLogin = '';
        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Supplier Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                    {$createLogin}
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'company_name', $row['company_name'])}</td>
                                <td>{$formObj->getTBRow('Website', 'website', $row['website'])}</td>
                                <td>{$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                                <td>{$formObj->getTBRow('Alternate Email', 'notification_email', $row['notification_email'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('TIN No.', 'tin_no', $row['tin_no'])}</td>
                                <td>{$formObj->getTBRow('CST No.', 'cst_no', $row['cst_no'])}</td>
                            </tr>

                            <tr>
                                <th colspan='6'>Supplier Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}</td>
                            </tr>

                            <tr>
                                <th colspan='6'>Return Address</th>
                            </tr>

                            <tr>
                                <td>{$formObj->getTBRow('Address1', 'return_address_flat', $row['return_address_flat'])}</td>
                                <td>{$formObj->getTBRow('Address2', 'return_address_street', $row['return_address_street'])}</td>
                                <td>{$formObj->getTBRow('District/ Town', 'return_address_town', $row['return_address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'return_address_state', $row['return_address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'return_address_country', $sqlCountry, $row['return_address_country'], $expCountry)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    function getEdit2($row){
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
        $sqlGroupName = $fn->getValueListSQL('companyGroupName');
        $sqlCustomerType = $fn->getValueListSQL('customerType');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $expVl = array('sqlType' => 'OneField');

        if ($cpCfg['m.tradingsg.company.hasDiscountPercent']) {
            $discountPercent = $formObj->getTBRow('Discount Percent', 'discount_percent', $row['discount_percent']);
        }

        if ($cpCfg['m.tradingsg.company.hasCstNo']) {
            $cstNo = $formObj->getTBRow('Cst No', 'cst_no', $row['cst_no']);
        }

        if ($cpCfg['m.tradingsg.company.hasTinNo']) {
            $tinNo = $formObj->getTBRow('Tin No', 'tin_no', $row['tin_no']);
        }

        if ($cpCfg['m.tradingsg.company.hasGstNo']) {
            $gstNo = $formObj->getTBRow('Gst No', 'gst_no', $row['gst_no']);
        }

        //{$formObj->getDDRowBySQL('Customer Type', 'customer_type', $sqlCustomerType, $row['customer_type'], $expVl)}
        //{$formObj->getYesNoRRow('Add FREIGHT COST', 'add_freight_cost', $row['add_freight_cost'])}

        $fieldset1 = "
        {$formObj->getTBRow('Name', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Main Fax', 'fax', $row['fax'])}
        ";


        $fieldset2 = "
        {$formObj->getTBRow('Office Address', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        ";

        $fieldset3 = "
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Supplier Type', 'supplier_type', $sqlSupplier, $row['supplier_type'], $expVl)}
        {$formObj->getDDRowBySQL('Industry', 'industry', $sqlIndustry, $row['industry'], $expVl)}
        {$formObj->getDDRowBySQL('Company Size', 'company_size', $sqlSize, $row['company_size'], $expVl)}
        {$formObj->getDDRowBySQL('Company Source', 'source', $sqlSource, $row['source'], $expVl)}
        {$discountPercent}
        {$cstNo}
        {$tinNo}
        {$gstNo}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Address', $fieldset2)}
        {$formObj->getFieldSetWrapped('More Details', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getCreateLoginForm() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $supplier_id = $fn->getReqParam('supplier_id');
        $email = $fn->getReqParam('email');

        $formAction = "index.php?_topRm=utils&module=tradingsg_supplier&_spAction=createLoginFormSubmit&showHTML=0";

        $text = "
        <form id='createLoginForm' class='createLoginForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('First Name', 'first_name', '')}
            {$formObj->getTBRow('Last Name', 'last_name', '')}
            {$formObj->getTBRow('Email', 'email', $email)}
            {$formObj->getTBRow('Password', 'pass_word', '')}
            <input type='hidden' name='supplier_id' value='{$supplier_id}' />
        </form>
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
        $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text = "
        {$this->getProductDetailDisplay($row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_supplier', 'attachment', $row)}
        ";

        $sqlSupplier = "
        SELECT s.*
        FROM supplier s
        WHERE s.supplier_id = {$row['supplier_id']}
        ";

        $resultSupplier = $db->sql_query($sqlSupplier);
        $rowSupplier = $db->sql_fetchrow($resultSupplier);

        $printText ="";
        if ($rowSupplier['supplier_id'] != '') {
            if($row['category'] == 'Lab'){
                $printText .="
                <div id='medTestVisitPortal'>{$this->getMedTestVisitPortal($row['supplier_id'])}</div>
                ";
            } else {
                $printText .="
                <div id='renewalLinkPortal'>{$this->getAddPurchaseOrder($row['supplier_id'])}</div>
                ";                
            }
        }
        $text = $printText . $text;

        return $text;
    }
    /**
     *
     */
    function getProductDetailDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        

        $SQLPD = "
        SELECT pop.cost_price
              ,pop.free_items   
              ,p.title AS product
        FROM `po_product` pop
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        LEFT JOIN (supplier s) ON (s.supplier_id = pop.supplier_id)
        WHERE pop.cost_price != 0.00
        AND pop.supplier_id = {$row['supplier_id']}
        GROUP BY pop.product_id
        ORDER BY pop.product_id DESC
        ";

        $resultPD   = $db->sql_query($SQLPD);
        $recCount = $db->sql_numrows($resultPD);
        while ($rowPD = $db->sql_fetchrow($resultPD)) {

            $rows .= "
            <tr>
                <td>{$rowPD['product']}</td>
                <td>{$rowPD['free_items']}</td>
                <td>{$rowPD['cost_price']}</td>
            </tr>
            ";
        }

        $header ="
        <tr>
          <th>Medicine Name</th>
          <th>Free</th>
          <th>Cost Price</th>
        </tr>
        ";

        if($recCount == 0){
            $header = "<tr><td align='center'>No Records Linked<br/><br/></td></tr>";
        }



        $text = "
        <div class='linkPortalWrapper tradingsg_supplier_productDetailDisplayLink'>
          <div class='panel panel-primary'>
            <div class='panel-heading'>
              <div expanded='1'>
                  <div class='floatbox'>
                      <div class='float_left RightPanelHeading'>Product Detail Display</div>
                      <div class='txtRight'>
                          <span class='count' id='ProductDetailDisplayPortalCount'>({$recCount})</span>
                          <div class='toggle'></div>
                      </div>
                  </div>
              </div>
            </div>
            <div class='panel-body'>
                <div class='linkPortalDataWrapper'>
                    <form>
                        <table class='ProductDetailDisplayList'>
                            <thead>
                               {$header}
                            </thead>
                            <tbody id='ProductDetailDisplayPortal'>
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
    function getAddPurchaseOrder($supplier_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        if($supplier_id == ''){
            $supplier_id = $fn->getReqParam('supplier_id');
        }

        $PurchaseOrder = $this->getAddPurchaseOrderDetail($supplier_id);

        $recCount = $fn->getRecordCount('purchase_order', "company_id_supplier = '{$supplier_id}'");

        $header ="
        <thead>
            <tr>
                <th width='8%'>PO Code</th>
                <th width='8%'>PO Date</th>
                <th width='8%'>Invoice Date</th>
                <th width='8%'>Invoice Code</th>
                <th width='14%' class='txtRight'>PO Value</th>
                <th width='15%' class='txtRight'>Balance</th>
                <th width='15%'>Payment Status</th>
                <th width='15%'></th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $actionButtons = '';

        $SQLPO = "
        SELECT p.purchase_order_id
        FROM purchase_order p
        WHERE p.company_id_supplier = {$supplier_id}
        AND (p.payment_status != 'Cancelled'
        OR p.payment_status IS NULL)
        ";
        $resultPO = $db->sql_query($SQLPO);
        $numRowsPO = $db->sql_numrows($resultPO);

        if($numRowsPO > 0){
            //$formActionPurchaseOrder = "index.php?module=tradingsg_supplier&_spAction=generatePurchaseOrderForm&showHTML=0";

            $actionButtons .="
            <div class='header'>
                <div class='floatbox'>
                    <div class='btn btn-info'>
                        <a supplier_id='{$supplier_id}' id='generatePO'>Make Supplier Payment</a>
                    </div>
                </div>
            </div>
            ";
        }

        $monthSearch    = '';

        $month  = $fn->getReqParam('month');
        if ($month == '') {
            $month = date('m');
        }

        $arr = array (
            '01' => 'January'
           ,'02' => 'February'
           ,'03' => 'March'
           ,'04' => 'April'
           ,'05' => 'May'
           ,'06' => 'June'
           ,'07' => 'July'
           ,'08' => 'August'
           ,'09' => 'September'
           ,'10' => 'October'
           ,'11' => 'November'
           ,'12' => 'December'
        );

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        LIMIT 2
        ";

        $siteSearch = '';

        $site_id  = $fn->getReqParam('site_id');
        if ($site_id == '') {
            $site_id = $fn->getSessionParam('cp_site_id');
        }

        $monthSearch = "
        <div class='float_left mt5 mb5'>
            <td class='fieldValue'>
                <select name='month'>
                    <option value=''>Select Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
        </div>
        ";

        $yearSearch = '';
        $SQLPOYear  = "
        SELECT DISTINCT DATE_FORMAT(purchase_order_date, '%Y')
        FROM purchase_order
        WHERE purchase_order_date != ''
        ";

        $year  = $fn->getReqParam('year');
        if ($year == '') {
            $year = date('Y');
        }

        $yearSearch = "
        <div class='float_left mt5 mb5'>
            <select name='year'>
                <option value=''>Select Year</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLPOYear, $year)}
            </select>
        </div>
        ";

        $siteSearch = "";
        if($cpCfg['cp.hasMultiUniqueSites']) {
            $siteSearch = "
            <div class='float_left mt5 mb5'>
                <td class='fieldValue'>
                    <select name='site'>
                        <option value=''>Select Site</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlSite, $site_id)}
                    </select>
                </td>
            </div>
            ";
        }

        $text = "
        <div class='linkPortalWrapper tradingsg_supplier__tradingsg_purchase_OrderLink' id='purchaseordermonthfilter'>
            {$actionButtons}
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Purchase Order Linked</div>
                    {$monthSearch}
                    {$yearSearch}
                    {$siteSearch}
                    <div class='txtRight float_right'>
                        <span class='count'>({$recCount})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody>
                            {$PurchaseOrder}
                        </tbody>
                    </table>
                    <input type='hidden' name='supplier_id' value='{$supplier_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddPurchaseOrderDetail($supplier_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($supplier_id == ''){
            $supplier_id = $fn->getReqParam('supplier_id');
        } 

        $site_id = $fn->getReqParam('site_id');

        $appendSite = '';
        if($site_id != "") {
            $appendSite = "AND pc.site_id = {$site_id}";
        }

        //$company_id_supplier = $fn->getReqParam('company_id_supplier');
        $rows = "";

        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        if ($month == '') {
            $month = date('m');
        } else {
            $month = $month;
        }

        if ($year == '') {
            $year = date('Y');
        } else {
            $year = $year;
        }

        $SQL="
        SELECT pc.*
        FROM purchase_order pc
        LEFT JOIN supplier su ON pc.company_id_supplier = su.supplier_id
        WHERE pc.company_id_supplier = '{$supplier_id}'
        AND DATE_FORMAT(pc.purchase_order_date, '%Y-%m') = '{$year}-{$month}'                 
        AND pc.status != 'Cancelled'
        {$appendSite}
        order by pc.invoice_date
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $OveraatotalCost  = 0;
        $OverallBalance   = 0;
        $overall_discount = 0;

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $purchase_order_date = $fn->getCPDate($row['purchase_order_date'], 'd-m-Y');
            $invoice_date        = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
            $overall_discount    = $row['overall_discount'];

            if($overall_discount == ''){
                $overall_discount = 0;
            }

            $SQLTotal = "
            SELECT SUM(pop.qty * pop.cost_price) AS total_cost
                  ,SUM((((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
                  ,SUM((((pop.qty * pop.cost_price) - (((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
            FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);
            $totalCost   = $rowTotal['total_cost'] - $rowTotal['Discount_Total'] + $rowTotal['GST_Total'];
            $OveraatotalCost += round($totalCost);

            //$totalCost = $rowTotal['total_cost'];
            //$totalCost = number_format($rowTotal['total_cost'], 2);
            $totalCost   = number_format(round($totalCost));
            $purchaseOrderLink = "index.php?_topRm=inventory&module=tradingsg_purchaseOrder&_action=edit&purchase_order_id={$row['purchase_order_id']}";

            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM supplier_receipt_history srh
            LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
            WHERE srh.purchase_order_id = {$row['purchase_order_id']}
              AND sr.receipt_status    != 'Cancelled'
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

            $Balance = $rowTotal['total_cost'] - $rowTotal['Discount_Total'] + $rowTotal['GST_Total'] - $rowPartialPayment['Po_partial_payment'];
            $OverallBalance   += round($Balance);
            $Balance = number_format(round($Balance));
            if($row['site_id'] == 2){
                $color =  'style=background-color:#DDEBF9';
            }
            else{
                $color =  'style=background-color:#FFFFFF';
            }

            $viewHistoryUrl = "index.php?module=tradingsg_supplier&_spAction=receiptHistoryForSupplier&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
            $viewHistory = "
            <a href='{$viewHistoryUrl}' purchase_order_id='{$row['purchase_order_id']}' class='receiptViewHistory'><u>View History</u></a>";

            $rows .= "
            <tr $color> 
                <td width='8%'>{$row['po_code']}</td>
                <td width='8%' ><a href='{$purchaseOrderLink}' target='_blank'><u>{$purchase_order_date}</u></a></td>
                <td width='8%' >{$invoice_date}</td>
                <td width='8%'>{$row['supplier_inv_code']}</td>
                <td width='14%' style='color:blue'; class='txtRight'>{$totalCost}</td>
                <td width='15%' style='color:blue'; class='txtRight'>{$Balance}</td>
                <td width='15%'>{$row['payment_status']}</td>
                <td width='15%'>{$viewHistory}</td>
            </tr>
            ";

            //$OveraatotalCost += $totalCost;
            //$OverallBalance   += $Balance;


            $count++;
        }
        $OveraatotalCost = number_format($OveraatotalCost, 2);
        $OverallBalance = number_format($OverallBalance, 2);
        
        $rows .= "
            <tr>
                <td class='txtRight lastRowBgColor' colspan='4'>Total</td>
                <td style='color:blue'; class='txtRight lastRowBgColor' ><b>{$OveraatotalCost}</b></td>
                <td style='color:blue'; class='txtRight lastRowBgColor' ><b>{$OverallBalance}</b></td>
                <td  class='txtRight lastRowBgColor'></td>
                <td  class='txtRight lastRowBgColor'></td>
            </tr>
        ";

        if($numRows == 0){
            $rows = "
                <tr>
                    <td class='noRenewal txtCenter' colspan='8'>
                        <br/>
                        No Records Linked
                        <br/><br/>
                    </td>
                </tr>
            ";

        }
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

        $sqlStatus = $fn->getValueListSQL('supplierStatus');

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
    /**
     *
     */
    function getGeneratePurchaseOrderForm() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $overall_discount = 0;
        unset($_SESSION['selectedPOIds']);

        $rows   = '';
        $today  = date('Y-m-d');
        $month  = $fn->getReqParam('month');
        $year   = $fn->getReqParam('year');
       
        if ($month == '') {
            $month = date('m');
            
            if($year == "") {
                $year  = date('Y');
            }
        }else{
            $month = $month;

            if($year == "") {
                $year  = date('Y');
            }
        }

        $supplier_id = $fn->getReqParam('supplier_id');
        $monthSearch = '';

        $arr = array (
                '01' => 'January'
               ,'02' => 'February'
               ,'03' => 'March'
               ,'04' => 'April'
               ,'05' => 'May'
               ,'06' => 'June'
               ,'07' => 'July'
               ,'08' => 'August'
               ,'09' => 'September'
               ,'10' => 'October'
               ,'11' => 'November'
               ,'12' => 'December'
            );


        $monthSearch = "
        <div class='float_right  monthfilter mt5 mb5'>
            <td class='fieldValue'>
                <select name='month'>
                    <option>Select Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
        </div>
        ";

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(supHist.amount) AS prev_sum
            FROM supplier_receipt_history supHist
            LEFT JOIN supplier_receipt r ON (r.supplier_receipt_id = supHist.supplier_receipt_id)
            WHERE supHist.purchase_order_id =  i.purchase_order_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
            ,o.supplier_id
        FROM purchase_order i
        LEFT JOIN `supplier` o ON (i.company_id_supplier = o.supplier_id)
        WHERE i.company_id_supplier = {$supplier_id}
        AND DATE_FORMAT(i.purchase_order_date, '%Y-%m') = '{$year}-{$month}'
        AND (i.payment_status = 'Due' || i.payment_status = 'Partially Paid' || i.payment_status IS NULL)
        AND i.status != 'Cancelled'
        order by i.invoice_date
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        /*if ($numRows == 0) {
            return "Sorry no po is available or all the po are closed";
        }*/

        $header ="
        <thead>
            <tr height='40px'>
                <th class='click-all-top'>
                    <a href='#' class='check-all'>
                        <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                    </a>
                    <a href='#' class='uncheck-all'>
                        <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                    </a>
                </th>                        
                <th>Invoice Code</th>
                <input type='hidden' name='supplier_id' value='{$supplier_id}' />
            </tr>
        </thead>
        ";
        
        $SupplierPayment = $this->getSupplierPaymentDetail();

        $formAction = "index.php?_topRm=inventory&module=tradingsg_supplier&_spAction=generatePurchaseOrderFormSubmit&showHTML=0";       
        $expNoEdit  = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Purchase Order</h3>
            <div id='supplierpaymentmonthfilter'>
                <div class='float_right'>
                    Choose Month : {$monthSearch}
                </div>
                <table border='1' width='100%' cellpadding='4' class='renewallist thinlist room-poCode-table'>
                    {$header}
                    <tbody>
                        {$SupplierPayment}
                    </tbody>
                </table>
            </div>
            {$formObj->getTBRow('Amount', 'amount', '', $expNoEdit)}
            <input type='hidden' name='totalAmountPo' value='' />
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='supplier_id' value='{$supplier_id}' />
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getSupplierPaymentDetail(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $overall_discount = 0;
        unset($_SESSION['selectedPOIds']);

        $rows   = '';
        $today  = date('Y-m-d');

        $month  = $fn->getReqParam('month');
        $year   = $fn->getReqParam('year');
       
        if ($month == '') {
            $month = date('m');
            
            if($year == "") {
                $year  = date('Y');
            }
        }else{
            $month = $month;

            if($year == "") {
                $year  = date('Y');
            }
        }

        $supplier_id = $fn->getReqParam('supplier_id');

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(supHist.amount) AS prev_sum
            FROM supplier_receipt_history supHist
            LEFT JOIN supplier_receipt r ON (r.supplier_receipt_id = supHist.supplier_receipt_id)
            WHERE supHist.purchase_order_id =  i.purchase_order_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM purchase_order i
        LEFT JOIN `supplier` o ON (i.company_id_supplier = o.supplier_id)
        WHERE i.company_id_supplier = {$supplier_id}
        AND DATE_FORMAT(i.purchase_order_date, '%Y-%m') = '{$year}-{$month}'
        AND (i.payment_status = 'Due' || i.payment_status = 'Partially Paid' || i.payment_status IS NULL)
        AND i.status != 'Cancelled'
        order by i.invoice_date
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if($numRows == 0) {
            return "<tr><td colspan='2'>Sorry no po is available or all the po are closed</td></tr>";
        }

        $count = 1;
        $po_amount = 0;
        $prev_inv_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $overall_discount = $row['overall_discount'];

            if($overall_discount == ''){
                $overall_discount = 0;
            }

            $sqlQty = "
            SELECT SUM(pop.qty*pop.cost_price) AS po_amount
            FROM po_product pop
            WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $sqlQty = "
            SELECT SUM(pop.qty * pop.cost_price) AS po_amount
                  ,SUM((((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
                  ,SUM((((pop.qty * pop.cost_price) - (((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
            FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);
            $po_amount   = $rowQty['po_amount'] - $rowQty['Discount_Total'] + $rowQty['GST_Total'];
            //$po_amount = $rowQty['po_amount'];

            $paidAmountPrev = "";
            $prev_inv_amount = number_format($row['prev_inv_amount'], 2);
            if($row['prev_inv_amount'] > 0){
                $paidAmountPrev = "Paid: {$prev_inv_amount}";
            }

            $po_amount = number_format(round($po_amount), 2);

            $inputRow = "
            <input type='checkbox' class='inputCheckboxForPurchaseOrder poCode' name='poCode[]' value='{$row['po_code']}' purchase_order_id='{$row['purchase_order_id']}'>
            <input type='hidden' class='inputSiteIdForPurchaseOrder siteId' name='siteId[]' value='{$row['site_id']}' purchase_order_id='{$row['purchase_order_id']}'>
            ";

            if($row['site_id'] == 2){
                $color =  'style=background-color:#DDEBF9';
            }
            else{
                $color =  'style=background-color:#FFFFFF';
            }

            $rows .= "
                <tr height='40px' {$color}>
                    <td>{$inputRow}</td>
                    <td>INV NO : {$row['supplier_inv_code']}({$po_amount})</td>
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
    function getNewSupplier(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addSupplier&lnkRoom=tradingsg_supplier&showHTML=0";
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Name', 'company_name')}
                {$formObj->getTBRow('Website', 'website')}
                {$formObj->getTBRow('Phone', 'phone')}
                {$formObj->getTBRow('Gst No', 'gst_no')}
                {$formObj->getTBRow('Office Address', 'address_flat')}
                {$formObj->getTBRow('Street Address', 'address_street')}
                {$formObj->getTBRow('District/ Town', 'address_town')}
                {$formObj->getTBRow('State/ Zip', 'address_state')}
                {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry)}
            </fieldset>
            
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedTestVisitPortal($supplier_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        if($supplier_id == ''){
            $supplier_id = $fn->getReqParam('supplier_id');
        }

        $MedTestVisit = $this->getMedTestVisitPortalDetail($supplier_id);

        $header ="
        <thead>
            <tr>
                <th>Date</th>
                <th>Investigations (Patient Visit)</th>
                <th>Investigations (Self)</th>
                <th>Investigations (In Patient)</th>
                <th class='txtRight'>Total amount</th>
            </tr>
        </thead>
        ";

        $monthSearch    = '';

        $month  = $fn->getReqParam('month');
        if ($month == '') {
            $month = date('m');
        }

        $arr = array (
                '01' => 'January'
               ,'02' => 'February'
               ,'03' => 'March'
               ,'04' => 'April'
               ,'05' => 'May'
               ,'06' => 'June'
               ,'07' => 'July'
               ,'08' => 'August'
               ,'09' => 'September'
               ,'10' => 'October'
               ,'11' => 'November'
               ,'12' => 'December'
               );

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        LIMIT 2
        ";

        $siteSearch    = '';

        $site_id  = $fn->getReqParam('site_id');
        if ($site_id == '') {
            $site_id = $fn->getSessionParam('cp_site_id');
        }

        $monthSearch = "
        <div class='float_left mt5 mb5'>
            <td class='fieldValue'>
                <select name='month'>
                    <option value=''>Select Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
        </div>
        ";

        $siteSearch = "
        <div class='float_left mt5 mb5'>
            <td class='fieldValue'>
                <select name='site'>
                    <option value=''>Select Site</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSite, $site_id)}
                </select>
            </td>
        </div>
        ";

        $text = "
        <div class='linkPortalWrapper' id='investigationMonthfilter'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Investigations Linked</div>
                    {$monthSearch}
                    {$siteSearch}
                    <div class='txtRight float_right'>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody>
                            {$MedTestVisit}
                        </tbody>
                    </table>
                    <input type='hidden' name='supplier_id' value='{$supplier_id}' />
                </form>
            </div>
        </div>
        ";

        return $text;

    }

    /**
     *
     */
    function getMedTestVisitPortalDetail($supplier_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($supplier_id == ''){
            $supplier_id = $fn->getReqParam('supplier_id');
        } 

        $site_id = $fn->getReqParam('site_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSite = '';
        if($site_id != "") {
            $appendSite = "AND mt.site_id = {$cpSiteIdSession}";
        }

        //$company_id_supplier = $fn->getReqParam('company_id_supplier');

        $rows  = "";

        $month       = $fn->getReqParam('month');

        if ($month == '') {
            $month = date('m');
        } else {
            $month = $month;
        }

        $SQL = "
        SELECT * FROM (
          SELECT site_id, DATE_FORMAT(mtv.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_visit mtv 
        WHERE mtv.supplier_id = {$supplier_id}
          UNION  
          SELECT site_id, DATE_FORMAT(mtl.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_lab mtl 
        WHERE mtl.supplier_id = {$supplier_id}
          UNION  
          SELECT site_id, DATE_FORMAT(mtip.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_in_patient mtip 
        WHERE mtip.supplier_id = {$supplier_id}
        ) A
        WHERE DATE_FORMAT(creation_date, '%m') = '{$month}'
        AND site_id = {$cpSiteIdSession}
        GROUP BY DATE_FORMAT(creation_date, '%Y-%m-%d')
        ORDER BY creation_date DESC
        ";
        /*$SQL = "
        SELECT mt.*
              ,SUM(mt.lab_supplier_fees) AS total_fees
        FROM medical_test_visit mt
        WHERE mt.supplier_id = {$supplier_id}
        AND DATE_FORMAT(mt.creation_date, '%m') = '{$month}'                 
        {$appendSite}
        GROUP BY mt.creation_date
        ";*/
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $OveraatotalCost  = 0;

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            $title = '';
            $SQLMTPV = "
            SELECT mt.title
                  ,SUM(mt.lab_supplier_fees) AS total_fees
            FROM medical_test_visit mt
            WHERE DATE_FORMAT(mt.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
            {$appendSite}
            GROUP BY mt.title
            ";
            $resultMTPV = $db->sql_query($SQLMTPV);
            $totalPattestamount = 0;
            while ($rowMTPV = $db->sql_fetchrow($resultMTPV)) {
                $title .= $rowMTPV['title'].', ';
                $totalPattestamount += $rowMTPV['total_fees'];
            }
            $title = rtrim($title, ", ");

            $title1 = '';
            $SQLMTLT = "
            SELECT mt.title
                  ,SUM(mt.lab_supplier_fees) AS total_fees
            FROM medical_test_lab mt
            WHERE DATE_FORMAT(mt.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
            {$appendSite}
            GROUP BY mt.title
            ";
            $resultMTLT = $db->sql_query($SQLMTLT);
            $totaltestamount = 0;
            while ($rowMTLT = $db->sql_fetchrow($resultMTLT)) {
                $title1 .= $rowMTLT['title'].', ';
                $totaltestamount += $rowMTLT['total_fees'];
            }
            $title1 = rtrim($title1, ", ");

            $title2 = '';
            $SQLMTIP = "
            SELECT mt.title
                  ,SUM(mt.lab_supplier_fees) AS total_fees
            FROM medical_test_in_patient mt
            WHERE DATE_FORMAT(mt.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
            {$appendSite}
            GROUP BY mt.title
            ";
            $resultMTIP = $db->sql_query($SQLMTIP);
            $totalIpTestAmount = 0;
            while ($rowMTIP = $db->sql_fetchrow($resultMTIP)) {
                $title2 .= $rowMTIP['title'].', ';
                $totalIpTestAmount += $rowMTIP['total_fees'];
            }

            $title2 = rtrim($title2, ", ");
            $total_fees = $totalPattestamount + $totaltestamount + $totalIpTestAmount;

            $rows .= "
                <tr> 
                    <td>{$creation_date}</td>
                    <td>{$title}</td>
                    <td>{$title1}</td>
                    <td>{$title2}</td>
                    <td class='txtRight'>{$total_fees}</td>
                </tr>
            ";
            $OveraatotalCost += $totalPattestamount + $totaltestamount + $totalIpTestAmount;
        }
        $OveraatotalCost = number_format($OveraatotalCost, 2);
        
        $rows .= "
            <tr>
                <td class='txtRight lastRowBgColor' colspan='4'>Total</td>
                <td style='color:blue'; class='txtRight lastRowBgColor' ><b>{$OveraatotalCost}</b></td>
            </tr>
        ";

        if($numRows == 0){
            $rows = "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }

    /**
     *
     */
    function getReceiptHistoryForSupplier() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_id    = $fn->getReqParam('purchase_order_id');

        $rows = '';
        $errorText = '';

        $sqlClient = "
        SELECT srh.amount
              ,sr.date
              ,sr.mode_of_payment
              ,sr.receipt_status
              ,sr.supplier_receipt_id
              ,sr.supplier_id
              ,sr.remarks
              ,srh.purchase_order_id
        FROM supplier_receipt_history srh
        LEFT JOIN (supplier_receipt sr) ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
        WHERE srh.purchase_order_id = {$purchase_order_id}
        ORDER BY srh.supplier_receipt_history_id
        ";

        $result     = $db->sql_query($sqlClient);
        $numRows    = $db->sql_numrows($result);

        $amount = '';
        if ($numRows == 0) {
            $clientRows =  "
            <table class='thinlist'>
                <td>Sorry, there is no payment records.</td>
            </table>";
        }
        else{
            while ($row = $db->sql_fetchrow($result)) {
                $date = $fn->getCPDate($row['date'], 'd-m-Y');
                if ($row['receipt_status'] != 'Cancelled'){
                    $cancelSupplierReceipt = "<a href='#' class='cancelSupplierReceipt' supplier_receipt_id='{$row['supplier_receipt_id']}' purchase_order_id='{$row['purchase_order_id']}' supplier_id='{$row['supplier_id']}'><u>Cancel</u></a>";
                } else{
                    $cancelSupplierReceipt = 'Cancelled';                    
                }

                $amount = number_format($row['amount'], 2);
                $rows .= "
                <tr>
                    <td>{$date}</td>
                    <td align='right'>{$amount}</td>
                    <td>{$row['mode_of_payment']}</td>
                    <td>{$row['remarks']}</td>
                    <!--<td>{$cancelSupplierReceipt}</td>-->
                </tr>
                ";
            }

            $clientRows = "
            <table class='thinlist'>
                <thead>
                    <th>Date</th>
                    <th class='txtRight'>Amount</th>
                    <th>Mode of Payment</th>
                    <th>Notes</th>
                    <!--<th></th>-->
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            ";
        }

        $text ="
        {$clientRows}
        ";

        return $text;
    }
}