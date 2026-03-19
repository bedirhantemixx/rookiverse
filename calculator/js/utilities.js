/**
 * Utilities & Cheat Sheets
 * Hole size calculator, spacer calculator, bearings table, mounting cheat sheet
 */
document.addEventListener('DOMContentLoaded', () => {

    // ─── Hole Size Calculator ───

    // Imperial bolt sizes: [name, tapped hole, close fit, free fit] in inches
    const IMPERIAL_BOLTS = [
        ['1/4"-20', 0.201, 0.257, 0.266],
        ['1/4"-28', 0.213, 0.257, 0.266],
        ['#10-24', 0.149, 0.196, 0.201],
        ['#10-32', 0.159, 0.196, 0.201],
        ['#8-32',  0.136, 0.170, 0.177],
        ['#6-32',  0.107, 0.144, 0.150],
        ['#4-40',  0.089, 0.116, 0.120],
        ['#2-56',  0.070, 0.089, 0.096],
        ['#0-80',  0.047, 0.064, 0.067],
        ['5/16"-18', 0.257, 0.323, 0.332],
        ['3/8"-16',  0.313, 0.386, 0.397],
    ];

    // Metric bolt sizes: [name, tapped hole, normal fit, loose fit] in inches
    const METRIC_BOLTS = [
        ['M10-1.5',  0.335, 0.397, 0.406],
        ['M8-1.25',  0.266, 0.323, 0.339],
        ['M6-1.0',   0.197, 0.246, 0.257],
        ['M5-0.8',   0.165, 0.205, 0.217],
        ['M4-0.7',   0.130, 0.165, 0.177],
        ['M3-0.5',   0.098, 0.126, 0.130],
        ['M2.5-0.45',0.081, 0.102, 0.106],
        ['M2-0.4',   0.063, 0.083, 0.087],
        ['M1.6-0.35',0.049, 0.067, 0.069],
        ['M1.4-0.3', 0.043, 0.059, 0.061],
        ['M1.2-0.25',0.037, 0.051, 0.053],
        ['M1-0.25',  0.030, 0.043, 0.045],
    ];

    function updateHoleSizes() {
        const hole = parseFloat(document.getElementById('hole-input').value) || 0;

        function renderBoltTable(bolts, containerId, fitLabels) {
            const container = document.getElementById(containerId);
            // Score each bolt by how close any of its hole sizes match
            const scored = bolts.map(b => {
                const diffs = [
                    Math.abs(b[1] - hole),
                    Math.abs(b[2] - hole),
                    Math.abs(b[3] - hole),
                ];
                return { bolt: b, minDiff: Math.min(...diffs), diffs };
            }).sort((a, b) => a.minDiff - b.minDiff);

            let html = '<table class="calc-table" style="font-size:0.8rem;">';
            html += `<thead><tr><th>Bolt</th><th>${fitLabels[0]}</th><th>${fitLabels[1]}</th><th>${fitLabels[2]}</th></tr></thead><tbody>`;
            scored.forEach((s, idx) => {
                const b = s.bolt;
                const highlight = idx === 0 ? 'background:#d1fae5;' : idx <= 3 ? 'background:#fef9c3;' : '';
                html += `<tr style="${highlight}">`;
                html += `<td class="font-medium">${b[0]}</td>`;
                for (let j = 1; j <= 3; j++) {
                    const match = Math.abs(b[j] - hole) < 0.002;
                    html += `<td${match ? ' style="font-weight:700;color:#E5AE32;"' : ''}>${b[j].toFixed(3)}"</td>`;
                }
                html += '</tr>';
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        renderBoltTable(IMPERIAL_BOLTS, 'hole-imperial', [t('calc.js.tapped'), t('calc.js.close_fit'), t('calc.js.free_fit')]);
        renderBoltTable(METRIC_BOLTS, 'hole-metric', [t('calc.js.tapped'), t('calc.js.normal_fit'), t('calc.js.loose_fit')]);
    }

    document.getElementById('hole-input').addEventListener('input', updateHoleSizes);
    updateHoleSizes();

    // ─── Spacer Calculator ───

    const SPACER_SIZES = [2, 1, 0.5, 0.25, 0.125, 0.0625, 0.03125]; // inches, largest first

    function updateSpacers() {
        const target = parseFloat(document.getElementById('spacer-input').value) || 0;
        let remaining = target;
        const result = [];

        // Greedy algorithm
        SPACER_SIZES.forEach(size => {
            const count = Math.floor(remaining / size + 0.0001); // small epsilon for float
            if (count > 0) {
                result.push({ size, count });
                remaining -= count * size;
            }
        });

        remaining = Math.max(0, remaining);

        let html = '<div class="space-y-1">';
        if (result.length === 0) {
            html += '<p class="text-gray-400 text-sm">' + t('calc.js.no_spacers') + '</p>';
        } else {
            result.forEach(r => {
                const label = r.size >= 1 ? `${r.size}"` : fractionLabel(r.size);
                html += `<div class="flex justify-between items-center py-1 border-b border-gray-100">
                    <span class="text-sm font-medium">${label} ${t('calc.js.spacer')}</span>
                    <span class="output-value" style="min-width:auto;padding:4px 8px;">${r.count}×</span>
                </div>`;
            });
            if (remaining > 0.0005) {
                html += `<div class="mt-2 text-xs text-red-500">${t('calc.js.remaining')} ${remaining.toFixed(4)}" ${t('calc.js.no_standard')}</div>`;
            } else {
                html += `<div class="mt-2 text-xs text-green-600">${t('calc.js.exact_match')}</div>`;
            }
        }
        html += '</div>';

        document.getElementById('spacer-result').innerHTML = html;
    }

    function fractionLabel(size) {
        const fracs = { 0.5: '1/2', 0.25: '1/4', 0.125: '1/8', 0.0625: '1/16', 0.03125: '1/32' };
        return (fracs[size] || size.toFixed(4)) + '"';
    }

    document.getElementById('spacer-input').addEventListener('input', updateSpacers);
    updateSpacers();

    // ─── Bearings Table ───

    const BEARINGS = [
        { type: 'Flanged', bore: '1/2" Hex', id: 0.504, od: 0.875, flangedOd: 1.000, height: 0.281 },
        { type: 'Flanged', bore: '1/2" Round', id: 0.500, od: 0.875, flangedOd: 1.000, height: 0.281 },
        { type: 'Flanged', bore: '3/8" Hex', id: 0.378, od: 0.750, flangedOd: 0.875, height: 0.250 },
        { type: 'Flanged', bore: '3/8" Round', id: 0.375, od: 0.750, flangedOd: 0.875, height: 0.250 },
        { type: 'Flanged', bore: '8mm Round', id: 0.315, od: 0.866, flangedOd: 0.984, height: 0.276 },
        { type: 'Flanged', bore: '6mm Round', id: 0.236, od: 0.748, flangedOd: 0.866, height: 0.236 },
        { type: 'Standard', bore: '1/2" Round', id: 0.500, od: 1.125, flangedOd: null, height: 0.313 },
        { type: 'Standard', bore: '3/8" Round', id: 0.375, od: 0.875, flangedOd: null, height: 0.281 },
        { type: 'Standard', bore: '8mm Round', id: 0.315, od: 0.866, flangedOd: null, height: 0.276 },
        { type: 'Needle', bore: '1/2" Hex', id: 0.504, od: 0.625, flangedOd: null, height: 0.500 },
        { type: 'Needle', bore: '3/8" Round', id: 0.375, od: 0.500, flangedOd: null, height: 0.500 },
    ];

    const bearingsBody = document.getElementById('bearings-body');
    BEARINGS.forEach(b => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="font-medium">${b.type}</td>
            <td>${b.bore}</td>
            <td>${b.id.toFixed(3)}</td>
            <td>${b.od.toFixed(3)}</td>
            <td>${b.flangedOd ? b.flangedOd.toFixed(3) : '—'}</td>
            <td>${b.height.toFixed(3)}</td>
        `;
        bearingsBody.appendChild(tr);
    });

    // ─── Mounting Cheat Sheet ───

    const MOUNTING = [
        { vendor: 'CTRE', product: 'PDP', holeDia: 0.196, screw: '#10', mountW: 5.28, mountH: 3.76, fullW: 6.93, fullH: 4.76 },
        { vendor: 'CTRE', product: 'PDH', holeDia: 0.196, screw: '#10', mountW: 5.87, mountH: 2.75, fullW: 6.93, fullH: 3.31 },
        { vendor: 'CTRE', product: 'Pigeon 2.0', holeDia: 0.113, screw: '#4', mountW: 1.85, mountH: 1.40, fullW: 2.00, fullH: 1.66 },
        { vendor: 'CTRE', product: 'CANivore', holeDia: 0.144, screw: '#6', mountW: 1.84, mountH: 1.10, fullW: 2.01, fullH: 1.26 },
        { vendor: 'REV', product: 'SPARK MAX', holeDia: 0.113, screw: '#4', mountW: 1.40, mountH: 0.96, fullW: 2.55, fullH: 1.14 },
        { vendor: 'REV', product: 'SPARK Flex', holeDia: 0.113, screw: '#4', mountW: 1.40, mountH: 0.96, fullW: 2.55, fullH: 1.14 },
        { vendor: 'REV', product: 'Power Module', holeDia: 0.196, screw: '#10', mountW: 6.50, mountH: 3.50, fullW: 7.50, fullH: 4.00 },
        { vendor: 'REV', product: 'Pneumatic Hub', holeDia: 0.144, screw: '#6', mountW: 3.00, mountH: 1.69, fullW: 3.50, fullH: 2.12 },
        { vendor: 'AndyMark', product: 'PDB', holeDia: 0.196, screw: '#10', mountW: 5.28, mountH: 3.76, fullW: 6.93, fullH: 4.76 },
        { vendor: 'NI', product: 'roboRIO', holeDia: 0.196, screw: '#10', mountW: 5.41, mountH: 3.38, fullW: 6.89, fullH: 5.73 },
        { vendor: 'NI', product: 'roboRIO 2.0', holeDia: 0.196, screw: '#10', mountW: 5.41, mountH: 3.38, fullW: 6.89, fullH: 5.73 },
        { vendor: 'Anderson', product: 'SB50', holeDia: null, screw: '—', mountW: 1.38, mountH: 0.87, fullW: 1.38, fullH: 1.41 },
        { vendor: 'Anderson', product: 'SB120', holeDia: null, screw: '—', mountW: 2.08, mountH: 1.13, fullW: 2.08, fullH: 1.82 },
    ];

    const mountingBody = document.getElementById('mounting-body');
    MOUNTING.forEach(m => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="font-medium text-gray-800">${m.vendor}</td>
            <td>${m.product}</td>
            <td>${m.holeDia ? m.holeDia.toFixed(3) : '—'}</td>
            <td>${m.screw}</td>
            <td>${m.mountW.toFixed(2)}</td>
            <td>${m.mountH.toFixed(2)}</td>
            <td>${m.fullW.toFixed(2)}</td>
            <td>${m.fullH.toFixed(2)}</td>
        `;
        mountingBody.appendChild(tr);
    });
});
