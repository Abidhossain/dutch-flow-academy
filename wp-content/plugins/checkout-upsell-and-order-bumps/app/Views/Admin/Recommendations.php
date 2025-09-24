<?php
defined('ABSPATH') || exit;

$recommendations_list = \CUW\App\Controllers\Admin\Page::getRecommendations();
$rtl = \CUW\App\Helpers\WP::isRtl();
?>

<div id="cuw-add-ons">
    <div class="d-flex title-container align-items-center justify-content-between">
        <h5><?php esc_html_e("Recommendations", 'checkout-upsell-and-order-bumps'); ?></h5>
    </div>
    <div class="row mx-auto">
        <div class="col-md-12 my-2">
            <div class="d-flex flex-wrap">
                <?php foreach ($recommendations_list as $slug => $recommendation) { ?>
                    <div class="cuw-recommendation card col-md-6 p-0 m-3 position-relative" style="max-width: 374px;">
                        <div style="min-height: 208px;">
                            <img class="img-fluid h-100" src="<?php echo esc_url($recommendation['banner_image']); // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>"
                                 alt="<?php echo esc_attr($recommendation['name']); ?>">
                        </div>
                        <div class="d-flex justify-content-between px-3 pt-3">
                            <div class="d-flex justify-content-center">
                                <a class="cuw-recommendation-image" href="<?php echo esc_url($recommendation['plugin_url']); ?>">
                                    <img height="64px" width="64px" src="<?php echo esc_url($recommendation['icon_url']); // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>"
                                         alt="<?php echo esc_attr($recommendation['name']); ?>">
                                </a>
                            </div>
                            <div class="d-flex flex-column w-75  my-2" style="gap: 4px;">
                                <h2 class="cuw-recommendation-header" style="font-size: 18px;">
                                    <?php echo esc_html($recommendation['name']); ?>
                                </h2>
                                <div class="cuw-recommendation-author" style="font-size: 14px;">
                                    <?php esc_html_e('By', 'checkout-upsell-and-order-bumps'); ?>
                                    <?php echo esc_html($recommendation['author']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="cuw-recommendation-description py-2 px-3" style="font-size: 14px;">
                            <?php echo esc_html($recommendation['description']); ?>
                        </div>
                        <div class="cuw-recommendation-actions d-flex justify-content-between align-items-center border-top p-2 m-0 mt-auto">
                            <div class="d-flex" style="gap: 8px;">
                                <?php if (!empty($recommendation['plugin_url'])): ?>
                                    <a href="<?php echo esc_url($recommendation['plugin_url']); ?>" target="_blank"
                                       title="<?php echo esc_attr($recommendation['name']); ?>"
                                       class="btn btn-outline-primary text-decoration-none">
                                        <?php esc_html_e("Learn more", 'checkout-upsell-and-order-bumps'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
