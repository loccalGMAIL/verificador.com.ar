<?php

namespace App\Services\Odoo;

use RuntimeException;

/** Error al comunicarse con la API de Odoo (red, autenticación, permisos o error del servidor). */
class OdooException extends RuntimeException {}
