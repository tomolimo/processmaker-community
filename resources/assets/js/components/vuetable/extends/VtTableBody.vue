<template>
    <draggable
        tag="tbody"
        v-if="props.opts.isDraggable"
        v-model="props.data"
        handle=".handle"
        @end="checkMove"
        @start="dragging = true"
    >
        <vnodes :vnodes="props.slots.prependBody"/>
        <vt-no-results-row v-if="props.data.length === 0"/>
            <table-rows v-for="(row,index) in props.data"
                :row="row"
                :index="props.initialIndex + index + 1"
                :renderChildRow="props.hasChildRow && props.openChildRows.includes(row[props.uniqueRowId])"
                :key="index"
            />

        <vnodes :vnodes="props.slots.appendBody"/>
    </draggable>
    <tbody v-else>
        <vnodes :vnodes="props.slots.prependBody"/>
        <vt-no-results-row v-if="props.data.length === 0"/>
        <table-rows v-for="(row,index) in props.data"
            :row="row"
            :index="props.initialIndex + index + 1"
            :renderChildRow="props.hasChildRow && props.openChildRows.includes(row[props.uniqueRowId])"
            :key="index"
        />
        <vnodes :vnodes="props.slots.appendBody"/>
    </tbody>
</template>

<script>
import draggable from "vuedraggable";
import eventBus from "../../../home/EventBus/eventBus";
import VtNoResultsRow from 'vue-tables-2/compiled/components/VtNoResultsRow'
import VtTableRow from 'vue-tables-2/compiled/components/VtTableRow'
import VtChildRow from 'vue-tables-2/compiled/components/VtChildRow'

export default {
    name: "MyTableBody",
    props: ['props'],
    components: {
        draggable,
        VtNoResultsRow,
        VtTableRow,
        VtChildRow,
        vnodes: {
            functional: true,
                render: (h, ctx) => ctx.props.vnodes
            },
        TableRows: {
            functional: true,
            render(h, ctx) {
                let props = ctx.data.attrs,
                    data = [
                    h('vt-table-row', {
                        props
                    })
                ];
                if (props.renderChildRow) {
                    data.push(h('vt-child-row', {
                        props
                    }))
                }
                return data
            }
        }
    },
    data() {
        return {
            enabled: true,
            dragging: false,
        }
    },
    methods: {
        checkMove (e) {
            this.dragging = false;
            eventBus.$emit("sort-case-list", e);
        }
    }
}
</script>