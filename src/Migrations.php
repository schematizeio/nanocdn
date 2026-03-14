<?php
namespace NanoCDN;

class Migrations
{
    private const TABLE_SQL = <<<'SQL'
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` varchar(100) NOT NULL,
  `executed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

    public static function ensureTable(): void
    {
        Database::get()->exec(self::TABLE_SQL);
    }

    /** @return array<int, array{id: string, name: string, sql: string}> */
    public static function getAll(): array
    {
        $file = __DIR__ . '/../config/migrations.php';
        if (!is_file($file)) {
            return [];
        }
        $list = require $file;
        return is_array($list) ? $list : [];
    }

    /** @return array<string> ids já executados */
    public static function getExecutedIds(): array
    {
        self::ensureTable();
        $rows = Database::fetchAll('SELECT id FROM migrations ORDER BY executed_at');
        return array_column($rows, 'id');
    }

    /** @return array<int, array{id: string, name: string, sql: string}> migrações pendentes */
    public static function getPending(): array
    {
        $executed = array_flip(self::getExecutedIds());
        $all = self::getAll();
        $pending = [];
        foreach ($all as $m) {
            if (isset($m['id']) && !isset($executed[$m['id']])) {
                $pending[] = $m;
            }
        }
        return $pending;
    }

    /**
     * Executa uma migração (pode conter vários statements separados por ;).
     * @return array{success: bool, message: string}
     */
    public static function runOne(array $migration): array
    {
        $id = $migration['id'] ?? '';
        $sql = $migration['sql'] ?? '';
        if ($id === '' || $sql === '') {
            return ['success' => false, 'message' => 'Migração inválida (id ou sql vazio).'];
        }
        self::ensureTable();
        $pdo = Database::get();
        $statements = self::splitSql($sql);
        $duplicateColumn = false;
        foreach ($statements as $stmt) {
            if ($stmt === '') {
                continue;
            }
            try {
                $pdo->exec($stmt);
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (strpos($msg, 'Duplicate column name') !== false || strpos($msg, 'Duplicate key name') !== false) {
                    $duplicateColumn = true;
                    continue;
                }
                return ['success' => false, 'message' => $msg];
            }
        }
        try {
            Database::run('INSERT INTO migrations (id) VALUES (?)', [$id]);
        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['success' => true, 'message' => 'Já registrada.'];
            }
            return ['success' => false, 'message' => 'Migração executada mas falha ao registrar: ' . $e->getMessage()];
        }
        return ['success' => true, 'message' => $duplicateColumn ? 'Colunas já existiam; registrado.' : 'OK'];
    }

    /**
     * Executa todas as pendentes.
     * @return array{run: int, ok: int, errors: array<int, string>}
     */
    public static function runPending(): array
    {
        $pending = self::getPending();
        $run = 0;
        $ok = 0;
        $errors = [];
        foreach ($pending as $m) {
            $run++;
            $result = self::runOne($m);
            if ($result['success']) {
                $ok++;
            } else {
                $errors[$run] = ($m['id'] ?? '?') . ': ' . $result['message'];
            }
        }
        return ['run' => $run, 'ok' => $ok, 'errors' => $errors];
    }

    private static function splitSql(string $sql): array
    {
        $sql = trim($sql);
        $parts = preg_split('/\s*;\s*(?=(?:[^\'"]|\'[^\']*\'|"[^"]*")*$)/', $sql);
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            $lines = explode("\n", $p);
            $stmt = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '--') === 0) {
                    continue;
                }
                $stmt[] = $line;
            }
            $s = implode(' ', $stmt);
            if ($s !== '') {
                $out[] = $s;
            }
        }
        return $out;
    }
}
