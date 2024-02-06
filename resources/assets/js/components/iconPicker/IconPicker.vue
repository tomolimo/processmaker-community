<template>
    <div class="rbt-icon-picker">
        <b-button @click="popUpActive = true">
            <i ref="icon" :class="icon"></i>
        </b-button>
        <div class="rip-popup-component" :style="popupActiveStyle">
            <div class="rip-popup-bg"></div>
            <div class="rip-popup">
                <div class="rip-popup-content">
                    <div class="rip-search">
                        <div class="rip-input">
                            <label for="ripSearch" style="display: none;"
                                >{{$t("ID_SEARCH_FOR_ICON")}}</label
                            >
                            <input
                                id="ripSearch"
                                :placeholder="$t('ID_SEARCH_FOR_ICON')"
                                v-model="searchText"
                                @input="searchTextChanged"
                            />
                            <span class="input-append">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                    <div class="rip-content">
                        <div class="rip-not-found" v-show="loading">
                            <i class="fas fa-spinner fa-pulse"></i>
                        </div>
                        <div class="rip-icons" v-show="!loading">
                            <h4 class="icon-title">
                                {{$t('ID_REGULAR_ICONS')}}
                            </h4>
                            <p
                                style="text-align: center;"
                                v-if="regularIcons.length <= 0"
                            >
                                <i class="fas fa-eye-slash"></i>
                                {{$t('ID_SORRY_NO_ICONS')}}
                            </p>
                            <ul class="rip-row" v-if="regularIcons.length > 0">
                                <li
                                    v-for="(icon, index) in regularIcons"
                                    :key="index"
                                    class="rip-col"
                                >
                                    <div class="icon-content text-center">
                                        <div
                                            class="icon-el"
                                            @click="selectIcon(icon, 'far')"
                                        >
                                            <i :class="`far fa-${icon}`"></i>
                                        </div>
                                        <div class="icon-title">
                                            {{ icon }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <h4 class="icon-title">
                                {{$t('ID_BRAND_ICONS')}}
                            </h4>
                            <p
                                style="text-align: center;"
                                v-if="brandIcons.length <= 0"
                            >
                                <i class="fas fa-eye-slash"></i>
                                {{$t('ID_BRAND_ICONS_NOT_FOUND')}}
                            </p>
                            <ul class="rip-row" v-if="brandIcons.length > 0">
                                <li
                                    v-for="(icon, index) in brandIcons"
                                    :key="index"
                                    class="rip-col"
                                >
                                    <div class="icon-content text-center">
                                        <div
                                            class="icon-el"
                                            @click="selectIcon(icon, 'fab')"
                                        >
                                            <i :class="`fab fa-${icon}`"></i>
                                        </div>
                                        <div class="icon-title">
                                            {{ icon }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <h4 class="icon-title">
                                {{$t('ID_SOLID_ICONS')}}
                            </h4>
                            <p
                                style="text-align: center;"
                                v-if="solidIcons.length <= 0"
                            >
                                <i class="fas fa-eye-slash"></i>
                                {{$t('ID_SOLID_ICONS_NOT_FOUND')}}
                            </p>
                            <ul class="rip-row" v-if="solidIcons.length > 0">
                                <li
                                    v-for="(icon, index) in solidIcons"
                                    :key="index"
                                    class="rip-col"
                                >
                                    <div class="icon-content text-center">
                                        <div
                                            class="icon-el"
                                            @click="selectIcon(icon, 'fas')"
                                        >
                                            <i :class="`fas fa-${icon}`"></i>
                                        </div>
                                        <div class="icon-title">
                                            {{ icon }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ripIcons from "./assets/icons";
export default {
    name: "VueAwesomeIconPicker",
    props: {
        button: {
            type: String,
            default: "Pick A Icon",
        },
        color: {
            type: String,
            default: "black",
        },
        title: {
            type: String,
            default: "Vue Awesome Icon Picker",
        },
        iconPreview: {
            type: Boolean,
            default: true,
        },
        default: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            allIcons: {
                brand: [],
                regular: [],
                solid: [],
            },

            popUpActive: false,
            icon: null,
            searchText: "",
            searchIconNotFound: false,
        };
    },
    watch: {
        color() {
        },
    },
    methods: {
        /**
         * Handler select icon
         */
        selectIcon(icon, type) {
            this.icon = `${type} fa-${icon}`;
            this.popUpActive = false;
            this.$emit("selected", this.icon);
        },
        /**
         * Handler search text changed 
         */
        searchTextChanged() {
            this.searchIcon(this.searchText);
        },
        /**
         * Set default icons
         */
        setDefaultIcons() {
            this.allIcons.brand = ripIcons.brand;
            this.allIcons.regular = ripIcons.regular;
            this.allIcons.solid = ripIcons.solid;
        },
        /**
         * Serach icons handler
         */
        searchIcon(txt) {
            this.loading = true;
            if (txt && txt.length > 0) {
                setTimeout(() => {
                    this.loading = false;
                }, 950);

                txt = txt.toLowerCase();
                Object.keys(ripIcons).forEach((key) => {
                    setTimeout(() => {
                        let icons = ripIcons[key].filter(
                            (ico) => ico.indexOf(txt) > -1
                        );
                        if (icons && icons.length > 0) {
                            this.allIcons[key] = icons;
                        } else {
                            this.allIcons[key] = [];
                        }
                    }, 320);
                });
            } else {
                setTimeout(() => {
                    this.setDefaultIcons();
                    this.loading = false;
                }, 950);
            }
        },
    },
    created() {
        this.setDefaultIcons();
        if (this.default) {
            this.icon = this.default;
        }
    },
    computed: {
        popupActiveStyle() {
            return !this.popUpActive ? "display: none;" : "";
        },
        brandIcons() {
            return this.loading ? [] : this.allIcons.brand;
        },
        solidIcons() {
            return this.loading ? [] : this.allIcons.solid;
        },
        regularIcons() {
            return this.loading ? [] : this.allIcons.regular;
        },
    },
};
</script>

<style lang="scss" scoped>
@import "./assets/RbtIconPicker";
</style>
