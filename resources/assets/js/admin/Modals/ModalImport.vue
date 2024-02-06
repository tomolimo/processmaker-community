<template>
    <div>
        <b-modal
            ref="modal-import"
            hide-footer
            size="md"
        >
            <template v-slot:modal-title>
                {{ $t('ID_IMPORT_CUSTOM_CASE_LIST') }}
            </template>
            <b-container fluid>
                <div v-if="!caseListDuplicate">
                    {{ $t('ID_PLEASE_ADD_THE_CUSTOM_LIST_FILE_TO_BE_UPLOADED') }}
                </div>
                <div v-if="caseListDuplicate">
                    {{ message }}
                </div>
                <div>
                    <b-form-file
                        v-model="fileCaseList"
                        :state="validFile"
                        ref="file-input"
                        :disabled="caseListDuplicate"
                    ></b-form-file>
                </div>
                <p>
                </p>
            </b-container>
            <div class="modal-footer">
                <div class="float-right">
                    <b-button
                        variant="danger"
                        data-dismiss="modal"
                        @click="hide"
                    >
                        {{ $t("ID_CANCEL") }}
                    </b-button>
                    <b-button 
                        variant="success"
                        v-if="!caseListDuplicate"
                        @click="importCustomCaseList"
                    >
                        {{ $t("ID_SAVE") }}
                    </b-button>
                    <b-button 
                        variant="info"
                        v-if="caseListDuplicate"
                        @click="continueImport()"
                    >
                        {{ $t("ID_CONTINUE") }}
                    </b-button>
                </div>
            </div>
        </b-modal>
        <!-- pmTable does not exist in the workspace -->
        <b-modal
            size="md"
            ok-only
            :ok-title="$t('ID_CLOSE')"
            ok-variant="danger"
            v-model="pmTableNoExist"
        >
            <template v-slot:modal-title>
                {{ $t('ID_IMPORT_CUSTOM_CASE_LIST') }}
            </template>
            <b-container fluid>
                <div>
                    {{ message }}
                </div>
            </b-container>
        </b-modal>
        <!-- pmTable incomplete columns for custom case list -->
        <b-modal
            hide-footer
            size="md"
            v-model="pmTableNoFields"
        >
            <template v-slot:modal-title>
                {{ $t('ID_IMPORT_CUSTOM_CASE_LIST') }}
            </template>
            <b-container fluid>
                <div>
                    {{ message }}
                </div>
            </b-container>
            <div class="modal-footer">
                <div class="float-right">
                    <b-button
                        variant="danger"
                        data-dismiss="modal"
                        @click="close"
                    >
                        {{ $t("ID_CLOSE") }}
                    </b-button>
                    <b-button 
                        variant="info"
                        @click="continueImport"
                    >
                        {{ $t("ID_CONTINUE") }}
                    </b-button>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
import api from "./../settings/customCaseList/Api/CaseList";
export default {
    name: "ModalImport",
    data() {
        return {
            data: [],
            validFile: null,
            fileCaseList: null,            
            caseListDuplicate: false,
            pmTableNoFields: false,
            pmTableNoExist: false,
            message: ''
        }
    },
    methods: {
        show() {
            this.caseListDuplicate = false;
            this.$refs["modal-import"].show();
        },
        /**
         * Close table
         */
        close() {
            this.pmTableNoFields = false;
        },
        /**
         * Hide modal import
         */
        hide() {
            this.caseListDuplicate = false;
            this.$refs["modal-import"].hide();
        },
        /**
         * Get the custom list case API
         */
        importCustomCaseList() {
            let that = this;
            this.data.file = this.fileCaseList;
            api.importCaseList(this.data)
            .then((response) => {
                switch (response.data.status) {
                    case 'tableNotExist': // pmTable does not exist
                        that.pmTableNoExist = true;
                        that.message = response.data.message
                        that.$refs["modal-import"].hide();
                        break;
                    case 'duplicateName': // Custom Case List duplicate
                        that.caseListDuplicate = true;
                        that.message = response.data.message
                        that.validFile = null;
                        break;
                    case 'invalidFields': // pmTable differentes columns
                        that.pmTableNoFields = true;
                        that.message = response.data.message
                        that.$refs["modal-import"].hide();
                        break;
                    default: // import without error
                        that.$refs["modal-import"].hide();
                        that.$parent.$refs["table"].getData();
                        break;
                    }
            })
            .catch((e) => {
                console.error(e);
            });
        },
        /**
         * Continue import custom case list
         */
        continueImport() {
            let that = this;
            this.data.file = this.fileCaseList;
            if (this.pmTableNoFields) {
                this.data.continue = 'invalidFields';
            } 
            if (this.caseListDuplicate) {
                this.data.continue = 'duplicateName';
            }
            api.importCaseList(this.data)
            .then((response) => {
                if (response.status === 200) {
                    that.$refs["modal-import"].hide();
                    that.$parent.$refs["table"].getData();
                }
            })
            .catch((e) => {
                console.error(e);
            });
        }
    }
}
</script>