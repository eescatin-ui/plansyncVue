@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="analytics-container">
    <!-- Analytics Header -->
    <div class="analytics-header">
        <div class="header-content">
            <h1 class="analytics-title">
                <i class="fas fa-chart-line analytics-icon"></i>
                Analytics Dashboard
            </h1>
            <p class="analytics-subtitle">Monitor system performance and user engagement metrics</p>
        </div>
        
        <div class="header-controls">
            <!-- Period Selector -->
            <div class="period-selector">
                <div class="period-buttons">
                    @php
                        $periods = ['day' => 'Today', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'];
                    @endphp
                    @foreach($periods as $key => $label)
                        <a href="?period={{ $key }}" 
                           class="period-btn {{ $period === $key ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="action-buttons">
                <button class="action-btn refresh-btn" id="refreshAnalytics" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
                
                <div class="dropdown export-dropdown">
                    <button class="action-btn export-btn" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.analytics.export', ['format' => 'csv', 'period' => $period]) }}">
                            <i class="fas fa-file-csv"></i> CSV Format
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.analytics.export', ['format' => 'json', 'period' => $period]) }}">
                            <i class="fas fa-file-code"></i> JSON Format
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Stats Grid -->
    <div class="kpi-grid">
        <!-- Total Users -->
        <div class="kpi-card user-kpi">
            <div class="kpi-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-value">{{ number_format($analytics['overview']['totalUsers']) }}</div>
                <div class="kpi-label">Total Users</div>
                <div class="kpi-trend">
                    <span class="trend-up">
                        <i class="fas fa-arrow-up"></i>
                        {{ $analytics['overview']['newUsers'] }} new
                    </span>
                    <span class="trend-text">this period</span>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="kpi-card active-kpi">
            <div class="kpi-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-value">{{ number_format($analytics['overview']['activeUsers']) }}</div>
                <div class="kpi-label">Active Users</div>
                <div class="kpi-trend">
                    <span class="trend-percent">
                        {{ round(($analytics['overview']['activeUsers'] / max($analytics['overview']['totalUsers'], 1)) * 100, 1) }}%
                    </span>
                    <span class="trend-text">engagement rate</span>
                </div>
            </div>
        </div>

        <!-- Tasks -->
        <div class="kpi-card task-kpi">
            <div class="kpi-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-value">{{ number_format($analytics['overview']['totalTasks']) }}</div>
                <div class="kpi-label">Total Tasks</div>
                <div class="kpi-trend">
                    <span class="trend-avg">{{ $analytics['overview']['avgTasksPerUser'] }}</span>
                    <span class="trend-text">avg per user</span>
                </div>
            </div>
        </div>

        <!-- Classes -->
        <div class="kpi-card class-kpi">
            <div class="kpi-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-value">{{ number_format($analytics['overview']['totalClasses']) }}</div>
                <div class="kpi-label">Total Classes</div>
                <div class="kpi-trend">
                    <span class="trend-avg">{{ $analytics['overview']['avgClassesPerUser'] }}</span>
                    <span class="trend-text">avg per user</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- User Growth Chart -->
        <div class="chart-card wide-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> User Growth</h3>
                <div class="chart-controls">
                    <button class="chart-btn active" data-chart="growth-new">New Users</button>
                    <button class="chart-btn" data-chart="growth-total">Total Users</button>
                    <button class="chart-btn" data-chart="growth-rate">Growth Rate</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Content Distribution -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Content Distribution</h3>
            </div>
            <div class="chart-container">
                <canvas id="contentDistributionChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color tasks-color"></span>
                    <span>Tasks ({{ number_format($analytics['overview']['totalTasks']) }})</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color notes-color"></span>
                    <span>Notes ({{ number_format($analytics['overview']['totalNotes']) }})</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color classes-color"></span>
                    <span>Classes ({{ number_format($analytics['overview']['totalClasses']) }})</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color reminders-color"></span>
                    <span>Reminders ({{ number_format($analytics['overview']['totalReminders']) }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="metrics-grid">
        <!-- Task Metrics -->
        <div class="metrics-card">
            <div class="metrics-header">
                <h3><i class="fas fa-tasks"></i> Task Analytics</h3>
            </div>
            <div class="metrics-body">
                <!-- Status Distribution -->
                <div class="metric-group">
                    <h4>Task Status</h4>
                    <div class="status-bars">
                        <div class="status-bar">
                            <div class="status-label">
                                <span class="status-dot pending"></span>
                                Pending
                            </div>
                            <div class="status-value">
                                {{ $analytics['contentStats']['tasksByStatus']['pending'] }}
                                <span class="status-percent">
                                    {{ round(($analytics['contentStats']['tasksByStatus']['pending'] / max($analytics['overview']['totalTasks'], 1)) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                        <div class="status-bar">
                            <div class="status-label">
                                <span class="status-dot in-progress"></span>
                                In Progress
                            </div>
                            <div class="status-value">
                                {{ $analytics['contentStats']['tasksByStatus']['in_progress'] }}
                                <span class="status-percent">
                                    {{ round(($analytics['contentStats']['tasksByStatus']['in_progress'] / max($analytics['overview']['totalTasks'], 1)) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                        <div class="status-bar">
                            <div class="status-label">
                                <span class="status-dot completed"></span>
                                Completed
                            </div>
                            <div class="status-value">
                                {{ $analytics['contentStats']['tasksByStatus']['completed'] }}
                                <span class="status-percent">
                                    {{ round(($analytics['contentStats']['tasksByStatus']['completed'] / max($analytics['overview']['totalTasks'], 1)) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Priority Distribution -->
                <div class="metric-group">
                    <h4>Task Priority</h4>
                    <div class="priority-cards">
                        <div class="priority-card low">
                            <div class="priority-icon">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="priority-content">
                                <div class="priority-count">{{ $analytics['contentStats']['tasksByPriority']['low'] }}</div>
                                <div class="priority-label">Low</div>
                            </div>
                        </div>
                        <div class="priority-card medium">
                            <div class="priority-icon">
                                <i class="fas fa-minus"></i>
                            </div>
                            <div class="priority-content">
                                <div class="priority-count">{{ $analytics['contentStats']['tasksByPriority']['medium'] }}</div>
                                <div class="priority-label">Medium</div>
                            </div>
                        </div>
                        <div class="priority-card high">
                            <div class="priority-icon">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="priority-content">
                                <div class="priority-count">{{ $analytics['contentStats']['tasksByPriority']['high'] }}</div>
                                <div class="priority-label">High</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Metrics -->
        <div class="metrics-card">
            <div class="metrics-header">
                <h3><i class="fas fa-chart-bar"></i> System Metrics</h3>
            </div>
            <div class="metrics-body">
                <!-- Retention & Churn -->
                <div class="metric-group">
                    <h4>User Retention</h4>
                    <div class="retention-metrics">
                        <div class="retention-card retention">
                            <div class="metric-value">{{ $analytics['systemUsage']['retentionRate'] }}%</div>
                            <div class="metric-label">Retention Rate</div>
                            <div class="metric-progress">
                                <div class="progress-bar" style="width: {{ $analytics['systemUsage']['retentionRate'] }}%"></div>
                            </div>
                        </div>
                        <div class="retention-card churn">
                            <div class="metric-value">{{ $analytics['systemUsage']['churnRate'] }}%</div>
                            <div class="metric-label">Churn Rate</div>
                            <div class="metric-progress">
                                <div class="progress-bar" style="width: {{ $analytics['systemUsage']['churnRate'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Device Usage -->
                <div class="metric-group">
                    <h4>Device Usage</h4>
                    <div class="device-metrics">
                        <div class="device-card">
                            <div class="device-icon">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <div class="device-content">
                                <div class="device-value">{{ $analytics['systemUsage']['deviceUsage']['desktop'] }}%</div>
                                <div class="device-label">Desktop</div>
                            </div>
                        </div>
                        <div class="device-card">
                            <div class="device-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="device-content">
                                <div class="device-value">{{ $analytics['systemUsage']['deviceUsage']['mobile'] }}%</div>
                                <div class="device-label">Mobile</div>
                            </div>
                        </div>
                        <div class="device-card">
                            <div class="device-icon">
                                <i class="fas fa-tablet-alt"></i>
                            </div>
                            <div class="device-content">
                                <div class="device-value">{{ $analytics['systemUsage']['deviceUsage']['tablet'] }}%</div>
                                <div class="device-label">Tablet</div>
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
                <h3><i class="fas fa-crown"></i> Top Active Users</h3>
                <span class="badge">{{ $analytics['topActiveUsers']->count() }} Users</span>
            </div>
            <div class="activity-body">
                <div class="user-list">
                    @foreach($analytics['topActiveUsers'] as $index => $user)
                        <div class="user-item">
                            <div class="user-rank">{{ $index + 1 }}</div>
                            <div class="user-avatar">
                                {{ substr($user['name'], 0, 1) }}
                            </div>
                            <div class="user-info">
                                <div class="user-name">{{ $user['name'] }}</div>
                                <div class="user-email">{{ $user['email'] }}</div>
                                <div class="user-last-seen">
                                    <i class="fas fa-clock"></i> {{ $user['last_login'] }}
                                </div>
                            </div>
                            <div class="user-metrics">
                                <div class="user-metric">
                                    <span class="metric-count">{{ $user['total_tasks'] }}</span>
                                    <span class="metric-label">Tasks</span>
                                </div>
                                <div class="user-metric">
                                    <span class="metric-count">{{ $user['total_classes'] }}</span>
                                    <span class="metric-label">Classes</span>
                                </div>
                                <div class="user-metric">
                                    <span class="metric-count">{{ $user['total_notes'] }}</span>
                                    <span class="metric-label">Notes</span>
                                </div>
                            </div>
                            <div class="user-score">
                                <div class="score-value">{{ $user['activity_score'] }}</div>
                                <div class="score-label">Score</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-card">
            <div class="activity-header">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
            </div>
            <div class="activity-body">
                <div class="activity-list">
                    @foreach($analytics['recentActivity'] as $activity)
                        <div class="activity-item">
                            <div class="activity-icon {{ $activity['color'] }}">
                                <i class="fas fa-{{ $activity['icon'] }}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">{{ $activity['title'] }}</div>
                                <div class="activity-description">{{ $activity['description'] }}</div>
                                <div class="activity-meta">
                                    <span class="activity-user">
                                        <i class="fas fa-user"></i> {{ $activity['user']->name }}
                                    </span>
                                    <span class="activity-time">
                                        <i class="fas fa-clock"></i> {{ $activity['time']->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <div class="activity-type {{ $activity['color'] }}">
                                {{ str_replace('_', ' ', $activity['type']) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-container {
    padding: 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    padding: 24px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
}

.header-content {
    flex: 1;
}

.analytics-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.analytics-icon {
    color: #667eea;
    font-size: 2.2rem;
}

.analytics-subtitle {
    color: #718096;
    font-size: 1rem;
    margin: 0;
}

.header-controls {
    display: flex;
    gap: 20px;
    align-items: center;
}

.period-selector {
    background: #f7fafc;
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.period-buttons {
    display: flex;
    gap: 4px;
}

.period-btn {
    padding: 8px 16px;
    border: none;
    background: transparent;
    color: #718096;
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.period-btn:hover {
    background: #edf2f7;
    color: #4a5568;
}

.period-btn.active {
    background: #667eea;
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
}

.action-buttons {
    display: flex;
    gap: 12px;
}

.action-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.refresh-btn {
    background: #edf2f7;
    color: #4a5568;
}

.refresh-btn:hover {
    background: #e2e8f0;
    transform: rotate(180deg);
}

.export-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

/* KPI Grid */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.kpi-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.kpi-card.user-kpi { border-color: #4299e1; }
.kpi-card.active-kpi { border-color: #48bb78; }
.kpi-card.task-kpi { border-color: #ed8936; }
.kpi-card.class-kpi { border-color: #9f7aea; }

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.kpi-card.user-kpi .kpi-icon { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }
.kpi-card.active-kpi .kpi-icon { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
.kpi-card.task-kpi .kpi-icon { background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); }
.kpi-card.class-kpi .kpi-icon { background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%); }

.kpi-content {
    flex: 1;
}

.kpi-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    line-height: 1;
    margin-bottom: 4px;
}

.kpi-label {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-trend {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
}

.trend-up {
    color: #48bb78;
    font-weight: 600;
}

.trend-percent {
    color: #667eea;
    font-weight: 600;
}

.trend-avg {
    color: #ed8936;
    font-weight: 600;
}

.trend-text {
    color: #a0aec0;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}

.chart-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.wide-card {
    grid-column: span 1;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.chart-header h3 {
    font-size: 1.25rem;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-controls {
    display: flex;
    gap: 8px;
}

.chart-btn {
    padding: 6px 16px;
    border: 2px solid #e2e8f0;
    background: white;
    color: #718096;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.chart-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

.chart-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.chart-container {
    height: 300px;
    position: relative;
}

.chart-legend {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #4a5568;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}

.tasks-color { background: #ed8936; }
.notes-color { background: #9f7aea; }
.classes-color { background: #667eea; }
.reminders-color { background: #48bb78; }

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}

.metrics-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.metrics-header {
    margin-bottom: 24px;
}

.metrics-header h3 {
    font-size: 1.25rem;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 10px;
}

.metric-group {
    margin-bottom: 32px;
}

.metric-group h4 {
    font-size: 1rem;
    color: #4a5568;
    margin-bottom: 16px;
    font-weight: 600;
}

/* Status Bars */
.status-bars {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f7fafc;
    border-radius: 12px;
}

.status-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    color: #2d3748;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.status-dot.pending { background: #ed8936; }
.status-dot.in-progress { background: #4299e1; }
.status-dot.completed { background: #48bb78; }

.status-value {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
}

.status-percent {
    color: #718096;
    font-size: 0.85rem;
    font-weight: 400;
}

/* Priority Cards */
.priority-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.priority-card {
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    transition: all 0.3s ease;
}

.priority-card:hover {
    transform: translateY(-3px);
}

.priority-card.low { background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%); }
.priority-card.medium { background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%); }
.priority-card.high { background: linear-gradient(135deg, #fed7e2 0%, #fbb6ce 100%); }

.priority-icon {
    font-size: 24px;
    margin-bottom: 12px;
}

.priority-card.low .priority-icon { color: #48bb78; }
.priority-card.medium .priority-icon { color: #f56565; }
.priority-card.high .priority-icon { color: #ed64a6; }

.priority-count {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
}

.priority-label {
    font-size: 0.9rem;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Retention Metrics */
.retention-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.retention-card {
    padding: 20px;
    border-radius: 15px;
    text-align: center;
}

.retention-card.retention { background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%); }
.retention-card.churn { background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); }

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
}

.metric-label {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 12px;
}

.metric-progress {
    height: 6px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    overflow: hidden;
}

.metric-progress .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 3px;
    transition: width 1s ease;
}

/* Device Metrics */
.device-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.device-card {
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    background: #f7fafc;
    transition: all 0.3s ease;
}

.device-card:hover {
    background: #edf2f7;
    transform: translateY(-3px);
}

.device-icon {
    font-size: 28px;
    margin-bottom: 12px;
    color: #667eea;
}

.device-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
}

.device-label {
    font-size: 0.9rem;
    color: #718096;
}

/* Activity Grid */
.activity-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.activity-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: #f7fafc;
    border-bottom: 2px solid #e2e8f0;
}

.activity-header h3 {
    font-size: 1.25rem;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.badge {
    padding: 6px 16px;
    background: #667eea;
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.activity-body {
    padding: 24px;
    max-height: 400px;
    overflow-y: auto;
}

/* User List */
.user-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.user-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f7fafc;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.user-item:hover {
    background: #edf2f7;
    transform: translateX(5px);
}

.user-rank {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
}

.user-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 2px;
}

.user-email {
    font-size: 0.85rem;
    color: #718096;
    margin-bottom: 4px;
}

.user-last-seen {
    font-size: 0.8rem;
    color: #a0aec0;
    display: flex;
    align-items: center;
    gap: 4px;
}

.user-metrics {
    display: flex;
    gap: 20px;
}

.user-metric {
    text-align: center;
}

.metric-count {
    display: block;
    font-weight: 700;
    color: #2d3748;
    font-size: 1.1rem;
}

.metric-label {
    display: block;
    font-size: 0.75rem;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-score {
    text-align: center;
    padding: 8px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

.score-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.score-label {
    font-size: 0.75rem;
    opacity: 0.9;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f7fafc;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #edf2f7;
    transform: translateX(5px);
}

.activity-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.activity-icon.success { background: #48bb78; }
.activity-icon.primary { background: #4299e1; }
.activity-icon.info { background: #667eea; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 4px;
}

.activity-description {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 8px;
}

.activity-meta {
    display: flex;
    gap: 16px;
    font-size: 0.8rem;
    color: #a0aec0;
}

.activity-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.activity-type {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.activity-type.success { background: #c6f6d5; color: #22543d; }
.activity-type.primary { background: #bee3f8; color: #2c5282; }
.activity-type.info { background: #c3dafe; color: #434190; }

/* Custom Scrollbar */
.activity-body::-webkit-scrollbar {
    width: 6px;
}

.activity-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.activity-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.activity-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Refresh button
    $('#refreshAnalytics').click(function() {
        window.location.reload();
    });

    // Initialize charts
    initializeCharts();
    
    // Chart control buttons
    $('.chart-btn').click(function() {
        $('.chart-btn').removeClass('active');
        $(this).addClass('active');
        updateUserGrowthChart($(this).data('chart'));
    });
});

function initializeCharts() {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const userGrowthChart = new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($analytics['userGrowth'])->pluck('date')->toArray()) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode(collect($analytics['userGrowth'])->pluck('new_users')->toArray()) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Content Distribution Chart
    const contentCtx = document.getElementById('contentDistributionChart').getContext('2d');
    const contentChart = new Chart(contentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tasks', 'Notes', 'Classes', 'Reminders'],
            datasets: [{
                data: [
                    {{ $analytics['overview']['totalTasks'] }},
                    {{ $analytics['overview']['totalNotes'] }},
                    {{ $analytics['overview']['totalClasses'] }},
                    {{ $analytics['overview']['totalReminders'] }}
                ],
                backgroundColor: ['#ed8936', '#9f7aea', '#667eea', '#48bb78'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Store chart instances for updates
    window.userGrowthChart = userGrowthChart;
    window.contentChart = contentChart;
}

function updateUserGrowthChart(type) {
    const chart = window.userGrowthChart;
    const growthData = {!! json_encode($analytics['userGrowth']) !!};
    
    let label, data, color;
    
    switch(type) {
        case 'growth-new':
            label = 'New Users';
            data = growthData.map(item => item.new_users);
            color = '#4e73df';
            break;
        case 'growth-total':
            label = 'Total Users';
            data = growthData.map(item => item.total_users);
            color = '#1cc88a';
            break;
        case 'growth-rate':
            label = 'Growth Rate (%)';
            data = growthData.map(item => item.growth_rate);
            color = '#f6c23e';
            break;
    }
    
    chart.data.datasets[0].label = label;
    chart.data.datasets[0].data = data;
    chart.data.datasets[0].borderColor = color;
    chart.data.datasets[0].backgroundColor = color + '20';
    chart.update();
}
</script>
@endpush
@endsection