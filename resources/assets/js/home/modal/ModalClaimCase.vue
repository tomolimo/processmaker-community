<template>
  <div>
    <b-modal
      ref="modal-claim-case"
      hide-footer
      :title="$t('ID_CONFIRMATION')"
      size="md"
    >
      <p>
        {{ $t("ID_ARE_YOU_SURE_CLAIM_TASK") }}
      </p>
      <div class="row float-right">
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-danger"
            data-dismiss="modal"
            @click="cancel"
          >
            {{ $t("ID_CANCEL") }}
          </button>
          <button type="button" class="btn btn-success" @click="claimCase">
            {{ $t("ID_CLAIM") }}
          </button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import api from "./../../api/index";
import eventBus from "../EventBus/eventBus";

export default {
  name: "ModalClaimCase",
  components: {},
  props: {
    dataCase: Object,
  },
  mounted() {},
  data() {
    return {
      data: null,
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    show() {
      this.$refs["modal-claim-case"].show();
    },
    cancel() {
      this.$refs["modal-claim-case"].hide();
    },
    claimCase() {
      let that = this;
      api.cases.claim(this.data).then((response) => {
        if (response.status === 200) {
          that.$refs["modal-claim-case"].hide();
          if (that.$parent.$refs["vueTable"] !== undefined) {
            that.$parent.$refs["vueTable"].getData();
          }
          if (that.$parent.$refs["vueListView"] !== undefined) {
            that.$parent.$refs["vueListView"].getData();
          }
          if (that.$parent.$refs["vueCardView"] !== undefined) {
            that.$parent.$refs["vueCardView"].getData();
          }
          //TODO Trigger onUpdateDataCase
          eventBus.$emit("home-update-datacase", {
            APP_UID: this.data.APP_UID,
            DEL_INDEX: this.data.DEL_INDEX,
            PRO_UID: this.data.PRO_UID,
            TAS_UID: this.data.TAS_UID,
            ACTION: "todo",
          });
          eventBus.$emit("home-update-page", "XCase");
        }
      }).catch(function (error) {
        that.$refs["modal-claim-case"].hide();
        that.$emit("claimCatch", error.response.data.error.message);
      });
    },
  },
};
</script>

<style>
</style>
