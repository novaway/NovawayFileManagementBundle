{
    "id": "manager",
    "paths": ["../../js"],
    "mode": "ADVANCED",
    "level": "VERBOSE",
    "inputs": "../../js/export.js",

    "define": {
        "goog.DEBUG": false
    },

    "type-prefixes-to-strip": ["goog.debug", "goog.asserts", "goog.assert", "console"],
    "name-suffixes-to-strip": ["logger", "logger_"],

    "output-file": "../../public/js/filemanager.js",
    "output-wrapper": "/**\n * Portions of this code are from the Google Closure Library,\n * received from the Closure Authors under the Apache 2.0 license.\n *\n * All other code is (C) Novaway and subject to the MIT license.\n */\n(function() {%output%})();",

    "pretty-print": false,
    "debug": false
}
