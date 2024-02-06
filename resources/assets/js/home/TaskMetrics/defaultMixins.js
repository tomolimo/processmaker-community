import _ from "lodash";
import api from "../../api/index";
export default {
  data() {
    let that = this;
    return {
      newCase: {
        title: this.$i18n.t("ID_NEW_CASE"),
        class: "btn-success",
        onClick: () => {
          this.$refs["newRequest"].show();
        },
      },
    }
  }
}