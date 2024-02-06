<template>
    <div id="v-todo" ref="v-todo" class="v-container-todo">
        <button-fleft :data="newCase"></button-fleft>
        <modal-new-request ref="newRequest"></modal-new-request>
        <ModalPauseCase ref="modal-pause-case"></ModalPauseCase>
        <ModalReassignCase ref="modal-reassign-case"></ModalReassignCase>
        <b-alert
            :show="dataAlert.dismissCountDown"
            dismissible
            :variant="dataAlert.variant"
            @dismissed="dataAlert.dismissCountDown = 0"
            @dismiss-count-down="countDownChanged"
        >
            {{ dataAlert.message }}
        </b-alert>
        <CustomFilter
            :filters="filters"
            :title="titleMap[data.pageParent].label"
            :icon="titleMap[data.pageParent].icon"
            :filterItems="filterItems"
            @onRemoveFilter="onRemoveFilter"
            @onUpdateFilters="onUpdateFilters"
        />
        <multiview-header
            :data="dataMultiviewHeader"
            :dataSubtitle="dataSubtitle"
        />

        <settings-popover
            :options="settingOptions"
            target="pm-dr-column-settings2"
            @onUpdateColumnSettings="onUpdateColumnSettings"
            :key="random + 1"
            :selected="columns"
        />
        <v-server-table
            v-if="typeView === 'GRID'"
            :data="tableData"
            :columns="columns"
            :options="getTableOptions()"
            ref="vueTable"
            @row-click="onRowClick"
            :key="random"
            name="todo"
        >
            <div
                v-for="col in columns"
                :slot="col"
                slot-scope="props"
                :key="col.id"
            >
                <div 
                    v-if="col === 'detail'"
                >
                    <div
                        class="btn-default"
                        :class="props.row.INIT_DATE ? '' : 'pm-main-text-color '"
                        @click="openCaseDetail(props.row)"
                    >
                        <i class="fas fa-info-circle"></i>
                    </div>
                </div>
                <div
                    v-if="col === 'case_number'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.CASE_NUMBER }}
                </div>
                <div
                    v-if="col === 'case_title'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.THREAD_TITLE }}
                </div>
                <div
                    v-if="col === 'process_category'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.PROCESS_CATEGORY }}
                </div>
                <div
                    v-if="col === 'process_name'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.PROCESS_NAME }}
                </div>
                <div
                    v-if="col === 'task'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    <TaskCell :data="props.row.TASK" />
                </div>
                <div
                    v-if="col === 'send_by'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    <CurrentUserCell :data="props.row.USER_DATA" />
                </div>
                <div
                    v-if="col === 'current_user'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.USERNAME_DISPLAY_FORMAT }}
                </div>
                <div
                    v-if="col === 'due_date'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.DUE_DATE }}
                </div>
                <div
                    v-if="col === 'delegation_date'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.DELEGATION_DATE }}
                </div>
                <div
                    v-if="col === 'priority'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row.PRIORITY }}
                </div>
                <div
                    :slot="col"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    {{ props.row[col] }}
                </div>
                <div
                    v-if="col === 'actions'"
                    :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
                >
                    <ellipsis :data="updateDataEllipsis(props.row)"> </ellipsis>
                </div>
            </div>
        </v-server-table>
        <VueCardView
            v-if="typeView === 'CARD'"
            :options="getVueViewOptions()"
            ref="vueCardView"
        >
            <b-col
                sm="11"
                slot="actions"
                slot-scope="props"
                class="vp-inbox-list-actions"
            >
                <b-row>
                    <b-col sm="12">
                        <div
                            class="v-pm-card-info"
                            :class="props.item.INIT_DATE ? '' : 'pm-main-text-color '"
                            @click="openCaseDetail(props.item)"
                        >
                            <i class="fas fa-info-circle"></i>
                        </div>
                    </b-col>
                    <b-col sm="12">
                        <ellipsis class="ellipsis-container" :data="updateDataEllipsis(props.item)"> </ellipsis>
                    </b-col>
                </b-row>
            </b-col>
            <template v-for="column in cardColumns" :slot="column" slot-scope="props" class="v-card-text">
                <div :class="props.item.INIT_DATE ? '' : 'font-weight-bold'" :key="column">
                    <span class="v-card-text-dark">
                        {{ getCustomHeading(column, props) }} :
                    </span>
                    <span  v-if="column === 'case_number'" class="v-card-text-highlight">
                        {{ props["item"]["CASE_NUMBER"] }}
                    </span>
                    <span  v-if="column === 'case_title'" class="v-card-text-highlight">
                        {{ props["item"]["THREAD_TITLE"] }}
                    </span>
                    <span  v-if="column === 'process_category'" class="v-card-text-highlight">
                        {{ props["item"]["PROCESS_CATEGORY"] }}
                    </span>
                    <span  v-if="column === 'process_name'" class="v-card-text-highlight">
                        {{ props["item"]["PROCESS_NAME"] }}
                    </span>
                    <span  v-if="column === 'due_date'" class="v-card-text-highlight">
                        {{ props["item"]["DUE_DATE"] }}
                    </span>
                    <span  v-if="column === 'delegation_date'" class="v-card-text-highlight">
                        {{ props["item"]["DELEGATION_DATE"] }}
                    </span>                   
                    <span v-if="column === 'task'" span class="v-card-text-light">
                        <TaskCell :data="props.item.TASK" />
                    </span>
                    <span  v-if="column === 'priority'" class="v-card-text-highlight">
                        {{ props["item"]["PRIORITY"] }}
                    </span>
                    <span v-else-if="column === 'send_by'" class="v-card-text-light">
                        <CurrentUserCell :data="props.item.USER_DATA" />
                    </span>
                    <span  v-else class="v-card-text-light">
                        {{ props["item"][column] }}
                    </span>
                </div>
            </template>
        </VueCardView>
        <VueListView
            v-if="typeView === 'LIST'"
            :options="getVueViewOptions()"
            ref="vueListView"
        >
        <div slot="actions" slot-scope="props">
            <b-row>
            <b-col sm="12">
                <div
                    class="v-pm-card-info"
                    :class="props.item.INIT_DATE ? '' : 'pm-main-text-color'"
                    @click="openCaseDetail(props.item)">
                <i class="fas fa-info-circle"></i>
                </div>
            </b-col>
            <b-col sm="12">
                <ellipsis class="ellipsis-container" :data="updateDataEllipsis(props.item)"> </ellipsis>
            </b-col>
            </b-row>
        </div>
            <template v-for="column in cardColumns" :slot="column" slot-scope="props" class="v-card-text">
                <div :class="props.item.INIT_DATE ? '' : 'font-weight-bold'" :key="column">
                    <span class="v-card-text-dark">
                        {{ getCustomHeading(column, props) }} :
                    </span>
                    <span  v-if="column === 'case_number'" class="v-card-text-highlight">
                        {{ props["item"]["CASE_NUMBER"] }}
                    </span>
                    <span  v-if="column === 'case_title'" class="v-card-text-highlight">
                        {{ props["item"]["THREAD_TITLE"] }}
                    </span>
                    <span  v-if="column === 'process_category'" class="v-card-text-highlight">
                        {{ props["item"]["PROCESS_CATEGORY"] }}
                    </span>
                    <span  v-if="column === 'process_name'" class="v-card-text-highlight">
                        {{ props["item"]["PROCESS_NAME"] }}
                    </span>
                    <span  v-if="column === 'due_date'" class="v-card-text-highlight">
                        {{ props["item"]["DUE_DATE"] }}
                    </span>
                    <span  v-if="column === 'delegation_date'" class="v-card-text-highlight">
                        {{ props["item"]["DELEGATION_DATE"] }}
                    </span>                   
                    <span v-if="column === 'task'" span class="v-card-text-light">
                        <TaskCell :data="props.item.TASK" />
                    </span>
                    <span  v-if="column === 'priority'" class="v-card-text-highlight">
                        {{ props["item"]["PRIORITY"] }}
                    </span>
                    <span v-else-if="column === 'send_by'" class="v-card-text-light">
                        <CurrentUserCell :data="props.item.USER_DATA" />
                    </span>
                    <span  v-else class="v-card-text-light">
                        {{ props["item"][column] }}
                    </span>
                </div>
            </template>
        </VueListView>
        <ModalUnpauseCase ref="modal-unpause-case"></ModalUnpauseCase>
        <ModalClaimCase ref="modal-claim-case"></ModalClaimCase>
        <ModalComments
            ref="modal-comments"
            @postNotes="onPostNotes"
        ></ModalComments>
    </div>
</template>

<script>
import HeaderCounter from "../../components/home/HeaderCounter.vue";
import ButtonFleft from "../../components/home/ButtonFleft.vue";
import ModalNewRequest from "../ModalNewRequest.vue";
import ModalUnpauseCase from "../modal/ModalUnpauseCase.vue";
import ModalClaimCase from "../modal/ModalClaimCase.vue";
import TaskCell from "../../components/vuetable/TaskCell.vue";
import CustomFilter from "../../components/search/CustomFilter";
import api from "../../api/index";
import utils from "../../utils/utils";
import MultiviewHeader from "../../components/headers/MultiviewHeader.vue";
import VueCardView from "../../components/dataViews/vueCardView/VueCardView.vue";
import VueListView from "../../components/dataViews/vueListView/VueListView.vue";
import defaultMixins from "./defaultMixins";
import Ellipsis from "../../components/utils/ellipsis.vue";
import ModalPauseCase from "../modal/ModalPauseCase.vue";
import ModalReassignCase from "../modal/ModalReassignCase.vue";
import ModalComments from "../modal/ModalComments.vue"
import { Event } from "vue-tables-2";
import CurrentUserCell from "../../components/vuetable/CurrentUserCell.vue";
import _ from "lodash";

export default {
    name: "CustomCaseList",
    mixins: [defaultMixins],
    components: {
        HeaderCounter,
        ButtonFleft,
        ModalNewRequest,
        ModalUnpauseCase,
        ModalClaimCase,
        TaskCell,
        CustomFilter,
        MultiviewHeader,
        VueCardView,
        VueListView,
        Ellipsis,
        ModalPauseCase,
        ModalReassignCase,
        CurrentUserCell,
        ModalComments
    },
    props: ["defaultOption", "settings", "data"],
    data() {
        let that = this;
        return {
            dataAlert: {
                dismissSecs: 5,
                dismissCountDown: 0,
                message: "",
                variant: "info",
            },
            titleMap: {
                inbox: {
                    icon:"fas fa-check-circle",
                    label: this.$i18n.t('ID_INBOX')
                },
                draft: {
                    icon:"fas fa-edit",
                    label: this.$i18n.t('ID_DRAFT')
                },
                paused: {
                    icon:"far fa-pause-circle",
                    label: this.$i18n.t('ID_PAUSED')
                },
                unassigned: {
                    icon:"fas fa-users",
                    label: this.$i18n.t('ID_UNASSIGNED')
                }
            },
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
            filters: {},
            defaultColumns: [
                "case_number",
                "thread_title",
                "process_name",
                "task",
                "send_by",
                "due_date",
                "delegation_date",
                "priority",
            ],
            settingOptions: [],
            cardColumns: [],
            isFistTime: true,
            columns: ["detail", "actions"],
            headings: {
                detail: this.$i18n.t("ID_DETAIL_CASE"),
                case_number: this.$i18n.t("ID_MYCASE_NUMBER"),
                thread_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
                process_name: this.$i18n.t("ID_PROCESS_NAME"),
                process_category: this.$i18n.t("ID_CATEGORY_PROCESS"),
                task: this.$i18n.t("ID_TASK"),
                send_by: this.$i18n.t("ID_SEND_BY"),
                due_date: this.$i18n.t("ID_DUE_DATE"),
                delegation_date: this.$i18n.t("ID_DELEGATION_DATE"),
                priority: this.$i18n.t("ID_PRIORITY"),
                actions: "",
            },
            tableData: [],
            icon: "fas fa-check-circle",
            options: {
                filterable: false,
                perPageValues: [],
                pagination: { 
                    chunk: 3,
                    nav: 'scroll',
                    edge: true
                },
                texts: {
                    count: this.$i18n.t("ID_SHOWING_FROM_RECORDS_COUNT"),
                    first: "<<",
                    last: ">>",
                    filter: this.$i18n.t("ID_FILTER") + ":",
                    limit: this.$i18n.t("ID_RECORDS") + ":",
                    page: this.$i18n.t("ID_PAGE") + ":",
                    noResults: this.$i18n.t("ID_NO_MATCHING_RECORDS"),
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
                orderBy:
                    this.settings && this.settings.orderBy
                        ? this.settings.orderBy
                        : {},
                requestFunction(data) {
                    return this.$parent.$parent.getCasesForVueTable(data);
                },
                settings: {
                    actions: {
                        class: "fas fa-cog",
                        id: "pm-dr-column-settings2",
                        events: {
                            click() {
                                that.$root.$emit(
                                    "bv::show::popover",
                                    "pm-dr-column-settings2"
                                );
                            },
                        },
                    },
                },
            },
            pmDateFormat: "Y-m-d H:i:s",
            clickCount: 0,
            singleClickTimer: null,
            statusTitle: {
                ON_TIME: this.$i18n.t("ID_IN_PROGRESS"),
                OVERDUE: this.$i18n.t("ID_TASK_OVERDUE"),
                DRAFT: this.$i18n.t("ID_IN_DRAFT"),
                PAUSED: this.$i18n.t("ID_PAUSED"),
                UNASSIGNED: this.$i18n.t("ID_UNASSIGNED"),
            },
            dataSubtitle: {
                subtitle: this.data.pageName,
                icon: this.data.pageIcon,
                color: this.data.color
            },
            itemMap: {
                case_number: "caseNumber",
                task: "taskTitle",
                thread_title: "caseTitle",
                delegation_date: "delegationDate",
                send_by: "bySendBy",
                process_name: "processName",
                process_category: "processCategory"
            },
            customItems:{
                VARCHAR: {
                    group: "radio",
                    type: "VARCHAR",
                    id: "string",
                    title: `${this.$i18n.t("ID_FILTER")}:`,
                    optionLabel: "",
                    tagPrefix: "",
                    detail: "",
                    tagText: "",
                    placeholder: "", 
                    items: [
                        {
                        id: "",
                        value: "",
                        },
                    ],
                    autoShow: true,
                    makeTagText: function (params, data) {
                        return `${this.tagPrefix} ${data[0].value}`;
                    },
                },
                DATETIME: {
                    group: "radio",
                    type: "DATETIME",
                    id: "datetime",
                    title: `${this.$i18n.t('ID_FILTER')}:`,
                    optionLabel: "",
                    detail: "",
                    tagText: "",
                    tagPrefix: "",
                    items:[
                        {
                            id: "",
                            value: ""
                        }
                    ],
                    makeTagText: function (params, data) {
                        let temp = data[0].value.split(",");
                        return `${this.tagPrefix} ${temp[0]} - ${temp[1]} `;
                    }
                }
            },
            filterItems:[],
            availableItems: {
                caseNumber:  {
                    group: "radio",
                    type: "CaseNumber",
                    id: "caseNumber",
                    title: `${this.$i18n.t("ID_FILTER")}: ${this.$i18n.t(
                        "ID_BY_CASE_NUMBER"
                    )}`,
                    optionLabel: this.$i18n.t("ID_BY_CASE_NUMBER"),
                    detail: this.$i18n.t("ID_PLEASE_SET_THE_CASE_NUMBER_TO_BE_SEARCHED"),
                    tagText: "",
                    tagPrefix: this.$i18n.t("ID_SEARCH_BY_CASE_NUMBER"),
                    items: [
                        {
                        id: "filterCases",
                        value: "",
                        },
                    ],
                    autoShow: true,
                    makeTagText: function (params, data) {
                        return `${params.tagPrefix}: ${data[0].value}`;
                    },
                },
                caseTitle: {
                    group: "radio",
                    type: "CaseTitle",
                    id: "caseTitle",
                    title: `${this.$i18n.t("ID_FILTER")}: ${this.$i18n.t(
                        "ID_BY_CASE_THREAD_TITLE"
                    )}`,
                    optionLabel: this.$i18n.t("ID_BY_CASE_THREAD_TITLE"),
                    tagPrefix: this.$i18n.t("ID_SEARCH_BY_CASE_THREAD_TITLE"),
                    detail: "",
                    tagText: "",    
                    items: [
                        {
                        id: "caseTitle",
                        value: "",
                        },
                    ],
                    autoShow: true,
                    makeTagText: function (params, data) {
                        return `${this.tagPrefix} ${data[0].value}`;
                    },
                },
                delegationDate: {
                    group: "radio",
                    type: "DateFilter",
                    id: "delegationDate",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_DELEGATION_DATE')}`,
                    optionLabel: this.$i18n.t('ID_BY_DELEGATION_DATE'),
                    detail: this.$i18n.t('ID_PLEASE_SELECT_THE_DELEGATION_DATE_TO_BE_SEARCHED'),
                    tagText: "",
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_DELEGATION_DATE'),
                    items:[
                        {
                            id: "delegateFrom",
                            value: "",
                            label: this.$i18n.t('ID_FROM_DELEGATION_DATE')
                        },
                        {
                            id: "delegateTo",
                            value: "",
                            label: this.$i18n.t('ID_TO_DELEGATION_DATE')
                        }
                    ],
                    makeTagText: function (params, data) {
                        return  `${params.tagPrefix} ${data[0].value} - ${data[1].value}`;
                    }
                },
                bySendBy: {
                    group: "radio",
                    type: "CurrentUser",
                    id: "bySendBy",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_SEND_BY')}`,
                    optionLabel: this.$i18n.t('ID_BY_SEND_BY'),
                    detail: this.$i18n.t('ID_PLEASE_SELECT_USER_NAME_TO_BE_SEARCHED'),
                    placeholder: this.$i18n.t('ID_USER_NAME'),
                    tagText: "",
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_SEND_BY'),
                    items:[
                        {
                            id: "sendBy",
                            value: "",
                            options: [],
                            placeholder: this.$i18n.t('ID_USER_NAME')
                        }
                    ],
                    makeTagText: function (params, data) {
                        return  `${params.tagPrefix} : ${data[0].label || ''}`;
                    }
                },
                taskTitle: {
                    group: "radio",
                    type: "TaskTitle",
                    id: "taskTitle",
                    title: `${this.$i18n.t("ID_FILTER")}: ${this.$i18n.t(
                        "ID_TASK_NAME"
                    )}`,
                    optionLabel: this.$i18n.t("ID_BY_TASK"),
                    detail: "",
                    tagText: "",
                    tagPrefix: this.$i18n.t("ID_SEARCH_BY_TASK_NAME"),
                    autoShow: true,
                    items: [
                        {
                        id: "task",
                        value: "",
                        options: [],
                        placeholder: this.$i18n.t("ID_TASK_NAME"),
                        },
                    ],
                    makeTagText: function (params, data) {
                        return `${this.tagPrefix}: ${data[0].label || ""}`;
                    },
                },
                processCategory: {
                    group: "checkbox",
                    type: "ProcessCategory",
                    id: "processCategory",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_PROCESS_CATEGORY')}`,
                    optionLabel: this.$i18n.t('ID_BY_PROCESS_CATEGORY'),
                    detail: "",
                    tagText: "",
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_PROCESS_CATEGORY'),
                    autoShow: false,
                    items:[
                        {
                            id: "process",
                            value: "",
                            options: [],
                            placeholder: this.$i18n.t('ID_CATEGORY_PROCESS')
                        }
                    ],
                    makeTagText: function (params, data) {
                        return  `${this.tagPrefix} ${data[0].options && data[0].options.label || ''}`;
                    }
                },
                processName: {
                    group: "checkbox",
                    type: "ProcessName",
                    id: "processName",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_PROCESS_NAME')}`,
                    optionLabel: this.$i18n.t('ID_BY_PROCESS_NAME'),
                    detail: "",
                    tagText: "",
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_PROCESS_NAME'),
                    autoShow: false,
                    items:[
                        {
                            id: "process",
                            value: "",
                            options: [],
                            placeholder: this.$i18n.t('ID_PROCESS_NAME')
                        }
                    ],
                    makeTagText: function (params, data) {
                        return  `${this.tagPrefix} ${data[0].options && data[0].options.label || ''}`;
                    }
                },
                showUserTooltip: true
            }
        };
    },
    created() {
        this.initFilters();
    },
    mounted() {
        let that = this;
        // force to open case
        this.openDefaultCase();
        // define sort event
        Event.$on("vue-tables.custom.sorted", function(data) {
             that.$emit("updateSettings", {
                data: data,
                key: "orderBy",
                page: that.data.pageParent,
                type: "custom",
                id: that.data.customListId
            });
        });
        Event.$on('clearSortEvent', this.clearSort);
    },
    watch: {
        columns: function(val) {
            if (this.isFistTime) {
                 this.isFistTime = false;
            }  else {
                this.$emit("updateSettings", {
                    data: val,
                    key: "columns",
                    page: this.data.pageParent,
                    type: "custom",
                    id: this.data.customListId
                });
            }
        },
    },
    computed: {
        /**
         * Build our ProcessMaker apiClient
         */
        ProcessMaker() {
            return window.ProcessMaker;
        },
    },
    methods: {
        /**
         * Get custom headigns for dynamic lists
         * @param {String} column
         * @param {Object} props
         * @returns {*}
         */
        getCustomHeading(column, props) {   
            if (props["headings"] && props["headings"][column]) {
                return props["headings"][column];
            } else {
                return column;
            }
        },
        /**
         * Initialize filters
         */
        initFilters() {
            let params;
            if (this.defaultOption) {
                params = utils.getAllUrlParams(this.defaultOption);
                if (params && params.openapplicationuid) {
                    this.$emit("onUpdateFilters", [
                        {
                            fieldId: "caseNumber",
                            filterVar: "caseNumber",
                            label: "",
                            options: [],
                            value: params.openapplicationuid,
                            autoShow: false,
                        },
                    ]);
                }
            }
        },
        /**
         * Open a case when the component was mounted
         */
        openDefaultCase() {
            let params;
            if (this.defaultOption) {
                params = utils.getAllUrlParams(this.defaultOption);
                if (params && params.app_uid && params.del_index) {
                    this.openCase({
                        APP_UID: params.app_uid,
                        DEL_INDEX: params.del_index,
                    });
                    this.$emit("cleanDefaultOption");
                } else if (params && params.openapplicationuid) {
                    //force to search in the parallel tasks
                    this.onUpdateFilters({
                        params: [
                            {
                                fieldId: "caseNumber",
                                filterVar: "caseNumber",
                                label: "",
                                options: [],
                                value: params.openapplicationuid,
                                autoShow: false,
                            },
                        ],
                        refresh: true,
                    });
                    this.$emit("cleanDefaultOption");
                }
            }
        },
        /**
         * On row click event handler
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
                if (this.data.pageParent === "paused") {
                    self.showModalUnpauseCase(event.row);
                } else if(this.data.pageParent === "unassigned") {
                    self.claimCase(event.row);
                } else {
                    self.openCase(event.row);
                }
            }
        },
        /**
         * Get cases todo data
         */
        getCasesForVueTable(data) {
            let that = this,
                dt,
                paged,
                limit = data.limit,
                start = data.page === 1 ? 0 : limit * (data.page - 1),
                filters = {},
                sort = "",
                id = this.data.customListId;
            filters = {
                paged: paged,
                limit: limit,
                offset: start,
            };
            if (_.isEmpty(that.filters) && this.data.settings) {
                _.forIn(this.data.settings.filters, function(item, key) {
                    if (filters && item.value) {
                        filters[item.filterVar] = item.value;
                    }
                });
            } else {
                _.forIn(this.filters, function(item, key) {
                    if (filters && item.value) {
                        filters[item.filterVar] = item.value;
                    }
                });
            }
            sort = that.prepareSortString(data);
            if (sort) {
                filters["sort"] = sort;
            }
            return new Promise((resolutionFunc, rejectionFunc) => {
                api.custom[that.data.pageParent]
                    ({
                        id,
                        filters,
                    })
                    .then((response) => {
                        let tmp,
                            columns = [],
                            product,
                            newItems = [];
                        that.filterItems = [];
                        that.headings = {
                            detail: this.$i18n.t("ID_DETAIL_CASE"),
                            actions: "",
                        };
                        response.data.columns.forEach((item) => {
                            if (item.enableFilter) {
                                if (that.availableItems[that.itemMap[item.field]]) {
                                    newItems.push(that.availableItems[that.itemMap[item.field]]);
                                } else {
                                    product = this.filterItemFactory(item);
                                    if (product) {
                                        newItems.push(product);
                                    }
                                }
                            }
                            that.headings[item.field] = item.name;
                            if(item.set){
                                columns.push(item.field);
                            }
                        });
                        that.filterItems = newItems;
                        dt = that.formatDataResponse(response.data.data);
                        
                        that.cardColumns = columns;
                        if (that.isFistTime) {
                            that.filters = that.data.settings && that.data.settings.filters ? that.data.settings.filters : {};
                            that.columns = that.data.settings && that.data.settings.columns ? that.data.settings.columns :  that.getTableColumns(columns);
                            that.settingOptions = that.formatColumnSettings(columns);
                        }
                        resolutionFunc({
                            data: dt,
                            count: response.data.total,
                        });
                    })
                    .catch((e) => {
                        rejectionFunc(e);
                    });
            });
        },
        /**
         * Create a filter item dinamically
         * @param {object} item
         * @returns {object|boolean}
         */
        filterItemFactory(item) {
            let product;
            if (item.type === "DATETIME") {
                product= _.cloneDeep(this.customItems["DATETIME"]);
            } else {
                product = _.cloneDeep(this.customItems["VARCHAR"]);
            }
            product.title += " " + item.name;
            product.id = item.field;    
            product.optionLabel = item.name;
            product.tagPrefix = item.name;
            if (product.items && product.items[0]) {
                product.items[0].id = item.idFilter?item.idFilter : item.field;
            }
            product.placeholder = "";
            return product;
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
         * Format Response API TODO to grid todo and columns
         * @param {object} response
         * @returns {object}
         */
        formatDataResponse(response) {
            let data = [];
            _.forEach(response, (v) => {
                data.push({
                    ...v,
                    ...{
                        CASE_NUMBER: v.APP_NUMBER,
                        THREAD_TITLE: v.DEL_TITLE,
                        PROCESS_NAME: v.PRO_TITLE,
                        PROCESS_CATEGORY: v.CATEGORY,
                        TASK: [
                            {
                                TITLE: v.TAS_TITLE,
                                CODE_COLOR: v.TAS_COLOR,
                                COLOR: v.TAS_COLOR_LABEL,
                                DELAYED_TITLE:
                                    v.TAS_STATUS === "OVERDUE"
                                        ? this.$i18n.t("ID_DELAYED") + ":"
                                        : this.statusTitle[v.TAS_STATUS],
                                DELAYED_MSG:
                                    v.TAS_STATUS === "OVERDUE" ? v.DELAY : "",
                            },
                        ],
                        USER_DATA: this.formatUser(v.SEND_BY_INFO),
                        USERNAME_DISPLAY_FORMAT: utils.userNameDisplayFormat({
                            userName: v.USR_LASTNAME,
                            firstName: v.USR_LASTNAME,
                            lastName: v.USR_LASTNAME,
                            format: window.config.FORMATS.format || null,
                        }),
                        DUE_DATE: v.DEL_TASK_DUE_DATE_LABEL,
                        DELEGATION_DATE: v.DEL_DELEGATE_DATE_LABEL,
                        INIT_DATE: v.DEL_INIT_DATE,
                        PRIORITY: v.DEL_PRIORITY_LABEL,
                        DEL_INDEX: v.DEL_INDEX,
                        APP_UID: v.APP_UID,
                        PRO_UID: v.PRO_UID,
                        TAS_UID: v.TAS_UID,
                    }
                });
            });
            return data;
        },
        /**
         * Set the format to show user's information
         * @return {array} data
         * @returns {object}
         */
        formatUser(data) {
            var dataFormat = [],
                userDataFormat;
            switch (data.key_name) {
                case 'user_tooltip':
                    userDataFormat = utils.userNameDisplayFormat({
                        userName: data.user_tooltip.usr_firstname,
                        firstName: data.user_tooltip.usr_lastname,
                        lastName: data.user_tooltip.usr_username,
                        format: window.config.FORMATS.format || null
                    });
                    dataFormat.push({
                        USERNAME_DISPLAY_FORMAT: userDataFormat,
                        EMAIL: data.user_tooltip.usr_email,
                        POSITION: data.user_tooltip.usr_position,
                        AVATAR: userDataFormat !== "" ? window.config.SYS_SERVER_AJAX +
                            window.config.SYS_URI +
                            `users/users_ViewPhotoGrid?pUID=${data.user_tooltip.usr_id}` : "",
                        UNASSIGNED: userDataFormat !== "" ? true : false,
                        SHOW_TOOLTIP: true
                    });
                    break;
                case 'dummy_task':
                    dataFormat = data.dummy_task.type + ': ' + data.dummy_task.name;
                    break;
                default:
                    dataFormat = "";
                    break;
            }
            return dataFormat;
        },
        /**
         * Open selected cases in the inbox
         *
         * @param {object} item
         */
        openCase(item) {
            this.$emit("onUpdateDataCase", {
                APP_UID: item.APP_UID,
                DEL_INDEX: item.DEL_INDEX,
                PRO_UID: item.PRO_UID,
                TAS_UID: item.TAS_UID,
                INIT_DATE: item.INIT_DATE,
                ACTION: "todo",
            });
            this.$emit("onUpdatePage", "XCase");
        },
        /**
         * Open case detail
         *
         * @param {object} item
         */
        openCaseDetail(item) {
            let that = this;
            api.cases.open(_.extend({ ACTION: "todo" }, item)).then(() => {
                api.cases
                    .cases_open(_.extend({ ACTION: "todo" }, item))
                    .then(() => {
                        that.$emit("onUpdateDataCase", {
                            APP_UID: item.APP_UID,
                            DEL_INDEX: item.DEL_INDEX,
                            PRO_UID: item.PRO_UID,
                            TAS_UID: item.TAS_UID,
                            APP_NUMBER: item.CASE_NUMBER,
                            INIT_DATE: item.INIT_DATE,
                            ACTION: "todo",
                        });
                        that.$emit("onUpdatePage", "case-detail");
                    });
            });
        },
        onRemoveFilter(data) {},
        /**
         * Prepare the data to be updated
         * @param {object} data
         */
        prepareAndUpdate(data) {
            let canUpdate = false,
                newFilters = [];
            data.params.forEach(item =>  {
                const container  = {...item};
                container.autoShow = false;
                if (item.value !== "") {
                    newFilters.push(container);
                    canUpdate = true;
                }
            });
            if (data.params.length == 0) {
            canUpdate = true;
            } 
            if (canUpdate) {
            this.$emit("updateSettings", {
                data: newFilters,
                key: "filters",
                page: this.data.pageParent,
                type: "custom",
                id: this.data.customListId
            });
            }
        },
        onUpdateFilters(data) {
            this.filters = data.params;
            this.prepareAndUpdate(data);
            if (data.refresh) {
                this.$nextTick(() => {
                    if (this.typeView === "GRID") {
                        this.$refs["vueTable"].getData();
                    }
                    if (this.typeView === "CARD") {
                        this.$refs["vueCardView"].getData();
                    }
                    if (this.typeView === "LIST") {
                        this.$refs["vueListView"].getData();
                    }
                });
            }
        },
        /**
         * update view in component
         */
        updateView(newData) {
            let newCriteria = [];
            this.isFistTime = true;
            this.typeView = "GRID";
            // force to update component id
            if (newData) {
                if(newData.customListId) {
                    this.data.customListId = newData.customListId;
                }
                this.dataSubtitle = {
                    subtitle: newData.pageName,
                    icon: newData.pageIcon,
                    color: newData.color
                }
                this.data.settings = newData.settings;
                this.filters = {};
                this.typeView = newData.settings && newData.settings.view ? newData.settings.view.typeView : this.typeView;
            }
            if (this.typeView === "GRID" && this.$refs["vueTable"]) {
                 if (newData && newData.settings && newData.settings.orderBy) {
                    this.$refs["vueTable"].setOrder(newData.settings.orderBy.column, newData.settings.orderBy.ascending);
                } else {
                    this.$refs["vueTable"].setOrder(false);
                 }
            }
            if (this.typeView === "CARD" && this.$refs["vueCardView"]) {
                this.$refs["vueCardView"].getData();
            }
            if (this.typeView === "LIST" && this.$refs["vueCardView"]) {
                this.$refs["vueListView"].getData();
            }
        },
        /**
         * Show modal to pause a case
         * @param {objec} data
         */
        showModalPause(data) {
            this.$refs["modal-pause-case"].data = data;
            this.$refs["modal-pause-case"].show();
        },
        /**
         * Show modal to reassign a case
         * @param {objec} data
         */
        showModalReassign(data) {
            this.$refs["modal-reassign-case"].data = data;
            this.$refs["modal-reassign-case"].show();
        },
        /**
         * Show options in the ellipsis
         * @param {objec} data
         */
        updateDataEllipsis(data) {
            return this.ellipsisItemFactory(data, this.data.pageParent);
        },
        /**
         * Show the alert message
         * @param {string} message - message to be displayen in the body
         * @param {string} type - alert type
         */
        showAlert(message, type) {
            this.dataAlert.message = message;
            this.dataAlert.variant = type || "info";
            this.dataAlert.dismissCountDown = this.dataAlert.dismissSecs;
        },
        /**
         * Updates the alert dismiss value to update
         * dismissCountDown and decrease
         * @param {mumber}
         */
        countDownChanged(dismissCountDown) {
            this.dataAlert.dismissCountDown = dismissCountDown;
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
        showModalUnpauseCase(item) {
          this.$refs["modal-unpause-case"].data = item;
          this.$refs["modal-unpause-case"].show();
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
         * Json factory for ellipsis control item
         * @param {object} data
         * @param {object} page
         * @returns {object}
         */
        ellipsisItemFactory (data, page) {
          let that = this;
          let dataEllipsisMap = {
            inbox: {
              APP_UID: data.APP_UID,
              buttons: {
                  open: {
                      name: "open",
                      icon: "far fa-edit",
                      fn: function() {
                          that.openCase(data);
                      },
                  },
                  note: {
                      name: "case note",
                      icon: "far fa-comments",
                      fn: function() {
                        that.openComments(data);
                      },
                  },
                  reassign: {
                      name: "reassign case",
                      icon: "fas fa-undo",
                      fn: function() {
                          that.showModalReassign(data);
                      },
                  },
                  pause: {
                      name: "pause case",
                      icon: "far fa-pause-circle",
                      fn: function() {
                          that.showModalPause(data);
                      },
                  },
              },
            },
            draft: {
              APP_UID: data.APP_UID,
              buttons: {
                open: {
                  name: "open",
                  icon: "far fa-edit",
                  fn: function() {
                    that.openCase(data);
                  }
                },
                note: {
                  name: "case note",
                  icon: "far fa-comments",
                  fn: function() {
                    that.openComments(data);
                  }
                },
              }
            },
            paused: {
              APP_UID: data.APP_UID,  
              buttons: {
                note: {
                  name: "case note",
                  icon: "far fa-comments",
                  fn: function() {
                    that.openComments(data);
                  }
                },
                play: {
                  name: "play case",
                  icon: "far fa-play-circle",
                  fn: function() {
                    that.showModalUnpauseCase(data);
                  }
                },
                reassign: {
                  name: "reassign case",
                  icon: "fas fa-undo",
                  fn: function() {
                    that.showModalReassign(data);
                  }
                }
              }
            },
            unassigned: {
              APP_UID: data.APP_UID,  
              buttons: {
                note: {
                  name: "case note",
                  icon: "far fa-comments",
                  fn: function() {
                    that.openComments(data);
                  }
                },
                claim: {
                  name: "claim case",
                  icon: "fas fa-briefcase",
                  fn: function() {
                    that.claimCase(data);
                  }
                }
              }
            }
          };
          return dataEllipsisMap[page];
        },
        /**
         * Reset the sort in the table
         */
        clearSort() {
            if (this.$refs['vueTable']) {
                this.$refs['vueTable'].setOrder(false);
                this.$emit("updateSettings", {
                    data: [],
                    key: "orderBy",
                    page: this.data.pageParent,
                    type: "custom",
                    id: this.data.customListId
                });
            }
        },
    },
};
</script>
<style>
.VueTables__row {
  height: 75px;
}
.v-container-todo {
    padding-top: 20px;
    padding-bottom: 20px;
    padding-left: 50px;
    padding-right: 50px;
}

.v-card-text-dark {
    color: #343944;
    display: inline-block;
}

.v-card-text-highlight {
    color: #313541;
    display: inline-block;
}

.v-card-text-light {
    color: #313541;
    display: inline-block;
}

.ellipsis-container {
    margin-top: 5em;
    float: right;
}

.v-pm-card-info {
    float: right;
}

.vp-inbox-list-actions {
    top: 25%;
    position: absolute;
}
</style>
