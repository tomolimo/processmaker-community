<template>
    <div>
        <SearchPopover :target="tag" @savePopover="onOk" :title="info.title">
            <template v-slot:body>
                <p>{{ info.detail }}</p>
                <form ref="form" @submit.stop.prevent="handleSubmit">
                    <b-form-group
                        label-for="name"
                        :invalid-feedback="$t('ID_PROCESS_IS_REQUIRED')"
                    >
                        <multiselect
                            v-model="filter[0].options"
                            :options="categories"
                            :placeholder="$t('ID_CATEGORY_NAME')"
                            label="CATEGORY_NAME"
                            track-by="CATEGORY_ID"
                            :show-no-results="false"
                            @search-change="asyncFind"
                            :loading="isLoading"
                            id="started"
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
    updateSearchTag
<script>
import SearchPopover from "./SearchPopover.vue";
import Multiselect from "vue-multiselect";
import api from "./../../../api/index";

export default {
    components: {
        SearchPopover,
        Multiselect
    },
    props: ["tag", "info", "filter"],
    data() {
        return {
            categories: [],
            isLoading: false,
            started: "",
            completed: ""
        };
    },
   
    methods: {
        /**
         * Find asynchronously in the server
         * @param {string} query - string from the text field
         */
        asyncFind(query) {
            this.isLoading = true;
            api.filters
                .categories(query)
                .then((response) => {
                    this.categories = response.data;
                    this.isLoading = false;
                })
                .catch((e) => {
                    console.error(err);
                });
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
            this.filter[0].value =  this.filter[0].options.CATEGORY_ID;
            this.filter[0].label = this.filter[0].options.CATEGORY_NAME;
           
            this.$emit("updateSearchTag", this.filter);
            this.$root.$emit("bv::hide::popover");
        }
    }
};  
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped></style>
