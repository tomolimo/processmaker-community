<template>
  <div>
    <v-client-table :data="data" :columns="columns" :options="options">
      <b-form-checkbox
        slot="enable"
        slot-scope="props"
        v-model="props.row.enable"
        name="check-button"
        switch
        class="b-switch"
        @change="toogleEnable(props.row)"
      />
      <div
        slot="service"
        slot-scope="props"
        v-b-tooltip.hover
        :title="props.row.description"
      >{{props.row.title}}</div>
      <div
        slot="schedule time"
        slot-scope="props"
        class="settings-cursive"
      >{{props.row.settings.description}}</div>
      <div slot="settings" slot-scope="props">
        <span
          v-if="verifyDefaultValue(props.row)"
        >{{TRANSLATIONS.ID_EMAIL_SERVER_DEFAULT}} {{TRANSLATIONS.ID_SETTINGS}}</span>
        <span v-else>{{TRANSLATIONS.ID_CUSTOM_SETTINGS}}</span>
        <span class="settings-radio" @click="clickSettings(props.row)">
          <b-icon-gear-fill />
        </span>
      </div>
    </v-client-table>
  </div>
</template>

<script>
import axios from "axios";
import _ from "lodash";
export default {
  name: "GridTaskScheduler",
  props: {
    data: Array,
    columns: Array
  },
  data() {
    return {
      TRANSLATIONS: window.TRANSLATIONS,
      options: {
          headings: {
              enable: window.TRANSLATIONS.ID_ENABLE,
              service: window.TRANSLATIONS.ID_SERVICE,
              "schedule time": window.TRANSLATIONS.ID_SCHEDULE_TIME || "Schedule time"
          },
          texts: {
              count: window.TRANSLATIONS.ID_SHOWING_FROM_RECORDS_COUNT,
              first: window.TRANSLATIONS.ID_FIRST,
              last: window.TRANSLATIONS.ID_LAST,
              filter: window.TRANSLATIONS.ID_FILTER + ":",
              limit: window.TRANSLATIONS.ID_RECORDS + ":",
              page: window.TRANSLATIONS.ID_PAGE + ":",
              noResults: window.TRANSLATIONS.ID_NO_MATCHING_RECORDS,
              filterPlaceholder: window.TRANSLATIONS.ID_EMPTY_SEARCH
          },
       }
    };
  },
  methods: {
    /**
     * Click in settings for show the modal
     */
    clickSettings: function(row) {
      this.$emit("modalShow", row);
    },
    /**
     * Toogle enable property
     */
    toogleEnable: function(row) {
      let dt = {
        id: row.id,
        enable: row.enable == true ? 0 : 1
      };
      axios({
        method: "post",
        url: window.server + `/api/1.0/${window.workspace}/scheduler`,
        data: dt,
        headers: {
          Authorization: `Bearer ` + window.credentials.accessToken
        }
      });
    },
    /**
     * Show the toast message
     */
    makeToast(variant = null, message) {
      this.$bvToast.toast(message, {
        title: "",
        variant: variant,
        solid: true
      });
    },
    /**
     * Verify if the record have default values
     */
    verifyDefaultValue(row) {
      let flag = true;
      _.map(row.default_value, (v, k) => {
        if (row[k] != v) {
          flag = false;
        }
      });
      return flag;
    }
  }
};
</script>
<style scoped>
.b-switch {
  text-align: center;
}
</style>
