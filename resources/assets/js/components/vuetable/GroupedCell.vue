<template>
    <div v-if="data.length" class="grouped-cell">
        <div v-for="(item, index) in data" v-bind:key="item.TITLE" class="d-flex mb-3">
            <div
                v-bind:style="{ color: activeColor(item.STATUS) }"
                :id="statusId + index"
            >
                <i class="fas fa-square"></i>
            </div>
            <b-popover :target="statusId + index" triggers="hover" placement="top">
                <b> {{ item.DELAYED_TITLE }} </b> {{ item.DELAYED_MSG }}
            </b-popover>
            <div class="col ellipsis" v-b-popover.hover.top="item.TAS_NAME">
                {{ item.TAS_NAME }}
            </div>
            <div class="avatar" :id="id + index">
                <b-avatar
                    variant="info"
                    :src="item.AVATAR"
                    size="2em"
                    v-show="item.UNASSIGNED"
                ></b-avatar>
            </div>
            <b-popover
                :target="id + index"
                placement="top"
                ref="popover"
                triggers="hover"    
            >
                <b-row >
                    <b-col md="3">
                        <b-avatar
                            variant="info"
                            :src="item.AVATAR"
                            size="4em"
                            v-show="item.UNASSIGNED"
                        ></b-avatar>
                    </b-col>    
                    <b-col md="9">
                        <div class="font-weight-bold">{{item.USERNAME}}</div>
                        <div v-if="item.POSITION !== ''">{{item.POSITION}}</div>
                        <b-link :href="mailto(item.EMAIL)" >{{item.EMAIL}}</b-link>
                    </b-col>
                </b-row>
            </b-popover>
        </div>
    </div>
</template>

<script>
export default {
    name: "GroupedCell",
    props: ["data"],
    data() {
        return {
            //Color map for ["In Progress", "overdue", "inDraft", "paused", "unnasigned"]
            colorMap: ["green", "red", "orange", "blue", "silver"],
            id: "avatar-" + _.random(1000000),
            statusId: "status-" + _.random(1000000)
        };
    },
    methods: {
        /**
         * Get the style color to be applied in the square icon
         * @param {number} - status color(1-5)
         * @return {string} - color atribute string
         */
        activeColor: function(codeColor) {
            return this.colorMap[codeColor - 1];
        },
        mailto: function(email) {
            return "mailto:" + email;
        }
    }
};
</script>

<style> 
.popover {
    max-width: 600px !important; 
    min-width: 200px !important;
}
.grouped-cell {
    font-size: small;
}

.ellipsis {
    white-space: nowrap;
    width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.color {
    color: red;
}
.avatar {
    color: "red";
    width: "1.3em";
}
</style>
