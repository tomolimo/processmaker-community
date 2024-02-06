<template>
  <th slot-scope="props" v-on="events()" v-bind="props.thAttrs" :key="random">
    <span class="VueTables__heading" :title="props.title">
      <vnodes :vnodes="props.heading" />
    </span>
    <vt-sort-control />
    <vt-settings-control :props="props" :parent="$parent" />
  </th>
</template>
<script>
import VtSortControl from "vue-tables-2/compiled/components/VtSortControl";
import VtSettingsControl from "./VtSettingsControl.vue";
export default {
  name: "VtTableHeading",
  components: {
    VtSortControl,
    VtSettingsControl,
    vnodes: {
      functional: true,
      render: (h, ctx) =>
        typeof ctx.props.vnodes === "object"
          ? ctx.props.vnodes
          : [ctx.props.vnodes],
    },
  },
  computed: {
    random() {
      return _.random(0, 10000000000);
    },
  },
  props: ["props"],
  mounted() {},
  methods: {
    events() {
      return this.props.opts.settings &&
        this.props.opts.settings[this.$parent.column]
        ? this.props.opts.settings[this.$parent.column]["events"]
        : this.props.thEvents;
    },
  },
};
</script>