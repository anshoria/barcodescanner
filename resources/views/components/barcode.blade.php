<div>
    <div class="flex items-center space-x-2">
        <input id="data-resi" type="text" wire:model="data.resi"
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

    <!-- Modal untuk scanner (diluar form)-->
</div>

<!-- Modal untuk scanner (memastikan berada di luar form) -->
<div id="qrcode-scanner-modal" 
     x-data="{}" 
     x-on:open-modal.window="if ($event.detail.id === 'qrcode-scanner-modal') $el.style.display = 'flex'"
     x-on:close-modal.window="if ($event.detail.id === 'qrcode-scanner-modal') $el.style.display = 'none'"
     style="display: none;" 
     class="fixed inset-0 z-50 bg-black bg-opacity-80 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
            <h3 class="text-lg font-medium dark:text-white">Scan Barcode</h3>
            <button type="button" onclick="closeScannerModal(event)" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-4">
            <div class="scan-area relative border-4 border-red-500 rounded-lg overflow-hidden aspect-video mb-2">
                <video id="scanner" style="width: 100%; height: 100%; object-fit: cover; display: none;"></video>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Arahkan kamera ke barcode atau QR code</p>
        </div>
    </div>
</div>

<script>
    // Load ZXing library
    let ZXing;
    const loadZXingLibrary = async () => {
        if (!ZXing) {
            await import('https://unpkg.com/@zxing/library@0.21.3');
            ZXing = window.ZXing;
        }
        return ZXing;
    };

    const successSound = new Audio('{{ asset("sound/barcode.mp3") }}');

    let codeReader = null;
    const initCodeReader = async () => {
        if (!codeReader) {
            await loadZXingLibrary();
            const hints = new Map();
            const formats = [
                ZXing.BarcodeFormat.QR_CODE,
                ZXing.BarcodeFormat.DATA_MATRIX,
                ZXing.BarcodeFormat.EAN_13,
                ZXing.BarcodeFormat.EAN_8,
                ZXing.BarcodeFormat.UPC_A,
                ZXing.BarcodeFormat.UPC_E,
                ZXing.BarcodeFormat.CODE_39,
                ZXing.BarcodeFormat.CODE_128,
                ZXing.BarcodeFormat.ITF,
                ZXing.BarcodeFormat.CODABAR
            ];
            hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, formats);
            
            // Buat reader dengan hints
            codeReader = new ZXing.BrowserMultiFormatReader(hints);
        }
        return codeReader;
    };

    let isScanning = false;
    let barcodeInputId = null;

    function openBarcodeScanner() {
        barcodeInputId = 'data-resi';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'qrcode-scanner-modal' } }));
    }

    function openScannerModal(inputId) {
        barcodeInputId = inputId;
        // Open the Filament modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'qrcode-scanner-modal' } }));
    }

    function closeScannerModal(event) {
        // Prevent any form submission
        if (event) {
            event.preventDefault();
        }
        // Close the Filament modal
        window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'qrcode-scanner-modal' } }));
        stopScanning(); // Make sure to stop the camera when the modal closes
        barcodeInputId = null;
    }

    async function startScanner(selectedDeviceId) {
        const reader = await initCodeReader();
        isScanning = true;
        reader.decodeFromVideoDevice(selectedDeviceId, 'scanner', (result, err) => {
            const scanArea = document.querySelector('.scan-area');
            if (result) {
                successSound.play().catch(e => console.warn('Could not play success sound:', e));

                const barcodeInput = document.getElementById(barcodeInputId);
                if (barcodeInput) {
                    // Set the value using Alpine to trigger Livewire's reactivity
                    if (barcodeInput._x_model) {
                        barcodeInput._x_model.set(result.text);
                    } else {
                        // Fallback if Alpine.js binding isn't available
                        barcodeInput.value = result.text;
                        barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
                scanArea.style.borderColor = 'green';
                stopScanning(); // Optionally stop scanning after successful read
                closeScannerModal(); // Close the modal after successful scan
            } else if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error(err);
            } else {
                scanArea.style.borderColor = 'red';
            }
        });
    }

    function stopScanning() {
        if (!isScanning) return;
        
        isScanning = false;
        const video = document.getElementById('scanner');
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
        if (video) {
            video.style.display = 'none';
        }
        
        if (codeReader) {
            try {
                codeReader.reset();
            } catch (error) {
                console.warn('Error resetting code reader:', error);
            }
        }
    }

    function startCamera() {
        initCodeReader().then((reader) => {
            reader.getVideoInputDevices().then((videoInputDevices) => {
                if (videoInputDevices.length === 0) {
                    alert("No camera devices found.");
                    return;
                }
                
                const rearCamera = videoInputDevices.find(device => 
                    device.label.toLowerCase().includes('back') || 
                    device.label.toLowerCase().includes('rear')
                );
                const selectedDeviceId = rearCamera ? rearCamera.deviceId : videoInputDevices[0].deviceId;

                navigator.mediaDevices.getUserMedia({ video: { deviceId: { exact: selectedDeviceId } } })
                    .then(function (stream) {
                        const video = document.getElementById('scanner');
                        video.srcObject = stream;
                        video.style.display = 'block'; // Ensure the video element is visible
                        startScanner(selectedDeviceId);
                    })
                    .catch(function (err) {
                        console.error("Error accessing the camera: ", err);
                        alert("Camera access is required to scan barcodes.");
                    });
            }).catch((err) => {
                console.error("Error getting video devices:", err);
                alert("Could not access camera devices.");
            });
        }).catch(err => {
            console.error("Error initializing code reader:", err);
            alert("Could not initialize barcode scanner.");
        });
    }

    // Listen for modal opening and start camera
    window.addEventListener('open-modal', event => {
        if (event.detail.id === 'qrcode-scanner-modal') {
            startCamera();
        }
    });

    // Listen for modal closing and stop camera
    window.addEventListener('close-modal', event => {
        if (event.detail.id === 'qrcode-scanner-modal') {
            stopScanning();
        }
    });

    // Tambahkan event listener untuk escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeScannerModal(event);
        }
    });

    // Preload the ZXing library when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        // Preload the library but don't initialize the reader yet
        loadZXingLibrary().catch(err => {
            console.warn('Failed to preload ZXing library:', err);
        });

        successSound.load();

        // Menambahkan event listener untuk klik di luar modal untuk menutupnya
        const modal = document.getElementById('qrcode-scanner-modal');
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeScannerModal(event);
            }
        });
    });
</script>