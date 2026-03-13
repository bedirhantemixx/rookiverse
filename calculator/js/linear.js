/**
 * Linear Mechanism Calculator
 * Euler integration with gravity load reflected to motor
 */
document.addEventListener('DOMContentLoaded', () => {
    MechTools.populateMotorSelect('motor', 'Kraken X60 (FOC)');

    function calculate() {
        const motorName = document.querySelector('[data-param="motor"]').value;
        const motor = MechTools.getMotor(motorName);
        const qty = MechTools.getInput('qty');
        const ratio = MechTools.getInput('ratio');
        const currentLimit = MechTools.getInput('currentLimit');
        const eff = MechTools.getInput('eff') / 100;
        const spoolD = MechTools.getInput('spoolD') * 0.0254; // in -> m
        const travel = MechTools.getInput('travel') * 0.0254; // in -> m
        const load = MechTools.getInput('load') * 0.453592; // lb -> kg
        const angle = MechTools.getInput('angle') * Math.PI / 180; // deg -> rad

        if (!motor || !ratio || !spoolD || !load) return;

        const g = 9.81;
        const spoolR = spoolD / 2;

        // Stall force at output
        const motorStallT = motorStallTorqueAtLimit(motor, currentLimit, qty);
        const wheelTorque = motorStallT * ratio * eff;
        const stallForce = wheelTorque / spoolR; // N
        const stallLoadLb = (stallForce / g) / 0.453592;

        // Gravity force component
        const gravForce = load * g * Math.sin(angle); // N
        const gravForceLb = (gravForce / 4.44822);

        MechTools.setOutput('out-stall-load', stallLoadLb, 1);
        MechTools.setOutput('out-grav-force', gravForceLb, 1);
        MechTools.setOutput('out-can-lift', stallForce > gravForce ? 'Yes' : 'No');

        // Max speed (free speed)
        const wheelRPM = motor.freeSpeed / ratio;
        const wheelOmega = wheelRPM * Math.PI / 30;
        const maxSpeed = wheelOmega * spoolR; // m/s
        const maxSpeedInS = maxSpeed / 0.0254;
        MechTools.setOutput('out-max-speed', maxSpeedInS, 1);

        // Euler integration
        const dt = 0.0005;
        const maxTime = 5;
        let position = 0; // m
        let velocity = 0; // m/s
        let reachedTime = null;
        let peakCurrent = 0;

        const labels = [];
        const posData = [];
        const velData = [];
        const curData = [];

        for (let t = 0; t <= maxTime; t += dt) {
            if (t % 0.01 < dt) {
                labels.push(t.toFixed(3));
                posData.push(position / 0.0254); // m -> in
                velData.push(velocity / 0.0254); // m/s -> in/s
            }

            // Linear velocity -> motor RPM
            const spoolOmega = velocity / spoolR;
            const motorRPM = spoolOmega * ratio * 30 / Math.PI;

            // Motor state
            const motorState = solveMotorState(motor, {
                rpm: Math.abs(motorRPM),
                voltage: 12,
                currentLimit: currentLimit,
                numMotors: qty,
            });

            // Force at output
            const motorForce = (motorState.totalTorque * ratio * eff) / spoolR;

            // Net force = motor - gravity
            const netForce = motorForce - gravForce;
            const accel = netForce / load;

            // Euler step
            velocity += accel * dt;
            velocity = Math.max(0, velocity); // no reverse
            if (velocity > maxSpeed) velocity = maxSpeed;
            position += velocity * dt;

            if (t % 0.01 < dt) {
                curData.push(motorState.current);
            }

            peakCurrent = Math.max(peakCurrent, motorState.current);

            if (reachedTime === null && position >= travel) {
                reachedTime = t;
            }
        }

        MechTools.setOutput('out-time', reachedTime !== null ? reachedTime : '> 5', 2);
        MechTools.setOutput('out-peak-current', peakCurrent, 1);

        // Charts
        MechTools.createChart('linear-chart', {
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Position (in)',
                        data: posData,
                        borderColor: MechTools.colors.gold,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Velocity (in/s)',
                        data: velData,
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
                        title: { display: true, text: 'Position (in)' },
                        beginAtZero: true,
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Velocity (in/s)' },
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                    }
                }
            }
        });

        MechTools.createChart('linear-current-chart', {
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
