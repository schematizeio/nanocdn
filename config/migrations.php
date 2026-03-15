<?php
/**
 * Lista de migrações (id único, nome para exibição, SQL).
 * Novas migrações: adicione ao array e mantenha o id único.
 */
return [
    [
        'id' => 'tenant_conversion_columns',
        'name' => 'Colunas conversion_sizes e conversion_formats na tabela tenants',
        'sql' => "ALTER TABLE tenants ADD COLUMN conversion_sizes text DEFAULT NULL COMMENT 'JSON array of size keys, null = use all global';
ALTER TABLE tenants ADD COLUMN conversion_formats text DEFAULT NULL COMMENT 'JSON array of formats, null = use all global';",
    ],
    [
        'id' => 'files_s3_key',
        'name' => 'Coluna s3_key na tabela files (API S3-compatible)',
        'sql' => "ALTER TABLE files ADD COLUMN s3_key varchar(512) DEFAULT NULL COMMENT 'Object key for S3 API';
ALTER TABLE files ADD UNIQUE KEY tenant_s3_key (tenant_id, s3_key);",
    ],
    [
        'id' => 'admin_invites',
        'name' => 'Tabela admin_invites (convites de cadastro)',
        'sql' => "CREATE TABLE IF NOT EXISTS admin_invites (
  id int unsigned NOT NULL AUTO_INCREMENT,
  token varchar(64) NOT NULL,
  email varchar(255) DEFAULT NULL,
  used_at datetime DEFAULT NULL,
  created_by int unsigned DEFAULT NULL,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY token (token),
  KEY used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ],
];
