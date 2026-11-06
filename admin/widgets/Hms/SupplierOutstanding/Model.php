<?
class CPL_Admin_Widgets_Hms_SupplierOutstanding_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        
        $SQL = "
        SELECT po.*
              ,su.company_name
        FROM purchase_order po
        LEFT JOIN (`supplier` su) ON (su.supplier_id = po.company_id_supplier)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'po';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $start_date    = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');

        if ($start_date != '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "po.invoice_date  >= '{$start_date}' AND po.invoice_date  <= '{$end_date}'";
        }

        //$searchVar->sqlSearchVar[] = "(po.payment_status = 'Due' OR po.payment_status IS NULL OR po.payment_status = 'Partially Paid')";
        $searchVar->sqlSearchVar[] = "po.company_id_supplier > 0";
        $searchVar->sqlSearchVar[] = "po.status != 'Cancelled'";
        $searchVar->groupBy   = "po.company_id_supplier";
        $searchVar->sortOrder = "su.company_name";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_supplierOutstanding');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}