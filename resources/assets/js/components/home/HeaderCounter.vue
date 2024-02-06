<template>
  <div class="header-container">
    <button
      type="button"
      v-for="header in data"
      :key="header.title"
      @click="header.onClick(header)"
      :class="classBtn(header.class)"
    >
      <div class="v-center-header">
        <div class="v-btn-textm-header">
          <span class="v-text2">
            <i :class="header.icon"></i>
          </span>
          <span class="v-btn-textb-header float-right">
            {{ header.counter }}
          </span>
        </div>
        <span class="v-btn-texts-header">
          {{ header.title }}
        </span>
      </div>
    </button>
  </div>
</template>

<script>
export default {
  name: "HeaderCounter",
  props: {
    data: Array,
  },
  data() {
    return {};
  },
  methods: {
    classBtn(cls) {
      return "btn btn-slim btn-force-radius v-btn-header " + cls;
    },
  },
};
</script>

<style>
.v-btn-textb-header {
  font-size: 1.1vw;
  padding-top: 5px;
}

.v-btn-textm-header {
  font-size: 1vw;
}

.v-btn-texts-header {
  display: inline-block;
  font-size: 1vw;
}
.v-btn-header {
  padding: 20px;
  flex: 2;
  display: flex;
  flex-direction: row;
  justify-content: center;
  margin-right: 15px;
}

.v-center-header {
  width: fit-content;
  display: inline-block;
}
.v-center {
  text-align: center;
}

.v-text2 {
  font-size: 2vw;
  float: left;
}
.header-container {
  display: flex;
  justify-content: space-between;
}

.btn-warning {
    color: white !important;
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-warning:hover {
    color: white !important;
    background-color: #e0a800;
    border-color: #d39e00;
}
</style>
