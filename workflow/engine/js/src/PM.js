var PM = PM || {};
(function() {
    PM.version = '3.0.1.8';
}());

// Overwrite the global ajax timeout
try {
    if (parent.ext_ajax_timeout) {
        Ext.Ajax.timeout = parent.ext_ajax_timeout;
    }
} catch (error) {
    // Nothing to_do
}
