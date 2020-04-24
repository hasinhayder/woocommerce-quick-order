; (function ($) {
    $(document).ready(function () {

        $("#wqo_genpw").on('click', function () {
            $.post(wqo.ajax_url, { 'action': 'wqo_genpw', 'nonce': wqo.nonce }, function (data) {
                $("#password").val(data);
            });
            //return false; 
        });
        $("#coupon").on('click', function () {
            if ($(this).attr('checked')) {
                $("#discount-label").html(wqo.dc);
                $("#discount").attr("placeholder", wqo.cc);
            } else {
                $("#discount-label").html(wqo.dt);
                $("#discount").attr("placeholder", wqo.dt);
            }
        });

        $("#email").on('blur', function () {
            $("#lmsg").show();
            let email = $(this).val();
            //alert(wqo.ajax_url);
            $.post(wqo.ajax_url, { 'action': 'wqo_fetch_user', 'email': email, 'nonce': wqo.nonce }, function (data) {
                console.log(data.fn);
                $("#first_name").val(data.fn);
                $("#last_name").val(data.ln);
                $("#customer_id").val(data.id);

                if (!data.error) {
                    $("#first_name").attr('readonly', 'readonly');
                    $("#last_name").attr('readonly', 'readonly');
                    $("#password_container").hide();
                } else {
                    $("#password_container").show();
                    $("#first_name").removeAttr('readonly')
                    $("#last_name").removeAttr('readonly');
                }
                $("#lmsg").hide();

            }, "json");
        });
    });
})(jQuery);