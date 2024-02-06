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
     * @returns {Object}
     */
    toExpression(settings) {
        let response = {};
        //starting property
        response.startingTime = settings.startingTime == "" ? null : settings.startingTime;
        //ending property
        response.endingTime = settings.endingTime == "" ? null : settings.endingTime;
        //expression property
        response.expression = this.formatExpression(settings);
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
     * @returns {Object}
     */
    toSettings(settings) {
        let per,
            parse = settings.expression.split(/\s+/);
        this.minutes = parse[0];
        this.hours = parse[1];
        this.days = parse[2];
        this.months = parse[3];
        this.dates = parse[4];

        //Periodicity property
        per = this.minutes + " " + this.hours;
        this.settings.periodicity = this.formatPeriodicity(per);
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
     * @returns {String}
     */
    toDescription(settings) {
        let response = "";
        switch (settings.periodicity.value) {
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
            case "oncePerDay":
                response += window.TRANSLATIONS["ID_ONCE_PER_DAY"] + " " + this.tConvert(settings.periodicity.oncePerDay);
                break;
            case "twicePerDay":
                response += window.TRANSLATIONS["ID_TWICE_PER_DAY"] + " " + this.tConvert(settings.periodicity.oncePerDay) + " & " + this.tConvert(settings.periodicity.twicePerDay);
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
     * @returns {String}
     */
    tConvert(time) {
        // Check correct time format and split into components
        if (time.split(":").length == 3) {
            time = [time.split(":")[0], time.split(":")[1]].join(":");
        }

        time = time.toString().match(/^([01]*\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
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
     * @returns {String}
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
     * @returns {String}
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
    /**
     * Verify if the per string is "dailyAt" or "twiceDaily"
     * @param {*} per 
     * @returns {Boolean}
     */
    verifyPeriodicyIfDailyAt(per) {
        let res = false;
        switch (per) {
            case "*/1 *":
            case "*/5 *":
            case "*/10 *":
            case "*/15 *":
            case "*/30 *":
            case "0 */1":
                break;
            default:
                res = true;
                break;
        }
        return res;
    }
    /**
     * Format the expression * * * * *
     * @param {*} set 
     * @returns {String}
     */
    formatExpression(set) {
        let response = "* * * * * *";
        if (set.periodicity.value == "oncePerDay") {
            response = this.formatPeriodicityDailyAt(set.periodicity.oncePerDay) + " * * " + set.repeatOn.join(",");
        } else if (set.periodicity.value == "twicePerDay") {
            response = this.formatPeriodicityTwiceDaily(set.periodicity.oncePerDay, set.periodicity.twicePerDay) + " * * " + set.repeatOn.join(",");
        } else {
            response = set.periodicity.value + " * * " + set.repeatOn.join(",");
        }
        return response;
    }
    /**
     * Format the string for "dailyAt" property
     * @param {*} dailyAt 
     * @returns {String}
     */
    formatPeriodicityDailyAt(dailyAt) {
        return "0 " + dailyAt.split(":")[0];
    }
    /**
     * Format the strings for "twiceDaily" property
     * @param {*} dailyAt1 
     * @param {*} dailyAt2 
     * @returns {String}
     */
    formatPeriodicityTwiceDaily(dailyAt1, dailyAt2) {
        return "0 " + parseInt(dailyAt1.split(":")[0]) + "," + parseInt(dailyAt2.split(":")[0]);
    }
    /**
     * Return the periodciity object to UI
     * @param {*} per 
     * @returns {Object}
     */
    formatPeriodicity(per) {
        let res = per,
            oncePerDay = "00:00:00",
            twicePerDay = "00:00:00",
            parse = per.split(/\s+/);
        if (this.verifyPeriodicyIfDailyAt(per)) {
            res = "oncePerDay";
            oncePerDay = parse[1].split(",")[0] + ":00:00";
            if (parse[1] && parse[1].split(",").length > 1 && parse[0] == "0") {
                res = "twicePerDay";
                twicePerDay = parse[1].split(",")[1] + ":00:00";
            }
        }
        return {
            value: res,
            oncePerDay,
            twicePerDay
        };
    }
}

module.exports.xCron = xCron;