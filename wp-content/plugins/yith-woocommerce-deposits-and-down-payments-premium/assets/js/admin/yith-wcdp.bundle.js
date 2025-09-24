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
;// CONCATENATED MODULE: ./assets/js/admin/src/modules/dependencies.js


/* global yith_wcaf */




var YITH_WCDP_Dependencies_Handler = /*#__PURE__*/function () {
  function YITH_WCDP_Dependencies_Handler($container) {
    var _this$$container;
    _classCallCheck(this, YITH_WCDP_Dependencies_Handler);
    // container
    _defineProperty(this, "$container", void 0);
    // fields;
    _defineProperty(this, "$fields", void 0);
    // dependencies tree.
    _defineProperty(this, "dependencies", {});
    this.$container = $container;
    if (!((_this$$container = this.$container) !== null && _this$$container !== void 0 && _this$$container.length)) {
      return;
    }
    this.init();
  }
  _createClass(YITH_WCDP_Dependencies_Handler, [{
    key: "init",
    value: function init() {
      var _this$$fields;
      this.initFields();
      if (!((_this$$fields = this.$fields) !== null && _this$$fields !== void 0 && _this$$fields.length)) {
        return false;
      }
      this.initDependencies();
      return true;
    }
  }, {
    key: "reInit",
    value: function reInit() {
      this.$fields.off('change', this.applyDependencies);
      return this.init();
    }
  }, {
    key: "initFields",
    value: function initFields() {
      this.$fields = this.$container.find(':input');
    }
  }, {
    key: "initDependencies",
    value: function initDependencies() {
      this.buildDependenciesTree();
      if (!Object.keys(this.dependencies).length) {
        return;
      }
      this.handleDependencies();
    }
  }, {
    key: "buildDependenciesTree",
    value: function buildDependenciesTree() {
      var self = this;
      this.$fields.closest('[data-dependencies]').each(function () {
        var $field = $(this),
          id = $field.attr('id');
        if (!id) {
          return;
        }
        var newBranch = _defineProperty({}, id, $field.data('dependencies'));
        self.dependencies = $.extend(self.dependencies, newBranch);
      });

      // backward compatibility with plugin-fw
      this.$container.find('[data-dep-target]').each(function () {
        var $container = $(this),
          id = $container.data('dep-id'),
          target = $container.data('dep-target'),
          value = $container.data('dep-value');
        if (!id || !target || !value) {
          return;
        }
        var newBranch = _defineProperty({}, target, _defineProperty({}, id, value.toString().split(',')));
        self.dependencies = $.extend(self.dependencies, newBranch);
      });
    }
  }, {
    key: "handleDependencies",
    value: function handleDependencies() {
      this.$fields.on('change', this.applyDependencies.bind(this));
      this.applyDependencies();
    }
  }, {
    key: "applyDependencies",
    value: function applyDependencies() {
      var _this = this;
      $.each(this.dependencies, function (field, conditions) {
        var $container = _this.findFieldContainer(field),
          show = _this.checkConditions(conditions);
        if (!$container.length) {
          return;
        }
        if (show) {
          $container === null || $container === void 0 ? void 0 : $container.fadeIn();
        } else {
          $container === null || $container === void 0 ? void 0 : $container.hide();
        }
      });
    }
  }, {
    key: "findField",
    value: function findField(field) {
      var $field = this.$container.find("#".concat(field));
      if (!$field.length) {
        return false;
      }
      return $field;
    }
  }, {
    key: "findFieldContainer",
    value: function findFieldContainer(field) {
      var $field = this.findField(field);
      if (!($field !== null && $field !== void 0 && $field.length)) {
        return false;
      }

      // maybe an inline-field
      var $container = $field.closest('.option-element');

      // maybe in a settings table
      if (!$container.length) {
        $container = $field.closest('.yith-plugin-fw__panel__option');
      }

      // maybe inside a form
      if (!$container.length) {
        $container = $field.closest('.form-row');
      }
      if (!$container.length) {
        return false;
      }
      return $container;
    }
  }, {
    key: "checkConditions",
    value: function checkConditions(conditions) {
      var _this2 = this;
      var result = true;
      $.each(conditions, function (field, condition) {
        var $field = _this2.findField(field),
          fieldValue;
        if (!result || !($field !== null && $field !== void 0 && $field.length)) {
          return;
        }
        if ($field.first().is('input[type="radio"]')) {
          fieldValue = $field.filter(':checked').val().toString();
        } else {
          var _$field$val;
          fieldValue = $field === null || $field === void 0 ? void 0 : (_$field$val = $field.val()) === null || _$field$val === void 0 ? void 0 : _$field$val.toString();
        }
        if (Array.isArray(condition)) {
          result = condition.includes(fieldValue);
        } else if (typeof condition === 'function') {
          result = condition(fieldValue);
        } else if (0 === condition.indexOf(':')) {
          result = $field.is(condition);
        } else if (0 === condition.indexOf('!:')) {
          result = !$field.is(condition.toString().substring(1));
        } else if (0 === condition.indexOf('!')) {
          result = condition.toString().substring(1) !== fieldValue;
        } else {
          result = condition.toString() === fieldValue;
        }
        if (typeof _this2.dependencies[field] !== 'undefined') {
          result = result && _this2.checkConditions(_this2.dependencies[field]);
        }
      });
      return result;
    }
  }]);
  return YITH_WCDP_Dependencies_Handler;
}();
function initDependencies($container) {
  var _$container;
  // init container
  if (!((_$container = $container) !== null && _$container !== void 0 && _$container.length)) {
    $container = $document;
  }
  var handler = $container.data('dependencies-handler');
  if (handler) {
    return handler;
  }
  handler = new YITH_WCDP_Dependencies_Handler($container);
  $container.data('dependencies-handler', handler);
  return handler;
}
function reInitDependencies($container) {
  var _$container2;
  // init container
  if (!((_$container2 = $container) !== null && _$container2 !== void 0 && _$container2.length)) {
    $container = $document;
  }
  var handler = $container.data('dependencies-handler');
  if (!handler) {
    handler = initDependencies($container);
  }
  handler.reInit();
  return handler;
}
/* harmony default export */ const dependencies = (initDependencies);

;// CONCATENATED MODULE: ./assets/js/admin/src/modules/validation.js


/* global yith_wcdp */




var YITH_WCDP_Validation_Handler = /*#__PURE__*/function () {
  function YITH_WCDP_Validation_Handler($container) {
    var _this$$container;
    _classCallCheck(this, YITH_WCDP_Validation_Handler);
    // container
    _defineProperty(this, "$container", void 0);
    // error class to add/remove to fields wrapper
    _defineProperty(this, "ERROR_CLASS", 'woocommerce-invalid');
    this.$container = $container;
    if (!((_this$$container = this.$container) !== null && _this$$container !== void 0 && _this$$container.length)) {
      return;
    }
    this.initValidation();
  }

  // init validation.
  _createClass(YITH_WCDP_Validation_Handler, [{
    key: "initValidation",
    value: function initValidation() {
      this.initForm();
      this.initFields();
    }
  }, {
    key: "initForm",
    value: function initForm() {
      var $forms = this.$container.is('form') ? this.$container : this.$container.find('form');
      if (!$forms.length) {
        return;
      }
      var self = this;
      $forms.on('submit yith_wcaf_validate_fields', function (ev) {
        var $form = $(this),
          res = self.validateForm($form);
        if (!res) {
          ev.stopImmediatePropagation();
          return false;
        }
        return true;
      });
    }
  }, {
    key: "initFields",
    value: function initFields() {
      var $fields = this.getFields(this.$container);
      if (!$fields.length) {
        return;
      }
      var self = this;
      $fields.on('keyup change', function () {
        var $field = $(this);
        self.validateField($field);
      });
    }

    // fields handling.
  }, {
    key: "getFieldWrapper",
    value: function getFieldWrapper($field) {
      return $field.closest('.form-row, .yith-plugin-fw-panel-wc-row');
    }
  }, {
    key: "getFields",
    value: function getFields($container) {
      var $fields = $('input, select, textarea', $container);
      return $fields.not('input[type="submit"]').not('input[type="hidden"]').not('.select2-search__field');
    }
  }, {
    key: "getVisibleFields",
    value: function getVisibleFields($container) {
      var _this = this;
      var $fields = this.getFields($container);
      return $fields.filter(function (index, field) {
        var $field = $(field),
          $fieldWrapper = _this.getFieldWrapper($field);
        return $fieldWrapper.is(':visible');
      });
    }
  }, {
    key: "isFieldValid",
    value: function isFieldValid($field) {
      var $wrapper = this.getFieldWrapper($field),
        fieldType = $field.attr('type'),
        value = $field.val(),
        alwaysRequiredFields = ['reg_username', 'reg_email', 'reg_password'];

      // check for required fields
      if ($field.prop('required') || $wrapper.hasClass('required') || $wrapper.hasClass('validate-required') || $wrapper.hasClass('yith-plugin-fw--required') || alwaysRequiredFields.includes($field.get(0).id)) {
        if ('checkbox' === fieldType && !$field.is(':checked')) {
          throw 'missing';
        } else if (!value || !(value !== null && value !== void 0 && value.length)) {
          throw 'missing';
        }
      }

      // check for patterns
      var pattern = $wrapper.data('pattern');
      if (pattern) {
        var regex = new RegExp(pattern);
        if (!regex.test(value)) {
          throw 'malformed';
        }
      }

      // check for min length
      var minLength = $wrapper.data('min_length');
      if (minLength && value.length < minLength) {
        throw 'short';
      }

      // check for max length
      var maxLength = $wrapper.data('max_length');
      if (maxLength && value.length > maxLength) {
        throw 'long';
      }

      // check for number
      if ('number' === fieldType) {
        var min = parseFloat($field.attr('min')),
          max = parseFloat($field.attr('max')),
          numVal = parseFloat(value);
        if (min && min > numVal || max && max < numVal) {
          throw 'overflow';
        }
      }

      // all validation passed; we can return true.
      return true;
    }
  }, {
    key: "validateField",
    value: function validateField($field) {
      try {
        this.isFieldValid($field);
      } catch (e) {
        this.reportError($field, e);
        return false;
      }
      this.removeError($field);
      return true;
    }
  }, {
    key: "validateForm",
    value: function validateForm($form) {
      var $visibleFields = this.getVisibleFields($form);
      if (!$visibleFields.length) {
        return true;
      }
      var self = this;
      var valid = true;
      $visibleFields.each(function () {
        var $field = $(this);
        if (!self.validateField($field)) {
          valid = false;
        }
      });
      if (!valid) {
        // scroll top.
        this.scrollToFirstError($form);

        // stop form submitting.
        return false;
      }
      return true;
    }

    // error handling.
  }, {
    key: "getErrorMsg",
    value: function getErrorMsg($field, errorType) {
      var _labels$errors, _labels$errors2, _labels$errors3, _labels$errors4, _labels$errors5;
      // check if we have a field-specific error message.
      var msg = $field.data('error');
      if (msg) {
        return msg;
      }

      // check if message is added to wrapper.
      var $wrapper = this.getFieldWrapper($field);
      msg = $wrapper.data('error');
      if (msg) {
        return msg;
      }

      // check if message is added to label.
      var $label = $wrapper.find('label');
      msg = $label.data('error');
      if (msg) {
        return msg;
      }
      if (!(labels !== null && labels !== void 0 && labels.errors)) {
        return false;
      }
      switch (errorType) {
        case 'missing':
          var fieldType = $field.attr('type');
          msg = 'checkbox' === fieldType ? (_labels$errors = labels.errors) === null || _labels$errors === void 0 ? void 0 : _labels$errors.accept_check : (_labels$errors2 = labels.errors) === null || _labels$errors2 === void 0 ? void 0 : _labels$errors2.compile_field;
          if (msg) {
            return msg;
          }

        // fallthrough if we didn't find a proper message yet.
        default:
          msg = (_labels$errors3 = labels.errors) !== null && _labels$errors3 !== void 0 && _labels$errors3[errorType] ? (_labels$errors4 = labels.errors) === null || _labels$errors4 === void 0 ? void 0 : _labels$errors4[errorType] : (_labels$errors5 = labels.errors) === null || _labels$errors5 === void 0 ? void 0 : _labels$errors5.general_error;
          break;
      }
      return msg;
    }
  }, {
    key: "reportError",
    value: function reportError($field, errorType) {
      var $wrapper = this.getFieldWrapper($field),
        errorMsg = this.getErrorMsg($field, errorType);
      $wrapper.addClass(this.ERROR_CLASS);
      if (!errorMsg) {
        return;
      }

      // remove existing errors.
      $wrapper.find('.error-msg').remove();

      // generate and append new error message.
      var $errorMsg = $('<span/>', {
        "class": 'error-msg',
        text: errorMsg
      });
      $wrapper.append($errorMsg);
    }
  }, {
    key: "removeError",
    value: function removeError($field) {
      var $wrapper = this.getFieldWrapper($field),
        $errorMsg = $wrapper.find('.error-msg');
      $wrapper.removeClass(this.ERROR_CLASS);
      $errorMsg.remove();
    }
  }, {
    key: "scrollToFirstError",
    value: function scrollToFirstError($form) {
      var $firstError = $form.find(".".concat(this.ERROR_CLASS)).first();
      var $target = this.findScrollableParent($form);
      if (!$target || !$target.length) {
        $target = $('html, body');
      }
      var scrollDiff = $firstError.offset().top - $target.offset().top;
      var scrollValue = scrollDiff;
      if (!$target.is('html, body')) {
        scrollValue = $target.get(0).scrollTop + scrollDiff;
      }
      $target.animate({
        scrollTop: scrollValue
      });
    }
  }, {
    key: "findScrollableParent",
    value: function findScrollableParent($node) {
      var node = $node.get(0);
      if (!node) {
        return null;
      }
      var overflowY, isScrollable;
      do {
        if (document === node) {
          return null;
        }
        overflowY = window.getComputedStyle(node).overflowY;
        isScrollable = overflowY !== 'visible' && overflowY !== 'hidden';
      } while (!(isScrollable && node.scrollHeight > node.clientHeight) && (node = node.parentNode));
      return $(node);
    }
  }]);
  return YITH_WCDP_Validation_Handler;
}();
function initValidation($container) {
  var _$container;
  // init container
  if (!((_$container = $container) !== null && _$container !== void 0 && _$container.length)) {
    $container = $document;
  }
  return new YITH_WCDP_Validation_Handler($container);
}
;// CONCATENATED MODULE: ./assets/js/admin/src/modules/fields.js


/* global jQuery */




var initFields = function initFields($container) {
  var _$container;
  // init container
  if (!((_$container = $container) !== null && _$container !== void 0 && _$container.length)) {
    $container = $document;
  }

  // data-value handling
  (function () {
    var $fields = $(':input[data-value]', $container);
    if (!$fields.length) {
      return;
    }
    $fields.each(function () {
      var $field = $(this),
        value = $field.data('value');
      if ($field.is('input[type="checkbox"]') || $field.is('input[type="radio"]')) {
        if ('boolean' === typeof value) {
          $field.prop('checked', value);
        } else if (value) {
          $field.prop('checked', value === $field.val());
        } else {
          $field.prop('checked', false);
        }
      } else if ($field.is('select') && Array.isArray(value)) {
        $field.val(value);
      } else if ($field.is('select') && 'object' === _typeof(value)) {
        for (var i in value) {
          var _$field$find;
          if (!((_$field$find = $field.find("[value=\"".concat(i, "\"]"))) !== null && _$field$find !== void 0 && _$field$find.length)) {
            $field.append($('<option/>', {
              value: i,
              text: value[i]
            }));
          }
        }
        $field.val(Object.keys(value));
      } else if ('boolean' === typeof value) {
        $field.val(value ? 1 : 0);
      } else if (value) {
        $field.val(String(value));
      }
      $field.trigger('change');
    });
  })();

  // init dependencies
  dependencies($container);

  // init validation
  initValidation($container);

  // trigger plugin-fw fields handling
  $container.trigger('yith_fields_init');
};

;// CONCATENATED MODULE: ./assets/js/admin/src/modules/yith-wcdp-add-deposit-rule-modal.js


/* globals yith_wcdp, yith */





var YITH_WCDP_Add_Deposit_Rule_Modal = /*#__PURE__*/function () {
  function YITH_WCDP_Add_Deposit_Rule_Modal($openers) {
    _classCallCheck(this, YITH_WCDP_Add_Deposit_Rule_Modal);
    // dom elements that open this modal.
    _defineProperty(this, "$openers", null);
    // $opener that triggered open.
    _defineProperty(this, "$target", null);
    // modal object
    _defineProperty(this, "modal", null);
    if (!$openers.length) {
      return;
    }
    this.$openers = $openers;
    this.init();
  }
  _createClass(YITH_WCDP_Add_Deposit_Rule_Modal, [{
    key: "init",
    value: function init() {
      var self = this;
      this.$openers.on('click', function (ev) {
        ev.preventDefault();
        self.$target = $(this);
        self.onOpen();
      });
    }
  }, {
    key: "onOpen",
    value: function onOpen() {
      var _this$$target;
      var $item = this === null || this === void 0 ? void 0 : (_this$$target = this.$target) === null || _this$$target === void 0 ? void 0 : _this$$target.closest('[data-item]'),
        item = ($item === null || $item === void 0 ? void 0 : $item.data('item')) || {},
        args = {
          title: item !== null && item !== void 0 && item.id ? labels.edit_rule_title : labels.add_rule_title,
          content: wp.template('yith-wcdp-add-deposit-rule-modal')(item),
          footer: false,
          showClose: true,
          width: 350,
          classes: {
            wrap: 'yith-wcdp-modal'
          }
        };

      // open modal passing item object.
      this.modal = yith.ui.modal(args);

      // init modal fields
      initFields(this.modal.elements.content);
    }
  }]);
  return YITH_WCDP_Add_Deposit_Rule_Modal;
}();
/* harmony default export */ const yith_wcdp_add_deposit_rule_modal = (YITH_WCDP_Add_Deposit_Rule_Modal);
;// CONCATENATED MODULE: ./assets/js/admin/src/yith-wcdp.js


/* globals yith_wcdp, yith */






var YITH_WCDP_Admin = /*#__PURE__*/function () {
  function YITH_WCDP_Admin() {
    _classCallCheck(this, YITH_WCDP_Admin);
    this.maybeInitDepositModal();
    this.maybeInitSettings();
    this.maybeInitVariationPanel();
  }
  _createClass(YITH_WCDP_Admin, [{
    key: "maybeInitDepositModal",
    value: function maybeInitDepositModal() {
      var $container = $('#yith_wcdp_panel_settings-rules');
      if (!$container.length) {
        return;
      }
      this.initDepositModal($container);
    }
  }, {
    key: "maybeInitSettings",
    value: function maybeInitSettings() {
      var $containers = $('#yith_wcdp_panel_settings-deposits').add('#yith_wcdp_panel_balances').add('#yith_wcdp_panel_customizations').add('#yith_wcdp_deposit_tab');
      if (!$containers.length) {
        return;
      }
      this.initSettings($containers);
    }
  }, {
    key: "maybeInitVariationPanel",
    value: function maybeInitVariationPanel() {
      var _this = this;
      var $container = $('#woocommerce-product-data');
      if (!$container.length) {
        return;
      }
      $container.on('woocommerce_variations_loaded', function () {
        _this.initSettings($container);
      });
      $container.on('woocommerce_variations_loaded woocommerce_variations_added woocommerce_variations_removed', function () {
        reInitDependencies($container);
      });
    }
  }, {
    key: "initDepositModal",
    value: function initDepositModal($container) {
      var self = this,
        $addButtons = $('.yith-wcdp-add-rule-button', $container),
        $editButtons = $('.edit-deposit-rule', $container);
      if (!$addButtons.length) {
        return;
      }
      new yith_wcdp_add_deposit_rule_modal($addButtons.add($editButtons));
    }
  }, {
    key: "initSettings",
    value: function initSettings($container) {
      initFields($container);
    }
  }]);
  return YITH_WCDP_Admin;
}();
jQuery(function () {
  new YITH_WCDP_Admin();
});
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=yith-wcdp.bundle.js.map