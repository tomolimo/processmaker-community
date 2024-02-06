<template>
  <div
    id="v-pm-drill-down"
    ref="v-pm-drill-down"
    class="v-pm-drill-down vp-inline-block"
  >
    <div class="p-1 v-flex">
      <h6 class="v-search-title">{{ $t("ID_DRILL_DOWN_NAVIGATOR") }}</h6>
    </div>
    <div
      v-for="item in loadItems()"
      :key="item.content"
      class="vp-padding-b10"
      @click="onClick(item)"
    >
      <span class="vp-inline-block vp-padding-r10 vp-font-size-r1">
        {{ item.label }}
      </span>
      <div class="vp-inline-block">
        <span :class="item.classObject"> {{ item.content }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
export default {
  name: "DrillDown",
  mixins: [],
  components: {},
  props: ["visited"],
  data() {
    let that = this;
    return {
      classObject: {
        "rounded-circle": true,
        "v-pm-drill-down-number": true,
        "vp-btn-secondary": true,
        "vp-btn-primary-inactive": false,
        "btn-primary": false,
        "vp-block": true,
      },
      data: [
        {
          label: that.$t("ID_LEVEL"),
          content: 0,
          click(elem) {
            that.$emit("onChangeLevel", elem);
          },
        },
        {
          label: that.$t("ID_LEVEL"),
          content: 1,
          click(elem) {
            that.$emit("onChangeLevel", elem);
          },
        },
        {
          label: that.$t("ID_LEVEL"),
          content: 2,
          click(elem) {
            that.$emit("onChangeLevel", elem);
          },
        },
        {
          label: that.$t("ID_LEVEL"),
          content: 3,
          click(elem) {
            that.$emit("onChangeLevel", elem);
          },
        },
      ],
    };
  },
  created() {},
  mounted() {},
  watch: {},
  computed: {
      level: function () {
          return _.find(this.visited, {'active': true }).level ;
      }
  },
  updated() {},
  beforeCreate() {},
  methods: {
    /**
     * Click in drill option
     */
    onClick(item) {
      let elem =_.find(this.visited, {'level': item.content });
      if (elem) {
        item.click(elem);
      }
    },
    /**
     * Load items in drill items
     */
    loadItems() {
      let array,
        that = this,
        item;
      array = _.clone(this.data);
      array.forEach((el) => {
        el.classObject = _.clone(that.classObject);
        item =_.find(this.visited, {'level': el.content });
        if (item) {
            if (item.active){
              el.classObject["vp-btn-primary-inactive"] = false;
              el.classObject["vp-btn-secondary"] = false;
              el.classObject["btn-primary"] = true;
            } else {
              el.classObject["vp-btn-secondary"] = false;
              el.classObject["btn-primary"] = false;
              el.classObject["vp-btn-primary-inactive"] = true;
            }
          }
      }); 
      return array;
    },
  },
};
</script>
<style>
.v-pm-drill-down-number {
  height: 5rem;
  width: 5rem;
  text-align: center;
  line-height: 5rem;
  font-size: 1.5rem;
}

.vp-inline-block {
  display: inline-block;
}
.vp-block {
  display: block;
}
.vp-padding-r10 {
  padding-right: 10px;
}

.vp-padding-b10 {
  padding-bottom: 10px;
}

.vp-font-size-r1 {
  font-size: 1rem;
}

.vp-btn-secondary {
  color: #2f3133;
  background-color: #b5b6b6;
}

.vp-btn-secondary:hover {
  color: #fff;
  background-color: #6c757d;
  border-color: #6c757d;
}

.vp-btn-primary-inactive {
    color: #6c757d;
    background-color: #007bff;
    border-color: #007bff;
}

.vp-btn-primary-inactive:hover {
  color: #6c757d;
  background-color: #0066d3;
  border-color: #0066d3;
}

.v-pm-drill-down {
  vertical-align: top;
  padding-left: 50px;
}
</style>