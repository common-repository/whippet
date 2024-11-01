/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/app.js":
/***/ (function(module, exports) {

/*jslint browser: true*/

var whippet;
whippet = {
    menu: {
        init: function init() {
            'use strict';

            var menuItem;
            menuItem = document.getElementById('wp-admin-bar-whippet');
            if (menuItem) {
                menuItem.addEventListener('click', function () {
                    var panel = document.getElementById('whippet');

                    if (panel) {
                        if ('none' === panel.style.display) {
                            panel.style.display = 'block';
                        } else {
                            panel.style.display = 'none';
                        }
                    }
                });
            }
        }
    },
    UI: {
        init: function init() {
            'use strict';

            var elements, submitButton;

            elements = document.querySelectorAll('#whippet .option-everwhere input[type=checkbox]');
            Array.prototype.forEach.call(elements, function (el) {
                el.addEventListener('change', function () {
                    var enabledCheckboxes = document.querySelectorAll('.options[data-id=\'' + this.getAttribute('id') + '\'] input');
                    var newState = this.checked;

                    Array.prototype.forEach.call(enabledCheckboxes, function (elX) {
                        elX.disabled = !newState;
                    });

                    var disabledHere = document.querySelectorAll('.disable-here[data-id=\'' + this.getAttribute('id') + '\'] input');
                    var newState = this.checked;

                    Array.prototype.forEach.call(disabledHere, function (elX) {
                        elX.disabled = newState;
                    });
                });
            });

            elements = document.querySelectorAll('#whippet input[type=checkbox]');
            Array.prototype.forEach.call(elements, function (el) {
                el.addEventListener('change', function () {
                    document.whippetChanged = true;
                });
            });

            submitButton = document.getElementById('submit-whippet');
            if (submitButton) {
                submitButton.addEventListener('click', function () {
                    document.whippetChanged = false;
                });
            }
        },
        protection: function protection() {
            'use strict';

            window.addEventListener('beforeunload', function (e) {
                if (document.whippetChanged) {
                    var confirmationMessage = 'It looks like you have been editing configuration and tried to leave page without saving. Press cancel to stay on page.';
                    (e || window.event).returnValue = confirmationMessage;
                    return confirmationMessage;
                }
            });
        }
    },
    ready: function ready(fn) {
        'use strict';

        if ('loading' !== document.readyState) {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    },
    init: function init() {
        'use strict';

        whippet.ready(whippet.menu.init);
        whippet.ready(whippet.UI.init);
        whippet.ready(whippet.UI.protection);
    }
};

setTimeout(function () {
    whippet.init();
}, 100);

/***/ }),

/***/ "./resources/scss/style-whippet.scss":
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/scss/style.scss":
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./resources/js/app.js");
__webpack_require__("./resources/scss/style-whippet.scss");
module.exports = __webpack_require__("./resources/scss/style.scss");


/***/ })

/******/ });
//# sourceMappingURL=app.js.map