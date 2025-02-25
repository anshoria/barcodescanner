<div>
    <div class="flex items-center space-x-2">
        <input 
            type="text" 
            wire:model="data.resi" 
            class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 border-gray-300 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500" 
        />
        <button 
            type="button"
            onclick="openBarcodeScanner()"
            class="filament-button inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Scan
        </button>
    </div>

    <!-- Audio untuk efek suara -->
    <audio id="barcode-beep" preload="auto">
        <source src="https://cdn.jsdelivr.net/gh/niklasvh/html-to-image@v1.11.11/examples/assets/beep.mp3" type="audio/mp3">
    </audio>

    <script>
        // Pastikan hanya dimuat sekali
        if (typeof window.barcodeScriptLoaded === 'undefined') {
            window.barcodeScriptLoaded = true;
            
            // Load library saat page load
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            document.head.appendChild(script);
        }

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
                audio.currentTime = 0;
                audio.play().catch(e => console.log("Audio play failed:", e));
            }
        }

        // Fungsi untuk membuka barcode scanner
        function openBarcodeScanner() {
            // Create modal container
            const modalContainer = document.createElement('div');
            modalContainer.id = 'barcode-scanner-modal';
            modalContainer.style.position = 'fixed';
            modalContainer.style.top = '0';
            modalContainer.style.left = '0';
            modalContainer.style.width = '100%';
            modalContainer.style.height = '100%';
            modalContainer.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
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
            scannerContainer.style.backgroundColor = '#f3f4f6';
            scannerContainer.style.position = 'relative';

            // Create footer
            const footer = document.createElement('div');
            footer.style.marginTop = '15px';
            footer.style.display = 'flex';
            footer.style.justifyContent = 'flex-end';

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
                if (modalContainer.html5QrCodeScanner) {
                    try {
                        modalContainer.html5QrCodeScanner.stop();
                    } catch(err) {
                        console.log("Error stopping scanner:", err);
                    }
                }
                modalContainer.remove();
            };

            // Append elements
            footer.appendChild(cancelButton);
            modalContent.appendChild(header);
            modalContent.appendChild(statusIndicator);
            modalContent.appendChild(scannerContainer);
            modalContent.appendChild(footer);
            modalContainer.appendChild(modalContent);
            document.body.appendChild(modalContainer);

            // Cek dan minta izin kamera terlebih dahulu
            let statusElement = document.getElementById('scanner-status');
            
            checkCameraPermission()
                .then(() => initializeScanner())
                .catch(error => {
                    statusElement.innerText = 'Error: ' + error.message;
                    statusElement.style.color = 'red';
                });

            // Fungsi untuk memeriksa izin kamera
            function checkCameraPermission() {
                return new Promise((resolve, reject) => {
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(stream => {
                            // Stop stream setelah izin diperoleh
                            stream.getTracks().forEach(track => track.stop());
                            resolve();
                        })
                        .catch(err => {
                            reject(new Error('Tidak dapat mengakses kamera. Pastikan memberikan izin kamera.'));
                        });
                });
            }
                
            // Inisialisasi scanner
            function initializeScanner() {
                statusElement.innerText = 'Memuat library scanner...';
                
                // Cek apakah library sudah dimuat
                function checkLibraryLoaded() {
                    if (typeof Html5Qrcode !== 'undefined') {
                        startScanner();
                    } else {
                        statusElement.innerText = 'Menunggu library scanner dimuat...';
                        setTimeout(checkLibraryLoaded, 100);
                    }
                }
                
                checkLibraryLoaded();
            }

            function startScanner() {
                statusElement.innerText = 'Menyiapkan kamera...';
                
                try {
                    const html5QrCodeScanner = new Html5Qrcode("reader");
                    
                    const config = {
                        fps: 15, // Turunkan sedikit untuk stabilitas
                        qrbox: { width: 250, height: 150 },
                        disableFlip: false
                    };

                    statusElement.innerText = 'Mengaktifkan kamera...';
                    
                    html5QrCodeScanner.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => {
                            // Tambahkan efek suara dan getar
                            playBeep();
                            vibrateDevice();
                            
                            statusElement.innerText = 'Barcode terdeteksi: ' + decodedText;
                            
                            // Success callback - fill the resi field
                            try {
                                const wireId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                                window.Livewire.find(wireId).set('data.resi', decodedText);
                                
                                // Close scanner after short delay
                                setTimeout(() => {
                                    html5QrCodeScanner.stop().catch(err => {});
                                    modalContainer.remove();
                                }, 500);
                            } catch (error) {
                                statusElement.innerText = 'Error mengisi field: ' + error.message;
                                console.error('Error setting form value:', error);
                            }
                        },
                        (errorMessage) => {
                            // Silent error handling during scanning
                        }
                    ).then(() => {
                        statusElement.innerText = 'Kamera aktif. Arahkan ke barcode.';
                        modalContainer.html5QrCodeScanner = html5QrCodeScanner;
                    }).catch((err) => {
                        statusElement.innerText = 'Error: ' + err.message;
                        statusElement.style.color = 'red';
                        console.error(`Error starting scanner: ${err}`);
                    });
                } catch (error) {
                    statusElement.innerText = 'Error memulai scanner: ' + error.message;
                    statusElement.style.color = 'red';
                    console.error('Error initializing scanner:', error);
                }
            }
        }
    </script>
</div>