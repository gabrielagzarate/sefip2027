# Plan de migración PHP 8.0 → 8.3 (SEFIP / legacy)

**Objetivo:** cero errores fatales, deprecations controladas y regresión mínima en flujos críticos. El repositorio ya anticipa parte del trabajo (PhpSpreadsheet 1.29.12, `lib/bootstrap_spreadsheet.php` y checklist en `README.md`).

**Estrategia acordada:** **oleada 1 = módulo POA funcional en PHP 8.3 primero** (capturas, consultas y reportes que el negocio priorice bajo `poa/` y `rep/doc/poa/`). Las revisiones transversas (p. ej. PDF en todo el sistema) se programan **después**; ver orden en `docs/MIGRACION-PHP83-PASOS.md`.

---

## Fase 0 — Baseline y gobernanza (3–5 días)

**Qué hacer**

- Levantar **ambiente de pruebas idéntico a producción** salvo PHP 8.3 (misma versión de MariaDB/MySQL, `sql_mode`, zona horaria, extensiones PHP).
- Fijar política de errores en QA: `error_reporting = E_ALL`, log a archivo; **no** ocultar deprecations en staging.
- Inventario de **flujos críticos** (matriz de negocio): login, capturas altas, consultas masivas, cargas Excel, reportes PDF, integraciones SOAP, jobs/cron si existen.
- Congelar en Git la rama de trabajo (`chore/php-8.3-migracion`) como **única línea** de cambios de compatibilidad hasta el cutover.

**Entregables**

- Lista priorizada de módulos + dueño técnico por área.
- Documento corto de **criterio de salida**: merge/corte a prod solo si se cumplen las condiciones de la Fase 6.

**Riesgo si se omite:** se corrige sin baseline y en producción aparecen fatales en rutas poco usadas.

---

## Fase 1 — Bloqueadores PHP 8+ (1–2 semanas, en paralelo con Fase 0)

**Prioridad máxima:** código que **no puede ejecutarse** en PHP 8.x.

| Área        | Hallazgo en repo                                      | Acción                                                                 |
| ----------- | ----------------------------------------------------- | ---------------------------------------------------------------------- |
| PDF legacy  | `lib/PHPExcel/Shared/PDF/tcpdf.php` (TCPDF 5.9, 2010) usa `each()` y `create_function()` | Cargar **tecnickcom/tcpdf** vía Composer + `lib/bootstrap_tcpdf.php`; el writer `lib/PHPExcel/Writer/PDF/tcPDF.php` ya prioriza ese autoload y solo cae al `tcpdf.php` embebido si no existe la clase `TCPDF` |
| Excel       | Varios `require_once` a `PhpSpreadsheet-1.4.0`        | Sustituir por `lib/bootstrap_spreadsheet.php` y validar carga/lectura/export |

**Entregables**

- Lista de pantallas/reportes que invocan el TCPDF heredado.
- Cambios que eliminen dependencia de APIs removidas en 8.0+ en ese camino.

### TCPDF — cadena de inclusión real (auditoría del repo)

Ningún archivo de aplicación (`rep/`, `scp/`, `tes/`, etc.) hace `require` directo de `lib/PHPExcel/Shared/PDF/tcpdf.php`.

La única carga del monolito legacy es indirecta:

1. `lib/PHPExcel/Writer/PDF/tcPDF.php` (writer **tcPDF** de PHPExcel).
2. Ese archivo hace `require_once` de `PHPExcel_Settings::getPdfRendererPath() . '/tcpdf.php'` **solo si** la clase `TCPDF` aún no existe.
3. Antes intenta `lib/bootstrap_tcpdf.php`, que carga el autoload de Composer (`vendor/autoload.php` en la raíz del proyecto, o `rep/lib/vendor/autoload.php`) para resolver **`tecnickcom/tcpdf`**.

**Nota:** en el código versionado no aparecen llamadas a `PHPExcel_Settings::setPdfRenderer()`; el export PHPExcel→PDF solo se ejecuta si en runtime se configura nombre y ruta del renderer (código no rastreado, include dinámico o despliegue antiguo). Aun así, el bootstrap evita depender del `tcpdf.php` de 2010 cuando Composer está instalado.

**Instalación (obligatoria en cada entorno):**

```bash
composer install
```

Raíz del proyecto: `composer.json` declara `tecnickcom/tcpdf`. La carpeta `vendor/` está en `.gitignore`; en servidor hay que ejecutar `composer install` (o empaquetar `vendor` en el artefacto de release). El paquete oficial requiere la extensión PHP **`curl`** (`ext-curl`); verificar en `php.ini` del ambiente 8.3.

### Lista corta de pruebas (PDF vía TCPDF / PHPExcel)

Priorizar **un flujo real** donde hoy exporten libro a PDF con PHPExcel; si no existe en QA, usar un script mínimo que cree un `PHPExcel`, configure `PHPExcel_Settings::setPdfRenderer(PHPExcel_Settings::PDF_RENDERER_TCPDF, …)` y guarde con writer `PDF`.

1. **Exportación PDF desde grid/libro** (misma pantalla que antes usaba TCPDF embebido): generación sin fatal, archivo abre en lector PDF.
2. **Acentos y ñ** en título o celda exportada (UTF-8).
3. **Orientación** carta horizontal vs vertical (si el módulo lo expone).
4. **Márgenes / salto de página** en hoja con varias filas (regresión visual rápida).

**Aclaración:** la mayoría de reportes bajo `rep/doc/**` usan **FPDF** (`class … extends PDF`), no este TCPDF; esas rutas no cambian con este bootstrap.

---

## Fase 2 — Unificación PhpSpreadsheet (1 semana, puede solaparse con Fase 1)

**Qué hacer**

- Migrar **todos** los `require_once ... PhpSpreadsheet-1.4.0/...` a la carga central (`lib/bootstrap_spreadsheet.php`).
- Por módulo: prueba de archivo válido e inválido, lectura de hoja/celdas, export sin warnings.

**Entregables**

- Cero referencias activas a `PhpSpreadsheet-1.4.0` en código de aplicación (vendor empaquetado solo si se conserva temporalmente por compatibilidad explícita).

---

## Fase 3 — Deprecations 8.2/8.3 y ruido en logs (2–3 semanas, incremental)

**Qué hacer**

- Atacar `utf8_encode` / `utf8_decode` en **código que el equipo mantiene** (PDF FPDF, SOAP/nusoap, parsers): migrar a `mb_convert_encoding` donde aplique, con pruebas de acentos y XML.
- Revisión de **accesos a superglobales** (`$_GET`, `$_POST`, `$_SESSION`) para evitar `Undefined array key`.
- Revisión de **null** en cadenas, fechas y operaciones numéricas en código compartido (`lib/`).

**Entregables**

- Reducción medible de líneas de log por petición en rutas críticas (antes/después en staging).

---

## Fase 4 — Base de datos y datos inconsistentes (en paralelo, 1–2 semanas)

**Qué hacer**

- Documentar `@@sql_mode` de QA vs producción; alinear.
- Auditar uso de fechas `0000-00-00`, columnas NOT NULL con valores vacíos y casts implícitos.
- Ajustar inserciones/actualizaciones o datos para no depender de comportamiento laxo.

**Entregables**

- Scripts o procedimientos de limpieza **en entorno de prueba primero**, con rollback documentado.

---

## Fase 5 — Infraestructura y extensiones PHP 8.3 (2–5 días)

**Qué hacer**

- Lista de extensiones necesarias: `mysqli` o `pdo_mysql`, `mbstring`, `xml`, `zip`, `intl` (si aplica), `soap` (si aplica extensión nativa), `gd` o `imagick` según reportes.
- Ajustar `php.ini` / pools FPM: `memory_limit`, `max_execution_time`, `upload_max_filesize` para cargas masivas.
- Validar que no queden supuestos de extensiones o configuraciones obsoletas.

---

## Fase 6 — QA formal y despliegue (1–2 semanas)

**Qué hacer**

- Smoke diario en 8.3: login + al menos un flujo por módulo asignado.
- Regresión en: autenticación, captura, consultas pesadas, Excel, PDF, SOAP.

**Criterio de salida sugerido**

- Sin **Fatal** ni **Parse error** en rutas del checklist acordado.
- Deprecations: resueltas o **listadas y aceptadas** con fecha de deuda técnica.

**Rollout:** canario (subconjunto de usuarios o nodo) antes del 100%.

---

## Cronograma sugerido (equipo pequeño a mediano)

| Semana | Enfoque |
| ------ | ------- |
| 1      | Fase 0 + Fase 1 (inventario TCPDF + primer arreglo) |
| 2      | Cierre Fase 1 + Fase 2 (spreadsheet unificado) |
| 3–4    | Fase 3 por módulos + Fase 4 (base de datos) |
| 5      | Fase 5 (infra) + endurecimiento |
| 6      | Fase 6 (QA + canario + producción) |

Ajustar según tamaño del equipo y cantidad de reportes PDF/Excel tocados.

---

## Roles

- **Líder técnico:** prioriza bloqueadores, define criterio de salida, aprueba cutover.
- **Por módulo:** dueño corrige su área (`scg/`, `tes/`, `scp/`, etc.) y ejecuta checklist funcional.
- **Infraestructura:** PHP 8.3, extensiones, logs, monitoreo post-deploy.

---

## Primera ola de archivos (arranque inmediato)

1. TCPDF: `composer install` + `lib/bootstrap_tcpdf.php` + `lib/PHPExcel/Writer/PDF/tcPDF.php` (hecho en repo); el archivo `lib/PHPExcel/Shared/PDF/tcpdf.php` queda solo como **respaldo** si no hay Composer.
2. Controladores con `require_once` a `PhpSpreadsheet-1.4.0` (póliza, carga masiva, cuenta contable, tesorería, etc.).
3. Después: `lib/tes/soap/*` y PDFs (`rep/lib/fpdf*`) según volumen de errores en log bajo PHP 8.3.

---

## Referencias en el mismo repositorio

- Pasos ordenados (1–10): `docs/MIGRACION-PHP83-PASOS.md`
- Integración central: `lib/bootstrap_spreadsheet.php`
- TCPDF (Composer): `lib/bootstrap_tcpdf.php`, `composer.json` (raíz)
- Checklist PDF: `docs/CHECKLIST-REVISION-PDF.md`
- Checklist y riesgos: sección de migración en `README.md`
