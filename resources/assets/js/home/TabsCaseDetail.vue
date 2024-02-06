<template>
  <div>
    <tabs 
      ref="tabs" 
      @changed="changed">
      <tab :name="$t('ID_STATUS')">
        <CaseStatus :data="dataCaseStatus"> </CaseStatus>
      </tab>
      <tab :name="$t('ID_CUSTOM_INFORMATION')">
        <MoreInformation :data="dataCase" v-if="currentTab == $t('ID_CUSTOM_INFORMATION')">
        </MoreInformation>
      </tab>
      <tab :name="$t('ID_PROCESS_MAP')">
        <ProcessMap :data="dataCase" v-if="currentTab == $t('ID_PROCESS_MAP')">
        </ProcessMap>
      </tab>
      <tab :name="$t('ID_CASE_HISTORY')">
        <CaseHistory :data="dataCase"> </CaseHistory>
      </tab>
      <tab :name="$t('ID_DYNAFORM_HISTORY')">
        <ChangeLog :caseData="dataCase"> </ChangeLog>
      </tab>
    </tabs>
  </div>
</template>

<script>
import Tabs from "./../components/tabs/Tabs.vue";
import Tab from "./../components/tabs/Tab.vue";
import CaseStatus from "./../components/home/caseDetail/CaseStatus.vue";
import ProcessMap from "./../components/home/caseDetail/ProcessMap.vue";
import CaseHistory from "./../components/home/caseDetail/CaseHistory.vue";
import ChangeLog from "./../components/home/caseDetail/ChangeLog.vue";
import MoreInformation from './../components/home/caseDetail/MoreInformation.vue';
import Api from "../api/index";

export default {
  name: "TabsCaseDetail",
  components: {
    Tabs,
    Tab,
    ProcessMap,
    CaseStatus,
    CaseHistory,
    ChangeLog,
    MoreInformation
  },
  props: {
    dataCase: Object,
    dataCaseStatus: Array
  },
  data() {
    return {
      currentTab: ""
    };
  },

  mounted() {},
  methods: {
    /**
     * When change the tab selection
     **/
    changed(selectedTab) {
      this.currentTab = selectedTab.tab.name;
    }
  }
};
</script>
<style>
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

.tabs-component-tab {
  color: #999;
  font-size: 14px;
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
</style>












