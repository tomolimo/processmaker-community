<template>
<div id="people">
    <ModalDeleteCaseList ref="modal-delete-list"></ModalDeleteCaseList>
    <ModalPreview ref="modal-preview"></ModalPreview>
    <ModalImport ref="modal-import"></ModalImport>
    <button-fleft :data="newList"></button-fleft>
    <button-fleft :data="importList"></button-fleft>
    <v-server-table 
        :data="tableData"
        :columns="columns"
        :options="options"
        ref="table" 
    >
        <div slot="actions" slot-scope="props">
            <ellipsis :data="updateDataEllipsis(props.row)"> </ellipsis>
        </div>
        <div slot="owner" slot-scope="props">
            <OwnerCell :data="props.row.owner" />
        </div>
    </v-server-table>
</div>
</template>
<script>
import Api from "./Api/CaseList";
import ButtonFleft from "../../../components/home/ButtonFleft.vue";
import Ellipsis from "../../../components/utils/ellipsis.vue";
import utils from "../../../utils/utils";
import OwnerCell from "../../../components/vuetable/OwnerCell";
import ModalDeleteCaseList from "./../../Modals/ModalDeleteCaseList.vue";
import ModalPreview from "./../../Modals/ModalPreview.vue";
import ModalImport from "./../../Modals/ModalImport.vue";
import download from "downloadjs";

export default {
    name: "Tables",
    props: ["module"],
    components: {
        ButtonFleft,
        Ellipsis,
        OwnerCell,
        ModalDeleteCaseList,
        ModalPreview,
        ModalImport,
    },
    data() {
        return {
            newList: {
                title: this.$i18n.t("New List"),
                class: "btn-success",
                onClick: () => {
                    this.$emit("showSketch", {
                        name: "",
                        description: "",
                        tableUid: "",
                        iconList: "far fa-check-circle",
                        iconColor: '#000000',
                        iconColorScreen: '#FFFFFF',
                        type: this.module

                    });

                    //TODO button
                }
            },
            importList: {
                title: this.$i18n.t("Import List"),
                class: "btn-success",   
                onClick: () => {
                    this.importCustomCaseList();
                }
            },
            columns: [
                "name",
                "process",
                "tableName",
                "owner",
                "createDate",
                "updateDate",
                "actions"
            ],
            tableData: [],
            options: {
                filterable: true,
                pagination: { 
                    chunk: 3,
                    nav: 'scroll',
                    edge: true
                },
                headings: {
                    name: this.$i18n.t("ID_NAME"),
                    process: this.$i18n.t("ID_PROCESS"),
                    tableName: this.$i18n.t("ID_PM_TABLE"),
                    owner: this.$i18n.t("ID_OWNER"),
                    createDate: this.$i18n.t("ID_DATE_CREATED"),
                    updateDate: this.$i18n.t("ID_DATE_UPDATED"),
                    actions: ""
                },
                texts: {
                    count: this.$i18n.t("ID_SHOWING_FROM_RECORDS_COUNT"),
                    first: "<<",
                    last: ">>",
                    filter: this.$i18n.t("ID_FILTER") + ":",
                    limit: this.$i18n.t("ID_RECORDS") + ":",
                    page: this.$i18n.t("ID_PAGE") + ":",
                    noResults: this.$i18n.t("ID_NO_MATCHING_RECORDS"),
                },
                requestFunction(data) {
                    return this.$parent.$parent.getCasesForVueTable(data);
                },
           
            },
            customColumns: [],
        };
    },
    methods: {
        /**
         * Get cases data by module
         * @param {object} datas
         * @returns {object}
         */
        getCasesForVueTable(data) {
            let that = this,
                dt,
                paged,
                limit = data.limit,
                start = data.page === 1 ? 0 : limit * (data.page - 1),
                filters = {};
            filters = {
                offset: start,
                limit: limit
            };
            if (data && data.query) {
                filters["search"] = data.query;
            }
            _.forIn(this.filters, function (item, key) {
                if(filters && item.value) {
                    filters[item.filterVar] = item.value;
                }
            });
            return new Promise((resolutionFunc, rejectionFunc) => {
                Api.getCaseList(filters, that.module)
                .then((response) => {      
                    dt = that.formatDataResponse(response.data.data); 
                    resolutionFunc({
                        data: dt,        
                        count: response.data.total
                    });
                })
                .catch((e) => {
                    rejectionFunc(e);
                });
            });
        },
        /**
         * Format Response API TODO to grid inbox and columns
         * @param {object} response
         * @returns {object}
         */
        formatDataResponse(response){
             let that = this,
                data = [],
                userDataFormat;  
            _.forEach(response, (v) => {
                userDataFormat = utils.userNameDisplayFormat({
                        userName: v.userName || "",
                        firstName: v.userFirstname || "",
                        lastName: v.userLastname || "",
                        format: window.config.FORMATS.format || null
                    });
                v["owner"] =    {
                    userAvatar: userDataFormat !== "" ? window.config.SYS_SERVER_AJAX +
                            window.config.SYS_URI +
                            `users/users_ViewPhotoGrid?pUID=${v.userId}` : "",
                    userInfo: userDataFormat || "",
                    userEmail: v.userEmail,
                    userId: v.userId,
                    userPosition: v.userPosition || "",
                    caseListId: v.id
                }
                data.push(v);
            });
            return data;
        },
        /**
         * Show modal to delete a custom case list
         * @param {object} data
         */
        showModalDelete(data) {
            this.$refs["modal-delete-list"].data = data;
            this.$refs["modal-delete-list"].show();
        },
        /**
         * Show modal preview
         * @param {object} data
         */
        showPreview(data) {
            this.$refs["modal-preview"].columns = this.getColumns(data);
            this.$refs["modal-preview"].type = data.type;
            this.$refs["modal-preview"].customCaseId = data.id;
            this.$refs["modal-preview"].show();
        },
        /**
         * Get columns to show in the preview
         * @param {Object} data
         * @returns {Array} columns
         */
        getColumns(data) {
            var columns = [],
                auxColumn,
                i;
            for (i = 0; i < data.columns.length; i += 1) {
                auxColumn = data.columns[i];
                if (auxColumn.set) {
                    columns.push(auxColumn.field);
                }
            }
            columns.push('actions');
            columns.unshift('detail');
            return columns
        },
        editCustomCaseList(data) {
            this.$emit("showSketch", {
                id: data.id,
                name: data.name,
                description: data.description,
                tableUid: data.tableUid,
                tableName: data.tableName,
                iconList: data.iconList,
                iconColor: data.iconColor,
                iconColorScreen: data.iconColorScreen,
                columns: data.columns,
                enableFilter: data.enableFilter,
                type: this.module
            });
        },
        /**
         * Export the Custom Case List in a json
         * @param {object} data
         */
        downloadCaseList(data) {
            var fileName = data.name,
                typeMime = "text/plain",
                dataExport = [];
            dataExport = this.filterDataToExport(data);
            download(JSON.stringify(dataExport), fileName + ".json", typeMime);
        },
        /**
         * Filter the sensible information to export
         * @param {Array} data
         */
        filterDataToExport(data) {
            var dataExport;
            dataExport = {
                type: data['type'],
                name: data['name'],
                description: data['description'],
                tableUid: data['tableUid'],
                tableName: data['tableName'],
                columns: data['columns'],
                userId: data['userId'],
                iconList: data['iconList'],
                iconColor: data['iconColor'],
                iconColorScreen: data['iconColorScreen'],
                createDate: data['createDate'],
                updateDate: data['updateDate']
            };
            return dataExport;
        },
        /**
        * Show options in the ellipsis 
        * @param {objec} data
        */
        updateDataEllipsis(data) {
            let that = this;
            return {
                APP_UID: data.id,
                buttons: {
                    note: {
                        name: "edit",
                        icon: "far fa-edit",
                        fn: function() {
                            that.editCustomCaseList(data);
                        }
                    },
                    open: {
                        name: "delete",
                        icon: "far fa-trash-alt",
                        color: "red",
                        fn: function() {
                            that.showModalDelete(data);
                        }
                    },
                    reassign: {
                        name: "download",
                        icon: "fas fa-arrow-circle-down",
                        fn: function() {
                            that.downloadCaseList(data);
                        }
                    },
                    preview: {
                        name: "preview",
                        icon: "fas fa-tv",
                        color: "green",
                        fn: function() {
                            that.showPreview(data);
                        }
                    }
                }
            }
        },
        importCustomCaseList() {
            this.$refs["modal-import"].show();
        }
    }
};
</script>
<style>
.VueTables__row {
  height: 75px;
}
.float-right {
    padding-left: 1.5%;
}
</style>