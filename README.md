# NanoCDN

Aplicação leve em PHP + MySQL para upload e processamento de arquivos em hospedagens compartilhadas, com multi-tenant e API por chave. Instalação pelo navegador, sem shell.

## Requisitos

- PHP 7.4+ (recomendado 8.x) com extensões: PDO, pdo_mysql, json, mbstring
- MySQL 5.7+ ou MariaDB 10.3+
- Para conversão de imagens: GD e/ou Imagick (WebP/AVIF conforme suporte)

**Hospedagem compartilhada:** se o limite de upload for menor que o desejado (ex.: 50 MB), ajuste no painel ou via `.user.ini`: `upload_max_filesize` e `post_max_size` (ex.: `upload_max_filesize=64M`, `post_max_size=64M`).

## Instalação

1. Coloque os arquivos no servidor (document root pode ser a raiz do projeto ou `public/`).
2. Acesse `https://seu-dominio.com/install.php`. Se o document root for `public/`, use `/install.php` (o script em `public/install.php` repassa para o instalador na raiz).
3. Preencha os dados do banco e do primeiro usuário admin; opcionalmente informe a URL base (útil atrás de proxy). Conclua a instalação.
4. Faça login em **Admin → Login** e crie um tenant. Gere uma API Key para usar na API.
5. (Opcional) Em **Checker** verifique se o servidor suporta conversão; em **config/config.php** defina `conversion.enabled = true` e ajuste tamanhos/formatos.

## Antes de colocar em produção

- [ ] HTTPS ativo e redirecionamento HTTP → HTTPS
- [ ] Remover ou renomear `install.php` e `public/install.php`
- [ ] Definir `NANOCDN_BASE_URL` (ou URL base na instalação) se usar proxy/subdomínio
- [ ] Conferir **Checker** (/admin/check): banco conectado, storage gravável
- [ ] Backup do banco e da pasta `storage/` configurado

## Segurança

- **HTTPS:** use sempre em produção; configure o servidor para redirecionar HTTP → HTTPS.
- **install.php:** após instalar, remova ou renomeie `install.php` (e `public/install.php`) para evitar reinstalação. O instalador já exibe "Já instalado" se o banco estiver configurado.
- **API Key:** trate como senha; não exponha em front-end público. Para chamadas no navegador, prefira um backend intermediário que use a API Key no servidor.
- **Painel admin:** o login e todos os formulários do painel usam proteção CSRF.
- **Storage:** a pasta `storage/` não deve ser acessível diretamente pela URL; os arquivos são entregues apenas pela rota `/f/{tenant_uuid}/{file_uuid}/{filename}`. O `.htaccess` em `storage/` nega acesso direto quando o servidor suporta `mod_authz_core`.
- **Sessão:** o cookie de sessão do painel usa `HttpOnly`, `SameSite=Lax` e, em HTTPS, `Secure`; o ID de sessão é regenerado após login.
- **Upload:** a API valida o tipo do arquivo pelo conteúdo (`finfo`) quando a lista de MIMEs permitidos está configurada.
- **robots.txt:** em `public/robots.txt` estão bloqueados `/admin` e `/install.php` para crawlers; `/f/` e `/api/health` são permitidos.

## Backup e manutenção

- Faça backup regular do **banco de dados** (export MySQL) e da pasta **storage/** (arquivos dos tenants).
- Use o **Checker** (/admin/check) para conferir conexão com o banco e permissão de gravação em storage antes de depender do sistema em produção.

## Estrutura

- **`.env`** – na **raiz do projeto** (criado pelo install ou manualmente). Credenciais do banco e opções (NANOCDN_DB_*, NANOCDN_BASE_URL, etc.). Pode editar à mão para ajustar.
- `public/` – front controller (`index.php`), API (`api.php`), admin (`admin.php`) e views
- `config/` – configuração (`config.php`); `.env.example` é só modelo
- `src/` – classes (Database, Auth, Tenant, FileManager, ImageConverter)
- `storage/` – arquivos por tenant: `{tenant_uuid}/{file_uuid}/{arquivo}-{size}.{ext}`
- `docs/API.md` – documentação detalhada da API para integração

## Atualização pelo painel

Se o projeto foi instalado via **git clone** ([schematizeio/nanocdn](https://github.com/schematizeio/nanocdn)), use **Admin → Atualizar** para rodar `git pull` e trazer as últimas alterações. Em hospedagem compartilhada, `exec()` pode estar desabilitada; nesse caso, atualize manualmente (git pull no servidor).

## Documentação da API

Consulte **docs/API.md** para:

- Autenticação (header `API-Key`)
- Endpoints: upload, listar, detalhe, excluir
- Formato das URLs públicas dos arquivos
- Exemplos em cURL, PHP e JavaScript

## CORS

Para chamar a API a partir de outro domínio (navegador), habilite CORS: defina `NANOCDN_CORS=1` no ambiente ou em `config/.env.installed`. Em `config/config.php` é possível restringir `cors.allowed_origins` a domínios específicos.

## Próximos passos opcionais

- Definir **URL base** na instalação (ou `NANOCDN_BASE_URL` no ambiente) se a aplicação estiver atrás de proxy ou em subdomínio.
- Avaliar rate limit na API, webhooks pós-upload ou metadados nos arquivos conforme a necessidade.

## Configuração de conversão

Em `config/config.php`:

- `conversion.enabled` – ativa geração de variações (global).
- `conversion.sizes` – lista de `{w, h}` (ex.: 1920x1080, 512x512).
- `conversion.formats` – ex.: `['png','webp','avif']`.

Por tenant, a opção **Conversão de imagens** no painel liga/desliga o uso dessas regras para aquele tenant.
