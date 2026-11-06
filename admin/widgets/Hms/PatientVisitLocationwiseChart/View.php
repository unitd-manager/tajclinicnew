<?
class CPL_Admin_Widgets_Hms_PatientVisitLocationwiseChart_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <script type='text/javascript' src='/admin/lib/fusioncharts.js'></script>
        <script type='text/javascript' src='/admin/lib/fusioncharts.charts.js'></script>
        <script type='text/javascript' src='/admin/lib/fusioncharts.theme.fint.js'></script>
        <script type='text/javascript' src='/admin/lib/fusioncharts-jquery-plugin.js'></script>
        
        <h2>Patient Visit Location wise(Current Month)</h2>

		<div class='tableOuter' id='chart-locationwise' style='height: 305px;'>
        </div>

        <script type='text/javascript'>
            jQuery('document').ready(function () {
                $('#chart-locationwise').insertFusionCharts({
                    type: 'column2d',
                    width: '1100',
                    height: '300',
                    dataFormat: 'json',
                    dataSource: {
                        'chart': {
                            'xAxisName': 'Location',
                            'palettecolors': '#B91383',
                            'labelFont': 'Arial',
                            'labelFontColor': '0075c2',
                            'labelFontSize': '12',
                            'labelFontBold':'1',
                            'lableFontItalic':'1',
                            'labelFontAlpha':'70',
                            'theme': 'fint',
                            'rotateValues': '1',
                            'valueFontColor': '#ffffff',
                         },
                        'data': [{$this->getRowsHTML()}]
                    }
                });
            });     
        </script>
        ";
         return $text;
    }

 function getRowsHTML() {
        $fn = Zend_Registry::get('fn');

        $rows = '';

        foreach($this->model->dataArray as $row){
            $rows .= "{'label':'{$row['address_area']}', 'value':'{$row['no_of_visit']}'},";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
   
}
