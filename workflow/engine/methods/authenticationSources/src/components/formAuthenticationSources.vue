<template>
    <div>
        <b-form @submit.stop.prevent="onSave">
            <b-container fluid>
                <b-row>
                    <b-col>
                        <b-form-group :label="$root.translation('ID_AVAILABLE_AUTHENTICATION_SOURCES')" description="">
                            <b-form-select v-model="form.availableAuthenticationSource"
                                           :options="availableAuthenticationSources"/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_NAME')">
                            <b-form-input v-model="form.name"
                                          :state="validateState('name')"
                                          autocomplete="off"/>
                            <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_TYPE')">
                            <b-form-select v-model="form.type"
                                           :options="types"
                                           @change="changeTypeForm"/>
                        </b-form-group>
                        <b-form-group v-if="form.type==='ad'"
                                      :label="$root.translation('ID_REQUIRE_SIGN_IN_POLICY_FOR_LDAP')">
                            <b-form-checkbox v-model="form.signInPolicyForLDAP"
                                             value="1"
                                             unchecked-value="0"/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_SERVER_ADDRESS')">
                            <b-form-input v-model="form.serverAddress"
                                          :state="validateState('serverAddress')"
                                          autocomplete="off"/>
                            <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_PORT')">
                            <b-input-group>
                                <template #append>
                                    <b-input-group-text class="p-0">
                                        <b-button size="md"
                                                  variant="outline-light"
                                                  class="border-0"
                                                  @click="disabledField.port=!disabledField.port;">
                                            <b-icon icon="pencil-fill" 
                                                    aria-hidden="true" 
                                                    variant="primary">
                                            </b-icon>
                                        </b-button>
                                    </b-input-group-text>
                                </template>
                                <b-form-input v-model="form.port"
                                              :state="validateState('port')"
                                              :disabled="disabledField.port"
                                              autocomplete="off"/>
                                <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                            </b-input-group>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_ENABLE_AUTOMATIC_REGISTER')"
                                      label-cols-lg="8">
                            <b-form-checkbox v-model="form.enableAutomaticRegister"
                                             value="1"
                                             unchecked-value="0"
                                             switch/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_ANONYMOUS')"
                                      label-cols-lg="8">
                            <b-form-checkbox v-model="form.anonymous"
                                             value="1"
                                             unchecked-value="0"
                                             switch/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_ENABLED_TLS')"
                                      label-cols-lg="8">
                            <b-form-checkbox v-model="form.enableTLS"
                                             value="1"
                                             unchecked-value="0"
                                             switch/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_BASE_DN')">
                            <b-form-input v-model="form.baseDN"
                                          placeholder="dc=business,dc=net"
                                          autocomplete="off"/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_INACTIVE_USERS')">
                            <b-form-input v-model="form.inactiveUsers"
                                          autocomplete="off"/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_ROLE')">
                            <b-form-select v-model="form.role"
                                           :options="roles"/>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group class="text-right">
                            <b-button variant="success" @click="$refs['fas-b-modal-upload-file'].show();">{{$root.translation('ID_IMPORT_SETTINGS')}}</b-button>
                        </b-form-group>
                        <b-form-group class="text-right">
                            <b-link href="#" @click="matchAttributesToSync" v-show="showMathAttributes || testStatus">{{$root.translation('ID_MATCH_ATTRIBUTES_TO_SYNC')}} &gt;&gt;</b-link>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_USERNAME')">
                            <b-form-input v-model="form.userName"
                                          :state="validateState('userName')"
                                          autocomplete="off"/>
                            <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_PASSWORD')">
                            <b-form-input v-model="form.password"
                                          :state="validateState('password')"
                                          type="password"
                                          autocomplete="off"/>
                            <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_USER_IDENTIFIER')">
                            <b-input-group>
                                <template #append>
                                    <b-input-group-text class="p-0">
                                        <b-button size="md"
                                                  variant="outline-light"
                                                  class="border-0"
                                                  @click="disabledField.userIdentifier=!disabledField.userIdentifier;">
                                            <b-icon icon="pencil-fill" 
                                                    aria-hidden="true" 
                                                    variant="primary">
                                            </b-icon>
                                        </b-button>
                                    </b-input-group-text>
                                </template>
                                <b-form-input v-model="form.userIdentifier"
                                              autocomplete="off"
                                              :disabled="disabledField.userIdentifier"/>
                            </b-input-group>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_GROUP_IDENTIFIER')">
                            <b-input-group>
                                <template #append>
                                    <b-input-group-text class="p-0">
                                        <b-button size="md"
                                                  variant="outline-light"
                                                  class="border-0"
                                                  @click="disabledField.groupIdentifier=!disabledField.groupIdentifier;">
                                            <b-icon icon="pencil-fill" 
                                                    aria-hidden="true" 
                                                    variant="primary">
                                            </b-icon>
                                        </b-button>
                                    </b-input-group-text>
                                </template>
                                <b-form-input v-model="form.groupIdentifier"
                                              autocomplete="off"
                                              :disabled="disabledField.groupIdentifier"/>
                            </b-input-group>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_FILTER_TO_SEARCH_USERS')">
                            <b-form-input v-model="form.filterToSearchUsers"
                                          autocomplete="off"/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_USER_CLASS_IDENTIFIER')">
                            <b-input-group>
                                <template #append>
                                    <b-input-group-text class="p-0">
                                        <b-button size="md"
                                                  variant="outline-light"
                                                  class="border-0"
                                                  @click="disabledField.userClassIdentifier=!disabledField.userClassIdentifier;">
                                            <b-icon icon="pencil-fill" 
                                                    aria-hidden="true" 
                                                    variant="primary">
                                            </b-icon>
                                        </b-button>
                                    </b-input-group-text>
                                </template>
                                <b-form-input v-model="form.userClassIdentifier"
                                              :disabled="disabledField.userClassIdentifier"
                                              autocomplete="off"/>
                            </b-input-group>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_GROUP_CLASS_IDENTIFIER')">
                            <b-input-group>
                                <template #append>
                                    <b-input-group-text class="p-0">
                                        <b-button size="md"
                                                  variant="outline-light"
                                                  class="border-0"
                                                  @click="disabledField.groupClassIdentifier=!disabledField.groupClassIdentifier;">
                                            <b-icon icon="pencil-fill" 
                                                    aria-hidden="true" 
                                                    variant="primary">
                                            </b-icon>
                                        </b-button>
                                    </b-input-group-text>
                                </template>
                                <b-form-input v-model="form.groupClassIdentifier"
                                              :disabled="disabledField.groupClassIdentifier"
                                              autocomplete="off"/>
                            </b-input-group>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_DEPARTMENT_CLASS_IDENTIFIER')">
                            <b-input-group>
                                <template #append>
                                    <b-input-group-text class="p-0">
                                        <b-button size="md"
                                                  variant="outline-light"
                                                  class="border-0"
                                                  @click="disabledField.departmentClassIdentifier=!disabledField.departmentClassIdentifier;">
                                            <b-icon icon="pencil-fill" 
                                                    aria-hidden="true" 
                                                    variant="primary">
                                            </b-icon>
                                        </b-button>
                                    </b-input-group-text>
                                </template>
                                <b-form-input v-model="form.departmentClassIdentifier"
                                              :disabled="disabledField.departmentClassIdentifier"
                                              autocomplete="off"/>
                            </b-input-group>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row class="text-right">
                    <b-col>
                        <b-form-group>
                            <b-button variant="danger" @click="$emit('cancel')">{{$root.translation('ID_CANCEL')}}</b-button>&nbsp;
                            <b-button type="submit" variant="success">{{buttonLabel}}</b-button>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
        </b-form>
        <b-modal id="messageForFailedTest"
                 ok-variant="success"
                 ok-only>
            {{testMessage}}
        </b-modal>
        <b-modal ref="fas-b-modal-upload-file"
                 :title="$root.translation('ID_IMPORT_SETTINGS')"
                 hide-footer
                 size="lg">
            <formUploadSource ref="formUploadSource"
                              @cancel="$refs['fas-b-modal-upload-file'].hide();$refs.formUploadSource.reset();"
                              @optionSaveButton="optionSaveButton"
                              skipNameValidation>
            </formUploadSource>
        </b-modal>
    </div>
</template>

<script>
    import formUploadSource from "./formUploadSource.vue"
    import { validationMixin } from "vuelidate"
    import { required } from "vuelidate/lib/validators"
    import axios from "axios"
    export default {
        mixins: [validationMixin],
        components: {
            formUploadSource
        },
        validations() {
            let fields = {
                form: {
                    name: {
                        required
                    },
                    serverAddress: {
                        required
                    },
                    port: {
                        required
                    }
                }
            };
            if (this.form.anonymous === '1') {
                fields.form.userName = {
                };
                fields.form.password = {
                };
            }
            if (this.form.anonymous === '0') {
                fields.form.userName = {
                    required
                };
                fields.form.password = {
                    required
                };
            }
            return fields;
        },
        data() {
            return {
                buttonLabel: this.$root.translation("ID_TEST"),
                testStatus: false,
                testMessage: "",
                showMathAttributes: false,
                form: {
                    uid: "",
                    availableAuthenticationSource: "ldapAdvanced",
                    name: "",
                    type: "ad",
                    serverAddress: "",
                    port: "389",
                    enableAutomaticRegister: "0",
                    anonymous: "0",
                    enableTLS: "0",
                    baseDN: "",
                    userName: "",
                    password: "",
                    userIdentifier: "samaccountname",
                    filterToSearchUsers: "",
                    gridText: "[]",
                    signInPolicyForLDAP: "1",
                    inactiveUsers: "",
                    role: "PROCESSMAKER_OPERATOR",
                    groupIdentifier: "member",
                    userClassIdentifier: "",
                    groupClassIdentifier: "(objectclass=posixgroup)(objectclass=group)(objectclass=groupofuniquenames)",
                    departmentClassIdentifier: "(objectclass=organizationalunit)"
                },
                availableAuthenticationSources: [
                    {value: "ldapAdvanced", text: "LDAP Advanced"},
                    {value: "ldap", text: "LDAP"}
                ],
                types: [
                    {value: "ad", text: "Active Directory"},
                    {value: "ldap", text: "Open LDAP"},
                    {value: "ds", text: "389 DS"}
                ],
                roles: [],
                disabledField: {
                    port: true,
                    userIdentifier: true,
                    groupIdentifier: true,
                    userClassIdentifier: true,
                    groupClassIdentifier: true,
                    departmentClassIdentifier: true
                }
            };
        },
        methods: {
            validateState(name) {
                const {$dirty, $error} = this.$v.form[name];
                return $dirty ? !$error : null;
            },
            reset() {
                this.form = {
                    uid: "",
                    availableAuthenticationSource: "ldapAdvanced",
                    name: "",
                    type: "ad",
                    serverAddress: "",
                    port: "389",
                    enableAutomaticRegister: "0",
                    anonymous: "0",
                    enableTLS: "0",
                    baseDN: "",
                    userName: "",
                    password: "",
                    userIdentifier: "samaccountname",
                    filterToSearchUsers: "",
                    gridText: "[]",
                    signInPolicyForLDAP: "1",
                    inactiveUsers: "",
                    role: "PROCESSMAKER_OPERATOR",
                    groupIdentifier: "member",
                    userClassIdentifier: "",
                    groupClassIdentifier: "(objectclass=posixgroup)(objectclass=group)(objectclass=groupofuniquenames)",
                    departmentClassIdentifier: "(objectclass=organizationalunit)"
                };
            },
            onSave() {
                this.$v.form.$touch();
                if (this.$v.form.$anyError) {
                    return;
                }
                if (this.testStatus) {
                    this.$emit("save", this.form);
                } else {
                    this.test(this.form);
                }
            },
            load(obj) {
                this.form = obj;
            },
            test(form) {
                let formDataForName = new FormData();
                formDataForName.append("AUTH_SOURCE_NAME", form.name);
                axios.post(this.$root.baseUrl() + "authSources/ldapAdvancedProxy.php?functionAccion=ldapVerifyName", formDataForName)
                        .then(response => {
                            //the name is valid
                            if (response.data.row === false || (this.form.uid !== "" && typeof this.form.uid === "string")) {
                                let formData = this.formToFormData(form);
                                axios.post(this.$root.baseUrl() + "authSources/ldapAdvancedProxy.php?functionAccion=ldapTestConnection", formData)
                                        .then(response => {
                                            //test is successful
                                            if (response.data.status === "OK") {
                                                this.testStatus = true;
                                                this.buttonLabel = this.$root.translation("ID_SAVE");
                                                if ("message" in response.data) {
                                                    this.$bvModal.msgBoxOk(response.data.message, {
                                                        title: " ", //is important because title disappear
                                                        hideHeaderClose: false,
                                                        okTitle: this.$root.translation('ID_OK'),
                                                        okVariant: "success",
                                                        okOnly: true
                                                    });
                                                }
                                                this.$bvModal.msgBoxOk(this.$root.translation('ID_SUCCESSFUL_TEST_CONNECTION'), {
                                                    title: " ", //is important because title disappear
                                                    hideHeaderClose: false,
                                                    okTitle: this.$root.translation('ID_OK'),
                                                    okVariant: "success",
                                                    okOnly: true
                                                });
                                                return;
                                            }
                                            //test fail
                                            this.testMessage = response.data.message;
                                            this.testStatus = false;
                                            this.buttonLabel = this.$root.translation("ID_TEST");
                                            this.$bvModal.show("messageForFailedTest");
                                        })
                                        .catch(error => {
                                            error;
                                        })
                                        .finally(() => {
                                        });
                                return;
                            }
                            //the name exist
                            this.$bvModal.msgBoxOk(this.$root.translation('ID_NAME_EXISTS'), {
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
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            matchAttributesToSync() {
                this.$emit('matchAttributesToSync');
            },
            setGridText(gridText) {
                this.form.gridText = gridText;
            },
            getGridText() {
                return this.form.gridText;
            },
            optionSaveButton(row) {
                this.$refs['fas-b-modal-upload-file'].hide();
                row.AUTH_SOURCE_UID = this.form.uid;
                let form = this.rowToForm(row);
                this.load(form);
            },
            rowToForm(row) {
                let gridText = [];
                if ("AUTH_SOURCE_GRID_ATTRIBUTE" in  row) {
                    for (let i in row.AUTH_SOURCE_GRID_ATTRIBUTE) {
                        let data = row.AUTH_SOURCE_GRID_ATTRIBUTE[i] || {};
                        gridText.push({
                            attributeRole: data.attributeRole || '',
                            attributeUser: data.attributeUser || '',
                            attributeLdap: data.attributeLdap || ''
                        });
                    }
                }
                var obj = {
                    uid: row.AUTH_SOURCE_UID,
                    availableAuthenticationSource: row.AUTH_SOURCE_PROVIDER,
                    name: row.AUTH_SOURCE_NAME,
                    type: row.LDAP_TYPE,
                    serverAddress: row.AUTH_SOURCE_SERVER_NAME,
                    port: row.AUTH_SOURCE_PORT,
                    enableAutomaticRegister: row.AUTH_SOURCE_AUTO_REGISTER,
                    anonymous: row.AUTH_ANONYMOUS,
                    enableTLS: row.AUTH_SOURCE_ENABLED_TLS,
                    baseDN: row.AUTH_SOURCE_BASE_DN,
                    userName: row.AUTH_SOURCE_SEARCH_USER,
                    password: row.AUTH_SOURCE_PASSWORD,
                    userIdentifier: row.AUTH_SOURCE_IDENTIFIER_FOR_USER,
                    filterToSearchUsers: row.AUTH_SOURCE_USERS_FILTER,
                    gridText: JSON.stringify(gridText),
                    signInPolicyForLDAP: row.AUTH_SOURCE_SIGNIN_POLICY_FOR_LDAP,
                    inactiveUsers: row.AUTH_SOURCE_RETIRED_OU,
                    role: row.USR_ROLE || "",
                    groupIdentifier: row.AUTH_SOURCE_IDENTIFIER_FOR_USER_GROUP || "",
                    userClassIdentifier: row.AUTH_SOURCE_IDENTIFIER_FOR_USER_CLASS || "",
                    groupClassIdentifier: row.GROUP_CLASS_IDENTIFIER || "",
                    departmentClassIdentifier: row.DEPARTMENT_CLASS_IDENTIFIER || ""
                };
                return obj;
            },
            formToFormData(form) {
                let formData = new FormData();
                formData.append("AUTH_SOURCE_UID", form.uid);
                formData.append("AUTH_SOURCE_NAME", form.name);
                formData.append("AUTH_SOURCE_PROVIDER", form.availableAuthenticationSource);
                formData.append("LDAP_TYPE", form.type);
                formData.append("AUTH_SOURCE_AUTO_REGISTER", form.enableAutomaticRegister);
                formData.append("AUTH_SOURCE_SERVER_NAME", form.serverAddress);
                formData.append("AUTH_SOURCE_PORT", form.port);
                formData.append("AUTH_SOURCE_ENABLED_TLS", form.enableTLS);
                formData.append("AUTH_SOURCE_BASE_DN", form.baseDN);
                formData.append("AUTH_ANONYMOUS", form.anonymous);
                formData.append("AUTH_SOURCE_SEARCH_USER", form.userName);
                formData.append("AUTH_SOURCE_PASSWORD", form.password);
                formData.append("AUTH_SOURCE_IDENTIFIER_FOR_USER", form.userIdentifier);
                formData.append("AUTH_SOURCE_USERS_FILTER", form.filterToSearchUsers);
                formData.append("AUTH_SOURCE_RETIRED_OU", form.inactiveUsers);
                formData.append("AUTH_SOURCE_ATTRIBUTE_IDS", "USR_FIRSTNAME|USR_LASTNAME|USR_EMAIL|USR_DUE_DATE|USR_STATUS|USR_STATUS_ID|USR_ADDRESS|USR_PHONE|USR_FAX|USR_CELLULAR|USR_ZIP_CODE|USR_POSITION|USR_BIRTHDAY|USR_COST_BY_HOUR|USR_UNIT_COST|USR_PMDRIVE_FOLDER_UID|USR_BOOKMARK_START_CASES|USR_TIME_ZONE|USR_DEFAULT_LANG|USR_LAST_LOGIN|");
                formData.append("AUTH_SOURCE_SHOWGRID", "");
                formData.append("AUTH_SOURCE_GRID_TEXT", form.gridText);
                formData.append("AUTH_SOURCE_SHOWGRID-checkbox", "on");
                //additional
                formData.append("AUTH_SOURCE_SIGNIN_POLICY_FOR_LDAP", form.signInPolicyForLDAP);
                formData.append("USR_ROLE", form.role);
                formData.append("AUTH_SOURCE_IDENTIFIER_FOR_USER_GROUP", form.groupIdentifier);
                formData.append("AUTH_SOURCE_IDENTIFIER_FOR_USER_CLASS", form.userClassIdentifier);
                formData.append("GROUP_CLASS_IDENTIFIER", form.groupClassIdentifier);
                formData.append("DEPARTMENT_CLASS_IDENTIFIER", form.departmentClassIdentifier);
                //compatibility for complement ppsellucianldap
                formData.append("CUSTOM_CHECK_AUTH_SOURCE_IDENTIFIER_FOR_USER", 0);
                formData.append("CUSTOM_CHECK_AUTH_SOURCE_IDENTIFIER_FOR_USER_GROUP", 0);
                formData.append("CUSTOM_CHECK_DEPARTMENT_CLASS_IDENTIFIER", 0);
                formData.append("CUSTOM_CHECK_GROUP_CLASS_IDENTIFIER", 0);
                formData.append("CUSTOM_AUTH_SOURCE_IDENTIFIER_FOR_USER", "");
                formData.append("CUSTOM_AUTH_SOURCE_IDENTIFIER_FOR_USER_GROUP", "");
                formData.append("CUSTOM_DEPARTMENT_CLASS_IDENTIFIER", "");
                formData.append("CUSTOM_GROUP_CLASS_IDENTIFIER", "");
                return formData;
            },
            changeTypeForm(value) {
                if (value === "ad") {
                    this.form.userIdentifier = "samaccountname";
                    this.form.groupIdentifier = "member";
                    this.form.signInPolicyForLDAP = "1";
                }
                if (value === "ldap") {
                    this.form.userIdentifier = "uid";
                    this.form.groupIdentifier = "memberuid";
                    this.form.signInPolicyForLDAP = "0";
                }
                if (value === "ds") {
                    this.form.userIdentifier = "uid";
                    this.form.groupIdentifier = "uniquemember";
                    this.form.signInPolicyForLDAP = "0";
                }
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
                            this.roles = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            }
        },
        watch: {
            form: {
                handler() {
                    this.testStatus = false;
                    this.buttonLabel = this.$root.translation("ID_TEST");
                    this.showMathAttributes = !(this.form.uid === "");
                },
                deep: true
            }
        },
        mounted() {
            this.$nextTick(function () {
                this.getRolesList();
            });
        }
    }
</script>

<style scoped>
</style>