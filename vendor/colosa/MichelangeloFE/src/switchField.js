var SwitchField = function (options) {
    this.renderType = (options && options.renderType) || "text";
    PMUI.field.CheckBoxGroupField.call(this, options);
    this.process = null;
    this.workspace = null;
    this.rows = options.rows;
    this.options = options;
    SwitchField.prototype.init.call(this, options);
};

SwitchField.prototype = new PMUI.field.CheckBoxGroupField();

SwitchField.prototype.setProcess = function (process) {
    this.process = process;
    return this;
};

SwitchField.prototype.setWorkspace = function (workspace) {
    this.workspace = workspace;
    return this;
};

SwitchField.prototype.init = function (options) {
    var defaults = {
        process: PMDesigner.project.projectId,
        workspace: WORKSPACE
    };
    jQuery.extend(true, defaults, options);
    this.setProcess(defaults.process)
        .setWorkspace(defaults.workspace);
};

SwitchField.prototype.createCallBack = function () {
    var that = this,
        newValue,
        init = 0,
        index = 0;
    return {
        success: function (variable) {
            var prevText,
                lastText,
                htmlControl = that.controls[index].html;
            init = htmlControl.selectionStart;
            prevText = htmlControl.value.substr(index, init);
            lastText = htmlControl.value.substr(htmlControl.selectionEnd, htmlControl.value.length);
            newValue = prevText + variable + lastText;
            that.setValue(newValue);
            that.isValid();
            htmlControl.selectionEnd = init + variable.length;
        }
    };
};

SwitchField.prototype.setPlaceholder = function (placeholder) {}

SwitchField.prototype.setMaxLength = function (placeholder) {}

SwitchField.prototype.setReadOnly = function (placeholder) {}

SwitchField.prototype.createHTML = function () {
    PMUI.field.CheckBoxGroupField.prototype.createHTML.call(this);
    this.setSwitchStyle();
    return this.html;
};

/**
 * Set style type switch to checkbox
 */
SwitchField.prototype.setSwitchStyle = function () {
    var table,
        span,
        label;
    if (this.html) {
        table = this.html.getElementsByTagName("table")[0];
        table.setAttribute('style', 'padding: 0px; border:0px');
        table.setAttribute('class', '');
        span = table.getElementsByTagName("span")[0];
        span.setAttribute('class', 'slider round');
        label = table.getElementsByTagName("label")[0];
        label.setAttribute('class', 'switch');
    }
};

// Overwrite original init function for FormItemFactory
PMUI.form.FormItemFactory.prototype.init = function () {
    var defaults = {
        products: {
            "criteria": CriteriaField,
            "switch": SwitchField,
            "field": PMUI.form.Field,
            "panel": PMUI.form.FormPanel,
            "text": PMUI.field.TextField,
            "password": PMUI.field.PasswordField,
            "dropdown": PMUI.field.DropDownListField,
            "radio": PMUI.field.RadioButtonGroupField,
            "checkbox": PMUI.field.CheckBoxGroupField,
            "textarea": PMUI.field.TextAreaField,
            "datetime": PMUI.field.DateTimeField,
            "optionsSelector": PMUI.field.OptionsSelectorField,
            "buttonField": PMUI.field.ButtonField,
            "annotation": PMUI.field.TextAnnotationField
        },
        defaultProduct: "panel"
    };
    this.setProducts(defaults.products)
        .setDefaultProduct(defaults.defaultProduct);
};
