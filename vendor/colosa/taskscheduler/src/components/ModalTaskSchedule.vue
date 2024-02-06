<template>
  <div>
    <b-modal
      ref="modal"
      hide-backdrop
      content-class="shadow"
      size="xl"
      :title="TRANSLATIONS.ID_CUSTOM_SCHEDULE_SETTINGS"
      @hide="eventHideModal"
      hide-footer
    >
      <b-form @submit.stop.prevent="submit">
        <b-row>
          <b-col>
            <label for="timepicker-placeholder">{{
              TRANSLATIONS.ID_PERIODICITY
            }}</label>
            <b-form-select
              v-model="row.settings.periodicity.value"
              :placeholder="TRANSLATIONS.ID_CHOOSE_TIME"
              :options="optionsPeriodicity"
              @input="enableValidation(row)"
            ></b-form-select>
          </b-col>
          <b-col>
            <label for="timepicker-placeholder">
              <span style="color: white">*</span>
            </label>
            <BTimeMixin
              v-if="
                row.settings.periodicity.value == 'twicePerDay' ||
                row.settings.periodicity.value == 'oncePerDay'
              "
              :placeholder="TRANSLATIONS.ID_CHOOSE_TIME"
              local="en"
              reset-button
              reset-value="00:00:00"
              v-model="row.settings.periodicity.oncePerDay"
              ref="oncePerDay"
            ></BTimeMixin>
          </b-col>
          <b-col>
            <label for="timepicker-placeholder">
              <span style="color: white">*</span>
            </label>
            <BTimeMixin
              v-if="row.settings.periodicity.value == 'twicePerDay'"
              :placeholder="TRANSLATIONS.ID_CHOOSE_TIME"
              local="en"
              reset-button
              reset-value="00:00:00"
              v-model="row.settings.periodicity.twicePerDay"
              ref="twicePerDay"
            ></BTimeMixin>
            <b-form-invalid-feedback
              id="endingTime-feedback"
            ></b-form-invalid-feedback>
          </b-col>
        </b-row>
        <b-row class="row-padding">
          <b-col>
            <label for="timepicker-placeholder">{{
              TRANSLATIONS.ID_STARTING_TIME
            }}</label>
            <b-form-timepicker
              :placeholder="TRANSLATIONS.ID_CHOOSE_TIME"
              local="en"
              reset-button
              v-model="row.settings.startingTime"
              :disabled="
                row.settings.periodicity.value === 'twicePerDay' ||
                row.settings.periodicity.value === 'oncePerDay'
              "
              @input="enableValidation(row)"
              :state="validateState('startingTime')"
              aria-describedby="startingTime-feedback"
            ></b-form-timepicker>
            <b-form-invalid-feedback
              id="startingTime-feedback"
            ></b-form-invalid-feedback>
          </b-col>
          <b-col>
            <label for="timepicker-placeholder">{{
              TRANSLATIONS.ID_ENDING_TIME
            }}</label>
            <b-form-timepicker
              :placeholder="TRANSLATIONS.ID_CHOOSE_TIME"
              local="en"
              reset-button
              :disabled="
                row.settings.periodicity.value === 'twicePerDay' ||
                row.settings.periodicity.value === 'oncePerDay'
              "
              v-model="row.settings.endingTime"
              @input="enableValidation(row)"
              :state="validateState('endingTime')"
              aria-describedby="endingTime-feedback"
            ></b-form-timepicker>
            <b-form-invalid-feedback
              id="endingTime-feedback"
            ></b-form-invalid-feedback>
          </b-col>
          <b-col>
            <label for="timepicker-placeholder">{{
              TRANSLATIONS.ID_TIME_ZONE
            }}</label>
            <b-form-select
              :options="timeZone"
              v-model="row.settings.timezone"
              :disabled="
                !(
                  row.settings.startingTime ||
                  row.settings.endingTime ||
                  row.settings.periodicity.value === 'twicePerDay' ||
                  row.settings.periodicity.value === 'oncePerDay'
                )
              "
              :state="validateState('timezone')"
              aria-describedby="timezone-feedback"
            ></b-form-select>
            <b-form-invalid-feedback
              id="timezone-feedback"
            ></b-form-invalid-feedback>
          </b-col>
        </b-row>
        <b-row class="row-padding">
          <b-col cols="8">
            <label for="timepicker-placeholder">{{
              TRANSLATIONS.ID_REPEAT_EVERY
            }}</label>
            <b-row>
              <b-col>
                <b-form-input
                  type="number"
                  v-model="row.settings.everyOn"
                  min="1"
                  :formatter="formatRepeatEvery"
                  @change="changeRepeatUnit(row.settings.everyOn)"
                ></b-form-input>
              </b-col>
              <b-col>
                <b-form-select
                  v-model="row.settings.interval"
                  @change="changeRepeatOption(row.settings.everyOn)"
                  :options="optionsRepeatEvery"
                ></b-form-select>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <b-row class="row-padding" v-if="row.settings.everyOn != 'd'">
          <b-col>
            <label for="timepicker-placeholder">
              {{ TRANSLATIONS.ID_REPEAT_ON }}
              <span class="invalid">*</span>
            </label>
            <div>
              <b-form-checkbox-group
                class="options-days"
                v-model="row.settings.repeatOn"
                :options="optionsDays"
                buttons
                button-variant="default"
                size="sm"
                name="buttons-2"
                :state="validateState('repeatOn')"
                aria-describedby="repeatOn-feedback"
              ></b-form-checkbox-group>
              <div
                class="invalid"
                v-if="
                  $v.row && $v.row.settings && $v.row.settings.repeatOn.$error
                "
                id="repeatOn-feedback"
              >
                {{ TRANSLATIONS.ID_REQUIRED_FIELD }}
              </div>
            </div>
          </b-col>
        </b-row>
        <div class="row-padding">
          <b-button type="submit" class="float-right b-button b-success">{{
            TRANSLATIONS.ID_SAVE
          }}</b-button>
          <span class="float-right w-box40" />
          <b-button class="float-right b-button b-danger" @click="hide">{{
            TRANSLATIONS.ID_CANCEL
          }}</b-button>
        </div>
      </b-form>
    </b-modal>
  </div>
</template>

<script>
import axios from "axios";
import { xCron } from "../xCron";
import _ from "lodash";
import { validationMixin } from "vuelidate";

import BTimeMixin from "./BTimeExtend/BTimeMixin";
import { required } from "vuelidate/lib/validators";

export default {
  name: "GridTaskScheduler",
  props: {
    options: Array,
    optionsDays: Array,
    timeZone: Array,
    optionsRepeatEvery: Array,
    optionsPeriodicity: Array
  },
  components: {
    BTimeMixin
  },
  mixins: [validationMixin],
  data() {
    return {
      TRANSLATIONS: window.TRANSLATIONS,
      row: {
        settings: {
          periodicity: {
            unit: null,
            oncePerDay: "00:00:00",
            twicePerDay: "00:00:00"
          },
          startingTime: "",
          endingTime: "",
          timeZone: "1",
          repeatEvery: {
            unit: 1,
            repeat: "w"
          },
          repeatDays: [0, 1, 2, 3, 4, 5, 6]
        }
      }
    };
  },
  validations() {
    if (this.row.settings.startingTime || this.row.settings.endingTime) {
      return {
        row: {
          settings: {
            startingTime: {
              required
            },
            endingTime: {
              required
            },
            timezone: {
              required
            },
            repeatOn: {
              required
            }
          }
        }
      };
    } else if (
      this.row.settings.periodicity.value == "oncePerDay" ||
      this.row.settings.periodicity.value == "twicePerDay"
    ) {
      return {
        row: {
          settings: {
            repeatOn: {
              required
            },
            timezone: {
              required
            }
          }
        }
      };
    } else {
      return {
        row: {
          settings: {
            repeatOn: {
              required
            }
          }
        }
      };
    }
  },
  mounted() {},
  methods: {
    /**
     * Open the model settings
     */
    clickSettings: function (row) {
      this.$root.$emit("modalShow", row);
    },
    /**
     * Show the modal
     */
    show: function () {
      this.$refs["modal"].show();
    },
    /**
     * When change repeat unit property
     */
    changeRepeatUnit: function (val) {
      if (val.toString() == "1") {
        this.optionsRepeatEvery = this.$parent.optionsRepeatSingle;
      } else {
        this.optionsRepeatEvery = this.$parent.optionsRepeatPlural;
      }
    },
    /**
     * When change the repeat Options property
     */
    changeRepeatOption: function (val) {
      if (val.toString() == "d") {
        this.row.settings.repeatOn = [0, 1, 2, 3, 4, 5, 6];
      }
    },
    /**
     * Validate: starting time, ending time, time zone
     */
    enableValidation: function (row) {
      if (
        row.settings.periodicity.value === "oncePerDay" ||
        row.settings.periodicity.value === "twicePerDay"
      ) {
        row.settings.startingTime = "";
        row.settings.endingTime = "";
      }

      if (!row.timezone && !row.settings.timezone) {
        row.settings.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
      }
      if (
        row.settings.startingTime == "" &&
        row.settings.endingTime == "" &&
        row.settings.periodicity.value != "oncePerDay" &&
        row.settings.periodicity.value != "twicePerDay"
      ) {
        row.settings.timezone = null;
      }
    },
    /**
     * Save the record in task scheduler
     */
    save: function () {
      let cronExp = new xCron(),
        exp;
      //format repeatOn property
      this.row.settings.repeatOn = this.formatRepeatOn(
        this.row.settings.repeatOn
      );
      exp = cronExp.toExpression(this.row.settings);
      _.extend(this.row, exp);
      this.row.settings.description = cronExp.toDescription(this.row.settings);
      this.$parent.updateSettings(this.row);
      exp.id = this.row.id;
      axios({
        method: "post",
        url: window.server + `/api/1.0/${window.workspace}/scheduler`,
        data: exp,
        headers: {
          Authorization: `Bearer ` + window.credentials.accessToken
        }
      });
      this.$refs["modal"].hide();
    },
    /**
     * Submit the form
     */
    submit: function () {
      this.$v.row.settings.$touch();
      if (this.$v.row.settings.$anyError) {
        return;
      }
      this.save();
    },
    /**
     * Hide modal
     */
    hide: function () {
      this.$refs["modal"].hide();
    },
    /**
     * Capture event when hide the modal
     */
    eventHideModal: function () {
      let cronExpression = new xCron(),
        t = _.extend({}, this.row);
      t.settings = _.extend({}, cronExpression.toSettings(t));
      this.$parent.updateSettings(t);
    },
    /**
     * Validate: starting time, ending time, time zone and repeat on
     */
    validateState(name) {
      if (this.$v.row && this.$v.row.settings && this.$v.row.settings[name]) {
        const { $dirty, $error } = this.$v.row.settings[name];
        return $dirty ? !$error : null;
      }
      return null;
    },
    /**
     * Format the property repeatOn
     */
    formatRepeatOn(repeatOn) {
      let res = _.sortBy(repeatOn, (num) => {
        return num;
      });
      return res;
    },
    makeToast(variant = null, message) {
      this.$bvToast.toast(message, {
        variant: variant,
        solid: true
      });
    },
    /**
     * Format repeat Every property, max length = 3
     */
    formatRepeatEvery(e) {
      return String(e).substring(0, 3);
    }
  }
};
</script>
<style>
.settings-radio {
  font-size: 20px;
  padding-left: 10px;
}

.settings-cursive {
  font-style: italic;
  font-size: 14px;
}

.options-days .btn-default.btn-sm.active {
  background-color: #0062cc;
  color: white;
}

.btn {
  background-image: none !important;
}

.row-padding {
  padding-top: 10px;
}

.invalid {
  width: 100%;
  margin-top: 0.25rem;
  font-size: 80%;
  color: #dc3545;
}

.b-button {
  padding-left: 30px !important;
  padding-right: 30px !important;
}

.w-box40 {
  display: inline-block;
  width: 10px;
  height: 10px;
}

.b-success {
  background-color: #2cbc99 !important;
  border-color: #2cbc99 !important;
}

.b-danger {
  background-color: #e4655f !important;
  border-color: #e4655f !important;
}
</style>
