<template>
    <b-modal
        ref="modal-preview"
        scrollable
        size="xl"
    >
        <template v-slot:modal-title></template>
        <b-container fluid>
            <v-server-table
                :data="tableData"
                :columns="columns"
                :options="options"
                ref="table-preview"
                name="preview"
            >
                <div slot="detail">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div slot="case_number" slot-scope="props">
                    {{ props.row.CASE_NUMBER }}
                </div>
                <div slot="thread_title" slot-scope="props">
                    {{ props.row.THREAD_TITLE }}
                </div>
                <div slot="process_name" slot-scope="props">
                    {{ props.row.PROCESS_NAME }}
                </div>
                <div slot="task" slot-scope="props">
                    <TaskCell :data="props.row.TASK" />
                </div>
                <div slot="send_by" slot-scope="props">
                    <CurrentUserCell :data="props.row.USER_DATA" />
                </div>
                <div slot="current_user" slot-scope="props">
                    {{ props.row.USERNAME_DISPLAY_FORMAT }}
                </div>
                <div slot="due_date" slot-scope="props">
                    {{ props.row.DUE_DATE }}
                </div>
                <div slot="delegation_date" slot-scope="props">
                    {{ props.row.DELEGATION_DATE }}
                </div>
                <div slot="priority" slot-scope="props">
                    {{ props.row.PRIORITY }}
                </div>
                <div slot="actions">
                    <i class="fas fa-ellipsis-v"></i>
                </div>
            </v-server-table>
        </b-container>
        <template #modal-footer>
            <b-button
                variant="danger"
                data-dismiss="modal"
                @click="cancel"
            >
                {{ $t("ID_CANCEL") }}
            </b-button>
        </template>
    </b-modal>
</template>
<script>
import api from "../../api/index";
import utils from "../../utils/utils";
import TaskCell from "../../components/vuetable/TaskCell.vue";
import CurrentUserCell from "../../components/vuetable/CurrentUserCell.vue"

export default {
    name: "ModalPreview",
    props: [],
    components: {
        TaskCell,
        CurrentUserCell,
    },
    data() {
        return {
            type: null,
            columns: null,
            tableData: [],
            options: {
                filterable: false,
                pagination: {
                  show: false
                },
                headings: {
                    detail: this.$i18n.t("ID_DETAIL_CASE"),
                    case_number: this.$i18n.t("ID_MYCASE_NUMBER"),
                    thread_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
                    process_name: this.$i18n.t("ID_PROCESS_NAME"),
                    task: this.$i18n.t("ID_TASK"),
                    send_by: this.$i18n.t("ID_SEND_BY"),
                    due_date: this.$i18n.t("ID_DUE_DATE"),
                    delegation_date: this.$i18n.t("ID_DELEGATION_DATE"),
                    priority: this.$i18n.t("ID_PRIORITY"),
                    actions: "",
                },
                requestFunction(data) {
                    return this.$parent.$parent.$parent.$parent.getCasesForPreview(data)
                },
            },
            customCaseId: '',
            statusTitle: {
                ON_TIME: this.$i18n.t("ID_IN_PROGRESS"),
                OVERDUE: this.$i18n.t("ID_TASK_OVERDUE"),
                DRAFT: this.$i18n.t("ID_IN_DRAFT"),
                PAUSED: this.$i18n.t("ID_PAUSED"),
                UNASSIGNED: this.$i18n.t("ID_UNASSIGNED"),
            }
        }
    },
    mounted() {
        
    },
    methods: {
        show() {
            this.$refs["modal-preview"].show();
        },
        cancel() {
            this.$refs["modal-preview"].hide();
        },
        /**
         * Get cases data
         */
        getCasesForPreview(data) {
            let that = this,
                dt,
                limit = data.limit,
                start = data.page === 1 ? 0 : limit * (data.page - 1),
                params = {};
            params = {
                filters: {
                    limit: limit,
                    offset: start,
                },
            }
            if (this.customCaseId !== '') {
                params['id'] = this.customCaseId;
            }
            return new Promise((resolutionFunc, rejectionFunc) => {
                switch (that.type) {
                    case 'inbox':
                        api.custom
                        .inbox(params)
                        .then((response) => {
                            dt = that.formatDataResponse(response.data.data);
                            resolutionFunc({
                                data: dt,
                                count: response.data.total,
                            });
                        })
                        .catch((e) => {
                            rejectionFunc(e);
                        });
                        break;
                    case 'draft':
                        api.custom
                        .draft(params)
                        .then((response) => {
                            dt = that.formatDataResponse(response.data.data);
                            resolutionFunc({
                                data: dt,
                                count: response.data.total,
                            });
                        })
                        .catch((e) => {
                            rejectionFunc(e);
                        });
                        break;
                    case 'paused':
                        api.custom
                        .paused(params)
                        .then((response) => {
                            dt = that.formatDataResponse(response.data.data);
                            resolutionFunc({
                                data: dt,
                                count: response.data.total,
                            });
                        })
                        .catch((e) => {
                            rejectionFunc(e);
                        });
                        break;
                    case 'unassigned':
                        api.custom
                        .unassigned(params)
                        .then((response) => {
                            dt = that.formatDataResponse(response.data.data);
                            resolutionFunc({
                                data: dt,
                                count: response.data.total,
                            });
                        })
                        .catch((e) => {
                            rejectionFunc(e);
                        });
                        break;
                }
            });
        },
        /**
         * Format Response API custom case list to grid todo and columns
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
         * @return {array} dataFormat
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
        }
    }
}
</script>
<style>
.VueTables__limit-field {
    display: none;
}
.table-responsive {
  margin-top: -1rem;
}
</style>