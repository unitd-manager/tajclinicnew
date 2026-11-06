<?
class CPL_Admin_Widgets_Hms_LabChartSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT mtv.*
              ,count(mtv.medical_test_id) AS medicaltestcount
              ,DATE_FORMAT(mtv.creation_date, '%b %Y') AS month
        FROM medical_test_visit mtv
        LEFT JOIN (medical_test mt) ON (mt.medical_test_id = mtv.medical_test_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'mtv';
        $month          = date('m');
        $year           = date('Y');

        $start_date = $year . '-' . $month . '-' . '01';
        $end_date = $year . '-' . $month . '-' . '31';
        $searchVar->sqlSearchVar[] = "mtv.creation_date >= '{$start_date}' AND mtv.creation_date <= '{$end_date}'";
        //$searchVar->groupBy = "mtv.creation_date";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_labChartSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}