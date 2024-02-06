<template>
  <th
    slot-scope="props"
    :id="`${convertString(props.heading)}`"
    v-on="props.thEvents"
    v-bind="props.thAttrs"
  >
    <span class="VueTables__heading" :title="props.title">
      <vnodes :vnodes="props.heading" />
    </span>
    <vt-sort-control />
  </th>
</template>

<script>
import VtSortControl from "vue-tables-2/compiled/components/VtSortControl.js";
export default {
  name: "VtTableHeading",
  components: {
    VtSortControl,
    vnodes: {
      functional: true,
      render: (h, ctx) =>
        typeof ctx.props.vnodes === "object"
          ? ctx.props.vnodes
          : [ctx.props.vnodes]
    }
  },
  props: ["props"],
  methods: {
    convertString: function(str) {
      var res = str.replace(/ /g, "");
      return res.toLowerCase();
    }
  }
};
</script>
<style>
.VueTables th#enable {
  width: 5%;
}
.VueTables th#title {
  width: 25%;
}
.VueTables th#scheduletime {
  width: 50%;
}
.VueTables th#settings {
  width: 20%;
  color: white;
}
</style>