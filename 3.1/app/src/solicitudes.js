export function createSolicitud(texto, nextId = Date.now) {
    const normalizado = String(texto ?? "").trim();
    if (!normalizado) return null;
    return { id: String(nextId()), texto: normalizado, estado: "pendiente" };
}

export function countPending(solicitudes) {
    return solicitudes.filter((solicitud) => solicitud.estado === "pendiente")
        .length;
}
