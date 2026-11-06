<?
class CPL_Admin_Widgets_Hms_LabReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //<th>X Ray</th>
        //<th>ECG</th>

        if($tv['module'] == 'common_dashboard'){
            $heading = "Lab Report Last 7 Days";
        }else {
            $heading = "Lab Report";
        }

        $summaryRec = $this->model->getSqlForCount();

        $text = "
        <h2>{$heading}</h2>
        <table class='thinlist summaryTable mb20'>
            <thead>
                <th colspan='6'>Summary</th>
            </thead>
            {$summaryRec}
        </table>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
						<th>Day</th>
						<th>Lab Test (Patient Visit)</th>
                        <th>Lab Test (Self)</th>
                        <th>Lab Test (In Patient)</th>
                        <th class='txtRight'>Total</th>
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

    function getRowsHTMLOLD() {
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $site_id        = $fn->getReqParam('site_id');
        $rows = '';
        $totalOverAll = 0;
        $totalOverAllCount = 0;
        $appendSql = '';

        foreach($this->model->dataArray as $row){

            $day  = $fn->getCPDate($row['creation_date'], 'D');
            $date = $fn->getCPDate($row['date'], 'd-m-Y');

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND m.site_id = {$cpSiteIdSession}";
            }

            $SQLLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,SUM(m.fees) AS fees
                   ,SUM(m.lab_supplier_fees) AS lab_supplier_fees
            FROM medical_test_visit m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['date']}'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultLabTest = $db->sql_query($SQLLabTest);
            $labtesttotal = "";
            $totaltestcount = 0;
            $totaltestamount = 0;
            while ($rowLabTest    = $db->sql_fetchrow($resultLabTest)) {
                if($rowLabTest['fees'] == ""){
                    $rowLabTest['fees'] = 0;
                }

                $fees = $rowLabTest['fees'] - $rowLabTest['lab_supplier_fees'];

                $labtesttotal .= $rowLabTest['title'].'('.$rowLabTest['count'].' - '.$fees.'), ';

                $totaltestcount  += $rowLabTest['count'];
                $totaltestamount += $rowLabTest['fees'];
            }

            $labtesttotal = rtrim($labtesttotal, ", ");

            $rows .= "
            <tr>
                <td>{$date}</td>
                <td>{$day}</td>
                <td>{$labtesttotal}</td>
                <td class='txtRight'>{$totaltestcount}({$totaltestamount})</td>
            </tr>
            ";

            $totalOverAll      += $totaltestamount; 
            $totalOverAllCount += $totaltestcount; 
        }

        $totalOverAll = number_format(round($totalOverAll), 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='3'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllCount}({$totalOverAll})</td>
        </tr>
        ";

        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $site_id        = $fn->getReqParam('site_id');
        $rows = '';
        $totalOverAll = 0;
        $totalOverAllCount = 0;
        $appendSql = '';

        foreach($this->model->dataArray as $row){
            $day  = $fn->getCPDate($row['creation_date'], 'D');
            $date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND m.site_id = {$cpSiteIdSession}";
            }

            /*$SQLLabTest = "
            select title, sum(fees) AS fees, sum(test_count) AS test_count
            from
            (
                SELECT COUNT(m.medical_test_id) AS test_count
                   ,m.title AS title
                   ,SUM(m.fees) AS fees
                FROM medical_test_visit m
                LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
                WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
                {$appendSql}
                GROUP BY m.title
            union all
                SELECT COUNT(m.medical_test_id) AS test_count
                       ,m.title AS title
                       ,SUM(m.fees) AS fees
                FROM medical_test_lab m
                LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
                WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
                {$appendSql}
                GROUP BY m.title
            ) t
            group by title
            ";*/
            //$resultTest = $db->sql_query($SQLTest);

            $SQLPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,SUM(m.fees) AS fees
                   ,SUM(m.lab_supplier_fees) AS lab_supplier_fees
            FROM medical_test_visit m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = m.patient_visit_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND pv.status != 'Cancelled'
              AND mt.category != 'Vaccination'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultPatLabTest = $db->sql_query($SQLPatLabTest);
            $patlabtesttotal = "";
            $totalPattestcount = 0;
            $totalPattestamount = 0;
            while ($rowPatLabTest    = $db->sql_fetchrow($resultPatLabTest)) {
                if($rowPatLabTest['fees'] == ""){
                    $rowPatLabTest['fees'] = 0;
                }

                if($rowPatLabTest['lab_supplier_fees'] == ""){
                    $rowPatLabTest['lab_supplier_fees'] = 0;
                }

                $fees = $rowPatLabTest['fees'] - $rowPatLabTest['lab_supplier_fees'];

                $patlabtesttotal .= $rowPatLabTest['title'].'('.$rowPatLabTest['count'].' - '.$fees.'), ';

                $totalPattestcount  += $rowPatLabTest['count'];
                $totalPattestamount += $fees;

            }
            $patlabtesttotal = rtrim($patlabtesttotal, ", ");

            $SQLLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,SUM(m.fees) AS fees
                   ,SUM(m.lab_supplier_fees) AS lab_supplier_fees
            FROM medical_test_lab m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (lab_test lt) ON (lt.lab_test_id = m.lab_test_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND lt.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultLabTest = $db->sql_query($SQLLabTest);
            $labtesttotal = "";
            $totaltestcount = 0;
            $totaltestamount = 0;
            while ($rowLabTest    = $db->sql_fetchrow($resultLabTest)) {
                if($rowLabTest['fees'] == ""){
                    $rowLabTest['fees'] = 0;
                }
                if($rowLabTest['lab_supplier_fees'] == ""){
                    $rowLabTest['lab_supplier_fees'] = 0;
                }

                $fees = $rowLabTest['fees'] - $rowLabTest['lab_supplier_fees'];

                $labtesttotal .= $rowLabTest['title'].'('.$rowLabTest['count'].' - '.$fees.'), ';

                $totaltestcount  += $rowLabTest['count'];
                $totaltestamount += $fees;

            }
            $labtesttotal = rtrim($labtesttotal, ", ");

            $SQLInPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,SUM(m.fees) AS fees
                   ,SUM(m.lab_supplier_fees) AS lab_supplier_fees
            FROM medical_test_in_patient m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (in_patient ip) ON (ip.in_patient_id = m.in_patient_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND ip.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultInPatLabTest = $db->sql_query($SQLInPatLabTest);
            $InPatLabTestTotal = "";
            $totalIpTestCount  = 0;
            $totalIpTestAmount = 0;
            while ($rowInPatLabTest    = $db->sql_fetchrow($resultInPatLabTest)) {
                if($rowInPatLabTest['fees'] == ""){
                    $rowInPatLabTest['fees'] = 0;
                }
                if($rowInPatLabTest['lab_supplier_fees'] == ""){
                    $rowInPatLabTest['lab_supplier_fees'] = 0;
                }

                $fees = $rowInPatLabTest['fees'] - $rowInPatLabTest['lab_supplier_fees'];

                $InPatLabTestTotal .= $rowInPatLabTest['title'].'('.$rowInPatLabTest['count'].' - '.$fees.'), ';

                $totalIpTestCount  += $rowInPatLabTest['count'];
                $totalIpTestAmount += $fees;

            }

            $InPatLabTestTotal = rtrim($InPatLabTestTotal, ", ");

            $totalAlltestcount  = $totaltestcount + $totalPattestcount + $totalIpTestCount;
            $totalAlltestamount = $totaltestamount + $totalPattestamount + $totalIpTestAmount;

            $InPatLabTestTotal  = $InPatLabTestTotal . "<b> TOTAL : {$totalIpTestAmount}</b>";
            $labtesttotal       = $labtesttotal . "<b> TOTAL : {$totaltestamount}</b>";
            $patlabtesttotal       = $patlabtesttotal . "<b> TOTAL : {$totalPattestamount}</b>";

            $rows .= "
            <tr>
                <td width='5%'>{$date}</td>
                <td width='5%'>{$day}</td>
                <td width='26%'>{$patlabtesttotal}</td>
                <td width='26%'>{$labtesttotal}</td>
                <td width='26%'>{$InPatLabTestTotal}</td>
                <td width='12%' class='txtRight'>{$totalAlltestcount}({$totalAlltestamount})</td>
            </tr>
            ";

            $totalOverAll      += $totalAlltestamount; 
            $totalOverAllCount += $totalAlltestcount; 
        }

        $totalOverAll = number_format(round($totalOverAll), 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='5'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalOverAllCount}({$totalOverAll})</td>
        </tr>
        ";

        return $text;
    }
}