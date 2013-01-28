$(document).ready(function(){
    $(".chzn-select").livequery(function(){
        $(this).chosen();
    });
    $('#readAllMessages').livequery(function(){
        $(this).on('click', function(){
            confirmSubmit('readAllMessages');
        });
    });
    $('#deleteAllMessages').livequery(function(){
        $(this).on('click', function(){
            confirmSubmit('deleteAllMessages');
        });
    });
    setInterval(function(){
        //Loader Script
        $('#loading').width($('#loading').parent().width());
        $('#loading').height($('#loading').parent().height());
    }, 500);
});

function changeMessagesSelection(element) {
    if(element.checked) {
        $('.companyMessage').each(function() {
            $(this).attr('checked', 'checked');
        });
    } else {
        $('.companyMessage').each(function() {
            $(this).removeAttr('checked');
        });
    }
}

function requestData(urlToRequest, divId, completeFunction) {
    $('#loading').show();
    $.ajax({
        url: urlToRequest,
        success: function(msg) {
            $(divId).html(msg);
        },
        complete: function(msg) {
            $('#loading').hide();
            if($.isFunction(completeFunction)){
                completeFunction();
            }
        }
    });
}

function getTabContents(tabberObject){
    var urlToRequest = '';
    var divId = '';
    //specify the url to use
    switch(tabberObject.index){
        case 0:
            urlToRequest = companyInboxurl;
            divId = '#inboxTab';
            break;
        case 1:
            urlToRequest = companyOutboxurl;
            divId = '#outboxTab';
            break;
        case 2:
            urlToRequest = companyMessageurl;
            divId = '#composeTab';
            break;
    }
    requestData(urlToRequest, divId);
}

function confirmSubmit(attributToAdd) {
    if($('.companyMessage:checked').length > 0){
        if(attributToAdd == 'deleteAllMessages'){
            jConfirm(
                'ok',
                'cancel',
                'Are you sure you want to Delete ' + $('.companyMessage:checked').length + ' messages',
                'Warning',
                function(confirm){
                    if(confirm){
                        submitMessagesForm('deleteAllMessages');
                    }
                });
        } else {
            submitMessagesForm('readAllMessages');
        }
    }
}

function submitMessagesForm(attributToAdd){
    $('#loading').show();
    $.ajax({
        url: $('#companyMessagesForm').attr('action'),
        type: $('#companyMessagesForm').attr('method'),
        data: $('#companyMessagesForm').serialize() + '&' + attributToAdd + '=1',
        complete: function() {
            if($('#currentBox').val() == 'outbox'){
                window.location = outboxUrl;
            } else {
                window.location = inboxUrl;
            }
        }
    });
}

function confirmDelete(form, url, divId){
    jConfirm(
        'ok',
        'cancel',
        'Are you sure you want to Delete This Message',
        'Warning',
        function(confirm){
            if(confirm){
                $.ajax({
                    url: $(form).attr('action'),
                    type: $(form).attr('method'),
                    data: $(form).serialize(),
                    complete: function() {
                        requestData(url, divId);
                    }
                });
            }
        });
}