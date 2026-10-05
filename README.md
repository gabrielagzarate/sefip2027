# **Ajustes para el sistema 2027**

## Índice

- [Npm para gestionar paquetes](#npm-para-gestionar-paquetes)
- [Estándar de código para PHP](#estándar-de-código-para-php)
- [Migración de PHP 8.0 a PHP 8.3](#migración-de-php-80-a-php-83)
- [Configuración inicial del proyecto](#️-configuración-inicial-del-proyecto)
- [Constantes credenciales (constantesCredenciales)](#constantes-credenciales-constantescredenciales)
- [Inicio seguro de sesión PHP (sessionSafeStart)](#inicio-seguro-de-sesión-php-sessionsafestart)
- [Manejo de errores PHP en producción (configurarErroresPhp)](#manejo-de-errores-php-en-producción-configurarErroresphp)
- [Lineamiento de rutas de imágenes (PDF y Excel)](#lineamiento-de-rutas-de-imágenes-pdf-y-excel)
- [Envío de correo con PHPMailer](#envío-de-correo-con-phpmailer)
- [Referencias legacy](#referencias-legacy)

## Npm para gestionar paquetes

* Versión Node requerida: 20.12.2 
* Versión de NPM requerida: 10.5.2

### Ejecutar en terminal
* npm i 
* npm run build


## Estándar de código para PHP

Para estilo de código, usar **PSR-12**.  

### Configuración definida en el proyecto

- Archivo: `.php-cs-fixer.dist.php`
- Alcance: módulos PHP del sistema (`srv`, `lib`, `scg`, `scp`, `tes`, `ent`, `poa`, `rep`).
- Exclusiones: código de terceros legacy (`vendor`, `lib/PHPExcel`, `rep/lib/PhpSpreadsheet-1.29.12` y sus vendors).

### Reglas de adopción para el equipo

- Aplicar formato PSR-12 en **código nuevo**.
- Aplicar formato en **archivos tocados** durante una tarea.
- Evitar reformatear todo el repositorio en un solo PR.

### Instalación (paso a paso)

#### 1) Requisitos mínimos

- Tener PHP instalado y disponible en terminal.
- Trabajar desde la raíz del proyecto:
  - ejemplo: `c:\laragon\www\sefip2027`

Validar PHP:

```powershell
php -v
```

#### 2) Instalación de `php-cs-fixer` (global con Composer). 

##### Instalación global con Composer

1. Verificar Composer:

```powershell
composer --version
```

2. Instalar herramienta global:

```powershell
composer global require friendsofphp/php-cs-fixer
```

3. Verificar que ejecuta:

```powershell
php-cs-fixer --version
```

Si no reconoce `php-cs-fixer`, reiniciar terminal o agregar al `PATH` la carpeta global de Composer.


#### 3) Verificar configuración del proyecto

Confirmar que existe el archivo:

```powershell
Get-Item .\.php-cs-fixer.dist.php
```

#### 4) Revisar un archivo específico (sin modificar)

Ejemplo con `ControladorPlanUR.php`:

Con instalación global:

```powershell
php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff "ruta\al\ControladorPlanUR.php"
```

#### 5) Aplicar formato al archivo específico

Con instalación global:

```powershell
php-cs-fixer fix --config=.php-cs-fixer.dist.php "ruta\al\ControladorPlanUR.php"
```

#### 6) Validación posterior obligatoria

- Probar el flujo funcional del controlador en sistema.
- Confirmar que no se modificó lógica, solo formato.
- Que el flujo funcional siga igual (pantalla, AJAX, persistencia).
- Que no se introduzcan errores en archivos mixtos legacy.
- Revisar diff antes de commit.

### Ejemplos de cambios típicos de formato

#### Ejemplo A: espacios y llaves

Antes:

```php
if($activo){
    $x=1;
}
```

Después:

```php
if ($activo) {
    $x = 1;
}
```

#### Ejemplo B: arrays cortos y comillas simples

Antes:

```php
$data = array("estado" => "ok", "mensaje" => "Proceso");
```

Después:

```php
$data = ['estado' => 'ok', 'mensaje' => 'Proceso'];
```

## Migración de PHP 8.0 a PHP 8.3

Estas recomendaciones están orientadas al equipo de desarrollo.

### 1. Entorno de desarrollo

- Usar PHP 8.3.x en local para desarrollo y pruebas.
- Verificar versión activa con `php -v`.

### 2. Compatibilidad de código (enfoque programadores)

- Sustituir funciones obsoletas/deprecadas detectadas en PHP 8.3.
- Revisar validaciones de tipo, `null`, y conversiones implícitas en comparaciones.
- Asegurar consultas con PDO parametrizado (sin concatenación de variables).
- Verificar compatibilidad de utilerías internas usadas en módulos legacy.

### 3. Checklist técnico de validación

- Ejecutar pruebas funcionales de autenticación, captura, consulta y reportes.
- Revisar logs y corregir cualquier `Deprecated`, `Warning` o `Fatal error`.
- Validar que no existan regresiones en funciones compartidas (`lib/`).
- Confirmar que las consultas SQL y flujos AJAX sigan respondiendo igual.

### 4. Alcance de cada programador (por controlador/módulo)

- Sustituir imports, `require_once` y llamadas legacy por el wrapper o librería definida.
- Ajustar manejo de tipos, validaciones y valores `null` donde aplique.
- Ejecutar pruebas funcionales del módulo a su cargo.
- Reportar incidencias no funcionales de infraestructura/librería al equipo base.


### 5. Actualización aplicada de PhpSpreadsheet

Se aplicó una actualización de la librería para reducir impacto en módulos.

#### Cambio aplicado

- **Antes:** base legacy `PhpSpreadsheet 1.4.0`.
- **Ahora:** `PhpSpreadsheet 1.29.12` en `rep/lib/PhpSpreadsheet-1.29.12`.
- **Objetivo:** compatibilidad con PHP 8.3 sin salto agresivo a versiones mayores.

#### Implicaciones técnicas

- Se actualizaron dependencias internas relevantes (`symfony/*`, `markbaker/*`, `psr/*`, `zipstream`, etc.).
- Puede haber diferencias menores en lectura/escritura de Excel (tipos de datos, validaciones, formatos).
- No se ajustaron automáticamente todos los controladores del sistema.

#### Integración central disponible

- Existe `lib/bootstrap_spreadsheet.php` para carga centralizada del autoload.
- Actualmente prioriza:
  1. `rep/lib/PhpSpreadsheet-1.29.12/vendor/autoload.php`
  2. `rep/lib/vendor/autoload.php` (fallback)

#### Validación obligatoria cuando se atienda cada archivo pendiente

1. Carga de archivo válida e inválida.
2. Lectura de hoja y celdas (`getSheetByName`, `getCell`).
3. Exportación/generación de archivo sin errores.
4. Sin `Deprecated`, `Warning` ni `Fatal` en PHP 8.3.

### 6. Riesgos técnicos a validar en revisión (programadores)

Checklist adicional para revisión de código durante la migración a PHP 8.3.

#### 6.1 Bloqueadores PHP 8.3 (prioridad alta)

- Revisar uso de funciones removidas en PHP 8 (`each`, `create_function`) en librerías legacy.
- Validar reportes PDF que dependen de `lib/PHPExcel/Shared/PDF/tcpdf.php`.
- Si un flujo usa esos componentes, registrar incidente y proponer reemplazo inmediato.

#### 6.2 Funciones de encoding deprecadas

- Identificar y reducir uso de `utf8_encode`/`utf8_decode` en código de negocio.
- Para código nuevo, usar `mb_convert_encoding`.
- Probar generación de archivos y servicios donde se procesen acentos/caracteres especiales.

#### 6.3 SQL mode y manejo de fechas

- Confirmar `@@sql_mode` del ambiente de pruebas antes de validar módulos.
- Evitar `0000-00-00` y usar `NULL` cuando no exista fecha real.
- Validar inserciones/actualizaciones con fechas.

#### 6.4 Rutas e includes estables

- Resolver rutas con `__DIR__` para evitar dependencia del directorio de ejecución.
- Evitar rutas relativas frágiles (`./`, `../../`) cuando haya alternativa estable.
- Verificar mayúsculas/minúsculas en rutas pensando en despliegues Linux.

#### 6.5 Superglobales y notices

- Proteger accesos a `$_SESSION`, `$_POST`, `$_GET`, `$_SERVER` usando `isset` o `??`.
- Evitar que la revisión termine con `Undefined array key` en logs.

#### 6.6 Pruebas mínimas por módulo (obligatorias)

- Criterio de aceptación técnico: sin `Deprecated`, `Warning` ni `Fatal` en PHP 8.3.

## ⚙️ Configuración inicial del proyecto

Antes de ejecutar el sistema, es necesario configurar los siguientes archivos y ubicarlos correctamente dentro del proyecto:

### 1. Configuración de base de datos

- **Archivo:** ruta definida por `RUTA_ACCESOS_XML` en `lib/constantesCredenciales.php` (por defecto `lib/conf/accesos.xml`)  
- **Descripción:**  
  Contiene las credenciales de conexión a la base de datos (host, usuario, contraseña y nombre de la base de datos).  
- **Acción requerida:**  
  Colocar el XML donde corresponda en cada ambiente y apuntar `RUTA_ACCESOS_XML` a esa ruta. No modificar `AccesoDatos.php` por servidor.

### 2. Constantes credenciales

- **Plantilla:** `lib/constantesCredenciales.php.example`  
- **Archivo local (no versionado):** `lib/constantesCredenciales.php`  
- **Descripción:**  
  Archivo general para valores sensibles o específicos de cada instalación (correo, PDFs y futuras integraciones).  
- **Acción requerida:**  
  Copiar la plantilla y completar los valores en cada entorno o rama de desarrollo:

```powershell
Copy-Item lib\constantesCredenciales.php.example lib\constantesCredenciales.php
```

Ver detalle y ejemplos de uso en [Constantes credenciales](#constantes-credenciales-constantescredenciales).

### 3. Archivo de constantes de Telegram

- **Archivo:** `lib/constantesTelegram.php`  
- **Descripción:**  
  Contiene la configuración necesaria para la integración con Telegram.  
- **Acción requerida:**  
  Asegurarse de que el archivo exista y contenga los valores correctos para el entorno.

### 4. Archivo de palabras para CAPTCHA

- **Archivo:** `lib/captcha/words.txt`  
- **Descripción:**  
  Archivo que contiene las palabras utilizadas para la generación del CAPTCHA dentro del sistema.  
- **Acción requerida:**  
  Verificar que el archivo exista y tenga contenido válido.

### 🔐 Consideraciones de seguridad

- No incluir archivos con credenciales sensibles en el repositorio (`lib/constantesCredenciales.php`, `lib/conf/accesos.xml`, `lib/constantesTelegram.php`).

## Constantes credenciales (`constantesCredenciales`)

Archivo único para definir valores que **no deben versionarse**. Hoy incluye credenciales SMTP y contraseñas de PDF; al necesitar otra configuración sensible, se agrega ahí con su `define()` y se documenta en la plantilla `.example`.

### Archivos involucrados

| Archivo | En repositorio | Función |
| ------- | -------------- | ------- |
| `lib/constantesCredenciales.php.example` | Sí | Plantilla con nombres de constantes y valores `CAMBIAR` |
| `lib/constantesCredenciales.php` | No (`.gitignore`) | Valores reales |
| `lib/constantes.php` | Sí | Carga `constantesCredenciales.php` al inicio; falla si falta |

### Configuración por programador

1. Tras clonar o actualizar con `development`, copiar la plantilla:

```powershell
Copy-Item lib\constantesCredenciales.php.example lib\constantesCredenciales.php
```

2. Editar `lib/constantesCredenciales.php` y sustituir cada `CAMBIAR` por el valor del ambiente.
3. No commitear `lib/constantesCredenciales.php`.

### Constantes disponibles (actual)

| Constante | Uso |
| --------- | --- |
| `RUTA_ACCESOS_XML` | Ruta del XML de conexiones BD (`AccesoDatos::gtAccesos`). Distinta por ambiente (dev / QAS / UAT / prod) |
| `SMTP_HOST`, `SMTP_PORT` | Servidor SMTP compartido |
| `SMTP_NOTIFICACIONES_USER`, `SMTP_NOTIFICACIONES_PASS` | Cuenta de notificaciones (SCP, tesorería, `ServicioEmail`) |
| `SMTP_SISTEMA_USER`, `SMTP_SISTEMA_PASS` | Cuenta de sistema (tickets de usuarios) |
| `PDF_PASSWORD_OWNER_POA` | Contraseña de propietario en PDFs del módulo **POA** |
| `PDF_PASSWORD_OWNER_SCP` | Contraseña de propietario en PDFs del módulo **SCP** |
| `PDF_PASSWORD_OWNER_MGA` | Contraseña de propietario en PDFs del módulo **MGA** |
| `PDF_PASSWORD_OWNER_TES` | Contraseña de propietario en PDFs del módulo **Tesorería** |
| `PDF_PASSWORD_OWNER_SCG` | Contraseña de propietario en PDFs del módulo **SCG** |
| `PDF_PASSWORD_OWNER_MYSQL_REPORT` | Contraseña de propietario en `rep/lib/mysql_report.php` (reporteador genérico SQL) |

Nuevas constantes sensibles: agregarlas en `constantesCredenciales.php.example` (con `CAMBIAR`) y en el archivo local de cada programador.

### Migración de reportes PDF (responsabilidad de cada programador)

Al migrar un controlador o reporte que aún tenga la contraseña hardcodeada en `SetProtection`, **sustituirla por la constante del módulo al que pertenece el archivo**, no por una genérica.

| Ubicación del reporte | Constante a usar |
| --------------------- | ---------------- |
| `rep/doc/scp/` | `PDF_PASSWORD_OWNER_SCP` |
| `rep/doc/scg/` | `PDF_PASSWORD_OWNER_SCG` |
| `rep/doc/tes/` | `PDF_PASSWORD_OWNER_TES` |
| `rep/doc/poa/` | `PDF_PASSWORD_OWNER_POA` |
| `rep/doc/mga/` | `PDF_PASSWORD_OWNER_MGA` |
| `rep/lib/mysql_report.php` | `PDF_PASSWORD_OWNER_MYSQL_REPORT` |
| `rep/doc/srv/FrmAcceso.php` | `PDF_PASSWORD_OWNER_POA` |

**Pasos al migrar un archivo:**

1. Confirmar que el reporte ya incluye `require_once` de `lib/constantes.php` (directamente o vía `AccesoDatos`).
2. Localizar la llamada a `SetProtection` y reemplazar el tercer argumento (contraseña literal) por la constante de la tabla anterior.
3. No mezclar constantes entre módulos: un PDF en `rep/doc/scg/` siempre usa `PDF_PASSWORD_OWNER_SCG`, aunque antes compartiera otra contraseña.
4. Probar la generación del PDF en local con `lib/constantesCredenciales.php` configurado.

**Antes (no subir a main):**

```php
$pdf->SetProtection(['print', 'annot-forms'], '', 'nUtq8Zsy42jkLvU');
```

**Después (ejemplo para un reporte en SCP):**

```php
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP);
```

### Ejemplo: correo (PHPMailer)

No hardcodear host, usuario ni contraseña. Usar las constantes cargadas vía `lib/constantes.php`:

```php
require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/PHPMailer.php';
require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->CharSet    = PHPMailer::CHARSET_UTF8;
$mail->Host       = SMTP_HOST;
$mail->Port       = SMTP_PORT;
$mail->SMTPAuth   = true;
$mail->Username   = SMTP_NOTIFICACIONES_USER;
$mail->Password   = SMTP_NOTIFICACIONES_PASS;
$mail->setFrom(SMTP_NOTIFICACIONES_USER, 'Sefip Notificaciones');
$mail->isHTML(true);
// destinatarios, Subject, Body...
$mail->send();
```

### Código nuevo o reportes pendientes de migrar

Si al tocar un archivo aún aparecen credenciales literales (`correo.oaxaca.gob.mx`, contraseñas en `SetProtection`, etc.), reemplazarlas por la constante del módulo o de correo que corresponda (ver tablas anteriores) y solicitar al equipo la revisión.

## Uso de constantes para tareas y nombre de base de datos
Para conocer las constantes agregadas consultar el archivo constantes.php

Ejemplo de contantes en base de datos:
SELECT sme_id,sme_asunto,sme_Mensaje,sme_color,sme_sistema
FROM ' . **BD_SINPRES** . '.sis_Mensaje
WHERE now() between sme_fechaInicial AND sme_fechaFinal AND sme_tipo = "M";

Ejemplo de contanstante para reemplazar el ejercicio:
setAnioFichero(APP_EJERCICIO);

## Implementación de PHP Data Objects (PDO)
consultar() cambia sus argumentos, recibe un arreglo de valores a enviar a la consulta formatos aceptados por PDO para incrustar variables

### 1. arreglo indexado, las varibles deben ir en el mismo orden que en los parametros
$sql = 'SELECT * FROM cat_UnidadResponsable WHERE gpo_id = ? and ur_id = ?'; 
$parametros = [1, 14];

### 2. arreglo asociativo
$sql = 'SELECT * FROM cat_UnidadResponsable WHERE gpo_id = :grupo and ur_id = :ur'; 
$parametros = ['grupo' => 1, 'ur' => 14];  
$parametros = [':grupo' => 1, ':ur' => 14];

$this->setSql($sql);
$this->conectar();
$this->consultar($parametros);
$this->desconectar();

Llena Tablas:

    $this->conectar();
    $sql = '
        SELECT gpo_id,ur_id,clc_id, clc_concepto,
        FROM scp_Clc
        INNER JOIN enc_FiltroClc USING (gpo_id, ur_id, tcl_id,stc_id)
        WHERE ecl_id = ?
        AND clc_tipoCereCo = ?
        GROUP BY gpo_id,ur_id,clc_id
        ORDER BY gpo_id,ur_id,clc_id;
    ';
    $parametros = [6,2];
    $this->setParams($parametros);
    $this->setSql($sql);
    
    $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
    $nav->idTabla = 'tbl-ceres';
    $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 10;
    if (($this->wrLlenaTabla($nav) == 0) && ($this->prm->tipo != '' && $this->prm->gpo_id != '' && $this->prm->ur_id != '')) {
        $this->mixScript('$("#buscar").click();');
    }


## Implementación de AlertyfyJs 

Se sustituye la libreria bootbox por AlertyfyJs
Ejemplo de implemantacion

[Confirmar una accion https://alertifyjs.com/confirm.html](https://alertifyjs.com/confirm.html)
```JS 
    /*
    * @title {String or DOMElement} The dialog title.
    * @message {String or DOMElement} The dialog contents.
    * @onok {Function} Invoked when the user clicks OK button.
    * @oncancel {Function} Invoked when the user clicks Cancel button or closes the dialog.
    *
    * alertify.confirm(title, message, onok, oncancel);
    *
    */
    
    $(".delPG").click(function(){
        alertify.confirm("Titulo de la modal", "Confirme que desee eliminar!", function(){
            comunica("' . $this->pantalla . '",{
                metodo:"main",
                gpo_id: ' . $this->prm->gpo_id . ',
                ur_id:' . $this->prm->ur_id . ',
                tipo:"' . $this->prm->tipo . '"
            });
        },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});

    });
```


[Alerta https://alertifyjs.com/confirm.html](https://alertifyjs.com/confirm.html)
```JS
    /*
    * @title {String or DOMElement} The dialog title.
    * @message {String or DOMElement} The dialog contents.
    * @onok {Function} Invoked when the user clicks OK button or closes the dialog.
    *
    * alertify.alert(title, message, onok);
    *
    */
    
    
    $(".delPG").click(function(){
        alertify.alert('Titulo de la modal', 'Mensaje del body!', 
            function(){ 
                // Agregar codigo en caso de ser necesario si no omitir la funcion, ya que solo es una alerta
            }
        ).set("labels", {ok:"Aceptar", cancel:"Cancelar"});
    });
```

[Alerta con input https://alertifyjs.com/prompt.html](https://alertifyjs.com/prompt.html)
```JS

    $("#idDelelemento").click(function(){
        alertify.prompt( 'Titulo de la modal', 'Mensaje', 'Valor inicial del input'
            , function(evt, value) { 
                //acción y validación en caso de aceptar
                console.log('El usuario ingreso el siguiente valor', value);
                //en caso de no ser valido
                if (value == ''){
                    notify('Valor no puede ser vacio', 'warning');
                    return false;
                }
            }
            , function() {
                //acción a ejecutar en caso de cancelación
                console.log('El usuario cancelo la modal'); 
        }).set("labels", {ok:"Aceptar", cancel:"Cancelar"});;
    });
```

## Nueva funcion F1
Sustituir la funcionalidad de F1 (slickgrid) por ponF1Proveedores, ejemplo:

$this->mixHtml('
    <div class="col-md-4 mb-2">
        <input type="text" id="rfcFiltro" maxlength="20" class="form-control" placeholder="F1: Catalogo" />
    </div>
    <div class="col-md-8 mb-2">
        <input id="nombreFiltro" type="text" class="form-control" disabled>
    </div>
');

$this->mixScript('
    '.$this->ponF1Proveedores('rfcFiltro', [], 'nombreFiltro').'
');

## Inicio seguro de sesión PHP (`sessionSafeStart`)

Helper para iniciar sesión de forma defensiva y evitar los warnings que aparecen cuando el navegador envía una cookie `PHPSESSID` corrupta o que no cumple el alfabeto/longitud configurados en `php.ini`.

### Síntomas que resuelve

Si en consola/log aparecen mensajes como:

```
Warning: session_start(): Session ID is too long or contains illegal characters. Only the A-Z, a-z, 0-9, "-", and "," characters are allowed in ...
Warning: session_start(): Failed to read session data: files (path: /var/opt/remi/php83/lib/php/session) in ...
```

…significa que el ID que el navegador del usuario envía en la cookie ya no cumple las reglas que `php.ini` exige actualmente (`session.sid_bits_per_character` y `session.sid_length`). El usuario también pierde sus variables de `$_SESSION` porque PHP no logra abrir el archivo de sesión.

### ¿Qué hace el helper?

- Verifica primero si la sesión ya está activa (es **idempotente**, no la reinicia).
- Si la cookie de sesión entrante **no cumple** el alfabeto o la longitud configurados, la descarta del superglobal `$_COOKIE` **antes** de llamar `session_start()`.
- Con la cookie descartada, PHP genera un ID nuevo válido y emite su propio `Set-Cookie`, sobrescribiendo la cookie mala en el navegador. Sin warnings.

### Ubicación

- **Archivo:** `lib/sessionSafeStart.php`
- **Función pública:** `sessionSafeStart(array $options = []): bool`

### Cuándo aplicarlo

**Bajo demanda**, no de forma masiva. Cuando un archivo controlador empiece a generar los warnings descritos arriba, se reemplaza su bloque manual de `session_start()` por una llamada a `sessionSafeStart()`.

### Ejemplo de implementación básico

#### Antes (patrón legacy)

```php
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class Telegram extends WebResponse
{
    // ...
}
```

#### Después (usando el helper)

```php
<?php

require_once dirname(__FILE__) . '/../lib/sessionSafeStart.php';
sessionSafeStart();

require_once dirname(__FILE__) . '/../lib/WebResponse.php';

class Telegram extends WebResponse
{
    // ...
}
```

> Se reemplaza el `if (session_status()...) { session_start(); }` por dos líneas: el `require_once` del helper y la llamada `sessionSafeStart()`. Nada más cambia en la lógica del controlador.

### Ajuste de la ruta `require_once` según ubicación

El helper vive en `lib/`. La cantidad de `../` depende de la profundidad del archivo que lo incluye:

| Ubicación del archivo            | `require_once` correcto                              |
| -------------------------------- | ---------------------------------------------------- |
| `srv/Foo.php`                    | `dirname(__FILE__) . '/../lib/sessionSafeStart.php'`     |
| `poa/identificacion/Foo.php`     | `dirname(__FILE__) . '/../../lib/sessionSafeStart.php'`  |
| `rep/excel/scg/Foo.php`          | `dirname(__FILE__) . '/../../../lib/sessionSafeStart.php'` |
| `lib/Foo.php`                    | `dirname(__FILE__) . '/sessionSafeStart.php'`            |

### Variantes legacy equivalentes que se reemplazan

Cualquiera de estos bloques iniciales se reemplaza por la llamada al helper:

```php
// Variante A
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variante B
if (!isset($_SESSION[_S])) {
    session_start();
}

// Variante C
if (!session_id()) {
    session_start();
}

// Variante D (llamada directa, riesgosa)
session_start();
```

→ Todas se sustituyen por:

```php
require_once dirname(__FILE__) . '/../lib/sessionSafeStart.php';
sessionSafeStart();
```

### Validación posterior al cambio

1. Cargar el módulo afectado y verificar que **no** aparecen warnings de `session_start()` en consola/log.
2. Confirmar que `$_SESSION[_S]` y demás variables de sesión siguen disponibles (no se forzó un logout).
3. Probar el flujo funcional del controlador (pantalla, AJAX, persistencia) tal como quedó.

### Notas técnicas

- El helper detecta el nombre real de la cookie con `session_name()`, **no** hardcodea `PHPSESSID` (por si en algún ambiente se renombra en `php.ini`).
- Lee `session.sid_bits_per_character` y aplica el alfabeto correcto (4 = hex, 5 = `0-9a-v`, 6 = `0-9a-zA-Z,-`).
- Lee `session.sid_length` para validar la longitud máxima.
- Es seguro llamarlo aunque algún `include` previo ya haya iniciado la sesión: regresa `true` sin reiniciarla.

## Manejo de errores PHP en producción (`configurarErroresPhp`)

Configuración centralizada para **no exponer errores PHP al navegador en producción** (OWASP A05 – Security Misconfiguration / CWE-209). En prod los errores se registran en el log del servidor; en QAS, UAT y local se siguen mostrando para depuración.

### Cambio centralizado (equipo base — ya aplicado)

No repetir esta lógica en controladores. Vive solo en archivos generales:

| Archivo | Responsabilidad |
| ------- | --------------- |
| `lib/helpers.php` | Función `configurarErroresPhp()` |
| `lib/constantes.php` | Invoca `configurarErroresPhp()` al final, después de definir `SERVER_PRODUCCION` |
| `lib/WebResponse.php` | **Sin** `ini_set('display_errors', '1')` — la configuración la aplica `constantes.php` |

Detección de producción: `SERVER_PRODUCCION` es `true` cuando `IP_SERVER === '172.17.90.88'` (misma regla que el resto del sistema).

Comportamiento:

| Ambiente | `display_errors` | `log_errors` |
| -------- | ---------------- | ------------ |
| Producción | `0` | `1` |
| QAS / UAT / local | `1` | (default PHP) |

### Responsabilidad de cada programador (por controlador)

Al tocar un controlador o script PHP, **eliminar** cualquier configuración local de errores que anule la centralizada:

```php
// Quitar de controladores y reportes
ini_set('display_errors', '1');
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Reglas:**

1. Cargar `lib/constantes.php` **antes** de cualquier otro `ini_set` relacionado con errores (o usar `WebResponse.php`, que ya incluye `constantes.php`).
2. **No** volver a activar `display_errors` después de cargar constantes.
3. Los `ini_set` de **memoria, tiempo o uploads** (`memory_limit`, `post_max_size`, etc.) pueden quedarse; solo se retiran los de exposición de errores.
4. Scripts que **no** cargan `constantes.php` (algunos reportes en `rep/`) deben incluirlo o invocar `configurarErroresPhp()` manualmente tras definir el entorno.

### Ejemplo de ajuste en un controlador

#### Antes (expone errores en producción)

```php
<?php

ini_set('display_errors', '1');
ini_set('memory_limit', '96M');
error_reporting(E_ALL);

require_once dirname(__FILE__) . "/../../lib/constantes.php";
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
```

#### Después (usa configuración central)

```php
<?php

ini_set('memory_limit', '96M');

require_once dirname(__FILE__) . "/../../lib/constantes.php";
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
```

> Al cargar `constantes.php`, `configurarErroresPhp()` ya aplicó la política según el ambiente. No hace falta nada más.


## Envío de correo con PHPMailer

La copia embebida del proyecto (`lib/scp/phpmailer`) corresponde a **PHPMailer 6+**. A diferencia de versiones antiguas (5.x), la clase ya no vive en el espacio de nombres global: está en `PHPMailer\PHPMailer`.

### Posible error

Si un controlador hace `new PHPMailer()` sin importar el namespace, PHP 8.3 responde:

```
Fatal error: Uncaught Error: Class "PHPMailer" not found
```

### Uso correcto

Incluir `lib/constantes.php` (carga `constantesCredenciales.php`), las clases de PHPMailer y declarar el `use` antes de la clase del controlador o servicio. Ver también [Constantes credenciales](#constantes-credenciales-constantescredenciales).

```php
require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/SMTP.php';
require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;

// ...

$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->isHTML(true);
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->CharSet = PHPMailer::CHARSET_UTF8;
$mail->Host = SMTP_HOST;
$mail->Port = SMTP_PORT;
$mail->Username = SMTP_NOTIFICACIONES_USER;
$mail->Password = SMTP_NOTIFICACIONES_PASS;
$mail->setFrom(SMTP_NOTIFICACIONES_USER, 'Sefip Notificaciones');
// destinatarios, Body, Subject...
$mail->send();
```

### Métodos y constantes que ya no aplican (PHPMailer 5.x)

| Antes (5.x) | Ahora (6+) |
| ----------- | ---------- |
| `IsSMTP()` | `isSMTP()` |
| `IsHtml(true)` | `isHTML(true)` |
| `SMTPSecure = 'tls'` | `PHPMailer::ENCRYPTION_STARTTLS` |
| `CharSet = 'utf-8'` | `PHPMailer::CHARSET_UTF8` |
| `new PHPMailer()` sin `use` | `use PHPMailer\PHPMailer\PHPMailer;` |

No es necesario asignar `$mail->Mailer = 'smtp'` después de `isSMTP()`; el método ya lo configura.

## Lineamiento de rutas de imágenes (PDF y Excel)

Para facilitar mantenimiento y despliegues entre ambientes:

- No hardcodear rutas de imágenes dentro de reportes PDF/Excel.
- La ruta debe definirse mediante constantes en `lib/constantes.php`.
- Si se requiere una nueva ruta/constante, el programador debe solicitarla al líder del proyecto.
- La alta de esa constante se integra por flujo controlado en la rama `development`.

## Referencias legacy

Las secciones siguientes se mantienen como referencia para módulos heredados.
Para implementación nueva, priorizar los apartados de estándar y migración.

## Ajustes generales
* Usar 4 espacios en lugar de tabs
* Seguir PSR-12 para estilo

### Guías legacy
Las secciones siguientes (PDO histórico, AlertifyJS, F1 y ejemplos antiguos) se mantienen como referencia para módulos legacy.
