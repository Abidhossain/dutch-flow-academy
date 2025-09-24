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
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js
function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }
  return self;
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js
function _getPrototypeOf(o) {
  _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/superPropBase.js

function _superPropBase(object, property) {
  while (!Object.prototype.hasOwnProperty.call(object, property)) {
    object = _getPrototypeOf(object);
    if (object === null) break;
  }
  return object;
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/get.js

function _get() {
  if (typeof Reflect !== "undefined" && Reflect.get) {
    _get = Reflect.get.bind();
  } else {
    _get = function _get(target, property, receiver) {
      var base = _superPropBase(target, property);
      if (!base) return;
      var desc = Object.getOwnPropertyDescriptor(base, property);
      if (desc.get) {
        return desc.get.call(arguments.length < 3 ? target : receiver);
      }
      return desc.value;
    };
  }
  return _get.apply(this, arguments);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js
function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };
  return _setPrototypeOf(o, p);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/inherits.js

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }
  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  Object.defineProperty(subClass, "prototype", {
    writable: false
  });
  if (superClass) _setPrototypeOf(subClass, superClass);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js


function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  } else if (call !== void 0) {
    throw new TypeError("Derived constructors may only return object or undefined");
  }
  return _assertThisInitialized(self);
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

;// CONCATENATED MODULE: ./assets/js/admin/src/vendor-fields-handler.js








function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
/**
 * Vendor Fields js helper
 *
 * @package YITH WooCommerce Multi Vendor
 * @since 4.0.0
 */



var VendorFieldsHandler = /*#__PURE__*/function (_FieldsHandler) {
  _inherits(VendorFieldsHandler, _FieldsHandler);
  var _super = _createSuper(VendorFieldsHandler);
  function VendorFieldsHandler() {
    var _this;
    _classCallCheck(this, VendorFieldsHandler);
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    _this = _super.call.apply(_super, [this].concat(args));
    _defineProperty(_assertThisInitialized(_this), "states", null);
    return _this;
  }
  _createClass(VendorFieldsHandler, [{
    key: "init",
    value: function init() {
      if (typeof yith_wcmv_vendors !== 'undefined') {
        /* State/Country select boxes */
        this.states = JSON.parse(yith_wcmv_vendors.countries.replace(/&quot;/g, '"'));
      }

      // Init special image upload value.
      this.container.find('.vendor-image-upload-input').each(this.initImageValue.bind(this));
      // Event listener.
      this.container.on('change', '.country-field', {
        self: this
      }, this.changeCountry);
      jQuery('.country-field').change(); // init.
      // Handle image upload
      this.container.on('click', '.upload_vendor_image_button', {
        self: this
      }, this.uploadImage);
      this.container.on('click', '.remove_vendor_image_button', {
        self: this
      }, this.removeImage);
      _get(_getPrototypeOf(VendorFieldsHandler.prototype), "init", this).call(this);
      this.container.on('keyup', '.ajax-check', {
        self: this
      }, this.validateField);
      this.initSlugField();
    }
  }, {
    key: "addImage",
    value: function addImage(container, id, url) {
      container.find('input[type="hidden"]').val(id);
      container.css('background-image', 'url(' + url + ')');
      container.find('.upload_vendor_image_button').hide();
      container.find('.remove_vendor_image_button').show();
    }
  }, {
    key: "removeImage",
    value: function removeImage(event) {
      event.preventDefault();
      var container = jQuery(this).closest('.vendor-image-upload');
      container.find('input[type="hidden"]').val('');
      container.css('background-image', '');
      container.find('.upload_vendor_image_button').show();
      jQuery(this).hide();
      return false;
    }
  }, {
    key: "changeCountry",
    value: function changeCountry(event) {
      var self = event.data.self;
      // Prevent if we don't have the data.
      if (self.states === null) {
        return;
      }
      var $this = jQuery(this),
        country = $this.val(),
        $state = $this.closest('form').find('.state-field'),
        $parent = $state.parent(),
        stateValue = $state.val(),
        input_name = $state.attr('name'),
        input_id = $state.attr('id'),
        placeholder = $state.attr('placeholder'),
        $newstate;

      // Remove the previous DOM element.
      $parent.show().find('.select2-container').remove();
      if (!jQuery.isEmptyObject(self.states[country])) {
        var state = self.states[country],
          $defaultOption = jQuery('<option value=""></option>').text(yith_wcmv_vendors.i18nSelectStateText);
        $newstate = jQuery('<select></select>').prop('id', input_id).prop('name', input_name).prop('placeholder', placeholder).addClass('state-field').append($defaultOption);
        jQuery.each(state, function (index) {
          var $option = jQuery('<option></option>').prop('value', index).text(state[index]);
          if (index === stateValue) {
            $option.prop('selected');
          }
          $newstate.append($option);
        });
        $newstate.val(stateValue);
        $state.replaceWith($newstate);
        $newstate.show().selectWoo().hide().trigger('change');
      } else {
        $newstate = jQuery('<input type="text" />').prop('id', input_id).prop('name', input_name).prop('placeholder', placeholder).addClass('state-field').val(stateValue);
        $state.replaceWith($newstate);
      }
    }
  }, {
    key: "uploadImage",
    value: function uploadImage(event) {
      event.preventDefault();
      var self = event.data.self,
        container = jQuery(this).closest('.vendor-image-upload');

      // Create the media frame.
      var file_frame = wp.media.frames.downloadable_file = wp.media({
        title: yith_wcmv_vendors.uploadFrameTitle,
        button: {
          text: yith_wcmv_vendors.uploadFrameButtonText
        },
        multiple: false
      });

      // When an image is selected, run a callback.
      file_frame.on('select', function () {
        var attachment = file_frame.state().get('selection').first().toJSON();
        self.addImage(container, attachment.id, attachment.sizes.full.url);
      });

      // Finally, open the modal.
      file_frame.open();
    }
  }, {
    key: "initImageValue",
    value: function initImageValue(index, input) {
      var _current$data;
      var current = jQuery(input),
        value = (_current$data = current.data('value')) !== null && _current$data !== void 0 ? _current$data : null;
      if (null === value) {
        return;
      }
      for (var key in value) {
        this.addImage(current.parent(), key, value[key]);
      }
      current.removeAttr('data-value');
    }
  }, {
    key: "initSlugField",
    value: function initSlugField() {
      var slug = this.container.find('input#slug'),
        desc = slug.closest('.vendor-field').find('.description');
      desc.data('text', desc.text());
      slug.on('keyup', function () {
        var val = jQuery(this).val();
        if (val) {
          val = val.toLowerCase().replace(/[^0-9a-z-]+/, '-');
          desc.text(desc.data('text').replace('%yith_shop_vendor%', val));
          jQuery(this).val(val);
        } else {
          desc.text('');
        }
      }).keyup();
    }
  }, {
    key: "validateField",
    value: function validateField(event) {
      var self = event.data.self;
      // Call must be unique. Abort the current one if processing
      ajax_request.abort();
      var input = jQuery(this),
        value = input.val();

      // Reset field.
      input.removeClass('error success');
      if (!value.length) {
        return false;
      }

      // Add loading icon.
      input.addClass('loading');
      ajax_request.get({
        request: input.data('action'),
        value: value,
        vendor_id: self.container.find('input[name="vendor_id"]').val()
      }).fail(function () {
        input.removeClass('loading');
      }).done(function (res) {
        input.removeClass('loading');
        if (res.success) {
          input.addClass('success');
          self.resetFieldError(input.attr('name'));
        } else {
          input.addClass('error');
          self.addFieldError(res.data.error, input.attr('name'));
        }
      });
    }
  }]);
  return VendorFieldsHandler;
}(FieldsHandler);

;// CONCATENATED MODULE: ./assets/js/admin/src/vendors-dashboard.js
/**
 * Vendor dashboard JS
 *
 * @package YITH WooCommerce Multi Vendor
 * @since 4.0.0
 */


var fields_container = jQuery('.vendor-fields-container');
if (fields_container.length) {
  var fields = new VendorFieldsHandler(fields_container);
  fields.init();
}
if (jQuery(document.body).hasClass('post-type-product')) {
  // Remove add new product button.
  if (yith_wcmv_vendors.hideImportProducts) {
    jQuery('.woocommerce-BlankState').find('.woocommerce-BlankState-cta.button').not('.button-primary').remove();
  }
  if (yith_wcmv_vendors.hideFeaturedProduct) {
    jQuery('#_featured').add("[for=\"_featured\"]").remove();
  }
}
if ((jQuery(document.body).hasClass('post-type-shop_order') || jQuery(document.body).hasClass('yith-wcfm-section-product_orders')) && !jQuery(document.body).hasClass('vendor_quote_management')) {
  var _yith_wcmv_vendors$or, _yith_wcmv_vendors$or2, _yith_wcmv_vendors$or3;
  jQuery('.wc-order-edit-line-item').remove();
  jQuery('.wc-order-edit-line-item-actions').remove();
  jQuery('a.delete-order-tax').remove();
  if ('no' === ((_yith_wcmv_vendors$or = yith_wcmv_vendors.orderDataToShow) === null || _yith_wcmv_vendors$or === void 0 ? void 0 : _yith_wcmv_vendors$or.customer)) {
    var elem = jQuery('#order_data').find('.wc-customer-user');
    elem.replaceWith('<input type="hidden" name="customer_user" value="' + elem.find('select').val() + '"/>');
    jQuery('.wc-customer-search').remove();
  }
  if ('no' === ((_yith_wcmv_vendors$or2 = yith_wcmv_vendors.orderDataToShow) === null || _yith_wcmv_vendors$or2 === void 0 ? void 0 : _yith_wcmv_vendors$or2.payment)) {
    jQuery('#order_data').find('.order_number').remove();
  }
  if ('no' === ((_yith_wcmv_vendors$or3 = yith_wcmv_vendors.orderDataToShow) === null || _yith_wcmv_vendors$or3 === void 0 ? void 0 : _yith_wcmv_vendors$or3.address)) {
    jQuery("#order_data .order_data_column:nth-child(2)").add("#order_data .order_data_column:nth-child(3)").remove();
  }
}
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=vendors-dashboard.js.map