PMDesigner.sidebar = [];

PMDesigner.sidebar.push(
    new ToolbarPanel({
        fields: [
            {
                selector: 'TASK',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-task'
                ],
                tooltip: "Task".translate()
            },
            {
                selector: 'SUB_PROCESS',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-subprocess'
                ],
                tooltip: "Sub Process".translate()
            }
        ]
    }),
    new ToolbarPanel({
        fields: [
            {
                selector: 'EXCLUSIVE',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-gateway-exclusive'
                ],
                tooltip: "Exclusive Gateway".translate()
            },
            {
                selector: 'PARALLEL',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-gateway-parallel'
                ],
                tooltip: "Parallel gateway".translate()
            },
            {
                selector: 'INCLUSIVE',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-gateway-inclusive'
                ],
                tooltip: "Inclusive Gateway".translate()
            }
        ]
    }),
    new ToolbarPanel({
        fields: [
            {
                selector: 'START',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-start'
                ],
                tooltip: "Start Event".translate()
            },
            {
                selector: 'START_TIMER',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-event-start-timer'
                ],
                tooltip: "Start Timer Event".translate()
            },
            {
                selector: 'INTERMEDIATE_EMAIL',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-intermediate-send-mesage'
                ],
                tooltip: "Intermediate Email Event".translate()
            },
            {
                selector: 'INTERMEDIATE_TIMER',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-event-intermediate-timer'
                ],
                tooltip: "Intermediate Timer Event".translate()
            },
            {
                selector: 'END',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-end'
                ],
                tooltip: "End Event".translate()
            },
            {
                selector: 'END_EMAIL',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-end-message'
                ],
                tooltip: "End Email Event ".translate()
            }
        ]
    }),
    new ToolbarPanel({
        fields: [
            {
                selector: 'DATAOBJECT',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-data-object'
                ],
                tooltip: "Data Object".translate()
            },
            {
                selector: 'DATASTORE',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-data-store'
                ],
                tooltip: "Data Store".translate()
            }
        ]
    }),
    new ToolbarPanel({
        fields: [
            {
                selector: 'PARTICIPANT',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-blackbox'
                ],
                tooltip: " Black Box Pool".translate()
            },
            {
                selector: 'POOL',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-pool'
                ],
                tooltip: "Pool".translate()
            },
            {
                selector: 'LANE',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-lane'
                ],
                tooltip: "Lane".translate()
            }
        ]
    }),
    new ToolbarPanel({
        fields: [
            {
                selector: 'GROUP',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-group'
                ],
                tooltip: "Group".translate()
            },
            {
                selector: 'TEXT_ANNOTATION',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-annotation'
                ],
                tooltip: "Text Annotation".translate()
            }
        ]
    }),
    new ToolbarPanel({
        fields: [
            {
                selector: 'LASSO',
                type: 'button',
                className: [
                    'mafe-designer-icon',
                    'mafe-toolbar-lasso'
                ],
                tooltip: "Lasso".translate()
            }

        ]
    }),
    new ToolbarPanel({
        fields: [
            {
                selector: 'enableAutosave',
                type: 'switch',
                className: [
                    'mafe-toolbar-autosave'
                ],
                tooltip: "Validate Now".translate(),
                checked: true,
                text: "Auto Save".translate(),
                checkHandler: function (value) {
                    PMDesigner.autoSaveValue(value);
                }
            }
        ]  
    })
);