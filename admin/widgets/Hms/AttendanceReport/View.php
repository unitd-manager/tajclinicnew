<?
class CPL_Admin_Widgets_Hms_AttendanceReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        //$start_date   = $fn->getReqParam('start_date');
        //$end_date     = $fn->getReqParam('end_date');
        $employee_id  = $fn->getReqParam('employee_id');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');

        if($monthVal != ''){
            $current_month = $monthVal;
        }

        if($yearVal != ''){
            $current_year = $yearVal;
        }

        $heading = "Attendance Report";
        $dates = '';

        $start_date = $current_year . '-' . $current_month . '-' . '01';
        //$end_date   = $current_year . '-' . $current_month . '-' . '31';
        $end_date   = date("Y-m-t", strtotime($start_date));

        for($i=$start_date; $i <= $end_date; $i++){
            $datesAtt  = date('d', strtotime($i));
            $datesDay  = date('l', strtotime($i));

            if($datesDay == "Sunday") {
                $bgcolor = "style='background-color:#fe4d4d !important'";
            }else {
                $bgcolor = "";
            }

            $dates .= "<th class='txtCenter' {$bgcolor}>{$datesAtt}</th>";
        }

        $text = "
        <h2>{$heading}</h2>
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Name [Hrs]</th>
                        <th></th>
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
        $cp_site_id = $fn->getSessionParam('cp_site_id');
        $rows = '';
        $month  = date('m');
        $year   = date('Y');
          
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        if($monthVal != ''){
            $month = $monthVal;
        }

        if($yearVal != ''){
            $year = $yearVal;
        }

        $daysin_month = cal_days_in_month(CAL_GREGORIAN, $month, $yearVal);
        // jcheck = 1(day shift), 2(night), 3(double shift)
        foreach($this->model->dataArray as $row){
            $jCheck = 2;
            $jvar   = 1;

            $SQLAttendCount = "
            SELECT count(a.employee_id) as days_present
            FROM `attendance` a
            WHERE (on_leave IS NULL OR on_leave = 0)
            AND a.employee_id = {$row['employee_id']}
            AND DATE_FORMAT(a.record_date, '%m') = '{$month}'
            AND DATE_FORMAT(a.record_date, '%Y') = '{$year}'
            ";
            $resultAttendCount = $db->sql_query($SQLAttendCount);
            $rowAttendCount    = $db->sql_fetchrow($resultAttendCount);

            if($row['employee_type'] == "Double Shift" && $cp_site_id == 1) {
                $jCheck = 3;
            }
            elseif ($cp_site_id == 2) {
                $jCheck = 2;
                $jvar = 2;
            }

            for($j = $jvar; $j <= $jCheck; $j++){
                $dates = '';
                $current_date  = date('Y-m-d');
                $current_year  = date('Y');
                $current_month = date('m');

                if($monthVal != ''){
                    $current_month = $monthVal;
                }

                if($yearVal != ''){
                    $current_year = $yearVal;
                }

                $start_date = $current_year . '-' . $current_month . '-' . '01';
                //$end_date   = $current_year . '-' . $current_month . '-' . '31';
                $end_date   = date("Y-m-t", strtotime($start_date));
                $totalTimeSecs  = '';
                $totalTimeSecs2 = '';
                $datesAttDay    = 0;

                for($i=$start_date; $i <= $end_date; $i++){
                    $on_leave = '';
                    $datesAttDay = date('d', strtotime($i));
                    $datesAtt    = date('Y-m-d', strtotime($i));

                    $SQL = "
                    SELECT a.*
                    FROM `attendance` a
                    WHERE a.record_date = '{$datesAtt}'
                    AND a.employee_id = {$row['employee_id']}
                    ";
                    $result1 = $db->sql_query($SQL);
                    $row1 = $db->sql_fetchrow($result1);
                    $numRows = $db->sql_numrows($result1);

                    //if($numRows > 0){
                    if($row1['record_date'] != ''){
                        if($row1['on_leave'] == 1){
                            $on_leave = "<span class='label label-danger'>A</span>";
                        } else {
                            $record_sign_in        = $row1['time_in_day_shift'];
                            $record_sign_out       = $row1['leave_time_day_shift'];
                            $time1                 = date("H:i", strtotime($record_sign_in));
                            $time2                 = date("H:i", strtotime($record_sign_out));
                            list($hours, $minutes) = explode(':', $time1);
                            $startTimestamp        = mktime($hours, $minutes);
                            list($hours, $minutes) = explode(':', $time2);
                            $endTimestamp          = mktime($hours, $minutes);
                            $seconds               = $endTimestamp - $startTimestamp;
                            $minutes               = ($seconds / 60) % 60;
                            $hours                 = floor($seconds / (60 * 60));
                            
                            if($row1['leave_time_day_shift'] != '00:00:00' && $row1['leave_time_day_shift'] != ''){
                                $total_time = sprintf("%02d", $hours). ":" .sprintf("%02d", $minutes);
                            } else {
                                $total_time = '';
                            }

                            $record_sign_in_day2  = $row1['time_in_double_shift_morning'];
                            $record_sign_out_day2 = $row1['leave_time_double_shift_morning'];
                            $time1_day2           = date("H:i", strtotime($record_sign_in_day2));
                            $time2_day2           = date("H:i", strtotime($record_sign_out_day2));
                            list($hours_day2, $minutes_day2) = explode(':', $time1_day2);
                            $startTimestamp_day2 = mktime($hours_day2, $minutes_day2);
                            list($hours_day2, $minutes_day2) = explode(':', $time2_day2);
                            $endTimestamp_day2 = mktime($hours_day2, $minutes_day2);
                            $seconds_day2      = $endTimestamp_day2 - $startTimestamp_day2;
                            $minutes_day2      = ($seconds_day2 / 60) % 60;
                            $hours_day2        = floor($seconds_day2 / (60 * 60));
                            
                            if($row1['leave_time_double_shift_morning'] != '00:00:00' && $row1['leave_time_double_shift_morning'] != ''){
                                $total_time_day2 = sprintf("%02d", $hours_day2). ":" .sprintf("%02d", $minutes_day2);
                            } else {
                                $total_time_day2 = '';
                            }

                            $record_sign_in_day3  = $row1['time_in_double_shift_evening'];
                            $record_sign_out_day3 = $row1['leave_time_double_shift_evening'];
                            $time1_day3           = date("H:i", strtotime($record_sign_in_day3));
                            $time2_day3           = date("H:i", strtotime($record_sign_out_day3));
                            list($hours_day3, $minutes_day3) = explode(':', $time1_day3);
                            $startTimestamp_day3 = mktime($hours_day3, $minutes_day3);
                            list($hours_day3, $minutes_day3) = explode(':', $time2_day3);
                            $endTimestamp_day3 = mktime($hours_day3, $minutes_day3);
                            $seconds_day3      = $endTimestamp_day3 - $startTimestamp_day3;
                            $minutes_day3      = ($seconds_day3 / 60) % 60;
                            $hours_day3        = floor($seconds_day3 / (60 * 60));
                            
                            if($row1['leave_time_double_shift_evening'] != '00:00:00' && $row1['leave_time_double_shift_evening'] != ''){
                                $total_time_day3 = sprintf("%02d", $hours_day3). ":" .sprintf("%02d", $minutes_day3);
                            } else {
                                $total_time_day3 = '';
                            }

                            $record_sign_in_night  = $row1['time_in_night_shift'];
                            $record_sign_out_night = $row1['leave_time_night_shift'];
                            $time1_night           = date("H:i", strtotime($record_sign_in_night));
                            $time2_night           = date("H:i", strtotime($record_sign_out_night));
                            list($hours_night, $minutes_night) = explode(':', $time1_night);
                            $startTimestamp_night  = mktime($hours_night, $minutes_night);
                            list($hours_night, $minutes_night) = explode(':', $time2_night);
                            $endTimestamp_night    = mktime($hours_night, $minutes_night);
                            $seconds_night         = $endTimestamp_night - $startTimestamp_night;
                            $minutes_night         = ($seconds_night / 60) % 60;
                            $hours_night           = floor($seconds_night / (60 * 60));
                            
                            if($row1['leave_time_night_shift'] != '00:00:00' && $row1['leave_time_night_shift'] != ''){
                                $total_time_night = sprintf("%02d", $hours_night). ":" .sprintf("%02d", $minutes_night);
                            } else {
                                $total_time_night = '';
                            }

                            if($j == 1) {
                                if($row['employee_type'] == "Double Shift") {
                                    if(($row1['time_in_double_shift_morning'] != "" && $row1['time_in_double_shift_morning'] > "00:00:00") || ($row1['leave_time_double_shift_morning'] != "" && $row1['leave_time_double_shift_morning'] > "00:00:00")) {
                                        //$total_time_day_test = $this->sum_the_two_time($total_time_day2, $total_time);
                                        $total_time_ds_morning = $total_time_day2;
                                        if($total_time_ds_morning == ""){
                                            $total_time_ds_morning = "00:00";
                                        }

                                        list($hour,$minute) = explode(':', $total_time_ds_morning);
                                        $totalTimeSecs += $hour*3600;
                                        $totalTimeSecs += $minute*60;
                                        $on_leave = 'P';

                                        /*if($total_time_ds_morning > "00:00") {
                                            if($total_time_ds_morning < "06:00") {
                                                $on_leave = '1/2';
                                            }
                                        }*/
                                    }
                                } else {

                                    if($row1['time_in_day_shift'] != "") {

                                        if($total_time == ""){
                                            $total_time = "00:00";
                                        }

                                        list($hour,$minute) = explode(':', $total_time);
                                        $totalTimeSecs += $hour*3600;
                                        $totalTimeSecs += $minute*60;

                                        $on_leave = 'P';
                                        if($total_time > "00:00") {
                                            if($total_time < "06:00" && $cp_site_id == 1) {
                                                $on_leave = '1/2';
                                            }
                                        }
                                    }
                                }
                            }

                            if($j == 2) {

                                if($row['employee_type'] == "Double Shift") {
                                    if(($row1['time_in_double_shift_evening'] != "" && $row1['time_in_double_shift_evening'] > "00:00:00") || ($row1['leave_time_double_shift_evening'] != "" && $row1['leave_time_double_shift_evening'] > "00:00:00")) {
                                        //$total_time_day_test = $this->sum_the_two_time($total_time_day2, $total_time);
                                        $total_time_ds_evening = $total_time_day3;
                                        if($total_time_ds_evening == ""){
                                            $total_time_ds_evening = "00:00";
                                        }
                                        list($hour,$minute) = explode(':', $total_time_ds_evening);
                                        $totalTimeSecs += $hour*3600;
                                        $totalTimeSecs += $minute*60;

                                        $on_leave = 'P';
                                        /*if($total_time_ds_evening > "00:00") {
                                            if($total_time_ds_evening < "06:00") {
                                                $on_leave = '1/2';
                                            }
                                        }*/
                                    }
                                } else {

                                    if($row1['time_in_night_shift'] != "") {
                                        if($total_time_night == ""){
                                            $total_time_night = "00:00";
                                        }

                                        list($hour,$minute) = explode(':', $total_time_night);
                                        $totalTimeSecs += $hour*3600;
                                        $totalTimeSecs += $minute*60;

                                        $on_leave = 'P';
                                        if($total_time_night > "00:00") {
                                            if($total_time_night < "06:00" && $cp_site_id == 1) {
                                                $on_leave = '1/2';
                                            }
                                        }
                                    }
                                }
                            }

                            if($j == 3) {
                                /*if($row['employee_type'] == "Double Shift") {
                                    if($row1['time_in_double_shift_morning'] != "" || $row1['time_in_double_shift_evening'] != "") {
                                        $total_time_day_test = $this->sum_the_two_time($total_time_day2, $total_time);
                                        if($total_time_day_test == ""){
                                            $total_time_day_test = "00:00";
                                        }
                                        list($hour,$minute) = explode(':', $total_time_day_test);
                                        $totalTimeSecs += $hour*3600;
                                        $totalTimeSecs += $minute*60;
                                        $on_leave = 'P';
                                    }
                                }*/

                                if($row['employee_type'] == "Double Shift") {
                                    if($row1['time_in_night_shift'] != "") {
                                        if($total_time_night == ""){
                                            $total_time_night = "00:00";
                                        }

                                        list($hour,$minute) = explode(':', $total_time_night);
                                        $totalTimeSecs += $hour*3600;
                                        $totalTimeSecs += $minute*60;

                                        $on_leave = 'P';
                                        if($total_time_night > "00:00") {
                                            if($total_time_night < "06:00" && $cp_site_id == 1) {
                                                $on_leave = '1/2';
                                            }
                                        }
                                    }
                                }
                            }

                            $total_time_full_shift = $this->sum_the_three_time($total_time, $total_time_day2, $total_time_night);

                            if($total_time_full_shift == ""){
                                $total_time_full_shift = "00:00";
                            }

                            list($hour2,$minute2) = explode(':', $total_time_full_shift);
                            $totalTimeSecs2 += $hour2*3600;
                            $totalTimeSecs2 += $minute2*60;
                        }
                    }
                    //}
                    $bgcolor = '';
                    /*if ($on_leave == '' && $numRows > 0) {
                       $bgcolor = "bgcolor='#85daff'"; 

                        if($row['employee_type'] == "Double Shift") {
                            if($j != 3) {
                                $on_leave = "<span class='label label-danger'>A</span>";
                            }
                        }
                    } */

                    $dates .= "<td style='border:1px solid grey;' align='center' {$bgcolor}>
                                    {$on_leave}
                                </td>";
                }

                $headingTd = "";
                if($row['employee_type'] == 'Double Shift') {
                    if($j == 1 && $cp_site_id == 1) {
                        $headingTd = "DSM";
                    }

                    if($j == 2 && $cp_site_id == 1) {
                        $headingTd = "DSE";
                    }

                    if($j == 3 && $cp_site_id == 1) {
                        $headingTd = "N";
                    }
                } else {
                    if($j == 1 && $cp_site_id == 1) {
                        $headingTd = "D";
                    }

                    if($j == 2 && $cp_site_id == 1) {
                        $headingTd = "N";
                    }

                    if($j == 3 && $cp_site_id == 1) {
                        $headingTd = "DS";
                    }
                }

                if($j == 1 && ($cp_site_id == 2 || $cp_site_id == 3 )) {
                    $headingTd = "Morn";
                }

                if($j == 2 && ($cp_site_id == 2 || $cp_site_id == 3 )) {
                    $headingTd = "Eveng";
                }

                $hours    = floor($totalTimeSecs/3600);
                $totalTimeSecs -= $hours*3600;
                $minutes  = floor($totalTimeSecs/60);

                if($minutes <= 9) {
                    $minutes = "0".$minutes;
                }

                if($hours <= 9) {
                    $hours = "0".$hours;
                }

                $total_hrs = $hours.':'.$minutes;


                $total_time_full_shift_print = '';
                if($j == 1 || ($j == 2 && $cp_site_id == 2)) {
                    $hour2    = floor($totalTimeSecs2/3600);
                    $totalTimeSecs2 -= $hour2*3600;
                    $minutes2  = floor($totalTimeSecs2/60);

                    if($minutes2 <= 9) {
                        $minutes2 = "0".$minutes2;
                    }

                    if($hour2 <= 9) {
                        $hour2 = "0".$hour2;
                    }

                    $total_time_full_shift = $hour2.':'.$minutes2;
                    $total_time_full_shift_print = $total_time_full_shift;
                }
                
                $employee_name = '';
                $days_attended = '';
                if($j == 1 || ($j == 2 && $cp_site_id == 2)) {
                    $employee_name = $row['first_name'].' ['.$total_time_full_shift_print.']';
                    
                    $days_attended = '- [' . $rowAttendCount['days_present'] . '/' . $daysin_month .']';
                }

                $rows .= "
                <tr>
                    <td>{$employee_name}{$days_attended}</td>
                    <td bgcolor='#F06292'>{$headingTd}</td>
                    {$dates}
                </tr>
                ";
            }

            $datesAttDay = $datesAttDay + 2;

            $rows .= "<tr class='seperateAttendanceReportShift'>
                        <td colspan='{$datesAttDay}'></td>
                      </tr>";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    function sum_the_two_time($time1, $time2) {
        $times = array($time1, $time2);
        $seconds = 0;

        foreach ($times as $time) {

            if($time != ""){
                list($hour,$minute) = explode(':', $time);
                $seconds += $hour*3600;
                $seconds += $minute*60;
            }
        }

        $hours    = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);

        if($minutes <= 9) {
            $minutes = "0".$minutes;
        }

        if($hours <= 9) {
            $hours = "0".$hours;
        }

        $total_hrs = $hours.':'.$minutes;

        if($hours == "00" && $minutes == "00"){
            $total_hrs = "";
        }

        return "{$total_hrs}";
    }

    function sum_the_three_time($time1, $time2, $time3) {
        $times = array($time1, $time2, $time3);
        $seconds = 0;

        foreach ($times as $time) {

            if($time != ""){
                list($hour,$minute) = explode(':', $time);
                $seconds += $hour*3600;
                $seconds += $minute*60;
            }
        }

        $hours    = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);

        if($minutes <= 9) {
            $minutes = "0".$minutes;
        }

        if($hours <= 9) {
            $hours = "0".$hours;
        }

        $total_hrs = $hours.':'.$minutes;

        if($hours == "00" && $minutes == "00"){
            $total_hrs = "";
        }

        return "{$total_hrs}";
    }
}