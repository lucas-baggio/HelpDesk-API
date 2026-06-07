<?php

namespace App\Domains\User\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Tecnico = 'tecnico';
    case Atendente = 'atendente';
}
