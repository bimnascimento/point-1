jQuery(document).ready(function($) {  
    jQuery('.color').colorPicker();
    var fileInput = '';  
    jQuery('#upload-logo-live-chat').click(function() {
        fileInput = jQuery(this).prev('input');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });
    window.send_to_editor = function(html) {
     imgurl = jQuery('img',html).attr('src');
     fileInput.val(imgurl);
     tb_remove();
    }  
    $("#fb-checkall").change(function(){
        $(".live_chat_facebook_show").prop('checked', $(this).prop("checked"));
    }) 
    $("#fb-checkall1").change(function(){
        $(".live_chat_facebook_show1").prop('checked', $(this).prop("checked"));
    }) 
    $("#ninja-display").change(function(){
        var id = $(this).val();
        if ( id != 1 ) {
            $("#ninja-hide-tr1").removeClass("hide");
            $("#ninja-hide-tr2").addClass("hide");
        }else{
            $("#ninja-hide-tr2").removeClass("hide");
            $("#ninja-hide-tr1").addClass("hide");
        } 
    })
});
