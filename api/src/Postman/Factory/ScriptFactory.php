<?php

declare(strict_types=1);

namespace App\Postman\Factory;

use App\Postman\Resource\Script;

class ScriptFactory
{
    public function create(): Script
    {
        $script = new Script();
        $script->type = 'text/javascript';
        $script->addExec("pm.environment.set('TLD-JWT', pm.response.json().token);");

        return $script;
    }
}
