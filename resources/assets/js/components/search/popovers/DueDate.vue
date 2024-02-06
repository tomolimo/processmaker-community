<template>
    <div id="">
        <SearchPopover
            :target="tag"
            @savePopover="onOk"
            :title="info.title"
        >
            <template v-slot:body>
                <p>{{ info.detail }}</p>
                <form ref="form" @submit.stop.prevent="handleSubmit">
                    <div class="row">
                        <div class="col">
                            <b-form-group >
                                <b-form-datepicker
                                    id="from"
                                    v-model="info.values.dueDateFrom"
                                    :placeholder="$t('ID_FROM_DUE_DATE')"
                                ></b-form-datepicker>
                            </b-form-group>
                        </div>
                        <div class="col">
                            <b-form-group>
                                <b-form-datepicker
                                    id="to"
                                    v-model="info.values.dueDateTo"
                                    :placeholder="$t('ID_TO_DUE_DATE')"
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
    props: ["tag", "info"],
    methods: {
        /**
         * Submit form handler
         */
        handleSubmit() {
            this.$emit("updateSearchTag", {
                DueDate: {
                    dueDateFrom: this.info.values.dueDateFrom,
                    dueDateTo: this.info.values.dueDateTo
                }
            });
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
        }
    }
};
</script>
<style scoped></style>
