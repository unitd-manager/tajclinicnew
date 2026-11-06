<?
class CPL_Admin_Widgets_Hms_AttendanceReportDashboard_View extends CP_Common_Lib_WidgetViewAbstract
{

    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $current_date  = date('Y-m-d');
        $current_year  = date('Y');
        $current_month = date('m');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $employee_id  = $fn->getReqParam('employee_id');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');

        $heading = "Attendance Report Last 7 Days";
        $dates = '';

        for($i=0; $i < 7; $i++){
            $datesAtt  = date('d-m-Y', strtotime(-$i.' days'));
            $dates .= "<th>{$datesAtt}</th>";
        }

        $text = "
        <h2>{$heading}</h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Name</th>
                        {$dates}
                    </tr>
                </thead>
                <tbody>
                    {$this->getRowsHTML()}
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
        
        $rows = '';

        foreach($this->model->dataArray as $row){
            $dates = '';

            for($i=0; $i < 7; $i++){
                $on_leave = '';
                $datesAtt  = date('Y-m-d', strtotime(-$i.' days'));
                $SQL = "
                SELECT a.*
                FROM `attendance` a
                WHERE a.record_date = '{$datesAtt}'
                  AND a.employee_id = {$row['employee_id']}
                ";
                $result1 = $db->sql_query($SQL);
                $row1 = $db->sql_fetchrow($result1);
                $numRows = $db->sql_numrows($result1);

                if($numRows > 0){
                    if($row1['on_leave'] == 1){
                        $on_leave = "<span class='label label-danger'>Absent</span>";
                    } else {
                        $on_leave = 'Present';                    
                    }
                }

                $dates .= "<td>{$on_leave}</td>";
            }
            
            $rows .= "
            <tr>
                <td>{$row['first_name']}</td>
                {$dates}
            </tr>
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}