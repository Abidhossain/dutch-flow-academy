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
;// CONCATENATED MODULE: ./assets/js/frontend/src/report-abuse.js



/**
 * Report abuse modal handler
 *
 * @package YITH WooCommerce Multi Vendor
 * @since 4.0.0
 * @author YITH
 */
var VendorReportAbuse = /*#__PURE__*/function () {
  function VendorReportAbuse(trigger) {
    _classCallCheck(this, VendorReportAbuse);
    _defineProperty(this, "modal", void 0);
    _defineProperty(this, "modalWrap", void 0);
    this.initModal();
    if (this.modal.length) {
      this.modal.on('click', '.yith-wpv-abuse-report-modal-close', this.closeModal.bind(this));
      this.modal.on('submit', 'form', this.submitModal.bind(this));
      trigger.on('click', this.openModal.bind(this));
    }
  }
  _createClass(VendorReportAbuse, [{
    key: "initModal",
    value: function initModal() {
      // Granted backward compatibility with old templates.
      this.modal = jQuery(document).find('#yith-wpv-abuse-report');
      if (this.modal.length) {
        this.wrap = this.modal.closest('#yith-wpv-abuse-report-modal');

        // If wrap doesn't exists, add it!
        if (!this.wrap.length) {
          this.modal.wrap('<div id="yith-wpv-abuse-report-modal"></div>');
          this.wrap = this.modal.closest('#yith-wpv-abuse-report-modal');
        }
      }
    }
  }, {
    key: "openModal",
    value: function openModal(event) {
      event.preventDefault();
      // Load the template.
      this.setModalContent('yith-wcmv-abuse-report-content');
      // Show modal.
      this.wrap.show();
      this.modal.fadeIn();
    }
  }, {
    key: "setModalContent",
    value: function setModalContent(template) {
      var content = wp.template(template);
      if (typeof content !== 'undefined') {
        this.modal.html(content());
      }

      // Add close trigger if missing. Added with JS for backward compatibility.
      if (!this.modal.find('.yith-wpv-abuse-report-modal-close').length) {
        this.modal.prepend('<span class="yith-wpv-abuse-report-modal-close"></span>');
      }
    }
  }, {
    key: "addModalError",
    value: function addModalError(error) {
      var error_wrap = this.modal.find('.yith-wpv-abuse-report-modal-error');
      if (!error_wrap.length) {
        this.modal.find('.yith-wpv-abuse-report-title').after('<div class="yith-wpv-abuse-report-modal-error">' + error + '</div>');
      } else {
        error_wrap.html(error);
      }
    }
  }, {
    key: "submitModal",
    value: function submitModal(event) {
      event.preventDefault();
      var self = this;
      var form = self.modal.find('form'),
        data = form.serializeArray();
      data.push({
        name: 'context',
        value: 'frontend'
      });
      jQuery.ajax({
        url: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'send_report_abuse'),
        data: data,
        method: 'POST',
        dataType: 'json',
        beforeSend: function beforeSend() {
          if (typeof jQuery.fn.block !== 'undefined') {
            self.modal.block({
              message: null,
              overlayCSS: {
                background: '#fff no-repeat center',
                opacity: 0.5,
                cursor: 'none'
              }
            });
          }
        },
        error: function error(jqXHR, textStatus, errorThrown) {
          console.log(textStatus, errorThrown);
        },
        success: function success(response) {
          if (response !== null && response !== void 0 && response.success) {
            // Load the template.
            self.setModalContent('yith-wcmv-abuse-report-sent');
            setTimeout(function () {
              self.closeModal();
            }, 5000);
          } else {
            self.addModalError(response === null || response === void 0 ? void 0 : response.data);
          }
        },
        complete: function complete() {
          if (typeof jQuery.fn.block !== 'undefined') {
            self.modal.unblock();
          }
        }
      });
    }
  }, {
    key: "closeModal",
    value: function closeModal(event) {
      this.wrap.hide();
      this.modal.hide().html('');
    }
  }]);
  return VendorReportAbuse;
}();
jQuery(document).ready(function () {
  var trigger = jQuery(document).find('#yith-wpv-abuse');
  if (trigger.length) {
    new VendorReportAbuse(trigger);
  }
});
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=report-abuse.js.map