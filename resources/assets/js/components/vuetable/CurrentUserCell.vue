<template>
    <div v-if="showTooltip" class="grouped-cell">
        <div v-for="(item, index) in data" v-bind:key="item.TITLE" class="d-flex">
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
                        <div class="font-weight-bold">{{item.USERNAME_DISPLAY_FORMAT}}</div>
                        <div v-if="item.POSITION !== ''">{{item.POSITION}}</div>
                        <b-link :href="mailto(item.EMAIL)" >{{item.EMAIL}}</b-link>
                    </b-col>
                </b-row>
            </b-popover>
            <div class="col ellipsis">
                {{ item.USERNAME_DISPLAY_FORMAT }}
            </div>
        </div>
    </div>
    <div v-else class="grouped-cell">
        <span class="col ellipsis">
            {{ data }}
        </span>
    </div>
</template>

<script>
export default {
    name: "CurrentUserCell",
    props: ["data"],
    data() {
        return {
            id: "avatar-" + _.random(1000000)
        };
    },
    computed: {
        /**
         * Verify if data is an array.
         */
        showTooltip() {
            return typeof this.data !== 'string'
        }
    },
    methods: {
        /**
         * Generates the mail link
         */
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
    width: auto;
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
