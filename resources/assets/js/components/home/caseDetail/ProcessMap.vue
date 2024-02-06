<template>
  <div class="card v-process-map">
    <iframe
      :width="width"
      ref="IFrameProcessMap"
      title="IFrameProcessMap"
      frameborder="0"
      :src="path"
      :height="height"
      allowfullscreen
    ></iframe>
  </div>
</template>

<script>
export default {
  name: "ProcessMap",
  props: {
    data: Object
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
      let url = "";
      if (this.data && this.data.PRO_UID) {
        url =
          window.config.SYS_SERVER_AJAX +
          window.config.SYS_URI +
          `designer?prj_uid=${this.data.PRO_UID}&prj_readonly=true&app_uid=${this.data.APP_UID}`;
        return url;
      }
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
.v-process-map {
  font-size: 13px;
}
</style>