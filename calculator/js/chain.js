/**
 * Chain Calculator
 * Calculates center-to-center distance for chain & sprocket systems
 */
document.addEventListener('DOMContentLoaded', () => {
    function calculate() {
        const pitch = MechTools.getInput('chain'); // inches
        const s1 = MechTools.getInput('s1');
        const s2 = MechTools.getInput('s2');
        const desiredCC = MechTools.getInput('cc');
        const allowHalf = MechTools.getInput('half');

        if (!pitch || !s1 || !s2 || !desiredCC) return;

        // Sprocket pitch diameters
        const pd1 = pitch / Math.sin(Math.PI / s1);
        const pd2 = pitch / Math.sin(Math.PI / s2);

        MechTools.setOutput('out-pd1', pd1, 3);
        MechTools.setOutput('out-pd2', pd2, 3);

        // Ratio
        const ratioVal = Math.max(s1, s2) / Math.min(s1, s2);
        MechTools.setOutput('out-ratio', `${ratioVal.toFixed(2)}:1`);

        // Approximate number of links
        const N = Math.max(s1, s2);
        const n = Math.min(s1, s2);
        const C = desiredCC;
        const P = pitch;

        const approxLinks = (2 * C / P) + (s1 + s2) / 2 + P * Math.pow((Math.abs(s2 - s1)) / (2 * Math.PI), 2) / C;

        // Round to nearest whole or half link
        const increment = allowHalf ? 0.5 : 1;
        const smallerLinks = Math.floor(approxLinks / increment) * increment;
        const largerLinks = smallerLinks + increment;

        // Calculate exact C-C for each link count
        const smallerCC = chainCenterDistance(smallerLinks, s1, s2, P);
        const largerCC = chainCenterDistance(largerLinks, s1, s2, P);

        MechTools.setOutput('out-sm-links', smallerLinks, 1);
        MechTools.setOutput('out-sm-cc', smallerCC, 4);

        MechTools.setOutput('out-lg-links', largerLinks, 1);
        MechTools.setOutput('out-lg-cc', largerCC, 4);
    }

    /**
     * Calculate exact center distance from link count
     */
    function chainCenterDistance(links, s1, s2, P) {
        const N = Math.max(s1, s2);
        const n = Math.min(s1, s2);
        const t1 = 2 * links - N - n;
        const discriminant = t1 * t1 - 0.810 * Math.pow(N - n, 2);
        if (discriminant < 0) return 0;
        return (P / 8) * (t1 + Math.sqrt(discriminant));
    }

    MechTools.bindInputs(calculate);
});
