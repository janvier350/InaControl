# Fase 1 - Multi-Tenant Schema

## Orden de ejecución en phpMyAdmin

Ejecutar los scripts en orden sobre el servidor de DESARROLLO (nunca sobre overcloc_INASAR):

| # | Archivo | Qué hace |
|---|---------|----------|
| 1 | `01_crear_base_de_datos.sql` | Crea la BD `overcloc_INACONTROL_DEV` |
| 2 | `02_tabla_empresa.sql` | Tabla maestra EMPRESA + 2 empresas de prueba |
| 3 | `03_tablas_administracion.sql` | Roles y usuarios con ID_EMPRESA |
| 4 | `04_tablas_operacionales.sql` | Todos los módulos con ID_EMPRESA |
| 5 | `05_logica_sesion.sql` | Configuración de módulos por empresa |

## Cómo funciona el aislamiento

```
Login → sesión guarda ID_EMPRESA + ROL
         │
         ├── ROL = SUPERADMIN → ve todas las empresas
         │
         └── Cualquier otro ROL → todas las queries
             llevan WHERE ID_EMPRESA = $_SESSION['id_empresa']
```

## Empresas de prueba
- **ID 1 → INASAR** (todos los módulos, plan PREMIUM)
- **ID 2 → Piloto Ing. Eléctrico** (soporte + inventario, plan BASICO)

## Regla de oro
Nunca hacer queries sin el filtro `AND ID_EMPRESA = ?`.
El archivo `class/conexionBD.php` nuevo tendrá una función
`getEmpresa()` que devuelve el ID desde la sesión.
