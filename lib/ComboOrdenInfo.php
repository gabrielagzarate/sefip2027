<?php
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
     
// Regresamos un combo con un orden especifico, para regresar la información
    // ------------------------------------------------------------------------------------------------------------------------
    function ComboOrdenInfo()
    {
        
        return '
            <select class="ordenadoPor form-select" name="ordenActual" id="ordenActual" class="form-control">
                <option value="or_N">NÚMERO DE PÓLIZA</option>
                <option value="or_F">FECHA</option>
                <option value="or_i">IMPORTE</option>
            </select>
        ';
    }