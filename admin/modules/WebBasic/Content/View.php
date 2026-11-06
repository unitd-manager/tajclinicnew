<?
class CPL_Admin_Modules_WebBasic_Content_View extends CP_Admin_Modules_WebBasic_Content_View
{

    /**
     * Adding help button form in the content list. Used in USS Products (ARIF)
     */
    function getHelpContentTask() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $module_name = $fn->getReqParam('module_name');

		$SQL = "
		SELECT c.title
		      ,c.description
		FROM content c
		LEFT JOIN (section s) ON (c.section_id = s.section_id)
		WHERE c.published = 1
		  AND s.section_type = '{$module_name}'
		";
        $result = $db->sql_query($SQL);

		$text = '';
        $i = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $text .= "
		    <div class='helpContentTaskForm'>
		    	  <div class='toggle'></div>
		       <div class='helpContentTask'>
		    		<strong><div class='contentTitle'>
		    			{$i}. {$row['title']}
		    		</div></strong>
		            <div class ='contentDescription'>
		    			{$row['description']}
		            </div>
		        </div>
	          </div>
            ";
            $i++;
        }
        return $text;
    }

    /**
     * Adding GET STARTED BUTTON form in the content list. Used in USS Products (THAMIM)
     */
    function getStartedContentTask() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

		$SQL = "
		SELECT c.title
		      ,c.description
		FROM content c
		WHERE c.published = 1
		AND c.content_type = 'Get Started'
		ORDER BY c.sort_order
		";
        $result = $db->sql_query($SQL);

		$text = '';
        $i = 1;

        $text .= "<strong>(Please click each title to know the detail)</strong>";
        
        while ($row = $db->sql_fetchrow($result)) {
            $text .= "
		    <div class=startedContentTaskForm'>
		    	  <div class='toggle'></div>
		       <div class='startedContentTask'>
		    		<strong><div class='contentTitle'>
		    			{$i}. {$row['title']}
		    		</div></strong>
		            <div class ='contentDescription'>
		    			{$row['description']}
		            </div>
		        </div>
	          </div>
            ";
            $i++;
        }

        $_SESSION['getStartedPopupOnloadSession'] = 1;

        return $text;
    }

    function getLocationSelectOnLogin() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $_SESSION['getLocationPopupOnloadSession'] = 1;

    }

}