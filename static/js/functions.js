// page init
$(document).ready(function() {

    $('#content').fitVids();

    $(".project-asset").fancybox({
        helpers:  {
            title:  null
        },
        nextEffect: 'none',
        prevEffect: 'none'
    });

    $('.opener').click(function(e) {
        e.preventDefault();
        $('#' + $(this).attr('rel')).toggle();
        $('#' + $(this).attr('rel') + '-parent').toggleClass('active');
    });

    $(".gallery li").hover(function() {
        if ($(window).width() > 600) {
            $(this).find(".caption").filter(':not(:animated)').fadeIn(500);
        }
    }, function() {
        if ($(window).width() > 600) {
            $(this).find(".caption").fadeOut(500);
        }
    });

    $("#portfolio-sortable tbody").sortable({
        helper: fixHelper,
        stop: function() {
            $.ajax({
                url: '/portfolio/save-order',
                type: 'POST',
                dataType: 'json',
                data : $(this).sortable("serialize")
            });
        }
    }).disableSelection();

    $("#credit-sortable tbody").sortable({
        helper: fixHelper,
        stop: function() {
            $.ajax({
                url: '/portfolio/save-credit-order',
                type: 'POST',
                dataType: 'json',
                data : $(this).sortable("serialize")
            });
        }
    }).disableSelection();

    $("#still-sortable tbody").sortable({
        helper: fixHelper,
        stop: function() {
            $.ajax({
                url: '/portfolio/save-asset-order',
                type: 'POST',
                dataType: 'json',
                data : $(this).sortable("serialize")
            });
        }
    }).disableSelection();

    $("#process-sortable tbody").sortable({
        helper: fixHelper,
        stop: function() {
            $.ajax({
                url: '/portfolio/save-asset-order',
                type: 'POST',
                dataType: 'json',
                data : $(this).sortable("serialize")
            });
        }
    }).disableSelection();

    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };


    $(".hide-panel").click(function() {
        $(".control-panel").hide();
        return false;
    });

    FB.init({
        appId: App.Config.App.app_id,
        status: true,
        cookie: true,
        xfbml: true,
        oauth: true,
        channelUrl : App.Config.App.static_url +'/static/3rdparty/facebook/channel.html'
    });

    if($.browser.opera){
        FB.XD._transport="postmessage";
        FB.XD.PostMessage.init();
    }

    FB.getLoginStatus(function(response){
        if(!App.Config.App.authenticated && response.status == "connected"){
            location.href = "/user/login?redirect=" + window.location.href;
        }
    });
});