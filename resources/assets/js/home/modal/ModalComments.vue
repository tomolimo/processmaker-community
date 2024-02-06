<template>
  <div>
    <b-modal ref="modal-comments" hide-footer size="xl">
      <b-alert
        :show="dataAlert.dismissCountDown"
        dismissible
        :variant="dataAlert.variant"
        @dismissed="dataAlert.dismissCountDown = 0"
        @dismiss-count-down="countDownChanged"
      >
        {{ dataAlert.message }}
      </b-alert>
      <div class="row">
        <div class="col-sm-8">
          <case-comments
            ref="case-comments"
            :data="dataComments"
            :onClick="onClickComment"
            :postComment="postComment"
            :dropFiles="dropFiles"
          />
        </div>
        <div class="col-sm-4">
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
    </b-modal>
  </div>
</template>

<script>
import Api from "./../../api/index";
import CaseComments from "../../components/home/caseDetail/CaseComments.vue";
import AttachedDocuments from "../../components/home/caseDetail/AttachedDocuments.vue";
import AttachedDocumentsEdit from "../../components/home/caseDetail/AttachedDocumentsEdit.vue";
export default {
  name: "ModalComments",
  components: {
    CaseComments,
    AttachedDocuments,
    AttachedDocumentsEdit,
  },
  props: {},
  mounted() {},
  data() {
    return {
      permission: true,
      dataAlert: {
        dismissSecs: 5,
        dismissCountDown: 0,
        message: "",
        variant: "info",
      },
      dataCase: null,
      attachDocuments: false,
      dataComments: {
        title: this.$i18n.t("ID_COMMENTS"),
        items: [],
      },
      dataAttachedDocuments: {
        title: "Attached Documents",
        items: [],
      },
      onClickComment: (data) => {
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
      postComment: (comment, send, files) => {
        let that = this;
        Api.caseNotes
          .post(
            _.extend({}, this.dataCase, {
              COMMENT: comment,
              SEND_MAIL: send,
              FILES: files,
            })
          )
          .then((response) => {
            if (response.status === 200 || response.status === 201) {
              that.attachDocuments = false;
              that.dataAttachedDocuments.items = [];
              that.getCasesNotes();
              this.$emit("postNotes"); 
            }
          })
          .catch((error) => {
            that.showAlert(error.response.data.error.message, "danger");
            that.dataAttachedDocuments.items = [];
          })
      },
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    show() {
      let that = this;
      //Clean the data attached documents for ever
      this.dataAttachedDocuments.items = [];
      this.getCasesNotes((response) => {
        if (that.permission) {
          that.$refs["modal-comments"].show();
        } else {
          that.$parent.showAlert(
            that.$i18n.t("ID_CASES_NOTES_NO_PERMISSIONS"),
            "danger"
          );
        }
      });
    },
    cancel() {
      this.$refs["modal-comments"].hide();
    },
    getCasesNotes(callback) {
      let that = this;
      Api.cases
        .casenotes(this.dataCase)
        .then((response) => {
          that.formatResponseCaseNotes(response.data.notes);
          that.permission = response.data.noPerms == 1 ? false : true;
          if (_.isFunction(callback)) {
            callback(response);
          }
        })
        .catch((err) => {
          throw new Error(err);
        });
    },
    onRemoveAttachedDocument(file) {
      this.$refs["case-comments"].removeFile(file);
    },
    formatResponseCaseNotes(notes) {
      let that = this,
        notesArray = [];
      _.each(notes, (n) => {
        n.id = _.random(1000000);
        notesArray.push({
          user: that.nameFormatCases(
            n.USR_FIRSTNAME,
            n.USR_LASTNAME,
            n.USR_USERNAME
          ),
          date: n.NOTE_DATE,
          comment: n.NOTE_CONTENT,
          data: n,
        });
      });

      this.dataComments.items = notesArray;
    },
    dropFiles(files) {
      this.attachDocuments = true;
      this.dataAttachedDocuments.items = [];
      this.dataAttachedDocuments.items = files;
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
  },
};
</script>

<style>
</style>
