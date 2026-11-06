<?
class CPL_Admin_Modules_Hms_Home_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $topRoomsArray = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();

        $text = "
        <div class='subcolumns homePageSummary'>
            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=main&module=hms_company'><img src='images/pm.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=main&module=hms_company'>Patient Management</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('main')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=utils&module=hms_contact'><img src='images/utils.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=utils&module=hms_contact'>Utils</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('utils')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=pharmacy&module=tradingsg_pos'><img src='images/pharmacy-icon.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=pharmacy&module=tradingsg_pos'>Pharmacy</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('pharmacy')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=finance&module=hms_contact'><img src='images/finance.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=finance&module=hms_contact'>Finance</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('finance')}
                        </div>
                    </div>
                </div>
            </div>

            <div class='c25l'>
                <div class='homePageSummaryList homePageSummaryPanel'>
                    <div class='floatbox summaryHeader'>
                        <div class='summDisplayPic'>
                            <div class='mt5 ml5 homeModuleDisplay'>
                                <div><a href='/admin/index.php?_topRm=admin&module=core_translation'><img src='images/admin.png'/></a></div>
                            </div>
                        </div>
                        <div class='summDisplayTitle'><a href='/admin/index.php?_topRm=admin&module=core_translation'>Admin</a></div>
                    </div>
                    <div class='summary'>
                        <div class='mt5 mb5 overallSummaryText floatbox'>
                            {$this->getModuleTitle1('admin')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getModuleTitle($topRm){
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $topRoomsArray = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();
            $moduleTitle = '';
        //foreach($topRoomsArray as $key => $value) {
            $arr = $cpCfg['cp.topRooms'][$topRm]['modules'];

            foreach($arr as $module) {
                if (array_key_exists($module, $modulesArr)) {
                    $moduleName = $roomsArrayTemp[] = $modulesArr[$module]['name'];
                    $moduleTitle .= "
                    <div class='modHeading'>
                        <div class='modHeadingBg'>{$modulesArr[$moduleName]['title']}</div>
                    </div>
                    ";
                }
            }
        //}

        return $moduleTitle;
    }

    /**
     *
     */
    function getModuleTitleOLD($topRm){
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $topRoomsArray = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();
            $moduleTitle = '';
        //foreach($topRoomsArray as $key => $value) {
            $arr = $cpCfg['cp.topRooms'][$topRm]['modules'];

            foreach($arr as $module) {
                if (array_key_exists($module, $modulesArr)) {
                    $moduleName = $roomsArrayTemp[] = $modulesArr[$module]['name'];
                    $moduleTitle .= "
                    <div class='homeModuleTitle'><a href='/admin/index.php?_topRm={$topRm}&module={$modulesArr[$module]['name']}'>{$modulesArr[$moduleName]['title']}</a></div>
                    ";
                }
            }
        //}

        return $moduleTitle;
    }

    /**
     *
     */
    function getModuleTitle1($topRm){
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $topRoomsArrAccess = Zend_Registry::get('topRoomsArrAccess');

        $arrTr = $cpCfg['cp.topRooms'];
        $roomsArrayTemp = array();
        $rowsTr  = '';
        $arr = $cpCfg['cp.topRooms'][$topRm]['modules'];
        $rows  = '';
        foreach ($arr as $module) {
            if ($cpCfg['cp.hasAccessModule']) {
                $modulesArrAccess = Zend_Registry::get('modulesArrAccess');
                $hasAccess = isset($modulesArrAccess[$module]) ? $modulesArrAccess[$module]['hasAccess'] : 0;
                if ($hasAccess == 0) {
                    continue;
                }
            }

            $title = $modulesArr[$module]['title'];
            //$url   = $modulesArr[$module]['url'];
            $url = "index.php?_topRm={$topRm}&module={$module}";

            $rows .= "
            <div class='homeModuleTitle'><a href='{$url}'>{$title}</a></div>
            ";
        }

        $rowsTr .= "
        {$rows}
        ";

        $text = "
        {$rowsTr}
        ";

        return $text;
    }
}