/**
 * Flywheel Calculator
 * Shooter wheel windup, speed transfer, and recovery analysis
 */
document.addEventListener('DOMContentLoaded', () => {
    MechTools.populateMotorSelect('motor', 'Kraken X60 (FOC)');

    function calculate() {
        const motorName = document.querySelector('[data-param="motor"]').value;
        const motor = MechTools.getMotor(motorName);
        const qty = MechTools.getInput('qty');
        const ratio = MechTools.getInput('ratio'); // step-up: motor:wheel
        const currentLimit = MechTools.getInput('currentLimit');
        const eff = MechTools.getInput('eff') / 100;
        const wheelR = MechTools.getInput('wheelR') * 0.0254; // in -> m
        const wheelW = MechTools.getInput('wheelW') * 0.453592; // lb -> kg
        const targetRPM = MechTools.getInput('targetRPM');
        const variation = MechTools.getInput('variation') / 100;
        const projR = MechTools.getInput('projR') * 0.0254; // in -> m
        const projW = MechTools.getInput('projW') * 0.453592; // lb -> kg

        if (!motor || !ratio || !wheelR || !wheelW) return;

        // Moment of inertia (solid cylinder)
        const J_wheel = 0.5 * wheelW * wheelR * wheelR;

        // Motor characteristics under current limit
        const stallTorque = motorStallTorqueAtLimit(motor, currentLimit, qty) * eff;
        const freeSpeedWheel = motor.freeSpeed / ratio; // RPM at wheel

        const targetOmega = targetRPM * Math.PI / 30; // rad/s

        // Max RPM achievable
        const maxRPM = motorFreeSpeedAtLimit(motor, currentLimit) / ratio;
        MechTools.setOutput('out-max-rpm', maxRPM, 0);

        // Surface speed at target RPM
        const surfaceSpeed = targetOmega * wheelR; // m/s
        const surfaceSpeedFtS = surfaceSpeed / 0.3048;
        MechTools.setOutput('out-surface-speed', surfaceSpeedFtS, 1);

        // Windup time (exponential spinup model)
        const freeOmega = freeSpeedWheel * Math.PI / 30;
        let windupTime;
        if (targetOmega >= freeOmega) {
            windupTime = Infinity;
        } else {
            windupTime = -(J_wheel * freeOmega) / (stallTorque) *
                Math.log((freeOmega - targetOmega) / freeOmega);
        }
        MechTools.setOutput('out-windup', windupTime, 2);

        // Speed transfer percentage
        const speedTransfer = (20 * J_wheel) /
            (7 * projW * Math.pow(2 * projR, 2) + 40 * J_wheel);
        MechTools.setOutput('out-transfer', (speedTransfer * 100), 1);

        // Exit velocity
        const exitVel = surfaceSpeed * speedTransfer;
        MechTools.setOutput('out-exit-vel', exitVel / 0.3048, 1);

        // Projectile kinetic energy (with rotational: 0.7 factor)
        const projEnergy = 0.7 * projW * exitVel * exitVel;
        MechTools.setOutput('out-proj-energy', projEnergy, 2);

        // Flywheel kinetic energy at target speed
        const fwEnergy = 0.5 * J_wheel * targetOmega * targetOmega;
        MechTools.setOutput('out-fw-energy', fwEnergy, 2);

        // Speed after shot
        const energyAfter = fwEnergy - projEnergy;
        let afterOmega, afterRPM;
        if (energyAfter > 0) {
            afterOmega = Math.sqrt(2 * energyAfter / J_wheel);
            afterRPM = afterOmega * 30 / Math.PI;
        } else {
            afterOmega = 0;
            afterRPM = 0;
        }
        MechTools.setOutput('out-after-rpm', afterRPM, 0);

        // Recovery time (from after-shot speed to target * (1 - variation))
        const recoveryTarget = targetOmega * (1 - variation);
        let recoveryTime;
        if (afterOmega >= recoveryTarget || recoveryTarget >= freeOmega) {
            recoveryTime = 0;
        } else {
            recoveryTime = -(J_wheel * freeOmega) / (stallTorque) *
                (Math.log((freeOmega - recoveryTarget) / freeOmega) -
                 Math.log((freeOmega - afterOmega) / freeOmega));
        }
        MechTools.setOutput('out-recovery', recoveryTime, 2);

        // Build chart
        buildChart(motor, qty, ratio, currentLimit, eff, J_wheel, targetRPM, afterRPM, windupTime, recoveryTime);
    }

    function buildChart(motor, qty, ratio, currentLimit, eff, J, targetRPM, afterRPM, windupTime, recoveryTime) {
        const stallTorque = motorStallTorqueAtLimit(motor, currentLimit, qty) * eff;
        const freeSpeedWheel = motor.freeSpeed / ratio;
        const freeOmega = freeSpeedWheel * Math.PI / 30;

        const totalTime = Math.min((windupTime + recoveryTime) * 1.5, 30);
        const dt = totalTime / 200;
        const labels = [];
        const rpmData = [];

        let omega = 0;
        for (let t = 0; t <= totalTime; t += dt) {
            labels.push(t.toFixed(2));

            // Exponential approach to free speed
            omega = freeOmega * (1 - Math.exp(-stallTorque * t / (J * freeOmega)));
            const rpm = omega * 30 / Math.PI;
            rpmData.push(rpm);
        }

        MechTools.createChart('flywheel-chart', {
            data: {
                labels: labels,
                datasets: [{
                    label: 'Wheel RPM',
                    data: rpmData,
                    borderColor: MechTools.colors.gold,
                    backgroundColor: 'rgba(229, 174, 50, 0.1)',
                    fill: true,
                }]
            },
            options: {
                scales: {
                    x: { title: { display: true, text: 'Time (s)' } },
                    y: { title: { display: true, text: 'RPM' }, beginAtZero: true }
                },
                plugins: {
                    annotation: {
                        annotations: {}
                    }
                }
            }
        });
    }

    MechTools.bindInputs(calculate);
});
