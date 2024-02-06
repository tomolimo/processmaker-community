import ApiInstance from "./Api.js";
import Services from "./Services";
let Api = new ApiInstance(Services);

export let process = {
    list: {
        start(dt) {
            return Api.fetch({
                service: "GET_NEW_CASES",
                method: "get",
                data: {},
                keys: {}
            });
        }
    },
    totalCasesByProcess(dt) {
        return Api.get({
            service: "TOTAL_CASES_BY_PROCESS",
            params: dt,
            keys: {}
        });
    },
    processCategories() {
        return Api.fetch({
            service: "PROCESS_CATEGORIES",
            method: "get",
            data: {},
            keys: {}
        });
    },
    totalCasesByRange(dt) {
      return Api.get({
          service: "TOTAL_CASES_BY_RANGE",
          method: "get",
          params: dt,
          keys: {}
      });
    },
    totalCasesByRisk(dt) {
      return Api.get({
          service: "CASES_RISK",
          method: "get",
          params: dt,
          keys: {}
      });
    },
};