!function(e){var t={};function o(a){if(t[a])return t[a].exports;var n=t[a]={i:a,l:!1,exports:{}};return e[a].call(n.exports,n,n.exports,o),n.l=!0,n.exports}o.m=e,o.c=t,o.d=function(e,t,a){o.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},o.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},o.t=function(e,t){if(1&t&&(e=o(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(o.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)o.d(a,n,function(t){return e[t]}.bind(null,n));return a},o.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="",o(o.s=3)}([function(e,t){e.exports=function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}},function(e,t){function o(e,t){for(var o=0;o<t.length;o++){var a=t[o];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}e.exports=function(e,t,a){return t&&o(e.prototype,t),a&&o(e,a),e}},,function(e,t,o){"use strict";o.r(t);var a=o(0),n=o.n(a),r=o(1),c=o.n(r);jQuery(document).ready((function(e){window.WC_Facebook_Google_Product_Category_Fields=function(){function t(o,a){var r=this;n()(this,t),this.categories=o,this.input_id=a;var c=e("#"+this.input_id);e('<div id="wc-facebook-google-product-category-fields"></div>').insertBefore(c).on("change","select.wc-facebook-google-product-category-select",(function(t){r.onChange(e(t.target))})),this.addInitialSelects(c.val());var i=this.globalsHolder().enhanced_attribute_optional_selector;void 0!==i&&e("#"+i).on("change",(function(){e(".wc-facebook-enhanced-catalog-attribute-optional-row").toggleClass("hidden",!e(this).prop("checked"))}))}return c()(t,[{key:"globalsHolder",value:function(){return"undefined"!=typeof facebook_for_woocommerce_product_categories?facebook_for_woocommerce_product_categories:"undefined"!=typeof facebook_for_woocommerce_settings_sync?facebook_for_woocommerce_settings_sync:facebook_for_woocommerce_products_admin}},{key:"getPageType",value:function(){return"undefined"!=typeof facebook_for_woocommerce_product_categories?0===e("input[name=tag_ID]").length?this.globalsHolder().enhanced_attribute_page_type_add_category:this.globalsHolder().enhanced_attribute_page_type_edit_category:this.globalsHolder().enhanced_attribute_page_type_edit_product}},{key:"addInitialSelects",value:function(e){var t=this;if(e){this.getSelectedCategoryIds(e).forEach((function(e){t.addSelect(t.getOptions(e[1]),e[0])}));var o=this.getOptions(e);Object.keys(o).length&&this.addSelect(o)}else this.addSelect(this.getOptions()),this.addSelect({})}},{key:"requestAttributesIfValid",value:function(){if("true"===e("#wc_facebook_can_show_enhanced_catalog_attributes_id").val()&&(e(".wc-facebook-enhanced-catalog-attribute-row").remove(),this.isValid())){var t="#"+this.input_id,o=e(t).parents("div.form-field"),a=this.globalsHolder().enhanced_attribute_optional_selector;this.getPageType()===this.globalsHolder().enhanced_attribute_page_type_edit_category?o=e(t).parents("tr.form-field"):this.getPageType()===this.globalsHolder().enhanced_attribute_page_type_edit_product&&(o=e(t).parents("p.form-field")),e.get(this.globalsHolder().ajax_url,{action:"wc_facebook_enhanced_catalog_attributes",security:"",selected_category:e(t).val(),tag_id:parseInt(e("input[name=tag_ID]").val(),10),taxonomy:e("input[name=taxonomy]").val(),item_id:parseInt(e("input[name=post_ID]").val(),10),page_type:this.getPageType()},(function(t){var n=e(t);e("#"+a,n).on("change",(function(){e(".wc-facebook-enhanced-catalog-attribute-optional-row").toggleClass("hidden",!e(this).prop("checked"))})),n.insertAfter(o),e(document.body).trigger("init_tooltips")}))}}},{key:"onChange",value:function(t){t.hasClass("locked")&&t.closest(".wc-facebook-google-product-category-field").nextAll().remove();var o=t.val();if(o){var a=this.getOptions(o);Object.keys(a).length&&this.addSelect(a)}else(o=t.closest("#wc-facebook-google-product-category-fields").find(".wc-facebook-google-product-category-select").not(t).last().val())||this.addSelect({});e("#"+this.input_id).val(o),this.requestAttributesIfValid()}},{key:"isValid",value:function(){return e(".wc-facebook-google-product-category-select").filter((function(t,o){return""!==e(o).val()})).length>=2}},{key:"addSelect",value:function(t,o){var a=e("#wc-facebook-google-product-category-fields"),n=a.find(".wc-facebook-google-product-category-select"),r=e('<select class="wc-enhanced-select wc-facebook-google-product-category-select"></select>');n.addClass("locked"),a.append(e('<div class="wc-facebook-google-product-category-field" style="margin-bottom: 16px">').append(r)),r.attr("data-placeholder",this.getSelectPlaceholder(n,t)).append(e('<option value=""></option>')),Object.keys(t).forEach((function(o){r.append(e('<option value="'+o+'">'+t[o]+"</option>"))})),r.val(o).select2({allowClear:!0})}},{key:"getSelectPlaceholder",value:function(e,t){return 0===e.length?facebook_for_woocommerce_google_product_category.i18n.top_level_dropdown_placeholder:1===e.length&&0===Object.keys(t).length?facebook_for_woocommerce_google_product_category.i18n.second_level_empty_dropdown_placeholder:facebook_for_woocommerce_google_product_category.i18n.general_dropdown_placeholder}},{key:"getOptions",value:function(e){return void 0===e||""===e?this.getTopLevelOptions():void 0===this.categories[e]||void 0===this.categories[e].options?[]:this.categories[e].options}},{key:"getTopLevelOptions",value:function(){var e=this,t={};return Object.keys(this.categories).forEach((function(o){e.categories[o].parent||(t[o]=e.categories[o].label)})),t}},{key:"getSelectedCategoryIds",value:function(e){var t=[];do{void 0!==this.categories[e]&&(t.push([e,this.categories[e].parent]),e=this.categories[e].parent)}while(""!==e);return t.reverse()}}]),t}()}))}]);