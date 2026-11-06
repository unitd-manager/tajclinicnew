Util.createCPObject('cpm.hms.labTest');

cpm.hms.labTest = {
    init: function(){
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

        $(".row-hms_labTest__hms_product input[name='days']").live("keydown", function (e) {
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
            var lab_test_id   = $(this).attr('lab_test_id');
            var url = 'index.php?module=hms_labTest&_spAction=updateMedicalVisitParameter&showHTML=0';
            $.get(url, {medical_test_parameter_id: medical_test_parameter_id, medical_test_id: medical_test_id, notes: notes, lab_test_id:lab_test_id}, function(json){

            });
        });

        $(".medTestMainSubmit").livequery("click", function (e) {            
            e.preventDefault();
            var lab_test_id   = $(this).attr('lab_test_id');
            cpm.hms.labTest.reloadInvestigationTabPortal(lab_test_id);
            alert('Saved Succesfully')
        });

        $(".medParaSubmit").livequery("click", function (e) {            
            e.preventDefault();
            alert('Saved Succesfully')
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
        $('.m-hms_labTest #addMedicines')
        .livequery('click', cpm.hms.labTest.patientMedicineAdd);

        $(".m-hms_labTest input[name='product_title']")
        .livequery(cpm.hms.labTest.patientProductTitle);

        $('.m-hms_labTest .instruction').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var instructionObj = $(this).parents('tr').find('select[name=instruction]');
            var instruction = instructionObj.val();
            var url = 'index.php?module=hms_labTest&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, instruction: instruction, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_labTest .route').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var routeObj = $(this).parents('tr').find('select[name=route]');
            var route = routeObj.val();
            var url = 'index.php?module=hms_labTest&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, route: route, product_id: product_id}, function(json){

            });
        });
        
          $('.m-hms_labTest .printLabel').livequery('click', function (e){
            var print_label = $(this).val();
            var is_checked  = $(this).is(':checked');
            var lab_test_id = $(this).attr('lab_test_id');
            var medical_test_id = $(this).attr('medical_test_id');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_labTest&_spAction=medicalTestRecordPrintUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {print_label:print_label, lab_test_id:lab_test_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).show();
                    parent.addClass('bgColorHighlight');
                });
            } else {
                var url = 'index.php?module=hms_labTest&_spAction=medicalTestRecordPrintUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {print_label:print_label, lab_test_id:lab_test_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                  
                });
            }
        });

        $('.m-hms_labTest .dosage').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var dosageObj = $(this).parents('tr').find('input[name=dosage]');
            var dosage = dosageObj.val();
            var url = 'index.php?module=hms_labTest&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, dosage: dosage, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_labTest .days').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var daysObj = $(this).parents('tr').find('input[name=days]');
            var days = daysObj.val();
            var url = 'index.php?module=hms_labTest&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, days: days, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_labTest .qty > input').livequery('change', function(){
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
                var url = 'index.php?module=hms_labTest&_spAction=updateProductLineItems&showHTML=0';
                $.get(url, {rec_id: rec_id, qty: qty, product_id: product_id}, function(json){

                });
            //}

        });

        $('.m-hms_labTest .selling-price').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var priceObj = $(this).parents('tr').find('input[name=selling_price]');
            var selling_price = priceObj.val();
            var url = 'index.php?module=hms_labTest&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, selling_price: selling_price}, function(json){

            });
        });

        $('.m-hms_labTest .employee_id').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var employeeObj = $(this).parents('tr').find('select[name=employee_id]');
            var employee_id = employeeObj.val();
            var url = 'index.php?module=hms_labTest&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, employee_id: employee_id}, function(json){

            });
        });

        $('.m-hms_labTest #addDoctorRecord').livequery('click', function (e){
            var title = "Create Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadDoctorTab(lab_test_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_labTest #addTreatmentRecord').livequery('click', function (e){
            var title = "Create Record";
            var lab_test_id = $('#record_id').val();
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    var treatment_category  = $("select[name='category']").val();
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        var url = 'index.php?module=hms_labTest&_spAction=TreatmentPortalDisplay&showHTML=0';
                        $.get(url,{lab_test_id:lab_test_id, TreatmentCategory: treatment_category}, function(html){
                            $('.treatmentTabDisplay').html(html);
                            Util.hideProgressInd();
                        });
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 340, expObj);
        });

        $('.m-hms_labTest #addDiagnosisRecord').livequery('click', function (e){
            var title = "Create Record";
            var lab_test_id   = $('#record_id').val();
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    var diagnosis_title  = $('#fld_diagnosis_title').val();
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        var url = 'index.php?module=hms_labTest&_spAction=DiagnosisPortalDisplay&showHTML=0';
                        $.get(url,{lab_test_id:lab_test_id, searchDiagnosis: diagnosis_title}, function(html){
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
        $('.m-hms_labTest #addLabsRecord').livequery('click', function (e){
            var title = "Create Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadLabsTab(lab_test_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 450, 250, expObj);
        });


        $("select[name='supplier_category']").livequery('change', function(){
            var url = 'index.php?module=hms_labTest&_spAction=labsSupplierJSON&showHTML=0';
            var supplier_category = $(this).val();
            $.get(url, {supplier_category: supplier_category}, function (data) {
                $("select[name='supplier_id']").cp_loadSelect(data);
            }, 'json');

        });

        /*$("select[name='patientVisitSummary_type']").livequery('change', function(){
            var patientVisitSummary_type = $(this).val();
            var patient_information_id   = $('#fld_patient_information_id').val();
            var url = 'index.php?module=hms_labTest&_spAction=PatientVisitSummaryPortal&showHTML=0';
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
            var url = 'index.php?module=hms_labTest&_spAction=PatientVisitSummaryPortal&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_information_id:patient_information_id, patientVisitSummary_type:patientVisitSummary_type}, function(html){
                $('#patientVisitSummaryPortal').html(html);
                Util.hideProgressInd();
            });
        });


        /* Add Patient Record*/
        $('.m-hms_labTest #addPatientRecord').livequery('click', cpm.hms.labTest.addPatientRecord);

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

                var url = 'index.php?module=hms_labTest&_spAction=CompanyNameJSON&showHTML=0';
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
        /*$('.m-hms_labTest #addMedicines').livequery('click', function (e){
            var title = "Create Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadMedicineTab(lab_test_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });*/

        /* Add note in treatment tab*/
        /*$('.m-hms_labTest a.addNoteTreatment').livequery('click', function (e){
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
        /*$('.m-hms_labTest a.addNoteLab').livequery('click', function (e){
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

        $('.m-hms_labTest #addLabRecord').livequery('click', function (e){
            var title = "Create Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadLabTab(lab_test_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_labTest .perio_chart_link').livequery('click', function (e){
            var title = "Perio Chart";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var url = "index.php?module=hms_labTest&_spAction=perioChartForm&lab_test_id="+lab_test_id+"&showHTML=0";
            var exp = {
                url: url
                ,afterOpen: function(){

                }
            };
            Util.openDialogForLink('Perio Chart', '965px', 'auto', 0, exp);

        });

        $('.m-hms_labTest #editDoctorRecord').livequery('click', function (e){
            var title = "Edit Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadDoctorTab(lab_test_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteDoctorRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_labTest&_spAction=deleteDoctorRecord&showHTML=0';
            var employee_visit_id = $(this).attr('employee_visit_id');
            var lab_test_id   = $(this).attr('lab_test_id');
            $.get(url,  {employee_visit_id:employee_visit_id}, function(html){
                cpm.hms.labTest.reloadDoctorTab(lab_test_id);
            });
        });

        $('.m-hms_labTest #editLabsRecord').livequery('click', function (e){
            var title = "Edit Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadLabsTab(lab_test_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'EditLabsRecordportalForm', title, 450, 250, expObj);
        });

        $('.deleteLabsRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_labTest&_spAction=deleteLabsRecord&showHTML=0';
            var labs_id = $(this).attr('labs_id');
            var lab_test_id   = $(this).attr('lab_test_id');
            $.get(url,  {labs_id:labs_id}, function(html){
                cpm.hms.labTest.reloadLabsTab(lab_test_id);
            });
        });

        $('.m-hms_labTest #acrylicFormDenture').livequery('click', function (e){
            var title = "DENTURE EXPRESS";
            e.preventDefault();
            var lab_test_id   = $(this).attr('lab_test_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadLabsTab(lab_test_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'acrylicDentureForm', title, 810, 508, expObj);
        });

        $('.m-hms_labTest #addCeramicForm').livequery('click', function (e){
            var title = "CERAMIC FORM";
            e.preventDefault();
            var lab_test_id   = $(this).attr('lab_test_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadLabsTab(lab_test_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'AddCeramicFormDetail', title, 810, 508, expObj);
        });

        $('.m-hms_labTest #addOrthodontic').livequery('click', function (e){
            var title = "ORTHODONTIC FORM";
            e.preventDefault();
            var lab_test_id   = $(this).attr('lab_test_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadLabsTab(lab_test_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'ChromeFormDetail', title, 810, 508, expObj);
        });

        $('.m-hms_labTest #editMedicineRecord').livequery('click', function (e){
            var title = "Edit Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadMedicineTab(lab_test_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteMedicineRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_labTest&_spAction=deleteMedicineRecord&showHTML=0';
            var medicines_visit_id = $(this).attr('medicines_visit_id');
            var lab_test_id   = $(this).attr('lab_test_id');
            $.get(url,  {medicines_visit_id:medicines_visit_id}, function(html){
                cpm.hms.labTest.reloadMedicineTab(lab_test_id);
            });
        });

        $('.m-hms_labTest #editLabRecord').livequery('click', function (e){
            var title = "Edit Record";
            var lab_test_id   = $(this).attr('lab_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadLabTab(lab_test_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_labTest .treatment_id').livequery('click', function (e){
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

        $('.m-hms_labTest .diagnosis_id').livequery('click', function (e){
            var treatment_id = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                $(this).closest('.diagnosisBox').addClass('checkedCheckBoxTreatment');
            } else {
                $(this).closest('.diagnosisBox').removeClass('checkedCheckBoxTreatment');
            }
        });

        $('.m-hms_labTest .addNoteTreatment').livequery('click', function (e){
            var parent = $(this).closest('.treatmentNotes');
            $('.hideNotes', parent).slideToggle();
        });

        $('.m-hms_labTest .addNoteLab').livequery('click', function (e){
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
                var lab_test_id = $(this).attr('lab_test_id');
                var lab_test_id_main = $(this).attr('lab_test_id_main');

                var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=applyMedicine&showHTML=0' +
                        '&lab_test_id=' + lab_test_id + '&lab_test_id_main=' + lab_test_id_main;
                $.get(url, {lab_test_id: lab_test_id, lab_test_id_main: lab_test_id_main}, function (html) {
                    cpm.hms.labTest.reloadMedicineTab(lab_test_id);
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
                var lab_test_id = $(this).attr('lab_test_id');

                var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=createAdmission&showHTML=0' +
                        '&lab_test_id=' + lab_test_id;
                $.get(url, {lab_test_id: lab_test_id}, function (html) {
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
                var lab_test_id = $(this).attr('lab_test_id');

                var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=createOrder&showHTML=0' +
                        '&lab_test_id=' + lab_test_id;
                $.get(url, {lab_test_id: lab_test_id}, function (html) {
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
                var receipt_amount = $('.m-hms_labTest input[name="due_receipt_amount"]').val();
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
                var receipt_amount = $('.m-hms_labTest input[name="due_receipt_amount"]').val();
                overAllTotalAmount_due = Number(parseInt(due_amount) + totalAmount);
                overAllTotalAmount = Number(parseInt(due_amount) + totalAmount - parseInt(receipt_amount));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount_due.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));

                $(".invoice_total_amount").val(totalAmount.toFixed(2));
                $("#overall_Total_invoice_hidden").val(totalAmount.toFixed(2));

            }
        });

        $('.m-hms_labTest #createOrderRecord').livequery('click', function (e){
            e.preventDefault();
            var lab_test_id = $(this).attr('lab_test_id');
            var order_id = $('input[name=order_id]').val();

            var urlOrder = 'index.php?_topRm=main&module=hms_labTest&_spAction=createOrder&showHTML=0' +
                           '&lab_test_id='+ lab_test_id;
            $.get(urlOrder, {lab_test_id: lab_test_id}, function (html) {
                $('#fld_order_id').val(html);
                url = "index.php?module=hms_order&_spAction=generateInvoiceForm&order_id="+html+"&receipt=1&showHTML=0";
                var title = "Bill Generation";
                e.preventDefault();

                var expObj = {
                    url: url
                   ,validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Invoice & Receipt created successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            /*var billsummaryUrl = "index.php?module=hms_labTest&_spAction=summaryInOrder&order_id="+order_id+"&showHTML=0";
                            $('#createOrderRecord').after("<div class='billSummaryOrder float_left'><a class='btn btn-primary' href='"+billsummaryUrl+"' id='billSummaryOrder' order_id='"+order_id+"'>Bill Summary</a></div>");
                            $('#createOrderRecord').remove();
                            cpm.hms.labTest.reloadReceiptPortal(html);
                            cpm.hms.labTest.reloadInvoicePortal(html, lab_test_id);
                            $("select[name=status]").val('Closed');*/
                            window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call('', 'portalForm', title, 400, 325, expObj);
            });
        });

        $('.cancelInvoice').live('click', function (e){
            var invoice_status = $(this).attr('invoice_status');
            var order_id = $('#fld_order_id').val();
            var lab_test_id = $('#record_id').val();
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
                            //cpm.hms.labTest.reloadInvoicePortal(order_id, lab_test_id);
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
            var lab_test_id = $('#record_id').val();
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
                    cpm.hms.labTest.reloadReceiptPortal(order_id);
                    cpm.hms.labTest.reloadInvoicePortal(order_id, lab_test_id);
                    //window.location.reload(true);
                });
            }
        });

        $('.m-hms_labTest #generateReceipt').livequery('click', function (e){
            var title = "Create Receipt";
            e.preventDefault();
            var order_id = $('#fld_order_id').val();
            var lab_test_id = $('#record_id').val();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labTest.reloadReceiptPortal(order_id);
                        cpm.hms.labTest.reloadInvoicePortal(order_id, lab_test_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
        });

        $('.m-hms_labTest input.invoiceCode').livequery('click', function (e){
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

        $('.m-hms_labTest input.dueInvoiceCode').livequery('click', function (e){
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

        $('.m-hms_labTest input[name="due_receipt_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var receipt_amount = $(this).val();
            var due_amount = $('.m-hms_labTest input[name="due_amount"]').val();
            var checked    = $('.m-hms_labTest input.dueInvoiceCode').attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;
            if(checkedVal == 1){
                var totalVal = $('input[id=fld_overall_Total_invoice]').val();
            }else{
                var totalVal = $('input[id=overall_Total_invoice_hidden]').val();
            }

            overAllTotalAmount = Number(parseInt(totalVal) - parseInt(receipt_amount));
            $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_labTest input[name="due_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var due_amount = $(this).val();
            var totalVal = $('#overall_Total_invoice_hidden').val();
            overAllTotalAmount = Number(parseInt(totalVal) + parseInt(due_amount));
            $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_labTest #billSummaryOrder').livequery('click', function (e){
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

        $('.m-hms_labTest .labTitle').livequery('click', function (e){
            var title = $(this).val();
            var is_checked  = $(this).is(':checked');
            var lab_test_id = $(this).attr('lab_test_id');
            var medical_test_id = $(this).attr('medical_test_id');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_labTest&_spAction=medicalTestRecordSubmit&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, lab_test_id:lab_test_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).show();
                    parent.addClass('bgColorHighlight');
                });
            } else {
                var url = 'index.php?module=hms_labTest&_spAction=medicalTestRecordDelete&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, lab_test_id:lab_test_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).hide();
                    parent.removeClass('bgColorHighlight');
                });
            }
        });

        $('.m-hms_labTest .otherLabs').livequery('click', function (e){
            var lab_supplier_fees = $(this).val();
            var is_checked  = $(this).is(':checked');
            var lab_test_id = $(this).attr('lab_test_id');
            var medical_test_id = $(this).attr('medical_test_id');
            var supplier_id = $(this).attr('supplier_id');
            var container = $(this).attr('container');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_labTest&_spAction=medicalTestRecordUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {lab_test_id:lab_test_id, container:container,  lab_supplier_fees:lab_supplier_fees, supplier_id:supplier_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabSupplierFees', parent).show();
                });
            } else {
                var lab_supplier_fees = 0;
                var supplier_id = '';
                var container = '';
                var url = 'index.php?module=hms_labTest&_spAction=medicalTestRecordUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {lab_test_id: lab_test_id, container:container, lab_supplier_fees:lab_supplier_fees, supplier_id:supplier_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabSupplierFees', parent).hide();
                });
            }
        });

        $('.labFees').livequery('change', function(){
            var fees = $(this).val();
            var medical_test_id   = $(this).attr('medical_test_id');
            var lab_test_id   = $(this).attr('lab_test_id');

            var url = 'index.php?module=hms_labTest&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, fees: fees, lab_test_id:lab_test_id}, function(json){

            });
        });

        $('#portalForm_medicalTestDisplay .fld_date').livequery('change', function(){
            var investigation_date = $(this).val();
            var parent = $(this).closest('div');
            var medicalTestIdObj = $(this).parents('div').find('input[name=medical_test_id]');
            var medical_test_id = medicalTestIdObj.val();
            var lab_test_idObj = $(this).parents('div').find('input[name=lab_test_id]');
            var lab_test_id = lab_test_idObj.val();

            var url = 'index.php?module=hms_labTest&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, investigation_date: investigation_date, lab_test_id:lab_test_id}, function(json){

            });
        });

        $('.labNotes').livequery('change', function(){
            var notes = $(this).val();
            var medical_test_id   = $(this).attr('medical_test_id');
            var lab_test_id   = $(this).attr('lab_test_id');

            var url = 'index.php?module=hms_labTest&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, notes: notes, lab_test_id:lab_test_id}, function(json){

            });
        });

        $('.m-hms_labTest .searchPatientButton').livequery('click', function (e){
           var inputBoxVaue  = $('input[name=patient_name]').val();
           var dropdownValue = $('#fld_search_type_by_list').val();
           var lock = 1;
           var url = 'index.php?module=hms_labTest&_spAction=patientVisitSearchResult&showHTML=0';
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

        $('a.createVisit').livequery('click', cpm.hms.labTest.createPatientVisit);

        $('input[name="selected_tooth[]"]').live('click', function (e){
            var tooth_id  = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                cpm.hms.labTest.SelectSymbolsForm();
            }
        });

        $('input[name="selected_tooth2[]"]').live('click', function (e){
            var tooth_id         = $(this).val();
            var is_checked       = $(this).is(':checked');
            var lab_test_id = $('#lab_test_id').val();
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
                        var url  = 'index.php?module=hms_labTest&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    for (i = parseInt(prevTooth_count); i >= parseInt(checboxid); i--) {
                        $("#selected_tooth2_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth2_"+i).val();
                        var url  = 'index.php?module=hms_labTest&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    cpm.hms.labTest.reloadToothList2(lab_test_id, tooth_form_type, labs_id);
                    cpm.hms.labTest.reloadToothList3(lab_test_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.labTest.SelectSymbolsForm(checboxid, tooth_id, lab_test_id, tooth_part);
                }
            }
        });

        $('input[name="selected_tooth3[]"]').live('click', function (e){
            var tooth_id         = $(this).val();
            var is_checked       = $(this).is(':checked');
            var lab_test_id = $('#lab_test_id').val();
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
                        var url  = 'index.php?module=hms_labTest&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }


                    for (i = parseInt(prevTooth_count); i >= parseInt(checboxid); i--) {
                        $("#selected_tooth3_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth3_"+i).val();
                        var url  = 'index.php?module=hms_labTest&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    cpm.hms.labTest.reloadToothList3(lab_test_id, tooth_form_type, labs_id);
                    cpm.hms.labTest.reloadToothList2(lab_test_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.labTest.SelectSymbolsForm(checboxid, tooth_id, lab_test_id, tooth_part);
                }
            }
        });

        $('input[name="selected_Symbols[]"]').live('click', function (e){
            var symbol_name      = $(this).val();
            var is_checked       = $(this).is(':checked');
            var tooth_id         = $('#tooth_id').val();
            var prevcount        = $('#Checkbox_ID').val();
            var lab_test_id = $('#lab_test_id').val();
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
                        var url  = 'index.php?module=hms_labTest&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
                            $('#dialog1').dialog('close');
                            Util.hideProgressInd();
                        });

                        cpm.hms.labTest.reloadToothList2(lab_test_id, tooth_form_type, labs_id);
                        cpm.hms.labTest.reloadToothList3(lab_test_id, tooth_form_type, labs_id);
                    }
                }
                else{
                    Util.showProgressInd();
                    var url  = 'index.php?module=hms_labTest&_spAction=addPerioChartRecord&showHTML=0';
                    $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
                        $('#dialog1').dialog('close');
                        Util.hideProgressInd();
                    });

                    cpm.hms.labTest.reloadToothList2(lab_test_id, tooth_form_type, labs_id);
                    cpm.hms.labTest.reloadToothList3(lab_test_id, tooth_form_type, labs_id);
                }
            }
        });

        $('select[name=employee_id]').livequery('change', function(){
            var employee_id = $(this).val();

            var url = 'index.php?module=hms_labTest&_spAction=updateConsultingFees&showHTML=0';
            $.get(url, {employee_id: employee_id}, function(html){
                $('#fld_consultation_fees').val(html);
            });
        });

        $('.followUpDate select').livequery('change', function(){
            var follow_up_date = $(this).val();
            var parent = $(this).closest('.treatmentNotes');

            var url = 'index.php?module=hms_labTest&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('.followUpDate input', parent).val(html);
            });
        });

        $('select[name=follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_labTest&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_follow_up_date').val(html);
            });
        });

        $('select[name=longtime_follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_labTest&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_longtime_follow_up_date').val(html);
            });
        });

        $('.m-hms_labTest .selectedToothSymbolEdit').livequery('click', function (e){
            var tooth_id         = $(this).attr('tooth_id');
            var tooth_part       = $(this).attr('tooth_part');
            var lab_test_id = $(this).attr('lab_test_id');
            var checboxid        = $(this).attr('Checkbox_ID');
            cpm.hms.labTest.fnOpenButtonTextChangedDialog(checboxid, tooth_id, lab_test_id, tooth_part);
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

        /*$('.m-hms_labTest .TreatmentSubmit').livequery('click', function (e){
            var url  = 'index.php?module=hms_labTest&_spAction=treatmentRecordSubmit&showHTML=0';
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
                var lab_test_id = $('#record_id').val();
                cpm.hms.labTest.reloadTreatmentTabPortal(lab_test_id);
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
                var lab_test_id = $('#record_id').val();
                cpm.hms.labTest.reloadInvestigationTabPortal(lab_test_id);
          },'json');
          return false;
       });*/

        $('#portalForm_subjectiveAssessmentDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_objectiveAssessmentDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_problemAnalysisDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_goalSmartDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_goalSmartDisplayForTreatmentTab').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_medicalTestDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_vitalSignsDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_complainDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('#portalForm_procedureDisplay').livequery('submit', cpm.hms.labTest.addPortalSaveRecord);
        $('.medicineSave').livequery('click', function(){
            alert('Medicine saved successfully')
       });


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
                var lab_test_id = $('#record_id').val();
                cpm.hms.labTest.reloadDiagnosisTabPortal(lab_test_id);
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
            var lab_test_id = $(this).attr('lab_test_id');
            if (!confirm(msg)){
                return false;
            }
            else {
                Util.showProgressInd();
                var url = 'index.php?module=hms_labTest&_spAction=cancelPatientVisitRecord&showHTML=0';
                $.get(url,{lab_test_id: lab_test_id}, function(html){
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
            var lab_test_id = $('#record_id').val();

            Util.showProgressInd();
            var url = 'index.php?module=hms_labTest&_spAction=TreatmentPortalDisplay&showHTML=0';
            $.get(url,{lab_test_id:lab_test_id, TreatmentCategory: TreatmentCategory}, function(html){
                $('.treatmentTabDisplay').html(html);
                Util.hideProgressInd();
            });
        });


        var timeoutId2;
        $(".treatmentSearchAuto").livequery("keyup", function (){
            clearTimeout(timeoutId2);
            var searchTreatment = $(this).val();
            var lab_test_id = $('#record_id').val();

            timeoutId2 = setTimeout(function() {
                var url = 'index.php?module=hms_labTest&_spAction=TreatmentPortalDisplay&showHTML=0';
                $.get(url,{lab_test_id:lab_test_id, searchTreatment: searchTreatment}, function(html){
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
            var lab_test_id = $('#record_id').val();

            timeoutId3 = setTimeout(function() {
                var url = 'index.php?module=hms_labTest&_spAction=DiagnosisPortalDisplay&showHTML=0';
                $.get(url,{lab_test_id:lab_test_id, searchDiagnosis:searchDiagnosis}, function(html){
                    $('.diagnosisTabDisplay').html(html);
                    Util.hideProgressInd();
                    $('.diagnosisSearchAuto').val(searchDiagnosis);
                });
            }, 1000);
        });

        $(".dlg-portalFormTextAreaAddNotesOnImageMapping button.btn-cancel").live('click', function (e){
            var lab_test_id = $('#record_id').val();
            cpm.hms.labTest.reloadImageMappingTab(lab_test_id);
        });

        $(".dlg-portalFormTextAreaEditNotesOnImageMapping button.btn-cancel").live('click', function (e){
            var lab_test_id = $('#record_id').val();
            cpm.hms.labTest.reloadImageMappingTab(lab_test_id);
        });

        $(".dlg-portalFormTextAreaAddNotesOnImageMapping .ui-dialog-titlebar-close ").livequery("click", function (e){
            var lab_test_id = $('#record_id').val();
            cpm.hms.labTest.reloadImageMappingTab(lab_test_id);
        });

        $(".dlg-portalFormTextAreaEditNotesOnImageMapping .ui-dialog-titlebar-close ").livequery("click", function (e){
            var lab_test_id = $('#record_id').val();
            cpm.hms.labTest.reloadImageMappingTab(lab_test_id);
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
                
                var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=prescribeMedicineFormSubmit&showHTML=0';
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
                
                var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=deleteMedicineVisit&showHTML=0';
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
            Util.openDialogForLink.call(this, 'Overall Summary', 800, 400, expObj);
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

        /*$('input[name=disease_name]').livequery('click', function(){
            var parent = $(this).closest('tr');
            var disease_name = $(this).attr('disease_name');
            //var prescription_id = $(this).attr('prescription_id');
            var lab_test_id = $(this).attr('lab_test_id');
            if(lab_test_id != ''){
                var url = 'index.php?module=hms_labTest&_spAction=addComplain';
                Util.showProgressInd();
                
                $.get(url, {lab_test_id: lab_test_id, disease_name: disease_name}, function(html){
                    Util.closeAllDialogs();
                });
                Util.hideProgressInd();
                window.location.reload(true);
            }
            
        });*/

        $("input[name='complain_title']")
        .livequery(cpm.hms.labTest.complainName);

        $("input[name='procedure_title']")
        .livequery(cpm.hms.labTest.procedureName);

        $("input[name='diagnosis_title']")
        .livequery(cpm.hms.labTest.diagnosisName);

        $("input[name='patient_name']")
        .livequery(cpm.hms.labTest.patientName);

        $("input[name='phone']")
        .livequery(cpm.hms.labTest.patientPhone);

        $("input[name='address_area']")
        .livequery(cpm.hms.labTest.patientAddress);

        $(".createPatientVisitSearchButton").livequery("click", function (e){
            var patient_information_id = $('input[name=patient_information_id]').val();
            var lab_test_id       = $('#record_id').val();
            var patient_name           = $('input[name=patient_name]').val();
            var father_name            = $('input[name=father_name]').val();
            var husband_name           = $('input[name=spuse_name]').val();
            var address_area           = $('input[name=address_area]').val();
            var phone                  = $('input[name=phone]').val();
            var age_year               = $('input[name=age_year]').val();
            var age_month              = $('input[name=age_month]').val();
            var age_day                = $('input[name=age_day]').val();
            var gender                 = $('select[name=gender]').val();
            var referral_doctor_id     = $('select[name=referral_doctor_id]').val();

            if(patient_name == ""){
                Util.alert("Please Select/Enter Patient Details To Create Visit!");
            }
            else{                
                var url = 'index.php?module=hms_labTest&_spAction=createVisitRecordSubmit&showHTML=0';

                $.get(url, {patient_information_id: patient_information_id, patient_name: patient_name, age_year:age_year, age_month:age_month, age_day:age_day, gender:gender, referral_doctor_id:referral_doctor_id, father_name:father_name, husband_name:husband_name, address_area:address_area, phone:phone}, function(html){
                    Util.hideProgressInd();
                    window.location.reload(true);
                });            
            }
        });

        /*$('input[name=disease_name]').livequery('click', function(){
            var prescription_id  = $(this).attr('prescription_id');
            var lab_test_id = $(this).attr('lab_test_id');
            var patient_information_id = $(this).attr('patient_information_id');
            var disease_name     = $(this).attr('disease_name');
            var is_checked       = $(this).is(':checked');
            
            if(is_checked == true){
                var urlDisease = 'index.php?module=hms_labTest&_spAction=addDiagnosis&showHTML=0';
                Util.showProgressInd();
                
                $.get(urlDisease, {prescription_id: prescription_id, patient_information_id: patient_information_id, lab_test_id: lab_test_id, disease_name: disease_name}, function(html){
                    var urlMedicine = 'index.php?_topRm=main&module=hms_labTest&_spAction=addPrescribeMedicineFormSubmit&showHTML=0';
                    $.get(urlMedicine, {prescription_id: prescription_id, lab_test_id:lab_test_id}, function(html){
                        //Util.closeAllDialogs();
                        Util.hideProgressInd();
                        cpm.hms.labTest.reloadSummaryPortal(lab_test_id);
                        cpm.hms.labTest.reloadMedicineTab(lab_test_id);
                        //Util.alert('Medicine added Successfully');
                    });
                });
            }else{
                var urlDiseaseRemove = 'index.php?module=hms_labTest&_spAction=removeDiagnosis&showHTML=0';
                Util.showProgressInd();
                
                $.get(urlDiseaseRemove, {prescription_id: prescription_id, patient_information_id: patient_information_id, lab_test_id: lab_test_id, disease_name: disease_name}, function(html){
                    var urlMedicineRemove = 'index.php?_topRm=main&module=hms_labTest&_spAction=RemovePrescribeMedicine&showHTML=0';
                    $.get(urlMedicineRemove, {prescription_id: prescription_id, lab_test_id:lab_test_id}, function(html){
                        //Util.closeAllDialogs();
                        Util.hideProgressInd();
                        cpm.hms.labTest.reloadSummaryPortal(lab_test_id);
                        cpm.hms.labTest.reloadMedicineTab(lab_test_id);
                        //Util.alert('Medicine added Successfully');
                    });
                });
            }
            
        });*/

        $('.diagnosisSave').livequery('click', function(){
            var disease_name = $('input[name=diagnosis_title]').val();
            var lab_test_id = $('#record_id').val();

            var urlDisease = 'index.php?module=hms_labTest&_spAction=addDiagnosis&showHTML=0';
            Util.showProgressInd();
            
            $.get(urlDisease, {lab_test_id: lab_test_id, disease_name: disease_name}, function(html){
                Util.hideProgressInd();
                cpm.hms.labTest.reloadSummaryPortal(lab_test_id);
            });            
        });

        $('.newProductTitle').livequery('click', function(){
            var title = $('input[name=product_title_new]').val();
            var lab_test_id = $('#record_id').val();

            if(title == ''){
                alert('Please enter the new medicine');
            }
            else{
                var url = 'index.php?module=hms_labTest&_spAction=addProductAndLineItem&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title: title, lab_test_id: lab_test_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.labTest.reloadMedicineTab(lab_test_id);
                });
            }
        });

        /*$('input[name=complain_title]').livequery('click', function(){
            var complain_id  = $(this).attr('complain_id');
            var lab_test_id = $(this).attr('lab_test_id');
            var patient_information_id = $(this).attr('patient_information_id');
            var title     = $(this).attr('title');
            var is_checked       = $(this).is(':checked');

            if(is_checked == true){
                var urlDisease = 'index.php?module=hms_labTest&_spAction=addComplain&showHTML=0';
                Util.showProgressInd();
                $.get(urlDisease, {complain_id: complain_id, patient_information_id: patient_information_id, lab_test_id: lab_test_id, title: title}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.labTest.reloadChiefComplains(lab_test_id);
                });
            }else{
                var urlDiseaseRemove = 'index.php?module=hms_labTest&_spAction=removeComplain&showHTML=0';
                Util.showProgressInd();
                $.get(urlDiseaseRemove, {complain_id: complain_id, patient_information_id: patient_information_id, lab_test_id: lab_test_id, title: title}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.labTest.reloadChiefComplains(lab_test_id);
                });
            }
            
        });*/

        $('.complainSave').livequery('click', function(){
            //var patient_information_id = $(this).attr('patient_information_id');
            var title = $('input[name=complain_title]').val();
            var lab_test_id = $('#record_id').val();

            var url = 'index.php?module=hms_labTest&_spAction=addComplain&showHTML=0';
            Util.showProgressInd();
            $.get(url, {lab_test_id: lab_test_id, title: title}, function(html){
                Util.hideProgressInd();
                cpm.hms.labTest.reloadChiefComplains(lab_test_id);
            });
            
        });

        $('.procedureSave').livequery('click', function(){
            //var patient_information_id = $(this).attr('patient_information_id');
            var visit_procedure = $('input[name=procedure_title]').val();
            var lab_test_id = $('#record_id').val();

            var url = 'index.php?module=hms_labTest&_spAction=addProcedure&showHTML=0';
            Util.showProgressInd();
            $.get(url, {lab_test_id: lab_test_id, visit_procedure: visit_procedure}, function(html){
                Util.hideProgressInd();
                cpm.hms.labTest.reloadProcedure(lab_test_id);
            });
            
        });

        $('.clearSearchValuesButton').livequery('click', function(){
            cpm.hms.labTest.clearSearchValues();
        });

    },


    reloadDoctorTab: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=doctorPortalDisplay&showHTML=0';
        $.get(url, {lab_test_id: lab_test_id}, function(html){
            $('#doctorDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadSummaryPortal: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=summaryPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {lab_test_id:lab_test_id}, function(html){
            $('#summaryPortalDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadChiefComplains: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=chiefComplainsDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {lab_test_id:lab_test_id}, function(html){
            $('#chiefComplains').html(html);
            Util.hideProgressInd();
        });
    },

    reloadProcedure: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=procedurePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {lab_test_id:lab_test_id}, function(html){
            $('#procedurePortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabsTab: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=LabsDisplay&showHTML=0';
        $.get(url, {lab_test_id: lab_test_id}, function(html){
            $('#labsDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadMedicineTab: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=medicinesPortalDisplay&showHTML=0';
        $.get(url, {lab_test_id: lab_test_id}, function(html){
            $('#medicinesDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabTab: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=labPortalDisplay&showHTML=0';
        $.get(url, {lab_test_id: lab_test_id}, function(html){
            $('#labDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadToothList2: function(lab_test_id, tooth_form_type, labs_id){
        var url = 'index.php?module=hms_labTest&_spAction=toothlistFirst&showHTML=0';
        $.get(url, {lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
            $('.toothSelectCheckbox2').html(html);
        });
    },

    reloadToothList3: function(lab_test_id, tooth_form_type, labs_id){
        var url = 'index.php?module=hms_labTest&_spAction=toothlistSecond&showHTML=0';
        $.get(url, {lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
            $('.toothSelectCheckbox3').html(html);
        });
    },

    patientMedicineAdd: function(e) {
        e.preventDefault();
        var lab_test_id = $(this).attr('lab_test_id');
        Util.showProgressInd();
        var url = 'index.php?module=hms_labTest&_spAction=addMedicine&lab_test_id=' + lab_test_id + '&showHTML=0';
        $.get(url, {lab_test_id: lab_test_id}, function(){
            cpm.hms.labTest.reloadMedicineTab(lab_test_id);
            Util.hideProgressInd();
        });
    },

    patientProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_labTest&_spAction=searchProductTitle&showHTML=0'
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
            
                var lab_test_id   = $(this).attr('lab_test_id');

                var url = 'index.php?module=hms_labTest&_spAction=createProductLineItems&showHTML=0';
                $.get(url, {product_id: product_id, lab_test_id: lab_test_id}, function(json){
                    cpm.hms.labTest.reloadMedicineTab(lab_test_id);
                    alert('Medicine Added Successfully');
                    $(".addExistingMedicine input[name='product_title']").focus();
                });
            }
        });
    },

    createPatientVisitDetails: function(patient_information_id, appointment_id, patient_name, age_year, age_month, age_day, gender, father_name, husband_name, address_area, phone){
        var title = "Choose Doctor/Nurse";
        var url   = "index.php?module=hms_labTest&_spAction=selectDoctorDetails&patient_information_id="+patient_information_id
                    +"&appointment_id="+appointment_id
                    +"&patient_name="+patient_name
                    +"&father_name="+father_name
                    +"&husband_name="+husband_name
                    +"&address_area="+address_area
                    +"&phone="+phone
                    +"&age_year="+age_year
                    +"&age_month="+age_month
                    +"&age_day="+age_day
                    +"&gender="+gender
                    +"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(html){
                Util.closeAllDialogs();

                cpm.hms.labTest.reloadSearchResult();
                lab_test_id = html.returnUrl;
                var printUrl = "index.php?_topRm=main&module=hms_labTest&_spAction=printTokenForVisit&patient_information_id="+patient_information_id+"&lab_test_id="+lab_test_id+"&showHTML=0";
                window.open(printUrl,'_blank');

                //cpm.hms.labTest.reloadQueueno();
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

    SelectSymbolsForm: function(checboxid, tooth_id, lab_test_id, tooth_part){
        var url = "index.php?module=hms_labTest&_spAction=perioChartSymbols&tooth_id="+tooth_id+"&checboxid="+checboxid+"&tooth_part="+tooth_part+"&showHTML=0";

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
        var url = 'index.php?module=hms_labTest&_spAction=patientVisitAppointmentSearchResult&showHTML=0';

        $.get(url, {inputBoxVaue: inputBoxVaue, dropdownValue:dropdownValue}, function(html){
            $('input[name=patient_information_id]').val('');
            $('input[name=patient_name]').val('');
            $('input[name=father_name]').val('');
            $('input[name=spuse_name]').val('');
            $('input[name=address_area]').val('');
            $('input[name=age]').val('');
            $('select[name=gender]').val('Male');
            $('input[name=phone]').val('');
            //$('input[name=weight]').val('');
            //$('input[name=temperature]').val('');
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

    DeleteFromPerioTable: function(tooth_id, lab_test_id, tooth_form_type, labs_id, tooth_part){
        var url = 'index.php?module=hms_labTest&_spAction=deletePerioChartRecord&showHTML=0';
        $.get(url, {tooth_id:tooth_id, lab_test_id:lab_test_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
            cpm.hms.labTest.reloadToothList2(lab_test_id, tooth_form_type, labs_id);
            cpm.hms.labTest.reloadToothList3(lab_test_id, tooth_form_type, labs_id);
        });
    },

    fnOpenButtonTextChangedDialog: function(checboxid, tooth_id, lab_test_id, tooth_part) {
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
                    cpm.hms.labTest.SelectSymbolsForm(checboxid, tooth_id, lab_test_id, tooth_part);
                },
                "Delete": function() {
                    $(this).dialog('close');
                    cpm.hms.labTest.DeleteFromPerioTable(tooth_id, lab_test_id, tooth_form_type, labs_id, tooth_part);
                },
                "Close": function() {
                    $(this).dialog('close');
                }
            }
        });
    },

    reloadInvoicePortal: function(order_id, lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=InvoicePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitInvoicePortal').html(html);

            var invoice_count    = $('#fld_invoice_count').val();
            if(invoice_count == 0){
                $('#billSummaryOrder').after("<a href='#' id='createOrderRecord' lab_test_id="+lab_test_id+" class='btn btn-info'>Generate Bill</a>");
                $('#billSummaryOrder').remove();
            }

            Util.hideProgressInd();
        });
    },

    reloadInvestigationTabPortal: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=medicalPortalDisplay&showHTML=0';
        $.get(url,{lab_test_id:lab_test_id}, function(html){
            $('#medicalDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadReceiptPortal: function(order_id){
        var url = 'index.php?module=hms_labTest&_spAction=ReceiptPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitReceiptPortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadTreatmentTabPortal: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=TreatmentPortalDisplay&showHTML=0';
        $.get(url,{lab_test_id:lab_test_id}, function(html){
            $('.treatmentTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadDiagnosisTabPortal: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=DiagnosisPortalDisplay&showHTML=0';
        $.get(url,{lab_test_id:lab_test_id}, function(html){
            $('.diagnosisTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    openTextAreaForImageSelectedArea: function(lab_test_id, title){
        var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=showNotesForImageMapping&showHTML=0'+'&lab_test_id='+ lab_test_id+'&title='+ title;

        var title = "Add Notes";
        var expObj = {
            url: url
           ,validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Saved Successfully!';
                Util.alert(msg, function(){
                    $(".ui-dialog-content").dialog("close");
                    cpm.hms.labTest.reloadImageMappingTab(lab_test_id);
                });
            }
        }
        Util.openFormInDialog.call('', 'portalFormTextAreaAddNotesOnImageMapping', title, 500, 300, expObj);
    },

    editTextAreaForImageSelectedArea: function(lab_test_image_mapping_id, lab_test_id){
        var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=editNotesForImageMapping&showHTML=0'+'&lab_test_id='+ lab_test_id+'&lab_test_image_mapping_id='+ lab_test_image_mapping_id;

        var title = "Edit Notes";
        var expObj = {
            url: url
           ,validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Updated Successfully!';
                Util.alert(msg, function(){
                    $(".ui-dialog-content").dialog("close");
                    cpm.hms.labTest.reloadImageMappingTab(lab_test_id);
                });
            }
        }
        Util.openFormInDialog.call('', 'portalFormTextAreaEditNotesOnImageMapping', title, 500, 300, expObj);
    },

    deleteTextAreaForImageSelectedArea: function(lab_test_image_mapping_id, lab_test_id){
        var msg = "Are you sure to delete this item?";
        if (confirm(msg)){
            var url = 'index.php?_topRm=main&module=hms_labTest&_spAction=deleteNotesForImageMapping&showHTML=0'+'&lab_test_id='+ lab_test_id+'&lab_test_image_mapping_id='+ lab_test_image_mapping_id;

            $.get(url, {lab_test_image_mapping_id: lab_test_image_mapping_id, lab_test_id: lab_test_id}, function(json){
                var msg = 'Deleted Successfully!';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    cpm.hms.labTest.reloadImageMappingTab(lab_test_id);
                });
            });
        }
    },

    openDBhasValueSelectedArea: function(lab_test_image_mapping_id, lab_test_id) {
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
                    cpm.hms.labTest.editTextAreaForImageSelectedArea(lab_test_image_mapping_id, lab_test_id);
                },
                "Delete": function() {
                    $(this).dialog('close');
                    cpm.hms.labTest.deleteTextAreaForImageSelectedArea(lab_test_image_mapping_id, lab_test_id);
                },
                "Close": function() {
                    $(this).dialog('close');
                }
            }
        });
    },

    reloadImageMappingTab: function(lab_test_id){
        var url = 'index.php?module=hms_labTest&_spAction=imageMappingDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {lab_test_id: lab_test_id}, function(html){
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
                  url: 'index.php?module=hms_labTest&_spAction=searchComplainDetails&showHTML=0',
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
                var lab_test_id = $('#record_id').val();
                var url = 'index.php?module=hms_labTest&_spAction=addComplain&showHTML=0';
                Util.showProgressInd();
                $.get(url, {complain_id: complain_id, lab_test_id: lab_test_id, title: title}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.labTest.reloadChiefComplains(lab_test_id);
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
                  url: 'index.php?module=hms_labTest&_spAction=searchProcedureDetails&showHTML=0',
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
                var lab_test_id = $('#record_id').val();
                var url = 'index.php?module=hms_labTest&_spAction=addProcedure&showHTML=0';
                Util.showProgressInd();
                $.get(url, {lab_test_id: lab_test_id, visit_procedure: visit_procedure}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.labTest.reloadProcedure(lab_test_id);
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
                  url: 'index.php?module=hms_labTest&_spAction=searchDiagnosisDetails&showHTML=0',
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
                var lab_test_id = $('#record_id').val();

                var url = 'index.php?module=hms_labTest&_spAction=addDiagnosis&showHTML=0';
                Util.showProgressInd();
                
                $.get(url, {prescription_id: prescription_id, lab_test_id: lab_test_id, disease_name: disease_name}, function(html){
                    var urlMedicine = 'index.php?_topRm=main&module=hms_labTest&_spAction=addPrescribeMedicineFormSubmit&showHTML=0';
                    $.get(urlMedicine, {prescription_id: prescription_id, lab_test_id:lab_test_id}, function(html){
                        //Util.closeAllDialogs();
                        Util.hideProgressInd();
                        cpm.hms.labTest.reloadSummaryPortal(lab_test_id);
                        cpm.hms.labTest.reloadMedicineTab(lab_test_id);
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
                  url: 'index.php?module=hms_labTest&_spAction=searchPatientDetails&showHTML=0',
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
            electFirst: true,
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
                
                $('input[name=patient_information_id]').val(patient_information_id);
                $('input[name=father_name]').val(father_name);
                $('input[name=spuse_name]').val(husband_name);
                $('input[name=address_area]').val(city_town);
                $('input[name=age_year]').val(age_year);
                $('input[name=age_month]').val(age_month);
                $('input[name=age_day]').val(age_day);
                $('select[name=gender]').val(gender);
                $('input[name=phone]').val(phone);
            }
        });
    },

    //Auto select patient details
    patientPhone: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_labTest&_spAction=searchPatientDetailsWithPhone&showHTML=0',
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
                
                $('input[name=patient_information_id]').val(patient_information_id);
                $('input[name=father_name]').val(father_name);
                $('input[name=spuse_name]').val(husband_name);
                $('input[name=address_area]').val(city_town);
                $('input[name=age_year]').val(age_year);
                $('input[name=age_month]').val(age_month);
                $('input[name=age_day]').val(age_day);
                $('select[name=gender]').val(gender);
                $('input[name=patient_name]').val(patient_name);
            }
        });
    },

    //Auto select patient details
    patientAddress: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_labTest&_spAction=searchPatientDetailsWithAddress&showHTML=0',
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
                    var lab_test_id = $('#record_id').val();
                    cpm.hms.labTest.reloadInvestigationTabPortal(lab_test_id);
                });
            }
        }).disableSelection();
    },
}

cpm.hms.labTest.createPatientVisit = function(){
    var url = 'index.php?module=hms_labTest&_spAction=createVisitRecordDirect&showHTML=0';
    var patient_information_id = $(this).attr('patient_information_id');
    var appointment_id         = $(this).attr('appointment_id');

    $.get(url,{patient_information_id:patient_information_id, dr_required:dr_required, appointment_id:appointment_id}, function(html){
        Util.closeAllDialogs();
        cpm.hms.labTest.reloadSearchResult();
        cpm.hms.labTest.reloadQueueno();
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

cpm.hms.labTest.addPatientRecord = function(e){
    var title = "Create Patient Record";
    var lab_test_id   = $(this).attr('lab_test_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Record created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //cpm.hms.labTest.reloadDoctorTab(lab_test_id);
                //window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 1100, 500, expObj);
}

cpm.hms.labTest.addPortalSaveRecord = function(){
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