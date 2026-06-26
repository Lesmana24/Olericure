@extends('layouts.main')

@section('title', $title)
@push('page-styles')
    {{-- flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v=1.4">
@endpush

@section('content')
    {{-- ================= KONTEN HTML ================= --}}
    <div class="navbar">
        <form action="{{ route('logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="logout">Log Out</button>
        </form>
        <div class="nav-icons">
            <div class="info">
                <a href="#"><img src="{{ asset('image/info.png') }}" alt="Info" /></a>
            </div>
            <div class="notif">
                <a href="{{ url('/notification') }}"><img src="{{ asset('image/notif.svg') }}" alt="Notifikasi" /></a>
            </div>
        </div>
    </div>

    <h1>Halo,<br>Agro Squad</h1>

    <div class="card">
        <div class="logo-circle">
            <a href="{{ url('/ai') }}">
                <img src="{{ asset('image/logo.png') }}" alt="Logo" />
            </a>
        </div>
        <div class="realtime-header">
            <div class="rt-box left">
                <h3><span id="live-suhu">--</span>°</h3>
                <p>Suhu Saat Ini</p>
            </div>

            <div class="rt-box right">
                <h3><span id="live-lembab">--</span>%</h3>
                <p>Kelembapan Saat Ini</p>
            </div>
        </div>

        <div class="mqtt-status-container">
            Status MQTT: <span id="mqtt-status" style="font-weight: bold; color: orange;">Menghubungkan...</span>
        </div>

        <!-- Blok Pertama: Batas Ambang Sensor -->
        <div class="sensor-control-section">
            <h3>Batas Ambang Sensor</h3>
            <div class="status">
                <div class="status-box">
                    <button type="button" class="icon-action" aria-label="Setting Suhu"
                        style="border:none;background:none;padding:0;" onclick="window.openThresholdModal('Suhu', 24, '°')">
                        <svg class="settings-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.49l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z" />
                        </svg>
                    </button>

                    <h2 id="display-suhu">{{ $batasSuhu }}°</h2>
                    <p>Batas Ambang Suhu</p>
                </div>

                <div class="status-box">
                    <button type="button" class="icon-action" aria-label="Setting Kelembapan"
                        style="border:none;background:none;padding:0;"
                        onclick="window.openThresholdModal('Kelembapan', 60, '%')">
                        <svg class="settings-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22L16.22,9l2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.49l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z" />
                        </svg>
                    </button>

                    <h2 id="display-kelembapan">{{ $batasLembab }}%</h2>
                    <p>Batas Ambang Kelembapan</p>
                </div>
            </div>
        </div>

        <!-- Blok Kedua: Durasi Penyiraman -->
        <div class="sensor-control-section sub-section">
            <h3>Durasi Penyiraman</h3>
            <div class="status">
                <div class="status-box">
                    <button type="button" class="icon-action" aria-label="Setting Durasi Suhu"
                        style="border:none;background:none;padding:0;"
                        onclick="window.openThresholdModal('Durasi Suhu', 60, 's')">
                        <svg class="settings-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.49l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z" />
                        </svg>
                    </button>

                    <h2 id="display-durasi-suhu">{{ $durasiSuhu }}s</h2>
                    <p>Durasi Kritis</p>
                </div>

                <div class="status-box">
                    <button type="button" class="icon-action" aria-label="Setting Durasi Jadwal"
                        style="border:none;background:none;padding:0;"
                        onclick="window.openThresholdModal('Durasi Jadwal', 60, 's')">
                        <svg class="settings-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.49l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z" />
                        </svg>
                    </button>

                    <h2 id="display-durasi-jadwal">{{ $durasiJadwal }}s</h2>
                    <p>Durasi Jadwal Otomatis</p>
                </div>
            </div>
        </div>

        <hr class="section-divider">

        <div class="schedule">
            <h3>Jadwal Otomatis</h3>

            <div class="day-selector">
                <div class="day-item">
                    <input type="checkbox" id="hari-0" value="0"
                        {{ $arrayHari[0] == '1' ? 'checked' : '' }}><br>Min
                </div>
                <div class="day-item">
                    <input type="checkbox" id="hari-1" value="1"
                        {{ $arrayHari[1] == '1' ? 'checked' : '' }}><br>Sen
                </div>
                <div class="day-item">
                    <input type="checkbox" id="hari-2" value="2"
                        {{ $arrayHari[2] == '1' ? 'checked' : '' }}><br>Sel
                </div>
                <div class="day-item">
                    <input type="checkbox" id="hari-3" value="3"
                        {{ $arrayHari[3] == '1' ? 'checked' : '' }}><br>Rab
                </div>
                <div class="day-item">
                    <input type="checkbox" id="hari-4" value="4"
                        {{ $arrayHari[4] == '1' ? 'checked' : '' }}><br>Kam
                </div>
                <div class="day-item">
                    <input type="checkbox" id="hari-5" value="5"
                        {{ $arrayHari[5] == '1' ? 'checked' : '' }}><br>Jum
                </div>
                <div class="day-item">
                    <input type="checkbox" id="hari-6" value="6"
                        {{ $arrayHari[6] == '1' ? 'checked' : '' }}><br>Sab
                </div>
            </div>

            <div class="time-container">
                <input type="text" id="customTime" placeholder="10:30" value="{{ $jadwalJam }}">
                <img class="time-icon" src="{{ asset('image/jam.svg') }}" alt="jam" />
            </div>

            <button class="btn-set" onclick="kirimJadwal()">Simpan Jadwal</button>
        </div>

        {{-- <img class="leaf left" src="{{ asset('image/pohon.png') }}" alt="leaf" /> --}}
        <img class="leaf right" src="{{ asset('image/pohon2.png') }}" alt="leaf" />
    </div>

    {{-- Custom Toast Notification --}}
    <div id="customToast" class="custom-toast">
        <div class="toast-icon" id="toastIcon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </div>
        <div class="toast-content">
            <span class="toast-title" id="toastTitle">Berhasil</span>
            <span class="toast-message" id="toastMessage">Data berhasil disimpan!</span>
        </div>
        <button class="toast-close" onclick="hideToast()" aria-label="Tutup notifikasi">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
        <div class="toast-progress" id="toastProgress"></div>
    </div>

    <div id="thresholdModal" class="modal-overlay" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalTitle">Suhu</h3>
                <button type="button" class="close-btn" aria-label="Tutup Panel" onclick="window.closeModal()"
                    style="border:none;background:none;padding:0;cursor:pointer;">&times;</button>
            </div>

            <hr class="modal-line">

            <div class="modal-body">
                <div class="counter-container">
                    <button class="btn-counter" onclick="window.updateValue(-1)">—</button>
                    <div class="value-display">
                        <input type="tel" id="modalValue" value="24" inputmode="numeric" pattern="[0-9]*"
                            autocomplete="off" />
                        <span id="modalUnit" class="unit-dot"></span>
                    </div>
                    <button class="btn-counter" onclick="window.updateValue(1)">+</button>
                </div>
            </div>

            <form id="thresholdForm" action="#" method="POST">
                @csrf
                <input type="hidden" name="type" id="inputType">
                <input type="hidden" name="value" id="inputValue">
                <button type="button" class="btn-set modal-btn" onclick="window.saveThreshold()">Set Batas</button>
            </form>
        </div>
    </div>

    {{-- Section informasi --}}
    <div id="infoBackdrop" class="custom-backdrop"></div>

    <div id="infoDrawer" class="custom-drawer">
        <div class="drawer-header">
            <div class="drawer-header-title">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0-3.332-.477-4.5-1.253">
                    </path>
                </svg>
                Panduan Sistem
            </div>
            <button id="closeDrawerBtn" class="drawer-close-btn">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div class="drawer-body">
            <!-- Section 1: Monitoring & Kontrol IoT -->
            <div class="guide-section">
                <div class="guide-title">
                    <div class="icon-box">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                            </path>
                        </svg>
                    </div>
                    Monitoring & Kontrol IoT
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <div><b>Batas Ambang Sensor (Suhu & Lembab):</b> Berfungsi sebagai pemicu (trigger) otomatis. Jika
                        suhu/kelembapan aktual melewati batas ini, sistem akan menyalakan pompa air.</div>
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div><b>Durasi Penyiraman:</b> Mengatur berapa lama pompa menyala saat suhu kritis tercapai atau saat
                        jadwal rutin tiba.</div>
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <div><b>Jadwal Otomatis:</b> Mengatur hari dan jam spesifik untuk menyiram tanaman secara rutin, tanpa
                        bergantung pada suhu.</div>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Section 2: Deteksi Penyakit AI -->
            <div class="guide-section">
                <div class="guide-title">
                    <div class="icon-box">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    Deteksi Penyakit AI (Smart Botanist)
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                    <div><b>Akses Fitur:</b> Klik logo Agro Squad yang melayang di atas untuk masuk ke halaman scan daun.
                    </div>
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <div><b>Upload Foto:</b> Unggah foto daun untuk dianalisis oleh AI (Cabai, Tomat, Terong, atau Melon).
                    </div>
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                        </path>
                    </svg>
                    <div><b>Chat Botanist:</b> Setelah hasil scan keluar, gunakan fitur ini untuk konsultasi penanganan dan
                        pengobatan penyakit.</div>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Section 3: Status Perangkat -->
            <div class="guide-section">
                <div class="guide-title">
                    <div class="icon-box">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                            </path>
                        </svg>
                    </div>
                    Status Perangkat
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>Indikator status MQTT di bagian atas menunjukkan koneksi perangkat fisik IoT Anda ke sistem.</div>
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#16a34a" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div><b style="color: #16a34a;">Terhubung (Hijau):</b> Perangkat IoT menyala dan terkoneksi dengan
                        internet.</div>
                </div>
                <div class="guide-item">
                    <svg width="20" height="20" stroke="#dc2626" fill="none" viewBox="0 0 24 24"
                        style="flex-shrink:0; margin-top:2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    <div><b style="color: #dc2626;">OFFLINE (Merah):</b> Perangkat tidak merespons, kemungkinan alat mati
                        atau terputus dari jaringan WiFi.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backdrop = document.getElementById('infoBackdrop');
            const drawer = document.getElementById('infoDrawer');
            const btnClose = document.getElementById('closeDrawerBtn');

            // Targetkan tombol Info (A icon) yang ada di navbar
            const infoBtn = document.querySelector('.info a');

            function openDrawer(e) {
                e.preventDefault(); // Mencegah pindah URL / scroll ke atas
                backdrop.classList.add('show');
                drawer.classList.add('open');
                document.body.style.overflow = 'hidden'; // Kunci scroll layar utama
            }

            function closeDrawer() {
                backdrop.classList.remove('show');
                drawer.classList.remove('open');
                document.body.style.overflow = ''; // Buka scroll
            }

            if (infoBtn) infoBtn.addEventListener('click', openDrawer);
            if (btnClose) btnClose.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);
        });
    </script>

    {{-- ================= SCRIPT MQTT (PAHO) ================= --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js" type="text/javascript"></script>

    <script>
        // --- 1. Konfigurasi Koneksi ---
        const mqtt_broker = "broker.emqx.io";
        const mqtt_port = 8084;
        const client_id = "Web_AgroSquad_" + Math.random().toString(16).substr(2, 8);

        const topic_suhu = "AgroSquad/monitoring/suhu";
        const topic_lembab = "AgroSquad/monitoring/lembab";

        // VARIABEL TIMER (Wajib di luar fungsi)
        let watchdogTimer = null;

        // --- 2. Inisialisasi Client ---
        const client = new Paho.MQTT.Client(mqtt_broker, mqtt_port, client_id);

        client.onConnectionLost = onConnectionLost;
        client.onMessageArrived = onMessageArrived;

        // --- 3. Mulai Koneksi ---
        console.log("Menghubungkan ke MQTT...");
        client.connect({
            useSSL: true,
            onSuccess: onConnect,
            onFailure: onFailure
        });

        // ================= FUNGSI UTAMA =================

        function onConnect() {
            console.log("MQTT Terhubung ke Broker!");

            document.getElementById("mqtt-status").innerText = "Menunggu Data Alat...";
            document.getElementById("mqtt-status").style.color = "orange";

            // Subscribe ke topik sensor
            client.subscribe(topic_suhu);
            client.subscribe(topic_lembab);

            // Subscribe ke topik kontrol (agar sinkron real-time dengan Mobile)
            client.subscribe("AgroSquad/kontrol/#");

            let dbSuhu = document.getElementById("display-suhu").innerText.replace('°', '');
            let dbLembab = document.getElementById("display-kelembapan").innerText.replace('%', '');
            let dbDurasiSuhu = document.getElementById("display-durasi-suhu").innerText.replace('s', '');
            let dbDurasiJadwal = document.getElementById("display-durasi-jadwal").innerText.replace('s', '');

            console.log("Sinkronisasi Awal: Mengirim data DB ke Alat (" + dbSuhu + ", " + dbLembab + ", " + dbDurasiSuhu +
                ", " + dbDurasiJadwal + ")");

            if (dbSuhu) {
                let msg = new Paho.MQTT.Message(dbSuhu);
                msg.destinationName = "AgroSquad/kontrol/batas_suhu";
                msg.retained = true;
                client.send(msg);
            }
            if (dbLembab) {
                let msg = new Paho.MQTT.Message(dbLembab);
                msg.destinationName = "AgroSquad/kontrol/batas_lembab";
                msg.retained = true;
                client.send(msg);
            }
            if (dbDurasiSuhu) {
                let msg = new Paho.MQTT.Message(dbDurasiSuhu);
                msg.destinationName = "AgroSquad/kontrol/durasi_suhu";
                msg.retained = true;
                client.send(msg);
            }
            if (dbDurasiJadwal) {
                let msg = new Paho.MQTT.Message(dbDurasiJadwal);
                msg.destinationName = "AgroSquad/kontrol/durasi_jadwal";
                msg.retained = true;
                client.send(msg);
            }

            // Sinkronisasi Jadwal Mingguan (agar retained lama di broker ter-overwrite)
            let hariArr = [];
            for (let i = 0; i <= 6; i++) {
                let cb = document.getElementById("hari-" + i);
                hariArr.push(cb && cb.checked ? "1" : "0");
            }
            let dbJam = document.getElementById("customTime").value || "00:00";
            let jadwalPayload = hariArr.join(",") + "#" + dbJam;

            let msgJadwal = new Paho.MQTT.Message(jadwalPayload);
            msgJadwal.destinationName = "AgroSquad/kontrol/jadwal_mingguan";
            msgJadwal.retained = true;
            client.send(msgJadwal);

            console.log("Sinkronisasi Jadwal: " + jadwalPayload);
        }

        function onFailure(responseObject) {
            console.log("Gagal Konek: " + responseObject.errorMessage);
            document.getElementById("mqtt-status").innerText = "Gagal Konek Server";
            document.getElementById("mqtt-status").style.color = "red";
        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log("Koneksi Putus: " + responseObject.errorMessage);
                document.getElementById("mqtt-status").innerText = "Koneksi Server Putus";
                document.getElementById("mqtt-status").style.color = "red";
            }
        }

        // --- SAAT DATA MASUK DARI ALAT ---
        function onMessageArrived(message) {
            console.log("Pesan Masuk: " + message.payloadString);

            // 1. PANGGIL FUNGSI DETEKSI ONLINE (Reset Timer)
            resetWatchdog();

            // 2. Update Tampilan Angka (Sensor dari ESP32)
            if (message.destinationName === topic_suhu) {
                document.getElementById("live-suhu").innerText = message.payloadString;
            } else if (message.destinationName === topic_lembab) {
                document.getElementById("live-lembab").innerText = message.payloadString;
            }

            // 3. Sinkronisasi Kontrol dari Mobile (Real-time)
            else if (message.destinationName === "AgroSquad/kontrol/batas_suhu") {
                document.getElementById("display-suhu").innerText = message.payloadString + "°";
            } else if (message.destinationName === "AgroSquad/kontrol/batas_lembab") {
                document.getElementById("display-kelembapan").innerText = message.payloadString + "%";
            } else if (message.destinationName === "AgroSquad/kontrol/durasi_suhu") {
                document.getElementById("display-durasi-suhu").innerText = message.payloadString + "s";
            } else if (message.destinationName === "AgroSquad/kontrol/durasi_jadwal") {
                document.getElementById("display-durasi-jadwal").innerText = message.payloadString + "s";
            }

            // 4. Sinkronisasi Jadwal Mingguan dari Mobile
            else if (message.destinationName === "AgroSquad/kontrol/jadwal_mingguan") {
                // Payload format: "1,1,0,0,0,0,0#15:30"
                let parts = message.payloadString.split("#");
                let jamBaru = parts[1]; // "15:30"
                let arrayHari = parts[0].split(","); // ["1","1","0","0","0","0","0"]

                // Update input waktu
                document.getElementById("customTime").value = jamBaru;

                // Update checkbox hari (id: hari-0 s/d hari-6)
                for (let i = 0; i <= 6; i++) {
                    document.getElementById("hari-" + i).checked = (arrayHari[i] === "1");
                }

                console.log("Jadwal disinkronkan dari Mobile: " + message.payloadString);
            }
        }

        // --- LOGIKA DETEKSI OFFLINE (WATCHDOG) ---
        function resetWatchdog() {
            const statusElem = document.getElementById("mqtt-status");

            // A. Karena fungsi ini dipanggil, berarti BARU SAJA ada data masuk -> ALAT ONLINE
            statusElem.innerText = "Perangkat ONLINE";
            statusElem.style.color = "green"; // Hijau
            statusElem.style.fontWeight = "bold";

            // B. Hapus timer lama (jika ada) agar tidak menghitung mundur dobel
            if (watchdogTimer) {
                clearTimeout(watchdogTimer);
            }

            // C. Pasang Timer Baru (Bom waktu 10 detik)
            watchdogTimer = setTimeout(function() {
                // Jika kode di dalam sini jalan, berarti sudah 10 detik HENING (gak ada data)
                statusElem.innerText = "Perangkat OFFLINE";
                statusElem.style.color = "red"; // Merah

                // Ubah angka jadi strip
                document.getElementById("live-suhu").innerText = "--";
                document.getElementById("live-lembab").innerText = "--";

                console.log("Timeout! Perangkat dianggap mati.");
            }, 10000); // 10 detik
        }
    </script>
    {{-- ================= SCRIPT LANGSUNG DI SINI ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Definisi Fungsi SECARA GLOBAL (window.)
        // Definisi Fungsi SECARA GLOBAL (window.)
        window.openThresholdModal = function(type, value, unit) { // 'value' disini sudah tidak kita pakai
            console.log('Klik terdeteksi:', type);

            var modal = document.getElementById('thresholdModal');
            var title = document.getElementById('modalTitle');
            var valSpan = document.getElementById('modalValue');

            // Update data global
            window.currentType = type;

            // === BAGIAN INI YANG BERUBAH ===
            // Ambil angka aktual dari teks di layar (yang sudah dimuat dari database)
            if (type === 'Suhu') {
                // Ambil teks dari id="display-suhu" (misal "35°")
                let valText = document.getElementById('display-suhu').innerText;
                // parseInt otomatis membuang karakter non-angka seperti "°"
                window.currentValue = parseInt(valText);
            } else if (type === 'Kelembapan') {
                // Ambil teks dari id="display-kelembapan" (misal "60%")
                let valText = document.getElementById('display-kelembapan').innerText;
                window.currentValue = parseInt(valText);
            } else if (type === 'Durasi Suhu') {
                let valText = document.getElementById('display-durasi-suhu').innerText;
                window.currentValue = parseInt(valText);
            } else if (type === 'Durasi Jadwal') {
                let valText = document.getElementById('display-durasi-jadwal').innerText;
                window.currentValue = parseInt(valText);
            }
            // ===============================

            title.innerText = type;
            valSpan.value = window.currentValue;

            // Tampilkan
            modal.style.display = 'flex';
        };

        window.closeModal = function() {
            document.getElementById('thresholdModal').style.display = 'none';
        };

        window.updateValue = function(change) {
            // Sync dari input dulu (kalau user sudah ketik manual)
            let current = parseInt(document.getElementById('modalValue').value) || 0;
            current += change;
            if (current < 0) current = 0; // Tidak boleh negatif
            window.currentValue = current;
            document.getElementById('modalValue').value = window.currentValue;
        };

        window.saveThreshold = function() {
            // 0. Sync nilai dari input (kalau user ketik manual)
            let inputVal = parseInt(document.getElementById('modalValue').value);
            if (isNaN(inputVal) || inputVal < 0) {
                showToast("Input Tidak Valid", "Masukkan angka yang benar!", "warning");
                return;
            }
            window.currentValue = inputVal;

            // 1. Validasi Koneksi MQTT
            if (!client.isConnected()) {
                alert("MQTT belum terhubung! Tunggu sebentar...");
                return;
            }

            let topic = "";
            let dbKey = ""; // Key untuk database
            let messagePayload = String(window.currentValue);

            if (window.currentType === 'Suhu') {
                document.getElementById('display-suhu').innerText = window.currentValue + '°';
                topic = "AgroSquad/kontrol/batas_suhu";
                dbKey = "batas_suhu"; // Sesuai database
            } else if (window.currentType === 'Kelembapan') {
                document.getElementById('display-kelembapan').innerText = window.currentValue + '%';
                topic = "AgroSquad/kontrol/batas_lembab";
                dbKey = "batas_lembab"; // Sesuai database
            } else if (window.currentType === 'Durasi Suhu') {
                document.getElementById('display-durasi-suhu').innerText = window.currentValue + 's';
                topic = "AgroSquad/kontrol/durasi_suhu";
                dbKey = "durasi_suhu"; // Sesuai database
            } else if (window.currentType === 'Durasi Jadwal') {
                document.getElementById('display-durasi-jadwal').innerText = window.currentValue + 's';
                topic = "AgroSquad/kontrol/durasi_jadwal";
                dbKey = "durasi_jadwal"; // Sesuai database
            }

            // 2. KIRIM KE MQTT (Untuk Alat)
            var message = new Paho.MQTT.Message(messagePayload);
            message.destinationName = topic;
            message.qos = 2; // Pastikan terkirim (Exactly Once)
            message.retained = true; // Simpan pesan terakhir di broker
            client.send(message);

            // 3. KIRIM KE DATABASE (Untuk Server Laravel) -- BARU!!
            fetch('/update-setting', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // Ambil token CSRF dari head
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        key: dbKey,
                        value: window.currentValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Sukses update database:", data);
                })
                .catch((error) => {
                    console.error('Error update database:', error);
                });

            console.log("Mengirim batas baru: " + messagePayload + " ke " + topic);
            window.closeModal();
        };

        // --- FUNGSI KIRIM JADWAL MINGGUAN ---
        window.kirimJadwal = function() {
            // 1. Validasi Koneksi
            if (!client.isConnected()) {
                showToast("Koneksi Terputus", "MQTT belum terhubung! Tunggu sebentar...", "warning");
                return;
            }

            // 2. Ambil Status Centang Hari (Minggu=0 s.d Sabtu=6)
            let polaHari = [];
            let adaHariDipilih = false;
            for (let i = 0; i <= 6; i++) {
                let isChecked = document.getElementById("hari-" + i).checked;
                if (isChecked) adaHariDipilih = true;
                polaHari.push(isChecked ? "1" : "0");
            }

            // 3. Ambil Jam (wajib hanya jika ada hari yang dipilih)
            var waktu = document.getElementById("customTime").value;
            if (adaHariDipilih && waktu === "") {
                showToast("Waktu Kosong", "Silakan pilih jam terlebih dahulu!", "warning");
                return;
            }

            // Jika tidak ada hari dipilih, kirim jam kosong
            if (!adaHariDipilih) waktu = waktu || "00:00";

            // 4. Susun Format Pesan: "0,1,0,1,0,0,0#08:00"
            let stringHari = polaHari.join(",");
            let payload = stringHari + "#" + waktu;
            let topicJadwal = "AgroSquad/kontrol/jadwal_mingguan";

            // 5. Kirim ke MQTT
            var message = new Paho.MQTT.Message(payload);
            message.destinationName = topicJadwal;
            message.qos = 2;
            message.retained = true;
            client.send(message);

            // 6. KIRIM KE DATABASE (SERVER)
            simpanKeDB('jadwal_hari', stringHari);
            simpanKeDB('jadwal_jam', waktu);

            console.log("Mengirim Jadwal: " + payload);

            // 7. Toast sesuai konteks
            if (adaHariDipilih) {
                showToast("Jadwal Tersimpan", "Jadwal penyiraman aktif pukul " + waktu, "success");
            } else {
                showToast("Jadwal Dinonaktifkan", "Jadwal otomatis telah dimatikan", "warning");
            }
        };

        function simpanKeDB(keyName, valueData) {
            fetch('/update-setting', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        key: keyName,
                        value: valueData
                    })
                })
                .then(res => res.json())
                .then(data => console.log("Saved " + keyName + ":", data))
                .catch(err => console.error("Error saving " + keyName, err));
        }

        // --- CUSTOM TOAST FUNCTIONS ---
        let toastTimeout = null;

        const toastIcons = {
            success: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            warning: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            error: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
        };

        function showToast(title, message, type = "success") {
            const toast = document.getElementById("customToast");
            const progress = document.getElementById("toastProgress");

            // Set content
            document.getElementById("toastTitle").textContent = title;
            document.getElementById("toastMessage").textContent = message;
            document.getElementById("toastIcon").innerHTML = toastIcons[type] || toastIcons.success;

            // Set type class
            toast.className = "custom-toast";
            if (type === "warning") toast.classList.add("toast-warning");
            if (type === "error") toast.classList.add("toast-error");

            // Reset progress bar animation
            progress.style.animation = "none";
            void progress.offsetHeight; // Force reflow
            progress.style.animation = "toastTimer 3.5s linear forwards";

            // Show
            toast.classList.add("show");

            // Auto hide after 3.5s
            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => hideToast(), 3500);
        }

        function hideToast() {
            document.getElementById("customToast").classList.remove("show");
            if (toastTimeout) clearTimeout(toastTimeout);
        }
        // Event Listener tutup modal
        window.onclick = function(event) {
            var modal = document.getElementById('thresholdModal');
            if (event.target == modal) {
                window.closeModal();
            }
        };

        // Flatpickr
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#customTime", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                defaultHour: 10,
                defaultMinute: 30,
            });
        });
    </script>

@endsection
