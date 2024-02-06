/**
 * @class PMConnectionDropBehavior
 * Extends the functionality to handle creation of connections
 *
 * @constructor
 * Creates a new instance of the object
 */
var PMConnectionDropBehavior = function (selectors) {
    PMUI.behavior.ConnectionDropBehavior.call(this, selectors);
};
PMConnectionDropBehavior.prototype = new PMUI.behavior.ConnectionDropBehavior();
/**
 * Defines the object type
 * @type {String}
 */
PMConnectionDropBehavior.prototype.type = "PMConnectionDropBehavior";

/**
 * Defines a Map of the basic Rules
 * @type {Object}
 */
PMConnectionDropBehavior.prototype.basicRules = {
    PMEvent: {
        PMEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMActivity: {
            connection: 'regular',
            type: 'SEQUENCE'
        }
    },
    PMActivity: {
        PMActivity: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMArtifact: {
            connection: 'dotted',
            destDecorator: 'con_none',
            type: 'ASSOCIATION'
        },
        PMIntermediateEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMEndEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMGateway: {
            connection: 'regular',
            type: 'SEQUENCE'
        }
    },
    PMStartEvent: {
        PMActivity: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMIntermediateEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMGateway: {
            connection: 'regular',
            type: 'SEQUENCE'
        }
    },
    PMIntermediateEvent: {
        PMActivity: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMIntermediateEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMEndEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMGateway: {
            connection: 'regular',
            type: 'SEQUENCE'
        }
    },
    PMBoundaryEvent: {
        PMActivity: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMIntermediateEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMEndEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMGateway: {
            connection: 'regular',
            type: 'SEQUENCE'
        }
    },
    PMGateway: {
        PMActivity: {
            connection: 'regular',
            type: 'SEQUENCE'
        },
        PMIntermediateEvent: {
            connection: 'regular',
            type: 'SEQUENCE'
        }
    },
    PMArtifact: {
        PMActivity: {
            connection: 'dotted',
            destDecorator: 'con_none',
            type: 'ASSOCIATION'
        }
    }
};

/**
 * Defines a Map of the init Rules
 * @type {Object}
 */

PMConnectionDropBehavior.prototype.initRules = {
    PMCanvas: {
        PMCanvas: {
            name: 'PMCanvas to PMCanvas',
            rules: PMConnectionDropBehavior.prototype.basicRules
        }
    },
    PMActivity: {
        PMCanvas: {
            name: 'PMActivity to PMCanvas',
            rules: PMConnectionDropBehavior.prototype.basicRules
        }
    }
};

/**
 * Handle the hook functionality when a drop start
 *  @param shape
 */
PMConnectionDropBehavior.prototype.dropStartHook = function (shape, e, ui) {
    shape.srcDecorator = null;
    shape.destDecorator = null;
    var draggableId = ui.draggable.attr("id"),
        source = shape.canvas.customShapes.find('id', draggableId),
        prop;
    if (source) {
        prop = this.validate(source, shape);
        if (prop) {
            shape.setConnectionType({
                type: prop.type,
                segmentStyle: prop.connection,
                srcDecorator: prop.srcDecorator,
                destDecorator: prop.destDecorator
            });

        } else {
            // verif if port is changed
            if (typeof source !== 'undefined') {
                if (!(ui.helper && ui.helper.attr('id') === "drag-helper")) {
                    return false;
                }
                shape.setConnectionType('none');
            }
        }
    }

    return true;
};

/**
 * Connection validations method
 * return an object if is valid otherwise return false
 * @param {Connection} source
 * @param {Connection} target
 */
PMConnectionDropBehavior.prototype.validate = function (source, target) {
    var sType,
        tType,
        rules,
        initRules,
        initRulesName,
        BPMNAuxMap = {
            PMEvent: {
                'START': 'PMStartEvent',
                'END': 'PMEndEvent',
                'INTERMEDIATE': 'PMIntermediateEvent',
                'BOUNDARY': 'PMBoundaryEvent'
            },
            bpmnArtifact: {
                'TEXTANNOTATION': 'bpmnAnnotation'
            }
        };

    if (source && target) {
        if (source.getID() === target.getID()) {
            return false;
        }

        if (this.initRules[source.getParent().getType()]
            && this.initRules[source.getParent().getType()][target.getParent().getType()]) {
            initRules = this.initRules[source.getParent().getType()][target.getParent().getType()].rules;
            initRulesName = this.initRules[source.getParent().getType()][target.getParent().getType()].name;
            // get the types
            sType = source.getType();
            tType = target.getType();
            //Custimize all PM events
            if (sType === 'PMEvent') {
                if (BPMNAuxMap[sType] && BPMNAuxMap[sType][source.getEventType()]) {
                    sType = BPMNAuxMap[sType][source.getEventType()];
                }
            }
            if (tType === 'PMEvent') {
                if (BPMNAuxMap[tType] && BPMNAuxMap[tType][target.getEventType()]) {
                    tType = BPMNAuxMap[tType][target.getEventType()];
                }
            }

            if (initRules[sType] && initRules[sType][tType]) {
                rules = initRules[sType][tType];
            } else {
                rules = false;
            }
            if (initRules) {
                switch (initRulesName) {
                    case 'bpmnPool to bpmnPool':
                        if (source.getParent().getID() !== target.getParent().getID()) {
                            rules = false;
                        }
                        break;
                    case 'bpmnLane to bpmnLane':
                        if (source.getFirstPool(source.parent).getID()
                            !== target.getFirstPool(target.parent).getID()) {
                            if (this.extraRules[sType]
                                && this.extraRules[sType][tType]) {
                                rules = this.extraRules[sType][tType];
                            } else {
                                rules = false;
                            }
                        }
                        break;
                    case 'bpmnActivity to bpmnLane':
                        if (this.basicRules[sType]
                            && this.basicRules[sType][tType]) {
                            rules = this.basicRules[sType][tType];
                        } else {
                            rules = false;
                        }
                        break;
                    default:
                        break;
                }
            } else {
                rules = false;
            }

        } else {
            // get the types
            sType = source.getType();
            tType = target.getType();
            //
            if (sType === 'PMEvent') {
                if (BPMNAuxMap[sType] && BPMNAuxMap[sType][source.getEventType()]) {
                    sType = BPMNAuxMap[sType][source.getEventType()];
                }
            }
            if (tType === 'PMEvent') {
                if (BPMNAuxMap[tType] && BPMNAuxMap[tType][target.getEventType()]) {
                    tType = BPMNAuxMap[tType][target.getEventType()];
                }
            }
            if (this.advancedRules[sType] && this.advancedRules[sType][tType]) {
                rules = this.advancedRules[sType][tType];
            } else {
                rules = false;
            }
        }
        return rules;
    }
};
PMConnectionDropBehavior.prototype.onDragEnter = function (customShape) {
    return function (e, ui) {
        var shapeRelative, i;
        if (customShape.extendedType !== "PARTICIPANT") {
            if (ui.helper && ui.helper.hasClass("dragConnectHandler")) {
                shapeRelative = customShape.canvas.dragConnectHandlers.get(0).relativeShape;
                if (shapeRelative.id !== customShape.id) {
                    for (i = 0; i < 4; i += 1) {
                        customShape.showConnectDropHelper(i, customShape);
                    }
                }
            }
        } else {
            shapeRelative = customShape.canvas.dragConnectHandlers.get(0).relativeShape;
            if (shapeRelative && customShape && shapeRelative.id !== customShape.id) {
                if (ui.helper && ui.helper.hasClass("dragConnectHandler")) {
                    for (i = 0; i < 10; i += 1) {
                        connectHandler = customShape.canvas.dropConnectHandlers.get(i);
                        connectHandler.setDimension(18 * customShape.canvas.getZoomFactor(), 18 * customShape.canvas.getZoomFactor());
                        connectHandler.setPosition(customShape.getZoomX() + i * customShape.getZoomWidth() / 10, customShape.getZoomY() - connectHandler.height / 2 - 1);
                        connectHandler.relativeShape = customShape;
                        connectHandler.attachDrop();

                        connectHandler.setVisible(true);
                    }

                    for (i = 0; i < 10; i += 1) {
                        connectHandler = customShape.canvas.dropConnectHandlers.get(i + 10);
                        connectHandler.setDimension(18 * customShape.canvas.getZoomFactor(), 18 * customShape.canvas.getZoomFactor());
                        connectHandler.setPosition(customShape.getZoomX() + i * customShape.getZoomWidth() / 10, customShape.getZoomY() + customShape.getZoomHeight() - connectHandler.height / 2 - 1);
                        connectHandler.relativeShape = customShape;
                        connectHandler.attachDrop();

                        connectHandler.setVisible(true);
                    }
                }
            }
        }
    }
};
/**
 * Handle the functionality when a shape is dropped
 * @param shape
 */
PMConnectionDropBehavior.prototype.onDrop = function (shape) {
    var that = this;
    return function (e, ui) {
        var customShape,
            id = ui.draggable.attr('id');
        if (shape.getType() === "PMParticipant" && !(customShape = shape.canvas.shapeFactory(id))) {
            if (customShape = shape.canvas.customShapes.find('id', id)) {
                customShape.dropOnParticipant = true;
            }
        }
        return false;
    };
};