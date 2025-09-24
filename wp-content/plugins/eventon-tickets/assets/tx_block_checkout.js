(function(wp, wc) {
    console.log('EVOTX: Block script loaded');
    if (!wc?.blocksCheckout) {
        console.error('EVOTX: wc.blocksCheckout not found');
        return;
    }

    const { registerCheckoutBlock } = wc.blocksCheckout;
    const { createElement } = wp.element;
    const { ValidatedTextInput } = wc.blocksCheckout;

    const ExtraFieldsBlock = ({ checkoutExtensionData, cart }) => {
        const fieldsData = window.evotxExtraFields || {};
        console.log('EVOTX: Fields Data:', fieldsData);
        const cartItems = cart?.cartItems || [];
        if (!cartItems.length) {
            console.log('EVOTX: No cart items found');
            return null;
        }

        const extraFields = [];
        Object.entries(fieldsData).forEach(([eventId, fields]) => {
            const eventTickets = cartItems.filter(item => item.meta?.evotx_event_id_wc === eventId);
            if (!eventTickets.length) return;

            eventTickets.forEach((item) => {
                const qty = item.quantity || 1;
                for (let q = 0; q < qty; q++) {
                    const fieldElements = Object.entries(fields).map(([key, field]) => 
                        createElement(ValidatedTextInput, {
                            id: `tixholders[${eventId}][0][${q}][1][${key}]`,
                            type: field.type || 'text',
                            label: field.label,
                            required: field.required || false,
                            value: '',
                            onChange: (value) => {
                                checkoutExtensionData.setExtensionData('evotx', `tixholders_${eventId}_0_${q}_1_${key}`, value);
                                console.log(`EVOTX: Set ${key} to ${value}`);
                            },
                        })
                    );
                    extraFields.push(
                        createElement('div', { className: 'evotx-ticket-holder' },
                            createElement('h3', null, `Ticket Holder #${q + 1} for Event ${eventId}`),
                            ...fieldElements
                        )
                    );
                }
            });
        });

        return extraFields.length ? createElement('div', { className: 'evotx-extra-fields' }, extraFields) : null;
    };

    const blockConfig = {
        metadata: {
            name: 'evotx/extra-checkout-fields',
            title: 'Extra Ticket Holder Fields',
            description: 'Additional fields for ticket holders',           
        },
        component: ExtraFieldsBlock,
        parent: [ 'woocommerce/checkout' ],
    };

    console.log('EVOTX: Block config before registration:', blockConfig);
    console.log('EVOTX: Metadata value:', blockConfig.metadata);

    try {
        registerCheckoutBlock(blockConfig);
        console.log('EVOTX: Block registered successfully');
    } catch (error) {
        console.error('EVOTX: Error registering block:', error);
    }
})(window.wp, window.wc);