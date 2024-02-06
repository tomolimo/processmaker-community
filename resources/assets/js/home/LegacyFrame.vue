<template>
  <div>
    <iframe
      :width="width"
      ref="xIFrame"
      frameborder="0"
      :src="pageUri"
      :height="height"
      allowfullscreen
      title="xIFrame"
    ></iframe>
  </div>
</template>

<script>
export default {
  name: "LegacyFrame",
  components: {},
  props: {
    pageUri: String,
  },
  mounted() {
    this.height = `${window.innerHeight - this.diffHeight}px`;
    window.addEventListener('resize', this.autoResizeFrame);
  },
  data() {
    return {
      height: "0px",
      width: "100%",
      diffHeight: 10
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    autoResizeFrame(event) {
      event.preventDefault();
      this.height = `${window.innerHeight - this.diffHeight}px`;
      event.stopPropagation();
    },
  },
};
</script>

<style>
</style>
