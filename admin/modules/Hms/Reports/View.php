<?
class CPL_Admin_Modules_Hms_Reports_View extends CP_Admin_Modules_Hms_Reports_View
{

    var $jssKeys = array('jqForm-3.15', 'chosen-1.5.1', 'jqUITimePickerAddon-0.9.3');

    /**
     *
     */
    function getList() {
        $listObj = Zend_Registry::get('listObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $rowCounter = 0;
        $rows = "";

        $text = "
        <div class='homeClass'><a href='index.php?_topRm=main&module=hms_home'>Home</a></div>
        <div class='floatbox'>
            <div class='float_left'>
                <a href='#' class='cpBack'>back</a>
            </div>
            <div class='float_right'>
                {$this->getReportsDropdown()}
            </div>
        </div>
        <div id='reportSearchPanel' class='ui-corner-all'>
        </div>
        <div id='reportContainer' class='ui-corner-all'>
        </div>
        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
        <script type='text/javascript'>
            google.load('visualization', '1.0', {'packages':['corechart']});
        </script>
		";

        return $text;
    }

    /**
     *
     */
    function getReportsDropdown() {
        $listObj = Zend_Registry::get('listObj');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');

        $rowCounter = 0;

        $repArrSrc = $this->model->reportsArray;

        $repArr = array();
        foreach($repArrSrc AS $key => $val){
            $repArr[$key] = $val['title'];
        }
        /*
        <option value='dailyCollectionReport'>Daily Collection Report</option>
        <option value='revenueByDay'>Revenue By Day</option>
        <option value='revenueByMonth'>Revenue By Month</option>
        */
                        /*<optgroup label='Standard Reports'>
                            <option value='attendanceReport'>Attendance Report</option>
                            <option value='patientVisitSummary'>Patient Visit Summary</option>
                            <option value='labReport'>Lab Report</option>
                            <option value='inPatientReport'>In Patient Report</option>
                            <option value='vaccinationReport'>Vaccination Report</option>
                            <option value='mfgCompanyReport'>Mfg Company Report</option>
                            <option value='mOLReport'>MOL Report</option>
                            <option value='patientVisitByMonth'>Patient Visit Location Wise</option>
                            <option value='labReportSummary'>Lab Report Summary</option>
                        </optgroup>
                        <optgroup label='Financial Reports'>
                            <option value='drPaymentReport'>Dr Payment Report</option>
                            <option value='supplierOutstandingReport'>Supplier Outstanding Report</option>
                            <option value='pharmacyDailySales'>Pharmacy Daily Sales</option>
                            <option value='productSalesReport'>Product Sales Report</option>
                            <option value='stockReport'>Stock Report</option>
                        </optgroup>*/

        $text = "
        <table class='search'>
            <tr>
                <td>
                    <select name='report' class='report'>
                        <option value=''>Select a Report</option>
                        <optgroup label='GST Reports'>
                            <option value='purchaseGstReport'>Purchase GST Report</option>
                            <option value='salesGstReport'>Sales GST Report</option>
                        </optgroup>
                        <optgroup label='Financial Reports'>
                            <option value='productSalesReport'>Product Sales Report</option>
                            <option value='nonMovingProducts'>Non Moving Products</option>
                        </optgroup>
                    </select>
                </td>
            </tr>
        </table>
        ";

        /*$text = "
        <table class='search'>
        <tr>
            <td>
                <select name='report' class='report'>
                    <option value=''>Please Choose the Report</option>
                    <optgroup label='Financial Reports'>
                        <option value='summaryPurchaseSales'>Summary Purchase Sales</option>
                        <option value='summaryPurchase'>Summary Purchase</option>
                        <option value='summarySales'>Summary Sales</option>
                    </optgroup>
                </select>
            </td>
        </tr>
        </table>
        ";*/

        return $text;
    }

    /**
     *
     */
    function getSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $pager = Zend_Registry::get('pager');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $reportsArray = $this->model->reportsArray;

        $report = $fn->getReqParam('report');
        $url = "";

        $year         	        = $fn->getReqParam('year');
        $month        	        = $fn->getReqParam('month');
        $sort_order   	        = $fn->getReqParam('sort_order');
        $search_by    	        = $fn->getReqParam('search_by');
        $active_start 	        = $fn->getReqParam('active_start');
        $active_end   	        = $fn->getReqParam('active_end');
        $course_id    	        = $fn->getReqParam('course_id');
        $subject_id   	        = $fn->getReqParam('subject_id');
        $batch_id     	        = $fn->getReqParam('batch_id');
        $status       	        = $fn->getReqParam('status');
        $staff_id     	        = $fn->getReqParam('staff_id');
        $employee_visit         = $fn->getReqParam('employee_visit');
        $employee_id            = $fn->getReqParam('employee_id');
        $teacher_id   	        = $fn->getReqParam('teacher_id');
        $product_id   	        = $fn->getReqParam('product_id');
        $company_id   	        = $fn->getReqParam('company_id');
        $site_id                = $fn->getReqParam('site_id');
        $patient_information_id = $fn->getReqParam('patient_information_id');
        $bill_type              = $fn->getReqParam('bill_type');
        $on_leave               = $fn->getReqParam('on_leave');
        $supplier_id            = $fn->getReqParam('supplier_id');
        $medicine_company_id    = $fn->getReqParam('medicine_company_id');
        $start_date   	        = $fn->getReqParam('start_date');
        $end_date 	  	        = $fn->getReqParam('end_date');
        $time_in                = $fn->getReqParam('time_in');
        $time_out               = $fn->getReqParam('time_out');

        /*if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }*/

        $location = '';
        if ($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $sqlLocation = "
            SELECT s.site_id
                  ,s.title
            FROM site s
            WHERE s.published = 1
            ORDER BY site_id
            ";
            $location_id    = $fn->getReqParam('location_id');
            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            /*if ($location_id == '') {
                $location_id = $cpSiteIdSession;
            }*/

            $location = "
            <td class='fieldValue'>
                <select name='location_id'>
                    <option value=''>Select Location</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlLocation, $location_id)}
                </select>
            </td>
            ";
        }

        $searchFldsArr = $reportsArray[$report]['searchFlds'];

        if ($year == '') {
            $year = date('Y');
        }

        $arr = array (
                '01' => 'January'
               ,'02' => 'February'
               ,'03' => 'March'
               ,'04' => 'April'
               ,'05' => 'May'
               ,'06' => 'June'
               ,'07' => 'July'
               ,'08' => 'August'
               ,'09' => 'September'
               ,'10' => 'October'
               ,'11' => 'November'
               ,'12' => 'December'
               );

        if ($month == '') {
            $month = date('m');
        }

        $spArrayDuration = array (
             'Past 3 Months'
            ,'Past 6 Months'
            ,'Past 1 Year'
        );

        $spArrayBillType = array (
             'Individual'
            ,'Company'
            ,'Panel'
        );

        $onLeave = array (
             'Present'
            ,'Absent'
        );

        $rows = '';
        foreach($searchFldsArr AS $searchFld){

            if ($report == 'patientVisitSummary' ||
                $report == 'dailyCollectionReport')
            {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS contact_date
                FROM  patient_visit pv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

                $sqlemployee_visit = "

                SELECT ev.employee_id
                      ,(CASE 
                        WHEN e.salutation = 'Dr' 
                        THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                        ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                        END) AS employee_name
                FROM employee_visit ev
                LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
                GROUP BY ev.employee_id
                ";

                /*$rows .= "
                <td>
                    <select name='employee_id' class='leadStaffFilter'>
                        <option value=''>Doctor/Nurse</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlemployee_visit, $employee_id)}
                    </select>
                </td>
                ";*/

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                /*$rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";*/
            }
            if($report == 'invoiceSummary') {

                /*$sqlPI ="
                SELECT p.patient_information_id
                      ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS patient_name
                FROM patient_information p
                ORDER BY patient_name ASC
                ";

                $rows .= "
                <td>
                    <select name='patient_information_id' class='invoiceSummaryFilter'>
                        <option value=''>Select Patient</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlPI, $patient_information_id)}
                    </select>
                </td>
                ";

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                $rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";*/

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
                FROM invoice i
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

                /*
                <td>
                    <input type='text' name='company_patient_search' class='invoiceSummary' />
                    <input type='hidden' name='company_patient_id' value=''/> 
                </td>
                */

                $rows .= "
                <td>
                    <select name='bill_type' class='invoiceSummaryFilter'>
                        <option value=''>Bill Type</option>
                        {$cpUtil->getDropDown1($spArrayBillType, $bill_type)}
                    </select>
                    <input type='hidden' name='bill_type_hidden' value=''/>
                </td>
                ";

                $sqlPI ="
                SELECT p.patient_information_id AS company_patient_id
                      ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS patient_name
                FROM patient_information p
                ORDER BY patient_name ASC
                ";
                $resultPI = $db->sql_query($sqlPI);
                $patientNameOption = '';
                while($rowPI    = $db->sql_fetchrow($resultPI)){
                    $patientNameOption .= "<option value='{$rowPI['company_patient_id']}'>{$rowPI['patient_name']}</option>";
                }

                $rows .= "
                <td  class='individualCombobox'>
                <script type='text/javascript'>
                    var config = {
                        '.chosen-select'           : {},
                        '.chosen-select-deselect'  : {allow_single_deselect:true},
                        '.chosen-select-no-single' : {disable_search_threshold:10},
                        '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                        '.chosen-select-width'     : {width:'95%'}
                    }
                    for (var selector in config) {
                        $(selector).chosen(config[selector]);
                    }
                </script>
                    <div>
                      <em>Into This</em>
                      <select name='company_patient_id' data-placeholder='Choose Patient...' class='chosen-select'>
                        <option value=''>Please Select</option>
                        {$patientNameOption}
                      </select>
                   </div>
                </td>
                ";
                //<select name='company_patient_id' class='invoiceSummary'>
                    //</select>
                        //<option value=''>Select Patient / Company</option>
                        //{$dbUtil->getDropDownFromSQLCols2($db, $sqlPI, $patient_information_id)}
                    //<input type='text' name='company_patient_id' class='invoiceSummary' />

            }
            if($report == 'stockReport') {

            }

            if($report == 'companyInvoiceSummary') {

                $sqlCompany = "
                SELECT company_id
                      ,company_name
                FROM company
                WHERE category = 'Client'
                ";

                $rows .= "
                <td>
                    <select name='company_id' class='companyFilter'>
                        <option value=''>Select Company</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
                    </select>
                </td>
                ";
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
                FROM invoice i
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

            }

            if($report == 'dutyRosterReport') {

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

            }
            if($report == 'expenseReport') {

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(e.creation_date , '%Y') AS expense_year
                FROM expense e
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";
                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                /*$rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";*/

            }

            if ($report == 'revenueByDay')  {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS contact_date
                FROM  patient_visit pv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                /*$rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";*/

            }

            if ($report == 'revenueByMonth')  {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y')
                FROM  invoice i
                ";

                $rows .= "
                <td>
                    <select name='year' class='year'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                /*$rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";*/
            }
            else if ($report == 'treatmentHistory' || $report == 'visitByDay') {
                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS contact_date
                FROM  patient_visit pv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

                $sqlBranch = "
                SELECT site_id
                      ,title
                FROM site
                WHERE published = 1
                ";

                /*$rows .= "
                <td>
                    <select name='site_id' class='leadStaffFilter'>
                        <option value=''>Select Branch</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $site_id)}
                    </select>
                </td>
                ";*/

            }else if ($report == 'labReportSummary') {

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(mtv.creation_date, '%Y') AS Year
                FROM  medical_test_visit mtv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";
                
                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";


            }else if ($report == 'labReport') {

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(mtv.creation_date, '%Y') AS Year
                FROM  medical_test_visit mtv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

            }else if ($report == 'vaccinationReport') {

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(vv.due_date, '%Y') AS Year
                FROM  vaccination_visit vv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";


            }else if ($report == 'drPaymentReport') {

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(mtv.creation_date, '%Y') AS Year
                FROM  medical_test_visit mtv
                ";

                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

            }else if ($report == 'inPatientReport') {

                $sqlYear = "
                SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS Year
                FROM  patient_visit pv
                ";
                //SELECT DISTINCT DATE_FORMAT(inp.date_admitted, '%Y') AS Year
               // FROM  in_patient inp
                $rows .= "
                <td>
                    <select name='year' class='leadStaffYearFilter'>
                        <option value=''>Choose Year</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                    </select>
                </td>
                ";

                $rows .= "
                <td>
                    <select name='month' class='ml10 mr10 month'>
                        <option value=''>Choose Month</option>
                        {$cpUtil->getDropDownFromArr($arr, $month)}
                    </select>
                </td>
                ";

                  $rows .= "
                <td class='dateRange'>
                    From Date:
                    <input type='text' allowEdit='1' name='start_date' class='fld_date'
                    id='fld_start_date' value='{$start_date}' />
                    To Date:
                    <input type='text' allowEdit='1' name='end_date' class='fld_date'
                    id='fld_end_date' value='{$end_date}' />
                </td>
                ";

              

            }

        if ($report == 'patientVisitByMonth') {

            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS Year
            FROM  patient_visit pv
            ";

            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td class='dateRange'>
                From Date:
                <input type='text' allowEdit='1' name='start_date' class='fld_date'
                id='fld_start_date' value='{$start_date}' />
                To Date:
                <input type='text' allowEdit='1' name='end_date' class='fld_date'
                id='fld_end_date' value='{$end_date}' />
            </td>
            ";

        }

        if ($report == 'attendanceReport')
        {
            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(record_date, '%Y') AS attendance_date
            FROM  attendance
            ";

            $rows .= "
            <!--<td>
                <select name='on_leave' class='invoiceSummaryFilter'>
                    <option value=''>Present / Absent</option>
                    {$cpUtil->getDropDown1($onLeave, $on_leave)}
                </select>
            </td>-->
            ";

            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <!--<td class='dateRange'>
                From Date:
                <input type='text' allowEdit='1' name='start_date' class='fld_date'
                id='fld_start_date' value='{$start_date}' />
                To Date:
                <input type='text' allowEdit='1' name='end_date' class='fld_date'
                id='fld_end_date' value='{$end_date}' />
            </td>-->
            ";

            $site_id = $fn->getSessionParam('cp_site_id');

            $appendSql = "";
            if ($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $appendSql = "AND e.site_id = {$site_id}";
            }

            $sqlAttendanceStaff = "
            SELECT e.employee_id
                  ,UPPER(e.first_name) AS staff_name
            FROM `employee` e
            WHERE e.category = 'Nurse'
            AND e.status = 'Active'
            {$appendSql}
            ORDER BY e.first_name
            ";

            $rows .= "
            <!--<td>
                <select name='employee_id' class='leadStaffFilter'>
                    <option value=''>Doctor/Nurse</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlAttendanceStaff, $employee_id)}
                </select>
            </td>-->
            ";

        }
        if ($report == 'balanceSheetReport')
        {
            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS Year
            FROM  patient_visit pv
            ";

            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td class='dateRange'>
                From Date:
                <input type='text' allowEdit='1' name='start_date' class='fld_date'
                id='fld_start_date' value='{$start_date}' />
                To Date:
                <input type='text' allowEdit='1' name='end_date' class='fld_date'
                id='fld_end_date' value='{$end_date}' />
            </td>
            ";

        }

        if ($report == 'balanceSheetLabReport')
        {
            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS Year
            FROM  patient_visit pv
            ";

            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td class='dateRange'>
                From Date:
                <input type='text' allowEdit='1' name='start_date' class='fld_date'
                id='fld_start_date' value='{$start_date}' />
                To Date:
                <input type='text' allowEdit='1' name='end_date' class='fld_date'
                id='fld_end_date' value='{$end_date}' />
            </td>
            ";

        }

        if ($report == 'balanceSheetPharmacyReport')
        {
            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(pv.check_up_date, '%Y') AS Year
            FROM  patient_visit pv
            ";

            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td class='dateRange'>
                From Date:
                <input type='text' allowEdit='1' name='start_date' class='fld_date'
                id='fld_start_date' value='{$start_date}' />
                To Date:
                <input type='text' allowEdit='1' name='end_date' class='fld_date'
                id='fld_end_date' value='{$end_date}' />
            </td>
            ";

        }

        if ($report == 'pharmacyDailySales') {

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";
            
        }

        if ($report == 'nonMovingProducts') {

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Duration</option>
                    {$cpUtil->getDropDown1($spArrayDuration, $month)}
                </select>
            </td>
            ";
            
        }

        if ($report == 'productSalesReport') {

            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
            FROM invoice i
            ";

            $rows .= "
            <td class='dateRange'>
                From Date:
                <input type='text' allowEdit='1' name='start_date' class='fld_date'
                id='fld_start_date' value='{$start_date}' />
                To Date:
                <input type='text' allowEdit='1' name='end_date' class='fld_date'
                id='fld_end_date' value='{$end_date}' />
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='year' class='year'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";
            
        }

        if($report == 'mfgCompanyReport') {

            $sqlCompany = "
            SELECT medicine_company_id
                  ,medicine_company_name
            FROM medicine_company
            order by medicine_company_name
            ";

            $rows .= "
            <td>
                <select name='medicine_company_id' class='companyFilter'>
                    <option value=''>Select Company</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $medicine_company_id)}
                </select>
            </td>
            ";
            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(pop.creation_date, '%Y') AS medicine_year
            FROM po_product pop
            ";

            $rows .= "
            <td>
                <select name='year' class='year'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td class='dateRange'>
                From Date:
                <input type='text' allowEdit='1' name='start_date' class='fld_date'
                id='fld_start_date' value='{$start_date}' />
                To Date:
                <input type='text' allowEdit='1' name='end_date' class='fld_date'
                id='fld_end_date' value='{$end_date}' />
            </td>
            ";

        }

        if ($report == 'mOLReport') {


            $sqlSupplier = "
            SELECT  c.supplier_id 
                   ,c.company_name
            FROM supplier c
            WHERE c.company_name != ''
            ORDER BY c.company_name ASC
            ";


            $rows .= "
            <td>
                <select name='supplier_id' class='supplierFilter'>
                    <option value=''>Select Supplier</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
                </select>
            </td>
            ";

        }


        if ($report == 'supplierOutstandingReport') {

            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(po.purchase_order_date, '%Y') AS Year
            FROM  purchase_order po
            ";

            $sqlSupplier = "
            SELECT  c.supplier_id 
                   ,c.company_name
            FROM supplier c
            WHERE c.company_name != ''
            ORDER BY c.company_name ASC
            ";

            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='supplier_id' class='supplierFilter'>
                    <option value=''>Select Supplier</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
                </select>
            </td>
            ";

        }

        if ($report == 'purchaseGstReport') {

            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(po.purchase_order_date, '%Y') AS Year
            FROM  purchase_order po 
            WHERE po.purchase_order_date IS NOT NULL
            ";

            $sqlSupplier = "
            SELECT  c.supplier_id 
                   ,c.company_name
            FROM supplier c
            WHERE c.company_name != ''
            ORDER BY c.company_name ASC
            ";

            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='supplier_id' class='supplierFilter'>
                    <option value=''>Select Supplier</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
                </select>
            </td>
            ";

        }

        if ($report == 'salesGstReport') {
            $sqlYear = "
            SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS Year
            FROM  invoice i
            ";
            
            $rows .= "
            <td>
                <select name='year' class='leadStaffYearFilter'>
                    <option value=''>Choose Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
                </select>
            </td>
            ";

            $rows .= "
            <td>
                <select name='month' class='ml10 mr10 month'>
                    <option value=''>Choose Month</option>
                    {$cpUtil->getDropDownFromArr($arr, $month)}
                </select>
            </td>
            ";

        }
        
        $hiddenDateField = "<input type=text class=hiddenDateDisplay name=hidden_date_display>";
        $text = "
        <form id='reportSearch'>
        <table class='search'>
            <tr>
                <td class='resetLink'><a href='javascript:void(0);' onClick=\"javascript:$('#reportSearch').clearForm();\">reset</a></td>
                {$rows}
                <td>
                    <input type='hidden' name='report' value='{$report}'>
                    <input type='hidden' id='reportName' value='{$report}'>
                    <input type='submit' value='GO' class='button'>
                </td>
            </tr>
        </table>
        </form>
        <script>
            $(function() {
                $('#reportSearchPanel table.search td.dateRange input.fld_date').each(function(){
                    var inputname = $(this).attr('name');
                    var image = $(this).closest('ui-datepicker-trigger');
                    var dateValue = $(this).val();
                    $(this).addClass('MainDateField');
                    $(this).before('{$hiddenDateField}');
                });

                // Call the function on each input
                $('.hiddenDateDisplay[data-onload]').each(function() {
                    var dateCheck = $(this).attr('data-onload');
                    
                    if(dateCheck != '') {
                        var date      = dateCheck.replace(/-/g, '/');
                        var newdate   = new Date(date);
                        var dd = ('0' + newdate.getDate()).slice(-2);
                        var mm = ('0' + (newdate.getMonth() + 1)).slice(-2)
                        var y  = newdate.getFullYear();
             
                        var endDate = dd + '-'+ mm + '-' + y;
                    }else {
                        var endDate = '';
                    }

                    $(this).val(endDate);
                });
            });
        </script>
        ";
        }
        return $text;
    }

    function getDisplayReport($text){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $report = $fn->getReqParam('report');

        $searchQueryString = $pager->removeQueryString(array("_spAction"));
        $exportLink = "{$searchQueryString}&_spAction=exportData&report={$report}&showHTML=0";
        $exportPDFLink = "{$searchQueryString}&_spAction=exportDataPdf&report={$report}&showHTML=0";
        
        $text = "
        <div>
            <a href='{$exportLink}' class='exportLink button'>
                <u1>Export to Excel</u1>
            </a>
            
            {$text}
        </div>
        ";

        return $text;

        $json = array();
        $json['html'] = $text;

        return json_encode($json);
    }


    /**
     *
     */
    function getMonthFilterValues() {
        return "
        <option value=''>Month Filter</option>
        <option value='01'>January</option>
        <option value='02'>February</option>
        <option value='03'>March</option>
        <option value='04'>April</option>
        <option value='05'>May</option>
        <option value='06'>June</option>
        <option value='07'>July</option>
        <option value='08'>August</option>
        <option value='09'>September</option>
        <option value='10'>October</option>
        <option value='11'>November</option>
        <option value='12'>December</option>
        ";
    }

}