/**
 * FRC Compressor Database
 * cfmPoly: polynomial coefficients for CFM as a function of pressure (PSI)
 *   CFM(P) = c[0] + c[1]*P + c[2]*P^2 + c[3]*P^3 + ...
 */
const COMPRESSORS = [
    {
        name: "VIAIR 90C",
        cfmPoly: [0.380, -0.002725, 0.00000725],
        maxPressure: 120,
    },
    {
        name: "VIAIR 250C-IG",
        cfmPoly: [1.050, -0.005500, 0.00001100],
        maxPressure: 120,
    },
    {
        name: "VIAIR 330C-IG",
        cfmPoly: [0.780, -0.003900, 0.00000700],
        maxPressure: 120,
    },
    {
        name: "Thomas 215",
        cfmPoly: [0.300, -0.001800, 0.00000400],
        maxPressure: 120,
    },
    {
        name: "Thomas 405",
        cfmPoly: [0.540, -0.003200, 0.00000750],
        maxPressure: 120,
    },
    {
        name: "AndyMark 1.1 Compressor",
        cfmPoly: [0.310, -0.002100, 0.00000500],
        maxPressure: 120,
    },
    {
        name: "CP26 Compressor",
        cfmPoly: [0.260, -0.001550, 0.00000350],
        maxPressure: 120,
    },
];

/**
 * Calculate compressor CFM at a given pressure
 */
function compressorCFM(compressor, pressurePsi) {
    let cfm = 0;
    for (let i = 0; i < compressor.cfmPoly.length; i++) {
        cfm += compressor.cfmPoly[i] * Math.pow(pressurePsi, i);
    }
    return Math.max(0, cfm);
}
