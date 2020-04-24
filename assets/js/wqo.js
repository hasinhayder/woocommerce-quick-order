; (function ($) {
    $(document).ready(function () {

        $("#wqo_genpw").on('click', function () {
            $.post(wqo.ajax_url, { 'action': 'wqo_genpw', 'nonce': wqo.nonce }, function (data) {
                $("#password").val(data);
            });
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
            if($(this).val()==''){
                return;
            }
            $("#first_name").val('');
            $("#last_name").val('');
            let email = $(this).val();
            //alert(wqo.ajax_url);
            $.post(wqo.ajax_url, { 'action': 'wqo_fetch_user', 'email': email, 'nonce': wqo.nonce }, function (data) {
                if ($("#first_name").val() == '') {
                    $("#first_name").val(data.fn);
                }
                if ($("#last_name").val() == '') {
                    $("#last_name").val(data.ln);
                }
                $("#phone").val(data.pn);
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

            }, "json");
        });


        if ($('#wqo-edit-button').length > 0) {
            tb_show(wqo.pt, "#TB_inline?inlineId=wqo-modal&width=700");
        }
    });
})(jQuery);