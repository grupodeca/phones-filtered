# phones-filtered

Aplicación PHP para consultar líneas telefónicas asociadas a dispositivos GPS que han dejado de reportar dentro de diferentes rangos de tiempo y exportar subconjuntos de líneas Telcel, internacionales o Webservice.

## Versión

Versión actual: **1.12**

El archivo `VERSION` contiene la versión vigente del proyecto.

## Compatibilidad

- PHP 5.4.16
- MySQL 5.7
- Bootstrap 5.3.8
- jQuery 3.7.1

Bootstrap se carga desde jsDelivr usando la versión 5.3.8 y sus valores SRI (`integrity`) correspondientes.

## Funcionalidad principal

La página consulta `pwd5_server.gps_info` y muestra dispositivos válidos agrupados por rangos continuos de minutos sin reportar:

- 10 <= minutos < 60
- 60 <= minutos < 120
- 120 <= minutos < 180
- 180 <= minutos < 240
- 240 <= minutos < 300
- 300 <= minutos < 360
- 360 <= minutos < 1440

Los límites se manejan como intervalos `[from, to)`: el límite inicial está incluido y el límite final no. Por ejemplo, una línea con exactamente 60 minutos pertenece al rango `60 <= minutos < 120`, no al rango anterior.

Permite:

- Mostrar todos los rangos o seleccionar rangos específicos desde la interfaz.
- Mostrar columnas adicionales con `?extra=true`.
- Filtrar IDs concretos con `?ids=ID1,ID2,...`.
- Exportar líneas Telcel con `?export=telcel`.
- Exportar líneas internacionales con `?export=intl`.
- Exportar líneas Webservice con `?export=webservice`.

## Notas informativas (v1.12)

Las tres notas originales de Helpdesk Telcel (reseteo de línea, borrado de registro y PDPs) se muestran dentro de un acordeón Bootstrap 5.3.8 titulado **Notas**, situado debajo de la fecha y hora y antes de los filtros. El panel inicia cerrado y puede abrirse o cerrarse con el botón. Los tres mensajes conservan su contenido y estilo `alert alert-primary` sin cambios.

## Filtro de tiempo

Todas las consultas que trabajan con los rangos usan:

```sql
reportDate <= ADDDATE('$now_utc', INTERVAL -$from MINUTE)
AND reportDate > ADDDATE('$now_utc', INTERVAL -$to MINUTE)
```

Esto evita huecos y evita que una misma línea aparezca en dos rangos contiguos.

Además, la versión 1.10 fija una sola referencia UTC al inicio de la ejecución:

```php
$now_utc = gmdate('Y-m-d H:i:s');
```

Todas las consultas de rangos usan ese mismo valor:

```sql
reportDate <= ADDDATE('$now_utc', INTERVAL -$from MINUTE)
AND reportDate > ADDDATE('$now_utc', INTERVAL -$to MINUTE)
```

Esto evita que un registro pueda cambiar de rango por los segundos transcurridos entre consultas distintas dentro de una misma ejecución.

Las etiquetas de horas se calculan con `$from/60` y `$to/60`. Por ejemplo, `10 min = 0.1667 hr`, por lo que redondeado a dos decimales se muestra correctamente como `0.17 hr`.

## Filtro Telcel

Una línea se considera Telcel para la exportación cuando:

```sql
phoneNumber LIKE '844%'
```

## Filtro Internacional

Una línea se considera internacional solamente cuando cumple una de estas dos condiciones:

1. Tiene `+`, después comienza con `1` y contiene exactamente 11 dígitos numéricos después del signo `+`.
   - Ejemplo: `+14243090604`
2. No tiene `+`, comienza con `1` y contiene exactamente 11 dígitos numéricos.
   - Ejemplo: `16615209812`

La condición usada por MySQL es:

```sql
(
	phoneNumber REGEXP '^[+]1[0-9]{10}$'
	OR phoneNumber REGEXP '^1[0-9]{10}$'
)
```

La misma condición se utiliza tanto para el archivo generado por **Exportar Internacional** como para el contador `Internacional` mostrado en pantalla.

Ejemplos que deben incluirse:

```text
+14243090604
+15303589169
16615209812
16615200392
+13239701924
```

## Exportación Webservice (desde v1.11)

- URL: `?export=webservice`.
- Archivo generado: `phone-filtered-webservice.txt` en el mismo directorio de la aplicación.
- Solo incluye dispositivos con `i.idgpstype IN (59,60)`.
- No se aplica un prefijo telefónico especial (`$where_number = "1=1"`); se conservan `isValid = 1`, teléfono no nulo ni cero, filtro opcional `ids` y los mismos siete rangos de antigüedad.
- Los SQL de conteo y exportación usan la misma condición `i.idgpstype IN (59,60)` y el mismo `$now_utc`.
- No cambia el contador de la pantalla ni las exportaciones Telcel e Internacional, que siguen excluyendo los tipos `(59,60,82,78,105,106)`.

## Versionado del prompt

La primera línea de `prompt.txt` contiene `# Version 1.12`. Debe actualizarse a la vez que el comentario de versión de `phones-filtered.php` y el archivo `VERSION`.

## Archivos del proyecto

- `phones-filtered.php`: aplicación principal.
- `README.md`: documentación de uso y reglas principales.
- `CHANGELOG.md`: historial de cambios por versión.
- `DIAGRAM.md`: diagrama del flujo funcional.
- `VERSION`: versión vigente.
- `prompt.txt`: requerimientos utilizados para las modificaciones solicitadas.

## Versionado

La versión inicial registrada del script es `1.6`.

La versión actual de esta entrega es `1.12`.
