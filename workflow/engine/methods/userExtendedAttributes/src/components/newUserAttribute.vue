<template>
    <div>
        <titleSection :title="$root.translation('ID_NEW_USER_ATTRIBUTE')"></titleSection>
        <b-form @submit.stop.prevent="onsubmit">
            <b-container fluid>
                <b-row>
                    <b-col cols="6">
                        <b-form-group :label="$root.translation('ID_NAME')">
                            <b-form-input v-model="form.name"
                                          autocomplete="off"
                                          :state="statusName"
                                          @keyup="validateName"/>
                            <b-form-invalid-feedback>{{statusNameMessage}}</b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group :label="$root.translation('ID_ATTRIBUTE_ID')">
                            <b-form-input v-model="form.attributeId"
                                          autocomplete="off"
                                          :state="statusAttributeId"
                                          @keyup="validateAttributeId"/>
                            <b-form-invalid-feedback>{{statusAttributeIdMessage}}</b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="6">
                        <b-form-group :label="$root.translation('ID_PROPERTIES')">
                            <b-form-checkbox v-model="form.hidden"
                                             value="1"
                                             unchecked-value="0"
                                             :disabled="form.required===1 || form.password===1">
                                {{$root.translation('ID_HIDDEN')}}
                                <b-button variant="outline-light"
                                          v-b-tooltip="{title:$root.translation('ID_ATTRIBUTE_WONT_BE_SEEN_IN_USER_INFORMATION'),placement:'right',variant:'warning',customClass:'custom-tooltip'}">
                                    <b-icon icon="question-circle-fill" 
                                            aria-hidden="true" 
                                            variant="primary"/>
                                </b-button>
                            </b-form-checkbox>
                            <b-form-checkbox v-model="form.required"
                                             value="1"
                                             unchecked-value="0"
                                             :disabled="form.hidden===1">
                                {{$root.translation('ID_REQUIRED')}}
                                <b-button variant="outline-light"
                                          v-b-tooltip="{title:$root.translation('ID_ATTRIBUTE_WILL_BE_REQUIRED_WHEN_EDITING_USER_SETTINGS'),placement:'right',variant:'warning',customClass:'custom-tooltip'}">
                                    <b-icon icon="question-circle-fill" 
                                            aria-hidden="true" 
                                            variant="primary"/>
                                </b-button>
                            </b-form-checkbox>
                            <b-form-checkbox v-model="form.password"
                                             value="1"
                                             unchecked-value="0"
                                             :disabled="form.hidden===1">
                                {{$root.translation('ID_TYPE_PASSWORD')}}
                                <b-button variant="outline-light"
                                          v-b-tooltip="{title:$root.translation('ID_ATTRIBUTE_WILL_BE_HIDDEN_USING_PLACEHOLDER'),placement:'right',variant:'warning',customClass:'custom-tooltip'}">
                                    <b-icon icon="question-circle-fill" 
                                            aria-hidden="true" 
                                            variant="primary"/>
                                </b-button>
                            </b-form-checkbox>
                        </b-form-group>
                    </b-col>
                    <b-col>
                    </b-col>
                </b-row>
                <b-row class="bv-row-flex-cols">
                    <b-col cols="6">
                        <b-form-group :label="$root.translation('ID_PROPERTIES')">
                            <b-form-radio v-model="form.option"
                                          value="allUser"
                                          class="mt-2">
                                {{$root.translation('ID_ALL_USERS')}}
                            </b-form-radio>
                            <b-form-radio v-model="form.option"
                                          value="byRol"
                                          class="mt-3">
                                {{$root.translation('ID_BY_ROLE')}}
                            </b-form-radio>
                        </b-form-group>
                    </b-col>
                    <b-col cols="6" align-self="end">
                        <b-form-group>
                            <b-input-group>
                                <b-form-select v-model="form.rol"
                                               :disabled="form.option==='allUser'"
                                               :options="roles"></b-form-select>
                                <b-input-group-append>
                                    <b-button variant="outline-secondary"
                                              @click="refreshRoles"
                                              :disabled="form.option==='allUser'">
                                        <b-icon icon="arrow-repeat" 
                                                aria-hidden="true"/>
                                    </b-button>
                                </b-input-group-append>
                                <b-input-group-append>
                                    <b-button variant="success"
                                              @click="addRole"
                                              :disabled="form.option==='allUser'">
                                        <b-icon icon="plus" aria-hidden="true"/> {{$root.translation('ID_ADD')}}
                                    </b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <b-form-group>
                            <b-form-tags v-model="form.selectedRoles"
                                         style="min-height:55px"
                                         :disabled="form.option==='allUser'">
                                <template v-slot="{ tags, inputAttrs, inputHandlers, tagVariant, addTag, removeTag, disabled}">
                                    <!--important is the control body-->
                                    <div class="d-inline-block">
                                        <div v-for="tag in tags" 
                                             :key="tag"
                                             :title="tag"
                                             class="d-inline-block border bg-light rounded-lg p-1 mr-2">
                                            {{getTextRol(tag)}}
                                            <b-button size="sm"
                                                      variant="light"
                                                      :disabled="disabled"
                                                      @click="deleteRol(tag,function(){removeTag(tag);})">
                                                <b-icon icon="x" 
                                                        aria-hidden="true" 
                                                        variant="primary"/>
                                            </b-button>    
                                        </div>
                                    </div>
                                </template>
                            </b-form-tags>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row class="text-right">
                    <b-col>
                        <b-form-group>
                            <b-button variant="danger"
                                      @click="$emit('cancel')">{{$root.translation('ID_CANCEL')}}</b-button>&nbsp;
                            <b-button type="submit" 
                                      variant="success"
                                      :disabled="!statusValidation">{{$root.translation('ID_SAVE')}}</b-button>
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
    import { validationMixin } from "vuelidate"
    export default {
        mixins: [validationMixin],
        components: {
            titleSection
        },
        data() {
            return {
                form: {
                    id: "",
                    name: "",
                    attributeId: "",
                    hidden: 0,
                    required: 0,
                    password: 0,
                    option: "allUser",
                    selectedRoles: [],
                    rol: "0"
                },
                roles: this.getRoles(),
                statusValidation: true,
                statusName: null,
                statusNameMessage: "",
                statusAttributeId: null,
                statusAttributeIdMessage: ""
            };
        },
        methods: {
            reset() {
                this.form = {
                    id: "",
                    name: "",
                    attributeId: "",
                    hidden: 0,
                    required: 0,
                    password: 0,
                    option: "allUser",
                    selectedRoles: [],
                    rol: "0"
                };
                this.statusValidation = true;
                this.statusName = null;
                this.statusAttributeId = null;
            },
            onsubmit() {
                let promise = this.validateName();
                promise.then(response => {
                    response;
                    let promise2 = this.validateAttributeId();
                    promise2.then(response2 => {
                        response2;
                        if (this.statusName === true && this.statusAttributeId === true) {
                            this.saveForm();
                        }
                    });
                });
            },
            validateName() {
                this.statusName = true;
                if (this.form.name.trim() === "") {
                    this.statusName = false;
                    this.statusNameMessage = this.$root.translation("ID_IS_REQUIRED");
                    return;
                }
                if (this.form.name.length >= 50) {
                    this.statusName = false;
                    this.statusNameMessage = this.$root.translation("ID_INVALID_MAX_PERMITTED", [this.$root.translation('ID_ATTRIBUTE_NAME'), '50']);
                    return;
                }
                if (/^[a-zA-Z][-_0-9a-zA-Z\s]+$/.test(this.form.name) === false) {
                    this.statusName = false;
                    this.statusNameMessage = this.$root.translation("ID_USE_ALPHANUMERIC_CHARACTERS_INCLUDING", ["- _"]);
                    return;
                }
                let formData = new FormData();
                formData.append("id", this.form.id);
                formData.append("name", this.form.name);
                return axios.post(this.$root.baseUrl() + "userExtendedAttributes/index?option=verifyName", formData)
                        .then(response => {
                            response;
                            if (response.data.valid === false) {
                                this.statusName = false;
                                this.statusNameMessage = response.data.message;
                            } else {
                                this.statusName = true;
                            }
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            validateAttributeId() {
                this.statusAttributeId = true;
                if (this.form.attributeId.trim() === "") {
                    this.statusAttributeId = false;
                    this.statusAttributeIdMessage = this.$root.translation("ID_IS_REQUIRED");
                    return;
                }
                if (this.form.attributeId.length >= 250) {
                    this.statusAttributeId = false;
                    this.statusAttributeIdMessage = this.$root.translation("ID_INVALID_MAX_PERMITTED", [this.$root.translation('ID_ATTRIBUTE_ID'), '250']);
                    return;
                }
                if (/^[a-zA-Z][-_.0-9a-zA-Z]+$/.test(this.form.attributeId) === false) {
                    this.statusAttributeId = false;
                    this.statusAttributeIdMessage = this.$root.translation("ID_USE_ALPHANUMERIC_CHARACTERS_INCLUDING", [". - _"]);
                    return;
                }
                let formData = new FormData();
                formData.append("id", this.form.id);
                formData.append("attributeId", this.form.attributeId);
                return axios.post(this.$root.baseUrl() + "userExtendedAttributes/index?option=verifyAttributeId", formData)
                        .then(response => {
                            response;
                            if (response.data.valid === false) {
                                this.statusAttributeId = false;
                                this.statusAttributeIdMessage = response.data.message;
                            } else {
                                this.statusAttributeId = true;
                            }
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            saveForm() {
                this.statusValidation = false;
                let formData = this.formToFormData(this.form);
                return axios.post(this.$root.baseUrl() + "userExtendedAttributes/index?option=save", formData)
                        .then(response => {
                            response;
                            this.$emit("save");
                            this.statusValidation = true;
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            getRoles() {
                this.refreshRoles();
                return this.roles;
            },
            refreshRoles() {
                let formData = new FormData();
                formData.append("request", "allRoles");
                axios.post(this.$root.baseUrl() + "roles/roles_Ajax", formData)
                        .then(response => {
                            response;
                            let data = [
                                {value: "0", text: this.$root.translation('ID_EMPTY_TYPE')}
                            ];
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
            },
            addRole() {
                if (this.form.rol === "0") {
                    return;
                }
                let obj = this.form.selectedRoles.find(rol => rol === this.form.rol);
                if (obj !== undefined) {
                    return;
                }
                this.form.selectedRoles.push(this.form.rol);
            },
            getTextRol(tag) {
                let obj = this.roles.find(rol => rol.value === tag);
                return obj.text;
            },
            deleteRol(tag, process) {
                this.$bvModal.msgBoxConfirm(this.$root.translation('ID_THE_USER_ROLES_FOR_ATTRIBUTE_HAS_BEEN_DELETED_PLEASE_CONFIRM', ['']), {
                    title: " ", //is important because title disappear
                    hideHeaderClose: false,
                    okTitle: this.$root.translation('ID_CONFIRM'),
                    okVariant: "success",
                    cancelTitle: this.$root.translation('ID_CANCEL'),
                    cancelVariant: "danger"
                }).then(value => {
                    if (value === false) {
                        return;
                    }
                    process();
                }).catch(err => {
                    err;
                });
            },
            formToFormData(form) {
                let formData = new FormData();
                formData.append("UEA_ID", form.id);
                formData.append("UEA_NAME", form.name.trim());
                formData.append("UEA_ATTRIBUTE_ID", form.attributeId);
                formData.append("UEA_HIDDEN", form.hidden);
                formData.append("UEA_REQUIRED", form.required);
                formData.append("UEA_PASSWORD", form.password);
                formData.append("UEA_OPTION", form.option);
                formData.append("UEA_ROLES", JSON.stringify(form.selectedRoles));
                return formData;
            },
            rowToForm(row) {
                let form = {
                    id: row.id,
                    name: row.name,
                    attributeId: row.attributeId,
                    hidden: row.hidden,
                    required: row.required,
                    password: row.password,
                    option: row.option,
                    selectedRoles: JSON.parse(row.roles)
                };
                return form;
            },
            load(row) {
                this.form = this.rowToForm(row);
            }
        }
    }
</script>

<style scoped>
    .bv-row-flex-cols{
        min-height:7rem;
    }
</style>