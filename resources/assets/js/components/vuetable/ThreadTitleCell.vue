<template>
    <div class="title-cell">
        <div
            v-for="item in data"
            :key="item.del_id"
            class="d-flex mb-3"
        >
            <p>
                {{ item.thread_title }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: "ThreadTitleCell",
    props: ["data"]
}
</script>