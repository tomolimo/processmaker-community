<template>
    <div>
        <SearchPopover :target="tag" @savePopover="onOk" :title="info.title">
            <template v-slot:body>
                <p>{{ info.detail }}</p>
                <b-form-group :label="info.label">
                    <b-form-checkbox
                        v-for="option in info.options"
                        v-model="info.casePriorities"
                        :key="option.value"
                        :value="option.value"
                        name="flavour-2a"
                        stacked
                    >
                        {{ option.text }}
                    </b-form-checkbox>
                </b-form-group>
            </template>
        </SearchPopover>
    </div>
</template>
|
<script>
import SearchPopover from "./SearchPopover.vue";

export default {
    components: {
        SearchPopover
    },
    props: ["tag", "info"],
    methods: {
        /**
         * Ok button handler
         */
        onOk() {
            this.handleSubmit();
        },
        /**
         * Submit button handler
         */
        handleSubmit() {
            let selectedOptions = [];
            let self = this;
            _.forEach(this.info.casePriorities, function(value) {
                selectedOptions.push(
                    _.find(self.info.options, function(o) {
                        return o.value === value;
                    })
                );
            });
            this.$emit("updateSearchTag", {
                CasePriority: {
                    priorities: this.info.casePriorities.join(","),
                    selectedOptions: selectedOptions,
                },
            });
            this.$root.$emit("bv::hide::popover");
        },
        /**
         * Tag Click handler
         */
        onClickTag(tag) {
            this.$root.$emit("bv::hide::popover");
        }
    }
};
</script>
<style scoped></style>
