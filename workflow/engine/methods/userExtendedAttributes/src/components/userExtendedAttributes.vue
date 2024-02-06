<template>
    <div>
        <titleSection :title="$root.translation('ID_USER_EXTENDED_ATTRIBUTES')"></titleSection>
        <b-form-group class="float-right">
            <b-button variant="success"
                      @click="newAttribute">
                <b-icon icon="plus" aria-hidden="true"/> {{$root.translation('ID_NEW_ATTRIBUTE')}}
            </b-button>
        </b-form-group>
        <v-server-table ref="vServerTable1"
                        :url="$root.baseUrl()+'userExtendedAttributes/index?option=list'"
                        :columns="columns"
                        :options="options">
            <div slot="roles"
                 slot-scope="props">
                {{formatingRoles(props.row)}}
            </div>
            <div slot="owner"
                 slot-scope="props">
                {{props.row.owner}}
                <b-avatar :id="'as-b-avatar-tooltip-'+props.index" 
                          size="sm" 
                          class="float-right">
                </b-avatar>
                <b-tooltip :target="'as-b-avatar-tooltip-'+props.index" 
                           triggers="hover" 
                           custom-class="custom-tooltip" 
                           placement="right"
                           variant="info">
                    <b-container fluid>
                        <b-row class="text-left">
                            <b-col>
                                {{props.row.usrUsername}}<br>
                                {{props.row.usrFirstname}} {{props.row.usrLastname}}<br>
                                {{props.row.usrEmail}}<br>
                                {{props.row.usrFax}}<br>
                                {{props.row.usrCellular}}<br>
                                {{props.row.usrTimeZone}}<br>
                            </b-col>
                            <b-col>
                                <b-avatar size="lg"
                                          variant="dark">
                                </b-avatar>
                            </b-col>
                        </b-row>
                    </b-container>
                </b-tooltip>
            </div>
            <div slot="icons"
                 slot-scope="props">
                <b-button-group>
                    <b-button :title="$root.translation('ID_EDIT_ATTRIBUTE')"
                              v-b-tooltip.hover
                              variant="light"
                              @click="editAttribute(props.row)">
                        <b-icon icon="pencil-fill" aria-hidden="true" variant="info"/>
                    </b-button>
                    <b-button :title="$root.translation('ID_DELETE_ATTRIBUTE')"
                              v-b-tooltip.hover
                              variant="light"
                              @click="deleteAttribute(props.row)">
                        <b-icon icon="trash" aria-hidden="true" variant="danger"/>
                    </b-button>
                </b-button-group>
            </div>
        </v-server-table>
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
                columns: [
                    "name",
                    "attributeId",
                    "roles",
                    "owner",
                    "dateCreate",
                    "icons"
                ],
                options: {
                    headings: {
                        name: this.$root.translation('ID_ATTRIBUTE_NAME'),
                        attributeId: this.$root.translation('ID_ATTRIBUTE'),
                        roles: this.$root.translation('ID_ROLE'),
                        owner: this.$root.translation('ID_OWNER'),
                        dateCreate: this.$root.translation('ID_PRO_CREATE_DATE'),
                        icons: ""
                    },
                    sortable: [
                        "name",
                        "attributeId",
                        "roles",
                        "dateCreate"
                    ],
                    filterable: [
                        "name",
                        "attributeId",
                        "roles",
                        "dateCreate"
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
                    requestFunction(data) {
                        data.start = (data.page - 1) * data.limit;
                        return axios.get(this.url, {params: data}, {}).catch(function (e) {
                            this.dispatch("error", e);
                        });
                    },
                    responseAdapter(data) {
                        return {
                            data: data.data.data,
                            count: data.data.count
                        };
                    }
                }
            };
        },
        methods: {
            newAttribute() {
                this.$emit("newAttribute");
            },
            editAttribute(row) {
                this.$emit("editAttribute", row);
            },
            deleteAttribute(row) {
                let formData = new FormData();
                formData.append("option", "verifyAttributeUse");
                formData.append("name", row.name);
                formData.append("attributeId", row.attributeId);
                axios.post(this.$root.baseUrl() + "userExtendedAttributes/index", formData)
                        .then(response => {
                            response;
                            let message = this.$root.translation('ID_THE_ATTRIBUTE_WILL_BE_DELETED_PLEASE_CONFIRM', [row.name]);
                            if ("isUsed" in response.data && "message" in response.data) {
                                if (response.data.isUsed === true) {
                                    message = response.data.message;
                                }
                            }
                            this.$bvModal.msgBoxConfirm(message, {
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
                                let formData = new FormData();
                                formData.append("option", "delete");
                                formData.append("id", row.id);
                                axios.post(this.$root.baseUrl() + "userExtendedAttributes/index", formData)
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
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
            },
            refresh() {
                this.$refs.vServerTable1.refresh();
            },
            formatingRoles(row) {
                if (row.option === "allUser") {
                    return this.$root.translation("ID_ALL_USERS");
                }
                if (row.option === "byRol") {
                    return row.rolesLabel.join(", ");
                }
                return "";
            }
        }
    }
</script>

<style scoped>
</style>
