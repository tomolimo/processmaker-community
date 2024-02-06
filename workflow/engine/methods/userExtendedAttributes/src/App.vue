<template>
    <div id="app">
        <userExtendedAttributes ref="userExtendedAttributes"
                                v-show="views.userExtendedAttributes"
                                @newAttribute="newAttribute"
                                @editAttribute="editAttribute">
        </userExtendedAttributes>
        <newUserAttribute ref="newUserAttribute"
                          v-show="views.newUserAttribute"
                          @save="saveAttribute"
                          @cancel="cancel">
        </newUserAttribute>
    </div>
</template>

<script>
    import userExtendedAttributes from './components/userExtendedAttributes.vue'
    import newUserAttribute from './components/newUserAttribute.vue'
    export default {
        name: 'app',
        components: {
            userExtendedAttributes,
            newUserAttribute
        },
        data() {
            return {
                views: {
                    userExtendedAttributes: true,
                    newUserAttribute: false
                }
            };
        },
        methods: {
            showView(name) {
                for (let view in this.views) {
                    this.views[view] = false;
                }
                this.views[name] = true;
            },
            cancel() {
                this.showView("userExtendedAttributes");
            },
            newAttribute() {
                this.$refs.newUserAttribute.reset();
                this.showView("newUserAttribute");
            },
            saveAttribute() {
                this.showView("userExtendedAttributes");
                this.$refs.userExtendedAttributes.refresh();
            },
            editAttribute(row) {
                this.$refs.newUserAttribute.reset();
                this.$refs.newUserAttribute.load(row);
                this.showView("newUserAttribute");
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
