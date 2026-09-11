# Diagrama funcional — phones-filtered

```mermaid
flowchart TD
    A[Petición HTTP a phones-filtered.php] --> B[Conectar a pwd5_server]
    B --> C[Leer parámetros extra, ids y export]
    C --> D[Definir rangos de tiempo]
    D --> E{¿Se solicitó export?}

    E -- No --> J[Consultar líneas sin reportar por cada rango]
    E -- Sí --> F{Tipo de exportación}

    F -- telcel --> G[Aplicar phoneNumber LIKE 844%]
    F -- intl --> H[Aplicar filtro internacional]

    H --> H1[Formato +1 + 10 dígitos]
    H --> H2[OR formato 1 + 10 dígitos]

    G --> I[Generar archivo TXT]
    H1 --> I
    H2 --> I

    J --> K[Calcular total del rango]
    K --> L[Calcular contador Telcel]
    L --> M[Calcular contador Internacional con la misma regla de exportación]
    M --> N[Consultar detalle de líneas]
    N --> O[Mostrar tabla HTML]

    I --> P[Mostrar enlace al TXT generado]
    O --> Q[Aplicar filtro visual de rangos con jQuery]
```

## Regla internacional de la versión 1.7

```sql
(
    phoneNumber REGEXP '^[+]1[0-9]{10}$'
    OR phoneNumber REGEXP '^1[0-9]{10}$'
)
```

La condición es un `OR`: basta con que el número cumpla uno de los dos formatos.
