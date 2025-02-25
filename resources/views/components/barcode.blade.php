<div>
    <div class="flex items-center space-x-2">
        <input
            type="text"
            id="barcode-input"
            class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-70 fi-input"
            placeholder="Scan a barcode"
            wire:model="data.barcode"
            readonly
        >
        <button
            type="button"
            x-data="{}"
            x-on:click="$dispatch('open-modal', { id: 'barcode-scanner-modal' })"
            class="fi-btn fi-btn-size-md relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition duration-75 fi-btn-color-primary bg-primary-600 text-white hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400"
        >
            <span class="fi-btn-label">
                Scan
            </span>
        </button>
    </div>

    <x-filament::modal id="barcode-scanner-modal" width="md" alignment="center">
        <x-slot name="heading">
            Scan Barcode
        </x-slot>

        <div class="space-y-4">
            <div id="barcode-scanner-container" class="relative overflow-hidden h-64 bg-gray-100 rounded-md">
                <video 
                    id="barcode-scanner" 
                    class="w-full h-full object-cover" 
                    playsinline 
                    autoplay
                    muted
                    style="transform: translate3d(0, 0, 0); backface-visibility: hidden;"
                ></video>
                <div class="scan-area absolute inset-0 border-2 border-red-500 m-auto w-2/3 h-1/2 pointer-events-none"></div>
                <div id="scanner-error" class="absolute inset-0 flex items-center justify-center text-red-500 bg-gray-100 bg-opacity-80 hidden">
                    <div class="text-center p-4">
                        <p class="font-bold">Camera Error</p>
                        <p id="error-message" class="text-sm"></p>
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Position the barcode within the scanning area.
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'barcode-scanner-modal' })"
                color="gray"
            >
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <script src="https://unpkg.com/@zxing/library@0.21.3"></script>
    <script>
        let modalSession = 0;
        let isScanning = false;
        let codeReader = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize ZXing library when page loads
            if (typeof ZXing !== 'undefined') {
                codeReader = new ZXing.BrowserMultiFormatReader();
            } else {
                console.error('ZXing library not loaded');
            }
            
            // Start scanner when modal opens
            document.addEventListener('open-modal', event => {
                if (event.detail.id === 'barcode-scanner-modal') {
                    // Increment session to track current modal instance
                    modalSession++;
                    const currentSession = modalSession;
                    
                    // Reset error display
                    const errorDisplay = document.getElementById('scanner-error');
                    if (errorDisplay) errorDisplay.classList.add('hidden');
                    
                    // Delay camera start to ensure modal is rendered
                    setTimeout(() => {
                        // Only proceed if this is still the active session
                        if (currentSession === modalSession) {
                            startCamera();
                        }
                    }, 500);
                }
            });
            
            // Stop scanner when modal closes
            document.addEventListener('close-modal', event => {
                if (event.detail.id === 'barcode-scanner-modal') {
                    stopScanner();
                }
            });
        });
        
        function showError(message) {
            const errorDisplay = document.getElementById('scanner-error');
            const errorMessage = document.getElementById('error-message');
            if (errorMessage && errorDisplay) {
                errorMessage.textContent = message;
                errorDisplay.classList.remove('hidden');
                console.error(message);
            }
        }
        
        function startCamera() {
            if (isScanning) return;
            
            // Ensure codeReader is initialized
            if (!codeReader) {
                try {
                    codeReader = new ZXing.BrowserMultiFormatReader();
                } catch (err) {
                    showError('Failed to initialize barcode scanner');
                    console.error(err);
                    return;
                }
            }
            
            // Get available cameras
            codeReader.listVideoInputDevices()
                .then((videoInputDevices) => {
                    if (videoInputDevices.length === 0) {
                        showError('No camera devices found.');
                        return;
                    }
                    
                    console.log('Available cameras:', videoInputDevices.map(device => device.label || device.deviceId));
                    
                    // Try to use the rear camera on mobile devices
                    const rearCamera = videoInputDevices.find(device => 
                        device.label.toLowerCase().includes('back') || 
                        device.label.toLowerCase().includes('rear')
                    );
                    const selectedDeviceId = rearCamera ? rearCamera.deviceId : videoInputDevices[0].deviceId;
                    
                    // Start the scanner
                    startScanner(selectedDeviceId);
                })
                .catch((err) => {
                    console.error('Error listing camera devices:', err);
                    showError(`Error accessing camera: ${err.message || 'Unknown error'}`);
                });
        }
        
        function startScanner(deviceId) {
            const currentSession = modalSession;
            const scanArea = document.querySelector('.scan-area');
            
            // Clear any existing video source
            const video = document.getElementById('barcode-scanner');
            if (video && video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
            
            codeReader.decodeFromVideoDevice(deviceId, 'barcode-scanner', (result, err) => {
                // Verify this callback is for the current modal session
                if (currentSession !== modalSession) return;
                
                if (result) {
                    console.log('Barcode detected:', result.text);
                    
                    // Update the input field value
                    const input = document.getElementById('barcode-input');
                    if (input) {
                        input.value = result.text;
                        
                        // Trigger input event for Livewire
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        
                        // For Livewire direct update
                        const livewireEl = document.querySelector('[wire\\:id]');
                        if (livewireEl && window.Livewire) {
                            const component = window.Livewire.find(
                                livewireEl.getAttribute('wire:id')
                            );
                            if (component) {
                                component.set('data.barcode', result.text);
                            }
                        }
                    }
                    
                    // Show success feedback
                    if (scanArea) scanArea.style.borderColor = 'green';
                    
                    // Close modal after successful scan
                    setTimeout(() => {
                        document.dispatchEvent(
                            new CustomEvent('close-modal', { 
                                detail: { id: 'barcode-scanner-modal' } 
                            })
                        );
                    }, 500);
                } else if (err && !(err instanceof ZXing.NotFoundException)) {
                    console.error('Scanner error:', err);
                } else {
                    // Normal scanning state
                    if (scanArea) scanArea.style.borderColor = 'red';
                }
            }).then(() => {
                isScanning = true;
                console.log('Scanner started successfully');
            }).catch(err => {
                console.error('Failed to start scanner:', err);
                showError(`Failed to start scanner: ${err.message || 'Unknown error'}`);
            });
        }
        
        function stopScanner() {
            if (!isScanning) return;
            
            try {
                if (codeReader) {
                    codeReader.reset();
                }
                
                // Make sure video tracks are stopped
                const video = document.getElementById('barcode-scanner');
                if (video && video.srcObject) {
                    video.srcObject.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                }
                
                isScanning = false;
                console.log('Scanner stopped');
            } catch (err) {
                console.error('Error stopping scanner:', err);
            }
        }
        
        // Clean up when page unloads
        window.addEventListener('beforeunload', () => {
            if (isScanning && codeReader) {
                codeReader.reset();
            }
        });
    </script>
</div>