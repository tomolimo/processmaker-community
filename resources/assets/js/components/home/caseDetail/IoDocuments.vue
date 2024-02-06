<template>
  <div class="card v-case-summary-card" style="width: 20rem">
    <div class="card-body">
      <h6 class="card-subtitle mb-2 text-muted">{{ data.titleInput }}</h6>
      <div class="card-text">
        <div
          v-for="item in data.inputDocuments"
          :key="item.data.APP_DOC_UID"
          class="v-attached-block"
        >
          <div class="v-list v-list-row block">
            <div class="v-list-item">
              <div class="v-attached-icon">
                <i :class="classIcon(item.extension)"></i>
              </div>
              <div class="flex">
                <a
                  @click="item.onClick"
                  :href="href(item)"
                  class="v-item-except text-sm h-1x"
                >
                  {{ item.title }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <br />
      <h6 class="card-subtitle mb-2 text-muted">{{ data.titleOutput }}</h6>
      <div class="card-text">
        <div
          v-for="item in data.outputDocuments"
          :key="item.title"
          class="v-attached-block"
        >
          <div class="v-list v-list-row block">
            <div class="v-list-item">
              <div class="v-attached-icon">
                <i :class="classIcon(item.extension)"></i>
              </div>
              <div class="flex">
                <a
                  @click="item.onClick"
                  target="_blank"
                  :href="hrefOutput(item)"
                  class="v-item-except text-sm h-1x"
                >
                  {{ item.title }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "IoDocuments",
  props: {
    data: Object,
  },
  data() {
    return {
      icon: {
        pdf: "fas fa-file-pdf",
        doc: "fas fa-file-word",
        png: "fas fa-picture-o",
      },
    };
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    classIcon(icon) {
      return this.icon[icon] ? this.icon[icon] : "fas fa-file-alt";
    },
    href(item) {
      if (item.data.DOWNLOAD_LINK) {
        return (
          window.config.SYS_SERVER_AJAX +
          window.config.SYS_URI +
          `cases/${item.data.DOWNLOAD_LINK}`
        );
      }
      return (
        window.config.SYS_SERVER_AJAX +
        window.config.SYS_URI +
        `cases/cases_ShowDocument?a=${item.data.APP_DOC_UID}&v=${item.data.DOC_VERSION}`
      );
    },
    hrefOutput(item) {
      let random = _.random(0, 10000000),
        cacheTime = Date.now();
      return (
        window.config.SYS_SERVER_AJAX +
        window.config.SYS_URI +
        `cases/${item.data.DOWNLOAD_LINK}`
      );
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
</style>