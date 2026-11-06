<?
class CPL_Admin_Lib_Functions Extends CP_Admin_Lib_Functions
{

    /**
     *
     */
    function getRecordRowByID($tableName, $key_field_name, $id, $exp = array()) {
        $db = Zend_Registry::get('db');

        $globalForAllSites = $this->getIssetParam($exp, 'globalForAllSites', false);
        $fetchType = $this->getIssetParam($exp, 'fetchType', MYSQL_BOTH);

        if ($id == ''){
            return;
        }

        $condn = $this->getIssetParam($exp, 'condn');
        if (is_array($condn)) {
            $condn = $this->addSiteIdToWhereCondn();
        }

        $SQL = "
        SELECT *
        FROM `{$tableName}`
        WHERE {$key_field_name} = {$id}
        {$condn}
        ";

        //print $SQL .'<hr>';

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result, $fetchType);

        return $row;
    }


    /**
     *
     */
    function getRecordByCondition($tableName, $condn = '', $orderBy = '', $module = '') {
        $db = Zend_Registry::get('db');
        if ($condn != '') {
            $condn = " WHERE {$condn}";
        }

        $orderBy = ($orderBy != '') ? " ORDER BY {$orderBy}" : '';

        $SQL = "
        SELECT *
        FROM `{$tableName}`
        {$this->appendSiteIdToCondn($condn, $module)}
        {$orderBy}
        LIMIT 0, 1
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            return $row;
        }
    }

    /**
     *
     */
    function getSiteIdForWhereCondn($tblPrefix = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tblPrefix = ($tblPrefix != '') ? ".{$tblPrefix}" : '';

        if ($cpCfg['cp.hasMultiUniqueSites']){
            if (CP_SCOPE == 'admin'){
                $site_id = @$_SESSION['cp_site_id'];
            } else {
                $site_id = $cpCfg['cp.site_id'];
            }

            if ($site_id != ''){
                return "site_id = {$tblPrefix}{$site_id}";
            }
        }
    }

    /**
     *
     */
    function appendSiteIdToCondn($condn, $module= '') {
        $cpCfg = Zend_Registry::get('cpCfg');

        $siteCondn = '';

        if ($module != '' & in_array($module, $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
            $siteCondn = '';
        }

        $condn .= ($condn != '' && $siteCondn != '') ? " AND {$siteCondn}"  : ($siteCondn != '' ? " WHERE {$siteCondn}" : '');

        return $condn;
    }

    /**
     *
     */
    function addRecord($fieldsArray, $tableName = '', $excludeFldsArr = array()) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tableName == '') {
            if ($tv['module'] != ''){
                $tableName   = $modulesArr[$tv['module']]['tableName'];
            } else if ($tv['lnkRoom'] != ''){
                $tableName   = $modulesArr[$tv['lnkRoom']]['tableName'];
            } else {
                exit('Room Name missing in: $fn->addRecord');
            }
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tableName != 'site'){
            if($dbUtil->getColumnExists($tableName, 'site_id')){
                if($tv['module'] != 'hms_inventory'){
                    $fieldsArray['site_id'] = $this->getSessionParam('cp_site_id');
                }
            }
        }

        if ($cpCfg['cp.hasMultiYears']){
            if($dbUtil->getColumnExists($tableName, 'cp_year')){
                $fieldsArray['cp_year'] = $fn->getSessionParam('cp_year');
            }
        }

        $fieldsArray = $this->addCreationDetailsToFieldsArray($fieldsArray, $tableName);
        //-----------------------------------------------------------------------//
        $SQL    = $dbUtil->getInsertSQLStringFromArray($fieldsArray, $tableName, $excludeFldsArr);
        //fb::log($SQL);
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        return $id;
    }
    /**
     *
     */
    function getConvertNumber($number) {
        
        /* Check whether number has decimal value or not. If no, add decimal point and zeros */
        if (!is_float($number)) {
            $number = $number . '.00';
        }

        list($integer, $fraction) = explode(".", (string) $number);

        $output = "";

        if ($integer{0} == "-") {
            $output = "negative ";
            $integer    = ltrim($integer, "-");
        } else if ($integer{0} == "+") {
            $output = "positive ";
            $integer    = ltrim($integer, "+");
        }

        if ($integer{0} == "0") {
            $output .= "zero";
        } else {
            $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
            $group   = rtrim(chunk_split($integer, 3, " "), " ");
            $groups  = explode(" ", $group);

            $groups2 = array();
            foreach ($groups as $g) {
                $groups2[] = $this->getConvertThreeDigit($g{0}, $g{1}, $g{2});
            }

            for ($z = 0; $z < count($groups2); $z++) {
                if ($groups2[$z] != "") {
                    $output .= $groups2[$z] . $this->getConvertGroup(11 - $z) . (
                            $z < 11
                            && !array_search('', array_slice($groups2, $z + 1, -1))
                            && $groups2[11] != ''
                            && $groups[11]{0} == '0'
                                ? " and "
                                : " "
                        );
                }
            }

            $output = rtrim($output, ", ");
        }

        if ($fraction > 0) {
            /* If the decimal point has more than three numbers */
            if (strlen($fraction) > 2) {
                $fraction = substr($fraction, 0, 2);
            }

            $fraction1 = substr($fraction, 0, 1);
            $fraction2 = substr($fraction, 1, 2);
            $output .= " and cents";
            if ($fraction1 > 0) {
                $output .= " " . $this->getConvertTwoDigit($fraction1, $fraction2);
            } else {
                $output .= " " . $this->getConvertDigit($fraction2);
            }
            /* Check whether decimal is 2 or 1 digit */
            /*
            if (strlen($fraction) == 2) {
                for ($i = 0; $i < strlen($fraction); $i++) {
                    $output .= " " . $this->getConvertDigit($fraction{$i});
                }
            } else {
                $output .= " " . $this->getConvertTwoDigit($fraction, '');
            }
            */
        }

        $output = $output . ' only';

        return $output;
    }

        /**
     *
     */
    function getConvertThreeDigit($digit1, $digit2, $digit3) {
        $buffer = "";

        if ($digit1 == "0" && $digit2 == "0" && $digit3 == "0") {
            return "";
        }

        if ($digit1 != "0") {
            $buffer .= $this->getConvertDigit($digit1) . " hundred";
            if ($digit2 != "0" || $digit3 != "0") {
                $buffer .= " and ";
            }
        }

        if ($digit2 != "0") {
            $buffer .= $this->getConvertTwoDigit($digit2, $digit3);
        } else if ($digit3 != "0") {
            $buffer .= $this->getConvertDigit($digit3);
        }

        return $buffer;
    }

    /**
     *
     */
    function getConvertTwoDigit($digit1, $digit2) {
        if ($digit2 == "0") {
            switch ($digit1) {
                case "1":
                    return "ten";
                case "2":
                    return "twenty";
                case "3":
                    return "thirty";
                case "4":
                    return "forty";
                case "5":
                    return "fifty";
                case "6":
                    return "sixty";
                case "7":
                    return "seventy";
                case "8":
                    return "eighty";
                case "9":
                    return "ninety";
            }
        } else if ($digit1 == "1") {
            switch ($digit2) {
                case "1":
                    return "eleven";
                case "2":
                    return "twelve";
                case "3":
                    return "thirteen";
                case "4":
                    return "fourteen";
                case "5":
                    return "fifteen";
                case "6":
                    return "sixteen";
                case "7":
                    return "seventeen";
                case "8":
                    return "eighteen";
                case "9":
                    return "nineteen";
            }
        } else {
            $temp = $this->getConvertDigit($digit2);
            switch ($digit1) {
                case "2":
                    return "twenty $temp";
                case "3":
                    return "thirty $temp";
                case "4":
                    return "forty $temp";
                case "5":
                    return "fifty $temp";
                case "6":
                    return "sixty $temp";
                case "7":
                    return "seventy $temp";
                case "8":
                    return "eighty $temp";
                case "9":
                    return "ninety $temp";
            }
        }
    }
    /**
     *
     */
    function getConvertDigit($digit) {
        switch ($digit) {
            case "0":
                return "zero";
            case "1":
                return "one";
            case "2":
                return "two";
            case "3":
                return "three";
            case "4":
                return "four";
            case "5":
                return "five";
            case "6":
                return "six";
            case "7":
                return "seven";
            case "8":
                return "eight";
            case "9":
                return "nine";
        }
    }
    /**
     *
     */
    function getConvertGroup($index) {
        switch ($index) {
            case 11:
                return " decillion";
            case 10:
                return " nonillion";
            case 9:
                return " octillion";
            case 8:
                return " septillion";
            case 7:
                return " sextillion";
            case 6:
                return " quintrillion";
            case 5:
                return " quadrillion";
            case 4:
                return " trillion";
            case 3:
                return " billion";
            case 2:
                return " million";
            case 1:
                return " thousand";
            case 0:
                return "";
        }
    }

    /**
     *
     */
    function getStockForProduct($product_id, $siteId = "") {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $appendSqlOrd   = "";
        $appendSqlPur   = "";
        $appendSqlInv   = "";
        $appendSqlStk   = "";
        $appendSqlToStk = "";
        $appendSqlToUFP = "";
        $appendSqlInT   = "";

        if($siteId){
            $appendSqlOrd   = "AND o.site_id        = {$siteId}";
            $appendSqlPur   = "AND po.site_id       = {$siteId}";
            $appendSqlInv   = "AND inv.site_id      = {$siteId}";
            $appendSqlStk   = "AND st.from_location = {$siteId}";
            $appendSqlToStk = "AND st.to_location   = {$siteId}";
            $appendSqlInT   = "AND site_id = {$siteId}";
        } else {
            $appendSqlStk   = "AND st.from_location = 1";
            $appendSqlToStk = "AND st.to_location   = 1";
            $appendSqlToUFP = "AND st.to_location   = 4";
        }

        $SQLOthersite = "
        SELECT
            (SELECT SUM(CASE 
                        WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                        THEN pp.qty * pp.pack_size
                        ELSE pp.qty END) AS purchased_qty
             FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$product_id}
             AND po.status != 'Cancelled'
             {$appendSqlPur}
             ) as product_qty_purchased

           ,(SELECT SUM(damage_qty) FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$product_id}
             {$appendSqlPur}
             ) as damage_qty

           ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
            LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
            WHERE inItm.record_id = {$product_id}
            AND inItm.not_add_in_stock != 1
            AND inv.status = 'Paid'
            AND inv.invoice_type = 'POS'
            {$appendSqlInv}
            ) as product_qty_sold

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlStk}
            ) as product_qty_sold_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToStk}
            ) as product_qty_got_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToUFP}
            ) as product_qty_used_for_patient

            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id)
            WHERE ini.record_id = {$product_id}
            AND inv.status != 'Cancelled'
            {$appendSqlInv}
            ) as sales_return_qty

            ,(SELECT SUM(adjust_stock)
              FROM inventory_adjust_stock
              WHERE product_id = {$product_id}
              {$appendSqlInT}
            ) AS adjust_stock
        ";
        $resultothersite = $db->sql_query($SQLOthersite);
        $rowothersite = $db->sql_fetchrow($resultothersite);

        $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_stock'] - $rowothersite['product_qty_sold'] + $rowothersite['sales_return_qty'] - $rowothersite['damage_qty'] + $rowothersite['product_qty_got_from_stock'] - $rowothersite['product_qty_used_for_patient'] + $rowothersite['adjust_stock'];

        $Values = array('OverallStock'         => $stock
                        ,'PurchasedQty'        => $rowothersite['product_qty_purchased']
                        ,'SoldQty'             => $rowothersite['product_qty_sold']
                        ,'StockTransferOutQty' => $rowothersite['product_qty_sold_from_stock']
                        ,'StockTransferInQty'  => $rowothersite['product_qty_got_from_stock']
                        ,'SalesReturnQty'      => $rowothersite['sales_return_qty']
                        ,'DamagedQty'          => $rowothersite['damage_qty']
                        ,'UsedPatientQty'      => $rowothersite['product_qty_used_for_patient']
                        ,'AdjustStock'         => $rowothersite['adjust_stock']);
        
        return $Values;
    }

    /**
     *
     */
    function getStockForProductBatchWise($product_id, $locationId = "", $batchNo = "") {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $appendSqlPur   = "";
        $appendSqlInv   = "";
        $appendSqlStk   = "";
        $appendSqlToStk = "";
        $appendSqlToUFP = "";
        $appendSqlInT   = "";

        if($locationId != "" && $batchNo != "") {
            $appendSqlPur   = "AND pp.batch_no      = '{$batchNo}'";
            $appendSqlInv   = "AND inItm.batch_no   = '{$batchNo}'";
            $appendSqlStk   = "AND st.from_location = {$locationId}
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToStk = "AND st.to_location   = {$locationId}
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToUFP = "AND st.from_location = {$locationId}
                               AND st.to_location   = 4
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlInT   = "AND batch_no         = '{$batchNo}'";
        }
        else if($locationId == "" && $batchNo != ""){
            $appendSqlPur   = "AND pp.batch_no      = '{$batchNo}'";
            $appendSqlInv   = "AND inItm.batch_no   = '{$batchNo}'";
            $appendSqlStk   = "AND st.from_location = 1
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToStk = "AND st.to_location   = 1
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToUFP = "AND st.to_location   = 4
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlInT   = "AND batch_no         = '{$batchNo}'";
        }
        else {
            $appendSqlStk   = "AND st.from_location = 1";
            $appendSqlToStk = "AND st.to_location   = 1";
            $appendSqlToUFP = "AND st.to_location   = 4";
        }
        
        $SQLOthersite = "
        SELECT
            (SELECT SUM(CASE 
                        WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                        THEN pp.qty * pp.pack_size
                        ELSE pp.qty END) AS purchased_qty
             FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$product_id}
             AND po.status != 'Cancelled'
             {$appendSqlPur}
             ) as product_qty_purchased

           ,(SELECT SUM(damage_qty) FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$product_id}
             {$appendSqlPur}
             ) as damage_qty

           ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
            LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
            WHERE inItm.record_id = {$product_id}
            AND inItm.not_add_in_stock != 1
            AND inv.status = 'Paid'
            AND inv.invoice_type = 'POS'
            {$appendSqlInv}
            ) as product_qty_sold

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlStk}
            ) as product_qty_sold_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToStk}
            ) as product_qty_got_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToUFP}
            ) as product_qty_used_for_patient

            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item inItm) ON (inItm.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id)
            WHERE inItm.record_id = {$product_id}
            AND inv.status != 'Cancelled'
            {$appendSqlInv}
            ) as sales_return_qty
            
            ,(SELECT SUM(adjust_stock)
              FROM inventory_adjust_stock
              WHERE product_id = {$product_id}
              {$appendSqlInT}
            ) AS adjust_stock
        ";
        $resultothersite = $db->sql_query($SQLOthersite);
        $rowothersite = $db->sql_fetchrow($resultothersite);

        $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_stock'] - $rowothersite['product_qty_sold'] + $rowothersite['sales_return_qty'] - $rowothersite['damage_qty'] + $rowothersite['product_qty_got_from_stock'] - $rowothersite['product_qty_used_for_patient'] + $rowothersite['adjust_stock'];

        $Values = array('OverallStock'    => $stock
                        ,'PurchasedQty'   => $rowothersite['product_qty_purchased']
                        ,'SoldQty'        => $rowothersite['product_qty_sold'] + $rowothersite['product_qty_sold_from_stock']
                        ,'SalesReturnQty' => $rowothersite['sales_return_qty']
                        ,'DamagedQty'     => $rowothersite['damage_qty']
                        ,'UsedPatientQty' => $rowothersite['product_qty_used_for_patient']
                        ,'AdjustStock'    => $rowothersite['adjust_stock']);

        return $Values;
    }

    /**
     *
     */
    function getStockForProductLocationWise($product_id, $locationId = "") {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $appendSqlOrd   = "";
        $appendSqlPur   = "";
        $appendSqlInv   = "";
        $appendSqlStk   = "";
        $appendSqlToStk = "";
        $appendSqlToUFP = "";
        $appendSqlInT   = "";

        if($locationId) {
            $appendSqlStk   = "AND st.from_location = {$locationId}";
            $appendSqlToStk = "AND st.to_location   = {$locationId}";
            $appendSqlToUFP = "AND st.from_location = {$locationId}
                               AND st.to_location   = 4";
        }
        else {
            $appendSqlStk   = "AND st.from_location = 1";
            $appendSqlToStk = "AND st.to_location   = 1";
            $appendSqlToUFP = "AND st.to_location   = 4";
        }

        $SQLOthersite = "
        SELECT
            (SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlStk}
            ) as product_qty_sold_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToStk}
            ) as product_qty_got_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToUFP}
            ) as product_qty_used_for_patient

            ,(SELECT SUM(adjust_stock)
              FROM inventory_adjust_stock
              WHERE product_id = {$product_id}
              {$appendSqlInT}
            ) AS adjust_stock
        ";
        $resultothersite = $db->sql_query($SQLOthersite);
        $rowothersite = $db->sql_fetchrow($resultothersite);

        $stock = $rowothersite['product_qty_got_from_stock'] - $rowothersite['product_qty_sold_from_stock'] - $rowothersite['product_qty_used_for_patient'] + $rowothersite['adjust_stock'];

        if($rowothersite['product_qty_got_from_stock'] == "") {
            $rowothersite['product_qty_got_from_stock'] = 0;
        }

        if($rowothersite['product_qty_sold_from_stock'] == "") {
            $rowothersite['product_qty_sold_from_stock'] = 0;
        }

        if($rowothersite['product_qty_used_for_patient'] == "") {
            $rowothersite['product_qty_used_for_patient'] = 0;
        }

        $Values = array('OverallStock'      => $stock
                        ,'StockTransferIn'  => $rowothersite['product_qty_got_from_stock']
                        ,'StockTransferOut' => $rowothersite['product_qty_sold_from_stock']
                        ,'UsedPatientQty'   => $rowothersite['product_qty_used_for_patient']);

        return $Values;
    }

    /**
     *
     */
    function getStockForProductBatchWiseWithoutAdjustStock($product_id, $locationId = "", $batchNo = "") {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $appendSqlPur   = "";
        $appendSqlInv   = "";
        $appendSqlStk   = "";
        $appendSqlToStk = "";
        $appendSqlToUFP = "";
        $appendSqlInT   = "";

        if($locationId != "" && $batchNo != "") {
            $appendSqlPur   = "AND pp.batch_no      = '{$batchNo}'";
            $appendSqlInv   = "AND inItm.batch_no   = '{$batchNo}'";
            $appendSqlStk   = "AND st.from_location = {$locationId}
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToStk = "AND st.to_location   = {$locationId}
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToUFP = "AND st.from_location = {$locationId}
                               AND st.to_location   = 4
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlInT   = "AND batch_no         = '{$batchNo}'";
        }
        else if($locationId == "" && $batchNo != ""){
            $appendSqlPur   = "AND pp.batch_no      = '{$batchNo}'";
            $appendSqlInv   = "AND inItm.batch_no   = '{$batchNo}'";
            $appendSqlStk   = "AND st.from_location = 1
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToStk = "AND st.to_location   = 1
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlToUFP = "AND st.to_location   = 4
                               AND sth.batch_no     = '{$batchNo}'";
            $appendSqlInT   = "AND batch_no         = '{$batchNo}'";
        }
        else {
            $appendSqlStk   = "AND st.from_location = 1";
            $appendSqlToStk = "AND st.to_location   = 1";
            $appendSqlToUFP = "AND st.to_location   = 4";
        }
        
        $SQLOthersite = "
        SELECT
            (SELECT SUM(CASE 
                        WHEN pp.pack_size REGEXP '^[+-]?[0-9]+$'
                        THEN pp.qty * pp.pack_size
                        ELSE pp.qty END) AS purchased_qty
             FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$product_id}
             AND po.status != 'Cancelled'
             {$appendSqlPur}
             ) as product_qty_purchased

           ,(SELECT SUM(damage_qty) FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$product_id}
             {$appendSqlPur}
             ) as damage_qty

           ,(SELECT SUM(inItm.qty) FROM invoice_item inItm
            LEFT JOIN (`invoice` inv) ON (inv.invoice_id = inItm.invoice_id)
            WHERE inItm.record_id = {$product_id}
            AND inItm.not_add_in_stock != 1
            AND inv.status = 'Paid'
            AND inv.invoice_type = 'POS'
            {$appendSqlInv}
            ) as product_qty_sold

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlStk}
            ) as product_qty_sold_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToStk}
            ) as product_qty_got_from_stock

            ,(SELECT SUM(CASE 
                            WHEN sth.pack_size REGEXP '^[+-]?[0-9]+$'
                            THEN sth.qty * sth.pack_size
                            ELSE sth.qty END)
            FROM stock_transfer_history sth
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id = sth.stock_transfer_id)
            WHERE sth.product_id = {$product_id}
            AND st.status = 'Delivered'
            {$appendSqlToUFP}
            ) as product_qty_used_for_patient

            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item inItm) ON (inItm.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id)
            WHERE inItm.record_id = {$product_id}
            AND inv.status != 'Cancelled'
            {$appendSqlInv}
            ) as sales_return_qty
        ";
        $resultothersite = $db->sql_query($SQLOthersite);
        $rowothersite = $db->sql_fetchrow($resultothersite);

        $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_stock'] - $rowothersite['product_qty_sold'] + $rowothersite['sales_return_qty'] - $rowothersite['damage_qty'] + $rowothersite['product_qty_got_from_stock'] - $rowothersite['product_qty_used_for_patient'];

        $Values = array('OverallStock'    => $stock
                        ,'PurchasedQty'   => $rowothersite['product_qty_purchased']
                        ,'SoldQty'        => $rowothersite['product_qty_sold'] + $rowothersite['product_qty_sold_from_stock']
                        ,'SalesReturnQty' => $rowothersite['sales_return_qty']
                        ,'DamagedQty'     => $rowothersite['damage_qty']
                        ,'UsedPatientQty' => $rowothersite['product_qty_used_for_patient']);

        return $Values;
    }
}