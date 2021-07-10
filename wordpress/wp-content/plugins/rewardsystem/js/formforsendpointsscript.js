jQuery(function ($) {
    var formforsendpoints = {
        init: function () {
            $(document.body).on('click', '#rs_send_points_submit_button', this.formforsendpointsclick);
            jQuery(".error").hide();
            jQuery(".success_info").hide();
        },
        formforsendpointsclick: function (evt) {
            evt.preventDefault();
            var $form = $(evt.target);
            var sendpoints_current_user_points = formforsendpoints_variable_js.currentuserpoint;
            var value = jQuery('#rs_select_user').val();

            var send_points = jQuery("#rs_total_reward_value_send").val();
            var send_points_validated = /^[0-9\b]+$/.test(send_points);
            var restrictpoint = formforsendpoints_variable_js.limittosendreq;

            var restrict_point_enable = formforsendpoints_variable_js.sendpointlimit;
            if (restrict_point_enable == '1') {
                if (restrictpoint != '' && restrictpoint != '0') {
                    if (send_points > restrictpoint) {
                        jQuery("#points_limit_error").fadeIn().delay(5000).fadeOut();
                        return false;
                    }
                }
            }


            if (send_points == "") {
                jQuery("#points_empty_error").fadeIn().delay(5000).fadeOut();
                return false;
            } else {

                jQuery("#points_empty_error").hide();
                if (send_points_validated == false) {
                    jQuery("#points_number_error").fadeIn().delay(5000).fadeOut();
                    return false;
                } else {

                    jQuery("#points_number_error").hide();
                }

            }
            if (value == '') {
                jQuery("#user_empty_error").fadeIn().delay(5000).fadeOut();
                return false;
            }
            jQuery(".success_info").show();
            jQuery(".success_info").fadeOut(3000);
            jQuery("#sendpoint_form")[0].reset();
            var send_request_user_id = formforsendpoints_variable_js.user_id;

            var send_request_user_name = formforsendpoints_variable_js.username;
            var send_default_status = "Due";
            var send_form_params = ({
                action: "rs_send_form_value",
                points_to_send: send_points,
                selecteduserforsend: value,
                userid_of_send_request: send_request_user_id,
                username_of_send_request: send_request_user_name,
                sender_current_points: sendpoints_current_user_points,
                send_default_status: send_default_status,
            });
            jQuery.post(formforsendpoints_variable_js.wp_ajax_url, send_form_params, function (response) {
                console.log('Got this from the server: ' + response);
            });
            return false;
        },
    };
    formforsendpoints.init();
});




