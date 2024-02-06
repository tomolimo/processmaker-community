<template>
    <div class="grouped-cell">
        <div class="d-flex justify-content-center avatar" :id="`label-${data.caseListId}`">
            <b-avatar
                variant="info"
                :src="data.userAvatar"
                size="2em"
            ></b-avatar>
        </div>
        <b-popover
                :target="`label-${data.caseListId}`"
                placement="top"
                ref="popover"
                triggers="hover"    
            >
                <b-row >
                    <b-col md="3">
                        <b-avatar
                            variant="info"
                            :src="data.userAvatar"
                            size="4em"
                        ></b-avatar>
                    </b-col>    
                    <b-col md="9">
                        <div class="font-weight-bold">{{data.userInfo}}</div>
                        <div v-if="data.userPosition !== ''">{{data.userPosition}}</div>
                        <b-link :href="mailto(data.userEmail)" >{{data.userEmail}}</b-link>
                    </b-col>
                </b-row>
            </b-popover>
    </div>
</template>

<script>
export default {
    name: "OwnerCell",
    props: ["data"],
    data() {
        return {
            //Color map for ["In Progress", "overdue", "inDraft", "paused", "unnasigned"]
            colorMap: ["green", "red", "orange", "aqua", "silver"],
            id: "avatar-" + _.random(1000000),
            statusId: "status-" + _.random(1000000)
        };
    },
    methods: {
        /**
         * Mail to link
         * @param {string} email
         * @returns {string}
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
.avatar {
    color: "red";
    width: "1.3em";
}
</style>
