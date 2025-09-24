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

;// CONCATENATED MODULE: ./assets/js/admin/src/announcements.js


/**
 * ANNOUNCEMENTS JAVASCRIPT HANDLER
 *
 * @package
 * @since 4.0.0
 */



var YITH_Announcements = /*#__PURE__*/function () {
  function YITH_Announcements() {
    _classCallCheck(this, YITH_Announcements);
    this.init();
  }
  _createClass(YITH_Announcements, [{
    key: "init",
    value: function init() {
      jQuery(document).on('click', 'a.page-title-action', {
        announcement: this
      }, this.create);
      jQuery(document).on('click', 'button.save-announcement-modal', this.save);
      // Edit.
      jQuery(document).on('click', '.yith-plugin-fw__action-button--edit-action, a.row-title', {
        announcement: this
      }, this.edit);
      // Enable.
      jQuery(document).on('change', 'input[name="_announcement_enabled"]', this.active);
    }
  }, {
    key: "openModal",
    value: function openModal(data) {
      var template = wp.template('yith-wcmv-modal-announcement'),
        templateButtons = wp.template('yith-wcmv-modal-announcement-buttons');
      yith.ui.modal({
        width: 700,
        closeSelector: '.close-announcement-modal',
        classes: {
          wrap: 'announcement-modal',
          content: 'yith-plugin-ui'
        },
        title: yith_wcmv_announcements.modalTitle,
        content: template(data),
        footer: templateButtons({}),
        onCreate: function onCreate() {
          var fields = new FieldsHandler(jQuery('form.announcement-modal-form'));
          fields.init();
        }
      });
    }
  }, {
    key: "create",
    value: function create(event) {
      event.preventDefault();
      event.data.announcement.openModal(yith_wcmv_announcements.defaultModalValues);
    }
  }, {
    key: "edit",
    value: function edit(event) {
      event.preventDefault();
      var handler = event.data.announcement;
      var row = jQuery(this).closest('tr'),
        announcement_id = row.find('[name="post[]"]').val();
      if (!announcement_id) {
        return false;
      }

      // Call must be unique. Abort the current one if processing
      ajax_request.abort();
      if (row.data('announcement-data')) {
        handler.openModal(row.data('announcement-data'));
      } else {
        ajax_request.call({
          request: 'get_announcement_data',
          announcement_id: announcement_id
        }, jQuery(this).closest('td')).done(function (response) {
          if (response.success) {
            row.data('announcement-data', response.data);
            handler.openModal(response.data);
          }
        });
      }
    }
  }, {
    key: "active",
    value: function active(event) {
      event.preventDefault();
      var active = jQuery(this).is(':checked') ? 'yes' : 'no',
        announcement_id = jQuery(this).closest('tr').find('[name="post[]"]').val();
      ajax_request.call({
        request: 'announcement_active_switch',
        announcement_id: announcement_id,
        active: active
      }, jQuery(this).closest('td'), 'POST');
    }
  }, {
    key: "save",
    value: function save(event) {
      event.preventDefault();
      jQuery('.announcement-modal-form').submit();
    }
  }]);
  return YITH_Announcements;
}();
if (typeof yith_wcmv_announcements !== 'undefined') {
  var announcement = new YITH_Announcements();
}
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=announcements.js.map