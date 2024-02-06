export default {
    data() {
        return {
            contextMenuItems: []
        };
    },
    methods: {
        /**
        * Handler for item context menu clicked
        */
        contextMenuItemClicked(info) {
            console.log(info);
        }
    }
}