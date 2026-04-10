<template>
  <div class="analytics-container">

    <!-- Analytics Header -->
    <div class="analytics-header">
      <div class="header-content">
        <h1 class="analytics-title">
          <span class="title-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
          </span>
          Analytics Dashboard
        </h1>
        <p class="analytics-subtitle">Monitor system performance and user engagement metrics</p>
      </div>

      <div class="header-controls">
        <div class="period-selector">
          <div class="period-buttons">
            <button
              v-for="periodOption in periods"
              :key="periodOption.value"
              class="period-btn"
              :class="{ active: selectedPeriod === periodOption.value }"
              @click="changePeriod(periodOption.value)"
            >
              {{ periodOption.label }}
            </button>
          </div>
        </div>

        <div class="action-buttons">
          <button class="action-btn refresh-btn" @click="refreshAnalytics" :disabled="loading" title="Refresh">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ spinning: loading }">
              <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
          </button>

          <div class="dropdown" ref="dropdownRef">
            <button class="action-btn export-btn" @click="toggleDropdown">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              Export
            </button>
            <div class="dropdown-menu" v-show="showDropdown">
              <button class="dropdown-item" @click="exportData('csv')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                CSV Format
              </button>
              <button class="dropdown-item" @click="exportData('json')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                JSON Format
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-ring"></div>
      <p class="loading-text">Loading analytics data...</p>
    </div>

    <template v-else>

      <!-- KPI Stats Grid -->
      <div class="kpi-grid">
        <div class="kpi-card kpi-users">
          <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="kpi-body">
            <div class="kpi-value">{{ formatNumber(analytics.overview.totalUsers) }}</div>
            <div class="kpi-label">Total Users</div>
            <div class="kpi-trend trend-up">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
              {{ formatNumber(analytics.overview.newUsers) }} new this period
            </div>
          </div>
        </div>

        <div class="kpi-card kpi-active">
          <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              <polyline points="16 11 17 11 17 17 16 17"/>
            </svg>
          </div>
          <div class="kpi-body">
            <div class="kpi-value">{{ formatNumber(analytics.overview.activeUsers) }}</div>
            <div class="kpi-label">Active Users</div>
            <div class="kpi-trend trend-blue">
              {{ getPercentage(analytics.overview.activeUsers, analytics.overview.totalUsers) }}% engagement rate
            </div>
          </div>
        </div>

        <div class="kpi-card kpi-tasks">
          <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
              <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
          </div>
          <div class="kpi-body">
            <div class="kpi-value">{{ formatNumber(analytics.overview.totalTasks) }}</div>
            <div class="kpi-label">Total Tasks</div>
            <div class="kpi-trend trend-amber">
              {{ analytics.overview.avgTasksPerUser || 0 }} avg per user
            </div>
          </div>
        </div>

        <div class="kpi-card kpi-classes">
          <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div class="kpi-body">
            <div class="kpi-value">{{ formatNumber(analytics.overview.totalClasses) }}</div>
            <div class="kpi-label">Total Classes</div>
            <div class="kpi-trend trend-purple">
              {{ analytics.overview.avgClassesPerUser || 0 }} avg per user
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-grid">

        <!-- User Growth Chart -->
        <div class="chart-card">
          <div class="chart-header">
            <h3 class="chart-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
              </svg>
              User Growth
            </h3>
            <div class="chart-toggle-group">
              <button
                v-for="opt in growthOptions"
                :key="opt.value"
                class="chart-toggle-btn"
                :class="{ active: userGrowthType === opt.value }"
                @click="userGrowthType = opt.value; updateUserGrowthChart()"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>

          <!-- Mini summary stats -->
          <div class="growth-stats">
            <div class="growth-stat">
              <span class="gs-value">{{ growthSummary.total.toLocaleString() }}</span>
              <span class="gs-label">Total new</span>
            </div>
            <div class="growth-stat">
              <span class="gs-value">{{ growthSummary.peak.toLocaleString() }}</span>
              <span class="gs-label">Peak day</span>
            </div>
            <div class="growth-stat">
              <span class="gs-value gs-up">+{{ growthSummary.rate }}%</span>
              <span class="gs-label">Avg growth</span>
            </div>
          </div>

          <div class="chart-canvas-wrap">
            <canvas ref="userGrowthChart"></canvas>
          </div>
        </div>

        <!-- Content Distribution Chart -->
        <div class="chart-card">
          <div class="chart-header">
            <h3 class="chart-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                <circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/>
              </svg>
              Content Distribution
            </h3>
          </div>

          <div class="donut-wrap">
            <canvas ref="contentDistributionChart"></canvas>
            <div class="donut-center">
              <div class="donut-center-num">{{ formatNumber(contentTotal) }}</div>
              <div class="donut-center-sub">total items</div>
            </div>
          </div>

          <div class="content-legend">
            <div
              v-for="item in contentLegendItems"
              :key="item.label"
              class="content-legend-row"
            >
              <span class="cl-dot" :style="{ background: item.color }"></span>
              <span class="cl-label">{{ item.label }}</span>
              <div class="cl-bar-wrap">
                <div class="cl-bar" :style="{ width: item.pct + '%', background: item.color }"></div>
              </div>
              <span class="cl-val">{{ formatNumber(item.value) }}</span>
              <span class="cl-pct">{{ item.pct }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Metrics Grid -->
      <div class="metrics-grid">

        <!-- Task Analytics -->
        <div class="metrics-card">
          <div class="metrics-header">
            <h3>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
              </svg>
              Task Analytics
            </h3>
          </div>
          <div class="metrics-body">
            <div class="metric-group">
              <h4 class="metric-group-title">Task Status</h4>
              <div class="status-list">
                <div class="status-row" v-for="s in taskStatuses" :key="s.key">
                  <div class="status-info">
                    <span class="status-dot" :style="{ background: s.color }"></span>
                    <span class="status-name">{{ s.label }}</span>
                  </div>
                  <div class="status-bar-outer">
                    <div class="status-bar-inner" :style="{ width: getTaskStatusPercentage(s.key) + '%', background: s.color }"></div>
                  </div>
                  <div class="status-counts">
                    <span class="status-num">{{ analytics.contentStats?.tasksByStatus?.[s.key] || 0 }}</span>
                    <span class="status-pct">{{ getTaskStatusPercentage(s.key) }}%</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="metric-group">
              <h4 class="metric-group-title">Task Priority</h4>
              <div class="priority-grid">
                <div class="priority-pill priority-low">
                  <div class="pp-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
                  </div>
                  <div class="pp-count">{{ analytics.contentStats?.tasksByPriority?.low || 0 }}</div>
                  <div class="pp-label">Low</div>
                </div>
                <div class="priority-pill priority-medium">
                  <div class="pp-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  </div>
                  <div class="pp-count">{{ analytics.contentStats?.tasksByPriority?.medium || 0 }}</div>
                  <div class="pp-label">Medium</div>
                </div>
                <div class="priority-pill priority-high">
                  <div class="pp-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                  </div>
                  <div class="pp-count">{{ analytics.contentStats?.tasksByPriority?.high || 0 }}</div>
                  <div class="pp-label">High</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- System Metrics -->
        <div class="metrics-card">
          <div class="metrics-header">
            <h3>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
              </svg>
              System Metrics
            </h3>
          </div>
          <div class="metrics-body">
            <div class="metric-group">
              <h4 class="metric-group-title">User Retention</h4>
              <div class="retention-rows">
                <div class="retention-row">
                  <div class="retention-info">
                    <span class="retention-label">Retention Rate</span>
                    <span class="retention-val retention-good">{{ analytics.systemUsage?.retentionRate || 0 }}%</span>
                  </div>
                  <div class="retention-track">
                    <div class="retention-fill retention-fill-good" :style="{ width: (analytics.systemUsage?.retentionRate || 0) + '%' }"></div>
                  </div>
                </div>
                <div class="retention-row">
                  <div class="retention-info">
                    <span class="retention-label">Churn Rate</span>
                    <span class="retention-val retention-bad">{{ analytics.systemUsage?.churnRate || 0 }}%</span>
                  </div>
                  <div class="retention-track">
                    <div class="retention-fill retention-fill-bad" :style="{ width: (analytics.systemUsage?.churnRate || 0) + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="metric-group">
              <h4 class="metric-group-title">Device Usage</h4>
              <div class="device-grid">
                <div class="device-card" v-for="d in deviceItems" :key="d.key">
                  <div class="device-icon" v-html="d.icon"></div>
                  <div class="device-val">{{ analytics.systemUsage?.deviceUsage?.[d.key] || 0 }}%</div>
                  <div class="device-label">{{ d.label }}</div>
                  <div class="device-ring-wrap">
                    <svg viewBox="0 0 40 40" class="device-ring">
                      <circle cx="20" cy="20" r="16" fill="none" stroke-width="4" class="ring-bg"/>
                      <circle cx="20" cy="20" r="16" fill="none" stroke-width="4" class="ring-fill"
                        :style="{
                          strokeDasharray: `${(analytics.systemUsage?.deviceUsage?.[d.key] || 0) * 1.005} 100.5`,
                          stroke: d.color
                        }"
                        stroke-linecap="round"
                        transform="rotate(-90 20 20)"
                      />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Users & Recent Activity -->
      <div class="activity-grid">

        <!-- Top Active Users -->
        <div class="activity-card">
          <div class="activity-header">
            <h3>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
              </svg>
              Top Active Users
            </h3>
            <span class="count-badge">{{ analytics.topActiveUsers?.length || 0 }} users</span>
          </div>
          <div class="activity-body">
            <div class="user-list">
              <div v-for="(user, index) in analytics.topActiveUsers" :key="user.id" class="user-item">
                <div class="user-rank" :class="{ 'rank-gold': index === 0, 'rank-silver': index === 1, 'rank-bronze': index === 2 }">
                  {{ index + 1 }}
                </div>
                <div class="user-avatar">{{ getUserInitials(user.name) }}</div>
                <div class="user-info">
                  <div class="user-name">{{ user.name }}</div>
                  <div class="user-email">{{ user.email }}</div>
                  <div class="user-seen">{{ user.last_login || 'Recently' }}</div>
                </div>
                <div class="user-stats">
                  <div class="user-stat">
                    <span class="us-num">{{ user.total_tasks || 0 }}</span>
                    <span class="us-lbl">Tasks</span>
                  </div>
                  <div class="user-stat">
                    <span class="us-num">{{ user.total_classes || 0 }}</span>
                    <span class="us-lbl">Classes</span>
                  </div>
                  <div class="user-stat">
                    <span class="us-num">{{ user.total_notes || 0 }}</span>
                    <span class="us-lbl">Notes</span>
                  </div>
                </div>
                <div class="score-badge">
                  <div class="score-num">{{ user.activity_score || 0 }}</div>
                  <div class="score-lbl">Score</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-card">
          <div class="activity-header">
            <h3>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
              Recent Activity
            </h3>
          </div>
          <div class="activity-body">
            <div class="activity-list">
              <div v-for="activity in analytics.recentActivity" :key="activity.id" class="activity-item">
                <div class="activity-dot" :class="'dot-' + activity.color"></div>
                <div class="activity-content">
                  <div class="activity-title">{{ activity.title }}</div>
                  <div class="activity-desc">{{ activity.description }}</div>
                  <div class="activity-meta">
                    <span>{{ activity.user?.name || 'Unknown' }}</span>
                    <span class="meta-sep">·</span>
                    <span>{{ timeAgo(activity.time) }}</span>
                  </div>
                </div>
                <div class="activity-tag" :class="'tag-' + activity.color">
                  {{ formatActivityType(activity.type) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<script>
import axios from 'axios';
import Chart from 'chart.js/auto';

export default {
  name: 'AdminAnalytics',
  data() {
    return {
      loading: false,
      selectedPeriod: 'week',
      periods: [
        { value: 'day',   label: 'Today' },
        { value: 'week',  label: 'Week'  },
        { value: 'month', label: 'Month' },
        { value: 'year',  label: 'Year'  }
      ],
      growthOptions: [
        { value: 'new',   label: 'New users'   },
        { value: 'total', label: 'Total'        },
        { value: 'rate',  label: 'Growth rate'  }
      ],
      analytics: {
        overview: {
          totalUsers: 0, newUsers: 0, activeUsers: 0,
          totalTasks: 0, totalNotes: 0, totalClasses: 0,
          totalReminders: 0, avgTasksPerUser: 0, avgClassesPerUser: 0
        },
        userGrowth: [],
        contentStats: {
          tasksByStatus:   { pending: 0, in_progress: 0, completed: 0 },
          tasksByPriority: { low: 0, medium: 0, high: 0 }
        },
        systemUsage: {
          retentionRate: 0, churnRate: 0,
          deviceUsage: { desktop: 0, mobile: 0, tablet: 0 }
        },
        topActiveUsers: [],
        recentActivity: []
      },
      userGrowthType: 'new',
      showDropdown: false,
      userGrowthChart: null,
      contentChart: null,
      taskStatuses: [
        { key: 'pending',     label: 'Pending',     color: '#F6AD55' },
        { key: 'in_progress', label: 'In Progress',  color: '#63B3ED' },
        { key: 'completed',   label: 'Completed',   color: '#68D391' }
      ],
      deviceItems: [
        {
          key: 'desktop', label: 'Desktop', color: '#667EEA',
          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>`
        },
        {
          key: 'mobile', label: 'Mobile', color: '#68D391',
          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>`
        },
        {
          key: 'tablet', label: 'Tablet', color: '#F6AD55',
          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>`
        }
      ]
    };
  },

  computed: {
    contentTotal() {
      const o = this.analytics.overview;
      return (o.totalTasks || 0) + (o.totalNotes || 0) + (o.totalClasses || 0) + (o.totalReminders || 0);
    },
    contentLegendItems() {
      const o = this.analytics.overview;
      const total = this.contentTotal || 1;
      return [
        { label: 'Tasks',     value: o.totalTasks     || 0, color: '#ED8936', pct: Math.round((o.totalTasks     || 0) / total * 100) },
        { label: 'Notes',     value: o.totalNotes     || 0, color: '#9F7AEA', pct: Math.round((o.totalNotes     || 0) / total * 100) },
        { label: 'Classes',   value: o.totalClasses   || 0, color: '#4299E1', pct: Math.round((o.totalClasses   || 0) / total * 100) },
        { label: 'Reminders', value: o.totalReminders || 0, color: '#48BB78', pct: Math.round((o.totalReminders || 0) / total * 100) }
      ];
    },
    growthSummary() {
      const data = this.analytics.userGrowth || [];
      const newCounts = data.map(d => d.new_users || 0);
      const total = newCounts.reduce((a, b) => a + b, 0);
      const peak  = newCounts.length ? Math.max(...newCounts) : 0;
      const rates = data.map(d => d.growth_rate || 0);
      const avgRate = rates.length ? (rates.reduce((a, b) => a + b, 0) / rates.length).toFixed(1) : 0;
      return { total, peak, rate: avgRate };
    }
  },

  mounted() {
    this.loadAnalytics();
    document.addEventListener('click', this.handleOutsideClick);
  },
  beforeUnmount() {
    if (this.userGrowthChart) this.userGrowthChart.destroy();
    if (this.contentChart)    this.contentChart.destroy();
    document.removeEventListener('click', this.handleOutsideClick);
  },

  methods: {
    handleOutsideClick(e) {
      if (this.$refs.dropdownRef && !this.$refs.dropdownRef.contains(e.target)) {
        this.showDropdown = false;
      }
    },

    async loadAnalytics() {
      this.loading = true;
      try {
        const response = await axios.get('/admin/analytics/data', {
          params: { period: this.selectedPeriod }
        });
        this.analytics = response.data;
        this.$nextTick(() => { this.initCharts(); });
      } catch (error) {
        console.error('Error loading analytics:', error);
        this.showError('Failed to load analytics data');
      } finally {
        this.loading = false;
      }
    },

    changePeriod(period) {
      this.selectedPeriod = period;
      this.loadAnalytics();
    },

    async refreshAnalytics() {
      await this.loadAnalytics();
    },

    async exportData(format) {
      try {
        const response = await axios.get('/admin/analytics/export', {
          params: { format, period: this.selectedPeriod },
          responseType: format === 'csv' ? 'blob' : 'json'
        });
        if (format === 'csv') {
          const url  = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', `analytics_${this.selectedPeriod}_${Date.now()}.csv`);
          document.body.appendChild(link);
          link.click();
          link.remove();
          window.URL.revokeObjectURL(url);
        } else {
          const dataStr = JSON.stringify(response.data, null, 2);
          const uri     = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
          const link    = document.createElement('a');
          link.setAttribute('href', uri);
          link.setAttribute('download', `analytics_${this.selectedPeriod}_${Date.now()}.json`);
          link.click();
        }
        this.showSuccess(`Exported as ${format.toUpperCase()}`);
      } catch (error) {
        console.error('Export error:', error);
        this.showError('Failed to export data');
      }
      this.showDropdown = false;
    },

    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },

    initCharts() {
      this.initUserGrowthChart();
      this.initContentDistributionChart();
    },

    initUserGrowthChart() {
      const ctx = this.$refs.userGrowthChart?.getContext('2d');
      if (!ctx) return;
      if (this.userGrowthChart) this.userGrowthChart.destroy();

      const growthData = this.analytics.userGrowth || [];
      const configs = {
        new:   { label: 'New Users',       data: growthData.map(d => d.new_users),    color: '#4299E1', fill: 'rgba(66,153,225,0.08)',   suffix: ' users' },
        total: { label: 'Total Users',      data: growthData.map(d => d.total_users),  color: '#48BB78', fill: 'rgba(72,187,120,0.08)',   suffix: ' users' },
        rate:  { label: 'Growth Rate (%)',  data: growthData.map(d => d.growth_rate),  color: '#9F7AEA', fill: 'rgba(159,122,234,0.08)', suffix: '%'      }
      };
      const cfg = configs[this.userGrowthType] || configs.new;

      this.userGrowthChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: growthData.map(d => d.date),
          datasets: [{
            label: cfg.label,
            data: cfg.data,
            borderColor: cfg.color,
            backgroundColor: cfg.fill,
            borderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: cfg.color,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(255,255,255,0.95)',
              titleColor: '#2D3748',
              bodyColor: '#4A5568',
              borderColor: '#E2E8F0',
              borderWidth: 1,
              padding: 10,
              callbacks: {
                label: ctx => ` ${ctx.parsed.y}${cfg.suffix}`
              }
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { font: { size: 11 }, color: '#A0AEC0', autoSkip: false, maxRotation: 0 },
              border: { display: false }
            },
            y: {
              beginAtZero: this.userGrowthType !== 'total',
              grid: { color: 'rgba(160,174,192,0.15)', drawBorder: false },
              ticks: {
                font: { size: 11 }, color: '#A0AEC0',
                callback: v => this.userGrowthType === 'rate' ? v + '%' : v
              },
              border: { display: false }
            }
          }
        }
      });
    },

    updateUserGrowthChart() {
      this.initUserGrowthChart();
    },

    initContentDistributionChart() {
      const ctx = this.$refs.contentDistributionChart?.getContext('2d');
      if (!ctx) return;
      if (this.contentChart) this.contentChart.destroy();

      const o = this.analytics.overview;
      this.contentChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Tasks', 'Notes', 'Classes', 'Reminders'],
          datasets: [{
            data: [
              o.totalTasks     || 0,
              o.totalNotes     || 0,
              o.totalClasses   || 0,
              o.totalReminders || 0
            ],
            backgroundColor: ['#ED8936', '#9F7AEA', '#4299E1', '#48BB78'],
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '72%',
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(255,255,255,0.95)',
              titleColor: '#2D3748',
              bodyColor: '#4A5568',
              borderColor: '#E2E8F0',
              borderWidth: 1,
              padding: 10,
              callbacks: {
                label: ctx => {
                  const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                  const pct   = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                  return ` ${ctx.label}: ${this.formatNumber(ctx.raw)} (${pct}%)`;
                }
              }
            }
          }
        }
      });
    },

    getTaskStatusPercentage(status) {
      const total = this.analytics.overview.totalTasks || 0;
      if (total === 0) return 0;
      const count = this.analytics.contentStats?.tasksByStatus?.[status] || 0;
      return ((count / total) * 100).toFixed(1);
    },

    getPercentage(part, total) {
      if (!total) return 0;
      return ((part / total) * 100).toFixed(1);
    },

    formatNumber(num) {
      if (!num && num !== 0) return '0';
      return num.toLocaleString();
    },

    getUserInitials(name) {
      if (!name) return 'NA';
      return name.substring(0, 2).toUpperCase();
    },

    formatActivityType(type) {
      if (!type) return '';
      return type.replace(/_/g, ' ');
    },

    timeAgo(date) {
      if (!date) return 'Recently';
      const seconds = Math.floor((new Date() - new Date(date)) / 1000);
      const intervals = { year: 31536000, month: 2592000, week: 604800, day: 86400, hour: 3600, minute: 60 };
      for (const [unit, secs] of Object.entries(intervals)) {
        const i = Math.floor(seconds / secs);
        if (i >= 1) return `${i} ${unit}${i === 1 ? '' : 's'} ago`;
      }
      return 'Just now';
    },

    showSuccess(msg) { console.log('Success:', msg); },
    showError(msg)   { console.error('Error:', msg); alert(msg); }
  },

  watch: {
    selectedPeriod() { this.loadAnalytics(); }
  }
};
</script>

<style scoped>
/* ─── Base ─────────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

.analytics-container {
  padding: 24px;
  background: #F0F4FF;
  min-height: 100vh;
  font-family: 'Inter', -apple-system, sans-serif;
}

/* ─── Header ────────────────────────────────────────────────────────── */
.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  background: #fff;
  border-radius: 16px;
  padding: 24px 28px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
  flex-wrap: wrap;
}

.analytics-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1A202C;
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 0 6px;
}

.title-icon {
  width: 36px;
  height: 36px;
  background: #EBF4FF;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4299E1;
  flex-shrink: 0;
}

.title-icon svg { width: 18px; height: 18px; }

.analytics-subtitle {
  font-size: 0.875rem;
  color: #718096;
  margin: 0;
}

.header-controls {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.period-buttons {
  display: flex;
  background: #F7FAFC;
  border-radius: 10px;
  padding: 4px;
  gap: 2px;
}

.period-btn {
  padding: 7px 16px;
  border: none;
  background: transparent;
  color: #718096;
  font-size: 0.8125rem;
  font-weight: 500;
  border-radius: 7px;
  cursor: pointer;
  transition: all 0.2s;
}

.period-btn:hover  { background: #EDF2F7; color: #2D3748; }
.period-btn.active { background: #fff; color: #2D3748; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

.action-buttons { display: flex; gap: 10px; }

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 9px;
  background: #fff;
  color: #4A5568;
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover { background: #F7FAFC; border-color: #CBD5E0; }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.action-btn svg { width: 14px; height: 14px; }

.refresh-btn svg.spinning { animation: spin 0.8s linear infinite; }

@keyframes spin { to { transform: rotate(360deg); } }

.export-btn {
  background: #4299E1;
  border-color: #4299E1;
  color: #fff;
}
.export-btn:hover { background: #3182CE; border-color: #3182CE; }

.dropdown { position: relative; }

.dropdown-menu {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  min-width: 160px;
  z-index: 100;
  overflow: hidden;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  width: 100%;
  border: none;
  background: #fff;
  color: #4A5568;
  font-size: 0.8125rem;
  text-align: left;
  cursor: pointer;
  transition: background 0.15s;
}
.dropdown-item:hover { background: #F7FAFC; }

/* ─── Loading ───────────────────────────────────────────────────────── */
.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 80px 20px;
}

.spinner-ring {
  width: 44px;
  height: 44px;
  border: 3px solid #E2E8F0;
  border-top-color: #4299E1;
  border-radius: 50%;
  animation: spin 0.9s linear infinite;
}

.loading-text {
  font-size: 0.875rem;
  color: #718096;
  margin: 0;
}

/* ─── KPI Grid ──────────────────────────────────────────────────────── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.kpi-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  border-left: 3px solid transparent;
  transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}

.kpi-users  { border-color: #4299E1; }
.kpi-active { border-color: #48BB78; }
.kpi-tasks  { border-color: #ED8936; }
.kpi-classes{ border-color: #9F7AEA; }

.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon svg { width: 20px; height: 20px; }

.kpi-users  .kpi-icon { background: #EBF8FF; color: #4299E1; }
.kpi-active .kpi-icon { background: #F0FFF4; color: #48BB78; }
.kpi-tasks  .kpi-icon { background: #FFFAF0; color: #ED8936; }
.kpi-classes .kpi-icon{ background: #FAF5FF; color: #9F7AEA; }

.kpi-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1A202C;
  line-height: 1;
  margin-bottom: 2px;
}

.kpi-label {
  font-size: 0.78rem;
  color: #A0AEC0;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin-bottom: 6px;
}

.kpi-trend {
  font-size: 0.8125rem;
  display: flex;
  align-items: center;
  gap: 4px;
}

.trend-up     { color: #48BB78; }
.trend-blue   { color: #4299E1; }
.trend-amber  { color: #ED8936; }
.trend-purple { color: #9F7AEA; }

/* ─── Charts Grid ───────────────────────────────────────────────────── */
.charts-grid {
  display: grid;
  grid-template-columns: 1.8fr 1fr;
  gap: 20px;
  margin-bottom: 24px;
}

.chart-card {
  background: #fff;
  border-radius: 14px;
  padding: 22px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 10px;
}

.chart-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #2D3748;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
}

.chart-toggle-group {
  display: flex;
  background: #F7FAFC;
  border-radius: 8px;
  padding: 3px;
  gap: 2px;
}

.chart-toggle-btn {
  padding: 5px 12px;
  border: none;
  background: transparent;
  color: #718096;
  font-size: 0.75rem;
  font-weight: 500;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.chart-toggle-btn:hover  { color: #2D3748; }
.chart-toggle-btn.active { background: #fff; color: #2D3748; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

/* Growth summary stats */
.growth-stats {
  display: flex;
  gap: 24px;
  padding: 12px 16px;
  background: #F7FAFC;
  border-radius: 10px;
  margin-bottom: 16px;
}

.growth-stat { display: flex; flex-direction: column; gap: 2px; }

.gs-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: #2D3748;
}

.gs-up { color: #48BB78; }

.gs-label {
  font-size: 0.7rem;
  color: #A0AEC0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.chart-canvas-wrap {
  position: relative;
  height: 220px;
}

/* Donut */
.donut-wrap {
  position: relative;
  height: 190px;
  margin-bottom: 16px;
}

.donut-center {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}

.donut-center-num {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2D3748;
}

.donut-center-sub {
  font-size: 0.7rem;
  color: #A0AEC0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Content legend */
.content-legend {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.content-legend-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.cl-dot {
  width: 8px;
  height: 8px;
  border-radius: 2px;
  flex-shrink: 0;
}

.cl-label {
  font-size: 0.8125rem;
  color: #4A5568;
  width: 68px;
  flex-shrink: 0;
}

.cl-bar-wrap {
  flex: 1;
  height: 6px;
  background: #EDF2F7;
  border-radius: 3px;
  overflow: hidden;
}

.cl-bar {
  height: 100%;
  border-radius: 3px;
  transition: width 0.6s ease;
}

.cl-val {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #2D3748;
  width: 40px;
  text-align: right;
  flex-shrink: 0;
}

.cl-pct {
  font-size: 0.75rem;
  color: #A0AEC0;
  width: 32px;
  text-align: right;
  flex-shrink: 0;
}

/* ─── Metrics Grid ──────────────────────────────────────────────────── */
.metrics-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 24px;
}

.metrics-card {
  background: #fff;
  border-radius: 14px;
  padding: 22px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.metrics-header {
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 1px solid #EDF2F7;
}

.metrics-header h3 {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #2D3748;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
}

.metric-group { margin-bottom: 24px; }
.metric-group:last-child { margin-bottom: 0; }

.metric-group-title {
  font-size: 0.78rem;
  font-weight: 600;
  color: #A0AEC0;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin: 0 0 12px;
}

/* Status bars */
.status-list { display: flex; flex-direction: column; gap: 10px; }

.status-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.status-info {
  display: flex;
  align-items: center;
  gap: 7px;
  width: 110px;
  flex-shrink: 0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-name {
  font-size: 0.8125rem;
  color: #4A5568;
}

.status-bar-outer {
  flex: 1;
  height: 6px;
  background: #EDF2F7;
  border-radius: 3px;
  overflow: hidden;
}

.status-bar-inner {
  height: 100%;
  border-radius: 3px;
  transition: width 0.6s ease;
  min-width: 2px;
}

.status-counts {
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 70px;
  justify-content: flex-end;
}

.status-num {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #2D3748;
}

.status-pct {
  font-size: 0.75rem;
  color: #A0AEC0;
}

/* Priority */
.priority-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.priority-pill {
  border-radius: 12px;
  padding: 14px 10px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.pp-icon { color: inherit; display: flex; }
.pp-icon svg { color: inherit; }

.priority-low    { background: #F0FFF4; color: #48BB78; }
.priority-medium { background: #FFFBEB; color: #D69E2E; }
.priority-high   { background: #FFF5F5; color: #E53E3E; }

.pp-count {
  font-size: 1.375rem;
  font-weight: 700;
  color: #2D3748;
  line-height: 1;
}

.pp-label {
  font-size: 0.75rem;
  color: #A0AEC0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Retention */
.retention-rows { display: flex; flex-direction: column; gap: 14px; }

.retention-row { display: flex; flex-direction: column; gap: 6px; }

.retention-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.retention-label {
  font-size: 0.8125rem;
  color: #4A5568;
}

.retention-val {
  font-size: 0.875rem;
  font-weight: 700;
}

.retention-good { color: #48BB78; }
.retention-bad  { color: #E53E3E; }

.retention-track {
  height: 6px;
  background: #EDF2F7;
  border-radius: 3px;
  overflow: hidden;
}

.retention-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.7s ease;
}

.retention-fill-good { background: #68D391; }
.retention-fill-bad  { background: #FC8181; }

/* Devices */
.device-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.device-card {
  background: #F7FAFC;
  border-radius: 12px;
  padding: 14px 10px;
  text-align: center;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.device-icon {
  color: #A0AEC0;
  display: flex;
  justify-content: center;
  margin-bottom: 2px;
}

.device-icon svg { width: 18px; height: 18px; }

.device-val {
  font-size: 1.125rem;
  font-weight: 700;
  color: #2D3748;
}

.device-label {
  font-size: 0.7rem;
  color: #A0AEC0;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.device-ring-wrap {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 28px;
  height: 28px;
}

.device-ring { width: 100%; height: 100%; }

.ring-bg   { stroke: #EDF2F7; }
.ring-fill {
  stroke-dashoffset: 0;
  transition: stroke-dasharray 0.8s ease;
}

/* ─── Activity Grid ─────────────────────────────────────────────────── */
.activity-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.activity-card {
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px;
  border-bottom: 1px solid #EDF2F7;
}

.activity-header h3 {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #2D3748;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
}

.count-badge {
  font-size: 0.75rem;
  font-weight: 500;
  background: #EBF8FF;
  color: #3182CE;
  padding: 3px 10px;
  border-radius: 20px;
}

.activity-body {
  padding: 18px 22px;
  max-height: 480px;
  overflow-y: auto;
}

.activity-body::-webkit-scrollbar { width: 4px; }
.activity-body::-webkit-scrollbar-track { background: transparent; }
.activity-body::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 2px; }

/* Top users */
.user-list { display: flex; flex-direction: column; gap: 12px; }

.user-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: #F7FAFC;
  border-radius: 12px;
  transition: background 0.15s;
  flex-wrap: wrap;
}

.user-item:hover { background: #EDF2F7; }

.user-rank {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #E2E8F0;
  color: #4A5568;
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.rank-gold   { background: #FEFCBF; color: #B7791F; }
.rank-silver { background: #E2E8F0; color: #4A5568; }
.rank-bronze { background: #FEEBC8; color: #C05621; }

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #4299E1;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-info { flex: 1; min-width: 120px; }

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2D3748;
}

.user-email {
  font-size: 0.75rem;
  color: #718096;
}

.user-seen {
  font-size: 0.7rem;
  color: #A0AEC0;
  margin-top: 2px;
}

.user-stats { display: flex; gap: 14px; }

.user-stat { text-align: center; }

.us-num {
  display: block;
  font-size: 0.9375rem;
  font-weight: 700;
  color: #2D3748;
}

.us-lbl {
  display: block;
  font-size: 0.65rem;
  color: #A0AEC0;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.score-badge {
  text-align: center;
  background: #4299E1;
  color: #fff;
  border-radius: 10px;
  padding: 6px 12px;
  min-width: 56px;
}

.score-num { font-size: 1.1rem; font-weight: 700; }
.score-lbl { font-size: 0.65rem; opacity: 0.85; }

/* Recent activity */
.activity-list { display: flex; flex-direction: column; gap: 14px; }

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  background: #F7FAFC;
  border-radius: 12px;
  transition: background 0.15s;
}

.activity-item:hover { background: #EDF2F7; }

.activity-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 5px;
}

.dot-success { background: #48BB78; }
.dot-primary { background: #4299E1; }
.dot-info    { background: #667EEA; }
.dot-warning { background: #ED8936; }

.activity-content { flex: 1; min-width: 0; }

.activity-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #2D3748;
  margin-bottom: 2px;
}

.activity-desc {
  font-size: 0.78rem;
  color: #718096;
  margin-bottom: 6px;
}

.activity-meta {
  font-size: 0.73rem;
  color: #A0AEC0;
  display: flex;
  align-items: center;
  gap: 4px;
}

.meta-sep { opacity: 0.5; }

.activity-tag {
  font-size: 0.68rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  padding: 3px 8px;
  border-radius: 5px;
  white-space: nowrap;
  flex-shrink: 0;
}

.tag-success { background: #C6F6D5; color: #22543D; }
.tag-primary { background: #BEE3F8; color: #2C5282; }
.tag-info    { background: #C3DAFE; color: #434190; }
.tag-warning { background: #FEECDC; color: #7B341E; }

/* ─── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .charts-grid,
  .metrics-grid,
  .activity-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .analytics-header { flex-direction: column; }
  .kpi-grid { grid-template-columns: 1fr 1fr; }
  .priority-grid,
  .device-grid { grid-template-columns: repeat(3, 1fr); }
  .user-item { flex-wrap: wrap; }
  .user-stats { width: 100%; justify-content: space-around; }
  .score-badge { width: 100%; }
}

@media (max-width: 480px) {
  .kpi-grid { grid-template-columns: 1fr; }
  .analytics-container { padding: 14px; }
}
</style>