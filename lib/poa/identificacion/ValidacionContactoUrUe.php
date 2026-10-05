<?php

/**
 * Validación de correo electrónico y celular entre Información UR (4 titulares) e Información UE.
 * En UR el título va en campo aparte; en UE va dentro del nombre del titular.
 * Permite duplicados solo cuando corresponde a la misma persona.
 */
require_once dirname(__FILE__) . '/../../AccesoDatos.php';

class ValidacionContactoUrUe
{
    /** Titular UR, titular administrativo, titular de planeación, responsable de seguimiento */
    private static $mapaUr = [
        ['tipo' => 'correo', 'columna' => 'ur_correoElectronicoTitular', 'nombre' => 'ur_nombreTitular', 'titulo' => 'ur_tituloTitular', 'etiqueta' => 'Titular UR'],
        ['tipo' => 'telefono', 'columna' => 'ur_celularTitular', 'nombre' => 'ur_nombreTitular', 'titulo' => 'ur_tituloTitular', 'etiqueta' => 'Titular UR'],
        ['tipo' => 'correo', 'columna' => 'ur_correoElectronicoAdministrativo', 'nombre' => 'ur_nombreAdministrativo', 'titulo' => 'ur_tituloAdministrativo', 'etiqueta' => 'Titular administrativo UR'],
        ['tipo' => 'telefono', 'columna' => 'ur_celularAdministrativo', 'nombre' => 'ur_nombreAdministrativo', 'titulo' => 'ur_tituloAdministrativo', 'etiqueta' => 'Titular administrativo UR'],
        ['tipo' => 'correo', 'columna' => 'ur_correoElectronicoPlaneacion', 'nombre' => 'ur_nombrePlaneacion', 'titulo' => 'ur_tituloPlaneacion', 'etiqueta' => 'Titular de planeación UR'],
        ['tipo' => 'telefono', 'columna' => 'ur_celularPlaneacion', 'nombre' => 'ur_nombrePlaneacion', 'titulo' => 'ur_tituloPlaneacion', 'etiqueta' => 'Titular de planeación UR'],
        ['tipo' => 'correo', 'columna' => 'ur_correoElectronicoEvaluacion', 'nombre' => 'ur_nombreEvaluacion', 'titulo' => 'ur_tituloEvaluacion', 'etiqueta' => 'Responsable de seguimiento UR'],
        ['tipo' => 'telefono', 'columna' => 'ur_celularEvaluacion', 'nombre' => 'ur_nombreEvaluacion', 'titulo' => 'ur_tituloEvaluacion', 'etiqueta' => 'Responsable de seguimiento UR'],
    ];

    private static $mapaUe = [
        ['tipo' => 'correo', 'columna' => 'ue_correoInstitucional', 'nombre' => 'ue_nombreTitular', 'etiqueta' => 'Titular UE'],
        ['tipo' => 'telefono', 'columna' => 'ue_celular', 'nombre' => 'ue_nombreTitular', 'etiqueta' => 'Titular UE'],
    ];

    public static function normalizarCorreo($valor)
    {
        return strtolower(trim((string) $valor));
    }

    public static function normalizarTelefono($valor)
    {
        return preg_replace('/\D/', '', (string) $valor);
    }

    /**
     * Quita acentos y diacríticos para comparar nombres (É = E, Ñ = N, etc.).
     */
    private static function quitarAcentos($texto)
    {
        $texto = (string) $texto;
        if (class_exists('Normalizer')) {
            $descompuesto = Normalizer::normalize($texto, Normalizer::FORM_D);
            if ($descompuesto !== false) {
                return preg_replace('/\p{Mn}/u', '', $descompuesto);
            }
        }
        if (function_exists('iconv')) {
            $sinAcento = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
            if ($sinAcento !== false) {
                return $sinAcento;
            }
        }
        $mapa = [
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A', 'Ã' => 'A',
            'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U',
            'Ñ' => 'N', 'Ç' => 'C',
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ];

        return strtr($texto, $mapa);
    }

    public static function normalizarNombre($valor)
    {
        $valor = self::quitarAcentos(trim((string) $valor));

        return preg_replace('/\s+/', ' ', strtoupper($valor));
    }

    /**
     * Nombre completo UR: título + nombre (UE guarda ambos en un solo campo).
     */
    public static function nombreCompletoUr($titulo, $nombre)
    {
        $titulo = trim((string) $titulo);
        $nombre = trim((string) $nombre);

        if ($titulo !== '' && $nombre !== '') {
            return self::normalizarNombre($titulo . ' ' . $nombre);
        }

        return self::normalizarNombre($nombre !== '' ? $nombre : $titulo);
    }

    /**
     * Catálogo de títulos capturados en planeación (complementa el patrón regex).
     */
    private static function titulosProfesionalesConocidos()
    {
        return [
            'ARQ', 'RET', 'DEM', 'ALMIRANTE', 'GRAL', 'CAP', 'MTRO', 'MTRA', 'PROF',
            'SR', 'SRA', 'ING', 'LIC', 'LICDA', 'LCDA', 'LCDO', 'DR', 'DRA', 'MAG',
            'DIPUTADO', 'DIPUTADA', 'LNC', 'CP', 'CPC', 'QFB', 'MVZ', 'MCP', 'PSP',
        ];
    }

    /**
     * Patrón de prefijos de título al inicio del nombre (nombres ya normalizados en mayúsculas).
     * Basado en títulos registrados en Información UR/UE.
     */
    private static function patronPrefijoTitulos()
    {
        return '/^((([A-Z]+\.)+[A-Z]?\.?-?|[A-Z]\.|ARQ\.?|RET\.?|DEM\.?|ALMIRANTE\.?|GRAL\.?|CAP\.?|'
            . 'MTRO\.?|MTRA\.?|PROF\.?|SR\.?|SRA\.?|ING\.?|LIC\.?|LICDA\.?|LCDA\.?|DR\.?|DRA\.?|MAG\.?|'
            . 'DIPUTADO|DIPUTADA)[\s\-]+)+/';
    }

    private static function esTituloProfesional($token)
    {
        return in_array(rtrim((string) $token, '.-'), self::titulosProfesionalesConocidos(), true);
    }

    /**
     * Separa título pegado al nombre: LIC.ELIZABETH -> LIC. ELIZABETH
     */
    private static function separarTituloPegadoAlNombre($nombreNormalizado)
    {
        foreach (self::titulosProfesionalesConocidos() as $titulo) {
            $prefijo = $titulo . '.';
            $longitud = strlen($prefijo);
            if (strlen($nombreNormalizado) > $longitud && substr($nombreNormalizado, 0, $longitud) === $prefijo) {
                $siguiente = $nombreNormalizado[$longitud];
                if ($siguiente !== ' ' && $siguiente !== '-') {
                    $nombreNormalizado = $prefijo . ' ' . substr($nombreNormalizado, $longitud);
                }
            }
        }

        $nombreNormalizado = preg_replace('/^([A-Z]{2,})\.([A-Z])/', '$1. $2', $nombreNormalizado, 1);
        $nombreNormalizado = preg_replace('/^([A-Z])\.([A-Z])/', '$1. $2', $nombreNormalizado, 1);

        return $nombreNormalizado;
    }

    /**
     * Quita prefijo de título en un token sin espacio (ej. LIC.ELIZABETH -> ELIZABETH).
     */
    private static function quitarTituloDeToken($token)
    {
        foreach (self::titulosProfesionalesConocidos() as $titulo) {
            $prefijo = $titulo . '.';
            if (strpos($token, $prefijo) === 0) {
                return substr($token, strlen($prefijo));
            }
        }

        return $token;
    }

    /**
     * Nombre sin títulos al inicio (Lic., Mtro., Arq., etc.) ya normalizado.
     */
    public static function quitarTitulosProfesionales($nombreNormalizado)
    {
        $nombreNormalizado = trim((string) $nombreNormalizado);
        if ($nombreNormalizado === '') {
            return '';
        }

        $nombreNormalizado = self::separarTituloPegadoAlNombre($nombreNormalizado);

        $patron = self::patronPrefijoTitulos();
        $anterior = null;
        while ($nombreNormalizado !== $anterior && preg_match($patron, $nombreNormalizado)) {
            $anterior = $nombreNormalizado;
            $nombreNormalizado = trim((string) preg_replace($patron, '', $nombreNormalizado, 1));
        }

        $partes = explode(' ', $nombreNormalizado);
        if (count($partes) > 0) {
            $partes[0] = self::quitarTituloDeToken($partes[0]);
            if ($partes[0] === '') {
                array_shift($partes);
            }
        }
        while (count($partes) > 0 && self::esTituloProfesional($partes[0])) {
            array_shift($partes);
        }

        return trim(implode(' ', $partes));
    }

    /**
     * Determina si dos nombres corresponden a la misma persona.
     * Ignora acentos, mayúsculas y títulos al inicio (Lic., Mtro., etc.).
     * No compara por prefijos parciales (LOPEZ ≠ RODRIGUEZ).
     */
    public static function nombresCoinciden($nombreA, $nombreB, $nombreSoloA = null, $nombreSoloB = null)
    {
        $a = self::normalizarNombre($nombreA);
        $b = self::normalizarNombre($nombreB);

        if ($a === '' || $b === '') {
            return false;
        }
        if ($a === $b) {
            return true;
        }

        $sinTituloA = self::quitarTitulosProfesionales($a);
        $sinTituloB = self::quitarTitulosProfesionales($b);
        if ($sinTituloA !== '' && $sinTituloA === $sinTituloB) {
            return true;
        }

        $soloA = $nombreSoloA !== null ? self::normalizarNombre($nombreSoloA) : '';
        $soloB = $nombreSoloB !== null ? self::normalizarNombre($nombreSoloB) : '';

        if ($soloA !== '' && $soloB !== '' && $soloA === $soloB) {
            return true;
        }
        if ($soloA !== '' && $soloA === $sinTituloB) {
            return true;
        }
        if ($soloB !== '' && $soloB === $sinTituloA) {
            return true;
        }

        return false;
    }

    private static function mismosUsuario(array $contactoA, array $contactoB)
    {
        return self::nombresCoinciden(
            $contactoA['nombre'],
            $contactoB['nombre'],
            isset($contactoA['nombre_solo']) ? $contactoA['nombre_solo'] : null,
            isset($contactoB['nombre_solo']) ? $contactoB['nombre_solo'] : null
        );
    }

    private static function contactoPendiente(array $contacto)
    {
        $nombre = self::normalizarNombre(isset($contacto['nombre']) ? $contacto['nombre'] : '');

        return [
            'tipo' => $contacto['tipo'],
            'valor' => $contacto['valor'],
            'nombre' => $nombre,
            'nombre_solo' => isset($contacto['nombre_solo']) ? self::normalizarNombre($contacto['nombre_solo']) : $nombre,
            'etiqueta' => isset($contacto['etiqueta']) ? $contacto['etiqueta'] : 'el contacto',
        ];
    }

    private static function normalizarValor($tipo, $valor)
    {
        if ($tipo === 'correo') {
            return self::normalizarCorreo($valor);
        }

        return self::normalizarTelefono($valor);
    }

    public static function validarLista(array $contactos, array $excluirUr = [], array $excluirUe = [], array $opciones = [])
    {
        $pendientes = [];

        foreach ($contactos as $contacto) {
            $tipo = isset($contacto['tipo']) ? $contacto['tipo'] : '';
            $valor = isset($contacto['valor']) ? $contacto['valor'] : '';
            $normalizado = self::normalizarValor($tipo, $valor);
            if ($normalizado === '') {
                continue;
            }
            $etiqueta = isset($contacto['etiqueta']) ? $contacto['etiqueta'] : 'el contacto';
            $pendientes[] = self::contactoPendiente([
                'tipo' => $tipo,
                'valor' => $normalizado,
                'nombre' => isset($contacto['nombre']) ? $contacto['nombre'] : '',
                'nombre_solo' => isset($contacto['nombre_solo']) ? $contacto['nombre_solo'] : null,
                'etiqueta' => $etiqueta,
            ]);
        }

        $total = count($pendientes);
        for ($i = 0; $i < $total; $i++) {
            for ($j = $i + 1; $j < $total; $j++) {
                if ($pendientes[$i]['tipo'] !== $pendientes[$j]['tipo']) {
                    continue;
                }
                if ($pendientes[$i]['valor'] !== $pendientes[$j]['valor']) {
                    continue;
                }
                if (!self::mismosUsuario($pendientes[$i], $pendientes[$j])) {
                    return self::respuestaError($pendientes[$i]['tipo'], $pendientes[$j]['etiqueta']);
                }
            }
        }

        $gpoId = isset($opciones['gpo_id']) ? $opciones['gpo_id'] : null;
        $alcance = isset($opciones['alcance']) ? $opciones['alcance'] : 'ur_y_ues';
        $soloUrId = null;
        if ($alcance !== 'todas_urs' && isset($opciones['ur_id']) && $opciones['ur_id'] !== '') {
            $soloUrId = $opciones['ur_id'];
        }

        $registrados = self::cargarContactosRegistrados($excluirUr, $excluirUe, $gpoId, $soloUrId);
        foreach ($pendientes as $pendiente) {
            foreach ($registrados as $registro) {
                if ($pendiente['tipo'] !== $registro['tipo']) {
                    continue;
                }
                if ($pendiente['valor'] !== $registro['valor']) {
                    continue;
                }
                if ($pendiente['nombre'] === '') {
                    return self::respuestaError($pendiente['tipo'], $registro['etiqueta']);
                }
                if (!self::mismosUsuario($pendiente, $registro)) {
                    return self::respuestaError($pendiente['tipo'], $registro['etiqueta']);
                }
            }
        }

        return ['ok' => true];
    }

    public static function contactosDesdePrmUr($prm)
    {
        $roles = [
            [
                'etiqueta' => 'Titular UR',
                'titulo' => 'tituloTitular',
                'nombre' => 'nombreTitular',
                'correo' => 'correoElectronicoTitular',
                'telefonos' => ['celularTitular'],
            ],
            [
                'etiqueta' => 'Titular administrativo UR',
                'titulo' => 'tituloAdministrativo',
                'nombre' => 'nombreAdministrativo',
                'correo' => 'correoElectronicoAdministrativo',
                'telefonos' => ['celularAdministrativo'],
            ],
            [
                'etiqueta' => 'Titular de planeación UR',
                'titulo' => 'tituloPlaneacion',
                'nombre' => 'nombrePlaneacion',
                'correo' => 'correoElectronicoPlaneacion',
                'telefonos' => ['celularPlaneacion'],
            ],
            [
                'etiqueta' => 'Responsable de seguimiento UR',
                'titulo' => 'tituloEvaluacion',
                'nombre' => 'nombreEvaluacion',
                'correo' => 'correoElectronicoEvaluacion',
                'telefonos' => ['celularEvaluacion'],
            ],
        ];

        return self::armarContactosRolesUr($prm, $roles);
    }

    public static function contactosDesdePrmUe($prm, $datosGuardados = null)
    {
        $nombre = null;
        if (isset($prm->nombreTitular) && trim((string) $prm->nombreTitular) !== '') {
            $nombre = $prm->nombreTitular;
        } elseif (is_array($datosGuardados) && !empty($datosGuardados['nombreTitular'])) {
            $nombre = $datosGuardados['nombreTitular'];
        }
        if ($nombre === null || trim((string) $nombre) === '') {
            return [];
        }
        $nombreCompleto = self::normalizarNombre($nombre);
        $nombreSolo = self::quitarTitulosProfesionales($nombreCompleto);
        if ($nombreSolo === '') {
            $nombreSolo = $nombreCompleto;
        }
        $mapa = [
            ['tipo' => 'correo', 'valor' => 'correo', 'etiqueta' => 'Titular UE'],
            ['tipo' => 'telefono', 'valor' => 'telCelular', 'etiqueta' => 'Titular UE'],
        ];
        $contactos = [];
        foreach ($mapa as $item) {
            $valor = null;
            if (isset($prm->{$item['valor']}) && trim((string) $prm->{$item['valor']}) !== '') {
                $valor = trim((string) $prm->{$item['valor']});
            } elseif (is_array($datosGuardados) && isset($datosGuardados[$item['valor']])
                && trim((string) $datosGuardados[$item['valor']]) !== '') {
                $valor = trim((string) $datosGuardados[$item['valor']]);
            }
            if ($valor === null || $valor === '') {
                continue;
            }
            $contactos[] = [
                'tipo' => $item['tipo'],
                'valor' => $valor,
                'nombre' => $nombreCompleto,
                'nombre_solo' => $nombreSolo,
                'etiqueta' => $item['etiqueta'],
            ];
        }

        return $contactos;
    }

    private static function armarContactosRolesUr($prm, array $roles)
    {
        $contactos = [];
        foreach ($roles as $rol) {
            $titulo = isset($prm->{$rol['titulo']}) ? $prm->{$rol['titulo']} : '';
            $nombre = isset($prm->{$rol['nombre']}) ? $prm->{$rol['nombre']} : '';
            $nombreCompleto = self::nombreCompletoUr($titulo, $nombre);
            $nombreSolo = self::normalizarNombre($nombre);
            if (isset($prm->{$rol['correo']})) {
                $valor = trim((string) $prm->{$rol['correo']});
                if ($valor !== '') {
                    $contactos[] = [
                        'tipo' => 'correo',
                        'valor' => $valor,
                        'nombre' => $nombreCompleto,
                        'nombre_solo' => $nombreSolo,
                        'etiqueta' => $rol['etiqueta'],
                    ];
                }
            }
            foreach ($rol['telefonos'] as $campoTelefono) {
                if (!isset($prm->{$campoTelefono})) {
                    continue;
                }
                $valor = trim((string) $prm->{$campoTelefono});
                if ($valor === '') {
                    continue;
                }
                $contactos[] = [
                    'tipo' => 'telefono',
                    'valor' => $valor,
                    'nombre' => $nombreCompleto,
                    'nombre_solo' => $nombreSolo,
                    'etiqueta' => $rol['etiqueta'],
                ];
            }
        }

        return $contactos;
    }

    private static function cargarContactosRegistrados(array $excluirUr, array $excluirUe, $gpoId = null, $soloUrId = null)
    {
        $registros = [];
        $conexion = new AccesoDatos();
        $conexion->conectar();

        $where = [];
        $whereAux = [];
        $params = [];
        if ($gpoId !== null && $gpoId !== '') {
            $where[] = 'gpo_id = ?';
            $whereAux[] = 'a.gpo_id = ?';
            $params[] = $gpoId;
        }
        if ($soloUrId !== null && $soloUrId !== '') {
            $where[] = 'ur_id = ?';
            $whereAux[] = 'a.ur_id = ?';
            $params[] = $soloUrId;
        }
        $clausulaWhere = count($where) > 0 ? ' WHERE ' . implode(' AND ', $where) : '';
        $clausulaWhereAux = count($whereAux) > 0 ? ' WHERE ' . implode(' AND ', $whereAux) : '';

        $sqlUr = 'SELECT a.gpo_id, a.ur_id, c.ur_clave, ' . self::columnasSql(self::$mapaUr, 'a')
            . ' FROM ' . BD_CTRL . '.aux_UnidadResponsable a'
            . ' INNER JOIN ' . BD_CTRL . '.cat_UnidadResponsable c USING (gpo_id, ur_id)'
            . $clausulaWhereAux;
        $conexion->setSql($sqlUr);
        $conexion->consultar($params);
        while ($fila = $conexion->extraer()) {
            if (self::excluirUr($fila, $excluirUr)) {
                continue;
            }
            $registros = array_merge($registros, self::expandirFila($fila, 'UR', self::$mapaUr));
        }

        $sqlUe = 'SELECT gpo_id, ur_id, ue_id, ' . self::columnasSql(self::$mapaUe)
            . ' FROM ' . BD_CTRL . '.cat_UnidadEjecutora' . $clausulaWhere;
        $conexion->setSql($sqlUe);
        $conexion->consultar($params);
        while ($fila = $conexion->extraer()) {
            if (self::excluirUe($fila, $excluirUe)) {
                continue;
            }
            $registros = array_merge($registros, self::expandirFila($fila, 'UE', self::$mapaUe));
        }

        $conexion->desconectar();

        return $registros;
    }

    private static function columnasSql(array $mapa, $prefijoTabla = '')
    {
        $columnas = [];
        $prefijo = $prefijoTabla !== '' ? $prefijoTabla . '.' : '';
        foreach ($mapa as $item) {
            $columnas[$item['columna']] = $prefijo . $item['columna'];
            if (isset($item['nombre']) && $item['nombre'] !== null) {
                $columnas[$item['nombre']] = $prefijo . $item['nombre'];
            }
            if (isset($item['titulo']) && $item['titulo'] !== null) {
                $columnas[$item['titulo']] = $prefijo . $item['titulo'];
            }
        }

        return implode(', ', array_values($columnas));
    }

    private static function nombreVisibleDesdeFila(array $fila, array $item)
    {
        if (isset($item['titulo'], $item['nombre'], $fila[$item['nombre']])) {
            $titulo = trim((string) $fila[$item['titulo']]);
            $nombre = trim((string) $fila[$item['nombre']]);
            if ($titulo !== '' && $nombre !== '') {
                return $titulo . ' ' . $nombre;
            }

            return $nombre !== '' ? $nombre : $titulo;
        }
        if (isset($item['nombre'], $fila[$item['nombre']])) {
            return trim((string) $fila[$item['nombre']]);
        }

        return '';
    }

    private static function tipoTelefonoEtiqueta(array $item)
    {
        if ($item['tipo'] !== 'telefono') {
            return '';
        }

        return ', celular';
    }

    private static function claveUrEtiqueta(array $fila)
    {
        if (isset($fila['ur_clave']) && $fila['ur_clave'] !== '') {
            return (string) $fila['ur_clave'];
        }

        return $fila['gpo_id'] . $fila['ur_id'];
    }

    private static function nombreDesdeFila(array $fila, array $item)
    {
        if (isset($item['titulo'], $fila[$item['titulo']], $item['nombre'], $fila[$item['nombre']])) {
            return self::nombreCompletoUr($fila[$item['titulo']], $fila[$item['nombre']]);
        }
        if (isset($item['nombre'], $fila[$item['nombre']])) {
            return self::normalizarNombre($fila[$item['nombre']]);
        }

        return '';
    }

    private static function nombreSoloDesdeFila(array $fila, array $item)
    {
        if (isset($item['nombre'], $fila[$item['nombre']])) {
            return self::normalizarNombre($fila[$item['nombre']]);
        }

        return '';
    }

    private static function expandirFila(array $fila, $origen, array $mapa)
    {
        $registros = [];
        foreach ($mapa as $item) {
            if (!isset($fila[$item['columna']])) {
                continue;
            }
            $valor = self::normalizarValor($item['tipo'], $fila[$item['columna']]);
            if ($valor === '') {
                continue;
            }
            $nombre = self::nombreDesdeFila($fila, $item);
            $nombreSolo = self::nombreSoloDesdeFila($fila, $item);
            $nombreVisible = self::nombreVisibleDesdeFila($fila, $item);
            $etiqueta = $item['etiqueta'] . self::tipoTelefonoEtiqueta($item);
            if ($nombreVisible !== '') {
                $etiqueta .= ': ' . $nombreVisible;
            }
            if ($origen === 'UE' && isset($fila['ue_id'])) {
                $etiqueta .= ' (UE ' . $fila['gpo_id'] . $fila['ur_id'] . $fila['ue_id'] . ')';
            } elseif (isset($fila['gpo_id'], $fila['ur_id'])) {
                $etiqueta .= ' (UR ' . self::claveUrEtiqueta($fila) . ')';
            }
            $registros[] = [
                'tipo' => $item['tipo'],
                'valor' => $valor,
                'nombre' => $nombre,
                'nombre_solo' => $nombreSolo,
                'etiqueta' => $etiqueta,
            ];
        }

        return $registros;
    }

    private static function excluirUr(array $fila, array $excluirUr)
    {
        return isset($excluirUr['gpo_id'], $excluirUr['ur_id'])
            && (string) $fila['gpo_id'] === (string) $excluirUr['gpo_id']
            && (string) $fila['ur_id'] === (string) $excluirUr['ur_id'];
    }

    private static function excluirUe(array $fila, array $excluirUe)
    {
        return isset($excluirUe['gpo_id'], $excluirUe['ur_id'], $excluirUe['ue_id'])
            && (string) $fila['gpo_id'] === (string) $excluirUe['gpo_id']
            && (string) $fila['ur_id'] === (string) $excluirUe['ur_id']
            && (string) $fila['ue_id'] === (string) $excluirUe['ue_id'];
    }

    private static function respuestaError($tipo, $registradoEn)
    {
        if ($tipo === 'correo') {
            $mensaje = 'El correo electrónico ya se encuentra registrado';
        } else {
            $mensaje = 'El teléfono celular ya se encuentra registrado';
        }
        if ($registradoEn !== '') {
            $mensaje .= ' (' . $registradoEn . ')';
        }
        $mensaje .= '.';

        return [
            'ok' => false,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
        ];
    }
}
