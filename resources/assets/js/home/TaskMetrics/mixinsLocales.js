export default {
    data() {
        let that = this;
        return {
            options: {
                chart: {
                    locales: [
                        {
                            name: "en",
                            options: {
                                months: [
                                    this.$t("ID_MONTH_1"),
                                    this.$t("ID_MONTH_2"),
                                    this.$t("ID_MONTH_3"),
                                    this.$t("ID_MONTH_4"),
                                    this.$t("ID_MONTH_5"),
                                    this.$t("ID_MONTH_6"),
                                    this.$t("ID_MONTH_7"),
                                    this.$t("ID_MONTH_8"),
                                    this.$t("ID_MONTH_9"),
                                    this.$t("ID_MONTH_10"),
                                    this.$t("ID_MONTH_11"),
                                    this.$t("ID_MONTH_12"),
                                ],
                                shortMonths: [
                                    this.$t("ID_MONTH_ABB_1"),
                                    this.$t("ID_MONTH_ABB_2"),
                                    this.$t("ID_MONTH_ABB_3"),
                                    this.$t("ID_MONTH_ABB_4"),
                                    this.$t("ID_MONTH_ABB_5"),
                                    this.$t("ID_MONTH_ABB_6"),
                                    this.$t("ID_MONTH_ABB_7"),
                                    this.$t("ID_MONTH_ABB_8"),
                                    this.$t("ID_MONTH_ABB_9"),
                                    this.$t("ID_MONTH_ABB_10"),
                                    this.$t("ID_MONTH_ABB_11"),
                                    this.$t("ID_MONTH_ABB_12"),
                                ],
                                days: [
                                    this.$t("ID_WEEKDAY_0"),
                                    this.$t("ID_WEEKDAY_1"),
                                    this.$t("ID_WEEKDAY_2"),
                                    this.$t("ID_WEEKDAY_3"),
                                    this.$t("ID_WEEKDAY_4"),
                                    this.$t("ID_WEEKDAY_5"),
                                    this.$t("ID_WEEKDAY_6"),
                                ],
                                shortDays: [
                                    this.$t("ID_WEEKDAY_ABB_0"),
                                    this.$t("ID_WEEKDAY_ABB_1"),
                                    this.$t("ID_WEEKDAY_ABB_2"),
                                    this.$t("ID_WEEKDAY_ABB_3"),
                                    this.$t("ID_WEEKDAY_ABB_4"),
                                    this.$t("ID_WEEKDAY_ABB_5"),
                                    this.$t("ID_WEEKDAY_ABB_6"),
                                ],
                                toolbar: {
                                    exportToSVG: this.$t("ID_DOWNLOAD_SVG"),
                                    exportToPNG: this.$t("ID_DOWNLOAD_PNG"),
                                    menu: this.$t("ID_MENU"),
                                    selection: this.$t(
                                        "ID_PROCESSMAP_SELECTION"
                                    ),
                                    selectionZoom: this.$t("ID_SELECTION_ZOOM"),
                                    zoomIn: this.$t("ID_ZOOM_IN"),
                                    zoomOut: this.$t("ID_ZOOM_OUT"),
                                    pan: this.$t("ID_PANNING"),
                                    reset: this.$t("ID_RESET"),
                                },
                            },
                        },
                    ],
                    defaultLocale: "en",
                },
            },
        };
    },
};
