<template>
    <div id="">
        <SearchPopover
            :target="tag"
            @savePopover="onOk"
            :title="info.title"
            :autoShow="info.autoShow || false"
        >
            <template v-slot:body>
                <p>{{ info.detail }}</p>
                <form ref="form" @submit.stop.prevent="handleSubmit">
                    <div class="row">
                        <div class="col">
                            <b-form-group>
                                <b-form-datepicker
                                    id="from"
                                    v-model="filter[0].value"
                                    :placeholder="info.items[0].label"
                                ></b-form-datepicker>
                            </b-form-group>
                        </div>
                        <div class="col">
                            <b-form-group>
                                <b-form-datepicker
                                    id="to"
                                    v-model="filter[1].value"
                                    :placeholder="info.items[1].label"
                                ></b-form-datepicker>
                            </b-form-group>
                        </div>
                    </div>
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
    methods: {
        /**
         * Submit form handler
         */
        handleSubmit() {
            this.$emit("updateSearchTag", this.filter);
            this.$root.$emit("bv::hide::popover");
        },
        /**
         * On ok event handler
         */
        onOk() {
            this.handleSubmit();
        },
        /**
         * On click tag event handler
         */
        onClickTag(tag) {
            this.$root.$emit("bv::hide::popover");
        },
    },
};
</script>
<style scoped></style>
