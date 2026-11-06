<?
class CPL_Admin_Widgets_Hms_VaccinationReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <h2>Vaccination Report</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Name</th>
						<th>Mobile No</th>
                        <th>Father Name</th>
                        <th>Husband Name</th>
                        <th>Town</th>
                        <th>Vaccination</th>
                        <th>Due Date</th>
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

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $datas = '';
      

        foreach($this->model->dataArray as $row){
            $due_date  = $fn->getCPDate($row['due_date'], 'd-m-Y');
 
		    $datas .= "
			<tr>
                <td>{$row['name']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['father_name']}</td>
                <td>{$row['spuse_name']}</td>
                <td>{$row['address_area']}</td>
                <td>{$row['group_name']}({$row['title']})</td>
                <td>{$due_date}</td>
             </tr>   
            ";
           
        }
       
        $text = "
        {$datas}
        ";

        return $text;
    }

}