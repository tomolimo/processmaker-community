<template>
    <div>
        <SearchPopover
            target="popover-target-1"
            @savePopover="onOk"
            :title="addSearchTitle"
        >
            <template v-slot:body>
                <b-form-group label-for="myCasesFilter">
                    <b-form-radio-group
                        v-model="selected"
                        :options="filterItems"
                        value-field="id"
                        text-field="optionLabel"
                        name="flavour-2a"
                        stacked
                    ></b-form-radio-group>
                </b-form-group>
                <b-form-checkbox
                    id="checkbox-2"
                    v-model="byProcessCategory"
                    name="checkbox-2"
                    value="processCategory"
                >
                    {{$t('ID_BY_PROCESS_CATEGORY') }}
                </b-form-checkbox>
                <b-form-checkbox
                    id="checkbox-1"
                    v-model="byProcessName"
                    name="checkbox-1"
                    value="processName"
                >
                    {{$t('ID_BY_PROCESS_NAME') }}
                </b-form-checkbox>  
            </template>
        </SearchPopover>

    <div class="p-1 filter-field">
        <h5 class="v-search-title">{{ title }}</h5>
        <div class="pm-mc-text-icon">
            <i :class="icon"></i>
        </div>
        <b-input-group class="w-75 p-1">
            <div class="input-group-tag mb-3">
                <div class="input-group-prepend">
                    <span
                        class="input-group-text bg-primary-pm text-white"
                        id="popover-target-1"
                        @click="searchClickHandler"
                    >
                        <b-icon icon="search"></b-icon>
                    </span>
                    <b-tooltip target="popover-target-1">{{$t('ID_MY_CASES_SEARCH')}}</b-tooltip>
                </div>
            <b-form-tags
                input-id="tags-pills"
                v-model="searchTags"
                :disabled="true"
                v-if="filters.length > 0"
            >
                <template v-slot="{ tags, tagVariant, removeTag }" >
                    <div class="d-inline-block" style="font-size: 1rem">
                        <b-form-tag
                            v-for="tag in tags"
                            @remove="customRemove(removeTag, tag)"
                            :key="tag"
                            :title="tag"
                            :variant="tagVariant"
                            class="mr-1 badge badge-light"
                        >
                            <div :id="tag">
                                <i class="fas fa-tags"></i>
                                {{ tagContent(tag) }}
                            </div>

                            <component
                                :filters="filters"
                                v-bind:is="tagComponent(tag)"
                                v-bind:info="tagInfo(tag)"
                                v-bind:tag="tag"
                                v-bind:filter="dataToFilter(tag)"
                                @updateSearchTag="updateSearchTag"
                            />
                        </b-form-tag>
                    </div>
                </template>
            </b-form-tags>
        </div>
      </b-input-group>
    </div>
    </div>
</template>

<script>
import SearchPopover from "./popovers/SearchPopover.vue";
import CaseNumber from "./popovers/CaseNumber.vue";
import CaseTitle from "./popovers/CaseTitle.vue";
import ProcessName from "./popovers/ProcessName.vue";
import ProcessCategory from "./popovers/ProcessCategory.vue";
import DateFilter from "./popovers/DateFilter.vue";
import TaskTitle from "./popovers/TaskTitle.vue";
import api from "./../../api/index";

export default { 
    name: "MyCasesFilter",
    props: ["filters","title", "icon"],
    components:{
        SearchPopover,
        CaseNumber,
        CaseTitle,
        ProcessName,
        ProcessCategory,
        DateFilter,
        TaskTitle
    },
    data() {
        return {
            searchLabel: this.$i18n.t('ID_SEARCH'),
            addSearchTitle: this.$i18n.t('ID_ADD_SEARCH_FILTER_CRITERIA'),
            searchTags: [],
            filterItems: [
                {   
                    type: "CaseNumber",
                    id: "caseNumber",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_CASE_NUMBER')}`,
                    optionLabel: this.$i18n.t('ID_BY_CASE_NUMBER'),
                    detail: this.$i18n.t('ID_PLEASE_SET_THE_CASE_NUMBER_TO_BE_SEARCHED'),
                    tagText: "",
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_CASE_NUMBER'),
                    items:[
                        {
                            id: "filterCases",
                            value: ""
                        }
                    ],
                    autoShow: true,
                    makeTagText: function (params, data) {
                          return  `${params.tagPrefix}: ${data[0].value}`;
                    }
                },
                {
                    type: "CaseTitle",
                    id: "caseTitle",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_CASE_THREAD_TITLE')}`,
                    optionLabel: this.$i18n.t('ID_BY_CASE_THREAD_TITLE'),
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_CASE_THREAD_TITLE'),
                    detail: "",
                    tagText: "",
                    items:[
                        {
                            id: "caseTitle",
                            value: ""
                        }
                    ],
                    autoShow: true,
                    makeTagText: function (params, data) {
                        return  `${this.tagPrefix} ${data[0].value}`;
                    }
                }, 
                {
                    type: "TaskTitle",
                    id: "taskTitle",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_TASK')}`,
                    optionLabel: this.$i18n.t('ID_BY_TASK'),
                    detail: "",
                    tagText: "",
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_TASK_NAME'),
                    autoShow: true,
                    items:[
                        {
                            id: "task",
                            value: "",
                            options: [],
                            placeholder: this.$i18n.t('ID_TASK_NAME')
                        }
                    ],
                    makeTagText: function (params, data) {
                        return  `${this.tagPrefix} ${data[0].label || ''}`;
                    }
                },
                {
                    type: "DateFilter",
                    id: "startDate",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_START_DATE')}`,
                    optionLabel: this.$i18n.t('ID_BY_START_DATE'),
                    detail: this.$i18n.t('ID_PLEASE_SET_A_RANGE_OF_CASES_START_DATE_TO_SEARCH'),
                    tagText: "",
                    autoShow: true,
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_START_DATE'),
                    items:[
                        {
                            id: "startCaseFrom",
                            value: "",
                            label: this.$i18n.t('ID_FROM_START_DATE')
                        },
                        {
                            id: "startCaseTo",
                            value: "",
                            label: this.$i18n.t('ID_TO_START_DATE')
                        }
                    ],
                    makeTagText: function (params, data) {
                        return  `${params.tagPrefix} ${data[0].value} - ${data[1].value}`;
                    }
                },
                {
                    type: "DateFilter",
                    id: "finishDate",
                    title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_FINISH_DATE')}`,
                    optionLabel: this.$i18n.t('ID_BY_FINISH_DATE'),
                    detail: this.$i18n.t('ID_PLEASE_SET_A_RANGE_OF_CASES_FINISH_DATE_TO_SEARCH'),
                    tagText: "",
                    autoShow: true,
                    tagPrefix:  this.$i18n.t('ID_SEARCH_BY_FINISH_DATE'),
                    items:[
                        {
                            id: "finishCaseFrom",
                            value: "",
                            label: this.$i18n.t('ID_FROM_FINISH_DATE'),
                        },
                        {
                            id: "finishCaseTo",
                            value: "",
                            label: this.$i18n.t('ID_TO_FINISH_DATE'),
                        }
                    ],
                    makeTagText: function (params, data) {
                        return  `${params.tagPrefix} ${data[0].value} - ${data[1].value}`;
                    }
                },
            ],
            processName: {
                type: "ProcessName",
                id: "processName",
                title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_PROCESS_NAME')}`,
                optionLabel: this.$i18n.t('ID_BY_PROCESS_NAME'),
                detail: "",
                tagText: "",
                tagPrefix:  this.$i18n.t('ID_SEARCH_BY_PROCESS_NAME'),
                autoShow: true,
                items:[
                    {
                        id: "process",
                        value: "",
                        options: [],
                        placeholder: this.$i18n.t('ID_PROCESS_NAME')
                    }
                ],
                makeTagText: function (params, data) {

                    return  `${this.tagPrefix} ${data[0].options && data[0].options.label || ''}`;
                }
            },
            processCategory:{
                type: "ProcessCategory",
                id: "processCategory",
                title: `${this.$i18n.t('ID_FILTER')}: ${this.$i18n.t('ID_BY_PROCESS_CATEGORY')}`,
                optionLabel: this.$i18n.t('ID_BY_PROCESS_CATEGORY'),
                detail: "",
                tagText: "",
                tagPrefix:  this.$i18n.t('ID_SEARCH_BY_PROCESS_CATEGORY'),
                autoShow: true,
                items:[
                    {
                        id: "category",
                        value: "",
                        options: [],
                        placeholder: ""
                    }
                ],
                makeTagText: function (params, data) {
                    return  `${params.tagPrefix}:  ${data[0].label || ''}`;
                }
            },
            selected: "",
            itemModel: {},
            byProcessName: "",
            byProcessCategory: "",
        };
    },
    mounted() {
        // Force to load filters when mounted the component
        let fils= this.filters;
        if(_.isArray(this.filters)){
            _.forEach(fils,(o)=>{
                o.autoShow = false;
            });
            this.setFilters(fils);
        }
  },
    watch: {
        filters: { 
            immediate: true, 
            handler(newVal, oldVal) { 
                this.searchTags = [];
                this.selected = [];
                this.setFilters(newVal);
            }
        }
    },
    methods: {
        
        /**
         * Add filter criteria save button handler
         */
        onOk() {
            let self = this,
                element,
                initialFilters = [],
                item;
            this.$root.$emit('bv::hide::popover');
            element = _.find(this.filterItems, function(o) { return o.id === self.selected; });
            if  (element) {
                initialFilters = this.prepareFilterItems(element.items, this.selected, true);
            }
            //adding process name filter
            if (self.byProcessName !== "") {
                if (element !== undefined) {
                    this.processName.autoShow = false;
                } else {
                    this.processName.autoShow = true;
                }
                initialFilters =[...new Set([...initialFilters,...this.prepareFilterItems(this.processName.items, self.byProcessName, true)])];
            }
            //adding process name filter
            if (self.byProcessCategory !== "") {
                if (element !== undefined) {
                    this.processCategory.autoShow = false;
                } else {
                    this.processCategory.autoShow = true;
                }
                initialFilters =[...new Set([...initialFilters,...this.prepareFilterItems(this.processCategory.items, self.byProcessCategory, true)])];
            }
            this.$emit("onUpdateFilters", {params: initialFilters, refresh: false}); 
        },
        /**
         * Prepare the filter items
         * @param {array} items
         * @param {id} string
         * @param {boolean} restore
         */
        prepareFilterItems(items, id, restore){
            let initialFilters = [],
                self = this,
                filter,
                item;
            _.forEach(items, function(value, key) {
                filter = _.find(self.filters, function(o) { return o.filterVar === value.id; });
                if (filter && restore) {
                    initialFilters.push(filter);
                } else {
                    item = {
                        filterVar: value.id,
                        fieldId: id,
                        value:  '',
                        label: "",
                        options: []
                    };
                    initialFilters.push(item);
                }
            });
            return initialFilters;
        },
        /**
         * Set Filters and make the tag labels
         * @param {object} filters json to manage the query 
         */
        setFilters(filters) {
            let self = this;
            _.forEach(filters, function(item, key) {
                let component = _.find(self.filterItems, function(o) { return o.id === item.fieldId; });
                if (component) {
                    self.searchTags.push(component.id);
                    self.selected = component.id;
                    self.itemModel[component.id] = component;
                    self.itemModel[component.id].autoShow = typeof item.autoShow !== "undefined" ? item.autoShow : true;
                }
                if(item.fieldId === "processName") {
                    self.searchTags.push(self.processName.id);
                    self.byProcessName = self.processName.id;
                    self.itemModel[self.processName.id] = self.processName;
                    self.itemModel[self.processName.id].autoShow = typeof self.processName.autoShow !== "undefined" ? self.processName.autoShow  : true;
                }
                if(item.fieldId === "processCategory") {
                    self.searchTags.push(self.processCategory.id);
                    self.byProcessCategory = self.processCategory.id;
                    self.itemModel[self.processCategory.id] = self.processCategory;
                    self.itemModel[self.processCategory.id].autoShow = typeof self.processCategory.autoShow !== "undefined" ? self.processCategory.autoShow  : true;
                }
            });
        },
        dataToFilter(id) {
            let data = [];
            _.forEach(this.filters, function(item) { 
                if (item.fieldId === id) {
                    data.push(item);
                }
            });
            return data;
        },
        /**
         * 
         */
        tagContent(id) {
            if (this.itemModel[id]  && typeof this.itemModel[id].makeTagText === "function") {
                return this.itemModel[id].makeTagText(this.itemModel[id],  this.dataToFilter(id));
            }
            return "";
        },
        tagComponent(id) {
            if (this.itemModel[id]) {
                return this.itemModel[id].type;
            }
            return null;
        },
    
        tagInfo(id) {
             if (this.itemModel[id]) {
                return this.itemModel[id];
            }
            return null;
        },
        /**
         * Remove from tag button
         * @param {function} removeTag - default callback
         * @param {string} tag filter identifier
         */
        customRemove(removeTag, tag) {
            let temp = [];
            _.forEach(this.filters, function(item, key) {
                if(item.fieldId !== tag) { 
                    temp.push(item);   
                }
            });
            if (tag === "processName") {
                this.byProcessName = "";
            }
            if (tag === "processCategory") {
                this.byProcessCategory = "";
            }
            this.$emit("onUpdateFilters", {params: temp, refresh: true});
        },
        /**
         * Update the filter model this is fired from filter popaver save action
         * @param {object} params - arrives the settings
         * @param {string} tag filter identifier
         */
        updateSearchTag(params) {      
            let temp = this.filters.concat(params);
            temp = [...new Set([...this.filters,...params])]
            this.$emit("onUpdateFilters",  {params: temp, refresh: true});
        },    
        searchClickHandler() {
            this.$root.$emit('bv::hide::popover');
        }
    }
};
</script>
<style lang="scss">
.bv-example-row .row + .row {
    margin-top: 1rem;
}

.bv-example-row-flex-cols .row {
    min-height: 10rem;
}
.bg-primary-pm {
  background-color: #0099dd;
}

.filter-field {
  display: flex;
}

.v-search-title {
  padding-right: 10px;
  line-height: 40px;
}
.pm-mc-text-icon{
  font-size: 1.40rem;
  padding-right: 10px;
  line-height: 40px;
}
</style>
<style scoped>
.b-form-tags {
    border: none;
}
.input-group-tag {
    position: relative;
    display: flex;
    flex-wrap: nowrap;
    align-items: stretch;
}
</style>
