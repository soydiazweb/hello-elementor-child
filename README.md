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
4. Entra a **Apariencia > Opciones del tema** y revisa textos, botones, colores, tamaños, URLs configurables y seguridad antispam de formularios.
5. Entra a **Apariencia > Conexión al panel**, pon la dirección del panel y la clave de registro, y pulsa **Registrar este sitio**.
6. Ve a **Ajustes > Enlaces permanentes** y pulsa **Guardar cambios** si la ruta `/panel/admin/` no responde después de actualizar archivos.

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
│   ├── form-security.php
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
- **Formularios `formsw`:** contenedor para activar etiquetas flotantes desde placeholder, validaciones básicas y textarea sin resize en los formularios internos.
- **Seguridad antispam de formularios:** bloqueo de palabras spam, bloqueo de URLs fuera de campos enlace, bloqueo de fragmentos maliciosos e IPs bloqueadas.
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

## Formularios dentro de un contenedor con clase `formsw`

Para activar las mejoras automáticas, agrega la clase CSS `formsw` a un contenedor padre. El tema buscará automáticamente cualquier `<form>` dentro de ese contenedor, aunque esté en varios niveles internos, y aplicará el efecto y las validaciones.

Ejemplo base:

```html
<div class="formsw">
  <!-- El form puede estar directamente aquí o dentro de otros divs/widgets -->
  <div class="contenedor-interno">
    <form>
      <input type="text" name="nombre" placeholder="Nombre">
      <input type="number" name="cantidad" placeholder="Cantidad">
      <input type="tel" name="telefono" placeholder="Teléfono">
      <textarea name="mensaje" placeholder="Mensaje"></textarea>
      <button type="submit">Enviar</button>
    </form>
  </div>
</div>
```

También sigue funcionando si colocas `formsw` directamente en el `<form>`, pero la forma recomendada es usarla en un `div` o contenedor padre.

Qué hace automáticamente:

- Convierte el `placeholder` de cada `input` y `textarea` en una etiqueta flotante dentro del campo.
- Los `input type="text"` no permiten números.
- Los `input type="number"` solo permiten números.
- Los `textarea` quedan con `resize: none`.
- Los teléfonos aceptan solo números, mínimo 8 y máximo 11 caracteres.

Para campos especiales puedes usar estas clases o atributos:

```html
<input type="text" class="sw-only-text" placeholder="Nombre">
<input type="text" class="sw-only-number" placeholder="Código numérico">
<input type="text" class="sw-phone" placeholder="Teléfono">
<input type="text" class="sw-allow-numbers" placeholder="Dirección con número">
<input type="text" class="sw-no-validate" placeholder="Campo sin validación automática">
```

También puedes usar `data-sw-field`: `text`, `number` o `phone`.


## Seguridad antispam de formularios

El tema integra las funcionalidades del plugin **No URL Field** para que puedas usar solo el tema hijo.

La configuración está en:

```text
Apariencia > Opciones del tema > Seguridad antispam de formularios
```

Funcionamiento:

- Bloquea envíos de formularios de Elementor Pro cuando un campo contiene palabras o frases definidas como spam.
- Bloquea URLs dentro de campos normales de texto, teléfono, email o textarea.
- Permite URLs únicamente cuando el campo del formulario es de tipo enlace/URL.
- Incluso en campos de enlace, bloquea protocolos o fragmentos sospechosos como `javascript:`, `data:`, acortadores o dominios agregados a la lista de fragmentos maliciosos.
- Trae una lista inicial editable de IPs y rangos CIDR bloqueados, uno por línea.
- Puede agregar automáticamente a la lista de IPs bloqueadas la IP que envía spam.

Notas de uso:

- Si un campo necesita aceptar un enlace, créalo como campo tipo URL/enlace en Elementor Pro.
- Si un visitante legítimo queda bloqueado por una palabra demasiado general, elimina o ajusta esa palabra en la lista de palabras spam.
- Las opciones antiguas del plugin `custom_form_keywords` e `ips_bloqueadas` se migran automáticamente al tema si existían en la base de datos.
- La lista inicial se puede borrar, reemplazar o ampliar desde el administrador. Acepta IP exacta, por ejemplo `203.0.113.10`, y rango CIDR, por ejemplo `45.82.76.0/24`.

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

## Corrección de idiomas en 2.5.1 (2026-11-16)

- Corregidos los nombres de las traducciones del tema hijo: WordPress busca `languages/es_GT.mo`, `languages/es_ES.mo`, etc., **no** `languages/hello-elementor-child-es_GT.mo` dentro del tema.
- Se incluyen los archivos `.po` editables y `.mo` compilados para los 17 locales regionales de español, además de `es` y `es_419`.
- Completadas las traducciones de mostrar/ocultar contraseña, mensajes de correos y shortcodes.
- Se mantiene el dominio `hello-elementor-child` para todas las llamadas `__()` y el registro del dominio en `after_setup_theme`.
- Después de actualizar el tema, limpia la caché del sitio (y PHP OPcache si está activo) y abre nuevamente `/panel/admin/`. El sitio debe estar en un locale español en **Ajustes > Generales > Idioma del sitio**.

## Cambios en 2.5.0 (2026-11-09)

- Iconos de mostrar/ocultar contraseña renovados y consistentes en inicio de sesión y restablecimiento, con etiquetas accesibles traducibles.
- Nueva plantilla HTML para los mensajes de texto plano enviados con `wp_mail` (notificaciones de nuevo usuario, cambios de cuenta, recuperación de contraseñas y otras notificaciones de WordPress).
- El color de marca se obtiene de la paleta principal del kit activo de Elementor, luego de los ajustes de color del tema, y finalmente del color neutro del tema hijo. Los cambios de paleta se reflejan en nuevos mensajes automáticamente.
- Enlaces a `wp-login.php` en notificaciones WordPress se convierten a la ruta personalizada `/panel/admin/` en los casos admitidos, preservando parámetros de recuperación.
- Los correos que ya sean HTML se conservan, evitando sobrescribir las plantillas de WooCommerce u otros complementos.
- Desactivar la plantilla global desde PHP: `add_filter( 'hello_elementor_child_enable_email_template', '__return_false' );`.

> La plantilla modifica la presentación, no la infraestructura de entrega. Es recomendable configurar un proveedor SMTP/transaccional y probar la entrega real tras instalar el tema. La versión no modifica archivos del tema padre.
