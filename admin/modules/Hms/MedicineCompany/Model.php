<?
class CPL_Admin_Modules_Hms_MedicineCompany_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT c.*
              ,gc.name AS country_name
        FROM medicine_company c
        LEFT JOIN (geo_country gc) ON (c.address_country = gc.country_code)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $status       = $fn->getReqParam('status');
        $medicine_company_id   = $fn->getReqParam('medicine_company_id');
        $medicine_company_name = $fn->getReqParam('medicine_company_name');

        if ($medicine_company_id != "") {
            $searchVar->sqlSearchVar[] = "c.medicine_company_id = '{$medicine_company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.medicine_company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.medicine_company_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($medicine_company_name != "") {
                $searchVar->sqlSearchVar[] = "c.medicine_company_name LIKE '%{$medicine_company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.medicine_company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            $searchVar->sqlSearchVar[] = "c.category = 'Client'";
            $searchVar->sortOrder = "c.medicine_company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('medicine_company_name', 'Please enter the company name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['category']  = 'Client';
        $fa['published'] = 1;
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'medicine_company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_street');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_town');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_state');
        $fa = $fn->addToFieldsArray($fa, 'billing_address_country');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'customer_type');
        $fa = $fn->addToFieldsArray($fa, 'mark_up_percentage');
        $fa = $fn->addToFieldsArray($fa, 'cst_no');
        $fa = $fn->addToFieldsArray($fa, 'tin_no');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'medicine_company_id'      => $phpExcel->getFldObj('Company ID')
             ,'medicine_company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'hms_medicineCompany'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }
    /**
     *
     */
    function getHmsCompanyHmsContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.medicine_company_id = b.medicine_company_id
          AND b.medicine_company_id = {$id}
        ";
    }
    /**
     *
     */
    function getHmsCompanyHmsDiscountLinkSQL($id) {

        return "
        SELECT d.discount_id
              ,pg.title
              ,c.title AS category_title
              ,d.margin
              ,d.discount_percent
        FROM discount d
        LEFT JOIN (product_group pg) ON (d.product_group_id = pg.product_group_id)
        LEFT JOIN (category c) ON (d.category_id = c.category_id)
        WHERE d.medicine_company_id = {$id}
        ORDER BY pg.sort_order
        ";
    }

    /**
     *
     */
    function getHmsCompanyHmsCompanyGroupLinkSQL1($id) {

        return "
        SELECT a.medicine_company_id
              ,a.medicine_company_name
              ,a.status
        FROM company_group b, company a
        WHERE a.medicine_company_id = b.medicine_company_id
          AND b.medicine_company_id = {$id}
        ";
    }

    /**
     *
     */
     function getLinkProductSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_id = $fn->getReqParam('product_id');
        $medicine_company_id= $fn->getReqParam('medicine_company_id');
        $SQL    = "
        SELECT medicine_company_id
        FROM product
        WHERE product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $recCount = $db->sql_numrows($result);
        if($row['medicine_company_id'] > 0){
            $mfg_link = "index.php?_topRm=pharmacy&module=hms_medicineCompany&_action=edit&medicine_company_id={$row['medicine_company_id']}";

            $text= "Product already linked. <a href='{$mfg_link}' target='_blank'><u>Click here to go to record</u></a>";
            return $text;  
        } else{
            $SQLUpdate    = "
            UPDATE product
            set medicine_company_id = '{$medicine_company_id}'
            WHERE product_id = {$product_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }
    }

    /**
     *
     */
    function getDeleteLinkedProduct(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $product_id = $fn->getReqParam('product_id');
        $medicine_company_id = $fn->getReqParam('medicine_company_id');

        $SQLUpdate    = "
        UPDATE product
        set medicine_company_id = ''
        WHERE product_id = {$product_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];
        $suppCondition = '';

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title, m.medicine_company_name) AS label
              ,m.medicine_company_name
        FROM product p
        LEFT JOIN (medicine_company m) ON (m.medicine_company_id = p.medicine_company_id)
        WHERE (p.title LIKE '{$productTitle}%')
          AND p.published = 1
        ORDER BY p.title
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getUpdateOfferMedicine() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_id = $fn->getReqParam('product_id');
        $offer_medicine = $fn->getReqParam('offer_medicine');

        $SQL    = "
        UPDATE product
        set offer_medicine = '{$offer_medicine}'
        WHERE product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);

    }
}
