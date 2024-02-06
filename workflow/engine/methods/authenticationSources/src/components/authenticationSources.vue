<template>
    <div>
        <titleSection :title="$root.translation('ID_AUTH_SOURCES')"></titleSection>
        <b-form-group class="float-right">
            <b-button variant="primary" 
                      @click="$refs['as-b-modal-upload-file'].show();">
                <b-icon icon="arrow-up-short" aria-hidden="true"/> {{$root.translation('ID_IMPORT_CONNECTION')}}
            </b-button>&nbsp;
            <b-button variant="success" 
                      @click="$emit('newConnection')">
                <b-icon icon="plus" aria-hidden="true"/> {{$root.translation('ID_NEW_CONNECTION')}}
            </b-button>
        </b-form-group>
        <v-server-table ref="vServerTable1" 
                        :url="baseUrl"
                        :columns="columns"
                        :options="options"
                        :data="tableData">
            <div slot="icons"
                 slot-scope="props">
                <b-button :id="'as-b-button-tooltip-'+props.index"
                          variant="light"
                          size="sm" 
                          class="mb-2"
                          @mousedown="$root.$emit('bv::hide::tooltip');$root.$emit('bv::show::tooltip','as-b-button-tooltip-'+props.index);"
                          @mouseup="$root.$emit('bv::hide::tooltip');$root.$emit('bv::show::tooltip','as-b-button-tooltip-'+props.index);">
                    <b-icon icon="three-dots-vertical" aria-hidden="true"/>
                </b-button>
                <b-tooltip :target="'as-b-button-tooltip-'+props.index" 
                           triggers="hover"
                           custom-class="custom-tooltip"
                           placement="left"
                           variant="light"
                           no-fade>
                    <b-button-group>
                        <b-button @click="importUsers(props.row)"
                                   v-b-tooltip.hover 
                                   :title="$root.translation('ID_IMPORT_USERS')"
                                   variant="light">
                            <b-icon icon="arrow-repeat" aria-hidden="true" variant="success"/>
                        </b-button>
                        <b-button @click="downloadRow(props.row)"
                                   v-b-tooltip.hover 
                                   :title="$root.translation('ID_DOWNLOAD_SETTINGS')"
                                   variant="light">
                            <b-icon icon="arrow-down" aria-hidden="true" variant="info"/>
                        </b-button>
                        <b-button @click="syncGroups(props.row)"
                                   v-b-tooltip.hover 
                                   :title="$root.translation('ID_GROUPS_SYNCHRONIZE')"
                                   variant="light">
                            <b-icon icon="people-fill" aria-hidden="true" variant="warning"/>
                        </b-button>
                        <b-button @click="syncDepartments(props.row)"
                                   v-b-tooltip.hover 
                                   :title="$root.translation('ID_DEPARTMENTS_SYNCHRONIZE')"
                                   variant="light">
                            <b-icon icon="diagram3-fill" aria-hidden="true" variant="warning"/>
                        </b-button>
                        <b-button @click="$emit('editSettings',props.row)"
                                   v-b-tooltip.hover 
                                   :title="$root.translation('ID_EDIT_SETTINGS')"
                                   variant="light">
                            <b-icon icon="pencil-fill" aria-hidden="true" variant="info"/>
                        </b-button>
                        <b-button @click="deleteRow(props.row)"
                                   v-b-tooltip.hover 
                                   :title="$root.translation('ID_DELETE_SETTINGS')"
                                   variant="light">
                            <b-icon icon="trash" aria-hidden="true" variant="danger"/>
                        </b-button>
                    </b-button-group>
                </b-tooltip>
            </div>
        </v-server-table>
        <b-modal ref="as-b-modal-upload-file"
                 :title="$root.translation('ID_UPLOAD_CONNECTION_SETTINGS')"
                 hide-footer
                 size="lg">
            <formUploadSource ref="formUploadSource"
                              @cancel="$refs['as-b-modal-upload-file'].hide();$refs.formUploadSource.reset();"
                              @optionSaveButton="optionSaveButton"
                              @optionUpdateButton="optionUpdateButton"
                              @optionNewButton="optionNewButton">
            </formUploadSource>
        </b-modal>
    </div>
</template>

<script>
    import titleSection from "./titleSection.vue"
    import formUploadSource from "./formUploadSource.vue"
    import axios from "axios"
    export default {
        components: {
            titleSection,
            formUploadSource
        },
        data() {
            return {
                baseUrl: this.$root.baseUrl() + "authSources/authSources_Ajax?action=authSourcesList",
                columns: [
                    "AUTH_SOURCE_NAME",
                    "AUTH_SOURCE_PROVIDER",
                    "AUTH_SOURCE_SERVER_NAME",
                    "AUTH_SOURCE_PORT",
                    "AUTH_SOURCE_ENABLED_TLS_LABEL",
                    "CURRENT_USERS",
                    "icons"
                ],
                options: {
                    headings: {
                        AUTH_SOURCE_NAME: this.$root.translation("ID_NAME"),
                        AUTH_SOURCE_PROVIDER: this.$root.translation("ID_PROVIDER"),
                        AUTH_SOURCE_SERVER_NAME: this.$root.translation("ID_SERVER_NAME"),
                        AUTH_SOURCE_PORT: this.$root.translation("ID_PORT"),
                        AUTH_SOURCE_ENABLED_TLS_LABEL: this.$root.translation("ID_ENABLED_TLS"),
                        CURRENT_USERS: this.$root.translation("ID_ACTIVE_USERS"),
                        icons: ""
                    },
                    sortable: [
                        "AUTH_SOURCE_NAME",
                        "AUTH_SOURCE_PROVIDER",
                        "AUTH_SOURCE_SERVER_NAME",
                        "AUTH_SOURCE_PORT",
                        "AUTH_SOURCE_ENABLED_TLS_LABEL",
                        "CURRENT_USERS"
                    ],
                    filterable: [
                        "AUTH_SOURCE_NAME",
                        "AUTH_SOURCE_PROVIDER",
                        "AUTH_SOURCE_SERVER_NAME",
                        "AUTH_SOURCE_PORT",
                        "AUTH_SOURCE_ENABLED_TLS_LABEL",
                        "CURRENT_USERS"
                    ],
                    texts: {
                        filter: "",
                        filterPlaceholder: this.$root.translation("ID_EMPTY_SEARCH"),
                        count: this.$root.translation("ID_SHOWING_FROM_RECORDS_COUNT"),
                        noResults: this.$root.translation("ID_NO_MATCHING_RECORDS"),
                        loading: this.$root.translation("ID_LOADING_GRID")
                    },
                    perPage: "pageSize" in window ? window.pageSize : 5,
                    perPageValues: [],
                    sortIcon: {
                        is: "glyphicon-sort",
                        base: "glyphicon",
                        up: "glyphicon-chevron-up",
                        down: "glyphicon-chevron-down"
                    },
                    requestKeys: {
                        query: "textFilter"
                    },
                    requestFunction(data) {
                        data.start = (data.page - 1) * data.limit;
                        return axios.get(this.url, {params: data}, {}).catch(function (e) {
                            this.dispatch("error", e);
                        });
                    },
                    responseAdapter(data) {
                        if (!("sources" in data.data)) {
                            data.data.sources = [];
                        }
                        if (!("total_sources" in data.data)) {
                            data.data.total_sources = 0;
                        }

                        return {
                            data: data.data.sources,
                            count: data.data.total_sources
                        };
                    }
                },
                tableData: []
            };
        },
        methods: {
            refresh() {
                this.$refs.vServerTable1.refresh();
            },
            deleteRow(row) {
                this.$root.$emit('bv::hide::tooltip');
                this.$bvModal.msgBoxConfirm(this.$root.translation('ID_ARE_YOU_SURE_TO_DELETE_CONNECTION_PLEASE_CONFIRM', [row.AUTH_SOURCE_NAME]), {
                    title: " ", //is important because title disappear
                    hideHeaderClose: false,
                    okTitle: this.$root.translation('ID_YES'),
                    okVariant: "success",
                    cancelTitle: this.$root.translation('ID_NO'),
                    cancelVariant: "danger"
                }).then(value => {
                    if (value === false) {
                        return;
                    }
                    let formData = new FormData();
                    formData.append("action", "deleteAuthSource");
                    formData.append("auth_uid", row.AUTH_SOURCE_UID);
                    axios.post(this.$root.baseUrl() + "authSources/authSources_Ajax", formData)
                            .then(response => {
                                response;
                                this.refresh();
                            })
                            .catch(error => {
                                error;
                            })
                            .finally(() => {
                            });
                }).catch(err => {
                    err;
                });
            },
            downloadRow(row) {
                this.$root.$emit('bv::hide::tooltip');
                let obj = JSON.parse(JSON.stringify(row)); //is need a object copy
                //compatibility for complement ppsellucianldap
                obj.AUTH_SOURCE_UID = "";
                delete obj.AUTH_SOURCE_PASSWORD;
                delete obj.CURRENT_USERS;
                delete obj["UPPER(RBAC_AUTHENTICATION_SOURCE.AUTH_SOURCE_NAME)"];
                delete obj.AUTH_SOURCE_VERSION;
                delete obj.AUTH_SOURCE_ATTRIBUTES;
                delete obj.AUTH_SOURCE_OBJECT_CLASSES;
                delete obj.AUTH_SOURCE_DATA;
                delete obj.AUTH_SOURCE_ENABLED_TLS_LABEL;
                delete obj.LDAP_PAGE_SIZE_LIMIT;
                if ("AUTH_SOURCE_GRID_ATTRIBUTE" in  obj) {
                    let convert = [];
                    for (let i in obj.AUTH_SOURCE_GRID_ATTRIBUTE) {
                        let data = obj.AUTH_SOURCE_GRID_ATTRIBUTE[i] || {};
                        convert.push({
                            attributeLdap: data.attributeLdap || '',
                            attributeUser: data.attributeUser || '',
                            attributeRole: data.attributeRole || ''
                        });
                    }
                    obj.AUTH_SOURCE_GRID_ATTRIBUTE = convert;
                }
                //creation file
                let name = obj.AUTH_SOURCE_NAME + ".json";
                if (window.navigator.msSaveBlob) {
                    window.navigator.msSaveBlob(new Blob([JSON.stringify(obj)], {type: 'application/octet-stream'}), name);
                    return;
                }
                let a = document.createElement('a');
                document.body.appendChild(a);
                a.href = window.URL.createObjectURL(new Blob([JSON.stringify(obj)], {type: 'application/octet-stream'}));
                a.download = name;
                a.click();
                document.body.removeChild(a);
            },
            importUsers(row) {
                //the return action is in: processmaker/workflow/engine/templates/ldapAdvanced/ldapAdvancedSearch.js
                location.href = this.$root.baseUrl() + "authSources/authSources_SearchUsers?sUID=" + row.AUTH_SOURCE_UID;
            },
            syncGroups(row) {
                //the return action is in: processmaker/workflow/engine/templates/authSources/authSourcesSynchronize.js
                location.href = this.$root.baseUrl() + "authSources/authSourcesSynchronize?authUid=" + row.AUTH_SOURCE_UID + "&tab=synchronizeGroups";
            },
            syncDepartments(row) {
                //the return action is in: processmaker/workflow/engine/templates/authSources/authSourcesSynchronize.js
                location.href = this.$root.baseUrl() + "authSources/authSourcesSynchronize?authUid=" + row.AUTH_SOURCE_UID + "&tab=synchronizeDepartments";
            },
            optionSaveButton(fileContent) {
                this.$refs['as-b-modal-upload-file'].hide();
                this.$emit('optionSaveButton', fileContent);
            },
            optionUpdateButton(fileContent, row) {
                this.$refs['as-b-modal-upload-file'].hide();
                this.$emit('optionUpdateButton', fileContent, row);
            },
            optionNewButton(fileContent) {
                this.$refs['as-b-modal-upload-file'].hide();
                this.$emit('optionNewButton', fileContent);
            }
        }
    }
</script>

<style scoped>
</style>
