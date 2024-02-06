<template>
  <div id="app">
    <div class="x-container">
      <GridTaskScheduler :data="dataScheduler" :columns="columns" @modalShow="modalShow"></GridTaskScheduler>
      <ModalTaskSchedule
        ref="modal"
        :options="options"
        :optionsPeriodicity="optionsPeriodicity"
        :optionsDays="optionsDays"
        :timeZone="timeZone"
        :optionsRepeatEvery="optionsRepeatSingle"
      ></ModalTaskSchedule>
    </div>
  </div>
</template>

<script>
import GridTaskScheduler from "./components/GridTaskScheduler.vue";
import ModalTaskSchedule from "./components/ModalTaskSchedule.vue";
import axios from "axios";
import _ from "lodash";
import { xCron } from "./xCron";

export default {
  name: "App",
  components: {
    GridTaskScheduler,
    ModalTaskSchedule
  },
  mounted() {
    let that = this,
      urlBase =
        window.server +
        `/api/1.0/${window.workspace}/scheduler` +
        (window.category ? "?category=" + window.category : "");
    axios
      .get(urlBase, {
        headers: {
          Authorization: `Bearer ` + window.credentials.accessToken
        }
      })
      .then((response) => {
        let sches = [],
          times = [];
        _.forEach(response.data, (task) => {
          let cronExpression = new xCron();
          task.settings = _.extend({}, cronExpression.toSettings(task));
          task.title = window.TRANSLATIONS[task.title] || task.title;
          task.description =
            window.TRANSLATIONS[task.description] || task.description;
          task.enable = task.enable == 1;
          sches.push(task);
        });

        _.forEach(window.timezoneArray, (task) => {
          times.push({
            value: task,
            text: task
          });
        });

        this.dataScheduler = sches;
        this.timeZone = times;
      })
      .catch(function(error) {
        if (error && error.message && error.code !== "ECONNABORTED" ) {
          that.$bvToast.toast(error.message || "", {
            title: "",
            variant: "danger",
            solid: true
          });
        }
      });
  },
  data() {
    return {
      columns: ["enable", "service", "schedule time", "settings"],
      dataScheduler: [],
      optionsDays: [
        { text: window.TRANSLATIONS["ID_WEEKDAY_ABB_1"], value: "1" },
        { text: window.TRANSLATIONS["ID_WEEKDAY_ABB_2"], value: "2" },
        { text: window.TRANSLATIONS["ID_WEEKDAY_ABB_3"], value: "3" },
        { text: window.TRANSLATIONS["ID_WEEKDAY_ABB_4"], value: "4" },
        { text: window.TRANSLATIONS["ID_WEEKDAY_ABB_5"], value: "5" },
        { text: window.TRANSLATIONS["ID_WEEKDAY_ABB_6"], value: "6" },
        { text: window.TRANSLATIONS["ID_WEEKDAY_ABB_0"], value: "0" }
      ],
      optionsRepeatSingle: [
        { text: window.TRANSLATIONS["ID_WEEK"], value: "week" },
        { text: window.TRANSLATIONS["ID_MONTH"], value: "month" },
        { text: window.TRANSLATIONS["ID_YEAR"], value: "year" }
      ],
      optionsRepeatPlural: [
        { text: window.TRANSLATIONS["ID_WEEKS"], value: "week" },
        { text: window.TRANSLATIONS["ID_MONTHS"], value: "month" },
        { text: window.TRANSLATIONS["ID_YEARS"], value: "year" }
      ],
      optionsPeriodicity: [
        { text: window.TRANSLATIONS["ID_EVERY_MINUTE"], value: "*/1 *" },
        { text: window.TRANSLATIONS["ID_EVERY_FIVE_MINUTES"], value: "*/5 *" },
        { text: window.TRANSLATIONS["ID_EVERY_TEN_MINUTES"], value: "*/10 *" },
        {
          text: window.TRANSLATIONS["ID_EVERY_FIFTEEN_MINUTES"],
          value: "*/15 *"
        },
        {
          text: window.TRANSLATIONS["ID_EVERY_THIRTY_MINUTES"],
          value: "*/30 *"
        },
        { text: window.TRANSLATIONS["ID_EVERY_HOUR"], value: "0 */1" },
        { text: window.TRANSLATIONS["ID_ONCE_PER_DAY"], value: "oncePerDay" },
        { text: window.TRANSLATIONS["ID_TWICE_PER_DAY"], value: "twicePerDay" }
      ],
      selected: []
    };
  },
  methods: {
    /**
     * Show the modal from grid vue
     */
    modalShow: function (row) {
      this.$refs["modal"].row = row;
      this.$refs["modal"].show();
      this.$refs["modal"].changeRepeatUnit(row.settings.everyOn);
      this.$refs["modal"].$v.$reset();
    },
    /**
     * Update properties in row
     */
    updateSettings: function (task) {
      let index = -1;
      for (let i = 0; i < this.dataScheduler.length; i += 1) {
        if (this.dataScheduler[i].id == task.id) {
          index = i;
        }
      }
      this.dataScheduler.splice(index, 1, task);
    }
  }
};
</script>

<style>
.x-container {
  padding: 40px;
}

.VueTables__search-field label {
  display: none !important;
}
</style>
