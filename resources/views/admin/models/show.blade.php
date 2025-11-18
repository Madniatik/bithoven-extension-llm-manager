<x-default-layout>
    @section('title', $model->name)
    
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.models.show', $model) }}
    @endsection

    @include('llm-manager::admin.models.partials._header')

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!--begin::Col-->
        <div class="col-xl-8">
            <!--begin::Tabs Content-->
            <div class="tab-content" id="modelTabContent">
                <!--begin::Tab Overview-->
                @include('llm-manager::admin.models.partials._overview-tab')
                <!--end::Tab Overview-->
                
                <!--begin::Tab Edit-->
                @include('llm-manager::admin.models.partials._edit-tab')
                <!--end::Tab Edit-->
                
                <!--begin::Tab Advanced-->
                @include('llm-manager::admin.models.partials._advanced-tab')
                <!--end::Tab Advanced-->
            </div>
            <!--end::Tabs Content-->
        </div>
        <!--end::Col-->
        
        <!--begin::Sidebar-->
        <div class="col-xl-4">
            @include('llm-manager::admin.models.partials._sidebar')
        </div>
        <!--end::Sidebar-->
    </div>

    @push('scripts')
    <script>
        // ============================================
        // Monitor Panel - Sistema de logs reutilizable
        // ============================================
        const Monitor = {
            log(monitorId, message, type = 'info', timestamp = null) {
                const container = document.getElementById(monitorId);
                if (!container) {
                    console.error(`Monitor container with id "${monitorId}" not found`);
                    return;
                }

                if (container.querySelector('.text-center')) {
                    container.innerHTML = '';
                }

                const time = timestamp || new Date().toLocaleTimeString('es-ES');
                const colors = {
                    'info': 'text-gray-700',
                    'success': 'text-success',
                    'warning': 'text-warning',
                    'error': 'text-danger',
                    'debug': 'text-muted',
                };

                const entry = document.createElement('div');
                entry.className = `mb-1 ${colors[type] || colors.info}`;
                entry.innerHTML = `<span class="text-muted">[${time}]</span> ${message}`;

                container.appendChild(entry);
                container.scrollTop = container.scrollHeight;
            },

            clear(monitorId) {
                const container = document.getElementById(monitorId);
                if (!container) return;

                container.innerHTML = `
                    <div class="text-muted text-center py-5">
                        <i class="ki-duotone ki-information-2 fs-3x mb-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div>Waiting for events...</div>
                    </div>
                `;
            },

            success(monitorId, message) {
                this.log(monitorId, '✅ ' + message, 'success');
            },

            error(monitorId, message) {
                this.log(monitorId, '❌ ' + message, 'error');
            },

            warning(monitorId, message) {
                this.log(monitorId, '⚠️ ' + message, 'warning');
            },

            info(monitorId, message) {
                this.log(monitorId, 'ℹ️ ' + message, 'info');
            },

            debug(monitorId, message) {
                this.log(monitorId, '🔍 ' + message, 'debug');
            }
        };

        function clearMonitor(id) {
            Monitor.clear(id);
        }

        function copyMonitorLogs(id) {
            const container = document.getElementById(id);
            if (!container) {
                console.error(`Monitor container with id "${id}" not found`);
                return;
            }

            // Get all log entries
            const logEntries = container.querySelectorAll('div.mb-1');
            if (logEntries.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Logs',
                    text: 'No hay logs para copiar',
                    timer: 2000
                });
                return;
            }

            // Extract text from each log entry
            let logsText = '';
            logEntries.forEach(entry => {
                logsText += entry.innerText + '\n';
            });

            // Copy to clipboard
            navigator.clipboard.writeText(logsText).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: `${logEntries.length} logs copiados al portapapeles`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }).catch(err => {
                console.error('Error copying logs:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo copiar al portapapeles'
                });
            });
        }

        // ============================================
        // Tab switching without changing URL with localStorage persistence
        document.addEventListener('DOMContentLoaded', function() {
            // Only select tabs within the model tab content
            const tabButtons = document.querySelectorAll('#modelTabNav [data-bs-toggle="tab"]');
            const tabPanes = document.querySelectorAll('#modelTabContent .tab-pane');
            const storageKey = 'llm_model_{{ $model->id }}_active_tab';
            
            // Restore last active tab from localStorage
            const savedTab = localStorage.getItem(storageKey);
            if (savedTab) {
                const savedButton = document.querySelector(`#modelTabNav [href="${savedTab}"]`);
                if (savedButton) {
                    // Remove active from all model tabs
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => {
                        pane.classList.remove('active', 'show');
                    });
                    
                    // Activate saved tab
                    savedButton.classList.add('active');
                    const targetPane = document.querySelector(savedTab);
                    if (targetPane) {
                        targetPane.classList.add('active', 'show');
                    }
                }
            }
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all model tabs and content
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => {
                        pane.classList.remove('active', 'show');
                    });
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const targetId = this.getAttribute('href');
                    const targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.classList.add('active', 'show');
                    }
                    
                    // Save active tab to localStorage
                    localStorage.setItem(storageKey, targetId);
                });
            });
        });

        // Toggle model status
        function toggleModelStatus() {
            const modelId = {{ $model->id }};
            const url = "{{ route('admin.llm.configurations.toggle', ':id') }}".replace(':id', modelId);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to toggle model status');
            });
        }

        // Test connection
        function testModelConnection() {
            const modelId = {{ $model->id }};
            const url = "{{ route('admin.llm.configurations.test') }}";
            
            const testButton = document.getElementById('test-connection-btn');
            const originalText = testButton.innerHTML;
            testButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
            testButton.disabled = true;
            
            // Monitor logs
            Monitor.info('llm-monitor', '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            Monitor.info('llm-monitor', '🧪 Iniciando Test de Conexión');
            Monitor.debug('llm-monitor', `Provider: {{ ucfirst($model->provider) }}`);
            Monitor.debug('llm-monitor', `Model: {{ $model->model }}`);
            Monitor.info('llm-monitor', '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ configuration_id: modelId })
            })
            .then(response => {
                Monitor.debug('llm-monitor', '📥 Respuesta recibida del servidor');
                return response.json();
            })
            .then(data => {
                testButton.innerHTML = originalText;
                testButton.disabled = false;
                
                // Mostrar metadata
                if (data.metadata) {
                    Monitor.info('llm-monitor', '');
                    Monitor.info('llm-monitor', '📊 METADATA:');
                    Monitor.debug('llm-monitor', `   URL: ${data.metadata.url || 'N/A'}`);
                    Monitor.debug('llm-monitor', `   Method: ${data.metadata.method || 'N/A'}`);
                    Monitor.debug('llm-monitor', `   HTTP Code: ${data.metadata.http_code || 'N/A'}`);
                    Monitor.debug('llm-monitor', `   Request Time: ${data.metadata.request_time_ms || 0}ms`);
                    Monitor.debug('llm-monitor', `   Total Time: ${data.metadata.total_time_ms || 0}ms`);
                    Monitor.debug('llm-monitor', `   Request Size: ${data.metadata.request_size_bytes || 0} bytes`);
                    Monitor.debug('llm-monitor', `   Response Size: ${data.metadata.response_size_bytes || 0} bytes`);
                }

                // Mostrar request body si existe
                if (data.request_body) {
                    Monitor.info('llm-monitor', '');
                    Monitor.info('llm-monitor', '📤 REQUEST BODY:');
                    const requestLines = data.request_body.split('\n');
                    requestLines.forEach(line => {
                        if (line.trim()) Monitor.debug('llm-monitor', `   ${line}`);
                    });
                }

                // Mostrar response
                if (data.response) {
                    Monitor.info('llm-monitor', '');
                    Monitor.info('llm-monitor', '📥 RESPONSE:');
                    const responseLines = data.response.split('\n');
                    responseLines.slice(0, 10).forEach(line => {
                        if (line.trim()) Monitor.debug('llm-monitor', `   ${line}`);
                    });
                    if (responseLines.length > 10) {
                        Monitor.debug('llm-monitor', `   ... (${responseLines.length - 10} more lines)`);
                    }
                }

                Monitor.info('llm-monitor', '');
                if (data.success) {
                    Monitor.success('llm-monitor', data.message || 'Test de conexión exitoso');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Connection Successful',
                        html: `<div class="text-start">
                            <p>${data.message || 'Connection test passed'}</p>
                            ${data.metadata ? `<hr><small class="text-muted">
                                <strong>Request Time:</strong> ${data.metadata.request_time_ms}ms<br>
                                <strong>HTTP Code:</strong> ${data.metadata.http_code}
                            </small>` : ''}
                        </div>`,
                        timer: 5000
                    });
                } else {
                    Monitor.error('llm-monitor', data.message || 'Test de conexión falló');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Failed',
                        html: `<div class="text-start">
                            <p>${data.message || 'Connection test failed'}</p>
                            ${data.metadata ? `<hr><small class="text-muted">
                                <strong>HTTP Code:</strong> ${data.metadata.http_code || 'N/A'}
                            </small>` : ''}
                        </div>`
                    });
                }
                
                Monitor.info('llm-monitor', '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            })
            .catch(error => {
                testButton.innerHTML = originalText;
                testButton.disabled = false;
                
                Monitor.error('llm-monitor', 'Error de red: ' + error.message);
                Monitor.info('llm-monitor', '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Test Failed',
                    text: 'An error occurred during connection test'
                });
            });
        }

        // Delete model
        function deleteModel() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the LLM configuration!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('admin.llm.configurations.destroy', $model) }}";
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    
                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Save model (AJAX submit from edit tab)
        function saveModel() {
            const form = document.getElementById('edit-model-form');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const saveButton = document.getElementById('save-model-btn');
            const originalText = saveButton.innerHTML;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            saveButton.disabled = true;
            
            fetch("{{ route('admin.llm.models.update', $model) }}", {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: data.message || 'Model updated successfully',
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed',
                        text: data.message || 'Failed to update model'
                    });
                }
            })
            .catch(error => {
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
                
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving'
                });
            });
        }
    </script>
    @endpush
</x-default-layout>
