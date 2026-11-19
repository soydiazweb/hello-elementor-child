=== Hello Elementor Child personalizado ===

Contributors: solucionweb
Requires at least: 5.9
Tested up to: 6.5
Stable tag: 2.4.4
Version: 2.4.4
Requires PHP: 7.4
License: GNU General Public License v3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Tags: elementor, child-theme, custom-login, translation-ready

Tema hijo personalizado para Hello Elementor con login privado, reglas de acceso, botón Back to Top, botón flotante de WhatsApp, shortcodes básicos, seguridad antispam para formularios y opciones administrables desde Apariencia > Opciones del tema.

== Funcionalidades ==

* Acceso privado en /panel/admin/.
* Bloqueo de /wp-login.php y /wp-admin/ para usuarios anónimos.
* Redirección de 404 a la URL de contacto/respaldo configurada.
* Botón global Back to Top con icono, texto, colores y tamaño administrables.
* Botón flotante de WhatsApp con número, tamaño, texto accesible y mensaje administrables.
* Icono fijo de WhatsApp desde assets/img/whatsapp.svg.
* Shortcode [powerby] con año actual, texto y enlace configurables.
* Shortcode [back] con icono, texto y URL fallback configurables.
* Formularios dentro de contenedor formsw: etiquetas flotantes, validación texto/número/teléfono y textarea sin resize.
* Seguridad antispam para formularios Elementor Pro: palabras spam, URLs fuera de campos enlace, fragmentos maliciosos e IPs bloqueadas.
* Traducciones visuales administrables por pestañas de idioma.
* Pantalla administrativa modernizada con textos de ayuda en español.

== Opciones del tema ==

La configuración está en:

Apariencia > Opciones del tema

Desde ahí se administra:

* Back to Top: activación, icono, texto accesible, tamaño, color de fondo, color del icono, color del borde y color hover.
* WhatsApp: activación, número, tamaño, texto accesible y mensaje inicial.
* Back shortcode: icono, texto y URL de respaldo.
* Powerby shortcode: texto, URL, texto del enlace y apertura en nueva pestaña.
* Login y redirecciones: título, subtítulo, texto de regreso y URL de contacto.
* Seguridad antispam: activación, palabras spam, URLs, fragmentos maliciosos, auto-bloqueo e IPs bloqueadas.
* Idiomas: textos administrables organizados por pestañas.

== WhatsApp ==

El botón flotante se configura desde Apariencia > Opciones del tema. Para activarlo, marca Mostrar botón e ingresa el número en formato internacional, sin espacios ni signo +. Ejemplo: 50255555555.

El icono no es editable desde la administración. El tema usa siempre el vector fijo incluido en assets/img/whatsapp.svg.

El botón se muestra arriba de Back to Top cuando ambos están activos y siempre conserva forma circular. Desde opciones solo se administra su tamaño, texto accesible y mensaje inicial.

== Formularios formsw ==

Agrega la clase formsw a un div o contenedor padre. El tema buscará automáticamente cualquier form dentro de ese contenedor, aunque esté en varios niveles internos, y le aplicará las mejoras automáticas.

Ejemplo:

<div class="formsw">
  <div class="contenedor-interno">
    <form>
      <input type="text" name="nombre" placeholder="Nombre">
      <input type="number" name="cantidad" placeholder="Cantidad">
      <input type="tel" name="telefono" placeholder="Teléfono">
      <textarea name="mensaje" placeholder="Mensaje"></textarea>
    </form>
  </div>
</div>

También sigue funcionando si colocas formsw directamente en el form, pero se recomienda usarlo en un contenedor padre.

Funcionamiento:

* El placeholder se convierte en una etiqueta flotante dentro del campo.
* Los campos type="text" no permiten números.
* Los campos type="number" solo permiten números.
* Los textarea tienen resize none.
* Los teléfonos type="tel", class="sw-phone" o data-sw-field="phone" permiten solo números, mínimo 8 y máximo 11 caracteres.

Clases opcionales:

* sw-only-text: fuerza validación de texto sin números.
* sw-only-number: fuerza solo números.
* sw-phone: fuerza validación de teléfono.
* sw-allow-numbers: permite números en un campo type="text", útil para direcciones.
* sw-no-validate: desactiva la validación automática de ese campo.


== Seguridad antispam de formularios ==

El tema integra las funcionalidades del plugin No URL Field para usar solo el tema hijo.

La configuración está en Apariencia > Opciones del tema > Seguridad antispam de formularios.

Funcionamiento:

* Bloquea envíos de formularios de Elementor Pro cuando un campo contiene palabras o frases definidas como spam.
* Bloquea URLs dentro de campos normales de texto, teléfono, email o textarea.
* Permite URLs únicamente cuando el campo del formulario es de tipo enlace/URL.
* Incluso en campos de enlace, bloquea protocolos o fragmentos sospechosos como javascript:, data:, acortadores o dominios agregados a la lista de fragmentos maliciosos.
* Trae una lista inicial editable de IPs y rangos CIDR bloqueados, uno por línea.
* Puede agregar automáticamente a la lista de IPs bloqueadas la IP que envía spam.

Notas:

* Si un campo necesita aceptar un enlace, créalo como campo tipo URL/enlace en Elementor Pro.
* Si un visitante legítimo queda bloqueado por una palabra demasiado general, elimina o ajusta esa palabra en la lista de palabras spam.
* Las opciones antiguas del plugin custom_form_keywords e ips_bloqueadas se migran automáticamente al tema si existían en la base de datos.
* La lista inicial se puede borrar, reemplazar o ampliar desde el administrador. Acepta IP exacta, por ejemplo 203.0.113.10, y rango CIDR, por ejemplo 45.82.76.0/24.

== Idiomas y traducciones ==

Los textos visuales se administran desde Apariencia > Opciones del tema > Idiomas. La sección usa pestañas para separar Español, Inglés, Francés, Italiano, Portugués y Alemán.

Los campos generales funcionan como respaldo. Si un campo de idioma está vacío, el tema usa el texto general.

Para textos técnicos que no están en la pantalla de opciones, usa los archivos .po/.mo dentro de /languages/ o un plugin como Loco Translate.

== Shortcodes ==

= [powerby] =

Muestra el año actual y un enlace configurable.

Ejemplos:

[powerby]
[powerby text="Solución Web"]
[powerby label="Desarrollado por" text="fullcom" url="https://fullcom.ec"]

= [back] =

Muestra un botón para regresar a la página anterior del navegador. Si no existe historial, usa una URL fallback.

Ejemplos:

[back]
[back text="Volver"]
[back icon="‹" fallback="/contacto/"]

== Limpieza de archivos ==

El tema incluye .gitignore y .distignore para evitar archivos basura de macOS, Windows, editores y paquetes generados. Esto ayuda a que no se suban nuevamente archivos como __MACOSX, .DS_Store, ._*, Thumbs.db, Desktop.ini, temporales o logs.

== Instalación ==

1. Subir el ZIP desde Apariencia > Temas > Añadir nuevo > Subir tema.
2. Activar el tema hijo.
3. Ir a Apariencia > Opciones del tema.
4. Configurar textos, colores, tamaños, WhatsApp, shortcodes e idiomas.
5. Guardar enlaces permanentes si /panel/admin/ no responde después de actualizar archivos.

== Mantenimiento ==

No agregar lógica directamente en functions.php. Para nuevas funcionalidades, crear un módulo dentro de inc/. Los estilos deben ir en assets/css/, los scripts en assets/js/ y los recursos visuales en assets/img/.


Corrección 2.5.1 (2026-11-16): los ficheros .mo/.po de themes se nombran por locale (es_GT.mo), se completan traducciones de login y se agregan es/es_419.
