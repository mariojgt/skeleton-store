<template>
    <Layout>
      <div class="container mx-auto p-4">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Subscription Management</h1>
          <button @click="openCreateModal" class="btn btn-primary">
            <PlusCircle class="w-5 h-5 mr-2" /> Add Subscription
          </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <!-- Active Subscriptions -->
          <div class="card bg-base-100 shadow-lg">
            <div class="card-body p-4">
              <div class="flex justify-between items-center">
                <h3 class="text-base-content/70 font-semibold">Active Subscriptions</h3>
                <Users class="w-6 h-6 text-primary" />
              </div>
              <p class="text-3xl font-bold mt-2">{{ stats.active_count }}</p>
            </div>
          </div>

          <!-- Revenue -->
          <div class="card bg-base-100 shadow-lg">
            <div class="card-body p-4">
              <div class="flex justify-between items-center">
                <h3 class="text-base-content/70 font-semibold">Total Revenue</h3>
                <DollarSign class="w-6 h-6 text-success" />
              </div>
              <p class="text-3xl font-bold mt-2">{{ formatCurrency(stats.total_revenue) }}</p>
            </div>
          </div>

          <!-- Expiring Soon -->
          <div class="card bg-base-100 shadow-lg">
            <div class="card-body p-4">
              <div class="flex justify-between items-center">
                <h3 class="text-base-content/70 font-semibold">Expiring Soon</h3>
                <Clock class="w-6 h-6 text-warning" />
              </div>
              <p class="text-3xl font-bold mt-2">{{ stats.expiring_soon }}</p>
            </div>
          </div>

          <!-- Cancelled -->
          <div class="card bg-base-100 shadow-lg">
            <div class="card-body p-4">
              <div class="flex justify-between items-center">
                <h3 class="text-base-content/70 font-semibold">Cancelled</h3>
                <Ban class="w-6 h-6 text-error" />
              </div>
              <p class="text-3xl font-bold mt-2">{{ stats.by_status.cancelled || 0 }}</p>
            </div>
          </div>
        </div>

        <!-- Filter Bar -->
        <div class="card bg-base-100 shadow-lg mb-6">
          <div class="card-body p-4">
            <h3 class="card-title mb-3">Filter Subscriptions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select v-model="filters.status" class="select select-bordered w-full" @change="loadSubscriptions">
                  <option value="">All Statuses</option>
                  <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Plan</label>
                <select v-model="filters.plan_id" class="select select-bordered w-full" @change="loadSubscriptions">
                  <option value="">All Plans</option>
                  <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Search</label>
                <div class="relative">
                  <input
                    v-model="filters.search"
                    type="text"
                    class="input input-bordered w-full pr-10"
                    placeholder="Search users..."
                    @keyup.enter="loadSubscriptions"
                  />
                  <button
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-base-content/50"
                    @click="loadSubscriptions"
                  >
                    <Search class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Subscriptions Table -->
        <div class="card bg-base-100 shadow-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User</th>
                  <th>Plan</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Status</th>
                  <th>Auto Renew</th>
                  <th class="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="subscription in subscriptions" :key="subscription.id">
                  <td>{{ subscription.id }}</td>
                  <td>
                    <div class="flex items-center space-x-2">
                      <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-8">
                          <span>{{ getUserInitials(subscription.user) }}</span>
                        </div>
                      </div>
                      <div>
                        <div class="font-bold">{{ subscription.user.name }}</div>
                        <div class="text-sm opacity-50">{{ subscription.user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>{{ subscription.plan.name }}</td>
                  <td>{{ formatDate(subscription.start_date) }}</td>
                  <td>{{ formatDate(subscription.end_date) }}</td>
                  <td>
                    <StatusBadge :status="subscription.status" />
                  </td>
                  <td>
                    <div class="badge" :class="subscription.auto_renew ? 'badge-success' : 'badge-error'">
                      {{ subscription.auto_renew ? 'Yes' : 'No' }}
                    </div>
                  </td>
                  <td class="text-right">
                    <div class="flex justify-end space-x-1">
                      <a :href="openSubscriptionUrl(subscription.id)" class="btn btn-sm btn-info">
                        <Eye class="w-4 h-4" />
                      </a>
                      <button @click="openCapabilitiesModal(subscription)" class="btn btn-sm btn-primary">
                        <Key class="w-4 h-4" />
                      </button>
                      <button @click="openStatusModal(subscription)" class="btn btn-sm btn-warning">
                        <Edit class="w-4 h-4" />
                      </button>
                      <button
                        v-if="subscription.status !== 'cancelled'"
                        @click="confirmCancel(subscription)"
                        class="btn btn-sm btn-error"
                      >
                        <Ban class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="subscriptions.length === 0">
                  <td colspan="8" class="text-center py-8">No subscriptions found</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 flex justify-between items-center">
            <button @click="loadMore" class="btn btn-ghost" :disabled="!hasMoreSubscriptions">
              <RefreshCw class="w-5 h-5 mr-2" /> Load More
            </button>
            <span class="text-sm text-base-content/70">
              Showing {{ subscriptions.length }} of {{ totalSubscriptions }} subscriptions
            </span>
          </div>
        </div>
      </div>

      <!-- Create Subscription Modal -->
      <Modal
        v-if="createModalVisible"
        @close="createModalVisible = false"
        title="Create New Subscription"
      >
        <div class="p-4">
          <form @submit.prevent="createSubscription">
            <!-- User Selection -->
            <div class="mb-4">
              <label class="block text-sm font-medium mb-2">User</label>
              <div class="relative user-search-container">
                <input
                  v-model="userSearch"
                  type="text"
                  class="input input-bordered w-full"
                  placeholder="Search for user..."
                  @input="searchUsers"
                  @focus="showUserResults = true"
                />
                <div
                  v-if="showUserResults && userResults.length > 0"
                  class="absolute z-10 w-full mt-1 bg-base-200 rounded-md shadow-lg max-h-60 overflow-auto"
                >
                  <div
                    v-for="user in userResults"
                    :key="user.id"
                    class="p-2 hover:bg-base-300 cursor-pointer"
                    @click="selectUser(user)"
                  >
                    <div class="font-semibold">{{ user.name }}</div>
                    <div class="text-sm opacity-70">{{ user.email }}</div>
                  </div>
                </div>
              </div>
              <div v-if="newSubscription.user" class="mt-2 p-2 bg-base-200 rounded-md">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-semibold">{{ newSubscription.user.name }}</div>
                    <div class="text-sm opacity-70">{{ newSubscription.user.email }}</div>
                  </div>
                  <button type="button" class="text-error" @click="newSubscription.user = null">
                    <X class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Plan Selection -->
            <div class="mb-4">
              <label class="block text-sm font-medium mb-2">Plan</label>
              <select v-model="newSubscription.plan_id" class="select select-bordered w-full">
                <option value="" disabled>Select a plan</option>
                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                  {{ plan.name }} - {{ formatCurrency(plan.price) }}
                </option>
              </select>
            </div>

            <!-- Start Date -->
            <div class="mb-4">
              <label class="block text-sm font-medium mb-2">Start Date</label>
              <input
                v-model="newSubscription.start_date"
                type="date"
                class="input input-bordered w-full"
                :min="today"
              />
            </div>

            <!-- Auto Renew -->
            <div class="mb-6">
              <label class="flex items-center cursor-pointer">
                <input
                  v-model="newSubscription.auto_renew"
                  type="checkbox"
                  class="checkbox checkbox-primary mr-2"
                />
                <span class="label-text">Auto Renew</span>
              </label>
            </div>

            <div class="flex justify-end space-x-2">
              <button type="button" class="btn btn-ghost" @click="createModalVisible = false">
                Cancel
              </button>
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="!newSubscription.user || !newSubscription.plan_id"
              >
                Create Subscription
              </button>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Update Status Modal -->
      <Modal
        v-if="statusModalVisible"
        @close="statusModalVisible = false"
        title="Update Subscription Status"
      >
        <div class="p-4">
          <form @submit.prevent="updateStatus">
            <div class="mb-4">
              <label class="block text-sm font-medium mb-2">Status</label>
              <select v-model="selectedStatus" class="select select-bordered w-full">
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div class="flex justify-end space-x-2">
              <button type="button" class="btn btn-ghost" @click="statusModalVisible = false">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Capabilities Modal -->
      <Modal
        v-if="capabilitiesModalVisible"
        @close="capabilitiesModalVisible = false"
        title="Manage User Capabilities"
        size="2xl"
      >
        <div class="p-4">
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <h4 class="text-lg font-semibold">
                Capabilities for {{ selectedSubscription?.user?.name || 'User' }}
              </h4>
              <div>
                <span class="text-sm font-medium">Plan:</span>
                <span class="badge badge-primary ml-1">{{ selectedSubscription?.plan?.name }}</span>
              </div>
            </div>
            <p class="text-sm mb-4">
              Manage the capabilities available to this user based on their subscription plan.
            </p>
          </div>

          <div v-if="loadingCapabilities" class="flex justify-center py-8">
            <Loader class="w-8 h-8 animate-spin text-primary" />
          </div>

          <div v-else>
            <!-- Current Capabilities -->
            <div class="overflow-x-auto">
              <h4 class="font-semibold mb-2">Current Capabilities</h4>
              <table class="table table-zebra w-full">
                <thead>
                  <tr>
                    <th>Capability</th>
                    <th>Usage Limit</th>
                    <th>Used</th>
                    <th>Remaining</th>
                    <th>Reset</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="capability in userCapabilities" :key="capability.id">
                    <td>
                      <div class="font-medium">{{ capability.name }}</div>
                      <div class="text-xs opacity-70">{{ capability.description }}</div>
                    </td>
                    <td>
                      <span v-if="capability.is_unlimited">Unlimited</span>
                      <span v-else>{{ capability.usage_limit }}</span>
                    </td>
                    <td>{{ capability.usage_count }}</td>
                    <td>
                      <span v-if="capability.is_unlimited">∞</span>
                      <span v-else-if="capability.restriction_type === 'credits'">
                        {{ capability.remaining_credits }}
                      </span>
                      <span v-else>
                        {{ capability.usage_limit - capability.usage_count }}
                      </span>
                    </td>
                    <td>
                      <span v-if="capability.next_reset">
                        {{ formatDate(capability.next_reset) }}
                      </span>
                      <span v-else>—</span>
                    </td>
                    <td>
                      <div class="flex space-x-1">
                        <button
                          @click="adjustCapability(capability)"
                          class="btn btn-xs btn-primary"
                        >
                          <Settings class="w-3 h-3" />
                        </button>
                        <button
                          @click="removeCapability(capability)"
                          class="btn btn-xs btn-error"
                        >
                          <Trash2 class="w-3 h-3" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="userCapabilities.length === 0">
                    <td colspan="6" class="text-center py-4">
                      No capabilities assigned to this user
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Add Capability -->
            <div class="divider"></div>

            <div class="mt-4">
              <h4 class="font-semibold mb-2">Add Capability</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-2">Capability</label>
                  <select v-model="newCapability.capability_id" class="select select-bordered w-full">
                    <option value="" disabled>Select a capability</option>
                    <option
                      v-for="capability in availableCapabilities"
                      :key="capability.id"
                      :value="capability.id"
                    >
                      {{ capability.name }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-2">Restriction Type</label>
                  <select v-model="newCapability.restriction_type" class="select select-bordered w-full">
                    <option value="" disabled>Select type</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="lifetime">Lifetime</option>
                    <option value="credits">Credits</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium mb-2">
                    <span v-if="newCapability.restriction_type === 'credits'">Initial Credits</span>
                    <span v-else>Usage Limit</span>
                  </label>
                  <input
                    v-model.number="newCapability.usage_limit"
                    type="number"
                    min="1"
                    class="input input-bordered w-full"
                    :disabled="newCapability.is_unlimited"
                  />
                </div>

                <div class="flex items-end">
                  <label class="flex items-center cursor-pointer">
                    <input
                      v-model="newCapability.is_unlimited"
                      type="checkbox"
                      class="checkbox checkbox-primary mr-2"
                    />
                    <span class="label-text">Unlimited Usage</span>
                  </label>
                </div>
              </div>

              <div class="flex justify-end mt-4">
                <button
                  @click="addCapability"
                  class="btn btn-primary"
                  :disabled="!newCapability.capability_id || (!newCapability.is_unlimited && !newCapability.usage_limit)"
                >
                  <PlusCircle class="w-4 h-4 mr-2" /> Add Capability
                </button>
              </div>
            </div>
          </div>

          <div class="flex justify-end mt-6">
            <button type="button" class="btn" @click="capabilitiesModalVisible = false">
              Close
            </button>
          </div>
        </div>
      </Modal>
    </Layout>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import axios from 'axios';
  import Layout from '@backend_layout/App.vue';
  import StatusBadge from '@backend_components/Subscrition/StatusBadge.vue';
  import Modal from '@backend_components/Subscrition/Modal.vue';

  // Import Lucide icons
  import {
    Users, DollarSign, Clock, Ban, Search,
    Eye, Edit, RefreshCw, PlusCircle, X,
    Key, Settings, Trash2, Loader
  } from 'lucide-vue-next';

  const props = defineProps({
    stats: {
      type: Object,
      required: true
    },
    subscriptions: {
      type: Array,
      required: true
    },
    statuses: {
      type: Object,
      required: true
    },
    plans: {
      type: Array,
      required: true
    }
  });

  // States
  const filters = ref({
    status: '',
    plan_id: '',
    search: '',
    page: 1
  });
  const subscriptions = ref(props.subscriptions || []);
  const totalSubscriptions = ref(0);
  const hasMoreSubscriptions = ref(true);

  // Modal states
  const createModalVisible = ref(false);
  const statusModalVisible = ref(false);
  const capabilitiesModalVisible = ref(false);
  const selectedSubscription = ref(null);
  const selectedStatus = ref('');

  // User search
  const userSearch = ref('');
  const userResults = ref([]);
  const showUserResults = ref(false);
  const newSubscription = ref({
    user: null,
    user_id: null,
    plan_id: '',
    start_date: new Date().toISOString().split('T')[0],
    auto_renew: false
  });

  // Capabilities management
  const loadingCapabilities = ref(false);
  const userCapabilities = ref([]);
  const availableCapabilities = ref([]);
  const newCapability = ref({
    capability_id: '',
    restriction_type: '',
    usage_limit: 10,
    is_unlimited: false
  });

  // Computed
  const today = computed(() => {
    return new Date().toISOString().split('T')[0];
  });

  // Initialize
  onMounted(() => {
    document.addEventListener('click', handleOutsideClick);
    loadSubscriptions();
    loadAvailableCapabilities();
  });

  // Methods
  function handleOutsideClick(event) {
    if (!event.target.closest('.user-search-container')) {
      showUserResults.value = false;
    }
  }

  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString();
  }

  function formatCurrency(amount) {
    if (amount === undefined || amount === null) return '$0.00';
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  }

  function getUserInitials(user) {
    if (!user || !user.name) return '?';
    return user.name
      .split(' ')
      .map(name => name[0])
      .slice(0, 2)
      .join('')
      .toUpperCase();
  }

  function openSubscriptionUrl(subscriptionId) {
    return route('admin.store.subscriptions.show', subscriptionId);
  }

  async function loadSubscriptions() {
    try {
      filters.value.page = 1;
      const response = await axios.get(route('admin.store.subscriptions.list'), {
        params: filters.value
      });
      subscriptions.value = response.data.data;
      totalSubscriptions.value = response.data.total;
      hasMoreSubscriptions.value = response.data.current_page < response.data.last_page;
    } catch (error) {
      console.error('Error loading subscriptions:', error);
    }
  }

  async function loadMore() {
    if (!hasMoreSubscriptions.value) return;

    try {
      filters.value.page += 1;
      const response = await axios.get(route('admin.store.subscriptions.list'), {
        params: filters.value
      });
      subscriptions.value = [...subscriptions.value, ...response.data.data];
      hasMoreSubscriptions.value = response.data.current_page < response.data.last_page;
    } catch (error) {
      console.error('Error loading more subscriptions:', error);
    }
  }

  function openCreateModal() {
    createModalVisible.value = true;
    newSubscription.value = {
      user: null,
      user_id: null,
      plan_id: '',
      start_date: new Date().toISOString().split('T')[0],
      auto_renew: false
    };
  }

  function openStatusModal(subscription) {
    selectedSubscription.value = subscription;
    selectedStatus.value = subscription.status;
    statusModalVisible.value = true;
  }

  function openCapabilitiesModal(subscription) {
    selectedSubscription.value = subscription;
    capabilitiesModalVisible.value = true;
    loadUserCapabilities(subscription.id);
  }

  async function searchUsers() {
    if (userSearch.value.length < 2) {
      userResults.value = [];
      return;
    }

    try {
      const response = await axios.get(route('admin.store.subscriptions.search-users'), {
        params: { search: userSearch.value }
      });
      userResults.value = response.data;
      showUserResults.value = true;
    } catch (error) {
      console.error('Error searching users:', error);
    }
  }

  function selectUser(user) {
    newSubscription.value.user = user;
    newSubscription.value.user_id = user.id;
    userSearch.value = user.name;
    showUserResults.value = false;
  }

  async function createSubscription() {
    if (!newSubscription.value.user_id || !newSubscription.value.plan_id) {
      return;
    }

    try {
      const response = await axios.post(route('admin.store.subscriptions.store'), newSubscription.value);
      // Add new subscription to the top of the list
      subscriptions.value.unshift(response.data.subscription);
      createModalVisible.value = false;

      // Show success message
      alert('Subscription created successfully');

      // Reset form
      newSubscription.value = {
        user: null,
        user_id: null,
        plan_id: '',
        start_date: new Date().toISOString().split('T')[0],
        auto_renew: false
      };
    } catch (error) {
      console.error('Error creating subscription:', error);
      alert('Error creating subscription. Please check form inputs and try again.');
    }
  }

  async function updateStatus() {
    if (!selectedSubscription.value) return;

    try {
      const response = await axios.put(
        route('admin.store.subscriptions.update-status', selectedSubscription.value.id),
        { status: selectedStatus.value }
      );

      // Update subscription in the list
      const index = subscriptions.value.findIndex(s => s.id === selectedSubscription.value.id);
      if (index !== -1) {
        subscriptions.value[index] = response.data.subscription;
      }

      statusModalVisible.value = false;
      // Show success message
      alert('Subscription status updated successfully');
    } catch (error) {
      console.error('Error updating status:', error);
      alert('Error updating subscription status. Please try again.');
    }
  }

  function confirmCancel(subscription) {
    if (confirm(`Are you sure you want to cancel the subscription for ${subscription.user.name}?`)) {
      cancelSubscription(subscription);
    }
  }

  async function cancelSubscription(subscription) {
    try {
      const response = await axios.put(
        route('admin.store.subscriptions.cancel', subscription.id)
      );

      // Update subscription in the list
      const index = subscriptions.value.findIndex(s => s.id === subscription.id);
      if (index !== -1) {
        subscriptions.value[index] = response.data.subscription;
      }

      // Show success message
      alert('Subscription cancelled successfully');
    } catch (error) {
      console.error('Error cancelling subscription:', error);
      alert('Error cancelling subscription. Please try again.');
    }
  }

  // Capabilities Management
  async function loadAvailableCapabilities() {
    try {
      const response = await axios.get(route('admin.store.capabilities.list'));
      availableCapabilities.value = response.data;
    } catch (error) {
      console.error('Error loading capabilities:', error);
    }
  }

  async function loadUserCapabilities(subscriptionId) {
    loadingCapabilities.value = true;
    try {
      const response = await axios.get(route('admin.store.capabilities.user', subscriptionId));
      userCapabilities.value = response.data;
    } catch (error) {
      console.error('Error loading user capabilities:', error);
      userCapabilities.value = [];
    } finally {
      loadingCapabilities.value = false;
    }
  }

  async function addCapability() {
    if (!selectedSubscription.value || !newCapability.value.capability_id) return;

    try {
      const payload = {
        subscription_id: selectedSubscription.value.id,
        capability_id: newCapability.value.capability_id,
        restriction_type: newCapability.value.restriction_type,
        usage_limit: newCapability.value.is_unlimited ? 0 : newCapability.value.usage_limit,
        is_unlimited: newCapability.value.is_unlimited
      };

      await axios.post(route('admin.store.capabilities.add'), payload);

      // Reload capabilities list
      await loadUserCapabilities(selectedSubscription.value.id);

      // Reset form
      newCapability.value = {
        capability_id: '',
        restriction_type: '',
        usage_limit: 10,
        is_unlimited: false
      };

      // Show success message
      alert('Capability added successfully');
    } catch (error) {
      console.error('Error adding capability:', error);
      alert('Error adding capability. Please try again.');
    }
  }

  async function removeCapability(capability) {
    if (!selectedSubscription.value) return;

    if (!confirm(`Are you sure you want to remove the "${capability.name}" capability?`)) {
      return;
    }

    try {
      await axios.delete(
        route('admin.store.capabilities.remove', capability.id),
        { data: { subscription_id: selectedSubscription.value.id } }
      );

      // Reload capabilities list
      await loadUserCapabilities(selectedSubscription.value.id);

      // Show success message
      alert('Capability removed successfully');
    } catch (error) {
      console.error('Error removing capability:', error);
      alert('Error removing capability. Please try again.');
    }
  }

  function adjustCapability(capability) {
    // Opening an adjust capability modal would go here
    // For simplicity, let's just use a prompt for now
    const newUsage = prompt(`Adjust ${capability.restriction_type === 'credits' ? 'remaining credits' : 'usage count'} for ${capability.name}:`,
      capability.restriction_type === 'credits' ? capability.remaining_credits : capability.usage_count);

    if (newUsage === null) return; // User cancelled

    const usageValue = parseInt(newUsage);
    if (isNaN(usageValue) || usageValue < 0) {
      alert('Please enter a valid non-negative number');
      return;
    }

    updateCapabilityUsage(capability, usageValue);
  }

  async function updateCapabilityUsage(capability, usageValue) {
    try {
      const payload = {
        subscription_id: selectedSubscription.value.id,
        usage_count: usageValue,
        is_unlimited: capability.is_unlimited,
      };

      await axios.put(
        route('admin.store.capabilities.update', capability.id),
        payload
      );

      // Reload capabilities list
      await loadUserCapabilities(selectedSubscription.value.id);

      // Show success message
      alert('Capability updated successfully');
    } catch (error) {
      console.error('Error updating capability:', error);
      alert('Error updating capability. Please try again.');
    }
  }
  </script>

  <style scoped>
  .avatar.placeholder div {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  </style>
