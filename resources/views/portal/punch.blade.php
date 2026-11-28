@extends('layouts.portal')
@section('title', 'Attendance Punch')

@section('content')
<div class="row justify-content-center py-3">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
            <!-- Header Card -->
            <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%);">
                <div class="badge bg-white text-success px-3 py-1 rounded-pill mb-2 fw-semibold">
                    <i class="bi bi-fingerprint me-1"></i> Staff Punch Terminal
                </div>
                <h3 class="fw-bold mb-1">Daily Attendance</h3>
                <div class="text-white-50 small">{{ now('Asia/Kolkata')->format('l, d F Y') }}</div>

                <!-- Live Clock -->
                <div class="my-3 py-2 px-4 rounded-pill d-inline-block bg-black bg-opacity-25 border border-white border-opacity-25 font-monospace fs-2 fw-bold text-white shadow-inner" id="livePunchClock">
                    --:--:--
                </div>

                @if($employee)
                    <div class="mt-2 pt-2 border-top border-white border-opacity-25">
                        <div class="fw-bold fs-5 text-white">{{ $employee->name }}</div>
                        <div class="small text-white-50">
                            <span class="badge bg-light text-dark me-1 font-monospace">{{ $employee->employee_code }}</span>
                            <span>{{ $employee->designation }} &middot; {{ $employee->branch?->name ?? 'Headquarters' }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-body p-4">

                <!-- GPS Status Pill -->
                <div class="mb-3 text-center">
                    <div id="gpsStatusBadge" class="badge bg-light text-secondary border px-3 py-2 fw-normal d-inline-flex align-items-center gap-1">
                        <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                        <span id="gpsStatusText">Detecting live GPS location...</span>
                    </div>
                </div>

                <!-- Status Banner -->
                <div class="text-center mb-4">
                    @if(!$hasCheckedIn)
                        @if($checkInWindow['allowed'] ?? false)
                            <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle text-success">
                                <i class="bi bi-check-circle-fill fs-5 me-1 align-middle"></i>
                                <span class="small fw-semibold">{{ $checkInWindow['message'] }} Click <strong>CHECK IN</strong> below to record attendance.</span>
                            </div>
                        @else
                            <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle text-dark">
                                <i class="bi bi-clock-history text-warning-emphasis fs-5 me-1 align-middle"></i>
                                <span class="small fw-semibold">{{ $checkInWindow['message'] }}</span>
                            </div>
                        @endif
                    @elseif($hasCheckedIn && !$hasCheckedOut)
                        <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle text-success">
                            <i class="bi bi-check-circle-fill fs-5 me-1 align-middle"></i>
                            <span class="small fw-semibold">Currently Active &middot; Checked in at <strong>{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>.</span>
                            @if($todayAttendance->check_in_latitude && $todayAttendance->check_in_longitude)
                                <div class="mt-2 pt-2 border-top border-success border-opacity-25 small">
                                    <a href="{{ $todayAttendance->checkInMapUrl() }}" target="_blank" class="text-success text-decoration-none fw-semibold">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        {{ $todayAttendance->check_in_address ?: 'Lat: ' . round($todayAttendance->check_in_latitude, 4) . ', Lng: ' . round($todayAttendance->check_in_longitude, 4) }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.75rem;"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-3 rounded-3 bg-info-subtle border border-info-subtle text-dark">
                            <i class="bi bi-shield-check text-success fs-5 me-1 align-middle"></i>
                            <span class="small fw-semibold">Today's attendance completed! Checked Out at <strong>{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</strong>.</span>
                            @if($todayAttendance->check_out_latitude && $todayAttendance->check_out_longitude)
                                <div class="mt-2 pt-2 border-top border-info border-opacity-25 small">
                                    <a href="{{ $todayAttendance->checkOutMapUrl() }}" target="_blank" class="text-primary text-decoration-none fw-semibold">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        Out Location: {{ $todayAttendance->check_out_address ?: 'Lat: ' . round($todayAttendance->check_out_latitude, 4) . ', Lng: ' . round($todayAttendance->check_out_longitude, 4) }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.75rem;"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Auto Logout Banner if employee did not logout before 7 PM -->
                @if($todayAttendance && $todayAttendance->is_auto_checkout)
                    <div class="alert alert-warning border border-warning shadow-sm rounded-3 p-3 mb-4 text-start">
                        <div class="d-flex align-items-center gap-2 fw-bold text-dark mb-1">
                            <i class="bi bi-clock-history text-warning fs-5"></i>
                            <span>Employee did not logout — System auto logged out at 07:00 PM</span>
                        </div>
                        <div class="small text-muted">
                            You did not punch out before 07:00 PM. The system automatically recorded your checkout at <strong>07:00 PM</strong> with status <strong>{{ $todayAttendance->status }}</strong> (Worked: {{ $todayAttendance->worked_hours }} hrs).
                        </div>
                    </div>
                @endif

                @php
                    $needsSelfie = (!$hasCheckedIn && ($checkInWindow['allowed'] ?? false)) || ($hasCheckedIn && !$hasCheckedOut);
                @endphp

                @if($needsSelfie)
                    <!-- Live Selfie Capture Box -->
                    <div class="card border border-primary-subtle bg-light bg-opacity-50 rounded-3 p-3 mb-4 text-center" id="selfieCaptureBox">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold small text-dark">
                                <i class="bi bi-camera-fill text-primary me-1"></i>Live Selfie Verification <span class="text-danger">*</span>
                            </span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" id="selfieStatusBadge">
                                Required before Punch
                            </span>
                        </div>

                        <div class="position-relative mx-auto rounded-3 overflow-hidden bg-dark mb-2" style="max-width: 280px; aspect-ratio: 4/3;">
                            <video id="selfieVideo" autoplay playsinline muted class="w-100 h-100 object-fit-cover d-none" style="transform: scaleX(-1);"></video>
                            <img id="selfiePreviewImg" src="" alt="Captured Selfie" class="w-100 h-100 object-fit-cover d-none">
                            <div id="cameraIdleMsg" class="position-absolute top-50 start-50 translate-middle text-white small px-3 w-100">
                                <i class="bi bi-camera-video-off fs-3 d-block mb-1 text-secondary"></i>
                                <span>{{ $hasCheckedIn ? 'Camera disabled while working. Enable when ready to Check Out.' : 'Camera is off. Click Enable Camera to take your selfie.' }}</span>
                            </div>
                            <div id="cameraFallbackMsg" class="position-absolute top-50 start-50 translate-middle text-white small px-2 d-none w-100">
                                <i class="bi bi-camera-video-off fs-4 d-block mb-1"></i>
                                <span>Live camera unavailable. Use Take Photo below.</span>
                            </div>
                        </div>

                        <canvas id="selfieCanvas" class="d-none"></canvas>

                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <button type="button" id="enableCameraBtn" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-semibold">
                                <i class="bi bi-camera-video me-1"></i>{{ $hasCheckedIn ? 'Enable Camera for Check-Out' : 'Enable Camera' }}
                            </button>
                            <button type="button" id="captureSelfieBtn" class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold d-none">
                                <i class="bi bi-camera me-1"></i>Capture Selfie
                            </button>
                            <button type="button" id="disableCameraBtn" class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-semibold d-none">
                                <i class="bi bi-camera-video-off me-1"></i>Disable Camera
                            </button>
                            <button type="button" id="retakeSelfieBtn" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-semibold d-none">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Retake
                            </button>
                            <label for="selfieFileFallback" id="selfieFallbackBtn" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-semibold mb-0 d-none cursor-pointer">
                                <i class="bi bi-upload me-1"></i>Take / Upload Selfie
                            </label>
                            <input type="file" id="selfieFileFallback" accept="image/*" capture="user" class="d-none">
                        </div>
                    </div>
                @endif

                <!-- Two Punch Buttons -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <!-- 1. CHECK IN BUTTON -->
                    @if(!$hasCheckedIn)
                        @if($checkInWindow['allowed'] ?? false)
                            {{-- ENABLED --}}
                            <form method="POST" action="{{ route('portal.punch_in') }}" id="punchInForm">
                                @csrf
                                <input type="hidden" name="latitude" class="geo-lat" value="{{ session('login_latitude') }}">
                                <input type="hidden" name="longitude" class="geo-lng" value="{{ session('login_longitude') }}">
                                <input type="hidden" name="address" class="geo-addr" value="{{ session('login_address') }}">
                                <input type="hidden" name="selfie_data" class="selfie-data-input">

                                <button type="submit" id="punchInSubmitBtn" class="btn btn-success btn-lg w-100 py-3 fw-bold fs-5 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" disabled title="Capture live selfie first">
                                    <i class="bi bi-box-arrow-in-right fs-4"></i>
                                    <span>CHECK IN NOW</span>
                                </button>
                            </form>
                        @else
                            {{-- DISABLED (Outside Time Window) --}}
                            <button type="button" class="btn btn-secondary btn-lg w-100 py-3 fw-bold rounded-3 opacity-75 d-flex align-items-center justify-content-center gap-2" disabled title="Check-in window is currently closed">
                                <i class="bi bi-lock-fill fs-4"></i>
                                <span>CHECK IN DISABLED (Closed)</span>
                            </button>
                        @endif
                    @else
                        {{-- DISABLED (Already Checked In) --}}
                        <button type="button" class="btn btn-outline-success btn-lg w-100 py-3 fw-bold rounded-3 opacity-75 d-flex align-items-center justify-content-center gap-2" disabled>
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                            <span>CHECKED IN ({{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }})</span>
                        </button>
                    @endif

                    <!-- 2. CHECK OUT BUTTON -->
                    @if(!$hasCheckedIn)
                        {{-- DISABLED (Cannot checkout before checkin) --}}
                        <button type="button" class="btn btn-light border text-muted btn-lg w-100 py-3 fw-semibold rounded-3 opacity-50 d-flex align-items-center justify-content-center gap-2" disabled title="Please check in first">
                            <i class="bi bi-lock-fill fs-5"></i>
                            <span>CHECK OUT (Check in first)</span>
                        </button>
                    @elseif($hasCheckedIn && !$hasCheckedOut)
                        @php
                            $inTime = \Carbon\Carbon::parse($todayAttendance->check_in, 'Asia/Kolkata');
                            $diffMinutes = max(0, $inTime->diffInMinutes(now('Asia/Kolkata')));
                            $elapsedH = floor($diffMinutes / 60);
                            $elapsedM = $diffMinutes % 60;
                        @endphp
                        <div class="text-center mb-1">
                            <span class="badge bg-light text-dark border px-3 py-2 small">
                                <i class="bi bi-stopwatch text-primary me-1"></i>
                                Working Duration: <strong>{{ $elapsedH }}h {{ $elapsedM }}m</strong>
                            </span>
                        </div>
                        {{-- ENABLED (Ready for checkout) --}}
                        <form method="POST" action="{{ route('portal.punch_out') }}" id="punchOutForm">
                            @csrf
                            <input type="hidden" name="latitude" class="geo-lat" value="{{ session('login_latitude') }}">
                            <input type="hidden" name="longitude" class="geo-lng" value="{{ session('login_longitude') }}">
                            <input type="hidden" name="address" class="geo-addr" value="{{ session('login_address') }}">
                            <input type="hidden" name="selfie_data" class="selfie-data-input">

                            <button type="submit" id="punchOutSubmitBtn" class="btn btn-warning text-dark btn-lg w-100 py-3 fw-bold fs-5 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none;" disabled title="Capture live selfie first">
                                <i class="bi bi-box-arrow-right fs-4 text-dark"></i>
                                <span>CHECK OUT (End Day)</span>
                            </button>
                        </form>
                    @else
                        {{-- DISABLED (Already Checked Out) --}}
                        @if($todayAttendance && $todayAttendance->is_auto_checkout)
                            <button type="button" class="btn btn-outline-warning text-dark btn-lg w-100 py-3 fw-bold rounded-3 opacity-90 d-flex align-items-center justify-content-center gap-2" disabled title="Employee did not logout — Auto logged out at 07:00 PM">
                                <i class="bi bi-clock-history fs-4 text-warning"></i>
                                <span>AUTO LOGGED OUT (07:00 PM)</span>
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-secondary btn-lg w-100 py-3 fw-bold rounded-3 opacity-75 d-flex align-items-center justify-content-center gap-2" disabled>
                                <i class="bi bi-check2-all fs-4 text-secondary"></i>
                                <span>CHECKED OUT ({{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }})</span>
                            </button>
                        @endif
                    @endif
                </div>

                <!-- Move to CRM (Visible ONLY to Admin & Super Admin) -->
                @if(auth()->user() && auth()->user()->hasAnyRole(['super-admin', 'admin']))
                    <div class="mb-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); border: none;">
                            <i class="bi bi-briefcase-fill fs-5"></i>
                            <span>Move to CRM (Log Leads & Visits)</span>
                            <i class="bi bi-arrow-right ms-auto"></i>
                        </a>
                    </div>
                @endif

                <!-- Go To Dashboard Button -->
                <div class="pt-3 border-top">
                    <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-success btn-lg w-100 py-2 fw-semibold rounded-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-speedometer2"></i>
                        <span>Go to My Attendance Dashboard</span>
                        <i class="bi bi-arrow-right ms-auto"></i>
                    </a>
                </div>

                <div class="text-center mt-3">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted small text-decoration-none">
                            <i class="bi bi-box-arrow-left me-1"></i> Sign Out / Switch User
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1. Live Digital Clock
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const clockEl = document.getElementById('livePunchClock');
        if (clockEl) {
            clockEl.textContent = timeStr;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 2. High-Accuracy Live Geolocation (100% Free - No API Key needed)
    (function () {
        const statusBadge = document.getElementById('gpsStatusBadge');
        const statusText = document.getElementById('gpsStatusText');

        if (!navigator.geolocation) {
            if (statusBadge) statusBadge.className = 'badge bg-warning-subtle text-warning border px-3 py-2';
            if (statusText) statusText.textContent = 'Geolocation not supported by this browser';
            return;
        }

        const geoOptions = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(
            async function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const accuracy = Math.round(pos.coords.accuracy);

                // Populate hidden inputs in both forms
                document.querySelectorAll('.geo-lat').forEach(el => el.value = lat);
                document.querySelectorAll('.geo-lng').forEach(el => el.value = lng);

                let displayAddr = `GPS Pin: ${lat.toFixed(4)}, ${lng.toFixed(4)} (±${accuracy}m)`;

                // Free Reverse Geocoding via OpenStreetMap Nominatim
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.display_name) {
                            // Extract meaningful short address (e.g., suburb, city, state)
                            const addr = data.address || {};
                            const shortName = [addr.suburb || addr.neighbourhood || addr.road, addr.city || addr.town || addr.county, addr.state].filter(Boolean).join(', ');
                            displayAddr = shortName || data.display_name;
                        }
                    }
                } catch (e) {
                    // Fallback to coordinates
                }

                document.querySelectorAll('.geo-addr').forEach(el => el.value = displayAddr);

                if (statusBadge) statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-2';
                if (statusText) statusText.innerHTML = `<i class="bi bi-geo-alt-fill text-danger me-1"></i> Live GPS: <strong>${displayAddr}</strong>`;
            },
            function (err) {
                if (statusBadge) statusBadge.className = 'badge bg-light text-muted border px-3 py-2';
                if (statusText) statusText.textContent = 'GPS permission optional: Click Allow if prompted';
            },
            geoOptions
        );
    })();

    // 3. Live Selfie Camera Capture & Compression (On-Demand — disabled after punch-in until user enables for checkout)
    (function () {
        const box = document.getElementById('selfieCaptureBox');
        if (!box) return;

        const video = document.getElementById('selfieVideo');
        const canvas = document.getElementById('selfieCanvas');
        const previewImg = document.getElementById('selfiePreviewImg');
        const idleMsg = document.getElementById('cameraIdleMsg');
        const enableBtn = document.getElementById('enableCameraBtn');
        const disableBtn = document.getElementById('disableCameraBtn');
        const captureBtn = document.getElementById('captureSelfieBtn');
        const retakeBtn = document.getElementById('retakeSelfieBtn');
        const fallbackBtn = document.getElementById('selfieFallbackBtn');
        const fallbackInput = document.getElementById('selfieFileFallback');
        const fallbackMsg = document.getElementById('cameraFallbackMsg');
        const statusBadge = document.getElementById('selfieStatusBadge');
        const submitBtns = [document.getElementById('punchInSubmitBtn'), document.getElementById('punchOutSubmitBtn')].filter(Boolean);

        let stream = null;

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            video.srcObject = null;
        }

        function setCameraIdle() {
            stopCamera();
            document.querySelectorAll('.selfie-data-input').forEach(el => el.value = '');
            video.classList.add('d-none');
            previewImg.classList.add('d-none');
            fallbackMsg.classList.add('d-none');
            idleMsg.classList.remove('d-none');
            enableBtn.classList.remove('d-none');
            captureBtn.classList.add('d-none');
            disableBtn.classList.add('d-none');
            retakeBtn.classList.add('d-none');
            statusBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            statusBadge.textContent = 'Required before Punch';
            submitBtns.forEach(btn => btn.disabled = true);
        }

        function enableFallback() {
            stopCamera();
            video.classList.add('d-none');
            idleMsg.classList.add('d-none');
            enableBtn.classList.add('d-none');
            captureBtn.classList.add('d-none');
            disableBtn.classList.add('d-none');
            fallbackMsg.classList.remove('d-none');
            fallbackBtn.classList.remove('d-none');
        }

        function startCamera() {
            if (!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)) {
                enableFallback();
                return;
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: false })
                .then(s => {
                    stream = s;
                    video.srcObject = s;
                    idleMsg.classList.add('d-none');
                    fallbackMsg.classList.add('d-none');
                    previewImg.classList.add('d-none');
                    video.classList.remove('d-none');
                    enableBtn.classList.add('d-none');
                    captureBtn.classList.remove('d-none');
                    disableBtn.classList.remove('d-none');
                    retakeBtn.classList.add('d-none');
                })
                .catch(() => enableFallback());
        }

        function setSelfieReady(dataUrl) {
            stopCamera(); // Immediately turn off camera once selfie is captured
            document.querySelectorAll('.selfie-data-input').forEach(el => el.value = dataUrl);
            previewImg.src = dataUrl;
            previewImg.classList.remove('d-none');
            video.classList.add('d-none');
            idleMsg.classList.add('d-none');
            fallbackMsg.classList.add('d-none');
            enableBtn.classList.add('d-none');
            captureBtn.classList.add('d-none');
            disableBtn.classList.add('d-none');
            retakeBtn.classList.remove('d-none');
            statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle';
            statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Selfie Ready';
            submitBtns.forEach(btn => btn.disabled = false);
        }

        enableBtn.addEventListener('click', startCamera);
        disableBtn.addEventListener('click', setCameraIdle);

        captureBtn.addEventListener('click', function () {
            if (!video.videoWidth) return;
            const maxW = 640;
            const scale = Math.min(1, maxW / video.videoWidth);
            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            setSelfieReady(canvas.toDataURL('image/jpeg', 0.78));
        });

        retakeBtn.addEventListener('click', function () {
            document.querySelectorAll('.selfie-data-input').forEach(el => el.value = '');
            submitBtns.forEach(btn => btn.disabled = true);
            statusBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            statusBadge.textContent = 'Required before Punch';
            startCamera();
        });

        fallbackInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                const img = new Image();
                img.onload = function () {
                    const maxW = 640;
                    const scale = Math.min(1, maxW / img.width);
                    canvas.width = Math.round(img.width * scale);
                    canvas.height = Math.round(img.height * scale);
                    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                    setSelfieReady(canvas.toDataURL('image/jpeg', 0.78));
                };
                img.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });

        window.addEventListener('beforeunload', stopCamera);
    })();
</script>
@endpush
