(function () {
    /**
     * Represents an item in the list of controls.
     *
     * @param {object} control
     * @returns {NoticeL#1.Notice}
     * @constructor
     */
    var Notice = function () {
        Notice.prototype.init.call(this);
    };
    /**
     * Initialize the class.
     */
    Notice.prototype.init = function () {
        var element;
        this.message = $("<div></div>");
        element = $("<div class='mafe-alert'></div>");
        element.append('<button class="button-close"><span class="fa fa-times"></span></button>')
            .append(this.message)
            .on('click', '.button-close', function () {
                element.fadeOut();
            });
        element.hide();
        this.body = element;
    };
    /**
     * Show message.
     * @param {type} message
     */
    Notice.prototype.show = function (message) {
        this.message.empty();
        this.message.append(message);
        this.body.show();
    };
    /**
     * Close message.
     */
    Notice.prototype.close = function () {
        this.body.fadeOut();
    };
    FormDesigner.extendNamespace('FormDesigner.main.Notice', Notice);
}());
