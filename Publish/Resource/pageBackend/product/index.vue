<template>
    <Layout>
        <Table
            :columns="props.columns"
            :model="props.model"
            :endpoint="props.endpoint"
            :endpoint-delete="props.endpointDelete"
            :endpoint-create="props.endpointCreate"
            :endpoint-edit="props.endpointEdit"
            :table-title="'Product'"
            :permission="props.permission"
            :custom_edit_route="props.custom_edit_route"
        />
    </Layout>
</template>

<script setup>
import Layout from "@backend_layout/App.vue";

// Import the table component from the builder api
import Table from "@builder/Table.vue";

const props = defineProps({
    endpoint: {
        type: String,
        default: "",
    },
    columns: {
        type: Object,
        default: () => ({}),
    },
    model: {
        type: String,
        default: "",
    },
    endpointDelete: {
        type: String,
        default: "",
    },
    endpointCreate: {
        type: String,
        default: "",
    },
    endpointEdit: {
        type: String,
        default: "",
    },
    permission: {
        type: String,
        default: "",
    },
    custom_edit_route: {
        type: String,
        default: "",
    },
});
</script>
