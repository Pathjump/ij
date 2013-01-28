$(document).ready(function(){
    setInterval(function(){
        //Loader Script
        $('#loading').width($('#loading').parent().width());
        $('#loading').height($('#loading').parent().height());
    }, 500);
    
    $('.taskNote').hide();
    //intialize job users
    if($('#form_internship').val() != ''){
        $.ajax({
            url: jobUsersUrl+"/"+$('#form_internship').val(),
            dataType: 'json',
            success: function(msg) {
                //                var msg = $.parseJSON(msg); 
                //set users
                $('#form_user').empty();
                $.each(msg, function(k, v) {
                    //display the key and value pair
                    $('#form_user').append(
                        $('<option></option>').val(k).html(v)
                        );
                });
                
                //select task user for edit action
                if(typeof userId != 'undefined')
                    $('#form_user').val(userId);
                
            },
            complete: function(msg) {
                $("select.chzn-done").trigger("liszt:updated");
            }
        });
    }
    
    
    //change job users when jobs change
    $('#form_internship').change(function(){
        $('#loading').show();
        $.ajax({
            url: jobUsersUrl+"/"+$(this).val(),
            dataType: 'json',
            success: function(msg) {
                //                var msg = $.parseJSON(msg); 
                //set users
                $('#form_user').empty();
                $.each(msg, function(k, v) {
                    //display the key and value pair
                    $('#form_user').append(
                        $('<option></option>').val(k).html(v)
                        );
                });
            },
            complete: function(msg) {
                $('#loading').hide();
                $("select.chzn-done").trigger("liszt:updated");
            }
        });
        
    });
    
    
});