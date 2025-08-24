@section('title', 'Identity Verification (KYC)')


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Identity Verification') }}
        </h2>
    </x-slot>

    {{-- <body class="bg-gray-100 min-h-screen"> --}}
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Identity Verification</h1>

            <div id="kyc-status" class="mb-6">
                @if (auth()->user()->kyc_status === 'approved')
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        ✅ Your identity has been verified successfully!
                    </div>
                @elseif(auth()->user()->kyc_status === 'rejected')
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        ❌ Identity verification failed. Please try again.
                    </div>
                @elseif(auth()->user()->kyc_status === 'pending_review')
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                        ⏳ Your verification is under review. We'll notify you once complete.
                    </div>
                @else
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                        📋 Please complete your identity verification to continue using our platform.
                    </div>
                @endif
            </div>

            @if (auth()->user()->kyc_status !== 'approved')
                <div id="kyc-form">
                    <button id="start-kyc"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Start Identity Verification
                    </button>
                </div>

                <div id="kyc-pending" class="hidden">
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        ⏳ Verification session created. Complete it in the new tab and return here to check status.
                    </div>
                    <button id="check-status"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Check Verification Status
                    </button>
                </div>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 underline">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startKycBtn = document.getElementById('start-kyc');
            const checkStatusBtn = document.getElementById('check-status');
            const kycForm = document.getElementById('kyc-form');
            const kycPending = document.getElementById('kyc-pending');

            let currentInquiryId = null;

            if (startKycBtn) {
                startKycBtn.addEventListener('click', async function() {
                    this.disabled = true;
                    this.textContent = 'Creating verification session...';

                    try {
                        const response = await fetch('/persona/inquiry', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                reference_id: 'user_{{ auth()->id() }}'
                            })
                        });

                        const data = await response.json();

                        if (data.data && data.data.id) {
                            currentInquiryId = data.data.id;

                            const sessionUrl =
                                `https://withpersona.com/verify?inquiry-id=${data.data.id}`;
                            window.open(sessionUrl, '_blank');

                            kycForm.classList.add('hidden');
                            kycPending.classList.remove('hidden');
                        } else {
                            alert('Failed to create verification session. Please try again.');
                            this.disabled = false;
                            this.textContent = 'Start Identity Verification';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        this.disabled = false;
                        this.textContent = 'Start Identity Verification';
                    }
                });
            }

            if (checkStatusBtn) {
                checkStatusBtn.addEventListener('click', checkVerificationStatus);
            }

            async function checkVerificationStatus() {
                if (!currentInquiryId) {
                    alert('No verification session found');
                    return;
                }

                checkStatusBtn.disabled = true;
                checkStatusBtn.textContent = 'Checking status...';

                try {
                    const response = await fetch(`/persona/inquiry/${currentInquiryId}`);
                    const data = await response.json();

                    const status = data.data?.attributes?.status;

                    if (status && status !== 'created' && status !== 'started') {
                        window.location.reload();
                    } else {
                        checkStatusBtn.disabled = false;
                        checkStatusBtn.textContent = 'Check Verification Status';
                        alert(
                            'Verification still in progress. Please complete it in the Persona tab and try again.');
                    }
                } catch (error) {
                    console.error('Status check error:', error);
                    checkStatusBtn.disabled = false;
                    checkStatusBtn.textContent = 'Check Verification Status';
                    alert('Error checking status. Please try again.');
                }
            }
        });
    </script>

</x-app-layout>
