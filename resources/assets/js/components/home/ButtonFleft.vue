<template>
  <div class="float-right">
    <button type="button" :class="classBtn(data.class)" @click="data.onClick">
      <div class="v-inline">
        <i class="fas fa-plus"></i>
      </div>
      <div class="v-inline .px-2">{{ data.title }}</div>
    </button>
  </div>
</template>

<script>
export default {
  name: "ButtonFleft",
  props: {
    data: Object,
  },
  data() {
    return {};
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
  },
};
</script>

<style>
.v-btn-request {
  display: inline-block;
}
.v-inline {
  display: inline-block;
}
</style>