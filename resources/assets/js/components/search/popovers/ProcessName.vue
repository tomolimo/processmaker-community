<template>
    <div>
        <SearchPopover
            :target="tag"
            @savePopover="onOk"
            :title="info.title"
            :autoShow="info.autoShow || false"
        >
            <template v-slot:body>
                <p>{{ info.detail }}</p>
                <form ref="form" @submit.stop.prevent="handleSubmit">
                    <b-form-group
                        label-for="name"
                        :invalid-feedback="$t('ID_PROCESS_IS_REQUIRED')"
                    >
                        <multiselect
                            v-model="filter[0].options"
                            :options="processes"
                            :placeholder="info.items[0].placeholder"
                            label="label"
                            track-by="value"
                            :show-no-results="false"
                            @search-change="asyncFind"
                            :loading="isLoading"
                            id="ajax"
                            :limit="10"
                            :clear-on-select="true"
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
import _ from "lodash";

export default {
    components: {
        SearchPopover,
        Multiselect,
    },
    props: ["tag", "info", "filter"],
    data() {
        return {
            processes: [],
            isLoading: false,
        };
    },
    methods: {
        /**
         * Find asynchronously in the server
         * @param {string} query - string from the text field
         */
        asyncFind(query) {
            let self = this,
                cat = this.verifyCategory(),
                params = { text: query };
            this.isLoading = true;
            if (cat) {
                params.category = cat;
            }
            self.processes = [];
            api.filters
                .processListPaged(params)
                .then((response) => {
                    self.processes = [];
                    _.forEach(response.data, function(elem, key) {
                        self.processes.push({
                            label: elem.PRO_TITLE,
                            value: elem.PRO_ID,
                        });
                    });
                    this.isLoading = false;
                })
                .catch((e) => {
                    console.error(err);
                });
        },
        verifyCategory() {
            let cat = _.find(
                this.$attrs.filters,
                (o) => o.fieldId == "processCategory"
            );
            return cat ? cat.value : null;
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
            this.filter[0].value = this.filter[0].options.value;
            this.$emit("updateSearchTag", this.filter);
            this.$root.$emit("bv::hide::popover");
        },
    },
};
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped></style>
