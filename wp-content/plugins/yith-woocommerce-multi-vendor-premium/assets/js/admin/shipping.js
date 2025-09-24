/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/typeof.js
function _typeof(obj) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) {
    return typeof obj;
  } : function (obj) {
    return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
  }, _typeof(obj);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js

function _toPrimitive(input, hint) {
  if (_typeof(input) !== "object" || input === null) return input;
  var prim = input[Symbol.toPrimitive];
  if (prim !== undefined) {
    var res = prim.call(input, hint || "default");
    if (_typeof(res) !== "object") return res;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (hint === "string" ? String : Number)(input);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js


function _toPropertyKey(arg) {
  var key = _toPrimitive(arg, "string");
  return _typeof(key) === "symbol" ? key : String(key);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/createClass.js

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor);
  }
}
function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  Object.defineProperty(Constructor, "prototype", {
    writable: false
  });
  return Constructor;
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/defineProperty.js

function _defineProperty(obj, key, value) {
  key = _toPropertyKey(key);
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }
  return obj;
}
;// CONCATENATED MODULE: ./assets/js/admin/src/ajax-request.js
/**
 * General AJAX request handler
 *
 * @package
 * @since 4.0.0
 * @author YITH
 */

var AjaxRequest = {
  xhr: false,
  block: function block(wrap) {
    if (wrap && wrap.length && typeof jQuery.fn.block !== 'undefined') {
      wrap.addClass('ajax-blocked');
      wrap.block({
        message: null,
        overlayCSS: {
          background: '#fff no-repeat center',
          opacity: 0.5,
          cursor: 'none'
        }
      });
    }
  },
  unblock: function unblock(wrap) {
    if (wrap && wrap.hasClass('ajax-blocked') && typeof jQuery.fn.block !== 'undefined') {
      wrap.unblock();
      wrap.removeClass('ajax-blocked');
    }
  },
  call: function call(data, wrap, type) {
    var self = this;
    type = typeof type !== 'undefined' ? type : 'GET';
    if (Array.isArray(data)) {
      data.push({
        name: 'action',
        value: yith_wcmv_ajax.ajaxAction
      });
      data.push({
        name: 'security',
        value: yith_wcmv_ajax.ajaxNonce
      });
      data.push({
        name: 'context',
        value: 'admin'
      });
    } else {
      data.action = yith_wcmv_ajax.ajaxAction;
      data.security = yith_wcmv_ajax.ajaxNonce;
      data.context = 'admin';
    }
    self.block(wrap);
    self.xhr = jQuery.ajax({
      url: yith_wcmv_ajax.ajaxUrl,
      data: data,
      type: type
    }).fail(function (response) {
      console.log(response);
      self.unblock(wrap);
    }).done(function (response) {
      self.unblock(wrap);
      self.xhr = false;
    });
    return self.xhr;
  },
  get: function get(data, wrap) {
    return this.call(data, wrap, 'GET');
  },
  post: function post(data, wrap) {
    return this.call(data, wrap, 'POST');
  },
  abort: function abort() {
    if (this.xhr) {
      this.xhr.abort();
    }
  }
};
/* harmony default export */ const ajax_request = (AjaxRequest);
;// CONCATENATED MODULE: ./assets/js/admin/src/fields-handler.js





/**
 * Common fields js handler
 *
 * @package YITH WooCommerce Multi Vendor
 * @since 4.0.0
 */
var FieldsHandler = /*#__PURE__*/function () {
  function FieldsHandler(container) {
    _classCallCheck(this, FieldsHandler);
    _defineProperty(this, "container", null);
    this.container = container;
  }
  _createClass(FieldsHandler, [{
    key: "init",
    value: function init() {
      if (!this.container.length) {
        return false;
      }

      // Init deps and fields.
      jQuery(document).trigger('yith_fields_init');
      jQuery(document.body).trigger('yith-plugin-fw-init-radio');
      jQuery(document).trigger('yith-add-box-button-toggle');

      // Init fields.
      this.initValue();
      this.initEnhancedSelect();
      // Init textarea editor.
      this.initTinyMCE();

      // Listen field change.
      this.container.on('change', '.field-required', {
        self: this
      }, this.listenRequired);
      this.container.on('change', '.email-validate', {
        self: this
      }, this.validateEmail);
      // Prevent submit on error.
      this.container.closest('form').on('submit', this.checkFormErrors.bind(this));
    }
  }, {
    key: "initTinyMCE",
    value: function initTinyMCE() {
      if (typeof tinyMCE == 'undefined' || typeof tinyMCEPreInit == 'undefined') {
        return;
      }
      this.container.find('.editor textarea').each(function () {
        // init editor
        var id = jQuery(this).attr('id'),
          mceInit = tinyMCEPreInit.mceInit,
          mceKey = Object.keys(mceInit)[0],
          mce = mceInit[mceKey],
          // get quick tags options
          qtInit = tinyMCEPreInit.qtInit,
          qtKey = Object.keys(qtInit)[0],
          qt = mceInit[qtKey];

        // change id
        mce.selector = id;
        mce.body_class = mce.body_class.replace(mceKey, id);
        qt.id = id;
        tinyMCE.init(mce);
        tinyMCE.execCommand('mceRemoveEditor', true, id);
        tinyMCE.execCommand('mceAddEditor', true, id);
        quicktags(qt);
        QTags._buttonsInit();
      });
    }
  }, {
    key: "initValue",
    value: function initValue() {
      // Init fields value.
      this.container.find(':input').each(function () {
        var _current$data;
        var current = jQuery(this),
          value = (_current$data = current.data('value')) !== null && _current$data !== void 0 ? _current$data : null;
        if (current.is(':radio') || 'hidden' === current.attr('type') || null === value) {
          // Radio is handled by plugin-fw
          return;
        }
        if (current.is(':checkbox')) {
          var checked = current.is(':checked');
          if (!checked && 'yes' === value || checked && 'yes' !== value) {
            current.click();
          }
        } else {
          current.val(value);
        }
      });
    }
  }, {
    key: "initEnhancedSelect",
    value: function initEnhancedSelect() {
      // AjaxRequest module and selectWoo plugin are requested.
      if (typeof yith_wcmv_ajax === 'undefined' || typeof jQuery.fn.selectWoo === 'undefined') {
        return false;
      }
      this.container.find('select.yith-wcmv-ajax-search').filter(':not(.initialized)').each(function () {
        var _select$data;
        // Set value if any on data.
        var select = jQuery(this),
          values = (_select$data = select.data('value')) !== null && _select$data !== void 0 ? _select$data : null;
        if (null !== values) {
          for (var option in values) {
            select.append(new Option(values[option], option, true, true));
          }
        }
        select.trigger('change');
        select.selectWoo({
          allowClear: true,
          placeholder: jQuery(this).data('placeholder'),
          minimumInputLength: '3',
          escapeMarkup: function escapeMarkup(m) {
            return m;
          },
          ajax: {
            url: yith_wcmv_ajax.ajaxUrl,
            dataType: 'json',
            delay: 1000,
            data: function data(params) {
              return {
                term: params.term,
                request: jQuery(this).data('action'),
                action: yith_wcmv_ajax.ajaxAction,
                security: yith_wcmv_ajax.ajaxNonce,
                context: 'admin'
              };
            },
            processResults: function processResults(results) {
              var terms = [];
              if (results.success) {
                jQuery.each(results.data, function (id, text) {
                  terms.push({
                    id: id,
                    text: text
                  });
                });
              }
              return {
                results: terms
              };
            },
            cache: true
          }
        }).addClass('initialized').on('select2:select', function (event) {
          select.find('option.value-placeholder').remove();
        }).on('select2:unselect', function (event) {
          var unselected = event.params.data.id;
          select.find('option[value="' + unselected + '"]').remove();
          if (!select.find('option').length) {
            select.append('<option value="" class="value-placeholder"></option>');
          }
        });
      });

      // simple select!
      this.container.find('select').filter(':not(.initialized)').each(function () {
        var _select$data2;
        var select = jQuery(this),
          value = (_select$data2 = select.data('value')) !== null && _select$data2 !== void 0 ? _select$data2 : null,
          placeholder = jQuery(this).find('option').filter('[value=""]'),
          args = {
            minimumResultsForSearch: 20 // at least 20 results must be displayed
          };

        // Add placeholder if there is an empty option.
        if (placeholder.length) {
          args.placeholder = {
            id: '',
            // the value of the option
            text: placeholder.text()
          };
          args.allowClear = true;
        }
        if (null !== value) {
          value += ''; // Make sure value is text.
          select.val(value.split(',')).change();
        }
        select.selectWoo(args).addClass('initialized');
      });
    }
  }, {
    key: "isValidEmail",
    value: function isValidEmail(email) {
      return email.toLowerCase().match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    }
  }, {
    key: "validateEmail",
    value: function validateEmail(event) {
      var self = event.data.self;
      var input = jQuery(this),
        value = input.val();
      if (value && !self.isValidEmail(value)) {
        var _yith_vendors;
        self.addFieldError((_yith_vendors = yith_vendors) === null || _yith_vendors === void 0 ? void 0 : _yith_vendors.emailFieldError, input.attr('name'));
      } else {
        self.resetFieldError(input.attr('name'));
      }
    }
  }, {
    key: "addFormError",
    value: function addFormError(error, wrap) {
      wrap.prepend('<div id="error-message">' + error + '</div>');
    }
  }, {
    key: "addFieldError",
    value: function addFieldError(error, field_name, wrap) {
      if (!wrap || !wrap.length) {
        wrap = this.container;
      }
      var field = wrap.find('[name="' + field_name + '"]');
      if (!field.length) {
        this.addFormError(error, wrap);
      }
      var error_wrap = field.next('.error-msg');
      // Add error class.
      field.addClass('field-error');
      // Add error.
      if (error_wrap.length) {
        error_wrap.html(error);
      } else {
        field.after('<span class="error-msg">' + error + '</span>');
      }
    }
  }, {
    key: "resetFormError",
    value: function resetFormError(wrap) {
      var _this = this;
      if (!wrap || !wrap.length) {
        wrap = this.container;
      }
      wrap.find('#error-message').remove();
      // Reset single fields.
      wrap.find('.field-error').each(function (i, field) {
        _this.resetFieldError(jQuery(field).attr('name'));
      });
    }
  }, {
    key: "resetFieldError",
    value: function resetFieldError(field_name) {
      if (field_name && this.container.find('[name="' + field_name + '"]')) {
        var field = this.container.find('[name="' + field_name + '"]');
        field.removeClass('field-error');
        field.next('.error-msg').remove();
      }
    }
  }, {
    key: "listenRequired",
    value: function listenRequired(event) {
      var self = event.data.self,
        field = jQuery(this),
        name = field.attr('name');
      if (!field.val()) {
        var _yith_vendors2;
        self.addFieldError((_yith_vendors2 = yith_vendors) === null || _yith_vendors2 === void 0 ? void 0 : _yith_vendors2.requiredFieldError, name);
      } else if (!field.hasClass('ajax-check')) {
        self.resetFieldError(name);
      }
    }
  }, {
    key: "isFormWithErrors",
    value: function isFormWithErrors() {
      var form = this.container.closest('form');
      form.find(':input').filter('.field-required').trigger('change');
      return !!form.find('#error-message, .field-error').length;
    }
  }, {
    key: "checkFormErrors",
    value: function checkFormErrors(event) {
      if (this.isFormWithErrors()) {
        event.preventDefault();
        jQuery('html').animate({
          scrollTop: this.container.find('#error-message, .field-error').first().offset().top - 100
        }, 1000);
      }
    }
  }]);
  return FieldsHandler;
}();

;// CONCATENATED MODULE: ./assets/js/admin/src/shipping.js



/**
 * Shipping module JavaScript
 *
 * @since 4.0.0
 * @author Francesco Licandro
 * @package YITH WooCommerce Multi Vendor
 */



var YITH_Shipping = /*#__PURE__*/function () {
  function YITH_Shipping() {
    _classCallCheck(this, YITH_Shipping);
    _defineProperty(this, "zoneTable", void 0);
    _defineProperty(this, "zoneAdd", void 0);
    _defineProperty(this, "zoneList", void 0);
    _defineProperty(this, "zoneListEmpty", void 0);
    _defineProperty(this, "buttonAdd", void 0);
    _defineProperty(this, "methodModal", void 0);
    _defineProperty(this, "methodModalTrigger", void 0);
    this.zoneTable = jQuery('#yith-wcmv-shipping-zones-wrapper');
    this.zoneAdd = this.zoneTable.find('.yith-wcmv-shipping-zone-new');
    this.zoneList = this.zoneTable.find('.yith-wcmv-shipping-zones');
    this.zoneListEmpty = this.zoneTable.find('.yith-wcmv-shipping-zones-empty');
    this.buttonAdd = this.zoneTable.find('.yith-wcmv-shipping-zones-add-button');
    this.methodModal = null;
    this.methodModalTrigger = null;
    this.init();
  }
  _createClass(YITH_Shipping, [{
    key: "init",
    value: function init() {
      if (!this.zoneTable.length || typeof yith_wcmv_shipping_general === 'undefined') {
        return false;
      }

      // Init field.
      this._initSelect();
      // Ini sortable
      this._initSortable();
      // Listener actions.
      this.zoneTable.on('click', '.yith-wcmv-shipping-zone-select-action', this.selectRegionsActions);
      this.zoneTable.on('click', '.yith-wcmv-shipping-zone-postcodes-toggle', this.showRegionsPostcode);
      this.zoneTable.on('yith-wcmv-shipping-list-updated', this.handleListUpdate.bind(this));

      // Handle Zone
      this.buttonAdd.on('click', this.addZone.bind(this));
      this.zoneTable.on('click', '.edit-zone a', {
        self: this
      }, this.editZone);
      this.zoneTable.on('click', '.trash-zone a', {
        self: this
      }, this.deleteZone);
      this.zoneTable.on('click', '.save-zone', {
        self: this
      }, this.saveZone);

      // Handle Shipping Method
      this.zoneTable.on('click', '.yith-wcmv-shipping-zone-settings__method-add', {
        self: this
      }, this.addMethod);
      this.zoneTable.on('click', '.edit-method a', {
        self: this
      }, this.editMethod);
      this.zoneTable.on('click', '.trash-method a', {
        self: this
      }, this.deleteMethod);
      jQuery(document).on('submit', '.yith-wcmv-shipping-method-modal form', {
        self: this
      }, this.modalSubmit);
    }

    // UTILS
  }, {
    key: "_zoneListIsEmpty",
    value: function _zoneListIsEmpty() {
      return !this.zoneList.children().length;
    }
  }, {
    key: "_switchButtonAdd",
    value: function _switchButtonAdd(active) {
      if (active) {
        this.buttonAdd.addClass('button-secondary').removeClass('button-primary').text(this.buttonAdd.data('closed_label'));
      } else {
        this.buttonAdd.addClass('button-primary').removeClass('button-secondary').text(this.buttonAdd.data('opened_label'));
      }
    }
  }, {
    key: "_initSelect",
    value: function _initSelect(container) {
      if (typeof jQuery.fn.selectWoo === 'undefined') {
        return false;
      }
      if (typeof container === 'undefined') {
        container = this.zoneTable;
      }
      jQuery(container).find('select:not(.initialized)').each(function () {
        var _jQuery$data;
        var args = {
            minimumResultsForSearch: 20 // at least 20 results must be displayed
          },
          value = (_jQuery$data = jQuery(this).data('value')) !== null && _jQuery$data !== void 0 ? _jQuery$data : null;
        if (jQuery(this).hasClass('yith-wcmv-shipping-zone-region-select')) {
          args.data = yith_wcmv_shipping_general.shippingRegions;
          args.escapeMarkup = function (m) {
            return m;
          };
        }
        jQuery(this).selectWoo(args).addClass('initialized');
        if (value) {
          jQuery(this).val(value).change();
        }
      });
    }
  }, {
    key: "_initSortable",
    value: function _initSortable() {
      var self = this,
        items = this.zoneList.find('.yith-wcmv-shipping-zone'),
        initialized = this.zoneList.hasClass('ui-sortable');
      if (items.length > 1) {
        if (initialized) {
          this.zoneList.sortable('refresh');
        } else {
          this.zoneList.sortable({
            handle: '.drag-zone',
            cursor: 'move',
            scrollSensitivity: 10,
            tolerance: 'pointer',
            axis: 'y',
            stop: function stop(event, ui) {
              var order = [];
              self.zoneList.find('.yith-wcmv-shipping-zone').each(function () {
                order.push(jQuery(this).data('zone_id'));
              });
              ajax_request.post({
                request: yith_wcmv_shipping_general.orderZonesAction,
                order: order
              });
            }
          });
        }
      }
    }

    // LISTENER
  }, {
    key: "handleListUpdate",
    value: function handleListUpdate() {
      // Toggle empty list visibility.
      if (this._zoneListIsEmpty()) {
        this.zoneListEmpty.show();
      } else {
        this.zoneListEmpty.hide();
      }
      this._initSelect();
      this._initSortable();
    }
  }, {
    key: "showRegionsPostcode",
    value: function showRegionsPostcode(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      jQuery(this).siblings('.yith-wcmv-shipping-zone-postcodes').toggle();
    }
  }, {
    key: "selectRegionsActions",
    value: function selectRegionsActions(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var action = jQuery(this).data('action'),
        select = jQuery(this).closest('.yith-wcmv-shipping-zone-settings__field').find('.yith-wcmv-shipping-zone-region-select');
      if ('remove-all' === action) {
        select.val('').change();
      } else if ('select-all' === action) {
        select.val('continent:all').change();
      }
    }

    // HANDLER ZONES
  }, {
    key: "addZone",
    value: function addZone(event) {
      var _this = this;
      event.preventDefault();
      event.stopImmediatePropagation();
      var template = wp.template('yith-wcmv-shipping-zones-new');
      if (this.zoneAdd.is(':visible')) {
        this._switchButtonAdd(false);
        this.zoneAdd.slideToggle('400', function () {
          _this.zoneAdd.html('');
        });
      } else {
        this._switchButtonAdd(true);
        this.zoneAdd.html(template({}));
        this._initSelect();
        // At the end, show it!
        this.zoneAdd.slideToggle();
      }
    }
  }, {
    key: "editZone",
    value: function editZone(event) {
      event.preventDefault();
      var wrap = jQuery(this).closest('.yith-wcmv-shipping-zone');
      wrap.toggleClass('editing');
      // Handle container class.
      if (!wrap.siblings('.editing').length) {
        event.data.self.zoneList.toggleClass('sortable-disabled');
      }
      // Show edit form.
      wrap.find('.yith-wcmv-shipping-zone-settings').slideToggle();
    }
  }, {
    key: "deleteZone",
    value: function deleteZone(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var self = event.data.self,
        zone = jQuery(this).closest('.yith-wcmv-shipping-zone'),
        zone_id = zone.data('zone_id');
      yith.ui.confirm({
        title: yith_wcmv_shipping_general.removeZoneTitle,
        message: yith_wcmv_shipping_general.removeZoneMessage,
        confirmButtonType: 'delete',
        confirmButton: yith_wcmv_shipping_general.removeButtonLabel,
        closeAfterConfirm: true,
        onConfirm: function onConfirm() {
          zone.remove();
          self.zoneTable.trigger('yith-wcmv-shipping-list-updated');
          ajax_request.post({
            request: yith_wcmv_shipping_general.removeZoneAction,
            zone_id: zone_id
          });
        }
      });
    }
  }, {
    key: "saveZone",
    value: function saveZone(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var self = event.data.self,
        zone = jQuery(this).closest('.yith-wcmv-shipping-zone, .yith-wcmv-shipping-zone-new'),
        zone_id = zone.data('zone_id');
      var data = zone.find(':input').serializeArray().filter(function (field) {
        return field.value.length;
      });
      if (!data.length) {
        return false;
      }
      data.push({
        name: "request",
        value: yith_wcmv_shipping_general.saveZoneAction
      }, {
        name: "zone_id",
        value: zone_id
      });
      ajax_request.post(data, zone).done(function (res) {
        var _res$data;
        if (res !== null && res !== void 0 && res.success && res !== null && res !== void 0 && (_res$data = res.data) !== null && _res$data !== void 0 && _res$data.html) {
          var _res$data2;
          var to_replace = self.zoneTable.find('.yith-wcmv-shipping-zone[data-zone_id="' + (res === null || res === void 0 ? void 0 : (_res$data2 = res.data) === null || _res$data2 === void 0 ? void 0 : _res$data2.zone_id) + '"]');
          if (to_replace.length) {
            to_replace.replaceWith(res.data.html);
          } else {
            // Reset form.
            self._switchButtonAdd(false);
            self.zoneAdd.html('').hide();
            // Add zone.
            self.zoneList.append(res.data.html);
          }
          self.zoneTable.trigger('yith-wcmv-shipping-list-updated');
        }
      });
    }

    // HANDLER METHODS
  }, {
    key: "openModal",
    value: function openModal(title, content, width) {
      var self = this;
      self.methodModal = yith.ui.modal({
        width: width !== null && width !== void 0 ? width : 500,
        classes: {
          wrap: 'yith-wcmv-shipping-method-modal',
          content: 'yith-plugin-ui'
        },
        title: title,
        content: content,
        onCreate: function onCreate() {
          var fields = new FieldsHandler(jQuery('.yith-wcmv-shipping-method-modal'));
          fields.init();
        }
      });
    }
  }, {
    key: "addMethod",
    value: function addMethod(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var self = event.data.self,
        template = wp.template('yith-wcmv-shipping-zones-new-method');
      self.openModal(yith_wcmv_shipping_general.shippingMethodTitle, template({
        zone_id: jQuery(this).data('zone_id')
      }));
    }
  }, {
    key: "editMethod",
    value: function editMethod(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var self = event.data.self,
        trigger = jQuery(this).parent(),
        method_id = trigger.attr('data-method_id'),
        template = wp.template('yith-wcmv-shipping-zones-settings-method');
      var data = JSON.parse(jQuery('#yith-wcmv-shipping-method-data-' + method_id).val());
      // Add keys to data.
      data.zone_id = trigger.attr('data-zone_id');
      data.method_id = method_id;
      self.openModal(yith_wcmv_shipping_general.shippingEditMethodTitle.replace('{{method_title}}', data.method_title), template(data), 600);
    }
  }, {
    key: "deleteMethod",
    value: function deleteMethod(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var method = jQuery(this).closest('.yith-wcmv-shipping-zone-settings__method'),
        trigger = jQuery(this).parent(),
        method_id = trigger.attr('data-method_id'),
        zone_id = trigger.attr('data-zone_id');
      yith.ui.confirm({
        title: yith_wcmv_shipping_general.removeMethodTitle,
        message: yith_wcmv_shipping_general.removeMethodMessage,
        confirmButtonType: 'delete',
        confirmButton: yith_wcmv_shipping_general.removeButtonLabel,
        closeAfterConfirm: true,
        onConfirm: function onConfirm() {
          method.remove();
          ajax_request.post({
            request: yith_wcmv_shipping_general.removeShippingMethod,
            method_id: method_id,
            zone_id: zone_id
          });
        }
      });
    }
  }, {
    key: "modalSubmit",
    value: function modalSubmit(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var self = event.data.self,
        form = jQuery(this),
        data = form.serializeArray();
      ajax_request.post(data, jQuery('.yith-plugin-fw__modal__main')).done(function (res) {
        var _res$data3;
        if (res !== null && res !== void 0 && res.success && res !== null && res !== void 0 && (_res$data3 = res.data) !== null && _res$data3 !== void 0 && _res$data3.html) {
          var _res$data4, _res$data5;
          var zone = self.zoneTable.find('.yith-wcmv-shipping-zone[data-zone_id="' + (res === null || res === void 0 ? void 0 : (_res$data4 = res.data) === null || _res$data4 === void 0 ? void 0 : _res$data4.zone_id) + '"]');
          if (!zone.length && self.zoneAdd.is(':visible')) {
            zone = self.zoneAdd;
          }
          var method = zone.find('.yith-wcmv-shipping-zone-settings__method[data-method_id="' + (res === null || res === void 0 ? void 0 : (_res$data5 = res.data) === null || _res$data5 === void 0 ? void 0 : _res$data5.method_id) + '"]');
          if (method.length) {
            method.replaceWith(res.data.html);
          } else {
            zone.find('.yith-wcmv-shipping-zone-settings__methods ul').prepend(res.data.html);
          }

          // Close modal
          self.methodModal.close();
        }
      });
    }
  }]);
  return YITH_Shipping;
}();
var shippingTable = new YITH_Shipping();
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=shipping.js.map