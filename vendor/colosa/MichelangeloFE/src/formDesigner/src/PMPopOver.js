(function () {
    /**
     * PopOver for PM Dynaform's designer.
     * @param {Object} settings An object containing the config options for the new PopOver.
     * @cfg {String|JQuery Object| Element} body The content for the PopOver, it casn be an HTML string, a JQuery Object
     * or an Element.
     * @cfg {String} [class=""] The class name for the popover, useful for applying custom styles through a css class.
     * @cfg {String} [placement=PMPopOver.PLACEMENT.RIGHT] A string that determines the placement of the popover related
     * to its target element. Please Use one of the keys defined under PMPopOver.PLACEMENT.
     *
     * @constructor
     */
    var PMPopOver = function (settings) {
        this._body = null;
        this._targetElement = null;
        this._dom = {};
        this._class = null;
        this._placement = null;
        this._visible = false;

        PMPopOver.prototype._init.call(this, settings);
    };
    /**
     * All the possible values for placement config option.
     * @type {{TOP: string, RIGHT: string, BOTTOM: string, LEFT: string}}
     */
    PMPopOver.PLACEMENT = {
        TOP: 'top',
        RIGHT: 'right',
        BOTTOM: 'bottom',
        LEFT: 'left'
    };
    /**
     * Initialize the class
     * @param settings
     * @private
     */
    PMPopOver.prototype._init = function (settings) {
        settings = $.extend({
            body: "",
            class: "",
            placement: PMPopOver.PLACEMENT.RIGHT
        }, settings);

        this._placement = settings.placement;
        this._class = settings.class;

        this.setBody(settings.body)
            .setTargetElement(settings.targetElement);
    };
    /**
     * Sets the element the PopOver belongs to.
     * @param targetElement
     * @chainable
     */
    PMPopOver.prototype.setTargetElement = function (targetElement) {
        if (!targetElement) {
            throw new Error('setTargetElement(): Invalid parameter.');
        }

        this._clickHandler = this._clickHandler || this.toggleVisible.bind(this);

        if (this._targetElement) {
            $(this._targetElement).off("click", this._clickHandler);
        }

        this._targetElement = targetElement;

        $(this._targetElement).on("click", this._clickHandler);

        return this;
    };
    /**
     * Sets the PopOver body.
     * @param body
     * @chainable
     */
    PMPopOver.prototype.setBody = function (body) {
        if (!(body instanceof Element || body instanceof jQuery || typeof body === 'string')) {
            throw new Error("setBody(): The parameter must be a DOM Element, jQuery element or a string.");
        }

        this._body = body;

        if (this._dom.content) {
            $(this._dom.content).empty().append(body);
        }

        return this;
    };
    /**
     * Returns the position of the PopOver target element.
     * @returns {Object}
     * @private
     */
    PMPopOver.prototype._getTargetPosition = function () {
        var $element = this._targetElement instanceof jQuery ? this._targetElement : $(this._targetElement),
            element = this._targetElement instanceof jQuery ? this._targetElement.get(0) : this._targetElement;

        return $.extend(element.getBoundingClientRect ? element.getBoundingClientRect() : {
            width: element.offsetWidth,
            height: element.offsetHeight
        }, $element.offset());
    };
    /**
     * Returns the final position for the popover.
     * @returns {{top: *, left: *}}
     * @private
     */
    PMPopOver.prototype._getPosition = function () {
        var targetPosition = this._getTargetPosition(),
            arrowOffset = 10,
            html = this.getHTML(),
            htmlWidth,
            htmlHeight,
            placement,
            top,
            left;

        document.body.appendChild(html);
        html.style.display = 'block';

        htmlWidth = html.offsetWidth;
        htmlHeight = html.offsetHeight;
        placement = this._placement;

        switch (placement) {
            case PMPopOver.PLACEMENT.TOP:
                top = targetPosition.top - htmlHeight - arrowOffset;
                left = targetPosition.left + (targetPosition.width / 2) - (htmlWidth / 2);
                break;
            case PMPopOver.PLACEMENT.RIGHT:
                top = targetPosition.top + (targetPosition.height / 2) - (htmlHeight / 2);
                left = targetPosition.left + targetPosition.width + arrowOffset;
                break;
            case PMPopOver.PLACEMENT.BOTTOM:
                top = targetPosition.top + targetPosition.height + arrowOffset;
                left = targetPosition.left + (targetPosition.width / 2) - (htmlWidth / 2);
                break;
            case PMPopOver.PLACEMENT.LEFT:
                top = targetPosition.top + (targetPosition.height / 2) - (htmlHeight / 2);
                left = targetPosition.left - htmlWidth - arrowOffset;
                break;
            default:
                throw new Error('_getPosition(): Invalid placement parameter.');
        }

        return {
            top: top,
            left: left
        };
    };
    /**
     * Displays the PopOver.
     * @chainable
     * @return {body}
     */
    PMPopOver.prototype.show = function () {
        var position = this._getPosition();

        $(this._html).removeClass("top right bottom left").addClass(this._placement).addClass("in");
        this._html.style.top = position.top + 'px';
        this._html.style.left = position.left + 'px';

        this._visible = true;

        return this;
    };
    /**
     * Hides the PopOver.
     * @chainable
     * @return {body}
     */
    PMPopOver.prototype.hide = function () {
        if (this._html) {
            $(this._html).fadeOut(150, "linear", function () {
                this.style.display = 'none';
                $(this).removeClass('in');
            });
        }

        this._visible = false;

        return this;
    };
    /**
     * Toggles the PopOver visibility.
     * @chainable
     */
    PMPopOver.prototype.toggleVisible = function () {
        return this._visible ? this.hide() : this.show();
    };
    /**
     * Creates the PopOver HTML.
     * @chainable
     * @return {body}
     * @private
     */
    PMPopOver.prototype._createHTML = function () {
        var container,
            arrow,
            content;

        if (this._html) {
            return this;
        }

        container = document.createElement('div');
        arrow = document.createElement('div');
        content = document.createElement('div');

        container.className = "mafe-popover fade " + this._class;
        arrow.className = "arrow";
        content.className = "mafe-popover-content";

        container.appendChild(arrow);
        container.appendChild(content);

        this._dom.container = container;
        this._dom.arrow = arrow;
        this._dom.content = content;

        this._html = container;

        this.setBody(this._body);

        this._html.addEventListener("mousedown", function (e) {
            e.stopPropagation();
        });

        document.addEventListener("mousedown", this.hide.bind(this), false);

        return this;
    };
    /**
     * Returns the PopOver HTML.
     * @returns {Element}
     */
    PMPopOver.prototype.getHTML = function () {
        if (!this._html) {
            this._createHTML();
        }

        return this._html;
    };

    FormDesigner.extendNamespace('FormDesigner.main.PMPopOver', PMPopOver);
})();
