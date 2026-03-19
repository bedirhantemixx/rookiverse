/**
 * Pneumatics Calculator
 * Multi-cylinder pneumatic system simulation with compressor fill curves
 * Simulates 150-second match with pressure over time
 */
document.addEventListener('DOMContentLoaded', () => {
    const BORE_SIZES = [0.75, 1.0625, 1.5, 2.0, 2.5];
    const ROD_SIZES = [0.25, 0.3125, 0.375, 0.4375, 0.625];

    let pistons = [
        { bore: 1.5, rodD: 0.375, stroke: 12, qty: 1, extendPsi: 60, retractPsi: 60, period: 8, enabled: true },
        { bore: 0.75, rodD: 0.25, stroke: 6, qty: 1, extendPsi: 60, retractPsi: 60, period: 12, enabled: true },
    ];

    // Restore pistons from URL
    const state = MechTools.state.read();
    if (state.pistons) {
        try {
            const parsed = JSON.parse(state.pistons);
            if (Array.isArray(parsed) && parsed.length > 0) {
                pistons = parsed;
            }
        } catch(e) {}
    }

    MechTools.populateCompressorSelect('compressor', 'VIAIR 250C-IG');

    function renderPistons() {
        const container = document.getElementById('pistons-container');
        container.innerHTML = '';

        pistons.forEach((p, i) => {
            const div = document.createElement('div');
            div.className = `piston-item ${p.enabled ? '' : 'disabled'}`;
            div.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-600">${t('calc.js.cylinder_n')} ${i + 1}</span>
                    <div class="flex items-center gap-2">
                        <label class="toggle-switch">
                            <input type="checkbox" class="piston-toggle" data-index="${i}" ${p.enabled ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                        ${pistons.length > 1 ? `<button class="remove-piston text-red-400 hover:text-red-600 p-1" data-index="${i}"><i data-lucide="x" class="w-4 h-4"></i></button>` : ''}
                    </div>
                </div>
                <div class="input-row">
                    <label>${t('calc.js.bore')}</label>
                    <div class="input-group">
                        <select class="piston-bore" data-index="${i}" style="width:100px;padding:6px;border:1px solid #d1d5db;border-radius:8px;font-size:0.85rem;">
                            ${BORE_SIZES.map(b => `<option value="${b}" ${b === p.bore ? 'selected' : ''}>${b}"</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="input-row">
                    <label>${t('calc.js.rod_diameter')}</label>
                    <div class="input-group">
                        <input type="number" class="piston-rod" data-index="${i}" value="${p.rodD}" min="0.1" step="0.0625"
                            style="width:70px;padding:6px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.85rem;">
                        <span class="unit">in</span>
                    </div>
                </div>
                <div class="input-row">
                    <label>${t('calc.js.stroke')}</label>
                    <div class="input-group">
                        <input type="number" class="piston-stroke" data-index="${i}" value="${p.stroke}" min="0.5" step="0.5"
                            style="width:70px;padding:6px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.85rem;">
                        <span class="unit">in</span>
                    </div>
                </div>
                <div class="input-row">
                    <label>${t('calc.js.count')}</label>
                    <div class="input-group">
                        <input type="number" class="piston-qty" data-index="${i}" value="${p.qty}" min="1" max="10" step="1"
                            style="width:50px;padding:6px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.85rem;">
                    </div>
                </div>
                <div class="input-row">
                    <label>${t('calc.js.extend_psi')}</label>
                    <div class="input-group">
                        <input type="number" class="piston-extpsi" data-index="${i}" value="${p.extendPsi}" min="1" max="120" step="1"
                            style="width:60px;padding:6px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.85rem;">
                        <span class="unit">PSI</span>
                    </div>
                </div>
                <div class="input-row">
                    <label>${t('calc.js.retract_psi')}</label>
                    <div class="input-group">
                        <input type="number" class="piston-retpsi" data-index="${i}" value="${p.retractPsi}" min="1" max="120" step="1"
                            style="width:60px;padding:6px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.85rem;">
                        <span class="unit">PSI</span>
                    </div>
                </div>
                <div class="input-row">
                    <label>${t('calc.js.cycle_period')}</label>
                    <div class="input-group">
                        <input type="number" class="piston-period" data-index="${i}" value="${p.period}" min="1" step="1"
                            style="width:60px;padding:6px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.85rem;">
                        <span class="unit">s</span>
                    </div>
                </div>
            `;
            container.appendChild(div);
        });

        // Bind piston events
        const bindings = [
            { cls: '.piston-bore', key: 'bore', evt: 'change' },
            { cls: '.piston-rod', key: 'rodD', evt: 'input' },
            { cls: '.piston-stroke', key: 'stroke', evt: 'input' },
            { cls: '.piston-qty', key: 'qty', evt: 'input' },
            { cls: '.piston-extpsi', key: 'extendPsi', evt: 'input' },
            { cls: '.piston-retpsi', key: 'retractPsi', evt: 'input' },
            { cls: '.piston-period', key: 'period', evt: 'input' },
        ];
        bindings.forEach(({ cls, key, evt }) => {
            container.querySelectorAll(cls).forEach(el => {
                el.addEventListener(evt, (e) => {
                    pistons[e.target.dataset.index][key] = Number(e.target.value) || 1;
                    calculate();
                });
            });
        });
        container.querySelectorAll('.piston-toggle').forEach(el => {
            el.addEventListener('change', (e) => {
                pistons[e.target.dataset.index].enabled = e.target.checked;
                renderPistons();
                calculate();
            });
        });
        container.querySelectorAll('.remove-piston').forEach(el => {
            el.addEventListener('click', (e) => {
                pistons.splice(Number(e.currentTarget.dataset.index), 1);
                renderPistons();
                calculate();
            });
        });

        lucide.createIcons();
    }

    document.getElementById('add-piston').addEventListener('click', () => {
        pistons.push({ bore: 1.5, rodD: 0.375, stroke: 8, qty: 1, extendPsi: 60, retractPsi: 60, period: 8, enabled: true });
        renderPistons();
        calculate();
    });

    function calculate() {
        const compName = document.querySelector('[data-param="compressor"]').value;
        const compressor = MechTools.getCompressor(compName);
        const tankVolMl = MechTools.getInput('tankVol');
        const tankQty = MechTools.getInput('tankQty');
        const storePsi = MechTools.getInput('storePsi');

        const tankVolIn3 = MechTools.units.mlToIn3(tankVolMl) * tankQty;

        // Per-cylinder forces and air consumption
        const forcesEl = document.getElementById('cylinder-forces');
        forcesEl.innerHTML = '';

        // Build per-cylinder data
        const cylData = [];
        pistons.forEach((p, i) => {
            if (!p.enabled) return;

            const boreArea = Math.PI * Math.pow(p.bore / 2, 2); // in²
            const rodArea = Math.PI * Math.pow(p.rodD / 2, 2); // in²
            const retractArea = boreArea - rodArea; // annular area

            const extendVol = boreArea * p.stroke; // in³ per extension
            const retractVol = retractArea * p.stroke; // in³ per retraction
            const airPerCycle = extendVol + retractVol; // double-acting

            const extendForce = boreArea * p.extendPsi; // lbf
            const retractForce = retractArea * p.retractPsi; // lbf

            cylData.push({
                index: i,
                bore: p.bore,
                stroke: p.stroke,
                qty: p.qty,
                extendPsi: p.extendPsi,
                retractPsi: p.retractPsi,
                period: p.period,
                extendVol,
                retractVol,
                airPerCycle,
                extendForce,
                retractForce,
            });

            const row = document.createElement('div');
            row.className = 'output-row';
            row.innerHTML = `
                <label>${t('calc.js.cylinder_n')} ${i + 1} (${p.bore}" × ${p.stroke}")</label>
                <div class="input-group">
                    <span class="output-value">${extendForce.toFixed(1)} / ${retractForce.toFixed(1)}</span>
                    <span class="unit">${t('calc.js.lbf_ext_ret')}</span>
                </div>
            `;
            forcesEl.appendChild(row);
        });

        // Total air per cycle
        let totalAirPerCycle = 0;
        cylData.forEach(c => { totalAirPerCycle += c.airPerCycle * c.qty; });

        MechTools.setOutput('out-air-cycle', totalAirPerCycle, 2);
        MechTools.setOutput('out-total-vol', tankVolIn3, 1);

        // Match simulation: 150 seconds with 1-second timesteps
        const matchDuration = 150; // seconds
        const dt = 1; // 1 second timesteps
        let pressure = storePsi; // start at full pressure (absolute gauge)
        let compressorActive = true;
        let compressorOnTime = 0;
        const pressureData = [pressure];
        const timeData = [0];

        for (let t = 1; t <= matchDuration; t++) {
            // Compressor work: adds air to tank
            let compressorWork = 0;
            if (compressorActive && compressor && pressure < storePsi) {
                const cfm = compressorCFM(compressor, pressure);
                const flowIn3PerSec = cfm * 1728 / 60;
                // Pressure increase from compressor
                compressorWork = (flowIn3PerSec * 14.7) / tankVolIn3 * dt;
                compressorOnTime += dt;
            }

            // Cylinder work: consumes air from tank
            let cylinderWork = 0;
            cylData.forEach(c => {
                // Does this cylinder fire this second?
                if (c.period > 0 && t % c.period === 0) {
                    // Air consumed at current pressure, converted to pressure drop
                    // Extend uses extendVol at extendPsi, retract uses retractVol at retractPsi
                    const extendAirAtAtm = c.extendVol * (c.extendPsi + 14.7) / 14.7;
                    const retractAirAtAtm = c.retractVol * (c.retractPsi + 14.7) / 14.7;
                    const totalAirAtAtm = (extendAirAtAtm + retractAirAtAtm) * c.qty;
                    // Pressure drop = air_consumed_atm * atm / tank_volume
                    cylinderWork = totalAirAtAtm * 14.7 / tankVolIn3;
                }
            });

            pressure += compressorWork - cylinderWork;
            pressure = Math.max(0, Math.min(pressure, storePsi));

            // Compressor turns off above storePsi, on below (hysteresis)
            if (pressure >= storePsi) compressorActive = false;
            if (pressure < storePsi - 5) compressorActive = true;

            pressureData.push(pressure);
            timeData.push(t);
        }

        // Duty cycle
        const dutyCycle = (compressorOnTime / matchDuration) * 100;
        MechTools.setOutput('out-duty-cycle', dutyCycle, 1);

        // Min pressure during match
        const minPressure = Math.min(...pressureData);
        MechTools.setOutput('out-min-pressure', minPressure, 1);

        // Recommended KOP tanks (574 mL each) to stay above 20 PSI
        let recTanks = tankQty;
        if (minPressure < 20) {
            for (let n = tankQty + 1; n <= 20; n++) {
                const testVolIn3 = MechTools.units.mlToIn3(tankVolMl) * n;
                let testPressure = storePsi;
                let testCompActive = true;
                let testMin = storePsi;

                for (let t = 1; t <= matchDuration; t++) {
                    let cw = 0;
                    if (testCompActive && compressor && testPressure < storePsi) {
                        const cfm = compressorCFM(compressor, testPressure);
                        cw = (cfm * 1728 / 60 * 14.7) / testVolIn3 * dt;
                    }
                    let cyW = 0;
                    cylData.forEach(c => {
                        if (c.period > 0 && t % c.period === 0) {
                            const ea = c.extendVol * (c.extendPsi + 14.7) / 14.7;
                            const ra = c.retractVol * (c.retractPsi + 14.7) / 14.7;
                            cyW = (ea + ra) * c.qty * 14.7 / testVolIn3;
                        }
                    });
                    testPressure += cw - cyW;
                    testPressure = Math.max(0, Math.min(testPressure, storePsi));
                    if (testPressure >= storePsi) testCompActive = false;
                    if (testPressure < storePsi - 5) testCompActive = true;
                    testMin = Math.min(testMin, testPressure);
                }
                if (testMin >= 20) {
                    recTanks = n;
                    break;
                }
            }
        }
        MechTools.setOutput('out-rec-tanks', recTanks, 0);

        // Actuations from full storage (no compressor)
        if (totalAirPerCycle > 0) {
            const usableAirIn3 = tankVolIn3 * (storePsi - 20) / 14.7; // stop at 20 PSI
            const avgWorkPsi = (storePsi + 20) / 2;
            const airPerCycleAtAtm = totalAirPerCycle * (avgWorkPsi + 14.7) / 14.7;
            const actuations = Math.floor(usableAirIn3 / airPerCycleAtAtm * tankQty);
            MechTools.setOutput('out-actuations', Math.max(0, actuations), 0);
        } else {
            MechTools.setOutput('out-actuations', '—');
        }

        // Fill time (0 to storePsi)
        if (compressor) {
            let fillP = 0;
            let fillTime = 0;
            const fillDt = 0.1;
            while (fillP < storePsi && fillTime < 300) {
                const cfm = compressorCFM(compressor, fillP);
                const flowIn3PerSec = cfm * 1728 / 60;
                fillP += (flowIn3PerSec * 14.7) / tankVolIn3 * fillDt;
                fillTime += fillDt;
            }
            MechTools.setOutput('out-fill-time', fillTime, 1);
        }

        // Update chart
        const labels = timeData.map(t => t.toString());
        MechTools.updateChart('pneumatics-chart',
            [{
                label: t('calc.js.tank_pressure_psi'),
                data: pressureData,
                borderColor: MechTools.colors.gold,
                backgroundColor: 'rgba(229, 174, 50, 0.1)',
                fill: true,
            }],
            labels
        );

        // Save state
        MechTools.state.write({ pistons: JSON.stringify(pistons) });
        MechTools.syncStateFromInputs();
    }

    // Init chart
    MechTools.createChart('pneumatics-chart', {
        options: {
            scales: {
                x: { title: { display: true, text: t('calc.js.time_s') } },
                y: { title: { display: true, text: t('calc.js.pressure_psi') }, min: 0 }
            }
        }
    });

    renderPistons();
    MechTools.bindInputs(calculate);
});
