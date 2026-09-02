<?php

declare(strict_types=1);

namespace App\Pi\Utils;

class Config
{
    public function getPIConfig($erp, $koop, $func)
    {
        $query = "select $func AS resu from pi_config_func where comp=$erp and koop LIKE '$koop'";
        $rowsTmp1 = \tldUtils::getSqlRowToAssocArray($query);

        return $rowsTmp1['resu'] ?? [];
    }

    public function getAllPIConfig($erp, array $koops, $func)
    {
        $query = "select koop, $func AS resu from pi_config_func where comp=$erp and koop IN ('".implode("','", $koops)."')";

        return \tldUtils::getSqlToAssocArray($query);
    }
}
