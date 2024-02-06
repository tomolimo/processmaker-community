<template>
    <div id="app">
        <authenticationSources ref="authenticationSources"
                               v-show="views.authenticationSources"
                               @newConnection="newConnection"
                               @editSettings="editSettings"
                               @optionSaveButton="optionSaveButton"
                               @optionUpdateButton="optionUpdateButton"
                               @optionNewButton="optionNewButton"/>

        <newConnection ref="newConnection"
                       v-show="views.newConnection"
                       @matchAttributesToSync="matchAttributesToSync"
                       @save="saveNewConnection"
                       @cancel="showView('authenticationSources')"/>

        <matchAttributes ref="matchAttributes" 
                         v-show="views.matchAttributes"
                         @connectionSettings="connectionSettings"
                         @addAttribute="addAttribute"
                         @editAttribute="editAttribute"/>

        <newMatchedAttribute ref="newMatchedAttribute" 
                             v-show="views.newMatchedAttribute"
                             @save="saveNewMatchedAttribute"
                             @cancel="cancelNewMatchedAttribute"/>

    </div>
</template>

<script>
    import authenticationSources from './components/authenticationSources.vue'
    import newConnection from './components/newConnection.vue'
    import matchAttributes from './components/matchAttributes.vue'
    import newMatchedAttribute from "./components/newMatchedAttribute.vue"
    import axios from "axios"
    export default {
        name: 'app',
        components: {
            authenticationSources,
            newConnection,
            matchAttributes,
            newMatchedAttribute
        },
        data() {
            return {
                views: {
                    authenticationSources: true,
                    newConnection: false,
                    matchAttributes: false,
                    newMatchedAttribute: false
                },
                selectedRow: null
            };
        },
        methods: {
            showView(name) {
                for (let view in this.views) {
                    this.views[view] = false;
                }
                this.views[name] = true;
            },

            newConnection() {
                this.$refs.newConnection.reset();
                this.$refs.newConnection.setTitle(this.$root.translation('ID_NEW_AUTHENTICATION_SOURCES'));
                this.showView('newConnection');
            },
            editSettings(row) {
                this.selectedRow = row;
                let form = this.$refs.newConnection.rowToForm(row);
                this.$refs.newConnection.setTitle(this.$root.translation('ID_EDIT_AUTHENTICATION_SOURCES'));
                this.$refs.newConnection.reset();
                this.$refs.newConnection.load(form);
                this.showView('newConnection');
            },
            optionSaveButton(row) {
                row.AUTH_SOURCE_UID = "";
                let form = this.$refs.newConnection.rowToForm(row);
                this.$refs.newConnection.setTitle(this.$root.translation('ID_NEW_AUTHENTICATION_SOURCES'));
                this.$refs.newConnection.reset();
                this.$refs.newConnection.load(form);
                this.showView('newConnection');
            },
            optionUpdateButton(row, rowResult) {
                row.AUTH_SOURCE_UID = rowResult.AUTH_SOURCE_UID;
                this.selectedRow = row;
                let form = this.$refs.newConnection.rowToForm(row);
                this.$refs.newConnection.setTitle(this.$root.translation('ID_EDIT_AUTHENTICATION_SOURCES'));
                this.$refs.newConnection.reset();
                this.$refs.newConnection.load(form);
                this.showView('newConnection');
            },
            optionNewButton(row) {
                row.AUTH_SOURCE_UID = "";
                let form = this.$refs.newConnection.rowToForm(row);
                this.$refs.newConnection.setTitle(this.$root.translation('ID_NEW_AUTHENTICATION_SOURCES'));
                this.$refs.newConnection.reset();
                this.$refs.newConnection.load(form);
                this.showView('newConnection');
            },

            saveNewConnection(form) {
                let formData = this.$refs.newConnection.formToFormData(form);
                axios.post(this.$root.baseUrl() + "authSources/ldapAdvancedProxy.php?functionAccion=ldapSave", formData)
                        .then(response => {
                            response;
                            this.$refs.authenticationSources.refresh();
                        })
                        .catch(error => {
                            error;
                        })
                        .finally(() => {
                        });
                this.showView('authenticationSources');
            },

            matchAttributesToSync() {
                let gridText = this.$refs.newConnection.getGridText();
                let rows = JSON.parse(gridText);
                this.showView('matchAttributes');
                this.$refs.matchAttributes.setRows(rows);
            },
            addAttribute() {
                this.$refs.newMatchedAttribute.reset();
                this.showView("newMatchedAttribute");
            },
            editAttribute(row, index) {
                this.$refs.newMatchedAttribute.load(row, index);
                this.showView("newMatchedAttribute");
            },
            saveNewMatchedAttribute(form) {
                this.$refs.matchAttributes.saveRow(form);
                this.showView('matchAttributes');
            },
            cancelNewMatchedAttribute() {
                this.showView('matchAttributes');
            },
            connectionSettings(rows) {
                let gridText = JSON.stringify(rows);
                this.$refs.newConnection.setGridText(gridText);
                this.showView('newConnection');
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
