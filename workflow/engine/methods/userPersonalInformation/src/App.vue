<template>
    <div id="app">
        <personalInformation refs="personalInformation"></personalInformation>
    </div>
</template>

<script>
    import personalInformation from "./components/personalInformation.vue"
    export default {
        name: 'app',
        components: {
            personalInformation
        },
        data() {
            return {
                views: {
                    personalInformation: true
                }
            };
        },
        methods: {
            showView(name) {
                for (let view in this.views) {
                    this.views[view] = false;
                }
                this.views[name] = true;
            }
        }
    }
</script>

<style>
    #app {
        margin: 20px;
    }
    .custom-tooltip > .tooltip-inner{
        max-width: none;
    }
</style>
