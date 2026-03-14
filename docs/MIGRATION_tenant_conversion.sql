-- Migração: opções de conversão por tenant (tamanhos e formatos como subconjunto do global)
-- Execute uma vez se a tabela tenants já existia antes desta alteração.
-- Se der "Duplicate column name", as colunas já existem.

ALTER TABLE tenants ADD COLUMN conversion_sizes text DEFAULT NULL COMMENT 'JSON array of size keys, null = use all global';
ALTER TABLE tenants ADD COLUMN conversion_formats text DEFAULT NULL COMMENT 'JSON array of formats, null = use all global';
