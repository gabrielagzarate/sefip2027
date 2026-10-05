# Migración SlickGrid 2.4.44
> Notas sobre la migración de la librería SlickGrid

> Fecha: 28/04/2022

## Documentación &#x1f5ba;

_Repositorio de la versión anterior (Desactualizado):_
- https://github.com/mleibman/SlickGrid

__Repositorio de la nueva versión__:
- https://github.com/6pac/SlickGrid

__Wiki__: 
- https://github.com/6pac/SlickGrid/wiki

## Notas &#x1f5d2;
1. Se actualizaron los archivos js de la librería.
```
   └─── js\slickGrid
```

2. Se agregaron nuevos estilos al CSS, línea 248.
```
   └─── css\slick.grid.css
```

3. La opción __forceFitColumns__ está deprecada. Se reemplazó por __autosizeColsMode__.

4. Se debe emplear la función __autosizeColumns()__ después de __init()__ para ajustar el ancho de las columnas al tamaño de la tabla.

5. Para las tablas que se visualizan en modales remover la opción __explicitInitialization: true__ y posteriormente aplicar el cambio del punto 4.

## Ejemplo &#x1f4bb;
- Acceder con UR: __115__, Usuario: __VHUITRONG__
- __SEFIP > SCG > Consulta > Libro Diario__
- Archivo pantallasModales  
```
└─── lib\pantallasModales.php
```
- Función: __DibujaPantallaBuscarCgClaveFinaciamientoJS()__
- __Lineas 1826 y 1945__

## Ejemplo tablas dentro de modales
- Archivo lib/funciones.php en la línea 305 se elimina la opción __explicitInitialization: true__ en la configuración (options) de la tabla.
- Posteriormente se utiliza __autosizeColumns()__ después de __init()__ en la línea 442.