# Changelog

Todos los cambios relevantes del proyecto se documentan en este archivo.

## 1.7 - 2026-09-11

### Actualizado

- Se actualizó Bootstrap de `5.3.2` a `5.3.8`, última versión disponible de la rama `5.3.x`.
- Se conservaron la estructura HTML, las clases Bootstrap existentes y el comportamiento de la página para no alterar la vista ni su funcionamiento.

### Corregido

- Se corrigió el filtro de **Exportar Internacional**.
- Ahora se consideran internacionales únicamente las líneas que cumplen alguna de estas condiciones:
  - `+1` seguido de exactamente 10 dígitos adicionales.
  - `1` seguido de exactamente 10 dígitos adicionales, sin signo `+`.
- Se reemplazó el filtro anterior `phoneNumber LIKE '+%'`, que aceptaba cualquier línea iniciada con `+`.
- El contador `Internacional` mostrado en pantalla usa ahora exactamente la misma regla que la exportación para evitar diferencias entre el conteo visual y el archivo TXT generado.

### Documentación

- Se amplió `README.md` con compatibilidad, parámetros, reglas de filtrado y versionado.
- Se agregó `DIAGRAM.md` con el flujo funcional de la aplicación.
- Se actualizó `VERSION` de `1.6` a `1.7`.

## 1.6

- Versión base existente al iniciar el control de cambios de este repositorio.
