<template>
    <div class="summaryForm">
        <iframe
            :width="width"
            ref="IFrameSummaryForm"
            title="IFrameSummaryForm"
            frameborder="0"
            :src="path"
            :height="height"
            allowfullscreen
        >
        </iframe>
    </div>
</template>

<script>
export default {
    name: "MoreInformation",
    props: {
        data: Object
    },
    data() {
        return {
            height: "500px",
            width: "100%",
            diffHeight: 10
        };
    },
    computed: {
        path() {
            let url = "";
            if (this.data && this.data.DYN_UID) {
                url =
                    window.config.SYS_SERVER_AJAX +
                    window.config.SYS_URI +
                    '/cases/summary?APP_UID='+this.data.APP_UID +
                    '&DEL_INDEX=' + this.data.DEL_INDEX +
                    '&DYN_UID=' + this.data.DYN_UID;
            }
            return url;
        },
    },
    mounted() {},
    methods: {
            
    }
}
</script>