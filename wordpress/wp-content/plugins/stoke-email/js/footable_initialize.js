jQuery(document).ready(function() {
    jQuery('.bis_footable').footable();
    jQuery('#bis_pagination').val(10);
    jQuery('#bis_pagination').on('change', function() {
        jQuery('.bis_footable').data('page-size', jQuery(this).value);
        jQuery('.bis_footable').trigger('footable_initialized');
    });


// For Upload
    var custom_uploader;


    jQuery('#bis_logo_upload').click(function(e) {

        e.preventDefault();

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            jQuery('#bis_mail_logo').val(attachment.url);
        });

        //Open the uploader dialog
        custom_uploader.open();

    });

    //for selection validation
    if (jQuery('#bis_enable_mail_logo').is(":checked")) {
        jQuery('.mail_logo_settings').parent().parent().css('display', 'table-row');
    } else {
        jQuery('.mail_logo_settings').parent().parent().css('display', 'none');
    }
    //on Change
    jQuery('#bis_enable_mail_logo').change(function() {
        if (jQuery(this).is(":checked")) {
            jQuery('.mail_logo_settings').parent().parent().css('display', 'table-row');
        } else {
            jQuery('.mail_logo_settings').parent().parent().css('display', 'none');
        }
    });
});