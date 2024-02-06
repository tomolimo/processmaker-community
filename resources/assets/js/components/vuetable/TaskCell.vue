<template>
    <div v-if="data.length" class="task-cell">
        <div 
            v-for="(item, index) in data"
            v-bind:key = index
            class="d-flex">
            <div
                v-bind:style="{ color: activeColor(item.CODE_COLOR) }"
                :id="statusId + index"
            >
                <i class="fas fa-square"></i>
            </div>
            <b-popover
                v-if="item.DELAYED_TITLE || item.DELAYED_MSG"
                :target="statusId + index"
                triggers="hover"
                placement="top"
            >
                <b> {{ item.DELAYED_TITLE }} </b> {{ item.DELAYED_MSG }}
            </b-popover>
            <div class="col ellipsis" v-b-popover.hover.top="item.TITLE">
                {{ item.TITLE }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "TaskCell",
    props: ["data"],
    data() {
        return {
            colorMap: ["green", "red", "orange", "blue", "silver"],
            statusId: "status-" + _.random(1000000)
        };
    },
    methods: {
        /**
         * Get the style color to be applied in the square icon
         * @param {number} - status color(1-5)
         * @return {string} - color atribute string
         */
        activeColor: function(codeColor) {
            return this.colorMap[codeColor - 1];
        }
    }
};
</script>

<style> 
.popover {
    max-width: 600px !important; 
    min-width: 200px !important;
}
.task-cell {
    font-size: normal;
}

.ellipsis {
    white-space: nowrap;
    width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.color {
    color: red;
}
.avatar {
    color: "red";
    width: "1.3em";
}
</style>
