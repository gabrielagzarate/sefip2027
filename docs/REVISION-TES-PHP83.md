# Revisión TES — ajustes para PHP 8.3

Alcance: carpeta `tes/` (~36 archivos PHP) y dependencias directas (`ent/tes/**`, `lib/tes/**` cuando los controladores los incluyen). Objetivo: anticipar riesgos antes del smoke del módulo en **PHP 8.3**.

---

## Resumen ejecutivo

| Prioridad | Tema | Acción sugerida |
| --------- | ---- | ---------------- |
| Media | **`ControladorMarcadoMinGeneral.php`** | Carga `lib/PHPExcel/IOFactory.php` y `lib/bootstrap_spreadsheet.php` + `use PhpOffice\PhpSpreadsheet\IOFactory`. **Smoke obligatorio:** marcado / carga por Excel (`.xlsx`). Confirmar que todas las llamadas `IOFactory::` resuelven a PhpSpreadsheet y que no queda código que espere `PHPExcel_IOFactory` con el mismo alias corto. |
| Media | **`declare(strict_types=1)`** | Solo en `tes/extracto/ControladorSubeExtracto.php`. Probar **subida de extracto** y conciliación con datos que mezclen strings numéricos; vigilar **TypeError** en 8.3. |
| Baja | **APIs deprecadas obvias** | No aparecen en `tes/**/*.php`: sin `strftime`, `utf8_encode`/`utf8_decode`, `create_function`, `each()` PHP, `FILTER_SANITIZE_STRING`, ni rutas a `PhpSpreadsheet-1.4.0`. |
| Baja | **SOAP / NuSOAP** | No hay referencias directas en `tes/`; si algún `ent/tes` o `lib/tes` carga SOAP al ejecutar un flujo, el riesgo sale en **runtime** (revisar log del smoke). |
| Baja | **`${...}` en cadenas** | Coincidencias son **plantillas para HTML/JS** (p. ej. `ControladorVinBeneficiarioMunicipio.php`), no interpolación PHP deprecada en 8.2. |

---

## Hallazgos por carpeta (orientación smoke)

| Carpeta | Rol típico | Nota 8.3 |
| ------- | ---------- | -------- |
| `tes/ministracion/` | Marcado, banca, tesorería manual, segundo plano | Incluye el controlador con **PhpSpreadsheet** + PHPExcel legacy; prioridad en smoke. |
| `tes/extracto/` | Sube extracto, conciliación, config bancos | `strict_types` en subida de extracto. |
| `tes/tesoreria/` | Propuestas, programación, cadenas | Probar flujos con lotes y fechas. |
| `tes/consulta/` | Consultas tesorería, extracto, CLC pendientes, ministrado | Riesgo habitual: **índices de sesión** y nulls en listados. |
| `tes/lotes/` | Planeación, gestión | Validar permisos y SPs asociados. |
| `tes/catalogo/` | Catálogos, clave financiamiento, beneficiario municipio | HTML embebido con `${...}` para front; sin cambio por deprecación PHP. |
| `tes/cheque/` | Impresión | Probar generación PDF/cheque si aplica. |
| `tes/models/` | `clcs20xx.php` | Archivos de modelo/año; revisar si solo datos o también lógica; smoke según uso. |

---

## Smoke TES sugerido (cuando toque oleada TES)

1. Login y menú **TES** (ministración / tesorería / consulta según permisos).
2. **`ControladorMarcadoMinGeneral`**: flujo de carga o marcado con archivo **Excel** pequeño.
3. **`ControladorSubeExtracto`**: subir extracto de prueba (o validar rechazo controlado).
4. Una **consulta** frecuente (`tes/consulta/`) y un flujo de **lotes** o **tesorería** usado en operación.
5. Revisar log: sin **Fatal**; anotar **Deprecated** y **TypeError** con stack a `tes/` o `ent/tes/`.

---

## Referencias en repo

- Excel centralizado: `lib/bootstrap_spreadsheet.php`
- Flujo general migración: `docs/MIGRACION-PHP83-PASOS.md`
- Revisiones hermanas: `docs/REVISION-SCP-PHP83.md`

---

*Revisión estática; validación final en PHP 8.3 con datos reales y log.*
