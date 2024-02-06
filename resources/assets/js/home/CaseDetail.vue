<template>
  <div id="case-detail" ref="case-detail" class="v-container-case-detail">
    <div>
      <b-alert
        :show="dataAlert.dismissCountDown"
        dismissible
        :variant="dataAlert.variant"
        @dismissed="dataAlert.dismissCountDown = 0"
        @dismiss-count-down="countDownChanged"
      >
        {{ dataAlert.message }}
      </b-alert>
      <p class="d-flex">
        <div class="mr-2 d-flex justify-content-between">
          <div class="d-flex">
            <h5 class="v-search-title">{{ $t("ID_CASE_DETAILS") }}</h5>
            <div class="pm-in-text-icon">
              <i class="fas fa-info-circle"></i>
            </div>
          </div>
          <button-fleft :data="newCase"></button-fleft>
        </div>
        <b-icon icon="arrow-left"></b-icon>
        <button type="button" class="btn btn-link" @click="$emit('onLastPage')">
          {{ $t("ID_BACK") }}
        </button>
      </p>
      <modal-new-request ref="newRequest"></modal-new-request>
    </div>
    <div class="row">
      <div class="col-sm-9">
        <div id="pending-task" ref="pending-task">
          <v-server-table
            :data="tableData"
            :columns="columns"
            :options="options"
            v-show="showTable"
            @row-click="onRowClick"
            ref="vueTable"
          >
            <div slot="task" slot-scope="props">
              <TaskCell :data="props.row.TASK" />
            </div>
            <div slot="thread_title" slot-scope="props">
                {{ props.row.THREAD_TITLE }}
            </div>
            <div slot="current_user" slot-scope="props">
                <CurrentUserCell :data="props.row.USER_DATA" />
            </div>
            <div slot="status" slot-scope="props">
              {{ props.row.STATUS }}
            </div>
            <div slot="due_date" slot-scope="props">
              {{ props.row.DUE_DATE }}
            </div>
            <div slot="actions" slot-scope="props">
              <b-button
                v-if="props.row.STATUS === 'OPEN' && (!supervisor || !flagSupervising)"
                @click="onClick(props)"
                variant="outline-success"
                >{{ $t("ID_CONTINUE") }}</b-button
              >
              <b-button
                v-if="props.row.STATUS === 'PAUSED'"
                @click="onClickUnpause(props)"
                variant="outline-primary"
                >{{ $t("ID_UNPAUSE") }}</b-button
              >
              <b-button
                v-if="props.row.USR_UID === '' && props.row.STATUS !== 'CLOSED' && supervisor && flagSupervising"
                @click="onClickAssign(props.row)"
                variant="primary"
                >
                <i class="fa fa-users"></i>
                {{ $t("ID_ASSIGN") }}
              </b-button>
              <b-button
                v-if="props.row.USR_UID !== '' && props.row.STATUS !== 'CLOSED' && supervisor && flagSupervising"
                @click="onClickReassign(props.row)"
                variant="success"
                :disabled="props.row.TASK[0].TAS_TYPE === 'MULTIPLE_INSTANCE_VALUE_BASED' || props.row.TASK[0].TAS_TYPE === 'MULTIPLE_INSTANCE'"
                >
                <i class="fas fa-exchange-alt"></i>
                {{ $t("ID_REASSIGN") }}
              </b-button>
            </div>
          </v-server-table>
        </div>
        <div class="text-right">
          <b-button
            v-if="supervisor && flagSupervising"
            @click="onClickReview(dataCaseReview)"
            variant="primary"
          >
            <i class="far fa-edit"></i>
            {{ $t("ID_REVIEW_CASE") }}
          </b-button>
        </div>
        <TabsCaseDetail 
          ref="tabsCaseDetail" 
          :dataCaseStatus="dataCaseStatusTab"
          :dataCase="dataCase"
        ></TabsCaseDetail>
        <ModalCancelCase ref="modal-cancel-case" :dataCase="dataCase"></ModalCancelCase>
      </div>
      <div class="col-sm-3">
        <case-summary
          v-if="dataCaseSummary"
          :data="dataCaseSummary"
        ></case-summary>
        <io-documents
          v-if="
            dataIoDocuments.inputDocuments.length > 0 ||
            dataIoDocuments.outputDocuments.length > 0
          "
          :data="dataIoDocuments"
        ></io-documents>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-9">
        <case-comments
          ref="case-comments"
          :data="dataComments"
          :onClick="onClickComment"
          :postComment="postComment"
          :dropFiles="dropFiles"
        />
      </div>
      <div class="col-sm-3">
        <attached-documents
          v-if="dataAttachedDocuments.items.length > 0 && !attachDocuments"
          :data="dataAttachedDocuments"
        ></attached-documents>
        <attached-documents-edit
          v-if="dataAttachedDocuments.items.length > 0 && attachDocuments"
          :data="dataAttachedDocuments"
          :onRemove="onRemoveAttachedDocument"
        ></attached-documents-edit>
      </div>
    </div>
    <ModalClaimCase ref="modal-claim-case" @claimCatch="claimCatch"></ModalClaimCase>
    <ModalReassignCase ref="modal-reassign-case" @claimCatch="claimCatch"></ModalReassignCase>
    <ModalAssignCase ref="modal-assign-case" @claimCatch="claimCatch"></ModalAssignCase>
    <ModalUnpauseCase ref="modal-unpause-case"></ModalUnpauseCase>
  </div>
</template>

<script>
import IoDocuments from "../components/home/caseDetail/IoDocuments.vue";
import CaseSummary from "../components/home/caseDetail/CaseSummary.vue";
import AttachedDocuments from "../components/home/caseDetail/AttachedDocuments.vue";
import AttachedDocumentsEdit from "../components/home/caseDetail/AttachedDocumentsEdit.vue";
import CaseComment from "../components/home/caseDetail/CaseComment";
import CaseComments from "../components/home/caseDetail/CaseComments";
import TabsCaseDetail from "../home/TabsCaseDetail.vue";
import ButtonFleft from "../components/home/ButtonFleft.vue";
import ModalAssignCase from "./modal/ModalAssignCase.vue";
import ModalCancelCase from "../home/modal/ModalCancelCase.vue";
import ModalNewRequest from "./ModalNewRequest.vue";
import ModalClaimCase from "./modal/ModalClaimCase.vue";
import ModalUnpauseCase from "./modal/ModalUnpauseCase.vue";
import ModalReassignCase from "./modal/ModalReassignCase.vue";
import TaskCell from "../components/vuetable/TaskCell.vue";
import CurrentUserCell from "../components/vuetable/CurrentUserCell.vue"
import utils from "./../utils/utils";
import Api from "../api/index";

export default {
  name: "CaseDetail",
  components: {
    TabsCaseDetail,
    IoDocuments,
    CaseSummary,
    AttachedDocuments,
    AttachedDocumentsEdit,
    CaseComment,
    CaseComments,
    ModalUnpauseCase,
    ModalAssignCase,
    ModalCancelCase,
    ButtonFleft,
    ModalNewRequest,
    ModalClaimCase,
    ModalReassignCase,
    TaskCell,
    CurrentUserCell
  },
  props: {},
  data() {
    return {
      dataAlert: {
        dismissSecs: 5,
        dismissCountDown: 0,
        message: "",
        variant: "info"
      },
      dataCase: null,
      newCase: {
        title: this.$i18n.t("ID_NEW_CASE"),
        class: "btn-success",
        onClick: () => {
          this.$refs["newRequest"].show();
        },
      },
      columns: [
        "task",
        "thread_title",
        "current_user",
        "status",
        "due_date",
        "actions"
      ],
      showTable: true,
      tableData: [],
      options: {
        pagination: { 
            chunk: 3,
            nav: 'scroll',
            edge: true
        },
        headings: {
          task: this.$i18n.t("ID_TASK"),
          thread_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
          current_user: this.$i18n.t("ID_CURRENT_USER"),
          status: this.$i18n.t("ID_STATUS"),
          due_date: this.$i18n.t("ID_DUE_DATE"),
          actions: this.$i18n.t("ID_ACTIONS")
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
          mode: "single", // or 'multiple'
          only: function (row) {
            return true; // any condition
          },
          selectAllMode: "all", // or 'page',
          programmatic: false,
        },
        filterable: false,
        sortable: [],
        requestFunction() {
          return this.$parent.$parent.getCasesForVueTable();
        },
      },
      dataCaseSummary: null,
      dataCaseStatusTab: null,
      dataIoDocuments: {
        titleInput: this.$i18n.t("ID_REQUEST_DOCUMENTS"),
        titleOutput: this.$i18n.t("ID_OUTPUT_DOCUMENTS"),
        inputDocuments: [],
        outputDocuments: []
      },
      dataAttachedDocuments: {
        title: "Attached Documents",
        items: []
      },
      attachDocuments: false,
      dataComments: {
        title: "Comments",
        items: []
      },
      dataCaseReview: {},
      app_num: this.$parent.dataCase.APP_NUMBER,
      supervisor: false,
      flagSupervising: false,
      clickCount: 0,
      singleClickTimer: null,
    };
  },

  mounted() {
    let that = this;
    //restore tab selected to initial state
    let hash = this.$refs["tabsCaseDetail"].$refs["tabs"].getTabHash(0);
    this.$refs["tabsCaseDetail"].$refs["tabs"].selectTab(hash);
    //set dataCase
    this.dataCase = this.$parent.dataCase;
    this.$el.getElementsByClassName("VuePagination__count")[0].remove();
    this.changeFlagSupervising(this.dataCase.FLAG);
    this.getDataCaseSummary();
    this.getInputDocuments();
    this.getOutputDocuments();
    this.getCasesNotes();
    this.requestOpenSummary();
  },
  methods: {
    /**
     * On row click event handler
     * @param {object} event
     */
    onRowClick(event) {
      let self = this;
      self.clickCount += 1;
      if (self.clickCount === 1) {
        self.singleClickTimer = setTimeout(function () {
          self.clickCount = 0;
        }, 400);
      } else if (self.clickCount === 2) {
        clearTimeout(self.singleClickTimer);
        self.clickCount = 0;
        if (event.row.STATUS === 'OPEN' && (!self.supervisor || !self.flagSupervising)) {
          self.onClick(event);
        }
        if (event.row.STATUS === 'PAUSED') {
          self.showModalUnpauseCase(event.row);
        }
        if (event.row.USR_UID === '' && event.row.STATUS !== 'CLOSED' && self.supervisor && self.flagSupervising) {
          self.onClickAssign(event.row);
        }
        if (event.row.USR_UID !== '' && event.row.STATUS !== 'CLOSED' && self.supervisor && self.flagSupervising) {
          self.onClickReassign(event.row);
        }
      }
    },
    /**
     * Shows the modal to unpause a case.
     * @param {Object} item - The data to be used by the modal to unpause
     */
    showModalUnpauseCase(item) {
      this.$refs["modal-unpause-case"].data = item;
      this.$refs["modal-unpause-case"].show();
    },
    postComment(comment, send, files) {
      let that = this;
      Api.caseNotes
        .post(
          _.extend({}, this.dataCase, {
            COMMENT: comment,
            SEND_MAIL: send,
            FILES: files
          })
        )
        .then((response) => {
          if (response.status === 200 || response.status === 201) {
            that.attachDocuments = false;
            that.dataAttachedDocuments.items = [];
            that.getCasesNotes();
          } else {
            that.showAlert(response.data.message, "danger");
            that.dataAttachedDocuments.items = [];
          }
        });
    },
    onClickComment(data) {
      let att = [];
      this.dataAttachedDocuments.items = [];
      _.each(data.data.attachments, (a) => {
        att.push({
          data: a,
          title: a.APP_DOC_FILENAME,
          extension: a.APP_DOC_FILENAME.split(".").pop(),
          onClick: () => {},
        });
      });
      this.dataAttachedDocuments.items = att;
    },
    getDataCaseSummary() {
      let action,
        option,
        that = this;
      Api.cases
        .casesummary(this.dataCase)
        .then((response) => {
          var data = response.data.summary;
          this.dataCaseStatusTab = [];
          this.dataCaseStatusTab.push({
            title: null,
            items: response.data.caseProperties
          });

          _.each(response.data.taskProperties, (o) => {
            this.dataCaseStatusTab.push({
              title: null,
              items: _.isArray(o) ? o : [o]
            });
          });

          this.dataCaseSummary = {
            title: this.$i18n.t("ID_SUMMARY"),
            titleActions: this.$i18n.t("ID_ACTIONS"),
            btnLabel: this.$i18n.t("ID_CANCEL_CASE"),
            btnType: false,
            onClick: null,
            label: {
              process: data[1].label,
              processDescription: data[2].label,
              caseNumber: data[3].label,
              caseTitle: data[4].label,
              status: data[5].label,
              create: data[6].label,
              delegationDate: this.$i18n.t("ID_TASK_DELEGATE_DATE"),
              duration: this.$i18n.t("ID_DURATION")
            },
            text: {
              process: data[1].value,
              processDescription: data[2].value,
              caseNumber: data[3].value,
              caseTitle: data[4].value,
              status: data[5].value,
              create: data[6].value,
              delegationDate: data[7] ? data[7].value : "",
              duration: data[8] ? data[8].value : ""
            }
          };
          // Hack for identify the cancel case button
          Api.cases.actions(this.dataCase).then((response) => {
            action = _.find(response.data, function (o) {
              return o.id === "ACTIONS";
            });
            if (action) {
              option = _.find(action.options, function (o) {
                return o.fn === "cancelCase";
              });
              if (option && !option.hide) {
                that.dataCaseSummary.onClick = () => {
                  that.$refs["modal-cancel-case"].show();
                };
              }
            }
          });
        })
        .catch((err) => {
          throw new Error(err);
        });
    },
    getInputDocuments() {
      Api.cases
        .inputdocuments(this.dataCase)
        .then((response) => {
          let data = response.data,
            document = data.data,
            i,
            info;

          if (data.totalCount > 0 && document !== []) {
            this.dataIoDocuments.inputDocuments = [];
            for (i = 0; i < data.totalCount; i += 1) {
              info = {
                title: document[i].TITLE,
                extension: document[i].TITLE.split(".")[1],
                onClick: () => {},
                data: document[i]
              };
              this.dataIoDocuments.inputDocuments.push(info);
            }
          }
        })
        .catch((err) => {
          throw new Error(err);
        });
    },
    getOutputDocuments() {
      Api.cases
        .outputdocuments(this.dataCase)
        .then((response) => {
          var data = response.data,
            document = data.data,
            i,
            info;

          if (data.totalCount > 0 && document !== []) {
            this.dataIoDocuments.outputDocuments = [];
            for (i = 0; i < data.totalCount; i += 1) {
              info = {
                title: document[i].TITLE,
                extension: document[i].TITLE.split(".")[1],
                onClick: () => {},
                data: document[i]
              };
              this.dataIoDocuments.outputDocuments.push(info);
            }
          }
        })
        .catch((err) => {
          throw new Error(err);
        });
    },
    dropFiles(files) {
      this.attachDocuments = true;
      this.dataAttachedDocuments.items = files;
    },
    onRemoveAttachedDocument(file) {
      this.$refs["case-comments"].removeFile(file);
    },
    /**
     * Get for user format name configured in Processmaker Environment Settings
     *
     * @param {string} name
     * @param {string} lastName
     * @param {string} userName
     * @return {string} nameFormat
     */
    nameFormatCases(name, lastName, userName) {
      let nameFormat = "";
      if (/^\s*$/.test(name) && /^\s*$/.test(lastName)) {
        return nameFormat;
      }
      if (this.nameFormat === "@firstName @lastName") {
        nameFormat = name + " " + lastName;
      } else if (this.nameFormat === "@firstName @lastName (@userName)") {
        nameFormat = name + " " + lastName + " (" + userName + ")";
      } else if (this.nameFormat === "@userName") {
        nameFormat = userName;
      } else if (this.nameFormat === "@userName (@firstName @lastName)") {
        nameFormat = userName + " (" + name + " " + lastName + ")";
      } else if (this.nameFormat === "@lastName @firstName") {
        nameFormat = lastName + " " + name;
      } else if (this.nameFormat === "@lastName, @firstName") {
        nameFormat = lastName + ", " + name;
      } else if (this.nameFormat === "@lastName, @firstName (@userName)") {
        nameFormat = lastName + ", " + name + " (" + userName + ")";
      } else {
        nameFormat = name + " " + lastName;
      }
      return nameFormat;
    },

    getCasesNotes() {
      let that = this;
      Api.caseNotes
        .get(this.dataCase)
        .then((response) => {
          that.formatResponseCaseNotes(response.data.data);
          that.dataComments.noPerms = response.data.noPerms || 0;
        })
        .catch((err) => {
          if (err.response.data) {
            that.showAlert(err.response.data.error.message, "info");
          }
        });
    },
    formatResponseCaseNotes(notes) {
      let that = this,
        notesArray = [];
      _.each(notes, (n) => {
        n.id = _.random(1000000);
        notesArray.push({
          user: that.nameFormatCases(
            n.usr_firstname,
            n.usr_lastname,
            n.usr_username
          ),
          date: n.note_date,
          comment: n.note_content,
          data: n
        });
      });

      this.dataComments.items = notesArray;
    },
    formatCaseProperties(data) {
      let index,
        sections = [];
      this.dataCaseStatusTab = [];
      _.each(data, (o) => {
        if (
          (index = _.findIndex(sections, (s) => {
            return s.title == o.section;
          })) == -1
        ) {
          sections.push({
            title: o.section,
            items: []
          });
          index = 0;
        }
        sections[index].items.push(o);
      });

      this.dataCaseStatusTab = sections;
    },
    getCasesForVueTable() {
      let that = this,
        dt;
      return new Promise((resolutionFunc, rejectionFunc) => {
        Api.cases
          .pendingtask(that.$parent.dataCase)
          .then((response) => {
            dt = that.formatDataResponse(response.data);
            this.dataCaseReview = dt[0];
            this.supervisor = that.isSupervisor();
            resolutionFunc({
              data: dt,
              count: response.data.length,
            });
            that.showTable = response.data.length > 0 ? true : false;
          })
          .catch((err) => {
            throw new Error(err);
          });
      });
    },
    formatDataResponse(response) {
      let data = [];
      _.forEach(response, (v) => {
        data.push({
          TASK: [
            {
              TITLE: v.TAS_TITLE,
              CODE_COLOR: v.TAS_COLOR,
              COLOR: v.TAS_COLOR_LABEL,
              TAS_TYPE: v.TAS_ASSIGN_TYPE
            },
          ],
          THREAD_TITLE: v.DEL_TITLE,
          USER_DATA: this.formatUser(v.user_tooltip),
          STATUS: v.DEL_THREAD_STATUS,
          DUE_DATE: v.DEL_TASK_DUE_DATE,
          TASK_COLOR: v.TAS_COLOR_LABEL,
          APP_UID: v.APP_UID,
          DEL_INDEX: v.DEL_INDEX,
          PRO_UID: v.PRO_UID,
          TAS_UID: v.TAS_UID,
          UNASSIGNED: v.UNASSIGNED,
          USR_ID: v.USR_ID,
          USR_UID: v.USR_UID
        });
      });
      return data;
    },
    /**
     * Format user information to show
     */
    formatUser(data) {
        var dataFormat = [],
            userDataFormat;
        userDataFormat = data.usr_id ?
            utils.userNameDisplayFormat({
                userName: data.usr_firstname,
                firstName: data.usr_lastname,
                lastName: data.usr_username,
                format: window.config.FORMATS.format || null
            })
            : this.$i18n.t("ID_UNASSIGNED");
        dataFormat.push({
            USERNAME_DISPLAY_FORMAT: userDataFormat !== "" ? userDataFormat : this.$i18n.t("ID_UNASSIGNED"),
            EMAIL: data.usr_email,
            POSITION: data.usr_position,
            AVATAR: userDataFormat !== this.$i18n.t("ID_UNASSIGNED") ? window.config.SYS_SERVER_AJAX +
                window.config.SYS_URI +
                `users/users_ViewPhotoGrid?pUID=${data.usr_id}` : "",
            UNASSIGNED: userDataFormat !== this.$i18n.t("ID_UNASSIGNED") ? true : false
        });    
        return dataFormat;
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
     * Click handler
     *
     * @param {object} data
     */
    onClick(data) {
      if (data.row.UNASSIGNED && data.row.USR_ID === 0) {
        this.claimCase(data.row);
      } else {
        this.$emit("onUpdateDataCase", {
          APP_UID: data.row.APP_UID,
          DEL_INDEX: data.row.DEL_INDEX,
          PRO_UID: data.row.PRO_UID,
          TAS_UID: data.row.TAS_UID,
          ACTION: this.dataCase.ACTION || "todo",
          UNASSIGNED: data.row.UNASSIGNED
        });
        this.$emit("onUpdatePage", "XCase");
      }
    },
    /**
     * Review case Click handler
     *
     * @param {object} data
     */
    onClickReview(data) {
      this.$emit("onUpdateDataCase", {
        APP_UID: data.APP_UID,
        DEL_INDEX: data.DEL_INDEX,
        PRO_UID: data.PRO_UID,
        TAS_UID: data.TAS_UID,
        ACTION: "to_revise"
      });
      this.$emit("onUpdatePage", "XCase");
    },
    /**
     * Unpause click handler
     *
     * @param {object} data
     */
    onClickUnpause(data) {
      let that = this;
      Api.cases.unpause(data.row)
        .then((response) => {
          if (response.statusText === "OK" || response.status === 200) {
            that.$refs["vueTable"].getData();
          }
        })
        .catch((error) => {
          that.showAlert(error.response.data.error.message, 'danger');
        });
    },
    /**
     * Assign click handler
     *
     * @param {object} item
     */
    onClickAssign(item) {
      let that = this;
      Api.cases.open(_.extend({ ACTION: "assign" }, item)).then(() => {
        Api.cases.cases_open(_.extend({ ACTION: "todo" }, item)).then(() => {
          that.$refs["modal-assign-case"].data = item;
          that.$refs["modal-assign-case"].show();
        });
      });
    },
    /**
     * Reassign click handler
     *
     * @param {object} item
     */
    onClickReassign(item) {
      let that = this;
      item.FLAG = this.flagSupervising;
      Api.cases.open(_.extend({ ACTION: "reassign" }, item)).then(() => {
        Api.cases.cases_open(_.extend({ ACTION: "todo" }, item)).then(() => {
          that.$refs["modal-reassign-case"].data = item;
          that.$refs["modal-reassign-case"].show();
        });
      });
    }, 
    /**
     * Is supervisor
     *
     * @return {bool} response
     */
    isSupervisor() {
      Api.cases.getIsSupervisor(this.app_num).then((response) => {
        let res = false;
        if (response.statusText === "OK" || response.status === 200) {
          this.supervisor = response.data;
        }
      })
    }, 
    /**
     * Change the flag supervisor
     *
     * @param {string} data
     */
     changeFlagSupervising(data) {
        this.flagSupervising = (data === 'SUPERVISING');
    },  
    /**
     * Claim case
     *
     * @param {object} item
     */
    claimCase(item) {
      let that = this;
      Api.cases.open(_.extend({ ACTION: "unassigned" }, item)).then(() => {
        Api.cases.cases_open(_.extend({ ACTION: "todo" }, item)).then(() => {
          that.$refs["modal-claim-case"].data = item;
          that.$refs["modal-claim-case"].show();
        });
      });
    },
    /**
     * Verify if the case has the permission Summary Form
     * to add dynUid in dataCase
     */
    requestOpenSummary() {
      Api.cases
        .openSummary(this.dataCase)
        .then((response) => {
          var data = response.data;
          if (data.dynUid !== "") {
            this.dataCase.DYN_UID = data.dynUid;
          }
        })
        .catch((e) => {
          console.error(e);
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
.v-container-case-detail {
  padding-top: 20px;
  padding-bottom: 20px;
  padding-left: 50px;
  padding-right: 20px;
}
.pm-in-text-icon {
  font-size: 1.4rem;
  padding-right: 10px;
  line-height: 40px;
}
.table td, .table th {
  vertical-align: middle;
}
</style>