const {registerPlugin} = wp.plugins;
const {
    ExperimentalOrderMeta,
    ExperimentalOrderShippingPackages,
    ExperimentalDiscountsMeta
} = wc.blocksCheckout;

const {__} = wp.i18n;
const {useState, useCallback} = wp.element;

/* Show upsell offers */
const UpsellOffers = ({cart, extensions, context, location}) => {
    let html = '';
    if (context === 'woocommerce/cart' && extensions.cuw_upsell_offers) {
        html = extensions.cuw_upsell_offers.cart_upsells[location];
    } else if (context === 'woocommerce/checkout' && extensions.cuw_upsell_offers) {
        html = extensions.cuw_upsell_offers.checkout_upsells[location];
    }
    return html ? <div dangerouslySetInnerHTML={{__html: html}}></div> : '';
};

/* Upsell offers block settings */
function getSettings() {
    return wc.wcSettings.getSetting("cuw-upsell-offers_data");
}

/* Upsell offers block setting */
function getSetting($key, value = '') {
    let settings = getSettings();
    return settings[$key] ? settings[$key] : value;
}

const renderUpsellOffers = () => {
    return (
        <>
            <ExperimentalOrderMeta>
                <UpsellOffers location="order_meta"/>
            </ExperimentalOrderMeta>
            <ExperimentalDiscountsMeta>
                <UpsellOffers location="coupon"/>
            </ExperimentalDiscountsMeta>
            <ExperimentalOrderShippingPackages>
                <UpsellOffers location="shipping"/>
            </ExperimentalOrderShippingPackages>
        </>
    );
}

/* Render offers */
const render = () => {
    return <>{renderUpsellOffers()}</>
};

/* Register offers block */
registerPlugin('cuw-upsell-offers', {
    render,
    scope: 'woocommerce-checkout',
});
