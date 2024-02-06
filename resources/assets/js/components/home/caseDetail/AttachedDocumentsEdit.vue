<template>
  <div class="card v-case-summary-card" style="width: 20rem">
    <div class="card-body">
      <h6 class="card-subtitle mb-2 text-muted">{{ data.title }}</h6>
      <div class="card-text">
        <div
          v-for="item in data.items"
          :key="item.data.id"
          class="v-attached-block"
        >
          <span>
            <div class="v-list v-list-row block">
              <div
                class="float-right text-md text-danger btn-default"
                @click="removeDocument(item)"
              >
                <i class="fas fa-times-circle"></i>
              </div>
              <div class="v-list-item">
                <div class="v-attached-icon">
                  <i :class="classIcon(item.extension)"></i>
                </div>
                <div class="flex">
                  <a
                    @click="item.onClick(item)"
                    class="v-item-except text-sm h-1x"
                  >
                    {{ item.title }}
                  </a>
                </div>
              </div>
            </div></span
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AttachedDocumentsEdit",
  props: {
    data: Object,
    onRemove: Function,
  },
  data() {
    return {
      icon: {
        pdf: "fas fa-file-pdf",
        doc: "fas fa-file-word",
        png: "fas fa-image",
      },
    };
  },
  computed: {},
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    classIcon(icon) {
      return this.icon[icon] ? this.icon[icon] : "fas fa-file-alt";
    },
    /**
     * Remove file from view and update the view
     */
    removeDocument(item) {
      let dt = this.data.items;
      _.remove(dt, function (n) {
        return item.title == n.title;
      });
      this.data.items = dt;
      this.$forceUpdate();
      this.onRemove(item);
    },
  },
};
</script>

<style>
.v-list-item {
  position: relative;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-direction: column;
  flex-direction: column;
  min-width: 0;
  word-wrap: break-word;
}

.v-list-row .v-list-item {
  -ms-flex-direction: row;
  flex-direction: row;
  -ms-flex-align: center;
  align-items: center;
}

.block {
  background: #fff;
  border-width: 0;
  border-radius: 0.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.v-list {
  padding-left: 0;
  padding-right: 0;
}

.v-item-except {
  padding-left: 10px;
  color: #6c757d !important;
}

.v-attached-icon {
  font-size: 25px;
}

.text-md {
  font-size: 20px;
}
</style>