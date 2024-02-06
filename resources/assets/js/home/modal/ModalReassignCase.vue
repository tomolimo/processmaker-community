<template>
  <div>
    <b-modal
      ref="modal-reassign-case"
      hide-footer
      size="lg"
    >
      <template v-slot:modal-title>
        {{ $t('ID_REASSIGN_CASE') }}
        <i :class="icon"></i>
      </template>
      <b-alert
        :show="dataAlert.dismissCountDown"
        dismissible
        :variant="dataAlert.variant"
        @dismissed="dataAlert.dismissCountDown = 0"
        @dismiss-count-down="countDownChanged"
      >
        {{ dataAlert.message }}
      </b-alert>
      <b-container fluid>
        <b-row class="my-1">
          <b-col sm="3">
            <label aria-label="selectUser">{{ $t('ID_SELECT_USER') }}</label>
          </b-col>
          <b-col sm="9">
            <b-form-select id="selectUser" v-model="userSelected" :options="users" aria-label="selectUser"></b-form-select>
          </b-col>
        </b-row>

        <b-row class="my-1">
          <b-col sm="3">
            <label for="reasonReassign">{{ $t('ID_REASON_REASSIGN') }}</label>
          </b-col>
          <b-col sm="9">
            <b-form-textarea
              id="reasonReassign"
              v-model="reasonReassign"              
              rows="3"
              max-rows="6"
            ></b-form-textarea>
          </b-col>
        </b-row>

        <b-row class="my-1">
          <b-col sm="3">
            <label for="notifyUser">{{ $t('ID_NOTIFY_USERS_CASE') }}</label>
          </b-col>
          <b-col sm="9">
            <b-form-checkbox v-model="notifyUser" id="notifyUser" name="notifyUser" switch>
            </b-form-checkbox>
          </b-col>
        </b-row>
      </b-container>
      <div class="modal-footer">
        <div class="float-right">
          <b-button
            variant="danger"
            data-dismiss="modal"
            @click="cancel"
          >
            {{ $t("ID_CANCEL") }}
          </b-button>
          <b-button 
            variant="success" 
            @click="reassignCase"
          >
            {{ $t("ID_REASSIGN") }}
          </b-button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import api from "./../../api/index";
import utils from "../../utils/utils";

export default {
  name: "ModalReassignCase",
  components: {},
  props: {},
  mounted() {},
  data() {
    return {
      dataAlert: {
        dismissSecs: 5,
        dismissCountDown: 0,
        message: "",
        variant: "danger",
      },
      data: null,
      locale: 'en-US',
      users: [],
      reasonReassign: null,
      userSelected: null,
      notifyUser: false,
      icon: "fas fa-undo"
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    /**
     * Show modal
     */
    show() {
      this.users = [];
      this.reasonReassign = null;
      this.userSelected = null;
      this.notifyUser = false;
      this.getUsersReassign();
      this.$refs["modal-reassign-case"].show();
      if (this.data.FLAG){
        this.icon = "fas fa-exchange-alt";
      }
    },
    /**
     * Button cancel
     */
    cancel() {
      this.$refs["modal-reassign-case"].hide();
    },
    /**
     * Service to get user reassign
     */
    getUsersReassign() {
      let that = this;
      api.cases.getUsersToReassign(this.data).then((response) => {
        var users = response.data.data,
          i;
        if (response.statusText == "OK" || response.status === 200) {
          for (i = 0; i < users.length; i += 1) {
            that.users.push({
              value: users[i].USR_UID,
              text: utils.userNameDisplayFormat({
                userName: users[i].USR_USERNAME || "",
                firstName: users[i].USR_FIRSTNAME || "",
                lastName: users[i].USR_LASTNAME || "",
                format: window.config.FORMATS.format || null
              })
            });
          }
        }
      });
    },
    /**
     * Service reassign case
     */
    reassignCase() {
      let that = this;
      this.data.userSelected = this.userSelected;
      this.data.reasonReassign = this.reasonReassign;
      this.data.notifyUser = this.notifyUser;
      if (!this.data.FLAG){
        api.cases.reassingCase(this.data).then((response) => {
          if (response.statusText == "OK" || response.status === 200) {
            that.$refs["modal-reassign-case"].hide();
            if (that.$parent.$refs["vueTable"] !== undefined) {
              that.$parent.$refs["vueTable"].getData();
            }
            if (that.$parent.$refs["vueListView"] !== undefined) {
              that.$parent.$refs["vueListView"].getData();
            }
            if (that.$parent.$refs["vueCardView"] !== undefined) {
              that.$parent.$refs["vueCardView"].getData();
            }
          }
        })
        .catch((e) => {
          if(e.response.data && e.response.data.error){
            that.showAlert(e.response.data.error.message, "danger");
          }
        });
      } else {
        api.cases.reassingCaseSupervisor(this.data).then((response) => {
          if (response.statusText == "OK" || response.status === 200) {
            that.$refs["modal-reassign-case"].hide();
            if (that.$parent.$refs["vueTable"] !== undefined) {
              that.$parent.$refs["vueTable"].getData();
            }
            if (that.$parent.$refs["vueListView"] !== undefined) {
              that.$parent.$refs["vueListView"].getData();
            }
            if (that.$parent.$refs["vueCardView"] !== undefined) {
              that.$parent.$refs["vueCardView"].getData();
            }
          }
        })
        .catch((e) => {
          if(e.response.data && e.response.data.error){
            that.showAlert(e.response.data.error.message, "danger");
          }
        });
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
    }
  },
};
</script>