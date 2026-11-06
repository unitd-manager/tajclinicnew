<?
class CPL_Admin_Modules_Tradingsg_PurchaseOrder_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $appendSQL = '';

        $SQL = "
        SELECT po.*
              ,su.company_name
              ,su.company_name AS supplier_name_substr
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM purchase_order po
        LEFT JOIN contact c ON po.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON po.staff_id = s.staff_id
        LEFT JOIN supplier su ON po.company_id_supplier = su.supplier_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'po';

        $status            = $fn->getReqParam('status');
        $supplier_id       = $fn->getReqParam('supplier_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $cpSiteIdSession   = $fn->getSessionParam('cp_site_id');
        $current_month     = $fn->getReqParam('current_month');
        $current_year  = $fn->getReqParam('current_year');

        if ($purchase_order_id != "") {
            $searchVar->sqlSearchVar[] = "po.purchase_order_id = '{$purchase_order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "po.purchase_order_id = '{$tv['record_id']}'";
        } else {

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "po.status = '{$status}'";
            } else{
                $searchVar->sqlSearchVar[] = "po.status != 'Cancelled'";
            }
            if ($supplier_id != "") {
                $searchVar->sqlSearchVar[] = "po.company_id_supplier = '{$supplier_id}'";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "po.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(po.flag != 1 OR po.flag IS null)";
            }

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND po.site_id = {$cpSiteIdSession}";
            }

            if ($tv['keyword'] == "") {
                if ($current_month != "") {
                    $search_form = date($current_year.'-'.$current_month.'-01');
                    $search_to   = date("Y-m-t", strtotime($search_form));
                    $searchVar->sqlSearchVar[] = "(po.purchase_order_date BETWEEN '{$search_form}' AND '{$search_to}')";
                }else {
                    $search_form = date('Y-m-01');
                    $search_to   = date('Y-m-t');
                    $searchVar->sqlSearchVar[] = "(po.purchase_order_date BETWEEN '{$search_form}' AND '{$search_to}')";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    po.po_code  LIKE '%{$tv['keyword']}%'
                    OR po.title LIKE '%{$tv['keyword']}%'
                    OR s.first_name LIKE '%{$tv['keyword']}%'
                    OR s.last_name LIKE '%{$tv['keyword']}%'
                    OR po.status LIKE '%{$tv['keyword']}%'
                    OR po.supplier_inv_code LIKE '%{$tv['keyword']}%'
                    OR po.purchase_order_id IN (
                        SELECT po.purchase_order_id
                        FROM product p
                        LEFT JOIN po_product pop ON (pop.product_id = p.product_id)
                        LEFT JOIN purchase_order po ON (po.purchase_order_id = pop.purchase_order_id)
                        WHERE p.title LIKE '%{$tv['keyword']}%'
                        AND po.purchase_order_id != ''
                        {$appendSql}
                    )
                )";
            }
        }

        $searchVar->sortOrder = "po.purchase_order_date DESC, po.purchase_order_id";

    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('company_id_supplier', 'Please choose the Supplier');

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
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $po_code = $this->getUpdatePOCode();

        $supplier_id = $fn->getPostParam('company_id_supplier');

        $SQLsupplier = "
        SELECT company_name
        FROM `supplier`
        WHERE supplier_id = '{$supplier_id}'
        ";
        $resultsupplier = $db->sql_query($SQLsupplier);
        $rowsupplier    = $db->sql_fetchrow($resultsupplier);

        $fa = $this->getFields();
        $fa['purchase_order_date'] = date('Y-m-d');
        $fa['status']  = 'In progress';
        $fa['payment_status']  = 'Due';
        $fa['title']   = 'Purchase From '.$rowsupplier['company_name'];
        $fa['po_code'] = $po_code;
        $id = $fn->addRecord($fa);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        //$validate->validateData('buy_currency', 'Please choose buy currency');
        $supplier_inv_code = $fn->getPostParam('supplier_inv_code', '', true);
        $purchase_order_id = $fn->getPostParam('purchase_order_id', '', true);

        if ($supplier_inv_code != ''){
            $rec = $fn->getRecordByCondition('purchase_order', "supplier_inv_code = '{$supplier_inv_code}' AND 
                purchase_order_id != {$purchase_order_id} AND status != 'Cancelled'");

            if (is_array($rec)){
                $validate->errorArray['supplier_inv_code']['name'] = "supplier_inv_code";
                $validate->errorArray['supplier_inv_code']['msg']  = "Invoice No Already Exists";
            }
        }


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
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id);
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
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'po_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'company_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'purchase_order_date');
        $fa = $fn->addToFieldsArray($fa, 'buy_currency');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'priority');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'payment_status');
        $fa = $fn->addToFieldsArray($fa, 'invoice_date'); 
        $fa = $fn->addToFieldsArray($fa, 'supplier_inv_code');


        return $fa;
    }

    /**
     *
     */
    function getAddMultipleLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_id  = $fn->getPostParam('purchase_order_id');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $product_arr        = $fn->getReqParam('product_id', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $validate->resetErrorArray();

        $filterArray3 = array_filter($qty_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Qty";
        }

        /*$filterArray2 = array_filter($price_arr);
        if (count($filterArray2) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Price";
        }
        */


        $filterArray = array_filter($product_arr);
        if (count($filterArray) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please select product";
        }

        $SQLproduct = "
        SELECT product_id
        FROM po_product
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $resultproduct = $db->sql_query($SQLproduct);
        $product_id_db_arr = array();
        while($rowproduct    = $db->sql_fetchrow($resultproduct)){
            if($rowproduct['product_id'] != ''){
                $product_id_db_arr[] = $rowproduct['product_id'];
            }
        }

        $product_id_arr = array();
        foreach($product_arr as $key => $value){
            if($value != ''){
                if(in_array($value, $product_id_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Please select different product";
                }

                if(!in_array($value, $product_id_arr)){
                    $product_id_arr[] = $value;
                }

                if(in_array($value, $product_id_db_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Selected product already exists.";
                }
            }

        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddMultipleLineItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $purchase_order_id  = $fn->getPostParam('purchase_order_id');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $product_arr        = $fn->getReqParam('product_id', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $count = count($product_arr);
        for ($i= 0; $i < $count; $i++) {
            $product_id         = $product_arr[$i];
            $qty                = $qty_arr[$i];

            if ($product_id) {
                $SQLPO ="
                SELECT pp.cost_price
                      ,pp.selling_price
                      ,pp.gst
                      ,pp.tag_no
                FROM po_product pp
                WHERE pp.product_id = {$product_id}
                ORDER BY pp.po_product_id desc
                ";
                $resultPo = $db->sql_query($SQLPO);
                $rowPo    = $db->sql_fetchrow($resultPo);

                if($rowPo['cost_price'] == ""){
                    $rowPo['cost_price'] = "0.00";
                }

                if($rowPo['selling_price'] == ""){
                    $rowPo['selling_price'] = "0.00";
                }

                if($rowPo['gst'] == ""){
                    $rowPo['gst'] = "0.00";
                }


                $fa = array();

                $fa['product_id']           = $product_id;
                $fa['selling_price']        = $rowPo['selling_price'];
                $fa['cost_price']           = $rowPo['cost_price'];
                $fa['qty_requested']        = $qty;
                $fa['gst']                  = $rowPo['gst'];
                $fa['tag_no']               = $rowPo['tag_no'];
                $fa['purchase_order_id']    = $purchase_order_id;
                $fa['supplier_id']          = $supplier_id;
                $fa['status']               = 'In progress';
                $fa['creation_date']        = date("Y-m-d H:i:s");
                $fa['created_by']           = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                $result = $db->sql_query($insert);
                $po_product_id = $db->sql_nextid();

                $fa1 = array();
                $fa1['price'] = $price;
                //$fa1['gst']   = $gst;

                $whereCondition = "WHERE product_id = {$product_id}";
                $SQL1    = $dbUtil->getUpdateSQLStringFromArray($fa1, "product", $whereCondition);
                $result1 = $db->sql_query($SQL1);

                /*$fa2 = array();
                $fa2['product_id']       = $product_id;
                $fa2['price']            = $price;
                $fa2['gst']              = $gst;
                $fa2['creation_date']    = date("Y-m-d H:i:s");
                $fa2['created_by']       = $fn->getSessionParam('userName');

                $insert1 = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_price');
                $result2 = $db->sql_query($insert1);*/
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddMultipleLineItemListValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_arr        = $fn->getReqParam('product_id', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $validate->resetErrorArray();

        $filterArray3 = array_filter($qty_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Qty";
        }

        /*$filterArray2 = array_filter($price_arr);
        if (count($filterArray2) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Price";
        }
        */


        $filterArray = array_filter($product_arr);
        if (count($filterArray) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please select product";
        }

        $SQLproduct = "
        SELECT product_id
        FROM po_product
        ";
        $resultproduct = $db->sql_query($SQLproduct);
        $product_id_db_arr = array();
        while($rowproduct    = $db->sql_fetchrow($resultproduct)){
            if($rowproduct['product_id'] != ''){
                $product_id_db_arr[] = $rowproduct['product_id'];
            }
        }

        $product_id_arr = array();
        foreach($product_arr as $key => $value){
            if($value != ''){
                if(in_array($value, $product_id_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Please select different product";
                }

                if(!in_array($value, $product_id_arr)){
                    $product_id_arr[] = $value;
                }

                /*if(in_array($value, $product_id_db_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Selected product already exists.";
                }*/
            }

        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddMultipleLineItemListSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddMultipleLineItemListValidate()){
            return $validate->getErrorMessageXML();
        }

        $supplier_id_arr = $fn->getPostParam('supplier_id', array());
        $product_arr     = $fn->getReqParam('product_id', array());
        $qty_arr         = $fn->getPostParam('qty', array());

        $count = count($product_arr);
        for ($i= 0; $i < $count; $i++) {
            $product_id  = $product_arr[$i];
            $supplier_id = $supplier_id_arr[$i];
            $qty         = $qty_arr[$i];

            if ($product_id) {
                $SQLPO ="
                SELECT pp.cost_price
                      ,pp.selling_price
                      ,pp.gst
                FROM po_product pp
                WHERE pp.product_id = {$product_id}
                ORDER BY pp.po_product_id desc
                ";
                $resultPo = $db->sql_query($SQLPO);
                $rowPo    = $db->sql_fetchrow($resultPo);

                if($rowPo['cost_price'] == ""){
                    $rowPo['cost_price'] = "0.00";
                }

                if($rowPo['selling_price'] == ""){
                    $rowPo['selling_price'] = "0.00";
                }

                if($rowPo['gst'] == ""){
                    $rowPo['gst'] = "0.00";
                }

                $fa = array();

                $fa['product_id']    = $product_id;
                $fa['selling_price'] = $rowPo['selling_price'];
                $fa['gst']           = $rowPo['gst'];
                $fa['cost_price']    = $rowPo['cost_price'];
                $fa['qty_requested'] = $qty;
                $fa['supplier_id']   = $supplier_id;

                $insert3 = $dbUtil->getInsertSQLStringFromArray($fa, 'product_supplier_temp');
                $result3 = $db->sql_query($insert3);

                $SQL = "
                SELECT ps.*
                      ,s.company_name
                FROM product_supplier_temp ps
                LEFT JOIN supplier s ON (s.supplier_id = ps.supplier_id)
                WHERE ps.supplier_id != ''
                  AND ps.product_id > 0
                GROUP BY s.company_name
                ";
                $result = $db->sql_query($SQL);
                while ($row = $db->sql_fetchrow($result)) {
                    $fa = array();
                    $fa['company_id_supplier'] = $row['supplier_id'];
                    $fa['creation_date'] = date('Y-m-d');
                    $fa['purchase_order_date'] = date('Y-m-d');
                    $fa['po_code'] = $this->getUpdatePOCode();
                    $fa['buy_currency'] = 'INR';

                    $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order');
                    $resultInsert = $db->sql_query($SQLInsert);
                    $purchase_order_id = $db->sql_nextid();

                    $SQLSelect = "
                    SELECT ps.*
                          ,s.company_name
                    FROM product_supplier_temp ps
                    LEFT JOIN supplier s ON (s.supplier_id = ps.supplier_id)
                    WHERE ps.supplier_id = {$row['supplier_id']}
                      AND ps.product_id > 0
                    GROUP BY ps.product_id
                    ";
                    $resultSelect = $db->sql_query($SQLSelect);
                    while ($rowPS = $db->sql_fetchrow($resultSelect)) {
                        $fa = array();
                        $fa['product_id']           = $rowPS['product_id'];
                        $fa['selling_price']        = $rowPS['selling_price'];
                        $fa['cost_price']           = $rowPS['cost_price'];
                        $fa['qty_requested']        = $rowPS['qty_requested'];
                        $fa['purchase_order_id']    = $purchase_order_id;
                        $fa['supplier_id']          = $rowPS['supplier_id'];
                        $fa['status']               = 'In progress';
                        $fa['creation_date']        = date("Y-m-d H:i:s");
                        $fa['created_by']           = $fn->getSessionParam('userName');

                        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                        $result = $db->sql_query($insert);
                        $po_product_id = $db->sql_nextid();

                        $fa1 = array();
                        $fa1['price'] = $price;
                        //$fa1['gst']   = $gst;

                        $whereCondition = "WHERE product_id = {$product_id}";
                        $SQL1    = $dbUtil->getUpdateSQLStringFromArray($fa1, "product", $whereCondition);
                        $result1 = $db->sql_query($SQL1);

                        /*$fa2 = array();
                        $fa2['product_id']       = $product_id;
                        $fa2['price']            = $price;
                        $fa2['gst']              = $gst;
                        $fa2['creation_date']    = date("Y-m-d H:i:s");
                        $fa2['created_by']       = $fn->getSessionParam('userName');

                        $insert1 = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_price');
                        $result2 = $db->sql_query($insert1);*/
                    }
                }
                $SQLDELETE="TRUNCATE product_supplier_temp";
                $resultDelete = $db->sql_query($SQLDELETE);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNewProductValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_id = $fn->getPostParam('purchase_order_id');
        $supplier_id       = $fn->getPostParam('supplier_id');
        $product_id_arr    = $fn->getReqParam('product_id', array());
        $price_arr         = $fn->getPostParam('price', array());
        $qty_arr           = $fn->getPostParam('qty', array());
        $qty_arr           = $fn->getPostParam('qty', array());
        $batch_no_arr      = $fn->getPostParam('batch_no', array());
        $expiry_date_arr   = $fn->getPostParam('expiry_date', array());
        $pack_size_arr     = $fn->getPostParam('pack_size', array());

        $validate->resetErrorArray();

        $filterArray3 = array_filter($qty_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box1']['name'] = "error_box1";
            $validate->errorArray['error_box1']['msg']  = "Please Enter Qty";
        }

        /*$filterArray2 = array_filter($price_arr);
        if (count($filterArray2) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Price";
        }
        */

        $filterArray = array_filter($product_id_arr);
        if (count($filterArray) == 0){
            $validate->errorArray['error_box1']['name'] = "error_box1";
            $validate->errorArray['error_box1']['msg']  = "Please enter product";
        }

        $SQLproduct = "
        SELECT product_id
        FROM po_product
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $resultproduct = $db->sql_query($SQLproduct);
        $product_id_db_arr = array();
        while($rowproduct    = $db->sql_fetchrow($resultproduct)){
            if($rowproduct['product_id'] != ''){
                $product_id_db_arr[] = $rowproduct['product_id'];
            }
        }

        $countProd = count($product_id_arr);
        $countCheck = 1;
        $product_idCheck = '';
        $batch_noCheck   = '';
        for ($i= 0; $i < $countProd; $i++) {
            $product_id  = $product_id_arr[$i];
            $batch_no    = $batch_no_arr[$i];
            $pack_size   = $pack_size_arr[$i];
            $expiry_date = $expiry_date_arr[$i];

            if($product_id != ""){
                $SQLProductCheck = "
                SELECT product_id
                FROM po_product
                WHERE purchase_order_id = {$purchase_order_id}
                AND product_id = {$product_id}
                AND batch_no = '{$batch_no}'
                ";
                $resultProductCheck  = $db->sql_query($SQLProductCheck);
                $numRowsProductCheck = $db->sql_numrows($resultProductCheck);

                if($numRowsProductCheck >= 1){
                    $validate->errorArray['error_box1']['name'] = "error_box1";
                    $validate->errorArray['error_box1']['msg']  = "Selected product already exists.";
                }

                if($product_id == $product_idCheck && $batch_no == $batch_noCheck) {
                    $countCheck++;
                }

                $product_idCheck = $product_id;
                $batch_noCheck   = $batch_no;

                if($expiry_date == "") {
                    $validate->errorArray['error_box1']['name'] = "error_box1";
                    $validate->errorArray['error_box1']['msg']  = "Please enter expiry date";
                }

                if($batch_no == "") {
                    $validate->errorArray['error_box1']['name'] = "error_box1";
                    $validate->errorArray['error_box1']['msg']  = "Please enter batch no";
                }

                if($pack_size == "") {
                    $validate->errorArray['error_box1']['name'] = "error_box1";
                    $validate->errorArray['error_box1']['msg']  = "Please enter pack size";
                }
            }
        }

        if($countCheck > 1){
            $validate->errorArray['error_box1']['name'] = "error_box1";
            $validate->errorArray['error_box1']['msg']  = "Selected product already exists.";
        }

        /*$product_id_arr = array();
        foreach($product_id_arr as $key => $value){
            if($value != ''){
                if(in_array($value, $product_id_arr)){
                    $validate->errorArray['error_box1']['name'] = "error_box1";
                    $validate->errorArray['error_box1']['msg']  = "Please select different product";
                }

                if(!in_array($value, $product_id_arr)){
                    $product_id_arr[] = $value;
                }

                if(in_array($value, $product_id_db_arr)){
                    $validate->errorArray['error_box1']['name'] = "error_box1";
                    $validate->errorArray['error_box1']['msg']  = "Selected product already exists.";
                }
            }

        }*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewProductSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddNewProductValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id_arr          = $fn->getReqParam('product_id', array());
        $purchase_order_id       = $fn->getPostParam('purchase_order_id');
        $supplier_id             = $fn->getPostParam('supplier_id');
        $price_arr               = $fn->getPostParam('price', array());
        $cost_price_arr          = $fn->getPostParam('cost_price', array());
        $qty_arr                 = $fn->getPostParam('qty', array());
        $gst_arr                 = $fn->getPostParam('gst', array());
        $hsn_arr                 = $fn->getPostParam('hsn', array());
        $pack_size_arr           = $fn->getPostParam('pack_size', array());
        $medicine_company_arr    = $fn->getPostParam('medicine_company', array());
        $batch_no_arr            = $fn->getPostParam('batch_no', array());
        $free_items_arr          = $fn->getPostParam('free_items', array());
        $expiry_date_arr         = $fn->getPostParam('expiry_date', array());
        $discount_percentage_arr = $fn->getPostParam('discount_percentage', array());

        $count = count($product_id_arr);
        for ($i= 0; $i < $count; $i++) {
            $product_id          = $product_id_arr[$i];
            $price               = $price_arr[$i];
            $cost_price          = $cost_price_arr[$i];
            $qty                 = $qty_arr[$i];
            $gst                 = $gst_arr[$i];
            $hsn                 = $hsn_arr[$i];
            $pack_size           = $pack_size_arr[$i];
            $medicine_company    = $medicine_company_arr[$i];
            $batch_no            = $batch_no_arr[$i];
            $free_items          = $free_items_arr[$i];
            $discount_percentage = $discount_percentage_arr[$i];
            $expiry_date         = $expiry_date_arr[$i];
            //$expiry_date = $fn->getPostParam('expiry_date_'.$i);

            if ($product_id) {
                $fa = array();

                if($price == ''){
                    $price = 0;
                }
                
                if($cost_price == ''){
                    $cost_price = 0;
                }

                if($gst == ''){
                    $gst = 0;
                }

                if($free_items == ''){
                    $free_items = 0;
                }

                if($discount_percentage == ''){
                    $discount_percentage = 0;
                }

                if($qty == ''){
                    $qty = 0;
                }

                $SQLUpdateProduct = "
                UPDATE product SET gst = '{$gst}', price = '{$price}'
                WHERE product_id = '{$product_id}'
                ";
                $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);
                
                $fa['product_id']          = $product_id;
                $fa['selling_price']       = $price;
                $fa['cost_price']          = $cost_price;
                $fa['qty_requested']       = $qty;
                $fa['status']              = 'New';
                $fa['purchase_order_id']   = $purchase_order_id;
                $fa['supplier_id']         = $supplier_id;
                $fa['gst']                 = $gst;
                $fa['hsn']                 = $hsn;
                $fa['status']              = 'In progress';
                $fa['pack_size']           = $pack_size;
                $fa['medicine_company_id'] = $medicine_company;
                $fa['batch_no']            = $batch_no;
                $fa['discount_type']       = '%';
                $fa['discount_percentage'] = $discount_percentage;
                $fa['free_items']          = $free_items;
                $fa['expiry_date']         = $expiry_date;
                $fa['created_by']          = $fn->getSessionParam('userName');
                $fa['creation_date']       = date("Y-m-d H:i:s");

                $insert        = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                $result        = $db->sql_query($insert);
                $po_product_id = $db->sql_nextid();
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNewProductListValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_arr        = $fn->getPostParam('product', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $validate->resetErrorArray();

        $filterArray3 = array_filter($qty_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Qty";
        }

        /*$filterArray2 = array_filter($price_arr);
        if (count($filterArray2) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Price";
        }
        */

        $filterArray = array_filter($product_arr);
        if (count($filterArray) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please enter product";
        }

        /*$SQLproduct = "
        SELECT product_id
        FROM po_product
        ";
        $resultproduct = $db->sql_query($SQLproduct);
        $product_id_db_arr = array();
        while($rowproduct    = $db->sql_fetchrow($resultproduct)){
            if($rowproduct['product_id'] != ''){
                $product_id_db_arr[] = $rowproduct['product_id'];
            }
        }

        $product_id_arr = array();
        foreach($product_arr as $key => $value){
            if($value != ''){
                if(in_array($value, $product_id_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Please select different product";
                }

                if(!in_array($value, $product_id_arr)){
                    $product_id_arr[] = $value;
                }

                if(in_array($value, $product_id_db_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Selected product already exists.";
                }
            }

        }*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewProductListSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddNewProductListValidate()){
            return $validate->getErrorMessageXML();
        }

        $supplier_id_arr    = $fn->getPostParam('supplier_id', array());
        $product_arr        = $fn->getPostParam('product', array());
        $price_arr          = $fn->getPostParam('price', array());
        $cost_price_arr     = $fn->getPostParam('cost_price', array());
        $qty_arr            = $fn->getPostParam('qty', array());
        $gst_arr            = $fn->getPostParam('gst', array());
        $unit_arr           = $fn->getPostParam('unit', array());
        $product_weight_arr = $fn->getPostParam('product_weight', array());
        $hsn_arr            = $fn->getPostParam('hsn', array());
        $category_id_arr    = $fn->getPostParam('category', array());
        $type_arr           = $fn->getPostParam('type', array());

        $count = count($product_arr);
        for ($i= 0; $i < $count; $i++) {
            $product         = $product_arr[$i];
            $price              = $price_arr[$i];
            $cost_price         = $cost_price_arr[$i];
            $qty                = $qty_arr[$i];
            $gst                = $gst_arr[$i];
            $unit               = $unit_arr[$i];
            $product_weight     = $product_weight_arr[$i];
            $hsn                = $hsn_arr[$i];
            $category_id        = $category_id_arr[$i];
            $supplier_id        = $supplier_id_arr[$i];
            $type               = $type_arr[$i];

            if ($product) {
                $fa = array();
                $fa['title']            = $product;
                $fa['selling_price']    = $price;
                $fa['cost_price']       = $cost_price;
                $fa['qty_requested']    = $qty;
                $fa['gst']              = $gst;
                $fa['unit']             = $unit;
                $fa['product_weight']   = $product_weight;
                $fa['hsn']              = $hsn;
                $fa['category_id']      = $category_id;
                $fa['supplier_id']      = $supplier_id;
                $fa['product_type']      = $type;

                $insert3 = $dbUtil->getInsertSQLStringFromArray($fa, 'product_supplier_temp');
                $result3 = $db->sql_query($insert3);

                $SQL = "
                SELECT ps.*
                      ,s.company_name
                      ,c.title AS category_title
                FROM product_supplier_temp ps
                LEFT JOIN supplier s ON (s.supplier_id = ps.supplier_id)
                LEFT JOIN category c ON (c.category_id = ps.category_id)
                WHERE ps.supplier_id != ''
                  AND ps.title != ''
                GROUP BY s.company_name
                ";
                $result = $db->sql_query($SQL);
                while ($row = $db->sql_fetchrow($result)) {
                    $fa = array();
                    $fa['company_id_supplier'] = $row['supplier_id'];
                    $fa['creation_date'] = date('Y-m-d');
                    $fa['po_code'] = $this->getUpdatePOCode();
                    $fa['buy_currency'] = 'INR';
                    $fa['status']  = 'In progress';
                    $fa['payment_status']  = 'Due';
                    $fa['purchase_order_date'] = date('Y-m-d');

                    $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order');
                    $resultInsert = $db->sql_query($SQLInsert);
                    $purchase_order_id = $db->sql_nextid();

                    $SQLSelect = "
                    SELECT ps.*
                          ,s.company_name
                          ,c.title AS category_title
                    FROM product_supplier_temp ps
                    LEFT JOIN supplier s ON (s.supplier_id = ps.supplier_id)
                    LEFT JOIN category c ON (c.category_id = ps.category_id)
                    WHERE ps.supplier_id = {$row['supplier_id']}
                      AND ps.title != ''
                    GROUP BY ps.title
                    ";
                    $resultSelect = $db->sql_query($SQLSelect);
                    while ($rowPS = $db->sql_fetchrow($resultSelect)) {
                        $fa1 = array();
                        $fa1['title'] = $rowPS['title'];
                        $fa1['price'] = $rowPS['selling_price'];
                        $fa1['gst']   = $rowPS['gst'];
                        $fa1['published']  = 1;
                        $fa1['product_weight']  = $rowPS['product_weight'];
                        $fa1['hsn']  = $rowPS['hsn'];
                        $fa1['unit']  = $rowPS['unit'];
                        $fa1['category_id']   = $rowPS['category_id'];
                        $fa1['product_type'] = 'Purchasing and Selling';
                        $fa1['item_code'] = $this->getUpdateProductCode();
                        $fa1['product_type']  = $rowPS['product_type'];

                        $insert1 = $dbUtil->getInsertSQLStringFromArray($fa1, 'product');
                        $result1 = $db->sql_query($insert1);
                        $product_id = $db->sql_nextid();

                        $fa = array();
                        $fa['product_id']           = $product_id;
                        $fa['selling_price']        = $rowPS['selling_price'];
                        $fa['cost_price']           = $rowPS['cost_price'];
                        $fa['qty_requested']        = $rowPS['qty_requested'];
                        $fa['status']               = 'New';
                        $fa['purchase_order_id']    = $purchase_order_id;
                        $fa['supplier_id']          = $rowPS['supplier_id'];
                        $fa['status']               = 'In progress';
                        $fa['creation_date']        = date("Y-m-d H:i:s");
                        $fa['created_by']           = $fn->getSessionParam('userName');

                        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                        $result = $db->sql_query($insert);
                        $po_product_id = $db->sql_nextid();

                        $fa1 = array();
                        $fa1['company_id']      = $rowPS['supplier_id'];
                        $fa1['product_id']      = $product_id;
                        $fa1['creation_date']   = date('Y-m-d H:i:s');

                        $insert4 = $dbUtil->getInsertSQLStringFromArray($fa1, 'product_company');
                        $result4 = $db->sql_query($insert4);
                    }
                }
                $SQLDELETE="TRUNCATE product_supplier_temp";
                $resultDelete = $db->sql_query($SQLDELETE);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */

    function getProductFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getProductValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id        = $fn->getPostParam('product_id');
        $purchase_order_id = $fn->getPostParam('purchase_order_id');
        $price             = $fn->getPostParam('price');
        $qty               = $fn->getPostParam('qty');
        $qty_delivered     = $fn->getPostParam('qty_delivered');
        $status            = $fn->getPostParam('status');

        $fa = array();

        $fa['product_id']       = $product_id;
        $fa['price']            = $price;
        $fa['qty_requested']    = $qty;
        $fa['qty']              = $qty_delivered;
        $fa['status']           = $status;
        $fa['purchase_order_id']= $purchase_order_id;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $fn->addRecord($fa, 'po_product');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */

    function getProductValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('product_group_id', 'Please select the product group');
        //$validate->validateData('product_id', 'Please enter Medicine Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditPoProductRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $status         = $fn->getPostParam('status');
        $po_product_id  = $fn->getPostParam('po_product_id');
        $qty_delivered  = $fn->getPostParam('qty_delivered');
        $qty            = $fn->getPostParam('qty');
        $damage_qty     = $fn->getPostParam('damage_qty');

        $validate->resetErrorArray();

        if($status != "Closed") {
            $validate->validateData('qty_delivered', 'Please enter the qty');
            $validate->validateData('status', 'Please select status');

            if($status == ""){
                $validate->errorArray['error_box1']['name'] = "error_box1";
                $validate->errorArray['error_box1']['msg']  = "Please select status";
            }

            if($qty_delivered == ""){
                $validate->errorArray['error_box1']['name'] = "error_box1";
                $validate->errorArray['error_box1']['msg']  = "Please enter the qty";
            }
            //$po_productRec  = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);

            if($qty_delivered > $qty){
                $validate->errorArray['error_box1']['name'] = "error_box1";
                $validate->errorArray['error_box1']['msg']  = "Please enter qty less than or equal to {$qty}.";

                $validate->errorArray['qty_delivered']['name'] = "qty_delivered";
                $validate->errorArray['qty_delivered']['msg']  = "Please enter qty less than or equal to {$qty}.";
            }

            if($damage_qty > $qty){
                $validate->errorArray['damage_qty']['name'] = "damage_qty";
                $validate->errorArray['damage_qty']['msg']  = "Please enter qty less than or equal to {$qty}.";

                $validate->errorArray['damage_qty']['name'] = "damage_qty";
                $validate->errorArray['damage_qty']['msg']  = "Please enter qty less than or equal to {$qty}.";
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     */
    function getEditPoProductRecordSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getEditPoProductRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $po_product_id       = $fn->getPostParam('po_product_id');
        $qty_delivered       = $fn->getPostParam('qty_delivered');
        $qty                 = $fn->getPostParam('qty');
        $status              = $fn->getPostParam('status');
        $gst                 = $fn->getPostParam('gst');
        $damage_qty          = $fn->getPostParam('damage_qty');
        $pack_size           = $fn->getPostParam('pack_size');
        $batch_no            = $fn->getPostParam('batch_no');
        $expiry_date         = $fn->getPostParam('expiry_date');
        $free_items          = $fn->getPostParam('free_items');
        $cost_price          = $fn->getPostParam('cost_price');
        $selling_price       = $fn->getPostParam('selling_price');
        $medicine_company    = $fn->getPostParam('medicine_company');
        $discount_percentage = $fn->getPostParam('discount_percentage');
        $gst                 = $fn->getPostParam('gst');
        $hsn                 = $fn->getPostParam('hsn');
        $product_id          = $fn->getPostParam('product_id_edit_hidden');

        if($damage_qty == ''){
            $damage_qty = 0;
        }

        if($discount_percentage == ''){
            $discount_percentage = 0;
        }

        $fa = array();
        if($status != "Closed") {
            $fa['pack_size']           = $pack_size;
            $fa['batch_no']            = $batch_no;
            $fa['expiry_date']         = $expiry_date;
            $fa['free_items']          = $free_items;
            $fa['selling_price']       = $selling_price;
            $fa['cost_price']          = $cost_price;
            $fa['qty_requested']       = $qty;
            $fa['qty']                 = $qty_delivered;
            $fa['damage_qty']          = $damage_qty;
            $fa['status']              = $status;
            $fa['discount_percentage'] = $discount_percentage;
            $fa['hsn']                 = $hsn;
        }
        
        $fa['product_id']          = $product_id;
        $fa['medicine_company_id'] = $medicine_company;
        $fa['gst']                 = $gst;
        $fa['modification_date']   = date("Y-m-d H:i:s");
        $fa['modified_by']         = $fn->getSessionParam('userName');            

        $whereCondition = "WHERE po_product_id = {$po_product_id}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, "po_product", $whereCondition);
        $result = $db->sql_query($SQL);

        $SQLPoProd = "
        SELECT product_id, qty, pack_size, batch_no, damage_qty, po_product_id, purchase_order_id, free_items
        FROM po_product
        WHERE po_product_id = '{$po_product_id}'
        ";
        $resultPoProd  = $db->sql_query($SQLPoProd);
        $PoProd = $db->sql_fetchrow($resultPoProd);

        /*Creating record in stock history when editing po product record and changing qty*/
        $SQLSH = "
        SELECT stock_history_id, po_product_id, SUM(qty) AS qty, SUM(damage_qty) AS damage_qty
        FROM stock_history
        WHERE po_product_id = '{$po_product_id}'
        ORDER BY stock_history_id desc
        ";
        $resultSH  = $db->sql_query($SQLSH);
        $rowSH = $db->sql_fetchrow($resultSH);

        $qty = $PoProd['qty'] - $rowSH['qty']; 
        $damage_qty = $PoProd['damage_qty'] - $rowSH['damage_qty']; 
        if($PoProd['qty'] != $rowSH['qty'] || $PoProd['damage_qty'] != $rowSH['damage_qty']){
            $fa = array();
            $fa['po_product_id'] = $po_product_id;
            $fa['product_id']  = $PoProd['product_id'];
            $fa['purchase_order_id']  = $PoProd['purchase_order_id'];
            $fa['qty']  = $qty;
            $fa['damage_qty']  = $damage_qty;
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_history');
            $resultInsert = $db->sql_query($SQLInsert); 
        }           

        /*$SQLOthersite = "
        SELECT
            (SELECT SUM(qty) FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$PoProd['product_id']}
             AND po.status != 'Cancelled') as product_qty_purchased
           
           ,(SELECT SUM(damage_qty) FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$PoProd['product_id']}) as product_qty_damaged

           ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
            LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id)
            WHERE invItem.record_id = {$PoProd['product_id']}
            AND invItem.not_add_in_stock != 1
            AND inv.status != 'Cancelled'
            ) as product_qty_sold

            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            WHERE ini.record_id = {$PoProd['product_id']}
              AND srh.status IS NULL
            ) as sales_return_qty
        ";
        $resultothersite = $db->sql_query($SQLOthersite);
        $rowothersite = $db->sql_fetchrow($resultothersite);

        $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold'] + $rowothersite['sales_return_qty'] - $rowothersite['product_qty_damaged'];
        */

        if($selling_price != ''){
            $fa2 = array();
            $fa2['product_id']       = $PoProd['product_id'];
            $fa2['price']            = $selling_price;
            $fa2['creation_date']    = date("Y-m-d H:i:s");
            $fa2['created_by']       = $fn->getSessionParam('userName');

            $insert1 = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_price');
            $result2 = $db->sql_query($insert1);

            /*$SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = {$stock}, gst = {$gst}, price = {$selling_price}
            WHERE product_id = '{$PoProd['product_id']}'
            ";            
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);*/
        } /*else {
            $SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = {$stock}
            WHERE product_id = '{$PoProd['product_id']}'
            ";
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);
        }*/

        /*$SQLUpdateInventory = "
        UPDATE inventory SET actual_stock = {$stock}
        WHERE product_id = '{$PoProd['product_id']}'
        ";
        $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);*/

        $SQLBatchWiseStock = "
        SELECT po_product_id
              ,purchased_qty
              ,damaged_qty
              ,free_qty
              ,current_stock
        FROM inventory_batchwise_stock
        WHERE po_product_id = '{$po_product_id}'
        AND product_id = '{$PoProd['product_id']}'
        ";
        $resultBatchWiseStock  = $db->sql_query($SQLBatchWiseStock);
        $BatchWiseStock        = $db->sql_fetchrow($resultBatchWiseStock);
        $numRowsBatchWiseStock = $db->sql_numrows($resultBatchWiseStock);
        
        if(is_numeric($PoProd['pack_size'])){
            $poQty = $PoProd['qty'] * $PoProd['pack_size'];
        } else {
            $poQty = $PoProd['qty'];
        }

        if(is_numeric($PoProd['pack_size'])){
            $freeQty = $PoProd['free_items'] * $PoProd['pack_size'];
        } else {
            $freeQty = $PoProd['free_items'];
        }

        if($numRowsBatchWiseStock == 0) {
            $stockUpdate = $poQty + $freeQty - $PoProd['damage_qty'];
            
            $faStock = array();
            $faStock['product_id']        = $PoProd['product_id'];
            $faStock['po_product_id']     = $PoProd['po_product_id'];
            $faStock['purchase_order_id'] = $PoProd['purchase_order_id'];
            $faStock['batch_no']          = $PoProd['batch_no'];
            $faStock['purchased_qty']     = $poQty;
            $faStock['damaged_qty']       = $PoProd['damage_qty'];
            $faStock['free_qty']          = $freeQty;
            $faStock['current_stock']     = $stockUpdate;

            $faStock     = $fn->addCreationDetailsToFieldsArray($faStock, 'inventory_batchwise_stock');
            $SQLStock    = $dbUtil->getInsertSQLStringFromArray($faStock, 'inventory_batchwise_stock');
            $resultStock = $db->sql_query($SQLStock);

            $SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0)+{$stockUpdate}
            WHERE product_id = '{$PoProd['product_id']}'
            ";
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

            $SQLInventory = "
            SELECT product_id
            FROM inventory
            WHERE product_id = {$PoProd['product_id']}
            ";
            $resultInventory  = $db->sql_query($SQLInventory);
            $numRowsInventory = $db->sql_numrows($resultInventory);

            if($numRowsInventory == 0) {
                $SQLInvCode = "
                SELECT MAX(CONVERT(inventory_code, UNSIGNED INTEGER)) + 1 AS inv_code
                FROM `inventory`
                ";
                $resultInvCode = $db->sql_query($SQLInvCode);
                $rowInvCode    = $db->sql_fetchrow($resultInvCode);

                if($rowInvCode['inv_code'] != ""){
                    $inv_code = $rowInvCode['inv_code'];
                }
                else{
                    $inv_code = "1001";
                }

                $fa = array();
                $fa['product_id']     = $PoProd['product_id'];
                $fa['actual_stock']   = $stockUpdate;
                $fa['inventory_code'] = $inv_code;
                $fa['creation_date']  = date('Y-m-d H:i:s');

                $inventory_id = $fn->addRecord($fa, 'inventory');
            } else {
                $SQLUpdateInventory = "
                UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0)+{$stockUpdate}
                WHERE product_id = '{$PoProd['product_id']}'
                ";
                $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
            }

        } else {
            $qty_batchwise        = $poQty - $BatchWiseStock['purchased_qty']; 
            $damage_qty_batchwise = $PoProd['damage_qty'] - $BatchWiseStock['damaged_qty']; 
            $free_qty_batchwise   = $freeQty - $BatchWiseStock['free_qty']; 
            $current_stock        = $BatchWiseStock['current_stock'] + $qty_batchwise + $free_qty_batchwise - $damage_qty_batchwise;
            $stockCalc            = $qty_batchwise + $free_qty_batchwise - $damage_qty_batchwise;

            $faStock = array();
            $faStock['purchased_qty'] = $BatchWiseStock['purchased_qty'] + $qty_batchwise;
            $faStock['damaged_qty']   = $BatchWiseStock['damaged_qty'] + $damage_qty_batchwise;
            $faStock['free_qty']      = $BatchWiseStock['free_qty'] + $free_qty_batchwise;
            $faStock['current_stock'] = $current_stock;
            
            $faStock        = $fn->addModificationDetailsToFieldsArray($faStock, 'inventory_batchwise_stock');
            $whereCondition = "WHERE po_product_id = {$po_product_id}
                               AND product_id = '{$PoProd['product_id']}'";
            $SQLStock    = $dbUtil->getUpdateSQLStringFromArray($faStock, "inventory_batchwise_stock", $whereCondition);
            $resultStock = $db->sql_query($SQLStock);

            $SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0)+{$stockCalc}
            WHERE product_id = '{$PoProd['product_id']}'
            ";
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

            $SQLUpdateInventory = "
            UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0)+{$stockCalc}
            WHERE product_id = '{$PoProd['product_id']}'
            ";
            $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getTradingsgPurchaseOrderTradingsgProductLinkSQL($id) {

        $SQL = "
        SELECT po.po_product_id
              ,p.title AS product_name
              ,po.price
              ,po.qty
              ,po.qty_delivered
              ,po.status
        FROM po_product po
        JOIN product p ON (p.product_id = po.product_id)
        WHERE po.purchase_order_id = {$id}
        ";

        return $SQL;
    }
    /**
     *
     */
    function getExportData($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');


        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Purchaseorder_Export_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cost Price');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Quantity');

        /*if ($cpCfg['cp.hasMultiUniqueSites']){
            $SQLSite = "
            SELECT title
            FROM site
            ";
            $resultSite = $db->sql_query($SQLSite);
            while ($rowSite = $db->sql_fetchrow($resultSite)) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
            }
        }*/

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        //foreach ($dataArray as $row){
            //$colc = 0;
            //$rowc++;

            $SQLProduct = "
                SELECT title
                       ,product_code
                       ,price
                       ,qty_in_stock
                FROM product
                ";
                $resultProduct = $db->sql_query($SQLProduct);
                while ($rowProduct    = $db->sql_fetchrow($resultProduct)) {

                $colc = 0;
                $rowc++;

                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowProduct['product_code']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowProduct['title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc);
                }

            /*if ($cpCfg['cp.hasMultiUniqueSites']){
                $SQLSite = "
                SELECT title
                       ,site_id
                FROM site
                ";
                $resultSite = $db->sql_query($SQLSite);
                while ($rowSite = $db->sql_fetchrow($resultSite)) {
                    $SQLProdPrice = "
                    SELECT price AS prod_price
                    FROM product_price
                    WHERE product_id = {$row['product_id']}
                    AND site_id = {$rowSite['site_id']}
                    ";
                    $resultProdPrice = $db->sql_query($SQLProdPrice);
                    while ($rowProdPrice = $db->sql_fetchrow($resultProdPrice)) {
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowProdPrice['prod_price']);
                    }
                }
            }*/

        //}

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

        $rowc++;

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');


        $fa = array(
             'title'               => $phpExcel->getImportFldObj('Title')
            ,'product_id'          => $phpExcel->getImportFldObj('Product Id')
            ,'item_code'           => $phpExcel->getImportFldObj('Item Code')
            ,'product_name'        => $phpExcel->getImportFldObj('Product Name')
            ,'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
            ,'batch_no'            => $phpExcel->getImportFldObj('Batch No')
            ,'expiry_date'         => $phpExcel->getImportFldObj('Expiry Date')
            ,'stock'               => $phpExcel->getImportFldObj('Stock')
            ,'gst'                 => $phpExcel->getImportFldObj('GST%')
            ,'cost_price'          => $phpExcel->getImportFldObj('Cost Price')
            ,'selling_price'       => $phpExcel->getImportFldObj('Selling Price')
            ,'pack_size'           => $phpExcel->getImportFldObj('Pack Size')
            ,'supplier'            => $phpExcel->getImportFldObj('Supplier')
             
        );

        $fa['product_id']['refOnly']    = true;
        $fa['item_code']['refOnly']     = true;
        $fa['product_name']['refOnly']  = true;
        $fa['batch_no']['refOnly']      = true;
        $fa['expiry_date']['refOnly']   = true;
        $fa['stock']['refOnly']         = true;
        $fa['gst']['refOnly']           = true;
        $fa['cost_price']['refOnly']    = true;
        $fa['selling_price']['refOnly'] = true;
        $fa['pack_size']['refOnly']     = true;
        $fa['supplier']['refOnly']      = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_purchaseOrder'
            ,'matchFieldArr'       => array('purchase_order_date')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback($purchase_order_id, $fa) {
        $cpCfg  = Zend_Registry::get('cpCfg');
        $fn     = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db     = Zend_Registry::get('db');

        $product_id    = $fa['product_id'];
        $item_code     = $fa['item_code'];
        $product_name  = $fa['product_name'];
        $batch_no      = $fa['batch_no'];
        $expiry_date   = $fa['expiry_date'];
        $stock         = $fa['stock'];
        $gst           = $fa['gst'];
        $cost_price    = $fa['cost_price'];
        $selling_price = $fa['selling_price'];
        $pack_size     = $fa['pack_size'];
        $supplier      = $fa['supplier'];

        if($gst == ''){
            $gst = '0.00';
        }

        if($cost_price == ''){
            $cost_price = '0.00';
        }

        if($selling_price == ''){
            $selling_price = '0.00';
        }

        if($stock == ''){
            $stock = '0';
        }

        $SQLSupplierCheck = "
        SELECT supplier_id
        FROM supplier
        WHERE company_name = '{$supplier}'
        ";
        $resultSupplierCheck  = $db->sql_query($SQLSupplierCheck);
        $numRowsSupplierCheck = $db->sql_numrows($resultSupplierCheck);

        if($numRowsSupplierCheck > 0) {
            $rowSupplierCheck = $db->sql_fetchrow($resultSupplierCheck);
            $supplier_id      = $rowSupplierCheck['supplier_id'];
        } else {
            $faSup = array();
            $faSup['company_name']  = $supplier;
            $faSup['status']        = 'Active';
            $faSup['published']     = 1;
            $faSup['creation_date'] = date("Y-m-d H:i:s");
            $faSup['created_by']    = $fn->getSessionParam('userName');
            
            $SQLSupplier    = $dbUtil->getInsertSQLStringFromArray($faSup, 'supplier');
            $resultSupplier = $db->sql_query($SQLSupplier);
            $supplier_id    = $db->sql_nextid();
        }

        $titleSQL = str_replace("'", "\'", $product_name);

        $currentDate = date("Y-m-d");
        $sqlProductCheck = "
        SELECT product_id
        FROM `product`
        WHERE title = '{$titleSQL}'
        AND DATE_FORMAT(creation_date, '%Y-%m-%d') = '{$currentDate}'
        ";
        $resultProductCheck  = $db->sql_query($sqlProductCheck);
        $numRowsProductCheck = $db->sql_numrows($resultProductCheck);
        $rowProductCheck     = $db->sql_fetchrow($resultProductCheck);

        if ($numRowsProductCheck == 0) {
            $fa2 = array();
            $fa2['qty_in_stock']  = $stock;
            $fa2['title']         = $product_name;
            $fa2['price']         = $selling_price;
            $fa2['supplier_id']   = $supplier_id;
            $fa2['gst']           = $gst;
            $fa2['published']     = 1;
            $fa2['item_code']     = $this->getUpdateProductCode();
            $fa2['product_type']  = 'Purchasing and Selling';
            $fa2['creation_date'] = date("Y-m-d H:i:s");
            $fa2['created_by']    = $fn->getSessionParam('userName');

            $SQLProduct    = $dbUtil->getInsertSQLStringFromArray($fa2, 'product');
            $resultProduct = $db->sql_query($SQLProduct);
            $product_id    = $db->sql_nextid();
        } else {
            $product_id  = $rowProductCheck['product_id'];
        }

        $poCode = $this->getUpdatePOCode();

        $SQLUpdateProduct = "
        UPDATE product SET price = '{$selling_price}'
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

        $SQLPOUpdate = "
        UPDATE purchase_order SET po_code = '{$poCode}', status = 'Closed', company_id_supplier = '{$supplier_id}'
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $resultPOUpdate = $db->sql_query($SQLPOUpdate);

        $sqlProductCompCheck = "
        SELECT product_id
        FROM product_company
        WHERE product_id = '{$product_id}'
        ";
        $resultProductCompCheck  = $db->sql_query($sqlProductCompCheck);
        $numRowsProductCompCheck = $db->sql_numrows($resultProductCompCheck);

        if($numRowsProductCompCheck == 0) {
            $fa1 = array();
            $fa1['company_id']    = $supplier_id;
            $fa1['product_id']    = $product_id;
            $fa1['creation_date'] = date('Y-m-d H:i:s');

            $insert  = $dbUtil->getInsertSQLStringFromArray($fa1, 'product_company');
            $result1 = $db->sql_query($insert);
        }

        $fa3 = array();
        $fa3['product_id']        = $product_id;
        $fa3['purchase_order_id'] = $purchase_order_id;
        $fa3['qty']               = $stock;
        $fa3['qty_requested']     = $stock;
        $fa3['gst']               = $gst;
        $fa3['selling_price']     = $selling_price;
        $fa3['cost_price']        = $cost_price;
        $fa3['batch_no']          = $batch_no;
        $fa3['expiry_date']       = $expiry_date;
        $fa3['status']            = 'Closed';
        $fa3['pack_size']         = $pack_size;
        $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

        $SQL           = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
        $result        = $db->sql_query($SQL);
        $po_product_id = $db->sql_nextid();

        $fa4 = array();
        $fa4['po_product_id']     = $po_product_id;
        $fa4['product_id']        = $product_id;
        $fa4['purchase_order_id'] = $purchase_order_id;
        $fa4['qty']               = $stock;
        $fa4['creation_date']     = date("Y-m-d H:i:s");

        $SQLInsert    = $dbUtil->getInsertSQLStringFromArray($fa4, 'stock_history');
        $resultInsert = $db->sql_query($SQLInsert);

        $SQLPoProd = "
        SELECT product_id
              ,qty
              ,pack_size
              ,damage_qty
              ,purchase_order_id
              ,batch_no
              ,po_product_id
              ,free_items
        FROM po_product
        WHERE po_product_id = '{$po_product_id}'
        ";
        $resultPoProd = $db->sql_query($SQLPoProd);
        $PoProd       = $db->sql_fetchrow($resultPoProd);

        $SQLBatchWiseStock = "
        SELECT po_product_id
              ,purchased_qty
              ,damaged_qty
              ,free_qty
              ,current_stock
        FROM inventory_batchwise_stock
        WHERE po_product_id = '{$po_product_id}'
        AND product_id = '{$PoProd['product_id']}'
        ";
        $resultBatchWiseStock  = $db->sql_query($SQLBatchWiseStock);
        $BatchWiseStock        = $db->sql_fetchrow($resultBatchWiseStock);
        $numRowsBatchWiseStock = $db->sql_numrows($resultBatchWiseStock);

        if(is_numeric($PoProd['pack_size'])){
            $poQty = $PoProd['qty'] * $PoProd['pack_size'];
        } else {
            $poQty = $PoProd['qty'];
        }

        if(is_numeric($PoProd['pack_size'])){
            $freeQty = $PoProd['free_items'] * $PoProd['pack_size'];
        } else {
            $freeQty = $PoProd['free_items'];
        }
        
        if($numRowsBatchWiseStock == 0) {
            $stockUpdate = $poQty + $freeQty - $PoProd['damage_qty'];

            $faStock = array();
            $faStock['product_id']        = $PoProd['product_id'];
            $faStock['po_product_id']     = $PoProd['po_product_id'];
            $faStock['purchase_order_id'] = $PoProd['purchase_order_id'];
            $faStock['batch_no']          = $PoProd['batch_no'];
            $faStock['purchased_qty']     = $poQty;
            $faStock['damaged_qty']       = $PoProd['damage_qty'];
            $faStock['free_qty']          = $freeQty;
            $faStock['current_stock']     = $stockUpdate;

            $faStock     = $fn->addCreationDetailsToFieldsArray($faStock, 'inventory_batchwise_stock');
            $SQLStock    = $dbUtil->getInsertSQLStringFromArray($faStock, 'inventory_batchwise_stock');
            $resultStock = $db->sql_query($SQLStock);

            $SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0)+{$stockUpdate}
            WHERE product_id = '{$PoProd['product_id']}'
            ";
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

            $SQLInventory = "
            SELECT product_id
            FROM inventory
            WHERE product_id = {$PoProd['product_id']}
            ";
            $resultInventory  = $db->sql_query($SQLInventory);
            $numRowsInventory = $db->sql_numrows($resultInventory);

            if($numRowsInventory == 0) {
                $SQLInvCode = "
                SELECT MAX(CONVERT(inventory_code, UNSIGNED INTEGER)) + 1 AS inv_code
                FROM `inventory`
                ";
                $resultInvCode = $db->sql_query($SQLInvCode);
                $rowInvCode    = $db->sql_fetchrow($resultInvCode);

                if($rowInvCode['inv_code'] != ""){
                    $inv_code = $rowInvCode['inv_code'];
                }
                else{
                    $inv_code = "1001";
                }

                $fa = array();
                $fa['product_id']     = $PoProd['product_id'];
                $fa['actual_stock']   = $stockUpdate;
                $fa['inventory_code'] = $inv_code;
                $fa['creation_date']  = date('Y-m-d H:i:s');

                $inventory_id = $fn->addRecord($fa, 'inventory');
            } else {
                $SQLUpdateInventory = "
                UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0)+{$stockUpdate}
                WHERE product_id = '{$PoProd['product_id']}'
                ";
                $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
            }
        } else {
            $qty_batchwise        = $poQty - $BatchWiseStock['purchased_qty']; 
            $damage_qty_batchwise = $PoProd['damage_qty'] - $BatchWiseStock['damaged_qty']; 
            $free_qty_batchwise   = $freeQty - $BatchWiseStock['free_qty']; 
            $current_stock        = $BatchWiseStock['current_stock'] + $qty_batchwise + $free_qty_batchwise - $damage_qty_batchwise;
            $stockCalc            = $qty_batchwise + $free_qty_batchwise - $damage_qty_batchwise;

            $faStock = array();
            $faStock['purchased_qty'] = $BatchWiseStock['purchased_qty'] + $qty_batchwise;
            $faStock['damaged_qty']   = $BatchWiseStock['damaged_qty'] + $damage_qty_batchwise;
            $faStock['free_qty']      = $BatchWiseStock['free_qty'] + $free_qty_batchwise;
            $faStock['current_stock'] = $current_stock;
            
            $faStock        = $fn->addModificationDetailsToFieldsArray($faStock, 'inventory_batchwise_stock');
            $whereCondition = "WHERE po_product_id = {$po_product_id}
                               AND product_id = '{$PoProd['product_id']}'";
            $SQLStock    = $dbUtil->getUpdateSQLStringFromArray($faStock, "inventory_batchwise_stock", $whereCondition);
            $resultStock = $db->sql_query($SQLStock);

            $SQLUpdateProduct = "
            UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0)+{$stockCalc}
            WHERE product_id = '{$PoProd['product_id']}'
            ";
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

            $SQLUpdateInventory = "
            UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0)+{$stockCalc}
            WHERE product_id = '{$PoProd['product_id']}'
            ";
            $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
        }
    }

    /**
     *
     */
    function getImportDataBackup(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');


        $fa = array(
              'title'               => $phpExcel->getImportFldObj('Product Name')
             ,'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
             ,'batch_no'            => $phpExcel->getImportFldObj('Batch No')
             ,'expiry_date'         => $phpExcel->getImportFldObj('Expiry Date')
             ,'qty'                 => $phpExcel->getImportFldObj('Qty')
             ,'changed_stock'       => $phpExcel->getImportFldObj('Adjust Stock')
             ,'unit'                => $phpExcel->getImportFldObj('Units')
             ,'gst'                 => $phpExcel->getImportFldObj('GST%')
             ,'selling_price'       => $phpExcel->getImportFldObj('Selling Price')
             ,'cost_price'          => $phpExcel->getImportFldObj('Cost Price')
             ,'company_id_supplier' => $phpExcel->getImportFldObj('Supplier')
             ,'batch_import'        => $phpExcel->getImportFldObj('Import')
             
        );

        $fa['title']['refOnly']         = true;
        $fa['batch_no']['refOnly']      = true;
        $fa['expiry_date']['refOnly']   = true;
        $fa['gst']['refOnly']           = true;
        $fa['selling_price']['refOnly'] = true;
        $fa['cost_price']['refOnly']    = true;
        $fa['qty']['refOnly']           = true;
        $fa['unit']['refOnly']          = true;
        $fa['changed_stock']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_purchaseOrder'
            ,'matchFieldArr'       => array('purchase_order_date', 'batch_import')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallbackBackup($purchase_order_id, $fa) {
        $cpCfg  = Zend_Registry::get('cpCfg');
        $fn     = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db     = Zend_Registry::get('db');

        $gst           = $fa['gst'];
        $selling_price = $fa['selling_price'];
        $cost_price    = $fa['cost_price'];
        $title         = $fa['title'];
        $batch_no      = $fa['batch_no'];
        $qty           = $fa['qty'];
        $unit          = $fa['unit'];
        $expiry_date   = $fa['expiry_date'];
        $changed_stock = $fa['changed_stock'];

        if($gst == ''){
            $gst = '0.00';
        }
        if($cost_price == ''){
            $cost_price = '0.00';
        }
        if($selling_price == ''){
            $selling_price = '0.00';
        }
        if($qty == ''){
            $qty = '0';
        }

        $poRec       = $fn->getRecordRowById('purchase_order', 'purchase_order_id', $purchase_order_id);
        $supplier_id = $poRec['company_id_supplier'];

        $titleSQL = str_replace("'", "\'", $title);
        
        $sqlProductCheck = "
        SELECT product_id
        FROM `product`
        WHERE title = '{$titleSQL}'
        ";
        $resultProductCheck  = $db->sql_query($sqlProductCheck);
        $numRowsProductCheck = $db->sql_numrows($resultProductCheck);
        $rowProductCheck     = $db->sql_fetchrow($resultProductCheck);

        if ($numRowsProductCheck == 0) {
            $fa2 = array();
            $fa2['qty_in_stock'] = $qty;
            $fa2['title']        = $title;
            $fa2['price']        = $selling_price;
            $fa2['supplier_id']  = $supplier_id;
            $fa2['gst']          = $gst;
            $fa2['published']    = 1;
            $fa2['item_code']    = $this->getUpdateProductCode();
            $fa2['product_type'] = 'Purchasing and Selling';
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product');

            $SQLProduct    = $dbUtil->getInsertSQLStringFromArray($fa2, 'product');
            $resultProduct = $db->sql_query($SQLProduct);
            $product_id    = $db->sql_nextid();
        } else {
            $product_id  = $rowProductCheck['product_id'];
        }

        $poCode = $this->getUpdatePOCode();

        $SQLUpdateProduct = "
        UPDATE product SET price = '{$selling_price}'
        WHERE product_id = '{$product_id}'
        ";
        $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

        $SQLPOUpdate = "
        UPDATE purchase_order SET po_code = '{$poCode}', status = 'Closed' 
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $resultPOUpdate = $db->sql_query($SQLPOUpdate);

        $fa1 = array();
        $fa1['company_id']      = $supplier_id;
        $fa1['product_id']      = $product_id;
        $fa1['creation_date']   = date('Y-m-d H:i:s');

        $insert = $dbUtil->getInsertSQLStringFromArray($fa1, 'product_company');
        $result1 = $db->sql_query($insert);

        $fa3 = array();
        $fa3['product_id']        = $product_id;
        $fa3['purchase_order_id'] = $purchase_order_id;
        $fa3['qty']               = $qty;
        $fa3['qty_requested']     = $qty;
        $fa3['gst']               = $gst;
        $fa3['selling_price']     = $selling_price;
        $fa3['cost_price']        = $cost_price;
        $fa3['batch_no']          = $batch_no;
        $fa3['expiry_date']       = $expiry_date;
        $fa3['status']            = 'Closed';
        $fa3['pack_size']         = $unit;
        $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
        $result = $db->sql_query($SQL);
        $po_product_id = $db->sql_nextid();

        $fa4 = array();
        $fa4['po_product_id']     = $po_product_id;
        $fa4['product_id']        = $product_id;
        $fa4['purchase_order_id'] = $purchase_order_id;
        $fa4['qty']               = $qty;
        $fa4['creation_date']     = date("Y-m-d H:i:s");

        $SQLInsert    = $dbUtil->getInsertSQLStringFromArray($fa4, 'stock_history');
        $resultInsert = $db->sql_query($SQLInsert);

        $SQLInventory = "
        SELECT p.product_id
        FROM product p
        LEFT JOIN inventory inv ON inv.product_id = p.product_id
        WHERE inv.product_id IS NULL
        AND p.product_id = {$product_id}
        ";
        $resultInventory = $db->sql_query($SQLInventory);
        while ($rowInventory = $db->sql_fetchrow($resultInventory)) {

            $SQLInvCode = "
            SELECT MAX(CONVERT(inventory_code, UNSIGNED INTEGER)) + 1 AS inv_code
            FROM `inventory`
            ";
            $resultInvCode = $db->sql_query($SQLInvCode);
            $rowInvCode    = $db->sql_fetchrow($resultInvCode);

            if($rowInvCode['inv_code'] != ""){
                $inv_code = $rowInvCode['inv_code'];
            }
            else{
                $inv_code = "1001";
            }

            $faInv = array();

            $faInv['product_id']     = $rowInventory['product_id'];
            $faInv['changed_stock']  = $changed_stock;
            $faInv['creation_date']  = date('Y-m-d H:i:s');
            $faInv['inventory_code'] = $inv_code;

            $inventory_id = $fn->addRecord($faInv, 'inventory');
        }
    }

    /**
     *
     */
    function getImportData1(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        /*
        STOCK - qty_in_stock - qty
        PRODUCT - title
        Item Code - item_code
        Category - category_id
        FC from China - fc_price
        Purchase Cost from BLOSSOMS - price
        Product Weight - product_weight
        VAT% - vat_percentage
        Price per KG - weight_per_kg
        Product Display Price - selling_price
        Add Shipping Cost - logistics
        Comission Calculation Price (Less VAT & Logistics) - agent_price
        TC Comsn % ( 5% - 20%) - commission
        */

        $fa = array(
              'title' => $phpExcel->getImportFldObj('PRODUCT NAME')
             ,'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
             ,'hsn'            => $phpExcel->getImportFldObj('HSN CODE')
             ,'qty'            => $phpExcel->getImportFldObj('Qty')
             ,'unit'           => $phpExcel->getImportFldObj('Units')
             ,'gst'            => $phpExcel->getImportFldObj('GST%')
             ,'selling_price'  => $phpExcel->getImportFldObj('Selling Price')
             ,'cost_price'     => $phpExcel->getImportFldObj('Cost Price')
             ,'category'       => $phpExcel->getImportFldObj('Category')
             ,'supplier'       => $phpExcel->getImportFldObj('Supplier')
             //,'discount'       => $phpExcel->getImportFldObj('Discount%')
             ,'product_weight' => $phpExcel->getImportFldObj('Weight')
             ,'bar_code' => $phpExcel->getImportFldObj('BAR CODE NO')
             
             //,'item_code' => $phpExcel->getImportFldObj('ITEM CODE')
             //,'price'          => $phpExcel->getImportFldObj('COST')
             //,'weight_per_kg'  => $phpExcel->getImportFldObj('WT / KG')
        );

        //$fa['po_code']['defaultValue'] = $this->getUpdatePOCode();
        //$fa['item_code']['refOnly'] = true;
        //$fa['price']['refOnly'] = true;
        //$fa['weight_per_kg']['refOnly'] = true;
        //$fa['supplier']['refOnly'] = true;
        /*$fa['color_size_qty1']['refOnly'] = true;
        $fa['color_size_qty2']['refOnly'] = true;*/

        $fa['title']['refOnly']         = true;
        $fa['gst']['refOnly']           = true;
        $fa['selling_price']['refOnly'] = true;
        $fa['cost_price']['refOnly']    = true;
        $fa['category']['refOnly']      = true;
        $fa['qty']['refOnly']           = true;
        $fa['hsn']['refOnly']           = true;
        $fa['product_weight']['refOnly'] = true;
        $fa['unit']['refOnly']      = true;
        $fa['bar_code']['refOnly']      = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_purchaseOrder'
            ,'matchFieldArr'       => array('purchase_order_date','supplier')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback1($purchase_order_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        //$price = $fa['price'];
        //$item_code = $fa['item_code'];
        //$weight_per_kg = $fa['weight_per_kg'];
        //$supplier = $fa['supplier'];
        //$description = $fa['description'];
        /*$color_size_qty1 = $fa['color_size_qty1'];
        $color_size_qty2 = $fa['color_size_qty2'];*/

        $product_weight = $fa['product_weight'];
        $gst             = $fa['gst'];
        $selling_price   = $fa['selling_price'];
        $cost_price      = $fa['cost_price'];
        $title           = $fa['title'];
        $category        = $fa['category'];
        $qty             = $fa['qty'];
        $hsn             = $fa['hsn'];
        $unit            = $fa['unit'];
        $bar_code        = $fa['bar_code'];
        $color_size_qty1 = '';
        $color_size_qty2 = '';

        if($gst == ''){
            $gst = '0.00';
        }
        if($cost_price == ''){
            $cost_price = '0.00';
        }
        if($selling_price == ''){
            $selling_price = '0.00';
        }
        if($qty == ''){
            $qty = '0';
        }

        $poRec    = $fn->getRecordRowById('purchase_order', 'purchase_order_id', $purchase_order_id);
        $supplier = $poRec['supplier'];

        /*$sqlcount1 = "
        SELECT COUNT(*)
        FROM `section`
        WHERE title = '{$section}'
        ";
        $resultcount1 = $db->sql_query($sqlcount1);
        $secRecCount    = $db->sql_fetchrow($resultcount1);

        if ($secRecCount == 0 && $section != '') {
            $fa1 = array();
            $fa1['title'] = $section;
            $fa1['published'] = 1;
            $fa1['section_type'] = 'Product';

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'section');
            $result = $db->sql_query($SQL);
            $section_id  = $db->sql_nextid();
        } else {
            $sqlsec = "
            SELECT section_id
                   ,title FROM section
                   WHERE title = '{$section}'
            ";
            $resultsec = $db->sql_query($sqlsec);
            $secRec    = $db->sql_fetchrow($resultsec);

            $section_id  = $secRec['section_id'];
        }*/

        $sqlcount3 = "
        SELECT title
        FROM `category`
        WHERE title = '{$category}'
        ";
        $resultcount3 = $db->sql_query($sqlcount3);
        $catRecCount    = $db->sql_fetchrow($resultcount3);

        if ($catRecCount['title'] == '' && $category != '') {
            $fa1 = array();
            $fa1['title']         = $category;
            $fa1['published']     = 1;
            $fa1['section_id']    = 13;
            $fa1['category_type'] = 'Product';

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'category');
            $result = $db->sql_query($SQL);
            $category_id  = $db->sql_nextid();
        } else {
            $sqlcat = "
            SELECT category_id
                   ,title FROM category
                   WHERE title = '{$category}'
            ";
            $resultcat = $db->sql_query($sqlcat);
            $catRec    = $db->sql_fetchrow($resultcat);

            $category_id  = $catRec['category_id'];
        }

        $sqlcount2 = "
        SELECT COUNT(*) AS supplier_count
        FROM `supplier`
        WHERE company_name = '{$supplier}'
        ";
        $resultcount2 = $db->sql_query($sqlcount2);
        $supRecCount    = $db->sql_fetchrow($resultcount2);

        if ($supRecCount['supplier_count'] == 0 && $supplier != '') {
            $fa1 = array();
            $fa1['company_name'] = $supplier;

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'supplier');
            $result = $db->sql_query($SQL);
            $supplier_id  = $db->sql_nextid();
        } else {
            $sqlsup = "
            SELECT supplier_id
                   ,company_name FROM supplier
                   WHERE company_name = '{$supplier}'
            ";
            $resultsup = $db->sql_query($sqlsup);
            $supRec    = $db->sql_fetchrow($resultsup);

            $supplier_id  = $supRec['supplier_id'];
        }

        //$fa2['product_code'] = $image_code;
        //$fa2['weight_per_kg']  = $weight_per_kg;
        //$fa2['description']  = $description;
        //$fa2['actual_price']  = $actual_price;
        $fa2 = array();
        $fa2['qty_in_stock'] = $qty;
        $fa2['product_weight']  = $product_weight;
        $fa2['title']        = $title;
        $fa2['price']        = $selling_price;
        $fa2['category_id']  = $category_id;
        $fa2['supplier_id']  = $supplier_id;
        $fa2['gst']          = $gst;
        $fa2['published']    = 1;
        $fa2['hsn']          = $hsn;
        $fa2['unit']         = $unit;
        $fa2['bar_code']     = $bar_code;
        $fa2['item_code']    = $this->getUpdateProductCode();
        $fa2['product_type'] = 'Purchasing and Selling';
        if($color_size_qty1 != ''){
            $fa2['color']    = 1;
            $fa2['product_size']  = 1;
            $fa2['model']    = 1;
        }

        //$fa2['discount_type']       = '%'; 
        //$fa2['discount_percentage'] = $discount;
        $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'product');
        $result = $db->sql_query($SQL);
        $product_id  = $db->sql_nextid();

        $poCode = $this->getUpdatePOCode();

        $SQLPOUpdate = "UPDATE purchase_order set company_id_supplier = {$supplier_id}, po_code = '{$poCode}' WHERE purchase_order_id = {$purchase_order_id}";
        $resultPOUpdate = $db->sql_query($SQLPOUpdate);

        $fa1 = array();
        $fa1['company_id']      = $supplier_id;
        $fa1['product_id']      = $product_id;
        $fa1['creation_date']   = date('Y-m-d H:i:s');

        $insert = $dbUtil->getInsertSQLStringFromArray($fa1, 'product_company');
        $result1 = $db->sql_query($insert);

        if($color_size_qty1 != ''){
            $this->getCreateColorAndSizeRecords($color_size_qty1, $product_id);
        }
        if($color_size_qty2 != ''){
            $this->getCreateColorAndSizeRecords($color_size_qty2, $product_id);
        }

        $SQLpcs = "
        SELECT *
        FROM product_color_by_size
        WHERE product_id = {$product_id}
        ";
        $resultpcs = $db->sql_query($SQLpcs);
        $numRowspcs = $db->sql_numrows($resultpcs);

        $SQLpc = "
        SELECT *
        FROM product_color
        WHERE product_id = {$product_id}
        ";
        $resultpc = $db->sql_query($SQLpc);
        $numRowspc = $db->sql_numrows($resultpc);

        $SQLps = "
        SELECT *
        FROM product_size
        WHERE product_id = {$product_id}
        ";
        $resultps = $db->sql_query($SQLps);
        $numRowsps = $db->sql_numrows($resultps);

        $SQLpm = "
        SELECT *
        FROM product_model
        WHERE product_id = {$product_id}
        ";
        $resultpm = $db->sql_query($SQLpm);
        $numRowspm = $db->sql_numrows($resultpm);

        while ($rowpm = $db->sql_fetchrow($resultpm)) {
            //$fa3['price']  = $price;
            //$fa3['product_weight']  = $product_weight;
            //$fa3['weight_per_kg']  = $weight_per_kg;
            $fa3 = array();
            $fa3['product_id']          = $product_id;
            $fa3['purchase_order_id']   = $purchase_order_id;
            $fa3['qty']                 = $rowpm['qty'];
            $fa3['qty_requested']       = $rowpm['qty'];
            $fa3['gst']                 = $gst;
            $fa3['logistics']           = $logistics;
            $fa3['selling_price']       = $selling_price;
            $fa3['agent_price']         = $agent_price;
            $fa3['commission']          = $commission;
            $fa3['color_size_code']     = $rowpm['code'];
            //$fa3['discount_type']       = '%'; 
            //$fa3['discount_percentage'] = $discount;
            $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
            $result = $db->sql_query($SQL);
        }

        if($numRowspcs > 0){
            while ($rowpcs = $db->sql_fetchrow($resultpcs)) {
                //$fa3['price']  = $price;
                //$fa3['product_weight']  = $product_weight;
                //$fa3['weight_per_kg']  = $weight_per_kg;
                $fa3 = array();
                $fa3['product_id']          = $product_id;
                $fa3['purchase_order_id']   = $purchase_order_id;
                $fa3['qty']                 = $rowpcs['qty'];
                $fa3['qty_requested']       = $rowpcs['qty'];
                $fa3['gst']                 = $gst;
                $fa3['logistics']           = $logistics;
                $fa3['selling_price']       = $selling_price;
                $fa3['agent_price']         = $agent_price;
                $fa3['commission']          = $commission;
                $fa3['color_size_code']     = $rowpcs['code'];
                //$fa3['discount_type']       = '%'; 
                //$fa3['discount_percentage'] = $discount;
                $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
                $result = $db->sql_query($SQL);
            }
        } else if($numRowspc > 0 || $numRowsps > 0){
            while ($rowpc = $db->sql_fetchrow($resultpc)) {
                //$fa3['price']  = $price;
                //$fa3['product_weight']  = $product_weight;
                //$fa3['weight_per_kg']  = $weight_per_kg;
                $fa3 = array();
                $fa3['product_id']          = $product_id;
                $fa3['purchase_order_id']   = $purchase_order_id;
                $fa3['qty']                 = $rowpc['qty'];
                $fa3['qty_requested']       = $rowpc['qty'];
                $fa3['gst']                 = $gst;
                $fa3['logistics']           = $logistics;
                $fa3['selling_price']       = $selling_price;
                $fa3['agent_price']         = $agent_price;
                $fa3['commission']          = $commission;
                $fa3['color_size_code']     = $rowpc['code'];
                //$fa3['discount_type']       = '%'; 
                //$fa3['discount_percentage'] = $discount;
                $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
                $result = $db->sql_query($SQL);
            }

            while ($rowps = $db->sql_fetchrow($resultps)) {
                //$fa3['price']  = $price;
                //$fa3['product_weight']  = $product_weight;
                //$fa3['weight_per_kg']  = $weight_per_kg;
                $fa3 = array();
                $fa3['product_id']          = $product_id;
                $fa3['purchase_order_id']   = $purchase_order_id;
                $fa3['qty']                 = $rowps['qty'];
                $fa3['qty_requested']       = $rowps['qty'];
                $fa3['gst']                 = $gst;
                $fa3['logistics']           = $logistics;
                $fa3['selling_price']       = $selling_price;
                $fa3['agent_price']         = $agent_price;
                $fa3['commission']          = $commission;
                $fa3['color_size_code']     = $rowps['code'];
                //$fa3['discount_type']       = '%'; 
                //$fa3['discount_percentage'] = $discount;
                $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
                $result = $db->sql_query($SQL);
            }

        } else {
            //$fa3['price']  = $price;
            //$fa3['weight_per_kg']  = $weight_per_kg;
            $fa3 = array();
            $fa3['product_id']          = $product_id;
            $fa3['purchase_order_id']   = $purchase_order_id;
            $fa3['qty']                 = $qty;
            $fa3['qty_requested']       = $qty;
            $fa3['gst']                 = $gst;
            $fa3['selling_price']       = $selling_price;
            $fa3['cost_price']          = $cost_price;
            $fa3['product_weight']  = $product_weight;
            //$fa3['discount_type']       = '%'; 
            //$fa3['discount_percentage'] = $discount;
            $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
            $result = $db->sql_query($SQL);
        }

            /*$invRec = $fn->getRecordByCondition('inventory', "product_id = '{$product_id}'");
            $fa4 = array();
            $fa4['product_id'] = $product_id;
            $fa4['actual_stock'] = $invRec['actual_stock'] + $qty;
            $fa4 = $fn->addCreationDetailsToFieldsArray($fa4, 'inventory');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa4, 'inventory');
            $result = $db->sql_query($SQL);*/
    }

    /**
     *
     */
    function getCreateColorAndSizeRecords($color_size_qty, $product_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $csq_arr = explode('-', $color_size_qty);
        $color = $csq_arr[0];
        $size = $csq_arr[1];
        $model = $csq_arr[2];
        $csqty = $csq_arr[3];

        $colorVal = ltrim($color,"C");
        $sizeVal = ltrim($size,"S");
        $modelVal = ltrim($model,"M");
        $csqtyVal = ltrim($csqty,"Q");

        if($colorVal != ''){
            $fa = array();
            $fa['product_id'] = $product_id;
            $fa['color'] = $colorVal;
            $fa['qty']  = $csqtyVal;
            $fa['code']  = $this->getUpdateColorCode();
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color');
            $result = $db->sql_query($SQL);
            $product_color_id = $db->sql_nextid();
        }

        if($sizeVal != ''){
            $fa = array();
            $fa['product_id'] = $product_id;
            $fa['size'] = $sizeVal;
            $fa['qty']  = $csqtyVal;
            $fa['code']  = $this->getUpdateSizeCode();
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_size');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_size');
            $result = $db->sql_query($SQL);
            $product_size_id = $db->sql_nextid();
        }

        if($modelVal != ''){
            $fa = array();
            $fa['product_id'] = $product_id;
            $fa['model'] = $modelVal;
            $fa['qty']  = $csqtyVal;
            $fa['code']  = $this->getUpdateModelCode();
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_model');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_model');
            $result = $db->sql_query($SQL);
            $product_model_id = $db->sql_nextid();
        }

        if($colorVal != '' && $sizeVal != ''){
            $fa = array();
            $fa['product_id'] = $product_id;
            $fa['product_color_id'] = $product_color_id;
            $fa['product_size_id']  = $product_size_id;
            $fa['qty']  = $csqtyVal;
            $fa['code']  = $this->getUpdateColorSizeCode();
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color_by_size');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color_by_size');
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getImportDataUpdate(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        /*
        STOCK - qty_in_stock - qty
        PRODUCT - title
        Item Code - item_code
        Category - category_id
        FC from China - fc_price
        Purchase Cost from BLOSSOMS - price
        Product Weight - product_weight
        VAT% - vat_percentage
        Price per KG - weight_per_kg
        Product Display Price - selling_price
        Add Shipping Cost - logistics
        Comission Calculation Price (Less VAT & Logistics) - agent_price
        TC Comsn % ( 5% - 20%) - commission
        */

        $fa = array(
              'title' => $phpExcel->getImportFldObj('PRODUCT')
             ,'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
             ,'item_code' => $phpExcel->getImportFldObj('ITEM CODE')
             ,'inventory_code' => $phpExcel->getImportFldObj('Inventory Code')
             ,'inventory_id' => $phpExcel->getImportFldObj('Inventory Id')
             ,'code' => $phpExcel->getImportFldObj('Code')
             ,'color' => $phpExcel->getImportFldObj('Color')
             ,'size'  => $phpExcel->getImportFldObj('Size')
             ,'model'  => $phpExcel->getImportFldObj('Model')
             ,'stock' => $phpExcel->getImportFldObj('Stock')
             ,'product_id' => $phpExcel->getImportFldObj('Product Id')
        );

        $fa['po_code']['defaultValue'] = $this->getUpdatePOCode();
        $fa['title']['refOnly'] = true;
        $fa['item_code']['refOnly'] = true;
        $fa['inventory_code']['refOnly'] = true;
        $fa['inventory_id']['refOnly'] = true;
        $fa['color']['refOnly'] = true;
        $fa['size']['refOnly'] = true;
        $fa['model']['refOnly'] = true;
        $fa['stock']['refOnly'] = true;
        $fa['product_id']['refOnly'] = true;
        $fa['code']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_purchaseOrder'
            ,'matchFieldArr'       => array('purchase_order_date')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallbackForStock'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallbackForStock($purchase_order_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $stock = $fa['stock'];
        $item_code = $fa['item_code'];
        $title = $fa['title'];
        $inventory_code = $fa['inventory_code'];
        $code = $fa['code'];
        $color = $fa['color'];
        $size = $fa['size'];
        $model = $fa['model'];
        $product_id = $fa['product_id'];
        $inventory_id = $fa['inventory_id'];

        //$productRec  = $fn->getRecordRowByID('product', 'product_id', $product_id);


        //if (is_numeric ($product_id) && $recCount == 0) {
            $fa3 = array();
            $fa3['product_id'] = $product_id;
            $fa3['inventory_id'] = $inventory_id;
            $fa3['purchase_order_id']  = $purchase_order_id;
            $fa3['qty']  = $stock;
            $fa3['qty_requested']  = $stock;
            $fa3['color_size_code']  = $code;
            $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
            $result = $db->sql_query($SQL);
        //}

        if($color != ''){
            $productColorChk = $fn->getRecordByCondition('product_color', "color = '{$color}' AND product_id = '{$product_id}'");
            if($productColorChk){
                //$productColorRec = $fn->getRecordByCondition('product_color', "code = '{$code}'");
                $qty = $productColorChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_color SET qty = {$qty}
                WHERE product_color_id = {$productColorChk['product_color_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_color_id = $productColorChk['product_color_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['color'] = $color;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color');
                $result = $db->sql_query($SQL);
                $product_color_id = $db->sql_nextid();
            }
        }

        if($size != ''){
            $productSizeChk = $fn->getRecordByCondition('product_size', "size = '{$size}' AND product_id = '{$product_id}'");
            if($productSizeChk){
                //$productSizeRec = $fn->getRecordByCondition('product_size', "code = '{$code}'");
                $qty = $productSizeChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_size SET qty = {$qty}
                WHERE product_size_id = {$productSizeChk['product_size_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_size_id = $productSizeChk['product_size_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['size'] = $size;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_size');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_size');
                $result = $db->sql_query($SQL);
                $product_size_id = $db->sql_nextid();
            }

        }

        if($model != ''){
            $productModelChk = $fn->getRecordByCondition('product_model', "model = '{$model}' AND product_id = '{$product_id}'");
            if($productModelChk){
                $qty = $productModelChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_model SET qty = {$qty}
                WHERE product_model_id = {$productModelChk['product_model_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_model_id = $productModelChk['product_model_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['model'] = $model;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_model');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_model');
                $result = $db->sql_query($SQL);
                $product_model_id = $db->sql_nextid();
            }

        }

        if($color != '' && $size != ''){
            $productColorSizeChk = $fn->getRecordByCondition('product_color_by_size', "product_color_id = '{$product_color_id}' AND product_size_id = '{$product_size_id}' AND product_id = '{$product_id}'");
            if($productColorSizeChk){
                //$productColorSizeRec = $fn->getRecordByCondition('product_color_by_size', "code = '{$code}'");
                $qty = $productColorSizeChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_color_by_size SET qty = {$qty}
                WHERE product_color_by_size_id = {$productColorSizeChk['product_color_by_size_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['product_color_id'] = $product_color_id;
                $fa['product_size_id']  = $product_size_id;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color_by_size');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color_by_size');
                $result = $db->sql_query($SQL);
            }
        }
            /*$invRec = $fn->getRecordByCondition('inventory', "product_id = '{$product_id}'");
            $fa4 = array();
            $fa4['product_id'] = $product_id;
            $fa4['actual_stock'] = $invRec['actual_stock'] + $qty;
            $fa4 = $fn->addCreationDetailsToFieldsArray($fa4, 'inventory');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa4, 'inventory');
            $result = $db->sql_query($SQL);*/
    }

    /**
     *
     */
    function getUpdateColorCode() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT code
        FROM product_color
        ORDER BY product_color_id DESC
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $count = 1;

        $color_code = ltrim($row['code'],"C");
        $colorCode = 'C'.$color_code + 1;

        return $colorCode;
    }

    /**
     *
     */
    function getUpdateSizeCode() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT code
        FROM product_size
        ORDER BY product_size_id DESC
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $count = 1;

        $size_code = ltrim($row['code'],"S");
        $sizeCode = 'S'.$size_code + 1;

        return $sizeCode;
    }

    /**
     *
     */
    function getUpdateModelCode() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT code
        FROM product_model
        ORDER BY product_model_id DESC
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $count = 1;

        $model_code = ltrim($row['code'],"M");
        $modelCode = 'M'.$model_code + 1;

        return $modelCode;
    }

    /**
     *
     */
    function getUpdateColorSizeCode() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT code
        FROM product_color_by_size
        ORDER BY product_color_by_size_id DESC
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $count = 1;

        $size_code = ltrim($row['code'],"CS");
        $sizeCode = 'CS'.$size_code + 1;

        return $sizeCode;
    }

    /**
     *
     */
    function getUpdatePOCodeOld() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */

        $poCode = $fn->getSettingsValueByKey("nextPurchaseOrderCode");

        $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPurchaseOrderCode'";
        $result = $db->sql_query($SQL);

        return $POCode;
    }

    /**
     *
     */
    function getUpdatePOCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        /* Updation of Purchase order Code */
        $site_id = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$site_id}";
        }

        /*$SQLSite = "
        SELECT title
        FROM site
        WHERE site_id = '{$site_id}'
        ";
        $resultSite = $db->sql_query($SQLSite);
        $rowSite    = $db->sql_fetchrow($resultSite);
        $siteArr    = explode(" ", $rowSite['title']);
        $sitePrefix = "";
        foreach($siteArr as $site) {
           $sitePrefix .= substr($site,0,1);
        }*/

        $SQLPoCode = "
        SELECT MAX(CONVERT(po_code, UNSIGNED INTEGER)) AS po_code
        FROM `purchase_order`
        WHERE status != 'Cancelled'
        {$appendSql}
        ";
        $resultPoCode = $db->sql_query($SQLPoCode);
        $rowPoCode    = $db->sql_fetchrow($resultPoCode);
        $po_code      = $rowPoCode['po_code'] + 1;

        if($rowPoCode['po_code'] == "" || $rowPoCode['po_code'] == 0) {
            $po_code = "1000";
        }
        else {
            $po_code = $po_code;
        }

        return $po_code;
    }

    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductItemCode");

        if($nextProductItemCode < 10){
            $ProCode = '000' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 99){
            $ProCode = '00' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 999){
            $ProCode = '0' . $nextProductItemCode;
        }
        else{
            $ProCode = $nextProductItemCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
    }
    /**
     *
     */

    function getPrintPOtoPDF() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $SQL = "
        SELECT p.*
               ,pop.qty as po_QTY
               ,pop.qty_delivered
               ,(pop.qty - pop.qty_delivered) AS qty_balance
               ,pop.status
               ,pop.price AS cost_price
               ,po.purchase_order_date
               ,po.po_code
               ,po.purchase_order_id
               ,m.address_flat
               ,m.address_street
               ,m.address_town
               ,m.address_state
               ,m.address_country
               ,m.company_name
        FROM purchase_order po
        LEFT JOIN po_product pop ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN product p ON p.product_id = pop.product_id
        LEFT JOIN supplier m ON m.supplier_id = po.company_id_supplier
        WHERE po.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        $purchase_order_date = $fn->getCPDate($company['purchase_order_date'], 'd/m/Y');

        $po_code = '';
        if($company['po_code'] != ''){
            $po_code = $company['po_code'];
        }

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;font-family:andalusb;">PURCHASE ORDER</td>
            </tr>
        </table>
        <table border="1" width="100%" style="font-size:15px;">
            <tr>
                <td width="50%" style="text-align:left;">
                    <span>PO Code : '.$po_code.'</span>
                </td>
                <td width="50%" style="text-align:right;">
                    <span>DATE : '.$purchase_order_date.'</span>
                </td>
            </tr>
        </table>
        ';

        $address_flat    = $company['address_flat'];
        $address_street  = $company['address_street'];
        $address_town    = $company['address_town'];
        $address_state   = $company['address_state'];
        $address_country = $company['address_country'];

        $recCountry['name'] = '';
        if($address_country != ''){
            $recCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$address_country}'");
        }

        $tbl2 ='<table border="1" width="100%" cellpadding="6" style="font-size:15px;">
                    <tr>
                        <td width="100%" style="line-height:20px;"><br/>
                            <span><b>TO:</b> '.strtoupper($company['company_name']).'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_flat.' '.$address_street.'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_town.' - '.$address_state.'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$recCountry['name'].'</span>
                        </td>
                    </tr>
                </table>
                ';

        $tbl3 ='<table border="1" width="100%" cellpadding="3" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="10%">S.NO</th>
                            <th width="80%" style="text-align:center;">PRODUCT NAME</th>
                            <th width="10%" style="text-align:center;">QTY</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $count = 1;
        while($row = $db->sql_fetchrow($result)){

            $SQLproduct    = "
            SELECT  po.product_id
                   ,po.price
            FROM  po_product po
            WHERE po.product_id = {$row['product_id']}
            AND po.purchase_order_id < {$row['purchase_order_id']}
            ORDER BY po.product_id DESC
            LIMIT 0,1
            ";

            $resultproduct = $db->sql_query($SQLproduct);
            $rowproduct    = $db->sql_fetchrow($resultproduct);

            $tbl3 = $tbl3.'<tr>
                                <td width="10%">'.$count.'</td>
                                <td width="80%">'.$row['title'].'</td>
                                <td width="10%" style="text-align:right;">'.$row['po_QTY'].'</td>
                            </tr>
                           ';

            $count++;
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['po_code'].'-Purchase-Order'.date('Y-m-d').'.pdf';
        $pdf->Output($download_title, 'I');
    }
    /**
     *
     */

    function getPrintPOwithpricetoPDF() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $SQL = "
        SELECT p.*
               ,pop.qty as po_QTY
               ,pop.qty_delivered
               ,(pop.qty - pop.qty_delivered) AS qty_balance
               ,pop.status
               ,pop.cost_price AS cost_price
               ,(pop.qty*pop.cost_price) AS total_price
               ,po.purchase_order_date
               ,po.po_code
               ,po.purchase_order_id
               ,m.company_name AS supplier_name
               ,m.address_flat
               ,m.address_street
               ,m.address_town
               ,m.address_state
               ,m.address_country
        FROM purchase_order po
        LEFT JOIN po_product pop ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN product p ON p.product_id = pop.product_id
        LEFT JOIN supplier m ON m.supplier_id = po.company_id_supplier
        WHERE po.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        $purchase_order_date = $fn->getCPDate($company['purchase_order_date'], 'd/m/Y');
        $po_code = '';
        if($company['po_code'] != ''){
            $po_code = $company['po_code'];
        }

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;font-family:andalusb;">PURCHASE ORDER</td>
            </tr>
        </table>
        <table border="1" width="100%" style="font-size:15px;">
            <tr>
                <td width="50%" style="text-align:left;">
                    <span>PO Code : '.$po_code.'</span>
                </td>
                <td width="50%" style="text-align:right;">
                    <span>DATE : '.$purchase_order_date.'</span>
                </td>
            </tr>
        </table>
        ';

        $address_flat        = $company['address_flat'];
        $address_street        = $company['address_street'];
        $address_town    = $company['address_town'];
        $address_state   = $company['address_state'];
        $address_country = $company['address_country'];

        $recCountry['name'] = '';
        if($address_country != ''){
            $recCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$address_country}'");
        }

        $tbl2 ='<table border="1" width="100%" cellpadding="6" style="font-size:15px;">
                    <tr>
                        <td width="100%" style="line-height:20px;"><br/>
                            <span><b>TO:</b> '.strtoupper($company['supplier_name']).'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_flat.' '.$address_street.'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_town.' - '.$address_state.'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$recCountry['name'].'</span>
                        </td>
                    </tr>
                </table>
                ';

        $tbl3 ='<table border="1" width="100%" cellpadding="4" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="10%">S.NO</th>
                            <th width="40%" style="text-align:center;">PRODUCT NAME</th>
                            <th width="10%" style="text-align:center;">QTY</th>
                            <th width="20%" style="text-align:center;">COST PRICE</th>
                            <th width="20%" style="text-align:center;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $count = 1;
        $overallTotal = 0;
        while($row = $db->sql_fetchrow($result)){

            $SQLproduct    = "
            SELECT  po.product_id
                   ,po.price
            FROM  po_product po
            WHERE po.product_id = {$row['product_id']}
            AND po.purchase_order_id < {$row['purchase_order_id']}
            ORDER BY po.product_id DESC
            LIMIT 0,1
            ";

            $resultproduct = $db->sql_query($SQLproduct);
            $rowproduct    = $db->sql_fetchrow($resultproduct);

            $tbl3 = $tbl3.'<tr>
                                <td width="10%">'.$count.'</td>
                                <td width="40%">'.$row['title'].'</td>
                                <td width="10%" style="text-align:center;">'.$row['po_QTY'].'</td>
                                <td width="20%" style="text-align:right;">'.$row['cost_price'].'</td>
                                <td width="20%" style="text-align:right;">'.number_format($row['total_price'], 2).'</td>
                            </tr>
                           ';

            $overallTotal += $row['total_price'];

            $count++;
        }

        $tbl3 = $tbl3.'<tr>
                            <td colspan="4" style="text-align:right;">TOTAL AMOUNT</td>
                            <td style="text-align:right;">'.number_format($overallTotal, 2).'</td>
                        </tr>
                        ';

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['po_code'].'-Purchase-Order'.date('Y-m-d').'.pdf';
        $pdf->Output($download_title, 'I');
    }


    /**
     *
     */
    function getPrintPOtoExcel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $purchase_order_id  = $fn->getReqParam('purchase_order_id');
        $cpSiteIdSession    = $fn->getSessionParam('cp_site_id');
        $template = 'PO-Product-Export.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Purchase-Order_' . $purchase_order_id . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT p.*
               ,pop.qty AS po_QTY
               ,pop.qty AS qty_delivered
               ,(pop.qty_requested - pop.qty) AS qty_balance
               ,pop.status
               ,pop.cost_price AS cost_price
               ,po.purchase_order_date
               ,po.po_code
               ,po.purchase_order_id
               ,m.company_name AS supplier_name
               ,m.address1
               ,m.address2
               ,m.address_town
               ,m.address_state
               ,m.address_country
        FROM purchase_order po
        LEFT JOIN po_product pop ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN product p ON p.product_id = pop.product_id
        LEFT JOIN supplier m ON m.supplier_id = po.company_id_supplier
        WHERE po.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();
        $blkProduct     = array();
        $blkQty         = array();
        $qty_delivered  = array();
        $blkSerialNo    = array();
        $qty_balance    = array();
        $blkItemCode    = array();
        $blkLastQPrice  = array();
        $blkcost_price  = array();
        $blktotal_price = array();
        $selling_price  = 0;
        $overallTotal   = 0;
        while ($row = $db->sql_fetchrow($result)) {
            //repeating rows of product values
            $arr1 = array('product_title' => $row['title']);
            $blkProduct[] = $arr1;

            /*$address1        = '';
            $address2        = '';
            $address_town    = '';
            $address_state   = '';
            $address_country = '';*/

            $address1        = $row['address1'];
            $address2        = $row['address2'];
            $address_town    = $row['address_town'];
            $address_state   = $row['address_state'];
            $address_country = $row['address_country'];


            $arr2 = array('qty' => $row['po_QTY']);
            $blkQty[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr4 = array('qty_delivered' => $row['qty_delivered']);
            $qty_delivered[] = $arr4;

            $arr5 = array('qty_balance' => $row['qty_balance']);
            $qty_balance[] = $arr5;

            $arr6 = array('item_code' => $row['item_code']);
            $blkItemCode[] = $arr6;

            $SQLproduct    = "
            SELECT  po.product_id
                   ,po.price
            FROM  po_product po
            WHERE po.product_id = {$row['product_id']}
            AND po.purchase_order_id < {$row['purchase_order_id']}
            ORDER BY po.product_id DESC
            LIMIT 0,1
            ";

            $resultproduct = $db->sql_query($SQLproduct);
            $rowproduct    = $db->sql_fetchrow($resultproduct);

            $arr7 = array('last_price' => $rowproduct['price']);
            $blkLastQPrice[] = $arr7;

            $arr8 = array('cost_price' => $row['cost_price']);
            $blkcost_price[] = $arr8;

            $arr9 = array('total_price' => number_format(($row['po_QTY'] * $row['cost_price']), 2));
            $blktotal_price[] = $arr9;

            $overallTotal += $row['po_QTY'] * $row['cost_price'];

            $po_date   = $fn->getCPDate($row['purchase_order_date'], 'd-m-Y');

            $recCountry['name'] = '';
            if($address_country != ''){
                $recCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$address_country}'");
            }

            $arr['po_code']         = $row['po_code'];
            $arr['supplier_name']   = $row['supplier_name'];
            $arr['address_flat']    = $address1;
            $arr['address_street']  = $address2;
            $arr['address_town']    = $address_town;
            $arr['address_state']   = $address_state;
            $arr['address_country'] = $recCountry['name'];
            $arr['po_date']         = $po_date;
            $blkMain[] = $arr;

            $serialNo++;
        }

        $arr10 = array('overall_Total' => number_format($overallTotal, 2));
        $blkoverallTotal[] = $arr10;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkProduct', $blkProduct);
        $TBS->MergeBlock('blkQty', $blkQty);
        $TBS->MergeBlock('blkcost_price', $blkcost_price);
        $TBS->MergeBlock('qty_delivered', $qty_delivered);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->MergeBlock('qty_balance', $qty_balance);
        $TBS->MergeBlock('blkItemCode', $blkItemCode);
        $TBS->MergeBlock('blkLastQPrice', $blkLastQPrice);
        $TBS->MergeBlock('blktotal_price', $blktotal_price);
        $TBS->MergeBlock('blkoverallTotal', $blkoverallTotal);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
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
        $supplier_id     = $fn->getReqParam('supplier_id');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];
        $suppCondition = '';

        if($supplier_id != ''){
            $suppCondition = "AND pc.company_id = {$supplier_id}";
        }

        /*$SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,i.inventory_code
              ,s.company_name
              ,s.supplier_id
              ,p.item_code
              ,p.price
              ,p.gst
              ,p.tag_no
              ,p.product_code
              ,(SELECT pp.cost_price FROM po_product pp
                WHERE pp.product_id = i.product_id
                ORDER BY pp.po_product_id desc
                LIMIT 0,1
                ) AS cost_price
              ,p.product_id AS id
              ,CONCAT_WS(' :: ', CONCAT('PROD-',p.item_code), p.title
              ,CONCAT('',p.product_weight, p.unit)
              ,p.price
              ,p.qty_in_stock
              ) AS label
        FROM inventory i
        LEFT JOIN product p ON(p.product_id = i.product_id)
        LEFT JOIN product_company pc ON(pc.product_id = p.product_id)
        LEFT JOIN supplier s ON(s.supplier_id = pc.company_id)
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%')
        AND p.published = 1
        {$suppCondition}
        ORDER BY p.title
        ";*/
        //AND (p.product_type != 'Selling Product' OR p.product_type IS NULL)

        /*$SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' ** ', p.item_code, p.title, p.price, p.stock) AS label
        FROM product p
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%')
        AND p.published = 1
        ORDER BY p.title
        ";*/

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title) AS label
              ,p.medicine_company_id AS medicine_company
        FROM product p
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
    function getSearchMOLProductList() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $formObj = Zend_Registry::get('formObj');

        $arr = array();
        $rows = '';

        $SQL = "
        SELECT p.title
              ,i.inventory_code
              ,i.actual_stock
              ,s.company_name
              ,s.supplier_id
              ,p.item_code
              ,p.price
              ,p.gst
              ,(SELECT pp.cost_price FROM po_product pp
                WHERE pp.product_id = i.product_id
                ORDER BY pp.po_product_id desc
                LIMIT 0,1
                ) AS cost_price
              ,p.product_id AS id
        FROM inventory i
        LEFT JOIN product p ON(p.product_id = i.product_id)
        LEFT JOIN product_company pc ON(pc.product_id = p.product_id)
        LEFT JOIN supplier s ON(s.supplier_id = pc.company_id)
        WHERE i.minimum_order_level < 50000
          AND i.minimum_order_level IS NOT NULL
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        /*$dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;*/

        while ($row = $db->sql_fetchrow($result)) {
            $arr['title'] = $row['title'];
            $arr['id']    = $row['id'];
            $arr['price'] = $row['price'];
            $arr['cost_price'] = $row['cost_price'];
            $arr['gst']   = $row['gst'];
            $arr['inventory_code'] = $row['inventory_code'];
            $arr['item_code'] = $row['item_code'];

            $product = "
            <input type='text' value='{$row['title']}' id='poProduct' class='text poProductTitle' name='product_title_list[]'>
            <input type='hidden' name='product_id[]' class='product_id_hidden' value='{$row['id']}'>
            ";
            $price = "<input type='text' value='{$row['price']}' id='price' class='text lineItemDescription' name='price[]' disabled>";
            $costPrice = "<input type='text' value='{$row['cost_price']}' id='costPrice' class='text poCostPrice' name='cost_price[]' disabled>";
            $gst = "<input type='text' value='{$row['gst']}' id='gst' class='text poGst' name='gst[]' disabled>";
            $qty = "<input type='text' value='{$row['actual_stock']}' id='qty' class='text poQuantity' name='qty[]'>";
            $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
            $inventory_code = "<div class='inventoryCode'>{$row['inventory_code']}</div>";
            $item_code = "<div class='itemCode'>{$row['item_code']}</div>";
            $supplier = "<div class='supplier'>{$row['company_name']}</div>
            <input type='hidden' name='supplier_id[]' class='supplier_id_hidden' value='{$row['supplier_id']}'>";

            $rows .= "
            <tr>
                <td class='productSize'>{$product}</td>
                <td>{$inventory_code}</td>
                <td>{$item_code}</td>
                <td>{$supplier}</td>
                <td class='qtySize'>{$qty}</td>
                <td class='priceSize'>{$costPrice}</td>
                <td class='priceSize'>{$price}</td>
                <td class='qtySize'>{$gst}</td>
                <td>{$clear}</td>
            </tr>
            ";
        }
        //return $cpUtil->getJsonFromArray($arr);
        return $rows;
    }

    /**
     *
     */
    function getUpdateQtyDelivered() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $poProductId  = $fn->getReqParam('poProductChecked', array());

        foreach($poProductId AS $po_product_id){
            if($po_product_id != ''){
                $SQLPOProduct = "
                UPDATE po_product SET qty = qty_requested, status = 'Closed'
                WHERE po_product_id = '{$po_product_id}'
                ";
                $resultPOProduct  = $db->sql_query($SQLPOProduct);

                $SQLPoProd = "
                SELECT product_id, qty, pack_size, damage_qty, purchase_order_id, batch_no, po_product_id, free_items
                FROM po_product
                WHERE po_product_id = '{$po_product_id}'
                ";
                $resultPoProd  = $db->sql_query($SQLPoProd);
                $PoProd = $db->sql_fetchrow($resultPoProd);

                /*$SQLPoProdQty = "
                SELECT product_id, SUM(qty) AS qty
                FROM po_product
                WHERE product_id = '{$PoProd['product_id']}'
                ";
                $resultPoProdQty  = $db->sql_query($SQLPoProdQty);
                $PoProdQty = $db->sql_fetchrow($resultPoProdQty);

                $SQLInv = "
                SELECT product_id
                      ,actual_stock
                FROM inventory
                WHERE product_id = '{$PoProd['product_id']}'
                ";
                $resultInv  = $db->sql_query($SQLInv);
                $PoInv = $db->sql_fetchrow($resultInv);

                if($PoInv['actual_stock'] > 0){
                    $stock = $PoInv['actual_stock'] + $PoProd['qty'] - $PoProd['damage_qty'];
                } else {
                    $stock = $PoProd['qty'];
                }

                $SQLUpdateProduct = "
                UPDATE product SET qty_in_stock = {$stock}
                WHERE product_id = '{$PoProd['product_id']}'
                ";
                $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                $shRec = $fn->getRecordByCondition('stock_history', "po_product_id = '{$po_product_id}'");
                if($shRec['stock_history_id'] == ''){
                    $fa = array();
                    $fa['po_product_id'] = $po_product_id;
                    $fa['product_id']  = $PoProd['product_id'];
                    $fa['purchase_order_id']  = $PoProd['purchase_order_id'];
                    $fa['qty']  = $PoProd['qty'];
                    $fa['creation_date']  = date("Y-m-d H:i:s");

                    $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_history');
                    $resultInsert = $db->sql_query($SQLInsert);
                }*/

                $SQLBatchWiseStock = "
                SELECT po_product_id
                      ,purchased_qty
                      ,damaged_qty
                      ,free_qty
                      ,current_stock
                FROM inventory_batchwise_stock
                WHERE po_product_id = '{$po_product_id}'
                AND product_id = '{$PoProd['product_id']}'
                ";
                $resultBatchWiseStock  = $db->sql_query($SQLBatchWiseStock);
                $BatchWiseStock        = $db->sql_fetchrow($resultBatchWiseStock);
                $numRowsBatchWiseStock = $db->sql_numrows($resultBatchWiseStock);

                if(is_numeric($PoProd['pack_size'])){
                    $poQty = $PoProd['qty'] * $PoProd['pack_size'];
                } else {
                    $poQty = $PoProd['qty'];
                }

                if(is_numeric($PoProd['pack_size'])){
                    $freeQty = $PoProd['free_items'] * $PoProd['pack_size'];
                } else {
                    $freeQty = $PoProd['free_items'];
                }
                
                if($numRowsBatchWiseStock == 0) {
                    $stockUpdate = $poQty + $freeQty - $PoProd['damage_qty'];

                    $faStock = array();
                    $faStock['product_id']        = $PoProd['product_id'];
                    $faStock['po_product_id']     = $PoProd['po_product_id'];
                    $faStock['purchase_order_id'] = $PoProd['purchase_order_id'];
                    $faStock['batch_no']          = $PoProd['batch_no'];
                    $faStock['purchased_qty']     = $poQty;
                    $faStock['damaged_qty']       = $PoProd['damage_qty'];
                    $faStock['free_qty']          = $freeQty;
                    $faStock['current_stock']     = $stockUpdate;

                    $faStock     = $fn->addCreationDetailsToFieldsArray($faStock, 'inventory_batchwise_stock');
                    $SQLStock    = $dbUtil->getInsertSQLStringFromArray($faStock, 'inventory_batchwise_stock');
                    $resultStock = $db->sql_query($SQLStock);

                    $SQLUpdateProduct = "
                    UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0)+{$stockUpdate}
                    WHERE product_id = '{$PoProd['product_id']}'
                    ";
                    $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                    $SQLInventory = "
                    SELECT product_id
                    FROM inventory
                    WHERE product_id = {$PoProd['product_id']}
                    ";
                    $resultInventory  = $db->sql_query($SQLInventory);
                    $numRowsInventory = $db->sql_numrows($resultInventory);

                    if($numRowsInventory == 0) {
                        $SQLInvCode = "
                        SELECT MAX(CONVERT(inventory_code, UNSIGNED INTEGER)) + 1 AS inv_code
                        FROM `inventory`
                        ";
                        $resultInvCode = $db->sql_query($SQLInvCode);
                        $rowInvCode    = $db->sql_fetchrow($resultInvCode);

                        if($rowInvCode['inv_code'] != ""){
                            $inv_code = $rowInvCode['inv_code'];
                        }
                        else{
                            $inv_code = "1001";
                        }

                        $fa = array();
                        $fa['product_id']     = $PoProd['product_id'];
                        $fa['actual_stock']   = $stockUpdate;
                        $fa['inventory_code'] = $inv_code;
                        $fa['creation_date']  = date('Y-m-d H:i:s');

                        $inventory_id = $fn->addRecord($fa, 'inventory');
                    } else {
                        $SQLUpdateInventory = "
                        UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0)+{$stockUpdate}
                        WHERE product_id = '{$PoProd['product_id']}'
                        ";
                        $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
                    }
                } else {
                    $qty_batchwise        = $poQty - $BatchWiseStock['purchased_qty']; 
                    $damage_qty_batchwise = $PoProd['damage_qty'] - $BatchWiseStock['damaged_qty']; 
                    $free_qty_batchwise   = $freeQty - $BatchWiseStock['free_qty']; 
                    $current_stock        = $BatchWiseStock['current_stock'] + $qty_batchwise + $free_qty_batchwise - $damage_qty_batchwise;
                    $stockCalc            = $qty_batchwise + $free_qty_batchwise - $damage_qty_batchwise;

                    $faStock = array();
                    $faStock['purchased_qty'] = $BatchWiseStock['purchased_qty'] + $qty_batchwise;
                    $faStock['damaged_qty']   = $BatchWiseStock['damaged_qty'] + $damage_qty_batchwise;
                    $faStock['free_qty']      = $BatchWiseStock['free_qty'] + $free_qty_batchwise;
                    $faStock['current_stock'] = $current_stock;
                    
                    $faStock        = $fn->addModificationDetailsToFieldsArray($faStock, 'inventory_batchwise_stock');
                    $whereCondition = "WHERE po_product_id = {$po_product_id}
                                       AND product_id = '{$PoProd['product_id']}'";
                    $SQLStock    = $dbUtil->getUpdateSQLStringFromArray($faStock, "inventory_batchwise_stock", $whereCondition);
                    $resultStock = $db->sql_query($SQLStock);

                    $SQLUpdateProduct = "
                    UPDATE product SET qty_in_stock = IFNULL(qty_in_stock, 0)+{$stockCalc}
                    WHERE product_id = '{$PoProd['product_id']}'
                    ";
                    $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                    $SQLUpdateInventory = "
                    UPDATE inventory SET actual_stock = IFNULL(actual_stock, 0)+{$stockCalc}
                    WHERE product_id = '{$PoProd['product_id']}'
                    ";
                    $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
                }
            }
        }
        
        /*$modObj = getCPModuleObj('tradingin_inventory');
        $modObj->view->getCreateInventoryRecords();*/
    }

    /**
     *
     */
    function getDeletePoProduct() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $po_product_id     = $fn->getReqParam('po_product_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $SQLDeletePOProduct = "
        DELETE FROM po_product
        WHERE po_product_id = '{$po_product_id}'
        AND purchase_order_id = '{$purchase_order_id}'
        ";
        $resultDeletePOProduct  = $db->sql_query($SQLDeletePOProduct);

        $SQLDeleteStock = "
        DELETE FROM inventory_batchwise_stock
        WHERE po_product_id = '{$po_product_id}'
        AND purchase_order_id = '{$purchase_order_id}'
        ";
        $resultDeleteStock = $db->sql_query($SQLDeleteStock);
    }

    /**
     *
     */
    function getDuplicatePO() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $purchase_order_id_source = $fn->getReqParam('purchase_order_id');
        $linkedProduct = $fn->getReqParam('linkedProduct');

        $rowPOSrc = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id_source);

        //create header
        $purchase_order_date    = strtotime(date('Y-m-d'));
        $followup_date = strtotime('+7 days', $purchase_order_date);

        $fa = array();
        $fa['po_code']              = $this->getUpdatePOCode();
        $fa['company_id_supplier']  = $rowPOSrc['company_id_supplier'];
        $fa['title']                = $rowPOSrc['title'];
        $fa['payment_terms']        = $rowPOSrc['payment_terms'];
        $fa['delivery_terms']       = $rowPOSrc['delivery_terms'];
        $fa['notes']                = $rowPOSrc['notes'];
        $fa['creation_date']        = date('Y-m-d');
        $fa['contact_id_supplier']  = $rowPOSrc['contact_id_supplier'];
        $fa['purchase_order_date']  = date('Y-m-d', $purchase_order_date);
        $fa['status']               = $rowPOSrc['status'];
        $fa['buy_currency']         = $rowPOSrc['buy_currency'];
        $fa['staff_id']             = $rowPOSrc['staff_id'];
        $fa['priority']             = $rowPOSrc['priority'];
        $fa['quote_id']             = $rowPOSrc['quote_id'];
        $fa['amount']               = $rowPOSrc['amount'];
        $fa['follow_up_date']       = date('Y-m-d', $followup_date);
        $fa['currency']             = $rowPOSrc['currency'];
        $fa['freight_cost']         = $rowPOSrc['freight_cost'];
        $fa['supplier_quote_id']    = $rowPOSrc['supplier_quote_id'];
        $fa['batch_import']         = $rowPOSrc['batch_import'];
        $fa['site_id']              = $rowPOSrc['site_id'];
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'purchase_order');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order');
        $db->sql_query($SQL);
        $purchase_order_id = $db->sql_nextid();

        if($linkedProduct == 1){
            $SQLPO    = "
            SELECT po.*
            FROM po_product po
            WHERE po.purchase_order_id = '{$purchase_order_id_source}'
            ";
            $resultPO = $db->sql_query($SQLPO);

            //$qty_balance = $row['qty_requested'] - $row['qty'];


            while ($row = $db->sql_fetchrow($resultPO)) {
                $fa1 = array();
                $fa1['purchase_order_id']   = $purchase_order_id;
                $fa1['product_id']          = $row['product_id'];
                //$fa1['qty']                 = $row['qty'];
                $fa1['price']               = $row['price'];
                //$fa1['status']              = $row['status'];
                //$fa1['qty_delivered']       = $row['qty_delivered'];
                $fa1['qty_requested']       = $row['qty_requested'];
                //$fa1['qty_balance']         = $qty_balance
                $fa1['selling_price']       = $row['selling_price'];
                $fa1['cost_price']       = $row['cost_price'];
                $fa1['gst']       = $row['gst'];
                $fa1 = $fn->addCreationDetailsToFieldsArray($fa1, 'po_product');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'po_product');
                $db->sql_query($SQL);
            }
        }
    }

    function getHighlightkeyword($str, $search) {
        $highlightcolor = "#daa732";
        $occurrences = substr_count(strtolower($str), strtolower($search));
        $newstring = $str;
        $match = array();

        for ($i=0;$i<$occurrences;$i++) {
            $match[$i] = stripos($str, $search, $i);
            $match[$i] = substr($str, $match[$i], strlen($search));
            $newstring = str_replace($match[$i], '[#]'.$match[$i].'[@]', strip_tags($newstring));
        }

        $newstring = str_replace('[#]', '<span style="color: '.$highlightcolor.';">', $newstring);
        $newstring = str_replace('[@]', '</span>', $newstring);
        return $newstring;

    }

    /**
     *
     */
    function getCreationModificationPO() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $po_product_id = $fn->getReqParam('po_product_id');

        $header = "
        <thead>
            <tr>
                <td>Created By/Creation Date</td>
                <td>Modified By/Modification Date</td>
            </tr>
        </thead>
        ";

        $SQLPO ="
        SELECT pp.creation_date
              ,pp.created_by
              ,pp.modification_date
              ,pp.modified_by
        FROM po_product pp
        WHERE pp.po_product_id = {$po_product_id}
        ";
        $resultPo = $db->sql_query($SQLPO);
        $rowPo    = $db->sql_fetchrow($resultPo);

        if($rowPo['modified_by'] != ""){
            $modified_by = "{$rowPo['modified_by']}/{$rowPo['modification_date']}";
        }else{
            $modified_by = "";
        }

        if($rowPo['created_by'] != ""){
            $created_by = "{$rowPo['created_by']}/{$rowPo['creation_date']}";
        }else{
            $created_by = "";
        }

        $rows = "
        <tbody>
            <tr>
                <td>{$created_by}</td>
                <td>{$modified_by}</td>
            </tr>
        </tbody>
        ";

        $text = "
        <form id='creationModificationPo' class='creationModificationPo' method='post'>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

     /**
     *
     */
    function getAddNewProductMasterValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter product name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewProductMasterSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddNewProductMasterValidate()){
            return $validate->getErrorMessageXML();
        }

        $title        = $fn->getPostParam('title');
        $generic_name = $fn->getPostParam('generic_name');
        $supplier_id  = $fn->getReqParam('supplier_id');
        $title  = trim($title);

        $fa = array();
        $fa['title']         = $title;
        $fa['generic_name']  = $generic_name;
        $fa['published']     = 1;
        $fa['product_type']  = 'Purchasing and Selling';
        $fa['item_code']     = $this->getUpdateProductCode();
        $fa['created_by']    = $fn->getSessionParam('userName');
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insert1    = $dbUtil->getInsertSQLStringFromArray($fa, 'product');
        $result1    = $db->sql_query($insert1);
        $product_id = $db->sql_nextid();

        $fa2 = array();
        $fa2['company_id']    = $supplier_id;
        $fa2['product_id']    = $product_id;
        $fa2['creation_date'] = date('Y-m-d H:i:s');

        $insert2 = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_company');
        $result2 = $db->sql_query($insert2);

        /*$sqlSite = "
        SELECT site_id
        FROM site
        ORDER BY site_id
        ";
        $resultSite   = $db->sql_query($sqlSite);
        
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $fa = array();
            $fa['product_id']       = $product_id;
            $fa['site_id']          = $rowSite['site_id'];
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['created_by']       = $fn->getSessionParam('userName');

            $insert3 = $dbUtil->getInsertSQLStringFromArray($fa, 'medicine_site');
            $result3 = $db->sql_query($insert3);
        }*/

        return $validate->getSuccessMessageXML();
    }

     /**
     *
     */
    function getUpdateOverallDiscount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $overall_discount  = $fn->getReqParam('overall_discount');

        $arr = array();

        if($overall_discount == ""){
            $overall_discount = 0;
        }

        $SQLUpdateOverallDisc = "
        UPDATE purchase_order SET overall_discount = '{$overall_discount}'
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQLUpdateOverallDisc);

        $SQLTotal = "
        SELECT SUM(pop.qty_requested * pop.cost_price) AS total_cost
              ,SUM((((pop.qty_requested * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty_requested * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
              ,SUM((((pop.qty_requested * pop.cost_price) - (((pop.qty_requested * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty_requested * pop.cost_price) * {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
        FROM po_product pop WHERE pop.purchase_order_id = {$purchase_order_id}
        ";
        $resultTotal   = $db->sql_query($SQLTotal);
        $rowTotal      = $db->sql_fetchrow($resultTotal);
        $totalCost     = $rowTotal['total_cost'] - $rowTotal['Discount_Total'] + $rowTotal['GST_Total'];
        $Grand_Total   = number_format(round($totalCost), 2);  
        $discountTotal = number_format(round($rowTotal['Discount_Total']), 2);

        $arr = array("grand_total"   => "Grand Total: ".$Grand_Total, "total_discount" => "Discount: ".$discountTotal);

        return json_encode($arr);
    }

    /**
     *
     */
    function getTotalPharmacySalesAmount($date, $current_month, $current_year) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $appendSqlCol     = "";
        $appendSqlSaleRet = "";
        $appendSqlPharma  = "";


        if($date != "") {
            $appendSqlCol     = "AND invoice_date = '{$date}'";
            $appendSqlSaleRet = "AND o.order_date = '{$date}'";
            $appendSqlPharma  = "WHERE p.date = '{$date}'";
        }

        if ($current_month != "") {
            $search_form = date($current_year.'-'.$current_month.'-01');
            $search_to   = date("Y-m-t", strtotime($search_form));
        } else {
            $search_form = date('Y-m-01');
            $search_to   = date('Y-m-t');
        }
        
        $appendSql  = "AND (po.purchase_order_date BETWEEN '{$search_form}' AND '{$search_to}')";
        $SQL = "
        SELECT po.*
        FROM purchase_order po
         WHERE po.status != 'Cancelled'
        AND (po.purchase_order_date BETWEEN '{$search_form}' AND '{$search_to}')
        ORDER BY po.purchase_order_date DESC, po.purchase_order_id
        ";
        $result = $db->sql_query($SQL);
        $totalCost = 0;
        while($row    = $db->sql_fetchrow($result)){
            $overall_discount = $row['overall_discount'];

            if($overall_discount == ''){
                $overall_discount = 0;
            }
            $SQLTotal = "
            SELECT SUM(pop.qty * pop.cost_price) AS total_cost
                  ,SUM((((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) + (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) AS Discount_Total
                  ,SUM((((pop.qty * pop.cost_price) - (((pop.qty * pop.cost_price) * pop.discount_percentage) / 100) - (((pop.qty * pop.cost_price) * {$overall_discount}) / 100)) * pop.gst) / 100) AS GST_Total
            FROM po_product pop
            WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal    = $db->sql_fetchrow($resultTotal);
            $totalCost   += $rowTotal['total_cost'] - $rowTotal['Discount_Total'] + $rowTotal['GST_Total'];
        }
        $totalCost   = round($totalCost);

        $Values = array('totalAmount'     => $totalCost);

        return $Values;
    }
}
