<template>
    <div>
        <b-modal
            ref="modal-delete-list"
            hide-footer
            size="md"
        >
            <template v-slot:modal-title>
                {{ $t('ID_DELETE_CUSTOM_CASE_LIST') }}
            </template>
            <b-container fluid>
                <p>
                    {{ $t("ID_ARE_YOU_SURE_DELETE_CUSTOM_CASE_LIST", {'CUSTOM_NAME': data.name})  }}
                </p>
            </b-container>
            <div class="modal-footer">
                <div class="float-right">
                    <b-button
                        variant="danger"
                        data-dismiss="modal"
                        @click="hide"
                    >
                        {{ $t("ID_NO") }}
                    </b-button>
                    <b-button 
                        variant="success" 
                        @click="deleteCustomCaseList"
                    >
                        {{ $t("ID_YES") }}
                    </b-button>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
import api from "./../settings/customCaseList/Api/CaseList";
export default {
    name: "ModalDeleteCaseList",
    data() {
        return {
            data: {
                name: null
            }
        }
    },
    methods: {
        show() {
            this.$refs["modal-delete-list"].show();
        },
        hide() {
            this.$refs["modal-delete-list"].hide();
        },
        deleteCustomCaseList() {
            let that = this;
            api.deleteCaseList(this.data).then((response) => { 
                if (response.statusText === "OK" || response.status === 200) {
                    that.$refs["modal-delete-list"].hide();
                    that.$parent.$refs["table"].getData();
                }
            });
        }
    }
}
</script>