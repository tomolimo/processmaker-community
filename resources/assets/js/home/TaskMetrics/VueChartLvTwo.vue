<template>
  <div ref="v-pm-charts" class="v-pm-charts vp-inline-block">
    <div class="p-1 v-flex">
      <h6 class="v-search-title">
        {{ $t("ID_DRILL_DOWN_NUMBER_TASKS_PROCESS_BY_TASK") }}
      </h6>
      <div>
        <BreadCrumb
          :options="dataBreadcrumbs()"
          :settings="settingsBreadcrumbs"
        />
        <div class="vp-width-p30 vp-inline-block">
          <b-form-datepicker
            id="date-from"
            :date-format-options="{
              year: '2-digit',
              month: '2-digit',
              day: '2-digit',
            }"
            size="sm"
            :placeholder="$t('ID_DELEGATE_DATE_FROM')"
            v-model="dateFrom"
            @input="changeOption"
          ></b-form-datepicker>
        </div>
        <div class="vp-width-p30 vp-inline-block">
          <b-form-datepicker
            id="date-to"
            size="sm"
            :date-format-options="{
              year: '2-digit',
              month: '2-digit',
              day: '2-digit',
            }"
            :placeholder="$t('ID_DELEGATE_DATE_TO')"
            v-model="dateTo"
            :min="dateFrom"
            :state="stateDateTo"
            @input="changeOption"
          ></b-form-datepicker>
        </div>
        <div class="vp-inline-block">
          <b-form-radio-group
            id="btn-radios"
            v-model="period"
            :options="periodOptions"
            button-variant="outline-secondary"
            size="sm"
            name="radio-btn-outline"
            buttons
            @change="changeOption"
          ></b-form-radio-group>
        </div>
      </div>
      <apexchart
        ref="LevelTwoChart"
        :width="width"
        :options="options"
        :series="series"
      ></apexchart>
      <div class="vp-text-align-center">
        <div class="vp-align-right vp-inline-block">
          <button
            @click="onClickDrillDown()"
            type="button"
            class="btn btn-primary"
          >
            <i class="fas fa-chart-line"></i
            ><span class="vp-padding-l10">{{ $t("ID_DRILL") }}</span>
          </button>
        </div>
        <div class="vp-inline-block">
          <button @click="onClickData()" type="button" class="btn btn-primary">
            <i class="fas fa-th"></i
            ><span class="vp-padding-l10">{{ $t("ID_DATA") }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import Api from "../../api/index";
import Multiselect from "vue-multiselect";
import BreadCrumb from "../../components/utils/BreadCrumb.vue";
import moment from "moment";
import eventBus from "./../EventBus/eventBus";
import mixinLocales from "./mixinsLocales";
export default {
  name: "VueChartLvTwo",
  mixins: [mixinLocales],
  components: {
    Multiselect,
    BreadCrumb,
  },
  props: ["data"],
  data() {
    let that = this;
    return {
      dateFrom: this.data[3]
        ? this.data[3].data.dateFrom
        : moment().subtract(30, "d").format("YYYY-MM-DD"),
      dateTo: this.data[3]
        ? this.data[3].data.dateTo
        : moment().format("YYYY-MM-DD"),
      period: this.data[3] ? this.data[3].data.period : "day",
      periodOptions: [
        { text: this.$t("ID_DAY"), value: "day" },
        { text: this.$t("ID_MONTH"), value: "month" },
        { text: this.$t("ID_YEAR"), value: "year" },
      ],
      settingsBreadcrumbs: [
        {
          class: "fas fa-info-circle",
          tooltip: this.$t("ID_TASK_RISK_LEVEL2_INFO"),
          onClick() {},
        },
      ],
      dataCasesByRange: [],
      width: 0,
      options: {
        chart: {
          type: "area",
          zoom: {
            enabled: false,
          },
          id: "LevelTwoChart",
          events: {
            markerClick: function (event, chartContext, config) {},
          },
        },
        dataLabels: {
          enabled: false,
        },
        stroke: {
          curve: "smooth",
        },
        xaxis: {
          type: "datetime",
        },
        yaxis: {
          tickAmount: 7,
        },
        tooltip: {
          fixed: {
            enabled: false,
            position: "topRight",
          },
        },
      },
      series: [],
      stateDateTo: null,
    };
  },
  created() {},
  mounted() {
    this.getBodyHeight();
    this.changeOption();
  },
  watch: {
    dateFrom() {
      this.validateDateTo();
    },
    dateTo() {
      this.validateDateTo();
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
     * Change datepickers or radio button
     */
    changeOption() {
      let that = this,
        dt;
      if (this.data.length > 2) {
        if (this.dateFrom && this.dateTo && this.period) {
          dt = {
            processId: this.data[2].id,
            caseList: this.data[1].id.toLowerCase(),
            dateFrom: moment(this.dateFrom).format("YYYY-MM-DD"),
            dateTo: moment(this.dateTo).format("YYYY-MM-DD"),
            groupBy: this.period,
          };
          Api.process
            .totalCasesByRange(dt)
            .then((response) => {
              that.formatDataRange(response.data);
            })
            .catch((e) => {
              console.error(e);
            });
        }
      }
    },
    /**
     * Format response from API
     * @param {object} data
     */
    formatDataRange(data) {
      let labels = [],
        serie = [];

      this.dataCasesByRange = data;
      _.each(data, (el) => {
        serie.push(el["TOTAL"]);
        labels.push(el["dateGroup"]);
      });
      this.$refs["LevelTwoChart"].updateOptions({
        labels: labels,
        title: {
          text: this.data[1]["PRO_TITLE"],
          align: "left",
        },
      });
      this.$apexcharts.exec("LevelTwoChart", "updateSeries", [
        {
          name: this.data[1]["PRO_TITLE"],
          data: serie,
        },
      ]);
    },
    /**
     * Show popover drill down options
     */
    onClickDrillDown() {
      this.$emit("updateDataLevel", {
        id: "level2",
        name: this.data[2]["name"],
        level: 3,
        data: {
          dateFrom: this.dateFrom,
          dateTo: this.dateTo,
          period: this.period,
        },
      });
    },
    /**
     * Show popover data options
     */
    onClickData() {
      let taskList = this.data[1].id.toLowerCase(),
        obj = [
          {
            autoShow: false,
            fieldId: "processName",
            filterVar: "process",
            label: "",
            options: {
              label: this.data[2]["name"],
              value: this.data[2]["id"],
            },
            value: this.data[2]["id"],
          },
          {
            autoShow: false,
            fieldId: "delegationDate",
            filterVar: "delegateFrom",
            label: "",
            options: [],
            value: this.dateFrom,
          },
          {
            autoShow: false,
            fieldId: "delegationDate",
            filterVar: "delegateTo",
            label: "",
            options: [],
            value: this.dateTo,
          },
        ];
      eventBus.$emit("home::update-settings", {
        data: obj,
        key: "filters",
        page: taskList,
        type: "normal",
      });
      eventBus.$emit("home::sidebar::click-item", taskList);
    },
    /**
     * Validate range date
     */
    validateDateTo() {
      if (this.dateFrom !== "" && this.dateTo !== "") {
        if (this.dateFrom > this.dateTo) {
          this.stateDateTo = false;
        } else {
          this.stateDateTo = null;
        }
      }
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
      if (this.data[2]) {
        res.push({
          label: this.data[2]["name"],
          onClick() {},
          color: null,
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

.vp-width-p30 {
  width: 30%;
}

.vp-inline-block {
  display: inline-block;
}

.vp-padding-l20 {
  padding-left: 20px;
}

.vp-text-align-center {
  text-align: center;
}

.apexcharts-menu-item.exportCSV{
  display: none !important;
}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>