<?php
defined('ABSPATH') || exit;

if (!isset($campaign)) {
    return;
}

$campaign_type = $campaign['type'];
$document_links = \CUW\App\Helpers\Tutorials::getCampaignDocumentLinks($campaign_type);
$youtube_links = \CUW\App\Helpers\Tutorials::getYoutubeVideoLinks($campaign_type);
?>

<div class="cuw-tutorials">
    <?php if (!empty($youtube_links)) { ?>
        <div class="cuw-videos">
            <div class="font-weight-medium text-dark">
                <?php esc_html_e('Videos', 'checkout-upsell-woocommerce'); ?>
            </div>
            <div class="d-flex flex-column m-2" style="gap: 8px">
                <ul>
                    <?php foreach ($youtube_links as $link) { ?>
                        <li style="list-style-type: disc; margin: 0 32px;">
                        <span>
                            <?php echo esc_html($link['title']) ?> -
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank"
                               style="display: inline-flex; align-items: center; text-decoration: none;">
                                <i class="cuw-icon-youtube"
                                   style="font-size: 18px;"></i>&nbsp;<?php esc_html_e('Watch', 'checkout-upsell-woocommerce'); ?>
                            </a>
                        </span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
    <?php if (!empty($document_links)) { ?>
        <div class="cuw-documents mt-3">
            <div class="font-weight-medium text-dark">
                <?php esc_html_e('Documents', 'checkout-upsell-woocommerce'); ?>
            </div>
            <div class="d-flex flex-column m-2" style="gap: 8px">
                <ul style="font-size: 16px;">
                    <?php foreach ($document_links as $link) { ?>
                        <li style="list-style-type: disc; margin: 0 32px;">
                           <span>
                                <?php echo esc_html($link['title']); ?> -
                               <a href="<?php echo esc_url($link['url']); ?>" target="_blank"
                                  style="display: inline-flex; align-items: center; text-decoration: none; gap: 2px;">
                                    <i class="cuw-icon-external-link inherit-color"
                                       style="font-size: 14px; font-weight: 600;"></i>&nbsp;<?php esc_html_e('Read', 'checkout-upsell-woocommerce'); ?>
                                </a>
                           </span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
</div>
