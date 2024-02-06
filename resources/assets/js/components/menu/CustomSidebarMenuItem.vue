<template>
    <div :id="`menu-${item.id}`">
        <div
            v-if="item.header && !isItemHidden"
            class="vsm--header"
            :class="item.class"
            v-bind="item.attributes"
        >
            {{ item.title }}
            <b-icon
                :icon="item.icon || ''"
                @click="item.onClick(item) || function() {}"
            ></b-icon>
        </div>
        <div
            v-else-if="!isItemHidden"
            class="vsm--item"
            :class="[{ 'vsm--item_open': show }]"
            @mouseover="mouseOverEvent"
            @mouseout="mouseOutEvent"
        >
            <custom-sidebar-menu-link
                :item="item"
                :class="itemLinkClass"
                :attributes="item.attributes"
                @click.native="clickEvent"
            >
                <custom-tooltip
                    v-if="item.icon && !isMobileItem && item.specialType !='header'"
                    :data="item"
                    :collapsed="isCollapsed"
                    :level="level"
                    :customStyle="setIconColor"
                    ref="tooltip"
                ></custom-tooltip>
                <transition name="fade-animation" :appear="isMobileItem">
                    <template
                        v-if="
                            (isCollapsed && !isFirstLevel) ||
                                !isCollapsed ||
                                isMobileItem
                        "
                    >
                        <span :class="item.specialType != 'header'?'vsm--title': 'vsm--header vsm--title--header'">
                            <template v-if="verifyTaskMetrics">
                                <span>
                                    {{ item.title }}
                                </span>
                            </template>
                            <span v-if="item.sortable" :style="item.specialType != 'header'? 'clear: right' : 'clear: none'">
                                <b-icon
                                    class="vp-icon"
                                    :id="`gear-${item.id}`"
                                    :icon="item.sortIcon"
                                    @click="onClickSortSettings"
                                    @mouseover="hoverHandler"
                                    @mouseleave="unhoverHandler"
                                    v-bind:style="{color: sortColor}"
                                ></b-icon>
                                <b-tooltip
                                    :target="`gear-${item.id}`"
                                    triggers="hover"
                                >
                                    {{ $t("ID_CASES_LIST_SETTINGS") }}
                                </b-tooltip>
                            </span>
                        </span>
                    </template>
                </transition>
                <custom-sidebar-menu-icon
                    v-if="item.icon && !isMobileItem && item.specialType =='header'"
                    :icon="item.icon"
                    v-bind:style="setIconColor"
                />
                <template
                    v-if="
                        (isCollapsed && !isFirstLevel) ||
                            !isCollapsed ||
                            isMobileItem
                    "
                >
                    <div
                        v-if="itemHasChild"
                        class="vsm--arrow"
                        :class="[
                            { 'vsm--arrow_open': show },
                            { 'vsm--arrow_slot': $slots['dropdown-icon'] },
                        ]"
                    >
                        <slot name="dropdown-icon" />
                    </div>
                </template>
            </custom-sidebar-menu-link>

            <template v-if="itemHasChild">
                <template
                    v-if="
                        (isCollapsed && !isFirstLevel) ||
                            !isCollapsed ||
                            isMobileItem
                    "
                >
                    <transition
                        :appear="isMobileItem"
                        name="expand"
                        @enter="expandEnter"
                        @afterEnter="expandAfterEnter"
                        @beforeLeave="expandBeforeLeave"
                    >
                        <div
                            v-if="show"
                            class="vsm--dropdown"
                            :class="isMobileItem && 'vsm--dropdown_mobile-item'"
                            :style="isMobileItem && mobileItemStyle.dropdown"
                        >
                            <div class="vsm--list">
                                <custom-sidebar-menu-item
                                    v-for="(subItem, index) in item.child"
                                    :key="index"
                                    :item="subItem"
                                    :level="level + 1"
                                    :show-child="showChild"
                                    :rtl="rtl"
                                    :is-collapsed="isCollapsed"
                                >
                                    <slot
                                        slot="dropdown-icon"
                                        name="dropdown-icon"
                                    />
                                </custom-sidebar-menu-item>
                            </div>
                        </div>
                    </transition>
                </template>
            </template>

            <b-modal
                ref="modal"
                v-if="item.sortable"
                id="my-modal"
                static
                title="Custom Case List Order"
            >
                <draggable
                    :list="item.child"
                    :disabled="!enabled"
                    class="list-group"
                    ghost-class="ghost"
                    @end="checkMove"
                    @start="dragging = true"
                    handle=".handle"
                >
                    <div
                        class="list-group-item"
                        v-for="element in item.child"
                        :key="element.title"
                    >
                        <b-row>
                            <b-col
                                ><b-icon icon="check-circle"></b-icon>
                            </b-col>
                            <b-col cols="9">{{ element.title }}</b-col>
                            <b-col
                                ><i class="fa fa-align-justify handle"></i
                            ></b-col>
                        </b-row>
                    </div>
                </draggable>

                <template #modal-footer="{ cancel }">
                    <b-button size="sm" variant="danger" @click="cancel()">
                        {{ $t("ID_CLOSE") }}
                    </b-button>
                </template>
            </b-modal>
        </div>
    </div>
</template>

<script>
import draggable from "vuedraggable";
import CustomSidebarMenuLink from "./CustomSidebarMenuLink";
import CustomSidebarMenuIcon from "./CustomSidebarMenuIcon";
import CustomTooltip from "./../utils/CustomTooltip.vue";
import eventBus from "./../../home/EventBus/eventBus";

export default {
    name: "CustomSidebarMenuItem",
    props: {
        item: {
            type: Object,
            required: true,
        },
        level: {
            type: Number,
            default: 1,
        },
        isMobileItem: {
            type: Boolean,
            default: false,
        },
        mobileItem: {
            type: Object,
            default: null,
        },
        activeShow: {
            type: Object,
            default: null,
        },
        showChild: {
            type: Boolean,
            default: false,
        },
        showOneChild: {
            type: Boolean,
            default: false,
        },
        rtl: {
            type: Boolean,
            default: false,
        },
        disableHover: {
            type: Boolean,
            default: false,
        },
        mobileItemStyle: {
            type: Object,
            default: null,
        },
    },
    components: {
        draggable,
        CustomSidebarMenuLink,
        CustomSidebarMenuIcon,
        CustomTooltip,
    },
    data() {
        return {
            enabled: true,
            dragging: false,
            itemShow: false,
            itemHover: false,
            exactActive: false,
            active: false,
            titleHover: "",
            menuMap: {
                CASES_INBOX: "inbox",
                CASES_DRAFT: "draft",
                CASES_PAUSED: "paused",
                CASES_SELFSERVICE: "unassigned"
            },
            sortColor: "white",
        };
    },
    mounted() {
        this.setHighlight();
    },
    computed: {
        isCollapsed() {
            return this.$parent.isCollapsed;
        },
        itemLinkClass() {
            return [
                "vsm--link",
                !this.isMobileItem ? `vsm--link_level-${this.level}` : "",
                { "vsm--link_mobile-item": this.isMobileItem },
                { "vsm--link_hover": this.hover },
                { "vsm--link_active": this.active },
                { "vsm--link_exact-active": this.exactActive },
                { "vsm--link_disabled": this.item.disabled },
                this.item.class,
            ];
        },
        draggingInfo() {
            return this.dragging ? "under drag" : "";
        },
        show: {
            get() {
                if (!this.itemHasChild) return false;
                if (this.showChild || this.isMobileItem) return true;
                return this.itemShow;
            },
            set(show) {
                if (this.showOneChild) {
                    show
                        ? this.emitActiveShow(this.item)
                        : this.emitActiveShow(null);
                }
                this.itemShow = show;
            },
        },
        isFirstLevel() {
            return this.level === 1;
        },
        itemHasChild() {
            return !!(this.item.child && this.item.child.length > 0);
        },
        isItemHidden() {
            if (this.isCollapsed) {
                if (
                    this.item.hidden &&
                    this.item.hiddenOnCollapse === undefined
                ) {
                    return true;
                } else {
                    return this.item.hiddenOnCollapse === true;
                }
            } else {
                return this.item.hidden === true;
            }
        },
        /**
         * Verify if the item is TASK_METRICS
         */
        verifyTaskMetrics() {
            return this.item.id === "TASK_METRICS";
        },
        /**
         * Set color to icon defined from custom case list
         */
        setIconColor() {
            return {
                color: this.item.color ? this.item.color : '#fff'
            };
        },
    },
    watch: {
        $route() {
            setTimeout(() => {
                if (this.item.header || this.item.component) return;
                this.initState();
            }, 1);
        },
        item(newItem, item) {
            this.emitItemUpdate(newItem, item);
        },
        activeShow() {
            this.itemShow = this.item === this.activeShow;
        },
    },
    created() {
        this.initState();
    },
    methods: {
        /**
         * set the highlight
         */
        setHighlight() {
            let that = this;
            eventBus.$on('highlight', (data) => {
                var i;
                for (i = 0; i < data.length; i += 1) {
                    if (that.item.page && that.item.page === data[i].id) {
                        if (that.$refs.tooltip && that.menuMap[that.item.id]) {
                            that.$refs.tooltip.setHighlight()
                        }
                    }
                }
            });
        },
        /**
         * Match the route to ensure the correct location
         * @param {string} href
         * @param {string} exactPath
         * @return {boolean}
         */
        matchRoute({ href, exactPath }) {
            if (!href) return false;
            if (this.$router) {
                const { route } = this.$router.resolve(href);
                return exactPath
                    ? route.path === this.$route.path
                    : this.matchExactRoute(href);
            } else {
                return exactPath
                    ? href === window.location.pathname
                    : this.matchExactRoute(href);
            }
        },
        /**
         * Match the exact route with the current location
         * @param {string} href
         * @return {boolean}
         */
        matchExactRoute(href) {
            if (!href) return false;
            if (this.$router) {
                const { route } = this.$router.resolve(href);
                return route.fullPath === this.$route.fullPath;
            } else {
                return (
                    href ===
                    window.location.pathname +
                        window.location.search +
                        window.location.hash
                );
            }
        },
        /**
         * Check if the child is active
         * @param {object} child
         * @return {boolean}
         */
        isChildActive(child) {
            if (!child) return false;
            return child.some((item) => {
                return this.isLinkActive(item);
            });
        },
        /**
         * Validate if the Alias is active
         * @param {object} item
         * @return {boobleam}
         */
        isAliasActive(item) {
            if (item.alias) {
                const current = this.$router
                    ? this.$route.fullPath
                    : window.location.pathname +
                      window.location.search +
                      window.location.hash;
                if (Array.isArray(item.alias)) {
                    return item.alias.some((alias) => {
                        return pathToRegexp(alias).test(current);
                    });
                } else {
                    return pathToRegexp(item.alias).test(current);
                }
            }
            return false;
        },
        /**
         * Validate if the link is active
         * @param {object} item
         * @return {boolean}
         */
        isLinkActive(item) {
            return (
                this.matchRoute(item) ||
                this.isChildActive(item.child) ||
                this.isAliasActive(item)
            );
        },
        /**
         * Ensurre if the link exact is active
         * @param {object} item
         * @return {boolean}
         */
        isLinkExactActive(item) {
            return this.matchExactRoute(item.href);
        },
        /**
         * Initialize the state of the menu item
         */
        initState() {
            this.initActiveState();
            this.initShowState();
        },
        /**
         * Initalize the active state of the menu item
         */
        initActiveState() {
            this.active = this.isLinkActive(this.item);
            this.exactActive = this.isLinkExactActive(this.item);
        },
        /**
         * Initialize and show active state menu item
         */
        initShowState() {
            if (!this.itemHasChild || this.showChild) return;
            if (
                (this.showOneChild && this.active && !this.show) ||
                (this.active && !this.show)
            ) {
                this.show = true;
            } else if (this.showOneChild && !this.active && this.show) {
                this.show = false;
            }
        },
        /**
         * Handler to check if the item is moving
         * @param {object} e
         */
        checkMove: function(e) {
            let aux = this.item.child.splice(e.newIndex, 1);
            this.item.child.splice(e.newIndex, 0, aux[0]);
            this.emitItemUpdate(this.item, this.item);
            eventBus.$emit("sort-menu", this.item.child);
        },
        /**
         * Click event Handler
         * @param {object} event
         */
        clickEvent(event) {
            if (this.item.disabled) return;
            if (!this.item.href) {
                event.preventDefault();
            }
            this.emitItemClick(event, this.item, this);
            this.emitMobileItem(event, event.currentTarget.offsetParent);
            if (!this.itemHasChild || this.showChild || this.isMobileItem)
                return;
            if (!this.item.href || this.exactActive) {
                this.show = !this.show;
            }
        },
        /**
         * Mouse over event handler
         * @param {object} event
         */
        mouseOverEvent(event) {
            if (this.item.disabled) return;
            event.stopPropagation();
            this.itemHover = true;
            if (!this.disableHover) {
                this.emitMobileItem(event, event.currentTarget);
            }
        },
        /**
         * Mouse out event handler
         * @param {object} event
         */
        mouseOutEvent(event) {
            event.stopPropagation();
            this.itemHover = false;
        },
        /**
         * Expand sidebar menu item handler
         * @param {object} el
         */
        expandEnter(el) {
            el.style.height = el.scrollHeight + "px";
        },
        /**
         * Expand after enter menu item handler
         * @param {object} el
         */
        expandAfterEnter(el) {
            el.style.height = "auto";
        },
        /**
         * Expand before leace handler
         * @param {object} el
         */
        expandBeforeLeave(el) {
            if (this.isCollapsed && this.isFirstLevel) {
                el.style.display = "none";
                return;
            }
            el.style.height = el.scrollHeight + "px";
        },
        /**
         * Emit Mobile item handler
         * @param {object} event
         * @param {object} itemEl
         */
        emitMobileItem(event, itemEl) {
            if (this.hover) return;
            if (!this.isCollapsed || !this.isFirstLevel || this.isMobileItem)
                return;
            this.$parent.$emit("unset-mobile-item", true);
            setTimeout(() => {
                if (this.$parent.mobileItem !== this.item) {
                    this.$parent.$emit("set-mobile-item", {
                        item: this.item,
                        itemEl,
                    });
                }
                if (event.type === "click" && !this.itemHasChild) {
                    this.$parent.$emit("unset-mobile-item", false);
                }
            }, 0);
        },
        /**
         * Click Sort settings event handler
         * @param {object} event
         */
        onClickSortSettings(event) {
            event.preventDefault();
            event.stopPropagation();
            this.$refs["modal"].show();
        },
        hoverHandler() {
            this.sortColor = '#02feff';
        },
        unhoverHandler() {
            this.sortColor = 'white';
        }
    },
    inject: ["emitActiveShow", "emitItemClick", "emitItemUpdate"],
};
</script>
<style scoped>
.vsm--header.vsm--title--header{
    display: initial;
    white-space: nowrap;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.vsm--header.vsm--title--header + .vsm--icon{
    float: none;
    line-height: 30px;
    margin-right: 10px;
    margin-left: 0px;
}

.vp-icon {
    margin-left: 5%;
}
</style>