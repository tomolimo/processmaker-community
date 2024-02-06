<template>
  <div class="card v-case-history">
    <iframe
      :width="width"
      ref="IFrameCaseHistory"
      title="IFrameCaseHistory"
      frameborder="0"
      :src="path"
      :height="height"
      allowfullscreen
    ></iframe>
  </div>
</template>

<script>
export default {
  name: "CaseHistory",
  props: {
    data: Object,
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
      let url =
        window.config.SYS_SERVER_AJAX +
        window.config.SYS_URI +
        `cases/ajaxListener?action=caseHistory`;
      return url;
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