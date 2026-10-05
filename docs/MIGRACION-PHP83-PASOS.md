# Migración a PHP 8.3 — flujo de trabajo (prioridad POA)

Guía operativa para el equipo. Detalle amplio: `docs/PLAN-MIGRACION-PHP-83.md`.

## Estrategia en oleadas

| Oleada | Alcance | Cuándo |
| ------ | ------- | ------ |
| **1 — POA** | Dejar **funcional** el módulo POA en PHP 8.3 (capturas, consultas, reportes que use POA, PDFs bajo `rep/doc/poa/` solo si bloquean un flujo). | **Ahora** |
| **2+** | Resto de módulos (SCG, TES, SCP, MGA, …). | Después de cierre de oleada 1 |
| **Posterior** | Revisiones transversas amplias (p. ej. checklist completo de PDF en todo `rep/doc/`). | Cuando el equipo programe esa fase; ver `docs/CHECKLIST-REVISION-PDF.md` |

Regla: **no bloquear** la oleada POA por auditorías globales de PDF; solo atender PDF si un caso POA concreto falla en 8.3.

---

## Paso 1 — Base técnica ya en repo (Excel / PhpSpreadsheet)

**Objetivo:** carga unificada de hojas de cálculo.

**Acción:** `lib/bootstrap_spreadsheet.php` (sin rutas a `PhpSpreadsheet-1.4.0` en los puntos ya migrados).

**Validación POA:** cualquier pantalla POA que importe `.xlsx` / lea Excel debe abrir sin fatal (probar al menos una carga masiva o import si existe en POA).

---

## Paso 2 — Ambiente QA PHP 8.3 enfocado en POA

**Objetivo:** reproducir errores solo del alcance POA.

**Acción:**

- Mismo código que staging/prod objetivo, **PHP 8.3**, `error_reporting = E_ALL`, log a archivo.
- Dueño POA ejecuta smoke sobre las áreas del código bajo `poa/` (controladores por carpeta):

  - `poa/mir/`, `poa/presupuestacion/`, `poa/seguimiento/`, `poa/avance/`, `poa/programacion/`, `poa/planeacion/`, `poa/cierre_poa/`, `poa/reportes/`

**Validación:** login → menú POA → **al menos un flujo por subcarpeta** usada en negocio (captura, consulta, reporte); anotar fatals y warnings en tabla de incidencias.

---

## Paso 3 — Correcciones de compatibilidad solo en rutas POA

**Objetivo:** cero fatals en flujos POA priorizados.

**Acción:**

- Corregir primero archivos bajo `poa/**` y lo que POA invoque directo (`rep/doc/poa/**`, `ent/poa/**` si aplica, includes desde `lib/` solo cuando el stack trace salga de una petición POA).
- Ignorar de momento fallos en módulos que no sean POA salvo que bloqueen el login o sesión global.

**Validación:** repetir el smoke del paso 2; log sin fatal en esas rutas.

---

## Paso 4 — TCPDF (solo si POA usa export PHPExcel → PDF)

**Objetivo:** no depender del `tcpdf.php` legacy en ese flujo.

**Acción:** si en pruebas POA aparece stack hacia `PHPExcel_Writer_PDF` / `TCPDF`, en QA ejecutar `composer install --no-dev` en la **raíz** y validar de nuevo.

**Si POA no usa ese export:** este paso queda **en espera** hasta otra oleada.

---

## Paso 5 — Base de datos (solo lo que afecte pruebas POA)

**Objetivo:** mismos datos y `sql_mode` que en prod futuro, sin sorpresas en fechas y NOT NULL en flujos POA.

**Acción:** alinear `@@sql_mode` en QA; corregir inserciones/lecturas que fallen **en caminos POA** probados.

---

## Paso 6 — Extensiones e `php.ini` (una vez por ambiente)

**Objetivo:** paridad mínima para PHP 8.3.

**Acción:** `mbstring`, `xml`, `zip`, `mysqli` o `pdo_mysql`, y las que exijan las librerías que POA use (`curl` si activan TCPDF por Composer).

**Validación:** `php -m` o `phpinfo()` controlado en QA.

---

## Paso 7 — Cierre oleada 1 (POA)

**Entregables:**

- Lista de pantallas POA probadas + resultado (OK / bug + enlace a issue).
- Decisión explícita: **“POA aceptado en PHP 8.3 para QA”** o lista de bloqueadores.

**Recién entonces** se amplía el mismo esquema (pasos 2–6 adaptados) a **SCG, TES, SCP**, etc.

---

## Paso 8 — Oleadas siguientes (otros módulos)

Repetir el patrón: **ambiente 8.3 → smoke por módulo → correcciones acotadas → cierre**. El orden entre módulos lo define negocio/riesgo.

**SCP (adelanto de revisión):** ver `docs/REVISION-SCP-PHP83.md` (NuSOAP, `strict_types` en afectaciones, carga masiva + PHPExcel/PhpSpreadsheet).

**TES (adelanto de revisión):** ver `docs/REVISION-TES-PHP83.md` (marcado ministración + Excel, `strict_types` en extracto, smoke por carpeta).

**SCG (adelanto de revisión):** ver `docs/REVISION-SCG-PHP83.md` (mucho `strict_types`, carga cuenta/póliza con PhpSpreadsheet, smoke por área).

**SRV (adelanto de revisión):** ver `docs/REVISION-SRV-PHP83.md` (`Controlador.php` con `strict_types`, usuarios, ticket; includes `ServicioUsuario` estabilizados).

**MGA (adelanto de revisión):** ver `docs/REVISION-MGA-PHP83.md` (~17 controladores; sin `strict_types` ni Excel en árbol; smoke por complementos/catálogo/scp).

---

## Paso 9 — Revisiones transversas (más adelante)

**Objetivo:** calidad global de PDF y cobertura amplia.

**Acción:** `docs/CHECKLIST-REVISION-PDF.md` (muestreo por `rep/doc/` completo), sin ser gate de la oleada POA.

---

## Paso 10 — Despliegue

Canario o ventana; **Composer + `vendor/` en raíz** solo si algún módulo ya priorizado lo requiere (p. ej. TCPDF); rollback documentado.

---

Resumen: **pasos 1 y 6 apoyan a todos; pasos 2–7 cierran POA primero; paso 9 es deliberadamente posterior.**
