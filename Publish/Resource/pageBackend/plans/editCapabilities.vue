<template>
  <div class="p-6 space-y-8 max-w-4xl mx-auto">
    <div class="space-y-2">
      <h1 class="text-3xl font-bold text-gray-900">Edit Capabilities</h1>
      <p class="text-gray-600">Configure capabilities for the plan: <strong>{{ plan.name }}</strong></p>
    </div>

    <form @submit.prevent="update" class="space-y-6">
      <div v-for="cap in form.capabilities" :key="cap.id" class="bg-white shadow-sm border rounded-xl p-6 space-y-4">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">{{ cap.name }}</h2>
          <p class="text-sm text-gray-500">{{ cap.description }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Unlimited toggle -->
          <div class="flex items-center space-x-4">
            <label class="text-gray-700 font-medium">Unlimited:</label>
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="cap.is_unlimited" class="sr-only peer">
              <div
                class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition duration-300 relative">
                <div
                  class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow-md transform peer-checked:translate-x-5 transition duration-300">
                </div>
              </div>
            </label>
          </div>

          <!-- Usage limit -->
          <div v-if="!cap.is_unlimited" class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Usage Limit</label>
            <input
              type="number"
              v-model.number="cap.usage_limit"
              placeholder="e.g. 1000"
              class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <!-- Initial Credits -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Initial Credits</label>
            <input
              type="number"
              v-model.number="cap.initial_credits"
              placeholder="e.g. 100"
              class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <!-- Restriction Type -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Restriction Type</label>
            <input
              type="text"
              v-model="cap.restriction_type"
              placeholder="e.g. monthly, daily, none"
              class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>
        </div>
      </div>

      <div class="pt-4">
        <button type="submit"
          class="w-full md:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
          Save Capabilities
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  plan: Object,
  capabilities: Array
});

const form = useForm({
  capabilities: props.capabilities.map(cap => {
    const existing = props.plan.capabilities.find(c => c.id === cap.id);
    return {
      ...cap,
      usage_limit: existing?.pivot?.usage_limit ?? 0,
      is_unlimited: existing?.pivot?.is_unlimited ?? false,
      restriction_type: existing?.pivot?.restriction_type ?? 'default',
      initial_credits: existing?.pivot?.initial_credits ?? 0,
    };
  })
});

function update() {
  form.put(route('admin.store.plans.update.capabilities', props.plan.id));
}
</script>
