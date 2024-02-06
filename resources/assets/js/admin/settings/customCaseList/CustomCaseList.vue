<template>
    <div
        id="home"
    >
        <div class="demo">
            <div class="container" v-if="!showSketch">
                <h5 >{{ $t("ID_CUSTOM_CASES_LISTS") }}</h5>
                <div class="x_content">
                    <b-container fluid>
                        <b-tabs content-class="mt-3" @input="onInputTab">
                            <b-tab v-for="i in tabs" :key="'dyn-tab-' + i.key" :title="i.title" lazy>
                               <Tables :module="i.key" 
                                    @showSketch="onShowSketch"
                                    @closeSketch="onCloseSketch"
                                />
                            </b-tab>
                        </b-tabs> 
                    </b-container>
                </div> 
            </div>
            <div class="container" v-if="showSketch">
                <CaseListSketch 
                    @showSketch="onShowSketch"  
                    @closeSketch="onCloseSketch"
                    :module="tabModule"
                    :params="params"
                />
            </div>
        </div>
    </div>
</template>
<script>
import Tables from "./Tables";
import CaseListSketch from "./CaseListSketch"
export default {
    name: "CustomCaseList",
    components: {
        Tables,
        CaseListSketch
    },
    data() {
        return {
            showSketch: false,
            params: {},
            tabModule: null,
            tabs: [
                {
                    key: "inbox",
                    title: this.$i18n.t("TO_DO")
                },
                {
                    key: "draft",
                    title: this.$i18n.t("ID_DRAFT")
                },
                {
                    key: "unassigned",
                    title: this.$i18n.t("ID_UNASSIGNED")
                },
                {
                    key: "paused",
                    title: this.$i18n.t("ID_PAUSED")
                }
            ]
        };
    },
    mounted() {
       this.tabModule= this.tabs[0];
    },
    methods: {
       /**
         * Show sketch
         */
        onShowSketch (params) {
            this.showSketch = true;
            this.params = params;
        },
        /**
         * Close sketch
         */
        onCloseSketch (params) {
            this.showSketch = false;
        },
        /**
         * On change input
         */
        onInputTab(tabIndex){
            this.tabModule= this.tabs[tabIndex];
        }
    }
};
</script>

<style lang="scss">
#home {
    padding-left: 0px;
    transition: 0.3s;
}
#home.collapsed {
    padding-left: 50px;
}
#home.onmobile {
    padding-left: 50px;
}

.container {
    max-width: 1500px;
}
</style>
