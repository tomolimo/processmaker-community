<template>
  <div class="container py-2">
    <div class="row"></div>
    <div class="comments col-md-12" id="comments">
      <p class="commentTitle">
        {{ data.title }}
      </p>
      <div v-for="item in data.items" :key="item.date">
        <case-comment
          :data="item"
          :onClick="onClick"
          :selected="item == itemSelected"
          @onSelected="onSelected"
        />
      </div>
    </div>
    <div class="v-comments col-md-12" @dragover="onDragOver">
      <div
        class="mask flex-center rgba-green-strong"
        v-show="showMaskDrop"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDropFile"
      >
        <p class="white-text">{{ $t("ID_UPLOAD_FILE") }}</p>
      </div>
      <div class="comment mb-2 row">
        <div class="comment-avatar col-md-1 col-sm-2 text-center pr-1">
          <a href=""
            ><img
              class="mx-auto rounded-circle v-img-fluid"
              :src="pathImgOwner"
              alt="avatar"
          /></a>
        </div>
        <div class="comment-content col-md-11 col-sm-10 v-comment">
          <div class="comment-meta">
            <a href="#">{{ data.user }}</a> {{ data.date }}
          </div>
          <div class="comment-body">
            <div class="form-group">
              <textarea
                class="form-control"
                name="comments"
                ref="comment"
                cols="80"
                rows="5"
                aria-label="comments"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="comment mb-2 row float-right">
        <div class="form-check v-check-comment">
          <input id="sendEmail" type="checkbox" class="form-check-input" ref="send" />
          <label class="form-check-label" for="sendEmail">
            {{ $t("ID_SEND_EMAIL_CASE_PARTICIPANTS") }}</label
          >
        </div>

        <button class="btn btn-success btn-sm" @click="onClickComment" :disabled="data.noPerms === 1">
          {{ $t("ID_SEND") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import CaseComment from "./CaseComment.vue";

export default {
  name: "CaseComments",
  props: {
    data: Object,
    onClick: Function,
    postComment: Function,
    dropFiles: Function,
  },
  components: {
    CaseComment,
  },
  data() {
    return {
      showMaskDrop: false,
      files: [],
      itemSelected: null,
    };
  },
  computed: {
    pathImgOwner() {
      return (
        window.config.SYS_SERVER_AJAX +
        window.config.SYS_URI +
        `users/users_ViewPhotoGrid?pUID=${window.config.USR_UID}`
      );
    },
  },
  methods: {
    classBtn(cls) {
      return "btn v-btn-request " + cls;
    },
    classIcon(icon) {
      return this.icon[icon];
    },
    onClickComment() {
      let fls = this.files;
      this.postComment(
        this.$refs["comment"].value,
        this.$refs["send"].checked,
        fls
      );
      this.resetComment();
    },
    resetComment() {
      this.$refs["comment"].value = "";
      this.$refs["send"].checked = false;
      this.files = [];
    },
    onSelected(item) {
      this.itemSelected = item;
    },
    onDropFile(e) {
      e.preventDefault();
      e.stopPropagation();
      if(this.data.noPerms === 1){
        return;
      }
      let that = this,
        fls = [];
      _.each(e.dataTransfer.files, (f) => {
        that.files.push(f);
      });
      that.files = that.files.slice(0,5);
      _.each(that.files, (f) => {
        fls.push({
          data: f,
          title: f.name,
          extension: f.name.split(".").pop(),
          onClick: () => {},
          id: _.random(1000000)
        });
      });

      this.dropFiles(fls);
      this.showMaskDrop = false;
    },
    onDragOver(e) {
      e.preventDefault();
      if(this.data.noPerms === 1){
        return;
      }
      if (!this.showMaskDrop) {
        this.showMaskDrop = true;
      }
    },
    onDragLeave(e) {
      if (this.showMaskDrop) {
        this.showMaskDrop = false;
      }
    },
    removeFile(file) {
      _.remove(this.files, function (n) {
        return file.title == n.name;
      });
    },
  },
};
</script>

<style>
.v-check-comment {
  padding-right: 20px;
}

.v-comments {
  display: inline-block;
}

.mask {
  z-index: 100;
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background-attachment: fixed;
  outline: 5px dashed #2ba070;
  outline-offset: -20px;
}
.flex-center {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  -webkit-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
  height: 100%;
}
.rgba-green-strong,
.rgba-green-strong:after {
  background-color: rgba(76, 175, 80, 0.7);
}

.v-img-fluid {
  max-width: 30px;
  height: auto;
}
.white-text {
  font-size: 32px;
  color: antiquewhite;
}
.commentTitle {
  font-weight: bolder;
}
</style>