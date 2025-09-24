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
;// CONCATENATED MODULE: ./assets/js/admin/src/commissions.js



/**
 * COMMISSION JAVASCRIPT HANDLER
 *
 * @package
 * @since 4.0.0
 */


var YITH_Commissions = /*#__PURE__*/function () {
  // Template properties.

  // Store select status changed in commission modal,

  function YITH_Commissions() {
    _classCallCheck(this, YITH_Commissions);
    _defineProperty(this, "modal", void 0);
    _defineProperty(this, "templateHeader", void 0);
    _defineProperty(this, "templateContent", void 0);
    _defineProperty(this, "templateFooter", void 0);
    _defineProperty(this, "statusChanged", void 0);
    this.templateHeader = wp.template('yith-wcmv-modal-commission-header');
    this.templateContent = wp.template('yith-wcmv-modal-commission-content');
    this.templateFooter = wp.template('yith-wcmv-modal-commission-footer');
    this.init();
  }
  _createClass(YITH_Commissions, [{
    key: "init",
    value: function init() {
      jQuery(document).on('click', '.commission-details:not(.disabled)', {
        self: this
      }, this.view);
      jQuery(document).on('change', '.commission-details-modal #commission-status', this.statusChange.bind(this));
      jQuery(document).on('click', '.commission-details-modal .save-commission', {
        self: this
      }, this.save);

      // Handle gateway panel.
      jQuery(document).on('change', '.enable-gateway-trigger .on_off', this.enableGateway);
      jQuery(document).on('submit', 'form.gateway-options-form', this.gatewayFormSubmit);
      jQuery(document).on('click', '.list-item.has-options .name > *', this.gatewaySlide);
    }
  }, {
    key: "openModal",
    value: function openModal(data) {
      if (typeof this.templateHeader === 'undefined' || typeof this.templateContent === 'undefined' || typeof this.templateFooter === 'undefined') {
        console.error('Error loading commissions templates');
        return false;
      }
      if (typeof data === 'undefined') {
        return false;
      }
      this.modal = yith.ui.modal({
        title: this.templateHeader(data),
        content: this.templateContent(data),
        footer: this.templateFooter(data),
        classes: {
          wrap: 'commission-details-modal'
        }
      });
    }
  }, {
    key: "view",
    value: function view(event) {
      event.preventDefault();
      var self = event.data.self;
      var trigger = jQuery(this),
        commissionID = trigger.data('commission_id'),
        commissionData = trigger.data('commission-data');
      if (typeof commissionData === 'undefined') {
        trigger.addClass('disabled');
        // Call must be unique. Abort the current one if processing
        ajax_request.abort();
        ajax_request.call({
          request: 'commission_details',
          commission_id: commissionID
        }, jQuery(this).closest('td')).done(function (response) {
          trigger.removeClass('disabled');
          if (response.success) {
            trigger.data('commission-data', response.data);
            self.openModal(response.data);
          }
        });
      } else {
        self.openModal(commissionData);
      }
      return false;
    }
  }, {
    key: "statusChange",
    value: function statusChange() {
      // Just set a flag if the commission status select change its value.
      this.statusChanged = true;
    }
  }, {
    key: "save",
    value: function save(event) {
      event.preventDefault();
      var self = event.data.self;
      if (true === self.statusChanged) {
        var status = jQuery('[name="commission-status"]').val(),
          commission_id = jQuery(this).attr('data-commission_id');
        ajax_request.post({
          request: 'commission_change_status',
          commission_id: commission_id,
          status: status
        }, jQuery('.commission-details-modal .yith-plugin-fw__modal__main')).done(function (res) {
          window.location.reload();
        });
      } else {
        // Just close the modal.
        self.modal.close();
      }
    }
  }, {
    key: "enableGateway",
    value: function enableGateway(event) {
      event.preventDefault();
      var trigger = jQuery(this),
        status = trigger.is(':checked');
      trigger.attr('disabled', 'disabled');
      ajax_request.call({
        request: 'switch_gateway_enabled',
        gateway_id: trigger.data('gateway_id'),
        gateway_enabled: status ? 'yes' : 'no'
      }, trigger.closest('.list-item')).done(function (response) {
        trigger.removeAttr('disabled');
        if (!response.success) {
          trigger.prop('checked', !status);
        }
      });
      return false;
    }
  }, {
    key: "gatewayFormSubmit",
    value: function gatewayFormSubmit(event) {
      event.preventDefault();
      var formData = jQuery(this).serializeArray();
      formData.push({
        name: 'request',
        value: 'save_gateway_options'
      });
      ajax_request.call(formData, jQuery(this).closest('.list-item'), 'POST');
      return false;
    }
  }, {
    key: "gatewaySlide",
    value: function gatewaySlide(event) {
      var wrap = jQuery(this).closest('.list-item');
      wrap.toggleClass('opened');
      wrap.find('.options').slideToggle();
    }
  }]);
  return YITH_Commissions;
}();
var commissions = new YITH_Commissions();
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=commissions.js.map