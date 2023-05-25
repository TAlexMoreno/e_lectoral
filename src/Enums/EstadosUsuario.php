<?php
namespace App\Enums;

enum EstadosUsuario: int {
    case JustCreated = 0;
    case Operative = 1;
    case LostAccess = 2;
    case Blocked = 3;
}