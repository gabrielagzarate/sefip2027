# Revisión SRV — ajustes para PHP 8.3

Alcance: carpeta `srv/` (~36 archivos PHP): controladores de servicio, catálogos, tickets, usuarios, integraciones (email, Telegram), `srv/Modelos/`, `srv/Vistas/`. Objetivo: anticipar riesgos antes del smoke en **PHP 8.3**.

---

## Resumen ejecutivo

| Prioridad | Tema | Acción sugerida |
| --------- | ---- | ---------------- |
| Alta | **`declare(strict_types=1)`** en `srv/Controlador.php` | Es la **base del front de servicios** (login, menú, ticket, etc.). Cualquier **TypeError** aquí impacta a todo el sistema. Smoke prioritario: login, selección de sistema, ticket si aplica. |
| Media | **`declare(strict_types=1)`** en `srv/ClasificacionAdministrativaB.php` | Probar flujos que invoquen clasificación administrativa (según menú/permisos). |
| Media | **Includes relativos al CWD** | Se reemplazó `require_once './ServicioUsuario.php'` por `dirname(__FILE__) . '/ServicioUsuario.php'` en `Controlador.php` y `ControladorUsuarios.php` para evitar fallos si el proceso no tiene CWD en `srv/`. |
| Baja | **APIs deprecadas obvias** | No aparecen en `srv/**/*.php`: sin `strftime`, `utf8_encode`/`utf8_decode`, `create_function`, `each()` PHP, `PhpSpreadsheet-1.4.0`, SOAP/NuSOAP. |
| Baja | **`mysql_escape_mimic` / `mysql_XML`** | Son **funciones del proyecto** (p. ej. `lib/mysql_escape_mimic.php`, utilidades XML), no la extensión `mysql_*` eliminada. Sin cambio obligatorio solo por PHP 8. |

---

## Archivos destacados (smoke / mantenimiento)

| Ruta | Nota |
| ---- | ---- |
| `srv/Controlador.php` | `strict_types`, muchos `switch` por `metodo`; núcleo de sesión y navegación. |
| `srv/ControladorUsuarios.php` | Gestión de usuarios (archivo grande); include `ServicioUsuario` corregido. |
| `srv/ServicioUsuario.php` | Lógica de autenticación / usuario; revisar log si hay fallos en login. |
| `srv/Modelos/modeloTicket.php` | Modelo extenso; probar flujo **ticket** si el negocio lo usa. |
| `srv/GuardaConsultaXML.php` | Exportaciones/consultas XML; validar salida y memoria en 8.3. |
| `srv/ServicioEmail.php`, `srv/Telegram.php` | Integraciones; probar solo si hay ambiente de envío permitido en QA. |
| `srv/Catalogo*.php`, `srv/Pes*.php`, etc. | Catálogos y PEP; smoke según opciones del menú que consuman el `Controlador`. |

---

## Smoke SRV sugerido (cuando toque oleada SRV / núcleo)

1. **Login** y **logout** (éxito y error controlado).
2. **Selección de sistema / módulo** (`seleccionaSistema`, `sinpres`, etc. según flujo real).
3. **Refresco de menú** (`refrescaMenu` o equivalente usado en prod).
4. **Usuarios:** listado o alta mínima si permisos (`ControladorUsuarios`).
5. **Ticket** (si aplica): creación o consulta (`ControladorTicket`, `modeloTicket`).
6. Revisar **log** en cada paso: sin **Fatal**; **TypeError** en `srv/Controlador.php` = prioridad máxima.

---

## Referencias en repo

- Flujo general migración: `docs/MIGRACION-PHP83-PASOS.md`
- Revisiones hermanas: `docs/REVISION-SCP-PHP83.md`, `docs/REVISION-TES-PHP83.md`, `docs/REVISION-SCG-PHP83.md`

---

*Revisión estática + corrección de includes; validación final en PHP 8.3 con log y usuario real.*
