<template>
  <div class="debugger-container">
    <tabs>
      <tab name="Variables">
        <div
          class="btn-toolbar justify-content-between"
          role="toolbar"
          aria-label="Toolbar with button groups"
        >
          <b-form-radio-group
            @change="changeOption"
            v-model="optionsDebugVars.selected"
            :options="optionsDebugVars.options"
            button-variant="outline-secondary"
            name="radio-btn-outline"
            size="sm"
            buttons
          ></b-form-radio-group>
        </div>
        <div style="padding-top: 10px">
          <v-client-table
            :data="dataTable"
            :columns="columns"
            :options="options"
            ref="vueTable"
          />
        </div>
      </tab>
      <tab name="Triggers">
        <div>
          <v-client-table
            :data="dataTableTriggers"
            :columns="columnsTriggers"
            :options="optionsTriggers"
            ref="vueTableTriggers"
          >
          <span 
            slot="code"
            v-html="props.row.code"
            slot-scope="props">{{props.row.code}}</span>
          </v-client-table>
        </div>
      </tab>
    </tabs>
  </div>
</template>

<script>
import Tabs from "../../../components/tabs/Tabs.vue";
import Tab from "../../../components/tabs/Tab.vue";
import api from "../../../api/index";
export default {
  name: "ButtonFleft",
  props: {
    data: Object
  },
  components: {
    Tabs,
    Tab
  },
  data() {
    return {
      debugFullPage: false,
      debugTabs: [],
      activetab: 1,
      variableTabs: [],
      debugSearch: "",
      isRTL: false,
      dataTable: [],
      dataTableTriggers: [],
      columns: ["key", "value"],
      columnsTriggers: ["name", "execution","code"],
      options: {
        perPage: 200,
        filterable: true,
        pagination: {
          show: false
        },
        headings: {
          key: this.$i18n.t("ID_NAME"),
          value: this.$i18n.t("ID_FIELD_DYNAFORM_TEXT")
        }
      },
      optionsTriggers: {
        perPage: 200,
        filterable: true,
        pagination: {
          show: false
        },
        headings: {
          name: this.$i18n.t("ID_NAME"),
          execution: this.$i18n.t("ID_EXECUTION"),
          code: this.$i18n.t("ID_CAPTCHA_CODE"),
        }
      },
      optionsDebugVars: {
        selected: "all",
        options: [
          { text: this.$i18n.t("ID_OPT_ALL"), value: "all" },
          { text: this.$i18n.t("ID_DYNAFORM"), value: "dyn" },
          { text: this.$i18n.t("ID_SYSTEM"), value: "sys" }
        ]
      }
    };
  },
  mounted() {
    this.loadData();
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    showDebugger() {
      this.$refs["modal-debugger"].show();
    },
    /**
     * Load the data for debugger
     */
    loadData() {
      this.getDebugVars({ filter: "all" });
      this.getDebugVarsTriggers();
    },
    /**
     * Get debug variables
     */
    getDebugVars(data) {
      let that = this,
        dt = [];
      api.cases.debugVars(data).then((response) => {
        _.forIn(response.data.data[0], function (value, key) {
          dt.push({
            key,
            value
          });
        });
        this.dataTable = dt;
      });
    },
    /**
     * Get trigger variables
     */
    getDebugVarsTriggers(data) {
      let that = this,
        dt = [];
      api.cases.debugVarsTriggers(data).then((response) => {
        if (response.data.data.length > 0) {
          _.each(response.data.data, function (o) {
            dt.push({
              name: o.name,
              execution: o.execution_time,
              code: o.code
            });
          });
          this.dataTableTriggers = dt;
        }
      });
    },
    /**
     * Change Radio option [All, Dynaform, System]
     */
    changeOption(opt) {
      this.getDebugVars({ filter: opt });
    }
  }
};
</script>

<style>
.debugger-container {
    overflow-x: hidden;
    overflow-y: auto;
  max-width: 25%;
  min-width: 25%;
  padding: 0.1rem;
  margin-right: 0;
  margin-left: 0;
  border-width: 1px;
  border-top-left-radius: 0.1rem;
  border-top-right-radius: 0.1rem;
}

.tabs-component {
  margin: 0 0;
}

.tabs-component-tabs {
  border: solid 1px #ddd;
  border-radius: 6px;
  margin-bottom: 5px;
}

@media (min-width: 700px) {
  .tabs-component-tabs {
    border: 0;
    align-items: stretch;
    display: flex;
    justify-content: flex-start;
    margin-bottom: -1px;
  }
}

.debugger-container .tabs-component-tab {
  color: #999;
  font-size: 0.6rem;
  font-weight: 600;
  margin-right: 0;
  list-style: none;
}

.tabs-component-tab:not(:last-child) {
  border-bottom: dotted 1px #ddd;
}

.tabs-component-tab:hover {
  color: #666;
}

.tabs-component-tab.is-active {
  color: #000;
}

.tabs-component-tab.is-disabled * {
  color: #cdcdcd;
  cursor: not-allowed !important;
}

@media (min-width: 700px) {
  .tabs-component-tab {
    background-color: #fff;
    border: solid 1px #ddd;
    border-radius: 3px 3px 0 0;
    margin-right: 0.5em;
    transform: translateY(2px);
    transition: transform 0.3s ease;
  }

  .tabs-component-tab.is-active {
    border-bottom: solid 1px #fff;
    z-index: 2;
    transform: translateY(0);
  }
}

.tabs-component-tab-a {
  align-items: center;
  color: inherit;
  display: flex;
  padding: 0.75em 1em;
  text-decoration: none;
}

.tabs-component-panels {
  padding: 4em 0;
}

@media (min-width: 700px) {
  .tabs-component-panels {
    border-top-left-radius: 0;
    background-color: #fff;
    border: solid 1px #ddd;
    border-radius: 0 6px 6px 6px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    padding: 0.5em 0.5em;
  }
}

.btn-group > input[type="checkbox"],
input[type="radio"] {
  box-sizing: border-box;
  padding: 0;
  display: none;
}

.btn-outline-secondary-active {
  color: #fff;
  background-color: #6c757d;
  border-color: #6c757d;
}

.VueTables__search-field > label {
  display: none;
}

.VueTables.VueTables--client .row {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
  margin-right: 0px;
  margin-left: 0px;
}

.debugger-container .VueTables.VueTables--client > * {
  font-size: 10px;
}

.debugger-container .VueTables.VueTables--client .table td,
.table th {
  padding: 0.3rem;
  vertical-align: top;
  border-top: 1px solid #dee2e6;
}

.debugger-container .form-control {
  display: block;
  width: 100%;
  height: 30px;
  padding: 0.5rem 0.5rem;
  font-size: 0.6rem;
  font-weight: 400;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.debugger-container .col-md-12 {
  position: relative;
  width: 100%;
  padding-right: 0;
  padding-left: 0;
}

.debugger-container .btn-group-sm>.btn, .btn-sm {
    padding: .25rem .5rem;
    font-size: .6rem;
    line-height: 1.5;
    border-radius: .2rem;
}

.debugger-container .php{
    font-family: Consolas, monospace;
    color: #000;
    margin-bottom: 0px;
    margin-top: 0px;
    background: #fff;
     border-radius: 0px; 
     padding: 0px; 
     line-height: 1.5; 
    overflow: auto;
}
</style>