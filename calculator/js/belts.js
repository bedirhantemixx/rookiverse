/**
 * Belt Calculator
 * Calculates center-to-center distance for timing belts
 */
document.addEventListener('DOMContentLoaded', () => {
    function calculate() {
        let pitchMm = MechTools.getInput('pitch');
        const p1Teeth = MechTools.getInput('p1');
        const p2Teeth = MechTools.getInput('p2');
        const desiredCC = MechTools.getInput('cc'); // inches
        const increment = MechTools.getInput('incr');

        if (!pitchMm || !p1Teeth || !p2Teeth || !desiredCC) return;

        // Convert pitch to inches
        let pitchIn;
        if (pitchMm < 1) {
            // Already in inches (XL belt)
            pitchIn = pitchMm;
        } else {
            pitchIn = pitchMm / 25.4;
        }

        // Pulley pitch diameters
        const pd1 = (p1Teeth * pitchIn) / Math.PI;
        const pd2 = (p2Teeth * pitchIn) / Math.PI;

        MechTools.setOutput('out-pd1', pd1, 3);
        MechTools.setOutput('out-pd2', pd2, 3);

        // Ratio
        const ratioVal = Math.max(p1Teeth, p2Teeth) / Math.min(p1Teeth, p2Teeth);
        MechTools.setOutput('out-ratio', `${ratioVal.toFixed(2)}:1`);

        // Approximate belt pitch length for desired C-C
        const C = desiredCC;
        const D1 = pd1, D2 = pd2;
        const approxLength = 2 * C + 1.5708 * (D1 + D2) + Math.pow(D1 - D2, 2) / (4 * C);

        // Convert to teeth
        const approxTeeth = approxLength / pitchIn;

        // Find closest smaller and larger belt sizes
        const incr = increment || 5;
        const smallerTeeth = Math.floor(approxTeeth / incr) * incr;
        const largerTeeth = smallerTeeth + incr;

        // Calculate exact C-C for each belt size
        const smallerCC = beltCenterDistance(smallerTeeth * pitchIn, D1, D2);
        const largerCC = beltCenterDistance(largerTeeth * pitchIn, D1, D2);

        // Teeth in mesh on smaller pulley
        const smallPulley = Math.min(p1Teeth, p2Teeth);
        const smallPD = Math.min(pd1, pd2);
        const largePD = Math.max(pd1, pd2);

        const smTIM = teethInMesh(smallerCC, smallPD, largePD, pitchIn);
        const lgTIM = teethInMesh(largerCC, smallPD, largePD, pitchIn);

        MechTools.setOutput('out-sm-teeth', smallerTeeth, 0);
        MechTools.setOutput('out-sm-cc', smallerCC, 4);
        MechTools.setOutput('out-sm-tim', smTIM, 1);

        MechTools.setOutput('out-lg-teeth', largerTeeth, 0);
        MechTools.setOutput('out-lg-cc', largerCC, 4);
        MechTools.setOutput('out-lg-tim', lgTIM, 1);
    }

    /**
     * Calculate exact center-to-center distance given belt length
     * L = belt pitch length, D1/D2 = pulley pitch diameters
     */
    function beltCenterDistance(L, D1, D2) {
        const b = 2 * L - Math.PI * (D1 + D2);
        const discriminant = b * b - 8 * Math.pow(D1 - D2, 2);
        if (discriminant < 0) return 0;
        return (b + Math.sqrt(discriminant)) / 8;
    }

    /**
     * Calculate teeth in mesh on smaller pulley
     */
    function teethInMesh(C, smallPD, largePD, pitch) {
        if (C <= 0) return 0;
        const wrapAngle = Math.PI - 2 * Math.asin(Math.abs(largePD - smallPD) / (2 * C));
        const contactArc = wrapAngle * (smallPD / 2);
        return contactArc / pitch;
    }

    MechTools.bindInputs(calculate);
});
