# phones-filtered

Aplicación PHP para consultar líneas telefónicas asociadas a dispositivos GPS que han dejado de reportar dentro de diferentes rangos de tiempo y exportar subconjuntos de líneas Telcel o internacionales.

## Versión

Versión actual: **1.9**

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

## Filtro de tiempo

Todas las consultas que trabajan con los rangos usan:

```sql
reportDate <= ADDDATE(UTC_TIMESTAMP(), INTERVAL -$from MINUTE)
AND reportDate > ADDDATE(UTC_TIMESTAMP(), INTERVAL -$to MINUTE)
```

Esto evita huecos y evita que una misma línea aparezca en dos rangos contiguos.

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

## Archivos del proyecto

- `phones-filtered.php`: aplicación principal.
- `README.md`: documentación de uso y reglas principales.
- `CHANGELOG.md`: historial de cambios por versión.
- `DIAGRAM.md`: diagrama del flujo funcional.
- `VERSION`: versión vigente.
- `prompt.txt`: requerimientos utilizados para las modificaciones solicitadas.

## Versionado

La versión inicial registrada del script es `1.6`.

La versión actual de esta entrega es `1.9`.
