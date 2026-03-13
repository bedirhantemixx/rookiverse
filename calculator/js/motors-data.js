/**
 * FRC Motor Database
 * All specs at 12V nominal unless noted
 * freeSpeed: RPM, stallTorque: N·m, stallCurrent: A, freeCurrent: A, weight: kg
 */
const MOTORS = [
    {
        name: "Kraken X60 (FOC)",
        freeSpeed: 5800,
        stallTorque: 9.37,
        stallCurrent: 483,
        freeCurrent: 2,
        weight: 0.3515,
        diameter: 0.06, // 60mm
    },
    {
        name: "Kraken X60",
        freeSpeed: 6000,
        stallTorque: 7.09,
        stallCurrent: 366,
        freeCurrent: 2,
        weight: 0.3515,
        diameter: 0.06,
    },
    {
        name: "NEO Vortex",
        freeSpeed: 6784,
        stallTorque: 3.60,
        stallCurrent: 211,
        freeCurrent: 3.6,
        weight: 0.348,
        diameter: 0.06,
    },
    {
        name: "Falcon 500",
        freeSpeed: 6380,
        stallTorque: 4.69,
        stallCurrent: 257,
        freeCurrent: 1.5,
        weight: 0.544,
        diameter: 0.06,
    },
    {
        name: "Falcon 500 (FOC)",
        freeSpeed: 6080,
        stallTorque: 5.84,
        stallCurrent: 304,
        freeCurrent: 1.5,
        weight: 0.544,
        diameter: 0.06,
    },
    {
        name: "NEO",
        freeSpeed: 5676,
        stallTorque: 2.6,
        stallCurrent: 105,
        freeCurrent: 1.8,
        weight: 0.425,
        diameter: 0.06,
    },
    {
        name: "CIM",
        freeSpeed: 5330,
        stallTorque: 2.41,
        stallCurrent: 131,
        freeCurrent: 2.7,
        weight: 1.065,
        diameter: 0.066,
    },
    {
        name: "Mini CIM",
        freeSpeed: 5840,
        stallTorque: 1.41,
        stallCurrent: 89,
        freeCurrent: 3,
        weight: 0.703,
        diameter: 0.057,
    },
    {
        name: "775pro",
        freeSpeed: 18730,
        stallTorque: 0.71,
        stallCurrent: 134,
        freeCurrent: 0.7,
        weight: 0.365,
        diameter: 0.042,
    },
    {
        name: "NEO 550",
        freeSpeed: 11000,
        stallTorque: 0.97,
        stallCurrent: 100,
        freeCurrent: 1.4,
        weight: 0.235,
        diameter: 0.036,
    },
    {
        name: "BAG Motor",
        freeSpeed: 13180,
        stallTorque: 0.43,
        stallCurrent: 53,
        freeCurrent: 1.8,
        weight: 0.347,
        diameter: 0.042,
    },
    {
        name: "AM 9015",
        freeSpeed: 14270,
        stallTorque: 0.36,
        stallCurrent: 71,
        freeCurrent: 3.7,
        weight: 0.226,
        diameter: 0.036,
    },
    {
        name: "Snowblower",
        freeSpeed: 100,
        stallTorque: 16.95,
        stallCurrent: 24,
        freeCurrent: 5,
        weight: 2.041,
        diameter: 0.086,
    },
];

// Compute derived motor constants
MOTORS.forEach(m => {
    const V = 12; // nominal voltage
    m.resistance = V / m.stallCurrent;
    m.kV = m.freeSpeed * (Math.PI / 30) / (V - m.resistance * m.freeCurrent); // rad/s per volt
    m.kT = m.stallTorque / m.stallCurrent; // N·m per amp
    m.freePower = m.freeSpeed * (Math.PI / 30) * 0; // 0 at free speed
    m.maxPower = (m.freeSpeed / 2) * (Math.PI / 30) * (m.stallTorque / 2); // at half speed
});

/**
 * Motor state solver
 * Given partial state, solve for full motor operating point
 */
function solveMotorState(motor, { rpm, voltage = 12, currentLimit = null, numMotors = 1 }) {
    const V = Math.min(voltage, 12);
    const omega = rpm * Math.PI / 30;

    // Back-EMF voltage
    const backEmf = omega / motor.kV;
    const current = (V - backEmf) / motor.resistance;

    let limitedCurrent = current;
    if (currentLimit !== null && current > currentLimit) {
        limitedCurrent = currentLimit;
    }
    if (limitedCurrent < 0) limitedCurrent = 0;

    const torquePerMotor = motor.kT * (limitedCurrent - motor.freeCurrent);
    const totalTorque = Math.max(0, torquePerMotor * numMotors);
    const power = totalTorque * omega;

    return {
        rpm,
        omega,
        current: limitedCurrent,
        torquePerMotor: Math.max(0, torquePerMotor),
        totalTorque,
        power,
        voltage: V,
        isCurrentLimited: currentLimit !== null && current > currentLimit,
    };
}

/**
 * Calculate free speed under current limit
 */
function motorFreeSpeedAtLimit(motor, currentLimit, voltage = 12) {
    if (!currentLimit || currentLimit >= motor.stallCurrent) {
        return motor.freeSpeed;
    }
    const V = Math.min(voltage, 12);
    const backEmf = V - currentLimit * motor.resistance;
    const omega = backEmf * motor.kV;
    return omega * 30 / Math.PI;
}

/**
 * Calculate stall torque under current limit
 */
function motorStallTorqueAtLimit(motor, currentLimit, numMotors = 1) {
    if (!currentLimit) {
        return motor.stallTorque * numMotors;
    }
    const effectiveCurrent = Math.min(currentLimit, motor.stallCurrent);
    return motor.kT * (effectiveCurrent - motor.freeCurrent) * numMotors;
}
