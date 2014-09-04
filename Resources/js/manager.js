goog.provide('novaway.FileManager');

/**
 * @constructor
 * @param {string} webpath
 * @param {Object.<string, Object>} imageDefinitions
 */
novaway.FileManager = function(webpath, imageDefinitions) {
    this.webPath = webpath || "";
    this.imageDefinitions = imageDefinitions || {};
};
goog.addSingletonGetter(novaway.FileManager);

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

    var filePath = this.getFilePath(obj, propertyName);
    return this.transformPathWithFormat(filePath, format);
};

/**
 * @param {string} path
 * @param {string} format
 * @returns {string|undefined}
 */
novaway.FileManager.prototype.transformPathWithFormat = function(path, format) {
    if (!(format in this.imageDefinitions)) {
        return undefined;
    }

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
 * @param {Object.<string, Object>} definitions
 */
novaway.FileManager.prototype.setImageDefinitions = function(definitions) {
    this.imageDefinitions = definitions;
};
