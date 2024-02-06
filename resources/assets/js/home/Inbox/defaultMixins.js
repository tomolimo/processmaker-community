import _ from "lodash";
import api from "../../api/index";
export default {
  data() {
    let that = this;
    return {
      typeView: this.settings && this.settings.view && this.settings.view.typeView
        ? this.settings.view.typeView
        : "GRID",
      random: 1,
      dataMultiviewHeader: {
        actions: [
          {
            id: "view-grid",
            title: "Grid",
            onClick(action) {
              that.typeView = "GRID";
              that.updateRootSettings("view", {
                typeView: that.typeView
              });
            },
            icon: "fas fa-table",
          },
          {
            id: "view-list",
            title: "List",
            onClick(action) {
              that.typeView = "LIST";
              that.updateRootSettings("view", {
                typeView: that.typeView
              });
            },
            icon: "fas fa-list",
          },
          {
            id: "view-card",
            title: "Card",
            onClick(action) {
              that.typeView = "CARD";
              that.updateRootSettings("view", {
                typeView: that.typeView
              });
            },
            icon: "fas fa-th",
          },
        ],
      },
      optionsVueView: {
        limit: 10,
        dblClick: (event, item, options) => {
          this.openCase(item);
        },
        headings: {
          case_number: this.$i18n.t("ID_MYCASE_NUMBER"),
          thread_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
          process_category: this.$i18n.t("ID_CATEGORY_PROCESS"),
          process_name: this.$i18n.t("ID_PROCESS_NAME"),
          task: this.$i18n.t("ID_TASK"),
          send_by: this.$i18n.t("ID_SEND_BY"),
          current_user: this.$i18n.t("ID_CURRENT_USER"),
          due_date: this.$i18n.t("ID_DUE_DATE"),
          delegation_date: this.$i18n.t("ID_DELEGATION_DATE"),
          priority: this.$i18n.t("ID_PRIORITY")
        },
        columns: [
          "case_number",
          "thread_title",
          "process_category",
          "process_name",
          "due_date",
          "delegation_date",
          "priority",
          "task",
          "send_by",
        ],
        requestFunction(data) {
          return that.getCasesViewMore(data);
        },
        requestFunctionViewMore(data) {
          return that.getCasesViewMore(data);
        }
      }
    }
  },
  created: function () {

  },
  methods: {
    /**
    * Get cases for Vue Card View
    */
    getCasesViewMore(data) {
      let that = this,
        dt,
        paged,
        limit = data.limit,
        start = data.page === 1 ? 0 : limit * (data.page - 1),
        filters = {};
      filters = {
        limit: limit,
        offset: start
      };
      _.forIn(this.filters, function (item, key) {
        if (filters && item.value) {
          filters[item.filterVar] = item.value;
        }
      });
      return new Promise((resolutionFunc, rejectionFunc) => {
        api.cases
          .todo(filters)
          .then((response) => {
            dt = that.formatDataResponse(response.data.data);
            resolutionFunc({
              data: dt,
              count: response.data.total,
            });
          })
          .catch((e) => {
            rejectionFunc(e);
          });
      });
    },
    /**
     * Format columns for custom columns
     * @param {*} headings 
     * @returns 
     */
    formatColumnSettings(headings) {
      let res = [];
      _.forEach(headings, function (value, key) {
        if (key != "actions") {
          res.push({ value, key });
        }
      });
      return res;
    },
    /**
     * Formating the columns selected
     * @param {*} columns 
     * @returns 
     */
    formatColumnSelected(columns) {
      let cols = _.clone(columns);
      cols.pop();
      return cols;
    },
    /**
     * Event handler when update the settings columns
     * @param {*} columns 
     */
    onUpdateColumnSettings(columns) {
      let cols = columns;
      if (_.findIndex(cols, 'actions') == -1) {
        cols.push("actions");
      }
      this.columns = cols;
      this.random = _.random(0, 10000000000);
    },
    /**
     * Update settings for user
     * @param {string} key
     * @param {*} data
     */
    updateRootSettings(key, data) {
      this.$emit("updateSettings", {
        data: data,
        key: key,
        page: "inbox",
        type: "normal",
        id: this.id
      });
    }
  }
}