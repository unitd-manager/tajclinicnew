<?
class CPL_Admin_Widgets_Hms_InPatientReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <h2>In Patient Report</h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S NO</th>
                        <th>Date Admitted</th>
                        <th>Patient Name</th>
                        <th>Time Admitted</th>
                        <th>Date Discharge</th>
                        <th>No of Days</th>
                        <th class='txtRight'>Amount</th>
                        <th>Status</th>
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

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $totalAmount = '';
        $datas = '';
        $count = 1;   
        $totalOverAll = 0;
        foreach($this->model->dataArray as $row){
            //$check_up_date  = $fn->getCPDate($row['check_up_date'], 'd-m-Y');
            $SQLEmpVisit = "
            SELECT SUM(consultation_fees) AS consultation_fees
                   ,employee_in_patient_id
            FROM employee_in_patient
            WHERE in_patient_id = {$row['in_patient_id']}
            ";
            $resultEmpVisit = $db->sql_query($SQLEmpVisit);
            $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

            $date_admitted  = $fn->getCPDate($row['date_admitted'], 'd-m-Y');
            $date_discharge = $fn->getCPDate($row['date_discharge'], 'd-m-Y');
            $totalAmount    = $rowEmpVisit['consultation_fees'] + $row['amount'] + $row['nursing_fees'] + $row['other_fees'];
            $name =  $row['name'] . ' / ' . substr($row['gender'],0,1) . ' / ' . $row['age_year'] . ' yrs';
            $datas .= "
            <tr>
                <td>{$count}</td>
                <td>{$date_admitted}</td>
                <td>{$name}</td>
                <td>{$row['time_admitted']}</td>
                <td>{$date_discharge}</td>
                <td>{$row['days_stayed']}</td>
                <td align='right'>{$totalAmount}</td>
                <td>{$row['status']}</td>
             </tr>   
            ";
            $totalOverAll += $totalAmount;
            $count++;
        }
       $totalOverAll = number_format($totalOverAll);
        $text = "
        {$datas}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='6'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAll}</td>
        </tr>
        ";

        return $text;
    }

}