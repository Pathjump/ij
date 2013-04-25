$(document).ready(function() {
    $('#homeSearchJob').click(function() {
        var company = $('#companyFilter').val();
        var city = $('#cityFilter').val();
        var state = $('#stateFilter').val();
        var category = $('#industryFilter').val();

        if (company || city || state || category)
            searchJobsUrl += '?';

        if (company)
            searchJobsUrl += "&company=" + company;

        if (city)
            searchJobsUrl += "&city=" + city;

        if (state)
            searchJobsUrl += "&state=" + state;

        if (category)
            searchJobsUrl += "&industry=" + category;

        window.location = searchJobsUrl;
    });

    //show map
    initialize();

    //delete job
    $('#deleteJob').click(function() {
        thisLink = $(this);
        //confirm delete job
        jConfirm('ok', 'cancle', 'Are you sure you want to delete this job', 'Delete job', function(r) {
            if (r == true) {
                window.location = thisLink.attr('deleteUrl');
            } else {
                return false;
            }
        });
    });

    $('#notActiveUserApply').click(function() {
        jAlert("You need to register to apply.");
    });

    $("a#jobApply").click(function() {
        $.colorbox({
            href: getUserCvsUrl
        });
    });

//    $("#jobApply").fancybox({
//        'href'              : getUserCvsUrl,
//        'type'              : 'ajax',
//        afterClose          : function() {
//        }
//    });
//
//    //close user cv popup
    $('#userCvCancle').live('click', function() {
        $.colorbox.close();
    });

    //close user cv popup
    $('#userCvSubmit').live('click', function() {
        //hide bottun
        $('#userCvSubmit').hide();
        $('#userCvCancle').hide();
        //show loading image
        $('#userAddJobImg').show();
        //get cv id
        var cvId = $('#userCvs').val();
        $.ajax({
            url: userJobApplyUrl + "/" + cvId,
            success: function(msg) {

            },
            complete: function(msg) {
                //remove job apply link
                $('a#jobApply').remove();
                //close popup
                $.colorbox.close();
                location.reload();

            }
        });
    });
});