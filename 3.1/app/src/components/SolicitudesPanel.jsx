import { Pressable, StyleSheet, Text, View } from "react-native";
import { useSolicitudes } from "../context/SolicitudesContext";
import { countPending } from "../solicitudes";

export function ResumenSolicitudes() {
    const { solicitudes } = useSolicitudes();
    return (
        <View style={styles.summary} accessibilityLiveRegion="polite">
            <Text style={styles.eyebrow}>ESTADO COMPARTIDO</Text>
            <View style={styles.numbers}>
                <View style={styles.metric}>
                    <Text style={styles.number}>{solicitudes.length}</Text>
                    <Text style={styles.label}>En total</Text>
                </View>
                <View style={styles.divider} />
                <View style={styles.metric}>
                    <Text style={[styles.number, styles.pendingNumber]}>
                        {countPending(solicitudes)}
                    </Text>
                    <Text style={styles.label}>Pendientes</Text>
                </View>
            </View>
            <Text style={styles.note}>
                La lista y este contador leen el mismo contexto.
            </Text>
        </View>
    );
}

export function ListaSolicitudes() {
    const { solicitudes, marcarCompletada } = useSolicitudes();
    return (
        <View style={styles.list}>
            <View style={styles.listHeading}>
                <Text style={styles.sectionTitle}>Solicitudes</Text>
                <Text style={styles.listCount}>
                    {solicitudes.length} registros
                </Text>
            </View>
            {solicitudes.map((solicitud, index) => {
                const pendiente = solicitud.estado === "pendiente";
                return (
                    <View key={solicitud.id} style={styles.row}>
                        <View
                            style={[
                                styles.index,
                                !pendiente && styles.indexDone,
                            ]}
                        >
                            <Text
                                style={[
                                    styles.indexText,
                                    !pendiente && styles.indexTextDone,
                                ]}
                            >
                                {String(index + 1).padStart(2, "0")}
                            </Text>
                        </View>
                        <View style={styles.rowBody}>
                            <Text style={styles.requestText}>
                                {solicitud.texto}
                            </Text>
                            <Text
                                style={[
                                    styles.status,
                                    !pendiente && styles.statusDone,
                                ]}
                            >
                                {pendiente ? "PENDIENTE" : "COMPLETADA"}
                            </Text>
                        </View>
                        {pendiente ? (
                            <Pressable
                                accessibilityRole="button"
                                accessibilityLabel={`Marcar completada: ${solicitud.texto}`}
                                onPress={() => marcarCompletada(solicitud.id)}
                                style={({ pressed }) => [
                                    styles.completeButton,
                                    pressed && styles.pressed,
                                ]}
                            >
                                <Text style={styles.completeText}>Listo</Text>
                            </Pressable>
                        ) : (
                            <View style={styles.check}>
                                <Text style={styles.checkText}>OK</Text>
                            </View>
                        )}
                    </View>
                );
            })}
        </View>
    );
}

const styles = StyleSheet.create({
    summary: {
        backgroundColor: "#173C3A",
        borderRadius: 22,
        padding: 22,
        marginTop: 22,
    },
    eyebrow: {
        color: "#A6D5C8",
        fontSize: 11,
        fontWeight: "700",
        letterSpacing: 1.4,
    },
    numbers: { flexDirection: "row", alignItems: "center", marginTop: 14 },
    metric: { flex: 1 },
    number: {
        color: "#F8F5ED",
        fontSize: 38,
        fontWeight: "700",
        lineHeight: 44,
    },
    pendingNumber: { color: "#F3C47B" },
    label: { color: "#D2E1D9", fontSize: 13, marginTop: 2 },
    divider: {
        height: 42,
        width: 1,
        backgroundColor: "#50716A",
        marginHorizontal: 8,
    },
    note: { color: "#C1D2CB", fontSize: 12, marginTop: 18 },
    list: { marginTop: 28 },
    listHeading: {
        flexDirection: "row",
        justifyContent: "space-between",
        alignItems: "center",
        marginBottom: 12,
    },
    sectionTitle: { color: "#183331", fontSize: 20, fontWeight: "700" },
    listCount: { color: "#697C75", fontSize: 12 },
    row: {
        minHeight: 76,
        flexDirection: "row",
        alignItems: "center",
        backgroundColor: "#FFFFFF",
        borderColor: "#E5E9E2",
        borderWidth: 1,
        borderRadius: 16,
        padding: 12,
        marginBottom: 9,
    },
    index: {
        width: 38,
        height: 38,
        borderRadius: 12,
        backgroundColor: "#E7F2ED",
        alignItems: "center",
        justifyContent: "center",
    },
    indexDone: { backgroundColor: "#EEF0EB" },
    indexText: { color: "#28645A", fontSize: 12, fontWeight: "700" },
    indexTextDone: { color: "#818B84" },
    rowBody: { flex: 1, paddingHorizontal: 11 },
    requestText: {
        color: "#223C38",
        fontSize: 14,
        fontWeight: "600",
        lineHeight: 19,
    },
    status: {
        color: "#A36520",
        fontSize: 10,
        fontWeight: "700",
        letterSpacing: 0.7,
        marginTop: 4,
    },
    statusDone: { color: "#557368" },
    completeButton: {
        minHeight: 44,
        minWidth: 54,
        borderRadius: 12,
        backgroundColor: "#F1F4EF",
        alignItems: "center",
        justifyContent: "center",
        paddingHorizontal: 10,
    },
    pressed: { opacity: 0.7 },
    completeText: { color: "#315A4E", fontSize: 12, fontWeight: "700" },
    check: {
        width: 38,
        height: 38,
        borderRadius: 12,
        backgroundColor: "#E7F2ED",
        alignItems: "center",
        justifyContent: "center",
    },
    checkText: { color: "#28645A", fontSize: 10, fontWeight: "800" },
});
