<?php defined('ABSPATH') || exit ?>

<?php
isset($page) || exit;

$page_url = $page->getUrl();

use \CUW\App\Pro\Helpers\Engine;

?>

<div id="engines-create" class="collapse">
    <div class="title-container d-flex border-bottom align-items-center" style="gap: 8px;">
        <button type="button" id="back-to-engines" class="btn border border-gray-light  p-2">
            <i class="cuw-icon-arrow-left mx-1"></i>
        </button>
        <h5 class=""><?php esc_html_e("Choose engine type...", 'checkout-upsell-woocommerce'); ?></h5>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="my-4 mx-2 d-flex flex-column align-items-center justify-content-center" id="available-pages"
                 style="gap: 24px;">
                <?php foreach (Engine::get() as $type => $engine) {
                    ?>
                    <a class="card create-engine-card d-flex flex-column w-100 text-decoration-none p-0 bg-white border border-gray-light mt-0"
                       href="<?php echo esc_url(!empty($engine) ? $page_url . "&tab=engines&create=new&type=" . $type : CUW()->plugin->getUrl($type)); ?>"
                       style="align-items: center; max-width: 824px;">
                        <div class="d-flex justify-content-center align-items-center w-100 p-3">
                            <div class="text-center">
                                <img src="<?php echo esc_url(\CUW\App\Helpers\Assets::getUrl('img-pro/' . esc_attr($engine['icon']) . '.svg')); ?>" alt="<?php echo esc_attr($type) ?>" >
                            </div>
                            <div class="card-body py-1 px-4 d-flex flex-column" style="gap: 4px;">
                                <h4><?php echo esc_html($engine['title']); ?></h4>
                                <p class="card-text text-custom-secondary" style="font-size: 14px;">
                                    <?php echo esc_html($engine['description']); ?>
                                </p>
                            </div>
                            <button type="button" id="create-engine-button" class="btn btn-outline-primary px-4 py-2">
                                <?php esc_html_e("Create", 'checkout-upsell-woocommerce'); ?>
                            </button>
                        </div>
                        <div class="card-body w-100 p-3 d-flex text-justify" style="border-top: 1px solid #E8EAED; gap: 8px;">
                            <div class="card-text text-custom-secondary mb-1" style="font-size: 14px; font-weight: 400;">
                                <?php echo esc_html__("Supported campaigns", 'checkout-upsell-woocommerce') . ':'; ?>
                            </div>
                            <div class="text-dark d-flex flex-wrap"
                                 style="gap: 6px;">
                                <?php foreach ($engine['campaigns'] as $campaign) {
                                    echo '<small class="badge-pill-grey-secondary px-2 py-1" style="line-height: 1.2; font-size: 12px; border-radius: 4px;">'
                                        . esc_html(\CUW\App\Helpers\Campaign::getTypes($campaign))
                                        . '</small>';
                                } ?>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
