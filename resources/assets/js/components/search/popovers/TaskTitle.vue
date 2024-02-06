<template>
    <div>
        <SearchPopover :target="tag" @savePopover="onOk" :title="info.title" :autoShow="info.autoShow || false">
            <template v-slot:body>
                <p>{{ info.detail }}</p>
                <form ref="form" @submit.stop.prevent="handleSubmit">
                    <b-form-group
                        label-for="name"
                        :invalid-feedback="$t('ID_PROCESS_IS_REQUIRED')"
                    >
                        <multiselect
                            v-model="filter[0].options"
                            :options="taks"
                            :placeholder="$t('ID_TASK_TITLE')"
                            label="TAS_PROCESS"
                            track-by="TAS_ID"
                            :show-no-results="false"
                            @search-change="asyncFind"
                            :loading="isLoading"
                            id="ajax"
                            :limit="10"
                            :clear-on-select="true"
                            :select-label="''"
                        >
                        </multiselect>
                    </b-form-group>
                </form>
            </template>
        </SearchPopover>
    </div>
</template>

<script>
import SearchPopover from "./SearchPopover.vue";
import Multiselect from "vue-multiselect";
import api from "./../../../api/index";

export default {
    components: {
        SearchPopover,
        Multiselect
    },
    props: ["tag", "info", "filters", "filter"],
    data() {
        return {
            taks: [],
            isLoading: false
        };
    },
    methods: {
        /**
         * Find asynchronously in the server
         * @param {string} query - string from the text field
         */

        asyncFind(query) {
            let params = {};
            this.isLoading = true;   
            params.proId = this.getProcess();
            params.query = query;
            api.filters
                .taskList(params)
                .then((response) => {
                    this.taks = response.data;
                    this.isLoading = false;
                })
                .catch((e) => {
                    console.error(err);
                });
        },
        /**
         * Get the process id to manage the dependency
         */
        getProcess() {
            let component = _.find(this.filters, function(o) { return o.fieldId === "processName"; });
            return component ? component.value : null;
        },
        /**
         * Form validations review
         */
        checkFormValidity() {
            const valid = this.query !== "";
            this.valueState = valid;
            return valid;
        },
        /**
         * On Ok event handler
         */
        onOk() {
            this.handleSubmit();
        },
        /**
         *  Form submit handler
         */
        handleSubmit() {
            this.filter[0].value = this.filter[0].options.TAS_ID;
            this.filter[0].label = this.filter[0].options.TAS_TITLE;
            this.$emit("updateSearchTag", this.filter);
            this.$root.$emit("bv::hide::popover");
        }
    }
};
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped></style>
