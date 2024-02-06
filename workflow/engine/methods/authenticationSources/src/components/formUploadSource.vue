<template>
    <div>
        <b-form @submit.stop.prevent="onSave">
            <b-container fluid>
                <b-row>
                    <b-col>
                        <b-form-group :label="$root.translation('ID_PLEASE_ADD_THE_FILE_SETTINGS_TO_BE_UPLOADED')" v-if="newName==true">
                            <b-form-file v-model="form.connectionSettings"
                                         @change="change"
                                         :state="validateState('connectionSettings')"
                                         :placeholder="$root.translation('ID_CHOOSE_A_FILE_OR_DROP_IT_HERE')"
                                         :drop-placeholder="$root.translation('ID_DROP_FILE_HERE')">
                            </b-form-file>
                            <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group :label="$root.translation('ID_CONNECTION_WITH_THE_SAME_NAME_PLEASE_SELECT_AN_OPTION',[fileContent.AUTH_SOURCE_NAME])" v-else>
                            <b-form-file v-model="form.connectionSettings"
                                         @change="change"
                                         :state="validateState('connectionSettings')"
                                         :placeholder="$root.translation('ID_CHOOSE_A_FILE_OR_DROP_IT_HERE')"
                                         :drop-placeholder="$root.translation('ID_DROP_FILE_HERE')">
                            </b-form-file>
                            <b-form-invalid-feedback>{{$root.translation('ID_IS_REQUIRED')}}</b-form-invalid-feedback>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row class="text-center">
                    <b-col>
                        <b-form-group v-if="newName==true">
                            <b-button variant="danger" 
                                      @click="$emit('cancel')">{{$root.translation('ID_CANCEL')}}</b-button>&nbsp;
                            <b-button type="submit" 
                                      variant="success" 
                                      :disabled='isDisabled' 
                                      id="save">{{$root.translation('ID_SAVE')}}</b-button>
                        </b-form-group>
                        <b-form-group v-else>
                            <b-button variant="danger" 
                                      @click="$emit('cancel')">{{$root.translation('ID_CANCEL')}}</b-button>&nbsp;
                            <b-button type="submit" 
                                      variant="primary" 
                                      :disabled='isDisabled'
                                      id="update">{{$root.translation('ID_UPDATE_SETTINGS')}}</b-button>&nbsp;
                            <b-button type="submit" 
                                      variant="success" 
                                      :disabled='isDisabled'
                                      id="new">{{$root.translation('ID_NEW_CONNECTION')}}</b-button>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-container>
        </b-form>
        <b-modal id="messageForInvalidFileExtension" ok-only static>{{$root.translation('ID_PMG_SELECT_FILE')}}</b-modal>
        <b-modal id="messageForInvalidFileFormat" ok-only static>{{$root.translation('ID_INVALID_DATA')}}</b-modal>
    </div>
</template>

<script>
    import { validationMixin } from "vuelidate"
    import { required } from "vuelidate/lib/validators"
    import axios from "axios"
    export default {
        mixins: [validationMixin],
        props: {
            skipNameValidation: Boolean
        },
        components: {
        },
        validations: {
            form: {
                connectionSettings: {
                    required
                }
            }
        },
        data() {
            return {
                form: {
                    connectionSettings: []
                },
                fileContent: {},
                isDisabled: true,
                validationResult: {},
                newName: true
            };
        },
        methods: {
            validateState(name) {
                const {$dirty, $error} = this.$v.form[name];
                return $dirty ? !$error : null;
            },
            onSave(e) {
                this.$v.form.$touch();
                if (this.$v.form.$anyError) {
                    return;
                }
                //validation
                if (e.submitter.id === "save") {
                    this.$emit('optionSaveButton', this.fileContent);
                }
                if (e.submitter.id === "update") {
                    this.$emit('optionUpdateButton', this.fileContent, this.validationResult.row);
                }
                if (e.submitter.id === "new") {
                    this.fileContent.AUTH_SOURCE_NAME = this.validationResult.suggestName;
                    this.$emit('optionNewButton', this.fileContent);
                }
            },
            reset() {
                this.newName = true;
                this.validationResult = {};
                this.form.connectionSettings = [];
            },
            change(e) {
                let input = e.target;
                if (input.files.length <= 0) {
                    return;
                }
                let file = input.files[0];
                if (file.name.indexOf(".json") < 0) {
                    this.$bvModal.show("messageForInvalidFileExtension");
                    this.reset();
                    this.isDisabled = true;
                    return;
                }
                let reader = new FileReader();
                reader.readAsText(file, "UTF-8");
                reader.onload = (e) => {
                    this.fileContent = JSON.parse(e.target.result);
                    //validation content
                    if (!("AUTH_SOURCE_NAME" in this.fileContent)) {
                        this.$bvModal.show("messageForInvalidFileFormat");
                        this.reset();
                        this.isDisabled = true;
                        return;
                    }
                    if (this.skipNameValidation === true) {
                        this.isDisabled = false;
                        return;
                    }
                    //validation name
                    let formData = new FormData();
                    formData.append("AUTH_SOURCE_NAME", this.fileContent.AUTH_SOURCE_NAME);
                    axios.post(this.$root.baseUrl() + "authSources/ldapAdvancedProxy.php?functionAccion=ldapVerifyName", formData)
                            .then(response => {
                                this.newName = response.data.row === false;
                                this.validationResult = response.data;
                                this.isDisabled = false;
                            })
                            .catch(error => {
                                error;
                                this.isDisabled = true;
                            })
                            .finally(() => {
                            });
                };
                reader.onerror = () => {
                };
                return;
            }
        }
    }
</script>

<style scoped>
</style>
