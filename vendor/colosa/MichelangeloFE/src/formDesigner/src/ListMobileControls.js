(function () {
    var ListMobileControls = function () {
        ListMobileControls.prototype.init.call(this);
    };
    /**
     * Initialize mobile controls.
     */
    ListMobileControls.prototype.init = function () {
        this.body = $("<div style='background:#262932;overflow:hidden;padding:4px;'></div>");
        this.controls = [
            {
                url: "" + $.imgUrl + "fd-geomap-mobile.png",
                label: "geomap".translate(),
                render: FormDesigner.main.TypesControl.geomap
            }, {
                url: "" + $.imgUrl + "fd-qrcode-mobile.png",
                label: "qr code".translate(),
                render: FormDesigner.main.TypesControl.qrcode
            }, {
                url: "" + $.imgUrl + "fd-signature-mobile.png",
                label: "signature".translate(),
                render: FormDesigner.main.TypesControl.signature
            }, {
                url: "" + $.imgUrl + "fd-image2.png",
                label: "image".translate(),
                render: FormDesigner.main.TypesControl.imagem
            }, {
                url: "" + $.imgUrl + "fd-audio-mobile.png",
                label: "audio".translate(),
                render: FormDesigner.main.TypesControl.audiom
            }, {
                url: "" + $.imgUrl + "fd-video-mobile.png",
                label: "video".translate(),
                render: FormDesigner.main.TypesControl.videom
            }
        ];
        this.load();
    };
    /**
     * Load mobile controls.
     */
    ListMobileControls.prototype.load = function () {
        var i;
        for (i = 0; i < this.controls.length; i += 1) {
            this.controls[i].target = this.addItem(this.controls[i]);
        }
    };
    /**
     * Add mobile control.
     * @param control
     * @return {PMUI.item.ListItem|ListItemL#1.ListItem}
     */
    ListMobileControls.prototype.addItem = function (control) {
        var item;
        item = new FormDesigner.main.ListItem(control);
        this.body.append(item.body);
        return item;
    };
    FormDesigner.extendNamespace('FormDesigner.main.ListMobileControls', ListMobileControls);
}());
