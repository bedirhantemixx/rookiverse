/**
 * Drivetrain Calculator
 * ILITE-style timestep simulation with battery sag, current limiting, and traction limiting
 */
document.addEventListener('DOMContentLoaded', () => {
    MechTools.populateMotorSelect('motor', 'Kraken X60 (FOC)');

    function calculate() {
        const motorName = document.querySelector('[data-param="motor"]').value;
        const motor = MechTools.getMotor(motorName);
        const motorsPerSide = MechTools.getInput('motorsPerSide');
        const ratio = MechTools.getInput('ratio');
        const eff = MechTools.getInput('eff') / 100;
        const currentLimit = MechTools.getInput('currentLimit');
        const robotWeight = MechTools.getInput('robotWeight'); // lb
        const bumperWeight = MechTools.getInput('bumperWeight'); // lb
        const wheelD = MechTools.getInput('wheelD'); // in
        const cof = MechTools.getInput('cof');
        const sprintDist = MechTools.getInput('sprintDist'); // ft
        const battVoltage = MechTools.getInput('voltage');
        const battR = MechTools.getInput('battR');

        if (!motor || !ratio || !wheelD) return;

        const totalWeight = robotWeight + bumperWeight; // lb
        const totalMass = totalWeight * 0.453592; // kg
        const wheelRadius = (wheelD / 2) * 0.0254; // m
        const numMotors = motorsPerSide * 2; // total motors
        const g = 9.81;

        // Theoretical max speed
        const wheelRPM = motor.freeSpeed / ratio;
        const wheelOmega = wheelRPM * Math.PI / 30;
        const maxSpeed = wheelOmega * wheelRadius; // m/s
        const maxSpeedFtS = maxSpeed / 0.3048;
        const maxSpeedMph = maxSpeedFtS * 3600 / 5280;

        MechTools.setOutput('out-max-speed', maxSpeedFtS, 1);
        MechTools.setOutput('out-max-mph', maxSpeedMph, 1);

        // Adjusted free speed (voltage-adjusted)
        const adjSpeed = maxSpeedFtS * (battVoltage / 12);
        MechTools.setOutput('out-adj-speed', adjSpeed, 1);

        // Max pushing force at stall
        const stallTorque = motorStallTorqueAtLimit(motor, currentLimit, numMotors) * eff;
        const pushForce = stallTorque / wheelRadius; // N
        const pushForceLbf = pushForce / 4.44822;
        MechTools.setOutput('out-push-force', pushForceLbf, 1);

        // Max traction force
        const normalForce = totalMass * g; // N
        const tractionForce = normalForce * cof;
        const tractionForceLbf = tractionForce / 4.44822;
        MechTools.setOutput('out-traction', tractionForceLbf, 1);

        const isTractionLimited = pushForce > tractionForce;
        MechTools.setOutput('out-traction-limited', isTractionLimited ? 'Yes' : 'No');

        // Stall current per motor
        const stallCur = Math.min(motor.stallCurrent, currentLimit);
        MechTools.setOutput('out-stall-current', stallCur, 1);

        // Timestep simulation
        const dt = 0.005; // 5ms
        const maxTime = 8; // seconds
        const labels = [];
        const velData = [];
        const posData = [];
        const curData = [];

        let velocity = 0; // m/s
        let position = 0; // m
        let sprintTime = null;
        const sprintDistM = sprintDist * 0.3048;

        for (let t = 0; t <= maxTime; t += dt) {
            if (t % 0.02 < dt) { // Sample every ~20ms for chart
                labels.push(t.toFixed(2));
                velData.push(velocity / 0.3048); // ft/s
                posData.push(position / 0.3048); // ft
            }

            // Motor RPM from wheel speed
            const wheelRPMCurrent = (velocity / wheelRadius) * 30 / Math.PI;
            const motorRPM = wheelRPMCurrent * ratio;

            // Battery sag
            const motorState = solveMotorState(motor, {
                rpm: motorRPM,
                voltage: battVoltage,
                currentLimit: currentLimit,
                numMotors: numMotors
            });

            // Battery voltage drop
            const totalCurrent = motorState.current * numMotors;
            const effectiveVoltage = battVoltage - totalCurrent * battR;

            // Recalculate with effective voltage
            const backEmf = motorRPM * Math.PI / 30 / motor.kV;
            let motorCurrent = (Math.max(0, effectiveVoltage) - backEmf) / motor.resistance;
            motorCurrent = Math.max(0, Math.min(motorCurrent, currentLimit));

            const torquePerMotor = motor.kT * Math.max(0, motorCurrent - motor.freeCurrent);
            // Force at wheel: motor torque * ratio = wheel torque, / wheel radius = force
            let force = (torquePerMotor * numMotors * eff * ratio) / wheelRadius;

            // Traction limit
            if (force > tractionForce) {
                force = tractionForce;
            }

            // Acceleration
            const accel = force / totalMass;

            // Euler integration
            velocity += accel * dt;
            if (velocity > maxSpeed * (battVoltage / 12)) {
                velocity = maxSpeed * (battVoltage / 12);
            }
            position += velocity * dt;

            if (t % 0.02 < dt) {
                curData.push(motorCurrent);
            }

            // Check sprint distance
            if (sprintTime === null && position >= sprintDistM) {
                sprintTime = t;
            }
        }

        MechTools.setOutput('out-sprint-time', sprintTime || '> ' + maxTime, 2);

        // Build charts
        MechTools.createChart('drive-chart', {
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Velocity (ft/s)',
                        data: velData,
                        borderColor: MechTools.colors.gold,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Position (ft)',
                        data: posData,
                        borderColor: MechTools.colors.blue,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                scales: {
                    x: { title: { display: true, text: 'Time (s)' } },
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Velocity (ft/s)' },
                        beginAtZero: true,
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Position (ft)' },
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                    }
                }
            }
        });

        MechTools.createChart('current-chart', {
            data: {
                labels: labels,
                datasets: [{
                    label: 'Motor Current (A)',
                    data: curData,
                    borderColor: MechTools.colors.red,
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                }]
            },
            options: {
                scales: {
                    x: { title: { display: true, text: 'Time (s)' } },
                    y: { title: { display: true, text: 'Current (A)' }, beginAtZero: true }
                }
            }
        });
    }

    MechTools.bindInputs(calculate);
});
