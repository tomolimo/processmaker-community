<template>
  <div id="v-unassigned" ref="v-unassigned" class="v-container-unassigned">
    <button-fleft :data="newCase"></button-fleft>
    <modal-new-request ref="newRequest"></modal-new-request>
    <CasesFilter
      :filters="filters"
      :title="$t('ID_UNASSIGNED')"
      :icon="icon"
      @onRemoveFilter="onRemoveFilter"
      @onUpdateFilters="onUpdateFilters"
    />
     <b-alert
        :show="dataAlert.dismissCountDown"
        dismissible
        :variant="dataAlert.variant"
        @dismissed="dataAlert.dismissCountDown = 0"
        @dismiss-count-down="countDownChanged"
    >
        {{ dataAlert.message }}
    </b-alert>
    <multiview-header
      :data="dataMultiviewHeader"
      :dataSubtitle="dataSubtitle"
    />
    <settings-popover :options="formatColumnSettings(options.headings)" target="pm-dr-column-settings" @onUpdateColumnSettings="onUpdateColumnSettings" :key="random+1" :selected="formatColumnSelected(columns)"/>
    <v-server-table
      v-if="typeView === 'GRID'"
      :columns="columns"
      :options="options"
      ref="vueTable"
      @row-click="onRowClick"
      :key="random"
      name="unassigned"
    >
      <div slot="detail" slot-scope="props">
        <div
          class="btn-default"
          :class="props.row.INIT_DATE ? '' : 'pm-main-text-color '"
          @click="openCaseDetail(props.row)">
          <i class="fas fa-info-circle"></i>
        </div>
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="case_number"
        slot-scope="props"
      >
        {{ props.row.CASE_NUMBER }}
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="thread_title"
        slot-scope="props"
      >
        {{ props.row.THREAD_TITLE }}
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="process_category"
        slot-scope="props"
      >
        {{ props.row.PROCESS_CATEGORY }}
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="process_name"
        slot-scope="props"
      >
        {{ props.row.PROCESS_NAME }}
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="task"
        slot-scope="props"
      >
        <TaskCell :data="props.row.TASK" />
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="send_by"
        slot-scope="props"
      >
          <CurrentUserCell :data="props.row.USER_DATA" />
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="due_date"
        slot-scope="props"
      >
        {{ props.row.DUE_DATE }}
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="delegation_date"
        slot-scope="props"
      >
        {{ props.row.DELEGATION_DATE }}
      </div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="priority"
        slot-scope="props"
      >
        {{ props.row.PRIORITY }}</div>
      <div
        :class="props.row.INIT_DATE ? '' : 'font-weight-bold'"
        slot="actions"
        slot-scope="props"
      >
        <ellipsis :data="updateDataEllipsis(props.row)"> </ellipsis>
      </div>
    </v-server-table>
    <VueCardView
      v-if="typeView === 'CARD'"
      :options="optionsVueList"
      ref="vueCardView"
    >
      <div slot="actions" slot-scope="props">
        <b-row>
          <b-col sm="12">
            <div
              class="v-pm-card-info"
              :class="props.item.INIT_DATE ? '' : 'pm-main-text-color'"
              @click="openCaseDetail(props.item)"
            >
              <i class="fas fa-info-circle"></i>
            </div>
          </b-col>
          <b-col sm="12">
            <ellipsis class="ellipsis-container" :data="updateDataEllipsis(props.item)"> </ellipsis>
          </b-col>
        </b-row>
      </div>
      <div slot="case_number" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-highlight"
        >
          {{ props["headings"][props.column] }} : {{ props["item"]["CASE_NUMBER"] }}</span
        >
      </div>
      <div slot="thread_title" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["THREAD_TITLE"] }}
        </span>
      </div>
      <div slot="process_category" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["PROCESS_CATEGORY"] }}
        </span>
      </div>
      <div slot="process_name" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["PROCESS_NAME"] }}
        </span>
      </div>
      <div slot="due_date" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["DUE_DATE"] }}
        </span>
      </div>
      <div slot="delegation_date" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["DELEGATION_DATE"] }}
        </span>
      </div>
      <div slot="priority" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["PRIORITY"] }}
        </span>
      </div>
      <div slot="task" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          <TaskCell :data="props.item.TASK" />
        </span>
      </div>
      <div slot="send_by" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          <CurrentUserCell :data="props.item.USER_DATA" />
        </span>
      </div>
    </VueCardView>
    <VueListView
      v-if="typeView === 'LIST'"
      :options="optionsVueList"
      ref="vueListView"
    >
      <div slot="actions" slot-scope="props">
        <b-row>
          <b-col sm="12">
            <div
              class="v-pm-card-info"
              :class="props.item.INIT_DATE ? '' : 'pm-main-text-color'"
              @click="openCaseDetail(props.item)"
            >
              <i class="fas fa-info-circle"></i>
            </div>
          </b-col>
          <b-col sm="12">
            <ellipsis class="ellipsis-container" :data="updateDataEllipsis(props.item)"> </ellipsis>
          </b-col>
        </b-row>
      </div>
      <div slot="case_number" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-highlight"
        >
          {{ props["headings"][props.column] }} : {{ props["item"]["CASE_NUMBER"] }}</span
        >
      </div>
      <div slot="thread_title" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["THREAD_TITLE"] }}
        </span>
      </div>
      <div slot="process_category" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["PROCESS_CATEGORY"] }}
        </span>
      </div>
      <div slot="process_name" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["PROCESS_NAME"] }}
        </span>
      </div>
      <div slot="due_date" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["DUE_DATE"] }}
        </span>
      </div>
      <div slot="delegation_date" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["DELEGATION_DATE"] }}
        </span>
      </div>
      <div slot="priority" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          {{ props["item"]["PRIORITY"] }}
        </span>
      </div>
      <div slot="task" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light"
        >
          <TaskCell :data="props.item.TASK" />
        </span>
      </div>
      <div slot="send_by" slot-scope="props" class="v-card-text">
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-dark"
        >
          {{ props["headings"][props.column] }} :</span
        >
        <span
          :class="props.item.INIT_DATE ? '' : 'font-weight-bold'"
          class="v-card-text-light">
          <CurrentUserCell :data="props.item.USER_DATA" />
        </span>
      </div>
    </VueListView>
    <ModalComments
        ref="modal-comments"
        @postNotes="onPostNotes"
    ></ModalComments>
    <ModalClaimCase ref="modal-claim-case"></ModalClaimCase>
  </div>
</template>

<script>
import HeaderCounter from "../../components/home/HeaderCounter.vue";
import ButtonFleft from "../../components/home/ButtonFleft.vue";
import ModalNewRequest from "../ModalNewRequest.vue";
import TaskCell from "../../components/vuetable/TaskCell.vue";
import CasesFilter from "../../components/search/CasesFilter";
import ModalClaimCase from "../modal/ModalClaimCase.vue";
import api from "../../api/index";
import utils from "../../utils/utils";
import Ellipsis from '../../components/utils/ellipsis.vue';
import MultiviewHeader from "../../components/headers/MultiviewHeader.vue";
import VueCardView from "../../components/dataViews/vueCardView/VueCardView.vue";
import VueListView from "../../components/dataViews/vueListView/VueListView.vue";
import defaultMixins from "./defaultMixins";
import ModalComments from "../modal/ModalComments.vue";
import { Event } from 'vue-tables-2';
import CurrentUserCell from "../../components/vuetable/CurrentUserCell.vue";

export default {
  name: "Unassigned",
  mixins: [defaultMixins],
  components: {
    HeaderCounter,
    ButtonFleft,
    ModalNewRequest,
    TaskCell,
    ModalClaimCase,
    CasesFilter,
    Ellipsis,
    MultiviewHeader,
    VueCardView,
    VueListView,
    CurrentUserCell,
    ModalComments
  },
  props: ["defaultOption", "settings"],
  data() {
    let that = this;
    return {
      dataAlert: {
          dismissSecs: 5,
          dismissCountDown: 0,
          message: "",
          variant: "info",
      },
      columMap: {
          case_number: "APP_NUMBER",
          thread_title: "DEL_TITLE",
          process_name: "PRO_TITLE"
      },
      newCase: {
        title: this.$i18n.t("ID_NEW_CASE"),
        class: "btn-success",
        onClick: () => {
          this.$refs["newRequest"].show();
        },
      },
      filters:
          this.settings && this.settings.filters
              ? this.settings.filters
              : {},
      columns:
          this.settings && this.settings.columns
              ? this.settings.columns
              : [
                  "detail",
                  "case_number",
                  "thread_title",
                  "process_name",
                  "task",
                  "send_by",
                  "due_date",
                  "delegation_date",
                  "priority",
                  "actions"
                ],
      icon:"fas fa-users",
      options: {
        filterable: false,
        perPageValues: [],
        pagination: { 
            chunk: 3,
            nav: 'scroll',
            edge: true
        },
        headings: {
          detail: this.$i18n.t("ID_DETAIL_CASE"),
          case_number: this.$i18n.t("ID_MYCASE_NUMBER"),
          thread_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
          process_category: this.$i18n.t("ID_CATEGORY_PROCESS"),
          process_name: this.$i18n.t("ID_PROCESS_NAME"),
          task: this.$i18n.t("ID_TASK"),
          send_by: this.$i18n.t("ID_SEND_BY"),
          due_date: this.$i18n.t("ID_DUE_DATE"),
          delegation_date: this.$i18n.t("ID_DELEGATION_DATE"),
          priority: this.$i18n.t("ID_PRIORITY"),
          actions: ""
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
          only: function (row) {
            return true;
          },
          selectAllMode: "page",
          programmatic: false,
        },
        sortable: ['case_number'],
        orderBy: this.settings && this.settings.orderBy ?  this.settings.orderBy: {},
        requestFunction(data) {
          return this.$parent.$parent.getCasesForVueTable(data);
        },
        settings: {
          "actions":{
            class: "fas fa-cog",
            id:"pm-dr-column-settings",
            events:{ 
                click(){
                    that.$root.$emit('bv::show::popover', 'pm-dr-column-settings')
                }
            }
          }
        },
      },
      pmDateFormat: "Y-m-d H:i:s",
      clickCount: 0,
      singleClickTimer: null,
      statusTitle: {
          "ON_TIME": this.$i18n.t("ID_IN_PROGRESS"),
          "OVERDUE": this.$i18n.t("ID_TASK_OVERDUE"),
          "DRAFT": this.$i18n.t("ID_IN_DRAFT"),
          "PAUSED": this.$i18n.t("ID_PAUSED"),
          "UNASSIGNED": this.$i18n.t("ID_UNASSIGNED")
      },
      dataSubtitle: null
    };
  },
  mounted() {
    let that = this;
    this.initFilters();
    // define sort event
    Event.$on('vue-tables.unassigned.sorted', function (data) {
      that.$emit("updateSettings", {
        data: data,
        key: "orderBy",
        page: "unassigned",
        id: this.id
      });
    });
    Event.$on('clearSortEvent', this.clearSort);
  },
   watch: {
    columns: function (val) {
      this.$emit("updateSettings", {
        data: val,
        key: "columns",
        page: "unassigned",
        type: "normal",
        id: this.id
      });
    }
  },
  computed: {
    /**
     * Build our ProcessMaker apiClient
     */
    ProcessMaker() {
      return window.ProcessMaker;
    }
  },
  updated() {},
  beforeCreate() {},
  methods: {
    /**
     * Initialize filters
     * updates the filters if there is an appUid parameter
     */
    initFilters() {
       let params,
       filter = {refresh: true};
        if(this.defaultOption) {
            params = utils.getAllUrlParams(this.defaultOption);
            if (params && params.openapplicationuid) {
                filter = {
                    params: [
                        {
                            fieldId: "caseNumber",
                            filterVar: "caseNumber",
                            label: "",
                            options:[],
                            value: params.openapplicationuid,
                            autoShow: false
                        }
                    ],
                    refresh: true
                };
                this.$emit("cleanDefaultOption");
                api.cases.pendingtask({APP_NUMBER:params.openapplicationuid}).then((response) => {
                    if (response.data && response.data.length == 1 && response.data[0] && response.data[0]['USR_ID'] == 0) {
                        this.claimCase(response.data[0]);
                    }
                });
                this.onUpdateFilters(filter);
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
            self.claimCase(event.row);
        }
    },
    /**
     * Get cases unassigned data
     */
    getCasesForVueTable(data) {
      let that = this,
        dt,
        paged,
        limit = data.limit,
        start = data.page === 1 ? 0 : limit * (data.page - 1),
        filters = {},
        sort = "";
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
          .unassigned(filters)
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
      });
    },
    /**
     * Prepare sort string to be sended in the service
     * @param {object} data
     * @returns {string}
     */
    prepareSortString(data){
        let sort = "";
        if (data.orderBy && this.columMap[data.orderBy]) {
            sort  =  `${this.columMap[data.orderBy]},${data.ascending === 1 ? "ASC": "DESC"}`;
        }
        return sort;
    },
    /**
     * Format Response API TODO to grid todo and columns
     */
    formatDataResponse(response) {
      let data = [];
      _.forEach(response, (v) => {
        data.push({
          CASE_NUMBER: v.APP_NUMBER,
          THREAD_TITLE: v.DEL_TITLE,
          PROCESS_NAME: v.PRO_TITLE,
          PROCESS_CATEGORY: v.CATEGORY,
          TASK: [{
            TITLE: v.TAS_TITLE,
            CODE_COLOR: v.TAS_COLOR,
            COLOR: v.TAS_COLOR_LABEL,
            DELAYED_TITLE: v.TAS_STATUS === "OVERDUE" ?
              this.$i18n.t("ID_DELAYED") + ":" : this.statusTitle[v.TAS_STATUS],
            DELAYED_MSG: v.TAS_STATUS === "OVERDUE" ? v.DELAY : ""
          }],
          USER_DATA: this.formatUser(v.SEND_BY_INFO),
          DUE_DATE: v.DEL_TASK_DUE_DATE_LABEL,
          DELEGATION_DATE: v.DEL_DELEGATE_DATE_LABEL,
          INIT_DATE: v.DEL_INIT_DATE,
          PRIORITY: v.DEL_PRIORITY_LABEL,
          PRO_UID: v.PRO_UID,
          TAS_UID: v.TAS_UID,
          DEL_INDEX: v.DEL_INDEX,
          APP_UID: v.APP_UID,
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
        ACTION: "todo"
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
        api.cases.cases_open(_.extend({ ACTION: "todo" }, item)).then(() => {
          that.$emit("onUpdateDataCase", {
            APP_UID: item.APP_UID,
            DEL_INDEX: item.DEL_INDEX,
            PRO_UID: item.PRO_UID,
            TAS_UID: item.TAS_UID,
            INIT_DATE: item.INIT_DATE,
            APP_NUMBER: item.CASE_NUMBER,
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
            page: "unassigned",
            type: "normal",
            id: this.id
          });
        }
    },
    /**
     * Update event handler
     * @param {object} data
     */
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
    updateView(){
      if (this.typeView === "GRID") {
        this.$refs["vueTable"].getData();
      }
      if (this.typeView === "CARD") {
        this.$refs["vueCardView"].getData();
      }
      if (this.typeView === "LIST") {
        this.$refs["vueListView"].getData();
      }
    },
    /**
     * Show options in the ellipsis 
     * @param {object} data
     */
    updateDataEllipsis(data) {
      let that = this;
      return {
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
            this.$emit("updateSettings", {
                data: [],
                key: "orderBy",
                page: "unassigned",
                type: "normal",
                id: this.id
            });
        }
    }
  },
};
</script>
<style>
.VueTables__row {
  height: 75px;
}
.v-container-unassigned {
  padding-top: 20px;
  padding-bottom: 20px;
  padding-left: 50px;
  padding-right: 50px;
}
.ellipsis-container {
  margin-top: 5em;
  float: right;
}

.v-pm-card-info{
  float: right;
}
</style>