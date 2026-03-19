/**
 * Ratio Finder
 * Finds COTS gear/sprocket combinations to achieve target ratios
 */
document.addEventListener('DOMContentLoaded', () => {

    // Common COTS tooth counts
    const GEAR_TEETH = [10, 12, 14, 15, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 40, 44, 48, 50, 54, 56, 60, 64, 68, 72, 76, 80, 84];
    const SPROCKET_TEETH = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 48, 54, 60];

    let searchTimer = null;

    function getTeethList() {
        const type = document.querySelector('[data-param="type"]').value;
        const minT = MechTools.getInput('minTeeth');
        const maxT = MechTools.getInput('maxTeeth');

        let teeth;
        if (type === 'gears') {
            teeth = GEAR_TEETH;
        } else if (type === 'sprockets') {
            teeth = SPROCKET_TEETH;
        } else {
            teeth = [...new Set([...GEAR_TEETH, ...SPROCKET_TEETH])].sort((a, b) => a - b);
        }

        return teeth.filter(t => t >= minT && t <= maxT);
    }

    function calculate() {
        // Debounce for 2-stage search
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(doSearch, 150);
    }

    function doSearch() {
        const target = MechTools.getInput('target');
        const maxError = MechTools.getInput('maxError') / 100;
        const stages = MechTools.getInput('stages');
        const maxResults = MechTools.getInput('maxResults');
        const teeth = getTeethList();

        if (target <= 0 || teeth.length === 0) return;

        const results = [];

        if (stages === 1) {
            // Single stage: driven/driving = ratio
            for (const driving of teeth) {
                for (const driven of teeth) {
                    if (driven <= driving) continue;
                    const ratio = driven / driving;
                    const error = Math.abs(ratio - target) / target;
                    if (error <= maxError) {
                        results.push({
                            ratio,
                            error,
                            stages: [{ driving, driven }],
                        });
                    }
                }
            }
        } else {
            // Two stages: find pairs where r1 * r2 ≈ target
            // Precompute single-stage ratios
            const singleRatios = [];
            for (const driving of teeth) {
                for (const driven of teeth) {
                    if (driven <= driving) continue;
                    singleRatios.push({ driving, driven, ratio: driven / driving });
                }
            }

            // Check all pairs
            for (let i = 0; i < singleRatios.length; i++) {
                const s1 = singleRatios[i];
                for (let j = 0; j < singleRatios.length; j++) {
                    const s2 = singleRatios[j];
                    const combo = s1.ratio * s2.ratio;
                    const error = Math.abs(combo - target) / target;
                    if (error <= maxError) {
                        results.push({
                            ratio: combo,
                            error,
                            stages: [
                                { driving: s1.driving, driven: s1.driven },
                                { driving: s2.driving, driven: s2.driven },
                            ],
                        });
                    }
                }
            }
        }

        // Sort by error
        results.sort((a, b) => a.error - b.error);
        const trimmed = results.slice(0, maxResults);

        renderResults(trimmed, target);
    }

    function renderResults(results, target) {
        const container = document.getElementById('results-container');
        const countEl = document.getElementById('result-count');

        if (results.length === 0) {
            container.innerHTML = '<p class="text-gray-400 text-sm">No combinations found. Try increasing max error or tooth range.</p>';
            countEl.textContent = '';
            return;
        }

        countEl.textContent = `(${results.length} found)`;
        container.innerHTML = '';

        results.forEach(r => {
            const errorPct = (r.error * 100).toFixed(2);
            const stagesStr = r.stages.map((s, i) =>
                `<span class="text-gray-700">${s.driving}T:${s.driven}T</span>`
            ).join(' <span class="text-gray-400">×</span> ');

            const div = document.createElement('div');
            div.className = 'ratio-result';
            div.innerHTML = `
                <div>
                    <div class="text-sm font-semibold">${r.ratio.toFixed(4)}:1</div>
                    <div class="text-xs text-gray-500">${stagesStr}</div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-medium ${r.error < 0.01 ? 'text-green-600' : r.error < 0.03 ? 'text-yellow-600' : 'text-red-500'}">
                        ${errorPct}% error
                    </span>
                </div>
            `;
            container.appendChild(div);
        });
    }

    MechTools.bindInputs(calculate);
});
