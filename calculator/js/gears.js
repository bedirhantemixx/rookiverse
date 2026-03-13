/**
 * Gear Spacing Calculator
 * C = (T1 + T2) / (2 * DP)
 */
document.addEventListener('DOMContentLoaded', () => {
    function calculate() {
        const g1 = MechTools.getInput('g1');
        const g2 = MechTools.getInput('g2');
        const dp = MechTools.getInput('dp');

        if (!g1 || !g2 || !dp || dp === 0) return;

        const pd1 = g1 / dp;
        const pd2 = g2 / dp;
        const cc = (g1 + g2) / (2 * dp);
        const ratio = Math.max(g1, g2) / Math.min(g1, g2);
        const driving = g1;
        const driven = g2;

        MechTools.setOutput('out-pd1', pd1, 4);
        MechTools.setOutput('out-pd2', pd2, 4);
        MechTools.setOutput('out-cc', cc, 4);
        MechTools.setOutput('out-ratio', `${driving}:${driven}`);

        if (g2 > g1) {
            MechTools.setOutput('out-reduction', `${(g2 / g1).toFixed(2)}:1 reduction`);
        } else if (g1 > g2) {
            MechTools.setOutput('out-reduction', `${(g1 / g2).toFixed(2)}:1 step-up`);
        } else {
            MechTools.setOutput('out-reduction', '1:1');
        }
    }

    MechTools.bindInputs(calculate);
});
