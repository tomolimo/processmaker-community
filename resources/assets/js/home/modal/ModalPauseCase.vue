<template>
  <div>
    <b-modal
      ref="modal-pause-case"
      hide-footer
      size="lg"
    >
      <template v-slot:modal-title>
        {{ $t('ID_PAUSE_CASE') }}
        <i class="far fa-pause-circle"></i>
      </template>
      <b-container fluid>
        <b-alert
          :show="dataAlert.dismissCountDown"
          dismissible
          :variant="dataAlert.variant"
          @dismissed="dataAlert.dismissCountDown = 0"
          @dismiss-count-down="countDownChanged"
        >
          {{ dataAlert.message }}
        </b-alert>
        <b-row class="my-1">
          <b-col sm="3">
            <label aria-label="pauseDate">{{ $t('ID_PAUSE_DATE') }}</label>
          </b-col>
          <b-col sm="5">
            <b-form-datepicker
              disabled  
              id="pauseDate"
              class="mb-2"
              aria-controls="pauseDate"
              v-model="pauseData.pauseDate"
              :locale="locale"
              :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
            ></b-form-datepicker>
          </b-col>
          <b-col sm="4">
            <input type="time" id="pauseTime" v-model="pauseData.pauseTime" class="form-control" disabled>
          </b-col>
        </b-row>
        
        <b-row class="my-1">
          <b-col sm="3">
            <label aria-label="unpauseDate">{{ $t('ID_UNPAUSE_DATE') }}</label>
          </b-col>
          <b-col sm="5">
            <b-form-datepicker
              id="unpauseDate"
              class="mb-2"
              aria-controls="unpauseDate"
              v-model="pauseData.unpauseDate"
              :locale="locale"
              :min="minDate"
              :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
            ></b-form-datepicker>
          </b-col>
          <b-col sm="4">
            <input type="time" v-model="pauseData.unpauseTime" id="unpauseTime" class="form-control">
          </b-col>
        </b-row>

        <b-row class="my-1">
          <b-col sm="3">
            <label for="reasonPause">{{ $t('ID_REASON_PAUSE') }}</label>
          </b-col>
          <b-col sm="9">
            <b-form-textarea
              id="reasonPause"
              v-model="pauseData.reasonPause"              
              rows="3"
              max-rows="6"
            ></b-form-textarea>
          </b-col>
        </b-row>

        <b-row>
          <b-col sm="3">
            <label for="notifyUser">{{ $t('ID_NOTIFY_USERS_CASE') }}</label>
          </b-col>
          <b-col sm="8">
            <b-form-checkbox v-model="pauseData.nofitfyUser" id="notifyUser" name="notifyUser" switch>
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
            @click="pauseCase"
          >
            {{ $t("ID_PAUSE") }}
          </b-button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import api from "./../../api/index";
export default {
  name: "ModalPauseCase",
  components: {},
  props: {},
  mounted() {},
  data() {
    return {
      data: null,
      locale: 'en-US',
      pauseData: {
        unpauseDate: '',
        unpauseTime: '',
        reasonPause: '',
        nofitfyUser: '',
        pauseDate: '',
        pauseTime: ''
      },
      minDate: '',
      dataAlert: {
        dismissSecs: 5,
        dismissCountDown: 0,
        message: "",
        variant: "info"
      },
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    show() {
      this.setDateTime();
      this.$refs["modal-pause-case"].show();
    },
    cancel() {
      this.$refs["modal-pause-case"].hide();
    },
    /**
     * Set DateTime with current time as default
     */
    setDateTime() {
      var now = new Date(),
          nextDay = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1),
          today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      this.minDate = nextDay;
      this.pauseData.pauseDate = today;
      this.pauseData.pauseTime = now.getHours() + ":" + now.getMinutes();

    },
    /**
     * Pause the case
     */
    pauseCase() {
      let that = this;
      this.data.unpausedDate = this.pauseData.unpauseDate;
      this.data.unpausedTime = this.pauseData.unpauseTime;
      this.data.nofitfyUser = this.pauseData.nofitfyUser;
      this.data.reasonPause = this.pauseData.reasonPause;
      api.cases.pauseCase(this.data)
        .then((response) => {
          if (response.statusText == "OK" || response.status === 200) {
            that.$refs["modal-pause-case"].hide();
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
        .catch((error) => {
          that.showAlert(error.response.data.error.message, "danger");
        });
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
