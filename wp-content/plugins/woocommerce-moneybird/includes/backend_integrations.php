<?php

namespace ExtensionTree\WCMoneyBird;

use WC_MoneyBird2;

function woocommerce_product_after_variable_attributes($loop, /** @noinspection PhpUnusedParameterInspection */
                                                       $variation_data, $variation) {
    // Render Moneybird settings for individual product variations
    $wcmb = WCMB();
    if (empty($wcmb)) {
        return;
    }
    $options = array_merge(
        array('' => __( 'Same as parent', 'woocommerce' )),
        $wcmb->get_revenue_ledger_accounts()
    );

    $mb_revenue_ledger_account = get_post_meta($variation->ID, '_mb_revenue_ledger_account_id', true);
    if (!$mb_revenue_ledger_account) {
        $mb_revenue_ledger_account = '';
    }

    woocommerce_wp_select(
        array(
            'id'            => "variable_mb_revenue_ledger_account{$loop}",
            'name'          => "variable_mb_revenue_ledger_account[{$loop}]",
            'value'         => $mb_revenue_ledger_account,
            'label'         => __('Moneybird revenue ledger account', 'woocommerce_moneybird'),
            'options'       => $options,
            'wrapper_class' => 'form-row form-row-full',
        )
    );
}


function woocommerce_save_product_variation($variation_id, $i) {
    // Save Moneybird settings for individual product variations
    if (!isset($_POST['variable_mb_revenue_ledger_account'])) {
        return;
    }

    if (!isset($_POST['variable_mb_revenue_ledger_account'][$i])) {
        return;
    }

    $val = wc_clean( wp_unslash( $_POST['variable_mb_revenue_ledger_account'][ $i ] ) );
    update_post_meta($variation_id, '_mb_revenue_ledger_account_id', $val);
}


function render_moneybird_invoice_link($invoice, $order) {
    $state_names = array(
        'draft' => __('draft', 'woocommerce_moneybird'),
        'open' => __('open', 'woocommerce_moneybird'),
        'scheduled' => __('scheduled', 'woocommerce_moneybird'),
        'pending_payment' => __('pending payment', 'woocommerce_moneybird'),
        'paid' => __('paid', 'woocommerce_moneybird'),
        'late' => __('late', 'woocommerce_moneybird'),
        'reminded' => __('reminded', 'woocommerce_moneybird'),
        'uncollectible' => __('uncollectible', 'woocommerce_moneybird'),
    );

    $deeplink = sprintf(
        'https://moneybird.com/%s/sales_invoices/%s',
        $invoice->administration_id,
        $invoice->id
    );
    $state_color = '#333';
    if (in_array($invoice->state, array('open', 'pending_payment'))) {
        $state_color = '#f60';
    } elseif (in_array($invoice->state, array('late', 'reminded', 'uncollectible'))) {
        $state_color = '#c00';
    } elseif ($invoice->state == 'paid') {
        $state_color = '#693';
    }
    _e('Invoice', 'woocommerce_moneybird');
    echo " <a href=\"$deeplink\">";
    if ($invoice->invoice_id) {
        echo $invoice->invoice_id . '</a> (<span style="font-weight: bold; color: ' . $state_color . '">' . ((isset($state_names[$invoice->state])) ? $state_names[$invoice->state] : $invoice->state) . '</span>)';
    } else {
        echo __('Draft', 'woocommerce_moneybird') . '</a>';
    }
    $invoice_pdf_url = wcmb_get_invoice_pdf_url($order);
    $invoice_packing_slip_pdf_url = wcmb_get_packing_slip_pdf_url($order);
    echo "&nbsp;|&nbsp;<span class=\"dashicons dashicons-media-document\"></span><a href=\"$invoice_pdf_url\">PDF</a>";
    echo "&nbsp;|&nbsp;<span class=\"dashicons dashicons-media-document\"></span><a href=\"$invoice_packing_slip_pdf_url\">";
    _e('Packing slip', 'woocommerce_moneybird');
    echo "</a>";
}


function render_moneybird_estimate_link($estimate) {
    $state_names = array(
        'draft' => __('draft', 'woocommerce_moneybird'),
        'open' => __('open', 'woocommerce_moneybird'),
        'late' => __('late', 'woocommerce_moneybird'),
        'accepted' => __('accepted', 'woocommerce_moneybird'),
        'rejected' => __('rejected', 'woocommerce_moneybird'),
        'billed' => __('billed', 'woocommerce_moneybird'),
        'archived' => __('archived', 'woocommerce_moneybird'),
    );

    $deeplink = sprintf(
        'https://moneybird.com/%s/estimates/%s',
        $estimate->administration_id,
        $estimate->id
    );
    $state_color = '#333';
    if ($estimate->state == 'open') {
        $state_color = '#f60';
    } elseif (in_array($estimate->state, array('late', 'rejected'))) {
        $state_color = '#c00';
    } elseif (in_array($estimate->state, array('accepted', 'billed'))) {
        $state_color = '#693';
    }
    _e('Estimate', 'woocommerce_moneybird');
    echo " <a href=\"$deeplink\">";
    if ($estimate->estimate_id) {
        echo $estimate->estimate_id . '</a> (<span style="font-weight: bold; color: ' . $state_color . '">' . ((isset($state_names[$estimate->state])) ? $state_names[$estimate->state] : $estimate->state) . '</span>)';
    } else {
        echo __('Draft', 'woocommerce_moneybird') . '</a>';
    }
}

function render_order_moneybird_block($order) {
    // Render Moneybird block in the order details block on the order edit page
    $wcmb = WCMB();
    if (empty($wcmb)) {
        return;
    }
    $order_type = is_callable(array($order, 'get_type')) ? $order->get_type() : 'shop_order';
    if ($order_type != 'shop_order') {
        return;
    }
    $invoice_id = trim($order->get_meta('moneybird_invoice_id', true));
    $invoice = $wcmb->get_invoice_from_order($order);
    $estimate_id = trim($order->get_meta('moneybird_estimate_id', true));
    $estimate = $wcmb->get_estimate_from_order($order);
    ?>
    <p class="form-field form-field-wide">
        <span style="display: block; padding: 1rem 0 0.5rem 0; font-weight: bold; color: #000;">Moneybird</span>
        <?php
        // Estimate
        if ($estimate_id) {
            echo '<span style="display: block; clear: both; color: #333;">';
            if ($estimate) {
                render_moneybird_estimate_link($estimate);
            } else {
                echo(sprintf(
                        __('%s could not be loaded through API. <br/> To clear the link, remove custom field "%s".', 'woocommerce_moneybird'),
                        __('Estimate', 'woocommerce_moneybird'),
                        'moneybird_estimate_id'
                ));
            }
            echo "</span>";
        } elseif (!empty($order->get_meta('moneybird_queue_generate_estimate', true))) {
            echo '<span style="display: block; clear: both; color: #333;">';
            echo(sprintf(
                __('%s generation is queued...', 'woocommerce_moneybird'),
                __('Estimate', 'woocommerce_moneybird')
            ));
            echo "</span>";
        } elseif (empty($invoice_id) && ($wcmb->settings['estimate_enabled'] === 'yes')) {
            ?>
            <input type="button" value="<?php _e('Generate estimate', 'woocommerce_moneybird'); ?>" name="moneybird-estimate" class="button save_order" style="margin-bottom:0.5rem;" onClick="jQuery('select[name=wc_order_action]').val('moneybird-estimate'); jQuery('form#post').submit(); jQuery('form#order').submit();">
            <?php
        }
        // Invoice
        if ($invoice_id) {
            echo '<span style="display: block; clear: both; color: #333;">';
            if ($invoice) {
                render_moneybird_invoice_link($invoice, $order);
            } else {
                echo(sprintf(
                        __('%s could not be loaded through API. <br/> To clear the link, remove custom field "%s".', 'woocommerce_moneybird'),
                        __('Invoice', 'woocommerce_moneybird'),
                        'moneybird_invoice_id'
                ));
            }
            echo "</span>";
        } elseif (!empty($order->get_meta('moneybird_queue_generate', true))) {
            echo '<span style="display: block; clear: both; color: #333;">';
            echo(sprintf(
                __('%s generation is queued...', 'woocommerce_moneybird'),
                __('Invoice', 'woocommerce_moneybird')
            ));
            echo "</span>";
        } else {
            ?>
            <input type="button" value="<?php _e('Generate invoice', 'woocommerce_moneybird'); ?>" name="moneybird-invoice" class="button save_order" style="margin-bottom:0.5rem;" onClick="jQuery('select[name=wc_order_action]').val('moneybird-invoice'); jQuery('form#post').submit(); jQuery('form#order').submit();">
            <?php
        }
        ?>
    </p>
    <script>
        function generate_refund_invoice(refund_id) {
            jQuery('#moneybird_refundinvoice_'+refund_id).html("Busy...");
            var api_url = "<?php echo str_replace('http://', '//', admin_url('admin-ajax.php')); ?>";
            jQuery.post(api_url, {'action': 'wcmb_api', 'mb_action': 'generate_refund_invoice', 'refund_id': refund_id})
                .done(function(data) {
                    jQuery('#moneybird_refundinvoice_'+refund_id).replaceWith(data);
                });
        }

        function unlink_refund_invoice(refund_id) {
            jQuery('#moneybird_refundinvoice_'+refund_id).html("Busy...");
            var api_url = "<?php echo str_replace('http://', '//', admin_url('admin-ajax.php')); ?>";
            jQuery.post(api_url, {'action': 'wcmb_api', 'mb_action': 'unlink_refund_invoice', 'refund_id': refund_id})
                .done(function(data) {
                    jQuery('#moneybird_refundinvoice_'+refund_id).replaceWith(data);
                });
        }

        jQuery(document).ready(function() {
            jQuery('tr.refund').each(function() {
                var refund_tr = jQuery(this);
                var refund_id = refund_tr.attr('data-order_refund_id');
                var api_url = "<?php echo str_replace('http://', '//', admin_url('admin-ajax.php')); ?>";
                console.log(api_url);
                jQuery.get(api_url+'?action=wcmb_api&mb_action=load_moneybirdbox_refund&refund_id='+refund_id)
                    .done(function(data) {
                        refund_tr.find('td.name').append(data);
                    });
            });
        });
    </script>
    <?php
    do_action('woocommerce_moneybird_after_adminblock', $order, $invoice, $estimate);
}


function render_refund_moneybird_block($refund_id) {
    // Render Moneybird block for refund on order edit page
    $wcmb = WCMB();
    if (empty($wcmb)) {
        return;
    }
    $refund = wc_get_order($refund_id);
    $invoice_id = trim($refund->get_meta('moneybird_invoice_id', true));
    $invoice = $wcmb->get_invoice_from_order($refund);
    echo '<p id="moneybird_refundinvoice_'. $refund_id . '">';
    if ($invoice_id) {
        // Existing credit invoice
        echo '<b>Moneybird ' . __('invoice', 'woocommerce_moneybird') . '</b>: ';
        if ($invoice) {
            $wcmb->render_admin_invoice_link($invoice, $refund);
        } else {
            _e('not available', 'woocommerce_moneybird');
        }
        echo ' [<a style="cursor: pointer; text-decoration: underline;" onclick="unlink_refund_invoice(' . $refund_id . ');">';
        _e('unlink invoice', 'woocommerce_moneybird');
        echo '</a>]';
    } else {
        // No credit invoice
        ?>
        <input type="button" value="<?php _e('Generate Moneybird credit invoice', 'woocommerce_moneybird'); ?>" name="moneybird" class="button button-small" style="font-size: 11px; padding: 0 8px;" onclick="generate_refund_invoice(<?php echo $refund_id; ?>);"/>
        <?php
    }
    echo '</p>';
}


function render_moneybird_product_meta_box($post) {
    // Render Moneybird settings meta box on product edit page
    $wcmb = WCMB();
    if (empty($wcmb)) {
        return;
    }
    if (!isset($wcmb->settings['administration_id']) || empty($wcmb->settings['administration_id'])) {
        return;
    }

    // Ledger account
    $ledger_account_id = get_post_meta($post->ID, '_mb_revenue_ledger_account_id', true);
    if (!$ledger_account_id) {
        $ledger_account_id = '';
    }
    $revenue_ledger_accounts = $wcmb->get_revenue_ledger_accounts();
    $current_ledger_account_id = $wcmb->get_revenue_ledger_account_id($post->ID);
    if (!empty($current_ledger_account_id)) {
        $current_name = $revenue_ledger_accounts[$current_ledger_account_id];
    } else {
        $current_name = __('Moneybird default', 'woocommerce_moneybird');
    }
    $current_reason = $wcmb->get_revenue_ledger_account_reason($post->ID);

    // Project
    $projects = $wcmb->get_projects();
    $project_id = get_post_meta($post->ID, '_mb_project_id', true);
    if (!$project_id) {
        $project_id = '';
    } elseif (!isset($projects[$project_id])) {
        $project_id = '';
    }

    // Workflow
    $workflow_id = get_post_meta($post->ID, '_mb_workflow_id', true);
    if (!$workflow_id) {
        $workflow_id = '';
    }
    $workflows = $wcmb->get_workflows();
    if (!empty($workflow_id) && !isset($workflows[$workflow_id])) {
        $workflow_id = '';
    }

    // Document style
    $document_style_id = get_post_meta($post->ID, '_mb_document_style_id', true);
    if (!$document_style_id) {
        $document_style_id = '';
    }
    $document_styles = $wcmb->get_document_styles();
    if (!empty($document_style_id) && !isset($document_styles[$document_style_id])) {
        $document_style_id = '';
    }

    // Extra product text
    $extra_product_text = get_post_meta($post->ID, '_mb_extra_product_text', true);
    ?>

    <!-- Exclude product -->
    <p>
        <label><b><?php _e('Exclude product', 'woocommerce_moneybird'); ?></b></label><br/>
        <?php $excluded = (get_post_meta($post->ID, '_mb_exclude', true) === 'yes'); ?>
        <input type="checkbox" name="wc_moneybird_exclude" id="wc_moneybird_exclude" value="yes" <?php if ($excluded): ?>checked="checked"<?php endif; ?>>
        <?php _e('Exclude this product from Moneybird invoices.', 'woocommerce_moneybird'); ?>
    </p>

    <div class="conditional_display" data-display-dependency="#wc_moneybird_exclude" data-display-if="#wc_moneybird_exclude:not(:checked)">
        <!-- Revenue ledger account -->
        <p>
            <label><b><?php _e('Revenue ledger account', 'woocommerce_moneybird'); ?></b></label>
            <span class="woocommerce-help-tip" data-tip="<?php _e('Override the default revenue ledger account for this product.', 'woocommerce_moneybird'); ?>"></span>
        </p>
        <p>
            <select name="wc_moneybird_revenue_ledger_account_id" id="wc_moneybird_revenue_ledger_account_id" class="postbox" style="margin-bottom: 5px;">
                <option value="" <?php selected($ledger_account_id, ''); ?>><?php _e('As specified by the product category or store-wide default', 'woocommerce_moneybird'); ?></option>
                <?php foreach ($revenue_ledger_accounts as $id => $name) { ?>
                    <option value="<?php echo $id; ?>" <?php selected($ledger_account_id, $id); ?>><?php echo $name; ?></option>
                <?php } ?>
            </select>
            <br/>
            <span style="margin-top: 0; color: grey;">
                    <?php echo sprintf(__('Revenue ledger account resulting from the current settings: <i><b>%s</b></i> (due to %s).', 'woocommerce_moneybird'), $current_name, $current_reason); ?>
                </span>
        </p>

        <!-- Project -->
        <p>
            <label><b>Moneybird project</b></label>
            <span class="woocommerce-help-tip" data-tip="<?php _e('Override the default Moneybird project for this product.', 'woocommerce_moneybird'); ?>"></span>
        </p>
        <p>
            <select name="wc_moneybird_project_id" id="wc_moneybird_project_id" class="postbox">
                <option value="" <?php selected($project_id, ''); ?>><?php _e('As specified in store-wide plugin settings', 'woocommerce_moneybird'); ?></option>
                <?php foreach ($projects as $id => $name) { ?>
                    <option value="<?php echo $id; ?>" <?php selected($project_id, $id); ?>><?php echo $name; ?></option>
                <?php } ?>
            </select>
        </p>

        <!-- Workflow -->
        <p>
            <label><b><?php _e('Invoice workflow', 'woocommerce_moneybird'); ?></b></label>
            <span class="woocommerce-help-tip" data-tip="<?php _e('Override the default workflow for this product. Note that a product-specific workflow will be applied independently of the order payment status. This setting does not apply to credit invoices.', 'woocommerce_moneybird'); ?>"></span>
        </p>
        <p>
            <select name="wc_moneybird_workflow_id" id="wc_moneybird_workflow_id" class="postbox">
                <option value="" <?php selected($workflow_id, ''); ?>><?php _e('As specified in store-wide plugin settings', 'woocommerce_moneybird'); ?></option>
                <?php foreach ($workflows as $id => $name) { ?>
                    <option value="<?php echo $id; ?>" <?php selected($workflow_id, $id); ?>><?php echo $name; ?></option>
                <?php } ?>
            </select>
        </p>

        <!-- Document style -->
        <p>
            <label><b><?php _e('Document style', 'woocommerce_moneybird'); ?></b></label>
            <span class="woocommerce-help-tip" data-tip="<?php _e('Override the default document style for this product.', 'woocommerce_moneybird'); ?>"></span>
        </p>
        <p>
            <select name="wc_moneybird_document_style_id" id="wc_moneybird_document_style_id" class="postbox">
                <option value="" <?php selected($document_style_id, ''); ?>><?php _e('As specified in store-wide plugin settings', 'woocommerce_moneybird'); ?></option>
                <?php foreach ($document_styles as $id => $name) { ?>
                    <option value="<?php echo $id; ?>" <?php selected($document_style_id, $id); ?>><?php echo $name; ?></option>
                <?php } ?>
            </select>
        </p>

        <!-- Custom product data -->
        <p>
            <label for="wc_moneybird_extra_product_text"><b><?php _e('Additional text on invoice/estimate', 'woocommerce_moneybird'); ?></b></label>
            <span class="woocommerce-help-tip" data-tip="<?php _e('Extra text that will be added to invoice/estimate lines corresponding to this product.', 'woocommerce_moneybird'); ?>"></span>
        </p>
        <p>
            <textarea name="wc_moneybird_extra_product_text" id="wc_moneybird_extra_product_text" style="width:100%;"><?php echo $extra_product_text; ?></textarea>
        </p>
    </div>

    <?php
}


function render_moneybird_user_profile_block($user) { ?>
    <h3>Moneybird</h3>
    <table class="form-table">
        <tr>
            <th><label for="moneybird_customer_id"><?php _e('Moneybird customer ID', 'woocommerce_moneybird'); ?></label></th>
            <td>
            <input type="text" name="moneybird_customer_id" id="moneybird_customer_id" value="<?php echo esc_attr(get_the_author_meta('moneybird_customer_id', $user->ID)); ?>" /><br />
            <p class="description">
                <?php _e('Customer ID of the Moneybird contact to use on invoices and estimates for this user.', 'woocommerce_moneybird'); ?><br/>
                <?php _e('Leave empty to automatically select or create the Moneybird contact.', 'woocommerce_moneybird'); ?>
            </p>
            </td>
        </tr>
    </table>
<?php
}


function add_meta_boxes() {
    // Add meta boxes for Moneybird settings
    add_meta_box(
        'wc_moneybird_product_options',
        'Moneybird',
        '\ExtensionTree\WCMoneyBird\render_moneybird_product_meta_box',
        'product'
    );
}


function save_post($post_id) {
    // Save Moneybird settings on product edit page

    if (!isset($_POST['wc_moneybird_revenue_ledger_account_id'])) {
        // No Moneybird-related fields
        return;
    }

    if (isset($_POST['wc_moneybird_exclude']) && ($_POST['wc_moneybird_exclude']=='yes')) {
        update_post_meta(
            $post_id,
            '_mb_exclude',
            'yes'
        );
    } else {
        delete_post_meta($post_id, '_mb_exclude');
    }
    $fields = array(
        'wc_moneybird_document_style_id' => '_mb_document_style_id',
        'wc_moneybird_revenue_ledger_account_id' => '_mb_revenue_ledger_account_id',
        'wc_moneybird_project_id' => '_mb_project_id',
        'wc_moneybird_workflow_id' => '_mb_workflow_id',
        'wc_moneybird_extra_product_text' => '_mb_extra_product_text'
    );
    foreach ($fields as $post_field => $meta_key) {
        if (array_key_exists($post_field, $_POST)) {
            update_post_meta(
                $post_id,
                $meta_key,
                $_POST[$post_field]
            );
        }
    }
}


function save_moneybird_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    update_user_meta($user_id, 'moneybird_customer_id', $_POST['moneybird_customer_id']);
    return true;
}


function edited_product_cat($term_id) {
    // Save Moneybird settings on product category edit page
    if (array_key_exists('wc_moneybird_revenue_ledger_account_id', $_POST)) {
        $account_id = $_POST['wc_moneybird_revenue_ledger_account_id'];
        update_term_meta($term_id, 'mb_revenue_ledger_account_id', $account_id);
    }
}


function product_cat_edit_form_fields($term) {
    // Render Moneybird settings on product category edit page
    $wcmb = WCMB();
    if (empty($wcmb)) {
        return;
    }
    if (!isset($wcmb->settings['administration_id']) || empty($wcmb->settings['administration_id'])) {
        return;
    }

    $term_id = $term->term_id;
    $ledger_account_id = get_term_meta($term_id, 'mb_revenue_ledger_account_id', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="mb_revenue_ledger_account_id"><?php _e('Moneybird revenue ledger account', 'woocommerce_moneybird'); ?></label></th>
        <td>
            <select name="wc_moneybird_revenue_ledger_account_id" id="wc_moneybird_revenue_ledger_account_id" class="postbox" style="margin-top:10px;">
                <option value="" <?php selected($ledger_account_id, ''); ?>><?php _e('As specified by the store-wide default', 'woocommerce_moneybird'); ?></option>
                <?php foreach ($wcmb->get_revenue_ledger_accounts() as $id => $name) { ?>
                    <option value="<?php echo $id; ?>" <?php selected($ledger_account_id, $id); ?>><?php echo $name; ?></option>
                <?php } ?>
            </select>
            <p class="description">
                <?php _e('You may link a product category to a specific revenue ledger account.', 'woocommerce_moneybird'); ?><br/>
                <?php _e('Revenue ledger accounts specified at the product level will take priority over the category setting.', 'woocommerce_moneybird'); ?><br/>
                <?php echo sprintf(__('The store-wide default revenue ledger account can be configured on the <a href="%s">plugin settings page</a>.', 'woocommerce_moneybird'), $wcmb->get_settings_url()); ?>
            </p>
        </td>
    </tr>
    <?php
}


function admin_enqueue_scripts($hook) {
    if (in_array($hook, array('post.php', 'woocommerce_page_wc-settings'))) {
        wp_enqueue_script(
            'woocommerce_moneybird_admin.js',
            plugins_url('../js/woocommerce_moneybird_admin.js', __FILE__ ),
            array('jquery'),
            WC_MONEYBIRD_VERSION,
            true
        );
    }
}


function debug_add_meta_box() {
    // Add Moneybird debug meta box to order edit page
    $screen = WC_MoneyBird2::is_hpos_active()
        ? wc_get_page_screen_id('shop-order')
        : 'shop_order';
    add_meta_box(
        'wc_moneybird_debug_order',
        'Moneybird debug',
        '\ExtensionTree\WCMoneyBird\render_moneybird_debug_order',
        $screen
    );
}

function render_moneybird_debug_order($post) {
    // Render Moneybird debug meta box on order edit page
    $wcmb = WCMB();
    if (empty($wcmb)) {
        return;
    }
    $post_id = $post->ID;
    if (!empty($_GET['wcmb-debug'])) {
        if (is_numeric($_GET['wcmb-debug']) && (intval($_GET['wcmb-debug']) > 1)) {
            $post_id = intval($_GET['wcmb-debug']);
        }
    }

    $order = wc_get_order($post_id);
    if (empty($order)) {
        return;
    }
    $parent_order = null;
    if ($order->get_type() == 'shop_order_refund') {
        $parent_order = wc_get_order($order->get_parent_id());
    }
    $tax_rate_mappings = $wcmb->get_tax_rate_mappings();
    $mb_tax_rates = $wcmb->get_mb_tax_rates();
    $wc_tax = new \WC_Tax();
    ?>
    <style>
        .wcmb-debug-block {
            display: none;
        }
    </style>
    <script>
        function wcmb_debug_toggle(blockname) {
            let block = jQuery('#wcmb-debug-' + blockname);
            if (block.hasClass('opened')) {
                block.hide();
                block.removeClass('opened');
            } else {
                let opened_blocks = jQuery('.wcmb-debug-block.opened');
                opened_blocks.hide();
                opened_blocks.removeClass('opened');
                block.show();
                block.addClass('opened');
            }
        }
        jQuery(document).ready(function() {
            jQuery('#wcmb-debug-menu .button').on('click', function() {
                var target = jQuery(this).data('wcmb-debug-target');
                if (target) {
                    wcmb_debug_toggle(target);
                }
            });
        });
    </script>
    <h3>
        <?php echo $order->get_type(); ?> <?php echo $order->get_id(); ?>
        <?php if ($parent_order): ?>
            Parent: <?php echo $parent_order->get_type(); ?> <?php echo $parent_order->get_id(); ?>
        <?php endif; ?>
    </h3>
    <div id="wcmb-debug-menu">
        <button class="button button-small" type="button" data-wcmb-debug-target="postmeta">Order data</button>
        <button class="button button-small" type="button" data-wcmb-debug-target="contact">Contact data</button>
        <button class="button button-small" type="button" data-wcmb-debug-target="items">Items</button>
        <button class="button button-small" type="button" data-wcmb-debug-target="tax">Tax rates</button>
        <button class="button button-small" type="button" data-wcmb-debug-target="invoice">Invoice</button>
    </div>
    <div class="wcmb-debug-block" id="wcmb-debug-postmeta">
        <pre><?php
        $base_data = $order->get_base_data();
        ksort($base_data);
        foreach ($base_data as $key => $value) {
            echo "<b>".$key."</b>\n";
            var_dump($value);
            echo "\n\n";
        }
        foreach ($order->get_meta_data() as $meta) {
            echo "<b>Meta: ".$meta->key."</b>\n";
            var_dump($meta->value);
            echo "\n\n";
        }
        ?></pre>
        <h3>Mollie transaction id</h3>
        <pre><?php var_dump($wcmb->get_payment_transaction_id($order)); ?></pre>
    </div>
    <div class="wcmb-debug-block" id="wcmb-debug-contact">
        <h3>Contact details</h3>
        <pre><?php var_dump($wcmb->get_order_contact_details(($parent_order) ? $parent_order : $order)); ?></pre>
    </div>
    <div class="wcmb-debug-block" id="wcmb-debug-items">
        <h3>Order total</h3>
        <pre><?php var_dump(floatval($order->get_total())); ?></pre>
        <hr/>
        <h3>Collected order items</h3>
        <pre><?php var_dump($wcmb->get_order_items($order, $tax_rate_mappings)); ?></pre>
        <hr/>
        <h3>Collected order fees</h3>
        <pre><?php var_dump($wcmb->get_order_fees($order, $tax_rate_mappings)); ?></pre>
        <hr/>
        <h3>Collected shipping costs</h3>
        <pre><?php var_dump($wcmb->get_order_shipping($order, $tax_rate_mappings)); ?></pre>
        <hr/>
        <h3>Gift card redemptions</h3>
        <pre><?php var_dump($wcmb->get_order_gift_card_redemptions($order)); ?></pre>
        <hr/>
        <h3>Coupon redemptions</h3>
        <pre><?php var_dump($wcmb->get_order_coupon_redemptions($order)); ?></pre>
        <hr/>

        <?php if (wc_tax_enabled()): ?>
        <h3>Item tax</h3>
        <pre><?php
        $order_taxes = $order->get_taxes();
        foreach ($order->get_items(apply_filters('woocommerce_admin_order_item_types', 'line_item')) as $item) {
            echo apply_filters('woocommerce_order_item_name', $item['name'], $item, true) . "\n";
            $tax_data = $item->get_taxes();
            $line_total_gross = $order->get_line_total($item);
            echo 'Line total gross: ' . $line_total_gross . "\n";
            foreach ($order_taxes as $tax_item) {
                $tax_item_id = $tax_item->get_rate_id();
                if (isset($tax_data['total'][$tax_item_id]) && is_numeric($tax_data['total'][$tax_item_id])) {
                    $tax_item_total = floatval($tax_data['total'][$tax_item_id]);
                } else {
                    $tax_item_total = 'none';
                }
                echo $wc_tax->get_rate_code($tax_item_id) . ': ' . $tax_item_total . "\n";
                echo "- Registered percentage: " . $tax_item->get_rate_percent() . "\n";
                if (is_float($tax_item_total)) {
                    if (abs($line_total_gross) > 1e-3) {
                        $pct = 100.0*($tax_item_total / $line_total_gross);
                    } else {
                        $pct = 0.0;
                    }

                    echo "- Calculated percentage: " . $pct . "\n";
                    $explicitly_linked_rate_id = $wcmb->get_mapped_mb_tax_rate_id($tax_item_id, $tax_rate_mappings, null, $pct);
                    if ($explicitly_linked_rate_id) {
                        echo "- Explicit link $tax_item_id &rarr; " . $explicitly_linked_rate_id . "\n";
                    } else {
                        // Try to find rate id based on rate code
                        $tax_item_id = $wcmb->get_wc_tax_rate_id_by_rate_code($tax_item->get_rate_code());
                        if ($tax_item_id && isset($tax_rate_mappings[(int)$tax_item_id])) {
                            echo "- Rate code link: $tax_item_id &rarr; " . $tax_rate_mappings[(int)$tax_item_id] . "\n";
                        } else {
                            echo "- Detecting on percentage $pct\n";
                            $percentage_matching_rate_id = $wcmb->detect_mb_taxrate($pct);
                            if ($percentage_matching_rate_id) {
                                echo "- Percentage matching: matched to MB rate $percentage_matching_rate_id\n";
                            } else {
                                echo "- Percentage matching: no match\n";
                            }
                        }
                    }
                }
            }
            echo "\n\n";
        }
        ?></pre><hr/>
        <?php endif; ?>
        <h3>Mapped custom fields</h3>
        <pre><?php
            $custom_fields = array();
            $custom_field_mappings = $wcmb->get_custom_field_mappings();
            $order_type = is_callable(array($order, 'get_type')) ? $order->get_type() : 'shop_order';
            foreach ($custom_field_mappings as $mapping) {
                if (($order_type == 'shop_order_refund') && !empty($parent_order)) {
                    $val = $wcmb->get_custom_field_value($parent_order, $mapping['wc']);
                } else {
                    $val = $wcmb->get_custom_field_value($order, $mapping['wc']);
                }
                $custom_fields[] = array(
                    'id'    => $mapping['mb'],
                    'wc'    => $mapping['wc'],
                    'value' => $val
                );
            }
            var_dump($custom_fields);
            ?>
        </pre>
        <hr/>
        <h3>Payments</h3>
        <pre><?php var_dump($wcmb->get_invoice_payments($order, null)); ?></pre>

    </div>
    <div class="wcmb-debug-block" id="wcmb-debug-tax">
        <?php
        ?>
        <h3>Mappings</h3>
        <pre><?php
            foreach ($tax_rate_mappings as $wc_tax_rate_id => $mb_tax_rate_id) {
                echo $wc_tax->get_rate_code($wc_tax_rate_id) . ' (' . $wc_tax->get_rate_percent($wc_tax_rate_id) . ')';
                if (isset($tax_rate_mappings[$wc_tax_rate_id]) && isset($mb_tax_rates[$mb_tax_rate_id])) {
                    echo ' &rarr; ' . $mb_tax_rates[$mb_tax_rate_id]->name . ' (' . $mb_tax_rate_id . ")";
                }
                echo "\n";
            }
            ?>
        </pre>
        <hr/>
        <h3>MB tax rates</h3>
        <pre><?php var_dump($mb_tax_rates); ?></pre>
    </div>
    <div class="wcmb-debug-block" id="wcmb-debug-invoice">
        <?php
        ?>
        <h3>Invoice object</h3>
        <?php
        $moneybird_invoice_id = $order->get_meta('moneybird_invoice_id', true);
        $invoice = null;
        if ($moneybird_invoice_id) {
            $invoice = $wcmb->mb_api->getSalesInvoice($moneybird_invoice_id);
        }
        ?>
        <pre><?php var_dump($invoice); ?></pre>
        <hr/>
    </div>
    <?php
}


function filter_wc_orders_list($query) {
    /*
    Filter WC orders list based on Moneybird invoice/estimate status
    GET parameter 'moneybird_filter' can be used to filter the list, possible values:
    
    - `invoice_yes`: only show orders that have an invoice in Moneybird
    - `invoice_no`: only show orders that do not have an invoice in Moneybird and are not in the invoicing queue
    - `invoice_pending`: only show orders that are in the invoicing queue
    - `invoice_no_or_pending`: only show orders that do not have an invoice in Moneybird (order can be in the invoicing queue)
    - `estimate_yes`: only show orders that have an estimate in Moneybird
    - `estimate_no`: only show orders that do not have an estimate in Moneybird and are not in the estimate generation queue
    - `estimate_pending`: only show orders that are in the estimate generation queue
    - `estimate_no_or_pending`: only show orders that do not have an estimate in Moneybird (order can be in the estimate generation queue)
    */

    if (!is_admin() || !isset($_GET['moneybird_filter'])) {
        return;
    }
    
    global $pagenow;

    if (($pagenow !== 'edit.php')  && ($query->query['post_type'] !== 'shop_order')) {
        return;
    }

    $meta_query = array();
    if ($_GET['moneybird_filter'] == 'invoice_yes') {
        $meta_query = array(array('key' => 'moneybird_invoice_id', 'compare' => 'EXISTS'));
    } elseif ($_GET['moneybird_filter'] == 'invoice_no_or_pending') {
        $meta_query = array(array('key' => 'moneybird_invoice_id', 'compare' => 'NOT EXISTS'));
    } elseif ($_GET['moneybird_filter'] == 'invoice_pending') {
        $meta_query = array(array('key' => 'moneybird_queue_generate', 'compare' => 'EXISTS'));
    } elseif ($_GET['moneybird_filter'] == 'invoice_no') {
        $meta_query = array(
            'relation' => 'AND',
            array('key' => 'moneybird_invoice_id', 'compare' => 'NOT EXISTS'),
            array('key' => 'moneybird_queue_generate', 'compare' => 'NOT EXISTS'),
        );
    } elseif ($_GET['moneybird_filter'] == 'estimate_yes') {
        $meta_query = array(array('key' => 'moneybird_estimate_id', 'compare' => 'EXISTS'));
    } elseif ($_GET['moneybird_filter'] == 'estimate_no_or_pending') {
        $meta_query = array(array('key' => 'moneybird_estimate_id', 'compare' => 'NOT EXISTS'));
    } elseif ($_GET['moneybird_filter'] == 'estimate_pending') {
        $meta_query = array(array('key' => 'moneybird_queue_generate_estimate', 'compare' => 'EXISTS'));
    } elseif ($_GET['moneybird_filter'] == 'estimate_no') {
        $meta_query = array(
            'relation' => 'AND',
            array('key' => 'moneybird_estimate_id', 'compare' => 'NOT EXISTS'),
            array('key' => 'moneybird_queue_generate_estimate', 'compare' => 'NOT EXISTS'),
        );
    }

    if ($meta_query) {
        $query->set('meta_query', $meta_query);
    }	
}


function render_wc_orders_filter() {
    if (!isset($_GET['post_type']) || ($_GET['post_type'] !== 'shop_order')) {
        return;
    }
    $moneybird_filter = isset($_GET['moneybird_filter']) ? $_GET['moneybird_filter'] : '';
    $moneybird_invoice_filters = array(
        array(
            'value' => 'invoice_yes', 
            'label' => __('With Moneybird invoice', 'woocommerce_moneybird')
        ),
        array(
            'value' => 'invoice_no', 
            'label' => __('Without Moneybird invoice', 'woocommerce_moneybird')
        ),
        array(
            'value' => 'invoice_pending', 
            'label' => __('In queue', 'woocommerce_moneybird')
        ),
        array(
            'value' => 'invoice_no_or_pending', 
            'label' => __('In queue or no invoice', 'woocommerce_moneybird')
        )
    );
    $moneybird_estimate_filters = array(
        array(
            'value' => 'estimate_yes', 
            'label' => __('With Moneybird estimate', 'woocommerce_moneybird')
        ),
        array(
            'value' => 'estimate_no', 
            'label' => __('Without Moneybird estimate', 'woocommerce_moneybird')
        ),
        array(
            'value' => 'estimate_pending', 
            'label' => __('In queue', 'woocommerce_moneybird')
        ),
        array(
            'value' => 'estimate_no_or_pending', 
            'label' => __('In queue or no estimate', 'woocommerce_moneybird')
        )
    );
    ?>

    <select id="moneybird_filter" name="moneybird_filter">
        <option value=""><?php _e('All Moneybird statuses', 'woocommerce_moneybird') ?></option>
        <optgroup label="<?php _e('Invoice status', 'woocommerce_moneybird') ?>">
            <?php foreach ($moneybird_invoice_filters as $filter): ?>
                <option value="<?php echo $filter['value']; ?>" <?php if ($moneybird_filter===$filter['value']): ?>selected<?php endif; ?>><?php echo $filter['label']; ?></option>
            <?php endforeach; ?>
        </optgroup>
        <optgroup label="<?php _e('Estimate status', 'woocommerce_moneybird') ?>">
            <?php foreach ($moneybird_estimate_filters as $filter): ?>
                <option value="<?php echo $filter['value']; ?>" <?php if ($moneybird_filter===$filter['value']): ?>selected<?php endif; ?>><?php echo $filter['label']; ?></option>
            <?php endforeach; ?>
    </select>
    <?php
}


// Register actions and filters
add_action('woocommerce_save_product_variation', '\ExtensionTree\WCMoneyBird\woocommerce_save_product_variation', 10, 2);
if (is_admin()) {
    if (current_user_can('edit_shop_orders')) {
        add_action('add_meta_boxes', '\ExtensionTree\WCMoneyBird\add_meta_boxes');
        add_action('admin_enqueue_scripts', '\ExtensionTree\WCMoneyBird\admin_enqueue_scripts');
        add_action('edited_product_cat', '\ExtensionTree\WCMoneyBird\edited_product_cat', 10, 1);
        add_action('product_cat_edit_form_fields', '\ExtensionTree\WCMoneyBird\product_cat_edit_form_fields', 10, 1);
        add_action('save_post', '\ExtensionTree\WCMoneyBird\save_post');
        add_action('woocommerce_product_after_variable_attributes', '\ExtensionTree\WCMoneyBird\woocommerce_product_after_variable_attributes', 10, 3);
        add_action('woocommerce_admin_order_data_after_order_details', '\ExtensionTree\WCMoneyBird\render_order_moneybird_block');    
    }

    if (isset($_GET['wcmb-debug'])) {
        add_action('add_meta_boxes', '\ExtensionTree\WCMoneyBird\debug_add_meta_box');
    }

    add_action('show_user_profile', '\ExtensionTree\WCMoneyBird\render_moneybird_user_profile_block', 10);
    add_action('edit_user_profile', '\ExtensionTree\WCMoneyBird\render_moneybird_user_profile_block', 10);
    add_action('personal_options_update', '\ExtensionTree\WCMoneyBird\save_moneybird_user_profile_fields');
    add_action('edit_user_profile_update', '\ExtensionTree\WCMoneyBird\save_moneybird_user_profile_fields');
    add_action('pre_get_posts', '\ExtensionTree\WCMoneyBird\filter_wc_orders_list', 99, 1);
    add_action('restrict_manage_posts', '\ExtensionTree\WCMoneyBird\render_wc_orders_filter');
}
