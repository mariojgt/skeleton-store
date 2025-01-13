<template>
    <Layout>
        <n-card
            title="Product"
            :class="'col-span-1 row-span-3 shadow-lg xl:col-span-2 bg-base-300'"
        >
            <TabGroup>
                <TabList class="flex space-x-1 rounded-xl p-1 tabs">
                    <Tab v-slot="{ selected }">
                        <a
                            class="tab tab-lg tab-bordered rounded-md bg-neutral"
                            :class="selected ? 'bg-primary text-black' : ''"
                            >Deatils</a
                        >
                    </Tab>
                    <Tab v-slot="{ selected }">
                        <a
                            class="tab tab-lg tab-bordered rounded-md bg-neutral"
                            :class="selected ? 'bg-primary text-black' : ''"
                            >Media and Description</a
                        >
                    </Tab>
                    <Tab v-slot="{ selected }">
                        <a
                            class="tab tab-lg tab-bordered rounded-md bg-neutral"
                            :class="selected ? 'bg-primary text-black' : ''"
                            >Settings</a
                        >
                    </Tab>
                    <Tab v-slot="{ selected }">
                        <a
                            class="tab tab-lg tab-bordered rounded-md bg-neutral"
                            :class="selected ? 'bg-primary text-black' : ''"
                            >Add Resources</a
                        >
                    </Tab>
                </TabList>
                <form @submit.prevent="submitForm">
                    <TabPanels class="mt-2">
                        <TabPanel>
                            <input-field
                                v-model="form.name"
                                label="Name"
                                type="text"
                                placeholder="Name"
                            />
                            <input-field
                                v-model="form.slug"
                                label="slug"
                                type="text"
                                placeholder="slug"
                            />
                            <input-field
                                v-model="form.price"
                                label="Price"
                                type="text"
                                placeholder="slug"
                            />
                            <TextMultipleSelector
                                v-model="form.category_id"
                                :label="'Category'"
                                placeholder="search"
                                :model="props.dynamicCategorySearch.model"
                                :columns="props.dynamicCategorySearch.columns"
                                :single-mode="
                                    props.dynamicCategorySearch.singleSearch
                                "
                                :load-data="selected_category.data"
                                :endpoint="props.dynamicCategorySearch.endpoint"
                            />
                        </TabPanel>
                        <TabPanel>
                            <label class="form-control">
                                <div class="label">
                                    <span class="label-text">Description</span>
                                </div>
                                <textarea
                                    class="textarea textarea-bordered w-full"
                                    placeholder="Product Description"
                                    v-model="form.description"
                                ></textarea>
                            </label>
                            <Image
                                v-model="form.product_image"
                                label="image"
                                placeholder="search"
                                :load-data="product.data.media"
                                :endpoint="props.image_search_endpoint"
                            />
                        </TabPanel>
                        <TabPanel>
                            <SelectInput
                                v-model="form.price_type"
                                :options="props.price_type_enum"
                                label="Price type"
                            />
                            <SelectInput
                                v-model="form.type"
                                :options="props.type_enum"
                                label="Type"
                            />
                            <Toggle
                                v-model="form.free_with_subscription"
                                label="Is Free WIth Subscription"
                            />
                        </TabPanel>
                        <TabPanel>
                            <div class="flex flex-col space-y-4">
                                <button type="button" class="btn btn-primary" @click="newResourceModal = true">
                                    Add New Resource
                                </button>

                                <!-- New Resource Modal -->
                                <div v-if="newResourceModal" class="p-4 border rounded-lg bg-base-300">
                                    <h2 class="text-xl font-bold mb-2">Add New Resource</h2>
                                    <input-field v-model="newResource.title" type="text" label="Title" />
                                    <input-field v-model="newResource.description" type="text" label="Description" />
                                    <div class="mb-2">
                                        <label>Resource Type</label>
                                        <select v-model="newResource.resource_type" class="select select-bordered w-full">
                                            <option value="link">Link</option>
                                            <option value="file">File</option>
                                        </select>
                                    </div>
                                    <input-field
                                        v-if="newResource.resource_type === 'link'"
                                        v-model="newResource.resource_url"
                                        type="text"
                                        label="Resource URL"
                                    />
                                    <input-field
                                        v-if="newResource.resource_type === 'file'"
                                        v-model="newResource.file_path"
                                        type="text"
                                        label="File Path"
                                    />

                                    <div class="flex space-x-2 mt-4">
                                        <button type="button" class="btn btn-success" @click="createResource">Create Resource</button>
                                        <button type="button" class="btn btn-error" @click="newResourceModal = false">Cancel</button>
                                    </div>
                                </div>

                                <!-- Resources List -->
                                <div v-for="(resource, index) in productResources" :key="index" class="p-4 border bg-base-100 rounded-lg flex flex-col space-y-2">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-lg font-semibold">{{ resource.title }}</h3>
                                        <div class="space-x-2">
                                            <button class="btn btn-secondary" @click="resource.editing = !resource.editing">Edit</button>
                                            <button class="btn btn-error" @click="deleteResource(resource)">Delete</button>
                                        </div>
                                    </div>
                                    <p class="text-sm">{{ resource.description }}</p>
                                    <div v-if="resource.resource_type === 'link'">
                                        <a :href="resource.resource_url" target="_blank" class="text-blue-500 underline">Open Link</a>
                                    </div>
                                    <div v-if="resource.resource_type === 'file'">
                                        <span>File: {{ resource.file_path }}</span>
                                    </div>

                                    <!-- Edit Form -->
                                    <div v-if="resource.editing" class="p-4 bg-base-200 rounded-lg">
                                        <input-field v-model="resource.title" type="text" label="Title" />
                                        <input-field v-model="resource.description" type="text" label="Description" />
                                        <div class="mb-2">
                                            <label>Resource Type</label>
                                            <select v-model="resource.resource_type" class="select select-bordered w-full">
                                                <option value="link">Link</option>
                                                <option value="file">File</option>
                                            </select>
                                        </div>
                                        <input-field
                                            v-if="resource.resource_type === 'link'"
                                            v-model="resource.resource_url"
                                            type="text"
                                            label="Resource URL"
                                        />
                                        <input-field
                                            v-if="resource.resource_type === 'file'"
                                            v-model="resource.file_path"
                                            type="text"
                                            label="File Path"
                                        />

                                        <div class="flex space-x-2 mt-4">
                                            <button class="btn btn-success" @click="updateResource(resource)">Save Changes</button>
                                            <button class="btn btn-error" @click="resource.editing = false">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabPanel>
                    </TabPanels>
                </form>
            </TabGroup>
            <div class="form-control pt-10">
                <submit name="Update" @click="submitForm" />
            </div>
        </n-card>
    </Layout>
</template>

<script setup>
import { api } from "../../../../../Boot/axios.js";
import { useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
import Layout from "@backend_layout/App.vue";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";
import {
    InputField,
    LinkButton,
    InputPassword,
    Submit,
    SelectInput,
    TextMultipleSelector,
    Image,
    Toggle,
} from "@mariojgt/masterui/packages/index";

const props = defineProps({
    product: {
        type: Object,
        default: () => ({}),
    },
    image_search_endpoint: {
        type: String,
        default: "",
    },
    dynamicCategorySearch: {
        type: Object,
        default: () => ({}),
    },
    selected_category: {
        type: Object,
        default: () => ({}),
    },
    type_enum: {
        type: Object,
        default: () => ({}),
    },
    price_type_enum: {
        type: Object,
        default: () => ({}),
    },
});

onMounted(() => {});

const form = useForm({
    name: props.product.data.name,
    slug: props.product.data.slug,
    description: props.product.data.description,
    price: props.product.data.price,
    product_image: props.product.data.media,
    media: props.product.data.media,
    category_id: props.product.data.category_id,
    type: props.product.data.type,
    price_type: props.product.data.price_type,
    free_with_subscription: props.product.data.free_with_subscription
});

// SubmitTheForm
const submitForm = () => {
    form.patch(
        route("admin.store.product.update", { product: props.product.data.id })
    );
};

// Add these to your existing script setup
let productResources = $ref([]);
let newResourceModal = $ref(false);
let newResource = $ref({
    title: '',
    description: '',
    resource_type: 'link',
    resource_url: '',
    file_path: ''
});

// Load resources
const loadResources = async () => {
    try {
        const response = await api.get(route('admin.store.product.resources.index', props.product.data.id));
        productResources = response.data.data;
    } catch (error) {
        console.error('Error loading resources:', error);
    }
};

// Create resource
const createResource = async () => {
    try {
        await api.post(
            route('admin.store.product.resources.store', props.product.data.id),
            newResource
        );
        newResourceModal = false;
        loadResources();
        // Reset form
        newResource = {
            title: '',
            description: '',
            resource_type: 'link',
            resource_url: '',
            file_path: ''
        };
    } catch (error) {
        console.error('Error creating resource:', error);
    }
};

// Update resource
const updateResource = async (resource) => {
    try {
        await api.put(
            route('admin.store.product.resources.update', [props.product.data.id, resource.id]),
            resource
        );
        loadResources();
    } catch (error) {
        console.error('Error updating resource:', error);
    }
};

// Delete resource
const deleteResource = async (resource) => {
    try {
        await api.delete(
            route('admin.store.product.resources.destroy', [props.product.data.id, resource.id])
        );
        loadResources();
    } catch (error) {
        console.error('Error deleting resource:', error);
    }
};

// Load resources on mount
onMounted(() => {
    loadResources();
});
</script>
