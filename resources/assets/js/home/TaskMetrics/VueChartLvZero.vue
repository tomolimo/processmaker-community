<template>
  <div ref="v-pm-charts" class="v-pm-charts vp-inline-block">
    <div class="p-1 v-flex">
      <h6 class="v-search-title">{{ $t("ID_DRILL_DOWN_NUMBER_TASKS") }}</h6>
      <BreadCrumb :options="dataBreadcrumbs" :settings="settingsBreadcrumbs" />
      <apexchart
        v-show="typeView === 'donut'"
        ref="apexchart1"
        :options="optionsDonut"
        :series="seriesDonut"
      ></apexchart>
      <apexchart
        v-show="typeView === 'bar'"
        ref="apexchart2"
        :options="optionsBar"
        :series="seriesBar"
      ></apexchart>
      <div class="row">
        <div class="col-sm vp-align-right">
          <button
            @click="changeView('donut')"
            type="button"
            class="btn btn-primary"
          >
            <i class="fas fa-chart-pie"></i
            ><span class="vp-padding-l10">{{ $t("ID_VIEW") }}</span>
          </button>
        </div>
        <div class="col-sm">
          <button
            @click="changeView('bar')"
            type="button"
            class="btn btn-primary"
          >
            <i class="fas fa-chart-bar"></i
            ><span class="vp-padding-l10">{{ $t("ID_VIEW") }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import Api from "../../api/index";
import BreadCrumb from "./../../components/utils/BreadCrumb.vue";
export default {
  name: "VueChartLvZero",
  mixins: [],
  components: { BreadCrumb },
  props: [],
  data() {
    let that = this;
    return {
      typeView: "donut",
      width: 0,
      data: [],
      currentSelection: null,
      seriesDonut: [],
      settingsBreadcrumbs: [
        {
          class: "fas fa-info-circle",
          tooltip: this.$t("ID_TASK_RISK_LEVEL0_INFO"),
          onClick() {},
        },
      ],
      dataBreadcrumbs: [
        {
          label: that.$i18n.t("ID_INBOX"),
          onClick() {
            that.$emit("updateDataLevel", {
              id: "inbox",
              name: that.$i18n.t("ID_INBOX"),
              level: 1,
              color: "#179a6e",
            });
          },
          color: "#179a6e",
        },
        {
          label: this.$i18n.t("ID_DRAFT"),
          onClick() {
            that.$emit("updateDataLevel", {
              id: "draft",
              name: that.$i18n.t("ID_DRAFT"),
              level: 1,
              color: "#feb019",
            });
          },
          color: "#feb019",
        },
        {
          label: this.$i18n.t("ID_PAUSED"),
          onClick() {
            that.$emit("updateDataLevel", {
              id:"paused",
              name: that.$i18n.t("ID_PAUSED"),
              level: 1,
              color: "#008ffb",
            });
          },
          color: "#008ffb",
        },
        {
          label: this.$i18n.t("ID_UNASSIGNED"),
          onClick() {
            that.$emit("updateDataLevel", {
              id: "unassigned",
              name: that.$i18n.t("ID_UNASSIGNED"),
              level: 1,
              color: "#8f99a0",
            });
          },

          color: "#8f99a0",
        },
      ],
      optionsDonut: {
        labels: [
          this.$i18n.t("ID_INBOX"),
          this.$i18n.t("ID_DRAFT"),
          this.$i18n.t("ID_PAUSED"),
          this.$i18n.t("ID_UNASSIGNED"),
        ],
        chart: {
          id: "apexchart1",
          type: "donut",
          events: {},
        },
        legend: {
          show: false,
          position: "top",
          offsetY: 0,
        },
      },
      seriesBar: [
        {
          data: [400, 430, 448, 470],
        },
      ],
      optionsBar: {
        chart: {
          type: "bar",
          id: "apexchart2",
          toolbar: {
            show: false,
          },
          events: {
            legendClick: function (chartContext, seriesIndex, config) {
              that.currentSelection = that.data[seriesIndex];
              that.$emit("updateDataLevel", {
                id: that.currentSelection["List Name"],
                name: that.currentSelection["List Name"],
                level: 0,
                data: that.currentSelection,
              });
            },
          },
        },
        plotOptions: {
          bar: {
            barHeight: "100%",
            distributed: true,
          },
        },
        legend: {
          show: false,
          position: "top",
          offsetY: 0,
        },
        colors: ["#33b2df", "#546E7A", "#d4526e", "#13d8aa"],
        dataLabels: {
          enabled: false,
        },
        xaxis: {
          categories: [
            this.$i18n.t("ID_INBOX"),
            this.$i18n.t("ID_DRAFT"),
            this.$i18n.t("ID_PAUSED"),
            this.$i18n.t("ID_UNASSIGNED"),
          ],
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
    this.getData();
  },
  watch: {},
  computed: {},
  updated() {},
  beforeCreate() {},
  methods: {
    /**
     * Return the height for Vue Card View body
     */
    getBodyHeight() {
      this.width = window.innerHeight * 0.8;
    },
    /**
     * Change view - donut/bar
     */
    changeView(view) {
      this.typeView = view;
      this.getData();
    },
    /**
     * Get data from rest API
     */
    getData() {
      let that = this;
      Api.cases
        .listTotalCases()
        .then((response) => {
          that.formatData(response.data);
        })
        .catch((response) => {});
    },
    /**
     * Format the data for chart
     */
    formatData(data) {
      let l = [],
        c = [],
        s = [];
      _.each(data, (el) => {
        l.push(el["List Name"]);
        s.push(el["Total"]);
        if (el["Color"] == "green") {
          c.push("#179a6e");
        }
        if (el["Color"] == "yellow") {
          c.push("#feb019");
        }
        if (el["Color"] == "blue") {
          c.push("#008ffb");
        }
        if (el["Color"] == "gray") {
          c.push("#8f99a0");
        }
      });
      this.data = data;
      this.seriesDonut = s;
      this.seriesBar = [
        {
          data: s,
        },
      ];
      this.$refs["apexchart1"].updateOptions({ labels: l, colors: c });
      this.$refs["apexchart2"].updateOptions({ labels: l, colors: c });
      this.$apexcharts.exec("apexchart1", "updateSeries", s);
      this.$apexcharts.exec("apexchart2", "updateSeries", [
        {
          data: s,
        },
      ]);
    },
    /**
     * Format color for show in breadcrumb
     * @param {string} color
     * @returns {string}
     */
    formatColor(color) {
      let code = "#ffffff";
      switch (color) {
        case "green":
          code = "#179a6e";
          break;
        case "yellow":
          code = "#feb019";
          break;
        case "blue":
          code = "#008ffb";
          break;
        case "gray":
          code = "#8f99a0";
          break;
      }
      return code;
    },
  },
};
</script>
<style>
.vp-center {
  text-align: center;
}

.vp-align-right {
  text-align: right;
}

.vp-padding-l10 {
  padding-left: 10px;
}
</style>