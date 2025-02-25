<div>
    <div class="flex items-center space-x-2">
        <input type="text" wire:model="data.resi"
            class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500" />
        <button type="button" onclick="openBarcodeScanner()"
            class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                </path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Scan
        </button>
    </div>

    <!-- Audio untuk efek suara -->
    <audio id="barcode-beep" preload="auto">
        <source src="{{ asset('sound/barcode.mp3') }}" type="audio/mp3">
        <!-- Fallback menggunakan data URI jika file tidak ditemukan -->
        <source
            src="data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA//tQwAAACwBtDWAEAAAMcG0XfAAAAwAAAAEuhAWZWkAAAQAAAAAU+i9X8Rf/4ix/jJHaHUF9IMQEReJfxzv////xFjGMYI3EAgEAlxthBwMMiICeCYZchIEAZleDJQPZv/7ksQKgAtIRO3Y8QABf4ubyx4gAJOKBrFFTDiTjwokIurkGBpA8DwPJBwKBQMmKYA3SQMBoGgaCBrxAxIGmdBUU5fAYDQMlIpFQMKCYGnm3ILDWqodKMTIdoUuGjUwAALTLnXX1oeIoNoSNYk3TppKfJOH1UEBJyAQGmNXXFhTQnL0vZc6aDCQ6sFBJ0i5Y1GJxLbRs5h7NdISNQJHUGCh4XNAH/9yt///+GlHf/5YiVYaoAAAAG5NUVAAUlcwT//uSxA+ACqAnWBGJMAF0Basx9g4AD6ChlZBhYHBwXFkJAFgHODAHEAUKBIMkPe0HAMA4TtIEogDw2EZAC5XtIYGRB2wEPaJQcFEtZdikVFLkdRFQKBI6nUiqJ1OGw40ZRIBDw85d2OtzZwJHBpyOMjYogAUhYfHCTMYKWxULjMlhEZBZ9sWUoYdsFdJKZ2C0Kez01RyQ+dE5mAZGQOcDDYkKh0yCBUllJ4wNKDgWVU1Vfk0IVEyY3HXjVKvODhRMNm5VTbrL/83/////////////9RapTEFNRTMuMTAwqqqqqqqqqg=="
            type="audio/mp3">
    </audio>

    <script>
        // Pre-aktivasi audio untuk mengatasi kebijakan autoplay
        document.addEventListener('click', function() {
            const audio = document.getElementById('barcode-beep');
            if (audio && !window.audioActivated) {
                window.audioActivated = true;
                audio.volume = 0;
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                }).catch(e => {});
            }
        }, {once: true});

        // Fungsi untuk efek getar
        function vibrateDevice() {
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
        }

        // Fungsi untuk efek suara yang lebih reliable
        function playBeep() {
            console.log("Memainkan suara beep");
            
            // Cara 1: Gunakan elemen audio yang sudah ada
            const audio = document.getElementById('barcode-beep');
            if (audio) {
                audio.currentTime = 0;
                audio.volume = 1.0;
                
                // Memastikan suara diputar meskipun masih sedang diputar
                audio.pause();
                
                const playPromise = audio.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.error("Gagal memainkan suara:", error);
                        
                        // Cara 2: Jika cara 1 gagal, buat elemen audio baru (fallback)
                        try {
                            const newAudio = new Audio("{{ asset('sound/barcode.mp3') }}");
                            newAudio.volume = 1.0;
                            newAudio.play();
                        } catch (e) {
                            console.error("Fallback audio juga gagal:", e);
                        }
                    });
                }
            }
        }

        // Fungsi untuk membuka barcode scanner
        function openBarcodeScanner() {
            // Preload HTML5-QRCode library
            if (typeof Html5Qrcode === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                script.onload = initScannerModal;
                document.head.appendChild(script);
            } else {
                initScannerModal();
            }

            function initScannerModal() {
                // Create modal container
                const modalContainer = document.createElement('div');
                modalContainer.id = 'barcode-scanner-modal';
                modalContainer.style.position = 'fixed';
                modalContainer.style.top = '0';
                modalContainer.style.left = '0';
                modalContainer.style.width = '100%';
                modalContainer.style.height = '100%';
                modalContainer.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                modalContainer.style.display = 'flex';
                modalContainer.style.alignItems = 'center';
                modalContainer.style.justifyContent = 'center';
                modalContainer.style.zIndex = '9999';

                // Create modal content
                const modalContent = document.createElement('div');
                modalContent.style.backgroundColor = 'white';
                modalContent.style.padding = '20px';
                modalContent.style.borderRadius = '8px';
                modalContent.style.width = '90%';
                modalContent.style.maxWidth = '500px';
                modalContent.innerHTML = `
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 15px;">Scan Barcode</h3>
                    <div id="scanner-status" style="margin-bottom: 10px; color: #4b5563; font-size: 14px;">Mempersiapkan kamera...</div>
                    <div style="position: relative;">
                        <div id="reader" style="width: 100%; min-height: 300px; border: 1px solid #e5e7eb; overflow: hidden; border-radius: 4px;"></div>
                        <div id="scan-region-highlight" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 300px; height: 100px; border: 3px solid #ef4444; border-radius: 4px; box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.3); z-index: 1; pointer-events: none;"></div>
                    </div>
                    <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <button id="flip-camera" style="padding: 8px 16px; background-color: #3b82f6; color: white; border-radius: 6px; border: none; cursor: pointer;">Balik Kamera</button>
                        <button id="close-scanner" style="padding: 8px 16px; background-color: #f3f4f6; color: #374151; border-radius: 6px; border: none; cursor: pointer;">Batal</button>
                    </div>
                `;

                modalContainer.appendChild(modalContent);
                document.body.appendChild(modalContainer);

                // Referensi ke elemen
                const statusElement = document.getElementById('scanner-status');
                const flipButton = document.getElementById('flip-camera');
                const closeButton = document.getElementById('close-scanner');

                // Variabel scanner
                let html5QrCode;
                let currentCamera = 'environment';

                // Inisialisasi scanner
                startScanner(currentCamera);

                function startScanner(facingMode) {
                    currentCamera = facingMode;
                    statusElement.innerText = 'Mengaktifkan kamera...';

                    try {
                        html5QrCode = new Html5Qrcode("reader");

                        // Konfigurasi optimal untuk barcode
                        const config = {
                            fps: 15,                      // Nilai optimal untuk keseimbangan performa
                            qrbox: {
                                width: 300,               // Lebar yang lebih besar untuk barcode 1D
                                height: 100               // Kurang tinggi untuk barcode 1D
                            },
                            formatsToSupport: [           // Fokus pada format umum untuk meningkatkan kecepatan
                                Html5QrcodeSupportedFormats.CODE_128,
                                Html5QrcodeSupportedFormats.EAN_13,
                                Html5QrcodeSupportedFormats.EAN_8,
                                Html5QrcodeSupportedFormats.CODE_39,
                                Html5QrcodeSupportedFormats.CODE_93
                            ],
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true
                            },
                            aspectRatio: 2.0              // Aspek rasio yang lebih lebar untuk barcode 1D
                        };

                        // Konfigurasi kamera
                        const cameraConfig = { 
                            facingMode: facingMode,
                            width: { ideal: 1280 },       // Resolusi tinggi untuk detail lebih baik
                            height: { ideal: 720 }
                        };

                        html5QrCode.start(
                            cameraConfig,
                            config,
                            (decodedText) => {
                                // Callback saat barcode terdeteksi
                                statusElement.innerText = 'Barcode terdeteksi!';
                                statusElement.style.color = '#10b981';
                                statusElement.style.fontWeight = 'bold';
                                
                                console.log("Barcode terdeteksi:", decodedText);

                                // Efek suara dan getar saat berhasil
                                playBeep();
                                vibrateDevice();

                                // Set nilai ke input
                                try {
                                    const wireId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                                    window.Livewire.find(wireId).set('data.resi', decodedText);

                                    // Tutup scanner setelah delay
                                    setTimeout(() => {
                                        stopCamera().then(() => {
                                            modalContainer.remove();
                                        });
                                    }, 1000);
                                } catch (error) {
                                    console.error('Error setting form value:', error);
                                    stopCamera().then(() => {
                                        modalContainer.remove();
                                    });
                                }
                            },
                            (errorMessage) => {
                                // Silent error handling
                            }
                        ).then(() => {
                            statusElement.innerText = 'Arahkan kamera ke barcode (posisi horizontal)';
                        }).catch((err) => {
                            if (facingMode === 'environment' && err.message.includes('Requested device not found')) {
                                // Coba dengan kamera depan jika kamera belakang tidak tersedia
                                startScanner('user');
                            } else {
                                statusElement.innerText = 'Error: ' + err.message;
                                console.error('Error starting scanner:', err);
                            }
                        });
                    } catch (error) {
                        console.error('Error initializing scanner:', error);
                    }
                }

                function stopCamera() {
                    if (html5QrCode) {
                        if (html5QrCode.isScanning) {
                            return html5QrCode.stop().catch(err => {
                                console.error('Error stopping camera:', err);
                                return Promise.resolve();
                            });
                        }
                    }
                    return Promise.resolve();
                }

                // Event handlers
                flipButton.addEventListener('click', function() {
                    const newFacingMode = currentCamera === 'environment' ? 'user' : 'environment';
                    stopCamera().then(() => {
                        startScanner(newFacingMode);
                    });
                });

                closeButton.addEventListener('click', function() {
                    stopCamera().then(() => {
                        modalContainer.remove();
                    });
                });
            }
        }
    </script>
</div>