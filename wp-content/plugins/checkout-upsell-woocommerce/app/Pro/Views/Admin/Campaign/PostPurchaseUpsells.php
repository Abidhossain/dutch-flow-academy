<?php
defined('ABSPATH') || exit;
if (!isset($action) || !isset($campaign)) {
    return;
}
?>

<?php if ($action == 'cuw_campaign_contents'): ?>
    <?php
    $page_builder = CUW()->input->get('page_builder', ($campaign['data']['page']['builder'] ?? ''), 'query');
    $template = CUW()->input->get('template', ($campaign['data']['page']['template'] ?? ''), 'query');

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'pp_offers',
        'title' => __('Offers', 'checkout-upsell-woocommerce'),
        'icon' => 'offers',
        'view' => 'Pro/Admin/Campaign/Components/Offers',
        'data' => ['campaign' => $campaign],
    ]);
    ?>
    <input type="hidden" id="cuw-ppu-page-builder" name="data[page][builder]"
           value="<?php echo esc_attr($page_builder); ?>">
    <input type="hidden" id="cuw-ppu-template-id" name="data[page][template]"
           value="<?php echo esc_attr($template); ?>">
<?php elseif ($action == 'cuw_after_campaign_page'): ?>
    <?php
    $page_builder = CUW()->input->get('page_builder', '', 'query');
    $template = CUW()->input->get('template', '', 'query');
    if (empty($campaign['id'])) {
        if (!empty($page_builder) && empty($template)) {
            CUW()->view('Pro/Admin/Campaign/Components/PostPurchaseTemplates', ['campaign' => $campaign, 'page_builder' => $page_builder]);
        } elseif (!empty($page_builder) && !empty($template)) {
            CUW()->view('Pro/Admin/Campaign/Components/PostPurchaseOfferPage', ['campaign' => $campaign, 'show' => true]);
        }
    } else {
        if (!empty($page_builder) && empty($template)) {
            CUW()->view('Pro/Admin/Campaign/Components/PostPurchaseTemplates', ['campaign' => $campaign, 'page_builder' => $page_builder]);
        } else {
            CUW()->view('Pro/Admin/Campaign/Components/PostPurchaseOfferPage', ['campaign' => $campaign]);
        }
    } ?>
<?php endif; ?>