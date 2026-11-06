Util.createCPObject('cpm.hms.patientInformation');

cpm.hms.patientInformation = {
    init: function(){
        $('select[name=company_id]').livequery('change', function(){
            var company_id = $(this).val();

            var url = 'index.php?module=hms_patientInformation&_spAction=updateCompanyDetails&showHTML=0';
            $.get(url, {company_id: company_id}, function(json){
            	$('#fld_company_phone').html(json.phone);
            	$('#fld_company_address_flat').html(json.address_flat);
            	$('#fld_company_address_street').html(json.address_street);
            	$('#fld_company_address_town').html(json.address_town);
            	$('#fld_company_address_state').html(json.address_state);
            	$('#fld_company_address_country').html(json.address_country);
            });
        });

        $('#frmNew select[name="pass_type"]').livequery('change', function(){
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

        $('#frmEdit select[name="pass_type"]').livequery('change', function(){
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

        $('select[name="bill_type"]').livequery('change', function(){
            var bill_type = $(this).val();

            if(bill_type == 'Company' || bill_type == 'Panel'){
                $('.companyDetailsTr').removeClass('companyDetailsHide');
                
                if(bill_type == 'Panel'){
                    $('.companyDetailsTr').find('th').html('Panel Details');
                    $('#fld_company_phone').html('');
                    $('#fld_company_address_flat').html('');
                    $('#fld_company_address_street').html('');
                    $('#fld_company_address_town').html('');
                    $('#fld_company_address_state').html('');
                    $('#fld_company_address_country').html('');
                    $('.row_company_id label').html('Panel Name');
                }
                
                if(bill_type == 'Company'){
                    $('.companyDetailsTr').find('th').html('Company Details');
                    $('#fld_company_phone').html('');
                    $('#fld_company_address_flat').html('');
                    $('#fld_company_address_street').html('');
                    $('#fld_company_address_town').html('');
                    $('#fld_company_address_state').html('');
                    $('#fld_company_address_country').html('');
                    bill_type = 'Client';
                    $('.row_company_id label').html('Client Name');
                }

                var url = 'index.php?module=hms_patientInformation&_spAction=CompanyNameJSON&showHTML=0';
                $.get(url, {company_category: bill_type}, function (data) {
                    $("select[name='company_id']").cp_loadSelect(data);
                }, 'json');

            }else{
                $('.companyDetailsTr').addClass('companyDetailsHide');
                $('select[name=company_id]').val('');
                $('#fld_company_phone').html('');
                $('#fld_company_address_flat').html('');
                $('#fld_company_address_street').html('');
                $('#fld_company_address_town').html('');
                $('#fld_company_address_state').html('');
                $('#fld_company_address_country').html('');
            }

        });

        $('select[name="primary_contact"]').livequery('change', function(){
            var primary_contact = $(this).val();

            if(primary_contact == 'Other'){
                $('.primaryContactTr').removeClass('PrimaryContactHide');
                
                if(primary_contact == 'Other'){
                    $('.row_patient_information_id label').html('Other');
                }
                $.get({primary_contact: primary_contact}, function (data) {
                    $('#fld_primary_contact').cp_loadSelect(data);
                });

            }else{
                $('.primaryContactTr').addClass('PrimaryContactHide');
            }

        });
	}
}