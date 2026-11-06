Util.createCPObject('cpm.hms.patientVisit');

cpm.hms.patientVisit = {
    init: function(){
        /*$(document).ready(function() {
            $('.totalFreeze').scrollToFixed();
        });*/

        $('.qucikaddPatientForm select[name="pass_type"]').livequery('change', function(){
            var pass_type = $(this).val();

            if (pass_type == 'NRIC') {
                $('.row_nric').removeClass('hideme');
                $('.row_registration_no').addClass('hideme');
                $('.row_dob').addClass('hideme');
                $('.row_gender').addClass('hideme');
            } else if (pass_type == 'Passport') {
                $('.row_registration_no').removeClass('hideme');
                $('.row_dob').removeClass('hideme');
                $('.row_gender').removeClass('hideme');
                $('.row_nric').addClass('hideme');
            } else if (pass_type == 'DOB') {
                $('.row_dob').removeClass('hideme');
                $('.row_nric').addClass('hideme');
                $('.row_registration_no').addClass('hideme');
                $('.row_gender').removeClass('hideme');
            }
        });

        //initialize tabs
        $('#tabs').tabs();

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });

        $(".row-hms_patientVisit__hms_product input[name='days']").live("keydown", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            
            if (keyCode == 13 || keyCode == 9) {
                e.preventDefault();
                $(".addExistingMedicine input[name='product_title']").focus();
            }
        });

        $("#tabs ul.ui-tabs-nav li a.chiefComplains").livequery("click", function (e) {            
            e.preventDefault();
            $("input[name='complain_title']").focus();
        });

        $("#tabs ul.ui-tabs-nav li a.medicines").livequery("click", function (e) {            
            e.preventDefault();
            $("input[name='product_title']").focus();
        });

        $("#tabs ul.ui-tabs-nav li a.investigations").livequery("click", function (e) {            
            e.preventDefault();
            $("input[name='complain_title']").focus();
        });

        $('.med_para_notes').livequery('change', function(){
            var notes = $(this).val();
            var medical_test_parameter_id   = $(this).attr('medical_test_parameter_id');
            var medical_test_id   = $(this).attr('medical_test_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');
            var url = 'index.php?module=hms_patientVisit&_spAction=updateMedicalVisitParameter&showHTML=0';
            $.get(url, {medical_test_parameter_id: medical_test_parameter_id, medical_test_id: medical_test_id, notes: notes, patient_visit_id:patient_visit_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .vaccinationTitle').livequery('click', function (e){
            var title = $(this).val();
            var medical_test_id = $(this).attr('medical_test_id');
            var patient_information_id = $(this).attr('patient_information_id');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var group_name = $(this).attr('group_name');
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                var url = 'index.php?module=hms_patientVisit&_spAction=vaccinationRecordSubmit&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, medical_test_id:medical_test_id, patient_information_id:patient_information_id, patient_visit_id:patient_visit_id, group_name:group_name}, function(html){
                    Util.hideProgressInd();
                });
            } else {
                var url = 'index.php?module=hms_patientVisit&_spAction=vaccinationRecordDelete&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, medical_test_id:medical_test_id, patient_visit_id:patient_visit_id, group_name:group_name}, function(html){
                    Util.hideProgressInd();
                });
            }
        });

        $('.m-hms_patientVisit .outside input[type=radio]').livequery('click', function (e){
            var title = $(this).val();
            var parent = $(this).closest('td');
            var medicalTestIdObj = $(this).parents('td').find('input[name=medical_test_id]');
            var medical_test_id = medicalTestIdObj.val();
            var patientVisitIdObj = $(this).parents('td').find('input[name=patient_visit_id]');
            var patient_visit_id = patientVisitIdObj.val();
            var patientInformationIdObj = $(this).parents('td').find('input[name=patient_information_id]');
            var patient_information_id = patientInformationIdObj.val();
            var groupNameObj = $(this).parents('td').find('input[name=group_name]');
            var group_name = groupNameObj.val();

            if(title == 1){
                var url = 'index.php?module=hms_patientVisit&_spAction=vaccinationOutsideRecordSubmit&showHTML=0';
                Util.showProgressInd();
                $.get(url, {medical_test_id:medical_test_id, patient_information_id:patient_information_id, patient_visit_id:patient_visit_id, group_name:group_name}, function(html){
                    Util.hideProgressInd();
                });
            }else{
                var url = 'index.php?module=hms_patientVisit&_spAction=vaccinationRecordDelete&showHTML=0';
                Util.showProgressInd();
                $.get(url, {medical_test_id:medical_test_id, patient_information_id:patient_information_id, patient_visit_id:patient_visit_id, group_name:group_name}, function(html){
                    Util.hideProgressInd();
                });                
            }
        });

        $(".vaccinationMainSubmit").livequery("click", function (e) {            
            e.preventDefault();
            alert('Saved Succesfully')
        });

        $('.vaccinationFees').livequery('change', function(){
            var fees = $(this).val();
            var medical_test_id = $(this).attr('medical_test_id');
            var patient_information_id = $(this).attr('patient_information_id');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var group_name = $(this).attr('group_name');

            var url = 'index.php?module=hms_patientVisit&_spAction=updateVaccinationFeesAndDate&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, fees: fees, patient_visit_id:patient_visit_id, patient_information_id:patient_information_id, group_name:group_name}, function(json){

            });
        });

        $('.vaccinationPortalDetail .givenDateVaccination .fld_date').livequery('change', function(){
            var vaccination_date = $(this).val();
            var parent = $(this).closest('td');
            var medicalTestIdObj = $(this).parents('td').find('input[name=medical_test_id]');
            var medical_test_id = medicalTestIdObj.val();
            var patientVisitIdObj = $(this).parents('td').find('input[name=patient_visit_id]');
            var patient_visit_id = patientVisitIdObj.val();

            var url = 'index.php?module=hms_patientVisit&_spAction=updateVaccinationFeesAndDate&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, vaccination_date:vaccination_date, patient_visit_id:patient_visit_id}, function(json){

            });
        });

        $('.vaccinationPortalDetail .dueDateVaccination .fld_date').livequery('change', function(){
            var due_date = $(this).val();
            var parent = $(this).closest('td');
            var medicalTestIdObj = $(this).parents('td').find('input[name=medical_test_id]');
            var medical_test_id = medicalTestIdObj.val();
            var patientVisitIdObj = $(this).parents('td').find('input[name=patient_visit_id]');
            var patient_visit_id = patientVisitIdObj.val();

            var url = 'index.php?module=hms_patientVisit&_spAction=updateVaccinationFeesAndDate&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, due_date: due_date, patient_visit_id:patient_visit_id}, function(json){

            });
        });

        $(".medParaSubmit").livequery("click", function (e) {            
            e.preventDefault();
            alert('Saved Succesfully')
        });

        $(".medTestMainSubmit").livequery("click", function (e) {            
            e.preventDefault();
            var patient_visit_id   = $(this).attr('patient_visit_id');
            cpm.hms.patientVisit.reloadInvestigationTabPortal(patient_visit_id);
            alert('Saved Succesfully')
        });

        $('.viewMedPara').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Medical Parameters', 500, 400, expObj);
        });

        // For ECHMS
        $('.qucikaddPatientForm select[name=private_insurance]').livequery('change', function(){
            var insuranceVal = $(this).val();

            if (insuranceVal == 'Yes') {
                $('.qucikaddPatientForm .row_insurance_company').removeClass('hideme');
            } else {
                $('.qucikaddPatientForm .row_insurance_company').addClass('hideme');
            }
        });
        $('.qucikaddPatientForm select[name=dr_referral]').livequery('change', function(){
            var insuranceVal = $(this).val();

            if (insuranceVal == 'Yes') {
                $('.qucikaddPatientForm .row_referral_doctor_name').removeClass('hideme');
            } else {
                $('.qucikaddPatientForm .row_referral_doctor_name').addClass('hideme');
            }
        });

        /* Add Medicine in patient visit medicines tab */
        $('.m-hms_patientVisit #addMedicines')
        .livequery('click', cpm.hms.patientVisit.patientMedicineAdd);

        $(".m-hms_patientVisit input[name='product_title']")
        .livequery(cpm.hms.patientVisit.patientProductTitle);

        $('.m-hms_patientVisit .instruction').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var instructionObj = $(this).parents('tr').find('select[name=instruction]');
            var instruction = instructionObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, instruction: instruction, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .route').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var routeObj = $(this).parents('tr').find('select[name=route]');
            var route = routeObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, route: route, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .dosage').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var dosageObj = $(this).parents('tr').find('input[name=dosage]');
            var dosage = dosageObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, dosage: dosage, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .days').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var daysObj = $(this).parents('tr').find('input[name=days]');
            var days = daysObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, days: days, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .qty > input').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var qtyObj = $(this).parents('tr').find('input[name=qty]');
            var qty = qtyObj.val();
            var previousQtyValue = $(this).attr('previousQtyValue');
            //var stock = parseInt($(this).attr('stock'), 10);

            /*if(stock < qty){
                alert('The qty should be less than the stock qty');
                $('#fld_medicineQty_'+rec_id).val(previousQtyValue);
                $('#fld_medicineQty_'+rec_id).focus();
            } else {*/
                var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
                $.get(url, {rec_id: rec_id, qty: qty, product_id: product_id}, function(json){

                });
            //}

        });

        $('.m-hms_patientVisit .selling-price').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var priceObj = $(this).parents('tr').find('input[name=selling_price]');
            var selling_price = priceObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, selling_price: selling_price}, function(json){

            });
        });

        $('.m-hms_patientVisit .employee_id').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var employeeObj = $(this).parents('tr').find('select[name=employee_id]');
            var employee_id = employeeObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, employee_id: employee_id}, function(json){

            });
        });

        $('.m-hms_patientVisit #addDoctorRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_patientVisit #addTreatmentRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id = $('#record_id').val();
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    var treatment_category  = $("select[name='category']").val();
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
                        $.get(url,{patient_visit_id:patient_visit_id, TreatmentCategory: treatment_category}, function(html){
                            $('.treatmentTabDisplay').html(html);
                            Util.hideProgressInd();
                        });
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 340, expObj);
        });

        $('.m-hms_patientVisit #addDiagnosisRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $('#record_id').val();
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    var diagnosis_title  = $('#fld_diagnosis_title').val();
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        var url = 'index.php?module=hms_patientVisit&_spAction=DiagnosisPortalDisplay&showHTML=0';
                        $.get(url,{patient_visit_id:patient_visit_id, searchDiagnosis: diagnosis_title}, function(html){
                            $('.diagnosisTabDisplay').html(html);
                            Util.hideProgressInd();
                            $('.diagnosisSearchAuto').val(diagnosis_title);
                        });
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 320, expObj);
        });

        /* Add Labs Record*/
        $('.m-hms_patientVisit #addLabsRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 450, 250, expObj);
        });

        $('.vaccinationName').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=vaccinationPortalDetailDisplay&showHTML=0';
            var group_name   = $(this).html();
            var patient_visit_id  = $(this).attr('patient_visit_id');
            //alert(group_name);
            $.get(url,  {group_name:group_name, patient_visit_id:patient_visit_id}, function(html){
                $('.vaccinationPortalDetail').html(html);
                Util.hideProgressInd();
            });
        });

        $("select[name='supplier_category']").livequery('change', function(){
            var url = 'index.php?module=hms_patientVisit&_spAction=labsSupplierJSON&showHTML=0';
            var supplier_category = $(this).val();
            $.get(url, {supplier_category: supplier_category}, function (data) {
                $("select[name='supplier_id']").cp_loadSelect(data);
            }, 'json');

        });

        /*$("select[name='patientVisitSummary_type']").livequery('change', function(){
            var patientVisitSummary_type = $(this).val();
            var patient_information_id   = $('#fld_patient_information_id').val();
            var url = 'index.php?module=hms_patientVisit&_spAction=PatientVisitSummaryPortal&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_information_id:patient_information_id, patientVisitSummary_type:patientVisitSummary_type}, function(html){
                $('#patientVisitSummaryPortal').html(html);
                Util.hideProgressInd();
            });
        });*/

        $(".patientVisitSummary_type").livequery('click', function(){
            var link_text = $(this).html();

            if(link_text == 'Display payment due records'){
                var patientVisitSummary_type = 'Due';
                $(".patientVisitSummary_type").html('Show All Records');
            }else if(link_text == 'Show All Records'){
                var patientVisitSummary_type = 'All';
                $(".patientVisitSummary_type").html('Display payment due records');
            }

            var patient_information_id   = $('#fld_patient_information_id').val();
            var url = 'index.php?module=hms_patientVisit&_spAction=PatientVisitSummaryPortal&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_information_id:patient_information_id, patientVisitSummary_type:patientVisitSummary_type}, function(html){
                $('#patientVisitSummaryPortal').html(html);
                Util.hideProgressInd();
            });
        });


        /* Add Patient Record*/
        $('.m-hms_patientVisit #addPatientRecord').livequery('click', cpm.hms.patientVisit.addPatientRecord);

        $('#displayText').livequery('click', function (e){

            var ele = document.getElementById('toggleText');
            var text = document.getElementById('displayText');

            if(ele.style.display == 'block') {
                ele.style.display = 'none';
                text.innerHTML = 'Show More Fields (+)';
            }
            else {
                ele.style.display = 'block';
                text.innerHTML = 'Hide More Fields (-)';
            }
        });

        $('select[name="bill_type"]').livequery('change', function(){
            var bill_type = $(this).val();
            var category  = $(this).attr('category');

            if(bill_type == 'Company' || bill_type == 'Panel'){
                $('.companyDetailsTr').removeClass('companyDetailsHide');
                
                $('.showHideForBillType').removeClass('displayNone');
                $('.showHideForAppointmentType').addClass('displayNone');
                
                if(bill_type == 'Panel'){
                    $('.row_company_id label').html('Panel Name');
                    $('.showHideForBillType').addClass('displayNone');
                    $('.showHideForAppointmentType').removeClass('displayNone');
                }
                
                if(bill_type == 'Company'){
                    bill_type = 'Client';
                    $('.row_company_id label').html('Client Name');
                    $('.showHideForBillType').addClass('displayNone');
                    $('.showHideForAppointmentType').removeClass('displayNone');
                }

                var url = 'index.php?module=hms_patientVisit&_spAction=CompanyNameJSON&showHTML=0';
                $.get(url, {company_category: bill_type}, function (data) {
                    $("select[name='company_id']").cp_loadSelect(data);
                }, 'json');

            }else{
                $('.companyDetailsTr').addClass('companyDetailsHide');
                $('select[name=company_id]').val('');
                $('.showHideForBillType').removeClass('displayNone');
                $('.showHideForAppointmentType').addClass('displayNone');
            }

        });

        /* For ECHMS */
        $('select[name="private_insurance"]').livequery('change', function(){
            var private_insurance = $(this).val();
            
            if(private_insurance == 'Yes'){
                $('.insuranceDetailsTr').removeClass('insuranceDetailsHide');
            } else {
                $('.insuranceDetailsTr').addClass('insuranceDetailsHide');
            }
        });
        /*$('.m-hms_patientVisit #addMedicines').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });*/

        /* Add note in treatment tab*/
        /*$('.m-hms_patientVisit a.addNoteTreatment').livequery('click', function (e){
                var title = "Add Note";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Note added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 500, 300, expObj);
        });*/

        /* Add note in Labs tab*/
        /*$('.m-hms_patientVisit a.addNoteLab').livequery('click', function (e){
                var title = "Add Note";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Note added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 500, 300, expObj);
        });*/

        $('.m-hms_patientVisit #addLabRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_patientVisit .perio_chart_link').livequery('click', function (e){
            var title = "Perio Chart";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var url = "index.php?module=hms_patientVisit&_spAction=perioChartForm&patient_visit_id="+patient_visit_id+"&showHTML=0";
            var exp = {
                url: url
                ,afterOpen: function(){

                }
            };
            Util.openDialogForLink('Perio Chart', '965px', 'auto', 0, exp);

        });

        $('.m-hms_patientVisit #editDoctorRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteDoctorRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=deleteDoctorRecord&showHTML=0';
            var employee_visit_id = $(this).attr('employee_visit_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');
            $.get(url,  {employee_visit_id:employee_visit_id}, function(html){
                cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
            });
        });

        $('.m-hms_patientVisit #editLabsRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'EditLabsRecordportalForm', title, 450, 250, expObj);
        });

        $('.deleteLabsRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=deleteLabsRecord&showHTML=0';
            var labs_id = $(this).attr('labs_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');
            $.get(url,  {labs_id:labs_id}, function(html){
                cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
            });
        });

        $('.m-hms_patientVisit #acrylicFormDenture').livequery('click', function (e){
            var title = "DENTURE EXPRESS";
            e.preventDefault();
            var patient_visit_id   = $(this).attr('patient_visit_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'acrylicDentureForm', title, 810, 508, expObj);
        });

        $('.m-hms_patientVisit #addCeramicForm').livequery('click', function (e){
            var title = "CERAMIC FORM";
            e.preventDefault();
            var patient_visit_id   = $(this).attr('patient_visit_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'AddCeramicFormDetail', title, 810, 508, expObj);
        });

        $('.m-hms_patientVisit #addOrthodontic').livequery('click', function (e){
            var title = "ORTHODONTIC FORM";
            e.preventDefault();
            var patient_visit_id   = $(this).attr('patient_visit_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'ChromeFormDetail', title, 810, 508, expObj);
        });

        $('.m-hms_patientVisit #editMedicineRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteMedicineRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=deleteMedicineRecord&showHTML=0';
            var medicines_visit_id = $(this).attr('medicines_visit_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');
            $.get(url,  {medicines_visit_id:medicines_visit_id}, function(html){
                cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
            });
        });

        $('.m-hms_patientVisit #editLabRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_patientVisit .treatment_id').livequery('click', function (e){
            var treatment_id = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                $('.hideTreatmentDetails_'+treatment_id).show();
                $(this).closest('.treatmentBox').addClass('checkedCheckBoxTreatment');
            } else {
                $('.hideTreatmentDetails_'+treatment_id).hide();
                $(this).closest('.treatmentBox').removeClass('checkedCheckBoxTreatment');
            }
        });

        $('.m-hms_patientVisit .diagnosis_id').livequery('click', function (e){
            var treatment_id = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                $(this).closest('.diagnosisBox').addClass('checkedCheckBoxTreatment');
            } else {
                $(this).closest('.diagnosisBox').removeClass('checkedCheckBoxTreatment');
            }
        });

        $('.m-hms_patientVisit .addNoteTreatment').livequery('click', function (e){
            var parent = $(this).closest('.treatmentNotes');
            $('.hideNotes', parent).slideToggle();
        });

        $('.m-hms_patientVisit .addNoteLab').livequery('click', function (e){
            var parent = $(this).closest('.labVisitNotes');
            $('.hideNotesLab', parent).slideToggle();
        });

        $('.applyForMedicineRecord').livequery('click', function (e){
            var link_text = $(this).html();

            msg = "Do you like to apply medicines?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var patient_visit_id = $(this).attr('patient_visit_id');
                var patient_visit_id_main = $(this).attr('patient_visit_id_main');

                var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=applyMedicine&showHTML=0' +
                        '&patient_visit_id=' + patient_visit_id + '&patient_visit_id_main=' + patient_visit_id_main;
                $.get(url, {patient_visit_id: patient_visit_id, patient_visit_id_main: patient_visit_id_main}, function (html) {
                    cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                    Util.hideProgressInd();
                    alert ('Medicines applied succesfully');
                });
            }
        });

        $('#createAdmission').livequery('click', function (e){
            var link_text = $(this).html();

            msg = "Would you like to create admission?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var patient_visit_id = $(this).attr('patient_visit_id');

                var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=createAdmission&showHTML=0' +
                        '&patient_visit_id=' + patient_visit_id;
                $.get(url, {patient_visit_id: patient_visit_id}, function (html) {
                    var urlRedirect = "index.php?_topRm=main&module=hms_inPatient&_action=edit&record_id=" + html;
                    document.location = urlRedirect;
                });
            }
        });

        /*$('#createOrderRecord').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == 'Generate Bill'){
                msg = "Do you like to generate order?";
            }else if(link_text == 'Update Bill'){
                msg = "Do you like to update order?";
            }

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var patient_visit_id = $(this).attr('patient_visit_id');

                var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=createOrder&showHTML=0' +
                        '&patient_visit_id=' + patient_visit_id;
                $.get(url, {patient_visit_id: patient_visit_id}, function (html) {
                    Util.hideProgressInd();
                    var convertUrl = "index.php?_topRm=finance&module=hms_order&_action=edit&order_id=" + html;
                    document.location = convertUrl;
                });
            }
        });*/

        $(".order_item_type_value").live("keyup", function() {
            var totalAmount = 0;
            var total_count = $('#total_count').val();
            var inputval = $(this).val();
            if(inputval != ''){
                for ( var i = 1; i<=total_count; i++ ){
                    var inputval = $('.list_fees_'+i).val();
                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }
                    totalAmount += Number(inputval);
                }

                $(".invoice_sub_total_amount").val(totalAmount.toFixed(2));

                //var discount = $(".invoice_discount_amount").val();
                if(inputval != ''){
                    totalAmount = totalAmount;
                }

                var overAllTotalAmount = 0;
                var due_amount = $('input[id=fld_due_amount]').val();
                var receipt_amount = $('.m-hms_patientVisit input[name="due_receipt_amount"]').val();
                overAllTotalAmount_due = Number(parseInt(due_amount) + totalAmount);
                overAllTotalAmount = Number(parseInt(due_amount) + totalAmount - parseInt(receipt_amount));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount_due.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));

                $(".invoice_total_amount").val(totalAmount.toFixed(2));
                $("#overall_Total_invoice_hidden").val(totalAmount.toFixed(2));

            }
        });

        $(".invoice_discount_amount").live("keyup", function() {
            var totalAmount = 0;
            var total_count = $('#total_count').val();
            var discount = $(this).val();
            if(discount != ''){
                for ( var i = 1; i<=total_count; i++ ){
                    var inputval = $('.list_fees_'+i).val();
                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }
                    totalAmount += Number(inputval);
                }

                $(".invoice_sub_total_amount").val(totalAmount.toFixed(2));

                totalAmount = totalAmount - discount;

                var overAllTotalAmount = 0;
                var due_amount = $('input[id=fld_due_amount]').val();
                var receipt_amount = $('.m-hms_patientVisit input[name="due_receipt_amount"]').val();
                overAllTotalAmount_due = Number(parseInt(due_amount) + totalAmount);
                overAllTotalAmount = Number(parseInt(due_amount) + totalAmount - parseInt(receipt_amount));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount_due.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));

                $(".invoice_total_amount").val(totalAmount.toFixed(2));
                $("#overall_Total_invoice_hidden").val(totalAmount.toFixed(2));

            }
        });

        $('.m-hms_patientVisit #createOrderRecord').livequery('click', function (e){
            e.preventDefault();
            var patient_visit_id = $(this).attr('patient_visit_id');
            var order_id = $('input[name=order_id]').val();

            var urlOrder = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=createOrder&showHTML=0' +
                           '&patient_visit_id='+ patient_visit_id;
            $.get(urlOrder, {patient_visit_id: patient_visit_id}, function (html) {
                $('#fld_order_id').val(html);
                url = "index.php?module=hms_order&_spAction=generateInvoiceForm&order_id="+html+"&receipt=1&showHTML=0";
                var title = "Bill Generation";
                e.preventDefault();

                var expObj = {
                    url: url
                   ,validate: true
                   ,callbackOnSuccess: function(){
                        //var msg = 'Invoice & Receipt created successfully';
                        //Util.alert(msg, function(){
                            //Util.closeAllDialogs();
                            /*var billsummaryUrl = "index.php?module=hms_patientVisit&_spAction=summaryInOrder&order_id="+order_id+"&showHTML=0";
                            $('#createOrderRecord').after("<div class='billSummaryOrder float_left'><a class='btn btn-primary' href='"+billsummaryUrl+"' id='billSummaryOrder' order_id='"+order_id+"'>Bill Summary</a></div>");
                            $('#createOrderRecord').remove();
                            cpm.hms.patientVisit.reloadReceiptPortal(html);
                            cpm.hms.patientVisit.reloadInvoicePortal(html, patient_visit_id);
                            $("select[name=status]").val('Closed');*/
                            var urlToList = 'index.php?_topRm=main&module=hms_patientVisit&_action=list';
                            document.location = urlToList;

                            var PrintPrescription  = 'index.php?module=hms_patientVisit&_spAction=printPrescription&patient_visit_id='+patient_visit_id+'&showHTML=0';
                            window.open(PrintPrescription, '_blank');
                            //window.location.reload(true);
                        //});
                    }
                }
                Util.openFormInDialog.call('', 'portalForm', title, 400, 325, expObj);
            });
        });
        
          $('.m-hms_patientVisit .printLabel').livequery('click', function (e){
            var print_label = $(this).val();
            var is_checked  = $(this).is(':checked');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var medical_test_id = $(this).attr('medical_test_id');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_patientVisit&_spAction=medicalTestRecordPrintUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {print_label:print_label, patient_visit_id:patient_visit_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).show();
                    parent.addClass('bgColorHighlight');
                });
            } else {
                var url = 'index.php?module=hms_patientVisit&_spAction=medicalTestRecordPrintUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {print_label:print_label, patient_visit_id:patient_visit_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                  
                });
            }
        });

        $('.cancelInvoice').live('click', function (e){
            var invoice_status = $(this).attr('invoice_status');
            var order_id = $('#fld_order_id').val();
            var patient_visit_id = $('#record_id').val();
            if (invoice_status != 'Paid') {
                msg = "Do you like to cancel the Invoice?";
                if (!confirm(msg)){
                    return false;
                }
                else {
                    var url = 'index.php?_topRm=finance&module=hms_order&_spAction=cancelInvoice&showHTML=0';
                    Util.showProgressInd();
                    var invoice_code = $(this).attr('invoice_code');
                    var invoice_id = $(this).attr('invoice_id');
                    $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id}, function(html){

                        /* Checking for one or more receipt for the invoice */
                        if (html == 'Cannot cancel') {
                            Util.alert ('Cancel the related receipts and then proceed canceling the invoice');
                            Util.hideProgressInd();
                        } else {
                            alert ('Invoice Cancelled Succesfully');
                            Util.hideProgressInd();
                            window.location.reload(true);
                            //cpm.hms.patientVisit.reloadInvoicePortal(order_id, patient_visit_id);
                        }
                    });
                }
            } else {
                msg = "Please cancel the receipt and then try canceling the Invoice";
                if (!confirm(msg)){
                    return false;
                } else {
                    return false;
                }
            }
        });

        $('.cancelReceipt').live('click', function (e){
            msg = "Do you like to cancel the Receipt?";
            var order_id = $('#fld_order_id').val();
            var patient_visit_id = $('#record_id').val();
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=hms_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_id = $(this).attr('receipt_id');
                var order_id     = $(this).attr('order_id');
                $.get(url,{receipt_id: receipt_id, order_id:order_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    cpm.hms.patientVisit.reloadReceiptPortal(order_id);
                    cpm.hms.patientVisit.reloadInvoicePortal(order_id, patient_visit_id);
                    //window.location.reload(true);
                });
            }
        });

        $('.m-hms_patientVisit #generateReceipt').livequery('click', function (e){
            var title = "Create Receipt";
            e.preventDefault();
            var order_id = $('#fld_order_id').val();
            var patient_visit_id = $('#record_id').val();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadReceiptPortal(order_id);
                        cpm.hms.patientVisit.reloadInvoicePortal(order_id, patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
        });

        $('.m-hms_patientVisit input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=hms_order&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $('.m-hms_patientVisit input.dueInvoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=hms_order&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('.due_amount_table_disable').slideToggle();
                var overAllTotalAmount = 0;
                var overAllBalanceAmount = 0;
                var invoice_amount = parseInt(html);
                var totalVal   = $('#overall_Total_invoice_hidden').val();
                var receiptVal = $('input[name="due_receipt_amount"]').val();
                overAllTotalAmount   = Number(parseInt(totalVal) + parseInt(invoice_amount));
                overAllBalanceAmount = Number(parseInt(totalVal) + parseInt(invoice_amount) -  parseInt(receiptVal));
                $('input[id=fld_due_amount_hidden]').val(invoice_amount.toFixed(2));
                $('input[id=fld_due_amount]').val(invoice_amount.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllBalanceAmount.toFixed(2));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount.toFixed(2));
                Util.hideProgressInd();
            });
        });

        $('.m-hms_patientVisit input[name="due_receipt_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var receipt_amount = $(this).val();
            var due_amount = $('.m-hms_patientVisit input[name="due_amount"]').val();
            var checked    = $('.m-hms_patientVisit input.dueInvoiceCode').attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;
            if(checkedVal == 1){
                var totalVal = $('input[id=fld_overall_Total_invoice]').val();
            }else{
                var totalVal = $('input[id=overall_Total_invoice_hidden]').val();
            }

            overAllTotalAmount = Number(parseInt(totalVal) - parseInt(receipt_amount));
            $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_patientVisit input[name="due_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var due_amount = $(this).val();
            var totalVal = $('#overall_Total_invoice_hidden').val();
            overAllTotalAmount = Number(parseInt(totalVal) + parseInt(due_amount));
            $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_patientVisit #billSummaryOrder').livequery('click', function (e){
            var title = "Bill Summary";
            var order_id = $(this).attr('order_id');
            e.preventDefault();

            var expObj = {
                afterOpen: function(){
                    Util.closeAllDialogs();
                }
            }

            Util.openDialogForLink.call(this, title, 600, 'auto', expObj);
        });

        $('.m-hms_patientVisit .labTitle').livequery('click', function (e){
            var title = $(this).val();
            var is_checked  = $(this).is(':checked');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var medical_test_id = $(this).attr('medical_test_id');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_patientVisit&_spAction=medicalTestRecordSubmit&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, patient_visit_id:patient_visit_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).show();
                    parent.addClass('bgColorHighlight');
                });
            } else {
                var url = 'index.php?module=hms_patientVisit&_spAction=medicalTestRecordDelete&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, patient_visit_id:patient_visit_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).hide();
                    parent.removeClass('bgColorHighlight');
                });
            }
        });

        $('.m-hms_patientVisit .otherLabs').livequery('click', function (e){
            var lab_supplier_fees = $(this).val();
            var is_checked  = $(this).is(':checked');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var medical_test_id = $(this).attr('medical_test_id');
            var supplier_id = $(this).attr('supplier_id');
            var container = $(this).attr('container');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_patientVisit&_spAction=medicalTestRecordUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {patient_visit_id:patient_visit_id, container:container,  lab_supplier_fees:lab_supplier_fees, supplier_id:supplier_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabSupplierFees', parent).show();
                });
            } else {
                var lab_supplier_fees = 0;
                var supplier_id = '';
                var container = '';
                var url = 'index.php?module=hms_patientVisit&_spAction=medicalTestRecordUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {patient_visit_id: patient_visit_id, container:container, lab_supplier_fees:lab_supplier_fees, supplier_id:supplier_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabSupplierFees', parent).hide();
                });
            }
        });

        $('#portalForm_medicalTestDisplay .fld_date').livequery('change', function(){
            var investigation_date = $(this).val();
            var parent = $(this).closest('div');
            var medicalTestIdObj = $(this).parents('div').find('input[name=medical_test_id]');
            var medical_test_id = medicalTestIdObj.val();
            var patientVisitIdObj = $(this).parents('div').find('input[name=patient_visit_id]');
            var patient_visit_id = patientVisitIdObj.val();

            var url = 'index.php?module=hms_patientVisit&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, investigation_date: investigation_date, patient_visit_id:patient_visit_id}, function(json){

            });
        });

        $('.labFees').livequery('change', function(){
            var fees = $(this).val();
            var medical_test_id   = $(this).attr('medical_test_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');

            var url = 'index.php?module=hms_patientVisit&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, fees: fees, patient_visit_id:patient_visit_id}, function(json){

            });
        });

        $('.labNotes').livequery('change', function(){
            var notes = $(this).val();
            var medical_test_id   = $(this).attr('medical_test_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');

            var url = 'index.php?module=hms_patientVisit&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, notes: notes, patient_visit_id:patient_visit_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .searchPatientButton').livequery('click', function (e){
           var inputBoxVaue  = $('input[name=patient_name]').val();
           var dropdownValue = $('#fld_search_type_by_list').val();
           var lock = 1;
           var url = 'index.php?module=hms_patientVisit&_spAction=patientVisitSearchResult&showHTML=0';
           Util.showProgressInd();
           $.get(url, {inputBoxVaue: inputBoxVaue, dropdownValue:dropdownValue, lock:lock}, function(html){
                Util.hideProgressInd();
                $('.searchTableInPatientVisit').html(html);
                $('.searchTableInPatientVisit').removeClass('searchTableInPatientVisithide');
                $('.searchTableInPatientVisitAppointment').hide();
                if(inputBoxVaue == ''){
                    $('.searchTableInPatientVisitAppointment').show();
                    $('.searchTableInPatientVisit').addClass('searchTableInPatientVisithide');
                }
           });

        });

        $('a.createVisit').livequery('click', cpm.hms.patientVisit.createPatientVisit);

        $('input[name="selected_tooth[]"]').live('click', function (e){
            var tooth_id  = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                cpm.hms.patientVisit.SelectSymbolsForm();
            }
        });

        $('input[name="selected_tooth2[]"]').live('click', function (e){
            var tooth_id         = $(this).val();
            var is_checked       = $(this).is(':checked');
            var patient_visit_id = $('#patient_visit_id').val();
            var checboxid        = $(this).attr('Checkbox_ID');
            var symbol_name      = $('#bridge_id').val();
            var prevTooth_count  = $('#toothPrev_count').val();
            var tooth_form_type  = $('#fld_tooth_form_type').val();
            var labs_id          = $('#fld_labs_id').val();
            var tooth_part       = $(this).attr('tooth_part');

            if(is_checked == true){
                if(symbol_name != undefined){
                    var i;
                    for (i = parseInt(prevTooth_count); i <= parseInt(checboxid); i++) {
                        $("#selected_tooth2_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth2_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    for (i = parseInt(prevTooth_count); i >= parseInt(checboxid); i--) {
                        $("#selected_tooth2_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth2_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.patientVisit.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id, tooth_part);
                }
            }
        });

        $('input[name="selected_tooth3[]"]').live('click', function (e){
            var tooth_id         = $(this).val();
            var is_checked       = $(this).is(':checked');
            var patient_visit_id = $('#patient_visit_id').val();
            var checboxid        = $(this).attr('Checkbox_ID');
            var symbol_name      = $('#bridge_id').val();
            var prevTooth_count  = $('#toothPrev_count').val();
            var tooth_form_type  = $('#fld_tooth_form_type').val();
            var labs_id          = $('#fld_labs_id').val();
            var tooth_part       = $(this).attr('tooth_part');

            if(is_checked == true){
                if(symbol_name != undefined){
                    var i;
                    for (i = parseInt(prevTooth_count); i <= parseInt(checboxid); i++) {
                        $("#selected_tooth3_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth3_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }


                    for (i = parseInt(prevTooth_count); i >= parseInt(checboxid); i--) {
                        $("#selected_tooth3_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth3_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.patientVisit.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id, tooth_part);
                }
            }
        });

        $('input[name="selected_Symbols[]"]').live('click', function (e){
            var symbol_name      = $(this).val();
            var is_checked       = $(this).is(':checked');
            var tooth_id         = $('#tooth_id').val();
            var prevcount        = $('#Checkbox_ID').val();
            var patient_visit_id = $('#patient_visit_id').val();
            var tooth_form_type  = $('#fld_tooth_form_type').val();
            var labs_id          = $('#fld_labs_id').val();
            var tooth_part       = $('#tooth_part').val();

            if(is_checked == true){
                if(symbol_name == 'Bridge'){
                    var msg = "Select the tooth to be connected?";
                    if (confirm(msg)){
                        $('#dialog1').dialog('close');
                        $('.ym-fbox-check').after("<input class='bridgeIDPassing' type='hidden' id='bridge_id' value=" + symbol_name + ">");
                        $('.ym-fbox-check').after("<input class='previousToothcount' type='hidden' id='toothPrev_count' value=" + prevcount + ">");
                    }else{
                        Util.showProgressInd();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
                            $('#dialog1').dialog('close');
                            Util.hideProgressInd();
                        });

                        cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                        cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                    }
                }
                else{
                    Util.showProgressInd();
                    var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                    $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
                        $('#dialog1').dialog('close');
                        Util.hideProgressInd();
                    });

                    cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                }
            }
        });

        $('select[name=employee_id]').livequery('change', function(){
            var employee_id = $(this).val();

            var url = 'index.php?module=hms_patientVisit&_spAction=updateConsultingFees&showHTML=0';
            $.get(url, {employee_id: employee_id}, function(html){
                $('#fld_consultation_fees').val(html);
            });
        });

        $('input[name=height]').livequery('change', function(){
            var height = $(this).val();
            var weight = $('input[name=weight]').val();

            var url = 'index.php?module=hms_patientVisit&_spAction=calculateBMI&showHTML=0';
            $.get(url, {height: height, weight:weight}, function(html){
                $('#fld_bmi').val(html);
            });
        });

        $('input[name=weight]').livequery('change', function(){
            var weight = $(this).val();
            var height = $('input[name=height]').val();

            var url = 'index.php?module=hms_patientVisit&_spAction=calculateBMI&showHTML=0';
            $.get(url, {height: height, weight:weight}, function(html){
                $('#fld_bmi').val(html);
            });
        });

        $('.followUpDate select').livequery('change', function(){
            var follow_up_date = $(this).val();
            var parent = $(this).closest('.treatmentNotes');

            var url = 'index.php?module=hms_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('.followUpDate input', parent).val(html);
            });
        });

        $('select[name=follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_follow_up_date').val(html);
            });
        });

        $('select[name=longtime_follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_longtime_follow_up_date').val(html);
            });
        });

        $('.m-hms_patientVisit .selectedToothSymbolEdit').livequery('click', function (e){
            var tooth_id         = $(this).attr('tooth_id');
            var tooth_part       = $(this).attr('tooth_part');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var checboxid        = $(this).attr('Checkbox_ID');
            cpm.hms.patientVisit.fnOpenButtonTextChangedDialog(checboxid, tooth_id, patient_visit_id, tooth_part);
        });

        $('.treatmentStatus').livequery('click', function (e){
            var status = $(this).val();
            var parent = $(this).closest('.treatmentNotes');
            if(status == 'Current'){
                $('.treatmentStatus', parent).blur();
                $('.treatmentStatus', parent).val('Future');
                $('.followUpDate', parent).show();

            } else {
                $('.treatmentStatus', parent).blur();
                $('.treatmentStatus', parent).val('Current');
                $('.followUpDate', parent).hide();
                $('.fld_date', parent).val('');
            }
        });

        /*$('.goToSearchPatientVisit').livequery('click', function (e){
            $('.searchListDisplay').show();
            $('.defaultListDisplay').hide();
            $('.cpSearch').hide();
        });*/

        $('.displayVisitRecords').livequery('click', function (e){
            $('.searchListDisplay').hide();
            $('.defaultListDisplay').show();
            $('.cpSearch').show();
        });

        /*$('.m-hms_patientVisit .TreatmentSubmit').livequery('click', function (e){
            var url  = 'index.php?module=hms_patientVisit&_spAction=treatmentRecordSubmit&showHTML=0';
            $.get(url, function(html){
                alert('Submited');
            });

        });*/
        $('#portalForm_labTestsDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_treatmentDisplay').livequery('submit', function(){
          Util.showProgressInd();
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
                var patient_visit_id = $('#record_id').val();
                cpm.hms.patientVisit.reloadTreatmentTabPortal(patient_visit_id);
          },'json');
          return false;
       });

        /*$('#portalForm_medicalTestDisplay').livequery('submit', function(){
          Util.showProgressInd();
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
                var patient_visit_id = $('#record_id').val();
                cpm.hms.patientVisit.reloadInvestigationTabPortal(patient_visit_id);
          },'json');
          return false;
       });*/

        $('#portalForm_subjectiveAssessmentDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_objectiveAssessmentDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_problemAnalysisDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_goalSmartDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_goalSmartDisplayForTreatmentTab').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_medicalTestDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_vitalSignsDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_complainDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_procedureDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('.medicineSave').livequery('click', function(){
            alert('Medicine saved successfully')
       });
        $('#portalForm_vaccinationDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);


        $('#portalForm_labDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_summaryDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Informations updated Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_labsDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Record Created Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_diagnosisDisplay').livequery('submit', function(){
          Util.showProgressInd();
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
                var patient_visit_id = $('#record_id').val();
                cpm.hms.patientVisit.reloadDiagnosisTabPortal(patient_visit_id);
          },'json');
          return false;
       });

        $('#portalForm_medHisDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_oralHygienic').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_habits').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_intraOral').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_extraOral').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_peridontium').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('form#portalForm_medicalCertificateDisplay').livequery(function() {
            Util.setUpAjaxFormGeneral('portalForm_medicalCertificateDisplay', function(json){
                Util.alert('Medical Certificate updated Successfully');
                Util.hideProgressInd();
            });
        });

        /*
        $('#portalForm_medicalCertificateDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
            //alert("myObject is " + response.toSource());
                //var response = response['msg'];
                // do something here on success
                var mgsalert='Medical Certificate updated Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });
        */

       $('.cancelVisitRecord').livequery('click', function(e){
            msg = "Please note related receipt,\n\n invoice will also be cancelled,\n\n Do you like to Cancel?";
            var patient_visit_id = $(this).attr('patient_visit_id');
            if (!confirm(msg)){
                return false;
            }
            else {
                Util.showProgressInd();
                var url = 'index.php?module=hms_patientVisit&_spAction=cancelPatientVisitRecord&showHTML=0';
                $.get(url,{patient_visit_id: patient_visit_id}, function(html){
                    Util.hideProgressInd();
                    Util.alert('Patient Visit & Related Invoice, Receipt Cancelled Successfully!')
                    window.location.reload(true);
                });
            }
       });

       $('.viewSummaryForTreatmentRecord').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Treatment Summary', 1100, 550, expObj);
        });

       $('.viewSummaryForLabsRecord').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Labs Payment Summary', 460, 422, expObj);
        });

        $('#supplier_categoryFormLink').live('click', function (e){
            alert('Please Cancel the receipt(s) to edit the record!');
        });

        $('#supplier_DeleteLink').live('click', function (e){
            alert('Please Cancel the receipt(s) to delete the record!');
        });

        $('#generateReceiptnoOrder_Id').live('click', function (e){
            alert('Please click patient visit code in the supplier link behind.\n\nAnd Generate Bill !');
        });

        $('#generateReceiptnoAmount').live('click', function (e){
            alert('Please enter amount before generating receipt!');
        });

        $('#generatenoReceipt').live('click', function (e){
            alert('Please generate receipt!');
        });

        $("select[name='treatmentCategory']").livequery('change', function (e){
            var TreatmentCategory = $(this).val();
            var patient_visit_id = $('#record_id').val();

            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
            $.get(url,{patient_visit_id:patient_visit_id, TreatmentCategory: TreatmentCategory}, function(html){
                $('.treatmentTabDisplay').html(html);
                Util.hideProgressInd();
            });
        });


        var timeoutId2;
        $(".treatmentSearchAuto").livequery("keyup", function (){
            clearTimeout(timeoutId2);
            var searchTreatment = $(this).val();
            var patient_visit_id = $('#record_id').val();

            timeoutId2 = setTimeout(function() {
                var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
                $.get(url,{patient_visit_id:patient_visit_id, searchTreatment: searchTreatment}, function(html){
                    $('.treatmentTabDisplay').html(html);
                    Util.hideProgressInd();
                    $('.treatmentSearchAuto').val(searchTreatment);
                });
            }, 1000);
        });

        var timeoutId3;
        $(".diagnosisSearchAuto").livequery("keyup", function (){
            clearTimeout(timeoutId3);
            var searchDiagnosis = $(this).val();
            var patient_visit_id = $('#record_id').val();

            timeoutId3 = setTimeout(function() {
                var url = 'index.php?module=hms_patientVisit&_spAction=DiagnosisPortalDisplay&showHTML=0';
                $.get(url,{patient_visit_id:patient_visit_id, searchDiagnosis:searchDiagnosis}, function(html){
                    $('.diagnosisTabDisplay').html(html);
                    Util.hideProgressInd();
                    $('.diagnosisSearchAuto').val(searchDiagnosis);
                });
            }, 1000);
        });

        $(".dlg-portalFormTextAreaAddNotesOnImageMapping button.btn-cancel").live('click', function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

        $(".dlg-portalFormTextAreaEditNotesOnImageMapping button.btn-cancel").live('click', function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

        $(".dlg-portalFormTextAreaAddNotesOnImageMapping .ui-dialog-titlebar-close ").livequery("click", function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

        $(".dlg-portalFormTextAreaEditNotesOnImageMapping .ui-dialog-titlebar-close ").livequery("click", function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

        $('.viewPrescribeMedicineRecord').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Prescribe Medicine', 1100, 550, expObj);
        });

        $('.addmedicine').livequery('click', function(e){
            msg = "Do you like to add Medicine?";
            var prescribe_medicine_id = $(this).attr('prescribe_medicine_id');
            
            if (!confirm(msg)){
                return false;
            } else {
                
                var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=prescribeMedicineFormSubmit&showHTML=0';
                $.get(url, {prescribe_medicine_id: prescribe_medicine_id}, function(html){
                    Util.closeAllDialogs();
                    Util.alert('Medicine added Successfully');
                });
                Util.hideProgressInd();
            }
        });

        $('.removemedicine').livequery('click', function(e){
            msg = "Do you like to delete Medicine?";
            var medicines_visit_id = $(this).attr('medicines_visit_id');
            if (!confirm(msg)){
                return false;
            } else {
                
                var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=deleteMedicineVisit&showHTML=0';
                $.get(url, {medicines_visit_id: medicines_visit_id}, function(html){
                    Util.closeAllDialogs();
                    Util.alert('Medicine deleted Successfully');
                });
                Util.hideProgressInd();
            }
        });

        $('.viewDiseaseListRecord').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Disease List', 500, 600, expObj);
        });

        $('.viewComplainListRecord').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Complain List', 500, 600, expObj);
        });

        $('.viewOverallSummary').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Overall Summary', 800, 500, expObj);
        });

        $('.vaccinationSchedule').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Vaccination Schedule', 500, 550, expObj);
        });

        $('.patientVisitVaccinationSchedule input.vaccinationId').livequery('change', function (e){
            Util.showProgressInd();
            var amount = parseInt(0);
            $('.patientVisitVaccinationSchedule tbody tr input[type=checkbox]:checked').each(function(){
                var parent = $(this).closest('tr');
                var valueObj = $('td .fees', parent);
                var fees = $(this).val();
                //alert(fees);
                if(fees != ''){
                    amount += Math.round(parseFloat(fees));
                }
            });
            $('.patientVisitVaccinationSchedule #fld_fees_amount').html(amount);

            Util.hideProgressInd();
        });

        $('.summaryForVaccination').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Vaccination Summary', 800, 500, expObj);
        });

        /*$('input[name=disease_name]').livequery('click', function(){
            var parent = $(this).closest('tr');
            var disease_name = $(this).attr('disease_name');
            //var prescription_id = $(this).attr('prescription_id');
            var patient_visit_id = $(this).attr('patient_visit_id');
            if(patient_visit_id != ''){
                var url = 'index.php?module=hms_patientVisit&_spAction=addComplain';
                Util.showProgressInd();
                
                $.get(url, {patient_visit_id: patient_visit_id, disease_name: disease_name}, function(html){
                    Util.closeAllDialogs();
                });
                Util.hideProgressInd();
                window.location.reload(true);
            }
            
        });*/

        $("input[name='complain_title']")
        .livequery(cpm.hms.patientVisit.complainName);

        $("input[name='procedure_title']")
        .livequery(cpm.hms.patientVisit.procedureName);

        $("input[name='diagnosis_title']")
        .livequery(cpm.hms.patientVisit.diagnosisName);

        $("input[name='patient_name']")
        .livequery(cpm.hms.patientVisit.patientName);

        $("input[name='phone']")
        .livequery(cpm.hms.patientVisit.patientPhone);

        $("input[name='address_area']")
        .livequery(cpm.hms.patientVisit.patientAddress);

        $("input[name='uhid']")
        .livequery(cpm.hms.patientVisit.patientUhid);

        $(".createPatientVisitSearchButton").livequery("click", function (e){
            var patient_information_id = $('input[name=patient_information_id]').val();
            var patient_visit_id       = $('#record_id').val();
            var patient_name           = $('input[name=patient_name]').val();
            var father_name            = $('input[name=father_name]').val();
            var husband_name           = $('input[name=spuse_name]').val();
            var address_area           = $('input[name=address_area]').val();
            var address_street         = $('input[name=address_street]').val();
            var phone                  = $('input[name=phone]').val();
            var age_year               = $('input[name=age_year]').val();
            var age_month              = $('input[name=age_month]').val();
            var age_day                = $('input[name=age_day]').val();
            var gender                 = $('select[name=gender]').val();
            var weight                 = $('input[name=weight]').val();
            var temperature            = $('input[name=temperature]').val();
            var height                 = $('input[name=height]').val();
            var bmi                    = $('input[name=bmi]').val();
            var blood_pressure         = $('input[name=blood_pressure]').val();
            var pulse_rate             = $('input[name=pulse_rate]').val();
            var respiratory_rate       = $('input[name=respiratory_rate]').val();
            var SpO2                   = $('input[name=SpO2]').val();

            if(patient_name == ""){
                Util.alert("Please Select/Enter Patient Details To Create Visit!");
            }
            else{                
                cpm.hms.patientVisit.createPatientVisitDetails(patient_information_id, '', patient_name, age_year, age_month, age_day, gender, father_name, husband_name, address_area, address_street, phone, weight, temperature, height, bmi, blood_pressure, pulse_rate, respiratory_rate, SpO2);
            }
        });

        /*$('input[name=disease_name]').livequery('click', function(){
            var prescription_id  = $(this).attr('prescription_id');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var patient_information_id = $(this).attr('patient_information_id');
            var disease_name     = $(this).attr('disease_name');
            var is_checked       = $(this).is(':checked');
            
            if(is_checked == true){
                var urlDisease = 'index.php?module=hms_patientVisit&_spAction=addDiagnosis&showHTML=0';
                Util.showProgressInd();
                
                $.get(urlDisease, {prescription_id: prescription_id, patient_information_id: patient_information_id, patient_visit_id: patient_visit_id, disease_name: disease_name}, function(html){
                    var urlMedicine = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=addPrescribeMedicineFormSubmit&showHTML=0';
                    $.get(urlMedicine, {prescription_id: prescription_id, patient_visit_id:patient_visit_id}, function(html){
                        //Util.closeAllDialogs();
                        Util.hideProgressInd();
                        cpm.hms.patientVisit.reloadSummaryPortal(patient_visit_id);
                        cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                        //Util.alert('Medicine added Successfully');
                    });
                });
            }else{
                var urlDiseaseRemove = 'index.php?module=hms_patientVisit&_spAction=removeDiagnosis&showHTML=0';
                Util.showProgressInd();
                
                $.get(urlDiseaseRemove, {prescription_id: prescription_id, patient_information_id: patient_information_id, patient_visit_id: patient_visit_id, disease_name: disease_name}, function(html){
                    var urlMedicineRemove = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=RemovePrescribeMedicine&showHTML=0';
                    $.get(urlMedicineRemove, {prescription_id: prescription_id, patient_visit_id:patient_visit_id}, function(html){
                        //Util.closeAllDialogs();
                        Util.hideProgressInd();
                        cpm.hms.patientVisit.reloadSummaryPortal(patient_visit_id);
                        cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                        //Util.alert('Medicine added Successfully');
                    });
                });
            }
            
        });*/

        $('.diagnosisSave').livequery('click', function(){
            var disease_name = $('input[name=diagnosis_title]').val();
            var patient_visit_id = $('#record_id').val();

            var urlDisease = 'index.php?module=hms_patientVisit&_spAction=addDiagnosis&showHTML=0';
            Util.showProgressInd();
            
            $.get(urlDisease, {patient_visit_id: patient_visit_id, disease_name: disease_name}, function(html){
                Util.hideProgressInd();
                cpm.hms.patientVisit.reloadSummaryPortal(patient_visit_id);
            });            
        });

        $('.newProductTitle').livequery('click', function(){
            var title = $('input[name=product_title_new]').val();
            var patient_visit_id = $('#record_id').val();

            if(title == ''){
                alert('Please enter the new medicine');
            }
            else{
                var url = 'index.php?module=hms_patientVisit&_spAction=addProductAndLineItem&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title: title, patient_visit_id: patient_visit_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                });
            }
        });

        /*$('input[name=complain_title]').livequery('click', function(){
            var complain_id  = $(this).attr('complain_id');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var patient_information_id = $(this).attr('patient_information_id');
            var title     = $(this).attr('title');
            var is_checked       = $(this).is(':checked');

            if(is_checked == true){
                var urlDisease = 'index.php?module=hms_patientVisit&_spAction=addComplain&showHTML=0';
                Util.showProgressInd();
                $.get(urlDisease, {complain_id: complain_id, patient_information_id: patient_information_id, patient_visit_id: patient_visit_id, title: title}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.patientVisit.reloadChiefComplains(patient_visit_id);
                });
            }else{
                var urlDiseaseRemove = 'index.php?module=hms_patientVisit&_spAction=removeComplain&showHTML=0';
                Util.showProgressInd();
                $.get(urlDiseaseRemove, {complain_id: complain_id, patient_information_id: patient_information_id, patient_visit_id: patient_visit_id, title: title}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.patientVisit.reloadChiefComplains(patient_visit_id);
                });
            }
            
        });*/

        /* Update town */
        $('#Updatetown').live('click', function (e){
                var patient_information_id = $(this).attr('patient_information_id');
                var title = "Update Town";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Town Updated Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'updateTownPortalForm', title, 600, 400, expObj);
        });

        $('.complainSave').livequery('click', function(){
            //var patient_information_id = $(this).attr('patient_information_id');
            var title = $('input[name=complain_title]').val();
            var patient_visit_id = $('#record_id').val();

            var url = 'index.php?module=hms_patientVisit&_spAction=addComplain&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_visit_id: patient_visit_id, title: title}, function(html){
                Util.hideProgressInd();
                cpm.hms.patientVisit.reloadChiefComplains(patient_visit_id);
            });
            
        });

        $('.procedureSave').livequery('click', function(){
            //var patient_information_id = $(this).attr('patient_information_id');
            var visit_procedure = $('input[name=procedure_title]').val();
            var patient_visit_id = $('#record_id').val();

            var url = 'index.php?module=hms_patientVisit&_spAction=addProcedure&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_visit_id: patient_visit_id, visit_procedure: visit_procedure}, function(html){
                Util.hideProgressInd();
                cpm.hms.patientVisit.reloadProcedure(patient_visit_id);
            });
            
        });

        $('.clearSearchValuesButton').livequery('click', function(){
            cpm.hms.patientVisit.clearSearchValues();
        });

        $('.feesUpdateForVisitRecord').livequery('click', function(){
            var consultation_fees = $(this).html();
            var employee_visit_id = $('input[name=employee_visit_id]').val();
            var patient_visit_id  = $('#record_id').val();

            var url = 'index.php?module=hms_patientVisit&_spAction=UpdateConsultingFeesLink&showHTML=0';
            Util.showProgressInd();
            $.get(url, {consultation_fees: consultation_fees, employee_visit_id: employee_visit_id, patient_visit_id: patient_visit_id}, function(html){
                Util.hideProgressInd();
                $('input[name=consultation_fees]').val(consultation_fees);
            });
            
        });

        $('.m-hms_patientVisit select[name=af_bf_food]').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var afBfFoodObj = $(this).parents('tr').find('select[name=af_bf_food]');
            var afBfFood    = afBfFoodObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, afBfFood: afBfFood, product_id: product_id}, function(json){

            });
        });

        $('#portalForm_dneReportDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert = 'Dne Report Updated Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_dneReportEarDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert = 'Dne Ear Report Updated Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

        $('#portalForm_dneReportNoseDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert = 'Dne Nose Report Updated Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

    },


    reloadDoctorTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=doctorPortalDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#doctorDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadSummaryPortal: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=summaryPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {patient_visit_id:patient_visit_id}, function(html){
            $('#summaryPortalDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadChiefComplains: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=chiefComplainsDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {patient_visit_id:patient_visit_id}, function(html){
            $('#chiefComplains').html(html);
            Util.hideProgressInd();
        });
    },

    reloadProcedure: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=procedurePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {patient_visit_id:patient_visit_id}, function(html){
            $('#procedurePortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabsTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=LabsDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#labsDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadMedicineTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=medicinesPortalDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#medicinesDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=labPortalDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#labDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadToothList2: function(patient_visit_id, tooth_form_type, labs_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=toothlistFirst&showHTML=0';
        $.get(url, {patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
            $('.toothSelectCheckbox2').html(html);
        });
    },

    reloadToothList3: function(patient_visit_id, tooth_form_type, labs_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=toothlistSecond&showHTML=0';
        $.get(url, {patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
            $('.toothSelectCheckbox3').html(html);
        });
    },

    patientMedicineAdd: function(e) {
        e.preventDefault();
        var patient_visit_id = $(this).attr('patient_visit_id');
        Util.showProgressInd();
        var url = 'index.php?module=hms_patientVisit&_spAction=addMedicine&patient_visit_id=' + patient_visit_id + '&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(){
            cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
            Util.hideProgressInd();
        });
    },

    patientProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_patientVisit&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 1
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id = selectedObj.id
                //alert (product_id);
                $(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");
                //alert ('12344444');

                //To Populate the related values in the table
                //--------------------------------------------
                Util.showProgressInd();
                var parent          = $(this).closest('tr');
                var rec_id          = $(parent).attr('recid');
                var productTitleObj = $(this ).closest('tr').find('.title');
                var instructionObj  = $(this ).parents('tr').find('.instruction select[name=instruction]');
                var dosageObj       = $(this ).closest('tr').find('.dosage');
            
                var patient_visit_id   = $(this).attr('patient_visit_id');

                var url = 'index.php?module=hms_patientVisit&_spAction=createProductLineItems&showHTML=0';
                $.get(url, {product_id: product_id, patient_visit_id: patient_visit_id}, function(json){
                    cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                    alert('Medicine Added Successfully');
                    $(".addExistingMedicine input[name='product_title']").focus();
                });
            }
        });
    },

    createPatientVisitDetails: function(patient_information_id, appointment_id, patient_name, age_year, age_month, age_day, gender, father_name, husband_name, address_area, address_street, phone, weight, temperature, height, bmi, blood_pressure, pulse_rate, respiratory_rate, SpO2){
        var title = "Choose Doctor/Nurse";
        var url   = "index.php?module=hms_patientVisit&_spAction=selectDoctorDetails&patient_information_id="+patient_information_id
                    +"&appointment_id="+appointment_id
                    +"&patient_name="+patient_name
                    +"&father_name="+father_name
                    +"&husband_name="+husband_name
                    +"&address_area="+address_area
                    +"&address_street="+address_street
                    +"&phone="+phone
                    +"&age_year="+age_year
                    +"&age_month="+age_month
                    +"&age_day="+age_day
                    +"&gender="+gender
                    +"&weight="+weight
                    +"&temperature="+temperature
                    +"&height="+height
                    +"&bmi="+bmi
                    +"&blood_pressure="+blood_pressure
                    +"&pulse_rate="+pulse_rate
                    +"&respiratory_rate="+respiratory_rate
                    +"&SpO2="+SpO2
                    +"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(html){
                Util.closeAllDialogs();

                cpm.hms.patientVisit.reloadSearchResult();
                patient_visit_id = html.returnUrl;
                var printUrl = "index.php?_topRm=main&module=hms_patientVisit&_spAction=printTokenForVisit&patient_information_id="+patient_information_id+"&patient_visit_id="+patient_visit_id+"&showHTML=0";
                window.open(printUrl,'_blank');

                //cpm.hms.patientVisit.reloadQueueno();
                var mgsalert2='Patient Visit Record Created';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
            }
        };
        Util.openFormInDialog.call('','portalFormPatientVisitCreate', title,  588, 'auto', exp);
    },

    SelectSymbolsForm: function(checboxid, tooth_id, patient_visit_id, tooth_part){
        var url = "index.php?module=hms_patientVisit&_spAction=perioChartSymbols&tooth_id="+tooth_id+"&checboxid="+checboxid+"&tooth_part="+tooth_part+"&showHTML=0";

        var exp = {
            url: url
            ,afterOpen: function(){

            }
        };
        Util.openDialogForLink('', 588, 'auto', 0, exp);
    },

    reloadSearchResult: function(){
        var inputBoxVaue  = $('.searchInputPatientVisit').val();
        var dropdownValue = $('#fld_search_type_by_list').val();
        var url = 'index.php?module=hms_patientVisit&_spAction=patientVisitAppointmentSearchResult&showHTML=0';

        $.get(url, {inputBoxVaue: inputBoxVaue, dropdownValue:dropdownValue}, function(html){
            $('input[name=patient_information_id]').val('');
            $('input[name=patient_name]').val('');
            $('input[name=father_name]').val('');
            $('input[name=spuse_name]').val('');
            $('input[name=address_area]').val('');
            $('input[name=address_street]').val('');
            $('input[name=age]').val('');
            $('select[name=gender]').val('Male');
            $('input[name=phone]').val('');
            $('input[name=weight]').val('');
            $('input[name=temperature]').val('');
            $('input[name=height]').val('');
            $('input[name=bmi]').val('');
            $('input[name=blood_pressure]').val('');
            $('input[name=pulse_rate]').val('');
            $('input[name=respiratory_rate]').val('');
            $('input[name=SpO2]').val('');
            $('.searchTableInPatientVisit').html(html);
            $('.searchTableInPatientVisit').removeClass('searchTableInPatientVisithide');
            $('.searchTableInPatientVisitAppointment').hide();

        });
    },

    reloadQueueno: function(){
        var url = 'index.php?_theme=matrix&_spAction=patientQueueNo&showHTML=0';
        $.get(url,  function(html){
            $('.queueNumberDisplay').html(html);
        });
    },

    DeleteFromPerioTable: function(tooth_id, patient_visit_id, tooth_form_type, labs_id, tooth_part){
        var url = 'index.php?module=hms_patientVisit&_spAction=deletePerioChartRecord&showHTML=0';
        $.get(url, {tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
            cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
            cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
        });
    },

    fnOpenButtonTextChangedDialog: function(checboxid, tooth_id, patient_visit_id, tooth_part) {
        var buf = "Are you sure want to?";
        var tooth_form_type  = $('#fld_tooth_form_type').val();
        var labs_id          = $('#fld_labs_id').val();
        // buf will be shown on the body of Dialog.
        $("#dialog-confirm").html(buf);

        // Define the Dialog and its properties.
        $("#dialog-confirm").dialog({
            resizable: false,
            modal: true,
            title: "",
            height: 'auto',
            width: 400,
            buttons: {
                "Edit": function() {
                    $(this).dialog('close');
                    cpm.hms.patientVisit.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id, tooth_part);
                },
                "Delete": function() {
                    $(this).dialog('close');
                    cpm.hms.patientVisit.DeleteFromPerioTable(tooth_id, patient_visit_id, tooth_form_type, labs_id, tooth_part);
                },
                "Close": function() {
                    $(this).dialog('close');
                }
            }
        });
    },

    reloadInvoicePortal: function(order_id, patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=InvoicePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitInvoicePortal').html(html);

            var invoice_count    = $('#fld_invoice_count').val();
            if(invoice_count == 0){
                $('#billSummaryOrder').after("<a href='#' id='createOrderRecord' patient_visit_id="+patient_visit_id+" class='btn btn-info'>Generate Bill</a>");
                $('#billSummaryOrder').remove();
            }

            Util.hideProgressInd();
        });
    },

    reloadInvestigationTabPortal: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=medicalPortalDisplay&showHTML=0';
        $.get(url,{patient_visit_id:patient_visit_id}, function(html){
            $('#medicalDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadReceiptPortal: function(order_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=ReceiptPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitReceiptPortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadTreatmentTabPortal: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
        $.get(url,{patient_visit_id:patient_visit_id}, function(html){
            $('.treatmentTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadDiagnosisTabPortal: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=DiagnosisPortalDisplay&showHTML=0';
        $.get(url,{patient_visit_id:patient_visit_id}, function(html){
            $('.diagnosisTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    openTextAreaForImageSelectedArea: function(patient_visit_id, title){
        var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=showNotesForImageMapping&showHTML=0'+'&patient_visit_id='+ patient_visit_id+'&title='+ title;

        var title = "Add Notes";
        var expObj = {
            url: url
           ,validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Saved Successfully!';
                Util.alert(msg, function(){
                    $(".ui-dialog-content").dialog("close");
                    cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
                });
            }
        }
        Util.openFormInDialog.call('', 'portalFormTextAreaAddNotesOnImageMapping', title, 500, 300, expObj);
    },

    editTextAreaForImageSelectedArea: function(patient_visit_image_mapping_id, patient_visit_id){
        var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=editNotesForImageMapping&showHTML=0'+'&patient_visit_id='+ patient_visit_id+'&patient_visit_image_mapping_id='+ patient_visit_image_mapping_id;

        var title = "Edit Notes";
        var expObj = {
            url: url
           ,validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Updated Successfully!';
                Util.alert(msg, function(){
                    $(".ui-dialog-content").dialog("close");
                    cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
                });
            }
        }
        Util.openFormInDialog.call('', 'portalFormTextAreaEditNotesOnImageMapping', title, 500, 300, expObj);
    },

    deleteTextAreaForImageSelectedArea: function(patient_visit_image_mapping_id, patient_visit_id){
        var msg = "Are you sure to delete this item?";
        if (confirm(msg)){
            var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=deleteNotesForImageMapping&showHTML=0'+'&patient_visit_id='+ patient_visit_id+'&patient_visit_image_mapping_id='+ patient_visit_image_mapping_id;

            $.get(url, {patient_visit_image_mapping_id: patient_visit_image_mapping_id, patient_visit_id: patient_visit_id}, function(json){
                var msg = 'Deleted Successfully!';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
                });
            });
        }
    },

    openDBhasValueSelectedArea: function(patient_visit_image_mapping_id, patient_visit_id) {
        var buf = "Are you sure want to?";
        // buf will be shown on the body of Dialog.
        $("#dialog-confirm").html(buf);

        // Define the Dialog and its properties.
        $("#dialog-confirm").dialog({
            resizable: false,
            modal: true,
            title: "",
            height: 'auto',
            width: 260,
            buttons: {
                "View/Edit": function() {
                    $(this).dialog('close');
                    cpm.hms.patientVisit.editTextAreaForImageSelectedArea(patient_visit_image_mapping_id, patient_visit_id);
                },
                "Delete": function() {
                    $(this).dialog('close');
                    cpm.hms.patientVisit.deleteTextAreaForImageSelectedArea(patient_visit_image_mapping_id, patient_visit_id);
                },
                "Close": function() {
                    $(this).dialog('close');
                }
            }
        });
    },

    reloadImageMappingTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=imageMappingDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            Util.closeAllDialogs();
            $('#imageMapping').html(html);
            Util.hideProgressInd();
        });
    },

    //Auto select complain details
    complainName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_patientVisit&_spAction=searchComplainDetails&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    if (data.length == 0) {
                        response("");
                    }
                    else {
                      response(data);
                    }

                  }});
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj            = ui.item;
                var complain_id = selectedObj.id
                var title       = selectedObj.complain_Name
                var patient_visit_id = $('#record_id').val();
                var url = 'index.php?module=hms_patientVisit&_spAction=addComplain&showHTML=0';
                Util.showProgressInd();
                $.get(url, {complain_id: complain_id, patient_visit_id: patient_visit_id, title: title}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.patientVisit.reloadChiefComplains(patient_visit_id);
                });
                
            }
        });
    },

    //Auto select Procedure details
    procedureName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_patientVisit&_spAction=searchProcedureDetails&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    if (data.length == 0) {
                        response("");
                    }
                    else {
                      response(data);
                    }

                  }});
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj     = ui.item;
                var visit_procedure       = selectedObj.visit_procedure
                var patient_visit_id = $('#record_id').val();
                var url = 'index.php?module=hms_patientVisit&_spAction=addProcedure&showHTML=0';
                Util.showProgressInd();
                $.get(url, {patient_visit_id: patient_visit_id, visit_procedure: visit_procedure}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.patientVisit.reloadProcedure(patient_visit_id);
                });
                
            }
        });
    },

    //Auto select diagnosis details
    diagnosisName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_patientVisit&_spAction=searchDiagnosisDetails&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    if (data.length == 0) {
                        response("");
                    }
                    else {
                      response(data);
                    }

                  }});
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj            = ui.item;
                var prescription_id = selectedObj.id
                var disease_name    = selectedObj.disease_name
                var patient_visit_id = $('#record_id').val();

                var url = 'index.php?module=hms_patientVisit&_spAction=addDiagnosis&showHTML=0';
                Util.showProgressInd();
                
                $.get(url, {prescription_id: prescription_id, patient_visit_id: patient_visit_id, disease_name: disease_name}, function(html){
                    var urlMedicine = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=addPrescribeMedicineFormSubmit&showHTML=0';
                    $.get(urlMedicine, {prescription_id: prescription_id, patient_visit_id:patient_visit_id}, function(html){
                        //Util.closeAllDialogs();
                        Util.hideProgressInd();
                        cpm.hms.patientVisit.reloadSummaryPortal(patient_visit_id);
                        cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                        //Util.alert('Medicine added Successfully');
                    });
                });
                
            }
        });
    },
    
    //Auto select patient details
    patientName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_patientVisit&_spAction=searchPatientDetails&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    // No matching result
                    if (data.length == 0) {
                        //$('input[name=patient_information_id]').val('');
                        /*$('input[name=father_name]').val('');
                        $('input[name=spuse_name]').val('');
                        $('input[name=address_area]').val('');
                        $('input[name=age]').val('');
                        $('select[name=gender]').val('Male');
                        $('input[name=phone]').val('');*/
                        response("");
                    }

                    else {
                      response(data);
                    }

                  }});
            },
            minLength : 1,
            selectFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj            = ui.item;
                var patient_information_id = selectedObj.id
                var father_name            = selectedObj.father_name
                var husband_name           = selectedObj.husband_name
                var city_town              = selectedObj.city_town
                var phone                  = selectedObj.phone
                var age_year               = selectedObj.age_year
                var age_month              = selectedObj.age_month
                var age_day                = selectedObj.age_day
                var gender                 = selectedObj.gender
                var address_street         = selectedObj.address_street
                var uhid                   = selectedObj.uhid
                var height                 = selectedObj.height
                var weight                 = selectedObj.weight
                var bmi                    = selectedObj.bmi

                $('input[name=patient_information_id]').val(patient_information_id);
                $('input[name=father_name]').val(father_name);
                $('input[name=spuse_name]').val(husband_name);
                $('input[name=address_area]').val(city_town);
                $('input[name=age_year]').val(age_year);
                $('input[name=age_month]').val(age_month);
                $('input[name=age_day]').val(age_day);
                $('select[name=gender]').val(gender);
                $('input[name=phone]').val(phone);
                $('input[name=address_street]').val(address_street);
                $('input[name=uhid]').val(uhid);
                $('input[name=height]').val(height);
                $('input[name=weight]').val(weight);
                $('input[name=bmi]').val(bmi);
            }
        });
    },

    //Auto select patient details
    patientPhone: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_patientVisit&_spAction=searchPatientDetailsWithPhone&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    // No matching result
                    if (data.length == 0) {
                        //$('input[name=patient_information_id]').val('');
                        /*$('input[name=patient_name]').val('');
                        $('input[name=father_name]').val('');
                        $('input[name=spuse_name]').val('');
                        $('input[name=address_area]').val('');
                        $('input[name=age]').val('');
                        $('select[name=gender]').val('Male');*/
                        response("");
                    }

                    else {
                      response(data);
                    }

                  }});
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj            = ui.item;
                var patient_information_id = selectedObj.id
                var father_name            = selectedObj.father_name
                var husband_name           = selectedObj.husband_name
                var city_town              = selectedObj.city_town
                var age_year               = selectedObj.age_year
                var age_month              = selectedObj.age_month
                var age_day                = selectedObj.age_day
                var gender                 = selectedObj.gender
                var patient_name           = selectedObj.patient_name
                var address_street         = selectedObj.address_street
                var uhid                   = selectedObj.uhid
                
                $('input[name=patient_information_id]').val(patient_information_id);
                $('input[name=father_name]').val(father_name);
                $('input[name=spuse_name]').val(husband_name);
                $('input[name=address_area]').val(city_town);
                $('input[name=age_year]').val(age_year);
                $('input[name=age_month]').val(age_month);
                $('input[name=age_day]').val(age_day);
                $('select[name=gender]').val(gender);
                $('input[name=patient_name]').val(patient_name);
                $('input[name=address_street]').val(address_street);
                $('input[name=uhid]').val(uhid);
            }
        });
    },

    //Auto select patient details
    patientAddress: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_patientVisit&_spAction=searchPatientDetailsWithAddress&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    // No matching result
                    if (data.length == 0) {
                        /*$('input[name=patient_name]').val('');
                        $('input[name=father_name]').val('');
                        $('input[name=spuse_name]').val('');
                        $('input[name=phone]').val('');
                        $('input[name=age]').val('');
                        $('select[name=gender]').val('Male');*/
                        response("");
                    }

                    else {
                      response(data);
                    }

                  }});
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj            = ui.item;
                var patient_information_id = selectedObj.id            
            }
        });
    },

    //Auto select patient details
    patientUhid: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_patientVisit&_spAction=searchPatientDetailsWithUhid&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    // No matching result
                    if (data.length == 0) {
                        //$('input[name=patient_information_id]').val('');
                        /*$('input[name=patient_name]').val('');
                        $('input[name=father_name]').val('');
                        $('input[name=spuse_name]').val('');
                        $('input[name=address_area]').val('');
                        $('input[name=age]').val('');
                        $('select[name=gender]').val('Male');*/
                        response("");
                    }

                    else {
                      response(data);
                    }

                  }});
            },
            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj            = ui.item;
                var patient_information_id = selectedObj.id
                var father_name            = selectedObj.father_name
                var husband_name           = selectedObj.husband_name
                var city_town              = selectedObj.city_town
                var age_year               = selectedObj.age_year
                var age_month              = selectedObj.age_month
                var age_day                = selectedObj.age_day
                var gender                 = selectedObj.gender
                var patient_name           = selectedObj.patient_name
                var address_street         = selectedObj.address_street
                var phone                  = selectedObj.phone
                
                $('input[name=patient_information_id]').val(patient_information_id);
                $('input[name=father_name]').val(father_name);
                $('input[name=spuse_name]').val(husband_name);
                $('input[name=address_area]').val(city_town);
                $('input[name=age_year]').val(age_year);
                $('input[name=age_month]').val(age_month);
                $('input[name=age_day]').val(age_day);
                $('select[name=gender]').val(gender);
                $('input[name=phone]').val(phone);
                $('input[name=patient_name]').val(patient_name);
                $('input[name=address_street]').val(address_street);
            }
        });
    },

    //Auto select patient details
    clearSearchValues: function() {
        $('input[name=patient_information_id]').val('');
        $('input[name=patient_name]').val('');
        $('input[name=father_name]').val('');
        $('input[name=spuse_name]').val('');
        $('input[name=address_area]').val('');
        $('input[name=age_year]').val('');
        $('input[name=age_month]').val('');
        $('input[name=age_day]').val('');
        $('select[name=gender]').val('Male');
        $('input[name=phone]').val('');
        $('input[name=address_street]').val('');
        $('input[name=uhid]').val('');
    },

    //Sort the inverstigation with selected order
    sortInvestigationGroups: function() {
        var count = 0;
        $("#sortableInvesticationCategories").sortable({
            stop: function( event, ui ) {
                var valuelistId = [];
                $(".investigationCategoryLink").each(function() {
                    var href = $(this).prop('href');
                    var valuelist_id = $(this).attr('valuelist_id');
                    var name = $(this).text();
                    count++;
                    valuelistId.push(valuelist_id);
                });

                Util.showProgressInd();
                var url = 'index.php?module=hms_patientVisit&_spAction=investigationsCategorySortOrderUpdate&showHTML=0';
                $.get(url, {valuelistId: valuelistId}, function(html){
                    var patient_visit_id = $('#record_id').val();
                    cpm.hms.patientVisit.reloadInvestigationTabPortal(patient_visit_id);
                });
            }
        }).disableSelection();
    },
}

cpm.hms.patientVisit.createPatientVisit = function(){
    var url = 'index.php?module=hms_patientVisit&_spAction=createVisitRecordDirect&showHTML=0';
    var dr_required            = $(this).attr('dr_required');
    var patient_information_id = $(this).attr('patient_information_id');
    var appointment_id         = $(this).attr('appointment_id');

    if(dr_required == ''){
        cpm.hms.patientVisit.createPatientVisitDetails(patient_information_id, appointment_id);
    }else{
        $.get(url,{patient_information_id:patient_information_id, dr_required:dr_required, appointment_id:appointment_id}, function(html){
            Util.closeAllDialogs();
            cpm.hms.patientVisit.reloadSearchResult();
            cpm.hms.patientVisit.reloadQueueno();
            var mgsalert='Patient Visit Record Created';
            var n = noty({
                text: mgsalert,
                type: 'confirm',
                dismissQueue: true,
                layout: 'topCenter',
                theme: 'defaultTheme',
                timeout: 5000,
            });
        });
    }
}

cpm.hms.patientVisit.addPatientRecord = function(e){
    var title = "Create Patient Record";
    var patient_visit_id   = $(this).attr('patient_visit_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Record created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
                //window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 1100, 500, expObj);
}

cpm.hms.patientVisit.addPortalSaveRecord = function(){
    $.post($(this).attr('action'), $(this).serialize(), function(response){
        // do something here on success
        var mgsalert='Record Saved Successfully';
        var n = noty({
            text: mgsalert,
            type: 'confirm',
            dismissQueue: true,
            layout: 'topCenter',
            theme: 'defaultTheme',
            timeout: 2000,
        });
    },'json');
    return false;
}

var Actions = {
    addNew: function () {
        $('#frmNew').submit();
    },

    cancelNew: function () {
        msg = "Are you sure you want to cancel?";
        if (confirm (msg)){
            history.back();
        }
    },

    cancelEdit: function () {
        msg = "Are you sure you want to cancel? \n\nYou will lose any changes made";
        if (confirm (msg)){
            history.back();
        }
    },
    
    save: function (room) {
        opts = {
            progressDlgType: 1
        }
        Util.setUpAjaxFormGeneral('frmEdit', null, null, opts);
        $('#frmEdit').submit();
    },

    apply: function (room){
        $('form#frmEdit input[name=apply]').val(1);

        Util.setUpAjaxFormGeneral('frmEdit', function(){
            //document.location = document.location;
            Util.hideProgressInd();
        });
        $('#frmEdit').submit();
    },
}