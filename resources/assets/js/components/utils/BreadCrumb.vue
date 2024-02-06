<template>
  <nav>
    <ol class="vp-breadcrumb">
      <li
        v-for="item in formatOptions(options)"
        :key="item.label"
        :class="item.classObject"
      >
        <span @click="item.onClick">
          <span v-if="item.color">
            <div
              class="vp-color-breadcrumb"
              :style="{ backgroundColor: item.color }"
            ></div
          ></span>
          <span>{{ item.label }}</span>
        </span>
      </li>
      <li
        v-for="item in settings"
        :key="item.id"
        class="vp-bread-crumbs-settings vp-float-right vp-inline-block"
      >
        <span
          @click="item.onClick"
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          :title="item.tooltip || ''"
        >
          <i :class="formatClass(item)"></i>
        </span>
      </li>
    </ol>
  </nav>
</template>

<script>
import _ from "lodash";
export default {
  name: "BreadCrumb",
  props: ["options", "settings"],
  data() {
    return {};
  },
  methods: {
    /**
     * format options to Bread Crumbs
     */
    formatOptions(data) {
      let options = data;
      for (let i = 0; i <= options.length - 1; i++) {
        options[i].classObject = {
          "vp-breadcrumb-item": true,
          active: false,
          "vp-inline-block": true,
        };
      }
      return options;
    },
    formatClass(item) {
      return item.class;
    },
  },
};
</script>
<style scoped>
.vp-float-right {
  float: right;
}

.vp-bread-crumbs-settings {
  line-height: 40px;
  font-size: 18px;
  padding-right: 10px;
}

.vp-breadcrumb {
  margin-bottom: 1rem;
  list-style: none;
  background-color: #e9ecef;
  border-radius: 0.25rem;
  line-height: 40px;
  height: 40px;
}

.vp-inline-block {
  display: inline-block;
}

.vp-breadcrumb-item {
  color: grey;
  padding-right: 35px;
}

.vp-color-breadcrumb {
  height: 10px;
  width: 10px;
  display: inline-block;
}
</style>
