import Alpine from 'alpinejs';
import JsBarcode from 'jsbarcode';
import { Html5Qrcode } from 'html5-qrcode';

window.Alpine = Alpine;
window.JsBarcode = JsBarcode;
window.Html5Qrcode = Html5Qrcode;

// Sintetizador Web Audio API para feedback sonoro inmediato del escáner (sin archivos externos)
window.playScannerBeep = (type = 'success') => {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.connect(gain);
        gain.connect(ctx.destination);

        if (type === 'success') {
            // Tono agudo y limpio típico de pistola Zebra / Honeywell (1800Hz)
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1800, ctx.currentTime);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.12);
        } else if (type === 'error') {
            // Tono grave de error / rechazo (250Hz)
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(250, ctx.currentTime);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.28);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.28);
        }
    } catch (e) {
        // En navegadores con autoplay policy restringida antes de interacción
    }
};

Alpine.start();
