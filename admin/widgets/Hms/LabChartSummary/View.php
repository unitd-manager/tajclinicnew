<?
class CPL_Admin_Widgets_Hms_LabChartSummary_View extends CP_Common_Lib_WidgetViewAbstract
{

    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        //include("/lib/fusioncharts.php");

        $text = "
        <script type='text/javascript' src='/admin/lib/fusioncharts.js'></script>
        <script type='text/javascript' src='/admin/lib/fusioncharts.charts.js'></script>
        <script type='text/javascript' src='/admin/lib/fusioncharts.theme.fint.js'></script>
        <script type='text/javascript' src='/admin/lib/fusioncharts-jquery-plugin.js'></script>
        
        <h2>Lab Chart Summary(Current Month)</h2>
        <div class='tableOuter' id='chart-containerlab'></div>

        <script type='text/javascript'>
            jQuery('document').ready(function () {
                $('#chart-containerlab').insertFusionCharts({
                    type: 'column2d',
                    width: '600',
                    height: '290',
                    dataFormat: 'json',
                    dataSource: {
                        'chart': {
                            'xAxisName': 'Month',
                            'palettecolors': 'e44a00',
                            'theme': 'fint'
                        },
                        'data': [{$this->getRowsHTML()}]
                    }
                });
            });     
        </script>
        ";

        return $text;
    }
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        
        $rows = '';
        foreach($this->model->dataArray as $row){
            $rows .= "{'label':'{$row['month']}', 'value':'{$row['medicaltestcount']}'},";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}