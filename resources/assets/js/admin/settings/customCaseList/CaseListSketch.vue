<template>
    <div id="home">
        <ModalPreview ref="modal-preview"></ModalPreview>
        <div class="demo">
            <div class="container">
                <h5>{{ $t("ID_NEW_CASES_LISTS") }} ({{ module.title }})</h5>
                <b-form>
                    <b-row>
                        <b-col cols="6">
                            <b-row>
                                <b-col cols="6">
                                    <b-form-group
                                        id="nameLabel"
                                        :label="$t('ID_NAME')"
                                        label-for="name"
                                    >
                                        <b-form-input
                                            id="name"
                                            v-model="params.name"
                                            :state="isValidName"
                                            :placeholder="
                                                $t('ID_SET_A_CASE_LIST_NAME')
                                            "
                                        ></b-form-input>
                                        <b-form-invalid-feedback
                                            :state="isValidName"
                                        >
                                            {{ $t("ID_REQUIRED_FIELD") }}
                                        </b-form-invalid-feedback>
                                    </b-form-group>
                                </b-col>
                                <b-col cols="6">
                                    <div :class="{ invalid: isValidTable === false }">
                                        <label>{{ $t("ID_PM_TABLE") }}</label>
                                        <multiselect
                                            v-model="pmTable"
                                            :options="pmTablesOptions"
                                            :placeholder="
                                                $t('ID_CHOOSE_OPTION')
                                            "
                                            label="label"
                                            track-by="value"
                                            :show-no-results="false"
                                            @search-change="asyncFind"
                                            @select="onSelect"
                                            :loading="isLoading"
                                            id="ajax"
                                            :limit="10"
                                            :clear-on-select="true"
                                        >
                                        </multiselect>
                                        <label
                                            :class="{
                                                'd-block invalid-feedback': isValidTable === false
                                            }"
                                            v-show="isValidTable === false"
                                            >{{
                                                $t("ID_REQUIRED_FIELD")
                                            }}</label
                                        >
                                    </div>
                                </b-col>
                            </b-row>

                            <b-form-group
                                id="descriptionLabel"
                                :label="$t('ID_DESCRIPTION')"
                                label-for="description"
                            >
                                <b-form-textarea
                                    id="description"
                                    v-model="params.description"
                                    :placeholder="$t('ID_SOME_TEXT')"
                                    rows="1"
                                    max-rows="1"
                                ></b-form-textarea>
                            </b-form-group>
                            <b-row>
                                <b-col cols="11">
                                    <v-client-table
                                        :columns="columns"
                                        v-model="data"
                                        :options="options"
                                        ref="pmTableColumns"
                                    >
                                        <!-- checkbox for each header (prefix column name with h__-->
                                        <template slot="h__selected">
                                            <input
                                                type="checkbox"
                                                @click="selectAllAtOnce()"
                                            />
                                        </template>
                                        <input
                                            slot="selected"
                                            slot-scope="props"
                                            type="checkbox"
                                            v-model="checkedRows"
                                            :checked="props.row.selected"
                                            :value="props.row.field"
                                        />
                                         <div slot="action" slot-scope="props">
                                        <b-button
                                            variant="light"
                                            @click="onAddRow(props.row)"
                                        >
                                            <i
                                                ref="iconClose"
                                                class="fas fa-plus"
                                            ></i>
                                        </b-button>
                                    </div>
                                    </v-client-table>
                                </b-col>
                                <b-col cols="1">
                                    <!-- Control panel -->
                                    <div class="control-panel">
                                        <div class="vertical-center">
                                            <button
                                                type="button"
                                                class="btn btn-light"
                                                @click="assignAll()"
                                                :disabled="isButtonDisabled"
                                            >
                                                <i
                                                    class="fa fa-angle-double-right"
                                                ></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-light"
                                                @click="assignSelected()"
                                                :disabled="isButtonDisabled"
                                            >
                                                <i
                                                    class="fa fa-angle-right"
                                                ></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-light"
                                                @click="unassignSelected()"
                                                :disabled="isButtonDisabled"
                                            >
                                                <i class="fa fa-angle-left"></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-light"
                                                @click="unassignAll()"
                                                :disabled="isButtonDisabled"
                                            >
                                                <i
                                                    class="fa fa-angle-double-left"
                                                ></i>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- End Control panel -->
                                </b-col>
                            </b-row>
                            <b-form-group
                                id="iconLabel"
                                :label="$t('ID_ICON')"
                                label-for="icon"
                            >
                                <icon-picker
                                    @selected="onSelectIcon"
                                    :default="params.iconList"
                                />
                            </b-form-group>
                            <div>
                                <b-form-group
                                    id="menuColor"
                                    :label="$t('ID_MENU_COLOR')"
                                    label-for="icon"
                                >
                                    <verte
                                        :value="params.iconColor"
                                        id="icon"
                                        @input="onChangeColor"
                                        picker="square"
                                        menuPosition="left"
                                        model="hex"
                                    >
                                        <svg viewBox="0 0 50 50">
                                            <rect width="50" height="50" style="stroke-width:6;stroke:rgb(0,0,0)" />
                                        </svg>
                                    </verte>
                                </b-form-group>
                            </div>

                            <div>
                                <b-form-group
                                    id="screenColor"
                                    :label="$t('ID_SCREEN_COLOR_ICON')"
                                    label-for="screen"
                                >
                                    <verte
                                        :value="params.iconColorScreen"
                                        @input="onChangeColorScreen"
                                        picker="square"
                                        menuPosition="left"
                                        model="hex"
                                    >
                                        <svg viewBox="0 0 50 50">
                                            <rect width="50" height="50" style="stroke-width:6;stroke:rgb(0,0,0)" />
                                        </svg>
                                    </verte>
                                </b-form-group>
                            </div>
                        </b-col>
                        <b-col cols="6">
                            <b-form-group
                                id="caseListFieldset"
                                :label="$t('ID_CASE_LIST')"
                            >
                                <v-client-table
                                    :columns="columnsCaseList"
                                    v-model="dataCaseList"
                                    :options="caseListOptions"
                                >
                                    <!-- checkbox for each header (prefix column name with h__-->
                                    <template slot="h__selected">
                                        <input
                                            type="checkbox"
                                            @click="selectAllAtOnceCaseList()"
                                        />
                                    </template>
                                    <input
                                        slot="selected"
                                        slot-scope="props"
                                        type="checkbox"
                                        v-model="checkedRowsCaseList"
                                        :checked="props.row.selected"
                                        :value="props.row.field"
                                    />
                                    <div slot="enableFilter" slot-scope="props">
                                    <b-row>
                                        <b-col cols="6">
                                            <i
                                                ref="iconClose"
                                                class="fas fa-info-circle"
                                                :id="`popover-1-${props.row.field}`"
                                            ></i>
                                            <b-popover
                                                :target="`popover-1-${props.row.field}`"
                                                placement="top"
                                                triggers="hover focus"
                                                :content="searchInfoContent(props.row)"
                                            ></b-popover>
                                        </b-col>
                                        <b-col cols="6">
                                            <b-form-checkbox
                                            v-if="disabledField(props.row.field)"
                                            v-model="enabledFilterRows"
                                            @change="onTongleFilter(props.row.field)" 
                                            name="check-button" 
                                            :checked="props.row.enableFilter"
                                            :value="props.row.field"
                                            switch
                                            disabled
                                            >
                                            </b-form-checkbox>
                                            <b-form-checkbox
                                                v-else
                                                v-model="enabledFilterRows"
                                                @change="onTongleFilter(props.row.field)"
                                                name="check-button"
                                                :checked="props.row.enableFilter"
                                                :value="props.row.field"
                                                switch
                                            >
                                            </b-form-checkbox>
                                        </b-col>
                                    </b-row>  
                                    </div>
                                    <div slot="action" slot-scope="props">
                                        <b-button
                                            variant="light"
                                            @click="onRemoveRow(props.row)"
                                        >
                                            <i
                                                ref="iconClose"
                                                class="fas fa-minus"
                                            ></i>
                                        </b-button>
                                    </div>
                                    <div slot="sort"><i class="fa fa-align-justify handle sort-handle"></i></div>
                                </v-client-table>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <div>
                        <b-button tvariant="danger" @click="onCancel">{{
                            $t("ID_CANCEL")
                        }}</b-button>
                        <b-button variant="outline-primary" @click="showPreview">{{
                            $t("ID_PREVIEW")
                        }}</b-button>
                        <b-button variant="primary" @click="onSubmit">{{
                            $t("ID_SAVE")
                        }}</b-button>
                    </div>
                </b-form>
            </div>
        </div>
    </div>
</template>
<script>
import Multiselect from "vue-multiselect";
import draggable from "vuedraggable";
import eventBus from "../../../home/EventBus/eventBus";
import Api from "./Api/CaseList";
import IconPicker from "../../../components/iconPicker/IconPicker.vue";
import ModalPreview from "../../Modals/ModalPreview.vue";
import _ from 'lodash';

export default {
    name: "CaseListSketh",
    components: {
        draggable,
        Multiselect,
        IconPicker,
        IconPicker,
        ModalPreview,
    },
    props: ["params", "module"],
    data() {
        return {
            enabled: true,
            dragging: false,
            icon: "fas fa-user-cog",
            isLoading: false,
            isButtonDisabled: false,
            isSelected: false,
            isSelectedCaseList: false,
            pmTablesOptions: [],
            checkedRows: [],
            enabledFilterRows: [],
            closedRows: [],
            checkedRowsCaseList: [],
            columns: ["selected", "name", "field", "type", "source", "action"],
            data: [],
            options: {
                headings: {
                    name: this.$i18n.t("ID_NAME"),
                    field: this.$i18n.t("ID_FIELD"),
                    type: this.$i18n.t("ID_TYPE"),
                    source: this.$i18n.t("ID_SOURCE"),
                    action: "",
                },
                sortable: [],
                filterable: true,
                perPage: 1000,
                perPageValues: [],
                texts: {
                    count: "",
                },
                isDraggable: false,
            },
            dataCaseList: [],
            columnsCaseList: [
                "selected",
                "name",
                "field",
                "type",
                "typeSearch",
                "enableFilter",
                "action",
                "sort",
            ],
            caseListOptions: {
                headings: {
                    name: this.$i18n.t("ID_NAME"),
                    field: this.$i18n.t("ID_FIELD"),
                    type: this.$i18n.t("ID_TYPE"),
                    typeSearch: this.$i18n.t("ID_TYPE_OF_SEARCHING"),
                    enableFilter: this.$i18n.t("ID_ENABLE_SEARCH_FILTER"),
                    action: "",
                    sort: "",
                },
                filterable: false,
                perPage: 1000,
                perPageValues: [],
                sortable: [],
                texts: {
                    count: "",
                },
                isDraggable: true,
            },
            defaultCaseList: [],
            isValidName: null,
            isValidTable: null,
            pmTable: null,
            isPreview: false
        };
    },
    computed: {
        validation() {
            return this.params.name !== "";
        },
    },
    mounted() {
        let that = this;
        this.getDefaultColumns(this.module.key);
        if(this.params.id) {
            this.editMode();
        }

        eventBus.$on("sort-case-list", (data) => {
            that.sortCaseList(data);
        });
    },
    methods: {
        /**
         * Prepare search popover info
         * @param {object} row
         * @returns {string}
         */
        searchInfoContent(row) {
            let info = this.$i18n.t("ID_THE_SEARCH_WILL_BE_FROM");
            switch (row.type) {
                case 'integer':
                    info += " " + this.$i18n.t("ID_A_RANGE_OF_VALUES");
                    break;
                case 'string':
                    info += " " + this.$i18n.t("ID_A_TEXT_SEARCH");
                    break;
                case 'date':
                    info += " " + this.$i18n.t("ID_DATE_TO_DATE");
                    break;    
                default:
                    info = this.$i18n.t("ID_NO_SEARCHING_METHOD");
            }
            return  info;
        },
        /**
         * Edit mode handler
         * prepare the datato be rendered
         */
        editMode(){
            let that = this;
            this.pmTable = {
                label: this.params.tableName,
                value: this.params.tableUid
            }   
            this.data =this.params.columns.filter(elem => elem.set === false);
            this.dataCaseList =this.params.columns.filter(elem => elem.set === true);
            this.dataCaseList.forEach(function (value) {
                //Force to false in process_category & process_name
                if (value.enableFilter && !that.disabledField(value.field)) {
                    that.enabledFilterRows.push(value.field);
                }
            });
        },
        /**
         * Select all checkbox handler into available pm tables column list
         */
        selectAllAtOnce() {
            let length = this.data.length;
            this.isSelected = !this.isSelected;
            this.checkedRows = [];
            for (let i = 0; i < length; i++) {
                this.data[i].selected = this.isSelected;
                if (this.isSelected) {
                    this.checkedRows.push(this.data[i].field);
                }
            }
        },
        /**
         * Select all checkbox handler into case list table
         */
        selectAllAtOnceCaseList() {
            let length = this.dataCaseList.length;
            this.isSelectedCaseList = !this.isSelectedCaseList;
            this.checkedRowsCaseList = [];
            for (let i = 0; i < length; i++) {
                this.dataCaseList[i].selected = this.isSelectedCaseList;
                if (this.isSelectedCaseList) {
                    this.checkedRowsCaseList.push(this.dataCaseList[i].field);
                }
            }
        },
        /**
         * Unassign the selected columns from custm list
         */
        unassignSelected() {
            let temp;
            let length = this.checkedRowsCaseList.length;
            for (let i = 0; i < length; i++) {
                temp = this.dataCaseList.find(
                    (x) => x.field === this.checkedRowsCaseList[i]
                );
                temp["set"] = false;
                this.data.push(temp);
                this.dataCaseList = this.dataCaseList.filter((item) => {
                    return item.field != this.checkedRowsCaseList[i];
                });
            }
            this.checkedRowsCaseList = [];
        },
        /**
         *  Unassign all columns from custom list
         */
        unassignAll() {
            this.data = [...this.data, ...this.dataCaseList];
              this.data.forEach(function (element) {
                element.set = false;
            });
            this.dataCaseList = [];
        },
        /**
         * Assign the selected row to custom list
         */
        assignSelected() {
            let temp;
            let length = this.checkedRows.length;
            for (let i = 0; i < length; i++) {
                temp = this.data.find((x) => x.field === this.checkedRows[i]);
                temp["set"] = true;
                this.dataCaseList.push(temp);
                this.data = this.data.filter((item) => {
                    return item.field != this.checkedRows[i];
                });
            }
            this.checkedRows = [];
        },
        /**
         * Assign all columns to custom list
         */
        assignAll() {
            this.dataCaseList = [...this.dataCaseList, ...this.data];
            this.dataCaseList.forEach(function (element) {
                element.set = true;
            });
            this.data = [];
        },
        /**
         * On select icon handler
         */
        onSelectIcon(data) {
            this.params.iconList = data;
        },
        /**
         * On change color handler
         */
        onChangeColor(color) {
            this.params.iconColor = color;
        },
        /**
         * On change color screen handler
         */
        onChangeColorScreen(color) {
            this.params.iconColorScreen = color;
        },
        /**
         * On Cancel event handler
         */
        onCancel() {
            this.$emit("closeSketch");
        },
        /**
         * Find asynchronously in the server
         * @param {string} query - string from the text field
         */
        asyncFind(query) {
            let self = this;
            this.isLoading = true;
            self.processes = [];
            self.pmTablesOptions = [];
            Api.reportTables({
                search: query,
            })
            .then((response) => {
                self.processes = [];
                _.forEach(response.data, function(elem, key) {
                    self.pmTablesOptions.push({
                        label: elem.name,
                        value: elem.uid,
                        fields: elem.fields,
                    });
                });

                this.isLoading = false;
            })
            .catch((err) => {
                console.error(err);
            });
        },
        /**
         * On select event handler in multiselect component
         * @param {object} option
         */
        onSelect(option) {
            this.checkedRows = [];
            this.data = option.fields;
            this.dataCaseList = this.defaultCaseList;
        },
        /**
         * On remove row event handler
         * @param {object} row
         */
        onRemoveRow(row) {
            var temp = this.dataCaseList.find((x) => x.field === row.field);
            if (temp) {
                temp["set"] = false;
                this.data.push(temp);
                this.dataCaseList = this.dataCaseList.filter((item) => {
                    return item.field != row.field;
                });
            }
        },
        /**
         * On remove row event handler
         * @param {object} row
         */
        onAddRow(row) {
            var temp = this.data.find((x) => x.field === row.field);
            if (temp) {
                temp["set"] = true;
                this.dataCaseList.push(temp);
                this.data = this.data.filter((item) => {
                    return item.field != row.field;
                });
            }
        },
        /**
         * On submit event handler
         */
        onSubmit() {
            let that = this;
            this.isValidName = true;
            this.isValidTable = true;
            if (!this.params.name) {
                this.isValidName = false;
                return;
            }
            if (!this.pmTable) {
                this.isValidTable = false;
                return;
            }
            this.params.tableUid = this.pmTable.value;
            this.params.columns = [...this.preparePostColumns(this.dataCaseList), ...this.preparePostColumns(this.data)];
            this.params.type = this.module.key;
            this.params.userId = window.config.userId;
            if (this.params.id) {
                delete this.params["tableName"];
                Api.updateCaseList(this.params)
                .then((response) => {
                    if (that.isPreview) {
                        that.$refs["modal-preview"].columns = that.getColumns();
                        that.$refs["modal-preview"].type = that.params.type;
                        that.$refs["modal-preview"].customCaseId = that.params.id;
                        that.$refs["modal-preview"].show();
                        that.isPreview = false;
                    } else {
                        this.$emit("closeSketch");
                    }
                })
                .catch((err) => {
                    this.makeToast('danger', this.$i18n.t('ID_ERROR'), err.response.statusText);
                    console.error(err);
                });
            } else {
                Api.createCaseList(this.params)
                .then((response) => {
                    if (that.isPreview) {
                        that.params.id = response.data.id;
                        that.$refs["modal-preview"].columns = that.getColumns();
                        that.$refs["modal-preview"].type = that.params.type;
                        that.$refs["modal-preview"].customCaseId = that.params.id;
                        that.$refs["modal-preview"].show();
                        that.isPreview = false;
                    } else {
                        this.$emit("closeSketch");
                    }
                })
                .catch((err) => {
                    this.makeToast('danger',this.$i18n.t('ID_ERROR') ,err.response.statusText);
                    console.error(err);
                });
            }
        },
        /**
         * Prepares columns data to be sended to the server
         * @param {array} collection
         */
        preparePostColumns(collection){
            let temp = [];
            collection.forEach(function (value) {
                temp.push({
                    field: value.field,
                    enableFilter: value.enableFilter || false,
                    set: value.set || false
                })  
            });
            return temp;
        },
        /**
         * Tongle filter switcher
         * @param {string} field
         */
        onTongleFilter(field){
            let objIndex = this.dataCaseList.findIndex((obj => obj.field === field));
            this.dataCaseList[objIndex].enableFilter = !this.dataCaseList[objIndex].enableFilter
        },
        /**
         * Make the toast component
         * @param {string} variant
         * @param {string} title
         * @param {string} message
         */
        makeToast(variant = null, title, message) {
            this.$bvToast.toast(message, {
                title: `${title || variant}`,
                variant: variant,
                solid: true
            })
        },
        /**
         * Get default Columns
         * @param {string} type
         */
        getDefaultColumns(type) {
            let that = this;
            Api.getDefault(type)
            .then((response) => {
                if (!that.params.columns) {
                    that.dataCaseList = response.data;
                }
                that.defaultCaseList = response.data;
            })
            .catch((e) => {
                console.error(e);
            })
        },
        /**
         * Show modal preview
         */
        showPreview() {
            this.isPreview = true;
            this.onSubmit();
        },
        /**
         * Get columns to show in the preview
         */
        getColumns() {
            var columns = [],
                auxColumn,
                i;
            for (i = 0; i < this.dataCaseList.length; i += 1) {
                auxColumn = this.dataCaseList[i];
                if (auxColumn.set) {
                    columns.push(auxColumn.field);
                }
            }
            columns.push('actions');
            columns.unshift('detail');
            return columns
        },
        disabledField(field){
            const fields = [ "due_date" , "process_category" , "process_name" , "priority" ];
            return !(fields.indexOf(field) == -1);
        },
        sortCaseList(data) {
            let auxList = _.cloneDeep(this.dataCaseList);
            let aux = auxList.splice(data.oldIndex, 1);
            auxList.splice(data.newIndex, 0, aux[0]);
            this.dataCaseList = auxList;
        }
    },
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
.verte {
    position: relative;
    display: flex;
    justify-content: normal;
}
.control-panel {
    height: 100%;
    width: 8%;
    float: left;
    position: relative;
}
.vertical-center {
    margin: 0;
    position: absolute;
    top: 50%;
    -ms-transform: translateY(-50%);
    transform: translateY(-50%);
}
.vertical-center > button {
    width: 70%;
    margin: 5px;
}
.invalid .multiselect__tags {
    border-color: #f04124;
}

.invalid .typo__label {
    color: #f04124;
}
.sort-handle {
    color: gray
}
</style>
