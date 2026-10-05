# Revisión SCG — ajustes para PHP 8.3

Alcance: carpeta `scg/` (~109 archivos PHP) y dependencias típicas (`ent/scg/**`, `rep/doc/scg/**`, `lib/scg/**`). Objetivo: anticipar riesgos antes del smoke del módulo en **PHP 8.3**.

---

## Resumen ejecutivo

| Prioridad | Tema | Acción sugerida |
| --------- | ---- | ---------------- |
| Alta | **`declare(strict_types=1)`** en varios controladores | Riesgo de **TypeError** si llegan strings numéricos donde se espera `int`/`float` o viceversa. Smoke SCG debe cubrir al menos **un flujo por archivo** listado abajo (catalogo, cuenta contable, consulta). |
| Media | **Carga Excel** (`ControladorCargaCuenta`, `ControladorCargaPoliza`) | Ya usan `lib/bootstrap_spreadsheet.php` y `\PhpOffice\PhpSpreadsheet\IOFactory::load`. Probar **plantillas oficiales** (hojas `CUENTAS` / `POLIZA`) y límites de columnas en 8.3. |
| Baja | **APIs deprecadas obvias** | No aparecen en `scg/**/*.php`: sin `strftime`, `utf8_encode`/`utf8_decode`, `create_function`, `each()` PHP, `FILTER_SANITIZE_STRING`, `PhpSpreadsheet-1.4.0`, ni SOAP/NuSOAP directo. |
| Baja | **`mysql_escape_mimic`** | Es función **propia** del proyecto (`lib/mysql_escape_mimic.php`), no la extensión `mysql_*` eliminada. Sin cambio obligatorio por PHP 8; seguir buenas prácticas de consultas parametrizadas donde ya existan. |

---

## Controladores con `strict_types=1` (prioridad smoke)

| Ruta | Área |
| ---- | ---- |
| `scg/consulta/ControladorLibroDiario.php` | Consulta |
| `scg/consulta/ControladorLibroMayor.php` | Consulta |
| `scg/catalogo/ControladorCOG.php` | Catálogo |
| `scg/catalogo/ControladorMomentoContable.php` | Catálogo |
| `scg/catalogo/ControladorAgrupacionPoliza.php` | Catálogo |
| `scg/catalogo/ControladorAgrupacionDocumento.php` | Catálogo |
| `scg/catalogo/ControladorMesContable.php` | Catálogo |
| `scg/catalogo/ControladorAgrupacionCuenta.php` | Catálogo |
| `scg/cuentaContable/ControladorCONAC.php` | Cuenta contable |
| `scg/cuentaContable/ControladorCargaCuenta.php` | Cuenta contable + Excel |
| `scg/cuentaContable/ControladorPlanCuenta.php` | Cuenta contable |
| `scg/cuentaContable/ControladorMaestro.php` | Cuenta contable |

---

## Hallazgos por área (orientación smoke)

| Carpeta | Rol típico | Nota 8.3 |
| ------- | ---------- | -------- |
| `scg/catalogo/` | COG, mes contable, agrupaciones, retención, tipo documento, etc. | Varios `strict_types`; probar altas/edición y listados. |
| `scg/cuentaContable/` | CONAC, plan de cuenta, maestro, **carga cuenta** | Carga Excel + `strict_types` en varios archivos. |
| `scg/poliza/` | Carga póliza, manual, muestra | **CargaPoliza** con PhpSpreadsheet; **PolizaManual** usa `mysql_escape_mimic` en flujos complejos. |
| `scg/consulta/` | Balanza, libro diario, libro mayor | **Libro diario** y **libro mayor** con `strict_types`. |
| `scg/conciliacion/` | Contable, modificado, devengado, pagado, transferido, etc. | Muchos controladores por año/estado; riesgo en **null** y arrays de sesión. |
| `scg/vinculacion/` y `scg/vinculacionAnteriores/` | CLC, pagado, ministrado histórico | Alto volumen de lógica; smoke por flujos que negocio priorice. |
| `scg/fondos/` | Ministrado por ejercicio | Probar al menos un año representativo. |
| `scg/cuentaPublica/` | Estados, variación hacienda, flujo, actividades | PDF vía `rep/doc/scg/` si aplica; ver checklist global más adelante. |
| `scg/banco/`, `scg/reporte/`, `scg/ldf/`, etc. | Ingreso/egreso, reportes, LDF | Incluir en smoke según uso institucional. |

---

## Smoke SCG sugerido (cuando toque oleada SCG)

1. Login y menú **SCG** con usuario con permisos reales.
2. **Catálogo:** un alta o edición en COG o mes contable (ruta con `strict_types`).
3. **Cuenta contable:** consulta plan de cuentas + **carga de cuentas** con Excel de prueba.
4. **Póliza:** **carga de póliza** con plantilla válida (hoja `POLIZA`).
5. **Consulta:** libro mayor o libro diario (export/consulta que usen).
6. **Conciliación o vinculación:** un flujo de alto uso (una pantalla).
7. Revisar log: sin **Fatal**; **TypeError** y **Deprecated** anotados con stack a `scg/` o `ent/scg/`.

---

## Referencias en repo

- Excel centralizado: `lib/bootstrap_spreadsheet.php`
- Flujo general migración: `docs/MIGRACION-PHP83-PASOS.md`
- Revisiones hermanas: `docs/REVISION-SCP-PHP83.md`, `docs/REVISION-TES-PHP83.md`

---

*Revisión estática; validación final en PHP 8.3 con datos reales y log.*
