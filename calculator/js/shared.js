/**
 * MechTools Shared Utilities
 * URL state management, unit conversion, input binding, share button
 */

// Translation helper for JS-facing strings
function t(key) {
    return (window.T && window.T[key]) || key;
}

const MechTools = {
    // ─── Unit Conversion ───
    units: {
        // Length
        inToM: (v) => v * 0.0254,
        mToIn: (v) => v / 0.0254,
        inToCm: (v) => v * 2.54,
        cmToIn: (v) => v / 2.54,
        inToMm: (v) => v * 25.4,
        mmToIn: (v) => v / 25.4,
        ftToIn: (v) => v * 12,
        inToFt: (v) => v / 12,
        ftToM: (v) => v * 0.3048,
        mToFt: (v) => v / 0.3048,

        // Mass
        lbToKg: (v) => v * 0.453592,
        kgToLb: (v) => v / 0.453592,
        ozToKg: (v) => v * 0.0283495,
        kgToOz: (v) => v / 0.0283495,

        // Angular
        rpmToRadS: (v) => v * Math.PI / 30,
        radSToRpm: (v) => v * 30 / Math.PI,
        degToRad: (v) => v * Math.PI / 180,
        radToDeg: (v) => v * 180 / Math.PI,

        // Force / Torque
        lbfToN: (v) => v * 4.44822,
        nToLbf: (v) => v / 4.44822,
        nmToInLb: (v) => v * 8.8507,
        inLbToNm: (v) => v / 8.8507,

        // Speed
        fpsToMps: (v) => v * 0.3048,
        mpsToFps: (v) => v / 0.3048,

        // Pressure
        psiToKpa: (v) => v * 6.89476,
        kpaToPsi: (v) => v / 6.89476,

        // Volume
        mlToIn3: (v) => v * 0.0610237,
        in3ToMl: (v) => v / 0.0610237,
    },

    // ─── URL State Management ───
    state: {
        /**
         * Read all query params into an object
         */
        read() {
            const params = new URLSearchParams(window.location.search);
            const state = {};
            for (const [key, value] of params) {
                state[key] = value;
            }
            return state;
        },

        /**
         * Update URL query params without reloading
         */
        write(params) {
            const url = new URL(window.location);
            for (const [key, value] of Object.entries(params)) {
                if (value !== null && value !== undefined && value !== '') {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            }
            window.history.replaceState({}, '', url);
        },

        /**
         * Get a single param with default
         */
        get(key, defaultValue) {
            const params = new URLSearchParams(window.location.search);
            const val = params.get(key);
            if (val === null) return defaultValue;
            const num = Number(val);
            return isNaN(num) ? val : num;
        }
    },

    // ─── Input Binding ───
    /**
     * Bind inputs to auto-recalculate. Call with a calculate function.
     * Reads all [data-param] inputs, syncs with URL state, and recalculates on change.
     */
    bindInputs(calculateFn) {
        const inputs = document.querySelectorAll('[data-param]');
        const state = this.state.read();

        // Restore state from URL
        inputs.forEach(input => {
            const key = input.dataset.param;
            if (state[key] !== undefined) {
                if (input.type === 'checkbox') {
                    input.checked = state[key] === 'true' || state[key] === '1';
                } else {
                    input.value = state[key];
                }
            }
        });

        // Bind change handlers
        inputs.forEach(input => {
            const events = input.type === 'checkbox' ? ['change'] : ['input', 'change'];
            events.forEach(evt => {
                input.addEventListener(evt, () => {
                    this.syncStateFromInputs();
                    calculateFn();
                });
            });
        });

        // Initial calculation
        calculateFn();
    },

    /**
     * Sync all [data-param] inputs to URL state
     */
    syncStateFromInputs() {
        const inputs = document.querySelectorAll('[data-param]');
        const params = {};
        inputs.forEach(input => {
            const key = input.dataset.param;
            if (input.type === 'checkbox') {
                params[key] = input.checked ? '1' : '0';
            } else {
                params[key] = input.value;
            }
        });
        this.state.write(params);
    },

    /**
     * Get value of a [data-param] input
     */
    getInput(param) {
        const el = document.querySelector(`[data-param="${param}"]`);
        if (!el) return null;
        if (el.type === 'checkbox') return el.checked;
        return Number(el.value);
    },

    /**
     * Set an output element value
     */
    setOutput(id, value, decimals = 3) {
        const el = document.getElementById(id);
        if (!el) return;
        if (typeof value === 'number') {
            if (isNaN(value) || !isFinite(value)) {
                el.textContent = '—';
            } else {
                el.textContent = Number(value.toFixed(decimals));
            }
        } else {
            el.textContent = value;
        }
    },

    // ─── Share Button ───
    initShareButton() {
        const btn = document.getElementById('share-btn');
        if (!btn) return;
        btn.addEventListener('click', () => {
            navigator.clipboard.writeText(window.location.href).then(() => {
                btn.classList.add('copied');
                const orig = btn.innerHTML;
                btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> ' + t('calc.js.copied');
                lucide.createIcons();
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = orig;
                    lucide.createIcons();
                }, 2000);
            });
        });
    },

    // ─── Chart Helpers ───
    charts: {},

    createChart(canvasId, config) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        // Destroy existing chart
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        const defaultConfig = {
            type: 'line',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26,32,44,0.9)',
                        titleFont: { size: 13 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { font: { size: 11 } }
                    }
                },
                elements: {
                    point: { radius: 0 },
                    line: { borderWidth: 2 }
                }
            }
        };

        // Deep merge
        const merged = this.deepMerge(defaultConfig, config);
        this.charts[canvasId] = new Chart(ctx, merged);
        return this.charts[canvasId];
    },

    updateChart(canvasId, datasets, labels) {
        const chart = this.charts[canvasId];
        if (!chart) return;
        if (labels) chart.data.labels = labels;
        chart.data.datasets = datasets;
        chart.update('none');
    },

    deepMerge(target, source) {
        const output = Object.assign({}, target);
        if (this.isObject(target) && this.isObject(source)) {
            Object.keys(source).forEach(key => {
                if (this.isObject(source[key])) {
                    if (!(key in target)) {
                        Object.assign(output, { [key]: source[key] });
                    } else {
                        output[key] = this.deepMerge(target[key], source[key]);
                    }
                } else {
                    Object.assign(output, { [key]: source[key] });
                }
            });
        }
        return output;
    },

    isObject(item) {
        return item && typeof item === 'object' && !Array.isArray(item);
    },

    // ─── Chart color palette ───
    colors: {
        gold: '#E5AE32',
        blue: '#3B82F6',
        green: '#10B981',
        red: '#EF4444',
        purple: '#8B5CF6',
        orange: '#F97316',
        cyan: '#06B6D4',
        pink: '#EC4899',
    },

    // ─── Format helpers ───
    fmt(value, decimals = 3) {
        if (typeof value !== 'number' || isNaN(value) || !isFinite(value)) return '—';
        return Number(value.toFixed(decimals)).toLocaleString();
    },

    // ─── Motor select helper ───
    populateMotorSelect(selectId, defaultMotor) {
        const select = document.querySelector(`[data-param="${selectId}"]`) || document.getElementById(selectId);
        if (!select || typeof MOTORS === 'undefined') return;

        select.innerHTML = '';
        MOTORS.forEach(motor => {
            const opt = document.createElement('option');
            opt.value = motor.name;
            opt.textContent = motor.name;
            if (motor.name === defaultMotor) opt.selected = true;
            select.appendChild(opt);
        });
    },

    getMotor(name) {
        if (typeof MOTORS === 'undefined') return null;
        return MOTORS.find(m => m.name === name) || MOTORS[0];
    },

    populateCompressorSelect(selectId, defaultComp) {
        const select = document.querySelector(`[data-param="${selectId}"]`) || document.getElementById(selectId);
        if (!select || typeof COMPRESSORS === 'undefined') return;

        select.innerHTML = '';
        COMPRESSORS.forEach(comp => {
            const opt = document.createElement('option');
            opt.value = comp.name;
            opt.textContent = comp.name;
            if (comp.name === defaultComp) opt.selected = true;
            select.appendChild(opt);
        });
    },

    getCompressor(name) {
        if (typeof COMPRESSORS === 'undefined') return null;
        return COMPRESSORS.find(c => c.name === name) || COMPRESSORS[0];
    }
};

// Auto-init share button on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    MechTools.initShareButton();
});
