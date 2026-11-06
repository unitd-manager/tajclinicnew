<?
class CPL_Admin_Modules_Tradingsg_Dashboard_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.dashboardArr'];

        $hook = getCPModuleHook('tradingsg_dashboard', 'list', $dataArray, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $rows = '';
        foreach($arr as $widgetArr){
            $widget   = $widgetArr['name'];
            $subClass = $widgetArr['subClass'];
            $cssClass = $widgetArr['cssClass'];

            $clsInst = getCPWidgetObj($widget);

            $rows .= "
            <div class='{$cssClass}'>
                <div class='{$subClass} widget' id='wd_{$widget}'>
                    {$clsInst->getWidget()}
                </div>
            </div>
            ";
        }

        $text = "
        <div id='dashboard' class='subcolumns'>
            {$rows}
        </div>
        ";

        return $text;
    }
}
