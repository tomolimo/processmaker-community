<template>
    <div id="v-pm-charts" ref="v-pm-charts" class="v-pm-charts vp-inline-block">
        <vue-chart-lv-zero
            v-if="level === 0"
            @updateDataLevel="updateDataLevel"
        />
        <vue-chart-lv-one
            :key="key1"
            v-if="level === 1"
            :data="levels"
            @updateDataLevel="updateDataLevel"
        />
        <vue-chart-lv-two
            :key="key2"
            v-if="level === 2"
            :data="levels"
            @updateDataLevel="updateDataLevel"
        />
        <vue-chart-lv-three
            :key="key3"
            v-if="level === 3"
            :data="levels"
            @updateDataLevel="updateDataLevel"
        />
    </div>
</template>

<script>
import VueChartLvZero from "./VueChartLvZero.vue";
import VueChartLvOne from "./VueChartLvOne.vue";
import VueChartLvTwo from "./VueChartLvTwo.vue";
import VueChartLvThree from "./VueChartLvThree.vue";
import _ from "lodash";

export default {
    name: "VueCharts",
    mixins: [],
    components: {
        VueChartLvZero,
        VueChartLvOne,
        VueChartLvTwo,
        VueChartLvThree,
    },
    props: ["levels"],
    data() {
        let that = this;
        return {
            key1: _.random(0, 100),
            key2: _.random(0, 100),
            key3: _.random(0, 100),
            settingsBreadCrumbs: [
                {
                    class: "fas fa-info-circle",
                    onClick() {},
                },
            ],
        };
    },
    created() {},
    mounted() {},
    watch: {},
    computed: {
        level: function() {
            return _.find(this.levels, { active: true }).level;
        },
    },
    updated() {},
    beforeCreate() {},
    methods: {
        /**
         * Set data level 0
         */
        updateDataLevel(data) {
            this.$emit("onChangeLevel", data);
            this.updateKey(data.level);
        },
        updateKey(level) {
            switch (level) {
                case 0:
                    break;
                case 1:
                    this.key1++;
                    break;
                case 2:
                    this.key2++;
                    break;
                case 3:
                    this.key3++;
                    break;
            }
        },
    },
};
</script>
<style></style>
