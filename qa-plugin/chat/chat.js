$(document).ready(function(){    
    $(".chat-open").click(function() {
        
        $.fancybox.open({
            href : '../qa-plugin/chat/chat-engine/index.php?user=' + $(this).data("user") + '&channel=' + $(this).data("channel") + '&submit=true',
            type : 'iframe',
            width: 1000,
            height: 600,
            minWidth: 1000,
            minHeight: 600,
            padding : 5,
            closeBtn : false
        });
    });

});