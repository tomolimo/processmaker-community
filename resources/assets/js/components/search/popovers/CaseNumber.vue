<template>
    <div>
        <SearchPopover
            :target="tag"
            :showPopover="showPopover"
            @closePopover="onClose"
            @savePopover="onOk"
            :title="info.title"
            :autoShow="info.autoShow || false"
        >
            <template v-slot:body>
                <p>{{ info.detail }}</p>
                <form ref="form" @submit.stop.prevent="handleSubmit">
                    <b-form-group
                        :state="valueState"
                        label-for="name-input"
                        :invalid-feedback="$t('ID_INVALID_CASE_NUMBER_RANGE')"
                    >
                        <b-form-input
                            id="name-input"
                            v-model="filter[0].value"
                            :placeholder="$t('ID_CASE_NUMBER_FILTER_RANGE_EG')"
                            :state="valueState"
                            required
                        ></b-form-input>
                    </b-form-group>
                </form>
            </template>
        </SearchPopover>
    </div>
</template>

<script>
import SearchPopover from "./SearchPopover.vue";

export default {
    components: {
        SearchPopover,
    },
    props: ["tag", "info", "filter"],
    data() {
        return {
            valueState: null,
            showPopover: false
        };
    },
    methods: {
        onClose() {
            this.showPopover = true;
        },
        checkFormValidity() {
            const regex = /^((\d+?)|(\d+?)(?:\-(\d+?))?)(?:\,((\d+?)|(\d+?)(?:\-(\d+?))?))*$/;
            regex.test(this.filter[0].value);
            this.valueState = regex.test(this.filter[0].value);
            return this.valueState;
        },
        handleSubmit() {
            let self = this;
            // Exit when the form isn't valid
            if (!this.checkFormValidity()) {
                return;
            }
            // Hide the modal manually
            this.$nextTick(() => {
                this.$emit("updateSearchTag", this.filter);
                this.$root.$emit("bv::hide::popover");
            });
        },
        onOk() {
            this.handleSubmit();
        },
        onClickTag(tag) {
            this.$root.$emit("bv::hide::popover");
        },
    },
};
</script>
<style scoped>
.popovercustom {
    max-width: 650px !important;
}
</style>
