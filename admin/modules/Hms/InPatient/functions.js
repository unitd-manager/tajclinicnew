Util.createCPObject('cpm.hms.inPatient');

cpm.hms.inPatient = {
    init: function(){
        //initialize tabs
        $('#tabs').tabs();

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });

        $(".row-hms_inPatient__hms_product input[name='days']").live("keydown", function (e) {
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
            var in_patient_id   = $(this).attr('in_patient_id');
            var test_repeat   = $(this).attr('test_repeat');

            var url = 'index.php?module=hms_inPatient&_spAction=updateMedicalVisitParameter&showHTML=0';
            $.get(url, {medical_test_parameter_id: medical_test_parameter_id, medical_test_id: medical_test_id, notes: notes, in_patient_id:in_patient_id, test_repeat:test_repeat}, function(json){

            });
        });

        $(".medParaSubmit").livequery("click", function (e) {            
            e.preventDefault();
            alert('Saved Succesfully')
        });

        $(".medTestMainSubmit").livequery("click", function (e) {            
            e.preventDefault();
            var in_patient_id   = $(this).attr('in_patient_id');
            cpm.hms.inPatient.reloadInvestigationTabPortal(in_patient_id);
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


        /* Add Medicine in patient visit medicines tab */
        $('.m-hms_inPatient #addMedicines')
        .livequery('click', cpm.hms.inPatient.patientMedicineAdd);

        $(".m-hms_inPatient input[name='product_title']")
        .livequery(cpm.hms.inPatient.patientProductTitle);

        $('.m-hms_inPatient .instruction').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var instructionObj = $(this).parents('tr').find('select[name=instruction]');
            var instruction = instructionObj.val();
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, instruction: instruction, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_inPatient .route').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var routeObj = $(this).parents('tr').find('select[name=route]');
            var route = routeObj.val();
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, route: route, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_inPatient .dosage').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var dosageObj = $(this).parents('tr').find('input[name=dosage]');
            var dosage = dosageObj.val();
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, dosage: dosage, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_inPatient .days').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var daysObj = $(this).parents('tr').find('input[name=days]');
            var days = daysObj.val();
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, days: days, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_inPatient .qty > input').livequery('change', function(){
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
                var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
                $.get(url, {rec_id: rec_id, qty: qty, product_id: product_id}, function(json){

                });
            //}

        });

        $('.m-hms_inPatient .selling-price').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var priceObj = $(this).parents('tr').find('input[name=selling_price]');
            var selling_price = priceObj.val();
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, selling_price: selling_price}, function(json){

            });
        });

        $('.m-hms_inPatient .employee_id').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var employeeObj = $(this).parents('tr').find('select[name=employee_id]');
            var employee_id = employeeObj.val();
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, employee_id: employee_id}, function(json){

            });
        });

        $('.m-hms_inPatient input[name=continue_medicine]').livequery('click', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var continueMedicineObj = $(this).parents('tr').find('input[name=continue_medicine]').is(':checked');
            var continue_medicine = 0;
            if(continueMedicineObj == true){
                continue_medicine = 1;
            }
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, continue_medicine: continue_medicine, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_inPatient input[name=treatment_given]').livequery('click', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var continueMedicineObj = $(this).parents('tr').find('input[name=treatment_given]').is(':checked');
            var treatment_given = 0;
            if(continueMedicineObj == true){
                treatment_given = 1;
            }
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, treatment_given: treatment_given, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_inPatient input[name=prescription]').livequery('click', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var continueMedicineObj = $(this).parents('tr').find('input[name=prescription]').is(':checked');
            var prescription = 0;
            if(continueMedicineObj == true){
                prescription = 1;
            }
            var url = 'index.php?module=hms_inPatient&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, prescription: prescription, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_inPatient #addDoctorRecord').livequery('click', function (e){
            var title = "Create Record";
            var in_patient_id   = $(this).attr('in_patient_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.inPatient.reloadDoctorTab(in_patient_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_inPatient #addSpecialistRecord').livequery('click', function (e){
            var title = "Create Record";
            var in_patient_id   = $(this).attr('in_patient_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.inPatient.reloadSpecialistTab(in_patient_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_inPatient #editSpecialistRecord').livequery('click', function (e){
            var title = "Edit Record";
            var in_patient_id   = $(this).attr('in_patient_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record updated successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.inPatient.reloadSpecialistTab(in_patient_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteSpecialistRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_inPatient&_spAction=deleteSpecialistRecord&showHTML=0';
            var employee_in_patient_id = $(this).attr('employee_in_patient_id');
            var in_patient_id   = $(this).attr('in_patient_id');
            $.get(url,  {employee_in_patient_id:employee_in_patient_id}, function(html){
                cpm.hms.inPatient.reloadSpecialistTab(in_patient_id);
            });
        });

        $('.m-hms_inPatient #addDiagnosisRecord').livequery('click', function (e){
            var title = "Create Record";
            var in_patient_id   = $('#record_id').val();
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    var diagnosis_title  = $('#fld_diagnosis_title').val();
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        var url = 'index.php?module=hms_inPatient&_spAction=DiagnosisPortalDisplay&showHTML=0';
                        $.get(url,{in_patient_id:in_patient_id, searchDiagnosis: diagnosis_title}, function(html){
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
        $('.m-hms_inPatient #addLabsRecord').livequery('click', function (e){
            var title = "Create Record";
            var in_patient_id   = $(this).attr('in_patient_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.inPatient.reloadLabsTab(in_patient_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 450, 250, expObj);
        });


        $("select[name='supplier_category']").livequery('change', function(){
            var url = 'index.php?module=hms_inPatient&_spAction=labsSupplierJSON&showHTML=0';
            var supplier_category = $(this).val();
            $.get(url, {supplier_category: supplier_category}, function (data) {
                $("select[name='supplier_id']").cp_loadSelect(data);
            }, 'json');

        });

        $(".testAgain").livequery('click', function(){
            msg = "Do you like to test again?";

            if (!confirm(msg)){
                return false;
            }
            else{
                var url = 'index.php?module=hms_inPatient&_spAction=medicalTestRecordAgainSubmit&showHTML=0';
                var parent = $(this).closest('.medTestTitle');
                var medical_test_id  = $(this).attr('medical_test_id');
                var in_patient_id  = $(this).attr('in_patient_id');
                $.get(url, {medical_test_id:medical_test_id, in_patient_id:in_patient_id}, function (html) {                
                    cpm.hms.inPatient.reloadInvestigationTabPortal(in_patient_id);

                });
            }
        });

        /*$("select[name='inPatientSummary_type']").livequery('change', function(){
            var inPatientSummary_type = $(this).val();
            var patient_information_id   = $('#fld_patient_information_id').val();
            var url = 'index.php?module=hms_inPatient&_spAction=inPatientSummaryPortal&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_information_id:patient_information_id, inPatientSummary_type:inPatientSummary_type}, function(html){
                $('#inPatientSummaryPortal').html(html);
                Util.hideProgressInd();
            });
        });*/

        $(".inPatientSummary_type").livequery('click', function(){
            var link_text = $(this).html();

            if(link_text == 'Display payment due records'){
                var inPatientSummary_type = 'Due';
                $(".inPatientSummary_type").html('Show All Records');
            }else if(link_text == 'Show All Records'){
                var inPatientSummary_type = 'All';
                $(".inPatientSummary_type").html('Display payment due records');
            }

            var patient_information_id   = $('#fld_patient_information_id').val();
            var url = 'index.php?module=hms_inPatient&_spAction=inPatientSummaryPortal&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_information_id:patient_information_id, inPatientSummary_type:inPatientSummary_type}, function(html){
                $('#inPatientSummaryPortal').html(html);
                Util.hideProgressInd();
            });
        });


        /* Add Patient Record*/
        $('.m-hms_inPatient #addPatientRecord').livequery('click', cpm.hms.inPatient.addPatientRecord);

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

        $('.m-hms_inPatient #editDoctorRecord').livequery('click', function (e){
            var title = "Edit Record";
            var in_patient_id   = $(this).attr('in_patient_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.inPatient.reloadDoctorTab(in_patient_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteDoctorRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_inPatient&_spAction=deleteDoctorRecord&showHTML=0';
            var employee_in_patient_id = $(this).attr('employee_in_patient_id');
            var in_patient_id   = $(this).attr('in_patient_id');
            $.get(url,  {employee_in_patient_id:employee_in_patient_id}, function(html){
                cpm.hms.inPatient.reloadDoctorTab(in_patient_id);
            });
        });

        $('.m-hms_inPatient #editMedicineRecord').livequery('click', function (e){
            var title = "Edit Record";
            var in_patient_id   = $(this).attr('in_patient_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.inPatient.reloadMedicineTab(in_patient_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteMedicineRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_inPatient&_spAction=deleteMedicineRecord&showHTML=0';
            var medicines_in_patient_id = $(this).attr('medicines_in_patient_id');
            var in_patient_id   = $(this).attr('in_patient_id');
            $.get(url,  {medicines_in_patient_id:medicines_in_patient_id}, function(html){
                cpm.hms.inPatient.reloadMedicineTab(in_patient_id);
            });
        });

        $('.m-hms_inPatient .addNoteTreatment').livequery('click', function (e){
            var parent = $(this).closest('.treatmentNotes');
            $('.hideNotes', parent).slideToggle();
        });

        $('.m-hms_inPatient .addNoteLab').livequery('click', function (e){
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
                var in_patient_id = $(this).attr('in_patient_id');

                var url = 'index.php?_topRm=main&module=hms_inPatient&_spAction=applyMedicine&showHTML=0' +
                        '&patient_visit_id=' + patient_visit_id + '&in_patient_id=' + in_patient_id;
                $.get(url, {patient_visit_id: patient_visit_id, in_patient_id: in_patient_id}, function (html) {
                    cpm.hms.inPatient.reloadMedicineTab(in_patient_id);
                    Util.hideProgressInd();
                    alert ('Medicines applied succesfully');
                });
            }
        });

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
                var receipt_amount = $('.m-hms_inPatient input[name="due_receipt_amount"]').val();
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
                var receipt_amount = $('.m-hms_inPatient input[name="due_receipt_amount"]').val();
                overAllTotalAmount_due = Number(parseInt(due_amount) + totalAmount);
                overAllTotalAmount = Number(parseInt(due_amount) + totalAmount - parseInt(receipt_amount));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount_due.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));

                $(".invoice_total_amount").val(totalAmount.toFixed(2));
                $("#overall_Total_invoice_hidden").val(totalAmount.toFixed(2));

            }
        });

        $('.m-hms_inPatient #createOrderRecord').livequery('click', function (e){
            e.preventDefault();
            var in_patient_id = $(this).attr('in_patient_id');
            var order_id = $('input[name=order_id]').val();

            var urlOrder = 'index.php?_topRm=main&module=hms_inPatient&_spAction=createOrder&showHTML=0' +
                           '&in_patient_id='+ in_patient_id;
            $.get(urlOrder, {in_patient_id: in_patient_id}, function (html) {
                $('#fld_order_id').val(html);
                url = "index.php?module=hms_order&_spAction=generateInvoiceFormInpatient&order_id="+html+"&receipt=1&showHTML=0";
                var title = "Bill Generation";
                e.preventDefault();

                var expObj = {
                    url: url
                   ,validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Invoice created successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            /*var billsummaryUrl = "index.php?module=hms_inPatient&_spAction=summaryInOrder&order_id="+order_id+"&showHTML=0";
                            $('#createOrderRecord').after("<div class='billSummaryOrder float_left'><a class='btn btn-primary' href='"+billsummaryUrl+"' id='billSummaryOrder' order_id='"+order_id+"'>Bill Summary</a></div>");
                            $('#createOrderRecord').remove();
                            cpm.hms.inPatient.reloadReceiptPortal(html);
                            cpm.hms.inPatient.reloadInvoicePortal(html, in_patient_id);
                            $("select[name=status]").val('Closed');*/
                            window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call('', 'portalForm', title, 400, 385, expObj);
            });
        });

        $('.m-hms_inPatient #updateOrderRecord').livequery('click', function (e){
            e.preventDefault();
            var in_patient_id = $(this).attr('in_patient_id');
            var order_id = $('input[name=order_id]').val();

            var urlOrder = 'index.php?_topRm=main&module=hms_inPatient&_spAction=createOrder&showHTML=0' +
                           '&in_patient_id='+ in_patient_id;
            $.get(urlOrder, {in_patient_id: in_patient_id}, function (html) {
                $('#fld_order_id').val(html);
                url = "index.php?module=hms_order&_spAction=generateInvoiceFormInpatient&order_id="+html+"&receipt=1&showHTML=0";
                var title = "Bill Generation";
                e.preventDefault();

                var expObj = {
                    url: url
                   ,validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Invoice created successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            /*var billsummaryUrl = "index.php?module=hms_inPatient&_spAction=summaryInOrder&order_id="+order_id+"&showHTML=0";
                            $('#createOrderRecord').after("<div class='billSummaryOrder float_left'><a class='btn btn-primary' href='"+billsummaryUrl+"' id='billSummaryOrder' order_id='"+order_id+"'>Bill Summary</a></div>");
                            $('#createOrderRecord').remove();
                            cpm.hms.inPatient.reloadReceiptPortal(html);
                            cpm.hms.inPatient.reloadInvoicePortal(html, in_patient_id);
                            $("select[name=status]").val('Closed');*/
                            window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call('', 'portalForm', title, 400, 385, expObj);
            });
        });

        $('.cancelInvoice').live('click', function (e){
            var invoice_status = $(this).attr('invoice_status');
            var order_id = $('#fld_order_id').val();
            var in_patient_id = $('#record_id').val();
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
                            //cpm.hms.inPatient.reloadInvoicePortal(order_id, in_patient_id);
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
            var in_patient_id = $('#record_id').val();
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
                    cpm.hms.inPatient.reloadReceiptPortal(order_id);
                    cpm.hms.inPatient.reloadInvoicePortal(order_id, in_patient_id);
                    //window.location.reload(true);
                });
            }
        });

        $('.m-hms_inPatient #generateReceipt').livequery('click', function (e){
            var title = "Create Receipt";
            e.preventDefault();
            var order_id = $('#fld_order_id').val();
            var in_patient_id = $('#record_id').val();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.inPatient.reloadReceiptPortal(order_id);
                        cpm.hms.inPatient.reloadInvoicePortal(order_id, in_patient_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
        });

        $('.m-hms_inPatient input.invoiceCode').livequery('click', function (e){
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

        $('.m-hms_inPatient input.dueInvoiceCode').livequery('click', function (e){
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

        $('.m-hms_inPatient input[name="due_receipt_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var receipt_amount = $(this).val();
            var due_amount = $('.m-hms_inPatient input[name="due_amount"]').val();
            var checked    = $('.m-hms_inPatient input.dueInvoiceCode').attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;
            if(checkedVal == 1){
                var totalVal = $('input[id=fld_overall_Total_invoice]').val();
            }else{
                var totalVal = $('input[id=overall_Total_invoice_hidden]').val();
            }

            overAllTotalAmount = Number(parseInt(totalVal) - parseInt(receipt_amount));
            $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_inPatient input[name="due_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var due_amount = $(this).val();
            var totalVal = $('#overall_Total_invoice_hidden').val();
            overAllTotalAmount = Number(parseInt(totalVal) + parseInt(due_amount));
            $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_inPatient #billSummaryOrder').livequery('click', function (e){
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

        $('.m-hms_inPatient .labTitle').livequery('click', function (e){
            var title = $(this).val();
            var in_patient_id = $(this).attr('in_patient_id');
            var medical_test_id = $(this).attr('medical_test_id');
            var test_repeat = $(this).attr('test_repeat');
            var is_checked  = $(this).is(':checked');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_inPatient&_spAction=medicalTestRecordAgainSubmit&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, in_patient_id:in_patient_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).show();
                    parent.addClass('bgColorHighlight');
                });
            } else {
                var url = 'index.php?module=hms_inPatient&_spAction=medicalTestRecordDelete&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title:title, in_patient_id:in_patient_id, medical_test_id:medical_test_id, test_repeat:test_repeat}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabDetails', parent).hide();
                    parent.removeClass('bgColorHighlight');
                });
            }
        });

        $('.m-hms_inPatient .otherLabs').livequery('click', function (e){
            var lab_supplier_fees = $(this).val();
            var is_checked  = $(this).is(':checked');
            var in_patient_id = $(this).attr('in_patient_id');
            var medical_test_id = $(this).attr('medical_test_id');
            var supplier_id = $(this).attr('supplier_id');
            var container = $(this).attr('container');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                var url = 'index.php?module=hms_inPatient&_spAction=medicalTestRecordUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {in_patient_id:in_patient_id, container:container,  lab_supplier_fees:lab_supplier_fees, supplier_id:supplier_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabSupplierFees', parent).show();
                });
            } else {
                var lab_supplier_fees = 0;
                var supplier_id = '';
                var container = '';
                var url = 'index.php?module=hms_inPatient&_spAction=medicalTestRecordUpdate&showHTML=0';
                Util.showProgressInd();
                $.get(url, {in_patient_id: in_patient_id, container:container, lab_supplier_fees:lab_supplier_fees, supplier_id:supplier_id, medical_test_id:medical_test_id}, function(html){
                    Util.hideProgressInd();
                    $('.hideLabSupplierFees', parent).hide();
                });
            }
        });

        $('.labFees').livequery('change', function(){
            var fees = $(this).val();
            var medical_test_id   = $(this).attr('medical_test_id');
            var in_patient_id   = $(this).attr('in_patient_id');
            var test_repeat   = $(this).attr('test_repeat');

            var url = 'index.php?module=hms_inPatient&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, fees: fees, in_patient_id:in_patient_id, test_repeat:test_repeat}, function(json){

            });
        });

        $('#portalForm_medicalTestDisplay .fld_date').livequery('change', function(){
            var investigation_date = $(this).val();
            var parent = $(this).closest('div');
            var medicalTestIdObj = $(this).parents('div').find('input[name=medical_test_id]');
            var medical_test_id = medicalTestIdObj.val();
            var in_patient_idObj = $(this).parents('div').find('input[name=in_patient_id]');
            var in_patient_id = in_patient_idObj.val();
            var test_repeatObj = $(this).parents('div').find('input[name=test_repeat]');
            var test_repeat = test_repeatObj.val();

            var url = 'index.php?module=hms_inPatient&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, investigation_date: investigation_date, in_patient_id:in_patient_id, test_repeat:test_repeat}, function(json){

            });
        });

        $('.labNotes').livequery('change', function(){
            var notes = $(this).val();
            var medical_test_id   = $(this).attr('medical_test_id');
            var in_patient_id   = $(this).attr('in_patient_id');
            var test_repeat   = $(this).attr('test_repeat');

            var url = 'index.php?module=hms_inPatient&_spAction=updateMedTestFeesAndNotes&showHTML=0';
            $.get(url, {medical_test_id: medical_test_id, notes: notes, in_patient_id:in_patient_id, test_repeat:test_repeat}, function(json){

            });
        });

        $('.m-hms_inPatient .searchPatientButton').livequery('click', function (e){
           var inputBoxVaue  = $('input[name=patient_name]').val();
           var dropdownValue = $('#fld_search_type_by_list').val();
           var lock = 1;
           var url = 'index.php?module=hms_inPatient&_spAction=inPatientSearchResult&showHTML=0';
           Util.showProgressInd();
           $.get(url, {inputBoxVaue: inputBoxVaue, dropdownValue:dropdownValue, lock:lock}, function(html){
                Util.hideProgressInd();
                $('.searchTableIninPatient').html(html);
                $('.searchTableIninPatient').removeClass('searchTableIninPatienthide');
                $('.searchTableIninPatientAppointment').hide();
                if(inputBoxVaue == ''){
                    $('.searchTableIninPatientAppointment').show();
                    $('.searchTableIninPatient').addClass('searchTableIninPatienthide');
                }
           });

        });

        $('a.createVisit').livequery('click', cpm.hms.inPatient.createinPatient);

        $('select[name=employee_id]').livequery('change', function(){
            var employee_id = $(this).val();

            var url = 'index.php?module=hms_inPatient&_spAction=updateConsultingFees&showHTML=0';
            $.get(url, {employee_id: employee_id}, function(html){
                $('#fld_consultation_fees').val(html);
            });
        });

        $('.followUpDate select').livequery('change', function(){
            var follow_up_date = $(this).val();
            var parent = $(this).closest('.treatmentNotes');

            var url = 'index.php?module=hms_inPatient&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('.followUpDate input', parent).val(html);
            });
        });

        $('select[name=follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_inPatient&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_follow_up_date').val(html);
            });
        });

        $('select[name=longtime_follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_inPatient&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_longtime_follow_up_date').val(html);
            });
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

        /*$('.goToSearchinPatient').livequery('click', function (e){
            $('.searchListDisplay').show();
            $('.defaultListDisplay').hide();
            $('.cpSearch').hide();
        });*/

        $('.displayVisitRecords').livequery('click', function (e){
            $('.searchListDisplay').hide();
            $('.defaultListDisplay').show();
            $('.cpSearch').show();
        });

        /*$('.m-hms_inPatient .TreatmentSubmit').livequery('click', function (e){
            var url  = 'index.php?module=hms_inPatient&_spAction=treatmentRecordSubmit&showHTML=0';
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
                var in_patient_id = $('#record_id').val();
                cpm.hms.inPatient.reloadTreatmentTabPortal(in_patient_id);
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
                var in_patient_id = $('#record_id').val();
                cpm.hms.inPatient.reloadInvestigationTabPortal(in_patient_id);
          },'json');
          return false;
       });*/

        $('#portalForm_medicalTestDisplay').livequery('submit', cpm.hms.inPatient.addPortalSaveRecord);
        $('#portalForm_vitalSignsDisplay').livequery('submit', cpm.hms.inPatient.addPortalSaveRecord);
        $('#portalForm_complainDisplay').livequery('submit', cpm.hms.inPatient.addPortalSaveRecord);
        $('#portalForm_procedureDisplay').livequery('submit', cpm.hms.inPatient.addPortalSaveRecord);
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
                var in_patient_id = $('#record_id').val();
                cpm.hms.inPatient.reloadDiagnosisTabPortal(in_patient_id);
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

        $('form#portalForm_medicalCertificateDisplay').livequery(function() {
            Util.setUpAjaxFormGeneral('portalForm_medicalCertificateDisplay', function(json){
                Util.alert('Medical Certificate updated Successfully');
                Util.hideProgressInd();
            });
        });

       $('.cancelAdmissionRecord').livequery('click', function(e){
            msg = "Please note related receipt,\n\n invoice will also be cancelled,\n\n Do you like to Cancel?";
            var in_patient_id = $(this).attr('in_patient_id');
            if (!confirm(msg)){
                return false;
            }
            else {
                Util.showProgressInd();
                var url = 'index.php?module=hms_inPatient&_spAction=cancelInPatientRecord&showHTML=0';
                $.get(url,{in_patient_id: in_patient_id}, function(html){
                    Util.hideProgressInd();
                    Util.alert('Admission & Related Invoice, Receipt Cancelled Successfully!')
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
            var in_patient_id = $('#record_id').val();

            Util.showProgressInd();
            var url = 'index.php?module=hms_inPatient&_spAction=TreatmentPortalDisplay&showHTML=0';
            $.get(url,{in_patient_id:in_patient_id, TreatmentCategory: TreatmentCategory}, function(html){
                $('.treatmentTabDisplay').html(html);
                Util.hideProgressInd();
            });
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
                
                var url = 'index.php?_topRm=main&module=hms_inPatient&_spAction=prescribeMedicineFormSubmit&showHTML=0';
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
                
                var url = 'index.php?_topRm=main&module=hms_inPatient&_spAction=deleteMedicineVisit&showHTML=0';
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


        $("input[name='complain_title']")
        .livequery(cpm.hms.inPatient.complainName);

        $("input[name='procedure_title']")
        .livequery(cpm.hms.inPatient.procedureName);

        $("input[name='diagnosis_title']")
        .livequery(cpm.hms.inPatient.diagnosisName);

        $("input[name='patient_name']")
        .livequery(cpm.hms.inPatient.patientName);

        $("input[name='phone']")
        .livequery(cpm.hms.inPatient.patientPhone);

        $("input[name='address_area']")
        .livequery(cpm.hms.inPatient.patientAddress);

        $(".createinPatientSearchButton").livequery("click", function (e){
            var patient_information_id = $('input[name=patient_information_id]').val();
            var in_patient_id       = $('#record_id').val();
            var patient_name           = $('input[name=patient_name]').val();
            var father_name            = $('input[name=father_name]').val();
            var husband_name           = $('input[name=spuse_name]').val();
            var address_area           = $('input[name=address_area]').val();
            var phone                  = $('input[name=phone]').val();
            var age_year               = $('input[name=age_year]').val();
            var age_month              = $('input[name=age_month]').val();
            var age_day                = $('input[name=age_day]').val();
            var gender                 = $('select[name=gender]').val();
            var weight                 = $('input[name=weight]').val();
            var temperature            = $('input[name=temperature]').val();

            if(patient_name == ""){
                Util.alert("Please Select/Enter Patient Details To Create Visit!");
            }
            else{                
                cpm.hms.inPatient.createinPatientDetails(patient_information_id, '', patient_name, age_year, age_month, age_day, gender, father_name, husband_name, address_area, phone, weight, temperature);
            }
        });

        $('.diagnosisSave').livequery('click', function(){
            var disease_name = $('input[name=diagnosis_title]').val();
            var in_patient_id = $('#record_id').val();

            var urlDisease = 'index.php?module=hms_inPatient&_spAction=addDiagnosis&showHTML=0';
            Util.showProgressInd();
            
            $.get(urlDisease, {in_patient_id: in_patient_id, disease_name: disease_name}, function(html){
                Util.hideProgressInd();
                cpm.hms.inPatient.reloadSummaryPortal(in_patient_id);
            });            
        });

        $('.newProductTitle').livequery('click', function(){
            var title = $('input[name=product_title_new]').val();
            var in_patient_id = $('#record_id').val();

            if(title == ''){
                alert('Please enter the new medicine');
            }
            else{
                var url = 'index.php?module=hms_inPatient&_spAction=addProductAndLineItem&showHTML=0';
                Util.showProgressInd();
                $.get(url, {title: title, in_patient_id: in_patient_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.inPatient.reloadMedicineTab(in_patient_id);
                });
            }
        });

        $('.complainSave').livequery('click', function(){
            //var patient_information_id = $(this).attr('patient_information_id');
            var title = $('input[name=complain_title]').val();
            var in_patient_id = $('#record_id').val();

            var url = 'index.php?module=hms_inPatient&_spAction=addComplain&showHTML=0';
            Util.showProgressInd();
            $.get(url, {in_patient_id: in_patient_id, title: title}, function(html){
                Util.hideProgressInd();
                cpm.hms.inPatient.reloadChiefComplains(in_patient_id);
            });
            
        });

        $('.procedureSave').livequery('click', function(){
            //var patient_information_id = $(this).attr('patient_information_id');
            var in_patient_procedure = $('input[name=procedure_title]').val();
            var in_patient_id = $('#record_id').val();

            var url = 'index.php?module=hms_inPatient&_spAction=addProcedure&showHTML=0';
            Util.showProgressInd();
            $.get(url, {in_patient_id: in_patient_id, in_patient_procedure: in_patient_procedure}, function(html){
                Util.hideProgressInd();
                cpm.hms.inPatient.reloadProcedure(in_patient_id);
            });
            
        });

        $('.clearSearchValuesButton').livequery('click', function(){
            cpm.hms.inPatient.clearSearchValues();
        });

        $('.feesUpdateForVisitRecord').livequery('click', function(){
            var consultation_fees = $(this).html();
            var employee_visit_id = $('input[name=employee_visit_id]').val();
            var in_patient_id  = $('#record_id').val();

            var url = 'index.php?module=hms_inPatient&_spAction=UpdateConsultingFeesLink&showHTML=0';
            Util.showProgressInd();
            $.get(url, {consultation_fees: consultation_fees, employee_visit_id: employee_visit_id, in_patient_id: in_patient_id}, function(html){
                Util.hideProgressInd();
                $('input[name=consultation_fees]').val(consultation_fees);
            });
            
        });

        $('input[name=consultation_fees]').livequery('keyup', function(){
            cpm.hms.inPatient.reloadTotalAmount();
        });

        $('input[name=amount]').livequery('keyup', function(){
            cpm.hms.inPatient.reloadTotalAmount();
        });

        $('input[name=nursing_fees]').livequery('keyup', function(){
            cpm.hms.inPatient.reloadTotalAmount();
        });

        $('input[name=other_fees]').livequery('keyup', function(){
            cpm.hms.inPatient.reloadTotalAmount();
        });
        
        $('.AddNewDoctorLink').live('click', function (e){
            e.preventDefault();
            var title = "New Employee";
            var parent = $(this).closest('.type-select.ym-fbox-select');
            var dropdown = $('select', parent).attr('name');

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(data){
                    Util.showProgressInd();
                    var category = $("form#newDoctorAddForm input[name='category_hidden']").val();
                    var visits_patients = $("form#newDoctorAddForm input[name='visits_patients']").val();
                    var url = 'index.php?module=hms_inPatient&_spAction=ReloadNewDoctorByJSON&showHTML=0';
                    $.get(url, {category:category, visits_patients:visits_patients}, function (data1) {
                        if(visits_patients == 1) {
                            var consultant_doctor = $(".m-hms_inPatient.v-edit select[name='consultant_doctor']").val();
                            var ref_doctor        = $(".m-hms_inPatient.v-edit select[name='ref_doctor']").val();
                            var review_doctor     = $(".m-hms_inPatient.v-edit select[name='review_doctor']").val();
                            $(".m-hms_inPatient.v-edit select[name='consultant_doctor']").cp_loadSelect(data1);
                            $(".m-hms_inPatient.v-edit select[name='ref_doctor']").cp_loadSelect(data1);
                            $(".m-hms_inPatient.v-edit select[name='review_doctor']").cp_loadSelect(data1);
                            $(".m-hms_inPatient.v-edit select[name='consultant_doctor']").val(consultant_doctor);
                            $(".m-hms_inPatient.v-edit select[name='ref_doctor']").val(ref_doctor);
                            $(".m-hms_inPatient.v-edit select[name='review_doctor']").val(review_doctor);
                        } else {
                            var doctor_id = $('.m-hms_inPatient.v-edit select[name='+dropdown+']').val();
                            $('.m-hms_inPatient.v-edit select[name='+dropdown+']').cp_loadSelect(data1);
                            $('.m-hms_inPatient.v-edit select[name='+dropdown+']').val(doctor_id);
                        }
                        Util.hideProgressInd();
                        Util.closeAllDialogs();
                    }, 'json');
                }
            }

            Util.openFormInDialog.call(this, 'newDoctorAddForm', title, 400, 300, expObj);
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
    },

    reloadTotalAmount: function(){
        var consultation_fees = $('input[name=consultation_fees]').val();
        var amount            = $('input[name=amount]').val();
        var nursing_fees      = $('input[name=nursing_fees]').val();
        var other_fees        = $('input[name=other_fees]').val();
        var total             = parseFloat(0);

        if(consultation_fees == ''){
            consultation_fees = 0;
        }

        if(amount == ''){
            amount = 0;
        }

        if(nursing_fees == ''){
            nursing_fees = 0;
        }

        if(other_fees == ''){
            other_fees = 0;
        }

        total = parseFloat(consultation_fees) + parseFloat(amount) + parseFloat(nursing_fees) + parseFloat(other_fees);

        $('.admissionTotalAmount').html(' - '+total+'RS');
    },

    reloadDoctorTab: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=doctorPortalDisplay&showHTML=0';
        $.get(url, {in_patient_id: in_patient_id}, function(html){
            $('#doctorDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadSpecialistTab: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=specialistPortalDisplay&showHTML=0';
        $.get(url, {in_patient_id: in_patient_id}, function(html){
            $('#specialistDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadSummaryPortal: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=summaryPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {in_patient_id:in_patient_id}, function(html){
            $('#summaryPortalDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadChiefComplains: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=chiefComplainsDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {in_patient_id:in_patient_id}, function(html){
            $('#chiefComplains').html(html);
            Util.hideProgressInd();
        });
    },

    reloadProcedure: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=procedurePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {in_patient_id:in_patient_id}, function(html){
            $('#procedurePortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabsTab: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=LabsDisplay&showHTML=0';
        $.get(url, {in_patient_id: in_patient_id}, function(html){
            $('#labsDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadMedicineTab: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=medicinesPortalDisplay&showHTML=0';
        $.get(url, {in_patient_id: in_patient_id}, function(html){
            $('#medicinesDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabTab: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=labPortalDisplay&showHTML=0';
        $.get(url, {in_patient_id: in_patient_id}, function(html){
            $('#labDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    patientMedicineAdd: function(e) {
        e.preventDefault();
        var in_patient_id = $(this).attr('in_patient_id');
        Util.showProgressInd();
        var url = 'index.php?module=hms_inPatient&_spAction=addMedicine&in_patient_id=' + in_patient_id + '&showHTML=0';
        $.get(url, {in_patient_id: in_patient_id}, function(){
            cpm.hms.inPatient.reloadMedicineTab(in_patient_id);
            Util.hideProgressInd();
        });
    },

    patientProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_inPatient&_spAction=searchProductTitle&showHTML=0'
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
            
                var in_patient_id   = $(this).attr('in_patient_id');

                var url = 'index.php?module=hms_inPatient&_spAction=createProductLineItems&showHTML=0';
                $.get(url, {product_id: product_id, in_patient_id: in_patient_id}, function(json){
                    cpm.hms.inPatient.reloadMedicineTab(in_patient_id);
                    alert('Medicine Added Successfully');
                    $(".addExistingMedicine input[name='product_title']").focus();
                });
            }
        });
    },

    createinPatientDetails: function(patient_information_id, appointment_id, patient_name, age_year, age_month, age_day, gender, father_name, husband_name, address_area, phone, weight, temperature){
        var title = "Choose Doctor/Nurse";
        var url   = "index.php?module=hms_inPatient&_spAction=selectDoctorDetails&patient_information_id="+patient_information_id
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
                    +"&weight="+weight
                    +"&temperature="+temperature
                    +"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(html){
                Util.closeAllDialogs();

                cpm.hms.inPatient.reloadSearchResult();
                in_patient_id = html.returnUrl;
                var printUrl = "index.php?_topRm=main&module=hms_inPatient&_spAction=printTokenForVisit&patient_information_id="+patient_information_id+"&in_patient_id="+in_patient_id+"&showHTML=0";
                window.open(printUrl,'_blank');

                //cpm.hms.inPatient.reloadQueueno();
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
        Util.openFormInDialog.call('','portalForminPatientCreate', title,  588, 'auto', exp);
    },

    reloadSearchResult: function(){
        var inputBoxVaue  = $('.searchInputinPatient').val();
        var dropdownValue = $('#fld_search_type_by_list').val();
        var url = 'index.php?module=hms_inPatient&_spAction=inPatientAppointmentSearchResult&showHTML=0';

        $.get(url, {inputBoxVaue: inputBoxVaue, dropdownValue:dropdownValue}, function(html){
            $('input[name=patient_information_id]').val('');
            $('input[name=patient_name]').val('');
            $('input[name=father_name]').val('');
            $('input[name=spuse_name]').val('');
            $('input[name=address_area]').val('');
            $('input[name=age]').val('');
            $('select[name=gender]').val('Male');
            $('input[name=phone]').val('');
            $('input[name=weight]').val('');
            $('input[name=temperature]').val('');
            $('.searchTableIninPatient').html(html);
            $('.searchTableIninPatient').removeClass('searchTableIninPatienthide');
            $('.searchTableIninPatientAppointment').hide();

        });
    },

    reloadQueueno: function(){
        var url = 'index.php?_theme=matrix&_spAction=patientQueueNo&showHTML=0';
        $.get(url,  function(html){
            $('.queueNumberDisplay').html(html);
        });
    },

    reloadInvoicePortal: function(order_id, in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=InvoicePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitInvoicePortal').html(html);

            var invoice_count    = $('#fld_invoice_count').val();
            if(invoice_count == 0){
                $('#billSummaryOrder').after("<a href='#' id='createOrderRecord' in_patient_id="+in_patient_id+" class='btn btn-info'>Generate Bill</a>");
                $('#billSummaryOrder').remove();
            }

            Util.hideProgressInd();
        });
    },

    reloadInvestigationTabPortal: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=medicalPortalDisplay&showHTML=0';
        $.get(url,{in_patient_id:in_patient_id}, function(html){
            $('#medicalDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadReceiptPortal: function(order_id){
        var url = 'index.php?module=hms_inPatient&_spAction=ReceiptPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitReceiptPortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadTreatmentTabPortal: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=TreatmentPortalDisplay&showHTML=0';
        $.get(url,{in_patient_id:in_patient_id}, function(html){
            $('.treatmentTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadDiagnosisTabPortal: function(in_patient_id){
        var url = 'index.php?module=hms_inPatient&_spAction=DiagnosisPortalDisplay&showHTML=0';
        $.get(url,{in_patient_id:in_patient_id}, function(html){
            $('.diagnosisTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    //Auto select complain details
    complainName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?module=hms_inPatient&_spAction=searchComplainDetails&showHTML=0',
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
                var in_patient_id = $('#record_id').val();
                var url = 'index.php?module=hms_inPatient&_spAction=addComplain&showHTML=0';
                Util.showProgressInd();
                $.get(url, {complain_id: complain_id, in_patient_id: in_patient_id, title: title}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.inPatient.reloadChiefComplains(in_patient_id);
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
                  url: 'index.php?module=hms_inPatient&_spAction=searchProcedureDetails&showHTML=0',
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
                var in_patient_procedure       = selectedObj.in_patient_procedure
                var in_patient_id = $('#record_id').val();
                var url = 'index.php?module=hms_inPatient&_spAction=addProcedure&showHTML=0';
                Util.showProgressInd();
                $.get(url, {in_patient_id: in_patient_id, in_patient_procedure: in_patient_procedure}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.inPatient.reloadProcedure(in_patient_id);
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
                  url: 'index.php?module=hms_inPatient&_spAction=searchDiagnosisDetails&showHTML=0',
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
                var in_patient_id = $('#record_id').val();

                var url = 'index.php?module=hms_inPatient&_spAction=addDiagnosis&showHTML=0';
                Util.showProgressInd();
                
                $.get(url, {prescription_id: prescription_id, in_patient_id: in_patient_id, disease_name: disease_name}, function(html){
                    var urlMedicine = 'index.php?_topRm=main&module=hms_inPatient&_spAction=addPrescribeMedicineFormSubmit&showHTML=0';
                    $.get(urlMedicine, {prescription_id: prescription_id, in_patient_id:in_patient_id}, function(html){
                        //Util.closeAllDialogs();
                        Util.hideProgressInd();
                        cpm.hms.inPatient.reloadSummaryPortal(in_patient_id);
                        cpm.hms.inPatient.reloadMedicineTab(in_patient_id);
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
                  url: 'index.php?module=hms_inPatient&_spAction=searchPatientDetails&showHTML=0',
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
                  url: 'index.php?module=hms_inPatient&_spAction=searchPatientDetailsWithPhone&showHTML=0',
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
                  url: 'index.php?module=hms_inPatient&_spAction=searchPatientDetailsWithAddress&showHTML=0',
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
                    var in_patient_id = $('#record_id').val();
                    cpm.hms.inPatient.reloadInvestigationTabPortal(in_patient_id);
                });
            }
        }).disableSelection();
    },
}

cpm.hms.inPatient.createinPatient = function(){
    var url = 'index.php?module=hms_inPatient&_spAction=createVisitRecordDirect&showHTML=0';
    var dr_required            = $(this).attr('dr_required');
    var patient_information_id = $(this).attr('patient_information_id');
    var appointment_id         = $(this).attr('appointment_id');

    if(dr_required == ''){
        cpm.hms.inPatient.createinPatientDetails(patient_information_id, appointment_id);
    }else{
        $.get(url,{patient_information_id:patient_information_id, dr_required:dr_required, appointment_id:appointment_id}, function(html){
            Util.closeAllDialogs();
            cpm.hms.inPatient.reloadSearchResult();
            cpm.hms.inPatient.reloadQueueno();
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

cpm.hms.inPatient.addPatientRecord = function(e){
    var title = "Create Patient Record";
    var in_patient_id   = $(this).attr('in_patient_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Record created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //cpm.hms.inPatient.reloadDoctorTab(in_patient_id);
                //window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 1100, 500, expObj);
}

cpm.hms.inPatient.addPortalSaveRecord = function(){
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