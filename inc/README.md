# Módulos del tema

Esta carpeta contiene la lógica PHP del tema hijo. `functions.php` solo define constantes y carga estos módulos.

## Archivos

- `i18n.php`: carga traducciones desde `/languages`.
- `helpers.php`: funciones compartidas como URLs de login, recuperación y contacto.
- `theme-options.php`: pantalla **Apariencia > Opciones del tema**, diseño administrativo moderno, opciones configurables y pestañas de idiomas.
- `assets.php`: carga CSS/JS del tema e imprime variables CSS para tamaños y colores de botones flotantes.
- `access-rules.php`: reglas para bloquear `/wp-login.php`, `/wp-admin/` y redirigir 404.
- `custom-login.php`: login personalizado en `/panel/admin/`.
- `back-to-top.php`: botón global para volver arriba; su icono, textos, colores y tamaño se administran desde opciones del tema.
- `whatsapp-button.php`: botón flotante global de WhatsApp, colocado arriba de Back to Top cuando ambos están activos; su número, mensaje y tamaño se administran desde opciones del tema. El icono no es administrable y usa `assets/img/whatsapp.svg`.
- `shortcodes.php`: registra únicamente `[powerby]` y `[back]`.
- `agente.php`: arranca el agente de Solución Web, que le reporta al Panel Kinsta quién tiene acceso de administrador y enciende la verificación en dos pasos por correo. Se aparta solo si el plugin suelto *Agente Solución Web* sigue instalado en el sitio.
- `agente/`: las clases del agente. Doce de los trece archivos son **copia exacta** del plugin `agente-sitio`, para poder traer una corrección sin tocarlos. El único que cambia es `agente/class-admin.php`, que dibuja la pantalla **Apariencia > Conexión al panel** con el diseño de este tema en vez del aspecto suelto de WordPress.

## Recursos visuales

Los recursos visuales deben ir en:

```text
assets/img/
```

El SVG fijo de WhatsApp está en:

```text
assets/img/whatsapp.svg
```

## Regla de mantenimiento

El tema incluye `.gitignore` y `.distignore` en la raíz para evitar que se migren archivos basura de macOS/Windows, temporales, logs, carpetas de editores o ZIPs generados.

Si hay que actualizar el agente, se copian los archivos de `includes/` del plugin a `inc/agente/` **menos `class-admin.php`**, que es el único adaptado al diseño del tema.

No agregar lógica directamente en `functions.php`. Para nuevas funcionalidades, crear un módulo claro en esta carpeta y cargarlo desde `functions.php`. Si la funcionalidad es visual y depende del tema, puede quedar aquí; si es una regla de negocio reutilizable entre muchos sitios, conviene convertirla en plugin.
