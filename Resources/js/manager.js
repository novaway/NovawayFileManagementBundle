goog.provide('novaway.FileManager');

goog.require('goog.array');

/**
 * @constructor
 * @param {string} webpath
 * @param {Object.<string, Object>} imageFormats
 */
novaway.FileManager = function(webpath, imageFormats) {
    this.webPath = webpath || "";
    this.imageFormats = imageFormats || {};
};

/**
 * @param {Object.<string, string|number|boolean>} obj
 * @param {string} propertyName
 * @param {string} format
 * @returns {string|undefined}
 */
novaway.FileManager.prototype.getPath = function(obj, propertyName, format) {
    if (!format) {
        return this.getFilePath(obj, propertyName);
    }

    if (!goog.array.contains(this.imageFormats[propertyName], format)) {
        throw new Error('The format "' + format + '" does not exist.');
    }

    var filePath = this.getFilePath(obj, propertyName);
    return this.transformPathWithFormat(filePath, format);
};

/**
 * @param {string} path
 * @param {string} format
 * @returns {string|undefined}
 */
novaway.FileManager.prototype.transformPathWithFormat = function(path, format) {
    return path.replace('{-imgformat-}', format);
};

/**
 * @param {Object.<string, string|number|boolean>} obj
 * @param {string} propertyName
 * @returns {string}
 */
novaway.FileManager.prototype.getFilePath = function(obj, propertyName) {
    return this.webPath + obj[propertyName] + "";
};

/**
 * @param {string} webpath
 */
novaway.FileManager.prototype.setWebPath = function(webpath) {
    this.webPath = webpath;
};

/**
 * @param {Object.<string, Object>} formats
 */
novaway.FileManager.prototype.setImageFormats = function(formats) {
    this.imageFormats = formats;
};
