/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 942:
/***/ ((module, exports) => {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
	Copyright (c) 2018 Jed Watson.
	Licensed under the MIT License (MIT), see
	http://jedwatson.github.io/classnames
*/
/* global define */

(function () {
	'use strict';

	var hasOwn = {}.hasOwnProperty;

	function classNames () {
		var classes = '';

		for (var i = 0; i < arguments.length; i++) {
			var arg = arguments[i];
			if (arg) {
				classes = appendClass(classes, parseValue(arg));
			}
		}

		return classes;
	}

	function parseValue (arg) {
		if (typeof arg === 'string' || typeof arg === 'number') {
			return arg;
		}

		if (typeof arg !== 'object') {
			return '';
		}

		if (Array.isArray(arg)) {
			return classNames.apply(null, arg);
		}

		if (arg.toString !== Object.prototype.toString && !arg.toString.toString().includes('[native code]')) {
			return arg.toString();
		}

		var classes = '';

		for (var key in arg) {
			if (hasOwn.call(arg, key) && arg[key]) {
				classes = appendClass(classes, key);
			}
		}

		return classes;
	}

	function appendClass (value, newClass) {
		if (!newClass) {
			return value;
		}
	
		if (value) {
			return value + ' ' + newClass;
		}
	
		return value + newClass;
	}

	if ( true && module.exports) {
		classNames.default = classNames;
		module.exports = classNames;
	} else if (true) {
		// register as 'classnames', consistent with npm package name
		!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () {
			return classNames;
		}).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	} else {}
}());


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
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
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

;// CONCATENATED MODULE: external ["wc","blocksCheckout"]
const external_wc_blocksCheckout_namespaceObject = window["wc"]["blocksCheckout"];
// EXTERNAL MODULE: ./node_modules/classnames/index.js
var classnames = __webpack_require__(942);
var classnames_default = /*#__PURE__*/__webpack_require__.n(classnames);
;// CONCATENATED MODULE: external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// CONCATENATED MODULE: external ["wp","data"]
const external_wp_data_namespaceObject = window["wp"]["data"];
;// CONCATENATED MODULE: external ["wp","hooks"]
const external_wp_hooks_namespaceObject = window["wp"]["hooks"];
;// CONCATENATED MODULE: external ["wp","compose"]
const external_wp_compose_namespaceObject = window["wp"]["compose"];
;// CONCATENATED MODULE: external ["wc","wcBlocksData"]
const external_wc_wcBlocksData_namespaceObject = window["wc"]["wcBlocksData"];
;// CONCATENATED MODULE: ./assets/js/blocks/src/grand-total/block.js
/**
 * External dependencies
 */







var Block = function Block(_ref) {
  var instanceId = _ref.instanceId,
    className = _ref.className;
  var extensionData = (0,external_wp_data_namespaceObject.useSelect)(function (select) {
      var store = select(external_wc_wcBlocksData_namespaceObject.CART_STORE_KEY),
        cartData = store.getCartData(),
        extensionData = cartData === null || cartData === void 0 ? void 0 : cartData.extensions;
      return extensionData === null || extensionData === void 0 ? void 0 : extensionData['yith\\deposits'];
    }),
    _ref2 = extensionData !== null && extensionData !== void 0 ? extensionData : {},
    hasDeposits = _ref2.has_deposits,
    grandTotals = _ref2.grand_totals,
    description = _ref2.expiration_note;
  if (!hasDeposits || !grandTotals) {
    return;
  }
  var registerCheckoutFilters = window.wc.blocksCheckout.registerCheckoutFilters,
    getCurrencyFromPriceResponse = window.wc.priceFormat.getCurrencyFromPriceResponse,
    grandTotal = grandTotals.total,
    balanceTotal = grandTotals.balance,
    balanceShippingTotal = grandTotals.balance_shipping,
    currency = getCurrencyFromPriceResponse(grandTotal),
    depositLabel = (0,external_wp_hooks_namespaceObject.applyFilters)('yith_wcdp_deposit_total_label', (0,external_wp_i18n_namespaceObject._x)('Deposit due today', '[FRONTEND] Grand total block', 'yith-woocommerce-deposits-and-down-payments')),
    balanceLabel = (0,external_wp_hooks_namespaceObject.applyFilters)('yith_wcdp_balance_total_label', (0,external_wp_i18n_namespaceObject._x)('Balance subtotal', '[FRONTEND] Grand total block', 'yith-woocommerce-deposits-and-down-payments')),
    balanceShippingLabel = (0,external_wp_hooks_namespaceObject.applyFilters)('yith_wcdp_balance_total_label', (0,external_wp_i18n_namespaceObject._x)('Balance shipping', '[FRONTEND] Grand total block', 'yith-woocommerce-deposits-and-down-payments')),
    grandTotalLabel = (0,external_wp_hooks_namespaceObject.applyFilters)('yith_wcdp_grand_total_label', (0,external_wp_i18n_namespaceObject._x)('Grand total', '[FRONTEND] Grand total block', 'yith-woocommerce-deposits-and-down-payments'));
  registerCheckoutFilters('yith\\deposits', {
    totalLabel: function totalLabel(label) {
      return hasDeposits ? depositLabel : label;
    }
  });
  return /*#__PURE__*/React.createElement(external_wc_blocksCheckout_namespaceObject.TotalsWrapper, {
    className: classnames_default()('yith-wcdp-grand-total-block__wrapper', className)
  }, /*#__PURE__*/React.createElement("div", {
    className: "yith-wcdp-grand-total-block__items"
  }, /*#__PURE__*/React.createElement(external_wc_blocksCheckout_namespaceObject.TotalsItem, {
    className: classnames_default()('wc-block-components-totals-footer-item', 'yith-wcdp-grand-total-block__items__subtotal', 'yith-wcdp-grand-total-block-line-item'),
    currency: currency,
    label: balanceLabel,
    value: parseInt(balanceTotal.price, 10)
  }), !!parseInt(balanceShippingTotal.price, 10) ? /*#__PURE__*/React.createElement(external_wc_blocksCheckout_namespaceObject.TotalsItem, {
    className: classnames_default()('wc-block-components-totals-footer-item', 'yith-wcdp-grand-total-block__items__shipping', 'yith-wcdp-grand-total-block-line-item'),
    currency: currency,
    label: balanceShippingLabel,
    value: parseInt(balanceShippingTotal.price, 10)
  }) : null), /*#__PURE__*/React.createElement(external_wc_blocksCheckout_namespaceObject.TotalsItem, {
    className: classnames_default()('wc-block-components-totals-footer-item', 'yith-wcdp-grand-total-block__total'),
    currency: currency,
    label: grandTotalLabel,
    value: parseInt(grandTotal.price, 10)
  }), description ? /*#__PURE__*/React.createElement("div", {
    className: classnames_default()('wc-block-components-panel', 'wc-block-components-totals-item__description', 'yith-wcdp-grand-total-block__description'),
    dangerouslySetInnerHTML: {
      __html: description
    }
  }) : null);
};
/* harmony default export */ const block = ((0,external_wp_compose_namespaceObject.withInstanceId)(Block));
;// CONCATENATED MODULE: ./assets/js/blocks/src/grand-total/block.json
const grand_total_block_namespaceObject = /*#__PURE__*/JSON.parse('{"apiVersion":2,"name":"yith/yith-wcdp-grand-total","version":"2.16.0","title":"YITH Deposits \\"Grand Total\\"","category":"yith","description":"Shows the \\"Grand Total\\" line, for orders containing deposits.","supports":{"html":false,"align":false,"multiple":false,"reusable":false},"parent":["woocommerce/checkout-totals-block","woocommerce/cart-totals-block"],"textdomain":"yith-woocommerce-deposits-and-down-payments","editorStyle":"yith-wcdp"}');
;// CONCATENATED MODULE: ./assets/js/blocks/src/grand-total/frontend.js
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */


(0,external_wc_blocksCheckout_namespaceObject.registerCheckoutBlock)({
  metadata: grand_total_block_namespaceObject,
  component: block
});
})();

var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=yith-wcdp-grand-total-frontend.bundle.js.map