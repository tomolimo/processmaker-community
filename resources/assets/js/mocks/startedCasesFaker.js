

function createFakerData () {
    var faker = require("faker");
    var _ = require("lodash");
    var  total = 20;
    return {
        
        data:_.times(total,function (n) {
            return  {
                "APP_NUMBER": faker.random.number(),
                "APP_TITLE": faker.name.title(),
                "PRO_TITLE": faker.finance.accountName(),
                "TAS_TITLE": faker.company.companyName(),
                "PENDING_TASKS": [
                    {
                        TAS_NAME: faker.company.companyName(),
                        STATUS: faker.random.number({ min:1, max:5 }),
                        AVATAR: faker.image.image(),
                        DELAYED_MSG: faker.name.title()
                    },
                    {
                        TAS_NAME: faker.company.companyName(),
                        STATUS: faker.random.number({ min:1, max:5 }),
                        AVATAR: faker.image.image(),
                        DELAYED_MSG: faker.name.title()
                    }
                    
                ],
                "APP_STATUS": "IN PROGRESS",
                "DEL_DELEGATE_DATE": "10/10/10",
                "DEL_DELEGATE_DATE_LABEL": "Nov 17th 2017",
                "DEL_FINISH_DATE": "10/10/10",
                "DEL_FINISH_DATE_LABEL": "January 17th 2017",
                "DURATION": "4 days",
                "DURATION_LABEL": "4 months 4 days"
            }
        }),
        total : total
    }
}
module.exports = createFakerData();
