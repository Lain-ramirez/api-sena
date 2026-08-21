## 1. REGISTER — POST

**URL**
```
http://localhost/api-sena/auth/register.php
```

## 4. VER TODAS LAS TAREAS — GET

**URL**
```
http://localhost/api-sena/tareas.php
```
Sin body.
Respuesta esperada: `200 OK`

---

## 6. ACTUALIZAR TAREA — PUT

**URL**
```
http://localhost/api-sena/tareas.php?id=1
```
**Body (raw → JSON)**
```json
{
    "titulo": "Estudiar PHP avanzado",
    "descripcion": "JWT y arquitectura REST",
    "estado": "en_progreso"
}
```
Respuesta esperada: `200 OK`

---

## 7. ELIMINAR TAREA — DELETE

**URL**
```
http://localhost/api-sena/tareas.php?id=1
```
Sin body.
Respuesta esperada: `200 OK`
