
# AulaFix

Aplicación web para la gestión de incidencias informáticas en el aula.

AulaFix permite registrar, consultar y gestionar problemas relacionados con los equipos informáticos de un centro educativo. Su objetivo es facilitar la comunicación entre alumnos y profesores y mejorar el seguimiento de las incidencias.

## 1. Tecnologías utilizadas

### Backend
- PHP 8.3.
- PDO para la conexión con la base de datos.
- MySQL 8.4.
- Programación orientada a objetos y programación estructurada según las necesidades del proyecto.

### Frontend
- HTML5.
- CSS3.
- JavaScript vanilla.

### Entorno de desarrollo
- Docker.
- Apache.
- phpMyAdmin.
- Visual Studio Code.
- Git y GitHub.

## 2. Funcionalidades principales

### Alumnos
- Registro e inicio de sesión.
- Consulta de incidencias.
- Creación de nuevas incidencias.
- Consulta del detalle de una incidencia.
- Eliminación de sus propias incidencias abiertas.

### Profesores
- Inicio de sesión.
- Consulta de todas las incidencias.
- Creación de incidencias.
- Consulta del detalle de las incidencias.
- Modificación del estado de las incidencias.
- Incorporación de comentarios al historial.
- Eliminación de incidencias.

## 3. Estructura del proyecto

```text
aulafix/
│
├── config.php
├── .env
├── README.md
│
├── database/
│   ├── esquema.sql
│   └── instalar.php
│
├── includes/
│   ├── auth.php
│   ├── db.php
│   ├── funciones.php
│   ├── cabecera.php
│   └── pie.php
│
└── public/
    ├── registro.php
    ├── login.php
    ├── logout.php
    ├── incidencias.php
    ├── nueva.php
    ├── incidencia.php
    │
    ├── css/
    │   └── estilos.css
    │
    └── js/
        ├── main.js
        ├── incidencia.js
        ├── vistas.js
        ├── fechas.js
        ├── preferencias.js
        └── nueva.js
```

## 4. Base de datos

El proyecto utiliza una base de datos MySQL denominada `aulafix`.

Está formada por cuatro tablas principales:

| Tabla | Descripción |
|---|---|
| usuarios | Almacena alumnos y profesores. |
| equipos | Registra los equipos informáticos. |
| incidencias | Almacena los problemas comunicados. |
| historial | Registra los cambios de estado y comentarios. |

El esquema completo se encuentra en:

`database/esquema.sql`

## 5. Configuración del entorno

El proyecto utiliza variables de entorno para gestionar las credenciales de conexión a MySQL.

Se deben configurar las siguientes variables en el archivo `.env`:

- DB_ROOT_PASSWORD
- DB_USER
- DB_PASS

El archivo `.env` es privado y no debe subirse al repositorio.

La conexión con MySQL se realiza desde `config.php` mediante PDO.

## 6. Ejecución del proyecto

El entorno de desarrollo utiliza tres contenedores Docker:

| Servicio | Puerto |
|---|---|
| Aplicación PHP y Apache | 8000 |
| phpMyAdmin | 8080 |
| MySQL | 3307 |

Direcciones locales:

- Aplicación: http://localhost:8000
- phpMyAdmin: http://localhost:8080
- MySQL: localhost:3307

Los contenedores deben estar iniciados para poder utilizar la aplicación.

## 7. Instalación de la base de datos

1. Iniciar los servicios Docker de AulaFix.
2. Acceder a phpMyAdmin.
3. Crear la base de datos `aulafix`, si todavía no existe.
4. Importar el archivo `database/esquema.sql`.
5. Comprobar que se han creado las cuatro tablas.
6. Ejecutar el instalador de datos de ejemplo desde el contenedor PHP:

```bash
docker exec aulafix-php php /var/www/html/database/instalar.php
```

El instalador incorpora usuarios, equipos, incidencias y registros de historial de prueba.

## 8. Seguridad

AulaFix incorpora diferentes mecanismos de seguridad:

- Contraseñas almacenadas mediante `password_hash()` con Argon2id.
- Verificación de contraseñas mediante `password_verify()`.
- Consultas preparadas con PDO.
- Validación de datos en el servidor.
- Protección CSRF en los formularios.
- Control de acceso según el rol del usuario.
- Regeneración del identificador de sesión al iniciar sesión.
- Escape de contenido HTML para prevenir ataques XSS.

## 9. Control de versiones

El desarrollo del proyecto se realiza utilizando Git y GitHub.

Se utilizan ramas independientes para organizar el trabajo de backend y frontend, facilitando la colaboración y la integración de cambios.

## 10. Proyecto académico

AulaFix es un proyecto integrador desarrollado en el ciclo formativo de Desarrollo de Aplicaciones Web (DAW).

Su finalidad es aplicar los conocimientos adquiridos en programación, desarrollo web, bases de datos, seguridad y gestión de proyectos.
