<template>
    <Layout>
      <div class="container mx-auto p-4">
        <!-- Page Header with Back Button -->
        <div class="flex justify-between items-center mb-6">
          <!-- <div class="flex items-center space-x-2">
            <a :href="route('admin.store.subscriptions.index')" class="btn btn-ghost">
              <ArrowLeft class="w-5 h-5" />
            </a>
            <h1 class="text-2xl font-bold">Subscription Details</h1>
          </div> -->
          <div class="flex space-x-2">
            <button @click="openExtendModal" class="btn btn-success">
              <CalendarPlus class="w-5 h-5 mr-1" /> Extend
            </button>
            <button @click="openChangePlanModal" class="btn btn-warning">
              <RefreshCw class="w-5 h-5 mr-1" /> Change Plan
            </button>
            <button
              v-if="subscription.status !== 'cancelled'"
              @click="confirmCancel"
              class="btn btn-error"
            >
              <Ban class="w-5 h-5 mr-1" /> Cancel
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Subscription Info -->
          <div class="col-span-2">
            <!-- Subscription Overview Card -->
            <div class="card bg-base-100 shadow-lg overflow-hidden mb-6">
              <div class="card-body p-6">
                <h2 class="card-title mb-4">Subscription Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Subscription ID -->
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Subscription ID</h3>
                    <p class="font-mono text-sm">{{ subscription.subscription_id || 'N/A' }}</p>
                  </div>

                  <!-- Status -->
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Status</h3>
                    <div class="flex items-center mt-1">
                      <StatusBadge :status="subscription.status" />
                      <button @click="openStatusModal" class="btn btn-xs btn-ghost ml-2">
                        <Edit class="w-3 h-3" />
                      </button>
                    </div>
                  </div>

                  <!-- Auto Renew -->
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Auto Renew</h3>
                    <div class="flex items-center mt-1">
                      <div class="badge" :class="subscription.auto_renew ? 'badge-success' : 'badge-error'">
                        {{ subscription.auto_renew ? 'Yes' : 'No' }}
                      </div>
                      <button @click="toggleAutoRenew" class="btn btn-xs btn-ghost ml-2">
                        <RefreshCcw class="w-3 h-3" />
                      </button>
                    </div>
                  </div>

                  <!-- Created At -->
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Created</h3>
                    <p>{{ formatDate(subscription.created_at) }}</p>
                  </div>

                  <!-- Start Date -->
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Start Date</h3>
                    <div class="flex items-center mt-1">
                      <p>{{ formatDate(subscription.start_date) }}</p>
                      <button @click="openDatesModal" class="btn btn-xs btn-ghost ml-2">
                        <Edit class="w-3 h-3" />
                      </button>
                    </div>
                  </div>

                  <!-- End Date -->
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">End Date</h3>
                    <div class="flex items-center mt-1">
                      <p>{{ formatDate(subscription.end_date) }}</p>
                    </div>
                  </div>

                  <!-- Time Left -->
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Time Remaining</h3>
                    <p>{{ duration_left }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment History -->
            <div class="card bg-base-100 shadow-lg overflow-hidden">
              <div class="card-body p-6">
                <h2 class="card-title mb-4">Payment History</h2>
                <div class="overflow-x-auto">
                  <table class="table table-zebra w-full">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Transaction ID</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="subscription.payments && subscription.payments.length > 0">
                        <template v-for="payment in subscription.payments" :key="payment.id">
                          <td>{{ formatDate(payment.created_at) }}</td>
                          <td>{{ formatCurrency(payment.amount) }}</td>
                          <td>{{ payment.payment_method }}</td>
                          <td>
                            <span
                              class="px-2 py-1 text-xs font-medium rounded-full"
                              :class="getPaymentStatusClass(payment.status)"
                            >
                              {{ payment.status }}
                            </span>
                          </td>
                          <td class="font-mono text-xs">{{ payment.transaction_id }}</td>
                        </template>
                      </tr>
                      <tr v-else>
                        <td colspan="5" class="text-center py-4">No payment records found</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Side Panel -->
          <div class="col-span-1">
            <!-- User Information -->
            <div class="card bg-base-100 shadow-lg overflow-hidden mb-6">
              <div class="card-body p-6">
                <h2 class="card-title mb-4">User Information</h2>
                <div class="flex items-center mb-4">
                  <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-12">
                      <span>{{ getUserInitials(subscription.user) }}</span>
                    </div>
                  </div>
                  <div class="ml-3">
                    <p class="font-semibold">{{ subscription.user.name }}</p>
                    <p class="text-sm text-base-content/70">{{ subscription.user.email }}</p>
                  </div>
                </div>
                <div class="divider my-2"></div>
                <div class="flex flex-col space-y-2">
                  <a :href="`mailto:${subscription.user.email}`" class="btn btn-outline btn-sm w-full">
                    <Mail class="w-4 h-4 mr-2" /> Email User
                  </a>
                  <a href="#" class="btn btn-outline btn-sm w-full">
                    <User class="w-4 h-4 mr-2" /> View Profile
                  </a>
                  <a href="#" class="btn btn-outline btn-sm w-full">
                    <History class="w-4 h-4 mr-2" /> View All Subscriptions
                  </a>
                </div>
              </div>
            </div>

            <!-- Plan Information -->
            <div class="card bg-base-100 shadow-lg overflow-hidden">
              <div class="card-body p-6">
                <h2 class="card-title mb-4">Plan Information</h2>
                <div class="space-y-3">
                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Name</h3>
                    <p class="font-medium">{{ subscription.plan.name }}</p>
                  </div>

                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Price</h3>
                    <p class="font-medium">{{ formatCurrency(subscription.plan.price) }}</p>
                  </div>

                  <div>
                    <h3 class="text-sm font-semibold text-base-content/70">Duration</h3>
                    <p class="font-medium">
                      {{ subscription.plan.duration }} {{ subscription.plan.duration_type }}
                    </p>
                  </div>

                  <div v-if="subscription.plan.description">
                    <h3 class="text-sm font-semibold text-base-content/70">Description</h3>
                    <p class="text-sm">{{ subscription.plan.description }}</p>
                  </div>
                </div>
                <div class="divider my-3"></div>
                <button @click="openChangePlanModal" class="btn btn-outline btn-sm w-full">
                  <RefreshCw class="w-4 h-4 mr-2" /> Change Plan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Update Modal -->
      <Modal v-if="statusModalVisible" @close="statusModalVisible = false" title="Update Status">
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

      <!-- Dates Modal -->
      <Modal v-if="datesModalVisible" @close="datesModalVisible = false" title="Update Dates">
        <div class="p-4">
          <form @submit.prevent="updateDates">
            <div class="grid grid-cols-1 gap-4">
              <div>
                <label class="block text-sm font-medium mb-2">Start Date</label>
                <input v-model="dates.start_date" type="date" class="input input-bordered w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">End Date</label>
                <input v-model="dates.end_date" type="date" class="input input-bordered w-full" />
              </div>
            </div>
            <div class="flex justify-end space-x-2 mt-4">
              <button type="button" class="btn btn-ghost" @click="datesModalVisible = false">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">Update Dates</button>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Change Plan Modal -->
      <Modal v-if="changePlanModalVisible" @close="changePlanModalVisible = false" title="Change Plan">
        <div class="p-4">
          <form @submit.prevent="changePlan">
            <div class="mb-4">
              <label class="block text-sm font-medium mb-2">New Plan</label>
              <select v-model="newPlanForm.plan_id" class="select select-bordered w-full">
                <option value="" disabled>Select a plan</option>
                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                  {{ plan.name }} - {{ formatCurrency(plan.price) }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label class="flex items-center cursor-pointer">
                <input
                  v-model="newPlanForm.adjust_end_date"
                  type="checkbox"
                  class="checkbox checkbox-primary mr-2"
                />
                <span class="label-text">Adjust end date based on new plan</span>
              </label>
            </div>
            <div class="flex justify-end space-x-2">
              <button type="button" class="btn btn-ghost" @click="changePlanModalVisible = false">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="!newPlanForm.plan_id">
                Change Plan
              </button>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Extend Modal -->
      <Modal v-if="extendModalVisible" @close="extendModalVisible = false" title="Extend Subscription">
        <div class="p-4">
          <form @submit.prevent="extendSubscription">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-2">Duration</label>
                <input
                  v-model="extendForm.duration"
                  type="number"
                  min="1"
                  class="input input-bordered w-full"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">Duration Type</label>
                <select v-model="extendForm.duration_type" class="select select-bordered w-full">
                  <option value="days">Days</option>
                  <option value="weeks">Weeks</option>
                  <option value="months">Months</option>
                  <option value="years">Years</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end space-x-2 mt-4">
              <button type="button" class="btn btn-ghost" @click="extendModalVisible = false">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">Extend Subscription</button>
            </div>
          </form>
        </div>
      </Modal>
    </Layout>
  </template>

  <script setup>
  import { ref } from 'vue';
  import { router } from '@inertiajs/vue3';
  import axios from 'axios';
  import Layout from '@backend_layout/App.vue';
  import StatusBadge from '@backend_components/Subscrition/StatusBadge.vue';
  import Modal from '@backend_components/Subscrition/Modal.vue';

  // Import Lucide icons
  import {
    ArrowLeft, Edit, RefreshCw, RefreshCcw, Ban,
    CalendarPlus, Mail, User, History
  } from 'lucide-vue-next';

  const props = defineProps({
    subscription: {
      type: Object,
      required: true
    },
    duration_left: {
      type: String,
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

  // Modal visibility states
  const statusModalVisible = ref(false);
  const datesModalVisible = ref(false);
  const changePlanModalVisible = ref(false);
  const extendModalVisible = ref(false);

  // Form states
  const selectedStatus = ref(props.subscription.status);
  const dates = ref({
    start_date: formatDateForInput(props.subscription.start_date),
    end_date: formatDateForInput(props.subscription.end_date)
  });
  const newPlanForm = ref({
    plan_id: '',
    adjust_end_date: true
  });
  const extendForm = ref({
    duration: 1,
    duration_type: 'months'
  });

  // Methods
  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString();
  }

  function formatDateForInput(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
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

  function getPaymentStatusClass(status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return 'bg-success/20 text-success';
      case 'pending':
        return 'bg-warning/20 text-warning';
      case 'failed':
        return 'bg-error/20 text-error';
      case 'refunded':
        return 'bg-info/20 text-info';
      default:
        return 'bg-base-300 text-base-content';
    }
  }

  // Modal controls
  function openStatusModal() {
    selectedStatus.value = props.subscription.status;
    statusModalVisible.value = true;
  }

  function openDatesModal() {
    dates.value = {
      start_date: formatDateForInput(props.subscription.start_date),
      end_date: formatDateForInput(props.subscription.end_date)
    };
    datesModalVisible.value = true;
  }

  function openChangePlanModal() {
    newPlanForm.value = {
      plan_id: '',
      adjust_end_date: true
    };
    changePlanModalVisible.value = true;
  }

  function openExtendModal() {
    extendForm.value = {
      duration: 1,
      duration_type: 'months'
    };
    extendModalVisible.value = true;
  }

  // Action methods
  async function updateStatus() {
    try {
      const response = await axios.put(
        route('admin.store.subscriptions.update-status', props.subscription.id),
        { status: selectedStatus.value }
      );
      window.location.reload();
    } catch (error) {
      console.error('Error updating status:', error);
      alert('Error updating subscription status');
    }
  }

  async function updateDates() {
    try {
      await axios.put(
        route('admin.store.subscriptions.update-dates', props.subscription.id),
        dates.value
      );
      window.location.reload();
    } catch (error) {
      console.error('Error updating dates:', error);
      alert('Error updating subscription dates');
    }
  }

  async function changePlan() {
    try {
      await axios.put(
        route('admin.store.subscriptions.change-plan', props.subscription.id),
        newPlanForm.value
      );
      window.location.reload();
    } catch (error) {
      console.error('Error changing plan:', error);
      alert('Error changing subscription plan');
    }
  }

  async function extendSubscription() {
    try {
      await axios.put(
        route('admin.store.subscriptions.extend', props.subscription.id),
        extendForm.value
      );
      window.location.reload();
    } catch (error) {
      console.error('Error extending subscription:', error);
      alert('Error extending subscription');
    }
  }

  function confirmCancel() {
    if (confirm(`Are you sure you want to cancel this subscription?`)) {
      cancelSubscription();
    }
  }

  async function cancelSubscription() {
    try {
      await axios.put(route('admin.store.subscriptions.cancel', props.subscription.id));
      window.location.reload();
    } catch (error) {
      console.error('Error cancelling subscription:', error);
      alert('Error cancelling subscription');
    }
  }

  async function toggleAutoRenew() {
    try {
      await axios.put(route('admin.store.subscriptions.toggle-renew', props.subscription.id));
      window.location.reload();
    } catch (error) {
      console.error('Error toggling auto-renew:', error);
      alert('Error updating auto-renew setting');
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
