<?
class CPL_Admin_Modules_Hms_PharmacyDailySales_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT p.*
        FROM pharma_daily_sales p
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
        $searchVar->mainTableAlias = 'p';

        $pharma_daily_sales_id = $fn->getReqParam('pharma_daily_sales_id');
        $current_month = $fn->getReqParam('current_month');
        $current_year  = $fn->getReqParam('current_year');
        $month         = date('m');
        $year          = date('Y');

        if ($pharma_daily_sales_id != "") {
            $searchVar->sqlSearchVar[] = "p.pharma_daily_sales_id = '{$pharma_daily_sales_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.pharma_daily_sales_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.pharma_daily_sales_id');

            if ($current_month != "") {
                $search_from = date($current_year.'-'.$current_month.'-01');
                $search_to   = date("Y-m-t", strtotime($search_from));
                $searchVar->sqlSearchVar[] = "(p.date BETWEEN '{$search_from}' AND '{$search_to}')";
            }else {
                $search_from = date('Y-m-01');
                $search_to   = date('Y-m-t');
                $searchVar->sqlSearchVar[] = "(p.date BETWEEN '{$search_from}' AND '{$search_to}')";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

            $searchVar->sortOrder = "p.date desc";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('date', 'Please enter the Date');

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
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'sales_amount');
        $fa = $fn->addToFieldsArray($fa, 'excess_amount');
        $fa = $fn->addToFieldsArray($fa, 'site_id');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');

        return $fa;
    }

    /**
     *
     */
    function getCreateCurrentDayPharmacyRecord() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //http://santhyahms.localhost/admin/index.php?_topRm=finance&module=hms_pharmacyDailySales&_spAction=createCurrentDayPharmacyRecord&showHTML=0

        $currentDate = date("Y-m-d");

        $SQLPharma = "
        SELECT p.pharma_daily_sales_id
        FROM pharma_daily_sales p
        WHERE p.date = '{$currentDate}'
        ";
        $resultPharma  = $db->sql_query($SQLPharma);
        $numRowsPharma = $db->sql_numrows($resultPharma);

        if($numRowsPharma == 0) {
            $fa = array();
            $fa['date']          = $currentDate;
            $fa['created_by']    = 'Super Admin';
            $fa['creation_date'] = date('Y-m-d H:i:s');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'pharma_daily_sales');
            $result = $db->sql_query($SQL);
        }
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

        if ($current_month != "") {
            $search_from = date($current_year.'-'.$current_month.'-01');
            $search_to   = date("Y-m-t", strtotime($search_from));
        } else {
            $search_from = date('Y-m-01');
            $search_to   = date('Y-m-t');
        }

        if($date != "") {
            $appendSqlCol     = "AND invoice_date = '{$date}'";
            $appendSqlSaleRet = "AND srh.date = '{$date}'";
            $appendSqlPharma  = "WHERE p.date = '{$date}'";
        }
        else if($date == "") {
            $appendSqlCol     = "AND (invoice_date BETWEEN '{$search_from}' AND '{$search_to}')";
            $appendSqlSaleRet = "AND (srh.date BETWEEN '{$search_from}' AND '{$search_to}')";
            $appendSqlPharma  = "WHERE (p.date BETWEEN '{$search_from}' AND '{$search_to}')";
        }

        $SQLCollection = "
        SELECT SUM(invoice_amount) AS total_amount
        FROM `invoice`
        WHERE status != 'Cancelled'
        AND invoice_type = 'POS'
        {$appendSqlCol}
        ";
        $resultCollection = $db->sql_query($SQLCollection);
        $recCollection    = $db->sql_fetchrow($resultCollection);

        $totalCollection = $recCollection['total_amount'];

        $SQLSalesReturn = "
        SELECT 
            SUM(srh.qty_return * srh.price) as sales_return_amount 
        FROM sales_return_history srh
        LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
        LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
        LEFT JOIN (`order` o) ON (o.order_id = srh.order_id)
        WHERE o.order_status != 'Cancelled'
        AND o.order_type = 'POS'
        {$appendSqlSaleRet}
        ";
        $resultSalesReturn = $db->sql_query($SQLSalesReturn);
        $recSalesReturn    = $db->sql_fetchrow($resultSalesReturn);

        $salesReturn =  $recSalesReturn['sales_return_amount'];

        $SQLPharma = "
        SELECT SUM(p.excess_amount) AS excess_amount
        FROM pharma_daily_sales p
        {$appendSqlPharma}
        ";
        $resultPharma = $db->sql_query($SQLPharma);
        $recPharma    = $db->sql_fetchrow($resultPharma);

        $totalAmount = $totalCollection + $recPharma['excess_amount'] - $salesReturn;

        $Values = array('salesReturn'     => $salesReturn
                       ,'totalCollection' => $totalCollection
                       ,'totalAmount'     => $totalAmount);

        return $Values;
    }

    /**
     *
     */
    function getTotalPharmacySalesGstAmount($date, $current_month, $current_year) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $appendSqlCol     = "";
        $appendSqlSaleRet = "";
        $appendSqlPharma  = "";

        if ($current_month != "") {
            $search_from = date($current_year.'-'.$current_month.'-01');
            $search_to   = date("Y-m-t", strtotime($search_from));
        } else {
            $search_from = date('Y-m-01');
            $search_to   = date('Y-m-t');
        }

        if($date != "") {
            $appendSqlCol     = "AND i.invoice_date = '{$date}'";
            $appendSqlSaleRet = "AND srh.date = '{$date}'";
            $appendSqlPharma  = "WHERE p.date = '{$date}'";
        }
        else if($date == "") {
            $appendSqlCol     = "AND (i.invoice_date BETWEEN '{$search_from}' AND '{$search_to}')";
            $appendSqlSaleRet = "AND (srh.date BETWEEN '{$search_from}' AND '{$search_to}')";
            $appendSqlPharma  = "WHERE (p.date BETWEEN '{$search_from}' AND '{$search_to}')";
        }

        $SQLCollection = "
        SELECT SUM(i.invoice_amount) AS total_amount
        FROM `invoice` i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        WHERE i.status != 'Cancelled'
        AND i.invoice_type = 'POS'
        AND o.enable_gst = 1
        {$appendSqlCol}
        ";
        $resultCollection = $db->sql_query($SQLCollection);
        $recCollection    = $db->sql_fetchrow($resultCollection);

        $totalCollection = $recCollection['total_amount'];

        $SQLSalesReturn = "
        SELECT 
            SUM(srh.qty_return * srh.price) as sales_return_amount 
        FROM sales_return_history srh
        LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
        LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
        LEFT JOIN (`order` o) ON (o.order_id = srh.order_id)
        WHERE o.order_status != 'Cancelled'
        AND o.order_type = 'POS'
        AND o.enable_gst = 1
        {$appendSqlSaleRet}
        ";
        $resultSalesReturn = $db->sql_query($SQLSalesReturn);
        $recSalesReturn    = $db->sql_fetchrow($resultSalesReturn);

        $salesReturn =  $recSalesReturn['sales_return_amount'];

        $totalAmount = $totalCollection - $salesReturn;

        $Values = array('salesReturn'     => $salesReturn
                       ,'totalCollection' => $totalCollection
                       ,'totalAmount'     => $totalAmount);

        return $Values;
    }
}
