import test from "node:test";
import assert from "node:assert/strict";
import { createSolicitud, countPending } from "../src/solicitudes.js";

test("normaliza el texto y construye una solicitud pendiente con id único", () => {
    const item = createSolicitud("  Revisar inscripción  ", () => 42);

    assert.deepEqual(item, {
        id: "42",
        texto: "Revisar inscripción",
        estado: "pendiente",
    });
});

test("rechaza solicitudes vacías o compuestas solo por espacios", () => {
    assert.equal(
        createSolicitud("   ", () => 1),
        null,
    );
    assert.equal(
        createSolicitud("", () => 1),
        null,
    );
});

test("cuenta solo las solicitudes pendientes de la lista compartida", () => {
    assert.equal(
        countPending([
            { id: "1", estado: "pendiente" },
            { id: "2", estado: "completada" },
            { id: "3", estado: "pendiente" },
        ]),
        2,
    );
});
