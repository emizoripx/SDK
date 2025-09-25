<?php

namespace Emizor\SDK\Enums;

enum ParametricType: string
{
    // Global
    case MOTIVO_ANULACION = 'motivos-de-anulacion';
    case TIPOS_DOCUMENTO_IDENTIDAD = 'tipos-documento-de-identidad';
    case METODOS_DE_PAGO = 'metodos-de-pago';
    case UNIDADES = 'unidades';

    // Specific
    case ACTIVIDADES = 'actividades';
    case LEYENDAS = 'leyendas';
    case PRODUCTOS_SIN = 'productos-sin';
}
