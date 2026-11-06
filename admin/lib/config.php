<?
$cpCfg = array();

$cpCfg['cp.theme'] = 'Angle';
$cpCfg['cp.hasAccessModule'] = true;
$cpCfg['cp.hasMultiUniqueSites']  = false;
$cpCfg['cp.assetVersion'] = '90';
$cpCfg['w.common_multiUniqueSite.ignoreModules'] = array(
     'webBasic_section'
    ,'webBasic_category'
    ,'webBasic_subCategory'
    ,'core_valuelist'
    ,'core_translation'
    ,'tradingsg_stockTransfer'
    ,'hms_treatment'
    ,'hms_diagnosis'
    ,'hms_prescription'
    ,'hms_complain'
    ,'hms_medicalTest'
    ,'hms_vaccination'
    ,'hms_inventory'
    ,'hms_product'
    ,'hms_medicalSupplier'
    ,'hms_labsSupplier'
    ,'hms_labs'
    ,'hms_expenseHead'
    ,'hms_medicalSupplier'
    ,'hms_labsSupplier'
    ,'hms_stockTransfer'
    ,'webBasic_content'
    ,'hms_patientInformationLink'
    ,'hms_labsSupplierLink'
    ,'core_setting'
    ,'core_userGroup'
    ,'tradingsg_supplier'
    ,'tradingin_inventory'
    ,'tradingsg_medicineCompany'
);

$cpCfg['cp.topRooms'] = array(
    'main' => array(
        'title' => 'Patient Mgmt'
       ,'modules' => array(
             'hms_home'
            ,'common_dashboard'
            ,'hms_patientVisit'
            ,'hms_patientInformation'
            ,'hms_prescription'
            ,'hms_complain'
            ,'hms_inPatient'
            ,'hms_labTest'
       )
       ,'default' => 'hms_patientVisit'
    )

    ,'utils' => array(
        'title' => 'Utils'
       ,'modules' => array(
             'hms_medicalTest'
            ,'hms_employee'
            ,'hms_attendance'
            ,'hms_vaccination'
       )
       ,'default' => 'hms_medicalTest'
    )

    ,'pharmacy' => array(
        'title' => 'Pharmacy'
       ,'modules' => array(
             'tradingsg_dashboard'
            ,'tradingsg_purchaseOrder'
            ,'tradingsg_supplier'
            ,'hms_medicineCompany'
            ,'hms_product'
            ,'tradingin_inventory'
            ,'tradingsg_pos'
            ,'hms_order'
            ,'tradingsg_stockTransfer'
       )
       ,'default' => 'hms_medicalTest'
    )

    ,'finance' => array(
        'title' => 'Finance'
       ,'modules' => array(
             'hms_pharmacyDailySales'
            ,'hms_expense'
            ,'hms_expenseHead'
            ,'hms_reports'
       )
       ,'default' => 'hms_order'
    )

    ,'admin' => array(
        'title' => 'Admin'
       ,'modules' => array(
             'hms_internalLocation'
            ,'webBasic_content'
            ,'webBasic_category'
            ,'webBasic_section'
            ,'core_valuelist'
            ,'core_setting'
            ,'core_userGroup'
            ,'core_staff'
            ,'core_translation'
       )
       ,'default' => 'core_translation'
    )

    /*,'reports' => array(
        'title' => 'Reports'
       ,'modules' => array(
             'hms_reports'
       )
       ,'default' => 'hms_reports'
    )*/
);


$hiddenModules = array(
     'common_contactLink'
    ,'common_testRecipientLink'
    ,'hms_contactLink'
    ,'common_interestLink'
    ,'webBasic_section'
    ,'webBasic_category'
    ,'webBasic_subCategory'
    ,'hms_patientInformationLink'
    ,'hms_labsSupplierLink'
    ,'tradingsg_contactLink'
 );


$tmpName = &$cpCfg['cp.topRooms'];
$cpCfg['cp.availableModules'] = array_merge(
     //$tmpName['home']['modules']
    //,$tmpName['dashboard']['modules']
     $tmpName['main']['modules']
    ,$tmpName['utils']['modules']
    ,$tmpName['pharmacy']['modules']
    ,$tmpName['finance']['modules']
    ,$tmpName['admin']['modules']
    //,$tmpName['reports']['modules']
    ,$hiddenModules
);

$cpCfg['cp.availableModGroups'] = array(
     'core'
    ,'common'
    ,'webBasic'
    ,'hms'
    ,'tradingsg'
);

$cpCfg['cp.availableWidgets'] = array(
     'hms_patientVisitSummary'
    ,'hms_dailyCollectionReport'
    ,'hms_patientVisitLocationwiseChart'
    ,'hms_revenueByDay'
    ,'hms_revenueByMonth'
    ,'hms_treatmentHistory'
    ,'hms_visitByDay'
    ,'hms_invoiceSummary'
    ,'hms_companyInvoiceSummary'
    ,'hms_revenueByMonthChart'
    ,'hms_panelInvoiceSummary'
    ,'hms_expenseReport'
    ,'hms_revenueByDayChart'
    ,'hms_patientVisitChart'
    ,'common_multiUniqueSite'
    ,'hms_diseaseSummaryChart'
    ,'hms_stockReport'
    ,'hms_dutyRosterReport'
    ,'hms_labReportSummary'
    ,'hms_labReport'
    ,'hms_labChartSummary'
    ,'hms_drPaymentReport'
    ,'hms_patientVisitByMonth'
    ,'hms_attendanceReport'
    ,'hms_inPatientReport'
    /*,'hms_balanceSheetReport'
    ,'hms_balanceSheetLabReport'
    ,'hms_balanceSheetPharmacyReport'*/
    ,'hms_vaccinationReport'
    ,'hms_attendanceReportDashboard'
    ,'hms_supplierOutstanding'
    ,'hms_supplierOutstandingReport'
    ,'hms_pharmacyDailySales'
    ,'hms_mfgCompanyReport'
    ,'hms_mOLReport'
    ,'hms_productSalesReport'
    ,'hms_nonMovingProducts'
    ,'hms_stockReport'
    ,'hms_purchaseGstReport'
    ,'hms_salesGstReport'
);

$cpCfg['cp.availablePlugins'] = array(
     'common_comment'
    ,'common_media'
    ,'common_login'
    ,'member_forgotPassword'
);


return $cpCfg;