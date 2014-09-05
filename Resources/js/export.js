/**
 * @fileoverview This file is the entry point for the compiler.
 *
 * You can compile this script by running (assuming you have JMSGoogleClosureBundle installed):
 *
 *    php app/console plovr:build @NovawayFileManagementBundle/compile.js
 */

goog.require('novaway.FileManager');

goog.exportSymbol('novaway.FileManager', novaway.FileManager);
goog.exportSymbol('novaway.FileManager.setData', function(data) {
    var manager = novaway.FileManager.getInstance();
    manager.setWebPath(/** @type {string} */ (data['webpath']));
    manager.setImageFormats(/** @type {Object.<string, Object.<string, string|number|boolean>>} */ (data['image_formats']));
});
goog.exportProperty(novaway.FileManager, 'getInstance', novaway.FileManager.getInstance);
goog.exportProperty(novaway.FileManager.prototype, 'getPath', novaway.FileManager.prototype.getPath);
goog.exportProperty(novaway.FileManager.prototype, 'transformPathWithFormat', novaway.FileManager.prototype.transformPathWithFormat);
goog.exportProperty(novaway.FileManager.prototype, 'getFilePath', novaway.FileManager.prototype.getFilePath);
goog.exportProperty(novaway.FileManager.prototype, 'setWebPath', novaway.FileManager.prototype.setWebPath);
goog.exportProperty(novaway.FileManager.prototype, 'setImageFormats', novaway.FileManager.prototype.setImageFormats);
