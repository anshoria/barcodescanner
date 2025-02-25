import './bootstrap';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

// Ekspor ke window untuk diakses dari HTML
window.Html5Qrcode = Html5Qrcode;

class BarcodeScanner {
    constructor() {
        this.html5QrCode = null;
        this.currentCamera = 'environment';
        this.beepAudio = null;
        this.modalContainer = null;
    }

    // Initialize beep sound
    initBeepSound() {
        if (this.beepAudio) return this.beepAudio;
        
        this.beepAudio = new Audio();
        this.beepAudio.src = 'data:audio/mp3;base64,SUQzAwAAAAACHVRJVDIAAAAZAAAARWZmZWN0cyAtIEJhcmNvZGUgU2Nhbm5lcgBUWUVSAAAABQAAADIwMjMAVENPTQAAAAUAAABTRlgAVENPTgAAAAUAAABTRlgAVEFMQgAAAAUAAABTRlgAVFJDSwAAAAIAAAAwAFRZRVIAAAAFAAAAMjAyMwBUQ09NAAAABQAAAFNGWABUUEUxAAAABQAAAFNGWABUQ09OAAAABQAAAFNGWABUUFVCAAAABQAAAFNGWABUUlRBAAAABQAAAEQCDCMAAFRDT00AAAAGAAAAYmVlcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//uQxAADwAABpAAAACAAADSAAAAETEFNRTMuMTAwVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/7kMQAA8AAAaQAAAAgAAA0gAAABFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVQ==';
        this.beepAudio.preload = 'auto';
        this.beepAudio.volume = 1.0;
        
        return this.beepAudio;
    }

    // Play beep sound
    playBeep() {
        const audio = this.initBeepSound();
        audio.currentTime = 0;
        
        return new Promise((resolve) => {
            const playAttempt = audio.play();
            
            if (playAttempt) {
                playAttempt.then(resolve).catch(() => {
                    console.log("Suara gagal diputar");
                    resolve();
                });
            } else {
                resolve();
            }
        });
    }

    // Vibrate device
    vibrateDevice() {
        if (navigator.vibrate) {
            navigator.vibrate(100);
        }
    }

    // Create and initialize scanner modal
    createScannerModal() {
        this.modalContainer = document.createElement('div');
        this.modalContainer.id = 'barcode-scanner-modal';
        this.modalContainer.style = 'position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;z-index:9999;';
        
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
        
        this.modalContainer.appendChild(modalContent);
        document.body.appendChild(this.modalContainer);
        
        // Setup event listeners
        document.getElementById('flip-button').addEventListener('click', () => {
            const newFacingMode = this.currentCamera === 'environment' ? 'user' : 'environment';
            this.restartCamera(newFacingMode);
        });
        
        document.getElementById('cancel-button').addEventListener('click', () => {
            this.closeScanner();
        });
    }

    // Start barcode scanner with optimized settings
    startScanner(facingMode = 'environment') {
        this.currentCamera = facingMode;
        const statusElement = document.getElementById('scanner-status');

        try {
            this.html5QrCode = new Html5Qrcode("reader");

            // Highly optimized configuration for fast scanning
            const config = {
                fps: 30,                // Increased for faster scanning
                qrbox: {
                    width: 280,
                    height: 180
                },
                rememberLastUsedCamera: true,
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.EAN_13
                ],
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                },
                disableFlip: true,       // Reduces processing overhead
                aspectRatio: 1.33,
                videoConstraints: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: { exact: facingMode }
                }
            };

            // Optimized scanner callback
            const successCallback = (decodedText) => {
                // Stop scanning immediately for better performance
                this.html5QrCode.stop().then(() => {
                    // Multi-sensory feedback in parallel
                    Promise.all([
                        this.playBeep(),
                        new Promise(resolve => {
                            this.vibrateDevice();
                            resolve();
                        })
                    ]).then(() => {
                        // Update status
                        statusElement.innerText = 'Barcode terdeteksi!';
                        statusElement.style.color = '#10b981';
                        statusElement.style.fontWeight = 'bold';
                        
                        try {
                            const wireId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                            window.Livewire.find(wireId).set('data.resi', decodedText);
                            
                            // Remove modal immediately
                            if (this.modalContainer) {
                                this.modalContainer.remove();
                                this.modalContainer = null;
                            }
                        } catch (error) {
                            console.error('Error setting form value:', error);
                            
                            // Show error message
                            statusElement.innerText = 'Error: ' + error.message;
                            statusElement.style.color = '#ef4444';
                            
                            // Close modal after showing error
                            setTimeout(() => {
                                if (this.modalContainer) {
                                    this.modalContainer.remove();
                                    this.modalContainer = null;
                                }
                            }, 2000);
                        }
                    });
                }).catch(err => {
                    console.error("Error stopping scanner:", err);
                    if (this.modalContainer) {
                        this.modalContainer.remove();
                        this.modalContainer = null;
                    }
                });
            };

            // Start scanning with silent error handling
            this.html5QrCode.start(
                { facingMode: facingMode },
                config,
                successCallback,
                () => {} // Silent error handler
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

    // Restart camera with better error handling
    restartCamera(facingMode) {
        if (this.html5QrCode) {
            this.html5QrCode.stop().finally(() => {
                this.startScanner(facingMode);
            });
        }
    }

    // Close scanner with improved cleanup
    closeScanner() {
        if (this.html5QrCode) {
            try {
                this.html5QrCode.stop().finally(() => {
                    if (this.modalContainer) {
                        this.modalContainer.remove();
                        this.modalContainer = null;
                    }
                    this.html5QrCode = null;
                });
            } catch (e) {
                console.error("Error stopping scanner:", e);
                if (this.modalContainer) {
                    this.modalContainer.remove();
                    this.modalContainer = null;
                }
                this.html5QrCode = null;
            }
        } else if (this.modalContainer) {
            this.modalContainer.remove();
            this.modalContainer = null;
        }
    }

    // Open scanner
    open() {
        this.createScannerModal();
        this.startScanner(this.currentCamera);
    }
}

// Singleton instance
const scanner = new BarcodeScanner();

// Expose to global scope for HTML onclick access
window.openBarcodeScanner = function() {
    scanner.open();
};

// Optional: Also export as ES module for import in other files
export function openBarcodeScanner() {
    scanner.open();
}