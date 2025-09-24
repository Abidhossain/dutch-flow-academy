<?php
defined('ABSPATH') || exit;

if (!isset($campaign) || !isset($form) || !isset($campaign_text) || !isset($campaign_type)) {
    return;
}
?>

<div id="offer-header" class="cuw-slider-header d-flex mt-2 align-items-center" data-key="" data-action="">
    <h4 class="cuw-slider-title">
        <?php esc_html_e("Offer", 'checkout-upsell-woocommerce'); ?>
        <span class="offer-index"></span>
    </h4>
    <div class="d-flex <?php echo CUW()->wp->isRtl() ? 'mr-auto' : 'ml-auto'; ?>" style="gap: 8px;">
        <button type="button" id="offer-close" class="btn btn-outline-secondary">
            <i class="cuw-icon-close-circle inherit-color mx-1"></i>
            <?php esc_html_e("Close", 'checkout-upsell-woocommerce'); ?>
        </button>
        <button type="button" id="offer-save" class="btn btn-primary">
            <i class="cuw-icon-tick-circle text-white mx-1"></i>
            <?php esc_html_e("Save", 'checkout-upsell-woocommerce'); ?>
        </button>
    </div>
</div>

<div class="row p-2 justify-content-center " style="gap:20px;">
    <div id="edit" class="mt-3 col-md-4 mt-4 px-0 border rounded-lg" style="height:70vh;overflow-y: scroll">
        <div class="p-0 cuw-tab-container">
            <ul class="nav nav-tabs d-flex justify-content-around border-bottom border-gray-light" id="myTabs"
                role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#offer-details">
                        1.<?php esc_html_e("Offer Info", 'checkout-upsell-woocommerce'); ?>
                    </a>
                </li>

                <?php if (in_array($campaign_type, ['checkout_upsells', 'cart_upsells'])) { ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#template-contents">
                            2.<?php esc_html_e("Content", 'checkout-upsell-woocommerce'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#template-styling">
                            3.<?php esc_html_e("Design", 'checkout-upsell-woocommerce'); ?>
                        </a>
                    </li>
                <?php } ?>

                <?php do_action('cuw_after_offer_tabs', $campaign_type, $campaign); ?>
            </ul>
        </div>

        <div class="offer-data">
            <input type="hidden" name="offer[id]" value="">
            <input type="hidden" name="offer[index]" value="">
            <input type="hidden" name="offer[product_name]">
            <input type="hidden" name="offer[data]">

            <div class="tab-content p-3">
                <div class="tab-pane fade show active" id="offer-details">
                    <div class="legend mb-2"><?php esc_html_e("Offer Product", 'checkout-upsell-woocommerce'); ?></div>
                    <div class="col px-0">
                        <div class="offer-product form-group">
                            <label class="form-label"><?php esc_html_e("Choose an Offer Product", 'checkout-upsell-woocommerce'); ?></label>
                            <select class="select2-list reload-preview form-control" id="offer-product"
                                    name="offer[product_id]"
                                    data-list="products"
                                    data-placeholder="<?php esc_attr_e("Choose product", 'checkout-upsell-woocommerce'); ?>">
                            </select>
                        </div>
                        <div class="offer-product-qty form-group">
                            <label for="offer-product-qty" class="form-label">
                                <?php esc_html_e("Quantity", 'checkout-upsell-woocommerce'); ?>
                                <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
                            </label>
                            <input type="number" class="reload-preview form-control" id="offer-product-qty"
                                   name="offer[product_qty]" min="0"
                                   placeholder="<?php esc_attr_e("Custom", 'checkout-upsell-woocommerce'); ?>">
                        </div>
                        <div class="offer-discount-type form-group">
                            <label for="offer-discount-type"
                                   class="form-label"><?php esc_html_e("Discount type", 'checkout-upsell-woocommerce'); ?></label>
                            <select class="reload-preview form-control" id="offer-discount-type"
                                    name="offer[discount_type]">
                                <option value="percentage"><?php esc_html_e("Percentage discount", 'checkout-upsell-woocommerce'); ?></option>
                                <option value="fixed_price"><?php esc_html_e("Fixed discount", 'checkout-upsell-woocommerce'); ?></option>
                                <option value="free"><?php esc_html_e("Free", 'checkout-upsell-woocommerce'); ?></option>
                                <option value="no_discount"><?php esc_html_e("No discount", 'checkout-upsell-woocommerce'); ?></option>
                            </select>
                        </div>
                        <div class="offer-discount-value form-group">
                            <label for="offer-discount-value"
                                   class="form-label"><?php esc_html_e("Discount value", 'checkout-upsell-woocommerce'); ?></label>
                            <input class="reload-preview form-control" type="number" id="offer-discount-value"
                                   name="offer[discount_value]" min="0"
                                   placeholder="<?php esc_attr_e("Value", 'checkout-upsell-woocommerce'); ?>">
                        </div>
                    </div>

                    <div class="legend mb-2"><?php esc_html_e("Usage limits", 'checkout-upsell-woocommerce'); ?></div>
                    <div class="col px-0">
                        <div class=" offer-limit form-group">
                            <label for="offer-limit" class="form-label">
                                <?php esc_html_e("Overall usage limit", 'checkout-upsell-woocommerce'); ?>
                                <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
                            </label>
                            <input class="form-control" type="number" step="1" id="offer-limit" name="offer[limit]"
                                   min="0"
                                   placeholder="<?php esc_attr_e("Unlimited usage", 'checkout-upsell-woocommerce'); ?>">
                        </div>
                        <div class="">
                            <label for="offer-limit-per-user" class="form-label">
                                <?php esc_html_e("Usage limit per customer", 'checkout-upsell-woocommerce'); ?>
                                <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
                            </label>
                            <input class="form-control" type="number" step="1" id="offer-limit-per-user"
                                   name="offer[limit_per_user]" min="0"
                                   placeholder="<?php esc_attr_e("Unlimited usage", 'checkout-upsell-woocommerce'); ?>">
                        </div>
                    </div>
                </div>

                <?php if (in_array($campaign_type, ['checkout_upsells', 'cart_upsells', 'post_purchase'])) { ?>
                    <div class="tab-pane fade" id="template-contents">
                        <div class="legend mb-2"><?php esc_html_e("Texts", 'checkout-upsell-woocommerce'); ?></div>
                        <div class="row">
                            <div class="col-12 form-group">
                                <label for="offer-title"
                                       class="form-label"><?php esc_html_e("Offer title", 'checkout-upsell-woocommerce'); ?></label>
                                <input type="text" class="form-control" id="offer-title">
                            </div>
                            <div class="col-12 form-group"
                                 style="<?php if ($campaign_type == 'cart_upsells') echo 'display: none;'; ?>">
                                <label for="offer-description"
                                       class="form-label"><?php esc_html_e("Offer description", 'checkout-upsell-woocommerce'); ?></label>
                                <textarea id="offer-description" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="col-12 form-group">
                                <label for="offer-cta-text"
                                       class="form-label"><?php esc_html_e("Offer CTA text", 'checkout-upsell-woocommerce'); ?></label>
                                <input type="text" id="offer-cta-text" class="form-control">
                            </div>
                        </div>

                        <div class="legend mb-2"><?php esc_html_e("Image", 'checkout-upsell-woocommerce'); ?></div>
                        <div class="row px-0">
                            <label for="offer-cta-text"
                                   class="form-label col-12"><?php esc_html_e("Offer image", 'checkout-upsell-woocommerce'); ?></label>

                            <div id="offer-image-type" class="col-12 d-flex" style="gap:12px;">
                                <div class="custom-control flex-fill custom-radio custom-control rounded px-3 py-2">
                                    <input type="radio" class="offer-image-radio custom-control-input position-relative"
                                           id="product-image" name="offer-image" value="0">
                                    <label class="custom-control-label"
                                           for="product-image"><?php esc_html_e("Product image", 'checkout-upsell-woocommerce'); ?></label>
                                </div>
                                <div class="custom-control flex-fill custom-radio custom-control rounded px-3 py-2">
                                    <input type="radio" class="offer-image-radio custom-control-input position-relative"
                                           id="custom-image" name="offer-image" value="">
                                    <label class="custom-control-label"
                                           for="custom-image"><?php esc_html_e("Custom image", 'checkout-upsell-woocommerce'); ?></label>
                                </div>
                            </div>
                            <div class="col">
                                <button style="gap:8px; display: flex; align-items: center; justify-content: center; "
                                        type="button"
                                        class="btn btn-outline-primary w-100 btn-sm px-3 mt-3 "
                                        id="select-image">
                                    <i class="cuw-icon-image text-primary mx-1"></i><?php esc_html_e("Select / Change image", 'checkout-upsell-woocommerce'); ?>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" id="offer-image-id" value="0">
                    </div>
                <?php } ?>

                <?php if (in_array($campaign_type, ['checkout_upsells', 'cart_upsells'])) { ?>
                    <div class="tab-pane fade" id="template-styling">
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <input type="hidden" id="template-name" value="">

                            <div class="legend mb-2"><?php esc_html_e("Template", 'checkout-upsell-woocommerce'); ?></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="offer-template-name"
                                           class="form-label"><?php esc_html_e("Active template", 'checkout-upsell-woocommerce'); ?></label>
                                    <div class="text-dark" id="offer-template-name"></div>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary" id="change-offer-template">
                                        <i class="cuw-icon-campaigns inherit-color mx-1"></i><?php esc_html_e("Change template", 'checkout-upsell-woocommerce'); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="legend  mb-2">
                                <div class="row">
                                    <div class="col-md-8">
                                        <?php esc_html_e("Styles", 'checkout-upsell-woocommerce'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="offer-cta-text"
                                           class="form-label"><?php esc_html_e("Custom styling", 'checkout-upsell-woocommerce'); ?></label>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox d-inline-block">
                                        <input type="checkbox" class="custom-control-input" id="custom-styling">
                                        <label class="custom-control-label" for="custom-styling">Enable</label>
                                    </div>
                                    <a id="reset-styles" class="text-primary ml-4 text-decoration-none cursor-pointer">
                                        <i class="cuw-icon-reset text-primary px-1"
                                           style="font-size:14px; font-weight: 600"></i>
                                        <?php esc_html_e("Reset", 'checkout-upsell-woocommerce'); ?>
                                    </a>
                                </div>
                            </div>
                            <div id="custom-styles">
                                <form id="template-styles" class="cuw-style-group">
                                    <div class="row">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h6 class="form-label"><?php esc_html_e("Template", 'checkout-upsell-woocommerce'); ?></h6>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label"><?php esc_html_e("Border", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="input-group" style="gap: 8px;">
                                                <select class="form-control cuw-border-width" name="border-width"
                                                        style="min-width: 50px;">
                                                    <option value="0"><?php esc_html_e("None", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="thin"><?php esc_html_e("Thin", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="medium"><?php esc_html_e("Medium", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="thick"><?php esc_html_e("Thick", 'checkout-upsell-woocommerce'); ?></option>
                                                </select>
                                                <select class="form-control cuw-border-style" name="border-style"
                                                        style="min-width: 50px;">
                                                    <option value="solid"><?php esc_html_e("Solid", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="double"><?php esc_html_e("Double", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="dotted"><?php esc_html_e("Dotted", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="dashed"><?php esc_html_e("Dashed", 'checkout-upsell-woocommerce'); ?></option>
                                                </select>
                                                <div class="cuw-border-color">
                                                    <div class="cuw-color-inputs input-group position-relative"
                                                         style="gap: 8px;">
                                                        <input type="text" class="cuw-color-input form-control w-50"
                                                               name="border-color"
                                                               maxlength="7"
                                                               placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                        <input style="top:0; right: 0; height: 36px; width: 48px;"
                                                               type="color"
                                                               class="cuw-color-picker border-left-0 rounded-right position-absolute form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="cuw-color-inputs input-group position-relative"
                                                 style="gap: 8px;">
                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="background-color"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input style="top:0; right: 0; height: 36px; width: 48px;" type="color"
                                                       class="cuw-color-picker border-left-0 rounded-right position-absolute form-control"/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <form id="title-styles">
                                    <div class="row " style="">
                                        <div class=" col-md-12">
                                            <h6 class=""><?php esc_html_e("Title / Banner", 'checkout-upsell-woocommerce'); ?></h6>
                                        </div>
                                        <div class="col-md-6" style="gap:12px;">
                                            <div class="form-group m-0">
                                                <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                                <div class="input-group " style="gap: 12px;">
                                                    <select class="form-control" name="font-size">
                                                        <option value=""><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="8px"><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="12px"><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="14px"><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="16px"><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="18px"><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="20px"><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="24px"><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                                        <option value="32px"><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group m-0">
                                                <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                                <div class="cuw-color-inputs input-group position-relative"
                                                     style="gap: 8px;">
                                                    <input type="text" class="cuw-color-input form-control w-50"
                                                           name="color"
                                                           maxlength="7"
                                                           placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                    <input type="color"
                                                           style="top:0; right: 0; height: 36px; width: 48px;"
                                                           class="cuw-color-picker border-left-0 rounded-right position-absolute  form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 flex-column  form-group m-0" style="gap: 10px;">
                                            <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="cuw-color-inputs input-group position-relative"
                                                 style="gap: 8px;">
                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="background-color"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                                       class="cuw-color-picker border-left-0 rounded-right position-absolute form-control">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <form id="description-styles"
                                      style="<?php if ($campaign_type == 'cart_upsells') echo 'display: none;'; ?>">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="mt-4"><?php esc_html_e("Description section", 'checkout-upsell-woocommerce'); ?></h6>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="input-group" style="gap: 8px;">
                                                <select class="form-control" name="font-size">
                                                    <option value=""><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="8px"><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="12px"><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="14px"><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="16px"><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="18px"><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="20px"><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="24px"><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="32px"><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group ">
                                            <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="cuw-color-inputs input-group position-relative"
                                                 style="gap: 8px;">
                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="color"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                                       class="cuw-color-picker border-left-0 rounded-right position-absolute form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="cuw-color-inputs input-group position-relative"
                                                 style="gap: 8px;">
                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="background-color"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input type="color" style="top:0; right: 0; height: 36px; width: 48px;"
                                                       class="cuw-color-picker border-left-0 rounded-right position-absolute form-control">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <form id="cta-styles">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6 class="mt-4"><?php esc_html_e("CTA section", 'checkout-upsell-woocommerce'); ?></h6>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label"><?php esc_html_e("Font size", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="input-group">
                                                <select class="form-control" name="font-size">
                                                    <option value=""><?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="8px"><?php esc_html_e("8px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="12px"><?php esc_html_e("12px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="14px"><?php esc_html_e("14px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="16px"><?php esc_html_e("16px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="18px"><?php esc_html_e("18px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="20px"><?php esc_html_e("20px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="24px"><?php esc_html_e("24px", 'checkout-upsell-woocommerce'); ?></option>
                                                    <option value="32px"><?php esc_html_e("32px", 'checkout-upsell-woocommerce'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label"><?php esc_html_e("Font color", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="cuw-color-inputs input-group" style="gap: 8px;">
                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="color"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input style="top:0; right: 0; height: 36px; width: 48px;" type="color"
                                                       class="cuw-color-picker border-left-0 rounded-right position-absolute form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label"><?php esc_html_e("Background color", 'checkout-upsell-woocommerce'); ?></label>
                                            <div class="cuw-color-inputs input-group position-relative"
                                                 style="gap: 8px;">

                                                <input type="text" class="cuw-color-input form-control w-50"
                                                       name="background-color"
                                                       maxlength="7"
                                                       placeholder="<?php esc_html_e("Default", 'checkout-upsell-woocommerce'); ?>">
                                                <input style="top:0; right: 0; height: 36px; width: 48px;" type="color"
                                                       class="cuw-color-picker border-left-0 rounded-right position-absolute form-control">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php do_action('cuw_after_offer_tab_contents', $campaign_type, $campaign); ?>
            </div>
        </div>
    </div>

    <div id="preview" class="col-md-7 d-flex flex-column border mt-4 p-3 rounded-lg"
         style="gap:12px;background: #F2F4F7; height:70vh; overflow-y: scroll">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="form-label"><?php esc_html_e("PREVIEW", 'checkout-upsell-woocommerce'); ?></h6>
            <div class="btn-group bg-white border border-gray-light rounded p-1 d-flex" id="cuw-device-preview">
                <label class="btn btn-primary m-0">
                    <i class="cuw-icon-desktop inherit-color mx-1"></i>
                    <input type="radio" name="device" value="desktop" class="d-none" checked>
                    <span class="mx-1"><?php esc_html_e("Desktop", 'checkout-upsell-woocommerce'); ?></span>
                </label>
                <label class="btn  m-0">
                    <i class="cuw-icon-mobile inherit-color mx-1"></i>
                    <input type="radio" name="device" value="mobile" class="d-none">
                    <span class="mx-1"><?php esc_html_e("Mobile", 'checkout-upsell-woocommerce'); ?></span>
                </label>
            </div>
        </div>
        <div id="cuw-preview">
            <div class="offer-preview"></div>
        </div>
    </div>
</div>

