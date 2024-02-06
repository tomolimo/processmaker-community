<template>
  <div>
    <b-modal
      ref="my-modal"
      hide-footer
      :title="$t('ID_WEVE_MADE_IT_EASY_FOR_YOU')"
      size="xl"
    >
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1"
            ><i class="fas fa-search"></i
          ></span>
        </div>
        <input
          v-model="filter"
          type="text"
          class="form-control"
          :placeholder="$t('ID_CASES_MENU_SEARCH')"
          aria-label="Search"
          aria-describedby="basic-addon1"
          @input="onChangeFilter"
        />
      </div>
      <div v-for="item in categoriesFiltered" :key="item.title">
        <process-category :data="item" :disable="disable" />
      </div>
    </b-modal>
  </div>
</template>

<script>
import ProcessCategory from "./../components/home/newRequest/ProcessCategory.vue";
import api from "./../api/index";
import _ from "lodash";
export default {
  name: "ModalNewRequest",
  components: {
    ProcessCategory,
  },
  props: {
    data: Object,
  },
  mounted() {},
  data() {
    return {
      disable: false,
      filter: "",
      categories: [],
      categoriesFiltered: [],
      TRANSLATIONS: window.config.TRANSLATIONS,
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    show() {
      this.filter = "";
      this.$refs["my-modal"].show();
      this.getProcess();
    },
    getProcess() {
      let that = this;
      api.process.list
        .start()
        .then((response) => {
            if (response.data && response.data.success !== "failure") {
                that.categories = that.formatCategories(response.data);
                that.categoriesFiltered = that.categories;
            }
        })
        .catch((e) => {
          console.error(e);
        });
    },
    /**
     * Change the property filter
     */
    onChangeFilter() {
      let that = this,
        categories = [],
        processes = [];
      this.categoriesFiltered = [];
      _.each(this.categories, (o) => {
        processes = that.filterProcesses(o.items);
        if (processes.length != 0) {
          that.categoriesFiltered.push({
            title: o.title,
            items: processes,
          });
        }
      });
    },
    /**
     * Filter the processes in category, serach by title and description
     */
    filterProcesses(processes) {
      let that = this;
      return _.filter(processes, (p) => {
        return p.title.toLowerCase().indexOf(that.filter.toLowerCase()) !== -1 ||
          p.description.toLowerCase().indexOf(that.filter.toLowerCase()) !== -1;
      });
    },
    formatResponseGetProcess(response) {
      let res = [],
        items,
        that = this,
        data = response.data;
      _.each(data, (o) => {
        items = [];
        _.each(o.children, (v) => {
          items.push({
            title: v.otherAttributes.value,
            task_uid: v.tas_uid,
            pro_uid: v.pro_uid,
            description: v.otherAttributes.catname,
            onClick: that.startNewCase,
          });
        });
        res.push({
          title: o.text,
          items: items,
        });
      });
      return res;
    },
    formatCategories(data) {
      let res = [],
        that = this,
        index,
        categories = [];
      _.each(data, (o) => {
        index = _.findIndex(categories, (c) => {
          return c.id == o.pro_category;
        });
        if (index == -1) {
          categories.push({
            id: o.pro_category,
            title: o.category_name,
            items: [],
          });
          index = categories.length - 1;
        }
        categories[index].items.push({
          title: o.pro_title,
          description: o.pro_description,
          task_uid: o.tas_uid,
          pro_uid: o.pro_uid,
          onClick: that.startNewCase,
        });
      });
      return categories;
    },
    startNewCase(dt) {
      let self = this;
      this.disable = true;
      api.cases
        .start(dt)
        .then(function (data) {
          self.$refs["my-modal"].hide();
          self.$parent.$parent.dataCase = {
            APP_UID: data.data.APPLICATION,
            DEL_INDEX: 1,
            ACTION: "draft",
            PRO_UID: data.data.PROCESS
          };
          self.disable = false;
          self.$parent.$parent.page = "XCase";
        })
        .catch((err) => {
          self.disable = false;
          throw new Error(err);
        });
    },
  },
};
</script>

<style>
</style>
