import _ from "lodash";
import api from "../../api/index";
export default {
    data() {
        return {
            random: 1,
            defaultColumnsDisabled: [
                "process_category"
            ]
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
        }
    }
}