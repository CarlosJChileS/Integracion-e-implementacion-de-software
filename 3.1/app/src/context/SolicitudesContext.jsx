import { createContext, useContext, useMemo, useState } from "react";
import { createSolicitud } from "../solicitudes";

const SolicitudesContext = createContext(null);
const solicitudesIniciales = [
    {
        id: "101",
        texto: "Validar inscripción de primer nivel",
        estado: "pendiente",
    },
    {
        id: "102",
        texto: "Confirmar documentos del estudiante",
        estado: "pendiente",
    },
];

export function SolicitudesProvider({ children }) {
    const [solicitudes, setSolicitudes] = useState(solicitudesIniciales);
    const agregarSolicitud = (texto) => {
        const nuevaSolicitud = createSolicitud(texto);
        if (!nuevaSolicitud) return false;
        setSolicitudes((actuales) => [...actuales, nuevaSolicitud]);
        return true;
    };
    const marcarCompletada = (id) =>
        setSolicitudes((actuales) =>
            actuales.map((solicitud) =>
                solicitud.id === id
                    ? { ...solicitud, estado: "completada" }
                    : solicitud,
            ),
        );
    const value = useMemo(
        () => ({ solicitudes, agregarSolicitud, marcarCompletada }),
        [solicitudes],
    );
    return (
        <SolicitudesContext.Provider value={value}>
            {children}
        </SolicitudesContext.Provider>
    );
}

export function useSolicitudes() {
    const context = useContext(SolicitudesContext);
    if (!context)
        throw new Error(
            "useSolicitudes debe usarse dentro de SolicitudesProvider.",
        );
    return context;
}
