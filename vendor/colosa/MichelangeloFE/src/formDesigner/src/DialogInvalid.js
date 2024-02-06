(function () {
    var DialogInvalid = function (appendTo, property, type, config) {
        this.property = property;
        this.type = type;
        this.config = config || null;
        this.onAccept = new Function();
        this.onClose = new Function();
        DialogInvalid.prototype.init.call(this, appendTo);
    };
    DialogInvalid.prototype.init = function (appendTo) {
        var that = this,
            configDialog = this.getErrorMessage(this.type, this.property, this.config);
        this.accept = $("<a href='#' class='fd-button fd-button-success'>" + "Ok".translate() + "</a>");
        this.accept.on("click", function () {
            that.onAccept();
            return false;
        });
        this.buttons = $("<div class='fd-button-panel'><div></div></div>");
        this.buttons.find("div:nth-child(1)").append(this.accept);

        this.dialog = $("<div title='" + configDialog.title + "'></div>");
        this.dialog.dialog({
            appendTo: appendTo ? appendTo : document.body,
            modal: true,
            autoOpen: true,
            width: 470,
            height: 170,
            resizable: false,
            close: function (event, ui) {
                that.onClose();
                that.dialog.remove();
            }
        });
        FormDesigner.main.DialogStyle(this.dialog, "alert");

        this.dialog.append("<div style='font-size:14px;margin:20px;'>" +
            configDialog.message +
            "</div>");
        this.dialog.append(this.buttons);
        this.accept.focus();
    };
    /**
     * Get error message
     * @param type
     * @param property
     * @param config
     * @returns {Object}
     */
    DialogInvalid.prototype.getErrorMessage = function (type, property, config) {
        var conf = {};
        conf.title = (config) ? this.config.title.translate() : 'Errors'.translate();
        switch (type) {
            case 'required':
                conf.message = "The ".translate() + property + " is required.".translate();
                break;
            case 'invalid':
                conf.message = "The ".translate() + property + " is invalid.".translate();
                break;
            case 'duplicated':
                conf.message = "The ".translate() + property + " is duplicated.".translate();
                break;
            case 'custom':
                conf.message = (config) ? config.msg.translate() : '';
                break;
        }
        return conf;
    };
    FormDesigner.extendNamespace('FormDesigner.main.DialogInvalid', DialogInvalid);
}());
