<?
class CPL_Admin_Widgets_Hms_MfgCompanyReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT mc.*
              ,p.title AS product
              ,SUM(pop.cost_price * pop.qty) AS total_value
              ,pop.medicine_company_id
        FROM `medicine_company` mc
        LEFT JOIN (po_product pop) ON (pop.medicine_company_id = mc.medicine_company_id)
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        ";


        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'mc';
        $month          = date('m');
        $year           = date('Y');
        $current_date   = date('Y-m-d');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $site_id        = $fn->getReqParam('site_id');

        $medicine_company_id   = $fn->getReqParam('medicine_company_id');

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$end_date}'";
        }

        if ($start_date == '' && $end_date == '') {
            if ($monthVal != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(pop.creation_date, '%m') = '{$monthVal}'" ;
            }
            if ($yearVal != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(pop.creation_date, '%Y') = '{$yearVal}'" ;
            }
        }

        if ($medicine_company_id != '') {
            $searchVar->sqlSearchVar[] = "pop.medicine_company_id = '{$medicine_company_id}'" ;
        }
        $searchVar->sqlSearchVar[] = "pop.medicine_company_id = '{$medicine_company_id}'" ;

        $searchVar->sqlSearchVar[] = "pop.cost_price != 0.00" ;

        $searchVar->groupBy   = "pop.product_id";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_mfgCompanyReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     */
    function getExportToExcel(){
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn       = Zend_Registry::get('fn');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "MfgCompanyReport__" . date("d-m-Y") . ".xls";

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
        $grandTotal  = 0;
        $appendSql = '';
        $startDateAppendSql = '';
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $medicineCompanyIdAppendSql = '';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $medicine_company_id   = $fn->getReqParam('medicine_company_id');

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Medicine Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Price');

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

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pop.creation_date >= '{$start_date}' AND pop.creation_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $monthValAppendSql = "AND DATE_FORMAT(pop.creation_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(pop.creation_date, '%Y') = '{$yearVal}'" ;
        }

        if ($medicine_company_id != '') {
            $medicineCompanyIdAppendSql = "AND mc.medicine_company_id = '{$medicine_company_id}'";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND mc.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT mc.*
              ,p.title AS product
              ,SUM(pop.cost_price * pop.qty) AS total_value
        FROM `medicine_company` mc
        LEFT JOIN (po_product pop) ON (pop.medicine_company_id = mc.medicine_company_id)
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        WHERE pop.cost_price != 0.00
        AND mc.medicine_company_id = '{$medicine_company_id}'
        {$appendSqlSite}
        {$monthValAppendSql}
        {$yearValAppendSql}
        {$startDateAppendSql}
        {$medicineCompanyIdAppendSql}                
        GROUP BY pop.product_id
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $total_value = number_format($row['total_value'], 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['product']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_value);

            $grandTotal += $row['total_value'];
        }

        $grandTotal = number_format($grandTotal, 2);

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $grandTotal);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}