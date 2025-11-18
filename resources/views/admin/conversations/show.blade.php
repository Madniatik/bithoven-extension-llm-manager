<x-default-layout>
    @section('title', 'Conversation Details')
    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.llm.conversations.show', $conversation) }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5">
        <!-- Conversation Info -->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Session Info</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-7">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Session ID:</span>
                            <span class="fw-bold text-gray-800 fs-7">{{ $conversation->session_id }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Configuration:</span>
                            <span class="badge badge-light-primary">{{ $conversation->configuration->name ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Status:</span>
                            @if($conversation->ended_at)
                                <span class="badge badge-light-secondary">Ended</span>
                            @elseif($conversation->expires_at && $conversation->expires_at->isPast())
                                <span class="badge badge-light-danger">Expired</span>
                            @else
                                <span class="badge badge-light-success">Active</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Messages:</span>
                            <span class="fw-bold text-gray-800">{{ $conversation->messages()->count() }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Total Tokens:</span>
                            <span class="fw-bold text-gray-800">{{ number_format($conversation->total_tokens) }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-600 fw-semibold fs-7 me-2">Total Cost:</span>
                            <span class="fw-bold text-gray-800">${{ number_format($conversation->total_cost, 6) }}</span>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Created:</div>
                        <div class="text-gray-800 fs-7">{{ $conversation->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>

                    @if($conversation->ended_at)
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Ended:</div>
                        <div class="text-gray-800 fs-7">{{ $conversation->ended_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    @endif

                    @if($conversation->expires_at)
                    <div class="mb-7">
                        <div class="text-gray-600 fw-semibold fs-7 mb-2">Expires:</div>
                        <div class="text-gray-800 fs-7">{{ $conversation->expires_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    @endif

                    <div class="separator separator-dashed my-5"></div>

                    <a href="{{ route('admin.llm.conversations.export', $conversation) }}" class="btn btn-light-primary w-100">
                        <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Export Conversation
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Messages</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">{{ $conversation->messages()->count() }} messages</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="scroll-y me-n5 pe-5 h-600px" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer" data-kt-scroll-wrappers="#kt_content" data-kt-scroll-offset="5px">
                        @foreach($conversation->messages as $message)
                        <div class="d-flex {{ $message->role === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-10">
                            <div class="d-flex flex-column align-items-{{ $message->role === 'user' ? 'end' : 'start' }}">
                                <div class="d-flex align-items-center mb-2">
                                    @if($message->role === 'assistant')
                                    <div class="symbol symbol-35px symbol-circle me-3">
                                        <span class="symbol-label bg-light-primary text-primary fw-bold">AI</span>
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <span class="text-gray-600 fw-semibold fs-8">
                                            {{ ucfirst($message->role) }}
                                        </span>
                                        <span class="text-gray-500 fw-semibold fs-8 ms-2">
                                            {{ $message->created_at->format('H:i:s') }}
                                        </span>
                                    </div>

                                    @if($message->role === 'user')
                                    <div class="symbol symbol-35px symbol-circle ms-3">
                                        <span class="symbol-label bg-light-success text-success fw-bold">U</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="p-5 rounded {{ $message->role === 'user' ? 'bg-light-success' : 'bg-light-primary' }}" style="max-width: 70%">
                                    <div class="text-gray-800 fw-semibold fs-6">
                                        {{ $message->content }}
                                    </div>
                                </div>

                                @if($message->token_count)
                                <div class="text-gray-500 fw-semibold fs-8 mt-1">
                                    Tokens: {{ number_format($message->token_count) }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
