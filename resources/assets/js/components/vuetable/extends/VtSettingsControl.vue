<template>
    <div class="align-middle table-settings">
        <span v-if="settings()" :class="classObject" :id="id"> </span>
    </div>
</template>
<script>
export default {
  name: "VtSettingsControl",
  props: ["props", "parent"],
  mounted() {},
  methods: {
    settings() {
      return (
        this.props.opts.settings && this.props.opts.settings[this.parent.column]
      );
    },
  },
  computed: {
    classObject() {
      return this.settings()
        ? this.props.opts.settings[this.parent.column]["class"]
        : this.props.class;
    },
    id() {
      return this.settings()
        ? this.props.opts.settings[this.parent.column]["id"]
        : "default-settings-case";
    },
  },
};
</script>
<style>
.table-settings {
  text-align: center;
}
</style>