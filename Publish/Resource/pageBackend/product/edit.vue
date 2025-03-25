<template>
    <Layout>
        <div class="mx-auto bg-base-100 p-6 rounded">
            <!-- Header with breadcrumbs and actions -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
                        {{ form.name || 'New Product' }}
                    </h1>
                    <div class="text-sm opacity-70 breadcrumbs">
                        <ul>
                            <li><a href="#" class="hover:text-primary">Products</a></li>
                            <li>{{ form.name || 'Edit Product' }}</li>
                        </ul>
                    </div>
                </div>
                <div class="flex gap-2 mt-3 sm:mt-0">
                    <button type="button" class="btn btn-outline btn-sm" @click="$router.back()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back
                    </button>
                    <button type="button" class="btn btn-primary btn-sm gap-1" @click="submitForm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                        </svg>
                        Save Product
                    </button>
                </div>
            </div>

            <n-card class="shadow-xl rounded-xl bg-base-100 border border-base-300">
                <!-- Tab Navigation -->
                <TabGroup>
                    <div class="border-b border-base-300 mb-6">
                        <TabList class="flex flex-wrap -mb-px">
                            <Tab v-slot="{ selected }">
                                <button class="inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 transition-all duration-200"
                                    :class="selected ? 'border-primary text-primary' : 'border-transparent hover:border-base-300 hover:text-base-content'">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                    </svg>
                                    Basic Details
                                </button>
                            </Tab>
                            <Tab v-slot="{ selected }">
                                <button class="inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 transition-all duration-200"
                                    :class="selected ? 'border-primary text-primary' : 'border-transparent hover:border-base-300 hover:text-base-content'">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    Media & Description
                                </button>
                            </Tab>
                            <Tab v-slot="{ selected }">
                                <button class="inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 transition-all duration-200"
                                    :class="selected ? 'border-primary text-primary' : 'border-transparent hover:border-base-300 hover:text-base-content'">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Settings
                                </button>
                            </Tab>
                            <Tab v-slot="{ selected }">
                                <button class="inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 transition-all duration-200"
                                    :class="selected ? 'border-primary text-primary' : 'border-transparent hover:border-base-300 hover:text-base-content'">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Resources
                                </button>
                            </Tab>
                        </TabList>
                    </div>

                    <form @submit.prevent="submitForm">
                        <TabPanels>
                            <!-- Basic Details Panel -->
                            <TabPanel>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div class="space-y-6">
                                        <div class="form-control">
                                            <input-field
                                                v-model="form.name"
                                                label="Product Name"
                                                type="text"
                                                placeholder="Enter product name"
                                            />
                                            <p class="text-xs opacity-70 mt-1">This name will be displayed on product listings and detail pages.</p>
                                        </div>

                                        <div class="form-control">
                                            <input-field
                                                v-model="form.slug"
                                                label="URL Slug"
                                                type="text"
                                                placeholder="product-url-slug"
                                            />
                                            <p class="text-xs opacity-70 mt-1">Used in the product's URL. Should be unique and contain only letters, numbers, and hyphens.</p>
                                        </div>

                                        <div class="form-control">
                                            <input-field
                                                v-model="form.price"
                                                label="Price"
                                                type="text"
                                                placeholder="0.00"
                                            />
                                            <p class="text-xs opacity-70 mt-1">Enter the product price in your default currency.</p>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="form-control">
                                            <TextMultipleSelector
                                                v-model="form.category_id"
                                                :label="'Category'"
                                                placeholder="Search for a category"
                                                :model="props.dynamicCategorySearch.model"
                                                :columns="props.dynamicCategorySearch.columns"
                                                :single-mode="props.dynamicCategorySearch.singleSearch"
                                                :load-data="selected_category.data"
                                                :endpoint="props.dynamicCategorySearch.endpoint"
                                            />
                                            <p class="text-xs opacity-70 mt-1">Select the appropriate category for this product.</p>
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Media and Description Panel -->
                            <TabPanel>
                                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                                    <div>
                                        <div class="form-control w-full mb-6">
                                            <div class="label">
                                                <span class="label-text font-medium">Product Description</span>
                                            </div>
                                            <Editor
                                                v-model="form.description"
                                                :api-key="props.apiKey"
                                            />
                                            <p class="text-xs opacity-70 mt-1">Include key features, specifications, and any important information about the product.</p>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="label">
                                            <span class="label-text font-medium">Product Images</span>
                                        </div>
                                        <div class="bg-base-200 p-4 rounded-lg border border-base-300">
                                            <Image
                                                v-model="form.product_image"
                                                label=""
                                                placeholder="Search images"
                                                :load-data="product.data.media"
                                                :endpoint="props.image_search_endpoint"
                                            />
                                            <p class="text-xs opacity-70 mt-3">Add high-quality images of your product. The first image will be used as the thumbnail.</p>
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Settings Panel -->
                            <TabPanel>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div class="space-y-6">
                                        <div class="form-control">
                                            <SelectInput
                                                v-model="form.price_type"
                                                :options="props.price_type_enum"
                                                label="Price Type"
                                            />
                                            <p class="text-xs opacity-70 mt-1">Select how pricing should be handled for this product.</p>
                                        </div>

                                        <div class="form-control">
                                            <SelectInput
                                                v-model="form.type"
                                                :options="props.type_enum"
                                                label="Product Type"
                                            />
                                            <p class="text-xs opacity-70 mt-1">Categorize this product by its type.</p>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="bg-base-200 p-6 rounded-lg border border-base-300">
                                            <h3 class="text-lg font-medium mb-4">Subscription Settings</h3>
                                            <div class="form-control">
                                                <Toggle
                                                    v-model="form.free_with_subscription"
                                                    label="Free With Subscription"
                                                />
                                                <p class="text-xs opacity-70 mt-1">When enabled, this product will be available to subscribers at no additional cost.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>

                            <!-- Resources Panel -->
                            <TabPanel>
                                <div class="space-y-6">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-lg font-medium">Product Resources</h3>
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm gap-1"
                                            @click="newResourceModal = true"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Add Resource
                                        </button>
                                    </div>
                                    <p class="text-sm opacity-70">Resources include downloadable files, links, and other assets related to this product.</p>

                                    <!-- Empty state -->
                                    <div v-if="!productResources.length" class="flex flex-col items-center justify-center py-12 bg-base-200 rounded-lg border border-dashed border-base-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-16 opacity-30 mb-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                        <p class="text-lg font-medium">No resources added yet</p>
                                        <p class="text-sm opacity-70 mb-4">Add downloadable files or external links for your customers</p>
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            @click="newResourceModal = true"
                                        >
                                            Add Your First Resource
                                        </button>
                                    </div>

                                    <!-- Resources List -->
                                    <div v-else class="space-y-4">
                                        <div
                                            v-for="(resource, index) in productResources"
                                            :key="index"
                                            class="bg-base-100 rounded-lg border border-base-300 transition-all duration-200 hover:shadow-md"
                                            :class="{'border-primary': resource.editing}"
                                        >
                                            <!-- View Mode -->
                                            <div v-if="!resource.editing" class="p-4">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-start space-x-3">
                                                        <!-- Icon based on resource type -->
                                                        <div class="rounded-lg bg-base-200 p-2">
                                                            <svg v-if="resource.resource_type === 'link'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-secondary">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                                            </svg>
                                                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-primary">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                            </svg>
                                                        </div>

                                                        <div>
                                                            <h4 class="font-medium">{{ resource.title }}</h4>
                                                            <p class="text-sm opacity-70 mt-1">{{ resource.description }}</p>

                                                            <div class="mt-2">
                                                                <span v-if="resource.resource_type === 'link'" class="text-xs inline-flex items-center gap-1 text-secondary">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                                                    </svg>
                                                                    <a :href="resource.resource_url" target="_blank" class="hover:underline">{{ resource.resource_url }}</a>
                                                                </span>
                                                                <span v-else class="text-xs inline-flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                                    </svg>
                                                                    {{ resource.file_path }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-2">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-ghost"
                                                            @click="resource.editing = !resource.editing"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                            </svg>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-ghost text-error"
                                                            @click="deleteResource(resource)"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Edit Mode -->
                                            <div v-else class="p-4">
                                                <div class="space-y-4">
                                                    <h4 class="font-medium border-b pb-2">Edit Resource</h4>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <input-field
                                                            v-model="resource.title"
                                                            type="text"
                                                            label="Title"
                                                            placeholder="Resource title"
                                                        />

                                                        <div class="form-control">
                                                            <label class="label">
                                                                <span class="label-text">Resource Type</span>
                                                            </label>
                                                            <select v-model="resource.resource_type" class="select select-bordered w-full">
                                                                <option value="link">External Link</option>
                                                                <option value="file">Downloadable File</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-control">
                                                        <input-field
                                                            v-model="resource.description"
                                                            type="text"
                                                            label="Description"
                                                            placeholder="Briefly describe this resource"
                                                        />
                                                    </div>

                                                    <div v-if="resource.resource_type === 'link'" class="form-control">
                                                        <input-field
                                                            v-model="resource.resource_url"
                                                            type="text"
                                                            label="URL"
                                                            placeholder="https://example.com"
                                                        />
                                                    </div>

                                                    <div v-else class="form-control">
                                                        <input-field
                                                            v-model="resource.file_path"
                                                            type="text"
                                                            label="File Path"
                                                            placeholder="path/to/file.pdf"
                                                        />
                                                    </div>

                                                    <div class="flex justify-end gap-2 pt-2">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-ghost"
                                                            @click="resource.editing = false"
                                                        >
                                                            Cancel
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-primary"
                                                            @click="updateResource(resource)"
                                                        >
                                                            Save Changes
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </form>
                </TabGroup>

                <!-- Floating Save Button -->
                <div class="sticky bottom-6 flex justify-end mt-8">
                    <button
                        type="button"
                        class="btn btn-primary shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                        @click="submitForm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" />
                        </svg>
                        Save Product
                    </button>
                </div>
            </n-card>

            <!-- Add Resource Modal -->
            <div v-if="newResourceModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                <div class="bg-base-100 rounded-lg shadow-xl max-w-lg w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-primary">Add New Resource</h3>
                            <button
                                type="button"
                                class="btn btn-sm btn-circle btn-ghost"
                                @click="newResourceModal = false"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <input-field
                                v-model="newResource.title"
                                type="text"
                                label="Title"
                                placeholder="Enter resource title"
                            />

                            <input-field
                                v-model="newResource.description"
                                type="text"
                                label="Description"
                                placeholder="Briefly describe this resource"
                            />

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Resource Type</span>
                                </label>
                                <select v-model="newResource.resource_type" class="select select-bordered w-full">
                                    <option value="link">External Link</option>
                                    <option value="file">Downloadable File</option>
                                </select>
                            </div>

                            <div v-if="newResource.resource_type === 'link'" class="form-control">
                                <input-field
                                    v-model="newResource.resource_url"
                                    type="text"
                                    label="Resource URL"
                                    placeholder="https://example.com"
                                />
                            </div>

                            <div v-if="newResource.resource_type === 'file'" class="form-control">
                                <input-field
                                    v-model="newResource.file_path"
                                    type="text"
                                    label="File Path"
                                    placeholder="path/to/file.pdf"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                class="btn btn-ghost"
                                @click="newResourceModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="createResource"
                            >
                                Add Resource
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { api } from "../../../../../Boot/axios.js";
import { useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
import Layout from "@backend_layout/App.vue";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";
import Editor from "@backend_components/Editor/Editor.vue";
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
    apiKey: {
        type: String,
        default: null,
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
