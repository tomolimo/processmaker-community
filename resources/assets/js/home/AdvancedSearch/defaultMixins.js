import api from "../../api/index";
export default {
    data() {
        return {
            random: 1,
            idContextMenu: "pm-ad-context-menu",
            contextMenuItems: []
        };
    },
    methods: {
        /**
         * Format columns for custom columns
         * @param {*} headings 
         * @returns 
         */
        formatColumnSettings(headings) {
            let res = [];
            _.forEach(headings, function (value, key) {
                if (key != "actions") {
                    res.push({ value, key });
                }
            });
            return res;
        },
        /**
         * Formating the columns selected
         * @param {*} columns 
         * @returns 
         */
        formatColumnSelected(columns) {
            let cols = _.clone(columns);
            cols.pop();
            return cols;
        },
        /**
         * Event handler when update the settings columns
         * @param {*} columns 
         */
        onUpdateColumnSettings(columns) {
            let cols = columns;
            if (_.findIndex(cols, 'actions') == -1) {
                cols.push("actions");
            }
            this.columns = cols;
            this.random = _.random(0, 10000000000);
        },
        /**
         * Row click event handler
         * @param {*} event 
         */
        configRowClick(event) {
            if (event.event.button === 2) {
                this.onRowContextMenu(event);
            } else {
                this.onRowClick(event);
            }
        },
        /**
         * Context Menu event handler
         * @param {*} event 
         */
        onRowContextMenu(event) {
            this.$refs[this.idContextMenu].showMenu(event.event, event.row);
        },
        /**
         * Row click event handler
         * @param {*} event 
         */
        onRowClick(event) {
            var self = this;
            self.clickCount += 1;
            if (self.clickCount === 1) {
                self.singleClickTimer = setTimeout(function () {
                    self.clickCount = 0;
                }, 400);
            } else if (self.clickCount === 2) {
                clearTimeout(self.singleClickTimer);
                self.clickCount = 0;
                self.openCaseDetail(event.row);
            }
        },
        /**
         * Handler for item context menu clicked
         */
        contextMenuItemClicked(event) {
        }
    }
}