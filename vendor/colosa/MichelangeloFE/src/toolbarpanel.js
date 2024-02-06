var ToolbarPanel = function (options) {
    this.tooltip = null;
    ToolbarPanel.prototype.init.call(this, options);
};

ToolbarPanel.prototype = new PMUI.core.Panel();
ToolbarPanel.prototype.type = "ToolbarPanel";

ToolbarPanel.prototype.init = function (options) {
    var defaults = {
        fields: [],
        tooltip: "",
        width: "96%"
    };
    jQuery.extend(true, defaults, options);
    PMUI.core.Panel.call(this, defaults);
    this.fields = [];
    this.setTooltip(defaults.tooltip);
    this.setFields(defaults.fields);
};
ToolbarPanel.prototype.setTooltip = function (message) {
    if (typeof message === "string") {
        this.tooltip = message;
    }
    return this;
};

ToolbarPanel.prototype.setFields = function (fields) {
    this.fields = fields;
    return this;
};
/**
 * Creates html structure for a button
 * @param {*} button
 */
ToolbarPanel.prototype.createButtonHTML = function (button) {
    var i,
        li = PMUI.createHTMLElement("li"),
        a = PMUI.createHTMLElement("a");

    li.id = button.selector;
    li.className = "mafe-toolbarpanel-btn";
    a.title = "";
    a.style.cursor = "move";
    jQuery(a).tooltip({
        content: button.tooltip,
        tooltipClass: "mafe-action-tooltip",
        position: {
            my: "left top",
            at: "left bottom",
            collision: "flipfit"
        }
    });

    for (i = 0; i < button.className.length; i += 1) {
        jQuery(a).addClass(button.className[i]);
    }

    li.appendChild(a);
    return li;
};

/**
 * Creates html structure for a switch tongle component
 * @param {*} element
 * @returns {String}
 */
ToolbarPanel.prototype.createSwitchHTML = function (element) {
    var li = PMUI.createHTMLElement("li"),
        input = PMUI.createHTMLElement("input"),
        label = PMUI.createHTMLElement("label"),
        labelDescription = PMUI.createHTMLElement("label");
    labelDescription.innerHTML = element.text || '';
    labelDescription.className = "tgl-label";
    input.type = "checkbox";
    li.className = "mafe-toolbarpanel-switch";
    input.type = "checkbox";
    input.id = element.selector;
    input.className = "tgl tgl-light";
    input.checked = element.checked || false;
    label.htmlFor = element.selector;
    label.className = "tgl-btn";
    input.addEventListener( 'change', function() {
        if (element.checkHandler) {
            if(this.checked) {
                element.checkHandler(true);
            } else {
                element.checkHandler(false);
            }
        }
    });
    li.appendChild(labelDescription);
    li.appendChild(input);
    li.appendChild(label);
    return li;
};

ToolbarPanel.prototype.createHTML = function () {
    var that = this,
        ul,
        html;
    PMUI.core.Panel.prototype.setElementTag.call(this, "ul");
    PMUI.core.Panel.prototype.createHTML.call(this);
    this.html.style.overflow = "visible";
    jQuery.each(this.fields, function (i, button) {
        if (button.type === "button") {
            html = that.createButtonHTML(button);
        } else if (button.type === "switch") {
            html = that.createSwitchHTML(button);
        }
        that.html.appendChild(html);
        button.html = html;
    });
    return this.html;
};

ToolbarPanel.prototype.activate = function () {
    var that = this;
    jQuery.each(this.fields, function (i, b) {
        if (b.type === "button") {
            jQuery(b.html).draggable({
                opacity: 0.7,
                helper: "clone",
                cursor: "hand"
            });
        }
    });
    return this;
};
/**
 * Enable the actions if the toolbar button has an action and is a button
 * @chainable
 */
ToolbarPanel.prototype.enableActions = function () {
    jQuery.each(this.fields, function (i, b) {
        if (b.type === "button") {
            if (b.actions) {
                new PMAction(b.actions);
            }
        }
        
    });
    return this;
};

ToolbarPanel.prototype.getSelectors = function () {
    var selectors = [],
        that = this;
    jQuery.each(this.fields, function (i, button) {
        selectors.push("#" + button.selector);
    });
    return selectors;
};
