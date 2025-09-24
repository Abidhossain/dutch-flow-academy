/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 61:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var _typeof = (__webpack_require__(698)["default"]);
function _regeneratorRuntime() {
  "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */
  module.exports = _regeneratorRuntime = function _regeneratorRuntime() {
    return exports;
  }, module.exports.__esModule = true, module.exports["default"] = module.exports;
  var exports = {},
    Op = Object.prototype,
    hasOwn = Op.hasOwnProperty,
    defineProperty = Object.defineProperty || function (obj, key, desc) {
      obj[key] = desc.value;
    },
    $Symbol = "function" == typeof Symbol ? Symbol : {},
    iteratorSymbol = $Symbol.iterator || "@@iterator",
    asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator",
    toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";
  function define(obj, key, value) {
    return Object.defineProperty(obj, key, {
      value: value,
      enumerable: !0,
      configurable: !0,
      writable: !0
    }), obj[key];
  }
  try {
    define({}, "");
  } catch (err) {
    define = function define(obj, key, value) {
      return obj[key] = value;
    };
  }
  function wrap(innerFn, outerFn, self, tryLocsList) {
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator,
      generator = Object.create(protoGenerator.prototype),
      context = new Context(tryLocsList || []);
    return defineProperty(generator, "_invoke", {
      value: makeInvokeMethod(innerFn, self, context)
    }), generator;
  }
  function tryCatch(fn, obj, arg) {
    try {
      return {
        type: "normal",
        arg: fn.call(obj, arg)
      };
    } catch (err) {
      return {
        type: "throw",
        arg: err
      };
    }
  }
  exports.wrap = wrap;
  var ContinueSentinel = {};
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}
  var IteratorPrototype = {};
  define(IteratorPrototype, iteratorSymbol, function () {
    return this;
  });
  var getProto = Object.getPrototypeOf,
    NativeIteratorPrototype = getProto && getProto(getProto(values([])));
  NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype);
  var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype);
  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function (method) {
      define(prototype, method, function (arg) {
        return this._invoke(method, arg);
      });
    });
  }
  function AsyncIterator(generator, PromiseImpl) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);
      if ("throw" !== record.type) {
        var result = record.arg,
          value = result.value;
        return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) {
          invoke("next", value, resolve, reject);
        }, function (err) {
          invoke("throw", err, resolve, reject);
        }) : PromiseImpl.resolve(value).then(function (unwrapped) {
          result.value = unwrapped, resolve(result);
        }, function (error) {
          return invoke("throw", error, resolve, reject);
        });
      }
      reject(record.arg);
    }
    var previousPromise;
    defineProperty(this, "_invoke", {
      value: function value(method, arg) {
        function callInvokeWithMethodAndArg() {
          return new PromiseImpl(function (resolve, reject) {
            invoke(method, arg, resolve, reject);
          });
        }
        return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg();
      }
    });
  }
  function makeInvokeMethod(innerFn, self, context) {
    var state = "suspendedStart";
    return function (method, arg) {
      if ("executing" === state) throw new Error("Generator is already running");
      if ("completed" === state) {
        if ("throw" === method) throw arg;
        return doneResult();
      }
      for (context.method = method, context.arg = arg;;) {
        var delegate = context.delegate;
        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);
          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }
        if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) {
          if ("suspendedStart" === state) throw state = "completed", context.arg;
          context.dispatchException(context.arg);
        } else "return" === context.method && context.abrupt("return", context.arg);
        state = "executing";
        var record = tryCatch(innerFn, self, context);
        if ("normal" === record.type) {
          if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue;
          return {
            value: record.arg,
            done: context.done
          };
        }
        "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg);
      }
    };
  }
  function maybeInvokeDelegate(delegate, context) {
    var methodName = context.method,
      method = delegate.iterator[methodName];
    if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel;
    var record = tryCatch(method, delegate.iterator, context.arg);
    if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel;
    var info = record.arg;
    return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel);
  }
  function pushTryEntry(locs) {
    var entry = {
      tryLoc: locs[0]
    };
    1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry);
  }
  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal", delete record.arg, entry.completion = record;
  }
  function Context(tryLocsList) {
    this.tryEntries = [{
      tryLoc: "root"
    }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0);
  }
  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];
      if (iteratorMethod) return iteratorMethod.call(iterable);
      if ("function" == typeof iterable.next) return iterable;
      if (!isNaN(iterable.length)) {
        var i = -1,
          next = function next() {
            for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next;
            return next.value = undefined, next.done = !0, next;
          };
        return next.next = next;
      }
    }
    return {
      next: doneResult
    };
  }
  function doneResult() {
    return {
      value: undefined,
      done: !0
    };
  }
  return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", {
    value: GeneratorFunctionPrototype,
    configurable: !0
  }), defineProperty(GeneratorFunctionPrototype, "constructor", {
    value: GeneratorFunction,
    configurable: !0
  }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) {
    var ctor = "function" == typeof genFun && genFun.constructor;
    return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name));
  }, exports.mark = function (genFun) {
    return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun;
  }, exports.awrap = function (arg) {
    return {
      __await: arg
    };
  }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () {
    return this;
  }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) {
    void 0 === PromiseImpl && (PromiseImpl = Promise);
    var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl);
    return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) {
      return result.done ? result.value : iter.next();
    });
  }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () {
    return this;
  }), define(Gp, "toString", function () {
    return "[object Generator]";
  }), exports.keys = function (val) {
    var object = Object(val),
      keys = [];
    for (var key in object) keys.push(key);
    return keys.reverse(), function next() {
      for (; keys.length;) {
        var key = keys.pop();
        if (key in object) return next.value = key, next.done = !1, next;
      }
      return next.done = !0, next;
    };
  }, exports.values = values, Context.prototype = {
    constructor: Context,
    reset: function reset(skipTempReset) {
      if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined);
    },
    stop: function stop() {
      this.done = !0;
      var rootRecord = this.tryEntries[0].completion;
      if ("throw" === rootRecord.type) throw rootRecord.arg;
      return this.rval;
    },
    dispatchException: function dispatchException(exception) {
      if (this.done) throw exception;
      var context = this;
      function handle(loc, caught) {
        return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught;
      }
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i],
          record = entry.completion;
        if ("root" === entry.tryLoc) return handle("end");
        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc"),
            hasFinally = hasOwn.call(entry, "finallyLoc");
          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0);
            if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc);
          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0);
          } else {
            if (!hasFinally) throw new Error("try statement without catch or finally");
            if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc);
          }
        }
      }
    },
    abrupt: function abrupt(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }
      finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null);
      var record = finallyEntry ? finallyEntry.completion : {};
      return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record);
    },
    complete: function complete(record, afterLoc) {
      if ("throw" === record.type) throw record.arg;
      return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel;
    },
    finish: function finish(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel;
      }
    },
    "catch": function _catch(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;
          if ("throw" === record.type) {
            var thrown = record.arg;
            resetTryEntry(entry);
          }
          return thrown;
        }
      }
      throw new Error("illegal catch attempt");
    },
    delegateYield: function delegateYield(iterable, resultName, nextLoc) {
      return this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      }, "next" === this.method && (this.arg = undefined), ContinueSentinel;
    }
  }, exports;
}
module.exports = _regeneratorRuntime, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ 698:
/***/ ((module) => {

function _typeof(obj) {
  "@babel/helpers - typeof";

  return (module.exports = _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) {
    return typeof obj;
  } : function (obj) {
    return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
  }, module.exports.__esModule = true, module.exports["default"] = module.exports), _typeof(obj);
}
module.exports = _typeof, module.exports.__esModule = true, module.exports["default"] = module.exports;

/***/ }),

/***/ 687:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

// TODO(Babel 8): Remove this file.

var runtime = __webpack_require__(61)();
module.exports = runtime;

// Copied from https://github.com/facebook/regenerator/blob/main/packages/runtime/runtime.js#L736=
try {
  regeneratorRuntime = runtime;
} catch (accidentalStrictMode) {
  if (typeof globalThis === "object") {
    globalThis.regeneratorRuntime = runtime;
  } else {
    Function("r", "regeneratorRuntime = r")(runtime);
  }
}


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

;// CONCATENATED MODULE: ./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }
  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}
function _asyncToGenerator(fn) {
  return function () {
    var self = this,
      args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);
      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }
      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }
      _next(undefined);
    });
  };
}
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
// EXTERNAL MODULE: ./node_modules/@babel/runtime/regenerator/index.js
var regenerator = __webpack_require__(687);
var regenerator_default = /*#__PURE__*/__webpack_require__.n(regenerator);
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

;// CONCATENATED MODULE: ./assets/js/admin/src/vendors-table.js





/**
 * VENDORS TABLE JAVASCRIPT HANDLER
 *
 * @package
 * @since 4.0.0
 */



var VendorsTable = /*#__PURE__*/function () {
  function VendorsTable() {
    _classCallCheck(this, VendorsTable);
    _defineProperty(this, "tableSelector", '#vendors-list-table');
    _defineProperty(this, "table", null);
    _defineProperty(this, "modal", null);
    _defineProperty(this, "modalType", null);
    _defineProperty(this, "fieldsHandler", null);
    this.table = jQuery(this.tableSelector);
    this.init();
  }
  _createClass(VendorsTable, [{
    key: "init",
    value: function init() {
      this.addHeadingCreateButton();
      jQuery(document).on('click', '.create-vendor', {
        self: this
      }, this.create);
      if (this.table.length) {
        this.table.on('click', '.view-vendor a', this.view);
        this.table.on('click', '.edit-vendor', {
          self: this
        }, this.edit);
        this.table.on('click', '.approve-vendor a', this.approve);
        this.table.on('click', '.reject-vendor a', this.reject);
      }
    }
  }, {
    key: "addHeadingCreateButton",
    value: function addHeadingCreateButton() {
      var _jQuery$find;
      (_jQuery$find = jQuery('#yith_wpv_panel_vendors-list').find('.yith-plugin-fw__panel__content__page__title')) === null || _jQuery$find === void 0 ? void 0 : _jQuery$find.after(jQuery('<a>', {
        href: '#',
        "class": 'create-vendor page-title-action',
        text: yith_wcmv_vendors.createVendorButtonLabel
      }));
    }
  }, {
    key: "initModalActions",
    value: function initModalActions() {
      if (null === this.modal) {
        return false;
      }
      this.modal.elements.title.on('click', '.steps-list a', {
        self: this
      }, this.navToStep);
      this.modal.elements.content.on('click', '.owner-navigation a', {
        self: this
      }, this.vendorOwnerNav);
      this.modal.elements.content.on('click', '.vendor-field .set-password', this.showPasswordField);
      this.modal.elements.footer.on('click', '.vendor-next-step', {
        self: this
      }, this.nextStep);
      this.modal.elements.footer.on('click', '.vendor-modal-submit', this.submitForm.bind(this));
    }
  }, {
    key: "create",
    value: function create(event) {
      event.preventDefault();
      event.data.self.openModal({
        type: 'create',
        "class": 'yith-wcmv-create-vendor-modal',
        content: yith_wcmv_vendors.createModalDefault
      });
    }
  }, {
    key: "view",
    value: function view(event) {
      event.preventDefault();
      window.open(jQuery(this).attr('href'), '_blank');
    }
  }, {
    key: "edit",
    value: function edit(event) {
      event.preventDefault();
      var self = event.data.self;
      var trigger = jQuery(this),
        vendor_id = trigger.data('vendor_id'),
        data = {
          request: 'get-vendor-data',
          vendor_id: vendor_id
        };
      ajax_request.call(data, trigger.closest('td'), 'GET').done(function (res) {
        if (res.success) {
          var _data = {
            modalType: 'edit'
          };
          self.openModal({
            type: 'edit',
            "class": 'yith-wcmv-edit-vendor-modal',
            header: _data,
            content: res.data !== 'undefined' ? jQuery.extend({}, _data, res.data) : {}
          });
        }
      });
    }
  }, {
    key: "approve",
    value: function approve(event) {
      event.preventDefault();
      var wrap = jQuery(this).closest('td'),
        data = {
          vendor_id: jQuery(this).parent().data('vendor_id'),
          request: 'approve-vendor'
        };
      ajax_request.call(data, wrap, 'POST').done(function (res) {
        var _res$data;
        if (res !== null && res !== void 0 && res.success && res !== null && res !== void 0 && res.data && (_res$data = res.data) !== null && _res$data !== void 0 && _res$data.html) {
          wrap.html(res.data.html);
        }
      });
    }
  }, {
    key: "reject",
    value: function reject(event) {
      event.preventDefault();
      var contentTemplate = wp.template('yith-wcmv-modal-vendor-reject');
      var wrap = jQuery(this).closest('td'),
        trigger = jQuery(this),
        vendor_name = trigger.closest('tr').find('td.name .edit-vendor').text();
      var modal = yith.ui.modal({
        title: yith_wcmv_vendors.rejectVendorModalTitle,
        content: contentTemplate({
          vendor: vendor_name
        }),
        footer: '',
        width: 350,
        classes: {
          wrap: 'yith-wcmv-reject-vendor-modal',
          content: 'yith-plugin-ui'
        }
      });
      modal.elements.content.find('form').on('submit', function (event) {
        event.preventDefault();
        var data = {
          vendor_id: trigger.parent().data('vendor_id'),
          message: jQuery('#reject-reason').val(),
          request: 'reject-vendor'
        };
        ajax_request.call(data, modal.elements.main, 'POST').done(function (res) {
          var _res$data2;
          modal.close();
          if (res !== null && res !== void 0 && res.success && res !== null && res !== void 0 && res.data && (_res$data2 = res.data) !== null && _res$data2 !== void 0 && _res$data2.html) {
            wrap.html(res.data.html);
          }
        });
      });
    }
  }, {
    key: "openModal",
    value: function openModal(data) {
      var self = this;
      var defaults = {
        type: 'create',
        "class": '',
        header: {},
        content: {},
        footer: {}
      };
      if (null !== this.modal) {
        return false;
      }
      var headerTemplate = wp.template('yith-wcmv-modal-vendor-modal-header'),
        contentTemplate = wp.template('yith-wcmv-modal-vendor-modal-content'),
        footerTemplate = wp.template('yith-wcmv-modal-vendor-modal-footer');
      data = jQuery.extend({}, defaults, data);
      self.modal = yith.ui.modal({
        title: headerTemplate(data.header),
        content: contentTemplate(data.content),
        footer: footerTemplate(data.footer),
        width: 1000,
        classes: {
          wrap: data["class"],
          content: 'yith-plugin-ui'
        },
        closeSelector: '.vendor-close-modal',
        onCreate: function onCreate() {
          self.modalType = data.type;
          self.fieldsHandler = new VendorFieldsHandler(jQuery('.yith-plugin-fw__modal__content'));
          self.fieldsHandler.init();
        },
        onClose: function onClose() {
          self.modal = null;
          self.modalType = null;
        }
      });
      self.initModalActions();
      jQuery(document).trigger('yith_wcmv_vendors_modal_opened', [self.modal, self.modalType]);
    }
  }, {
    key: "goToStep",
    value: function () {
      var _goToStep = _asyncToGenerator( /*#__PURE__*/regenerator_default().mark(function _callee(stepID) {
        var step, owner;
        return regenerator_default().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              step = this.modal.elements.title.find('li[data-step="' + stepID + '"]');
              if (step.length) {
                _context.next = 3;
                break;
              }
              return _context.abrupt("return", false);
            case 3:
              _context.next = 5;
              return this.maybeCreateOwner();
            case 5:
              owner = _context.sent;
              if (owner !== null && owner !== void 0 && owner.success) {
                _context.next = 8;
                break;
              }
              return _context.abrupt("return", false);
            case 8:
              // Update Nav.
              step.removeClass('done').addClass('current').prevAll().removeClass('current').addClass('done').end().nextAll().removeClass('current done');

              // Update Content.
              this.modal.elements.content.find('fieldset[data-step="' + stepID + '"]').siblings('fieldset').hide().end().fadeIn();

              // If is modal create, update also footer buttons
              if ('create' === this.modalType) {
                if (!step.next().length) {
                  this.modal.elements.footer.find('.vendor-next-step').hide().end().find('.vendor-modal-submit').show();
                } else {
                  this.modal.elements.footer.find('.vendor-modal-submit').hide().end().find('.vendor-next-step').show();
                }
              }
            case 11:
            case "end":
              return _context.stop();
          }
        }, _callee, this);
      }));
      function goToStep(_x) {
        return _goToStep.apply(this, arguments);
      }
      return goToStep;
    }()
  }, {
    key: "navToStep",
    value: function navToStep(event) {
      event.preventDefault();
      var self = event.data.self,
        trigger = jQuery(this);
      if (self.isFormWithError()) {
        return false;
      }

      // Nav only done steps for create modal.
      if ('create' === self.modalType && !trigger.parent().hasClass('done')) {
        return false;
      }
      self.goToStep(trigger.attr('href').replace('#', ''));
    }
  }, {
    key: "getCurrentStep",
    value: function getCurrentStep() {
      return this.modal.elements.title.find('.steps-list li.current').data('step');
    }
  }, {
    key: "nextStep",
    value: function nextStep(event) {
      event.preventDefault();
      var self = event.data.self;
      var currentStep = self.getCurrentStep();
      if (!currentStep || self.isFormWithError()) {
        return false;
      }
      var nextStep = self.modal.elements.title.find('li[data-step="' + currentStep + '"]').next().data('step');
      if (nextStep) {
        self.goToStep(nextStep);
      }
    }
  }, {
    key: "vendorOwnerNav",
    value: function vendorOwnerNav(event) {
      var _event$data$self$fiel;
      event.preventDefault();
      var trigger = jQuery(this),
        wrap = trigger.closest('.vendor-owner-wrapper'),
        dest = wrap.find(trigger.attr('href'));
      trigger.addClass('current').siblings().removeClass('current');
      dest.fadeIn().siblings(':not( .owner-navigation )').hide();
      (_event$data$self$fiel = event.data.self.fieldsHandler) === null || _event$data$self$fiel === void 0 ? void 0 : _event$data$self$fiel.resetFormError(wrap);
    }
  }, {
    key: "maybeCreateOwner",
    value: function maybeCreateOwner() {
      var _this = this;
      if (!this.modal.elements.content.find('#create-owner').is(':visible')) {
        return {
          success: true
        };
      }
      var data = {};
      jQuery.each(this.modal.elements.content.find('form').serializeArray(), function (i, item) {
        if (0 === item.name.indexOf('new_owner_')) {
          data[item.name] = item.value;
        }
      });
      data.request = 'create_owner';
      return ajax_request.post(data, this.modal.elements.content).done(function (res) {
        var wrap = _this.modal.elements.content.find('#create-owner');
        if (res.success) {
          var _this$fieldsHandler;
          // add new owner.
          var newOption = new Option(res.data.name, res.data.id, true, true);

          // Reset errors.
          (_this$fieldsHandler = _this.fieldsHandler) === null || _this$fieldsHandler === void 0 ? void 0 : _this$fieldsHandler.resetFormError(wrap);
          _this.modal.elements.content.find('.yith-wcmv-owner-select').append(newOption).trigger('change');
          _this.modal.elements.content.find('#create-owner').find('input').val('');
          _this.modal.elements.content.find('.owner-navigation a').first().click();
        } else {
          var _this$fieldsHandler2;
          (_this$fieldsHandler2 = _this.fieldsHandler) === null || _this$fieldsHandler2 === void 0 ? void 0 : _this$fieldsHandler2.addFieldError(res.data.message, res.data.field, wrap);
        }
      });
    }
  }, {
    key: "showPasswordField",
    value: function showPasswordField(event) {
      event.preventDefault();
      jQuery(this).hide().next().fadeIn();
    }
  }, {
    key: "submitForm",
    value: function () {
      var _submitForm = _asyncToGenerator( /*#__PURE__*/regenerator_default().mark(function _callee2() {
        var owner;
        return regenerator_default().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _context2.next = 2;
              return this.maybeCreateOwner();
            case 2:
              owner = _context2.sent;
              if (owner !== null && owner !== void 0 && owner.success && !this.isFormWithError()) {
                if (typeof jQuery.fn.block !== 'undefined') {
                  this.modal.elements.main.block({
                    message: null,
                    overlayCSS: {
                      background: '#fff no-repeat center',
                      opacity: 0.5,
                      cursor: 'none'
                    }
                  });
                }
                this.modal.elements.content.find('form').submit();
              }
            case 4:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this);
      }));
      function submitForm() {
        return _submitForm.apply(this, arguments);
      }
      return submitForm;
    }() // ERRORS HANDLER
  }, {
    key: "isFormWithError",
    value: function isFormWithError() {
      var _this2 = this;
      var wrap = this.modal !== null ? this.modal.elements.content : '';
      if (!wrap.length) {
        return false;
      }
      // Check for single field.
      wrap.find('.field-required').filter(':visible').each(function (i, field) {
        if (!jQuery(field).closest('.field-error').length && !jQuery(field).val()) {
          var _this2$fieldsHandler;
          (_this2$fieldsHandler = _this2.fieldsHandler) === null || _this2$fieldsHandler === void 0 ? void 0 : _this2$fieldsHandler.addFieldError(yith_vendors.requiredFieldError, jQuery(field).attr('name'));
        }
      });
      var errors = wrap.find('#error-message, .field-error');
      if (!errors.length) {
        return false;
      }
      var step = errors.last().closest('.step-content').attr('data-step');
      if (step !== this.getCurrentStep) {
        this.goToStep(step);
      }
      return true;
    }
  }]);
  return VendorsTable;
}();

;// CONCATENATED MODULE: ./assets/js/admin/src/vendor-registration-table.js




/**
 * Vendor registration table custom field JS
 *
 * @package YITH WooCommerce Multi Vendor
 * @since 4.0.0
 */



var VendorRegistrationTable = /*#__PURE__*/function () {
  function VendorRegistrationTable() {
    _classCallCheck(this, VendorRegistrationTable);
    _defineProperty(this, "field_handler", void 0);
    this.init();
  }
  _createClass(VendorRegistrationTable, [{
    key: "init",
    value: function init() {
      this.table = jQuery('.yith-vendor-registration-table-wrapper');
      this.form = null;
      this.modal = null;
      if (this.table.length) {
        this.initSortable();
        this.table.on('click', '.yith-vendor-registration-table__add-fields', {
          self: this
        }, this.addField);
        this.table.on('click', '.yith-vendor-registration-table__edit-field', {
          self: this
        }, this.editField);
        this.table.on('click', '.yith-vendor-registration-table__delete-field', {
          self: this
        }, this.deleteField);
        this.table.on('click', '.yith-vendor-registration-table__restore-default', {
          self: this
        }, this.formReset);
        this.table.on('change', 'input[name="active"]', {
          self: this
        }, this.fieldActiveSwitch);
      }
    }
  }, {
    key: "initSortable",
    value: function initSortable() {
      var self = this,
        items_wrapper = self.table.find('tbody'),
        items = items_wrapper.find('tr');
      if (items.length > 1) {
        items_wrapper.sortable({
          handle: '.yith-vendor-registration-table__drag-field',
          cursor: 'move',
          scrollSensitivity: 10,
          tolerance: 'pointer',
          axis: 'y',
          stop: function stop(event, ui) {
            var order = [];
            items_wrapper.find('tr').each(function () {
              order.push(jQuery(this).data('id'));
            });
            ajax_request.post({
              request: 'registration_table_order_fields',
              order: order
            }, self.table);
          }
        }).disableSelection();
      }
    }
  }, {
    key: "modalActions",
    value: function modalActions() {
      if (this.modal) {
        this.form = this.modal.elements.content.find('#vendor-registration-field-form');
        this.form.on('keyup', '#name', this.sanitizeFieldName);
        this.form.on('change', '#name', {
          self: this
        }, this.checkDuplicatedName);
        // Handle options.
        this.initTableOptions();
        this.form.on('click', '#add_new_option', this.addNewOption.bind(this));
        this.form.on('click', '.options-table .delete', this.deleteOption);
        // Handle submit form.
        this.modal.elements.footer.on('click', '.vendor-registration-field-form-submit', {
          self: this
        }, this.formSubmit);
      }
    }
  }, {
    key: "sanitizeFieldName",
    value: function sanitizeFieldName() {
      var value = jQuery(this).val();
      // Format value.
      value = value.toLowerCase().replace(/[^0-9a-z-]+/, '-');
      // Set new value.
      jQuery(this).val(value);
    }
  }, {
    key: "checkDuplicatedName",
    value: function checkDuplicatedName(event) {
      var self = event.data.self;
      var value = jQuery(this).val();
      if (value) {
        if (self.table.find('tr:not(.editing)').filter('tr[data-name="' + value + '"]').length) {
          self.field_handler.addFieldError(jQuery(this).data('error'), 'registration_form[name]');
        } else {
          self.field_handler.resetFieldError('registration_form[name]');
        }
      }
    }
  }, {
    key: "tableReplace",
    value: function tableReplace(newTable) {
      this.table.replaceWith(newTable);
      this.init(); // Refresh form.
    }
  }, {
    key: "openModal",
    value: function openModal(data, title) {
      var self = this,
        contentTemplate = wp.template('yith-wcmv-modal-registration-form'),
        footerTemplate = wp.template('yith-wcmv-modal-registration-form-footer');
      var tableRows = this.table.find('tr[data-name]');
      this.modal = yith.ui.modal({
        title: title,
        content: contentTemplate(data),
        footer: footerTemplate(),
        width: 550,
        classes: {
          wrap: 'yith-vendor-registration-form-modal',
          content: 'yith-plugin-ui'
        },
        onCreate: function onCreate() {
          var formTable = jQuery('#vendor-registration-field-form .form-table');
          self.field_handler = new FieldsHandler(formTable);
          self.field_handler.init();

          // Handle special field vendor-name
          if ('vendor-name' === (data === null || data === void 0 ? void 0 : data.field_id)) {
            formTable.find(':input').filter(function () {
              return -1 === jQuery.inArray(this.id, ['label', 'class', 'placeholder', 'position']);
            }).closest('tr').remove();
          }
          if (!jQuery.isEmptyObject(data)) {
            tableRows.filter('[data-name="' + data.name + '"]').addClass('editing');
          }
        },
        onClose: function onClose() {
          tableRows.removeClass('editing');
        }
      });
      this.modalActions();
    }
  }, {
    key: "addField",
    value: function addField(event) {
      event.preventDefault();
      event.data.self.openModal(yith_wcmv_vendors.registrationTableFieldsDefault, yith_wcmv_vendors.registrationModalAddTitle);
    }
  }, {
    key: "editField",
    value: function editField(event) {
      event.preventDefault();
      var row = jQuery(this).closest('tr'),
        options = jQuery(this).closest('tr').data('options');
      if (typeof options === 'undefined') {
        return;
      }
      options.options = JSON.stringify(options.options); // Stringify options.
      options.field_id = row.data('id'); // Add row ID.
      event.data.self.openModal(options, yith_wcmv_vendors.registrationModalEditTitle);
    }
  }, {
    key: "deleteField",
    value: function deleteField(event) {
      event.preventDefault();
      var self = event.data.self,
        field_id = jQuery(this).closest('tr').data('id');
      if (!field_id) {
        return false;
      }
      yith.ui.confirm({
        title: yith_wcmv_vendors.registrationDeleteFieldTitle,
        message: yith_wcmv_vendors.registrationDeleteFieldContent,
        confirmButtonType: 'delete',
        confirmButton: yith_wcmv_vendors.registrationDeleteFieldButton,
        closeAfterConfirm: true,
        onConfirm: function onConfirm() {
          var data = {
            request: 'registration_table_field_delete',
            field_id: field_id
          };
          ajax_request.call(data, self.table, 'POST').done(function (res) {
            if (res.success && res.data !== 'undefined') {
              self.tableReplace(res.data.html);
            }
          });
        }
      });
    }
  }, {
    key: "formSubmit",
    value: function formSubmit(event) {
      event.preventDefault();
      var self = event.data.self;

      // Trigger name check!
      self.form.find('#name').trigger('change');
      if (self.field_handler.isFormWithErrors()) {
        return false;
      }
      var data = self.form.serializeArray();

      // Add request param and do AJAX request
      data.push({
        name: 'request',
        value: 'registration_table_field_save'
      });
      self.modal.close();
      ajax_request.call(data, self.table, 'POST').done(function (res) {
        if (res.success && res.data !== 'undefined') {
          self.tableReplace(res.data.html);
        }
      });
    }
  }, {
    key: "fieldActiveSwitch",
    value: function fieldActiveSwitch(event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var input = jQuery(this),
        self = event.data.self;
      var data = {
        request: 'registration_table_field_active_switch',
        field_id: input.closest('tr').data('id'),
        active: input.is(':checked') ? 'yes' : 'no'
      };
      ajax_request.call(data, self.table, 'POST').done(function (res) {
        if (res.success && res.data !== 'undefined') {
          self.tableReplace(res.data.html);
        }
      });
    }
  }, {
    key: "formReset",
    value: function formReset(event) {
      event.preventDefault();
      var self = event.data.self;
      yith.ui.confirm({
        title: yith_wcmv_vendors.registrationTableResetTitle,
        message: yith_wcmv_vendors.registrationTableResetContent,
        confirmButtonType: 'delete',
        confirmButton: yith_wcmv_vendors.registrationTableResetButton,
        closeAfterConfirm: true,
        onConfirm: function onConfirm() {
          ajax_request.call({
            request: 'registration_table_fields_reset'
          }, self.table, 'POST').done(function (res) {
            if (res.success && res.data !== 'undefined') {
              self.tableReplace(res.data.html);
            }
          });
        }
      });
    }

    // OPTIONS HANDLER
  }, {
    key: "initTableOptions",
    value: function initTableOptions() {
      var _this = this;
      var table = this.form.find('table.options-table'),
        value = table.data('value');
      // Add an empty row if there aren't any row.
      if (_typeof(value) !== 'object') {
        this.addOption();
      } else {
        var i = 0;
        jQuery.each(value, function (value, label) {
          _this.addOption(i, value, label);
          i++;
        });
      }

      // Init sortable
      table.find('tbody').sortable({
        handle: '.drag',
        cursor: 'move',
        scrollSensitivity: 10,
        tolerance: 'pointer',
        axis: 'y'
      });
    }
  }, {
    key: "addNewOption",
    value: function addNewOption(event) {
      event.preventDefault();
      var index = 0;
      // Get the higher index value
      while (this.form.find('table.options-table tr[data-index="' + index + '"]').length) {
        index++;
      }
      this.addOption(index);
    }
  }, {
    key: "addOption",
    value: function addOption() {
      var index = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
      var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
      var label = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
      var table = this.form.find('table.options-table'),
        name = table.data('name');
      table.find('tbody').append('<tr data-index="' + index + '">' + '<td class="column-label"><input type="text" name="' + name + '[' + index + '][label]" id="options_' + index + '_label" value="' + label + '"></td>' + '<td class="column-value"><input type="text" name="' + name + '[' + index + '][value]" id="options_' + index + '_value" value="' + value + '"></td>' + '<td class="column-actions"><span class="drag yith-icon yith-icon-drag ui-sortable-handle"></span><a href="#" role="button" class="delete yith-icon yith-icon-trash"></a></td>' + '</tr>');
    }
  }, {
    key: "deleteOption",
    value: function deleteOption(event) {
      event.preventDefault();
      jQuery(this).closest('tr').remove();
    }
  }]);
  return VendorRegistrationTable;
}();

;// CONCATENATED MODULE: ./assets/js/admin/src/vendors.js
/**
 * Vendor TAB js
 *
 * @package YITH WooCommerce Multi Vendor
 * @since 4.0.0
 */




if (jQuery('.vendors-list-table-wrapper').length) {
  new VendorsTable();
}
if (jQuery('.yith-vendor-registration-table-wrapper').length) {
  new VendorRegistrationTable();
}
jQuery(document.body).on('click', '.yith_wpv_vendors_skip_review_for_all', function (event) {
  event.preventDefault();
  var button = jQuery(this);
  yith.ui.confirm({
    title: '',
    message: yith_vendors.forceSkipMessage,
    onConfirm: function onConfirm() {
      ajax_request.call({
        request: button.data('action')
      }, button, 'POST');
    }
  });
});
})();

var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=vendors.js.map