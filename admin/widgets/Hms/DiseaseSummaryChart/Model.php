<?
class CPL_Admin_Widgets_Hms_DiseaseSummaryChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT  p.*
        FROM `prescription` p
        ";

        return $SQL;
    }

    /**
     *
     */
    /*function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 't';
        $current_year = date('Y');
        $current_month = date('m');
    }*/

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_diseaseSummaryChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}