/**
 * Motors Info Page
 * Sortable table of FRC motor specs + performance curves
 */
document.addEventListener('DOMContentLoaded', () => {
    let sortKey = 'maxPower';
    let sortDir = -1; // descending

    function renderTable() {
        const tbody = document.getElementById('motors-body');
        tbody.innerHTML = '';

        const data = MOTORS.map(m => ({
            ...m,
            powerToWeight: m.maxPower / m.totalWeight,
            torqueDensity: m.stallTorque / m.totalWeight,
            power20: motorPowerAtCurrentLimit(m, 20),
            power40: motorPowerAtCurrentLimit(m, 40),
            power60: motorPowerAtCurrentLimit(m, 60),
            kM: m.stallTorque / Math.sqrt(m.stallCurrent * m.stallCurrent * m.resistance),
        }));

        data.sort((a, b) => {
            let va = a[sortKey], vb = b[sortKey];
            if (typeof va === 'string') {
                return sortDir * va.localeCompare(vb);
            }
            return sortDir * (va - vb);
        });

        data.forEach(m => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-medium text-gray-800 whitespace-nowrap">${m.name}</td>
                <td>${(m.totalWeight * 2.20462).toFixed(2)}</td>
                <td>${m.freeSpeed.toLocaleString()}</td>
                <td>${m.stallTorque.toFixed(2)}</td>
                <td>${m.stallCurrent.toFixed(0)}</td>
                <td>${m.freeCurrent.toFixed(1)}</td>
                <td>${m.power20.toFixed(0)}</td>
                <td>${m.power40.toFixed(0)}</td>
                <td>${m.power60.toFixed(0)}</td>
                <td>${(m.power40 / (m.totalWeight * 2.20462)).toFixed(0)}</td>
                <td>${m.resistance.toFixed(4)}</td>
                <td>${m.kT.toFixed(4)}</td>
                <td>${(m.freeSpeed / 12).toFixed(0)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Sortable headers
    document.querySelectorAll('#motors-table th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const key = th.dataset.sort;
            if (sortKey === key) {
                sortDir *= -1;
            } else {
                sortKey = key;
                sortDir = key === 'name' ? 1 : -1;
            }

            document.querySelectorAll('#motors-table th').forEach(h => h.classList.remove('text-gold'));
            th.classList.add('text-gold');

            renderTable();
        });
    });

    // Motor curves chart
    const select = document.getElementById('motor-chart-select');
    MOTORS.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.name;
        opt.textContent = m.name;
        if (m.name === 'Kraken X60 (FOC)') opt.selected = true;
        select.appendChild(opt);
    });

    MechTools.createChart('motor-curves-chart', {
        options: {
            scales: {
                x: { title: { display: true, text: 'Speed (RPM)' } },
                y: { title: { display: true, text: 'Torque (N·m)' }, min: 0, position: 'left' },
                y1: {
                    position: 'right',
                    title: { display: true, text: 'Power (W)' },
                    min: 0,
                    grid: { drawOnChartArea: false },
                },
                y2: {
                    position: 'right',
                    title: { display: true, text: 'Current (A)' },
                    min: 0,
                    grid: { drawOnChartArea: false },
                }
            }
        }
    });

    function updateCurves() {
        const motor = MechTools.getMotor(select.value);
        if (!motor) return;

        const steps = 50;
        const labels = [];
        const torqueData = [];
        const currentData = [];
        const powerData = [];

        for (let i = 0; i <= steps; i++) {
            const rpm = (motor.freeSpeed * i) / steps;
            labels.push(rpm.toFixed(0));

            const omega = rpm * Math.PI / 30;
            const backEmf = omega / motor.kV;
            const current = (12 - backEmf) / motor.resistance;
            const torque = motor.kT * Math.max(0, current - motor.freeCurrent);
            const power = torque * omega;

            torqueData.push(torque);
            currentData.push(current);
            powerData.push(power);
        }

        MechTools.updateChart('motor-curves-chart', [
            {
                label: 'Torque (N·m)',
                data: torqueData,
                borderColor: MechTools.colors.gold,
                yAxisID: 'y',
            },
            {
                label: 'Current (A)',
                data: currentData,
                borderColor: MechTools.colors.red,
                yAxisID: 'y2',
            },
            {
                label: 'Power (W)',
                data: powerData,
                borderColor: MechTools.colors.blue,
                yAxisID: 'y1',
            },
        ], labels);
    }

    select.addEventListener('change', updateCurves);

    renderTable();
    updateCurves();
});
