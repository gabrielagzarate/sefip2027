# Revisión MGA — ajustes para PHP 8.3

Alcance: carpeta `mga/` (~17 archivos PHP): complementos, catálogos, carga de trabajo, integración `mga/scp/`, mensajes y tickets de administración. Objetivo: anticipar riesgos antes del smoke en **PHP 8.3**.

---

## Resumen ejecutivo

| Prioridad | Tema | Acción sugerida |
| --------- | ---- | ---------------- |
| Baja | **APIs deprecadas obvias** | En `mga/**/*.php` no aparecen: `strftime`, `utf8_encode`/`utf8_decode`, `create_function`, `each()` PHP, `FILTER_SANITIZE_STRING`, `PhpSpreadsheet-1.4.0`, SOAP/NuSOAP, ni `require_once './...'` frágil al CWD. |
| Baja | **`declare(strict_types=1)`** | Ningún archivo bajo `mga/` lo declara; el riesgo principal en MGA será **runtime** (`Undefined array key`, `null`, includes de `lib/` y `ent/` compartidos). |
| Baja | **Excel / PhpSpreadsheet** | No hay referencias directas a PHPExcel/PhpSpreadsheet en `mga/`; si algún flujo exporta vía `rep/doc/mga/` o genéricos, validarlo en smoke. |

---

## Inventario por carpeta (orientación smoke)

| Carpeta / archivo | Rol típico |
| ----------------- | ---------- |
| `mga/complementos/` | Techos, actividades, ajuste, previsiones, gestor subprograma, envío de passwords |
| `mga/catalogos/` | Catálogos 2, documentos normativos, asignación de partida |
| `mga/cargaTrabajo/` | Carga de trabajo |
| `mga/scp/` | Excepciones recurso federal, permisos modificaciones, bloqueo CLC (cruce con lógica SCP) |
| Raíz `mga/` | `ControladorMensajesSistema.php`, `ControladorVinculacionMir.php`, `ControladorTicketAdmon.php` |

---

## Smoke MGA sugerido (cuando toque oleada MGA)

1. Login con usuario **MGA** (o permisos equivalentes).
2. **Complementos:** un flujo de techos o actividades (según uso institucional).
3. **Catálogos:** apertura de `ControladorCatalogos2` o documentos normativos.
4. **`mga/scp/`:** permisos modificaciones o bloqueo CLC (solo si el negocio lo usa en QA).
5. **Ticket administración** o **mensajes sistema** si aplica.
6. Revisar **log**: sin **Fatal**; warnings en código compartido (`lib/`, `ent/`) con stack que pase por `mga/` → corregir en oleada correspondiente.

---

## Dependencias fuera de `mga/`

Los controladores suelen incluir `lib/`, `ent/` y posiblemente reportes bajo `rep/doc/mga/`. Los problemas de PHP 8.3 pueden **originarse fuera** de `mga/` aunque la pantalla sea MGA; usar el stack trace del log.

---

## Referencias en repo

- Flujo general migración: `docs/MIGRACION-PHP83-PASOS.md`
- Revisiones hermanas: `docs/REVISION-SCP-PHP83.md`, `docs/REVISION-TES-PHP83.md`, `docs/REVISION-SCG-PHP83.md`, `docs/REVISION-SRV-PHP83.md`

---

*Revisión estática; sin cambios de código en esta ronda (no se detectaron includes relativos ni APIs obsoletas en `mga/`). Validación final en PHP 8.3 con log.*
