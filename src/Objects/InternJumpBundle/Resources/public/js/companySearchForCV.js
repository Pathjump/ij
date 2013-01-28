$(document).ready(function(){
    setInterval(function(){
        //Loader Script
        $('#loading').width($('#loading').parent().width());
        $('#loading').height($('#loading').parent().height());
    }, 500);
    //add scroll for the categories inputs
    var oScroll1 = $('div#scrollbar1');
    if(oScroll1.length > 0){
        oScroll1.tinyscrollbar();
    }
    //initialize chosen
    $('.chzn-select').chosen({
        allow_single_deselect: true
    });
    //check if we have any country selected
    if($('#form_country').val()) {
        //get the correct city and state select from the server
        changeCityAndStateSelects($('#form_country').val());
    }
    //on the change of country select change the city and state selects values
    $('#form_country').on('change', function() {
        if($(this).val()) {
            //change the city and state selects
            changeCityAndStateSelects($(this).val());
        } else {
            //empty the city select
            $('#form_city').empty();
            //empty the state select
            $('#form_state').empty();
            $("select.chzn-select").trigger("liszt:updated");
        }
    });
});

function changeCityAndStateSelects(countryId) {
    //display the loader
    $('#loading').show();
    $.ajax({
        url: countrySelectsUrl + countryId,
        success: function(msg) {
            msg = $.parseJSON(msg);
            //get the current selected country if exist
            var selectedCity = $('#form_city').val();
            //empty the city select
            $('#form_city').empty();
            //append the new data
            $.each(msg.cities, function(k, v) {
                $('#form_city').append('<option value=""></option>');
                if(selectedCity == v){
                    $('#form_city').append(
                        $('<option selected="selected"></option>').val(v).html(v)
                        );
                } else {
                    $('#form_city').append(
                        $('<option></option>').val(v).html(v)
                        );
                }
            });
            //get the current selected country if exist
            var selectedState = $('#form_state').val();
            //empty the state select
            $('#form_state').empty();
            //append the new data
            $.each(msg.states, function(k, v) {
                $('#form_state').append('<option value=""></option>');
                if(selectedState == v){
                    $('#form_state').append(
                        $('<option selected="selected"></option>').val(v).html(v)
                        );
                } else {
                    $('#form_state').append(
                        $('<option></option>').val(v).html(v)
                        );
                }
            });
        },
        complete: function() {
            //hide the loader
            $('#loading').hide();
            $("select.chzn-select").trigger("liszt:updated");
        }
    });
}

function refreshPage(page) {
    //display the loader
    $('#loading').show();
    $.ajax({
        url: $('#cv-search-form').attr('action'),
        data: $('#cv-search-form').serialize() + '&page=' + page,
        success: function(data) {
            $('#company-cv-search-results').html(data);
        },
        complete: function() {
            //hide the loader
            $('#loading').hide();
        }
    });
}