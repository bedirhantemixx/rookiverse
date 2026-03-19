/**
 * Compressors Info Page
 * Table of compressor specs + CFM vs pressure chart
 */
document.addEventListener('DOMContentLoaded', () => {
    let sortKey = 'cfm100';
    let sortDir = -1;

    // Pre-calculate CFM at reference pressures
    const compData = COMPRESSORS.map(c => {
        const weightLb = (c.weight || 0) * 2.20462;
        const cfm0 = compressorCFM(c, 0);
        const cfm50 = compressorCFM(c, 50);
        const cfm100 = compressorCFM(c, 100);
        return {
            name: c.name,
            weightLb,
            cfm0,
            cfm50,
            cfm100,
            cfmPerLb: weightLb > 0 ? cfm100 / weightLb : 0,
            _comp: c,
        };
    });

    function renderTable() {
        const tbody = document.getElementById('comp-body');
        tbody.innerHTML = '';

        const sorted = [...compData].sort((a, b) => {
            let va = a[sortKey], vb = b[sortKey];
            if (typeof va === 'string') return sortDir * va.localeCompare(vb);
            return sortDir * (va - vb);
        });

        sorted.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-medium text-gray-800 whitespace-nowrap">${row.name}</td>
                <td>${row.weightLb.toFixed(2)}</td>
                <td>${row.cfm0.toFixed(2)}</td>
                <td>${row.cfm50.toFixed(2)}</td>
                <td>${row.cfm100.toFixed(2)}</td>
                <td>${row.cfmPerLb.toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Sortable headers
    document.querySelectorAll('#comp-table th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const key = th.dataset.sort;
            if (sortKey === key) {
                sortDir *= -1;
            } else {
                sortKey = key;
                sortDir = key === 'name' ? 1 : -1;
            }
            document.querySelectorAll('#comp-table th').forEach(h => h.classList.remove('text-gold'));
            th.classList.add('text-gold');
            renderTable();
        });
    });

    // CFM vs Pressure chart
    const chartColors = [
        MechTools.colors.gold, MechTools.colors.blue, MechTools.colors.green,
        MechTools.colors.red, MechTools.colors.purple, MechTools.colors.orange,
        MechTools.colors.cyan, MechTools.colors.pink,
    ];

    const pressureRange = [];
    for (let p = 0; p <= 120; p += 5) pressureRange.push(p);

    const datasets = COMPRESSORS.map((c, i) => ({
        label: c.name,
        data: pressureRange.map(p => compressorCFM(c, p)),
        borderColor: chartColors[i % chartColors.length],
        backgroundColor: 'transparent',
    }));

    MechTools.createChart('comp-chart', {
        data: {
            labels: pressureRange.map(p => p.toString()),
            datasets,
        },
        options: {
            scales: {
                x: { title: { display: true, text: t('calc.js.pressure_psi') } },
                y: { title: { display: true, text: t('calc.js.flow_rate_cfm') }, min: 0 }
            },
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    renderTable();
});
