/**
 * Intake Calculator
 * Calculates roller surface speed and recommended ratio
 */
document.addEventListener('DOMContentLoaded', () => {
    MechTools.populateMotorSelect('motor', 'Kraken X60 (FOC)');

    function calculate() {
        const motorName = document.querySelector('[data-param="motor"]').value;
        const motor = MechTools.getMotor(motorName);
        const qty = MechTools.getInput('qty');
        const ratio = MechTools.getInput('ratio');
        const rollerD = MechTools.getInput('rollerD'); // inches
        const driveSpeed = MechTools.getInput('driveSpeed'); // ft/s
        const multiplier = MechTools.getInput('multiplier');

        if (!motor || !ratio || !rollerD) return;

        // Motor free speed -> roller RPM
        const rollerRPM = motor.freeSpeed / ratio;

        // Surface speed = roller_RPM * circumference
        const rollerCircumference = Math.PI * rollerD; // inches
        const surfaceSpeedInS = rollerRPM * rollerCircumference / 60; // in/s
        const surfaceSpeedFtS = surfaceSpeedInS / 12; // ft/s

        MechTools.setOutput('out-speed', surfaceSpeedFtS, 1);
        MechTools.setOutput('out-speed-ins', surfaceSpeedInS, 1);
        MechTools.setOutput('out-roller-rpm', rollerRPM, 0);

        // Recommended ratio for target speed
        const targetSpeed = driveSpeed * multiplier; // ft/s
        const targetSpeedInS = targetSpeed * 12; // in/s
        const targetRPM = targetSpeedInS * 60 / rollerCircumference;
        const recommendedRatio = motor.freeSpeed / targetRPM;

        MechTools.setOutput('out-rec-ratio', recommendedRatio.toFixed(2) + ':1');
        MechTools.setOutput('out-target-speed', targetSpeed, 1);
        MechTools.setOutput('out-rec-label', `for ${multiplier}x drivetrain speed`);

        // Speed comparison
        const speedRatio = surfaceSpeedFtS / driveSpeed;
        MechTools.setOutput('out-speed-ratio', speedRatio.toFixed(1) + 'x drivetrain');
    }

    MechTools.bindInputs(calculate);
});
