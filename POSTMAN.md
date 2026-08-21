# Pruebas en Postman — API SENA

Base URL: `http://localhost/api-sena`

---

## 1. REGISTER — POST

**URL**
```
http://localhost/api-sena/auth/register.php
```
**Body (raw → JSON)**
```json
{
    "nombre": "Juan Perez",
    "email": "juan@correo.com",
    "password": "clave123"
}
```
Respuesta esperada: `201 Created`

---

## 2. LOGIN — POST

**URL**
```
http://localhost/api-sena/auth/login.php
```
**Body (raw → JSON)**
```json
{
    "email": "juan@correo.com",
    "password": "clave123"
}
```
Respuesta esperada: `200 OK`

---

## 3. CREAR TAREA — POST

**URL**
```
http://localhost/api-sena/tareas.php
```
**Body (raw → JSON)**
```json
{
    "titulo": "Estudiar PHP",
    "descripcion": "Aprender PDO y buenas practicas",
    "estado": "pendiente"
}
```
Respuesta esperada: `201 Created`

---

## 4. VER TODAS LAS TAREAS — GET

**URL**
```
http://localhost/api-sena/tareas.php
```
Sin body.
Respuesta esperada: `200 OK`

---

## 5. VER UNA TAREA — GET

**URL**
```
http://localhost/api-sena/tareas.php?id=1
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

---

## Estados válidos para tareas

```
pendiente
en_progreso
completada
```
