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

    <script>
        // Load HTML5-QRCode library from CDN
        if (typeof Html5Qrcode === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            document.head.appendChild(script);
        }

        // Function to open barcode scanner
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

            // Create scanner container
            const scannerContainer = document.createElement('div');
            scannerContainer.id = 'reader';
            scannerContainer.style.width = '100%';
            scannerContainer.style.minHeight = '300px';

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
                modalContainer.remove();
                if (html5QrCodeScanner) {
                    html5QrCodeScanner.clear();
                }
            };

            // Append elements
            footer.appendChild(cancelButton);
            modalContent.appendChild(header);
            modalContent.appendChild(scannerContainer);
            modalContent.appendChild(footer);
            modalContainer.appendChild(modalContent);
            document.body.appendChild(modalContainer);

            // Initialize scanner when the library is loaded
            const initScanner = function() {
                if (typeof Html5Qrcode !== 'undefined') {
                    const html5QrCodeScanner = new Html5Qrcode("reader");
                    const config = {
                        fps: 10,
                        qrbox: { width: 250, height: 150 },
                    };

                    html5QrCodeScanner.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => {
                            // Success callback - fill the resi field
                            const wireId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                            window.Livewire.find(wireId).set('data.resi', decodedText);
                            
                            // Close scanner
                            html5QrCodeScanner.stop();
                            modalContainer.remove();
                        },
                        (errorMessage) => {
                            // Error callback (silent)
                        }
                    ).catch((err) => {
                        console.error(`Error starting scanner: ${err}`);
                    });

                    // Store scanner reference
                    modalContainer.html5QrCodeScanner = html5QrCodeScanner;
                } else {
                    // Library not loaded yet, wait a bit
                    setTimeout(initScanner, 500);
                }
            };

            // Start scanner initialization
            setTimeout(initScanner, 500);
        }
    </script>
</div>