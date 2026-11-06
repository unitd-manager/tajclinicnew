<?
class CPL_Admin_Modules_Hms_Expense_View extends CP_Common_Lib_ModuleViewAbstract
{
   function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $text = '';
        $rows = '';
        $readonly = '';
        $OrderItems = '';

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $date = $fn->getCPDate($row['date'],"d-m-Y");

            $groupName = "";
            if($row['group'] != ""){
                $SQLEG = "
                SELECT title
                FROM expense_group
                WHERE expense_group_id = '{$row['group']}'
                ";
                $resultEG   = $db->sql_query($SQLEG);
                $rowEG = $db->sql_fetchrow($resultEG);

                $groupName = $rowEG['title'];
            }

            $subGroupName = "";
            if($row['sub_group'] != ""){
                $appendSql = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSql = "AND site_id = {$cpSiteIdSession}";
                }

                $SQLESG = "
                SELECT  title
                FROM expense_sub_group
                WHERE expense_sub_group_id = '{$row['sub_group']}'
                {$appendSql}
                ";
                $resultESG = $db->sql_query($SQLESG);
                $rowESG    = $db->sql_fetchrow($resultESG);

                $subGroupName = $rowESG['title'];
            }

            if($row['amount'] != ""){
                $row['amount'] = number_format($row['amount'], 2);
            }

            $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
            $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

            if($row['modified_by'] != "" && $row['modification_date'] != ""){
                $createdModifiedBy = "<i>{$row['modified_by']} {$modification_date}</i>";
            }else{
                $createdModifiedBy = "<i>{$row['created_by']} {$creation_date}</i>";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($date)}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListDataCell($row['amount'], 'right')}
            {$listObj->getListDataCell($subGroupName)}
            {$listObj->getListDataCell($groupName)}
            {$listObj->getListDataCell($row['source'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($createdModifiedBy)}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Date', 'e.date')}
        {$listObj->getListHeaderCell('Description', 'e.description')}
        {$listObj->getListHeaderCell('Amount', 'e.amount', 'txtRight')}
        {$listObj->getListHeaderCell('Sub Expense', 'e.sub_group')}
        {$listObj->getListHeaderCell('Expense Head', 'e.group')}
        {$listObj->getListHeaderCell('Source', 'e.source')}
        {$listObj->getListHeaderCell('Status', 'e.status')}
        {$listObj->getListHeaderCell('Updated By')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $current_date = date('Y-m-d');

        $fieldset = "
        {$formObj->getDateRow('Date', 'date', $current_date)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('New Expense', $fieldset)}
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $formObj->mode = $tv['action'];
        $expVL = array('sqlType' => 'OneField');
        $expenseSource   = $fn->getValueListSQL('expenseSource', 'sort_order');

        /*$sqlSource  = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'expenseSource'
        ORDER BY sort_order
        ";*/

        $appendSql  = "";
        $appendSql2 = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            //$appendSql  = "WHERE site_id = {$cpSiteIdSession}";

            if($row['group'] != ""){
                $appendSql2 = "AND esg.site_id = {$cpSiteIdSession}";
            }
            else{
                $appendSql2 = "WHERE esg.site_id = {$cpSiteIdSession}";
            }
        }

        $SQLEG="
        SELECT expense_group_id
                ,title
        FROM expense_group
        ORDER BY expense_group_id
        ";
        $resultEG   = $db->sql_query($SQLEG);
        $rowEG = $db->sql_fetchrow($resultEG);

        $expense_group_id = $fn->getReqParam('expense_group_id');

        $appendGroup = "";
        $SQLESG = "";
        if($row['group'] != ""){
            $appendGroup = "WHERE eg.expense_group_id = '{$row['group']}'";

            $SQLESG = "
            SELECT  esg.expense_sub_group_id
                   ,esg.title
            FROM expense_sub_group esg
            LEFT JOIN (expense_group eg) ON (eg.expense_group_id = esg.expense_group_id)
            {$appendGroup}
            {$appendSql2}
            ORDER BY title
            ";
        }

        $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

        $siteNameHidden = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $sqlSite = "
            SELECT site_id
                  ,title
            FROM site
            WHERE site_id = '{$cpSiteIdSession}'
            ";
            $resultSite = $db->sql_query($sqlSite);
            $rowSite    = $db->sql_fetchrow($resultSite);

            $siteNameHidden = "
            <input name='site_name' value='{$rowSite['title']}' id='fld_site_name_hidden' />
            ";
        }

        $statusArray = array(
            "Due"
           ,"Paid"
        );

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Expense Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDateRow('Date', 'date', $row['date'])}</td>
                                <td>{$formObj->getDDRowBySQL('Expense Head', 'group', $SQLEG, $row['group'])}</td>
                                <td>{$formObj->getDDRowBySQL('Expense Sub Head', 'sub_group', $SQLESG, $row['sub_group'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Status', 'status', $statusArray, $row['status'])}</td>
                                <td>{$formObj->getTBRow('Amount', 'amount', $row['amount'])}</td>
                                <td>{$formObj->getDDRowBySQL('Source', 'source', $expenseSource, $row['source'], $expVL)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Description', 'description', $row['description'])}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {$siteNameHidden}
        ";

        return $text;
    }   
     /**
     *
     */
    function getEdit1($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $expVL = array('sqlType' => 'OneField');
        $sqlGroup = $fn->getValueListSQL('group','value');
        $formAddGroup = "index.php?_topRm={$tv['topRm']}&module=hms_expense&_spAction=addNewValuelistForm&valuelist_name=Group&expense_id={$row['expense_id']}&showHTML=0";
        $expGroup     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddGroup}' class='mr20 addNewValue' valuelist_name='Group'>Add</a>");


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Expense Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDateRow('Date', 'date', $row['date'])}</td>
                                <td>{$formObj->getDDRowBySQL('Group', 'group', $sqlGroup, $row['group'], $expGroup)}</td>
                                <td>{$formObj->getTBRow('Sub Group', 'sub_group', $row['sub_group'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Amount', 'amount', $row['amount'])}</td>
                                <td colspan='2'>{$formObj->getTARow('Description', 'description', $row['description'])}</td>
                            </tr>
                            <tr>
                                <td colspan='3' class='creModdate'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');
        $text = '';

        $record_id = $fn->getIssetParam($row, 'expense_id');

        $text .="
        {$comment->getView(array(
             'roomName' => 'hms_expense'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }
    /**
     *
     */
    function getQuickSearch() {
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln      = Zend_Registry::get('ln');

        $date1           = $fn->getReqParam('date_1');
        $date2           = $fn->getReqParam('date_2');
        $group           = $fn->getReqParam('group');
        $sub_group       = $fn->getReqParam('sub_group');
        $current_month   = $fn->getReqParam('current_month');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $source          = $fn->getReqParam('source');
        $status          = $fn->getReqParam('status');

        $statusArray = array(
            "Due"
           ,"Paid"
        );

        //$sqlgroup = $fn->getValueListSQL('group');
        $sqlgroup = "
        SELECT expense_group_id 
              ,title
        FROM expense_group
        ";

        $sqlsubgroup = "";
        if($group != ""){
            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND esg.site_id = {$cpSiteIdSession}";
            }

            $appendGroup = "WHERE eg.expense_group_id = '{$group}'";

            $sqlsubgroup = "
            SELECT  esg.expense_sub_group_id
                   ,esg.title
            FROM expense_sub_group esg
            LEFT JOIN (expense_group eg) ON (eg.expense_group_id = esg.expense_group_id)
            {$appendGroup}
            {$appendSql}
            ";
        }

        $currentMonthArray = array (
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

        if($current_month == ""){
            $current_month = date('m');
        }
        $sqlSource   = $fn->getValueListSQL('expenseSource', 'sort_order');

        $text = "
        <td>
            <select name='current_month'>
                {$cpUtil->getDropDownFromArr($currentMonthArray, $current_month)}
           </select>
        </td>
        <td>
            <select name='group'>
                <option value=''>Expense Head</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlgroup, $group)}
           </select>
        </td>  
        <td>
            <select name='sub_group'>
                <option value=''>Sub Expense Head</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlsubgroup, $sub_group)}
           </select>
        </td>       
        <td>
            <select name='source'>
                <option value=''>Source</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlSource, $source)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($statusArray, $status)}
           </select>
        </td>
        <td>
            {$formObj->getDateRangeRow('Date:', 'date', $date1, $date2)}
        </td>
        ";

        return $text;
    }
    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $expense_id    = $fn->getReqParam('expense_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=hms_expense&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='expense_id' value='{$expense_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */

}