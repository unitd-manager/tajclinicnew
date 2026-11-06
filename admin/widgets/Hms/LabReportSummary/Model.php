<?
class CPL_Admin_Widgets_Hms_LabReportSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        /*$SQL = "
        SELECT mt.*
              ,SUM(mt.fees) As amount
              ,mtv.creation_date
              ,count(mtv.medical_test_id) AS medicaltestcount
        FROM medical_test mt
        LEFT JOIN (medical_test_visit mtv) ON (mtv.medical_test_id = mt.medical_test_id)
        ";*/

        $SQL = "
        select title, sum(amount_fees) AS amount, creation_date, sum(medicaltestcount_test) AS medicaltestcount, site_id    
        from
        (
            SELECT mt.title AS title
              ,SUM(mt.fees) As amount_fees
              ,mtv.creation_date AS creation_date
              ,count(mtv.medical_test_id) AS medicaltestcount_test
              ,mtv.site_id AS site_id
            FROM medical_test mt
            LEFT JOIN (medical_test_visit mtv) ON (mtv.medical_test_id = mt.medical_test_id)
            WHERE DATE_FORMAT(mtv.creation_date, '%m') = '{$monthVal}'
            AND DATE_FORMAT(mtv.creation_date, '%Y') = '{$yearVal}'
            AND mtv.site_id = '1'
            GROUP BY title, Date_format(mtv.creation_date, '%m')
        union all
            SELECT mt.title AS title
              ,SUM(mt.fees) As amount_fees
              ,mtl.creation_date AS creation_date
              ,count(mtl.medical_test_id) AS medicaltestcount_test
              ,mtl.site_id AS site_id
            FROM medical_test mt
            LEFT JOIN (medical_test_lab mtl) ON (mtl.medical_test_id = mt.medical_test_id)
            WHERE DATE_FORMAT(mtl.creation_date, '%m') = '{$monthVal}'
            AND DATE_FORMAT(mtl.creation_date, '%Y') = '{$yearVal}'
            AND mtl.site_id = '1'
            GROUP BY title, Date_format(mtl.creation_date, '%m')
        ) A
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
        $searchVar->mainTableAlias = 'A';
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        $medical_test_id = $fn->getReqParam('medical_test_id');

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(creation_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(creation_date, '%Y') = '{$yearVal}'" ;
        }

        $searchVar->groupBy   = "title,Date_format(creation_date, '%m')";
        //$searchVar->sortOrder = "Date_format(creation_date, '%m') DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_labReportSummary');

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
        $totalOverAllCase = 0;
        $totalOverAll = 0;
        $month          = date('m');
        $year           = date('Y');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "labReportSummary__" . date("d-m-Y") . ".xls";

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

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Medical Test');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Number of Test');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');

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

        if ($monthVal != '') {
            $monthValAppendSql = "AND DATE_FORMAT(mtv.creation_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(mtv.creation_date, '%Y') = '{$yearVal}'" ;
        }


        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "WHERE mtv.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT mt.*
              ,SUM(mt.fees) As amount
              ,mtv.creation_date
              ,count(mtv.medical_test_id) AS medicaltestcount
        FROM medical_test mt
        LEFT JOIN (medical_test_visit mtv) ON (mtv.medical_test_id = mt.medical_test_id)
        {$monthValAppendSql}
        {$yearValAppendSql}
        {$appendSqlSite}
        GROUP BY mtv.title, Date_format(mtv.creation_date, '%m')
        ORDER BY Date_format(mtv.creation_date, '%m') DESC
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $month = $fn->getCPDate($row['creation_date'], 'M');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $month);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['medicaltestcount']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);

            $totalOverAllCase += $row['medicaltestcount'];
            $totalOverAll += $row['amount'];
        }

        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAllCase);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalOverAll);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}