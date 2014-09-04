/**
 * Portions of this code are from the Google Closure Library,
 * received from the Closure Authors under the Apache 2.0 license.
 *
 * All other code is (C) Novaway and subject to the MIT license.
 */
(function() {var a=this;function g(b,c){var d=b.split("."),e=a;d[0]in e||!e.execScript||e.execScript("var "+d[0]);for(var f;d.length&&(f=d.shift());)d.length||void 0===c?e=e[f]?e[f]:e[f]={}:e[f]=c};function h(b,c){this.h=b||"";this.c=c||{}}h.b=function(){return h.d?h.d:h.d=new h};h.prototype.i=function(b,c,d){return d?this.g(this.a(b,c),d):this.a(b,c)};h.prototype.g=function(b,c){return c in this.c?b.replace("{-imgformat-}",c):void 0};h.prototype.a=function(b,c){return this.h+b[c]+""};h.prototype.f=function(b){this.h=b};h.prototype.e=function(b){this.c=b};g("novaway.FileManager",h);g("novaway.FileManager.setData",function(b){var c=h.b();c.f(b.webpath);c.e(b.image_definitions)});h.getInstance=h.b;h.prototype.getPath=h.prototype.i;h.prototype.transformPathWithFormat=h.prototype.g;h.prototype.getFilePath=h.prototype.a;h.prototype.setWebPath=h.prototype.f;h.prototype.setImageDefinitions=h.prototype.e;window.FileManager=h.b();})();