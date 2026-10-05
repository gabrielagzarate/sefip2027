# Revisión SCP — ajustes para PHP 8.3

Alcance: carpeta `scp/` (~60 archivos PHP), entorno objetivo **PHP 8.3**. POA se valida en paralelo; esta nota concentra **riesgos y trabajo sugerido** para SCP.

---

## Resumen ejecutivo

| Prioridad | Tema | Acción sugerida |
| --------- | ---- | ---------------- |
| Alta | **NuSOAP** embebido en `scp/afectaciones/lib/nusoap.php` | Al validar CFDI/SAT (`valida_en_sat` y similares), esperar **Deprecated**: `strftime()` (8.1+) y `utf8_decode()` (8.2+). Mediano plazo: sustituir cliente SOAP por `SoapClient` nativo o librería mantenida; corto plazo: tolerar deprecations en log o parche puntual en esa copia. |
| Alta | **`declare(strict_types=1)`** en afectaciones | Archivos: `ControladorBancoDocumentos.php`, `ControladorCompromiso.php`, `ControladorAfectacion.php`. Riesgo de **TypeError** si entran strings numéricos donde el tipado espera `int`/`float`. Smoke SCP debe incluir flujos de esos tres. |
| Media | **Carga masiva** `scp/admon/ControladorCargaMasiva.php` | Ya usa `lib/bootstrap_spreadsheet.php` + `PhpOffice\PhpSpreadsheet\IOFactory`, pero sigue usando **`PHPExcel_Style_NumberFormat::toFormattedString`** (PHPExcel legacy). Convive con dos mundos; probar fechas en plantillas y valorar migrar esas llamadas a API PhpSpreadsheet. |
| Media | **Ruta NuSOAP** | Corregido `require_once('./lib/nusoap.php')` → `dirname(__FILE__) . '/lib/nusoap.php'` en `ControladorBancoDocumentos.php` (evita fallo si el CWD del worker no es `scp/afectaciones/`). |
| Baja | **Patrones “mysql_”** en SCP | Coincidencias son **`mysql_escape_mimic()`** (función propia en `lib/`), no extensión `mysql_*` eliminada. Sin cambio obligatorio por PHP 8. |
| Baja | **Interpolación `${var}` en PHP** | No encontrada en SCP. Los `${mes}` visibles en cadenas son **plantillas para el navegador** (JS), dentro de comillas PHP que no interpolan. |

---

## Hallazgos por área

### `scp/afectaciones/` + `lib/nusoap.php`

- **`strftime('%Y-%m-%d %H:%M:%S', $sec)`** (aprox. línea 863): deprecado desde PHP 8.1; sustituto típico `(new \DateTimeImmutable('@' . $sec))->format('Y-m-d H:i:s')` (ajustar zona si aplica).
- **`utf8_decode($data)`** (aprox. línea 6939): deprecado en 8.2; sustituto típico `mb_convert_encoding($data, 'ISO-8859-1', 'UTF-8')` solo si el flujo realmente necesita ISO-8859-1.
- Clases con **constructores estilo PHP 4** (`function nusoap_base()`): pueden generar **Deprecated** en 8.x; el volumen del archivo hace inviable un parche grande sin pruebas SAT.

**Prueba funcional mínima:** validación de XML contra SAT desde Banco de documentos (camino que incluye `valida_en_sat`).

### `scp/admon/ControladorCargaMasiva.php`

- Mezcla **PHPExcel** (`PHPExcel_Style_NumberFormat`) con **PhpSpreadsheet** (`IOFactory`). Prioridad: **smoke de carga** `.xlsx` / `.xls` y revisión de log en 8.3.

### `scp/adecuaciones/`, `scp/Consultas/`, `scp/reportes/`, etc.

- Sin coincidencias de las APIs clásicas buscadas (`each`, `create_function`, `strftime` propio, `utf8_*` en PHP de negocio fuera de nusoap).
- Riesgo residual: **accesos a `$_GET`/`$_POST`/`$_SESSION`**, **null** en funciones internas, y código en **`ent/scp/`** que invoquen estos controladores (revisar log con stack trace al probar).

---

## Smoke SCP sugerido (cuando toque oleada SCP)

1. Login y menú **SCP** (afectaciones / adecuaciones / consultas según permisos).
2. **Banco de documentos**: listar, alta simple, **validación SAT** si la usan (dispara NuSOAP).
3. **Carga masiva** (admon): un archivo de prueba pequeño.
4. Una **consulta** (`scp/Consultas/`) y un **reporte** (`scp/reportes/`) de uso frecuente.
5. Revisar log: sin **Fatal**; deprecations de NuSOAP anotadas como deuda si no se sustituye aún.

---

## Referencias en repo

- Flujo general migración: `docs/MIGRACION-PHP83-PASOS.md`
- Excel centralizado: `lib/bootstrap_spreadsheet.php`

---

*Documento generado como revisión estática + ajuste de ruta NuSOAP; validación final en PHP 8.3 con datos reales.*
