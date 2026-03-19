/**
 * FRC Motor Database
 * All specs at 12V nominal unless noted
 * freeSpeed: RPM, stallTorque: N·m, stallCurrent: A, freeCurrent: A, weight: kg
 * controllerWeight: additional controller weight in kg (0 for integrated)
 */
const MOTORS = [
    // --- Brushless (integrated controller) ---
    {
        name: "Kraken X60 (FOC)",
        freeSpeed: 5800,
        stallTorque: 9.37,
        stallCurrent: 483,
        freeCurrent: 2,
        weight: 0.3515,
        controllerWeight: 0,
        diameter: 0.06,
    },
    {
        name: "Kraken X60",
        freeSpeed: 6000,
        stallTorque: 7.09,
        stallCurrent: 366,
        freeCurrent: 2,
        weight: 0.3515,
        controllerWeight: 0,
        diameter: 0.06,
    },
    {
        name: "Kraken X44 (FOC)",
        freeSpeed: 7100,
        stallTorque: 5.29,
        stallCurrent: 276,
        freeCurrent: 2,
        weight: 0.290,
        controllerWeight: 0,
        diameter: 0.044,
    },
    {
        name: "Kraken X44",
        freeSpeed: 7400,
        stallTorque: 3.98,
        stallCurrent: 209,
        freeCurrent: 2,
        weight: 0.290,
        controllerWeight: 0,
        diameter: 0.044,
    },
    {
        name: "NEO Vortex",
        freeSpeed: 6784,
        stallTorque: 3.60,
        stallCurrent: 211,
        freeCurrent: 3.6,
        weight: 0.348,
        controllerWeight: 0,
        diameter: 0.06,
    },
    {
        name: "Falcon 500 (FOC)",
        freeSpeed: 6080,
        stallTorque: 5.84,
        stallCurrent: 304,
        freeCurrent: 1.5,
        weight: 0.544,
        controllerWeight: 0,
        diameter: 0.06,
    },
    {
        name: "Falcon 500",
        freeSpeed: 6380,
        stallTorque: 4.69,
        stallCurrent: 257,
        freeCurrent: 1.5,
        weight: 0.544,
        controllerWeight: 0,
        diameter: 0.06,
    },
    {
        name: "NEO 2.0",
        freeSpeed: 5676,
        stallTorque: 2.6,
        stallCurrent: 105,
        freeCurrent: 1.8,
        weight: 0.425,
        controllerWeight: 0.113,
        diameter: 0.06,
    },
    {
        name: "NEO",
        freeSpeed: 5676,
        stallTorque: 2.6,
        stallCurrent: 105,
        freeCurrent: 1.8,
        weight: 0.425,
        controllerWeight: 0.113,
        diameter: 0.06,
    },
    {
        name: "NEO 550",
        freeSpeed: 11000,
        stallTorque: 0.97,
        stallCurrent: 100,
        freeCurrent: 1.4,
        weight: 0.235,
        controllerWeight: 0.113,
        diameter: 0.036,
    },
    {
        name: "Minion",
        freeSpeed: 6100,
        stallTorque: 1.84,
        stallCurrent: 83,
        freeCurrent: 1.2,
        weight: 0.195,
        controllerWeight: 0.145,
        diameter: 0.040,
    },
    {
        name: "Minion (Adv Hall)",
        freeSpeed: 5820,
        stallTorque: 2.30,
        stallCurrent: 98,
        freeCurrent: 1.2,
        weight: 0.195,
        controllerWeight: 0.145,
        diameter: 0.040,
    },
    {
        name: "Thrifty Pulsar",
        freeSpeed: 6000,
        stallTorque: 1.20,
        stallCurrent: 50,
        freeCurrent: 0.7,
        weight: 0.230,
        controllerWeight: 0.113,
        diameter: 0.040,
    },

    // --- Brushed (require external controller) ---
    {
        name: "CIM",
        freeSpeed: 5330,
        stallTorque: 2.41,
        stallCurrent: 131,
        freeCurrent: 2.7,
        weight: 1.065,
        controllerWeight: 0.113,
        diameter: 0.066,
    },
    {
        name: "Mini CIM",
        freeSpeed: 5840,
        stallTorque: 1.41,
        stallCurrent: 89,
        freeCurrent: 3,
        weight: 0.703,
        controllerWeight: 0.113,
        diameter: 0.057,
    },
    {
        name: "775pro",
        freeSpeed: 18730,
        stallTorque: 0.71,
        stallCurrent: 134,
        freeCurrent: 0.7,
        weight: 0.365,
        controllerWeight: 0.113,
        diameter: 0.042,
    },
    {
        name: "775 RedLine",
        freeSpeed: 19500,
        stallTorque: 0.64,
        stallCurrent: 122,
        freeCurrent: 0.7,
        weight: 0.340,
        controllerWeight: 0.113,
        diameter: 0.042,
    },
    {
        name: "BAG Motor",
        freeSpeed: 13180,
        stallTorque: 0.43,
        stallCurrent: 53,
        freeCurrent: 1.8,
        weight: 0.347,
        controllerWeight: 0.113,
        diameter: 0.042,
    },
    {
        name: "AM 9015",
        freeSpeed: 14270,
        stallTorque: 0.36,
        stallCurrent: 71,
        freeCurrent: 3.7,
        weight: 0.226,
        controllerWeight: 0.113,
        diameter: 0.036,
    },
    {
        name: "BaneBots 550",
        freeSpeed: 19000,
        stallTorque: 0.38,
        stallCurrent: 84,
        freeCurrent: 0.4,
        weight: 0.200,
        controllerWeight: 0.113,
        diameter: 0.036,
    },
    {
        name: "Snowblower",
        freeSpeed: 100,
        stallTorque: 16.95,
        stallCurrent: 24,
        freeCurrent: 5,
        weight: 2.041,
        controllerWeight: 0.113,
        diameter: 0.086,
    },

    // --- FTC / Other motors ---
    {
        name: "HD Hex Motor",
        freeSpeed: 6000,
        stallTorque: 0.173,
        stallCurrent: 9.8,
        freeCurrent: 0.25,
        weight: 0.178,
        controllerWeight: 0,
        diameter: 0.040,
    },
    {
        name: "Core Hex Motor",
        freeSpeed: 6000,
        stallTorque: 0.173,
        stallCurrent: 9.8,
        freeCurrent: 0.25,
        weight: 0.230,
        controllerWeight: 0,
        diameter: 0.040,
    },
    {
        name: "NeveRest",
        freeSpeed: 6600,
        stallTorque: 0.173,
        stallCurrent: 9.8,
        freeCurrent: 0.35,
        weight: 0.255,
        controllerWeight: 0,
        diameter: 0.042,
    },
    {
        name: "Modern Robotics",
        freeSpeed: 6600,
        stallTorque: 0.173,
        stallCurrent: 9.8,
        freeCurrent: 0.35,
        weight: 0.209,
        controllerWeight: 0,
        diameter: 0.042,
    },
    {
        name: "V5 Smart Motor",
        freeSpeed: 3600,
        stallTorque: 0.891,
        stallCurrent: 2.5,
        freeCurrent: 0.14,
        weight: 0.342,
        controllerWeight: 0,
        diameter: 0.060,
    },
    {
        name: "goBILDA 5202 (1150 RPM)",
        freeSpeed: 1150,
        stallTorque: 3.2,
        stallCurrent: 9.2,
        freeCurrent: 0.3,
        weight: 0.340,
        controllerWeight: 0,
        diameter: 0.040,
    },
    {
        name: "goBILDA 5203 (435 RPM)",
        freeSpeed: 435,
        stallTorque: 8.5,
        stallCurrent: 9.2,
        freeCurrent: 0.3,
        weight: 0.340,
        controllerWeight: 0,
        diameter: 0.040,
    },
    {
        name: "goBILDA 5204 (312 RPM)",
        freeSpeed: 312,
        stallTorque: 11.8,
        stallCurrent: 9.2,
        freeCurrent: 0.3,
        weight: 0.340,
        controllerWeight: 0,
        diameter: 0.040,
    },
    {
        name: "goBILDA 5204 (60 RPM)",
        freeSpeed: 60,
        stallTorque: 61.3,
        stallCurrent: 9.2,
        freeCurrent: 0.3,
        weight: 0.340,
        controllerWeight: 0,
        diameter: 0.040,
    },
];

// Compute derived motor constants
MOTORS.forEach(m => {
    const V = 12; // nominal voltage
    m.resistance = V / m.stallCurrent;
    m.kV = m.freeSpeed * (Math.PI / 30) / (V - m.resistance * m.freeCurrent); // rad/s per volt
    m.kT = m.stallTorque / m.stallCurrent; // N·m per amp
    m.maxPower = (m.freeSpeed / 2) * (Math.PI / 30) * (m.stallTorque / 2); // at half speed
    m.totalWeight = m.weight + (m.controllerWeight || 0);
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

/**
 * Calculate power at a given current limit
 */
function motorPowerAtCurrentLimit(motor, currentLimit) {
    if (!currentLimit || currentLimit >= motor.stallCurrent) {
        return motor.maxPower;
    }
    // At the current limit, the motor reaches a speed where back-EMF limits current
    const freeSpeedLimited = motorFreeSpeedAtLimit(motor, currentLimit);
    const stallTorqueLimited = motor.kT * (currentLimit - motor.freeCurrent);
    // Peak power is at half the limited free speed
    return (freeSpeedLimited / 2) * (Math.PI / 30) * (stallTorqueLimited / 2);
}
