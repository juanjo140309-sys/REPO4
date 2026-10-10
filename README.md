# Gestión de artículos — PHP + MySQL

Aplicación web para registrar, consultar, buscar, editar y eliminar artículos.

## Campos
- Nombre
- Marca
- Unidades
- Ubicación

## Instalación en AlwaysData

1. Sube `index.php`, `config.php` y `styles.css` a la carpeta web de tu sitio.
2. Abre `config.php` y coloca la contraseña real de MySQL en `$pass`.
3. Confirma que la base de datos `juancxo_articulosgit` ya exista en AlwaysData y que el usuario tenga permisos sobre ella.
4. Visita el dominio de tu sitio. La aplicación crea la tabla `articulos` automáticamente si no existe.

## Seguridad
- No publiques `config.php` con la contraseña real en un repositorio público.
- Si la contraseña compartida anteriormente era real, cámbiala desde el panel de AlwaysData y usa la nueva en `config.php`.
- Las consultas se ejecutan con PDO y parámetros preparados.
