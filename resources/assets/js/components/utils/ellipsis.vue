<template>
    <div>
        <div 
            class="ellipsis-button align-middle"
            v-show="!showActions"
            @mouseenter="showActionButtons"
        >
            <span>
                <i class="fas fa-ellipsis-v"></i>
            </span>
        </div>
        <div class="float-right" v-show="showActions">
            <transition name="fade">
                <div
                    class="v-inline"
                    v-show="showActions"
                    ref="ellipsis"
                    @mouseleave="hideActionButtons"
                >
                    <div class="buttonGroup">
                        <b-button
                            v-for="item in data.buttons"
                            :key="item.name"
                            variant="outline-info"
                            @click="executeFunction(item.fn)"
                            :aria-label="item.name"
                        >
                            <i class="custom-icon" :class="item.icon" v-bind:style="{color: item.color}"></i>
                        </b-button>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script>
import eventBus from "./../../home/EventBus/eventBus"
export default {
    name: "Ellipsis",
    props: {
        data: Object
    },
    data () {
        return {
            showActions: false
        }
    },
    mounted () {
        eventBus.$on('closeEllipsis', this.hideActionButtons);
    },
    deactivated () {
        eventBus.$off('closeEllipsis', this.hideActionButtons);
    },
    destroyed () {
        eventBus.$off('closeEllipsis', this.hideActionButtons);
    },

    methods: {
        /**
         * Callback function from parent
         */
        executeFunction(fn) {
            if (fn) {
                fn();
            }
        },
        /**
         * Show the action buttons by row
         */
        showActionButtons() {
            var i,
                elelemts;
            this.showActions = true;
            if (this.showActions) {
                if (this.$parent.Row !== undefined) {
                    eventBus.$emit('closeEllipsis', this.data);
                    for (i = 0; i < this.$parent.$parent.$parent.$children.length -1 ; i++){
                        this.$parent.$parent.$parent.$children[i].$el.style.opacity = 0.15
                    }
                } else if (this.$parent.item !== undefined) {
                    if (this.$parent.$parent.$parent.$refs.vueListView !== undefined) {
                        elelemts = this.$parent.$el.getElementsByClassName('col-sm-5');
                        elelemts[0].style.opacity = 0.4;
                        elelemts[1].style.opacity = 0.4;
                    }
                    if (this.$parent.$parent.$parent.$refs.vueCardView !== undefined) {
                        this.$parent.$el.getElementsByClassName('col-sm-9')[0].style.opacity = 0.2
                    }
                }
            } else {
                this.hideActionButtons();     
            }
        },
        /**
         * Hide action buttons
         * @param {object} dataE
         */
        hideActionButtons(dataE) {
            var i,
                elelemts;
            if (this.$parent.Row !== undefined) {
                if (this.data.APP_UID !== dataE.APP_UID) {
                    this.showActions = false;
                    for (i = 0; i < this.$parent.$parent.$parent.$children.length -1 ; i++){
                        this.$parent.$parent.$parent.$children[i].$el.style.opacity = 1
                    }
                }
            } else if (this.$parent.item !== undefined) {
                this.showActions = false;
                if (this.$parent.$parent.$parent.$refs.vueListView !== undefined) {
                    elelemts = this.$parent.$el.getElementsByClassName('col-sm-5');
                    elelemts[0].style.opacity = 1;
                    elelemts[1].style.opacity = 1;
                }
                if (this.$parent.$parent.$parent.$refs.vueCardView !== undefined) {
                    this.$parent.$el.getElementsByClassName('col-sm-9')[0].style.opacity = 1;
                }
            }
        },
    }
}
</script>

<style>
    .v-btn-request {
        display: inline-block;
    }
    .v-inline {
        display: inline-block;
    }
    .ellipsis-button {
        font-size: 22px;
        text-align: center;
    }
    .buttonGroup {
        position: relative;
        flex-direction: row-reverse;
        width: 0px;
        z-index: 999;
        display: inline-flex !important;
        opacity: 1 !important;
        height: 50px !important;
    }
    .btn-outline-info {
        border: none;
        font-size: 25px;
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.5s;
        position: relative;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
        position: relative;
    }
</style>