<?php
$DEFAULT_TITLE .= '\Development';

if (!$user->isInGroup('gg_MIS')) {
    $DEFAULT_ERROR[] = "You do not have access to this module";
    tldUtils::log_event($GLOBALS['PHP_AUTH_USER'].' blocked, MIS Inventory module');
    return;
}

$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=dev">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=dev&m[1]=reports&m[2]=comModByModuleStats">Common module usage by module stats</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=dev&m[1]=baanSessions">Baan Custom Sessions</a>
EOF;

switch ($m[1] ?? null) {
    case 'reports':
        switch ($m[2] ?? null) {
            case 'comModByModuleStats':
                $moduleList = array_keys(tldUtils::getModLinks());
                $commonTableList = [
                    'mod_costs' => ['module'],
                    'mod_costs_type' => ['module'],
                    'mod_faq' => ['module'],
                    'mod_files' => ['module'],
                    'mod_keys' => ['module'],
                    'mod_kpi' => ['module'],
                    'mod_kpireview' => [],
                    'mod_kpireview_comments' => [],
                    'mod_labors' => ['module'],
                    'mod_links' => ['module', 'type'],
                    'mod_lists' => ['module'],
                    'mod_logs' => ['module'],
                    'mod_models' => ['module'],
                    'mod_not' => ['module'],
                    'mod_org' => ['module'],
                    'mod_parts' => ['module'],
                ];

                $matrixData = [];
                foreach ($commonTableList as $table => $fieldModuleCode) {
                    if (empty($fieldModuleCode)) {
                        continue;
                    }
                    foreach ($moduleList as $moduleCode) {
                        $WHERE = "";
                        foreach ($fieldModuleCode as $k => $field) {
                            if ($k == 0) {
                                $WHERE .= "$field LIKE '$moduleCode'";
                            } else {
                                $WHERE .= " OR $field LIKE '$moduleCode'";
                            }
                        }
                        // Query
                        $query = "SELECT '$table' AS tableName, '$moduleCode' AS module, COUNT(*) AS num FROM $table WHERE $WHERE";
                        $matrixData[] = tldUtils::getSqlRowToAssocArray($query);
                    }
                }
                // Display
                $form = new tldMatrix(
                    $matrixData,
                    "tableName", "module", "num",
                    null,
                    "Common module usage by Module"
                );
                $body = $form->fetch();
                break;
        }
        break;
    case 'baanSessions':
        $query = <<<EOF
SELECT
      t1.t_cpac,
      t1.t_cmod,
      t1.t_cses,
      t1.t_vers,
      t1.t_rele,
      t1.t_cust,
      t2.t_clan,
      t1.t_date,
      t2.t_desc
FROM
      tttadv200000 AS t1,
      tttadv130000 AS t2
WHERE
      t1.t_cpac=t2.t_cpac AND
      t1.t_vers=t2.t_vers AND
      t1.t_rele=t2.t_rele AND
      t1.t_cust=t2.t_cust AND
      t1.t_cmod + t1.t_cses = t2.t_rkey AND
      t2.t_kdes='2' AND
      (t1.t_cust='prd0' OR t1.t_cust='chpr')
EOF;
        $rows = tldUtils::getSqlToAssocArray(
            $query,
            "odbc",
            [
                "src" => "baan",
            ]
        );
        if (count($rows)) {
            $sess["calendar"]["tasks"] = $rows;
            $form = new tldReportMultiLevel(
                $rows,
                [
                    "t_cpac", "t_cmod", "t_cses",
                ],
                [
                    "t_cpac" => "Package",
                    "t_cmod" => "Module",
                    "t_cses" => "Session",
                    "t_vers" => "Version",
                    "t_rele" => "Release",
                    "t_cust" => "Custom",
                    "t_clan" => "Language",
                    "t_date" => "Date",
                    "t_desc" => "Description",
                ],
                [
                    "title" => "TLD Custom Baan Sessions",
                ]
            );
            $body .= $form->fetch();
        }
        break;
    case 'baanTables':
        $query = <<<EOF
select t1.*, t2.t_desc
from tttadv420000 as t1, tttadv130000 as t2
where
t1.t_cpac=t2.t_cpac
and t1.t_cmod=t2.t_cmod
and t1.t_vers=t2.t_vers
and t1.t_rele=t2.t_rele
and t1.t_cust=t2.t_cust
and t1.t_cprs=t2.t_cfrm
and t1.t_cmod='tld'
order by t1.t_cpac, t1.t_cses, t1.t_vers, t1.t_cust
EOF;
        $rows = tldUtils::getSqlToAssocArray(
            $query,
            "odbc",
            [
                "src" => "baan",
            ]
        );
        if (count($rows)) {
            $sess["calendar"]["tasks"] = $rows;
            $form = new tldReportMultiLevel(
                $rows,
                ["t_cpac"],
                [
                    "t_cpac" => "Package",
                    "t_cmod" => "Module",
                    "t_cses" => "Session",
                    "t_vers" => "Version",
                    "t_rele" => "Release",
                    "t_cust" => "Custom",
                    "t_desc" => "Description",
                ],
                [
                    "title" => "TLD Custom Baan Sessions",
                ]
            );
            $body .= $form->fetch();
        }
        break;
    default:
        $body = $smarty->fetch("$PATH/dev/homepage.dev.tpl");
}
