# Práctica 3.1 · Gestión de estado y actualización en tiempo real

**Implementación y demostración:** Carlos Chile
**Proyecto de aula:** ILIA · gestor de solicitudes académicas
**Repositorio:** [Integración e implementación de software](https://github.com/CarlosJChileS/Integracion-e-implementacion-de-software/tree/main/3.1)

## Resultado

La aplicación Expo comparte una sola lista de solicitudes mediante `SolicitudesProvider`. El formulario agrega elementos al contexto y `ListaSolicitudes` y `ResumenSolicitudes` leen ese mismo estado con `useContext`; al crear o completar una solicitud, la lista y los contadores se actualizan inmediatamente, sin recargar la pantalla.

La lista inicial contiene dos solicitudes pendientes. La demostración añade una tercera para comprobar que cambian el listado, el total y el contador de pendientes. El ejercicio para compañeros usa el botón **Listo** para cambiar una solicitud a completada y comprobar que el contador de pendientes disminuye.

## Requisitos

- Node.js LTS y npm.
- Expo Go en un teléfono para ejecutar la app móvil, o un navegador para reproducir la demo web.

## Ejecutar

Desde esta carpeta (`3.1/app`):

```powershell
npm install
npx expo start
```

Escanea el QR con Expo Go para abrir Android o iOS. Para ejecutar en navegador:

```powershell
npx expo start --web
```

## Recorrido técnico

1. `app/src/context/SolicitudesContext.jsx` crea el contexto y el provider. El provider inicializa la lista, implementa `agregarSolicitud` y permite cambiar una solicitud a completada.
2. `app/App.js` envuelve la pantalla con `<SolicitudesProvider>`.
3. `app/src/components/FormularioSolicitud.jsx` valida que el texto no esté vacío y agrega una solicitud a través de `useSolicitudes()`.
4. `app/src/components/SolicitudesPanel.jsx` exporta `ListaSolicitudes` y `ResumenSolicitudes`. Ambos usan `useSolicitudes()`; el resumen cuenta pendientes sobre la misma lista que representa la lista visual.
5. Al agregar una solicitud, React actualiza en memoria el contexto y vuelve a dibujar ambos componentes. Al completar una, la lista cambia su etiqueta y el total pendiente baja.

Fragmento central del provider:

```jsx
const [solicitudes, setSolicitudes] = useState(solicitudesIniciales);

const agregarSolicitud = (texto) => {
  const nuevaSolicitud = createSolicitud(texto);
  if (!nuevaSolicitud) return false;
  setSolicitudes((actuales) => [...actuales, nuevaSolicitud]);
  return true;
};
```

## Conceptos para explicar

- **Estado:** datos que pueden cambiar mientras la pantalla está abierta.
- **Provider:** componente que mantiene la lista y la pone a disposición de los componentes descendientes.
- **useContext:** hook con el que formulario, lista y resumen consultan las acciones o datos del mismo provider.
- **Actualización local:** al ejecutar `setSolicitudes`, React vuelve a renderizar los consumidores del contexto sin recargar la pantalla.
## Actualización local y tiempo real entre dispositivos

Esta práctica demuestra actualización inmediata **local** dentro de la aplicación: los componentes comparten estado en memoria en un solo proceso. No implementa sincronización entre teléfonos ni persistencia en Laravel. Si se cierra la app, las solicitudes creadas desaparecen; otro dispositivo no recibe esos cambios. La sincronización real entre dispositivos requeriría un backend o servicio compartido, persistencia y un canal de actualización, por ejemplo WebSockets o eventos en tiempo real.

## Guion de exposición de 25 minutos

| Tiempo | Actividad |
|---|---|
| 5 min | Explicar estado, Context, `useState`, `useContext` y la diferencia entre actualizar una vista local y sincronizar dispositivos. |
| 10 min | Ejecutar el proyecto, mostrar `SolicitudesContext.jsx`, señalar el provider en `App.js` y agregar una solicitud mientras se observan la lista y los dos contadores. |
| 7 min | Guiar el ejercicio: cada equipo usa **Listo** para completar una solicitud y comprueba el cambio en la fila y en pendientes. |
| 3 min | Verificar el resultado, preguntar qué ocurriría al cerrar la app o abrirla en otro teléfono y resolver dudas. |

### Demostración

1. Inicia la app con Expo Go o en web.
2. Antes de interactuar, observa **2 en total** y **2 pendientes**.
3. Escribe `Comprobar documentos de ingreso` y pulsa **Agregar solicitud**.
4. Comprueba sin recargar: aparece la tercera fila, el total cambia a 3 y pendientes cambia a 3.
5. Pulsa **Listo** en la primera solicitud. La fila aparece como completada y pendientes baja a 2.
6. Aclara que el cambio es local y en memoria; no es una transmisión a otros dispositivos.

## Evidencias

- [Estado inicial](evidencias/antes.png)
- [Después de agregar una solicitud](evidencias/despues-agregar.png)
- [Después de completar una solicitud](evidencias/despues-completar.png)
- [Código del provider](app/src/context/SolicitudesContext.jsx)
- [Colección de pruebas automatizadas](app/tests/solicitudes.test.js)

Las capturas se obtuvieron ejecutando el proyecto en un navegador con viewport móvil. `npm test` valida la normalización del texto, el rechazo de entradas vacías y el cálculo de solicitudes pendientes. La interacción de alta y cambio de estado también se verificó sobre la interfaz renderizada.

## Espacios de Moodle

La guía señala estos espacios para la actividad y sus materiales asociados:

- `Guia_Consulta_Grupal_Tema_3_1_Gestion_Estado`
- `Práctica 3.1 · Gestor de estado en la aplicación`
- `Autónomo 3.1 · Beneficios de la gestión de estado`
## Autor

Carlos Chile (implementación documentada en este repositorio). La guía pide registrar a todos los integrantes de la exposición; solo se incluye aquí el nombre confirmado en esta práctica.
