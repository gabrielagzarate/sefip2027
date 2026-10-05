# Lista de revisión — generación de PDF (PHP 8.3)

Objetivo: validar que **todos los caminos de PDF** relevantes no generen fatal, warning repetido ni regresión visible bajo **PHP 8.3**, y documentar qué motor usa cada flujo.

**Cuándo usar esta lista:** fase **posterior** a dejar **POA** (y demás módulos prioritarios) funcionando en 8.3; no es gate obligatorio de la primera oleada. El flujo por oleadas está en `docs/MIGRACION-PHP83-PASOS.md`.

---

## 0. Antes de empezar (ambiente)

| # | Verificación | OK |
| - | ------------ | -- |
| 0.1 | Servidor o contenedor en **PHP 8.3** (misma familia que producción objetivo). | ☐ |
| 0.2 | `error_reporting` amplio en QA y **log** de PHP accesible (no silenciar deprecations solo para “pasar” la prueba). | ☐ |
| 0.3 | Datos de prueba mínimos (UR/ejercicio/folio) acordados con negocio. | ☐ |
| 0.4 | **TCPDF por Composer** (solo si van a probar export **PHPExcel → PDF**): en la raíz del proyecto, `composer install --no-dev` y existencia de `vendor/tecnickcom/tcpdf/`. En producción actual sin Composer en raíz, ese camino **no aplica** hasta que desplieguen `vendor/`. | ☐ |

---

## 1. Inventario por flujo (obligatorio: llenar al menos una fila por área)

Copiar filas. Columna **Motor**: `FPDF`, `PHPExcel+TCPDF`, `Otro` (indicar librería).

| Módulo / pantalla | URL o menú | Archivo PHP principal | Motor | Responsable | Fecha revisión |
| ----------------- | ---------- | ---------------------- | ----- | ----------- | -------------- |
| | | | | | |
| | | | | | |

**Cómo orientar el motor (rápido):**

- **`extends PDF`** o **`extends ReporteBase`** bajo `rep/doc/**` → casi siempre **FPDF** (`rep/lib/fpdf*`, `lib/reportetabla*`). Hay decenas de reportes en `rep/doc/`; no hace falta listarlos todos aquí si usan **muestreo por área** (punto 2).
- **`PHPExcel_Settings::setPdfRenderer`** + writer PDF → **PHPExcel + TCPDF** (poco frecuente en el código versionado; confirmar en runtime o configs).
- **`new TCPDF`** fuera de PHPExcel → revisar include y versión.
- Cualquier otro include (`dompdf`, `mpdf`, etc.) → **Otro**.

---

## 2. Muestreo mínimo recomendado (FPDF / `rep/doc`)

No es viable “clic en los 80+ reportes” en un solo día; sí cubrir **riesgo por área**:

| # | Área (carpeta bajo `rep/doc/`) | Mínimo de reportes a abrir | OK |
| - | ------------------------------ | -------------------------- | -- |
| 2.1 | `poa/` (incl. `mir/`, `cierre_poa/`, `avance/`) | Al menos **2** (uno vertical, uno horizontal si aplica). | ☐ |
| 2.2 | `scp/` (afectaciones, adecuaciones, reportes) | Al menos **2**. | ☐ |
| 2.3 | `scg/` (cuenta pública, consulta, vinculación, póliza) | Al menos **2**. | ☐ |
| 2.4 | `tes/` (ministración, consulta, tesorería, catálogo) | Al menos **2**. | ☐ |
| 2.5 | `mga/` | **1** si el módulo está en uso. | ☐ |
| 2.6 | Raíz `rep/doc/rep_clc.php` u otro de alto tráfico | **1** si aplica al negocio. | ☐ |

Añadir a la tabla del punto 1 cada archivo probado.

---

## 3. Por cada PDF probado (casilla)

| # | Criterio | OK |
| - | -------- | -- |
| 3.1 | El PDF **se descarga o abre** sin pantalla en blanco ni error HTTP 500. | ☐ |
| 3.2 | **Acentos y ñ** correctos en títulos y cuerpo (UTF-8 / Latin-1 según diseño). | ☐ |
| 3.3 | **Tablas**: encabezados alineados, sin solapamiento grave; celdas numéricas legibles. | ☐ |
| 3.4 | **Saltos de página**: segunda página con encabezado/pie coherente (si el reporte es multi-página). | ☐ |
| 3.5 | **Importes / formato** (miles, decimales) igual que en PHP 8.0 de referencia o aceptado por negocio. | ☐ |
| 3.6 | Log sin **Fatal**, **Parse error**, ni **Deprecated** recurrente en esa petición (anotar excepciones acordadas). | ☐ |

---

## 4. PHPExcel → PDF (TCPDF) — solo si aplica

| # | Criterio | OK |
| - | -------- | -- |
| 4.1 | Confirmado que el flujo usa writer PDF de PHPExcel (no solo botón etiquetado “Pdf” en UI que exporte otro formato). | ☐ |
| 4.2 | Con `vendor/` de Composer en el ambiente de prueba: generación **sin fatal**; PDF válido. | ☐ |
| 4.3 | Caso límite: hoja con varias filas y una celda con texto largo. | ☐ |

Si en producción **no** hay `vendor/` en la raíz, documentar: “TCPDF Composer no aplica en prod hasta despliegue X”.

---

## 5. Cierre de la revisión

| # | Entregable | OK |
| - | ---------- | -- |
| 5.1 | Tabla del punto 1 actualizada con todos los PDF muestreados. | ☐ |
| 5.2 | Lista de **defectos** (archivo, motor, síntoma, severidad, captura o ID de log). | ☐ |
| 5.3 | Decisión: **OK para 8.3** / **OK con deuda** / **bloqueado** (motivo). | ☐ |

---

## Referencias en el repo

- Plan general PHP 8.3: `docs/PLAN-MIGRACION-PHP-83.md`
- TCPDF vía Composer y bootstrap: `lib/bootstrap_tcpdf.php`, `lib/PHPExcel/Writer/PDF/tcPDF.php`, `composer.json`
- Base común de muchos reportes: `rep/doc/ReporteBase.php` (`extends PDF`)
