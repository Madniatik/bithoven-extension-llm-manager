{{-- 
    Activity History Component (Unified)
    
    Variants:
    - 'card': Full card with header and refresh button (Admin Test page)
    - 'table': Just the table, no wrapper (Monitor tab - header managed by monitor)
    
    Props:
    - $variant: string (default: 'table') - 'card' or 'table'
    - $sessionId: int|null (optional) - Filter by session
--}}

@php
    $variant = $variant ?? 'table';
    $sessionId = $sessionId ?? null;
@endphp

@if($variant === 'card')
    {{-- VARIANT: Card (Admin Test page) --}}
    <div id="activityHistoryTable" class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">Activity History</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-primary" onclick="ActivityHistory.refresh()">
                    <i class="ki-outline ki-arrows-circle fs-4"></i>
                    Refresh
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-50px">#</th>
                            <th class="min-w-100px">Time</th>
                            <th class="min-w-100px">Provider</th>
                            <th class="min-w-150px">Model</th>
                            <th class="min-w-80px text-end">Tokens</th>
                            <th class="min-w-100px text-end">Cost</th>
                            <th class="min-w-80px text-end">Duration</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-100px text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activityTableBody">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-10">
                                <i class="ki-outline ki-information-5 fs-3x mb-3"></i>
                                <p class="mb-0">Loading activity history...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    {{-- VARIANT: Table (Monitor tab - no card, no header) --}}
    <div id="activityHistoryTable" class="h-100">
        <div class="table-responsive h-100">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="px-10 min-w-50px">#</th>
                        <th class="px-10 min-w-100px">Time</th>
                        <th class="px-10 min-w-100px">Provider</th>
                        <th class="px-10 min-w-150px">Model</th>
                        <th class="px-10 min-w-80px text-end">Tokens</th>
                        <th class="px-10 min-w-100px text-end">Cost</th>
                        <th class="px-10 min-w-80px text-end">Duration</th>
                        <th class="px-10 min-w-100px">Status</th>
                        <th class="px-10 min-w-100px text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="activityTableBody">
                    <tr>
                        <td colspan="9" class="text-center text-muted py-10">
                            <i class="ki-outline ki-information-5 fs-3x mb-3"></i>
                            <p class="mb-0">Loading activity history...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

@push('scripts')
<script>
/**
 * Activity History Manager (Database-driven)
 * Replaces localStorage implementation
 */
const ActivityHistory = {
    endpoint: '{{ route("admin.llm.stream.activity-history") }}',
    currentLimit: 10,      // Current limit for pagination
    sessionId: null,       // Current session ID
    
    /**
     * Load activity history from database
     * @param {number|null} sessionId - Optional session filter
     * @param {number|null} limit - Number of items to load (uses currentLimit if null)
     */
    async load(sessionId = null, limit = null) {
        // Store sessionId for loadMore()
        if (sessionId !== null) {
            this.sessionId = sessionId;
        }
        
        // Use provided limit or currentLimit
        const loadLimit = limit !== null ? limit : this.currentLimit;
        
        try {
            const params = new URLSearchParams();
            if (this.sessionId) params.append('session_id', this.sessionId);
            params.append('limit', loadLimit);
            
            const response = await fetch(`${this.endpoint}?${params.toString()}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.render(result.data);
            } else {
                this.renderError('Failed to load activity history');
            }
        } catch (error) {
            console.error('Activity History load error:', error);
            this.renderError(error.message);
        }
    },
    
    /**
     * Load more activity history records
     * Increments limit by 10 and reloads
     */
    async loadMore() {
        // Increment limit
        this.currentLimit += 10;
        
        // Reload with new limit
        await this.load(null, this.currentLimit);
        
        // Show success toast
        if (window.Swal) {
            const theme = document.documentElement.getAttribute('data-bs-theme');
            const isDark = theme === 'dark';
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                background: isDark ? '#1e1e2d' : '#ffffff',
                color: isDark ? '#ffffff' : '#181c32',
                icon: 'success',
                title: `Loaded ${this.currentLimit} records`,
                iconColor: isDark ? '#ffffff' : '#181c32'
            });
        }
    },
    
    /**
     * Render activity history table
     * @param {Array} data - Activity history data
     */
    render(data) {
        const tbody = document.getElementById('activityTableBody');
        
        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-10">
                        <i class="ki-outline ki-information-5 fs-3x mb-3"></i>
                        <p class="mb-0">No activity yet. Start a streaming test above.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        data.forEach((activity, index) => {
            const date = new Date(activity.timestamp);
            const timeStr = date.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            const dateStr = date.toLocaleDateString('es-ES');
            
            const statusBadge = activity.status === 'success' 
                ? '<span class="badge badge-light-success">Completed</span>'
                : '<span class="badge badge-light-danger">Error</span>';
            
            const providerBadge = this.getProviderBadge(activity.provider);
            
            html += `
                <tr>
                    <td class="px-10">
                        <span class="text-muted fw-semibold">${data.length - index}</span>
                    </td>
                    <td class="px-10">
                        <div class="d-flex flex-column">
                            <span class="text-dark fw-bold">${timeStr}</span>
                            <span class="text-muted fs-7">${dateStr}</span>
                        </div>
                    </td>
                    <td class="px-10">${providerBadge}</td>
                    <td class="px-10">
                        <span class="text-dark fw-semibold">${activity.model}</span>
                    </td>
                    <td class="px-10 text-end">
                        <span class="badge badge-light">${activity.tokens.toLocaleString()}</span>
                    </td>
                    <td class="px-10 text-end">
                        <span class="text-dark fw-bold">$${activity.cost.toFixed(6)}</span>
                    </td>
                    <td class="px-10 text-end">
                        <span class="text-muted">${activity.duration}s</span>
                    </td>
                    <td class="px-10">${statusBadge}</td>
                    <td class="px-10 text-end">
                        <button type="button" 
                                class="btn btn-sm btn-light btn-active-light-primary" 
                                onclick="ActivityHistory.toggleDetails(${activity.log_id})"
                                title="Toggle details">
                            <i class="ki-outline ki-eye fs-5"></i>
                        </button>
                        ${activity.log_id ? `
                            <a href="/admin/llm/activity/${activity.log_id}" 
                               class="btn btn-sm btn-light btn-active-light-primary"
                               title="View full log">
                                <i class="ki-outline ki-document fs-5"></i>
                            </a>
                        ` : ''}
                    </td>
                </tr>
                <tr id="details-${activity.log_id}" style="display: none;">
                    <td colspan="9" class="bg-light-primary">
                        <div class="p-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Prompt:</strong>
                                    <p class="text-muted mb-0">${this.escapeHtml(activity.prompt)}...</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Response:</strong>
                                    <p class="text-muted mb-0">${this.escapeHtml(activity.response)}...</p>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    },
    
    /**
     * Render error message
     * @param {string} message - Error message
     */
    renderError(message) {
        const tbody = document.getElementById('activityTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-danger py-10">
                    <i class="ki-outline ki-cross-circle fs-3x mb-3"></i>
                    <p class="mb-0">Error loading activity history: ${this.escapeHtml(message)}</p>
                </td>
            </tr>
        `;
    },
    
    /**
     * Toggle details row
     * @param {number} logId - Log ID
     */
    toggleDetails(logId) {
        const detailsRow = document.getElementById(`details-${logId}`);
        if (detailsRow) {
            detailsRow.style.display = detailsRow.style.display === 'none' ? '' : 'none';
        }
    },
    
    /**
     * Refresh activity history
     */
    refresh() {
        this.load();
    },
    
    /**
     * Get provider badge HTML
     * @param {string} provider - Provider name
     * @return {string} Badge HTML
     */
    getProviderBadge(provider) {
        const badges = {
            'openai': '<span class="badge badge-light-success">OpenAI</span>',
            'ollama': '<span class="badge badge-light-info">Ollama</span>',
            'openrouter': '<span class="badge badge-light-primary">OpenRouter</span>',
            'anthropic': '<span class="badge badge-light-warning">Anthropic</span>',
        };
        
        return badges[provider.toLowerCase()] || `<span class="badge badge-light">${provider}</span>`;
    },
    
    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @return {string} Escaped text
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Auto-load activity history on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($sessionId) && $sessionId)
        ActivityHistory.load({{ $sessionId }});
    @else
        ActivityHistory.load();
    @endif
});

// Auto-refresh activity history when streaming completes
window.addEventListener('llm-streaming-completed', function(event) {
    console.log('🔄 Streaming completed, refreshing Activity History...', event.detail);
    @if(isset($sessionId) && $sessionId)
        ActivityHistory.load({{ $sessionId }});
    @else
        ActivityHistory.load();
    @endif
});
</script>
@endpush
