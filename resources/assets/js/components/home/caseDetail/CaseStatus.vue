<template>
  <div class="card v-pmcase-summary-card">
    <div class="card-body" v-if="data">
      <div
        v-for="section in data"
        :key="section.title"
        class="v-attached-block"
      >
        <h6 class="card-subtitle mb-2 text-muted">{{ section.title }}</h6>
        <div class="card-text v-pmcase-summary-card-body">
          <div
            v-for="item in section.items"
            :key="item.title"
            class="v-attached-block"
          >
            <span> {{ item.label }} </span><span class="font-weight-bold">
              {{ item.value }}</span
            >
          </div>
        </div>
        <br />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CaseStatus",
  props: {
    data: Array,
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
.v-pmcase-summary-card {
  font-size: 13px;
}

.v-pmcase-summary-card-body {
  padding-left: 15px;
}

.v-pmcase-summary-center {
  text-align: center;
}
</style>