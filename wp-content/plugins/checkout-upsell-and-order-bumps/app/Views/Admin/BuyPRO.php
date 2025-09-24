<?php
defined('ABSPATH') || exit;
$buy_pro_url = 'https://upsellwp.com/pricing/?utm_campaign=upsellwp&utm_medium=pro_url&utm_source=buy-pro';
?>
<div class="mt-5 mx-5">
    <div class="cuw-upgrade-box p-4 mx-5">
        <div class="d-flex align-items-center">
            <div class="col-md-8">
                <div class="text-primary h3 mb-4"><?php esc_html_e("Unlock More Revenue with UpsellWP PRO", 'checkout-upsell-and-order-bumps'); ?></div>
                <p class="text-secondary mb-3">
                    <?php esc_html_e("Your customers are already shopping—why not maximize every sale? With UpsellWP PRO, you can seamlessly add upsells, cross-sells, and smart product recommendations at every step of their journey. More value for them, more revenue for you.", 'checkout-upsell-and-order-bumps'); ?>
                </p>
                <a href="<?php echo esc_url($buy_pro_url); ?>" target="_blank" class="text-decoration-none">
                    <button class="btn btn-primary cuw-upgrade-pro-button px-4 py-2"><?php esc_html_e("Buy UpsellWP PRO Now", 'checkout-upsell-and-order-bumps'); ?></button>
                </a>
            </div>
            <div class="col-md-4">
                <img class="img-fluid" src="<?php echo esc_url(\CUW\App\Helpers\Assets::getUrl('img/upsellwp.png')) // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>" alt="UpsellWP Logo">
            </div>
        </div>
    </div>
</div>

<div class="mt-5 mx-5">
    <section class="mx-5 text-center">
        <div class="text-primary h4 mb-4"><?php esc_html_e("Why Upgrade to PRO?", 'checkout-upsell-and-order-bumps'); ?></div>
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4 pb-5">
                <div class="cuw-upgrade-details-box d-flex flex-column p-4">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <i class="cuw-icon-boost-sale"></i>
                        <div class="h5 text-primary m-0" style="text-align: initial;"><?php esc_html_e("Boost Sales with Smart Product Offers", 'checkout-upsell-and-order-bumps'); ?></div>
                    </div>
                    <p class="text-secondary my-3"><?php esc_html_e("Engage shoppers at the right moment with", 'checkout-upsell-and-order-bumps'); ?> :</p>
                    <ul class="list-unstyled text-secondary mt-2">
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark">
                                    <?php esc_html_e("Advanced Frequently Bought Together", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Suggest complementary products right on the product page.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: A customer buying a camera? Show them a tripod and memory card as a bundle.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                           </div>
                        </li>
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark" style="text-align: initial;">
                                    <?php esc_html_e("Add-to-Cart Popup Upsells & Product Recommendations ", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Offer an upsell as soon as an item is added to the cart.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: A shopper adds a phone? Prompt them to grab a screen protector and case.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark">
                                    <?php esc_html_e("Product Add-ons & Cart Add-ons ", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Let customers enhance their purchase with optional extras.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: Selling shoes? Offer premium insoles or shoe care kits right before checkout.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 pb-5">
                <div class="cuw-upgrade-details-box d-flex flex-column p-4">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <i class="cuw-icon-maximum-revenue text-primary"></i>
                        <div class="h5 text-primary m-0" style="text-align: initial;"><?php esc_html_e("Maximize Revenue at Every Stage", 'checkout-upsell-and-order-bumps'); ?></div>
                    </div>
                    <p class="text-secondary my-3"><?php esc_html_e("Don’t leave money on the table. Upsell at key conversion points", 'checkout-upsell-and-order-bumps'); ?> :</p>
                    <ul class="list-unstyled text-secondary mt-2">
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark">
                                    <?php esc_html_e("Cart Page Upsells", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Increase AOV before checkout.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: A customer in the cart page with a coffee maker? Suggest a pack of premium coffee beans.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark" style="text-align: initial;">
                                    <?php esc_html_e("One-Click Checkout Upsells & Order bump", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Make it effortless for customers to say yes..", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: A shopper buying a subscription box? Offer an exclusive one-time add-on at checkout.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark">
                                    <?php esc_html_e("Double the Order Option", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Encourage customers to buy more with a single click.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e('Example: "Double the order, Get 20% Off" – perfect for consumable products like skincare or supplements.', 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 pb-5">
                <div class="cuw-upgrade-details-box d-flex flex-column p-4">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <i class="cuw-icon-more-purchase"></i>
                        <div class="h5 text-primary m-0" style="text-align: initial;"><?php esc_html_e("Turn Post-Purchase into More Purchases", 'checkout-upsell-and-order-bumps'); ?></div>
                    </div>
                    <p class="text-secondary my-3"><?php esc_html_e("Keep selling even after checkout", 'checkout-upsell-and-order-bumps'); ?> :</p>
                    <ul class="list-unstyled text-secondary mt-2">
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark">
                                    <?php esc_html_e("Post-Purchase Upsells & Downsells", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Offer exclusive deals after payment.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: A customer buys a laptop? Show them an extended warranty or premium laptop bag before the order confirmation.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark" style="text-align: initial;">
                                    <?php esc_html_e("Thank You Page Upsells", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Keep customers shopping even after they’ve completed their order.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: Just bought a yoga mat? Offer a limited-time discount on yoga blocks or straps right on the thank you page.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 pb-5">
                <div class="cuw-upgrade-details-box d-flex flex-column p-4">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <i class="cuw-icon-smart-recommendations"></i>
                        <div class="h5 text-primary m-0" style="text-align: initial;"><?php esc_html_e("Smart Product Recommendation Engine", 'checkout-upsell-and-order-bumps'); ?></div>
                    </div>
                    <p class="text-secondary my-3"><?php esc_html_e("UpsellWP PRO’s Recommendation Engine makes upselling smarter and more effective by automatically suggesting", 'checkout-upsell-and-order-bumps'); ?> :</p>
                    <ul class="list-unstyled text-secondary mt-2">
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark">
                                    <?php esc_html_e("Top-Selling Products", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Show what’s popular to drive more conversions.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: Selling clothing? Recommend best-selling jackets or accessories to every shopper.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark" style="text-align: initial;">
                                    <?php esc_html_e("New Arrivals", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Promote fresh inventory at key touchpoints.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: A beauty store can highlight newly launched skincare serums when a customer buys a moisturizer.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3" style="gap: 8px;">
                            <i class="cuw-icon-tick"></i>
                            <div class="d-flex flex-column align-items-start" style="gap: 8px;">
                                <div class="h6 m-0 text-dark">
                                    <?php esc_html_e("Frequently Bought Together Items", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Help customers discover complementary products.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                                <div class="text-justify">
                                    <?php esc_html_e("Example: Someone buys a gaming console? Recommend extra controllers and game titles in one click.", 'checkout-upsell-and-order-bumps'); ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </section>
</div>

<div class="mt-3">
    <section class="text-center">
        <div class="h4 text-primary"><?php esc_html_e("Why Choose UpsellWP PRO", 'checkout-upsell-and-order-bumps'); ?> ?</div>
        <div class="d-flex flex-column align-items-center mt-3">
            <ul class="list-unstyled text-start text-secondary">
                <li class="d-flex align-items-center mb-3" style="gap: 8px;">
                    <div class="d-flex" style="gap: 8px;">
                        <i class="cuw-icon-tick"></i>
                        <div class="h5 m-0 text-dark"><?php esc_html_e("Drive Higher Average Order Value (AOV)", 'checkout-upsell-and-order-bumps'); ?></div>
                    </div>
                    <div><?php esc_html_e("Increase revenue without acquiring new customers.", 'checkout-upsell-and-order-bumps'); ?></div>
                </li>
                <li class="d-flex align-items-center mb-3" style="gap: 8px;">
                    <div class="d-flex" style="gap: 8px;">
                        <i class="cuw-icon-tick"></i>
                        <div class="h5 m-0 text-dark"><?php esc_html_e("Seamless Shopping Experience", 'checkout-upsell-and-order-bumps'); ?></div>
                    </div>
                    <div><?php esc_html_e("No friction, just smart offers at the right time.", 'checkout-upsell-and-order-bumps'); ?></div>
                </li>
                <li class="d-flex align-items-center mb-3" style="gap: 8px;">
                    <div class="d-flex" style="gap: 8px;">
                        <i class="cuw-icon-tick"></i>
                        <div class="h5 m-0 text-dark"><?php esc_html_e("Smart Upsell Strategy", 'checkout-upsell-and-order-bumps'); ?></div>
                    </div>
                    <div><?php esc_html_e("Use data to make better recommendations and maximize sales.", 'checkout-upsell-and-order-bumps'); ?></div>
                </li>
            </ul>
        </div>
    </section>
</div>

<div class="mt-5 d-flex justify-content-center">
    <section class="w-75">
        <div class="cuw-buy-pro-box">
            <div class="mx-auto d-flex flex-column justify-content-center" style="gap: 16px;">
                <div class="h4 text-primary"><?php esc_html_e("Every Order is an Opportunity – Don’t Let It Go to Waste", 'checkout-upsell-and-order-bumps'); ?></div>
                <p class="text-secondary"><?php esc_html_e("With UpsellWP PRO, you’re not just selling—you’re maximizing every sale.", 'checkout-upsell-and-order-bumps'); ?></p>
                <div class="text-dark font-weight-bold"><?php esc_html_e("Upgrade today and start increasing your store revenue effortlessly.", 'checkout-upsell-and-order-bumps'); ?></div>
                <a href="<?php echo esc_url($buy_pro_url); ?>" class="text-decoration-none" target="_blank">
                    <button class="btn btn-primary cuw-upgrade-pro-button px-4 py-2 mx-auto">
                        <?php esc_html_e("Buy UpsellWP PRO Now", 'checkout-upsell-and-order-bumps'); ?>
                    </button>
                </a>
            </div>
        </div>
    </section>
</div>