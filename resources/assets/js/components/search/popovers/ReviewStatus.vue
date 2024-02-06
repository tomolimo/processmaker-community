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
                        <b-form-radio-group
                            v-model="selected"
                            :options="readUnread"
                            name="review-status-options"
                            stacked
                        ></b-form-radio-group>
                    </b-form-group>
                </form>
            </template>
        </SearchPopover>
    </div>
</template>

<script>
import SearchPopover from "./SearchPopover.vue";
import Multiselect from "vue-multiselect";

export default {
    components: {
        SearchPopover,
        Multiselect,
    },
    props: ["tag", "info", "filter"],
    data() {
        return {
            selected: "",
            readUnread: [
                { text: this.$i18n.t("ID_READ_FILTER_OPTION"), value: 'READ' },
                { text: this.$i18n.t("ID_UNREAD_FILTER_OPTION"), value: 'UNREAD' }
            ]
        };
    },
    created() {
        this.selected = this.filter[0].value;
    },
    methods: {
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
            this.filter[0].value = this.selected;
            this.$emit("updateSearchTag", this.filter);
            this.$root.$emit("bv::hide::popover");
        },
    },
};
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped></style>
