<?
class CPL_Admin_Widgets_Hms_NonMovingProducts_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $current_date = date('Y-m-d');

        $jsonArray =  $this->getRowsHTML();
        $month     = $fn->getReqParam('month');
        if($month == ''){
            $start_date_formatted = $dateUtil->formatDate('2018-12-13', 'DD/MM/YYYY');
            $end_date_formatted = $dateUtil->formatDate($current_date, 'DD/MM/YYYY');
            $month = "
            <th>Start Date : {$start_date_formatted}</th>
            <th>&nbsp;&nbsp;&nbsp;End Date : {$end_date_formatted}</th>
            ";
        }

        $text = "
        <div class='float_left mr20 '><h1>Non Moving Products</h1></div>
        <div class='float_left ml20'><h1>for&nbsp;&nbsp;&nbsp; {$month}</h1></div>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Medicine Name</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    {$jsonArray['text']}
                </tbody>
            </table>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $current_date = date('Y-m-d');

        $rows   = '';
        $count  = 1;
        $stock = '';
        foreach($this->model->dataArray as $row){   
            $stock = str_replace('.00', '', $row['stock']);
            $rows .= "
            <tr>
                <td width='5%'>{$count}</td>
                <td width='70%'>{$row['title']}</td>
                <td width='25%'>{$stock}</td>
            </tr>
            ";

            $count++;
        }

        $text = "
        {$rows}
        ";

        return array('text' => $text);
    }
}