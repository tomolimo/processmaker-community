<template>
  <div>
    <b-modal
      ref="modal-unpause-case"
      hide-footer
      :title="$t('ID_CONFIRMATION')"
      size="md"
    >
      <p>
        {{ $t("ID_ARE_YOU_SURE_UNPAUSE_TASK") }}
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
          <button type="button" class="btn btn-success" @click="unpauseCase">
            {{ $t("ID_UNPAUSE") }}
          </button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import api from "./../../api/index";
export default {
  name: "ModalUnpauseCase",
  components: {},
  props: {},
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
      this.$refs["modal-unpause-case"].show();
    },
    cancel() {
      this.$refs["modal-unpause-case"].hide();
    },
    unpauseCase() {
      let that = this;
      api.cases.unpause(this.data).then((response) => {
        if (response.statusText == "OK" || response.status === 200) {
          that.$refs["modal-unpause-case"].hide();
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
      });
    },
  },
};
</script>

<style>
</style>
