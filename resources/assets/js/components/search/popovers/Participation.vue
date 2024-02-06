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
                        <b-form-checkbox
                            id="started"
                            v-model="filter[0].status"
                            name="started"
                            value="accepted"
                            unchecked-value="not_accepted"
                            >
                            {{$t("ID_STARTED_BY")}}
                        </b-form-checkbox>
                        <multiselect
                            v-model="filter[0].options"
                            :options="users"
                            :placeholder="$t('ID_USER_NAME')"
                            label="USR_FULLNAME"
                            track-by="USR_ID"
                            :show-no-results="false"
                            @search-change="asyncFind"
                            :loading="isLoading"
                            id="started"
                            :limit="10"
                            :clear-on-select="true"
                            :disabled="filter[0].status !== 'accepted'"
                        >
                        </multiselect>
                    </b-form-group> 
                    <b-form-group>
                        <b-form-checkbox
                            id="completed"
                            v-model="filter[1].status"
                            name="completed"
                            value="accepted"
                            unchecked-value="not_accepted"
                            >
                            {{$t("ID_COMPLETED_BY")}}
                        </b-form-checkbox>
                        <multiselect
                            v-model="filter[1].options"
                            :options="users"
                            :placeholder="$t('ID_USER_NAME')"
                            label="USR_FULLNAME"
                            track-by="USR_ID"
                            :show-no-results="false"
                            @search-change="asyncFind"
                            :loading="isLoading"
                            id="completed"
                            :limit="10"
                            :clear-on-select="true"
                            :disabled="filter[1].status !== 'accepted'"
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
    props: ["tag", "info", "filter"],
    data() {
        return {
            users: [],
            isLoading: false,
            started: "",
            completed: ""
        };
    },
    watch: {
        filter: { 
            immediate: true, 
            handler(newVal, oldVal) { 
                if (newVal[0].status === "not_accepted") {
                    newVal[0].options = [];
                    newVal[0].label = "";
                    newVal[0].value = "";
                }
                if (newVal[1].status === "not_accepted") {
                    newVal[1].options = [];
                    newVal[1].label = "";
                    newVal[1].value = "";
                }
                
            }
        }
    },
    methods: {
        /**
         * Find asynchronously in the server
         * @param {string} query - string from the text field
         */
        asyncFind(query) {
            this.isLoading = true;
            api.filters
                .userList(query)
                .then((response) => {
                    this.users = response.data;
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
            this.filter[0].value =  this.filter[0].options.USR_ID;
            this.filter[0].label = this.filter[0].options.USR_FULLNAME;
            this.filter[1].value =  this.filter[1].options.USR_ID;
            this.filter[1].label = this.filter[1].options.USR_FULLNAME;
            this.$emit("updateSearchTag", this.filter);
            this.$root.$emit("bv::hide::popover");
        }
    }
};
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped></style>
