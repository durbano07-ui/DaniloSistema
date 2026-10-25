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

// Tablas responsivas: copia el nombre de cada columna a sus celdas para mostrarlas como tarjetas en móvil
const etiquetarTablas = () => {
    document.querySelectorAll('table.tabla-responsiva').forEach((tabla) => {
        const encabezados = [...tabla.querySelectorAll(':scope > thead > tr:first-child > th')].map((th) =>
            th.querySelector('.sr-only') ? '' : th.textContent.trim().replace(/\s+/g, ' ')
        );

        tabla.querySelectorAll(':scope > tbody > tr').forEach((fila) => {
            const celdas = [...fila.children].filter((c) => c.tagName === 'TD' || c.tagName === 'TH');
            if (celdas.length === 1 && celdas[0].colSpan > 1) {
                fila.classList.add('fila-completa');
                return;
            }

            let columna = 0;
            celdas.forEach((celda) => {
                const nombre = encabezados[columna] ?? '';
                if (!celda.hasAttribute('data-label')) {
                    celda.setAttribute('data-label', nombre);
                }
                if (nombre === '' || /^(acci[oó]n(es)?|quitar|anular|eliminar)$/i.test(nombre)) {
                    celda.classList.add('celda-acciones');
                }
                columna += celda.colSpan || 1;
            });
        });
    });
};

// Vuelve a etiquetar cuando Alpine agrega filas (pedidos por escáner, actas con factura)
let etiquetadoPendiente = false;
new MutationObserver(() => {
    if (etiquetadoPendiente) {
        return;
    }
    etiquetadoPendiente = true;
    requestAnimationFrame(() => {
        etiquetadoPendiente = false;
        etiquetarTablas();
    });
}).observe(document.documentElement, { childList: true, subtree: true });

document.addEventListener('DOMContentLoaded', etiquetarTablas);

Alpine.start();
