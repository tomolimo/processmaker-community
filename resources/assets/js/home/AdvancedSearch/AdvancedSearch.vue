<template>
    <div id="v-mycases3" ref="v-mycases2" class="v-container-mycases">
        <b-alert
            :show="dismissCountDown"
            dismissible
            :variant="variant"
            @dismissed="dismissCountDown = 0"
            @dismiss-count-down="countDownChanged"
        >
            {{ message }}
        </b-alert>
        <button-fleft :data="newCase"></button-fleft>
        <h5>{{ $t("ID_ADVANCEDSEARCH") }}</h5>

        <AdvancedFilter
            :id="id"
            :name="name"
            :filters="filters"
            @onJumpCase="onJumpCase"
            @onSubmit="onSubmitFilter"
            @onRemoveFilter="onRemoveFilter"
            @onSearch="onSearch"
            @onUpdateFilters="onUpdateFilters"
        />

        <modal-new-request ref="newRequest"></modal-new-request>
        <settings-popover :options="formatColumnSettings(options.headings)" target="pm-dr-column-settings" @onUpdateColumnSettings="onUpdateColumnSettings" :key="random+1" :selected="formatColumnSelected(columns)"/>
        <v-server-table
            :data="tableData"
            :columns="columns"
            :options="options"
            ref="vueTable"
            @row-click="configRowClick"
            :key="random"
        >
            <div slot="info" slot-scope="props">
                <b-icon
                    icon="exclamation-circle-fill"
                    variant="primary"
                    @click="openCaseDetail(props.row)"
                ></b-icon>
            </div>
            <div slot="case_number" slot-scope="props">
                {{ props.row.CASE_NUMBER }}
            </div>
            <div slot="thread_title" slot-scope="props">
                <ThreadTitleCell :data="props.row.THREAD_TITLE" />
            </div>
            <div slot="case_status" slot-scope="props">
                {{ props.row.APP_STATUS }}
            </div>
            <div slot="process_category" slot-scope="props">
                {{ props.row.PROCESS_CATEGORY }}
            </div>
            <div slot="process_name" slot-scope="props">
                {{ props.row.PROCESS_NAME }}
            </div>
            <div slot="task" slot-scope="props">
                <TaskCell :data="props.row.TASK" />
            </div>
            <div slot="status" slot-scope="props">
                {{ props.row.STATUS }}
            </div>
            <div slot="current_user" slot-scope="props">
                <CurrentUserCell :data="props.row.USER_DATA" />
            </div>
            <div slot="start_date" slot-scope="props">
                {{ props.row.START_DATE }}
            </div>
            <div slot="finish_date" slot-scope="props">
                {{ props.row.FINISH_DATE }}
            </div>
            <div slot="duration" slot-scope="props">
                {{ props.row.DURATION }}
            </div>
              <div slot="actions" slot-scope="props">
                <div class="btn-default"  v-bind:style="{ color: props.row.MESSAGE_COLOR}" @click="openComments(props.row)">
                    <span class="fas fa-comments"></span>
                </div>
            </div>
        </v-server-table>
        <vue-simple-context-menu
            :elementId="idContextMenu"
            :options="contextMenuItems"
            :ref="idContextMenu"
            @option-clicked="contextMenuItemClicked"
        />
        <ModalComments
            ref="modal-comments"
            @postNotes="onPostNotes"
        ></ModalComments>
        <ModalClaimCase ref="modal-claim-case" @claimCatch="claimCatch"></ModalClaimCase>
    </div>
</template>
<script>
import ButtonFleft from "../../components/home/ButtonFleft.vue";
import ModalNewRequest from "../ModalNewRequest.vue";
import AdvancedFilter from "../../components/search/AdvancedFilter";
import TaskCell from "../../components/vuetable/TaskCell.vue";
import CurrentUserCell from "../../components/vuetable/CurrentUserCell.vue";
import ModalComments from "../modal/ModalComments.vue";
import ThreadTitleCell from "../../components/vuetable/ThreadTitleCell.vue"
import ModalClaimCase from "../modal/ModalClaimCase.vue";
import api from "../../api/index";
import utils from "../../utils/utils";
import defaultMixin from "./defaultMixins.js";
import customMixin from "./customMixins";
import { Event } from "vue-tables-2";

export default {
    name: "AdvancedSearch",
    mixins: [defaultMixin, customMixin],
    components: {
        AdvancedFilter,
        ButtonFleft,
        ModalNewRequest,
        TaskCell,
        CurrentUserCell,
        ModalClaimCase,
        ModalComments,
        ThreadTitleCell,
    },
    props: ["id", "name", "filters"],
    data() {
        let that = this;
        return {
            dismissSecs: 5,
            dismissCountDown: 0,
            message: "",
            variant: "info",
            metrics: [],
            filter: "CASES_INBOX",
            allView: [],
            filtersModel: {},
            filterHeader: "STARTED_BY_ME",
            headers: [],
            columMap: {
                case_number: "APP_NUMBER",
                thread_title: "DEL_TITLE",
                process_name: "PRO_TITLE",
            },
            newCase: {
                title: this.$i18n.t("ID_NEW_CASE"),
                class: "btn-success",
                onClick: () => {
                    this.$refs["newRequest"].show();
                },
            },
            columns: [
                "case_number",
                "process_category",
                "process_name",
                "status",
                "thread_title",
                "task",
                "current_user",
                "start_date",
                "finish_date",
                "duration",
                "actions",
            ],
            tableData: [],
            options: {
                filterable: false,
                perPageValues: [],
                pagination: { 
                    chunk: 3,
                    nav: 'scroll',
                    edge: true
                },
                headings: {
                    case_number: this.$i18n.t("ID_MYCASE_NUMBER"),
                    process_category: this.$i18n.t("ID_CATEGORY_PROCESS"),
                    process_name: this.$i18n.t("ID_PROCESS_NAME"),
                    status: this.$i18n.t("ID_STATUS"),
                    thread_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
                    task: this.$i18n.t("ID_TASK"),
                    current_user: this.$i18n.t("ID_CURRENT_USER"),
                    start_date: this.$i18n.t("ID_START_DATE"),
                    finish_date: this.$i18n.t("ID_FINISH_DATE"),
                    duration: this.$i18n.t("ID_DURATION"),
                    actions: "",
                },
                texts: {
                    count:this.$i18n.t("ID_SHOWING_FROM_RECORDS_COUNT"),
                    first: "<<",
                    last: ">>",
                    filter: this.$i18n.t("ID_FILTER") + ":",
                    limit: this.$i18n.t("ID_RECORDS") + ":",
                    page: this.$i18n.t("ID_PAGE") + ":",
                    noResults: this.$i18n.t("ID_NO_MATCHING_RECORDS")
                },
                selectable: {
                    mode: "single",
                    only: function(row) {
                        return true;
                    },
                    selectAllMode: "page",
                    programmatic: false,
                },
                sortable: ["case_number"],
                requestFunction(data) {
                    return this.$parent.$parent.getCasesForVueTable(data);
                },
                customFilters: ["myfilter"]
            },
            pmDateFormat: window.config.FORMATS.dateFormat,
            clickCount: 0,
            singleClickTimer: null,
            statusTitle: {
                "ON_TIME": this.$i18n.t("ID_IN_PROGRESS"),
                "OVERDUE": this.$i18n.t("ID_TASK_OVERDUE"),
                "DRAFT": this.$i18n.t("ID_IN_DRAFT"),
                "PAUSED": this.$i18n.t("ID_PAUSED"),
                "UNASSIGNED": this.$i18n.t("ID_UNASSIGNED")
            }
        };
    },
    mounted() {
        Event.$on('clearSortEvent', this.clearSort);
    },
    watch: {
        id: function() {
            this.$refs.vueTable.refresh();
        },
    },
    methods: {
        /**
         * Row click event handler
         * @param {object} event
         */
        onRowClick(event) {
            let self = this;
            self.clickCount += 1;
            if (self.clickCount === 1) {
                self.singleClickTimer = setTimeout(function() {
                    self.clickCount = 0;            
                }, 400);
            } else if (self.clickCount === 2) {
                clearTimeout(self.singleClickTimer);
                self.clickCount = 0;
                self.openCaseDetail(event.row);
            }
        },
        /**
         * Get cases data by header
         */
        getCasesForVueTable(data) {
            let that = this,
                dt,
                paged,
                limit = data.limit,
                filters = {},
                start = data.page === 1 ? 0 : limit * (data.page - 1),
                sort = "";
            paged = start + "," + limit ;
            filters = {
                limit: limit,
                offset: start
            };
            _.forIn(this.filters, function (item, key) {
                if(filters && item.value) {
                    filters[item.filterVar] = item.value;
                }
            });

            sort = that.prepareSortString(data);
            if (sort) {
                filters["sort"] = sort;
            }
            return new Promise((resolutionFunc, rejectionFunc) => {
                api.cases
                    .search(filters)
                    .then((response) => {
                        dt = that.formatDataResponse(response.data.data);
                        resolutionFunc({
                            data: dt,
                            count:
                                dt.length < data.limit
                                    ? data.limit * data.page
                                    : data.limit * data.page + 1,
                        });
                    })
                    .catch((e) => {
                        rejectionFunc(e);
                    });
            });
        },
        /**
         * Prepare sort string to be sended in the service
         * @param {object} data
         * @returns {string}
         */
        prepareSortString(data) {
            let sort = "";
            if (data.orderBy && this.columMap[data.orderBy]) {
                sort = `${this.columMap[data.orderBy]},${
                    data.ascending === 1 ? "ASC" : "DESC"
                }`;
            }
            return sort;
        },
        /**
         * Format the service response
         */
        formatDataResponse(response) {
            let data = [];
            _.forEach(response, (v) => {
                data.push({
                    CASE_NUMBER: v.APP_NUMBER,
                    THREAD_TITLE: v.THREAD_TITLES,
                    PROCESS_NAME: v.PRO_TITLE,
                    PROCESS_CATEGORY: v.CATEGORY,
                    TASK: this.formatTasks(v.THREAD_TASKS),
                    USER_DATA: this.formatUser(v.THREAD_USERS),
                    START_DATE: v.APP_CREATE_DATE_LABEL,
                    FINISH_DATE: v.APP_FINISH_DATE_LABEL,
                    DURATION: v.DURATION,
                    DEL_INDEX: v.DEL_INDEX,
                    APP_UID: v.APP_UID,
                    STATUS: this.$i18n.t("ID_CASES_STATUS_"+ v.APP_STATUS.toUpperCase()),
                    PRO_UID: v.PRO_UID,
                    TAS_UID: v.TAS_UID,
                    MESSAGE_COLOR: v.CASE_NOTES_COUNT > 0 ? "black":"silver"
                });
            });
            return data;
        },
        /**
         * Format the data for the column task
         */
        formatTasks(data) {
            var i,
                dataFormat = [];
            for (i = 0; i < data.length; i += 1) {
                dataFormat.push({
                    TITLE: data[i].tas_title,
                    CODE_COLOR: data[i].tas_color,
                    DELAYED_TITLE: data[i].tas_status === "OVERDUE" ?
                            this.$i18n.t("ID_DELAYED") + ":" : this.statusTitle[data[i].tas_status],
                    DELAYED_MSG: data[i].tas_status === "OVERDUE" ? data[i].delay : ""

                });
            }
            return dataFormat;
            
        },
        formatUser(data) {
            var i,
                dataFormat = [],
                userDataFormat;
            for (i = 0; i < data.length; i += 1) {
                userDataFormat = utils.userNameDisplayFormat({
                        userName: data[i].usr_firstname,
                        firstName: data[i].usr_lastname,
                        lastName: data[i].usr_username,
                        format: window.config.FORMATS.format || null
                    });
                dataFormat.push({
                    USERNAME_DISPLAY_FORMAT: userDataFormat !== "" ? userDataFormat : this.$i18n.t("ID_UNASSIGNED"),
                    EMAIL: data[i].user_tooltip.usr_email,
                    POSITION: data[i].user_tooltip.usr_position,
                    AVATAR: userDataFormat !== "" ? window.config.SYS_SERVER_AJAX +
                                window.config.SYS_URI +
                                `users/users_ViewPhotoGrid?pUID=${data[i].user_id}` : "",
                    UNASSIGNED: userDataFormat !== "" ? true : false
                });
            }
            return dataFormat;
        },
        /**
         * Convert string to date format
         *
         * @param {string} value
         * @return {date} myDate
         */
        convertDate(value) {
            myDate = new Date(1900, 0, 1, 0, 0, 0);
            try {
                if (!isNaN(Date.parse(value))) {
                    var myArray = value.split(" ");
                    var myArrayDate = myArray[0].split("-");
                    if (myArray.length > 1) {
                        var myArrayHour = myArray[1].split(":");
                    } else {
                        var myArrayHour = new Array("0", "0", "0");
                    }
                    var myDate = new Date(
                        myArrayDate[0],
                        myArrayDate[1] - 1,
                        myArrayDate[2],
                        myArrayHour[0],
                        myArrayHour[1],
                        myArrayHour[2]
                    );
                }
            } catch (err) {
                throw new Error(err);
            }
            return myDate;
        },
        /**
         * Get a format for specific date
         *
         * @param {string} d
         * @return {string} dateToConvert
         */
        dateFormatCases(d) {
            let dateToConvert = d;
            const stringToDate = this.convertDate(dateToConvert);
            if (this.pmDateFormat === "Y-m-d H:i:s") {
                dateToConvert = dateFormat(stringToDate, "yyyy-mm-dd HH:MM:ss");
            } else if (this.pmDateFormat === "d/m/Y") {
                dateToConvert = dateFormat(stringToDate, "dd/mm/yyyy");
            } else if (this.pmDateFormat === "m/d/Y") {
                dateToConvert = dateFormat(stringToDate, "mm/dd/yyyy");
            } else if (this.pmDateFormat === "Y/d/m") {
                dateToConvert = dateFormat(stringToDate, "yyyy/dd/mm");
            } else if (this.pmDateFormat === "Y/m/d") {
                dateToConvert = dateFormat(stringToDate, "yyyy/mm/dd");
            } else if (this.pmDateFormat === "F j, Y, g:i a") {
                dateToConvert = dateFormat(
                    stringToDate,
                    "mmmm d, yyyy, h:MM tt"
                );
            } else if (this.pmDateFormat === "m.d.y") {
                dateToConvert = dateFormat(stringToDate, "mm.dd.yy");
            } else if (this.pmDateFormat === "j, n, Y") {
                dateToConvert = dateFormat(stringToDate, "d,m,yyyy");
            } else if (this.pmDateFormat === "D M j G:i:s T Y") {
                dateToConvert = dateFormat(
                    stringToDate,
                    "ddd mmm d HH:MM:ss Z yyyy"
                );
            } else if (this.pmDateFormat === "M d, Y") {
                dateToConvert = dateFormat(stringToDate, "mmm dd, yyyy");
            } else if (this.pmDateFormat === "m D, Y") {
                dateToConvert = dateFormat(stringToDate, "mm ddd, yyyy");
            } else if (this.pmDateFormat === "D d M, Y") {
                dateToConvert = dateFormat(stringToDate, "ddd dd mmm, yyyy");
            } else if (this.pmDateFormat === "D M, Y") {
                dateToConvert = dateFormat(stringToDate, "ddd mmm, yyyy");
            } else if (this.pmDateFormat === "d M, Y") {
                dateToConvert = dateFormat(stringToDate, "dd mmm, yyyy");
            } else if (this.pmDateFormat === "d m, Y") {
                dateToConvert = dateFormat(stringToDate, "dd mm, yyyy");
            } else if (this.pmDateFormat === "d.m.Y") {
                dateToConvert = dateFormat(stringToDate, "mm.dd.yyyy");
            } else {
                dateToConvert = dateFormat(
                    stringToDate,
                    'dd "de" mmmm "de" yyyy'
                );
            }
            return dateToConvert;
        },
        /**
         * Open selected cases in the inbox
         *
         * @param {object} item
         */
        openCase(item) {
            const action = "todo";
            if (this.isIE) {
                window.open(
                    "../../../cases/open?APP_UID=" +
                        item.row.APP_UID +
                        "&DEL_INDEX=" +
                        item.row.DEL_INDEX +
                        "&action=" +
                        action
                );
            } else {
                window.location.href =
                    "../../../cases/open?APP_UID=" +
                    item.row.APP_UID +
                    "&DEL_INDEX=" +
                    item.row.DEL_INDEX +
                    "&action=" +
                    action;
            }
        },
        /**
         * Open case detail
         *
         * @param {object} item
         */
        openCaseDetail(item) {
            this.$emit("onUpdateDataCase", {
                APP_UID: item.APP_UID,
                DEL_INDEX: item.DEL_INDEX,
                PRO_UID: item.PRO_UID,
                TAS_UID: item.TAS_UID,
                APP_NUMBER: item.CASE_NUMBER
            });
            this.$emit("onUpdatePage", "case-detail");
        },
        /**
         * Method to validate if the case is unassigned
         */
        onJumpCase(caseNumber) {
            api.cases.pendingtask({APP_NUMBER:caseNumber}).then((response) => {
                if (response.data && response.data[0] && response.data[0]['USR_ID'] == 0) {
                    this.claimCase(response.data[0]);
                } else {
                    this.jump(caseNumber);
                }
            }).catch((e)=>{
                this.jump(caseNumber);
            });
        },
        /**
         * Method to jump case based in APP_NUMBER
         */
        jump (caseNumber) {
            let self = this;
            const params = {
                APP_NUMBER: caseNumber,
                ACTION: "jump",
                ACTION_FROM_LIST: "search",
            };
          
            api.cases
                .jump(params)
                .then(function(response) {
                    if (response.data.exists) {
                        self.$parent.dataCase = params;
                        self.$parent.page = "XCase";
                    } else {
                        self.showAlert(response.data.message, "danger");                     
                    }
                })
                .catch((err) => {
                    self.showAlert(err.message, "danger");          
                });
        },
        /**
         * Updates the alert dismiss value to update
         * dismissCountDown and decrease
         * @param {mumber}
         */
        countDownChanged(dismissCountDown) {
            this.dismissCountDown = dismissCountDown;
        },
        /**
         * Show the alert message
         * @param {string} message - message to be displayen in the body
         * @param {string} type - alert type
         */
        showAlert(message, type) {
            this.message = message;
            this.variant = type || "info";
            this.dismissCountDown = this.dismissSecs;
        },
        /**
         * Handler submit filter
         * @param {object} data - data returned from the server
         */
        onSubmitFilter(params) {
            if (params.type === "update") {
                api.filters
                    .put({
                        id: params.id,
                        name: params.name,
                        filters: JSON.stringify(params.filters),
                    })
                    .then((response) => {
                        this.$emit("onSubmitFilter", params);
                    })
                    .catch((e) => {
                        this.showAlert(e.message, "danger");
                    });
            } else {
                api.filters
                    .post({
                        name: params.name,
                        filters: JSON.stringify(params.filters),
                    })
                    .then((response) => {
                        this.$emit("onSubmitFilter", response.data);
                    })
                    .catch((e) => {
                        this.showAlert(e.message, "danger");
                    });
            }
        },
        /**
         * Handler on remove filter
         * @param {number} id - data returned fron the server
         */
        onRemoveFilter(id) {
            api.filters
                .delete({
                    id: this.id,
                })
                .then((response) => {
                    this.$emit("onUpdateFilters", {});
                    this.$emit("onRemoveFilter", id);
                })
                .catch((e) => {
                    this.showAlert(e.message, "danger");
                });
        },
        /**
         * Handler on search filter
         * @param {number} id - data returned fron the server
         */
        onSearch(params) {
            this.$nextTick(() => {
                this.$refs.vueTable.refresh();
            });
        },
        onUpdateFilters(params) {
            this.$emit("onUpdateFilters", params);
        },
        /**
         * Open the case notes modal
         * @param {object} data - needed to create the data
         */
        openComments(data) {
            let that = this;
            api.cases.open(_.extend({ ACTION: "todo" }, data)).then(() => {
                that.$refs["modal-comments"].dataCase = data;
                that.$refs["modal-comments"].show();
            });
        },
        /**
         * Post notes event handler
         */
        onPostNotes() {
            this.$refs["vueTable"].getData();
        },
        /**
         * Reset the sort in the table
         */
        clearSort() {
            if (this.$refs['vueTable']) {
                this.$refs['vueTable'].setOrder(false);
            }
        },
        /**
         * Claim case
         *
         * @param {object} item
         */
        claimCase(item) {
            let that = this;
            api.cases.open(_.extend({ ACTION: "unassigned" }, item)).then(() => {
                api.cases.cases_open(_.extend({ ACTION: "todo" }, item)).then(() => {
                that.$refs["modal-claim-case"].data = item;
                that.$refs["modal-claim-case"].show();
                });
            });
        },
        /**
         * Claim catch error handler message
         */
        claimCatch(message) {
            this.showAlert(message, "danger");
        }
    },
};
</script>
<style>
.VueTables__row {
  height: 75px;
}
.VuePagination__count {
    display: none;
}
.v-container-mycases {
    padding-top: 20px;
    padding-bottom: 20px;
    padding-left: 50px;
    padding-right: 50px;
}
.v-user-cell {
    display: inline-block;
}
.v-user-cell-ellipsis {
    white-space: nowrap;
    width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
