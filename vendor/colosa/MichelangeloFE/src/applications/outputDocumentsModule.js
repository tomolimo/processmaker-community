(function () {
    var openTinyInMainWindow,
        dataOutPutDocument,
        openFormInMainWindow,
        messageRequired;

    PMDesigner.output = function (event) {
        var winMainOutputDocument, formOutput, rowData, updateOutPut, restClient, isDirtyFormOutput, clickedClose = true, that = this,
            setDataRow,
            clearDataRow,
            getGridOutput,
            disableAllItems,
            deleteDataRow,
            updateOutput,
            refreshGridPanelInMainWindow,
            openGridPanelInMainWindow,
            openFormForEditInMainWindow,
            editorTiny,
            outputFormDocPdfSecurityOpen,
            docMargin,
            password,
            outputFormDocPdfSecurityOwner,
            outputFormDocPdfSecurityEnabled,
            btnCloseWindowOutputDoc,
            btnSaveWindowOutputDoc,
            btnCancelTiny,
            newButtonOutput,
            gridOutput,
            winMainOutputDocument,
            btnSaveTiny,
            listOutputDocs,
            headerSettings,
            footerSettings,
            changeType,
            setMinValue,
            getFieldById;
    
        /**
         * Get field by id string, if not found return null.
         * @param {string} id
         * @returns {object}
         */
        getFieldById = function (id) {
            var fields = formOutput.getFields();
            for (var i in fields) {
                if (fields[i].id === id) {
                    return fields[i];
                }
            }
            return null;
        };

        setDataRow = function (row) {
            dataOutPutDocument = row.getData();
            rowData = row;
        };

        clearDataRow = function () {
            dataOutPutDocument = '';
            rowData = '';
        };

        isDirtyFormOutput = function () {
            var message_window;
            $("input,select,textarea").blur();
            if (formOutput.isVisible()) {
                if (formOutput.isDirty()) {
                    message_window = new PMUI.ui.MessageWindow({
                        id: "cancelMessageTriggers",
                        width: 490,
                        title: "Output Documents".translate(),
                        windowMessageType: "warning",
                        bodyHeight: 'auto',
                        message: 'Are you sure you want to discard your changes?'.translate(),
                        footerItems: [
                            {
                                text: "No".translate(),
                                handler: function () {
                                    message_window.close();
                                },
                                buttonType: "error"
                            },
                            {
                                text: "Yes".translate(),
                                handler: function () {
                                    clearDataRow();
                                    message_window.close();
                                    if (clickedClose) {
                                        tinymce.EditorManager.execCommand('mceRemoveControl', true, 'tinyeditor');
                                        winMainOutputDocument.close();
                                    } else {
                                        clearDataRow();
                                        openGridPanelInMainWindow();
                                    }

                                },
                                buttonType: "success"
                            }
                        ]
                    });
                    message_window.open();
                    message_window.showFooter();
                } else {
                    clearDataRow();
                    if (clickedClose) {
                        tinymce.EditorManager.execCommand('mceRemoveControl', true, 'tinyeditor');
                        winMainOutputDocument.close()
                    } else {
                        openGridPanelInMainWindow();
                    }
                }
            } else {
                winMainOutputDocument.close();
            }
        };
        getGridOutput = function () {
            var restClientGet = new PMRestClient({
                endpoint: 'output-documents',
                typeRequest: 'get',
                functionSuccess: function (xhr, response) {
                    listOutputDocs = response;
                    gridOutput.setDataItems(listOutputDocs);
                    gridOutput.sort('out_doc_title', 'asc');
                },
                functionFailure: function (xhr, response) {
                    PMDesigner.msgWinError(response.error.message);
                },
                messageError: "There are problems getting the output documents, please try again.".translate()
            });
            restClientGet.executeRestClient();
        };

        disableAllItems = function () {
            winMainOutputDocument.hideFooter();
            formOutput.reset();

            winMainOutputDocument.getItems()[0].setVisible(false);
            winMainOutputDocument.getItems()[1].setVisible(false);
            for (var i = 0; i <= winMainOutputDocument.getItems()[1].getItems().length - 1; i += 1) {
                winMainOutputDocument.getItems()[1].getItems()[i].setVisible(false);
            }
            btnSaveWindowOutputDoc.setVisible(false);
            btnCloseWindowOutputDoc.setVisible(false);
            btnSaveTiny.setVisible(false);
            btnCancelTiny.setVisible(false);

            winMainOutputDocument.footer.getItems()[2].setVisible(false);
        };

        refreshGridPanelInMainWindow = function () {
            disableAllItems();
            winMainOutputDocument.getItems()[0].setVisible(true);
            winMainOutputDocument.setTitle("Output Documents".translate());
            getGridOutput();
        };

        openGridPanelInMainWindow = function () {
            disableAllItems();
            winMainOutputDocument.getItems()[0].setVisible(true);
            winMainOutputDocument.setTitle("Output Documents".translate());
            $(winMainOutputDocument.body).removeClass("pmui-background");
        };

        openFormInMainWindow = function () {
            disableAllItems();
            winMainOutputDocument.showFooter();
            winMainOutputDocument.getItems()[1].setVisible(true);
            for (var i = 0; i < winMainOutputDocument.getItems()[1].getItems().length; i += 1) {
                if (winMainOutputDocument.getItems()[1].getItems()[i].type !== "PMTinyField") {
                    winMainOutputDocument.getItems()[1].getItems()[i].setVisible(true);
                }
            }
            btnSaveWindowOutputDoc.setVisible(true);
            btnCloseWindowOutputDoc.setVisible(true);
            winMainOutputDocument.footer.getItems()[2].setVisible(true);
            password.setVisible(false);
            headerSettings.setVisible(false);
            footerSettings.setVisible(false);
            footerSettings.getField('total_number_page_footer').disable();
            headerSettings.getField('total_number_page_header').disable();
            winMainOutputDocument.setTitle("Create Output Document".translate());
            winMainOutputDocument.setHeight(520);
            formOutput.panel.style.addProperties({padding: '20px 10px'});
            formOutput.setFocus();
        };

        openFormForEditInMainWindow = function (outputDocumentData) {
            disableAllItems();
            winMainOutputDocument.showFooter();
            btnSaveWindowOutputDoc.setVisible(true);
            btnCloseWindowOutputDoc.setVisible(true);
            winMainOutputDocument.footer.getItems()[1].setVisible(false);
            formOutput.setWidth(700);
            winMainOutputDocument.getItems()[1].setVisible(true);
            winMainOutputDocument.setTitle("Edit Output Document".translate());
            $(winMainOutputDocument.body).addClass("pmui-background");
            for (var i = 0; i < winMainOutputDocument.getItems()[1].getItems().length; i += 1) {
                if (winMainOutputDocument.getItems()[1].getItems()[i].type !== "PMTinyField") {
                    winMainOutputDocument.getItems()[1].getItems()[i].setVisible(true);
                }
            }

            password.setVisible(false);
            headerSettings.setVisible(false);
            footerSettings.setVisible(false);
            if (dataOutPutDocument != '' && dataOutPutDocument != undefined) {
                var dataEdit = formOutput.getFields();
                getFieldById('outputDocTitle').setValue(dataOutPutDocument['out_doc_title']);
                getFieldById('outputDocFilenameGenerated').setValue(dataOutPutDocument['out_doc_filename']);
                getFieldById('outputDocDescription').setValue(dataOutPutDocument['out_doc_description']);
                getFieldById('outputDocReportGenerator').setValue(dataOutPutDocument['out_doc_report_generator']);
                getFieldById('outputDocMedia').setValue(dataOutPutDocument['out_doc_media']);
                getFieldById('outputDocOrientation').setValue(dataOutPutDocument['out_doc_landscape']);
                getFieldById('outputDocMarginLeft').setValue(dataOutPutDocument['out_doc_left_margin']);
                getFieldById('outputDocMarginRight').setValue(dataOutPutDocument['out_doc_right_margin']);
                getFieldById('outputDocMarginTop').setValue(dataOutPutDocument['out_doc_top_margin']);
                getFieldById('outputDocMarginBottom').setValue(dataOutPutDocument['out_doc_bottom_margin']);
                getFieldById('outputDocToGenerate').setValue(dataOutPutDocument['out_doc_generate']);

                //Set data in header settings
                if (dataOutPutDocument['out_doc_header'] !== "[]") {
                    getFieldById('enableHeader').setValue(dataOutPutDocument['out_doc_header'].enableHeader ? '["1"]' : '0');
                    dataOutPutDocument['out_doc_header'].enableHeader ? headerSettings.setVisible(true) : headerSettings.setVisible(false);
                    getFieldById('headerTitle').setValue(dataOutPutDocument['out_doc_header'].title);
                    getFieldById('fontSizeHeader').setValue(dataOutPutDocument['out_doc_header'].titleFontSize);
                    getFieldById('positionXTitleHeader').setValue(dataOutPutDocument['out_doc_header'].titleFontPositionX);
                    getFieldById('positionYTitleHeader').setValue(dataOutPutDocument['out_doc_header'].titleFontPositionY);
                    getFieldById('headerLogo').setValue(dataOutPutDocument['out_doc_header'].logo);
                    getFieldById('logoWidthHeader').setValue(dataOutPutDocument['out_doc_header'].logoWidth);
                    getFieldById('positionXLogoHeader').setValue(dataOutPutDocument['out_doc_header'].logoPositionX);
                    getFieldById('positionYLogoHeader').setValue(dataOutPutDocument['out_doc_header'].logoPositionY);
                    getFieldById('pageNumberHeader').setValue(dataOutPutDocument['out_doc_header'].pageNumber ? '["1"]' : '0');
                    getFieldById('paginationTitleHeader').setValue(dataOutPutDocument['out_doc_header'].pageNumberTitle);
                    getFieldById('totalNumberPageHeader').setValue(dataOutPutDocument['out_doc_header'].pageNumberTotal ? '["1"]' : '0');
                    getFieldById('positionXNumberHeader').setValue(dataOutPutDocument['out_doc_header'].pageNumberPositionX);
                    getFieldById('positionYNumberHeader').setValue(dataOutPutDocument['out_doc_header'].pageNumberPositionY);
                    if (getFieldById('pageNumberHeader').getValue() === '["1"]') {
                        getFieldById('paginationTitleHeader').enable();
                        getFieldById('totalNumberPageHeader').enable();
                        getFieldById('positionXNumberHeader').enable();
                        getFieldById('positionYNumberHeader').enable();
                    } else {
                        getFieldById('paginationTitleHeader').disable();
                        getFieldById('totalNumberPageHeader').disable();
                        getFieldById('positionXNumberHeader').disable();
                        getFieldById('positionYNumberHeader').disable();
                    }
                }
                //Set data in footer settings
                if (dataOutPutDocument['out_doc_header'] !== "[]") {
                    getFieldById('enableFooter').setValue(dataOutPutDocument['out_doc_footer'].enableFooter ? '["1"]' : '0');
                    dataOutPutDocument['out_doc_footer'].enableFooter ? footerSettings.setVisible(true) : footerSettings.setVisible(false);
                    getFieldById('footerTitle').setValue(dataOutPutDocument['out_doc_footer'].title);
                    getFieldById('fontSizeFooter').setValue(dataOutPutDocument['out_doc_footer'].titleFontSize);
                    getFieldById('positionXTitleFooter').setValue(dataOutPutDocument['out_doc_footer'].titleFontPositionX);
                    getFieldById('positionYTitleFooter').setValue(dataOutPutDocument['out_doc_footer'].titleFontPositionY);
                    getFieldById('footerLogo').setValue(dataOutPutDocument['out_doc_footer'].logo);
                    getFieldById('logoWidthFooter').setValue(dataOutPutDocument['out_doc_footer'].logoWidth);
                    getFieldById('positionXLogoFooter').setValue(dataOutPutDocument['out_doc_footer'].logoPositionX);
                    getFieldById('positionYLogoFooter').setValue(dataOutPutDocument['out_doc_footer'].logoPositionY);
                    getFieldById('pageNumerFooter').setValue(dataOutPutDocument['out_doc_footer'].pageNumber ? '["1"]' : '0');
                    getFieldById('paginationTitleFooter').setValue(dataOutPutDocument['out_doc_footer'].pageNumberTitle);
                    getFieldById('totalNumberPageFooter').setValue(dataOutPutDocument['out_doc_footer'].pageNumberTotal ? '["1"]' : '0');
                    getFieldById('positionXNumberFooter').setValue(dataOutPutDocument['out_doc_footer'].pageNumberPositionX);
                    getFieldById('positionYNumberFooter').setValue(dataOutPutDocument['out_doc_footer'].pageNumberPositionY);
                    if (getFieldById('pageNumerFooter').getValue() === '["1"]') {
                        getFieldById('paginationTitleFooter').enable();
                        getFieldById('totalNumberPageFooter').enable();
                        getFieldById('positionXNumberFooter').enable();
                        getFieldById('positionYNumberFooter').enable();
                    } else {
                        getFieldById('paginationTitleFooter').disable();
                        getFieldById('totalNumberPageFooter').disable();
                        getFieldById('positionXNumberFooter').disable();
                        getFieldById('positionYNumberFooter').disable();
                    }
                }

                if (dataOutPutDocument["out_doc_generate"] != "DOC") {
                    getFieldById('outputDocDPFSecurity').setVisible(true);
                } else {
                    getFieldById('outputDocDPFSecurity').setVisible(false);
                }

                getFieldById('outputDocDPFSecurity').setValue(dataOutPutDocument['out_doc_pdf_security_enabled']);
                if (dataOutPutDocument['out_doc_pdf_security_enabled'] != 0) {
                    password.setVisible(true);
                }
                getFieldById('outputFormDocPdfSecurityOpen').setValue(dataOutPutDocument['out_doc_pdf_security_open_password']);
                getFieldById('outputFormDocPdfSecurityOwner').setValue(dataOutPutDocument['out_doc_pdf_security_owner_password']);

                dataOutPutDocument['out_doc_pdf_security_permissions'] = dataOutPutDocument['out_doc_pdf_security_permissions'].split("|");
                getFieldById('outputFormDocPdfSecurityPermissions').setValue(JSON.stringify(dataOutPutDocument['out_doc_pdf_security_permissions']));

                getFieldById('outputDocEnableVersioning').setValue(dataOutPutDocument['out_doc_versioning']);
                getFieldById('outputDocDestinationPath').setValue(dataOutPutDocument['out_doc_destination_path']);
                getFieldById('outputDocTags').setValue(dataOutPutDocument['out_doc_tags']);
                getFieldById('outputDocGenerateFileLink').setValue(dataOutPutDocument["out_doc_open_type"]);
            }
            winMainOutputDocument.setHeight(520);
            formOutput.panel.style.addProperties({padding: '20px 10px'});
            formOutput.setFocus();
        };

        openTinyInMainWindow = function (outputDocumentData) {
            //Fix for IE11
            var isIe11 = /Trident\/7\.0;.*rv\s*\:?\s*11/.test(navigator.userAgent);

            if (isIe11) {
                tinyMCE.isGecko = false;
            }

            //Set TinyMCE
            disableAllItems();
            winMainOutputDocument.showFooter();
            tinyEditorField = 13;
            formOutput.setVisible(true);
            formOutput.getItems()[tinyEditorField].setVisible(true);
            formOutput.setWidth(890);
            btnSaveTiny.setVisible(true);
            btnCancelTiny.setVisible(true);
            if (!editorTiny.isInitialized) {
                editorTiny.createHTML();
                editorTiny.setParameterTiny();
                editorTiny.isInitialized = true;
            } else {
                tinyMCE.execCommand('mceFocus', false, 'tinyeditor');
            }
            var dataEdit = formOutput.getFields();
            winMainOutputDocument.setTitle("Edit Output Document".translate());
            if (dataOutPutDocument != '' && dataOutPutDocument != undefined) {
                dataOutPutDocument['out_doc_template'] = (dataOutPutDocument['out_doc_template'] != null) ? dataOutPutDocument['out_doc_template'] : ' ';
                dataEdit[47].setValue(dataOutPutDocument['out_doc_template']);
                dataEdit[47].setValueTiny(dataOutPutDocument['out_doc_template']);
                dataEdit[47].setHeight(425);

                formOutput.getItems()[13].setVisible(false)
                dataEdit[47].setVisible(true);
            }
            formOutput.panel.style.addProperties({padding: '0px 10px'});
            winMainOutputDocument.setHeight(520);
            if (!editorTiny.isInitialized)
                tinymce.execCommand('mceFocus', false, 'tinyeditor');
        };

        deleteDataRow = function () {
            confirmWindow = new PMUI.ui.MessageWindow({
                id: "outputMessageWindowWarning",
                windowMessageType: 'warning',
                bodyHeight: 'auto',
                width: 490,
                title: "Output Documents".translate(),
                message: "Do you want to delete this Output Document?".translate(),
                footerItems: [
                    {
                        id: 'confirmWindowButtonNo',
                        text: "No".translate(),
                        visible: true,
                        handler: function () {
                            confirmWindow.close();
                        },
                        buttonType: "error"
                    }, {
                        id: 'confirmWindowButtonYes',
                        text: "Yes".translate(),
                        visible: true,
                        handler: function () {
                            var restClient;
                            confirmWindow.close();
                            restClient = new PMRestClient({
                                endpoint: "output-document/" + dataOutPutDocument.out_doc_uid,
                                typeRequest: 'remove',
                                functionSuccess: function (xhr, response) {
                                    refreshGridPanelInMainWindow();
                                },
                                functionFailure: function (xhr, response) {
                                    PMDesigner.msgWinError(response.error.message);
                                },
                                messageError: "There are problems deleting the OutputDocument, please try again.".translate(),
                                messageSuccess: 'Output Document deleted successfully'.translate(),
                                flashContainer: gridOutput
                            });
                            restClient.executeRestClient();
                        },
                        buttonType: "success"
                    },
                ]
            });
            confirmWindow.open();
            confirmWindow.dom.titleContainer.style.height = "17px";
            confirmWindow.showFooter();
        };

        updateOutput = function (data) {
            dataOutPutDocument = '';
            var restClientUpdate = new PMRestClient({
                endpoint: "output-document/" + data.out_doc_uid,
                typeRequest: 'update',
                data: data,
                functionSuccess: function (xhr, response) {
                    dataOutPutDocument = data;
                    refreshGridPanelInMainWindow();
                },
                functionFailure: function (xhr, response) {
                    PMDesigner.msgWinError(response.error.message);
                },
                messageError: "There are problems updating the OutputDocument, please try again.".translate(),
                messageSuccess: 'Output Document edited successfully'.translate(),
                flashContainer: gridOutput
            });
            restClientUpdate.executeRestClient();
        };

        editorTiny = new PMTinyField({
            id: 'outputEditorTiny',
            theme: "advanced",
            plugins: "advhr,advimage,advlink,advlist,autolink,autoresize,contextmenu,directionality,emotions,example,example_dependency,fullpage,fullscreen,iespell,inlinepopups,insertdatetime,layer,legacyoutput,lists,media,nonbreaking,noneditable,pagebreak,paste,preview,print,save,searchreplace,style,tabfocus,table,template,visualblocks,visualchars,wordcount,xhtmlxtras,pmSimpleUploader,pmVariablePicker,style",
            mode: "specific_textareas",
            editorSelector: "tmceEditor",
            widthTiny: DEFAULT_WINDOW_WIDTH - 58,
            heightTiny: DEFAULT_WINDOW_HEIGHT - 100,
            directionality: 'ltr',
            verifyHtml: false,
            themeAdvancedButtons1: "pmSimpleUploader,|,pmVariablePicker,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect,|,cut,copy,paste,|,bullist,numlist,|,outdent,indent,blockquote",
            themeAdvancedButtons2: "tablecontrols,|,undo,redo,|,link,unlink,image,|,forecolor,backcolor,styleprops,|,hr,removeformat,visualaid,|,sub,sup,|,ltr,rtl,|,code",
            popupCss: "/js/tinymce/jscripts/tiny_mce/themes/advanced/skins/default/dialogTinyBpmn.css",
            contentCss: "/css/fonts.css,/fonts/styles.php",
            themeAdvancedFonts: tcPdfFonts,
            skin: "o2k7",
            skin_variant: "silver"
        });

        docMargin = new PMUI.form.FormPanel({
            fieldset: true,
            layout: 'hbox',
            legend: "Margin".translate(),
            items: [
                new PMUI.form.FormPanel({
                    fieldset: true,
                    layout: "box",
                    proportion: 0.6,
                    padding: "5px 5px",
                    items: [
                        new PMUI.form.FormPanel({
                            fieldset: true,
                            height: 200,
                            width: 170,
                            borderWidth : "3px",
                        })
                    ]
                }),
                {
                    pmType: "panel",
                    layout: 'vbox',
                    items: [
                        {
                            id: 'outputDocMarginLeft',
                            pmType: "text",
                            label: "Left".translate(),
                            required: true,
                            value: 20,
                            name: "out_doc_left_margin",
                            controlsWidth: 50,
                            labelWidth: '35%'
                        }, {
                            id: 'outputDocMarginRight',
                            pmType: "text",
                            label: "Right".translate(),
                            required: true,
                            value: 20,
                            name: "out_doc_right_margin",
                            controlsWidth: 50,
                            labelWidth: '35%'
                        }
                    ]
                },
                {
                    pmType: "panel",
                    layout: 'vbox',
                    proportion: 1.5,
                    items: [
                        {
                            id: 'outputDocMarginTop',
                            pmType: "text",
                            label: "Top".translate(),
                            required: true,
                            value: 20,
                            name: "out_doc_top_margin",
                            controlsWidth: 50,
                            labelWidth: '27%'
                        }, {
                            id: 'outputDocMarginBottom',
                            pmType: "text",
                            label: "Bottom".translate(),
                            required: true,
                            value: 20,
                            name: "out_doc_bottom_margin",
                            controlsWidth: 50,
                            labelWidth: '27%'
                        }
                    ]
                }
            ]
        });

        //Field "Open Password - Owner Password"
        outputFormDocPdfSecurityOpen = new PMUI.field.PasswordField({
            id: "outputFormDocPdfSecurityOpen",
            name: "out_doc_pdf_security_open_password",
            value: "",
            label: "Open Password ".translate(),
            required: true,
            controlsWidth: 300
        });

        outputFormDocPdfSecurityOwner = new PMUI.field.PasswordField({
            id: "outputFormDocPdfSecurityOwner",
            name: "out_doc_pdf_security_owner_password",
            value: "",
            label: "Owner Password ".translate(),
            required: true,
            controlsWidth: 300

        });

        password = new PMUI.form.FormPanel({
            width: 500,
            height: 130,
            fieldset: true,
            visible: false,
            legend: "",
            items: [
                {
                    pmType: "panel",
                    layout: 'vbox',
                    items: [
                        outputFormDocPdfSecurityOpen,
                        outputFormDocPdfSecurityOwner
                    ]
                },
                {
                    pmType: "panel",
                    layout: 'vbox',
                    items: [
                        {
                            id: 'outputFormDocPdfSecurityPermissions',
                            pmType: 'checkbox',
                            label: "Allowed Permissions".translate(),
                            value: '',
                            name: 'out_doc_pdf_security_permissions',
                            required: false,
                            controlPositioning: 'horizontal',
                            separator: "|",
                            maxDirectionOptions: 4,
                            options: [
                                {
                                    id: 'monday',
                                    label: "print".translate(),
                                    value: 'print'
                                },
                                {
                                    id: 'monday',
                                    label: "modify".translate(),
                                    value: 'modify'
                                },
                                {
                                    id: 'monday',
                                    label: "copy".translate(),
                                    value: 'copy'
                                },
                                {
                                    id: 'monday',
                                    label: "forms".translate(),
                                    value: 'forms'
                                }

                            ]
                        }
                    ]
                }
            ],
            layout: "vbox"
        });

        /**
         * Header Settings
         */
        headerSettings = new PMUI.form.FormPanel({
            fieldset: true,
            layout: 'vbox',
            name: "header_settings",
            legend: "Header Settings".translate(),
            items: [
                {
                    pmType: "panel",
                    layout: 'hbox',
                    items: [
                        new CriteriaField({
                            id: 'headerTitle',
                            pmType: "text",
                            name: 'header_title',
                            label: "Header Title".translate(),
                            labelWidth: '26%',
                            controlsWidth: 285,
                            required: false,
                            proportion: 3
                        }),
                        {
                            id: 'fontSizeHeader',
                            pmType: "text",
                            label: "Font Size".translate(),
                            required: false,
                            value: 8,
                            name: "font_size_header",
                            controlsWidth: 50,
                            labelWidth: '51%',
                            proportion: 1.2,
                            onChange: function (newVal, oldVal) {
                                if (newVal <= 7 || newVal >= 73) {
                                    this.setValue(oldVal);
                                }
                            }
                        },
                        {
                            id: 'positionXTitleHeader',
                            pmType: "text",
                            label: "Position X".translate(),
                            required: false,
                            value: 0,
                            name: "position_x_title_header",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                        {
                            id: 'positionYTitleHeader',
                            pmType: "text",
                            label: "Position Y".translate(),
                            required: false,
                            value: 0,
                            name: "position_y_title_header",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                    ]
                },
                {
                    pmType: "panel",
                    layout: 'hbox',
                    items: [
                        new CriteriaField({
                            id: 'headerLogo',
                            pmType: "text",
                            name: 'header_logo',
                            label: "Header Logo".translate(),
                            labelWidth: '26%',
                            controlsWidth: 285,
                            required: false,
                            proportion: 3
                        }),
                        {
                            id: 'logoWidthHeader',
                            pmType: "text",
                            label: "Logo Width".translate(),
                            required: false,
                            value: 0,
                            name: "logo_width_header",
                            controlsWidth: 50,
                            labelWidth: '51%',
                            proportion: 1.2
                        },
                        {
                            id: 'positionXLogoHeader',
                            pmType: "text",
                            label: "Position X".translate(),
                            required: false,
                            value: 0,
                            name: "position_x_logo_header",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                        {
                            id: 'positionYLogoHeader',
                            pmType: "text",
                            label: "Position Y".translate(),
                            required: false,
                            value: 0,
                            name: "position_y_logo_header",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                    ]
                },
                {
                    pmType: "panel",
                    layout: "hbox",
                    items: [
                        new PMUI.form.FormPanel({
                            fieldset: false,
                            layout: 'hbox',
                            legend: "Header Settings".translate(),
                            proportion: 3,
                            fontSize: 10,
                            padding: 0,
                            items: [
                                new SwitchField({
                                    id: 'pageNumberHeader',
                                    labelWidth: "50%",
                                    label: "Page Number".translate(),
                                    name: "page_number_header",
                                    value: '1',
                                    controlsWidth: 54,
                                    proportion: 0.45,
                                    controlPositioning: 'vertical',
                                    options: [
                                        {
                                            id: 'pageNumberOptionHeader',
                                            disabled: false,
                                            value: '1',
                                            selected: false
                                        }
                                    ],
                                    onChange: function (newVal, oldVal) {
                                        if (newVal === '["1"]') {
                                            headerSettings.getField('pagination_title_header').updateDisabled(false);
                                            headerSettings.getField('total_number_page_header').enable();
                                            getFieldById('totalNumberPageHeader').enable();
                                            getFieldById('positionXNumberHeader').enable();
                                            getFieldById('positionYNumberHeader').enable();
                                        } else {
                                            headerSettings.getField('pagination_title_header').updateDisabled(true);
                                            headerSettings.getField('total_number_page_header').disable();
                                            getFieldById('totalNumberPageHeader').disable();
                                            getFieldById('positionXNumberHeader').disable();
                                            getFieldById('positionYNumberHeader').disable();
                                        }
                                    }
                                }),
                                new CriteriaField({
                                    id: 'paginationTitleHeader',
                                    pmType: "text",
                                    name: 'pagination_title_header',
                                    label: "Pagination Title".translate(),
                                    labelWidth: '27%',
                                    controlsWidth: 170,
                                    required: false,
                                    disabled: true
                                }),
                            ]
                        }),
                        new SwitchField({
                            id: 'totalNumberPageHeader',
                            labelWidth: "60%",
                            label: "Total Number of Pages".translate(),
                            name: "total_number_page_header",
                            value: '1',
                            controlPositioning: 'vertical',
                            controlsWidth: 54,
                            proportion: 1.2,
                            options: [
                                {
                                    id: 'totalNumberPageOptionHeader',
                                    disabled: false,
                                    value: '1',
                                    selected: false
                                }
                            ],
                            onChange: function (newVal, oldVal) {
                            }
                        }),
                        {
                            id: 'positionXNumberHeader',
                            pmType: "text",
                            label: "Position X".translate(),
                            required: false,
                            value: 0,
                            name: "position_x_number_header",
                            controlsWidth: 50,
                            labelWidth: '62%',
                            disabled: true
                        },
                        {
                            id: 'positionYNumberHeader',
                            pmType: "text",
                            label: "Position Y".translate(),
                            required: false,
                            value: 0,
                            name: "position_y_number_header",
                            controlsWidth: 50,
                            labelWidth: '62%',
                            disabled: true
                        },
                    ]
                }
            ]
        });

        /**
         * Footer settings
         */
        footerSettings = new PMUI.form.FormPanel({
            fieldset: true,
            layout: 'vbox',
            name: "footer_settings",
            legend: "Footer Settings".translate(),
            items: [
                {
                    pmType: "panel",
                    layout: 'hbox',
                    items: [
                        new CriteriaField({
                            id: 'footerTitle',
                            pmType: "text",
                            name: 'footer_title',
                            label: "Footer Title".translate(),
                            labelWidth: '26%',
                            controlsWidth: 285,
                            required: false,
                            proportion: 3
                        }),
                        {
                            id: 'fontSizeFooter',
                            pmType: "text",
                            label: "Font Size".translate(),
                            required: false,
                            value: 8,
                            name: "font_size_footer",
                            controlsWidth: 50,
                            labelWidth: '51%',
                            proportion: 1.2,
                            onChange: function (newVal, oldVal) {
                                if (newVal <= 7 || newVal >= 73) {
                                    this.setValue(oldVal);
                                }
                            }
                        },
                        {
                            id: 'positionXTitleFooter',
                            pmType: "text",
                            label: "Position X".translate(),
                            required: false,
                            value: 0,
                            name: "position_x_title_footer",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                        {
                            id: 'positionYTitleFooter',
                            pmType: "text",
                            label: "Position Y".translate(),
                            required: false,
                            value: 0,
                            name: "position_y_title_footer",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                    ]
                },
                {
                    pmType: "panel",
                    layout: 'hbox',
                    items: [
                        new CriteriaField({
                            id: 'footerLogo',
                            pmType: "text",
                            name: 'footer_logo',
                            label: "Footer Logo".translate(),
                            labelWidth: '26%',
                            controlsWidth: 285,
                            required: false,
                            proportion: 3
                        }),
                        {
                            id: 'logoWidthFooter',
                            pmType: "text",
                            label: "Logo Width".translate(),
                            required: false,
                            value: 0,
                            name: "logo_width_footer",
                            controlsWidth: 50,
                            labelWidth: '51%',
                            proportion: 1.2
                        },
                        {
                            id: 'positionXLogoFooter',
                            pmType: "text",
                            label: "Position X".translate(),
                            required: false,
                            value: 0,
                            name: "position_x_logo_footer",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                        {
                            id: 'positionYLogoFooter',
                            pmType: "text",
                            label: "Position Y".translate(),
                            required: false,
                            value: 0,
                            name: "position_y_logo_footer",
                            controlsWidth: 50,
                            labelWidth: '62%'
                        },
                    ]
                },
                {
                    pmType: "panel",
                    layout: "hbox",
                    items: [
                        new PMUI.form.FormPanel({
                            fieldset: false,
                            layout: 'hbox',
                            legend: "Footer Settings".translate(),
                            proportion: 3,
                            fontSize: 10,
                            padding: 0,
                            items: [
                                new SwitchField({
                                    id: 'pageNumerFooter',
                                    labelWidth: "50%",
                                    label: "Page Number".translate(),
                                    name: "page_number_footer",
                                    value: '1',
                                    controlsWidth: 54,
                                    proportion: 0.45,
                                    controlPositioning: 'vertical',
                                    options: [
                                        {
                                            id: 'pageNumberOptionFooter',
                                            disabled: false,
                                            value: '1',
                                            selected: false
                                        }
                                    ],
                                    onChange: function (newVal, oldVal) {
                                        if (newVal === '["1"]') {
                                            footerSettings.getField('pagination_title_footer').updateDisabled(false);
                                            footerSettings.getField('total_number_page_footer').enable();
                                            getFieldById('totalNumberPageFooter').enable();
                                            getFieldById('positionXNumberFooter').enable();
                                            getFieldById('positionYNumberFooter').enable();
                                        } else {
                                            footerSettings.getField('pagination_title_footer').updateDisabled(true);
                                            footerSettings.getField('total_number_page_footer').disable();
                                            getFieldById('totalNumberPageFooter').disable();
                                            getFieldById('positionXNumberFooter').disable();
                                            getFieldById('positionYNumberFooter').disable();
                                        }
                                    }
                                }),
                                new CriteriaField({
                                    id: 'paginationTitleFooter',
                                    pmType: "text",
                                    name: 'pagination_title_footer',
                                    label: "Pagination Title".translate(),
                                    labelWidth: '27%',
                                    controlsWidth: 170,
                                    required: false,
                                    disabled: true
                                }),
                            ]
                        }),
                        new SwitchField({
                            id: 'totalNumberPageFooter',
                            labelWidth: "60%",
                            label: "Total Number of Pages".translate(),
                            name: "total_number_page_footer",
                            value: '1',
                            controlPositioning: 'vertical',
                            controlsWidth: 54,
                            proportion: 1.2,
                            options: [
                                {
                                    id: 'totalNumberPageOptionFooter',
                                    disabled: false,
                                    value: '1',
                                    selected: false
                                }
                            ],
                            onChange: function (newVal, oldVal) {
                            }
                        }),
                        {
                            id: 'positionXNumberFooter',
                            pmType: "text",
                            label: "Position X".translate(),
                            required: false,
                            value: 0,
                            name: "position_x_number_footer",
                            controlsWidth: 50,
                            labelWidth: '62%',
                            disabled: true
                        },
                        {
                            id: 'positionYNumberFooter',
                            pmType: "text",
                            label: "Position Y".translate(),
                            required: false,
                            value: 0,
                            name: "position_y_number_footer",
                            controlsWidth: 50,
                            labelWidth: '62%',
                            disabled: true
                        },
                    ]
                }
            ]
        });

        /**
         * Change the type of control
         */
        changeType = function () {
            headerSettings.getField('font_size_header').getControl().getHTML().type = "number";
            headerSettings.getField('position_x_title_header').getControl().getHTML().type = "number";
            headerSettings.getField('position_y_title_header').getControl().getHTML().type = "number";
            headerSettings.getField('logo_width_header').getControl().getHTML().type = "number";
            headerSettings.getField('position_x_logo_header').getControl().getHTML().type = "number";
            headerSettings.getField('position_y_logo_header').getControl().getHTML().type = "number";
            headerSettings.getField('position_x_number_header').getControl().getHTML().type = "number";
            headerSettings.getField('position_y_number_header').getControl().getHTML().type = "number";
            footerSettings.getField('font_size_footer').getControl().getHTML().type = "number";
            footerSettings.getField('position_x_title_footer').getControl().getHTML().type = "number";
            footerSettings.getField('position_y_title_footer').getControl().getHTML().type = "number";
            footerSettings.getField('logo_width_footer').getControl().getHTML().type = "number";
            footerSettings.getField('position_x_logo_footer').getControl().getHTML().type = "number";
            footerSettings.getField('position_y_logo_footer').getControl().getHTML().type = "number";
            footerSettings.getField('position_x_number_footer').getControl().getHTML().type = "number";
            footerSettings.getField('position_y_number_footer').getControl().getHTML().type = "number";
        };

        /**
         * Set a minimum value to avoid entering negative numbers
         */
        setMinValue = function () {
            headerSettings.getField('position_x_title_header').getControl().getHTML().min = "0";
            headerSettings.getField('position_y_title_header').getControl().getHTML().min = "0";
            headerSettings.getField('logo_width_header').getControl().getHTML().min = "0";
            headerSettings.getField('position_x_logo_header').getControl().getHTML().min = "0";
            headerSettings.getField('position_y_logo_header').getControl().getHTML().min = "0";
            headerSettings.getField('position_x_number_header').getControl().getHTML().min = "0";
            headerSettings.getField('position_y_number_header').getControl().getHTML().min = "0";
            footerSettings.getField('position_x_title_footer').getControl().getHTML().min = "0";
            footerSettings.getField('position_y_title_footer').getControl().getHTML().min = "0";
            footerSettings.getField('logo_width_footer').getControl().getHTML().min = "0";
            footerSettings.getField('position_x_logo_footer').getControl().getHTML().min = "0";
            footerSettings.getField('position_y_logo_footer').getControl().getHTML().min = "0";
            footerSettings.getField('position_x_number_footer').getControl().getHTML().min = "0";
            footerSettings.getField('position_y_number_footer').getControl().getHTML().min = "0";
        };

        //Field "PDF security"
        outputFormDocPdfSecurityEnabled = new PMUI.field.DropDownListField({
            id: "outputDocDPFSecurity",
            name: "out_doc_pdf_security_enabled",
            label: "PDF security".translate(),
            labelWidth: "27%",
            valueType: "number",
            visible: false,

            options: [
                {
                    value: 0,
                    label: "Disabled".translate(),
                    selected: true
                },
                {
                    value: 1,
                    label: "Enabled".translate()

                }
            ],

            controlsWidth: 100,

            onChange: function (newValue, prevValue) {
                var visible = true;

                if (newValue == 0) {
                    visible = false;

                    outputFormDocPdfSecurityOpen.setValue("");
                    outputFormDocPdfSecurityOwner.setValue("");
                }

                password.setVisible(visible);
            }
        });

        //the form is 700px width, but with the tiny grows to 890
        formOutput = new PMUI.form.Form({
            id: 'outputForm',
            name: 'outputForm',
            fieldset: true,
            title: "",
            visibleHeader: false,
            width: DEFAULT_WINDOW_WIDTH - 43,
            items: [
                {
                    id: 'outputDocTitle',
                    pmType: "text",
                    name: 'out_doc_title',
                    label: "Title".translate(),
                    labelWidth: '27%',
                    controlsWidth: 300,
                    required: true
                },
                new CriteriaField({
                    id: 'outputDocFilenameGenerated',
                    pmType: "text",
                    name: 'out_doc_filename',
                    label: "Filename generated".translate(),
                    labelWidth: '27%',
                    controlsWidth: 300,
                    required: true
                }),
                {
                    id: 'outputDocDescription',
                    pmType: "textarea",
                    name: 'out_doc_description',
                    label: "Description".translate(),
                    labelWidth: '27%',
                    controlsWidth: 500,
                    rows: 100,
                    style: {cssClasses: ['mafe-textarea-resize']}
                },
                {
                    id: 'outputDocReportGenerator',
                    pmType: "dropdown",
                    name: 'out_doc_report_generator',
                    label: "Report Generator".translate(),
                    labelWidth: '27%',
                    require: true,
                    controlsWidth: 165,
                    options: [
                        {
                            label: "TCPDF".translate(),
                            value: "TCPDF"
                        },
                        {
                            label: "HTML2PDF (Old Version)".translate(),
                            value: "HTML2PDF"
                        }
                    ],
                    value: "TCPDF"
                },
                {
                    id: 'outputDocMedia',
                    pmType: "dropdown",
                    name: 'out_doc_media',
                    label: "Media".translate(),
                    labelWidth: '27%',
                    controlsWidth: 165,
                    options: [
                        {label: "Letter".translate(), value: "Letter"},
                        {label: "Legal".translate(), value: "Legal"},
                        {label: "Executive".translate(), value: "Executive"},
                        {label: "B5".translate(), value: "B5"},
                        {label: "Folio".translate(), value: "Folio"},
                        {label: "A0Oversize".translate(), value: "A0Oversize"},
                        {label: "A0".translate(), value: "A0"},
                        {label: "A1".translate(), value: "A1"},
                        {label: "A2".translate(), value: "A2"},
                        {label: "A3".translate(), value: "A3"},
                        {label: "A4".translate(), value: "A4"},
                        {label: "A5".translate(), value: "A5"},
                        {label: "A6".translate(), value: "A6"},
                        {label: "A7".translate(), value: "A7"},
                        {label: "A8".translate(), value: "A8"},
                        {label: "A9".translate(), value: "A9"},
                        {label: "A10", value: "A10"},
                        {label: "Screenshot640".translate(), value: "SH640"},
                        {label: "Screenshot800".translate(), value: "SH800"},
                        {label: "Screenshot1024".translate(), value: "SH1024"}
                    ]
                },
                {
                    id: 'outputDocOrientation',
                    pmType: "dropdown",
                    name: 'out_doc_landscape',
                    labelWidth: '27%',
                    label: "Orientation".translate(),
                    controlsWidth: 165,
                    options: [
                        {
                            label: "Portrait".translate(),
                            selected: true,
                            value: 0
                        },
                        {
                            label: "Landscape".translate(),
                            value: 1
                        }
                    ],
                    valueType: 'number'
                },
                docMargin,
                new SwitchField({
                    id: 'enableHeader',
                    labelWidth: "6%",
                    label: "Header".translate(),
                    name: "enableHeader",
                    value: '0',
                    controlsWidth: 54,
                    controlPositioning: 'vertical',
                    options: [
                        {
                            id: 'enableHeader',
                            disabled: false,
                            value: '1',
                            selected: false
                        }
                    ],
                    onChange: function (newVal, oldVal) {
                        if (newVal === '["1"]') {
                            headerSettings.setVisible(true);
                        } else {
                            headerSettings.setVisible(false);
                        }
                    }
                }),
                headerSettings,
                new SwitchField({
                    id: 'enableFooter',
                    labelWidth: "6%",
                    label: "Footer".translate(),
                    name: "enableFooter",
                    value: '0',
                    controlsWidth: 54,
                    controlPositioning: 'vertical',
                    options: [
                        {
                            id: 'enableFooter',
                            disabled: false,
                            value: '1',
                            selected: false
                        }
                    ],
                    onChange: function (newVal, oldVal) {
                        if (newVal === '["1"]') {
                            footerSettings.setVisible(true);
                        } else {
                            footerSettings.setVisible(false);
                        }
                    }
                }),
                footerSettings,
                {
                    id: 'outputDocToGenerate',
                    pmType: "dropdown",
                    name: 'out_doc_generate',
                    controlsWidth: 70,
                    labelWidth: '27%',
                    label: "Output Document to Generate".translate(),
                    options: [
                        {
                            label: "Both".translate(),
                            value: "BOTH"
                        },
                        {
                            label: "Doc".translate(),
                            value: "DOC"
                        },
                        {
                            label: "Pdf".translate(),
                            value: "PDF"
                        }
                    ],
                    value: "BOTH",
                    onChange: function (newValue, prevValue) {
                        if (newValue == "DOC") {
                            outputFormDocPdfSecurityEnabled.setVisible(false);
                            outputFormDocPdfSecurityEnabled.setValue(0);
                            password.setVisible(false);
                            outputFormDocPdfSecurityOpen.setValue("");
                            outputFormDocPdfSecurityOwner.setValue("");
                        } else {
                            outputFormDocPdfSecurityEnabled.setVisible(true);
                        }
                    }
                },
                outputFormDocPdfSecurityEnabled,
                password,
                {
                    id: 'outputDocEnableVersioning',
                    pmType: "dropdown",
                    name: "out_doc_versioning",
                    controlsWidth: 70,
                    labelWidth: '27%',
                    label: 'Enable versioning'.translate(),
                    options: [
                        {
                            label: "Yes".translate(),
                            value: 1
                        },
                        {
                            label: "No".translate(),
                            selected: true,
                            value: 0
                        }
                    ],
                    valueType: 'number'
                },
                new CriteriaField({
                    id: 'outputDocDestinationPath',
                    pmType: "text",
                    name: "out_doc_destination_path",
                    labelWidth: '27%',
                    label: "Destination Path".translate(),
                    controlsWidth: 340
                }),
                new CriteriaField({
                    id: 'outputDocTags',
                    pmType: "text",
                    name: "out_doc_tags",
                    labelWidth: '27%',
                    label: "Tags".translate(),
                    controlsWidth: 340
                }),
                {
                    id: "outputDocGenerateFileLink",
                    name: "cboByGeneratedFile",
                    pmType: "dropdown",
                    controlsWidth: 155,
                    labelWidth: "27%",
                    label: "By clicking on the generated file link".translate(),

                    options: [
                        {
                            value: 0,
                            label: "Open the file".translate()
                        },
                        {
                            label: "Download the file".translate(),
                            value: 1,
                            selected: true
                        }
                    ],

                    valueType: "number"
                }
            ],
            style: {
                cssProperties: {
                    marginLeft: '20px'
                }
            }
        });

        formOutput.style.addProperties({marginLeft: '20px'});
        gridOutput = new PMUI.grid.GridPanel({
            id: 'gridOutput',
            pageSize: 10,
            width: "96%",
            style: {
                cssClasses: ["mafe-gridPanel"]
            },
            filterPlaceholder: 'Search ...'.translate(),
            emptyMessage: 'No records found'.translate(),
            nextLabel: 'Next'.translate(),
            previousLabel: 'Previous'.translate(),
            tableContainerHeight: 374,
            customStatusBar: function (currentPage, pageSize, numberItems, criteria, filter) {
                return messagePageGrid(currentPage, pageSize, numberItems, criteria, filter);
            },
            columns: [
                {
                    id: 'gridOutputButtonShow',
                    title: '',
                    dataType: 'button',
                    buttonLabel: 'Show ID'.translate(),
                    columnData: "out_doc_uid",
                    buttonStyle: {
                        cssClasses: [
                            'mafe-button-show'
                        ]
                    },
                    onButtonClick: function (row, grid) {
                        var data = row.getData();
                        showUID(data.out_doc_uid);
                    }
                },
                {
                    title: 'Title'.translate(),
                    dataType: 'string',
                    width: '392px',
                    alignment: "left",
                    columnData: "out_doc_title",
                    sortable: true,
                    alignmentCell: 'left'
                },
                {
                    title: 'Type'.translate(),
                    dataType: 'string',
                    width: '100px',
                    alignmentCell: 'left',
                    columnData: "out_doc_type",
                    sortable: true
                },
                {
                    id: 'gridOutputButtonEdit',
                    title: '',
                    dataType: 'button',
                    buttonStyle: {
                        cssClasses: [
                            'mafe-button-edit'
                        ]
                    },
                    buttonLabel: 'Edit'.translate(),
                    onButtonClick: function (row, grid) {
                        messageRequired.hide();
                        setDataRow(row);
                        openFormForEditInMainWindow();
                    }
                },
                {
                    id: 'gridOutputButtonProperties',
                    title: '',
                    dataType: 'button',
                    buttonStyle: {
                        cssClasses: [
                            'mafe-button-properties'
                        ]
                    },
                    buttonLabel: 'Open Editor'.translate(),
                    onButtonClick: function (row, grid) {
                        setDataRow(row);
                        openTinyInMainWindow(row);
                    }
                },
                {
                    id: 'gridOutputButtonDelete',
                    title: '',
                    dataType: 'button',
                    buttonStyle: {
                        cssClasses: [
                            'mafe-button-delete'
                        ]
                    },
                    buttonLabel: 'Delete'.translate(),
                    onButtonClick: function (row, grid) {
                        setDataRow(row);
                        deleteDataRow();
                    }
                }
            ]
        });

        /**
         * Filter data of header settings
         * @param {Array} data
         * @return {Array}
         */
        setDataHeaderSettings = function (data) {
            var headerData = {
                "logo": data.header_logo,
                "logoWidth": data.logo_width_header,
                "logoPositionX": data.position_x_logo_header,
                "logoPositionY": data.position_y_logo_header,
                "title": data.header_title,
                "titleFontSize": data.font_size_header,
                "titleFontPositionX": data.position_x_title_header,
                "titleFontPositionY": data.position_y_title_header,
                "pageNumber": headerSettings.getField('page_number_header').value === '["1"]',
                "pageNumberTitle": headerSettings.getField('pagination_title_header').value,
                "pageNumberTotal": headerSettings.getField('total_number_page_header').value === '["1"]',
                "pageNumberPositionX": headerSettings.getField('position_x_number_header').value,
                "pageNumberPositionY": headerSettings.getField('position_y_number_header').value,
                "enableHeader": data.enableHeader === '["1"]'
            };
            //it is necessary to clean the data because it is already in the json
            delete data.header_logo;
            delete data.logo_width_header;
            delete data.position_x_logo_header;
            delete data.position_y_logo_header;
            delete data.header_title;
            delete data.font_size_header;
            delete data.position_x_title_header;
            delete data.position_y_title_header;
            delete data.page_number_header;
            delete data.pagination_title_header;
            delete data.total_number_page_header;
            delete data.position_x_number_header;
            delete data.position_y_number_header;
            delete data.enableHeader;
            return headerData;
        };

        /**
         * Filter data of footer settings
         * @param {Array} data
         * @return {Array}
         */
         setDataFooterSettings = function (data) {
            var footerData = {
                "logo": data.footer_logo,
                "logoWidth": data.logo_width_footer,
                "logoPositionX": data.position_x_logo_footer,
                "logoPositionY": data.position_y_logo_footer,
                "title": data.footer_title,
                "titleFontSize": data.font_size_footer,
                "titleFontPositionX": data.position_x_title_footer,
                "titleFontPositionY": data.position_y_title_footer,
                "pageNumber": footerSettings.getField('page_number_footer').value === '["1"]',
                "pageNumberTitle": footerSettings.getField('pagination_title_footer').value,
                "pageNumberTotal": footerSettings.getField('total_number_page_footer').value === '["1"]',
                "pageNumberPositionX": footerSettings.getField('position_x_number_footer').value,
                "pageNumberPositionY": footerSettings.getField('position_y_number_footer').value,
                "enableFooter": data.enableFooter === '["1"]'
            };
            //it is necessary to clean the data because it is already in the json
            delete data.footer_logo;
            delete data.logo_width_footer;
            delete data.position_x_logo_footer;
            delete data.position_y_logo_footer;
            delete data.footer_title;
            delete data.font_size_footer;
            delete data.position_x_title_footer;
            delete data.position_y_title_footer;
            delete data.page_number_footer;
            delete data.pagination_title_footer;
            delete data.total_number_page_footer;
            delete data.position_x_number_footer;
            delete data.position_y_number_footer;
            delete data.enableFooter;
            return footerData;
        };

        btnSaveWindowOutputDoc = new PMUI.ui.Button({
            id: 'btnSaveWindowOutputDoc',
            text: "Save".translate(),
            handler: function () {
                var itemOutPut;
                if ((navigator.userAgent.indexOf("MSIE") != -1) || (navigator.userAgent.indexOf("Trident") != -1)) {
                    itemOutPut = getData2PMUI(formOutput.html);
                } else {
                    itemOutPut = formOutput.getData();
                }
                if (itemOutPut.out_doc_title != "" && itemOutPut.out_doc_filename != "") {

                    itemOutPut['out_doc_type'] = "HTML";

                    var items = jQuery.parseJSON(itemOutPut['out_doc_pdf_security_permissions']);
                    itemOutPut['out_doc_pdf_security_permissions'] = '';
                    for (var i = 0; i < items.length; i += 1) {
                        itemOutPut['out_doc_pdf_security_permissions'] += (i == 0) ? items[i] : '|' + items[i];
                    }

                    itemOutPut["out_doc_landscape"] = parseInt(itemOutPut["out_doc_landscape"]);
                    itemOutPut["out_doc_pdf_security_enabled"] = parseInt(itemOutPut["out_doc_pdf_security_enabled"]);
                    itemOutPut["out_doc_versioning"] = parseInt(itemOutPut["out_doc_versioning"]);
                    itemOutPut["out_doc_open_type"] = parseInt(getData2PMUI(formOutput.html).cboByGeneratedFile);
                    itemOutPut["out_doc_header"] = setDataHeaderSettings(itemOutPut);
                    itemOutPut["out_doc_footer"] = setDataFooterSettings(itemOutPut);
                    if (dataOutPutDocument != '' && dataOutPutDocument != undefined) {
                        itemOutPut['out_doc_uid'] = dataOutPutDocument.out_doc_uid;
                        restClient = new PMRestClient({
                            endpoint: "output-document/" + dataOutPutDocument.out_doc_uid,
                            typeRequest: 'update',
                            data: itemOutPut,
                            functionSuccess: function (xhr, response) {
                                dataOutPutDocument = itemOutPut;
                                refreshGridPanelInMainWindow();
                            },
                            functionFailure: function (xhr, response) {
                                PMDesigner.msgWinError(response.error.message);
                            },
                            messageError: "There are problems updating the OutputDocument, please try again.".translate(),
                            messageSuccess: 'Output Document edited successfully'.translate(),
                            flashContainer: gridOutput
                        });
                        restClient.executeRestClient();
                    } else {
                        if (1 === parseInt(itemOutPut.out_doc_pdf_security_enabled) && (itemOutPut.out_doc_pdf_security_open_password.trim() === "" || itemOutPut.out_doc_pdf_security_owner_password.trim() === "")) {
                            password.getItems()[0].getItems()[0].isValid();
                            password.getItems()[0].getItems()[1].isValid();
                            return false;
                        }
                        itemOutPut['out_doc_uid'] = '';
                        restClient = new PMRestClient({
                            endpoint: "output-document",
                            typeRequest: 'post',
                            data: itemOutPut,
                            functionSuccess: function (xhr, response) {
                                dataOutPutDocument = itemOutPut;
                                refreshGridPanelInMainWindow();
                            },
                            functionFailure: function (xhr, response) {
                                PMDesigner.msgWinError(response.error.message);
                            },
                            messageError: "There are problems saved the OutputDocument, please try again.".translate(),
                            messageSuccess: 'Output Document saved successfully'.translate(),
                            flashContainer: gridOutput
                        });
                        restClient.executeRestClient();
                    }
                    clearDataRow();
                } else {
                    formOutput.getField("out_doc_title").isValid();
                    formOutput.getField("out_doc_filename").isValid();
                }
            },
            buttonType: 'success'
        });

        btnCloseWindowOutputDoc = new PMUI.ui.Button({
            id: 'btnCloseWindowOutputDoc',
            text: "Cancel".translate(),
            handler: function () {
                clickedClose = false;
                isDirtyFormOutput();
            },
            buttonType: 'error'
        });

        newButtonOutput = new PMUI.ui.Button({
            id: 'outputButtonNew',
            text: 'Create'.translate(),
            height: "36px",
            width: 100,
            style: {
                cssClasses: [
                    'mafe-button-create'
                ]
            },
            handler: function () {
                clearDataRow();
                openFormInMainWindow();
            }
        });

        btnCancelTiny = new PMUI.ui.Button({
            id: 'btnCloseTiny',
            text: 'Cancel'.translate(),
            handler: function () {
                /*if (typeof dataOutPutDocument['externalType'] != 'undefined' && dataOutPutDocument['externalType']) {
                 winMainOutputDocument.close();
                 return;
                 }*/
                PMDesigner.hideAllTinyEditorControls();
                clickedClose = false;
                isDirtyFormOutput();
            },
            buttonType: 'error'
        });

        btnSaveTiny = new PMUI.ui.Button({
            id: 'btnSaveTiny',
            text: 'Save'.translate(),
            handler: function () {
                PMDesigner.hideAllTinyEditorControls();
                dataOutPutDocument['out_doc_template'] = tinyMCE.activeEditor.getContent();
                updateOutput(dataOutPutDocument);
                if (typeof dataOutPutDocument['externalType'] != 'undefined' && dataOutPutDocument['externalType']) {
                    winMainOutputDocument.close();
                    return;
                }
                clearDataRow();
                refreshGridPanelInMainWindow();
            },
            buttonType: 'success'
        });

        winMainOutputDocument = new PMUI.ui.Window({
            id: "winMainOutputDocument",
            title: "Output Documents".translate(),
            height: DEFAULT_WINDOW_HEIGHT,
            width: DEFAULT_WINDOW_WIDTH,
            buttonPanelPosition: "bottom",
            onBeforeClose: function () {
                PMDesigner.hideAllTinyEditorControls();
                clickedClose = true;
                isDirtyFormOutput();
            },
            footerItems: [
                btnCancelTiny,
                btnSaveTiny,
                btnCloseWindowOutputDoc,
                btnSaveWindowOutputDoc]
        });

        formOutput.addItem(editorTiny);
        formOutput.footer.setVisible(false);

        winMainOutputDocument.addItem(gridOutput);
        winMainOutputDocument.addItem(formOutput);

        refreshGridPanelInMainWindow();

        validateKeysField(docMargin.getField('out_doc_left_margin').getControls()[0].getHTML(), ['isbackspace', 'isnumber']);
        validateKeysField(docMargin.getField('out_doc_right_margin').getControls()[0].getHTML(), ['isbackspace', 'isnumber']);
        validateKeysField(docMargin.getField('out_doc_top_margin').getControls()[0].getHTML(), ['isbackspace', 'isnumber']);
        validateKeysField(docMargin.getField('out_doc_bottom_margin').getControls()[0].getHTML(), ['isbackspace', 'isnumber']);
        changeType();
        setMinValue();

        if (typeof listOutputDocs !== "undefined") {
            winMainOutputDocument.open();
            $('#gridOutput .pmui-textcontrol').css({'margin-top': '5px', width: '250px'});
            messageRequired = $(document.getElementById("requiredMessage"));
            applyStyleWindowForm(winMainOutputDocument);

            editorTiny.isInitialized = false;
            winMainOutputDocument.footer.html.style.textAlign = 'right';

            gridOutput.dom.toolbar.appendChild(newButtonOutput.getHTML());
            newButtonOutput.defineEvents();
            winMainOutputDocument.defineEvents();
            disableAllItems();
            winMainOutputDocument.getItems()[0].setVisible(true);
        }
    };

    PMDesigner.output.showTiny = function (uid) {
        getItemdOutput = function () {
            var restClientGet = new PMRestClient({
                endpoint: 'output-document/' + uid,
                typeRequest: 'get',
                functionSuccess: function (xhr, response) {
                    dataOutPutDocument = response;
                },
                functionFailure: function (xhr, response) {
                    PMDesigner.msgWinError(response.error.message);
                },
                messageError: "There are problems getting the output documents, please try again.".translate()
            });
            restClientGet.executeRestClient();
        };
        getItemdOutput();
        dataOutPutDocument['externalType'] = true;
        openTinyInMainWindow(dataOutPutDocument);
    };

    PMDesigner.output.create = function () {
        openFormInMainWindow();
    };
}());
