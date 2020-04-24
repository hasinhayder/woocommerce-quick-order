<?php
/*
Plugin Name: Quick Order For WooCommerce
Plugin URI: https://github.com/hasinhayder/woocommerce-quick-order
Description: Quickly create WooCommerce order for existing and new customers. 
Version: 1.0.0
Author: Hasin Hayder
Author URI: https://hasin.me
License: GPLv2 or later
Text Domain: qofw
*/

function qofw_scripts($hook) {
    if ('toplevel_page_quick-order-create' == $hook) {
        wp_enqueue_style('qofw-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
        wp_enqueue_script('qofw-script', plugin_dir_url(__FILE__) . 'assets/js/qofw.js', array('jquery', 'thickbox'), '1.0.0', true);
        $nonce = wp_create_nonce('qofw');
        wp_localize_script('qofw-script', 'qofw', array(
            'nonce' => $nonce,
            'ajax_url' => admin_url('admin-ajax.php'),
            'dc' => __('Discount Coupon', 'qofw'),
            'cc' => __('Coupon Code', 'qofw'),
            'dt' => __('Discount In Taka', 'qofw'),
            'pt' => __('WooCommerce Quick Order', 'qofw'), //plugin title
        ));
        add_thickbox();
    }
}
add_action('admin_enqueue_scripts', 'qofw_scripts');

add_action('admin_menu', function () {
    add_menu_page(
        __('Quick Order Create', 'qofw'),
        __('WC Quick Order', 'qofw'),
        'manage_woocommerce',
        'quick-order-create',
        'qofw_admin_page'
    );
});

function qofw_admin_page() {
?>
    <div class="qofw-form-wrapper">
        <div class="qofw-form-title">
            <h4><?php _e('WooCommerce Quick Order', 'qofw'); ?></h4>
        </div>
        <div class='qofw-form-container'>
            <div class="qofw-form">
                <form action='<?php echo esc_url(admin_url('admin-post.php')); ?>' class='pure-form pure-form-aligned' method='POST'>
                    <fieldset>
                        <input type='hidden' name='customer_id' id='customer_id' value='0'>
                        <div class='pure-control-group'>
                            <?php $label = __('Email Address', 'qofw'); ?>
                            <label for='name'><?php echo $label; ?></label>
                            <input class='qofw-control' required name='email' id='email' type='email' placeholder='<?php echo $label; ?>'>
                        </div>

                        <div class='pure-control-group'>
                            <?php $label = __('First Name', 'qofw'); ?>
                            <label for='first_name'><?php echo $label; ?></label>
                            <input class='qofw-control' required name='first_name' id='first_name' type='text' placeholder='<?php echo $label; ?>'>
                        </div>

                        <div class='pure-control-group'>
                            <?php $label = __('Last Name', 'qofw'); ?>
                            <label for='last_name'><?php echo $label; ?></label>
                            <input class='qofw-control' required name='last_name' id='last_name' type='text' placeholder='<?php echo $label; ?>'>
                        </div>

                        <div class='pure-control-group' id='password_container'>
                            <?php $label = __('Password', 'qofw'); ?>
                            <label for='password'><?php echo $label; ?></label>
                            <input class='qofw-control-right-gap' name='password' id='password' type='text' placeholder='<?php echo $label; ?>'>
                            <button type='button' id='qofw_genpw' class="button button-primary button-hero">
                                <?php _e('Generate', 'qofw'); ?>
                            </button>
                        </div>

                        <div class='pure-control-group'>
                            <?php $label = __('Phone Number', 'qofw'); ?>
                            <label for='phone'><?php echo $label; ?></label>
                            <input class='qofw-control' name='phone' id='phone' type='text' placeholder='<?php echo $label; ?>'>
                        </div>

                        <div class='pure-control-group'>
                            <?php $label = __('Discount in Taka', 'qofw'); ?>
                            <label id="discount-label" for="discount"><?php echo $label; ?></label>
                            <input class='qofw-control' name="discount" id="discount" type='text' placeholder='<?php echo $label; ?>'>
                        </div>

                        <div class='pure-control-group' style="margin-top:20px;margin-bottom:20px;">
                            <?php $label = __('I want to input coupon code', 'qofw'); ?>
                            <label for='coupon'></label>
                            <input type='checkbox' name='coupon' id='coupon' value='1' /><?php echo $label; ?>
                        </div>

                        <div class='pure-control-group'>
                            <?php $label = __('Product Name', 'qofw'); ?>
                            <label for='item'><?php echo $label; ?></label>
                            <select class='qofw-control' name='item' id='item'>
                                <option value="0"><?php _e('Select One', 'qofw'); ?></option>
                                <?php
                                $products = wc_get_products(array('post_status' => 'published', 'posts_per_page' => -1));
                                foreach ($products as $product) {
                                ?>
                                    <option value='<?php echo $product->get_ID(); ?>''><?php echo $product->get_Name(); ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>

                        <div class=' pure-control-group'>
                                        <?php $label = __('Order Note', 'qofw'); ?>
                                        <label for='note'><?php echo $label; ?></label>
                                        <input class='qofw-control' name='note' id="note" type='text' placeholder='<?php echo $label; ?>'>
                        </div>

                        <div class='pure-control-group' style='margin-top:20px;'>
                            <label></label>
                            <button type='submit' name='submit' class='button button-primary button-hero'>
                                <?php _e('Create Order', 'qofw'); ?>
                            </button>
                        </div>
                    </fieldset>
                    <input type="hidden" name="action" value="qofw_form">
                    <input type="hidden" name="qofw_identifier" value="<?php echo md5(time()); ?>">
                    <?php wp_nonce_field('qofw_form', 'qofw_form_nonce'); ?>
                </form>
            </div>
            <div class="qofw-info">
            </div>
            <div class="qofw-clearfix"></div>
        </div>

    </div>
    <div id="qofw-modal">
        <div class="qofw-modal-content">
            <?php
            if (isset($_GET['order_id'])) {
                do_action('qofw_order_processing_complete', sanitize_text_field($_GET['order_id']));
            }
            ?>
        </div>
    </div>

<?php

}
add_action('admin_post_qofw_form', function () {
    if (isset($_POST['submit'])) {
        $order_id =  qofw_process_submission();
        wp_safe_redirect(
            esc_url_raw(
                add_query_arg('order_id', $order_id, admin_url('admin.php?page=quick-order-create'))
            )
        );
    }
});


add_action('wp_ajax_qofw_genpw', function () {
    $nonce = sanitize_text_field($_POST['nonce']);
    $action = 'qofw';
    if (wp_verify_nonce($nonce, $action)) {
        echo wp_generate_password(12);
    }
    die();
});

add_action('wp_ajax_qofw_fetch_user', function () {
    $nonce = sanitize_text_field($_POST['nonce']);
    $email = strtolower(sanitize_text_field($_POST['email']));
    $action = 'qofw';
    if (wp_verify_nonce($nonce, $action)) {
        $user = get_user_by('email', $email);
        if ($user) {
            echo json_encode(array(
                'error' => false,
                'id' => $user->ID,
                'fn' => $user->first_name,
                'ln' => $user->last_name,
                'pn' => get_user_meta($user->ID, 'phone_number', true)
            ));
        } else {
            echo json_encode(array(
                'error' => true,
                'id' => 0,
                'fn' => __('Not Found', 'qofw'),
                'ln' => __('Not Found', 'qofw'),
                'pn' => ''
            ));
        }
    }
    die();
});

function qofw_process_submission() {
    $qofw_order_identifier = sanitize_text_field($_POST['qofw_identifier']);
    $processed = get_transient("qofw{$qofw_order_identifier}");
    if ($processed) {
        return $processed;
    }
    if (wp_verify_nonce(sanitize_text_field($_POST['qofw_form_nonce']), 'qofw_form')) {
        if (sanitize_text_field($_POST['customer_id']) == 0) {
            $email = strtolower(sanitize_text_field($_POST['email']));
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $password = sanitize_text_field($_POST['password']);
            $phone_number = sanitize_text_field($_POST['phone']);
            $customer = wp_create_user($email, $password, $email);
            update_user_meta($customer, 'first_name', $first_name);
            update_user_meta($customer, 'last_name', $last_name);
            update_user_meta($customer, 'phone_number', $phone_number);
            $customer = new WP_User($customer);
        } else {
            $customer = new WP_User(sanitize_text_field($_POST['customer_id']));
        }
        WC()->frontend_includes();
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
        WC()->customer = new WC_Customer($customer->ID, 1);

        $cart = new WC_Cart();
        WC()->cart = $cart;
        $cart->empty_cart();
        $cart->add_to_cart(sanitize_text_field($_POST['item']), 1);

        $discount = trim(sanitize_text_field($_POST['discount']));
        if ($discount == '') {
            $discount = 0;
        }
        $isCoupon = (isset($_POST['coupon'])) ? true : false;

        $checkout = WC()->checkout();
        $phone = sanitize_text_field($_POST['phone']);
        $order_id = $checkout->create_order(array(
            'billing_phone' => $phone,
            'billing_email' => $customer->user_email,
            'payment_method' => 'cash',
            'billing_first_name' => $customer->first_name,
            'billing_last_name' => $customer->last_name,
        ));

        set_transient("qofw{$qofw_order_identifier}", $order_id, 60);

        $order = wc_get_order($order_id);
        update_post_meta($order_id, '_customer_user', $customer->ID);
        if ($isCoupon) {
            $order->apply_coupon($discount);
        } elseif ($discount > 0) {
            $total = $order->calculate_totals();
            $order->set_discount_total($discount);
            $order->set_total($total - floatval($discount));
        }
        if (isset($_POST['note']) && !empty($_POST['note'])) {
            $order_note = apply_filters('qofw_order_note', sanitize_text_field($_POST['note']), $order_id);
            $order->add_order_note($order_note);
        }
        $order_status = apply_filters('qofw_order_status', 'processing');
        $order->set_status($order_status);
        do_action('qofw_order_complete', $order_id);
        return $order->save();
    }
}
add_action('qofw_order_processing_complete', function ($order_id) {
    $order = wc_get_order($order_id);
    $message =  __("<p>Your order number %s is now complete. Please click the next button to edit this order</p><p>%s</p>", 'qofw');
    $order_button = sprintf("<a target='_blank' href='%s' id='qofw-edit-button' class='button button-primary button-hero'>%s %s</a>", $order->get_edit_order_url(), __('Edit Order # ', 'qofw'), $order_id);

    printf($message, $order_id, $order_button);
});
