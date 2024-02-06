<template>
  <div class="d-flex">
    <iframe
      :width="width"
      ref="xIFrame"
      title="xIFrame"
      frameborder="0"
      :src="path"
      :height="height"
      allowfullscreen
      @load="onLoadIframe"
    ></iframe>
    <Debugger v-if="openDebug === true" :style="'height:' + height + 'px'" ref="debugger"/>
  </div>
</template>

<script>
import Debugger from "../components/home/debugger/Debugger.vue";
import api from "../api/index";
export default {
  name: "XCase",
  components: {
    Debugger
  },
  props: {
    data: Object
  },
  data() {
    return {
      openDebug: false,
      dataCase: null,
      height: "100%",
      width: "100%",
      diffHeight: 10,
      path: "",
    };
  },
  mounted() {
    let that = this;
    this.height = window.innerHeight - this.diffHeight;
    this.dataCase = this.$parent.dataCase;
    if (this.dataCase.ACTION === "jump") {
      this.path =
        window.config.SYS_SERVER_AJAX +
        window.config.SYS_URI +
        `cases/open?APP_NUMBER=${this.dataCase.APP_NUMBER}&action=${this.dataCase.ACTION}&actionFromList=${this.dataCase.ACTION_FROM_LIST}`;
    } else {
      this.path =
        window.config.SYS_SERVER_AJAX +
        window.config.SYS_URI +
        `cases/open?APP_UID=${this.dataCase.APP_UID}&DEL_INDEX=${this.dataCase.DEL_INDEX}&TAS_UID=${this.dataCase.TAS_UID}&action=${this.dataCase.ACTION}`;
    }
    if (this.dataCase.UNASSIGNED === true) {
      this.path =
        window.config.SYS_SERVER_AJAX +
        window.config.SYS_URI +
        `cases/open?APP_UID=${this.dataCase.APP_UID}&DEL_INDEX=${this.dataCase.DEL_INDEX}&action=unassigned`;
    }

    setTimeout(() => {
      let that = this;
      if (this.dataCase.APP_UID && this.dataCase.PRO_UID) {
        api.cases.debugStatus(this.dataCase)
          .then((response) => {
            if (response.data) {
              that.openDebug = true;
            }
          })
          .catch((error) => {
            that.openDebug = false;
          });
      }
    }, 2000);
    window.addEventListener("resize", this.handleIframeResize);
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    /**
     * update view in component
     */
    updateView(){
      if(this.openDebug){
        this.$refs["debugger"].loadData();
      }
    },
    onLoadIframe() {},
    /**
     * Resize event Handler
     * @param {object} e
     */
    handleIframeResize(e) {
      this.height = window.innerHeight - this.diffHeight;
    }
  },
};
</script>

<style>
.debugger-inline-cont {
  overflow: hidden;
}
</style>
