<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Inbox Messaging System API",
 *     version="1.0.0",
 *     description="API REST para sistema de mensajería tipo Inbox con autenticación JWT",
 *     @OA\Contact(
 *         email="admin@inbox.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor de desarrollo local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Ingrese el token JWT en el formato: Bearer {token}"
 * )
 */
abstract class Controller
{
    //
}
