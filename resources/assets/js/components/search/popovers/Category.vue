<template>
    <div id="">
        <SearchPopover
            :target="tag"
            @closePopover="onClose"
            @savePopover="onOk"
        >
            <template v-slot:target-item>
                <div @click="onClickTag(tag)" :id="tag">
                    <b-icon icon="tags-fill" font-scale="1"></b-icon>
                    {{ info.tagText }}
                </div>
            </template>
            <template v-slot:body>
                <h6>Filter: Case Title</h6>
                <input
                    type="text"
                    size="150"
                    class="form-control"
                    placeholder="Case Title Name"
                />
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
    data() {
        return {};
    },
    computed: {},
    methods: {
        onClose() {
        },
        onOk() {

        },
        onClickTag(tag) {
            this.$root.$emit("bv::hide::popover");
        },
    },
};
</script>
<style scoped></style>
