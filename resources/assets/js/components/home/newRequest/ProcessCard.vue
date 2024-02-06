<template>
  <div :class="classCard()" @click="clickCard(data)">
    <b-card
      :sub-title="data.title"
      class="overflow-hidden"
      style="min-width: 340px; max-width: 340px"
      bg-variant="light"
    >
      <b-card-text class="v-process-card-text">
        {{ data.description }}
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
export default {
  name: "ProcessCard",
  props: {
    data: Object,
    disable: Boolean,
  },
  data() {
    return {};
  },
  methods: {
    classCard() {
      if (this.disable) {
        return "v-inline v-process-card v-disable";
      }
      return "v-inline v-process-card";
    },
    clickCard(data) {
      if (!this.disable) {
        this.data.onClick(data);
      }
    },
  },
};
</script>

<style>
.v-inline {
  display: inline-block;
}

.v-disable {
  opacity: 0.2;
}

.v-process-card {
  margin-right: 15px;
  margin-bottom: 15px;
  border-left: solid lightseagreen;
}

.v-process-card-text {
  font-size: 13px;
}
</style>