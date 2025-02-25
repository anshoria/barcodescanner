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
            src="data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA//tQwAAAAAAAAAAAAAAAAAAAAAAAA..."
            type="audio/mp3">
    </audio>

    <script>
        // Fungsi untuk efek getar
        function vibrateDevice() {
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
        }

        // Fungsi untuk efek suara
        function playBeep() {
            const audio = document.getElementById('barcode-beep');
            if (audio) {
                // Reset audio ke awal
                audio.currentTime = 0;

                // Pastikan volume maksimum
                audio.volume = 1.0;

                // Mencoba memainkan dan menangani error
                const playPromise = audio.play();

                if (playPromise !== undefined) {
                    playPromise.then(_ => {
                        // Berhasil diputar
                        console.log("Audio berhasil diputar");
                    }).catch(error => {
                        console.error("Audio gagal diputar:", error);
                    });
                }
            } else {
                console.error("Element audio tidak ditemukan");
            }
        }

        // Fungsi untuk membuka barcode scanner
        function openBarcodeScanner() {
            // Load library jika belum
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

                // Create header
                const header = document.createElement('div');
                header.style.marginBottom = '15px';
                header.innerHTML = '<h3 style="font-size: 1.25rem; font-weight: 600;">Scan Barcode</h3>';

                // Create status indicator
                const statusIndicator = document.createElement('div');
                statusIndicator.id = 'scanner-status';
                statusIndicator.style.marginBottom = '10px';
                statusIndicator.style.color = '#4b5563';
                statusIndicator.style.fontSize = '14px';
                statusIndicator.innerText = 'Mempersiapkan kamera...';

                // Create scanner container
                const scannerContainer = document.createElement('div');
                scannerContainer.id = 'reader';
                scannerContainer.style.width = '100%';
                scannerContainer.style.minHeight = '300px';
                scannerContainer.style.border = '1px solid #e5e7eb';
                scannerContainer.style.overflow = 'hidden';
                scannerContainer.style.borderRadius = '4px';

                // Create guidance overlay
                const guidanceOverlay = document.createElement('div');
                guidanceOverlay.style.position = 'relative';
                guidanceOverlay.style.top = '-300px';
                guidanceOverlay.style.left = '50%';
                guidanceOverlay.style.transform = 'translateX(-50%)';
                guidanceOverlay.style.width = '280px';
                guidanceOverlay.style.height = '180px';
                guidanceOverlay.style.border = '2px solid #ef4444';
                guidanceOverlay.style.borderRadius = '8px';
                guidanceOverlay.style.boxShadow = '0 0 0 2000px rgba(0, 0, 0, 0.3)';
                guidanceOverlay.style.zIndex = '1';
                guidanceOverlay.style.pointerEvents = 'none';

                // Create footer
                const footer = document.createElement('div');
                footer.style.marginTop = '15px';
                footer.style.display = 'flex';
                footer.style.justifyContent = 'space-between';
                footer.style.alignItems = 'center';

                // Create camera flip button
                const flipButton = document.createElement('button');
                flipButton.innerText = 'Flip Camera';
                flipButton.style.padding = '8px 16px';
                flipButton.style.backgroundColor = '#3b82f6';
                flipButton.style.color = 'white';
                flipButton.style.borderRadius = '6px';
                flipButton.style.border = 'none';
                flipButton.style.cursor = 'pointer';
                flipButton.onclick = function() {
                    const currentFacingMode = currentCamera === 'environment' ? 'user' : 'environment';
                    restartCamera(currentFacingMode);
                };

                // Create cancel button
                const cancelButton = document.createElement('button');
                cancelButton.innerText = 'Cancel';
                cancelButton.style.padding = '8px 16px';
                cancelButton.style.backgroundColor = '#f3f4f6';
                cancelButton.style.color = '#374151';
                cancelButton.style.borderRadius = '6px';
                cancelButton.style.border = 'none';
                cancelButton.style.cursor = 'pointer';
                cancelButton.onclick = function() {
                    if (html5QrCode) {
                        try {
                            html5QrCode.stop();
                        } catch (err) {
                            console.log("Error stopping scanner:", err);
                        }
                    }
                    modalContainer.remove();
                };

                // Append elements
                footer.appendChild(flipButton);
                footer.appendChild(cancelButton);
                modalContent.appendChild(header);
                modalContent.appendChild(statusIndicator);
                modalContent.appendChild(scannerContainer);
                scannerContainer.appendChild(guidanceOverlay);
                modalContent.appendChild(footer);
                modalContainer.appendChild(modalContent);
                document.body.appendChild(modalContainer);

                // Start scanner
                let html5QrCode;
                let currentCamera = 'environment';

                startScanner(currentCamera);

                function startScanner(facingMode) {
                    currentCamera = facingMode;
                    const statusElement = document.getElementById('scanner-status');

                    try {
                        html5QrCode = new Html5Qrcode("reader");

                        const config = {
                            fps: 20,
                            qrbox: {
                                width: 280,
                                height: 180
                            },
                            formatsToSupport: [
                                Html5QrcodeSupportedFormats.CODE_128,
                                Html5QrcodeSupportedFormats.EAN_13,
                                Html5QrcodeSupportedFormats.EAN_8,
                                Html5QrcodeSupportedFormats.CODE_39,
                                Html5QrcodeSupportedFormats.CODE_93,
                                Html5QrcodeSupportedFormats.UPC_A,
                                Html5QrcodeSupportedFormats.UPC_E
                            ],
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true
                            },
                            aspectRatio: 1.0
                        };

                        html5QrCode.start({
                                facingMode: facingMode
                            },
                            config,
                            (decodedText) => {
                                // Efek suara dan getar saat berhasil
                                playBeep();
                                vibrateDevice();

                                statusElement.innerText = 'Barcode terdeteksi!';
                                statusElement.style.color = '#10b981';
                                statusElement.style.fontWeight = 'bold';

                                // Set nilai ke input resi
                                try {
                                    const wireId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                                    window.Livewire.find(wireId).set('data.resi', decodedText);

                                    // Tutup modal setelah delay singkat
                                    setTimeout(() => {
                                        if (html5QrCode) {
                                            html5QrCode.stop().catch(() => {});
                                        }
                                        modalContainer.remove();
                                    }, 1000);
                                } catch (error) {
                                    console.error('Error setting form value:', error);
                                }
                            },
                            (errorMessage) => {
                                // Silent error handling
                            }
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

                function restartCamera(facingMode) {
                    if (html5QrCode) {
                        html5QrCode.stop().then(() => {
                            startScanner(facingMode);
                        }).catch((err) => {
                            console.error('Error stopping scanner:', err);
                            startScanner(facingMode);
                        });
                    }
                }
            }
        }
    </script>
</div>
