<?php
defined('ABSPATH') || exit;

if (!isset($campaign)) {
    return;
}

$has_pro = CUW()->plugin->has_pro;

$campaign_type = isset($campaign['type']) ? $campaign['type'] : '';
$campaign_data = !empty($campaign['data']) ? $campaign['data'] : [];
$template = !empty($campaign['data']['template']) ? $campaign['data']['template'] : [];
$discount = !empty($campaign['data']['discount']) ? $campaign['data']['discount'] : [];

$template_name = \CUW\App\Helpers\Campaign::getTemplateName($campaign);
$default_template = \CUW\App\Helpers\Template::getDefaultData($template_name, $campaign_type);
$template = array_merge($default_template, $template);
$template['name'] = $template_name;
$styles = $template['styles'] ?? [];

$advanced_section = in_array($campaign_type, apply_filters('cuw_edit_campaign_advanced_section', ['fbt', 'thankyou_upsells', 'noc']));
?>
<div id="cuw-template">
    <div class="cuw-slider-header d-flex justify-content-between align-items-center mt-2" style="gap:8px;">
        <h4 class="cuw-slider-title"><?php esc_html_e("Template", 'checkout-upsell-woocommerce'); ?></h4>
        <div class="d-flex" style="gap:8px;">
            <button type="button" id="cuw-template-save" style="gap: 6px;" class="btn btn-outline-secondary">
                <i class="cuw-icon-tick-circle inherit-color"></i>
                <?php esc_html_e("Save & Close", 'checkout-upsell-woocommerce'); ?>
            </button>
        </div>
    </div>
    <div class="row p-2 justify-content-center " style="gap:20px;">
        <div id="edit" class="mt-3 col-md-4 mt-4 px-0 border rounded-lg" style="height:70vh;overflow-y: scroll">
            <div class="p-0 cuw-tab-container">
                <ul class="nav nav-tabs d-flex justify-content-around border-bottom" id="myTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#template-content" role="tab">
                            1.<?php esc_html_e("Content", 'checkout-upsell-woocommerce'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#template-design" role="tab">
                            2.<?php esc_html_e("Design", 'checkout-upsell-woocommerce'); ?>
                        </a>
                    </li>
                    <?php if ($advanced_section) { ?>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#advanced-section" role="tab">
                                3.<?php esc_html_e("Advanced", 'checkout-upsell-woocommerce'); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade show active mt-3 p-3" id="template-content">
                    <?php if (isset($template['title'])) { ?>
                        <div class="form-group cuw-template-texts">
                            <label for="template-title"
                                   class="form-label mb-1 font-weight-semibold"><?php esc_html_e("Title", 'checkout-upsell-woocommerce'); ?></label>
                            <input type="text" name="data[template][title]"
                                   value="<?php echo esc_attr($template['title']); ?>" id="template-title"
                                   class="form-control" data-target=".cuw-template-title">
                        </div>
                    <?php } ?>

                    <?php if (isset($template['description'])) { ?>
                        <div class="form-group cuw-template-texts">
                            <label for="template-description"
                                   class="form-label mb-1 font-weight-semibold"><?php esc_html_e("Description", 'checkout-upsell-woocommerce'); ?></label>
                            <input type="text" name="data[template][description]"
                                   value="<?php echo esc_attr($template['description']); ?>" id="template-description"
                                   class="form-control" data-target=".cuw-template-description">
                        </div>
                    <?php } ?>

                    <?php if (isset($template['cta_text'])) { ?>
                        <div class="form-group cuw-template-texts mb-0">
                            <label for="template-cta-text"
                                   class="form-label mb-1 font-weight-semibold"><?php esc_html_e("CTA text", 'checkout-upsell-woocommerce'); ?></label>
                            <input type="text" name="data[template][cta_text]"
                                   value="<?php echo esc_attr($template['cta_text']); ?>" id="template-cta-text"
                                   class="form-control" data-target=".cuw-template-cta-text">
                        </div>
                        <?php if ($campaign_type == 'fbt') { ?>
                            <span class="d-block small mb-3" style="opacity: 0.8; font-size: 12px;">
                                <?php esc_html_e('Available placeholders', 'checkout-upsell-woocommerce'); ?>: {items_text}, {items_count}
                            </span>
                        <?php }
                        do_action('cuw_after_template_cta_input', $campaign);
                        ?>
                    <?php } ?>
                </div>
                <div class="tab-pane fade mt-3" id="template-design">
                    <div class="cuw-template-styles p-3">
                        <?php if (isset($styles['template'])) { ?>
                            <div id="template-styles" class="row cuw-style-group">
                                <div class="col-md-12 d-flex align-items-center justify-content-between">
                                    <h6 class="form-label"><?php esc_html_e("Template styles", 'checkout-upsell-woocommerce'); ?></h6>
                                    <div style="gap:4px;"
                                         class="cuw-reset-styles d-flex text-primary align-items-center">
                                        <i class="cuw-icon-reset text-primary px-1"
                                           style="font-size:14px; font-weight: 600;"></i>
                                        <a class="text-decoration-none cursor-pointer"><?php esc_html_e("Reset", 'checkout-upsell-woocommerce'); ?></a>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label class="form-label"><?php esc_html_e("Border", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group" style="gap:8px;">
                                        <select class="form-control cuw-border-width"
                                                name="data[template][styles][template][border-width]"
                                                data-name="border-width" data-target=".cuw-template">
                                            <option value="0" <?php selected('0', $styles['template']['border-width']); ?>><?php esc_html_e("None", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="thin" <?php selected('thin', $styles['template']['border-width']); ?>><?php esc_html_e("Thin", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="medium" <?php selected('medium', $styles['template']['border-width']); ?>><?php esc_html_e("Medium", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="thick" <?php selected('thick', $styles['template']['border-width']); ?>><?php esc_html_e("Thick", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                        <select class="form-control cuw-border-style"
                                                name="data[template][styles][template][border-style]"
                                                data-name="border-style" data-target=".cuw-template">
                                            <option value="solid" <?php selected('solid', $styles['template']['border-style']); ?>><?php esc_html_e("Solid", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="double" <?php selected('double', $styles['template']['border-style']); ?>><?php esc_html_e("Double", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="dotted" <?php selected('dotted', $styles['template']['border-style']); ?>><?php esc_html_e("Dotted", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="dashed" <?php selected('dashed', $styles['template']['border-style']); ?>><?php esc_html_e("Dashed", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                        <div class="cuw-border-color">
                                            <div class="cuw-color-inputs position-relative input-group">
                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="data[template][styles][template][border-color]"
                                                       data-name="border-color" data-target=".cuw-template"
                                                       value="<?php echo esc_attr($styles['template']['border-color']); ?>"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input type="color"
                                                       class="cuw-color-picker color-picker-container form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">
                                        <input type="color"
                                               class="cuw-color-picker color-picker-container form-control">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][template][background-color]"
                                               data-name="background-color" data-target=".cuw-template"
                                               value="<?php echo esc_attr($styles['template']['background-color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                    </div>
                                </div>

                                <?php if (isset($styles['template']['padding'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Padding size", 'checkout-upsell-woocommerce'); ?></label>
                                        <select class="form-control" name="data[template][styles][template][padding]"
                                                data-name="padding" data-target=".cuw-template">
                                            <option value="0" <?php selected('0', $styles['template']['padding']); ?>><?php esc_html_e("0", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="2px" <?php selected('2px', $styles['template']['padding']); ?>><?php esc_html_e("2px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="4px" <?php selected('4px', $styles['template']['padding']); ?>><?php esc_html_e("4px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="8px" <?php selected('8px', $styles['template']['padding']); ?>><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $styles['template']['padding']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $styles['template']['padding']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $styles['template']['padding']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $styles['template']['padding']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['content'])) { ?>
                            <div id="content-styles" class="row cuw-style-group">
                                <div class="col-md-12 d-flex align-items-center justify-content-between">
                                    <h6 class="form-label"><?php esc_html_e("Modal styles", 'checkout-upsell-woocommerce'); ?></h6>
                                    <div style="gap:4px;"
                                         class="cuw-reset-styles d-flex text-primary align-items-center">
                                        <i class="cuw-icon-reset text-primary"
                                           style="font-size:14px; font-weight: 600"></i>
                                        <a class="text-decoration-none cursor-pointer"><?php esc_html_e("Reset", 'checkout-upsell-woocommerce'); ?></a>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label class="form-label"><?php esc_html_e("Border", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group" style="gap:8px;">
                                        <select class="form-control cuw-border-width"
                                                name="data[template][styles][content][border-width]"
                                                data-name="border-width" data-target=".cuw-modal-content">
                                            <option value="0" <?php selected('0', $styles['content']['border-width']); ?>><?php esc_html_e("None", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="thin" <?php selected('thin', $styles['content']['border-width']); ?>><?php esc_html_e("Thin", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="medium" <?php selected('medium', $styles['content']['border-width']); ?>><?php esc_html_e("Medium", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="thick" <?php selected('thick', $styles['content']['border-width']); ?>><?php esc_html_e("Thick", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                        <select class="form-control cuw-border-style"
                                                name="data[template][styles][content][border-style]"
                                                data-name="border-style" data-target=".cuw-modal-content">
                                            <option value="solid" <?php selected('solid', $styles['content']['border-style']); ?>><?php esc_html_e("Solid", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="double" <?php selected('double', $styles['content']['border-style']); ?>><?php esc_html_e("Double", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="dotted" <?php selected('dotted', $styles['content']['border-style']); ?>><?php esc_html_e("Dotted", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="dashed" <?php selected('dashed', $styles['content']['border-style']); ?>><?php esc_html_e("Dashed", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                        <div class="cuw-border-color">
                                            <div class="cuw-color-inputs input-group position-relative">
                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="data[template][styles][content][border-color]"
                                                       data-name="border-color" data-target=".cuw-modal-content"
                                                       value="<?php echo esc_attr($styles['content']['border-color']); ?>"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input type="color"
                                                       class="cuw-color-picker color-picker-container form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 form-group">
                                    <label class="form-label"><?php esc_html_e("Width", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-range-group input-group"
                                         style="display: flex; justify-content: space-between; align-items: center">
                                        <input type="range" class="cuw-range-input form-control"
                                               name="data[template][styles][content][max-width]" data-name="max-width"
                                               data-target=".cuw-modal-content" min="480" max="1080"
                                               value="<?php echo esc_attr($styles['content']['max-width']); ?>">
                                        <span class="mx-2"><span
                                                    class="cuw-range-value"><?php echo esc_html($styles['content']['max-width']); ?></span><?php esc_html_e("px", 'checkout-upsell-woocommerce'); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['title'])) { ?>
                            <div id="title-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"><?php esc_html_e("Title styles", 'checkout-upsell-woocommerce'); ?></h6>
                                </div>

                                <?php if (isset($styles['title']['font-size'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                        <select class="form-control" name="data[template][styles][title][font-size]"
                                                data-name="font-size" data-target=".cuw-template-title">
                                            <option value="" <?php selected('', $styles['title']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $styles['title']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $styles['title']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $styles['title']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $styles['title']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $styles['title']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $styles['title']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="28px" <?php selected('28px', $styles['title']['font-size']); ?>><?php esc_html_e("28px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $styles['title']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                <?php } ?>

                                <?php if (isset($styles['title']['color'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                        <div class="cuw-color-inputs input-group position-relative">
                                            <input type="text" class="cuw-color-input form-control w-50"
                                                   name="data[template][styles][title][color]" data-name="color"
                                                   data-target=".cuw-template-title"
                                                   value="<?php echo esc_attr($styles['title']['color']); ?>"
                                                   maxlength="7"
                                                   placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                            <input type="color"
                                                   class="cuw-color-picker color-picker-container form-control">
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if (isset($styles['title']['background-color'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                        <div class="cuw-color-inputs input-group position-relative">
                                            <input type="text" class="cuw-color-input form-control w-50"
                                                   name="data[template][styles][title][background-color]"
                                                   data-name="background-color" data-target=".cuw-template-title"
                                                   value="<?php echo esc_attr($styles['title']['background-color']); ?>"
                                                   maxlength="7"
                                                   placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                            <input type="color"
                                                   class="cuw-color-picker color-picker-container form-control">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['header'])) { ?>
                            <div id="header-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"><?php esc_html_e("Header styles", 'checkout-upsell-woocommerce'); ?></h6>
                                </div>

                                <?php if (isset($styles['header']['font-size'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                        <select class="form-control" name="data[template][styles][header][font-size]"
                                                data-name="font-size" data-target=".cuw-modal-header">
                                            <option value="" <?php selected('', $styles['header']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $styles['header']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $styles['header']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $styles['header']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $styles['header']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $styles['header']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $styles['header']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="28px" <?php selected('28px', $styles['header']['font-size']); ?>><?php esc_html_e("28px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $styles['header']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                <?php } ?>

                                <?php if (isset($styles['header']['color'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                        <div class="cuw-color-inputs input-group position-relative">
                                            <input type="text" class="cuw-color-input form-control w-50"
                                                   name="data[template][styles][header][color]" data-name="color"
                                                   data-target=".cuw-modal-header"
                                                   value="<?php echo esc_attr($styles['header']['color']); ?>"
                                                   maxlength="7"
                                                   placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                            <input type="color"
                                                   class="cuw-color-picker color-picker-container form-control">
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if (isset($styles['header']['background-color'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                        <div class="cuw-color-inputs input-group position-relative">
                                            <input type="text" class="cuw-color-input form-control w-50"
                                                   name="data[template][styles][header][background-color]"
                                                   data-name="background-color" data-target=".cuw-modal-header"
                                                   value="<?php echo esc_attr($styles['header']['background-color']); ?>"
                                                   maxlength="7"
                                                   placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                            <input type="color"
                                                   class="cuw-color-picker color-picker-container form-control">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['subheader'])) { ?>
                            <div id="subheader-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"><?php esc_html_e("Subheader styles", 'checkout-upsell-woocommerce'); ?></h6>
                                </div>

                                <?php if (isset($styles['subheader']['font-size'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                        <select class="form-control" name="data[template][styles][subheader][font-size]"
                                                data-name="font-size" data-target=".cuw-modal-subheader">
                                            <option value="" <?php selected('', $styles['subheader']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="12px" <?php selected('12px', $styles['subheader']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="14px" <?php selected('14px', $styles['subheader']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="16px" <?php selected('16px', $styles['subheader']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="18px" <?php selected('18px', $styles['subheader']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="20px" <?php selected('20px', $styles['subheader']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="24px" <?php selected('24px', $styles['subheader']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="28px" <?php selected('28px', $styles['subheader']['font-size']); ?>><?php esc_html_e("28px", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="32px" <?php selected('32px', $styles['subheader']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                <?php } ?>

                                <?php if (isset($styles['subheader']['color'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                        <div class="cuw-color-inputs input-group">

                                            <input type="text" class="cuw-color-input form-control w-50"
                                                   name="data[template][styles][subheader][color]" data-name="color"
                                                   data-target=".cuw-modal-subheader"
                                                   value="<?php echo esc_attr($styles['subheader']['color']); ?>"
                                                   maxlength="7"
                                                   placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                            <input type="color" class="cuw-color-picker form-control">
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if (isset($styles['subheader']['background-color'])) { ?>
                                    <div class="col-md-6 form-group">
                                        <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                        <div class="cuw-color-inputs input-group">

                                            <input type="text" class="cuw-color-input form-control w-50"
                                                   name="data[template][styles][subheader][background-color]"
                                                   data-name="background-color" data-target=".cuw-modal-subheader"
                                                   value="<?php echo esc_attr($styles['subheader']['background-color']); ?>"
                                                   maxlength="7"
                                                   placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                            <input type="color" class="cuw-color-picker form-control">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['body'])) { ?>
                            <div id="product-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"><?php esc_html_e("Body styles", 'checkout-upsell-woocommerce'); ?></h6>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Padding size", 'checkout-upsell-woocommerce'); ?></label>
                                    <select class="form-control" name="data[template][styles][body][padding]"
                                            data-name="padding" data-target=".cuw-modal-body">
                                        <option value="0" <?php selected('0', $styles['body']['padding']); ?>><?php esc_html_e("0", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="2px" <?php selected('2px', $styles['body']['padding']); ?>><?php esc_html_e("2px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="4px" <?php selected('4px', $styles['body']['padding']); ?>><?php esc_html_e("4px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="8px" <?php selected('8px', $styles['body']['padding']); ?>><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="12px" <?php selected('12px', $styles['body']['padding']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="16px" <?php selected('16px', $styles['body']['padding']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="24px" <?php selected('24px', $styles['body']['padding']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="32px" <?php selected('32px', $styles['body']['padding']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][body][background-color]"
                                               data-name="background-color" data-target=".cuw-modal-body"
                                               value="<?php echo esc_attr($styles['body']['background-color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="cuw-color-picker form-control">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['image'])) { ?>
                            <div id="product-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"><?php esc_html_e("Product styles", 'checkout-upsell-woocommerce'); ?></h6>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label class="form-label"><?php esc_html_e("Image size", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-range-group input-group"
                                         style="display: flex; justify-content: space-between; align-items: center">
                                        <input type="range" class="cuw-range-input form-control"
                                               name="data[template][styles][image][size]" data-name="size"
                                               data-target=".cuw-product-image, .cuw-product-image-wrapper"
                                               min="80" max="320"
                                               value="<?php echo esc_attr($styles['image']['size']); ?>">
                                        <span class="mx-2"><span
                                                    class="cuw-range-value"><?php echo esc_html($styles['image']['size']); ?></span><?php esc_html_e("px", 'checkout-upsell-woocommerce'); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['description'])) { ?>
                            <div id="description-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"><?php esc_html_e("Description styles", 'checkout-upsell-woocommerce'); ?></h6>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <select class="form-control" name="data[template][styles][description][font-size]"
                                            data-name="font-size"
                                            data-target=".cuw-template-description, .cuw-template-coupon-message">
                                        <option value="" <?php selected('', $styles['description']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="8px" <?php selected('8px', $styles['description']['font-size']); ?>><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="12px" <?php selected('12px', $styles['description']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="14px" <?php selected('14px', $styles['description']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="16px" <?php selected('16px', $styles['description']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="18px" <?php selected('18px', $styles['description']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="20px" <?php selected('20px', $styles['description']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="24px" <?php selected('24px', $styles['description']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="32px" <?php selected('32px', $styles['description']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">

                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][description][color]" data-name="color"
                                               data-target=".cuw-template-description, .cuw-template-coupon-message"
                                               value="<?php echo esc_attr($styles['description']['color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="cuw-color-picker form-control">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">

                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][description][background-color]"
                                               data-name="background-color"
                                               data-target=".cuw-template-description, .cuw-template-coupon-message"
                                               value="<?php echo esc_attr($styles['description']['background-color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="cuw-color-picker form-control">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['coupon'])) { ?>
                            <div id="coupon-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"><?php esc_html_e("Coupon styles", 'checkout-upsell-woocommerce'); ?></h6>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label class="form-label"><?php esc_html_e("Border", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group" style="gap:8px;">
                                        <select class="form-control cuw-border-width"
                                                name="data[template][styles][coupon][border-width]"
                                                data-name="border-width" data-target=".cuw-template-coupon">
                                            <option value="0" <?php selected('0', $styles['coupon']['border-width']); ?>><?php esc_html_e("None", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="thin" <?php selected('thin', $styles['coupon']['border-width']); ?>><?php esc_html_e("Thin", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="medium" <?php selected('medium', $styles['coupon']['border-width']); ?>><?php esc_html_e("Medium", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="thick" <?php selected('thick', $styles['coupon']['border-width']); ?>><?php esc_html_e("Thick", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                        <select class="form-control cuw-border-style"
                                                name="data[template][styles][coupon][border-style]"
                                                data-name="border-style" data-target=".cuw-template-coupon">
                                            <option value="solid" <?php selected('solid', $styles['coupon']['border-style']); ?>><?php esc_html_e("Solid", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="double" <?php selected('double', $styles['coupon']['border-style']); ?>><?php esc_html_e("Double", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="dotted" <?php selected('dotted', $styles['coupon']['border-style']); ?>><?php esc_html_e("Dotted", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="dashed" <?php selected('dashed', $styles['coupon']['border-style']); ?>><?php esc_html_e("Dashed", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                        <div class="cuw-border-color">
                                            <div class="cuw-color-inputs input-group">

                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="data[template][styles][coupon][border-color]"
                                                       data-name="border-color" data-target=".cuw-template-coupon"
                                                       value="<?php echo esc_attr($styles['coupon']['border-color']); ?>"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input type="color" class="cuw-color-picker form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">

                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][coupon][background-color]"
                                               data-name="background-color" data-target=".cuw-template-coupon"
                                               value="<?php echo esc_attr($styles['coupon']['background-color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="cuw-color-picker form-control">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                    <select class="form-control" name="data[template][styles][coupon][font-size]"
                                            data-name="font-size" data-target=".cuw-template-coupon">
                                        <option value="" <?php selected('', $styles['coupon']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="8px" <?php selected('8px', $styles['coupon']['font-size']); ?>><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="12px" <?php selected('12px', $styles['coupon']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="14px" <?php selected('14px', $styles['coupon']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="16px" <?php selected('16px', $styles['coupon']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="18px" <?php selected('18px', $styles['coupon']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="20px" <?php selected('20px', $styles['coupon']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="24px" <?php selected('24px', $styles['coupon']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="32px" <?php selected('32px', $styles['coupon']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">

                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][coupon][color]" data-name="color"
                                               data-target=".cuw-template-coupon"
                                               value="<?php echo esc_attr($styles['coupon']['color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="cuw-color-picker form-control">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['cta'])) { ?>
                            <div id="cta-section-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"> <?php esc_html_e("CTA styles", 'checkout-upsell-woocommerce'); ?> </h6>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?> </label>
                                    <select class="form-control" name="data[template][styles][cta][font-size]"
                                            data-name="font-size"
                                            data-target=".cuw-template-cta-section, .cuw-template-cta-button">
                                        <option value="" <?php selected('', $styles['cta']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="8px" <?php selected('8px', $styles['cta']['font-size']); ?>><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="12px" <?php selected('12px', $styles['cta']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="14px" <?php selected('14px', $styles['cta']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="16px" <?php selected('16px', $styles['cta']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="18px" <?php selected('18px', $styles['cta']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="20px" <?php selected('20px', $styles['cta']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="24px" <?php selected('24px', $styles['cta']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="32px" <?php selected('32px', $styles['cta']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative">
                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][cta][color]" data-name="color"
                                               data-target=".cuw-template-cta-section, .cuw-template-cta-button"
                                               value="<?php echo esc_attr($styles['cta']['color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color"
                                               class="cuw-color-picker color-picker-container form-control">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group position-relative">
                                        <input type="text" class="form-control cuw-color-input w-50"
                                               name="data[template][styles][cta][background-color]"
                                               data-name="background-color"
                                               data-target=".cuw-template-cta-section, .cuw-template-cta-button, .cuw-template-cta-element"
                                               value="<?php echo esc_attr($styles['cta']['background-color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color"
                                               class="cuw-color-picker color-picker-container form-control">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['action'])) { ?>
                            <div id="action-styles" class="row cuw-style-group mb-1">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"> <?php esc_html_e("Action styles", 'checkout-upsell-woocommerce'); ?> </h6>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?> </label>
                                    <select class="form-control" name="data[template][styles][action][font-size]"
                                            data-name="font-size" data-target=".cuw-template-action">
                                        <option value="" <?php selected('', $styles['action']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="8px" <?php selected('8px', $styles['action']['font-size']); ?>><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="12px" <?php selected('12px', $styles['action']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="14px" <?php selected('14px', $styles['action']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="16px" <?php selected('16px', $styles['action']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="18px" <?php selected('18px', $styles['action']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="20px" <?php selected('20px', $styles['action']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="24px" <?php selected('24px', $styles['action']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="32px" <?php selected('32px', $styles['action']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">

                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][action][color]" data-name="color"
                                               data-target=".cuw-template-action"
                                               value="<?php echo esc_attr($styles['action']['color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="cuw-color-picker form-control">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">

                                        <input type="text" class="form-control cuw-color-input w-50"
                                               name="data[template][styles][action][background-color]"
                                               data-name="background-color" data-target=".cuw-template-action"
                                               value="<?php echo esc_attr($styles['action']['background-color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="form-control cuw-color-picker">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (isset($styles['footer'])) { ?>
                            <div id="footer-styles" class="row cuw-style-group">
                                <div class="col-md-12 d-flex align-items-center">
                                    <h6 class="form-label"> <?php esc_html_e("Footer styles", 'checkout-upsell-woocommerce'); ?> </h6>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?> </label>
                                    <select class="form-control" name="data[template][styles][footer][font-size]"
                                            data-name="font-size" data-target=".cuw-modal-footer">
                                        <option value="" <?php selected('', $styles['footer']['font-size']); ?>><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="8px" <?php selected('8px', $styles['footer']['font-size']); ?>><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="12px" <?php selected('12px', $styles['footer']['font-size']); ?>><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="14px" <?php selected('14px', $styles['footer']['font-size']); ?>><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="16px" <?php selected('16px', $styles['footer']['font-size']); ?>><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="18px" <?php selected('18px', $styles['footer']['font-size']); ?>><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="20px" <?php selected('20px', $styles['footer']['font-size']); ?>><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="24px" <?php selected('24px', $styles['footer']['font-size']); ?>><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                        <option value="32px" <?php selected('32px', $styles['footer']['font-size']); ?>><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group ">

                                        <input type="text" class="cuw-color-input form-control w-50"
                                               name="data[template][styles][footer][color]" data-name="color"
                                               data-target=".cuw-modal-footer"
                                               value="<?php echo esc_attr($styles['footer']['color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="cuw-color-picker form-control">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="cuw-color-inputs input-group">

                                        <input type="text" class="form-control cuw-color-input w-50"
                                               name="data[template][styles][footer][background-color]"
                                               data-name="background-color" data-target=".cuw-modal-footer"
                                               value="<?php echo esc_attr($styles['footer']['background-color']); ?>"
                                               maxlength="7"
                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                        <input type="color" class="form-control cuw-color-picker">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <?php if ($advanced_section) { ?>
                    <div class="tab-pane fade mt-3" id="advanced-section">
                        <div class="cuw-template-advanced p-3">
                            <?php if (!empty($template['checkbox'])) { ?>
                                <div class="form-group cuw-template-checkbox d-flex flex-column">
                                    <label for="template-checkbox"
                                           class="form-label mb-1 font-weight-semibold"><?php esc_html_e("Checkboxes", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group">
                                        <select class="form-control cuw-border-width" name="data[template][checkbox]"
                                                data-name="border-width" data-target=".cuw-product-checkbox">
                                            <option value="checked" <?php selected('checked', $template['checkbox']); ?>><?php esc_html_e("Checked by default", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="unchecked" <?php selected('unchecked', $template['checkbox']); ?>><?php esc_html_e("Unchecked by default", 'checkout-upsell-woocommerce'); ?></option>
                                            <?php if ($campaign_type == 'fbt') { ?>
                                                <option value="uncheckable" <?php selected('uncheckable', $template['checkbox']); ?> <?php if (!$has_pro) echo 'disabled'; ?>>
                                                    <?php esc_html_e("Uncheckable", 'checkout-upsell-woocommerce'); ?>
                                                    <?php if (!$has_pro) echo ' - ' . esc_html__("PRO", 'checkout-upsell-woocommerce'); ?>
                                                </option>
                                                <option value="hidden" <?php selected('hidden', $template['checkbox']); ?> <?php if (!$has_pro) echo 'disabled'; ?>>
                                                    <?php esc_html_e("Hidden", 'checkout-upsell-woocommerce'); ?>
                                                    <?php if (!$has_pro) echo ' - ' . esc_html__("PRO", 'checkout-upsell-woocommerce'); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if (!empty($template['save_badge'])) { ?>
                                <div class="form-group cuw-template-save-badge d-flex flex-column">
                                    <div class="cuw-save-badge">
                                        <label for="template-save-badge" class="form-label mb-1 font-weight-semibold">
                                            <?php esc_html_e("Show save badge", 'checkout-upsell-woocommerce'); ?>
                                        </label>
                                        <div class="input-group">
                                            <select class="form-control cuw-border-width"
                                                    name="data[template][save_badge]"
                                                    data-name="border-width" data-target=".cuw-product-save-badge">
                                                <option value="do_not_display" <?php selected('do_not_display', $template['save_badge']); ?>><?php esc_html_e("Do not display", 'checkout-upsell-woocommerce'); ?></option>
                                                <option value="only_products" <?php selected('only_products', $template['save_badge']); ?> <?php if (!$has_pro) echo 'disabled'; ?>>
                                                    <?php esc_html_e("Only on products", 'checkout-upsell-woocommerce'); ?>
                                                    <?php if (!$has_pro) echo ' - ' . esc_html__("PRO", 'checkout-upsell-woocommerce'); ?>
                                                </option>
                                                <option value="only_total" <?php selected('only_total', $template['save_badge']); ?> <?php if (!$has_pro) echo 'disabled'; ?>>
                                                    <?php esc_html_e("Only on total section", 'checkout-upsell-woocommerce'); ?>
                                                    <?php if (!$has_pro) echo ' - ' . esc_html__("PRO", 'checkout-upsell-woocommerce'); ?>
                                                </option>
                                                <option value="both_products_and_total" <?php selected('both_products_and_total', $template['save_badge']); ?> <?php if (!$has_pro) echo 'disabled'; ?>>
                                                    <?php esc_html_e("On both products and total section", 'checkout-upsell-woocommerce'); ?>
                                                    <?php if (!$has_pro) echo ' - ' . esc_html__("PRO", 'checkout-upsell-woocommerce'); ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="cuw-save-badge-text mt-2"
                                         style="<?php if (in_array($template['save_badge'], ['do_not_display', 'only_total'])) echo 'display: none;' ?>">
                                        <label for="save-badge-text" class="form-label mb-1 font-weight-semibold">
                                            <?php esc_html_e("Product badge text", 'checkout-upsell-woocommerce'); ?>
                                        </label>
                                        <div class="input-group">
                                            <input class="form-control cuw-border-width"
                                                   name="data[template][save_badge_text]"
                                                   value="<?php echo esc_attr($template['save_badge_text']); ?>"
                                                   data-name="border-width"
                                                   data-target=".cuw-product-badge-display-type">
                                        </div>
                                        <span class="d-block small mb-3" style="opacity: 0.8; font-size: 12px;">
                                            <?php esc_html_e('Available placeholders', 'checkout-upsell-woocommerce'); ?>: {price}, {percentage}
                                        </span>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($campaign_type == 'noc' && !empty($template['message'])) { ?>
                                <div class="form-group cuw-noc-message d-flex flex-column">
                                    <label for="template-checkbox"
                                           class="form-label mb-1 font-weight-semibold"><?php esc_html_e("Coupon message", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="input-group">
                                        <select class="form-control" name="data[template][message]"
                                                data-target=".cuw-template-coupon-message">
                                            <option value="hide" <?php selected('hide', $template['message']); ?>><?php esc_html_e("Hide", 'checkout-upsell-woocommerce'); ?></option>
                                            <option value="show" <?php selected('show', $template['message']); ?>><?php esc_html_e("Show", 'checkout-upsell-woocommerce'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            <?php }
                                do_action('cuw_campaign_template_edit_advanced_fields', $campaign);
                            ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div id="preview" class="col-md-7 border mt-4 p-3 rounded-lg"
             style="background: #f1f5f9; height:70vh; overflow-y: scroll">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="form-label"><?php esc_html_e("PREVIEW", 'checkout-upsell-woocommerce'); ?></h6>
                <div class="btn-group bg-white align-items-center p-1 rounded d-flex" style=";" id="cuw-device-preview">
                    <label class="btn btn-primary m-0">
                        <i class="cuw-icon-desktop inherit-color mx-1"></i>
                        <input type="radio" name="device" value="desktop" class="d-none" checked><span
                                class="mx-1"><?php esc_html_e("Desktop", 'checkout-upsell-woocommerce'); ?></span>
                    </label>
                    <label class="btn btn-light m-0">
                        <i class="cuw-icon-mobile inherit-color mx-1"></i>
                        <input type="radio" name="device" value="mobile" class="d-none"> <span
                                class="mx-1"><?php esc_html_e("Mobile", 'checkout-upsell-woocommerce'); ?></span>
                    </label>
                </div>
            </div>
            <div id="cuw-edit-template-preview" class="mt-3">
                <div class="cuw-template-preview">
                    <?php
                    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo \CUW\App\Helpers\Template::getPreviewHtml(['type' => $campaign_type,
                        'data' => [
                            'discount' => $discount,
                            'template' => $template,
                        ]
                    ]);
                    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
