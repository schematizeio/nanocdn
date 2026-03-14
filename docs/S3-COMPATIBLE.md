# NanoCDN – API compatível com S3

Este documento descreve a **API S3-compatible** do NanoCDN: endpoints, autenticação, formato de requisições e respostas (incluindo XML), erros e exemplos com cURL, AWS CLI e SDKs.

---

## Índice

1. [Visão geral](#1-visão-geral)
2. [Autenticação e bucket](#2-autenticação-e-bucket)
3. [Base URL e convenções](#3-base-url-e-convenções)
4. [PutObject](#4-putobject)
5. [GetObject](#5-getobject)
6. [HeadObject](#6-headobject)
7. [DeleteObject](#7-deleteobject)
8. [ListObjectsV2](#8-listobjectsv2)
9. [Respostas de erro (XML)](#9-respostas-de-erro-xml)
10. [Limites e compatibilidade](#10-limites-e-compatibilidade)
11. [Exemplos com AWS CLI e SDKs](#11-exemplos-com-aws-cli-e-sdks)

---

## 1. Visão geral

A API S3-compatible do NanoCDN emula operações comuns do Amazon S3:

- **PutObject** – upload de objeto (cria ou sobrescreve)
- **GetObject** – download de objeto
- **HeadObject** – metadados do objeto (headers)
- **DeleteObject** – exclusão de objeto
- **ListObjectsV2** – listagem de objetos com prefixo e paginação

**Diferença principal em relação ao AWS S3:** a autenticação é feita com o header **`API-Key`** (API Key do tenant), e não com AWS Signature Version 4. O **bucket** na URL corresponde ao **tenant** (use o slug ou o UUID do tenant).

Objetos enviados via esta API recebem uma **chave S3** (`s3_key`) que é usada nas operações Get, Head, Delete e na listagem. A entrega pública continua disponível em `/f/{tenant_uuid}/{file_uuid}/{filename}` para todas as variantes (original e conversões).

---

## 2. Autenticação e bucket

### Autenticação

Todas as requisições à API S3 do NanoCDN devem incluir:

- **Header:** `API-Key: nc_...` (ou `X-Api-Key: nc_...`)

A mesma API Key usada na API REST (`/api/upload`, `/api/files`) é usada aqui. Ela identifica o **tenant**.

### Bucket = Tenant

Na URL, o primeiro segmento após `/api/s3/` é o **bucket**. No NanoCDN, o bucket é o **tenant**:

- Use o **slug** do tenant (ex.: `meu-site`) **ou**
- Use o **UUID** do tenant (ex.: `a1b2c3d4-e5f6-7890-abcd-ef1234567890`)

O servidor valida que o bucket informado corresponde ao tenant da API Key. Se não corresponder, retorna **403 AccessDenied**.

Exemplos de URL base para um tenant com slug `acme`:

- `https://seu-dominio.com/api/s3/acme/...`
- Ou, com UUID: `https://seu-dominio.com/api/s3/a1b2c3d4-e5f6-7890-abcd-ef1234567890/...`

### Key (chave do objeto)

A **key** é o caminho lógico do objeto, com suporte a “pastas” (ex.: `imagens/2024/foto.png`). Pode ser qualquer string válida em URL; segmentos após o bucket são unidos com `/` para formar a key.

---

## 3. Base URL e convenções

- **Base URL:** `{BASE_URL}/api/s3`
- **Objeto:** `{BASE_URL}/api/s3/{bucket}/{key}`  
  Ex.: `https://cdn.exemplo.com/api/s3/acme/assets/logo.png` → bucket=`acme`, key=`assets/logo.png`
- **Listagem:** `{BASE_URL}/api/s3/{bucket}?list-type=2&prefix=...&max-keys=...&continuation-token=...`

Métodos HTTP:

| Operação       | Método |
|----------------|--------|
| PutObject      | PUT    |
| GetObject      | GET    |
| HeadObject     | HEAD   |
| DeleteObject   | DELETE |
| ListObjectsV2  | GET (sem key no path) |

---

## 4. PutObject

Cria um objeto ou substitui um existente com a mesma key.

### Request

- **Método:** PUT  
- **URL:** `{BASE_URL}/api/s3/{bucket}/{key}`  
- **Headers obrigatórios:** `API-Key` (ou `X-Api-Key`)  
- **Headers opcionais:** `Content-Type` (tipo MIME do corpo)  
- **Body:** conteúdo binário do objeto (corpo da requisição)

### Exemplo (cURL)

```bash
curl -X PUT "https://seu-dominio.com/api/s3/acme/imagens/foto.png" \
  -H "API-Key: nc_sua_chave_aqui" \
  -H "Content-Type: image/png" \
  --data-binary @/caminho/local/foto.png
```

### Resposta de sucesso

- **HTTP 200**
- **Headers:** `ETag` (entre aspas, ex.: `"d41d8cd98f00b204e9800998ecf8427e"`)
- **Body:** vazio (Content-Length: 0)

### Comportamento

- Se já existir um objeto com a mesma key no tenant, ele é **substituído** (overwrite).
- O tamanho do corpo é limitado pelo mesmo limite de upload da API REST (ex.: 50 MB por padrão).
- Para imagens, se o tenant tiver conversão habilitada, serão geradas variantes (tamanhos/formatos configurados). O GetObject retorna a variante **original**; as demais ficam acessíveis pela URL pública `/f/...`.

### Erros

| HTTP | Código XML   | Condição                          |
|------|--------------|-----------------------------------|
| 400  | EntityTooLarge | Corpo maior que o limite permitido |
| 403  | AccessDenied | Bucket não corresponde ao tenant da API Key |
| 500  | InternalError | Erro ao salvar (disco, etc.)     |

---

## 5. GetObject

Retorna o conteúdo do objeto.

### Request

- **Método:** GET  
- **URL:** `{BASE_URL}/api/s3/{bucket}/{key}`  
- **Headers obrigatórios:** `API-Key` (ou `X-Api-Key`)

### Resposta de sucesso

- **HTTP 200**
- **Headers:**  
  - `Content-Type` – tipo MIME do objeto  
  - `Content-Length` – tamanho em bytes  
  - `Last-Modified` – data/hora em GMT (RFC 2822)  
  - `ETag` – identificador do recurso (entre aspas)  
  - `Accept-Ranges: bytes`
- **Body:** conteúdo binário do objeto (variante original)

### Exemplo (cURL)

```bash
curl -H "API-Key: nc_sua_chave_aqui" \
  "https://seu-dominio.com/api/s3/acme/imagens/foto.png" \
  -o foto.png
```

### Erros

| HTTP | Código XML | Condição        |
|------|------------|-----------------|
| 404  | NoSuchKey  | Key não existe  |

---

## 6. HeadObject

Retorna apenas os headers do objeto (sem corpo). Útil para verificar existência, tamanho e tipo.

### Request

- **Método:** HEAD  
- **URL:** `{BASE_URL}/api/s3/{bucket}/{key}`  
- **Headers obrigatórios:** `API-Key` (ou `X-Api-Key`)

### Resposta de sucesso

- **HTTP 200**
- **Headers:** `Content-Type`, `Content-Length`, `Last-Modified`, `ETag`, `Accept-Ranges: bytes`
- **Body:** nenhum

### Erros

- **404** – key não existe (resposta sem corpo ou com XML de erro).

---

## 7. DeleteObject

Remove o objeto (e todas as variantes associadas).

### Request

- **Método:** DELETE  
- **URL:** `{BASE_URL}/api/s3/{bucket}/{key}`  
- **Headers obrigatórios:** `API-Key` (ou `X-Api-Key`)

### Resposta de sucesso

- **HTTP 204 No Content**
- **Body:** vazio

### Resposta quando a key não existe

- **HTTP 404** com corpo XML de erro (NoSuchKey).

### Exemplo (cURL)

```bash
curl -X DELETE -H "API-Key: nc_sua_chave_aqui" \
  "https://seu-dominio.com/api/s3/acme/imagens/foto.png"
```

---

## 8. ListObjectsV2

Lista objetos do bucket (tenant) com suporte a prefixo e paginação. A resposta é em **XML**, no formato ListBucketResult (compatível com S3).

### Request

- **Método:** GET  
- **URL:** `{BASE_URL}/api/s3/{bucket}?list-type=2&prefix=...&max-keys=...&continuation-token=...`  
- **Headers obrigatórios:** `API-Key` (ou `X-Api-Key`)

### Parâmetros de query

| Parâmetro           | Obrigatório | Descrição |
|---------------------|-------------|-----------|
| list-type=2         | Sim         | Deve ser `2` para ListObjectsV2 |
| prefix              | Não         | Prefixo da key (ex.: `imagens/`). Só retorna keys que começam com o prefixo. |
| max-keys            | Não         | Número máximo de objetos na resposta (1–1000; padrão 1000). |
| continuation-token  | Não         | Token de continuação para a próxima página (valor numérico retornado em `NextContinuationToken`). |

### Resposta de sucesso (XML)

- **HTTP 200**
- **Content-Type:** `application/xml; charset=utf-8`
- **Body:** XML no formato:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
  <Name>acme</Name>
  <Prefix>imagens/</Prefix>
  <KeyCount>2</KeyCount>
  <MaxKeys>1000</MaxKeys>
  <IsTruncated>true</IsTruncated>
  <NextContinuationToken>1000</NextContinuationToken>
  <Contents>
    <Key>imagens/foto1.png</Key>
    <Size>12345</Size>
    <LastModified>2025-03-14T12:00:00.000Z</LastModified>
    <ETag>"abc123..."</ETag>
  </Contents>
  <Contents>
    <Key>imagens/foto2.png</Key>
    <Size>67890</Size>
    <LastModified>2025-03-14T13:00:00.000Z</LastModified>
    <ETag>"def456..."</ETag>
  </Contents>
</ListBucketResult>
```

- **Name:** bucket (tenant).
- **Prefix:** prefixo solicitado.
- **KeyCount:** quantidade de itens em **Contents** nesta página.
- **MaxKeys:** limite usado.
- **IsTruncated:** `true` se há mais páginas; use **NextContinuationToken** na próxima requisição.
- **NextContinuationToken:** enviar como `continuation-token` para obter a próxima página.
- **Contents:** um bloco por objeto, com **Key**, **Size**, **LastModified**, **ETag**.

### Observação

Apenas objetos que possuem **s3_key** (ou seja, criados via API S3 ou com key atribuída) aparecem na listagem. Objetos enviados somente pela API REST (`/api/upload`) sem key S3 não são listados aqui.

### Exemplo (cURL)

```bash
# Listar até 100 objetos com prefixo "imagens/"
curl -H "API-Key: nc_sua_chave_aqui" \
  "https://seu-dominio.com/api/s3/acme?list-type=2&prefix=imagens/&max-keys=100"

# Próxima página (usar o NextContinuationToken da resposta anterior)
curl -H "API-Key: nc_sua_chave_aqui" \
  "https://seu-dominio.com/api/s3/acme?list-type=2&prefix=imagens/&max-keys=100&continuation-token=1000"
```

---

## 9. Respostas de erro (XML)

Quando ocorre erro nas operações S3, o corpo da resposta é **XML**:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Error>
  <Code>NoSuchKey</Code>
  <Message>The specified key does not exist.</Message>
</Error>
```

### Códigos de erro comuns

| Código XML     | HTTP | Descrição |
|----------------|------|-----------|
| AccessDenied   | 403  | Bucket não corresponde ao tenant da API Key. |
| NoSuchKey      | 404  | Key não existe (GetObject, HeadObject, DeleteObject). |
| EntityTooLarge | 400  | Corpo do PutObject excede o limite de upload. |
| InvalidRequest | 400  | Método ou path não suportado. |
| InternalError  | 500  | Erro interno do servidor. |

---

## 10. Limites e compatibilidade

- **Tamanho máximo por objeto:** o mesmo da API REST (ex.: 50 MB), configurável em `upload.max_size_mb`.
- **Listagem:** `max-keys` entre 1 e 1000.
- **Key:** qualquer string válida em URL; tamanho máximo de armazenamento 512 caracteres.
- **Autenticação:** apenas header `API-Key` (ou `X-Api-Key`). AWS Signature Version 4 **não** é suportada.
- **Respostas:** ListObjectsV2 e erros em XML; PutObject/GetObject/Head/Delete seguem o comportamento descrito (headers e corpo binário quando aplicável).

---

## 11. Exemplos com AWS CLI e SDKs

### Uso com cURL (qualquer ambiente)

Já coberto nos tópicos acima. Lembre-se de usar sempre o header `API-Key`.

### Uso com AWS CLI (endpoint customizado)

O AWS CLI suporta endpoint customizado (S3-compatible). Como o NanoCDN usa API Key em vez de assinatura AWS, é necessário um **wrapper** ou configuração que injete o header. Exemplo conceitual com variável e endpoint:

```bash
# Definir endpoint e bucket (tenant)
export NANOCDN_URL="https://seu-dominio.com/api/s3"
export NANOCDN_BUCKET="acme"
export NANOCDN_KEY="nc_sua_chave"

# PutObject “manual” com cURL (recomendado para compatibilidade total)
curl -X PUT "${NANOCDN_URL}/${NANOCDN_BUCKET}/path/to/file.jpg" \
  -H "API-Key: ${NANOCDN_KEY}" \
  -H "Content-Type: image/jpeg" \
  --data-binary @file.jpg
```

Para usar `aws s3 cp` diretamente seria necessário um proxy ou gateway que converta chamadas assinadas em chamadas com `API-Key`; isso fica fora do escopo desta documentação.

### Uso em aplicações (PHP, Python, Node, Go)

- **PHP:** use cURL ou Guzzle com `PUT`/`GET`/`DELETE`/`HEAD` nas URLs acima e header `API-Key`.
- **Python:** `requests.put(url, data=open('file.png','rb'), headers={'API-Key': key, 'Content-Type': 'image/png'})` para PutObject; `requests.get(url, headers={'API-Key': key})` para GetObject; parse do XML da listagem com `xml.etree.ElementTree` ou similar.
- **Node.js:** `axios` ou `fetch` com os mesmos métodos e headers.
- **Go:** `http.NewRequest("PUT", url, body)` com header `API-Key`; idem para GET/DELETE/HEAD.

Em todos os casos, a **base URL** é `{BASE}/api/s3/{bucket}` e a **key** é o path do objeto. O bucket deve ser o slug ou o UUID do tenant associado à API Key.

---

## Resumo rápido

1. **Base:** `{BASE_URL}/api/s3/{bucket}/{key}` (objetos) ou `{BASE_URL}/api/s3/{bucket}?list-type=2&...` (listagem).
2. **Auth:** header `API-Key` (ou `X-Api-Key`) com a API Key do tenant.
3. **Bucket:** slug ou UUID do tenant.
4. **PutObject:** PUT com corpo binário; 200 + `ETag`.
5. **GetObject:** GET; 200 + corpo + headers (Content-Type, Content-Length, ETag, Last-Modified).
6. **HeadObject:** HEAD; 200 com headers apenas.
7. **DeleteObject:** DELETE; 204 quando existe, 404 quando não existe.
8. **ListObjectsV2:** GET com `list-type=2`, `prefix`, `max-keys`, `continuation-token`; resposta XML ListBucketResult.

Para mais detalhes da API REST (upload multipart, listagem JSON, etc.), consulte [API.md](API.md).
