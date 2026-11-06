/* Filtering month with respect to values chosen */
$('#wd_hms_pharmacyDailySales select[name=month]').livequery('change', function(){
    var month = $(this).val();

    var url = 'index.php?widget=hms_pharmacyDailySales&_spAction=rowsHTML';
    Util.showProgressInd();
    $.get(url,{month: month, change: 1}, function(html){
        $('#wd_hms_pharmacyDailySales tbody').html(html);
        Util.hideProgressInd();
    });
});

