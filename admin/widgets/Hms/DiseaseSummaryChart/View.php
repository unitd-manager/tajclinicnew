<?
class CPL_Admin_Widgets_Hms_DiseaseSummaryChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Disease Summary</h2>
        <div class='tableOuter' id='piechart' style='height: 500px;'>
        </div>

        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type='text/javascript'>
                // Load the Visualization API and the piechart package.
                google.load('visualization', '1', {'packages':['corechart']});

                // Set a callback to run when the Google Visualization API is loaded.
                google.setOnLoadCallback(drawChart);

                // Callback that creates and populates a data table,
                // instantiates the pie chart, passes in the data and
                // draws it.
                function drawChart() {

                    // Create the data table.
                    var data = google.visualization.arrayToDataTable([
                        ['Task', 'Hours per Day'],
                      {$this->getRowsHTML()}
                    ]);

                    var options = {
                        title: 'Treatment History',
                        is3D: true,
                    };

                    var chart = new google.visualization.PieChart(document.getElementById('piechart'));
                    chart.draw(data, options);
                }

        </script>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $site_id = $fn->getSessionParam('cp_site_id');

        $rows = '';

        foreach($this->model->dataArray as $row){
            $recCount = $fn->getRecordCount('patient_visit', "");
            
            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $appendSqlSite = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND p.site_id = {$cpSiteIdSession}";
            }

            $SQLTreatmentVisit = "
            SELECT p.*
            FROM patient_visit p
            WHERE p.complain LIKE '%{$row['disease_name']}%'
            {$appendSqlSite}
            ";

            $resultCountTreat = $db->sql_query($SQLTreatmentVisit);
            $recCountTreat    = $db->sql_numrows($resultCountTreat);

            //$recCountTreat = $fn->getRecordCount('treatment_visit', "treatment_id = '{$row['treatment_id']}'");
            $used = $recCountTreat/$recCount * 100;
            $used = number_format($used, 2);
            $rows .= "['{$row['disease_name']}', {$used}],";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}