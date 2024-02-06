class xCron {
    constructor() {
        this.minutes = null;
        this.hours = null;
        this.days = null;
        this.months = null;
        this.dates = null;

        this.settings = {};
    }
    /**
     * Convert settings from modal properties to API properties
     * @param {*} settings 
     */
    toExpression(settings) {
        let response = {};
        //starting property
        response.startingTime = settings.startingTime == "" ? null : settings.startingTime;
        //ending property
        response.endingTime = settings.endingTime == "" ? null : settings.endingTime;
        //expression property
        response.expression = settings.periodicity != "" ? settings.periodicity + " * * " + settings.repeatOn.join(",") : "0 */" + settings.periodicityUnit + " * * " + +settings.repeatOn.join(",");
        //timezone
        response.timezone = settings.timezone == "" ? null : settings.timezone;
        //Every on
        response.everyOn = settings.everyOn;
        //Interval
        response.interval = settings.interval;

        return response;
    }

    /**
     * Convert the settings from API to modal properties
     * @param {*} settings 
     */
    toSettings(settings) {
        let parse = settings.expression.split(/\s+/);
        this.minutes = parse[0];
        this.hours = parse[1];
        this.days = parse[2];
        this.months = parse[3];
        this.dates = parse[4];

        //Periodicity property
        this.settings.periodicity = this.minutes + " " + this.hours;
        //Repeat On
        this.settings.repeatOn = this.dates == "*" ? ['0', '1', '2', '3', '4', '5', '6'] : this.dates.split(",");
        //Starting Time
        this.settings.startingTime = !settings.startingTime ? "" : settings.startingTime;
        //Ending Time
        this.settings.endingTime = !settings.endingTime ? "" : settings.endingTime;
        //everyOn
        this.settings.everyOn = settings.everyOn;
        //interval
        this.settings.interval = settings.interval;
        //timezone
        this.settings.timezone = settings.timezone;
        //Description
        this.settings.description = this.toDescription(this.settings);

        return this.settings;
    }

    /**
     * Return the literal description from schedule time
     * @param {*} settings 
     */
    toDescription(settings) {
        let response = "";
        switch (settings.periodicity) {
            case "*/1 *":
                response += window.TRANSLATIONS["ID_EVERY_MINUTE"];
                break;
            case "*/5 *":
                response += window.TRANSLATIONS["ID_EVERY_FIVE_MINUTES"];
                break;
            case "*/10 *":
                response += window.TRANSLATIONS["ID_EVERY_TEN_MINUTES"];
                break;
            case "*/15 *":
                response += window.TRANSLATIONS["ID_EVERY_FIFTEEN_MINUTES"];
                break;
            case "*/30 *":
                response += window.TRANSLATIONS["ID_EVERY_THIRTY_MINUTES"];
                break;
            case "0 */1":
                response += window.TRANSLATIONS["ID_EVERY_HOUR"];
                break;
        }

        if (settings.startingTime) {
            response += " " + window.TRANSLATIONS["ID_AT_TILL"].replace("${0}", this.tConvert(settings.startingTime)).replace("${1}", this.tConvert(settings.endingTime));
        }

        if (settings.timezone) {
            response += " " + window.TRANSLATIONS["ID_TIME_IN"].replace("${0}", settings.timezone);
        }

        if (settings.everyOn) {
            response += this.intervalToDescription(settings.everyOn, settings.interval);
        }

        if (settings.repeatOn) {
            response += this.repeatOnToDescription(settings.repeatOn);
        }

        return response;
    }

    /**
     * Convert time string {0-23} to AM/PM
     * @param {*} time 
     */
    tConvert(time) {
        // Check correct time format and split into components
        if (time.split(":").length == 3) {
            time = [time.split(":")[0], time.split(":")[1]].join(":");
        }
        time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
        if (time.length > 1) { // If time format correct
            time = time.slice(1);  // Remove full string match value
            time[5] = +time[0] < 12 ? 'AM' : 'PM'; // Set AM/PM
            time[0] = +time[0] % 12 || 12; // Adjust hours
        }
        return time.join(''); // return adjusted time or original string
    }

    /**
     * Return string , from interval property
     * @param {*} everyOn 
     * @param {*} interval 
     */
    intervalToDescription(everyOn, interval) {
        let res = "",
            ds = {
                "week": window.TRANSLATIONS["ID_WEEK"],
                "month": window.TRANSLATIONS["ID_MONTH"],
                "year": window.TRANSLATIONS["ID_YEAR"],
            },
            dm = {
                "week": window.TRANSLATIONS["ID_WEEKS"],
                "month": window.TRANSLATIONS["ID_MONTHS"],
                "year": window.TRANSLATIONS["ID_YEARS"],
            };
        if (everyOn == "1") {
            res += " " + window.TRANSLATIONS["ID_EVERY"].replace("${0}", "").replace("${1}", ds[interval]);
        } else {
            res += " " + window.TRANSLATIONS["ID_EVERY"].replace("${0}", everyOn).replace("${1}", dm[interval]);
        }
        if (everyOn == "1" && interval == "week") {
            res = "";
        }

        return res;
    }
    /**
     * Return string, from repeatOn property
     * @param {*} repeatOn 
     */
    repeatOnToDescription(repeatOn) {
        let response = " on ",
            days = [
                window.TRANSLATIONS['ID_WEEKDAY_0'],
                window.TRANSLATIONS['ID_WEEKDAY_1'],
                window.TRANSLATIONS['ID_WEEKDAY_2'],
                window.TRANSLATIONS['ID_WEEKDAY_3'],
                window.TRANSLATIONS['ID_WEEKDAY_4'],
                window.TRANSLATIONS['ID_WEEKDAY_5'],
                window.TRANSLATIONS['ID_WEEKDAY_6']
            ];

        for (let i = 0; i < repeatOn.length; i += 1) {
            response += days[parseInt(repeatOn[i])];
            response += (i == repeatOn.length - 1) ? " " : ", ";
        }

        if (repeatOn.length == 7) {
            response = "";
        }
        return response;
    }
}

module.exports.xCron = xCron;