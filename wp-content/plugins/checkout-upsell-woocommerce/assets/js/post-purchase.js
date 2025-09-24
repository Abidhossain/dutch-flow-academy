jQuery(function ($) {
    let cuw_i18n = cuw_admin.i18n || {};
    let cuw_data = cuw_admin.data || {};
    let cuw_views = cuw_admin.views || {};
    let cuw_ajax_url = cuw_admin.ajax_url;
    let cuw_ajax_nonce = cuw_admin.ajax_nonce;

    const cuw_post_purchase = {
        init: function () {
            cuw_post_purchase.event_listeners();
            cuw_post_purchase.default_triggers();
        },

        get_offers_count: function () {
            return $("#cuw-campaign .offer-flow .cuw-offer").length;
        },

        // get new key
        get_offer_index: function (uuid) {
            let offer_index = 0
            $("#cuw-campaign .offer-flow .cuw-offer").each(function (index, el) {
                if ($(el).data('key') === uuid) {
                    return offer_index = index + 1;
                }
            });
            return offer_index;
        },

        get_new_uuid: function (length = 8) {
            const chars = "abcde0123456789";
            let uuid = "";
            for (let i = 0; i < length; i++) {
                uuid += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return uuid;
        },

        update_offer_section: function () {
            if ($("#cuw-campaign .offer-flow .cuw-offer").length === 0) {
                $("#no-offers").removeClass('d-none').addClass('d-flex');
            } else {
                $("#no-offers").removeClass('d-flex').addClass('d-none');
            }
        },

        // load or destroy select2
        select2: function (selector = '', action = 'load') {
            selector = (selector != '' ? selector + ' ' : '');
            if (action == 'destroy') {
                $(selector + ".select2-list").select2('destroy');
                $(selector + ".select2-local").select2('destroy');
                return;
            }

            $(selector + ".select2-list").select2({
                width: "100%",
                minimumInputLength: 1,
                language: {
                    noResults: function () {
                        return cuw_i18n.select2_no_results;
                    },
                    errorLoading: function () {
                        return cuw_i18n.select2_error_loading;
                    }
                },
                ajax: {
                    url: cuw_ajax_url,
                    type: "POST",
                    dataType: "json",
                    delay: 250,
                    data: function (params) {
                        let data = $(this).data();
                        let method = $(this).data('list');
                        ['list', 'select2', 'select2Id', 'placeholder'].forEach(key => delete data[key]);
                        return {
                            query: params.term,
                            action: 'cuw_ajax',
                            method: "list_" + method,
                            params: data,
                            nonce: cuw_ajax_nonce || "",
                        }
                    },
                    processResults: function (response) {
                        return {results: response.data || []};
                    }
                }
            });

            $(selector + ".select2-local").select2({width: "100%"});
        },

        // get offer data
        get_data: function (key = '{uuid}') {
            let selector = '#cuw-post-purchase-offer-page [name="offers[' + key + ']';
            return {
                id: $('#cuw-campaign ' + selector + '[id]"]').val() || 0,
                product_id: $('#cuw-campaign ' + selector + '[product_id]"]').val() || '',
                product_name: $('#cuw-campaign ' + selector + '[product_name]"]').val() || '',
                product_qty: $('#cuw-campaign ' + selector + '[product_qty]"]').val() || '',
                discount_type: $('#cuw-campaign ' + selector + '[discount_type]"]').val() || 'percentage',
                discount_value: $('#cuw-campaign ' + selector + '[discount_value]"]').val() || '',
                image_id: $('#cuw-campaign ' + selector + '[data][image_id]"]').val() || 0,
                limit: $('#cuw-campaign ' + selector + '[limit]"]').val() || '',
                limit_per_user: $('#cuw-campaign ' + selector + '[limit_per_user]"]').val() || '',
                used: $('#cuw-campaign ' + selector + '[used]"]').val() || 0,
                views: $('#cuw-campaign ' + selector + '[views]"]').val() || 0,
                data: $('#cuw-campaign ' + selector + '[data]"]').val() || JSON.stringify(cuw_post_purchase.get_default_page_data()),
            }
        },

        // get default page data
        get_default_page_data: function () {
            return cuw_data.default_page_data;
        },

        // preview offer template
        preview: function () {
            let preview = $("#cuw-campaign #cuw-post-purchase-offer-page .template-preview .cuw-offer");
            let offer_data = cuw_post_purchase.get_data();
            let template_data = cuw_post_purchase.get_offer_template_data();
            cuw_post_purchase.load_template(preview, offer_data, template_data, template_data.image_id);
        },

        // to load template through ajax
        load_template: function (target, offer_data, template_data = null, image_id = 0) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'get_ppu_offer_template',
                    product: {id: offer_data.product_id, qty: offer_data.product_qty},
                    discount: {type: offer_data.discount_type, value: offer_data.discount_value},
                    data: {template: template_data, image_id: image_id},
                    nonce: cuw_ajax_nonce || ""
                },
                beforeSend: function () {
                    target.css('opacity', 0.5);
                },
                success: function (response) {
                    target.css('opacity', 1);
                    if (response.data && response.data.html) {
                        target.html(response.data.html);
                        $("#cuw-offer-tab-section .cuw-text-section :input").trigger('input');
                        $("#cuw-offer-tab-section .cuw-design-section .cuw-color-input, #cuw-offer-tab-section .cuw-design-section select").each(function (index, el) {
                            cuw_post_purchase.update_style($(el));
                        });
                        $("#cuw-campaign #cuw-post-purchase-offer-page #offer-details-notice").trigger('change');

                        let order_notice = $("#cuw-campaign #cuw-post-purchase-offer-page #offer-notice-enabled");
                        $("#cuw-campaign #cuw-post-purchase-offer-page #order-details-section").css('display', (order_notice.val() == 1 ? 'block' : 'none'));
                        order_notice.prop('checked', (order_notice.val() == 1) ? true : false);
                        $("#cuw-campaign #cuw-post-purchase-offer-page #order-details-section :input").prop('disabled', (order_notice.val() != 1));
                        if (order_notice.val() == 1) {
                            $("#cuw-campaign #cuw-post-purchase-offer-page #offer-details-notice").trigger('change');
                        } else {
                            cuw_post_purchase.change_notice_type('hide', '.cuw-ppu-order-details');
                        }

                        let offer_timer = $("#cuw-campaign #cuw-post-purchase-offer-page #offer-timer-enabled");
                        $("#cuw-campaign #cuw-post-purchase-offer-page #timer-details-section").css('display', (offer_timer.val() == 1 ? 'block' : 'none'));
                        offer_timer.prop('checked', (offer_timer.val() == 1) ? true : false);
                        $("#cuw-campaign #cuw-post-purchase-offer-page #timer-details-section :input").prop('disabled', (offer_timer.val() != 1));
                        if (offer_timer.val() == 1) {
                            $("#cuw-campaign #cuw-post-purchase-offer-page #timer-notice-type").trigger('change');
                        } else {
                            cuw_post_purchase.change_notice_type('hide', '.cuw-ppu-offer-timer');
                        }

                        let order_totals = $("#cuw-campaign #cuw-post-purchase-offer-page #order-totals-enabled");
                        $("#cuw-campaign #cuw-post-purchase-offer-page .cuw-ppu-order-totals").css('display', (order_totals.val() == 1 ? 'block' : 'none'));
                        order_totals.prop('checked', (order_totals.val() == 1) ? true : false);
                    } else {
                        target.html('');
                    }
                }
            });
        },

        // get image
        get_image: function (image_id, product_id = 0) {
            let html;
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                async: false,
                data: {
                    action: 'cuw_ajax',
                    method: 'get_offer_image',
                    image_id: image_id,
                    product_id: product_id,
                    nonce: cuw_ajax_nonce || ""
                },
                success: function (response) {
                    html = response.data.html ?? "";
                }
            });
            return html;
        },

        // set offer data
        set_data: function (uuid, data = null) {
            let offer = $("#cuw-campaign .offer-flow #offer-" + uuid + " .offer-item-" + uuid);
            setTimeout(function () {
                let image_id = data && data.image_id ? data.image_id : 0;
                let product_id = (image_id == 0 || image_id == '0') && data && data.product_id ? data.product_id : 0;
                offer.find(".offer-item-image").html(cuw_post_purchase.get_image(image_id, product_id));
            }, 0);
            offer.find(".offer-item-name").html(data.product_name ? data.product_name : '');
            offer.find(".offer-item-qty").html(data.product_qty ? data.product_qty : 'custom');
            let item_discount = data.discount_type ? cuw_helper.get_discount_text(data.discount_type, data.discount_value) : '';
            if (item_discount) {
                offer.find(".discount-separator").hide();
                offer.find(".discount-block").show();
                offer.find(".offer-item-discount").html(item_discount);
            } else {
                offer.find(".discount-separator").hide();
                offer.find(".discount-block").hide();
            }
        },

        // get offer template data
        get_offer_template_data: function () {
            return {
                title: $("#cuw-post-purchase-offer-page #offer-title").val(),
                description: $("#cuw-post-purchase-offer-page #offer-description").val(),
                cta_text: $("#cuw-post-purchase-offer-page #button-text").val(),
                image_id: $("#cuw-post-purchase-offer-page #offer-image-id").val(),
                template_id: $("#cuw-campaign #cuw-ppu-template-id").val(),
            };
        },

        // add validation attention message
        show_field_attention: function (div, message = cuw_i18n.this_field_is_required) {
            div.append('<small class="invalid text-danger d-block mt-1">' + message + '</small>');
            div.find('input, select, .select2-selection').addClass('border-danger');
        },

        // remove validation attention message
        hide_field_attention: function (div) {
            div.find('.invalid').remove();
            div.find('input, select, .select2-selection').removeClass('border-danger');
        },

        // select image for offer
        select_image: function () {
            let image_frame;
            if (image_frame) {
                image_frame.open();
            }

            image_frame = wp.media({
                title: 'Select Media',
                multiple: false,
                library: {
                    type: 'image',
                }
            });

            image_frame.on('close', function () {
                let image_ids = [];
                let selection = image_frame.state().get('selection');
                selection.each(function (attachment) {
                    image_ids.push(attachment['id']);
                });
                if (image_ids.length !== 0) {
                    $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-content-image #custom-image").val(image_ids.join(","));
                    cuw_post_purchase.preview();
                }
            });

            image_frame.on('open', function () {
                let image_ids = $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-content-image #custom-image").val().split(",");

                let selection = image_frame.state().get('selection');
                image_ids.forEach(function (id) {
                    let attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
            });

            image_frame.open();
        },

        hide_custom_options: function () {
            $("#cuw-campaign #cuw-post-purchase-offer-page .cuw-custom-option").each(function (index, el) {
                $(el).removeClass('border-bottom');
                $("#cuw-campaign #cuw-post-purchase-offer-page " + $(el).data('target')).hide();
                $(el).find('.accordion-icon i').removeClass('cuw-icon-accordion-close').addClass('cuw-icon-accordion-open');
            });
        },

        show_offer_page: function (action = 'add', offer_uuid = '', offer_type = '') {
            let uuid = (action === 'edit') ? offer_uuid : cuw_post_purchase.get_new_uuid();
            $("#cuw-post-purchase-offer-page #post-purchase-offer-header").data('uuid', uuid).data('action', action)
                .data('parent_uuid', offer_uuid).data('offer_type', offer_type);
            if (action === 'edit') {
                let image_id = $("#cuw-campaign .offer-flow li #offer-" + offer_uuid + "-data #offer-image-id").val();
                if (image_id != 0) {
                    $("#cuw-post-purchase-offer-page #cuw-offer-tab-section #custom-image").val(image_id);
                    $("#cuw-post-purchase-offer-page #cuw-offer-tab-section #select-image").show();
                } else {
                    $("#cuw-post-purchase-offer-page #cuw-offer-tab-section #select-image").hide();
                }
                $("#cuw-campaign .offer-flow li #offer-" + offer_uuid + "-data :input").each(function (index, el) {
                    $("#cuw-post-purchase-offer-page #cuw-offer-tab-section :input").eq(index).val($(this).val());
                });
                let offer_id = $("#cuw-campaign .offer-flow li #offer-" + offer_uuid + "-data #offer-product select").html();
                $("#cuw-post-purchase-offer-page #cuw-offer-tab-section #offer-product select").html(offer_id);
                let discount_select = $("#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-type select");
                if (discount_select.val() === 'free' || discount_select.val() === 'no_discount') {
                    $("#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-value").hide();
                } else {
                    $("#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-value").show();
                }
                $("#cuw-offer-tab-section .cuw-text-section :input").trigger('input');
                $('#cuw-campaign .cuw-template-border .cuw-border-width').trigger('change');
                $('#cuw-campaign #cuw-post-purchase-offer-page #offer-product select').trigger('change');
            } else {
                let option_tab = cuw_views.offer.post_purchase_offer_data
                $("#cuw-post-purchase-offer-page #cuw-offer-tab-section").html(option_tab);
            }
            cuw_post_purchase.preview();
            $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-option-tab").find('.invalid').remove();
            $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-content-section-tab").addClass('cuw-active-tab');
            $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-design-section-tab").removeClass('cuw-active-tab');
            $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-content-section").removeClass('d-none').addClass('d-flex');
            $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-design-section").removeClass('d-flex').addClass('d-none');
            cuw_post_purchase.hide_custom_options();
            $("#cuw-offer-tab-section .cuw-design-section .cuw-color-input, #cuw-offer-tab-section .cuw-design-section select").each(function (index, el) {
                cuw_post_purchase.update_style($(el));
            });
            cuw_post_purchase.select2('#cuw-post-purchase-offer-page #cuw-offer-tab-section #offer-product');

            window.scrollTo(0, 0);
            $("#campaign-form").hide();
            $("#cuw-post-purchase-offer-page").show();
        },

        hide_offer_page: function () {
            $("#cuw-post-purchase-offer-page .back-to-options-tab").trigger('click');
            $("#cuw-post-purchase-offer-page").hide();
            $("#campaign-form").show();
        },

        // save offer
        save_offer: function () {
            let offer_header = $("#cuw-campaign #post-purchase-offer-header");
            let data = cuw_post_purchase.get_data();

            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'save_offer',
                    campaign_id: 0,
                    offer: data,
                    nonce: cuw_ajax_nonce || ""
                },
                beforeSend: function () {
                    cuw_page.spinner('show');
                },
                complete: function () {
                    cuw_page.spinner('hide');
                },
                success: function (response) {
                    if (response && response.data) {
                        let status = response.data.status ?? "";
                        let message = response.data.message ?? "";
                        cuw_post_purchase.hide_field_attention($("#cuw-campaign #offer-slider #offer-details [name]").parent());
                        if (status === "error" && typeof message === "object") {
                            if (message.fields) {
                                $.each(message.fields, function (field_name) {
                                    let input_name = field_name.replace('offer', 'offers[{uuid}]');
                                    let div = $('#cuw-campaign #cuw-post-purchase-offer-page [name^="' + input_name + '"]').parent();
                                    cuw_post_purchase.hide_field_attention(div);
                                    cuw_post_purchase.show_field_attention(div, message.fields[field_name]);
                                });
                            }
                            message = cuw_i18n.offer_not_saved;
                        } else if (status === 'success') {
                            cuw_post_purchase.save_node(offer_header);
                            cuw_post_purchase.set_data(offer_header.data('uuid'), data);
                            if ($("#campaign-id").val() === '0') {
                                $("#cuw-campaign #campaign-actions #switch-campaign-enable").attr('checked', false);
                            }
                            $("#cuw-campaign #campaign-save").trigger('click');
                            $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-option-tab").find('.invalid').remove();
                        }
                        if (message) {
                            cuw_page.notify(message, status);
                        }
                        cuw_post_purchase.update_offer_section();
                    }
                }
            });
        },

        save_node: function (offer_header) {
            let uuid = offer_header.data('uuid');
            let parent_uuid = offer_header.data('parent_uuid');
            let offer_type = offer_header.data('offer_type');
            let action = offer_header.data('action');
            if (uuid === '') {
                uuid = cuw_post_purchase.get_new_uuid();
                offer_header.data('uuid', uuid);
            }
            let content = cuw_views.offer.post_purchase_offer.replace(/{uuid}/g, uuid);
            let offer_data = $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-offer-tab-section").html().replace(/{uuid}/g, uuid);

            content = content.replace(/{offer_type}/g, offer_type);
            offer_data = offer_data.replace(/{uuid}/g, uuid);

            if (cuw_post_purchase.get_offers_count() > 0) {
                offer_data = offer_data.replace(/{parent_uuid}/g, parent_uuid);
                let parent_offer_depth = $('#cuw-campaign .offer-flow input[name="data[offers_map][' + parent_uuid + '][depth]"]').val();
                let parent_offer_position = $('input[name="data[offers_map][' + parent_uuid + '][position]"]').val();
                let position = parseInt(parent_offer_position > 1 ? parent_offer_position : 0) + (offer_type === 'accept' ? 1 : 2);
                offer_data = offer_data.replace(/{depth}/g, parseInt(parent_offer_depth) + 1);
                offer_data = offer_data.replace(/{position}/g, position);
                $('#cuw-campaign .offer-data input[name="offers[' + parent_uuid + '][id]"]').closest('.offer-data')
                    .find('input[name="data[offers_map][' + parent_uuid + '][' + offer_type + '_uuid]"]').val(uuid);
                $('#cuw-campaign .offer-flow #offer-' + parent_uuid + ' .' + offer_type + '-' + parent_uuid
                    + ' .add-' + offer_type + '-' + parent_uuid + '-offer-section').addClass('d-none');
                if (action === 'add') {
                    $('#cuw-campaign .offer-flow #offer-' + parent_uuid + ' .' + offer_type + '-' + parent_uuid).append(content);
                }
                $("#cuw-campaign .offer-flow li #offer-" + uuid + "-data").html(offer_data);
                if (parent_offer_depth > 1 && action === 'add') {
                    $("#cuw-campaign .offer-flow li #offer-" + uuid + "").find('ul').removeClass('d-flex').addClass('d-none');
                    $("#cuw-campaign .offer-flow li #offer-" + uuid + " #offer-" + uuid + "-exit-badge").removeClass('d-none').addClass('d-flex');
                }
            } else {
                offer_data = offer_data.replace(/{parent_uuid}/g, '');
                offer_data = offer_data.replace(/{depth}/g, '1');
                offer_data = offer_data.replace(/{position}/g, '1');
                if (action === 'add') {
                    $('#cuw-campaign .offer-flow li').html(content);
                }
                $("#cuw-campaign .offer-flow li #offer-" + uuid + "-data").html(offer_data);
            }
            $("#cuw-campaign .offer-flow li .offer-item-" + uuid).find('.offer-view').css({'background-color': '#E8EAED'});
            $("#cuw-campaign .offer-flow li .offer-item-" + uuid).find('.offer-view-link').css('pointer-events', 'none');
            $("#cuw-campaign #cuw-post-purchase-offer-page #cuw-offer-tab-section #cuw-offer-data :input").each(function (index, el) {
                $("#cuw-campaign .offer-flow li #offer-" + uuid + "-data #cuw-offer-data :input").eq(index).val($(this).val());
            });
            $("#cuw-post-purchase-offer-page #post-purchase-offer-header #back-to-campaigns-list").addClass('d-none');
            $("#cuw-post-purchase-offer-page #post-purchase-offer-header #back-to-campaign-page").removeClass('d-none');
        },

        // remove offer
        remove_offer: function (uuid, offer_type, show_notice = true) {
            $("#cuw-campaign #modal-remove").modal('hide');
            let offer_id = $('#cuw-campaign .offer-flow [name="offers[' + uuid + '][id]"]').val();
            cuw_post_purchase.remove_node(uuid, offer_id, offer_type)
            if (offer_id && offer_id !== '0') {
                $.ajax({
                    type: 'post',
                    url: cuw_ajax_url,
                    data: {
                        action: 'cuw_ajax',
                        method: 'delete_offer',
                        id: offer_id,
                        nonce: cuw_ajax_nonce || ""
                    },
                    beforeSend: function () {
                        cuw_page.spinner('show');
                    },
                    complete: function () {
                        cuw_page.spinner('hide');
                    },
                    success: function (response) {
                        if (response.data && response.data.status && response.data.message) {
                            $('#cuw-campaign .offer-flow #offer-' + uuid).remove();
                            cuw_post_purchase.update_offer_section();
                            if (show_notice) {
                                cuw_page.notify(response.data.message, response.data.status);
                            }
                        }
                    }
                });
            } else {
                $('#cuw-campaign .offer-flow #offer-' + uuid).remove();
                cuw_post_purchase.update_offer_section();
            }
        },

        remove_node: function (offer_uuid, offer_id, offer_type) {
            let parent_offer_uuid = $("#offer-" + offer_uuid + "-data .parent-offer-uuid").val();
            let accept_offer_uuid = $("#offer-" + offer_uuid + "-data .accept-offer-uuid").val();
            let decline_offer_uuid = $("#offer-" + offer_uuid + "-data .decline-offer-uuid").val();

            $("#cuw-campaign .offer-flow .cuw-offer .offer-uuid").each(function (index, element) {
                if ($(element).val() === parent_offer_uuid) {
                    $("#cuw-campaign input[name='data[offers_map][" + parent_offer_uuid + "][" + offer_type + "_uuid]']").val('');
                    $("#cuw-campaign .offer-flow .add-" + offer_type + "-" + parent_offer_uuid + "-offer-section").removeClass('d-none');
                } else if ($(element).val() === accept_offer_uuid) {
                    cuw_post_purchase.remove_offer($(element).closest('.cuw-offer').data('key'), 'accept', false);
                } else if ($(element).val() === decline_offer_uuid) {
                    cuw_post_purchase.remove_offer($(element).closest('.cuw-offer').data('key'), 'decline', false);
                }
            });
        },

        default_triggers: function () {
            $("#cuw-offer-tab-section .cuw-text-section :input").trigger('input');
        },

        update_style: function (section) {
            if (!section.data('name')) {
                return;
            }
            $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview').find(section.data('target')).css(section.data('name'), section.val());
        },

        change_notice_type: function (notice_type, section) {
            if (notice_type === 'custom') {
                $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview ' + section + '.cuw-built-in').show();
                $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview ' + section + '.cuw-wc-notice').hide();
            } else if (notice_type === 'wc_notice') {
                $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview ' + section + '.cuw-built-in').hide();
                $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview ' + section + '.cuw-wc-notice').show();
            } else {
                $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview ' + section + '.cuw-built-in').hide();
                $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview ' + section + '.cuw-wc-notice').hide();
            }
        },

        event_listeners: function () {
            $(document).on('click', '#cuw-post-purchase-offer-page .cuw-offer-customize-tab', function () {
                if (!$(this).hasClass('cuw-active-tab')) {
                    $("#cuw-post-purchase-offer-page .cuw-offer-customize-tab").toggleClass('cuw-active-tab');
                    $("#cuw-post-purchase-offer-page .cuw-custom-section").removeClass('d-flex').addClass('d-none');
                    let active_section = $(this).data('target');
                    $("#cuw-post-purchase-offer-page " + active_section).removeClass('d-none').addClass('d-flex');
                }
            });

            $(document).on('click', '#cuw-post-purchase-offer-page .cuw-custom-option', function () {
                $(this).toggleClass('border-bottom');
                $("#cuw-post-purchase-offer-page " + $(this).data('target')).slideToggle();
                $(this).find('.accordion-icon i').toggleClass('cuw-icon-accordion-open cuw-icon-accordion-close');
            });

            $(document).on('click', '#cuw-post-purchase-offer-page .back-to-options-tab', function () {
                $("#cuw-post-purchase-offer-page #cuw-option-tab").removeClass('d-none');
                $("#cuw-post-purchase-offer-page " + $(this).data('target')).addClass('d-none');
            });

            $(document).on('input', '#cuw-campaign .cuw-color-inputs .cuw-color-picker', function () {
                $(this).closest('.cuw-color-inputs').find('.cuw-color-input').val($(this).val()).trigger('input');
            });
            $(document).on('input blur', '#cuw-campaign .cuw-color-inputs .cuw-color-input', function () {
                if ($(this).val() && !/^#[0-9a-fA-F]{6}$/i.test($(this).val())) {
                    $(this).addClass('border-danger');
                } else {
                    $(this).removeClass('border-danger');
                }
                $(this).closest('.cuw-color-inputs').find('.cuw-color-picker').val($(this).val());
            });

            $(document).on('change', '#cuw-campaign #cuw-post-purchase-offer-page #offer-product select', function () {
                let product_name = $("option:selected", this).text();
                $('#cuw-campaign #cuw-post-purchase-offer-page .cuw-product-name').val(product_name);
                cuw_post_purchase.preview();
            });

            $(document).on('input', '#cuw-campaign #cuw-post-purchase-offer-page #post-purchase-offer-qty', function () {
                cuw_post_purchase.preview();
            })

            $(document).on('click', '#cuw-campaign #cuw-post-purchase-offer-page #post-purchase-offer-save', function () {
                cuw_post_purchase.save_offer();
            });

            $(document).on('click', '#cuw-campaign .offer-flow .add-offer', function () {
                cuw_post_purchase.show_offer_page('add', $(this).data('parent_offer_uuid'), $(this).data('offer_type'));
            });

            $(document).on('click', '#cuw-campaign .offer-flow .offer-edit', function () {
                cuw_post_purchase.show_offer_page('edit', $(this).data('uuid'));
            });

            $(document).on('click', '#cuw-campaign .offer-flow .offer-remove', function () {
                $("#cuw-campaign #modal-remove .offer-title").html(cuw_i18n.offer + " " + cuw_post_purchase.get_offer_index($(this).data('uuid')));
                $("#modal-remove .modal-footer .offer-delete").data('id', $(this).data('uuid')).data('offer_type', $(this).data('offer_type'));
                $("#cuw-campaign .modal-body .cuw-child-offer-warning").show();
            });

            $("#modal-remove .modal-footer .offer-delete").on('click', function () {
                cuw_post_purchase.remove_offer($(this).data('id'), $(this).data('offer_type'));
            });

            $(document).on('click', '#cuw-campaign #cuw-post-purchase-offer-page #back-to-campaign-page', function () {
                cuw_post_purchase.hide_offer_page();
            });

            $(document).on('change', '#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-type select', function () {
                if ($(this).val() === 'free' || $(this).val() === 'no_discount') {
                    $("#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-value").hide();
                    $("#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-value input").val(0);
                } else {
                    $("#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-value").show();
                    $("#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-value input").val('');
                }
                cuw_post_purchase.preview();
            });

            $(document).on('change', '#cuw-campaign #cuw-post-purchase-offer-page .offer-discount-value input', function () {
                cuw_post_purchase.preview();
            });

            $(document).on('change', '#cuw-content-discount #offer-discount-type, #cuw-content-discount #offer-discount-value', function () {
                $("#cuw-offer-tab-section .cuw-text-section :input").trigger('input');
            });

            $(document).on('input', '#cuw-offer-tab-section .cuw-design-section :input', function () {
                cuw_post_purchase.update_style($(this));
            });

            $(document).on('input', '#cuw-offer-tab-section .cuw-text-section :input', function () {
                if ($(this).data('section') === 'timer') {
                    let timer_text = $(document).find("#cuw-campaign #cuw-post-purchase-offer-page #timer-details-section #timer-message").val();
                    let minutes = $(document).find("#cuw-campaign #cuw-post-purchase-offer-page #timer-details-section #timer-minutes").val();
                    minutes = (minutes.toString().length < 2) ? '0' + minutes : minutes;
                    let seconds = $(document).find("#cuw-campaign #cuw-post-purchase-offer-page #timer-details-section #timer-seconds").val();
                    seconds = (seconds.toString().length < 2) ? '0' + seconds : seconds;
                    let message = timer_text.replace('{minutes}', minutes).replace('{seconds}', seconds);
                    $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview').find(".cuw-ppu-timer-message").html(message);
                } else if ($(this).data('section') === 'offer_title') {
                    let discount = cuw_helper.get_discount_text($(document).find("#cuw-campaign #cuw-post-purchase-offer-page #cuw-content-discount #offer-discount-type").val()
                        , $(document).find("#cuw-campaign #cuw-post-purchase-offer-page #cuw-content-discount #offer-discount-value").val());
                    let value = $("#cuw-campaign #cuw-post-purchase-offer-page #offer-title").val().replace('{discount}', discount);
                    $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview').find($(this).data('target')).html(value);
                } else {
                    $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview').find($(this).data('target')).html($(this).val());
                }
            });

            $('#cuw-campaign .cuw-template-border .cuw-border-width').change(function () {
                let border_inputs = $(this).closest('.cuw-template-border').find('.cuw-border-style, .cuw-border-color');
                $(this).val() === '0' ? border_inputs.hide() : border_inputs.show();
            });

            $(document).on('change', "#offer-details-notice, #timer-notice-type", function () {
                cuw_post_purchase.change_notice_type($(this).val(), $(this).data('section'));
            });

            $(document).on('change', '#offer-notice-enabled', function () {
                let value = $(this).is(':checked') ? 1 : 0;
                $(this).val(value);
                $("#cuw-campaign #cuw-post-purchase-offer-page #order-details-section").toggle();
                let notice_type = $(document).find("#cuw-post-purchase-offer-page #order-details-section #offer-details-notice");
                cuw_post_purchase.change_notice_type($(this).is(':checked') ? notice_type.val() : 'hide', notice_type.data('section'));
                $("#cuw-campaign #cuw-post-purchase-offer-page #order-details-section :input").prop('disabled', !$(this).is(':checked'));
            });

            $(document).on('change', '#offer-timer-enabled', function () {
                let value = $(this).is(':checked') ? 1 : 0;
                $(this).val(value);
                $("#cuw-campaign #cuw-post-purchase-offer-page #timer-details-section").toggle();
                let notice_type = $(document).find("#cuw-post-purchase-offer-page #timer-details-section #timer-notice-type");
                cuw_post_purchase.change_notice_type($(this).is(':checked') ? notice_type.val() : 'hide', notice_type.data('section'));
                $("#cuw-campaign #cuw-post-purchase-offer-page #timer-details-section :input").prop('disabled', !$(this).is(':checked'));
            });

            $(document).on('change', '#order-totals-enabled', function () {
                let value = $(this).is(':checked') ? 1 : 0;
                $(this).val(value);
                $('#cuw-campaign #cuw-post-purchase-offer-page .template-preview').find($(this).data('section')).css('display', ($(this).is(':checked') ? 'block' : 'none'));
            });

            $(document).on('change', '#cuw-content-image #offer-image-type #offer-image-id', function () {
                if ($(this).val() !== '0') {
                    $("#cuw-content-image #select-image").show();
                } else {
                    $("#cuw-content-image #select-image").hide();
                    cuw_post_purchase.preview();
                }
            });

            $(document).on('click', '#cuw-campaign #cuw-content-image #select-image', function () {
                cuw_post_purchase.select_image();
            });
        }
    }

    $(document).ready(function () {
        if ($("#cuw-campaign").length !== 0 && $("#cuw-campaign").data('type') === 'post_purchase_upsells') {
            cuw_post_purchase.init();
        }
    });
});