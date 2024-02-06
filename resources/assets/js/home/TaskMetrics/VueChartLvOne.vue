<template>
  <div ref="v-pm-charts" class="v-pm-charts vp-inline-block">
    <div class="p-1 v-flex">
      <h6 class="v-search-title">
        {{ $t("ID_DRILL_DOWN_NUMBER_TASKS_PROCESS") }}
      </h6>
      <div>
        <BreadCrumb
          :options="dataBreadcrumbs()"
          :settings="settingsBreadcrumbs"
        />
        <ProcessPopover
          :options="optionsProcesses"
          :selected="selectedProcesses"
          @onChange="onChangeSearchPopover"
          target="pm-task-process"
          ref="pm-task-process"
          @onUpdateColumnSettings="onUpdateColumnSettings"
        />
        <div class="vp-width-p40 vp-inline-block">
          <multiselect
            v-model="category"
            :options="optionsCategory"
            :searchable="false"
            :close-on-select="true"
            :show-labels="false"
            track-by="id"
            label="name"
          ></multiselect>
        </div>
        <label class="vp-inline-block vp-padding-l20">{{
          $t("ID_TOP10")
        }}</label>
        <div class="vp-inline-block">
          <b-form-checkbox
            v-model="top"
            name="check-button"
            @change="changeOption"
            switch
          >
          </b-form-checkbox>
        </div>
        <b-popover
          ref="popover"
          :target="popoverTarget"
          variant="secondary"
          placement="right"
        >
          <div class="vp-chart-minipopover">
            <div class="vp-align-right vp-flex1">
              <button
                type="button"
                class="btn btn-link btn-sm"
                @click="onClickDrillDown"
              >
                <i class="fas fa-chart-line"></i>
                {{ $t("ID_DRILL") }}
              </button>
            </div>
            <div class="vp-flex1">
              <button
                type="button"
                class="btn btn-link btn-sm"
                @click="onClickData"
              >
                <i class="fas fa-th"></i>
                {{ $t("ID_DATA") }}
              </button>
            </div>
          </div>
        </b-popover>
        <div class="vp-inline-block vp-right vp-padding-r40">
          <span
            class="v-search-title"
            @click="showProcessesPopover"
            id="pm-task-process"
          >
            <i class="fas fa-cog"></i>
          </span>
        </div>
      </div>
      <div class="v-search-info">
        {{ $t("ID_SELECT_PROCESS_DRILL") }}
      </div>
      <apexchart
        ref="LevelOneChart"
        :width="width"
        :options="options"
        :series="seriesBar"
      ></apexchart>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import jquery from "jquery";
import Api from "../../api/index";
import BreadCrumb from "../../components/utils/BreadCrumb.vue";
import ProcessPopover from "./ProcessPopover.vue";
import Multiselect from "vue-multiselect";
import eventBus from "./../EventBus/eventBus";
import mixinLocales from "./mixinsLocales";

export default {
  name: "VueChartLvOne",
  mixins: [mixinLocales],
  components: {
    Multiselect,
    BreadCrumb,
    ProcessPopover,
  },
  props: ["data"],
  data() {
    let that = this;
    return {
      popoverTarget: "",
      dataPointIndex: null,
      showPopover: false,
      category: null,
      dataProcesses: null, //Data API processes
      settingsBreadcrumbs: [
        {
          class: "fas fa-info-circle",
          tooltip: this.$t("ID_TASK_RISK_LEVEL1_INFO"),
          onClick() {},
        },
      ],
      optionsCategory: [],
      optionsProcesses: [],
      selectedProcesses: [],
      top: this.data[2] ? this.data[2].data.top : true,
      width: 0,
      totalCases: [],
      currentSelection: null,
      seriesBar: [
        {
          data: [],
        },
      ],
      options: {
        chart: {
          type: "bar",
          id: "LevelOneChart",
          toolbar: {
            show: true,
          },
          export: {
            csv: false
          },
          events: {
            click: function (event, chartContext, config) {
              that.$refs.popover.$emit("close");
              if (config.dataPointIndex != -1) {
                that.currentSelection = that.totalCases[config.dataPointIndex];
                that.onShowDrillDownOptions(
                  event.currentTarget,
                  config.dataPointIndex
                );
              }
            },
          },
        },
        plotOptions: {
          bar: {
            barHeight: "100%",
            distributed: true,
            horizontal: true,
          },
        },
        colors: ["#33b2df", "#546E7A", "#d4526e", "#13d8aa"],
        dataLabels: {
          enabled: false,
        },
        xaxis: {
          categories: [],
        },
        tooltip: {
          x: {
            show: false,
          },
          y: {
            title: {
              formatter: function () {
                return "";
              },
            },
          },
        },
      },
    };
  },
  created() {},
  mounted() {
    this.getBodyHeight();
    this.getCategories();
    this.getProcesses();
  },
  watch: {
    category(nvalue, old) {
      this.changeOption();
    },
    optionsCategory(nvalue, old) {
      this.category = this.data[2] ? this.data[2].data.category : nvalue[0];
    },
    optionsProcesses(nvalue, old) {
      this.selectedProcesses = this.data[2]
        ? this.data[2].data.selectedProcesses
        : _.flatMap(nvalue, (n) => n.key);
      this.changeOption();
    },
  },
  computed: {},
  updated() {},
  beforeCreate() {},
  methods: {
    /**
     * Return the height for Vue Card View body
     */
    getBodyHeight() {
      this.width = window.innerHeight;
    },
    /**
     * Get Categories form API
     */
    getCategories() {
      let that = this;
      Api.filters
        .categories()
        .then((response) => {
          that.formatDataCategories(response.data);
        })
        .catch((e) => {
          console.error(err);
        });
    },
    /**
     * Get Processes form API
     * @param {string} query - Text value in search popover
     */
    getProcesses(query) {
      let that = this;
      Api.filters
        .processListPaged({
          text: query || "",
          paged: false,
        })
        .then((response) => {
          that.formatDataProcesses(response.data);
        })
        .catch((e) => {
          console.error(err);
        });
    },
    /**
     * Format categories for multiselect
     * @param {*} data
     */
    formatDataCategories(data) {
      let array = [];
      array.push({
        name: this.$t("ID_ALL_CATEGORIES"),
        id: "all",
      });
      array.push({
        name: this.$t("ID_PROCESS_NONE_CATEGORY"),
        id: "0",
      });
      _.each(data, (el) => {
        array.push({ name: el["CATEGORY_NAME"], id: el["CATEGORY_ID"] });
      });
      this.optionsCategory = array;
    },
    /**
     * Format processes for popover
     * @param {*} data
     */
    formatDataProcesses(data) {
      let labels = [],
        array = [];
      _.each(data, (el) => {
        array.push({ value: el["PRO_TITLE"], key: el["PRO_ID"] });
        labels;
      });
      this.optionsProcesses = array;
      //Update the labels
      this.dataProcesses = data;
    },
    /**
     * Change the options in TOTAL CASES BY PROCESS
     * @param {*} option
     */
    changeOption() {
      let that = this,
        dt = {};
      if (this.category && this.selectedProcesses.length > 0 && this.data[1]) {
        dt = {
          category: this.category.id,
          caseList: this.data[1].id.toLowerCase(),
          processes: this.selectedProcesses,
          topTen: this.top,
        };
        this.category.id == "all" ? delete dt.category : null;
        Api.process
          .totalCasesByProcess(dt)
          .then((response) => {
            that.totalCases = response.data;
            that.formatTotalCases(response.data);
          })
          .catch((e) => {
            console.error(e);
          });
      }
    },
    /**
     * Show the processes popover
     */
    showProcessesPopover() {
      this.$root.$emit("bv::show::popover", "pm-task-process");
      this.$refs["pm-task-process"].setOptions(this.optionsProcesses);
      this.$refs["pm-task-process"].setSelectedOptions(this.selectedProcesses);
    },
    /**
     * Format response form BE to chart
     * @param {*} data
     */
    formatTotalCases(data) {
      let serie = [],
        labels = [];
      _.each(data, (el) => {
        serie.push(el["TOTAL"]);
        labels.push(el["PRO_TITLE"]);
      });

      this.$refs["LevelOneChart"].updateOptions({ labels: labels });
      this.$apexcharts.exec("LevelOneChart", "updateSeries", [
        {
          data: serie,
        },
      ]);
    },
    /**
     * Update list processes in chart
     * @param {*} data
     */
    onUpdateColumnSettings(data) {
      this.selectedProcesses = data;
      this.changeOption();
    },
    /**
     * Update labels in chart
     * @param {*} processes
     */
    updateLabels(processes) {
      let labels = [];
      _.each(processes, (el) => {
        labels.push(el["PRO_TITLE"]);
      });
      this.$refs["LevelOneChart"].updateOptions({ labels: labels });
    },
    /**
     * UPdate serie in chart
     * @param {*} processes
     */
    updateSerie(processes) {
      let labels = [];
      _.each(processes, (el) => {
        labels.push(el["TOTAL"]);
      });
      this.$refs["LevelOneChart"].updateOptions({ labels: labels });
    },
    /**
     * Force update view when update level
     */
    forceUpdateView() {
      this.changeOption();
    },
    /**
     * Event handler change input search popover
     * @param {string} query - value in popover search input
     */
    onChangeSearchPopover(query) {
      this.getProcesses(query);
    },
    /**
     * Show popover drill down options
     * @param {objHtml} target
     * @param {number} index
     */
    onShowDrillDownOptions(target, index) {
      let obj,
        dt,
        that = this;
      if (index != -1) {
        obj = jquery(target).find("path")[index];
        dt = this.dataProcesses[index];
        this.popoverTarget = obj.id;
        setTimeout(() => {
          that.$refs.popover.$emit("open");
        }, 200);
      }
    },
    /**
     * Show popover drill down options
     */
    onClickDrillDown() {
      this.$emit("updateDataLevel", {
        id: this.currentSelection["PRO_ID"],
        name: this.currentSelection["PRO_TITLE"],
        level: 2,
        data: {
          top: this.top,
          category: this.category,
          selectedProcesses: this.selectedProcesses,
        },
      });
    },
    /**
     * Show popover data options
     */
    onClickData() {
      let taskList = this.data[1].id.toLowerCase(),
        obj = {
          autoShow: false,
          fieldId: "processName",
          filterVar: "process",
          label: "",
          options: {
            label: this.currentSelection["PRO_TITLE"],
            value: this.currentSelection["PRO_ID"],
          },
          value: this.currentSelection["PRO_ID"],
        };
      eventBus.$emit("home::update-settings", {
        data: [obj],
        key: "filters",
        page: taskList,
        type: "normal",
      });
      eventBus.$emit("home::sidebar::click-item", taskList);
    },
    /**
     * Return the breadcrumbs
     */
    dataBreadcrumbs() {
      let res = [];
      if (this.data[1]) {
        res.push({
          label: this.data[1]["name"],
          onClick() {},
          color: this.data[1]["color"],
        });
      }
      return res;
    },
  },
};
</script>
<style>
.vp-task-metrics-label {
  display: inline-block;
}

.vp-width-p40 {
  width: 40%;
}

.vp-inline-block {
  display: inline-block;
}

.vp-padding-l20 {
  padding-left: 20px;
}

.vp-padding-r40 {
  padding-right: 40px;
}

.vp-right {
  float: right;
}

.vp-chart-minipopover {
  display: flex;
}

.vp-flex1 {
  flex: 1;
}

.v-search-info {
  font-size: 15px;
  color: darkgray;
  padding-left: 5%;
  padding-top: 10px;
  text-align: end;
}
.apexcharts-menu-item.exportCSV{
  display: none !important;
}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>