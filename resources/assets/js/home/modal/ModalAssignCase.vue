<template>
  <div>
    <b-modal
      ref="modal-assign-case"
      hide-footer
      size="lg"
    >
      <template v-slot:modal-title>
        {{ $t('ID_ASSIGN_CASE') }}
        <i class="fas fa-users"></i>
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
            <label for="selectUser">{{ $t('ID_SELECT_USER') }}</label>
          </b-col>
          <b-col sm="9">
            <b-form-select v-model="userSelected" :options="users"></b-form-select>
          </b-col>
        </b-row>

        <b-row class="my-1">
          <b-col sm="3">
            <label for="reasonAssign">{{ $t('ID_REASON') }}</label>
          </b-col>
          <b-col sm="9">
            <b-form-textarea
              id="reasonAssign"
              v-model="reasonAssign"              
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
            @click="assignCase"
          >
            {{ $t("ID_ASSIGN") }}
          </b-button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import api from "../../api/index";
import utils from "../../utils/utils";

export default {
  name: "ModalAssignCase",
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
      reasonAssign: null,
      userSelected: null,
      notifyUser: false
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
      this.getUsers();
      this.$refs["modal-assign-case"].show();
    },
    /**
     * Button cancel
     */
    cancel() {
      this.$refs["modal-assign-case"].hide();
    },
    /**
     * Service to get users
     */
    getUsers() {
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
     * Service assign case, using reassign api service
     */
    assignCase() {
      let that = this;
      this.data.userSelected = this.userSelected;
      this.data.reasonAssign = this.reasonAssign;
      this.data.notifyUser = this.notifyUser;
      api.cases.assignCase(this.data).then((response) => {
        if (response.statusText == "OK" || response.status === 200) {
          that.$refs["modal-assign-case"].hide();
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
    },
    /**
     * Show the alert message
     * @param {string} message - message to be displayed in the body
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