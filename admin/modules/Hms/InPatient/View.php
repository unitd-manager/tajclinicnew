<?
class CPL_Admin_Modules_Hms_InPatient_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $count      = 0;
        $rows       = '';
        foreach ($dataArray as $row){

            $age = '';

            if($row['age_year'] != ''){
                $age = $row['age_year'].' Yrs';
            } elseif($row['age_month'] != ''){
                $age = $row['age_month'].' Months';
            } elseif($row['age_day'] != ''){
                $age = $row['age_day'].' Days';
            }

            $date_admitted = $fn->getCPDate($row['date_admitted'], 'd');
            $date_discharge = $fn->getCPDate($row['date_discharge'], 'd');
            
            $no_of_days = '';
            if($date_discharge != ''){
                $no_of_days = $date_discharge - $date_admitted;
            }
            
            $dateAdmitted  = $fn->getCPDate($row['date_admitted'], 'd-m-Y');
            $dateDischarge = $fn->getCPDate($row['date_discharge'], 'd-m-Y');
            $timeAdmitted  = $fn->getCPDate($row['time_admitted'], 'h:m a');

            $SQLEmpVisit = "
            SELECT SUM(consultation_fees) AS consultation_fees
                   ,employee_in_patient_id
            FROM employee_in_patient
            WHERE in_patient_id = {$row['in_patient_id']}
            ";
            $resultEmpVisit = $db->sql_query($SQLEmpVisit);
            $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

            $totalAmount    = $rowEmpVisit['consultation_fees'] + $row['amount'] + $row['nursing_fees'] + $row['other_fees'];
            $theatreCharges = $row['theatre_charges'];
            
            if($totalAmount != ""){
                $totalAmount = number_format($totalAmount);
            }
            $pat_name = $row['patient_name'] . '/' . $age;
            $dateAdmitted = $dateAdmitted . '/' . $timeAdmitted;

            $yearCreated = $fn->getCPDate($row['creation_date'], "y");
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['uhid'])}
            {$listObj->getListDataCell('IP-'.$row['code'].'-'.$yearCreated)}
            {$listObj->getListDataCell($pat_name)}
            {$listObj->getListDataCell($row['address_area'])}
            {$listObj->getListDataCell($row['diagnosis'])}
            {$listObj->getListDataCell($dateAdmitted)}
            {$listObj->getListDataCell($row['admission_type'])}
            {$listObj->getListDataCell($totalAmount, 'right')}
            {$listObj->getListDataCell($theatreCharges, 'right')}
            {$listObj->getListDataCell($dateDischarge)}
            {$listObj->getListDataCell($no_of_days)}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['patient_information_id'])}
            ";

            $count++ ;
        }
        
        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('UHID', 'p.uhid')}
        {$listObj->getListHeaderCell('IP Code', 'ip.code')}
        {$listObj->getListHeaderCell('Patient Name', 'p.name')}
        {$listObj->getListHeaderCell('Town/City', 'p.address_area')}
        {$listObj->getListHeaderCell('Diagnosis', 'ip.diagnosis')}
        {$listObj->getListHeaderCell('Date of Admission', 'ip.date_admitted')}
        {$listObj->getListHeaderCell('Admission Type', 'ip.admission_type')}
        {$listObj->getListHeaderCell('Admission Charges', '','txtRight')}
        {$listObj->getListHeaderCell('Theatre Charges', '','txtRight')}
        {$listObj->getListHeaderCell('Date of Discharge', 'ip.date_discharge')}
        {$listObj->getListHeaderCell('No of Days')}
        {$listObj->getListHeaderCell('Status', 'ip.status')}
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

        $fielset1 = "
        {$formObj->getDateRow('Date Admitted (YYYY-MM-DD)', 'date_admitted')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset1)}
        ";

        return $text;
    }
   
    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $discountPercent = '';
        $cstNo = '';
        $tinNo = '';

        //$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['address_country']);

        $expVl           = array('sqlType' => 'OneField');
        $expBillType     = array('sqlType' => 'OneField', 'hideFirstOption' => 1);
        $sqlCategory     = $fn->getValueListSQL('patientVisitCategory');
        $sqlTitle        = $fn->getValueListSQL('patientVisitTitle');
        $sqlBillType     = $fn->getValueListSQL('billType');
        $expNoEdit       = array('isEditable' => 0);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $followUp = array(
                       '1 week'  => 'One week later'
                      ,'2 weeks'  => 'Two weeks later'
                      ,'3 weeks'  => 'Three weeks later'
                      ,'4 weeks'  => 'Four weeks later'
                      ,'5 weeks'  => 'Five weeks later'
                      ,'6 weeks'  => 'Six weeks later'
                      ,'2 months'  => 'Two months later'
                      ,'3 months'  => 'Three months later'
                      ,'6 months'  => 'Six months later'
                      );
        
        $expArr = array('useKey' => 1);

        $newDoctorUrl = 'index.php?module=hms_inPatient&_spAction=newDoctorAdd&visits_patients=1&showHTML=0';
        $newDoctor    = "
        <a class='AddNewDoctorLink' href='{$newDoctorUrl}'>
            Add
        </a>";
        $expNewDoctor = array("notesRight" => $newDoctor, "notesRightCls" => "formFieldNotesRight btn btn-success");

        $newDoctorAnesthesiologyUrl = 'index.php?module=hms_inPatient&_spAction=newDoctorAdd&category=Anesthesiology&showHTML=0';
        $newDoctorAnesthesiology    = "
        <a class='AddNewDoctorLink' href='{$newDoctorAnesthesiologyUrl}'>
            Add
        </a>";
        $expNewDoctorAnesthesiology = array("sqlType" => "OneField", "notesRight" => $newDoctorAnesthesiology, "notesRightCls" => "formFieldNotesRight btn btn-success");

        $surgeonType = "General Surgeon";
        $newDoctorSurgeonUrl = 'index.php?module=hms_inPatient&_spAction=newDoctorAdd&category='.$surgeonType.'&showHTML=0';
        $newDoctorSurgeon    = "
        <a class='AddNewDoctorLink' href='{$newDoctorSurgeonUrl}'>
            Add
        </a>";
        $expNewDoctorSurgeon = array("sqlType" => "OneField", "notesRight" => $newDoctorSurgeon, "notesRightCls" => "formFieldNotesRight btn btn-success");

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND site_id = {$cpSiteIdSession}";
        }

        $statusArray = array(
            "Admitted"
           ,"Discharged"
           ,"On Hold"
           ,"Cancelled"
        );

        /*$sqlEmployee = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_name
        ";*/

        $sqlEmployee = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        AND e.category = 'Doctor' OR e.category = 'Duty Doctor'
        {$appendSqlEmp}
        ORDER BY e.employee_name
        ";

        $status  = $fn->getReqParam('status');

        /*$statusArray = array(
            "New"
           ,"Closed"
           ,"Cancelled"
        );*/

        $sqlGender      = $fn->getValueListSQL('gender');

        $medHisLbl = "
        <li class='first'>
            <a href='#tabs-1' class='chiefComplains'>Chief Complains</a>
        </li>
        ";

        $medHisTab = "
        <div id='tabs-1'>
        <div class='floatbox'>
            <div class='float_left complainTabClass'>
                <div id='chiefComplains'>{$this->getChiefComplainsDisplay($row['in_patient_id'])}</div>
            </div>
            <div class='float_left complainTabClass'>
                <div id='summaryPortalDisplay'>{$this->getSummaryPortalDisplay($row['in_patient_id'])}</div>
            </div>
            <div class='float_left'>
                <div id='procedurePortal'>{$this->getProcedurePortalDisplay($row['in_patient_id'])}</div>
            </div>
        </div>
        </div>
        ";

        $medicineLbl = "
        <li class='first'>
            <a href='#tabs-5' class='medicines'>Medicines</a>
        </li>
        ";

        $medicineTab = "
        <div id='tabs-5'>
            <div id='medicinesDisplay'>{$this->getMedicinesPortalDisplay($row['in_patient_id'])}</div>
        </div>
        ";

        $medicalTestLbl = "
        <li class='first'>
            <a href='#tabs-6' class='investigations'>Investigations</a>
        </li>
        ";

        $medicalTestTab = "
        <div id='tabs-6'>
            <div id='medicalDisplay'>{$this->getMedicalPortalDisplay($row['in_patient_id'])}</div>
        </div>
        ";

        $addDrLbl = "
        <li class='first'>
            <a href='#tabs-2'>Add Consultant</a>
        </li>
        ";

        $formActionAddDr = "index.php?module=hms_inPatient&_spAction=addDoctorRecord&in_patient_id={$row['in_patient_id']}&showHTML=0";
        $addDrTab = "
        <div id='tabs-2'>
            <div class='btn btn-info mb10'><a href='{$formActionAddDr}' id='addDoctorRecord' in_patient_id={$row['in_patient_id']}>Add Record</a></div>
            <div id='doctorDisplay'>{$this->getDoctorPortalDisplay($row['in_patient_id'])}</div>
        </div>
        ";

        $addSpecialistLbl = "
        <li class='first'>
            <a href='#tabs-3'>Add Specialist</a>
        </li>
        ";

        $formActionAddSpecialist = "index.php?module=hms_inPatient&_spAction=addSpecialistRecord&in_patient_id={$row['in_patient_id']}&showHTML=0";
        $addSpecialistTab = "
        <div id='tabs-3'>
            <div class='btn btn-info mb10'><a href='{$formActionAddSpecialist}' id='addSpecialistRecord' in_patient_id={$row['in_patient_id']}>Add Record</a></div>
            <div id='specialistDisplay'>{$this->getSpecialistPortalDisplay($row['in_patient_id'])}</div>
        </div>
        ";

        $theaterCaseLbl = "
        <li class='first'>
            <a href='#tabs-7' class='theatercase'>Theatre Case</a>
        </li>
        ";

        $theaterCaseTab = "
        <div id='tabs-7'>
            <div id='theaterCaseDisplay'></div>
        </div>
        ";

        $SQLEmpVisit = "
        SELECT consultation_fees
               ,employee_in_patient_id
        FROM employee_in_patient
        WHERE in_patient_id = {$row['in_patient_id']}
        ORDER BY employee_in_patient_id ASC
        ";
        $resultEmpVisit = $db->sql_query($SQLEmpVisit);
        $rowEmpVisit    = $db->sql_fetchrow($resultEmpVisit);

        $feesRow          = "{$formObj->getTBRow('Dr Fees', 'consultation_fees', $rowEmpVisit['consultation_fees'])}";
        $nursingFeesRow   = "{$formObj->getTBRow('Nursing Fees', 'nursing_fees', $row['nursing_fees'])}";
        $otherFeesRow     = "{$formObj->getTBRow('Other Fees', 'other_fees', $row['other_fees'])}";
        $roomRentRow      = "{$formObj->getTBRow('Room Rent', 'amount', $row['amount'])}";
        $advanceRow       = $formObj->getTBRow('Advance Payment', 'advance_payment', $row['advance_payment']);
        $entFeesRow       = $formObj->getTBRow('ENT Fees', 'ent_fees', $row['ent_fees']);
        $drFeesRow       = $formObj->getTBRow('Dr Fees', 'dr_fees', $row['dr_fees']);
        $dressingFeesRow       = $formObj->getTBRow('Dressing Fees', 'dressing_fees', $row['dressing_fees']);
        $injectionFeesRow       = $formObj->getTBRow('Injection Fees', 'injection_fees', $row['injection_fees']);
        $foreignBodyRemovalRow       = $formObj->getTBRow('Foreign Body Removal', 'foreign_body_removal', $row['foreign_body_removal']);
        $waxRemovalRow       = $formObj->getTBRow('Wax Removal', 'wax_removal', $row['wax_removal']);
        $ivFluidRow       = $formObj->getTBRow('Iv Fluid', 'iv_fluid', $row['iv_fluid']);
        $xRayRow       = $formObj->getTBRow('X Ray', 'x_ray', $row['x_ray']);
        $aprConditionChargesRow       = $formObj->getTBRow('Apr Condition Charges', 'apr_condition_charges', $row['apr_condition_charges']);
        $infusionSyringeRow       = $formObj->getTBRow('Infusion Syringe', 'infusion_syringe', $row['infusion_syringe']);
        $rhyesTubeInsertionRow       = $formObj->getTBRow('Rhyes Tube Insertion', 'rhyes_tube_insertion', $row['rhyes_tube_insertion']);
        $stomachWashRow       = $formObj->getTBRow('Stomach Wash', 'stomach_wash', $row['stomach_wash']);
        $enemaRow       = $formObj->getTBRow('ENEMA', 'enema', $row['enema']);
        $catherisationRow       = $formObj->getTBRow('Catherisation', 'catherisation', $row['catherisation']);
        $wardMachineRow       = $formObj->getTBRow('Ward Medicine Replacement', 'ward_medicine_replacement', $row['ward_medicine_replacement']);
        $otMedicineRow       = $formObj->getTBRow('Ot Medicine Replacement', 'ot_medicine_replacement', $row['ot_medicine_replacement']);
        $fileCardRow       = $formObj->getTBRow('File Card', 'file_card', $row['file_card']);

        $surFeesRow       = "{$formObj->getTBRow('Surgeon Fees', 'surgeon_fees', $row['surgeon_fees'])}";
        $anaFeesRow       = "{$formObj->getTBRow('Anaesthetic Fees', 'anesthetic_fees', $row['anesthetic_fees'])}";
        $theatreFeesRow   = "{$formObj->getTBRow('Theatre Assistant Fees', 'theater_assistant_fees', $row['theater_assistant_fees'])}";
        $theatreChargeRow = "{$formObj->getTBRow('Theatre Charges', 'theatre_charges', $row['theatre_charges'])}";
        $refChargeRow     = "{$formObj->getTBRow('Referral Charges', 'referral_charges', $row['referral_charges'])}";
        $drugChargeRow    = "{$formObj->getTBRow('Replacement Drug Charges', 'replacement_drug_charges', $row['replacement_drug_charges'])}";
        $asstSurFeesRow   = "{$formObj->getTBRow('Asst Surgeon Fees', 'asst_surgeon_fees', $row['asst_surgeon_fees'])}";

        $gotoOrder            = '';
        $generateOrder        = '';
        $invoicePortalDisplay = '';
        $actionButtons        = '';
        $receiptPortalDisplay = '';
        $cancelAdmission      = '';
        $updateOrder = '';

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $SQLOrder ="
        SELECT order_id
        FROM `order`
        WHERE in_patient_id = {$row['in_patient_id']}
        AND order_type = 'IP'
        {$appendSql}
        ";
        $resultOrder  = $db->sql_query($SQLOrder);
        $numRowsOrder = $db->sql_numrows($resultOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);

        if($numRowsOrder > 0){
            $SQLInvoice = "
            SELECT i.*
            FROM invoice i
            WHERE i.order_id = {$rowOrder['order_id']}
            AND i.status != 'Cancelled'
            ";
            $resultInvoice = $db->sql_query($SQLInvoice);
            $numRowsInvoice = $db->sql_numrows($resultInvoice);

            if($numRowsInvoice == 0){
                if($row['status'] != 'Cancelled'){
                    $generateOrder = "<a href='#' id='createOrderRecord' in_patient_id='{$row['in_patient_id']}' class='btn btn-info'>Generate Bill</a>";
                    $cancelAdmission = "<a in_patient_id='{$row['in_patient_id']}' class='btn btn-danger cancelAdmissionRecord'>Cancel Admission</a>";
                }
            }
            else{

                $billSummaryOrder = "index.php?module=hms_inPatient&_spAction=summaryInOrder&order_id={$rowOrder['order_id']}&showHTML=0";
                $generateOrder = "<div class='billSummaryOrder float_left'><a class='btn btn-primary' href='{$billSummaryOrder}' id='billSummaryOrder' order_id='{$rowOrder['order_id']}'>Bill Summary</a></div>";                
                $updateOrder = "<a href='#' id='updateOrderRecord' in_patient_id='{$row['in_patient_id']}' class='btn btn-info'>Update Bill</a>";

                $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Advance Payment');
                if($apChk > 0){
                    $advanceRow = "{$formObj->getTBRow('Advance Payment', 'advance_payment', $row['advance_payment'], $expNoEdit)}
                    <input type='hidden' name='advance_payment' value='{$row['advance_payment']}' />";
                } else {
                    $advanceRow = $formObj->getTBRow('Advance Payment', 'advance_payment', $row['advance_payment']);                    
                }

                $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Dressing Fees');
                if($apChk > 0){
                    $dressingFeesRow = "{$formObj->getTBRow('Dressing Fees', 'dressing_fees', $row['dressing_fees'], $expNoEdit)}
                    <input type='hidden' name='dressing_fees' value='{$row['dressing_fees']}' />";
                } else {
                    $dressingFeesRow = $formObj->getTBRow('Dressing Fees', 'dressing_fees', $row['dressing_fees']);                    
                }

                $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Dr Fees');
                if($apChk > 0){
                    $drFeesRow = "{$formObj->getTBRow('Dr Fees', 'dr_fees', $row['dr_fees'], $expNoEdit)}
                    <input type='hidden' name='dr_fees' value='{$row['dr_fees']}' />";
                } else {
                    $drFeesRow = $formObj->getTBRow('Dr Fees', 'dr_fees', $row['dr_fees']);                    
                }

                 $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'ENT Fees');
                if($apChk > 0){
                    $entFeesRow = "{$formObj->getTBRow('ENT Fees', 'ent_fees', $row['ent_fees'], $expNoEdit)}
                    <input type='hidden' name='ent_fees' value='{$row['ent_fees']}' />";
                } else {
                    $entFeesRow = $formObj->getTBRow('ENT Fees', 'ent_fees', $row['ent_fees']);                    
                }

                  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'File Card');
                if($apChk > 0){
                    $fileCardRow = "{$formObj->getTBRow('File Card', 'file_card', $row['file_card'], $expNoEdit)}
                    <input type='hidden' name='file_card' value='{$row['file_card']}' />";
                } else {
                    $fileCardRow = $formObj->getTBRow('File Card', 'file_card', $row['file_card']);                    
                }

                  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Ot Medicine Replacement');
                if($apChk > 0){
                    $otMedicineRow = "{$formObj->getTBRow('Ot Medicine Replacement', 'ot_medicine_replacement', $row['ot_medicine_replacement'], $expNoEdit)}
                    <input type='hidden' name='ot_medicine_replacement' value='{$row['ot_medicine_replacement']}' />";
                } else {
                    $otMedicineRow = $formObj->getTBRow('Ot Medicine Replacement', 'ot_medicine_replacement', $row['ot_medicine_replacement']);                    
                }


                  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Ward Medicine Replacement');
                if($apChk > 0){
                    $wardMachineRow = "{$formObj->getTBRow('Ward Medicine Replacement', 'ward_medicine_replacement', $row['ward_medicine_replacement'], $expNoEdit)}
                    <input type='hidden' name='ward_medicine_replacement' value='{$row['ward_medicine_replacement']}' />";
                } else {
                    $wardMachineRow = $formObj->getTBRow('Ward Medicine Replacement', 'ward_medicine_replacement', $row['ward_medicine_replacement']);                    
                }



  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Catherisation');
                if($apChk > 0){
                    $catherisationRow = "{$formObj->getTBRow('Catherisation', 'catherisation', $row['catherisation'], $expNoEdit)}
                    <input type='hidden' name='catherisation' value='{$row['catherisation']}' />";
                } else {
                    $catherisationRow = $formObj->getTBRow('Catherisation', 'catherisation', $row['catherisation']);                    
                }


  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'ENEMA');
                if($apChk > 0){
                    $enemaRow = "{$formObj->getTBRow('ENEMA', 'enema', $row['enema'], $expNoEdit)}
                    <input type='hidden' name='enema' value='{$row['enema']}' />";
                } else {
                    $enemaRow = $formObj->getTBRow('ENEMA', 'enema', $row['enema']);                    
                }


  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Stomach Wash');
                if($apChk > 0){
                    $stomachWashRow = "{$formObj->getTBRow('Stomach Wash', 'stomach_wash', $row['stomach_wash'], $expNoEdit)}
                    <input type='hidden' name='stomach_wash' value='{$row['stomach_wash']}' />";
                } else {
                    $stomachWashRow = $formObj->getTBRow('Stomach Wash', 'stomach_wash', $row['stomach_wash']);                    
                }

  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Rhyes Tube Insertion');
                if($apChk > 0){
                    $rhyesTubeInsertionRow = "{$formObj->getTBRow('Rhyes Tube Insertion', 'rhyes_tube_insertion', $row['rhyes_tube_insertion'], $expNoEdit)}
                    <input type='hidden' name='rhyes_tube_insertion' value='{$row['rhyes_tube_insertion']}' />";
                } else {
                    $rhyesTubeInsertionRow = $formObj->getTBRow('ENT Fees', 'rhyes_tube_insertion', $row['rhyes_tube_insertion']);                    
                }

  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Infusion Syringe');
                if($apChk > 0){
                    $infusionSyringeRow = "{$formObj->getTBRow('Infusion Syringe', 'infusion_syringe', $row['infusion_syringe'], $expNoEdit)}
                    <input type='hidden' name='infusion_syringe' value='{$row['infusion_syringe']}' />";
                } else {
                    $infusionSyringeRow = $formObj->getTBRow('Infusion Syringe', 'infusion_syringe', $row['infusion_syringe']);                    
                }

  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Apr Condition Charges');
                if($apChk > 0){
                    $aprConditionChargesRow = "{$formObj->getTBRow('Apr Condition Charges', 'apr_condition_charges', $row['apr_condition_charges'], $expNoEdit)}
                    <input type='hidden' name='apr_condition_charges' value='{$row['apr_condition_charges']}' />";
                } else {
                    $aprConditionChargesRow = $formObj->getTBRow('Apr Condition Charges', 'apr_condition_charges', $row['apr_condition_charges']);                    
                }

  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'X Ray');
                if($apChk > 0){
                    $xRayRow = "{$formObj->getTBRow('X Ray', 'x_ray', $row['x_ray'], $expNoEdit)}
                    <input type='hidden' name='x_ray' value='{$row['x_ray']}' />";
                } else {
                    $xRayRow = $formObj->getTBRow('X Ray', 'x_ray', $row['x_ray']);                    
                }

  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Iv Fluid');
                if($apChk > 0){
                    $ivFluidRow = "{$formObj->getTBRow('Iv Fluid', 'iv_fluid', $row['iv_fluid'], $expNoEdit)}
                    <input type='hidden' name='iv_fluid' value='{$row['iv_fluid']}' />";
                } else {
                    $ivFluidRow = $formObj->getTBRow('Iv Fluid', 'iv_fluid', $row['iv_fluid']);                    
                }

  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Wax Removal');
                if($apChk > 0){
                    $waxRemovalRow = "{$formObj->getTBRow('Wax Removal', 'wax_removal', $row['wax_removal'], $expNoEdit)}
                    <input type='hidden' name='wax_removal' value='{$row['wax_removal']}' />";
                } else {
                    $waxRemovalRow = $formObj->getTBRow('Wax Removal', 'wax_removal', $row['wax_removal']);                    
                }


                  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Injection Fees');
                if($apChk > 0){
                    $injectionFeesRow = "{$formObj->getTBRow('Injection Fees', 'injection_fees', $row['injection_fees'], $expNoEdit)}
                    <input type='hidden' name='injection_fees' value='{$row['injection_fees']}' />";
                } else {
                    $injectionFeesRow = $formObj->getTBRow('Injection Fees', 'injection_fees', $row['injection_fees']);                    
                }



  $apChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Foreign Body Removal');
                if($apChk > 0){
                    $foreignBodyRemovalRow = "{$formObj->getTBRow('Foreign Body Removal', 'foreign_body_removal', $row['foreign_body_removal'], $expNoEdit)}
                    <input type='hidden' name='foreign_body_removal' value='{$row['foreign_body_removal']}' />";
                } else {
                    $foreignBodyRemovalRow = $formObj->getTBRow('Foreign Body Removal', 'foreign_body_removal', $row['foreign_body_removal']);                    
                }




                $dnChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Doctor/Nurse');
                if($dnChk > 0){
                    $feesRow = "{$formObj->getTBRow('Dr Fees', 'consultation_fees', $rowEmpVisit['consultation_fees'], $expNoEdit)}
                    <input type='hidden' name='consultation_fees' value='{$rowEmpVisit['consultation_fees']}' />";
                } else {
                    $feesRow = $formObj->getTBRow('Dr Fees', 'consultation_fees', $rowEmpVisit['consultation_fees']);                    
                }

                $nfChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Nursing Fees');
                if($nfChk > 0){
                    $nursingFeesRow = "{$formObj->getTBRow('Nursing Fees', 'nursing_fees', $row['nursing_fees'], $expNoEdit)}
                    <input type='hidden' name='nursing_fees' value='{$row['nursing_fees']}' />";
                } else {
                $nursingFeesRow = $formObj->getTBRow('Nursing Fees', 'nursing_fees', $row['nursing_fees']);
                }

                $ofChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Other Fees');
                if($ofChk > 0){
                    $otherFeesRow = "{$formObj->getTBRow('Other Fees', 'other_fees', $row['other_fees'], $expNoEdit)}
                    <input type='hidden' name='other_fees' value='{$row['other_fees']}' />";
                } else{
                    $otherFeesRow = $formObj->getTBRow('Other Fees', 'other_fees', $row['other_fees']);
                }

                $rrChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Room Rent');
                if($rrChk > 0){
                    $roomRentRow    = "{$formObj->getTBRow('Room Rent', 'amount', $row['amount'], $expNoEdit)}
                    <input type='hidden' name='amount' value='{$row['amount']}' />";
                } else {
                    $roomRentRow    = $formObj->getTBRow('Room Rent', 'amount', $row['amount']);   
                }

                $sfChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Surgeon Fees');
                if($sfChk > 0){
                    $surFeesRow    = "{$formObj->getTBRow('Surgeon Fees', 'surgeon_fees', $row['surgeon_fees'], $expNoEdit)}
                    <input type='hidden' name='surgeon_fees' value='{$row['surgeon_fees']}' />";
                } else {
                    $surFeesRow    = "{$formObj->getTBRow('Surgeon Fees', 'surgeon_fees', $row['surgeon_fees'])}";
                }

                $asfChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Asst Surgeon Fees');
                if($asfChk > 0){
                    $asstSurFeesRow    = "{$formObj->getTBRow('Asst Surgeon Fees', 'asst_surgeon_fees', $row['asst_surgeon_fees'], $expNoEdit)}
                    <input type='hidden' name='asst_surgeon_fees' value='{$row['asst_surgeon_fees']}' />";
                } else {
                    $asstSurFeesRow    = "{$formObj->getTBRow('Asst Surgeon Fees', 'asst_surgeon_fees', $row['asst_surgeon_fees'])}";
                }

                $afChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Anaesthetic Fees');
                if($afChk > 0){
                    $anaFeesRow    = "{$formObj->getTBRow('Anaesthetic Fees', 'anesthetic_fees', $row['anesthetic_fees'], $expNoEdit)}
                    <input type='hidden' name='anesthetic_fees' value='{$row['anesthetic_fees']}' />";
                } else {
                    $anaFeesRow    = "{$formObj->getTBRow('Anaesthetic Fees', 'anesthetic_fees', $row['anesthetic_fees'])}";
                }

                $tafChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Theatre Assistant Fees');
                if($tafChk > 0){
                    $theatreFeesRow    = "{$formObj->getTBRow('Theatre Assistant Fees', 'theater_assistant_fees', $row['theater_assistant_fees'], $expNoEdit)}
                    <input type='hidden' name='theater_assistant_fees' value='{$row['theater_assistant_fees']}' />";
                } else {
                    $theatreFeesRow    = "{$formObj->getTBRow('Theatre Assistant Fees', 'theater_assistant_fees', $row['theater_assistant_fees'])}";
                }

                $tcChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Theatre Charges');
                if($tcChk > 0){
                    $theatreChargeRow    = "{$formObj->getTBRow('Theatre Charges', 'theatre_charges', $row['theatre_charges'], $expNoEdit)}
                    <input type='hidden' name='theatre_charges' value='{$row['theatre_charges']}' />";
                } else {
                    $theatreChargeRow    = "{$formObj->getTBRow('Theatre Charges', 'theatre_charges', $row['theatre_charges'])}";
                }

                $rcChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Referral Charges');
                if($rcChk > 0){
                    $refChargeRow    = "{$formObj->getTBRow('Referral Charges', 'referral_charges', $row['referral_charges'], $expNoEdit)}
                    <input type='hidden' name='referral_charges' value='{$row['referral_charges']}' />";
                } else {
                    $refChargeRow    = "{$formObj->getTBRow('Referral Charges', 'referral_charges', $row['referral_charges'])}";
                }

                $rdcChk = $this->getConditionForInvoiceItem($rowOrder['order_id'], 'Replacement Drug Charges');
                if($rdcChk > 0){
                    $drugChargeRow    = "{$formObj->getTBRow('Replacement Drug Charges', 'replacement_drug_charges', $row['replacement_drug_charges'], $expNoEdit)}
                    <input type='hidden' name='replacement_drug_charges' value='{$row['replacement_drug_charges']}' />";
                } else {
                    $drugChargeRow    = "{$formObj->getTBRow('Replacement Drug Charges', 'replacement_drug_charges', $row['replacement_drug_charges'])}";
                }
            }

            $modObj = getCPModuleObj('hms_order');
            $invoicePortalDisplay =  $this->getInvoicePortalDisplay($rowOrder['order_id']);

            $formActionReceipt = "index.php?module=hms_order&_spAction=generateReceiptForm&order_id={$rowOrder['order_id']}&patient_information_id={$row['patient_information_id']}&in_patient_id={$row['in_patient_id']}&showHTML=0";

            $actionButtons ="
            <div class='btn btn-info mb5'>
                <a href='{$formActionReceipt}' id='generateReceipt'>CREATE RECEIPT</a>
            </div>
            ";
            $receiptPortalDisplay =  $this->getReceiptPortalDisplay($rowOrder['order_id']);
        }
        else{
            if($row['status'] != 'Cancelled'){
                $generateOrder = "<a href='#' id='createOrderRecord' in_patient_id='{$row['in_patient_id']}' class='btn btn-info'>Generate Bill</a>";            
                $cancelAdmission = "<a in_patient_id='{$row['in_patient_id']}' class='btn btn-danger cancelAdmissionRecord'>Cancel Admission</a>";
            }
            
            $advanceRow = $formObj->getTBRow('Advance Payment', 'advance_payment', $row['advance_payment']);
            $drFeesRow = $formObj->getTBRow('Dr Fees', 'dr_fees', $row['dr_fees']);
             $entFeesRow = $formObj->getTBRow('ENT Fees', 'ent_fees', $row['ent_fees']);
                     $dressingFeesRow       = $formObj->getTBRow('Dressing Fees', 'dressing_fees', $row['dressing_fees']);
        $injectionFeesRow       = $formObj->getTBRow('Injection Fees', 'injection_fees', $row['injection_fees']);
        $foreignBodyRemovalRow       = $formObj->getTBRow('Foreign Body Removal', 'foreign_body_removal', $row['foreign_body_removal']);
        $waxRemovalRow       = $formObj->getTBRow('Wax Removal', 'wax_removal', $row['wax_removal']);
        $ivFluidRow       = $formObj->getTBRow('Iv Fluid', 'iv_fluid', $row['iv_fluid']);
        $xRayRow       = $formObj->getTBRow('X Ray', 'x_ray', $row['x_ray']);
        $aprConditionChargesRow       = $formObj->getTBRow('Apr Condition Charges', 'apr_condition_charges', $row['apr_condition_charges']);
        $infusionSyringeRow       = $formObj->getTBRow('Infusion Syringe', 'infusion_syringe', $row['infusion_syringe']);
        $rhyesTubeInsertionRow       = $formObj->getTBRow('Rhyes Tube Insertion', 'rhyes_tube_insertion', $row['rhyes_tube_insertion']);
        $stomachWashRow       = $formObj->getTBRow('Stomach Wash', 'stomach_wash', $row['stomach_wash']);
        $enemaRow       = $formObj->getTBRow('ENEMA', 'enema', $row['enema']);
        $catherisationRow       = $formObj->getTBRow('Catherisation', 'catherisation', $row['catherisation']);
        $wardMachineRow       = $formObj->getTBRow('Ward Medicine Replacement', 'ward_medicine_replacement', $row['ward_medicine_replacement']);
        $otMedicineRow       = $formObj->getTBRow('Ot Medicine Replacement', 'ot_medicine_replacement', $row['ot_medicine_replacement']);
        $fileCardRow       = $formObj->getTBRow('File Card', 'file_card', $row['file_card']);
            $feesRow        = $formObj->getTBRow('Dr Fees', 'consultation_fees', $rowEmpVisit['consultation_fees']);
            $nursingFeesRow = $formObj->getTBRow('Nursing Fees', 'nursing_fees', $row['nursing_fees']);
            $otherFeesRow   = $formObj->getTBRow('Other Fees', 'other_fees', $row['other_fees']);
            $roomRentRow    = $formObj->getTBRow('Room Rent', 'amount', $row['amount']);
            $surFeesRow    = "{$formObj->getTBRow('Surgeon Fees', 'surgeon_fees', $row['surgeon_fees'])}";
            $asstSurFeesRow    = "{$formObj->getTBRow('Asst Surgeon Fees', 'asst_surgeon_fees', $row['asst_surgeon_fees'])}";
            $anaFeesRow    = "{$formObj->getTBRow('Anaesthetic Fees', 'anesthetic_fees', $row['anesthetic_fees'])}";
            $theatreFeesRow    = "{$formObj->getTBRow('Theatre Assistant Fees', 'theater_assistant_fees', $row['theater_assistant_fees'])}";
            $theatreChargeRow    = "{$formObj->getTBRow('Theatre Charges', 'theatre_charges', $row['theatre_charges'])}";
            $refChargeRow    = "{$formObj->getTBRow('Referral Charges', 'referral_charges', $row['referral_charges'])}";
            $drugChargeRow    = "{$formObj->getTBRow('Replacement Drug Charges', 'replacement_drug_charges', $row['replacement_drug_charges'])}";
        }
        
        $printPrescription = '';
        $labReport = '';
        $labReqReport = '';
        $printDischargeCard = '';
        if($row['status'] != 'Cancelled'){
            $urlPrescription = "index.php?module=hms_inPatient&_spAction=printPrescription&in_patient_id={$row['in_patient_id']}&showHTML=0";
            $printPrescription = "<a href='{$urlPrescription}' id='printPrescription' in_patient_id='{$row['in_patient_id']}' class='btn btn-info' target='_blank'>Print Prescription</a>";

            $urllabReport = "index.php?module=hms_inPatient&_spAction=printLabReport&in_patient_id={$row['in_patient_id']}&showHTML=0";
            $labReport = "<a href='{$urllabReport}' id='labReport' in_patient_id='{$row['in_patient_id']}' class='btn btn-info' target='_blank'>Lab Report</a>";

            $urllabReqReport = "index.php?module=hms_inPatient&_spAction=printLabRequestForm&in_patient_id={$row['in_patient_id']}&showHTML=0";
            $labReqReport = "<a href='{$urllabReqReport}' id='labReport' in_patient_id='{$row['in_patient_id']}' class='btn btn-info' target='_blank'>Lab Request Form</a>";
            
            $urlDischargeCard   = "index.php?module=hms_inPatient&_spAction=printDischargeCard&in_patient_id={$row['in_patient_id']}&showHTML=0";
            $printDischargeCard = "<a href='{$urlDischargeCard}' id='printDischargeCard' in_patient_id='{$row['in_patient_id']}' class='btn btn-info' target='_blank'>Discharge Summary</a>";
        }

        $gotoSearch = "
        <div class='floatbox editTopButtonActionDiv'>
            <div class='float_left'>
                {$generateOrder}
                {$updateOrder}
                {$printPrescription}
                {$labReqReport}
                {$labReport}
                {$printDischargeCard}
                {$gotoOrder}
                {$cancelAdmission}
            </div>
        </div>";

        $ip_code = "";
        $yearCreated = $fn->getCPDate($row['creation_date'], "y");
        if($row['code'] != ""){
            $ip_code = "IP-{$row['code']}-{$yearCreated}";
        }

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND site_id = {$cpSiteIdSession}";
        }

        /*$sqlEmployee = "
        SELECT employee_id
              ,(CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE visits_patients = 1
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";*/

        $sqlEmployee = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        AND e.category = 'Doctor' OR e.category = 'Duty Doctor'
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY e.employee_name
        ";

        $totalAmount = $rowEmpVisit['consultation_fees'] + $row['amount'] + $row['nursing_fees'] + $row['other_fees'];
        $totalAmount = number_format($totalAmount);
        $totalAmount = 'Amount: '.$totalAmount.'₹';

        $patSumCount = $fn->getRecordCount('patient_visit', "patient_information_id = '{$row['patient_information_id']}' AND status != 'Cancelled'");
        $pastVisit = '';

        if($patSumCount > 0){
            $viewOverallSummaryLink = "index.php?_topRm=main&module=hms_inPatient&_spAction=overallSummary&patient_visit_id={$row['patient_visit_id']}&patient_information_id={$row['patient_information_id']}&in_patient_id={$row['in_patient_id']}&showHTML=0";

            $pastVisit="<a href='{$viewOverallSummaryLink}' class='viewOverallSummary'><u>Past Summary</u></a>";
        }

        $admissionTypeArray = array(
            "Normal"
           ,"Surgery"
        );

        $surgeryTypeArray = array(
            "Minor"
           ,"Major"
        );

        $sqlEmployeeTheaterAssistant = "
        SELECT (CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE category = 'Theatre Assistant'
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

        $sqlEmployeeAnesthetists = "
        SELECT (CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE category = 'Anesthesiology'
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";

        $sqlEmployeeSurgeon = "
        SELECT (CASE 
                WHEN salutation = 'Dr' 
                THEN CONCAT_WS('', salutation, '.', first_name, middle_name, last_name) 
                ELSE CONCAT_WS('', first_name, middle_name, last_name)
                END) AS employee_name
        FROM employee
        WHERE category = 'General Surgeon'
        AND status = 'Active'
        {$appendSqlEmp}
        ORDER BY employee_name
        ";
        $expVl = array('sqlType' => 'OneField');

        $totalSurgeryAmount = $row['surgeon_fees'] + $row['anesthetic_fees'] + $row['theater_assistant_fees'] + $row['theatre_charges'];
        $totalSurgeryAmount = number_format($totalSurgeryAmount);
        $totalSurgeryAmount = 'Amount: '.$totalSurgeryAmount.'₹';

        $advice_discharge = "{$formObj->getHTMLEditor('Advice On Discharge', 'advice_discharge', $row['advice_discharge'])}";
        $summary = "{$formObj->getHTMLEditor('Summary', 'summary', $row['summary'])}";
        $system_examination = "{$formObj->getHTMLEditor('System Examination', 'system_examination', $row['system_examination'])}";
        $instruction_to_patient = "{$formObj->getHTMLEditor('Instruction To Patient', 'instruction_to_patient', $row['instruction_to_patient'])}";
        $treatment_given = "{$formObj->getHTMLEditor('Treatment Given', 'treatment_given', $row['treatment_given'])}";

        $text = "
        {$gotoSearch}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>In Patient Details</div>
                    <div class='toggle'></div>
                    <div class='float_right ml20'>{$ip_code}</div>
                    <div class='float_right createdModifiedEditTop'><b>Created By :</b> {$row['created_by']} on {$row['creation_date']}&nbsp;&nbsp;&nbsp;&nbsp;<b>Modified By:</b> {$row['modified_by']} {$row['modification_date']}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='7%'>
                                    <div class='mt15'>
                                        {$formObj->getTBRow('UHID', 'uhid', $row['uhid'], $expNoEdit)}
                                    </div>
                                </td>
                                <td width='18%'>
                                    {$formObj->getTBRow('Name', 'name', $row['patient_name'])}
                                </td>
                                <td width='15%'>{$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVl)}
                                </td>
                                <td width='12%'>{$formObj->getTBRow('Mobile', 'patient_phone', $row['patient_phone'])}</td>
                                <td width='32%'>
                                    <div class='ageBox'>{$formObj->getTBRow('Age (Yrs)' , 'age_year', $row['age_year'])}</div>
                                    <div class='ageBox'>{$formObj->getTBRow('(Month)' , 'age_month', $row['age_month'])}</div>
                                    <div class='ageBox'>{$formObj->getTBRow('(Days)' , 'age_day', $row['age_day'])}</div>
                                </td>
                                <td width='15%'>{$formObj->getTBRow('Father Name', 'father_name', $row['father_name'])}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='15%'>{$formObj->getTBRow('Husband Name', 'spuse_name', $row['spuse_name'])}</td>
                                <td width='15%'>{$formObj->getTBRow('Town/City', 'address_area', $row['address_area'])}</td>
                                <td width='10%'>{$formObj->getDDRowByArr('Status', 'status', $statusArray, $row['status'])}</td>
                                <td colspan='2'>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('HT', 'height', $row['height'])}</div>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('Wt (in kgs)', 'weight', $row['weight'])}</div>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('BMI', 'bmi', $row['bmi'])}</div>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('BP', 'blood_pressure', $row['blood_pressure'])}</div>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('PR', 'pulse_rate', $row['pulse_rate'])}</div>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('RR', 'respiratory_rate', $row['respiratory_rate'])}</div>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('SpO2', 'SpO2', $row['SpO2'])}</div>
                                    <div class='ageBox tempBox'>{$formObj->getTBRow('Temp-°F', 'temperature', $row['temperature'])}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>
                        Admission Details
                    </div>
                    <div class='admissionTotalAmount'>
                        - {$totalAmount}
                    </div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Admission Type', 'admission_type', $admissionTypeArray, $row['admission_type'])}</td>
                                <td>{$formObj->getDateRow('Date of Admission', 'date_admitted', $row['date_admitted'])}</td>
                                <td>{$formObj->getTimeRow('Time of Admitted', 'time_admitted', $row['time_admitted'])}</td>
                                <td>{$formObj->getDateRow('Date of Discharge', 'date_discharge', $row['date_discharge'])}</td>
                                <td>{$formObj->getTimeRow('Time of Discharge', 'time_of_discharge', $row['time_of_discharge'])}</td>
                             <td>{$formObj->getTBRow('Days Stayed', 'days_stayed', $row['days_stayed'])}</td>

                            </tr>
                            <tr>
                                <td>{$advanceRow}</td>
                                <td>{$drFeesRow}</td>
                                <td>{$dressingFeesRow}</td>
                                <td>{$injectionFeesRow}</td>
                                <td>{$foreignBodyRemovalRow}</td>
                                </tr>
                                <tr>
                                
                                <td>{$ivFluidRow}</td>
                                 <td>{$xRayRow}</td>
                                <td>{$entFeesRow}</td>
                                <td>{$roomRentRow}</td>
                                <td>{$nursingFeesRow}</td>
                                <td>{$otherFeesRow}</td>
                            </tr>
                            <tr>
                            <td>{$waxRemovalRow}</td>
                                <td>{$formObj->getDDRowBySQL('Consultant Dr.', 'consultant_doctor', $sqlEmployee, $row['consultant_doctor'], $expNewDoctor)}</td>
                                <td>{$formObj->getDDRowBySQL('Ref Dr.', 'ref_doctor', $sqlEmployee, $row['ref_doctor'], $expNewDoctor)}</td>
                                <td>{$formObj->getDDRowBySQL('Review with Dr.', 'review_doctor', $sqlEmployee, $row['review_doctor'], $expNewDoctor)}</td>
                                <td>{$formObj->getDateRow('Review Date', 'date_review', $row['date_review'])}</td>
                            </tr>
                            <tr>    
                                <td colspan='2'>{$formObj->getFieldSetWrapped('Advice On Discharge', $advice_discharge)}</td>
                                <td colspan='3'>{$formObj->getFieldSetWrapped('Summary', $summary)}</td>
                            </tr>
                            <tr>    
                                <td colspan='2'>{$formObj->getFieldSetWrapped('System Examination', $system_examination)}</td>
                                <td colspan='3'>{$formObj->getFieldSetWrapped('Instruction To Patient', $instruction_to_patient)}</td>
                            </tr>
                            <tr>    
                                <td colspan='2'>{$formObj->getFieldSetWrapped('Treatment Given', $treatment_given)}</td>
                                <input type='hidden' name='employee_in_patient_id' value='{$rowEmpVisit['employee_in_patient_id']}' />
                                <input type='hidden' name='in_patient_id' value='{$row['in_patient_id']}' />
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>
                        Surgery Details
                    </div>
                    <div class='admissionTotalAmount'>
                        - {$totalSurgeryAmount}
                    </div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDateRow('Date of Surgery', 'date_surgery', $row['date_surgery'])}</td>
                                <td>{$formObj->getTBRow('Theatre Assistant', 'theater_assistant', $row['theater_assistant'])}</td>
                                <td>{$formObj->getDDRowBySQL('Anaesthetist', 'anesthetists', $sqlEmployeeAnesthetists, $row['anesthetists'], $expNewDoctorAnesthesiology)}</td>
                                <td>{$formObj->getDDRowBySQL('Surgeon', 'surgeon', $sqlEmployeeSurgeon, $row['surgeon'], $expNewDoctorSurgeon)}</td>
                                <td>{$formObj->getDDRowByArr('Surgery Type', 'surgery_type', $surgeryTypeArray, $row['surgery_type'])}</td>
                                <td>{$surFeesRow}</td>
                            </tr>
                            <tr>    
                                <td>{$anaFeesRow}</td>
                                <td>{$theatreFeesRow}</td>
                                <td>{$formObj->getTBRow('Duration of Surgery', 'duration_of_surgery', $row['duration_of_surgery'])}</td>
                                <td>{$formObj->getTBRow('Name of Surgery', 'name_of_surgery', $row['name_of_surgery'])}</td>
                                <td>{$theatreChargeRow}</td>
                                <td>{$asstSurFeesRow}</td>
                            </tr>
                            <tr>    
                                <td>{$aprConditionChargesRow}</td>
                                <td>{$infusionSyringeRow}</td>
                                <td>{$rhyesTubeInsertionRow}</td>
                                <td>{$stomachWashRow}</td>
                                <td>{$enemaRow}</td>
                            </tr>
                            <tr>
                                <td>{$catherisationRow}</td>
                                <td>{$wardMachineRow}</td>
                                <td>{$otMedicineRow}</td>
                                <td>{$fileCardRow}</td>
                                
                            </tr>
                            <tr>    
                                <td>{$formObj->getTimeRow('Start Time', 'start_time', $row['start_time'])}</td>
                                <td>{$formObj->getTimeRow('End Time', 'end_time', $row['end_time'])}</td>
                                <td>{$refChargeRow}</td>
                                <td>{$drugChargeRow}</td>
                                <input type='hidden' name='in_patient_id' value='{$row['in_patient_id']}' />
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>
                        {$pastVisit}
                    </div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <div id='tabs' class='mb20'>
                        <ul>
                            {$medHisLbl}
                            {$medicineLbl}
                            {$medicalTestLbl}
                            {$addDrLbl}
                            {$addSpecialistLbl}
                        </ul>
                        {$medHisTab}
                        {$medicineTab}
                        {$medicalTestTab}
                        {$addDrTab}
                        {$addSpecialistTab}
                        <div class='tab-footer'>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id='patientVisitInvoicePortal'>
            {$invoicePortalDisplay}
        </div>

        {$actionButtons}
        <div id='patientVisitReceiptPortal'>{$receiptPortalDisplay}</div>
        <input type='hidden' id='fld_order_id' name='order_id' value='{$rowOrder['order_id']}' />
        ";

        return $text;
    }

    /**
     *
     */
    function getOverallSummary(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $patient_information_id = $fn->getReqParam('patient_information_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $in_patient_id = $fn->getReqParam('in_patient_id');        

        $appendSqlPV = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPV = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQLPatientVisit = "
        SELECT pv.*
        FROM patient_visit pv
        WHERE pv.patient_information_id = '{$patient_information_id}'
        AND pv.status != 'Cancelled'
        ORDER BY check_up_date DESC
        ";
        $resultPv   = $db->sql_query($SQLPatientVisit);
        $employee_id_count = '';
        $treatment = '';
        $employeeTitle = '';
        $PvText = '';
        $balance_Amount = '0.00';
        $overall_balance_Amount = '0.00';
        while ($rowPv = $db->sql_fetchrow($resultPv)) {
            $SQLEV = "
            SELECT Distinct ev.employee_id
                  ,(CASE 
                    WHEN e.salutation = 'Dr' 
                    THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                    ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                    END) AS employee_name
                  ,ev.patient_visit_id
            FROM employee_visit ev
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE ev.patient_visit_id = '{$rowPv['patient_visit_id']}'
            GROUP BY employee_name
            ";
            $resultEv = $db->sql_query($SQLEV);
            $drAttended = '';
            while ($rowEv = $db->sql_fetchrow($resultEv)) {
                $drAttended .=$rowEv['employee_name'] . ', ';
            }
            $dr_attended = rtrim($drAttended,', ');
            $dr_attended =  substr($dr_attended , 0, 9);

            $SQL1 = "
            SELECT mt.title
            ,mt.notes
            FROM medical_test_visit mt
            WHERE mt.patient_visit_id = '{$rowPv['patient_visit_id']}'
            ";
            $result1 = $db->sql_query($SQL1);
            $labTest = '';
            while ($rowTv = $db->sql_fetchrow($result1)) {
                $labTest .= $rowTv['title'] . '-' . $rowTv['notes'] .  ', ';
            }
            $labTest = rtrim($labTest,', ');

            $SQL2 = "
            SELECT mt.*
            FROM medicines_visit mt
            WHERE mt.patient_visit_id = '{$rowPv['patient_visit_id']}'
            ";
            $result2 = $db->sql_query($SQL2);
            $medTest = '';
            $instructionDisplay = '';
            while ($row2 = $db->sql_fetchrow($result2)) {
                if($row2['qty'] == 0 || $row2['qty'] == ''){
                    $qty = "{$row2['qty']}";
                } else {
                    $qty = "({$row2['qty']})";                    
                }

                if($row2['instruction'] != ''){
                    $instruction = explode(", ", $row2['instruction']);
                    $instructionLen = count($instruction);

                    $morning = 0;
                    $noon = 0;
                    $night = 0;

                    if($row2['dosage'] == ''){
                        $row2['dosage'] = 1;
                    }

                    for($i=0;$i<$instructionLen;$i++){
                        //print $instruction[$i];
                        if($instruction[$i] == 'Morning'){
                            $morning = $row2['dosage'];
                        }
                        if($instruction[$i] == 'Noon'){
                            $noon = $row2['dosage'];
                        }
                        if($instruction[$i] == 'Night'){
                            $night = $row2['dosage'];
                        }
                    }

                    if($row2['instruction'] == 'STAT' || $row2['instruction'] == 'SOS'){
                        $instructionDisplay = $row2['instruction'];
                    } else {
                        $instructionDisplay = $morning.' - '.$noon.' - '.$night;
                    }
                }

                $medTest .="<div>{$row2['title']}{$qty} {$instructionDisplay}</div>";
            }
            //$medTest = rtrim($medTest,', ');

            $check_up_date = $fn->getCPDate($rowPv['check_up_date'],"d-m-Y");

            $orderRec = $fn->getRecordByCondition('order', "patient_visit_id = '{$rowPv['patient_visit_id']}' AND order_type = 'OP'");
            $total_invoice_amount = '0.00';
            $invoiced_Paid_Amount = '0.00';

            if($orderRec['order_id'] != ''){
                $subSqlForPercentSum = "
                SELECT SUM(ini.unit_price) AS total_invoice_amount
                       ,inv.discount 
                FROM `invoice`inv
                LEFT JOIN invoice_item ini ON (ini.invoice_id = inv.invoice_id)
                WHERE inv.order_id = {$orderRec['order_id']}
                AND inv.status != 'Cancelled'
                AND ini.record_type = 'Doctor/Nurse'
                ";
                $resultSubSql = $db->sql_query($subSqlForPercentSum);
                $rowSql       = $db->sql_fetchrow($resultSubSql);

                $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
                $total_invoice_amount = number_format($total_invoice_amount, 2);
            }

            $visit_code_Link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowPv['patient_visit_id']}";
            $visit_codePVt = "<a href='{$visit_code_Link}' target='_blank'>VST- {$rowPv['visit_code']}</a>";

            $viewSummaryTreatmentLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=viewSummaryTreatment&patient_visit_id={$rowPv['patient_visit_id']}&showHTML=0";
            $viewSummaryTreatment = "<a href='{$viewSummaryTreatmentLink}' class='viewSummaryForTreatmentRecord'><u>View Summary</u></a>";

            //$applyMedicineLink = "index.php?_topRm=main&module=hms_patientVisit&_spAction=applyMedicine&patient_visit_id={$rowPv['patient_visit_id']}&patient_visit_id_main={$patient_visit_id}&showHTML=0";
            $applyMedicine = '';
            if($medTest != '') {
                $applyMedicine = "<a href='#' class='btn btn-info applyForMedicineRecord' patient_visit_id={$rowPv['patient_visit_id']} in_patient_id={$in_patient_id}>Apply</a>";
            }

            $PvText .= "
            <tr>
                <td width='10%'>{$check_up_date}</td>
                <td width='10%'>{$visit_codePVt}</td>
                <td width='10%'>{$dr_attended}</td>
                <td width='15%'>{$rowPv['complain']}</td>
                <td width='37%'>{$applyMedicine}{$medTest}</td>
                <td width='13%'>{$labTest}</td>
                <td width='5%'>{$total_invoice_amount}</td>
                <td width='10%'>{$rowPv['notes']}</td>
            </tr>
            ";
        }

        $text = "
        <div class=''>
            <div>
                <div class='patientVisitSummaryPortal'>
                    <table class='thinlist mb20 overallSummaryPortal'>
                        <thead>
                            <tr>
                                <th class=''>Date</td>
                                <th class=''>Code</td>
                                <th class=''>Attended By</td>
                                <th class=''>Disease List</td>
                                <th class=''>Medicine List</td>
                                <th class=''>Lab Test</td>
                                <th class=''>Fees</td>
                                <th class=''>Notes</td>
                            </tr>
                        </thead>
                        <tbody>
                            {$PvText}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <input type='hidden' id='fld_patient_information_id' value='{$patient_information_id}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');


        $record_id = $fn->getIssetParam($row, 'in_patient_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_inPatient', 'attachment', $row)}
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
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $status  = $fn->getReqParam('status');
        $admission_type  = $fn->getReqParam('admission_type');
        $currentMonth    = $fn->getReqParam('currentMonth');

        $statusArray = array(
            "Admitted"
           ,"Discharged"
           ,"On Hold"
           ,"Cancelled"
        );

        $admissionTypeArray = array(
            "Normal"
           ,"Surgery"
        );

        $currMonthArray = array(
             "All Records"
            ,"CurrentMonth"
        );

        if($currentMonth == ""){
            $currentMonth = 'CurrentMonth';
        }

        $text = "
        <td>
            <select name='currentMonth'>
                {$cpUtil->getDropDown1($currMonthArray, $currentMonth)}
           </select>
        </td>
        <td>
            <select name='admission_type'>
                <option value=''>Admission Type</option>
                {$cpUtil->getDropDown1($admissionTypeArray, $admission_type)}
           </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($statusArray, $status)}
           </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getChiefComplainsDisplay($in_patient_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($in_patient_id == ''){
            $in_patient_id = $fn->getReqParam('in_patient_id');
        }

        $formAction          = "index.php?_topRm=main&module=hms_inPatient&_spAction=chiefComplainsSubmit&showHTML=0";
        $rows                = "";
        $inputRow            = "";
        $viewComplainListLink = "index.php?_topRm=main&module=hms_inPatient&_spAction=complainList&in_patient_id={$in_patient_id}&showHTML=0";
        $viewComplainList     = "<a href='{$viewComplainListLink}' class='viewComplainListRecord btn btn-info float_left'>Complain List</a>";
        $patientVisitRec     = $fn->getRecordRowByID('in_patient', 'in_patient_id', $in_patient_id);

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_complainDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    <div class='addComplain float_left mb10'>
                        <input type='text' value='' id='fld_complain_title' class='text' name='complain_title' placeholder='Add Complain' in_patient_id={$in_patient_id}>
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-info complainSave' type='button' value='Add' name='portalForm' />
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-warning' type='submit' value='Save' name='portalForm' />
                    </div>
                </div>

                <!--<div class='type-button floatbox'>
                    <input class='button float_left' type='submit' value='Save' name='portalForm' />
                    {$viewComplainList}
                </div>-->
                <div class=''>
                    {$formObj->getTARow('Complain', 'complain', $patientVisitRec['complain'])}
                </div>
                <input type='hidden' name='in_patient_id' value='{$in_patient_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getProcedurePortalDisplay($in_patient_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($in_patient_id == ''){
            $in_patient_id = $fn->getReqParam('in_patient_id');
        }

        $formAction          = "index.php?_topRm=main&module=hms_inPatient&_spAction=procedurePortalSubmit&showHTML=0";
        $rows                = "";
        $inputRow            = "";
        $patientVisitRec     = $fn->getRecordRowByID('in_patient', 'in_patient_id', $in_patient_id);

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_procedureDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    <div class='addProcedure float_left mb10'>
                        <input type='text' value='' id='fld_procedure_title' class='text' name='procedure_title' placeholder='Add Procedure' in_patient_id={$in_patient_id}>
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-info procedureSave' type='button' value='Add' name='portalForm' />
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-warning' type='submit' value='Save' name='portalForm' />
                    </div>
                </div>

                <div class=''>
                    {$formObj->getTARow('Procedure', 'in_patient_procedure', $patientVisitRec['in_patient_procedure'])}
                </div>
                <input type='hidden' name='in_patient_id' value='{$in_patient_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryPortalDisplay($in_patient_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($in_patient_id == ''){
            $in_patient_id = $fn->getReqParam('in_patient_id');
        }

        $formAction          = "index.php?_topRm=main&module=hms_inPatient&_spAction=summaryPortalSubmit&showHTML=0";
        $rows                = "";
        $inputRow            = "";
        $viewDiseaseListLink = "index.php?_topRm=main&module=hms_inPatient&_spAction=diseaseList&in_patient_id={$in_patient_id}&showHTML=0";
        $viewDiseaseList     = "<a href='{$viewDiseaseListLink}' class='viewDiseaseListRecord btn btn-info float_left'>Diagnosis List</a>";
        $patientVisitRec     = $fn->getRecordRowByID('in_patient', 'in_patient_id', $in_patient_id);

        $text = "
        <div id='' class=''>
            <form></form>
            <form id='portalForm_summaryDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    <div class='addDiagnosis float_left mb10'>
                        <input type='text' value='' id='fld_diagnosis_title' class='text' name='diagnosis_title' placeholder='Add Diagnosis' in_patient_id={$in_patient_id}>
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-info diagnosisSave' type='button' value='Add' name='portalForm' />
                    </div>
                    <div class='float_left'>
                        <input class='btn btn-warning' type='submit' value='Save' name='portalForm' />
                    </div>
                </div>
                <!--<div class='type-button floatbox'>
                    <input class='button float_left' type='submit' value='Save' name='portalForm' />
                    {$viewDiseaseList}
                </div>-->
                <div class=''>
                    {$formObj->getTARow('Diagnosis', 'diagnosis', $patientVisitRec['diagnosis'])}
                </div>
                <input type='hidden' name='in_patient_id' value='{$in_patient_id}' />
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMedicinesPortalDisplay($in_patient_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $cpUtil = Zend_Registry::get('cpUtil');

        if($in_patient_id == ''){
            $in_patient_id = $fn->getReqParam('in_patient_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";
        $stock = '';

        $sqlInstruction = $fn->getValueListSQL('instruction');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT mv.*
              ,CONCAT_WS('',e.salutation,'.', e.first_name, e.middle_name, e.last_name) AS employee_name
        FROM medicines_in_patient mv
        LEFT JOIN (employee e) ON (e.employee_id = mv.employee_id)
        WHERE mv.in_patient_id = {$in_patient_id}
        ORDER BY mv.medicines_in_patient_id
        ";
        $result   = $db->sql_query($SQL);

        $SQLRoute      = $fn->getValueListSQL('route');
        $SQLdosage      = $fn->getValueListSQL('dosage');

        while ($rowMV = $db->sql_fetchrow($result)) {
            $editURL = "index.php?_topRm=main&module=hms_inPatient&_spAction=editMedicineRecord&showHTML=0&in_patient_id={$rowMV['in_patient_id']}&medicines_in_patient_id={$rowMV['medicines_in_patient_id']}";
            $editRow = "<td><a href='{$editURL}' id='editMedicineRecord' in_patient_id={$rowMV['in_patient_id']}>Edit</a></td>";
                /*<td class='title'>
                    <input type='text' value='{$rowMV['title']}' name='title' disabled>
                </td>*/
            $medicine_Link = "index.php?_topRm=utils&module=hms_product&_action=edit&product_id={$rowMV['product_id']}";     

            if($rowMV['continue_medicine'] == 1){
                $checked = "checked='checked'";
            } else {
                $checked = '';       
            }

            if($rowMV['treatment_given'] == 1){
                $checkedTreatment = "checked='checked'";
            } else {
                $checkedTreatment = '';       
            }

            if($rowMV['prescription'] == 1){
                $checkedPrescription = "checked='checked'";
            } else {
                $checkedPrescription = '';       
            }

            $rows .= "
            <tr recid='{$rowMV['medicines_in_patient_id']}' product_id='{$rowMV['product_id']}' class='portal-row2 row-hms_inPatient__hms_product'>
                <td class='employee_id'>
                    <select name='employee_id'>
                        <option value=''>Please Select</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlDoctor, $rowMV['employee_id'])}
                    </select>
                </td>
                <td class='title'>
                    <a href='{$medicine_Link}' target='_blank'><u>{$rowMV['title']}</u></a>
                </td>
                <td class='route'>
                    <select name='route' >
                        <option value=''>Select</option>
                            {$dbUtil->getDropDownFromSQLCols1($db, $SQLRoute, $rowMV['route'])}
                    </select>
                </td>
                <td class='dosage'>
                    <!--<select name='dosage'>
                        <option value=''>Select</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $SQLdosage, $rowMV['dosage'])}
                    </select>-->
                    <input type='text' value='{$rowMV['dosage']}' name='dosage'>
                </td>
                <td class='qty'>
                    <input type='text' value='{$rowMV['qty']}' name='qty'>
                </td>
                <td class='instruction'>
                    <select name='instruction'>
                        <option value=''>Please Select</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlInstruction, $rowMV['instruction'])}
                    </select>
                </td>
                <td class='days'>
                    <input type='text' value='{$rowMV['days']}' name='days'>
                </td>
                <td class='continue_medicine'>
                    <input  class='check' type='checkbox' value='{$rowMV['continue_medicine']}' name='continue_medicine' {$checked}>
                </td>
                <td class='treatment_given'>
                    <input  class='check' type='checkbox' value='{$rowMV['treatment_given']}' name='treatment_given' {$checkedTreatment}>
                </td>
                <td class='prescription'>
                    <input  class='check' type='checkbox' value='{$rowMV['prescription']}' name='prescription' {$checkedPrescription}>
                </td>
                <td><a href='#' class='deleteMedicineRecord' medicines_in_patient_id='{$rowMV['medicines_in_patient_id']}' in_patient_id={$rowMV['in_patient_id']}><u>Delete</u></a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th width='8%'>Dr</th>
            <th width='22%'>Medicine Name</th>
            <th width='7%'>Route</th>
            <th width='10%'>Dosage</th>
            <th width='5%'>Qty</th>
            <th width='20%'>Instruction</th>
            <th width='5%'>Days</th>
            <th width='5%'>Cont</th>
            <th width='5%'>Trtmt</th>
            <th width='5%'>Prescn</th>
            <th width='8%'>Delete</th>
        </tr>
        ";
        /*<div class='addNewMedicine float_left'>
            <input type='text' value='' id='fld_product_title' class='text' name='product_title_new' placeholder='Add New Medicine'>
        </div>
        <div class='float_left'>
            <input class='btn btn-info newProductTitle' type='button' value='Create' name='portalForm' />
        </div>*/

        $text = "
        <form></form>
        <div class='addExistingMedicine float_left mb10'>
            <input type='text' value='' id='fld_product_title' class='text' name='product_title' placeholder='Add Existing Medicine' in_patient_id={$in_patient_id}>
        </div>
        <div class='float_left'>
            <input class='btn btn-warning medicineSave' type='button' value='Save' name='portalForm' />
        </div>
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getMedicalPortalDisplay($in_patient_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');

        if($in_patient_id == ''){
            $in_patient_id = $fn->getReqParam('in_patient_id');
        }

        $formAction = "index.php?_topRm=main&module=hms_inPatient&_spAction=MedicalTestRecordSubmit&showHTML=0";
        $rows  = "";
        $catRows = '';
        $catLinks = '';

        $SQLCat = "
        SELECT value
              ,valuelist_id
        FROM valuelist
        WHERE key_text = 'investigationCategory'
          AND value != 'Vaccination'
        ORDER BY sort_order
        ";
        $resultCat   = $db->sql_query($SQLCat);
        $count = 0;
        while ($rowCat = $db->sql_fetchrow($resultCat)) {
            $SQLMedicalTest = "
            SELECT m.medical_test_id
                  ,m.title
                  ,m.fees
                  ,m.lab_supplier_fees
                  ,m.container
                  ,mtp.fees AS fees_history
                  ,mtp.medical_test_in_patient_id
                  ,mtp.notes
                  ,mtp.title AS title_history
                  ,mtp.test_repeat
                  ,mtp.creation_date
            FROM medical_test m
            LEFT JOIN medical_test_in_patient mtp ON (mtp.medical_test_id = m.medical_test_id AND mtp.in_patient_id = {$in_patient_id})
            WHERE m.category = '{$rowCat['value']}'
            ORDER BY m.title, mtp.medical_test_in_patient_id
            ";

            $result   = $db->sql_query($SQLMedicalTest);
            $inputRow  = "";
            $title ="{$rowCat['value']}";
            $countrow=1;
            $countAgain = 0;
            while ($row = $db->sql_fetchrow($result)) {
                $medTestVisitRec = $fn->getRecordByCondition('medical_test_in_patient', "in_patient_id = '{$in_patient_id}' AND title='{$row['title']}'");

                if($row['medical_test_in_patient_id'] != ''){
                    $checked = "checked='checked'";
                    $class ="";
                    $bgColor = "bgColorHighlight";
                } else {
                    $checked = '';
                    $class ="displayNone";
                    $bgColor = '';
                }

                if($medTestVisitRec['supplier_id'] > 0){
                    $checked1 = "checked='checked'";
                    $class1 ="";
                } else {
                    $checked1 = '';
                    $class1 ="displayNone";
                }

                if($row['notes'] != ''){
                    $notes = 'View Result';
                }else {
                    $notes = 'Add Result';
                }

                if($row['fees_history'] != ''){
                    $fees = $row['fees_history'];
                }else {
                    $fees = $row['fees'];
                }

                if($row['creation_date'] != ''){
                    $creationDate = $fn->getCPDate($row['creation_date'],"Y-m-d");
                }else {
                    $creationDate = date("Y-m-d");
                }

                        /*<div class='hideTreatmentDetails_{$row['title']}_{$count} hideLabDetails {$class} labVisitNotes'>
                            <input type='text' value='{$fees}' id='fld_fees' class='text mt10 mb10' name='fees[]'>
                            <div><a href='#' class='addNoteLab'>{$notes}</a></div>
                            <div class='hideNotesLab'>
                                <div class='type-text ym-fbox-text row_notes'>
                                    <textarea id='fld_notes' name='notes[]'>{$medTestVisitRec['notes']}</textarea>
                                </div>
                            </div>
                        </div>*/
                $divSeparation = '';
                if($countrow == 5){
                    $divSeparation = "</div><div class='col-md-4 col-sm-4'>";
                    $countrow = 0;
                }
                $SQLMTP = "
                SELECT m.medical_test_parameter_id
                      ,m.title
                      ,m.normal_value
                      ,m.medical_test_id
                FROM medical_test_parameter m
                WHERE m.medical_test_id = '{$row['medical_test_id']}'
                ORDER BY m.title
                ";

                $resultMTP   = $db->sql_query($SQLMTP);
                $numRows = $db->sql_numrows($resultMTP);
                $resultShow = '';
                $classResult = '';
                $view = '';
                if($numRows > 0){
                    $classResult = "hideme";

                    $viewMedPara = "index.php?_topRm=main&module=hms_inPatient&_spAction=viewMedicalParameters&in_patient_id={$in_patient_id}&medical_test_id={$row['medical_test_id']}&test_repeat={$row['test_repeat']}&showHTML=0";

                    $view = "<a href='{$viewMedPara}' class='viewMedPara float_right'><u>Add Result</u></a>";
                }

                $takeTestAgain = '';

                if($row['title_history'] != $row['title']){
                    $countAgain = 0;
                }

                if($row['title_history'] == $row['title'] && $countAgain == 0){
                    $takeTestAgain = "<a href='#' class='testAgain' in_patient_id = '{$in_patient_id}' medical_test_id='{$row['medical_test_id']}'><u>Take Test Again</u></a>";
                    $countAgain++;
                }
                $investigation_date_id = 'investigation_date_'.$row['medical_test_id'];
                $hospitalFees = $fees - $row['lab_supplier_fees'];
                $supplierRec = $fn->getRecordByCondition('supplier', "category = 'Lab'");
                
                $inputRow .= "
                <div class='medTestTitle'>
                    <div class='type-check ym-fbox-check labTestBox {$bgColor}'>
                        <input type='checkbox' id='title_{$count}' {$checked} value='{$row['title']}_{$count}' name='title[]' class='labTitle' in_patient_id = '{$in_patient_id}' medical_test_id='{$row['medical_test_id']}' test_repeat = '{$row['test_repeat']}'>
                        <label for='title_{$count}'>{$row['title']}</label>
                        <div class='hideTreatmentDetails_{$row['title']}_{$count} hideLabDetails {$class} labVisitNotes'>
                            {$view}
                            <div>
                                <label>Fees</label>
                                <input type='text' value='{$fees}' id='fld_fees' class='labFees text mt10 mb10' name='fees[]' in_patient_id = '{$in_patient_id}' medical_test_id='{$row['medical_test_id']}' test_repeat = '{$row['test_repeat']}'>
                            </div>
                            <div>
                                {$formObj->getDateRow('Date (Year-Month-Date)', $investigation_date_id, $creationDate)}
                                <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id' />
                                <input type='hidden' value='{$in_patient_id}' name='in_patient_id' />
                                <input type='hidden' value='{$row['test_repeat']}' name='test_repeat' />
                            </div>
                            <div class='{$classResult}'>
                                <label>Result</label>
                                <div class='type-text ym-fbox-text row_notes'>
                                    <textarea id='fld_notes' class='labNotes' name='notes[]' in_patient_id = '{$in_patient_id}' medical_test_id='{$row['medical_test_id']}' test_repeat = '{$row['test_repeat']}'>{$row['notes']}</textarea>
                                </div>
                            </div>
                            <div>
                                <input type='checkbox' id='' {$checked1} value='{$row['lab_supplier_fees']}' name='other_labs[]' class='otherLabs' in_patient_id = '{$in_patient_id}' medical_test_id='{$row['medical_test_id']}' supplier_id = {$supplierRec['supplier_id']} container = '{$row['container']}'>
                                <label for='{$row['medical_test_id']}'>Other Labs</label>
                            </div>
                            <div class='hideLabSupplierFees {$class1}'>
                                <div>
                                    <label>Lab Supplier Fees : {$row['lab_supplier_fees']}<label>
                                </div>
                                <div>
                                    <label>Hospital Fees : {$hospitalFees}<label>
                                </div>
                                <div>
                                    <label>Container : {$row['container']}<label>
                                </div>
                            </div>
                        </div>

                        <input type='hidden' value='{$row['medical_test_id']}' name='medical_test_id[]' />
                    </div>
                    <div class=''>{$takeTestAgain}</div>
                </div>
                {$divSeparation}
                ";
                $count ++;
                $countrow ++;
            }
            $catRows .= "
            <div class='panel panel-default'>
                <div class='panel-heading' id='{$title}'>
                    <strong>{$title}</strong>
                    <a href='#saveBtn' class='ml20'><u>Go to Top</u></a>
                    <a class='medTestMainSubmit btn btn-info ml20' in_patient_id = '{$in_patient_id}'>Save</a>
                </div>
                <div class='panel-body'><div class='floatbox col-md-4 col-sm-4'>{$inputRow}</div></div>
            </div>
            ";
            //$catLinks .= "<a href='#{$title}' class='mr20'><u>{$title}</u></a>";
            $catLinks .= "<li class='ui-state-default'><a href='#{$title}' valuelist_id='{$rowCat['valuelist_id']}' class='investigationCategoryLink mr20'><u>{$title}</u></a></li>";
        }

        $patientVisitRec = $fn->getRecordByCondition('in_patient', "in_patient_id = '{$in_patient_id}'");
        $text = "
        <div id='' class=''>
        <form></form>
            <form id='portalForm_medicalTestDisplay' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
                <div class='type-button' id='saveBtn'></div>
                <ul id='sortableInvesticationCategories'>  
                    {$catLinks}
                </ul>
                <br/><br/><br/>
                <div class='floatbox'>{$catRows}</div>
                <input type='hidden' name='in_patient_id' value='{$in_patient_id}' />
            </form>
            <script type='text/javascript'>
                jQuery('document').ready(function () {
                    cpm.hms.inPatient.sortInvestigationGroups();
                });
            </script>
        </div>
        ";
                /*<div class='type-button' id='saveBtn'>
                    <input class='button' type='submit' value='Save' name='portalForm' />
                </div>*/

        return $text;
    }
    /**
     *
     */
    function getViewMedicalParameters() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $medical_test_id = $fn->getReqParam('medical_test_id');
        $in_patient_id = $fn->getReqParam('in_patient_id');
        $test_repeat = $fn->getReqParam('test_repeat');

        if($test_repeat == ''){
            $test_repeat = 0;
        }

        $SQLMTP = "
        SELECT m.medical_test_parameter_id
              ,m.title
              ,m.normal_value
              ,m.medical_test_id
        FROM medical_test_parameter m
        WHERE m.medical_test_id = '{$medical_test_id}'
        ORDER BY m.medical_test_parameter_id
        ";

        $resultMTP   = $db->sql_query($SQLMTP);
        $numRows = $db->sql_numrows($resultMTP);
        $rowTitle = '';
        while ($rowMTP = $db->sql_fetchrow($resultMTP)) {
            $medVisitParaRec = $fn->getRecordByCondition('medical_visit_parameter', "medical_test_parameter_id = '{$rowMTP['medical_test_parameter_id']}' AND medical_test_id='{$rowMTP['medical_test_id']}' AND in_patient_id = {$in_patient_id} AND test_repeat = {$test_repeat}");

            $rowTitle .= "
            <div class='type-text ym-fbox-text row_notes medParaList'>
                <label>{$rowMTP['title']}</label> 
                <textarea id='fld_para_notes' class='med_para_notes' medical_test_id ='{$rowMTP['medical_test_id']}' medical_test_parameter_id={$rowMTP['medical_test_parameter_id']} in_patient_id = {$in_patient_id} test_repeat = {$test_repeat} name='para_notes[]'>{$medVisitParaRec['notes']}</textarea>
            </div>
            <input type='hidden' value='{$rowMTP['medical_test_parameter_id']}' name='medical_test_parameter_id[]' />
            <input type='hidden' value='{$rowMTP['medical_test_id']}' name='medical_test_id_para[]' />
            ";
        }
        $medTestVisitRecCon = $fn->getRecordByCondition('medical_test_in_patient', "in_patient_id = '{$in_patient_id}' AND medical_test_id='{$medical_test_id}' AND test_repeat = {$test_repeat}");
        if($medTestVisitRecCon['medical_test_in_patient_id'] != ''){
            $text = "
            {$rowTitle}
            <div><a class='medParaSubmit btn btn-info'>Save</a></div>
            ";
        } else{
            $text = "
            Please click save.
            ";            
        }


        return $text;
    }

    /**
     *
     */
    function getPrintPrescription() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Prescription');
        $pdf->SetTitle('Print Prescription');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 13);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $in_patient_id = $fn->getReqParam('in_patient_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.uhid
              ,m.title AS medicine
              ,m.instruction
              ,m.dosage
              ,m.route
              ,m.qty
              ,e.first_name
            FROM in_patient pv
            LEFT JOIN medicines_in_patient m ON (m.in_patient_id = pv.in_patient_id)
            LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
            LEFT JOIN (employee e) ON (e.employee_id = pv.employee_id)
            WHERE pv.in_patient_id = '{$in_patient_id}'
              AND m.prescription = 1
            ORDER BY 
            CASE
                WHEN (m.title LIKE 'inj.%' OR 
                      m.title LIKE '%.inj' OR
                      m.title LIKE 'inj %' OR
                      m.title LIKE '% inj'
                      ) THEN 1
                ELSE m.title
            END
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('','B', 9);

        $today = date("d-m-Y");
        $gender = '';
        if($company['gender'] == 'Female'){
            $gender = 'F';
        }else if($company['gender'] == 'Male'){
            $gender = 'M';            
        }

        $currentDate   = date("d-m-Y");

        $visit_code = '';
        $yearCreated = $fn->getCPDate($company['creation_date'], "y");
        if($company['code'] != ''){
            $visit_code = 'IP-'.$company['code'].'-'.$yearCreated;
        }

        $age = '';

        if($company['age_year'] != ''){
            $age = $company['age_year'].' Yrs';
        } elseif($company['age_month'] != ''){
            $age = $company['age_month'].' Months';
        } elseif($company['age_day'] != ''){
            $age = $company['age_day'].' Days';
        }

        //$doctorName = 'DR.SHEIK ABDUL KHADER';
        /*if($company['first_name'] == 'DR.SHEIK ABDUL KHADER'){
            $doctorName = $company['first_name'];
        }*/
        
        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td width="75%">UHID : '.$company['uhid'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Patient Name : '.$company['patient_name'].'&nbsp;&nbsp;('.$age .'/'.$gender.')</td>
                <td width="25%" align="left">Date &nbsp;: '.$currentDate.'</td>
            </tr>
            <tr>
                <td width="75%" align="">Doctor : '.$company['first_name'].'</td>
                <td width="25%" align="left">Code : '.$visit_code.'</td>
            </tr>
        </table>
        <table border="0" width="108%">
            <tr>
                <td align="right">Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().'</td>
            </tr>
        </table>
        ';
        
        $fbsVal = $fn->getRecordByCondition('medical_test_in_patient', "in_patient_id = '{$in_patient_id}' AND title='FBS'");
        $rbsVal = $fn->getRecordByCondition('medical_test_in_patient', "in_patient_id = '{$in_patient_id}' AND title='RBS'");
        $ppVal = $fn->getRecordByCondition('medical_test_in_patient', "in_patient_id = '{$in_patient_id}' AND title='POST PRANDIAL'");

        $blood_pressure = '';
        if($company['blood_pressure'] != ''){
            $blood_pressure = $company['blood_pressure'].' mm/hg';
        }
        $fbsVal_notes = '';
        if($fbsVal['notes'] != ''){
            $fbsVal_notes = $fbsVal['notes'].' mg/dl';
        }
        $rbsVal_notes = '';
        if($rbsVal['notes'] != ''){
            $rbsVal_notes = $rbsVal['notes'].' mg/dl';
        }
        
        $ppVal_notes = '';
        if($ppVal['notes'] != ''){
            $ppVal_notes = $ppVal['notes'].' mg/dl';
        }
       $tbl2 = '
        <table border="0" width="100%" style="padding-top:10px;">
            <tr>
                <td width="18%" align="left">T- '.$company['temperature'] .'</td>
                <td width="35%" align="left">BP- '.$blood_pressure.'</td>
                <td width="22%" align="left">PR- '.$company['pulse_rate'].'</td>
                <td width="25%" align="left">WT- '.$company['weight'].'Kgs</td>
            </tr>
        </table>
        <table border="0" width="100%">
            <tr>
                <td width="33%" align="left">FBS- '.$fbsVal_notes.'</td>
                <td width="33%" align="left">RBS- '.$rbsVal_notes.'</td>
                <td width="33%" align="left">PP- '.$ppVal_notes.'</td>
            </tr>
        </table>
        ';

        /*$tblHead ='
        <table border="1" width="100%" cellpadding="5">
            <tr>
                <td width="13%">BILL NO :</td>
                <td width="37%">'.$orderNo.'</td>
                <td width="9%">DATE :</td>
                <td width="41%">'.$company['invoice_creation_date'].'</td>
            </tr>
        </table>
        ';*/

        $tbl3 = '';

        $tbl3 ='<table border="0" width="100%" cellpadding="4" style="border-top:1px solid #000000;">';

        $sub_total = 0;
        $count = 1;
        $total_qty = 0;
        $discount = 0;
        $discountValueTotal = 0;

        while ($row = $db->sql_fetchrow($result)) {
            if($row['medicine'] != '' && $row['instruction'] != ''){
                /*if($count == 1){
                    $tbl3 ='<table border="0" width="100%" cellpadding="4">';
                }*/
                $instruction = explode(", ", $row['instruction']);
                //print_r($instruction);
                $instructionLen = count($instruction);
                //print($instructionLen);

                $morning = 0;
                $noon = 0;
                $night = 0;
                $dosage = '';

                if($row['dosage'] == ''){
                    $row['dosage'] = 1;
                }

                for($i=0;$i<$instructionLen;$i++){
                    if($instruction[$i] == 'Morning'){
                        if((strpos($row['dosage'], 'ML') !== false ) || (strpos($row['dosage'], 'ml') !== false)){
                            $morning = $row['dosage'];
                            $dosage = '';
                        }
                        else{
                            $morning = 1;
                            if($row['dosage']){
                                $dosage = '  ('.$row['dosage']. ')';
                            }
                        }
                    }
                    if($instruction[$i] == 'Noon'){
                        if((strpos($row['dosage'], 'ML') !== false ) || (strpos($row['dosage'], 'ml') !== false)){
                            $noon = $row['dosage'];
                            $dosage = '';
                        }
                        else{
                            $noon = 1;
                            if($row['dosage']){
                                $dosage = '  ('.$row['dosage']. ')';
                            }
                        }
                    }
                    if($instruction[$i] == 'Night'){
                        if((strpos($row['dosage'], 'ML') !== false ) || (strpos($row['dosage'], 'ml') !== false)){
                            $night = $row['dosage'];
                            $dosage = '';
                        }
                        else{
                            $night = 1;
                            if($row['dosage']){
                                $dosage = '  ('.$row['dosage']. ')';
                            }
                        }
                    }
                }

                if($row['qty'] == 0 || $row['qty'] == ''){
                    $qty = "{$row['qty']}";
                }else {
                    $qty = "({$row['qty']})";                    
                }

                $route = substr($row['route'], 0, 3);
                if($row['instruction'] == 'STAT' || $row['instruction'] == 'SOS'){
                    $tbl3 = $tbl3.'<tr>
                                        <td align="left" width="35%">'.$row['medicine'].'</td>
                                        <td align="left" width="10%">'.$qty.'</td>
                                        <td align="left" width="8%">'.$route.'</td>
                                        <td align="center" width="47%">'.$row['instruction'].'</td>
                                    </tr>
                                    ';
                } else {
                    $tbl3 = $tbl3.'<tr>
                                        <td align="left" width="35%">'.$row['medicine'].''.$dosage.'</td>
                                        <td align="left" width="10%">'.$qty.'</td>
                                        <td align="left" width="8%">'.$route.'</td>
                                        <td align="center" width="13%">'.$morning.'</td>
                                        <td align="center" width="4%">-</td>
                                        <td align="center" width="13%">'.$noon.'</td>
                                        <td align="center" width="4%">-</td>
                                        <td align="center" width="13%">'.$night.'</td>
                                    </tr>
                                    ';
                }
                if($count == 1){
                    //$tbl3 = $tbl3.'</table>';
                }
            }
            $count++;
        }
                    $tbl3 = $tbl3.'</table>';
        $SQL1 = "
        SELECT mt.*
              ,m.normal_value
        FROM medical_test_in_patient mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.in_patient_id = '{$in_patient_id}'
        ";
        $result1 = $db->sql_query($SQL1);
        $tbl4 = '
        <table cellpadding="4" border="0">
            <thead>
            <tr><th colspan="3" style="text-decoration:underline;">Reports:</th></tr>
            <tr>
                <th style="text-decoration:underline;">TEST</th>
                <th style="text-decoration:underline;">FINDINGS</th>
                <th style="text-decoration:underline;">REF-VALUE</th>
            </tr>
            </thead>
        ';
        while ($row1 = $db->sql_fetchrow($result1)) {

            /*$tbl4 = $tbl4.'<tr>
                                <td align="left">'.$row1['title'].'</td>
                                <td align="left">'.$row1['notes'].'</td>
                                <td align="left">'.$row1['normal_value'].'</td>
                            </tr>
                            ';*/
        }

        $tbl4 = $tbl4.'</table>';

        $pdf->writeHTML($tbl1, false, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tblHead, true, false, false, false, '');
        $pdf->writeHTML($tbl3, false, false, false, false, '');
        //$pdf->writeHTML($tbl4, true, false, false, false, '');
        $download_title = 'Prescription.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintLabReport() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Lab Report');
        $pdf->SetTitle('Lab Report');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $in_patient_id = $fn->getReqParam('in_patient_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.uhid
              ,e.first_name
              ,m.title AS medicine
              ,m.instruction
              ,m.dosage
              ,m.route
              ,m.qty
            FROM in_patient pv
            LEFT JOIN medicines_in_patient m ON (m.in_patient_id = pv.in_patient_id)
            LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
            LEFT JOIN (employee_in_patient ev) ON (ev.in_patient_id = pv.in_patient_id)
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE pv.in_patient_id = '{$in_patient_id}'
            ORDER BY m.medicines_in_patient_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('','B', 9);

        $today = date("d-m-Y");
        $gender = '';
        if($company['gender'] == 'Female'){
            $gender = 'F';
        }else if($company['gender'] == 'Male'){
            $gender = 'M';            
        }

        $currentDate   = date("d-m-Y");

        $visit_code = '';
        $yearCreated = $fn->getCPDate($company['creation_date'], "y");
        if($company['code'] != ''){
            $visit_code = 'IP-'.$company['code'].'-'.$yearCreated;
        }

        $age = '';

        if($company['age_year'] != ''){
            $age = $company['age_year'].' Yrs';
        } elseif($company['age_month'] != ''){
            $age = $company['age_month'].' Months';
        } elseif($company['age_day'] != ''){
            $age = $company['age_day'].' Days';
        }

        $doctorName = '';
        if($company['first_name'] == 'DR.SHEIK ABDUL KHADER'){
            $doctorName = $company['first_name'];
        }
        
        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td width="80%">UHID : '.$company['uhid'].'  &nbsp;&nbsp;&nbsp; Pt Name : '.$company['patient_name'].'('.$gender .'/'.$age.')</td>
                <td width="20%" align="left">Date &nbsp;: '.$currentDate.'</td>
            </tr>
            <tr>
                <td width="80%" align="">Doctor : '.$cpCfg['cp.doctorNamePDF'].'</td>
                <td width="20%" align="left">Code : '.$visit_code.'</td>
            </tr>
        </table>
        <table border="0" width="109%">
            <tr>
                <td align="right">Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().'</td>
            </tr>
        </table>
        ';

        $SQL1 = "
        SELECT mt.*
              ,m.normal_value
              ,m.fees AS amount
              ,m.units
        FROM medical_test_in_patient mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.in_patient_id = '{$in_patient_id}'
        ";
        $result1 = $db->sql_query($SQL1);
        $numRows1 = $db->sql_numrows($result1);
        $marginTop = '';
        if($numRows1 == 1){
            $marginTop = "<br/><br/>";
        }

        $tbl4 = '
        <table cellpadding="4" border="0">
            <thead>
            <tr><th colspan="3" style="text-decoration:underline;">Reports:</th></tr>
            <tr bgcolor="#D3D3D3" >
                <th style="border-left:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="35%">Test Name</th>
                <th style="border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="25%">Value</th>
                <th style="border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="19%">Units</th>
                <th style="border-right:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="21%">Ref.Range</th>
            </tr>
            </thead>
        ';
        while ($row1 = $db->sql_fetchrow($result1)) {

            $SQLMTP = "
            SELECT m.medical_test_parameter_id
                  ,m.title
                  ,m.normal_value
                  ,m.medical_test_id
                  ,m.units
            FROM medical_test_parameter m
            WHERE m.medical_test_id = '{$row1['medical_test_id']}'
            ORDER BY m.medical_test_parameter_id
            ";

            $resultMTP   = $db->sql_query($SQLMTP);
            $numRowsMTP = $db->sql_numrows($resultMTP);

            if($numRowsMTP > 0){
                $tbl4 = $tbl4.'
                <tr>
                    <td width="100%" align="left" style="line-height:30px;font-size:11px;">'.$row1['title'].'</td>
                </tr>
                ';
            }else{
                $tbl4 = $tbl4.'
                <tr>'.$marginTop.'
                    <td width="35%" align="left" style="line-height:30px;font-size:11px;">'.$row1['title'].'</td>
                    <td width="25%" align="left" style="line-height:30px;" >'.$row1['notes'].'</td>
                    <td width="13%" align="left" style="line-height:30px;">'.$row1['units'].'</td>
                    <td width="27%" align="left" style="line-height:30px;">'.$row1['normal_value'].'</td>
                </tr>
                ';
            }

            while ($rowMTP = $db->sql_fetchrow($resultMTP)) {
                $medVisitParaRec = $fn->getRecordByCondition('medical_visit_parameter', "medical_test_parameter_id = '{$rowMTP['medical_test_parameter_id']}' AND medical_test_id='{$rowMTP['medical_test_id']}' AND in_patient_id = {$in_patient_id} AND test_repeat = {$row1['test_repeat']}");
                $tbl4 = $tbl4.'<tr>
                            <td width="35%" align="left">'.strtoupper($rowMTP['title']).'</td>
                            <td width="25%" align="left">'.$medVisitParaRec['notes'].'</td>
                            <td width="19%" align="left" style="font-size:9px;">'.$rowMTP['units'].'</td>
                            <td width="21%" align="left" style="font-size:9px;">'.$rowMTP['normal_value'].'</td>
                        </tr>
                        ';
            }
        }

        $tbl4 = $tbl4.'</table>';

        $pdf->writeHTML($tbl1, false, false, false, false, '');
        //$pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tblHead, true, false, false, false, '');
        //$pdf->writeHTML($tbl3, false, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $download_title = 'Lab_Report.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintLabRequestForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Lab Request Form');
        $pdf->SetTitle('Lab Request Form');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $in_patient_id = $fn->getReqParam('in_patient_id');

        $SQL = "
        SELECT pv.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.uhid
              ,e.first_name
              ,m.title AS medicine
              ,m.instruction
              ,m.dosage
              ,m.route
              ,m.qty
            FROM in_patient pv
            LEFT JOIN medicines_in_patient m ON (m.in_patient_id = pv.in_patient_id)
            LEFT JOIN (patient_information p) ON (p.patient_information_id = pv.patient_information_id)
            LEFT JOIN (employee_in_patient ev) ON (ev.in_patient_id = pv.in_patient_id)
            LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
            WHERE pv.in_patient_id = '{$in_patient_id}'
            ORDER BY m.medicines_in_patient_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('','B', 9);

        $today = date("d-m-Y");
        $gender = '';
        if($company['gender'] == 'Female'){
            $gender = 'F';
        }else if($company['gender'] == 'Male'){
            $gender = 'M';            
        }

        $currentDate   = date("d-m-Y");

        $visit_code = '';
        $yearCreated = $fn->getCPDate($company['creation_date'], "y");
        if($company['code'] != ''){
            $visit_code = 'IP-'.$company['code'].'-'.$yearCreated;
        }

        $age = '';

        if($company['age_year'] != ''){
            $age = $company['age_year'].' Yrs';
        } elseif($company['age_month'] != ''){
            $age = $company['age_month'].' Months';
        } elseif($company['age_day'] != ''){
            $age = $company['age_day'].' Days';
        }

        $doctorName = '';
        if($company['first_name'] == 'DR.SHEIK ABDUL KHADER'){
            $doctorName = $company['first_name'];
        }
        
        $tbl1 = '
        <table border="0" width="100%" style="border-bottom:2px solid #000000; padding-bottom:3px;">
            <tr>
                <td width="80%">UHID : '.$company['uhid'].'  &nbsp;&nbsp;&nbsp; Pt Name : '.$company['patient_name'].'('.$gender .'/'.$age.')</td>
                <td width="20%" align="left">Date &nbsp;: '.$currentDate.'</td>
            </tr>
            <tr>
                <td width="80%" align="">Doctor : '.$cpCfg['cp.doctorNamePDF'].'</td>
                <td width="20%" align="left">Code : '.$visit_code.'</td>
            </tr>
        </table>
        <table border="0" width="109%">
            <tr>
                <td align="right">Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().'</td>
            </tr>
        </table>
        ';

        $SQL1 = "
        SELECT mt.*
              ,m.normal_value
              ,mt.fees AS amount
        FROM medical_test_in_patient mt
        LEFT JOIN medical_test m ON (m.medical_test_id = mt.medical_test_id)
        WHERE mt.in_patient_id = '{$in_patient_id}'
        ";
        $result1 = $db->sql_query($SQL1);
        $total_amount = 0;
        $tbl4 = '
        <table cellpadding="4" border="0" width="100%">
            <thead>
            <tr><th colspan="2" style="text-decoration:underline;">Lab Test Required:</th></tr>
            <tr bgcolor="#D3D3D3">
                <th style="border-left:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="70%">Test Name</th>
                <th align="right" style="border-right:1px solid #9A9A93;border-top:1px solid #9A9A93;border-bottom:1px solid #9A9A93;" width="30%">Fees (Rs)</th>
            </tr>
            </thead>
        ';
        while ($row1 = $db->sql_fetchrow($result1)) {

            $tbl4 = $tbl4.'<tr>
                                <td align="left" width="70%">'.$row1['title'].'</td>
                                <td align="right" width="30%">'.$row1['amount'].'</td>
                            </tr>
                            ';

            /*$SQLMTP = "
            SELECT m.medical_test_parameter_id
                  ,m.title
                  ,m.normal_value
                  ,m.medical_test_id
            FROM medical_test_parameter m
            WHERE m.medical_test_id = '{$row1['medical_test_id']}'
            ORDER BY m.title
            ";

            $resultMTP   = $db->sql_query($SQLMTP);
            $numRowsMTP = $db->sql_numrows($resultMTP);
            while ($rowMTP = $db->sql_fetchrow($resultMTP)) {
                $tbl4 = $tbl4.'
                        <tr>
                            <td align="left" style="font-size:9px;" width="70%">'.strtoupper($rowMTP['title']).'</td>
                            <td align="right"  width="30%"></td>
                        </tr>
                        ';
            }*/

            $total_amount += $row1['amount'];
        }

        $tbl4 = $tbl4.'<tr>
                            <td align="right" width="75%">Total Amount : </td>
                            <td align="right"  width="25%" style="border-top:1px solid #000000; border-bottom:1px solid #000000;">'.$total_amount.'</td>
                        </tr>
                    </table>';

        $pdf->writeHTML($tbl1, false, false, false, false, '');
        //$pdf->writeHTML($tbl2, true, false, false, false, '');
        //$pdf->writeHTML($tblHead, true, false, false, false, '');
        //$pdf->writeHTML($tbl3, false, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $download_title = 'Lab_Report.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getDoctorPortalDisplay($in_patient_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($in_patient_id == ''){
            $in_patient_id = $fn->getReqParam('in_patient_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT ev.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM employee_in_patient ev
        LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
        WHERE ev.in_patient_id = {$in_patient_id}
        AND ev.type IS NULL
        ORDER BY ev.employee_in_patient_id
        ";
        $result   = $db->sql_query($SQL);

        while ($rowEV = $db->sql_fetchrow($result)) {

            if($rowEV['employee_id'] == ""){
                $rowEV['employee_name'] = "Nurse";
                $rowEV['category']      = "Nurse";
            }

            $editURL = "index.php?_topRm=main&module=hms_inPatient&_spAction=editDoctorRecord&showHTML=0&in_patient_id={$rowEV['in_patient_id']}&employee_in_patient_id={$rowEV['employee_in_patient_id']}";
            $editRow = "<td><a href='{$editURL}' id='editDoctorRecord' in_patient_id={$rowEV['in_patient_id']}><u>Edit</u></a></td>";
            $rows .= "
            <tr>
                <td>{$rowEV['category']}</td>
                <td>{$rowEV['employee_name']}</td>
                <td>{$rowEV['consultation_fees']}</td>
                <td>{$rowEV['no_of_visits']}</td>
                <td>{$rowEV['notes']}</td>
                {$editRow}
                <td><a href='#' class='deleteDoctorRecord' employee_in_patient_id='{$rowEV['employee_in_patient_id']}' in_patient_id={$rowEV['in_patient_id']}><u>Delete</u></a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Dr</th>
        <th>Name</th>
        <th>Consulting Fees</th>
        <th>No.of Visit</th>
        <th>Notes</th>
        <th>Edit</th>
        <th>Delete</th>
        </tr>
        ";

        $text = "
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
     function getAddDoctorRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_inPatient&_spAction=addDoctorRecordSubmit&showHTML=0";
        $in_patient_id = $fn->getReqParam('in_patient_id');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.first_name
        ";

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor)}
            {$formObj->getTBRow('Consulting Fees', 'consultation_fees')}
            {$formObj->getTARow('Notes', 'notes')}
            <input type='hidden' name='in_patient_id' value='{$in_patient_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditDoctorRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_inPatient&_spAction=editDoctorRecordSubmit&showHTML=0";
        $in_patient_id = $fn->getReqParam('in_patient_id');
        $employee_in_patient_id = $fn->getReqParam('employee_in_patient_id');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT ev.*
        FROM employee_in_patient ev
        WHERE ev.employee_in_patient_id = {$employee_in_patient_id}
        ";
        $result   = $db->sql_query($SQL);
        $rowEV = $db->sql_fetchrow($result);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Doctor', 'employee_id', $sqlDoctor, $rowEV['employee_id'])}
            {$formObj->getTBRow('Consulting Fees', 'consultation_fees', $rowEV['consultation_fees'])}
            {$formObj->getTBRow('No.of Visit', 'no_of_visits', $rowEV['no_of_visits'])}
            {$formObj->getTARow('Notes', 'notes', $rowEV['notes'])}
            <input type='hidden' name='employee_in_patient_id' value='{$employee_in_patient_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getSpecialistPortalDisplay($in_patient_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($in_patient_id == ''){
            $in_patient_id = $fn->getReqParam('in_patient_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT ev.*
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM employee_in_patient ev
        LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
        WHERE ev.in_patient_id = {$in_patient_id}
        AND ev.type = 'Specialist'
        ORDER BY ev.employee_in_patient_id
        ";
        $result   = $db->sql_query($SQL);

        while ($rowEV = $db->sql_fetchrow($result)) {

            if($rowEV['employee_id'] == ""){
                $rowEV['employee_name'] = "Nurse";
                $rowEV['category']      = "Nurse";
            }

            $editURL = "index.php?_topRm=main&module=hms_inPatient&_spAction=editSpecialistRecord&showHTML=0&in_patient_id={$rowEV['in_patient_id']}&employee_in_patient_id={$rowEV['employee_in_patient_id']}";
            $editRow = "<td><a href='{$editURL}' id='editSpecialistRecord' in_patient_id={$rowEV['in_patient_id']}><u>Edit</u></a></td>";
            $rows .= "
            <tr>
                <td>{$rowEV['category']}</td>
                <td>{$rowEV['employee_name']}</td>
                <td>{$rowEV['consultation_fees']}</td>
                <td>{$rowEV['no_of_visits']}</td>
                <td>{$rowEV['notes']}</td>
                {$editRow}
                <td><a href='#' class='deleteSpecialistRecord' employee_in_patient_id='{$rowEV['employee_in_patient_id']}' in_patient_id={$rowEV['in_patient_id']}><u>Delete</u></a></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Dr</th>
        <th>Name</th>
        <th>Fees</th>
        <th>No.of Visit</th>
        <th>Notes</th>
        <th>Edit</th>
        <th>Delete</th>
        </tr>
        ";

        $text = "
        <div id='' class='doctorDisplay'>
            <form id='' class='' method='post' action='{$formAction}'>
                <div id='doctorPortalOuter'>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </form>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
     function getAddSpecialistRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_inPatient&_spAction=addSpecialistRecordSubmit&showHTML=0";
        $in_patient_id = $fn->getReqParam('in_patient_id');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.first_name
        ";

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Specialist', 'employee_id', $sqlDoctor)}
            {$formObj->getTBRow('Fees', 'consultation_fees')}
            {$formObj->getTBRow('No.of Visit', 'no_of_visits')}
            {$formObj->getTARow('Notes', 'notes')}
            <input type='hidden' name='in_patient_id' value='{$in_patient_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditSpecialistRecord() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';

        $formAction = "index.php?_topRm=main&module=hms_inPatient&_spAction=editSpecialistRecordSubmit&showHTML=0";
        $in_patient_id = $fn->getReqParam('in_patient_id');
        $employee_in_patient_id = $fn->getReqParam('employee_in_patient_id');

        $appendSqlEmp = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlEmp = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlDoctor = "
        SELECT e.employee_id
              ,(CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
        FROM employee e
        WHERE e.visits_patients = 1
        {$appendSqlEmp}
        ORDER BY e.employee_id
        ";

        $SQL = "
        SELECT ev.*
        FROM employee_in_patient ev
        WHERE ev.employee_in_patient_id = {$employee_in_patient_id}
        AND ev.type = 'Specialist'
        ";
        $result   = $db->sql_query($SQL);
        $rowEV = $db->sql_fetchrow($result);

        $text = "
        <form id='portalForm' class='yform columnar invoiceForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Specialist', 'employee_id', $sqlDoctor, $rowEV['employee_id'])}
            {$formObj->getTBRow('Fees', 'consultation_fees', $rowEV['consultation_fees'])}
            {$formObj->getTBRow('No.of Visit', 'no_of_visits', $rowEV['no_of_visits'])}
            {$formObj->getTARow('Notes', 'notes', $rowEV['notes'])}
            <input type='hidden' name='employee_in_patient_id' value='{$employee_in_patient_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryInOrder() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $order_id = $fn->getReqParam('order_id');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND o.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT o.*
              ,(SELECT SUM(round((oi.unit_price * oi.qty),2))
               FROM order_item oi
               WHERE oi.order_id = o.order_id
               ) AS order_amount
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                WHERE i.order_id = o.order_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM receipt r
                WHERE o.order_id = r.order_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
              ,(SELECT  SUM(oi.unit_price)
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type = 'Doctor/Nurse'
                )AS consultation_fees
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS Total_Amount
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                LEFT JOIN `invoice` i ON (i.order_id = {$order_id})
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) as Amount_Paid
        FROM `order`o
        WHERE o.order_id = {$order_id}
        {$appendSql}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $orderAmt   = number_format(round($row['order_amount']), 2);
        $invoiceAmt = number_format($row['invoice_amount'] ,2);
        $receiptAmt = number_format($row['receipt_amount'] ,2);

        $total_invoice_amount = 0;
        if($row['invoice_amount'] != ''){
            $total_invoice_amount = $row['invoice_amount'] - $row['discount'];
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
        }else{
            $total_invoice_amount = $row['invoice_amount'];
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
        }

        $total_invoice_amount = number_format($total_invoice_amount, 2);

        $outstandingInvoiceAmt = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);
        $overallBalanceAmt     = number_format($row['order_amount'] - $row['receipt_amount'], 2);

        $order_items_Details = '';

        $appendSqlRecord = '';
        if($row['order_type'] == 'IP'){
            $appendSqlRecord = "AND record_type NOT IN ('Medical Test')";
        }

        $Lab = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$row['order_id']}
        AND record_type != ''
        {$appendSqlRecord}
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        $Sub_Total = 0;
        if($numRowsOrderItem > 0){
            $count = 1;
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$row['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                    $rowOrderItem['record_type'] = 'Consultation Fees';
                }


                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount'] = $rowOrderItem['QTY_AMOUNT'];
                }

                $Lab .= "<tr>
                            <td><b>{$rowOrderItem['record_type']}</b>
                            <ol>
                        ";


                if($numRowsList > 0){
                    while($rowList    = $db->sql_fetchrow($resultList)){
                        if($rowOrderItem['record_type'] != 'Consultation Fees'){
                            $Lab .= "<li>{$rowList['item_title']}</li>";
                        }
                    }
                }

                $Lab .="</ol></td>
                                <td class='txtRight'>{$rowOrderItem['Amount']}</td>
                            </tr>";

                $Sub_Total += $rowOrderItem['Amount'];

                $count++;
            }
        }

        $order_items_Details .="{$Lab}";
        $total_amount = number_format($Sub_Total - $row['discount'], 2);
        $Sub_Total    = number_format($Sub_Total, 2);
        $discount     = number_format($row['discount'], 2);

        $rows = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <table class='thinlist'>
                        <tr>
                            <th>Total Amount: {$total_invoice_amount}</th>
                            <th>Amount Paid: {$invoiced_Paid_Amount}</th>
                            <th>Amount Due: {$balance_Amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tr>
                            <th>Description</th>
                            <th class='txtRight'>Amount</th>
                        </tr>
                        {$order_items_Details}
                        <tr>
                            <th>Sub Total</th>
                            <th class='txtRight'>{$Sub_Total}</th>
                        </tr>
                        <tr>
                            <th>Discount</th>
                            <th class='txtRight'>{$discount}</th>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <th class='txtRight'>{$total_amount}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        ";

        $text = "
        {$rows}
        ";

        return $text;

    }

    /**
     *
     */
    function getPrintDischargeCard() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Discharge Card');
        $pdf->SetTitle('Print Discharge Card');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //$pdf->setPrintFooter(false);

        // set margins
        $pdf->SetMargins(5, PDF_MARGIN_TOP, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER, 1);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->setPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $in_patient_id = $fn->getReqParam('in_patient_id');

        $SQLIp = "
        SELECT ip.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.address_area
              ,p.uhid
        FROM in_patient ip
        LEFT JOIN (patient_information p) ON (p.patient_information_id = ip.patient_information_id)
        WHERE ip.in_patient_id = '{$in_patient_id}'
        ";
        $resultIp = $db->sql_query($SQLIp);
        $result2  = $db->sql_query($SQLIp);
        $rowIp    = $db->sql_fetchrow($result2);

        $date_admitted  = $fn->getCPDate($rowIp['date_admitted'], "d-m-Y");
        $date_discharge = $fn->getCPDate($rowIp['date_discharge'], "d-m-Y");
        $date_surgery   = $fn->getCPDate($rowIp['date_surgery'], "d-m-Y");
        $date_review    = $fn->getCPDate($rowIp['date_review'], "d-m-Y");

        $age = '';

        if($rowIp['age_year'] != ''){
            $age .= $rowIp['age_year'].' Yrs';
        } elseif($rowIp['age_month'] != ''){
            $age .= $rowIp['age_month'].' Months';
        } elseif($rowIp['age_day'] != ''){
            $age .= $rowIp['age_day'].' Days';
        }

        $gender = '';
        if($rowIp['gender'] == 'Female'){
            $gender = 'F';
        }else if($rowIp['gender'] == 'Male'){
            $gender = 'M';            
        }

        $SQLVE = "
        SELECT (CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM employee_in_patient ev
        LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
        WHERE ev.in_patient_id = {$in_patient_id}
        ORDER BY ev.employee_in_patient_id
        ";
        $resultVE = $db->sql_query($SQLVE);
        $rowVE = $db->sql_fetchrow($resultVE);

        $SQLEV = "
        SELECT (CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM employee_in_patient ev
        LEFT JOIN (employee e) ON (e.employee_id = ev.employee_id)
        WHERE ev.in_patient_id = {$in_patient_id}
        ORDER BY ev.employee_in_patient_id
        ";
        $resultEV = $db->sql_query($SQLEV);
        $ConsultantDr = "";
        while ($rowEV = $db->sql_fetchrow($resultEV)) {
            $ConsultantDr .= $rowEV['employee_name']."<br> ";
        }
        $ConsultantDr = rtrim($ConsultantDr, ", ");

        $sqlEmployeeRev = "
        SELECT (CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM in_patient i
        LEFT JOIN (employee e) ON (e.employee_id = i.review_doctor)
        WHERE i.in_patient_id = {$in_patient_id}
        ";
        $resultEmployeeRev = $db->sql_query($sqlEmployeeRev);
        $rowEmployeeRev = $db->sql_fetchrow($resultEmployeeRev);

        $sqlEmployeeConsult = "
        SELECT (CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM in_patient i
        LEFT JOIN (employee e) ON (e.employee_id = i.consultant_doctor)
        WHERE i.in_patient_id = {$in_patient_id}
        ";
        $resultEmployeeConsult = $db->sql_query($sqlEmployeeConsult);
        $rowEmployeeConsult = $db->sql_fetchrow($resultEmployeeConsult);

        $sqlEmployeeRef = "
        SELECT (CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM in_patient i
        LEFT JOIN (employee e) ON (e.employee_id = i.ref_doctor)
        WHERE i.in_patient_id = {$in_patient_id}
        ";
        $resultEmployeeRef = $db->sql_query($sqlEmployeeRef);
        $rowEmployeeRef = $db->sql_fetchrow($resultEmployeeRef);

        $tbl4 ='<table border="0" width="100%" cellpadding="4">';

        //AND (mv.continue_medicine = 0 OR mv.continue_medicine IS NULL)
        $SQLMV = "
        SELECT mv.*
        FROM medicines_in_patient mv
        WHERE mv.in_patient_id = {$in_patient_id}
        AND mv.treatment_given = 1
        ORDER BY mv.medicines_in_patient_id
        ";
        $resultMV = $db->sql_query($SQLMV);
        $count = 0;
        $treatmentTitle = '';
        while ($rowMV = $db->sql_fetchrow($resultMV)) {
            if($rowMV['title'] != '' && $rowMV['instruction'] != ''){
                $instruction = explode(", ", $rowMV['instruction']);
                $instructionLen = count($instruction);

                $morning = 0;
                $noon = 0;
                $night = 0;

                if($rowMV['dosage'] == ''){
                    $rowMV['dosage'] = 1;
                }

                for($i=0;$i<$instructionLen;$i++){
                    if($instruction[$i] == 'Morning'){
                        $morning = $rowMV['dosage'];
                    }
                    if($instruction[$i] == 'Noon'){
                        $noon = $rowMV['dosage'];
                    }
                    if($instruction[$i] == 'Night'){
                        $night = $rowMV['dosage'];
                    }
                }

                if($rowMV['qty'] == 0 || $rowMV['qty'] == ''){
                    $qty = "{$rowMV['qty']}";
                }else {
                    $qty = "({$rowMV['qty']})";                    
                }

                $morningins = 0;
                $noonins = 0;
                $nightins = 0;

                for($i=0;$i<$instructionLen;$i++){
                    if($instruction[$i] == 'Morning'){
                        $morningins = '1';
                    }
                    if($instruction[$i] == 'Noon'){
                        $noonins = '1';
                    }
                    if($instruction[$i] == 'Night'){
                        $nightins = '1';
                    }
                }

                if($rowMV['days'] == 0 || $rowMV['days'] == ''){
                    $days = "{$rowMV['days']}";
                }else {
                    $days = "({$rowMV['days']} Days)";                    
                }


                $route = substr($rowMV['route'], 0, 3);
                /*$tbl4 = $tbl4.'<tr>
                                    <td align="left" width="35%">'.$rowMV['title'].'</td>
                                    <td align="left" width="65%">'.$route.'</td>
                                </tr>
                                ';*/
                $treatmentTitle .= $rowMV['title'].' '.$morningins.'- '.$noonins.'- '.$nightins.' '.$days.', ';

            }

            $count++;
        }

        $treatmentTitle = rtrim($treatmentTitle, ', ');
        $tbl4 = $tbl4.'<tr>
                            <td align="left" width="100%">'.$treatmentTitle.'</td>
                        </tr>
                        ';

        $tbl4 = $tbl4.'</table>';

        $tbl3 ='<table border="0" width="100%" cellpadding="4">';

        $SQLMV = "
        SELECT mv.*
        FROM medicines_in_patient mv
        WHERE mv.in_patient_id = {$in_patient_id}
        AND mv.continue_medicine = 1
        ORDER BY mv.medicines_in_patient_id
        ";
        $resultMV = $db->sql_query($SQLMV);
        $count = 0;
        while ($rowMV = $db->sql_fetchrow($resultMV)) {
            if($rowMV['title'] != '' && $rowMV['instruction'] != ''){
                $instruction = explode(", ", $rowMV['instruction']);
                $instructionLen = count($instruction);

                $morning = 0;
                $noon = 0;
                $night = 0;

                if($rowMV['dosage'] == ''){
                    $rowMV['dosage'] = 1;
                }

                for($i=0;$i<$instructionLen;$i++){
                    if($instruction[$i] == 'Morning'){
                        $morning = $rowMV['dosage'];
                    }
                    if($instruction[$i] == 'Noon'){
                        $noon = $rowMV['dosage'];
                    }
                    if($instruction[$i] == 'Night'){
                        $night = $rowMV['dosage'];
                    }
                }

                if($rowMV['qty'] == 0 || $rowMV['qty'] == ''){
                    $qty = "{$rowMV['qty']}";
                }else {
                    $qty = "({$rowMV['qty']})";                    
                }

                $route = substr($rowMV['route'], 0, 3);
                if($rowMV['instruction'] == 'STAT' || $rowMV['instruction'] == 'SOS'){
                    $tbl3 = $tbl3.'<tr>
                                        <td align="left" width="35%">'.$rowMV['title'].'</td>
                                        <td align="left" width="10%">'.$qty.'</td>
                                        <td align="left" width="8%">'.$route.'</td>
                                        <td align="center" width="47%">'.$rowMV['instruction'].'</td>
                                    </tr>
                                    ';
                } else {
                    $tbl3 = $tbl3.'<tr>
                                        <td align="left" width="35%">'.$rowMV['title'].'</td>
                                        <td align="left" width="10%">'.$qty.'</td>
                                        <td align="left" width="8%">'.$route.'</td>
                                        <td align="center" width="13%">'.$morning.'</td>
                                        <td align="center" width="4%">-</td>
                                        <td align="center" width="13%">'.$noon.'</td>
                                        <td align="center" width="4%">-</td>
                                        <td align="center" width="13%">'.$night.'</td>
                                    </tr>
                                    ';
                }

            }

            $count++;
        }

        $tbl3 = $tbl3.'</table>';

        $SQLRDr = "
        SELECT (CASE 
                WHEN e.salutation = 'Dr' 
                THEN CONCAT_WS('', e.salutation, '.', e.first_name, e.middle_name, e.last_name) 
                ELSE CONCAT_WS('', e.first_name, e.middle_name, e.last_name)
                END) AS employee_name
              ,e.category
        FROM employee e
        WHERE e.employee_id = {$rowIp['employee_id']}
        ";
        $resultRDr   = $db->sql_query($SQLRDr);
        $rowRDr      = $db->sql_fetchrow($resultRDr);
        $ReferenceDr = $rowRDr['employee_name'];

        $weightKg = "";
        if($rowIp['weight'] != ""){
            $weightKg = $rowIp['weight'].' KGS';
        }

        /*  <tr>
                <td width="30%" style="border-left:1px solid #000000;font-weight: bold;">Drugs to Continue :</td>
                <td width="70%"  colspan="5" style="border-right:1px solid #000000;"></td>
            </tr>
            <tr>
                <td width="100%"  colspan="6" style="height:200px;vertical-align:top;border-left:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;">'.$tbl3.'</td>
            </tr>
        */
        
        $ip_code = "";
        $yearCreated = $fn->getCPDate($rowIp['creation_date'], "y");
        if($rowIp['code'] != ""){
            $ip_code = "IP-{$rowIp['code']}-{$yearCreated}";
        }

        $tbl1 = '
        <table border="0" cellpadding="3" width="100%" style="font-size:12px;">
            <tr>
                <td width="100%" align="center" colspan="6" style="border-bottom:1px solid #000000;"><br/>
                    <span style="font-size:12px;"><b>DISCHARGE SUMMARY</b></span>
                </td>
            </tr>
            <tr>
                <td width="8%" style="border-left:1px solid #000000;font-weight: bold;">UHID</td>
                <td width="15%">: '.$rowIp['uhid'].'</td>
                <td width="8%" style="font-weight: bold;">Name</td>
                <td width="47%"><b>:</b> '.$rowIp['patient_name'].'</td>
                <td width="7%" style="font-weight: bold;">D.O.A :</td>
                <td width="15%" style="border-right:1px solid #000000;">'.$date_admitted.'</td>
            </tr>
            <tr>
                <td width="8%" style="border-left:1px solid #000000;font-weight: bold;">IP. No.</td>
                <td width="15%">: '.$ip_code.'</td>
                <td width="10%" style="font-weight: bold;">Age / Sex :</td>
                <td width="18%">'.$age.' / '.$gender.'</td>
                <td width="9%" style="font-weight: bold;">Weight :</td>
                <td width="18%">'.$weightKg.'</td>
                <td width="7%" style="font-weight: bold;">D.O.S :</td>
                <td width="15%" style="border-right:1px solid #000000;">'.$date_surgery.'</td>
            </tr>
            <tr>
                <td width="8%" style="border-left:1px solid #000000;font-weight: bold;">Address</td>
                <td width="70%">: '.$rowIp['address_area'].'</td>
                <td width="7%" style="font-weight: bold;">D.O.D :</td>
                <td width="15%" style="border-right:1px solid #000000;">'.$date_discharge.'</td>
            </tr>
            
            <!--<tr>
                <td width="12%"  style="height:20px;border-top:1px solid #000000;border-left:1px solid #000000;border-bottom:1px solid #000000;font-weight: bold;">Ref :</td>
                <td width="88%" colspan="5" style="height:20px;border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;">'.$rowEmployeeRef['employee_name'].'</td>
            </tr>-->
            <!--<tr>
                <td width="12%" style="border-top:1px solid #000000;border-left:1px solid #000000;border-bottom:1px solid #000000;font-weight: bold;">Admitted for :</td>
                <td width="88%"  colspan="5" style="border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;">'.$rowIp['complain'].'</td>
            </tr>-->
            <tr>
                <td width="10%" style="border-top:1px solid #000000;border-left:1px solid #000000;border-bottom:1px solid #000000;font-weight: bold;">Diagnosis :</td>
                <td width="90%"  colspan="5" style="border-right:1px solid #000000;border-top:1px solid #000000;border-bottom:1px solid #000000;">'.$rowIp['diagnosis'].'</td>
            </tr>
            <tr>
                <td width="15%"  style="border-left:1px solid #000000;border-bottom:1px solid #000000;font-weight: bold;">Procedure Done :</td>
                <td width="85%"  colspan="5" style="border-right:1px solid #000000;border-bottom:1px solid #000000;">'.$rowIp['in_patient_procedure'].'</td>
            </tr>
            <tr>
                <td width="15%" style="border-top:1px solid #000000;border-left:1px solid #000000;border-bottom:1px solid #000000;font-weight: bold;">Contsultants : </td>
                <td width="85%" style="border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;">'.$rowEmployeeConsult['employee_name'].' <br> '.$ConsultantDr.'</td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="60%" style="font-weight: bold;">Treatment Summary :</td>
                <td width="30%"  colspan="5" ></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="80%"  colspan="4" style="border-bottom:1px solid #000000;">'.nl2br($rowIp['summary']).'</td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="60%" style="font-weight: bold;">System Examination :</td>
                <td width="30%"  colspan="5" ></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="80%"  colspan="4" style="border-bottom:1px solid #000000;">'.nl2br($rowIp['system_examination']).'</td>
                <td width="10%"></td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" cellpadding="3" width="100%" style="font-size:12px;">
            <tr>
                <td width="10%"></td>
                <td width="30%" style="font-weight: bold;">Treatment Given :</td>
                <td width="60%"  colspan="5" ></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="80%"  colspan="4" >'.nl2br($rowIp['treatment_given']).'<br/><br/>'.$tbl4.'</td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td width="100%"  colspan="6" style="vertical-align:top;border-bottom:1px solid #000000;"></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="60%" style="font-weight: bold;">Advice on discharge / Plan :</td>
                <td width="30%"  colspan="5" ></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="80%"  colspan="4" style="border-bottom:1px solid #000000;">'.nl2br($rowIp['advice_discharge']).'</td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="60%" style="font-weight: bold;">Instruction to the patient :</td>
                <td width="30%"  colspan="5" ></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="80%"  colspan="4" style="border-bottom:1px solid #000000;">'.nl2br($rowIp['instruction_to_patient']).'</td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="80%" align="left" style="border-left:1px solid #000000; border-right:1px solid #000000;"><b>Review with Dr : </b>'.$rowEmployeeRev['employee_name'].'</td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="20%" style="border-left:1px solid #000000;"></td>
                <td width="60%"  colspan="5" style="border-right:1px solid #000000;"></td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="40%" style="border-left:1px solid #000000;border-bottom:1px solid #000000;"><b>Review Date : </b>'.$date_review.'</td>
                <td width="40%" align="right" colspan="5" style="border-right:1px solid #000000;font-weight:bold;border-bottom:1px solid #000000;">Signature of Medical Officer</td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td width="10%"></td>
                <td width="90%"  colspan="6"><b>If any emergency please contact  +452 2362112 / 98421 60658 / 63696 91109</b></td>
            </tr>
        </table>
        ';
            
    
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $download_title = 'DischargeCard.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
     function getConditionForInvoiceItem($order_id, $record_type) {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $SQLInvItem = "
        SELECT ii.*
        FROM invoice_item ii
        LEFT JOIN invoice i ON (i.invoice_id = ii.invoice_id)
        WHERE i.order_id = {$order_id}
        AND i.status != 'Cancelled'
        AND ii.item_title = '{$record_type}'
        ";
        $resultInv = $db->sql_query($SQLInvItem);
        $rowInv    = $db->sql_fetchrow($resultInv);

        return $rowInv['invoice_item_id'];
    }

    /**
     *
     */
    function getNewDoctorAdd() {
        $cpCfg   = Zend_Registry::get('cpCfg');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $visits_patients = $fn->getReqParam('visits_patients');
        $category        = $fn->getReqParam('category');

        $formAction = "index.php?module=hms_inPatient&_spAction=newDoctorAddSubmit&showHTML=0";
        $categoryArr = array(
             'Doctor'
            ,'Duty Doctor'
            ,'Ent Speciality'
            ,'Anesthesiology'
            ,'General Surgeon'
            ,'General Physician'
            ,'Pediatrician'
            ,'Orthopaedician'
            ,'Obstetrics & Gynaecology'
            ,'Radiologist'
            ,'Neurosurgery'
            ,'Neurologist'
            ,'Ophthalmologist'
            ,'Cardiology'
            ,'Gastroenterologist'
            ,'Dental Surgeon'
            ,'Urologist'
        );

        $categoryDropdown = "";
        if($visits_patients == 1) {
            $categoryDropdown = $formObj->getDDRowByArr('Choose Category', 'category', $categoryArr, '');
        }

        $text = "
        <form name='newDoctorAddForm' id='newDoctorAddForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Name', 'first_name', '')}
            {$categoryDropdown}
            <input name='category_hidden' type='hidden' value='{$category}'>
            <input name='visits_patients' type='hidden' value='{$visits_patients}'>
        </form>
        ";

        return $text;
    }

    /**
     */
    function getInvoicePortalDisplay($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

        $formAction = '';

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Invoice(s)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    {$this->getInvoicePortalDisplayDetail($order_id)}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getInvoicePortalDisplayDetail($order_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $sqlAppend = "";

        $status = $fn->getReqParam('status');

        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQL = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            {$sqlAppend}
            ) AS receipt_codes_history
        FROM invoice i
        WHERE i.order_id = {$order_id}
        ORDER BY i.invoice_id
        ";

        $result   = $db->sql_query($SQL);
        $discount = '';
        $tdCheckBox = '';
        $checkBoxStatus = '';
        $count = 1;
        $invoice_code = '';
        $add_registration_fee = '';
        $invoice_hist_amount  = '';
        $total = '';
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $gstvalue = '';
            $gsttaxvalue = '';
            $pfvalue = '';
            $frieghtValue = '';
            $total = '';
            $selectedValuePaid   = '';
            $selectedValueDue    = '';
            $selectedValueCancel = '';
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);


            $urlPrint  = "index.php?_topRm=main&module=hms_inPatient&_spAction=printInvoiceRecord&invoice_code={$rowInvoice['invoice_id']}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowInvoice['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowInvoice['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=tradingin_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            if($rowInvoice['status'] != 'Cancelled'){
                $total += $rowInvoice['invoice_amount'];
            }

            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $cancel_image = $cpCfg['cp.localPath']."images/icon-cancel.ico";
                $cancelInvoiceLink = "<a href='#' class='cancelInvoice' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}'><img src='{$cancel_image}' class='icon'></a>";
            }

            $highlight = '';
            if ($rowInvoice['status'] == 'Cancelled') {
                $highlight = 'highlightCell';
                $cancelReceiptLink = "Cancelled";
                $cancelInvoiceLink = $rowInvoice['cancelled_by'].' ON '.$rowInvoice['cancelled_date'];
            }

            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');
            $modification_date = $fn->getCPDate($rowInvoice['modification_date'], 'd-m-Y H:i:s');
            if($modification_date == ''){
                $modification_date = $fn->getCPDate($rowInvoice['creation_date'], 'd-m-Y H:i:s');
            }
            if($rowInvoice['modified_by'] == ''){
                $rowInvoice['modified_by'] = $rowInvoice['created_by'];
            }

            if($total > 0){
                $total = $total - $rowInvoice['discount'];
            }

            $medicalFees = 0;
            if($rowORder['order_type'] == "IP" && $total > 0){
                $SQLInvoiceItem = "
                SELECT SUM(unit_price) AS medical_fees
                FROM invoice_item
                WHERE record_type = 'Medical Test'
                AND invoice_id = {$rowInvoice['invoice_id']}
                GROUP BY record_type
                ";
                $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);
                $rowInvoiceItem    = $db->sql_fetchrow($resultInvoiceItem);

                $medicalFees = $rowInvoiceItem['medical_fees'];
            }

            if($rowORder['order_type'] == 'POS') {
                $cancelInvoiceLink = "";
            }

            $totalvalueRounded = number_format(round($total - $medicalFees),2);

            $print_image = $cpCfg['cp.localPath']."images/icon-print.ico";

            $rows .= "
            <tr>
                <td>{$rowInvoice['invoice_code']}</td>
                <td>{$invoice_date}</td>
                <td class='{$highlight}'>{$rowInvoice['status']}</td>
                <td align='right'>{$totalvalueRounded}</td>
                <td>{$modification_date}</td>
                <td>{$rowInvoice['modified_by']}</td>
                <td><a href='{$urlPrint}' target='_blank'><img src='{$print_image}' class='icon'></a></td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
                ";
            }


        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Invoice Code</th>
        <th>Invoice Date</th>
        <th>Status</th>
        <th class='txtRight'>Invoice Amount</th>
        <th>Modified Date</th>
        <th>Modified By</th>
        <th>Print Invoice</th>
        <th>Cancel Invoice</th>
        </tr>
        ";

        $invoice_count = $fn->getRecordCount('invoice', "order_id = '{$order_id}' AND status != 'Cancelled'");

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
            {$rowsPvt}
        </table>
        <input type='hidden' id='fld_invoice_count' value='{$invoice_count}'>
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptPortalDisplay($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

        $rows = "";
        $links= "";
        $sqlAppend = "";
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $order_id);

        $SQL = "
        SELECT r.receipt_id
              ,r.receipt_status
              ,r.receipt_code
              ,r.date
              ,r.mode_of_payment
              ,r.amount
              ,irh.invoice_id
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$order_id}
              {$sqlAppend}
        GROUP BY r.receipt_id
        UNION
        SELECT r.receipt_id
              ,r.receipt_status
              ,r.receipt_code
              ,r.date
              ,r.mode_of_payment
              ,irh.amount
              ,irh.invoice_id
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON ( r.receipt_id = irh.receipt_id )
        LEFT JOIN (invoice i) ON ( i.invoice_id = irh.related_invoice_id )
        WHERE r.receipt_status != 'Cancelled'
        AND irh.related_invoice_id != irh.invoice_id
        AND r.order_id = {$order_id}
        AND i.invoice_id IN(
            SELECT invoice_id
            FROM invoice
            WHERE order_id = {$order_id}
            AND i.status !='Cancelled'
        )
        {$sqlAppend}
        GROUP BY r.receipt_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            $urlPrint = "index.php?_topRm=main&module=hms_inPatient&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&receipt_id={$rowReceipt['receipt_id']}&order_id={$order_id}&showHTML=0";
            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $highlight = '';
            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancel_image = $cpCfg['cp.localPath']."images/icon-cancel.ico";
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' order_id =
                '{$order_id}' receipt_id='{$rowReceipt['receipt_id']}'><img src='{$cancel_image}' class='icon'></a>";
            }
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $highlight = 'highlightCell';
                $cancelReceiptLink = "Cancelled";
            }

            if($rowORder['order_type'] == "IP" && $rowReceipt['amount'] > 0){
                $SQLInvoiceItem = "
                SELECT SUM(unit_price) AS medical_fees
                FROM invoice_item
                WHERE record_type = 'Medical Test'
                AND invoice_id = {$rowReceipt['invoice_id']}
                GROUP BY record_type
                ";
                $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);
                $rowInvoiceItem    = $db->sql_fetchrow($resultInvoiceItem);

                $rowReceipt['amount'] = $rowReceipt['amount'] - $rowInvoiceItem['medical_fees'];
            }

            $receiptAmount = number_format(round($rowReceipt['amount']), 2);

            if($rowORder['order_type'] == 'POS') {
                $cancelReceiptLink = "";
            }

            $print_image = $cpCfg['cp.localPath']."images/icon-print.ico";
            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receiptAmount}</td>
                <td>{$rowReceipt['receipt_status']}</td>
                <td><a href='{$urlPrint}' target='_blank'><img src='{$print_image}' class='icon'></a></td>
                <td class='{$highlight}'>{$cancelReceiptLink}</td>
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Receipt Code</th>
            <th>Receipt Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Receipt Amount</th>
            <th>Status</th>
            <th>Print Receipt</th>
            <th>Cancel Receipt</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=finance&module=pms_order&_spAction=generateRefundForm&showHTML=0&order_id={$order_id}&receipt_id={$receiptRec['receipt_id']}";

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left InvoiceToggleHeading'>Receipt(s)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                        <table class='thinlist'>
                            {$header}
                            {$rows}
                        </table>
                        <input type='hidden' name='order_id' value='{$order_id}' />
                        <input type='hidden' name='receipt_id' value='{$receiptRec['receipt_id']}' />
                    </form>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintInvoiceRecord() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootA5.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage('P', 'A5');

        $quote_id = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_code = $fn->getReqParam('invoice_code');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND i.site_id = {$cpSiteIdSession}";
        }
        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_po_code
                ,o.shipping_address1
                ,o.shipping_address_area
                ,o.shipping_address_city
                ,o.shipping_address_country_code
                ,o.shipping_address_po_code
                ,o.shipping_phone
                ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                ,o.order_id
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.invoice_code
                ,i.invoice_terms
                ,o.in_patient_id
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.status
                ,co.first_name
                ,co.salutation
                ,ROUND((ini.qty * ini.unit_price), 2) AS amount
              ,(SELECT ROUND(SUM(init.qty * init.unit_price), 2) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        WHERE i.invoice_id = '{$invoice_code}'
        {$appendSql}
        ORDER BY ini.invoice_item_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //
        if ($company['status'] == 'Cancelled') {
            /* Watermark code start for Cancelled */
            $ImageW = 130; //WaterMark Size
            $ImageH = 150;

            $myPageWidth = $pdf->getPageWidth();
            $myPageHeight = $pdf->getPageHeight();
            $myX = ( $myPageWidth / 2 ) - 60;  //210 WaterMark Positioning
            $myY = ( $myPageHeight / 2 ) - 95; //297

            $pdf->SetAlpha(0.40); //opacity of bg image

            $bg_image = $cpCfg['cp.localPath']."images/cancelled.jpg";
            //$bg_image = $pdf->Image('images/logo_bg.jpg');
            //Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
            $pdf->Image($bg_image, $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);
            $pdf->SetAlpha(1);
            /* Watermark code end for Cancelled */
        }

        $pdf->SetFont('','B', 8);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:13px;">
            <tr>
                <td align="center" style="font-weight:bold;"><u>BILL</u></td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        $invoice_code = substr($company['invoice_code'], 2);

        $PatientOtherAddress = '';
        if($company['shipping_address_city'] != '') {
            $PatientOtherAddress .= $company['shipping_address_city'];
        }

        if($company['shipping_address_country_code'] != '') {
            $PatientOtherAddress .= $company['shipping_address_country_code'].' - ';
        }

        if($company['shipping_address_po_code'] != '') {
            $PatientOtherAddress .= $company['shipping_address_po_code'];
        }

        $PatientMainAddress = '';
        if($company['shipping_address1'] != '') {
            if($PatientOtherAddress != '') {
                $PatientMainAddress .= $company['shipping_address1'].'<br/>';
            } else {
                $PatientMainAddress .= $company['shipping_address1'].'.';
            }
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:10px;">
                    <tr>
                        <td width="62%" style="line-height:20px;"><br/>
                            <span><b>NAME :</b> '.strtoupper($company['patient_name']).'</span><br/><br/>
                            <span><b>ADDRESS :</b><br/></span>
                            <span>'.strtoupper($PatientMainAddress).'</span>
                            <span>'.strtoupper($PatientOtherAddress).'</span>
                        </td>
                        <td width="38%" style="line-height:20px;" align="right"><br/>
                            <span>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$invoice_date.'</span><br/>
                            <span>INVOICE CODE : '.$company['invoice_code'].'</span>
                        </td>
                    </tr>
                </table>
                ';


        $tbl3 ='<table border="0" width="100%" cellpadding="4" style="font-size:10px;">
                    <thead>
                        <tr>
                            <th width="10%" style="border-top:1px dashed black;border-bottom:1px dashed black;">S.NO</th>
                            <th width="77%" style="border-top:1px dashed black;border-bottom:1px dashed black;">DESCRIPTION</th>
                            <th width="13%" style="text-align:right;border-top:1px dashed black;border-bottom:1px dashed black;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $SQLIp = "
        SELECT ip.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.address_area
        FROM in_patient ip
        LEFT JOIN (patient_information p) ON (p.patient_information_id = ip.patient_information_id)
        WHERE ip.in_patient_id = '{$company['in_patient_id']}'
        ";
        $resultIp = $db->sql_query($SQLIp);
        $result2  = $db->sql_query($SQLIp);
        $rowIp    = $db->sql_fetchrow($result2);

        $count = 1;
        $discount = '';
        $Total_Amount = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,unit_price AS PRICE
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$company['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY FIELD(record_type, 'Admission Details', 'Medical Test', 'Surgery Details', 'Diagnosis', 'Treatment', 'Inventory', 'Doctor/Nurse')
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        if($numRowsOrderItem > 0){
            $count = 1;
            $Sub_Total = '';
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$company['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                $SQL = "
                SELECT  record_type
               ,unit_price AS PRICE
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$company['order_id']}
        AND record_type != ''
        AND item_title ='Advance Payment'
        GROUP BY record_type
        ORDER BY FIELD(record_type, 'Admission Details', 'Medical Test', 'Surgery Details', 'Diagnosis', 'Treatment', 'Inventory', 'Doctor/Nurse')
        ";
                
                $result = $db->sql_query($SQL);
                $row1 = $db->sql_fetchrow($result);

                $SQLOrderItemListCheck = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id  = {$company['order_id']}
                AND unit_price > 0
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultListCheck  = $db->sql_query($SQLOrderItemListCheck);
                $numRowsListCheck = $db->sql_numrows($resultListCheck);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                        $rowOrderItem['record_type'] = 'Consultation Fees';
                }

                if($rowOrderItem['record_type'] == 'Medical Test'){
                        $rowOrderItem['record_type'] = 'Lab Test';
                }

                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount']      = $rowOrderItem['QTY_AMOUNT'];
                }

                if($rowOrderItem['record_type'] == 'Consultation Fees') {
                    $tbl3 = $tbl3.'<tr>
                                        <td width="10%">'.$count.'</td>
                                        <td width="70%"><u>'.$rowOrderItem['record_type'].':</u>
                                        <ol>
                                   ';

                    if($numRowsList > 0){
                        while($rowList    = $db->sql_fetchrow($resultList)){
                            $surgeonName = "";
                            if($rowList['item_title'] == "Surgeon Fees") {
                                if($rowIp['surgeon'] != "") {
                                    $surgeonName = '<br/>'.$rowIp['surgeon'];
                                }
                            }

                            $anesthetistsName = "";
                            if($rowList['item_title'] == "Anaesthetic Fees") {
                                if($rowIp['anesthetists'] != "") {
                                    $anesthetistsName = '<br/>'.$rowIp['anesthetists'];
                                }
                            }

                            $theaterAssisntName = "";
                            if($rowList['item_title'] == "Theatre Assistant Fees") {
                                if($rowIp['theater_assistant'] != "") {
                                    $theaterAssisntName = '<br/>'.$rowIp['theater_assistant'];
                                }
                            }

                            $otStaffName = "";
                            if($rowList['item_title'] == "OT Charges") {
                                if($rowIp['ot_staff'] != "") {
                                    $otStaffName = '<br/>'.$rowIp['ot_staff'];
                                }
                            }

                            $tbl3 = $tbl3.'<li>'.$rowList['item_title'].'</li>';
                        }
                    }

                    // Hiding price for Diagnosis
                    if ($rowOrderItem['record_type'] == 'Diagnosis') {
                        $oiAmount = '';
                    } else {
                        $oiAmount = $rowOrderItem['Amount'];
                    }

                    $tbl3 = $tbl3.'</ol></td>
                                        <td width="20%" style="text-align:right;">'.$oiAmount.'</td>
                                    </tr>';
                } else if($rowOrderItem['record_type'] == 'Lab Test') {
                    // Hiding price for Diagnosis
                    if ($rowOrderItem['record_type'] == 'Diagnosis') {
                        $oiAmount = '';
                    } else {
                        $oiAmount = $rowOrderItem['Amount'];
                    }

                    $tbl3 = $tbl3.'<tr>
                                        <td width="7%">'.$count.'</td>
                                        <td width="80%">'.$rowOrderItem['record_type'].'</td>
                                        <td width="13%">'.$oiAmount.'</td>
                                    </tr>
                                   ';
                } else {
                    if($rowOrderItem['record_type'] == 'Surgery Details' && $numRowsListCheck == 0) {
                    } else {
                        $tbl3 = $tbl3.'<tr>
                                            <td width="7%">'.$count.'</td>
                                            <td width="80%"><u>'.$rowOrderItem['record_type'].':</u></td>
                                            <td width="13%"></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td colspan="2">
                                                <table width="100%" border="0" cellpadding="2">
                                       ';

                        if($numRowsList > 0){
                            $countSubItem = 1;
                            while($rowList    = $db->sql_fetchrow($resultList)){
                                $surgeonName = "";
                                if($rowList['item_title'] == "Surgeon Fees") {
                                    if($rowIp['surgeon'] != "") {
                                        $surgeonName = '<br/>'.$rowIp['surgeon'];
                                    }
                                }

                                $anesthetistsName = "";
                                if($rowList['item_title'] == "Anaesthetic Fees") {
                                    if($rowIp['anesthetists'] != "") {
                                        $anesthetistsName = '<br/>'.$rowIp['anesthetists'];
                                    }
                                }

                                $theaterAssisntName = "";
                                if($rowList['item_title'] == "Theatre Assistant Fees") {
                                    if($rowIp['theater_assistant'] != "") {
                                        $theaterAssisntName = '<br/>'.$rowIp['theater_assistant'];
                                    }
                                }

                                $otStaffName = "";
                                if($rowList['item_title'] == "OT Charges") {
                                    if($rowIp['ot_staff'] != "") {
                                        $otStaffName = '<br/>'.$rowIp['ot_staff'];
                                    }
                                }

                                if($rowOrderItem['record_type'] == 'Surgery Details' && $rowList['unit_price'] == 0) {
                                } else {
                                    $tbl3 = $tbl3.'
                                            <tr>
                                                <td width="6%">'.$countSubItem.'.</td>
                                                <td width="80%">'.$rowList['item_title'].'</td>
                                                <td width="14%" style="text-align:right;">'.$rowList['unit_price'].'</td>
                                            </tr>
                                            ';

                                    $countSubItem++;
                                }
                            }

                            $tbl3 = $tbl3.'</table></td></tr>';
                        }

                        // Hiding price for Diagnosis
                        if ($rowOrderItem['record_type'] == 'Diagnosis') {
                            $oiAmount = '';
                        } else {
                            $oiAmount = $rowOrderItem['Amount'];
                        }
                    }
                }

                $Sub_Total += $rowOrderItem['Amount'];

                if($rowOrderItem['record_type'] == 'Surgery Details' && $numRowsListCheck == 0) {
                } else {
                    $count++;
                }
            }
            $discount1 = 0;

            $Total_Amount = $Sub_Total - $company['discount'] - $row1['PRICE'];
            $Total_Amount_in_words = $fn->getConvertNumber($Total_Amount .'.00');
            $Sub_Total    = number_format($Sub_Total, 2);
            $discount     = number_format($company['discount'], 2);
            $discount1     = number_format($row1['PRICE'], 2);
            $Total_Amount = number_format($Total_Amount, 2);

            $tbl3 = $tbl3.'<tr>
                                <td colspan="2" style="text-align:right;border-top:1px dashed black;border-bottom:1px dashed black;">SUB TOTAL</td>
                                <td style="text-align:right;border-top:1px dashed black;border-bottom:1px dashed black;">'.$Sub_Total.'</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align:right;border-bottom:1px dashed black;">ADVANCE PAYMENT</td>
                                <td style="text-align:right;border-bottom:1px dashed black;">- '.$discount1.'</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align:right;border-bottom:1px dashed black;">TOTAL AMOUNT</td>
                                <td style="text-align:right;border-bottom:1px dashed black;">'.$Total_Amount.'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align:center;">'.strtoupper($Total_Amount_in_words).'</td>
                            </tr>
            ';
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintReceipt() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootA5.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage('P', 'A5');

        $receipt_code = $fn->getReqParam('receipt_code');
        $receipt_id = $fn->getReqParam('receipt_id');
        $order_id = $fn->getReqParam('order_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlCheck = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlCheck = " AND r.site_id = {$cpSiteIdSession}";
        }
        $SQLCheck = "
        SELECT r.order_id
        FROM receipt r
        WHERE r.receipt_id = '{$receipt_id}'
        {$appendSqlCheck}
        ";
        $resultCheck = $db->sql_query($SQLCheck);
        $rowCheck    = $db->sql_fetchrow($resultCheck);

        if($rowCheck['order_id'] == $order_id){
            $SQL = "
            SELECT ini.*
                  ,c.company_name
                  ,o.cust_address1
                  ,o.cust_address2
                  ,o.cust_address_po_code
                  ,o.shipping_address1
                  ,o.shipping_address_area
                  ,o.shipping_address_city
                  ,o.shipping_address_country_code
                  ,o.shipping_address_po_code
                  ,o.shipping_phone
                  ,o.patient_visit_id
                  ,o.patient_information_id
                  ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                  ,o.order_id
                  ,o.in_patient_id
                  ,i.discount
                  ,i.creation_date
                  ,i.invoice_id AS invoice_id_main
                  ,i.invoice_code
                  ,i.invoice_amount
                  ,r.receipt_id
                  ,r.amount AS receipt_amount
                  ,r.receipt_code
                  ,r.mode_of_payment
                  ,r.remarks
                  ,r.creation_date AS receipt_date
                  ,r.receipt_status
            FROM receipt r
            LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN invoice i ON (i.invoice_id = irh.invoice_id)
            LEFT JOIN invoice_item ini ON (i.invoice_id = ini.invoice_id)
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            LEFT JOIN company c ON (c.company_id = o.company_id)
            WHERE r.receipt_id = '{$receipt_id}'
            AND r.order_id = {$order_id}
            {$appendSqlCheck}
            ";
        }else{
            $SQL = "
            SELECT ini.*
                  ,c.company_name
                  ,o.cust_address1
                  ,o.cust_address2
                  ,o.cust_address_po_code
                  ,o.shipping_address1
                  ,o.shipping_address_area
                  ,o.shipping_address_city
                  ,o.shipping_address_country_code
                  ,o.shipping_address_po_code
                  ,o.shipping_phone
                  ,o.patient_visit_id
                  ,o.patient_information_id
                  ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                  ,o.order_id
                  ,o.in_patient_id
                  ,i.discount
                  ,i.creation_date
                  ,i.invoice_id AS invoice_id_main
                  ,i.invoice_code
                  ,i.invoice_amount
                  ,r.receipt_id
                  ,r.amount AS receipt_amount
                  ,r.receipt_code
                  ,r.mode_of_payment
                  ,r.remarks
                  ,r.creation_date AS receipt_date
                  ,irh.amount AS receipt_amount
                  ,r.receipt_status
            FROM receipt r
            LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN invoice i ON (i.invoice_id = irh.related_invoice_id)
            LEFT JOIN invoice_item ini ON (i.invoice_id = ini.invoice_id)
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            LEFT JOIN company c ON (c.company_id = o.company_id)
            WHERE r.receipt_id = '{$receipt_id}'
            AND r.receipt_status != 'Cancelled'
            AND irh.invoice_id != irh.related_invoice_id
            {$appendSqlCheck}
            ";
        }

        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //
        if ($company['receipt_status'] == 'Cancelled') {
            /* Watermark code start for Cancelled */
            $ImageW = 130; //WaterMark Size
            $ImageH = 150;

            $myPageWidth = $pdf->getPageWidth();
            $myPageHeight = $pdf->getPageHeight();
            $myX = ( $myPageWidth / 2 ) - 60;  //210 WaterMark Positioning
            $myY = ( $myPageHeight / 2 ) - 95; //297

            $pdf->SetAlpha(0.40); //opacity of bg image

            $bg_image = $cpCfg['cp.localPath']."images/cancelled.jpg";
            //$bg_image = $pdf->Image('images/logo_bg.jpg');
            //Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
            $pdf->Image($bg_image, $myX, $myY, $ImageW, $ImageH, '', '', '', true, 150);
            $pdf->SetAlpha(1);
            /* Watermark code end for Cancelled */
        }

        $pdf->SetFont('','B', 9);

        $today = date("d-m-Y");
        $receipt_date = $fn->getCPDate($company['receipt_date'], 'd-m-Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:13px;">
            <tr>
                <td align="center" style="font-weight:bold;"><u>RECEIPT</u></td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        $PatientOtherAddress = '';
        if($company['shipping_address_city'] != '') {
            $PatientOtherAddress .= $company['shipping_address_city'];
        }

        if($company['shipping_address_country_code'] != '') {
            $PatientOtherAddress .= $company['shipping_address_country_code'].' - ';
        }

        if($company['shipping_address_po_code'] != '') {
            $PatientOtherAddress .= $company['shipping_address_po_code'];
        }

        $PatientMainAddress = '';
        if($company['shipping_address1'] != '') {
            if($PatientOtherAddress != '') {
                $PatientMainAddress .= $company['shipping_address1'].'<br/>';
            } else {
                $PatientMainAddress .= $company['shipping_address1'].'.';
            }
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:10px;">
                    <tr>
                        <td width="70%" style="line-height:20px;"><br/>
                            <span><b>NAME :</b> '.strtoupper($company['patient_name']).'</span><br/><br/>
                            <span><b>ADDRESS :</b><br/></span>
                            <span>'.strtoupper($PatientMainAddress).'</span>
                            <span>'.strtoupper($PatientOtherAddress).'</span>
                        </td>
                        <td width="30%" style="line-height:20px;"><br/>
                            <span>DATE : '.$receipt_date.'</span><br/>
                            <span>Code : '.$company['receipt_code'].'</span>
                        </td>
                    </tr>
                </table>
                ';


        $tbl3 ='<table border="0" width="100%" cellpadding="4" style="font-size:10px;">
                    <thead>
                        <tr>
                            <th style="border-top:1px dashed black;border-bottom:1px dashed black;" width="10%">S.NO</th>
                            <th style="border-top:1px dashed black;border-bottom:1px dashed black;" width="60%">DESCRIPTION</th>
                            <th style="border-top:1px dashed black;border-bottom:1px dashed black;text-align:right;" width="30%">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $SQLIp = "
        SELECT ip.*
              ,p.name AS patient_name
              ,p.age_year
              ,p.age_month
              ,p.age_day
              ,p.gender
              ,p.address_area
        FROM in_patient ip
        LEFT JOIN (patient_information p) ON (p.patient_information_id = ip.patient_information_id)
        WHERE ip.in_patient_id = '{$company['in_patient_id']}'
        ";
        $resultIp = $db->sql_query($SQLIp);
        $result2  = $db->sql_query($SQLIp);
        $rowIp    = $db->sql_fetchrow($result2);

        $count = 1;
        $discount = '';
        $Total_Amount = '';
        $SQLOrderItem = "
        SELECT  record_type
               ,SUM(unit_price) AS Amount
               ,SUM(unit_price*qty) AS QTY_AMOUNT
        FROM order_item
        WHERE order_id = {$company['order_id']}
        AND record_type != ''
        GROUP BY record_type
        ORDER BY record_type ASC
        ";
        $resultOrderItem = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem = $db->sql_numrows($resultOrderItem);

        if($numRowsOrderItem > 0){
            $count = 1;
            $Sub_Total = '';
            while($rowOrderItem  = $db->sql_fetchrow($resultOrderItem)){
                $SQLOrderItemList = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id = {$company['order_id']}
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultList = $db->sql_query($SQLOrderItemList);
                $numRowsList = $db->sql_numrows($resultList);

                $SQLOrderItemListCheck = "
                SELECT  item_title
                        ,unit_price
                        ,order_item_id
                FROM order_item
                WHERE order_id  = {$company['order_id']}
                AND unit_price > 0
                AND record_type = '{$rowOrderItem['record_type']}'
                ";
                $resultListCheck  = $db->sql_query($SQLOrderItemListCheck);
                $numRowsListCheck = $db->sql_numrows($resultListCheck);

                if($rowOrderItem['record_type'] == 'Doctor/Nurse'){
                        $rowOrderItem['record_type'] = 'Consultation Fees';
                }

                if($rowOrderItem['record_type'] == 'Medical Test'){
                        $rowOrderItem['record_type'] = 'Lab Test';
                }

                if($rowOrderItem['record_type'] == 'Inventory'){
                    $rowOrderItem['record_type'] = 'Medicines and Other Charges';
                    $rowOrderItem['Amount']      = $rowOrderItem['QTY_AMOUNT'];
                }

                if($rowOrderItem['record_type'] == 'Consultation Fees') {
                    $tbl3 = $tbl3.'<tr>
                                        <td width="10%">'.$count.'</td>
                                        <td width="70%"><u>'.$rowOrderItem['record_type'].':</u>
                                        <ol>
                                   ';

                    if($numRowsList > 0){
                        while($rowList    = $db->sql_fetchrow($resultList)){
                            $surgeonName = "";
                            if($rowList['item_title'] == "Surgeon Fees") {
                                if($rowIp['surgeon'] != "") {
                                    $surgeonName = '<br/>'.$rowIp['surgeon'];
                                }
                            }

                            $anesthetistsName = "";
                            if($rowList['item_title'] == "Anaesthetic Fees") {
                                if($rowIp['anesthetists'] != "") {
                                    $anesthetistsName = '<br/>'.$rowIp['anesthetists'];
                                }
                            }

                            $theaterAssisntName = "";
                            if($rowList['item_title'] == "Theatre Assistant Fees") {
                                if($rowIp['theater_assistant'] != "") {
                                    $theaterAssisntName = '<br/>'.$rowIp['theater_assistant'];
                                }
                            }

                            $otStaffName = "";
                            if($rowList['item_title'] == "OT Charges") {
                                if($rowIp['ot_staff'] != "") {
                                    $otStaffName = '<br/>'.$rowIp['ot_staff'];
                                }
                            }

                            $tbl3 = $tbl3.'<li>'.$rowList['item_title'].'</li>';
                        }
                    }

                    // Hiding price for Diagnosis
                    if ($rowOrderItem['record_type'] == 'Diagnosis') {
                        $oiAmount = '';
                    } else {
                        $oiAmount = $rowOrderItem['Amount'];
                    }

                    $tbl3 = $tbl3.'</ol></td>
                                        <td width="20%" style="text-align:right;">'.$oiAmount.'</td>
                                    </tr>';
                } else if($rowOrderItem['record_type'] == 'Lab Test') {
                    // Hiding price for Diagnosis
                    if ($rowOrderItem['record_type'] == 'Diagnosis') {
                        $oiAmount = '';
                    } else {
                        $oiAmount = $rowOrderItem['Amount'];
                    }

                    $tbl3 = $tbl3.'<tr>
                                        <td width="10%">'.$count.'</td>
                                        <td width="70%">'.$rowOrderItem['record_type'].'</td>
                                        <td width="20%">'.$oiAmount.'</td>
                                    </tr>
                                   ';
                } else {
                    if($rowOrderItem['record_type'] == 'Surgery Details' && $numRowsListCheck == 0) {
                    } else {
                        $tbl3 = $tbl3.'<tr>
                                            <td width="10%">'.$count.'</td>
                                            <td width="70%"><u>'.$rowOrderItem['record_type'].':</u></td>
                                            <td width="20%"></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td colspan="2">
                                                <table width="100%" border="0" cellpadding="2">
                                       ';

                        if($numRowsList > 0){
                            $countSubItem = 1;
                            while($rowList    = $db->sql_fetchrow($resultList)){
                                $surgeonName = "";
                                if($rowList['item_title'] == "Surgeon Fees") {
                                    if($rowIp['surgeon'] != "") {
                                        $surgeonName = '<br/>'.$rowIp['surgeon'];
                                    }
                                }

                                $anesthetistsName = "";
                                if($rowList['item_title'] == "Anaesthetic Fees") {
                                    if($rowIp['anesthetists'] != "") {
                                        $anesthetistsName = '<br/>'.$rowIp['anesthetists'];
                                    }
                                }

                                $theaterAssisntName = "";
                                if($rowList['item_title'] == "Theatre Assistant Fees") {
                                    if($rowIp['theater_assistant'] != "") {
                                        $theaterAssisntName = '<br/>'.$rowIp['theater_assistant'];
                                    }
                                }

                                $otStaffName = "";
                                if($rowList['item_title'] == "OT Charges") {
                                    if($rowIp['ot_staff'] != "") {
                                        $otStaffName = '<br/>'.$rowIp['ot_staff'];
                                    }
                                }

                                if($rowOrderItem['record_type'] == 'Surgery Details' && $rowList['unit_price'] == 0) {
                                } else {
                                    $tbl3 = $tbl3.'
                                            <tr>
                                                <td width="4%">'.$countSubItem.'.</td>
                                                <td width="74%">'.$rowList['item_title'].'</td>
                                                <td width="22%" style="text-align:right;">'.$rowList['unit_price'].'</td>
                                            </tr>
                                            ';

                                    $countSubItem++;
                                }
                            }

                            $tbl3 = $tbl3.'</table></td></tr>';
                        }

                        // Hiding price for Diagnosis
                        if ($rowOrderItem['record_type'] == 'Diagnosis') {
                            $oiAmount = '';
                        } else {
                            $oiAmount = $rowOrderItem['Amount'];
                        }
                    }
                }

                $Sub_Total += $rowOrderItem['Amount'];

                if($rowOrderItem['record_type'] == 'Surgery Details' && $numRowsListCheck == 0) {
                } else {
                    $count++;
                }
            }

            $SQLDues = "
            SELECT i.invoice_code
                  ,i.invoice_amount
                  ,i.discount
                  ,irh.amount
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON ( r.receipt_id = irh.receipt_id )
            LEFT JOIN (invoice i) ON ( i.invoice_id = irh.related_invoice_id )
            WHERE r.receipt_status != 'Cancelled'
            AND irh.related_invoice_id != irh.invoice_id
            AND r.receipt_id = {$company['receipt_id']}
            AND r.order_id = {$company['order_id']}
            ";
            $resultDues  = $db->sql_query($SQLDues);
            $numRowsDues = $db->sql_numrows($resultDues);
            $invoice_amount = 0;
            $invoice_due_amount = 0;
            if($numRowsDues > 0){
                $checkboxInvoice = '';
                $Due_items_Details = '';
                $tbl3 = $tbl3.'<tr>
                                 <td colspan="2">Other Invoice(s) Due:
                                 <ol>
                ';
                while ($rowDues = $db->sql_fetchrow($resultDues)) {
                    $invoice_amount += $rowDues['amount'];
                    $invoice_due_amount += $rowDues['invoice_amount'];
                    $tbl3 = $tbl3.'
                        <li>'.$rowDues['invoice_code'].'</li>
                    ';
                }

                $invoice_amount = number_format($invoice_amount, 2);
                $tbl3 = $tbl3.'</ol>
                            </td>
                               <td style="text-align:right;">
                                  '.$invoice_amount.'
                               </td>
                            </tr>';
            }

            $Total_Amount  = $Sub_Total - $company['discount'] + $invoice_amount;
            $Total_Amount_in_words = $fn->getConvertNumber($company['receipt_amount'] .'.00');
            $Total_Amount_balance  = $Sub_Total - $company['discount'] + $invoice_due_amount;
            $balanceAmount = $Total_Amount_balance - $company['receipt_amount'];
            $Sub_Total     = number_format($Sub_Total + $invoice_amount, 2);
            $discount      = number_format($company['discount'], 2);
            $Total_Amount  = number_format($Total_Amount, 2);
            $ReceiptAmount = number_format($company['receipt_amount'], 2);
            $tbl3 = $tbl3.'<tr>
                                <td colspan="2" style="border-top:1px dashed black;border-bottom:1px dashed black;text-align:right;">TOTAL AMOUNT</td>
                                <td style="border-top:1px dashed black;border-bottom:1px dashed black;text-align:right;">'.$Total_Amount.'</td>
                            </tr>
            ';

            $appendSql = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = " AND i.site_id = {$cpSiteIdSession}";
            }

            $SQLPrevSum = "
            SELECT i.*
                ,(
                SELECT SUM(invHist.amount) AS prev_sum
                FROM invoice_receipt_history invHist
                LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
                WHERE invHist.related_invoice_id =  i.invoice_id
                AND r.receipt_status != 'Cancelled'
                AND r.receipt_id < '{$company['receipt_id']}'
                ) as prev_inv_amount_group
            FROM invoice i
            LEFT JOIN `order` o ON (i.order_id = o.order_id)
            WHERE i.order_id = {$company['order_id']}
                AND i.status != 'Cancelled'                
                {$appendSql}
            ";
            $resultPrevSum  = $db->sql_query($SQLPrevSum);
            $numRowsPrevSum = $db->sql_numrows($resultPrevSum);
            $rowPrevSum     = $db->sql_fetchrow($resultPrevSum);
            $previous_paid_amount = 0;
            if($rowPrevSum['prev_inv_amount_group'] != ''){
                $previous_paid_amount = $rowPrevSum['prev_inv_amount_group'];
                $previous_paid_amount_formatted = number_format($previous_paid_amount, 2);

                $tbl3 = $tbl3.'<tr>
                                <td colspan="2" style="border-bottom:1px dashed black;text-align:right;">AMOUNT PAID PREVIOUS</td>
                                <td style="border-bottom:1px dashed black;text-align:right;">'.$previous_paid_amount_formatted.'</td>
                            </tr>
                ';
            }

            $balanceAmount = number_format($balanceAmount - $previous_paid_amount, 2);

            $tbl3 = $tbl3.'<tr bgColor="#BCFDFD">
                                <td colspan="2" style="border-bottom:1px dashed black;text-align:right;">AMOUNT PAID NOW</td>
                                <td style="border-bottom:1px dashed black;text-align:right;">'.$ReceiptAmount.'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align:center;">'.strtoupper($Total_Amount_in_words).'</td>
                            </tr>
            ';              

            /*<tr>
                                <td colspan="2" style="text-align:right;">BALANCE AMOUNT</td>
                                <td style="text-align:right;">'.$balanceAmount.'</td>
                            </tr>*/
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }
}