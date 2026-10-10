# Changelog

Todos los cambios relevantes del proyecto se documentan en este archivo.

## 1.10 - 2026-10-09

### Corregido

- Se fijó una sola referencia UTC al inicio de cada ejecución con `$now_utc = gmdate('Y-m-d H:i:s');`.
- Todas las consultas de rangos de tiempo usan ahora el mismo `$now_utc` en lugar de evaluar `UTC_TIMESTAMP()` de forma independiente.
- Esto evita que un registro pueda cambiar de periodo durante la misma ejecución por el paso de segundos entre consultas.
- Se mantienen los rangos con semántica `[from, to)` para evitar huecos y duplicados en las fronteras.
- Se confirmó que `10 min` equivale a `0.1667 hr` y debe mostrarse como `0.17 hr` al redondear a dos decimales.
- Se conserva el cálculo de etiquetas con `$from/60` y `$to/60`; no se resta 1 a `$from`.

### Versionado

- Se actualizó la versión del proyecto de `1.9` a `1.10`.
- Se sincronizaron `phones-filtered.php`, `VERSION`, `README.md`, `CHANGELOG.md`, `DIAGRAM.md` y `prompt.txt`.

## 1.9 - 2026-10-09

### Corregido

- Se consolidó el arreglo de rangos de tiempo como `10→60`, `60→120`, `120→180`, `180→240`, `240→300`, `300→360` y `360→1440`.
- Se corrigieron las seis consultas que filtran por tiempo para usar el límite inicial inclusivo:
  - `reportDate <= ... -$from MINUTE`
  - `reportDate > ... -$to MINUTE`
- Los rangos quedan con semántica `[from, to)`, evitando huecos y evitando duplicados en las fronteras de 60, 120, 180, 240, 300 y 360 minutos.
- Se corrigieron las etiquetas en horas para calcular `$from/60` en lugar de `($from-1)/60`.
- Se normalizó el número de versión mostrado en el `<title>`, comentario del script, `VERSION`, `README.md`, `CHANGELOG.md` y `DIAGRAM.md` a `1.9`.

### Conservado

- Se conserva la conexión MySQL configurada actualmente en `phones-filtered.php`.
- Se conserva Bootstrap `5.3.8`.
- Se conserva jQuery `3.7.1`.
- Se conserva el filtro internacional basado en dos expresiones `REGEXP` unidas con `OR`.
- No se modificó la lógica visual de selección de rangos.

### Documentación

- Se actualizó `prompt.txt` para dejar explícita la semántica `[from, to)` del Objetivo 2.
- Se actualizaron `README.md` y `DIAGRAM.md` para documentar los nuevos límites.

## 1.7 - 2026-09-11

### Actualizado

- Se actualizó Bootstrap de `5.3.2` a `5.3.8`.
- Se conservaron la estructura HTML, las clases Bootstrap existentes y el comportamiento de la página.

### Corregido

- Se corrigió el filtro de **Exportar Internacional**.
- Ahora se consideran internacionales únicamente las líneas que cumplen alguna de estas condiciones:
  - `+1` seguido de exactamente 10 dígitos adicionales.
  - `1` seguido de exactamente 10 dígitos adicionales, sin signo `+`.
- Se reemplazó el filtro anterior `phoneNumber LIKE '+%'`.
- El contador `Internacional` mostrado en pantalla usa la misma regla que la exportación.

### Documentación

- Se amplió `README.md`.
- Se agregó `DIAGRAM.md`.
- Se actualizó `VERSION` de `1.6` a `1.7`.

## 1.6

- Versión base existente al iniciar el control de cambios de este repositorio.
