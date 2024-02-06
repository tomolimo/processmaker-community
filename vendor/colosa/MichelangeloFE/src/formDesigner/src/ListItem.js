(function () {
    /**
     * Represents an item in the list of controls.
     *
     * @param {object} control
     * @returns {ListItemL#1.ListItem}
     * @constructor
     */
    var ListItem = function (control) {
        this.control = control;
        ListItem.prototype.init.call(this);
    };
    /**
     * Initialize the class.
     */
    ListItem.prototype.init = function () {
        this.deprecatedControlClassName = 'mafe-deprecated-control';
        this.body = $(
            "<div class='fd-list-responsive'>" +
            "<div style=''><img src='" + this.control.url + "'></img></div>" +
            "<div style=''>" + this.control.label + "</div>" +
            "<div class='" + this.deprecatedControlClassName + "'>" +
            "</div>");
        this.body.attr("render", this.control.render);
        this.body.draggable({
            appendTo: document.body,
            revert: "invalid",
            helper: "clone",
            cursor: "move",
            zIndex: 1000,
            connectToSortable: ".itemControls,.itemsVariablesControls"
        });
        this.deprecated(this.control.deprecated);
        this.createPopOver();
    };
    /**
     * Enable or disable deprecated icon.
     * @param {boolean} status
     */
    ListItem.prototype.deprecated = function (status) {
        var element = this.body.find("." + this.deprecatedControlClassName);
        if (status === true) {
            element.show();
        } else {
            element.hide();
        }
    };
    /**
     * Create FormDesigner.main.PMPopOver element.
     */
    ListItem.prototype.createPopOver = function () {
        var content = $('<div><div class="mafe-deprecated-title">' + 'Warning!'.translate() + '</div><p>'
            + this.control.deprecationMessage + ' For additional information:'.translate() + '</p><p><a href="'
            + FormDesigner.DEPRECATION_LINK + '" target="_blank">' + FormDesigner.DEPRECATION_LINK
            + '</a></p><button type="button" class="deprecated-ok-btn">' + 'Got it'.translate() + '</button></div>');
        this.pmPopOver = new FormDesigner.main.PMPopOver({
            body: content,
            targetElement: this.body.find("." + this.deprecatedControlClassName),
            placement: "right",
            class: "deprecated"
        });
        content.find('button').data('parentBody', this.pmPopOver);
        content.on('click', 'button', function () {
            $(this).data('parentBody').hide();
        });
    };
    /**
     * Get FormDesigner.main.PMPopOver instance.
     * @returns {FormDesigner.main.PMPopOver}
     */
    ListItem.prototype.getPopOver = function () {
        return this.pmPopOver;
    };
    FormDesigner.extendNamespace('FormDesigner.main.ListItem', ListItem);
}());
