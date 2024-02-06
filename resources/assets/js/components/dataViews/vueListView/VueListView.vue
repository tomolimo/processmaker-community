<template>
  <div class="pm-vue-list-view" :height="height">
    <div class="pm-vue-list-view-container">
      <div class="pm-vue-list-view-body" :style="{ height: height + 'px' }">
        <vue-list
          v-for="item in data"
          :key="item.id"
          :item="item"
          :options="options"
        >
          <b-row>
            <b-col sm="10">
              <b-row>
                <b-col sm="5" v-for="column in options.columns" :key="column">
                  <slot
                    :name="column"
                    :item="item"
                    :column="column"
                    :headings="options.headings"
                  >
                  </slot>
                </b-col>
              </b-row>
            </b-col>
            <b-col sm="2">
              <slot name="actions" :item="item"></slot>
            </b-col>
          </b-row>
        </vue-list>
      </div>

      <div class="pm-vue-list-view-footer">
        <a @click="viewMore" class="list-group-item">{{ loadMore }}</a>
      </div>
    </div>
  </div>
</template>

<script>
import VueList from "./VueList.vue";
import DefaultMixins from "./VueListViewMixins";
export default {
  name: "VueListView",
  mixins: [DefaultMixins],
  components: {
    VueList,
  },
  props: ["options"],
  data() {
    return {
      loadMore: this.$t("ID_LOAD_MORE"),
      chunkColumns: [],
    };
  },
  mounted() {},
  methods: {
    classBtn(cls) {
      return "btn btn-slim btn-force-radius v-btn-header " + cls;
    }
  },
};
</script>

<style>
.pm-vue-list-view {
  font-family: "proxima-nova", "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 0.9rem;
}

.pm-vue-list-view-body {
  border: 1px solid rgba(0, 0, 0, 0.125);
  padding-bottom: 5px;
  margin-top: 5px;
  overflow-y: auto;
}

.pm-vue-list-view-footer {
  text-align: center;
  line-height: 1.25;
}
</style>
