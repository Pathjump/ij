$(document).ready(function(){
    //show map
    initialize();
    
    //delete job
    $('#deleteJob').click(function(){
        thisLink = $(this);
        //confirm delete job
        jConfirm('ok','cancle','Are you sure you want to delete this job', 'Delete job', function (r) {
            if (r == true) {
                window.location = thisLink.attr('deleteUrl');
            }else{
                return false;
            }
        });
    });
    
    $("#jobApply").fancybox({
        'href'              : getUserCvsUrl,
        'type'              : 'ajax',
        afterClose          : function() {
        }
    });
    
    //close user cv popup
    $('#userCvCancle').live('click',function(){
        $.fancybox.close(); 
    });
    
    //close user cv popup
    $('#userCvSubmit').live('click',function(){
        //hide bottun 
        $('#userCvSubmit').hide();
        $('#userCvCancle').hide();
        //show loading image
        $('#userAddJobImg').show();
        //get cv id
        var cvId = $('#userCvs').val();
        $.ajax({
            url: userJobApplyUrl+"/"+cvId,
            success: function(msg) {
                if(msg == 'done'){
                    $('#jobApplySuccess').text(job_apply_success_message);
                }
            },
            complete: function(msg) {
                //remove job apply link
                $('a#jobApply').remove();
                //close popup
                $.fancybox.close(); 
            }
        });
    });
});