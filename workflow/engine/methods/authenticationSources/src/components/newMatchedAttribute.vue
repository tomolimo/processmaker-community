<template>
    <div>
        <titleSection :title="$root.translation('ID_NEW_MATCHED_ATTRIBUTE')"></titleSection>
        <b-form @submit.stop.prevent="onSave">
            <b-container fluid>
                <b-row>
                    <b-col>
                        <b-form-group :label="$root.translation('ID_ROLE')">
                            <b-form-select v-model="form.attributeRole"
                                           :options="roles"
                                           @change="changeRoles"/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_PROCESSMAKER_USER_FIELD')" description="">
                            <b-form-select v-model="form.attributeUser"
                                           :options="userAttributes"/>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_LDAP_ATTRIBUTE')">
                            <b-form-input v-model="form.attributeLdap"
                                          :state="true"
                                          autocomplete="off"/>
                            <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row class="text-right">
                    <b-col>
                        <b-form-group>
                            <b-button variant="danger" @click="$emit('cancel')">{{$root.translation('ID_CANCEL')}}</b-button>&nbsp;
                            <b-button type="submit" variant="success">{{$root.translation('ID_SAVE')}}</b-button>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
        </b-form>
    </div>
</template>

<script>
    import axios from "axios"
    import { validationMixin } from "vuelidate"
    import { required } from "vuelidate/lib/validators"
    import titleSection from "./titleSection.vue"
    export default {
        mixins: [validationMixin],
        components: {
            titleSection
        },
        validations: {
            form: {
                attributeLdap: {
                    required
                }
            }
        },
        data() {
            return {
                form: {
                    index: null,
                    attributeLdap: "",
                    attributeRole: "",
                    attributeUser: ""
                },
                roles: [{
                        value: "", text: "All"
                    }],
                userAttributes: []
            };
        },
        mounted() {
            let promise = this.getRolesList();
            promise.then(response => {
                response;
                this.changeRoles();
            });
        },
        methods: {
            validateState(name) {
                const {$dirty, $error} = this.$v.form[name];
                return $dirty ? !$error : null;
            },
            onSave() {
                this.$v.form.$touch();
                if (this.$v.form.$anyError) {
                    return;
                }
                this.$emit("save", this.form);
            },
            load(row, index) {
                this.form.index = index;
                this.form.attributeLdap = row.attributeLdap;
                this.form.attributeRole = row.attributeRole;
                this.form.attributeUser = row.attributeUser;
            },
            reset() {
                this.form = {
                    index: null,
                    attributeLdap: "",
                    attributeRole: "",
                    attributeUser: ""
                };
            },
            changeRoles() {
                let formData = new FormData();
                formData.append("option", "listByRol");
                formData.append("rolCode", this.form.attributeRole);
                return axios.post(this.$root.baseUrl() + "userExtendedAttributes/index", formData)
                        .then(response => {
                            response;
                            let data = [{
                                    value: "",
                                    text: this.$root.translation('ID_SELECTED_FIELD')
                                }];
                            for (let i in response.data.data) {
                                data.push({
                                    value: response.data.data[i].value,
                                    text: response.data.data[i].text
                                });
                            }
                            this.userAttributes = data;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getRolesList() {
                let formData = new FormData();
                formData.append("request", "allRoles");
                return axios.post(this.$root.baseUrl() + "roles/roles_Ajax", formData)
                        .then(response => {
                            response;
                            let data = [{
                                    value: "",
                                    text: this.$root.translation('ID_ALL')
                                }];
                            for (let i in response.data) {
                                data.push({
                                    value: response.data[i].ROL_CODE,
                                    text: response.data[i].ROL_NAME
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
        }
    }
</script>

<style scoped>
</style>
