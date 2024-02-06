export default {
  data() {
    let that = this;
    return {
      height: 0,
      config: {
        page: 1
      },
      data: []
    }
  },
  mounted: function () {
    this.getData();
    this.getBodyHeight();
  },
  methods: {
    /**
     * Get data similar to vue Table
     */
    getData() {
      let options = _.extend({}, this.config, this.options),
        that = this;
      this.options.requestFunction(options)
        .then((data) => {
          that.data = data.data;
        })
        .catch((e) => {
          console.error(e);
        });
    },
    /**
     * Get data when press the button more view
     */
    viewMore() {
      let options = _.extend({}, this.config, this.options, { page: this.config.page + 1 }),
        that = this;
      this.options.requestFunctionViewMore(options)
        .then((data) => {
          if (data.data && data.data.length != 0) {
            that.data = that.data.concat(data.data);
            that.config.page += 1;
          } else {
            that.loadMore = that.$t("ID_NO_MORE_INFORMATION");
          }
        })
        .catch((e) => {
          console.error(e);
        });
    },
    /**
     * Return the height for Vue Card View body
     */
    getBodyHeight() {
      this.height = window.innerHeight - this.$root.$el.clientHeight;
    }
  }
}