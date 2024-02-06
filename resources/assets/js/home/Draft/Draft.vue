<template>
  <div id="v-draft" ref="v-draft" class="v-container-draft">
    <button-fleft :data="newCase"></button-fleft>
    <modal-new-request ref="newRequest"></modal-new-request>
    <b-alert
        :show="dataAlert.dismissCountDown"
        dismissible
        :variant="dataAlert.variant"
        @dismissed="dataAlert.dismissCountDown = 0"
        @dismiss-count-down="countDownChanged"
    >
        {{ dataAlert.message }}
    </b-alert>
    <CasesFilter
      :filters="filters"
      :title="$t('ID_DRAFT')"
      :icon="icon"
      :hiddenItems="hiddenItems"
      @onRemoveFilter="onRemoveFilter"
      @onUpdateFilters="onUpdateFilters"
    />
    <multiview-header
      :data="dataMultiviewHeader"
      :dataSubtitle="dataSubtitle"
    />
    <settings-popover
      :options="formatColumnSettings(options.headings)"
      target="pm-dr-column-settings"
      @onUpdateColumnSettings="onUpdateColumnSettings"
      :key="random+1"
      :selected="formatColumnSelected(columns)"
    />
    <v-server-table
      v-if="typeView === 'GRID'"
      :data="tableData"
      :columns="columns"
      :options="options"
      ref="vueTable"
      @row-click="onRowClick"
      :key="random"
      name="draft"
    >
      <div slot="detail" slot-scope="props">
        <div
          class="btn-default"
          :class="props.row.INIT_DATE ? '' : 'pm-main-text-color '"
          @click="openCaseDetail(props.row)"
        >
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
        slot="priority"
        slot-scope="props"
      >
        {{ props.row.PRIORITY }}
      </div>
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
      :options="optionsVueView"
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

    </VueCardView>
    <VueListView
      v-if="typeView === 'LIST'"
      :options="optionsVueView"
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
    </VueListView>
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
import ModalComments from "../modal/ModalComments.vue";
import CasesFilter from "../../components/search/CasesFilter";
import TaskCell from "../../components/vuetable/TaskCell.vue";
import api from "../../api/index";
import utils from "../../utils/utils";
import MultiviewHeader from "../../components/headers/MultiviewHeader.vue";
import VueCardView from "../../components/dataViews/vueCardView/VueCardView.vue";
import VueListView from "../../components/dataViews/vueListView/VueListView.vue";
import defaultMixins from "./defaultMixins";
import Ellipsis from '../../components/utils/ellipsis.vue';
import { Event } from 'vue-tables-2';

export default {
  name: "Draft",
  mixins: [defaultMixins],
  components: {
    HeaderCounter,
    ButtonFleft,
    ModalNewRequest,
    TaskCell,
    CasesFilter,
    Ellipsis,
    MultiviewHeader,
    VueCardView,
    VueListView,
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
                  "priority",
                  "actions"
                ],
      tableData: [],
      icon:"fas fa-edit",
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
          priority: this.$i18n.t("ID_PRIORITY"),
          actions: ""
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
        texts: {
            count:this.$i18n.t("ID_SHOWING_FROM_RECORDS_COUNT"),
            first: "<<",
            last: ">>",
            filter: this.$i18n.t("ID_FILTER") + ":",
            limit: this.$i18n.t("ID_RECORDS") + ":",
            page: this.$i18n.t("ID_PAGE") + ":",
            noResults: this.$i18n.t("ID_NO_MATCHING_RECORDS")
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
      dataSubtitle: null,
      hiddenItems: ['bySendBy']
    };
  },
  created() {
    this.initFilters();
  },
  mounted() {
    let that = this;
    this.openDefaultCase();
     // define sort event
    Event.$on('vue-tables.draft.sorted', function (data) {
      that.$emit("updateSettings", {
        data: data,
        key: "orderBy",
        page: "draft",
        type: "normal",
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
        page: "draft",
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
     */
    initFilters() {
       let params;
        if(this.defaultOption) {
            params = utils.getAllUrlParams(this.defaultOption);
              if (params && params.openapplicationuid) {
                this.$emit("onUpdateFilters",[
                    {
                        fieldId: "caseNumber",
                        filterVar: "caseNumber",
                        label: "",
                        options:[],
                        value: params.openapplicationuid,
                        autoShow: false
                    }
                ]);
              }
        }
    },
    /**
     * Open a case when the component was mounted
     */
    openDefaultCase() {
        let params;
        if(this.defaultOption) {
            params = utils.getAllUrlParams(this.defaultOption);
            if (params && params.app_uid && params.del_index) {
                this.openCase({
                    APP_UID: params.app_uid,
                    DEL_INDEX: params.del_index
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
                            options:[],
                            value: params.openapplicationuid,
                            autoShow: false
                        }
                    ],
                    refresh: true
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
            self.openCase(event.row);
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
          .draft(filters)
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
          PRIORITY: v.DEL_PRIORITY_LABEL,
          PRO_UID: v.PRO_UID,
          TAS_UID: v.TAS_UID,
          DEL_INDEX: v.DEL_INDEX,
          APP_UID: v.APP_UID,
          INIT_DATE: v.DEL_INIT_DATE
        });
      });
      return data;
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
        ACTION: "draft"
      });
      this.$emit("onUpdatePage", "XCase");
    },
    /**
     * Open case detail from draft
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
            APP_NUMBER: item.CASE_NUMBER,
            INIT_DATE: item.INIT_DATE,
            ACTION: "draft"
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
            page: "draft",
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
                page: "draft",
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
.v-container-draft {
  padding-top: 20px;
  padding-bottom: 20px;
  padding-left: 50px;
  padding-right: 50px;
}
.ellipsis-container {
  margin-top: 5em;
  float: right;
}
.v-pm-card-info {
    float: right;
}
</style>