<?php
/*
    Plugin Name: WooCommerce Quick Order
    Plugin URI:
    Description: Friendly Description
    Version: 1.0.0
    Author: Plain Text Author Name
    Author URI:
    License: GPLv2 or later
    Text Domain: wqo
     */

function wqo_scripts($hook) {
    if ('toplevel_page_quick-order-create' == $hook) {
        wp_enqueue_style('wqo-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), time());
        wp_enqueue_script('wqo-script', plugin_dir_url(__FILE__) . 'assets/js/wqo.js', array('jquery'), time(), true);
        $nonce = wp_create_nonce('wqo');
        wp_localize_script('wqo-script', 'wqo', array(
            'nonce' => $nonce, 
            'ajax_url' => admin_url('admin-ajax.php'),
            'dc'=>__('Discount Coupon','wqo'),
            'cc'=>__('Coupon Code','wqo'),
            'dt'=>__('Discount In Taka','wqo')
        ));
    }
}
add_action('admin_enqueue_scripts', 'wqo_scripts');

add_action('admin_menu', function () {
    add_menu_page(
        __('Quick Order Create', 'wqo'),
        __('WC Quick Order', 'wqo'),
        'manage_options',
        'quick-order-create',
        'wqo_admin_page'
    );
});

function wqo_admin_page() {
?>
    <h2><?php _e('Quick Order Create', 'wqo'); ?></h2>

    <div class="wqo-form-wrapper">
        <div class="wqo-form-title">
            <h4><?php _e('WooCommerce Quick Order Create', 'wqo'); ?></h4>
        </div>
        <div class='wqo-form-container'>
            <form class='pure-form pure-form-aligned' method='POST'>
                <fieldset>
                    <input type='hidden' name='customer_id' id='customer_id' value='0'>
                    <div class='pure-control-group'>
                        <?php $label = __('Email Address', 'wqo'); ?>
                        <label for='name'><?php echo $label; ?></label>
                        <input class='wqo-control' required name='email' id='email' type='email' placeholder='<?php echo $label; ?>'>
                        <span class='pure-form-message-inline' style="display:none" id='lmsg'>Checking For Existing User</span>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('First Name', 'wqo'); ?>
                        <label for='first_name'><?php echo $label; ?></label>
                        <input class='wqo-control' required name='first_name' id='first_name' type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Last Name', 'wqo'); ?>
                        <label for='last_name'><?php echo $label; ?></label>
                        <input class='wqo-control' required name='last_name' id='last_name' type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group' id='password_container'>
                        <?php $label = __('Password', 'wqo'); ?>
                        <label for='password'><?php echo $label; ?></label>
                        <input class='wqo-control-right-gap' name='password' id='password' type='text' placeholder='<?php echo $label; ?>'>
                        <button type='button' id='wqo_genpw' class="button button-primary button-hero">
                            <?php _e('Generate', 'wqo'); ?>
                        </button>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Phone Number', 'wqo'); ?>
                        <label for='phone'><?php echo $label; ?></label>
                        <input class='wqo-control' name='phone' id='phone' type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Discount in Taka', 'wqo'); ?>
                        <label id="discount-label" for="discount"><?php echo $label; ?></label>
                        <input class='wqo-control' name="discount" id="discount" type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group' style="margin-top:20px;margin-bottom:20px;">
                        <?php $label = __('I want to input coupon code', 'wqo'); ?>
                        <label for='coupon'></label>
                        <input type='checkbox' name='coupon' id='coupon' value='1' /><?php echo $label; ?>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Product Name', 'wqo'); ?>
                        <label for='item'><?php echo $label; ?></label>
                        <select class='wqo-control' name='item' id='item'>
                            <option value="0">Select One</option>
                            <?php
                            $products = wc_get_products(array('post_status' => 'published'));
                            foreach ($products as $product) {
                            ?>
                                <option value='<?php echo $product->get_ID(); ?>''><?php echo $product->get_Name(); ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class=' pure-control-group'>
                                    <?php $label = __('Order Note', 'wqo'); ?>
                                    <label for='note'><?php echo $label; ?></label>
                                    <input class='wqo-control' name='note' id="note" type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group' style='margin-top:20px;'>
                        <label></label>
                        <button type='submit' name='submit' class='button button-primary button-hero'>
                            <?php _e('Create Order', 'wqo'); ?>
                        </button>
                    </div>

                    <?php
                    if (isset($_POST['submit'])) {
                        echo '<hr/>';
                        wqo_process_submission();
                    }
                    ?>

                </fieldset>
            </form>
        </div>

    </div>

<?php

}

add_action('wp_ajax_wqo_genpw', function () {
    echo wp_generate_password(12);
    die();
});

add_action('wp_ajax_wqo_fetch_user', function () {
    $nonce = sanitize_text_field($_POST['nonce']);
    $email = sanitize_text_field($_POST['email']);
    $action = 'wqo';
    if (wp_verify_nonce($nonce, $action)) {
        $user = get_user_by('email', $email);
        if ($user) {
            echo json_encode(array('error' => false, 'id' => $user->ID, 'fn' => $user->first_name, 'ln' => $user->last_name));
        } else {
            echo json_encode(array('error' => true, 'id' => 0, 'fn' => 'Not Found', 'ln' => 'Not Found'));
        }
    }
    die();
});

function wqo_process_submission() {
    if ($_POST['customer_id'] == 0) {
        $email = sanitize_text_field($_POST['email']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $password = sanitize_text_field($_POST['password']);
        $customer = wp_create_user($email, $password, $email);
        update_user_meta($customer, 'first_name', $first_name);
        update_user_meta($customer, 'last_word', $last_name);
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

    $discount = sanitize_text_field($_POST['discount']);
    if ($discount == '') {
        $discount = 0;
    }
    $isCoupon = (isset($_POST['coupon'])) ? true : false;

    $checkout = WC()->checkout();
    $phone = sanitize_text_field($_POST['phone']);
    $order_id = $checkout->create_order(array('billing_phone' => $phone, 'billing_email' => $customer->user_email, 'payment_method' => 'cash'));
    $order = wc_get_order($order_id);
    update_post_meta($order_id, '_customer_user', $customer->ID);
    if ($isCoupon) {
        echo "Coupon {$discount}<br/>";
        $order->apply_coupon($discount);
    } elseif ($discount > 0) {
        echo "Disc {$discount}<br/>";
        $total = $order->calculate_totals();
        echo $total . ":<br/>";
        echo $total - floatval($discount) . ":<br/>";
        $order->set_discount_total($discount);
        $order->set_total($total - floatval($discount));
    }
    if (isset($_POST['note']) && !empty($_POST['note'])) {
        $order->add_order_note(sanitize_text_field($_POST['note']));
    }
    $order->set_status('completed');
    $order->payment_complete();
    $order->save();
    $cart->empty_cart();
    do_action('woocommerce_order_status_completed', $order_id);
    printf("<a target='_blank' href='%s' class='button button-primary button-hero'>%s %s</a>", $order->get_edit_order_url(), __('Edit This Order', 'wqo'), $order_id);
}
