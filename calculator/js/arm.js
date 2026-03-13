/**
 * Arm Calculator
 * Euler integration with gravity torque: T_grav = L_com * m * g * cos(theta)
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
        const comDist = MechTools.getInput('comDist') * 0.0254; // in -> m
        const mass = MechTools.getInput('mass') * 0.453592; // lb -> kg
        const startAngle = MechTools.getInput('startAngle') * Math.PI / 180; // deg -> rad
        const endAngle = MechTools.getInput('endAngle') * Math.PI / 180;

        if (!motor || !ratio || !comDist || !mass) return;

        const g = 9.81;
        const J = mass * comDist * comDist; // point mass MOI

        // Motor stall torque at arm (with current limit)
        const motorStallTorque = motorStallTorqueAtLimit(motor, currentLimit, qty);
        const armStallTorque = motorStallTorque * ratio * eff;

        // Max gravity torque (at horizontal)
        const maxGravTorque = comDist * mass * g;

        MechTools.setOutput('out-grav-torque', maxGravTorque, 2);
        MechTools.setOutput('out-stall-torque', armStallTorque, 2);

        const torqueMargin = ((armStallTorque - maxGravTorque) / maxGravTorque) * 100;
        MechTools.setOutput('out-torque-margin', torqueMargin, 1);
        MechTools.setOutput('out-can-hold', armStallTorque > maxGravTorque ? 'Yes' : 'No');

        // Euler integration
        const dt = 0.0005; // 0.5ms
        const maxTime = 5;
        const direction = endAngle > startAngle ? 1 : -1;

        let theta = startAngle;
        let omega = 0;
        let reachedTime = null;
        let peakCurrent = 0;

        const labels = [];
        const posData = [];
        const torqueData = [];
        const currentData = [];

        for (let t = 0; t <= maxTime; t += dt) {
            // Sample for chart
            if (t % 0.01 < dt) {
                labels.push(t.toFixed(3));
                posData.push(theta * 180 / Math.PI);
            }

            // Motor RPM from arm angular velocity
            const motorRPM = Math.abs(omega) * ratio * 30 / Math.PI;

            // Motor state
            const motorState = solveMotorState(motor, {
                rpm: motorRPM,
                voltage: 12,
                currentLimit: currentLimit,
                numMotors: qty,
            });

            // Motor torque at arm output
            let motorTorqueAtArm = motorState.totalTorque * ratio * eff;

            // Gravity torque (opposes if going up, assists if going down)
            const gravTorque = comDist * mass * g * Math.cos(theta);

            // Net torque: motor torque acts in direction of travel, gravity opposes upward motion
            let netTorque;
            if (direction > 0) {
                // Going up: motor pushes +, gravity pulls -
                netTorque = motorTorqueAtArm - gravTorque;
            } else {
                // Going down: motor pushes -, gravity assists
                netTorque = -motorTorqueAtArm + gravTorque;
            }

            // Angular acceleration
            const alpha = netTorque / J;

            // Euler step
            omega += alpha * dt;

            // Clamp velocity direction
            if (direction > 0) {
                omega = Math.max(0, omega);
            } else {
                omega = Math.min(0, omega);
            }

            theta += omega * dt;

            if (t % 0.01 < dt) {
                torqueData.push(motorTorqueAtArm);
                currentData.push(motorState.current);
            }

            peakCurrent = Math.max(peakCurrent, motorState.current);

            // Check if reached target
            if (reachedTime === null) {
                if (direction > 0 && theta >= endAngle) {
                    reachedTime = t;
                } else if (direction < 0 && theta <= endAngle) {
                    reachedTime = t;
                }
            }
        }

        MechTools.setOutput('out-time', reachedTime !== null ? reachedTime : '> 5', 2);
        MechTools.setOutput('out-peak-current', peakCurrent, 1);

        // Charts
        MechTools.createChart('arm-pos-chart', {
            data: {
                labels: labels,
                datasets: [{
                    label: 'Arm Angle (deg)',
                    data: posData,
                    borderColor: MechTools.colors.gold,
                    backgroundColor: 'rgba(229, 174, 50, 0.1)',
                    fill: true,
                }]
            },
            options: {
                scales: {
                    x: { title: { display: true, text: 'Time (s)' } },
                    y: { title: { display: true, text: 'Angle (deg)' } }
                }
            }
        });

        MechTools.createChart('arm-torque-chart', {
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Motor Torque at Arm (N·m)',
                        data: torqueData,
                        borderColor: MechTools.colors.green,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Motor Current (A)',
                        data: currentData,
                        borderColor: MechTools.colors.red,
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
                        title: { display: true, text: 'Torque (N·m)' },
                        beginAtZero: true,
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Current (A)' },
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                    }
                }
            }
        });
    }

    MechTools.bindInputs(calculate);
});
