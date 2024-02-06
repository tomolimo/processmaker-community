<template>
    <div>
        <titleSection :title="title"></titleSection>
        <formAuthenticationSources ref="formAuthenticationSources"
                                   @matchAttributesToSync="$emit('matchAttributesToSync',$event)"
                                   @cancel="cancel"
                                   @save="$emit('save',$event)"/>
    </div>
</template>

<script>
    import titleSection from "./titleSection.vue"
    import formAuthenticationSources from "./formAuthenticationSources.vue"
    export default {
        data() {
            return {
                title: "#"
            };
        },
        components: {
            titleSection,
            formAuthenticationSources
        },
        methods: {
            setTitle(value) {
                this.title = value;
            },
            reset() {
                this.$refs.formAuthenticationSources.reset();
            },
            load(obj) {
                this.$refs.formAuthenticationSources.load(obj);
            },
            cancel() {
                this.reset();
                this.$emit('cancel');
            },
            rowToForm(row) {
                return this.$refs.formAuthenticationSources.rowToForm(row);
            },
            formToFormData(form) {
                return this.$refs.formAuthenticationSources.formToFormData(form);
            },
            setGridText(data) {
                this.$refs.formAuthenticationSources.setGridText(data);
            },
            getGridText() {
                return this.$refs.formAuthenticationSources.getGridText();
            }
        }
    }
</script>

<style scoped>
</style>