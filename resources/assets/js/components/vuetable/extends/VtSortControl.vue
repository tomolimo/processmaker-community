<template>
    <div>
        <i v-if="props.sortable" :class="icon"></i>
        <b-button
            class="btn-clear-sort"
            variant="primary"
            @click="clear"
            size="sm"
            v-if="this.props.sortStatus.sorted"
        >
            {{ $t('ID_CLEAR') }}
        </b-button>
    </div>
</template>
<script>
import { Event } from "vue-tables-2";
export default {
    name: "VtSortControl",
    props: ["props"],
    computed: {
        icon() {
            // if not sorted return base icon
            if (!this.props.sortStatus.sorted) return "fas fa-sort";
            // return sort direction icon
            return this.props.sortStatus.asc
                ? "fas fa-sort-amount-up"
                : "fas fa-sort-amount-down";
        },
    },
    methods: {
        /**
         * Send the event to reset the sort
         */
        clear (e) {
            e.preventDefault();
            Event.$emit("clearSortEvent");
            e.stopPropagation();
        }
    }
};
</script>
