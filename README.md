# Hello Elementor Child personalizado

Tema hijo para **Hello Elementor** con utilidades pequeñas de presentación y acceso. La lógica está separada por módulos dentro de `inc/`, los estilos/scripts están en `assets/` y el icono fijo de WhatsApp está en `assets/img/whatsapp.svg`.

## Requisitos

- WordPress 5.9 o superior.
- PHP 7.4 o superior recomendado.
- Tema padre activo o instalado: `hello-elementor`.

## Instalación

1. En WordPress entra a **Apariencia > Temas > Añadir nuevo > Subir tema**.
2. Sube el ZIP del tema hijo.
3. Activa **Hello Elementor Child**.
4. Entra a **Apariencia > Opciones del tema** y revisa textos, botones, colores, tamaños y URLs configurables.
5. Ve a **Ajustes > Enlaces permanentes** y pulsa **Guardar cambios** si la ruta `/panel/admin/` no responde después de actualizar archivos.

## Estructura

```text
hello-theme-child/
├── functions.php
├── style.css
├── README.md
├── readme.txt
├── .gitignore
├── .distignore
├── assets/
│   ├── css/
│   │   ├── back-to-top.css
│   │   ├── login.css
│   │   └── shortcodes.css
│   ├── img/
│   │   └── whatsapp.svg
│   └── js/
│       ├── back-to-top.js
│       ├── custom.js
│       └── login.js
├── inc/
│   ├── access-rules.php
│   ├── assets.php
│   ├── back-to-top.php
│   ├── custom-login.php
│   ├── helpers.php
│   ├── i18n.php
│   ├── shortcodes.php
│   ├── theme-options.php
│   └── whatsapp-button.php
└── languages/
```

## Opciones del tema

La configuración está en:

```text
Apariencia > Opciones del tema
```

La pantalla administrativa incluye una guía en español y un diseño más moderno con tarjetas, controles tipo switch y pestañas para idiomas.

Desde ahí se administra:

- **Back to Top:** activación, icono, texto accesible, tamaño, color de fondo, color del icono, color del borde y color al pasar el mouse.
- **WhatsApp:** activación, número, tamaño, texto accesible y mensaje inicial.
- **Shortcode `[back]`:** icono, texto y URL de respaldo.
- **Shortcode `[powerby]`:** texto, URL, texto del enlace y apertura en nueva pestaña.
- **Login y redirecciones:** título, subtítulo, texto de regreso y URL de contacto/respaldo.
- **Idiomas:** traducciones administrables por pestañas.

## Botón flotante de WhatsApp

El botón se configura desde **Apariencia > Opciones del tema > Botón flotante de WhatsApp**.

Para activarlo:

1. Marca **Mostrar botón**.
2. Coloca el número en formato internacional, sin espacios ni signo `+`.
3. Configura el tamaño del botón.
4. Configura el texto accesible y el mensaje inicial.

Ejemplo de número:

```text
50255555555
```

El icono de WhatsApp **no es administrable**. El tema usa siempre el vector fijo incluido en:

```text
assets/img/whatsapp.svg
```

Cuando WhatsApp y Back to Top están activos, WhatsApp se coloca automáticamente arriba de Back to Top. Ambos botones se mantienen circulares aunque cambies el tamaño.

## Back to Top

El botón **Back to Top** aparece globalmente en el frontend y permite volver al inicio de la página actual.

Desde opciones puedes configurar:

- Activar/desactivar.
- Icono.
- Texto accesible.
- Tamaño.
- Color de fondo.
- Color del icono.
- Color del borde.
- Color de fondo al pasar el mouse.

## Shortcodes disponibles

Solo existen 2 shortcodes oficiales.

### `[powerby]`

Muestra el año actual y un enlace configurable.

Ejemplos:

```text
[powerby]
[powerby text="Solución Web"]
[powerby label="Desarrollado por" text="fullcom" url="https://fullcom.ec"]
```

### `[back]`

Muestra un botón para regresar a la página anterior del navegador. Si no existe historial, usa una URL de respaldo.

Ejemplos:

```text
[back]
[back text="Volver"]
[back icon="‹" fallback="/contacto/"]
```

## Idiomas y traducciones administrables

En **Apariencia > Opciones del tema > Idiomas** los textos se administran mediante pestañas.

Idiomas incluidos en la pantalla:

- Español.
- Inglés.
- Francés.
- Italiano.
- Portugués.
- Alemán.

Funcionamiento:

1. Los campos generales funcionan como respaldo.
2. Los campos por idioma reemplazan el texto general cuando WordPress detecta ese idioma.
3. Si un campo de idioma queda vacío, el tema usa el valor general.
4. Para textos técnicos que no aparecen en la pantalla de opciones, usa los archivos `.po/.mo` dentro de `languages/` o un plugin como Loco Translate.

## Login personalizado

El tema crea el acceso privado en:

```text
/panel/admin/
```

También adapta enlaces de login, logout y recuperación de contraseña para evitar que el usuario termine en `wp-login.php`.

## Reglas de acceso

Para usuarios anónimos:

- `/wp-login.php` devuelve 404.
- `/wp-admin/` devuelve 404.
- Las páginas 404 se redirigen a la URL de contacto/respaldo configurada.

Los usuarios con permisos de administración pueden seguir trabajando normalmente.

## Limpieza de archivos

El tema incluye:

```text
.gitignore
.distignore
```

Estos archivos evitan que se suban o empaqueten archivos basura de macOS, Windows, editores, temporales y paquetes generados, por ejemplo:

```text
.DS_Store
__MACOSX/
._*
Thumbs.db
Desktop.ini
.idea/
.vscode/
*.log
*.tmp
*.zip
*.rar
```

## Mantenimiento recomendado

- No agregar lógica directamente en `functions.php`.
- Crear un archivo nuevo dentro de `inc/` para cada funcionalidad nueva.
- Colocar CSS en `assets/css/`.
- Colocar JavaScript en `assets/js/`.
- Mantener recursos visuales, como SVG o imágenes, en `assets/img/`.
- Actualizar la versión en `functions.php`, `style.css` y `readme.txt` cuando haya cambios.
