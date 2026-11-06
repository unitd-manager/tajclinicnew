Util.createCPObject('cpt.angle');
cpt.angle = {
	init: function(){

        window.onload = getStartedContent();
        function getStartedContent() {
            //var popupSession         = $('#getStartedPopupOnloadSession').val();
            //var popupLocationSession = $('#getLocationPopupOnloadSession').val();

            /*if(popupLocationSession == ''){
                $('#locationChoosemodal').modal('show');
                $('.chooseLocationByUserSubmit').live('click', function (e){
                    e.preventDefault();
                    var url = 'index.php?widget=common_multiUniqueSite&_spAction=changeSite&showHTML=0';
                    var cp_site_id = $('select[name=chooseLocationByUserDropdown]').val();
                    $.get(url, {cp_site_id: cp_site_id}, function(){
                        cpt.angle.resetSessionForLocation();
                    })
                });
                
            }*/

            /*if(popupSession == ''){
                setTimeout(function() {
                    $("a.getStartedContentTask").trigger('click');
                },100);
            }*/

            $('.v-list table.cpSearch .type-text.ym-fbox-text.dateRange input.fld_date').each(function(){
                var inputname = $(this).attr('name');
                var dateValue = $(this).val();
                $(this).addClass('MainDateField');
                $(this).after("<input type='text' class='hiddenDateDisplay' name='hidden_date_display' data-onload='"+dateValue+"'>");
            });

            $('.v-list table.cpSearch td.dateRange input.fld_date').each(function(){
                var inputname = $(this).attr('name');
                var dateValue = $(this).val();
                $(this).addClass('MainDateField');
                $(this).after("<input type='text' class='hiddenDateDisplay' name='hidden_date_display' data-onload='"+dateValue+"'>");
            });

            $('.v-list table.cpSearch .type-text.ym-fbox-text.dateRange .hiddenDateDisplay[data-onload]').each(function() {
                var dateCheck = $(this).attr('data-onload');
                
                if(dateCheck != '') {
                    var date      = dateCheck.replace(/-/g, '/');
                    var newdate   = new Date(date);
                    var dd = ('0' + newdate.getDate()).slice(-2);
                    var mm = ('0' + (newdate.getMonth() + 1)).slice(-2)
                    var y  = newdate.getFullYear();
         
                    var endDate = dd + '-'+ mm + '-' + y;
                }else {
                    var endDate = '';
                }

                $(this).val(endDate);
            });

            $('.v-list table.cpSearch td.dateRange .hiddenDateDisplay[data-onload]').each(function() {
                var dateCheck = $(this).attr('data-onload');
                
                if(dateCheck != '') {
                    var date      = dateCheck.replace(/-/g, '/');
                    var newdate   = new Date(date);
                    var dd = ('0' + newdate.getDate()).slice(-2);
                    var mm = ('0' + (newdate.getMonth() + 1)).slice(-2)
                    var y  = newdate.getFullYear();
         
                    var endDate = dd + '-'+ mm + '-' + y;
                }else {
                    var endDate = '';
                }

                $(this).val(endDate);
            });
        }

        //Click event to scroll to top
        /*$('.scrollToTop').click(function(){
            $('html, body').animate({scrollTop : 0},800);
            return false;
        });*/

        $('.viewsearchMedicines').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Search Medicines', 660, 450, expObj);
        });


        $("input[name='product_title_search']").livequery(function(){
            var titleObj = this;
            $(titleObj).autocomplete({
                 source : 'index.php?module=tradingsg_purchaseOrder&_spAction=searchProductTitle&showHTML=0'
                ,minLength : 1
                ,selectFirst: true
                ,autoFocus: true
                ,select: function(event, ui) {
                    var selectedObj = ui.item;
                    var product_id = selectedObj.id
                    $("input[name='product_id[]']", parent).val(product_id);
                    

                    var url = 'index.php?_theme=angle&_spAction=searchMedicinePortal&showHTML=0';
                    $.get(url, {product_id: product_id}, function(json){
                        cpt.angle.reloadMedicineTab(product_id);
                        //alert(product_id);
                  
                    });
                }
            });
        });

        $('.leftNav .hlist ul li.first').livequery('click', function(){
            var parent = $(this).closest('li');
            parent.next('ul.displayNone').slideToggle();
        });

    	//show hide description in Help Content - TRADE SMART (USS Product)
        $('.contentTitle').livequery('click', function(){
            //$('.contentDescription').css('display','none');
            var parent = $(this).closest('.helpContentTask');
            $('.contentDescription', parent).slideToggle();
            var parent = $(this).closest('.startedContentTask');
            $('.contentDescription', parent).slideToggle();
        });

		// Adding help button pop window in the content list  - TRADE SMART (USS Product)
		$("a.helpContentTask").livequery('click', function (e){
		    var module_name = $(this).attr('module_name');
		    var url = 'index.php?module=webBasic_content&_spAction=helpContentTask&module_name=' + module_name + '&showHTML=0';
		    var exp = {
		        url: url
		    };
		    Util.openDialogForLink('Help Content',  1000, 500, 0, exp);
		});

    	//show hide description in GET STARTED Content - TRADE SMART (USS Product)
    	$('.contentTitle').livequery('click', function(){
    		var parent = $(this).closest('.getStartedContentTask');
    	    $('.contentDescription', parent).slideToggle();
    	});

		// Adding GET STARTED button pop window in the content list  - TRADE SMART (USS Product)
		$("a.getStartedContentTask").livequery('click', function (e){
		    var module_name = $(this).attr('module_name');
		    var url = 'index.php?module=webBasic_content&_spAction=startedContentTask&module_name=' + module_name + '&showHTML=0';
		    var exp = {
		        url: url
		    };
		    Util.openDialogForLink('Get Started',  1000, 500, 0, exp);
		});

    	$("#nav .hlist ul li a span").addClass('inner');
    	$("#nav .hlist ul li a").blend();

        $("ul.homeTop li").livequery('click', function(){
            $(this).children("ul.sub").slideToggle();
        });

        $("ul.homeTop font a").livequery('click', function(){
            $(this).children("ul.sub").slideToggle();
        });

        $(".leftnavShowHide").livequery('click', function(){
            $('#col1').slideToggle('fast', function() {
                $('.leftnavShowHide').toggleClass('leftnavShowHideicon', $('#col1').is(':hidden'));
            });

            $('#col3').addClass('fullleftlist');

        });

        $("#timeout-example").livequery('click', function(e){
           e.preventDefault();
           $.timeoutDialog({timeout: 1, countdown: 60, logout_redirect_url: 'index.php?plugin=common_login&_spAction=logout', restart_on_yes: false});
         });


        /*$("ul.homeTop li").hover(function () { //When trigger is hovered...
            //$(this).children("ul.sub").slideDown('fast').show();
            $(this).children("ul.sub").slideToggle()
            }, function () {
            //$(this).children("ul.sub").slideUp('slow');
            //$(this).children("ul.sub").slideUp(100);
        });*/


    	$('.contentScroller, .m-common_dashboard .widget div.tableOuter').addClass('scroll-pane');
    	/*$('.scroll-pane').jScrollPane(
    	    {}
    	);*/

    	if ($('.tplLogin').length > 0){
    	    var toSubtract = $('#header').outerHeight(true) + $('#footer').outerHeight(true);
    	    var mainPanelHt = $(window).height() - toSubtract - 20;
    	    $('#col3_content').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
    	    $("#col3_content #loginOuter").cp_center();
    	}

    	$("table.search td select").change(function() {
    	    $('#searchTop').submit();
    	});

        $('.TimeoutHeaderButton').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You Update Time Out?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeOutUpdate&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    Util.hideProgressInd();
                    alert('Time Out Updated!');
                });
            }
        });

        $('.TimeinHeaderButton').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You like to Add Time In?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeInUpdate&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    $(".TimeinHeaderButton").attr('disabled', 'disabled');
                    $(".TimeoutButtonHeader").removeClass('displayNone');
                    Util.hideProgressInd();
                    alert('Time In Updated!');
                });
            }
        });

        $('.TimeoutHeaderButton2').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You Update Time Out?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeOutUpdateNight&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    Util.hideProgressInd();
                    alert('Time Out Updated!');
                });
            }
        });

        $('.TimeinHeaderButton2').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You like to Add Time In?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeInUpdateNight&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    $(".TimeinHeaderButton2").attr('disabled', 'disabled');
                    $(".TimeoutButton2Header").removeClass('displayNone');
                    Util.hideProgressInd();
                    alert('Time In Updated!');
                });
            }
        });

        $('.TimeinHeaderButtonDay2').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You like to Add Time In?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeInUpdateDay&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    $(".TimeinHeaderButtonDay2").attr('disabled', 'disabled');
                    $(".TimeoutButton3Header").removeClass('displayNone');
                    Util.hideProgressInd();
                    alert('Time In Updated!');
                });
            }
        });

        $('.TimeoutHeaderButtonDay2').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You Update Time Out?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeOutUpdateDay&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    Util.hideProgressInd();
                    alert('Time Out Updated!');
                });
            }
        });

        $('.TimeinHeaderButtonDSEvening').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You like to Add Time In?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeInUpdateEvening&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    $(".TimeinHeaderButtonDSEvening").attr('disabled', 'disabled');
                    $(".TimeoutButton4Header").removeClass('displayNone');
                    Util.hideProgressInd();
                    alert('Time In Updated!');
                });
            }
        });

        $('.TimeoutHeaderButtonDSEvening').livequery('click', function (e){
            var staff_id = $(this).attr('staff_id');

            msg = "Would You Update Time Out?";
            if (confirm (msg)){
                Util.showProgressInd();
                var url = 'index.php?plugin=common_login&_spAction=staffTimeOutUpdateEvening&showHTML=0';

                $.get(url, {staff_id: staff_id}, function(){
                    Util.hideProgressInd();
                    alert('Time Out Updated!');
                });
            }
        });

        $('.v-edit .hasDatepicker, .v-new .hasDatepicker').livequery('change', function(e){
            var parent    = $(this).closest(".type-text.ym-fbox-text");
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $('.hiddenDateDisplay', parent).val(endDate);
        });

        $('.v-list table.cpSearch .type-text.ym-fbox-text .hasDatepicker').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $(this).next("input.hiddenDateDisplay").val(endDate);
        });

        $('.v-list table.cpSearch td.dateRange .hasDatepicker').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $(this).next("input.hiddenDateDisplay").val(endDate);
        });

        $('#reportSearchPanel table.search td.dateRange .hasDatepicker').livequery('change', function(e){
            var dateCheck = $(this).val();

            if(dateCheck != "") {
                var date      = dateCheck.replace(/-/g, "/");
                var newdate   = new Date(date);
                var dd = ("0" + newdate.getDate()).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
                var y  = newdate.getFullYear();
     
                var endDate = dd + '-'+ mm + '-' + y;
            }else {
                var endDate = "";
            }

            $(this).prev("input.hiddenDateDisplay").val(endDate);
        });
 	},

    reloadMedicineTab: function(product_id){
        var url = 'index.php?_theme=angle&_spAction=searchMedicinePortal&showHTML=0';
        $.get(url, {product_id: product_id}, function(html){
            $('#productLink').html(html);
            Util.hideProgressInd();
        });
    },

    
    resetSessionForLocation: function(purchase_order_id){
        var url = 'index.php?module=webBasic_content&_spAction=LocationSelectOnLogin&showHTML=0';
        $.get(url, function(html){
            var topRm = $('#cpTopRm').val();
            var cpRoom = $('#cpRoom').val();
            var urlRedirect = "index.php?_topRm=" + topRm +
                      "&module=" + cpRoom;
            document.location = urlRedirect;
        });
    },


}

function DropDown(el) {
                this.dd = el;
                this.placeholder = this.dd.children('span');
                this.opts = this.dd.find('ul.dropdown > li');
                this.val = '';
                this.index = -1;
                this.initEvents();
            }
            DropDown.prototype = {
                initEvents : function() {
                    var obj = this;

                    obj.dd.livequery('click', function(event){
                        $(this).toggleClass('active');
                        return false;
                    });

                    obj.opts.livequery('click',function(){
                        var opt = $(this);
                        obj.val = opt.text();
                        obj.index = opt.index();
                        obj.placeholder.text(obj.val);
                    });
                },
                getValue : function() {
                    return this.val;
                },
                getIndex : function() {
                    return this.index;
                }
            }

            $(function() {

                var dd = new DropDown( $('#dd') );

                $(document).click(function() {
                    // all dropdowns
                    $('.wrapper-dropdown-3').removeClass('active');
                });

            });