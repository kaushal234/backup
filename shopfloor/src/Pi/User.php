<?php

declare(strict_types=1);

namespace App\Pi;

use App\Pi\Utils\ArrayUtils;

class User
{
    /**
     * @var array
     */
    private $user;

    private function __construct($id = null, $user = null)
    {
        if (null !== $id) {
            $query = <<<SQL
SELECT p.*, g.group_name AS 'group.name', g.level AS 'group.level'
FROM people AS p
LEFT JOIN people_groups AS g
  ON g.parent_id = p.id
WHERE p.id = '$id' OR p.username = '$id'
SQL;
            $data = \tldUtils::getSqlToAssocArray($query);
            $this->user = self::formatUserFromDBRows($data);
        } elseif (null !== $user) {
            $this->user = $user;
        }
    }

    public function __get($prop)
    {
        return $this->user[$prop] ?? null;
    }

    public static function fromId($id)
    {
        return new self($id);
    }

    public function getId(): int
    {
        return (int) ($this->user['id'] ?? 0);
    }

    public function getUserid(): string
    {
        return $this->user['email'] ?? '';
    }

    public static function formatUserFromDBRows(?array $data = null): array
    {
        if (empty($data)) {
            $user['groups'] = [];

            return $user;
        }
        $user = array_map(static function ($value) {
            return \is_string($value) ? trim($value) : $value;
        }, $data[0]);

        $groups = [];
        foreach ($data as $d) {
            $row = ArrayUtils::toMultiDimensional($d);
            $groups[] = array_map('trim', $row['group']);
        }

        $user['groups'] = $groups;
        unset($user['group.name'], $user['group.level']);

        return $user;
    }

    public function getUser(): array
    {
        return $this->user;
    }

    public function isPasswordValid(string $password): bool
    {
        return null !== $this->user && $this->user['password'] === stripslashes($password);
    }

    public function isInGroups(array $groups, $level = null): bool
    {
        $commonGroups = array_intersect($groups, $this->getGroupsName());

        if (null !== $level) {
            $commonGroups = array_filter($commonGroups, static function ($group) use ($level) {
                return $group['level'] == $level;
            });
        }

        return !empty($commonGroups);
    }

    private function getGroupsName(): array
    {
        if (null === $this->user || null == $this->user['groups']) {
            return [];
        }

        return array_column($this->user['groups'], 'name');
    }
}
