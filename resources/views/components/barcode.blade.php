<div>
    <div class="flex items-center space-x-2">
        <input 
            type="text" 
            wire:model="data.resi" 
            class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500" 
        />
        <button 
            type="button"
            onclick="startBarcodeScanner()"
            class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Scan
        </button>
    </div>

    <script>
        // Preload audio untuk menghindari masalah izin autoplay
        const beepSound = new Audio();
        beepSound.src = "{{ asset('sound/barcode.mp3') }}"; 
        beepSound.volume = 1.0;
        beepSound.load();

        // Fungsi untuk efek getar
        function vibrateDevice() {
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
        }

        // Fungsi untuk memainkan suara
        function playBeep() {
    console.log("Memainkan suara beep dari file");
    
    // Buat instance baru setiap kali untuk menghindari masalah pemutaran berulang
    const sound = new Audio("{{ asset('sound/barcode.mp3') }}");
    sound.volume = 1.0;
    
    const playPromise = sound.play();
    if (playPromise !== undefined) {
        playPromise.catch(error => {
            console.error("Gagal memainkan suara:", error);
            // Fallback jika gagal memutar file
            navigator.vibrate && navigator.vibrate(200);
        });
    }
}

        // Fungsi utama untuk scanner
        function startBarcodeScanner() {
            // Pastikan audio aktif dengan interaksi pengguna
            beepSound.play().then(() => {
                beepSound.pause();
                beepSound.currentTime = 0;
                initScanner();
            }).catch(e => {
                console.log("Audio perlu interaksi pengguna:", e);
                initScanner();
            });
        }

        function initScanner() {
            // Buat skrip jika belum ada
            if (typeof Html5Qrcode === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
                script.onload = createScannerModal;
                document.head.appendChild(script);
            } else {
                createScannerModal();
            }
        }

        function createScannerModal() {
            // Buat modal container
            const modalContainer = document.createElement('div');
            modalContainer.id = 'barcode-scanner-modal';
            modalContainer.style.position = 'fixed';
            modalContainer.style.top = '0';
            modalContainer.style.left = '0';
            modalContainer.style.width = '100%';
            modalContainer.style.height = '100%';
            modalContainer.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            modalContainer.style.display = 'flex';
            modalContainer.style.alignItems = 'center';
            modalContainer.style.justifyContent = 'center';
            modalContainer.style.zIndex = '9999';

            // Konten modal
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
                    <div id="scan-region-highlight" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70%; height: 100px; border: 3px solid #ef4444; border-radius: 4px; box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.3); z-index: 1; pointer-events: none;"></div>
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
            initializeScanner();
            
            function initializeScanner() {
                try {
                    html5QrCode = new Html5Qrcode("reader");
                    
                    // Konfigurasi optimal untuk barcode 1D (seperti di paket)
                    const config = {
                        fps: 20,
                        qrbox: { width: 300, height: 100 }, // Lebih lebar, kurang tinggi - optimal untuk barcode 1D
                        aspectRatio: 2.0, // Rasio aspek lebih lebar
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.CODE_128,
                            Html5QrcodeSupportedFormats.EAN_13,
                            Html5QrcodeSupportedFormats.EAN_8,
                            Html5QrcodeSupportedFormats.CODE_39,
                            Html5QrcodeSupportedFormats.CODE_93
                        ],
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true // Gunakan API bawaan browser jika tersedia
                        }
                    };
                    
                    startCamera(currentCamera, config);
                    
                    // Event listeners
                    flipButton.addEventListener('click', function() {
                        const newCamera = currentCamera === 'environment' ? 'user' : 'environment';
                        stopCamera().then(() => startCamera(newCamera, config));
                    });
                    
                    closeButton.addEventListener('click', function() {
                        stopCamera().then(() => modalContainer.remove());
                    });
                } catch (error) {
                    statusElement.innerText = 'Error: ' + error.message;
                    console.error('Error initializing scanner:', error);
                }
            }
            
            function startCamera(facingMode, config) {
                currentCamera = facingMode;
                statusElement.innerText = 'Mengaktifkan kamera...';
                
                const cameraConstraints = { 
                    facingMode: { exact: facingMode },
                    // Tambahkan constraint resolusi untuk performa lebih baik
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                };
                
                html5QrCode.start(
                    cameraConstraints,
                    config,
                    (decodedText) => {
                        // Callback saat barcode terdeteksi
                        statusElement.innerText = 'Barcode terdeteksi!';
                        console.log("Barcode terdeteksi:", decodedText);
                        
                        // Efek suara dan getar
                        playBeep();
                        vibrateDevice();
                        
                        // Set nilai ke input
                        try {
                            const wireId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                            window.Livewire.find(wireId).set('data.resi', decodedText);
                            
                            // Tutup scanner setelah delay
                            setTimeout(() => {
                                stopCamera().then(() => modalContainer.remove());
                            }, 1000);
                        } catch (error) {
                            console.error('Error setting form value:', error);
                        }
                    },
                    (errorMessage) => {
                        // Silent error callback
                    }
                ).then(() => {
                    statusElement.innerText = 'Arahkan kamera ke barcode (posisi horizontal)';
                }).catch((err) => {
                    if (facingMode === 'environment' && err.message.includes('Requested device not found')) {
                        // Coba dengan kamera depan jika kamera belakang tidak tersedia
                        startCamera('user', config);
                    } else {
                        statusElement.innerText = 'Error: ' + err.message;
                        console.error('Error starting scanner:', err);
                    }
                });
            }
            
            function stopCamera() {
                if (html5QrCode && html5QrCode.getState() === Html5QrcodeScannerState.SCANNING) {
                    return html5QrCode.stop();
                }
                return Promise.resolve();
            }
        }
    </script>
</div>