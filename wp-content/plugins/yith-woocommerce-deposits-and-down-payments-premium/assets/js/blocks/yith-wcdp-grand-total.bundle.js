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

;// CONCATENATED MODULE: external ["wp","blocks"]
const external_wp_blocks_namespaceObject = window["wp"]["blocks"];
;// CONCATENATED MODULE: external ["wp","blockEditor"]
const external_wp_blockEditor_namespaceObject = window["wp"]["blockEditor"];
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
;// CONCATENATED MODULE: external ["wc","blocksCheckout"]
const external_wc_blocksCheckout_namespaceObject = window["wc"]["blocksCheckout"];
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
;// CONCATENATED MODULE: ./assets/js/blocks/src/grand-total/edit.js
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */

var Edit = function Edit(_ref) {
  var attributes = _ref.attributes;
  var className = attributes.className;
  var blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)();
  return /*#__PURE__*/React.createElement("div", blockProps, /*#__PURE__*/React.createElement(block, {
    className: className
  }));
};
var Save = function Save() {
  return /*#__PURE__*/React.createElement("div", external_wp_blockEditor_namespaceObject.useBlockProps.save());
};
;// CONCATENATED MODULE: ./assets/js/blocks/src/grand-total/block.json
const grand_total_block_namespaceObject = /*#__PURE__*/JSON.parse('{"apiVersion":2,"name":"yith/yith-wcdp-grand-total","version":"2.16.0","title":"YITH Deposits \\"Grand Total\\"","category":"yith","description":"Shows the \\"Grand Total\\" line, for orders containing deposits.","supports":{"html":false,"align":false,"multiple":false,"reusable":false},"parent":["woocommerce/checkout-totals-block","woocommerce/cart-totals-block"],"textdomain":"yith-woocommerce-deposits-and-down-payments","editorStyle":"yith-wcdp"}');
;// CONCATENATED MODULE: external ["wp","url"]
const external_wp_url_namespaceObject = window["wp"]["url"];
;// CONCATENATED MODULE: ./plugin-fw/includes/builders/gutenberg/src/common/ajaxFetch.js
/**
 * Ajax Fetch
 */

/**
 * WordPress dependencies
 */


/**
 * Check status of ajax call
 * @param response
 * @returns {*}
 */
function ajaxCheckStatus(response) {
  if (response.status >= 200 && response.status < 300) {
    return response;
  }
  throw response;
}

/**
 * Parse the response of the ajax call
 * @param response
 * @returns {*}
 */
function parseResponse(response) {
  return response.json ? response.json() : response.text();
}

/**
 * Fetch using WordPress Ajax
 *
 * @param {object} data The data to use in the ajax call.
 * @param {string} url The ajax URL.
 * @returns {Promise<Response>}
 */
var ajaxFetch = function ajaxFetch(data) {
  var _data$security;
  var url = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : yithGutenberg.ajaxurl;
  data.security = (_data$security = data.security) !== null && _data$security !== void 0 ? _data$security : yithGutenberg.ajaxNonce;
  url = addQueryArgs(url, data);
  return fetch(url).then(ajaxCheckStatus).then(parseResponse);
};
;// CONCATENATED MODULE: ./plugin-fw/includes/builders/gutenberg/src/common/icons.js
/**
 * SVG Icons
 */

/**
 * The YITH Logo Icon
 * @type {JSX.Element}
 */
var yith_icon = /*#__PURE__*/React.createElement("svg", {
  viewBox: "0 0 22 22",
  xmlns: "http://www.w3.org/2000/svg",
  width: "22",
  height: "22",
  role: "img",
  "aria-hidden": "true",
  focusable: "false"
}, /*#__PURE__*/React.createElement("path", {
  width: "22",
  height: "22",
  d: "M 18.24 7.628 C 17.291 8.284 16.076 8.971 14.587 9.688 C 15.344 7.186 15.765 4.851 15.849 2.684 C 15.912 0.939 15.133 0.045 13.514 0.003 C 11.558 -0.06 10.275 1.033 9.665 3.284 C 10.007 3.137 10.359 3.063 10.723 3.063 C 11.021 3.063 11.267 3.184 11.459 3.426 C 11.651 3.668 11.736 3.947 11.715 4.262 C 11.695 5.082 11.276 5.961 10.46 6.896 C 9.644 7.833 8.918 8.3 8.282 8.3 C 7.837 8.3 7.625 7.922 7.646 7.165 C 7.667 6.765 7.804 5.955 8.056 4.735 C 8.287 3.579 8.403 2.801 8.403 2.401 C 8.403 1.707 8.224 1.144 7.867 0.713 C 7.509 0.282 6.994 0.098 6.321 0.161 C 5.858 0.203 5.175 0.624 4.27 1.422 C 3.596 2.035 2.923 2.644 2.25 3.254 L 2.976 4.106 C 3.564 3.664 3.922 3.443 4.048 3.443 C 4.448 3.443 4.637 3.717 4.617 4.263 C 4.617 4.306 4.427 4.968 4.049 6.251 C 3.671 7.534 3.471 8.491 3.449 9.122 C 3.407 9.985 3.565 10.647 3.924 11.109 C 4.367 11.677 5.106 11.919 6.142 11.835 C 7.366 11.751 8.591 11.298 9.816 10.479 C 10.323 10.142 10.808 9.753 11.273 9.311 C 11.105 10.153 10.905 10.868 10.673 11.457 C 8.402 12.487 6.762 13.37 5.752 14.107 C 4.321 15.137 3.554 16.241 3.449 17.419 C 3.259 19.459 4.29 20.479 6.541 20.479 C 8.055 20.479 9.517 19.554 10.926 17.703 C 12.125 16.126 13.166 14.022 14.049 11.394 C 15.578 10.635 16.87 9.892 17.928 9.164 C 17.894 9.409 18.319 7.308 18.24 7.628 Z  M 7.393 16.095 C 7.056 16.095 6.898 15.947 6.919 15.653 C 6.961 15.106 7.908 14.38 9.759 13.476 C 8.791 15.221 8.002 16.095 7.393 16.095 Z"
}));
;// CONCATENATED MODULE: external "lodash"
const external_lodash_namespaceObject = window["lodash"];
;// CONCATENATED MODULE: ./plugin-fw/includes/builders/gutenberg/src/common/checkForDeps.js
/**
 * Check for dependencies
 *
 * @param {object} attributeArgs Attribute arguments.
 * @param {object} attributes The attributes.
 * @returns {boolean}
 */


var checkForSingleDep = function checkForSingleDep(attributes, dep, controlType) {
  var show = true;
  if (dep && dep.id && 'value' in dep) {
    var depValue = dep.value;
    if (['toggle', 'checkbox'].includes(controlType)) {
      depValue = true === depValue || 'yes' === depValue || 1 === depValue;
    }
    depValue = _.isArray(depValue) ? depValue : [depValue];
    show = typeof attributes[dep.id] !== 'undefined' && depValue.includes(attributes[dep.id]);
  }
  return show;
};
var checkForDeps_checkForDeps = function checkForDeps(attributeArgs, attributes) {
  var controlType = attributeArgs.controlType;
  var show = true;
  if (attributeArgs.deps) {
    if (_.isArray(attributeArgs.deps)) {
      for (var i in attributeArgs.deps) {
        var singleDep = attributeArgs.deps[i];
        show = checkForSingleDep(attributes, singleDep, controlType);
        if (!show) {
          break;
        }
      }
    } else {
      show = checkForSingleDep(attributes, attributeArgs.deps, controlType);
    }
  }
  return show;
};
;// CONCATENATED MODULE: ./plugin-fw/includes/builders/gutenberg/src/common/generateShortcode.js

/**
 * Internal dependencies
 */


/**
 * Generate the shortcode
 *
 * @param {object} blockArgs The block arguments.
 * @param {object} attributes The attributes
 * @returns {string}
 */
var generateShortcode = function generateShortcode(blockArgs, attributes) {
  var theShortcode = '';
  var callback = false;
  if (typeof blockArgs.callback !== 'undefined') {
    if (jQuery && blockArgs.callback in jQuery.fn) {
      callback = jQuery.fn[blockArgs.callback];
    } else if (blockArgs.callback in window) {
      callback = window[blockArgs.callback];
    }
  }
  if (typeof callback === 'function') {
    theShortcode = callback(attributes, blockArgs);
  } else {
    var shortcodeAttrs = blockArgs.attributes ? Object.entries(blockArgs.attributes).map(function (_ref) {
      var _ref2 = _slicedToArray(_ref, 2),
        attributeName = _ref2[0],
        attributeArgs = _ref2[1];
      var show = checkForDeps(attributeArgs, attributes);
      var value = attributes[attributeName];
      if (show && typeof value !== 'undefined') {
        var shortcodeValue = !!attributeArgs.remove_quotes ? value : "\"".concat(value, "\"");
        return attributeName + '=' + shortcodeValue;
      }
    }) : [];
    var shortcodeAttrsText = shortcodeAttrs.length ? ' ' + shortcodeAttrs.join(' ') : '';
    theShortcode = "[".concat(blockArgs.shortcode_name).concat(shortcodeAttrsText, "]");
  }
  return theShortcode;
};
;// CONCATENATED MODULE: ./plugin-fw/includes/builders/gutenberg/src/common/index.js




;// CONCATENATED MODULE: ./assets/js/blocks/src/grand-total/index.js
/**
 * External dependencies
 */


/**
 * Internal dependencies
 */



(0,external_wp_blocks_namespaceObject.registerBlockType)(grand_total_block_namespaceObject, {
  icon: {
    src: yith_icon
  },
  edit: Edit,
  save: Save
});
})();

var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=yith-wcdp-grand-total.bundle.js.map