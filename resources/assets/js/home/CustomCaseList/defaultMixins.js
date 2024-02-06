import _ from "lodash";
import api from "../../api/index";
export default {
  data() {
    let that = this;
    return {
      typeView: this.data.settings && this.data.settings.view && this.data.settings.view.typeView
        ? this.data.settings.view.typeView
        : "GRID",
      random: 1,
      dataCasesList: [],
      defaultColumns: [
        "case_number",
        "case_title",
        "process_name",
        "task",
        "send_by",
        "due_date",
        "delegation_date",
        "priority",
      ],
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
          if (this.data.pageParent === "paused") {
            this.showModalUnpauseCase(item);
          } else if(this.data.pageParent === "unassigned") {
            this.claimCase(item);
          } else {
            this.openCase(item);
          }
        },
        headings: {
          case_number: this.$i18n.t("ID_MYCASE_NUMBER"),
          case_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
          process_name: this.$i18n.t("ID_PROCESS_NAME"),
          task: this.$i18n.t("ID_TASK"),
          send_by: this.$i18n.t("ID_SEND_BY"),
          current_user: this.$i18n.t("ID_CURRENT_USER"),
          due_date: this.$i18n.t("ID_DUE_DATE"),
          delegation_date: this.$i18n.t("ID_DELEGATION_DATE"),
          priority: this.$i18n.t("ID_PRIORITY")
        },
        columns: [],
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
     * Get cases for 'Vue Card View' and 'Vue List View'.
     * @param {object} data
     * @returns {Promise}
     */
    getCasesViewMore(data) {
      let that = this,
                dt,
                paged,
                limit = data.limit,
                start = data.page === 1 ? 0 : limit * (data.page - 1),
                filters = {},
                sort = "",
                id = this.data.customListId;
            filters = {
                paged: paged,
                limit: limit,
                offset: start
            };
            if (_.isEmpty(that.filters) && this.data.settings) {
                _.forIn(this.data.settings.filters, function(item, key) {
                    if (filters && item.value) {
                        filters[item.filterVar] = item.value;
                    }
                });
            } else {
                _.forIn(this.filters, function(item, key) {
                    if (filters && item.value) {
                        filters[item.filterVar] = item.value;
                    }
                });
            }
            sort = that.prepareSortString(data);
            if (sort) {
                filters["sort"] = sort;
            }
            return new Promise((resolutionFunc, rejectionFunc) => {
                api.custom[that.data.pageParent]
                    ({
                        id,
                        filters
                    })
                    .then((response) => {
                        let tmp,
                            columns = [],
                            product,
                            newItems = [];
                        that.filterItems = [];
                        that.headings = {};
                        response.data.columns.forEach((item) => {
                            if (item.enableFilter) {
                                if (that.availableItems[that.itemMap[item.field]]) {
                                    newItems.push(that.availableItems[that.itemMap[item.field]]);
                                } else {
                                    product = this.filterItemFactory(item);
                                    if (product) {
                                        newItems.push(product);
                                    }
                                }
                            }
                            that.headings[item.field] = item.name;
                            columns.push(item.field);
                        });
                        that.filterItems = newItems;
                        dt = that.formatDataResponse(response.data.data);
                        that.cardColumns = columns;
                        if (that.isFistTime) {
                            that.filters = that.data.settings && that.data.settings.filters ? that.data.settings.filters : {};
                            that.columns = that.data.settings && that.data.settings.columns ? that.data.settings.columns :  that.getTableColumns(columns);
                            that.settingOptions = that.formatColumnSettings(columns);
                        }
                        resolutionFunc({
                            data: dt,
                            count: response.data.total
                        });
                    })
                    .catch((e) => {
                        rejectionFunc(e);
                    });
            });
    },
    /**
     * Event handler when update the settings columns
     * @param {*} columns 
     */
    onUpdateColumnSettings(columns) {
      this.columns = this.getTableColumns(columns);
      this.random = _.random(0, 10000000000);
    },
    /**
     * Get columns for origin , settings or custom cases list
     */
    getColumnsFromSource() {
      let dt = _.clone(this.dataCasesList),
        res = _.clone(this.defaultColumns);
      if (!this.data.customListId) {
        res = _.map(_.filter(dt, o => o.set), s => s.field);
      }
      return res;
    },
    /**
     * Return the columns for table - concat with field "detail" "actions"
     */
    getTableColumns(columns) {
        return _.concat(["detail"], this.removeDefaultColumns(columns), ["actions"]);
    },
    /**
     * Remove the default columns, 'detail' and 'actions'
     * @param {Array} columns 
     */
    removeDefaultColumns(columns) {
        if (columns[0] === 'detail') {
            columns.shift()
        }
        if (columns[columns.length - 1] === 'actions') {
            columns.pop();
        }
        return columns;
    },
    /**
     * Return options for Table
     * @returns Object
     */
    getTableOptions() {
      let dt = _.clone(this.options);
      dt.headings = _.pick(this.headings, this.columns);
      return dt;
    },
    /**
     * Return options for Table
     * @returns Object
     */
    getVueViewOptions() {
      let dt = _.clone(this.optionsVueView);
      dt.columns = this.cardColumns;
      return dt;
    },
    /**
     * Format column settings for popover
     * @param {*} headings 
     * @returns 
     */
    formatColumnSettings(columns) {
      return _.map(columns, (value, key) => {
        if (this.headings[value]) {
          return { value: this.headings[value], key: value };
        }
        return { value, key: value }
      });
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
        page: this.data.pageParent,
        type: "custom",
        id: this.data.customListId
      });
    }
  }
}