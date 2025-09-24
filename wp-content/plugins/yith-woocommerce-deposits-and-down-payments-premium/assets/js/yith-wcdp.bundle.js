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

;// CONCATENATED MODULE: ./assets/js/src/globals.js


/* global jQuery yith_wcdp yith */

// these constants will be wrapped inside webpack closure, to prevent collisions
var _yith_wcdp;
var $ = jQuery,
  $document = $(document),
  $body = $('body'),
  block = function block($el) {
    if ('undefined' === typeof $.fn.block) {
      return false;
    }
    try {
      $el.block({
        message: null,
        overlayCSS: {
          background: '#fff',
          opacity: 0.6
        }
      });
      return $el;
    } catch (e) {
      return false;
    }
  },
  unblock = function unblock($el) {
    if ('undefined' === typeof $.fn.unblock) {
      return false;
    }
    try {
      $el.unblock();
    } catch (e) {
      return false;
    }
  },
  labels = (_yith_wcdp = yith_wcdp) === null || _yith_wcdp === void 0 ? void 0 : _yith_wcdp.labels;

;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/typeof.js
function _typeof(o) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, _typeof(o);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js

function toPrimitive(t, r) {
  if ("object" != _typeof(t) || !t) return t;
  var e = t[Symbol.toPrimitive];
  if (void 0 !== e) {
    var i = e.call(t, r || "default");
    if ("object" != _typeof(i)) return i;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return ("string" === r ? String : Number)(t);
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js


function toPropertyKey(t) {
  var i = toPrimitive(t, "string");
  return "symbol" == _typeof(i) ? i : i + "";
}
;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/createClass.js

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, toPropertyKey(descriptor.key), descriptor);
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
  key = toPropertyKey(key);
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
;// CONCATENATED MODULE: ./assets/js/src/modules/yith-wcdp-deposit-form.js


/* globals yith_wcdp, yith, accounting */




var YITH_WCDP_Deposit_Form = /*#__PURE__*/function () {
  function YITH_WCDP_Deposit_Form() {
    _classCallCheck(this, YITH_WCDP_Deposit_Form);
    _defineProperty(this, "xhr", null);
    _defineProperty(this, "$form", null);
    _defineProperty(this, "$depositContainer", null);
    _defineProperty(this, "$depositOptions", null);
    _defineProperty(this, "$variationAddToCart", null);
    var form = $('form.cart');
    if (!form.length) {
      return;
    }
    this.init();
  }
  _createClass(YITH_WCDP_Deposit_Form, [{
    key: "getDepositPreferences",
    value: function getDepositPreferences() {
      if (!this.$depositOptions.length) {
        this.initDom();
      }
      if (!this.$depositOptions.length) {
        return false;
      }
      return this.$depositOptions.data();
    }
  }, {
    key: "getDepositValue",
    value: function getDepositValue(price) {
      var depositPreferences = this.getDepositPreferences();
      if (!depositPreferences) {
        return price;
      }
      var depositPrice;
      if ('amount' === depositPreferences.depositType && !!depositPreferences.depositAmount) {
        depositPrice = Math.min(price, depositPreferences.depositAmount);
      } else if ('rate' === depositPreferences.depositType && !!depositPreferences.depositRate) {
        depositPrice = price * parseFloat(depositPreferences.depositRate) / 100;
        depositPrice = Math.min(price, depositPrice);
      } else {
        depositPrice = price;
      }
      return depositPrice;
    }
  }, {
    key: "formatPrice",
    value: function formatPrice(price) {
      return accounting.formatMoney(price, {
        symbol: yith_wcdp.currency_format.symbol,
        decimal: yith_wcdp.currency_format.decimal,
        thousand: yith_wcdp.currency_format.thousand,
        precision: yith_wcdp.currency_format.precision,
        format: yith_wcdp.currency_format.format
      });
    }
  }, {
    key: "init",
    value: function init() {
      this.initDom();
      this.initVariations();
      this.initActions();
    }
  }, {
    key: "initDom",
    value: function initDom() {
      var _this$$form, _this$$depositContain, _this$$form2;
      this.$form = $('form.cart');
      this.$depositContainer = (_this$$form = this.$form) === null || _this$$form === void 0 ? void 0 : _this$$form.find('#yith-wcdp-add-deposit-to-cart');
      this.$depositOptions = (_this$$depositContain = this.$depositContainer) === null || _this$$depositContain === void 0 ? void 0 : _this$$depositContain.find('.yith-wcdp-single-add-to-cart-fields');
      this.$variationAddToCart = (_this$$form2 = this.$form) === null || _this$$form2 === void 0 ? void 0 : _this$$form2.find('.woocommerce-variation-add-to-cart');
    }
  }, {
    key: "initVariations",
    value: function initVariations() {
      var _this = this;
      if (!this.$form.length || !this.$form.hasClass('variations_form')) {
        return;
      }
      this.$form.on('found_variation', function (ev, variation) {
        return _this.onFoundVariation(variation);
      }).on('reset_data', function () {
        return _this.removeTemplate();
      });
    }
  }, {
    key: "initActions",
    value: function initActions() {
      var _this2 = this;
      // Event Tickets, Product Addons, Composite compatibilities
      $document.on('yith_wcevti_price_refreshed yith_wapo_product_price_updated yith_wcp_price_updated', function (ev, fullPrice) {
        return _this2.updateTotals(fullPrice);
      });

      // Dynamic compatibility
      $document.on('ywdpd_price_html_updated', function (ev, formattedPrice, fullPrice) {
        if (!fullPrice) {
          return;
        }
        _this2.updateTotals(fullPrice);
      });

      // Bundle compatibility
      $document.on('yith_wcpb_ajax_update_price_request', function (ev, response) {
        if (!response || !(response !== null && response !== void 0 && response.price)) {
          return;
        }
        _this2.updateTotals(response.price);
      });
    }
  }, {
    key: "onFoundVariation",
    value: function onFoundVariation(variation) {
      var _this3 = this;
      this.doTemplateUpdate(variation).then(function () {
        $document.trigger('yith_wcdp_updated_deposit_form', _this3.$depositOptions);
      });
    }
  }, {
    key: "doTemplateUpdate",
    value: function doTemplateUpdate(variation) {
      var _this4 = this;
      if (yith_wcdp.ajax_variations) {
        return this.updateTemplateViaAjax(variation);
      }
      return new Promise(function (resolve) {
        if ('undefined' !== typeof variation.add_deposit_to_cart) {
          _this4.updateTemplateViaVariation(variation);
        } else if (deposit_options.length) {
          _this4.updateTotals(variation.display_price);
        }
        resolve();
      });
    }
  }, {
    key: "updateTemplateViaAjax",
    value: function updateTemplateViaAjax(variation) {
      var _this5 = this,
        _yith_wcdp,
        _yith_wcdp$actions,
        _yith_wcdp$actions$ge,
        _yith_wcdp2,
        _yith_wcdp2$actions,
        _yith_wcdp2$actions$g;
      this.xhr = $.ajax({
        beforeSend: function beforeSend() {
          if (_this5.xhr != null) {
            _this5.xhr.abort();
          }
          _this5.hideTemplate();
          block(_this5.$form);
        },
        complete: function complete() {
          return unblock(_this5.$form);
        },
        data: {
          variation_id: variation === null || variation === void 0 ? void 0 : variation.variation_id,
          variation_attr: this.$form.find('.variations select').serializeArray().reduce(function (a, v) {
            a[v.name] = v.value;
            return a;
          }, {}),
          action: (_yith_wcdp = yith_wcdp) === null || _yith_wcdp === void 0 ? void 0 : (_yith_wcdp$actions = _yith_wcdp.actions) === null || _yith_wcdp$actions === void 0 ? void 0 : (_yith_wcdp$actions$ge = _yith_wcdp$actions.get_add_deposit) === null || _yith_wcdp$actions$ge === void 0 ? void 0 : _yith_wcdp$actions$ge.name,
          _wpnonce: (_yith_wcdp2 = yith_wcdp) === null || _yith_wcdp2 === void 0 ? void 0 : (_yith_wcdp2$actions = _yith_wcdp2.actions) === null || _yith_wcdp2$actions === void 0 ? void 0 : (_yith_wcdp2$actions$g = _yith_wcdp2$actions.get_add_deposit) === null || _yith_wcdp2$actions$g === void 0 ? void 0 : _yith_wcdp2$actions$g.nonce
        },
        dataType: 'html',
        method: 'POST',
        success: function success(template) {
          return _this5.updateTemplate(template);
        },
        url: yith_wcdp.ajax_url
      });
      return this.xhr;
    }
  }, {
    key: "updateTemplateViaVariation",
    value: function updateTemplateViaVariation(variation) {
      this.updateTemplate(variation.add_deposit_to_cart);
    }
  }, {
    key: "hideTemplate",
    value: function hideTemplate() {
      if (!this.$depositContainer.length) {
        return;
      }
      this.$depositContainer.hide();
    }
  }, {
    key: "updateTemplate",
    value: function updateTemplate(newTemplate) {
      this.removeTemplate();
      this.$variationAddToCart.before(newTemplate);
      this.initDom();
    }
  }, {
    key: "removeTemplate",
    value: function removeTemplate() {
      if (!this.$depositContainer.length) {
        return;
      }
      this.$depositContainer.remove();
    }
  }, {
    key: "updateTotals",
    value: function updateTotals(fullPrice) {
      var formattedFullPrice = this.formatPrice(fullPrice),
        depositPrice = this.getDepositValue(fullPrice),
        formattedDepositPrice = this.formatPrice(depositPrice);
      this.$depositOptions.find('.full-price').html(formattedFullPrice);
      this.$depositOptions.find('.deposit-price').html(formattedDepositPrice);
    }
  }]);
  return YITH_WCDP_Deposit_Form;
}();

;// CONCATENATED MODULE: ./assets/js/src/modules/yith-wcdp-modal.js


/* globals yith_wcdp, yith */




var YITH_WCDP_Modal = /*#__PURE__*/function () {
  function YITH_WCDP_Modal($opener, args) {
    _classCallCheck(this, YITH_WCDP_Modal);
    // modal opener
    _defineProperty(this, "opener", null);
    // target of the open event
    _defineProperty(this, "$target", null);
    // modal object
    _defineProperty(this, "$modal", null);
    // modal content
    _defineProperty(this, "$content", null);
    if (!$opener) {
      return;
    }
    this.opener = $opener;
    this.args = $.extend({
      title: false,
      shouldOpen: false,
      template: false,
      onOpen: false,
      onClose: false
    }, args || {});
    this.init();
  }
  _createClass(YITH_WCDP_Modal, [{
    key: "init",
    value: function init() {
      var _this = this;
      $(document).off('click', this.opener).on('click', this.opener, function (ev) {
        _this.$target = $(ev.target);
        if (!_this.shouldOpen()) {
          return;
        }
        ev.preventDefault();
        _this.onOpen();
      });
    }

    // events handling
  }, {
    key: "shouldOpen",
    value: function shouldOpen() {
      var _this$args;
      if ('function' === typeof ((_this$args = this.args) === null || _this$args === void 0 ? void 0 : _this$args.shouldOpen)) {
        return this.args.shouldOpen.call(this);
      }
      return true;
    }
  }, {
    key: "onOpen",
    value: function onOpen() {
      var _this$args2, _this$$content;
      var template = ((_this$args2 = this.args) === null || _this$args2 === void 0 ? void 0 : _this$args2.template) || '',
        $content = null;
      if ('function' === typeof template) {
        template = template.call(this);
      }
      if (!((_this$$content = this.$content) !== null && _this$$content !== void 0 && _this$$content.length)) {
        var _template;
        if (this.$target.data('modal')) {
          $content = $("#".concat(this.$target.data('modal'))).detach();
        } else if (!template) {
          return;
        } else if ('string' === typeof template) {
          $content = $(template).detach();
        } else if ('function' === typeof template) {
          $content = template().detach();
        } else if ((_template = template) !== null && _template !== void 0 && _template.lenght) {
          $content = template.detach();
        }
        this.$content = $content;
      }
      this.maybeOpenModal(this.$content);
    }
  }, {
    key: "onClose",
    value: function onClose() {
      this.maybeCloseModal();
    }
  }, {
    key: "maybeBuildModal",
    value: function maybeBuildModal() {
      var _this$$modal,
        _this$args3,
        _this2 = this;
      if ((_this$$modal = this.$modal) !== null && _this$$modal !== void 0 && _this$$modal.length) {
        return this.$modal;
      }
      var $modal = $('<div/>', {
          "class": 'yith-wcdp-modal'
        }),
        $contentContainer = $('<div/>', {
          "class": 'content pretty-scrollbar'
        }),
        $closeButton = $('<a/>', {
          "class": 'close-button main-close-button',
          html: '&times;',
          role: 'button',
          href: '#'
        });
      this.$modal = $modal;
      $modal.append($contentContainer).append($closeButton);
      if ((_this$args3 = this.args) !== null && _this$args3 !== void 0 && _this$args3.title) {
        var $title = $('<div/>', {
          "class": 'title',
          html: "<h3>".concat(this.args.title, "</h3>")
        });
        $modal.prepend($title);
      }
      $modal.on('click', '.close-button', function (ev) {
        ev.preventDefault();
        _this2.onClose();
      });
      $body.append($modal);
      return this.$modal;
    }
  }, {
    key: "maybeDestroyModal",
    value: function maybeDestroyModal() {
      var _this$$modal2;
      if (!((_this$$modal2 = this.$modal) !== null && _this$$modal2 !== void 0 && _this$$modal2.length)) {
        return;
      }
      this.$modal.remove();
    }
  }, {
    key: "maybeOpenModal",
    value: function maybeOpenModal(content) {
      var _this$$modal3,
        _this3 = this;
      if (!((_this$$modal3 = this.$modal) !== null && _this$$modal3 !== void 0 && _this$$modal3.length)) {
        this.maybeBuildModal();
      }
      if (this.$modal.hasClass('open')) {
        return;
      }
      this.$modal.find('.content').append(content).end().fadeIn(function () {
        var _this3$args;
        _this3.$modal.addClass('open');
        if ('function' === typeof ((_this3$args = _this3.args) === null || _this3$args === void 0 ? void 0 : _this3$args.onOpen)) {
          var _this3$args2;
          (_this3$args2 = _this3.args) === null || _this3$args2 === void 0 ? void 0 : _this3$args2.onOpen.call(_this3);
        }
      });
      $body.addClass('yith-wcdp-open-modal');
    }
  }, {
    key: "maybeCloseModal",
    value: function maybeCloseModal() {
      var _this$$modal4,
        _this4 = this;
      if (!((_this$$modal4 = this.$modal) !== null && _this$$modal4 !== void 0 && _this$$modal4.length)) {
        this.maybeBuildModal();
      }
      if (!this.$modal.hasClass('open')) {
        return;
      }
      this.$modal.fadeOut(function () {
        var _this4$args;
        _this4.$modal.removeClass('open');
        $body.removeClass('yith-wcdp-open-modal');
        if ('function' === typeof ((_this4$args = _this4.args) === null || _this4$args === void 0 ? void 0 : _this4$args.onClose)) {
          var _this4$args2;
          (_this4$args2 = _this4.args) === null || _this4$args2 === void 0 ? void 0 : _this4$args2.onClose.call(_this4);
        }
      });
    }
  }]);
  return YITH_WCDP_Modal;
}();

;// CONCATENATED MODULE: ./assets/js/src/modules/yith-wcdp-buttons-with-submenu.js


/* globals yith_wcdp, yith */




var YITH_WCDP_Buttons_With_Submenu = /*#__PURE__*/function () {
  function YITH_WCDP_Buttons_With_Submenu($container) {
    _classCallCheck(this, YITH_WCDP_Buttons_With_Submenu);
    _defineProperty(this, "$container", null);
    _defineProperty(this, "$buttons", null);
    _defineProperty(this, "$openers", null);
    if (!$container.length) {
      return;
    }
    this.$container = $container;
    this.init();
  }
  _createClass(YITH_WCDP_Buttons_With_Submenu, [{
    key: "init",
    value: function init() {
      this.$buttons = this.$container.find('.button-with-submenu');
      this.$openers = this.$buttons.find('a.submenu-opener');
      this.initOpeners();
      this.initBackdrop();
    }
  }, {
    key: "initOpeners",
    value: function initOpeners() {
      var self = this;
      this.$openers.not('.initialized').each(function () {
        var $opener = $(this);
        $opener.on('click', function (ev) {
          ev.stopPropagation();
          self.toggleMenu.call(self, $opener);
        }).addClass('initialized');
      });
    }
  }, {
    key: "initBackdrop",
    value: function initBackdrop() {
      $(document).on('click', this.closeAll.bind(this));
    }
  }, {
    key: "toggleMenu",
    value: function toggleMenu($opener) {
      var $button = $opener.parent('.button-with-submenu'),
        opened = $button.hasClass('opened');
      if (opened) {
        this.close($button);
      } else {
        this.closeAll();
        this.open($button);
      }
    }
  }, {
    key: "open",
    value: function open($button) {
      $button.addClass('opened');
    }
  }, {
    key: "close",
    value: function close($button) {
      $button.removeClass('opened');
    }
  }, {
    key: "closeAll",
    value: function closeAll() {
      this.$buttons.removeClass('opened');
    }
  }]);
  return YITH_WCDP_Buttons_With_Submenu;
}();

;// CONCATENATED MODULE: ./assets/js/src/yith-wcdp.js


/* globals yith_wcdp, yith, accounting */




jQuery(function () {
  // add deposit to cart from.
  new YITH_WCDP_Deposit_Form();

  // deposits details form.
  var $depositsDetails = $('#yith_wcdp_deposits_details');
  if ($depositsDetails.length) {
    new YITH_WCDP_Buttons_With_Submenu($depositsDetails);
  }

  // balances details modal
  var initBalanceDetailsModal = function initBalanceDetailsModal() {
    new YITH_WCDP_Modal('.deposit-expiration-modal-opener', {
      title: yith_wcdp.labels.deposit_expiration_modal_title
    });
  };
  $document.on('updated_checkout', initBalanceDetailsModal);
  initBalanceDetailsModal();
});
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=yith-wcdp.bundle.js.map