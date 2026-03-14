# NanoCDN – Documentação da API

Esta documentação descreve como integrar outros serviços à API do NanoCDN para upload, listagem e exclusão de arquivos, e como obter URLs públicas para entrega via CDN.

---

## Índice

1. [Visão geral](#1-visão-geral)
2. [Autenticação](#2-autenticação)
3. [Base URL e endpoints](#3-base-url-e-endpoints)
4. [Limites e configuração](#4-limites-e-configuração)
5. [Upload de arquivo](#5-upload-de-arquivo)
6. [Listar arquivos](#6-listar-arquivos)
7. [Obter detalhes de um arquivo](#7-obter-detalhes-de-um-arquivo)
8. [Excluir arquivo](#8-excluir-arquivo)
9. [URLs públicas dos arquivos](#9-urls-públicas-dos-arquivos)
10. [Códigos HTTP e erros](#10-códigos-http-e-erros)
11. [Exemplos de integração](#11-exemplos-de-integração)
12. [API compatível com S3](#12-api-compatível-com-s3)

---

## 1. Visão geral

A API do NanoCDN é RESTful e usa **API Key** no header para identificar o tenant. Todas as requisições (exceto a URL pública de arquivos) devem incluir o header de autenticação.

- **Formato das respostas:** JSON (`Content-Type: application/json; charset=utf-8`).
- **Métodos:** GET, POST, DELETE.
- **Autenticação:** header `API-Key` (ou `X-Api-Key`) com a chave do tenant.
- **CORS:** opcional. Se habilitado (`NANOCDN_CORS=1` ou `config.php` → `cors.enabled`), a API envia os headers necessários para requisições a partir de outro domínio (navegador). Requisições `OPTIONS` são respondidas com 204. Útil para SPAs ou front-ends em domínio diferente.

---

## 2. Autenticação

Cada **tenant** possui uma ou mais **API Keys**. A chave é gerada no painel admin (Tenant → Gerar nova API Key) e deve ser guardada em local seguro; ela é exibida apenas uma vez no momento da criação.

### Como enviar a API Key

Inclua em **todas** as requisições à API (exceto ao acessar a URL pública do arquivo):

- **Header recomendado:** `API-Key: nc_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
- **Alternativa:** `X-Api-Key: nc_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

Exemplo com cURL:

```bash
curl -H "API-Key: nc_sua_chave_aqui" "https://seu-dominio.com/api/files"
```

Exemplo com PHP:

```php
$ch = curl_init('https://seu-dominio.com/api/files');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['API-Key: nc_sua_chave_aqui']);
```

### Respostas de autenticação

| Situação        | HTTP   | Resposta JSON (exemplo)                          |
|-----------------|--------|--------------------------------------------------|
| Header ausente  | 401    | `{"error":"Missing API-Key header","code":401}`  |
| Chave inválida  | 403    | `{"error":"Invalid API key","code":403}`         |

---

## 3. Base URL e endpoints

Substitua `https://seu-dominio.com` pela URL base da sua instalação do NanoCDN.

| Recurso              | Método | Caminho           | Descrição                    |
|----------------------|--------|-------------------|------------------------------|
| Saúde                | GET    | `/api/health`     | Status da API e do banco (sem API Key) |
| Upload               | POST   | `/api/upload`     | Enviar um arquivo            |
| Listar arquivos      | GET    | `/api/files`      | Listar arquivos do tenant    |
| Detalhes do arquivo  | GET    | `/api/files/{uuid}`| Dados e URLs de um arquivo   |
| Excluir arquivo      | DELETE | `/api/files/{uuid}`| Excluir um arquivo           |
| **S3-compatible**   |        | `/api/s3/{bucket}/{key}` | PutObject, GetObject, DeleteObject, HeadObject, ListObjectsV2 — ver [§12](#12-api-compatível-com-s3) e [S3-COMPATIBLE.md](S3-COMPATIBLE.md) |

**URL pública do arquivo e saúde (sem API Key):**

- GET `https://seu-dominio.com/api/health` – retorna `{"status":"ok","database":"connected","version":"0.1.0"}` ou 503 com `version` em caso de banco inacessível.
- GET `https://seu-dominio.com/f/{tenant_uuid}/{file_uuid}/{filename}`  
  Exemplo: `https://seu-dominio.com/f/a1b2c3d4-.../e5f6g7h8-.../imagem-1920x1080.webp`

---

## 4. Limites e configuração

- **Tamanho máximo de upload:** definido em `config/config.php` → `upload.max_size_mb` (padrão 50 MB). O servidor PHP deve ter `upload_max_filesize` e `post_max_size` iguais ou maiores.
- **Tipos de arquivo permitidos:** `upload.allowed_mimes` (ex.: `image/jpeg`, `image/png`, `image/gif`, `image/webp`, `image/avif`). O servidor valida também pelo conteúdo (finfo). Se a lista estiver vazia, a validação por MIME é desativada.
- **Listagem de arquivos:** `limit` máximo 100 por requisição; `offset` para paginação.
- **Entrega `/f/`:** suporta cache condicional (ETag, Last-Modified, 304). Segurança: path resolvido deve estar dentro do storage.

---

## 5. Upload de arquivo

Envia um arquivo para o tenant associado à API Key. O arquivo é armazenado e, se a conversão estiver habilitada para o tenant, são geradas variações (tamanhos/formatos configurados).

### Request

- **Método:** POST  
- **URL:** `{BASE_URL}/api/upload`  
- **Content-Type:** `multipart/form-data`  
- **Campo do arquivo:** `file` (nome do campo obrigatório)

### Headers obrigatórios

- `API-Key: nc_...`

### Parâmetros do formulário

| Campo | Tipo   | Obrigatório | Descrição                    |
|-------|--------|-------------|------------------------------|
| file  | arquivo| Sim         | Arquivo de imagem (JPEG, PNG, GIF, WebP, AVIF) |

### Resposta de sucesso (HTTP 201)

```json
{
  "ok": true,
  "file_uuid": "550e8400-e29b-41d4-a716-446655440000",
  "original_name": "foto.png",
  "variants": [
    {
      "size_key": "original",
      "format": "png",
      "path": "tenant-uuid/file-uuid/foto-original.png",
      "size_bytes": 12345,
      "url": "https://seu-dominio.com/f/tenant-uuid/file-uuid/foto-original.png"
    },
    {
      "size_key": "1920x1080",
      "format": "webp",
      "path": "tenant-uuid/file-uuid/foto-1920x1080.webp",
      "size_bytes": 8000,
      "url": "https://seu-dominio.com/f/tenant-uuid/file-uuid/foto-1920x1080.webp"
    }
  ]
}
```

- **file_uuid:** identificador único do arquivo (use em GET e DELETE).  
- **variants:** lista de versões (original + conversões). Cada item inclui **url** pronta para uso.

### Erros comuns

| HTTP | JSON (exemplo) | Causa provável              |
|------|----------------|-----------------------------|
| 400  | `{"error":"Upload failed","upload_error":...}` | Falha no upload (campo `file` ou erro PHP) |
| 400  | `{"error":"File too large","max_mb":50}`      | Arquivo maior que o limite (ex.: 50 MB)    |
| 400  | `{"error":"File type not allowed","allowed":[...]}` | MIME type não permitido   |
| 500  | `{"error":"Server error","message":"..."}`   | Erro interno (ex.: disco, permissão)       |

---

## 6. Listar arquivos

Retorna a lista de arquivos do tenant com suas variações e URLs.

### Request

- **Método:** GET  
- **URL:** `{BASE_URL}/api/files`  
- **Query (opcional):**  
  - `limit` – quantidade por página (padrão: 20, máximo: 100)  
  - `offset` – deslocamento para paginação (padrão: 0)

Exemplo: `GET /api/files?limit=10&offset=0`

### Resposta de sucesso (HTTP 200)

A resposta inclui `total` (total de arquivos do tenant), `limit` e `offset` para paginação.

```json
{
  "total": 42,
  "limit": 20,
  "offset": 0,
  "files": [
    {
      "id": 1,
      "file_uuid": "550e8400-e29b-41d4-a716-446655440000",
      "original_name": "foto.png",
      "mime_type": "image/png",
      "extension": "png",
      "size_bytes": 12345,
      "path_original": "tenant-uuid/file-uuid/foto-original.png",
      "created_at": "2025-03-14 12:00:00",
      "variants": [
        {
          "size_key": "original",
          "format": "png",
          "path": "...",
          "size_bytes": 12345
        }
      ],
      "urls": {
        "original.png": "https://seu-dominio.com/f/tenant-uuid/file-uuid/foto-original.png",
        "1920x1080.webp": "https://seu-dominio.com/f/tenant-uuid/file-uuid/foto-1920x1080.webp"
      }
    }
  ]
}
```

O objeto **urls** mapeia chaves `size_key.format` para a URL pública da variante.

---

## 7. Obter detalhes de um arquivo

Retorna os dados de um arquivo específico pelo `file_uuid`.

### Request

- **Método:** GET  
- **URL:** `{BASE_URL}/api/files/{file_uuid}`  

Exemplo: `GET /api/files/550e8400-e29b-41d4-a716-446655440000`

### Resposta de sucesso (HTTP 200)

```json
{
  "id": 1,
  "file_uuid": "550e8400-e29b-41d4-a716-446655440000",
  "original_name": "foto.png",
  "mime_type": "image/png",
  "extension": "png",
  "size_bytes": 12345,
  "path_original": "tenant-uuid/file-uuid/foto-original.png",
  "created_at": "2025-03-14 12:00:00",
  "variants": [
    {
      "size_key": "original",
      "format": "png",
      "path": "...",
      "size_bytes": 12345,
      "url": "https://seu-dominio.com/f/tenant-uuid/file-uuid/foto-original.png"
    }
  ]
}
```

### Erro

- **404:** `{"error":"File not found"}` – arquivo não existe ou pertence a outro tenant.

---

## 8. Excluir arquivo

Remove o arquivo e todas as suas variações (arquivos em disco e registros).

### Request

- **Método:** DELETE  
- **URL:** `{BASE_URL}/api/files/{file_uuid}`  

Exemplo: `DELETE /api/files/550e8400-e29b-41d4-a716-446655440000`

### Resposta de sucesso (HTTP 200)

```json
{
  "ok": true
}
```

### Erro

- **404:** `{"error":"File not found"}`

---

## 9. URLs públicas dos arquivos

As URLs de entrega **não** exigem API Key. São pensadas para uso em front-end (img src, link de download, etc.).

### Formato

```
{BASE_URL}/f/{tenant_uuid}/{file_uuid}/{filename}
```

- **tenant_uuid:** UUID do tenant (fixo por instalação/tenant).  
- **file_uuid:** retornado no upload e nas respostas da API.  
- **filename:** nome do arquivo na pasta (ex.: `foto-1920x1080.webp`). Pode ser obtido pelo último segmento de `path` em cada variante.

### Exemplo

Após um upload que retornou:

```json
"variants": [
  { "size_key": "1920x1080", "format": "webp", "path": "a1b2.../e5f6.../foto-1920x1080.webp" }
]
```

A URL pública é:

```
https://seu-dominio.com/f/a1b2c3d4-uuid-tenant/e5f6g7h8-uuid-file/foto-1920x1080.webp
```

Os headers de resposta incluem cache (`Cache-Control: public, max-age=31536000`), `ETag` e `Last-Modified` para validação condicional (o servidor responde 304 Not Modified quando o cliente envia `If-None-Match` ou `If-Modified-Since` adequados). Útil para CDN e proxy.

**Segurança:** o servidor valida que o arquivo resolvido está dentro do diretório de storage (via `realpath`) antes de entregar; symlinks e path traversal são bloqueados.

---

## 10. Códigos HTTP e erros

| Código | Significado   | Uso típico na API                          |
|--------|---------------|--------------------------------------------|
| 200    | OK            | GET (list/detail), DELETE                  |
| 201    | Created       | POST upload sucesso                        |
| 304    | Not Modified  | GET /f/... com If-None-Match/If-Modified-Since (cache) |
| 400    | Bad Request   | Upload inválido, tipo/tamanho não permitido|
| 401    | Unauthorized  | API Key ausente                            |
| 403    | Forbidden     | API Key inválida ou inativa                |
| 404    | Not Found     | Recurso (arquivo/endpoint) não encontrado   |
| 500    | Server Error  | Erro interno (log no servidor)             |

Sempre que houver erro, o corpo é JSON, por exemplo:

```json
{
  "error": "Descrição curta",
  "code": 401
}
```

Alguns erros incluem campos extras (ex.: `upload_error`, `max_mb`, `allowed`, `message`).

---

## 11. Exemplos de integração

### 11.1 cURL – Upload

```bash
curl -X POST "https://seu-dominio.com/api/upload" \
  -H "API-Key: nc_sua_chave_aqui" \
  -F "file=@/caminho/local/foto.png"
```

### 11.2 cURL – Listar arquivos

```bash
curl -H "API-Key: nc_sua_chave_aqui" \
  "https://seu-dominio.com/api/files?limit=20&offset=0"
```

### 11.3 cURL – Obter detalhes de um arquivo

```bash
curl -H "API-Key: nc_sua_chave_aqui" \
  "https://seu-dominio.com/api/files/550e8400-e29b-41d4-a716-446655440000"
```

### 11.4 cURL – Excluir arquivo

```bash
curl -X DELETE -H "API-Key: nc_sua_chave_aqui" \
  "https://seu-dominio.com/api/files/550e8400-e29b-41d4-a716-446655440000"
```

### 11.5 PHP – Upload e uso da URL

```php
<?php
$apiKey = 'nc_sua_chave_aqui';
$baseUrl = 'https://seu-dominio.com';
$filePath = '/caminho/local/foto.png';

$cfile = new CURLFile($filePath, mime_content_type($filePath), basename($filePath));
$ch = curl_init($baseUrl . '/api/upload');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => ['file' => $cfile],
    CURLOPT_HTTPHEADER => ['API-Key: ' . $apiKey],
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
if ($httpCode === 201 && !empty($data['variants'])) {
    $url = $data['variants'][0]['url'];
    echo "Arquivo disponível em: $url\n";
} else {
    echo "Erro: " . ($data['error'] ?? $response) . "\n";
}
```

### 11.6 PHP – Listar e usar URLs em HTML

```php
<?php
$apiKey = 'nc_sua_chave_aqui';
$baseUrl = 'https://seu-dominio.com';

$ch = curl_init($baseUrl . '/api/files?limit=10');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ['API-Key: ' . $apiKey],
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

if (!empty($data['files'])) {
    foreach ($data['files'] as $file) {
        $urls = $file['urls'] ?? [];
        $url = $urls['original.' . $file['extension']] ?? reset($urls);
        echo '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($file['original_name']) . '">' . "\n";
    }
}
```

### 11.7 JavaScript (fetch) – Upload

```javascript
const apiKey = 'nc_sua_chave_aqui';
const baseUrl = 'https://seu-dominio.com';

async function uploadFile(file) {
  const form = new FormData();
  form.append('file', file);
  const res = await fetch(baseUrl + '/api/upload', {
    method: 'POST',
    headers: { 'API-Key': apiKey },
    body: form,
  });
  const data = await res.json();
  if (res.status === 201) {
    console.log('URL:', data.variants[0].url);
    return data;
  }
  throw new Error(data.error || 'Upload failed');
}

// Uso: input type="file" -> input.files[0]
document.querySelector('input[type=file]').addEventListener('change', async (e) => {
  const file = e.target.files[0];
  if (file) await uploadFile(file);
});
```

### 11.8 Integração em outro backend (ex.: Node/Go/Python)

- Use o mesmo formato: **POST** `multipart/form-data` com campo **file** e header **API-Key**.
- Trate os códigos 201 (sucesso), 400 (validação), 401/403 (auth) e 500 (erro servidor).
- Armazene o `file_uuid` e as URLs retornadas em **variants[].url** para referência e exibição.

---

## Resumo rápido para implementação em outros serviços

1. **Obter API Key** no painel NanoCDN (tenant → Gerar nova API Key).  
2. **Upload:** POST `{BASE}/api/upload`, header `API-Key`, body `multipart/form-data` com campo `file`.  
3. **Guardar** `file_uuid` e as URLs em `variants[].url` (ou `urls` no GET).  
4. **Listar:** GET `{BASE}/api/files` com `API-Key` e paginação `limit`/`offset`.  
5. **Detalhe:** GET `{BASE}/api/files/{file_uuid}` com `API-Key`.  
6. **Excluir:** DELETE `{BASE}/api/files/{file_uuid}` com `API-Key`.  
7. **Exibir/entregar:** usar a URL pública `{BASE}/f/{tenant_uuid}/{file_uuid}/{filename}` sem autenticação.

Com isso, a integração da API do NanoCDN em outros serviços fica coberta de forma consistente e previsível.

---

## 12. API compatível com S3

O NanoCDN expõe uma **API compatível com S3** (Amazon Simple Storage Service) para permitir o uso de clientes e SDKs que falam o protocolo S3 (listagem em XML, PutObject, GetObject, DeleteObject, HeadObject, ListObjectsV2).

### Diferenças em relação ao S3 da AWS

- **Autenticação:** em vez de AWS Signature Version 4, use o header **`API-Key`** (ou `X-Api-Key`) com a API Key do tenant, igual à API REST do NanoCDN.
- **Bucket:** o “bucket” é o **tenant**. Use o **slug** ou o **UUID** do tenant como nome do bucket na URL. Ex.: se o tenant tem slug `meu-site`, as chamadas são `PUT /api/s3/meu-site/chave/do/objeto`.
- **Base URL:** `{BASE_URL}/api/s3/{bucket}/{key}` (objetos) ou `{BASE_URL}/api/s3/{bucket}?list-type=2&...` (listagem).
- **Respostas de listagem:** XML no formato ListBucketResult (ListObjectsV2). Erros também em XML (`<Error><Code>...</Code><Message>...</Message></Error>`).

### Endpoints S3 (resumo)

| Operação        | Método | URL | Descrição |
|-----------------|--------|-----|-----------|
| PutObject      | PUT    | `/api/s3/{bucket}/{key}` | Cria ou substitui objeto; corpo = conteúdo binário |
| GetObject      | GET    | `/api/s3/{bucket}/{key}` | Retorna o conteúdo do objeto |
| HeadObject     | HEAD   | `/api/s3/{bucket}/{key}` | Retorna apenas headers (Content-Length, Content-Type, ETag, Last-Modified) |
| DeleteObject   | DELETE | `/api/s3/{bucket}/{key}` | Remove o objeto |
| ListObjectsV2  | GET    | `/api/s3/{bucket}?list-type=2&prefix=&max-keys=&continuation-token=` | Lista objetos (XML) |

### Exemplo rápido – cURL

```bash
# Upload (PutObject)
curl -X PUT "https://seu-dominio.com/api/s3/meu-tenant/imagens/foto.png" \
  -H "API-Key: nc_sua_chave" \
  -H "Content-Type: image/png" \
  --data-binary @foto.png

# Download (GetObject)
curl -H "API-Key: nc_sua_chave" \
  "https://seu-dominio.com/api/s3/meu-tenant/imagens/foto.png" -o foto.png

# Listar (ListObjectsV2)
curl -H "API-Key: nc_sua_chave" \
  "https://seu-dominio.com/api/s3/meu-tenant?list-type=2&prefix=imagens/&max-keys=100"

# Excluir (DeleteObject)
curl -X DELETE -H "API-Key: nc_sua_chave" \
  "https://seu-dominio.com/api/s3/meu-tenant/imagens/foto.png"
```

**Documentação completa** (formato XML, paginação, erros, uso com AWS CLI e outros clientes): [S3-COMPATIBLE.md](S3-COMPATIBLE.md).
