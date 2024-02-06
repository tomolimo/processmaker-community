<template>
  <div>
    <b-modal
      ref="modal-cancel-case"
      hide-footer
      :title="$t('ID_CANCEL_CASE')"
      size="md"
    >
      <b-alert
        :show="dataAlert.dismissCountDown"
        dismissible
        :variant="dataAlert.variant"
        @dismissed="dataAlert.dismissCountDown = 0"
        @dismiss-count-down="countDownChanged"
      >
        {{ dataAlert.message }}
      </b-alert>
      <p>
        You are tying to cancel the current case. Please be aware this action
        cannot be undone
      </p>
      <div class="form-group">
        <textarea
          class="form-control"
          name="comments"
          ref="comment"
          cols="80"
          rows="5"
          aria-label="commentsCancelCase"
        ></textarea>
      </div>
      <div class="row">
        <div class="col-md-12 ml-auto">
          <input id="sendEmailCancelCase" type="checkbox" class="" ref="send" />
          <label class="form-check-label" for="sendEmailCancelCase">
            Send email to participants</label
          >
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary" @click="cancelCase">
          {{ $t("ID_CANCEL_CASE") }}
        </button>
        <button
          type="button"
          class="btn btn-secondary"
          data-dismiss="modal"
          @click="cancel"
        >
          {{ $t("ID_CANCEL") }}
        </button>
      </div>
    </b-modal>
  </div>
</template>

<script>
import api from "./../../api/index";
import eventBus from "../EventBus/eventBus";
export default {
  name: "ModalCancelCase",
  components: {},
  props: {
    dataCase: Object,
  },
  mounted() {},
  data() {
    return {
      dataAlert: {
        dismissSecs: 5,
        dismissCountDown: 0,
        message: "",
        variant: "danger",
      },
      filter: "",
      categories: [],
      categoriesFiltered: [],
      TRANSLATIONS: window.config.TRANSLATIONS,
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    show() {
      this.$refs["modal-cancel-case"].show();
    },
    cancel() {
      this.$refs["modal-cancel-case"].hide();
    },
    cancelCase() {
      let that = this;
      api.cases
        .cancel(
          _.extend({}, this.dataCase, {
            COMMENT: this.$refs["comment"].value,
            SEND: this.$refs["send"].checked ? 1 : 0,
          })
        )
        .then((response) => {
          if (response.status === 200) {
            that.$refs["modal-cancel-case"].hide();
            eventBus.$emit("home-update-page", "inbox");
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
