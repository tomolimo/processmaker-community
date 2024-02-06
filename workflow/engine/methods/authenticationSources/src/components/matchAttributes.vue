<template>
    <div>
        <titleSection :title="$root.translation('ID_MATCH_ATTRIBUTES')"></titleSection>
        <b-form-group class="text-right">
            <b-link href="#" @click="connectionSettings"> &lt;&lt; {{$root.translation('ID_CONNECTION_SETTINGS')}}</b-link>
        </b-form-group>
        <b-form-group class="float-right">
            <b-button variant="success" @click="addAttribute">{{$root.translation('ID_ADD_ATTRIBUTE')}}</b-button>
        </b-form-group>
        <v-client-table :columns="columns"
                        :options="options"
                        :data="rows">
            <div slot="icons"
                 slot-scope="props">
                <b-button-group>
                    <b-button @click="editAttribute(props.row,props.index)"
                               v-b-tooltip.hover 
                               :title="$root.translation('ID_EDIT_ATTRIBUTE')"
                               variant="light">
                        <b-icon icon="pencil-fill" aria-hidden="true" variant="info"/>
                    </b-button>
                    <b-button @click="deleteAttribute(props.index,props.row)"
                               v-b-tooltip.hover 
                               :title="$root.translation('ID_DELETE_ATTRIBUTE')"
                               variant="light">
                        <b-icon icon="trash" aria-hidden="true" variant="danger"/>
                    </b-button>
                </b-button-group>
            </div>
        </v-client-table>
        <b-modal id="messageForDeleteAttribute"
                 @ok="deleteAttributeProcess"
                 :ok-title="$root.translation('ID_YES')"
                 ok-variant="success"
                 :cancel-title="$root.translation('ID_NO')"
                 cancel-variant="danger">
            {{$root.translation('ID_ARE_YOU_SURE_TO_DELETE_ATTRIBUTE_PLEASE_CONFIRM',[selectedRowName])}}
        </b-modal>
    </div>
</template>

<script>
    import titleSection from "./titleSection.vue"
    export default {
        components: {
            titleSection
        },
        data() {
            return {
                selectedRowIndex: 0,
                selectedRowName: "",
                columns: [
                    "attributeRole",
                    "attributeUser",
                    "attributeLdap",
                    "icons"
                ],
                options: {
                    headings: {
                        attributeRole: this.$root.translation('ID_ROLE'),
                        attributeUser: this.$root.translation('ID_USER_FIELD'),
                        attributeLdap: this.$root.translation('ID_LDAP_FIELD'),
                        icons: ""
                    },
                    sortable: [
                        "attributeLdap",
                        "attributeRole",
                        "attributeUser"
                    ],
                    filterable: [
                        "attributeLdap",
                        "attributeRole",
                        "attributeUser"
                    ],
                    texts: {
                        filter: "",
                        filterPlaceholder: this.$root.translation("ID_EMPTY_SEARCH"),
                        count: this.$root.translation("ID_SHOWING_FROM_RECORDS_COUNT"),
                        noResults: this.$root.translation("ID_NO_MATCHING_RECORDS"),
                        loading: this.$root.translation("ID_LOADING_GRID")
                    },
                    perPage: 5,
                    perPageValues: [],
                    sortIcon: {
                        is: "glyphicon-sort",
                        base: "glyphicon",
                        up: "glyphicon-chevron-up",
                        down: "glyphicon-chevron-down"
                    }
                },
                rows: []
            };
        },
        methods: {
            setRows(rows) {
                this.rows = rows;
            },
            addAttribute() {
                this.$emit("addAttribute");
            },
            editAttribute(row, index) {
                this.$emit("editAttribute", row, index);
            },
            deleteAttribute(index, row) {
                this.selectedRowName = row.attributeLdap;
                this.selectedRowIndex = index;
                this.$bvModal.show("messageForDeleteAttribute");
            },
            deleteAttributeProcess() {
                this.rows.splice(this.selectedRowIndex - 1, 1);
            },
            saveRow(object) {
                let obj = Object.assign({}, object);//important to clone the object
                if (obj.index === null) {
                    delete obj.index;
                    this.rows.push(obj);
                } else {
                    let i = obj.index;
                    delete obj.index;
                    Object.assign(this.rows[i - 1], obj);
                }
            },
            connectionSettings() {
                this.$emit('connectionSettings', this.rows);
            }
        }
    }
</script>

<style scoped>
</style>
