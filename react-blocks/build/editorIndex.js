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
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./react-blocks/src/editorIndex.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/assertThisInitialized.js ***!
  \**********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

module.exports = _assertThisInitialized;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/classCallCheck.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/classCallCheck.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

module.exports = _classCallCheck;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/createClass.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/createClass.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

module.exports = _createClass;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/extends.js":
/*!********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/extends.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _extends() {
  module.exports = _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  };

  return _extends.apply(this, arguments);
}

module.exports = _extends;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/getPrototypeOf.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _getPrototypeOf(o) {
  module.exports = _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

module.exports = _getPrototypeOf;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/inherits.js":
/*!*********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/inherits.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var setPrototypeOf = __webpack_require__(/*! ./setPrototypeOf */ "./node_modules/@babel/runtime/helpers/setPrototypeOf.js");

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
  if (superClass) setPrototypeOf(subClass, superClass);
}

module.exports = _inherits;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js":
/*!**************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js ***!
  \**************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var _typeof = __webpack_require__(/*! ../helpers/typeof */ "./node_modules/@babel/runtime/helpers/typeof.js");

var assertThisInitialized = __webpack_require__(/*! ./assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");

function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  }

  return assertThisInitialized(self);
}

module.exports = _possibleConstructorReturn;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/setPrototypeOf.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/setPrototypeOf.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _setPrototypeOf(o, p) {
  module.exports = _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

module.exports = _setPrototypeOf;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/typeof.js":
/*!*******************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/typeof.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof2(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof2 = function _typeof2(obj) { return typeof obj; }; } else { _typeof2 = function _typeof2(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof2(obj); }

function _typeof(obj) {
  if (typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol") {
    module.exports = _typeof = function _typeof(obj) {
      return _typeof2(obj);
    };
  } else {
    module.exports = _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : _typeof2(obj);
    };
  }

  return _typeof(obj);
}

module.exports = _typeof;

/***/ }),

/***/ "./react-blocks/src/blocks/Covers/Covers.js":
/*!**************************************************!*\
  !*** ./react-blocks/src/blocks/Covers/Covers.js ***!
  \**************************************************/
/*! exports provided: Covers */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Covers", function() { return Covers; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _components_LayoutSelector_LayoutSelector__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../../components/LayoutSelector/LayoutSelector */ "./react-blocks/src/components/LayoutSelector/LayoutSelector.js");
/* harmony import */ var _components_Preview__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../../components/Preview */ "./react-blocks/src/components/Preview.js");










var Covers =
/*#__PURE__*/
function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default()(Covers, _Component);

  function Covers(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, Covers);

    _this = _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default()(this, _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(Covers).call(this, props));
    _this.state = {
      tagTokens: [],
      postTypeTokens: []
    };
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(Covers, [{
    key: "onSelectedTagsChange",
    value: function onSelectedTagsChange(tokens) {
      var _this2 = this;

      var tagIds = tokens.map(function (token) {
        return _this2.props.tagsList.filter(function (tag) {
          return tag.name === token;
        })[0].id;
      });
      this.props.onSelectedTagsChange(tagIds);
      this.setState({
        tagTokens: tokens
      });
    }
  }, {
    key: "onSelectedPostTypesChange",
    value: function onSelectedPostTypesChange(tokens) {
      var _this3 = this;

      var postTypeIds = tokens.map(function (token) {
        return _this3.props.postTypesList.filter(function (postType) {
          return postType.name === token;
        })[0].id;
      });
      this.props.onSelectedPostTypesChange(postTypeIds);
      this.setState({
        postTypeTokens: tokens
      });
    }
  }, {
    key: "renderEdit",
    value: function renderEdit() {
      var _this4 = this;

      var __ = wp.i18n.__;
      var tagSuggestions = this.props.tagsList.map(function (tag) {
        return tag.name;
      });
      var postTypeSuggestions = this.props.postTypesList.map(function (postType) {
        return postType.name;
      });
      var postsSuggestions = this.props.posts.map(function (post) {
        return post.title.rendered;
      });
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("h3", null, __('What style of cover do you need?', 'p4ge')), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_components_LayoutSelector_LayoutSelector__WEBPACK_IMPORTED_MODULE_8__["LayoutSelector"], {
        selectedOption: this.props.cover_type,
        onSelectedLayoutChange: this.props.onSelectedLayoutChange,
        options: [{
          label: __('Take Action Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/take_action_covers.png',
          value: 1,
          help: __('Take action covers pull the featured image, tags, have a 25 character excerpt and have a call to action button')
        }, {
          label: __('Campaign Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/campaign_covers.png',
          value: 2,
          help: __('Campaign covers pull the associated image and hashtag from the system tag definitions.')
        }, {
          label: __('Content Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/content_covers.png',
          value: 3,
          help: __('Content covers pull the image from the post.')
        }]
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["SelectControl"], {
        label: "Rows to display",
        value: this.props.covers_view,
        options: [{
          label: '1 Row',
          value: '1'
        }, {
          label: '2 Rows',
          value: '2'
        }, {
          label: 'All rows',
          value: 'all'
        }],
        onChange: this.props.onRowsChange
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["TextControl"], {
        label: "Title",
        placeholder: "Enter title",
        value: this.props.title,
        onChange: this.props.onTitleChange
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["TextareaControl"], {
        label: "Description",
        placeholder: "Enter description",
        value: this.props.description,
        onChange: this.props.onDescriptionChange
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["FormTokenField"], {
        value: this.state.tagTokens,
        suggestions: tagSuggestions,
        label: "Select Tags",
        onChange: function onChange(tokens) {
          return _this4.onSelectedTagsChange(tokens);
        },
        placeholder: "Select Tags"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("p", {
        class: "FieldHelp"
      }, "Associate this block with Actions that have specific Tags")), this.props.cover_type === 3 ? Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["FormTokenField"], {
        value: this.state.postTypeTokens,
        suggestions: postTypeSuggestions,
        label: "Post Types",
        onChange: function onChange(tokens) {
          return _this4.onSelectedPostTypesChange(tokens);
        },
        placeholder: "Select Tags"
      }) : null, this.props.cover_type === 3 && (this.props.tags.length === 0 || this.props.post_types.length === 0) ? Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("label", null, "Manual override"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["FormTokenField"], {
        value: this.props.selectedPosts,
        suggestions: postsSuggestions,
        label: "CAUTION: Adding covers manually will override the automatic functionality. DRAG & DROP: Drag and drop to reorder cover display priority.",
        onChange: function onChange(tokens) {
          return _this4.props.onSelectedPostsChange(tokens);
        },
        placeholder: "Select Tags"
      })) : null);
    }
  }, {
    key: "render",
    value: function render() {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, this.props.isSelected ? this.renderEdit() : null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_components_Preview__WEBPACK_IMPORTED_MODULE_9__["Preview"], {
        showBar: this.props.isSelected
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["ServerSideRender"], {
        block: 'planet4-blocks/covers',
        attributes: {
          cover_type: this.props.cover_type,
          covers_view: this.props.covers_view,
          tags: this.props.tags,
          post_types: this.props.post_types,
          title: this.props.title,
          description: this.props.description
        }
      })));
    }
  }]);

  return Covers;
}(react__WEBPACK_IMPORTED_MODULE_6__["Component"]);
;

/***/ }),

/***/ "./react-blocks/src/blocks/Covers/CoversBlock.js":
/*!*******************************************************!*\
  !*** ./react-blocks/src/blocks/Covers/CoversBlock.js ***!
  \*******************************************************/
/*! exports provided: CoversBlock */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "CoversBlock", function() { return CoversBlock; });
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/extends */ "./node_modules/@babel/runtime/helpers/extends.js");
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _CoversIcon_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./CoversIcon.js */ "./react-blocks/src/blocks/Covers/CoversIcon.js");
/* harmony import */ var _Covers_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Covers.js */ "./react-blocks/src/blocks/Covers/Covers.js");





var CoversBlock = function CoversBlock() {
  _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default()(this, CoversBlock);

  var registerBlockType = wp.blocks.registerBlockType;
  var withSelect = wp.data.withSelect;
  registerBlockType('planet4-blocks/covers', {
    title: 'Covers',
    icon: _CoversIcon_js__WEBPACK_IMPORTED_MODULE_3__["CoversIcon"],
    category: 'planet4-blocks',
    // Transform the shortcode into a Gutenberg block
    // this is used when a user clicks "Convert to blocks"
    // on the "Classic Editor" block
    transforms: {
      from: [{
        type: 'shortcode',
        // Shortcode tag can also be an array of shortcode aliases
        tag: 'shortcake_newcovers',
        attributes: {
          cover_type: {
            type: 'integer',
            // This `shortcode` definition will be used as a callback,
            // it is a function which expects an object with at least
            // a `named` key with `cover_type` property whose default value is 1.
            // See: https://simonsmith.io/destructuring-objects-as-function-parameters-in-es6
            shortcode: function shortcode(_ref) {
              var _ref$named$cover_type = _ref.named.cover_type,
                  cover_type = _ref$named$cover_type === void 0 ? '1' : _ref$named$cover_type;
              return cover_type;
            }
          },
          title: {
            type: 'string',
            shortcode: function shortcode(_ref2) {
              var _ref2$named$title = _ref2.named.title,
                  title = _ref2$named$title === void 0 ? '' : _ref2$named$title;
              return title;
            }
          },
          description: {
            type: 'string',
            shortcode: function shortcode(_ref3) {
              var _ref3$named$descripti = _ref3.named.description,
                  description = _ref3$named$descripti === void 0 ? '' : _ref3$named$descripti;
              return description;
            }
          }
        }
      }]
    },
    // This attributes definition mimics the one in the PHP side.
    attributes: {
      title: {
        type: 'string'
      },
      description: {
        type: 'string'
      },
      tags: {
        type: 'array',
        default: []
      },
      posts: {
        type: 'array',
        default: []
      },
      post_types: {
        type: 'array',
        default: []
      },
      covers_view: {
        type: 'string',
        default: '1'
      },
      cover_type: {
        type: 'integer',
        default: 1
      }
    },
    // withSelect is a "Higher Order Component", it works as
    // a Decorator, it will provide some basic API functionality
    // through `select`.
    edit: withSelect(function (select) {
      var tagsTaxonomy = 'post_tag';
      var postTypesTaxonomy = 'p4-page-type';
      var args = {
        hide_empty: false
      };

      var _select = select('core'),
          getEntityRecords = _select.getEntityRecords; // We should probably wrap all these in a single call,
      // or maybe use our own way of retrieving data from the
      // API, I don't know how this scales.


      var tagsList = getEntityRecords('taxonomy', tagsTaxonomy, args);
      var postTypesList = getEntityRecords('taxonomy', postTypesTaxonomy);
      var posts = getEntityRecords('postType', 'post');
      return {
        postTypesList: postTypesList,
        tagsList: tagsList,
        posts: posts
      };
    })(function (_ref4) {
      var postTypesList = _ref4.postTypesList,
          tagsList = _ref4.tagsList,
          posts = _ref4.posts,
          isSelected = _ref4.isSelected,
          attributes = _ref4.attributes,
          setAttributes = _ref4.setAttributes;

      if (!tagsList || !postTypesList || !posts) {
        return "Populating block's fields...";
      } // TO-DO: Check for posts types and posts too...


      if (!tagsList && !tagsList.length === 0) {
        return "No tags...";
      } // These methods are passed down to the
      // Covers component, they update the corresponding attribute.


      function onRowsChange(value) {
        setAttributes({
          covers_view: value
        });
      }

      function onTitleChange(value) {
        setAttributes({
          title: value
        });
      }

      function onDescriptionChange(value) {
        setAttributes({
          description: value
        });
      }

      function onSelectedTagsChange(tagIds) {
        setAttributes({
          tags: tagIds
        });
      }

      function onSelectedPostsChange(value) {
        setAttributes({
          selectedPosts: value.tokens
        });
      }

      function onSelectedPostTypesChange(postTypeIds) {
        setAttributes({
          post_types: postTypeIds
        });
      }

      function onSelectedLayoutChange(value) {
        setAttributes({
          cover_type: Number(value)
        });
      } // We pass down all the attributes to Covers as props using
      // the spread operator. Then we selectively add more
      // props.


      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__["createElement"])(_Covers_js__WEBPACK_IMPORTED_MODULE_4__["Covers"], _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default()({}, attributes, {
        isSelected: isSelected,
        tagsList: tagsList,
        postTypesList: postTypesList,
        posts: posts,
        onSelectedTagsChange: onSelectedTagsChange,
        onSelectedLayoutChange: onSelectedLayoutChange,
        onTitleChange: onTitleChange,
        onDescriptionChange: onDescriptionChange,
        onSelectedPostsChange: onSelectedPostsChange,
        onSelectedPostTypesChange: onSelectedPostTypesChange,
        onRowsChange: onRowsChange
      }));
    }),
    save: function save() {
      return null;
    }
  });
};

/***/ }),

/***/ "./react-blocks/src/blocks/Covers/CoversIcon.js":
/*!******************************************************!*\
  !*** ./react-blocks/src/blocks/Covers/CoversIcon.js ***!
  \******************************************************/
/*! exports provided: CoversIcon */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "CoversIcon", function() { return CoversIcon; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_6__);







var CoversIcon =
/*#__PURE__*/
function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default()(CoversIcon, _Component);

  function CoversIcon() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, CoversIcon);

    return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default()(this, _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(CoversIcon).apply(this, arguments));
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(CoversIcon, [{
    key: "render",
    value: function render() {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("svg", {
        xmlns: "http://www.w3.org/2000/svg",
        version: "1.1",
        x: "0px",
        y: "0px",
        viewBox: "0 0 66 82.5",
        "enable-background": "new 0 0 66 66"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("g", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("g", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M8.193,37.334l23.804-10.512v-8.007L6.099,30.093c-0.24,0.11-0.44,0.31-0.53,0.56c-0.1,0.24-0.09,0.52,0.01,0.76    L8.193,37.334z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M8.999,39.163l4.9,11.08c4.55-1.49,7.1,2.61,8.76,3.78c1.51-1.01,4.47-5.71,9.34-3.55v-21.47L8.999,39.163z     M21.059,42.853l-1,0.46c-0.5,0.23-1.1,0.02-1.33-0.49c-0.23-0.5-0.01-1.09,0.49-1.33l1-0.46c0.5-0.23,1.1-0.01,1.33,0.49    C21.779,42.033,21.559,42.623,21.059,42.853z M27.059,39.853l-1,0.46c-0.5,0.23-1.1,0.02-1.33-0.49c-0.23-0.5-0.01-1.09,0.49-1.33    l1-0.46c0.5-0.23,1.1-0.01,1.33,0.49C27.779,39.033,27.559,39.623,27.059,39.853z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M33.998,26.816l24.005,10.392l2.416-5.816c0.21-0.5-0.02-1.08-0.52-1.3L33.998,18.813V26.816z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M33.999,28.993v22.77c0.95,0.8,2.02,2.26,2.44,2.26c1.97-1.66,3.56-4.08,6.88-4.08c4.21,0,6.03,4.07,6.9,4.07    c0.1-0.087,0.722-0.298,1.25-1.09l5.77-13.87L33.999,28.993z M41.269,39.823c-0.23,0.5-0.83,0.72-1.33,0.49l-1-0.46    c-0.5-0.23-0.72-0.82-0.49-1.33c0.23-0.5,0.83-0.72,1.33-0.49l1,0.46C41.279,38.733,41.499,39.323,41.269,39.823z M47.269,42.823    c-0.23,0.5-0.83,0.72-1.33,0.49l-1-0.46c-0.5-0.23-0.72-0.82-0.49-1.33c0.23-0.5,0.82-0.72,1.33-0.49l1,0.46    C47.279,41.733,47.499,42.323,47.269,42.823z"
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("g", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M37.999,2.983v5.63h-10v-5.63c0-1.1,0.9-2,2-2h6C37.099,0.983,37.999,1.883,37.999,2.983z"
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M48.319,13.823c-0.38-1.86-2.03-3.21-3.93-3.21h-22.78c-1.9,0-3.55,1.35-3.92,3.21l-1.98,9.91   c5.27-2.31,11.32-4.94,17.29-7.54c1.02,0.45,9.84,4.29,17.29,7.54L48.319,13.823z M24.999,17.233h-2c-0.55,0-1-0.45-1-1s0.45-1,1-1   h2c0.55,0,1,0.45,1,1S25.549,17.233,24.999,17.233z M42.999,17.233h-2c-0.55,0-1-0.45-1-1s0.45-1,1-1h2c0.55,0,1,0.45,1,1   S43.549,17.233,42.999,17.233z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M64,62.982c-1.207,0-1.78-0.597-2.69-1.653c-2.737-3.176-5.671-3.178-8.41,0c-1.901,2.205-3.401,2.296-5.378,0   c-2.735-3.175-5.668-3.179-8.403,0c-1.938,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.668-3.178-8.404,0   c-1.939,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.667-3.178-8.402,0c-1.939,2.251-3.429,2.254-5.37,0   c-0.9-1.046-2.02-2.348-4.2-2.348c-0.552,0-1,0.448-1,1s0.448,1,1,1c1.204,0,1.776,0.597,2.684,1.652   c2.733,3.177,5.665,3.178,8.401,0c1.939-2.252,3.43-2.254,5.371,0c2.735,3.177,5.667,3.178,8.402,0   c1.939-2.252,3.432-2.254,5.373,0c2.734,3.176,5.667,3.179,8.402,0c1.938-2.252,3.43-2.254,5.372,0c2.736,3.176,5.67,3.178,8.409,0   c1.942-2.252,3.437-2.255,5.381,0c0.901,1.046,2.023,2.347,4.205,2.347c0.552,0,1-0.448,1-1C65,63.43,64.552,62.982,64,62.982z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M64,55.982c-1.207,0-1.78-0.597-2.69-1.653c-2.737-3.176-5.671-3.178-8.41,0c-1.901,2.205-3.401,2.296-5.378,0   c-2.735-3.175-5.668-3.179-8.403,0c-1.938,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.668-3.178-8.404,0   c-1.939,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.667-3.178-8.402,0c-1.939,2.251-3.429,2.254-5.37,0   c-0.9-1.046-2.02-2.348-4.2-2.348c-0.552,0-1,0.448-1,1s0.448,1,1,1c1.204,0,1.776,0.597,2.684,1.652   c2.733,3.177,5.665,3.178,8.401,0c1.939-2.252,3.43-2.254,5.371,0c2.735,3.177,5.667,3.178,8.402,0   c1.939-2.252,3.432-2.254,5.373,0c2.734,3.176,5.667,3.179,8.402,0c1.938-2.252,3.43-2.254,5.372,0c2.736,3.176,5.67,3.178,8.409,0   c1.942-2.252,3.437-2.255,5.381,0c0.901,1.046,2.023,2.347,4.205,2.347c0.552,0,1-0.448,1-1S64.552,55.982,64,55.982z"
      })));
    }
  }]);

  return CoversIcon;
}(react__WEBPACK_IMPORTED_MODULE_6__["Component"]);

/***/ }),

/***/ "./react-blocks/src/blocks/Submenu/Submenu.js":
/*!****************************************************!*\
  !*** ./react-blocks/src/blocks/Submenu/Submenu.js ***!
  \****************************************************/
/*! exports provided: Submenu */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Submenu", function() { return Submenu; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _components_LayoutSelector_LayoutSelector__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../../components/LayoutSelector/LayoutSelector */ "./react-blocks/src/components/LayoutSelector/LayoutSelector.js");
/* harmony import */ var _components_Preview__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../../components/Preview */ "./react-blocks/src/components/Preview.js");










var Submenu =
/*#__PURE__*/
function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default()(Submenu, _Component);

  function Submenu(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, Submenu);

    _this = _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default()(this, _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(Submenu).call(this, props));
    _this.state = {
      tagTokens: [],
      postTypeTokens: []
    };
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(Submenu, [{
    key: "renderEdit",
    value: function renderEdit() {
      var __ = wp.i18n.__;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("h3", null, __('What style of menu do you need?', 'p4ge')), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_components_LayoutSelector_LayoutSelector__WEBPACK_IMPORTED_MODULE_8__["LayoutSelector"], {
        selectedOption: this.props.submenu_style,
        onSelectedLayoutChange: this.props.onSelectedLayoutChange,
        options: [{
          label: __('Long full-width', 'p4ge'),
          image: window.p4ge_vars.home + 'images/submenu-long.jpg',
          value: 1,
          help: __('Use: on long pages (more than 5 screens) when list items are long (+ 10 words)<br>No max items<br>recommended.')
        }, {
          label: __('Short full-width', 'p4ge'),
          image: window.p4ge_vars.home + 'images/submenu-short.jpg',
          value: 2,
          help: __('Use: on long pages (more than 5 screens) when list items are short (up to 5 words)<br>No max items<br>recommended.')
        }, {
          label: __('Short sidebar', 'p4ge'),
          image: window.p4ge_vars.home + 'images/submenu-sidebar.jpg',
          value: 3,
          help: __('Use: on long pages (more than 5 screens) when list items are short (up to 10 words)<br>Max items<br>recommended: 9')
        }]
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["TextControl"], {
        label: "Submenu Title",
        placeholder: "Enter title",
        value: this.props.title,
        onChange: this.props.onTitleChange
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("br", null), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, "Level 1"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["SelectControl"], {
        label: "Submenu item",
        help: "Submenu item",
        value: this.props.heading1,
        options: [{
          label: 'None',
          value: '0'
        }, {
          label: 'Heading 1',
          value: '1'
        }, {
          label: 'Heading 2',
          value: '2'
        }],
        onChange: this.props.onHeadingChange,
        className: "block-attribute-wrapper"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["CheckboxControl"], {
        label: "Link",
        help: "Submenu item",
        value: this.props.link1,
        checked: this.props.link1,
        onChange: this.props.onRowsChange,
        className: "block-attribute-wrapper"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["SelectControl"], {
        label: "List style",
        help: "List style",
        value: this.props.style1,
        options: [{
          label: 'None',
          value: 'none'
        }, {
          label: 'Bullet',
          value: 'bullet'
        }, {
          label: 'Number',
          value: 'number'
        }],
        onChange: this.props.onRowsChange,
        className: "block-attribute-wrapper"
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, "Level 2"), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["SelectControl"], {
        label: "Submenu item",
        help: "Submenu item",
        value: this.props.heading2,
        options: [{
          label: 'None',
          value: '0'
        }, {
          label: 'Heading 1',
          value: '1'
        }, {
          label: 'Heading 2',
          value: '2'
        }],
        onChange: this.props.onRowsChange,
        class: "sdaf",
        className: "block-attribute-wrapper"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["CheckboxControl"], {
        label: "Link",
        help: "Submenu item",
        value: this.props.link1,
        checked: this.props.link1,
        onChange: this.props.onRowsChange,
        className: "block-attribute-wrapper"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__["SelectControl"], {
        label: "List style",
        help: "List style",
        value: this.props.style2,
        options: [{
          label: 'None',
          value: 'none'
        }, {
          label: 'Bullet',
          value: 'bullet'
        }, {
          label: 'Number',
          value: 'number'
        }],
        onChange: this.props.onRowsChange,
        className: "block-attribute-wrapper"
      })));
    }
  }, {
    key: "render",
    value: function render() {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", null, this.props.isSelected ? this.renderEdit() : null);
    }
  }]);

  return Submenu;
}(react__WEBPACK_IMPORTED_MODULE_6__["Component"]);
;

/***/ }),

/***/ "./react-blocks/src/blocks/Submenu/SubmenuBlock.js":
/*!*********************************************************!*\
  !*** ./react-blocks/src/blocks/Submenu/SubmenuBlock.js ***!
  \*********************************************************/
/*! exports provided: SubmenuBlock */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "SubmenuBlock", function() { return SubmenuBlock; });
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/extends */ "./node_modules/@babel/runtime/helpers/extends.js");
/* harmony import */ var _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _SubmenuIcon_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./SubmenuIcon.js */ "./react-blocks/src/blocks/Submenu/SubmenuIcon.js");
/* harmony import */ var _Submenu_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Submenu.js */ "./react-blocks/src/blocks/Submenu/Submenu.js");





var SubmenuBlock = function SubmenuBlock() {
  _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default()(this, SubmenuBlock);

  var registerBlockType = wp.blocks.registerBlockType;
  var withSelect = wp.data.withSelect;
  registerBlockType('planet4-blocks/submenu', {
    title: 'Submenu',
    icon: _SubmenuIcon_js__WEBPACK_IMPORTED_MODULE_3__["SubmenuIcon"],
    category: 'planet4-blocks',
    transforms: {
      from: [{
        type: 'shortcode',
        // Shortcode tag can also be an array of shortcode aliases
        tag: 'shortcake_submenu',
        attributes: {
          submenu_style: {
            type: 'integer',
            shortcode: function shortcode(_ref) {
              var _ref$named$submenu_st = _ref.named.submenu_style,
                  submenu_style = _ref$named$submenu_st === void 0 ? '1' : _ref$named$submenu_st;
              return submenu_style;
            }
          },
          title: {
            type: 'string',
            shortcode: function shortcode(_ref2) {
              var _ref2$named$title = _ref2.named.title,
                  title = _ref2$named$title === void 0 ? '' : _ref2$named$title;
              return title;
            }
          } // description: {
          // 	type: 'string',
          // 	shortcode: ( { named: { description = '' } } ) => description,
          // },

        }
      }]
    },
    attributes: {
      heading1: {
        type: 'integer',
        default: 0
      },
      link1: {
        type: 'boolean'
      },
      style1: {
        type: 'string',
        default: 'none'
      },
      heading2: {
        type: 'integer',
        default: 0
      },
      link2: {
        type: 'boolean'
      },
      submenu_style: {
        type: 'integer',
        default: 1
      },
      title: {
        type: 'string'
      }
    },
    edit: withSelect(function (select) {
      var tagsTaxonomy = 'post_tag';
      var postTypesTaxonomy = 'p4-page-type';
      var args = {
        hide_empty: false
      };

      var _select = select('core'),
          getEntityRecords = _select.getEntityRecords; // const tagsList = getEntityRecords( 'taxonomy', tagsTaxonomy, args );


      var postTypesList = getEntityRecords('taxonomy', postTypesTaxonomy);
      var posts = getEntityRecords('postType', 'post');
      return {// postTypesList,
        // tagsList,
        // posts
      };
    })(function (_ref3) {
      var isSelected = _ref3.isSelected,
          attributes = _ref3.attributes,
          setAttributes = _ref3.setAttributes;

      function onRowsChange(value) {
        setAttributes({
          covers_view: value
        });
      }

      function onTitleChange(value) {
        setAttributes({
          title: value
        });
      }

      function onHeadingChange(value) {
        setAttributes({
          heading1: value
        });
      }

      function onSelectedLayoutChange(value) {
        setAttributes({
          cover_type: Number(value)
        });
      }

      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__["createElement"])(_Submenu_js__WEBPACK_IMPORTED_MODULE_4__["Submenu"], _babel_runtime_helpers_extends__WEBPACK_IMPORTED_MODULE_0___default()({}, attributes, {
        isSelected: isSelected // tagsList={ tagsList }
        // postTypesList={ postTypesList }
        // posts={ posts }
        // onSelectedTagsChange={ onSelectedTagsChange }
        ,
        onSelectedLayoutChange: onSelectedLayoutChange,
        onTitleChange: onTitleChange,
        onHeadingChange: onHeadingChange,
        onRowsChange: onRowsChange
      }));
    }),
    save: function save() {
      return null;
    }
  });
};

/***/ }),

/***/ "./react-blocks/src/blocks/Submenu/SubmenuIcon.js":
/*!********************************************************!*\
  !*** ./react-blocks/src/blocks/Submenu/SubmenuIcon.js ***!
  \********************************************************/
/*! exports provided: SubmenuIcon */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "SubmenuIcon", function() { return SubmenuIcon; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_6__);







var SubmenuIcon =
/*#__PURE__*/
function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default()(SubmenuIcon, _Component);

  function SubmenuIcon() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, SubmenuIcon);

    return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default()(this, _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(SubmenuIcon).apply(this, arguments));
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(SubmenuIcon, [{
    key: "render",
    value: function render() {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("svg", {
        xmlns: "http://www.w3.org/2000/svg",
        version: "1.1",
        x: "0px",
        y: "0px",
        viewBox: "0 0 66 82.5",
        "enable-background": "new 0 0 66 66"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("g", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("g", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M8.193,37.334l23.804-10.512v-8.007L6.099,30.093c-0.24,0.11-0.44,0.31-0.53,0.56c-0.1,0.24-0.09,0.52,0.01,0.76    L8.193,37.334z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M8.999,39.163l4.9,11.08c4.55-1.49,7.1,2.61,8.76,3.78c1.51-1.01,4.47-5.71,9.34-3.55v-21.47L8.999,39.163z     M21.059,42.853l-1,0.46c-0.5,0.23-1.1,0.02-1.33-0.49c-0.23-0.5-0.01-1.09,0.49-1.33l1-0.46c0.5-0.23,1.1-0.01,1.33,0.49    C21.779,42.033,21.559,42.623,21.059,42.853z M27.059,39.853l-1,0.46c-0.5,0.23-1.1,0.02-1.33-0.49c-0.23-0.5-0.01-1.09,0.49-1.33    l1-0.46c0.5-0.23,1.1-0.01,1.33,0.49C27.779,39.033,27.559,39.623,27.059,39.853z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M33.998,26.816l24.005,10.392l2.416-5.816c0.21-0.5-0.02-1.08-0.52-1.3L33.998,18.813V26.816z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M33.999,28.993v22.77c0.95,0.8,2.02,2.26,2.44,2.26c1.97-1.66,3.56-4.08,6.88-4.08c4.21,0,6.03,4.07,6.9,4.07    c0.1-0.087,0.722-0.298,1.25-1.09l5.77-13.87L33.999,28.993z M41.269,39.823c-0.23,0.5-0.83,0.72-1.33,0.49l-1-0.46    c-0.5-0.23-0.72-0.82-0.49-1.33c0.23-0.5,0.83-0.72,1.33-0.49l1,0.46C41.279,38.733,41.499,39.323,41.269,39.823z M47.269,42.823    c-0.23,0.5-0.83,0.72-1.33,0.49l-1-0.46c-0.5-0.23-0.72-0.82-0.49-1.33c0.23-0.5,0.82-0.72,1.33-0.49l1,0.46    C47.279,41.733,47.499,42.323,47.269,42.823z"
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("g", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M37.999,2.983v5.63h-10v-5.63c0-1.1,0.9-2,2-2h6C37.099,0.983,37.999,1.883,37.999,2.983z"
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M48.319,13.823c-0.38-1.86-2.03-3.21-3.93-3.21h-22.78c-1.9,0-3.55,1.35-3.92,3.21l-1.98,9.91   c5.27-2.31,11.32-4.94,17.29-7.54c1.02,0.45,9.84,4.29,17.29,7.54L48.319,13.823z M24.999,17.233h-2c-0.55,0-1-0.45-1-1s0.45-1,1-1   h2c0.55,0,1,0.45,1,1S25.549,17.233,24.999,17.233z M42.999,17.233h-2c-0.55,0-1-0.45-1-1s0.45-1,1-1h2c0.55,0,1,0.45,1,1   S43.549,17.233,42.999,17.233z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M64,62.982c-1.207,0-1.78-0.597-2.69-1.653c-2.737-3.176-5.671-3.178-8.41,0c-1.901,2.205-3.401,2.296-5.378,0   c-2.735-3.175-5.668-3.179-8.403,0c-1.938,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.668-3.178-8.404,0   c-1.939,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.667-3.178-8.402,0c-1.939,2.251-3.429,2.254-5.37,0   c-0.9-1.046-2.02-2.348-4.2-2.348c-0.552,0-1,0.448-1,1s0.448,1,1,1c1.204,0,1.776,0.597,2.684,1.652   c2.733,3.177,5.665,3.178,8.401,0c1.939-2.252,3.43-2.254,5.371,0c2.735,3.177,5.667,3.178,8.402,0   c1.939-2.252,3.432-2.254,5.373,0c2.734,3.176,5.667,3.179,8.402,0c1.938-2.252,3.43-2.254,5.372,0c2.736,3.176,5.67,3.178,8.409,0   c1.942-2.252,3.437-2.255,5.381,0c0.901,1.046,2.023,2.347,4.205,2.347c0.552,0,1-0.448,1-1C65,63.43,64.552,62.982,64,62.982z"
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("path", {
        d: "M64,55.982c-1.207,0-1.78-0.597-2.69-1.653c-2.737-3.176-5.671-3.178-8.41,0c-1.901,2.205-3.401,2.296-5.378,0   c-2.735-3.175-5.668-3.179-8.403,0c-1.938,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.668-3.178-8.404,0   c-1.939,2.252-3.43,2.254-5.371,0c-2.735-3.177-5.667-3.178-8.402,0c-1.939,2.251-3.429,2.254-5.37,0   c-0.9-1.046-2.02-2.348-4.2-2.348c-0.552,0-1,0.448-1,1s0.448,1,1,1c1.204,0,1.776,0.597,2.684,1.652   c2.733,3.177,5.665,3.178,8.401,0c1.939-2.252,3.43-2.254,5.371,0c2.735,3.177,5.667,3.178,8.402,0   c1.939-2.252,3.432-2.254,5.373,0c2.734,3.176,5.667,3.179,8.402,0c1.938-2.252,3.43-2.254,5.372,0c2.736,3.176,5.67,3.178,8.409,0   c1.942-2.252,3.437-2.255,5.381,0c0.901,1.046,2.023,2.347,4.205,2.347c0.552,0,1-0.448,1-1S64.552,55.982,64,55.982z"
      })));
    }
  }]);

  return SubmenuIcon;
}(react__WEBPACK_IMPORTED_MODULE_6__["Component"]);

/***/ }),

/***/ "./react-blocks/src/components/LayoutSelector/LayoutSelector.js":
/*!**********************************************************************!*\
  !*** ./react-blocks/src/components/LayoutSelector/LayoutSelector.js ***!
  \**********************************************************************/
/*! exports provided: LayoutSelector */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "LayoutSelector", function() { return LayoutSelector; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");
/* harmony import */ var _babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__);









var LayoutSelector =
/*#__PURE__*/
function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default()(LayoutSelector, _Component);

  function LayoutSelector(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, LayoutSelector);

    _this = _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default()(this, _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(LayoutSelector).call(this, props));
    _this.state = {
      selectedOption: props.selectedOption
    };
    _this.setSelected = _this.setSelected.bind(_babel_runtime_helpers_assertThisInitialized__WEBPACK_IMPORTED_MODULE_4___default()(_this));
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(LayoutSelector, [{
    key: "setSelected",
    value: function setSelected(option) {
      this.setState({
        selectedOption: option
      });
      this.props.onSelectedLayoutChange(option);
    }
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;

      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__["createElement"])("div", {
        className: "LayoutSelector"
      }, this.props.options.map(function (layoutOption) {
        return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__["createElement"])("label", {
          className: "LayoutOption"
        }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__["createElement"])("div", {
          style: {
            display: 'flex'
          }
        }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_8__["RadioControl"], {
          name: 'layoutOption',
          selected: Number(_this2.state.selectedOption),
          options: [{
            value: Number(layoutOption.value)
          }],
          onChange: _this2.setSelected
        }), layoutOption.label), layoutOption.image ? Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__["createElement"])("img", {
          src: layoutOption.image
        }) : null, layoutOption.help ? Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__["createElement"])("p", {
          className: "help"
        }, layoutOption.help) : null);
      }));
    }
  }]);

  return LayoutSelector;
}(react__WEBPACK_IMPORTED_MODULE_7__["Component"]);

/***/ }),

/***/ "./react-blocks/src/components/Preview.js":
/*!************************************************!*\
  !*** ./react-blocks/src/components/Preview.js ***!
  \************************************************/
/*! exports provided: Preview */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Preview", function() { return Preview; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_6__);







var Preview =
/*#__PURE__*/
function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default()(Preview, _Component);

  function Preview() {
    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, Preview);

    return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default()(this, _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(Preview).apply(this, arguments));
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(Preview, [{
    key: "render",
    value: function render() {
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", {
        className: "Preview"
      }, this.props.showBar ? Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__["createElement"])("div", {
        className: "PreviewBar"
      }, "Preview") : null, this.props.children);
    }
  }]);

  return Preview;
}(react__WEBPACK_IMPORTED_MODULE_6__["Component"]);

/***/ }),

/***/ "./react-blocks/src/editorIndex.js":
/*!*****************************************!*\
  !*** ./react-blocks/src/editorIndex.js ***!
  \*****************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks_Covers_CoversBlock__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./blocks/Covers/CoversBlock */ "./react-blocks/src/blocks/Covers/CoversBlock.js");
/* harmony import */ var _blocks_Submenu_SubmenuBlock__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./blocks/Submenu/SubmenuBlock */ "./react-blocks/src/blocks/Submenu/SubmenuBlock.js");


var coversBlock = new _blocks_Covers_CoversBlock__WEBPACK_IMPORTED_MODULE_0__["CoversBlock"]();
var submenuBlock = new _blocks_Submenu_SubmenuBlock__WEBPACK_IMPORTED_MODULE_1__["SubmenuBlock"]();

/***/ }),

/***/ "@wordpress/components":
/*!*********************************************!*\
  !*** external {"this":["wp","components"]} ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["components"]; }());

/***/ }),

/***/ "@wordpress/element":
/*!******************************************!*\
  !*** external {"this":["wp","element"]} ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["element"]; }());

/***/ }),

/***/ "react":
/*!*********************************!*\
  !*** external {"this":"React"} ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["React"]; }());

/***/ })

/******/ });
//# sourceMappingURL=editorIndex.js.map