Util.createCPObject('cpm.payroll.payrollManagement');

cpm.payroll.payrollManagement.init = function(){
    $(window).load(function(){
        $('.m-payroll_payrollManagement .GenerateRecords').livequery('click', function(){
            var current_Month = $(this).attr('current_Month');
            var current_Year = $(this).attr('current_Year');
            var url = 'index.php?module=payroll_payrollManagement&_spAction=updateRecords&showHTML=0';
            var record_count = $(this).attr('record_count');
            if(record_count == 0){
                $.get(url, {current_Month: current_Month, current_Year: current_Year}, function(json){
                    window.location.reload(true);
                });
            }else{
                Util.alert('Records Already Created');
            }
        });
    });

    /*Earnings: on key change popualte Amount and Total*/
    $('.m-payroll_payrollManagement #fld_ot_hours').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_commission').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_monthly_allowance').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_additional_wages').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_allowance1').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_allowance2').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_allowance3').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });


    /*Deductions: on key change popualte Amount and Total*/
    $('.m-payroll_payrollManagement #fld_mbf').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_loan_amount').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_income_tax_amount').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_sosco').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_accommodation').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_other_deduction').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_deduction1').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_deduction2').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_deduction3').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $("a.printPayslipForAllLink").livequery('click', function (e){
        var title = "Generate All Payslip";
        var url   = "index.php?module=payroll_payrollManagement&_spAction=printPayslipForm&showHTML=0"; 
        var expObj = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                Util.showProgressInd();
                var year        = $('#fld_payroll_year').val();
                var month       = $('#fld_payroll_Month').val();
                //month           = populateAmount.pad2(month);
                var convertUrl = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=printPaySlipForAllPdf&payroll_year=" + year + "&payroll_month=" + month;
                Util.closeAllDialogs();
                Util.hideProgressInd();
                window.open(convertUrl, 'blank');
                //document.location = convertUrl;
            }
        };
        Util.openFormInDialog.call('', 'portalFormPrintPayslip', title, 525, 'auto', expObj);
    });

    $(".m-payroll_payrollManagement .row_status select[name='status']").livequery('change', function(e){
        var status = $(this).val();
        $('.m-payroll_payrollManagement .passType').removeClass('displayNone');
        if (status == 'Paid') {
            $('.m-payroll_payrollManagement .row_paid_date').show();
        }else{
            $('.m-payroll_payrollManagement .row_paid_date').hide();
            $('.m-payroll_payrollManagement .passType').addClass('displayNone');
        }
    });


    var populateAmount = {
        populateDeductionAmount: function(){
            var totalAmount     = 0;
            var netTotal        = 0;

            var mbf             = $('.m-payroll_payrollManagement #fld_mbf').val();
            var cpf             = $('.m-payroll_payrollManagement .cpfEmployee').html();
            var loanAmount      = $('.m-payroll_payrollManagement #fld_loan_amount').val();
            var incomeTaxAmount = $('.m-payroll_payrollManagement #fld_income_tax_amount').val();
            var sosco           = $('.m-payroll_payrollManagement #fld_sosco').val();
            var accommodation   = $('.m-payroll_payrollManagement #fld_accommodation').val();
            var other_deduction = $('.m-payroll_payrollManagement #fld_other_deduction').val();
            var deduction1      = $('.m-payroll_payrollManagement #fld_deduction1').val();
            var deduction2      = $('.m-payroll_payrollManagement #fld_deduction2').val();
            var deduction3      = $('.m-payroll_payrollManagement #fld_deduction3').val();

            if(mbf == undefined || mbf == ''){
               mbf = parseInt(0);
            }

            if(cpf == undefined || cpf == ''){
               cpf = parseInt(0);
            }

            if(loanAmount == undefined || loanAmount == ''){
               loanAmount = parseInt(0);
            }

            if(incomeTaxAmount == undefined || incomeTaxAmount == ''){
               incomeTaxAmount = parseInt(0);
            }

            if(sosco == undefined || sosco == ''){
               sosco = parseInt(0);
            }

            if(accommodation == undefined || accommodation == ''){
               accommodation = parseInt(0);
            }

            if(other_deduction == undefined || other_deduction == ''){
               other_deduction = parseInt(0);
            }

            if(deduction1 == undefined || deduction1 == ''){
               deduction1 = parseInt(0);
            }

            if(deduction2 == undefined || deduction2 == ''){
               deduction2 = parseInt(0);
            }

            if(deduction3 == undefined || deduction3 == ''){
               deduction3 = parseInt(0);
            }

            totalAmount = parseFloat(parseInt(cpf) + parseInt(mbf) + parseInt(loanAmount) + parseInt(incomeTaxAmount) + parseInt(sosco) + parseInt(accommodation) + parseInt(other_deduction) + parseInt(deduction1) + parseInt(deduction2) + parseInt(deduction3));

            $('.m-payroll_payrollManagement .totalDeduction').html(totalAmount.toFixed(2));

            var totalDeduction = $('.m-payroll_payrollManagement .totalDeduction').html();
            var totalEarnings  = $('.m-payroll_payrollManagement .grossPay').html();

            if(totalDeduction == undefined || totalDeduction == ''){
               totalDeduction = parseInt(0);
            }

            if(totalEarnings == undefined || totalEarnings == ''){
               totalEarnings = parseInt(0);
            }

            netTotal  =   parseFloat(parseInt(totalEarnings) - parseInt(totalDeduction));
            $('.m-payroll_payrollManagement .netTotalPayrollMgmt').html(netTotal.toFixed(2));
        },

        populateEarningsAmount: function(){
            var ot_amount          = 0;
            var totalAmount        = 0;
            var netTotal           = 0;
            var hours              = $('.m-payroll_payrollManagement #fld_ot_hours').val();
            var payRate            = $('.m-payroll_payrollManagement .otPayRate').html();
            var basicPay           = $('.m-payroll_payrollManagement .basicPayRate').html();
            var commission         = $('.m-payroll_payrollManagement input[name=commission]').val();
            var additional_wages   = $('.m-payroll_payrollManagement input[name=additional_wages]').val();
            var monthly_allowance  = $('.m-payroll_payrollManagement input[name=monthly_allowance]').val();
            var allowance1         = $('.m-payroll_payrollManagement input[name=allowance1]').val();
            var allowance2         = $('.m-payroll_payrollManagement input[name=allowance2]').val();
            var allowance3         = $('.m-payroll_payrollManagement input[name=allowance3]').val();

            ot_amount = hours * payRate;

            if(basicPay == undefined || basicPay == ''){
               basicPay = parseInt(0);
            }

            if(ot_amount == undefined || ot_amount == ''){
               ot_amount = parseInt(0);
            }

            if(commission == undefined || commission == ''){
               commission = parseInt(0);
            }

            if(monthly_allowance == undefined || monthly_allowance == ''){
               monthly_allowance = parseInt(0);
            }

            if(additional_wages == undefined || additional_wages == ''){
               additional_wages = parseInt(0);
            }

            if(allowance1 == undefined || allowance1 == ''){
               allowance1 = parseInt(0);
            }

            if(allowance2 == undefined || allowance2 == ''){
               allowance2 = parseInt(0);
            }

            if(allowance3 == undefined || allowance3 == ''){
               allowance3 = parseInt(0);
            }

            totalAmount = parseFloat(parseInt(basicPay) + parseInt(ot_amount) + parseInt(commission) + parseInt(monthly_allowance) + parseInt(additional_wages) + parseInt(allowance1) + parseInt(allowance2) + parseInt(allowance3));

            $('.m-payroll_payrollManagement input[id=fld_ot_amount]').val(ot_amount.toFixed(2));
            $('.m-payroll_payrollManagement .grossPay').html(totalAmount.toFixed(2));

            var totalDeduction = $('.m-payroll_payrollManagement .totalDeduction').html();
            var totalEarnings  = $('.m-payroll_payrollManagement .grossPay').html();

            if(totalDeduction == undefined || totalDeduction == ''){
               totalDeduction = parseInt(0);
            }

            if(totalEarnings == undefined || totalEarnings == ''){
               totalEarnings = parseInt(0);
            }

            netTotal  =   parseFloat(parseInt(totalEarnings) - parseInt(totalDeduction));
            $('.m-payroll_payrollManagement .netTotalPayrollMgmt').html(netTotal.toFixed(2));
        },

        pad2: function(number) {
            return (number < 10 ? '0' : '') + number
        }

    }

}