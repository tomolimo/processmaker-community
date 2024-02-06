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
                                    v-model="from"
                                ></b-form-datepicker>
                            </b-form-group>
                        </div>
                        <div class="col">
                            <b-form-group>
                                <b-form-datepicker
                                    id="to"
                                    v-model="to"
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
    data() {
        return {
            from: this.filter[0] ? this.filter[0].value.split(",")[0] : "",
            to: this.filter[0] ? this.filter[0].value.split(",")[1] : ""
        };
    },
    watch: {
        filter: function (val) {
            let data;
            if(val[0]){
                data = val[0].value.split(",");
                if (data.length > 1) {
                    this.from = data[0];
                    this.to = data[1];
                }
            }
        }
    },
    methods: {
        /**
         * Submit form handler
         */
        handleSubmit() {
            if (this.from && this.to) {
                this.filter[0].value = this.from + "," + this.to;
            }
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
