<template>
  <td :tabindex="props.tabIndex" :class="`${props.classes} __${convertString(props.column)}`">
    <vnodes :vnodes="props.content" />
  </td>
</template>

<script>
export default {
  name: "TableCell",
  props: ["props"],
  components: {
    vnodes: {
      functional: true,
      render: (h, ctx) =>
        typeof ctx.props.vnodes === "object"
          ? ctx.props.vnodes
          : [ctx.props.vnodes]
    }
  },
  created: function() {},
  methods: {
    convertString: function(str) {
      var res;
      if (str) {
        res = str.replace(/ /g, "");
        return res.toLowerCase();
      }
      return str;
    }
  }
};
</script>