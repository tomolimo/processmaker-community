<template>
  <div class="pm-multiview-header">
    <div class="pm-multiview-header-title"></div>
    <div class="pm-multiview-header-actions">
      <b-row>
        <b-col sm="8">
          <div class="subtitle" v-if="dataSubtitle">
            <h6>
              {{ dataSubtitle.subtitle }}
              <span>
                <i :class="dataSubtitle.icon" v-bind:style="setIconColor"></i>
              </span>
            </h6>
          </div>
        </b-col>
        <b-col sm="4" class="pm-multiview-header-actions-buttons">
          <b-button
            v-for="action in data.actions"
            :key="action.id"
            @click="action.onClick(action)"
            class="pm-multiview-header-button"
            :title="action.title"
            variant="primary"
          >
            <div>
              <span>
                <i :class="action.icon"></i>
              </span>
            </div>
          </b-button>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
export default {
  name: "MultiviewHeader",
  props: [
    "data",
    "dataSubtitle"
  ],
  data() {
    return {};
  },
  computed : {
    /**
     * Set color to icon defined from custom case list
     */
    setIconColor() {
      return {
        color: this.dataSubtitle.color ? this.dataSubtitle.color : '#000'
      };
    }
  },
  methods: {
    classBtn(cls) {
      return "btn btn-slim btn-force-radius v-btn-header " + cls;
    },
  },
};
</script>

<style>
.pm-multiview-header {
  margin-bottom: 7px;
}
.pm-multiview-header-actions-buttons {
  text-align: end;
}
.pm-multiview-header-button {
  background-color: transparent;
  border-color: white;
  font-size: 1.6rem;
  color: #007bff;
  display: inline-block;
  padding: 0px 7px 0px 7px;
}
</style>
