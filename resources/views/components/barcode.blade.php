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

    <script>
        // Preload library saat dokumen dibuka
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Html5Qrcode === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.8/dist/html5-qrcode.min.js';
                document.head.appendChild(script);
            }
            
            // Preload suara
            ensureBeepSound();
        });
        
        // Cache untuk audio
        let beepAudio = null;

        function ensureBeepSound() {
            if (beepAudio) return beepAudio;
            
            beepAudio = new Audio();
            beepAudio.src = 'data:audio/mp3;base64,SUQzAwAAAAACHVRJVDIAAAAZAAAARWZmZWN0cyAtIEJhcmNvZGUgU2Nhbm5lcgBUWUVSAAAABQAAADIwMjMAVENPTQAAAAUAAABTRlgAVENPTgAAAAUAAABTRlgAVEFMQgAAAAUAAABTRlgAVFJDSwAAAAIAAAAwAFRZRVIAAAAFAAAAMjAyMwBUQ09NAAAABQAAAFNGWABUUEUxAAAABQAAAFNGWABUQ09OAAAABQAAAFNGWABUUFVCAAAABQAAAFNGWABUUlRBAAAABQAAAEQCDCMAAFRDT00AAAAGAAAAYmVlcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//uQxAADwAABpAAAACAAADSAAAAETEFNRTMuMTAwVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/7kMQAA8AAAaQAAAAgAAA0gAAABFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVQ==';
            beepAudio.preload = 'auto';
            beepAudio.volume = 1.0;
            
            return beepAudio;
        }

        // Fungsi untuk efek suara - optimasi dengan caching
        function playBeep() {
            const audio = ensureBeepSound();
            audio.currentTime = 0;
            
            // Gunakan Promise untuk memastikan audio diputar sebelum melanjutkan
            return new Promise((resolve) => {
                const playAttempt = audio.play();
                
                if (playAttempt) {
                    playAttempt.then(resolve).catch(() => {
                        console.log("Suara gagal diputar");
                        resolve(); // Lanjutkan meskipun suara gagal
                    });
                } else {
                    resolve();
                }
            });
        }

        // Fungsi untuk efek getar yang lebih efisien
        function vibrateDevice() {
            if (navigator.vibrate) {
                navigator.vibrate(100); // Durasi lebih pendek, cukup untuk feedback
            }
        }

        // Fungsi untuk membuka barcode scanner dengan optimasi
        function openBarcodeScanner() {
            // Pastikan library sudah dimuat
            if (typeof Html5Qrcode === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.8/dist/html5-qrcode.min.js';
                script.onload = initScannerModal;
                document.head.appendChild(script);
            } else {
                initScannerModal();
            }

            function initScannerModal() {
                // Buat DOM scanner secara minimal
                const modalContainer = document.createElement('div');
                modalContainer.id = 'barcode-scanner-modal';
                modalContainer.style = 'position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;z-index:9999;';
                
                const modalContent = document.createElement('div');
                modalContent.style = 'background-color:white;padding:20px;border-radius:8px;width:90%;max-width:500px;';
                
                modalContent.innerHTML = `
                    <div style="margin-bottom:15px"><h3 style="font-size:1.25rem;font-weight:600;">Scan Barcode</h3></div>
                    <div id="scanner-status" style="margin-bottom:10px;color:#4b5563;font-size:14px;">Mempersiapkan kamera...</div>
                    <div id="reader" style="width:100%;min-height:300px;border:1px solid #e5e7eb;overflow:hidden;border-radius:4px;position:relative;">
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:280px;height:180px;border:2px solid #ef4444;border-radius:8px;z-index:1;pointer-events:none;"></div>
                    </div>
                    <div style="margin-top:15px;display:flex;justify-content:space-between;align-items:center;">
                        <button id="flip-button" style="padding:8px 16px;background-color:#3b82f6;color:white;border-radius:6px;border:none;cursor:pointer;">Flip Camera</button>
                        <button id="cancel-button" style="padding:8px 16px;background-color:#f3f4f6;color:#374151;border-radius:6px;border:none;cursor:pointer;">Cancel</button>
                    </div>
                `;
                
                modalContainer.appendChild(modalContent);
                document.body.appendChild(modalContainer);
                
                // Setup event listeners
                document.getElementById('flip-button').addEventListener('click', function() {
                    const newFacingMode = currentCamera === 'environment' ? 'user' : 'environment';
                    restartCamera(newFacingMode);
                });
                
                document.getElementById('cancel-button').addEventListener('click', function() {
                    if (html5QrCode) {
                        html5QrCode.stop().catch(() => {});
                    }
                    modalContainer.remove();
                });

                // Start scanner dengan konfigurasi kecepatan tinggi
                let html5QrCode;
                let currentCamera = 'environment';

                startScanner(currentCamera);

                function startScanner(facingMode) {
                    currentCamera = facingMode;
                    const statusElement = document.getElementById('scanner-status');

                    try {
                        html5QrCode = new Html5Qrcode("reader");

                        // Konfigurasi super-optimized untuk kecepatan maksimum
                        const config = {
                            fps: 25, // Tingkatkan FPS untuk pemindaian lebih responsif
                            qrbox: {
                                width: 280,
                                height: 180
                            },
                            rememberLastUsedCamera: true,
                            // Tentukan eksplisit format barcode yang akan dipindai
                            formatsToSupport: [
                                Html5QrcodeSupportedFormats.CODE_128,
                                Html5QrcodeSupportedFormats.EAN_13
                            ],
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true
                            },
                            aspectRatio: 1.33,
                            // Tambahkan konfigurasi kamera untuk kinerja terbaik
                            videoConstraints: {
                                width: { ideal: 1280 },
                                height: { ideal: 720 },
                                facingMode: { exact: facingMode }
                            }
                        };

                        // Handler untuk hasil pemindaian
                        const successCallback = (decodedText) => {
                            // Segera hentikan pemindaian untuk menghemat resource
                            html5QrCode.pause();
                            
                            // Berikan feedback multi-indera secara paralel
                            Promise.all([
                                playBeep(),
                                new Promise(resolve => {
                                    vibrateDevice();
                                    resolve();
                                })
                            ]).then(() => {
                                // Update status dan set nilai
                                statusElement.innerText = 'Barcode terdeteksi!';
                                statusElement.style.color = '#10b981';
                                statusElement.style.fontWeight = 'bold';
                                
                                try {
                                    const wireId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                                    window.Livewire.find(wireId).set('data.resi', decodedText);
                                    
                                    // Tutup modal segera
                                    if (html5QrCode) {
                                        html5QrCode.stop().finally(() => {
                                            modalContainer.remove();
                                        });
                                    }
                                } catch (error) {
                                    console.error('Error setting form value:', error);
                                    // Jika gagal, lanjutkan pemindaian
                                    html5QrCode.resume();
                                }
                            });
                        };

                        // Handler error yang efisien
                        const errorCallback = () => {
                            // Abaikan error umum, ini mengurangi overhead console logging
                        };

                        // Mulai pemindaian dengan konfigurasi optimal
                        html5QrCode.start(
                            { facingMode: facingMode },
                            config,
                            successCallback,
                            errorCallback
                        ).then(() => {
                            statusElement.innerText = 'Arahkan kamera ke barcode';
                        }).catch((err) => {
                            statusElement.innerText = 'Error: ' + err.message;
                            console.error('Error starting scanner:', err);
                        });
                    } catch (error) {
                        console.error('Error initializing scanner:', error);
                    }
                }

                // Restart kamera yang dioptimasi
                function restartCamera(facingMode) {
                    if (html5QrCode) {
                        html5QrCode.stop().finally(() => {
                            startScanner(facingMode);
                        });
                    }
                }
            }
        }
    </script>
</div>