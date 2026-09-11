# phones-filtered

Aplicación PHP para consultar líneas telefónicas asociadas a dispositivos GPS que han dejado de reportar dentro de diferentes rangos de tiempo y exportar subconjuntos de líneas Telcel o internacionales.

## Versión

Versión actual: **1.7**

El archivo `VERSION` contiene la versión vigente del proyecto.

## Compatibilidad

- PHP 5.4.16
- MySQL 5.7
- Bootstrap 5.3.2
- jQuery 3.7.1

## Funcionalidad principal

La página consulta `pwd5_server.gps_info` y muestra dispositivos válidos agrupados por rangos de minutos sin reportar:

- 10 a 60 minutos
- 61 a 120 minutos
- 121 a 180 minutos
- 181 a 240 minutos
- 241 a 300 minutos
- 301 a 360 minutos
- 361 a 1440 minutos

Permite:

- Mostrar todos los rangos o seleccionar rangos específicos desde la interfaz.
- Mostrar columnas adicionales con `?extra=true`.
- Filtrar IDs concretos con `?ids=ID1,ID2,...`.
- Exportar líneas Telcel con `?export=telcel`.
- Exportar líneas internacionales con `?export=intl`.

## Filtro Telcel

Una línea se considera Telcel para la exportación cuando:

```sql
phoneNumber LIKE '844%'
```

## Filtro Internacional

Desde la versión 1.7 una línea se considera internacional solamente cuando cumple una de estas dos condiciones:

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

Cada modificación funcional incrementa un decimal:

```text
1.6 -> 1.7 -> 1.8 -> 1.9 -> 2.0
```
