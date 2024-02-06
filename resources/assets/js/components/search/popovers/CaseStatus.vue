<template>
    <div>
        <SearchPopover
            :target="tag"
            @savePopover="onOk"
            :title="info.title"
        >
            <template v-slot:body>
                <h6>{{ info.detail }}</h6>
                <b-form-group :label="info.label">
                    <b-form-checkbox
                        v-for="option in info.items[0].options"
                        v-model="filter[0].options"
                        :key="option.value"
                        :value="option.value"
                        name="flavour-2a"
                        stacked
                    >
                        {{ option.label }}
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
    props: ["tag", "info", "filter"],
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
            let selectedLabels = [],
                self = this,
                item;
            this.filter[0].value=this.filter[0].options.join(",");
            _.forEach(this.filter[0].options, function(value) {
                item = _.find(self.info.items[0].options, function(o) { return o.value === value; });
                if (item) {
                    selectedLabels.push(item.label);
                }
            });
            this.filter[0].label=selectedLabels.join(",");
            this.$emit("updateSearchTag", this.filter);
            this.$root.$emit("bv::hide::popover")
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
