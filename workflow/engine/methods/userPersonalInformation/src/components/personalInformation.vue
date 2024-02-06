<template>
    <div>
        <titleSection :title="$root.translation('ID_PERSONAL_INFORMATION')"></titleSection>
        <b-form @submit.stop.prevent="onsubmit">
            <b-container fluid>
                <b-row>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_FIRSTNAME')+' (*)'">
                            <b-form-input v-model="form.USR_FIRSTNAME"
                                          autocomplete="off"
                                          :state="validate.USR_FIRSTNAME.state"
                                          @keyup="validateFirstName"
                                          :disabled="disabledField.USR_FIRSTNAME"></b-form-input>
                            <b-form-invalid-feedback>{{validate.USR_FIRSTNAME.message}}</b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                    <b-col cols="2">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_LASTNAME')+' (*)'">
                            <b-form-input v-model="form.USR_LASTNAME"
                                          autocomplete="off"
                                          :state="validate.USR_LASTNAME.state"
                                          @keyup="validateLastName"
                                          :disabled="disabledField.USR_LASTNAME"></b-form-input>
                            <b-form-invalid-feedback>{{validate.USR_LASTNAME.message}}</b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                    <b-col cols="1">
                    </b-col>
                    <b-col cols="3">
                        <b-avatar rounded 
                                  ref="userAvatar"
                                  size="5rem" 
                                  button
                                  @click="avatarClick"
                                  badge-variant="light"
                                  :src="urlImage"
                                  :disabled="disabledField.USR_PHOTO">
                        </b-avatar>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_ADDRESS')">
                            <b-form-input v-model="form.USR_ADDRESS"
                                          autocomplete="off"
                                          :disabled="disabledField.USR_ADDRESS"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="2">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_ZIP_CODE')">
                            <b-form-input v-model="form.USR_ZIP_CODE"
                                          autocomplete="off"
                                          :disabled="disabledField.USR_ZIP_CODE"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="1">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_USERNAME')+' (*)'">
                            <b-form-input v-model="form.USR_USERNAME"
                                          autocomplete="off"
                                          :state="validate.USR_USERNAME.state"
                                          @keyup="validateUserName"
                                          :disabled="disabledField.USR_USERNAME"></b-form-input>
                            <b-form-valid-feedback><span v-html="validate.USR_USERNAME.message"></span></b-form-valid-feedback>
                            <b-form-invalid-feedback><span v-html="validate.USR_USERNAME.message"></span></b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_COUNTRY')">
                            <b-form-select v-model="form.USR_COUNTRY"
                                           :options="countryList"
                                           @change="getStateList"
                                           :disabled="disabledField.USR_COUNTRY"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="2">
                        <b-form-group :label="$root.translation('ID_STATE_REGION')">
                            <b-form-select v-model="form.USR_CITY"
                                           :options="stateList"
                                           @change="getLocationList"
                                           :disabled="disabledField.USR_CITY"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_CITY')">
                            <b-form-select v-model="form.USR_LOCATION"
                                           :options="locationList"
                                           :disabled="disabledField.USR_LOCATION"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="1">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_EMAIL')+' (*)'">
                            <b-form-input v-model="form.USR_EMAIL"
                                          autocomplete="off"
                                          :state="validate.USR_EMAIL.state"
                                          @keyup="validateEmail"
                                          :disabled="disabledField.USR_EMAIL"></b-form-input>
                            <b-form-invalid-feedback>{{validate.USR_EMAIL.message}}</b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_PHONE')">
                            <b-form-input v-model="form.USR_PHONE"
                                          autocomplete="off"
                                          :disabled="disabledField.USR_PHONE"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="2">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_POSITION')">
                            <b-form-input v-model="form.USR_POSITION"
                                          autocomplete="off"
                                          :disabled="disabledField.USR_POSITION"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="1">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_STATUS')">
                            <b-form-select v-model="form.USR_STATUS"
                                           :options="userStatus"
                                           :disabled="disabledField.USR_STATUS"/>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="5">
                        <b-form-group :label="$root.translation('ID_REPLACED_BY')">
                            <b-input-group>
                                <b-input-group-prepend class="w-25">
                                    <b-form-input v-model="filterUser"
                                                  autocomplete="off"
                                                  @keyup="getUsersList"
                                                  placeholder="search"
                                                  :disabled="disabled"></b-form-input>
                                </b-input-group-prepend>
                                <b-form-select v-model="form.USR_REPLACED_BY"
                                               :options="usersList"
                                               :disabled="disabledField.USR_REPLACED_BY"></b-form-select>
                            </b-input-group>
                        </b-form-group>
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_CALENDAR')">
                            <b-form-select v-model="form.USR_CALENDAR"
                                           :options="availableCalendars"
                                           :disabled="disabledField.USR_CALENDAR"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="1">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_EXPIRATION_DATE')">
                            <b-form-datepicker v-model="form.USR_DUE_DATE"
                                               :date-format-options="{year:'numeric',month:'numeric',day:'numeric'}"
                                               :disabled="disabledField.USR_DUE_DATE"/>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_TIME_ZONE')">
                            <b-form-select v-model="form.USR_TIME_ZONE"
                                           :options="timeZoneList"
                                           :disabled="disabledField.USR_TIME_ZONE"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="2">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_DEFAULT_LANGUAGE')">
                            <b-form-select v-model="form.USR_DEFAULT_LANG"
                                           :options="languagesList"
                                           :disabled="disabledField.USR_DEFAULT_LANG"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="1">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_ROLE')">
                            <b-form-select v-model="form.USR_ROLE"
                                           :options="rolesList"
                                           @change="changeRole"
                                           :disabled="disabledField.USR_ROLE"/>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_DEFAULT_MAIN_MENU_OPTION')">
                            <b-form-select v-model="form.PREF_DEFAULT_MENUSELECTED"
                                           :options="defaultMainMenuOptionList"
                                           :disabled="disabledField.PREF_DEFAULT_MENUSELECTED"
                                           @change="changeDefaultMainMenuOption"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="2">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_DEFAULT_CASES_MENU_OPTION')">
                            <b-form-select v-model="form.PREF_DEFAULT_CASES_MENUSELECTED"
                                           :options="defaultCasesMenuOptionList"
                                           :disabled="disabledField.PREF_DEFAULT_CASES_MENUSELECTED || switchChangeDefaultMainMenuOption"/>
                        </b-form-group>
                    </b-col>
                    <b-col cols="1">
                    </b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_NEW_PASSWORD')+' (*)'"
                                      :class="classCustom">
                            <b-form-input v-model="form.USR_NEW_PASS"
                                          autocomplete="off"
                                          :state="validate.USR_NEW_PASS.state"
                                          type="password"
                                          @keyup="validatePassword"
                                          @change="editing=false;"
                                          :disabled="disabledField.USR_NEW_PASS"></b-form-input>
                            <b-form-valid-feedback><span v-html="validate.USR_NEW_PASS.message"></span></b-form-valid-feedback>
                            <b-form-invalid-feedback><span v-html="validate.USR_NEW_PASS.message"></span></b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="3"></b-col>
                    <b-col cols="2"></b-col>
                    <b-col cols="3"></b-col>
                    <b-col cols="1"></b-col>
                    <b-col cols="3">
                        <b-form-group :label="$root.translation('ID_CONFIRM_PASSWORD')+' (*)'"
                                      :class="classCustom">
                            <b-form-input v-model="form.USR_CNF_PASS"
                                          autocomplete="off"
                                          :state="validate.USR_CNF_PASS.state"
                                          type="password"
                                          @keyup="validateConfirmationPassword"
                                          @change="editing=false;"
                                          :disabled="disabledField.USR_CNF_PASS"></b-form-input>
                            <b-form-invalid-feedback><span v-html="validate.USR_CNF_PASS.message"></span></b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="8">
                        <fieldset class="border pt-1 pl-3 pb-3 pr-3">
                            <legend style="width:auto;">{{$root.translation('ID_EXTENDED_ATTRIBUTES')}}</legend>
                            <b-container class="mb-3">
                                <b-row>
                                    <b-col>
                                        <div v-for="userExtendedAttribute in userExtendedAttributesList" 
                                             :key="userExtendedAttribute.id">
                                            <b-form-group :label="userExtendedAttribute.name+(userExtendedAttribute.required===1?' (*)':'')"
                                                          v-if="userExtendedAttribute.hidden===0?true:false">
                                                <b-form-input v-model="form.USR_EXTENDED_ATTRIBUTES_DATA[userExtendedAttribute.attributeId]"
                                                              autocomplete="off"
                                                              :type="userExtendedAttribute.password===1?'password':'text'"
                                                              :state="validateExtendedAttribute(userExtendedAttribute)"
                                                              :disabled="disabled"></b-form-input>
                                                <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                                            </b-form-group>
                                        </div>
                                    </b-col>
                                </b-row>
                            </b-container>
                        </fieldset>
                    </b-col>
                    <b-col cols="4">
                        <fieldset class="border pt-1 pl-3 pb-3 pr-3">
                            <legend style="width:auto;">{{$root.translation('ID_COSTS')}}</legend>
                            <b-form-group :label="$root.translation('ID_COST_BY_HOUR')">
                                <b-form-input v-model="form.USR_COST_BY_HOUR"
                                              autocomplete="off"
                                              :state="validate.USR_COST_BY_HOUR.state"
                                              @keyup="validateCostByHour"
                                              :disabled="disabledField.USR_COST_BY_HOUR"></b-form-input>
                                <b-form-invalid-feedback>{{validate.USR_COST_BY_HOUR.message}}</b-form-invalid-feedback>
                            </b-form-group>
                            <b-form-group :label="$root.translation('ID_UNITS')">
                                <b-form-input v-model="form.USR_UNIT_COST"
                                              autocomplete="off"
                                              :disabled="disabledField.USR_UNIT_COST"/>
                            </b-form-group>
                        </fieldset>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="12">
                        <b-form-group class="mt-4">
                            <b-form-checkbox v-model="form.USR_LOGGED_NEXT_TIME"
                                             value="1"
                                             unchecked-value="0"
                                             :disabled="disabledField.USR_LOGGED_NEXT_TIME">
                                {{$root.translation('ID_USER_MUST_CHANGE_PASSWORD_AT_NEXT_LOGON')}}
                            </b-form-checkbox>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row :class="classCustom">
                    <b-col cols="12">
                        <b-form-group class="mt-4 float-right">
                            <b-button variant="danger"
                                      @click="cancel">{{$root.translation('ID_CANCEL')}}</b-button>&nbsp;
                            <b-button type="submit" 
                                      variant="success"
                                      :disabled="disableButtonSave">{{$root.translation('ID_SAVE')}}</b-button>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row :class="classCustom2">
                    <b-col cols="12">
                        <b-form-group class="mt-4 float-right">
                            <b-button variant="success"
                                      @click="editPersonalInformation">{{$root.translation('ID_EDIT')}}</b-button>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
        </b-form>
    </div>
</template>

<script>
    import titleSection from "./titleSection.vue"
    import axios from "axios"
    export default {
        components: {
            titleSection
        },
        data() {
            return {
                form: {
                    USR_UID: "",
                    USR_FIRSTNAME: "",
                    USR_LASTNAME: "",
                    USR_ADDRESS: "",
                    USR_ZIP_CODE: "",
                    USR_COUNTRY: "",
                    USR_CITY: "",
                    USR_LOCATION: "",
                    USR_USERNAME: "",
                    USR_PHONE: "",
                    USR_POSITION: "",
                    USR_EMAIL: "",
                    USR_REPLACED_BY: "",
                    USR_CALENDAR: "",
                    USR_STATUS: "ACTIVE",
                    USR_TIME_ZONE: "",
                    USR_DEFAULT_LANG: "",
                    USR_DUE_DATE: this.getDefaultDueDate(),
                    PREF_DEFAULT_MENUSELECTED: "PM_SETUP",
                    PREF_DEFAULT_CASES_MENUSELECTED: "",
                    USR_ROLE: "PROCESSMAKER_OPERATOR",
                    USR_COST_BY_HOUR: "0",
                    USR_UNIT_COST: "$",
                    USR_NEW_PASS: "",
                    USR_CNF_PASS: "",
                    USR_LOGGED_NEXT_TIME: "0",
                    USR_EXTENDED_ATTRIBUTES_DATA: [],
                    USR_PHOTO: ""
                },
                validate: {
                    USR_FIRSTNAME: {
                        message: "",
                        state: null
                    },
                    USR_LASTNAME: {
                        message: "",
                        state: null
                    },
                    USR_USERNAME: {
                        message: "",
                        state: null
                    },
                    USR_EMAIL: {
                        message: "",
                        state: null
                    },
                    USR_NEW_PASS: {
                        message: "",
                        state: null
                    },
                    USR_CNF_PASS: {
                        message: "",
                        state: null
                    },
                    USR_COST_BY_HOUR: {
                        message: "",
                        state: null
                    }
                },
                countryList: [{
                        value: "",
                        text: this.$root.translation('ID_SELECT')
                    }],
                stateList: [{
                        value: "",
                        text: this.$root.translation('ID_SELECT')
                    }],
                locationList: [{
                        value: "",
                        text: this.$root.translation('ID_SELECT')
                    }],
                usersList: [],
                filterUser: "",
                availableCalendars: [],
                userStatus: [
                    {value: "ACTIVE", text: this.$root.translation("ID_ACTIVE")},
                    {value: "INACTIVE", text: this.$root.translation("ID_INACTIVE")},
                    {value: "VACATION", text: this.$root.translation("ID_VACATION")}
                ],
                timeZoneList: [],
                languagesList: [],
                defaultMainMenuOptionList: [],
                defaultCasesMenuOptionList: [],
                rolesList: [],
                userExtendedAttributesList: [],
                disableButtonSave: false,
                editing: false,
                urlImage: "",
                disabled: false,
                disabledField: {
                    USR_FIRSTNAME: false,
                    USR_LASTNAME: false,
                    USR_ADDRESS: false,
                    USR_ZIP_CODE: false,
                    USR_COUNTRY: false,
                    USR_CITY: false,
                    USR_LOCATION: false,
                    USR_USERNAME: false,
                    USR_PHONE: false,
                    USR_POSITION: false,
                    USR_EMAIL: false,
                    USR_REPLACED_BY: false,
                    USR_CALENDAR: false,
                    USR_STATUS: false,
                    USR_TIME_ZONE: false,
                    USR_DEFAULT_LANG: false,
                    USR_DUE_DATE: false,
                    PREF_DEFAULT_MENUSELECTED: false,
                    PREF_DEFAULT_CASES_MENUSELECTED: false,
                    USR_ROLE: false,
                    USR_COST_BY_HOUR: false,
                    USR_UNIT_COST: false,
                    USR_NEW_PASS: false,
                    USR_CNF_PASS: false,
                    USR_LOGGED_NEXT_TIME: false,
                    USR_PHOTO: false
                },
                permission: {},
                classCustom: "",
                classCustom2: "",
                switchChangeDefaultMainMenuOption: true,
                memoryChangeDefaultMainMenuOption: ""
            };
        },
        mounted() {
            this.$nextTick(function () {
                let promise = null;
                this.getTimeZoneList();
                if ("USR_UID" in window && window.USR_UID !== "") {
                    this.editing = true;
                    promise = this.load();
                    promise.then(() => {
                        this.loadServices();
                    });
                } else {
                    this.editing = false;
                    this.loadServices();
                }
                //additional modes
                if (this.$root.modeOfForm() === 1) {
                    this.disabled = false;
                    for (let i in this.disabledField) {
                        this.disabledField[i] = false;
                    }
                    this.classCustom = "";
                    this.classCustom2 = "sr-only sr-only-focusable";
                }
                if (this.$root.modeOfForm() === 2) {
                    this.disabled = true;
                    for (let i in this.disabledField) {
                        this.disabledField[i] = true;
                    }
                    this.classCustom = "sr-only sr-only-focusable";
                    this.classCustom2 = "";
                }
                if (this.$root.canEdit() === false) {
                    this.classCustom2 = "sr-only sr-only-focusable";
                }
            });
        },
        methods: {
            editPersonalInformation() {
                this.classCustom = "";
                this.classCustom2 = "sr-only sr-only-focusable";
                this.disabled = false;
                for (let i in this.disabledField) {
                    this.disabledField[i] = false;
                }
                for (let i in this.permission) {
                    if (i in this.disabledField) {
                        this.disabledField[i] = true;
                    }
                }
            },
            cancel() {
                if (this.$root.modeOfForm() === 1) {
                    window.location = this.$root.baseUrl() + "users/users_List";
                }
                if (this.$root.modeOfForm() === 2) {
                    this.classCustom = "sr-only sr-only-focusable";
                    this.classCustom2 = "";
                    this.disabled = true;
                    for (let i in this.disabledField) {
                        this.disabledField[i] = true;
                    }
                    for (let i in this.validate) {
                        this.validate[i].state = null;
                    }
                }
            },
            onsubmit() {
                this.validateFirstName();
                this.validateLastName();
                let promise = this.validateUserName();
                promise.then(response => {
                    response;
                    this.validateEmail();
                    let promise2 = this.validatePassword();
                    promise2.then(response2 => {
                        response2;
                        this.validateConfirmationPassword();
                        this.validateCostByHour();
                        if (this.validate.USR_FIRSTNAME.state === false ||
                                this.validate.USR_LASTNAME.state === false ||
                                this.validate.USR_USERNAME.state === false ||
                                this.validate.USR_EMAIL.state === false ||
                                this.validate.USR_NEW_PASS.state === false ||
                                this.validate.USR_CNF_PASS.state === false ||
                                this.validate.USR_COST_BY_HOUR.state === false
                                ) {
                            return;
                        }
                        for (let i in this.userExtendedAttributesList) {
                            let status = this.validateExtendedAttribute(this.userExtendedAttributesList[i]);
                            if (this.userExtendedAttributesList[i].required === 1) {
                                if (status === null || status === false) {
                                    return;
                                }
                            }
                        }
                        this.save();
                    });
                });
            },
            save() {
                //get form data
                let extendedAttributes = {};
                for (let i in this.userExtendedAttributesList) {
                    let attributeId = this.userExtendedAttributesList[i].attributeId;
                    let value = this.form.USR_EXTENDED_ATTRIBUTES_DATA[attributeId];
                    if (value !== undefined) {
                        extendedAttributes[attributeId] = value;
                    }
                }
                let formData = new FormData();
                formData.append("USR_FIRSTNAME", this.form.USR_FIRSTNAME);
                formData.append("USR_LASTNAME", this.form.USR_LASTNAME);
                formData.append("USR_USERNAME", this.form.USR_USERNAME);
                formData.append("USR_EMAIL", this.form.USR_EMAIL);
                formData.append("USR_ADDRESS", this.form.USR_ADDRESS);
                formData.append("USR_ZIP_CODE", this.form.USR_ZIP_CODE);
                formData.append("USR_COUNTRY", this.form.USR_COUNTRY);
                formData.append("USR_CITY", this.form.USR_CITY);
                formData.append("USR_REGION", this.form.USR_CITY); //important for compatibility
                formData.append("USR_LOCATION", this.form.USR_LOCATION);
                formData.append("USR_PHONE", this.form.USR_PHONE);
                formData.append("USR_POSITION", this.form.USR_POSITION);
                formData.append("USR_REPLACED_BY", this.form.USR_REPLACED_BY);
                formData.append("USR_DUE_DATE", this.form.USR_DUE_DATE);
                formData.append("USR_CALENDAR", this.form.USR_CALENDAR);
                formData.append("USR_STATUS", this.form.USR_STATUS);
                formData.append("USR_ROLE", this.form.USR_ROLE);
                formData.append("USR_TIME_ZONE", this.form.USR_TIME_ZONE);
                formData.append("USR_DEFAULT_LANG", this.form.USR_DEFAULT_LANG);
                formData.append("USR_COST_BY_HOUR", this.form.USR_COST_BY_HOUR);
                formData.append("USR_UNIT_COST", this.form.USR_UNIT_COST);
                formData.append("currentPassword", "");
                formData.append("USR_NEW_PASS", this.form.USR_NEW_PASS);
                formData.append("USR_CNF_PASS", this.form.USR_CNF_PASS);
                formData.append("USR_PHOTO", this.form.USR_PHOTO);
                formData.append("PREF_DEFAULT_MENUSELECTED", this.form.PREF_DEFAULT_MENUSELECTED);
                formData.append("PREF_DEFAULT_CASES_MENUSELECTED", this.form.PREF_DEFAULT_CASES_MENUSELECTED);
                formData.append("action", "saveUser");
                formData.append("USR_UID", this.form.USR_UID);
                formData.append("USR_LOGGED_NEXT_TIME", this.form.USR_LOGGED_NEXT_TIME);
                formData.append("USR_EXTENDED_ATTRIBUTES_DATA", JSON.stringify(extendedAttributes));
                formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            if ("error" in response.data && response.data.error !== "") {
                                this.$bvModal.msgBoxOk(response.data.error, {
                                    title: " ", //is important because title disappear
                                    hideHeaderClose: false,
                                    okTitle: this.$root.translation('ID_OK'),
                                    okVariant: "success",
                                    okOnly: true
                                }).then(value => {
                                    if (value === false) {
                                        return;
                                    }
                                }).catch(err => {
                                    err;
                                });
                            } else {
                                if (this.$root.modeOfForm() === 1) {
                                    window.location = this.$root.baseUrl() + "users/users_List";
                                }
                                if (this.$root.modeOfForm() === 2) {
                                    this.classCustom = "sr-only sr-only-focusable";
                                    this.classCustom2 = "";
                                    this.disabled = true;
                                    for (let i in this.disabledField) {
                                        this.disabledField[i] = true;
                                    }
                                    for (let i in this.validate) {
                                        this.validate[i].state = null;
                                    }
                                }
                            }
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            validateFirstName() {
                this.validate.USR_FIRSTNAME.state = true;
                if (this.form.USR_FIRSTNAME.trim() === "") {
                    this.validate.USR_FIRSTNAME.state = false;
                    this.validate.USR_FIRSTNAME.message = this.$root.translation('ID_IS_REQUIRED');
                }
            },
            validateLastName() {
                this.validate.USR_LASTNAME.state = true;
                if (this.form.USR_LASTNAME.trim() === "") {
                    this.validate.USR_LASTNAME.state = false;
                    this.validate.USR_LASTNAME.message = this.$root.translation('ID_IS_REQUIRED');
                }
            },
            validateUserName() {
                this.validate.USR_USERNAME.state = true;
                if (this.form.USR_USERNAME.trim() === "") {
                    this.validate.USR_USERNAME.state = false;
                    this.validate.USR_USERNAME.message = this.$root.translation('ID_IS_REQUIRED');
                    return new Promise((resolve, reject) => {
                        resolve();
                        reject;
                    });
                }
                this.disableButtonSave = true;
                let formData = new FormData();
                formData.append("action", "testUsername");
                formData.append("USR_UID", this.form.USR_UID);
                formData.append("NEW_USERNAME", this.form.USR_USERNAME);
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            if ("error" in response.data && response.data.error !== "") {
                                this.$bvModal.msgBoxOk(this.$root.translation(response.data.error), {
                                    title: " ", //is important because title disappear
                                    hideHeaderClose: false,
                                    okTitle: this.$root.translation('ID_OK'),
                                    okVariant: "success",
                                    okOnly: true
                                }).then(value => {
                                    if (value === false) {
                                        return;
                                    }
                                }).catch(err => {
                                    err;
                                });
                                this.disableButtonSave = false;
                                this.validate.USR_USERNAME.state = true;
                                return;
                            }
                            this.disableButtonSave = false;
                            this.validate.USR_USERNAME.message = response.data.descriptionText;
                            if (response.data.exists === false) {
                                this.validate.USR_USERNAME.state = true;
                                return;
                            }
                            this.validate.USR_USERNAME.state = false;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            validateEmail() {
                this.validate.USR_EMAIL.state = true;
                if (this.form.USR_EMAIL.trim() === "") {
                    this.validate.USR_EMAIL.state = false;
                    this.validate.USR_EMAIL.message = this.$root.translation('ID_IS_REQUIRED');
                    return;
                }
                if (/^(\w+)([-+.'][\w]+)*@(\w[-\w]*\.){1,5}([A-Za-z]){2,6}$/.test(this.form.USR_EMAIL) === false) {
                    this.validate.USR_EMAIL.state = false;
                    this.validate.USR_EMAIL.message = this.$root.translation('ID_INVALID_VALUE', [this.$root.translation('ID_EMAIL')]);
                }
            },
            validatePassword() {
                this.validate.USR_NEW_PASS.state = true;
                if (this.editing === true) {
                    return new Promise((resolve, reject) => {
                        resolve();
                        reject;
                    });
                }
                if (this.form.USR_NEW_PASS.trim() === "") {
                    this.validate.USR_NEW_PASS.state = false;
                    this.validate.USR_NEW_PASS.message = this.$root.translation('ID_IS_REQUIRED');
                    return new Promise((resolve, reject) => {
                        resolve();
                        reject;
                    });
                }
                this.disableButtonSave = true;
                let formData = new FormData();
                formData.append("action", "testPassword");
                formData.append("PASSWORD_TEXT", this.form.USR_NEW_PASS);
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            this.disableButtonSave = false;
                            let status = (response.data.STATUS === true);
                            this.validate.USR_NEW_PASS.state = status;
                            this.validate.USR_NEW_PASS.message = response.data.DESCRIPTION;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            validateConfirmationPassword() {
                this.validate.USR_CNF_PASS.state = true;
                if (this.editing === true) {
                    return new Promise((resolve, reject) => {
                        resolve();
                        reject;
                    });
                }
                if (this.form.USR_CNF_PASS.trim() === "") {
                    this.validate.USR_CNF_PASS.state = false;
                    this.validate.USR_CNF_PASS.message = this.$root.translation('ID_IS_REQUIRED');
                    return;
                }
                if (this.form.USR_CNF_PASS !== this.form.USR_NEW_PASS) {
                    this.validate.USR_CNF_PASS.state = false;
                    this.validate.USR_CNF_PASS.message = this.$root.translation("ID_NEW_PASS_SAME_OLD_PASS");
                }
            },
            validateCostByHour() {
                this.validate.USR_COST_BY_HOUR.state = true;
                this.form.USR_COST_BY_HOUR = this.form.USR_COST_BY_HOUR.toString();
                if (this.form.USR_COST_BY_HOUR.trim() === "") {
                    this.validate.USR_COST_BY_HOUR.state = false;
                    this.validate.USR_COST_BY_HOUR.message = this.$root.translation('ID_IS_REQUIRED');
                    return;
                }
                if (/^[0-9]/i.test(this.form.USR_COST_BY_HOUR) === false) {
                    this.validate.USR_COST_BY_HOUR.state = false;
                    this.validate.USR_COST_BY_HOUR.message = this.$root.translation('ID_INVALID_VALUE', [this.$root.translation('ID_COST_BY_HOUR')]);
                }
            },
            validateExtendedAttribute(obj) {
                let value = this.form.USR_EXTENDED_ATTRIBUTES_DATA[obj.attributeId];
                if (obj.required === 1) {
                    if (value === undefined || value === "") {
                        return false;
                    }
                    return true;
                }
                return null;
            },
            load() {
                this.urlImage = this.$root.baseUrl() + 'users/users_ViewPhotoGrid?pUID=' + window.USR_UID + '&h=' + Math.random();
                let formData = new FormData();
                formData.append("action", "userData");
                formData.append("USR_UID", window.USR_UID);
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            if ("permission" in response.data) {
                                this.permission = response.data.permission;
                                //match attribute
                                if ("USR_REGION" in this.permission) {
                                    this.permission.USR_CITY = this.permission.USR_REGION;
                                    delete this.permission.USR_REGION;
                                }
                                if ("USR_CUR_PASS" in this.permission) {
                                    delete this.permission.USR_CUR_PASS;
                                }
                            }
                            if ("user" in response.data) {
                                for (let i in this.form) {
                                    if (i in response.data.user) {
                                        let value = response.data.user[i]
                                        if (i === "USR_EXTENDED_ATTRIBUTES_DATA") {
                                            if (value === null) {
                                                value = "{}";
                                            }
                                            value = JSON.parse(value);
                                        }
                                        this.form[i] = value;
                                    }
                                }
                                //other
                                if ("REPLACED_NAME" in response.data.user &&
                                        "USR_REPLACED_BY" in response.data.user) {
                                    this.usersList = [{
                                            value: response.data.user.USR_REPLACED_BY,
                                            text: response.data.user.REPLACED_NAME
                                        }];
                                }
                                //for Default Cases Menu option
                                this.changeDefaultMainMenuOption();
                            }
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            loadServices() {
                this.getCountryList();
                this.getAvailableCalendars();
                this.getLanguagesList();
                this.getDefaultMainMenuOptionList();
                this.getDefaultCasesMenuOptionList();
                this.getRolesList();
                this.getUserExtendedAttributesList();
            },
            getCountryList() {
                let formData = new FormData();
                formData.append("action", "countryList");
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [{
                                    value: "",
                                    text: this.$root.translation('ID_SELECT')
                                }];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].IC_UID,
                                    text: response.data[i].IC_NAME
                                });
                            }
                            this.countryList = data;
                            this.getStateList();
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getStateList() {
                let formData = new FormData();
                formData.append("action", "stateList");
                formData.append("IC_UID", this.form.USR_COUNTRY);
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [{
                                    value: "",
                                    text: this.$root.translation('ID_SELECT')
                                }];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].IS_UID,
                                    text: response.data[i].IS_NAME
                                });
                            }
                            this.stateList = data;
                            this.getLocationList();
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getLocationList() {
                let formData = new FormData();
                formData.append("action", "locationList");
                formData.append("IC_UID", this.form.USR_COUNTRY);
                formData.append("IS_UID", this.form.USR_CITY);
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [{
                                    value: "",
                                    text: this.$root.translation('ID_SELECT')
                                }];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].IL_UID,
                                    text: response.data[i].IL_NAME
                                });
                            }
                            this.locationList = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getUsersList() {
                if (this.filterUser.trim() === "") {
                    this.usersList = [];
                    return null;
                }
                let formData = new FormData();
                formData.append("action", "usersList");
                formData.append("USR_UID", this.form.USR_UID);
                formData.append("filter", this.filterUser.trim());
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].USR_UID,
                                    text: response.data[i].USER_FULLNAME
                                });
                            }
                            this.usersList = data;
                            //set the first element
                            if (data.length > 0) {
                                this.form.USR_REPLACED_BY = data[0].value;
                            } else {
                                this.form.USR_REPLACED_BY = "";
                            }
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getAvailableCalendars() {
                let formData = new FormData();
                formData.append("action", "availableCalendars");
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].CALENDAR_UID,
                                    text: response.data[i].CALENDAR_NAME
                                });
                            }
                            this.availableCalendars = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getTimeZoneList() {
                let formData = new FormData();
                formData.append("action", "timeZoneParameters");
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [];
                            for (let i in response.data.timeZoneList) {
                                data.push({
                                    value: response.data.timeZoneList[i].value,
                                    text: response.data.timeZoneList[i].text
                                });
                            }
                            this.timeZoneList = data;
                            this.form.USR_TIME_ZONE = response.data.systemTimeZone;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getLanguagesList() {
                let formData = new FormData();
                formData.append("action", "languagesList");
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].LAN_ID,
                                    text: response.data[i].LAN_NAME
                                });
                            }
                            this.languagesList = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getDefaultMainMenuOptionList() {
                let formData = new FormData();
                formData.append("action", "defaultMainMenuOptionList");
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].id,
                                    text: response.data[i].name
                                });
                            }
                            this.defaultMainMenuOptionList = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getDefaultCasesMenuOptionList() {
                let formData = new FormData();
                formData.append("action", "defaultCasesMenuOptionList");
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [{
                                    value: "",
                                    text: this.$root.translation('ID_SELECT')
                                }];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].id,
                                    text: response.data[i].name
                                });
                            }
                            this.defaultCasesMenuOptionList = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getRolesList() {
                let formData = new FormData();
                formData.append("action", "rolesList");
                return axios.post(this.$root.baseUrl() + "users/usersAjax", formData)
                        .then(response => {
                            response;
                            let data = [];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].ROL_UID,
                                    text: response.data[i].ROL_CODE
                                });
                            }
                            this.rolesList = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getUserExtendedAttributesList() {
                let formData = new FormData();
                formData.append("option", "userExtendedAttributesList");
                formData.append("roleCode", this.form.USR_ROLE);
                return axios.post(this.$root.baseUrl() + "userExtendedAttributes/index", formData)
                        .then(response => {
                            response;
                            this.userExtendedAttributesList = response.data.data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getDefaultDueDate() {
                let date = new Date();
                let month = "" + (date.getMonth() + 1);
                let day = "" + date.getDate();
                let year = date.getFullYear();
                if (month.length < 2)
                    month = "0" + month;
                if (day.length < 2)
                    day = "0" + day;
                return [year + 1, month, day].join("-");
            },
            avatarClick() {
                let form = document.createElement('form');
                var input = document.createElement("input");
                input.type = "file";
                form.appendChild(input);
                form.style.display = "none";
                document.body.appendChild(form);
                let that = this;
                input.onchange = function () {
                    var file = input.files[0];
                    //validate file extension
                    if (/(\.jpg|\.jpeg|\.png|\.gif)$/i.test(file.name) === false) {
                        that.$bvModal.msgBoxOk(that.$root.translation('ID_INVALID_EXTENSION'), {
                            title: " ", //is important because title disappear
                            hideHeaderClose: false,
                            okTitle: that.$root.translation('ID_OK'),
                            okVariant: "success",
                            okOnly: true
                        }).then(value => {
                            if (value === false) {
                                return;
                            }
                        }).catch(err => {
                            err;
                        });
                        return;
                    }
                    that.form.USR_PHOTO = file;
                    //preview
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        that.urlImage = e.target.result;
                    };
                    reader.readAsDataURL(file);
                };
                input.click();
            },
            changeRole() {
                this.getUserExtendedAttributesList();
            },
            changeDefaultMainMenuOption() {
                let isPmCases = this.form.PREF_DEFAULT_MENUSELECTED === "PM_CASES";

                //disable PREF_DEFAULT_CASES_MENUSELECTED
                this.switchChangeDefaultMainMenuOption = !isPmCases;

                //remember PREF_DEFAULT_CASES_MENUSELECTED
                if (isPmCases && this.form.PREF_DEFAULT_CASES_MENUSELECTED !== "") {
                    this.memoryChangeDefaultMainMenuOption = this.form.PREF_DEFAULT_CASES_MENUSELECTED;
                }

                //restore
                this.form.PREF_DEFAULT_CASES_MENUSELECTED = isPmCases ? this.memoryChangeDefaultMainMenuOption : "";
            }
        }
    }
</script>

<style scoped>
</style>