import { ScrollView, StatusBar, StyleSheet, Text, View } from "react-native";
import { FormularioSolicitud } from "./src/components/FormularioSolicitud";
import {
    ListaSolicitudes,
    ResumenSolicitudes,
} from "./src/components/SolicitudesPanel";
import { SolicitudesProvider } from "./src/context/SolicitudesContext";

function PantallaSolicitudes() {
    return (
        <ScrollView
            contentContainerStyle={styles.page}
            keyboardShouldPersistTaps="handled"
        >
            <View style={styles.header}>
                <View style={styles.brandMark}>
                    <Text style={styles.brandInitials}>I</Text>
                </View>
                <Text style={styles.brand}>
                    ILIA{" "}
                    <Text style={styles.brandLight}>/ GESTIÓN ACADÉMICA</Text>
                </Text>
            </View>
            <Text style={styles.kicker}>PRÁCTICA 3.1 · GESTIÓN DE ESTADO</Text>
            <Text style={styles.title}>Solicitudes{"\n"}en curso</Text>
            <Text style={styles.intro}>
                Un solo estado compartido mantiene sincronizados el formulario,
                la lista y el resumen.
            </Text>
            <FormularioSolicitud />
            <ResumenSolicitudes />
            <ListaSolicitudes />
            <View style={styles.footer}>
                <Text style={styles.footerText}>
                    DEMO LOCAL · React Context + useState
                </Text>
                <Text style={styles.footerSubtext}>
                    El estado vive en memoria en este dispositivo.
                </Text>
            </View>
        </ScrollView>
    );
}

export default function App() {
    return (
        <SolicitudesProvider>
            <View style={styles.screen}>
                <StatusBar barStyle="dark-content" />
                <PantallaSolicitudes />
            </View>
        </SolicitudesProvider>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: "#F4F3EC" },
    page: {
        width: "100%",
        maxWidth: 620,
        alignSelf: "center",
        paddingHorizontal: 22,
        paddingTop: 24,
        paddingBottom: 40,
    },
    header: { flexDirection: "row", alignItems: "center", marginBottom: 34 },
    brandMark: {
        width: 34,
        height: 34,
        borderRadius: 11,
        backgroundColor: "#173C3A",
        alignItems: "center",
        justifyContent: "center",
        marginRight: 10,
    },
    brandInitials: { color: "#FFFFFF", fontWeight: "800", fontSize: 16 },
    brand: {
        color: "#173C3A",
        fontSize: 12,
        fontWeight: "800",
        letterSpacing: 1.3,
    },
    brandLight: { color: "#71847B", fontWeight: "600", letterSpacing: 0.7 },
    kicker: {
        color: "#C36E4B",
        fontSize: 11,
        fontWeight: "800",
        letterSpacing: 1.2,
    },
    title: {
        color: "#173C3A",
        fontSize: 37,
        fontWeight: "700",
        lineHeight: 42,
        marginTop: 10,
    },
    intro: {
        color: "#60736B",
        fontSize: 14,
        lineHeight: 21,
        marginTop: 10,
        maxWidth: 480,
    },
    footer: {
        borderTopColor: "#DCE3DC",
        borderTopWidth: 1,
        paddingTop: 16,
        marginTop: 25,
    },
    footerText: {
        color: "#45675D",
        fontSize: 10,
        fontWeight: "800",
        letterSpacing: 1,
    },
    footerSubtext: { color: "#71847B", fontSize: 12, marginTop: 5 },
});
