<?php
defined('ABSPATH') || exit;

$addons_list = \CUW\App\Controllers\Common\AddOns::getList();
$rtl = \CUW\App\Helpers\WP::isRtl();
$active_addons = $addons_list['active_addons'] ?? [];
$available_addons = $addons_list['available_addons'] ?? [];
?>

<div id="cuw-add-ons">
    <div class="d-flex title-container align-items-center justify-content-between">
        <h5><?php esc_html_e("Add-ons", 'checkout-upsell-woocommerce'); ?></h5>
    </div>
    <div class="row mx-auto">
        <div class="col-md-12 my-2">
            <h2 class="mx-3 mt-3"><?php esc_html_e('Active add-ons', 'checkout-upsell-woocommerce'); ?></h2>
            <div class="d-flex flex-wrap">
                <?php if (!empty($active_addons)) { ?>
                    <?php foreach ($active_addons as $slug => $addon) { ?>
                        <div class="cuw-addon card col-md-6 p-0 m-3 position-relative" style="max-width: 374px;">
                            <div class="d-flex justify-content-between align-baseline px-3 pt-3">
                                <div class="d-flex justify-content-center">
                                    <a class="cuw-addon-image" href="<?php echo esc_url($addon['plugin_url']); ?>">
                                        <img height="64px" width="64px" src="<?php echo esc_url($addon['icon_url']); ?>"
                                             alt="<?php echo esc_attr($addon['name']); ?>">
                                    </a>
                                </div>
                                <div class="d-flex flex-column w-75 my-2" style="gap: 4px;">
                                    <h2 class="cuw-addon-header" style="font-size: 18px;">
                                        <?php echo esc_html($addon['name']); ?>
                                        <span class="text-secondary"
                                              style="font-size: 12px; font-weight: 400;">
                                            v<?php echo esc_html($addon['version']); ?>
                                        </span>
                                    </h2>
                                    <div class="cuw-addon-author" style="font-size: 14px;">
                                        <?php esc_html_e('By', 'checkout-upsell-woocommerce'); ?>
                                        <?php echo esc_html($addon['author']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="cuw-addon-description py-2 px-3" style="font-size: 14px;">
                                <?php echo esc_html($addon['description']); ?>
                            </div>
                            <div class="cuw-addon-actions d-flex justify-content-between align-items-center border-top p-2 m-0 mt-auto">
                                <div class="d-flex" style="gap: 8px;">
                                    <?php if (!empty($addon['page_url'])) : ?>
                                        <a href="<?php echo esc_url($addon['page_url']); ?>"
                                           class="btn btn-primary text-decoration-none">
                                            <?php esc_html_e("Open", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($addon['settings_url'])): ?>
                                        <a href="<?php echo esc_url($addon['settings_url']); ?>"
                                           title="<?php echo esc_attr($addon['name']); ?>"
                                           class="btn btn-outline-primary text-decoration-none">
                                            <?php esc_html_e("Configure", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php do_action('cuw_' . $slug . '_addon_actions', $addon); ?>
                                </div>
                                <?php if (!empty($addon['is_installed'])) { ?>
                                    <div class="d-flex" style="gap: 8px;">
                                        <a href="<?php echo esc_url(add_query_arg(['cuw_deactivate_addon' => $slug, 'nonce' => wp_create_nonce('cuw_addon_deactivate')])); ?>"
                                           class="btn btn-outline-secondary text-decoration-none">
                                            <?php esc_html_e("Deactivate", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="m-3">
                        <p class="text-secondary"><?php esc_html_e("No active add-ons", 'checkout-upsell-woocommerce'); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="col-md-12 my-2">
            <h2 class="mx-3"><?php esc_html_e('Available add-ons', 'checkout-upsell-woocommerce'); ?></h2>
            <div class="d-flex flex-wrap">
                <?php if (!empty($available_addons)) { ?>
                    <?php foreach ($available_addons as $slug => $addon) { ?>
                        <div class="cuw-addon card col-md-6 p-0 m-3 position-relative" style="max-width: 374px;">
                            <?php if (!empty($addon['is_pro'])) { ?>
                                <div class="position-absolute"
                                     style="<?php echo $rtl ? 'left: 4px;' : 'right: 4px;'; ?> top: 4px;">
                                    <small class="badge badge-blue-primary font-weight-medium"
                                           style="line-height:1; padding: 3px 6px; margin: 2px 2px 0 2px; border-radius: 4px;">
                                        <?php esc_html_e('Paid', 'checkout-upsell-woocommerce'); ?>
                                    </small>
                                </div>
                            <?php } ?>
                            <div class="d-flex justify-content-between px-3 pt-3">
                                <div class="d-flex justify-content-center">
                                    <a class="cuw-addon-image" href="<?php echo esc_url($addon['plugin_url']); ?>">
                                        <img height="64px" width="64px" src="<?php echo esc_url($addon['icon_url']); ?>"
                                             alt="<?php echo esc_attr($addon['name']); ?>">
                                    </a>
                                </div>
                                <div class="d-flex flex-column w-75  my-2" style="gap: 4px;">
                                    <h2 class="cuw-addon-header" style="font-size: 18px;">
                                        <?php echo esc_html($addon['name']); ?>
                                        <span class="text-secondary" style="font-size: 12px; font-weight: 400;">
                                            v<?php echo esc_html($addon['version']); ?>
                                        </span>
                                    </h2>
                                    <div class="cuw-addon-author" style="font-size: 14px;">
                                        <?php esc_html_e('By', 'checkout-upsell-woocommerce'); ?>
                                        <?php echo esc_html($addon['author']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="cuw-addon-description py-2 px-3" style="font-size: 14px;">
                                <?php echo esc_html($addon['description']); ?>
                            </div>
                            <?php if (!empty($addon['message'])) { ?>
                                <div class="text-info px-3 pb-3 mt-n1 small">
                                    <?php echo wp_kses_post($addon['message']); ?>
                                </div>
                            <?php } ?>
                            <div class="cuw-addon-actions d-flex justify-content-between align-items-center border-top p-2 m-0 mt-auto">
                                <div class="d-flex" style="gap: 8px;">
                                    <?php if (!empty($addon['download_url']) && empty($addon['is_installed'])) : ?>
                                        <a href="<?php echo esc_url($addon['download_url']); ?>"
                                           title="<?php echo esc_attr($addon['name']); ?>"
                                           class="btn btn-primary text-decoration-none">
                                            <?php esc_html_e("Download", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    <?php elseif (empty($addon['download_url']) && empty($addon['is_installed']) && !empty($addon['plugin_url'])) : ?>
                                        <a href="<?php echo esc_url($addon['plugin_url']); ?>" target="_blank"
                                           title="<?php echo esc_attr($addon['name']); ?>"
                                           class="btn btn-primary text-decoration-none px-4">
                                            <?php esc_html_e("Get", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($addon['plugin_url'])): ?>
                                        <a href="<?php echo esc_url($addon['plugin_url']); ?>" target="_blank"
                                           title="<?php echo esc_attr($addon['name']); ?>"
                                           class="btn btn-outline-primary text-decoration-none">
                                            <?php esc_html_e("Learn more", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex" style="gap: 8px;">
                                    <?php if (!empty($addon['is_installed'])): ?>
                                        <a href="<?php echo esc_url(add_query_arg(['cuw_activate_addon' => $slug, 'nonce' => wp_create_nonce('cuw_addon_activate')])); ?>"
                                           class="btn btn-primary text-decoration-none"
                                           style="<?php if (empty($addon['is_activatable'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
                                            <?php esc_html_e("Activate", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="m-3">
                        <p class="text-secondary"><?php esc_html_e("No more available add-ons", 'checkout-upsell-woocommerce'); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
