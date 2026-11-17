import { useState } from "react";
import { Pressable, StyleSheet, Text, TextInput, View } from "react-native";
import { useSolicitudes } from "../context/SolicitudesContext";

export function FormularioSolicitud() {
    const [texto, setTexto] = useState("");
    const [mensaje, setMensaje] = useState("");
    const { agregarSolicitud } = useSolicitudes();
    const guardar = () => {
        if (!agregarSolicitud(texto)) {
            setMensaje("Escribe una solicitud antes de agregarla.");
            return;
        }
        setTexto("");
        setMensaje(
            "Solicitud agregada; la lista y los contadores ya se actualizaron.",
        );
    };
    return (
        <View style={styles.card}>
            <Text style={styles.label}>Nueva solicitud</Text>
            <TextInput
                accessibilityLabel="Descripción de la nueva solicitud"
                onChangeText={(value) => {
                    setTexto(value);
                    setMensaje("");
                }}
                onSubmitEditing={guardar}
                placeholder="Ej. Revisar matrícula de segundo nivel"
                placeholderTextColor="#87968E"
                returnKeyType="done"
                style={styles.input}
                value={texto}
            />
            <Pressable
                accessibilityRole="button"
                onPress={guardar}
                style={({ pressed }) => [
                    styles.button,
                    pressed && styles.pressed,
                ]}
            >
                <Text style={styles.buttonText}>Agregar solicitud</Text>
            </Pressable>
            <Text accessibilityLiveRegion="polite" style={styles.feedback}>
                {mensaje ||
                    "El cambio se refleja localmente, sin recargar la pantalla."}
            </Text>
        </View>
    );
}

const styles = StyleSheet.create({
    card: {
        backgroundColor: "#FFFFFF",
        borderColor: "#E5E9E2",
        borderWidth: 1,
        borderRadius: 20,
        padding: 18,
        marginTop: 22,
    },
    label: {
        color: "#243F39",
        fontSize: 14,
        fontWeight: "700",
        marginBottom: 9,
    },
    input: {
        minHeight: 50,
        borderColor: "#D9E1DA",
        borderWidth: 1,
        borderRadius: 12,
        color: "#203B36",
        fontSize: 14,
        paddingHorizontal: 14,
    },
    button: {
        minHeight: 48,
        alignItems: "center",
        justifyContent: "center",
        backgroundColor: "#D9764E",
        borderRadius: 12,
        marginTop: 11,
    },
    pressed: { opacity: 0.82 },
    buttonText: { color: "#FFFFFF", fontSize: 14, fontWeight: "700" },
    feedback: { color: "#60756C", fontSize: 12, lineHeight: 18, marginTop: 11 },
});
