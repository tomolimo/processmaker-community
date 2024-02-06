<template>
  <div class="card v-case-history">
    <iframe
      :width="width"
      ref="IFrameChangeLog"
      title="IFrameChangeLog"
      frameborder="0"
      :src="path"
      :height="height"
      allowfullscreen
    ></iframe>
  </div>
</template>

<script>
export default {
  name: "ChangeLog",
  props: {
    caseData: Object,
  },
  data() {
    return {
      height: "500px",
      width: "100%",
      diffHeight: 10,
    };
  },
  computed: {
    path() {
      if (this.caseData) {
        let url =
          window.config.SYS_SERVER_AJAX +
          window.config.SYS_URI +
          `cases/ajaxListener?action=changeLogHistory` +
          `&APP_UID=${this.caseData.APP_UID}` +
          `&PRO_UID=${this.caseData.PRO_UID}` +
          `&TAS_UID=${this.caseData.TAS_UID}`;
        return url;
      }
      return '';
    },
  },
  mounted() {},
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
  },
};
</script>

<style>
.v-case-history {
  font-size: 13px;
}
</style>