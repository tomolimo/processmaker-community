<template>
    <div>
        <slot name="target-item"></slot>

        <b-popover
            :show="autoShow"
            :target="target"
            ref="popover"
            triggers="click"
            placement="bottom"
            class="popovercustom"
            @show="onshow"
        >
            <template #title>
                <b-button @click="onClose" class="close" aria-label="Close">
                    <span class="d-inline-block" aria-hidden="true"
                        >&times;</span
                    >
                </b-button>
                {{ title }}
            </template>
            <div>
                <slot name="body"></slot>
                <div class="v-popover-footer">
                    <div class="float-right">
                        <b-button @click="onClose" size="sm" variant="danger"> {{$t('ID_CANCEL')}}</b-button>
                        <b-button @click="onSave" size="sm" variant="success">{{$t('ID_SAVE')}}</b-button>
                    </div>
                </div>
            </div>
        </b-popover>
    </div>
</template>
<script>
export default {
    props: ['target', "title", "autoShow"],

    methods: {
        /**
         * Close buton click handler 
         */
        onClose() {
            this.$refs.popover.$emit('close');
            this.$emit('closePopover');
        },
        /**
         * Save button click handler 
         */
        onSave() {
            this.$emit('savePopover');
        },
        /**
         * Show popover event handler
         */
        onshow() {
            this.$root.$emit('bv::hide::popover');   
        }
    }
};
</script>

<style scoped>

.popover {
    max-width: 650px !important; 
    min-width: 400px !important;
}

.v-popover-footer{
    display: flow-root;
}
</style>