<template>
  <div class="pm-vue-card">
    <div
      :class="item.INIT_DATE ? 'pm-case-unread' : 'pm-case-read'"
      class="card pm-vue-card-inside"
      style="width: 20rem"
      @dblclick="dblClick"
    >
      <div class="card-view-body">
        <slot> </slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "VueCardView",
  props: ["options", "item"],
  data() {
    return {};
  },
  methods: {
    classBtn(cls) {
      return "btn btn-slim btn-force-radius v-btn-header " + cls;
    },
    /**
     * Event handler dbl click
     */
    dblClick(event){
      this.options.dblClick(event, this.item, this.options);
    }
  },
};
</script>

<style>
.pm-vue-card {
  display: inline-block;
  padding: 0.7rem;
}
.pm-vue-card-inside {
  border-left: solid lightseagreen;
  color: #212529;
  background-color: #f8f9fa;
}
.pm-vue-card-inside:hover {
  background-color: #cfd9e4;
}
.card-view-body {
  height: 266px;
  overflow-x: hidden;
  flex: 1 1 auto;
  min-height: 1px;
  padding: 1.25rem;
}
.pm-case-read {
  border-left: 15px solid transparent;
  border-radius: 0.25rem;
  border-width: 1px 1px 1px 15px;
  background-image:
    linear-gradient(white, white),
    linear-gradient(to bottom, #70C6F3, #0099DC);
  background-origin: border-box;
  background-clip: content-box, border-box;
}
.pm-case-unread {
  border-left: 15px solid transparent;
  border-radius: 0.25rem;
  border-width: 1px 1px 1px 15px;
  background-image: 
    linear-gradient(white, white),
    linear-gradient(to bottom, rgba(204,204,204,1),rgba(135,135,135,1));
  background-origin: border-box;
  background-clip: content-box, border-box;
}
</style>
