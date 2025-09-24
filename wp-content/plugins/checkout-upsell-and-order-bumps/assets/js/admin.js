jQuery(function ($) {

    let cuw_i18n = cuw_admin.i18n || {};
    let cuw_data = cuw_admin.data || {};
    let cuw_views = cuw_admin.views || {};
    let cuw_is_rtl = cuw_admin.is_rtl;
    let cuw_has_pro = cuw_admin.has_pro;
    let cuw_page_url = cuw_admin.page_url;
    let cuw_ajax_url = cuw_admin.ajax_url;
    let cuw_ajax_nonce = cuw_admin.ajax_nonce;
    const cuw_alert_counter = {counts: 1};

    /* Page */
    window.cuw_page = {

        // init
        init: function () {
            this.event_listeners();
        },

        // notify message
        notify: function (message, type = "success") {
            if (type === "error") type = "danger";

            let icon = 'dashicons dashicons-yes-alt';
            let colors = 'bg-success text-white';

            if (type === "warning") {
                icon = 'dashicons dashicons-warning';
                colors = 'bg-warning text-white';
            } else if (type === "danger") {
                icon = 'dashicons dashicons-warning';
                colors = 'bg-danger text-white';
            } else if (type === "info") {
                icon = 'dashicons dashicons-info';
                colors = 'bg-info text-white';
            }

            let id = 'notify-' + cuw_alert_counter.counts;
            let html = '<div id="' + id + '" class="alert alert-' + type + ' ' + colors + ' px-2" style="display: none;">';
            html += '<div><span class="' + icon + '"></span> ' + message + '</div><span class="float-auto dashicons dashicons-no"></span></div>';
            $("#cuw-page #notify").append(html);

            cuw_alert_counter.counts++;
            let message_div = $("#" + id);
            message_div.fadeIn(500);
            setTimeout(function () {
                message_div.fadeOut(500, function () {
                    $(this).remove();
                });
            }, 5000);
        },

        // show or hide overlay
        overlay: function (action, html = '', color = '') {
            let overlay_div = $("#cuw-page #overlay");
            if (color === 'transparent') {
                overlay_div.css('opacity', 0);
            } else if (color === 'dark') {
                overlay_div.css('background', "rgba(1,1,1,0.6)");
            } else {
                overlay_div.css('background', "rgba(255,255,255,0.6)");
            }
            if (action === 'show') {
                overlay_div.html(html).show();
            } else {
                overlay_div.html(html).hide();
            }
        },

        // spinning overlay for ajax
        spinner: function (action) {
            if (action === 'show') {
                this.overlay('show', '<div class="spinner-border text-primary"></div>');
            } else {
                this.overlay('hide');
            }
        },

        // scroll top
        scroll_top: function () {
            $("html, body").animate({scrollTop: 0}, "slow");
        },

        // redirect page
        redirect: function (to = '', delay = 0) {
            setTimeout(function () {
                cuw_page.overlay('show', '', 'transparent')
            }, 0);
            setTimeout(function () {
                window.location = (to === 'back') ? document.referrer : cuw_page_url + to
            }, delay);
        },

        // reload page
        reload: function (delay = 0) {
            setTimeout(function () {
                cuw_page.overlay('show', '', 'transparent')
            }, 0);
            setTimeout(function () {
                window.location.reload()
            }, delay);
        },

        // copy text
        copy: function (text) {
            if (navigator && navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    cuw_page.notify(cuw_i18n.copied, 'success');
                });
            }
        },

        // get query param from the current url
        query_param: function (key, default_value = null) {
            try {
                let param = (new URLSearchParams(window.location.search)).get(key);
                return param !== null ? param : default_value;
            } catch (e) {
            }
            return default_value;
        },

        // event listeners
        event_listeners: function () {
            $("#cuw-page .modal").on("show.bs.modal", function () {
                cuw_page.scroll_top();
                cuw_page.overlay('show', '', 'dark');
            }).on("hide.bs.modal", function () {
                cuw_page.overlay('hide', '', 'dark');
            });

            $("#cuw-page #notify").on("click", ".dashicons-no", function () {
                $(this).closest(".alert").fadeOut(300, function () {
                    $(this).remove();
                });
            });

            $("#cuw-page").on("click", ".cuw-copy", function () {
                cuw_page.copy($(this).html());
            });

            $("#cuw-campaign").on("click", ".cuw-copy-text", function () {
                cuw_page.copy($(this).closest('.cuw-campaign-copy').find('.cuw-copy-text-content').html());
            });

            $("#help-panel-toggle, #help-panel-close").click(function () {
                $("#cuw-page #help-panel").toggleClass("panel-open");
            });
        }
    }

    const cuw_slider = {
        show: function (slider, temp = false) {
            if (typeof slider === 'string') {
                slider = $(slider).first();
            }
            slider.show();
            slider.find('.cuw-slider-close').click(function () {
                temp ? slider.remove() : slider.hide()
            });
            $(window).click(function (event) {
                if ($(event.target).hasClass('cuw-slider')) {
                    temp ? slider.remove() : slider.hide()
                }
            });
            $(document).keydown(function (event) {
                if (event.keyCode === 27) {
                    temp ? slider.remove() : slider.hide()
                }
            });
        },

        // hide modal
        hide: function (slider) {
            if (typeof slider === 'string') {
                slider = $(slider).first();
            }
            slider.hide();
        }
    }


    /* Campaigns */
    const cuw_campaigns = {

        // init
        init: function () {
            this.event_listeners();
        },

        // search campaign
        search: function () {
            $("#cuw-campaigns #search-campaign").submit();
        },

        // enable campaign
        enable: function (id, enabled) {
            this.ajax('enable_campaign', {id: id, enabled: enabled ? 1 : 0});
        },

        // duplicate campaign
        duplicate: function (id) {
            this.ajax('duplicate_campaign', {id: id});
        },

        // delete campaign
        delete: function (ids, bulk = false) {
            if (bulk) {
                cuw_campaigns.bulk_actions('delete', ids);
            } else {
                this.ajax('delete_campaign', {id: ids[0]});
            }
            $("#cuw-campaigns #modal-delete").modal('hide');
        },

        // toggle list and create section
        toggle_section: function () {
            $("#cuw-campaigns #campaigns-create, #cuw-campaigns #campaigns-list").toggle();
        },

        // bulk action for campaigns
        bulk_actions: function (action, ids = null) {
            if (ids === null) {
                ids = this.get_choosen_campaign_ids();
            }
            this.ajax('bulk_actions', {bulk_action: action, ids: ids});
            $("#cuw-campaigns #bulk-toolbar #checks-count").html(0);
        },

        // get choosen campaign ids
        get_choosen_campaign_ids: function () {
            let ids = [];
            $("#cuw-campaigns .check-single:checked").each(function () {
                ids.push($(this).val());
            });
            return ids;
        },

        // show basic toolbar
        show_basic_toolbar: function () {
            $("#cuw-campaigns #bulk-toolbar").attr('style', 'display:none !important');
            $("#cuw-campaigns #basic-toolbar").attr('style', 'display:flex !important');
        },

        // show bulk toolbar
        show_bulk_toolbar: function () {
            $("#cuw-campaigns #basic-toolbar").attr('style', 'display:none !important');
            $("#cuw-campaigns #bulk-toolbar").attr('style', 'display:flex !important');
        },

        // ajax request and response handler
        ajax: function (method, data) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: $.extend({
                    action: 'cuw_ajax',
                    method: method,
                    nonce: cuw_ajax_nonce || ""
                }, data),
                beforeSend: function () {
                    cuw_page.spinner('show');
                },
                complete: function () {
                    cuw_page.spinner('hide');
                },
                success: function (response) {
                    let status = response.data.status ?? "error";
                    let message = response.data.message ?? cuw_i18n.error;
                    if (message) {
                        cuw_page.notify(message, status);
                    }
                    if (response.data.redirect) {
                        cuw_page.redirect('&' + response.data.redirect);
                    }
                    if (response.data.change) {
                        if (response.data.change.id && response.data.change.status) {
                            let status = response.data.change.status;
                            let html = '<span class="p-2 status-' + response.data.change.status.code + '">' + status.text + '</span>';
                            $("#cuw-campaigns table .campaign-" + response.data.change.id).find(".campaign-status").html(html);
                        }
                    }
                    if (response.data.remove) {
                        if (response.data.remove.ids) {
                            $.each(response.data.remove.ids, function (key, value) {
                                $("#cuw-campaigns table .campaign-" + value).fadeOut(300, function () {
                                    $(this).remove();
                                });
                            });
                        } else if (response.data.remove.id) {
                            $("#cuw-campaigns table .campaign-" + response.data.remove.id).fadeOut(300, function () {
                                $(this).remove();
                            });
                        }
                    }
                    if (response.data.refresh) {
                        cuw_page.reload(2000);
                    }
                }
            });
        },

        set_campaigns_list_limit: function (value) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'set_campaigns_list_limit',
                    value: value,
                    nonce: cuw_ajax_nonce || ""
                },
                success: function (response) {
                    if (response.data && response.data.refresh) {
                        cuw_page.reload();
                    } else {
                        cuw_page.notify(cuw_i18n.error, 'error');
                    }
                }
            })
        },

        // event listeners
        event_listeners: function () {
            $("#cuw-campaigns .create-campaign").click(function () {
                if (!cuw_has_pro && $("#cuw-campaigns table tr.campaign").length >= 5) {
                    cuw_page.notify(cuw_i18n.campaign_max_limit_reached, 'info');
                    return;
                }
                cuw_campaigns.toggle_section();
                $("#cuw-campaigns .campaign-filters #all").trigger("click");
            });
            $("#cuw-campaigns #back-to-campaigns").click(function () {
                cuw_campaigns.toggle_section()
            });
            $("#cuw-campaigns .campaign-filter-type").click(function () {
                // Remove 'bg-primary-light' class from all li elements
                $("#cuw-campaigns .campaign-filters li").removeClass("active-tab-container");

                // Add 'bg-primary-light' class to the clicked li element
                $(this).removeClass("bg-white");
                $(this).addClass("active-tab-container");
                let category_type = $(this).attr("id");
                if (category_type !== 'all') {
                    $("#cuw-campaigns #available-campaigns .create-campaign-card").hide();
                    $('#cuw-campaigns #available-campaigns .' + category_type).show();
                } else {
                    $("#cuw-campaigns #available-campaigns .card").show();
                }
                $("#cuw-campaigns .campaign-filters li h6").removeClass("text-primary").addClass("text-secondary");
                $('#cuw-campaigns .campaign-filters li #' + category_type).removeClass("text-secondary").addClass("text-primary");
                $("#cuw-campaigns #campaign-sort-name").text($(this).find('.campaign-filter-name').text());
            });
            $("#cuw-campaigns #search-campaign").keyup(function (event) {
                if (event.keyCode === 13) {
                    cuw_campaigns.search();
                }
            });
            $("#cuw-campaigns #check-all").click(function () {
                if ($(this).is(":checked")) {
                    cuw_campaigns.show_bulk_toolbar();
                    $("#cuw-campaigns .check-single").prop('checked', true).trigger('change');
                } else {
                    cuw_campaigns.show_basic_toolbar();
                    $("#cuw-campaigns .check-single").prop('checked', false).trigger('change');
                }
            });
            $("#cuw-campaigns .check-single").change(function () {
                let checks_count = $("#cuw-campaigns .check-single:checked").length;
                if (checks_count > 0) {
                    cuw_campaigns.show_bulk_toolbar();
                } else {
                    cuw_campaigns.show_basic_toolbar();
                }
                $("#cuw-campaigns #bulk-toolbar #checks-count").html(checks_count);
            });
            $("#cuw-campaigns").on("click", ".campaign-enable", function () {
                cuw_campaigns.enable($(this).data('id'), $(this).is(":checked"));
            }).on("click", ".campaign-delete", function () {
                cuw_campaigns.delete($(this).data('ids'), $(this).data('bulk'));
            }).on("click", ".campaign-duplicate", function () {
                cuw_campaigns.duplicate($(this).data('id'));
            });
            $("#cuw-campaigns #modal-delete").on("show.bs.modal", function (event) {
                let ids = [], title = '', target = $(event.relatedTarget);
                let bulk = target.data('bulk') ? true : false;
                if (bulk) {
                    ids = cuw_campaigns.get_choosen_campaign_ids();
                } else {
                    ids.push(target.data('id'));
                }
                ids.forEach(function (id) {
                    title += "<br>" + " #" + id + ": " + $("#cuw-campaigns .campaign-" + id).data('title');
                });
                $("#cuw-campaigns #modal-delete .campaign-title").html(title);
                $("#cuw-campaigns #modal-delete .campaign-delete").data('ids', ids);
                $("#cuw-campaigns #modal-delete .campaign-delete").data('bulk', bulk);
            });

            $("#cuw-campaigns #campaigns-list #campaign-list-block").find("#campaigns-per-page").change(function () {
                cuw_campaigns.set_campaigns_list_limit($(this).val());
            });

            $("#modal-page-builder .modal-body .builder-section").on('click', function () {
                $("#modal-page-builder .modal-body").find(":input[type='radio']").prop('checked', false);
                $("#modal-page-builder .modal-body .builder-section").addClass('border-gray-extra-light').removeClass('active-page-builder');
                $("#modal-page-builder .modal-footer .campaign-create-url").css({'pointer-events': 'auto', 'opacity': '1'});
                $(this).find("input[type='radio']").prop('checked', true);
                $(this).addClass('active-page-builder').removeClass('border-gray-extra-light');
                $("#modal-page-builder .modal-footer .campaign-create-url").attr('href', $(this).data('href'));
            });

            $("#modal-page-builder .modal-body input").on('click', function () {
                $("#modal-page-builder .modal-footer .campaign-create-url").css({'pointer-events': 'auto', 'opacity': '1'});
                $("#modal-page-builder .modal-body .builder-section").addClass('border-gray-extra-light').removeClass('active-page-builder');
                $("#modal-page-builder .modal-body .builder-section input[type='radio']:checked").closest(' .builder-section').addClass('active-page-builder').removeClass('border-gray-extra-light');
                $("#modal-page-builder .modal-footer .campaign-create-url").attr('href', $("#modal-page-builder .modal-body .builder-section input[type='radio']:checked").closest(' .builder-section').data('href'));
            });
        }
    }


    /* Campaign - offer section */
    const cuw_offer = {

        // init
        init: function () {
            this.event_listeners();
            cuw_customize.init();
        },

        // preview offer template
        preview: function (action = 'show') {
            let preview = $("#cuw-campaign #cuw-preview .offer-preview, #cuw-campaign #view-offer-slider #cuw-preview .offer-preview");
            let offer_data = this.get_data(0, true);
            let template_data = cuw_customize.get_offer_template_data();
            if ((action === 'show' || action === 'reload') && offer_data.product_id) {
                if (cuw_campaign.type === 'checkout_upsells' || cuw_campaign.type === 'cart_upsells') {
                    this.load_template(preview, offer_data, template_data.template, template_data.image_id);
                } else if (cuw_campaign.type === 'post_purchase') {
                    this.load_iframe(preview, offer_data, template_data);
                }
            } else if (action === 'update') {
                if (cuw_campaign.type === 'checkout_upsells' || cuw_campaign.type === 'cart_upsells') {
                    preview.html(this.prepare_template(preview.html(), template_data));
                } else if (cuw_campaign.type === 'post_purchase') {
                    this.load_iframe(preview, offer_data, template_data);
                }
            } else {
                preview.html('');
            }
        },

        // to load preview url
        load_iframe: function (target, offer_data, template_data) {
            let url = $("#page-id").find('option:selected').data('url');
            url += (url.includes('?') === false ? '?' : '&') + $.param({
                cuw_offer_preview: 1,
                cuw_page_id: template_data.page_id,
                offer_title: template_data.title,
                offer_cta_text: template_data.cta_text,
                offer_description: template_data.description,
                product_id: offer_data.product_id,
                product_qty: offer_data.product_qty,
                discount_type: offer_data.discount_type,
                discount_value: offer_data.discount_value,
                image_id: template_data.image_id,
            });
            target.html('<iframe id="cuw-preview-iframe" height="400" width="100%" " src="' + url + '"></iframe>');
            //$("#cuw-campaign .offer-preview-link").attr('href', url).show();
        },

        // to load template through ajax
        load_template: function (target, offer_data, template_name = null, image_id = 0, plain = false) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'get_offer_template',
                    product: {id: offer_data.product_id, qty: offer_data.product_qty},
                    discount: {type: offer_data.discount_type, value: offer_data.discount_value},
                    data: {template: template_name, image_id: image_id},
                    campaign_type: cuw_campaign.type,
                    nonce: cuw_ajax_nonce || ""
                },
                beforeSend: function () {
                    target.css('opacity', 0.5);
                },
                success: function (response) {
                    target.css('opacity', 1);
                    if (response.data && response.data.html) {
                        let template_data = cuw_customize.get_offer_template_data();
                        target.html(cuw_offer.prepare_template(response.data.html, template_data, '', plain));
                    } else {
                        target.html('');
                    }
                }
            });
        },

        // prepare template
        prepare_template: function (html, data = null, image = '', plain = false) {
            let $html = $('<div/>', {html: html});
            if (data !== null && !plain) {
                let discount_text = $('.cuw-offer', $html).data('discount') || '';
                if (data.title !== '') {
                    $(".cuw-offer .cuw-offer-title", $html).html(data.title.replace('{discount}', discount_text)).show();
                    $(".cuw-offer .cuw-badge", $html).show();
                } else {
                    $(".cuw-offer .cuw-offer-title", $html).html('').hide();
                    $(".cuw-offer .cuw-badge", $html).hide();
                }
                if (data.description !== '') {
                    $(".cuw-offer .cuw-offer-description", $html).html('<span style="white-space: pre-wrap;">' + data.description + '</span>').show();
                } else {
                    $(".cuw-offer .cuw-offer-description", $html).html('').hide();
                }

                $(".cuw-offer .cuw-offer-cta-text", $html).html(data.cta_text);

                if (data.styles) {
                    $.each(data.styles, function (block, styles) {
                        $.each(styles, function (name, value) {
                            let target = cuw_customize.get_offer_styling_target(block);
                            if (target) {
                                $(target, $html).css(name, value);
                            }
                        });
                    });
                }
            }

            if (plain) {
                $(".alert", $html).remove();
            }

            if (image !== '') {
                $(".cuw-offer .cuw-product-image", $html).html(image);
            }

            if (typeof $html === "object") {
                $html = $($html).html();
            }
            return $html;
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

        // get offer data
        get_data: function (key, from_slider = false) {
            let selector = from_slider ? '#offer-slider .offer-data [name="offer' : '#cuw-offers [name="offers[' + key + ']';
            return {
                id: $('#cuw-campaign ' + selector + '[id]"]').val() || 0,
                product_id: $('#cuw-campaign ' + selector + '[product_id]"]').val() || 0,
                product_name: $('#cuw-campaign ' + selector + '[product_name]"]').val() || '',
                product_qty: $('#cuw-campaign ' + selector + '[product_qty]"]').val() || '',
                discount_type: $('#cuw-campaign ' + selector + '[discount_type]"]').val() || 'percentage',
                discount_value: $('#cuw-campaign ' + selector + '[discount_value]"]').val() || '',
                limit: $('#cuw-campaign ' + selector + '[limit]"]').val() || '',
                limit_per_user: $('#cuw-campaign ' + selector + '[limit_per_user]"]').val() || '',
                used: $('#cuw-campaign ' + selector + '[used]"]').val() || 0,
                views: $('#cuw-campaign ' + selector + '[views]"]').val() || 0,
                data: $('#cuw-campaign ' + selector + '[data]"]').val() || JSON.stringify(cuw_customize.get_default_template_data())
            }
        },

        // set offer data
        set_data: function (key, data = null, to_slider = false) {
            let selector = to_slider ? '#offer-slider .offer-data [name="offer' : '#cuw-offers [name="offers[' + key + ']';
            let template_data_json = data ? data.data : JSON.stringify(cuw_customize.get_default_template_data());
            $('#cuw-campaign ' + selector + '[id]"]').val(data ? data.id : 0);
            $('#cuw-campaign ' + selector + '[product_id]"]').val(data ? data.product_id : 0);
            $('#cuw-campaign ' + selector + '[product_name]"]').val(data ? data.product_name : '');
            $('#cuw-campaign ' + selector + '[product_qty]"]').val(data ? data.product_qty : '');
            $('#cuw-campaign ' + selector + '[discount_type]"]').val(data ? data.discount_type : 'percentage').trigger('change');
            $('#cuw-campaign ' + selector + '[discount_value]"]').val(data ? data.discount_value : '');
            $('#cuw-campaign ' + selector + '[limit]"]').val(data ? data.limit : '');
            $('#cuw-campaign ' + selector + '[limit_per_user]"]').val(data ? data.limit_per_user : '');
            $('#cuw-campaign ' + selector + '[data]"]').val(template_data_json);

            if (to_slider) {
                let option = '<option value="' + (data ? data.product_id : '') + '" selected>' + (data ? data.product_name : '') + '</option>';
                $('#cuw-campaign #offer-slider .offer-product .select2-list').html(option);
                $('#cuw-campaign #offer-slider .offer-used').html(data ? data.used : 0);
                $('#cuw-campaign #offer-slider .offer-views').html(data ? data.views : 0);
            } else {
                let offer = $("#cuw-campaign #cuw-offers #offer-" + key);
                if ($("#cuw-campaign #cuw-preview-iframe").length) {
                    setTimeout(function () {
                        let template_data = JSON.parse(template_data_json);
                        let image_id = template_data && template_data.image_id ? template_data.image_id : 0;
                        let product_id = (image_id == 0 || image_id == '0') && data && data.product_id ? data.product_id : 0;
                        offer.find(".offer-item-image").html(cuw_offer.get_image(image_id, product_id));
                    }, 0);
                } else {
                    offer.find(".offer-item-image").html($("#cuw-campaign #cuw-preview .offer-preview .cuw-product-image").html() || '');
                }
                offer.find(".offer-item-name").html(data ? data.product_name : '');
                offer.find(".offer-item-qty").html(data ? data.product_qty : '');
                offer.find(".offer-item-discount").html(data ? cuw_helper.get_discount_text(data.discount_type, data.discount_value) : '');
            }
        },

        // validation
        validate: function (data) {
            let passed = true;
            let offer_data = $("#cuw-campaign #offer-slider .offer-data");
            cuw_campaign.hide_field_attention(offer_data);
            if (!data.product_id) {
                cuw_campaign.show_field_attention(offer_data.find('.offer-product'));
                passed = false;
            }
            if (!data.discount_type) {
                cuw_campaign.show_field_attention(offer_data.find('.offer-discount-type'));
                passed = false;
            }
            if (data.discount_value === '') {
                cuw_campaign.show_field_attention(offer_data.find('.offer-discount-value'));
                passed = false;
            }
            return passed;
        },

        // message
        message: function (action, message = '', error = false, type = 'offer') {
            let offer_message = (type === 'offer_flow')
                ? $("#cuw-campaign .offer-flow .cuw-offer-message")
                : $("#cuw-campaign #cuw-offers .cuw-offer-message");
            if (action !== 'hide') {
                offer_message.html(message).show();
                offer_message.removeClass(error ? 'text-secondary' : 'text-danger');
                offer_message.addClass(error ? 'text-danger' : 'text-secondary');
            } else {
                offer_message.html('').hide();
            }
        },

        // get offers
        get_offers: function () {
            return $("#cuw-campaign #cuw-offers .cuw-offer .offer-data");
        },

        // get offer by key
        get_offer: function (key) {
            return $('#cuw-campaign #cuw-offers .cuw-offer[data-key="' + key + '"]');
        },

        // get offers count
        get_offers_count: function () {
            return this.get_offers().length;
        },

        // get index
        get_index: function (key = 0) {
            let index = key === 0 ? this.get_offers_count() + 1 : this.get_offer(key).data('index') || key;
            return index <= 2 && this.get_display_method() === 'ab_testing' ? (index === 1 ? 'A' : 'B') : index;
        },

        // update
        update: function () {
            let message = $("#cuw-campaign #cuw-offers .cuw-offer-message");
            this.get_offers_count() === 0 ? message.show() : message.hide();

            this.get_offers().each(function (key) {
                $(this).closest('.cuw-offer').data('index', key + 1);
            });
        },

        // get display method
        get_display_method: function () {
            return $("#cuw-campaign #offer-data #offer-display-method").val();
        },

        // get new key
        get_new_key: function () {
            let last_key = $("#cuw-campaign #cuw-offers .cuw-offer:last-child").data('key');
            return last_key ? last_key + 1 : 1;
        },

        // add offer
        add: function () {
            if (this.max_offers_limit_is_reached()) {
                cuw_page.notify(cuw_i18n.offer_max_limit_reached, 'info');
            } else {
                this.show_slider(this.get_new_key(), 'add');
            }
        },

        // check if the max offers limit is reached or not
        max_offers_limit_is_reached: function () {
            return this.get_offers_count() >= this.get_offers_max_limit();
        },

        // get offers max limit
        get_offers_max_limit: function () {
            let display_method = $("#cuw-campaign #offer-display-method").val() || '';
            return (display_method === 'ab_testing') ? 2 : cuw_data.offer.max_limit;
        },

        // update offer max limit
        update_offers_max_limit: function () {
            $("#cuw-campaign .offers-max-limit").html(cuw_i18n.offer_max_limit.replace('%s', cuw_offer.get_offers_max_limit()));
        },

        // edit offer
        edit: function (key) {
            let data = this.get_data(key, false);
            this.show_slider(key, 'edit', data);
            this.preview('show');
        },

        //view offer
        view: function (key) {
            let data = this.get_data(key, false);
            this.show_view_offer_slider(key, data);
            this.preview('show');
        },

        // show offer slider
        show_slider: function (key, action, data = null) {
            let index = this.get_index(action === 'add' ? 0 : key);
            let offer_header = $("#cuw-campaign #offer-header");
            offer_header.data('key', key).data('action', action);
            offer_header.find('.offer-index').html(index);
            cuw_offer.set_data(key, data, true);
            cuw_customize.load_offer_template_data(data);
            cuw_campaign.hide_field_attention($("#cuw-campaign #offer-slider .offer-data"));
            $("#cuw-campaign #offer-slider .nav-tabs .nav-item:first-child .nav-link").click();
            $('#cuw-campaign #offer-slider .offer-image-radio').trigger('change');
            cuw_slider.show('#offer-slider');
        },

        // show view offer slider
        show_view_offer_slider: function (key, data) {
            cuw_offer.set_data(key, data, true);
            cuw_customize.load_offer_template_data(data);
            cuw_slider.show('#view-offer-slider');
        },

        // save offer
        save: function () {
            let offer_header = $("#cuw-campaign #offer-header");
            let key = offer_header.data('key');
            let action = offer_header.data('action');

            let template_data = JSON.stringify(cuw_customize.get_offer_template_data());
            $('#cuw-campaign #offer-slider .offer-data [name="offer[data]"]').val(template_data);

            let campaign_id = cuw_campaign.get_id();
            let data = cuw_offer.get_data(0, true);
            if (cuw_offer.validate(data)) {
                $.ajax({
                    type: 'post',
                    url: cuw_ajax_url,
                    data: {
                        action: 'cuw_ajax',
                        method: 'save_offer',
                        campaign_id: campaign_id,
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
                            cuw_campaign.hide_field_attention($("#cuw-campaign #offer-slider #offer-details [name]").parent());
                            if (status === "error" && typeof message === "object") {
                                if (message.fields) {
                                    $.each(message.fields, function (field_name) {
                                        let div = $('#cuw-campaign #offer-slider #offer-details [name^="' + field_name + '"]').parent();
                                        cuw_campaign.show_field_attention(div, message.fields[field_name]);
                                    });
                                }
                                message = cuw_i18n.offer_not_saved;
                            } else if (status === 'success') {
                                if (action === 'add') {
                                    let content = cuw_views.offer.content.replace(/{key}/g, key);
                                    $("#cuw-campaign #cuw-offers").append(content);
                                }
                                if (response.data.id) {
                                    data.id = response.data.id; // to update offer id
                                }
                                cuw_offer.set_data(key, data, false);

                                if (action === 'add' || !response.data.id) {
                                    cuw_slider.hide('#offer-slider');
                                }
                                cuw_offer.update();
                            }
                            if (message) {
                                cuw_page.notify(message, status);
                            }
                        }
                    }
                });
            } else {
                cuw_page.notify(cuw_i18n.offer_not_saved, 'error');
            }
        },

        // duplicate offer
        duplicate: function (key) {
            if (this.max_offers_limit_is_reached()) {
                cuw_page.notify(cuw_i18n.offer_max_limit_reached, 'info');
            } else {
                let data = this.get_data(key, false);
                let offer = this.get_offer(key);

                let new_key = this.get_new_key();
                let content = cuw_views.offer.content.replace(/{key}/g, new_key);
                $("#cuw-campaign #cuw-offers").append(content);

                data.id = '0'; // to create a new offer
                cuw_offer.set_data(new_key, data, false);

                let new_offer = this.get_offer(new_key);
                new_offer.find('.offer-item-image').html(offer.find('.offer-item-image').html());
                new_offer.find('.offer-item-qty').html(offer.find('.offer-item-qty').html());
            }
        },

        // remove offer
        remove: function (key) {
            $("#cuw-campaign #modal-remove").modal('hide');
            let offer_id = $('#cuw-campaign #cuw-offers .cuw-offer [name="offers[' + key + '][id]"]').val();
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
                            cuw_offer.get_offer(key).fadeOut(300, function () {
                                $(this).remove();
                            });
                            setTimeout(function () {
                                cuw_offer.update();
                            }, 500);
                            cuw_page.notify(response.data.message, response.data.status);
                        }
                    }
                });
            } else {
                this.get_offer(key).fadeOut(300, function () {
                    $(this).remove();
                });
                setTimeout(function () {
                    cuw_offer.update();
                }, 500);
            }
        },

        // event listeners
        event_listeners: function () {
            $("#cuw-campaign #offer-add").click(function () {
                cuw_offer.add()
            });

            $("#cuw-campaign #cuw-offers").on("click", ".offer-edit", function () {
                let key = $(this).closest(".cuw-offer").data('key');
                cuw_offer.edit(key);
            }).on("click", ".offer-view", function () {
                let key = $(this).closest(".cuw-offer").data('key');
                cuw_offer.view(key);
            }).on("click", ".offer-duplicate", function () {
                let key = $(this).closest(".cuw-offer").data('key');
                cuw_offer.duplicate(key);
            });

            $("#cuw-campaign #offer-slider .offer-product select").change(function () {
                let product_name = $("option:selected", this).text();
                $('#cuw-campaign #offer-slider [name="offer[product_name]"]').val(product_name);
            });

            $('#cuw-campaign #offer-slider #offer-image-type .offer-image-radio').on('change', function () {
                if ($(this).is(':checked')) {
                    $(this).parent().addClass('border border-primary');
                    $(this).closest('#offer-image-type').find('.offer-image-radio').not(':checked').parent().removeClass('border border-primary');
                }
            });

            $("#cuw-campaign #offer-save").click(function () {
                cuw_offer.save()
            });

            $("#cuw-campaign #offer-close").click(function () {
                cuw_slider.hide('#offer-slider');
            });

            $("#cuw-campaign #offer-slider #offer-details input, #cuw-campaign #offer-slider #offer-details select").change(function () {
                cuw_campaign.hide_field_attention($(this).parent());
            });

            $("#cuw-campaign #offer-slider .offer-discount-type select").change(function () {
                if ($(this).val() === 'free' || $(this).val() === 'no_discount') {
                    $("#cuw-campaign #offer-slider .offer-discount-value").hide();
                    $("#cuw-campaign #offer-slider .offer-discount-value input").val(0);
                } else {
                    $("#cuw-campaign #offer-slider .offer-discount-value").show();
                    $("#cuw-campaign #offer-slider .offer-discount-value input").val('');
                }
            });

            $("#cuw-campaign #offer-slider .reload-preview").change(function () {
                cuw_offer.preview('reload');
            });

            $("#cuw-campaign #modal-remove").on("show.bs.modal", function (event) {
                let key = $(event.relatedTarget).data('key');
                $("#cuw-campaign #modal-remove .offer-title").html(cuw_i18n.offer + " " + cuw_offer.get_index(key));
                $("#cuw-campaign #modal-remove .offer-delete").data('id', key);
            });

            $("#cuw-campaign #modal-remove .offer-delete").click(function () {
                cuw_offer.remove($(this).data('id'))
            });

            $("#cuw-campaign #offer-data #offer-display-method").change(function () {
                let ab_testing = $("#cuw-campaign #offer-data #ab-testing-section");
                if ($(this).val() === 'ab_testing') {
                    ab_testing.css("display", 'flex').find('input').attr("disabled", false);
                } else {
                    ab_testing.css("display", 'none').find('input').attr("disabled", true);
                }

                cuw_offer.update_offers_max_limit();
            });

            $("#cuw-campaign #offer-data #offer-a, #cuw-campaign #offer-data #offer-b").on("change keyup", function () {
                let this_percentage = $(this).val();
                let next_percentage = (100 - this_percentage) >= 0 ? (100 - this_percentage) : 0;
                if ($(this).attr('id') === 'offer-a') {
                    $("#cuw-campaign #offer-data #offer-b").val(next_percentage);
                } else {
                    $("#cuw-campaign #offer-data #offer-a").val(next_percentage);
                }
            });

            $("#cuw-campaign #template-contents .offer-image-radio").click(function (event) {
                if (event.target.id === 'custom-image') {
                    $("#cuw-campaign #template-contents #select-image").show();
                } else {
                    $("#cuw-campaign #template-contents #select-image").hide();
                }
                $("#cuw-campaign #template-contents #offer-image-id").val($(this).val()).trigger('change');
            });
        }
    }

    /* Campaign - action section */
    const cuw_action = {

        // init
        init: function () {
            this.event_listeners();
        },

        // event listeners
        event_listeners: function () {
            $("#cuw-campaign #cuw-action").on('change', "#action-discount-type, #action-discount-value", function () {
                $('#cuw-template .cuw-template-texts').find(':input').trigger('input');
            });

            $("#cuw-campaign #cuw-action").on('input', "#action-coupon-prefix, #action-coupon-length", function () {
                let prefix = $("#cuw-campaign #cuw-action #action-coupon-prefix").val() || '';
                let length = $("#cuw-campaign #cuw-action #action-coupon-length").val() || 0;
                $("#cuw-campaign #cuw-template .cuw-template-preview").find('.cuw-template-coupon').html(prefix + "abcdef0123456789".substring(0, length));
            });
            $("#cuw-campaign #cuw-action #action-coupon-prefix").trigger('input');

            $("#cuw-campaign #cuw-action #action-toggle-config a").click(function () {
                let hidden = $("#cuw-campaign #cuw-action #action-config").is(":hidden");
                $(this).find("i").toggleClass("cuw-icon-down cuw-icon-up");
                $(this).find("span").html($(this).parent().data(hidden ? 'hide' : 'show'));
                $("#cuw-campaign #cuw-action #action-config").slideToggle();
                $(".select2-list").trigger('change');
            });
        }
    }

    const cuw_customize = {

        // init
        init: function () {
            this.event_listeners();
        },

        // load offer template data
        load_offer_template_data: function (data = null) {
            let template_data = data ? JSON.parse(data.data) : this.get_default_template_data();
            this.set_offer_template_data(template_data);
            if (template_data.image_id === 0 || template_data.image_id === '0') {
                $("#cuw-campaign #template-contents #product-image").prop('checked', true);
                $("#cuw-campaign #template-contents #select-image").hide();
            } else {
                $("#cuw-campaign #template-contents #custom-image").prop('checked', true);
                $("#cuw-campaign #template-contents #select-image").show();
            }
        },

        // get offer template data
        get_offer_template_data: function () {
            let data = {
                title: $("#cuw-campaign #template-contents #offer-title").val(),
                description: $("#cuw-campaign #template-contents #offer-description").val(),
                cta_text: $("#cuw-campaign #template-contents #offer-cta-text").val(),
                image_id: $("#cuw-campaign #template-contents #offer-image-id").val(),
            }
            if (cuw_campaign.type === 'post_purchase') {
                data.page_id = $("#cuw-campaign #template-styling #page-id").val();
            } else {
                let template_name = $("#cuw-campaign #template-styling #template-name").val() || '';
                let custom_styling = $("#cuw-campaign #template-styling #custom-styling").is(':checked');
                data.template = template_name;
                data.custom_styling = custom_styling;
                data.styles = cuw_customize.get_offer_styling_data(custom_styling ? '' : template_name)
            }
            return data;
        },

        // set offer template data
        set_offer_template_data: function (data) {
            $("#cuw-campaign #template-contents #offer-title").val(data.title);
            $("#cuw-campaign #template-contents #offer-description").val(data.description);
            $("#cuw-campaign #template-contents #offer-cta-text").val(data.cta_text);
            $("#cuw-campaign #template-contents #offer-image-id").val(data.image_id);
            if (cuw_campaign.type === 'post_purchase') {
                $("#cuw-campaign #template-styling #page-id").val(data.page_id).trigger('change');
            } else {
                $("#cuw-campaign #template-styling #template-name").val(data.template).trigger('change');
                $("#cuw-campaign #template-styling #custom-styling").prop('checked', data.custom_styling).trigger('change');
                if (data.custom_styling && data.styles) {
                    cuw_customize.set_offer_styling_data(data.styles);
                } else {
                    cuw_customize.reset_offer_styling_data();
                }
            }
        },

        // get offer styling data
        get_offer_styling_data: function (template_name = '') {
            if (template_name !== '') {
                return this.get_templates()[template_name] ? this.get_templates()[template_name]['styles'] : {};
            } else {
                let data = {
                    template: $("#cuw-campaign #template-styling #template-styles").serializeArray(),
                    title: $("#cuw-campaign #template-styling #title-styles").serializeArray(),
                    description: $("#cuw-campaign #template-styling #description-styles").serializeArray(),
                    cta: $("#cuw-campaign #template-styling #cta-styles").serializeArray(),
                };
                let formatted = {};
                $.each(data, function (block, styles) {
                    formatted[block] = {}
                    $.each(styles, function (key, input) {
                        formatted[block][input.name] = input.value;
                    });
                });
                return formatted;
            }
        },

        // set offer styling data
        set_offer_styling_data: function (data) {
            if (data) {
                $.each(data, function (block, styles) {
                    $.each(styles, function (name, value) {
                        $('#cuw-campaign #template-styling #' + block + '-styles [name="' + name + '"]').val(value).trigger('change');
                    });
                });
            }
            $("#cuw-campaign #custom-styles .cuw-color-input").trigger('input');
        },

        // set offer styling data
        reset_offer_styling_data: function () {
            let template_name = this.get_offer_template_data()['template'];
            let data = this.get_templates()[template_name] ? this.get_templates()[template_name]['styles'] : {};
            this.set_offer_styling_data(data);
        },

        // get offer styling target
        get_offer_styling_target: function (block) {
            let target = false;
            if (block === 'template') {
                target = '.cuw-container';
            } else if (block === 'title') {
                target = '.cuw-container .cuw-offer-title';
            } else if (block === 'description') {
                target = '.cuw-container .cuw-offer-description';
            } else if (block === 'cta') {
                target = '.cuw-container .cuw-offer-cta-section';
            }
            return target;
        },

        // get default template name
        get_default_template: function () {
            return cuw_data.default_template;
        },

        // get available templates
        get_templates: function () {
            return cuw_data.templates;
        },

        // get default template data
        get_default_template_data: function () {
            if (cuw_campaign.type === 'post_purchase') {
                return this.get_default_page_data();
            }
            return this.get_templates()[this.get_default_template()];
        },

        // get default page data
        get_default_page_data: function () {
            return cuw_data.offer.default_page_data;
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
                    $("#cuw-campaign #template-contents #custom-image").val(image_ids.join(","));
                    $("#cuw-campaign #template-contents #offer-image-id").val(image_ids.join(",")).trigger('change');
                }
            });

            image_frame.on('open', function () {
                let image_ids = $("#cuw-campaign #template-contents #offer-image-id").val().split(",");
                let selection = image_frame.state().get('selection');
                image_ids.forEach(function (id) {
                    let attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
            });

            image_frame.open();
        },

        // set offer image
        set_offer_image: function (image_id) {
            if (image_id === 0 || image_id === '0') {
                let data = cuw_offer.get_data(0, true);
                let image = cuw_offer.get_image(0, data.product_id);
                $("#cuw-campaign #cuw-preview .offer-preview .cuw-product-image").html(image);
            } else if (image_id !== '') {
                let image = cuw_offer.get_image(image_id);
                $("#cuw-campaign #cuw-preview .offer-preview .cuw-product-image").html(image);
            }
        },

        // load offer templates
        load_offer_templates: function () {
            let templates = $("#cuw-campaign #change-template #templates");
            let image = $("#cuw-campaign #cuw-preview .offer-preview .cuw-product-image").html() || '';
            let offer_data = cuw_offer.get_data(0, true);
            templates.html('');
            $.each(this.get_templates(), function (template_name) {
                if (offer_data.product_id) {
                    let class_attr = template_name.includes('-wide') ? "col-md-12" : "col-md-6";
                    templates.append('<div class="' + class_attr + ' template-preview-card">' +
                        '<div class="offer-preview template-preview" data-template="' + template_name + '">' +
                        '<div class="d-flex justify-content-center"><div class="spinner-border text-primary"></div></div>' +
                        '</div>' +
                        '<div class="template-name">' + template_name + '</div>' +
                        '</div>');
                    let target = $('#cuw-campaign #change-template #templates [data-template="' + template_name + '"]');
                    cuw_offer.load_template(target, offer_data, template_name, image, true);
                }
            });
        },

        // set offer template and close modal
        set_offer_template: function (template) {
            $("#cuw-campaign #template-styling #template-name").val(template.data("template")).trigger('change');
            $("#cuw-campaign #template-contents #customize-template .offer-preview").html(template.html());

            cuw_offer.preview('reload');
        },

        // event listeners
        event_listeners: function () {
            $("#cuw-campaign #template-contents #select-image").click(function () {
                cuw_customize.select_image();
            });

            $("#cuw-campaign #template-contents #offer-image-id").change(function () {
                if (cuw_campaign.type === 'post_purchase') {
                    cuw_offer.preview('update');
                } else {
                    cuw_customize.set_offer_image($(this).val());
                }
            });

            $("#cuw-campaign #template-contents").on("input", "input, select, textarea", function () {
                cuw_offer.preview('update');
            });

            $("#cuw-campaign #template-styling #template-name").change(function () {
                $("#cuw-campaign #template-styling #offer-template-name").html(this.value);
            });

            $("#cuw-campaign #template-styling #page-id").change(function () {
                $("#cuw-campaign #template-styling #offer-page-id").html(' /' + $(this).find('option:selected').data('slug'));
                cuw_offer.preview('update');
            });

            $("#cuw-campaign #offer-slider #custom-styling").change(function () {
                if (this.checked) {
                    $("#cuw-campaign #offer-slider #custom-styles, #cuw-campaign #offer-slider #reset-styles").show();
                } else {
                    $("#cuw-campaign #offer-slider #custom-styles, #cuw-campaign #offer-slider #reset-styles").hide();
                    cuw_customize.reset_offer_styling_data();
                }
                cuw_offer.preview('update');
            });

            $("#cuw-campaign #cuw-products input[type='radio']").change(function () {
                $(this).closest('.custom-control').toggleClass('active-product');
                if ($(this).is(':checked')) {
                    $(this).closest('.custom-control').toggleClass('active-product');
                }
                $("#cuw-campaign #cuw-products #specific-products").toggle($(this).val() == 'specific');
            });

            $("#cuw-campaign #cuw-products #item-quantity select").change(function () {
                $("#cuw-campaign #cuw-products #item-quantity #quantity-value").toggle($(this).val() == 'fixed');
            });

            $("#cuw-campaign #cuw-products #item-quantity select").change(function () {
                let quantity_value = $("#cuw-campaign #cuw-products #item-quantity #quantity-value");
                quantity_value.toggle($(this).val() == 'fixed');
                quantity_value.find('input').prop('disabled', $(this).val() != 'fixed');
            });

            $("#cuw-campaign #template-styling #reset-styles").click(function () {
                cuw_customize.reset_offer_styling_data();
                cuw_offer.preview('update');
            });

            $("#cuw-campaign #custom-styles").on("input", "input, select", function () {
                let block = $(this).closest("form").attr('id').replace('-styles', '');
                let target = cuw_customize.get_offer_styling_target(block);
                if (target) {
                    $("#cuw-campaign #cuw-preview .offer-preview " + target).css(this.name, this.value);
                }
            });

            $("#cuw-campaign #change-offer-template").on("click", function () {
                cuw_customize.load_offer_templates()
                cuw_slider.show("#choose-offer-template-slider");
            });

            $("#cuw-campaign #change-template").on("click", ".offer-preview", function () {
                cuw_customize.set_offer_template($(this));
                cuw_slider.hide("#choose-offer-template-slider");
            });

            $("#cuw-campaign #cuw-offer-template-close").click(function () {
                cuw_slider.hide("#choose-offer-template-slider");
            });


            $("#cuw-campaign #cuw_template .cuw-custom-styling").change(function () {
                $(this).closest("#cuw-template").find(".cuw-template-styles, .cuw-reset-styles").toggle();
                if (!$(this).is(':checked')) {
                    $("#cuw-campaign #cuw-template .cuw-reset-styles").trigger('click');
                }
            });

            $("#cuw-campaign #redirect-url select").change(function () {
                let url_value = $("#cuw-campaign #redirect-url #url-value");
                url_value.toggle($(this).val() == 'custom');
                url_value.find('input').prop('disabled', $(this).val() != 'custom');
            });

            $("#cuw-campaign #cuw-template .cuw-reset-styles").click(function () {
                let templates = cuw_data.templates;
                let active_template = $("#cuw-campaign #template-name").val();
                if (templates[active_template]['styles']) {
                    let custom_styles = $("#cuw-campaign .cuw-template-styles");
                    $.each(templates[active_template]['styles'], function (section, styles) {
                        $.each(styles, function (name, value) {
                            custom_styles.find('[name="data[template][styles][' + section + '][' + name + ']"]').val(value).trigger('input').trigger('change');
                        });
                    });
                }
            });

            $('#cuw-campaign .cuw-color-inputs .cuw-color-picker').on('input', function () {
                $(this).closest('.cuw-color-inputs').find('.cuw-color-input').val($(this).val()).trigger('input');
            });
            $('#cuw-campaign .cuw-color-inputs .cuw-color-input').on('input blur', function () {
                if ($(this).val() && !/^#[0-9a-fA-F]{6}$/i.test($(this).val())) {
                    $(this).addClass('border-danger');
                } else {
                    $(this).removeClass('border-danger');
                }
                $(this).closest('.cuw-color-inputs').find('.cuw-color-picker').val($(this).val());
            }).trigger('input');

            $('#cuw-campaign .cuw-range-group .cuw-range-input').on('input', function () {
                $(this).closest('.cuw-range-group').find('.cuw-range-value').text($(this).val());
            });

            $('#cuw-campaign .cuw-style-group .cuw-border-width').change(function () {
                let border_inputs = $(this).closest('.cuw-style-group').find('.cuw-border-style, .cuw-border-color');
                $(this).val() === '0' ? border_inputs.hide() : border_inputs.show();
            }).trigger('change');

            $('#cuw-campaign #cuw-template .cuw-template-texts').on('input', ':input', function () {
                let discount = cuw_helper.get_discount_text($("#cuw-campaign #cuw-action #action-discount-type").val(), $("#cuw-campaign #cuw-action #action-discount-value").val());
                let value = $(this).val().replace('{discount}', discount);
                if ($(this).data('target') === '.cuw-template-cta-text' && $('#cuw-campaign #cuw-template .cuw-template-preview .cuw-products').length > 0) {
                    $('#cuw-campaign #cuw-template .cuw-template-preview .cuw-products').find('.cuw-template-cta-text, .cuw-template-cta-button').data('text', value);
                    $(document).trigger("cuw_products_load_section", [$('#cuw-campaign #cuw-template .cuw-template-preview .cuw-products')]);
                }
                $('#cuw-campaign #cuw-template .cuw-template-preview').find($(this).data('target')).html(value);
            });

            $('#cuw-campaign #cuw-template .cuw-template-checkbox').on('change', 'select', function () {
                let target = $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview').find($(this).data('target'));
                target.css('opacity', $(this).val() !== 'uncheckable' ? '1' : '0.8');
                target.not('[data-hidden="1"]').css('display', $(this).val() !== 'hidden' ? 'block' : 'none');
                target.not('[data-checked="1"]').prop('checked', $(this).val() !== 'unchecked');
                target.trigger('change');
            });

            $('#cuw-campaign #cuw-template .cuw-template-save-badge .cuw-save-badge').on('change', 'select', function () {
                let target = $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview');
                target.find('.cuw-badge, .cuw-total-savings').hide();
                let selected_type = $(this).val();
                if (selected_type == 'only_products') {
                    target.find('.cuw-badge').show();
                } else if (selected_type == 'only_total') {
                    target.find('.cuw-total-savings').show();
                } else if (selected_type == 'both_products_and_total') {
                    target.find('.cuw-badge, .cuw-total-savings').show();
                }
                $(this).closest('.cuw-template-save-badge').find('.cuw-save-badge-text').css('display', ['do_not_display', 'only_total'].includes(selected_type) ? 'none' : 'block');
            });

            $('#cuw-campaign #cuw-template .cuw-template-save-badge .cuw-save-badge-text').on('input', ':input', function () {
                let target = $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview');
                let text = $(this).val();

                target.find('.cuw-products .cuw-product').each(function (index, product) {
                    let regular_price = $(product).data('regular_price');
                    let price = $(product).data('price');

                    if (price !== '' && regular_price !== '' && price !== regular_price) {
                        text = text.replace('{price}', cuw_helper.format_price(regular_price - price))
                            .replace('{percentage}', cuw_helper.format_percentage((regular_price - price) / regular_price * 100));
                    }
                    $(product).find('.cuw-badge').html(text);
                });
            });

            $('#cuw-campaign #cuw-template .cuw-noc-message select').on('change', function () {
                let target = $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview');
                target.find($(this).data('target')).show();
                if ($(this).val() === 'hide') {
                    target.find($(this).data('target')).hide();
                }
            })

            $('#cuw-campaign #cuw-template .cuw-template-styles').on('input', ':input', function () {
                if (!$(this).data('name')) {
                    return;
                }
                if ($(this).data('name') === 'size') {
                    $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview').find($(this).data('target')).css('height', $(this).val() + 'px');
                    $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview').find($(this).data('target')).css('width', $(this).val() + 'px');
                    $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview').find('.cuw-product-card').css('width', $(this).val() + 'px');
                    $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview').find('.cuw-product-separator').css('height', $(this).val() + 'px');
                } else if (['height', 'min-height', 'max-height', 'width', 'min-width', 'max-width'].includes($(this).data('name'))) {
                    $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview').find($(this).data('target')).css($(this).data('name'), $(this).val() + 'px');
                } else {
                    $('#cuw-campaign #cuw-template .cuw-template-preview, #cuw-campaign #cuw-view-template .cuw-template-preview').find($(this).data('target')).css($(this).data('name'), $(this).val());
                }
            });

            $("#cuw-campaign #template-slider").on("click", ".template-preview", function () {
                let template_name = $(this).data("template");
                if (template_name.indexOf('default') === -1) {
                    $("#cuw-campaign #edit-template, #cuw-campaign #view-template").show();
                    $("#cuw-campaign #template-title-wrapper").hide();
                    $("#cuw-campaign #cuw_product_recommendations_page #display-location .shortcode-option").show();
                    $("#cuw-campaign #cuw-shortcode-wrapper").show();
                    $("#cuw-campaign #cuw_product_recommendations_page .pr_columns").addClass('d-none');
                    $("#cuw-campaign #edit-template-slider #template-title").show().prop('disabled', false);
                } else {
                    $("#cuw-campaign #edit-template-slider #template-title").hide().prop('disabled', true);
                    $("#cuw-campaign #edit-template, #cuw-campaign #view-template").hide();
                    $("#cuw-campaign #template-title-wrapper").show();
                    $("#cuw-campaign #cuw_product_recommendations_page #display-location .shortcode-option").hide();
                    $("#cuw-campaign #cuw-shortcode-wrapper").hide();
                    $("#cuw-campaign #cuw_product_recommendations_page .pr_columns").removeClass('d-none');
                }

                $("#cuw-campaign #template-name").val($(this).data("template")).trigger('change');
                $("#cuw-campaign #cuw-template .cuw-template-preview ,#cuw-campaign #cuw-view-template .cuw-template-preview").html($(this).html());
                $("#cuw-campaign #cuw-template .cuw-reset-styles").trigger('click');
                $('#cuw-campaign #cuw-template .cuw-template-texts').find(':input').trigger('input');
                $('#cuw-campaign #cuw-template .cuw-template-checkbox').find('select').trigger('change');
                $("#cuw-campaign #cuw-template .cuw-noc-message").find('select').trigger('change');
                $("#cuw-campaign #cuw-action #action-coupon-prefix").trigger('input');
                $("#cuw-campaign #template-slider").hide();
            });

            // desktop and mobile preview for edit template and edit offer
            $("#cuw-campaign #cuw-device-preview").on("change", "input[type='radio'][name='device']", function () {
                $("input[type='radio'][name='device']").closest('label').removeClass('btn-primary').addClass('btn-light');
                $(this).closest('label').removeClass('btn-light').addClass('btn-primary');
                if ($(this).val() === 'mobile') {
                    $(this).closest('#preview').removeClass('col-md-7').addClass('col-md-5');
                    $(this).closest('.cuw-slider-content').find('#edit').removeClass('col-md-4').addClass('col-md-6');
                    $(this).closest('#preview').find('#cuw-edit-template-preview').addClass('cuw-template-mobile-preview');
                    $(this).closest('#preview').find('#cuw-edit-template-preview').find('.cuw-mobile-block').css('display', 'block');
                    $(this).closest('.cuw-slider-content').css('width', '60%');
                } else {
                    $(this).closest('#preview').addClass('col-md-7').removeClass('col-md-5');
                    $(this).closest('.cuw-slider-content').find('#edit').removeClass('col-md-6').addClass('col-md-4');
                    $(this).closest('#preview').find('#cuw-edit-template-preview').removeClass('cuw-template-mobile-preview');
                    $(this).closest('#preview').find('#cuw-edit-template-preview').find('.cuw-mobile-block').css('display', 'none');
                    $(this).closest('.cuw-slider-content').css('width', '80%');
                }
            });

            //desktop and mobile preview for view template and view offer
            $("#cuw-campaign #cuw-view-device-preview").on("change", "input[type='radio'][name='preview-device']", function () {
                let template_preview = $(this).closest('#cuw-view-template').find('#preview').find('#cuw-view-template-preview');

                $("input[type='radio'][name='preview-device']").closest('label').removeClass('btn-primary').addClass('btn-light');
                $(this).closest('label').removeClass('btn-light').addClass('btn-primary');
                if ($(this).val() === 'mobile') {
                    $("input[type='radio'][name='preview-device']").closest('label').find('span').css('display', 'none');
                    $(this).closest('.cuw-slider-content').css('width', '25%');
                    template_preview.addClass('cuw-template-mobile-preview');
                    template_preview.find('.cuw-mobile-block').css('display', 'block');
                } else {
                    $("input[type='radio'][name='preview-device']").closest('label').find('span').css('display', 'inline');
                    $(this).closest('.cuw-slider-content').css('width', '60%');
                    template_preview.removeClass('cuw-template-mobile-preview');
                    template_preview.find('.cuw-mobile-block').css('display', 'none');
                }
            });
        }
    }

    const cuw_campaign = {
        type: '',
        action: '',

        // init
        init: function () {
            this.select2();
            this.event_listeners();
            this.init_template_title();

            if ($('#cuw-campaign #cuw-engine-filter-container').length > 0) {
                cuw_engine.event_listeners();
            }

            let campaign = $("#cuw-campaign");
            this.type = campaign.data('type');
            this.action = campaign.data('action');
            this.format_conditions($("#cuw-campaign #cuw-conditions"));
            this.format_filters($("#cuw-campaign #cuw-filters"));

            cuw_offer.init();
            cuw_action.init();
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

        // reload select2
        reload: function () {
            this.select2();
        },

        // get campaign id
        get_id: function () {
            return $("#cuw-campaign #campaign-id").val();
        },

        // filters methods
        add_filter: function () {
            let select_type = $("#cuw-campaign #filter-type select");
            let type = select_type.val();
            if (type) {
                let id = $.now();
                let name = select_type.find("option:selected").text();
                let data = cuw_views.filters[type].replace(/{key}/g, id);
                let html = cuw_views.filters.wrapper.replace(/{key}/g, id);
                html = html.replace('{name}', name);
                html = html.replace(/{type}/g, type);
                html = html.replace('{data}', data);
                $("#cuw-campaign #filter-section").html(html);
                $("#cuw-campaign #filter-section .filter-inputs").show();
                $("#cuw-campaign #filter-section").find(".filter-row, .filter-name, .filter-count").hide();
                if (type == 'all_products') {
                    $("#cuw-campaign #filter-section .filter-edit").hide();
                    $("#cuw-campaign #slider-filter-add").prop('disabled', false);
                } else {
                    $("#cuw-campaign #slider-filter-add").prop('disabled', true);
                }
                select_type.removeClass('border-danger');
                cuw_campaign.select2('#cuw-campaign #filter-section');
            } else {
                select_type.addClass('border-danger');
            }
        },
        remove_filter: function (filter) {
            filter.fadeOut(300, function () {
                $(this).remove();
            });
            setTimeout(function () {
                cuw_campaign.update_filters_section();
            }, 500);
        },
        update_filters_section: function () {
            if ($("#cuw-campaign #cuw-filters .cuw-filter").length === 0) {
                $("#cuw-campaign #filters-match").css("display", 'none').find('input').attr("disabled", true);
                $("#cuw-campaign #no-filters").show();
            } else {
                let releation_text = $('#cuw-campaign #filters-match input[name="filters[relation]"]:checked').val();
                $("#cuw-campaign #filters-match").css("display", 'flex').find('input').attr("disabled", false);
                $("#cuw-campaign #no-filters").hide();
                $("#cuw-campaign #cuw-filters").children().each(function (index, element) {
                    index++;
                    if (index == 1) {
                        $(element).find(".filter-relation").html('');
                    } else {
                        $(element).find(".filter-relation").html(releation_text).show();
                    }
                    $(element).find(".filter-count").html(cuw_i18n.filter_text + " " + index);
                });
            }
        },

        format_filter: function (section) {
            let text = "";
            let texts = [];
            if (section.length) {
                if (section.find(".filter-name").length) {
                    texts.push(section.find(".filter-name").text());
                }
                if (section.find(".filter-method select").length) {
                    texts.push(section.find(".filter-method select").find("option:selected").text());
                }

                if (section.find(".filter-values select").length) {
                    let values = section.find(".filter-values .select2-container .select2-selection__rendered .select2-selection__choice");
                    let option_names = [];
                    values.each(function (key, value) {
                        option_names.push($(value).attr("title"));
                    });
                    texts.push(option_names.join(', '));
                }
            }
            text = '<span class="d-flex align-items-center">' +
                texts.join('<i class="cuw-icon-chevron-' + (cuw_is_rtl ? 'left' : 'right') + ' text-dark"></i>') + '</span>';
            section.find(".filter-text").html(text);
        },

        format_filters: function (section) {
            section.find(".cuw-filter").each(function (key, value) {
                cuw_campaign.format_filter($(value));
            });
            cuw_campaign.select2('#cuw-campaign #cuw-filters', 'destroy');
        },

        // condition methods
        add_condition: function () {
            let select_type = $("#cuw-campaign #condition-type select");
            let type = select_type.val();
            if (type) {
                let id = $.now();
                let name = select_type.find("option:selected").text();
                let data = cuw_views.conditions[type].replace(/{key}/g, id);
                let html = cuw_views.conditions.wrapper.replace(/{key}/g, id);
                html = html.replace('{name}', name);
                html = html.replace('{type}', type);
                html = html.replace('{data}', data);
                $("#cuw-campaign #condition-section").html(html);
                $("#cuw-campaign #condition-section .condition-inputs").show();
                $("#cuw-campaign #condition-section").find(".condition-row, .condition-name, .condition-count").hide();
                $("#cuw-campaign #slider-condition-add").prop('disabled', true);
                select_type.removeClass('border-danger');
                cuw_campaign.select2('#cuw-campaign #condition-section');
            } else {
                select_type.addClass('border-danger');
            }
            this.update_conditions_section();
            $("#cuw-campaign #condition-section .trigger-change").trigger("change");
        },
        remove_condition: function (condition) {
            condition.fadeOut(300, function () {
                $(this).remove();
            });
            setTimeout(function () {
                cuw_campaign.update_conditions_section();
            }, 500);
        },

        update_conditions_section: function () {
            if ($("#cuw-campaign #cuw-conditions .cuw-condition").length === 0) {
                $("#cuw-campaign #conditions-match").css("display", 'none').find('input').attr("disabled", true);
                $("#cuw-campaign #no-conditions").show();
            } else {
                let releation_text = $('#cuw-campaign #conditions-match input[name="conditions[relation]"]:checked').val();
                $("#cuw-campaign #conditions-match").css("display", 'flex').find('input').attr("disabled", false);
                $("#cuw-campaign #no-conditions").hide();
                $("#cuw-campaign #cuw-conditions").children().each(function (index, element) {
                    index++;
                    if (index == 1) {
                        $(element).find(".condition-relation").html('');
                    } else {
                        $(element).find(".condition-relation").html(releation_text).show();
                    }
                    $(element).find(".condition-count").html(cuw_i18n.condition_text + " " + index);
                });
            }
        },

        format_condition: function (section) {
            let text = "";
            let texts = [];
            if (section.length) {
                if (section.find(".condition-name").length) {
                    texts.push(section.find(".condition-name").text());
                }
                if (section.find(".condition-method select").length) {
                    texts.push(section.find(".condition-method select").find("option:selected").text());
                }
                if (section.find(".condition-operator select").length) {
                    let operators = section.find(".condition-operator select").find("option:selected");
                    let operator_names = '';
                    operators.each(function (key, value) {
                        operator_names += $(value).text() + ($(operators).last().text() === $(value).text() ? '' : '<i class="cuw-icon-chevron-' + (cuw_is_rtl ? 'left' : 'right') + ' text-dark"></i>');
                    });
                    texts.push(operator_names);
                }
                if (section.find(".condition-value :input").length) {
                    let condition_value = [];
                    section.find(".condition-value :input").each(function (index, element) {
                        if ($(element).hasClass('select2-local')) {
                            if ($(element).attr('multiple')) {
                                let option_names = section.find(".condition-values .select2-container .select2-selection__rendered .select2-selection__choice");
                                option_names.each(function (key, value) {
                                    condition_value.push($(value).attr("title"));
                                });
                            } else {
                                condition_value.push(section.find(".condition-value .select2-selection__rendered").html());
                            }
                        } else {
                            condition_value.push($(element).val());
                        }
                    });
                    texts.push(condition_value.join(' - '));
                }
                if (section.find(".condition-values select").length) {
                    section.find(".condition-values").each(function (value_index, condition_value) {
                        let values = $(condition_value).find(".select2-container .select2-selection__rendered .select2-selection__choice");
                        let option_names = [];
                        values.each(function (key, value) {
                            option_names.push($(value).attr("title"));
                        });
                        texts.push(option_names.join(', '));
                    });
                }
            }
            text = '<span class="d-flex align-items-center">' +
                texts.join('<i class="cuw-icon-chevron-' + (cuw_is_rtl ? 'left' : 'right') + ' text-dark"></i>') + '</span>';
            section.find(".condition-text").html(text);
        },

        format_conditions: function (section) {
            section.find(".cuw-condition").each(function (key, value) {
                cuw_campaign.format_condition($(value));
            });
            cuw_campaign.select2('#cuw-campaign #cuw-conditions', 'destroy');
        },

        // add validation attention message
        show_field_attention: function (div, message = cuw_i18n.this_field_is_required) {
            div.append('<small class="invalid text-danger d-block mt-1">' + message + '</small>');
            div.find('input, select, .select2-selection').addClass('border-danger');
            div.closest('.cuw-filter').find('.filter-text-wrapper').addClass('border border-danger');
            div.closest('.cuw-condition').find('.condition-text-wrapper').addClass('border border-danger');
        },

        // remove validation attention message
        hide_field_attention: function (div) {
            div.find('.invalid').remove();
            div.find('input, select, .select2-selection').removeClass('border-danger');
            div.closest('.cuw-filter').find('.filter-text-wrapper').removeClass('border border-danger');
            div.closest('.cuw-condition').find('.condition-text-wrapper').removeClass('border border-danger');
        },

        // validate before save
        validate: function () {
            let passed = true;

            if ($('#cuw-campaign #cuw-triggers').length > 0) {
                if ($('#cuw-campaign #cuw-triggers').find(".cuw-trigger input[type='checkbox']:checked").length === 0) {
                    passed = false;
                    $(".cuw-trigger-message").show();
                }

                if ($("#cuw-triggers .cuw-trigger #added-to-cart").is(':checked')) {
                    let trigger_filter = $("#cuw-triggers #trigger-advanced-settings").find("#triggers-filter :input[type='radio']:checked").val();
                    if (trigger_filter !== 'all_products') {
                        let values = $("#cuw-triggers #trigger-advanced-settings").find('#choose-specific-' + trigger_filter + ' .select2-list').val();
                        if (!values || values.length === 0) {
                            passed = false;
                            $("#cuw-triggers #trigger-advanced-settings").slideDown();
                            $("#cuw-triggers #trigger-advanced-settings").find('#choose-specific-' + trigger_filter + ' .cuw-trigger-filter-message').removeClass('d-none');
                        }
                    }
                }
            }

            if ($('#cuw-filters').length > 0) {
                if ($("#cuw-filters .cuw-filter").length === 0) {
                    passed = false;
                }
            }
            if ($('#cuw-offers').length > 0) {
                let offers_count = cuw_offer.get_offers_count();
                let display_method = cuw_offer.get_display_method();
                if (offers_count === 0) {
                    cuw_offer.message('show', cuw_i18n.at_least_one_offer_required, true);
                    passed = false;
                } else if (display_method === "ab_testing" && offers_count !== 2) {
                    if (offers_count > 2) {
                        cuw_offer.message('show', cuw_i18n.offer_ab_requires_exactly_two_offers, true);
                    } else {
                        cuw_offer.message('show', cuw_i18n.offer_ab_requires_two_offers, true);
                    }
                    passed = false;
                }
            }

            if ($('.offer-flow').length > 0) {
                if ($("#cuw-campaign .offer-flow .offer-data").length === 0) {
                    cuw_offer.message('show', cuw_i18n.at_least_one_offer_required, true, 'offer_flow');
                    passed = false;
                }
            }

            if ($('#cuw-engine-filter-container').length > 0) {
                if ($("#cuw-engine-filter-container #added-engine-filters-list div").length === 0) {
                    passed = false;
                    $("#cuw-engine-filter-container #filter-type .cuw-engine-filter-section").each(function (index, element) {
                        if ($(element).find("#filter-section").html() !== '') {
                            passed = true;
                        }
                    });
                    if (!passed) {
                        cuw_engine.message('show', cuw_i18n.filter_required, $('#no-engine-filters'), true)
                        $('#no-engine-filters').removeClass('d-none');
                        $("#cuw-engine-filter-container .selected-filters-label").addClass('d-none');
                    }
                }
            }

            if ($('#cuw-campaign #cuw-discounts').length > 0) {
                if ($("#cuw-campaign #cuw-discounts .cuw-discount").length === 0) {
                    passed = false;
                    $('#cuw-no-discounts-added').removeClass('d-none');
                }
            }

            if (!passed) {
                cuw_page.notify(cuw_i18n.campaign_not_saved, 'error');
            }
            return passed;
        },

        // save campaign
        save: function (close) {
            if (!this.validate()) {
                return;
            }

            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'save_campaign',
                    form_data: $("#cuw-campaign #campaign-form").serialize(),
                    page_no: cuw_page.query_param('page_no', 1),
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
                        let status = response.data.status ?? "error";
                        let message = response.data.message ?? cuw_i18n.error;
                        $("#cuw-campaign #cuw-offers .offer-item").removeClass('border-danger');
                        cuw_campaign.hide_field_attention($("#cuw-campaign #campaign-form [name]").parent());
                        if (status === "error" && typeof message === "object") {
                            if (message.fields) {
                                $.each(message.fields, function (field_name) {
                                    let div = $('#cuw-campaign #campaign-form [name^="' + field_name + '"]').parent();
                                    cuw_campaign.show_field_attention(div, message.fields[field_name]);
                                });
                            }
                            if (message.divs) {
                                $.each(message.divs, function (key, selector) {
                                    $('#cuw-campaign ' + selector).addClass('border-danger');
                                });
                            }
                            cuw_page.notify(cuw_i18n.campaign_not_saved, 'error');
                        } else {
                            cuw_page.notify(message, status);
                        }
                        if (status === "success" && close) {
                            cuw_campaign.close(2000);
                        } else if (response.data.redirect) {
                            cuw_page.redirect('&' + response.data.redirect, 2000);
                        }
                    } else {
                        cuw_page.notify(cuw_i18n.error, 'error');
                    }
                }
            });
        },

        // close campaign
        close: function (delay = 0) {
            let page_no = cuw_page.query_param('page_no', 1);
            if (this.action === 'edit' && page_no > 1) {
                cuw_page.redirect('&tab=campaigns&page_no=' + page_no, delay);
            } else {
                cuw_page.redirect('&tab=campaigns', delay);
            }
        },

        init_template_title : function() {
            if ($("#cuw_template #template-name").length > 0) {
                let template_name = $("#cuw_template #template-name").val();
                if (template_name.indexOf('default') === -1) {
                    $("#cuw-campaign #edit-template-slider #template-title").show().prop('disabled', false);
                } else {
                    $("#cuw-campaign #edit-template-slider #template-title").hide().prop('disabled', true);
                }
            }
        },

        // event listeners
        event_listeners: function () {
            $("#cuw-campaign #filter-slider #filter-type").on('change', 'select', function () {
                cuw_campaign.add_filter();
            });

            $("#cuw-campaign #cuw-filters").on("click", ".filter-remove", function () {
                cuw_campaign.remove_filter($(this).closest('.cuw-filter'));
            });

            $("#cuw-campaign #condition-type").on('change', 'select', function () {
                cuw_campaign.add_condition();
            });

            $('#cuw-campaign #conditions-match input[name="conditions[relation]"]').on('change', function () {
                $("#cuw-campaign #cuw-conditions .condition-relation").text($(this).val());
                if ($(this).val() === 'and') {
                    $("#cuw-campaign #cuw-conditions .condition-relation").addClass('relation-and').removeClass('relation-or');
                } else {
                    $("#cuw-campaign #cuw-conditions .condition-relation").removeClass('relation-and').addClass('relation-or');
                }
                cuw_campaign.update_conditions_section();
            });

            $("#cuw-campaign #cuw-conditions").on("click", ".condition-remove", function () {
                cuw_campaign.remove_condition($(this).closest('.cuw-condition'));
            });

            $("#cuw-campaign #condition-section").on("change", ".cuw-condition .condition-method select", function () {
                if ($(this).val() === 'empty' || $(this).val() === 'not_empty') {
                    $(this).closest('.cuw-condition').find(".condition-values").hide();
                    $(this).closest('.cuw-condition').find(".condition-values :input").prop('disabled', true);
                } else {
                    $(this).closest('.cuw-condition').find(".condition-values").show();
                    $(this).closest('.cuw-condition').find(".condition-values :input").prop('disabled', false);
                }
            });

            $('#cuw-campaign #filters-match input[name="filters[relation]"]').on('change', function () {
                $("#cuw-campaign #cuw-filters .filter-relation").text($(this).val());
                if ($(this).val() === 'and') {
                    $("#cuw-campaign #cuw-filters .filter-relation").addClass('relation-and').removeClass('relation-or');
                } else {
                    $("#cuw-campaign #cuw-filters .filter-relation").removeClass('relation-and').addClass('relation-or');
                }
                cuw_campaign.update_filters_section();
            });

            $("#cuw-campaign #campaign-save").click(function () {
                cuw_campaign.save(false)
            });
            $("#cuw-campaign #campaign-save-close").click(function () {
                cuw_campaign.save(true)
            });
            $("#cuw-campaign #campaign-close").click(function () {
                cuw_campaign.close()
            });

            $("#cuw-campaign").on("change", "#campaign-form [name]", function () {
                cuw_campaign.hide_field_attention($(this).parent());
            }).on('select2:open', function (event) {
                let target = $(".select2-search__field[aria-controls='select2-" + event.target.id + "-results']");
                if (!target.length) {
                    target = $(".select2-search__field[aria-owns='select2-" + event.target.id + "-results']");
                }
                target.each(function (key, value) {
                    value.focus()
                });
            });

            $("#cuw-campaign #is-bundle").change(function () {
                $("#cuw-campaign #bundle-discount-label").toggle($(this).is(':checked'));
                $("#cuw-campaign #individual-discount-label").toggle(!$(this).is(':checked'));
                if ($(this).is(':checked')) {
                    $("#bundle-item-quantity").slideDown();
                    $("#bundle-item-quantity").find(':input').prop('disabled', false);
                } else {
                    $("#bundle-item-quantity").slideUp();
                    $("#bundle-item-quantity").find(':input').prop('disabled', true);
                }
            });

            $("#cuw-campaign #cuw-triggers #action-toggle-config a").click(function () {
                let hidden = $("#cuw-campaign #cuw-triggers #trigger-advanced-settings").is(":hidden");
                $(this).find("i").toggleClass("cuw-icon-down cuw-icon-up");
                $(this).find("span").html($(this).parent().data(hidden ? 'hide' : 'show'));
                $("#cuw-campaign #cuw-triggers #trigger-advanced-settings").slideToggle();
            });

            $('#cuw-triggers').on('change', '.cuw-trigger input[type="checkbox"]', function () {
                let advanced_settings_toggle = $(this).closest('.cuw-trigger').find('#action-toggle-config');
                let advanced_settings_section = $(this).closest('.cuw-trigger').find('#trigger-advanced-settings');
                if ($(this).is(':checked') && $(this).val() == "added_to_cart") {
                    advanced_settings_toggle.slideDown();
                    advanced_settings_toggle.find('a').html('<i class="cuw-icon-down inherit-color"></i>' + advanced_settings_toggle.data('show'));
                    advanced_settings_section.find(':input').prop('disabled', false);
                } else {
                    advanced_settings_toggle.slideUp();
                    advanced_settings_section.slideUp();
                    advanced_settings_section.find(':input').prop('disabled', true);
                }
            });

            $('#cuw-triggers #triggers-filter').on('change', 'input[type="radio"]', function () {
                let specific_product_section = $(this).closest("#triggers-filter").find("#choose-specific-products");
                let specific_categories_section = $(this).closest("#triggers-filter").find("#choose-specific-categories");

                if ($(this).val() == "products") {
                    specific_product_section.slideDown().find(':input').prop('disabled', false);
                    specific_categories_section.slideUp().find(':input').prop('disabled', true);
                    specific_product_section.find('.select2-list option').remove();
                    cuw_campaign.select2();
                } else if ($(this).val() == "categories") {
                    specific_categories_section.slideDown().find(':input').prop('disabled', false);
                    specific_product_section.slideUp().find(':input').prop('disabled', true);
                    specific_categories_section.find('.select2-list option').remove();
                    cuw_campaign.select2();
                } else {
                    specific_product_section.slideUp().find(':input').prop('disabled', true);
                    specific_categories_section.slideUp().find(':input').prop('disabled', true);
                }
            });

            $("#cuw-campaign #cuw-products .use-product").click(function () {
                if (!$(this).find("input[type='radio']").prop("checked")) {
                    $(this).find("input[type='radio']").prop("checked", true).trigger("click");
                    $('#cuw-campaign #cuw-products .custom-control').removeClass('selected-border');
                    $(this).addClass('selected-border');
                }
            });

            $('#cuw-campaign #cuw-products input[type="radio"]').click(function () {
                $("#cuw-campaign #cuw-products #specific-products select").prop('disabled', $(this).val() != 'specific');
                if ($(this).val() == 'specific') {
                    $("#cuw-campaign #cuw-products #specific-products").slideDown();
                    cuw_campaign.select2();
                } else {
                    $("#cuw-campaign #cuw-products #specific-products").slideUp();
                }

                $("#cuw-campaign #cuw-products #single-product select").prop('disabled', $(this).val() != 'single');
                if ($(this).val() == 'single') {
                    $("#cuw-campaign #cuw-products #single-product").slideDown();
                    cuw_campaign.select2();
                } else {
                    $("#cuw-campaign #cuw-products #single-product").slideUp();
                }

                $("#cuw-campaign #cuw-products #recommendation-engines select").prop('disabled', $(this).val() != 'engine');
                if ($(this).val() == 'engine') {
                    $("#cuw-campaign #cuw-products #recommendation-engines").slideDown();
                    cuw_campaign.select2();
                } else {
                    $("#cuw-campaign #cuw-products #recommendation-engines").slideUp();
                }
            });

            $('#cuw-campaign #cuw-triggers .cuw-trigger input[type="checkbox"]').click(function () {
                $('#cuw-triggers .cuw-trigger-message').hide();
            });

            $("#cuw-campaign .cuw-discount-apply-to select").change(function () {
                let discount_details = $("#cuw-campaign .cuw-discount-details");
                if ($(this).val() === 'no_products') {
                    discount_details.slideUp();
                    discount_details.find(':input').prop('disabled', true);
                } else {
                    discount_details.find(':input').prop('disabled', false);
                    discount_details.slideDown();
                }
            });

            $("#cuw-campaign #cuw-discount .cuw-discount-type select").change(function () {
                let discount_value = $(this).closest("#cuw-discount").find(".cuw-discount-value");
                if ($(this).val() === 'free' || $(this).val() === 'no_discount') {
                    discount_value.find('input').val(0);
                    discount_value.hide();
                } else {
                    discount_value.find('input').val('');
                    discount_value.show();
                }
            });

            $("#cuw-campaign .accordion-head").click(function () {
                $(this).toggleClass("show");
                $(this).find('.navigator i').toggleClass('cuw-icon-accordion-open cuw-icon-accordion-close');
            });

            //to show sliders
            $("#cuw-campaign #cuw_template .choose-template").click(function () {
                cuw_slider.show('#template-slider');
            });

            $("#cuw-campaign #cuw_template .view-template").click(function () {
                cuw_slider.show('#view-template-slider');
            });

            $("#cuw-campaign #cuw_template .edit-template").click(function () {
                cuw_slider.show('#edit-template-slider');
                $('#edit-template-slider #edit :input').trigger('change');
            });

            $("#cuw-campaign #add-condition").click(function () {
                $("#cuw-campaign #condition-section").data('action', 'add');
                $("#cuw-campaign #condition-type").show();
                $("#cuw-campaign #condition-slider .cuw-slider-body #condition-section").html('');
                $("#cuw-campaign #condition-type select").val('');
                cuw_slider.show('#condition-slider');
            });

            $("#cuw-campaign #add-filter").click(function () {
                $("#cuw-campaign #filter-section").data('action', 'add');
                $("#cuw-campaign #filter-type").show();
                $("#cuw-campaign #filter-slider .cuw-slider-body #filter-section").html('');
                $("#cuw-campaign #filter-type select").val('');
                cuw_slider.show('#filter-slider');
            });

            //to hide sliders
            $("#cuw-campaign #template-slider #cuw-change-template-close").click(function () {
                cuw_slider.hide('#template-slider');
            });

            $("#cuw-campaign #cuw-template #cuw-template-save").click(function () {
                cuw_slider.hide('#edit-template-slider');
            });

            $("#cuw-campaign #cuw-template #cuw-template-close").click(function () {
                cuw_slider.hide('#edit-template-slider');
            });

            $("#cuw-campaign #cuw-view-template #cuw-view-template-close").click(function () {
                cuw_slider.hide('#view-template-slider');
            });

            $("#cuw-campaign #cuw-view-offer #cuw-view-offer-close").click(function () {
                cuw_slider.hide('#view-offer-slider');
            });

            $("#cuw-campaign #condition-slider #cuw-condition-close").click(function () {
                cuw_slider.hide('#condition-slider');
            });

            $("#cuw-campaign #filter-slider #cuw-filter-close").click(function () {
                cuw_slider.hide('#filter-slider');
            });

            //to edit campaign name
            $("#cuw-campaign #campaign-header #campaign-name-settings").find("#edit-campaign-name").click(function () {
                $("#cuw-campaign #header").hide();
                $("#cuw-campaign #edit-campaign-name-block").show();
            });

            $("#cuw-campaign #edit-campaign-name-block #campaign-name-save, #cuw-campaign #edit-campaign-name-block #campaign-name-close").click(function () {
                $("#cuw-campaign #edit-campaign-name-block").hide();
                let campaign_name = $("#cuw-campaign #edit-campaign-name-block input").val();
                $("#cuw-campaign #header #campaign-header #campaign-name-settings").find("#campaign-name").text(campaign_name);
                $("#cuw-campaign #header").show();
            });

            //to add or edit condition
            $("#cuw-campaign #slider-condition-add").click(function () {
                let id = $("#cuw-campaign #condition-section .cuw-condition").data('id');
                let action = $("#cuw-campaign #condition-section").data('action');
                if (!id || !action) {
                    return;
                }

                cuw_campaign.format_condition($("#cuw-campaign #condition-section .cuw-condition"));
                cuw_campaign.select2('#cuw-campaign #condition-section', 'destroy');

                if (action === 'add') {
                    $("#cuw-campaign #cuw-conditions").append($("#cuw-campaign #condition-section").html());
                } else if (action === 'edit') {
                    let html = $("#cuw-campaign #condition-section .cuw-condition").html();
                    $("#cuw-campaign #cuw-conditions .cuw-condition[data-id='" + id + "']").html(html);
                }

                $("#cuw-campaign #condition-section .cuw-condition[data-id='" + id + "'] :input").each(function (index, el) {
                    $("#cuw-campaign #cuw-conditions .cuw-condition[data-id='" + id + "'] :input").eq(index).val($(this).val());
                });
                $("#cuw-campaign #cuw-conditions .condition-inputs").hide();
                $("#cuw-campaign #condition-section .cuw-condition").remove();
                $("#cuw-campaign #cuw-conditions").find(".condition-row, .condition-count").show();
                $("#cuw-campaign #slider-condition-add").prop('disabled', true);
                cuw_campaign.update_conditions_section();
                cuw_slider.hide('#condition-slider');
            });

            //to edit condition
            $("#cuw-campaign #cuw-conditions").on("click", ".condition-edit", function () {
                $("#cuw-campaign #condition-type").hide();
                $("#cuw-campaign #condition-section").data('action', 'edit');
                $("#cuw-campaign #condition-section").html($(this).closest('.cuw-condition').clone());
                let id = $(this).closest('.cuw-condition').data('id');
                $("#cuw-campaign #cuw-conditions .cuw-condition[data-id='" + id + "'] :input").each(function (index, el) {
                    $("#cuw-campaign #condition-section .cuw-condition[data-id='" + id + "'] :input").eq(index).val($(this).val());
                });
                $("#cuw-campaign #condition-section").find(".condition-name, .condition-inputs").show();
                $("#cuw-campaign #condition-section").find(".condition-row, .condition-relation, .condition-count").hide();
                $("#cuw-campaign #slider-condition-add").prop('disabled', false);
                cuw_campaign.select2('#cuw-campaign #condition-section');
                cuw_slider.show('#condition-slider');
            });

            $("#cuw-campaign #condition-section").on("change input", ".cuw-condition :input", function () {
                $("#cuw-campaign #slider-condition-add").prop('disabled', false);
                let skip = false;
                $(this).closest('.cuw-condition').find('.condition-data :input').not('.select2-search__field, .optional').each(function (index, element) {
                    if ($(element).hasClass('coupon-condition') && (element.value === 'empty' || element.value === 'not_empty')) {
                        skip = true;
                    }

                    if (element.value === '' && !skip) {
                        $("#cuw-campaign #slider-condition-add").prop('disabled', true);
                        return;
                    }
                });
            });

            //to add or edit filter
            $("#cuw-campaign #slider-filter-add").click(function () {
                let id = $("#cuw-campaign #filter-section .cuw-filter").data('id');
                let action = $("#cuw-campaign #filter-section").data('action');
                if (!id || !action) {
                    return;
                }

                cuw_campaign.format_filter($("#cuw-campaign #filter-section .cuw-filter"));
                cuw_campaign.select2('#cuw-campaign #filter-section', 'destroy');

                if (action === 'add') {
                    $("#cuw-campaign #cuw-filters").append($("#cuw-campaign #filter-section").html());
                } else if (action === 'edit') {
                    let html = $("#cuw-campaign #filter-section .cuw-filter").html();
                    $("#cuw-campaign #cuw-filters .cuw-filter[data-id='" + id + "']").html(html);
                }

                $("#cuw-campaign #filter-section .cuw-filter[data-id='" + id + "'] :input").each(function (index, el) {
                    $("#cuw-campaign #cuw-filters .cuw-filter[data-id='" + id + "'] :input").eq(index).val($(this).val());
                });
                $("#cuw-campaign #cuw-filters .filter-inputs").hide();
                $("#cuw-campaign #filter-section .cuw-filter").remove();
                $("#cuw-campaign #cuw-filters").find(".filter-row, .filter-count").show();
                $("#cuw-campaign #slider-filter-add").prop('disabled', true);
                cuw_campaign.update_filters_section();
                cuw_slider.hide('#filter-slider');
            });

            //to edit filter
            $("#cuw-campaign #cuw-filters").on("click", ".filter-edit", function () {
                $("#cuw-campaign #filter-type").hide();
                $("#cuw-campaign #filter-section").data('action', 'edit');
                $("#cuw-campaign #filter-section").html($(this).closest('.cuw-filter').clone());
                let id = $(this).closest('.cuw-filter').data('id');
                $("#cuw-campaign #cuw-filters .cuw-filter[data-id='" + id + "'] :input").each(function (index, el) {
                    $("#cuw-campaign #filter-section .cuw-filter[data-id='" + id + "'] :input").eq(index).val($(this).val());
                });
                $("#cuw-campaign #filter-section").find(".filter-name, .filter-inputs").show();
                $("#cuw-campaign #filter-section").find(".filter-row, .filter-relation, .filter-count").hide();
                $("#cuw-campaign #slider-filter-add").prop('disabled', false);
                cuw_campaign.select2('#cuw-campaign #filter-section');
                cuw_slider.show('#filter-slider');
            });

            $("#cuw-campaign #filter-section").on("input change", ".cuw-filter :input", function () {
                $("#cuw-campaign #slider-filter-add").prop('disabled', false);
                $(this).closest('.cuw-filter .filter-data').find(":input").not('.select2-search__field, .optional').each(function (index, element) {
                    if (element.value === '') {
                        $("#cuw-campaign #slider-filter-add").prop('disabled', true);
                        return;
                    }
                });
            });

            $("#cuw-campaign #delete-all-conditions").click(function () {
                $("#cuw-campaign #cuw-conditions").html('');
                cuw_campaign.update_conditions_section();
            });

            $("#cuw-campaign #delete-all-filters").click(function () {
                $("#cuw-campaign #cuw-filters").html('');
                cuw_campaign.update_filters_section();
            });

            $("#cuw-campaign #page-type").change(function () {
                cuw_page.spinner('show');
                window.location = $(this).find(':selected').data('url');
            });

            //to toggle end date in optional settings
            $("#cuw-campaign #cuw-schedule #toggle-end-date").change(function () {
                $(this).closest('#cuw-schedule').find('#end-date').toggle();
            });

            $("#cuw-campaign #cuw_product_recommendations_page #display-location").change(function () {
                if ($(this).val() == 'shortcode') {
                    $("#cuw-campaign #cuw-shortcode-wrapper").show();
                } else {
                    $("#cuw-campaign #cuw-shortcode-wrapper").hide();
                }
            });
        }
    }

    /* Engines List */

    const cuw_engines = {
        init: function () {
            this.event_listeners();
        },

        ajax: function (method, data) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: $.extend({
                    action: 'cuw_ajax',
                    method: method,
                    nonce: cuw_ajax_nonce || ""
                }, data),
                beforeSend: function () {
                    cuw_page.spinner('show');
                },
                complete: function () {
                    cuw_page.spinner('hide');
                },
                success: function (response) {
                    let status = response.data.status ?? "error";
                    let message = response.data.message ?? cuw_i18n.error;
                    if (message) {
                        cuw_page.notify(message, status);
                    }
                    if (response.data.redirect) {
                        cuw_page.redirect('&' + response.data.redirect);
                    }
                    if (response.data.change) {
                        if (response.data.change.id && response.data.change.status) {
                            let status = response.data.change.status;
                            let html = '<span class="p-2 status-' + response.data.change.status.code + '">' + status.text + '</span>';
                            $("#cuw-engines table .engine-" + response.data.change.id).find(".engine-status").html(html);
                        }
                    }
                    if (response.data.remove) {
                        if (response.data.remove.ids) {
                            $.each(response.data.remove.ids, function (key, value) {
                                $("#cuw-engines table .engine-" + value).fadeOut(300, function () {
                                    $(this).remove();
                                });
                            });
                        } else if (response.data.remove.id) {
                            $("#cuw-engines table .engine-" + response.data.remove.id).fadeOut(300, function () {
                                $(this).remove();
                            });
                        }
                    }
                    if (response.data.refresh) {
                        cuw_page.reload(2000);
                    }
                }
            });
        },

        // get chosen engine ids
        get_chosen_engine_ids: function () {
            let ids = [];
            $("#cuw-engines .check-single:checked").each(function () {
                ids.push($(this).val());
            });
            return ids;
        },

        // show bulk toolbar
        show_bulk_toolbar: function () {
            $("#cuw-engines #basic-toolbar").attr('style', 'display:none !important');
            $("#cuw-engines #bulk-toolbar").attr('style', 'display:flex !important');
        },

        // show basic toolbar
        show_basic_toolbar: function () {
            $("#cuw-engines #bulk-toolbar").attr('style', 'display:none !important');
            $("#cuw-engines #basic-toolbar").attr('style', 'display:flex !important');
        },

        // bulk action for engines
        bulk_actions: function (action, ids = null) {
            if (ids === null) {
                ids = this.get_choosen_campaign_ids();
            }
            this.ajax('engine_bulk_actions', {bulk_action: action, ids: ids});
            $("#cuw-engines #bulk-toolbar #checks-count").html(0);
        },

        // delete engine
        delete: function (ids, bulk = false) {
            if (bulk) {
                cuw_engines.bulk_actions('delete', ids);
            } else {
                this.ajax('delete_engine', {id: ids[0]});
            }
            $("#cuw-engines #modal-delete").modal('hide');
        },

        // toggle list and create section
        toggle_section: function () {
            $("#cuw-engines #engines-create, #cuw-engines #engines-list").toggle();
        },

        enable: function (id, enabled) {
            this.ajax('enable_engine', {id: id, enabled: enabled ? 1 : 0});
        },

        set_engines_list_limit: function (value) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'set_engines_list_limit',
                    value: value,
                    nonce: cuw_ajax_nonce || ""
                },
                success: function (response) {
                    if (response.data && response.data.refresh) {
                        cuw_page.reload();
                    } else {
                        cuw_page.notify(cuw_i18n.error, 'error');
                    }
                }
            })
        },

        event_listeners: function () {
            $("#cuw-engines .create-engine").click(function () {
                cuw_engines.toggle_section();
            });

            $("#cuw-engines #back-to-engines").click(function () {
                cuw_engines.toggle_section();
            });

            $("#cuw-engines #check-all").click(function () {
                if ($(this).is(":checked")) {
                    cuw_engines.show_bulk_toolbar();
                    $("#cuw-engines .check-single").prop('checked', true).trigger('change');
                } else {
                    cuw_engines.show_basic_toolbar();
                    $("#cuw-engines .check-single").prop('checked', false).trigger('change');
                }
            });

            $("#cuw-engines").on("click", ".engine-enable", function () {
                cuw_engines.enable($(this).data('id'), $(this).is(":checked"));
            }).on("click", ".engine-delete", function () {
                cuw_engines.delete($(this).data('ids'), $(this).data('bulk'));
            });

            $("#cuw-engines .check-single").change(function () {
                let checks_count = $("#cuw-engines .check-single:checked").length;
                if (checks_count > 0) {
                    cuw_engines.show_bulk_toolbar();
                } else {
                    cuw_engines.show_basic_toolbar();
                }
                $("#cuw-engines #bulk-toolbar #checks-count").html(checks_count);
            });

            $("#cuw-engines #modal-delete").on("show.bs.modal", function (event) {
                let ids = [], title = '', target = $(event.relatedTarget), linked_ids = 0;
                let bulk = target.data('bulk') ? true : false;
                if (bulk) {
                    ids = cuw_engines.get_chosen_engine_ids();
                } else {
                    ids.push(target.data('id'));
                }
                ids.forEach(function (id) {
                    title += "<br>" + " #" + id + ": " + $("#cuw-engines .engine-" + id).data('title');
                    linked_ids += parseInt($("#cuw-engines .engine-" + id).data('linked_campaigns'));
                });
                $("#cuw-engines #modal-delete .engine-title").html(title);
                $("#cuw-engines #modal-delete .engine-delete").data('ids', ids);
                $("#cuw-engines #modal-delete .engine-delete").data('bulk', bulk);
                if (linked_ids > 0) {
                    $("#cuw-engines #modal-delete .engine-delete-text").hide();
                    $("#cuw-engines #modal-delete .engine-delete-warning").show();
                    $("#cuw-engines #modal-delete .engine-count").html(linked_ids);
                    $("#cuw-engines #modal-delete .engine-delete-yes, .engine-delete-no").hide();
                    $("#cuw-engines #modal-delete .engine-delete-close").show();
                } else {
                    $("#cuw-engines #modal-delete .engine-delete-warning").hide();
                    $("#cuw-engines #modal-delete .engine-delete-text").show();
                    $("#cuw-engines #modal-delete .engine-delete-yes, .engine-delete-no").show();
                    $("#cuw-engines #modal-delete .engine-delete-close").hide();
                }
            });

            $("#cuw-engines #engines-list #engine-list-block").find("#engines-per-page").change(function () {
                cuw_engines.set_engines_list_limit($(this).val());
            });
        }
    }

    /* Recommendation Engine */
    const cuw_engine = {

        type: '',
        action: '',

        // init
        init: function () {
            this.select2();
            this.event_listeners();

            let engine = $('#cuw-engine');
            this.type = engine.data('type');
            this.action = engine.data('action');
        },

        validate: function () {
            let passed = true;

            if ($('#cuw-engine-filter-container').length > 0) {
                if ($("#cuw-engine-filter-container #added-engine-filters-list .cuw-added-engine-filter").length === 0) {
                    passed = false;
                    $("#cuw-engine-filter-container #filter-type .cuw-engine-filter-section").each(function (index, element) {
                        if ($(element).find("#filter-section").children().length > 0) {
                            passed = true;
                        }
                    });
                    if (!passed) {
                        cuw_engine.message('show', cuw_i18n.filter_required, $('#no-engine-filters'), true)
                        $('#no-engine-filters').removeClass('d-none');
                        $("#cuw-engine-filter-container .selected-filters-label").addClass('d-none');
                    }
                }
            }

            if (!passed) {
                cuw_page.notify(cuw_i18n.engine_not_saved, 'error');
            }
            return passed;
        },

        // save engine
        save: function (close) {
            if (!this.validate()) {
                return;
            }

            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'save_engine',
                    form_data: $("#cuw-engine #engine-form").serialize(),
                    page_no: cuw_page.query_param('page_no', 1),
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
                        let status = response.data.status ?? "error";
                        let message = response.data.message ?? cuw_i18n.error;
                        cuw_engine.hide_field_attention($("#cuw-engine #engine-form [name]").parent());
                        if (status === "error" && typeof message === "object") {
                            if (message.fields) {
                                $.each(message.fields, function (field_name) {
                                    let div = $('#cuw-engine #engine-form [name^="' + field_name + '"]').parent();
                                    cuw_engine.show_field_attention(div, message.fields[field_name]);
                                });
                            }
                            if (message.divs) {
                                $.each(message.divs, function (key, selector) {
                                    $('#cuw-engine ' + selector).addClass('border-danger');
                                });
                            }
                            cuw_page.notify(cuw_i18n.engine_not_saved, 'error');
                        } else {
                            cuw_page.notify(message, status);
                        }
                        if (status === "success" && close) {
                            cuw_engine.close(2000);
                        } else if (response.data.redirect) {
                            cuw_page.redirect('&' + response.data.redirect, 2000);
                        }
                    } else {
                        cuw_page.notify(cuw_i18n.error, 'error');
                    }
                }
            });
        },

        // message
        message: function (action, message = '', path, error = false) {
            if (action !== 'hide') {
                path.html(message).show();
                path.removeClass(error ? 'text-secondary' : 'text-danger');
                path.addClass(error ? 'text-danger' : 'text-secondary');
            } else {
                path.html('').hide();
            }
        },

        // close engine
        close: function (delay = 0) {
            let page_no = cuw_page.query_param('page_no', 1);
            if (this.action === 'edit' && page_no > 1) {
                cuw_page.redirect('&tab=engines&page_no=' + page_no, delay);
            } else {
                cuw_page.redirect('&tab=engines', delay);
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

        // reload select2
        reload: function () {
            this.select2();
        },

        // add validation attention message
        show_field_attention: function (div, message = cuw_i18n.this_field_is_required) {
            div.append('<small class="invalid text-danger d-block mt-1">' + message + '</small>');
            div.find('input, select, .select2-selection').addClass('border-danger');
            div.closest('.cuw-engine-filter').find('.filter-text-wrapper').addClass('border border-danger');
        },

        // remove validation attention message
        hide_field_attention: function (div) {
            div.find('.invalid').remove();
            div.find('input, select, .select2-selection').removeClass('border-danger');
        },

        // filter methods
        add_filter: function (filter) {
            let type = filter.data('id');
            if (type) {
                let id = $.now();
                let data = cuw_views.engine_filters[type].replace(/{key}/g, id);
                let html = cuw_views.engine_filters.wrapper.replace(/{key}/g, id);
                html = html.replace(/{type}/g, type);
                html = html.replace('{data}', data);
                filter.find("#filter-section").html(html);
                $("#filter-section .filter-inputs").show();
                $("#filter-section .filter-row").hide();
                filter.removeClass('border-danger')
                $("#filter-section .cuw-engine-filter :input").trigger("change");
                cuw_engine.select2('#filter-section');
                filter.find("#add-engine-filter").addClass('d-none');
                filter.find("#remove-engine-filter").removeClass('d-none');
                filter.removeClass('border-gray-light').addClass('border-primary');
            } else {
                filter.addClass('border-danger');
            }
        },

        remove_filter: function (filter) {
            filter.find("#filter-section").html('');
            filter.find("#remove-engine-filter").addClass('d-none');
            filter.find("#add-engine-filter").removeClass('d-none');
            filter.removeClass('border-primary').addClass('border-gray-light');
        },

        validate_filter: function () {
            let active_filter = false;
            $("#filter-type .cuw-engine-filter-section").each(function (index, element) {
                if ($(element).find("#filter-section").html() !== '') {
                    active_filter = true;
                    $(element).find(":input").not('#filter-section .select2-search__field').each(function (input_index, input_section) {
                        if (input_section.value === '') {
                            return active_filter = false;
                        }
                    });
                    if (!active_filter) {
                        return active_filter;
                    }
                }
            });
            return active_filter;
        },

        // amplifiers
        add_amplifier: function (amplifier) {
            $('#cuw-engine-amplifiers #amplifier-section').each(function (index, element) {
                $(element).html('');
            });
            $("#amplifier-type .cuw-engine-amplifier-section").removeClass('border-primary').addClass('border-gray-light');
            let type = amplifier.data('id');
            if (type) {
                let id = $.now();
                let data = cuw_views.engine_amplifiers[type].replace(/{key}/g, id);
                let html = cuw_views.engine_amplifiers.wrapper.replace(/{key}/g, id);
                html = html.replace(/{type}/g, type);
                html = html.replace('{data}', data);
                amplifier.find("#amplifier-section").html(html);
                $("#amplifier-section .amplifier-inputs").show();
                $("#amplifier-section .amplifier-row").hide()
                amplifier.removeClass('border-danger');
                amplifier.removeClass('border-gray-light').addClass('border-primary');
            } else {
                amplifier.addClass('border-danger');
            }
        },

        event_listeners: function () {

            //to edit engine name
            $("#cuw-engine #engine-name-settings").find("#edit-engine-name").click(function () {
                $("#cuw-engine #header").hide();
                $("#cuw-engine #edit-engine-name-block").show();
            });

            $("#cuw-engine #edit-engine-name-block #engine-name-save, #cuw-engine #edit-engine-name-block #engine-name-close").on('click', function () {
                $("#cuw-engine #edit-engine-name-block").hide();
                let engine_name = $(" #cuw-engine #edit-engine-name-block input").val();
                $("#cuw-engine #header #engine-header #engine-name-settings").find("#engine-name").text(engine_name);
                $("#cuw-engine #header").show();
            });

            $("#cuw-engine #edit-engine-name-block #engine-name-close").on('click', function () {
                $(" #cuw-engine #edit-engine-name-block").hide();
                $(" #cuw-engine #header").show();
            });

            // engine save and close
            $("#cuw-engine #engine-save").click(function () {
                cuw_engine.save(false)
            });
            $("#cuw-engine #engine-save-close").click(function () {
                cuw_engine.save(true)
            });
            $("#cuw-engine #engine-close").click(function () {
                cuw_engine.close()
            });

            $("#cuw-engine .accordion-head").click(function () {
                $(this).toggleClass("show");
                $(this).find('.navigator i').toggleClass('cuw-icon-accordion-open cuw-icon-accordion-close');
            });

            // move to filter
            $(".move-to-filter").on('click', function () {
                $("#page-accordion .accordion-head").trigger('click');
                $(this).closest("#cuw_product_recommendations_page").find(".form-separator").addClass('d-none');
                $(this).closest(".input-group").addClass('d-none');
                $("#filter-accordion .accordion-head").trigger('click');
            });

            // engine filter events add and remove
            $('.filter-action-section').on('click', function (event) {
                let section = $(this).closest('.cuw-engine-filter-section');
                if (section.find('#filter-section .cuw-engine-filter').length == 0) {
                    cuw_engine.add_filter(section);
                } else {
                    cuw_engine.remove_filter(section);
                }
                $(".move-to-amplifier").attr('disabled', !cuw_engine.validate_filter());
                $("#engine-filter-slider #save-filter").attr('disabled', !cuw_engine.validate_filter());
            });

            // validate filter
            $("#filter-type").on('input', ' :input', function () {
                $(".move-to-amplifier").attr('disabled', !cuw_engine.validate_filter());
                $("#engine-filter-slider #save-filter").attr('disabled', !cuw_engine.validate_filter());
            });

            // move to amplifier
            $(".move-to-amplifier").on('click', function () {
                $("#filter-accordion .accordion-head").trigger('click');
                $("#amplifier-accordion .input-group, #amplifier-accordion .form-separator").removeClass('d-none');
                $("#cuw_engine_amplifier .input-group #change-amplifier").addClass('d-none');
                $("#cuw_engine_amplifier .input-group #engine-save").removeClass('d-none');
                $("#cuw_product_recommendations_amplifiers .input-group #engine-save, " +
                    "#cuw_product_recommendations_amplifiers .input-group #change-amplifier").addClass('d-none');
                $("#cuw_product_recommendations_amplifiers .input-group .move-to-template").removeClass('d-none');
                $("#amplifier-type .cuw-engine-amplifier-section").removeClass('d-none');
                $("#amplifier-type input[type='radio']:checked").closest(".cuw-engine-amplifier-section")
                    .removeClass('border-gray-light').addClass('border-primary');
                $("#amplifier-accordion .accordion-head").trigger('click');
                $(this).closest("#cuw_engine_filter").find(".form-separator").addClass('d-none');
                $(this).closest(".input-group").addClass('d-none');
            });

            // change filter
            $("#add-new-filter").on('click', function () {
                cuw_slider.show('#engine-filter-slider');
            });

            // close slider filter
            $("#cuw-filter-close").on('click', function () {
                cuw_slider.hide('#engine-filter-slider');
            });

            // save filter
            $("#engine-filter-slider #save-filter").on('click', function () {
                cuw_engine.select2('#engine-filter-slider', 'destroy');
                $("#engine-filter-slider #cuw-engine-filters-list .cuw-engine-filter-section").each(function (index, element) {
                    if ($(element).find("#filter-section").children().length > 0) {
                        $(element).addClass('cuw-added-engine-filter');
                        $("#added-engine-filters-list:last").append($(element).clone());
                        $("#engine-filter-slider #cuw-engine-filters-list .cuw-engine-filter-section[data-id='" + $(element).data('id') + "'] :input").each(function (index, el) {
                            $("#added-engine-filters-list .cuw-engine-filter-section[data-id='" + $(element).data('id') + "'] :input").eq(index).val($(this).val());
                        });
                        $(element).removeClass('cuw-added-engine-filter');
                        cuw_engine.remove_filter($("#engine-filter-slider #cuw-engine-filters-list #" + $(element).attr('id')));
                        $("#engine-filter-slider #cuw-engine-filters-list #" + $(element).attr('id')).removeClass('d-flex').addClass('d-none');
                    }
                });
                cuw_engine.select2('#engine-filter-slider');
                cuw_engine.select2("#added-engine-filters-list");

                $("#cuw-engine-filter-container #added-engine-filters-list .cuw-added-engine-filter").each(function (index, element) {
                    $(element).removeClass('border-primary').addClass('border-gray-light');
                    $(element).find('#remove-engine-filter').addClass('d-none');
                    $(element).find('#remove-existing-engine-filter').removeClass('d-none');
                });
                $('#no-engine-filters').addClass('d-none');
                cuw_slider.hide('#engine-filter-slider');
                $(this).attr('disabled', true);
            });

            // remove existing filter
            $(document).on('click',  '#added-engine-filters-list #remove-existing-engine-filter',function () {
                let filter_type = $(this).closest(".cuw-added-engine-filter").data('type');
                if (!filter_type) {
                    filter_type = $(this).closest(".cuw-engine-filter-section").data('id');
                }
                $(this).closest(".cuw-added-engine-filter").remove();
                $(this).closest(".cuw-engine-filter-section").remove();
                $("#engine-filter-slider #cuw-engine-filters-list #engine-filter-" + filter_type).removeClass('d-none');
                if ($("#added-engine-filters-list").children().length === 0) {
                    $('#no-engine-filters').removeClass('d-none');
                }
            });

            // engine amplifiers events
            // add amplifier
            $("#amplifier-type #add-engine-amplifier").on('click', function () {
                cuw_engine.add_amplifier($(this).closest('.cuw-engine-amplifier-section'));
            });

            // change amplifier
            $("#change-amplifier").on("click", function () {
                $("#cuw-engine-amplifiers").css('max-height', 'none');
                $("#amplifier-type .cuw-engine-amplifier-section").removeClass('d-none');
                $("#amplifier-type input[type='radio']:checked").closest(".cuw-engine-amplifier-section").removeClass('border-gray-light').addClass('border-primary');
                $(this).closest("#cuw_engine_amplifier").find('.form-separator').addClass('d-none');
                $(this).closest('.input-group').addClass('d-none');
            });

            // move to template
            $(".move-to-template").on('click', function () {
                $("#amplifier-accordion .accordion-head").trigger('click');
                $("#template-accordion .accordion-head").trigger('click');
                $(this).closest("#cuw_product_recommendations_amplifiers").find(".form-separator").addClass('d-none');
                $(this).closest(".input-group").addClass('d-none');
            });
        }
    }


    /* Settings */
    const cuw_settings = {

        // init
        init: function () {
            this.event_listeners();
        },

        // save
        save: function () {
            cuw_settings.check_numeric_input();

            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'save_settings',
                    form_data: $("#cuw-settings #settings-form").serialize(),
                    nonce: cuw_ajax_nonce || ""
                },
                beforeSend: function () {
                    cuw_page.spinner('show');
                },
                complete: function () {
                    cuw_page.spinner('hide');
                },
                success: function (response) {
                    let status = response.data.status ?? "error";
                    let message = response.data.message ?? cuw_i18n.error;
                    cuw_page.notify(message, status);
                }
            });
        },

        // make license request
        license_ajax: function (action) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'perform_license_actions',
                    perform: action,
                    key: $("#cuw-settings #license-key").val(),
                    nonce: cuw_ajax_nonce || ""
                },
                beforeSend: function () {
                    cuw_page.spinner('show');
                },
                complete: function () {
                    cuw_page.spinner('hide');
                },
                success: function (response) {
                    let status = response.data.status ?? "error";
                    let message = response.data.message ?? cuw_i18n.error;
                    cuw_page.notify(message, status);
                    if (response.data.license) {
                        if (response.data.license.status == 'active') {
                            $("#cuw-settings #license-key").removeClass('border-danger').addClass('border-success');
                            $("#cuw-settings #activate-license").hide();
                            $("#cuw-settings #deactivate-license").show();
                        } else {
                            $("#cuw-settings #license-key").removeClass('border-success').addClass('border-danger');
                            $("#cuw-settings #deactivate-license").hide();
                            $("#cuw-settings #activate-license").show();
                        }
                        $("#cuw-settings #license-status").html(response.data.license.status_text);
                    }
                }
            });
        },

        check_numeric_input: function () {
            $("#cuw-settings .cuw-format-numeric-input").each(function () {
                if ($(this).val().length === 0 && !isNaN(parseInt($(this).attr('min')))) {
                    $(this).val($(this).attr('min'));
                }
            });
        },

        format_numeric_input: function (numeric_field) {
            if (!isNaN(parseInt(numeric_field.attr('max'))) && parseInt(numeric_field.attr('max')) <= parseInt(numeric_field.val())) {
                numeric_field.val(numeric_field.attr('max'));
            } else if (!isNaN(parseInt(numeric_field.attr('min'))) && parseInt(numeric_field.attr('min')) >= parseInt(numeric_field.val())) {
                numeric_field.val(numeric_field.attr('min'));
            } else {
                numeric_field.val(numeric_field.val().replace(/\D/g, ''));
            }
        },

        // event listeners
        event_listeners: function () {
            $("#cuw-settings #settings-save").click(function () {
                cuw_settings.save()
            });

            $("#cuw-settings #activate-license").click(function () {
                cuw_settings.license_ajax('activate');
            });
            $("#cuw-settings #deactivate-license").click(function () {
                cuw_settings.license_ajax('deactivate');
            });
            $("#cuw-settings #check-license-status").click(function () {
                cuw_settings.license_ajax('check_status');
            });

            $("#cuw-settings #post-purchase-process-type").change(function () {
                $("#cuw-settings #supported-payment-methods").html($(this).find('option:selected').data('methods'));
            });

            $("#cuw-settings #settings-statics #send-weekly-report").on('click', function () {
                $(this).closest('#settings-statics').find('.cuw-email-block').toggle();
            });

            $("#cuw-settings #engine-cache-enabled").on('click', function () {
                $("#cuw-settings .cuw-engine-cache-expiration-block").toggle();
            });

            $("#cuw-settings .cuw-format-numeric-input").on('input', function () {
                cuw_settings.format_numeric_input($(this));
            });

            $("#cuw-settings #always-display-offer").on('click', function () {
                $('#cuw-settings .cuw-offer-notice-position').toggle();
                $('#cuw-settings .cuw-offer-notice-position :input').attr('disabled', $(this).is(':checked'));
            });
        }
    }

    /* Stats */
    const cuw_stats = {
        cache: {},
        init: function (section) {
            this.event_listeners(section);
            this.load(section);
        },

        // load
        load: function (section) {
            let range = '';
            let date = {};
            let tab = section.data('tab');
            let campaign = section.find("#campaign").val() || '';
            if (tab === 'dashboard') {
                range = section.find("#range input[type=radio]:checked").val();
            } else {
                range = section.find("#range").val();
            }
            let currency = section.find("#currency").val() || '';
            if (range === 'custom') {
                $("#cuw-reports #custom-range").show();
                let date_from = section.find("#date-from").val() || '';
                let date_to = section.find("#date-to").val() || '';
                if (date_from) section.find("input#date-to").attr("min", date_from);
                if (date_to) section.find("input#date-from").attr("max", date_to);
                if (date_from && date_to) {
                    date.from = date_from;
                    date.to = date_to;
                } else {
                    return;
                }
            } else {
                section.find("#custom-range").hide();
            }

            //disabling inputs during the time of loading
            section.find(':input').prop('disabled', true);

            let key = `${range}_${currency}`;
            key += (campaign ? `_${campaign}` : '');
            key += (date.from ? `_${date.from}` : '');
            key += (date.to ? `_${date.to}` : '');

            if (this.cache[key]) {
                cuw_stats.show(this.cache[key], section);
            } else {
                $.ajax({
                    type: 'post',
                    url: cuw_ajax_url,
                    data: {
                        action: 'cuw_ajax',
                        method: 'get_chart_data',
                        campaign: campaign,
                        tab: tab,
                        range: range,
                        date: date,
                        currency: currency,
                        nonce: cuw_ajax_nonce || ""
                    },
                    beforeSend: function () {
                        section.find('.chart-section .spinner-border').show();
                    },
                    success: function (response) {
                        if (response.data) {
                            let chart_data = response.data;
                            if (!cuw_stats.cache[key]) {
                                cuw_stats.cache[key] = {};
                            }
                            cuw_stats.cache[key].chart_data = chart_data;
                            section.find('.chart-section .spinner-border').hide();
                            cuw_stats.show(cuw_stats.cache[key], section);
                        }
                    }
                });

                $.ajax({
                    type: 'post',
                    url: cuw_ajax_url,
                    data: {
                        action: 'cuw_ajax',
                        method: 'get_upsell_info',
                        campaign: campaign,
                        tab: tab,
                        range: range,
                        date: date,
                        currency: currency,
                        nonce: cuw_ajax_nonce || ""
                    },
                    beforeSend: function () {
                        section.find('.card .spinner-border').show();
                    },
                    success: function (response) {
                        if (response.data) {
                            let upsell_data = response.data;
                            if (!cuw_stats.cache[key]) {
                                cuw_stats.cache[key] = {};
                            }
                            cuw_stats.cache[key].upsell_data = upsell_data;
                            section.find('.card .spinner-border').hide();
                            cuw_stats.show(cuw_stats.cache[key], section);
                        }
                    }
                });
            }
        },

        // load html data
        load_html: function (data, section) {
            if (data.html) {
                $.each(data.html, function (key, value) {
                    if (value !== '-') {
                        section.find('#' + key).html(value).closest(".col-md-6").show();
                    } else {
                        section.find('#' + key).closest(".col-md-6").hide();
                    }
                });

                let difference_wrapper = section.find('.difference-wrapper');
                difference_wrapper.css('display', 'none');
                section.find('.arrow-up, .arrow-down').hide();
                section.find('.difference').removeClass(['text-danger', 'text-success']);
                if (data.diff) {
                    difference_wrapper.css('display', 'flex');
                    section.find('.since-text').html(cuw_i18n['since_' + data.diff.since]);
                    $.each(data.diff.percentages, function (key, value) {
                        if (value > 0) {
                            section.find('#' + key + "-diff .arrow-up").show();
                            section.find('#' + key + "-diff .difference").addClass('text-success');
                        } else {
                            section.find('#' + key + "-diff .arrow-down").show();
                            section.find('#' + key + "-diff .difference").addClass('text-danger');
                        }
                        section.find('#' + key + "-diff .percentage").html(Math.abs(value) + '%');
                    });
                }
            }
        },

        // load chart data
        load_chart: function (data, section) {
            let ctx_data = {};
            if (section.data('tab') === 'dashboard') {
                ctx_data = {
                    'dashboard-revenue': section.find('#upsell-revenue-chart'),
                    'dashboard-campaign-revenue': section.find('#campaign-revenue-chart'),
                    'dashboard-products-purchased': section.find('#products-purchased-chart')
                };
            } else if (section.data('tab') === "reports") {
                ctx_data = {
                    'reports-chart': section.find('#reports-chart')
                };
            }

            if (data.length !== 0) {
                $(section).find('.chart-section #default-text').hide();
                $.each(ctx_data, function (key, value) {
                    let ctx = $(value);
                    let config = {};
                    let chart = Chart.getChart(ctx);
                    if (chart !== undefined) {
                        chart.destroy();
                    }

                    if (key === "reports-chart") {
                        config = {
                            type: "line",
                            data: {
                                datasets: [
                                    {
                                        label: cuw_i18n.revenue,
                                        fill: false,
                                        lineTension: 0.4,
                                        borderColor: "#4bc072",
                                        backgroundColor: "rgba(75,192,114,0.8)",
                                        data: data.revenue
                                    },
                                    {
                                        label: cuw_i18n.items,
                                        fill: false,
                                        lineTension: 0.4,
                                        borderColor: "#3696ff",
                                        backgroundColor: "rgba(0,123,255,0.8)",
                                        data: data.items,
                                        hidden: false
                                    }]
                            },
                            options: {
                                scales: {
                                    x: {reverse: cuw_is_rtl},
                                    y: {beginAtZero: true, position: cuw_is_rtl ? 'right' : 'left'}
                                }
                            }
                        }
                    } else if (key === "dashboard-campaign-revenue") {
                        config = {
                            type: "bar",
                            data: {
                                labels: data.campaigns_revenue.labels,
                                datasets: [{
                                    axis: 'y',
                                    label: cuw_i18n.revenue,
                                    data: data.campaigns_revenue.revenues,
                                    fill: false,
                                    borderRadius: 4,
                                    barThickness: 15,
                                    backgroundColor: '#0a5cff'
                                }],
                            },
                            options: {
                                plugins: {legend: {display: false}},
                                responsive: true,
                                maintainAspectRatio: true,
                                indexAxis: 'y',
                                scales: {
                                    x: {reverse: cuw_is_rtl},
                                    y: {position: cuw_is_rtl ? 'right' : 'left'}
                                }
                            }
                        }
                    } else if (key === "dashboard-revenue") {
                        config = {
                            type: "line",
                            data: {
                                datasets: [{
                                    label: cuw_i18n.revenue,
                                    fill: false,
                                    lineTension: 0.4,
                                    borderColor: "#3377ff",
                                    backgroundColor: "#0a5cff",
                                    data: data.revenue
                                }],
                            },
                            options: {
                                plugins: {legend: {display: false}},
                                scales: {
                                    x: {grid: {display: false}, reverse: cuw_is_rtl},
                                    y: {
                                        grid: {display: false},
                                        beginAtZero: true,
                                        position: cuw_is_rtl ? 'right' : 'left'
                                    }
                                }
                            }
                        }
                    } else if (key === "dashboard-products-purchased") {
                        config = {
                            type: "line",
                            data: {
                                datasets: [{
                                    label: 'Products Purchased',
                                    fill: false,
                                    lineTension: 0.4,
                                    borderColor: "#3377ff",
                                    backgroundColor: "#0a5cff",
                                    data: data.items
                                }]
                            },
                            options: {
                                plugins: {legend: {display: false}},
                                scales: {
                                    x: {grid: {display: false}, reverse: cuw_is_rtl},
                                    y: {
                                        grid: {display: false},
                                        beginAtZero: true,
                                        position: cuw_is_rtl ? 'right' : 'left'
                                    }
                                }
                            }
                        }
                    }
                    new Chart(ctx, config);
                });
            } else {
                $.each(ctx_data, function (key, value) {
                    $(value).hide();
                    $(section).find('.chart-section #default-text').replaceWith('<div style="display:flex; justify-content:center; height:100px; font-size: 1rem; align-items: center;">' + cuw_i18n.no_data_found + '</div>');
                });
            }
        },
        show: function (data, section) {
            //load data if only both chart data and upsell data are present
            if (data.chart_data && data.upsell_data) {
                section.find(':input').prop('disabled', false);
                cuw_stats.load_chart(data.chart_data, section);
                cuw_stats.load_html(data.upsell_data, section);
            }
        },

        set_revenue_tax_display: function (type) {
            $.ajax({
                type: 'post',
                url: cuw_ajax_url,
                data: {
                    action: 'cuw_ajax',
                    method: 'set_revenue_tax_display',
                    type: type,
                    nonce: cuw_ajax_nonce || ""
                },
                success: function (response) {
                    if (response.data && response.data.refresh) {
                        cuw_page.reload();
                    } else {
                        cuw_page.notify(cuw_i18n.error, 'error');
                    }
                }
            })
        },

        event_listeners: function (section) {
            $(section).on("change", "#campaign, #range, #date-from, #date-to, #currency", function () {
                cuw_stats.load(section)
            });

            $(section).on("change", "input[type='radio'][name='range']", function () {
                $("input[type='radio'][name='range']").closest('label').removeClass('btn-primary').addClass('btn-white');
                $(this).closest('label').removeClass('btn-white').addClass('btn-primary');
            });

            $(section).on("change", '#revenue-type', function () {
                cuw_stats.set_revenue_tax_display($(this).val());
            });
        }
    }

    /* Add-ons */
    const cuw_addons = {

        // Init
        init: function () {
            this.addon_notice();
        },

        // Addon notice
        addon_notice: function () {
            if (cuw_page.query_param('addon_activated') === '1') {
                cuw_page.notify(cuw_i18n.addon_activated);
            } else if (cuw_page.query_param('addon_activated') === '0') {
                cuw_page.notify(cuw_i18n.addon_activation_failed, 'error');
            } else if (cuw_page.query_param('addon_deactivated') === '1') {
                cuw_page.notify(cuw_i18n.addon_deactivated);
            } else if (cuw_page.query_param('addon_deactivated') === '0') {
                cuw_page.notify(cuw_i18n.addon_deactivation_failed, 'error');
            }
        },
    }

    /* Init */
    $(document).ready(function () {
        if ($("#cuw-page").length !== 0) {
            cuw_page.init();

            if ($("#cuw-campaigns").length !== 0) {
                cuw_campaigns.init();
            } else if ($("#cuw-campaign").length !== 0) {
                cuw_campaign.init();
            } else if ($("#cuw-engine").length !== 0) {
                cuw_engine.init();
            } else if ($("#cuw-engines").length !== 0) {
                cuw_engines.init();
            } else if ($("#cuw-reports").length !== 0) {
                cuw_stats.init($("#cuw-reports"));
            } else if ($("#cuw-dashboard").length !== 0) {
                cuw_stats.init($("#cuw-dashboard"));
            } else if ($("#cuw-settings").length !== 0) {
                cuw_settings.init();
            } else if ($("#cuw-add-ons").length !== 0) {
                cuw_addons.init();
            }
        }
    });

});