(function ($) {
    "use strict";

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     */

    // scroll add and remove class
    $(window).scroll(function () {
        if (
            $(this).scrollTop() + $(window).height() <
            $(document).height() - 150
        ) {
            $(".woo-invoice-save-changes-selector").addClass(
                "woo-invoice-save-changes"
            );
        } else {
            $(".woo-invoice-save-changes-selector").removeClass(
                "woo-invoice-save-changes"
            );
        }
    });

    $(window).scroll(function () {
        if (
            $(this).scrollTop() + $(window).height() <
            $(document).height() - 150
        ) {
            $(".woo-invoice-banner-sidebar").addClass(
                "woo-invoice-banner-sidebar-sticky"
            );
        } else {
            $(".woo-invoice-banner-sidebar").removeClass(
                "woo-invoice-banner-sidebar-sticky"
            );
        }
    });

    $(window).on("mousewheel", function (e) {
        //if($(window).scrollTop() + $(window).height() > $(document).height()-100)  {

        //$(".woo-invoice-save-changes-selector").removeClass("woo-invoice-save-changes");
        // } else {
        //$(".woo-invoice-save-changes-selector").addClass("woo-invoice-save-changes");
        //}

        var initialContent = $(".woo-invoice-dashboard-content > li:eq(0)");
        $(
            ".woo-invoice-dashboard-sidebar .woo-invoice-sidebar-navbar-light"
        ).height(initialContent.parent().height() - 23);
    });

    $(window).load(function () {
        $("input[name='wf_tabs']").on("change", function () {
            var selectedTab = $(this).val();
            sessionStorage.setItem("active_tab", selectedTab);
        });

        var activeTab = sessionStorage.getItem("active_tab");

        if (activeTab === "settings") {
            $("#tab1").attr("checked", true);
        } else if (activeTab === "seller&buyer") {
            $("#tab2").attr("checked", true);
        } else if (activeTab === "localization") {
            $("#tab3").attr("checked", true);
        } else if (activeTab === "bulk_download") {
            $("#tab4").attr("checked", true);
        }
        // set active of Setting tab ended

        //Bulk input date validation
        var from_date;
        var to_date;
        var toCheck = 0;
        var fromCheck = 0;

        $("#Date-from").on("change", function () {
            from_date = Date.parse($(this).val());
            fromCheck = 1;
            if (toCheck && fromCheck) {
                if (to_date < from_date) {
                    alert("Input date should be less than or equal Date To");
                    $("#Date-from").val("");
                    fromCheck = 0;
                }
            }
        });

        $("#Date-to").on("change", function () {
            to_date = Date.parse($(this).val());
            toCheck = 1;
            if (toCheck && fromCheck) {
                if (to_date < from_date) {
                    alert(
                        "Input date should be greater than or equal Date From"
                    );
                    $("#Date-to").val("");
                    toCheck = 0;
                }
            }
        });

        $(function () {
            var tabs = $(".woo-invoice-sidebar-navbar-nav > li > a"); //grab tabs
            var contents = $(".woo-invoice-dashboard-content > li"); //grab contents

            if (sessionStorage.getItem("activeSidebarTab") != null) {
                var activeSidebarTab =
                    sessionStorage.getItem("activeSidebarTab");
                contents.hide(); //hide all contents
                tabs.removeClass("active"); //remove 'current' classes
                $(contents[activeSidebarTab]).show(); //show tab content that matches tab title index
                var activeTabSelector = $(
                    ".woo-invoice-sidebar-navbar-nav > li:eq( " +
                        activeSidebarTab +
                        " ) > a"
                );
                activeTabSelector.addClass("active");
                /*$(this).addClass('active'); //add current class on clicked tab title*/
                $(
                    ".woo-invoice-dashboard-sidebar .woo-invoice-sidebar-navbar-light"
                ).height($(contents[activeSidebarTab]).parent().height() - 23);
            } else {
                var initialContent = $(
                    ".woo-invoice-dashboard-content > li:eq(0)"
                );
                initialContent.css("display", "block"); //show tab content that matches tab title index
                var activeTabSelector = $(
                    ".woo-invoice-sidebar-navbar-nav > li:eq(0) > a"
                );
                activeTabSelector.addClass("active");
                $(
                    ".woo-invoice-dashboard-sidebar .woo-invoice-sidebar-navbar-light"
                ).height(initialContent.parent().height() - 23);
            }

            tabs.bind("click", function (e) {
                e.preventDefault();
                var tabIndex = $(this).parent().prevAll().length;
                contents.hide(); //hide all contents
                tabs.removeClass("active"); //remove 'current' classes
                $(contents[tabIndex]).show(); //show tab content that matches tab title index
                $(this).addClass("active"); //add current class on clicked tab title
                var selectedSidebarTab = $(this).parent().prevAll().length;
                sessionStorage.setItem("activeSidebarTab", selectedSidebarTab);
                $(
                    ".woo-invoice-dashboard-sidebar .woo-invoice-sidebar-navbar-light"
                ).height(contents.parent().height() - 23);
            });
        });
    });

    $(document).on("click", ".woo-invoice-template-selection", function (e) {
        e.preventDefault();
        let template = $(this).data("template");
        $("#winvoiceModalTemplates").modal("hide");
        $("body").removeClass(function (index, className) {
            return (className.match(/\S+-modal-open(^|\s)/g) || []).join(" ");
        });
        $('div[class*="-modal-backdrop"]').remove();
        $(this).find("img").removeClass("woo-invoice-template");
        $(this).find("img").removeClass("woo-invoice-disable-template");
        $(this).find("img").addClass("woo-invoice-slected-template");

        $(".woo-invoice-element-disable")
            .find("img")
            .addClass("woo-invoice-template");
        $(".woo-invoice-element-disable")
            .find("img")
            .removeClass("woo-invoice-disable-template");
        $(".woo-invoice-element-disable").css("z-index", "3333");
        $(".woo-invoice-element-disable")
            .siblings("div")
            .css("z-index", "1111");
        $(".woo-invoice-element-disable").siblings("a").css("z-index", "2222");

        $(this)
            .parent()
            .siblings()
            .find("img")
            .removeClass("woo-invoice-slected-template")
            .addClass("woo-invoice-template");
        $.ajax({
            url: woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2,
            type: "post",
            data: {
                _ajax_nonce: woo_invoice_ajax_obj_2.nonce,
                action: "wpifw_save_pdf_template",
                template: template,
            },
            success: function (response) {
                $(".woo-invoice-template-preview").attr(
                    "src",
                    response.data.location +
                        "/" +
                        response.data.template_name +
                        ".png"
                );
                $(".invoice_template_preiview_btn").attr(
                    "href",
                    response.data.location +
                        "/" +
                        response.data.template_name +
                        ".png"
                );
            },
        });
    });

    $(document).on("click", ".woo-invoice-element-disable", function (e) {
        e.preventDefault();
        $(this).children("img").removeClass("woo-invoice-template");
        $(this).children("img").addClass("woo-invoice-disable-template");
        $(this).css("z-index", "1111");
        $(this).siblings("div").css("z-index", "2222");
        $(this).siblings("a").css("z-index", "3333");
    });

    //Datepicker
    flatpickr(".woo-invoice-datepicker", {
        dateFormat: "n/j/Y",
        allowInput: true,
        onOpen: function (selectedDates, dateStr, instance) {
            instance.setDate(instance.input.value, false);
        },
    });

    /////////////////////////////////////
    // Display order meta for invoice.
    /////////////////////////////////////
    var metaOrderHTML = $(".woo_invoice_order_meta_html").first().clone();

    // clone a new input field for setting order mate.
    $(document).on("click", ".woo-invoice-add-order-meta", function (e) {
        e.preventDefault();

        if (
            $(".woo_invoice_order_meta > .woo_invoice_order_meta_html").length >
            0
        ) {
            display_challan_pro_notice_modal(
                "You can add more than 1 order meta in"
            );
        } else {
            $(this)
                .siblings(".woo_invoice_order_meta")
                .append(metaOrderHTML.clone());
        }
    });

    // delete order meta.
    $(document).on("click", ".woo-invoice-delete-order-meta", function (e) {
        e.preventDefault();
        $(this).parent().remove();
        if ($(".woo_invoice_order_meta_html").length === "1") {
            //$('.woo_invoice-add-order-meta .dashicons-plus-alt').css("margin-left","0px");
        }
    });
    ////////////////////////////////////////////
    //  End :  display order meta  for invoice.
    ////////////////////////////////////////////

    ////////////////////////////////////////
    // Display order item meta for invoice.
    ////////////////////////////////////////
    var orderItemMeta = $(".woo_invoice_order_item_meta_html").first().clone();
    // clone a new input field for setting order mate.
    $(document).on("click", ".woo-invoice-add-order-item-meta", function (e) {
        e.preventDefault();
        if (
            $(
                ".woo_invoice_order_item_meta > .woo_invoice_order_item_meta_html"
            ).length > 0
        ) {
            display_challan_pro_notice_modal(
                "You can add more than 1 order item meta in"
            );
        } else {
            $(this)
                .siblings(".woo_invoice_order_item_meta")
                .append(orderItemMeta.clone());
        }
    });
    // delete order meta.
    $(document).on(
        "click",
        ".woo-invoice-delete-order-item-meta",
        function (e) {
            e.preventDefault();
            $(this).parent().remove();
            if ($(".woo_invoice_order_item_meta_html").length === "1") {
                //$('.woo_invoice-add-order-meta .dashicons-plus-alt').css("margin-left","0px");
            }
        }
    );
    /////////////////////////////////////////////////
    //  End :  display order item meta for invoice.
    /////////////////////////////////////////////////

    /////////////////////////////////////
    // Display product meta for invoice.
    /////////////////////////////////////
    var productMeta = $(".woo_invoice_product_meta_html").first().clone();

    // clone a new input field for setting order mate.
    $(document).on("click", ".woo-invoice-product-add-meta", function (e) {
        e.preventDefault();
        if (
            $(".woo_invoice_product_meta > .woo_invoice_product_meta_html")
                .length > 0
        ) {
            display_challan_pro_notice_modal(
                "You can add more than 1 product meta in"
            );
        } else {
            $(this)
                .siblings(".woo_invoice_product_meta")
                .append(productMeta.clone());
        }
    });
    // delete order meta.
    $(document).on("click", ".woo-invoice-delete-product-meta", function (e) {
        e.preventDefault();
        $(this).parent().remove();
        if ($(".woo_invoice_product_meta_html").length === "1") {
            //$('.woo_invoice-add-order-meta .dashicons-plus-alt').css("margin-left","0px");
        }
    });
    ////////////////////////////////////////////
    //  End :  display order meta  for invoice.
    ////////////////////////////////////////////

    ///////////////////////////////////////////////
    // Alert for bulk csv file and shipping label.
    //////////////////////////////////////////////
    $(document).on("click", "#wpifw_submit_bulk_download", function (e) {
        $(this)
            .parent()
            .parent()
            .serializeArray()
            .map((field) => {
                if (
                    field.name == "wpifw_bulk_type" &&
                    field.value == "WPIFW_CSV"
                ) {
                    e.preventDefault();
                    display_challan_pro_notice_modal(
                        "To download bulk csv file you need"
                    );
                } else if (
                    field.name == "wpifw_bulk_type" &&
                    field.value == "WPIFW_SHIPPING_LABEL"
                ) {
                    e.preventDefault();
                    display_challan_pro_notice_modal(
                        "To download bulk shipping label you need"
                    );
                }
            });
    });
    ///////////////////////////////////////////////////////
    //  End :  Alert for bulk csv file and shipping label.
    ///////////////////////////////////////////////////////

    //////////////////////////////////////////////////
    //  Start :  Payment method select option notice.
    //////////////////////////////////////////////////
    function display_challan_pro_notice_for_select_option(
        selector_type,
        selector
    ) {
        $(document).on("change", `${selector_type}${selector}`, function (e) {
            e.preventDefault();
            if ($(this).val() !== "all") {
                $(this)
                    .parent()
                    .append(
                        `<p  class="woo_invoice_meta_html woo_invoice_col_3 woo_invoice_pro_notice">Notice: Challan Pro only.</p>`
                    );
                setTimeout(() => {
                    $(this).parent().children("p").hide("slow");
                    setTimeout(() => {
                        $(this).parent().children("p").remove();
                    }, 500);
                }, 2000);
            }
        });
    }
    display_challan_pro_notice_for_select_option(
        "#",
        "wpifw_bulk_download_order_payment_method"
    );
    display_challan_pro_notice_for_select_option(
        "#",
        "wpifw_bulk_download_order_status"
    );

    ////////////////////////////////////////////////
    //  End :  Payment method select option notice.
    ////////////////////////////////////////////////
    $(".wpifw_attr").selectize({
        plugins: ["remove_button"],
        render: {
            item: function (data, escape) {
                return (
                    '<div class="item wpifw_selector">' +
                    escape(data.text) +
                    "</div>"
                );
            },
        },
    });

    /////////////////////////////
    // Display csv file
    /////////////////////////////
    $(document).on("change", "#wpifw_bulk_type", function (e) {
        e.preventDefault();
        if ($(this).val() == "WPIFW_CSV") {
            $(".woo-invoice-add-csv-fields").show();
        } else {
            $(".woo-invoice-add-csv-fields").hide();
        }
    });

    //////////////////////////////////////////////////////////////
    // Show Product Column
    //////////////////////////////////////////////////////////////
    $('.wpifw_header').selectize(
        {
            plugins: ['remove_button'],
            maxItems: 4,
            render: {
                item: function (data, escape) {
                    let selectItem = data.value;
                    let freeHeader = [ 'price','total','quantity'];
                    let columnName = data.text.slice(0, -5);
                    if(freeHeader.includes(selectItem)){
                        $('.woo_invoice_pro_notice').hide();
                    }else{
                        $('.woo_invoice_pro_notice_name').text(columnName);
                        display_challan_pro_notice_modal(`To add <span style="font-weight: bold;">${columnName}</span> column you have to upgrade`);
                        $('.woo_invoice_pro_notice').show();
                    }

                    return '<div class="item wpifw_selector" disabled="disable">'+ escape(data.text) + '</div>';
                }
            },
            onInitialize: function () {
                var selectize = this;
                $.ajax(
                    {
                        url: woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2,
                        type: 'post',
                        data: {
                            _ajax_nonce: woo_invoice_ajax_obj_2.nonce,
                            action: "wpifw_get_product_column_show",
                        },
                        success: function (response) {
                            var selected_items = [].slice.call(response.data);
                            selectize.setValue(selected_items);
                        }
                    }
                );
            }
        }
    );

    //////////////////////////////////////////////////////////////
    // Save Product Column.
    //////////////////////////////////////////////////////////////
    $(document).on(
        'change', '.wpifw_header', function (e) {
            e.preventDefault();
            $.ajax(
                {
                    url: woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2,
                    type: 'post',
                    data: {
                        _ajax_nonce: woo_invoice_ajax_obj_2.nonce,
                        action: "wpifw_select_product_column",
                        columns: $(".wpifw_header").val()
                    },
                    success: function (response) {
                    }
                }
            );
        }
    );

    //////////////////////////////////////
    // Start : Show modal and close modal.
    //////////////////////////////////////
    // modal show function
    function display_challan_pro_notice_modal(content) {
        $(".woo-invoice-modal").show();
        $(".woo-invoice-modal-body p").html(
            `${content}  <a type='button' href='https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free' target='_blank'>Challan Pro</a>`
        );
    }

    // Close modal.
    $(".woo-invoice-modal .woo-invoice-modal-close").on("click", function () {
        $(".woo-invoice-modal").hide();
    });

    //////////////////////////////////////
    // End : Show modal and close modal.
    //////////////////////////////////////

    $(window).load(function () {
        $("#wpfooter #footer-thankyou").html(
            "If you like <strong><ins>Challan</ins></strong> please leave us a <a target='_blank' style='color:#f9b918' href='https://wordpress.org/support/view/plugin-reviews/webappick-pdf-invoice-for-woocommerce?rate=5#postform'>★★★★★</a> rating. A huge thank you in advance!"
        );
    });
})(jQuery);
