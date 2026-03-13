/**
 * Ratio Calculator
 * Compound ratio from multiple driving/driven tooth pairs
 */
document.addEventListener('DOMContentLoaded', () => {
    let stages = [
        { driving: 18, driven: 72 },
        { driving: 24, driven: 48 },
    ];

    // Restore from URL
    const state = MechTools.state.read();
    if (state.stages) {
        try {
            const parsed = JSON.parse(state.stages);
            if (Array.isArray(parsed) && parsed.length > 0) {
                stages = parsed;
            }
        } catch(e) {}
    }

    function renderStages() {
        const container = document.getElementById('stages-container');
        container.innerHTML = '';

        stages.forEach((stage, i) => {
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 mb-2';
            div.innerHTML = `
                <span class="text-xs font-semibold text-gray-400 w-6">S${i + 1}</span>
                <input type="number" class="stage-driving" data-index="${i}" value="${stage.driving}" min="1" step="1"
                    style="width:80px;padding:8px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.875rem;">
                <span class="text-gray-400 text-sm">:</span>
                <input type="number" class="stage-driven" data-index="${i}" value="${stage.driven}" min="1" step="1"
                    style="width:80px;padding:8px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.875rem;">
                ${stages.length > 1 ? `<button class="remove-stage text-red-400 hover:text-red-600 p-1" data-index="${i}"><i data-lucide="x" class="w-4 h-4"></i></button>` : ''}
            `;
            container.appendChild(div);
        });

        // Bind events
        container.querySelectorAll('.stage-driving').forEach(el => {
            el.addEventListener('input', (e) => {
                stages[e.target.dataset.index].driving = Number(e.target.value) || 1;
                calculate();
            });
        });
        container.querySelectorAll('.stage-driven').forEach(el => {
            el.addEventListener('input', (e) => {
                stages[e.target.dataset.index].driven = Number(e.target.value) || 1;
                calculate();
            });
        });
        container.querySelectorAll('.remove-stage').forEach(el => {
            el.addEventListener('click', (e) => {
                const idx = Number(e.currentTarget.dataset.index);
                stages.splice(idx, 1);
                renderStages();
                calculate();
            });
        });

        lucide.createIcons();
    }

    document.getElementById('add-stage').addEventListener('click', () => {
        stages.push({ driving: 12, driven: 48 });
        renderStages();
        calculate();
    });

    function calculate() {
        let overallRatio = 1;
        const breakdownEl = document.getElementById('stage-breakdown');
        breakdownEl.innerHTML = '';

        stages.forEach((stage, i) => {
            const stageRatio = stage.driving / stage.driven;
            overallRatio *= stageRatio;

            const row = document.createElement('div');
            row.className = 'output-row';
            row.innerHTML = `
                <label>Stage ${i + 1} (${stage.driving}:${stage.driven})</label>
                <span class="output-value">${(stage.driven / stage.driving).toFixed(3)}:1</span>
            `;
            breakdownEl.appendChild(row);
        });

        const totalReduction = 1 / overallRatio;
        MechTools.setOutput('out-ratio', `${totalReduction.toFixed(4)}:1`);
        MechTools.setOutput('out-reduction', `${totalReduction.toFixed(2)}:1`);

        if (totalReduction > 1) {
            MechTools.setOutput('out-type', 'Reduction');
        } else if (totalReduction < 1) {
            MechTools.setOutput('out-type', 'Step-up');
        } else {
            MechTools.setOutput('out-type', '1:1 Direct');
        }

        // Save to URL
        MechTools.state.write({ stages: JSON.stringify(stages) });
    }

    renderStages();
    calculate();
});
