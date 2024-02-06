<template>
    <span
        @mouseover="hoverHandler"
        @mouseleave="unhoverHandler"
        v-bind:class="{highlightText: isHighlight, loadingTooltip: isLoading}"
    >
        <div
            :id="`label-${data.id}-mobile`"
            v-if="collapsed && mobile && level == 1"
            class="float-left"
        >
            {{ data.title }}
        </div>
        <div
            :id="`label-${data.id}`"
            v-else-if="!collapsed || mobile || level > 1"
            class="float-left"
        >
            <custom-sidebar-menu-icon
                :icon="data.icon"
                :style="customStyle"
            />
            {{ data.title }}
        </div>
        <div v-else-if="collapsed">
            <custom-sidebar-menu-icon
                :id="`label-${data.id}`"
                :icon="data.icon"
                :style="customStyle"
            />
        </div>
        <b-tooltip 
            :target="mobile ? `label-${data.id}-mobile` : `label-${data.id}`"
            :boundary="mobile ? `label-${data.id}-mobile` : `label-${data.id}`"
            :show.sync="showTooltip"
            :placement="collapsed ? 'auto' : 'topright'"
            v-if="showTooltip"
        >
            {{ labelTooltip }}
            <p v-if="labelName !== '' || labelDescription !== ''">
                <span v-if="labelName !== ''">{{ labelName }}</span>
                <span v-if="labelName !== '' && labelDescription !== ''">:</span>
                <span v-if="labelDescription !== ''">{{ labelDescription }}</span>
            </p>
        </b-tooltip>
    </span>
</template>

<script>
import api from "./../../api/index";
import CustomSidebarMenuIcon from "../menu/CustomSidebarMenuIcon.vue";

export default {
    name: "CustomTooltip",
    components: {
        CustomSidebarMenuIcon,
    },
    props: {
        data: Object,
        collapsed: Boolean,
        customStyle: Object,
        level: Number,
        mobile: Boolean
    },
    data() {
        return {
            labelTooltip: "",
            labelName: "",
            labelDescription: "",
            hovering: "",
            show: false,
            menuMap: {
                CASES_INBOX: "inbox",
                CASES_DRAFT: "draft",
                CASES_PAUSED: "paused",
                CASES_SELFSERVICE: "unassigned"
            },
            isHighlight: false,
            showTooltip: false,
            isLoading: false,
            loading: ""
        };
    },
    methods: {
        /**
         * Delay the hover event
         */
        hoverHandler() {
            if (this.loading) {
                clearTimeout(this.loading);
            }
            if (this.hovering) {
                clearTimeout(this.hovering);
            }
            this.loading = setTimeout(() => { this.isLoading = true }, 1000) ;
            this.hovering = setTimeout(() => { this.setTooltip() }, 3000);
        },
        /**
         * Reset the delay and hide the tooltip
         */
        unhoverHandler() {
            this.labelTooltip = "";
            this.labelName = "";
            this.labelDescription = "";
            this.showTooltip = false;
            this.isLoading = false;
            clearTimeout(this.loading);
            clearTimeout(this.hovering);
        },
        /**
         * Set the label to show in the tooltip
         */
        setTooltip() {
            let that = this;
            if (this.menuMap[this.data.id]) {
                api.menu.getTooltip(that.data.page).then((response) => {
                    that.showTooltip = true;
                    that.isLoading = false;
                    that.labelTooltip = response.data.label;
                });
            } else {
                api.menu.getTooltipCaseList(that.data)
                .then((response) => {
                    that.showTooltip = true;
                    that.isLoading = false;
                    that.labelTooltip = response.data.label;
                    that.labelName = response.data.name;
                    that.labelDescription = response.data.description;
                });
            }
        },
        /**
         * Set bold the label 
         */
        setHighlight() {
            this.isHighlight = true;
        }
    },
};
</script>
<style>
.highlightText {
    font-weight: 900;
}
.loadingTooltip {
    cursor: wait;
}
</style>
