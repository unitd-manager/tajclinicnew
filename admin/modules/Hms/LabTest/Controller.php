<?
class CPL_Admin_Modules_Hms_LabTest_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getAddDoctorRecord() {
        return $this->view->getAddDoctorRecord();
    }

    function getAddMedicine() {
        return $this->model->getAddMedicine();
    }

    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getCreateProductLineItems() {
        return $this->model->getCreateProductLineItems();
    }
    
     function getMedicalTestRecordPrintUpdate() {
        return $this->model->getMedicalTestRecordPrintUpdate();
    }


    function getAddProductAndLineItem() {
        return $this->model->getAddProductAndLineItem();
    }

    function getUpdateProductLineItems() {
        return $this->model->getUpdateProductLineItems();
    }

    function getAddDoctorRecordSubmit() {
        return $this->model->getAddDoctorRecordSubmit();
    }

    function getAddLabRecord() {
        return $this->view->getAddLabRecord();
    }

    function getAddLabRecordSubmit() {
        return $this->model->getAddLabRecordSubmit();
    }

    function getLabsSubmit() {
        return $this->model->getLabsSubmit();
    }

    function getDoctorPortalDisplay() {
        return $this->view->getDoctorPortalDisplay();
    }

    function getEditDoctorRecord() {
        return $this->view->getEditDoctorRecord();
    }

    function getEditDoctorRecordSubmit() {
        return $this->model->getEditDoctorRecordSubmit();
    }

    function getDeleteDoctorRecord() {
        return $this->model->getDeleteDoctorRecord();
    }

    function getDeleteMedicineRecord() {
        return $this->model->getDeleteMedicineRecord();
    }

    function getMedicinesPortalDisplay() {
        return $this->view->getMedicinesPortalDisplay();
    }

    function getLabPortalDisplay() {
        return $this->view->getLabPortalDisplay();
    }

    function getEditLabRecord() {
        return $this->view->getEditLabRecord();
    }

    function getEditLabRecordSubmit() {
        return $this->model->getEditLabRecordSubmit();
    }

    function getTreatmentRecordSubmit() {
        return $this->model->getTreatmentRecordSubmit();
    }

    function getDiagnosisRecordSubmit() {
        return $this->model->getDiagnosisRecordSubmit();
    }

    function getMedicalTestRecordSubmit() {
        return $this->model->getMedicalTestRecordSubmit();
    }

    function getMedicalTestRecordUpdate() {
        return $this->model->getMedicalTestRecordUpdate();
    }

    function getSearchList(){
        return $this->view->getSearchList();
    }

    function getPatientVisitSearchResult(){
        return $this->view->getPatientVisitSearchResult();
    }

    function getPatientVisitAppointmentSearchResult(){
        return $this->view->getPatientVisitAppointmentSearchResult();
    }

    function getSelectDoctorDetails(){
        return $this->view->getSelectDoctorDetails();
    }

    function getCreateVisitRecordSubmit(){
        return $this->model->getCreateVisitRecordSubmit();
    }

    function getCreateVisitRecordDirect(){
        return $this->model->getCreateVisitRecordDirect();
    }

    function getPerioChartForm(){
        return $this->view->getPerioChartForm();
    }

    function getPerioChartSymbols(){
        return $this->view->getPerioChartSymbols();
    }

    function getAddPerioChartRecord(){
        return $this->model->getAddPerioChartRecord();
    }

    function getToothlistFirst(){
        return $this->view->getToothlistFirst();
    }

    function getDeletePerioChartRecord(){
        return $this->model->getDeletePerioChartRecord();
    }

    function getToothlistSecond(){
        return $this->view->getToothlistSecond();
    }

    function getMedicalHistorySubmit() {
        return $this->model->getMedicalHistorySubmit();
    }

    function getOralHygienicySubmit() {
        return $this->model->getOralHygienicySubmit();
    }

    function getHabitsSubmit() {
        return $this->model->getHabitsSubmit();
    }

    function getIntraOralExamSubmit() {
        return $this->model->getIntraOralExamSubmit();
    }

    function getExtraOralExamSubmit() {
        return $this->model->getExtraOralExamSubmit();
    }

    function getPeridontiumSubmit() {
        return $this->model->getPeridontiumSubmit();
    }

    function getAddNoteTreatment() {
        return $this->view->getAddNoteTreatment();
    }

    function getAddNoteTreatmentSubmit() {
        return $this->model->getAddNoteTreatmentSubmit();
    }

    function getAddNoteLab() {
        return $this->view->getAddNoteLab();
    }

    function getAddNoteLabSubmit() {
        return $this->model->getAddNoteLabSubmit();
    }

    function getSummaryPortalSubmit() {
        return $this->model->getSummaryPortalSubmit();
    }

    function getMedicalCetificatePortalSubmit() {
        return $this->model->getMedicalCetificatePortalSubmit();
    }

    function getCreateOrder() {
        return $this->model->getCreateOrder();
    }

    function getUpdateConsultingFees() {
        return $this->model->getUpdateConsultingFees();
    }

    function getConvertFollowUpDate() {
        return $this->model->getConvertFollowUpDate();
    }

    function getAddPatientRecord() {
        return $this->view->getAddPatientRecord();
    }

    function getAddPatientRecordSubmit() {
        return $this->model->getAddPatientRecordSubmit();
    }

    function getAddLabsRecord() {
        return $this->view->getAddLabsRecord();
    }

    function getAddLabsRecordSubmit() {
        return $this->model->getAddLabsRecordSubmit();
    }

    function getLabsSupplierJSON() {
        return $this->view->getLabsSupplierJSON();
    }

    function getEditLabsRecord() {
        return $this->view->getEditLabsRecord();
    }

    function getSummaryInOrder() {
        return $this->view->getSummaryInOrder();
    }

    function getLabsDisplay() {
        return $this->view->getLabsDisplay();
    }

    function getEditLabsRecordSubmit() {
        return $this->model->getEditLabsRecordSubmit();
    }

    function getDeleteLabsRecord() {
        return $this->model->getDeleteLabsRecord();
    }

    function getPatientVisitSummaryPortal() {
        return $this->view->getPatientVisitSummaryPortal();
    } 

    function getOverallSummary() {
        return $this->view->getOverallSummary();
    } 

    function getInvoicePortalDisplay() {
        $modObj = getCPModuleObj('hms_order');
        return $modObj->view->getInvoicePortalDisplay();
    }
    
    function getReceiptPortalDisplay() {
        $modObj = getCPModuleObj('hms_order');
        return $modObj->view->getReceiptPortalDisplay();
    }

    function getAcrylicDentureForm() {
        return $this->view->getAcrylicDentureForm();
    }

    function getAddAcrylicDentureFormRecord() {
        return $this->model->getAddAcrylicDentureFormRecord();
    }

    function getTreatmentPortalDisplay() {
        return $this->view->getTreatmentPortalDisplay();
    }

    function getDiagnosisPortalDisplay() {
        return $this->view->getDiagnosisPortalDisplay();
    }

    function getCancelPatientVisitRecord() {
        return $this->model->getCancelPatientVisitRecord();
    }

    function getAddCeramicForm() {
        return $this->view->getAddCeramicForm();
    }

    function getAddCeramicFormRecord() {
        return $this->model->getAddCeramicFormRecord();
    }

    function getAddOrthodonticForm() {
        return $this->view->getAddOrthodonticForm();
    }

    function getAddChromeFormRecord() {
        return $this->model->getAddChromeFormRecord();
    }

    function getViewSummaryLabs(){
        return $this->view->getViewSummaryLabs();
    }

    function getAddTreatmentRecord(){
        return $this->view->getAddTreatmentRecord();
    }

    function getAddTreatmentRecordSubmit(){
        return $this->model->getAddTreatmentRecordSubmit();
    }

    function getAddDiagnosisRecord(){
        return $this->view->getAddDiagnosisRecord();
    }

    function getAddDiagnosisRecordSubmit(){
        return $this->model->getAddDiagnosisRecordSubmit();
    }

    function getUpdateInvoiceCode(){
        return $this->view->getUpdateInvoiceCode();
    }

    function getUpdateReceiptCode(){
        return $this->view->getUpdateReceiptCode();
    }

    function getPrintMedicalCertificateRecord(){
        return $this->model->getPrintMedicalCertificateRecord();
    }

    function getCompanyNameJSON() {
        return $this->view->getCompanyNameJSON();
    }

    function getviewSummaryTreatment(){
        return $this->view->getviewSummaryTreatment();
    }

    function getLabTestsRecordSubmit(){
        return $this->model->getLabTestsRecordSubmit();
    }

    function getSearchComplainDetails(){
        return $this->model->getSearchComplainDetails();
    }

    function getSearchProcedureDetails(){
        return $this->model->getSearchProcedureDetails();
    }

    function getSearchDiagnosisDetails(){
        return $this->model->getSearchDiagnosisDetails();
    }

    function getSearchPatientDetails(){
        return $this->model->getSearchPatientDetails();
    }

    function getSearchPatientDetailsWithPhone(){
        return $this->model->getSearchPatientDetailsWithPhone();
    }

    function getSearchPatientDetailsWithAddress(){
        return $this->model->getSearchPatientDetailsWithAddress();
    }

    function getPrescribeMedicine() {
        return $this->view->getPrescribeMedicine();
    }

    function getPrescribeMedicineFormSubmit() {
        return $this->model->getPrescribeMedicineFormSubmit();
    }

    function getDeleteMedicineVisit() {
        return $this->model->getDeleteMedicineVisit();
    }

    function getDiseaseList() {
        return $this->view->getDiseaseList();
    }

    function getAddComplain() {
        return $this->model->getAddComplain();
    }

    function getAddProcedure() {
        return $this->model->getAddProcedure();
    }

    function getAddDiagnosis() {
        return $this->model->getAddDiagnosis();
    }

    function getPrintTokenForVisit() {
        return $this->view->getPrintTokenForVisit();
    }

    function getPrintPrescription() {
        return $this->view->getPrintPrescription();
    }

    function getAddPrescribeMedicineFormSubmit() {
        return $this->model->getAddPrescribeMedicineFormSubmit();
    }

    function getSummaryPortalDisplay() {
        return $this->view->getSummaryPortalDisplay();
    }

    function getChiefComplainsDisplay() {
        return $this->view->getChiefComplainsDisplay();
    }

    function getProcedurePortalDisplay() {
        return $this->view->getProcedurePortalDisplay();
    }

    function getRemoveComplain() {
        return $this->model->getRemoveComplain();
    }

    function getRemoveDiagnosis() {
        return $this->model->getRemoveDiagnosis();
    }

    function getRemovePrescribeMedicine() {
        return $this->model->getRemovePrescribeMedicine();
    }

    function getChiefComplainsSubmit() {
        return $this->model->getChiefComplainsSubmit();
    }

    function getProcedurePortalSubmit() {
        return $this->model->getProcedurePortalSubmit();
    }

    function getComplainList() {
        return $this->view->getComplainList();
    }

    function getVitalSignsSubmit() {
        return $this->model->getVitalSignsSubmit();
    }

    function getPrintLabReport() {
        return $this->view->getPrintLabReport();
    }

    function getPrintLabRequestForm() {
        return $this->view->getPrintLabRequestForm();
    }

    function getMedicalPortalDisplay() {
        return $this->view->getMedicalPortalDisplay();
    }

    function getApplyMedicine() {
        return $this->model->getApplyMedicine();
    }

    function getCreateAdmission() {
        return $this->model->getCreateAdmission();
    }

    function getPatVisitRecWithInvCancelled() {
        return $this->view->getPatVisitRecWithInvCancelled();
    }

    function getViewMedicalParameters() {
        return $this->view->getViewMedicalParameters();
    }

    function getUpdateMedicalVisitParameter() {
        return $this->model->getUpdateMedicalVisitParameter();
    }

    function getMedicalTestRecordDelete() {
        return $this->model->getMedicalTestRecordDelete();
    }

    function getUpdateMedTestFeesAndNotes() {
        return $this->model->getUpdateMedTestFeesAndNotes();
    }
}