<template>
  <div class="pm-vue-card-view" :height="height">
    <div class="pm-vue-card-view-container">
      <div
        class="pm-vue-card-view-body"
        :style="{height: height + 'px'}"
      >
        <vue-card v-for="item in data" :key="item.id" :item="item" :options="options">
          <b-row>
            <b-col sm="9">
              <slot
                v-for="column in options.columns"
                :name="column"
                :item="item"
                :column="column"
                :headings="options.headings"
              ></slot>
            </b-col>
            <b-col sm="3">
              <slot
                name="actions"
                :item="item"
              ></slot>
            </b-col>
          </b-row>
        </vue-card>
      </div>

      <div class="pm-vue-card-view-footer">
        <a @click="viewMore" class="list-group-item">{{loadMore}}</a>
      </div>
    </div>
  </div>
</template>

<script>
import VueCard from "./VueCard.vue";
import DefaultMixins from "./VueCardViewMixins";
export default {
  name: "VueCardView",
  mixins: [DefaultMixins],
  components: {
    VueCard,
  },
  props: ["options"],
  data() {
    return {
      loadMore: this.$t("ID_LOAD_MORE")
    };
  },
  mounted() {
  },
  methods: {
    classBtn(cls) {
      return "btn btn-slim btn-force-radius v-btn-header " + cls;
    },
    /**
     * Filter the column send_by
     */
    filterOptions() {
      this.options.columns = this.options.columns.filter(function(item) {
        return item !== "send_by";
      });
    }
  },
};
</script>

<style>
.pm-vue-card-view {
  font-family: "proxima-nova", "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 0.9rem;
}

.pm-vue-card-view-body {
  border: 1px solid rgba(0, 0, 0, 0.125);
  padding-bottom: 5px;
  margin-top: 5px;
  overflow-y: auto;
}

.pm-vue-card-view-footer {
  text-align: center;
  line-height: 1.25;
}
</style>
