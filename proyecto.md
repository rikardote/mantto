# 🛠 Sistema de Mantenimiento (Laravel)

## 📌 Descripción General

Sistema web para la gestión de solicitudes de mantenimiento por múltiples unidades (hospitales, UMF, áreas administrativas, etc.), con control centralizado por supervisores.

Cada unidad:

* Captura sus propias solicitudes
* Solo visualiza su información

El supervisor:

* Visualiza todas las solicitudes
* Administra el flujo
* Consulta métricas y desempeño

---

# 🏢 Unidades

Listado inicial:

* HG 5 DE DICIEMBRE
* HG FRAY JUNIPERO SERRA
* CH ENSENADA
* CMF MESA DE OTAY
* UMF LOS ALGODONES
* UMF ESTACION DELTA
* UMF SAN FELIPE
* UMF TECATE
* UMF SAN QUINTIN
* UMF ISLA CEDROS
* EBDI 34
* EBDI 59
* EBDI 60
* EBDI 105
* ALMACEN ESTATAL
* DELEGACION

---

# 👥 Roles del Sistema

## 🔹 Unidad (Centro de Trabajo)

* **Control Total de su Área:** Dueño de su propia gestión.
* Crear solicitudes.
* Asignar folios y órdenes de servicio.
* Marcar inicio de trabajos ("En Proceso").
* Finalizar y cerrar sus propios tickets.
* Editar sus solicitudes en cualquier momento.

## 🔹 Supervisor (Administrador de Información)

* Concentra toda la información del sistema.
* Monitorea si se está trabajando en los temas.
* Consulta métricas detalladas en tiempo real.
* Tiene permisos de superusuario para intervenir si es necesario.

## 🔹 Técnico

* **Sin acceso al sistema**
* Notifica avances y finalización de forma verbal/física al Encargado de Área.

---

# 🧱 Estructura de Base de Datos

## 📌 Tabla: unidades

* id
* nombre
* tipo
* activo

---

## 📌 Tabla: users

* id
* name
* email
* password
* rol (unidad, supervisor, tecnico)
* unidad_id (nullable)

---

## 📌 Tabla: servicios

* id
* nombre
* descripcion
* activo

---

## 📌 Tabla: tipos_mantenimiento

* id
* nombre (Preventivo, Correctivo, Predictivo, Emergencia)
* activo

---

## 📌 Tabla: prioridades

* id
* nombre (Alta, Media, Baja)
* tiempo_respuesta_horas
* descripcion

---

## 📌 Tabla: solicitudes_mantenimiento

* id

* unidad_id

* servicio_id

* tipo_mantenimiento_id

* prioridad_id

* titulo

* descripcion

* descripcion_servicio_otro (nullable)

* folio_oficio (nullable)

* orden_servicio (nullable)

* estatus

* fecha_solicitud

* fecha_atencion (nullable)

* fecha_cierre (nullable)

* fecha_limite

* creado_por

---

## 📌 Tabla: avances (opcional)

* id
* solicitud_id
* user_id
* comentario
* porcentaje
* fecha

---

# 🔄 Flujo del Sistema (Workflow)

1. 🟢 Unidad crea solicitud
   → estatus: `abierto`
   → fecha_solicitud

2. 🟡 Supervisor valida
   → estatus: `validado`

3. 🔵 Se genera orden de servicio
   → se llena `orden_servicio`
   → estatus: `asignado`

4. 🟠 Técnico inicia trabajo
   → estatus: `en_proceso`
   → fecha_atencion

5. 🔴 Trabajo finalizado
   → estatus: `terminado`
   → fecha_cierre

---

# ⚙️ Catálogos

## Servicios

* Aire acondicionado / HVAC
* Fontanería / Hidráulico
* Eléctrico
* Calderas
* etc.
* Otro

---

## Tipos de mantenimiento

* Preventivo
* Correctivo
* Predictivo
* Emergencia

---

## Prioridades

* Alta (inmediata)
* Media (48–72 hrs)
* Baja (programable)

---

# 🧠 Reglas de Negocio

## 🔥 Emergencia

* Prioridad automática: Alta

---

## ⏱ SLA

* Se calcula automáticamente:

```
fecha_limite = fecha_solicitud + tiempo_respuesta
```

---

## 🔒 Restricciones

* Unidad solo ve sus solicitudes
* Supervisor ve todo
* Orden de servicio solo la captura supervisor

---

## 📝 Validaciones

* titulo: obligatorio
* descripcion: obligatorio
* servicio: obligatorio
* tipo: obligatorio
* prioridad: obligatoria

---

## 📎 Caso “Otro”

Si servicio = "Otro":

* descripcion_servicio_otro → obligatorio

---

# 📊 Métricas futuras

* Solicitudes por unidad
* Solicitudes por servicio
* % correctivo vs preventivo
* Tiempo de respuesta
* Tiempo de resolución
* Cumplimiento SLA
* Emergencias registradas

---

# 🚀 Fases del Proyecto

## Fase 1 (MVP)

* Login
* CRUD solicitudes
* Catálogos básicos
* Roles
* Flujo básico

## Fase 2

* Dashboard
* Métricas
* Avances

## Fase 3

* Notificaciones
* Documentos (oficios)
* App móvil

---

# 🔥 Resumen

Sistema basado en:

* 🏢 Unidades aisladas
* 🔧 Servicios dinámicos
* ⚙️ Tipos de mantenimiento
* 🚨 Prioridades + SLA
* 📊 Control centralizado
* 🧾 Trazabilidad administrativa

---

**Resultado:**
Un sistema tipo **CMMS institucional escalable** listo para crecer.
